/**
 * formulas.js: the ONLY place calculator math lives (CLAUDE.md §5).
 * Used in the browser by each calculator and in Node by scripts/build-data.mjs.
 * Pure functions. Data values come in as parameters, never as constants here.
 * The only constants allowed are physical ones (PHASES.md Phase 1, task 3).
 *
 * Phase 0: stubs with JSDoc. Phase 1 implements and unit-tests every function.
 */

/** Physical constants (not data). */
export const PHYSICS = Object.freeze({
	lbPerGallon: 8.34, // weight of one US gallon of water, lb
	btuPerKwh: 3412, // energy in one kWh, BTU
	btuPerTherm: 100000, // energy in one therm, BTU
	minutesPerHour: 60,
});

const todo = (name) => {
	throw new Error(`formulas.${name} is not implemented yet (Phase 1).`);
};

/* ---------- 3.1 Age calculator + serial decoder ---------- */

/**
 * Decode a manufacture date from a serial number using one brand's patterns.
 * @param {string} serial Serial number as printed on the rating plate.
 * @param {Array<object>} patterns Patterns for that brand from data/serial-formats.json.
 * @returns {{year:number, month:(number|null), patternId:string}|null} null when no pattern matches.
 */
export function decodeSerial(serial, patterns) { return todo('decodeSerial'); }

/**
 * Age in years (one decimal) between a manufacture date and a reference date.
 * @param {{year:number, month:(number|null)}} made
 * @param {Date} today
 * @returns {number}
 */
export function ageYears(made, today) { return todo('ageYears'); }

/**
 * Expected life range adjusted for hardness, maintenance and anode replacement.
 * @param {{min:number, typical:number, max:number}} lifespan From data/lifespans.json for the unit type.
 * @param {{hardness:number, noMaintenance:number, anodeReplaced:number}} adjustments Years, from data (editorial assumptions).
 * @returns {{min:number, typical:number, max:number}}
 */
export function adjustedLifespan(lifespan, adjustments) { return todo('adjustedLifespan'); }

/**
 * Share of expected life used, remaining-life range and status band.
 * @param {number} age Years.
 * @param {{min:number, typical:number, max:number}} life
 * @param {{amberFrom:number, redFrom:number}} bands Fractions, from data.
 * @returns {{usedFraction:number, remaining:{min:number,max:number}, status:'ok'|'warn'|'bad'}}
 */
export function lifeStatus(age, life, bands) { return todo('lifeStatus'); }

/* ---------- 3.2 Tankless size ---------- */

/**
 * Total simultaneous flow: Σ(count × gpm).
 * @param {Array<{count:number, gpm:number}>} fixtures
 * @returns {number} GPM
 */
export function totalGpm(fixtures) { return todo('totalGpm'); }

/**
 * Temperature rise needed: setpoint − inlet (groundwater) temperature.
 * @param {number} setpointF
 * @param {number} inletF
 * @returns {number} °F
 */
export function temperatureRise(setpointF, inletF) { return todo('temperatureRise'); }

/**
 * Heat output needed: GPM × 8.34 × 60 × ΔT.
 * @param {number} gpm
 * @param {number} riseF
 * @returns {number} BTU/h delivered to the water
 */
export function outputBtuh(gpm, riseF) { return todo('outputBtuh'); }

/**
 * Gas input rating needed: output ÷ efficiency.
 * @param {number} outputBtuhValue
 * @param {number} efficiency Decimal (e.g. a UEF value from data/uef-ranges.json).
 * @returns {number} BTU/h input
 */
export function gasInputBtuh(outputBtuhValue, efficiency) { return todo('gasInputBtuh'); }

/**
 * Electric power needed: output ÷ 3412 (BTU per kWh).
 * @param {number} outputBtuhValue
 * @returns {number} kW
 */
export function electricKw(outputBtuhValue) { return todo('electricKw'); }

/**
 * Current draw at a given voltage, plus the continuous-load breaker size factor.
 * @param {number} kw
 * @param {number} volts From data (residential supply voltage).
 * @param {number} continuousFactor From data (e.g. code continuous-load factor).
 * @returns {{amps:number, minimumBreakerAmps:number}}
 */
export function electricAmps(kw, volts, continuousFactor) { return todo('electricAmps'); }

