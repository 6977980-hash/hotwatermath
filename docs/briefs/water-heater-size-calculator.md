# SERP brief: /water-heater-size-calculator/

- **Primary keyword:** what size water heater do i need (Keyword Planner 5K)
- **Secondary:** water heater size calculator (500), water heater sizing calculator, water heater size chart (500), how many gallons water heater do i need (500), what size water heater for family of 4 / 5 (500 each), family of 2 / 6, water heater size for 3 or 4 bedroom house, first hour rating calculator
- **Checked:** 2026-11-13 (US search; pages opened the same day where the site allowed it)

## Top 5 organic results for "what size water heater do i need"

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | bobvila.com/articles/what-size-water-heater-do-i-need | Big publisher (sponsored by SupplyHouse, 2019/2020) | Article | Household-size ranges (1–2: 23–36 gal … 5+: over 56), per-use peak-hour gallons, FHR explained, tankless flow section | Overlapping household ranges; says FHR "reflects efficiency"; household table and FHR method never connected; no gas vs electric difference; no calculator; dated |
| 2 | angi.com/articles/what-size-water-heater-do-i-need.htm | Big publisher | Article | (403 to automated access; judged from the search snippet) household-size rule of thumb | Lead-gen for pros; no tool |
| 3 | apps.directenergy.com/learning-center/what-size-water-heater-home | Energy retailer | Article | (host does not resolve from our container; judged from the snippet) | Not opened |
| 4 | hotwater.com/info-center/what-size-water-heater-do-I-need.html | A. O. Smith (manufacturer) | Article + link to its selector | Family size → tank volume (up to 2: 30–40, 3: 40–50, 4: 50–75, 5: 75–100); peak-hour method in words; tankless flow; 120 °F FAQ | **No worked example with numbers**; refers to a calculator that is not on the page; volume table mixes fuels |
| 5 | abt.com/learn/water-heater-buying-guide | Retailer | Buying guide | 10–15 gallons per person rule | Rule of thumb only; no FHR, no sources |

Also in the top 15: Rheem "What size gas water heater do I need" (manufacturer: household table 1–2: 30–40 … 5+: 75 or tankless, worked example 55 gal), cooltoday, epb.com, elocal, networx, local plumber blogs. For "water heater size calculator / first hour rating calculator" the SERP is mostly **clone calculators** (plumb-calcs.pages.dev, simplifycalc.pages.dev, calculatediy.pages.dev, agentcalc, helpertools.pages.dev, diysmarthomehub, diycalc.vercel.app, hakaru.io), plus PNNL's BASC FHR tool, thefurnaceoutlet and the archived DOE worksheet. The search summary of the clones shows invented formulas such as "people × 12 gal × usage factor × 1.5" and "FHR = tank × 0.7 + 20", with no sources.

**Content type Google ranks:** answer-first articles for "what size … do I need", tools for "… calculator". The keyword map gives both to one tool page, so the page answers in the Quick Answer and every H2, then lets the tool give the exact number.

**Organic difficulty verdict: medium.** Bob Vila, Angi and A. O. Smith are strong, but local blogs and thin clones fill the rest of the top 15.

## Questions the top pages answer
What size for 1–2, 3, 4, 5+ people? What is first hour rating? How do I work out peak-hour demand? Gas or electric? What if the tank is too small or too big? Tank or tankless?

## How our page is different from the generic calculator clones (CLAUDE.md §1)
- Per-use gallons from the **AHRI peak-hour worksheet**, named next to the result; recommended size from the **first hour ratings of real models** (Rheem spec sheets, ENERGY STAR certified list), not a made-up FHR formula.
- The result shows all three fuels and places the user's busy hour against **American Water Heater's published FHR range** for their household size.
- Published cross-check on /methodology against American, Rheem and A. O. Smith household guides and Rheem's worked example, with differences explained.

## At least two things none of the top 5 have
1. One calculation that connects household size, peak-hour demand, first hour rating and tank size for gas, electric and heat pump.
2. A household table that puts our result next to two manufacturers' tables.
3. FHR spread by size from real models (why gallons alone mislead), and the federal rule that makes big electric tanks heat pumps.
4. A worked example with every number.

## Quality check (CLAUDE.md §8), 2026-11-13

**1. Beat the SERP.** Covered from the top results: household-size table (Bob Vila, A. O. Smith, Abt, Rheem), peak-hour method and per-use gallons (Bob Vila, A. O. Smith, Rheem), FHR definition (all), gas vs electric (Rheem, Networx snippet), under/oversizing (A. O. Smith, cooltoday), tub rule (American). Added, none of them have it: the interactive calculation for three fuels; FHR ranges from real models; manufacturer tables side by side with our result; 40 vs 50 numbers; worked example; published cross-check. Angi and Direct Energy could not be opened (403 / host not reachable) and were judged from search snippets.

**2. Sources verified** (opened 2026-11-13):

| Source | Result | Figure or claim confirmed |
|---|---|---|
| American Water Heater residential catalog (NRBBR00107.pdf) | 200, text extracted | FHR requirements: 2 people 38–49, 3: 49–62, 4: 62–74, 5: 74–95, 6: 95–124, 7+: 124+; high demand: more than two full baths, teenagers, oversize tub; tank = 100% of tub capacity |
| Rheem, What size gas water heater do I need | 200 | 1–2: 30–40, 2–4: 40–50, 4–5: 50–55, 5+: 75 or tankless; example 30 + 20 + 5 = 55 gal → FHR ≥ 55 |
| A. O. Smith (hotwater.com) What size water heater | 200 | up to 2: 30–40, 3: 40–50, 4: 50–75, 5: 75–100; FHR "equals or slightly exceeds" peak-hour demand |
| AHRI peak-hour worksheet, PNNL BASC FHR tool, Rheem spec sheets, ENERGY STAR dataset, 10 CFR 430.32, Appendix E | Phase 1 data, re-requested by `validate-data --urls` | per-use gallons, FHR ≥ PHD rule, FHR by model, electric > 55 gal heat pump rule |

**Uncertain / ours:** household busy-hour presets and the optional 10% margin are our assumptions, labelled on the page and on /methodology. The FHR sample for gas and electric is mostly one manufacturer.

**3. Live page check:** see HANDOFF.md (Phase 3 "Verified").
