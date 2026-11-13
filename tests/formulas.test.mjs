/**
 * Unit tests for assets/js/formulas.js (PHASES.md Phase 1, task 3): at least one test per formula, plus edge cases.
 * Hand-checked numbers are written out in the comments so a reviewer can redo them on a calculator.
 * Run: npm test
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import * as F from '../assets/js/formulas.js';
import { describe as describeCost } from '../assets/js/cost-calculator.js';
import { describe as describeTco } from '../assets/js/tankless-vs-tank.js';
import { describe as describeOp } from '../assets/js/gas-vs-electric.js';

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
	assert.equal(F.repairOrReplace({ ...base, tankLeaking: true }, rules).verdict, 'replace');
	assert.equal(F.repairOrReplace({ ...base, repairCost: 1100 }, rules).verdict, 'replace'); // 55% > 50%
	assert.equal(F.repairOrReplace({ ...base, ageYears: 10, repairCost: 600 }, rules).verdict, 'replace'); // 10 ≥ 9.75 and 30% > 25%
	assert.equal(F.repairOrReplace({ ...base, ageYears: 10, repairCost: 200 }, rules).verdict, 'borderline'); // old, cheap repair
	assert.equal(F.repairOrReplace({ ...base, repairCost: 600 }, rules).verdict, 'borderline'); // young, 30%
	{ const x = F.repairOrReplace(base, rules); assert.equal(x.verdict, 'repair'); assert.deepEqual(x.reasons, ['young', 'repair-cheap']); assert.equal(x.share, 0.1); assert.equal(x.oldFromYears, 9.75); }
	assert.deepEqual(F.repairOrReplace({ ...base, flooded: true }, rules).reasons, ['flooded']);
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

test('decodeSerial: older Navien hyphenated tag with a four-digit year (third-party examples)', () => {
	const p = sf.navien.patterns;
	const a = F.decodeSerial('8881-20100520-3026', p, 2026);
	assert.deepEqual([a.year, a.month, a.patternId], [2010, 5, 'navien-dash-yyyymmdd']);
	// 9262-20081208-1027 would fit the current layout as 2000; the current layout starts in 2010, so the old one wins.
	const b = F.decodeSerial('9262-20081208-1027', p, 2026);
	assert.deepEqual([b.year, b.month, b.patternId], [2008, 12, 'navien-dash-yyyymmdd']);
	assert.equal(F.decodeSerial('9545J1152752044', p, 2026).patternId, 'navien-yymdd');
	// A four-digit year in the future is rejected.
	assert.equal(F.decodeSerial('1234-20990101-0000', p, 2026), null);
});

test('decodeSerial: Whirlpool serials use the American year-week code (CPSC 2018 range 1115–1631)', () => {
	const p = sf.american.patterns;
	assert.deepEqual([F.decodeSerial('1104T403904', p, 2026).year, F.decodeSerial('1104T403904', p, 2026).week], [2011, 4]);
	assert.equal(F.decodeSerial('1905T000001', p, 2026).month, 1); // week 5 is late January, not May
	assert.deepEqual([F.decodeSerial('9950123040', p, 2026).year, F.decodeSerial('9950123040', p, 2026).month], [1999, 12]);
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

/* ---------- Phase 2b additions ---------- */

test('decodeSerial: Rheem older tags (prefix letters, then month and year) and current serials still read as week + year', () => {
	const p = sf.rheem.patterns;
	const at = (s) => (({ year, month, patternId }) => ({ year, month, patternId }))(F.decodeSerial(s, p, 2026));
	assert.deepEqual(at('R0804B10488'), { year: 2004, month: 8, patternId: 'rheem-letter-mmyy-plant' }); // InspectApedia style 1
	assert.deepEqual(at('RH0806B17262'), { year: 2006, month: 8, patternId: 'rheem-prefix-mmyy' }); // Rheemglas Fury tag
	// Official Rheem/Ruud lookup answers recorded 2026-11-13: M302112345 → 07/27/2021, Q151512345 → 04/09/2015.
	assert.deepEqual(at('M302112345'), { year: 2021, month: 7, patternId: 'rheem-plant-wwyy' });
	assert.deepEqual(at('Q151512345'), { year: 2015, month: 4, patternId: 'rheem-plant-wwyy' });
});

