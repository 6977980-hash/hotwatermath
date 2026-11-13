/**
 * formulas.js: the ONLY place calculator math lives (CLAUDE.md §5).
 * Used in the browser by each calculator and in Node by scripts/build-data.mjs.
 * Pure functions. Data values come in as parameters, never as constants here.
 * The only constants allowed are physical ones and unit definitions (PHASES.md Phase 1, task 3).
 * Every function is documented on /methodology and unit-tested in tests/formulas.test.mjs.
 */

/** Physical constants and unit definitions (not data). */
export const PHYSICS = Object.freeze({
	lbPerGallon: 8.34, // weight of one US gallon of water, lb (1 BTU heats 1 lb of water by 1 °F)
	btuPerKwh: 3412, // energy in one kWh, BTU
	btuPerTherm: 100000, // energy in one therm, BTU (definition)
	cubicFeetPerMcf: 1000, // 1 Mcf = 1,000 cubic feet (definition)
	wattsPerKw: 1000, // definition
	centsPerDollar: 100,
	minutesPerHour: 60,
	daysPerYear: 365,
	monthsPerYear: 12,
	weeksPerYear: 52,
	litersPerGallon: 3.785, // one US gallon in liters (3.78541, rounded)
	fahrenheitPerCelsius: 1.8, // size of one °C step in °F
});

const isNum = (v) => typeof v === 'number' && Number.isFinite(v);
const round = (v, d = 0) => {
	const f = 10 ** d;
	return Math.round(v * f) / f;
};

/* ---------- shared helpers ---------- */

/**
 * Low / typical (median) / high of a list of numbers, e.g. prices or first hour ratings of a sample of models.
 * @param {number[]} values
 * @returns {{low:number, typical:number, high:number, n:number}|null} null for an empty list.
 */
export function summarize(values) {
	const v = values.filter(isNum).sort((a, b) => a - b);
	if (!v.length) return null;
	const mid = Math.floor(v.length / 2);
	const median = v.length % 2 ? v[mid] : (v[mid - 1] + v[mid]) / 2;
	return { low: v[0], typical: median, high: v[v.length - 1], n: v.length };
}

/* ---------- 3.1 Age calculator + serial decoder ---------- */

/** Pick the century for a two-digit year: the latest year ending in yy that is not after referenceYear. */
function yearFromTwoDigits(yy, referenceYear) {
	const base = Math.floor(referenceYear / 100) * 100;
	const y = base + yy;
	return y <= referenceYear ? y : y - 100;
}

/** Approximate month (1–12) of a production week (1–53). */
export function monthFromWeek(week) {
	return Math.min(PHYSICS.monthsPerYear, Math.floor(((week - 1) * PHYSICS.monthsPerYear) / PHYSICS.weeksPerYear) + 1);
}

/**
 * Decode a manufacture date from a serial number using one brand's patterns (data/serial-formats.json).
 * Patterns are tried in order; the first one whose regex matches and whose decoded date is valid wins.
 * Field types: "yy" (two-digit year), "mm" (two-digit month), "ww" (two-digit week),
 * "letterYear" (letter cycle: year = anchor + index of the letter, see cycleYears), "letterMonth" (letters list, Jan first).
 * @param {string} serial Serial number as printed on the rating plate.
 * @param {Array<object>} patterns Patterns for that brand.
 * @param {number} referenceYear Current year; no decoded date may be after it.
 * @returns {{year:number, month:(number|null), week:(number|null), patternId:string, otherYears:number[]}|null}
 */
