# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Audit fix Phase 1 — tools and calculators (2026-11-14), from the site audit of 2026-11-14 (`/mnt/project-files/hotwatermath/audit/2026-11-14/`). Style 0.16.0. Phase 8 pages (cost index, `/data/`, `/embed-calculators/`) are still `draft` waiting for the owner's OK.
**Next phase to run:** Audit fix Phase 3 — content and state pages (F-16/S-6 state duplicate text and rebates, F-29, F-17/18/19 copy, F-09/10/13/14). Brief: `/mnt/project-files/hotwatermath/audit/2026-11-14/handoff/phase-3-content.md`. Phases 2 (redirects), 4 (CSS/header/mobile) and 5 (security) are independent. Phase 7 still waits for GSC data.

**What changed in audit fix Phase 1:**
- `assets/js/formulas.js` — `decodeSerial(serial, patterns, today, minYear)` rejects future and pre-floor dates (F-05/F-28); `hotFlowGpm` + `tanklessSizing` size at each fixture's use temperature (F-01); `pointOfUseSizing` (S-8); `repairOrReplace` past-life + replace signs (F-08); propane in `annualEnergyUse`/`annualCost` (S-4); `costOfOwnership` residual value, `yearlyMaintenance` (F-03/S-2)
- `assets/js/ui.js` — `parseNumber`, `checkLimits`, `invalidResult`, `markInvalid`, per-result source line (F-02/F-12, F-06); `assets/css/global.css` field error styles
- `assets/js/{tankless-size,tank-size,age,repair-replace,expansion-tank,gas-vs-electric,tankless-vs-tank,heat-pump-savings,cost-model}.js` — validation in every tool, POU mode, custom shower flow and soaking tub, tankless brands preselect type (F-11), repair link keeps only a typed price (F-26), propane, heat pump location warning and rebate cap, simple payback
- New data: `data/input-limits.json`, `data/propane-prices.json`. Changed: `serial-formats.json` (A. O. Smith numeric, plausibility, tankless brand types), `fixture-flow-rates.json` (use temps, soaking tub, custom shower), `peak-hour-demand.json` (showerFlow, largeTub), `tankless-reference-units.json` (Eemax point-of-use units), `installation-costs.json` (maintenance), `decision-rules.json`, `heat-pump.json` (location), `brand-pages.json` (A. O. Smith example now numeric + A. O. Smith notice check), `pages.json` (2 quick answers, 1 description)
- Forms: `parts/tankless-inputs.php` (mode, POU fields, shower flow, soaking tub), `parts/tank-size-inputs.php` (shower flow, big tub), gas vs electric (gas fuel + propane), tankless vs tank (propane option + price, years maxlength 3), heat pump (location)
- Content: tankless size (method, worked example with hot share, limitations, 2 FAQs), tankless vs tank (value left, simple payback, upkeep), tankless installation cost (one sentence), A. O. Smith age (numeric format), methodology (input checks, sizing method, POU, tank-size inputs, age plausibility and flush default, repair rules, propane, ownership method, heat pump location and rebate cap)
- `scripts/build-data.mjs` — limits/pou/propane/maintenance in client data, Noritz cross-check at use temps with `unitsMatch`, per-brand source lines, new quick-answer guards for the tankless size and tankless vs tank pages; `tests/formulas.test.mjs` 78 tests

**Verified (before deploy):** `npm test` 78/78; `validate-data` 81 files OK; `build:data` + `build:charts` committed; `php -l` clean; offline render (WP stubs) 0 PHP notices on 16 changed or dependent pages. New sources opened 2026-11-14 and quoted in the data files (A. O. Smith SAP serial notice, Rinnai RU199 sizing note, Rheem sizing page, EIA propane price and BTU content, Eemax sizing guide and ProAdvantage spec sheet, Rinnai flush page, WebstaurantStore vinegar, Home Depot flush kit and anode listings, Rheem maintenance page, EPA WaterSense showerheads, ENERGY STAR HPWH guide).

**Verified live:** see the Log line and the PR thread (filled after deploy).

**Known issues / TODOs carried forward:**
- Gas tankless now comes out about $425 ahead of a gas tank over 10 years on total cost (value left counted: 20-year vs 13-year life) while its simple payback is about 22 years. The tool and article say both and show the cash spent; the owner may prefer one headline metric.
- State pages count "full-flow showers" a reference unit runs at the full 120 °F setpoint (not the 105 °F mix), so they are conservative next to the size calculator. Prose untouched per the brief; Phase 3 can align the wording.
- Upkeep is priced as do-it-yourself; a plumber's descaling visit costs more and has no listing we can cite.
- Heat pump cold-garage derate: no DOE/NEEA/ENERGY STAR figure found, so only a warning.
- Cost index: $1 rounding changes in 3 states (still draft).
- Carried: IRC P2903.4 text (403); labor = national ranges × BEA RPP; real-ads Lighthouse re-measure; codes.iccsafe.org not script-checkable.

**Owner steps:** flush the Hostinger CDN after this deploy (JS, CSS and data changed). Carried: OK the 3 Phase 8 drafts; GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs; outreach; post-launch plumber fact-check.

**Notes for the next thread:**
- Phase 3 shares `formulas.js` and `data/`: pull `main` first. Any number a page prints comes from `data/computed/`; rebuild with `npm run build:data` and keep the quick-answer guards green.
- Offline render harness: stub WP functions (esc_*, esc_textarea, selected, checked, wp_kses, add_action, add_filter, is_user_logged_in, current_user_can, untrailingslashit, home_url, wp_parse_url, wp_json_encode, wp_strip_all_tags, hwm_adsense_client, hwm_adsense_unit), define `HWM_DIR`, include `inc/data.php` + `inc/helpers.php`, then `hwm_content()`.
- Index refresh, build:charts and build:og notes from Phase 8 still apply.

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
