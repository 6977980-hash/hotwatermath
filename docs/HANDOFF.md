# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** none (project not started)
**Next phase to run:** Phase 0 — Foundation (see `docs/PHASES.md`)

**What exists now:**
- `CLAUDE.md` — master rules (read first)
- `docs/STRATEGY.md` — keyword map, calculator specs, data file list
- `docs/PHASES.md` — phase-by-phase tasks and kick-off prompts
- `docs/HANDOFF.md` — this file
- `docs/DECISIONS.md` — decisions log (includes the 2026-11-12 WordPress change and plan-review fixes)

**Verified:** nothing yet.

**Known issues / TODOs carried forward:**
- Domain is `hotwatermath.com`, brand is "Hot Water Math" (final). Phase 0 writes them into `data/site.json`. Site runs on WordPress at Hostinger; Hostinger Git deploys repo `main` into `public_html/wp-content/themes/hotwatermath`.
- Owner must provide: AdSense client ID, GA4 ID, GSC verification token, Bing Webmaster token (Phase 5, stored in `data/site.json` → `integrations`). Owner must turn on AdSense "Privacy & messaging" once the AdSense account exists.
- Keyword Planner volumes for brand-age and troubleshooting queries received 2026-11-13 and written into STRATEGY §2 (files in `/mnt/project-files/keyword-data/`). Owner may supply original photos of rating plates/serial labels.
- **Do not start any phase until the owner explicitly says so** (owner instruction, 2026-11-12).
- Owner must fill `data/affiliate.json` (Phase 1 creates it empty).
- No external reviewer will be used (owner decision, 2026-11-09). Trust = transparent methodology + manufacturer cross-check tables + honest About page. Never add a "reviewed by" line.
- No plumber/contractor referrals or lead-gen anywhere (owner decision). Only AdSense (+ optional product affiliate).
- Owner must flip each page from `draft` to `live` in `pages.json` after reading it.
- Safety-critical pages are built last (Phase 6d, CLAUDE.md §8b). Owner will arrange a plumber fact-check after launch and send results; Claude updates the pages then.
- Founder LinkedIn (`https://www.linkedin.com/in/ali-ahmad-chaudhry-12777486/`) goes into `data/site.json` `author.sameAs` in Phase 0. Owner should add hotwatermath.com to that profile.
- Claude owns the quality gate (CLAUDE.md §8): beat the live top 5 and verify every source by opening it; owner only replies OK to go live. Owner does outreach after launch.
- Phase 8 (links/authority) is owner-driven and must run alongside Phases 6–7; without it, traffic expectations are the lower range in STRATEGY §7.

**Notes for the next thread:**
- Start with Phase 0 kick-off prompt from `docs/PHASES.md`.
- Do not build any tool pages in Phase 0.
- Phase order: 0 → 1 → 2 → 2b → 3 → 4 → 5 → 6b batch 1 → 6 → 6b batch 2 → 6c → 6d, with 7 monthly and 8 in parallel after launch.

## Log
- 2026-11-09 — Project docs created (CLAUDE.md, STRATEGY.md, PHASES.md, HANDOFF.md). No code yet.
- 2026-11-09 — Strategy review v2: added SERP briefs, draft/live gating, embed widgets, Dataset/`/data`, troubleshooting cluster (6b), state pages (6c), authority phase (8), realistic traffic targets.

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