export function decodeSerial(serial, patterns, referenceYear) {
	const s = String(serial || '').toUpperCase().replace(/[\s-]/g, '');
	if (!s) return null;
	for (const p of patterns) {
		const m = s.match(new RegExp(p.regex));
		if (!m) continue;
		let year = null;
		let month = null;
		let week = null;
		let otherYears = [];
		let ok = true;
		for (const [field, spec] of Object.entries(p.fields)) {
			const raw = m[spec.group];
			if (raw === undefined) { ok = false; break; }
			if (spec.type === 'yy') year = yearFromTwoDigits(parseInt(raw, 10), referenceYear);
			else if (spec.type === 'yyyy') { year = parseInt(raw, 10); if (year > referenceYear) ok = false; }
			else if (spec.type === 'mm') month = parseInt(raw, 10);
			else if (spec.type === 'ww') week = parseInt(raw, 10);
			else if (spec.type === 'letterMonth') {
				const i = spec.letters.indexOf(raw);
				if (i < 0) { ok = false; break; }
				month = i + 1;
			} else if (spec.type === 'letterYear') {
				const i = spec.letters.indexOf(raw);
				if (i < 0) { ok = false; break; }
				const years = spec.cycleStarts.map((start) => start + i).filter((y) => y <= referenceYear).sort((a, b) => b - a);
				if (!years.length) { ok = false; break; }
				[year, ...otherYears] = years;
			} else { ok = false; break; }
			if (field === 'month' && (month < 1 || month > 12)) ok = false;
			if (field === 'week' && (week < 1 || week > 53)) ok = false;
		}
		if (!ok || year === null) continue;
		if (isNum(p.validFromYear) && year < p.validFromYear) continue;
		if (isNum(p.validToYear) && year > p.validToYear) continue;
		if (week !== null && month === null) month = monthFromWeek(week);
		return { year, month, week, patternId: p.id, otherYears };
	}
	return null;
}

/**
 * Age in years (one decimal) between a manufacture date and a reference date.
 * An unknown month is treated as mid-year (July), which keeps the error under six months.
 * @param {{year:number, month:(number|null)}} made
 * @param {Date} today
 * @returns {number}
 */
export function ageYears(made, today) {
	const month = isNum(made.month) ? made.month : 7;
	const madeAt = made.year + (month - 1) / PHYSICS.monthsPerYear;
	const now = today.getFullYear() + today.getMonth() / PHYSICS.monthsPerYear;
	return Math.max(0, round(now - madeAt, 1));
}

/**
 * Expected life range shifted by the sum of the selected adjustments (years, from data/lifespans.json).
 * @param {{low:number, typical:number, high:number}} lifespan For the unit type.
 * @param {number[]} deltasYears Adjustments that apply, e.g. [-1] for hard water.
 * @param {number} minimumYears Floor so a life never drops below a sane value (from data).
 * @returns {{low:number, typical:number, high:number}}
 */
export function adjustedLifespan(lifespan, deltasYears, minimumYears) {
	const d = deltasYears.filter(isNum).reduce((a, b) => a + b, 0);
	const f = (v) => Math.max(minimumYears, v + d);
	return { low: f(lifespan.low), typical: f(lifespan.typical), high: f(lifespan.high) };
}

/**
 * Share of typical life used, remaining-life range and status band.
 * @param {number} age Years.
 * @param {{low:number, typical:number, high:number}} life
 * @param {{amberFrom:number, redFrom:number}} bands Fractions of typical life, from data.
 * @returns {{usedFraction:number, remaining:{min:number,max:number}, status:'ok'|'warn'|'bad'}}
 */
export function lifeStatus(age, life, bands) {
	const usedFraction = life.typical > 0 ? age / life.typical : 1;
	const remaining = { min: Math.max(0, round(life.low - age, 1)), max: Math.max(0, round(life.high - age, 1)) };
	const status = usedFraction >= bands.redFrom ? 'bad' : usedFraction >= bands.amberFrom ? 'warn' : 'ok';
	return { usedFraction: round(usedFraction, 3), remaining, status };
}

/**
 * Full age estimate for one unit: age, adjusted life and status (combines ageYears, adjustedLifespan, lifeStatus).
 * @param {{year:number, month:(number|null)}} made Build date.
 * @param {Date} today
 * @param {{low:number, typical:number, high:number}} lifespan For the unit type (data/lifespans.json).
 * @param {number[]} deltasYears Editorial adjustments that apply (data/lifespans.json → adjustments).
 * @param {number} minimumYears
 * @param {{amberFrom:number, redFrom:number}} bands
 * @returns {{age:number, life:{low:number,typical:number,high:number}, usedFraction:number, remaining:{min:number,max:number}, status:'ok'|'warn'|'bad'}}
 */
