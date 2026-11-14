/**
 * heat-pump-savings.js: heat pump water heater savings and payback (STRATEGY §3.8, inside /heat-pump-water-heater-cost/).
 * Math: formulas.js (simplePaybackYears, netSavingOver) through cost-model.js (estimateInstalled, operatingCosts), the
 * same functions the cost calculator and the gas vs electric tool use, so the numbers agree across pages.
 * Data: <script type="application/json" id="hwm-hp-data"> (data/computed/heat-pump-water-heater-cost.json → client).
 * describe() is also used by scripts/build-data.mjs for the server-rendered default result and the state table.
 */
import { simplePaybackYears, netSavingOver } from './formulas.js';
import { estimateInstalled, defaultExtras, placeFor, gallonsFor, operatingCosts } from './cost-model.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatCurrency, formatNumber, checkLimits, invalidResult } from './ui.js';

const usd = (v) => formatCurrency(v);

/**
 * @param {object} v Form values: current ('electric_tank'|'gas_tank'), size ('50'|'65'|'80'), household, state,
 *   kwh, therm (prices), uef (heat pump UEF), rebate (USD), space (true when the room needs space work).
 * @param {object} cfg Client data: { model, defaults, paybackGoodYears }.
 */
export function describe(v, cfg) {
	const m = cfg.model;
	const bad = checkLimits(v, cfg.limits, ['propane']);
	if (bad.length) return { ...invalidResult(bad), result: null };
	const current = v.current === 'gas_tank' ? 'gas_tank' : 'electric_tank';
	const size = m.types.heat_pump.sizes.some((s) => s.id === String(v.size)) ? String(v.size) : m.types.heat_pump.defaultSize;
	const place = placeFor(m, v.state);
	const where = v.state && m.states[v.state] ? place.name : 'U.S. average';
	const prices = { usdPerKwh: Number.isFinite(v.kwh) ? v.kwh : place.usdPerKwh, usdPerTherm: Number.isFinite(v.therm) ? v.therm : place.usdPerTherm };
	const hpUef = Number.isFinite(v.uef) ? v.uef : m.types.heat_pump.uef;
	const typedRebate = Number.isFinite(v.rebate) && v.rebate > 0 ? v.rebate : 0;
	const loc = cfg.location.options.find((o) => o.id === v.location) || cfg.location.options.find((o) => o.id === cfg.location.default);
	const gallons = gallonsFor(m, v.household);
	const op = operatingCosts(m, { gallonsPerDay: gallons, setpointF: m.setpointF, inletF: place.inletF, prices, uefs: { heat_pump: hpUef } });
	const by = Object.fromEntries(op.rows.map((r) => [r.id, r]));
	const cur = by[current];
	const hp = by.heat_pump;
	// The heat pump job: replacing an electric tank reuses the 240-volt circuit; replacing gas needs one (fuel switch).
	const scenario = current === 'gas_tank' ? 'fuelSwitch' : 'replace';
	const hpExtras = [...defaultExtras(m, scenario, 'heat_pump'), ...(v.space ? ['heatPumpSpace'] : [])];
	const hpJob = estimateInstalled(m, { type: 'heat_pump', size, state: v.state, extras: hpExtras });
	// The alternative: replacing the current heater like for like (its default size).
	const altJob = estimateInstalled(m, { type: current, size: m.types[current].defaultSize, state: v.state, extras: defaultExtras(m, 'replace', current) });
	// A rebate can never be worth more than the heat pump job itself (audit F-02: $99,999 gave a "$98,631 less" result).
	const rebate = Math.min(typedRebate, hpJob.typical);
	const extra = hpJob.typical - altJob.typical - rebate;
	const saving = Math.round(cur.year - hp.year);
	const payback = simplePaybackYears(extra, saving);
	const life = m.types.heat_pump.life;
	const net = netSavingOver(saving, life, extra);
	const good = cfg.paybackGoodYears;
	const status0 = payback === null ? 'bad' : payback <= good ? 'ok' : payback <= life ? 'warn' : 'bad';
	const status = loc.status === 'warn' && status0 === 'ok' ? 'warn' : status0;
	const curLabel = current === 'gas_tank' ? 'gas tank' : 'electric tank';
	const headline = saving > 0
		? `Saves about ${usd(saving)} a year; ${payback === 0 ? 'no extra cost to repay' : payback === null ? 'never pays back' : `pays back in about ${formatNumber(payback, 1)} years`}`
		: `Costs about ${usd(-saving)} a year more to run than your ${curLabel}`;
	const statusLabel0 = payback === null ? 'Does not pay back' : payback === 0 ? 'Cheaper from day one' : payback <= good ? 'Pays back quickly' : payback <= life ? 'Pays back within its life' : 'Pays back slowly';
	// A cold space (S-5) lowers the label too: the saving shown is the best case there.
	const statusLabel = status !== status0 ? `${statusLabel0} at best: cold space` : statusLabel0;
	const explain = [
		`With ${where} prices (${formatNumber(prices.usdPerKwh * 100, 1)}¢ per kWh${current === 'gas_tank' ? `, $${formatNumber(prices.usdPerTherm, 2)} per therm` : ''}), a ${size}-gallon heat pump costs about ${usd(hp.year)} a year to run against ${usd(cur.year)} for your ${curLabel}.`,
		`Installed, it costs about ${usd(hpJob.typical)} against ${usd(altJob.typical)} for a new ${curLabel}${rebate ? `, less your ${usd(rebate)} rebate` : ''}: ${extra > 0 ? `${usd(extra)} more` : `${usd(-extra)} less`}.`,
		saving > 0 ? (net >= 0 ? `Over a ${life}-year life that nets about ${usd(net)}.` : `Over a ${life}-year life it falls about ${usd(-net)} short of repaying the extra cost.`) : '',
		typedRebate > rebate ? `Your rebate is more than the installed price, so it is counted only up to ${usd(rebate)}.` : '',
		loc.note,
	].filter(Boolean).join(' ');
	const formula = [
		`Hot water: ${gallons} gal a day, ${m.setpointF} °F setting, ${formatNumber(place.inletF, 1)} °F incoming water`,
		`${cur.label}: ${cur.use.kwh !== undefined ? `${formatNumber(cur.use.kwh)} kWh × $${prices.usdPerKwh}` : `${formatNumber(cur.use.therms)} therms × $${prices.usdPerTherm}`} = ${usd(cur.year)}/yr (UEF ${formatNumber(cur.uef, 2)})`,
		`Heat pump: ${formatNumber(hp.use.kwh)} kWh × $${prices.usdPerKwh} = ${usd(hp.year)}/yr (UEF ${formatNumber(hpUef, 2)})`,
		`Yearly saving = ${usd(cur.year)} − ${usd(hp.year)} = ${usd(saving)}`,
		`Heat pump installed (${hpJob.items.map((i) => i.label.split(' (')[0]).join(', ')}) = ${usd(hpJob.typical)}`,
		`New ${curLabel} installed (like for like) = ${usd(altJob.typical)}`,
		`Extra cost = ${usd(hpJob.typical)} − ${usd(altJob.typical)}${rebate ? ` − ${usd(rebate)} rebate${typedRebate > rebate ? ' (capped at the installed price)' : ''}` : ''} = ${usd(extra)}`,
		`Payback = extra ÷ yearly saving = ${payback === null ? 'never (no saving)' : payback === 0 ? '0 (no extra cost)' : `${formatNumber(payback, 1)} years`}`,
		`Net over ${life} years = ${usd(saving)} × ${life} − ${usd(extra)} = ${usd(net)}`,
		`Location: ${loc.label.toLowerCase()} (${loc.status === 'warn' ? 'warning only; no derate applied' : 'no change'})`,
	].join('\n');
	const rows = [
		{ item: 'Installed cost', cur: usd(altJob.typical), hp: usd(hpJob.typical) },
		{ item: 'Running cost a year', cur: usd(cur.year), hp: usd(hp.year) },
		{ item: `Running cost over ${life} years`, cur: usd(cur.year * life), hp: usd(hp.year * life) },
	];
	return {
		status,
		statusLabel,
		headline,
		explain,
		formula,
		table: { caption: `Heat pump vs a new ${curLabel}, typical installed prices`, columns: [{ key: 'item', label: 'Item' }, { key: 'cur', label: `New ${curLabel}`, num: true }, { key: 'hp', label: 'Heat pump', num: true }], rows, total: { item: `Total over ${life} years`, cur: usd(altJob.typical + cur.year * life), hp: usd(hpJob.typical - rebate + hp.year * life) } },
		result: { saving, extra, payback, net, life, hpInstalled: hpJob.typical, altInstalled: altJob.typical, hpYear: hp.year, curYear: cur.year },
	};
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="heat-pump-water-heater-cost"]');
	const cfgEl = document.getElementById('hwm-hp-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const m = cfg.model;
		const form = root.querySelector('form');
		const stateEl = form.elements.namedItem('state');
		const kwhEl = form.elements.namedItem('kwh');
		const thermEl = form.elements.namedItem('therm');
		let last = null;
		for (const el of [kwhEl, thermEl]) el?.addEventListener('input', () => { el.dataset.edited = '1'; });
		stateEl?.addEventListener('change', () => {
			const p = placeFor(m, stateEl.value);
			if (kwhEl && !kwhEl.dataset.edited) kwhEl.value = String(p.usdPerKwh);
			if (thermEl && !thermEl.dataset.edited) thermEl.value = String(p.usdPerTherm);
		});
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				if (!last.result) return;
				const p = last.result.payback;
				track('calc_result', { tool: 'heat-pump-water-heater-cost', current: values.current, state: values.state, payback_bucket: p === null ? 'never' : p <= 3 ? '0-3' : p <= 7 ? '3-7' : p <= 13 ? '7-13' : '13+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const params = new URLSearchParams(window.location.search);
		const fromUrl = readState(form);
		if (fromUrl && params.has('state') && !params.has('kwh')) stateEl?.dispatchEvent(new Event('change'));
		if (params.has('kwh') && kwhEl) kwhEl.dataset.edited = '1';
		if (params.has('therm') && thermEl) thermEl.dataset.edited = '1';
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'heat-pump-water-heater-cost', referrer: document.referrer });
	}
}
