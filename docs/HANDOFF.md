# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed work:** Three new tools (owner request 2026-11-20, ideas 1, 5, 6), style 0.20.1 (0.20.1 = mobile first-viewport fix), owner OKed 2026-11-21 07:34Z, now `live` (style 0.20.2):
- `/water-heater-model-number-lookup/`: model + serial (+ optional label photo, local only) → size, fuel, height, input, warranty years, build date, age, warranty end.
- `/heat-pump-water-heater-rebates/`: state + income (+ cost) → 25C status ($0), HEEHR amount (14 NASEO-listed programs; CA fully reserved; DC and NM low-income only) and the utility programs we checked.
- `/water-heater-quote-checker/`: quote total (+ unit, labor, permit lines) and the job → below / fair low / fair high / above / far above, using the cost calculator's model.
**Next phase to run:** Phase 7 (from `docs/PHASES.md`), once Search Console has about 4 weeks of data.

**What exists now (new or changed):**
- `data/model-formats.json`, `data/heehr-states.json`, `data/quote-check.json`; `data/input-limits.json` (+2 tools); `data/site.json` dateVerified (+3); `data/pages.json` (+3 tools, live 2026-11-21).
- `assets/js/formulas.js` (+ decodeModel, warrantyEnd, heehrRebate, quoteBand, lineBand); `assets/js/model-lookup.js`, `rebate-finder.js`, `quote-checker.js`.
- `scripts/build-data.mjs` section "2026-11-20": computed files for the 3 tools; Rheem rule checked against all 80 Rheem listings (fails the build on a mismatch); registry figure guards.
- `content/water-heater-model-number-lookup.php`, `content/heat-pump-water-heater-rebates.php`, `content/water-heater-quote-checker.php`; `content/methodology.php` (3 sections, TOC, quote assumption row); `parts/cost-inputs.php` (`legend` arg).
- `tests/new-tools.test.mjs`; `docs/briefs/` (3 briefs with quality check); `docs/STRATEGY.md` Keyword Map items 8–10; `docs/DECISIONS.md` (5 rows); `assets/og/` (3 PNGs).

**Verified (before deploy):** `npm test` 89/89; `validate-data` 90 files OK; `php -l` clean; PHP render smoke test of the 3 pages and /methodology with WordPress stubs (no warnings; 1,139–1,552 body words each, 6 FAQs, 5–6 sources); Playwright on the rendered calculators: defaults, Bradford White propane 10-year, Rinnai (refused), CA reserved, OH none, AZ $1,750 cap, bad cost input flagged, TX $2,400 quote "fair, high side", type change refills sizes, share URL state; no console errors.

**Known issues / TODOs carried forward:**
- NY (NYSERDA) and RI HEEHR pages answer 403 to our checker; their rows rely on NASEO only (`pageChecked: false`).
- Model rules cover Rheem XG/XE, Bradford White RG, Whirlpool and American only; A. O. Smith, State and tankless brands are serial-only.
- Rebate finder lists utilities only in the 8 states with utility data; ENERGY STAR's finder covers the rest. Review `heehr-states.json` and `hpwh-rebates.json` every three months (next 2027-02-20).
- Earlier items still open: F-15 optional hPanel check; electric tankless UEF recheck vs CCMS; solar NREL 2012 cost; gas tankless headline metric; IRC P2903.4 (403).

**Owner steps:** request indexing for the three new tools in Search Console; flush the Hostinger CDN after the deploy if a page looks stale.

- 2026-11-13 — Phase 8 done — cost index by state (51 rows, chart, CSV, embed code), /data/ with 12 CSV datasets + Dataset schema, /embed-calculators/, nofollow in-frame links, check-embeds script, OUTREACH.md rewritten (draft pages).

---
- 2026-11-14 — Audit fix Phase 1 done — tools: tankless sizing at the 105 °F mix, one input validator, ownership with value left/upkeep/simple payback, serial fixes, repair rules, propane, point of use, heat pump location (style 0.16.0).
- 2026-11-14 — Audit fix Phase 3 done — content and state pages: copy fixes, one rise and one "old" band, all tank sizes priced, state pages unique (Jaccard mean 0.37) with sourced code notes and utility rebates, tankless delay, lifespan sources, methodology TOC, DOE 2029 rule, solar cost page (draft).
- 2026-11-14 — Audit fix Phase 4 done — expansion tank (and tankless, gas vs electric) first input inside 360×640, no empty Advertisement landmark, 56 px header; F-15 is a CDN-edge hop, left as optional owner check (style 0.17.1).
- 2026-11-14 — Audit fix Phase 5 done — stray screenshots removed, XML-RPC pingbacks off, public pages DENY framing (embeds still frameable), HSTS includeSubDomains (style 0.17.2).
- 2026-11-14 — Layout fix done — desktop "On this page" rail (sticky TOC) in the empty right column, wider home page (style 0.18.0).
- 2026-11-14 — Home redesign done — banded home page: start-here paths, icon calculator cards, stat tiles, grouped guides (style 0.19.0).
- 2026-11-20 — New tools done — model number lookup, heat pump water heater rebate finder (HEEHR by state + utilities), water heater quote checker; all draft (style 0.20.0).
- 2026-11-20 — Live fix — 360px first-viewport rule: new tools' field grid stacks to one column under 600px (`.field-grid--stack`), two quick answers shortened (style 0.20.1).
- 2026-11-21 — Go-live — owner OK: model number lookup, heat pump water heater rebates, quote checker flipped to live (style 0.20.2).

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
- 2026-11-21 — GSC-driven content fixes — first GSC audit (report in project files gsc-audit/2026-11-21); max temperature H2 + FAQ (safety.temp.aosVeryHotF, A. O. Smith manual re-checked 2026-11-21), relief valve, tankless wiring and anode FAQs, FHR definition H2, links to quote checker, rebate finder and model lookup (style 0.20.3).