export function ageEstimate(made, today, lifespan, deltasYears, minimumYears, bands) {
	const age = ageYears(made, today);
	const life = adjustedLifespan(lifespan, deltasYears, minimumYears);
	return { age, life, ...lifeStatus(age, life, bands) };
}

/* ---------- 3.2 Tankless size ---------- */

/**
 * Total simultaneous flow: Σ(count × gpm).
 * @param {Array<{count:number, gpm:number}>} fixtures
 * @returns {number} GPM
 */
export function totalGpm(fixtures) {
	return round(fixtures.reduce((sum, f) => sum + (isNum(f.count) && isNum(f.gpm) ? f.count * f.gpm : 0), 0), 2);
}

/**
 * Temperature rise needed: setpoint − inlet (groundwater) temperature. Never negative.
 * @param {number} setpointF
 * @param {number} inletF
 * @returns {number} °F
 */
export function temperatureRise(setpointF, inletF) {
	return Math.max(0, setpointF - inletF);
}

/**
 * Heat output needed: GPM × 8.34 lb/gal × 60 min/h × ΔT.
 * @param {number} gpm
 * @param {number} riseF
 * @returns {number} BTU/h delivered to the water
 */
export function outputBtuh(gpm, riseF) {
	return gpm * PHYSICS.lbPerGallon * PHYSICS.minutesPerHour * riseF;
}

/**
 * Gas input rating needed: output ÷ efficiency.
 * @param {number} outputBtuhValue
 * @param {number} efficiency Decimal (e.g. a UEF value from data/uef-ranges.json).
 * @returns {number} BTU/h input
 */
export function gasInputBtuh(outputBtuhValue, efficiency) {
	return efficiency > 0 ? outputBtuhValue / efficiency : Infinity;
}

/**
 * Electric power needed: output ÷ 3412 BTU per kWh.
 * @param {number} outputBtuhValue
 * @returns {number} kW
 */
export function electricKw(outputBtuhValue) {
	return outputBtuhValue / PHYSICS.btuPerKwh;
}

/**
 * Current draw at a given voltage: kW × 1000 ÷ volts.
 * @param {number} kw
 * @param {number} volts From data/electrical.json.
 * @returns {number} amps
 */
export function electricAmps(kw, volts) {
	return volts > 0 ? (kw * PHYSICS.wattsPerKw) / volts : Infinity;
}

/**
 * Smallest manufacturer reference unit (data/electrical.json) with at least the required kW.
 * @param {number} kw Required power.
 * @param {Array<{model:string, kw:number, amps:number, breakers:string}>} units
 * @returns {object|null} null when even the largest unit is too small.
 */
export function referenceUnitFor(kw, units) {
	return [...units].sort((a, b) => a.kw - b.kw).find((u) => u.kw >= kw) || null;
}

/**
 * Heat output of an electric unit: kW × 3412 BTU per kWh.
 * @param {number} kw
 * @returns {number} BTU/h
 */
export function btuhFromKw(kw) {
	return kw * PHYSICS.btuPerKwh;
}

/**
 * Largest temperature rise a unit can hold at a given flow: output ÷ (GPM × 8.34 × 60).
 * @param {number} outputBtuhValue
 * @param {number} gpm
 * @returns {number} °F
 */
export function riseAtGpm(outputBtuhValue, gpm) {
	return gpm > 0 ? outputBtuhValue / (gpm * PHYSICS.lbPerGallon * PHYSICS.minutesPerHour) : Infinity;
}

/**
 * Size chart: maximum GPM a unit can deliver at each temperature rise.
 * @param {number} outputBtuhValue Unit output (input × efficiency) or kW × 3412.
 * @param {number[]} rises List of ΔT values (°F).
 * @returns {Array<{rise:number, gpm:number}>}
 */
