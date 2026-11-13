# SERP brief: /water-heater-pilot-light-wont-stay-lit/ (Phase 6d, safety-critical, CLAUDE.md §8b)

- **Primary keyword:** water heater pilot light won't stay lit (Keyword Planner 500)
- **Secondary:** pilot light keeps going out, thermocouple, thermopile, status light blinking, how to relight, flammable vapor sensor lockout
- **Checked:** 2026-11-13

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | hunker.com/13415796/my-hot-water-heater-pilot-light-wont-stay-lit | Publisher | Article | Basics, thermocouple, relight steps (5-minute wait), safety tips | Only reference is a furnace blog; no thermopile, no status codes, no FVIR, wait time not from a manual |
| 2 | hometips.com/repair-fix/water-heater-pilot-light-wont-stay-lit.html | Publisher | Article | Causes, thermocouple | TLS fails from our sandbox; snippet only |
| 3 | appliancepartspros.com/b/?p=11066 | Parts retailer | Article | 5 causes, DIY orifice cleaning with a wire, Honeywell reset (hold 30–60 s) | Tells owners to clean the orifice and replace parts (Rheem's manual leaves this to a technician); no FVIR, no thermopile, no codes |
| 4 | ifixit.com/Answers/View/501068 | Q&A | Forum | Owner answers | No sources |
| 5 | rotorooter.com/…/why-is-my-water-heater-pilot-light-not-staying-lit/ | Plumbing company | FAQ | "Usually the thermocouple; replace it" | One cause only, no sources, sells service |

Also in the top 10: fix.com, constellationhome, searspartsdirect (403), acservicetech, waterheaterrescue, rethority, hotwater.com (A. O. Smith) pilot guide.

**Content type Google ranks:** troubleshooting articles. **Organic difficulty verdict: medium:** publishers and parts retailers, but none cites a manufacturer manual.

## Which official source backs each claim the SERP makes
- "Bad thermocouple is the usual cause": Rheem GG Series manual (pilot does not stay lit when the red button is released: loose or failed thermocouple, safety magnet, single-use shut-off) and A. O. Smith pilot guide (broken thermocouple → pilot will not remain lit once the knob is released).
- "Drafts / not enough air": A. O. Smith pilot guide; Signature 100 chart (insufficient combustion air).
- "Dirty or clogged pilot": Signature 100 chart; Rheem says a qualified service technician cleans or replaces it.
- "Air in the gas line": A. O. Smith gas manual notice (new heater may take several attempts); Rheem (technician purges).
- "Wait 5 minutes" (Hunker, AppliancePartsPros): A. O. Smith's label says 10 minutes, and its control requires 10 minutes between attempts.
- "Hold 30–60 seconds": A. O. Smith: click the igniter for up to 90 seconds until the status light blinks.
- "Gas control valve may be bad": A. O. Smith pilot guide (service visit, valve replaced) and status codes 4, 7, 8.

## What they all miss (our additions)
1. **Diagnosis by timing** table (won't light / out on release / dark status light / out later / overheated / after vapors), each tied to a manual.
2. **Status light code table** (A. O. Smith thermopile control: off, solid, 1, 2, 4, 5, 7, 8 flashes).
3. **FVIR** explained from the manual: can disable the heater on bad air supply; vapor incident = replace the heater; never disable the thermal switch.
4. The label's **lighting procedure** with the right numbers (10 min, 1/4 inch, 90 s, three attempts), attributed, plus "don't light by hand" for electronic models.
5. **Parts prices** (thermocouples $8.49–$32.01, pilot assemblies $39.99–$184.18, gas controls $91.71–$388.37) and an honest "no sourced labor figure".
6. Embedded repair-or-replace calculator.
7. Correction of DIY orifice cleaning advice with Rheem's own warning.

## How we are different from generic calculator clones
Every cause and code is from a named manufacturer manual, and the page says where its own advice starts (the "Our advice" column).

## Quality check (2026-11-13)
**Beat the SERP:** covers thermocouple, drafts, dirty pilot, gas supply, air in line, gas valve, relighting, safety, plus the additions. §8b: no gas work steps; the only procedure is the label's lighting instructions, attributed and summarized; all part repairs go to a qualified technician; safety notice (gas smell, flammable vapors, carbon monoxide from lack of air) at the top.

**Sources verified (opened 2026-11-13):**

| Source | URL | Claim / figure | Confirmed |
|---|---|---|---|
| A. O. Smith Signature 100 manual 100341469 (March 2021) | aosmithatlowes.com/media/1692/signature-100-series-direct-vent-100341469.pdf | lighting steps (10 min, 1/4 in, 90 s, 3 attempts, call technician or gas supplier); "pilot will not light or remain lit" causes; status codes | quoted (pdftotext) |
| A. O. Smith pilot light guide | hotwater.com/info-center/how-to-light-a-pilot-light.html | thermocouple/thermopile role; drafts; air supply → CO; gas pressure outside heater; kinked flex tubes; control valve → service visit; label supersedes; homeowner can relight; manual models lit with a long lighter | quoted (curl) |
| A. O. Smith gas manual 100400765 | assets.hotwater.com/damroot/Original/10017/100400765.pdf | FVIR text, vapor incident → replace heater, thermal switch warning, air in new gas line, "DO NOT try to light the pilot by hand" on its electronic model, smell gas, flood rule | quoted |
| Rheem GG Series manual AP12172-5 | images.salsify.com/…/04ce5276….pdf | pilot does not stay lit causes → qualified technician; DO NOT attempt repair of gas control, burners, safety devices; lint/debris on air inlets | quoted |
| The Home Depot searches | homedepot.com/s/water%20heater%20thermocouple, …thermopile, …gas%20control%20valve | listing prices | listing prices (JSON in page) |
| Rheem repair vs replace | rheem.com/…/water-heater-lifespan-when-to-repair-vs-replace/ | 50% rule (in data/decision-rules.json) | in data |

**Uncertain / honest notes:** the status codes are A. O. Smith's control; other brands differ and the page says so. The Rheem manual is a 2003 model (standing pilot with thermocouple), used for older heaters. No labor price found that we would stand behind.
