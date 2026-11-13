/**
 * gas-vs-electric.js: yearly operating cost of five water heater types (STRATEGY §3.7). Math: formulas.js
 * (annualHeatBtu → annualEnergyUse → annualCost) through cost-model.js operatingCosts(), the same function the
 * tankless vs tank tool uses. Data: <script type="application/json" id="hwm-op-data">.
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result.
 */
import { PHYSICS, annualHeatBtu } from './formulas.js';
import { placeFor, gallonsFor, operatingCosts } from './cost-model.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatCurrency, formatNumber } from './ui.js';

const usd = (v) => formatCurrency(v);
const an = (s) => (/^[aeiou]/i.test(s) ? `an ${s}` : `a ${s}`);
const lc = (s) => s.charAt(0).toLowerCase() + s.slice(1);
const energy = (u) => (u.therms !== undefined ? `${formatNumber(u.therms)} therms` : `${formatNumber(u.kwh)} kWh`);

/**
 * @param {object} v Form values: household, state, setpoint, kwh, therm, uef_<type> for each type.
 * @param {object} cfg Client data: { model, defaults }.
 */
export function describe(v, cfg) {
	const m = cfg.model;
	const place = placeFor(m, v.state);
	const setpointF = Number.isFinite(v.setpoint) && v.setpoint >= 100 && v.setpoint <= 160 ? v.setpoint : m.setpointF;
	const prices = { usdPerKwh: Number.isFinite(v.kwh) && v.kwh > 0 ? v.kwh : place.usdPerKwh, usdPerTherm: Number.isFinite(v.therm) && v.therm > 0 ? v.therm : place.usdPerTherm };
	const uefs = Object.fromEntries(m.typeOrder.map((id) => [id, Number.isFinite(v[`uef_${id}`]) ? v[`uef_${id}`] : null]));
	const gallons = gallonsFor(m, v.household);
	const op = operatingCosts(m, { gallonsPerDay: gallons, setpointF, inletF: place.inletF, prices, uefs });
	const by = Object.fromEntries(op.rows.map((r) => [r.id, r]));
	const gas = by.gas_tank;
	const el = by.electric_tank;
	const sorted = [...op.rows].sort((a, b) => a.year - b.year);
	const where = v.state && m.states[v.state] ? `${place.name}` : 'U.S. average';
	const cheaperTank = gas.year <= el.year ? gas : el;
	const otherTank = cheaperTank === gas ? el : gas;
	const rows = op.rows.map((r) => ({ type: r.label, uef: formatNumber(r.uef, 2), use: energy(r.use), month: usd(r.month), year: usd(r.year) }));
	return {
		status: cheaperTank === gas ? 'ok' : 'warn',
		statusLabel: `Cheapest to run: ${sorted[0].label}`,
		headline: `Gas tank ${usd(gas.year)} a year vs electric tank ${usd(el.year)}`,
		explain: `With ${where} prices, ${an(lc(cheaperTank.label))} costs ${usd(otherTank.year - cheaperTank.year)} a year less to run than ${an(lc(otherTank.label))}. The cheapest type to run is ${an(lc(sorted[0].label))} at ${usd(sorted[0].year)} a year (${usd(sorted[0].month)} a month). Use: ${gallons} gallons a day heated by ${formatNumber(op.rise, 1)} °F.`,
		formula: [
			`Rise = ${formatNumber(setpointF)} °F − ${formatNumber(place.inletF, 1)} °F incoming = ${formatNumber(op.rise, 1)} °F`,
			`Heat per year = ${gallons} gal × ${PHYSICS.daysPerYear} × ${PHYSICS.lbPerGallon} lb × ${formatNumber(op.rise, 1)} °F = ${formatNumber(annualHeatBtu(gallons, op.rise))} BTU`,
			`Gas: therms = heat ÷ ${formatNumber(PHYSICS.btuPerTherm)} ÷ UEF; × $${prices.usdPerTherm}/therm`,
			`Electric: kWh = heat ÷ ${formatNumber(PHYSICS.btuPerKwh)} ÷ UEF; × $${prices.usdPerKwh}/kWh`,
			...op.rows.map((r) => `${r.label} (UEF ${formatNumber(r.uef, 2)}): ${energy(r.use)} → ${usd(r.year)}/yr`),
		].join('\n'),
		table: { caption: 'Yearly operating cost by type', columns: [{ key: 'type', label: 'Type' }, { key: 'uef', label: 'UEF', num: true }, { key: 'use', label: 'Energy/yr', num: true }, { key: 'month', label: 'Per month', num: true }, { key: 'year', label: 'Per year', num: true }], rows },
		rows: op.rows,
	};
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="gas-vs-electric-water-heater"]');
	const cfgEl = document.getElementById('hwm-op-data');
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
				track('calc_result', { tool: 'gas-vs-electric-water-heater', state: values.state, household: String(values.household) });
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
		if (window.self !== window.top) track('embed_view', { tool: 'gas-vs-electric-water-heater', referrer: document.referrer });
	}
}
