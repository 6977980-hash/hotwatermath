/**
 * Build data/computed/*.json from assets/js/formulas.js + data/*.json (CLAUDE.md §5).
 * PHP renders these files; it never does calculator math.
 *
 * Every computed file has { meta, ..., tables } where tables follow the hwm_table() convention in inc/data.php.
 * data/computed/_manifest.json stores a hash of every input; scripts/validate-data.mjs fails when an input
 * changed after the last build (stale computed data).
 *
 * Usage: npm run build:data
 */
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import * as F from '../assets/js/formulas.js';
import { describe as describeAge } from '../assets/js/age.js';
import { describe as describeTankless, FIXTURE_KEYS } from '../assets/js/tankless-size.js';
import { describe as describeTank, ACTIVITY_KEYS } from '../assets/js/tank-size.js';
import { describe as describeRepair } from '../assets/js/repair-replace.js';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const outDir = join(root, 'data/computed');
mkdirSync(outDir, { recursive: true });

/** Inputs of the build. Keep in sync with scripts/validate-data.mjs (it reads this list from the manifest). */
export const INPUTS = [
	'assets/js/formulas.js',
	'scripts/build-data.mjs',
	'data/site.json',
	'data/energy-prices.json',
	'data/groundwater-temp-by-state.json',
	'data/regional-cost-index.json',
	'data/energystar-water-heaters.json',
	'data/fhr-to-tank-size.json',
	'data/uef-ranges.json',
	'data/fixture-flow-rates.json',
	'data/peak-hour-demand.json',
	'data/lifespans.json',
	'data/hot-water-use.json',
	'data/cost-ranges.json',
	'data/electrical.json',
	'data/serial-formats.json',
	'data/incentives.json',
	'data/decision-rules.json',
	'data/tankless-reference-units.json',
	'data/brand-pages.json',
	'assets/js/age.js',
	'assets/js/tankless-size.js',
	'assets/js/tank-size.js',
	'assets/js/repair-replace.js',
	'data/household-size-guides.json',
];

const read = (f) => JSON.parse(readFileSync(join(root, f), 'utf8'));
const d = Object.fromEntries(INPUTS.filter((f) => f.endsWith('.json')).map((f) => [f.replace(/^data\/|\.json$/g, ''), read(f)]));
const r = (v, n = 0) => Math.round(v * 10 ** n) / 10 ** n;
const fmt = (v, n = 0) => Number(v).toLocaleString('en-US', { minimumFractionDigits: n, maximumFractionDigits: n });
const derived = (from) => ({ source: `Computed by npm run build:data from ${from} with assets/js/formulas.js`, sourceUrl: 'https://hotwatermath.com/methodology/', sourceDate: d.site.defaults.defaultElectricRateUsdPerKwh.sourceDate });
const table = (caption, columns, rows, src) => ({ caption, columns, rows, source: src.source, sourceUrl: src.sourceUrl, sourceDate: src.sourceDate });
const states = Object.entries(d['groundwater-temp-by-state'].states).sort((a, b) => a[1].name.localeCompare(b[1].name));

const out = {};

/* ---------- energy prices ---------- */
{
	const e = d['energy-prices'];
	const conv = (s) => ({ usdPerKwh: r(F.usdPerKwh(s.electricCentsPerKwh), 4), usdPerTherm: r(F.gasUsdPerTherm(s.gasUsdPerMcf, s.gasBtuPerCf), 3) });
	const byState = Object.fromEntries(Object.entries(e.states).map(([k, s]) => [k, { name: s.name, ...conv(s), electricYear: s.electricYear, gasYear: s.gasYear }]));
	out['energy-prices'] = {
		meta: { ...derived('data/energy-prices.json (EIA)'), notes: 'Dollars per kWh and per therm by state. Gas: $/Mcf ÷ (BTU per cubic foot × 1,000 ÷ 100,000).' },
		us: conv(e.us),
		states: byState,
		tables: {
			energyPrices: table(
				'Average residential energy prices by state',
				[
					{ key: 'name', label: 'State' },
					{ key: 'kwh', label: 'Electricity', unit: '$/kWh', num: true },
					{ key: 'therm', label: 'Natural gas', unit: '$/therm', num: true },
					{ key: 'gasYear', label: 'Gas price year', num: true },
				],
				Object.values(byState).sort((a, b) => a.name.localeCompare(b.name)).map((s) => ({ name: s.name, kwh: fmt(s.usdPerKwh, 3), therm: fmt(s.usdPerTherm, 2), gasYear: s.gasYear })),
				{ source: e.meta.source, sourceUrl: e.meta.sourceUrl, sourceDate: e.meta.sourceDate },
			),
		},
	};
}

/* ---------- inlet (mains) water temperature ---------- */
{
	const g = d['groundwater-temp-by-state'];
	const byState = Object.fromEntries(states.map(([k, s]) => [k, { name: s.name, ...F.mainsWaterTemp(s.annualAvgAirF, s.maxMonthlyDiffF, g.correlation) }]));
	out['inlet-temps'] = {
		meta: { ...derived('data/groundwater-temp-by-state.json (NOAA) and the Christensen–Burch correlation'), notes: g.meta.notes },
		states: byState,
		tables: {
			inletTemps: table(
				'Estimated incoming water temperature by state',
				[
					{ key: 'name', label: 'State' },
					{ key: 'annualAvg', label: 'Year average', unit: '°F', num: true },
					{ key: 'coldestMonth', label: 'Coldest month', unit: '°F', num: true },
				],
				Object.values(byState).map((s) => ({ name: s.name, annualAvg: fmt(s.annualAvg), coldestMonth: fmt(s.coldestMonth) })),
				{ source: g.meta.source, sourceUrl: g.meta.sourceUrl, sourceDate: g.meta.sourceDate },
			),
		},
	};
}

/* ---------- regional multipliers ---------- */
{
	const rc = d['regional-cost-index'];
	const byState = Object.fromEntries(Object.entries(rc.states).map(([k, s]) => [k, { name: s.name, multiplier: r(s.rppAllItems / 100, 3) }]));
	out['regional-multipliers'] = {
		meta: { ...derived('data/regional-cost-index.json (BEA)'), notes: 'Multiplier = regional price parity ÷ 100. Applied to labor and extras only.' },
		states: byState,
		tables: {
			regional: table(
				'Regional cost multiplier by state (1.00 = U.S. average)',
				[{ key: 'name', label: 'State' }, { key: 'm', label: 'Multiplier', num: true }],
				Object.values(byState).sort((a, b) => a.name.localeCompare(b.name)).map((s) => ({ name: s.name, m: fmt(s.multiplier, 2) })),
				{ source: rc.meta.source, sourceUrl: rc.meta.sourceUrl, sourceDate: rc.meta.sourceDate },
			),
		},
	};
}

/* ---------- first hour rating → tank size ---------- */
{
	const fh = d['fhr-to-tank-size'];
	const rowsFor = (fuel, models) => {
		const sizes = [...new Set(models.map((m) => m.nominalGallons))].sort((a, b) => a - b);
		return sizes.map((n) => {
			const s = F.summarize(models.filter((m) => m.nominalGallons === n).map((m) => m.fhr));
			return { fuel, nominalGallons: n, fhrLow: s.low, fhrTypical: s.typical, fhrHigh: s.high, n: s.n };
		});
	};
	const hp = d['energystar-water-heaters'].heatPumpFhrByNominal
		.filter((x) => x.nominalGallons >= 50)
		.map((x) => ({ fuel: 'heatpump', nominalGallons: x.nominalGallons, fhrLow: x.low, fhrTypical: x.typical, fhrHigh: x.high, n: x.n }));
	const rows = [...rowsFor('gas', fh.models.gas), ...rowsFor('electric', fh.models.electric), ...hp];
	const fuelLabel = { gas: 'Gas', electric: 'Electric', heatpump: 'Heat pump' };
	out['fhr-table'] = {
		meta: { ...derived('data/fhr-to-tank-size.json and data/energystar-water-heaters.json'), notes: fh.meta.notes },
		rows,
		tables: {
			fhr: table(
				'First hour rating by tank size and fuel (sample of current models)',
				[
					{ key: 'fuel', label: 'Fuel' },
					{ key: 'size', label: 'Tank', unit: 'gal', num: true },
					{ key: 'typical', label: 'Typical FHR', unit: 'gal', num: true },
					{ key: 'range', label: 'Range', unit: 'gal', num: true },
					{ key: 'n', label: 'Models', num: true },
				],
				rows.map((x) => ({ fuel: fuelLabel[x.fuel], size: x.nominalGallons, typical: fmt(x.fhrTypical), range: `${fmt(x.fhrLow)}–${fmt(x.fhrHigh)}`, n: x.n })),
				{ source: 'Rheem Performance spec sheets (gas, electric); ENERGY STAR certified list (heat pump)', sourceUrl: fh.meta.sourceUrl, sourceDate: fh.meta.sourceDate },
			),
		},
	};
}

