# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 2b, thread 2 — Whirlpool, American, Navien age pages + GE/Kenmore sections (2026-11-13). Phase 2b is finished (see DECISIONS: GE/Kenmore folded into Rheem/State, Rinnai not decodable).
**Next phase to run:** Phase 3 — Tools 3 & 4: Tank Size Calculator, Repair-or-Replace (see `docs/PHASES.md`)

**What exists now (new or changed in Phase 2b thread 2):**
- `content/whirlpool-water-heater-age.php`, `content/american-water-heater-age.php`, `content/navien-water-heater-age.php` — brand tool pages (draft): preset decoder, format table, serial SVG, decoder check table, worked examples, label location, warranty/lifespan notes, FAQ (6 each), sources (8–9)
- `content/rheem-water-heater-age.php` (H3 "GE water heater age"), `content/state-water-heater-age.php` (H3 "Kenmore water heater age" + 1 FAQ), `content/ao-smith-water-heater-age.php` (week-to-month caption corrected to the real formula)
- `content/water-heater-age-calculator.php` — summary table links use `summaryRows[].page` (GE → Rheem page, Kenmore → State page)
- `parts/serial-figure.php` — new `unit: wall` drawing (wall-hung tankless, label on the right)
- `assets/js/formulas.js` — `yyyy` field type in `decodeSerial` (rejects future years)
- `data/serial-formats.json` — American source = CPSC 2018 + Energy Trust, A. O. Smith since April 2006; Navien current layout `validFromYear: 2010`, Y/Z months sourced to Building Intelligence Center, new `navien-dash-yyyymmdd` pattern; `brandOptions[].page` for GE/Kenmore
- `data/brand-pages.json` — `whirlpool` (code american), `american`, `navien` (defaultType tankless): checks, examples, Whirlpool lookup/warranty codes, American model warranties/history, Navien warranty/life/label
- `scripts/build-data.mjs` — `b.code`, `b.defaultType`, `yyyy` label, `summaryRows.page`
- `data/pages.json` (3 pages draft + SEO fields), `data/site.json` dateVerified, `data/computed/{whirlpool,american,navien}-water-heater-age.json`, `assets/og/` 3 images
- `docs/briefs/{whirlpool,american,navien}-water-heater-age.md` (SERP table, verdict, quality check; Navien brief records why Rinnai is not built); 3 `docs/DECISIONS.md` rows; `tests/formulas.test.mjs` 39 tests; `style.css` 0.5.0

**Verified (before deploy):**
- `npm test` 39/39; `npm run validate-data` 39 files OK; `--urls` 108 URLs, all 200 except InspectApedia (409 to scripts; opened with a browser fetch 2026-11-13).
- Offline render (PHP stub) of the 3 new pages + Rheem, State, A. O. Smith, age page: no PHP notices; age summary links Whirlpool, American, Navien, GE→Rheem, Kenmore→State.
- Prose words (no tables/FAQ): Whirlpool 1,250; American 1,026; Navien 983. Body text difference: Whirlpool vs American 62%; every brand pair 62–76%.
- Hand-checked serials (all asserted in build-data and tests):
  - Whirlpool: 1115T… → Apr 2011, 1631T… → Jul 2016 (CPSC 2018 lists Whirlpool; made Apr 8, 2011–Aug 1, 2016); 1104T403904 → Jan 2011 (wk 4), 0128104380 → Jul 2001 (wk 28) (Building Intelligence Center rule).
  - American: 1115… → Apr 2011, 1631… → Jul 2016 (CPSC 2018 lists American); 9746… → Nov 1997 (Energy Trust: week 46 of 1997); 9950123040 → Dec 1999 (InspectApedia: 1999 week 50).
  - Navien: 7410x18709… → Jul 2018, 7419x18X14… → Oct 2018, 6608x188… → Aug 2018 (CPSC recall 19-057); 7414C14X1771143 → Oct 2014 (Building Intelligence Center); 8881-20100520-3026 → May 2010 (FastWaterHeater).
- Whirlpool official lookup (whirlpoolwaterheaters.com Need More Help) tested 2026-11-13: published example serials not recognised (needs a real serial).

**Known issues / TODOs carried forward:**
- Navien Y/Z months and the hyphenated layout are third party only. American Appliance and American Standard notes are third party.
- GE: no source for who makes current GE-branded units; the Rheem page keeps the caveat. Kenmore: only State/A. O. Smith-made units decode.
- waterheaterrecall.com failed TLS from our checker; not linked.
- Carried from thread 1: Rheem lookup misses for older prefixed tags; Energy Trust "A through N" month note; no State-published lifespan; Bradford White plate position only in an image; Rinnai/Noritz/Takagi/Bosch not decoded; tankless chart from 2016 Rinnai; labor/extras costs (Phase 4); Hostinger CDN may need a flush after deploys.
- Owner OK still needed to flip any page to `live` (all age pages are draft).

**Notes for the next thread (Phase 3):**
- Every brand page lists `repair-or-replace-water-heater` in next steps/related; it shows as plain text until Phase 3 makes that page visible, then links automatically.
- Offline render recipe: a PHP stub that defines ABSPATH/HWM_DIR and the few WP functions used (esc_*, selected, checked, wp_json_encode, untrailingslashit, wp_parse_url, home_url) then `require inc/data.php` and `inc/helpers.php` and calls `hwm_content($slug)`.

## Log
- 2026-11-09 — Project docs created (CLAUDE.md, STRATEGY.md, PHASES.md, HANDOFF.md). No code yet.
- 2026-11-09 — Strategy review v2: added SERP briefs, draft/live gating, embed widgets, Dataset/`/data`, troubleshooting cluster (6b), state pages (6c), authority phase (8), realistic traffic targets.
- 2026-11-12 — Phase 0 done — WordPress theme foundation, live on hotwatermath.com (PRs #1, #2).
- 2026-11-12 — Phase 1 done — sourced data layer (17 data files, 83/83 sources 200), formulas + tests, computed tables, full /methodology.
- 2026-11-13 — Phase 2 done — age calculator (Rheem code checked against Rheem/Ruud lookup, Navien via CPSC) and tankless size calculator (state inlet temps, size charts, Rinnai/Noritz/Rheem cross-checks); both draft.
- 2026-11-13 — Phase 2b (thread 1) done — lifespan article + Bradford White, A. O. Smith, Rheem, State age pages (draft), decoder checked against Rheem/Ruud lookup, Bradford White chart and CPSC recalls.
- 2026-11-13 — Phase 2b (thread 2) done — Whirlpool, American, Navien age pages (draft), GE/Kenmore sections on Rheem/State, decoder checked against CPSC recalls; Phase 2b complete.

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
