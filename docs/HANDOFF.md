# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 2 — Tools 1 & 2: Age Calculator + Tankless Size Calculator (2026-11-13)
**Next phase to run:** Phase 2b — Lifespan article + per-brand serial decoder pages, thread 1 (lifespan + Bradford White, A. O. Smith, Rheem, State) (see `docs/PHASES.md`)

**What exists now (new or changed in Phase 2):**
- `content/water-heater-age-calculator.php` — tool page: decoder inputs, all-brands summary table (brand cells link to the planned brand pages once they exist), rating-plate SVG, worked example, lifespan section, FAQ (7), sources (9)
- `content/tankless-water-heater-size-calculator.php` — tool page: state + fixtures + fuel inputs, gas and electric size charts, state inlet table, worked example (Minnesota), family-of-4 table by climate, electric reference table, FAQ (7), sources (11)
- `assets/js/age.js`, `assets/js/tankless-size.js` — calculator modules; each exports `describe()` (also used by build-data for the default result), URL-param state, copy/share, GA4 `calc_result` and `embed_view` (no-op until GA loads)
- `assets/js/formulas.js` — added `ageEstimate`, `smallestAtLeast`, `unitsNeeded`, `tanklessSizing`; PHYSICS + `litersPerGallon`, `fahrenheitPerCelsius`
- `data/computed/water-heater-age-calculator.json`, `data/computed/tankless-water-heater-size-calculator.json` — default result, worked examples, charts, cross-checks, `client` (inline JSON for the browser)
- `data/tankless-reference-units.json` (new) — Rinnai chart units, Noritz examples, Lochinvar guide, cold-climate rule
- `data/serial-formats.json` — Rheem plant-letter + week + year pattern (checked against Rheem/Ruud's lookup), Navien (CPSC), `brandOptions` dropdown with aliases, official lookups (Rheem, Ruud, Bradford White, Navien)
- `data/lifespans.json` — USGS hardness scale, `toolTypes`, Rheem's published lifespan/signs (`manufacturerViews`)
- `data/site.json` — `dateVerified` set for both tools (2026-11-13)
- `data/pages.json` — both tools `draft` with SEO fields + `scripts`; planned entries added: 10 brand pages (Phase 2b), tankless installation cost (6), gas line size and electrical requirements (6d)
- `content/methodology.php` — both manufacturer cross-check tables filled from computed data; age section notes aliases and undecoded brands
- `inc/helpers.php` — `hwm_link()` (links only to visible pages) and `hwm_tool_data_script()`
- `assets/css/global.css` — `.field-grid`, `.figure`, `[hidden]`
- `assets/og/water-heater-age-calculator.png`, `assets/og/tankless-water-heater-size-calculator.png`
- `docs/briefs/water-heater-age-calculator.md`, `docs/briefs/tankless-water-heater-size-calculator.md` — SERP briefs + quality check (sources table)
- `tests/formulas.test.mjs` — 33 tests; `style.css` Version 0.3.0; 5 new `docs/DECISIONS.md` rows

**Verified (before deploy):**
- `npm test` 33/33; `npm run validate-data` 30 files OK, computed fresh; `--urls` 90/90 source URLs 200 (2026-11-13).
- Offline render (PHP stub of the WP helpers) of both pages and /methodology: no PHP notices; Playwright at 360 px: no console errors, no sideways scroll, first input inside the first 640 px (age: brand select ends at 590 px; tankless: state select ends at 617 px).
- Prose word count excluding tables and FAQ: age 1,465, tankless 1,440.
- Hand-checked calculator cases (as of 2026-11-13):
  - Age: Rheem `Q431912345`, gas tank, moderate water, flushed → built Oct 2019, 7.1 yr, 55% of 13 → green. (Rheem/Ruud lookup: 10/18/2019.)
  - Age: Bradford White `SF1234567`, electric tank, hard water, never flushed → June 2018, 8.4 yr, life 13 − 1 − 1 = 11, 76% → amber, also 1998/1978.
  - Age: Navien `7410A18709ABCD`, tankless → July 2018 (CPSC: July 9, 2018).
  - Tankless: 2 showers × 2.5 GPM, 50 °F in, 120 °F out, UEF 0.95 → 5 GPM, 70 °F rise, 175,140 BTU/h output, 184,358 BTU/h input (one 199,000 unit), 51.3 kW / 214 A (more than one 36 kW unit).
  - Tankless: Minnesota coldest month 36.6 °F, 2 showers + kitchen faucet → 7.2 GPM, 83.4 °F rise, 316,295 BTU/h → 2 gas units; 88.1 kW electric → not realistic.
  - Tankless: 1 shower, 70 °F in → 2.5 GPM, 50 °F rise, 18.3 kW → RETEX-24 (18 kW too small).
- Manufacturer cross-checks (on /methodology): Rinnai RUR98i/RL94i/V65i flow at 70 and 50 °F rise within −0.9% to −2.5%; Noritz examples same GPM and rise (6 GPM case: our input is 3.2% above one 199,000 unit, Noritz picks one large unit — explained); Rheem RETEX-36 within 1%; age decoder matches Rheem/Ruud lookup (3/3), Bradford White table (2/2) and CPSC Navien example.
- Hardcoded-digit grep on both content files: only regulation names (10 CFR 430.32, 89 FR), years of sources (2024 rules, 1991–2020 normals), "24-hour test" (DOE test name), "50 states", question wording ("family of 4", "2 or 3 bathrooms"), "the digits 0 and 1" and source access dates. All data numbers render from data/computed files.
- Live (after deploy): see the follow-up note below once checked.

**Known issues / TODOs carried forward:**
- Navien months Y/Z (Nov/Dec) are inferred, not shown in an opened source (noted in data). Rinnai, Noritz, Takagi, Bosch not decoded (no source); Phase 2b Navien/Rinnai brand pages must find a manufacturer source first.
- Angi and propane.com block automated fetches (403); judged from search snippets in the tankless brief.
- STRATEGY §3.2 "125% breaker rule" not shown (no confirmed source for tankless); Phase 6d electrical page should settle it from the NEC/manual.
- Tankless gas sizes come from a 2016 Rinnai chart (Energy Factor era); refresh with a current spec sheet when convenient.
- Age default result and example are frozen "as of" `dateVerified`; re-run `npm run build:data` when `dateVerified` changes.
- Links to drafts/planned pages render as plain text for visitors (by design): Next-steps and Related cards stay empty for anonymous visitors until those pages are live.
- Carried from Phase 1: labor/extras costs missing (Phase 4); Hostinger CDN may need a flush after deploys.

**Notes for the next thread (Phase 2b):**
- Brand pages are registered as `planned` with `parent: water-heater-age-calculator`; flip to `draft` when built. The age page's summary table links each brand cell automatically once the page is visible.
- Brand page decoder: reuse `assets/js/age.js` (it reads `#hwm-age-data`); preset with `?brand=<id>` or by setting the select default in the content file.
- Rheem lookup endpoint used for checks: `https://www.ruud.com/media/getData.php?service=getWaterHeaterAge&SerialNumber=<serial>` (returns JSON with ManufacturingDate). Use sparingly, test serials only.
- Lifespan article must use `data/lifespans.json` (DOE 15/20 years averages, FEMP 13, Rheem 8–12 / 15–20+ in `manufacturerViews`).

## Log
- 2026-11-09 — Project docs created (CLAUDE.md, STRATEGY.md, PHASES.md, HANDOFF.md). No code yet.
- 2026-11-09 — Strategy review v2: added SERP briefs, draft/live gating, embed widgets, Dataset/`/data`, troubleshooting cluster (6b), state pages (6c), authority phase (8), realistic traffic targets.
- 2026-11-12 — Phase 0 done — WordPress theme foundation, live on hotwatermath.com (PRs #1, #2).
- 2026-11-12 — Phase 1 done — sourced data layer (17 data files, 83/83 sources 200), formulas + tests, computed tables, full /methodology.
- 2026-11-13 — Phase 2 done — age calculator (Rheem code checked against Rheem/Ruud lookup, Navien via CPSC) and tankless size calculator (state inlet temps, size charts, Rinnai/Noritz/Rheem cross-checks); both draft.

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
