/**
 * repair-replace.js: repair or replace decision tool (STRATEGY §3.4). Math is in formulas.js; data comes from the page
 * (<script type="application/json" id="hwm-repair-data">, printed by PHP from data/computed/repair-or-replace-water-heater.json).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result, so the text is written once.
 * Accepts ?age=&type= from the age calculator.
 */
import { repairOrReplace, yearlyEnergyCost, yearlySaving } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber, formatCurrency, checkLimits, invalidResult } from './ui.js';

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
	const has = (id) => Boolean(v[id]);
	// Empty age or repair quote get their own prompts below; anything typed that is out of range or not a number is
	// flagged, never replaced by a default (audit F-02).
	const bad = checkLimits(v, cfg.limits, ['propane', ...(v.age === null ? ['age'] : []), ...(v.repair === null ? ['repair'] : [])]);
	if (bad.length) return invalidResult(bad);
	const age = Number.isFinite(v.age) ? v.age : null;
	const repair = Number.isFinite(v.repair) ? v.repair : null;
	const replace = Number.isFinite(v.replace) ? v.replace : t.replacementUsd;
	if (age === null) {
		return { statusLabel: 'Add the age', headline: 'Enter how old the water heater is', explain: 'Use the age calculator if you do not know it: it reads the build date from the serial number.', formula: '' };
	}
	if (repair === null && !has('tankLeak') && !has('flooded')) {
		return { statusLabel: 'Add the repair quote', headline: 'Enter the repair quote in dollars', explain: 'Use the total the repair will cost you, parts and labor.', formula: '' };
	}
	const signs = (cfg.rules.replaceSigns || []).filter((x) => has(x.id));
	const d = repairOrReplace({ ageYears: age, expectedLife: t.life, lifeHigh: cfg.rules.replaceAtLifeHigh ? t.lifeHigh : undefined, repairCost: repair ?? 0, replacementCost: replace, tankLeaking: has('tankLeak'), flooded: has('flooded'), signs }, cfg.rules);
	const signLabel = (id) => (cfg.symptoms.find((x) => x.id === id)?.label || id).toLowerCase();
	const why = {
		'tank-leak': 'A leak from the tank itself cannot be repaired.',
		flooded: 'A flooded water heater must be replaced, the manufacturer says.',
		'repair-over-always-share': `The repair is ${pct(d.share)} of the replacement price, more than the ${pct(cfg.rules.repairShareAlways)} limit.`,
		'past-life': `At ${one(age)} years it is at or past the ${t.lifeHigh}-year top of the usual life range for a ${t.label.toLowerCase()}, so a repair buys little time whatever it costs.`,
		old: `At ${one(age)} years it has used ${pct(d.lifeUsed)} of a ${t.life}-year typical life (${pct(cfg.rules.ageShare)} or more counts as old).`,
		'within-life': `At ${one(age)} years it is within its normal life: ${pct(d.lifeUsed)} of a ${t.life}-year typical life used.`,
		'replace-sign': `You ticked ${d.signs.map(signLabel).join(' and ')}, which Rheem lists among the signs a heater should be replaced, so we lean one step toward replacing.`,
		'repair-over-old-share': `The repair is ${pct(d.share)} of the replacement price, more than ${pct(cfg.rules.repairShareWhenOld)}.`,
		'repair-cheap': `The repair is ${pct(d.share)} of the replacement price.`,
	};
	const pastLife = d.reasons.includes('past-life');
	const signed = d.reasons.includes('replace-sign');
	const labels = { replace: pastLife ? 'Replace soon' : 'Replace', borderline: 'Borderline', repair: 'Repair' };
	const heads = {
		replace: pastLife ? 'Replace soon: it is past the usual life' : signed ? 'Replace it: the warning signs and the numbers both point that way' : 'Replace it rather than pay for this repair',
		borderline: signed ? 'Borderline: the quote is fine, but the warning signs point to a replacement' : d.reasons.includes('old') ? 'Repair is cheap, but the heater is near the end of its life' : 'Repair is possible, but the quote is high for a heater this age',
		repair: 'Repair it: the quote is small and the heater is within its normal life',
	};
	const sentences = d.reasons.map((r) => why[r]);
	if (d.verdict === 'borderline') sentences.push(signed ? 'Have the tank checked for corrosion before you pay for the repair.' : d.reasons.includes('old') ? 'Paying for the repair is reasonable if you also start planning a replacement.' : 'Get a second quote, or ask what else is worn before you decide.');
	for (const s of cfg.symptoms) if (has(s.id) && (s.effect === 'warn' || s.effect === 'safety')) sentences.push(s.note);

	// Energy: running cost of a like-for-like new unit vs the upgrade type, at the DOE test-day use.
	const prices = { usdPerKwh: Number.isFinite(v.kwh) ? v.kwh : cfg.prices.usdPerKwh, usdPerTherm: Number.isFinite(v.therm) ? v.therm : cfg.prices.usdPerTherm };
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
		`Rules: leak or flood → replace; age ≥ ${t.lifeHigh} years (top of the life range) → replace soon; share > ${pct(cfg.rules.repairShareAlways)} → replace; old and share > ${pct(cfg.rules.repairShareWhenOld)} → replace; old → borderline; share > ${pct(cfg.rules.repairShareWhenOld)} → borderline; else repair. Rusty hot water, or rumbling once old, moves the answer one step toward replace`,
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
			// Deep-link the cost calculator to the same type (it reads ?type=&size=).
			const costLink = document.querySelector('.next-steps a[href*="/water-heater-cost-calculator/"]');
			const t = cfg.types[values.type];
			if (costLink && t && t.costType) {
				const u = new URL(costLink.href);
				u.search = '';
				u.searchParams.set('type', t.costType);
				u.searchParams.set('size', t.costSize);
				costLink.href = u.toString();
			}
			if (record) {
				// F-26: the default replacement price follows the type; only a price the visitor typed goes into the link,
				// otherwise a shared or reloaded link froze the old type's price.
				const state = { ...values };
				if (replaceEl && !replaceEl.dataset.edited) delete state.replace;
				writeState(state);
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
