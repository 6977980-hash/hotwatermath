# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 4 — Tools 5, 6, 7: cost calculator, tankless vs tank, gas vs electric (2026-11-13).
**Next phase to run:** Phase 5 — Launch: home, hubs, interlinking audit, ads, QA, deploy (see `docs/PHASES.md`).

**What exists now (new or changed in Phase 4):**
- `content/water-heater-cost-calculator.php` — tool page (draft): type, size, state, job, extras checkboxes; itemized low/typical/high table; H2s: what drives the price, price by type and size, labor, extras, job types, by state, worked example, fair-quote checklist, mistakes, pro, limitations; FAQ 7; sources 6
- `content/tankless-vs-tank-water-heater.php` — tool page (draft): fuel, household, state, years, prices; 10-year totals for both fuels, household table, state break-even table, 20-year view; FAQ 6; sources 6
- `content/gas-vs-electric-water-heater.php` — tool page (draft): household, state, prices, setpoint, UEF per type; running cost of 5 types, by household and by state, EnergyGuide cross-check; FAQ 7; sources 6
- `content/repair-or-replace-water-heater.php` — new-unit price is now installed (label, hint, examples, limitations)
- `assets/js/cost-model.js` (shared model), `assets/js/{cost-calculator,tankless-vs-tank,gas-vs-electric}.js` (tool modules with `describe()`); `formulas.js` + `householdGallonsPerDay()`; `ui.js` + `renderTable()`; `tank-size.js` and `repair-replace.js` deep-link the cost calculator with `?type=&size=`
- `parts/result-table.php` (server-rendered result table), `parts/calculator.php` prints it; `assets/css/global.css` result table styles and mobile grid fix (`.calc` one `minmax(0,1fr)` column, `fieldset { min-width: 0 }`)
- `data/installation-costs.json` (new: labor, extras, scenarios, references to DOE 2024 rules + Home Depot parts); `inc/helpers.php` import map adds `cost-model`
- `scripts/build-data.mjs` — shared model, Phase 4 sections, cross-checks, Quick Answer guard; `data/computed/{water-heater-cost-calculator,tankless-vs-tank-water-heater,gas-vs-electric-water-heater}.json`; repair computed file uses installed prices
- `content/methodology.php` — installation-costs data row, cost/operating/TCO sections and three filled cross-check tables
- `data/pages.json` (3 pages draft + SEO fields), `data/site.json` dateVerified, `assets/og/` 3 images, `docs/briefs/` 3 briefs with Quality check, 5 `docs/DECISIONS.md` rows, `tests/formulas.test.mjs` 50 tests, `style.css` 0.7.0

**Verified (before deploy):**
- `npm test` 50/50; `npm run validate-data` 46 files OK; `--urls` 117/117 source URLs 200.
- Offline render (PHP stub + Playwright, 360×640): no PHP notices, no JS errors, no horizontal scroll on all 5 tool pages after the grid fix; first input top 572 px on the three new pages (513 on repair/tank). Prose words (no tables/FAQ): cost 1,538; tankless vs tank 1,096; gas vs electric 1,172; repair 1,375.
- Hand-checked cost: 50-gal gas, U.S. = 899 + 650 + 260 = $1,809 (range 1,249–2,599); electric 50 = $1,579; California gas 50 = 899 + 720 + 288 = $1,907; California gas → tankless = 1,108 + (850 + 260 + 500 + 600) × 1.107 = $3,555.
- Hand-checked running cost: 55 gal × 365 × 8.34 × 62 °F ÷ 3,412 ÷ 0.93 = 3,271 kWh × $0.1723 = $564 (electric tank); ÷ 100,000 ÷ 0.635 = 163 therms × $1.481 = $242 (gas tank); heat pump $138; Texas gas $243 / electric $392.
- Hand-checked ownership: gas 10 yr tank $4,230 vs tankless $4,937 (no payback), heat pump $4,927; electric 10 yr tank $7,215 vs heat pump $4,327 (year 4); gas 20 yr tankless pays back in year 13; 6-person gas home → heat pump saves $1,122 over 10 years.
- Consistency test: same state and household → same installed price on cost and TCO pages and same running cost on TCO and gas vs electric pages.
- Cross-checks (on /methodology): installed cost vs DOE 2024 (gas +26%, electric +75%, heat pump +59%, gas tankless +6%, explained: 2022/2023 dollars vs 2025 listings); running energy vs EnergyGuide labels within 1.3% (Rheem electric, gas, heat pump; American gas) and FEMP 0.7%; ownership vs DOE heat pump payback 3.2 vs 5.6 years and first-year costs (explained).

**Verified live (after PR #15 deployed, 2026-11-13):**
- style.css 0.7.0 served; `npm run check` 283/283 with and without `--bust`.
- All 3 new pages and repair: 200, `noindex, follow`, JSON-LD WebPage + BreadcrumbList + WebApplication + FAQPage; server-rendered defaults $1,809 installed / gas tank $242 vs electric $564 / tank cheapest over 10 years.
- 360×640 live: first input top 573 px (repair 514), no horizontal scroll, no console errors; share links work (`?type=electric_tank&state=CA…` → $1,661; `?fuel=electric` → heat pump saves $2,888; `?state=TX` → $243 vs $392).
- Lighthouse mobile (P/A/BP/SEO): 100/100/100/66 on all three; SEO 66 only from draft noindex. CLS 0, LCP 1.0–1.3 s.

**Known issues / TODOs carried forward:**
- Labor/extras ranges are editorial, anchored to DOE (2022/2023 dollars) and Home Depot parts; Angi/HomeAdvisor/BLS pages were blocked, so no current trade survey backs the "typical" labor values. Re-check when a primary labor source is found.
- Gas tankless running cost ignores fan/controls electricity (DOE includes it); stated on the page.
- Hubs and `/llms.txt` list `live` pages only, so the three new drafts appear there only after the owner's OK.
- Carried: tank size presets editorial; Navien Y/Z months third party; GE/Kenmore sections only; Rinnai not decoded; Hostinger CDN may need a flush after deploys; owner OK needed to flip any page to `live` (all tool pages are draft).

**Notes for the next thread (Phase 5):**
- Any new tool module must import `./formulas.js`, `./ui.js` or `./cost-model.js` by those exact paths (import map) and be listed in `INPUTS` in `scripts/build-data.mjs`.
- If you edit a Phase 4 Quick Answer in `data/pages.json`, `npm run build:data` will fail unless it still quotes the computed numbers.
- Offline render recipe as in Phase 3 (PHP stub + Playwright `page.route`), stub needs `apply_filters`, `wp_kses`, `is_front_page`, `is_page` shims too.

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
