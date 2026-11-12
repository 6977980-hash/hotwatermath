/**
 * Build one 1200×630 Open Graph PNG per registry page (CLAUDE.md §6 "OG images"):
 * assets/og/<slug>.png (home → home.png) plus assets/og/default.png. Output is committed.
 * Only pages that are not "planned" get an image. Each file must stay under 200 KB.
 * Usage: npm run build:og
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { Resvg } from '@resvg/resvg-js';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const site = JSON.parse(readFileSync(join(root, 'data/site.json'), 'utf8'));
const { pages } = JSON.parse(readFileSync(join(root, 'data/pages.json'), 'utf8'));
const outDir = join(root, 'assets/og');
mkdirSync(outDir, { recursive: true });

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

/** Greedy word wrap by an approximate character budget per line. */
function wrap(text, maxChars, maxLines) {
	const words = String(text).split(/\s+/);
	const lines = [];
	let line = '';
	for (const w of words) {
		if ((line + ' ' + w).trim().length > maxChars && line) {
			lines.push(line);
			line = w;
		} else {
			line = (line + ' ' + w).trim();
		}
	}
	if (line) lines.push(line);
	if (lines.length > maxLines) {
		lines.length = maxLines;
		lines[maxLines - 1] = lines[maxLines - 1].replace(/\s+\S*$/, '') + '…';
	}
	return lines;
}

const mark = `<g transform="translate(80,80) scale(1.25)"><rect width="64" height="64" rx="14" fill="#0b4a7d"/><path d="M32 9c-9 11.5-17 21-17 30a17 17 0 0 0 34 0c0-9-8-18.5-17-30z" fill="#fff"/><rect x="23" y="34" width="18" height="4" rx="2" fill="#b8410b"/><rect x="23" y="42" width="18" height="4" rx="2" fill="#b8410b"/></g>`;

function card(title, subtitle) {
	const titleLines = wrap(title, 26, 3);
	const subLines = wrap(subtitle, 52, 2);
	const titleY = 250 + (3 - titleLines.length) * 36;
	const t = titleLines.map((l, i) => `<tspan x="80" y="${titleY + i * 72}">${esc(l)}</tspan>`).join('');
	const s = subLines.map((l, i) => `<tspan x="80" y="${titleY + titleLines.length * 72 + 10 + i * 40}">${esc(l)}</tspan>`).join('');
	return `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
<rect width="1200" height="630" fill="#ffffff"/>
<rect x="0" y="0" width="1200" height="12" fill="#0b4a7d"/>
<rect x="0" y="618" width="1200" height="12" fill="#b8410b"/>
${mark}
<text x="185" y="133" font-family="Liberation Sans, DejaVu Sans, Arial, sans-serif" font-size="36" font-weight="700" fill="#17212e">${esc(site.brand)}</text>
<text font-family="Liberation Sans, DejaVu Sans, Arial, sans-serif" font-size="64" font-weight="700" fill="#17212e">${t}</text>
<text font-family="Liberation Sans, DejaVu Sans, Arial, sans-serif" font-size="30" fill="#4a5668">${s}</text>
<text x="80" y="590" font-family="Liberation Sans, DejaVu Sans, Arial, sans-serif" font-size="26" fill="#0b4a7d">${esc(site.domain)}</text>
</svg>`;
}

const render = (svg) => new Resvg(svg, { fitTo: { mode: 'width', value: 1200 }, font: { loadSystemFonts: true, defaultFontFamily: 'Liberation Sans' } }).render().asPng();

let failed = false;
const jobs = [{ name: 'default', title: site.tagline, subtitle: site.description }];
for (const p of pages) {
	if (p.status === 'planned') continue;
	jobs.push({ name: p.slug === '' ? 'home' : p.slug, title: p.h1 || p.title, subtitle: p.subtitle || p.description || '' });
}
for (const job of jobs) {
	const buf = render(card(job.title, job.subtitle));
	writeFileSync(join(outDir, `${job.name}.png`), buf);
	const kb = Math.round(buf.length / 1024);
	if (buf.length > 200 * 1024) failed = true;
	console.log(`${kb > 200 ? 'TOO BIG ' : ''}assets/og/${job.name}.png ${kb} KB`);
}
if (failed) process.exit(1);
