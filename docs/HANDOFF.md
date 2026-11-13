# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 6d batch 2 — Safety pages: gas line size for tankless, tankless electrical requirements, pilot light won't stay lit (2026-11-13). The 3 pages are `draft` (noindex) until the owner OKs them (style 0.14.0). Phase 6d is complete with this batch.
**Next phase to run:** Phase 8 — Authority & links (`/data` page, installation cost index article, embed checks, OUTREACH materials). Phase 7 needs GSC query data, and GSC is not verified yet, so it waits for the owner.

**What exists now (new or changed in Phase 6d batch 2):**
- `content/gas-line-size-for-tankless-water-heater.php` — gas + installer notices, BTU → CFH, size lookup tables at 0.5 and 3.0 in. w.c. (computed), Rinnai cross-check (12 of 14 match, 2 explained), IFGC 2021 Table 402.4(2)/(3) rows, Rheem meter example, propane/CSST/altitude, embedded tankless calculator (gas), FAQ 7, sources 5
- `content/tankless-water-heater-electrical-requirements.php` — electrical notice, Stiebel Eltron + Rheem unit table (draw per circuit, breaker vs draw), 125% rule placed correctly (IRC E3702.10 vs E4101.4.1), ampacity table 60/75 °C, service minimums, 208 V table, gas tankless 120 V, embedded tankless calculator (electric), FAQ 6, sources 5
- `content/water-heater-pilot-light-wont-stay-lit.php` — gas/vapor/CO notices, timing diagnosis table, A. O. Smith status light codes, label lighting steps (attributed), causes table, FVIR, part prices, embedded repair-or-replace, FAQ 7, sources 6
- `data/safety.json` — +13 references with quotes; blocks `gasLine`, `electrical`, `pilot`; tables `pilotStatus`, `pilotCauses`
- `assets/js/formulas.js` + `gasCfh`, `gasPipeSize`, `gasMaxLengthFt`, `powerAtVoltage`; `tests/formulas.test.mjs` +3 tests
- `scripts/build-data.mjs` — batch 2 block: `data/computed/{gas-line-size-for-tankless-water-heater,tankless-water-heater-electrical-requirements}.json`; guards: Rinnai mismatch list, 208 V check, description figures
- `parts/tankless-inputs.php` — shared tankless inputs with optional `fuel` preset (tool page output unchanged, md5 identical)
- `content/methodology.php` (#safety-pages batch 2 items, code quoting note, limitations); `content/no-hot-water.php` and `content/tankless-water-heater-installation-cost.php` link the new pages (plain text until live)
- `data/pages.json` 3 drafts; `assets/og/` 3 images; `docs/briefs/` 3 briefs with quality check; `docs/DECISIONS.md` 1 row

**Verified (before deploy):**
- `npm test` 69/69; `validate-data` 76 files OK; `--urls`: all new sources 200 except the two codes.iccsafe.org pages (403 to scripts, read in the owner's ICC view) plus old known warnings (InspectApedia 409, Watts ETP 403).
- Offline render (WP stubs): 0 PHP errors; gas ~2,900 words, electrical ~2,170, pilot ~2,660.
- Hand checks: 199,000 BTU/h ÷ 1,000 = 199 CFH → 0.5 in. w.c. table needs 1 in at 40 ft, 3/4 in at 20 ft; Rheem example 394,900 ÷ 1,024 = 386 CFH > 250 CFH meter; Tempra 24: 24,000 W ÷ 240 V = 100 A, 2 × 50 A circuits; 24 kW × (208/240)² = 18.0 kW (Stiebel publishes 18.0); RETEX-18: 37.5 A per circuit on 40 A = 107%.
- §8b grep: no gas or wiring steps; the only procedure is A. O. Smith's label lighting steps, attributed.

**Verified live (after PR #37 deployed, 2026-11-13):** style.css 0.14.0 served; `/?sync=` 200; the 3 pages 200 with `noindex, follow`, self canonical, one H1; tankless tool still `index`. `npm run check -- --bust` 730/730; `npm run check-links -- --bust` 1429/1429 (56 pages). Playwright 360x640: no horizontal scroll, no console errors on the 3 pages and the tankless tool; gas page embed defaults to gas (184,358 BTU/h; 1 shower → 92,179), electrical embed defaults to electric (51.3 kW / 214 A; 1 shower → 25.7 kW / 107 A); pilot embed has "no hot water" pre-ticked and updates (age 14 → Borderline).

**Owner steps:**
1. Read the 3 pages (draft) and reply OK to flip them `live`.
2. Flush the Hostinger CDN after this deploy (formulas.js changed).
3. Carried: GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs. Phase 7 needs GSC data.
4. Later: the post-launch plumber fact-check (§8b) should include all 6 safety pages; the gas and electrical pages ideally also by a licensed electrician/gas fitter.

**Known issues / TODOs carried forward:**
- codes.iccsafe.org and NFPA LiNK cannot be checked by script; IFGC/IRC rows were read in the owner's ICC view 2026-11-13. Full code excerpts stay in `/mnt/project-files/code-data/` (copyrighted, never commit).
- Rinnai FAQ lists RUC80i 3/4 in at 166 ft (our table lookup gives 250 ft); the page shows Rinnai's shorter figure and explains.
- No sourced labor price for pilot repairs; page says so.
- Repair-or-replace embeds: any query string (even `?utm_source=` or a cache-buster) counts as shared state and clears the pre-ticked symptom (`assets/js/repair-replace.js` line ~116, `ui.js readState` returns `params.size > 0`). Without a query string the preset works. Small fix for a later thread: only treat known field names as state.
- Carried: IRC P2903.4 text (403); labor = national ranges × BEA RPP; real-ads Lighthouse re-measure.

**Notes for the next thread:**
- When the 3 pages go live, the `hwm_link` mentions on no-hot-water and tankless installation cost turn into links automatically; consider adding the pilot slug to the repair tool's "problems" list.
- Offline render harness: stub WP functions (esc_*, selected, checked, add_action, add_filter, is_user_logged_in, current_user_can, untrailingslashit, hwm_jsonld, hwm_adsense_client, hwm_adsense_unit) and include `inc/data.php` + `inc/helpers.php`, then `hwm_content()`.
- `npm run build:og` needs `npm ci` first and rewrites some unrelated PNGs; restore them with `git checkout`.

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
