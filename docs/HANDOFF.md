# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 5 — Launch: home, hubs, link audit, ads/consent, GA4, IndexNow (2026-11-13).
**Next phase to run:** Phase 6b batch 1 — Troubleshooting: leaking, how to flush, anode rod, no hot water (see `docs/PHASES.md`).

**What exists now (new or changed in Phase 5):**
- `content/home.php` — launch home: 7 core tool cards, question → calculator table (definition + comparison table), trust strip with live counts (sourced data files, cross-check cases), brand decoder links, recently updated, guides
- `templates/hub.php` — /calculators/ groups core tools and "Serial number decoders by brand"; `inc/schema.php` CollectionPage `mainEntity` ItemList of live children; Person `worksFor` typed (schema.org validator 0 errors)
- `inc/integrations.php` — Consent Mode v2 defaults (denied EEA/UK/CH, granted elsewhere), GA4 + AdSense loaded after `load`, `google-adsense-account` meta; embeds get GA4 only
- `parts/ad-slot.php` — box only when client + slot unit ID set; fixed-height `<ins>` (CSS in `global.css`)
- `data/site.json` → `integrations.adsenseSlots {A,B,C,S}` (new), `indexNowKey` set (`/ba2ff6ca8e6c1daca44777baadc13239.txt`)
- `scripts/check-links.mjs` (`npm run check-links`) — internal links 200, ≥3 body links, hub/home coverage, AuthorLine + disclaimer, embed/affiliate rel, banned phrases
- `docs/OUTREACH.md` — owner playbook (pitch templates, HARO/Qwoted/Featured routine, 20 resource-page targets, Reddit, Pinterest, entity profiles)
- `README.md` integrations table; `docs/DECISIONS.md` 4 rows; `content/about.php`, `content/editorial-policy.php` reworded (no banned phrases even in negative sentences); A. O. Smith / American summaries grammar; `style.css` 0.8.0
- All 25 non-planned pages are `live` (flipped by the go-live PR #17 with the owner's OK).

**Verified (before deploy, offline render: PHP stub + Playwright, all pages as live):**
- `npm test` 50/50; `validate-data` 46 files OK; `build:data` fresh; `check-links` 564/564; `check` page checks all pass (stub-only failures for WP routing ignored).
- Lighthouse mobile, ads off: home, /calculators/, /guides/ 100/100/100/100. With a test AdSense client + unit IDs + test GA4 ID: home 93 (TBT 300 ms from Google scripts), cost calculator 98/100/100/100, /privacy-policy/ 100; CLS 0.000–0.008 at 320/360/1280 px with slots reserved (A/B 280 px mobile, 250 desktop, S 630).
- 320 px and 360 px: no horizontal scroll on home, hubs, tools, article, about; first calculator input at 546–605 px (inside 640).
- html-validate: only `wcag/h32` (calculator form has no submit button: results update live) — accepted.
- schema.org validator: 0 errors, 0 warnings on home, /about/, /contact/, /methodology/, /guides/, /calculators/, a tool, a brand page, the article.
- Trust pass: AuthorLine + disclaimer on all 14 live tool pages; /methodology has filled cross-check tables for all 7 tools (4–34 cases each, with run date); /about and /editorial-policy state what we are not and ads/affiliate independence; no "reviewed by", "licensed plumber recommends", "find a pro", "get quotes" anywhere.

**Verified live (after PR #18 deployed, 2026-11-13):**
- style.css 0.8.0 served; `npm run check --bust` 333/333; `npm run check-links --bust` 564/564 (0 broken links, every page ≥3 body links, home + /calculators/ link every tool, /guides/ the article).
- Lighthouse mobile (P/A/BP/SEO): home 100/100/100/100, cost calculator 100/100/100/100, tankless size 100/100/100/100, /calculators/ 100/100/100/100, /privacy-policy/ 100/100/100/100; CLS 0, LCP 0.8–1.1 s, TTFB 116–430 ms.
- 360×640 on all 7 tools: no horizontal scroll, first input at 514–573 px, server-rendered results as expected ($1,809 installed; 5.0 GPM at 70 °F; 50-gal gas FHR 69+; gas $242 vs electric $564), no console errors, no pop-ups.
- IndexNow key file `/ba2ff6ca8e6c1daca44777baadc13239.txt` 200; direct API ping for `/` returned **202**. `/ads.txt` 200 with "No ad network is configured yet" (the `google.com, pub-…, DIRECT, f08c47fec0942fa0` line appears once `adsenseClient` is set; checked offline with a test ID). No Google scripts load while the IDs are empty.
- `http://hotwatermath.com/` → `https://hotwatermath.com/` in 1 hop; `https://www.` → apex in 1 hop; **`http://www.` takes 2 hops** (Hostinger's edge upgrades to `https://www.` first, then WordPress sends it to the apex). Not fixable in the theme.
- Favicon, logo-512, apple-touch-icon, manifest, OG images (home 46 KB, cost 31 KB, 1200×630) all 200. Sitemap 200; robots.txt points to it ("Discourage search engines" is off).
- **Found:** `/llms.txt` is a physical file written by the Hostinger Tools plugin (lists "Hello world!"), so it overrides the theme's version. Owner step 7 below.
- LiteSpeed served the old home page after the deploy (other pages purged); this follow-up PR bumps the version to purge again.

**Owner steps (dashboard clicks; Claude cannot do these):**
1. **Google Search Console:** add a Domain property for `hotwatermath.com` (DNS TXT in Hostinger) or a URL-prefix property with the HTML tag (send Claude the tag value → `integrations.gscToken`). Then Sitemaps → submit `https://hotwatermath.com/wp-sitemap.xml`; URL Inspection → Request indexing for the home page and the 7 tools.
2. **Bing Webmaster Tools:** "Import from Google Search Console" (fastest) or add the site with the `msvalidate.01` tag (→ `integrations.bingToken`). Submit the same sitemap. IndexNow already pings Bing on each publish.
3. **GA4:** create a property + web stream for `https://hotwatermath.com`, send Claude the `G-…` ID (→ `integrations.ga4Id`). Mark `calc_result` as a key event.
4. **AdSense (when ready; site already has ≥15 live pages and all utility pages):** sign up with `https://hotwatermath.com`, send Claude the `pub-…` ID (→ `adsenseClient`; this switches on /ads.txt and the verification meta). In AdSense → Privacy & messaging, create and publish the **European regulations (GDPR)** message and the **US state regulations** message. After approval create 4 Display ad units (Responsive) and send their IDs for slots A, B, C, S. Keep **Auto ads off**.
5. **LiteSpeed Cache:** keep it on; after each deploy Purge All (theme also purges on version change). Hostinger CDN: flush if a page looks stale.
6. Make sure `contact@hotwatermath.com` receives mail; add hotwatermath.com to your LinkedIn profile.
7. **Hostinger Tools plugin:** deactivate and delete it (README step 4), then in hPanel → File Manager delete `public_html/llms.txt`, so the theme's `/llms.txt` (live pages only) is served instead of the plugin's file.
8. Optional: to make `http://www.` redirect in one hop, ask Hostinger support to redirect `www` straight to `https://hotwatermath.com` at the edge. Google follows two hops fine, so this is low priority.

**Post-launch watchlist (Search Console → Performance, check weekly for 8 weeks):**
- Tools: water heater age calculator; how old is my water heater; what size tankless water heater do i need; tankless water heater size calculator; what size water heater do i need; water heater size calculator; repair or replace water heater; how much is a new water heater; water heater cost calculator; tankless vs traditional water heater; gas vs electric water heater.
- Articles/brands: how long does a water heater last; bradford white water heater age; rheem / a.o. smith / state / whirlpool / american / navien water heater age (and "serial number lookup").
- Pages report: all 25 live URLs "Indexed"; Core Web Vitals report once field data arrives (fix any URL group that is not "Good").

**Known issues / TODOs carried forward:**
- With real AdSense on, Lighthouse Performance on the home page may drop to ~93 (Google scripts' TBT); scripts already load after `load` + idle. Re-measure once real ads run.
- Calculator forms have no submit button (html-validate wcag/h32); results update live with `aria-live`.
- IndexNow ping result is stored in the WP option `hwm_indexnow_last` (not visible from outside); live check done by a direct API call (see live results).
- Carried from Phase 4: labor ranges editorial (DOE-anchored); gas tankless fan electricity ignored; tank presets editorial; Navien Y/Z months third party; GE/Kenmore sections only; Rinnai not decoded.

**Notes for the next thread (Phase 6b batch 1):**
- `npm run check-links` must pass after deploy; new articles must be linked from /guides/ (automatic when `live`) and have ≥3 body links.
- Phase 6b acceptance needs /guides/ to group "Troubleshooting" separately from "Costs & Sizing": the hub template now groups by `parent` for tools; add a `group` field to article entries and group on it the same way.
- New pages stay `draft` until the owner's OK; the home "Recently updated" list and hubs pick them up automatically once live.

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
