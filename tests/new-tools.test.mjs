/**
 * Tests for the model number lookup, rebate finder and quote checker (2026-11-20).
 * Hand-checked numbers are written out in the comments. Run: npm test
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import * as F from '../assets/js/formulas.js';
import { describe as describeMl, readModel, guessFromText } from '../assets/js/model-lookup.js';
import { describe as describeRb } from '../assets/js/rebate-finder.js';
import { describe as describeQc } from '../assets/js/quote-checker.js';

const json = (f) => JSON.parse(readFileSync(new URL(`../data/${f}.json`, import.meta.url), 'utf8'));
const mf = json('model-formats').brands;
const ml = json('computed/water-heater-model-number-lookup').client;
const rb = json('computed/heat-pump-water-heater-rebates').client;
const qc = json('computed/water-heater-quote-checker').client;
const model = json('computed/water-heater-cost-calculator').client.model;
const asOf = new Date('2026-11-20T12:00:00Z');

test('decodeModel: Rheem XG50T06EC38U1 → G, 50, T, 06, 38', () => {
	const r = F.decodeModel('xg50t06ec38u1', mf.rheem.patterns);
	assert.deepEqual(r.raw, { fuel: 'G', gallons: '50', height: 'T', warrantyYears: '06', series: 'EC', input: '38' });
	assert.equal(F.decodeModel('MR50245', mf.rheem.patterns), null);
	assert.equal(F.decodeModel('', mf.rheem.patterns), null);
});

test('readModel: Rheem heat pump, Bradford White size class and propane, Whirlpool code, American years', () => {
	const hp = readModel('XE50T10H45U1', 'rheem', ml).facts;
	assert.equal(hp.type, 'heat_pump');
	assert.equal(hp.input, '4,500 W'); // 45 × 100 W
	assert.equal(hp.warrantyYears, 10);
	const plug = readModel('XE65T10HM00U0', 'rheem', ml).facts;
	assert.equal(plug.input, 'Not in the model number');
	const bw = readModel('RG250L10X', 'bradford-white', ml).facts;
	assert.equal(bw.gallons, 50);
	assert.equal(bw.sizeClass, true);
	assert.equal(bw.warrantyYears, 10);
	assert.equal(bw.fuel, 'Propane');
	const wp = readModel('40T12-40NG', 'whirlpool', ml).facts;
	assert.equal(wp.warrantyYears, 12);
	assert.equal(wp.labor, 3);
	assert.equal(readModel('G101-50T40', 'american', ml).facts.warrantyYears, 10);
	assert.equal(readModel('E6N-30L', 'american', ml).facts.warrantyYears, 6);
});

test('warrantyEnd: June 2021 + 6 years → June 2027, 0.6 years left on 2026-11-20', () => {
	// (2027×12+5) − (2026×12+10) = 7 months = 0.58 → 0.6
	assert.deepEqual(F.warrantyEnd({ year: 2021, month: 6 }, 6, asOf), { year: 2027, month: 6, yearsLeft: 0.6 });
	// Unknown month → July. 2015 + 6 = July 2021, ended.
	assert.ok(F.warrantyEnd({ year: 2015, month: null }, 6, asOf).yearsLeft < 0);
});

test('model lookup describe: default Rheem gives build date, age and an ended warranty', () => {
	const r = describeMl({ brand: 'rheem', model: 'XG50T06EC38U1', serial: 'Q431912345', type: 'auto' }, ml, asOf);
	assert.equal(r.made.year, 2019);
	assert.equal(r.facts.gallons, 50);
	assert.equal(r.status, 'warn');
	assert.match(r.headline, /tank warranty over/);
	const none = describeMl({ brand: 'rinnai', model: 'RE180IN', serial: '', type: 'auto' }, ml, asOf);
	assert.equal(none.status, undefined);
	assert.match(none.explain, /no sourced model number key/);
});

test('guessFromText: finds model and serial after their labels', () => {
	const g = guessFromText(['RHEEM', 'MODEL NO. XG50T06EC38U1', 'SERIAL NO: Q431912345']);
	assert.equal(g.model, 'XG50T06EC38U1');
	assert.equal(g.serial, 'Q431912345');
});

test('heehrRebate: cap, half share, not eligible', () => {
	assert.equal(F.heehrRebate(4000, 1, 1750), 1750);
	assert.equal(F.heehrRebate(3000, 0.5, 1750), 1500);
	assert.equal(F.heehrRebate(3000, 0, 1750), 0);
	assert.equal(F.heehrRebate(null, 1, 1750), 0);
});

test('rebate finder: California reserved → $0; DC serves below 80% only; state with no program', () => {
	assert.equal(describeRb({ state: 'CA', income: 'below80', cost: 3000 }, rb).result.heehr, 0);
	assert.equal(describeRb({ state: 'DC', income: '80to150', cost: 3000 }, rb).result.heehr, 0);
	assert.equal(describeRb({ state: 'DC', income: 'below80', cost: 3000 }, rb).result.heehr, 1750);
	const tx = describeRb({ state: 'TX', income: 'below80', cost: 3000 }, rb);
	assert.equal(tx.result.heehr, 0);
	assert.equal(tx.result.bestUtility, 1000); // Austin Energy
	const bad = describeRb({ state: 'AZ', income: 'below80', cost: 50 }, rb);
	assert.equal(bad.status, 'bad');
});

test('quoteBand and lineBand', () => {
	const r = { low: 1000, typical: 1500, high: 2000 };
	assert.equal(F.quoteBand(900, r, 0.5).band, 'below');
	assert.equal(F.quoteBand(1500, r, 0.5).band, 'lowSide');
	assert.equal(F.quoteBand(1800, r, 0.5).band, 'highSide');
	assert.equal(F.quoteBand(2900, r, 0.5).band, 'above'); // ≤ 2000 × 1.5 = 3000
	assert.equal(F.quoteBand(3100, r, 0.5).band, 'farAbove');
	assert.equal(Math.round(F.quoteBand(1800, r, 0.5).vsTypical * 100), 20);
	assert.equal(F.lineBand(50, { low: 100, high: 200 }), 'low');
	assert.equal(F.lineBand(150, { low: 100, high: 200 }), 'in');
	assert.equal(F.lineBand(250, { low: 100, high: 200 }), 'high');
});

test('quote checker: no total waits; total gets a band that matches the cost model', () => {
	const cfg = { ...qc, model };
	const base = { type: 'gas_tank', size: '50', state: 'US', scenario: 'replace', permit: true };
	const wait = describeQc({ ...base, total: null }, cfg);
	assert.equal(wait.result.band, null);
	const r = describeQc({ ...base, total: 2500 }, cfg);
	// U.S. 50-gal gas with permit: 1,249 / 1,809 / 2,599 → 2,500 is fair, high side.
	assert.equal(r.result.band, 'highSide');
	assert.equal(describeQc({ ...base, total: 9000 }, cfg).result.band, 'farAbove');
	const lines = describeQc({ ...base, total: 2000, laborPrice: 2000 }, cfg);
	assert.match(lines.explain, /installation labor at \$2,000 is above our range/);
});
