/**
 * cost-model.js: the shared cost model for the three Phase 4 tools (cost calculator, tankless vs tank, gas vs electric)
 * and the repair-or-replace tool's installed replacement price. It only wires data to formulas.js, so the same inputs
 * give the same numbers on every page (PHASES.md Phase 4 acceptance). No math constants here; data comes in as `m`
 * (the "model" object that scripts/build-data.mjs writes into each tool's computed file).
 */
import { installedCost, temperatureRise, yearlyEnergyCost, costOfOwnership, breakEvenYear, householdGallonsPerDay } from './formulas.js';

/** Unit price key for a type and size, e.g. ('gas_tank', '50') → 'gas_tank_50'. Falls back to the type's default size. */
export function unitKeyFor(m, type, size) {
	const t = m.types[type];
	if (!t) return null;
	const want = `${type}_${size}`;
	return m.units[want] ? want : `${type}_${t.defaultSize}`;
}

/** Location data for a state code, or the U.S. average ('US'). */
export function placeFor(m, state) {
	return m.states[state] || m.us;
}

/** Extras that a scenario ticks by default for a type. */
export function defaultExtras(m, scenario, type) {
	const s = m.scenarios[scenario] || m.scenarios.replace;
	return (s.defaultExtras[type] || []).slice();
}

/**
 * Installed cost estimate for one job.
 * @param {object} m Model data.
 * @param {{type:string, size:string, state:string, extras:string[]}} job
 * @returns {{key:string, unit:object, multiplier:number, low:number, typical:number, high:number, items:Array<object>}}
 */
export function estimateInstalled(m, job) {
	const key = unitKeyFor(m, job.type, job.size);
	const unit = m.units[key];
	const place = placeFor(m, job.state);
	const extras = (job.extras || []).filter((id) => m.extras[id]).map((id) => ({ id, ...m.extras[id] }));
	const r = installedCost(unit, m.labor[job.type], extras, place.multiplier);
	const label = (id) => (id === 'unit' ? unit.label : id === 'labor' ? 'Installation labor' : m.extras[id].label);
	return { key, unit, multiplier: place.multiplier, ...r, items: r.items.map((i) => ({ ...i, label: label(i.id) })) };
}

/**
 * Hot water use in gallons a day: a household size times per-person use, or the DOE typical day.
 * @param {object} m
 * @param {string|number} household 'typical' or a number of people.
 */
export function gallonsFor(m, household) {
	return householdGallonsPerDay(Number(household), m.perPersonGallons, m.typicalGallons);
}

/**
 * Yearly running cost of every type for one household and place.
 * @param {object} m
 * @param {{gallonsPerDay:number, setpointF:number, inletF:number, prices:{usdPerKwh:number, usdPerTherm:number}, uefs?:object}} h
 * @returns {{rise:number, rows:Array<{id:string,label:string,fuel:string,uef:number,use:object,year:number,month:number}>}}
 */
export function operatingCosts(m, h) {
	const rise = temperatureRise(h.setpointF, h.inletF);
	const rows = m.typeOrder.map((id) => {
		const t = m.types[id];
		const uef = h.uefs && h.uefs[id] > 0 ? h.uefs[id] : t.uef;
		return { id, label: t.label, fuel: t.fuel, uef, ...yearlyEnergyCost(h.gallonsPerDay, rise, t.fuel, uef, h.prices) };
	});
	return { rise, rows };
}

/**
 * Cost of ownership for a set of options over the same years, and break-even of each against the first (the tank).
 * @param {Array<{id:string, label:string, installed:number, annualOperating:number, lifeYears:number, credit:number}>} options
 * @param {number} years
 */
export function ownershipCompare(options, years) {
	const res = options.map((o) => ({ ...o, ...costOfOwnership(o, years) }));
	const base = res[0];
	return res.map((o, i) => ({ ...o, breakEven: i === 0 ? null : breakEvenYear(base.byYear, o.byYear) }));
}
