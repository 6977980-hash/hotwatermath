# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 2b, thread 1 — lifespan article + Bradford White, A. O. Smith, Rheem, State age pages (2026-11-13)
**Next phase to run:** Phase 2b, thread 2 — remaining brand pages (Whirlpool, American, GE, Kenmore, Navien, Rinnai), see notes below (see `docs/PHASES.md`)

**What exists now (new or changed in Phase 2b thread 1):**
- `content/how-long-does-a-water-heater-last.php` — article (draft): lifespan table, DOE vs maker SVG chart, why sources disagree, gas/electric/tankless/heat pump, what shortens life, embedded age calculator, "how old is too old" table, signs, warranty, FAQ (6), sources (7)
- `content/bradford-white-water-heater-age.php`, `content/ao-smith-water-heater-age.php`, `content/rheem-water-heater-age.php`, `content/state-water-heater-age.php` — brand tool pages (draft), decoder preset per brand, format table, test-case table, serial SVG, worked examples, lifespan/warranty notes, FAQ (6 each)
- `parts/age-inputs.php` (shared age inputs; the age page now uses it, output unchanged), `parts/serial-figure.php`, `parts/decoder-checks.php`, `parts/serial-formats-table.php`
- `data/brand-pages.json` (new) — per-brand default serial, hand-checks with sources, worked examples, CPSC recall facts
- `data/serial-formats.json` — new patterns: Rheem older prefixed tags (InspectApedia), A. O. Smith one-letter month (CPSC 2008 range), State pre-2004 two-letter (Building Intelligence Center); `layout` and `era` text per pattern
- `data/lifespans.json` — `manufacturerViews` for A. O. Smith and Bradford White, Rheem maintenance numbers, `doeFigures` (15 / 13 / AHRI 10–13)
- `data/computed/<brand>-water-heater-age.json` ×4, `data/computed/how-long-does-a-water-heater-last.json`; age `crossChecks` now include the brand cases (shown on /methodology)
- `scripts/build-data.mjs` — Phase 2b block; build fails if a brand hand-check stops matching its source
- `assets/js/age.js` — root also matches `.calc[data-tool$="-water-heater-age"]`; GA4 events use the page's tool slug
- `parts/calculator.php` — "How we calculated" anchor from `pages.json → methodAnchor`
- `data/pages.json` — the 5 pages `draft` with SEO fields; `data/site.json` dateVerified for them; `assets/og/` 5 new images
- `docs/briefs/` — 5 new briefs with SERP table, verdict and quality check; 3 `docs/DECISIONS.md` rows; `tests/formulas.test.mjs` 37 tests; `style.css` 0.4.0

**Verified (before deploy):**
- `npm test` 37/37; `npm run validate-data` 36 files OK; `--urls` 97 URLs, all 200 except InspectApedia (409 to scripts; opened with a browser-style fetch 2026-11-13).
- Offline render (PHP stub of the WP helpers) of all 5 pages + age page + /methodology: no PHP notices; age page markup identical after the input refactor.
- Playwright 360 px (offline, theme CSS + age.js): no console errors, no sideways scroll; brand select ends at 558–566 px without the header (≈ 615–625 px with it, inside 640).
- Prose words (no tables/FAQ): lifespan 1,615; Bradford White 1,291; A. O. Smith 1,306; Rheem 1,042; State 965. Body text difference between brand pages: 67–81% (A. O. Smith vs State lowest).
- Hand-checked serials (decoder = source; all asserted in build-data and tests):
  - Bradford White: SF → June 2018, XA → Jan 2021, LK → Oct 2014 (Bradford White chart); YD1395234 → Apr 2022 first, 2002 listed (CPSC 2002 recall: sold Apr–May 2002).
  - A. O. Smith: 1115… → Apr 2011, 1631… → Jul 2016 (CPSC 2018: made Apr 8, 2011–Aug 1, 2016); L07A071460 → Nov 2007 (CPSC 2008: sold Nov 2007–Jan 2008); 0805A → Jan 2008 week 5 (Energy Trust).
  - Rheem: M302112345 → Jul 2021 (lookup 07/27/2021), Q151512345 → Apr 2015 (04/09/2015), Q431912345 → Oct 2019, Q052412345 → Jan 2024, 0794F → Jul 1994 (Energy Trust), RH0806B17262 → Aug 2006 (InspectApedia).
  - State: 1115… → Apr 2011 (CPSC 2018), C05 → Mar 2005 (Energy Trust), M07A009387 → Dec 2007 (building-center + CPSC 2008 range), AF04A093001 → Jun 2004 (building-center).
- No brand page title/H1 targets "water heater age calculator"; lifespan article H1 is the informational question, distinct from the tool page.

**Known issues / TODOs carried forward:**
- Rheem/Ruud lookup returned no date for R0804B10488, RH0806B17262, A141511869 (2026-11-13): older prefixed layouts rest on InspectApedia only.
- Energy Trust says month letters "A through N" excluding I (13 letters); we treat N as unreadable (CPSC confirms L = Nov, M = Dec).
- No State-published lifespan found; the State page says so.
- Bradford White plate position is only shown in an image on its site; our text says "outer jacket".
- Live checks (Lighthouse, schema validator, live Playwright) are recorded after deploy in the follow-up below.
- Carried: Navien Y/Z months inferred; Rinnai/Noritz/Takagi/Bosch not decoded; tankless chart from 2016 Rinnai; labor/extras costs (Phase 4); Hostinger CDN may need a flush after deploys.

**Notes for the next thread (Phase 2b thread 2):**
- PHASES rule 3: only build a brand page if it has a sourced, distinct format. American and Whirlpool share the American year-week code (Whirlpool is made by American): build ONE page or make sure the two differ by ≥40%; GE uses the Rheem code and Kenmore the State code: probably a short section on the Rheem/State pages or a combined page, not separate copies. Navien is decodable (CPSC). Rinnai has no source yet: do not build until one is found.
- Pattern to copy: add a brand to `data/brand-pages.json` (slug, option, defaultSerial, checks with sources, examples), flip its `pages.json` entry to `draft` with SEO fields + `scripts: ["age"]` + `methodAnchor`, write `content/<slug>.php` using the parts above, add `dateVerified`, `npm run build:data`, `npm run build:og`.
- The age page summary table links each brand automatically once its page is visible (drafts only for logged-in editors).

## Log
- 2026-11-09 — Project docs created (CLAUDE.md, STRATEGY.md, PHASES.md, HANDOFF.md). No code yet.
- 2026-11-09 — Strategy review v2: added SERP briefs, draft/live gating, embed widgets, Dataset/`/data`, troubleshooting cluster (6b), state pages (6c), authority phase (8), realistic traffic targets.
- 2026-11-12 — Phase 0 done — WordPress theme foundation, live on hotwatermath.com (PRs #1, #2).
- 2026-11-12 — Phase 1 done — sourced data layer (17 data files, 83/83 sources 200), formulas + tests, computed tables, full /methodology.
- 2026-11-13 — Phase 2 done — age calculator (Rheem code checked against Rheem/Ruud lookup, Navien via CPSC) and tankless size calculator (state inlet temps, size charts, Rinnai/Noritz/Rheem cross-checks); both draft.
- 2026-11-13 — Phase 2b (thread 1) done — lifespan article + Bradford White, A. O. Smith, Rheem, State age pages (draft), decoder checked against Rheem/Ruud lookup, Bradford White chart and CPSC recalls.

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
