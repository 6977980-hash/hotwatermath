# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 8 — Authority & links: cost index, `/data/`, embed landing page, outreach materials (2026-11-13). The 3 new pages are `draft` (noindex) until the owner OKs them (style 0.15.0). Phase 6d batch 2 pages (gas line size, tankless electrical, pilot light) are also still draft, waiting for the owner's OK.
**Next phase to run:** Phase 7 — Post-launch optimization, as soon as GSC is verified and has about 4 weeks of query data. Until then there is no further build phase in PHASES.md; good fill-ins on request: more Pinterest charts (`scripts/build-charts.mjs`, plan in OUTREACH.md §6), the repair-or-replace query-string fix below.

**What exists now (new or changed in Phase 8):**
- `content/water-heater-installation-cost-index.php` — cost index article: press-style key findings (5 bullets), inline SVG chart, top 5/bottom 5, full 51-row index + U.S. row, spread table, gas/electric/heat pump section, embedded cost calculator, method + limitations, chart embed code, cite box, FAQ 6, sources 6
- `content/data.php` — `/data/`: 12 datasets with columns, rows, how built, primary sources, CSV download, cite box, license, FAQ 4
- `content/embed-calculators.php` — `/embed-calculators/`: how to embed, rules (free, no ads, optional nofollow credit, never traded for a link), snippet per core tool, brand decoder URLs, privacy note, FAQ 4
- `data/datasets.json` (registry) → `data/computed/datasets.json`; `data/computed/water-heater-installation-cost-index.json` (build:data block "Phase 8", guards on quoted figures)
- `scripts/build-charts.mjs` (`npm run build:charts`) → `assets/charts/water-heater-cost-index.{svg,png,json}`
- `scripts/check-embeds.mjs` (`npm run check-embeds`) — embed route + snippet + CSV checks
- `inc/routes.php` `/data/<id>.csv` (404 while `/data/` is draft); `inc/schema.php` `hwm_schema_datasets()` (Dataset `@graph` on `/data/`, one Dataset on the index); `templates/embed.php` all in-frame links `nofollow` + new tab; `inc/helpers.php` snippet comment "credit optional"; `footer.php` Data/Embed links (only when live)
- `content/methodology.php` `#cost-index`; `content/water-heater-installation-cost.php` links the index (plain text until live)
- `assets/css/global.css` dataset/cite/chart/press styles; `data/pages.json` 3 drafts; `assets/og/` 3 images; `docs/briefs/water-heater-installation-cost-index.md`; `docs/DECISIONS.md` 2 rows; `docs/OUTREACH.md` rewritten (3 templates, 30 targets with contact method, Reddit, Pinterest plan per tool, tracking)

**Verified (before deploy):**
- `npm test` 69/69; `validate-data` 79 files OK; offline render (WP stubs) 0 PHP notices on the 3 pages, methodology and replacement cost; `php -l` clean.
- Sources opened 2026-11-13: BEA RPP PDF, EIA Table 2.10 (Idaho 11.83 ¢, Massachusetts 30.41 ¢ = our file), EIA gas prices, NOAA, DOE 89 FR 37778, Home Depot listings, ENERGY STAR data, AHRI worksheet, Lowe's Rinnai poster, Energy Trust decoder, CC BY 4.0: all 200. FRED IDRPPALL 2024 = 95.494 (file 95.5).
- Embeds on the old live code: all 200, noindex, `frame-ancestors *`, 23–28 KB gzip incl. CSS/JS; in-frame methodology/source links were followed links (fixed in this PR).
- Chart checked by eye (PNG); palette validated (CVD ΔE 9.2, contrast warn on aqua → table view + shapes).

**Owner steps:**
1. Read the 3 drafts (`/water-heater-installation-cost-index/`, `/data/`, `/embed-calculators/`) and the 3 Phase 6d batch 2 drafts; reply OK to flip them `live`. Pitch only after the index and `/data/` are live.
2. Flush the Hostinger CDN after this deploy (CSS and pages changed).
3. Outreach (OUTREACH.md): create LinkedIn page, X and Pinterest with identical branding and send Claude the URLs (they go into `data/site.json → social` → `Organization.sameAs`); sign up for HARO/Qwoted/Featured; send the first 20 pitches (start with targets 15, 16, 21, 30); post helpful Reddit answers weekly; log results monthly in the table below.
4. Carried: GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs. Phase 7 needs GSC data. Post-launch plumber fact-check of the 6 safety pages.

