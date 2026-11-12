# CLAUDE.md — Water Heater Calculator Site (Master Instructions)

> **This file is the single source of truth.** Every thread, every phase, every page must obey it.
> If anything in `docs/` contradicts this file, THIS FILE WINS. If you believe this file is wrong,
> stop and ask the owner — never silently diverge.

## 0. How to work in this repo (read first, every thread)

1. Read, in order: `CLAUDE.md` → `docs/HANDOFF.md` → the phase section you were asked to do in `docs/PHASES.md` → only the parts of `docs/STRATEGY.md` that phase needs.
2. Do ONLY the phase you were asked to do. Do not start the next phase. Do not "improve" earlier phases unless the phase instructions say so.
3. Before finishing, run the phase's **Acceptance Checklist** and then **update `docs/HANDOFF.md`** (see §9). A phase is not done until HANDOFF.md is updated.
4. Keep tokens low: don't re-read files you already have in context; don't dump whole files into chat; summarize.
5. Never invent data. Every number that is not pure arithmetic (prices, temperatures, lifespans, efficiencies, fixture flow rates) must live in `data/*.json` with a `source` and `sourceDate` field, and content must render it from there (see §5).
6. If a decision is not covered here and matters (new dependency, new URL pattern, new page type), write it into `docs/DECISIONS.md` with a one-line rationale so later threads inherit it.

## 1. What this project is

A **US-targeted, English-language tool site** about residential water heaters. Core promise: *"Size it, age it, price it — with the real formulas, not guesses."*

- Audience: US homeowners replacing/choosing a water heater; secondary: DIYers, small plumbers.
- Business model: Google AdSense (primary), Amazon/Home Depot product affiliate (secondary, optional). **No contractor lead-gen, no plumber referrals, no "find a pro" widgets, no sponsored recommendations of any service provider.** We never name or link a specific plumber/company as a recommendation.
- Positioning (SERP re-checked 2026-11-12): informational queries are held by big publishers (Bob Vila, Angi, Homes & Gardens) and local plumber blogs. Age/serial queries are held by **manufacturer tools (Rheem, Ruud) and per-brand decoder pages (InspectApedia, building-center.org)**. Calculator queries are weak but **crowded with dozens of mass-generated calculator sites** (generic `*.pages.dev` clones). A plain calculator is therefore not a differentiator. We win with **accurate interactive calculators whose proof is visible on the page (named sources, manufacturer cross-check, state-specific data) + answer-first content + flawless technical SEO + real topical depth + earned links**.
- **Differentiation rule:** every tool page must show, next to the result and without scrolling far, what a clone site cannot: the named source of each input default, a link to that tool's manufacturer cross-check on `/methodology`, and state-specific data where it applies. If a page would look the same as a generic calculator clone, it is not ready.
- **Honest identity (E-E-A-T without pretending):** we are an independent *data and calculation* site built by a calculator developer, not plumbers, and we say so plainly. We never claim trade credentials, never invent reviewers, never show fake "expert" badges. Trust is earned through **transparent verification** instead:
  1. Every formula is published on `/methodology` with its primary source (DOE, ENERGY STAR, EIA, EPA WaterSense, IECC, manufacturer spec sheets).
  2. **Cross-check table:** for each tool, `/methodology` shows 3+ test cases comparing our output with the result from public manufacturer sizing guides/tables (e.g. Rheem, A.O. Smith, Rinnai, Navien, Bradford White sizing literature) for the same inputs, with links. Differences are explained, not hidden. This is re-run whenever formulas or data change.
  3. Every number on the site is traceable to a data file with a source and date (§5).
  4. A visible, plain-English disclaimer on every tool: estimates for planning, not professional advice; confirm sizing/code with a licensed professional in your area (generic — no names, no links, no lead forms).
  The AuthorLine reads: "Built by {{author}} · Formulas and sources on /methodology · Last verified {{dateVerified}} · Last updated {{dateModified}}".
