# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 3 — Tools 3 & 4: tank size calculator and repair-or-replace (2026-11-13).
**Next phase to run:** Phase 4 — Tools 5, 6, 7: Cost Calculator, Tankless vs Tank, Gas vs Electric (see `docs/PHASES.md`).

**What exists now (new or changed in Phase 3):**
- `content/water-heater-size-calculator.php` — tool page (draft): people preset + busy-hour counts + fuel + optional margin; H2s: how to size, household table vs American/Rheem, why FHR beats gallons (FHR table), worked example (family of 4), 40 vs 50, gas/electric/heat pump, mistakes, pro, limitations; FAQ 8; sources 9
- `content/repair-or-replace-water-heater.php` — tool page (draft): age, type, repair quote, new-unit price, 7 symptoms, energy prices; flowchart (`parts/decision-flowchart.php`, SVG), repair limits table, symptom table with manufacturer quotes, 3 worked examples, energy-saving table, questions to ask, pro, limitations; FAQ 6; sources 7
- `assets/js/tank-size.js`, `assets/js/repair-replace.js` — tool modules (`describe()` shared with build-data); `assets/js/age.js` now rewrites the "repair or replace" next-step link to `?age=&type=`
- `assets/js/formulas.js` — `tankSizing()`, `yearlyEnergyCost()`, `yearlySaving()`; `repairOrReplace()` returns `share`, `lifeUsed`, `oldFromYears` and handles `flooded`
- `data/household-size-guides.json` (new: American FHR by family size, Rheem gas sizes + worked example, A. O. Smith volumes); `data/peak-hour-demand.json` (+ `householdPresets`, `margin`, editorial); `data/decision-rules.json` (+ `defaultInputs`, `replacementPriceKey`, `upgradeTo`, `upgradeLabel`, `fuelOf`, `symptoms`)
- `scripts/build-data.mjs` — Phase 3 sections; INPUTS now include all tool modules; `data/computed/{water-heater-size-calculator,repair-or-replace-water-heater}.json`
- `content/methodology.php` — both cross-check tables filled (tank: 14 cases vs American, Rheem, A. O. Smith; repair: 6 cases vs Rheem guide and A. O. Smith manual), household presets and repair inputs explained
- `data/pages.json` (2 pages draft + SEO fields), `data/site.json` dateVerified, `assets/og/` 2 images, `docs/briefs/{water-heater-size-calculator,repair-or-replace-water-heater}.md`, 2 `docs/DECISIONS.md` rows, `tests/formulas.test.mjs` 43 tests, `style.css` 0.6.0

**Verified (before deploy):**
- `npm test` 43/43; `npm run validate-data` 42 files OK; `--urls` all data URLs 200.
- Offline render (PHP stub + Playwright, 360×640): no PHP notices, no JS errors, no horizontal scroll; first input top at 513 px on both pages. Prose words (no tables/FAQ): tank 1,103; repair 900.
- Hand-checked tank cases: 3 showers + hands/face + food prep = 60 + 4 + 5 = 69 gal → gas 50 (FHR 81), electric 55 (74), heat pump 65 (75); 2 showers + hands + food = 49 → gas 30, electric 40; 4 showers + hands + shave + food = 91 → gas 55, no electric tank, heat pump 80 (some models); 6-person preset 99 → gas 75 (FHR 100). Margin: 69 × 1.1 = 75.9 → 76.
- Hand-checked repair cases: 8-yr gas tank, $250 / $899 = 28% → borderline; 11-yr, $350 = 39%, 85% of life → replace; 4-yr electric, $150 / $819 = 18% → repair; leak or flood → replace at any age; 12-yr tankless (old from 15) → borderline; electric tank → heat pump saves $460/yr (55 gal/day, 67 °F, UEF 0.93 vs 3.8, $0.1723/kWh).
- Cross-checks: American FHR ranges 5/5 inside (presets tuned to them, stated); Rheem gas sizes 5/5 inside; A. O. Smith volume 3/3 for at least one fuel; Rheem worked example 77 vs 55 gal (+40%, per-use gallons differ, explained); repair 5/6 match Rheem/A. O. Smith, 1 stricter on purpose (explained).

