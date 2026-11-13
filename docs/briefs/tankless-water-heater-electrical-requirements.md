# SERP brief: /tankless-water-heater-electrical-requirements/ (Phase 6d, safety-critical, CLAUDE.md §8b)

- **Primary keyword:** tankless water heater electrical requirements (with breaker size, amp draw, wire size; STRATEGY item 15)
- **Secondary:** tankless breaker size, wire size, amp draw, 200 amp service, 208 volts, gas tankless outlet
- **Checked:** 2026-11-13

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | eccotemp.com/blog/electric-tankless-water-heater-installation-requirements/ | Manufacturer blog | Article | Own SHiE model table (breakers, 8 AWG), checklist, permits | No code reference, no service size, no 125% discussion, no wire temperature rating |
| 2 | pickhvac.com/water-heater-breaker-size/ | Publisher | Article | W ÷ V × 1.25 math, generic tankless row "30–150 A" | Applies the storage-tank 125% rule generally; tankless row inconsistent with its own math; no manufacturer table |
| 3 | hotwater.com/info-center/tankless-water-heater-installation-guide.html | Manufacturer | Guide | General install overview | Not an electrical sizing page |
| 4 | hvacbase.org/tankless-water-heater-breaker-size | Publisher | Article | kW ranges → circuits, NEC cites | Mixes the 125% rule in and out; no source for its tankless rows; no 60/75 °C distinction |
| 5 | finehomebuilding.com/forum/double-pole-breaker-size | Forum | Q&A | Practitioner discussion | Opinions, no source |

Also in the top 10: eleccalc.vercel.app (generic calculator clone), plumbinglab, expertce, easybear blog pages, a RONA-hosted manual, a Lowe's-hosted manual.

**Content type Google ranks:** articles and manufacturer pages; one calculator clone. Keyword stays an ARTICLE with tables. **Organic difficulty verdict: medium:** no strong publisher, but several near-duplicate articles.

## Which official source backs each claim the SERP makes
- "125% of the rating": IRC 2021 E3702.10 [NEC 422.13], which applies to **storage-type** heaters of 120 gallons or less. For tankless units, E4101.4.1 [NEC 422.11(E)]: overcurrent protection shall not exceed the marked rating; Stiebel Eltron: "Overcurrent protection sized at 100% of load. Tankless water heaters are considered a non-continuous load."
- Breaker and wire per model: Stiebel Eltron Tempra spec sheet, Rheem RETEX spec sheet.
- 8 AWG on 40 vs 50 A: IRC Table E3705.1 [310.16] (60 °C 40 A, 75 °C 50 A), E3705.4.4 [334.80] NM = 60 °C, E3705.4.1 [110.14(C)] terminations.
- Service size: Stiebel Eltron minimum main service per model (100–300 A).
- 208 V: Rheem ("25% reduced temperature output"), Stiebel's 208 V ratings; Ohm's law.
- Gas tankless power: Rinnai FAQ (120 V, < 3 A).

## What they all miss (our additions)
1. **Two manufacturers side by side** with draw per circuit and "breaker vs draw" (100% to 120%), which shows why charts disagree.
2. **The 125% rule placed correctly**: storage-type wording quoted from the IRC, the marked-rating rule, and Stiebel's 100% statement.
3. **Wire temperature column** (60 vs 75 °C) with the code rows and the NM cable limit, explaining how 8 AWG can be on a 50 A breaker in Stiebel's table.
4. **208 V table**: our Ohm's-law figure matches all six of Stiebel's published 208 V ratings.
5. Stiebel's minimum service per model and DOE's panel upgrade range.
6. Embedded tankless size calculator preset to electric (kW and amps for the household).

## How we are different from generic calculator clones
Breaker and wire numbers are the makers' own, shown next to the code text they have to satisfy, with the arithmetic checked against the makers' 208 V figures; nothing is a generic "X kW needs Y A" rule.

## Quality check (2026-11-13)
**Beat the SERP:** covers breaker size, amp draw, wire size, service, permits, licensed electrician, plus the six additions. No wiring steps; "what you can do" is reading the main breaker and asking for a load calculation. Safety notice at the top (power off at the breaker, A. O. Smith) plus a "not a wiring design" notice.

**Sources verified (opened 2026-11-13):**

| Source | URL | Claim / figure | Confirmed |
|---|---|---|---|
| ICC 2021 IRC Part VIII (Ch. 37, Ch. 41) | codes.iccsafe.org/content/IRC2021P2/chapter-37-branch-circuit-and-feeder-requirements | E3702.10 125% storage-type ≤120 gal [422.13]; E4101.4.1 [422.11(E)]; Table E3705.1 copper 10–4 AWG; E3705.4.1; E3705.4.4; Table E3705.6 | read in the owner's ICC free view (403 to scripts) |
| Stiebel Eltron Tempra specs rev. 6.2025 | stiebel-eltron-usa.com/sites/default/files/pdf/tech-specs-tempra-trend-plus.pdf | kW 240/208, amps, breakers, wire, service 100–300 A, 100% non-continuous, 75 °C, <3% drop | quoted (pdftotext) |
| Rheem RETEX spec sheet THD-PTE Rev. 2 | images.thdstatic.com/…/16165a91….pdf | kW, amps, breakers, wire; 208 V 25% reduced output | quoted (pdftotext) |
| Rinnai professional FAQ | rinnai.us/professional/faq | 120 V AC, 60 Hz, less than three amps | quoted |
| Installation cost model | data/installation-costs.json → extras.circuit, extras.panel | $300–$1,200 per circuit; panel $750–$2,000 (DOE) | in data |

**Uncertain / honest notes:** NFPA 70 (NEC) itself did not load in NFPA LiNK; the page quotes the IRC sections, which print the NEC section numbers. Rheem RETEX-08 and RETEX-11 were left out because their published amps do not equal kW ÷ 240 V. "Draw per circuit" assumes an even split across circuits.
