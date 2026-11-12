# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 1 — Data layer + /methodology (2026-11-12)
**Next phase to run:** Phase 2 — Tools 1 & 2: Age Calculator + Tankless Size Calculator (see `docs/PHASES.md`)

**What exists now (new or changed in Phase 1; Phase 0 files unchanged unless listed):**
- `assets/js/formulas.js` — all STRATEGY §3 formulas implemented as pure functions; `PHYSICS` constants (incl. `wattsPerKw`)
- `tests/formulas.test.mjs` — 27 tests, one or more per formula incl. edge cases, plus "every serial example in the data decodes" (`npm test`)
- `scripts/build-data.mjs` — `npm run build:data` → `data/computed/*.json` + `_manifest.json` (input hashes for the stale check)
- `scripts/validate-data.mjs` — `npm run validate-data` (meta source/url/date, no nulls/non-finite, 51 state keys, stale computed data); `-- --urls` requests every sourceUrl
- `scripts/fetch-sources.mjs` + `scripts/lib/states.mjs` — `npm run fetch:sources -- eia noaa rpp energystar` regenerates the four generated data files from primary sources (needs `NODE_USE_ENV_PROXY=1` behind the proxy)
- Data files (all `sourceDate` 2026-11-12):
  - `data/site.json` — defaults: setpoint 120 °F (DOE FEMP), $0.1723/kWh (EIA EPA table 2.10, 2025), $1.481/therm (EIA dnav 2025 $/Mcf ÷ heat content); `dateVerified` per tool (all empty until each tool's cross-check runs)
  - `data/fixture-flow-rates.json` — fixture GPM: 10 CFR 430.32 maximums, EPA WaterSense low-flow, PNNL BASC (ASHRAE) appliance/tub ranges; DOE example (UNT archive 2017)
  - `data/peak-hour-demand.json` — gallons per use for the peak-hour worksheet: AHRI 2011 worksheet (example = 70 gal)
  - `data/fhr-to-tank-size.json` — FHR per gas/electric tank model: Rheem Performance spec sheets (THD-PGRH, PGHD, PE06, PEDR6)
  - `data/energystar-water-heaters.json` — generated: ENERGY STAR certified list summary (heat pump FHR by size, UEF ranges, gas tankless max GPM at 67 °F)
  - `data/uef-ranges.json` — UEF by type (spec sheets + ENERGY STAR) and federal minimum coefficients (10 CFR 430.32(d)), electric-resistance 55 gal limit
  - `data/hot-water-use.json` — DOE Appendix E draw patterns and test conditions (58/125 °F); FEMP 55 gal/day; per-person use (DOE Data Book 8.2.4, archived)
  - `data/groundwater-temp-by-state.json` — generated: NOAA 1991–2020 air normals per state + Christensen–Burch correlation (EnergyPlus Engineering Reference)
  - `data/energy-prices.json` — generated: EIA residential electricity (2025) and gas price + heat content by state (6 states use 2024 gas, EIA withheld 2025)
  - `data/regional-cost-index.json` — generated: BEA RPP all items 2024 by state (via FRED)
  - `data/lifespans.json` — lifespans by type (DOE 2024 rules, FEMP) + editorial adjustments and status bands
  - `data/serial-formats.json` — date-code patterns: Bradford White, Rheem, A. O. Smith, State, American (Energy Trust of Oregon decoder; Bradford White)
  - `data/cost-ranges.json` — equipment-only Home Depot listing samples (model, price, URL); no labor/extras yet
  - `data/electrical.json` — Rheem RETEX-13…36 kW / amps / breakers (spec sheet THD-PTE)
  - `data/decision-rules.json` — editorial repair-or-replace thresholds, ownership period 10 years
  - `data/incentives.json` — IRS 25C: expired 2025-12-31, current federal credit $0
  - `data/affiliate.json` — empty (block does not render)
- `data/computed/` — `energy-prices`, `inlet-temps`, `regional-multipliers`, `fhr-table`, `uef-defaults`, `unit-prices`, `reference-tables` (display tables + `physics`), `cross-checks` (formula vs published figures)
- `content/methodology.php` — complete: constants, formula cross-checks, one section per tool (`id` = tool slug) with formula, data, tables, limitations and an empty manufacturer cross-check table; data-file source table; editorial assumptions; update cadence; limitations; corrections
- `.htaccess` also blocks `tests/`; `style.css` Version 0.2.1 (0.2.1 = follow-up fix: wide tables made the page scroll sideways at 360px; `.layout` grid children now `min-width: 0` and the hand-written tables on /methodology use `.table-wrap`); README dev scripts; 8 new `docs/DECISIONS.md` entries

**Verified:**
- `npm test`: 27/27 pass. `npm run validate-data`: 27 files OK, computed data fresh.
- `NODE_USE_ENV_PROXY=1 npm run validate-data -- --urls` on 2026-11-12: **83/83 source URLs answered 200.**
- Formula cross-checks (in `data/computed/cross-checks.json`, shown on /methodology): DOE FEMP electric 3,461 vs 3,437 kWh (+0.7%); Rheem RETEX-36 rise at 2.5/3/4/5/6 GPM 98/82/61/49/41 vs 98/82/62/49/41 °F; DOE example 3.25 GPM / 70 °F rise (0%); AHRI worksheet 70 gal (0%).
- `php -l` clean; /methodology rendered with a PHP stub harness (no notices; 23 tables; no empty values).
- Live after PR #3 (0.2.0): `npm run check` 118/118 with and without `--bust`; /methodology 200, noindex, 23 tables, data-rendered numbers present; `data/*.json` public (200), `tests/` and `scripts/` 403. 360px check found sideways scroll (fixed in the 0.2.1 follow-up PR; re-verified live).

**Known issues / TODOs carried forward:**
- **energy.gov Energy Saver pages are gone (404)** and web.archive.org is unreachable from the build container; use the UNT web archive or FEMP/eCFR instead.
- **BLS blocks scripted access (403)**; do not try to get around it. Regional factor uses BEA RPP (DECISIONS).
- Serial formats: no tankless brands (Rinnai, Navien, Noritz, Takagi) and no Whirlpool/GE/Kenmore yet — add only with an openable source (Phase 2/2b).
- Cost: installation labor and extras missing on purpose (Phase 4 must find named, openable references); propane prices not in data.
- Manufacturer cross-check tables on /methodology are empty skeletons; Phases 2–4 fill them (3+ cases per tool, linked) and set `dateVerified`.
- Hostinger CDN still needs a flush after deploys if the plain URL serves stale HTML (see Phase 0 note).
- Methodology `dateModified` in `pages.json` stays 2026-11-13 (Phase 0 value, not earlier than `datePublished`).

**Notes for the next thread:**
- Render numbers with `hwm_value()` / `hwm_table()`; computed tables live under `computed/<file>` → `tables.<name>`. Physics constants: `hwm_value('computed/reference-tables', 'physics.lbPerGallon')`.
- Tool JS imports `formulas.js`; its defaults should come from `data/*.json` (fetched or inlined by PHP), never typed into JS.
- Write `data/computed/<tool-slug>.json` (the server-rendered default) from `build-data.mjs`, add new inputs to `INPUTS`, rerun `npm run build:data`, `npm test`, `npm run validate-data`.
- Inlet temperature for tankless sizing: `computed/inlet-temps` → `states.<ST>.coldestMonth`.
- Bump `style.css` Version in every PR; after deploy open `https://hotwatermath.com/?sync=<random>` and run `npm run check -- --bust` and without `--bust`.

## Log
- 2026-11-09 — Project docs created (CLAUDE.md, STRATEGY.md, PHASES.md, HANDOFF.md). No code yet.
- 2026-11-09 — Strategy review v2: added SERP briefs, draft/live gating, embed widgets, Dataset/`/data`, troubleshooting cluster (6b), state pages (6c), authority phase (8), realistic traffic targets.
- 2026-11-12 — Phase 0 done — WordPress theme foundation, live on hotwatermath.com (PRs #1, #2).
- 2026-11-12 — Phase 1 done — sourced data layer (17 data files, 83/83 sources 200), formulas + tests, computed tables, full /methodology.

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
