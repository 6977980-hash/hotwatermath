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

/** Reference date for the decoder: a Date (year, month and week checked) or a bare year (only the year checked). */
function referenceParts(ref) {
	if (ref instanceof Date) {
		const year = ref.getFullYear();
		const dayOfYear = Math.floor((Date.UTC(year, ref.getMonth(), ref.getDate()) - Date.UTC(year, 0, 1)) / 86400000) + 1;
		return { year, month: ref.getMonth() + 1, week: Math.ceil(dayOfYear / 7) };
	}
	return { year: ref, month: null, week: null };
}

/**
 * Decode a manufacture date from a serial number using one brand's patterns (data/serial-formats.json).
 * Patterns are tried in order; the first one whose regex matches and whose decoded date is valid wins.
 * Field types: "yy" (two-digit year), "mm" (two-digit month), "ww" (two-digit week),
 * "letterYear" (letter cycle: year = anchor + index of the letter, see cycleYears), "letterMonth" (letters list, Jan first).
 * A decoded date is rejected (the next pattern is tried) when it is after the reference date or before minYear,
 * so a future two-digit year such as "28" never turns into 1928 (serial-formats.json → plausibility).
 * @param {string} serial Serial number as printed on the rating plate.
 * @param {Array<object>} patterns Patterns for that brand.
 * @param {Date|number} today Today's date (or just the current year); no decoded date may be after it.
 * @param {number} [minYear] Oldest build year accepted (data: current year − plausibility.maxAgeYears).
 * @returns {{year:number, month:(number|null), week:(number|null), patternId:string, otherYears:number[]}|null}
 */
export function decodeSerial(serial, patterns, today, minYear) {
	const s = String(serial || '').toUpperCase().replace(/[\s-]/g, '');
	if (!s) return null;
	const ref = referenceParts(today);
	const referenceYear = ref.year;
	const floor = isNum(minYear) ? minYear : -Infinity;
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
				const years = spec.cycleStarts.map((start) => start + i).filter((y) => y <= referenceYear && y >= floor).sort((a, b) => b - a);
				if (!years.length) { ok = false; break; }
				[year, ...otherYears] = years;
			} else { ok = false; break; }
			if (field === 'month' && (month < 1 || month > 12)) ok = false;
			if (field === 'week' && (week < 1 || week > 53)) ok = false;
		}
		if (!ok || year === null) continue;
		if (year < floor || year > referenceYear) continue;
		if (isNum(p.validFromYear) && year < p.validFromYear) continue;
		if (isNum(p.validToYear) && year > p.validToYear) continue;
		if (week !== null && month === null) month = monthFromWeek(week);
		// Not after today: a later month, or a later week, of the current year is a misread.
		const ahead = year === referenceYear && ref.month !== null && (week !== null ? week > ref.week : month !== null && month > ref.month);
		if (ahead) {
			// A letter cycle whose latest year is this year but whose month is still ahead: the previous cycle is meant.
			if (!otherYears.length) continue;
			[year, ...otherYears] = otherYears;
		}
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
 * Hot (heater) flow of a set of fixtures: each fixture's mixed flow × its hot share at its use temperature
 * (hotShareOfMix(useTempF, setpoint, inlet)). A fixture without useTempF, or one used at or above the setpoint
 * (dishwasher, clothes washer), draws its whole flow from the heater. Rinnai sizes showers this way: "Tankless set
 * temperature of 120°F, mixed shower temperature of 105°F" (data/fixture-flow-rates.json → useTempF).
 * @param {Array<{count:number, gpm:number, useTempF?:number}>} fixtures
 * @param {number} setpointF
 * @param {number} inletF
 * @returns {{mixedGpm:number, hotGpm:number, rows:Array<{count:number, gpm:number, useTempF:number, share:number, hotGpm:number}>}}
 */
