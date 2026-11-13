/**
 * Re-download the machine-readable primary sources and regenerate the data files built from them.
 * Run quarterly (energy prices) or yearly (the rest), then `npm run build:data` and commit (see /methodology).
 *
 *   npm run fetch:sources              # all
 *   npm run fetch:sources -- eia noaa  # only some: eia | noaa | rpp | energystar
 *
 * Behind an HTTP proxy, run with NODE_USE_ENV_PROXY=1 so Node's fetch uses HTTPS_PROXY.
 *
 * Writes:
 *   data/energy-prices.json             EIA residential electricity (Electric Power Annual table 2.10) and natural gas
 *                                       (price + heat content) by state; also data/site.json → defaults (US averages)
 *   data/groundwater-temp-by-state.json NOAA 1991–2020 monthly air temperature normals per state (inputs to the mains
 *                                       water temperature correlation in formulas.js)
 *   data/regional-cost-index.json       BEA regional price parities (all items) by state, via FRED
 *   data/energystar-water-heaters.json  ENERGY STAR certified water heater list, summarized (heat pump FHR by size, UEF)
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { STATES } from './lib/states.mjs';
import { gasUsdPerTherm, usdPerKwh, summarize } from '../assets/js/formulas.js';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const today = new Date().toISOString().slice(0, 10);
const UA = 'HotWaterMath-data/1.0 (+https://hotwatermath.com/methodology/)';
const only = process.argv.slice(2);
const want = (k) => !only.length || only.includes(k);
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function get(url, tries = 4) {
	for (let i = 1; i <= tries; i++) {
		try {
			const res = await fetch(url, { headers: { 'User-Agent': UA } });
			if (res.ok) return await res.text();
			if (i === tries) throw new Error(`HTTP ${res.status} for ${url}`);
		} catch (e) {
			if (i === tries) throw e;
		}
		await sleep(1500 * i);
	}
	throw new Error(`unreachable ${url}`);
}

const writeJson = (file, obj) => {
	writeFileSync(join(root, 'data', file), JSON.stringify(obj, null, 2) + '\n');
	console.log(`wrote data/${file}`);
};
const readJson = (file) => JSON.parse(readFileSync(join(root, 'data', file), 'utf8'));
const strip = (s) => s.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/\s+/g, ' ').trim();
const byName = Object.fromEntries(STATES.map((s) => [s.name, s]));
const r4 = (v) => Math.round(v * 10000) / 10000;
const r1 = (v) => Math.round(v * 10) / 10;

/* ---------- EIA ---------- */

const EIA_ELEC = 'https://www.eia.gov/electricity/annual/html/epa_02_10.html';
const EIA_GAS_PRICE = 'https://www.eia.gov/dnav/ng/ng_pri_sum_a_EPG0_PRS_DMcf_a.htm';
const EIA_GAS_HEAT = 'https://www.eia.gov/dnav/ng/ng_cons_heat_a_EPG0_VGTH_btucf_a.htm';

/** EIA Electric Power Annual table 2.10: first numeric column is residential for the latest year. */
function parseElectric(html) {
	const year = Number((html.match(/by State, (\d{4}) and \d{4}/) || [])[1]);
	const rows = {};
	for (const tr of html.match(/<tr[\s\S]*?<\/tr>/gi) || []) {
		const cells = (tr.match(/<t[dh][\s\S]*?<\/t[dh]>/gi) || []).map(strip);
		if (cells.length < 3) continue;
		const v = parseFloat(cells[1]);
		if (Number.isFinite(v)) rows[cells[0]] = v;
	}
	return { year, rows };
}

/** EIA natural gas "dnav" annual tables: row label + one value per year column; uses the latest year that has data. */
function parseDnav(html) {
	const years = [...html.matchAll(/class="Series5">(\d{4})</g)].map((m) => Number(m[1]));
	const rows = {};
	const re = /class="DataStub1">(?:<B>)?([^<]+)(?:<B>)?<\/td>[\s\S]*?<\/table>\s*<\/td>([\s\S]*?)<\/tr>/g;
	for (const m of html.matchAll(re)) {
		const vals = [...m[2].matchAll(/class="(?:DataB|Current2)">([^<]*)</g)].map((x) => parseFloat(x[1].replace(/,/g, '')));
		let idx = -1;
		for (let i = vals.length - 1; i >= 0; i--) if (Number.isFinite(vals[i])) { idx = i; break; }
		if (idx >= 0) rows[m[1].trim()] = { value: vals[idx], year: years[idx] };
	}
	return { latestYear: years[years.length - 1], rows };
}

