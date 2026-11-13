# SERP brief: /gas-line-size-for-tankless-water-heater/ (Phase 6d, safety-critical, CLAUDE.md §8b)

- **Primary keyword:** gas line size for tankless water heater (Keyword Planner 500, $9 bid)
- **Secondary:** what size gas line for tankless, 1/2 inch gas line tankless, 3/4 inch gas line tankless, gas pipe sizing chart, tankless gas meter size, propane tankless gas line
- **Checked:** 2026-11-13

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | jlconline.com/how-to/hvac/3-gas-line-tips-for-tankless-water-heaters/ | Trade magazine (sponsored by a propane trade group) | Short article | Tank 1/2 in, tankless may need 3/4 in; pressure drop; pipe materials | No tables, no numbers for drop, no meter, no code table numbers; promises five tips, gives three |
| 2 | media.rinnai.us …/TB-111 Half Inch Gas line.pdf | Manufacturer | Technical bulletin | Longest-length worked example, 0.3 vs 3.0 in. w.c. drop, 1,000 BTU/ft³ | Example only; images of tables; no meter check |
| 3 | support.eccotemp.com …/gas-piping-facts | Manufacturer (copy of Rheem's brochure text) | FAQ | 7 in. w.c. supply, 0.5 drop, 3.0 drop conditions, meter × 1,024 | Refers to Tables 2–4 that are not on the page |
| 4 | thefurnaceoutlet.com/tankless-water-heater-gas-line-sizing… | Retailer blog | Long article | BTU → CFH at 1,000, total load examples, meter, propane differs | No table and no answer: the worked example stops before a pipe size; NFPA cited via a meeting agenda |
| 5 | rinnai.us/professional/faq | Manufacturer | FAQ | Max equivalent length of 1/2 and 3/4 in per model, natural gas and propane | Model lookup only; one figure (RUC80i 3/4 in 166 ft) looks like a slip |

Also in the top 10: Rheem "Gas Line Information 2018" PDF (distributor-hosted), propane.com, jaspector.com (IRC code page), a `*.pages.dev` clone ("3/4 inch in most cases"), a scraped blogspot copy, GE manual pages.

**Content type Google ranks:** articles and manufacturer documents, no tools. Keyword stays an ARTICLE with a table (STRATEGY item 14). **Organic difficulty verdict: easy to medium:** manufacturers' PDFs and thin articles; no publisher page gives the code table with the conditions.

## Which official source backs each claim the SERP makes
- "Tankless needs 3/4 inch": Rheem brochure ("a near 200,000 BTU gas appliance will require a minimum of a 3/4-inch gas supply line"), Rinnai TB-111 ("typically require a 3/4" gas line"); IFGC 2021 Table 402.4(2) confirms by lookup.
- "1/2 inch can work": Rinnai FAQ/TB-111 and Rheem: only on the 3.0 in. w.c. table, which IFGC 2021 Table 402.4(3) limits to an initial supply pressure of 8.0 in. w.c. or more (Rheem adds: dropped pressure must exceed the highest appliance minimum).
- "Divide BTU by 1,000 (or 1,024)": Rinnai FAQ ("1 Cu. Ft. Nat'l gas = 1,000 BTU"), Rheem (1,024).
- "Size for all appliances": IFGC 2021 Section 402.2 (sum of maximum inputs, all operating at once).
- "Meter may be too small": Rheem (meter and regulator capacity, 250 CFH typical, example 386 CFH).
- "7 in. w.c. typical supply": Rheem brochure.
- pages.dev "3/4 in most cases" and similar: unsourced; superseded by the table lookup.

## What they all miss (our additions)
1. **The code rows themselves** (IFGC 2021 Tables 402.4(2) and 402.4(3), 10–100 ft, four sizes), with table number and edition, quoted from the code.
2. **A BTU × length lookup** for four common tankless inputs at both pressure drops (computed with formulas.gasPipeSize).
3. **Manufacturer cross-check:** our reverse lookup reproduces 12 of Rinnai's 14 published natural-gas maximum lengths; the two differences are explained (RUC80i 166 vs 250 ft; RU199i 50 vs 40 ft) and the shorter figure is shown.
4. **Meter load table** from Rheem's example, before and after the tankless unit.
5. Embedded tankless size calculator (gives the BTU input to look up).
6. Propane, CSST (Section 402.3 item 2: manufacturer's listed tables) and altitude (Section 402.2, above 2,000 ft) caveats.

## How we are different from generic calculator clones
The page publishes the code rows it uses with table number and edition, shows its lookup reproducing a manufacturer's own table, and names every condition (pressure, length rounding, fittings, shared load) instead of a single "3/4 inch" answer.

## Quality check (2026-11-13)
**Beat the SERP:** covers every useful point from the five (3/4 vs 1/2, pressure drop, BTU→CFH, total load, meter, propane, materials, pro installation) plus the six additions above. No gas work steps: the page says what the code and manuals require and that a licensed professional sizes and installs; the "what you can do" list is reading rating plates and asking questions. Safety notice at the top (A. O. Smith "if you smell gas", via the shared part) plus an installer notice.

**Sources verified (opened 2026-11-13):**

| Source | URL | Claim / figure | Confirmed |
|---|---|---|---|
| ICC 2021 IFGC Chapter 4 | codes.iccsafe.org/content/IFGC2021P1/chapter-4-gas-piping-installations | 401.4, 402.1, 402.2 (sum of inputs, simultaneous, altitude >2,000 ft), 402.3 (tables, listed manufacturer tables, engineering), Table 402.4(2) rows 10–100 ft (0.5 in. w.c.), Table 402.4(3) rows 10–500 ft (3.0 in. w.c., initial supply ≥8.0 in. w.c.) | read in the owner's ICC free view (403 to scripts); rows copied from that read |
| Rheem gas piping brochure (2018) | images.kleen-ritecorp.com/…/Gas Line Information 2018.pdf | 140,000–200,000 BTU; 7 in. w.c.; 0.5 in. drop; 3/4 in minimum near 200k; 3.0 drop conditions (8 in. w.c.); ×1,024; example 394,900 BTU → 386 CFH; tank 235,000; meter 250 CFH; undersized effects; negative pressure valve; licensed tradesman | quoted (pdftotext) |
| Rinnai TB-111 | media.rinnai.us/…/TB-111 Half Inch Gas line.pdf | 3/4 typical, 1/2 in some cases; 1,000 Btu/ft³; 52 ft rounded to 60 ft; licensed professional | quoted (pdftotext) |
| Rinnai professional FAQ | rinnai.us/professional/faq | NG and LP max lengths per model; 1,000 BTU/ft³; 8.0 in. w.c., 3.0 drop; ceases operation on inadequate gas flow; 120 V < 3 A | quoted (curl + WebFetch) |
| Installation cost model | data/installation-costs.json → extras.gasLine | $300–$1,000, typical $600 | in data (Phase 4 sources) |

**Uncertain / honest notes:** codes.iccsafe.org answers scripts with 403, so the automatic link check cannot confirm it; the rows were read in the ICC free view on 2026-11-13. The Rheem brochure is a distributor-hosted copy. NFPA 54 itself was not opened (NFPA LiNK did not load); the page says some areas adopt NFPA 54 and shows that Rinnai's NFPA 54-based table matches the IFGC rows.
