/**
 * cost-calculator.js: installed water heater cost (STRATEGY §3.5). Math is in formulas.js (installedCost), wiring in
 * cost-model.js; data comes from <script type="application/json" id="hwm-cost-data"> (data/computed/water-heater-cost-calculator.json → client).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result.
 * Deep links: ?type=gas_tank&size=40 (other pages link here preset), plus &state=&scenario= and one param per extra.
 */
import { estimateInstalled, defaultExtras, placeFor } from './cost-model.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatCurrency, formatNumber } from './ui.js';

const usd = (v) => formatCurrency(v);

/**
 * @param {object} v Form values: type, size, state, scenario, and one boolean per extra id.
 * @param {object} cfg Client data: { model, defaults }.
 */
export function describe(v, cfg) {
	const m = cfg.model;
	const type = m.types[v.type] ? v.type : cfg.defaults.type;
	const extras = Object.keys(m.extras).filter((id) => v[id]);
	const est = estimateInstalled(m, { type, size: v.size, state: v.state, extras });
	const place = placeFor(m, v.state);
	const where = v.state && m.states[v.state] ? place.name : 'the U.S. average';
	const extrasSum = est.items.filter((i) => i.id !== 'unit' && i.id !== 'labor');
	const labor = est.items.find((i) => i.id === 'labor');
	const rows = est.items.map((i) => ({ item: i.label, low: usd(i.low), typical: usd(i.typical), high: usd(i.high) }));
	const explain = [
		`${est.unit.label}: the unit itself is about ${usd(est.unit.typical)} (${usd(est.unit.low)} to ${usd(est.unit.high)} in current listings).`,
		`Installation adds about ${usd(est.typical - est.unit.typical)} for labor${extrasSum.length ? ` and ${extrasSum.length} extra${extrasSum.length > 1 ? 's' : ''}` : ''} in ${where} (price factor ${formatNumber(est.multiplier, 2)}).`,
		extras.includes('permit') ? '' : 'No permit or haul-away is included; most places require a permit to replace a water heater.',
	].filter(Boolean).join(' ');
	const formula = [
		`Unit (typical) = ${usd(est.unit.typical)}`,
		`Labor = ${usd(m.labor[type].typical)} × ${formatNumber(est.multiplier, 3)} = ${usd(labor.typical)}`,
		...extrasSum.map((i) => `${i.label} = ${usd(m.extras[i.id].typical)} × ${formatNumber(est.multiplier, 3)} = ${usd(i.typical)}`),
		`Total (typical) = ${est.items.map((i) => usd(i.typical)).join(' + ')} = ${usd(est.typical)}`,
		`Low and high use the low and high end of every line the same way.`,
	].join('\n');
	return {
		statusLabel: 'Installed estimate',
		headline: `About ${usd(est.typical)} installed`,
		explain: `${explain} Range: ${usd(est.low)} to ${usd(est.high)}.`,
		formula,
		table: { caption: 'Itemized estimate', columns: [{ key: 'item', label: 'Item' }, { key: 'low', label: 'Low', num: true }, { key: 'typical', label: 'Typical', num: true }, { key: 'high', label: 'High', num: true }], rows, total: { item: 'Total', low: usd(est.low), typical: usd(est.typical), high: usd(est.high) } },
		estimate: est,
	};
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="water-heater-cost-calculator"]');
	const cfgEl = document.getElementById('hwm-cost-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const m = cfg.model;
		const form = root.querySelector('form');
		const typeEl = form.elements.namedItem('type');
		const sizeEl = form.elements.namedItem('size');
		const scenEl = form.elements.namedItem('scenario');
		let last = null;
		const fillSizes = () => {
			const t = m.types[typeEl.value];
			if (!t || !sizeEl) return;
			const keep = sizeEl.value;
			sizeEl.replaceChildren(...t.sizes.map((s) => new Option(s.label, s.id)));
			sizeEl.value = t.sizes.some((s) => s.id === keep) ? keep : t.defaultSize;
		};
		const tickDefaults = () => {
			const on = defaultExtras(m, scenEl.value, typeEl.value);
			for (const id of Object.keys(m.extras)) {
				const el = form.elements.namedItem(id);
				if (el) el.checked = on.includes(id);
			}
		};
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				const t = last.estimate.typical;
				track('calc_result', { tool: 'water-heater-cost-calculator', unit_type: values.type, scenario: values.scenario, state: values.state, total_bucket: t < 1500 ? '<1500' : t < 2500 ? '1500-2500' : t < 4000 ? '2500-4000' : '4000+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		typeEl?.addEventListener('change', () => { fillSizes(); tickDefaults(); });
		scenEl?.addEventListener('change', tickDefaults);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const params = new URLSearchParams(window.location.search);
		const fromUrl = readState(form);
		if (fromUrl) {
			fillSizes();
			if (params.has('size') && sizeEl) sizeEl.value = params.get('size');
			fillSizes();
			const anyExtra = Object.keys(m.extras).some((id) => params.has(id));
			if (anyExtra) for (const id of Object.keys(m.extras)) { const el = form.elements.namedItem(id); if (el) el.checked = params.has(id); }
			else tickDefaults();
		}
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'water-heater-cost-calculator', referrer: document.referrer });
	}
}