export function hotFlowGpm(fixtures, setpointF, inletF) {
	const rows = fixtures.filter((f) => isNum(f.count) && f.count > 0 && isNum(f.gpm)).map((f) => {
		const use = isNum(f.useTempF) && f.useTempF < setpointF ? f.useTempF : setpointF;
		const share = use >= setpointF ? 1 : hotShareOfMix(use, setpointF, inletF) ?? 1;
		return { count: f.count, gpm: f.gpm, useTempF: use, share, hotGpm: f.count * f.gpm * share };
	});
	return { mixedGpm: totalGpm(fixtures), hotGpm: round(rows.reduce((a, x) => a + x.hotGpm, 0), 2), rows: rows.map((x) => ({ ...x, hotGpm: round(x.hotGpm, 2) })) };
}

/**
 * Tankless sizing for one household: flow, rise, output, gas input, electric kW and amps, and the reference sizes that cover it.
 * gpm is the hot flow the heater makes at the setpoint (hotFlowGpm); mixedGpm is what the fixtures deliver.
 * @param {{fixtures:Array<{count:number,gpm:number,useTempF?:number}>, setpointF:number, inletF:number, gasEfficiency:number, volts:number,
 *   gasSizesBtuh:number[], electricUnits:Array<{model:string,kw:number,amps:number,breakers:string,wire:string}>}} i
 * @returns {object}
 */