export function maxGpmByRise(outputBtuhValue, rises) {
	return rises.map((rise) => ({ rise, gpm: rise > 0 ? round(outputBtuhValue / (PHYSICS.lbPerGallon * PHYSICS.minutesPerHour * rise), 2) : Infinity }));
}

/**
 * Smallest item in a list whose numeric field is at least the value (e.g. the smallest gas input size that covers the need).
 * @param {number} value
 * @param {Array<object>} items
 * @param {string} key Field to compare.
 * @returns {object|null} null when even the largest item is too small.
 */
export function smallestAtLeast(value, items, key) {
	return [...items].sort((a, b) => a[key] - b[key]).find((i) => i[key] >= value) || null;
}

/**
 * Number of identical units needed to cover a requirement (at least 1).
 * @param {number} required e.g. BTU/h input or kW needed.
 * @param {number} perUnit Capacity of one unit in the same unit.
 * @returns {number}
 */
export function unitsNeeded(required, perUnit) {
	if (!(perUnit > 0)) return Infinity;
	return Math.max(1, Math.ceil(round(required / perUnit, 6)));
}

/**
 * Tankless sizing for one household: flow, rise, output, gas input, electric kW and amps, and the reference sizes that cover it.
 * @param {{fixtures:Array<{count:number,gpm:number}>, setpointF:number, inletF:number, gasEfficiency:number, volts:number,
 *   gasSizesBtuh:number[], electricUnits:Array<{model:string,kw:number,amps:number,breakers:string,wire:string}>}} i
 * @returns {object}
 */
export function tanklessSizing(i) {
	const gpm = totalGpm(i.fixtures);
	const rise = temperatureRise(i.setpointF, i.inletF);
	const output = outputBtuh(gpm, rise);
	const gasInput = gasInputBtuh(output, i.gasEfficiency);
	const kw = electricKw(output);
	const amps = electricAmps(kw, i.volts);
	const largestGas = Math.max(...i.gasSizesBtuh);
	const gasSize = smallestAtLeast(gasInput, i.gasSizesBtuh.map((btuh) => ({ btuh })), 'btuh');
	const electricUnit = referenceUnitFor(kw, i.electricUnits);
	const largestKw = Math.max(...i.electricUnits.map((u) => u.kw));
	return {
		gpm,
		rise,
		outputBtuh: round(output),
		gasInputBtuh: round(gasInput),
		gasSizeBtuh: gasSize ? gasSize.btuh : null,
		gasUnitsAtLargest: unitsNeeded(gasInput, largestGas),
		largestGasBtuh: largestGas,
		kw: round(kw, 1),
		amps: round(amps),
		electricUnit,
		electricUnitsAtLargest: unitsNeeded(kw, largestKw),
		largestKw,
	};
}

/**
 * Water mains (inlet) temperature from outdoor air temperature, using the Christensen–Burch correlation
 * (Hendron et al. 2004, as documented in the EnergyPlus Engineering Reference).
 * Tmains = (Tavg + offset) + ratio × (ΔTmax / 2) × sin(...), ratio = ratioBase + ratioSlope × (Tavg − refTemp).
 * The sine swings between −1 and +1 over the year, so the coldest month is (Tavg + offset) − ratio × ΔTmax / 2.
 * @param {number} annualAvgAirF Average annual outdoor air temperature, °F.
 * @param {number} maxMonthlyDiffF Warmest minus coldest monthly average air temperature, °F.
 * @param {{offsetF:number, ratioBase:number, ratioSlope:number, refTempF:number}} c Correlation coefficients (data).
 * @returns {{annualAvg:number, coldestMonth:number, warmestMonth:number}} °F, one decimal
 */
export function mainsWaterTemp(annualAvgAirF, maxMonthlyDiffF, c) {
	const avg = annualAvgAirF + c.offsetF;
	const ratio = c.ratioBase + c.ratioSlope * (annualAvgAirF - c.refTempF);
	const swing = ratio * (maxMonthlyDiffF / 2);
	return { annualAvg: round(avg, 1), coldestMonth: round(avg - swing, 1), warmestMonth: round(avg + swing, 1) };
}

