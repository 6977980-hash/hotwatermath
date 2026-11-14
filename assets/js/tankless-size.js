/**
 * tankless-size.js: tankless water heater size calculator (STRATEGY §3.2). Math is in formulas.js; data comes from the page
 * (<script type="application/json" id="hwm-tankless-data">, printed by PHP from data/computed/tankless-water-heater-size-calculator.json).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result, so the text is written once.
 */
import { tanklessSizing, pointOfUseSizing, PHYSICS } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber, checkLimits, invalidResult } from './ui.js';

const n0 = (v) => formatNumber(v, 0);
const n1 = (v) => formatNumber(v, 1);
const n2 = (v) => formatNumber(v, 2);
export const FIXTURE_KEYS = ['shower', 'bathroomFaucet', 'kitchenFaucet', 'dishwasher', 'clothesWasher', 'tubFill', 'soakingTub'];

/** Input limits of this tool, with the one "count" rule applied to every fixture field. */
function limitsFor(cfg) {
	const { count, ...rest } = cfg.limits || {};
	return { ...rest, ...(count ? Object.fromEntries(FIXTURE_KEYS.map((k) => [k, { ...count, label: `${cfg.fixtures[k].label} count` }])) : {}) };
}

/** Point-of-use mode: one fixture, electric, sized to kW and amps (audit suggestion S-8). */
function describePou(v, cfg, setpoint, inlet) {
	const key = cfg.pou.fixtures.includes(v.pouFixture) ? v.pouFixture : cfg.pou.fixtures[0];
	const fx = cfg.fixtures[key];
	const gpm = Number.isFinite(v.pouGpm) ? v.pouGpm : fx.gpm;
	const use = Number.isFinite(fx.useTempF) && fx.useTempF < setpoint ? fx.useTempF : setpoint;
	const units = [...cfg.pou.units, ...cfg.electricUnits.map((u) => ({ ...u, volts: cfg.volts, maker: 'Rheem' }))];
	if (use <= inlet) {
		return { statusLabel: 'Check temperatures', headline: 'The incoming water is already as warm as the tap needs', explain: `Incoming water is ${n0(inlet)} °F and this fixture is used at about ${n0(use)} °F.`, formula: '' };
	}
	const p = pointOfUseSizing({ gpm, useTempF: use, inletF: inlet, units });
	const name = fx.label.toLowerCase();
	const unitText = p.unit
		? `The smallest listed unit with that power is the ${p.unit.maker} ${p.unit.model} (${n1(p.unit.kw)} kW, ${p.unit.volts} V, ${n0(p.unit.amps)} A${p.unit.breakers ? `, ${p.unit.breakers} breakers` : ''}${p.unit.wire ? `, ${p.unit.wire} wire` : ''}).`
		: `That is more than the largest unit we list (${n0(p.largestKw)} kW), so lower the flow or use a tank or gas unit.`;
	const amps240 = (p.kw * PHYSICS.wattsPerKw) / cfg.volts;
	return {
		status: p.unit ? (p.unit.kw >= cfg.electricUnits[0].kw ? 'warn' : 'ok') : 'bad',
		statusLabel: p.unit ? (p.unit.kw >= cfg.electricUnits[0].kw ? 'Needs a whole-house size unit' : 'One point-of-use unit can do it') : 'Too much for one electric unit',
		headline: `About ${n1(p.kw)} kW for one ${name} at ${n1(gpm)} GPM`,
		explain: `A point-of-use heater warms the ${name}'s whole flow from ${n0(inlet)} °F straight to about ${n0(use)} °F, a ${n0(p.rise)} °F rise. That takes about ${n1(p.kw)} kW (${n0(amps240)} A at ${cfg.volts} V). ${unitText} The electrician and the unit's manual set the real breaker and wire.`,
		formula: [
			`Rise = ${n0(use)} °F at the tap − ${n1(inlet)} °F incoming = ${n1(p.rise)} °F (no mixing: the unit heats the flow to use temperature)`,
			`Output = ${n2(gpm)} GPM × ${PHYSICS.lbPerGallon} lb/gal × ${PHYSICS.minutesPerHour} min × ${n1(p.rise)} °F = ${n0(p.outputBtuh)} BTU/h`,
			`Power = ${n0(p.outputBtuh)} ÷ ${n0(PHYSICS.btuPerKwh)} BTU per kWh = ${n2(p.kw)} kW (Eemax's rule: GPM × rise ÷ 6.83)`,
			p.unit ? `Smallest listed unit with at least ${n2(p.kw)} kW: ${p.unit.model}, ${n1(p.unit.kw)} kW` : `No listed unit reaches ${n2(p.kw)} kW`,
		].join('\n'),
		pou: { ...p, gpm, fixture: key },
	};
}