async function eia() {
	const elec = parseElectric(await get(EIA_ELEC));
	const price = parseDnav(await get(EIA_GAS_PRICE));
	const heat = parseDnav(await get(EIA_GAS_HEAT));
	const states = {};
	for (const s of STATES) {
		const e = elec.rows[s.name];
		const p = price.rows[s.name];
		const h = heat.rows[s.name];
		if (!Number.isFinite(e) || !p || !h) throw new Error(`EIA: missing data for ${s.name}`);
		states[s.code] = {
			name: s.name,
			electricCentsPerKwh: e,
			electricYear: elec.year,
			gasUsdPerMcf: p.value,
			gasYear: p.year,
			gasBtuPerCf: h.value,
			gasHeatYear: h.year,
		};
	}
	const us = {
		electricCentsPerKwh: elec.rows['U.S. Total'],
		electricYear: elec.year,
		gasUsdPerMcf: price.rows['U.S.'].value,
		gasYear: price.rows['U.S.'].year,
		gasBtuPerCf: heat.rows['U.S.'].value,
		gasHeatYear: heat.rows['U.S.'].year,
	};
	const withheld = Object.values(states).filter((s) => s.gasYear !== price.latestYear).map((s) => `${s.name} (${s.gasYear})`);
	writeJson('energy-prices.json', {
		meta: {
			source: 'U.S. Energy Information Administration (EIA): Electric Power Annual table 2.10 (residential electricity price by state); Natural Gas Annual residential price and heat content of delivered gas by state',
			sourceUrl: EIA_ELEC,
			sourceDate: today,
			notes: `Annual averages. Electricity: residential, cents per kWh, ${elec.year}. Natural gas: residential, dollars per thousand cubic feet (Mcf), latest year with data (${price.latestYear}); EIA withholds some states' latest value, and for those the previous year is used: ${withheld.join(', ') || 'none'}. Dollars per therm = $/Mcf ÷ (BTU per cubic foot × 1,000 ÷ 100,000), computed by formulas.gasUsdPerTherm in npm run build:data. Generated by scripts/fetch-sources.mjs; do not edit by hand.`,
		},
		sources: {
			electricity: { source: `EIA, Electric Power Annual, Table 2.10, residential, ${elec.year}`, sourceUrl: EIA_ELEC },
			gasPrice: { source: 'EIA, Natural Gas Prices, Average Residential Price by state (annual)', sourceUrl: EIA_GAS_PRICE },
			gasHeatContent: { source: 'EIA, Heat Content of Natural Gas Delivered to Consumers by state (annual)', sourceUrl: EIA_GAS_HEAT },
		},
		us,
		states,
	});

	const site = readJson('site.json');
	site.defaults.defaultElectricRateUsdPerKwh = {
		value: r4(usdPerKwh(us.electricCentsPerKwh)),
		unit: 'USD/kWh',
		source: `EIA Electric Power Annual, Table 2.10: U.S. average residential electricity price, ${us.electricYear} (${us.electricCentsPerKwh} cents/kWh)`,
		sourceUrl: EIA_ELEC,
		sourceDate: today,
	};
	site.defaults.defaultGasRateUsdPerTherm = {
		value: Math.round(gasUsdPerTherm(us.gasUsdPerMcf, us.gasBtuPerCf) * 1000) / 1000,
		unit: 'USD/therm',
		source: `EIA: U.S. average residential natural gas price, ${us.gasYear} ($${us.gasUsdPerMcf}/Mcf), converted with the U.S. heat content of delivered gas, ${us.gasHeatYear} (${us.gasBtuPerCf} BTU/cf)`,
		sourceUrl: EIA_GAS_PRICE,
		sourceDate: today,
	};
	writeJson('site.json', site);
}

/* ---------- NOAA ---------- */

const NOAA_BASE = 'https://www.ncei.noaa.gov/access/monitoring/climate-at-a-glance';

