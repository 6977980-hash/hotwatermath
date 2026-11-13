# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 6c — State pages, 10 states (2026-11-13). The 10 new pages are `draft` (noindex) until the owner OKs them. Phase 6b batch 2's 4 pages are also still `draft`, waiting for the owner's OK.
**Next phase to run:** Phase 6d — Safety-critical pages (built last), per `docs/PHASES.md`.

**What exists now (new or changed in Phase 6c):**
- `templates/state-cost.php` — shared template for `/water-heater-installation-cost/{state}/` (routed by `template: "state-cost"` in `inc/helpers.php`)
- `content/water-heater-installation-cost-state.php` — shared body: cost vs U.S., "What stands out" highlights, metro table, itemized gas + electric, calculator preset to the state, sourced state notes, running cost + 10-year cost, fuel mix, winter tankless + month-by-month table, rebate status, FAQ (6), sources (8–10). Section order follows the state's main fuel.
- `data/state-pages.json` — 10 states (Census Vintage 2025 rank), EIA RECS 2020 fuel shares, rebate status quoted from each official page (checked 2026-11-13), California strap-law note (Seismic Safety Commission)
- `data/metro-price-parities.json` + `scripts/fetch-metro-rpp.mjs` (`npm run fetch:metros`) — 5 largest metros per state with BEA metro RPP via FRED; skipped metros without a series are recorded
- `assets/js/formulas.js` + `monthlyMainsTemps`, `MID_MONTH_DAYS`, `MONTH_DAYS`; `data/groundwater-temp-by-state.json → correlation` + lag/sine terms (also in `scripts/fetch-sources.mjs`); `tests/formulas.test.mjs` +1 test
- `scripts/build-data.mjs` — state block: `data/computed/water-heater-installation-cost-<state>.json` ×10 and `data/computed/state-cost-index.json`; guards on description/quick-answer prices and ≥2 highlights
- `inc/pages-sync.php` — child pages: slug `a/b` → `post_name` b under parent a (parent must come first in the registry)
- `inc/seo.php`, `scripts/build-og.mjs` — OG file for a child slug uses `-` for `/`; `assets/og/` 10 images
- `content/water-heater-installation-cost.php` (live parent) — new table of the state pages (links show for visitors once a child is live); `content/methodology.php` section `#state-cost-pages`
- `scripts/check-links.mjs` — parent ↔ child check (child links parent in body; parent links live children, names draft ones)
- `data/pages.json` 10 drafts + group "Cost by State"; `style.css` 0.12.0; `docs/briefs/water-heater-installation-cost-states.md` (one brief for all 10, with quality check); `docs/DECISIONS.md` 2 rows

**Verified (before deploy):**
- `npm test` 64/64; `validate-data` 70 files OK; `--urls`: every new source 200 (47 FRED metro series, Census, EIA RECS, 8 state rebate pages, SSC); old known warnings only (InspectApedia 409, Watts 403).
- Offline render (WP stubs): 0 PHP errors on all 10 pages, parent and methodology; 2,600–2,850 words each.
- Uniqueness (state name masked): sentence/line diff 36–46% between every pair (acceptance ≥30%); strict word-level diff 13–43% (Texas vs Florida lowest).
- Hand checks: Texas 50-gal gas = $899 + ($650 + $260) × 0.971 = $1,782; Texas winter tankless 189,050 BTU/h ÷ (8.34 × 60 × 59.3 °F) = 6.4 GPM; heat pump saving/payback equal the heat pump calculator's state table (Texas $296/yr, 4.6 yrs).

**Owner steps:**
1. Read the 10 state drafts (and the 4 batch-2 drafts) and reply OK to flip them `live` (quality gate §8).
2. Flush the Hostinger CDN after this deploy (formulas.js changed).
3. Carried: GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs.

**Known issues / TODOs carried forward:**
- Rebate status could not be confirmed for New York (NYSERDA 403), Ohio (no official status page found) and Michigan's remaining funds (michigan.gov 403); the pages say so. Re-check every 3 months with `data/incentives.json`.
- Price differences between states are modest because only labor/permit/extras scale; the pages say the unit price is national.
- Cleveland, Dayton and Kiryas Joel–Poughkeepsie have no BEA metro RPP series (redrawn areas); named on the page.
- Carried: IRC P2903.4 text (403); labor = national ranges × BEA RPP; real-ads Lighthouse re-measure.

**Notes for the next thread:**
- To make the state pages live, flip `status` in `data/pages.json`; the parent's state table then links them and `/guides/` shows the "Cost by State" group. Add more states only after GSC shows these 10 indexed (PHASES 6c task 5): add a record to `data/state-pages.json`, a registry entry, run `npm run fetch:metros` and `npm run build:data`.
- Offline render harness: stub WP functions (esc_*, selected, checked, add_action, is_user_logged_in → true, hwm_jsonld) and include `inc/data.php` + `inc/helpers.php`, then `hwm_content()`.

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
