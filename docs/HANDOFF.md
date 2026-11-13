# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 6b batch 2 — Troubleshooting (reset button, noise, runs out fast, rotten eggs) (2026-11-13). The 4 new pages are `draft` (noindex) until the owner OKs them. Phase 6's 7 pages went live in PR #28 (owner OK).
**Next phase to run:** Phase 6c — State pages (10 states), then Phase 6d (safety pages), per `docs/PHASES.md`.

**What exists now (new or changed in Phase 6b batch 2):**
- `content/water-heater-reset-button.php` — ECO explained, SVG location diagram, safe reset steps, A. O. Smith vs GE view, causes table, breaker vs reset table, gas/tankless, repair-or-replace embed, FAQ (6)
- `content/water-heater-making-noise.php` — 11-sound table with "Normal?", SVG cutaway, sections by sound, tankless + heat pump, hydrogen warning, repair-or-replace embed (noise ticked), FAQ (7)
- `content/hot-water-runs-out-fast.php` — triage questions, 9-cause table, size calculator embed, 10-state winter vs summer table, dip tube, lower element, recovery table, tankless, FAQ (6)
- `content/water-heater-smells-like-rotten-eggs.php` — gas leak first, SVG flowchart, smell-source table, fixes-by-duration table (sources disagree on anode metal), well water treatment by H2S level, age calculator embed, FAQ (6)
- `data/troubleshooting.json` — 11 new references (A. O. Smith electric manual 2000598248, noise pages, reheat/heat-up pages, water quality; Rheem noise guide; GE ECO; Stiebel Eltron sizing sheet; Penn State Extension; Kansas Corporation Commission), blocks `reset`, `noise`, `runsOut`, `smell`, tables `resetCauses`, `noiseSounds`, `runsOutCauses`, `smellSources`, `h2sTreatment`
- `assets/js/formulas.js` (+ hotShareOfMix, hotGallonsDrawn); `tests/formulas.test.mjs` +2 tests; `scripts/build-data.mjs` runs-out block + row-count guard; `data/computed/hot-water-runs-out-fast.json`
- `content/methodology.php` — section `#hot-water-runs-out-fast` (method of the winter vs summer table)
- `data/pages.json` 4 drafts; size calculator `problems` += runs out fast; repair-or-replace `problems` += noise; `assets/og/` 4 images; `style.css` 0.11.0
- `docs/briefs/` 4 briefs with quality check; `docs/DECISIONS.md` 1 row

**Verified (before deploy):**
- `npm test` 63/63; `validate-data` 57 files OK; `--urls`: all new sources 200 (only the known InspectApedia 409 and Watts 403 warnings); `build:data` fresh.
- Offline render (WP stubs): 0 PHP errors on the 4 pages; prose ~1,700–2,200 words each; hardcoded-number grep clean (digits only in document numbers and FAQ question wording).
- Winter vs summer table: Minnesota 37 → 59 °F inlet, 20.5 vs 18.9 gal per 10-min shower (+9%), 36% longer reheat; hand check 105/120/50 °F → 79% hot.

**Verified live:** see the Log line and the PR; filled in after deploy.

**Owner steps:**
1. Read the 4 drafts and reply OK to flip them `live` (quality gate §8).
2. Flush the Hostinger CDN after this deploy (formulas.js changed; import map versions it).
3. Carried: GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs.

**Known issues / TODOs carried forward:**
- No current manual we opened publishes the ECO trip temperature; the reset page says so and gives none.
- Angi, HomeServe (403) and hometips (TLS) could not be opened for the briefs.
- The A. O. Smith electric manual quoted is its electronic-thermostat model; its ECO and troubleshooting text matches the standard-tank info page.
- Carried: IRC P2903.4 text (403); labor = national ranges × BEA RPP; real-ads Lighthouse re-measure.

**Notes for the next thread:**
- To make these 4 pages live, flip `status` in `data/pages.json`; hubs, sitemap and /llms.txt pick them up.
- Offline render harness idea: stub WP functions (esc_*, selected, checked, add_action, is_user_logged_in → true, hwm_jsonld) and include `inc/data.php` + `inc/helpers.php`, then the content file.

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
