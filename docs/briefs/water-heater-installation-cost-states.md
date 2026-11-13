# SERP brief: state cost pages, /water-heater-installation-cost/{state}/

One brief for the ten pages, because they share one template and one intent pattern (DECISIONS 2026-11-13, Phase 6c). Each state has its own SERP section below.

- **Primary keywords:** water heater installation cost {state}; secondary: water heater replacement cost {state}, cost to replace water heater in {state}, tankless water heater installation cost {state}
- **States:** the 10 most populous by Census Vintage 2025 (POPESTIMATE2025): California, Texas, Florida, New York, Pennsylvania, Illinois, Ohio, Georgia, North Carolina, Michigan
- **Checked:** 2026-11-13 (US search, top results opened or read from the results page)

## What ranks, state by state

| State | Who is in the top results | What they have | What they get wrong or miss |
|---|---|---|---|
| Texas | HomeServe state guide (two URLs), contractorplus.app city pages (Houston, Dallas, Austin, San Antonio), HomeAdvisor Austin, a Houston plumber | One statewide average ($1,613 replace, HomeServe), city averages, the Texas licensed-plumber rule (plumber blog) | No breakdown by type or fuel, no energy cost, no sources; contractorplus pages are auto-generated material calculators |
| California | Payless Water Heaters (contractor), HomeAdvisor city pages (San Jose, LA, Fullerton), HomeServe, contractorplus, swivl, a mass-generated "2026" page | Wide ranges ($1,200–$7,500), attic and code fixes as cost drivers | No strap law source, no metro comparison, no running cost; one contractorplus page shows $0.00 totals |
| Florida | CoolToday (2016 and 2018 posts), HomeAdvisor city tloc pages, HomeServe (two versions that disagree), costof-homes.pages.dev clone | Old ranges, city averages | Out of date; HomeServe's two versions conflict; nothing on Florida being 88% electric or on rebate status |
| New York | contractorplus (many NYC pages), HomeServe, HomeAdvisor (NYC, Staten Island, Yorktown Heights), HomeBlue, costof-homes clone, Urban Green Council | NYC averages, heat pump adoption context | No fuel oil mention in cost guides, no upstate vs NYC split with a source, rebate info dated 2025 |
| Pennsylvania | HomeServe (state + national), contractorplus, Philadelphia contractor sites on *.pages.dev, HomeBlue city pages | $1,411 state average, Philly contractor ranges, permit note | No Pittsburgh vs Philadelphia comparison with a source, no rebate status (Penn Energy Savers not launched), no fuel oil |
| Illinois | HomeServe, Four Seasons (contractor), contractorplus (Chicago, Naperville, Rockford), HomeAdvisor (Chicago, Schaumburg, Naperville) | $1,452 state average, Chicago permit $100–$200 (one page) | No running cost (Illinois gas is cheap), no rebate status (HEAR not launched) |
| Ohio | Angi national guide, contractorplus city pages (several with $0 placeholders), HomeAdvisor Columbus, HomeServe | National average, Columbus average | No Ohio-specific numbers beyond one average, broken placeholder pages |
| Georgia | contractorplus (Savannah, Atlanta, Augusta), HomeAdvisor Alpharetta, Estes (contractor), swivl pages | $2,500–$7,500 range (contractor), city ranges | Ranges conflict wildly; no rebate status (HEAR reopened but limited) |
| North Carolina | HomeServe, HomeAdvisor city pages (High Point, Fayetteville, Raleigh, Durham, Wilmington), contractorplus, a netlify plumber blog | City averages from $807 to $1,685 | Nothing on the open Energy Saver NC heat pump water heater rebate |
| Michigan | HomeServe, HomeAdvisor (Troy, Utica, Midland), costof-homes clone pages, contractorplus, swivl | City averages, permit ranges | No cold-water / tankless note, no cheap-gas running cost, no MiHER status |

**Content type Google ranks:** cost-guide articles and auto-generated city cost pages, not calculators. These pages are articles with the calculator embedded, so the content type matches.

**Organic difficulty verdict: easy to medium.** HomeServe holds one guide per state and HomeAdvisor holds city pages, but most of the rest is contractor marketing and mass-generated pages (contractorplus, swivl, costof-homes.pages.dev). No government or manufacturer page ranks.

## What every competitor misses (our additions, on every state page)
1. **Itemized lines for the state** (unit, labor, permit, extras) from one published model, gas and electric, not a single average.
2. **The five largest metro areas priced separately** with BEA's metro price parities, so "Houston vs Dallas" or "Pittsburgh vs Philadelphia" is answered with a source.
3. **Running cost in the state** with EIA state prices, and 10-year cost of each tank type; heat pump payback from the same calculator as our heat pump page.
4. **How the state's homes heat water** (EIA RECS 2020 fuel shares), which decides whether a like-for-like job is gas or electric.
5. **Winter tankless output** and a **month-by-month table** of incoming water temperature, tankless flow and energy bill.
6. **Rebate status quoted from the state's own official page with the check date**, or a plain statement that we could not confirm it.
7. State-specific sourced notes where they exist (California strap law, Seismic Safety Commission).

