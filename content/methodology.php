<?php
/**
 * /methodology: every formula, every data source, the cross-checks, update cadence and limitations.
 * All numbers are read from data/*.json and data/computed/*.json (CLAUDE.md §5); the math itself
 * lives only in assets/js/formulas.js. Tool sections use the tool slug as their id so each tool's
 * "How we calculated this" panel can link to /methodology/#<slug>.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_email = (string) hwm_site( 'contact.email' );
$hwm_ref   = 'computed/reference-tables';
/** Physics constant from formulas.PHYSICS (copied into computed data by npm run build:data). */
$hwm_k = static fn( string $key, int $dec = 0 ): string => hwm_value( $hwm_ref, 'physics.' . $key, $dec );
/** Link to a page in the registry by slug, with its registry title as the anchor unless one is given. */
$hwm_link = static function ( string $slug, string $anchor = '' ): string {
	$entry = hwm_page( $slug );
	$text  = '' !== $anchor ? $anchor : (string) ( $entry['title'] ?? $slug );
	return '<a href="' . esc_url( hwm_url( $slug ) ) . '">' . esc_html( $text ) . '</a>';
};
/** "Not yet run" row for a tool's manufacturer cross-check table. */
$hwm_xcheck = static function ( string $what ): void {
	?>
	<div class="table-wrap" role="region" aria-label="Manufacturer cross-check: <?php echo esc_attr( $what ); ?>" tabindex="0">
	<table>
		<caption>Manufacturer cross-check: <?php echo esc_html( $what ); ?></caption>
		<thead><tr><th scope="col">Test case (inputs)</th><th scope="col">Our result</th><th scope="col">Manufacturer guide result</th><th scope="col">Difference and why</th><th scope="col">Manufacturer source</th></tr></thead>
		<tbody><tr><td colspan="5">Not run yet. At least three test cases are run and published here, with links, when this calculator is built. Until then the calculator is not public.</td></tr></tbody>
	</table>
</div>
	<?php
};
/** Filled manufacturer cross-check table from data/computed/<tool>.json → crossChecks. */
$hwm_xfilled = static function ( string $tool, string $what ): void {
	$rows = (array) hwm_raw( 'computed/' . $tool, 'crossChecks' );
	?>
	<div class="table-wrap" role="region" aria-label="Manufacturer cross-check: <?php echo esc_attr( $what ); ?>" tabindex="0">
	<table>
		<caption>Manufacturer cross-check: <?php echo esc_html( $what ); ?> (run <?php echo esc_html( hwm_date( (string) hwm_site( 'dateVerified.' . $tool ) ) ); ?>)</caption>
		<thead><tr><th scope="col">Test case (inputs)</th><th scope="col">Our result</th><th scope="col">Manufacturer or official result</th><th scope="col">Difference and why</th><th scope="col">Source</th></tr></thead>
		<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( (string) $row['label'] ); ?></th>
					<td><?php echo esc_html( (string) $row['ours'] ); ?></td>
					<td><?php echo esc_html( (string) $row['published'] ); ?></td>
					<td><?php echo isset( $row['diffPct'] ) ? esc_html( number_format( (float) $row['diffPct'], 1 ) . '%. ' ) : ( ! empty( $row['match'] ) ? 'Match. ' : 'Mismatch. ' ); ?><?php echo esc_html( (string) $row['why'] ); ?></td>
					<td><a href="<?php echo esc_url( (string) $row['sourceUrl'] ); ?>" rel="noopener"><?php echo esc_html( (string) $row['source'] ); ?></a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
	<?php
};
$hwm_gw   = 'groundwater-temp-by-state';
$hwm_life = 'lifespans';
$hwm_dr   = 'decision-rules';
?>
<h2>What this page covers</h2>
<p>This page shows the math and the data behind every Hot Water Math calculator. For each tool you get the formula in plain words, where each default number comes from, and how our results compare with published figures and manufacturer sizing guides. If a number on any page of this site cannot be traced back to a source listed here, that is a mistake and we want to hear about it.</p>
<dl>
	<dt>Formula</dt>
	<dd>The exact calculation the tool runs. The same code runs in your browser and builds every table on our pages, so a page cannot disagree with its own calculator.</dd>
	<dt>Default (assumed value)</dt>
	<dd>A number the calculator uses until you enter your own, such as your incoming water temperature or your electricity price. Every default is labeled as assumed in the tool and can be changed.</dd>
	<dt>Cross-check</dt>
	<dd>A test where we feed our formula the same inputs as a published example and compare the answers. We publish differences instead of hiding them.</dd>
	<dt>Editorial assumption</dt>
	<dd>A rule we chose ourselves because no agency publishes one. We mark each one as ours and explain the reasoning.</dd>
</dl>

<nav class="toc" aria-labelledby="toc-title">
	<h2 id="toc-title" class="toc-title">On this page</h2>
	<ol>
		<li><a href="#constants">Which physical constants do the calculators use?</a></li>
		<li><a href="#cross-checks">Do our formulas match published examples?</a></li>
		<li><a href="#calculators">How each calculator works</a>
			<ol>
				<li><a href="#tankless-water-heater-size-calculator">Tankless water heater size</a></li>
				<li><a href="#water-heater-size-calculator">Tank water heater size</a></li>
				<li><a href="#water-heater-age-calculator">Water heater age</a></li>
				<li><a href="#repair-or-replace-water-heater">Repair or replace</a></li>
				<li><a href="#water-heater-cost-calculator">Water heater cost</a></li>
				<li><a href="#gas-vs-electric-water-heater">Gas vs electric operating cost</a></li>
				<li><a href="#tankless-vs-tank-water-heater">Tankless vs tank cost of ownership</a></li>
				<li><a href="#heat-pump-water-heater-cost">Heat pump water heater savings and payback</a></li>
				<li><a href="#water-heater-expansion-tank-size">Thermal expansion tank size</a></li>
				<li><a href="#hot-water-runs-out-fast">Winter vs summer shower table (hot water runs out fast)</a></li>
				<li><a href="#safety-pages">Safety pages: tank setting, relief valve, heating element, gas line and electrical tables</a></li>
				<li><a href="#state-cost-pages">State cost pages (installation cost by state)</a></li>
				<li><a href="#cost-index">Water heater cost index by state</a></li>
				<li><a href="#solar-water-heater-cost">Solar water heater cost and payback</a></li>
			</ol>
		</li>
		<li><a href="#data-sources">Where does our data come from?</a></li>
		<li><a href="#assumptions">Which numbers are our own assumptions?</a></li>
		<li><a href="#review">How often do we review the numbers?</a></li>
		<li><a href="#limits">What are the limits of these calculators?</a></li>
		<li><a href="#report-a-mistake">How to report a mistake</a></li>
	</ol>
</nav>

<h2 id="constants">Which physical constants do the calculators use?</h2>
<p>Every water heater calculation starts from the energy needed to warm water. One British thermal unit (BTU) raises one pound of water by 1 °F, and one gallon of water weighs <?php echo esc_html( $hwm_k( 'lbPerGallon', 2 ) ); ?> pounds. The other constants below are unit definitions.</p>
<div class="table-wrap" role="region" aria-label="Constants used in every formula" tabindex="0">
<table>
	<caption>Constants used in every formula</caption>
	<thead><tr><th scope="col">Constant</th><th scope="col">Value</th><th scope="col">Where it comes from</th></tr></thead>
	<tbody>
		<tr><td>Weight of one US gallon of water</td><td><?php echo esc_html( $hwm_k( 'lbPerGallon', 2 ) ); ?> lb</td><td>Standard value used in DOE and manufacturer sizing formulas (water near room temperature)</td></tr>
		<tr><td>Energy in one kilowatt-hour</td><td><?php echo esc_html( $hwm_k( 'btuPerKwh' ) ); ?> BTU</td><td>Unit conversion, rounded to a whole BTU</td></tr>
		<tr><td>Energy in one therm of natural gas</td><td><?php echo esc_html( $hwm_k( 'btuPerTherm' ) ); ?> BTU</td><td>Definition of the therm</td></tr>
		<tr><td>Cubic feet in one Mcf</td><td><?php echo esc_html( $hwm_k( 'cubicFeetPerMcf' ) ); ?></td><td>Definition (EIA reports gas prices per thousand cubic feet)</td></tr>
		<tr><td>Watts in one kilowatt</td><td><?php echo esc_html( $hwm_k( 'wattsPerKw' ) ); ?></td><td>Definition</td></tr>
	</tbody>
</table>
</div>

<h2 id="cross-checks">Do our formulas match published examples?</h2>
<p>Yes, closely. Before any calculator is built we test the shared formulas against worked examples published by the Department of Energy, AHRI and a manufacturer spec sheet. The table shows our answer next to the published one. Small differences come from rounding in the published figures.</p>
<?php echo hwm_table( 'computed/cross-checks', 'formulaChecks' ); // phpcs:ignore -- escaped in the part. ?>

