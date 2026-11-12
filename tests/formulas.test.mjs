/**
 * Unit tests for assets/js/formulas.js (PHASES.md Phase 1, task 3): at least one test per formula, plus edge cases.
 * Hand-checked numbers are written out in the comments so a reviewer can redo them on a calculator.
 * Run: npm test
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import * as F from '../assets/js/formulas.js';

const data = (f) => JSON.parse(readFileSync(new URL(`../data/${f}.json`, import.meta.url), 'utf8'));
const near = (a, b, tol = 1e-9) => assert.ok(Math.abs(a - b) <= tol, `${a} ≉ ${b} (±${tol})`);

test('summarize: low / median / high; even count averages the middle two; empty → null', () => {
	assert.deepEqual(F.summarize([3, 1, 2]), { low: 1, typical: 2, high: 3, n: 3 });
	assert.deepEqual(F.summarize([4, 1, 3, 2]), { low: 1, typical: 2.5, high: 4, n: 4 });
	assert.equal(F.summarize([]), null);
	assert.deepEqual(F.summarize([5, NaN, null]), { low: 5, typical: 5, high: 5, n: 1 });
});

/* ---------- serial decoder ---------- */
const sf = data('serial-formats').brands;

test('decodeSerial: Bradford White letter cycle picks the latest year not in the future', () => {
	// D = 4th letter → 1967/1987/2007/2027; F = June. In 2026, 2027 is in the future → 2007.
	const r = F.decodeSerial('DF1234567', sf['bradford-white'].patterns, 2026);
	assert.equal(r.year, 2007);
	assert.equal(r.month, 6);
	assert.deepEqual(r.otherYears, [1987, 1967]);
	// Year letters skip I, O, Q, R, U, V: S is the 15th letter → 2004 + 14 = 2018; L = November.
	assert.deepEqual([F.decodeSerial('SL0000000', sf['bradford-white'].patterns, 2026).year, F.decodeSerial('SL0000000', sf['bradford-white'].patterns, 2026).month], [2018, 11]);
});

test('decodeSerial: Bradford White rejects excluded letters', () => {
	assert.equal(F.decodeSerial('IA1234567', sf['bradford-white'].patterns, 2026), null); // I is not a year letter
	assert.equal(F.decodeSerial('AN1234567', sf['bradford-white'].patterns, 2026), null); // N is not a month letter
});

test('decodeSerial: Rheem MMYY (0794 → July 1994; 1121 → November 2021)', () => {
	assert.deepEqual(
		(({ year, month }) => ({ year, month }))(F.decodeSerial('0794F12345', sf.rheem.patterns, 2026)),
		{ year: 1994, month: 7 },
	);
	const r = F.decodeSerial('1121M00123', sf.rheem.patterns, 2026);
	assert.equal(r.year, 2021);
	assert.equal(r.month, 11);
	assert.equal(F.decodeSerial('1394000000', sf.rheem.patterns, 2026), null); // month 13
});

test('decodeSerial: A. O. Smith YYWW since 2008, letter-month before 2008', () => {
	const a = F.decodeSerial('0805A123456', sf['ao-smith'].patterns, 2026);
	assert.deepEqual([a.year, a.week, a.month, a.patternId], [2008, 5, 1, 'aos-yyww']); // week 5 → floor((5−1)×12/52)+1 = January
});

test('decodeSerial: A. O. Smith pre-2008 letter month (H06 → August 2006), not misread as YYWW', () => {
	const r = F.decodeSerial('MH06123456', sf['ao-smith'].patterns, 2026);
	assert.deepEqual([r.year, r.month, r.patternId], [2006, 8, 'aos-letter-month-yy']);
	assert.equal(F.decodeSerial('0612A123456', sf['ao-smith'].patterns, 2026), null); // 2006 YYWW is not a valid A. O. Smith code
});

test('decodeSerial: State and American, two-digit century choice', () => {
	const s = F.decodeSerial('C05123456', sf.state.patterns, 2026);
	assert.deepEqual([s.year, s.month], [2005, 3]);
	const am = F.decodeSerial('9746123456', sf.american.patterns, 2026);
	assert.deepEqual([am.year, am.week], [1997, 46]);
	assert.equal(F.decodeSerial('', sf.american.patterns, 2026), null);
});

test('monthFromWeek: week 1 → Jan, week 26 → Jun, week 52/53 → Dec', () => {
	assert.equal(F.monthFromWeek(1), 1);
	assert.equal(F.monthFromWeek(26), 6);
	assert.equal(F.monthFromWeek(52), 12);
	assert.equal(F.monthFromWeek(53), 12);
});

