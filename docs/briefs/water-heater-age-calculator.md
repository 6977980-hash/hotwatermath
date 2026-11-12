# SERP brief: /water-heater-age-calculator/

- **Primary keyword:** water heater age calculator (Keyword Planner 500)
- **Secondary:** how old is my water heater, water heater serial number age (500), water heater age by serial number, water heater serial number lookup / decoder
- **Checked:** 2026-11-12 (US search, live pages opened the same day)

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | ruud.com/how-old-is-my-water-heater/ | Ruud (Rheem group), manufacturer | Tool | One serial field; returns build date and age (years, months) from Rheem's own records | Ruud/Rheem only; no format explained; no "where is the serial"; no lifespan context; contractor banner |
| 2 | diysmarthomehub.com (Water Heater Age Calculator by Serial Number) | Small affiliate site | Tool + article | Brand dropdown (AOS/State/American, Rheem, Bradford White, Kenmore, GE), serial, manual date, type, water quality, anode; lifespan table; factor table | **No sources at all**; Rheem rule given as "WWYY or YYWW" (ambiguous, and misses the plant-letter format Rheem's own tool uses); Bradford White 20-year ambiguity not explained; lifespans contradict each other (gas 8–12 in the table, ~13 in text; electric "almost 20"); no FAQ |
| 3 | rheem.com/how-old-is-my-equipment/ | Rheem, manufacturer | Tool | Official lookup for Rheem tank and tankless | Rheem only; no explanation |
| 4 | workingre.com/how-to-determine-water-heater-age/ (Sensible Digs) | Trade magazine reprint | Article | Brand-by-brand prose for AOS, Rheem, Navien, Bradford White, American, State, Lochinvar, Rinnai | No tool; no tables (rules only in images); **confuses install and build date**; Navien and Rinnai rules contradictory; no sources; copyright 2013 |
| 5 | inspectapedia.com/plumbing/Water-Heater-Age-Guesser.php | Large reference site | Article | How to estimate age when the label is gone (manual dates, building age, pipes) | No calculator; cluttered; typos; brand detail on other pages; stale "current date" |

Also seen in the top 15: InspectApedia age PDF and FAQ pages, Energy Trust of Oregon decoder (PDF), City of Palo Alto "Check your water heater's age", waterheaterhub, plumber blogs (fastwaterheater, tlcplumbing), sensibledigs brand pages, hvacbase lifespan calculator.

**Content type Google ranks:** tools first (two manufacturer lookups, one clone calculator), then articles. A tool page is the right format for this keyword.

**Organic difficulty verdict: medium.** Manufacturers hold #1 and #3 for their own brands only. Two weak sites (diysmarthomehub, workingre reprint) are in the top 5 and several plumber blogs fill the top 15, so a better multi-brand tool can rank. The brand-specific queries are handled by Phase 2b pages.

## Questions the top pages answer
How do I read the serial number? Where is the serial number? How long does a water heater last? When should I replace it? What if the label is missing? What shortens life (hard water, no flushing, anode)?

## How our page is different from the generic calculator clones (CLAUDE.md §1)
- Every brand rule names its source next to the table, and the decoder was **cross-checked against Rheem/Ruud's own lookup and Bradford White's own year table** (results on /methodology, linked under the result).
- Letter-coded years (Bradford White repeats every 20 years) show **all possible years**, not a silent guess.
- Lifespans come from DOE's 2024 efficiency rules and DOE FEMP, shown with the source; our hardness / flushing / anode adjustments are **labelled as our own assumptions** and are editable inputs, not hidden.
- Navien's date code is taken from a **CPSC recall notice** (a federal source), which no top result cites.
- When the brand is not decodable, we say so and link the manufacturer's own lookup instead of guessing.

## At least two things none of the top 5 have
1. A multi-brand decoder whose rules are sourced and cross-checked against the manufacturer's own tool (published test cases).
2. A clear warning that the serial gives the **build** date, not the install date, with a worked example.
3. An original SVG showing where the rating plate sits on gas, electric and tankless units.
4. A status band (share of expected life used) with the remaining-life range.

## Page plan
H1 "Water heater age calculator" → subtitle → Quick Answer → calculator → H2s: How do you read a water heater serial number? (all-brands summary table + brand page links) → Where is the serial number? (SVG) → Worked example → How long does a water heater last? (short; links to the lifespan article, planned) → What changes the estimate → When the serial will not decode → Common mistakes → When this is a job for a licensed professional → Limitations → FAQ → Sources.

## Quality check (CLAUDE.md §8), 2026-11-13

**1. Beat the SERP (top 5 re-opened 2026-11-12/13).** Every useful point they cover is on our page: how to read the serial by brand (all five), where the serial is (Ruud, Bradford White), lifespan and when to replace (diysmarthomehub, workingre), hard water / anode / flushing (diysmarthomehub), what to do without a label (InspectApedia). Added, none of them have it: a sourced multi-brand decoder cross-checked against Rheem/Ruud's own lookup and Bradford White's table (published on /methodology); every possible year for 20-year letter codes; build date vs install date; Navien code from a CPSC notice; an original rating-plate SVG; editable, labelled lifespan adjustments with DOE sources; a worked example with every step. Wording and structure are our own.

**2. Sources verified** (opened 2026-11-12/13; `npm run validate-data -- --urls`: 90/90 data-file URLs answered 200):

| Source | Checked | Figure or claim confirmed |
|---|---|---|
| ruud.com/how-old-is-my-water-heater (+ its lookup service) | 2026-11-13 | Q431912345 → 10/18/2019; M101812345 → 03/07/2018; Q052412345 → 01/25/2024 (plant letter, week, year) |
| rheem.com/how-old-is-my-equipment | 2026-11-13 | Official Rheem lookup exists (200) |
| bradfordwhite.com/my-water-heater | 2026-11-12 | "first two characters … year and month"; A = 2004 or 2024 … Z = 2023 or 2043; skips I, O, Q, R, U, V; months A–M without I |
| CPSC Navien recalls 2019 and 2018 | 2026-11-12 | "first two digits identify the year, the next single digit … month … last two digits … day. For example, 18709 stands for July 9, 2018. X stands for October." |
| navieninc.com/warranty | 2026-11-13 | Serial on the outside right of the unit, between two barcodes, 15–16 digits |
| Energy Trust of Oregon decoder PDF | 2026-11-13 | Rheem 0794… = July 1994 (month, year); American year + week (9746 = week 46 of 1997); American makes Whirlpool; State/Kenmore; General Electric listed under Rheem |
| govinfo 89 FR 2024-09209 (May 6, 2024) | 2026-11-13 | "average product lifetime to be around 15 years for storage water heaters" |
| govinfo 89 FR 2024-30369 (Dec 26, 2024) | 2026-11-13 | "average product lifetime to be around 20 years for instantaneous water heaters" |
| rheem.com lifespan article (Mar 25, 2026) | 2026-11-13 | tank 8–12 years, tankless 15–20+; signs: leak at base, rusty water, rumbling or popping |
| USGS Hardness of water | 2026-11-13 | soft 0–60, moderately hard 61–120, hard 121–180, very hard >180 mg/L; hardness map page exists (opened via fetch; it returns 403 to curl) |

Removed or not used because no source could be confirmed: Rinnai, Noritz, Takagi and Bosch date codes (third-party guides contradict each other).

**Uncertain:** Navien months Y and Z (November, December) follow the sequence but no opened source shows them; the data file says so. Kenmore and GE are decoded with the State and Rheem codes with a visible caveat, per the Energy Trust decoder.

**3. Live page check:** see HANDOFF.md (Phase 2 "Verified").
