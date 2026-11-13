# OUTREACH.md — Owner playbook for links and mentions (CLAUDE.md §7b, PHASES.md Phase 8)

> The owner runs outreach; Claude drafts and supplies numbers on request. Rules that never bend:
> no paid links, no PBNs, no link exchanges, never trade the embed for a link, never ask for a followed link.
> We pitch **data and tools**, not the brand. Every number we send is one the site renders from `data/` with a source.

## 1. What we offer (the assets)

| Asset | URL | Why someone would cite it |
|---|---|---|
| Seven calculators with published formulas | `/calculators/` | A homeowner question answered with the reader's own numbers |
| Methodology + manufacturer cross-check tables | `/methodology/` | Shows our numbers agree (or why they differ) with Rheem, A. O. Smith, Rinnai, Noritz, DOE and EnergyGuide labels |
| Serial-number decoders by brand | `/water-heater-age-calculator/` and the brand pages | Inspectors and agents need build dates all the time |
| State data (groundwater temperature, energy prices, cost index) | inside the tools; `/data/` downloads come in Phase 6+ | Journalists and utilities like state-level numbers |
| Embed widget | "Embed this calculator" on each tool | Free extra for a resource page. Credit link is the brand name, `rel="nofollow"`, and may be removed |

## 2. Pitch templates (edit the bracketed parts; keep them short)

**A. Resource-page pitch (inspectors, energy nonprofits, extension services)**

> Subject: A free water heater age decoder for your [resources] page
>
> Hi [name], I run Hot Water Math, a small independent site of water heater calculators. I saw your [page title] page lists [similar resource]. Our [age calculator / sizing tool] reads the build date from the serial number for [N] brands, and every rule is checked against the manufacturer's own lookup or CPSC recall records ([methodology link]). If it is useful for your readers, feel free to link it. You can also embed the calculator; the credit line is optional. Thanks either way, [name]

**B. Data pitch (journalists, newsletters)**

> Subject: [State] numbers on what a water heater costs to run
>
> Hi [name], for your piece on [topic]: with [state]'s current EIA energy prices, a typical [household] home pays about $[X]/year to run an electric tank and $[Y] for a heat pump model ([tool link], formula and sources on [methodology link]). Happy to run the numbers for other states.

**C. Correction-style pitch (when a page cites an outdated number)**

> Hi [name], your [page] says [claim]. The current [DOE / EIA / manufacturer] figure is [value] ([primary source link]). We keep a sourced table of these at [our page] if it helps. No need to link us; I just wanted you to have the current number.

Embed offer line (use as is): *"You are welcome to embed the calculator. It carries a small 'Calculator by Hot Water Math' credit with a nofollow link, and you can remove it."*

## 3. Expert-quote routine (HARO / Qwoted / Featured) — 20 minutes, 3 days a week

1. Sign up: Help a Reporter (helpareporter.com), Qwoted (qwoted.com), Featured (featured.com). Profile: Ali Ahmad, Founder of Hot Water Math, calculator developer. **Never** claim to be a plumber.
2. Answer only queries about water heater cost, sizing, lifespan, energy cost, tank vs tankless, heat pump water heaters.
3. Every answer: one concrete number from the site (ask Claude for the current value and its source), one sentence of method, one link to the matching tool. 120–200 words.
4. Log each pitch in a sheet: date, outlet, query, number given, link earned (Y/N).

## 4. Resource-page outreach list (20 targets)

Find the page with a search such as `site:<domain> water heater` or `site:<domain> resources`. Pitch only where a real resources/guide page exists. Status column is for the owner.

| # | Site | Type | Angle | Status |
|---|---|---|---|---|
| 1 | nachi.org (InterNACHI) | Home inspectors | Age decoder; inspectors date units daily | |
| 2 | homeinspector.org (ASHI) | Home inspectors | Age decoder, repair-or-replace | |
| 3 | structuretech.com/blog | Inspector blog | Age decoder, cross-check method | |
| 4 | houselogic.com (NAR) | Homeowner guides | Cost calculator, lifespan | |
| 5 | familyhandyman.com | DIY publisher | Tank sizing, cost data | |
| 6 | thisoldhouse.com | DIY publisher | Tankless sizing, state groundwater data | |
| 7 | bobvila.com | DIY publisher | Cost by state, data pitch | |
| 8 | popularmechanics.com | DIY publisher | Tankless vs tank cost of ownership | |
| 9 | rewiringamerica.org | Electrification nonprofit | Gas vs electric / heat pump running cost by state | |
| 10 | aceee.org | Efficiency research | Heat pump running cost by state | |
| 11 | rmi.org | Energy research | Heat pump vs gas operating cost | |
| 12 | efficiencyvermont.com | Efficiency utility | Heat pump water heater calculator | |
| 13 | energytrust.org | Efficiency nonprofit | Age decoder (they publish one; offer the cross-check) | |
| 14 | extension.org + state university extension housing pages | Extension services | Sizing and running cost tools | |
| 15 | pmmag.com (Plumbing & Mechanical) | Trade media | Sizing formulas vs manufacturer tables | |
| 16 | achrnews.com | Trade media | Heat pump water heater cost data | |
| 17 | hvacrschool.com | Trade education | Temperature-rise and FHR formulas | |
| 18 | contractormag.com | Trade media | Data pitch on installed costs | |
| 19 | phcppros.com | Trade media | Data pitch on state energy costs | |
| 20 | greenbuildingadvisor.com | Building science | Heat pump vs gas running cost, methodology | |

Do not pitch competitors' calculator sites, contractor lead-gen sites, or any site that asks for payment.

## 5. Reddit guidelines (r/Plumbing, r/HomeImprovement, r/DIY)

- Answer the question fully in the comment itself. Link a tool only when it does the user's exact calculation, at most one link per comment, and say it is your site ("I built this").
- Never post a link-only comment, never post the site as a submission, never reply to your own threads from another account.
- At most 2–3 helpful comments with a link per week; many more without links. Read each subreddit's rules on self-promotion first.

## 6. Pinterest board plan

- Boards: "Water heater sizing", "Water heater cost", "How old is my water heater", "Heat pump water heaters".
- Pins: our own SVG charts and tables exported as 1000×1500 PNG (Claude can generate them from `data/` with an npm script on request), each linking to the matching tool. No stock photos, no brand logos.
- 3–5 pins a week; title = the question, description = the Quick Answer.

## 7. Entity profiles checklist (same name, logo, description everywhere)

Name: **Hot Water Math** · Logo: `assets/brand/logo-512.png` · Description: the `description` in `data/site.json` · Contact: contact@hotwatermath.com · URL: https://hotwatermath.com/

- [ ] LinkedIn company page (and add hotwatermath.com to the founder's personal profile so the link goes both ways)
- [ ] X (Twitter) profile
- [ ] Pinterest business profile (claim the website)
- [ ] When each profile exists, send Claude the URLs: they go into `data/site.json` → `social`, which feeds `Organization.sameAs`.