test('ageYears: months count; unknown month = July; never negative', () => {
	assert.equal(F.ageYears({ year: 2016, month: 11 }, new Date(2026, 10, 12)), 10); // Nov 2016 → Nov 2026
	assert.equal(F.ageYears({ year: 2016, month: null }, new Date(2026, 10, 12)), 10.3); // Jul 2016 → Nov 2026 = 10 y 4 m
	assert.equal(F.ageYears({ year: 2027, month: 1 }, new Date(2026, 10, 12)), 0);
});

test('adjustedLifespan: shifts all three values and respects the floor', () => {
	assert.deepEqual(F.adjustedLifespan({ low: 10, typical: 13, high: 15 }, [-1, -1, 1], 5), { low: 9, typical: 12, high: 14 });
	assert.deepEqual(F.adjustedLifespan({ low: 10, typical: 13, high: 15 }, [-20], 5), { low: 5, typical: 5, high: 5 });
	assert.deepEqual(F.adjustedLifespan({ low: 10, typical: 13, high: 15 }, [], 5), { low: 10, typical: 13, high: 15 });
});

test('lifeStatus: bands at 60% and 90% of typical life; remaining never negative', () => {
	const life = { low: 10, typical: 13, high: 15 };
	const bands = { amberFrom: 0.6, redFrom: 0.9 };
	assert.equal(F.lifeStatus(5, life, bands).status, 'ok'); // 5/13 = 38%
	assert.equal(F.lifeStatus(7.8, life, bands).status, 'warn'); // 60%
	const old = F.lifeStatus(16, life, bands);
	assert.equal(old.status, 'bad');
	assert.deepEqual(old.remaining, { min: 0, max: 0 });
	assert.deepEqual(F.lifeStatus(8, life, bands).remaining, { min: 2, max: 7 });
});

/* ---------- tankless ---------- */

test('totalGpm and temperatureRise (DOE example: 0.75 + 2.5 = 3.25 GPM, 120 − 50 = 70 °F)', () => {
	assert.equal(F.totalGpm([{ count: 1, gpm: 0.75 }, { count: 1, gpm: 2.5 }]), 3.25);
	assert.equal(F.totalGpm([{ count: 2, gpm: 2.5 }, { count: 0, gpm: 2.2 }, { count: null, gpm: 1 }]), 5);
	assert.equal(F.temperatureRise(120, 50), 70);
	assert.equal(F.temperatureRise(120, 130), 0); // inlet warmer than setpoint
});

test('outputBtuh, gasInputBtuh, electricKw, btuhFromKw', () => {
	// 3.25 GPM × 8.34 × 60 × 70 = 113,841 BTU/h
	near(F.outputBtuh(3.25, 70), 113841, 1e-6);
	// at UEF 0.95: 113,841 / 0.95 = 119,832.6
	near(F.gasInputBtuh(113841, 0.95), 119832.63, 0.01);
	assert.equal(F.gasInputBtuh(1000, 0), Infinity);
	// 113,841 / 3412 = 33.36 kW
	near(F.electricKw(113841), 33.365, 0.001);
	assert.equal(F.btuhFromKw(36), 122832);
});

test('electricAmps: 36 kW at 240 V = 150 A (matches Rheem RETEX-36 spec sheet)', () => {
	assert.equal(F.electricAmps(36, 240), 150);
	assert.equal(F.electricAmps(10, 0), Infinity);
});

test('referenceUnitFor: smallest reference unit with enough kW, null when none', () => {
	const units = data('electrical').referenceUnits;
	assert.equal(F.referenceUnitFor(25, units).model, 'RETEX-27');
	assert.equal(F.referenceUnitFor(18, units).model, 'RETEX-18');
	assert.equal(F.referenceUnitFor(40, units), null);
});

test('maxGpmByRise and riseAtGpm reproduce the RETEX-36 chart (98 °F at 2.5 GPM, 41 °F at 6 GPM)', () => {
	const out = F.btuhFromKw(36);
	assert.equal(Math.round(F.riseAtGpm(out, 2.5)), 98);
	assert.equal(Math.round(F.riseAtGpm(out, 6)), 41);
	assert.equal(F.riseAtGpm(out, 0), Infinity);
	const chart = F.maxGpmByRise(out, [41, 98]);
	near(chart[0].gpm, 6, 0.02);
	near(chart[1].gpm, 2.5, 0.02);
	assert.equal(F.maxGpmByRise(out, [0])[0].gpm, Infinity);
});