/* ---------- 3.3 Tank size ---------- */

/**
 * Peak-hour demand: Σ(count × gallonsPerUse).
 * @param {Array<{count:number, gallons:number}>} activities From data/peak-hour-demand.json.
 * @returns {number} gallons in the busiest hour
 */
export function peakHourDemand(activities) {
	return round(activities.reduce((sum, a) => sum + (isNum(a.count) && isNum(a.gallons) ? a.count * a.gallons : 0), 0), 1);
}

/**
 * Smallest nominal tank size whose typical first hour rating meets the required FHR, for one fuel.
 * @param {number} fhrGallons Required first hour rating (≥ peak-hour demand).
 * @param {string} fuel 'gas' | 'electric' | 'heatpump'
 * @param {Array<{fuel:string, nominalGallons:number, fhrLow:number, fhrTypical:number, fhrHigh:number}>} table
 *   Built from data/fhr-to-tank-size.json (see data/computed/fhr-table.json).
 * @returns {{nominalGallons:number, row:object, fits:'typical'|'some-models'|'none'}|null} null when the fuel has no rows.
 */
export function tankForFhr(fhrGallons, fuel, table) {
	const rows = table.filter((r) => r.fuel === fuel).sort((a, b) => a.nominalGallons - b.nominalGallons);
	if (!rows.length) return null;
	const typical = rows.find((r) => r.fhrTypical >= fhrGallons);
	if (typical) return { nominalGallons: typical.nominalGallons, row: typical, fits: 'typical' };
	const some = rows.find((r) => r.fhrHigh >= fhrGallons);
	if (some) return { nominalGallons: some.nominalGallons, row: some, fits: 'some-models' };
	const largest = rows[rows.length - 1];
	return { nominalGallons: largest.nominalGallons, row: largest, fits: 'none' };
}

/**
 * DOE test draw pattern a unit is rated under, from its first hour rating (10 CFR 430 Subpart B, Appendix E, Table I).
 * @param {number} fhrGallons
 * @param {Array<{id:string, fhrFrom:number, fhrBelow:(number|undefined)}>} patterns From data/hot-water-use.json.
 * @returns {object|null}
 */
export function drawPatternForFhr(fhrGallons, patterns) {
	return patterns.find((p) => fhrGallons >= p.fhrFrom && (!isNum(p.fhrBelow) || fhrGallons < p.fhrBelow)) || null;
}

/* ---------- 3.4 Repair or replace ---------- */

/**
 * Repair / replace / borderline decision with reason codes (content turns codes into sentences).
 * @param {{ageYears:number, expectedLife:number, repairCost:number, replacementCost:number, tankLeaking:boolean, flooded?:boolean}} input
 * @param {{ageShare:number, repairShareWhenOld:number, repairShareAlways:number}} rules Thresholds from data/decision-rules.json.
 * @returns {{verdict:'repair'|'replace'|'borderline', reasons:string[], share:number, lifeUsed:number, oldFromYears:number}}
 *   share = repair ÷ replacement; lifeUsed = age ÷ expected life; oldFromYears = age at which the "old" rule starts.
 */
export function repairOrReplace(input, rules) {
	const share = input.replacementCost > 0 ? input.repairCost / input.replacementCost : 1;
	const lifeUsed = input.expectedLife > 0 ? input.ageYears / input.expectedLife : 1;
	const extra = { share: round(share, 3), lifeUsed: round(lifeUsed, 3), oldFromYears: round(rules.ageShare * input.expectedLife, 2) };
	const out = (verdict, reasons) => ({ verdict, reasons, ...extra });
	if (input.tankLeaking) return out('replace', ['tank-leak']);
	if (input.flooded) return out('replace', ['flooded']);
	const old = input.expectedLife > 0 && input.ageYears >= rules.ageShare * input.expectedLife;
	if (share > rules.repairShareAlways) return out('replace', ['repair-over-always-share']);
	if (old && share > rules.repairShareWhenOld) return out('replace', ['old', 'repair-over-old-share']);
	if (old) return out('borderline', ['old', 'repair-cheap']);
	if (share > rules.repairShareWhenOld) return out('borderline', ['young', 'repair-over-old-share']);
	return out('repair', ['young', 'repair-cheap']);
}

