# SERP brief: /water-heater-recall-lookup/

- **Primary keyword:** water heater recall lookup (water heater recall by model number)
- **Secondary (H2s here):** is my water heater recalled, Rheem/A. O. Smith/Bradford White water heater recall, water heater recall list, CPSC water heater recall
- **Not this page:** age and serial decoding stays with `/water-heater-age-calculator/` and the brand age pages; model decoding stays with `/water-heater-model-number-lookup/`.
- **Checked:** 2026-11-21

## Top results (US search, 2026-11-21)

| # | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|
| 1 | cpsc.gov recall notices | Government | One notice per recall | One recall at a time; no way to check a model against all of them |
| 2 | waterheaterrecall.com and similar | Single-recall microsites | One maker's program | Only that one recall |
| 3 | saferproducts.gov search | Government database | Raw search | Hard to use; mixes boilers and commercial units |
| 4 | Maker recall pages | Manufacturers | Their own recalls | Only their brand, often only current ones |
| 5 | News posts | Publishers | Recall news | Dated, one recall |

**Content type Google ranks:** government notices and single-recall pages. No result checks a model against every recall.

**Organic difficulty verdict: medium.** cpsc.gov owns each recall's own name; the generic "lookup by model" intent has no tool.

## What they all miss (our additions)
1. **One check against all 24 residential water heater recalls since 2001**, with model prefixes and serial ranges taken from each notice.
2. **Match levels** (match / possible / outside the range) and the brand's other recalls, with each notice's own remedy and contact.
3. **Optional recall alert email** when a new recall for your brand is added.
4. Links from the age calculator and brand pages, so a reader who just decoded the age can check the recall in one click.

## How we are different from generic calculator clones
Data comes verbatim from the CPSC SaferProducts API (`data/recalls.json`, `npm run fetch:recalls` fails on any changed field); the curation rule and cross-check cases are on /methodology.

## Quality check (2026-11-21)
| Source | URL | Claim on our page | Confirmed |
|---|---|---|---|
| CPSC SaferProducts Recall API | saferproducts.gov/RestWebServices/Recall?format=json | 24 recalls: dates, titles, units, hazards, remedies, contacts (verbatim) | fetched 2026-11-21; `fetch:recalls` 0 changed, 0 new |
| CPSC recall notice URLs | cpsc.gov/Recalls/... | link targets | URLs from the API; cpsc.gov HTML answers 403 to our checker, so the content check is against the API record |

**Uncertain / honest notes:** "No match is not proof of safety" is stated in the result. Models and serial ranges were transcribed from each notice's text; build:data fails if a listed model does not match its own recall.
