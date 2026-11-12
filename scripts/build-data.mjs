/**
 * Build data/computed/*.json from assets/js/formulas.js + data/*.json (CLAUDE.md §5).
 * PHP renders these files; it never does calculator math.
 *
 * Phase 0: no calculator exists yet, so there is nothing to compute. Phase 1 adds one
 * builder per tool below (default result, pre-computed tables, per-state numbers) and
 * a freshness stamp so `npm run check` can fail when computed files are older than their inputs.
 * Usage: npm run build:data
 */
import { mkdirSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const outDir = join(root, 'data/computed');
mkdirSync(outDir, { recursive: true });

/** @type {Record<string, () => object>} tool slug → builder returning { default: {...}, tables: {...} } */
const builders = {};

const names = Object.keys(builders);
if (!names.length) {
	console.log('build:data: no calculators registered yet (Phase 0). Nothing to build.');
}
for (const name of names) {
	writeFileSync(join(outDir, `${name}.json`), JSON.stringify(builders[name](), null, 2) + '\n');
	console.log(`wrote data/computed/${name}.json`);
}
