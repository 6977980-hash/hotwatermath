# PHASES.md — Build plan, one phase per Claude Code thread

> Each phase is run in a **fresh thread**. The owner pastes the "Kick-off prompt" for that phase. The thread reads `CLAUDE.md` → `docs/HANDOFF.md` → this phase → the needed parts of `docs/STRATEGY.md`, does the work, runs the Acceptance Checklist, updates `docs/HANDOFF.md`, commits, opens one pull request, **auto-merges it when the checklist passes**, verifies the live site after Hostinger deploys, and tells the owner the next phase's kick-off line (CLAUDE.md §9). Nothing else. No phase starts until the owner asks for it.

## Phase map (run in this order, one new thread each)

| # | Phase | What it delivers | Threads |
|---|---|---|---|
| 0 | Foundation | Theme skeleton, design, logo/favicon, utility pages, SEO output | 1 |
| 1 | Data layer | All sourced data files (every source opened and verified), formulas, methodology | 1 |
| 2 | Tools 1–2 | Age calculator + serial decoder, tankless size calculator | 1 |
| 2b | Lifespan + brands | "How long does a water heater last" article, per-brand decoder pages (order: Bradford White, A.O. Smith, Rheem, State, Whirlpool, then American, GE, Kenmore, Navien, Rinnai). The first thread (lifespan + first 4 brands) must be done before launch. | 3 (4 brand pages per thread) |
| 3 | Tools 3–4 | Tank size calculator, repair-or-replace | 1 |
| 4 | Tools 5–7 | Cost calculator, tankless vs tank, gas vs electric | 1 |
| 5 | Launch | Home, hubs, link audit, AdSense/consent/ads.txt, GA4, GSC + Bing, launch-day checks | 1 |
| 6 | Expansion articles | Installation cost pages, heat pump, 40 vs 50, expansion tank | 1–2 |
| 6b | Troubleshooting | Care and troubleshooting cluster. **Batch 1 (leaking, how to flush, anode rod, no hot water) runs right after Phase 5, before Phase 6**; batch 2 (reset button, noise, runs out fast, rotten eggs) after Phase 6. | 2 (4 pages per thread) |
| 6c | State pages | 10 state cost pages | 1 |
| 6d | Safety pages | Gas line, electrical, pilot light, T&P valve, element, temperature | 2 (3 pages per thread) |
| 7 | Optimization | Monthly, from Search Console data | 1 per month |
| 8 | Authority | /data page, cost index article, embed landing, outreach material | 1 |

Each thread's last three steps are always the same: update HANDOFF.md → auto-merge the pull request → verify live and report.

Legend: ✅ = must pass before the phase is marked done.

---

## Phase 0 — Foundation (repo, design system, layouts, utility pages)

**Kick-off prompt (paste into a new thread):**
> Read CLAUDE.md, then docs/HANDOFF.md, then "Phase 0" in docs/PHASES.md. Do Phase 0 only. When finished, run the acceptance checklist, update docs/HANDOFF.md per CLAUDE.md §9, and commit as `phase-0: foundation`.