test('decodeSerial: A. O. Smith CPSC recall ranges (1115–1631 = year + week; L07A = November 2007)', () => {
	const p = sf['ao-smith'].patterns;
	assert.deepEqual([F.decodeSerial('1115A000001', p, 2026).year, F.decodeSerial('1115A000001', p, 2026).month], [2011, 4]);
	assert.deepEqual([F.decodeSerial('1631A000001', p, 2026).year, F.decodeSerial('1631A000001', p, 2026).month], [2016, 7]);
	assert.deepEqual([F.decodeSerial('L07A071460', p, 2026).year, F.decodeSerial('L07A071460', p, 2026).month], [2007, 11]);
	assert.equal(F.decodeSerial('N07A071460', p, 2026), null); // N would be a 13th month letter
});

test('decodeSerial: State pre-2004 two-letter style and one-letter style', () => {
	const p = sf.state.patterns;
	assert.deepEqual([F.decodeSerial('AF04A093001', p, 2026).year, F.decodeSerial('AF04A093001', p, 2026).month], [2004, 6]);
	assert.deepEqual([F.decodeSerial('M07A009387', p, 2026).year, F.decodeSerial('M07A009387', p, 2026).month], [2007, 12]);
	assert.equal(F.decodeSerial('AF09A093001', p, 2026), null); // two-letter style ended in 2004
});

test('brand-pages.json: every hand-check decodes to the date its source gives', () => {
	const bp = data('brand-pages').brands;
	for (const [brand, b] of Object.entries(bp)) {
		for (const c of b.checks) {
			const m = F.decodeSerial(c.serial, sf[b.code || brand].patterns, 2026);
			assert.ok(m, `${brand} ${c.serial} did not decode`);
			assert.equal(m.month, c.month, `${brand} ${c.serial} month`);
			assert.ok(m.year === c.year || (c.cycle && m.otherYears.includes(c.year)), `${brand} ${c.serial} year`);
		}
	}
});

/* ---------- Phase 3: tank size and repair or replace (hand-checked) ---------- */

const tankCfg = JSON.parse(readFileSync(new URL('../data/computed/water-heater-size-calculator.json', import.meta.url), 'utf8')).client;
const repairCfg = JSON.parse(readFileSync(new URL('../data/computed/repair-or-replace-water-heater.json', import.meta.url), 'utf8')).client;
const { describe: describeTank } = await import('../assets/js/tank-size.js');
const { describe: describeRepair } = await import('../assets/js/repair-replace.js');

test('tankSizing: 3 showers + hands/face + food prep = 60 + 4 + 5 = 69 gal → 50 gal gas (typical FHR 81), 55 electric (74), 65 heat pump (75)', () => {
	const s = F.tankSizing([{ count: 3, gallons: 20 }, { count: 1, gallons: 4 }, { count: 1, gallons: 5 }], 0, 1, tankCfg.fhrTable);
	assert.equal(s.phd, 69);
	assert.equal(s.fhr, 69);
	assert.equal(s.gas.nominalGallons, 50);
	assert.equal(s.electric.nominalGallons, 55);
	assert.equal(s.heatpump.nominalGallons, 65);
	assert.equal(F.tankSizing([{ count: 3, gallons: 20 }, { count: 1, gallons: 4 }, { count: 1, gallons: 5 }], 0.1, 1, tankCfg.fhrTable).fhr, 76); // 69 × 1.1 = 75.9 → 76
});