<h2 id="calculators">How each calculator works</h2>
<p>Each section below covers one tool: its formula, the data it uses, its limits, and a table for the manufacturer cross-check. The cross-check is run with at least three test cases from manufacturer sizing literature when the tool is built, and again whenever its formula or data change.</p>
<p><strong>Input checks.</strong> Every calculator reads numbers the same way: a dollar sign, spaces and thousands commas are ignored, so "$1,200" reads as 1200. Each field has a plausible range written in one data file (for example <?php echo esc_html( hwm_value( 'input-limits', 'tools.tankless-water-heater-size-calculator.inlet.min' ) ); ?> to <?php echo esc_html( hwm_value( 'input-limits', 'tools.tankless-water-heater-size-calculator.inlet.max' ) ); ?> °F for incoming water, or <?php echo esc_html( hwm_value( 'input-limits', 'tools.water-heater-expansion-tank-size.supply.min' ) ); ?> to <?php echo esc_html( hwm_value( 'input-limits', 'tools.water-heater-expansion-tank-size.supply.max' ) ); ?> psi for supply pressure, the usual relief valve setting). A value outside its range, or text that is not a number, is marked on the field and the result asks you to fix it; we never swap in a default behind your back. An empty optional field uses the default shown next to it.</p>

<section id="tankless-water-heater-size-calculator">
	<h3>Tankless water heater size</h3>
	<p>A tankless water heater is sized by two numbers: the flow of hot water you use at the same time, in gallons per minute (GPM), and how many degrees the heater must warm the incoming water. The heater's output must cover both at once.</p>
	<ol>
		<li><strong>Peak flow at the fixtures</strong> = the sum of the flow rates of the fixtures you run together.</li>
		<li><strong>Hot water flow</strong> = each fixture's flow × its hot share, added up. A shower or bathroom sink is used at about <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.shower.useTempF' ) ); ?> °F, so its hot share = (<?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.shower.useTempF' ) ); ?> − incoming) ÷ (setpoint − incoming); a bathtub at <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.tubFill.useTempF' ) ); ?> °F. The kitchen faucet, dishwasher and clothes washer are counted at full flow to the setpoint, because they can take hot water straight from the heater. Rinnai's sizing charts work the same way (a <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.shower.useTempF' ) ); ?> °F shower mix from a <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F heater); Noritz's printable guide heats the full fixture flow to the setpoint, which picks larger units. The cross-check below shows both.</li>
		<li><strong>Temperature rise</strong> = your setpoint minus your incoming water temperature. The default setpoint is <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F, the DOE FEMP recommendation for most homes.</li>
		<li><strong>Heat output (BTU per hour)</strong> = GPM × <?php echo esc_html( $hwm_k( 'lbPerGallon', 2 ) ); ?> lb per gallon × <?php echo esc_html( $hwm_k( 'minutesPerHour' ) ); ?> minutes × temperature rise.</li>
		<li><strong>Gas input (BTU per hour)</strong> = heat output ÷ efficiency (UEF). The default UEF for a gas tankless unit is <?php echo esc_html( hwm_value( 'computed/uef-defaults', 'types.gas_tankless.typical', 2 ) ); ?>, the median of the ENERGY STAR certified list.</li>
		<li><strong>Electric power (kW)</strong> = heat output ÷ <?php echo esc_html( $hwm_k( 'btuPerKwh' ) ); ?>. <strong>Current (amps)</strong> = kW × <?php echo esc_html( $hwm_k( 'wattsPerKw' ) ); ?> ÷ volts, at <?php echo esc_html( hwm_value( 'electrical', 'residentialVolts' ) ); ?> V by default.</li>
	</ol>
	<p>Fixture flow rates are the federal maximums for fixtures sold today, with EPA WaterSense values for low-flow fixtures. Appliance and tub fill rates are the midpoint of the ranges in the PNNL Building America Solution Center table.</p>
	<?php echo hwm_table( $hwm_ref, 'fixtures' ); // phpcs:ignore ?>
	<p><strong>Incoming water temperature by state.</strong> No agency measures tap water temperature state by state, so we estimate it from NOAA air temperature normals (1991–2020) with the mains water correlation used in DOE's EnergyPlus building software (Christensen and Burch, described by Hendron and others, 2004):</p>
	<ul>
		<li>Yearly average water temperature = yearly average air temperature + <?php echo esc_html( hwm_value( $hwm_gw, 'correlation.offsetF' ) ); ?> °F.</li>
		<li>Coldest month = yearly average − ratio × (the gap between the warmest and coldest monthly air averages ÷ 2), where ratio = <?php echo esc_html( hwm_value( $hwm_gw, 'correlation.ratioBase', 2 ) ); ?> + <?php echo esc_html( hwm_value( $hwm_gw, 'correlation.ratioSlope', 2 ) ); ?> × (yearly average air temperature − <?php echo esc_html( hwm_value( $hwm_gw, 'correlation.refTempF' ) ); ?> °F).</li>
	</ul>
	<p>Tankless sizing uses the coldest month, because a heater sized for the yearly average runs out of capacity in winter. We use statewide air temperatures except for Alaska (Anchorage), Hawaii (Honolulu) and Washington, DC (Reagan National Airport), where NOAA has no statewide series or the statewide figure is dominated by places where almost nobody lives. The calculators' state lists name the city for those three. We rechecked Honolulu against NOAA on 2026-11-14: our monthly air normals match NOAA's 1991–2020 series (coldest month <?php echo esc_html( hwm_value( 'groundwater-temp-by-state', 'states.HI.monthlyNormalsF.0' ) ); ?> °F). The correlation then puts Honolulu's coldest-month incoming water at <?php echo esc_html( hwm_value( 'computed/inlet-temps', 'states.HI.coldestMonth' ) ); ?> °F, warmer than the air, because it was fitted to mainland climates; in Hawaii, enter your own incoming temperature if you know it.</p>
	<details>
		<summary>Estimated incoming water temperature for all 50 states and DC</summary>
		<?php echo hwm_table( 'computed/inlet-temps', 'inletTemps' ); // phpcs:ignore ?>
	</details>
	<p>For electric tankless units, the tool also shows the wiring a manufacturer lists for units of that size, as a reference to discuss with your electrician, not as a design.</p>
	<?php echo hwm_table( $hwm_ref, 'electricReference' ); // phpcs:ignore ?>
	<p><strong>Your own shower flow.</strong> You can type the rating stamped on your shower head (<?php echo esc_html( hwm_value( 'fixture-flow-rates', 'customShower.minGpm', 1 ) ); ?> to <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'customShower.maxGpm', 1 ) ); ?> GPM, for rain heads or two heads). EPA WaterSense: standard heads use <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.shower.gpm', 1 ) ); ?> GPM and WaterSense heads no more than <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.shower.lowFlowGpm', 1 ) ); ?>. A large soaking tub filler is counted at <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.soakingTub.gpm', 1 ) ); ?> GPM, the top of the PNNL tub fill range.</p>
	<p><strong>Point of use.</strong> In point-of-use mode the tool sizes one small electric unit for one tap or shower: the unit heats that fixture's whole flow from the incoming temperature straight to its use temperature, so kW = GPM × rise × <?php echo esc_html( $hwm_k( 'lbPerGallon', 2 ) ); ?> × <?php echo esc_html( $hwm_k( 'minutesPerHour' ) ); ?> ÷ <?php echo esc_html( $hwm_k( 'btuPerKwh' ) ); ?>, which is the same as Eemax's sizing guide rule (<?php echo esc_html( (string) hwm_raw( 'tankless-reference-units', 'pointOfUse.sizingCheck.formula' ) ); ?>). It then names the smallest unit in our list (Eemax ProAdvantage spec sheet, then the whole-house Rheem units above) with at least that power, with its volts and amps.</p>
	<p><strong>Limitations.</strong> Use temperatures are typical values, not your habits: a hotter shower draws more hot water. Real units lose some output at high altitude and with long pipe runs, and some models have a minimum flow below which they do not fire.</p>
	<p>The cross-check below runs our formula on published manufacturer figures: the flow Rinnai lists for three units at two temperature rises, the two worked examples in Noritz's sizing guide, and Rheem's electric temperature rise table. The tool's size chart uses gas input sizes from the Rinnai chart.</p>
	<?php $hwm_xfilled( 'tankless-water-heater-size-calculator', 'tankless sizing (Rinnai, Noritz, Rheem)' ); ?>
</section>

