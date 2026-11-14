/**
 * ui.js: shared browser helpers for every calculator (no math here; math is in formulas.js).
 * Loaded as an ES module by each calculator module.
 */

/** Read a form's named inputs into a plain object (numbers parsed for inputmode=decimal / type=number). */
export function readInputs(form) {
	const out = {};
	for (const el of form.elements) {
		if (!el.name || el.disabled) continue;
		if (el.type === 'checkbox') {
			out[el.name] = el.checked;
		} else if (el.type === 'radio') {
			if (el.checked) out[el.name] = el.value;
		} else if (el.type === 'number' || el.inputMode === 'decimal' || el.inputMode === 'numeric') {
			const n = parseFloat(String(el.value).replace(/,/g, ''));
			out[el.name] = Number.isFinite(n) ? n : null;
		} else {
			out[el.name] = el.value;
		}
	}
	return out;
}

const usd0 = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
const usd2 = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 });

/** "$1,234" (or "$12.34" with cents=true). */
export function formatCurrency(value, cents = false) {
	return Number.isFinite(value) ? (cents ? usd2 : usd0).format(value) : '–';
}

/** "1,234.5" with a fixed number of decimals. */
export function formatNumber(value, decimals = 0) {
	return Number.isFinite(value)
		? value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
		: '–';
}

/** Put the current inputs into the URL query (replaceState; no history spam). */
export function writeState(values) {
	const url = new URL(window.location.href);
	url.search = '';
	for (const [key, value] of Object.entries(values)) {
		if (value === null || value === '' || value === false) continue;
		url.searchParams.set(key, value === true ? '1' : String(value));
	}
	window.history.replaceState(null, '', url);
	return url.toString();
}

/** Fill form fields from the URL query (only fields that exist in the form). */
export function readState(form) {
	const params = new URLSearchParams(window.location.search);
	for (const [key, value] of params) {
		const el = form.elements.namedItem(key);
		if (!el) continue;
		if (el instanceof RadioNodeList) {
			for (const r of el) r.checked = r.value === value;
		} else if (el.type === 'checkbox') {
			el.checked = value === '1' || value === 'true';
		} else {
			el.value = value;
		}
	}
	return params.size > 0;
}

/** Copy text to the clipboard; resolves true on success. */
export async function copyText(text) {
	try {
		await navigator.clipboard.writeText(text);
		return true;
	} catch {
		const area = document.createElement('textarea');
		area.value = text;
		area.setAttribute('readonly', '');
		area.style.position = 'absolute';
		area.style.left = '-9999px';
		document.body.appendChild(area);
		area.select();
		const ok = document.execCommand('copy');
		area.remove();
		return ok;
	}
}

/** Share the current URL (native share sheet where available, else copy). */
export async function shareLink(title) {
	const url = window.location.href;
	if (navigator.share) {
		try {
			await navigator.share({ title, url });
			return 'shared';
		} catch {
			/* user cancelled; fall back to copy */
		}
	}
	return (await copyText(url)) ? 'copied' : 'failed';
}

/** Debounce a function by ms. */
export function debounce(fn, ms = 150) {
	let timer;
	return (...args) => {
		clearTimeout(timer);
		timer = setTimeout(() => fn(...args), ms);
	};
}

/** Briefly change a button's label to confirm an action, then restore it. */
export function flashLabel(button, text, ms = 1600) {
	const original = button.dataset.label || button.textContent;
	button.dataset.label = original;
	button.textContent = text;
	setTimeout(() => { button.textContent = original; }, ms);
}

/**
 * Update the server-rendered result panel without layout jumps.
 * @param {HTMLElement} root The .calc section.
 * @param {{status?:'ok'|'warn'|'bad', statusLabel?:string, headline:string, explain:string, formula:string}} r
 */
export function renderResult(root, r) {
	const box = root.querySelector('[data-result]');
	if (!box) return;
	box.classList.remove('result--ok', 'result--warn', 'result--bad');
	if (r.status) box.classList.add(`result--${r.status}`);
	const set = (sel, value) => {
		const el = box.querySelector(sel);
		if (el && value !== undefined) el.textContent = value;
	};
	set('[data-result-status]', r.statusLabel);
	set('[data-result-headline]', r.headline);
	set('[data-result-explain]', r.explain);
	set('[data-result-formula]', r.formula);
	if (r.table) renderTable(box, r.table);
}

/**
 * Fill (or create) the result table inside the result box. Same shape as data/computed → default.table:
 * { caption, columns: [{ key, label, num? }], rows: [{ key: text }], total?: { key: text } }.
 * The row count of a tool stays the same between updates, so the box does not jump.
 */
export function renderTable(box, t) {
	let wrap = box.querySelector('[data-result-table]');
	if (!wrap) {
		wrap = document.createElement('div');
		wrap.className = 'table-wrap result__table';
		wrap.setAttribute('data-result-table', '');
		box.querySelector('[data-result-explain]')?.after(wrap);
	}
	const table = document.createElement('table');
	if (t.caption) table.createCaption().textContent = t.caption;
	const head = table.createTHead().insertRow();
	for (const c of t.columns) {
		const th = document.createElement('th');
		th.scope = 'col';
		th.textContent = c.label;
		if (c.num) th.className = 'num';
		head.appendChild(th);
	}
	const addRow = (section, row, strong) => {
		const tr = section.insertRow();
		t.columns.forEach((c, i) => {
			const cell = document.createElement(i === 0 ? 'th' : 'td');
			if (i === 0) cell.scope = 'row';
			if (c.num) cell.className = 'num';
			cell.textContent = row[c.key] ?? '';
			if (strong) cell.style.fontWeight = '700';
			tr.appendChild(cell);
		});
	};
	const body = table.createTBody();
	for (const row of t.rows) addRow(body, row, false);
	if (t.total) addRow(table.createTFoot(), t.total, true);
	wrap.replaceChildren(table);
}

/**
 * Send an analytics event (no-op when no tags are set up). Key inputs must be bucketed by the caller.
 * Direct GA4 gets a gtag event; Google Tag Manager gets a dataLayer event of the same name (use a Custom Event trigger).
 * @param {string} name e.g. 'calc_result'
 * @param {object} params
 */
export function track(name, params = {}) {
	const tags = window.hwmTags || {};
	if (tags.ga4 && typeof window.gtag === 'function') window.gtag('event', name, params);
	if (tags.gtm && Array.isArray(window.dataLayer)) window.dataLayer.push({ event: name, ...params });
}

/**
 * Wire the shared buttons (copy result, share link) for one calculator section.
 * @param {HTMLElement} root
 * @param {() => string} resultText Returns the plain-text result to copy.
 */
export function wireActions(root, resultText) {
	root.querySelector('[data-action="copy"]')?.addEventListener('click', async (e) => {
		flashLabel(e.currentTarget, (await copyText(resultText())) ? 'Copied' : 'Copy failed');
	});
	root.querySelector('[data-action="share"]')?.addEventListener('click', async (e) => {
		const outcome = await shareLink(document.title);
		if (outcome !== 'shared') flashLabel(e.currentTarget, outcome === 'copied' ? 'Link copied' : 'Copy failed');
	});
}
