/**
 * Build the cost index chart (Phase 8 linkable asset) from data/computed/water-heater-installation-cost-index.json:
 * assets/charts/water-heater-cost-index.svg (inlined on the index article) and a 2x PNG of the same chart
 * (assets/charts/water-heater-cost-index.png) for the "embed this chart" snippet and Pinterest. Output is committed.
 * Run after npm run build:data. Usage: npm run build:charts (needs npm ci for @resvg/resvg-js).
 * Palette: categorical slots 1-3 of the dataviz reference palette, checked with its validator (light surface);
 * each series also has its own marker shape, so color is never the only cue. The diamond is drawn first and larger,
 * so a gas circle at the same value stays visible inside it.
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const site = JSON.parse(readFileSync(join(root, 'data/site.json'), 'utf8'));
const idx = JSON.parse(readFileSync(join(root, 'data/computed/water-heater-installation-cost-index.json'), 'utf8'));
const outDir = join(root, 'assets/charts');
mkdirSync(outDir, { recursive: true });

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const usd = (v) => '$' + Math.round(v).toLocaleString('en-US');
const FONT = 'Liberation Sans, DejaVu Sans, Arial, sans-serif';
const SERIES = [
	{ key: 'h', color: '#1baf7a', shape: 'diamond', name: 'Heat pump' },
	{ key: 'g', color: '#2a78d6', shape: 'circle', name: 'Gas tank' },
	{ key: 'e', color: '#eb6834', shape: 'square', name: 'Electric tank' },
];
const { chart } = idx;
const W = 760;
const L = 156;
const R = 28;
const TOP = 112;
const ROW = 15;
const plotH = chart.rows.length * ROW;
const H = TOP + plotH + 78;
const max = Math.ceil(Math.max(...chart.rows.flatMap((r) => [r.g, r.e, r.h])) / 2000) * 2000;
const x = (v) => L + ((W - L - R) * v) / max;

const marker = (s, cx, cy) => {
	const ring = 'stroke="#ffffff" stroke-width="1.5"';
	if (s.shape === 'circle') return `<circle cx="${cx.toFixed(1)}" cy="${cy}" r="4" fill="${s.color}" ${ring}/>`;
	if (s.shape === 'square') return `<rect x="${(cx - 4).toFixed(1)}" y="${cy - 4}" width="8" height="8" rx="1.5" fill="${s.color}" ${ring}/>`;
	return `<path d="M${cx.toFixed(1)} ${cy - 7}l7 7-7 7-7-7z" fill="${s.color}" ${ring}/>`;
};

const parts = [];
parts.push(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}" role="img" aria-labelledby="hwm-ci-title hwm-ci-desc" font-family="${FONT}">`);
parts.push(`<title id="hwm-ci-title">${esc(`${chart.years}-year cost to own a 50-gallon water heater, by state`)}</title>`);
const first = chart.rows[0];
const last = chart.rows[chart.rows.length - 1];
parts.push(`<desc id="hwm-ci-desc">${esc(`Dot plot of installed price plus ${chart.years} years of energy for a gas tank, heat pump and electric tank in each state and DC, ranked from cheapest to own (${first.name}) to most expensive (${last.name}). The full numbers are in the table below the chart.`)}</desc>`);
parts.push(`<rect width="${W}" height="${H}" fill="#ffffff"/>`);
parts.push(`<text x="16" y="30" font-size="19" font-weight="700" fill="#17212e">${esc(`${chart.years}-year cost to own a 50-gallon water heater, by state`)}</text>`);
parts.push(`<text x="16" y="52" font-size="13" fill="#4a5668">${esc(`Installed price + ${chart.years} years of energy for a typical household. Ranked by the cheapest of the three.`)}</text>`);
// Legend (always present for 3 series; shapes repeat the color cue).
let lx = 16;
for (const s of SERIES) {
	parts.push(marker(s, lx + 6, 78));
	parts.push(`<text x="${lx + 16}" y="82" font-size="13" fill="#17212e">${esc(s.name)}</text>`);
	lx += 26 + s.name.length * 7.4 + 18;
}
// Grid + axis labels.
for (let v = 0; v <= max; v += 2000) {
	const gx = x(v).toFixed(1);
	parts.push(`<line x1="${gx}" y1="${TOP - 6}" x2="${gx}" y2="${TOP + plotH}" stroke="#e3e7ec" stroke-width="1"/>`);
	parts.push(`<text x="${gx}" y="${TOP + plotH + 18}" font-size="12" fill="#4a5668" text-anchor="middle">${v === 0 ? '$0' : '$' + v / 1000 + 'k'}</text>`);
}
chart.rows.forEach((r, i) => {
	const cy = TOP + i * ROW + ROW / 2;
	const vals = SERIES.map((s) => r[s.key]);
	parts.push(`<g><title>${esc(`${i + 1}. ${r.name}: gas tank ${usd(r.g)}, heat pump ${usd(r.h)}, electric tank ${usd(r.e)}`)}</title>`);
	if (i % 2 === 0) parts.push(`<rect x="0" y="${cy - ROW / 2}" width="${W}" height="${ROW}" fill="#f6f8fa"/>`);
	parts.push(`<text x="${L - 10}" y="${cy + 4}" font-size="11.5" fill="#17212e" text-anchor="end">${esc(`${i + 1}. ${r.name}`)}</text>`);
	parts.push(`<line x1="${x(Math.min(...vals)).toFixed(1)}" y1="${cy}" x2="${x(Math.max(...vals)).toFixed(1)}" y2="${cy}" stroke="#c5ccd4" stroke-width="2"/>`);
	for (const s of SERIES) parts.push(marker(s, x(r[s.key]), cy));
	parts.push('</g>');
});
const sy = TOP + plotH + 44;
parts.push(`<text x="16" y="${sy}" font-size="12" fill="#4a5668">${esc(`Data: BEA price parities, EIA ${idx.figures.dataYear.electric} energy prices, NOAA water temperatures, DOE 2024 cost rules, retail listings.`)}</text>`);
parts.push(`<text x="16" y="${sy + 20}" font-size="12" font-weight="700" fill="#0b4a7d">${esc(`${site.brand} · ${site.domain}/water-heater-installation-cost-index/`)}</text>`);
parts.push('</svg>');
const svg = parts.join('\n') + '\n';
writeFileSync(join(outDir, 'water-heater-cost-index.svg'), svg);
console.log(`assets/charts/water-heater-cost-index.svg ${Math.round(svg.length / 1024)} KB (${W}×${H})`);

const { Resvg } = await import('@resvg/resvg-js');
const png = new Resvg(svg, { fitTo: { mode: 'width', value: W * 2 }, font: { loadSystemFonts: true, defaultFontFamily: 'Liberation Sans' } }).render().asPng();
writeFileSync(join(outDir, 'water-heater-cost-index.png'), png);
writeFileSync(join(outDir, 'water-heater-cost-index.json'), JSON.stringify({ width: W * 2, height: H * 2, svgWidth: W, svgHeight: H }) + '\n');
console.log(`assets/charts/water-heater-cost-index.png ${Math.round(png.length / 1024)} KB (${W * 2}×${H * 2})`);