<section id="water-heater-size-calculator">
	<h3>Tank water heater size</h3>
	<p>A storage tank water heater is sized by its first hour rating (FHR): how many gallons of hot water it can deliver in one busy hour, starting with a full tank. The FHR is printed on the EnergyGuide label and in spec sheets, and it matters more than the tank's gallon size.</p>
	<ol>
		<li><strong>Peak-hour demand</strong> = the number of times each hot water use happens in your busiest hour × the hot water per use, added up. The per-use values come from the AHRI peak-hour worksheet.</li>
		<li><strong>Recommended size</strong> = the smallest tank whose typical FHR meets the peak-hour demand. If no size meets it at the typical FHR, we show the size where some models still meet it, and say so.</li>
	</ol>
	<?php echo hwm_table( $hwm_ref, 'peakHour' ); // phpcs:ignore ?>
	<p>Typical FHR by size comes from real models: manufacturer spec sheets for gas and electric tanks, and the ENERGY STAR certified list for heat pump water heaters. The table shows the median and range of the models we found for each size.</p>
	<?php echo hwm_table( 'computed/fhr-table', 'fhr' ); // phpcs:ignore ?>
	<p><strong>Your shower head and a big tub.</strong> The AHRI shower figure assumes a standard head of <?php echo esc_html( hwm_value( 'peak-hour-demand', 'showerFlow.baseGpm', 1 ) ); ?> GPM (our assumption about the worksheet); if you type your head's flow, shower gallons scale with it. A large soaking tub is added as its gallons × the hot share at <?php echo esc_html( hwm_value( 'peak-hour-demand', 'largeTub.useTempF' ) ); ?> °F, up to <?php echo esc_html( hwm_value( 'peak-hour-demand', 'largeTub.maxGallons' ) ); ?> gallons (Rheem).</p>
	<p><strong>Limitations.</strong> Your busiest hour is a guess you make about your own habits. FHR varies between models of the same size, so check the label of the unit you plan to buy. Federal efficiency rules effectively require large electric storage tanks to be heat pump models, which is why the electric rows stop at <?php echo esc_html( hwm_value( 'uef-ranges', 'federalMinimums.electricResistanceMaxGallons' ) ); ?> gallons.</p>
	<p><strong>Household presets.</strong> Picking a household size fills in a typical busiest hour. These presets are our assumption, set so that the peak-hour demand falls inside American Water Heater's first hour rating range for the same family size; you can change every count. An optional <?php echo esc_html( hwm_value( 'computed/water-heater-size-calculator', 'defaultCase.marginPct' ) ); ?>% margin, also our assumption, is offered for homes that often run several uses at once.</p>
	<p>The cross-check below compares our results with three manufacturers' household guides (American's first hour rating ranges, Rheem's gas tank sizes, A. O. Smith's tank volumes) and with Rheem's worked example. Because the presets were set to land inside American's ranges, an American match is not counted as confirmation: only the Rheem and A. O. Smith rows are independent checks, and the table marks the American rows as consistency checks. The tank sizes behind each result come from a small spec sheet sample (one maker's gas and electric lines, several brands for heat pumps), so the result says how many models it rests on.</p>
	<?php $hwm_xfilled( 'water-heater-size-calculator', 'tank sizing (American, Rheem, A. O. Smith sizing guides)' ); ?>
</section>

<section id="water-heater-age-calculator">
	<h3>Water heater age</h3>
	<p>Most manufacturers put the build date into the serial number. The tool matches your serial number against each brand's published date code, reads the year and month (or week), and works out the age from today's date.</p>
	<ol>
		<li><strong>Build date</strong> = decoded from the serial number pattern for your brand. Letter-coded years repeat on a fixed cycle, so we pick the most recent year that is not in the future and show the earlier possibilities too.</li>
		<li><strong>Plausibility.</strong> A decoded date in the future, or more than <?php echo esc_html( hwm_value( 'serial-formats', 'plausibility.maxAgeYears' ) ); ?> years ago, is treated as a misread serial, never as a real age; the same limit applies to a build year you type. A. O. Smith family serials made since late 2016 are all digits (year and week, then nine digits), as A. O. Smith's own notice on the change shows.</li>
		<li><strong>Age</strong> = today's date minus the build date. When a code gives only the year, we count from the middle of that year.</li>
		<li><strong>Expected life</strong> = the typical life for the type of heater, adjusted for your water and maintenance (see editorial assumptions below; the "flushed yearly" box starts unticked, because most tanks are not flushed), never below <?php echo esc_html( hwm_value( $hwm_life, 'adjustments.minimumLifeYears' ) ); ?> years.</li>
		<li><strong>Status</strong> = green below <?php echo esc_html( hwm_value( $hwm_life, 'statusBands.amberFrom', 1 ) ); ?> × the expected life, amber from there, red from <?php echo esc_html( hwm_value( $hwm_life, 'statusBands.redFrom', 1 ) ); ?> × the expected life.</li>
	</ol>
	<?php echo hwm_table( $hwm_ref, 'serialFormats' ); // phpcs:ignore ?>
	<?php echo hwm_table( $hwm_ref, 'lifespans' ); // phpcs:ignore ?>
	<p>Lifespans come from DOE's 2024 efficiency rules, which estimate how long water heaters last from national housing surveys. DOE puts the average storage tank at about <?php echo esc_html( hwm_value( $hwm_life, 'types.gas_tank.high' ) ); ?> years; its earlier rule, ENERGY STAR and DOE FEMP use <?php echo esc_html( hwm_value( $hwm_life, 'types.gas_tank.typical' ) ); ?>. We use <?php echo esc_html( hwm_value( $hwm_life, 'types.gas_tank.typical' ) ); ?> as typical with a <?php echo esc_html( hwm_value( $hwm_life, 'types.gas_tank.low' ) ); ?> to <?php echo esc_html( hwm_value( $hwm_life, 'types.gas_tank.high' ) ); ?> year range. For tankless units DOE gives an average of about <?php echo esc_html( hwm_value( $hwm_life, 'types.gas_tankless.typical' ) ); ?> years and no range, so we show that figure without inventing one.</p>
	<p>Some brands are decoded with another maker's code, following the Energy Trust of Oregon decoder: Whirlpool with the American code, Kenmore with the State code and GE with the Rheem code, each with a warning in the result. Rinnai, Noritz, Takagi and Bosch are not decoded, because guides online disagree about their codes and we could not open a manufacturer or government source that confirms one; for these the calculator asks for the year.</p>
	<p><strong>Limitations.</strong> We only decode brands whose date code we could confirm in a published source. Brands not listed yet are added once we find a source we can open and check. A heater can also sit in a warehouse for months before it is installed, so the install date may be later than the build date.</p>
	<p>The cross-check below decodes test serials with our rules and compares the date with the manufacturer's own answer or a government record: Rheem/Ruud's official age lookup (queried with test serials on the check date), Bradford White's published letter table, U.S. CPSC recall notices that print serial ranges next to production or sales dates (Navien, A. O. Smith, State, Bradford White), and the Energy Trust of Oregon decoder. Older Rheem tag layouts and the pre-2004 State layout come from third-party decoders (InspectApedia, Building Intelligence Center); the brand pages label them as such. The same cases are listed on each brand page.</p>
	<?php $hwm_xfilled( 'water-heater-age-calculator', 'serial number decoding (Rheem/Ruud lookup, Bradford White table, CPSC recall notices, Energy Trust)' ); ?>
</section>

<section id="repair-or-replace-water-heater">
	<h3>Repair or replace</h3>
	<p>No government agency publishes a repair-or-replace rule for water heaters, so this tool uses rules we chose and state openly. You can judge them for yourself:</p>
	<ol>
		<li>A leaking tank is always a replacement, because the tank itself cannot be repaired. So is a heater that was flooded, as A. O. Smith's manual requires.</li>
		<li>Replace when the repair costs more than <?php echo esc_html( hwm_value( $hwm_dr, 'repairOrReplace.repairShareAlways', 2 ) ); ?> × the price of a new installed unit.</li>
		<li>Replace when the heater has used at least <?php echo esc_html( hwm_value( 'lifespans', 'statusBands.oldFrom', 2 ) ); ?> × its expected life and the repair costs more than <?php echo esc_html( hwm_value( $hwm_dr, 'repairOrReplace.repairShareWhenOld', 2 ) ); ?> × the price of a new one.</li>
		<li>A heater at or past the top of its type's life range (for example <?php echo esc_html( hwm_value( $hwm_life, 'types.gas_tank.high' ) ); ?> years for a tank) is shown as "replace soon", whatever the quote.</li>
		<li>An old heater with a cheap repair is shown as borderline. Anything else is a repair.</li>
		<li>Rusty hot water at any age, or rumbling and popping once the heater is old, are signs Rheem lists for replacing a heater; ticking one moves the answer one step toward replacing (repair to borderline, borderline to replace).</li>
	</ol>
	<p><strong>Inputs.</strong> Typical life comes from DOE (see the age calculator above). The new-unit price defaults to the typical installed price of a like-for-like replacement at U.S. average costs, from the cost calculator's model below; type a real quote to replace it. The running-cost comparison uses DOE's typical household use (<?php echo esc_html( hwm_value( 'hot-water-use', 'typicalHouseholdGallonsPerDay.value' ) ); ?> gallons a day) heated <?php echo esc_html( hwm_value( 'computed/repair-or-replace-water-heater', 'client.use.riseF' ) ); ?> °F, from the <?php echo esc_html( hwm_value( 'computed/repair-or-replace-water-heater', 'client.use.inletF' ) ); ?> °F U.S. average incoming water to the <?php echo esc_html( hwm_value( 'computed/repair-or-replace-water-heater', 'client.use.setpointF' ) ); ?> °F setting: the same rise the gas vs electric calculator uses, so both tools give the same yearly cost, the median UEF of each type and U.S. average EIA prices: yearly cost = gallons × 365 × <?php echo esc_html( $hwm_k( 'lbPerGallon', 2 ) ); ?> × rise ÷ UEF, converted to kWh or therms and multiplied by the price.</p>
	<?php echo hwm_table( 'computed/repair-or-replace-water-heater', 'limits' ); // phpcs:ignore ?>
	<p><strong>Limitations.</strong> The thresholds are judgment calls, not measured data. Safety problems, such as a relief valve that keeps discharging, override any cost rule. A fuel switch or a hard install costs more than the like-for-like price, so a real quote gives a fairer answer.</p>
	<p>The cross-check below runs our rules on cases that Rheem's repair-or-replace guide and A. O. Smith's manual answer directly. One case differs on purpose, and the table says why.</p>
	<?php $hwm_xfilled( 'repair-or-replace-water-heater', 'repair or replace (Rheem guide, A. O. Smith manual)' ); ?>
</section>

<section id="water-heater-cost-calculator">
	<h3>Water heater cost</h3>
	<p>Installed cost = the price of the unit + installation labor + the extras your job needs, such as a permit, haul-away or an expansion tank. Labor and extras are multiplied by a regional price factor for your state. The unit price is not, because heaters are sold at national prices.</p>
	<ul>
		<li><strong>Unit prices</strong> come from retail listings we checked by hand. The table shows the median and range for each size class.</li>
		<li><strong>Regional factor</strong> = your state's Regional Price Parity from the U.S. Bureau of Economic Analysis ÷ 100. A factor above 1 means prices in your state are above the U.S. average. RPPs measure overall consumer prices, so they stand in for local labor costs; they are not a plumber wage survey.</li>
		<li><strong>Labor and extras</strong> are our own low, typical and high ranges, written down in a data file with the reference each one leans on. No public source publishes plumber labor by job type: DOE builds its installation costs from RSMeans, a paid database, and publishes only averages. Where DOE gives a figure we use it as the typical value, for example $<?php echo esc_html( hwm_value( 'installation-costs', 'extras.permit.typical', 0 ) ); ?> for permit, removal and disposal; where it gives a range, such as $<?php echo esc_html( hwm_value( 'installation-costs', 'extras.panel.low', 0 ) ); ?> to $<?php echo esc_html( hwm_value( 'installation-costs', 'extras.panel.high', 0 ) ); ?> for a panel upgrade, low and high come from it. Industry cost guides are used only as a sanity check.</li>
		<li><strong>The job</strong> (like-for-like, fuel switch, tank to tankless) ticks the extras such a job usually needs, for example venting and a larger gas line for a gas tank to tankless switch. You can change every tick.</li>
	</ul>
	<p>Low = cheapest listing + the low end of every installation line; high = dearest listing + the high end. The same model prices the tankless vs tank comparison and the installed replacement price in the repair-or-replace tool, so the three pages agree.</p>
	<?php echo hwm_table( 'computed/water-heater-cost-calculator', 'labor' ); // phpcs:ignore ?>
	<?php echo hwm_table( 'computed/water-heater-cost-calculator', 'extras' ); // phpcs:ignore ?>
	<?php echo hwm_table( 'computed/unit-prices', 'unitPrices' ); // phpcs:ignore ?>
	<details>
		<summary>Regional price factor for all 50 states and DC</summary>
		<?php echo hwm_table( 'computed/regional-multipliers', 'regional' ); // phpcs:ignore ?>
	</details>
	<p><strong>Limitations.</strong> Listing prices from one retailer on one day are a snapshot, so we recheck them every three months. Labor ranges are our estimate, and DOE's anchors are in 2022 or 2023 dollars. A quote from an installer who has seen your home will always be more precise than any average.</p>
	<p>No manufacturer publishes installed prices, so the cross-check compares our like-for-like totals with the average installed costs in DOE's 2024 rules, and with one industry average as a sanity check. Our totals are higher than DOE's for tanks; the table says why.</p>
	<?php $hwm_xfilled( 'water-heater-cost-calculator', 'installed cost (DOE 2024 rules)' ); ?>
</section>

<section id="gas-vs-electric-water-heater">
	<h3>Gas vs electric operating cost</h3>
	<p>Yearly running cost comes from the heat your hot water needs, the heater's efficiency and your energy price.</p>
	<ol>
		<li><strong>Heat needed per year (BTU)</strong> = gallons per day × <?php echo esc_html( $hwm_k( 'daysPerYear' ) ); ?> days × <?php echo esc_html( $hwm_k( 'lbPerGallon', 2 ) ); ?> lb per gallon × temperature rise.</li>
		<li><strong>Energy used</strong> = heat needed ÷ UEF, then ÷ <?php echo esc_html( $hwm_k( 'btuPerTherm' ) ); ?> for therms of gas or ÷ <?php echo esc_html( $hwm_k( 'btuPerKwh' ) ); ?> for kWh of electricity.</li>
		<li><strong>Yearly cost</strong> = energy used × your price per therm or per kWh.</li>
	</ol>
	<p>The default hot water use is <?php echo esc_html( hwm_value( 'hot-water-use', 'typicalHouseholdGallonsPerDay.value' ) ); ?> gallons a day, which DOE FEMP calls typical household use. It is also the medium draw pattern in DOE's test procedure. With no state picked, the incoming water is <?php echo esc_html( hwm_value( 'hot-water-use', 'testConditions.inletF' ) ); ?> °F, the supply temperature in DOE's efficiency test, heated to your setting (<?php echo esc_html( hwm_value( 'computed/repair-or-replace-water-heater', 'client.use.setpointF' ) ); ?> °F by default), a <?php echo esc_html( hwm_value( 'computed/repair-or-replace-water-heater', 'client.use.riseF' ) ); ?> °F rise. We use the setting, not DOE's <?php echo esc_html( hwm_value( 'hot-water-use', 'testConditions.outletF' ) ); ?> °F test outlet, because most homes run at the lower setting; the repair or replace tool uses the same rise. Pick your state and the tool uses its incoming water temperature instead.</p>
	<?php echo hwm_table( $hwm_ref, 'drawPatterns' ); // phpcs:ignore ?>
	<p>Default efficiency by type is the median of real models: manufacturer spec sheets for standard tanks and the ENERGY STAR certified list for high-efficiency gas, heat pump and gas tankless units. Electric tankless units are not ENERGY STAR certified, so their default stays close to the federal minimum.</p>
	<?php echo hwm_table( 'computed/uef-defaults', 'uef' ); // phpcs:ignore ?>
	<p>Energy prices are the latest yearly residential averages from the U.S. Energy Information Administration: <?php echo esc_html( hwm_value( 'computed/energy-prices', 'us.usdPerKwh', 4 ) ); ?> dollars per kWh and <?php echo esc_html( hwm_value( 'computed/energy-prices', 'us.usdPerTherm', 3 ) ); ?> dollars per therm for the U.S. as a whole. EIA reports gas per thousand cubic feet, so we convert with each state's heat content of delivered gas: price per therm = price per Mcf ÷ (BTU per cubic foot × <?php echo esc_html( $hwm_k( 'cubicFeetPerMcf' ) ); ?> ÷ <?php echo esc_html( $hwm_k( 'btuPerTherm' ) ); ?>).</p>
	<details>
		<summary>Electricity and natural gas prices for all 50 states and DC</summary>
		<?php echo hwm_table( 'computed/energy-prices', 'energyPrices' ); // phpcs:ignore ?>
	</details>
	<p><strong>Household size.</strong> When you pick a number of people, daily use = people × <?php echo esc_html( hwm_value( 'hot-water-use', 'perPersonGallonsPerDay.value' ) ); ?> gallons (DOE Buildings Energy Data Book); otherwise the typical day above. <strong>Incoming water</strong> is your state's year-average estimate (the U.S. average uses DOE's <?php echo esc_html( hwm_value( 'hot-water-use', 'testConditions.inletF' ) ); ?> °F test temperature), and the setpoint defaults to <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F.</p>
	<p><strong>Limitations.</strong> Your bill's real rate includes fixed charges and time-of-use pricing that a yearly average hides; enter your own rate when you know it. Heat pump efficiency drops in a cold room, and the UEF test does not capture that.</p>
	<p><strong>Propane.</strong> Pick propane and the gas heaters burn propane at the same UEF: gallons a year = heat needed ÷ UEF ÷ <?php echo esc_html( hwm_value( 'propane-prices', 'btuPerGallon.value', 0 ) ); ?> BTU per gallon (EIA), priced at $<?php echo esc_html( hwm_value( 'propane-prices', 'us.usdPerGallon', 3 ) ); ?> a gallon, EIA's U.S. residential average for <?php echo esc_html( (string) hwm_raw( 'propane-prices', 'us.period' ) ); ?> before taxes. Type your own price; propane varies a lot by region and season.</p>
	<p>The cross-check feeds each federal EnergyGuide label's own UEF, test-day use and price into our formula and compares the energy use with the label, ENERGY STAR or DOE FEMP figure.</p>
	<?php $hwm_xfilled( 'gas-vs-electric-water-heater', 'operating cost (EnergyGuide labels, ENERGY STAR, DOE FEMP)' ); ?>
