/**
 * expansion-tank.js: thermal expansion tank size for a storage water heater (STRATEGY §3.8, page 16).
 * Math: formulas.js expansionTankSizing (AMTROL's published method). Data: <script type="application/json" id="hwm-et-data">
 * (data/computed/water-heater-expansion-tank-size.json → client). describe() is also used by scripts/build-data.mjs.
 */
import { expansionTankSizing } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber } from './ui.js';

const gal = (v, d = 1) => `${formatNumber(v, d)} gal`;

/**
 * @param {object} v Form values: gallons (heater), temp (°F setting), supply (psi), max (psi).
 * @param {object} cfg Client data: { data: {expansionFactors, atmosphericPsi, tanks, headroomShare}, defaults, highSupplyPsi, prvLow, prvHigh }.
 */
export function describe(v, cfg) {
	const d = cfg.defaults;
	const pick = (x, lo, hi, def) => (Number.isFinite(x) && x >= lo && x <= hi ? x : def);
	const i = {
		heaterGallons: pick(v.gallons, 1, 200, d.heaterGallons),
		tempF: pick(v.temp, 100, 180, d.tempF),
		supplyPsi: pick(v.supply, 10, 150, d.supplyPsi),
		maxPsi: pick(v.max, 50, 160, d.maxPsi),
	};
	const r = expansionTankSizing(i, cfg.data);
	const formula = [
		`Expansion factor at ${formatNumber(i.tempF)} °F (from ${cfg.data.expansionFactors.startTempF} °F) = ${formatNumber(r.factor, 4)} (${formatNumber(r.factor * 100, 2)}% of the water)`,
		`Expanded water = ${formatNumber(i.heaterGallons)} gal × ${formatNumber(r.factor, 4)} = ${formatNumber(r.expanded, 2)} gal`,
		r.dpf === null
			? `Design pressure factor: not possible, the maximum (${i.maxPsi} psi) must be above the supply pressure (${i.supplyPsi} psi)`
			: `Design pressure factor = (${i.maxPsi} + ${cfg.data.atmosphericPsi}) ÷ (${i.maxPsi} − ${i.supplyPsi}) = ${formatNumber(r.dpf, 2)}`,
		r.required === null ? '' : `Tank volume needed = ${formatNumber(r.expanded, 2)} gal × ${formatNumber(r.dpf, 2)} = ${formatNumber(r.required, 2)} gal`,
		r.tank ? `Smallest tank with total ≥ ${formatNumber(r.required, 2)} gal and acceptance ≥ ${formatNumber(r.expanded, 2)} gal: ${r.tank.label} (${gal(r.tank.totalGallons)} total, ${gal(r.tank.acceptanceGallons)} acceptance)` : '',
		r.safer ? `Within ${formatNumber(cfg.data.headroomShare * 100)}% of that tank's limit, so the next size is the safer pick: ${r.safer.label}` : '',
		`Set the tank's air precharge to your supply pressure: ${formatNumber(i.supplyPsi)} psi`,
	].filter(Boolean).join('\n');
	const rows = cfg.data.tanks.map((t) => ({ tank: t.label, total: gal(t.totalGallons), accept: gal(t.acceptanceGallons), fits: r.required !== null && t.totalGallons >= r.required && t.acceptanceGallons >= r.expanded ? 'Yes' : 'No' }));
	const table = { caption: 'Which tank sizes are big enough', columns: [{ key: 'tank', label: 'Tank' }, { key: 'total', label: 'Total volume', num: true }, { key: 'accept', label: 'Max. acceptance', num: true }, { key: 'fits', label: 'Big enough' }], rows };
	const high = i.supplyPsi > cfg.highSupplyPsi ? ` Your supply pressure is above ${cfg.highSupplyPsi} psi; A. O. Smith advises a pressure-reducing valve set to ${cfg.prvLow} to ${cfg.prvHigh} psi.` : '';
	if (r.dpf === null) {
		return { status: 'bad', statusLabel: 'Check the pressures', headline: 'The maximum pressure must be above your supply pressure', explain: `The tank can only absorb expansion if pressure is allowed to rise above the ${formatNumber(i.supplyPsi)} psi supply. Use your relief valve setting (often 150 psi) as the maximum.`, formula, table, result: { ...r, input: i } };
	}
	if (r.tooBig) {
		return { status: 'bad', statusLabel: 'Larger than a home tank', headline: `Needs ${gal(r.required)} of tank volume`, explain: `That is more than the largest tank in our list. Ask the tank maker or a plumbing designer to size it, or lower the supply pressure with a pressure-reducing valve.${high}`, formula, table, result: { ...r, input: i } };
	}
	const pickTank = r.safer || r.tank;
	return {
		status: r.safer || high ? 'warn' : 'ok',
		statusLabel: r.safer ? 'Close to the limit: go one size up' : 'Minimum tank size',
		headline: r.safer ? `A ${r.tank.label} is the minimum; a ${r.safer.label} is safer` : `A ${r.tank.label} is enough`,
		explain: `Heating ${formatNumber(i.heaterGallons)} gallons from ${cfg.data.expansionFactors.startTempF} °F to ${formatNumber(i.tempF)} °F adds about ${gal(r.expanded, 2)} of water. With ${formatNumber(i.supplyPsi)} psi supply and a ${formatNumber(i.maxPsi)} psi maximum, the tank needs ${gal(r.required, 2)} of total volume. Choose a ${pickTank.label} and set its precharge to ${formatNumber(i.supplyPsi)} psi.${high}`,
		formula,
		table,
		result: { ...r, input: i },
	};
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="water-heater-expansion-tank-size"]');
	const cfgEl = document.getElementById('hwm-et-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const form = root.querySelector('form');
		let last = null;
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				const g = last.result.input.heaterGallons;
				track('calc_result', { tool: 'water-heater-expansion-tank-size', heater_bucket: g <= 40 ? '<=40' : g <= 50 ? '41-50' : g <= 80 ? '51-80' : '80+', supply_bucket: last.result.input.supplyPsi <= 50 ? '<=50' : last.result.input.supplyPsi <= 80 ? '51-80' : '80+', tank: last.result.tank ? last.result.tank.id : 'none' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const fromUrl = readState(form);
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'water-heater-expansion-tank-size', referrer: document.referrer });
	}
}