/**
 * @param {object} v Form values: mode ('whole'|'pou'), state, inlet, setpoint, fuel, efficiency, lowflow, showerGpm,
 *   one count per fixture key; in point-of-use mode pouFixture and pouGpm.
 * @param {object} cfg Client data (computed → client).
 * @returns {{status:(string|undefined), statusLabel:string, headline:string, explain:string, formula:string, sizing?:object, invalid?:Array}}
 */
export function describe(v, cfg) {
	const fuel = ['gas', 'propane', 'electric'].includes(v.fuel) ? v.fuel : 'gas';
	const mode = v.mode === 'pou' ? 'pou' : 'whole';
	const skip = mode === 'pou' ? ['efficiency', 'showerGpm', ...FIXTURE_KEYS] : ['pouGpm', ...(fuel === 'electric' ? ['efficiency'] : [])];
	const bad = checkLimits(v, limitsFor(cfg), skip);
	if (bad.length) return invalidResult(bad);
	const setpoint = Number.isFinite(v.setpoint) ? v.setpoint : cfg.setpointF;
	const inlet = Number.isFinite(v.inlet) ? v.inlet : (cfg.states[v.state]?.coldestMonth ?? cfg.defaultInletF);
	if (mode === 'pou') return describePou(v, cfg, setpoint, inlet);
	const eff = Number.isFinite(v.efficiency) ? v.efficiency : cfg.gasEfficiency.condensing;
	const showerGpm = Number.isFinite(v.showerGpm) ? v.showerGpm : null;
	const fixtures = FIXTURE_KEYS.map((k) => ({
		key: k,
		count: Math.max(0, Math.round(Number(v[k]) || 0)),
		gpm: k === 'shower' && showerGpm !== null ? showerGpm : v.lowflow ? cfg.fixtures[k].lowFlowGpm : cfg.fixtures[k].gpm,
		useTempF: cfg.fixtures[k].useTempF,
	}));
	if (!fixtures.some((f) => f.count > 0)) {
		return { statusLabel: 'Add fixtures', headline: 'Choose what runs at the same time', explain: 'Set how many showers, faucets and appliances you want to run at once in your busiest moment.', formula: '' };
	}
	if (setpoint <= inlet) {
		return { status: 'bad', statusLabel: 'Check temperatures', headline: 'The hot water temperature must be above the incoming water', explain: `Incoming water is ${n0(inlet)} °F and the setpoint is ${n0(setpoint)} °F, so there is nothing to heat.`, formula: '', invalid: [{ name: 'setpoint', label: 'Water heater setting', message: 'The setting must be above the incoming water temperature.' }] };
	}
	const s = tanklessSizing({ fixtures, setpointF: setpoint, inletF: inlet, gasEfficiency: eff, volts: cfg.volts, gasSizesBtuh: cfg.gasSizesBtuh, electricUnits: cfg.electricUnits });
	const cold = s.rise > cfg.rules.coldClimateRiseF && s.gpm > cfg.rules.coldClimateGpm;
	const gasText = s.gasSizeBtuh
		? `You need about ${n0(s.gasInputBtuh)} BTU/h of ${fuel === 'propane' ? 'propane' : 'gas'} input at UEF ${eff}. A ${n0(s.gasSizeBtuh)} BTU/h unit covers it.`
		: `You need about ${n0(s.gasInputBtuh)} BTU/h of ${fuel === 'propane' ? 'propane' : 'gas'} input at UEF ${eff}, more than one ${n0(s.largestGasBtuh)} BTU/h unit (the largest common home size). Plan for ${s.gasUnitsAtLargest} units, or run fewer fixtures at once.`;
	const elecText = s.electricUnit
		? `You need about ${n1(s.kw)} kW (${n0(s.amps)} A at ${cfg.volts} V). A ${n0(s.electricUnit.kw)} kW unit such as the ${s.electricUnit.model} covers it; that model lists ${s.electricUnit.breakers} breakers.`
		: `You need about ${n1(s.kw)} kW (${n0(s.amps)} A at ${cfg.volts} V), more than the largest ${n0(s.largestKw)} kW whole-house electric unit. It would take ${s.electricUnitsAtLargest} units, so gas, a tank or fewer fixtures at once are usually more practical.`;
	const mixed = s.flowRows.filter((r) => r.share < 1);
	const mixText = mixed.length
		? `Your fixtures deliver ${n1(s.mixedGpm)} GPM. Showers, sinks and tubs run at about ${[...new Set(mixed.map((r) => n0(r.useTempF)))].join(' to ')} °F, so they take only part of it from the heater: ${n1(s.gpm)} GPM of ${n0(setpoint)} °F water in all.`
		: `Your fixtures draw ${n1(s.gpm)} GPM straight from the heater.`;
	let status;
	let label;
	let explain;
	if (fuel === 'electric') {
		status = s.electricUnit ? (cold ? 'warn' : 'ok') : 'bad';
		label = s.electricUnit ? (cold ? 'Possible, but tight in winter' : 'One electric unit can do it') : 'Too much for one electric unit';
		explain = `${mixText} ${elecText}${cold ? ` With a ${n0(s.rise)} °F rise and more than ${cfg.rules.coldClimateGpm} GPM, a single electric unit is rarely enough in cold weather.` : ''} Gas instead: about ${n0(s.gasInputBtuh)} BTU/h.`;
	} else {
		status = s.gasSizeBtuh ? 'ok' : 'warn';
		label = s.gasSizeBtuh ? 'One unit can do it' : `Needs ${s.gasUnitsAtLargest} units or less flow`;
		explain = `${mixText} ${gasText} Electric instead: about ${n1(s.kw)} kW${s.electricUnit ? '' : `, more than one ${n0(s.largestKw)} kW unit`}.`;
	}
	const used = fixtures.filter((f) => f.count > 0);
	const hotPart = (r) => (r.share < 1 ? `${r.count} × ${n2(r.gpm)} × ${formatNumber(r.share, 3)}` : `${r.count} × ${n2(r.gpm)}`);
	const formula = [
		`Mixed flow at the fixtures = ${used.map((f) => `${f.count} × ${n2(f.gpm)}`).join(' + ')} = ${n2(s.mixedGpm)} GPM`,
		...s.flowRows.filter((r) => r.share < 1).map((r) => `Hot share at ${n0(r.useTempF)} °F use = (${n0(r.useTempF)} − ${n1(inlet)}) ÷ (${n0(setpoint)} − ${n1(inlet)}) = ${formatNumber(r.share, 3)}`),
		`Hot flow from the heater = ${s.flowRows.map(hotPart).join(' + ')} = ${n2(s.gpm)} GPM`,
		`Rise = ${n0(setpoint)} °F − ${n1(inlet)} °F = ${n1(s.rise)} °F`,
		`Output = ${n2(s.gpm)} GPM × ${PHYSICS.lbPerGallon} lb/gal × ${PHYSICS.minutesPerHour} min × ${n1(s.rise)} °F = ${n0(s.outputBtuh)} BTU/h`,
		`Gas input = ${n0(s.outputBtuh)} ÷ ${eff} = ${n0(s.gasInputBtuh)} BTU/h`,
		`Electric = ${n0(s.outputBtuh)} ÷ ${n0(PHYSICS.btuPerKwh)} BTU per kWh = ${n1(s.kw)} kW; × ${n0(PHYSICS.wattsPerKw)} ÷ ${cfg.volts} V = ${n0(s.amps)} A`,
	].join('\n');
	return { status, statusLabel: label, headline: `${n1(s.gpm)} GPM of hot water at a ${n0(s.rise)} °F rise`, explain, formula, sizing: s };
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
			const v = readInputs(form);
			const pou = v.mode === 'pou';
			if (effField) effField.hidden = v.fuel === 'electric' || pou;
			for (const el of root.querySelectorAll('[data-mode]')) el.hidden = el.dataset.mode !== (pou ? 'pou' : 'whole');
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
				if (last.pou) track('calc_result', { tool: 'tankless-water-heater-size-calculator', mode: 'pou', fixture: last.pou.fixture, kw_bucket: last.pou.kw < 5 ? '<5' : last.pou.kw < 13 ? '5-13' : '13+' });
				if (last.sizing) track('calc_result', { tool: 'tankless-water-heater-size-calculator', mode: 'whole', fuel: values.fuel, state: values.state || 'none', gpm_bucket: last.sizing.gpm < 4 ? '<4' : last.sizing.gpm < 6 ? '4-6' : last.sizing.gpm < 8 ? '6-8' : '8+', rise_bucket: last.sizing.rise < 50 ? '<50' : last.sizing.rise < 70 ? '50-70' : '70+' });
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