test('tank describe: 2 showers + hands/face + food prep = 49 gal → 30 gal gas; 4 showers + hands + shave + food = 91 → 55 gal gas, no electric tank', () => {
	const two = describeTank({ people: '2', fuel: 'gas', shower: 2, handsFace: 1, foodPrep: 1 }, tankCfg);
	assert.equal(two.sizing.phd, 49);
	assert.equal(two.sizing.gas.nominalGallons, 30);
	assert.match(two.explain, /inside that range/); // American: 2 people 38–49
	const five = describeTank({ people: '5', fuel: 'electric', shower: 4, handsFace: 1, shaving: 1, foodPrep: 1 }, tankCfg);
	assert.equal(five.sizing.phd, 91);
	assert.equal(five.sizing.gas.nominalGallons, 55);
	assert.equal(five.sizing.electric.fits, 'none');
	assert.equal(five.status, 'warn');
	assert.equal(describeTank({ fuel: 'gas' }, tankCfg).formula, ''); // nothing counted
});

test('repair describe (installed prices since Phase 4): 8-yr gas tank $250 vs $1,809 = 14% → repair; $500 = 28% → borderline; 11-yr $350 = 19% → borderline; 11-yr $500 = 28% → replace; leak → replace', () => {
	const gp = repairCfg.types.gas_tank.replacementUsd;
	assert.equal(gp, 1809); // 899 unit + 650 labor + 260 permit and haul-away
	const a = describeRepair({ age: 8, type: 'gas_tank', repair: 250, replace: gp }, repairCfg);
	assert.equal(a.decision.verdict, 'repair');
	assert.equal(Math.round(a.decision.share * 100), 14);
	assert.equal(describeRepair({ age: 8, type: 'gas_tank', repair: 500, replace: gp }, repairCfg).decision.verdict, 'borderline');
	assert.equal(describeRepair({ age: 11, type: 'gas_tank', repair: 350, replace: gp }, repairCfg).decision.verdict, 'borderline');
	assert.equal(describeRepair({ age: 11, type: 'gas_tank', repair: 500, replace: gp }, repairCfg).decision.verdict, 'replace');
	assert.equal(describeRepair({ age: 2, type: 'gas_tank', repair: null, tankLeak: true }, repairCfg).decision.verdict, 'replace');
	assert.equal(describeRepair({ age: 4, type: 'electric_tank', repair: 150, replace: repairCfg.types.electric_tank.replacementUsd }, repairCfg).decision.verdict, 'repair');
	assert.equal(describeRepair({ age: 12, type: 'tankless', repair: 600, replace: repairCfg.types.tankless.replacementUsd }, repairCfg).decision.verdict, 'borderline'); // 27%, tankless old only from 15 years
});

test('yearlyEnergyCost: 55 gal/day, 67 °F, electric UEF 0.93 at $0.1723 → 55×365×8.34×67 ÷ 3412 ÷ 0.93 = 3,535 kWh ≈ $609', () => {
	const c = F.yearlyEnergyCost(55, 67, 'electric', 0.93, { usdPerKwh: 0.1723 });
	assert.equal(c.use.kwh, 3535);
	assert.ok(Math.abs(c.year - 609.08) < 0.5);
	assert.equal(F.yearlySaving({ year: 609.4 }, { year: 149.2 }), 460);
});

/* ---------- Phase 4: cost, cost of ownership, operating cost ---------- */
const costCfg = data('computed/water-heater-cost-calculator').client;
const tcoCfg = data('computed/tankless-vs-tank-water-heater').client;
const opCfg = data('computed/gas-vs-electric-water-heater').client;

test('householdGallonsPerDay: 4 people × 25.1 = 100.4 → 100; none → fallback', () => {
	assert.equal(F.householdGallonsPerDay(4, 25.1, 55), 100);
	assert.equal(F.householdGallonsPerDay(NaN, 25.1, 55), 55);
	assert.equal(F.householdGallonsPerDay(0, 25.1, 55), 55);
});