**Tasks**
1. Theme skeleton at the repo root (repo root = theme root): `style.css` (theme header only), `functions.php` (loads `inc/*.php`), `index.php`, `page.php`, `404.php`, `header.php`, `footer.php`, `templates/tool.php`, `templates/article.php`, `templates/hub.php`, `templates/embed.php`. Add `.editorconfig`, `.gitignore`, `package.json` (dev scripts only), `README.md` (how the theme deploys through Hostinger Git, how to run the scripts), `docs/DECISIONS.md` (keep existing rows).
2. `data/site.json` with brand "Hot Water Math", domain `hotwatermath.com`, author "Ali Ahmad" (founder) with `sameAs` LinkedIn URL from CLAUDE.md §1, contact email, `integrations` (AdSense client, GA4 ID, GSC token, Bing token, IndexNow key — all empty) and default assumptions **with source fields left empty for Phase 1 to fill**.
3. `assets/css/global.css`: CSS variables (light theme only for v1), system font stack, type scale, spacing, container (max 1100px), components: buttons, inputs (≥44px), cards, tables (responsive wrapper), `.quick-answer`, `.ad-slot` (reserved heights per CLAUDE.md §7), `<details>` FAQ styling, result panel (green/amber/red states), skip-link, focus styles. Print stylesheet basics.
4. `inc/cleanup.php`: the WordPress clean-up list in CLAUDE.md §2 (emoji, oEmbed, block CSS, generator, jQuery migrate, comments off, author/date/tag/category/attachment archives off, default post/sample page not indexable). Permalinks `/%postname%/`; non-slash → slash 301.
5. `inc/data.php`: `hwm_data()`, `hwm_value()`, `hwm_table()` reading `data/*.json` and `data/computed/*.json` (cached in a static variable). `inc/pages-sync.php`: creates/updates one WordPress Page per `data/pages.json` entry when the theme version changes (slug, title, parent, template; never deletes without a registry flag). `status: draft` → `noindex` + excluded from sitemap and hubs; `planned` → no page; `live` → indexable.
6. `inc/seo.php` + `inc/schema.php`: title, description, canonical (trailing slash), robots, OG/Twitter, JSON-LD helpers for WebPage/BreadcrumbList/WebApplication/Article/Organization/WebSite/Person/FAQPage, all driven by `data/pages.json` and `data/site.json`. Core sitemap filtered to live pages. `robots_txt` filter.
7. Template parts in `parts/`: `breadcrumb`, `quick-answer`, `ad-slot` (prints nothing while the AdSense client is empty), `faq` (renders `<details>` + FAQPage JSON-LD from an array), `sources`, `author-line`, `related-tools`, `data-table` (sourced table with caption + source line), `next-steps`, `calculator` (wrapper that server-renders the default result from `data/computed/`).
8. Shared calculator plumbing: `assets/js/formulas.js` (empty exports with JSDoc stubs listing every function from STRATEGY §3), `assets/js/ui.js` (helpers: read inputs, format currency/number, URL param state, copy/share, debounce). Scripts enqueued as modules only on pages whose registry entry names them.
9. Utility pages with real content (not lorem) as `content/<slug>.php`: `/about`, `/methodology` (structure + placeholders that say "to be completed in Phase 1/2" — no fake content), `/editorial-policy`, `/contact` (mailto + simple form note), `/privacy-policy` (AdSense/GA4-compliant per CLAUDE.md §7: Google as vendor, advertising cookies, opt-out, consent), `/terms`, `/affiliate-disclosure`, `/404`, `/calculators` and `/guides` hubs (lists wired to `data/pages.json`), temporary `/` that lists coming tools.
10. `inc/routes.php`: virtual root files `/ads.txt` (empty until a publisher ID is set), `/llms.txt` (skeleton, optional), IndexNow key file; `/embed/<tool>/` rewrite rule (template only, tools come later). `inc/headers.php`: security headers, framing denied except `/embed/*`. Brand assets per CLAUDE.md §6: SVG logo, 512×512 PNG logo for schema, full favicon set (`/favicon.ico` at root, SVG, 192/512 PNG, apple-touch-icon, manifest). `scripts/build-og.mjs` (SVG → PNG into `assets/og/`, committed).
11. Scripts (dev only): `npm run check` → `scripts/check-seo.mjs`, which fetches every `live` and `draft` URL from the deployed site and validates exactly one H1, a canonical with trailing slash, a description, ≥1 JSON-LD block, correct robots for the page's status, and that no default WordPress URL (author, category, attachment, `?p=`) is indexable.
12. README notes for Hostinger: Git deploy path `public_html/wp-content/themes/hotwatermath`, branch `main`; LiteSpeed Cache settings to use and to avoid (CLAUDE.md §2); WordPress settings the owner must click once (activate theme, permalinks, "discourage search engines" OFF, timezone, delete default content).

**Acceptance Checklist**
- ✅ Theme activates with no PHP notices (`WP_DEBUG` on); PR merged, Hostinger deployed, live site checked.
- ✅ `npm run check` passes against the live site.
- ✅ Lighthouse mobile on `/about` ≥95/95/95/100 with LiteSpeed cache on.
- ✅ No external fonts/scripts; CSS ≤30KB; no jQuery, emoji or block-library assets in the page source.
- ✅ Every utility page has unique title/description/canonical/breadcrumb schema; default WordPress archives return 404 or redirect.
- ✅ HANDOFF.md updated; `data/pages.json` registry and the page-sync behaviour documented.

