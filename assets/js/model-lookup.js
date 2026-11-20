/**
 * model-lookup.js: water heater model number lookup and label decoder. Reads the model number (size, fuel, height,
 * input, warranty) with data/model-formats.json and the serial number (build date) with data/serial-formats.json,
 * then works out the age, the life used and when the tank warranty ends. Math is in formulas.js; data comes from
 * <script type="application/json" id="hwm-ml-data"> (data/computed/water-heater-model-number-lookup.json → client).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result.
 * A photo of the label stays in the browser: it is shown next to the form and, where the browser has a built-in
 * text detector, used to suggest the model and serial. Nothing is uploaded.
 */
import { decodeModel, decodeSerial, ageEstimate, warrantyEnd } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatNumber } from './ui.js';

const one = (v) => formatNumber(v, 1);

/**
 * What the model number says, in words.
 * @returns {{facts:object, lines:string[]}} facts: gallons, sizeClass, fuel, height, input, warrantyYears, labor, type
 */
export function readModel(model, brandKey, cfg) {
	const b = brandKey ? cfg.models[brandKey] : null;
	if (!b || !model) return { facts: {}, lines: [], brand: b };
	const hit = decodeModel(model, b.patterns);
	if (!hit) return { facts: {}, lines: [], brand: b, miss: true };
	const p = b.patterns.find((x) => x.id === hit.patternId);
	const f = {};
	const lines = [];
	const raw = hit.raw;
	if (raw.fuel) { f.fuel = p.fields.fuel.map[raw.fuel]; lines.push(`${raw.fuel} = ${f.fuel.toLowerCase()}`); }
	if (raw.gas) { f.fuel = p.fields.gas.map[raw.gas]; lines.push(`${raw.gas} = ${f.fuel.toLowerCase()}`); }
	if (raw.gallons) {
		f.gallons = Number(raw.gallons);
		f.sizeClass = Boolean(p.fields.gallons.sizeClass);
		lines.push(`${raw.gallons} = ${f.sizeClass ? `${f.gallons}-gallon size class` : `${f.gallons} gallons`}`);
	}
	if (raw.height) { f.height = p.fields.height.map[raw.height]; lines.push(`${raw.height} = ${f.height.toLowerCase()}`); }
	if (raw.warrantyYears) { f.warrantyYears = Number(raw.warrantyYears); lines.push(`${raw.warrantyYears} = ${f.warrantyYears}-year tank warranty`); }
	if (raw.warrantyCode) {
		const t = (cfg.whirlpoolCodes || []).find((c) => c.code === raw.warrantyCode);
		if (t) { f.warrantyYears = t.tank; f.labor = t.labor; lines.push(`warranty code ${raw.warrantyCode} = ${t.tank}-year tank, ${t.parts}-year parts, ${t.labor}-year labor`); }
	}
	if (raw.input && p.fields.input) {
		const n = Number(raw.input);
		const gas = raw.fuel === 'G';
		if (n === 0) f.input = 'Not in the model number';
		else f.input = gas ? `${formatNumber(n * p.fields.input.gasMultiplier)} ${p.fields.input.gasUnit}` : `${formatNumber(n * p.fields.input.electricMultiplier)} ${p.fields.input.electricUnit}`;
		lines.push(`${raw.input} = ${n === 0 ? p.fields.input.zeroMeans : f.input}`);
	}
	// Unit type for the life estimate: the Rheem XE series letters that start with H are heat pumps.
	if (raw.fuel === 'G' || raw.gas) f.type = 'gas_tank';
	else if (raw.fuel === 'E') f.type = raw.series && p.heatPumpSeriesPrefix && raw.series.startsWith(p.heatPumpSeriesPrefix) ? 'heat_pump' : 'electric_tank';
	if (f.type === 'heat_pump') f.fuel = 'Electric (heat pump)';
	return { facts: f, lines, brand: b, patternId: hit.patternId };
}

/**
 * @param {object} v Form values: brand, model, serial, type ('auto' or a lifespans type id).
 * @param {object} cfg Client data.
 * @param {Date} today
 */
