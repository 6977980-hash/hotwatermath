/**
 * tank-size.js: tank water heater size calculator (STRATEGY §3.3). Math is in formulas.js; data comes from the page
 * (<script type="application/json" id="hwm-tank-data">, printed by PHP from data/computed/water-heater-size-calculator.json).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result, so the text is written once.
 */
import { tankSizing, hotShareOfMix } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber, checkLimits, invalidResult } from './ui.js';

const n0 = (v) => formatNumber(v, 0);
export const ACTIVITY_KEYS = ['shower', 'bath', 'shaving', 'handsFace', 'shampoo', 'handDishwashing', 'dishwasher', 'foodPrep', 'clothesWasher'];
const FUELS = ['gas', 'electric', 'heatpump'];
const FUEL_LABEL = { gas: 'gas', electric: 'electric', heatpump: 'heat pump' };

/** One sentence for one fuel's recommendation. */
function fuelSentence(fuel, pick, fhr, cfg) {
	const name = FUEL_LABEL[fuel];
	if (!pick) return '';
	const fmtRow = (row) => `typical first hour rating ${n0(row.fhrTypical)} gal`;
	if (pick.fits === 'typical') return `${n0(pick.nominalGallons)}-gallon ${name} (${fmtRow(pick.row)})`;
	if (pick.fits === 'some-models') return `${n0(pick.nominalGallons)}-gallon ${name}, but only the stronger models (up to ${n0(pick.row.fhrHigh)} gal; check the label)`;
	if (fuel === 'electric') return `no standard electric tank (the largest, ${n0(pick.nominalGallons)} gallons, rates about ${n0(pick.row.fhrTypical)} gal; bigger electric tanks must be heat pumps under federal rules)`;
	return `no single ${name} tank in our sample reaches ${n0(fhr)} gal (the ${n0(pick.nominalGallons)}-gallon model rates up to ${n0(pick.row.fhrHigh)} gal)`;
}

/**
 * @param {object} v Form values: people, fuel, margin, and one count per activity key.
 * @param {object} cfg Client data (computed → client).
 * @returns {{status:(string|undefined), statusLabel:string, headline:string, explain:string, formula:string, sizing?:object}}
 */