test('mainsWaterTemp: Christensen–Burch correlation (hand check for 50 °F air, 40 °F swing)', () => {
	const c = data('groundwater-temp-by-state').correlation;
	// avg = 50 + 6 = 56; ratio = 0.4 + 0.01 × (50 − 44) = 0.46; swing = 0.46 × 20 = 9.2 → coldest 46.8, warmest 65.2
	assert.deepEqual(F.mainsWaterTemp(50, 40, c), { annualAvg: 56, coldestMonth: 46.8, warmestMonth: 65.2 });
	// no seasonal swing → coldest = average
	assert.deepEqual(F.mainsWaterTemp(70, 0, c), { annualAvg: 76, coldestMonth: 76, warmestMonth: 76 });
});

/* ---------- tank size ---------- */

test('peakHourDemand: AHRI worksheet example = 70 gallons', () => {
	const a = data('peak-hour-demand').activities;
	assert.equal(F.peakHourDemand([{ count: 3, gallons: a.shower.gallons }, { count: 1, gallons: a.shaving.gallons }, { count: 1, gallons: a.shampoo.gallons }, { count: 1, gallons: a.handDishwashing.gallons }]), 70);
	assert.equal(F.peakHourDemand([]), 0);
});

test('tankForFhr: smallest size whose typical FHR meets the need; falls back to "some models" and "none"', () => {
	const table = [
		{ fuel: 'gas', nominalGallons: 40, fhrLow: 64, fhrTypical: 68, fhrHigh: 84 },
		{ fuel: 'gas', nominalGallons: 50, fhrLow: 75, fhrTypical: 81, fhrHigh: 86 },
		{ fuel: 'electric', nominalGallons: 50, fhrLow: 61, fhrTypical: 62, fhrHigh: 63 },
	];
	assert.deepEqual((({ nominalGallons, fits }) => ({ nominalGallons, fits }))(F.tankForFhr(66, 'gas', table)), { nominalGallons: 40, fits: 'typical' });
	assert.deepEqual((({ nominalGallons, fits }) => ({ nominalGallons, fits }))(F.tankForFhr(70, 'gas', table)), { nominalGallons: 50, fits: 'typical' });
	assert.deepEqual((({ nominalGallons, fits }) => ({ nominalGallons, fits }))(F.tankForFhr(85, 'gas', table)), { nominalGallons: 50, fits: 'some-models' });
	assert.deepEqual((({ nominalGallons, fits }) => ({ nominalGallons, fits }))(F.tankForFhr(90, 'gas', table)), { nominalGallons: 50, fits: 'none' });
	assert.equal(F.tankForFhr(50, 'heatpump', table), null);
});

test('drawPatternForFhr: DOE Appendix E bins (FHR 50 → low, 51 → medium, 75 → high)', () => {
	const p = data('hot-water-use').drawPatterns;
	assert.equal(F.drawPatternForFhr(10, p).id, 'very-small');
	assert.equal(F.drawPatternForFhr(50, p).id, 'low');
	assert.equal(F.drawPatternForFhr(51, p).id, 'medium');
	assert.equal(F.drawPatternForFhr(75, p).id, 'high');
	assert.equal(F.drawPatternForFhr(-1, p), null);
});

/* ---------- repair or replace ---------- */

test('repairOrReplace: leak, 50% rule, old + 25%, borderline and repair', () => {
	const rules = data('decision-rules').repairOrReplace;
	const base = { ageYears: 5, expectedLife: 13, repairCost: 200, replacementCost: 2000, tankLeaking: false };
	assert.equal(F.repairOrReplace({ ...base, tankLeaking: true }).verdict ?? F.repairOrReplace({ ...base, tankLeaking: true }, rules).verdict, 'replace');
	assert.equal(F.repairOrReplace({ ...base, repairCost: 1100 }, rules).verdict, 'replace'); // 55% > 50%
	assert.equal(F.repairOrReplace({ ...base, ageYears: 10, repairCost: 600 }, rules).verdict, 'replace'); // 10 ≥ 9.75 and 30% > 25%
	assert.equal(F.repairOrReplace({ ...base, ageYears: 10, repairCost: 200 }, rules).verdict, 'borderline'); // old, cheap repair
	assert.equal(F.repairOrReplace({ ...base, repairCost: 600 }, rules).verdict, 'borderline'); // young, 30%
	assert.deepEqual(F.repairOrReplace(base, rules), { verdict: 'repair', reasons: ['young', 'repair-cheap'] });
	assert.equal(F.repairOrReplace({ ...base, replacementCost: 0 }, rules).verdict, 'replace'); // no replacement price → share = 1
});

