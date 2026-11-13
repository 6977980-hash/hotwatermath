# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 6 — Expansion articles (2026-11-13). All 7 new pages are `draft` (noindex) until the owner OKs them.
**Next phase to run:** Phase 6b batch 2 (reset button, noise, runs out fast, rotten eggs), then the rest of Phase 6 / 6d per `docs/PHASES.md`.

**What exists now (new or changed in Phase 6):**
- `content/heat-pump-water-heater-cost.php` — cost by size, savings calculator, savings, payback table, bill lines, 2026 incentives, space, state table, mistakes, FAQ (7)
- `content/water-heater-installation-cost.php` — "where the money goes" receipt table, embedded cost calculator, by type/size, replacement vs installation, common jobs, state table, FAQ (6)
- `content/tankless-water-heater-installation-cost.php` — itemized switch, calculator preset to gas tank → tankless, job table, venting, gas line, electrical table (cost only), state table, FAQ (6)
- `content/gas-water-heater-installation-cost.php` — job table, itemized, quote checklist, calculator, electric → gas switch, state table, sizing, running cost, sourced safety notice, FAQ (6)
- `content/electric-water-heater-installation-cost.php` — calculator preset to 50-gal electric, job table, itemized, gas → electric, tank vs heat pump, 55-gal limit, state table, FAQ (6)
- `content/40-vs-50-gallon-water-heater.php` — side-by-side table, AHRI busiest-hour table, embedded size calculator, household table, running cost, FAQ (6)
- `content/water-heater-expansion-tank-size.php` — tool page: quick table, method, factors, tank sizes, precharge, Watts difference, prices, FAQ (6)
- `assets/js/heat-pump-savings.js`, `assets/js/expansion-tank.js`; `assets/js/formulas.js` (+ simplePaybackYears, netSavingOver, expansionFactor, designPressureFactor, expansionTankSizing)
- `data/heat-pump.json`, `data/expansion-tanks.json` (sourced, quotes + check dates); `data/computed/` 7 new files; `scripts/build-data.mjs` Phase 6 block + Quick Answer guards (now incl. 40 vs 50)
- `parts/cost-inputs.php` (preset), `parts/tank-size-inputs.php`; `parts/calculator.php` (`default` arg, embed block on tools only)
- `content/methodology.php` — sections `#heat-pump-water-heater-cost`, `#water-heater-expansion-tank-size` with cross-check tables, 2 data files, 2 assumptions
- `data/pages.json` 7 drafts; `data/site.json` dateVerified; `assets/og/` 7 images; `style.css` 0.10.0; `tests/formulas.test.mjs` +11 tests
- `docs/briefs/` 7 briefs; `docs/STRATEGY.md` §3.8 spec; `docs/DECISIONS.md` 2 rows

**Verified (before deploy):**
- `npm test` 61/61; `validate-data` 56 files OK; `--urls`: all new sources 200 except Watts (403 to Node, opened fine in a browser fetch the same day); `build:data` fresh.
- Offline render: 0 PHP errors on the 7 pages, both tools they embed and /methodology; prose words 1,277–1,869 (articles), 1,380 (tool). Hardcoded-number grep clean.
- Expansion tank: AMTROL worked example and all 45 quick-chart cells (37 same, 7 = our safer pick, 1 explained). Heat pump: ENERGY STAR and DOE cross-checks on /methodology.
- 40 vs 50 household fit changed to the size calculator's presets (40 gas = 3 people, 50 gas = 4) so the two pages agree.

**Verified live (after PR #24 deployed, 2026-11-13):**
- style.css 0.10.0 served; all 7 pages 200 with `noindex, follow`, one H1, no PHP errors; server-rendered defaults match the computed numbers.
- `npm run check -- --bust` 454/463 at first: all 9 failures were one empty response for /american-water-heater-age/ (a transient; 200 and a full page when re-fetched). `npm run check-links -- --bust` 820/821: the phrase "get quotes" on the tankless page tripped the banned-phrase rule; reworded in the follow-up PR.
- Playwright at 360x640: no horizontal scroll and no console errors on the 7 pages. The calculators react: heat pump vs gas → $104 a year and 16.7 years; tankless in California → $3,555; expansion tank at 80 gal / 80 psi → 2-gallon minimum, 4.4-gallon safer.

**Owner steps:**
1. Read the 7 drafts and reply OK to flip them `live` (quality gate §8).
2. Flush the Hostinger CDN after this deploy (new JS files and import-map versions).
3. Carried: GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs.

**Known issues / TODOs carried forward:**
- IRC P2903.4 (codes.iccsafe.org) returned 403, so the expansion tank page cites AMTROL and Washington DOH, not the code text.
- HomeServe, ConsumerAffairs and SupplyHouse (403), ENERGY STAR ask-the-experts (JS challenge) could not be opened for the briefs.
- Labor is national ranges × BEA RPP, not local quotes (stated on every cost page). Utility rebates are user-entered, not modelled.
- Carried from earlier phases: real-ads Lighthouse re-measure; Phase 4 items.

**Notes for the next thread:**
- To make the Phase 6 pages live, flip `status` in `data/pages.json`; hubs, sitemap and /llms.txt pick them up.
- Articles that embed a calculator use `hwm_part('cost-inputs', ['preset' => ...])` / `hwm_part('tank-size-inputs')` + `hwm_part('calculator', ['tool' => ..., 'default' => ...])`.

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