/* ---------- UEF defaults ---------- */
{
	const u = d['uef-ranges'];
	const es = d['energystar-water-heaters'];
	const fromModels = (models) => {
		const s = F.summarize(models.filter((m) => m.nominalGallons === 40 || m.nominalGallons === 50).map((m) => m.uef));
		return { low: s.low, typical: r(s.typical, 3), high: s.high };
	};
	const types = {
		gas_tank: { label: u.types.gas_tank.label, ...fromModels(d['fhr-to-tank-size'].models.gas) },
		gas_tank_high_efficiency: { label: u.types.gas_tank_high_efficiency.label, low: es.uef.gasStorage.low, typical: es.uef.gasStorage.typical, high: es.uef.gasStorage.high },
		electric_tank: { label: u.types.electric_tank.label, ...fromModels(d['fhr-to-tank-size'].models.electric) },
		heat_pump: { label: u.types.heat_pump.label, low: es.uef.heatPump240V.low, typical: es.uef.heatPump240V.typical, high: es.uef.heatPump240V.high },
		gas_tankless: { label: u.types.gas_tankless.label, low: u.types.gas_tankless.low, typical: u.types.gas_tankless.typical, high: u.types.gas_tankless.high },
		electric_tankless: { label: u.types.electric_tankless.label, low: u.types.electric_tankless.low, typical: u.types.electric_tankless.typical, high: u.types.electric_tankless.typical },
	};
	out['uef-defaults'] = {
		meta: { ...derived('data/uef-ranges.json, data/fhr-to-tank-size.json and data/energystar-water-heaters.json'), notes: u.meta.notes },
		types,
		tables: {
			uef: table(
				'Uniform Energy Factor (UEF) by water heater type',
				[{ key: 'label', label: 'Type' }, { key: 'typical', label: 'Default', num: true }, { key: 'range', label: 'Range', num: true }],
				Object.values(types).map((t) => ({ label: t.label, typical: fmt(t.typical, 2), range: t.low === t.high ? fmt(t.low, 2) : `${fmt(t.low, 2)}–${fmt(t.high, 2)}` })),
				{ source: 'Rheem spec sheets, ENERGY STAR certified list and criteria, 10 CFR 430.32(d)', sourceUrl: u.meta.sourceUrl, sourceDate: u.meta.sourceDate },
			),
		},
	};
}

/* ---------- unit prices ---------- */
{
	const c = d['cost-ranges'];
	const cats = Object.fromEntries(Object.entries(c.units).map(([k, x]) => {
		const s = F.summarize(x.samples.map((y) => y.priceUsd));
		return [k, { type: x.type, label: x.label, low: r(s.low), typical: r(s.typical), high: r(s.high), n: s.n }];
	}));
	out['unit-prices'] = {
		meta: { ...derived('data/cost-ranges.json (retail listings)'), notes: c.meta.notes, lastReviewed: c.lastReviewed },
		units: cats,
		tables: {
			unitPrices: table(
				`Equipment prices, unit only (listings checked ${c.lastReviewed})`,
				[{ key: 'label', label: 'Water heater' }, { key: 'typical', label: 'Typical', unit: '$', num: true }, { key: 'range', label: 'Range', unit: '$', num: true }, { key: 'n', label: 'Listings', num: true }],
				Object.values(cats).map((x) => ({ label: x.label, typical: fmt(x.typical), range: `${fmt(x.low)}–${fmt(x.high)}`, n: x.n })),
				{ source: c.meta.source, sourceUrl: c.meta.sourceUrl, sourceDate: c.meta.sourceDate },
			),
		},
	};
}

/* ---------- reference tables (rendered straight from data) ---------- */
{
	const fx = d['fixture-flow-rates'];
	const ph = d['peak-hour-demand'];
	const ls = d.lifespans;
	const hw = d['hot-water-use'];
	const el = d.electrical;
	const sf = d['serial-formats'];
	out['reference-tables'] = {
		meta: { ...derived('the sourced data files named in each table'), notes: 'Display tables; each table carries its own source. physics = the constants in formulas.PHYSICS, copied here so pages can print them.' },
		physics: { ...F.PHYSICS },
		tables: {
			fixtures: table(
				'Fixture flow rates used for tankless sizing',
				[{ key: 'label', label: 'Fixture' }, { key: 'gpm', label: 'Default', unit: 'GPM', num: true }, { key: 'low', label: 'Low-flow', unit: 'GPM', num: true }, { key: 'range', label: 'Typical range', unit: 'GPM', num: true }],
				Object.values(fx.fixtures).map((f) => ({ label: f.label, gpm: fmt(f.gpm, 1), low: fmt(f.lowFlowGpm, 1), range: `${fmt(f.rangeLow, 1)}–${fmt(f.rangeHigh, 1)}` })),
				{ source: '10 CFR 430.32, EPA WaterSense, PNNL Building America Solution Center (ASHRAE Handbook data)', sourceUrl: fx.meta.sourceUrl, sourceDate: fx.meta.sourceDate },
			),
			peakHour: table(
				'Hot water per use for the peak-hour worksheet',
				[{ key: 'label', label: 'Use' }, { key: 'gallons', label: 'Hot water per use', unit: 'gal', num: true }],
				Object.values(ph.activities).map((a) => ({ label: a.label, gallons: a.gallons })),
				ph.meta,
			),
			lifespans: table(
				'Expected life by water heater type',
				[{ key: 'label', label: 'Type' }, { key: 'typical', label: 'Typical', unit: 'years', num: true }, { key: 'range', label: 'Range', unit: 'years', num: true }],
				Object.values(ls.types).map((t) => ({ label: t.label, typical: t.typical, range: t.low === t.high ? `${t.low} (DOE average; no range published)` : `${t.low}–${t.high}` })),
				ls.meta,
			),
			drawPatterns: table(
				'DOE test draw patterns behind the UEF rating',
				[{ key: 'label', label: 'Draw pattern' }, { key: 'fhr', label: 'First hour rating', unit: 'gal', num: true }, { key: 'gpd', label: 'Hot water drawn', unit: 'gal/day', num: true }],
				hw.drawPatterns.map((p) => ({ label: p.label, fhr: p.fhrBelow ? `${p.fhrFrom} to under ${p.fhrBelow}` : `${p.fhrFrom} or more`, gpd: p.gallonsPerDay })),
				hw.meta,
			),
			electricReference: table(
				'Electric tankless: one manufacturer\'s published electrical requirements',
				[{ key: 'model', label: 'Model' }, { key: 'kw', label: 'Power', unit: 'kW', num: true }, { key: 'amps', label: 'Current', unit: 'A', num: true }, { key: 'breakers', label: 'Breakers' }, { key: 'wire', label: 'Wire' }],
				el.referenceUnits.map((x) => ({ model: x.model, kw: fmt(x.kw, 0), amps: x.amps, breakers: x.breakers, wire: x.wire })),
				el.meta,
			),
			serialFormats: table(
				'Serial number date codes we can decode',
				[{ key: 'brand', label: 'Brand' }, { key: 'rule', label: 'How the date is written' }, { key: 'example', label: 'Example' }],
				Object.values(sf.brands).flatMap((b) => b.patterns.map((p, i) => ({ brand: i === 0 ? b.label : '', rule: describePattern(p), example: p.example }))),
				sf.meta,
			),
		},
	};
}

function describePattern(p) {
	if (p.layout) return p.layout;
	const parts = Object.entries(p.fields).sort((a, b) => a[1].group - b[1].group).map(([, s]) => ({ yy: 'two-digit year', mm: 'two-digit month', ww: 'two-digit week', yyyy: 'four-digit year', letterYear: 'year letter', letterMonth: 'month letter' })[s.type]);
	const when = p.validFromYear ? ` (${p.validFromYear} and later)` : p.validToYear ? ` (up to ${p.validToYear})` : '';
	return `${parts.join(', then ')}${when}`;
}

/* ---------- formula cross-checks against published figures ---------- */
{
	const hw = d['hot-water-use'];
	const el = d.electrical;
	const fx = d['fixture-flow-rates'];
	const ph = d['peak-hour-demand'];
	const checks = [];
	// 1. Annual electric use vs DOE FEMP (20,075 gal/yr = 55 gal/day, 67 °F test rise, UEF 0.95).
	{
		const heat = F.annualHeatBtu(hw.typicalHouseholdGallonsPerDay.value, hw.testConditions.riseF);
		const ours = F.annualEnergyUse(heat, 'electric', hw.femp.electricUef).kwh;
		checks.push({ id: 'annual-kwh-femp', label: `Electric tank, UEF ${hw.femp.electricUef}, ${fmt(hw.typicalHouseholdGallonsPerDay.gallonsPerYear)} gal/yr, ${hw.testConditions.riseF} °F rise`, ours: `${fmt(ours)} kWh/yr`, published: `${fmt(hw.femp.annualKwh)} kWh/yr`, diffPct: r(((ours - hw.femp.annualKwh) / hw.femp.annualKwh) * 100, 1), source: hw.femp.source, sourceUrl: hw.femp.sourceUrl });
	}
	// 2. Electric tankless output vs manufacturer rise chart.
	for (const p of el.riseChartCheck.points) {
		const rise = F.riseAtGpm(F.btuhFromKw(el.riseChartCheck.kw), p.gpm);
		checks.push({ id: `rise-${el.riseChartCheck.model}-${p.gpm}`, label: `${el.riseChartCheck.model} (${el.riseChartCheck.kw} kW) at ${fmt(p.gpm, 1)} GPM`, ours: `${fmt(rise)} °F rise`, published: `${p.riseF} °F rise`, diffPct: r(((rise - p.riseF) / p.riseF) * 100, 1), source: `${el.meta.source}, temperature rise table`, sourceUrl: el.meta.sourceUrl });
	}
	// 3. DOE tankless example: flow and rise.
	{
		const ex = fx.doeExample;
		const gpm = F.totalGpm([{ count: 1, gpm: ex.faucetGpm }, { count: 1, gpm: ex.showerGpm }]);
		const rise = F.temperatureRise(ex.outletF, ex.inletF);
		checks.push({ id: 'doe-tankless-example', label: `Faucet ${ex.faucetGpm} GPM + shower ${ex.showerGpm} GPM, ${ex.inletF} °F in, ${ex.outletF} °F out`, ours: `${fmt(gpm, 2)} GPM at ${rise} °F rise`, published: `${fmt(ex.totalGpm, 2)} GPM at ${ex.riseF} °F rise`, diffPct: r(((gpm - ex.totalGpm) / ex.totalGpm) * 100, 1), source: ex.source, sourceUrl: ex.sourceUrl });
	}
	// 4. AHRI peak-hour worksheet example.
	{
		const a = ph.activities;
		const ex = ph.example;
		const phd = F.peakHourDemand([{ count: ex.showers, gallons: a.shower.gallons }, { count: ex.shaves, gallons: a.shaving.gallons }, { count: ex.shampoos, gallons: a.shampoo.gallons }, { count: ex.handDishwashing, gallons: a.handDishwashing.gallons }]);
		checks.push({ id: 'ahri-peak-hour-example', label: `${ex.showers} showers, ${ex.shaves} shave, ${ex.shampoos} shampoo, ${ex.handDishwashing} hand dishwashing`, ours: `${fmt(phd)} gal`, published: `${fmt(ex.peakHourDemand)} gal`, diffPct: r(((phd - ex.peakHourDemand) / ex.peakHourDemand) * 100, 1), source: ex.source, sourceUrl: ex.sourceUrl });
	}
	out['cross-checks'] = {
		meta: { ...derived('published worked examples (DOE FEMP, Rheem spec sheet, DOE Energy Saver, AHRI)'), notes: 'Checks that our formulas reproduce published figures. diffPct = (ours − published) ÷ published × 100.' },
		checks,
		tables: {
			formulaChecks: table(
				'Our formulas against published figures',
				[{ key: 'label', label: 'Case' }, { key: 'ours', label: 'Our result' }, { key: 'published', label: 'Published' }, { key: 'diff', label: 'Difference', unit: '%', num: true }],
				checks.map((c) => ({ label: c.label, ours: c.ours, published: c.published, diff: fmt(c.diffPct, 1) })),
				{ source: 'DOE FEMP; Rheem form THD-PTE; DOE Energy Saver (archived); AHRI worksheet', sourceUrl: 'https://hotwatermath.com/methodology/', sourceDate: d.site.defaults.defaultElectricRateUsdPerKwh.sourceDate },
			),
		},
	};
}

