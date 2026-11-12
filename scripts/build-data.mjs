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
	const parts = Object.entries(p.fields).sort((a, b) => a[1].group - b[1].group).map(([, s]) => ({ yy: 'two-digit year', mm: 'two-digit month', ww: 'two-digit week', letterYear: 'year letter', letterMonth: 'month letter' })[s.type]);
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
