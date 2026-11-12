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