export function describe(v, cfg, today) {
	const option = cfg.brandOptions.find((o) => o.id === v.brand) || cfg.brandOptions[cfg.brandOptions.length - 1];
	const model = String(v.model || '').trim().toUpperCase();
	const serial = String(v.serial || '').trim().toUpperCase();
	const m = readModel(model, cfg.modelMap[option.id], cfg);
	const f = m.facts;
	const code = option.decodeAs ? cfg.serialBrands[option.decodeAs] : null;
	const minYear = today.getFullYear() - cfg.maxAgeYears;
	const made = serial && code ? decodeSerial(serial, code.patterns, today, minYear) : null;

	const typeId = v.type && v.type !== 'auto' ? v.type : f.type || option.type || '';
	const type = cfg.types.find((t) => t.id === typeId) || null;
	const madeText = made ? `${made.month ? `${cfg.months[made.month - 1]} ` : ''}${made.year}` : '';
	const est = made && type ? ageEstimate(made, today, type, [], cfg.minimumLifeYears, cfg.bands) : null;
	const w = made && f.warrantyYears ? warrantyEnd(made, f.warrantyYears, today) : null;
	const wText = w ? `${cfg.months[w.month - 1]} ${w.year}` : '';

	const notDecoded = 'Not in the model number: read it on the label';
	const rows = [
		{ item: 'Brand', value: option.label },
		{ item: 'Built', value: made ? `${madeText}${made.otherYears && made.otherYears.length ? ` (or ${made.otherYears.join(', ')})` : ''}` : serial ? 'Serial not decoded' : 'Enter the serial number' },
		{ item: 'Age', value: est ? `${one(est.age)} years` : '–' },
		{ item: 'Size', value: f.gallons ? (f.sizeClass ? `${f.gallons}-gallon class` : `${f.gallons} gallons`) : notDecoded },
		{ item: 'Fuel', value: f.fuel || notDecoded },
		{ item: 'Input', value: f.input || notDecoded },
		{ item: 'Tank warranty', value: f.warrantyYears ? `${f.warrantyYears} years${f.labor ? ` (labor ${f.labor})` : ''}` : notDecoded },
		{ item: 'Warranty ends (about)', value: w ? `${wText}${w.yearsLeft > 0 ? `, ${one(w.yearsLeft)} years left` : ', already ended'}` : '–' },
	];
	const table = { caption: 'What your label says', columns: [{ key: 'item', label: 'Item' }, { key: 'value', label: 'Decoded' }], rows };

	const formula = [
		model ? (m.lines.length ? `Model ${model} (${m.brand.label} rule ${m.patternId}): ${m.lines.join('; ')}` : `Model ${model}: no ${option.label} model rule matches${m.brand ? '' : ' (we have no sourced key for this brand)'}`) : 'Model: not entered',
		serial ? (made ? `Serial ${serial} (${code.label} date code): built ${madeText}${made.week ? `, week ${made.week}` : ''}` : `Serial ${serial}: ${code ? `does not match the ${code.label} date code` : `no sourced date code for ${option.label}`}`) : 'Serial: not entered',
		est ? `Age = ${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')} − ${madeText} = ${one(est.age)} years; typical life ${est.life.typical} years (${type.label.toLowerCase()}) → ${Math.round(est.usedFraction * 100)}% used` : '',
		w ? `Warranty end ≈ build date + ${f.warrantyYears} years = ${wText}; the warranty itself runs from installation, so it can end a little later` : '',
	].filter(Boolean).join('\n');

	if (!made && !m.lines.length) {
		let explain = 'Type the model and serial numbers from the rating plate on the side of the heater.';
		if (model && m.miss) explain = `"${model}" does not match the ${option.label} model rule we have. Check it against the label (O and 0, I and 1 are easy to mix up); the size and fuel are printed on the label too.`;
		else if (model && !m.brand) explain = `We have no sourced model number key for ${option.label}, so read the size, fuel and input on the label. The serial number still gives the build date where the brand's code is known.`;
		if (serial && !code) explain += ` We do not decode ${option.label} serial numbers: no manufacturer or government source we could open confirms the code.`;
		else if (serial && code) explain += ` The serial "${serial}" does not match the ${code.label} date code.`;
		return { statusLabel: 'Waiting for the label', headline: 'Enter the model and serial number from the label', explain, formula, table };
	}

	const parts = [];
	if (made) parts.push(est ? `Built ${madeText}, so it is about ${one(est.age)} years old: ${Math.round(est.usedFraction * 100)}% of the ${est.life.typical}-year typical life for a ${type.label.toLowerCase()}.` : `Built ${madeText}. Pick the type of heater to see how much of its life is used.`);
	if (f.gallons || f.fuel) parts.push(`The model number says ${[f.gallons ? (f.sizeClass ? `a ${f.gallons}-gallon size class` : `${f.gallons} gallons`) : '', f.fuel ? f.fuel.toLowerCase() : '', f.height ? f.height.toLowerCase() : ''].filter(Boolean).join(', ')}.`);
	if (f.sizeClass) parts.push(cfg.sizeClassNote);
	if (w) parts.push(w.yearsLeft > 0 ? `Its ${f.warrantyYears}-year tank warranty runs to about ${wText}, counted from the build date.` : `Its ${f.warrantyYears}-year tank warranty ended about ${wText}, counted from the build date.`);
	else if (f.warrantyYears) parts.push(`It has a ${f.warrantyYears}-year tank warranty; add the serial number to see when it ends.`);
	if (made && made.otherYears && made.otherYears.length) parts.push(`The year letters repeat every 20 years, so it could also be ${made.otherYears.join(' or ')}; the label's look settles which.`);
	if (option.caveat && made) parts.push(option.caveat);

	let status;
	let statusLabel = 'Decoded';
	if (w) { status = w.yearsLeft > 0 ? 'ok' : 'warn'; statusLabel = w.yearsLeft > 0 ? 'Likely still under tank warranty' : 'Tank warranty likely over'; }
	if (est && est.status === 'bad') { status = 'bad'; statusLabel = cfg.statusLabels.bad; }
	else if (est && !w) { status = est.status; statusLabel = cfg.statusLabels[est.status]; }
	const headline = made
		? `${option.label}, built ${madeText}: about ${one(est ? est.age : 0)} years old${w ? (w.yearsLeft > 0 ? `, warranty to about ${wText}` : ', tank warranty over') : ''}`
		: `${option.label} ${[f.gallons ? (f.sizeClass ? `${f.gallons}-gallon class` : `${f.gallons}-gallon`) : '', f.fuel ? f.fuel.toLowerCase() : '', 'water heater'].filter(Boolean).join(' ')}${f.warrantyYears ? ` with a ${f.warrantyYears}-year tank warranty` : ''}`;
	return { status, statusLabel, headline: made && !est ? `${option.label}, built ${madeText}` : headline, explain: parts.join(' '), formula, table, made, facts: f };
}

