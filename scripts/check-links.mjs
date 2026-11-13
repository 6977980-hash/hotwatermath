/**
 * Internal-link and trust audit against a running site (PHASES.md Phase 5 tasks 3, 4 and 5b).
 *
 * For every registry page with status live or draft (fetched as a logged-out visitor):
 *   - every internal link in the page returns 200 directly (no 404, no redirect: links use the trailing-slash URL);
 *   - every live tool/article/hub page has ≥3 distinct internal links in <main> besides home, itself and the breadcrumb;
 *   - tool pages show the AuthorLine (methodology link) and the planning disclaimer;
 *   - embed snippets credit the brand name only, with rel="nofollow", one link;
 *   - every affiliate link (hosts from data/affiliate.json, plus known retailer hosts) has rel="sponsored nofollow noopener";
 *   - no page contains "reviewed by", "licensed plumber recommends", "find a pro" or "get quotes".
 * Hub coverage: every live core tool is linked from the home page and /calculators/, every live tool from /calculators/,
 * every live article from /guides/.
 *
 * Usage: npm run check-links                     (https://hotwatermath.com)
 *        npm run check-links -- --base=http://127.0.0.1:8080
 *        npm run check-links -- --bust           (cache-busting query on page requests)
 */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const site = JSON.parse(readFileSync(join(root, 'data/site.json'), 'utf8'));
const { pages } = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8'));
const affiliate = JSON.parse(readFileSync(join(root, 'data/affiliate.json'), 'utf8'));
const args = Object.fromEntries(process.argv.slice(2).map((a) => a.replace(/^--/, '').split('=')).map(([k, v]) => [k, v ?? true]));
const base = String(args.base || site.origin).replace(/\/$/, '');
const bust = Boolean(args.bust);
const origins = new Set([base, site.origin]);

let failures = 0;
let passes = 0;
const fail = (where, msg) => { failures++; console.log(`FAIL ${where} — ${msg}`); };
const ok = (cond, where, msg) => (cond ? passes++ : fail(where, msg));

const pathOf = (slug) => (slug === '' ? '/' : `/${slug}/`);
const decode = (s) => s.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#0?39;/g, "'");
const attr = (tag, name) => (tag.match(new RegExp(`\\s${name}\\s*=\\s*["']([^"']*)["']`, 'i')) || [])[1] ?? null;

async function get(url) {
	for (let attempt = 0; attempt < 3; attempt++) {
		try {
			const res = await fetch(url, { redirect: 'manual', headers: { 'User-Agent': 'HotWaterMath-check/1.0', 'Cache-Control': 'no-cache' } });
			const body = res.status === 200 && (res.headers.get('content-type') || '').includes('html') ? await res.text() : '';
			return { status: res.status, location: res.headers.get('location') || '', body };
		} catch (e) {
			if (attempt === 2) return { status: 0, location: '', body: '', error: String(e) };
		}
	}
}

/** Internal link targets (path only, hash and query dropped) found in an HTML fragment. */
function internalLinks(html) {
	const out = [];
	for (const m of html.matchAll(/<a\b[^>]*>/gi)) {
		const href = attr(m[0], 'href');
		if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) continue;
		let u;
		try { u = new URL(decode(href), base + '/'); } catch { continue; }
		if (!origins.has(u.origin)) continue;
		out.push(u.pathname);
	}
	return out;
}

// Affiliate links: hosts of the items in data/affiliate.json, any link already marked sponsored, and Amazon tag= links.
const affiliateHosts = new Set((affiliate.items || []).map((i) => { try { return new URL(i.url).host.toLowerCase(); } catch { return ''; } }).filter(Boolean));
const isAffiliate = (tag, u) => affiliateHosts.has(u.host.toLowerCase()) || /sponsored/i.test(attr(tag, 'rel') || '') || (/amazon\.|amzn\.to/.test(u.host) && u.searchParams.has('tag'));
const banned = [/reviewed by/i, /licensed plumber recommends/i, /find a pro\b/i, /get quotes/i];

const linkStatus = new Map();
async function checkTarget(path) {
	if (!linkStatus.has(path)) linkStatus.set(path, get(base + path).then((r) => r.status));
	return linkStatus.get(path);
}

