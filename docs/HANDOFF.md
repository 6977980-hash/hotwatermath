# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Audit fix Phase 3 — content, trust and state pages (2026-11-14), from the site audit of 2026-11-14 (`/mnt/project-files/hotwatermath/audit/2026-11-14/`). Style 0.17.0. New page `/solar-water-heater-cost/` is `draft` waiting for the owner's OK; the 3 Phase 8 pages (cost index, `/data/`, `/embed-calculators/`) are still `draft` too.
**Next phase to run:** Audit fix Phase 4 — UX, mobile and accessibility (F-20, F-21, F-30). Brief: `/mnt/project-files/hotwatermath/audit/2026-11-14/handoff/phase-4-ux.md`. Then Phase 5 (speed and security, F-22 to F-25): `handoff/phase-5-security.md`. Phase 2 (index and redirects, including F-15) is independent. Phase 7 still waits for GSC data.

**What changed in audit fix Phase 3:**
- Copy fixes: garbled quotes (F-17, tank size + lifespan), "planned guides" (F-18), file names shown to readers (F-19: cost tables, decoder notes, `parts/decoder-checks.php`), "the 50 states and DC" everywhere (F-29, incl. the cost index quick answer)
- F-10: tank-size status names the sample size; the American guide check is called a consistency check, not an independent one (tool, build-data, methodology)
- F-09: one temperature rise (58 → 120 °F) for repair-or-replace and gas vs electric; `data/lifespans.json statusBands.oldFrom` is the single "old" threshold (repair tool, age tool sentence, methodology); 30- and 55-gallon gas and electric tanks priced in `data/cost-ranges.json` (Home Depot, 2026-11-14) so every tank-size result has a price
- F-13: electric tankless UEF from the Rheem RTEX spec sheet (`uef-ranges.json`); `tanklessCircuits` extra scaled for whole-house kW (`installation-costs.json`)
- F-14: `stationLabel` for AK/HI/DC (`groundwater-temp-by-state.json`, `hwm_state_label()` in `inc/helpers.php`, used in 4 state pickers); Honolulu rechecked against NOAA (methodology)
- F-16 + S-6: state pages rebuilt from data: `data/state-pages.json` gains sourced `notes` (permit and code facts, 1–2 per state with quote and check date) and a hand-written `local` section per state (tokens filled by build-data); `data/hpwh-rebates.json` (15 confirmed utility programs, 2 ended, 4 not checkable); state pages get a utility rebate table, a local section and shorter shared prose; build-data throws if a state has fewer than 3 unique sections. Jaccard (8-word shingles, numbers masked, `<main>` text): pairs 0.45–0.59 → 0.33–0.45, mean 0.51 → 0.37
- S-6 also: heat pump cost page has a rebate table by state and utility
- S-7: `parts/tankless-delay.php` (cold water sandwich + recirculation, Eemax white paper and Rinnai page, data in `tankless-reference-units.json hotWaterDelay`) on the tankless size and tankless vs tank pages
- S-10: lifespan by source table (DOE, AHRI, 3 makers, 3 cost guides; `lifespans.json costGuideViews`) on the lifespan article
- S-11: table of contents on `/methodology/` (H2 ids, tool sections) + `.toc` / `.source-quote` CSS
- S-12: `/solar-water-heater-cost/` (draft): `data/solar-water-heating.json` (DOE FEMP calculator method + city sun data, NREL 2012 cost range, IRS 25D ended, 3 cost guides), `formulas.solarWaterHeating` + `solarYearlySaving` (test reproduces FEMP's example), state payback table, brief `docs/briefs/solar-water-heater-cost.md`, STRATEGY keyword 12b, methodology section
- S-13: `parts/doe-2029.php` (89 FR 37778 quotes in `uef-ranges.json doe2029`) on repair or replace, gas vs electric, electric installation cost, heat pump cost, lifespan and solar pages
- `tests/formulas.test.mjs` 80 tests

**Verified (before deploy):** `npm test` 80/80; `validate-data` 84 files OK; `build:data` committed; `php -l` clean on every changed PHP file; offline render (WP stubs) 0 notices on 14 changed pages + all 10 state pages. Every new source opened on 2026-11-14 and quoted in its data file; sources that would not open (ilga.gov, Atlanta/Columbus/San Jose permit pages, Duke, ComEd, PECO, energy.gov Energy Saver solar pages, CCMS) are not used.

**Verified live (after PR #46 deployed, 2026-11-14):** style.css 0.17.0 served. `npm run check -- --bust` 778/778 (60 pages); `check-links -- --bust` 1565/1565 (a first run showed 4 transient 307/403 answers from Hostinger while three checks ran at once; rerun clean); `check-embeds -- --bust` 230/230. Playwright at 360×640 on 15 changed pages (home, methodology, heat pump, lifespan, repair, gas vs electric, electric install, tankless size, tankless vs tank, tank size, CA/GA/MI state pages, solar, age): no horizontal scroll, no page errors, TOC and source quotes render. `/solar-water-heater-cost/` serves `noindex, follow` (draft).

**Known issues / TODOs carried forward:**
- F-15 (redirect chain) not touched: redirects belong to Phase 2 per the brief.
- Electric tankless UEF is from one maker's spec sheet; CCMS was not reachable. Recheck against CCMS when it loads.
- Solar page: NREL's installed cost is a 2012 range (no newer public figure); FEMP has no city for Arkansas, Iowa, DC.
- Michigan's two utility programs do not publish an amount; shown as "amount not stated".
- Gas tankless total-cost vs payback headline (owner may pick one metric); DIY upkeep pricing; heat pump cold-garage derate; IRC P2903.4 (403); real-ads Lighthouse re-measure.

**Owner steps:** flush the Hostinger CDN after this deploy (CSS, JS and data changed). OK the solar draft. Carried: OK the 3 Phase 8 drafts; GSC Verify + sitemap, Bing, GA4 inside GTM, AdSense IDs; outreach; post-launch plumber fact-check.

**Notes for the next thread:**
- Pull `main` first. Numbers on pages come from `data/computed/`; rebuild with `npm run build:data` and keep the quick-answer guards green (the solar page now has one).
- State page prose lives in `data/state-pages.json` (`notes`, `local`) and `content/water-heater-installation-cost-state.php`; re-run the Jaccard check if you touch either.
- Offline render harness: stub WP functions (esc_*, esc_textarea, selected, checked, wp_kses, add_action, add_filter, is_user_logged_in, current_user_can, untrailingslashit, trailingslashit, home_url, wp_parse_url, wp_json_encode, wp_strip_all_tags, get_query_var, is_admin, apply_filters, hwm_adsense_client, hwm_adsense_unit), define `HWM_DIR`, include `inc/data.php` + `inc/helpers.php`, then `hwm_content()`.

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
- 2026-11-13 — Go-live — owner OK: gas line size, tankless electrical requirements and pilot light flipped to `live`.
- 2026-11-13 — Phase 8 done — cost index by state (51 rows, chart, CSV, embed code), /data/ with 12 CSV datasets + Dataset schema, /embed-calculators/, nofollow in-frame links, check-embeds script, OUTREACH.md rewritten (draft pages).

---
- 2026-11-14 — Audit fix Phase 1 done — tools: tankless sizing at the 105 °F mix, one input validator, ownership with value left/upkeep/simple payback, serial fixes, repair rules, propane, point of use, heat pump location (style 0.16.0).
- 2026-11-14 — Audit fix Phase 3 done — content and state pages: copy fixes, one rise and one "old" band, all tank sizes priced, state pages unique (Jaccard mean 0.37) with sourced code notes and utility rebates, tankless delay, lifespan sources, methodology TOC, DOE 2029 rule, solar cost page (draft).

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
- 2026-11-14 — GTM added — Google Tag Manager GTM-5K9PR6XR (deferred, consent defaults first, noscript iframe, no GTM on embeds); privacy policy and CLAUDE.md §2/§10 updated.
