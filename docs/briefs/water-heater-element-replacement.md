# SERP brief: /water-heater-element-replacement/ (Phase 6d, safety-critical, CLAUDE.md §8b)

- **Primary keyword:** water heater element replacement (Keyword Planner 5K)
- **Secondary:** how to test a water heater element, element resistance, bad element symptoms, upper vs lower, cost, 4500 watt element
- **Checked:** 2026-11-13

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | hometips.com/repair-fix/replace-water-heater-element.html | Publisher | How-to | Steps, $10–$60 part | TLS fails from our sandbox; snippet only, not used as evidence |
| 2 | searspartsdirect.com/…/how-to-replace-an-electric-water-heater-heating-element | Parts retailer | How-to | Steps (403 to our fetcher) | Could not be read |
| 3 | hunker.com/13714709/how-to-replace-a-heating-element… | Publisher | How-to | Test steps, replace steps, drain half for upper element | **Says a good element reads zero ohms** (wrong: zero is a short); no dry-fire warning, no cost, no data plate rule, no when-to-call |
| 4 | angi.com/articles/how-to-replace-water-heater-element.htm | Publisher | How-to | 9 steps, cost $150–$200 (snippet) | 403 to our fetcher; cost unsourced |
| 5 | plumbersstock.com/…/replacing-a-water-heater-element.html | Retailer blog | How-to | Steps | 403 to our fetcher |

Also in the top 10: h2ouse, appliancepartspros, aosmithatlowes help page (opens as a menu only), sensibledigs, plumbingsupply.

**Content type Google ranks:** how-to articles. **Organic difficulty verdict: medium:** publishers and parts retailers; only the manufacturer manual is a primary source, and none of the readable results cites it.

## Which official source backs each claim the SERP makes
- Steps (power off, drain, element wrench, gasket, refill, power on): A. O. Smith electric Use & Care Guide (real; we restate it, attributed, and nothing beyond it, CLAUDE.md §8b).
- 10–30 ohm "typical" reading: A. O. Smith gives 5–25 ohms; Ohm's law gives 12.8 ohms for 4,500 W at 240 V.
- Zero ohms = good (Hunker): wrong; contradicted by Ohm's law and A. O. Smith's range.
- $150–$200 job cost (Angi snippet): no source we can open; not repeated.

## What they all miss (our additions)
1. **Resistance table** for 6 common ratings (computed: volts² ÷ watts, plus current), next to A. O. Smith's 5–25 ohm pass range.
2. **Symptom table**: upper vs lower element vs shorted element vs gasket, each from A. O. Smith.
3. **Dry-fire rule** quoted from the manual (full stream for 3 minutes before power), and the dry-fire code.
4. **Correction** of the "zero ohms is good" myth.
5. **Real parts prices** (The Home Depot, $8.99–$59.99; tune-up kit $34.98) and an honest "no sourced labor figure".
6. Embedded repair-or-replace calculator (Rheem 50% rule).

## How we are different from generic calculator clones
Values come from Ohm's law in formulas.js and the manufacturer manual; the calculator decides whether the repair is worth it.

## Quality check (2026-11-13)
**Beat the SERP:** symptoms, which element, test values, buying the right one, cost, the manual's steps, dry fire, mistakes, when to call a pro, plus the additions. Steps are A. O. Smith's homeowner procedure, attributed, with its shock warning and "qualified person" line; the meter test is given as values only, no walkthrough.

**Sources verified (opened 2026-11-13):**

| Source | URL | Claim / figure | Confirmed |
|---|---|---|---|
| A. O. Smith electric manual 2000598248 | aosmithatlowes.com/media/1710/smart-electric-2000598248.pdf | 5–25 ohms; lower element wears first; burned-out upper element → no hot water; one element at a time; shorted element → T&P gush; shock warning; non-contact tester; data plate wattage/voltage; 13 replacement steps; 3 minutes full stream; 2 hours to heat; dry-fire code; sockets slip; thermostat needs no draining; both elements good → thermostat | quoted |
| A. O. Smith gas manual 100400765 | assets.hotwater.com/damroot/Original/10017/100400765.pdf | flooded controls → replace the heater | quoted |
| The Home Depot search | homedepot.com/s/water%20heater%20heating%20element%204500%20watt | $8.99 Rheem copper … $59.99 Westinghouse titanium; kit $34.98 | listing prices |
| Rheem repair vs replace | rheem.com/…/water-heater-lifespan-when-to-repair-vs-replace/ | 50% rule (in data/decision-rules.json) | in data |

**Uncertain / honest notes:** the manual is A. O. Smith's electronic-thermostat model sold at Lowe's; standard two-element tanks follow the same element procedure in A. O. Smith's guidance, but the reader is told their own manual wins.