/* ---------- Phase 2 tools: age calculator ---------- */
const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const madeLabel = (m) => (m.month ? `${MONTHS[m.month - 1]} ${m.year}` : `${m.year}`);
{
	const sf = d['serial-formats'];
	const ls = d.lifespans;
	const asOfIso = d.site.dateVerified['water-heater-age-calculator'];
	const asOf = new Date(`${asOfIso}T12:00:00Z`);
	const refYear = asOf.getUTCFullYear();
	const adj = ls.adjustments;
	const lifeFor = (typeId) => ls.types[ls.toolTypes.options.find((o) => o.id === typeId).life];
	const deltas = (hardness, flushed, anode) => [adj.hardness[hardness] ?? 0, flushed ? 0 : adj.noAnnualFlush, anode ? adj.anodeReplaced : 0];
	const estimate = (brand, serial, typeId, hardness, flushed, anode) => {
		const made = F.decodeSerial(serial, sf.brands[brand].patterns, refYear);
		const est = F.ageEstimate(made, asOf, lifeFor(typeId), deltas(hardness, flushed, anode), adj.minimumLifeYears, ls.statusBands);
		return { made, ...est };
	};
	const statusLabel = { ok: 'Well within expected life', warn: 'Getting older: plan ahead', bad: 'Near or past expected life' };
	const pct = (f) => Math.round(f * 100);

	// Default (server-rendered) result = the form's default inputs.
	const def = { brand: 'rheem', serial: 'Q431912345', type: 'gas_tank', hardness: 'moderate', flushed: true, anode: false };
	const e = estimate(def.brand, def.serial, def.type, def.hardness, def.flushed, def.anode);
	const life = e.life;
	// Worked example on the page: Bradford White electric tank, hard water, never flushed.
	const wx = { brand: 'bradford-white', serial: 'SF1234567', type: 'electric_tank', hardness: 'hard', flushed: false, anode: false };
	const w = estimate(wx.brand, wx.serial, wx.type, wx.hardness, wx.flushed, wx.anode);
	const wBase = lifeFor(wx.type);

	// Cross-checks against manufacturer references (the official lookup's answers were recorded on the check date).
	const xc = [];
	for (const t of [
		{ serial: 'Q431912345', official: '2019-10-18' },
		{ serial: 'M101812345', official: '2018-03-07' },
		{ serial: 'Q052412345', official: '2024-01-25' },
	]) {
		const m = F.decodeSerial(t.serial, sf.brands.rheem.patterns, refYear);
		const [oy, om] = t.official.split('-').map(Number);
		xc.push({ id: `rheem-${t.serial}`, label: `Rheem/Ruud serial ${t.serial} (test serial)`, ours: madeLabel(m), published: `${MONTHS[om - 1]} ${Number(t.official.slice(8))}, ${oy}`, match: m.year === oy && m.month === om, why: 'Same month. The serial holds a production week, so we show the month; the official lookup returns a day inside that week.', source: 'Ruud official water heater age calculator', sourceUrl: sf.brands.rheem.sourceUrl });
	}
	for (const t of [{ serial: 'SF', year: 2018, month: 6 }, { serial: 'XA', year: 2021, month: 1 }]) {
		const m = F.decodeSerial(`${t.serial}1234567`, sf.brands['bradford-white'].patterns, refYear);
		xc.push({ id: `bw-${t.serial}`, label: `Bradford White serial starting ${t.serial}`, ours: `${madeLabel(m)} (also possible: ${m.otherYears.join(', ')})`, published: `${MONTHS[t.month - 1]} ${t.year} (table: ${t.serial[0]} = ${t.year} or ${t.year + 20}, ${t.serial[1]} = ${MONTHS[t.month - 1]})`, match: m.year === t.year && m.month === t.month, why: 'Same date. Bradford White letters repeat every 20 years; we pick the latest year that is not in the future and list the others, as the manufacturer table does.', source: 'Bradford White, Determine the age of my water heater', sourceUrl: sf.brands['bradford-white'].sourceUrl });
	}
	{
		const m = F.decodeSerial('7410A18709AAAA', sf.brands.navien.patterns, refYear);
		xc.push({ id: 'navien-cpsc', label: 'Navien NPE-180A serial 7410x18709xxxx (recall example)', ours: madeLabel(m), published: 'July 9, 2018', match: m.year === 2018 && m.month === 7, why: 'Same month. We do not show the day.', source: 'U.S. CPSC recall notice, Navien tankless water heaters (2019)', sourceUrl: sf.brands.navien.sourceUrl });
	}

	const summaryRows = sf.brandOptions.options.map((o) => {
		const b = o.decodeAs ? sf.brands[o.decodeAs] : null;
		return {
			id: o.id,
			page: o.page || `${o.id}-water-heater-age`,
			brand: o.label,
			rule: b ? b.patterns.map(describePattern).join('; or ') : 'Not decoded yet: enter the year from the label',
			example: b ? b.patterns.map((p) => p.example).join('; ') : '',
			note: o.caveat || '',
		};
	});

	out['water-heater-age-calculator'] = {
		meta: { ...derived('data/serial-formats.json and data/lifespans.json'), notes: `Default result and worked example are computed as of ${asOfIso} (site.json dateVerified). The live calculator uses the visitor's date.` },
		asOf: asOfIso,
		defaultInputs: def,
		default: null,
		statusLabels: statusLabel,
		example: {
			input: wx,
			madeLabel: madeLabel(w.made),
			year: w.made.year,
			cycleYears: w.made.year - w.made.otherYears[0],
			cycleStarts: sf.brands['bradford-white'].patterns[0].fields.year.cycleStarts,
			otherYears: w.made.otherYears,
			age: fmt(w.age, 1),
			baseLow: wBase.low, baseTypical: wBase.typical, baseHigh: wBase.high,
			hardDelta: adj.hardness.hard, flushDelta: adj.noAnnualFlush,
			lifeLow: w.life.low, lifeTypical: w.life.typical, lifeHigh: w.life.high,
			usedPct: pct(w.usedFraction),
			remainingMin: fmt(w.remaining.min, 1), remainingMax: fmt(w.remaining.max, 1),
			status: w.status, statusLabel: statusLabel[w.status],
		},
		bandsPct: { amberFrom: pct(ls.statusBands.amberFrom), redFrom: pct(ls.statusBands.redFrom) },
		crossChecks: xc,
		summaryRows,
		client: {
			asOf: asOfIso,
			months: MONTHS,
			brandOptions: sf.brandOptions.options,
			brands: Object.fromEntries(Object.entries(sf.brands).map(([k, b]) => [k, { label: b.label, patterns: b.patterns.map(({ id, regex, fields, validFromYear, validToYear }) => ({ id, regex, fields, validFromYear, validToYear })) }])),
			officialLookups: sf.officialLookups,
			types: ls.toolTypes.options.map((o) => ({ id: o.id, label: o.label, ...ls.types[o.life] })),
			adjustments: adj,
			bands: ls.statusBands,
			statusLabels: statusLabel,
		},
	};
	const ageOut = out['water-heater-age-calculator'];
	const dd = describeAge({ ...def, madeYear: null, madeMonth: '' }, ageOut.client, asOf);
	ageOut.default = {
		status: dd.status, statusLabel: dd.statusLabel, headline: dd.headline,
		explain: `${dd.explain} (Example serial, as of ${asOfIso}; type yours.)`,
		formula: dd.formula,
		sources: 'Rheem date code: checked against Rheem/Ruud\'s own lookup. Lifespan: DOE 2024 water heater rule and DOE FEMP. Hardness, flushing and anode adjustments are our editorial assumptions. <a href="/methodology/#water-heater-age-calculator">See the decoder checked against Rheem/Ruud, Bradford White and CPSC</a>.',
	};
}

