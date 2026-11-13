/**
 * tankless-vs-tank.js: cost of ownership of a tank, a tankless unit and a heat pump water heater (STRATEGY §3.6).
 * Installed cost comes from the same model as the cost calculator, running cost from the same model as the gas vs
 * electric tool (cost-model.js → formulas.js). Data: <script type="application/json" id="hwm-tco-data">.
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result.
 */
import { estimateInstalled, defaultExtras, placeFor, gallonsFor, operatingCosts, ownershipCompare } from './cost-model.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatCurrency, formatNumber } from './ui.js';

const usd = (v) => formatCurrency(v);
const lc = (s) => s.charAt(0).toLowerCase() + s.slice(1);

/** The three options compared for a fuel, with the scenario that prices each one. */
export function optionsFor(cfg, fuel) {
	return cfg.options[fuel] || cfg.options[cfg.defaults.fuel];
}

/**
 * @param {object} v Form values: fuel, household, state, years, kwh, therm.
 * @param {object} cfg Client data: { model, options, defaults }.
 */
export function describe(v, cfg) {
	const m = cfg.model;
	const fuel = cfg.options[v.fuel] ? v.fuel : cfg.defaults.fuel;
	const place = placeFor(m, v.state);
	const years = Number.isFinite(v.years) && v.years >= 1 && v.years <= 30 ? Math.round(v.years) : cfg.defaults.years;
	const prices = { usdPerKwh: Number.isFinite(v.kwh) && v.kwh > 0 ? v.kwh : place.usdPerKwh, usdPerTherm: Number.isFinite(v.therm) && v.therm > 0 ? v.therm : place.usdPerTherm };
	const gallons = gallonsFor(m, v.household);
	const op = operatingCosts(m, { gallonsPerDay: gallons, setpointF: m.setpointF, inletF: place.inletF, prices });
	const opById = Object.fromEntries(op.rows.map((r) => [r.id, r]));
	const opts = optionsFor(cfg, fuel).map((o) => {
		const est = estimateInstalled(m, { type: o.type, size: o.size, state: v.state, extras: defaultExtras(m, o.scenario, o.type) });
		return { id: o.type, label: o.label, installed: est.typical, annualOperating: opById[o.type].year, lifeYears: m.types[o.type].life, credit: m.types[o.type].credit };
	});
	const res = ownershipCompare(opts, years);
	const [tank, tankless, hp] = res;
	const cheapest = [...res].sort((a, b) => a.total - b.total)[0];
	const be = (o) => (o.breakEven ? `pays back its higher price in year ${o.breakEven}` : `does not pay back its higher price within ${years} years`);
	const rows = res.map((o) => ({ option: o.label, installed: usd(o.installed - o.credit), year: usd(o.annualOperating), repl: formatNumber(o.replacements), total: usd(o.total), be: o.breakEven ? `Year ${o.breakEven}` : o === tank ? '—' : 'No' }));
	const where = v.state && m.states[v.state] ? `in ${place.name}` : 'at U.S. average prices';
	const explain = [
		`Over ${years} years ${where}, the ${lc(tankless.label)} costs ${usd(tankless.total)} in total against ${usd(tank.total)} for the ${lc(tank.label)}, so it ${be(tankless)}.`,
		`The ${lc(hp.label)} totals ${usd(hp.total)} and ${be(hp)}.`,
		`Hot water use: ${gallons} gallons a day, heated by ${formatNumber(op.rise)} °F.`,
	].join(' ');
	const formula = [
		`Rise = ${m.setpointF} °F setpoint − ${formatNumber(place.inletF, 1)} °F incoming water = ${formatNumber(op.rise, 1)} °F`,
		...res.map((o) => `${o.label}: ${usd(o.installed)} installed${o.credit ? ` − ${usd(o.credit)} credit` : ''} + ${usd(o.annualOperating)}/yr × ${years} yr${o.replacements ? ` + ${o.replacements} replacement${o.replacements > 1 ? 's' : ''} × ${usd(o.installed)} (life ${o.lifeYears} yr)` : ''} = ${usd(o.total)}`),
		`Break-even = first year the option's running total is at or below the tank's.`,
		`Federal tax credit: ${usd(tankless.credit)} (25C ended for installs after 2025).`,
	].join('\n');
	return {
		status: cheapest.id === tank.id ? 'warn' : 'ok',
		statusLabel: `Cheapest over ${years} years: ${cheapest.label}`,
		headline: cheapest.id === tank.id ? `The ${lc(tank.label)} stays cheapest over ${years} years` : `${cheapest.label} saves ${usd(tank.total - cheapest.total)} over ${years} years`,
		explain,
		formula,
		table: { caption: `Cost of ownership over ${years} years`, columns: [{ key: 'option', label: 'Option' }, { key: 'installed', label: 'Installed', num: true }, { key: 'year', label: 'Running cost/yr', num: true }, { key: 'repl', label: 'Replacements', num: true }, { key: 'total', label: `${years}-year total`, num: true }, { key: 'be', label: 'Pays back', num: true }], rows },
		result: res,
	};
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="tankless-vs-tank-water-heater"]');
	const cfgEl = document.getElementById('hwm-tco-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const m = cfg.model;
		const form = root.querySelector('form');
		const stateEl = form.elements.namedItem('state');
		const kwhEl = form.elements.namedItem('kwh');
		const thermEl = form.elements.namedItem('therm');
		let last = null;
		for (const el of [kwhEl, thermEl]) el?.addEventListener('input', () => { el.dataset.edited = '1'; });
		stateEl?.addEventListener('change', () => {
			const p = placeFor(m, stateEl.value);
			if (kwhEl && !kwhEl.dataset.edited) kwhEl.value = String(p.usdPerKwh);
			if (thermEl && !thermEl.dataset.edited) thermEl.value = String(p.usdPerTherm);
		});
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				track('calc_result', { tool: 'tankless-vs-tank-water-heater', fuel: values.fuel, state: values.state, household: String(values.household), years: values.years });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const params = new URLSearchParams(window.location.search);
		const fromUrl = readState(form);
		if (fromUrl && params.has('state') && !params.has('kwh')) stateEl?.dispatchEvent(new Event('change'));
		if (params.has('kwh') && kwhEl) kwhEl.dataset.edited = '1';
		if (params.has('therm') && thermEl) thermEl.dataset.edited = '1';
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'tankless-vs-tank-water-heater', referrer: document.referrer });
	}
}
