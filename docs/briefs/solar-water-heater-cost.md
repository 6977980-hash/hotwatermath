# SERP brief: /solar-water-heater-cost/

- **Primary keyword:** solar water heater cost
- **Secondary (H2s here, not separate pages):** solar water heater installation cost, solar hot water heater cost, payback, tax credit 2026, solar vs heat pump water heater, is a solar water heater worth it
- **Checked:** 2026-11-14 (US search; pages opened the same day with a headless browser)
- **Why this page:** audit 2026-11-14 suggestion S-12 (owner approved the suggestion; page stays `draft` until the owner OKs it).

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | angi.com/articles/how-much-does-solar-water-heater-cost.htm | Lead-gen publisher (updated Oct 17, 2026) | Cost article | $1,780–$5,722 local range, $3,706 average, $357–$12,000 extremes; size, materials, parts, labor, location H3s; passive vs active; removal and permits $150–$950; savings section | Credit text is out of date (talks about 30%→26% stepping down; the IRS says the credit ended 2025-12-31). Ranges are from its own quotes, no state savings, no payback math |
| 2 | homeadvisor.com/cost/plumbing/install-a-solar-water-heater/ | Same company as Angi (updated Jun 19, 2026) | Cost article | Same $1,780–$5,722 / $3,706 data; cost factors (collector, tank, parts, labor, location, sun exposure); active vs passive; DIY vs pro | "You may be eligible for a 30% federal tax credit" listed as a pro. Wrong for 2026. No payback by place |
| 3 | fixr.com/costs/solar-water-heater-installation | Cost-guide site | Long article, 10 tables | $8,000–$10,000 national range, $9,000 for a 100-gal active indirect system; system types, collector and tank prices, brands, sizing, maintenance, vs gas / vs electric | Its range does not overlap Angi's at all; credit described as current ("to qualify for a federal tax credit…") |
| 4 | homeserve.com/en-us/blog/cost-guide/solar-water-heater/ | Home warranty company | Article | 403 to our fetcher; judged from the result title only | Could not be opened, so nothing from it is used |
| 5 | homeguide.com/costs/solar-water-heater-cost | Cost-guide / lead-gen | Long article, 11 tables | $3,000–$9,000 before credits, $1,500–$6,600 "after tax credits and rebates", $6,000 average; collector, tank and parts prices; brands; "2 to 6 year payback" | Says solar water heaters "purchased before 2033 are eligible for a 30% Renewable Energy Tax Credit". The IRS says no credit for property placed in service after 2025-12-31. The 2–6 year payback has no stated basis and does not match DOE-method results |

Also in the top 10: energy.gov/node/1265666 (DOE "Estimating the Cost and Energy Efficiency of a Solar Water Heater"), which now returns "Page not found" on energy.gov (checked 2026-11-14); elocal, howmuch.net, Solar Oregon.

**Content type Google ranks:** cost articles. No calculator in the top 5. An article with a state table is the right type; we do not build a separate solar tool.

**Organic difficulty verdict: medium-hard.** Angi/HomeAdvisor, Fixr and HomeGuide are big, established cost-guide domains. But every one of them is wrong on the 2026 tax credit, they contradict each other on price, and none shows savings or payback by state, so a sourced page has a real gap to fill.

## What they all miss (our additions)
1. **Correct 2026 credit status** with the IRS quote: the 25D credit is not available for property placed in service after 2025-12-31. Every top result still counts it.
2. **Savings and payback for all states with DOE sun data**, computed with the published equations of DOE FEMP's solar hot water calculator, against both an electric and a gas tank, at each state's EIA price and incoming water temperature.
3. **The cost disagreement shown openly:** NREL's installed range beside the three cost guides, instead of one unsourced number.
4. **Solar vs heat pump** for the same household, using our existing heat pump savings model. Most homes get more saving for less money from a heat pump; the page says so.
5. **DOE 2029 rule** context (89 FR 37778) for anyone replacing an electric tank.

## How we are different from generic calculator clones
Clones and cost guides give one national number. We show where each number comes from (NREL, DOE FEMP, IRS, EIA), reproduce FEMP's own worked example in a unit test, and give a state table that changes the answer by place.

## Data and method
- `data/solar-water-heating.json`: FEMP design defaults, equations (cell formulas quoted), sun data for one city per state (city in the state's largest metro), NREL cost range with quote, three cost guides with quotes, IRS credit quotes.
- `assets/js/formulas.js`: `solarWaterHeating()` and `solarYearlySaving()`. Test: `tests/formulas.test.mjs` "S-12".
- `scripts/build-data.mjs` → `data/computed/solar-water-heater-cost.json`.
- No embedded calculator: the heat pump savings calculator is linked instead, because the article's job is to compare solar against it.

## Quality check (2026-11-14, before owner OK)
**Beat the SERP:** covers installed cost, why the range is wide, system types, tank cost, tax credit, savings, payback, solar vs heat pump, winter and cloudy days, freeze protection, certification, upkeep, mistakes and when to call a pro, plus the five additions above. Wording and structure are our own.

**Sources verified (opened 2026-11-14; quotes stored in `data/solar-water-heating.json`):**

| Source | URL | Figure / claim on our page | Confirmed |
|---|---|---|---|
| DOE FEMP Solar Hot Water System Calculator page | energy.gov/cmei/femp/articles/solar-hot-water-system-calculator | Method; "optimal system size is often larger (providing 60–90% of the load)" | page loads, text quoted |
| FEMP spreadsheet (June 2023) | energy.gov/sites/default/files/2023-06/femp-solar-hot-water-system-calculator-june-2023.xlsx | η 0.3, utilization 0.9, 8.33 BTU/gal·°F, pump formula, 0.5% O&M, city sun data | downloaded; cell formulas read; example reproduced to the decimal |
| NREL/TP-5500-54793 (2012) | osti.gov/servlets/purl/1219746 | "$6,000-$10,000 installed before rebates and incentives" | PDF opened, quoted |
| IRS Residential Clean Energy Credit | irs.gov/credits-deductions/residential-clean-energy-credit | 30%; "not available for any property placed in service after December 31, 2025"; SRCC certification | page opened, quoted |
| HomeAdvisor / Angi, HomeGuide, Fixr | see table above | cost-guide ranges shown for comparison only | opened, quoted |
| Home Depot Richmond S80U-1 | homedepot.com/p/…/202959691 | $1,189 solar storage tank | listing opened |
| EIA prices, state inlet temps, cost model | existing data files | savings inputs | already verified in earlier phases |

**Uncertain, said on the page:** NREL's range is from 2012 and not inflation-adjusted (no newer public figure found); FEMP's method is built for federal buildings; one city per state; no FEMP city for Arkansas, Iowa or DC.

**Live check:** to do after deploy (draft page, noindex).