const fetched = new Map();
const visible = pages.filter((p) => p.status === 'live' || p.status === 'draft');
for (const entry of visible) {
	const path = pathOf(entry.slug);
	const r = await get(base + path + (bust ? `?nocache=${Date.now()}` : ''));
	if (r.status !== 200) { fail(path, `HTTP ${r.status} ${r.error || ''}`); continue; }
	fetched.set(entry.slug, r.body);
	const html = r.body;
	const main = (html.match(/<main\b[\s\S]*<\/main>/i) || [''])[0];
	const body = main.replace(/<nav[^>]*class="[^"]*breadcrumb[^"]*"[\s\S]*?<\/nav>/i, '');

	// 1. Every internal link resolves with 200 (no redirect hop).
	const targets = [...new Set(internalLinks(html))].filter((p) => !p.startsWith('/wp-content/') && !p.startsWith('/wp-json/'));
	for (const t of targets) {
		const status = await checkTarget(t);
		ok(status === 200, path, `internal link ${t} → HTTP ${status}`);
	}

	// 2. ≥3 internal links in the body (live content pages; utility pages are exempt).
	if (entry.status === 'live' && ['tool', 'article', 'hub', 'home'].includes(entry.type)) {
		const inBody = new Set(internalLinks(body).filter((p) => p !== '/' && p !== path && !p.startsWith('/wp-content/')));
		ok(inBody.size >= 3, path, `only ${inBody.size} internal links in <main> body (need ≥3)`);
	}

	// 3. Trust line and disclaimer on every tool page.
	if (entry.type === 'tool') {
		const line = (html.match(/<div class="author-line">[\s\S]*?<\/div>/i) || [''])[0];
		ok(/Built by/.test(line) && /\/methodology\//.test(line), path, 'AuthorLine missing or lacks /methodology/ link');
		ok(/class="disclaimer"/.test(line), path, 'tool disclaimer missing');
	}

	// 4. Embed snippet: brand-only anchor, rel="nofollow", exactly one link.
	for (const m of html.matchAll(/<textarea[^>]*aria-label="Embed code"[^>]*>([\s\S]*?)<\/textarea>/gi)) {
		const snippet = decode(m[1]);
		const links = [...snippet.matchAll(/<a\b([^>]*)>([\s\S]*?)<\/a>/gi)];
		ok(links.length === 1, path, `embed snippet has ${links.length} links (want 1)`);
		for (const l of links) {
			ok(/rel="[^"]*nofollow/.test(l[1]), path, 'embed credit link lacks rel="nofollow"');
			ok(l[2].trim() === site.brand, path, `embed credit anchor "${l[2].trim()}" is not the brand name`);
		}
	}

	// 5. Affiliate links are qualified.
	for (const m of html.matchAll(/<a\b[^>]*>/gi)) {
		const href = attr(m[0], 'href') || '';
		let u;
		try { u = new URL(decode(href), base + '/'); } catch { continue; }
		if (!isAffiliate(m[0], u)) continue;
		const rel = (attr(m[0], 'rel') || '').split(/\s+/);
		ok(['sponsored', 'nofollow', 'noopener'].every((x) => rel.includes(x)), path, `affiliate link ${href} rel="${rel.join(' ')}"`);
	}

	// 6. Banned trust phrases (visible text and attributes).
	const text = html.replace(/<script[\s\S]*?<\/script>/gi, '');
	for (const re of banned) ok(!re.test(text), path, `contains banned phrase ${re}`);
}

// Hub coverage.
const linksOn = (slug) => new Set(internalLinks((fetched.get(slug) || '').match(/<main\b[\s\S]*<\/main>/i)?.[0] || ''));
const home = linksOn('');
const calc = linksOn('calculators');
const guides = linksOn('guides');
for (const p of pages.filter((x) => x.status === 'live')) {
	const path = pathOf(p.slug);
	if (p.type === 'tool') {
		ok(calc.has(path), '/calculators/', `does not link live tool ${path}`);
		if (!p.parent) ok(home.has(path), '/', `does not link live core tool ${path}`);
	}
	if (p.type === 'article') ok(guides.has(path), '/guides/', `does not link live article ${path}`);
}

console.log(`\n${passes} passed, ${failures} failed — ${visible.length} pages, ${linkStatus.size} distinct internal targets (${base})`);
process.exit(failures ? 1 : 0);