/**
 * Yearly energy cost of one water heater type for a household: heat ÷ efficiency × price.
 * Composes annualHeatBtu, annualEnergyUse and annualCost.
 * @param {number} gallonsPerDay
 * @param {number} riseF
 * @param {'gas'|'electric'} fuel
 * @param {number} uef
 * @param {{usdPerKwh?:number, usdPerTherm?:number}} prices
 * @returns {{use:{kwh?:number, therms?:number}, year:number, month:number}}
 */
export function yearlyEnergyCost(gallonsPerDay, riseF, fuel, uef, prices) {
	const use = annualEnergyUse(annualHeatBtu(gallonsPerDay, riseF), fuel, uef);
	return { use, ...annualCost(use, prices) };
}

/**
 * Yearly saving of moving from one water heater to another (positive = the new one is cheaper to run).
 * @param {{year:number}} current From yearlyEnergyCost().
 * @param {{year:number}} next From yearlyEnergyCost().
 * @returns {number} USD per year, rounded to whole dollars
 */
export function yearlySaving(current, next) {
	return round(current.year - next.year);
}

/* ---------- 3.3 Tank size: recommendation for every fuel ---------- */

/**
 * Peak-hour demand, required first hour rating and the recommended tank for each fuel.
 * @param {Array<{count:number, gallons:number}>} activities
 * @param {number} marginShare 0 for none, e.g. 0.1 for +10% (optional margin).
 * @param {number} fhrToPhdRatio Minimum FHR ÷ peak-hour demand (1.0 per AHRI/PNNL).
 * @param {Array<object>} table Rows of data/computed/fhr-table.json.
 * @returns {{phd:number, fhr:number, gas:object|null, electric:object|null, heatpump:object|null}}
 */
export function tankSizing(activities, marginShare, fhrToPhdRatio, table) {
	const phd = peakHourDemand(activities);
	const fhr = Math.ceil(round(phd * fhrToPhdRatio * (1 + (isNum(marginShare) ? marginShare : 0)), 6));
	return { phd, fhr, gas: tankForFhr(fhr, 'gas', table), electric: tankForFhr(fhr, 'electric', table), heatpump: tankForFhr(fhr, 'heatpump', table) };
}

/* ---------- 3.5 Installed cost ---------- */

/**
 * Installed cost range: unit + (labor + extras) × regional multiplier.
 * The multiplier applies to labor and extras only, because equipment is priced nationally
 * (see docs/DECISIONS.md, 2026-11-12, Phase 1).
 * @param {{low:number,typical:number,high:number}} unit
 * @param {{low:number,typical:number,high:number}} labor
 * @param {Array<{id:string, low:number,typical:number,high:number}>} extras
 * @param {number} regionalMultiplier From data/regional-cost-index.json (1.00 = national).
 * @returns {{low:number,typical:number,high:number, items:Array<object>}}
 */
export function installedCost(unit, labor, extras, regionalMultiplier) {
	const m = isNum(regionalMultiplier) ? regionalMultiplier : 1;
	const scale = (r) => ({ low: round(r.low * m), typical: round(r.typical * m), high: round(r.high * m) });
	const items = [
		{ id: 'unit', low: unit.low, typical: unit.typical, high: unit.high },
		{ id: 'labor', ...scale(labor) },
		...extras.map((e) => ({ id: e.id, ...scale(e) })),
	];
	const sum = (k) => items.reduce((a, i) => a + i[k], 0);
	return { low: sum('low'), typical: sum('typical'), high: sum('high'), items };
}

/* ---------- 3.7 Operating cost ---------- */