- Owner and founder: Ali Ahmad (LinkedIn: `https://www.linkedin.com/in/ali-ahmad-chaudhry-12777486/`, stored as `author.sameAs` in `data/site.json`; shown on `/about` and in the AuthorLine link target, and used in `Person.sameAs` and `Organization.founder`). Contact email: `contact@hotwatermath.com`. **Brand: Hot Water Math. Domain: `hotwatermath.com`** (canonical origin `https://hotwatermath.com`, no `www`). These live in `data/site.json` and are read from there everywhere — never hardcode them in pages.

## 2. Tech stack (fixed — do not change)

Owner decision 2026-11-12: the site is a **custom WordPress theme on Hostinger**. The earlier Astro + Cloudflare Pages stack is cancelled (see `docs/DECISIONS.md`).

| Layer | Choice | Why |
|---|---|---|
| Platform | **WordPress** (latest stable) on Hostinger, PHP 8.2+ | Owner's choice |
| Theme | **One custom classic theme, built from scratch.** The repo root is the theme root (`style.css` with the theme header at the root). No parent theme, no page builder, no block-theme `theme.json` templates. | Full control of HTML, smallest payload |
| Deploy | Hostinger Git auto-deploy: repo `main` → `public_html/wp-content/themes/hotwatermath`. Every change goes through a pull request that is merged to `main`; after each deploy, verify the live site. | Owner's workflow |
| Styling | **One plain CSS file** `assets/css/global.css` + CSS variables (root `style.css` holds only the theme header). No Tailwind, no frameworks. | Smallest payload |
| Calculators | **Vanilla JS modules** in `assets/js/`, enqueued with `type="module"` only on the page that uses them. No jQuery on the front end. | No framework overhead |
| Data | **JSON files** in `data/` (sourced), read in PHP through one helper (`hwm_data()` / `hwm_value()`) | Single source of truth for all numbers |
| Computed values | `npm run build:data` runs `assets/js/formulas.js` in Node and writes `data/computed/*.json` (default results, pre-computed tables, per-state numbers). The files are committed. **PHP never does calculator math; it only renders data and computed files.** | Formulas stay in one place; pages are meaningful without JS |
| Content | Each URL is a WordPress Page created automatically from the registry `data/pages.json` (a sync routine in `inc/pages-sync.php` runs when the theme version changes). The page body lives in the repo as `content/<slug>.php` and is rendered by `templates/tool.php` or `templates/article.php`. Content is **not** written in the WordPress editor. | Content is versioned, reviewed in PRs, and survives a database loss |
| SEO output | The theme prints title, meta description, canonical, robots, Open Graph and JSON-LD itself (`inc/seo.php`, `inc/schema.php`). **No SEO plugin.** Sitemap = WordPress core `wp-sitemap.xml`, filtered to `live` pages only. | No plugin bloat, one source of truth |
| Plugins | **Allowed: LiteSpeed Cache only** (page cache, CSS/JS minify; no lazy-load of the calculator, no "delay JS" on calculator scripts). Any other plugin needs a `docs/DECISIONS.md` entry approved by the owner. | Speed without plugin sprawl |
| Fonts | System font stack only | No font requests |
| Images | Inline **SVG diagrams** made by us. Original photos taken by the owner (for example a real rating-plate label) are allowed and encouraged where they show first-hand experience. No stock photos, no downloaded images of brands/people. | CWV + licensing + experience signal |
| Analytics | GA4 loaded deferred, with Consent Mode v2 defaults (§7); Google Search Console and Bing Webmaster Tools verification meta | Standard |
| Ads | AdSense slots as **fixed-height reserved containers** (see §7) | Zero CLS |
| Settings | Public IDs (AdSense client, GA4 ID, GSC and Bing tokens, IndexNow key) live in `data/site.json` under `integrations`. An empty value turns that feature off. | No hardcoding, no dashboard-only settings |

WordPress clean-up the theme must do: remove emoji scripts, oEmbed discovery, `wp-block-library` CSS, RSD/WLW links, generator tag, jQuery migrate on the front end; disable comments, author archives, date archives, tag/category archives, attachment pages and the default "Hello world" post/sample page (redirect or 404) so no thin WordPress URL is indexable.