export function describe(v, cfg) {
	const fuel = FUELS.includes(v.fuel) ? v.fuel : 'gas';
	const { count, ...lim } = cfg.limits || {};
	const bad = checkLimits(v, { ...lim, ...(count ? Object.fromEntries(ACTIVITY_KEYS.map((k) => [k, { ...count, label: cfg.activities[k].label }])) : {}) });
	if (bad.length) return invalidResult(bad);
	// Custom shower head (S-3): AHRI's gallons per shower scaled by your flow ÷ the 2.5 GPM it is read at.
	const showerGpm = Number.isFinite(v.showerGpm) ? v.showerGpm : null;
	const gallonsOf = (k) => (k === 'shower' && showerGpm !== null ? Math.round(((cfg.activities.shower.gallons * showerGpm) / cfg.showerFlow.baseGpm) * 10) / 10 : cfg.activities[k].gallons);
	const activities = ACTIVITY_KEYS.map((k) => ({ key: k, count: Math.max(0, Math.round(Number(v[k]) || 0)), gallons: gallonsOf(k) }));
	// Large soaking tub (S-3): its fill in gallons × the tank share of a 104 °F bath.
	const tub = Number.isFinite(v.tubGallons) && v.tubGallons > 0 ? v.tubGallons : 0;
	const tubShare = hotShareOfMix(cfg.largeTub.useTempF, cfg.setpointF, cfg.inletF);
	if (tub) activities.push({ key: 'largeTub', count: 1, gallons: Math.round(tub * tubShare * 10) / 10 });
	if (!activities.some((a) => a.count > 0)) {
		return { statusLabel: 'Add your busiest hour', headline: 'Count the hot water uses in your busiest hour', explain: 'Pick your household size to fill in a typical busy hour, or set how many showers, loads and other uses happen in that hour.', formula: '' };
	}
	const margin = v.margin ? cfg.margin : 0;
	const s = tankSizing(activities, margin, cfg.fhrRatio, cfg.fhrTable);
	const pick = s[fuel];
	const others = FUELS.filter((f) => f !== fuel).map((f) => fuelSentence(f, s[f], s.fhr, cfg)).filter(Boolean);
	const status = pick.fits === 'typical' ? 'ok' : 'warn';
	let statusLabel;
	let headline;
	if (pick.fits === 'none') {
		statusLabel = fuel === 'electric' ? 'Too much for an electric tank' : 'Bigger than one standard tank';
		headline = `Look for a first hour rating of ${n0(s.fhr)} gallons or more`;
	} else {
		statusLabel = pick.fits === 'typical' ? 'Most models of this size meet it' : 'Only some models meet it';
		headline = `${n0(pick.nominalGallons)}-gallon ${FUEL_LABEL[fuel]} tank (first hour rating ${n0(s.fhr)}+ gal)`;
	}
	const people = Number.parseInt(v.people, 10);
	const guide = cfg.guide.rows.find((r) => r.people === people) || null;
	const sentences = [`Your busiest hour uses about ${n0(s.phd)} gallons of hot water${margin ? `, plus a ${n0(margin * 100)}% margin` : ''}, so choose a heater with a first hour rating (FHR) of at least ${n0(s.fhr)} gallons.`];
	if (pick.fits === 'none') sentences.push(`For ${FUEL_LABEL[fuel]}: ${fuelSentence(fuel, pick, s.fhr, cfg)}. Consider fewer uses in that hour, a tankless unit or two heaters.`);
	sentences.push(`Other fuels: ${others.join('; ')}.`);
	if (guide) {
		const inside = s.phd >= guide.low && s.phd <= guide.high;
		sentences.push(`${cfg.guide.label} suggests an FHR of ${guide.low}–${guide.high} gal for ${people} people; your busy hour is ${inside ? 'inside' : s.phd < guide.low ? 'below' : 'above'} that range.`);
	}
	const used = activities.filter((a) => a.count > 0);
	const labelOf = (k) => (k === 'largeTub' ? 'large tub' : cfg.activities[k].label.toLowerCase());
	const formula = [
		showerGpm !== null ? `Shower = ${cfg.activities.shower.gallons} gal × ${showerGpm} GPM ÷ ${cfg.showerFlow.baseGpm} GPM = ${gallonsOf('shower')} gal hot water` : '',
		tub ? `Large tub = ${n0(tub)} gal fill × (${cfg.largeTub.useTempF} − ${cfg.inletF}) ÷ (${cfg.setpointF} − ${cfg.inletF}) = ${formatNumber(tub * tubShare, 1)} gal hot water` : '',
		`Peak-hour demand = ${used.map((a) => `${a.count} × ${a.gallons} gal (${labelOf(a.key)})`).join(' + ')} = ${n0(s.phd)} gal`,
		`Required FHR = ${n0(s.phd)} × ${cfg.fhrRatio}${margin ? ` × ${1 + margin}` : ''} = ${n0(s.fhr)} gal (rounded up)`,
		...FUELS.map((f) => {
			const p = s[f];
			const row = p.row;
			return `${FUEL_LABEL[f][0].toUpperCase()}${FUEL_LABEL[f].slice(1)}: ${p.fits === 'none' ? 'no size meets it' : `${row.nominalGallons} gal`}; that size rates ${n0(row.fhrLow)}–${n0(row.fhrHigh)} gal, typical ${n0(row.fhrTypical)} (${row.n} model${row.n === 1 ? '' : 's'})`;
		}),
	].filter(Boolean).join('\n');
	return { status, statusLabel, headline, explain: sentences.join(' '), formula, sizing: s };
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="water-heater-size-calculator"]');
	const cfgEl = document.getElementById('hwm-tank-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const form = root.querySelector('form');
		const peopleEl = form.elements.namedItem('people');
		let last = null;
		peopleEl?.addEventListener('change', () => {
			const preset = cfg.presets[peopleEl.value];
			if (!preset) return;
			for (const k of ACTIVITY_KEYS) {
				const el = form.elements.namedItem(k);
				if (el) el.value = String(preset[k] || 0);
			}
		});
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			// Deep-link the cost calculator to the recommended type and size (it reads ?type=&size=).
			const costLink = document.querySelector('.next-steps a[href*="/water-heater-cost-calculator/"]');
			const pick = last.sizing && last.sizing[values.fuel];
			if (costLink && pick) {
				const u = new URL(costLink.href);
				u.search = '';
				u.searchParams.set('type', { gas: 'gas_tank', electric: 'electric_tank', heatpump: 'heat_pump' }[values.fuel] || 'gas_tank');
				u.searchParams.set('size', String(pick.nominalGallons));
				costLink.href = u.toString();
			}
			if (record) {
				writeState(values);
				if (last.sizing) track('calc_result', { tool: 'water-heater-size-calculator', fuel: values.fuel, people: values.people || 'none', phd_bucket: last.sizing.phd < 50 ? '<50' : last.sizing.phd < 70 ? '50-70' : last.sizing.phd < 90 ? '70-90' : '90+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const fromUrl = readState(form);
		if (fromUrl) {
			const params = new URLSearchParams(window.location.search);
			for (const el of form.querySelectorAll('input[type="checkbox"]')) if (!params.has(el.name)) el.checked = false;
		}
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'water-heater-size-calculator', referrer: document.referrer });
	}
}
