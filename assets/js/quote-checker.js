/**
 * quote-checker.js: is a water heater installation quote fair? Compares a quote total (and any line items) with the
 * cost model's low / typical / high price for the same job in the same state (cost-model.js estimateInstalled), and
 * rates it with the bands in data/quote-check.json. Math is in formulas.js (quoteBand, lineBand).
 * Data: <script id="hwm-cost-data"> (the shared cost model, printed by parts/cost-inputs.php) and
 * <script id="hwm-qc-data"> (data/computed/water-heater-quote-checker.json → client). describe() is also used by
 * scripts/build-data.mjs for the server-rendered default result.
 */
import { estimateInstalled, defaultExtras } from './cost-model.js';
import { quoteBand, lineBand } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatCurrency, formatNumber, checkLimits, invalidResult } from './ui.js';

const usd = (v) => formatCurrency(v);
const range = (r) => `${usd(r.low)} to ${usd(r.high)}`;

/**
 * @param {object} v Form values: type, size, state, scenario, one boolean per extra id, total, unitPrice, laborPrice, permitPrice ($).
 * @param {object} cfg { model, bands, farAboveShare, lineBands, limits, defaults }
 */
export function describe(v, cfg) {
	const bad = checkLimits(v, cfg.limits);
	if (bad.length) return { ...invalidResult(bad), result: null };
	const m = cfg.model;
	const type = m.types[v.type] ? v.type : cfg.defaults.type;
	const extras = Object.keys(m.extras).filter((id) => v[id]);
	const est = estimateInstalled(m, { type, size: v.size, state: v.state, extras });
	const where = v.state && m.states[v.state] ? `in ${m.states[v.state].name}` : 'at U.S. average prices';
	const job = `${est.unit.label.replace(/ \(.*\)$/, '').toLowerCase()} ${where}`;
	const item = (id) => est.items.find((i) => i.id === id);

	const lines = [];
	const addLine = (label, amount, r, scaled) => {
		if (!Number.isFinite(amount)) return;
		const b = lineBand(amount, r);
		lines.push({ label, amount, r, b, scaled });
	};
	addLine('Unit (the water heater itself)', v.unitPrice, est.unit, false);
	addLine('Installation labor', v.laborPrice, item('labor'), true);
	if (item('permit')) addLine('Permit and haul-away', v.permitPrice, item('permit'), true);

	const rows = est.items.map((i) => {
		const l = lines.find((x) => x.r === (i.id === 'unit' ? est.unit : i));
		return { item: i.label, quote: l ? usd(l.amount) : '–', ours: range(i), verdict: l ? cfg.lineBands[l.b] : '–' };
	});
	const total = Number.isFinite(v.total) ? v.total : null;
	const tTotal = { item: 'Total', quote: total === null ? '–' : usd(total), ours: `${range(est)} (typical ${usd(est.typical)})`, verdict: '' };
	const table = { caption: 'Your quote against our estimate for the same job', columns: [{ key: 'item', label: 'Item' }, { key: 'quote', label: 'Your quote', num: true }, { key: 'ours', label: 'Our range', num: true }, { key: 'verdict', label: 'Verdict' }], rows, total: tTotal };

	const formulaBase = [
		`Our estimate = unit + labor × ${formatNumber(est.multiplier, 3)} (state price factor) + each ticked extra × ${formatNumber(est.multiplier, 3)}`,
		`Low ${usd(est.low)} · typical ${usd(est.typical)} · high ${usd(est.high)} for a ${job}`,
	];
	if (total === null) {
		return { statusLabel: 'Enter your quote', headline: `Our range for this job: ${range(est)}`, explain: `For a ${job} with ${extras.length ? `${extras.length} extra${extras.length > 1 ? 's' : ''} ticked` : 'no extras'}, we estimate ${usd(est.typical)} installed. Type the total on your quote to see where it falls, and add the line items if the quote lists them.`, formula: formulaBase.join('\n'), table, result: { est, band: null } };
	}
	const qb = quoteBand(total, est, cfg.farAboveShare);
	const band = cfg.bands.find((b) => b.id === qb.band);
	const pct = Math.round(Math.abs(qb.vsTypical) * 100);
	tTotal.verdict = band.label;
	const vs = pct === 0 ? 'the same as our typical price' : `${pct}% ${qb.vsTypical > 0 ? 'above' : 'below'} our typical ${usd(est.typical)}`;
	const lineText = lines.filter((l) => l.b !== 'in').map((l) => `${l.label.toLowerCase()} at ${usd(l.amount)} is ${cfg.lineBands[l.b]} (${range(l.r)})`);
	const explain = [
		`Your quote of ${usd(total)} is ${vs} for a ${job}.`,
		band.advice,
		lineText.length ? `Line items: ${lineText.join('; ')}.` : lines.length ? 'Every line you entered is inside our range.' : '',
		extras.length ? '' : 'Tick what the quote includes (permit, expansion tank, code items) so we compare like with like.',
	].filter(Boolean).join(' ');
	const formula = [
		...formulaBase,
		`Your quote ${usd(total)} ÷ typical ${usd(est.typical)} − 1 = ${qb.vsTypical >= 0 ? '+' : '−'}${pct}%`,
		`Bands: below ${usd(est.low)} = below range; up to ${usd(est.typical)} = fair, low side; up to ${usd(est.high)} = fair, high side; up to ${usd(est.high * (1 + cfg.farAboveShare))} (high + ${Math.round(cfg.farAboveShare * 100)}%, our rule) = above range; more = far above`,
		...lines.map((l) => `${l.label}: ${usd(l.amount)} vs ${range(l.r)}${l.scaled ? ' (state-scaled)' : ' (national listing prices)'} → ${cfg.lineBands[l.b]}`),
	].join('\n');
	return { status: band.status, statusLabel: band.label, headline: `${usd(total)} is ${band.label.charAt(0).toLowerCase()}${band.label.slice(1)}`, explain, formula, table, result: { est, band: qb.band, vsTypical: qb.vsTypical } };
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="water-heater-quote-checker"]');
	const qcEl = document.getElementById('hwm-qc-data');
	const costEl = document.getElementById('hwm-cost-data');
	if (root && qcEl && costEl) {
		const cfg = { ...JSON.parse(qcEl.textContent), model: JSON.parse(costEl.textContent).model };
		const m = cfg.model;
		const form = root.querySelector('form');
		const typeEl = form.elements.namedItem('type');
		const sizeEl = form.elements.namedItem('size');
		const scenEl = form.elements.namedItem('scenario');
		let last = null;
		const fillSizes = () => {
			const t = m.types[typeEl.value];
			if (!t || !sizeEl) return;
			const keep = sizeEl.value;
			sizeEl.replaceChildren(...t.sizes.map((s) => new Option(s.label, s.id)));
			sizeEl.value = t.sizes.some((s) => s.id === keep) ? keep : t.defaultSize;
		};
		const tickDefaults = () => {
			const on = defaultExtras(m, scenEl.value, typeEl.value);
			for (const id of Object.keys(m.extras)) {
				const el = form.elements.namedItem(id);
				if (el) el.checked = on.includes(id);
			}
		};
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				if (!last.result || !last.result.band) return;
				track('calc_result', { tool: 'water-heater-quote-checker', unit_type: values.type, state: values.state, band: last.result.band, vs_typical_bucket: last.result.vsTypical < -0.2 ? '<-20%' : last.result.vsTypical < 0 ? '-20-0%' : last.result.vsTypical < 0.2 ? '0-20%' : last.result.vsTypical < 0.5 ? '20-50%' : '50%+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		typeEl?.addEventListener('change', () => { fillSizes(); tickDefaults(); });
		scenEl?.addEventListener('change', tickDefaults);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const params = new URLSearchParams(window.location.search);
		const fromUrl = readState(form);
		if (fromUrl) {
			fillSizes();
			if (params.has('size') && sizeEl) sizeEl.value = params.get('size');
			const anyExtra = Object.keys(m.extras).some((id) => params.has(id));
			if (anyExtra) for (const id of Object.keys(m.extras)) { const el = form.elements.namedItem(id); if (el) el.checked = params.has(id); }
			else tickDefaults();
		}
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'water-heater-quote-checker', referrer: document.referrer });
	}
}
