# SERP brief: /water-heater-cost-calculator/

- **Primary keyword:** how much is a new water heater (Keyword Planner 5K)
- **Secondary:** water heater cost calculator (500), 40 gallon water heater replacement cost (5K), 50 gallon water heater cost (5K), water heater cost by size/type, water heater labor cost, permit for water heater
- **Checked:** 2026-11-13 (US search)

## Top 5 organic results for "how much is a new water heater"

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | homeadvisor.com/cost/plumbing/install-a-water-heater | Lead-gen publisher (Angi family) | Cost article | $882–$1,807 "normal range", $350–$12,000 extremes, cost by type, "get quotes" | 403 to our fetcher; judged from snippet. Ranges from its own lead data, no method, no state numbers, lead forms |
| 2 | thumbtack.com (Q&A pages) | Lead-gen marketplace | Q&A | One national average | One number, no itemizing, lead form |
| 3 | ambientedge.com/faqs/how-much-does-a-new-water-heater-cost | Local plumber (Las Vegas) | Article | H2s: type affects cost (gas, electric, oil, solar, hybrid, propane), size, venting, labor, removal, gas lines, electrical, carpentry; $800–$1,550, avg $1,175 | No sources, no state factor, no tool, sales pitch |
| 4 | bankrate.com …/how-much-is-a-water-heater | Finance publisher | Article | (page body JS-rendered; judged from snippet) price by type | Generic ranges, no install breakdown by job |
| 5 | networx.com/article/water-heater-costs | Lead-gen | Article | (41 words served to fetcher; snippet only) | Lead-gen |

Also strong for the cost head terms: Bob Vila (citing Angi: $874–$1,765, avg $1,293, labor $150–$450, permit $25–$300, removal $100–$500, gas line $275–$825), This Old House (citing Home Depot data: avg $1,950, $1,600–$5,400, tank labor $300–$800, tankless labor $800–$1,500), Forbes/HomeGuide (403). Several content-farm pages (`*.risingsunartscentre.org`, `srand.publisher-collective.com`) also rank: weak, mass-generated.

**Content type Google ranks:** cost articles with ranges. The keyword map gives this intent to a tool; the page therefore leads with a Quick Answer and a full cost article under the calculator (by type and size, labor, extras, states, fair-quote checklist).

**Organic difficulty verdict: hard for the head term** (HomeAdvisor/Angi, Bob Vila, This Old House), **medium for the size long tails** (40/50 gallon cost), where weak and mass-generated pages rank.

## How our page is visibly different from the clones and articles
- Itemized estimate (unit, labor, each extra) for the user's own type, size, state and job, with the formula shown.
- Equipment prices come from named current listings (count and date shown); installation ranges are written down with the DOE figure each one leans on; state factor is the BEA price parity.
- Published cross-check against DOE's average installed costs on /methodology, with differences explained.
- No "get quotes" button, no lead form, no named installer.

## At least two things none of the top 5 have
1. A calculator that itemizes the job and changes with state and scenario (like-for-like, fuel switch, tank to tankless).
2. A state-by-state table for a 50-gallon gas tank (all 50 states + DC).
3. Unit-only vs installed price side by side for every size we track.
4. A "common jobs" table showing what a fuel switch or tank-to-tankless adds.
5. Sources: DOE's 2024 rules for each installation anchor.

## Quality check (CLAUDE.md §8), 2026-11-13

**1. Beat the SERP.** Covered: price by type (all), size (Ambient Edge, Bob Vila), labor (all), removal/haul-away (Ambient Edge, Bob Vila, TOH), permits (Bob Vila, TOH), gas line and electrical (Ambient Edge, Bob Vila), venting (Ambient Edge, TOH), expansion tank and code items (Bob Vila, TOH), fuel switch cost (TOH, NerdWallet), regional differences (TOH, in words only). Added: calculator, itemized table, state table, scenario table, DOE anchors and cross-check, fair-quote checklist. HomeAdvisor, Bankrate and Networx were judged from snippets (blocked or JS-rendered).

**2. Sources verified** (opened 2026-11-13):
- DOE final rule, consumer water heaters, 89 FR 37778 (govinfo HTML and PDF): installation-cost definition; Table V.2 gas storage installed $1,432 (2022$); Table V.8 electric $902, heat pump level $1,855; panel upgrade "$750--$2,000"; new 240-volt circuit "at least several hundred dollars"; heat pump adders "$0 to $4,000 ... averaging $2,000". https://www.govinfo.gov/content/pkg/FR-2024-05-06/html/2024-09209.htm
- DOE final rule, gas-fired instantaneous, 89 FR 105188 (govinfo PDF): installation cost $1,102 non-condensing / $1,025 condensing; permitting, removal and disposal $260 (TSD table 8D.5.66); relocation up to $2,000; Rinnai quote of DOE venting estimates $499 / $263; Table V.2 baseline installed $2,087 (2023$). https://www.govinfo.gov/content/pkg/FR-2024-12-26/pdf/2024-30369.pdf
- Home Depot listings: expansion tanks $34.42 (HDET2G), $59.71 (FTET21-N), $59.98 (G12W), $67.02 (ST-5); drain pan $11.98 (HP00224BL). Equipment price samples as in data/cost-ranges.json (rechecked 2026-11-12).
- This Old House cost guide (sanity check only), Bob Vila replacement cost (sanity check only): figures listed above.
- BEA Regional Price Parities via FRED (data/regional-cost-index.json, 2026-11-12).

**3. Live check:** see docs/HANDOFF.md (Phase 4).

**Uncertain:** labor ranges are our estimate (DOE publishes averages, not ranges); DOE figures are 2022/2023 dollars.
