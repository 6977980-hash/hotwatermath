# SERP brief: /water-heater-failure-database/

- **Primary keyword:** water heater failure rate by brand (report later); page now = reader-submitted failure reports
- **Secondary:** most reliable water heater brand, why did my water heater fail, how old was your water heater when it failed
- **Checked:** 2026-11-21

## Top results (US search, 2026-11-21)
Opinion lists ("best brands"), Reddit threads and plumber blogs. No independent data set of failures by brand and age exists in the results.

**Organic difficulty verdict: medium–hard** for "most reliable brand" (big publishers); easy for the data angle once the report exists.

## What we add
1. An open form (brand, type, install and fail year, cause, outcome) with no email required.
2. A public report only after 100 submissions and at least 10 per brand (rule in `data/failure-database.json`), so no brand is judged on a handful of reports.
3. Ties to the age calculator and lifespans data.

## Quality check (2026-11-21)
No outside figures are claimed on the page; lifespan numbers render from `data/lifespans.json` (sourced earlier). The page stays **draft/noindex** until the report exists, because a form alone is thin content. Submissions are stored in `wp_hwm_failures`; no personal data is asked for.
