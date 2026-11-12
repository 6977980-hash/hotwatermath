/**
 * Post-deploy SEO check against a running site (CLAUDE.md §6, PHASES.md Phase 0 task 11).
 *
 * For every registry page with status live or draft:
 *   exactly one <h1>; <title> present and ≤60 chars; meta description 140–155 chars;
 *   canonical = origin + /slug/ (trailing slash); ≥1 JSON-LD block that parses;
 *   BreadcrumbList + WebPage present; robots matches status (live → index, draft → noindex);
 *   no jQuery, emoji script, block-library CSS or external <script src> other than Google ad/analytics hosts.
 * Also: default WordPress URLs (author, category, tag, date, attachment, ?p=, feed, hello-world,
 * sample-page) must not be indexable (404, redirect, or noindex); non-slash URLs 301 to the slash URL;
 * /robots.txt points to the sitemap; the sitemap lists only live pages.
 *
 * Usage: npm run check                       (checks https://hotwatermath.com)
 *        npm run check -- --base=http://localhost:8080
 *        npm run check -- --bust              (adds a cache-busting query to each page request)
 */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const site = JSON.parse(readFileSync(join(root, 'data/site.json'), 'utf8'));
const { pages } = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8'));
const args = Object.fromEntries(process.argv.slice(2).map((a) => a.replace(/^--/, '').split('=')).map(([k, v]) => [k, v ?? true]));
const base = String(args.base || site.origin).replace(/\/$/, '');
const bust = Boolean(args.bust);
const allowedScriptHosts = ['pagead2.googlesyndication.com', 'www.googletagmanager.com', 'fundingchoicesmessages.google.com', 'www.google-analytics.com'];

let failures = 0;
let passes = 0;
const fail = (url, msg) => { failures++; console.log(`FAIL ${url} — ${msg}`); };
const pass = () => { passes++; };

async function get(url, opts = {}) {
	const res = await fetch(url, { redirect: 'manual', headers: { 'User-Agent': 'HotWaterMath-check/1.0', 'Cache-Control': 'no-cache' }, ...opts });
	const body = res.status >= 300 && res.status < 400 ? '' : await res.text();
	return { status: res.status, location: res.headers.get('location') || '', robotsHeader: res.headers.get('x-robots-tag') || '', body };
}