/* ---------- installed cost ---------- */

test('installedCost: unit is national, labor and extras scale by the regional multiplier', () => {
	const r = F.installedCost({ low: 500, typical: 700, high: 900 }, { low: 400, typical: 600, high: 800 }, [{ id: 'permit', low: 50, typical: 100, high: 150 }], 1.1);
	// typical = 700 + 600 × 1.1 + 100 × 1.1 = 700 + 660 + 110 = 1,470
	assert.equal(r.typical, 1470);
	assert.equal(r.low, 500 + 440 + 55);
	assert.equal(r.items.length, 3);
	assert.equal(F.installedCost({ low: 1, typical: 1, high: 1 }, { low: 1, typical: 1, high: 1 }, [], undefined).typical, 2); // missing multiplier → 1
});

/* ---------- operating cost ---------- */

test('gasUsdPerTherm and usdPerKwh (EIA unit conversion)', () => {
	// $15.34/Mcf at 1,036 BTU/cf → 10.36 therms/Mcf → $1.4807/therm
	near(F.gasUsdPerTherm(15.34, 1036), 1.48069, 1e-5);
	assert.equal(F.gasUsdPerTherm(10, 0), Infinity);
	assert.equal(F.usdPerKwh(17.23), 0.1723);
});

test('annualHeatBtu, annualEnergyUse, annualCost (DOE FEMP cross-check: 3,437 kWh within 1%)', () => {
	// 55 gal/day × 365 × 8.34 × 67 = 11,217,508.5 BTU
	near(F.annualHeatBtu(55, 67), 11217508.5, 1e-6);
	const kwh = F.annualEnergyUse(F.annualHeatBtu(55, 67), 'electric', 0.95).kwh;
	assert.ok(Math.abs(kwh - 3437) / 3437 < 0.01, `kWh ${kwh}`);
	assert.deepEqual(F.annualEnergyUse(10000000, 'gas', 0.5), { therms: 200 });
	assert.deepEqual(F.annualEnergyUse(1, 'gas', 0), { therms: Infinity });
	assert.deepEqual(F.annualCost({ kwh: 1000 }, { usdPerKwh: 0.15 }), { year: 150, month: 12.5 });
	assert.deepEqual(F.annualCost({ therms: 200 }, { usdPerTherm: 1.5 }), { year: 300, month: 25 });
	assert.deepEqual(F.annualCost({}, {}), { year: 0, month: 0 });
});

/* ---------- cost of ownership ---------- */

test('costOfOwnership: replacement inside the horizon, credit only once', () => {
	// 1,000 installed − 200 credit + 100/yr; life 5 in a 10-year horizon → one replacement at year 5 (+1,000), none at year 10.
	const r = F.costOfOwnership({ installed: 1000, annualOperating: 100, lifeYears: 5, credit: 200 }, 10);
	assert.equal(r.replacements, 1);
	assert.equal(r.total, 800 + 1000 + 1000);
	assert.equal(r.byYear.length, 10);
	assert.equal(r.byYear[4], 800 + 500 + 1000);
	assert.equal(F.costOfOwnership({ installed: 500, annualOperating: 0, lifeYears: 0, credit: 0 }, 3).replacements, 0);
});

test('breakEvenYear: first year B is at or below A; null if never', () => {
	assert.equal(F.breakEvenYear([100, 200, 300], [250, 260, 270]), 3);
	assert.equal(F.breakEvenYear([100, 200], [50, 60]), 1);
	assert.equal(F.breakEvenYear([100, 200], [150, 250]), null);
});

/* ---------- every serial example in the data file decodes ---------- */
test('serial-formats.json: every brand pattern matches its own example', () => {
	for (const [brand, b] of Object.entries(sf)) {
		for (const p of b.patterns) {
			const sample = p.example.split(' ')[0].replace(/\*/g, 'X'); // * = any character
			const r = F.decodeSerial(sample, [p], 2026);
			assert.ok(r, `${brand}/${p.id} did not decode its example ${sample}`);
		}
	}
});

/* ---------- Phase 2 additions ---------- */

