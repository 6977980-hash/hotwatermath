/**
 * Data validation (PHASES.md Phase 1, task 2). Fails (exit 1) when:
 *   - a data file lacks meta.source (or meta.sourceUrl, except owner-maintained files);
 *   - any value anywhere is null (numbers must be real or the key must be left out);
 *   - a state-keyed file ("states") does not have exactly the 50 states + DC;
 *   - data/computed/ is stale: an input of npm run build:data changed after the last build.
 * With --urls it also requests every sourceUrl found in data/*.json and reports the HTTP status
 * (a source must have answered 200 at least once; results are recorded in docs/HANDOFF.md).
 *
 * Usage: npm run validate-data
 *        npm run validate-data -- --urls      (behind a proxy: NODE_USE_ENV_PROXY=1)
 */
import { readFileSync, readdirSync, existsSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { STATE_CODES } from './lib/states.mjs';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const checkUrls = process.argv.includes('--urls');
const NO_URL_OK = new Set(['pages.json', 'affiliate.json']); // registry and owner-maintained list
let errors = 0;
const err = (f, m) => { errors++; console.log(`FAIL ${f}: ${m}`); };

function walk(node, path, visit) {
	visit(node, path);
	if (node && typeof node === 'object') for (const [k, v] of Object.entries(node)) walk(v, `${path}.${k}`, visit);
}

const files = [
	...readdirSync(join(root, 'data')).filter((f) => f.endsWith('.json')).map((f) => `data/${f}`),
	...(existsSync(join(root, 'data/computed')) ? readdirSync(join(root, 'data/computed')).filter((f) => f.endsWith('.json')).map((f) => `data/computed/${f}`) : []),
];
const urls = new Map();

for (const f of files) {
	let d;
	try { d = JSON.parse(readFileSync(join(root, f), 'utf8')); } catch (e) { err(f, `invalid JSON (${e.message})`); continue; }
	const base = f.split('/').pop();
	const computed = f.includes('/computed/');
	if (!d.meta || !String(d.meta.source || '').trim()) err(f, 'missing meta.source');
	if (!computed && !NO_URL_OK.has(base) && !String(d.meta?.sourceUrl || '').trim()) err(f, 'missing meta.sourceUrl');
	if (!computed && !String(d.meta?.sourceDate || '').trim() && base !== 'pages.json') err(f, 'missing meta.sourceDate');
	walk(d, base, (v, p) => {
		if (v === null) err(f, `null value at ${p}`);
		if (typeof v === 'number' && !Number.isFinite(v)) err(f, `non-finite number at ${p}`);
	});
	if (d.states) {
		const keys = Object.keys(d.states).sort();
		const missing = STATE_CODES.filter((c) => !keys.includes(c));
		const extra = keys.filter((k) => !STATE_CODES.includes(k));
		if (missing.length || extra.length) err(f, `states: missing [${missing}] extra [${extra}]`);
	}
	if (!computed) walk(d, base, (v, p) => { if (/\.sourceUrl$/.test(p) && typeof v === 'string' && /^https?:\/\//.test(v)) urls.set(v, [...(urls.get(v) || []), `${base}${p.slice(base.length)}`]); });
}

// Stale computed data: compare input hashes with the manifest written by build:data.
const manifestPath = join(root, 'data/computed/_manifest.json');
if (!existsSync(manifestPath)) err('data/computed', 'no _manifest.json; run npm run build:data');
else {
	const m = JSON.parse(readFileSync(manifestPath, 'utf8'));
	for (const [input, h] of Object.entries(m.inputs)) {
		if (!existsSync(join(root, input))) { err('data/computed', `input ${input} no longer exists`); continue; }
		const now = createHash('sha256').update(readFileSync(join(root, input))).digest('hex').slice(0, 16);
		if (now !== h) err('data/computed', `stale: ${input} changed after the last build (run npm run build:data)`);
	}
	for (const o of m.outputs) if (!existsSync(join(root, `data/computed/${o}.json`))) err('data/computed', `missing output ${o}.json`);
}

if (checkUrls) {
	console.log(`Checking ${urls.size} source URLs…`);
	for (const [u, where] of urls) {
		let status = 'ERR';
		try {
			const res = await fetch(u, { redirect: 'follow', headers: { 'User-Agent': 'HotWaterMath-validate/1.0 (+https://hotwatermath.com/methodology/)' } });
			status = res.status;
		} catch (e) { status = `ERR ${e.cause?.code || e.message}`; }
		console.log(`${status === 200 ? 'OK  ' : 'WARN'} ${status} ${u}  (${where[0]}${where.length > 1 ? ` +${where.length - 1}` : ''})`);
	}
}

console.log(errors ? `\nvalidate-data: ${errors} problem(s)` : `validate-data: ${files.length} files OK`);
process.exit(errors ? 1 : 0);