/* ---------- Phase 2b: brand age pages + lifespan article ---------- */
{
	const sf = d['serial-formats'];
	const ls = d.lifespans;
	const bp = d['brand-pages'];
	const ageOut = out['water-heater-age-calculator'];
	const adj = ls.adjustments;
	const lifeFor = (typeId) => ls.types[ls.toolTypes.options.find((o) => o.id === typeId).life];
	const typeLabel = (typeId) => ls.toolTypes.options.find((o) => o.id === typeId).label;
	const deltas = (hardness, flushed, anode, typeId) => [adj.hardness[hardness] ?? 0, flushed ? 0 : adj.noAnnualFlush, anode && typeId !== 'tankless' ? adj.anodeReplaced : 0];
	const statusLabel = ageOut.statusLabels;
	const pct = (f) => Math.round(f * 100);
	const SOURCE_NAMES = { 'insider.energytrust.org': 'Energy Trust of Oregon', 'www.cpsc.gov': 'U.S. CPSC recall notice', 'www.ruud.com': 'Rheem/Ruud lookup', 'www.bradfordwhite.com': 'Bradford White', 'inspectapedia.com': 'InspectApedia (third party)', 'building-center.org': 'Building Intelligence Center (third party)', 'www.building-center.org': 'Building Intelligence Center (third party)' };
	const sourceName = (url) => SOURCE_NAMES[new URL(url).host] || new URL(url).host;

	for (const [brandId, b] of Object.entries(bp.brands)) {
		const asOfIso = d.site.dateVerified[b.slug] || d.site.dateVerified['water-heater-age-calculator'];
		const asOf = new Date(`${asOfIso}T12:00:00Z`);
		const refYear = asOf.getUTCFullYear();
		const code = sf.brands[b.code || brandId];
		const option = sf.brandOptions.options.find((o) => o.id === b.option);
		const checks = b.checks.map((c) => {
			const m = F.decodeSerial(c.serial, code.patterns, refYear);
			const yearOk = m && (m.year === c.year || (c.cycle && m.otherYears.includes(c.year)));
			return {
				serial: c.serial,
				ours: m ? `${madeLabel(m)}${m.week ? ` (week ${m.week})` : ''}${m.otherYears.length ? `; also possible: ${m.otherYears.join(', ')}` : ''}` : 'not decoded',
				pattern: m ? m.patternId : '',
				published: c.published,
				match: Boolean(m && yearOk && m.month === c.month),
				cycle: Boolean(c.cycle),
				source: c.source,
				sourceUrl: c.sourceUrl,
			};
		});
		const bad = checks.filter((c) => !c.match);
		if (bad.length) throw new Error(`brand-pages ${brandId}: hand-check mismatch ${bad.map((c) => c.serial).join(', ')}`);
		const examples = b.examples.map((x) => {
			const m0 = F.decodeSerial(x.serial, code.patterns, refYear);
			const made = x.useOtherYear ? { ...m0, year: m0.otherYears[0], otherYears: [m0.year, ...m0.otherYears.slice(1)] } : m0;
			const base = lifeFor(x.type);
			const est = F.ageEstimate(made, asOf, base, deltas(x.hardness, x.flushed, x.anode, x.type), adj.minimumLifeYears, ls.statusBands);
			return {
				id: x.id, serial: x.serial, input: x,
				typeLabel: typeLabel(x.type).toLowerCase(),
				madeLabel: madeLabel(made), year: made.year, month: made.month, ...(m0.week ? { week: m0.week } : {}),
				latestYear: m0.year, otherYears: m0.otherYears, pattern: m0.patternId,
				age: fmt(est.age, 1),
				baseLow: base.low, baseTypical: base.typical, baseHigh: base.high,
				hardDelta: adj.hardness[x.hardness] ?? 0, flushDelta: x.flushed ? 0 : adj.noAnnualFlush, anodeDelta: x.anode && x.type !== 'tankless' ? adj.anodeReplaced : 0,
				lifeLow: est.life.low, lifeTypical: est.life.typical, lifeHigh: est.life.high,
				usedPct: pct(est.usedFraction),
				remainingMin: fmt(Math.max(0, est.remaining.min), 1), remainingMax: fmt(Math.max(0, est.remaining.max), 1),
				status: est.status, statusLabel: statusLabel[est.status],
			};
		});
		const formatRows = code.patterns.map((p) => ({
			id: p.id,
			rule: describePattern(p),
			example: p.example,
			era: p.era ? p.era : p.validFromYear ? `${p.validFromYear} and later` : p.validToYear ? `Up to about ${p.validToYear}` : 'All years',
			source: sourceName(p.sourceUrl || code.sourceUrl),
			sourceUrl: p.sourceUrl || code.sourceUrl,
		}));
		const def = { brand: b.option, serial: b.defaultSerial, type: b.defaultType || 'gas_tank', hardness: 'moderate', flushed: true, anode: false };
		const dd = describeAge({ ...def, madeYear: null, madeMonth: '' }, ageOut.client, asOf);
		const o = {
			meta: { ...derived('data/brand-pages.json, data/serial-formats.json and data/lifespans.json'), notes: `Brand page for ${code.label}. Default result, hand-checks and worked examples are computed as of ${asOfIso}. The live calculator uses the visitor's date.` },
			asOf: asOfIso,
			brand: brandId,
			label: code.label,
			alsoSoldAs: code.alsoSoldAs,
			caveat: option.caveat || '',
			defaultInputs: def,
			default: {
				status: dd.status, statusLabel: dd.statusLabel, headline: dd.headline,
				explain: `${dd.explain} (Example serial in the ${code.label} format, as of ${asOfIso}; type yours.)`,
				formula: dd.formula,
				sources: `${code.label} date code: ${[...new Set(formatRows.map((f) => f.source))].join(', ')}. Lifespan: DOE 2024 water heater rule and DOE FEMP. Adjustments are our editorial assumptions. <a href="/methodology/#water-heater-age-calculator">See every decoder test case</a>.`,
			},
			checks,
			checksMatched: checks.filter((c) => c.match).length,
			examples: Object.fromEntries(examples.map((e) => [e.id, e])),
			formatRows,
			client: ageOut.client,
		};
		if (brandId === 'bradford-white') {
			const y = code.patterns[0].fields.year;
			const mo = code.patterns[0].fields.month;
			o.yearLetters = [...y.letters].map((L, i) => ({ letter: L, years: y.cycleStarts.map((c) => c + i).join(', ') }));
			o.monthLetters = [...mo.letters].map((L, i) => ({ letter: L, month: MONTHS[i] }));
			o.cycleYears = y.cycleStarts[1] - y.cycleStarts[0];
			o.cycleStarts = y.cycleStarts;
		}
		if (code.patterns.some((p) => Object.values(p.fields).some((f) => f.type === 'ww'))) {
			const byMonth = new Map();
			for (let w = 1; w <= 53; w++) { const m = F.monthFromWeek(w); byMonth.set(m, [...(byMonth.get(m) || []), w]); }
			o.weekToMonth = [...byMonth.entries()].map(([m, ws]) => ({ month: MONTHS[m - 1], weeks: `${ws[0]}–${ws[ws.length - 1]}` }));
		}
		if (b.lookupMisses) o.lookupMisses = b.lookupMisses;
		out[b.slug] = o;
		// Methodology cross-check table: add the brand hand-checks that the age page does not already list.
		const have = new Set(ageOut.crossChecks.map((c) => c.label));
		for (const c of checks) {
			const label = `${code.label} serial ${c.serial}`;
			if (have.has(label) || ageOut.crossChecks.some((x) => x.id.endsWith(c.serial))) continue;
			ageOut.crossChecks.push({ id: `${brandId}-${c.serial}`, label, ours: c.ours, published: c.published, match: c.match, why: c.cycle ? `Same month. ${code.label} year letters repeat every 20 years; the decoder shows the latest year first and lists ${c.ours.split('also possible: ')[1] || ''}, which includes the date this source gives.` : 'Same month.', source: c.source, sourceUrl: c.sourceUrl });
		}
	}

	/* Lifespan article: how much of the expected life a unit of a given age has used. */
	{
		const asOfIso = d.site.dateVerified['how-long-does-a-water-heater-last'] || d.site.dateVerified['water-heater-age-calculator'];
		const asOf = new Date(`${asOfIso}T12:00:00Z`);
		const ages = [3, 6, 9, 12, 15, 18];
		const kinds = [
			{ key: 'tank', label: 'Tank (gas, electric or heat pump)', life: ls.types.gas_tank },
			{ key: 'tankless', label: 'Tankless', life: ls.types.gas_tankless },
		];
		const rows = ages.map((a) => {
			const made = { year: asOf.getUTCFullYear() - a, month: asOf.getUTCMonth() + 1, week: null };
			const row = { age: a };
			for (const k of kinds) {
				const e = F.ageEstimate(made, asOf, k.life, [], adj.minimumLifeYears, ls.statusBands);
				row[k.key] = `${pct(e.usedFraction)}% (${statusLabel[e.status].toLowerCase()})`;
			}
			return row;
		});
		const hard = F.adjustedLifespan(ls.types.gas_tank, [adj.hardness.veryHard, adj.noAnnualFlush], adj.minimumLifeYears);
		const care = F.adjustedLifespan(ls.types.gas_tank, [adj.anodeReplaced], adj.minimumLifeYears);
		const mv = ls.manufacturerViews;
		out['how-long-does-a-water-heater-last'] = {
			meta: { ...derived('data/lifespans.json'), notes: `Share of expected life used by a unit of each age, as of ${asOfIso}. Bars: lifespan ranges in years, scaled for the SVG chart.` },
			asOf: asOfIso,
			worstCase: { low: hard.low, typical: hard.typical, high: hard.high },
			bestCase: { low: care.low, typical: care.typical, high: care.high },
			tables: {
				ageShare: table(
					'How much of its expected life a water heater of each age has used',
					[{ key: 'age', label: 'Age (years)', num: true }, ...kinds.map((k) => ({ key: k.key, label: `${k.label}: ${k.life.typical}-year ${k.life.low === k.life.high ? 'average' : 'typical'} life` }))],
					rows,
					{ source: `Computed with the age calculator's formula (age ÷ typical life; bands: amber from ${pct(ls.statusBands.amberFrom)}%, red from ${pct(ls.statusBands.redFrom)}%) and DOE lifetimes from ${ls.meta.source}`, sourceUrl: ls.meta.sourceUrl, sourceDate: ls.meta.sourceDate },
				),
			},
			bars: (() => {
				const maxYears = 25;
				const scale = (y) => r((y / maxYears) * 100, 2);
				const list = [
					{ label: 'DOE: storage tank', low: ls.types.gas_tank.low, high: ls.types.gas_tank.high, typical: ls.types.gas_tank.typical, kind: 'doe' },
					{ label: 'DOE: tankless', low: ls.types.gas_tankless.low, high: ls.types.gas_tankless.high, typical: ls.types.gas_tankless.typical, kind: 'doe' },
					{ label: `${mv.rheem.label}: tank`, low: mv.rheem.tankLow, high: mv.rheem.tankHigh, kind: 'maker' },
					{ label: `${mv['ao-smith'].label}: tank`, low: mv['ao-smith'].tankLow, high: mv['ao-smith'].tankHigh, kind: 'maker' },
					{ label: `${mv.rheem.label}: tankless`, low: mv.rheem.tanklessLow, high: mv.rheem.tanklessHigh, kind: 'maker' },
					{ label: `${mv['ao-smith'].label}: tankless`, low: mv['ao-smith'].tanklessLow, high: mv['ao-smith'].tanklessHigh, kind: 'maker' },
				];
				return { maxYears, ticks: [0, 5, 10, 15, 20, 25].map((t) => ({ t, x: scale(t) })), rows: list.map((b) => ({ ...b, x: scale(b.low), w: r(Math.max(scale(b.high) - scale(b.low), 0.8), 2), ...(b.typical ? { tx: scale(b.typical) } : {}) })) };
			})(),
		};
	}
}