/**
 * Size chart: maximum GPM a unit can deliver at each temperature rise.
 * @param {number} outputBtuhValue Unit output (input × efficiency) or kW × 3412.
 * @param {number[]} rises List of ΔT values (°F).
 * @returns {Array<{rise:number, gpm:number}>}
 */
export function maxGpmByRise(outputBtuhValue, rises) { return todo('maxGpmByRise'); }

/* ---------- 3.3 Tank size ---------- */

/**
 * Peak-hour demand: Σ(count × gallonsPerUse).
 * @param {Array<{count:number, gallons:number}>} activities From data/peak-hour-demand.json.
 * @returns {number} gallons in the busiest hour
 */
export function peakHourDemand(activities) { return todo('peakHourDemand'); }

/**
 * Typical tank size for a required first hour rating and fuel.
 * @param {number} fhrGallons
 * @param {string} fuel 'gas' | 'electric' | 'heatpump'
 * @param {Array<object>} table From data/fhr-to-tank-size.json.
 * @returns {{tankGallons:number[], note:string}|null}
 */
export function tankForFhr(fhrGallons, fuel, table) { return todo('tankForFhr'); }

/* ---------- 3.4 Repair or replace ---------- */

/**
 * Repair / replace / borderline decision with reasons.
 * @param {{ageYears:number, expectedLife:number, repairCost:number, replacementCost:number, tankLeaking:boolean}} input
 * @param {{ageShare:number, repairShareWhenOld:number, repairShareAlways:number}} rules Thresholds from data.
 * @returns {{verdict:'repair'|'replace'|'borderline', reasons:string[]}}
 */
export function repairOrReplace(input, rules) { return todo('repairOrReplace'); }

/* ---------- 3.5 Installed cost ---------- */

/**
 * Installed cost range: (unit + labor + extras) × regional multiplier.
 * @param {{low:number,typical:number,high:number}} unit
 * @param {{low:number,typical:number,high:number}} labor
 * @param {Array<{low:number,typical:number,high:number}>} extras
 * @param {number} regionalMultiplier From data/regional-cost-index.json (1.00 = national).
 * @returns {{low:number,typical:number,high:number, items:Array<object>}}
 */
export function installedCost(unit, labor, extras, regionalMultiplier) { return todo('installedCost'); }

/* ---------- 3.7 Operating cost ---------- */

/**
 * Yearly energy to heat water: gal/day × 365 × 8.34 × ΔT.
 * @param {number} gallonsPerDay
 * @param {number} riseF
 * @returns {number} BTU per year delivered to the water
 */
export function annualHeatBtu(gallonsPerDay, riseF) { return todo('annualHeatBtu'); }

/**
 * Energy bought per year for one water heater type.
 * @param {number} heatBtu From annualHeatBtu().
 * @param {'electric'|'gas'} fuel
 * @param {number} uef Uniform Energy Factor for the type, from data/uef-ranges.json.
 * @returns {{kwh?:number, therms?:number}}
 */
export function annualEnergyUse(heatBtu, fuel, uef) { return todo('annualEnergyUse'); }

/**
 * Yearly and monthly cost from energy use and price.
 * @param {{kwh?:number, therms?:number}} use
 * @param {{usdPerKwh?:number, usdPerTherm?:number}} prices From data/energy-prices.json or user input.
 * @returns {{year:number, month:number}}
 */
export function annualCost(use, prices) { return todo('annualCost'); }

/* ---------- 3.6 Tankless vs tank, 10-year cost of ownership ---------- */

/**
 * Total cost of ownership over a horizon, including replacements and tax credits.
 * @param {{installed:number, annualOperating:number, lifeYears:number, credit:number}} option
 * @param {number} years Horizon (default from data).
 * @returns {{total:number, byYear:number[], replacements:number}}
 */
export function costOfOwnership(option, years) { return todo('costOfOwnership'); }

/**
 * First year in which option B's cumulative cost drops below option A's.
 * @param {number[]} byYearA
 * @param {number[]} byYearB
 * @returns {number|null} Year number, or null when B never breaks even within the horizon.
 */
export function breakEvenYear(byYearA, byYearB) { return todo('breakEvenYear'); }