</section>

<section id="tankless-vs-tank-water-heater">
	<h3>Tankless vs tank cost of ownership</h3>
	<p>Cost of ownership adds up what each option costs you over the same number of years:</p>
	<ol>
		<li><strong>Start</strong> = installed cost − any tax credit that applies.</li>
		<li><strong>Each year</strong> add that option's yearly running cost: energy from the gas vs electric formula above, plus upkeep. Tankless upkeep is a yearly vinegar descaling flush (Rinnai: at least once a year, with undiluted white vinegar; $<?php echo esc_html( hwm_value( 'installation-costs', 'maintenance.tankless.perTimeUsd', 2 ) ); ?> a case) plus a $<?php echo esc_html( hwm_value( 'installation-costs', 'maintenance.tankless.onceUsd', 2 ) ); ?> flush kit spread over the unit's life. Tank upkeep is an anode rod every <?php echo esc_html( hwm_value( 'installation-costs', 'maintenance.tank.everyYears' ) ); ?> years (the long end of Rheem's advice; $<?php echo esc_html( hwm_value( 'installation-costs', 'maintenance.tank.partUsd', 2 ) ); ?>, the median Home Depot listing). Both are priced as do-it-yourself work.</li>
		<li>When an option reaches the end of its expected life inside the period, add its installed cost again for a replacement.</li>
		<li><strong>Value left</strong> = the installed price × the share of its life still ahead at the end of the period, subtracted from the total. Without it, a tank replaced near the end of the period would carry a full new heater's price for only a year or two of use.</li>
		<li><strong>Simple payback</strong> = the extra up-front cost ÷ the yearly saving in running cost, in years, with no interest. The "pays back" year in our tables is that number rounded up, shown only when it falls inside the period.</li>
	</ol>
	<p>The two answers can differ. A gas tankless unit lasts longer than a tank, so it can come out ahead on total cost once its value left is counted while its simple payback is still longer than the period. The result says when that happens and shows the cash actually spent.</p>
	<p>The default period is <?php echo esc_html( hwm_value( $hwm_dr, 'ownershipYearsDefault' ) ); ?> years. Expected lives come from the lifespan table above.</p>
	<p><strong>Tax credits.</strong> The federal Energy Efficient Home Improvement Credit (25C) covered <?php echo esc_html( hwm_value( 'incentives', 'credits.0.percent' ) ); ?>% of the cost of a qualifying heat pump water heater, up to $<?php echo esc_html( hwm_value( 'incentives', 'credits.0.maxUsd', 0 ) ); ?>, and <?php echo esc_html( hwm_value( 'incentives', 'credits.1.percent' ) ); ?>% of a qualifying gas water heater, up to $<?php echo esc_html( hwm_value( 'incentives', 'credits.1.maxUsd', 0 ) ); ?>. According to the IRS, it applies only to property placed in service through <?php echo esc_html( hwm_date( hwm_value( 'incentives', 'credits.0.placedInServiceThrough' ) ) ); ?>, so our calculators use a federal credit of $0 for a heater installed now. State and utility rebates vary too much to model; ENERGY STAR's Rebate Finder lists them.</p>
	<p><strong>Options compared.</strong> On gas: a 50-gallon gas tank (like-for-like), a gas tankless unit (tank-to-tankless job) and a 50-gallon heat pump (fuel switch). On electricity: a 50-gallon electric tank, an electric tankless unit and a 50-gallon heat pump. Installed costs come from the cost calculator's model and running costs from the gas vs electric formula, both for your state.</p>
	<p><strong>Limitations.</strong> The comparison ignores price inflation and the time value of money, and it assumes your hot water use stays the same. A replacement inside the period is priced like the first install, and value left is counted on a straight line.</p>
	<p>The cross-check compares our running costs and heat pump payback with DOE's 2024 life-cycle cost results (national averages, 2022 or 2023 dollars). No manufacturer publishes a cost of ownership we can test against.</p>
	<?php $hwm_xfilled( 'tankless-vs-tank-water-heater', 'cost of ownership (DOE 2024 rules)' ); ?>
</section>

<section id="heat-pump-water-heater-cost">
	<h3>Heat pump water heater savings and payback</h3>
	<p>The savings calculator on the heat pump cost page reuses two models already described above, so its numbers agree with every other tool:</p>
	<ol>
		<li><strong>Installed cost</strong> of both jobs comes from the cost calculator's model: the heat pump replacing your current heater (a fuel switch with a new 240-volt circuit when you have gas, a like-for-like job when you have an electric tank), and a new heater of your current type in the same spot. Ticking the space option adds the heat pump space-work extra.</li>
		<li><strong>Yearly running cost</strong> of each heater comes from the gas vs electric formula, with your state's prices, incoming water temperature and household use.</li>
		<li><strong>Extra cost</strong> = heat pump installed − new current-type heater installed − any rebate you enter. A rebate is counted only up to the heat pump's installed price.</li>
		<li><strong>Simple payback</strong> = extra cost ÷ yearly saving. <strong>Net over the heater's life</strong> = yearly saving × typical life − extra cost. No interest or price changes are applied.</li>
	</ol>
	<p>The green "pays back quickly" label means a payback within <?php echo esc_html( hwm_value( 'heat-pump', 'savingsCalculator.paybackGoodYears' ) ); ?> years, our editorial threshold; amber means within the heater's typical life; red means longer, or never. Space rules, electrical needs and rebates come from ENERGY STAR, the U.S. Code and the IRS, listed in the heat pump data file.</p>
	<p><strong>Where it goes.</strong> You pick the space the heat pump will sit in. ENERGY STAR advises spaces that stay between <?php echo esc_html( hwm_value( 'heat-pump', 'space.spaceTempLowF' ) ); ?> and <?php echo esc_html( hwm_value( 'heat-pump', 'space.spaceTempHighF' ) ); ?> °F, and says intake air below about <?php echo esc_html( hwm_value( 'heat-pump', 'space.spaceTempLowF' ) ); ?> °F triggers the electric elements and significantly reduces efficiency. No DOE, NEEA or ENERGY STAR source we could open gives one derate number for a cold garage, so for that choice the tool shows a warning and keeps the best-case saving; it does not invent a factor.</p>
	<p><strong>Limitations.</strong> The heat pump's efficiency drops in a cold room and its backup elements run more in a busy hour; the UEF rating averages this out. Utility rebates are not built in because they change too often; enter yours.</p>
	<p>The cross-check compares our results with ENERGY STAR's published saving and energy reduction, and with DOE's 2024 payback for heat pump water heaters.</p>
	<?php $hwm_xfilled( 'heat-pump-water-heater-cost', 'heat pump savings (ENERGY STAR, DOE 2024 rule)' ); ?>
</section>

<section id="water-heater-expansion-tank-size">
	<h3>Thermal expansion tank size</h3>
	<p>The calculator follows AMTROL's published sizing method for its Therm-X-Trol tanks:</p>
	<ol>
		<li><strong>Expanded water</strong> = heater gallons × expansion factor, from AMTROL's table of how much water grows when heated from <?php echo esc_html( hwm_value( 'expansion-tanks', 'expansionFactors.startTempF' ) ); ?> °F. Between table temperatures we interpolate in a straight line.</li>
		<li><strong>Design pressure factor</strong> = (maximum pressure + <?php echo esc_html( hwm_value( 'expansion-tanks', 'atmosphericPsi' ) ); ?>) ÷ (maximum pressure − supply pressure), in psi gauge.</li>
		<li><strong>Tank volume needed</strong> = expanded water × design pressure factor.</li>
		<li>Pick the smallest tank whose total volume is at least the volume needed and whose maximum acceptance volume is at least the expanded water. When either is within <?php echo esc_html( hwm_value( 'computed/water-heater-expansion-tank-size', 'figures.headroomPct' ) ); ?>% of the tank's limit, we also name the next size as the safer pick (our rule).</li>
	</ol>
	<p>The defaults are a <?php echo esc_html( hwm_value( 'expansion-tanks', 'defaults.heaterGallons' ) ); ?>-gallon heater, the site's <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F setpoint, <?php echo esc_html( hwm_value( 'expansion-tanks', 'defaults.supplyPsi' ) ); ?> psi supply and a <?php echo esc_html( hwm_value( 'expansion-tanks', 'defaults.maxPsi' ) ); ?> psi maximum (the relief valve setting AMTROL's charts assume). Tank volumes are AMTROL's residential models; other brands publish their own.</p>
	<p><strong>Limitations.</strong> One storage water heater only; not for boilers, wells or several heaters in series. Watts' calculator uses a warmer starting temperature and a final pressure tied to the supply pressure, so it gives larger tanks.</p>
	<p>The cross-check runs AMTROL's worked example and every cell of its quick sizing chart through our formula.</p>
	<?php $hwm_xfilled( 'water-heater-expansion-tank-size', 'expansion tank sizing (AMTROL)' ); ?>