**Link tracking (monthly; owner fills, Claude can update on request)**

| Month | Followed editorial referring domains (new / total) | Pitches sent | Replies | Embeds seen in GA4 (`embed_view`, awareness only) | Notes |
|---|---|---|---|---|---|
| 2026-11 | 0 / 0 | 0 | 0 | — (GA4 not set up) | Assets built (draft) |

**Known issues / TODOs carried forward:**
- The index's "cheapest to own" assumes a home with gas service; stated on the page. Rebates excluded (they change often).
- `/data/` CSVs are `noindex` files; Google Dataset Search reads the `Dataset` markup on `/data/`. Validate it in the Rich Results Test / Schema validator once live.
- Embed chart image URL is the theme path (`/wp-content/themes/hotwatermath/assets/charts/…png`); keep the theme folder name stable.
- Carried: repair-or-replace query-string preset bug (`assets/js/repair-replace.js` ~116); IRC P2903.4 text (403); labor = national ranges × BEA RPP; real-ads Lighthouse re-measure; codes.iccsafe.org not script-checkable.

**Notes for the next thread:**
- Annual refresh of the index: update `data/energy-prices.json` / `data/regional-cost-index.json` when EIA/BEA release, then `npm run build:data && npm run build:charts`, bump `dateModified` of the index, and re-check the quick answer guard.
- `npm run build:charts` and `build:og` need `npm ci`; `build:og` rewrites some unrelated PNGs, restore them with `git checkout`.
- Offline render harness: stub WP functions (esc_*, selected, checked, add_action, add_filter, is_user_logged_in, current_user_can, untrailingslashit, home_url, wp_parse_url, wp_json_encode, wp_strip_all_tags, hwm_adsense_client, hwm_adsense_unit) and include `inc/data.php` + `inc/helpers.php`, then `hwm_content()`.