/* ---------- Phase 2 tools: tankless size calculator ---------- */
{
	const fx = d['fixture-flow-rates'];
	const tr = d['tankless-reference-units'];
	const el = d.electrical;
	const it = out['inlet-temps'].states;
	const uef = out['uef-defaults'].types;
	const setpoint = d.site.defaults.setpointTempF.value;
	const defInlet = fx.doeExample.inletF;
	const gasSizes = tr.sizeChart.gasInputsBtuh;
	const units = el.referenceUnits;
	const size = (counts, inletF, eff = uef.gas_tankless.typical, lowFlow = false) => F.tanklessSizing({
		fixtures: Object.entries(counts).map(([k, count]) => ({ count, gpm: lowFlow ? fx.fixtures[k].lowFlowGpm : fx.fixtures[k].gpm })),
		setpointF: setpoint, inletF: inletF, gasEfficiency: eff, volts: el.residentialVolts, gasSizesBtuh: gasSizes, electricUnits: units,
	});

	const defCounts = { shower: 2 };
	const s0 = size(defCounts, defInlet);
	// Worked example: Minnesota (coldest month), two showers + kitchen faucet, gas, then electric and low-flow.
	const exState = 'MN';
	const exInlet = it[exState].coldestMonth;
	const exCounts = { shower: 2, kitchenFaucet: 1 };
	const ex = size(exCounts, exInlet);
	const exLow = size(exCounts, exInlet, uef.gas_tankless.typical, true);
	// Climate comparison for the FAQ (family of 4: two showers + bathroom faucet) in a warm, middle and cold state.
	const climate = ['FL', 'TN', 'MN'].map((st) => ({ st, name: it[st].name, inlet: it[st].coldestMonth, ...size({ shower: 2, bathroomFaucet: 1 }, it[st].coldestMonth) }));
	const coldest = Object.values(it).reduce((a, b) => (b.coldestMonth < a.coldestMonth ? b : a));
	const warmest = Object.values(it).reduce((a, b) => (b.coldestMonth > a.coldestMonth ? b : a));

	const riseCols = tr.sizeChart.risesF;
	const gasChartRows = riseCols.map((rise) => ({ rise: `${rise} °F`, ...Object.fromEntries(gasSizes.map((b) => [`g${b}`, fmt(F.maxGpmByRise(b * uef.gas_tankless.typical, [rise])[0].gpm, 1)])) }));
	const elecKws = units.map((u) => u.kw);
	const elecChartRows = riseCols.map((rise) => ({ rise: `${rise} °F`, ...Object.fromEntries(elecKws.map((k) => [`e${k}`, fmt(F.maxGpmByRise(F.btuhFromKw(k), [rise])[0].gpm, 1)])) }));

	// Cross-checks against manufacturer sizing literature.
	const xc = [];
	for (const u of tr.gasUnits.filter((x) => ['RUR98i', 'RL94i', 'V65i'].includes(x.model))) {
		for (const [rise, pub] of [[70, u.gpmAt70], [50, u.gpmAt50]]) {
			const gpm = F.maxGpmByRise(u.inputBtuh * u.efficiency, [rise])[0].gpm;
			xc.push({ id: `rinnai-${u.model}-${rise}`, label: `Rinnai ${u.model}, ${fmt(u.inputBtuh)} BTU/h, efficiency ${u.efficiency}, ${rise} °F rise`, ours: `${fmt(gpm, 1)} GPM`, published: `${fmt(pub, 1)} GPM`, diffPct: r(((gpm - pub) / pub) * 100, 1), why: 'Within a few percent. Rinnai rounds and rates with its own test efficiency; our formula uses the rated Energy Factor.', source: 'Rinnai tankless model chart (2016)', sourceUrl: tr.meta.sourceUrl });
		}
	}
	for (const ex2 of tr.noritzExample.examples) {
		const g = F.totalGpm(Object.entries(ex2.fixtures).map(([k, count]) => ({ count, gpm: tr.noritzExample.fixtureGpm[k] })));
		const s = F.tanklessSizing({ fixtures: [{ count: 1, gpm: g }], setpointF: tr.noritzExample.setpointF, inletF: tr.noritzExample.groundwaterF, gasEfficiency: uef.gas_tankless.typical, volts: el.residentialVolts, gasSizesBtuh: gasSizes, electricUnits: units });
		xc.push({ id: ex2.id, label: `Noritz example: ${ex2.label}, ${tr.noritzExample.groundwaterF} °F groundwater, ${tr.noritzExample.setpointF} °F setpoint`, ours: `${fmt(s.gpm)} GPM, ${s.rise} °F rise; ${fmt(s.gasInputBtuh)} BTU/h input; ${s.gasUnitsAtLargest} unit${s.gasUnitsAtLargest > 1 ? 's' : ''} of ${fmt(s.largestGasBtuh)} BTU/h`, published: `${fmt(ex2.gpm)} GPM, ${tr.noritzExample.riseF} °F rise; ${ex2.pick}`, diffPct: r(((s.gpm - ex2.gpm) / ex2.gpm) * 100, 1), why: s.gasUnitsAtLargest === 1 || ex2.id === 'noritz-2' ? 'Same flow, rise and number of units.' : `Same flow and rise. Our required input is ${fmt(r(((s.gasInputBtuh - s.largestGasBtuh) / s.largestGasBtuh) * 100, 1), 1)}% above one ${fmt(s.largestGasBtuh)} BTU/h unit at UEF ${uef.gas_tankless.typical}, so we say one unit is just short; Noritz matches the "approximate flow" on its chart and picks one large unit. Both agree it is a one-large-unit or two-unit job.`, source: tr.noritzExample.source, sourceUrl: tr.noritzExample.sourceUrl });
	}
	for (const p of el.riseChartCheck.points.filter((x) => [3, 4, 5].includes(x.gpm))) {
		const kwNeeded = F.electricKw(F.outputBtuh(p.gpm, p.riseF));
		const unit = F.referenceUnitFor(kwNeeded, units);
		xc.push({ id: `rheem-electric-${p.gpm}`, label: `Electric: ${fmt(p.gpm, 1)} GPM at ${p.riseF} °F rise`, ours: `${fmt(kwNeeded, 1)} kW needed`, published: `${el.riseChartCheck.model} (${el.riseChartCheck.kw} kW) delivers this flow and rise`, diffPct: r(((kwNeeded - el.riseChartCheck.kw) / el.riseChartCheck.kw) * 100, 1), why: `Within ${fmt(Math.abs(r(((kwNeeded - el.riseChartCheck.kw) / el.riseChartCheck.kw) * 100, 1)), 1)}% of the unit's rating. Rheem rounds the rise to whole degrees; when our need is a fraction above ${el.riseChartCheck.kw} kW the tool says one unit is just short, which errs on the safe side.`, source: `${el.meta.source}, temperature rise table`, sourceUrl: el.meta.sourceUrl });
	}

	out['tankless-water-heater-size-calculator'] = {
		meta: { ...derived('data/fixture-flow-rates.json, data/tankless-reference-units.json, data/electrical.json, data/uef-ranges.json and data/groundwater-temp-by-state.json'), notes: 'Default result, size charts, worked example and manufacturer cross-check for the tankless size calculator.' },
		defaultInputs: { counts: defCounts, inletF: defInlet, setpointF: setpoint, fuel: 'gas', efficiency: uef.gas_tankless.typical },
		default: null,
		example: {
			state: it[exState].name, inlet: exInlet, setpoint, counts: exCounts,
			showerGpm: fx.fixtures.shower.gpm, kitchenGpm: fx.fixtures.kitchenFaucet.gpm,
			gpm: fmt(ex.gpm, 1), rise: fmt(ex.rise, 1), output: fmt(ex.outputBtuh), gasInput: fmt(ex.gasInputBtuh), gasUnits: ex.gasUnitsAtLargest, gasSize: ex.gasSizeBtuh ? fmt(ex.gasSizeBtuh) : '', largestGas: fmt(ex.largestGasBtuh),
			kw: fmt(ex.kw, 1), amps: fmt(ex.amps), electricUnits: ex.electricUnitsAtLargest, largestKw: fmt(ex.largestKw),
			lowShowerGpm: fx.fixtures.shower.lowFlowGpm, lowGpm: fmt(exLow.gpm, 1), lowGasInput: fmt(exLow.gasInputBtuh), lowGasUnits: exLow.gasUnitsAtLargest, lowGasSize: exLow.gasSizeBtuh ? fmt(exLow.gasSizeBtuh) : '', lowKw: fmt(exLow.kw, 1),
			uef: uef.gas_tankless.typical,
		},
		defaultCase: { gpm: fmt(s0.gpm, 1), lpm: fmt(s0.gpm * F.PHYSICS.litersPerGallon, 1), rise: s0.rise, gasInput: fmt(s0.gasInputBtuh), gasSize: s0.gasSizeBtuh ? fmt(s0.gasSizeBtuh) : '', kw: fmt(s0.kw, 1), amps: fmt(s0.amps), electricUnits: s0.electricUnitsAtLargest, inlet: defInlet },
		climate: climate.map((c) => ({ st: c.st, name: c.name, inlet: fmt(c.inlet), rise: fmt(c.rise), gpm: fmt(c.gpm, 1), gasInput: fmt(c.gasInputBtuh), gasUnits: c.gasUnitsAtLargest, kw: fmt(c.kw, 1) })),
		chartHighlight: (() => {
			const rise = tr.sizeChart.risesF.includes(tr.publishedRisesF[0]) ? tr.publishedRisesF[0] : tr.sizeChart.risesF[0];
			const gasBtuh = Math.max(...gasSizes);
			const kw = Math.max(...elecKws);
			return { rise, gasBtuh: fmt(gasBtuh), gasGpm: fmt(F.maxGpmByRise(gasBtuh * uef.gas_tankless.typical, [rise])[0].gpm, 1), kw: fmt(kw), elecGpm: fmt(F.maxGpmByRise(F.btuhFromKw(kw), [rise])[0].gpm, 1) };
		})(),
		publishedRisesF: tr.publishedRisesF,
		inletExtremes: { coldest: { name: coldest.name, f: fmt(coldest.coldestMonth) }, warmest: { name: warmest.name, f: fmt(warmest.coldestMonth) } },
		crossChecks: xc,
		tables: {
			gasChart: table(
				`Gas tankless size chart: maximum GPM by temperature rise (UEF ${uef.gas_tankless.typical})`,
				[{ key: 'rise', label: 'Temperature rise' }, ...gasSizes.map((b) => ({ key: `g${b}`, label: `${fmt(b)} BTU/h`, unit: 'GPM', num: true }))],
				gasChartRows,
				{ source: `Computed with GPM = input × UEF ÷ (${F.PHYSICS.lbPerGallon} × ${F.PHYSICS.minutesPerHour} × rise); input sizes from the Rinnai model chart; UEF = ENERGY STAR median`, sourceUrl: 'https://hotwatermath.com/methodology/#tankless-water-heater-size-calculator', sourceDate: tr.meta.sourceDate },
			),
			electricChart: table(
				'Electric tankless size chart: maximum GPM by temperature rise',
				[{ key: 'rise', label: 'Temperature rise' }, ...elecKws.map((k) => ({ key: `e${k}`, label: `${fmt(k)} kW`, unit: 'GPM', num: true }))],
				elecChartRows,
				{ source: `Computed with GPM = kW × ${F.PHYSICS.btuPerKwh} ÷ (${F.PHYSICS.lbPerGallon} × ${F.PHYSICS.minutesPerHour} × rise); kW sizes from ${el.meta.source}`, sourceUrl: el.meta.sourceUrl, sourceDate: el.meta.sourceDate },
			),
		},
		client: {
			setpointF: setpoint,
			defaultInletF: defInlet,
			fixtures: Object.fromEntries(Object.entries(fx.fixtures).map(([k, f]) => [k, { label: f.label, gpm: f.gpm, lowFlowGpm: f.lowFlowGpm }])),
			states: Object.fromEntries(Object.entries(it).map(([k, s]) => [k, { name: s.name, coldestMonth: s.coldestMonth, annualAvg: s.annualAvg }])),
			gasEfficiency: { condensing: uef.gas_tankless.typical, nonCondensing: uef.gas_tankless.low },
			electricEfficiency: uef.electric_tankless.typical,
			gasSizesBtuh: gasSizes,
			electricUnits: units,
			volts: el.residentialVolts,
			rules: tr.rules,
		},
	};
	const tOut = out['tankless-water-heater-size-calculator'];
	const td = describeTankless({ state: '', inlet: defInlet, setpoint, fuel: 'gas', efficiency: uef.gas_tankless.typical, lowflow: false, ...Object.fromEntries(FIXTURE_KEYS.map((k) => [k, defCounts[k] || 0])) }, tOut.client);
	tOut.default = {
		status: td.status, statusLabel: td.statusLabel, headline: td.headline, explain: td.explain, formula: td.formula,
		sources: `Defaults: ${setpoint} °F setpoint (DOE FEMP); fixture flows are federal maximums (10 CFR 430.32), low-flow values EPA WaterSense; ${defInlet} °F incoming water is the DOE example until you pick a state (then NOAA normals with the DOE EnergyPlus mains correlation, coldest month); UEF ${uef.gas_tankless.typical} is the median ENERGY STAR certified gas tankless unit. <a href="/methodology/#tankless-water-heater-size-calculator">See our results checked against Rinnai, Noritz and Rheem charts</a>.`,
	};
}