test('decodeSerial: Rheem plant letter + week + year matches Rheem/Ruud official lookup (checked 2026-11-13)', () => {
	const p = sf.rheem.patterns;
	// Official lookup: Q431912345 → 10/18/2019; week 43 → month floor(42 × 12 / 52) + 1 = 10.
	assert.deepEqual([F.decodeSerial('Q431912345', p, 2026).year, F.decodeSerial('Q431912345', p, 2026).month], [2019, 10]);
	// M101812345 → 03/07/2018; week 10 → month 3.
	assert.deepEqual([F.decodeSerial('M101812345', p, 2026).year, F.decodeSerial('M101812345', p, 2026).month], [2018, 3]);
	// Q052412345 → 01/25/2024; week 5 → month 1.
	assert.deepEqual([F.decodeSerial('Q052412345', p, 2026).year, F.decodeSerial('Q052412345', p, 2026).month], [2024, 1]);
	// Old all-digit format still decodes as month + year.
	assert.equal(F.decodeSerial('0794123456', p, 2026).month, 7);
});

test('decodeSerial: Navien year, month character, day (CPSC example 18709 = July 9, 2018; X = October)', () => {
	const p = sf.navien.patterns;
	const r = F.decodeSerial('7410A18709ABCD', p, 2026);
	assert.deepEqual([r.year, r.month], [2018, 7]);
	assert.equal(F.decodeSerial('7410A18X14ABCD', p, 2026).month, 10);
	assert.equal(F.decodeSerial('7410A18014ABCD', p, 2026), null); // 0 is not a month
});

test('ageEstimate: combines age, adjusted life and status', () => {
	// June 2018 built, 2026-11-13: age 2026 + 10/12 − (2018 + 5/12) = 8.4; life 13 − 1 − 1 = 11 → 76% → amber.
	const e = F.ageEstimate({ year: 2018, month: 6 }, new Date('2026-11-13T12:00:00Z'), { low: 10, typical: 13, high: 15 }, [-1, -1], 5, { amberFrom: 0.6, redFrom: 0.9 });
	assert.equal(e.age, 8.4);
	assert.deepEqual(e.life, { low: 8, typical: 11, high: 13 });
	assert.equal(e.status, 'warn');
	assert.deepEqual(e.remaining, { min: 0, max: 4.6 });
});

test('smallestAtLeast and unitsNeeded', () => {
	assert.deepEqual(F.smallestAtLeast(150001, [{ b: 199000 }, { b: 150000 }, { b: 180000 }], 'b'), { b: 180000 });
	assert.equal(F.smallestAtLeast(200000, [{ b: 199000 }], 'b'), null);
	assert.equal(F.unitsNeeded(342379, 199000), 2);
	assert.equal(F.unitsNeeded(199000, 199000), 1);
	assert.equal(F.unitsNeeded(10, 0), Infinity);
});

test('tanklessSizing: two 2.5 GPM showers, 50 °F in, 120 °F out, UEF 0.95 (hand-checked)', () => {
	// 5 GPM × 8.34 × 60 × 70 = 175,140 BTU/h; ÷ 0.95 = 184,358; ÷ 3412 = 51.33 kW; × 1000 ÷ 240 = 213.9 A.
	const el = data('electrical');
	const s = F.tanklessSizing({ fixtures: [{ count: 2, gpm: 2.5 }], setpointF: 120, inletF: 50, gasEfficiency: 0.95, volts: 240, gasSizesBtuh: [120000, 150000, 180000, 199000], electricUnits: el.referenceUnits });
	assert.equal(s.gpm, 5);
	assert.equal(s.rise, 70);
	assert.equal(s.outputBtuh, 175140);
	assert.equal(s.gasInputBtuh, 184358);
	assert.equal(s.gasSizeBtuh, 199000);
	assert.equal(s.kw, 51.3);
	assert.equal(s.amps, 214);
	assert.equal(s.electricUnit, null);
	assert.equal(s.electricUnitsAtLargest, 2);
});

test('tanklessSizing: one shower, warm climate fits one electric unit', () => {
	// 2.5 GPM, 120 − 70 = 50 °F rise: 2.5 × 500.4 × 50 = 62,550 BTU/h ÷ 3412 = 18.33 kW → RETEX-24 (18 kW is too small).
	const el = data('electrical');
	const s = F.tanklessSizing({ fixtures: [{ count: 1, gpm: 2.5 }], setpointF: 120, inletF: 70, gasEfficiency: 0.95, volts: 240, gasSizesBtuh: [120000, 199000], electricUnits: el.referenceUnits });
	assert.equal(s.kw, 18.3);
	assert.equal(s.electricUnit.model, 'RETEX-24');
});