## Log
- 2026-11-09 — Project docs created (CLAUDE.md, STRATEGY.md, PHASES.md, HANDOFF.md). No code yet.
- 2026-11-09 — Strategy review v2: added SERP briefs, draft/live gating, embed widgets, Dataset/`/data`, troubleshooting cluster (6b), state pages (6c), authority phase (8), realistic traffic targets.
- 2026-11-12 — Phase 0 done — WordPress theme foundation, live on hotwatermath.com (PRs #1, #2).
- 2026-11-12 — Phase 1 done — sourced data layer (17 data files, 83/83 sources 200), formulas + tests, computed tables, full /methodology.
- 2026-11-13 — Phase 2 done — age calculator (Rheem code checked against Rheem/Ruud lookup, Navien via CPSC) and tankless size calculator (state inlet temps, size charts, Rinnai/Noritz/Rheem cross-checks); both draft.
- 2026-11-13 — Phase 2b (thread 1) done — lifespan article + Bradford White, A. O. Smith, Rheem, State age pages (draft), decoder checked against Rheem/Ruud lookup, Bradford White chart and CPSC recalls.
- 2026-11-13 — Phase 2b (thread 2) done — Whirlpool, American, Navien age pages (draft), GE/Kenmore sections on Rheem/State, decoder checked against CPSC recalls; Phase 2b complete.
- 2026-11-13 — Phase 3 done — tank size calculator and repair-or-replace tool (draft), cross-checked against American, Rheem and A. O. Smith guidance; age → repair link passes age and type.
- 2026-11-13 — Phase 4 done — cost calculator, tankless vs tank and gas vs electric tools (draft) on one shared cost model, checked against DOE 2024 rules and EnergyGuide labels; repair tool now uses installed prices.
- 2026-11-13 — Go-live — owner OK: 25 draft pages (tools, brand pages, article, hubs, utility pages) flipped to `live`.
- 2026-11-13 — Phase 5 done — launch home, hubs with ItemList schema, consent-gated GA4/AdSense loader, IndexNow key, check-links audit, OUTREACH playbook.
- 2026-11-13 — Go-live — owner OK: leaking, flush, anode rod, no hot water flipped to `live`.
- 2026-11-13 — Phase 6b batch 1 done — leaking, how to flush, anode rod and no hot water articles (draft), sourced troubleshooting data file, /guides/ groups, Common problems links on tools.
- 2026-11-13 — Phase 6 done — 7 expansion pages as drafts (heat pump cost + savings calculator, replacement, tankless, gas, electric installation cost, 40 vs 50, expansion tank calculator), AMTROL and ENERGY STAR cross-checks.
- 2026-11-13 — Phase 6b batch 2 done — reset button, noise, runs out fast and rotten eggs articles (draft), 11 new sourced references, winter vs summer shower table from formulas.js.
- 2026-11-13 — Go-live — owner OK: the 7 Phase 6 pages flipped to `live`.
- 2026-11-13 — Phase 6c done — 10 state cost pages (draft) on one shared template as child pages of the replacement cost article: state and metro prices, EIA fuel mix and running costs, monthly incoming water, official rebate status.
- 2026-11-13 — Phase 6d batch 1 done — temperature setting, relief valve leaking and element replacement (draft), sourced data/safety.json, scald/capacity/ohms tables, expansion tank and repair calculators embedded.
- 2026-11-13 — Phase 6d batch 2 done — gas line size, tankless electrical requirements and pilot light (draft), IFGC/IRC rows with Rinnai and Stiebel cross-checks, gas/208 V formulas, tankless inputs shared part.
- 2026-11-13 — Phase 8 done — cost index by state (51 rows, chart, CSV, embed code), /data/ with 12 CSV datasets + Dataset schema, /embed-calculators/, nofollow in-frame links, check-embeds script, OUTREACH.md rewritten (draft pages).

---

## Template (copy this into "Current State" when you finish a phase)

```
**Last completed phase:** Phase N — <name> (YYYY-MM-DD)
**Next phase to run:** Phase N+1 — <name>

**What exists now:** (paths, one line each; only add what changed or matters)
- ...

**Verified:**
- check/validate-data/build:data/check-links: pass/fail; PR merged + live site checked: yes/no
- Lighthouse mobile (page → P/A/BP/SEO): ...
- Schema validation: 0 errors on <pages>
- Hand-checked calculator cases: <inputs → expected → got> ×3 per tool

**Known issues / TODOs carried forward:**
- ...

**Notes for the next thread:**
- ...
```
- 2026-11-09 — v3: removed plumber reviewer and all referral/lead-gen; trust model = transparent methodology + manufacturer cross-check tables.
- 2026-11-13 — Domain finalised: hotwatermath.com, brand "Hot Water Math". Owner decision: follow our own strategy; do not model pages on waterheatercalc.com.
- 2026-11-12 — v4 (plan review): stack rewritten for custom WordPress theme on Hostinger; lifespan article split from age calculator; per-brand decoder pages (Phase 2b); embed link rules; AdSense compliance (ads.txt, consent, rel=sponsored); schema expectations corrected; safety pages moved to Phase 6d; Bing/IndexNow added; traffic targets lowered.
- 2026-11-13 — Owner decisions: founder LinkedIn added for E-E-A-T; Claude runs the per-page quality gate and source verification; owner handles outreach and post-launch plumber fact-check.
- 2026-11-13 — Owner instruction: one phase per new thread, PR auto-merged when the phase checklist passes, HANDOFF note for the next phase (CLAUDE.md §9, phase map in PHASES.md).
- 2026-11-12 — Phase 0 done — custom WordPress theme foundation: registry-driven pages, SEO/schema output, clean-up, utility pages, brand assets, check script.
- 2026-11-13 — Go-live — owner OK: 10 state cost pages (Phase 6c) and 4 troubleshooting pages (Phase 6b batch 2) flipped to live.
- 2026-11-13 — Phase 6d batch 1 pages live — owner OK; temperature setting, relief valve leaking, element replacement flipped live.
