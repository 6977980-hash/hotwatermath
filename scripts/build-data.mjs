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
import { estimateInstalled, defaultExtras, operatingCosts, gallonsFor } from '../assets/js/cost-model.js';
import { describe as describeCost } from '../assets/js/cost-calculator.js';
import { describe as describeTco } from '../assets/js/tankless-vs-tank.js';
import { describe as describeOp } from '../assets/js/gas-vs-electric.js';
import { describe as describeHp } from '../assets/js/heat-pump-savings.js';
import { describe as describeEt } from '../assets/js/expansion-tank.js';

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
	'data/installation-costs.json',
	'data/pages.json',
	'assets/js/cost-model.js',
	'assets/js/cost-calculator.js',
	'assets/js/tankless-vs-tank.js',
	'assets/js/gas-vs-electric.js',
	'assets/js/ui.js',
	'data/heat-pump.json',
	'data/expansion-tanks.json',
	'assets/js/heat-pump-savings.js',
	'assets/js/expansion-tank.js',
	'data/troubleshooting.json',
	'data/state-pages.json',
	'data/metro-price-parities.json',
	'data/safety.json',
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

/* ---------- Phase 4: shared cost model (cost calculator, tankless vs tank, gas vs electric, repair) ---------- */
// One object, copied into each tool's client data, so every page prices and runs the same household the same way.
const model = (() => {
	const ic = d['installation-costs'];
	const up = out['unit-prices'].units;
	const uef = out['uef-defaults'].types;
	const ls = d.lifespans;
	const hw = d['hot-water-use'];
	const ep = out['energy-prices'];
	const inlet = out['inlet-temps'].states;
	const rm = out['regional-multipliers'].states;
	const credits = d.incentives.currentFederalCreditUsd;
	const fuelOf = { gas_tank: 'gas', electric_tank: 'electric', heat_pump: 'electric', gas_tankless: 'gas', electric_tankless: 'electric' };
	const typeOrder = ['gas_tank', 'electric_tank', 'heat_pump', 'gas_tankless', 'electric_tankless'];
	const sizeLabel = (x) => (x.nominalGallons ? `${x.nominalGallons} gallons` : 'Whole house');
	const units = {};
	const sizes = Object.fromEntries(typeOrder.map((t) => [t, []]));
	for (const [key, x] of Object.entries(d['cost-ranges'].units)) {
		const id = x.nominalGallons ? String(x.nominalGallons) : 'whole';
		units[`${x.type}_${id}`] = { ...up[key], sourceKey: key };
		sizes[x.type].push({ id, label: sizeLabel(x) });
	}
	const defaultSize = { gas_tank: '50', electric_tank: '50', heat_pump: '50', gas_tankless: 'whole', electric_tankless: 'whole' };
	const types = Object.fromEntries(typeOrder.map((t) => [t, { label: uef[t].label, fuel: fuelOf[t], uef: uef[t].typical, life: ls.types[t].typical, credit: credits[t], sizes: sizes[t], defaultSize: defaultSize[t] }]));
	const strip = (o) => ({ low: o.low, typical: o.typical, high: o.high });
	const states = Object.fromEntries(Object.keys(rm).map((k) => [k, { name: rm[k].name, multiplier: rm[k].multiplier, usdPerKwh: ep.states[k].usdPerKwh, usdPerTherm: ep.states[k].usdPerTherm, inletF: inlet[k].annualAvg }]));
	return {
		units, types, typeOrder,
		labor: Object.fromEntries(typeOrder.map((t) => [t, strip(ic.labor[t])])),
		extras: Object.fromEntries(Object.entries(ic.extras).map(([k, x]) => [k, { label: x.label, ...strip(x) }])),
		scenarios: ic.scenarios,
		states,
		us: { name: 'U.S. average', multiplier: 1, usdPerKwh: ep.us.usdPerKwh, usdPerTherm: ep.us.usdPerTherm, inletF: hw.testConditions.inletF },
		setpointF: d.site.defaults.setpointTempF.value,
		typicalGallons: hw.typicalHouseholdGallonsPerDay.value,
		perPersonGallons: hw.perPersonGallonsPerDay.value,
		lastReviewed: ic.lastReviewed,
	};
})();
const stateOptions = [{ id: 'US', name: 'U.S. average' }, ...states.map(([k, s]) => ({ id: k, name: s.name }))];

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
		// Installed replacement price (Phase 4): same model as the cost calculator, like-for-like job, U.S. average.
		const src = d['cost-ranges'].units[priceKey];
		const est = estimateInstalled(model, { type: src.type, size: src.nominalGallons ? String(src.nominalGallons) : 'whole', state: 'US', extras: defaultExtras(model, 'replace', src.type) });
		return [t.id, {
			label: t.label, life: t.life, uefId: t.uefId, uef: uef[t.uefId].typical, fuel: dr.fuelOf[t.id],
			replacementUsd: est.typical, equipmentUsd: up[priceKey].typical, costType: src.type, costSize: src.nominalGallons ? String(src.nominalGallons) : 'whole',
			replacementLabel: up[priceKey].label, replacementRange: `${fmt(est.low)}–${fmt(est.high)}`,
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
		meta: { ...derived('data/decision-rules.json, data/lifespans.json, data/cost-ranges.json, data/uef-ranges.json, data/hot-water-use.json and data/energy-prices.json'), notes: 'Default result, repair limits by type, energy comparison and cross-check for the repair-or-replace tool. Replacement prices are installed prices (equipment + labor + permit and haul-away, U.S. average) from the shared cost model in assets/js/cost-model.js.' },
		defaultInputs: { ...di, replace: types[di.type].replacementUsd },
		default: null,
		defaultCase: { age: di.ageYears, repair: fmt(di.repairUsd), replace: fmt(types[di.type].replacementUsd), share: Math.round(ex1.share * 100), verdict: ex1.verdict, life: types[di.type].life, oldFrom: fmt(ex1.oldFromYears, 2) },
		rulesPct: { always: Math.round(rules.repairShareAlways * 100), age: Math.round(rules.ageShare * 100), old: Math.round(rules.repairShareWhenOld * 100) },
		examples: [
			{ age: 11, type: 'gas_tank', repair: 350 },
			{ age: 4, type: 'electric_tank', repair: 150 },
			{ age: 9, type: 'heat_pump', repair: 900 },
		].map((e) => { const x = ask(e.age, e.type, e.repair); return { ...e, label: types[e.type].label, life: types[e.type].life, replace: fmt(types[e.type].replacementUsd), repairFmt: fmt(e.repair), share: Math.round(x.share * 100), used: Math.round(x.lifeUsed * 100), verdict: x.verdict, oldFrom: fmt(x.oldFromYears, 2) }; }),
		energy,
		crossChecks: xc,
		tables: {
			limits: table(
				'Most to pay for a repair, by type (our rules applied to typical installed replacement prices)',
				[
					{ key: 'label', label: 'Type' },
					{ key: 'price', label: 'New unit, installed', unit: '$', num: true },
					{ key: 'young', label: 'Repair limit, younger unit', unit: '$', num: true },
					{ key: 'oldFrom', label: '"Old" from', unit: 'years', num: true },
					{ key: 'old', label: 'Repair limit, old unit', unit: '$', num: true },
				],
				limits,
				{ source: `Rules from data/decision-rules.json (${Math.round(rules.repairShareAlways * 100)}% / ${Math.round(rules.ageShare * 100)}% of life / ${Math.round(rules.repairShareWhenOld * 100)}%); typical life from DOE; installed prices = Home Depot equipment listings (${d['cost-ranges'].lastReviewed}) + our installation ranges (${d['installation-costs'].lastReviewed}), U.S. average`, sourceUrl: 'https://hotwatermath.com/methodology/#repair-or-replace-water-heater', sourceDate: d['cost-ranges'].lastReviewed },
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
		sources: `Rules: ours, stated on /methodology. Typical life: DOE (${types.gas_tank.life} years for tanks, ${types.tankless.life} for tankless). New-unit price: typical installed price, U.S. average (equipment from Home Depot listings, ${d['cost-ranges'].lastReviewed}, plus labor, permit and haul-away from our cost model); type your own quote if you have one. <a href="/methodology/#repair-or-replace-water-heater">See our rules checked against Rheem and A. O. Smith guidance</a>.`,
	};
}

/* ---------- Phase 4 tools: water heater cost calculator ---------- */
const ic = d['installation-costs'];
const refLink = (id) => ({ source: `${ic.references[id].publisher}: ${ic.references[id].title}`, sourceUrl: ic.references[id].sourceUrl });
const pct = (ours, theirs) => r(((ours - theirs) / theirs) * 100, 1);
{
	const df = ic.defaults;
	const like = (type, size, state = 'US') => estimateInstalled(model, { type, size, state, extras: defaultExtras(model, 'replace', type) });
	const byUnit = Object.entries(model.units).map(([key, u]) => {
		const size = key.slice(u.type.length + 1);
		const e = like(u.type, size);
		return { key, type: u.type, size, label: u.label, unit: fmt(u.typical), low: fmt(e.low), typical: fmt(e.typical), high: fmt(e.high), typicalNum: e.typical };
	});
	const g = (key) => byUnit.find((x) => x.key === key);
	const stateRows = states.map(([k, s]) => { const e = like('gas_tank', '50', k); return { code: k, name: s.name, m: fmt(model.states[k].multiplier, 2), typical: fmt(e.typical), typicalNum: e.typical, low: fmt(e.low), high: fmt(e.high) }; });
	const sortedStates = [...stateRows].sort((a, b) => a.typicalNum - b.typicalNum);
	const scenarioRows = [
		['Gas tank to gas tank (50 gal)', 'gas_tank', '50', 'replace'],
		['Electric tank to electric tank (50 gal)', 'electric_tank', '50', 'replace'],
		['Electric tank to heat pump (50 gal)', 'heat_pump', '50', 'replace'],
		['Gas tank to heat pump (50 gal)', 'heat_pump', '50', 'fuelSwitch'],
		['Gas tank to gas tankless', 'gas_tankless', 'whole', 'toTankless'],
		['Electric tank to electric tankless', 'electric_tankless', 'whole', 'toTankless'],
		['Electric tank to gas tank (50 gal)', 'gas_tank', '50', 'fuelSwitch'],
	].map(([label, type, size, sc]) => { const e = estimateInstalled(model, { type, size, state: 'US', extras: defaultExtras(model, sc, type) }); return { label, extras: defaultExtras(model, sc, type).map((x) => model.extras[x].label.split(' (')[0].split(',')[0]).join(', '), low: fmt(e.low), typical: fmt(e.typical), high: fmt(e.high), typicalNum: e.typical }; });
	// Cross-checks: our like-for-like national estimates vs DOE's average installed costs (and two industry averages).
	const xc = [
		{ id: 'doe-gas', label: '50-gal gas tank, like-for-like, U.S. average', ours: `$${g('gas_tank_50').typical}`, published: 'DOE average installed cost $1,432 (2022 dollars, baseline gas storage)', diffPct: pct(g('gas_tank_50').typicalNum, 1432), why: 'Ours is in today’s listing prices; DOE’s is 2022 dollars and averages all sizes from 20 to 55 gallons. PHCC told DOE its RSMeans-based installation costs are too low.', ...refLink('doe-storage-2024') },
		{ id: 'doe-electric', label: '50-gal electric tank, like-for-like, U.S. average', ours: `$${g('electric_tank_50').typical}`, published: 'DOE average installed cost $902 (2022 dollars, baseline electric storage)', diffPct: pct(g('electric_tank_50').typicalNum, 902), why: 'The largest gap. DOE’s figure is 2022 dollars, includes smaller tanks and many new-construction installs; current 50-gallon listings alone are near $820.', ...refLink('doe-storage-2024') },
		{ id: 'doe-hp', label: '50-gal heat pump, replacing an electric tank, U.S. average', ours: `$${g('heat_pump_50').typical}`, published: 'DOE average installed cost $1,855 (2022 dollars, heat pump level)', diffPct: pct(g('heat_pump_50').typicalNum, 1855), why: 'DOE priced heat pump units from its own cost model for the 2030 market; today’s retail listings are higher. Space work for tight closets is an extra in both.', ...refLink('doe-storage-2024') },
		{ id: 'doe-tankless', label: 'Gas tankless replacing a gas tankless, U.S. average', ours: `$${g('gas_tankless_whole').typical}`, published: 'DOE average installed cost $2,087 (2023 dollars, baseline gas tankless)', diffPct: pct(g('gas_tankless_whole').typicalNum, 2087), why: 'Close. Our equipment median is a whole-house unit (8 GPM or more); our labor plus permit ($1,110 typical) matches DOE’s $1,102 installation average.', ...refLink('doe-gas-tankless-2024') },
		{ id: 'hd', label: 'Typical water heater installation, all types', ours: `$${g('gas_tank_50').typical} (50-gal gas) to $${g('heat_pump_50').typical} (heat pump)`, published: 'Home Depot data (via This Old House): $1,950 average, $1,600-$5,400 range', match: true, why: 'Industry average, used only as a sanity check: it sits inside our range of common jobs.', ...refLink('industry-this-old-house') },
	];
	const client = { model, defaults: { type: df.type, size: df.size, state: df.state, scenario: df.scenario } };
	const v0 = { type: df.type, size: df.size, state: df.state, scenario: df.scenario, ...Object.fromEntries(defaultExtras(model, df.scenario, df.type).map((x) => [x, true])) };
	const dd = describeCost(v0, client);
	const mult = Object.values(model.states).map((x) => x.multiplier);
	out['water-heater-cost-calculator'] = {
		meta: { ...derived('data/cost-ranges.json, data/installation-costs.json and data/regional-cost-index.json'), notes: 'Default result, installed cost tables, state table and cross-checks for the cost calculator.' },
		defaultInputs: v0,
		default: { status: dd.status, statusLabel: dd.statusLabel, headline: dd.headline, explain: dd.explain, formula: dd.formula, table: dd.table, sources: `Unit: Home Depot listings (${d['cost-ranges'].lastReviewed}). Labor and extras: our ranges anchored to DOE's 2024 water heater rules (${ic.lastReviewed}). State factor: BEA price parities. <a href="/methodology/#water-heater-cost-calculator">See our numbers checked against DOE's installed costs</a>.` },
		figures: {
			gas40: g('gas_tank_40'), gas50: g('gas_tank_50'), gas75: g('gas_tank_75'), el40: g('electric_tank_40'), el50: g('electric_tank_50'), hp50: g('heat_pump_50'), gasTankless: g('gas_tankless_whole'), elTankless: g('electric_tankless_whole'),
			laborGas: model.labor.gas_tank, laborElectric: model.labor.electric_tank, laborTankless: model.labor.gas_tankless, permit: model.extras.permit,
			cheapState: sortedStates[0], dearState: sortedStates[sortedStates.length - 1], multLow: fmt(Math.min(...mult), 2), multHigh: fmt(Math.max(...mult), 2),
			scenarios: scenarioRows, lastReviewed: ic.lastReviewed,
		},
		stateOptions,
		crossChecks: xc,
		tables: {
			byUnit: table('Installed cost by type and size, like-for-like replacement, U.S. average', [{ key: 'label', label: 'Water heater' }, { key: 'unit', label: 'Unit only', unit: '$', num: true }, { key: 'typical', label: 'Installed, typical', unit: '$', num: true }, { key: 'range', label: 'Installed range', unit: '$', num: true }], byUnit.map((x) => ({ label: x.label, unit: x.unit, typical: x.typical, range: `${x.low}–${x.high}` })), { source: `Equipment: Home Depot listings (${d['cost-ranges'].lastReviewed}); labor, permit and haul-away: data/installation-costs.json (${ic.lastReviewed})`, sourceUrl: 'https://hotwatermath.com/methodology/#water-heater-cost-calculator', sourceDate: ic.lastReviewed }),
			extras: table('Extras and what they usually cost (U.S. average, before your state factor)', [{ key: 'label', label: 'Extra' }, { key: 'typical', label: 'Typical', unit: '$', num: true }, { key: 'range', label: 'Range', unit: '$', num: true }, { key: 'why', label: 'Where the number comes from' }], Object.values(ic.extras).map((x) => ({ label: x.label, typical: fmt(x.typical), range: `${fmt(x.low)}–${fmt(x.high)}`, why: x.why })), { source: ic.meta.source, sourceUrl: ic.meta.sourceUrl, sourceDate: ic.meta.sourceDate }),
			labor: table('Installation labor by type, like-for-like (U.S. average)', [{ key: 'label', label: 'Type' }, { key: 'typical', label: 'Typical', unit: '$', num: true }, { key: 'range', label: 'Range', unit: '$', num: true }, { key: 'why', label: 'Why' }], model.typeOrder.map((t) => ({ label: model.types[t].label, typical: fmt(ic.labor[t].typical), range: `${fmt(ic.labor[t].low)}–${fmt(ic.labor[t].high)}`, why: ic.labor[t].why })), { source: ic.meta.source, sourceUrl: ic.meta.sourceUrl, sourceDate: ic.meta.sourceDate }),
			scenarios: table('Common jobs and their installed cost (U.S. average)', [{ key: 'label', label: 'Job' }, { key: 'extras', label: 'Extras included' }, { key: 'typical', label: 'Typical', unit: '$', num: true }, { key: 'range', label: 'Range', unit: '$', num: true }], scenarioRows.map((x) => ({ label: x.label, extras: x.extras, typical: x.typical, range: `${x.low}–${x.high}` })), { source: 'Computed with the cost calculator’s own model (equipment listings + data/installation-costs.json)', sourceUrl: 'https://hotwatermath.com/methodology/#water-heater-cost-calculator', sourceDate: ic.lastReviewed }),
			byState: table('50-gallon gas tank, like-for-like, installed cost by state', [{ key: 'name', label: 'State' }, { key: 'm', label: 'Price factor', num: true }, { key: 'typical', label: 'Typical', unit: '$', num: true }, { key: 'range', label: 'Range', unit: '$', num: true }], stateRows.map((x) => ({ name: x.name, m: x.m, typical: x.typical, range: `${x.low}–${x.high}` })), { source: 'Equipment listings + data/installation-costs.json, labor and extras × BEA Regional Price Parity ÷ 100', sourceUrl: d['regional-cost-index'].meta.sourceUrl, sourceDate: d['regional-cost-index'].meta.sourceDate }),
		},
		client,
	};
}

/* ---------- Phase 4 tools: gas vs electric operating cost ---------- */
{
	const client = { model, defaults: { household: 'typical', state: 'US' } };
	const run = (state, household = 'typical') => describeOp({ household, state }, client);
	const d0 = run('US');
	const op0 = Object.fromEntries(d0.rows.map((x) => [x.id, x]));
	const stateRows = states.map(([k, s]) => { const o = Object.fromEntries(run(k).rows.map((x) => [x.id, x])); return { name: s.name, gas: o.gas_tank.year, el: o.electric_tank.year, hp: o.heat_pump.year }; });
	const gasWins = stateRows.filter((x) => x.gas < x.el).length;
	const hpVsGas = stateRows.filter((x) => x.hp < x.gas).length;
	const people = [1, 2, 3, 4, 5, 6].map((n) => { const o = Object.fromEntries(run('US', n).rows.map((x) => [x.id, x])); return { people: n, gallons: gallonsFor(model, n), gas: fmt(o.gas_tank.year), el: fmt(o.electric_tank.year), hp: fmt(o.heat_pump.year), gtl: fmt(o.gas_tankless.year), etl: fmt(o.electric_tankless.year) }; });
	// Cross-checks: our formula with each label's own UEF, draw pattern and FTC price vs the federal EnergyGuide label / ENERGY STAR / FEMP figure.
	const hw = d['hot-water-use'];
	const pat = Object.fromEntries(hw.drawPatterns.map((p) => [p.id, p.gallonsPerDay]));
	const rise = hw.testConditions.riseF;
	const kwh = (gal, u) => F.annualEnergyUse(F.annualHeatBtu(gal, rise), 'electric', u).kwh;
	const th = (gal, u) => F.annualEnergyUse(F.annualHeatBtu(gal, rise), 'gas', u).therms;
	const xc = [
		{ id: 'eg-electric', label: 'Rheem XE50T06ST45U1 electric tank: UEF 0.93, medium draw (55 gal/day)', ours: `${fmt(kwh(pat.medium, 0.93))} kWh/yr; $${fmt(r(kwh(pat.medium, 0.93) * 0.14))} at 14.00 ¢/kWh`, published: 'EnergyGuide label: 3,493 kWh/yr, $489', diffPct: pct(kwh(pat.medium, 0.93), 3493), why: 'Within 1.5%. The label uses the model’s unrounded test result; we use the rounded UEF.', source: 'Rheem EnergyGuide label (via Home Depot listing)', sourceUrl: 'https://images.thdstatic.com/catalog/pdfImages/22/22032cf2-1800-482d-b21c-8526c5c35cdd.pdf' },
		{ id: 'eg-gas', label: 'Rheem XG50T06EC38U1 gas tank: UEF 0.63, high draw (84 gal/day)', ours: `${fmt(th(pat.high, 0.63))} therms/yr; $${fmt(r(th(pat.high, 0.63) * 1.21))} at $1.21/therm`, published: 'EnergyGuide label: 269 therms/yr, $325', diffPct: pct(th(pat.high, 0.63), 269), why: 'Within 1.5%, same reason (rounded UEF).', source: 'Rheem EnergyGuide label (via Home Depot listing)', sourceUrl: 'https://images.thdstatic.com/catalog/pdfImages/5d/5df0a059-bf89-46bb-8edd-faacf8b52e0a.pdf' },
		{ id: 'eg-hp', label: 'Rheem XE50T10HS45U1 heat pump: UEF 3.88 (ENERGY STAR), medium draw', ours: `${fmt(kwh(pat.medium, 3.88))} kWh/yr`, published: 'EnergyGuide label: 837 kWh/yr ($117); ENERGY STAR list: 846 kWh/yr', diffPct: pct(kwh(pat.medium, 3.88), 846), why: 'Matches the ENERGY STAR figure; the label’s 837 kWh is 1.2% lower than both.', source: 'ENERGY STAR certified water heaters', sourceUrl: 'https://images.thdstatic.com/catalog/pdfImages/a8/a86691b3-d01e-4e2a-b392-8daf1711eddb.pdf' },
		{ id: 'es-tankless', label: 'American GT-240-NEH gas tankless: UEF 0.95, high draw', ours: `${fmt(th(pat.high, 0.95))} therms/yr`, published: 'ENERGY STAR list: 178 therms/yr', diffPct: pct(th(pat.high, 0.95), 178), why: 'Within 1.5%.', source: 'ENERGY STAR certified water heaters dataset', sourceUrl: d['energystar-water-heaters'].meta.sourceUrl },
		{ id: 'femp', label: 'Electric tank UEF 0.95, 20,075 gal/yr (DOE FEMP)', ours: `${fmt(kwh(hw.typicalHouseholdGallonsPerDay.value, hw.femp.electricUef))} kWh/yr`, published: `DOE FEMP: ${fmt(hw.femp.annualKwh)} kWh/yr`, diffPct: pct(kwh(hw.typicalHouseholdGallonsPerDay.value, hw.femp.electricUef), hw.femp.annualKwh), why: 'Within 1%.', source: 'DOE FEMP', sourceUrl: hw.femp.sourceUrl },
	];
	const elKw = F.summarize(d['fhr-to-tank-size'].models.electric.map((x) => x.elementKw));
	out['gas-vs-electric-water-heater'] = {
		meta: { ...derived('data/energy-prices.json, data/groundwater-temp-by-state.json, data/uef-ranges.json and data/hot-water-use.json'), notes: 'Default result, per-state and per-household tables and EnergyGuide cross-checks for the gas vs electric tool.' },
		default: { status: d0.status, statusLabel: d0.statusLabel, headline: d0.headline, explain: d0.explain, formula: d0.formula, table: d0.table, sources: `Prices: EIA, ${d['energy-prices'].meta.sourceDate.slice(0, 4)} data. Efficiency: median UEF of real models (Rheem spec sheets, ENERGY STAR). Use: DOE FEMP typical day. <a href="/methodology/#gas-vs-electric-water-heater">See our formula checked against federal EnergyGuide labels</a>.` },
		figures: {
			us: Object.fromEntries(Object.entries(op0).map(([k, x]) => [k, { year: fmt(x.year), month: fmt(x.month), kwh: x.use.kwh !== undefined ? fmt(x.use.kwh) : '', kwhMonth: x.use.kwh !== undefined ? fmt(x.use.kwh / 12) : '', therms: x.use.therms !== undefined ? fmt(x.use.therms) : '', thermsMonth: x.use.therms !== undefined ? fmt(x.use.therms / 12, 1) : '', uef: fmt(x.uef, 2) }])),
			gasWins, hpVsGas, stateCount: stateRows.length,
			tanklessKwLow: fmt(Math.min(...d.electrical.referenceUnits.map((u) => u.kw))), tanklessKwHigh: fmt(Math.max(...d.electrical.referenceUnits.map((u) => u.kw))),
			kwhPriceLow: fmt(Math.min(...Object.values(out['energy-prices'].states).map((x) => x.usdPerKwh)), 3), kwhPriceHigh: fmt(Math.max(...Object.values(out['energy-prices'].states).map((x) => x.usdPerKwh)), 3),
			hpShare: Math.round((op0.heat_pump.use.kwh / op0.electric_tank.use.kwh) * 100),
			elementKw: fmt(elKw.typical, 1), elementKwLow: fmt(elKw.low, 1), elementKwHigh: fmt(elKw.high, 1),
			gallons: model.typicalGallons, rise: fmt(model.setpointF - model.us.inletF), setpoint: model.setpointF, inlet: model.us.inletF,
			kwhPrice: model.us.usdPerKwh, thermPrice: model.us.usdPerTherm,
		},
		stateOptions,
		crossChecks: xc,
		labelMaxDiffPct: fmt(Math.max(...xc.map((x) => Math.abs(x.diffPct))), 1),
		tables: {
			people: table('Yearly running cost by household size (U.S. average prices)', [{ key: 'people', label: 'People', num: true }, { key: 'gallons', label: 'Gal/day', num: true }, { key: 'gas', label: 'Gas tank', unit: '$', num: true }, { key: 'el', label: 'Electric tank', unit: '$', num: true }, { key: 'hp', label: 'Heat pump', unit: '$', num: true }, { key: 'gtl', label: 'Gas tankless', unit: '$', num: true }, { key: 'etl', label: 'Electric tankless', unit: '$', num: true }], people, { source: `Computed: ${hw.perPersonGallonsPerDay.value} gal per person a day (DOE Buildings Energy Data Book), ${model.setpointF} °F setpoint, ${model.us.inletF} °F incoming water (DOE test), median UEFs, EIA prices`, sourceUrl: 'https://hotwatermath.com/methodology/#gas-vs-electric-water-heater', sourceDate: d['energy-prices'].meta.sourceDate }),
			byState: table('Yearly running cost by state: gas tank vs electric tank vs heat pump (typical household)', [{ key: 'name', label: 'State' }, { key: 'gas', label: 'Gas tank', unit: '$/yr', num: true }, { key: 'el', label: 'Electric tank', unit: '$/yr', num: true }, { key: 'hp', label: 'Heat pump', unit: '$/yr', num: true }, { key: 'win', label: 'Cheaper tank' }], stateRows.map((x) => ({ name: x.name, gas: fmt(x.gas), el: fmt(x.el), hp: fmt(x.hp), win: x.gas < x.el ? 'Gas' : 'Electric' })), { source: `Computed: ${model.typicalGallons} gal/day, ${model.setpointF} °F setpoint, each state’s average incoming water temperature (NOAA-based estimate), EIA state prices, median UEFs`, sourceUrl: d['energy-prices'].meta.sourceUrl, sourceDate: d['energy-prices'].meta.sourceDate }),
		},
		client,
	};
}

/* ---------- Phase 4 tools: tankless vs tank cost of ownership ---------- */
{
	const options = {
		gas: [{ type: 'gas_tank', size: '50', scenario: 'replace', label: 'Gas tank (50 gal)' }, { type: 'gas_tankless', size: 'whole', scenario: 'toTankless', label: 'Gas tankless' }, { type: 'heat_pump', size: '50', scenario: 'fuelSwitch', label: 'Heat pump (50 gal)' }],
		electric: [{ type: 'electric_tank', size: '50', scenario: 'replace', label: 'Electric tank (50 gal)' }, { type: 'electric_tankless', size: 'whole', scenario: 'toTankless', label: 'Electric tankless' }, { type: 'heat_pump', size: '50', scenario: 'replace', label: 'Heat pump (50 gal)' }],
	};
	const years = d['decision-rules'].ownershipYearsDefault;
	const client = { model, options, defaults: { fuel: 'gas', household: 'typical', state: 'US', years } };
	const run = (fuel, state = 'US', household = 'typical', y = years) => describeTco({ fuel, state, household, years: y }, client);
	const g0 = run('gas');
	const e0 = run('electric');
	const pick = (res) => Object.fromEntries(res.result.map((o) => [o.id, { installed: fmt(o.installed), year: fmt(o.annualOperating), total: fmt(o.total), totalNum: o.total, be: o.breakEven || 0, repl: o.replacements, life: o.lifeYears }]));
	const g = pick(g0);
	const e = pick(e0);
	const g20 = pick(run('gas', 'US', 'typical', 20));
	const stateRows = states.map(([k, s]) => { const x = pick(run('gas', k)); const y = pick(run('electric', k)); return { name: s.name, gt: x.gas_tankless.be ? `Year ${x.gas_tankless.be}` : 'No', hpg: x.heat_pump.be ? `Year ${x.heat_pump.be}` : 'No', hpe: y.heat_pump.be ? `Year ${y.heat_pump.be}` : 'No', gtNum: x.gas_tankless.be }; });
	const people = [2, 4, 6].map((n) => { const x = pick(run('gas', 'US', n)); return { people: n, tank: x.gas_tank.total, tankless: x.gas_tankless.total, be: x.gas_tankless.be ? `Year ${x.gas_tankless.be}` : `Not in ${years} years` }; });
	// Cross-checks against DOE's 2024 rules (first-year operating cost and heat pump payback, national averages).
	const op = operatingCosts(model, { gallonsPerDay: model.typicalGallons, setpointF: model.setpointF, inletF: model.us.inletF, prices: { usdPerKwh: model.us.usdPerKwh, usdPerTherm: model.us.usdPerTherm } });
	const opBy = Object.fromEntries(op.rows.map((x) => [x.id, x]));
	const hpInst = estimateInstalled(model, { type: 'heat_pump', size: '50', state: 'US', extras: defaultExtras(model, 'replace', 'heat_pump') }).typical;
	const elInst = estimateInstalled(model, { type: 'electric_tank', size: '50', state: 'US', extras: defaultExtras(model, 'replace', 'electric_tank') }).typical;
	const payback = r((hpInst - elInst) / (opBy.electric_tank.year - opBy.heat_pump.year), 1);
	const xc = [
		{ id: 'doe-hp-payback', label: 'Heat pump instead of an electric tank: simple payback', ours: `${fmt(payback, 1)} years (extra ${fmt(hpInst - elInst)} ÷ ${fmt(opBy.electric_tank.year - opBy.heat_pump.year)} saved a year)`, published: 'DOE: 5.6 years (Table V.8, 2022 dollars)', diffPct: pct(payback, 5.6), why: 'Same order. DOE used 2022 prices and its own equipment cost model; our heat pump listings carry a larger price gap today.', ...refLink('doe-storage-2024') },
		{ id: 'doe-el-op', label: 'Electric tank, first-year running cost', ours: `$${fmt(opBy.electric_tank.year)}`, published: 'DOE: $340 (2022 dollars, field use)', diffPct: pct(opBy.electric_tank.year, 340), why: 'DOE models each household’s real use and 2022 prices; we use a typical 55-gallon day and 2025 EIA prices. Electricity cost more in 2025.', ...refLink('doe-storage-2024') },
		{ id: 'doe-hp-op', label: 'Heat pump, first-year running cost', ours: `$${fmt(opBy.heat_pump.year)}`, published: 'DOE: $171 (2022 dollars)', diffPct: pct(opBy.heat_pump.year, 171), why: 'Our median UEF comes from today’s ENERGY STAR list, which is higher than DOE’s first heat pump level.', ...refLink('doe-storage-2024') },
		{ id: 'doe-gt-op', label: 'Gas tankless, first-year running cost', ours: `$${fmt(opBy.gas_tankless.year)}`, published: 'DOE: $303 (2023 dollars, baseline gas tankless)', diffPct: pct(opBy.gas_tankless.year, 303), why: 'DOE’s households with tankless units use more hot water than a typical day, and its figure includes electricity for the burner fan and controls, which UEF-based math leaves out.', ...refLink('doe-gas-tankless-2024') },
	];
	out['tankless-vs-tank-water-heater'] = {
		meta: { ...derived('the cost calculator and gas vs electric models plus data/lifespans.json and data/incentives.json'), notes: 'Default result, ownership tables, state break-even table and DOE cross-checks for the tankless vs tank tool.' },
		default: { status: g0.status, statusLabel: g0.statusLabel, headline: g0.headline, explain: g0.explain, formula: g0.formula, table: g0.table, sources: `Installed costs: same model as our cost calculator (${ic.lastReviewed}). Running costs: same formula as our gas vs electric tool, EIA prices. Lifespans: DOE. Federal credit: $0 (IRS, 25C ended 2025). <a href="/methodology/#tankless-vs-tank-water-heater">See our results checked against DOE</a>.` },
		figures: { gas: g, electric: e, gas20: g20, years, years20: 20, gasTanklessSaving: fmt(g0.result[0].annualOperating - g0.result[1].annualOperating), gas20Tank: g20.gas_tank, gasTanklessStatesPay: stateRows.filter((x) => x.gtNum).length, stateCount: stateRows.length, people, perPerson: model.perPersonGallons, lifeTank: model.types.gas_tank.life, lifeTankless: model.types.gas_tankless.life },
		stateOptions,
		crossChecks: xc,
		tables: {
			byState: table(`When the extra cost pays back, by state (typical household, ${years} years)`, [{ key: 'name', label: 'State' }, { key: 'gt', label: 'Gas tankless vs gas tank' }, { key: 'hpg', label: 'Heat pump vs gas tank' }, { key: 'hpe', label: 'Heat pump vs electric tank' }], stateRows.map(({ gtNum, ...x }) => x), { source: 'Computed with the tool’s own model: installed costs (listings + data/installation-costs.json × state factor), EIA state prices, state incoming water temperature, DOE lifespans', sourceUrl: 'https://hotwatermath.com/methodology/#tankless-vs-tank-water-heater', sourceDate: ic.lastReviewed }),
		},
		client,
	};
}

/* ---------- Phase 4: Quick Answers in pages.json must quote the computed numbers (CLAUDE.md §5) ---------- */
{
	const pages = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8')).pages;
	const qa = (slug) => pages.find((p) => p.slug === slug)?.quickAnswer || '';
	const cf = out['water-heater-cost-calculator'].figures;
	const of = out['gas-vs-electric-water-heater'].figures.us;
	const tf = out['tankless-vs-tank-water-heater'].figures;
	const usd = (v) => '$' + v;
	const must = [
		['water-heater-cost-calculator', [usd(cf.gas50.typical), usd(cf.el50.typical)]],
		['gas-vs-electric-water-heater', [usd(of.gas_tank.year), usd(of.electric_tank.year), usd(of.heat_pump.year)]],
		['tankless-vs-tank-water-heater', [tf.years + ' years', tf.electric.heat_pump.be + ' years']],
	];
	for (const [slug, parts] of must) for (const part of parts) if (!qa(slug).includes(part)) throw new Error('pages.json quickAnswer for ' + slug + ' must contain "' + part + '" (computed); update it.');
	if (tf.gas.gas_tankless.be) throw new Error('tankless-vs-tank quickAnswer says gas tankless does not pay back in the default period; computed says it does.');
}

/* ---------- Phase 6: expansion articles (heat pump cost, installation costs, 40 vs 50, expansion tank) ---------- */
// Every article reuses the Phase 4 cost model, so a number on an article matches the calculators to the dollar.
const usdN = (v) => fmt(Math.round(v));
const job = (type, size, scenario, state = 'US', extra = []) => estimateInstalled(model, { type, size, state, extras: [...defaultExtras(model, scenario, type), ...extra] });
const jobFig = (e) => ({ low: fmt(e.low), typical: fmt(e.typical), high: fmt(e.high), typicalNum: e.typical, unit: fmt(e.unit.typical), install: fmt(e.typical - e.unit.typical), unitShare: Math.round((e.unit.typical / e.typical) * 100) });
const costSrc = { source: `Equipment: Home Depot listings (${d['cost-ranges'].lastReviewed}); labor, permit and extras: data/installation-costs.json (${ic.lastReviewed}), anchored to DOE's 2024 water heater rules`, sourceUrl: 'https://hotwatermath.com/methodology/#water-heater-cost-calculator', sourceDate: ic.lastReviewed };
const itemRows = (e) => e.items.map((i) => ({ item: i.label, low: fmt(i.low), typical: fmt(i.typical), high: fmt(i.high) }));
const itemCols = [{ key: 'item', label: 'Item' }, { key: 'low', label: 'Low', unit: '$', num: true }, { key: 'typical', label: 'Typical', unit: '$', num: true }, { key: 'high', label: 'High', unit: '$', num: true }];
const itemTable = (caption, e) => ({ ...table(caption, itemCols, [...itemRows(e), { item: 'Total', low: fmt(e.low), typical: fmt(e.typical), high: fmt(e.high) }], costSrc) });
const costClient = out['water-heater-cost-calculator'].client;
/** Server-rendered default of the cost calculator for a preset (articles embed the calculator preset to their type). */
const costPreset = (type, size, scenario) => {
	const v = { type, size, state: 'US', scenario, ...Object.fromEntries(defaultExtras(model, scenario, type).map((x) => [x, true])) };
	const dd = describeCost(v, costClient);
	return { inputs: v, default: { status: dd.status, statusLabel: dd.statusLabel, headline: dd.headline, explain: dd.explain, formula: dd.formula, table: dd.table, sources: out['water-heater-cost-calculator'].default.sources } };
};
const stateTable = (caption, cols, fn, src) => table(caption, [{ key: 'name', label: 'State' }, ...cols], states.map(([k, s]) => ({ name: s.name, ...fn(k) })), src);

/* heat pump water heater cost + savings calculator (STRATEGY §3.8) */
{
	const hp = d['heat-pump'];
	const client = { model, defaults: { current: 'electric_tank', size: '50', household: 'typical', state: 'US' }, paybackGoodYears: hp.savingsCalculator.paybackGoodYears };
	const run = (o = {}) => describeHp({ current: 'electric_tank', size: '50', household: 'typical', state: 'US', ...o }, client);
	const e0 = run();
	const g0 = run({ current: 'gas_tank' });
	const e4 = run({ household: hp.energyStar.householdPeople });
	const eSpace = run({ space: true });
	const gSpace = run({ current: 'gas_tank', space: true });
	const eReb = run({ rebate: hp.rebates.heehrMaxUsd });
	const gReb = run({ current: 'gas_tank', rebate: hp.rebates.heehrMaxUsd });
	const res = (x) => ({ saving: fmt(x.result.saving), extra: fmt(x.result.extra), payback: x.result.payback === null ? 'never' : fmt(x.result.payback, 1), paybackNum: x.result.payback, net: fmt(x.result.net), netNum: x.result.net, hpInstalled: fmt(x.result.hpInstalled), altInstalled: fmt(x.result.altInstalled), hpYear: fmt(x.result.hpYear), curYear: fmt(x.result.curYear) });
	const sizes = model.types.heat_pump.sizes.map((s) => {
		const fromEl = job('heat_pump', s.id, 'replace');
		const fromGas = job('heat_pump', s.id, 'fuelSwitch');
		const fhr = out['fhr-table'].rows.find((r) => r.fuel === 'heatpump' && r.nominalGallons === Number(s.id));
		return { size: s.id, unit: fmt(fromEl.unit.typical), unitRange: `${fmt(fromEl.unit.low)}–${fmt(fromEl.unit.high)}`, fromEl: fmt(fromEl.typical), fromElRange: `${fmt(fromEl.low)}–${fmt(fromEl.high)}`, fromGas: fmt(fromGas.typical), fromGasRange: `${fmt(fromGas.low)}–${fmt(fromGas.high)}`, fromElNum: fromEl.typical, fhr: fhr ? fmt(fhr.fhrTypical) : '' };
	});
	const stateRows = states.map(([k, s]) => {
		const e = run({ state: k }).result;
		const g = run({ state: k, current: 'gas_tank' }).result;
		return { code: k, name: s.name, eSave: e.saving, ePay: e.payback, gSave: g.saving, gPay: g.payback };
	});
	const payTxt = (p) => (p === null ? 'Never' : p === 0 ? 'At once' : `${fmt(p, 1)} yrs`);
	const life = model.types.heat_pump.life;
	const elFast = stateRows.filter((x) => x.ePay !== null && x.ePay <= client.paybackGoodYears).length;
	const gasInLife = stateRows.filter((x) => x.gPay !== null && x.gPay <= life).length;
	const gasNever = stateRows.filter((x) => x.gPay === null).length;
	const sortedE = [...stateRows].sort((a, b) => b.eSave - a.eSave);
	const sortedG = [...stateRows].sort((a, b) => b.gSave - a.gSave);
	const ep = e0.result;
	const elUse = operatingCosts(model, { gallonsPerDay: model.typicalGallons, setpointF: model.setpointF, inletF: model.us.inletF, prices: { usdPerKwh: model.us.usdPerKwh, usdPerTherm: model.us.usdPerTherm } }).rows;
	const elKwh = elUse.find((x) => x.id === 'electric_tank').use.kwh;
	const hpKwh = elUse.find((x) => x.id === 'heat_pump').use.kwh;
	const lessPct = Math.round((1 - hpKwh / elKwh) * 100);
	const hpRef = (id) => ({ source: `${hp.references[id].publisher}: ${hp.references[id].title}`, sourceUrl: hp.references[id].sourceUrl });
	const xc = [
		{ id: 'es-saving', label: `Heat pump instead of an electric tank, household of ${hp.energyStar.householdPeople} (${gallonsFor(model, hp.energyStar.householdPeople)} gal/day), U.S. average price`, ours: `$${fmt(e4.result.saving)} a year`, published: `ENERGY STAR: about $${fmt(hp.energyStar.savingUsdPerYear)} a year for a household of four`, diffPct: pct(e4.result.saving, hp.energyStar.savingUsdPerYear), why: 'ENERGY STAR does not publish its household’s daily use or electricity price. We use DOE’s per-person use and the 2025 EIA average price; a household that uses more hot water saves more.', ...hpRef('es-guide') },
		{ id: 'es-energy', label: 'Energy use of a heat pump vs a standard electric tank (median UEF 3.8 vs 0.93)', ours: `${lessPct}% less energy`, published: `ENERGY STAR: ${hp.energyStar.lessEnergyPct}% less energy`, diffPct: pct(lessPct, hp.energyStar.lessEnergyPct), why: 'Our UEF is the median of today’s ENERGY STAR list; ENERGY STAR’s round figure covers older and smaller certified models too.', ...hpRef('es-guide') },
		{ id: 'doe-payback', label: 'Simple payback, heat pump instead of a 50-gal electric tank, U.S. average', ours: `${fmt(ep.payback, 1)} years`, published: 'DOE: 5.6 years (Table V.8, 2022 dollars)', diffPct: pct(ep.payback, 5.6), why: 'DOE used 2022 electricity prices and its own equipment cost model, and it adds space-work costs to some homes. Electricity cost more in 2025, which shortens payback; tick “space work” to see the slow case.', ...refLink('doe-storage-2024') },
	];
	const inc = d.incentives;
	out['heat-pump-water-heater-cost'] = {
		meta: { ...derived('the cost calculator and gas vs electric models plus data/heat-pump.json'), notes: 'Default result, size, state and incentive tables, and ENERGY STAR / DOE cross-checks for the heat pump water heater cost article and its savings calculator.' },
		default: { status: e0.status, statusLabel: e0.statusLabel, headline: e0.headline, explain: e0.explain, formula: e0.formula, table: e0.table, sources: `Installed costs: same model as our cost calculator (${ic.lastReviewed}). Running costs: same formula as our gas vs electric tool, EIA prices. Federal credit: $0 (IRS, 25C ended 2025). <a href="/methodology/#heat-pump-water-heater-cost">See our savings checked against ENERGY STAR and DOE</a>.` },
		figures: {
			e0: res(e0), g0: res(g0), e4: res(e4), eSpace: res(eSpace), gSpace: res(gSpace), eReb: res(eReb), gReb: res(gReb),
			sizes: Object.fromEntries(sizes.map((x) => [x.size, x])), life, paybackGood: client.paybackGoodYears, elFast, gasInLife, gasNever, stateCount: stateRows.length,
			bestE: { name: sortedE[0].name, saving: fmt(sortedE[0].eSave), payback: payTxt(sortedE[0].ePay) }, worstE: { name: sortedE[sortedE.length - 1].name, saving: fmt(sortedE[sortedE.length - 1].eSave), payback: payTxt(sortedE[sortedE.length - 1].ePay) },
			bestG: { name: sortedG[0].name, saving: fmt(sortedG[0].gSave), payback: payTxt(sortedG[0].gPay) },
			lessPct, elKwh: fmt(elKwh), hpKwh: fmt(hpKwh), uef: fmt(model.types.heat_pump.uef, 1), elUef: fmt(model.types.electric_tank.uef, 2), gallons: model.typicalGallons, kwhPrice: model.us.usdPerKwh, thermPrice: model.us.usdPerTherm,
			space: fmt(model.extras.heatPumpSpace.typical), spaceLow: fmt(model.extras.heatPumpSpace.low), spaceHigh: fmt(model.extras.heatPumpSpace.high), circuit: fmt(model.extras.circuit.typical), panelLow: fmt(model.extras.panel.low), panelHigh: fmt(model.extras.panel.high), labor: fmt(model.labor.heat_pump.typical), laborLow: fmt(model.labor.heat_pump.low), laborHigh: fmt(model.labor.heat_pump.high),
			creditEnded: inc.credits[0].placedInServiceThrough, creditPct: inc.credits[0].percent, creditMax: fmt(inc.credits[0].maxUsd), heehr: fmt(hp.rebates.heehrMaxUsd), heehrHousehold: fmt(hp.rebates.heehrHouseholdMaxUsd),
		},
		stateOptions,
		crossChecks: xc,
		tables: {
			bySize: table('Heat pump water heater cost by size (U.S. average)', [{ key: 'size', label: 'Size' }, { key: 'fhr', label: 'Typical FHR', unit: 'gal', num: true }, { key: 'unit', label: 'Unit only', unit: '$', num: true }, { key: 'fromEl', label: 'Installed, replacing electric', unit: '$', num: true }, { key: 'fromGas', label: 'Installed, replacing gas', unit: '$', num: true }], sizes.map((x) => ({ size: `${x.size} gallons`, fhr: x.fhr, unit: x.unit, fromEl: x.fromEl, fromGas: x.fromGas })), { ...costSrc, source: `${costSrc.source}. FHR: median of ENERGY STAR certified models` }),
			itemsEl: itemTable('Itemized: 50-gallon heat pump replacing an electric tank (U.S. average)', job('heat_pump', '50', 'replace')),
			itemsGas: itemTable('Itemized: 50-gallon heat pump replacing a gas tank (U.S. average)', job('heat_pump', '50', 'fuelSwitch')),
			byState: stateTable(`Heat pump savings and payback by state (typical household, 50 gallons, no rebate)`, [{ key: 'eSave', label: 'Saves vs electric', unit: '$/yr', num: true }, { key: 'ePay', label: 'Payback vs electric' }, { key: 'gSave', label: 'Saves vs gas', unit: '$/yr', num: true }, { key: 'gPay', label: 'Payback vs gas' }], (k) => { const x = stateRows.find((y) => y.code === k); return { eSave: fmt(x.eSave), ePay: payTxt(x.ePay), gSave: fmt(x.gSave), gPay: payTxt(x.gPay) }; }, { source: `Computed with the savings calculator: ${model.typicalGallons} gal/day, ${model.setpointF} °F setting, state incoming water temperature, EIA 2025 state prices, median UEFs, installed costs × BEA state price factor`, sourceUrl: 'https://hotwatermath.com/methodology/#heat-pump-water-heater-cost', sourceDate: ic.lastReviewed }),
			incentives: table('Heat pump water heater incentives (checked 2026-11-13)', [{ key: 'name', label: 'Program' }, { key: 'amount', label: 'Amount' }, { key: 'who', label: 'Who can get it' }, { key: 'status', label: 'Status now' }], [
				{ name: 'Federal tax credit (25C)', amount: `${inc.credits[0].percent}% of cost, up to $${fmt(inc.credits[0].maxUsd)} a year`, who: 'Any taxpayer, main home', status: `Ended: only for heaters placed in service by ${inc.credits[0].placedInServiceThrough}` },
				{ name: 'High-Efficiency Electric Home Rebate (HEEHR)', amount: `Up to $${fmt(hp.rebates.heehrMaxUsd)} for a heat pump water heater`, who: `Income below ${hp.rebates.heehrHalfCostIncomePct}% of area median (full cost below ${hp.rebates.heehrFullCostIncomePct}%)`, status: 'Open in select states only' },
				{ name: 'Utility and state rebates', amount: 'Varies by utility', who: 'Varies', status: 'Check the ENERGY STAR Rebate Finder by ZIP code' },
			], { source: 'IRS (25C); 42 U.S.C. 18795a and DOE Home Energy Rebates (HEEHR); ENERGY STAR Rebate Finder', sourceUrl: inc.meta.sourceUrl, sourceDate: '2026-11-13' }),
		},
		client,
	};
}

/* water heater replacement (installation) cost */
{
	const cf = out['water-heater-cost-calculator'].figures;
	const byType = ['gas_tank_40', 'gas_tank_50', 'gas_tank_75', 'electric_tank_40', 'electric_tank_50', 'heat_pump_50', 'gas_tankless_whole', 'electric_tankless_whole'].map((key) => {
		const u = model.units[key];
		const size = key.slice(u.type.length + 1);
		const e = job(u.type, size, 'replace');
		return { key, label: u.label, unit: fmt(e.unit.typical), install: fmt(e.typical - e.unit.typical), typical: fmt(e.typical), range: `${fmt(e.low)}–${fmt(e.high)}`, share: `${Math.round((e.unit.typical / e.typical) * 100)}%` };
	});
	const sr = states.map(([k, s]) => ({ code: k, name: s.name, m: model.states[k].multiplier, g: job('gas_tank', '50', 'replace', k).typical, e: job('electric_tank', '50', 'replace', k).typical, h: job('heat_pump', '50', 'replace', k).typical }));
	const sortedG = [...sr].sort((a, b) => a.g - b.g);
	const sortedE = [...sr].sort((a, b) => a.e - b.e);
	const g50 = job('gas_tank', '50', 'replace');
	const e50 = job('electric_tank', '50', 'replace');
	const newInstall = job('gas_tank', '50', 'replace', 'US', ['relocation', 'expansionTank', 'codeItems']);
	const fullCode = job('gas_tank', '50', 'replace', 'US', ['expansionTank', 'codeItems']);
	out['water-heater-installation-cost'] = {
		meta: { ...derived('the cost calculator model (data/cost-ranges.json, data/installation-costs.json, data/regional-cost-index.json)'), notes: 'Figures and tables for the water heater replacement (installation) cost article.' },
		figures: {
			g50: jobFig(g50), e50: jobFig(e50), g40: cf.gas40, e40: cf.el40, hp50: cf.hp50, gasTankless: cf.gasTankless, newInstall: jobFig(newInstall), fullCode: jobFig(fullCode),
			laborGas: { low: fmt(model.labor.gas_tank.low), typical: fmt(model.labor.gas_tank.typical), high: fmt(model.labor.gas_tank.high) }, laborEl: { low: fmt(model.labor.electric_tank.low), typical: fmt(model.labor.electric_tank.typical), high: fmt(model.labor.electric_tank.high) },
			permit: fmt(model.extras.permit.typical), expansion: fmt(model.extras.expansionTank.typical), codeItems: fmt(model.extras.codeItems.typical), relocation: fmt(model.extras.relocation.typical), relocationHigh: fmt(model.extras.relocation.high),
			cheapG: { name: sortedG[0].name, typical: fmt(sortedG[0].g) }, dearG: { name: sortedG[sortedG.length - 1].name, typical: fmt(sortedG[sortedG.length - 1].g) }, cheapE: { name: sortedE[0].name, typical: fmt(sortedE[0].e) }, dearE: { name: sortedE[sortedE.length - 1].name, typical: fmt(sortedE[sortedE.length - 1].e) },
			scenarios: cf.scenarios, lastReviewed: ic.lastReviewed, unitsReviewed: d['cost-ranges'].lastReviewed, stateCount: sr.length,
		},
		tables: {
			byType: table('Water heater replacement cost by type and size (like for like, U.S. average)', [{ key: 'label', label: 'Water heater' }, { key: 'unit', label: 'Unit', unit: '$', num: true }, { key: 'install', label: 'Labor, permit, haul-away', unit: '$', num: true }, { key: 'typical', label: 'Total, typical', unit: '$', num: true }, { key: 'range', label: 'Total range', unit: '$', num: true }, { key: 'share', label: 'Unit share', num: true }], byType.map(({ key, ...x }) => x), costSrc),
			byState: stateTable('Water heater replacement cost by state: 50-gallon tanks, like for like', [{ key: 'm', label: 'Price factor', num: true }, { key: 'g', label: 'Gas tank', unit: '$', num: true }, { key: 'e', label: 'Electric tank', unit: '$', num: true }, { key: 'h', label: 'Heat pump', unit: '$', num: true }], (k) => { const x = sr.find((y) => y.code === k); return { m: fmt(x.m, 2), g: fmt(x.g), e: fmt(x.e), h: fmt(x.h) }; }, { source: 'Equipment listings + data/installation-costs.json; labor, permit and extras × BEA Regional Price Parity ÷ 100', sourceUrl: d['regional-cost-index'].meta.sourceUrl, sourceDate: d['regional-cost-index'].meta.sourceDate }),
			replaceVsNew: table('Replacement vs new installation: what changes (50-gallon gas tank, U.S. average)', itemCols, [
				{ item: 'Replacement in the same spot', low: fmt(g50.low), typical: fmt(g50.typical), high: fmt(g50.high) },
				{ item: 'Replacement plus code items and an expansion tank', low: fmt(fullCode.low), typical: fmt(fullCode.typical), high: fmt(fullCode.high) },
				{ item: 'New location (relocation, code items, expansion tank)', low: fmt(newInstall.low), typical: fmt(newInstall.typical), high: fmt(newInstall.high) },
			], costSrc),
		},
	};
}

/* tankless water heater installation cost (embeds the cost calculator preset to gas tank → tankless) */
{
	const pre = costPreset('gas_tankless', 'whole', 'toTankless');
	const gT = job('gas_tankless', 'whole', 'toTankless');
	const gR = job('gas_tankless', 'whole', 'replace');
	const eT = job('electric_tankless', 'whole', 'toTankless');
	const eR = job('electric_tankless', 'whole', 'replace');
	const gTank = job('gas_tank', '50', 'replace');
	const eTank = job('electric_tank', '50', 'replace');
	const sr = states.map(([k, s]) => ({ code: k, name: s.name, gT: job('gas_tankless', 'whole', 'toTankless', k).typical, gR: job('gas_tankless', 'whole', 'replace', k).typical, eT: job('electric_tankless', 'whole', 'toTankless', k).typical }));
	const sorted = [...sr].sort((a, b) => a.gT - b.gT);
	const units = d.electrical.referenceUnits;
	const tco = out['tankless-vs-tank-water-heater'].figures;
	out['tankless-water-heater-installation-cost'] = {
		meta: { ...derived('the cost calculator model plus data/electrical.json'), notes: 'Calculator preset, figures and tables for the tankless water heater installation cost article.' },
		calculator: pre,
		figures: {
			gT: jobFig(gT), gR: jobFig(gR), eT: jobFig(eT), eR: jobFig(eR), gTank: jobFig(gTank), eTank: jobFig(eTank),
			gasPremium: fmt(gT.typical - gTank.typical), elPremium: fmt(eT.typical - eTank.typical),
			venting: fmt(model.extras.venting.typical), ventingLow: fmt(model.extras.venting.low), ventingHigh: fmt(model.extras.venting.high), gasLine: fmt(model.extras.gasLine.typical), gasLineLow: fmt(model.extras.gasLine.low), gasLineHigh: fmt(model.extras.gasLine.high),
			circuit: fmt(model.extras.circuit.typical), panel: fmt(model.extras.panel.typical), panelLow: fmt(model.extras.panel.low), panelHigh: fmt(model.extras.panel.high),
			labor: { low: fmt(model.labor.gas_tankless.low), typical: fmt(model.labor.gas_tankless.typical), high: fmt(model.labor.gas_tankless.high) }, laborEl: { low: fmt(model.labor.electric_tankless.low), typical: fmt(model.labor.electric_tankless.typical), high: fmt(model.labor.electric_tankless.high) },
			cheap: { name: sorted[0].name, typical: fmt(sorted[0].gT) }, dear: { name: sorted[sorted.length - 1].name, typical: fmt(sorted[sorted.length - 1].gT) },
			kwLow: fmt(Math.min(...units.map((u) => u.kw))), kwHigh: fmt(Math.max(...units.map((u) => u.kw))), ampsLow: fmt(Math.min(...units.map((u) => u.amps))), ampsHigh: fmt(Math.max(...units.map((u) => u.amps))),
			gtBreakEven: tco.gas.gas_tankless.be, years: tco.years, gtStatesPay: tco.gasTanklessStatesPay, lifeTankless: tco.lifeTankless, lifeTank: tco.lifeTank, tanklessSaving: tco.gasTanklessSaving,
		},
		tables: {
			jobs: table('Tankless installation cost by job (U.S. average)', [{ key: 'job', label: 'Job' }, { key: 'extras', label: 'Extras included' }, { key: 'typical', label: 'Typical', unit: '$', num: true }, { key: 'range', label: 'Range', unit: '$', num: true }], [
				['Gas tank to gas tankless', gT, 'toTankless', 'gas_tankless'], ['Gas tankless to gas tankless (replacement)', gR, 'replace', 'gas_tankless'], ['Electric tank to electric tankless', eT, 'toTankless', 'electric_tankless'], ['Electric tankless to electric tankless', eR, 'replace', 'electric_tankless'],
			].map(([label, e, sc, type]) => ({ job: label, extras: defaultExtras(model, sc, type).map((x) => model.extras[x].label.split(' (')[0].split(',')[0]).join(', '), typical: fmt(e.typical), range: `${fmt(e.low)}–${fmt(e.high)}` })), costSrc),
			itemsGas: itemTable('Itemized: gas tank to gas tankless (U.S. average)', gT),
			electric: table('Electric tankless: what the panel has to supply (one manufacturer’s whole-house range)', [{ key: 'model', label: 'Unit' }, { key: 'kw', label: 'Power', unit: 'kW', num: true }, { key: 'amps', label: 'Current at 240 V', unit: 'A', num: true }, { key: 'breakers', label: 'Breakers' }, { key: 'wire', label: 'Wire' }], units.map((u) => ({ model: u.model, kw: fmt(u.kw), amps: fmt(u.amps), breakers: u.breakers, wire: u.wire })), { source: d.electrical.meta.source, sourceUrl: d.electrical.meta.sourceUrl, sourceDate: d.electrical.meta.sourceDate }),
			byState: stateTable('Tankless installation cost by state (gas tank to gas tankless)', [{ key: 'gT', label: 'Tank to tankless', unit: '$', num: true }, { key: 'gR', label: 'Tankless replacement', unit: '$', num: true }, { key: 'eT', label: 'Electric tank to electric tankless', unit: '$', num: true }], (k) => { const x = sr.find((y) => y.code === k); return { gT: fmt(x.gT), gR: fmt(x.gR), eT: fmt(x.eT) }; }, { source: 'Equipment listings + data/installation-costs.json; labor and extras × BEA Regional Price Parity ÷ 100', sourceUrl: d['regional-cost-index'].meta.sourceUrl, sourceDate: d['regional-cost-index'].meta.sourceDate }),
		},
	};
}

/* gas and electric water heater installation cost */
{
	const op = out['gas-vs-electric-water-heater'].figures;
	const g = { s40: job('gas_tank', '40', 'replace'), s50: job('gas_tank', '50', 'replace'), s75: job('gas_tank', '75', 'replace'), fromEl: job('gas_tank', '50', 'fuelSwitch'), tankless: job('gas_tankless', 'whole', 'toTankless') };
	const e = { s40: job('electric_tank', '40', 'replace'), s50: job('electric_tank', '50', 'replace'), fromGas: job('electric_tank', '50', 'fuelSwitch'), fromGasPanel: job('electric_tank', '50', 'fuelSwitch', 'US', ['panel']), hp: job('heat_pump', '50', 'replace'), tankless: job('electric_tankless', 'whole', 'toTankless') };
	const sizeRows = (set, keys) => keys.map(([label, k]) => ({ label, unit: fmt(set[k].unit.typical), typical: fmt(set[k].typical), range: `${fmt(set[k].low)}–${fmt(set[k].high)}` }));
	const sizeCols = [{ key: 'label', label: 'Job' }, { key: 'unit', label: 'Unit', unit: '$', num: true }, { key: 'typical', label: 'Installed, typical', unit: '$', num: true }, { key: 'range', label: 'Installed range', unit: '$', num: true }];
	const fhr = (fuel, gal) => out['fhr-table'].rows.find((r) => r.fuel === fuel && r.nominalGallons === gal);
	const sr = states.map(([k, s]) => ({ code: k, name: s.name, g40: job('gas_tank', '40', 'replace', k).typical, g50: job('gas_tank', '50', 'replace', k).typical, e40: job('electric_tank', '40', 'replace', k).typical, e50: job('electric_tank', '50', 'replace', k).typical }));
	const hpe = d['heat-pump'].electrical;
	out['gas-water-heater-installation-cost'] = {
		meta: { ...derived('the cost calculator model'), notes: 'Figures and tables for the gas water heater installation cost article.' },
		figures: {
			s40: jobFig(g.s40), s50: jobFig(g.s50), s75: jobFig(g.s75), fromEl: jobFig(g.fromEl), tankless: jobFig(g.tankless),
			labor: { low: fmt(model.labor.gas_tank.low), typical: fmt(model.labor.gas_tank.typical), high: fmt(model.labor.gas_tank.high) }, permit: fmt(model.extras.permit.typical), venting: fmt(model.extras.venting.typical), ventingLow: fmt(model.extras.venting.low), ventingHigh: fmt(model.extras.venting.high), gasLine: fmt(model.extras.gasLine.typical), gasLineLow: fmt(model.extras.gasLine.low), gasLineHigh: fmt(model.extras.gasLine.high), codeItems: fmt(model.extras.codeItems.typical), expansion: fmt(model.extras.expansionTank.typical),
			fhr40: fmt(fhr('gas', 40).fhrTypical), fhr50: fmt(fhr('gas', 50).fhrTypical), fhr75: fmt(fhr('gas', 75).fhrTypical),
			runYear: op.us.gas_tank.year, runMonth: op.us.gas_tank.month, elRunYear: op.us.electric_tank.year, gasWins: op.gasWins, stateCount: op.stateCount, thermPrice: op.thermPrice,
			life: model.types.gas_tank.life,
		},
		tables: {
			jobs: table('Gas water heater installation cost by job (U.S. average)', sizeCols, sizeRows(g, [['40-gallon gas tank, like for like', 's40'], ['50-gallon gas tank, like for like', 's50'], ['75-gallon gas tank, like for like', 's75'], ['Electric tank to 50-gallon gas tank', 'fromEl'], ['Gas tank to gas tankless', 'tankless']]), costSrc),
			items: itemTable('Itemized: 50-gallon gas tank, like for like (U.S. average)', g.s50),
			itemsSwitch: itemTable('Itemized: switching an electric tank to a 50-gallon gas tank (U.S. average)', g.fromEl),
			byState: stateTable('Gas water heater installation cost by state (like for like)', [{ key: 'g40', label: '40 gallons', unit: '$', num: true }, { key: 'g50', label: '50 gallons', unit: '$', num: true }], (k) => { const x = sr.find((y) => y.code === k); return { g40: fmt(x.g40), g50: fmt(x.g50) }; }, { source: 'Equipment listings + data/installation-costs.json; labor and permit × BEA Regional Price Parity ÷ 100', sourceUrl: d['regional-cost-index'].meta.sourceUrl, sourceDate: d['regional-cost-index'].meta.sourceDate }),
		},
	};
	const pre = costPreset('electric_tank', '50', 'replace');
	out['electric-water-heater-installation-cost'] = {
		meta: { ...derived('the cost calculator model'), notes: 'Calculator preset, figures and tables for the electric water heater installation cost article.' },
		calculator: pre,
		figures: {
			s40: jobFig(e.s40), s50: jobFig(e.s50), fromGas: jobFig(e.fromGas), fromGasPanel: jobFig(e.fromGasPanel), hp: jobFig(e.hp), tankless: jobFig(e.tankless),
			labor: { low: fmt(model.labor.electric_tank.low), typical: fmt(model.labor.electric_tank.typical), high: fmt(model.labor.electric_tank.high) }, permit: fmt(model.extras.permit.typical), circuit: fmt(model.extras.circuit.typical), circuitLow: fmt(model.extras.circuit.low), circuitHigh: fmt(model.extras.circuit.high), panel: fmt(model.extras.panel.typical), panelLow: fmt(model.extras.panel.low), panelHigh: fmt(model.extras.panel.high), expansion: fmt(model.extras.expansionTank.typical),
			fhr40: fmt(fhr('electric', 40).fhrTypical), fhr50: fmt(fhr('electric', 50).fhrTypical), gas40: fmt(fhr('gas', 40).fhrTypical), gas50: fmt(fhr('gas', 50).fhrTypical),
			elementKw: op.elementKw, elementKwLow: op.elementKwLow, elementKwHigh: op.elementKwHigh, breakerAmps: hpe.breakerAmps, wireGauge: hpe.wireGauge,
			runYear: op.us.electric_tank.year, runMonth: op.us.electric_tank.month, kwh: op.us.electric_tank.kwh, hpRunYear: op.us.heat_pump.year, kwhPrice: op.kwhPrice, hpExtra: fmt(e.hp.typical - e.s50.typical), hpSaving: fmt(op.us.electric_tank.year.replace(/,/g, '') - op.us.heat_pump.year.replace(/,/g, '')),
			maxElectricGallons: d['uef-ranges'].federalMinimums.electricResistanceMaxGallons, life: model.types.electric_tank.life,
		},
		tables: {
			jobs: table('Electric water heater installation cost by job (U.S. average)', sizeCols, sizeRows(e, [['40-gallon electric tank, like for like', 's40'], ['50-gallon electric tank, like for like', 's50'], ['Gas tank to 50-gallon electric tank (new circuit)', 'fromGas'], ['Gas tank to electric, with a panel upgrade', 'fromGasPanel'], ['Electric tank to 50-gallon heat pump', 'hp'], ['Electric tank to electric tankless', 'tankless']]), costSrc),
			items: itemTable('Itemized: 50-gallon electric tank, like for like (U.S. average)', e.s50),
			byState: stateTable('Electric water heater installation cost by state (like for like)', [{ key: 'e40', label: '40 gallons', unit: '$', num: true }, { key: 'e50', label: '50 gallons', unit: '$', num: true }], (k) => { const x = sr.find((y) => y.code === k); return { e40: fmt(x.e40), e50: fmt(x.e50) }; }, { source: 'Equipment listings + data/installation-costs.json; labor and permit × BEA Regional Price Parity ÷ 100', sourceUrl: d['regional-cost-index'].meta.sourceUrl, sourceDate: d['regional-cost-index'].meta.sourceDate }),
		},
	};
}

/* 40 vs 50 gallon water heater (embeds the tank size calculator) */
{
	const rows = out['fhr-table'].rows;
	const fr = (fuel, gal) => rows.find((r) => r.fuel === fuel && r.nominalGallons === gal);
	const am = d['household-size-guides'].guides.american.rows;
	// Largest household whose American FHR band starts at or below the size's typical FHR (our reading of the guide).
	// Fits = the largest household whose typical busiest hour (our size calculator's presets) the typical FHR covers, so this page agrees with the size calculator.
	const fitsUpTo = (fhrTypical) => out['water-heater-size-calculator'].byPeople.filter((x) => Number(String(x.phd).replace(/,/g, '')) <= fhrTypical).reduce((a, x) => Math.max(a, x.n), 0);
	const models = d['fhr-to-tank-size'].models;
	const uefMed = (fuel, gal) => F.summarize(models[fuel].filter((x) => x.nominalGallons === gal).map((x) => x.uef));
	const inputs = (fuel, gal) => F.summarize(models[fuel].filter((x) => x.nominalGallons === gal).map((x) => (fuel === 'gas' ? x.inputKbtuh : x.elementKw)));
	const cmp = ['gas', 'electric'].map((fuel) => {
		const t = fuel === 'gas' ? 'gas_tank' : 'electric_tank';
		const a = job(t, '40', 'replace');
		const b = job(t, '50', 'replace');
		const f40 = fr(fuel, 40);
		const f50 = fr(fuel, 50);
		return { fuel, fhr40: f40.fhrTypical, fhr50: f50.fhrTypical, fhr40Range: `${fmt(f40.fhrLow)}–${fmt(f40.fhrHigh)}`, fhr50Range: `${fmt(f50.fhrLow)}–${fmt(f50.fhrHigh)}`, fits40: fitsUpTo(f40.fhrTypical), fits50: fitsUpTo(f50.fhrTypical), unit40: a.unit.typical, unit50: b.unit.typical, inst40: a.typical, inst50: b.typical, uef40: uefMed(fuel, 40)?.typical, uef50: uefMed(fuel, 50)?.typical, in40: inputs(fuel, 40)?.typical, in50: inputs(fuel, 50)?.typical };
	});
	const [gas, el] = cmp;
	const hp80 = fr('heatpump', 80);
	const hp50 = fr('heatpump', 50);
	const g75 = fr('gas', 75);
	const c = (x) => ({ ...x, fhr40: fmt(x.fhr40, 1).replace(/\.0$/, ''), fhr50: fmt(x.fhr50, 1).replace(/\.0$/, ''), unit40: fmt(x.unit40), unit50: fmt(x.unit50), inst40: fmt(x.inst40), inst50: fmt(x.inst50), unitDiff: fmt(x.unit50 - x.unit40), instDiff: fmt(x.inst50 - x.inst40), fhrDiff: fmt(x.fhr50 - x.fhr40, 1).replace(/\.0$/, ''), showers40: Math.floor(x.fhr40 / d['peak-hour-demand'].activities.shower.gallons), showers50: Math.floor(x.fhr50 / d['peak-hour-demand'].activities.shower.gallons), uef40: fmt(x.uef40, 2), uef50: fmt(x.uef50, 2), in40: fmt(x.in40, 1).replace(/\.0$/, ''), in50: fmt(x.in50, 1).replace(/\.0$/, '') });
	const ph = d['peak-hour-demand'];
	out['40-vs-50-gallon-water-heater'] = {
		meta: { ...derived('data/fhr-to-tank-size.json, data/household-size-guides.json and the cost calculator model'), notes: 'Figures and tables for the 40 vs 50 gallon article. "Fits up to N people" is our reading of American Water Heater’s first hour rating bands: the largest household whose band starts at or below the size’s typical FHR.' },
		figures: { gas: c(gas), el: c(el), hp50: fmt(hp50.fhrTypical, 1).replace(/\.0$/, ''), hp80: fmt(hp80.fhrTypical, 1).replace(/\.0$/, ''), hp80Range: `${fmt(hp80.fhrLow)}–${fmt(hp80.fhrHigh)}`, g75: fmt(g75.fhrTypical), hp80Inst: fmt(job('heat_pump', '80', 'replace').typical), hp50Inst: fmt(job('heat_pump', '50', 'replace').typical), g75Inst: fmt(job('gas_tank', '75', 'replace').typical), americanRows: am },
		tables: {
			compare: table('40-gallon vs 50-gallon water heater, side by side', [{ key: 'what', label: '' }, { key: 'g40', label: 'Gas 40', num: true }, { key: 'g50', label: 'Gas 50', num: true }, { key: 'e40', label: 'Electric 40', num: true }, { key: 'e50', label: 'Electric 50', num: true }], [
				{ what: 'First hour rating, typical (gal)', g40: c(gas).fhr40, g50: c(gas).fhr50, e40: c(el).fhr40, e50: c(el).fhr50 },
				{ what: 'First hour rating, range (gal)', g40: gas.fhr40Range, g50: gas.fhr50Range, e40: el.fhr40Range, e50: el.fhr50Range },
				{ what: 'Burner (kBtu/h) or element (kW)', g40: c(gas).in40, g50: c(gas).in50, e40: c(el).in40, e50: c(el).in50 },
				{ what: 'UEF, typical', g40: c(gas).uef40, g50: c(gas).uef50, e40: c(el).uef40, e50: c(el).uef50 },
				{ what: 'Covers a typical household of up to', g40: `${gas.fits40} people`, g50: `${gas.fits50} people`, e40: `${el.fits40} people`, e50: `${el.fits50} people` },
				{ what: 'Unit price, typical ($)', g40: fmt(gas.unit40), g50: fmt(gas.unit50), e40: fmt(el.unit40), e50: fmt(el.unit50) },
				{ what: 'Installed, like for like ($)', g40: fmt(gas.inst40), g50: fmt(gas.inst50), e40: fmt(el.inst40), e50: fmt(el.inst50) },
			], { source: `Rheem Performance spec sheets (FHR, input, UEF); American Water Heater selection guide (household bands); Home Depot listings (${d['cost-ranges'].lastReviewed}) and data/installation-costs.json (installed)`, sourceUrl: d['fhr-to-tank-size'].meta.sourceUrl, sourceDate: d['fhr-to-tank-size'].meta.sourceDate }),
			peakHour: table('How much hot water your busiest hour uses (AHRI worksheet values)', [{ key: 'label', label: 'Use' }, { key: 'gallons', label: 'Gallons each', num: true }], Object.values(ph.activities || {}).map((a) => ({ label: a.label, gallons: fmt(a.gallons) })), { source: ph.meta.source, sourceUrl: ph.meta.sourceUrl, sourceDate: ph.meta.sourceDate }),
		},
	};
}

/* expansion tank size tool (page 16) */
{
	const et = d['expansion-tanks'];
	const data = { expansionFactors: et.expansionFactors, atmosphericPsi: et.atmosphericPsi, tanks: et.tanks, headroomShare: et.defaults.headroomShare };
	const pa = et.pressureAdvice;
	const client = { data, defaults: { heaterGallons: et.defaults.heaterGallons, tempF: d.site.defaults.setpointTempF.value, supplyPsi: et.defaults.supplyPsi, maxPsi: et.defaults.maxPsi }, highSupplyPsi: pa.maxPsi, prvLow: pa.prvLowPsi, prvHigh: pa.prvHighPsi };
	const run = (o = {}) => describeEt({ gallons: client.defaults.heaterGallons, temp: client.defaults.tempF, supply: client.defaults.supplyPsi, max: client.defaults.maxPsi, ...o }, client);
	const d0 = run();
	const sizeOf = (r) => (r.result.safer ? `${r.result.tank.id} (${r.result.safer.id} safer)` : r.result.tank ? r.result.tank.id : '—');
	const heaters = [30, 40, 50, 65, 75, 80, 100];
	const psis = [40, 50, 60, 70, 80];
	const quick = heaters.map((gal) => ({ gal: String(gal), ...Object.fromEntries(psis.map((p) => [`p${p}`, sizeOf(run({ gallons: gal, supply: p }))])) }));
	// Cross-check: every cell of AMTROL's quick chart vs our method with the same assumptions (40 °F start, 150 psi max).
	const byModel = Object.fromEntries(et.tanks.map((t) => [t.model, t]));
	let exact = 0, saferMatch = 0;
	const misses = [];
	for (const row of et.quickChart.rows) et.quickChart.pressures.forEach((p, k) => {
		const r = F.expansionTankSizing({ heaterGallons: row.gallons, tempF: row.tempF, supplyPsi: p, maxPsi: 150 }, data);
		const want = row.picks[k];
		if (r.tank && r.tank.model === want) exact++;
		else if (r.safer && r.safer.model === want) saferMatch++;
		else misses.push({ ...row, psi: p, want, ours: r.tank ? r.tank.model : 'none', expanded: r.expanded });
	});
	const cells = et.quickChart.rows.length * et.quickChart.pressures.length;
	const ref = (id) => ({ source: `${et.references[id].publisher}: ${et.references[id].title}`, sourceUrl: et.references[id].sourceUrl });
	const sample = (gal, t, p) => { const r = F.expansionTankSizing({ heaterGallons: gal, tempF: t, supplyPsi: p, maxPsi: 150 }, data); const row = et.quickChart.rows.find((x) => x.gallons === gal && x.tempF === t); const want = row.picks[et.quickChart.pressures.indexOf(p)]; return { r, want, wantTank: byModel[want] }; };
	const s1 = sample(50, 140, 60);
	const s2 = sample(80, 160, 80);
	const s3 = sample(40, 160, 80);
	const lab = (t) => `${t.label} (${t.model})`;
	const xc = [
		{ id: 'amtrol-example', label: 'AMTROL worked example: 240-gal heater, 150 °F, 60 psi supply, 125 psi maximum', ours: (() => { const r = F.expansionTankSizing({ heaterGallons: 240, tempF: 150, supplyPsi: 60, maxPsi: 125 }, data); return `${fmt(r.expanded, 1)} gal expanded × DPF ${fmt(r.dpf, 2)} = ${fmt(r.required, 1)} gal`; })(), published: 'AMTROL: 4.3 gal expanded × DPF 2.1 = 9.0 gal (ST-25V or ST-30V-C)', match: true, why: 'Same method. AMTROL rounds the DPF to 2.1 before multiplying; unrounded it is 2.15, which gives 9.2 gallons.', ...ref('amtrol-sizing') },
		{ id: 'amtrol-50-140-60', label: '50-gal heater, 140 °F, 60 psi supply, 150 psi relief valve', ours: `${fmt(s1.r.required, 2)} gal needed → ${lab(s1.r.tank)}`, published: `AMTROL quick chart: ${s1.want}`, match: s1.r.tank.model === s1.want, why: 'Same tank.', ...ref('amtrol-chart') },
		{ id: 'amtrol-80-160-80', label: '80-gal heater, 160 °F, 80 psi supply, 150 psi relief valve', ours: `${fmt(s2.r.required, 2)} gal needed → minimum ${lab(s2.r.tank)}${s2.r.safer ? `, safer ${lab(s2.r.safer)}` : ''}`, published: `AMTROL quick chart: ${s2.want}`, match: (s2.r.safer || s2.r.tank).model === s2.want, why: 'The required 3.93 gallons fits a 4.4-gallon tank with little room to spare. AMTROL picks the larger tank; our headroom rule names it as the safer pick.', ...ref('amtrol-chart') },
		{ id: 'amtrol-40-160-80', label: '40-gal heater, 160 °F, 80 psi supply, 150 psi relief valve', ours: `${fmt(s3.r.required, 2)} gal needed → minimum ${lab(s3.r.tank)}${s3.r.safer ? `, safer ${lab(s3.r.safer)}` : ''}`, published: `AMTROL quick chart: ${s3.want}`, match: (s3.r.safer || s3.r.tank).model === s3.want, why: 'Borderline case: 1.97 gallons needed against a 2.0-gallon tank. AMTROL picks the larger tank; so does our headroom rule.', ...ref('amtrol-chart') },
		{ id: 'amtrol-all', label: `All ${cells} cells of AMTROL’s quick sizing chart (40, 50, 60, 80, 120 gal; 140, 160, 180 °F; 40, 60, 80 psi)`, ours: `${exact} same minimum tank, ${saferMatch} same as our safer pick, ${misses.length} different`, published: 'AMTROL Therm-X-Trol Quick Sizing Charts (MC8520, 04/22)', match: misses.length <= 1, why: misses.map((x) => `${x.gallons}-gal, ${x.tempF} °F, ${x.psi} psi: AMTROL ${x.want}, ours ${x.ours}, because ${fmt(x.expanded, 2)} gal of expanded water is more than the smaller tank's ${fmt(byModel[x.want].acceptanceGallons, 1)} gal acceptance volume.`).join(' ') || 'All match.', ...ref('amtrol-chart') },
	];
	const hd = ic.references['home-depot-parts'];
	out['water-heater-expansion-tank-size'] = {
		meta: { ...derived('data/expansion-tanks.json (AMTROL method and tank data)'), notes: 'Default result, quick-size table, expansion factors and AMTROL cross-checks for the expansion tank size calculator.' },
		default: { status: d0.status, statusLabel: d0.statusLabel, headline: d0.headline, explain: d0.explain, formula: d0.formula, table: d0.table, sources: `Method and tank data: AMTROL sizing guide and spec sheet. <a href="/methodology/#water-heater-expansion-tank-size">See every cell of AMTROL’s chart checked against ours</a>.` },
		figures: {
			d0: { tank: d0.result.tank.label, expanded: fmt(d0.result.expanded, 2), required: fmt(d0.result.required, 2), dpf: fmt(d0.result.dpf, 2), factorPct: fmt(d0.result.factor * 100, 1) },
			heater: client.defaults.heaterGallons, temp: client.defaults.tempF, supply: client.defaults.supplyPsi, max: client.defaults.maxPsi,
			f140: fmt(F.expansionFactor(140, et.expansionFactors) * 100, 1), f120: fmt(F.expansionFactor(120, et.expansionFactors) * 100, 1),
			r80: (() => { const r = run({ gallons: 80, supply: 80 }).result; return { tank: r.tank.label, safer: r.safer ? r.safer.label : '', required: fmt(r.required, 2) }; })(),
			r50at80: (() => { const r = run({ supply: 80 }).result; return { tank: r.tank.label, safer: r.safer ? r.safer.label : '', required: fmt(r.required, 2) }; })(),
			r50max125: (() => { const r = run({ max: 125 }).result; return { tank: r.tank.label, safer: r.safer ? r.safer.label : '', required: fmt(r.required, 2) }; })(),
			cells, exact, saferMatch, missCount: misses.length, headroomPct: fmt(et.defaults.headroomShare * 100),
			...(() => { const ps = hd.quotes.filter((q) => /expansion|therm-x-trol/i.test(q)).map((q) => Number((q.match(/\$([\d.]+)/) || [])[1])).filter(Number.isFinite); return { partLow: fmt(Math.min(...ps)), partHigh: fmt(Math.max(...ps)) }; })(), costTypical: fmt(model.extras.expansionTank.typical), costLow: fmt(model.extras.expansionTank.low), costHigh: fmt(model.extras.expansionTank.high),
			prvLow: pa.prvLowPsi, prvHigh: pa.prvHighPsi, maxSupply: pa.maxPsi, relief: et.relief.commonPsi.join(' or '),
		},
		crossChecks: xc,
		tables: {
			quick: table(`Expansion tank size by heater size and supply pressure (${client.defaults.tempF} °F setting, ${client.defaults.maxPsi} psi relief valve)`, [{ key: 'gal', label: 'Heater (gal)', num: true }, ...psis.map((p) => ({ key: `p${p}`, label: `${p} psi`, unit: 'gal tank', num: true }))], quick, { source: 'Computed with AMTROL’s sizing method (expansion factor × design pressure factor) and AMTROL tank volumes; “safer” = within our 15% headroom rule', sourceUrl: et.meta.sourceUrl, sourceDate: et.meta.sourceDate }),
			factors: table('How much water grows when heated from 40 °F', [{ key: 't', label: 'Tank setting', unit: '°F', num: true }, { key: 'pct', label: 'Volume increase', unit: '%', num: true }, { key: 'g50', label: 'In a 50-gal tank', unit: 'gal', num: true }], et.expansionFactors.rows.map((x) => ({ t: fmt(x.tempF), pct: fmt(x.factor * 100, 2), g50: fmt(x.factor * 50, 2) })), { source: et.expansionFactors.source, sourceUrl: et.expansionFactors.sourceUrl, sourceDate: et.meta.sourceDate }),
			tanks: table('Common residential expansion tank sizes', [{ key: 'label', label: 'Tank' }, { key: 'model', label: 'Example model' }, { key: 'total', label: 'Total volume', unit: 'gal', num: true }, { key: 'accept', label: 'Max. acceptance', unit: 'gal', num: true }], et.tanks.map((t) => ({ label: t.label, model: `AMTROL ${t.model}`, total: fmt(t.totalGallons, 1), accept: fmt(t.acceptanceGallons, 1) })), { source: et.tanksSource, sourceUrl: et.references['amtrol-chart'].sourceUrl, sourceDate: et.meta.sourceDate }),
			prices: table('Thermal expansion tank prices (parts only)', [{ key: 'p', label: 'Listing' }], hd.quotes.filter((q) => /expansion|therm-x-trol/i.test(q)).map((q) => ({ p: q })), { source: `${hd.publisher}: ${hd.title}`, sourceUrl: hd.sourceUrl, sourceDate: hd.checked }),
		},
		stateOptions: [],
		client,
	};
}

/* ---------- Phase 6b batch 2: hot water runs out fast (winter vs summer hot share of a shower) ---------- */
{
	const ro = d.troubleshooting.runsOut;
	const inlet = out['inlet-temps'].states;
	const hotF = d.site.defaults.setpointTempF.value;
	const shower = d['fixture-flow-rates'].fixtures.shower;
	const mins = ro.exampleShowerMinutes;
	const pct = (v) => Math.round(v * 100);
	const row = (code) => {
		const s = inlet[code];
		const w = F.hotShareOfMix(ro.showerMixF, hotF, s.coldestMonth);
		const su = F.hotShareOfMix(ro.showerMixF, hotF, s.warmestMonth);
		const gw = F.hotGallonsDrawn(shower.gpm, mins, w);
		const gs = F.hotGallonsDrawn(shower.gpm, mins, su);
		const slow = Math.round((F.temperatureRise(hotF, s.coldestMonth) / F.temperatureRise(hotF, s.warmestMonth) - 1) * 100); // recovery gallons per hour ∝ 1 ÷ rise
		return { code, name: s.name, winterF: r(s.coldestMonth), summerF: r(s.warmestMonth), winterPct: pct(w), summerPct: pct(su), winterGal: r(gw, 1), summerGal: r(gs, 1), extraPct: Math.round((gw / gs - 1) * 100), slowPct: slow };
	};
	const rows = ro.exampleStates.map(row);
	const coldest = rows.reduce((a, b) => (b.winterF < a.winterF ? b : a));
	const ex = { cold: ro.winterInletHighF }; // A. O. Smith's top of the winter range
	const share50 = F.hotShareOfMix(ro.showerMixF, hotF, ex.cold);
	const lowFlowGal = F.hotGallonsDrawn(shower.lowFlowGpm, mins, share50);
	const fullGal = F.hotGallonsDrawn(shower.gpm, mins, share50);
	out['hot-water-runs-out-fast'] = {
		meta: { ...derived('data/troubleshooting.json (shower mix temperature), data/fixture-flow-rates.json (shower flow), data/site.json (tank setting) and data/computed/inlet-temps.json'), notes: 'Hot share = (shower temperature − inlet) ÷ (tank setting − inlet), formulas.hotShareOfMix; hot gallons = flow × minutes × hot share, formulas.hotGallonsDrawn. Reheat time per gallon scales with the temperature rise (formulas.temperatureRise), so winter ÷ summer rise − 1 is how much longer each gallon takes. No heat loss in the pipes is counted, so real draws are a little higher. The shower length is our example.' },
		figures: {
			hotF, mixF: ro.showerMixF, gpm: shower.gpm, lowFlowGpm: shower.lowFlowGpm, minutes: mins,
			example: { inletF: ex.cold, pct: pct(share50), fullGal: r(fullGal, 1), lowFlowGal: r(lowFlowGal, 1), savedGal: r(fullGal - lowFlowGal, 1) },
			coldest: { name: coldest.name, winterF: coldest.winterF, extraPct: coldest.extraPct },
			maxExtraPct: Math.max(...rows.map((x) => x.extraPct)),
			minExtraPct: Math.min(...rows.map((x) => x.extraPct)),
			coldestSlowPct: coldest.slowPct,
			maxSlowPct: Math.max(...rows.map((x) => x.slowPct)),
			minSlowPct: Math.min(...rows.map((x) => x.slowPct)),
		},
		tables: {
			winterSummer: table(
				`How much tank water one ${mins}-minute shower uses, winter vs summer`,
				[
					{ key: 'name', label: 'State' },
					{ key: 'winterF', label: 'Inlet, coldest month', unit: '°F', num: true },
					{ key: 'summerF', label: 'Inlet, warmest month', unit: '°F', num: true },
					{ key: 'winterGal', label: 'Tank water used, winter', unit: 'gal', num: true },
					{ key: 'summerGal', label: 'Tank water used, summer', unit: 'gal', num: true },
					{ key: 'extra', label: 'More tank water in winter', num: true },
					{ key: 'slow', label: 'Longer to reheat each gallon in winter', num: true },
				],
				rows.map((x) => ({ name: x.name, winterF: x.winterF, summerF: x.summerF, winterGal: fmt(x.winterGal, 1), summerGal: fmt(x.summerGal, 1), extra: x.extraPct + '%', slow: x.slowPct + '%' })),
				{ source: `Computed: ${shower.gpm} GPM shower at ${ro.showerMixF} °F (Stiebel Eltron sizing sheet) from a ${hotF} °F tank; inlet temperatures estimated from NOAA air temperatures (see /methodology)`, sourceUrl: 'https://hotwatermath.com/methodology/', sourceDate: d.troubleshooting.meta.sourceDate },
			),
		},
	};
}

/* Phase 6: Quick Answers must quote the computed numbers */
{
	const pages = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8')).pages;
	const qa = (slug) => pages.find((p) => p.slug === slug)?.quickAnswer || '';
	const hpf = out['heat-pump-water-heater-cost'].figures;
	const ivf = out['water-heater-installation-cost'].figures;
	const tlf = out['tankless-water-heater-installation-cost'].figures;
	const gsf = out['gas-water-heater-installation-cost'].figures;
	const elf = out['electric-water-heater-installation-cost'].figures;
	const etf = out['water-heater-expansion-tank-size'].figures;
	const must = [
		['heat-pump-water-heater-cost', ['$' + hpf.sizes['50'].fromEl, '$' + hpf.e0.saving]],
		['water-heater-installation-cost', ['$' + ivf.g50.typical, '$' + ivf.e50.typical]],
		['tankless-water-heater-installation-cost', ['$' + tlf.gT.typical, '$' + tlf.gR.typical]],
		['gas-water-heater-installation-cost', ['$' + gsf.s40.typical, '$' + gsf.s50.typical]],
		['electric-water-heater-installation-cost', ['$' + elf.s40.typical, '$' + elf.s50.typical]],
		['water-heater-expansion-tank-size', [etf.d0.tank]],
		['40-vs-50-gallon-water-heater', [out['40-vs-50-gallon-water-heater'].figures.gas.fhrDiff + ' more gallons', '$' + out['40-vs-50-gallon-water-heater'].figures.gas.instDiff]],
	];
	for (const [slug, parts] of must) for (const part of parts) if (pages.some((p) => p.slug === slug && p.status !== 'planned') && !qa(slug).includes(part)) throw new Error('pages.json quickAnswer for ' + slug + ' must contain "' + part + '" (computed); update it.');
}

/* Phase 6b batch 2: counts in titles and descriptions must match the data tables they describe */
{
	const pages = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8')).pages;
	const t = d.troubleshooting.tables;
	const must = [
		['water-heater-reset-button', 'description', t.resetCauses.rows.length + ' reasons'],
		['water-heater-making-noise', 'description', t.noiseSounds.rows.length + ' sounds'],
		['hot-water-runs-out-fast', 'metaTitle', t.runsOutCauses.rows.length + ' Causes'],
	];
	for (const [slug, field, part] of must) {
		const pg = pages.find((x) => x.slug === slug);
		if (pg && pg.status !== 'planned' && !String(pg[field]).includes(part)) throw new Error(`pages.json ${field} for ${slug} must contain "${part}" (row count of its table); update it.`);
	}
}

/* ---------- Phase 6c: state cost pages (/water-heater-installation-cost/{state}/) ---------- */
// One computed file per state page. Every number comes from the shared cost model, the state data files
// (BEA price factor, EIA prices, NOAA-derived inlet temperatures) and data/state-pages.json (RECS fuel mix, rebates).
{
	const sp = d['state-pages'];
	const pagesReg = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8')).pages;
	const allCodes = Object.keys(model.states);
	const rankOf = (code, fn) => [...allCodes].sort((a, b) => fn(a) - fn(b)).indexOf(code) + 1; // 1 = lowest
	const usJob = { g50: job('gas_tank', '50', 'replace'), e50: job('electric_tank', '50', 'replace'), hp50: job('heat_pump', '50', 'replace'), gTl: job('gas_tankless', 'whole', 'replace'), toTl: job('gas_tankless', 'whole', 'toTankless') };
	const ref = d['tankless-reference-units'].gasUnits.find((u) => u.model === 'RUR98i');
	const refOut = ref.inputBtuh * ref.efficiency; // output = input × efficiency (formulas.gasInputBtuh inverted)
	const usMix = sp.references.recs.us;
	const showerGpm = d['fixture-flow-rates'].fixtures.shower.gpm;
	const inletAll = out['inlet-temps'].states;
	const setF = model.setpointF;
	const years = 10;
	const opFor = (k) => operatingCosts(model, { gallonsPerDay: model.typicalGallons, setpointF: setF, inletF: inletAll[k].annualAvg, prices: { usdPerKwh: model.states[k].usdPerKwh, usdPerTherm: model.states[k].usdPerTherm } }).rows;
	const opUs = operatingCosts(model, { gallonsPerDay: model.typicalGallons, setpointF: setF, inletF: model.us.inletF, prices: { usdPerKwh: model.us.usdPerKwh, usdPerTherm: model.us.usdPerTherm } }).rows;
	const yearOf = (rows, id) => rows.find((x) => x.id === id).year;
	const pctVs = (a, b) => Math.round(((a - b) / b) * 100);
	const vsWord = (p) => (p === 0 ? 'the same as' : p > 0 ? `${p}% above` : `${-p}% below`);
	const ord = (n) => `${n}${[11, 12, 13].includes(n % 100) ? 'th' : ({ 1: 'st', 2: 'nd', 3: 'rd' })[n % 10] || 'th'}`;
	const index = [];
	for (const s of sp.statePages) {
		const k = s.code;
		const st = model.states[k];
		const slug = `water-heater-installation-cost/${s.slug}`;
		const reg = pagesReg.find((p) => p.slug === slug);
		if (!reg) throw new Error(`data/pages.json has no entry for ${slug}`);
		const j = { g40: job('gas_tank', '40', 'replace', k), g50: job('gas_tank', '50', 'replace', k), e40: job('electric_tank', '40', 'replace', k), e50: job('electric_tank', '50', 'replace', k), hp50: job('heat_pump', '50', 'replace', k), hpGas: job('heat_pump', '50', 'fuelSwitch', k), gTl: job('gas_tankless', 'whole', 'replace', k), toTl: job('gas_tankless', 'whole', 'toTankless', k), eTl: job('electric_tankless', 'whole', 'replace', k), g50code: job('gas_tank', '50', 'replace', k, ['expansionTank', 'codeItems']) };
		const op = opFor(k);
		const gYear = yearOf(op, 'gas_tank');
		const eYear = yearOf(op, 'electric_tank');
		const hYear = yearOf(op, 'heat_pump');
		const cheapest = [...op].sort((a, b) => a.year - b.year)[0];
		// Same call as the heat pump savings calculator, so both pages give the same saving and payback for this state.
		const hpRes = describeHp({ current: 'electric_tank', size: '50', household: 'typical', state: k }, out['heat-pump-water-heater-cost'].client).result;
		const hpSave = hpRes.saving;
		const hpExtra = hpRes.extra;
		const hpPay = hpRes.payback;
		const own = (inst, yr) => inst + yr * years;
		const ownG = own(j.g50.typical, gYear);
		const ownE = own(j.e50.typical, eYear);
		const ownH = own(j.hp50.typical, hYear);
		const ownBest = [['gas_tank', ownG], ['electric_tank', ownE], ['heat_pump', ownH]].sort((a, b) => a[1] - b[1])[0][0];
		const inl = inletAll[k];
		const riseW = F.temperatureRise(setF, inl.coldestMonth);
		const riseA = F.temperatureRise(setF, inl.annualAvg);
		const riseS = F.temperatureRise(setF, inl.warmestMonth);
		const [gW, gA, gS] = F.maxGpmByRise(refOut, [riseW, riseA, riseS]).map((x) => x.gpm);
		const wh = s.waterHeating;
		const elecFirst = wh.electricPct > wh.gasPct;
		const mainType = elecFirst ? 'electric_tank' : 'gas_tank';
		const presetExtras = s.notes.map((n) => n.presetExtra).filter(Boolean);
		const v = { type: mainType, size: '50', state: k, scenario: 'replace', ...Object.fromEntries([...defaultExtras(model, 'replace', mainType), ...presetExtras].map((x) => [x, true])) };
		const dd = describeCost(v, costClient);
		const ep = out['energy-prices'].states[k];
		const multRank = rankOf(k, (c) => model.states[c].multiplier);
		const kwhRank = rankOf(k, (c) => model.states[c].usdPerKwh);
		const thermRank = rankOf(k, (c) => model.states[c].usdPerTherm);
		// Month by month: incoming water (full correlation), what the reference tankless delivers, and the main tank's energy bill.
		const gw = d['groundwater-temp-by-state'];
		const monthsIn = F.monthlyMainsTemps(gw.states[k].annualAvgAirF, gw.states[k].maxMonthlyDiffF, gw.correlation);
		const mainUef = model.types[mainType].uef;
		const monthRows = monthsIn.map((t, i) => {
			const rise = F.temperatureRise(setF, t);
			const yr = F.yearlyEnergyCost(model.typicalGallons, rise, model.types[mainType].fuel, mainUef, { usdPerKwh: st.usdPerKwh, usdPerTherm: st.usdPerTherm }).year;
			return { month: MONTHS[i], air: fmt(gw.states[k].monthlyNormalsF[i], 1), inlet: fmt(t, 1), gpm: fmt(F.maxGpmByRise(refOut, [rise])[0].gpm, 1), cost: fmt(Math.round((yr * F.MONTH_DAYS[i]) / 365)), costNum: Math.round((yr * F.MONTH_DAYS[i]) / 365), inletNum: t };
		});
		const coldM = [...monthRows].sort((a, b) => a.inletNum - b.inletNum)[0];
		const warmM = [...monthRows].sort((a, b) => b.inletNum - a.inletNum)[0];
		// Metro areas: the same job priced with each metro's BEA price parity instead of the state's.
		const metros = d['metro-price-parities'].metrosByState[k].map((mt) => {
			const mm = { ...model, states: { ...model.states, METRO: { ...st, multiplier: mt.rppAllItems / 100 } } };
			const mj = (type) => estimateInstalled(mm, { type, size: '50', state: 'METRO', extras: defaultExtras(mm, 'replace', type) });
			return { name: mt.name, rpp: mt.rppAllItems, year: mt.year, g50: mj('gas_tank').typical, e50: mj('electric_tank').typical, sourceUrl: mt.sourceUrl };
		});
		const mSorted = [...metros].sort((a, b) => a.g50 - b.g50);
		const metroSkipped = d['metro-price-parities'].skippedNoSeries?.[k] || [];
		const fig = {
			code: k, name: st.name, slug: s.slug, url: `/${slug}/`, stateCount: allCodes.length, populationRank: s.populationRank, population: fmt(s.population), popMillion: fmt(s.population / 1e6, 1),
			mult: fmt(st.multiplier, 3), multPct: pctVs(st.multiplier, 1), multVs: vsWord(Math.round((st.multiplier - 1) * 100)), multRank, multRankOrd: ord(multRank), multRankPhrase: multRank > allCodes.length / 2 ? (allCodes.length - multRank + 1 === 1 ? 'highest' : `${ord(allCodes.length - multRank + 1)} highest`) : `${ord(multRank)} lowest`, rpp: fmt(d['regional-cost-index'].states[k].rppAllItems, 1), rppYear: d['regional-cost-index'].states[k].year,
			g40: jobFig(j.g40), g50: jobFig(j.g50), e40: jobFig(j.e40), e50: jobFig(j.e50), hp50: jobFig(j.hp50), hpGas: jobFig(j.hpGas), gTl: jobFig(j.gTl), toTl: jobFig(j.toTl), eTl: jobFig(j.eTl), g50code: jobFig(j.g50code),
			g50Diff: fmt(Math.abs(j.g50.typical - usJob.g50.typical)), g50DiffSign: j.g50.typical >= usJob.g50.typical ? 'more' : 'less', e50Diff: fmt(Math.abs(j.e50.typical - usJob.e50.typical)), e50DiffSign: j.e50.typical >= usJob.e50.typical ? 'more' : 'less',
			usG50: fmt(usJob.g50.typical), usE50: fmt(usJob.e50.typical), usHp50: fmt(usJob.hp50.typical), usToTl: fmt(usJob.toTl.typical),
			laborG: fmt(Math.round(model.labor.gas_tank.typical * st.multiplier)), laborE: fmt(Math.round(model.labor.electric_tank.typical * st.multiplier)), permit: fmt(Math.round(model.extras.permit.typical * st.multiplier)),
			kwh: fmt(st.usdPerKwh, 3), kwhCents: fmt(ep.usdPerKwh * 100, 2), kwhYear: ep.electricYear, kwhRank, kwhRankOrd: ord(kwhRank), kwhVs: vsWord(pctVs(st.usdPerKwh, model.us.usdPerKwh)), therm: fmt(st.usdPerTherm, 2), thermYear: ep.gasYear, thermRank, thermRankOrd: ord(thermRank), thermVs: vsWord(pctVs(st.usdPerTherm, model.us.usdPerTherm)), usKwh: fmt(model.us.usdPerKwh, 3), usTherm: fmt(model.us.usdPerTherm, 2),
			gYear: fmt(gYear), eYear: fmt(eYear), hYear: fmt(hYear), usGYear: fmt(yearOf(opUs, 'gas_tank')), usEYear: fmt(yearOf(opUs, 'electric_tank')), usHYear: fmt(yearOf(opUs, 'heat_pump')), cheapest: model.types[cheapest.id].label, cheapestId: cheapest.id, gallons: model.typicalGallons, setpointF: setF,
			gasVsElecYear: fmt(Math.abs(eYear - gYear)), gasCheaperToRun: gYear < eYear, gasPremium: fmt(j.g50.typical - j.e50.typical), gasPay: gYear < eYear ? ((x) => (x < 1 ? 'under a year' : `about ${fmt(x, 1)} years`))(F.simplePaybackYears(j.g50.typical - j.e50.typical, eYear - gYear)) : '',
			hpSave: fmt(hpSave), hpExtra: fmt(hpExtra), hpPay: hpPay === null ? 'never' : fmt(hpPay, 1), hpPayNum: hpPay, hpLife: model.types.heat_pump.life,
			years, ownG: fmt(ownG), ownE: fmt(ownE), ownH: fmt(ownH), ownBest: model.types[ownBest].label, ownBestId: ownBest,
			inletAnnual: fmt(inl.annualAvg, 1), inletWinter: fmt(inl.coldestMonth, 1), inletSummer: fmt(inl.warmestMonth, 1), riseW: fmt(riseW, 1), riseA: fmt(riseA, 1), riseS: fmt(riseS, 1),
			refModel: `${ref.maker} ${ref.model}`, refInput: fmt(ref.inputBtuh), gpmW: fmt(gW, 1), gpmA: fmt(gA, 1), gpmS: fmt(gS, 1), showerGpm: fmt(showerGpm, 1), showersW: Math.floor(gW / showerGpm), showersS: Math.floor(gS / showerGpm), seasonLossPct: Math.round((1 - gW / gS) * 100),
			electricPct: wh.electricPct, gasPct: wh.gasPct, propanePct: wh.propanePct, fuelOilPct: wh.fuelOilPct ?? 0, homesMillion: fmt(wh.homesMillion, 2), elecFirst, mainType, mainLabel: model.types[mainType].label,
			hpPaysInLife: hpPay !== null && hpPay <= model.types.heat_pump.life, elecMajority: wh.electricPct >= 50, gasMajority: wh.gasPct >= 50, fuelOilShown: (wh.fuelOilPct ?? 0) > 0, gasTanklessSave: fmt(gYear - yearOf(op, 'gas_tankless')), gasTanklessYear: fmt(yearOf(op, 'gas_tankless')), tlPremium: fmt(j.toTl.typical - j.g50.typical), coldMonth: coldM.month, coldMonthInlet: coldM.inlet, coldMonthCost: coldM.cost, warmMonth: warmM.month, warmMonthInlet: warmM.inlet, warmMonthCost: warmM.cost, winterExtraPct: Math.round((F.temperatureRise(setF, coldM.inletNum) / F.temperatureRise(setF, warmM.inletNum) - 1) * 100), metroCount: metros.length, metroCheap: { name: mSorted[0].name, g50: fmt(mSorted[0].g50), e50: fmt(mSorted[0].e50), rpp: fmt(mSorted[0].rpp, 1) }, metroDear: { name: mSorted[mSorted.length - 1].name, g50: fmt(mSorted[mSorted.length - 1].g50), e50: fmt(mSorted[mSorted.length - 1].e50), rpp: fmt(mSorted[mSorted.length - 1].rpp, 1) }, metroSpread: fmt(mSorted[mSorted.length - 1].g50 - mSorted[0].g50), metroSkipped: metroSkipped.join('; '), metroYear: metros[0].year, rebate: s.rebate, notes: s.notes, heehrMax: fmt(sp.references.heehrHpwhMaxUsd.value), presetExtras,
		};
		// "What stands out": sentences written from this state's own extremes (rank among the 50 states and DC).
		const N = allCodes.length;
		const hl = [];
		const hiOrd = (r) => (N - r + 1 === 1 ? 'highest' : `${ord(N - r + 1)} highest`);
		if (multRank >= N - 5) hl.push(`${st.name}'s price level is the ${hiOrd(multRank)} of ${N} states and DC, so labor, permits and extras cost more here than almost anywhere: about $${fig.laborG} of labor on a gas tank against $${fmt(model.labor.gas_tank.typical)} nationally.`);
		else if (multRank <= 20) hl.push(`${st.name} is one of the cheaper states for installation labor: its price level is the ${ord(multRank)} lowest of ${N}, so a gas tank's labor runs about $${fig.laborG} against $${fmt(model.labor.gas_tank.typical)} nationally.`);
		else if (fig.multPct === 0) hl.push(`${st.name}'s price level matches the U.S. average exactly, so its prices are the national prices; only your metro area moves them.`);
		if (kwhRank >= N - 10) hl.push(`Electricity is expensive in ${st.name} (${fig.kwhCents} cents per kWh, the ${hiOrd(kwhRank)}), so a standard electric tank costs about $${fig.eYear} a year to run and a heat pump saves about $${fig.hpSave} a year against it.`);
		else if (kwhRank <= 20) hl.push(`Electricity is cheaper than average in ${st.name} (${fig.kwhCents} cents per kWh, ${fig.kwhVs} the U.S. average), so a standard electric tank costs about $${fig.eYear} a year to run here, against $${fig.gYear} for gas.`);
		if (thermRank >= N - 10) hl.push(`Natural gas costs more here than in most states ($${fig.therm} per therm, the ${hiOrd(thermRank)}), so gas's running-cost advantage is smaller than the national picture suggests.`);
		else if (thermRank <= 12) hl.push(`Natural gas is cheap in ${st.name} ($${fig.therm} per therm, the ${ord(thermRank)} lowest of ${N}), so a gas tank costs only about $${fig.gYear} a year to run, and gas stays the cheapest tank to own over ${years} years.`);
		if (wh.electricPct >= 70) hl.push(`${wh.electricPct}% of ${st.name} homes heat water with electricity, against ${usMix.electricPct}% nationally, so most replacements here are electric-to-electric and need no gas or vent work.`);
		else if (wh.gasPct >= 65) hl.push(`${wh.gasPct}% of ${st.name} homes heat water with natural gas, against ${usMix.gasPct}% nationally, so most replacements here are gas-to-gas with an existing vent and gas line.`);
		else if (Math.abs(wh.gasPct - wh.electricPct) <= 10) hl.push(`${st.name} is split between fuels: ${wh.gasPct}% of homes heat water with gas and ${wh.electricPct}% with electricity, so check which you have before you compare quotes; a fuel switch adds a gas line and vent, or a 240-volt circuit.`);
		if (inl.coldestMonth < 45) hl.push(`Incoming water drops to about ${fig.inletWinter} °F in the coldest month, so a whole-house tankless unit manages only ${fig.showersW === 1 ? 'one full-flow shower' : `${fig.showersW} full-flow showers`} at a time in winter; a tank, or two tankless units, suits larger households better.`);
		else if (inl.coldestMonth > 60) hl.push(`Incoming water stays near ${fig.inletWinter} °F even in the coldest month, so a single whole-house tankless unit can run ${fig.showersW} full-flow showers at once all year.`);
		if (wh.fuelOilPct) hl.push(`${wh.fuelOilPct}% of ${st.name} homes still heat water with fuel oil, often from the heating boiler; those systems are priced differently from the tanks on this page.`);
		if (s.rebate.status === 'open' && s.rebate.hpwh) hl.push(`Of the ${sp.statePages.length} states we cover, ${st.name} is one where the state's official page confirms the federal appliance rebate pays for a heat pump water heater now: up to $${fig.heehrMax} for income-qualified homes.`);
		if (mSorted[mSorted.length - 1].g50 - mSorted[0].g50 >= 50) hl.push(`Where you live inside ${st.name} matters: the same 50-gallon gas job is about $${fig.metroDear.g50} in the ${fig.metroDear.name} area and $${fig.metroCheap.g50} in the ${fig.metroCheap.name} area.`);
		if (s.notes.length) hl.push(...s.notes.map((n) => n.heading + '.'));
		if (hl.length < 2) throw new Error(`${st.name}: fewer than two state-specific highlights; add a rule or a sourced note.`);
		fig.highlights = hl;
		index.push({ code: k, name: st.name, slug, g50: j.g50.typical, e50: j.e50.typical, hp50: j.hp50.typical, mult: st.multiplier, electricPct: wh.electricPct, gasPct: wh.gasPct, kwh: st.usdPerKwh, therm: st.usdPerTherm });
		const usRow = (label, a, b) => ({ label, state: a, us: b });
		const cmpCols = [{ key: 'label', label: 'Job (like-for-like replacement)' }, { key: 'state', label: st.name, unit: '$', num: true }, { key: 'us', label: 'U.S. average', unit: '$', num: true }];
		const runCols = [{ key: 'label', label: 'Type' }, { key: 'uef', label: 'UEF', num: true }, { key: 'state', label: `${st.name}, per year`, unit: '$', num: true }, { key: 'us', label: 'U.S. average, per year', unit: '$', num: true }, { key: 'own', label: `Installed + ${years} years of energy`, unit: '$', num: true }];
		const ownOf = { gas_tank: ownG, electric_tank: ownE, heat_pump: ownH };
		const instOf = { gas_tank: j.g50.typical, electric_tank: j.e50.typical, heat_pump: j.hp50.typical, gas_tankless: j.gTl.typical, electric_tankless: j.eTl.typical };
		out[slug.replace('/', '-')] = {
			meta: { ...derived('the cost calculator model, data/energy-prices.json, data/groundwater-temp-by-state.json, data/tankless-reference-units.json and data/state-pages.json'), notes: `Figures, tables and calculator preset for the ${st.name} water heater installation cost page.` },
			calculator: { inputs: v, default: { status: dd.status, statusLabel: dd.statusLabel, headline: dd.headline, explain: dd.explain, formula: dd.formula, table: dd.table, sources: out['water-heater-cost-calculator'].default.sources } },
			figures: fig,
			tables: {
				compare: table(`${st.name} vs U.S. average: installed cost, typical`, cmpCols, [
					usRow('40-gallon gas tank', fmt(j.g40.typical), fmt(job('gas_tank', '40', 'replace').typical)), usRow('50-gallon gas tank', fmt(j.g50.typical), fmt(usJob.g50.typical)), usRow('40-gallon electric tank', fmt(j.e40.typical), fmt(job('electric_tank', '40', 'replace').typical)), usRow('50-gallon electric tank', fmt(j.e50.typical), fmt(usJob.e50.typical)), usRow('50-gallon heat pump (replacing electric)', fmt(j.hp50.typical), fmt(usJob.hp50.typical)), usRow('Gas tankless (replacing tankless)', fmt(j.gTl.typical), fmt(usJob.gTl.typical)), usRow('Gas tank to gas tankless', fmt(j.toTl.typical), fmt(usJob.toTl.typical)),
				], { ...costSrc, source: `${costSrc.source}; ${st.name} labor and extras × BEA price factor ${fmt(st.multiplier, 3)}` }),
				itemsMain: itemTable(`Itemized: 50-gallon ${elecFirst ? 'electric' : 'gas'} tank replacement in ${st.name}`, elecFirst ? j.e50 : j.g50),
				itemsOther: itemTable(`Itemized: 50-gallon ${elecFirst ? 'gas' : 'electric'} tank replacement in ${st.name}`, elecFirst ? j.g50 : j.e50),
				running: table(`Yearly energy cost and ${years}-year cost in ${st.name} (${model.typicalGallons} gal/day, ${setF} °F)`, runCols, (elecFirst ? ['electric_tank', 'heat_pump', 'gas_tank', 'gas_tankless', 'electric_tankless'] : ['gas_tank', 'gas_tankless', 'electric_tank', 'heat_pump', 'electric_tankless']).map((id) => ({ label: model.types[id].label, uef: fmt(op.find((x) => x.id === id).uef, 2), state: fmt(yearOf(op, id)), us: fmt(yearOf(opUs, id)), own: fmt(ownOf[id] ?? own(instOf[id], yearOf(op, id))) })), { source: `EIA ${ep.electricYear} ${st.name} residential prices ($${fmt(st.usdPerKwh, 3)}/kWh, $${fmt(st.usdPerTherm, 2)}/therm); ${st.name} incoming water ${fmt(inl.annualAvg, 1)} °F; median UEF by type; installed cost from our cost model`, sourceUrl: 'https://hotwatermath.com/methodology/#state-cost-pages', sourceDate: ic.lastReviewed }),
				metros: table(`50-gallon replacement in ${st.name}'s largest metro areas (typical installed price)`, [{ key: 'name', label: 'Metro area' }, { key: 'rpp', label: `Price level (U.S. = 100, ${metros[0].year})`, num: true }, { key: 'g50', label: 'Gas tank', unit: '$', num: true }, { key: 'e50', label: 'Electric tank', unit: '$', num: true }], [...metros.map((x) => ({ name: x.name, rpp: fmt(x.rpp, 1), g50: fmt(x.g50), e50: fmt(x.e50) })), { name: `${st.name} statewide`, rpp: fmt(d['regional-cost-index'].states[k].rppAllItems, 1), g50: fmt(j.g50.typical), e50: fmt(j.e50.typical) }], { source: `BEA Regional Price Parities by metro area via FRED (${metros.map((x) => x.sourceUrl.split('/').pop()).join(', ')}); metro populations: Census Vintage 2025; prices from our cost model with the metro's price level in place of the state's`, sourceUrl: 'https://fred.stlouisfed.org/series/' + d['metro-price-parities'].metrosByState[k][0].fredSeries, sourceDate: d['metro-price-parities'].meta.sourceDate }),
				months: table(`${st.name} month by month: incoming water, tankless output and the ${model.types[mainType].label.toLowerCase()} energy bill`, [{ key: 'month', label: 'Month' }, { key: 'air', label: 'Outdoor air', unit: '°F', num: true }, { key: 'inlet', label: 'Incoming water', unit: '°F', num: true }, { key: 'gpm', label: 'Tankless flow', unit: 'GPM', num: true }, { key: 'cost', label: `${model.types[mainType].label} energy`, unit: '$', num: true }], monthRows.map(({ costNum, inletNum, ...r }) => r), { source: `Air: NOAA 1991–2020 monthly normals; incoming water: EnergyPlus mains correlation (formulas.monthlyMainsTemps); flow: ${ref.maker} ${ref.model} at the month's rise to ${setF} °F; energy: ${model.typicalGallons} gal/day, UEF ${fmt(mainUef, 2)}, EIA ${ep.electricYear} ${st.name} prices`, sourceUrl: gw.correlation.sourceUrl, sourceDate: gw.meta.sourceDate }),
				tankless: table(`What one whole-house gas tankless delivers in ${st.name} (${ref.maker} ${ref.model}, ${fmt(ref.inputBtuh)} BTU/h)`, [{ key: 'season', label: 'Season' }, { key: 'inlet', label: 'Incoming water', unit: '°F', num: true }, { key: 'rise', label: `Rise to ${setF} °F`, unit: '°F', num: true }, { key: 'gpm', label: 'Hot water flow', unit: 'GPM', num: true }, { key: 'showers', label: `${fmt(showerGpm, 1)} GPM showers at once`, num: true }], [
					{ season: 'Coldest month', inlet: fmt(inl.coldestMonth, 1), rise: fmt(riseW, 1), gpm: fmt(gW, 1), showers: Math.floor(gW / showerGpm) },
					{ season: 'Year average', inlet: fmt(inl.annualAvg, 1), rise: fmt(riseA, 1), gpm: fmt(gA, 1), showers: Math.floor(gA / showerGpm) },
					{ season: 'Warmest month', inlet: fmt(inl.warmestMonth, 1), rise: fmt(riseS, 1), gpm: fmt(gS, 1), showers: Math.floor(gS / showerGpm) },
				], { source: `Incoming water: NOAA air temperature normals with the DOE/NREL mains correlation (data/computed/inlet-temps.json). Flow = input × efficiency ÷ (${F.PHYSICS.lbPerGallon} × ${F.PHYSICS.minutesPerHour} × rise), formulas.maxGpmByRise; unit input and efficiency from ${ref.maker}'s chart`, sourceUrl: d['tankless-reference-units'].meta.sourceUrl, sourceDate: d['tankless-reference-units'].meta.sourceDate }),
				fuelMix: table(`How ${st.name} homes heat water (main fuel, share of homes)`, [{ key: 'fuel', label: 'Main water heating fuel' }, { key: 'state', label: st.name, unit: '%', num: true }, { key: 'us', label: 'U.S.', unit: '%', num: true }], [
					{ fuel: 'Electricity', state: wh.electricPct, us: usMix.electricPct }, { fuel: 'Natural gas', state: wh.gasPct, us: usMix.gasPct }, { fuel: 'Propane', state: wh.propanePct, us: usMix.propanePct }, ...(wh.fuelOilPct ? [{ fuel: 'Fuel oil or kerosene', state: wh.fuelOilPct, us: usMix.fuelOilPct }] : []),
				], { source: `${sp.references.recs.publisher}, ${sp.references.recs.title} (${fmt(wh.homesMillion, 2)} million ${st.name} homes)`, sourceUrl: sp.references.recs.pageUrl, sourceDate: sp.meta.sourceDate }),
			},
		};
		// Guard: the registry's description and quick answer quote these numbers; they must match the model.
		for (const field of ['description', 'quickAnswer']) for (const n of [fmt(j.g50.typical), fmt(j.e50.typical)]) if (!String(reg[field]).includes(`$${n}`)) throw new Error(`pages.json ${field} for ${slug} must contain $${n}; update it.`);
	}
	out['state-cost-index'] = {
		meta: { ...derived('the state cost pages'), notes: 'One row per built state page, for the parent replacement cost article (links to every state page).' },
		rows: index.map((x) => ({ ...x, g50f: fmt(x.g50), e50f: fmt(x.e50), hp50f: fmt(x.hp50), multf: fmt(x.mult, 3), main: x.electricPct > x.gasPct ? 'Electric' : 'Gas' })),
	};
}

/* ---------- Phase 6d batch 1: temperature setting, relief valve, heating element ---------- */
{
	const sf = d.safety;
	const ro = d.troubleshooting.runsOut;
	const shower = d['fixture-flow-rates'].fixtures.shower;
	const mins = ro.exampleShowerMinutes;
	const inletF = ro.winterInletHighF; // A. O. Smith's top of the winter range, same example as hot-water-runs-out-fast
	const base = d.site.defaults.setpointTempF.value;
	const et = out['water-heater-expansion-tank-size'].figures;
	const shareBase = F.hotShareOfMix(ro.showerMixF, base, inletF);
	const capRows = sf.temp.capacitySettingsF.map((t) => {
		const s = F.hotShareOfMix(ro.showerMixF, t, inletF);
		return { t, share: Math.round(s * 100), gal: F.hotGallonsDrawn(shower.gpm, mins, s), extra: Math.round(F.extraMixedWaterShare(shareBase, s) * 100) };
	});
	const at = (t) => capRows.find((x) => x.t === t);
	out['water-heater-temperature-setting'] = {
		meta: { ...derived('data/safety.json (settings compared), data/troubleshooting.json (shower temperature, inlet example), data/fixture-flow-rates.json and data/site.json'), notes: 'Tank share of a mixed shower = (shower − inlet) ÷ (tank − inlet), formulas.hotShareOfMix; tank gallons = flow × minutes × share, formulas.hotGallonsDrawn; extra mixed water = share at 120 °F ÷ share at the hotter setting − 1, formulas.extraMixedWaterShare. Only true when a mixing valve (or the user) mixes the water down to the same shower temperature. Heat loss in the pipes and in the tank is not counted, so real tanks deliver a little less in every row.' },
		figures: { base, mixF: ro.showerMixF, inletF, gpm: shower.gpm, minutes: mins, share120: at(120).share, gal120: fmt(at(120).gal, 1), share140: at(140).share, gal140: fmt(at(140).gal, 1), extra140: at(140).extra, extra130: at(130).extra, exp120: et.f120, exp140: et.f140 },
		tables: {
			capacity: table(`How far a tank stretches at each setting (${fmt(shower.gpm, 1)} GPM shower at ${ro.showerMixF} °F, ${inletF} °F incoming water, ${mins} minutes)`, [{ key: 't', label: 'Tank setting', unit: '°F', num: true }, { key: 'share', label: 'Share of the shower from the tank', unit: '%', num: true }, { key: 'gal', label: 'Tank water used', unit: 'gal', num: true }, { key: 'extra', label: `Mixed water vs ${base} °F`, num: true }], capRows.map((x) => ({ t: fmt(x.t), share: fmt(x.share), gal: fmt(x.gal, 1), extra: x.t === base ? 'Baseline' : `+${x.extra}%` })), { source: 'Computed with formulas.hotShareOfMix and hotGallonsDrawn; shower temperature from Stiebel Eltron\'s sizing sheet, winter inlet from A. O. Smith, shower flow is the federal maximum. Needs a mixing valve to hold the shower at the same temperature.', sourceUrl: 'https://hotwatermath.com/methodology/#safety-pages', sourceDate: sf.meta.sourceDate }),
		},
	};
	const tp = sf.tp;
	out['water-heater-pressure-relief-valve-leaking'] = {
		meta: { ...derived('data/safety.json (relief valve figures) and data/computed/water-heater-expansion-tank-size.json'), notes: 'Highest normal operating pressure = valve set pressure × Watts\' 75% limit. Expansion = AMTROL factor for a 50-gallon tank heated from 40 °F (expansion tank calculator).' },
		figures: { maxOperatingPsi: fmt(tp.commonSetPsi * tp.maxOperatingShare, 1), sharePct: fmt(tp.maxOperatingShare * 100), heater: et.heater, exp120: et.f120, exp140: et.f140, exp120Gal: et.d0.expanded, tank: et.d0.tank },
	};
	const el = sf.element;
	const elRows = el.ratings.map((x) => ({ volts: x.volts, watts: x.watts, ohms: F.elementResistanceOhms(x.volts, x.watts), amps: F.electricAmps(x.watts / 1000, x.volts) }));
	const e4500 = elRows.find((x) => x.volts === 240 && x.watts === 4500);
	out['water-heater-element-replacement'] = {
		meta: { ...derived('data/safety.json (element ratings)'), notes: 'Resistance = volts² ÷ watts, formulas.elementResistanceOhms; current = watts ÷ volts, formulas.electricAmps. These are the cold readings of a good element at its rated voltage; A. O. Smith\'s own pass range is wider (5–25 ohms) to cover tolerance and other ratings.' },
		figures: { ohms4500: fmt(e4500.ohms, 1), amps4500: fmt(e4500.amps, 1), minOhms: fmt(Math.min(...elRows.map((x) => x.ohms)), 1), maxOhms: fmt(Math.max(...elRows.map((x) => x.ohms)), 1) },
		tables: {
			ohms: table('What a good heating element should read (cold, power off)', [{ key: 'rating', label: 'Element rating' }, { key: 'ohms', label: 'Resistance', unit: 'Ω', num: true }, { key: 'amps', label: 'Current when on', unit: 'A', num: true }], elRows.map((x) => ({ rating: `${fmt(x.watts)} W, ${x.volts} V`, ohms: fmt(x.ohms, 1), amps: fmt(x.amps, 1) })), { source: `Ohm's law: resistance = volts² ÷ watts, current = watts ÷ volts (formulas.elementResistanceOhms, electricAmps). ${el.ratingsSource}. A. O. Smith's pass range: ${el.ohmsLow}–${el.ohmsHigh} ohms.`, sourceUrl: 'https://hotwatermath.com/methodology/#safety-pages', sourceDate: sf.meta.sourceDate }),
		},
	};
	// Guard: the element page's description quotes the 4,500 W resistance and the rounded part price range.
	{
		const pg = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8')).pages.find((x) => x.slug === 'water-heater-element-replacement');
		const want = [`${fmt(e4500.ohms, 1)} ohms`, `$${Math.round(Number(el.priceLow))} to $${Math.round(Number(el.priceHigh))}`];
		for (const w of want) if (pg && pg.status !== 'planned' && !String(pg.description).includes(w)) throw new Error(`pages.json description for water-heater-element-replacement must contain "${w}"; update it.`);
	}
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
