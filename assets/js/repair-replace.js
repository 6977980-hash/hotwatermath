/**
 * repair-replace.js: repair or replace decision tool (STRATEGY §3.4). Math is in formulas.js; data comes from the page
 * (<script type="application/json" id="hwm-repair-data">, printed by PHP from data/computed/repair-or-replace-water-heater.json).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result, so the text is written once.
 * Accepts ?age=&type= from the age calculator.
 */
import { repairOrReplace, yearlyEnergyCost, yearlySaving } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber, formatCurrency } from './ui.js';

const one = (v) => formatNumber(v, 1);
const pct = (v) => `${formatNumber(v * 100, 0)}%`;
const usd = (v) => formatCurrency(v);

/**
 * @param {object} v Form values: age, type, repair, replace, kwh, therm, and one checkbox per symptom id.
 * @param {object} cfg Client data (computed → client).
 * @returns {{status:(string|undefined), statusLabel:string, headline:string, explain:string, formula:string, decision?:object}}
 */
export function describe(v, cfg) {
	const type = cfg.types[v.type] ? v.type : cfg.defaultInputs.type;
	const t = cfg.types[type];
	const age = Number.isFinite(v.age) && v.age >= 0 ? v.age : null;
	const repair = Number.isFinite(v.repair) && v.repair >= 0 ? v.repair : null;
	const replace = Number.isFinite(v.replace) && v.replace > 0 ? v.replace : t.replacementUsd;
	const has = (id) => Boolean(v[id]);
	if (age === null) {
		return { statusLabel: 'Add the age', headline: 'Enter how old the water heater is', explain: 'Use the age calculator if you do not know it: it reads the build date from the serial number.', formula: '' };
	}
	if (repair === null && !has('tankLeak') && !has('flooded')) {
		return { statusLabel: 'Add the repair quote', headline: 'Enter the repair quote in dollars', explain: 'Use the total the repair will cost you, parts and labor.', formula: '' };
	}
	const d = repairOrReplace({ ageYears: age, expectedLife: t.life, repairCost: repair ?? 0, replacementCost: replace, tankLeaking: has('tankLeak'), flooded: has('flooded') }, cfg.rules);
	const why = {
		'tank-leak': 'A leak from the tank itself cannot be repaired.',
		flooded: 'A flooded water heater must be replaced, the manufacturer says.',
		'repair-over-always-share': `The repair is ${pct(d.share)} of the replacement price, more than the ${pct(cfg.rules.repairShareAlways)} limit.`,
		old: `At ${one(age)} years it has used ${pct(d.lifeUsed)} of a ${t.life}-year typical life (${pct(cfg.rules.ageShare)} or more counts as old).`,
		young: `At ${one(age)} years it has used ${pct(d.lifeUsed)} of a ${t.life}-year typical life.`,
		'repair-over-old-share': `The repair is ${pct(d.share)} of the replacement price, more than ${pct(cfg.rules.repairShareWhenOld)}.`,
		'repair-cheap': `The repair is ${pct(d.share)} of the replacement price.`,
	};
	const labels = { replace: 'Replace', borderline: 'Borderline', repair: 'Repair' };
	const heads = {
		replace: 'Replace it rather than pay for this repair',
		borderline: d.reasons.includes('old') ? 'Repair is cheap, but the heater is near the end of its life' : 'Repair is possible, but the quote is high for a young heater',
		repair: 'Repair it: the quote is small and the heater is young',
	};
	const sentences = d.reasons.map((r) => why[r]);
	if (d.verdict === 'borderline') sentences.push(d.reasons.includes('old') ? 'Paying for the repair is reasonable if you also start planning a replacement.' : 'Get a second quote, or ask what else is worn before you decide.');
	for (const s of cfg.symptoms) if (has(s.id) && (s.effect === 'warn' || s.effect === 'safety')) sentences.push(s.note);

	// Energy: running cost of a like-for-like new unit vs the upgrade type, at the DOE test-day use.
	const prices = { usdPerKwh: Number.isFinite(v.kwh) && v.kwh > 0 ? v.kwh : cfg.prices.usdPerKwh, usdPerTherm: Number.isFinite(v.therm) && v.therm > 0 ? v.therm : cfg.prices.usdPerTherm };
	const up = cfg.types[type].upgrade;
	let energyLine = '';
	if (up && up.id !== t.uefId) {
		const now = yearlyEnergyCost(cfg.use.gallonsPerDay, cfg.use.riseF, t.fuel, t.uef, prices);
		const next = yearlyEnergyCost(cfg.use.gallonsPerDay, cfg.use.riseF, up.fuel, up.uef, prices);
		const save = yearlySaving(now, next);
		if (d.verdict !== 'repair' && save > 0) sentences.push(`If you replace it, a ${up.short} (UEF ${up.uef}) would cost about ${usd(save)} a year less to run than a standard ${t.label.toLowerCase()}.`);
		energyLine = `Running cost (${cfg.use.gallonsPerDay} gal/day, ${cfg.use.riseF} °F rise): ${t.label} UEF ${t.uef} = ${usd(now.year)}/yr; ${up.label} UEF ${up.uef} = ${usd(next.year)}/yr; difference ${usd(save)}/yr`;
	}
	const formula = [
		`Expected life (${t.label.toLowerCase()}) = ${t.life} years; "old" from ${cfg.rules.ageShare} × ${t.life} = ${one(d.oldFromYears)} years`,
		`Life used = ${one(age)} ÷ ${t.life} = ${pct(d.lifeUsed)}`,
		repair !== null ? `Repair share = ${usd(repair)} ÷ ${usd(replace)} = ${pct(d.share)}` : '',
		`Rules: leak or flood → replace; share > ${pct(cfg.rules.repairShareAlways)} → replace; old and share > ${pct(cfg.rules.repairShareWhenOld)} → replace; old → borderline; share > ${pct(cfg.rules.repairShareWhenOld)} → borderline; else repair`,
		energyLine,
	].filter(Boolean).join('\n');
	const status = { replace: 'bad', borderline: 'warn', repair: 'ok' }[d.verdict];
	return { status, statusLabel: labels[d.verdict], headline: heads[d.verdict], explain: sentences.join(' '), formula, decision: d };
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="repair-or-replace-water-heater"]');
	const cfgEl = document.getElementById('hwm-repair-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const form = root.querySelector('form');
		const typeEl = form.elements.namedItem('type');
		const replaceEl = form.elements.namedItem('replace');
		let last = null;
		typeEl?.addEventListener('change', () => {
			const t = cfg.types[typeEl.value];
			if (t && replaceEl && !replaceEl.dataset.edited) replaceEl.value = String(t.replacementUsd);
		});
		replaceEl?.addEventListener('input', () => { replaceEl.dataset.edited = '1'; });
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				if (last.decision) track('calc_result', { tool: 'repair-or-replace-water-heater', unit_type: values.type, verdict: last.decision.verdict, age_bucket: values.age < 5 ? '0-5' : values.age < 10 ? '5-10' : values.age < 15 ? '10-15' : '15+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const params = new URLSearchParams(window.location.search);
		// From the age calculator: ?age=&type= (type ids are the same as the age calculator's).
		if (params.has('type') && typeEl && cfg.types[params.get('type')] && replaceEl && !params.has('replace')) replaceEl.value = String(cfg.types[params.get('type')].replacementUsd);
		const fromUrl = readState(form);
		if (fromUrl) {
			for (const el of form.querySelectorAll('input[type="checkbox"]')) if (!params.has(el.name)) el.checked = false;
			if (params.has('replace') && replaceEl) replaceEl.dataset.edited = '1';
		}
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'repair-or-replace-water-heater', referrer: document.referrer });
	}
}