## How we are different from generic calculator clones
The clones (contractorplus, swivl, costof-homes) print a single number per city with no source, sometimes $0.00. Our pages show the named source next to every table (BEA, EIA, NOAA, Census, state energy office), link the formulas on /methodology, and embed the cost calculator preset to the state.

## Quality check (CLAUDE.md §8), 2026-11-13

### 1. Beat the SERP
Covered from the top results: statewide average (we give gas and electric, typical and range), city prices (five metros), tank vs tankless (tankless rows and the switch cost), permits (included and named), cost drivers (extras), rebates (official status). Added beyond all of them: items 1 to 7 above. No wording or structure copied.

### 2. Sources verified (opened 2026-11-13)
| Source | URL | What we use | Checked |
|---|---|---|---|
| Census Vintage 2025 state totals | www2.census.gov/…/NST-EST2025-ALLDATA.csv | Top 10 states (CA 39,355,309 … MI 10,127,884; NJ 11th) | 200, figures read from the file |
| Census Vintage 2025 CBSA totals | www2.census.gov/…/cbsa-est2025-alldata.csv | Largest metros per state | 200 |
| FRED RPPALL<CBSA> (BEA) | fred.stlouisfed.org/series/RPPALL26420 etc. (47 series) | Metro price parities 2024 (e.g. Houston 98.6, Los Angeles 113.6, Chicago 103.6) | all 200; Cleveland, Dayton and Kiryas Joel–Poughkeepsie have no series (404) and are named on the page |
| EIA RECS 2020 state water heating | eia.gov/…/State%20Water%20Heating.xlsx | Fuel shares (e.g. FL 88% electric, CA 78% gas, NY 11% fuel oil) | 200, read from the sheet |
| EnergyPlus Engineering Reference | bigladdersoftware.com/…/water-systems.html | Monthly mains correlation incl. lag = 35 − 1.0 × (Tavg − 44) | 200, formula quoted |
| California Energy Commission | energy.ca.gov/…/inflation-reduction-act-residential-energy-rebate-programs | "As of August 31, 2026, HEEHRA rebates for Phase I are fully reserved." Phase I = heat pump HVAC | 200 |
| Texas SECO | comptroller.texas.gov/programs/seco/funding/ira/ | "Current Status (Updated August 21, 2026) Rebates are not currently available." | 200 |
| Florida Energy Saver Program (FDACS) | floridaenergysaverprogram.fdacs.gov/ | "…programs are expected to launch in the future." | 200 |
| Pennsylvania DEP | pa.gov/agencies/dep/residents/saving-energy/inflation-reduction-act | Redesign after DOE's June 1, 2026 guidance | 200 |
| Illinois EPA | epa.illinois.gov/topics/energy/energy-rebates.html | HEAR "application has not yet been processed by USDOE" | 200 |
| Georgia (GEFA) | energyrebates.georgia.gov/home-electric-appliance-rebates | "HEAR program for single-family has reopened with limited measures" (wiring and panel with a HER project) | 200 |
| NC DEQ, Energy Saver North Carolina | deq.nc.gov/…/energy-saver-north-carolina | "Up to $1,750 for an ENERGY STAR-certified electric heat pump water heater"; 80–150% AMI | 200 |
| Michigan EGLE bulletin | content.govdelivery.com/accounts/MIDEQ/bulletins/3dd47d0 | Statewide MiHER launch, April 23, 2025 | 200 (michigan.gov itself 403) |
| DOE Home Energy Rebates | energy.gov/cmei/scep/home-energy-rebates-program | "Home Energy Rebates are now available in select states." | 200 |
| California Seismic Safety Commission FAQ | ssc.ca.gov/about/faqs/ | Strapping law quote | 200 |
| ENERGY STAR Rebate Finder | energystar.gov/rebate-finder | Utility rebates pointer | 200 |
| Existing sources (BEA state RPP, EIA prices, NOAA, DOE 2024 rule, Home Depot) | data files | Unchanged from Phases 1 and 4 | `validate-data --urls` 200 |

Not confirmed (said so on the page): New York (NYSERDA pages 403 to our checker), Ohio (no Department of Development status page found), Michigan's remaining funds (michigan.gov 403).

### 3. Uniqueness (PHASES 6c acceptance)
Offline render of all 10 pages, state name masked: sentence/line diff between any two pages is 36% to 46% (lowest pair Illinois vs Ohio). A stricter word-level diff is 13% to 43% (Texas vs Florida lowest: both warm, mostly electric states). Each page has 2,600–2,850 words.

### 4. Live check
See docs/HANDOFF.md (after deploy).