</section>
<section id="hot-water-runs-out-fast">
	<h3>Winter vs summer shower table (hot water runs out fast)</h3>
	<p>Not a calculator, but the table on <?php echo hwm_link( 'hot-water-runs-out-fast', 'hot water runs out fast' ); // phpcs:ignore ?> is our arithmetic, so here is the method:</p>
	<ol>
		<li><strong>Share of tank water in a mixed shower</strong> = (shower temperature − inlet) ÷ (tank setting − inlet), an energy balance with no pipe losses. The shower temperature is <?php echo esc_html( hwm_value( 'troubleshooting', 'runsOut.showerMixF' ) ); ?> °F, the value in Stiebel Eltron's sizing sheet; the tank is the site's <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F setpoint.</li>
		<li><strong>Tank water used</strong> = shower flow (the federal maximum, <?php echo esc_html( hwm_value( 'fixture-flow-rates', 'fixtures.shower.gpm' ) ); ?> GPM) × minutes (our example, <?php echo esc_html( hwm_value( 'troubleshooting', 'runsOut.exampleShowerMinutes' ) ); ?>) × that share.</li>
		<li><strong>Longer to reheat</strong> = winter temperature rise ÷ summer temperature rise − 1, because the heat needed per gallon is proportional to the rise.</li>
	</ol>
	<p>Inlet temperatures are the coldest and warmest monthly estimates described above under the tankless calculator. <strong>Limitations.</strong> Pipe heat losses, standby losses and shower habits are not modelled; real draws are a little higher.</p>
