# SERP brief: /tankless-water-heater-size-calculator/

- **Primary keyword:** what size tankless water heater do i need (Keyword Planner 5K)
- **Secondary:** tankless water heater sizing (5K), tankless water heater size calculator (5K), tankless water heater size chart (500), tankless flow rate calculator (500), how to size a tankless water heater (500), tankless size for family of 4 (500), electric tankless water heater size calculator (500), tankless GPM / BTU calculator, what size tankless for 2 or 3 bathrooms, temperature rise
- **Checked:** 2026-11-12 (US search; pages opened the same day where the site allowed it)

## Top 5 organic results for "what size tankless water heater do i need"

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | happyhiller.com (What Size Tankless Water Heater Do I Need?) | Local plumber blog (2020) | Article | Six steps: flow rate, temperature rise, groundwater, gas vs electric, energy factor | **No formula, no fixture GPM values, no table, no worked numbers**; says tankless never needs venting (gas units do); unsourced groundwater figures; dated |
| 2 | angi.com/articles/how-big-should-my-tankless-water-heater-be.htm | Big publisher | Article | Flow rate + rise method, household-size ballpark, 2–12 GPM range (from search snippet) | Page blocks automated access (HTTP 403), so it was reviewed from the search result only; no state data; lead-gen for pros |
| 3 | propane.com (What Size of Tankless Water Heater Do I Need?) | Propane industry council | Article | Propane-focused sizing (search snippet) | Blocks automated access (403); propane only |
| 4 | bobvila.com/articles/what-size-water-heater-do-i-need | Big publisher | Article | Tank and tankless sizing overview | Mostly tank sizing; tankless is one section |
| 5 | waterfilterguru.com/what-size-tankless-water-heater-do-i-need/ | Affiliate site | Article + calculator | "(+ Calculator)" in title | URL answered **404** on 2026-11-12 (stale result) |

Also in the top 15: Angi tank sizing, A. O. Smith (hotwater.com) sizing guide, Direct Energy, lentheplumber.com, theexaminernews "free custom calculator", mrrooter.ca, powerequipmentdirect, thetradetable, iwae.com, impressiveclimate.

**For "tankless water heater size calculator"** the SERP is crowded with clones: heatersize-net.pages.dev, sizecalculator-net.pages.dev, crunchmilk.pages.dev, totalcalchub, best-osmosis-systems, diysmarthomehub, plus Intellihot (manufacturer, commercial-leaning), Navien's blog and InspectApedia. Opened on 2026-11-12:
- heatersize-net.pages.dev: blank inputs, no defaults, no formula, **no sources**, no author; groundwater as three vague bands.
- diysmarthomehub tankless calculator: fixture defaults (shower 2.0, kitchen 1.8, bathroom faucet 1.0, tub 4.0) with **no source**; four "climate zone" inlet temperatures instead of real local data; no formula shown; conflicting shower figures in its own text.
- InspectApedia Tankless_Water_Heater_Size: manual method only; "TGPH" unit error; DOE example cited; no state data.

**Content type Google ranks:** "what size … do I need" is held mostly by answer-first articles, several of which bolt on a calculator; "… size calculator" is held by tools. The keyword map gives both to one tool page, so this page must read as a complete answer-first article **and** be the best tool. Every H2 opens with a standalone answer, and the Quick Answer gives the number before the tool.

**Organic difficulty verdict: medium.** Angi, Bob Vila and propane.com are strong, but local plumber blogs (happyhiller, lentheplumber, iwae) and thin calculator clones sit in the top 15, so a better page can rank.

## Questions the top pages answer
What is temperature rise? How many GPM does a house need? Gas or electric? What size for a family of 4 / 2 or 3 bathrooms? Can one unit run two showers? What about cold climates? Should I oversize?

## Manufacturer sizing literature used for the cross-check
- Rinnai America model chart (2016, hosted on lowes.com): input BTU, energy factor, GPM at 70 °F and 50 °F rise per model.
- Noritz "How to size a tankless" printable (2024, training.noritz.com): 120 °F setpoint, worked examples 6 GPM and 10 GPM at 65 °F rise.
- Lochinvar tankless sizing guide (assets.lochinvar.com): GPM by rise per model, showers at 50 °F and 70 °F inlet.
- Rheem RETEX electric spec sheet (already in `data/electrical.json`).

