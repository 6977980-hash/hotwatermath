# SERP brief: /water-heater-model-number-lookup/

- **Primary keyword:** water heater model number lookup (water heater model number decoder)
- **Secondary (H2s here):** how to read a water heater model number, water heater rating plate, water heater warranty lookup, what size is my water heater
- **Not this page:** "water heater serial number lookup/decoder" belongs to `/water-heater-age-calculator/` (Keyword Map item 1). This page links there for the age-only intent.
- **Checked:** 2026-11-20

## Top results (US search, 2026-11-20)

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | insider.energytrust.org CPF_Serial_Water_Heater.pdf | Utility program | PDF chart | Serial date codes by brand | Serial only; nothing on model numbers, size or warranty |
| 2 | ruud.com cross-reference tool | Manufacturer | Tool | Old Ruud model → new model | One brand, no decoding of the parts |
| 3 | inspectapedia.com water heater age pages | Reference site | Long articles | Serial decoders per brand, manuals | Age only; very long; no interactive tool |
| 4 | building-center.org | Reference site | Per-brand pages | Serial charts | Age only, partly paywalled |
| 5 | fastwaterheater.com model numbers & rating plates | Local installer blog | Article | Where the plate is, what is on it | No decoder; lead-gen site |

Also seen: mountainview.gov PDF, partselect (model number locations), GE "find your model number", DOE product lookup (tax-credit tier, frozen catalog).

**Content type Google ranks:** mixed (charts, manufacturer tools, articles). An interactive tool with a strong explainer fits.

**Organic difficulty verdict: medium.** Reference sites are old and strong on age, but nobody decodes size, fuel and warranty from the model number in one tool.

## What they all miss (our additions)
1. **Model + serial in one tool:** size, fuel, height, input, warranty years, build date, age and warranty end.
2. **Warranty end date** (build date + warranty years), with the honest note that warranties run from installation (Whirlpool warranty quote).
3. **Rules proven against data:** the Rheem rule is run against all 80 Rheem tank listings in our price data on every build; Bradford White against its spec sheet; Whirlpool and American against their own documents.
4. **Photo of the label** opened locally (never uploaded), with browser text detection where available.
5. **Size class honesty:** Bradford White RG250L6N is a 50 class holding 48 gallons.

## How we are different from generic calculator clones
Every rule cites the maker's document, the cross-check table is on /methodology, and brands with no source are refused instead of guessed.

## Quality check (2026-11-20)
| Source | URL | Claim on our page | Confirmed |
|---|---|---|---|
| Bradford White spec sheet C1101-B-0618 | sidharvey.com/documents/BRG250T6N.pdf | "For Propane (LP) models change suffix “N” to “X”. For 10 year models, change suffix from “6” to “10”."; RG130T6N 30, RG240T6N 40, RG250T6N 50, RG250L6N 48, RG260T6N 60 gal; 150 psi working pressure | quoted, opened 2026-11-20 |
| Whirlpool limited warranty (form 100236126) | whirlpoolwaterheaters.com/…/035505160348_warranty.pdf | Example 30T6-30NG; codes 6/9/12 → tank 6/9/12, parts 6/9/12, labor 1/2/3; "leaks within the warranty period … after the original installation" | quoted, opened 2026-11-20 |
| Home Depot Rheem listings | data/cost-ranges.json (checked 2026-11-12/14) | XG/XE gallons, height, input, warranty in product names; 80 of 80 decode; XG75T06EN76U1 reads 76,000 vs 75,100 BTU (rounding, stated on page) | build:data check |
| American support titles | data/brand-pages.json (checked 2026-11-13) | G61/G101/E6N/E10N warranty years | build:data check |
| Serial codes, official lookups | data/serial-formats.json | reused from the age calculator (verified Phase 2/2b, audit fix 1) | inherited |

**Uncertain / honest notes:** no public model-number key for A. O. Smith, State, Rinnai, Navien, Noritz or Takagi; the page says so and decodes their serial only. DOE's product lookup was not used (catalog frozen, tax-credit tiers only). Browser text detection (TextDetector) exists only in some Chromium builds; the page falls back to a photo preview.
