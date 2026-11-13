# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 6d batch 1 — Safety pages: temperature setting, relief valve leaking, element replacement (2026-11-13). Owner OKed the 3 pages on 2026-11-13 ("ok live krdo"); they are `live` (style 0.13.1).
**Next phase to run:** Phase 6d batch 2 — gas line size for tankless, tankless electrical requirements, pilot light won't stay lit (per `docs/PHASES.md` and `docs/DECISIONS.md` 6d row).

**What exists now (new or changed in Phase 6d batch 1):**
- `content/water-heater-temperature-setting.php` — 120 vs 140 °F table, scald table (A. O. Smith + CPSC + gas dial), CDC Legionella section, hotter-tank capacity table, embedded tank size calculator, CPSC thermometer method, gas/electric/tankless adjustment (owner-level only), vacation + hydrogen warning, FAQ 7, sources 8
- `content/water-heater-pressure-relief-valve-leaking.php` — drip vs gush table, pressure scale SVG (50–60 / 80 / 112.5 / 150 psi), thermal expansion + embedded expansion tank calculator, annual test per label, no repair steps (Watts), part prices, discharge pipe rules table, FAQ 6, sources 6
- `content/water-heater-element-replacement.php` — electrical notice, symptom table, ohms table (computed), buying by data plate, prices, embedded repair-or-replace, A. O. Smith's own replacement steps (attributed), dry-fire rule, upper vs lower table, FAQ 6, sources 5
- `data/safety.json` — 12 references with quoted text (A. O. Smith gas + electric manuals, CPSC 5098, 4 CDC pages, DOE FEMP, Watts tag + spec sheet, 2 Home Depot searches), figures and 3 tables
- `assets/js/formulas.js` + `elementResistanceOhms`, `extraMixedWaterShare`; `tests/formulas.test.mjs` +2 tests
- `scripts/build-data.mjs` — 6d block: `data/computed/water-heater-{temperature-setting,pressure-relief-valve-leaking,element-replacement}.json`; guard: element description must quote the 4,500 W ohms and the rounded price range
- `parts/expansion-tank-inputs.php` — shared inputs (tool page output unchanged, md5 identical before/after)
- `content/water-heater-leaking.php` (live) — relief valve H2 now links the new page (plain text until it is live); `content/methodology.php` section `#safety-pages` + data file row
- `data/pages.json` 3 drafts (group Troubleshooting & Care); `assets/og/` 3 images; `style.css` 0.13.0; `docs/briefs/` 3 briefs with claim → source lists and quality check; `docs/DECISIONS.md` 1 row

**Verified (before deploy):**
- `npm test` 66/66; `validate-data` 74 files OK; `--urls`: every new source 200 (old known warnings only: InspectApedia 409, Watts ETP 403).
- Offline render (WP stubs): 0 PHP errors on the 3 pages, the expansion tank tool, leaking and methodology; 2,600–2,900 words each.
- Hand checks: 105 °F shower at 50 °F inlet: 55 ÷ 70 = 78.6% from a 120 °F tank, 55 ÷ 90 = 61.1% at 140 °F, 0.786 ÷ 0.611 − 1 = +29%; 240² ÷ 4,500 = 12.8 Ω, 4,500 ÷ 240 = 18.75 A; 150 psi × 75% = 112.5 psi.
- §8b grep: every imperative gas/electrical step sits in a list introduced as A. O. Smith's or the valve label's own procedure; no gas work steps; no valve repair steps.

**Verified live (after PR #34 deployed, 2026-11-13):** style.css 0.13.0 served; `/?sync=` 200; the 3 pages 200 with `noindex, follow`, self canonical, one H1. `npm run check -- --bust` 691/691; `npm run check-links -- --bust` 1350/1350 (one transient 307 on /privacy-policy/ right after the deploy, 200 on retry). Playwright 360x640: no horizontal scroll, no console errors; embedded calculators react (tank size 4 people → 55-gal gas; expansion tank 80 gal → 2-gal minimum, 4.4-gal safer; repair-or-replace updates).

**Owner steps:**
1. Done 2026-11-13: owner OKed the 3 safety pages; flipped `live`.
2. Flush the Hostinger CDN after this deploy (formulas.js changed).
3. Carried: GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs. PHASES 6d asks for GSC to show the tools indexed before these go live; GSC is not verified yet.
4. Later: the post-launch plumber fact-check (§8b) should include these 3 pages.

**Known issues / TODOs carried forward:**
- No sourced labor price for relief valve or element jobs; pages say so and send readers to the repair-or-replace calculator.
- Watts spec sheet is a distributor-hosted copy (watts.com 403 to scripts); text matches Watts' product pages.
- Batch 2 code text: the owner opened ICC in their Chrome; internal excerpts (copyrighted, never commit) are in `/mnt/project-files/code-data/` (IFGC 2021 Ch. 4 gas tables 402.4(1)-(16); IRC 2021 Ch. 37 + E4101.4.1 electrical rules with NEC refs). NFPA LiNK did not load. Quote only the rows a page needs, with table number and edition.
- Carried: IRC P2903.4 text (403); labor = national ranges × BEA RPP; real-ads Lighthouse re-measure.

**Notes for the next thread:**
- Batch 2 slugs are already `planned` in `data/pages.json` (gas line, electrical); add the pilot light entry. Put their facts in `data/safety.json` (same reference pattern) and reuse `parts/safety-notice.php`.
- Offline render harness: stub WP functions (esc_*, selected, checked, add_action, add_filter, is_user_logged_in, current_user_can, untrailingslashit, hwm_jsonld) and include `inc/data.php` + `inc/helpers.php`, then `hwm_content()`.
- `npm run build:og` rewrites some unrelated PNGs; restore them with `git checkout` before committing.

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
2026-11-13 — Phase 6d batch 1 pages live — owner OK; temperature setting, relief valve leaking, element replacement flipped live.