---

## Phase 1 — Data layer (all sourced data + methodology text)

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 1" in docs/PHASES.md, and docs/STRATEGY.md §3–§4. Do Phase 1 only: create every data file with real, sourced values, and write /methodology. Run the checklist, update HANDOFF.md, commit as `phase-1: data layer`.

**Tasks**
1. Create every file listed in STRATEGY §4 with real values, `meta.source/sourceUrl/sourceDate`, and inline `notes` for any judgment call. Use web search to find primary sources (DOE/energy.gov, ENERGY STAR, EIA, EPA WaterSense, IRS, manufacturer spec sheets/serial decoders). Prefer .gov. Where only industry ranges exist (costs), state our own range and list 2–3 references used as sanity checks.
2. `scripts/validate-data.mjs`: fails the build if any data file lacks `meta.source`, or any numeric field is null, or any state is missing from state-keyed files (50 + DC).
3. Implement every function in `assets/js/formulas.js` as pure functions with unit tests (`node --test`), using data imports only via parameters (no hardcoded constants except physical ones: 8.34 lb/gal, 3412 BTU/kWh, 100,000 BTU/therm, 500 ≈ 8.34×60). Add `scripts/build-data.mjs` (`npm run build:data`): runs the formulas with the default inputs and writes `data/computed/*.json` (default results, size charts, per-state tables); a check fails if computed files are stale.
4. Write `/methodology` fully: each formula, each data source, update cadence (quarterly review of prices/costs, annual review of the rest), limitations, how adjustments (hardness/maintenance) were decided, and the **cross-check section skeleton** (per CLAUDE.md §1): a table per tool to be filled in Phases 2–4 with 3+ test cases comparing our output against public manufacturer sizing guides/tables (link each source). Add `dateVerified` to `site.json` per tool.
5. Fill `site.json` default assumptions with EIA sources and dates.
6. `/llms.txt` output gains a methodology summary (optional, low priority).

**Acceptance Checklist**
- ✅ `npm run validate-data` passes; `node --test` passes (≥1 test per formula, including an edge case); `npm run build:data` output committed and fresh.
- ✅ Every data file has a working `sourceUrl` (script checks for 200 OK at least once; record result in HANDOFF).
- ✅ `/methodology` has no placeholders.
- ✅ HANDOFF lists each data file with a one-line description + source.

---

## Phase 2 — Tools 1 & 2: Age Calculator + Serial Decoder, Tankless Size Calculator

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 2" in docs/PHASES.md, and docs/STRATEGY.md §2 (items 1–2), §3.1–3.2, §5–§6. Build both tool pages completely (calculator + full content + schema). Run the checklist, update HANDOFF.md, commit as `phase-2: age and tankless tools`.

