# SERP brief: /water-heater-leaking/

- **Primary keyword:** water heater leaking (Keyword Planner 50K, bid about $54)
- **Secondary (H2s on this page, not separate pages):** water heater leaking from bottom (5K), leaking from top, leaking from pressure relief valve (the full T&P page is Phase 6d), what to do if water heater is leaking, can a leaking water heater be repaired
- **Checked:** 2026-11-13 (US search; pages opened the same day)

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | hometips.com/repair-fix/water-heater-leaking.html | Home-repair publisher | Article | Condensation can look like a leak; leak points (from snippet) | Site returned a TLS error and then 503 to us; judged from the search snippet. No primary source visible |
| 2 | waypointinspection.com/water-heater-leaking/ | Home inspector (franchise) | Short article (~600 words) | Basic causes | Thin; no table, no pressure numbers, no sources |
| 3 | parkerandsons.com/blog/why-is-my-water-heater-leaking | Local plumber (AZ) | Long blog (~2,400 words) | Causes by part, T&P, drain valve, anode, repair vs replace | No sources, no numbers for pressure or drain time, no diagram, ends in lead-gen |
| 4 | angi.com/articles/why-water-water-heater-pan.htm | Big publisher | Article | (blocked our fetcher, 403) | Judged from snippet: age and pressure as causes; Angi lead forms |
| 5 | appliancepartspros.com/b/?p=11159 | Parts retailer (2024-11-08) | How-to + FAQ | 4 troubleshooting steps; FAQ (bottom, drain valve, dangerous?, still use?) | Sells parts; no pressure range, no expansion-tank cost, no safety sourcing |

Also in the top 10: Angi "leaking from the top", HomeServe, San Jose Water, Bob Vila, Mr. Rooter, Bill Howe, Family Handyman: plumber and publisher blogs without primary sources.

**Content type Google ranks:** articles (troubleshooting guides). No tool ranks; an article with an embedded repair-or-replace tool fits.

**Organic difficulty verdict: medium.** Big publishers (Angi, Bob Vila, Family Handyman) sit next to local plumber blogs and a parts retailer; at least three small or weak sites hold top-10 spots, so the long-tail H2s (from bottom, from top, relief valve) are realistic.

## What they all miss (our additions)
1. **Leak map SVG + table** of the six leak points with cause, fix and a repair/replace verdict per point.
2. **Every claim tied to a manufacturer source** (A. O. Smith leak guide and residential gas manual, Rheem), opened and quoted in `data/troubleshooting.json`.
3. **Numbers others skip:** tank 30 to 80 gallons, drain time 20 to 30 minutes, mold in 24 to 48 hours, pressure 50 to 60 psi with an 80 psi code maximum, expansion tank cost range from our installation-cost data.
4. **Embedded repair-or-replace calculator** with the tank-leak rule.
5. **Gas vs electric leak spots** (burner area CO risk; element gasket) sourced to A. O. Smith.
6. **Sourced safety notice** (gas smell, breaker, scald) at the top.

## How we are different from generic calculator clones
Not a calculator query. The difference from the plumber blogs is sourcing and the decision tool; there is no lead form and no "find a plumber" box.

## Quality check (2026-11-13)
**Beat the SERP:** covers every useful point in the top 5 (what to do first, condensation vs leak, bottom/top/T&P/drain valve, repair vs replace, prevention, when to call a pro) plus the six additions above. Wording and structure are our own.

**Sources verified (opened 2026-11-13; quotes stored in `data/troubleshooting.json` → references):**

| Source | URL | Figure / claim on our page | Confirmed text |
|---|---|---|---|
| A. O. Smith leak page | hotwater.com/info-center/what-to-do-when-your-hot-water-heater-is-leaking.html | 30–80 gal; 20–30 min drain; mold 24–48 h; four leak points; condensation test; element gasket; burner CO risk | quoted in data |
| A. O. Smith leak guide | hotwater.com/info-center/water-heater-leaking.html | base leak = serious; anode 3–5 years; don't keep using | quoted |
| A. O. Smith gas manual 100400765 (PDF, 2025/2026) | assets.hotwater.com/damroot/Original/10017/100400765.pdf | T&P drip = pressure/expansion; PRV 50–60 psi; 80 psi code max; gauge on outside faucet over 24 h; never cap; drain pan + leak detector; condensation normal | quoted |
| A. O. Smith not-working page | hotwater.com/info-center/water-heater-not-working.html | 50–60 psi, not over 80 | quoted |
| A. O. Smith maintenance | hotwater.com/info-center/water-heater-maintenance.html | T&P yearly by owner, every 5 years by pro | quoted |
| Rheem (Mar 25, 2026) | rheem.com/water-heating/articles/water-heater-lifespan-when-to-repair-vs-replace/ | tank leak = replacement; 40–60 psi | already in data (Phase 3) |
| CPSC Pub. 5098 | cpsc.gov/s3fs-public/5098-Tap-Water-Scalds.pdf | 150 °F, two seconds, third-degree burns | quoted |
| Expansion tank cost | data/installation-costs.json (Phase 4, Home Depot + DOE anchors) | $100–$300, typical $175 | Phase 4 brief |

**Uncertain / honest notes:** HomeTips and Angi could not be opened (TLS error/503 and 403). The relief valve section restates the manual only; the full T&P page waits for Phase 6d.