**Verified live (after PR #13 deployed, 2026-11-13):**
- style.css 0.6.0 served; `npm run check` 250/250 with and without `--bust`.
- Both pages 200, `noindex, follow`, JSON-LD WebPage + BreadcrumbList + WebApplication + FAQPage (8 / 6 questions) parse.
- 360×640 live: first input top 514 px, no horizontal scroll, no console errors; share links work (`?people=5…` → 55-gallon gas, FHR 91+; `?age=12.4&type=electric_tank` → Replace).
- Lighthouse mobile (P/A/BP/SEO): tank size 100/100/100/66, repair 97/100/100/66; SEO 66 only from `is-crawlable` (draft noindex). CLS 0, LCP 0.95 s / 1.06 s.

**Known issues / TODOs carried forward:**
- Repair tool's new-unit price is equipment only until Phase 4 adds sourced labor/extras (Phase 4 should switch the default to the installed typical and update the methodology text and DECISIONS row).
- Gas/electric FHR sample is mostly Rheem; heat pump from ENERGY STAR. Household presets and the 10% margin are editorial.
- Angi (403), Direct Energy (host not resolvable) and hometips (503) were judged from search snippets.
- Carried: Navien Y/Z months third party; GE/Kenmore sections only; Rinnai not decoded; Hostinger CDN may need a flush after deploys; owner OK needed to flip any page to `live` (all tool pages are draft).

**Notes for the next thread (Phase 4):**
- The cost calculator should reuse `data/computed/unit-prices.json` and give the repair tool an installed replacement price (`decision-rules.json → replacementPriceKey` maps types to price keys).
- Tank size and repair pages list `water-heater-cost-calculator` and `gas-vs-electric-water-heater` in next steps/related; they show once those pages are draft/live.
- New JS modules must import `./formulas.js` and `./ui.js` (the import map versions them) and be listed in `INPUTS` in `scripts/build-data.mjs`.
- Offline render recipe: PHP stub defining ABSPATH/HWM_DIR/HWM_URI and the WP shims (esc_*, selected, checked, wp_json_encode, untrailingslashit, wp_parse_url, home_url, is_user_logged_in, hwm_jsonld/hwm_schema_faq no-ops), then `inc/data.php`, `inc/helpers.php`, `hwm_tool_content($slug)` and the template parts; Playwright `page.route` serves CSS/JS from the repo.

## Log
- 2026-11-09 — Project docs created (CLAUDE.md, STRATEGY.md, PHASES.md, HANDOFF.md). No code yet.
- 2026-11-09 — Strategy review v2: added SERP briefs, draft/live gating, embed widgets, Dataset/`/data`, troubleshooting cluster (6b), state pages (6c), authority phase (8), realistic traffic targets.
- 2026-11-12 — Phase 0 done — WordPress theme foundation, live on hotwatermath.com (PRs #1, #2).
- 2026-11-12 — Phase 1 done — sourced data layer (17 data files, 83/83 sources 200), formulas + tests, computed tables, full /methodology.
- 2026-11-13 — Phase 2 done — age calculator (Rheem code checked against Rheem/Ruud lookup, Navien via CPSC) and tankless size calculator (state inlet temps, size charts, Rinnai/Noritz/Rheem cross-checks); both draft.
- 2026-11-13 — Phase 2b (thread 1) done — lifespan article + Bradford White, A. O. Smith, Rheem, State age pages (draft), decoder checked against Rheem/Ruud lookup, Bradford White chart and CPSC recalls.
- 2026-11-13 — Phase 2b (thread 2) done — Whirlpool, American, Navien age pages (draft), GE/Kenmore sections on Rheem/State, decoder checked against CPSC recalls; Phase 2b complete.
- 2026-11-13 — Phase 3 done — tank size calculator and repair-or-replace tool (draft), cross-checked against American, Rheem and A. O. Smith guidance; age → repair link passes age and type.

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