Dev tooling (not shipped to visitors): Node LTS + `npm` for `scripts/*.mjs` (data validation, formula tests, computed-data build, post-deploy checks against the live site). No other runtime deps unless written into `docs/DECISIONS.md`.

## 3. Information architecture & URL rules

- Flat, descriptive, hyphenated URLs, lowercase, no dates, no `/blog/`. WordPress permalink structure is `/%postname%/`, so **every live URL and canonical ends with a trailing slash** (the docs write URLs without it for brevity). The non-slash version must 301 to the slash version; internal links always use the slash version.
- Tools live at root: `/tankless-water-heater-size-calculator`, `/water-heater-age-calculator`, etc.
- Supporting/long-tail articles also at root (e.g. `/40-gallon-water-heater-replacement-cost`).
- Per-brand serial decoder pages at root: `/{brand}-water-heater-age` (e.g. `/rheem-water-heater-age`), see STRATEGY §2 item 1c.
- Hub pages: `/calculators` (all tools), `/guides` (all articles).
- Site utility pages: `/about`, `/contact`, `/methodology`, `/editorial-policy`, `/privacy-policy`, `/terms`, `/affiliate-disclosure`.
- One URL = one primary keyword = one search intent. Never create two pages that target the same intent. Check `docs/STRATEGY.md §Keyword Map` before creating any page; if a keyword is already assigned to a page, add a section to that page instead.
- Canonical is always self-referencing absolute URL. No URL parameters indexed (calculator state may be in the hash `#` or `?` but `?` versions get `noindex` via canonical to the clean URL).

## 4. Page template (every tool page, in this order)

1. `<header>` site nav (same everywhere, ≤7 links).
2. **Breadcrumb** (Home › Calculators › Page) with `BreadcrumbList` schema.
3. **H1** — exactly one, contains the primary keyword naturally. Below it one sentence (≤25 words) stating what the tool does and for whom.
4. **Quick Answer box** (`<div class="quick-answer">`) — **≤40 words, max 2 sentences**, directly answering the query in plain English *before* the tool (e.g. "Most 3–4 person households need a 50-gallon gas or 65-gallon electric tank. Use the calculator for your exact first-hour rating."). This is the AEO/GEO capsule; write it as if it will be quoted verbatim by an AI. **Rule:** on a 360px-wide phone the calculator's first input must still be inside the first viewport — if it isn't, shorten the H1 subtitle/Quick Answer, never move the tool down.
5. **The calculator** — inputs on top/left, live results on right/below, with: result headline, one-line explanation, "How we calculated this" collapsible showing the actual formula with the user's numbers, "Copy result", "Share link" (URL params) and **"Embed this calculator"** (iframe snippet with a brand-name credit link that follows the widget-link rules in §7b), and a **"What to do next"** box linking to the 2–3 most relevant internal pages. Fire a GA4 event `calc_result` (tool, key inputs bucketed) on each calculation.
6. **Ad slot A** (after results).
7. **H2 sections** (each H2 is a real question people search; first paragraph under each H2 answers it in ≤60 words, then detail): how the sizing/cost/age works, the reference table (HTML `<table>`, sourced), worked example, common mistakes, when to call a pro.
8. **Ad slot B** (mid-content).
9. **FAQ** — 5–8 questions taken from the page's keyword list in STRATEGY.md; each answer 30–70 words; uses `<details>`/`<summary>` + `FAQPage` schema (kept for answer engines; expect no FAQ rich result in Google, see §6).
10. **Sources** — numbered list of outbound links to primary sources (DOE/energy.gov, ENERGY STAR, EIA, IECC/ICC, manufacturer spec sheets). Minimum 3 per tool page.
11. **Author + verification line**: "Built by {{author}} · Formulas and sources on /methodology · Last verified {{dateVerified}} · Last updated {{dateModified}}" linking to `/about` and `/methodology`, followed by the one-line disclaimer (§1.4).
12. **Related tools** (3 cards) and `<footer>`.

