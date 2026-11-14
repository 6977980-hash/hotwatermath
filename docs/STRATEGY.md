# STRATEGY.md — Niche research, keyword map, and page specs

> Subordinate to `CLAUDE.md`. This file answers: *what pages exist, which keyword each owns, what each calculator computes, and why we expect to rank.*

## 1. Why this niche (research summary, Nov 2026)

- Google Keyword Planner (US, Nov 2025–Oct 2026, owner's export of 2026-11-13 saved at `/mnt/project-files/keyword-data/keyword-planner-seed-2026-11-13.csv`): **~301,000 searches/month** across the 93 seed keywords. The numbers below were corrected against that export. Volumes are bucketed (50 / 500 / 5K / 50K): each bucket is a wide range ("5K" means roughly 1K–10K), so the total is an upper-end estimate, not a forecast. After launch, real demand is read from Search Console impressions and the map is corrected in Phase 7.
- **50K tier:** water heater replacement cost, tankless water heater installation cost, tankless water heater cost, *how long does a water heater last* (Low competition). ("water heater installation cost" is 5K in the 2026-11-13 export, down 90% YoY, so "replacement cost" is the head term.)
- **5K tier (13):** what size water heater do i need, what size tankless water heater do i need, tankless water heater sizing, gas vs electric water heater, tankless vs traditional water heater, tankless water heater pros and cons, heat pump water heater cost, how much is a new water heater, 40 gallon water heater replacement cost, 50 gallon water heater cost, gas/electric water heater installation cost, water heater installation cost, water heater lifespan, water heater life expectancy, how long does a tankless water heater last, **tankless water heater size calculator** (5K average, but down 90% in the last three months: treat as 500–5K).
- **Calculator terms (500 each):** water heater size calculator, electric tankless water heater size calculator, tankless water heater flow rate calculator, water heater cost calculator, water heater age calculator (down 90% in the last three months), hot water cost calculator.
- **Rising (+900%):** 50 and 80 gallon heat pump water heater, heat pump water heater vs electric / vs gas, repair or replace water heater, tankless water heater size for family of 4, 50 gallon water heater cost. Heat pump demand is growing, so the heat pump page is built first in Phase 6.
- **Top-of-page bids (high range, PKR→USD ≈ /280):** replacement cost ≈ $23, installation cost ≈ $19, tankless install ≈ $15, how long does it last ≈ $13, "water heater serial number age" ≈ $32 (Low competition), **"repair or replace water heater" ≈ $65 (500/month, +900% in three months)**.
- **SERP findings:**
  - Cost head terms: strong incumbents (Bob Vila, Angi, Thumbtack, HomeServe, Fixr). → Attack via size/type-specific long-tail pages with an embedded calculator, not the head term.
  - "how long does a water heater last" (re-checked 2026-11-12): the whole first page is **articles** (Homes & Gardens, Bob Vila, Angi, Fox News, then plumber blogs). Google reads this as an informational query, so it gets its **own article** (item 1b) with the age calculator embedded, not the tool page.
  - "water heater age calculator / serial number": **Rheem and Ruud run official age calculators**, and InspectApedia and building-center.org rank with **one page per brand**. The earlier note that nobody has a tool was wrong. Our edge is one decoder covering all brands plus lifespan context, and per-brand pages (item 1c) to meet brand searches.
  - "what size water heater do i need": Bob Vila/Angi tables, then local plumbers. No real interactive peak-hour-demand calculator. → Winnable.
  - Calculator-specific queries (re-checked 2026-11-12): weak but **crowded**. Dozens of mass-generated calculator clones on free hosting (`*.pages.dev`, `vercel.app`), a few hacked domains, and a handful of real sites (waterfilterguru, thefurnaceoutlet, Efficiency Maine, manufacturers). → Winnable, but only if the page shows what clones cannot: named sources, manufacturer cross-check, state data (CLAUDE.md §1 Differentiation rule).
- **Brand and troubleshooting data (owner export 2026-11-13):** brand age queries are large (Bradford White 50K; A.O. Smith, Rheem, State, Whirlpool 5K each), and three troubleshooting terms are 50K (leaking, how to flush, anode rod). These two clusters are now the main traffic plan alongside the tools.
- **Strategy in one line:** launch with sizing + age tools (weak SERPs, good volume), give informational head terms their own articles that feed the tools, surround them with cost/comparison pages that carry the high bids, interlink tightly, then expand to install-cost and heat-pump pages.

### How to read the Keyword Planner numbers (rule for every thread)
- Keyword Planner is an advertiser tool. From it we take **search volume** (as a range) and **bids** (a hint of how much an ad click, and so our ad RPM, is worth). Nothing else.
- Its **"Competition" Low/Medium/High is advertiser competition**: how many advertisers bid on the keyword. It says nothing about how hard it is to rank organically. Wherever this plan quotes "Low/High" next to a keyword, read it only as ad competition. Never call a keyword an easy win because that column says Low.
- **Organic difficulty is judged only from the live SERP**, in the SERP brief: who holds the top 10 (manufacturers, big publishers, forums, small sites), how good and how fresh their pages are, whether a tool or an article ranks, and whether weak sites like ours already rank. If the owner supplies Ahrefs/Semrush keyword difficulty and the top pages' referring domains, record them in the brief as an extra signal.
- A keyword counts as an easy win only when the SERP brief shows at least two small or weak sites in the top 10.

## 2. Keyword Map (one intent = one URL)

Format: `URL` — **Primary keyword** (bucket) — secondary keywords — type — phase.

### Tools (Phase 2–4)
1. `/water-heater-age-calculator` — **water heater age calculator** (500) — how old is my water heater, water heater serial number age (500), water heater age by serial number, water heater serial number lookup/decoder — TOOL — Phase 2
   - 1b. `/how-long-does-a-water-heater-last` — **how long does a water heater last** (50K, Low) — water heater lifespan (5K), how long does a tankless water heater last (5K), water heater life expectancy (5K), how long do electric/gas water heaters last — ARTICLE + embedded age calculator — Phase 2b. This is an informational intent: answer first (lifespan table by type from `lifespans.json`), then what shortens/extends life, signs of failure, then "check your own unit's age" with the embedded tool. The tool page (1) answers lifespan only in one short FAQ that links here.
   - 1c. `/{brand}-water-heater-age` — **{brand} water heater age / {brand} water heater serial number lookup** — one page per brand whose serial format is genuinely different and sourced in `serial-formats.json`. **Build order from the owner's Keyword Planner export of 2026-11-13** (`/mnt/project-files/keyword-data/keyword-planner-brand-troubleshooting-2026-11-13.csv`; ad competition is Low, which does not measure organic difficulty; the Phase 2b SERP briefs decide the real order): 1) Bradford White (age 50K + serial number lookup 5K) 2) A.O. Smith (age 5K, lookup 500) 3) Rheem, covers Ruud (age 5K, lookup 500 and rising) 4) State (5K) 5) Whirlpool (5K) 6) American (500) 7) GE (500) 8) Kenmore (500) 9) Navien (lookup 500) 10) Rinnai (lookup 500). This brand cluster is larger than the generic calculator terms combined, so it is a launch priority, not an extra. Buckets are ranges; confirm with Search Console. If two brands share one format (same manufacturer), they share one page. Each page: the decoder preset to that brand, that brand's format table with every era/variant, 2+ worked examples, where the label is on that brand's units (SVG or owner photo), that brand's warranty/lifespan notes (sourced). — TOOL (brand preset) — Phase 2b
   - No cannibalization: page 1 owns generic queries and shows a short all-brands summary table linking to each brand page; brand pages own only "{brand} + age/serial" queries and link back to page 1.
2. `/tankless-water-heater-size-calculator` — **what size tankless water heater do i need** (5K) — tankless water heater sizing (5K), tankless water heater size calculator (5K average, recently falling), tankless water heater size chart (500), tankless water heater flow rate calculator (500), how to size a tankless water heater (500), tankless water heater size for family of 4 (500), electric tankless water heater size calculator (500), gas tankless water heater sizing, tankless gpm calculator, tankless btu calculator, what size tankless for 2/3 bathrooms, temperature rise calculator — TOOL — Phase 2
3. `/water-heater-size-calculator` — **what size water heater do i need** (5K) — water heater size calculator (500), water heater sizing calculator, water heater size chart (500), how many gallons water heater do i need (500), what size water heater for family of 4 (500), family of 5 (500), family of 2/6, water heater size for 3/4 bedroom house, first hour rating calculator — TOOL — Phase 3
4. `/repair-or-replace-water-heater` — **repair or replace water heater** (500, ≈$65 bid, +900%) — water heater repair vs replace cost, should i replace my water heater — TOOL (decision) — Phase 3
5. `/water-heater-cost-calculator` — **how much is a new water heater** (5K) — water heater cost calculator (500), 40 gallon water heater replacement cost (5K), 50 gallon water heater cost (5K), water heater cost by size/type — TOOL — Phase 4
6. `/tankless-vs-tank-water-heater` — **tankless vs traditional water heater** (5K) — tankless water heater pros and cons (5K), is a tankless water heater worth it (500), tankless vs tank water heater cost (500), hybrid water heater vs tankless (500) — TOOL (10-year cost of ownership) — Phase 4
7. `/gas-vs-electric-water-heater` — **gas vs electric water heater** (5K) — gas vs electric water heater cost (500), water heater wattage (500), tankless water heater electricity usage (500), electric/gas water heater cost per month, how many kwh does a water heater use — TOOL (operating cost) — Phase 4

### Articles with embedded calculator (Phase 6)
8. `/water-heater-installation-cost` — **water heater replacement cost** (50K) — water heater installation cost (5K, falling), cost to replace water heater, labor cost — ARTICLE + cost calc. Title and H1 lead with "Water Heater Replacement Cost" and use "installation" as the secondary term; one page for both, do NOT make a separate page. The URL stays as is (changing it needs owner approval).
9. `/tankless-water-heater-installation-cost` — **tankless water heater installation cost** (50K) — tankless water heater cost (50K), cost to install tankless water heater — ARTICLE + cost calc preset tankless
10. `/gas-water-heater-installation-cost` — **gas water heater installation cost** (5K) — ARTICLE
11. `/electric-water-heater-installation-cost` — **electric water heater installation cost** (5K) — ARTICLE
12. `/heat-pump-water-heater-cost` — **heat pump water heater cost** (5K) — heat pump water heater tax credit (500), tax credit 2026 (500), savings calculator, payback, 50/80 gallon heat pump water heater (500 each), vs gas/electric (500 each) — ARTICLE + savings calc
   - 12b. `/solar-water-heater-cost` — **solar water heater cost** (SERP 2026-11-14: cost-guide articles) — solar water heater installation cost, solar hot water heater cost, payback, tax credit 2026, solar vs heat pump water heater, is a solar water heater worth it — ARTICLE (no own calculator; state payback table from DOE FEMP equations) — audit 2026-11-14 S-12, draft until owner OK. Owns solar intents only; heat pump intents stay on page 12.
13. `/40-vs-50-gallon-water-heater` — **40 gallon vs 50 gallon water heater** (500) — 50 vs 80 gallon (50) — ARTICLE
14. `/gas-line-size-for-tankless-water-heater` — **gas line size for tankless water heater** (500, $9 bid) — ARTICLE + sizing table — **safety-critical: Phase 6d, CLAUDE.md §8b**
15. `/tankless-water-heater-electrical-requirements` — tankless water heater breaker size, amp draw, wire size — ARTICLE + table — **safety-critical: Phase 6d, CLAUDE.md §8b**
16. `/water-heater-expansion-tank-size` — expansion tank size calculator (50) — small TOOL

### Troubleshooting & care cluster (Phase 6b) — topical authority + feeds the Repair-or-Replace tool
Informational, high volume, lower RPM, but this is what makes Google treat the site as a *water heater* authority rather than a 7-page tool site. Each page ends with the embedded Repair-or-Replace or Age calculator. **Volumes from the owner's export of 2026-11-13, build in this order:** water heater leaking (50K, bid ≈$54; "leaking from bottom" 5K is an H2 on the same page), how to flush a water heater (50K), water heater anode rod (50K, High competition, partly product intent), no hot water (5K, +900%; "water heater not heating" 5K on the same page), water heater reset button (5K), water heater making noise (5K), hot water runs out fast (5K), water heater smells like rotten eggs (5K). Safety-critical ones (Phase 6d): pressure relief valve leaking (5K), temperature setting (5K), element replacement (5K), pilot light won't stay lit (500).
17. `/water-heater-leaking` — water heater leaking (from bottom / from top / from pressure relief valve as H2s) — ARTICLE
18. `/no-hot-water` — no hot water (gas vs electric causes), water heater not heating — ARTICLE
19. `/water-heater-pilot-light-wont-stay-lit` — pilot light keeps going out, thermocouple — ARTICLE — **safety-critical: Phase 6d**
20. `/water-heater-making-noise` — popping/rumbling/knocking, sediment — ARTICLE
21. `/water-heater-temperature-setting` — what temperature should a water heater be set at, 120 vs 140 — ARTICLE (+ small scald/Legionella explainer, sourced CDC/DOE) — **safety-critical: Phase 6d**
22. `/how-to-flush-a-water-heater` — how often, steps, tankless descaling — ARTICLE
23. `/water-heater-anode-rod` — what it is, when to replace, cost — ARTICLE
24. `/water-heater-pressure-relief-valve-leaking` — T&P valve — ARTICLE — **safety-critical: Phase 6d**
25. `/water-heater-reset-button` — electric reset tripping — ARTICLE
26. `/water-heater-element-replacement` — test/replace element, cost — ARTICLE — **safety-critical: Phase 6d**
27. `/hot-water-runs-out-fast` — causes, dip tube, sizing → links to size calc — ARTICLE
28. `/water-heater-smells-like-rotten-eggs` — sulfur smell, anode — ARTICLE
(Expand only after GSC shows traction; never below 5K combined cluster volume per page group.)

### State pages (Phase 6c) — only because our data is genuinely state-specific
`/water-heater-installation-cost/{state}` — e.g. `/water-heater-installation-cost/texas` — **water heater installation cost {state}** — each page renders: regional labor multiplier, state energy prices (operating-cost table), inlet water temp (tankless sizing note), state/utility rebates from `incentives.json`, permit norms (sourced where possible), and the cost calculator preset to the state. Start with the 10 most populous states; add more only if the first 10 index and rank. If two states would read identically, don't build the second.

### Linkable assets (Phase 8)
- `/data` — downloadable CSVs of every public table with `Dataset` schema and "how to cite".
- `/water-heater-installation-cost-index` — annual by-state index article with chart, methodology, embeddable chart image, press-style summary.
- `/embed/<tool>` routes for every calculator (`noindex`; credit link is brand-name anchor with `rel="nofollow"`, see CLAUDE.md §7b). These bring awareness and referral visits; the `/data` page and the index article are what we pitch for editorial links.

### Hubs & utility (Phase 0 / 5)
- `/` — brand + "water heater calculator" + links to all tools; `/calculators`; `/guides`; `/about`; `/methodology`; `/editorial-policy`; `/contact`; `/privacy-policy`; `/terms`; `/affiliate-disclosure`; `/404`.
- Root files served by the theme: `/ads.txt`, `/robots.txt`, `/llms.txt` (optional), IndexNow key file; sitemap at `/wp-sitemap.xml`.

### Explicitly NOT building (low/zero volume): standalone running-cost calculators (merge into #7), commercial sizing, RV, point-of-use, under-sink, mobile home, recovery-time calculator (fold into #3 as a section).

## 3. Calculator specifications

All formulas live in `assets/js/formulas.js` and are documented on `/methodology`. Inputs below are the UI; defaults come from `data/site.json`.

### 3.1 Water Heater Age Calculator + Serial Number Decoder (`/water-heater-age-calculator`)
**Inputs:** brand (dropdown: Rheem/Ruud, A.O. Smith, Bradford White, State, American, Whirlpool, GE, Kenmore, Rinnai, Navien, Noritz, Takagi, Bosch, Other), serial number (text), OR manufacture year (fallback), type (gas tank / electric tank / tankless / heat pump), water hardness (soft/moderate/hard/very hard — optional, with "find yours" link), maintenance (annual flush yes/no), anode rod ever replaced (y/n).
**Logic:** decode manufacture year/month from serial using brand patterns in `data/serial-formats.json` (each pattern with example + source URL to manufacturer or documented decoder). Age = today − manufacture date. Expected life from `lifespans.json` (typical range by type). Adjust: hard water −1 to −2 yrs, no maintenance −1, anode replaced +1 to +2 (document adjustments as our editorial assumptions in `methodology`). Output: age, % of expected life used, "estimated remaining life" range, status (Green <60%, Amber 60–90%, Red >90%), and signs-to-watch list.
**Next steps box:** → repair-or-replace, → size calculator, → cost calculator.
**Must have:** all-brands summary decoding table rendered in HTML with a link to each brand page (1c), one worked example, "where to find the serial number" diagram (SVG), a line saying when to use the manufacturer's own lookup (Rheem/Ruud have official tools; link them as sources), and one short lifespan answer linking to the lifespan article (1b). `?brand=` presets the decoder; brand pages embed it preset.

### 3.2 Tankless Water Heater Size Calculator (`/tankless-water-heater-size-calculator`)
**Inputs:** state (dropdown → groundwater temp from `groundwater-temp-by-state.json`, editable), desired hot water temp (default 120°F), fixtures running at the same time with counts (shower, bathroom faucet, kitchen faucet, dishwasher, clothes washer, tub fill) using GPM from `fixture-flow-rates.json`, fuel (gas/propane/electric), optional: low-flow fixtures toggle.
**Formulas:** `totalGPM = Σ(count × gpm)`; `tempRise = setpoint − groundwaterTemp`; `BTUh = totalGPM × 500 × tempRise` (8.34 lb/gal × 60 min ≈ 500); `requiredInputBTUh = BTUh / efficiency` (gas UEF default from data); `electric kW = totalGPM × tempRise × 0.1465` (= GPM×500×ΔT / 3412); electric amps at 240V = kW×1000/240, breaker sizing note (125% rule) and typical 3×40A or 4×40A configs for 27–36 kW.
**Output:** required GPM at ΔT, recommended unit class (e.g. "≈ 7.5 GPM @ 55°F rise → 180,000–199,000 BTU/h gas" or "≈ 28 kW electric, 3×40A"), whether a single electric unit is realistic in cold climates (flag when ΔT>60°F and GPM>4), size chart table (GPM by ΔT), map/table of groundwater temps.
**Next steps:** → tankless vs tank, → tankless installation cost, → gas line size, → electrical requirements.

### 3.3 Water Heater Size Calculator — tank (`/water-heater-size-calculator`)
**Inputs:** household size, fuel (gas/electric/heat pump), peak hour activities with counts (showers, baths, shaving, hand washing, dishwasher, clothes washer, food prep) using gallons from `peak-hour-demand.json` (DOE "Sizing a New Water Heater" worksheet values), optional "we often run things at once" multiplier.
**Formulas:** `peakHourDemand = Σ(count × gallonsPerUse)`; recommended **First Hour Rating** ≥ PHD; map FHR → typical tank size by fuel from `fhr-to-tank-size.json` (gas recovers faster → smaller tank for same FHR). Also show quick rule-of-thumb table by household size (sourced) and explain why FHR beats tank gallons.
**Output:** PHD (gal), recommended FHR, recommended tank size by fuel (e.g. "50-gal gas or 65–80-gal electric / 65–80-gal heat pump"), "if you choose 40 vs 50" note, electric vs gas recovery note.
**Next steps:** → cost calculator preset, → gas vs electric, → 40 vs 50, → tankless alternative.

### 3.4 Repair or Replace (`/repair-or-replace-water-heater`)
**Inputs:** age (or link from age calc via URL params), type, repair quote ($), symptoms checklist (leak from tank, rust-colored water, no hot water, pilot/element issue, noise, pressure valve, etc.), current fuel cost (defaults).
**Logic:** Replace if: tank leaking (always), OR age ≥ 75% of expected life AND repair > 25% of replacement cost, OR repair > 50% of replacement cost (the "50% rule"). Replacement cost from `cost-ranges.json` by type/size. Show the annual energy saving from moving to a current-UEF unit (formulas from 3.7) to make the economics explicit.
**Output:** Repair / Replace / Borderline with reasons; cost comparison table; "questions to ask before you accept any quote" (generic consumer guidance, no referrals).

### 3.5 Water Heater Cost Calculator (`/water-heater-cost-calculator`)
**Inputs:** type (gas tank, electric tank, heat pump, gas tankless, electric tankless), size (gallons or GPM), state (regional multiplier from `regional-cost-index.json`), scenario (like-for-like swap / fuel switch / tank→tankless), extras checkboxes (new venting, gas line upgrade, electrical panel/circuit, expansion tank, permit, haul-away, code upgrades, relocation).
**Formulas:** `unit = range(type,size)`; `labor = range(type, scenario)`; `extras = Σ selected`; `total = (unit + labor + extras) × regionalMultiplier`; output low/typical/high. All ranges in `cost-ranges.json` with sources and a `lastReviewed` date; show "ranges reviewed {date}".
**Output:** low–typical–high total, itemized table, unit-only vs installed, "how to get a fair quote" checklist.

### 3.6 Tankless vs Tank — 10-year cost of ownership (`/tankless-vs-tank-water-heater`)
**Inputs:** household size, fuel, state energy prices (defaults from `energy-prices.json`, editable), expected years in home (default 10).
**Formulas:** installed cost (from 3.5) + annual operating cost (from 3.7) × years + expected replacements within horizon (tank life vs tankless life from `lifespans.json`) − federal tax credit where applicable (gas tankless ENERGY STAR, heat pump) from `incentives.json` (with dates; mark expiry).
**Output:** side-by-side table, break-even year, verdict with assumptions listed, pros/cons table.

### 3.7 Gas vs Electric — operating cost (`/gas-vs-electric-water-heater`)
**Inputs:** household size → daily hot water gallons (default per-person from `hot-water-use.json`, DOE test uses 64.3 gal/day for a household), groundwater temp by state, setpoint, fuel prices (editable), UEF per type (defaults from `uef-ranges.json`).
**Formulas:** `energyBTU/yr = gallons/day × 365 × 8.34 × ΔT`; electric kWh = BTU / 3412 / UEF; gas therms = BTU / 100,000 / UEF; heat pump uses UEF ≈ 3–4; annual cost = units × price. Show per-month and per-year, and CO₂ note optional (sourced factors) — skip if it adds complexity.
**Output:** comparison table across gas / electric / heat pump / tankless gas / tankless electric; sensitivity to price; wattage/kWh FAQ answers rendered from the same formulas.

### 3.8 Heat pump savings (section inside page 12) and Expansion tank (page 16) — specified in Phase 6.
**Heat pump savings** (`assets/js/heat-pump-savings.js`, inside `/heat-pump-water-heater-cost/`). Inputs: current heater (electric tank / gas tank), heat pump size (50/65/80), household, state, electricity and gas price (EIA default for the state, editable), heat pump UEF (ENERGY STAR median, editable), rebate (default $0), "room needs space work" checkbox. Method: both jobs priced by `cost-model.js estimateInstalled` (heat pump = like-for-like when replacing electric, fuel switch with a new circuit when replacing gas; space work adds `heatPumpSpace`); running cost by `operatingCosts`; extra cost = heat pump − new current-type heater − rebate; `simplePaybackYears` and `netSavingOver` (formulas.js). Output: yearly saving, payback, net over the heater's life, 3-row comparison table. Status: green ≤ `paybackGoodYears` (data/heat-pump.json, editorial), amber ≤ typical life, red after. Cross-check: ENERGY STAR $550/yr and 70% less energy; DOE 2024 payback.
**Expansion tank** (`assets/js/expansion-tank.js`, tool page `/water-heater-expansion-tank-size/`). Inputs: heater gallons (50), setting °F (120), supply psi (60), maximum psi (150, relief valve). Method (AMTROL MC10007): expanded = gallons × expansion factor (Table 1, from 40 °F, interpolated); DPF = (max + 14.7) ÷ (max − supply); required = expanded × DPF; smallest tank (AMTROL MC8520 volumes, `data/expansion-tanks.json`) with total ≥ required and acceptance ≥ expanded; within 15% of either limit → next size named as "safer" (editorial). Bad status if max ≤ supply or above the largest tank; warning above 80 psi supply with the A. O. Smith PRV advice. Cross-check: AMTROL worked example + all 45 quick-chart cells.

## 4. Data files to create (Phase 1) — each with `meta.source`, `meta.sourceUrl`, `meta.sourceDate`
- `site.json` — brand, author, defaults (setpoint 120°F; US average residential electricity $/kWh and gas $/therm from EIA with month/year).
- `fixture-flow-rates.json` — GPM per fixture (standard and low-flow), source: DOE/EPA WaterSense/manufacturer typical.
- `peak-hour-demand.json` — gallons per use for the FHR worksheet, source: DOE energy.gov "Sizing a New Water Heater".
- `fhr-to-tank-size.json` — FHR ranges → typical tank gallons by fuel, source: DOE + manufacturer spec sheets (cite 2–3).
- `groundwater-temp-by-state.json` — average inlet water temp °F per state (+ DC), source: USGS/NOAA-derived tables commonly published by manufacturers; cite the specific table used; note it is an average and editable.
- `lifespans.json` — typical/min/max years by type, source: DOE + manufacturer warranty data; adjustments documented as editorial assumptions.
- `serial-formats.json` — per brand: pattern, how to read year/month, example, source URL.
- `cost-ranges.json` — unit, labor, extras by type/size/scenario; `lastReviewed`; sources: manufacturer MSRP ranges, retailer listings, and published industry ranges (used as sanity check); state our own range.
- `regional-cost-index.json` — multiplier per state (1.00 = national), source: a published construction/labor cost index (cite) or BLS wage data method documented in methodology.
- `energy-prices.json` — residential electricity ($/kWh) and natural gas ($/therm) by state, source: EIA (month/year).
- `uef-ranges.json` — UEF typical by type, source: ENERGY STAR / DOE.
- `hot-water-use.json` — gallons/day per person and DOE test draw pattern, source: DOE.
- `incentives.json` — federal credits/rebates with amount, eligibility, effective/expiry dates, source: IRS/ENERGY STAR; mark "verify current status" and a review date.
- `affiliate.json` — recommended unit categories → URL (owner fills), empty by default.

## 5. Content template for H2s (use per page; adapt wording)
- "How the calculator works" (formula in plain words + exact formula)
- "{Primary keyword} — the quick answer" (table)
- "Worked example: {typical household}"
- "What changes the answer" (climate/inlet temp, fixtures, fuel, hardness…)
- "Common sizing/costing mistakes"
- "When this is a job for a licensed professional" (generic safety/code guidance only — never a referral, name, link or form)
- "Limitations of this estimate"
- FAQ (5–8 Qs from the page's keyword list, each answer 30–70 words)
- Sources

## 6. Internal linking map
- Lifespan article (1b) ↔ Age calculator ↔ brand pages (1c); every brand page → Age calculator and → Repair/Replace.
- Home → all 7 tools + hubs. `/calculators` → all tools. `/guides` → all articles.
- Age ↔ Repair/Replace ↔ Cost. Tank size ↔ Tankless size ↔ Tankless vs Tank. Cost ↔ Installation cost articles ↔ Gas vs Electric. Heat pump cost ↔ Gas vs Electric ↔ Tankless vs Tank. Gas line / electrical ↔ Tankless size ↔ Tankless install cost.
- Every tool's "What to do next" box = 2–3 of the links above, chosen by result (e.g. age result Red → Repair/Replace first).

## 7. Success metrics (realistic, new domain)

All traffic and revenue figures below are estimates, not promises. They were lowered on 2026-11-12 after the live SERP check (manufacturer tools on age queries, crowded calculator SERPs, AI answers taking clicks on informational queries).

- Launch (Phase 5): 7 tools + 8 utility pages live, Lighthouse mobile ≥95/95/95/100, 0 schema errors, GSC **and Bing Webmaster** verified, sitemap submitted in both, IndexNow working, `/ads.txt` live. AdSense applied once ≥15 quality `live` pages exist (after Phase 6 starts) and the consent message is configured.
- Month 1–3 (sandbox period): expect little traffic. Goals are indexing of all pages, impressions on ≥50 mapped keywords, first embeds and first 3–5 editorial links, methodology cross-checks published, Phase 2b/6/6b under way.
- Month 4–6: 500-bucket calculator terms and brand-age terms in top 10; both size calculators in top 20 for their 5K terms; lifespan article indexed with impressions; 15+ referring domains; 2–6K sessions/month.
- Month 7–12: ≥2 of the 5K terms in top 10; troubleshooting cluster indexed and ranking; 8–20K sessions/month; apply to Journey by Mediavine at 10K.
- Month 12–18: **10–25K sessions/month without strong link work; 30–50K if Phase 8 (links) is executed consistently.** Blended RPM on AdSense is more likely $6–15 for this traffic mix; higher on Mediavine. The 50K cost head terms are not expected to rank in year one.
- Leading indicators to watch monthly: referring domains (followed, editorial), GSC impressions on tool and brand-age terms, avg position of the 7 tool pages, Bing impressions, embed count (awareness only).

## 8. What we are NOT doing (and why)
- No "best tankless water heater" review pages at launch — ultra-competitive affiliate SERP; revisit after authority exists.
- No brand-vs-brand pages at launch (Rheem vs A.O. Smith) — low data differentiation; maybe Phase 7+.
- No separate replacement-cost page (cannibalization). No running-cost micro-tools (no volume).
- No paid links, PBNs, followed widget links, or AI-spun mass pages.
- No SEO or page-builder plugins; no content written in the WordPress editor (CLAUDE.md §2).