## How our page is different from the generic calculator clones (CLAUDE.md §1)
- **State-specific inlet temperature** for all 50 states and DC (NOAA normals + the DOE EnergyPlus mains correlation, coldest month), editable.
- Fixture defaults are the **federal maximum flow rates** (10 CFR 430.32) with WaterSense low-flow values, named next to the result.
- Formula with the user's own numbers in "How we calculated this", and a **published cross-check against Rinnai, Noritz and Rheem literature** on /methodology.
- Both fuels: gas input BTU/h at an editable UEF, electric kW, amps and the breakers one manufacturer lists, plus a cold-climate warning for single electric units.

## At least two things none of the top 5 have
1. Real incoming water temperature by state (coldest month), not climate bands.
2. A size chart (GPM by temperature rise) for common gas inputs and electric kW, computed with the same formula and checked against manufacturer charts.
3. A worked example with every number visible.
4. The number of units needed when one gas unit is not enough.

## Page plan
H1 "Tankless water heater size calculator" → subtitle → Quick Answer → calculator → H2s: How do you size a tankless water heater? → Tankless water heater size chart (GPM × rise) → What is temperature rise, and what is it in your state? → Worked example → Gas vs electric sizing → What size for a family of 4, or for 2 or 3 bathrooms? → Common sizing mistakes → When this is a job for a licensed professional → Limitations → FAQ → Sources.

## Quality check (CLAUDE.md §8), 2026-11-13

**1. Beat the SERP.** Covered from the top results: flow rate + temperature rise method (all), groundwater by climate (happyhiller, clones), gas vs electric (happyhiller, Angi snippet), propane (propane.com), household-size ballpark (Angi), oversizing advice (Noritz/clones), multiple units (search results). Added, none of them have it: state-by-state coldest-month inlet temperature for 50 states + DC; fixture defaults from federal law with WaterSense alternatives; a GPM-by-rise size chart for gas and electric computed with the published formula; a worked example with every number; a family-of-4 table by climate; units-needed answer; electric breaker reference from a spec sheet; a published cross-check against Rinnai, Noritz and Rheem literature. Angi and propane.com block automated access, so they were judged from their search snippets only (noted above).

**2. Sources verified** (opened 2026-11-12/13; `npm run validate-data -- --urls`: 90/90 URLs answered 200):

| Source | Checked | Figure or claim confirmed |
|---|---|---|
| eCFR 10 CFR 430.32 | 2026-11-13 (Phase 1 figures re-requested, 200) | showerhead 2.5 GPM at 80 psi; faucets 2.2 GPM at 60 psi |
| EPA WaterSense showerheads / bathroom faucets | 2026-11-13 (200) | ≤ 2.0 GPM showerheads, ≤ 1.5 GPM bathroom faucets |
| PNNL BASC instantaneous sizing | 2026-11-13 (200) | dishwasher 1.0–3.0, washer 1.0–6.0, tub 1.0–6.0 GPM |
| NOAA Climate at a Glance + EnergyPlus mains correlation | 2026-11-13 (200) | inputs of `data/groundwater-temp-by-state.json` (Phase 1) |
| DOE FEMP | 2026-11-13 (200) | "For most residential applications, set the temperature at 120°F" (Phase 1 quote) |
| ENERGY STAR key product criteria | 2026-11-13 (200) | gas tankless UEF ≥ 0.95 |
| Rinnai model chart (lowes.com PDF, © 2016) | 2026-11-12 | RUR98i 199,000 BTU/h, EF 0.95, 5.5/7.7 GPM at 70/50 °F; RL94i 199,000, 0.82, 4.7/6.6; V65i 150,000, 0.82, 3.6/5.0; RUC90i, RUC80i, RL75i, V53e |
| Noritz "How to size a tankless" (2024) | 2026-11-12 | 120 − 55 = 65 °F; shower 2, lav 1, kitchen 1.5, dishwasher 2, washer 2 GPM; 6 GPM → one unit (EZ111 …), 10 GPM → a pair; "always better to slightly oversize" |
| Lochinvar tankless sizing guide | 2026-11-12 | GPM by rise per model; capacity in showers at 50/70 °F inlet |
| Rheem RETEX spec sheet THD-PTE | 2026-11-13 (200) | 13–36 kW, amps and breakers per model (Phase 1 data) |
| DOE Energy Saver tankless example (UNT archive) | 2026-11-13 (200) | 0.75 + 2.5 = 3.25 GPM, 70 °F rise |

**Uncertain:** the cold-climate warning threshold (rise > 60 °F and flow > 4 GPM) is our editorial rule from STRATEGY §3.2 and is labelled as ours. The breaker "125% rule" in STRATEGY §3.2 was not added: we could not confirm that the NEC continuous-load clause applies to tankless units, so the page shows only the manufacturer's listed breakers.

**3. Live page check:** see HANDOFF.md (Phase 2 "Verified").
