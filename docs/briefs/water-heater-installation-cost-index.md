# SERP brief: /water-heater-installation-cost-index/ (Phase 8 linkable asset)

**Primary keyword:** water heater cost index (no measurable volume; this is a link asset, not a traffic page).
**Supporting queries:** water heater cost by state, water heater installation cost by state, cheapest state to replace a water heater, water heater running cost by state.
**Checked:** 2026-11-13, US search.

## Intent and content type
"Water heater installation cost by state" returns cost guides and state lists (HomeServe, Thumbtack, usecalcpro blog) plus mass-generated `*.pages.dev` clones and spam domains. Google ranks **articles with a state table**, not tools. Our existing article `/water-heater-installation-cost/` already owns "replacement / installation cost" and carries its own 51-row price table. To avoid two pages on one intent, this page is a **ranked data asset**: cost of *ownership* (install + 10 years of energy) for three tank types, with a chart, CSV and press summary. It targets journalists and resource pages, not the head term (docs/DECISIONS.md, Phase 8).

## Top results and what they have
| # | Result | H2s / structure | Tables / tools | What they miss or get wrong |
|---|---|---|---|---|
| 1 | HomeServe, "How much does it cost to repair or replace your water heater?" (403 to our fetch; read from the search result) | National averages, state-by-state repair/diagnosis/replacement | State table; several states "insufficient data" (Alaska, Arizona) | No method, no running cost, gaps in coverage, figures come from its own service jobs |
| 2 | usecalcpro.com, "Average water heater cost by state in 2026" | All 50 states table; top 5 dearest/cheapest; regional analysis; drivers; tank vs tankless; FAQ | State, avg, low, high, vs national ($1,800) | Install only, no energy; "sources" are other cost guides (HomeGuide, Angi, This Old House, Forbes Home); low/high not explained; no DC; nothing downloadable |
| 3 | Thumbtack, water heater installation cost | National range, 50-gal gas range | None by state | No state data |
| 4 | otcair.org, HPWH costs in the Northeast and Mid-Atlantic (PDF) | Regional report | Material/labor indexes for some states | Only the Northeast; a 2023 report; PDF only |
| 5 | Calculator clones (`costofhomeservice.pages.dev` and similar) | Template text | Generic national numbers | No sources, no state data, no method |

## What our page adds (at least two things none of them have)
1. **Total cost over 10 years**, not just the install price: installed price + EIA-priced energy for gas, electric and heat pump in every state and DC, ranked.
2. **A transparent method from primary sources** (BEA price levels, EIA prices, NOAA water temperature, DOE 2024 cost rules), with the formula on /methodology and a **free CSV** under CC BY 4.0 with `Dataset` schema.
3. An **embeddable chart** (PNG + SVG) with a removable nofollow brand credit, a **press-style 5-bullet summary** and a **cite box**.
4. Findings no one else states: installed prices differ ~13% between states but energy bills up to ~3–4×; a heat pump is cheaper to own than an electric tank in all 51.

## Visible difference from calculator clones
Named sources next to every table, a methodology anchor, state-specific numbers for all 51, a downloadable dataset and an interactive cost calculator from the same model.

## Organic difficulty verdict
**Medium** for "water heater cost by state" (HomeServe is a strong brand; the rest are weak blogs and clones). The page is built to earn links, so ranking for the head term is a bonus, not the goal.

## Quality check (CLAUDE.md §8), 2026-11-13
1. **Beat the SERP:** covers everything useful above (all-state table, cheapest/dearest states, drivers of the difference, tank vs alternative) and adds items 1–4. No wording or structure copied.
2. **Sources opened and confirmed today:**
   - BEA RPP PDF (rpp0226.pdf) — 200; Idaho RPP checked on FRED series IDRPPALL: 95.494 (2024), our file 95.5.
   - EIA Electric Power Annual Table 2.10 — 200; 2025 residential: Idaho 11.83 ¢/kWh, Massachusetts 30.41 ¢/kWh, both match `data/energy-prices.json`.
   - EIA natural gas residential price by state — 200.
   - NOAA Climate at a Glance — 200.
   - DOE final rule, 89 FR 37778 (May 6, 2024) on govinfo — 200 (labor and adders already verified in Phase 6, `data/installation-costs.json`).
   - Home Depot water heater listings — 200 (unit prices checked 2026-11-12, Phase 6).
   - Creative Commons BY 4.0 legal code page — 200.
3. **Live check:** after deploy (see HANDOFF).

Uncertain: the "cheapest to own" column assumes a home with gas service; the page says so in the method and in the gas/heat-pump section.