test('cost describe: 50-gal gas tank, like-for-like, U.S. average = 899 + 650 + 260 = $1,809 (range 749+350+150 = 1,249 to 999+1,100+500 = 2,599)', () => {
	const r = describeCost({ type: 'gas_tank', size: '50', state: 'US', scenario: 'replace', permit: true }, costCfg);
	assert.equal(r.estimate.typical, 1809);
	assert.equal(r.estimate.low, 1249);
	assert.equal(r.estimate.high, 2599);
	assert.equal(r.table.rows.length, 3);
});

test('cost describe: state factor scales labor and extras only (California RPP 110.7 → 1.107)', () => {
	const r = describeCost({ type: 'gas_tank', size: '50', state: 'CA', scenario: 'replace', permit: true }, costCfg);
	assert.equal(costCfg.model.states.CA.multiplier, 1.107);
	assert.equal(r.estimate.typical, 899 + Math.round(650 * 1.107) + Math.round(260 * 1.107)); // 899 + 720 + 288 = 1,907
});

test('cost describe: gas tank to tankless adds venting and gas line: 1,108 + 850 + 260 + 500 + 600 = $3,318', () => {
	const extras = costCfg.model.scenarios.toTankless.defaultExtras.gas_tankless;
	const v = { type: 'gas_tankless', size: 'whole', state: 'US', scenario: 'toTankless', ...Object.fromEntries(extras.map((x) => [x, true])) };
	assert.equal(describeCost(v, costCfg).estimate.typical, 3318);
});

test('operating cost: 55 gal/day, 120 − 58 = 62 °F rise, electric tank UEF 0.93 at the U.S. price → 55×365×8.34×62 ÷ 3412 ÷ 0.93 = 3,271 kWh', () => {
	const r = describeOp({ household: 'typical', state: 'US' }, opCfg);
	const el = r.rows.find((x) => x.id === 'electric_tank');
	assert.equal(el.use.kwh, 3271);
	near(el.year, 3271 * opCfg.model.us.usdPerKwh, 0.01);
});

test('consistency across pages: same household → same installed and running cost on cost, TCO and operating pages', () => {
	const tco = describeTco({ fuel: 'gas', household: 'typical', state: 'TX', years: 10 }, tcoCfg);
	const op = describeOp({ household: 'typical', state: 'TX' }, opCfg);
	const cost = describeCost({ type: 'gas_tank', size: '50', state: 'TX', scenario: 'replace', permit: true }, costCfg);
	const tank = tco.result.find((o) => o.id === 'gas_tank');
	assert.equal(tank.installed, cost.estimate.typical);
	assert.equal(tank.annualOperating, op.rows.find((x) => x.id === 'gas_tank').year);
});

test('cost of ownership: gas tank vs tankless over 20 years: tank replaced once at year 13, tankless breaks even that year', () => {
	const r = describeTco({ fuel: 'gas', household: 'typical', state: 'US', years: 20 }, tcoCfg);
	const [tank, tankless] = r.result;
	assert.equal(tank.replacements, 1);
	assert.equal(tankless.replacements, 0);
	assert.equal(tankless.breakEven, 13);
});

/* ---------- Phase 6: heat pump payback and expansion tank sizing ---------- */
import { describe as describeHp } from '../assets/js/heat-pump-savings.js';
import { describe as describeEt } from '../assets/js/expansion-tank.js';

const computed = (f) => JSON.parse(readFileSync(new URL(`../data/computed/${f}.json`, import.meta.url), 'utf8'));
const et = data('expansion-tanks');
const etData = { expansionFactors: et.expansionFactors, atmosphericPsi: et.atmosphericPsi, tanks: et.tanks, headroomShare: et.defaults.headroomShare };

test('simple payback: 1,368 extra ÷ 426 a year = 3.2 years; no extra cost → 0; no saving → null', () => {
	assert.equal(F.simplePaybackYears(1368, 426), 3.2);
	assert.equal(F.simplePaybackYears(-50, 426), 0);
	assert.equal(F.simplePaybackYears(1368, 0), null);
	assert.equal(F.simplePaybackYears(1368, -10), null);
});

test('net saving over a life: 426 × 13 − 1,368 = 4,170', () => {
	assert.equal(F.netSavingOver(426, 13, 1368), 4170);
});