/** Find a model and a serial in text read from a photo of the label (browser text detector). */
export function guessFromText(lines) {
	const out = {};
	const text = lines.join('\n').toUpperCase();
	const after = (re) => { const x = text.match(re); return x ? x[1].replace(/[^A-Z0-9-]/g, '') : ''; };
	out.model = after(/(?:MODEL|M\/N|MOD\.?)\s*(?:NO\.?|NUMBER|#)?\s*[:.]?\s*([A-Z0-9][A-Z0-9 -]{4,20})/);
	out.serial = after(/(?:SERIAL|S\/N|SER\.?)\s*(?:NO\.?|NUMBER|#)?\s*[:.]?\s*([A-Z0-9][A-Z0-9 -]{5,20})/);
	return out;
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="water-heater-model-number-lookup"]');
	const cfgEl = document.getElementById('hwm-ml-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const form = root.querySelector('form');
		let last = null;
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg, new Date());
			renderResult(root, last);
			if (record) {
				writeState(values);
				const age = last.made ? new Date().getFullYear() - last.made.year : null;
				track('calc_result', { tool: 'water-heater-model-number-lookup', brand: values.brand, model_decoded: last.facts && Object.keys(last.facts).length ? 'yes' : 'no', age_bucket: age === null ? 'none' : age < 6 ? '0-5' : age < 11 ? '6-10' : age < 16 ? '11-15' : '16+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);

		// Photo of the label: preview only, never uploaded.
		const file = root.querySelector('[data-photo-input]');
		const img = root.querySelector('[data-photo-preview]');
		const note = root.querySelector('[data-photo-note]');
		file?.addEventListener('change', async () => {
			const f = file.files && file.files[0];
			if (!f || !img) return;
			if (img.dataset.url) URL.revokeObjectURL(img.dataset.url);
			img.dataset.url = URL.createObjectURL(f);
			img.src = img.dataset.url;
			img.hidden = false;
			if (!('TextDetector' in window)) { if (note) note.textContent = 'Read the model and serial from your photo and type them above. Your photo stays on this device.'; return; }
			try {
				await img.decode();
				const found = await new window.TextDetector().detect(img);
				const g = guessFromText(found.map((x) => x.rawValue));
				const set = (name, val) => { const el = form.elements.namedItem(name); if (el && val && !el.value) el.value = val; };
				set('model', g.model);
				set('serial', g.serial);
				if (note) note.textContent = g.model || g.serial ? 'We filled in what your browser could read. Check each character against the label.' : 'Your browser could not read the text. Type the model and serial above.';
				run(true);
			} catch {
				if (note) note.textContent = 'Read the model and serial from your photo and type them above. Your photo stays on this device.';
			}
		});

		const fromUrl = readState(form);
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'water-heater-model-number-lookup', referrer: document.referrer });
	}
}
