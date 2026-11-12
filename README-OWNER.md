# How to use this package (owner notes — Roman Urdu)

## Setup (ek dafa)
1. Repo: `6977980-hash/hotwatermath` (ye hi WordPress theme ka root hai; Hostinger Git isko `public_html/wp-content/themes/hotwatermath` mein deploy karta hai).
2. Is folder ki files repo ke root mein daalo:
   - `CLAUDE.md` (root mein hi rehna chahiye — Claude Code isko automatically parhta hai)
   - `docs/STRATEGY.md`, `docs/PHASES.md`, `docs/HANDOFF.md`, `docs/DECISIONS.md`
   - `README-OWNER.md` (ye sirf aapke liye hai; chaho to repo mein na rakho)
3. Claude Code mein ye repo open karo / project banao.

## Har phase ke liye (naya thread) — phase mukammal hone pe PR khud auto-merge hota hai, Hostinger deploy karta hai, aur HANDOFF.md agle phase ke liye note rakhta hai
1. Naya thread/session shuru karo.
2. `docs/PHASES.md` se us phase ka **Kick-off prompt** copy kar ke paste karo. Bas. Aur kuch mat likho.
3. Thread khatam hone pe check karo ke `docs/HANDOFF.md` update hua hai aur commit hua hai. Agar nahi hua to likho: "Update docs/HANDOFF.md per CLAUDE.md §9 and commit."
4. Agla phase → naya thread → agla kick-off prompt.

## Aapko khud kya dena hai
- Domain aur brand set ho chuke hain: hotwatermath.com / Hot Water Math. Aapko kuch replace nahi karna.
- Phase 5 se pehle: AdSense client ID, GA4 ID, Search Console token, Bing Webmaster token. AdSense account banne ke baad "Privacy & messaging" on karna (consent message).
- Agar ho sake to apne heater ke label ki asli photo (brand pages ke liye). Keyword Planner ka data mil chuka hai.
- Phase 1 ke baad: `data/affiliate.json` mein Amazon/Home Depot links (optional).

## Agar Claude Code kuch badalna chahe
Agar wo stack/URL/template change karne ka kahe, pehle `docs/DECISIONS.md` mein entry likhwao aur aap approve karo. Warna "No — follow CLAUDE.md" likh do.

## Domain ideas (sab check karo availability)
waterheatercalc.com · heatersizer.com · hotwatermath.com · waterheaterwise.com · tanksizer.com · sizemyheater.com
Brand short, pronounceable, "water heater" ya "heater" ho to topical signal milta hai.

## v2 mein kya add hua (ranking ke liye)
- Har page se pehle SERP brief (docs/briefs/) — top-5 ko dekh kar unse behtar likhna lazmi.
- Har naya page `draft` (noindex) rehta hai jab tak aap parh kar `live` na karo.
- Koi plumber reviewer NAHI (aapka faisla). Trust ka tareeqa: /methodology pe har formula + source + manufacturer sizing guides se cross-check table, aur /about pe saaf likha ke hum developer hain, plumber nahi, aur kisi ko refer nahi karte.
- Har calculator ka embed widget (/embed/...) — sirf brand naam ka nofollow credit link (Google widget-link policy). Ye pehchan aur visitors ke liye hai, backlinks ke liye /data page aur cost-index article hain.
- /data page (CSV + Dataset schema) aur annual cost-index article — linkable assets.
- Phase 6b troubleshooting cluster (12 pages) — topical authority; Phase 6c state pages (10 states, sirf data-driven).
- Phase 8 authority/links — aapka kaam (outreach, Reddit, Pinterest, HARO); Claude material banayega.
- Traffic expectation (andaza, guarantee nahi): 12–18 mahine mein 10–25K (bina links) / 30–50K (links ke sath).

## v4 mein kya badla (12 Nov 2026 plan review)
1. "how long does a water heater last" ka alag article; age calculator ab sirf "age calculator / serial number" target karta hai.
2. Har brand ka alag serial decoder page (Rheem, A.O. Smith, Bradford White...) — naya Phase 2b.
3. Har tool page pe sources aur manufacturer cross-check result ke sath nazar aayein, taake clone calculator sites se farq dikhe.
4. Embed link: sirf brand naam, nofollow, hatane ki ijazat.
5. AdSense: ads.txt, Google consent message (EU/UK), affiliate links pe rel="sponsored", approval se pehle khali ad boxes nahi.
6. Schema: WebApplication bina ratings ke; FAQ aur llms.txt se rich result ki umeed nahi.
7. Gas/electrical/pilot light/Legionella pages sab se aakhir mein (Phase 6d), sirf official sources, koi DIY steps nahi. Plumber fact-check aapki marzi (abhi faisla nahi).
8. Poora stack WordPress custom theme (Hostinger) ke hisaab se likha gaya; Astro/Cloudflare khatam. Sirf LiteSpeed Cache plugin, koi SEO plugin ya page builder nahi.
9. Bing Webmaster + IndexNow; Keyword Planner numbers ranges hain, launch ke baad Search Console se theek honge. Traffic targets kam kiye.

Phase order: 0 → 1 → 2 → 2b → 3 → 4 → 5 → 6b (pehla batch) → 6 → 6b (doosra batch) → 6c → 6d (7 har mahine, 8 launch ke baad sath sath).

## 13 Nov 2026 ke faislay
- E-E-A-T: founder Ali Ahmad, LinkedIn profile About/Contact page aur schema mein jayega. Aap apni LinkedIn profile pe hotwatermath.com ka link laga do.
- Har page ka content top results se behtar hai ya nahi, aur har source asli aur sahi hai ya nahi, ye Claude check karega aur aap ko chhoti report dega. Aap sirf "OK" likho to page live hoga.
- Outreach aap launch ke baad karoge. Plumber fact-check aap launch ke baad karwa ke batao ge, Claude pages update karega.
