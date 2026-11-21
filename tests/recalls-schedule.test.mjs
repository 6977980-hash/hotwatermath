/**
 * Recall lookup and maintenance schedule (2026-11-21). Hand-checked against the CPSC notice text and the makers'
 * manuals quoted in data/maintenance.json. Run: npm test
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import * as F from '../assets/js/formulas.js';
import { describe as describeRl } from '../assets/js/recall-lookup.js';
import { describe as describeMs } from '../assets/js/maintenance-schedule.js';

const json = (f) => JSON.parse(readFileSync(new URL(`../data/${f}.json`, import.meta.url), 'utf8'));
const recalls = json('recalls').recalls;
const rl = json('computed/water-heater-recall-lookup').client;
const ms = json('computed/water-heater-maintenance-schedule').client;
const top = (v) => F.recallCheck(v, recalls).top;

test('normalizeCode strips spaces, dashes and dots', () => {
	assert.equal(F.normalizeCode(' fcg-75 300 '), 'FCG75300');
	assert.equal(F.normalizeCode('G6-UT3030NV'), 'G6UT3030NV');
});

test('Rheem 16177: 2014 serial is a match, 2017 serial is outside the range', () => {
	assert.equal(top({ brand: 'rheem', model: 'XE50T12EC55U0', serial: 'Q2314A12345' }), 'match');
	assert.equal(top({ brand: 'rheem', model: 'xe50t12ec55u0', serial: 'Q2517A12345' }), 'outside');
	assert.equal(top({ brand: 'rheem', model: 'XE50T12EC55U0', serial: 'A1215X' }), 'match'); // A1015 to A1615 range
});

test('A. O. Smith 19030: first four digits 1805 to 1809', () => {
	assert.equal(top({ brand: 'ao-smith', model: 'G6-UT3030NV', serial: '1807A1' }), 'match');
	assert.equal(top({ brand: 'ao-smith', model: 'G6-UT3030NV', serial: '1810A1' }), 'outside');
});

test('Model prefix (American Standard GN/GSN) and model with no serial rule', () => {
	assert.equal(top({ brand: 'american-standard', model: 'GSN-40', serial: '' }), 'match');
	assert.equal(top({ brand: 'aquastar', model: 'AQ38B NG', serial: '' }), 'match');
});

test('Navien 19057 needs the serial for a match; possible without it', () => {
	assert.equal(top({ brand: 'navien', model: 'NPE-180A', serial: '' }), 'possible');
	assert.equal(top({ brand: 'navien', model: 'NPE-180A', serial: '7410X18815ABCD' }), 'match');
	assert.equal(top({ brand: 'navien', model: 'NPE-180A', serial: '7410X19815ABCD' }), 'outside');
});

test('Brand-wide and part recalls: only on evidence', () => {
	// Ultra-Low NOx (19029): A. O. Smith serial 1420 is in 1115–1631 → possible; 1701 is not → same-brand list only.
	assert.equal(top({ brand: 'ao-smith', model: 'GCR-50', serial: '1420A00000' }), 'possible');
	const r = F.recallCheck({ brand: 'ao-smith', model: 'GCR-50', serial: '1701A00000' }, recalls);
	assert.equal(r.top, 'none');
	assert.ok(r.brandOnly.some((x) => x.id === '19029'));
	// Robertshaw R110 (05274): Rheem made 2005 → possible; made 2015 → not flagged.
	assert.equal(F.recallCheck({ brand: 'rheem', model: 'XG50T06EC38U1', serial: '', year: 2005 }, recalls).hits[0].recall.id, '05274');
	assert.equal(top({ brand: 'rheem', model: 'XG50T06EC38U1', serial: '', year: 2015 }), 'none');
	// Coaire covers all models.
	assert.equal(top({ brand: 'coaire', model: 'anything', serial: '' }), 'match');
});

test('recall describe(): no match never says safe; bad year is flagged', () => {
	const r = describeRl({ brand: 'rinnai', model: 'RU199iN', serial: '', madeYear: null }, rl);
	assert.equal(r.status, 'ok');
	assert.match(r.headline, /^No match in the \d+ U\.S\. CPSC/);
	assert.match(r.explain, /not proof of safety/);
	assert.equal(describeRl({ brand: 'rheem', model: 'X', serial: '', madeYear: 1900 }, rl).status, 'bad');
});

test('maintenance: gas tank installed Jan 2020 → first flush Jul 2020, valve test Jan 2021, plan replacement Jan 2030', () => {
	const rows = F.maintenanceSchedule({ type: 'gas_tank', installed: new Date(Date.UTC(2020, 0, 15)), water: 'normal' }, ms, new Date(Date.UTC(2020, 1, 1)));
	const due = (id) => rows.find((x) => x.id === id).due.toISOString().slice(0, 7);
	assert.equal(due('flush'), '2020-07');
	assert.equal(due('anode'), '2020-07');
	assert.equal(due('tp'), '2021-01');
	assert.equal(due('tpPro'), '2025-01');
	assert.equal(due('replace'), '2030-01'); // DOE low end, 10 years
});

test('maintenance: hard water halves the flush interval; last service moves the dates', () => {
	const asOf = new Date(Date.UTC(2026, 10, 21));
	const rows = F.maintenanceSchedule({ type: 'electric_tank', installed: new Date(Date.UTC(2022, 5, 15)), water: 'hard', lastService: new Date(Date.UTC(2025, 8, 15)) }, ms, asOf);
	const r = (id) => rows.find((x) => x.id === id);
	assert.equal(r('flush').everyMonths, 6);
	assert.equal(r('flush').due.toISOString().slice(0, 7), '2026-03');
	assert.equal(r('flush').status, 'overdue');
	assert.equal(r('tpPro').due.toISOString().slice(0, 7), '2027-06'); // counts from install
});

test('maintenance: tankless gets descale + inlet filter, no anode', () => {
	const rows = F.maintenanceSchedule({ type: 'gas_tankless', installed: new Date(Date.UTC(2024, 0, 15)), water: 'normal' }, ms, new Date(Date.UTC(2024, 1, 1)));
	assert.deepEqual(rows.map((x) => x.id).sort(), ['descale', 'inletFilter', 'replace']);
});

test('maintenance describe(): future install year and last service before install are flagged', () => {
	const asOf = new Date(Date.UTC(2026, 10, 21));
	assert.equal(describeMs({ type: 'gas_tank', installYear: 2027, installMonth: 1, water: 'normal', lastYear: null, lastMonth: 1 }, ms, asOf).status, 'bad');
	assert.equal(describeMs({ type: 'gas_tank', installYear: 2022, installMonth: 1, water: 'normal', lastYear: 2020, lastMonth: 1 }, ms, asOf).status, 'bad');
	const ok = describeMs({ type: 'gas_tank', installYear: 2024, installMonth: 1, water: 'normal', lastYear: 2026, lastMonth: 10 }, ms, asOf);
	assert.equal(ok.status, 'ok');
	assert.match(ok.headline, /^Next: /);
});
