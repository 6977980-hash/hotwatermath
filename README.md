# Hot Water Math – WordPress theme

The whole site (theme, page registry, content, data) for **hotwatermath.com**. The repository root is the theme root.
Rules for anyone working here are in [`CLAUDE.md`](CLAUDE.md); the build plan is in [`docs/PHASES.md`](docs/PHASES.md) and the current state in [`docs/HANDOFF.md`](docs/HANDOFF.md).

## How it deploys

- Hostinger hPanel → Websites → hotwatermath.com → Advanced → **GIT**
  - Repository: `https://github.com/6977980-hash/hotwatermath.git`, branch `main`
  - Install path: `public_html/wp-content/themes/hotwatermath`
  - Auto-deployment: on (Hostinger's GitHub connection deploys every push to `main`)
- Every change goes through a pull request merged into `main`. Hostinger then deploys automatically.
- On the first request after a deploy that changes `style.css` `Version` or `data/pages.json`, the theme re-syncs pages and purges the LiteSpeed cache (`inc/pages-sync.php`). Because a cached page never reaches PHP, trigger it with any uncached request, e.g. `https://hotwatermath.com/?sync=<anything>`. **Bump `Version` in `style.css` in every PR** so the purge runs.

## How pages work

| Piece | Where |
|---|---|
| Page registry (URL, type, status, title, meta description, H1, subtitle, quick answer, dates) | `data/pages.json` |
| Page body | `content/<slug>.php` (home: `content/home.php`) |
| Templates | `page.php` (home + utility), `templates/hub.php`, `templates/tool.php`, `templates/article.php`, `templates/embed.php` |
| Reusable parts | `parts/*.php` (breadcrumb, quick-answer, calculator, ad-slot, faq, sources, author-line, related-tools, next-steps, data-table) |
| Sourced data | `data/*.json` (read with `hwm_data()`, `hwm_value()`, `hwm_table()`) |
| Computed values | `data/computed/*.json`, built by `npm run build:data` from `assets/js/formulas.js` |

**Registry statuses**

- `planned`: no WordPress page exists. Hubs and the home page list it as "coming soon" without a link.
- `draft`: the page exists and is viewable, but it is `noindex`, not in the sitemap, not in hub lists or related links for visitors (logged-in editors see it everywhere, marked Draft).
- `live`: indexable, in `wp-sitemap.xml`, hubs, `/llms.txt`, and pinged to IndexNow when it first goes live or its `dateModified` changes. Only the owner's OK moves a page to `live` (CLAUDE.md §8).

**Page sync** (`inc/pages-sync.php`) creates or updates one WordPress Page per non-planned registry entry, matched by the `_hwm_slug` post meta. It never deletes; an entry set back to `planned` with `"remove": true` is moved to the WordPress trash. It also sets the home page as the static front page, keeps permalinks at `/%postname%/`, and unpublishes the default "Hello world" post and "Sample Page". Any WordPress URL that is not a registry page (posts, archives, attachments, feeds) returns 404.

**Content file conventions**: a content file echoes its H2 sections and may set `$faq` (`[['q'=>..., 'a'=>...]]`), `$sources` (`[['title','url','publisher','accessed']]`), `$next_steps` (`[['slug','text','why']]`), `$related` (slugs) and, for tools, `$calculator_inputs` (HTML). Numbers in prose must come from `hwm_value()` / `hwm_table()`, never typed by hand.

## Dev scripts (Node 20+, nothing here ships to visitors)

```bash
npm install
npm run check                               # SEO/robots/schema check against https://hotwatermath.com
npm run check -- --base=http://localhost:8080 --bust
npm run check-links                         # internal links 200, ≥3 body links, hubs cover every tool, trust line, embed/affiliate rel, banned phrases
npm run build:og                            # assets/og/<slug>.png, 1200×630, per non-planned page
npm run build:brand                         # logo/favicon PNGs + favicon.ico from assets/brand/icon.svg
npm run build:data                          # data/computed/*.json from assets/js/formulas.js + data/*.json (commit the output)
npm test                                    # formula unit tests (node --test)
npm run validate-data                       # meta/source fields, no nulls, 51 states, computed data not stale
NODE_USE_ENV_PROXY=1 npm run validate-data -- --urls   # also request every sourceUrl (record results in HANDOFF)
NODE_USE_ENV_PROXY=1 npm run fetch:sources -- eia noaa rpp energystar   # refresh generated data files from primary sources, then build:data
```

Run `npm run build:og` after adding a page or changing an H1/subtitle, and commit the PNGs.

## Integrations (`data/site.json` → `integrations`)

Every Google feature is switched on by filling one public ID and deploying. An empty value turns the feature off.

| Key | Example | What it turns on |
|---|---|---|
| `gscToken` | `abc123…` (the `content` of Google's HTML-tag method) | `<meta name="google-site-verification">` on every page |
| `bingToken` | `0F1E2D…` (Bing's `msvalidate.01` value) | `<meta name="msvalidate.01">` on every page |
| `ga4Id` | `G-XXXXXXXXXX` | Consent Mode v2 defaults + GA4, loaded after the page has loaded; `calc_result` and `embed_view` events from the calculators and embeds |
| `adsenseClient` | `ca-pub-1234567890123456` or `pub-…` | `/ads.txt` line, `<meta name="google-adsense-account">` (site verification), Consent Mode v2 defaults, and `adsbygoogle.js` loaded after page load (this also shows the "Privacy & messaging" consent message once it is turned on in AdSense) |
| `adsenseSlots.A/B/C/S` | `1234567890` (ad unit ID) | The reserved ad box for that slot: A after results, B mid-content, C end of article, S desktop sidebar. A box shows only when the client **and** its unit ID are set, so there are no empty boxes during the AdSense review |
| `indexNowKey` | 32 hex characters | `/<key>.txt` key file, and an IndexNow ping (Bing, Yandex and others) for every page that goes `live` or gets a new `dateModified`, sent on the first request after a deploy |

Consent Mode v2: all four signals default to `denied` in the EEA, the UK and Switzerland (`wait_for_update` 500 ms) and to `granted` elsewhere; Google's own certified consent message (AdSense → Privacy & messaging) updates them. No third-party cookie banner.

When you create AdSense ad units: use **Display ads**, shape **Responsive**, then paste only the `data-ad-slot` number here. Leave **Auto ads off** (they can insert ads above the calculator and move the layout).

## One-time WordPress settings (owner, in wp-admin)

1. **Appearance → Themes**: activate **Hot Water Math**. Activation creates the pages and sets permalinks to "Post name".
2. **Settings → Reading**: "Search engine visibility" (Discourage search engines) must be **off**. Pages stay `noindex` on their own until they are `live`.
3. **Settings → General**: WordPress Address and Site Address both `https://hotwatermath.com`; timezone `America/New_York` (or your choice; dates on pages come from the registry).
4. **Plugins**: keep **LiteSpeed Cache** only. Deactivate and delete the other Hostinger plugins (Hostinger Tools, Hostinger AI, Hostinger Reach/Easy Onboarding, etc.); they add scripts and admin features the site does not use (CLAUDE.md §2, §10).
5. **Posts / Pages**: nothing to do; default content is handled by the theme. Never write content in the editor.

### LiteSpeed Cache settings

- Use: Cache → Enable cache **on**, Cache Logged-in Users **off**, Browser cache **on**; Page Optimization → CSS Minify **on**, JS Minify **on**.
- Avoid: "Load JS Deferred / Delay JS", "Lazy Load Images" for anything above the fold, "Guest Mode / Guest Optimization", "Localize Resources", any CDN/QUIC.cloud image or font optimization, "Generate UCSS/CCSS". They can break the calculators or add third-party requests.
- After a deploy: LiteSpeed → Toolbox → Purge All (the theme also purges automatically on the first uncached request after a version change).
