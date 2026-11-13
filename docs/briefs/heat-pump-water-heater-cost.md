# SERP brief: /heat-pump-water-heater-cost/

- **Primary keyword:** heat pump water heater cost
- **Secondary (H2s here, not separate pages):** heat pump water heater installation cost, heat pump water heater savings, payback, tax credit 2026, space requirements, heat pump vs gas
- **Checked:** 2026-11-13 (US search; pages opened the same day)

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | homes.rewiringamerica.org/projects/how-much-does-a-heat-pump-water-heater-cost | Nonprofit | Article | $3,600–$6,500 installed; savings $80–$230/yr; rebates | Still lists the 30% / $2,000 federal tax credit, which ended 2025-12-31 (IRS). No state numbers, no payback math |
| 2 | pickhvac.com/heat-pump-water-heater-costs-pricing-savings-rebates/ | HVAC affiliate site (Sep 2025) | Long article | Equipment/installation ranges, payback examples, space/electrical/condensate H3s | No sources for ranges; 25C credit shown as current; no calculator |
| 3 | pickhvac.com/heat-pump-water-heater-installation-cost/ | Same site (Jul 2025) | Article | $1,800–$5,000; breakdown table; permits $50–$300 | Unsourced ranges; title says 2026 but published 2025; no state data |
| 4 | homeserve.com/blog/cost-guide/heat-pump-water-heater-installation-costs | Home warranty company | Article | (403 to our fetcher; judged from snippet) | Snippet-only; no visible primary source |
| 5 | energystar.gov ask-the-experts (cost of installing) | EPA | Short Q&A | (JS challenge, could not be opened) | Used ENERGY STAR's installation guide and technical guide PDFs instead |

Also in the top 10: cleanenergyconnection.org (contractor directory, no dollar figures), checkatrade (UK), city utility PDFs.

**Content type Google ranks:** cost articles. A cost article with an embedded savings calculator fits; a bare calculator would not.

**Organic difficulty verdict: medium.** Affiliate and nonprofit articles, not big publishers, hold most spots, and none has state-level numbers or a working payback tool.

## What they all miss (our additions)
1. **Savings and payback calculator** for your state, current heater, household and rebate, using the same cost and running-cost model as our other tools.
2. **Correct 2026 incentive status:** the federal 25C credit ended 2025-12-31 (IRS); HEEHR rebate up to $1,750 where states run it (42 U.S.C. 18795a, DOE).
3. **State table** of savings and payback against electric and gas.
4. **Payback vs gas told honestly:** pays back within its life in only a handful of states.
5. **Space, electrical and noise rules quoted from ENERGY STAR**, with numbers rendered from data.

## How we are different from generic calculator clones
Clones show a single "savings" number with no price model. Ours prices both jobs, shows the formula with your numbers, and cross-checks against ENERGY STAR and DOE on /methodology.

## Quality check (2026-11-13)
**Beat the SERP:** covers installed cost by size, breakdown, savings, payback, incentives, space, noise, mistakes, when to call a pro, plus the five additions. Wording and structure are our own.

**Sources verified (opened 2026-11-13; quotes in `data/heat-pump.json` → references):**

| Source | URL | Figure / claim on our page | Confirmed |
|---|---|---|---|
| ENERGY STAR installation guide (2024) | energystar.gov/sites/default/files/2024-08/HPWH Installation Guide.pdf | 700 / 450 cu ft, 84 cu ft louvered closet, 38 °F, size up from gas, 30 A / 10 AWG | quoted |
| ENERGY STAR technical guide (Jun 2024) | energystar.gov/.../ENERGY STAR Heat Pump Water Heater Technical Guide 508C.pdf | 70% less energy, $550/yr household of 4, 55 / 45 dBA, 40 °F intake | quoted |
| ENERGY STAR product page | energystar.gov/products/heat_pump_water_heaters | 40–90 °F space, 1,000 cu ft | quoted |
| IRS 25C page | irs.gov/credits-deductions/energy-efficient-home-improvement-credit | credit for improvements through 2025-12-31 | quoted |
| 42 U.S.C. 18795a | law.cornell.edu/uscode/text/42/18795a | $1,750 HPWH cap; 80% / 150% income bands | quoted |
| DOE Home Energy Rebates | energy.gov/scep/home-energy-rebate-programs | available in select states; up to $14,000 | quoted |
| DOE 2024 storage rule | govinfo.gov FR-2024-05-06 | heat pump installed cost and space adders (Phase 4 anchors) | Phase 4 brief |
| EIA prices | data/energy-prices.json | state electricity and gas prices | Phase 3 |

**Cross-check:** ours $774/yr vs ENERGY STAR $550 for a household of 4 (ENERGY STAR's daily use and price unstated); 76% vs 70% less energy; DOE payback in /methodology table.

**Uncertain / honest notes:** HomeServe (403) and ENERGY STAR ask-the-experts (JS challenge) could not be opened. Rewiring America's lower savings ($80–$230) mix gas and electric homes and use a lower hot water use; ours are per-case and shown with the formula.