**Tasks**
0. **SERP briefs first** (CLAUDE.md §4): write `docs/briefs/water-heater-age-calculator.md` and `docs/briefs/tankless-water-heater-size-calculator.md` from the top-5 US results before writing any content.
1. `/water-heater-age-calculator` per STRATEGY §3.1: calculator module `age.js`, per-brand serial decoding table rendered in HTML, "where to find the serial" SVG, full content per CLAUDE §4 and STRATEGY §5, FAQ from the page's keyword list (must include: how to tell age from serial number; where is the serial number; what if my brand is not listed; how long does a water heater last — one short answer that links to the lifespan article 1b; signs it's failing), Sources (≥3, including the Rheem and Ruud official lookups), WebApplication + FAQPage + Breadcrumb + WebPage schema. The table on this page is the all-brands **summary**; the full per-brand detail belongs to the brand pages built in Phase 2b (mark them `planned`).
2. `/tankless-water-heater-size-calculator` per §3.2: `tankless-size.js`, state dropdown → groundwater temp, fixtures, gas/electric outputs, size chart table (GPM × ΔT), cold-climate electric warning, FAQ (family of 4; 2 vs 3 bathrooms; electric vs gas sizing; what temperature rise means; can one unit run two showers), Sources, schema.
3. Both pages: server-rendered default result, URL-param state, copy/share, **embed snippet + `/embed/<tool>` route** (compact, no nav/ads, `noindex`; credit link = brand-name anchor with `rel="nofollow"`, removable, per CLAUDE.md §7b), GA4 `calc_result` event hook (no-op until GA flag set), "How we calculated" with live numbers, NextSteps box (links may point to Phase 3–4 URLs that don't exist yet — mark them in `pages.json` as `status: planned` so the hub hides them until built).
4. Register both pages in `pages.json` as `status: draft` (noindex until owner flips to `live`); hubs list them; update `/llms.txt`.
5. Word count: ≥1,000 words of real content each (excluding tables/FAQ). Readability: short paragraphs, no filler. Each page has ≥1 unique non-template element (SVG, flowchart, worked example).

**Acceptance Checklist**
- ✅ Calculators produce correct results for 3 hand-checked cases each (write them in HANDOFF) **and** the `/methodology` cross-check table for each tool is filled with those cases vs a manufacturer sizing guide (linked), differences explained; `dateVerified` set.
- ✅ Works with JS disabled (default result + all content visible).
- ✅ Rich Results Test / schema validator: 0 errors.
- ✅ Lighthouse mobile ≥95/95/95/100 on both; CLS ≤0.02 with ad slots reserved.
- ✅ Every number in prose comes from data/formulas (grep for hardcoded digits in `content/*.php` prose; justify any exceptions in HANDOFF).
- ✅ Differentiation rule (CLAUDE.md §1) met: sources of defaults and the cross-check link are visible next to the result; the SERP brief says how the page differs from the clone calculators.
- ✅ PR merged, deployed, and both pages checked on the live site (as `draft`/noindex).

---

## Phase 2b — Lifespan article + per-brand serial decoder pages

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 2b" in docs/PHASES.md, and docs/STRATEGY.md §2 (items 1, 1b, 1c), §3.1, §5–§6. Build the lifespan article, then the brand pages in the order listed in STRATEGY §2 item 1c (4 brand pages per thread maximum). Checklist, HANDOFF, commit as `phase-2b: lifespan and brand age pages <batch>`.

**Tasks**
0. SERP briefs first: one for `/how-long-does-a-water-heater-last`, one per brand page (search "{brand} water heater age" and "{brand} water heater serial number lookup").
1. `/how-long-does-a-water-heater-last` — article template: Quick Answer (lifespan by type), lifespan table from `lifespans.json`, what shortens/extends life (hardness, maintenance, anode), signs of failure, tank vs tankless vs heat pump, embedded age calculator ("check your own unit"), FAQ (tankless lifespan; electric vs gas; can it last 20 years; does warranty length predict life), Sources, Article schema. ≥1,200 words, never padded.
2. Brand pages `/{brand}-water-heater-age` per STRATEGY §2 item 1c, in the volume order listed there. Each: decoder preset to the brand (`?brand=` on the shared component), that brand's full format table with every era/variant from `serial-formats.json`, 2+ worked examples with real-format serials, label location (SVG or owner photo), brand-specific warranty/lifespan notes (sourced), link to the manufacturer's own lookup where one exists, links back to the age calculator and to Repair-or-Replace.
3. Only build a brand page if `serial-formats.json` has a sourced, distinct format for it. Brands sharing a format share one page. If two brand pages would read the same apart from the name, merge them.
4. Age calculator page: summary table rows now link to the live brand pages; flip brand pages from `planned` to `draft`.

**Acceptance Checklist** — Phase 2 checklist per page; ✅ a diff of any two brand pages shows ≥40% different body text/tables; ✅ decoder gives the correct date for 3 hand-checked serials per brand (written in HANDOFF, with the source); ✅ no brand page targets the generic "water heater age calculator" query in title/H1; ✅ lifespan article and tool page do not share a title/H1 intent.

---

## Phase 3 — Tools 3 & 4: Tank Size Calculator, Repair-or-Replace

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 3" in docs/PHASES.md, docs/STRATEGY.md §2 (items 3–4), §3.3–3.4, §5–§6. Build both pages completely. Checklist, HANDOFF, commit as `phase-3: tank size and repair-or-replace`.

**Tasks**
0. SERP briefs for both pages in `docs/briefs/` first.
1. `/water-heater-size-calculator` per §3.3 (peak-hour demand → FHR → tank by fuel), rule-of-thumb table by household size, "why FHR beats gallons" section, 40 vs 50 note, FAQ (family of 4/5/2/6; 3/4 bedroom house; how many gallons; gas vs electric size difference; first hour rating).
2. `/repair-or-replace-water-heater` per §3.4, accepting `?age=&type=` from the age calculator; decision rules rendered as a visible flowchart (SVG) + text; "questions to ask the plumber"; FAQ.
3. Cross-link: Age → Repair/Replace (pass params), Tank size ↔ Tankless size, both → Cost (planned).
4. Update `pages.json`, hubs, `/llms.txt`.

**Acceptance Checklist** — same as Phase 2 (3 hand-checked cases per tool, no-JS, schema 0 errors, Lighthouse, no hardcoded numbers).

---

## Phase 4 — Tools 5, 6, 7: Cost Calculator, Tankless vs Tank, Gas vs Electric

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 4" in docs/PHASES.md, docs/STRATEGY.md §2 (items 5–7), §3.5–3.7, §5–§6. Build all three pages completely. Checklist, HANDOFF, commit as `phase-4: cost and comparison tools`.

**Tasks**
0. SERP briefs for all three pages in `docs/briefs/` first.
1. `/water-heater-cost-calculator` per §3.5 with itemized output, state multiplier, scenario presets (`?type=&size=` so other pages can deep-link), "how to get a fair quote" checklist, FAQ (how much is a new water heater; 40 gallon replacement cost; 50 gallon cost; labor cost; permit needed?).
2. `/tankless-vs-tank-water-heater` per §3.6: 10-year TCO table, break-even, pros/cons table, incentives with review date, FAQ (worth it? lasts longer? cost difference? hybrid vs tankless?).
3. `/gas-vs-electric-water-heater` per §3.7: operating cost table across 5 types, wattage/kWh/therms answers rendered from formulas, FAQ.
4. Wire all NextSteps boxes across the 7 tools (STRATEGY §6). Flip planned → live in `pages.json`.

**Acceptance Checklist** — same as Phase 2, plus: ✅ the same household inputs give consistent numbers across cost / TCO / operating-cost pages (shared formulas) — document one cross-check in HANDOFF.

---

## Phase 5 — Launch: home, hubs, interlinking audit, ads, QA, deploy

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 5" in docs/PHASES.md. Do the launch phase only. Checklist, HANDOFF, commit as `phase-5: launch`.

**Tasks**
1. Homepage: H1 with brand + "water heater calculators", Quick Answer about what the site does, 7 tool cards with one-line value props, "how our numbers are made" trust strip linking to /methodology, latest-updated list, Organization + WebSite schema.
2. `/calculators` and `/guides` hubs finalized with descriptions and schema (`CollectionPage`).
3. Internal-link audit script `scripts/check-links.mjs`: no broken internal links, every page has ≥3 internal links in body, every tool linked from home + hub. Fix gaps.
4. AdSense: load the AdSense script (deferred) only when `integrations.adsenseClient` in `data/site.json` is set; slots A/B/C/S per CLAUDE §7; verify CLS stays ≤0.02 with it on (use a test client ID). **Compliance pass (CLAUDE.md §7):** `/ads.txt` returns the correct line once the publisher ID is set; Consent Mode v2 defaults in place before GA4/AdSense load; privacy policy text complete; every affiliate link has `rel="sponsored nofollow noopener"`; every embed credit link has `rel="nofollow"` and a brand-only anchor. Owner steps listed in HANDOFF: turn on "Privacy & messaging" in AdSense (EEA/UK/Swiss consent message + US state opt-out message) after the account exists.
5. GA4 (deferred) when `integrations.ga4Id` is set, with `calc_result` and `embed_view` events wired; Search Console and Bing Webmaster verification meta tags from `integrations`; IndexNow key file served and the ping-on-publish hook tested.
5b. **Trust pass:** verify every tool page shows the AuthorLine + disclaimer (CLAUDE.md §1/§4.11), `/about` states plainly what we are and are not (no plumbers, no referrals, no paid recommendations), `/methodology` cross-check tables are complete for all 7 tools with `dateVerified`, and `/editorial-policy` states ads/affiliate independence. Grep the whole site for "reviewed by", "licensed plumber recommends", "find a pro", "get quotes" — none may exist.
5c. Create `docs/OUTREACH.md` (owner playbook): data-asset and resource-page pitch templates (the embed is offered as a free extra with a nofollow brand credit, never traded for a link), HARO/Qwoted/Featured routine, resource-page outreach list (20 plumbing/HVAC/home-inspection sites), Reddit guidelines, Pinterest board plan, entity profiles checklist (LinkedIn page, X, Pinterest with identical name/logo/description → `Organization.sameAs`).
6. Final QA: run `npm run check`, `validate-data`, `check-links`, Lighthouse on home + 2 tools + 1 article-less utility page; HTML validation; schema validation on every page type; mobile manual pass (320px).
7. Go live: confirm WordPress "discourage search engines" is off, LiteSpeed cache on and purged, flip owner-approved pages to `live`; submit `wp-sitemap.xml` in **Google Search Console and Bing Webmaster Tools**; request indexing for the 7 tools in GSC; confirm an IndexNow ping returns 200/202. Write exact steps done into HANDOFF (owner does the dashboard clicks; list them).
8. `README.md` updated with the `integrations` settings; `/llms.txt` regenerated from the registry (optional).

**Acceptance Checklist**
- ✅ All scripts pass; Lighthouse targets met on sampled pages; 0 broken links; 0 schema errors.
- ✅ Site live on `hotwatermath.com`, sitemap submitted in GSC and Bing (or exact owner steps listed).
- ✅ `/ads.txt`, consent defaults, `rel` attributes on affiliate and embed links all verified on the live site.
- ✅ Launch-day checks on the live site: HTTPS forced and `http`/`www` redirect in one hop; favicon and logo load at their final URLs; every page has a 1200×630 OG image (test one share preview); TTFB under 600ms with cache on; no pop-ups; mobile pass at 360px on all 7 tools; Search Console property added so field Core Web Vitals start collecting.
- ✅ HANDOFF has the "post-launch watchlist" (queries to track in GSC, from STRATEGY §2).

---

## Phase 6 — Expansion articles (installation costs, heat pump, comparisons, technical)

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 6" in docs/PHASES.md, docs/STRATEGY.md §2 (items 8–13 and 16), §3.8, §5–§6. Build the pages in the order listed, each complete. Checklist, HANDOFF, commit as `phase-6: expansion articles`.

**Order & notes**
0. Build `/heat-pump-water-heater-cost` first (heat pump queries are up 900% in the 2026-11-13 Keyword Planner export), then continue in the order below.
1. `/water-heater-installation-cost` (primary keyword "water heater replacement cost", 50K) — the big one; embeds cost calc default; includes a by-state table rendered from `regional-cost-index` × base ranges; by-size and by-type tables; "replacement vs installation" clarified in one H2 (do NOT make a separate replacement page).
2. `/tankless-water-heater-installation-cost` (50K) — embeds cost calc preset tankless; venting/gas line/electrical upgrade sections (cost and what the upgrade is, no how-to; link to pages 14–15 once Phase 6d builds them).
3. `/heat-pump-water-heater-cost` — includes savings/payback calculator section (spec it in STRATEGY §3.8 first, then build), incentives table with review date.
4. `/gas-water-heater-installation-cost`, `/electric-water-heater-installation-cost` — shorter, embed cost calc presets, cross-link.
5. `/40-vs-50-gallon-water-heater` — embeds tank size calc; FHR comparison table.
6. `/water-heater-expansion-tank-size` — small calc (tank gallons + pressure → expansion tank size), sourced table.
(Pages 14 and 15, gas line size and electrical requirements, moved to Phase 6d.)
Each page: article template, Quick Answer, embedded calculator component, ≥1,200 words, tables, FAQ, Sources, Article schema, NextSteps.

**Acceptance Checklist** — Phase 2 checklist per page; ✅ SERP brief exists per page; ✅ `pages.json` + hubs + `/llms.txt` updated; ✅ no new page overlaps an existing intent (grep the Keyword Map).

---

## Phase 6b — Troubleshooting & care cluster (topical authority)

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 6b" in docs/PHASES.md, docs/STRATEGY.md §2 (items 17–28 except the safety-critical ones 19, 21, 24, 26), §5–§6. Build the pages in the volume order given in STRATEGY §2 (from the owner's Keyword Planner export), 4 pages per thread maximum. Batch 1 = leaking, how to flush, anode rod, no hot water; batch 2 = reset button, noise, runs out fast, rotten eggs. Checklist, HANDOFF, commit as `phase-6b: troubleshooting <batch>`.

**Tasks**
1. SERP brief per page. 2. Article template with varied structure (symptom → causes table → fix steps → when to replace → embedded Repair-or-Replace or Age calculator → FAQ → Sources). 3. Safety notices (gas, electricity, scald) sourced from CPSC/DOE where relevant; any gas or electrical section follows CLAUDE.md §8b (explain and cite, no how-to beyond the manufacturer manual). 4. Each page links to ≥2 tools and ≥2 sibling articles; tools' NextSteps boxes gain "common problems" links. 5. `status: draft` until owner review.

**Acceptance Checklist** — Phase 2 checklist per page; ✅ no page duplicates another symptom intent; ✅ hub `/guides` groups "Troubleshooting" separately from "Costs & Sizing".

---

## Phase 6c — State pages (data-driven, 10 states)

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 6c" in docs/PHASES.md, docs/STRATEGY.md §2 (State pages). Build `/water-heater-installation-cost/{state}` for the 10 most populous states from data only, as child pages of the parent article using one shared template. Checklist, HANDOFF, commit as `phase-6c: state pages`.

**Tasks**
1. Ten child-page entries in `data/pages.json` (parent = the installation-cost article) rendered by one template `templates/state-cost.php` reading `regional-cost-index`, `energy-prices`, `groundwater-temp-by-state`, `incentives` (state-filtered). 2. Each page must show visibly different numbers and at least one state-specific paragraph written from data (climate → tankless sizing note; rebates; typical fuel mix if sourced). 3. Cost calculator preset to the state. 4. `BreadcrumbList` with the parent article; parent article links to all state pages in a table. 5. Only the 10 states; the rest stay unbuilt until GSC proves the first 10.

**Acceptance Checklist** — Phase 2 checklist on 3 sampled states; ✅ a diff of two state pages shows ≥30% different body text/numbers; ✅ parent ↔ child links verified by `check-links`.

---

## Phase 6d — Safety-critical pages (built last)

**Kick-off prompt:**
> Read CLAUDE.md (especially §8b), docs/HANDOFF.md, "Phase 6d" in docs/PHASES.md, docs/STRATEGY.md §2 (items 14, 15, 19, 21, 24, 26). Build at most 3 pages per thread. Checklist, HANDOFF, commit as `phase-6d: safety pages <batch>`.

**Run only when:** Phases 5, 6 and 6b are live and Search Console shows the tools and articles indexed. If the owner has arranged a plumber fact-check (CLAUDE.md §8b open option), record it in DECISIONS first.

**Tasks**
1. SERP brief per page, listing which official source backs each claim the top results make.
2. Pages: `/gas-line-size-for-tankless-water-heater` (BTU → pipe size table quoted from NFPA 54/IFGC sizing tables, local AHJ caveat, link to tankless size calc), `/tankless-water-heater-electrical-requirements` (kW → amps → breakers/wire table, 125% rule, NEC in general terms, "licensed electrician" notice), `/water-heater-pilot-light-wont-stay-lit`, `/water-heater-temperature-setting` (CDC/DOE/CPSC on scald vs Legionella), `/water-heater-pressure-relief-valve-leaking`, `/water-heater-element-replacement`.
3. Every page follows CLAUDE.md §8b: safety notice at top, a citation on every safety claim, no step-by-step gas or live-electrical instructions beyond the cited manufacturer manual, "what it is, what the code/manual requires, what it costs, when to call a licensed professional".
4. Each page embeds the most relevant calculator and links to ≥2 tools.

**Acceptance Checklist** — Phase 2 checklist per page; ✅ every safety claim has an official-source citation (list claim → source in the brief); ✅ grep shows no imperative gas/electrical repair steps that are not attributed to a manufacturer manual; ✅ owner has read each page before `live`.

---

## Phase 7 — Post-launch optimization (repeat monthly)

**Kick-off prompt:**
> Read CLAUDE.md, docs/HANDOFF.md, "Phase 7" in docs/PHASES.md. The owner will paste GSC query/page data. Do one optimization cycle only. Update HANDOFF, commit as `phase-7: optimization <month>`.

**Tasks**
0. Compare real GSC impressions with the Keyword Planner buckets in STRATEGY §2 and correct the map where they disagree (buckets are ranges). Look at Bing Webmaster data too.
1. From GSC data: pages with impressions but CTR <2% → rewrite title/description (keep H1). Queries ranking 8–20 → add/upgrade an H2 or FAQ that answers them on the owning page (check Keyword Map first; never create a duplicate page).
2. New long-tail queries not in the Keyword Map → add to STRATEGY §2 as sections (default) or new pages (only if a distinct intent with its own volume).
3. Quarterly: refresh `energy-prices.json`, `cost-ranges.json`, `incentives.json` from sources; bump `lastReviewed`/`dateModified` only where values changed.
4. Re-run all check scripts + Lighthouse on changed pages.

---

## Phase 8 — Authority & links (runs in parallel with 6/6b/7, owner-driven, Claude assists)

**Kick-off prompt:**
> Read CLAUDE.md §7b, docs/HANDOFF.md, "Phase 8" in docs/PHASES.md, docs/STRATEGY.md "Linkable assets". Build the linkable assets listed, then produce the outreach materials. Checklist, HANDOFF, commit as `phase-8: authority assets`.

**Tasks**
1. `/data` page: every public table as CSV download + `Dataset` schema + "cite this" box; link from `/methodology` and footer.
2. `/water-heater-installation-cost-index` annual article: by-state table + SVG chart, methodology, "embed this chart" snippet with attribution, press-style 5-bullet summary.
3. Verify every `/embed/<tool>` route works cross-origin (headers), is ≤30KB, is `noindex`, and that the snippet's credit link is a brand-name anchor with `rel="nofollow"` and is marked removable; add `/embed` landing page explaining how to embed. What we pitch for editorial links is the `/data` page and the index article; the embed is offered as a free extra, never in exchange for a link.
4. Write in `docs/OUTREACH.md`: 3 pitch templates (data/index article for writers, resource page, journalist/HARO); the embed is mentioned as a free extra only, a 30-site target list with contact method (owner fills emails), Reddit answer guidelines (help first, link only when asked/relevant), Pinterest pin plan (one SVG chart per tool).
5. Owner tasks (listed in HANDOFF, not done by Claude): create LinkedIn page, X, Pinterest with identical branding; add to `Organization.sameAs`; send first 20 pitches; sign up for HARO/Qwoted/Featured; post helpful answers weekly.

**Acceptance Checklist** — ✅ `/data`, index article and `/embed` live and validated; ✅ OUTREACH.md complete; ✅ HANDOFF has a monthly link-tracking table (date, followed editorial referring domains, embeds seen in GA4 as awareness only).

---

## Global Definition of Done (applies to every phase)
- All `scripts/*.mjs` checks pass (data validation, formula tests, fresh `data/computed/`, post-deploy SEO and link checks against the live site); no PHP notices with `WP_DEBUG` on.
- Change shipped through a PR merged to `main`, Hostinger deployed, changed pages verified live.
- Lighthouse mobile ≥95/95/95/100 on changed pages.
- Schema validated, 0 errors.
- No hardcoded data numbers outside `data/` and `formulas.js`.
- A SERP brief exists in `docs/briefs/` for every new content page.
- Every new page passed the quality gate in CLAUDE.md §8 (top 5 re-checked and beaten, every source opened and verified, results written in the brief) and stays `status: draft` (noindex) until the owner replies OK.
- `pages.json`, hubs and the sitemap reflect reality.
- No new plugin, no content written in the WordPress editor, affiliate links `rel="sponsored"`, embed credit links `rel="nofollow"`.
- `docs/HANDOFF.md` updated per CLAUDE.md §9 and committed.