</section>

<section id="safety-pages">
	<h3>Safety pages: tank setting, relief valve, heating element, gas line and electrical tables</h3>
	<p>The safety pages restate official sources (CPSC, CDC, DOE) and manufacturer manuals; the only numbers that are ours are three small pieces of arithmetic:</p>
	<ol>
		<li><strong>Hot water at a higher setting</strong> (<?php echo hwm_link( 'water-heater-temperature-setting', 'water heater temperature setting' ); // phpcs:ignore ?>): the tank share of a <?php echo esc_html( hwm_value( 'troubleshooting', 'runsOut.showerMixF' ) ); ?> °F shower is (shower − inlet) ÷ (tank − inlet), as in the table above; extra mixed water = share at <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F ÷ share at the hotter setting − 1. It only holds when a mixing valve holds the shower at the same temperature.</li>
		<li><strong>Normal pressure limit</strong> (<?php echo hwm_link( 'water-heater-pressure-relief-valve-leaking', 'relief valve leaking' ); // phpcs:ignore ?>): the valve's set pressure × <?php echo esc_html( hwm_value( 'computed/water-heater-pressure-relief-valve-leaking', 'figures.sharePct' ) ); ?>%, Watts' stated maximum operating share.</li>
		<li><strong>Element resistance</strong> (<?php echo hwm_link( 'water-heater-element-replacement', 'water heater element replacement' ); // phpcs:ignore ?>): Ohm's law, resistance = volts² ÷ watts and current = watts ÷ volts, for the ratings sold for home water heaters. A. O. Smith's pass range (<?php echo esc_html( hwm_value( 'safety', 'element.ohmsLow' ) ); ?>–<?php echo esc_html( hwm_value( 'safety', 'element.ohmsHigh' ) ); ?> ohms) is shown next to it.</li>
	</ol>
	<p>The gas, electrical and pilot pages add three more:</p>
	<ol start="4">
		<li><strong>Gas pipe lookup</strong> (<?php echo hwm_link( 'gas-line-size-for-tankless-water-heater', 'gas line size for a tankless water heater' ); // phpcs:ignore ?>): gas flow = BTU/h ÷ <?php echo esc_html( number_format( (float) hwm_raw( 'safety', 'gasLine.btuPerCf' ) ) ); ?> BTU per cubic foot (Rinnai's basis); the length is rounded up to the next row of <?php echo esc_html( (string) hwm_raw( 'safety', 'gasLine.edition' ) ); ?> <?php echo esc_html( (string) hwm_raw( 'safety', 'gasLine.tables.drop05.table' ) ); ?> or <?php echo esc_html( (string) hwm_raw( 'safety', 'gasLine.tables.drop30.table' ) ); ?> and the smallest pipe size whose capacity covers the flow is chosen. <strong>Cross-check:</strong> the reverse lookup (longest length a size still carries) reproduces <?php echo esc_html( hwm_value( 'computed/gas-line-size-for-tankless-water-heater', 'figures.rinnaiMatches' ) ); ?> of the <?php echo esc_html( hwm_value( 'computed/gas-line-size-for-tankless-water-heater', 'figures.rinnaiChecks' ) ); ?> maximum lengths in Rinnai's published natural gas table; the two differences are explained on the page, and we show the shorter figure.</li>
		<li><strong>Amps per circuit</strong> (<?php echo hwm_link( 'tankless-water-heater-electrical-requirements', 'tankless electrical requirements' ); // phpcs:ignore ?>): kW × 1,000 ÷ 240 V, divided evenly over the circuits the maker specifies, and compared with the maker's breaker. Power on 208 V = kW × (208 ÷ 240)²; it matches Stiebel Eltron's six published 208-volt ratings to the tenth of a kW.</li>
		<li><strong>Pilot light page</strong> (<?php echo hwm_link( 'water-heater-pilot-light-wont-stay-lit', 'water heater pilot light won\'t stay lit' ); // phpcs:ignore ?>): no arithmetic; causes, status codes and the lighting procedure are restated from A. O. Smith and Rheem manuals.</li>
	</ol>
	<p>Code text (2021 IFGC and 2021 IRC, which restates the NEC) is copyrighted; we quote only the rows and sentences a page needs, with the table or section number and edition, and the local code official decides what applies.</p>
	<p><strong>Limitations.</strong> Pipe and tank heat losses are not modelled; element readings vary with tolerance and temperature. The gas lookup covers Schedule 40 steel pipe and natural gas only. Every safety statement on these pages carries its own source, and none is our own advice.</p>
</section>

<section id="state-cost-pages">
	<h3>State cost pages (installation cost by state)</h3>
	<p>Each page under <?php echo hwm_link( 'water-heater-installation-cost', 'water heater replacement cost' ); // phpcs:ignore ?> for a state reuses the cost calculator's model and the state data files, so its numbers match the calculators to the dollar:</p>
	<ol>
		<li><strong>Installed cost</strong> = unit price + (labor + permit + extras) × the state's price factor (BEA Regional Price Parity ÷ 100), exactly as in the cost calculator.</li>
		<li><strong>Metro areas</strong>: the same job with the metro area's own BEA price parity in place of the state's. The five most populous metropolitan areas of the state that have a parity series are listed (Census Vintage 2025 populations); areas redrawn after BEA's last metro release have no series and are named on the page.</li>
		<li><strong>Running cost</strong>: the gas vs electric formula with the state's EIA residential prices and its average incoming water temperature; heat pump saving and payback come from the heat pump savings calculator with the state selected.</li>
		<li><strong>Month by month</strong>: incoming water uses the full Christensen–Burch correlation from the EnergyPlus Engineering Reference, Tmains = (Tavg + <?php echo esc_html( hwm_value( 'groundwater-temp-by-state', 'correlation.offsetF' ) ); ?>) + ratio × (ΔTmax ÷ 2) × sin(<?php echo esc_html( hwm_value( 'groundwater-temp-by-state', 'correlation.degPerDay' ) ); ?> × (day − <?php echo esc_html( hwm_value( 'groundwater-temp-by-state', 'correlation.dayOffset' ) ); ?> − lag) − <?php echo esc_html( hwm_value( 'groundwater-temp-by-state', 'correlation.phaseDeg' ) ); ?>), with lag = <?php echo esc_html( hwm_value( 'groundwater-temp-by-state', 'correlation.lagBaseDays' ) ); ?> − (Tavg − <?php echo esc_html( hwm_value( 'groundwater-temp-by-state', 'correlation.refTempF' ) ); ?>) days, evaluated mid-month. Tankless flow = unit output ÷ (<?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.lbPerGallon' ) ); ?> × <?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.minutesPerHour' ) ); ?> × rise) for the <?php echo esc_html( hwm_value( 'tankless-reference-units', 'gasUnits.0.inputBtuh', 0 ) ); ?> BTU/h condensing unit in Rinnai's chart; the monthly energy bill is the yearly formula for that month's rise × days ÷ 365.</li>
		<li><strong>Fuel mix</strong>: EIA's 2020 Residential Energy Consumption Survey, main water heating fuel by state. <strong>Rebates</strong>: the state energy office's own page, quoted with the date we checked it; where we could not open an official page we say so instead of guessing.</li>
		<li><strong>Which states</strong>: the ten most populous by the Census Vintage 2025 estimate. Other states get a page only after these ten are indexed and ranking.</li>
	</ol>
	<p><strong>Limitations.</strong> The price factor measures all consumer prices, including rent, not plumber wages; within a metro area, prices still vary by neighborhood and by contractor.</p>
</section>

<section id="cost-index">
	<h3>Water heater cost index by state</h3>
	<p>The <?php echo hwm_link( 'water-heater-installation-cost-index', 'water heater cost index' ); // phpcs:ignore ?> applies the same model to all 50 states and DC for three 50-gallon tanks (gas, electric, heat pump), like for like:</p>
	<ol>
		<li><strong>Installed price</strong>: as on the state cost pages (unit + labor, permit and haul-away × the state's price factor).</li>
		<li><strong>Yearly energy</strong>: the gas vs electric formula for a typical household (<?php echo esc_html( hwm_value( 'computed/water-heater-installation-cost-index', 'figures.gallons' ) ); ?> gal/day, <?php echo esc_html( hwm_value( 'computed/water-heater-installation-cost-index', 'figures.setpoint' ) ); ?> °F) with the state's EIA prices and average incoming water temperature.</li>
		<li><strong><?php echo esc_html( hwm_value( 'computed/water-heater-installation-cost-index', 'figures.years' ) ); ?>-year cost</strong> = installed price + years × yearly energy (<code>formulas.costOfOwnership</code>, no rebates, no discounting; all three tanks outlast the window, so no replacement is added). States are ranked by the lowest of the three.</li>
		<li><strong>Chart</strong>: drawn from the same computed file by <code>npm run build:charts</code>; the PNG used in the chart embed code is rendered from the same SVG.</li>
	</ol>
	<p>Every table behind the index is a CSV on the <?php echo hwm_link( 'data', 'data downloads page' ); // phpcs:ignore ?>.</p>
</section>

<section id="solar-water-heater-cost">
	<h3>Solar water heater cost and payback</h3>
	<p>The <?php echo hwm_link( 'solar-water-heater-cost', 'solar water heater cost guide' ); // phpcs:ignore ?> applies the equations of DOE FEMP's <a href="<?php echo esc_url( (string) hwm_raw( 'solar-water-heating', 'meta.sourceUrl' ) ); ?>" rel="noopener">solar hot water calculator</a> (built for federal buildings) to one typical household in each state, with FEMP's own sun data for one city per state:</p>
	<ol>
		<li><strong>Load</strong> = gallons a day × <?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.btuPerGallonF' ) ); ?> × (setting − incoming water) ÷ 100,000 therms.</li>
		<li><strong>Collector area</strong> = load ÷ collector efficiency (<?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.etaSolar' ) ); ?>) ÷ the city's maximum daily sunshine, so solar covers the whole load on the sunniest day (FEMP Scenario 1).</li>
		<li><strong>Solar heat a year</strong> = area × average daily sunshine × efficiency × 365 × usable share (<?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.utilization' ) ); ?>). Pump electricity = area × <?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.pumpKwhPerFt2Day' ) ); ?> kWh per ft² per day.</li>
		<li><strong>Yearly saving</strong> = solar heat ÷ the backup's UEF × the state's EIA price − pump electricity − upkeep (<?php echo esc_html( (string) ( (float) hwm_value( 'solar-water-heating', 'femp.omShareOfCostPerYear' ) * 100 ) ); ?>% of the system cost a year, FEMP). Payback = (system cost − a new standard tank) ÷ yearly saving (<code>formulas.solarWaterHeating</code>, <code>formulas.solarYearlySaving</code>).</li>
	</ol>
	<p><strong>Cross-check.</strong> For the spreadsheet's own example (<?php echo esc_html( number_format( (float) hwm_value( 'solar-water-heating', 'femp.example.gallonsPerDay' ) ) ); ?> gallons a day, <?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.example.inletF' ) ); ?> to <?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.example.hotF' ) ); ?> °F, <?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.example.place' ) ); ?>) our code returns FEMP's collector area (<?php echo esc_html( number_format( (float) hwm_value( 'solar-water-heating', 'femp.example.areaFt2' ), 2 ) ); ?> ft²), solar heat (<?php echo esc_html( number_format( (float) hwm_value( 'solar-water-heating', 'femp.example.solarThermsYear' ), 1 ) ); ?> therms a year) and pump energy (<?php echo esc_html( hwm_value( 'solar-water-heating', 'femp.example.pumpThermsYear' ) ); ?> therms a year); a unit test keeps it that way. FEMP counts the pump at the fuel price; we price it at the electricity rate, which is what a home pays.</p>
	<p><strong>Limitations.</strong> The installed cost is NREL's <?php echo esc_html( hwm_value( 'solar-water-heating', 'installedCost.year' ) ); ?> national range ($<?php echo esc_html( number_format( (float) hwm_value( 'solar-water-heating', 'installedCost.low' ) ) ); ?>–$<?php echo esc_html( number_format( (float) hwm_value( 'solar-water-heating', 'installedCost.high' ) ) ); ?>), not adjusted for inflation or state, because no newer public figure exists; we use its middle. Cost guides are shown beside it for comparison only. FEMP lists no city for <?php echo esc_html( hwm_value( 'computed/solar-water-heater-cost', 'figures.missing' ) ); ?>.</p>
