/**
 * maintenance-schedule.js: when is each water heater maintenance job due? Type, install month and year, water and
 * (optional) the last service give the next date for each task in data/maintenance.json, plus the replacement
 * planning date from data/lifespans.json. Math is in formulas.js (maintenanceSchedule, addMonths).
 * Data: <script id="hwm-ms-data"> (data/computed/water-heater-maintenance-schedule.json → client).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result.
 */
import { maintenanceSchedule } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, checkLimits, invalidResult } from './ui.js';

const STATUS = { overdue: 'Overdue', soon: 'Due soon', ok: 'Scheduled' };
const month = (d) => d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', timeZone: 'UTC' });
const lc = (t) => t.charAt(0).toLowerCase() + t.slice(1);
const every = (m) => (m === null ? 'Once' : m % 12 === 0 ? (m === 12 ? 'Every year' : `Every ${m / 12} years`) : `Every ${m} months`);

/** Build the inputs maintenanceSchedule() needs from form values (months are 1–12; lastYear may be empty). */
export function toSchedule(v) {
	const installed = new Date(Date.UTC(v.installYear, (v.installMonth || 1) - 1, 15));
	const lastService = Number.isInteger(v.lastYear) ? new Date(Date.UTC(v.lastYear, (v.lastMonth || 1) - 1, 15)) : null;
	return { type: v.type, installed, water: v.water, lastService };
}

/**
 * @param {{type:string, installYear:number, installMonth:number, water:string, lastYear:number|null, lastMonth:number}} v
 * @param {object} cfg Client data (types, tasks, replacement, dueSoonDays, lifespans, limits, months).
 * @param {Date} asOf
 */
export function describe(v, cfg, asOf) {
	const bad = checkLimits({ installYear: v.installYear, lastYear: v.lastYear }, cfg.limits);
	if (!bad.length && Number.isInteger(v.installYear) && v.installYear > asOf.getUTCFullYear()) bad.push({ name: 'installYear', label: 'Year installed', message: 'Year installed is in the future.' });
	if (!bad.length && Number.isInteger(v.lastYear) && v.lastYear < v.installYear) bad.push({ name: 'lastYear', label: 'Last service year', message: 'The last service is before the install year; leave it blank if you are not sure.' });
	if (bad.length) return { ...invalidResult(bad), result: null };
	const s = toSchedule(v);
	const rows = maintenanceSchedule(s, cfg, asOf);
	const task = (id) => cfg.tasks.find((t) => t.id === id) || cfg.replacement;
	const overdue = rows.filter((r) => r.status === 'overdue');
	const next = rows.find((r) => r.status !== 'overdue') || rows[rows.length - 1];
	const typeLabel = cfg.types[v.type].label.toLowerCase();
	const table = {
		caption: `Maintenance schedule for a ${typeLabel} installed ${month(s.installed)}`,
		columns: [{ key: 'task', label: 'Task' }, { key: 'due', label: 'Next due' }, { key: 'every', label: 'How often' }, { key: 'status', label: 'Status' }],
		rows: rows.map((r) => ({ task: r.label, due: month(r.due), every: every(r.everyMonths), status: STATUS[r.status] })),
	};
	const hard = v.water === 'hard' && cfg.types[v.type].family === 'tank';
	const soft = v.water === 'softened' && cfg.types[v.type].family === 'tank';
	const notes = [
		hard ? 'On hard water A. O. Smith says to flush twice a year, so the flush comes every 6 months.' : '',
		soft ? task('anode').softenedNote : '',
		s.lastService ? 'Dates count from your last service (the 5-year professional valve check counts from install).' : 'With no last service date, each task counts from the install date; a task that was never done shows as overdue.',
	].filter(Boolean).join(' ');
	const headline = overdue.length
		? `${overdue.length} task${overdue.length > 1 ? 's are' : ' is'} overdue: ${overdue.map((r) => lc(r.label)).slice(0, 2).join(' and ')}${overdue.length > 2 ? ', and more' : ''}`
		: `Next: ${lc(next.label)} by ${month(next.due)}`;
	const explain = `${overdue.length ? `After that, the next job is ${lc(next.label)} by ${month(next.due)}. ` : ''}${notes} Every interval comes from the maker's manual; the table lists who should do each job.`;
	const formula = [
		`Installed ${month(s.installed)}${s.lastService ? `; last service ${month(s.lastService)}` : ''}; water: ${cfg.water[v.water]?.label || v.water}`,
		...rows.map((r) => {
			const t = task(r.id);
			if (r.id === cfg.replacement.id) return `${r.label}: install + ${cfg.lifespans[v.type][cfg.replacement.lifespanKey]} years (low end of the DOE lifespan range) = ${month(r.due)}`;
			const from = s.lastService && r.id !== 'tpPro' ? `last service + ${r.everyMonths} months` : `install + ${t.firstMonths} months`;
			return `${r.label}: ${from} = ${month(r.due)} (${t.publisher || 'maker'}: every ${r.everyMonths} months)`;
		}),
	].join('\n');
	const status = overdue.length ? 'warn' : 'ok';
	return { status, statusLabel: overdue.length ? 'Overdue' : 'On schedule', headline, explain, formula, table, result: { rows, overdue: overdue.length } };
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="water-heater-maintenance-schedule"]');
	const el = document.getElementById('hwm-ms-data');
	if (root && el) {
		const cfg = JSON.parse(el.textContent);
		const form = root.querySelector('form');
		let last = null;
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg, new Date());
			renderResult(root, last);
			const signup = document.querySelector('[data-signup="health"]');
			if (signup) for (const k of ['type', 'installYear', 'installMonth', 'water']) { const f = signup.querySelector(`[name="${k}"]`); if (f && values[k] !== null && values[k] !== undefined) f.value = String(values[k]); }
			if (record) {
				writeState(values);
				if (last.result) track('calc_result', { tool: 'water-heater-maintenance-schedule', unit_type: values.type, water: values.water, overdue: last.result.overdue > 0 ? 'yes' : 'no' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const fromUrl = readState(form);
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'water-heater-maintenance-schedule', referrer: document.referrer });
	}
}