const attr = (tag, name) => (tag.match(new RegExp(`${name}\\s*=\\s*["']([^"']*)["']`, 'i')) || [])[1] ?? null;
const decode = (s) => s.replace(/&amp;/g, '&').replace(/&#0?39;/g, "'").replace(/&quot;/g, '"').replace(/&#8211;/g, '–').replace(/&ndash;/g, '–');

async function checkPage(entry) {
	const path = entry.slug === '' ? '/' : `/${entry.slug}/`;
	const url = base + path + (bust ? `?nocache=${Date.now()}` : '');
	const expectedCanonical = base + path;
	const r = await get(url);
	if (r.status !== 200) return fail(path, `HTTP ${r.status}`);
	const html = r.body;
	const head = html.split(/<\/head>/i)[0];

	const h1s = (html.match(/<h1[\s>]/gi) || []).length;
	h1s === 1 ? pass() : fail(path, `${h1s} <h1> elements`);

	const title = decode((head.match(/<title>([^<]*)<\/title>/i) || [])[1] || '');
	!title ? fail(path, 'missing <title>') : title.length > 60 ? fail(path, `title ${title.length} chars (>60): ${title}`) : pass();

	const descTag = (head.match(/<meta[^>]+name=["']description["'][^>]*>/i) || [])[0] || '';
	const desc = decode(attr(descTag, 'content') || '');
	!desc ? fail(path, 'missing meta description') : desc.length < 140 || desc.length > 155 ? fail(path, `description ${desc.length} chars (want 140–155)`) : pass();

	const canonTag = (head.match(/<link[^>]+rel=["']canonical["'][^>]*>/i) || [])[0] || '';
	const canonical = attr(canonTag, 'href');
	canonical === expectedCanonical ? pass() : fail(path, `canonical ${canonical} ≠ ${expectedCanonical}`);
	canonical && !canonical.endsWith('/') ? fail(path, 'canonical lacks trailing slash') : pass();

	const robotsTag = (head.match(/<meta[^>]+name=["']robots["'][^>]*>/i) || [])[0] || '';
	const robots = (attr(robotsTag, 'content') || '').toLowerCase();
	const wantNoindex = entry.status !== 'live';
	robots.includes('noindex') === wantNoindex ? pass() : fail(path, `robots "${robots}" wrong for status ${entry.status}`);

	const blocks = [...html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/gi)].map((m) => m[1]);
	if (!blocks.length) fail(path, 'no JSON-LD');
	const types = [];
	for (const b of blocks) {
		try { types.push(JSON.parse(b)['@type']); } catch { fail(path, 'JSON-LD does not parse'); }
	}
	['BreadcrumbList'].forEach((t) => (types.includes(t) ? pass() : fail(path, `missing ${t} schema`)));
	types.some((t) => /Page$/.test(t)) ? pass() : fail(path, 'missing WebPage-type schema');
	if (entry.type === 'home') ['WebSite', 'Organization'].forEach((t) => (types.includes(t) ? pass() : fail(path, `missing ${t} schema`)));
	if (entry.type === 'tool') ['WebApplication'].forEach((t) => (types.includes(t) ? pass() : fail(path, `missing ${t} schema`)));
	if (entry.type === 'article') ['Article'].forEach((t) => (types.includes(t) ? pass() : fail(path, `missing ${t} schema`)));
	if (entry.slug === 'about') types.includes('Person') ? pass() : fail(path, 'missing Person schema');
	if (/aggregateRating|"review"|reviewedBy/i.test(blocks.join(''))) fail(path, 'forbidden rating/review/reviewedBy schema');

	if (/jquery(\.min)?\.js|wp-emoji|wp-block-library|wp-embed(\.min)?\.js/i.test(html)) fail(path, 'WordPress bloat asset found (jQuery/emoji/block-library/embed)');
	for (const m of html.matchAll(/<script[^>]+src=["']([^"']+)["']/gi)) {
		const host = new URL(m[1], base).host;
		if (host !== new URL(base).host && !allowedScriptHosts.includes(host)) fail(path, `external script ${m[1]}`);
	}
	if (/fonts\.googleapis|fonts\.gstatic/i.test(html)) fail(path, 'external font request');
	if (/reviewed by/i.test(html.replace(/<[^>]+>/g, ' ')) && !/never show a "reviewed by" line|no "reviewed by" line/i.test(html)) fail(path, '"reviewed by" text found');

	const og = (head.match(/<meta[^>]+property=["']og:image["'][^>]*>/i) || [])[0];
	og ? pass() : fail(path, 'missing og:image');

	// Non-slash version must 301 to the slash version.
	if (entry.slug !== '') {
		const ns = await get(base + '/' + entry.slug);
		ns.status === 301 && ns.location.replace(/^https?:\/\/[^/]+/, '') === path ? pass() : fail('/' + entry.slug, `non-slash URL returned ${ns.status} → ${ns.location}`);
	}
}

async function checkNotIndexable(path) {
	const r = await get(base + path);
	const metaNoindex = /<meta[^>]+name=["']robots["'][^>]+noindex/i.test(r.body);
	if (r.status === 404 || r.status === 410 || (r.status >= 300 && r.status < 400) || metaNoindex || /noindex/i.test(r.robotsHeader)) return pass();
	fail(path, `indexable default WordPress URL (HTTP ${r.status})`);
}

const checked = pages.filter((p) => p.status === 'live' || p.status === 'draft');
console.log(`Checking ${checked.length} pages on ${base}${bust ? ' (cache-busting)' : ''}`);
for (const entry of checked) await checkPage(entry);

for (const p of ['/author/admin/', '/?author=1', '/category/uncategorized/', '/tag/test/', '/2026/', '/?p=1', '/?attachment_id=1', '/feed/', '/hello-world/', '/sample-page/', '/comments/feed/', '/?s=water']) {
	await checkNotIndexable(p);
}

// robots.txt and sitemap.
const robots = await get(base + '/robots.txt');
robots.status === 200 && /Sitemap:\s*\S+wp-sitemap\.xml/i.test(robots.body) ? pass() : fail('/robots.txt', 'missing or has no Sitemap line (is "Discourage search engines" on?)');
const sm = await get(base + '/wp-sitemap-posts-page-1.xml');
const smUrls = [...sm.body.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1]);
const liveUrls = pages.filter((p) => p.status === 'live').map((p) => base + (p.slug === '' ? '/' : `/${p.slug}/`));
for (const u of smUrls) (liveUrls.includes(u) ? pass() : fail('/wp-sitemap-posts-page-1.xml', `non-live URL in sitemap: ${u}`));
for (const u of liveUrls) (smUrls.includes(u) ? pass() : fail('/wp-sitemap-posts-page-1.xml', `live URL missing from sitemap: ${u}`));
for (const p of ['/wp-sitemap-users-1.xml', '/wp-sitemap-taxonomies-category-1.xml', '/wp-sitemap-posts-post-1.xml']) {
	const r = await get(base + p);
	r.status === 404 || (r.status >= 300 && r.status < 400) ? pass() : fail(p, `should not exist (HTTP ${r.status})`);
}

console.log(`\n${passes} checks passed, ${failures} failed.`);
process.exit(failures ? 1 : 0);