</section>

<h2 id="data-sources">Where does our data come from?</h2>
<p>Every number lives in a data file with a named source, a link and the date we last checked it. We prefer government sources first, then codes and standards, then manufacturer spec sheets. Other websites' cost guides are never used as the source of a number.</p>
<?php
$hwm_files = array(
	'site'                      => 'Default setpoint and national energy prices',
	'safety'                    => 'Scald times, CDC Legionella limits, relief valve and heating element facts (safety pages)',
	'fixture-flow-rates'        => 'Fixture and appliance flow rates',
	'groundwater-temp-by-state' => 'Air temperature normals and the mains water correlation',
	'peak-hour-demand'          => 'Hot water per use for the peak-hour worksheet',
	'fhr-to-tank-size'          => 'First hour rating by tank size (gas and electric models)',
	'energystar-water-heaters'  => 'Heat pump, high-efficiency gas and gas tankless models',
	'uef-ranges'                => 'Efficiency (UEF) by type and federal minimums',
	'hot-water-use'             => 'DOE test conditions and typical daily use',
	'electrical'                => 'Electric tankless electrical requirements (one manufacturer)',
	'energy-prices'             => 'Electricity and natural gas prices by state',
	'lifespans'                 => 'Expected life by type',
	'serial-formats'            => 'Serial number date codes by brand',
	'cost-ranges'               => 'Equipment prices from retail listings',
	'installation-costs'        => 'Installation labor and extras (our ranges, anchored to DOE)',
	'regional-cost-index'       => 'Regional price factor by state',
	'incentives'                => 'Federal tax credits',
	'heat-pump'                 => 'Heat pump space, electrical, savings and rebate figures (ENERGY STAR, U.S. Code, IRS)',
	'expansion-tanks'           => 'Expansion factors, tank volumes and quick sizing chart (AMTROL)',
	'decision-rules'            => 'Repair-or-replace thresholds (editorial)',
);
?>
<div class="table-wrap" role="region" aria-label="Data files and their sources" tabindex="0">
<table>
	<caption>Data files and their sources</caption>
	<thead><tr><th scope="col">What it holds</th><th scope="col">Source</th><th scope="col">Last checked</th></tr></thead>
	<tbody>
		<?php foreach ( $hwm_files as $hwm_f => $hwm_label ) : ?>
			<?php
			$hwm_m = hwm_raw( $hwm_f, 'meta' );
			if ( ! is_array( $hwm_m ) ) {
				continue;
			}
			$hwm_url_src = (string) ( $hwm_m['sourceUrl'] ?? '' );
			$hwm_own     = false !== strpos( $hwm_url_src, (string) hwm_site( 'domain' ) );
			?>
			<tr>
				<td><?php echo esc_html( $hwm_label ); ?></td>
				<td>
					<?php if ( $hwm_url_src && ! $hwm_own ) : ?>
						<a href="<?php echo esc_url( $hwm_url_src ); ?>" rel="noopener"><?php echo esc_html( (string) $hwm_m['source'] ); ?></a>
					<?php else : ?>
						<?php echo esc_html( (string) $hwm_m['source'] ); ?>
					<?php endif; ?>
				</td>
				<td><?php echo esc_html( hwm_date( (string) ( $hwm_m['sourceDate'] ?? '' ) ) ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>

<h2 id="assumptions">Which numbers are our own assumptions?</h2>
<p>A few numbers on this site are our judgment, not measured data, because no agency publishes them. We list them here so you can disagree with them, and every calculator lets you override them.</p>
<div class="table-wrap" role="region" aria-label="Editorial assumptions" tabindex="0">
<table>
	<caption>Editorial assumptions</caption>
	<thead><tr><th scope="col">Assumption</th><th scope="col">Value</th><th scope="col">Why we chose it</th></tr></thead>
	<tbody>
		<tr><td>Hard water shortens tank life</td><td><?php echo esc_html( hwm_value( $hwm_life, 'adjustments.hardness.hard' ) ); ?> year (hard), <?php echo esc_html( hwm_value( $hwm_life, 'adjustments.hardness.veryHard' ) ); ?> years (very hard)</td><td>Scale builds up on the tank bottom and elements and speeds up wear. Manufacturer manuals tell owners in hard-water areas to flush more often, but nobody publishes a measured figure, so we use a small, round adjustment.</td></tr>
		<tr><td>No yearly flushing</td><td><?php echo esc_html( hwm_value( $hwm_life, 'adjustments.noAnnualFlush' ) ); ?> year</td><td>Manufacturer manuals call for draining sediment regularly; skipping it lets sediment build up.</td></tr>
		<tr><td>Anode rod replaced</td><td>+<?php echo esc_html( hwm_value( $hwm_life, 'adjustments.anodeReplaced' ) ); ?> year</td><td>The anode corrodes instead of the tank. Replacing it extends tank protection, but the gain depends on your water, so we keep it modest.</td></tr>
		<tr><td>Shortest expected life we show</td><td><?php echo esc_html( hwm_value( $hwm_life, 'adjustments.minimumLifeYears' ) ); ?> years</td><td>Keeps adjustments from adding up to an unrealistic result.</td></tr>
		<tr><td>Heat pump "pays back quickly"</td><td>Within <?php echo esc_html( hwm_value( 'heat-pump', 'savingsCalculator.paybackGoodYears' ) ); ?> years</td><td>About half a heat pump's typical life, so most of its life is net saving. It only sets the label colour; the payback number is shown either way.</td></tr>
		<tr><td>Expansion tank "safer" pick</td><td>Within <?php echo esc_html( hwm_value( 'computed/water-heater-expansion-tank-size', 'figures.headroomPct' ) ); ?>% of a tank's limit</td><td>Real pressures and settings drift. AMTROL's own chart picks the larger tank in most borderline cases, and this rule reproduces that.</td></tr>
		<tr><td>Age status colors</td><td>Amber from <?php echo esc_html( hwm_value( $hwm_life, 'statusBands.amberFrom', 1 ) ); ?>, red from <?php echo esc_html( hwm_value( $hwm_life, 'statusBands.redFrom', 1 ) ); ?> × expected life</td><td>Gives you time to plan a replacement before the typical end of life.</td></tr>
		<tr><td>Installation labor and extras ranges</td><td>See the water heater cost section</td><td>No public source publishes labor by job type; our ranges sit on DOE's averages and ranges, with industry guides as a sanity check.</td></tr>
		<tr><td>Repair-or-replace thresholds</td><td>See the repair or replace section</td><td>Conservative rules of the kind homeowners already use, written down so you can check them.</td></tr>
	</tbody>
</table>
</div>

<h2 id="review">How often do we review the numbers?</h2>
<ul>
	<li><strong>Every three months:</strong> energy prices, equipment and installation costs, and tax credits or rebates.</li>
	<li><strong>Once a year:</strong> everything else, including flow rates, incoming water temperatures, efficiency ratings, lifespans and serial number formats.</li>
	<li><strong>Whenever a formula or data file changes:</strong> the published-example and manufacturer cross-checks are run again, and the affected pages get a new "Last updated" date.</li>
</ul>
<p>The "Last checked" date of each source is in the data file table above. A tool's "Last verified" date, shown at the bottom of each calculator, is the day its cross-check was last run.</p>

<h2 id="limits">What are the limits of these calculators?</h2>
<ul>
	<li>Results are planning estimates. Your pipes, water pressure, local code and the exact model you buy all change the real answer.</li>
	<li>State averages, such as incoming water temperature or energy prices, hide local variation. Use your own measured value or your bill's rate when you have one.</li>
	<li>Some sources are older than we would like. DOE removed several of its consumer Energy Saver pages from energy.gov; where we rely on one of them, we cite an archived copy and say so in the data file.</li>
	<li>We are not plumbers or electricians, and nobody in the trade has reviewed these pages yet. We do not give instructions for gas, venting or electrical work beyond what the codes and manufacturer manuals say. Confirm sizing and code requirements with a licensed professional in your area.</li>
</ul>

<h2 id="report-a-mistake">How to report a mistake</h2>
<p>If a number does not match your manufacturer's literature or a source we cite, email <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a> with the page and the source. We check it, fix it if we are wrong, and update the page's date. More on how we handle sources and corrections is in <?php echo $hwm_link( 'editorial-policy', 'our editorial policy' ); // phpcs:ignore ?>, and who builds these tools is on <?php echo $hwm_link( 'about', 'the about page' ); // phpcs:ignore ?>. All tools are listed on <?php echo $hwm_link( 'calculators', 'the calculators page' ); // phpcs:ignore ?>.</p>
<?php
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy, FEMP', 'title' => 'Purchasing Energy-Efficient Residential Water Heaters', 'url' => (string) hwm_raw( 'site', 'defaults.setpointTempF.sourceUrl' ), 'accessed' => (string) hwm_raw( 'site', 'defaults.setpointTempF.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Electric Power Annual, Table 2.10: Average price of electricity to ultimate customers by state', 'url' => (string) hwm_raw( 'energy-prices', 'sources.electricity.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Natural gas prices: average residential price by state', 'url' => (string) hwm_raw( 'energy-prices', 'sources.gasPrice.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'NOAA National Centers for Environmental Information', 'title' => 'Climate at a Glance: statewide time series', 'url' => (string) hwm_raw( $hwm_gw, 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_gw, 'meta.sourceDate' ) ),
	array( 'publisher' => 'EnergyPlus Engineering Reference', 'title' => 'Water Systems: Water Mains Temperatures', 'url' => (string) hwm_raw( $hwm_gw, 'correlation.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_gw, 'meta.sourceDate' ) ),
	array( 'publisher' => 'Electronic Code of Federal Regulations', 'title' => '10 CFR 430.32: Energy and water conservation standards', 'url' => (string) hwm_raw( 'fixture-flow-rates', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'fixture-flow-rates', 'meta.sourceDate' ) ),
	array( 'publisher' => 'Electronic Code of Federal Regulations', 'title' => '10 CFR 430, Subpart B, Appendix E: Uniform test method for water heaters', 'url' => (string) hwm_raw( 'hot-water-use', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'hot-water-use', 'meta.sourceDate' ) ),
	array( 'publisher' => 'Federal Register', 'title' => 'Energy Conservation Standards for Consumer Water Heaters (final rule, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_life, 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_life, 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by State', 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
	array( 'publisher' => 'Internal Revenue Service', 'title' => 'Energy Efficient Home Improvement Credit', 'url' => (string) hwm_raw( 'incentives', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'incentives', 'meta.sourceDate' ) ),
	array( 'publisher' => 'PNNL Building America Solution Center', 'title' => 'Domestic hot water instantaneous sizing', 'url' => (string) hwm_raw( 'fixture-flow-rates', 'fixtures.dishwasher.sourceUrl' ), 'accessed' => (string) hwm_raw( 'fixture-flow-rates', 'meta.sourceDate' ) ),
);
$related = array( 'calculators', 'about', 'editorial-policy' );