/* ---------- Phase 3 tools: tank size calculator ---------- */
{
	const ph = d['peak-hour-demand'];
	const hg = d['household-size-guides'].guides;
	const fhrTable = out['fhr-table'].rows;
	const presets = ph.householdPresets.byPeople;
	const people = Object.keys(presets).map(Number).sort((a, b) => a - b);
	const acts = (counts) => ACTIVITY_KEYS.map((k) => ({ key: k, count: counts[k] || 0, gallons: ph.activities[k].gallons }));
	const sizeFor = (counts, margin = 0) => F.tankSizing(acts(counts), margin, ph.fhrRule.minimumFhrToPhdRatio, fhrTable);
	const fuelName = { gas: 'Gas', electric: 'Electric', heatpump: 'Heat pump' };
	const pickText = (p) => (p.fits === 'none' ? `none (largest ${p.nominalGallons} gal rates ${fmt(p.row.fhrHigh)})` : `${p.nominalGallons} gal${p.fits === 'some-models' ? ' (some models)' : ''}`);
	const client = {
		activities: Object.fromEntries(ACTIVITY_KEYS.map((k) => [k, { label: ph.activities[k].label, gallons: ph.activities[k].gallons }])),
		presets: Object.fromEntries(people.map((n) => [n, Object.fromEntries(ACTIVITY_KEYS.map((k) => [k, presets[n][k] || 0]))])),
		fhrRatio: ph.fhrRule.minimumFhrToPhdRatio,
		margin: ph.margin.share,
		fhrTable,
		guide: { label: hg.american.label, rows: hg.american.rows },
	};
	const defPeople = ph.householdPresets.defaultPeople;
	const byPeople = people.map((n) => {
		const s = sizeFor(presets[n]);
		const am = hg.american.rows.find((x) => x.people === n);
		const rhAll = hg.rheem.rows.filter((x) => n >= x.peopleFrom && n <= x.peopleTo);
		const rh = rhAll[0];
		const ao = hg.aosmith.rows.find((x) => n >= x.peopleFrom && n <= x.peopleTo);
		return { n, s, am, rh, rhAll, ao };
	});
	const label = (n) => `${n} ${n === 1 ? 'person' : 'people'}`;
	const presetText = (c) => ACTIVITY_KEYS.filter((k) => c[k]).map((k) => `${c[k]} ${ph.activities[k].label.toLowerCase()}`).join(', ');
	// Cross-checks against manufacturer sizing guides.
	const xc = [];
	for (const x of byPeople.filter((y) => y.am)) {
		const inside = x.s.phd >= x.am.low && x.s.phd <= x.am.high;
		xc.push({ id: `american-${x.n}`, label: `${label(x.n)}: our typical busy hour (${presetText(presets[x.n])})`, ours: `${fmt(x.s.phd)} gal peak-hour demand → FHR ${fmt(x.s.fhr)}+`, published: `FHR ${x.am.low}–${x.am.high} gal`, match: inside, why: inside ? 'Inside the published range. Our busy-hour presets are set so they land in this range; the AHRI per-use gallons do the rest.' : 'Outside the range; see the note.', source: hg.american.source, sourceUrl: hg.american.sourceUrl });
	}
	const rng = (x) => (x.low === x.high ? `${x.low}` : `${x.low}–${x.high}`);
	for (const x of byPeople.filter((y) => y.rh && y.n >= 2)) {
		const g = x.s.gas;
		const inside = g.fits !== 'none' && x.rhAll.some((y) => g.nominalGallons >= y.low && g.nominalGallons <= y.high);
		xc.push({ id: `rheem-${x.n}`, label: `${label(x.n)}, gas tank`, ours: `${pickText(g)}`, published: x.rhAll.map((y) => `${y.peopleLabel} people: ${rng(y)} gal`).join('; '), match: inside, why: inside ? `Inside Rheem's range${x.rhAll.length > 1 ? ' (Rheem\'s rows overlap, so two rows apply)' : ''}.` : `Our pick comes from the first hour rating of real models: the busy hour needs FHR ${fmt(x.s.fhr)}, which a ${pickText(g)} gas tank meets.`, source: hg.rheem.source, sourceUrl: hg.rheem.sourceUrl });
	}
	{
		const ex = hg.rheem.example;
		const ours = sizeFor({ shower: ex.showers, clothesWasher: ex.laundryLoads, foodPrep: ex.kitchenUses });
		xc.push({ id: 'rheem-example', label: `Rheem's worked example: ${ex.label.toLowerCase()}`, ours: `${fmt(ours.phd)} gal (AHRI per-use values)`, published: `${ex.peakHourDemand} gal, so FHR ${ex.minimumFhr}+`, diffPct: r(((ours.phd - ex.peakHourDemand) / ex.peakHourDemand) * 100, 1), why: `Same method, different per-use gallons: AHRI counts ${ph.activities.shower.gallons} gal per shower and ${ph.activities.clothesWasher.gallons} per washer load, Rheem ${ex.showersGallons / ex.showers} and ${ex.laundryGallons}. Our figure errs on the large side, which avoids running out; you can lower the counts if your showers are short.`, source: hg.rheem.source, sourceUrl: hg.rheem.sourceUrl });
	}
	for (const x of byPeople.filter((y) => y.ao && y.n >= 3)) {
		const vols = ['gas', 'electric', 'heatpump'].map((f) => x.s[f]).filter((p) => p.fits !== 'none').map((p) => p.nominalGallons);
		const inside = vols.some((g) => g >= x.ao.low && g <= x.ao.high);
		xc.push({ id: `aosmith-${x.n}`, label: `${label(x.n)}, any fuel`, ours: ['gas', 'electric', 'heatpump'].map((f) => `${fuelName[f]} ${pickText(x.s[f])}`).join('; '), published: `${x.ao.low}–${x.ao.high} gal`, match: inside, why: inside ? 'At least one fuel lands in the published range; gas needs fewer gallons because it reheats faster.' : `Lower than A. O. Smith's volume range: a ${pickText(x.s.gas)} gas tank already meets FHR ${fmt(x.s.fhr)}. Volume tables cover every fuel at once, so they run large for gas.`, source: hg.aosmith.source, sourceUrl: hg.aosmith.sourceUrl });
	}
	const ahri = ph.example;
	const ahriS = sizeFor({ shower: ahri.showers, shaving: ahri.shaves, shampoo: ahri.shampoos, handDishwashing: ahri.handDishwashing });
	const defCounts = presets[defPeople];
	const s0 = sizeFor(defCounts);
	const sMargin = sizeFor(defCounts, ph.margin.share);
	const g40 = fhrTable.find((x) => x.fuel === 'gas' && x.nominalGallons === 40);
	const g50 = fhrTable.find((x) => x.fuel === 'gas' && x.nominalGallons === 50);
	const e40 = fhrTable.find((x) => x.fuel === 'electric' && x.nominalGallons === 40);
	const e50 = fhrTable.find((x) => x.fuel === 'electric' && x.nominalGallons === 50);
	const hp50 = fhrTable.find((x) => x.fuel === 'heatpump' && x.nominalGallons === 50);
	out['water-heater-size-calculator'] = {
		meta: { ...derived('data/peak-hour-demand.json, data/fhr-to-tank-size.json, data/energystar-water-heaters.json and data/household-size-guides.json'), notes: 'Default result, household table, worked example and manufacturer cross-check for the tank size calculator.' },
		defaultInputs: { people: defPeople, counts: defCounts, fuel: 'gas', margin: false },
		default: null,
		defaultCase: { phd: fmt(s0.phd), fhr: fmt(s0.fhr), gas: s0.gas.nominalGallons, electric: s0.electric.nominalGallons, heatpump: s0.heatpump.nominalGallons, marginFhr: fmt(sMargin.fhr), marginPct: fmt(ph.margin.share * 100), preset: presetText(defCounts) },
		example: { counts: defCounts, people: defPeople, lines: ACTIVITY_KEYS.filter((k) => defCounts[k]).map((k) => ({ label: ph.activities[k].label, count: defCounts[k], gallons: ph.activities[k].gallons, total: defCounts[k] * ph.activities[k].gallons })), phd: fmt(s0.phd), fhr: fmt(s0.fhr), gas: s0.gas.nominalGallons, gasFhr: fmt(s0.gas.row.fhrTypical), electric: s0.electric.nominalGallons, electricFhr: fmt(s0.electric.row.fhrTypical), heatpump: s0.heatpump.nominalGallons, heatpumpFhr: fmt(s0.heatpump.row.fhrTypical) },
		ahri: { phd: fmt(ahriS.phd), published: ahri.peakHourDemand },
		compare: {
			gas40: fmt(g40.fhrTypical), gas50: fmt(g50.fhrTypical), elec40: fmt(e40.fhrTypical), elec50: fmt(e50.fhrTypical), hp50: fmt(hp50.fhrTypical),
			gas40Range: `${fmt(g40.fhrLow)}–${fmt(g40.fhrHigh)}`, gas50Range: `${fmt(g50.fhrLow)}–${fmt(g50.fhrHigh)}`,
			gasVsElec50: fmt(g50.fhrTypical - e50.fhrTypical), step40to50Gas: fmt(g50.fhrTypical - g40.fhrTypical), step40to50Elec: fmt(e50.fhrTypical - e40.fhrTypical),
		},
		byPeople: byPeople.map((x) => ({ n: x.n, phd: fmt(x.s.phd), fhr: fmt(x.s.fhr), gas: x.s.gas.fits === 'none' ? '' : x.s.gas.nominalGallons, electric: x.s.electric.fits === 'none' ? '' : x.s.electric.nominalGallons, heatpump: x.s.heatpump.fits === 'none' ? '' : x.s.heatpump.nominalGallons })),
		crossChecks: xc,
		tables: {
			households: table(
				'Tank size by household size: our busy-hour preset vs manufacturer guides',
				[
					{ key: 'people', label: 'People' },
					{ key: 'phd', label: 'Our peak hour', unit: 'gal', num: true },
					{ key: 'gas', label: 'Gas', unit: 'gal', num: true },
					{ key: 'electric', label: 'Electric', unit: 'gal', num: true },
					{ key: 'hp', label: 'Heat pump', unit: 'gal', num: true },
					{ key: 'am', label: 'American FHR', unit: 'gal', num: true },
					{ key: 'rh', label: 'Rheem gas', unit: 'gal', num: true },
				],
				byPeople.map((x) => ({ people: String(x.n), phd: fmt(x.s.phd), gas: pickText(x.s.gas), electric: x.s.electric.fits === 'none' ? 'heat pump' : pickText(x.s.electric), hp: pickText(x.s.heatpump), am: x.am ? `${x.am.low}–${x.am.high}` : '–', rh: x.rh ? (x.rh.low === x.rh.high ? `${x.rh.low}` : `${x.rh.low}–${x.rh.high}`) : '–' })),
				{ source: `Computed with the calculator (AHRI per-use gallons, FHR of real models); American Water Heater selection guide; Rheem household-size guide`, sourceUrl: 'https://hotwatermath.com/methodology/#water-heater-size-calculator', sourceDate: d['household-size-guides'].meta.sourceDate },
			),
		},
		client,
	};
	const tz = out['water-heater-size-calculator'];
	const td = describeTank({ people: String(defPeople), fuel: 'gas', margin: false, ...Object.fromEntries(ACTIVITY_KEYS.map((k) => [k, defCounts[k] || 0])) }, client);
	tz.default = {
		status: td.status, statusLabel: td.statusLabel, headline: td.headline, explain: td.explain, formula: td.formula,
		sources: `Per-use gallons: AHRI peak-hour worksheet. First hour ratings: Rheem spec sheets (gas, electric) and the ENERGY STAR certified list (heat pump). The ${defPeople}-person busy hour is our assumption; change the counts. <a href="/methodology/#water-heater-size-calculator">See our results checked against American, Rheem and A. O. Smith sizing guides</a>.`,
	};
}

