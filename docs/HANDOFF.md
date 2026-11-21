# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed work (2026-11-21):** owner request ideas 3, 5, 1, style 0.21.0; owner OKed 2026-11-21 22:10Z, so all three are `live` (style 0.21.1). The failure database is live but `"noindex": true` until its report:
- `/water-heater-recall-lookup/`: brand + model + serial (+ year) → match / possible / outside against 24 CPSC water heater recalls since 2001, plus a recall alert email signup.
- `/water-heater-maintenance-schedule/`: type, install month/year, water, last service → due dates per task (maker-sourced intervals) + replacement planning date, plus the Heater Health Reminder signup.
- `/water-heater-failure-database/`: failure report form (brand, type, years, cause, outcome); report at 100 submissions.
**Next phase to run:** Phase 7 (from `docs/PHASES.md`), once Search Console has about 4 weeks of data.

**What exists now (new or changed):**
- `data/recalls.json`, `data/maintenance.json`, `data/failure-database.json`; `data/input-limits.json` (+2); `data/site.json` (+2 dateVerified); `data/pages.json` (+3 draft).
- `assets/js/formulas.js` (+ recallCheck, serialInRecall, normalizeCode, addMonths, maintenanceSchedule); `assets/js/recall-lookup.js`, `maintenance-schedule.js`, `forms.js`.
- `inc/lists.php` (email lists, failure form, daily cron, `/subscription/` route, admin page, SMTP via wp-config constants); `templates/subscription.php`; `parts/signup-form.php`; `parts/heater-care-links.php` (in age calculator, 7 brand pages, lifespan, flush and anode pages).
- `content/` 3 new pages; `content/methodology.php` (2 sections); `content/privacy-policy.php` (#email-lists).
- `scripts/build-data.mjs` (3 computed files, guards); `scripts/fetch-recalls.mjs` (`npm run fetch:recalls`, run with `NODE_USE_ENV_PROXY=1`); `tests/recalls-schedule.test.mjs`, `tests/lists-php.test.mjs`; `docs/briefs/` (3); Keyword Map 11–13; DECISIONS (5 rows); `assets/og/` (4 PNGs incl. the missing solar one).

**Verified (before deploy):** `npm test` 104/104; `validate-data` 96 files OK; `php -l` clean. Local WordPress (SQLite) end to end: signup, honeypot, bad email, confirm, health reminder by cron, recall alert brand filter, one-click unsubscribe, failure insert and validation, `/subscription/` no-cache + noindex. Playwright: calculators update, JS forms show messages, first input inside 360×640 (recall 588px, schedule 616px), no sideways scroll, no console errors.

**Known issues / TODOs carried forward:**
- When the failure database reaches 100 submissions, publish the report and remove `noindex` from its registry entry. WP-Cron runs on visits; an hPanel cron hitting `wp-cron.php` daily makes reminders punctual.
- cpsc.gov HTML answers 403 to our checker; recall content is checked against the CPSC API record.
- Re-run `npm run fetch:recalls` monthly; the daily job also emails the owner new CPSC candidates.
- Earlier items still open: NY/RI HEEHR 403; rebate data review 2027-02-20; F-15 optional hPanel check; electric tankless UEF recheck vs CCMS; solar NREL 2012 cost; IRC P2903.4 (403).

**Owner steps:** flush the Hostinger CDN; request indexing for the recall lookup and the maintenance schedule in Search Console. SMTP is set in `wp-config.php` and tested (confirm email arrived and was confirmed, 2026-11-21).

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
- 2026-11-21 — New tools done — recall lookup (24 CPSC recalls) + recall alerts, maintenance schedule + Heater Health Reminder, failure database form; own email lists over Hostinger SMTP; all draft (style 0.21.0).
- 2026-11-21 — Go-live — owner OK: recall lookup and maintenance schedule live; failure database live with the new `noindex` flag; SMTP tested (style 0.21.1).
