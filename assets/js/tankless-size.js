/**
 * tankless-size.js: tankless water heater size calculator (STRATEGY §3.2). Math is in formulas.js; data comes from the page
 * (<script type="application/json" id="hwm-tankless-data">, printed by PHP from data/computed/tankless-water-heater-size-calculator.json).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result, so the text is written once.
 */
import { tanklessSizing, PHYSICS } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber } from './ui.js';

const n0 = (v) => formatNumber(v, 0);
const n1 = (v) => formatNumber(v, 1);
export const FIXTURE_KEYS = ['shower', 'bathroomFaucet', 'kitchenFaucet', 'dishwasher', 'clothesWasher', 'tubFill'];

/**
 * @param {object} v Form values: state, inlet, setpoint, fuel, efficiency, lowflow, and one count per fixture key.
 * @param {object} cfg Client data (computed → client).
 * @returns {{status:(string|undefined), statusLabel:string, headline:string, explain:string, formula:string, sizing?:object}}
 */
export function describe(v, cfg) {
	const fuel = ['gas', 'propane', 'electric'].includes(v.fuel) ? v.fuel : 'gas';
	const setpoint = Number.isFinite(v.setpoint) ? v.setpoint : cfg.setpointF;
	const inlet = Number.isFinite(v.inlet) ? v.inlet : (cfg.states[v.state]?.coldestMonth ?? cfg.defaultInletF);
	const eff = Number.isFinite(v.efficiency) && v.efficiency > 0 && v.efficiency <= 1 ? v.efficiency : cfg.gasEfficiency.condensing;
	const fixtures = FIXTURE_KEYS.map((k) => ({ count: Math.max(0, Math.round(Number(v[k]) || 0)), gpm: v.lowflow ? cfg.fixtures[k].lowFlowGpm : cfg.fixtures[k].gpm }));
	if (!fixtures.some((f) => f.count > 0)) {
		return { statusLabel: 'Add fixtures', headline: 'Choose what runs at the same time', explain: 'Set how many showers, faucets and appliances you want to run at once in your busiest moment.', formula: '' };
	}
	if (setpoint <= inlet) {
		return { statusLabel: 'Check temperatures', headline: 'The hot water temperature must be above the incoming water', explain: `Incoming water is ${n0(inlet)} °F and the setpoint is ${n0(setpoint)} °F, so there is nothing to heat.`, formula: '' };
	}
	const s = tanklessSizing({ fixtures, setpointF: setpoint, inletF: inlet, gasEfficiency: eff, volts: cfg.volts, gasSizesBtuh: cfg.gasSizesBtuh, electricUnits: cfg.electricUnits });
	const cold = s.rise > cfg.rules.coldClimateRiseF && s.gpm > cfg.rules.coldClimateGpm;
	const gasText = s.gasSizeBtuh
		? `You need about ${n0(s.gasInputBtuh)} BTU/h of ${fuel === 'propane' ? 'propane' : 'gas'} input at UEF ${eff}. A ${n0(s.gasSizeBtuh)} BTU/h unit covers it.`
		: `You need about ${n0(s.gasInputBtuh)} BTU/h of ${fuel === 'propane' ? 'propane' : 'gas'} input at UEF ${eff}, more than one ${n0(s.largestGasBtuh)} BTU/h unit (the largest common home size). Plan for ${s.gasUnitsAtLargest} units, or run fewer fixtures at once.`;
	const elecText = s.electricUnit
		? `You need about ${n1(s.kw)} kW (${n0(s.amps)} A at ${cfg.volts} V). A ${n0(s.electricUnit.kw)} kW unit such as the ${s.electricUnit.model} covers it; that model lists ${s.electricUnit.breakers} breakers.`
		: `You need about ${n1(s.kw)} kW (${n0(s.amps)} A at ${cfg.volts} V), more than the largest ${n0(s.largestKw)} kW whole-house electric unit. It would take ${s.electricUnitsAtLargest} units, so gas, a tank or fewer fixtures at once are usually more practical.`;
	let status;
	let label;
	let explain;
	if (fuel === 'electric') {
		status = s.electricUnit ? (cold ? 'warn' : 'ok') : 'bad';
		label = s.electricUnit ? (cold ? 'Possible, but tight in winter' : 'One electric unit can do it') : 'Too much for one electric unit';
		explain = `${elecText}${cold ? ` With a ${n0(s.rise)} °F rise and more than ${cfg.rules.coldClimateGpm} GPM, a single electric unit is rarely enough in cold weather.` : ''} Gas instead: about ${n0(s.gasInputBtuh)} BTU/h.`;
	} else {
		status = s.gasSizeBtuh ? 'ok' : 'warn';
		label = s.gasSizeBtuh ? 'One unit can do it' : `Needs ${s.gasUnitsAtLargest} units or less flow`;
		explain = `${gasText} Electric instead: about ${n1(s.kw)} kW${s.electricUnit ? '' : `, more than one ${n0(s.largestKw)} kW unit`}.`;
	}
	const formula = [
		`Flow = ${fixtures.filter((f) => f.count).map((f) => `${f.count} × ${f.gpm}`).join(' + ')} = ${n1(s.gpm)} GPM`,
		`Rise = ${n0(setpoint)} °F − ${n1(inlet)} °F = ${n1(s.rise)} °F`,
		`Output = ${n1(s.gpm)} GPM × ${PHYSICS.lbPerGallon} lb/gal × ${PHYSICS.minutesPerHour} min × ${n1(s.rise)} °F = ${n0(s.outputBtuh)} BTU/h`,
		`Gas input = ${n0(s.outputBtuh)} ÷ ${eff} = ${n0(s.gasInputBtuh)} BTU/h`,
		`Electric = ${n0(s.outputBtuh)} ÷ ${n0(PHYSICS.btuPerKwh)} BTU per kWh = ${n1(s.kw)} kW; × ${n0(PHYSICS.wattsPerKw)} ÷ ${cfg.volts} V = ${n0(s.amps)} A`,
	].join('\n');
	return { status, statusLabel: label, headline: `${n1(s.gpm)} GPM at a ${n0(s.rise)} °F rise`, explain, formula, sizing: s };
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="tankless-water-heater-size-calculator"]');
	const cfgEl = document.getElementById('hwm-tankless-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const form = root.querySelector('form');
		const stateEl = form.elements.namedItem('state');
		const inletEl = form.elements.namedItem('inlet');
		const effEl = form.elements.namedItem('efficiency');
		const effField = root.querySelector('[data-field="efficiency"]');
		let last = null;
		const syncFuel = () => {
			const fuel = readInputs(form).fuel;
			if (effField) effField.hidden = fuel === 'electric';
		};
		stateEl?.addEventListener('change', () => {
			const st = cfg.states[stateEl.value];
			inletEl.value = st ? String(st.coldestMonth) : String(cfg.defaultInletF);
		});
		const run = (record) => {
			syncFuel();
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				if (last.sizing) track('calc_result', { tool: 'tankless-water-heater-size-calculator', fuel: values.fuel, state: values.state || 'none', gpm_bucket: last.sizing.gpm < 4 ? '<4' : last.sizing.gpm < 6 ? '4-6' : last.sizing.gpm < 8 ? '6-8' : '8+', rise_bucket: last.sizing.rise < 50 ? '<50' : last.sizing.rise < 70 ? '50-70' : '70+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		if (effEl && !effEl.value) effEl.value = String(cfg.gasEfficiency.condensing);
		const fromUrl = readState(form);
		if (fromUrl) {
			const params = new URLSearchParams(window.location.search);
			for (const el of form.querySelectorAll('input[type="checkbox"]')) if (!params.has(el.name)) el.checked = false;
		}
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'tankless-water-heater-size-calculator', referrer: document.referrer });
	}
}
