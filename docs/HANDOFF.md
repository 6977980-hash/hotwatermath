# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Audit fix Phase 4 — UX, mobile and accessibility (2026-11-14), from the site audit of 2026-11-14 (`/mnt/project-files/hotwatermath/audit/2026-11-14/`). Style 0.17.1. Drafts still waiting for the owner's OK: `/solar-water-heater-cost/` and the 3 Phase 8 pages (cost index, `/data/`, `/embed-calculators/`).
**Next phase to run:** Audit fix Phase 5 — speed and security (F-22 to F-25). Brief: `/mnt/project-files/hotwatermath/audit/2026-11-14/handoff/phase-5-security.md`; it ends with the one combined owner to-do list. Phase 7 still waits for GSC data.

**What changed in audit fix Phase 4:**
- F-20: shorter `subtitle`/`quickAnswer` in `data/pages.json` for the expansion tank page (first input bottom 749 → 635 px at 360×640) and, because their first input also ended just below 640 px, the quick answers of the tankless size (644 → 616) and gas vs electric (644 → 616) pages. Computed-number guards still pass; `dateModified` 2026-11-14 on those 3 pages.
- F-21: `hwm_ad_enabled()` and `hwm_ad_sidebar()` in `inc/helpers.php`; `templates/tool.php`, `article.php`, `state-cost.php` print the slot S `<aside aria-label="Advertisement">` only when the AdSense client and the S unit ID are set; `parts/ad-slot.php` uses the same check. The empty 300 px desktop column stays (grid unchanged), so nothing moves when ads switch on.
- F-30: `.site-header__inner` min-height is `--header-h` minus the 1 px border, so the header is 56 px.
- F-15 (left over from Phase 2): not fixable in the theme. The first hop (http → https, path kept) is answered by Hostinger's CDN edge (`server: hcdn`, no `x-redirect-by`) before WordPress runs; WordPress then does www/slash in one hop. Kept as a low-priority optional owner check in hPanel (see Known issues).

**Verified (before deploy):** `npm test` 80/80; `validate-data` 84 files OK; `build:data` committed; `php -l` clean; ad sidebar rendered with stubs: nothing when IDs are empty, aside + reserved box when client and unit are set. New copy measured by patching the live pages at 360×640: first input of all 15 tool pages ends at ≤ 635 px, header 56 px.

**Verified live (after PR #48 deployed, 2026-11-14):** style.css 0.17.1 served. `npm run check -- --bust` 778/778; `check-links -- --bust` 1565/1565 (60 pages); `check-embeds -- --bust` 230/230. Playwright 360×640 on all 15 tool pages + home, lifespan article and Texas state page: header 56 px, no Advertisement aside, no horizontal scroll, first input ends ≤ 635 px (expansion tank 635). At 1280 px the article column is unchanged (736 px).

**Known issues / TODOs carried forward:**
- F-15: `http://…` URLs take 2 hops (CDN https, then WordPress). Owner may check hPanel for a CDN/SSL "Force HTTPS" switch; turning it off only helps if WordPress then does http → https itself without a loop, so test before keeping it. Google follows both hops; impact is low.
- Electric tankless UEF is from one maker's spec sheet; CCMS was not reachable. Recheck against CCMS when it loads.
- Solar page: NREL's installed cost is a 2012 range; FEMP has no city for Arkansas, Iowa, DC. Michigan's two utility programs publish no amount.
- Gas tankless total-cost vs payback headline (owner may pick one metric); DIY upkeep pricing; heat pump cold-garage derate; IRC P2903.4 (403); real-ads Lighthouse re-measure.

**Owner steps (held for the one combined list at the end of Phase 5):** flush the Hostinger CDN after this deploy. Carried: OK the solar draft and the 3 Phase 8 drafts; GSC Verify + sitemap, Bing, GA4 inside GTM, AdSense IDs; outreach; post-launch plumber fact-check; optional F-15 hPanel check.

- 2026-11-13 — Phase 8 done — cost index by state (51 rows, chart, CSV, embed code), /data/ with 12 CSV datasets + Dataset schema, /embed-calculators/, nofollow in-frame links, check-embeds script, OUTREACH.md rewritten (draft pages).

---
- 2026-11-14 — Audit fix Phase 1 done — tools: tankless sizing at the 105 °F mix, one input validator, ownership with value left/upkeep/simple payback, serial fixes, repair rules, propane, point of use, heat pump location (style 0.16.0).
- 2026-11-14 — Audit fix Phase 3 done — content and state pages: copy fixes, one rise and one "old" band, all tank sizes priced, state pages unique (Jaccard mean 0.37) with sourced code notes and utility rebates, tankless delay, lifespan sources, methodology TOC, DOE 2029 rule, solar cost page (draft).
- 2026-11-14 — Audit fix Phase 4 done — expansion tank (and tankless, gas vs electric) first input inside 360×640, no empty Advertisement landmark, 56 px header; F-15 is a CDN-edge hop, left as optional owner check (style 0.17.1).

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
