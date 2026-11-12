/**
 * Build raster brand assets from assets/brand/icon.svg (CLAUDE.md §6 "Brand assets"):
 * logo-512.png (Organization.logo), icon-192.png, icon-512.png, apple-touch-icon.png (180),
 * favicon.ico (16/32/48, PNG-in-ICO). Output is committed; URLs must never change.
 * Usage: npm run build:brand
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { Resvg } from '@resvg/resvg-js';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const brand = join(root, 'assets/brand');
const svg = readFileSync(join(brand, 'icon.svg'), 'utf8');
// Apple touch icons are shown with the platform's own rounded mask, so use a square background.
const squareSvg = svg.replace(/rx="14"/, 'rx="0"');

const png = (source, size) => new Resvg(source, { fitTo: { mode: 'width', value: size } }).render().asPng();

const outputs = {
	'logo-512.png': png(svg, 512),
	'icon-512.png': png(svg, 512),
	'icon-192.png': png(svg, 192),
	'apple-touch-icon.png': png(squareSvg, 180),
};
for (const [name, buf] of Object.entries(outputs)) {
	writeFileSync(join(brand, name), buf);
	console.log(`wrote assets/brand/${name} (${buf.length} bytes)`);
}

// favicon.ico with PNG images (supported by all current browsers and by Google).
const sizes = [16, 32, 48];
const images = sizes.map((s) => png(svg, s));
const header = Buffer.alloc(6);
header.writeUInt16LE(0, 0);
header.writeUInt16LE(1, 2);
header.writeUInt16LE(images.length, 4);
let offset = 6 + 16 * images.length;
const entries = images.map((img, i) => {
	const e = Buffer.alloc(16);
	e.writeUInt8(sizes[i] % 256, 0);
	e.writeUInt8(sizes[i] % 256, 1);
	e.writeUInt8(0, 2);
	e.writeUInt8(0, 3);
	e.writeUInt16LE(1, 4);
	e.writeUInt16LE(32, 6);
	e.writeUInt32LE(img.length, 8);
	e.writeUInt32LE(offset, 12);
	offset += img.length;
	return e;
});
const ico = Buffer.concat([header, ...entries, ...images]);
writeFileSync(join(brand, 'favicon.ico'), ico);
console.log(`wrote assets/brand/favicon.ico (${ico.length} bytes)`);
