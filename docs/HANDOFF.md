# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 6b batch 1 — Troubleshooting: leaking, how to flush, anode rod, no hot water (2026-11-13). Owner OK received the same day: all four pages are `live`.
**Next phase to run:** Phase 6 — Expansion articles (start with `/heat-pump-water-heater-cost`, see `docs/PHASES.md`). Phase 6b batch 2 (reset button, noise, runs out fast, rotten eggs) runs after Phase 6.

**What exists now (new or changed in Phase 6b batch 1):**
- `content/water-heater-leaking.php` — leak-point SVG + table, first steps, condensation vs leak, bottom / top / relief valve / gas vs electric H2s, embedded repair-or-replace tool, prevention, FAQ (7)
- `content/how-to-flush-a-water-heater.php` — manufacturer schedule table, setup SVG, manual steps, gas vs electric, clogged valve, old-tank question with embedded age calculator, tankless descaling (Rinnai), mistakes, FAQ (6)
- `content/water-heater-anode-rod.php` — what it does, life, replace-at rule (6 in. core wire), depletion SVG, types + dated Home Depot prices table, metal choice, location, manual procedure (summary), warranty, worth-it with embedded age calculator, FAQ (6)
- `content/no-hot-water.php` — one tap vs all, flowchart SVG + check table by type, electric / gas / tankless H2s, "some but not enough" causes, embedded repair-or-replace tool, FAQ (6)
- `data/troubleshooting.json` — 13 references with quotes and check dates; figures for leak, flush, anode, no-hot-water, safety; 4 tables
- `parts/repair-inputs.php` (shared repair-or-replace inputs; the tool page uses it too), `parts/safety-notice.php` (sourced gas / power / scald notice), `parts/next-steps.php` ("Common problems" line from a tool's `problems`)
- `templates/hub.php` — /guides/ grouped by `group` in `pages.json → meta.articleGroups`; `templates/tool.php` passes `problems`
- `data/pages.json` — 4 draft entries (`group`, `problemLabel`, `scripts`), `meta.articleGroups`, lifespan article `group`, `problems` on 5 tools
- `assets/og/` — 4 new OG images; `assets/css/global.css` (`.safety-notice`, `.next-steps__problems`); `style.css` 0.9.0
- `docs/briefs/` — 4 briefs with SERP table, difficulty verdict, additions and quality check; `docs/DECISIONS.md` 2 rows

**Verified (before deploy):**
- `npm test` 50/50; `validate-data` 47 files OK; `validate-data --urls`: every new source 200 except Home Depot (403 to Node's user agent, 200 in a browser request; same as the Phase 4 Home Depot sources); `build:data` fresh.
- Offline render (PHP stub): 0 PHP errors for the 4 articles, /guides/, /calculators/ and the tools that changed (visitor and editor views); prose 1,785–2,367 words per article (excluding calculator and tables); no horizontal scroll at 360 px; SVGs checked at 360 and 1200 px. Visitors see no links to the drafts; editors see "Common problems" on tool pages.
- Hardcoded-number grep on the 4 content files: only the diagram label "(6)"; every figure renders from data.

**Verified live (after PR #21 deployed, 2026-11-13):**
- style.css 0.9.0 served; all 4 articles 200 with `noindex, follow`; /guides/ shows groups and no draft links for visitors; tools render with no PHP errors.
- `npm run check-links -- --bust` 646/646 (29 pages). `npm run check -- --bust` 375/377: two titles were over 60 characters (flush 64, anode 62), shortened in the follow-up PR (now 58 and 56).
- Playwright at 360x640 on the live drafts: no horizontal scroll, no console errors; the embedded repair tool switches to "Replace it rather than pay for this repair" when "Water leaking from the tank itself" is ticked; the embedded age calculator runs.

**Owner steps:**
1. Done: owner OK'd the four pages; they are `live` (go-live PR).
2. Flush the Hostinger CDN after this deploy if a page looks stale (hPanel → Performance → CDN).
3. Carried from Phase 5: GSC Verify + sitemap, Bing, GA4 ID, AdSense IDs.

**Known issues / TODOs carried forward:**
- The relief valve, pilot light and reset button get their own pages later (6d / batch 2); these pages name them in plain text until then (no link to unbuilt pages).
- Anode prices are one retailer on one date; re-check when the page is refreshed. No labor price for fitting a rod (no primary source).
- DOE's old "drain a quart every three months" tip is not on any live energy.gov page we could find, so it is not used.
- HomeTips (TLS error / 503) and Angi (403) could not be opened for the briefs; judged from snippets and noted.
- Carried from Phase 5: real-ads Lighthouse re-measure; calculator forms have no submit button (accepted); Phase 4 items.

**Notes for the next thread:**
- When the owner OKs these pages, flip `status` to `live` in `data/pages.json` (the hub, home "Recently updated", sitemap and the tools' "Common problems" links pick them up automatically) and run `npm run check-links -- --bust` after deploy.
- New article entries need a `group` from `meta.articleGroups` (Phase 6 articles: "Costs & Sizing").
- Troubleshooting batch 2 can reuse `parts/safety-notice.php`, `parts/repair-inputs.php` and add its facts to `data/troubleshooting.json`.

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