test('expansion factor: table rows exact (120 °F → 0.0100), interpolated (125 °F → 0.0112), from 0 at 40 °F, capped at 180 °F', () => {
	near(F.expansionFactor(120, et.expansionFactors), 0.01);
	near(F.expansionFactor(125, et.expansionFactors), 0.0112, 1e-9);
	near(F.expansionFactor(40, et.expansionFactors), 0);
	near(F.expansionFactor(70, et.expansionFactors), 0.0062 * 30 / 60, 1e-9);
	near(F.expansionFactor(200, et.expansionFactors), 0.0276);
});

test('design pressure factor: (150 + 14.7) ÷ (150 − 60) = 1.83; maximum not above supply → null', () => {
	near(F.designPressureFactor(150, 60, 14.7), 164.7 / 90, 1e-12);
	assert.equal(F.designPressureFactor(60, 60, 14.7), null);
});

test('expansion tank: AMTROL worked example, 240 gal at 150 °F, 60 psi supply, 125 psi max → 4.3 gal × 2.15 = 9.2 gal → 10-gallon tank', () => {
	const r = F.expansionTankSizing({ heaterGallons: 240, tempF: 150, supplyPsi: 60, maxPsi: 125 }, etData);
	near(r.expanded, 240 * 0.0179, 1e-9); // 4.296
	near(r.dpf, 139.7 / 65, 1e-3); // 2.149 (rounded to 3 decimals)
	near(r.required, 4.296 * (139.7 / 65), 1e-2); // 9.23
	assert.equal(r.tank.model, 'ST-25V');
});

test('expansion tank: default 50 gal, 120 °F, 60/150 psi → 0.50 gal × 1.83 = 0.92 gal → 2-gallon tank, no safer pick', () => {
	const r = F.expansionTankSizing({ heaterGallons: 50, tempF: 120, supplyPsi: 60, maxPsi: 150 }, etData);
	near(r.required, 0.5 * (164.7 / 90), 1e-2); // 0.915 → 0.92
	assert.equal(r.tank.model, 'ST-5');
	assert.equal(r.safer, null);
});

test('expansion tank: borderline 40 gal, 160 °F, 80 psi (1.97 gal vs a 2.0-gal tank) → ST-5 minimum, ST-12 safer, as AMTROL picks', () => {
	const r = F.expansionTankSizing({ heaterGallons: 40, tempF: 160, supplyPsi: 80, maxPsi: 150 }, etData);
	assert.equal(r.tank.model, 'ST-5');
	assert.equal(r.safer.model, 'ST-12');
});

test('expansion tank: acceptance volume decides 50 gal, 160 °F, 40 psi (1.05 gal expanded > 0.9 gal acceptance) → ST-12', () => {
	const r = F.expansionTankSizing({ heaterGallons: 50, tempF: 160, supplyPsi: 40, maxPsi: 150 }, etData);
	near(r.expanded, 50 * 0.0209, 1e-9);
	assert.equal(r.tank.model, 'ST-12');
});

test('expansion tank: AMTROL quick chart, every cell matches our minimum or safer pick except the one documented acceptance case', () => {
	let misses = 0;
	for (const row of et.quickChart.rows) {
		et.quickChart.pressures.forEach((psi, i) => {
			const r = F.expansionTankSizing({ heaterGallons: row.gallons, tempF: row.tempF, supplyPsi: psi, maxPsi: 150 }, etData);
			const ok = r.tank.model === row.picks[i] || (r.safer && r.safer.model === row.picks[i]);
			if (!ok) misses += 1;
		});
	}
	assert.equal(misses, 1);
});

test('expansion tank describe: invalid pressures → bad status; high supply → warning with PRV advice', () => {
	const cfg = computed('water-heater-expansion-tank-size').client;
	assert.equal(describeEt({ gallons: 50, temp: 120, supply: 100, max: 90 }, cfg).status, 'bad');
	const hi = describeEt({ gallons: 50, temp: 120, supply: 90, max: 150 }, cfg);
	assert.equal(hi.status, 'warn');
	assert.match(hi.explain, /pressure-reducing valve/);
});

