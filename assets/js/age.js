/**
 * age.js: water heater age calculator (STRATEGY §3.1). Math is in formulas.js; data comes from the page
 * (<script type="application/json" id="hwm-age-data">, printed by PHP from data/computed/water-heater-age-calculator.json).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result, so the text is written once.
 */
import { decodeSerial, ageEstimate } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber } from './ui.js';

const one = (v) => formatNumber(v, 1);

/**
 * Turn the form values into the result panel text.
 * @param {object} v Form values: brand, serial, madeYear, madeMonth (named so they never collide with WordPress query vars "year"/"monthnum"), type, hardness, flushed, anode.
 * @param {object} cfg Client data from data/computed/water-heater-age-calculator.json → client.
 * @param {Date} today
 * @returns {{status:(string|undefined), statusLabel:string, headline:string, explain:string, formula:string, made?:object, age?:number}}
 */
export function describe(v, cfg, today) {
	const option = cfg.brandOptions.find((o) => o.id === v.brand) || cfg.brandOptions[cfg.brandOptions.length - 1];
	const code = option.decodeAs ? cfg.brands[option.decodeAs] : null;
	const serial = String(v.serial || '').trim();
	const refYear = today.getFullYear();
	const minYear = refYear - cfg.maxAgeYears;
	let made = null;
	let from = '';
	if (serial && code) {
		made = decodeSerial(serial, code.patterns, today, minYear);
		if (made) from = 'serial';
	}
	const year = Number.isFinite(v.madeYear) ? Math.round(v.madeYear) : null;
	if (!made && year) {
		if (year > refYear || year < minYear) {
			return { status: 'bad', statusLabel: 'Check the year', headline: `Enter a year between ${minYear} and ${refYear}`, explain: 'The year printed on the rating plate, or decoded from the serial number.', formula: '', invalid: ['madeYear'], sourcesText: '' };
		}
		const month = Number.parseInt(v.madeMonth, 10);
		made = { year, month: month >= 1 && month <= 12 ? month : null, week: null, otherYears: [] };
		from = 'year';
	}
	if (!made) {
		let explain;
		if (serial && code) {
			explain = `We could not read a date from "${serial}" with the ${code.label} code: it does not match the code, or it gives a date in the future or before ${minYear}. Check the characters against the rating plate (O and 0, I and 1 are easy to mix up), or enter the year the label shows.`;
		} else if (serial && !code) {
			explain = `We do not decode ${option.label} serial numbers yet, because no manufacturer or government source we could open confirms the code. Enter the manufacture year from the rating plate, or ask ${option.label} with the serial number.`;
		} else {
			explain = 'Type the serial number from the rating plate, or the year the unit was made.';
		}
		return { statusLabel: 'Waiting for a serial number', headline: 'Enter a serial number or the year it was made', explain, formula: '', sourcesText: '' };
	}

	const adj = cfg.adjustments;
	const parts = [];
	const deltas = [];
	const h = adj.hardness[v.hardness] ?? 0;
	if (h) { deltas.push(h); parts.push(`${h} for ${adj.hardnessScale[v.hardness].label.toLowerCase()} water`); }
	if (!v.flushed) { deltas.push(adj.noAnnualFlush); parts.push(`${adj.noAnnualFlush} for no yearly flush`); }
	const type0 = cfg.types.find((t) => t.id === v.type) || cfg.types[0];
	if (v.anode && type0.id !== 'tankless') { deltas.push(adj.anodeReplaced); parts.push(`+${adj.anodeReplaced} for a replaced anode rod`); }
	const type = type0;
	const e = ageEstimate(made, today, type, deltas, adj.minimumLifeYears, cfg.bands);
	const monthName = made.month ? cfg.months[made.month - 1] : '';
	const madeText = monthName ? `${monthName} ${made.year}` : `${made.year}`;
	const pct = Math.round(e.usedFraction * 100);
	const sameRange = e.life.low === e.life.high;
	const lifeText = sameRange ? `${e.life.typical}-year average life` : `${e.life.typical}-year typical life`;
	const sentences = [`That is about ${pct}% of the ${lifeText} for this type (${type.label.toLowerCase()})${parts.length ? ', adjusted for your answers' : ''}.`];
	if (cfg.bands.oldFrom && e.usedFraction >= cfg.bands.oldFrom) sentences.push(`Past ${Math.round(cfg.bands.oldFrom * 100)}% of its typical life, our repair or replace calculator counts it as old, so big repairs stop making sense.`);
	if (e.remaining.max > 0) {
		sentences.push(sameRange || e.remaining.min <= 0 ? `About ${one(e.remaining.max)} years left at most; it could fail sooner.` : `Estimated remaining life: ${one(e.remaining.min)} to ${one(e.remaining.max)} years.`);
	} else {
		sentences.push('It is past the usual range, so plan a replacement before it fails.');
	}
	if (made.otherYears && made.otherYears.length) {
		sentences.push(`${code.label} year letters repeat every ${made.year - made.otherYears[0]} years, so it could also be ${made.otherYears.join(' or ')}; the label's look and other dates on it settle which.`);
	}
	if (from === 'serial' && option.caveat) sentences.push(option.caveat);
	if (from === 'year') sentences.push('Based on the year you entered.');
	const codeLine = from === 'serial'
		? `Built: ${madeText} (${code.label} code${made.week ? `: week ${made.week}, year ${made.year}` : ''})`
		: `Built: ${madeText} (year entered${made.month ? '' : '; counted from mid-year'})`;
	const formula = [
		codeLine,
		`Age = today − ${madeText} = ${one(e.age)} years`,
		`Expected life (${type.label.toLowerCase()}) = ${sameRange ? `${type.typical} years (DOE average)` : `${type.low}–${type.high} years, typical ${type.typical}`}; adjustments: ${parts.length ? parts.join(', ') : 'none'}`,
		parts.length ? `Adjusted life = ${sameRange ? e.life.typical : `${e.life.low}–${e.life.high}, typical ${e.life.typical}`} years (never below ${adj.minimumLifeYears})` : '',
		`Share used = ${one(e.age)} ÷ ${e.life.typical} = ${pct}%`,
	].filter(Boolean).join('\n');
	return {
		status: e.status,
		statusLabel: cfg.statusLabels[e.status],
		headline: `About ${one(e.age)} years old (built ${madeText})`,
		explain: sentences.join(' '),
		formula,
		made,
		age: e.age,
		sourcesText: `${from === 'serial' ? `${code.label} date code: ${code.sourceLine}.` : 'Build year: as you entered it.'} ${cfg.lifeSourceLine}`,
	};
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	// The age calculator page, the brand age pages (/<brand>-water-heater-age/) and the lifespan article share this module.
	const root = document.querySelector('.calc[data-tool="water-heater-age-calculator"], .calc[data-tool$="-water-heater-age"]');
	const cfgEl = document.getElementById('hwm-age-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const form = root.querySelector('form');
		let last = null;
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg, new Date());
			renderResult(root, last);
			// Pass the age and type on to the repair-or-replace tool (it reads ?age=&type=).
			const repairLink = document.querySelector('.next-steps a[href*="/repair-or-replace-water-heater/"]');
			if (repairLink && Number.isFinite(last.age)) {
				const u = new URL(repairLink.href);
				u.search = '';
				u.searchParams.set('age', last.age.toFixed(1));
				if (values.type) u.searchParams.set('type', values.type);
				repairLink.href = u.toString();
			}
			if (record) {
				writeState(values);
				if (last.status) track('calc_result', { tool: root.dataset.tool, brand: values.brand, unit_type: values.type, age_bucket: last.age < 5 ? '0-5' : last.age < 10 ? '5-10' : last.age < 15 ? '10-15' : '15+' });
			}
		};
		// Brands that sell only tankless units preselect the tankless type (serial-formats.json → brandOptions[].type).
		const brandEl = form.elements.namedItem('brand');
		const typeEl = form.elements.namedItem('type');
		brandEl?.addEventListener('change', () => {
			const o = cfg.brandOptions.find((x) => x.id === brandEl.value);
			if (o && o.type && typeEl) typeEl.value = o.type;
		});
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
		if (window.self !== window.top) track('embed_view', { tool: root.dataset.tool, referrer: document.referrer });
	}
}
