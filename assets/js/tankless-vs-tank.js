/**
 * tankless-vs-tank.js: cost of ownership of a tank, a tankless unit and a heat pump water heater (STRATEGY §3.6).
 * Installed cost comes from the same model as the cost calculator, running cost from the same model as the gas vs
 * electric tool (cost-model.js → formulas.js). Data: <script type="application/json" id="hwm-tco-data">.
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result.
 */
import { estimateInstalled, defaultExtras, placeFor, gallonsFor, operatingCosts, ownershipCompare } from './cost-model.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatCurrency, formatNumber, checkLimits, invalidResult } from './ui.js';

const usd = (v) => formatCurrency(v);
const lc = (s) => s.charAt(0).toLowerCase() + s.slice(1);

/** The three options compared for a fuel, with the scenario that prices each one. Propane uses the gas options. */
export function optionsFor(cfg, fuel) {
	const list = cfg.options[fuel === 'propane' ? 'gas' : fuel] || cfg.options[cfg.defaults.fuel];
	return fuel === 'propane' ? list.map((o) => ({ ...o, label: o.label.replace(/^Gas/, 'Propane') })) : list;
}

/**
 * @param {object} v Form values: fuel, household, state, years, kwh, therm.
 * @param {object} cfg Client data: { model, options, defaults }.
 */
export function describe(v, cfg) {
	const m = cfg.model;
	const fuel = (cfg.options[v.fuel] || v.fuel === 'propane') ? v.fuel : cfg.defaults.fuel;
	const bad = checkLimits(v, cfg.limits, fuel === 'propane' ? [] : ['propane']);
	if (bad.length) return { ...invalidResult(bad), result: null };
	const place = placeFor(m, v.state);
	const years = Number.isFinite(v.years) ? Math.round(v.years) : cfg.defaults.years;
	const prices = { usdPerKwh: Number.isFinite(v.kwh) ? v.kwh : place.usdPerKwh, usdPerTherm: Number.isFinite(v.therm) ? v.therm : place.usdPerTherm, usdPerGallonPropane: Number.isFinite(v.propane) ? v.propane : m.propane.usdPerGallon };
	const gallons = gallonsFor(m, v.household);
	const op = operatingCosts(m, { gallonsPerDay: gallons, setpointF: m.setpointF, inletF: place.inletF, prices, gasFuel: fuel === 'propane' ? 'propane' : 'natural' });
	const opById = Object.fromEntries(op.rows.map((r) => [r.id, r]));
	const opts = optionsFor(cfg, fuel).map((o) => {
		const est = estimateInstalled(m, { type: o.type, size: o.size, state: v.state, extras: defaultExtras(m, o.scenario, o.type) });
		const r = opById[o.type];
		// Running cost = energy + sourced yearly maintenance (descaling for tankless, anode for tanks; audit F-03 / S-2).
		return { id: o.type, label: o.label, installed: est.typical, energy: r.year, maintenance: r.maintenance, annualOperating: Math.round((r.year + r.maintenance) * 100) / 100, lifeYears: m.types[o.type].life, credit: m.types[o.type].credit };
	});
	const res = ownershipCompare(opts, years);
	const [tank, tankless, hp] = res;
	const cheapest = [...res].sort((a, b) => a.total - b.total)[0];
	const yrs = (p) => `${formatNumber(p, 1)} years`;
	const be = (o) => {
		if (o.payback === 0) return 'costs no more up front, so it is ahead from day one';
		if (o.payback === null) return 'never pays back its higher price, because it does not cost less to run';
		return o.breakEven ? `pays back its higher price in about ${yrs(o.payback)} (simple payback)` : `would take about ${yrs(o.payback)} to pay back its higher price, longer than ${years} years`;
	};
	const pb = (o) => (o === tank ? '—' : o.payback === 0 ? 'At once' : o.payback === null ? 'Never' : yrs(o.payback));
	const rows = res.map((o) => ({ option: o.label, installed: usd(o.installed - o.credit), year: usd(o.annualOperating), repl: formatNumber(o.replacements), total: usd(o.total), be: pb(o) }));
	const where = v.state && m.states[v.state] ? `in ${place.name}` : 'at U.S. average prices';
	const explain = [
		`Over ${years} years ${where}, the ${lc(tankless.label)} costs ${usd(tankless.total)} in total against ${usd(tank.total)} for the ${lc(tank.label)}, so it ${be(tankless)}.`,
		tankless.total < tank.total && !tankless.breakEven && tankless.payback ? `It still comes out ahead over ${years} years only because it has more life left at the end (${usd(tankless.residual)} of value left against ${usd(tank.residual)}); in cash spent so far it is ${usd(tankless.spent)} against ${usd(tank.spent)}.` : '',
		`The ${lc(hp.label)} totals ${usd(hp.total)} and ${be(hp)}.`,
		`Hot water use: ${gallons} gallons a day, heated by ${formatNumber(op.rise)} °F.`,
	].filter(Boolean).join(' ');
	const formula = [
		`Rise = ${m.setpointF} °F setpoint − ${formatNumber(place.inletF, 1)} °F incoming water = ${formatNumber(op.rise, 1)} °F`,
		...res.map((o) => `${o.label}: running cost ${usd(o.energy)} energy + ${usd(o.maintenance)} upkeep = ${usd(o.annualOperating)}/yr`),
		...res.map((o) => `${o.label}: ${usd(o.installed)} installed${o.credit ? ` − ${usd(o.credit)} credit` : ''} + ${usd(o.annualOperating)}/yr × ${years} yr${o.replacements ? ` + ${o.replacements} replacement${o.replacements > 1 ? 's' : ''} × ${usd(o.installed)} (life ${o.lifeYears} yr)` : ''}${o.residual ? ` − ${usd(o.residual)} value left (${o.remainingYears} of ${o.lifeYears} years of life remain)` : ''} = ${usd(o.total)}`),
		...res.slice(1).map((o) => `${o.label} payback = (${usd(o.installed - o.credit)} − ${usd(tank.installed - tank.credit)}) ÷ (${usd(tank.annualOperating)} − ${usd(o.annualOperating)}) = ${o.payback === null ? 'never (no yearly saving)' : o.payback === 0 ? '0 (no extra cost)' : yrs(o.payback)}`),
		'Payback = extra up-front cost ÷ yearly saving, no interest. Value left = installed price × remaining life ÷ life, so a unit bought near the end is not counted as used up.',
		`Federal tax credit: ${usd(tankless.credit)} (25C ended for installs after 2025).`,
	].join('\n');
	return {
		status: cheapest.id === tank.id ? 'warn' : 'ok',
		statusLabel: `Cheapest over ${years} years: ${cheapest.label}`,
		headline: cheapest.id === tank.id ? `The ${lc(tank.label)} stays cheapest over ${years} years` : `${cheapest.label} saves ${usd(tank.total - cheapest.total)} over ${years} years`,
		explain,
		formula,
		table: { caption: `Cost of ownership over ${years} years`, columns: [{ key: 'option', label: 'Option' }, { key: 'installed', label: 'Installed', num: true }, { key: 'year', label: 'Running cost/yr', num: true }, { key: 'repl', label: 'Replacements', num: true }, { key: 'total', label: `${years}-year total`, num: true }, { key: 'be', label: 'Simple payback', num: true }], rows },
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
				if (last.result) track('calc_result', { tool: 'tankless-vs-tank-water-heater', fuel: values.fuel, state: values.state, household: String(values.household), years: values.years });
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