/**
 * Natural gas price per therm from EIA's price per thousand cubic feet and the gas heat content.
 * therms per Mcf = btuPerCf × 1,000 ÷ 100,000.
 * @param {number} usdPerMcf
 * @param {number} btuPerCf Heat content of delivered gas, BTU per cubic foot (EIA).
 * @returns {number} USD per therm
 */
export function gasUsdPerTherm(usdPerMcf, btuPerCf) {
	const thermsPerMcf = (btuPerCf * PHYSICS.cubicFeetPerMcf) / PHYSICS.btuPerTherm;
	return thermsPerMcf > 0 ? usdPerMcf / thermsPerMcf : Infinity;
}

/**
 * Electricity price per kWh from EIA's cents per kWh.
 * @param {number} centsPerKwh
 * @returns {number} USD per kWh
 */
export function usdPerKwh(centsPerKwh) {
	return centsPerKwh / PHYSICS.centsPerDollar;
}

/**
 * Yearly energy to heat water: gal/day × 365 × 8.34 × ΔT.
 * @param {number} gallonsPerDay
 * @param {number} riseF
 * @returns {number} BTU per year delivered to the water
 */
export function annualHeatBtu(gallonsPerDay, riseF) {
	return gallonsPerDay * PHYSICS.daysPerYear * PHYSICS.lbPerGallon * riseF;
}

/**
 * Energy bought per year for one water heater type: heat ÷ 3412 ÷ UEF (kWh) or heat ÷ 100,000 ÷ UEF (therms).
 * @param {number} heatBtu From annualHeatBtu().
 * @param {'electric'|'gas'} fuel
 * @param {number} uef Uniform Energy Factor for the type, from data/uef-ranges.json.
 * @returns {{kwh?:number, therms?:number}}
 */
export function annualEnergyUse(heatBtu, fuel, uef) {
	if (!(uef > 0)) return fuel === 'gas' ? { therms: Infinity } : { kwh: Infinity };
	return fuel === 'gas'
		? { therms: round(heatBtu / PHYSICS.btuPerTherm / uef, 1) }
		: { kwh: round(heatBtu / PHYSICS.btuPerKwh / uef, 0) };
}

/**
 * Yearly and monthly cost from energy use and price.
 * @param {{kwh?:number, therms?:number}} use
 * @param {{usdPerKwh?:number, usdPerTherm?:number}} prices From data/computed/energy-prices.json or user input.
 * @returns {{year:number, month:number}}
 */
export function annualCost(use, prices) {
	const year = (use.kwh || 0) * (prices.usdPerKwh || 0) + (use.therms || 0) * (prices.usdPerTherm || 0);
	return { year: round(year, 2), month: round(year / PHYSICS.monthsPerYear, 2) };
}

/* ---------- 3.6 Tankless vs tank, cost of ownership ---------- */

/**
 * Total cost of ownership over a horizon: installed cost − credit, plus operating cost every year,
 * plus a full installed cost (no credit) each time the unit reaches its life within the horizon.
 * @param {{installed:number, annualOperating:number, lifeYears:number, credit:number}} option
 * @param {number} years Horizon (default from data).
 * @returns {{total:number, byYear:number[], replacements:number}} byYear[i] = cumulative cost at the end of year i + 1.
 */
export function costOfOwnership(option, years) {
	let total = option.installed - (option.credit || 0);
	let replacements = 0;
	const byYear = [];
	for (let y = 1; y <= years; y++) {
		total += option.annualOperating;
		if (option.lifeYears > 0 && y % option.lifeYears === 0 && y < years) {
			total += option.installed;
			replacements++;
		}
		byYear.push(round(total));
	}
	return { total: round(total), byYear, replacements };
}

/**
 * First year in which option B's cumulative cost is at or below option A's.
 * @param {number[]} byYearA
 * @param {number[]} byYearB
 * @returns {number|null} Year number (1-based), or null when B never breaks even within the horizon.
 */
export function breakEvenYear(byYearA, byYearB) {
	const n = Math.min(byYearA.length, byYearB.length);
	for (let i = 0; i < n; i++) if (byYearB[i] <= byYearA[i]) return i + 1;
	return null;
}