Article (non-tool) pages use the same template minus the calculator, but **must embed the most relevant calculator as a compact component** where it fits (e.g. the cost article embeds the cost calculator preset to that size). **Vary article structure** (order of H2s, presence of a flowchart vs table vs checklist) so pages don't read as one template stamped 30 times — Google's scaled-content policy targets exactly that.

**SERP brief before writing any page (mandatory):** search the primary keyword (US), open the top 5 organic results, list in `docs/briefs/<slug>.md`: their H2s, the tables/tools they have, the questions they answer, what they get wrong or miss. Our page must cover everything useful they cover, *plus* the interactive tool, *plus* at least two things they miss. The brief must also state **how our page is visibly different from the generic calculator clones in that SERP** (§1 Differentiation rule) and **what content type Google ranks for the query** (tool, article, brand page): if the top results are articles, the keyword belongs to an article, not a tool page. The brief also gives an **organic difficulty verdict** (easy / medium / hard) from who ranks in the top 10, never from Keyword Planner's "Competition" column, which only measures advertisers (STRATEGY §1). Keep the brief in the repo (it's the evidence for later refreshes).

## 5. Single-source-of-truth data rules

- `data/site.json` — brand name, domain, author, contact, social, default assumptions (`setpointTempF: 120`, `defaultElectricRateUsdPerKwh`, `defaultGasRateUsdPerTherm`, their sources and dates).
- `data/*.json` — every table used by any calculator or article: fixture flow rates, peak-hour demand values, groundwater temperature by state, expected lifespans by type, serial-number formats by brand, unit/labor/extras cost ranges, regional cost multipliers, UEF ranges, fuel energy content.
- Every data file has a top-level `meta: { source, sourceUrl, sourceDate, notes }` and each record may override with its own `source`.
- Content must **render** numbers via the PHP helpers (e.g. `<?php echo hwm_value('lifespans', 'tank.gas.typical'); ?>` or `hwm_table('lifespans')`), never hardcode them in prose. If a number appears in prose, it must be imported from data. This is how the site stays contradiction-free.
- One place for formulas: `assets/js/formulas.js` exporting pure functions. The live calculator imports it in the browser; pre-computed tables and default results come from `data/computed/*.json`, generated from the same file by `npm run build:data`. There is no PHP copy of any formula. A check script fails if `data/computed/` is older than the formulas or data it was built from.
- Default assumptions shown to the user (temperature setpoint, efficiency, energy price) are always editable in the calculator and always labeled "assumed; change if you know yours".

## 6. SEO / AEO / GEO rules (non-negotiable)

**On-page**
- `<title>` ≤60 chars: `{Primary Keyword} – {Benefit} | Hot Water Math`. Meta description 140–155 chars with the primary keyword and a concrete number or outcome.
- One H1. H2s are questions or clear noun phrases from the keyword list. No keyword stuffing; natural density.
- Semantic HTML only: `header/nav/main/article/section/aside/footer`, `<table>` for tables, `<dl>` for definitions.
- Internal links: every page links to ≥3 related pages with descriptive anchors (never "click here"). Hubs link to all children; children link back to hub and to siblings.
- Outbound links to primary sources on every page (`rel="noopener"`, not `nofollow` for .gov/.edu/manufacturers).
- Real `datePublished` and `dateModified`; update `dateModified` only when content actually changes.

