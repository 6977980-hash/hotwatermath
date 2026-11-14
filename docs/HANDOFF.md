# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Audit fix Phase 5 — speed and security (2026-11-14), the last phase of the 2026-11-14 site audit (`/mnt/project-files/hotwatermath/audit/2026-11-14/`). Style 0.17.2. All audit fix phases (1, 3, 4, 5; Phase 2's F-15 is an owner hPanel check) are done.
**Next phase to run:** Phase 7 (from `docs/PHASES.md`), once Google Search Console is verified and has about 4 weeks of data. Owner OKed the 4 drafts on 2026-11-14 10:34Z: `/solar-water-heater-cost/`, `/water-heater-installation-cost-index/`, `/data/`, `/embed-calculators/` are now `live` (style 0.17.3). Owner done: CDN flush, 4 screenshots deleted in File Manager, GSC sitemap submitted, Bing imported from GSC, Force HTTPS fine (F-15 closed). GA4 inside GTM is handled in its own thread; AdSense later.

**What changed in audit fix Phase 5:**
- F-22: removed `chart-mobile.png`, `data-360.png`, `embed-calculators-360.png`, `water-heater-installation-cost-index-360.png` from the theme root (none referenced); `.gitignore` ignores root `/*.png` except `screenshot.png`.
- F-23: `inc/cleanup.php` `xmlrpc_methods` filter unsets `pingback.ping` and `pingback.extensions.getPingbacks`; `X-Pingback` header stays removed.
- F-24: `inc/headers.php` public pages send `X-Frame-Options: DENY` + `frame-ancestors 'none'`; Customizer preview keeps SAMEORIGIN; `/embed/*` unchanged (`frame-ancestors *`). HSTS now `includeSubDomains` (decision in DECISIONS.md).
- F-25: advice only (apply for AdSense once GSC shows indexed pages and some traffic).

**Verified (before deploy):** `npm test` 80/80; `validate-data` 84 files OK; `php -l` clean on changed PHP.

**Verified live (after PR #50 deployed, 2026-11-14):** style.css 0.17.2 served. Home and age tool: `X-Frame-Options: DENY`, `frame-ancestors 'none'`, HSTS `max-age=31536000; includeSubDomains`. `/embed/water-heater-age-calculator/`: no X-Frame-Options, `frame-ancestors *`. One `system.listMethods` call: no `pingback.*` methods. `npm run check -- --bust` 778/778; `check-links -- --bust` 1565/1565 (60 pages); `check-embeds -- --bust` 230/230. The 4 screenshots still answer 200 from origin (CDN MISS): Hostinger's Git deploy does not delete removed files, so the owner deletes them in File Manager (`public_html/wp-content/themes/hotwatermath/`).

**Known issues / TODOs carried forward:**
- Customizer preview with the new DENY rule could not be tested logged in from the sandbox; the code keeps SAMEORIGIN when `is_customize_preview()` is true. Content is not edited in WordPress anyway.
- F-15: `http://…` URLs take 2 hops (CDN https, then WordPress). Optional owner hPanel check; test for a loop before keeping any change.
- Electric tankless UEF is from one maker's spec sheet; recheck against CCMS when it loads.
- Solar page: NREL installed cost is a 2012 range; FEMP has no city for Arkansas, Iowa, DC.
- Gas tankless total-cost vs payback headline (owner may pick one metric); DIY upkeep pricing; heat pump cold-garage derate; IRC P2903.4 (403); real-ads Lighthouse re-measure.

**Owner steps:** the one combined list was posted in the audit Phase 5 thread on 2026-11-14.

- 2026-11-13 — Phase 8 done — cost index by state (51 rows, chart, CSV, embed code), /data/ with 12 CSV datasets + Dataset schema, /embed-calculators/, nofollow in-frame links, check-embeds script, OUTREACH.md rewritten (draft pages).

---
- 2026-11-14 — Audit fix Phase 1 done — tools: tankless sizing at the 105 °F mix, one input validator, ownership with value left/upkeep/simple payback, serial fixes, repair rules, propane, point of use, heat pump location (style 0.16.0).
- 2026-11-14 — Audit fix Phase 3 done — content and state pages: copy fixes, one rise and one "old" band, all tank sizes priced, state pages unique (Jaccard mean 0.37) with sourced code notes and utility rebates, tankless delay, lifespan sources, methodology TOC, DOE 2029 rule, solar cost page (draft).
- 2026-11-14 — Audit fix Phase 4 done — expansion tank (and tankless, gas vs electric) first input inside 360×640, no empty Advertisement landmark, 56 px header; F-15 is a CDN-edge hop, left as optional owner check (style 0.17.1).
- 2026-11-14 — Audit fix Phase 5 done — stray screenshots removed, XML-RPC pingbacks off, public pages DENY framing (embeds still frameable), HSTS includeSubDomains (style 0.17.2).

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