test('heat pump describe: electric tank, U.S. average → saves $426 a year, extra $1,368, payback 3.2 years; gas tank pays back after its life', () => {
	const cfg = computed('heat-pump-water-heater-cost').client;
	const e = describeHp({ current: 'electric_tank', size: '50', household: 'typical', state: 'US' }, cfg).result;
	assert.equal(e.saving, 426);
	assert.equal(e.extra, 1368);
	assert.equal(e.payback, 3.2);
	const g = describeHp({ current: 'gas_tank', size: '50', household: 'typical', state: 'US' }, cfg);
	assert.ok(g.result.payback > g.result.life);
	assert.equal(g.status, 'bad');
	const reb = describeHp({ current: 'electric_tank', size: '50', household: 'typical', state: 'US', rebate: 2000 }, cfg).result;
	assert.equal(reb.payback, 0);
});

test('hotShareOfMix: 105 °F shower from a 120 °F tank → 79% hot at 50 °F inlet, 70% at 70 °F; clamps; bad input → null', () => {
	near(F.hotShareOfMix(105, 120, 50), 0.7857); // 55 ÷ 70
	near(F.hotShareOfMix(105, 120, 70), 0.7); // 35 ÷ 50
	assert.equal(F.hotShareOfMix(130, 120, 50), 1);
	assert.equal(F.hotShareOfMix(40, 120, 50), 0);
	assert.equal(F.hotShareOfMix(105, 50, 50), null);
});

test('hotGallonsDrawn: 2.5 GPM for 10 minutes at 78.57% hot → 19.64 gallons from the tank', () => {
	near(F.hotGallonsDrawn(2.5, 10, 0.7857), 19.64, 0.005);
	assert.equal(F.hotGallonsDrawn(2.5, 10, null), null);
});

test('monthly mains temperature: stays inside the coldest/warmest band, coldest in late winter, matches the annual mean', () => {
	const c = JSON.parse(readFileSync(new URL('../data/groundwater-temp-by-state.json', import.meta.url), 'utf8')).correlation;
	// EnergyPlus example: Chicago O'Hare TMY2, 9.69 °C average (49.4 °F), 28.1 °C max monthly difference (50.6 °F).
	const m = F.monthlyMainsTemps(49.4, 50.6, c);
	const band = F.mainsWaterTemp(49.4, 50.6, c);
	assert.equal(m.length, 12);
	for (const t of m) assert.ok(t >= band.coldestMonth - 0.05 && t <= band.warmestMonth + 0.05);
	const coldest = m.indexOf(Math.min(...m));
	assert.ok(coldest === 1 || coldest === 2, `coldest month index ${coldest}`); // February or March: mains lag the air
	const mean = m.reduce((a, b) => a + b, 0) / 12;
	assert.ok(Math.abs(mean - band.annualAvg) < 0.6);
});

test('elementResistanceOhms: 4500 W at 240 V → 12.8 Ω, 5500 W → 10.47 Ω, 1500 W at 120 V → 9.6 Ω; bad input → null', () => {
	near(F.elementResistanceOhms(240, 4500), 12.8);
	near(F.elementResistanceOhms(240, 5500), 10.47, 0.005);
	near(F.elementResistanceOhms(120, 1500), 9.6);
	assert.equal(F.elementResistanceOhms(240, 0), null);
});

test('extraMixedWaterShare: 105 °F shower, 50 °F inlet: a 140 °F tank gives 29% more mixed water than 120 °F', () => {
	const a = F.hotShareOfMix(105, 120, 50); // 0.7857
	const b = F.hotShareOfMix(105, 140, 50); // 0.6111
	near(F.extraMixedWaterShare(a, b), 0.2857, 0.0005);
	assert.equal(F.extraMixedWaterShare(a, 0), null);
});