**Structured data (JSON-LD, one `<script>` per type, validated)**
- Every page: `WebSite` (home only) + `Organization` (home only) + `BreadcrumbList` + `WebPage`.
- Tool pages: `WebApplication` (applicationCategory `UtilitiesApplication`, `operatingSystem: "Web"`, `offers.price: 0`) + `FAQPage`. **Never add `aggregateRating` or `review`** unless real ratings are collected on the site; without them Google shows no software rich result, and that is accepted. The markup is there to describe the page correctly, not to win a rich result.
- Articles: `Article` with `author` (Person) and `publisher`. No `reviewedBy` — we have no reviewer and will not invent one.
- `Person` schema for the author on `/about` (name Ali Ahmad, jobTitle: "Founder, Hot Water Math" plus a truthful description such as calculator developer; `sameAs` = the LinkedIn URL in `data/site.json`). `Organization` on the home page carries `founder` pointing to the same Person. `/about` and `/contact` link the LinkedIn profile visibly. Owner task: add hotwatermath.com to that LinkedIn profile so the link goes both ways.
- Sourced data tables that we publish as downloadable CSV (`/data` page) get `Dataset` schema (name, description, license, distribution, source `isBasedOn`). Good for GEO and for earning citations.
- No `HowTo` schema (deprecated for rich results); use plain ordered lists. `FAQPage` rich results are not shown for sites like ours; we keep the markup for answer engines only and never count on it for CTR.

**AEO / GEO (answer engines & AI search)**
- Quick Answer capsule on every page (§4.4). Every H2's first paragraph is a complete standalone answer.
- Use consistent entity naming: "tankless water heater" (not "on-demand" except once as a synonym), "tank water heater" / "storage tank water heater", "heat pump water heater (hybrid)".
- Include at least one **comparison table** and one **definition** per page.
- Numbers always with units and a source. State assumptions explicitly.
- Publish `/llms.txt` (site summary + list of tools with one-line descriptions and URLs), generated from `data/pages.json`. Google has said it does not use this file, so it is optional and low priority: never spend review time on it and never treat it as a ranking factor.
- No content behind JS: all text, tables and FAQ answers are in the HTML; the calculator's *default* result is also server-rendered by PHP from `data/computed/` so the page is meaningful without JS.

**E-E-A-T**
- `/about`: who runs the site (real name, real role: independent developer of calculator sites), why it exists, how calculators are built and verified, what we are *not* (not plumbers, not a referral service, no paid recommendations). Honesty here is the trust signal.
- `/methodology`: formulas, data sources, the **cross-check table vs manufacturer sizing literature**, update cadence, limitations. Every tool's "How we calculated" links here.
- `/editorial-policy`: sourcing standards, corrections, ads/affiliate independence.
- Each tool page has a short **"Limitations"** paragraph (honesty signals).

**Technical**
- Lighthouse (mobile) targets: Performance ≥95, Accessibility ≥95, Best Practices ≥95, SEO 100. LCP <1.8s, CLS <0.02, INP <200ms.
- Sitemap: WordPress core `wp-sitemap.xml`, filtered so it lists only `live` pages (no users, taxonomies, or drafts). `robots.txt` (via the `robots_txt` filter) allows all and points to the sitemap. No `noindex` except drafts, embeds (`/embed/*`), and utility/thank-you pages.
- Search engines: verify the site in **Google Search Console and Bing Webmaster Tools**; submit the sitemap in both. Implement **IndexNow** in the theme (key file served at the root, ping when a page goes `live` or its `dateModified` changes). Bing also feeds ChatGPT and Copilot answers, so it is not optional.
- Caching: LiteSpeed page cache on for all public pages; purge on deploy. Pages must not depend on cookies or logged-in state.
- Open Graph + Twitter card meta on every page with a static OG image per page (generated by an npm script, SVG→PNG, committed to `assets/og/`) — no external image URLs.
- 404 page with search + links to hubs. Security headers sent by the theme (`send_headers` hook); `X-Frame-Options`/`frame-ancestors` deny framing everywhere except `/embed/*`.
- Root files the theme serves virtually (a theme deploy cannot write to the web root): `/ads.txt`, `/llms.txt`, the IndexNow key file. If a physical file with the same name exists in `public_html`, it wins, so do not create one by hand.
- **Brand assets (all made by us, in `assets/brand/`):** one SVG logo (wordmark + mark) used in the header; a square mark exported as PNG 512×512 for `Organization.logo` (crawlable, not blocked); favicon set: `/favicon.ico` (48×48, served at the root by the theme), SVG icon, 192×192 and 512×512 PNG, 180×180 `apple-touch-icon`, and a minimal web manifest with `theme-color`. Google only shows a favicon in results if it is a multiple of 48px, square, and at a stable crawlable URL, so never change its URL.
- **OG images:** 1200×630 PNG per page, under 200KB, with the page title and brand; `og:image:width/height/alt` set; `twitter:card` = `summary_large_image`.
- **Content images:** every `<img>` has `width`, `height` and descriptive `alt`; below-the-fold images `loading="lazy"`; the LCP element is never lazy-loaded; photos served as WebP/AVIF at the displayed size.
- **Domain hygiene:** HTTPS forced; `http://` and `www.` both 301 to `https://hotwatermath.com/` in one hop; WordPress Address and Site Address set to the canonical origin; no mixed content.
- **Speed is judged on real users:** Lighthouse is the lab gate, but Google ranks on field Core Web Vitals. Server response (TTFB) under 600ms with LiteSpeed cache on; after launch, watch the Core Web Vitals report in Search Console and fix any URL group that is not "Good".
- **UX basics:** readable 16px+ body text, contrast ≥4.5:1, sticky header no taller than 56px on mobile, no pop-ups or interstitials, no layout jump when results update, visible focus states, every form control labelled, working site search, back-to-top on long pages, footer with About/Contact/Privacy/Terms on every page.
- Mobile-first; calculator usable one-handed; inputs ≥44px tap targets; labels always visible; `inputmode="decimal"` on numeric fields.

