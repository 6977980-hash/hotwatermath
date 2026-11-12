# HANDOFF.md — State of the project between threads

> Every thread reads this second (after `CLAUDE.md`). Every thread **overwrites "Current State"** and **appends one line to "Log"** before finishing. Keep it short and factual. This file is the memory between phases.

## Current State

**Last completed phase:** Phase 0 — Foundation (2026-11-12)
**Next phase to run:** Phase 1 — Data layer (see `docs/PHASES.md`)

**What exists now:**
- `style.css` (theme header, Version 0.1.1), `functions.php` (loads `inc/*.php`), `header.php`, `footer.php`, `index.php`, `page.php` (home + utility), `404.php`, `search.php`
- `templates/tool.php`, `templates/article.php`, `templates/hub.php`, `templates/embed.php`
- `inc/data.php` — `hwm_data()`, `hwm_raw()`, `hwm_value()`, `hwm_site()`, `hwm_table()`
- `inc/helpers.php` — registry lookups, `hwm_url()`, `hwm_current()`, `hwm_part()`, `hwm_content()`, nav, registry search, asset enqueue (CSS + per-page JS modules from `scripts` in the registry), template routing by registry `type`, embed snippet
- `inc/cleanup.php` — WordPress clean-up per CLAUDE.md §2; non-registry URLs → 404; permalinks `/%postname%/`
- `inc/pages-sync.php` — registry → WordPress pages sync (see README "How pages work"); deploy signature; LiteSpeed purge
- `inc/seo.php` — title/description/canonical/robots/OG/Twitter/icons/verification meta; sitemap filtered to live pages; `robots_txt` filter
- `inc/schema.php` — JSON-LD: WebSite, Organization (+founder), WebPage/AboutPage/ContactPage/CollectionPage, BreadcrumbList, Person, WebApplication, Article, FAQPage
- `inc/routes.php` — virtual `/ads.txt`, `/llms.txt`, IndexNow key file, `/favicon.ico`, `/site.webmanifest`, `/embed/<tool>/`; IndexNow ping on live/dateModified change
- `inc/headers.php` — security headers; framing only same-origin, `/embed/*` frameable anywhere
- `parts/` — breadcrumb, page-intro, quick-answer, calculator (server-rendered default from `data/computed/<tool>.json`), ad-slot (prints nothing while `integrations.adsenseClient` is empty), faq (+FAQPage), sources, author-line (+disclaimer), related-tools, next-steps, data-table
- `assets/css/global.css` (≈15 KB, system fonts, light theme, print styles)
- `assets/js/formulas.js` (JSDoc stubs for every STRATEGY §3 function + physical constants), `assets/js/ui.js` (inputs, formatting, URL state, copy/share, debounce, result render, GA event hook)
- `assets/brand/` — `logo.svg`, `icon.svg`, `logo-512.png`, `icon-192.png`, `icon-512.png`, `apple-touch-icon.png`, `favicon.ico` (16/32/48)
- `assets/og/*.png` — one 1200×630 image per non-planned page + `default.png`
- `data/site.json` — brand, domain, author (Ali Ahmad, LinkedIn sameAs), contact email, empty `integrations`, `defaults` with empty sources (Phase 1 fills), `dateVerified` (empty)
- `data/pages.json` — registry: home, calculators, guides, about, methodology, editorial-policy, contact, privacy-policy, terms, affiliate-disclosure (all `draft`); the 7 tools + lifespan article (`planned`)
- `content/*.php` — real content for home (temporary), both hubs, about, methodology (structure + "to be completed in Phase N" notes, no numbers), editorial-policy, contact, privacy-policy, terms, affiliate-disclosure
- `scripts/check-seo.mjs` (`npm run check`), `scripts/build-og.mjs`, `scripts/build-brand.mjs`, `scripts/build-data.mjs` (no builders yet)
- `.htaccess` (blocks repo-only files from the web), `.editorconfig`, `.gitignore`, `package.json` (dev only), `README.md` (deploy, registry, owner WordPress settings, LiteSpeed settings)
- `docs/keyword-data/*.csv` — owner's Keyword Planner exports of 2026-11-13

