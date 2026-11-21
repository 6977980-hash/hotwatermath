/**
 * Re-read the U.S. CPSC recall database and check data/recalls.json against it.
 *  - FAIL when a field we copy word for word (title, date, url, units, hazard, remedy, contact) no longer matches
 *    the CPSC record, or a recall in our file is gone from CPSC.
 *  - LIST new CPSC records that mention "water heater" or "tankless" and are not in our file (neither kept nor in
 *    meta.excluded), for a person to read and add by hand (brands, models and serial rules need reading the notice).
 *
 * Usage: npm run fetch:recalls            (behind a proxy: NODE_USE_ENV_PROXY=1 npm run fetch:recalls)
 */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const data = JSON.parse(readFileSync(join(root, 'data/recalls.json'), 'utf8'));
const res = await fetch(data.meta.sourceUrl, { headers: { 'User-Agent': 'HotWaterMath-recalls/1.0 (+https://hotwatermath.com/methodology/)' } });
if (!res.ok) { console.log(`FAIL CPSC answered ${res.status}`); process.exit(1); }
const all = await res.json();
const byId = new Map(all.map((r) => [r.RecallNumber, r]));
let fails = 0;
for (const r of data.recalls) {
	const c = byId.get(r.id);
	if (!c) { fails++; console.log(`FAIL ${r.id}: no longer in the CPSC list`); continue; }
	const now = { title: c.Title, date: (c.RecallDate || '').slice(0, 10), url: c.URL, units: c.Products?.[0]?.NumberOfUnits, hazard: c.Hazards?.[0]?.Name, remedy: c.Remedies?.[0]?.Name || '', contact: c.ConsumerContact };
	for (const [k, v] of Object.entries(now)) if ((v ?? '') !== r[k]) { fails++; console.log(`FAIL ${r.id}.${k} changed:\n  ours: ${r[k]}\n  cpsc: ${v}`); }
}
const known = new Set([...data.recalls.map((r) => r.id), ...(data.meta.excluded || [])]);
const minYear = Math.min(...data.recalls.map((r) => Number(r.date.slice(0, 4))));
const fresh = all.filter((r) => !known.has(r.RecallNumber) && Number((r.RecallDate || '').slice(0, 4)) >= minYear && /water heater|tankless/i.test(`${r.Title} ${r.Description} ${JSON.stringify(r.Products)}`));
for (const r of fresh) console.log(`NEW? ${r.RecallNumber} ${(r.RecallDate || '').slice(0, 10)} ${r.Title}\n      ${r.URL}`);
console.log(`${data.recalls.length} recalls checked against ${all.length} CPSC records: ${fails} changed, ${fresh.length} new candidate(s) to read.`);
process.exit(fails ? 1 : 0);