/* ---------- Phase 3 tools: repair or replace ---------- */
{
	const dr = d['decision-rules'];
	const ls = d.lifespans;
	const up = out['unit-prices'].units;
	const uef = out['uef-defaults'].types;
	const hw = d['hot-water-use'];
	const ep = out['energy-prices'].us;
	const typeDefs = [
		{ id: 'gas_tank', label: 'Gas tank', life: ls.types.gas_tank.typical, uefId: 'gas_tank' },
		{ id: 'electric_tank', label: 'Electric tank', life: ls.types.electric_tank.typical, uefId: 'electric_tank' },
		{ id: 'heat_pump', label: 'Heat pump (hybrid)', life: ls.types.heat_pump.typical, uefId: 'heat_pump' },
		{ id: 'tankless', label: 'Gas tankless', life: ls.types.gas_tankless.typical, uefId: 'gas_tankless' },
		{ id: 'electric_tankless', label: 'Electric tankless', life: ls.types.electric_tankless.typical, uefId: 'electric_tankless' },
	];
	const types = Object.fromEntries(typeDefs.map((t) => {
		const priceKey = dr.replacementPriceKey[t.id];
		const upId = dr.upgradeTo[t.id];
		return [t.id, {
			label: t.label, life: t.life, uefId: t.uefId, uef: uef[t.uefId].typical, fuel: dr.fuelOf[t.id],
			replacementUsd: up[priceKey].typical, replacementLabel: up[priceKey].label, replacementRange: `${fmt(up[priceKey].low)}–${fmt(up[priceKey].high)}`,
			upgrade: { id: upId, label: uef[upId].label, short: dr.upgradeLabel[upId], uef: uef[upId].typical, fuel: dr.fuelOf[upId] },
		}];
	}));
	const client = {
		types, rules: dr.repairOrReplace, defaultInputs: dr.defaultInputs,
		symptoms: dr.symptoms.items.map((s) => ({ id: s.id, label: s.label, effect: s.effect, note: s.note })),
		prices: { usdPerKwh: ep.usdPerKwh, usdPerTherm: ep.usdPerTherm },
		use: { gallonsPerDay: hw.typicalHouseholdGallonsPerDay.value, riseF: hw.testConditions.riseF },
	};
	const rules = dr.repairOrReplace;
	const ask = (age, type, repair, extra = {}) => F.repairOrReplace({ ageYears: age, expectedLife: types[type].life, repairCost: repair, replacementCost: types[type].replacementUsd, tankLeaking: false, ...extra }, rules);
	// Most you should pay for a repair, by type and age band.
	const limits = typeDefs.map((t) => ({ label: t.label, price: fmt(types[t.id].replacementUsd), young: fmt(r(types[t.id].replacementUsd * rules.repairShareAlways)), oldFrom: fmt(r(rules.ageShare * t.life, 1), 1), old: fmt(r(types[t.id].replacementUsd * rules.repairShareWhenOld)) }));
	// Energy comparison by type.
	const energy = typeDefs.filter((t) => dr.upgradeTo[t.id] !== t.uefId).map((t) => {
		const T = types[t.id];
		const now = F.yearlyEnergyCost(client.use.gallonsPerDay, client.use.riseF, T.fuel, T.uef, client.prices);
		const next = F.yearlyEnergyCost(client.use.gallonsPerDay, client.use.riseF, T.upgrade.fuel, T.upgrade.uef, client.prices);
		return { label: T.label, now: fmt(now.year), up: T.upgrade.label, next: fmt(next.year), save: fmt(F.yearlySaving(now, next)) };
	});
	// Cross-checks against manufacturer guidance (Rheem repair-vs-replace article, A. O. Smith manual).
	const rh = dr.symptoms.items.find((s) => s.id === 'tankLeak');
	const fl = dr.symptoms.items.find((s) => s.id === 'flooded');
	const gp = types.gas_tank.replacementUsd;
	const xc = [];
	{
		const x = ask(4, 'gas_tank', 0, { tankLeaking: true });
		xc.push({ id: 'leak', label: '4-year-old gas tank, leak from the tank body', ours: x.verdict, published: 'Replacement usually required', match: x.verdict === 'replace', why: 'Same answer at any age or repair price.', source: rh.source.split('"')[1] ? 'Rheem, When to Repair vs. Replace (2026)' : rh.source, sourceUrl: rh.sourceUrl });
	}
	{
		const x = ask(5, 'gas_tank', r(gp * 0.6));
		xc.push({ id: 'over-half', label: `5-year-old gas tank, repair ${fmt(r(gp * 0.6))} vs ${fmt(gp)} new (${Math.round(x.share * 100)}%)`, ours: x.verdict, published: 'Repair over 50% of a new unit: replacement is typically better', match: x.verdict === 'replace', why: 'Same 50% rule.', source: 'Rheem, When to Repair vs. Replace (2026)', sourceUrl: rh.sourceUrl });
	}
	{
		const x = ask(5, 'electric_tank', r(types.electric_tank.replacementUsd * 0.15));
		xc.push({ id: 'young-cheap', label: `5-year-old electric tank, element repair ${fmt(r(types.electric_tank.replacementUsd * 0.15))} (${Math.round(x.share * 100)}% of new)`, ours: x.verdict, published: 'Under 6–8 years, no tank corrosion or leak, repair well below 50%: repair', match: x.verdict === 'repair', why: 'Same answer.', source: 'Rheem, When to Repair vs. Replace (2026)', sourceUrl: rh.sourceUrl });
	}
	{
		const x = ask(6, 'gas_tank', r(gp * 0.35));
		xc.push({ id: 'young-mid', label: `6-year-old gas tank, repair ${fmt(r(gp * 0.35))} (${Math.round(x.share * 100)}% of new)`, ours: x.verdict, published: 'Under 6–8 years and below 50%: repair', match: false, why: `We are stricter: between ${Math.round(rules.repairShareWhenOld * 100)}% and ${Math.round(rules.repairShareAlways * 100)}% we say borderline and suggest a second quote, because Rheem's "well below 50%" has no number.`, source: 'Rheem, When to Repair vs. Replace (2026)', sourceUrl: rh.sourceUrl });
	}
	{
		const x = ask(12, 'gas_tank', r(gp * 0.3));
		xc.push({ id: 'old-mid', label: `12-year-old gas tank, repair ${fmt(r(gp * 0.3))} (${Math.round(x.share * 100)}% of new)`, ours: x.verdict, published: '10–15+ years with recurring issues: generally replace', match: x.verdict === 'replace', why: `Same direction. Our "old" line is ${rules.ageShare} × ${types.gas_tank.life} = ${fmt(r(rules.ageShare * types.gas_tank.life, 2), 2)} years (DOE-based life), close to Rheem's 10 years.`, source: 'Rheem, When to Repair vs. Replace (2026)', sourceUrl: rh.sourceUrl });
	}
	{
		const x = ask(3, 'electric_tank', 0, { flooded: true });
		xc.push({ id: 'flood', label: '3-year-old electric tank whose thermostats were under water', ours: x.verdict, published: 'The entire water heater must be replaced', match: x.verdict === 'replace', why: 'Same answer.', source: 'A. O. Smith Use and Care Guide (2021)', sourceUrl: fl.sourceUrl });
	}
	const di = dr.defaultInputs;
	const ex1 = ask(di.ageYears, di.type, di.repairUsd);
	out['repair-or-replace-water-heater'] = {
		meta: { ...derived('data/decision-rules.json, data/lifespans.json, data/cost-ranges.json, data/uef-ranges.json, data/hot-water-use.json and data/energy-prices.json'), notes: 'Default result, repair limits by type, energy comparison and cross-check for the repair-or-replace tool. Replacement prices are equipment only until labor data is added.' },
		defaultInputs: { ...di, replace: types[di.type].replacementUsd },
		default: null,
		defaultCase: { age: di.ageYears, repair: fmt(di.repairUsd), replace: fmt(types[di.type].replacementUsd), share: Math.round(ex1.share * 100), verdict: ex1.verdict, life: types[di.type].life, oldFrom: fmt(ex1.oldFromYears, 2) },
		rulesPct: { always: Math.round(rules.repairShareAlways * 100), age: Math.round(rules.ageShare * 100), old: Math.round(rules.repairShareWhenOld * 100) },
		examples: [
			{ age: 11, type: 'gas_tank', repair: 350 },
			{ age: 4, type: 'electric_tank', repair: 150 },
			{ age: 9, type: 'heat_pump', repair: 600 },
		].map((e) => { const x = ask(e.age, e.type, e.repair); return { ...e, label: types[e.type].label, life: types[e.type].life, replace: fmt(types[e.type].replacementUsd), repairFmt: fmt(e.repair), share: Math.round(x.share * 100), used: Math.round(x.lifeUsed * 100), verdict: x.verdict, oldFrom: fmt(x.oldFromYears, 2) }; }),
		energy,
		crossChecks: xc,
		tables: {
			limits: table(
				'Most to pay for a repair, by type (our rules applied to typical equipment prices)',
				[
					{ key: 'label', label: 'Type' },
					{ key: 'price', label: 'New unit (equipment)', unit: '$', num: true },
					{ key: 'young', label: 'Repair limit, younger unit', unit: '$', num: true },
					{ key: 'oldFrom', label: '"Old" from', unit: 'years', num: true },
					{ key: 'old', label: 'Repair limit, old unit', unit: '$', num: true },
				],
				limits,
				{ source: `Rules from data/decision-rules.json (${Math.round(rules.repairShareAlways * 100)}% / ${Math.round(rules.ageShare * 100)}% of life / ${Math.round(rules.repairShareWhenOld * 100)}%); typical life from DOE; equipment prices from Home Depot listings (${d['cost-ranges'].lastReviewed}), before installation`, sourceUrl: 'https://hotwatermath.com/methodology/#repair-or-replace-water-heater', sourceDate: d['cost-ranges'].lastReviewed },
			),
			energy: table(
				'Yearly running cost: a standard new unit vs the efficient upgrade',
				[{ key: 'label', label: 'Replacing a' }, { key: 'now', label: 'Standard new unit', unit: '$/yr', num: true }, { key: 'up', label: 'Upgrade' }, { key: 'next', label: 'Upgrade cost', unit: '$/yr', num: true }, { key: 'save', label: 'Saving', unit: '$/yr', num: true }],
				energy,
				{ source: `Computed: ${client.use.gallonsPerDay} gal/day at a ${client.use.riseF} °F rise (DOE test day), UEF medians (Rheem spec sheets, ENERGY STAR), U.S. average prices $${client.prices.usdPerKwh}/kWh and $${client.prices.usdPerTherm}/therm (EIA)`, sourceUrl: 'https://hotwatermath.com/methodology/#repair-or-replace-water-heater', sourceDate: d['energy-prices'].meta.sourceDate },
			),
		},
		client,
	};
	const rz = out['repair-or-replace-water-heater'];
	const rd = describeRepair({ age: di.ageYears, type: di.type, repair: di.repairUsd, replace: types[di.type].replacementUsd }, client);
	rz.default = {
		status: rd.status, statusLabel: rd.statusLabel, headline: rd.headline, explain: rd.explain, formula: rd.formula,
		sources: `Rules: ours, stated on /methodology. Typical life: DOE (${types.gas_tank.life} years for tanks, ${types.tankless.life} for tankless). New-unit price: typical equipment price from Home Depot listings (${d['cost-ranges'].lastReviewed}), before installation; type your installed quote if you have one. <a href="/methodology/#repair-or-replace-water-heater">See our rules checked against Rheem and A. O. Smith guidance</a>.`,
	};
}

/* ---------- write + manifest ---------- */
for (const [name, obj] of Object.entries(out)) {
	writeFileSync(join(outDir, `${name}.json`), JSON.stringify(obj, null, 2) + '\n');
	console.log(`wrote data/computed/${name}.json`);
}
const hash = (f) => createHash('sha256').update(readFileSync(join(root, f))).digest('hex').slice(0, 16);
writeFileSync(
	join(outDir, '_manifest.json'),
	JSON.stringify({ meta: { source: 'npm run build:data', sourceUrl: '', sourceDate: '', notes: 'Hashes of the build inputs; scripts/validate-data.mjs fails when they no longer match (stale computed data).' }, outputs: Object.keys(out), inputs: Object.fromEntries(INPUTS.map((f) => [f, hash(f)])) }, null, 2) + '\n',
);
console.log('wrote data/computed/_manifest.json');
