# SERP brief: /water-heater-expansion-tank-size/

- **Primary keyword:** expansion tank size calculator (water heater expansion tank size)
- **Secondary (H2s here):** what size expansion tank do I need, do I need an expansion tank, expansion tank pressure setting, expansion tank for 50 gallon water heater
- **Checked:** 2026-11-13

## Top 5 organic results

| # | URL | Who | Type | What it has | What it gets wrong or misses |
|---|---|---|---|---|---|
| 1 | watts.com/resources/planning/etp | Manufacturer | Calculator | Inputs: heater gal, initial/final pressure, temperatures; outputs acceptance and total volume | No formula shown; assumptions (90 °F start, final = 1.5 × initial) give larger tanks; no explanation for homeowners |
| 2 | supplyhouse.com ExpansionTankSizeCalculator | Retailer | Calculator | (403 to our fetcher) | Could not be opened |
| 3 | regulus.cz | Czech manufacturer | Calculator | Metric, heating systems | Not US potable water |
| 4 | diysmarthomehub.com | Blog | Calculator + matrix | Matrix by heater size and pressure | No method or source; matrix gives 4 gal for 50 gal at 80 psi where AMTROL's method gives 2 gal |
| 5 | ehow.com / university.hotwater.com | Publisher / manufacturer training | Article | Basic sizing | No calculator |

Also in the top 10: a generic `*.pages.dev` calculator clone (agentcalc), Watts non-potable calculator.

**Content type Google ranks:** tools (manufacturer and retailer calculators). A tool page fits.

**Organic difficulty verdict: medium.** Watts and SupplyHouse are strong, but nothing in the top 10 shows the method, the source or a cross-check.

## What they all miss (our additions)
1. **Published method** (AMTROL MC10007) with the user's numbers in "How we calculated this".
2. **Cross-check against all 45 cells** of AMTROL's quick chart: 37 same, 7 same as our safer pick, 1 explained.
3. **Quick table** by heater size and supply pressure at 120 °F.
4. **Tank acceptance volumes** (not just total volume).
5. **Closed-system explanation** from a state health department, precharge rule from AMTROL, PRV advice from A. O. Smith.
6. **Why Watts gives bigger tanks**, explained.

## How we are different from generic calculator clones
Named method, every default sourced, manufacturer cross-check on /methodology, honest "safer pick" rule disclosed as ours.

## Quality check (2026-11-13)
**Sources verified (quotes in `data/expansion-tanks.json` → references):**

| Source | URL | Claim | Confirmed |
|---|---|---|---|
| AMTROL MC10007 (03/19) | res.cloudinary.com/.../9017-112-03_19-MC10007-Sizing-TXT_cepv3g.pdf | Table 1 factors from 40 °F; DPF formula; precharge = line pressure; cold side after backflow preventer; worked example | quoted |
| AMTROL MC8520 (04/22) | res.cloudinary.com/.../MC8520-04_22-TXT-Sizing-Chart_pzeetd.pdf | ST-5 2.0/0.9, ST-12 4.4/3.2, ST-25V, ST-30V volumes; quick charts | quoted |
| Washington State DOH (2025) | doh.wa.gov/sites/default/files/2025-08/water_heater.pdf | T&P preset 125 or 150 psi; essential in closed systems | quoted |
| Watts ETP | watts.com/resources/planning/etp | 80 psi UPC note; final pressure +50%; 90 °F start | quoted |
| A. O. Smith gas manual | assets.hotwater.com/.../100400765.pdf | PRV 50–60 psi | quoted (data/troubleshooting.json) |
| Home Depot listings | data/computed → prices table | $34–$67 parts | listing quotes in data/installation-costs.json |

**Uncertain / honest notes:** the IRC P2903.4 text (codes.iccsafe.org) returned 403, so the page does not quote the code; it points readers to their building department instead.
