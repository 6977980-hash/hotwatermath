/**
 * rebate-finder.js: heat pump water heater rebates by state. Lists the federal tax credit status, the state's
 * federal HEEHR program (data/heehr-states.json, from NASEO's launch table and each state page) and the utility
 * programs we checked (data/hpwh-rebates.json), and works out the HEEHR amount for an income band and project cost.
 * Math is in formulas.js (heehrRebate); data comes from <script type="application/json" id="hwm-rb-data">
 * (data/computed/heat-pump-water-heater-rebates.json → client). describe() is also used by scripts/build-data.mjs.
 */
import { heehrRebate } from './formulas.js';
import { readInputs, readState, writeState, renderResult, wireActions, track, debounce, formatCurrency, checkLimits, invalidResult } from './ui.js';

const usd = (v) => formatCurrency(v);

/**
 * @param {object} v Form values: state (two-letter code), income ('below80' | '80to150' | 'above150' | 'unsure'), cost ($, optional).
 * @param {object} cfg Client data.
 */
export function describe(v, cfg) {
	const bad = checkLimits(v, cfg.limits);
	if (bad.length) return { ...invalidResult(bad), result: null };
	const st = cfg.states.find((s) => s.id === v.state) || cfg.states.find((s) => s.id === cfg.defaults.state);
	const ourCost = cfg.costByState[st.id];
	const cost = Number.isFinite(v.cost) ? v.cost : ourCost.typical;
	const costNote = Number.isFinite(v.cost) ? `your project cost of ${usd(cost)}` : `our typical installed price in ${st.name}, ${usd(cost)} for a ${cfg.costLabel}`;
	const prog = cfg.heehr[st.id] || null;
	const rules = cfg.federal.incomeRules;
	const capRule = prog && prog.incomeCapId ? rules.find((r) => r.id === prog.incomeCapId) : null;
	const incomeId = v.income || cfg.defaults.income;
	const shownRule = rules.find((r) => r.id === incomeId) || null;
	// "Not sure": show the most a household could get (below 80% AMI) and say so.
	const rule = shownRule || rules[0];
	let share = rule.shareOfCost;
	if (capRule && rules.indexOf(rule) > rules.indexOf(capRule)) share = 0;
	const open = prog && prog.status === 'open';
	const heehr = open ? heehrRebate(cost, share, cfg.federal.cap) : 0;
	const utilities = cfg.utilities[st.id] || [];
	const bestUtility = utilities.reduce((a, u) => (Number.isFinite(u.maxUsd) && u.maxUsd > (a ? a.maxUsd : 0) ? u : a), null);

	const rows = [
		{ program: 'Federal tax credit (25C)', sponsor: 'IRS', amount: '$0', status: `Ended for heat pump water heaters installed after ${cfg.credit.endText}` },
		{ program: prog ? `HEEHR: ${prog.program}` : 'HEEHR (federal rebate run by your state)', sponsor: prog ? `${st.name} energy office` : '–', amount: open ? (heehr > 0 ? `Up to ${usd(heehr)}` : '$0 at this income') : '$0 now', status: prog ? (open ? `Open (launched ${prog.launched}). ${prog.eligibility}` : `Fully reserved. ${prog.statusText}`) : cfg.notListed },
		...utilities.map((u) => ({ program: u.offer, sponsor: u.sponsor, amount: Number.isFinite(u.maxUsd) ? `Up to ${usd(u.maxUsd)}` : 'Discount, amount not stated', status: `${u.conditions} Checked ${u.checkedText}.` })),
		{ program: 'Other utility and local rebates', sponsor: 'ENERGY STAR Rebate Finder', amount: 'Varies', status: 'Search by ZIP code for programs we have not checked' },
	];
	const table = { caption: `Heat pump water heater rebates in ${st.name}`, columns: [{ key: 'program', label: 'Program' }, { key: 'sponsor', label: 'Run by' }, { key: 'amount', label: 'Amount', num: true }, { key: 'status', label: 'Status and who qualifies' }], rows };

	const formula = [
		'Federal 25C credit = $0 (IRS: placed in service through December 31, 2025)',
		prog
			? open
				? `HEEHR = min(${usd(cfg.federal.cap)}, ${usd(cost)} × ${share * 100}%) = ${usd(heehr)}  (${rule.label.toLowerCase()}: ${rule.rule.toLowerCase()}${capRule && share === 0 ? `; ${st.name} serves only households ${capRule.label.toLowerCase()}` : ''})`
				: `HEEHR = $0: ${st.name}'s program says its rebates are fully reserved`
			: `HEEHR = $0: ${st.name} is not in NASEO's launch table`,
		utilities.length ? `Utility programs we checked in ${st.name}: ${utilities.map((u) => `${u.sponsor} ${Number.isFinite(u.maxUsd) ? `up to ${usd(u.maxUsd)}` : '(amount not stated)'}`).join('; ')}` : `Utility programs we checked in ${st.name}: none yet; use the ENERGY STAR finder by ZIP`,
		`Cost used: ${costNote}`,
	].join('\n');

	const parts = [];
	if (open && heehr > 0) parts.push(`${st.name}'s HEEHR program can pay up to ${usd(heehr)} toward ${costNote}, for a household ${rule.label.toLowerCase()}${shownRule ? '' : ' (the most any household can get; pick your income band for yours)'}.`);
	else if (open && share === 0) parts.push(`${st.name}'s HEEHR program does not cover a household ${rule.label.toLowerCase()}${capRule ? `; it serves only households ${capRule.label.toLowerCase()}` : ''}.`);
	else if (prog && !open) parts.push(`${st.name}'s HEEHR rebates are fully reserved: ${prog.statusText}`);
	else parts.push(`${st.name} has no HEEHR program in NASEO's launch table yet.`);
	if (bestUtility) parts.push(`${bestUtility.sponsor} offers up to ${usd(bestUtility.maxUsd)}${utilities.length > 1 ? `, the largest of ${utilities.length} utility programs we checked here` : ''}; you need to be its customer.`);
	else if (utilities.length) parts.push(`${utilities.map((u) => u.sponsor).join(' and ')} ${utilities.length > 1 ? 'offer' : 'offers'} a discount; the page does not name the amount.`);
	parts.push(`The federal tax credit ended on ${cfg.credit.endText}. Reserve a rebate before you buy: California's program, for one, pays nothing for equipment bought before the rebate is reserved.`);

	const total = heehr + (bestUtility ? bestUtility.maxUsd : 0);
	const status = total > 0 ? 'ok' : 'warn';
	const headline = open && heehr > 0
		? `Up to ${usd(heehr)} from ${st.name}'s HEEHR program${bestUtility ? `, plus up to ${usd(bestUtility.maxUsd)} from ${bestUtility.short}` : ''}`
		: bestUtility
			? `Up to ${usd(bestUtility.maxUsd)} from ${bestUtility.short} in ${st.name}`
			: `No heat pump water heater rebate we could confirm in ${st.name} right now`;
	return { status, statusLabel: total > 0 ? 'Rebates found' : 'Nothing confirmed yet', headline, explain: parts.join(' '), formula, table, result: { state: st.id, heehr, bestUtility: bestUtility ? bestUtility.maxUsd : 0, cost } };
}

/* ---------- browser wiring ---------- */
if (typeof document !== 'undefined') {
	const root = document.querySelector('.calc[data-tool="heat-pump-water-heater-rebates"]');
	const cfgEl = document.getElementById('hwm-rb-data');
	if (root && cfgEl) {
		const cfg = JSON.parse(cfgEl.textContent);
		const form = root.querySelector('form');
		let last = null;
		const run = (record) => {
			const values = readInputs(form);
			last = describe(values, cfg);
			renderResult(root, last);
			if (record) {
				writeState(values);
				if (!last.result) return;
				const h = last.result.heehr;
				track('calc_result', { tool: 'heat-pump-water-heater-rebates', state: last.result.state, income: values.income, heehr_bucket: h === 0 ? '0' : h < 1000 ? '<1000' : '1000+' });
			}
		};
		const deferred = debounce(() => run(true), 400);
		form.addEventListener('input', deferred);
		form.addEventListener('change', deferred);
		const fromUrl = readState(form);
		run(fromUrl);
		wireActions(root, () => (last ? `${last.headline}. ${last.explain} ${window.location.href}` : window.location.href));
		if (window.self !== window.top) track('embed_view', { tool: 'heat-pump-water-heater-rebates', referrer: document.referrer });
	}
}