export function tanklessSizing(i) {
	const flow = hotFlowGpm(i.fixtures, i.setpointF, i.inletF);
	const gpm = flow.hotGpm;
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
		mixedGpm: flow.mixedGpm,
		flowRows: flow.rows,
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
 * Point-of-use electric tankless sizing for one fixture: the unit heats the fixture's whole flow straight to the use
 * temperature (no mixing), so kW = GPM × 8.34 × 60 × (use − inlet) ÷ 3412 (Eemax sizing guide: kW = GPM × rise ÷ 6.83),
 * then the smallest listed unit with at least that power.
 * @param {{gpm:number, useTempF:number, inletF:number, units:Array<{model:string, kw:number, volts:number, amps:number}>}} i
 * @returns {{rise:number, outputBtuh:number, kw:number, unit:object|null, largestKw:number}}
 */
export function pointOfUseSizing(i) {
	const rise = temperatureRise(i.useTempF, i.inletF);
	const output = outputBtuh(i.gpm, rise);
	const kw = electricKw(output);
	const unit = referenceUnitFor(round(kw, 2), i.units);
	return { rise, outputBtuh: round(output), kw: round(kw, 2), unit, largestKw: Math.max(...i.units.map((u) => u.kw)) };
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

/** Julian day in the middle of each month (non-leap year), used for monthly mains temperatures. */
export const MID_MONTH_DAYS = Object.freeze([15, 46, 74, 105, 135, 166, 196, 227, 258, 288, 319, 349]);
/** Days in each month of a non-leap year (the year has 365 days in annualHeatBtu as well). */
export const MONTH_DAYS = Object.freeze([31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]);

/**
 * Month-by-month water mains (inlet) temperature with the full Christensen–Burch correlation (EnergyPlus Engineering
 * Reference): Tmains = (Tavg + offset) + ratio × (ΔTmax / 2) × sin(degPerDay × (day − dayOffset − lag) − phase),
 * lag = lagBase − lagSlope × (Tavg − refTemp) days, angles in degrees. Mains water lags the air by about a month.
 * @param {number} annualAvgAirF Average annual outdoor air temperature, °F.
 * @param {number} maxMonthlyDiffF Warmest minus coldest monthly average air temperature, °F.
 * @param {{offsetF:number, ratioBase:number, ratioSlope:number, refTempF:number, lagBaseDays:number, lagSlopeDaysPerF:number, degPerDay:number, dayOffset:number, phaseDeg:number}} c
 * @returns {number[]} 12 values, January first, °F with one decimal
 */
export function monthlyMainsTemps(annualAvgAirF, maxMonthlyDiffF, c) {
	const avg = annualAvgAirF + c.offsetF;
	const ratio = c.ratioBase + c.ratioSlope * (annualAvgAirF - c.refTempF);
	const lag = c.lagBaseDays - c.lagSlopeDaysPerF * (annualAvgAirF - c.refTempF);
	const rad = Math.PI / 180;
	return MID_MONTH_DAYS.map((day) => round(avg + ratio * (maxMonthlyDiffF / 2) * Math.sin(rad * (c.degPerDay * (day - c.dayOffset - lag) - c.phaseDeg)), 1));
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
 * Order: leak or flood → replace; age at or past the high end of the life range → replace ("past-life", whatever the
 * repair costs); share rules; then each replacement sign (signs whose rule applies: "always", or "old" once the unit
 * is old) moves the verdict one step toward replace (repair → borderline → replace).
 * @param {{ageYears:number, expectedLife:number, lifeHigh?:number, repairCost:number, replacementCost:number, tankLeaking:boolean,
 *   flooded?:boolean, signs?:Array<{id:string, when:'always'|'old'}>}} input
 * @param {{ageShare:number, repairShareWhenOld:number, repairShareAlways:number}} rules Thresholds from data/decision-rules.json.
 * @returns {{verdict:'repair'|'replace'|'borderline', reasons:string[], signs:string[], share:number, lifeUsed:number, oldFromYears:number}}
 *   share = repair ÷ replacement; lifeUsed = age ÷ expected life; oldFromYears = age at which the "old" rule starts.
 */
export function repairOrReplace(input, rules) {
	const share = input.replacementCost > 0 ? input.repairCost / input.replacementCost : 1;
	const lifeUsed = input.expectedLife > 0 ? input.ageYears / input.expectedLife : 1;
	const old = input.expectedLife > 0 && input.ageYears >= rules.ageShare * input.expectedLife;
	const signs = (input.signs || []).filter((x) => x.when === 'always' || (x.when === 'old' && old)).map((x) => x.id);
	const extra = { share: round(share, 3), lifeUsed: round(lifeUsed, 3), oldFromYears: round(rules.ageShare * input.expectedLife, 2), signs };
	const out = (verdict, reasons) => ({ verdict, reasons, ...extra });
	if (input.tankLeaking) return out('replace', ['tank-leak']);
	if (input.flooded) return out('replace', ['flooded']);
	if (isNum(input.lifeHigh) && input.lifeHigh > 0 && input.ageYears >= input.lifeHigh) return out('replace', ['past-life']);
	let verdict;
	let reasons;
	if (share > rules.repairShareAlways) [verdict, reasons] = ['replace', ['repair-over-always-share']];
	else if (old && share > rules.repairShareWhenOld) [verdict, reasons] = ['replace', ['old', 'repair-over-old-share']];
	else if (old) [verdict, reasons] = ['borderline', ['old', 'repair-cheap']];
	else if (share > rules.repairShareWhenOld) [verdict, reasons] = ['borderline', ['within-life', 'repair-over-old-share']];
	else [verdict, reasons] = ['repair', ['within-life', 'repair-cheap']];
	if (signs.length && verdict !== 'replace') {
		verdict = verdict === 'repair' ? 'borderline' : 'replace';
		reasons = [...reasons, 'replace-sign'];
	}
	return out(verdict, reasons);
}

/**
 * Yearly energy cost of one water heater type for a household: heat ÷ efficiency × price.
 * Composes annualHeatBtu, annualEnergyUse and annualCost.
 * @param {number} gallonsPerDay
 * @param {number} riseF
 * @param {'gas'|'electric'|'propane'} fuel
 * @param {number} uef
 * @param {{usdPerKwh?:number, usdPerTherm?:number, usdPerGallonPropane?:number, propaneBtuPerGallon?:number}} prices
 * @returns {{use:{kwh?:number, therms?:number, gallons?:number}, year:number, month:number}}
 */
export function yearlyEnergyCost(gallonsPerDay, riseF, fuel, uef, prices) {
	const use = annualEnergyUse(annualHeatBtu(gallonsPerDay, riseF), fuel, uef, prices.propaneBtuPerGallon);
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
 * Energy bought per year for one water heater type: heat ÷ 3412 ÷ UEF (kWh), heat ÷ 100,000 ÷ UEF (therms), or
 * heat ÷ BTU per gallon ÷ UEF (propane gallons; EIA: 1 gallon of propane = 91,452 BTU, data/energy-prices.json).
 * @param {number} heatBtu From annualHeatBtu().
 * @param {'electric'|'gas'|'propane'} fuel
 * @param {number} uef Uniform Energy Factor for the type, from data/uef-ranges.json.
 * @param {number} [btuPerGallonPropane] Needed for propane.
 * @returns {{kwh?:number, therms?:number, gallons?:number}}
 */
export function annualEnergyUse(heatBtu, fuel, uef, btuPerGallonPropane) {
	if (fuel === 'propane') return uef > 0 && btuPerGallonPropane > 0 ? { gallons: round(heatBtu / btuPerGallonPropane / uef, 1) } : { gallons: Infinity };
	if (!(uef > 0)) return fuel === 'gas' ? { therms: Infinity } : { kwh: Infinity };
	return fuel === 'gas'
		? { therms: round(heatBtu / PHYSICS.btuPerTherm / uef, 1) }
		: { kwh: round(heatBtu / PHYSICS.btuPerKwh / uef, 0) };
}

/**
 * Yearly and monthly cost from energy use and price.
 * @param {{kwh?:number, therms?:number, gallons?:number}} use
 * @param {{usdPerKwh?:number, usdPerTherm?:number, usdPerGallonPropane?:number}} prices From data/computed/energy-prices.json or user input.
 * @returns {{year:number, month:number}}
 */
export function annualCost(use, prices) {
	const year = (use.kwh || 0) * (prices.usdPerKwh || 0) + (use.therms || 0) * (prices.usdPerTherm || 0) + (use.gallons || 0) * (prices.usdPerGallonPropane || 0);
	return { year: round(year, 2), month: round(year / PHYSICS.monthsPerYear, 2) };
}

/**
 * Daily hot water use for a household: people × gallons per person, rounded to whole gallons.
 * @param {number} people Household size; anything not above 0 means "not given".
 * @param {number} perPersonGallons Gallons a day per person (data/hot-water-use.json).
 * @param {number} fallbackGallons Used when no household size is given (DOE typical day).
 * @returns {number} gallons per day
 */
export function householdGallonsPerDay(people, perPersonGallons, fallbackGallons) {
	return isNum(people) && people > 0 ? round(people * perPersonGallons) : fallbackGallons;
}

/* ---------- 3.6 Tankless vs tank, cost of ownership ---------- */

/**
 * Net cost of ownership if the horizon ends after `years`: installed cost − credit, plus operating cost (energy +
 * maintenance) every year, plus a full installed cost each time the unit reaches its life before the horizon ends,
 * minus the residual value of the unit still in service: installed × remaining life ÷ life (straight line).
 * Without the residual credit a replacement bought in the last years would count in full although most of its life
 * is still ahead (audit F-03).
 * @param {{installed:number, annualOperating:number, lifeYears:number, credit:number}} option
 * @param {number} years
 * @returns {{total:number, spent:number, residual:number, replacements:number, remainingYears:number}}
 */
function ownershipAt(option, years) {
	const life = option.lifeYears > 0 ? option.lifeYears : Infinity;
	const replacements = Number.isFinite(life) ? Math.floor((years - 1) / life) : 0;
	const age = years - replacements * life;
	const remainingYears = Number.isFinite(life) ? Math.max(0, life - age) : 0;
	const residual = Number.isFinite(life) ? (option.installed * remainingYears) / life : 0;
	const spent = option.installed - (option.credit || 0) + option.annualOperating * years + option.installed * replacements;
	return { total: round(spent - residual), spent: round(spent), residual: round(residual), replacements, remainingYears };
}

/**
 * Total cost of ownership over a horizon (see ownershipAt), plus the cash spent year by year.
 * @param {{installed:number, annualOperating:number, lifeYears:number, credit:number}} option
 * @param {number} years Horizon (default from data).
 * @returns {{total:number, spent:number, residual:number, remainingYears:number, byYear:number[], replacements:number}}
 *   total = net of the residual value; byYear[i] = cash spent by the end of year i + 1 (replacements when they are bought).
 */
export function costOfOwnership(option, years) {
	const byYear = [];
	let cash = option.installed - (option.credit || 0);
	for (let y = 1; y <= years; y++) {
		cash += option.annualOperating;
		if (option.lifeYears > 0 && y % option.lifeYears === 0 && y < years) cash += option.installed;
		byYear.push(round(cash));
	}
	return { ...ownershipAt(option, years), byYear };
}

/**
 * Yearly maintenance cost of a type from sourced inputs: consumables each time × times a year, plus a tool or part
 * bought once and spread over the unit's life, or a part replaced every N years.
 * @param {{perTimeUsd?:number, timesPerYear?:number, onceUsd?:number, everyYears?:number, partUsd?:number}} m
 * @param {number} lifeYears Spreads onceUsd.
 * @returns {number} USD a year, two decimals
 */
export function yearlyMaintenance(m, lifeYears) {
	if (!m) return 0;
	const consumables = (m.perTimeUsd || 0) * (m.timesPerYear || 0);
	const tool = lifeYears > 0 ? (m.onceUsd || 0) / lifeYears : 0;
	const part = m.everyYears > 0 ? (m.partUsd || 0) / m.everyYears : 0;
	return round(consumables + tool + part, 2);
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

/* ---------- Phase 6: heat pump payback (STRATEGY §3.8) ---------- */

/**
 * Simple payback: years for a yearly saving to repay an extra up-front cost.
 * @param {number} extraCost USD more than the alternative (after any rebate). 0 or less → pays back at once.
 * @param {number} yearlySavingUsd USD saved a year (positive = the new option is cheaper to run).
 * @returns {number|null} Years (one decimal), 0 when there is no extra cost, null when it never pays back.
 */
export function simplePaybackYears(extraCost, yearlySavingUsd) {
	if (!isNum(extraCost) || extraCost <= 0) return 0;
	if (!isNum(yearlySavingUsd) || yearlySavingUsd <= 0) return null;
	return round(extraCost / yearlySavingUsd, 1);
}

/**
 * Net saving over a number of years: yearly saving × years − extra up-front cost (no discounting).
 * @param {number} yearlySavingUsd
 * @param {number} years
 * @param {number} extraCost
 * @returns {number} USD, rounded
 */
export function netSavingOver(yearlySavingUsd, years, extraCost) {
	return round(yearlySavingUsd * years - extraCost);
}

/* ---------- Phase 6: thermal expansion tank (STRATEGY §3.8, page 16) ---------- */

/**
 * Share of a tank's water volume that it gains when heated from the table's start temperature to tempF.
 * Linear interpolation between table rows; below the first row it interpolates from 0 at the start temperature,
 * above the last row it uses the last row (no extrapolation).
 * @param {number} tempF Tank setting, °F.
 * @param {{startTempF:number, rows:Array<{tempF:number, factor:number}>}} table data/expansion-tanks.json → expansionFactors
 * @returns {number} e.g. 0.01 for 1.0%
 */
export function expansionFactor(tempF, table) {
	const rows = [{ tempF: table.startTempF, factor: 0 }, ...table.rows].sort((a, b) => a.tempF - b.tempF);
	if (!isNum(tempF) || tempF <= rows[0].tempF) return 0;
	const last = rows[rows.length - 1];
	if (tempF >= last.tempF) return last.factor;
	const i = rows.findIndex((r) => r.tempF >= tempF);
	const a = rows[i - 1];
	const b = rows[i];
	return round(a.factor + ((tempF - a.tempF) / (b.tempF - a.tempF)) * (b.factor - a.factor), 5);
}

/**
 * Design pressure factor (Boyle's law for a precharged tank): (Pmax + atm) ÷ (Pmax − Psupply), gauge pressures.
 * @param {number} maxPsi Highest pressure allowed (e.g. the relief valve setting), psig.
 * @param {number} supplyPsi Static supply pressure (= tank precharge), psig.
 * @param {number} atmosphericPsi Atmospheric pressure, psi (data).
 * @returns {number|null} null when the maximum is not above the supply pressure.
 */
export function designPressureFactor(maxPsi, supplyPsi, atmosphericPsi) {
	if (!isNum(maxPsi) || !isNum(supplyPsi) || maxPsi <= supplyPsi) return null;
	return round((maxPsi + atmosphericPsi) / (maxPsi - supplyPsi), 3);
}

/**
 * Thermal expansion tank size for a storage water heater.
 * expanded = heater gallons × expansion factor; required = expanded × DPF; the smallest tank whose total volume
 * ≥ required and whose acceptance volume ≥ expanded. When either figure is within headroomShare of that tank's
 * limit, the next tank is returned as a safer pick.
 * @param {{heaterGallons:number, tempF:number, supplyPsi:number, maxPsi:number}} i
 * @param {{expansionFactors:object, atmosphericPsi:number, tanks:Array<{id:string,totalGallons:number,acceptanceGallons:number}>, headroomShare:number}} d
 * @returns {{factor:number, expanded:number, dpf:number|null, required:number|null, tank:object|null, safer:object|null, tooBig:boolean}}
 */
export function expansionTankSizing(i, d) {
	const factor = expansionFactor(i.tempF, d.expansionFactors);
	const expanded = round(i.heaterGallons * factor, 3);
	const dpf = designPressureFactor(i.maxPsi, i.supplyPsi, d.atmosphericPsi);
	if (dpf === null) return { factor, expanded, dpf, required: null, tank: null, safer: null, tooBig: false };
	const required = round(expanded * dpf, 2);
	const tanks = [...d.tanks].sort((a, b) => a.totalGallons - b.totalGallons);
	const idx = tanks.findIndex((t) => t.totalGallons >= required && t.acceptanceGallons >= expanded);
	if (idx < 0) return { factor, expanded, dpf, required, tank: null, safer: null, tooBig: true };
	const tank = tanks[idx];
	const h = isNum(d.headroomShare) ? d.headroomShare : 0;
	const tight = required > tank.totalGallons * (1 - h) || expanded > tank.acceptanceGallons * (1 - h);
	const safer = tight && tanks[idx + 1] ? tanks[idx + 1] : null;
	return { factor, expanded, dpf, required, tank, safer, tooBig: false };
}

/* ---------- Phase 6b batch 2: hot water runs out fast ---------- */

/**
 * Share of hot water in a mix at the tap (energy balance, no losses): (mix − cold) ÷ (hot − cold), clamped to 0–1.
 * Example: 105 °F shower, 120 °F tank, 50 °F inlet → 55 ÷ 70 = 0.786 (79% hot).
 * @param {number} mixF Temperature at the tap, °F.
 * @param {number} hotF Tank (hot supply) temperature, °F.
 * @param {number} coldF Incoming cold water temperature, °F.
 * @returns {number|null} 0–1, or null when the hot supply is not above the cold.
 */
export function hotShareOfMix(mixF, hotF, coldF) {
	if (!isNum(mixF) || !isNum(hotF) || !isNum(coldF) || hotF <= coldF) return null;
	return round(Math.min(1, Math.max(0, (mixF - coldF) / (hotF - coldF))), 4);
}

/**
 * Gallons of tank (hot) water a fixture draws: mixed flow × minutes × hot share.
 * @param {number} gpm Mixed flow at the fixture, gallons per minute.
 * @param {number} minutes Length of use.
 * @param {number|null} hotShare From hotShareOfMix.
 * @returns {number|null}
 */
export function hotGallonsDrawn(gpm, minutes, hotShare) {
	if (!isNum(gpm) || !isNum(minutes) || !isNum(hotShare)) return null;
	return round(gpm * minutes * hotShare, 2);
}

/**
 * Cold resistance of a resistive heating element from its rating: R = V² ÷ W (Ohm's law, P = V² ÷ R).
 * A good element reads close to this; a reading near zero means a short, no reading means it is open (burned out).
 * @param {number} volts Rated voltage on the element and the heater's data plate.
 * @param {number} watts Rated wattage.
 * @returns {number|null} Ohms, or null for bad input.
 */
export function elementResistanceOhms(volts, watts) {
	if (!isNum(volts) || !isNum(watts) || volts <= 0 || watts <= 0) return null;
	return round((volts * volts) / watts, 2);
}

/**
 * How much more mixed water a tank delivers at a hotter setting, for the same mixed temperature:
 * the tank share falls, so each tank gallon stretches further. Extra = share(base) ÷ share(hotter) − 1.
 * @param {number|null} baseShare hotShareOfMix at the base setting.
 * @param {number|null} hotterShare hotShareOfMix at the hotter setting.
 * @returns {number|null} 0.25 = 25% more mixed water.
 */
export function extraMixedWaterShare(baseShare, hotterShare) {
	if (!isNum(baseShare) || !isNum(hotterShare) || hotterShare <= 0) return null;
	return round(baseShare / hotterShare - 1, 4);
}

/**
 * Gas flow an appliance needs, in cubic feet per hour: input BTU/h ÷ heating value (BTU per cubic foot).
 * Fuel gas code tables are in cubic feet per hour; Rinnai's table uses 1,000 BTU per cubic foot of natural gas.
 * @param {number} btuh Appliance input rating from its rating plate.
 * @param {number} btuPerCf Heating value of the gas.
 * @returns {number|null}
 */
export function gasCfh(btuh, btuPerCf) {
	if (!isNum(btuh) || !isNum(btuPerCf) || btuh < 0 || btuPerCf <= 0) return null;
	return round(btuh / btuPerCf, 1);
}

/**
 * Pipe-size lookup in a fuel gas code capacity table. Lengths are rounded UP to the next row (a run of 52 ft is read
 * in the 60 ft row, as in Rinnai TB-111), then the smallest size whose capacity covers the flow is chosen.
 * @param {number} cfh Flow needed (cubic feet per hour).
 * @param {number} lengthFt Equivalent length of the run from the meter.
 * @param {Array<{lengthFt:number, cfh:number[]}>} rows Capacity rows, one value per size.
 * @param {string[]} sizes Nominal size labels in the same order as cfh[].
 * @returns {{size:string|null, rowFt:number|null}} size null = larger than the sizes given; rowFt null = run longer than the table.
 */
export function gasPipeSize(cfh, lengthFt, rows, sizes) {
	const row = [...rows].sort((a, b) => a.lengthFt - b.lengthFt).find((r) => r.lengthFt >= lengthFt);
	if (!row || !isNum(cfh)) return { size: null, rowFt: null };
	const i = row.cfh.findIndex((c) => c >= cfh);
	return { size: i >= 0 ? sizes[i] : null, rowFt: row.lengthFt };
}

/**
 * Longest table length at which one pipe size still carries the flow (the reverse lookup that manufacturers
 * publish as "maximum equivalent length").
 * @param {number} cfh
 * @param {Array<{lengthFt:number, cfh:number[]}>} rows
 * @param {number} sizeIndex Column of the size in cfh[].
 * @returns {number|null} feet, or null when even the shortest row is too small.
 */
export function gasMaxLengthFt(cfh, rows, sizeIndex) {
	const ok = rows.filter((r) => r.cfh[sizeIndex] >= cfh).map((r) => r.lengthFt);
	return ok.length ? Math.max(...ok) : null;
}

/**
 * Power of a resistive heater on a lower voltage: P = P_rated × (V ÷ V_rated)², because resistance stays the same.
 * A 240 V tankless unit on 208 V gives about 75% of its power (Rheem: "25% reduced temperature output").
 * @param {number} kw Rated power.
 * @param {number} ratedVolts
 * @param {number} actualVolts
 * @returns {number|null}
 */
export function powerAtVoltage(kw, ratedVolts, actualVolts) {
	if (!isNum(kw) || !isNum(ratedVolts) || !isNum(actualVolts) || ratedVolts <= 0) return null;
	return round(kw * (actualVolts / ratedVolts) ** 2, 3);
}

/**
 * Solar water heater for one household, with the equations of DOE FEMP's solar hot water calculator
 * (Scenario 1: the collector covers the whole load on the sunniest day).
 * Load (therms/day) = gal/day × 8.33 × ΔT ÷ 100,000; area (ft²) = load ÷ η_solar ÷ I_max × 100,000;
 * solar heat (therms/yr) = area × I_ave × η_solar × 365 × utilization ÷ 100,000; pump (kWh/yr) = area × 0.001 × 365.
 * @param {{ gallonsPerDay:number, riseF:number, iMaxBtuFt2Day:number, iAveBtuFt2Day:number }} i
 * @param {{ etaSolar:number, utilization:number, btuPerGallonF:number, pumpKwhPerFt2Day:number }} c FEMP defaults
 * @returns {{ loadThermsYear:number, areaFt2:number, solarThermsYear:number, solarShare:number, pumpKwhYear:number }}
 */
export function solarWaterHeating(i, c) {
	const loadThermsDay = (i.gallonsPerDay * c.btuPerGallonF * i.riseF) / 100000;
	const areaFt2 = (loadThermsDay / c.etaSolar / i.iMaxBtuFt2Day) * 100000;
	const solarThermsYear = (areaFt2 * i.iAveBtuFt2Day * c.etaSolar * 365 * c.utilization) / 100000;
	const loadThermsYear = loadThermsDay * 365;
	return { loadThermsYear, areaFt2, solarThermsYear, solarShare: solarThermsYear / loadThermsYear, pumpKwhYear: areaFt2 * c.pumpKwhPerFt2Day * 365 };
}

/**
 * Yearly saving of a solar system over its backup alone: the fuel the backup no longer burns
 * (solar heat ÷ backup UEF), minus the pump's electricity and FEMP's yearly upkeep share of the system cost.
 * @param {{ solarThermsYear:number, pumpKwhYear:number }} s Output of solarWaterHeating()
 * @param {'electric'|'gas'} fuel Backup fuel
 * @param {number} uef Backup efficiency
 * @param {{ usdPerKwh:number, usdPerTherm:number }} prices
 * @param {number} upkeepUsd Yearly upkeep (FEMP: 0.5% of the installed cost)
 */
export function solarYearlySaving(s, fuel, uef, prices, upkeepUsd) {
	const fuelSaved = fuel === 'electric' ? ((s.solarThermsYear * 100000) / PHYSICS.btuPerKwh / uef) * prices.usdPerKwh : (s.solarThermsYear / uef) * prices.usdPerTherm;
	return fuelSaved - s.pumpKwhYear * prices.usdPerKwh - upkeepUsd;
}