async function noaa() {
	const states = {};
	for (const s of STATES) {
		const [kind, id] = s.noaa.split('/');
		const page = `${NOAA_BASE}/${kind}/time-series/${id}/tavg/1/0/1991-2020`;
		const url = `${page}/data.csv`;
		const csv = await get(url);
		const title = (csv.match(/^#\s*(?:&quot;|")?(.*?)(?:&quot;|")?\s*$/m) || [])[1] || '';
		const sums = Array(12).fill(0);
		const counts = Array(12).fill(0);
		for (const line of csv.split('\n')) {
			const m = line.match(/^(\d{4})(\d{2}),(-?\d+(?:\.\d+)?)/);
			if (!m) continue;
			const i = Number(m[2]) - 1;
			sums[i] += Number(m[3]);
			counts[i]++;
		}
		if (counts.some((c) => c < 25)) throw new Error(`NOAA: incomplete monthly data for ${s.name} (${url})`);
		const monthly = sums.map((v, i) => r1(v / counts[i]));
		const annual = r1(monthly.reduce((a, b) => a + b, 0) / 12);
		states[s.code] = {
			name: s.name,
			series: title.replace(/ Average Temperature$/, ''),
			seriesType: kind,
			sourceUrl: page,
			annualAvgAirF: annual,
			maxMonthlyDiffF: r1(Math.max(...monthly) - Math.min(...monthly)),
			monthlyNormalsF: monthly,
		};
		await sleep(250);
	}
	writeJson('groundwater-temp-by-state.json', {
		meta: {
			source: 'NOAA National Centers for Environmental Information, Climate at a Glance: monthly average air temperature, 1991–2020; mains water correlation by Christensen and Burch (Hendron et al. 2004) as published in the EnergyPlus Engineering Reference',
			sourceUrl: `${NOAA_BASE}/statewide/time-series/`,
			sourceDate: today,
			notes: 'Incoming (mains) water temperature is not measured state by state, so it is estimated from air temperature with the correlation used by DOE building energy software: annual average = air average + offsetF; the coldest month is lower by ratio × (maxMonthlyDiffF ÷ 2), ratio = ratioBase + ratioSlope × (air average − refTempF). The math is in formulas.mainsWaterTemp; results are in data/computed/inlet-temps.json. Statewide averages are used except Alaska (Anchorage), Hawaii (Honolulu) and DC (Washington Reagan National): NOAA has no statewide series for Hawaii or DC, and the Alaska statewide average is dominated by the unpopulated interior. Values are averages for planning; a measured inlet temperature always wins and the calculator lets you enter it. Generated by scripts/fetch-sources.mjs; do not edit by hand.',
		},
		correlation: {
			source: 'EnergyPlus Engineering Reference, Water Systems: Water Mains Temperatures (correlation by C. Christensen and J. Burch, described in Hendron et al. 2004)',
			sourceUrl: 'https://bigladdersoftware.com/epx/docs/9-6/engineering-reference/water-systems.html',
			offsetF: 6,
			ratioBase: 0.4,
			ratioSlope: 0.01,
			refTempF: 44,
			lagBaseDays: 35,
			lagSlopeDaysPerF: 1,
			degPerDay: 0.986,
			dayOffset: 15,
			phaseDeg: 90,
		},
		states,
	});
}

/* ---------- BEA regional price parities (via FRED) ---------- */

async function rpp() {
	const states = {};
	let year = null;
	for (const s of STATES) {
		const id = `${s.code}RPPALL`;
		const csv = await get(`https://fred.stlouisfed.org/graph/fredgraph.csv?id=${id}`);
		const rows = csv.trim().split('\n').slice(1).map((l) => l.split(',')).filter((r) => Number.isFinite(parseFloat(r[1])));
		const last = rows[rows.length - 1];
		if (!last) throw new Error(`FRED: no data for ${id}`);
		const y = Number(last[0].slice(0, 4));
		year = year ?? y;
		if (y !== year) throw new Error(`FRED: ${id} latest year ${y} ≠ ${year}`);
		states[s.code] = { name: s.name, rppAllItems: Math.round(parseFloat(last[1]) * 10) / 10, year: y, fredSeries: id };
		await sleep(400);
	}
	writeJson('regional-cost-index.json', {
		meta: {
			source: `U.S. Bureau of Economic Analysis (BEA), Regional Price Parities by State, all items, ${year} (released February 2026), read from FRED (Federal Reserve Bank of St. Louis)`,
			sourceUrl: 'https://www.bea.gov/sites/default/files/2026-02/rpp0226.pdf',
			sourceDate: today,
			notes: 'RPP = 100 is the U.S. average price level. The installed-cost calculator multiplies labor and extras (not the equipment, which is priced nationally) by RPP ÷ 100. RPPs measure overall consumer prices, including rents, so they are a proxy for local labor costs, not a plumber wage survey; DOE uses city-level RSMeans labor rates for the same purpose, which are not public. Generated by scripts/fetch-sources.mjs; do not edit by hand.',
		},
		sources: {
			fred: { source: 'FRED series <STATE>RPPALL (BEA data)', sourceUrl: 'https://fred.stlouisfed.org/series/AZRPPALL' },
		},
		states,
	});
}

/* ---------- ENERGY STAR certified water heaters ---------- */

const ES_API = 'https://data.energystar.gov/resource/pbpq-swnu.json?$limit=50000';
/** Model-number prefixes that state the nominal tank size, per manufacturer naming (checked against the listings). */
const HP_NOMINAL_RULES = [
	{ brand: 'Rheem / Ruud / Richmond', regex: '^XE(40|50|65|80)T10H' },
	{ brand: 'A. O. Smith / U.S. Craftmaster', regex: '^HPTU-(50|66|80)' },
	{ brand: 'A. O. Smith', regex: '^HPS10-(50|66|80)' },
	{ brand: 'Bradford White / Jetglas', regex: '^RE2H(50|65|80)' },
	{ brand: 'Ruud', regex: '^PROPH(40|50|65|80)' },
];

async function energystar() {
	const rows = JSON.parse(await get(ES_API));
	const num = (v) => (v === undefined || v === null || v === '' ? NaN : Number(v));
	const hp = rows.filter((r) => r.type === 'Hybrid/Electric Heat Pump');
	const byNominal = {};
	for (const r of hp) {
		for (const rule of HP_NOMINAL_RULES) {
			const m = String(r.model_number || '').toUpperCase().match(new RegExp(rule.regex));
			if (!m) continue;
			let n = Number(m[1]);
			if (n === 66) n = 65; // A. O. Smith sells its 65-gallon class as "66"
			(byNominal[n] ||= []).push(num(r.first_hour_rating_gallons));
			break;
		}
	}
	const heatPumpFhr = Object.entries(byNominal)
		.map(([n, v]) => ({ nominalGallons: Number(n), ...summarize(v) }))
		.sort((a, b) => a.nominalGallons - b.nominalGallons);
	const uef = (list) => summarize(list.map((r) => num(r.uniform_energy_factor_uef)));
	const gasTankless = rows.filter((r) => r.type === 'Gas Tankless');
	writeJson('energystar-water-heaters.json', {
		meta: {
			source: 'ENERGY STAR Certified Water Heaters dataset (U.S. EPA), Version 5.0 program requirements',
			sourceUrl: 'https://data.energystar.gov/Active-Specifications/ENERGY-STAR-Certified-Water-Heaters/pbpq-swnu',
			sourceDate: today,
			notes: `Summary of ${rows.length} certified models. Heat pump first hour ratings by nominal size use only models whose model number states the nominal size (rules below); "typical" is the median. UEF summaries are low / median / high across all certified models of the type. ENERGY STAR lists only efficient models, so these are not the whole market (standard gas and electric tanks come from manufacturer spec sheets in data/fhr-to-tank-size.json and data/uef-ranges.json). Generated by scripts/fetch-sources.mjs; do not edit by hand.`,
		},
		nominalSizeRules: HP_NOMINAL_RULES,
		counts: {
			all: rows.length,
			heatPump: hp.length,
			gasTankless: gasTankless.length,
			gasStorage: rows.filter((r) => r.type === 'Gas Storage').length,
		},
		heatPumpFhrByNominal: heatPumpFhr,
		uef: {
			heatPump240V: uef(hp.filter((r) => r.heat_pump_type === '240 Volt Integrated HPWH')),
			heatPump120V: uef(hp.filter((r) => r.heat_pump_type === '120 Volt Integrated HPWH')),
			gasTankless: uef(gasTankless),
			gasStorage: uef(rows.filter((r) => r.type === 'Gas Storage')),
		},
		gasTankless: {
			// ENERGY STAR "Max GPM" is the rated maximum flow over a 67 °F rise (DOE test). The dataset's input-rate
			// column mixes units between records, so it is not summarized.
			maxGpmAt67FRise: summarize(gasTankless.map((r) => num(r.maximum_gallons_per_minute))),
		},
	});
}

const tasks = { eia, noaa, rpp, energystar };
for (const [name, fn] of Object.entries(tasks)) {
	if (!want(name)) continue;
	console.log(`fetch:sources ${name}…`);
	await fn();
}
