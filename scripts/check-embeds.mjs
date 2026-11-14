/**
 * Phase 8 task 3: check every /embed/<tool>/ route on a running site (CLAUDE.md §7b).
 * For each live or draft tool in data/pages.json:
 *   200 without a redirect; X-Robots-Tag and <meta robots> say noindex; framing allowed from any origin
 *   (CSP frame-ancestors *, no X-Frame-Options); no ad script; exactly one credit link, anchor = brand name,
 *   rel contains nofollow; total weight (HTML + stylesheet + module scripts and their imports, gzip -9) ≤ 36 KB (raised from 30 KB on 2026-11-14: input checks, point of use and propane added ~4 KB).
 * Also checks the /data/<id>.csv files: 200 text/csv with the expected row count while /data/ is draft or live,
 * 404 when it is only planned.
 * Usage: npm run check-embeds [-- --base=https://hotwatermath.com] [-- --bust]
 */
import { readFileSync } from 'node:fs';
import { gzipSync } from 'node:zlib';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const site = JSON.parse(readFileSync(join(root, 'data/site.json'), 'utf8'));
const { pages } = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8'));
const datasets = JSON.parse(readFileSync(join(root, 'data/computed/datasets.json'), 'utf8')).datasets;
const args = Object.fromEntries(process.argv.slice(2).map((a) => a.replace(/^--/, '').split('=')).map(([k, v]) => [k, v ?? true]));
const base = String(args.base || site.origin).replace(/\/$/, '');
const bust = args.bust ? `?cb=${Date.now()}` : '';
const LIMIT = 36 * 1024;

let fails = 0;
let passes = 0;
const ok = (cond, where, msg) => { if (cond) passes++; else { fails++; console.log(`FAIL ${where}: ${msg}`); } };
const get = (url) => fetch(url, { redirect: 'manual', headers: { 'user-agent': 'hwm-check-embeds' } });

async function weight(html, pageUrl) {
	const seen = new Set();
	let total = gzipSync(html, { level: 9 }).length;
	const importMap = JSON.parse((html.match(/<script type="importmap">(.*?)<\/script>/s) || [, '{"imports":{}}'])[1]).imports || {};
	const queue = [...html.matchAll(/<link rel="stylesheet" href="([^"]+)"|<script type="module" src="([^"]+)"/g)].map((m) => (m[1] || m[2]).replace(/&amp;/g, '&'));
	while (queue.length) {
		const url = new URL(queue.shift(), pageUrl).href;
		if (seen.has(url)) continue;
		seen.add(url);
		const body = await (await fetch(url)).text();
		total += gzipSync(body, { level: 9 }).length;
		if (url.endsWith('.js') || url.includes('.js?')) {
			for (const m of body.matchAll(/from\s+['"](\.\/[^'"]+)['"]/g)) {
				const name = m[1];
				queue.push(importMap[name] || new URL(name, url).href);
			}
		}
	}
	return { total, files: seen.size };
}

for (const p of pages.filter((x) => x.type === 'tool' && ['live', 'draft'].includes(x.status))) {
	const url = `${base}/embed/${p.slug}/${bust}`;
	const res = await get(url);
	const where = `/embed/${p.slug}/`;
	ok(res.status === 200, where, `status ${res.status}`);
	if (res.status !== 200) continue;
	const html = await res.text();
	const h = (n) => res.headers.get(n) || '';
	ok(/noindex/.test(h('x-robots-tag')), where, 'X-Robots-Tag lacks noindex');
	ok(/<meta name="robots" content="noindex/.test(html), where, 'meta robots lacks noindex');
	ok(/frame-ancestors \*/.test(h('content-security-policy')), where, 'CSP does not allow framing from any origin');
	ok(!h('x-frame-options'), where, `X-Frame-Options present (${h('x-frame-options')})`);
	ok(!/adsbygoogle|googlesyndication/.test(html), where, 'ad script found in embed');
	// Every link inside the frame is nofollow; exactly one is the credit, with the brand name as its anchor.
	const links = [...html.matchAll(/<a\s([^>]*)>([^<]*)<\/a>/g)];
	for (const l of links) ok(/rel="[^"]*nofollow/.test(l[1]), where, `link "${l[2].trim()}" lacks rel="nofollow"`);
	const credits = [...html.matchAll(/class="embed-credit">[^<]*<a\s([^>]*)>([^<]*)<\/a>/g)];
	ok(credits.length === 1, where, `${credits.length} credit lines (want 1)`);
	for (const c of credits) ok(c[2].trim() === site.brand, where, `credit anchor "${c[2].trim()}" is not the brand name`);
	const w = await weight(html, url);
	ok(w.total <= LIMIT, where, `weight ${(w.total / 1024).toFixed(1)} KB gzip > 36 KB`);
	console.log(`${where} ${(w.total / 1024).toFixed(1)} KB gzip (${w.files + 1} files)`);
}

// The snippet we hand out (on /embed-calculators/ and in "Embed this calculator"): one link, brand anchor, nofollow, says removable.
{
	const helpers = readFileSync(join(root, 'inc/helpers.php'), 'utf8');
	const fn = helpers.slice(helpers.indexOf('function hwm_embed_snippet'), helpers.indexOf('}', helpers.indexOf('function hwm_embed_snippet')));
	ok((fn.match(/<a /g) || []).length === 1, 'hwm_embed_snippet', 'snippet must contain exactly one link');
	ok(/rel="nofollow"/.test(fn), 'hwm_embed_snippet', 'snippet link lacks rel="nofollow"');
	ok(/hwm_site\( 'brand' \)\s*\)\s*\.\s*'<\/a>/.test(fn), 'hwm_embed_snippet', 'snippet anchor is not the brand name');
	ok(/optional; you may remove it/.test(fn), 'hwm_embed_snippet', 'snippet does not say the credit is removable');
}

const dataPage = pages.find((x) => x.slug === 'data');
for (const d of datasets) {
	const where = `/data/${d.id}.csv`;
	const res = await get(`${base}${where}${bust}`);
	if (['live', 'draft'].includes(dataPage?.status)) {
		ok(res.status === 200, where, `status ${res.status}`);
		if (res.status !== 200) continue;
		ok(/text\/csv/.test(res.headers.get('content-type') || ''), where, 'not text/csv');
		const lines = (await res.text()).trim().split(/\r?\n/);
		ok(lines.length === d.rowCount + 1, where, `${lines.length - 1} rows (want ${d.rowCount})`);
	} else {
		ok(res.status === 404, where, `/data/ is planned but the CSV answers ${res.status}`);
	}
}

console.log(`check-embeds: ${passes} passed, ${fails} failed`);
process.exit(fails ? 1 : 0);