## 7. Monetization rules

- AdSense slots: `A` (after calculator results), `B` (mid-content), `C` (end of article before related tools), `S` (sticky sidebar on desktop ≥1100px only). Each is a `<div class="ad-slot" data-slot="A">` with a **reserved min-height** (mobile 280px, desktop 250px) so CLS stays 0. Until AdSense approval, slots render nothing at all (no reserved empty box, so reviewers and users do not see blank holes); once the AdSense client ID is set, the reserved boxes switch on.
- Never place an ad between the H1 and the calculator. Never more than one ad in the first viewport.
- Affiliate (optional, products only): a "Units in this size class" block may appear **only after** a result and only on sizing pages; it lists product *categories/specs* (e.g. "50-gal gas, FHR ≥ 80") with retailer links from `data/affiliate.json`; labeled "Affiliate links — see disclosure". **Every affiliate link carries `rel="sponsored nofollow noopener"`** (Google's link spam policy requires paid/affiliate links to be qualified). If `affiliate.json` is empty the block does not render.
- **No service-provider recommendations of any kind** (no plumbers, installers, lead forms, "get quotes" buttons). If this ever changes, it requires a `docs/DECISIONS.md` entry and a visible "sponsored" label — never a hidden paid recommendation.
- Ads/affiliate never influence results or content. Say so on `/editorial-policy`.
- **AdSense compliance (required before applying):**
  - `/ads.txt` served at the root with the line `google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0`, built from the publisher ID in `data/site.json`.
  - **Consent:** use AdSense "Privacy & messaging" (Google's own certified CMP) for the EEA/UK/Switzerland consent message and the US state opt-out message. A Google-certified CMP is mandatory to serve ads to EEA/UK/Swiss visitors. GA4 and AdSense load with **Consent Mode v2** defaults set to denied for those regions until the visitor chooses. No third-party cookie-banner script.
  - `/privacy-policy` names Google as a third-party vendor, explains advertising cookies and how to opt out, and links to Google's "How Google uses information from sites that use our services" page.
  - No ad next to the calculator's buttons or inputs in a way that invites accidental clicks; no text asking users to click ads; ad label is "Advertisement" only.
  - Apply only when the site has the utility pages, working navigation, and ≥15 `live` pages of original content. Draft (noindex) pages do not count.
- **Upgrade path:** AdSense at launch → apply to **Journey by Mediavine** at ~10K sessions/month → Mediavine/Raptive at 50K+. Ad slot markup must stay generic (`.ad-slot[data-slot]`) so the provider can be swapped without touching pages.

## 7b. Authority & links (off-page is part of the plan, not an afterthought)

- **Embeddable widgets (brand and referral traffic, not a link scheme):** every tool offers an iframe embed (`/embed/<tool>` route: compact, no nav, no ads, `noindex`). Google's link spam policy names links embedded in widgets distributed across sites, so the credit link follows these rules without exception: anchor text is the **brand name only** ("Hot Water Math"), never a keyword; the link in the snippet we hand out carries **`rel="nofollow"`**; the snippet says the credit line may be removed; one link per embed. We never ask for a followed link in exchange for the widget. Embeds earn awareness, referral visits, and the chance that people who use the tool write about it on their own. Track embeds via a GA4 `embed_view` event with `document.referrer`.
- **Linkable data assets (our main link-earning mechanism):** `/data` page with downloadable CSVs + `Dataset` schema, and at least one annual "index" article (e.g. *Water Heater Installation Cost Index by State*) with a chart and a "cite this" box.
- **Digital PR / outreach (owner-run, documented in `docs/OUTREACH.md`):** HARO + Qwoted + Featured replies with calculator-backed numbers; resource-page outreach to plumbing/HVAC/home-inspection sites; genuine help on Reddit (r/Plumbing, r/HomeImprovement, r/DIY) linking the tool only when it answers the question; Pinterest pins of our SVG charts/tables.
- **Entity consistency:** same brand name, logo, description and contact on site, LinkedIn page, X, Pinterest, and `Organization` schema `sameAs`.
- Never buy links, never use PBNs, never do link exchanges at scale.

## 8. Content voice & quality bar

- Plain American English, 8th-grade readability, second person ("your household"). Short paragraphs (≤3 sentences). No fluff intros, no "In today's world", no "it's important to note", no emojis, no exclamation marks.
- Every claim with a number has a source. Prefer DOE/energy.gov, ENERGY STAR, EIA, IECC, manufacturer spec sheets. Never cite competitors' cost guides as the source of truth; use them only to sanity-check ranges, and say "industry ranges" with our own data file as the rendered value.
- Each page targets **one** intent (STRATEGY.md). Minimum 900 words of real content on tool pages, 1,200 on articles — but never pad; if the topic is covered, stop.
- US units (gallons, °F, BTU/h, kW, therms). Mention metric once in the FAQ if useful.
- Dates: use real build/update dates. Never fake "Updated 2026" badges.
- **Quality gate before publish (owner decision 2026-11-13: Claude does the checking):** every page is `status: draft` (noindex) until it passes this gate, done by Claude in the same thread and written into `docs/briefs/<slug>.md` under "Quality check":
  1. **Beat the SERP:** open the live top 5 US results for the primary keyword again and list, point by point, what each covers. Our page must cover every useful point and add at least two things none of them have. If it does not, improve the page before going further. Never copy wording or structure.
  2. **Verify every source:** open each source URL, confirm it loads, confirm the number or claim on our page matches what the source says today, and record URL + date checked + the quoted figure. A number whose source cannot be opened and confirmed is removed or replaced. Never cite from memory or from a search snippet.
  3. **Check the live page** after deploy: renders correctly on mobile, calculator gives the hand-checked results, schema valid.
  Then Claude posts a short summary to the owner (what beats the SERP, sources verified, anything uncertain). The page goes `live` when the owner replies OK. This is our defence against scaled-content problems and factual slips.
- **Plumber fact-check (owner decision 2026-11-13):** the owner will arrange it after launch and send the results; Claude then updates the affected pages and records who checked what and when (truthfully) per §8b. Until then, no "reviewed by" line.
- Each page must contain at least one element that is unique to it and not produced by the template (a specific table, an SVG diagram, a worked example with real numbers, a decision flowchart).
- **First-hand evidence:** where the owner can supply an original photo (a real rating plate, a serial label, an anode rod), use it with a truthful caption. Never imply hands-on trade experience we do not have.

### 8b. Safety-critical topics (weakest E-E-A-T area — extra rules)

These pages touch gas, mains electricity, scalding or Legionella, where wrong advice can hurt someone, and Google holds such content to a higher standard: `/gas-line-size-for-tankless-water-heater`, `/tankless-water-heater-electrical-requirements`, `/water-heater-pilot-light-wont-stay-lit`, `/water-heater-pressure-relief-valve-leaking`, `/water-heater-element-replacement`, `/water-heater-temperature-setting`, plus any gas or electrical section inside another page.

- **Built last** (Phase 6d), after the tools and the other articles are live and indexed.
- **Restate official sources only**: NFPA 54 / IFGC, NEC (in general terms), CPSC, CDC, DOE/energy.gov, and manufacturer installation manuals. Every safety claim carries its own citation. No advice that comes only from our own reasoning or from other blogs.
- **No step-by-step instructions** for gas work or for work on live electrical parts beyond what the cited manufacturer manual tells a homeowner to do. Explain what the problem is, what the code or manual requires, what it typically costs, and when it is a job for a licensed professional.
- A safety notice at the top of the page (gas smell → leave and call the utility; power off at the breaker; scald risk), sourced.
- Sizing tables for gas pipe or wire are presented as "what the code tables say, for discussion with your installer", with the local-authority caveat, never as a design the reader should install from.
- **Plumber fact-check after launch (owner will arrange and report):** when the owner sends the checker's corrections, Claude applies them, adds a `docs/DECISIONS.md` entry, and the page states truthfully who checked it and when. Until then there is no "reviewed by" line anywhere (§10).

## 9. Handoff protocol (every phase, mandatory)

At the end of your phase, overwrite the **"Current State"** section of `docs/HANDOFF.md` with:
1. Phase completed + date.
2. What exists now (pages, components, data files, scripts) — paths only, one line each.
3. What was verified (checklist results, Lighthouse scores, schema validation).
4. Known issues / TODOs carried forward (be honest).
5. Exact next phase to run and anything the next thread must know that is not in CLAUDE.md/STRATEGY.md.
Also append one line to the **"Log"** section: `YYYY-MM-DD — Phase N done — <one-line summary>`.
Commit with message `phase-N: <summary>`.

**Shipping a phase (owner's standing instruction, 2026-11-13: auto-merge):**
1. One phase = one new thread = one pull request to `main`. The HANDOFF.md update is part of that same pull request.
2. When the phase's Acceptance Checklist passes, **merge the pull request yourself (auto-merge)** without waiting for the owner. Hostinger then deploys automatically.
3. After the deploy, verify the changed pages on the live site. If something is wrong, fix it with a follow-up pull request in the same thread (also auto-merged) and correct HANDOFF.md.
4. Auto-merge covers code and `draft` (noindex) pages only. Flipping a page to `live` still needs the owner's OK (§8 quality gate).
5. Finish by telling the owner in plain words: what was built, what was verified live, and the exact kick-off line for the next phase. Never start the next phase in the same thread.

## 10. Hard "never" list

- Never change the stack, URL patterns, or page template without a `docs/DECISIONS.md` entry approved by the owner.
- Never hardcode a data number in prose or JS outside `data/` and `formulas.js`.
- Never create a second page for an intent already assigned in the Keyword Map.
- Never use stock photos, brand logos, or images of people (the owner's own original photos of equipment are fine, §2).
- Never add external scripts other than AdSense (including its Privacy & messaging consent message) and GA4.
- Never install a WordPress plugin other than LiteSpeed Cache, a page builder, or a parent theme without an owner-approved `docs/DECISIONS.md` entry. Never write page content in the WordPress editor.
- Never put a keyword in a widget/embed credit link, never ship that link without `rel="nofollow"`, and never ship an affiliate link without `rel="sponsored"`.
- Never add `aggregateRating`/`review` schema without real collected ratings.
- Never publish gas or electrical how-to steps beyond the cited manufacturer manual (§8b).
- Never write filler, fake dates, fake reviews, fake "expert" credentials, or a "reviewed by" line — the site has no external reviewer.
- Never recommend, name, link or embed a plumber/installer/contractor or any lead-gen form.
- Never skip the handoff update.
