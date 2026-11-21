/**
 * recall-lookup.js: is this water heater in a CPSC recall? Checks brand, model and serial against the recalls in
 * data/recalls.json (copied into data/computed/water-heater-recall-lookup.json → client). Matching is in
 * formulas.js (recallCheck). describe() is also used by scripts/build-data.mjs for the server-rendered default.
 * The result never says a heater is safe: no match only means no match in the recalls we track.
 */
import { recallCheck } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, checkLimits, invalidResult } from './ui.js';

const LEVEL = { match: 'Recalled: matches', possible: 'Check: possible match', outside: 'Listed model, serial outside the range' };
const dateText = (iso) => new Date(`${iso}T12:00:00Z`).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' });

/**
 * @param {{brand:string, model:string, serial:string, madeYear:number|null}} v Never name a field 'year': it is a WordPress query var.
 * @param {{recalls:Array, brands:object, limits:object, count:number, since:number}} cfg
 */
export function describe(v, cfg) {
	const bad = checkLimits({ madeYear: v.madeYear }, cfg.limits);
	if (bad.length) return { ...invalidResult(bad), result: null };
	const r = recallCheck({ ...v, year: v.madeYear }, cfg.recalls);
	const brandName = cfg.brands[v.brand] || '';
	const rows = r.hits.map((h) => ({ recall: `${h.recall.product} (${dateText(h.recall.date)})`, level: LEVEL[h.level], why: h.reason }));
	for (const b of r.brandOnly) rows.push({ recall: `${b.product} (${dateText(b.date)})`, level: b.brandWide || b.component ? 'Same brand: check the year and serial' : 'Same brand, not your model', why: b.which });
	const table = rows.length ? { caption: 'Recalls that touch your brand or model', columns: [{ key: 'recall', label: 'Recall (CPSC date)' }, { key: 'level', label: 'Result' }, { key: 'why', label: 'Why' }], rows } : null;
	const scope = `the ${cfg.count} U.S. CPSC water heater recalls announced since ${cfg.since}`;
	const formula = [
		`Model typed: ${v.model || '(none)'} → compared without spaces, dashes or dots`,
		`Checked against the model lists, model prefixes and serial ranges of ${scope}`,
		'Brand-wide and part (gas valve) recalls: matched by brand, then by serial range and year made where the notice gives them',
		`Result: ${r.hits.filter((h) => h.level === 'match').length} match, ${r.hits.filter((h) => h.level === 'possible').length} possible, ${r.brandOnly.length} other recall(s) of the brand`,
	].join('\n');
	const top = r.hits[0];
	let status;
	let statusLabel;
	let headline;
	let explain;
	if (top && top.level === 'match') {
		status = 'bad';
		statusLabel = 'Recall match';
		headline = `This heater matches a CPSC recall: ${top.recall.product}`;
		explain = `${top.reason} The hazard: ${top.recall.hazard} What to do: ${top.recall.remedy} Confirm with the company before you act: ${top.recall.contact}`;
	} else if (top && top.level === 'possible') {
		status = 'warn';
		statusLabel = 'Possible match';
		headline = `Possible match: ${top.recall.product}`;
		explain = `${top.reason} ${top.recall.which} Confirm with the company: ${top.recall.contact}`;
	} else if (top && top.level === 'outside') {
		status = 'ok';
		statusLabel = 'Outside the recalled range';
		headline = 'Your model is in a recall, but your serial is outside the recalled range';
		explain = `${top.recall.which} Serial ranges are our reading of the CPSC notice; the company can confirm in a minute: ${top.recall.contact}`;
	} else {
		status = 'ok';
		statusLabel = 'No match';
		headline = v.model || brandName ? `No match in ${scope}` : 'Pick the brand and type the model number';
		explain = v.model || brandName
			? `${r.brandOnly.length ? `${brandName} has ${r.brandOnly.length} recall${r.brandOnly.length > 1 ? 's' : ''} in our list, listed below, but none names this model. ` : ''}No match is not proof of safety: recalls by other agencies, a gas utility or the maker alone are not in the CPSC list. Check the model on the maker's site too, and sign up below to hear about new recalls.`
			: 'The model number is on the rating label on the side of the heater, after "Model" or "M/N". The serial number helps for recalls that cover only some build dates.';
	}
	return { status, statusLabel, headline, explain, formula, table, result: { top: r.top, hits: r.hits.length, brandOnly: r.brandOnly.length } };
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="water-heater-recall-lookup"]');
	const el = document.getElementById('hwm-rl-data');
	if (root && el) {
		const cfg = JSON.parse(el.textContent);
		const form = root.querySelector('form');
		let last = null;
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			const box = root.querySelector('[data-result-table]');
			if (box) box.hidden = !last.table;
			const brandField = document.querySelector('[data-signup="recall"] [name="brand"]');
			if (brandField && values.brand) brandField.value = values.brand;
			if (record) {
				writeState(values);
				if (last.result) track('calc_result', { tool: 'water-heater-recall-lookup', brand: values.brand || 'none', result: last.result.top });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const fromUrl = readState(form);
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'water-heater-recall-lookup', referrer: document.referrer });
	}
}