**Verified:**
- Local WordPress 7.1.4 (PHP 8.3, `WP_DEBUG` + `WP_DEBUG_LOG` on): theme activates, pages sync, **no PHP notices or warnings** (debug.log never created).
- `npm run check -- --base=http://localhost:8080`: 118/118 pass (one H1, title ≤60, description 140–155, canonical with slash, robots = noindex for drafts, JSON-LD parses with BreadcrumbList + WebPage (+ WebSite/Organization on home, Person on about), no jQuery/emoji/block-library/embed assets, no external scripts or fonts, OG image, non-slash → 301, default WordPress URLs 404, robots.txt has Sitemap line, sitemap lists only live pages, no users/taxonomy/post sitemaps).
- Tool and article templates tested with a temporary draft tool (removed before commit): calculator part renders the server-side default, FAQPage + WebApplication schema, module script enqueued only on that page, `/embed/<tool>/` 200 with `noindex` + `frame-ancestors *`.
- Lighthouse (local, mobile, `/about/`): Performance 100, Accessibility 100, Best Practices 100, SEO 66 only because drafts are `noindex` ("is-crawlable"); expected 100 once live.
- 360px and 1280px screenshots: no horizontal scroll, menu toggle works, first viewport shows H1 + quick answer.
- **Live site (2026-11-12, after the owner activated the theme):** `npm run check -- --bust` against https://hotwatermath.com: 118/118 pass. Lighthouse mobile on live `/about/`: Performance 100, Accessibility 100, Best Practices 100, SEO 66 (noindex drafts only); LCP 0.8 s, CLS 0, TBT 0 ms, TTFB 220 ms. 360px render checked, no console errors, no horizontal scroll. Security headers present; `/robots.txt`, `/ads.txt`, `/site.webmanifest` served by the theme; repo-only files return 403; `http://` and `www` 301 to the apex.

**Known issues / TODOs carried forward:**
- **Hostinger CDN (`server: hcdn`) caches HTML separately from LiteSpeed.** After the theme switch the plain `https://hotwatermath.com/` URL kept serving the old theme's cached HTML (with the old Hostinger Reach script) while uncached URLs showed the new theme. The theme's LiteSpeed purge does not reach this CDN. After each deploy the owner flushes it in hPanel (Websites → hotwatermath.com → Performance → CDN → Flush cache), or turns the CDN off; verify with `npm run check` **without** `--bust` (that is what visitors and Google get).
- `/favicon.ico` at the root returns Hostinger's static 404 (see DECISIONS); the icon is served from the theme URL instead. Harmless, but do not "fix" it by uploading a file by hand.
- `http://www.hotwatermath.com/x` redirects in two hops (→ `https://www…` → apex). Hostinger-level; acceptable, revisit only if Search Console complains.
- `data/site.json` → `defaults` values for electricity/gas price are `null` with empty sources; Phase 1 fills them (and `setpointTempF` source).
- `/methodology` is structure only; Phase 1 writes formulas and data sources; Phases 2–4 add cross-check tables.
- GA4/AdSense/consent loaders are not written yet (Phase 5); the hooks exist (`integrations` in `site.json`, `track()` in `ui.js`, ad-slot part, ads.txt route).
- `npm run check` does not yet check `data/computed/` freshness (Phase 1 adds it with the first builder).
- `data/affiliate.json` not created yet (Phase 1).
- All pages are `draft`. The owner decides when utility pages go `live` (they can go live before the tools).

**Notes for the next thread:**
- Every PR must bump `Version` in `style.css`; after Hostinger deploys, open `https://hotwatermath.com/?sync=<random>` once (uncached request) so pages re-sync and LiteSpeed purges, then run `npm run check -- --bust`.
- Content files set `$faq`, `$sources`, `$next_steps`, `$related`, `$calculator_inputs`; never type numbers into prose, use `hwm_value()` / `hwm_table()` (`tables.<name>` convention documented in `inc/data.php`).
- A tool's registry entry lists its JS in `scripts` (e.g. `["age"]` → `assets/js/age.js`); `data/computed/<slug>.json` holds the server-rendered `default` result.
- Run `npm run build:og` after adding pages and commit the PNGs.

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
- 2026-11-12 — Phase 0 done — custom WordPress theme foundation: registry-driven pages, SEO/schema output, clean-up, utility pages, brand assets, check script.
