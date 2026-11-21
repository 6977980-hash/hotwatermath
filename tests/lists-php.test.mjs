/**
 * The reminder emails (inc/lists.php, PHP) and the maintenance schedule page (formulas.js) must give the same dates.
 * Runs the PHP function with tiny WordPress stubs through the php CLI; skipped when php is not installed.
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import * as F from '../assets/js/formulas.js';

const root = new URL('..', import.meta.url).pathname;
let php = true;
try { execFileSync('php', ['-v']); } catch { php = false; }
const m = JSON.parse(readFileSync(new URL('../data/maintenance.json', import.meta.url), 'utf8'));
const life = JSON.parse(readFileSync(new URL('../data/lifespans.json', import.meta.url), 'utf8')).types;

function phpRows(profile, asOfIso) {
	const code = `define('ABSPATH','/');define('HWM_DIR',${JSON.stringify(root.replace(/\/$/, ''))});
function add_action(...$a){} function add_filter(...$a){} function wp_unslash($v){return $v;}
require HWM_DIR.'/inc/data.php'; require HWM_DIR.'/inc/lists.php';
echo json_encode(hwm_maintenance_rows(json_decode($argv[1],true), new DateTimeImmutable($argv[2], new DateTimeZone('UTC'))));`;
	return JSON.parse(execFileSync('php', ['-r', code, JSON.stringify(profile), asOfIso]).toString());
}

/** JS side: the first date in the task's series after asOf, built with formulas.js addMonths. */
function jsNext(profile, task, asOf) {
	const inst = new Date(Date.UTC(profile.installYear, profile.installMonth - 1, 15));
	const every = profile.water === 'hard' && task.hardEveryMonths ? task.hardEveryMonths : task.everyMonths;
	let k = 0;
	let due = F.addMonths(inst, task.firstMonths);
	while (due <= asOf) due = F.addMonths(inst, task.firstMonths + every * ++k);
	return due.toISOString().slice(0, 10);
}

for (const profile of [
	{ type: 'gas_tank', installYear: 2021, installMonth: 4, water: 'hard' },
	{ type: 'electric_tank', installYear: 2019, installMonth: 1, water: 'normal' },
	{ type: 'gas_tankless', installYear: 2024, installMonth: 12, water: 'softened' },
]) {
	test(`email dates = page formula: ${profile.type}, ${profile.installMonth}/${profile.installYear}, ${profile.water}`, { skip: !php && 'php not installed' }, () => {
		const asOfIso = '2026-11-21';
		const asOf = new Date(`${asOfIso}T00:00:00Z`);
		const rows = phpRows(profile, asOfIso);
		const fam = m.types[profile.type].family;
		for (const t of m.tasks.filter((x) => x.family === fam)) {
			assert.equal(rows.find((r) => r.id === t.id).nextIso, jsNext(profile, t, asOf), t.id);
		}
		const rep = rows.find((r) => r.id === 'replace');
		const want = F.addMonths(new Date(Date.UTC(profile.installYear, profile.installMonth - 1, 15)), Math.round(life[profile.type].low * 12)).toISOString().slice(0, 10);
		assert.equal(rep.nextIso, want);
	});
}

test('PHP month rule keeps the day or the last day of the month (like formulas.js addMonths)', { skip: !php && 'php not installed' }, () => {
	const out = execFileSync('php', ['-r', `define('ABSPATH','/');define('HWM_DIR',${JSON.stringify(root.replace(/\/$/, ''))});function add_action(...$a){} function add_filter(...$a){} require HWM_DIR.'/inc/data.php'; require HWM_DIR.'/inc/lists.php'; echo hwm_add_months(new DateTimeImmutable('2024-01-31'),1)->format('Y-m-d');`]).toString();
	assert.equal(out, '2024-02-29');
	assert.equal(F.addMonths(new Date(Date.UTC(2024, 0, 31)), 1).toISOString().slice(0, 10), '2024-02-29');
});
