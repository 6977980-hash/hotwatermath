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

<h2>Which physical constants do the calculators use?</h2>
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

<h2>Do our formulas match published examples?</h2>
<p>Yes, closely. Before any calculator is built we test the shared formulas against worked examples published by the Department of Energy, AHRI and a manufacturer spec sheet. The table shows our answer next to the published one. Small differences come from rounding in the published figures.</p>
<?php echo hwm_table( 'computed/cross-checks', 'formulaChecks' ); // phpcs:ignore -- escaped in the part. ?>

<h2>How each calculator works</h2>
<p>Each section below covers one tool: its formula, the data it uses, its limits, and a table for the manufacturer cross-check. The cross-check is run with at least three test cases from manufacturer sizing literature when the tool is built, and again whenever its formula or data change.</p>

<section id="tankless-water-heater-size-calculator">
	<h3>Tankless water heater size</h3>
	<p>A tankless water heater is sized by two numbers: the flow of hot water you use at the same time, in gallons per minute (GPM), and how many degrees the heater must warm the incoming water. The heater's output must cover both at once.</p>
	<ol>
		<li><strong>Peak flow</strong> = the sum of the flow rates of the fixtures you run together.</li>
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
	<p>Tankless sizing uses the coldest month, because a heater sized for the yearly average runs out of capacity in winter. We use statewide air temperatures except for Alaska (Anchorage), Hawaii (Honolulu) and Washington, DC (Reagan National Airport), where NOAA has no statewide series or the statewide figure is dominated by places where almost nobody lives.</p>
	<details>
		<summary>Estimated incoming water temperature for all 50 states and DC</summary>
		<?php echo hwm_table( 'computed/inlet-temps', 'inletTemps' ); // phpcs:ignore ?>
	</details>
	<p>For electric tankless units, the tool also shows the wiring a manufacturer lists for units of that size, as a reference to discuss with your electrician, not as a design.</p>
	<?php echo hwm_table( $hwm_ref, 'electricReference' ); // phpcs:ignore ?>
	<p><strong>Limitations.</strong> Flow at a fixture mixes hot and cold water, while the formula heats the full flow to the setpoint. That slightly overstates the need, which errs on the safe side. Real units lose some output at high altitude and with long pipe runs, and some models have a minimum flow below which they do not fire.</p>
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
	<p><strong>Limitations.</strong> Your busiest hour is a guess you make about your own habits. FHR varies between models of the same size, so check the label of the unit you plan to buy. Federal efficiency rules effectively require large electric storage tanks to be heat pump models, which is why the electric rows stop at <?php echo esc_html( hwm_value( 'uef-ranges', 'federalMinimums.electricResistanceMaxGallons' ) ); ?> gallons.</p>
	<?php $hwm_xcheck( 'tank sizing (A. O. Smith, Bradford White, Rheem sizing guides)' ); ?>
</section>

<section id="water-heater-age-calculator">
	<h3>Water heater age</h3>
	<p>Most manufacturers put the build date into the serial number. The tool matches your serial number against each brand's published date code, reads the year and month (or week), and works out the age from today's date.</p>
	<ol>
		<li><strong>Build date</strong> = decoded from the serial number pattern for your brand. Letter-coded years repeat on a fixed cycle, so we pick the most recent year that is not in the future and show the earlier possibilities too.</li>
		<li><strong>Age</strong> = today's date minus the build date. When a code gives only the year, we count from the middle of that year.</li>
		<li><strong>Expected life</strong> = the typical life for the type of heater, adjusted for your water and maintenance (see editorial assumptions below), never below <?php echo esc_html( hwm_value( $hwm_life, 'adjustments.minimumLifeYears' ) ); ?> years.</li>
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
		<li>A leaking tank is always a replacement, because the tank itself cannot be repaired.</li>
		<li>Replace when the repair costs more than <?php echo esc_html( hwm_value( $hwm_dr, 'repairOrReplace.repairShareAlways', 2 ) ); ?> × the price of a new installed unit.</li>
		<li>Replace when the heater has used at least <?php echo esc_html( hwm_value( $hwm_dr, 'repairOrReplace.ageShare', 2 ) ); ?> × its expected life and the repair costs more than <?php echo esc_html( hwm_value( $hwm_dr, 'repairOrReplace.repairShareWhenOld', 2 ) ); ?> × the price of a new one.</li>
		<li>An old heater with a cheap repair is shown as borderline. Anything else is a repair.</li>
	</ol>
	<p><strong>Limitations.</strong> The thresholds are judgment calls, not measured data. Safety problems, such as a faulty relief valve or a gas smell, override any cost rule.</p>
	<?php $hwm_xcheck( 'repair or replace (manufacturer warranty and service guidance)' ); ?>
</section>

<section id="water-heater-cost-calculator">
	<h3>Water heater cost</h3>
	<p>Installed cost = the price of the unit + installation labor + the extras your job needs, such as a permit, haul-away or an expansion tank. Labor and extras are multiplied by a regional price factor for your state. The unit price is not, because heaters are sold at national prices.</p>
	<ul>
		<li><strong>Unit prices</strong> come from retail listings we checked by hand. The table shows the median and range for each size class.</li>
		<li><strong>Regional factor</strong> = your state's Regional Price Parity from the U.S. Bureau of Economic Analysis ÷ 100. A factor above 1 means prices in your state are above the U.S. average. RPPs measure overall consumer prices, so they stand in for local labor costs; they are not a plumber wage survey.</li>
		<li><strong>Labor and extras</strong> are not in our data yet. No primary source we could open publishes them; DOE itself uses paid RSMeans data. They will be added with named references when the cost calculator is built, never guessed.</li>
	</ul>
	<?php echo hwm_table( 'computed/unit-prices', 'unitPrices' ); // phpcs:ignore ?>
	<details>
		<summary>Regional price factor for all 50 states and DC</summary>
		<?php echo hwm_table( 'computed/regional-multipliers', 'regional' ); // phpcs:ignore ?>
	</details>
	<p><strong>Limitations.</strong> Listing prices from one retailer on one day are a snapshot, so we recheck them every three months. A quote from an installer who has seen your home will always be more precise than any average.</p>
	<?php $hwm_xcheck( 'installed cost (manufacturer list prices and published installation requirements)' ); ?>
</section>

<section id="gas-vs-electric-water-heater">
	<h3>Gas vs electric operating cost</h3>
	<p>Yearly running cost comes from the heat your hot water needs, the heater's efficiency and your energy price.</p>
	<ol>
		<li><strong>Heat needed per year (BTU)</strong> = gallons per day × <?php echo esc_html( $hwm_k( 'daysPerYear' ) ); ?> days × <?php echo esc_html( $hwm_k( 'lbPerGallon', 2 ) ); ?> lb per gallon × temperature rise.</li>
		<li><strong>Energy used</strong> = heat needed ÷ UEF, then ÷ <?php echo esc_html( $hwm_k( 'btuPerTherm' ) ); ?> for therms of gas or ÷ <?php echo esc_html( $hwm_k( 'btuPerKwh' ) ); ?> for kWh of electricity.</li>
		<li><strong>Yearly cost</strong> = energy used × your price per therm or per kWh.</li>
	</ol>
	<p>The default hot water use is <?php echo esc_html( hwm_value( 'hot-water-use', 'typicalHouseholdGallonsPerDay.value' ) ); ?> gallons a day, which DOE FEMP calls typical household use. It is also the medium draw pattern in DOE's test procedure. The default rise is <?php echo esc_html( hwm_value( 'hot-water-use', 'testConditions.riseF' ) ); ?> °F, the rise in the official efficiency test (<?php echo esc_html( hwm_value( 'hot-water-use', 'testConditions.inletF' ) ); ?> °F in, <?php echo esc_html( hwm_value( 'hot-water-use', 'testConditions.outletF' ) ); ?> °F out). With your state's incoming water temperature and your setpoint, the tool uses your real rise instead.</p>
	<?php echo hwm_table( $hwm_ref, 'drawPatterns' ); // phpcs:ignore ?>
	<p>Default efficiency by type is the median of real models: manufacturer spec sheets for standard tanks and the ENERGY STAR certified list for high-efficiency gas, heat pump and gas tankless units. Electric tankless units are not ENERGY STAR certified, so their default stays close to the federal minimum.</p>
	<?php echo hwm_table( 'computed/uef-defaults', 'uef' ); // phpcs:ignore ?>
	<p>Energy prices are the latest yearly residential averages from the U.S. Energy Information Administration: <?php echo esc_html( hwm_value( 'computed/energy-prices', 'us.usdPerKwh', 4 ) ); ?> dollars per kWh and <?php echo esc_html( hwm_value( 'computed/energy-prices', 'us.usdPerTherm', 3 ) ); ?> dollars per therm for the U.S. as a whole. EIA reports gas per thousand cubic feet, so we convert with each state's heat content of delivered gas: price per therm = price per Mcf ÷ (BTU per cubic foot × <?php echo esc_html( $hwm_k( 'cubicFeetPerMcf' ) ); ?> ÷ <?php echo esc_html( $hwm_k( 'btuPerTherm' ) ); ?>).</p>
	<details>
		<summary>Electricity and natural gas prices for all 50 states and DC</summary>
		<?php echo hwm_table( 'computed/energy-prices', 'energyPrices' ); // phpcs:ignore ?>
	</details>
	<p><strong>Limitations.</strong> Your bill's real rate includes fixed charges and time-of-use pricing that a yearly average hides; enter your own rate when you know it. Heat pump efficiency drops in a cold room, and the UEF test does not capture that. Propane prices are not in our data yet.</p>
	<?php $hwm_xcheck( 'operating cost (manufacturer EnergyGuide label yearly cost estimates)' ); ?>
</section>

<section id="tankless-vs-tank-water-heater">
	<h3>Tankless vs tank cost of ownership</h3>
	<p>Cost of ownership adds up what each option costs you over the same number of years:</p>
	<ol>
		<li><strong>Start</strong> = installed cost − any tax credit that applies.</li>
		<li><strong>Each year</strong> add that option's yearly operating cost, from the gas vs electric formula above.</li>
		<li>When an option reaches the end of its expected life inside the period, add its installed cost again for a replacement.</li>
		<li><strong>Break-even year</strong> = the first year the option that costs more up front has become the cheaper one in total.</li>
	</ol>
	<p>The default period is <?php echo esc_html( hwm_value( $hwm_dr, 'ownershipYearsDefault' ) ); ?> years. Expected lives come from the lifespan table above.</p>
	<p><strong>Tax credits.</strong> The federal Energy Efficient Home Improvement Credit (25C) covered <?php echo esc_html( hwm_value( 'incentives', 'credits.0.percent' ) ); ?>% of the cost of a qualifying heat pump water heater, up to $<?php echo esc_html( hwm_value( 'incentives', 'credits.0.maxUsd', 0 ) ); ?>, and <?php echo esc_html( hwm_value( 'incentives', 'credits.1.percent' ) ); ?>% of a qualifying gas water heater, up to $<?php echo esc_html( hwm_value( 'incentives', 'credits.1.maxUsd', 0 ) ); ?>. According to the IRS, it applies only to property placed in service through <?php echo esc_html( hwm_date( hwm_value( 'incentives', 'credits.0.placedInServiceThrough' ) ) ); ?>, so our calculators use a federal credit of $0 for a heater installed now. State and utility rebates vary too much to model; ENERGY STAR's Rebate Finder lists them.</p>
	<p><strong>Limitations.</strong> The comparison ignores price inflation and the time value of money, and it assumes your hot water use stays the same.</p>
	<?php $hwm_xcheck( 'cost of ownership (manufacturer EnergyGuide label and lifetime figures)' ); ?>
</section>

<h2>Where does our data come from?</h2>
<p>Every number lives in a data file with a named source, a link and the date we last checked it. We prefer government sources first, then codes and standards, then manufacturer spec sheets. Other websites' cost guides are never used as the source of a number.</p>
<?php
$hwm_files = array(
	'site'                      => 'Default setpoint and national energy prices',
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
	'regional-cost-index'       => 'Regional price factor by state',
	'incentives'                => 'Federal tax credits',
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

<h2>Which numbers are our own assumptions?</h2>
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
		<tr><td>Age status colors</td><td>Amber from <?php echo esc_html( hwm_value( $hwm_life, 'statusBands.amberFrom', 1 ) ); ?>, red from <?php echo esc_html( hwm_value( $hwm_life, 'statusBands.redFrom', 1 ) ); ?> × expected life</td><td>Gives you time to plan a replacement before the typical end of life.</td></tr>
		<tr><td>Repair-or-replace thresholds</td><td>See the repair or replace section</td><td>Conservative rules of the kind homeowners already use, written down so you can check them.</td></tr>
	</tbody>
</table>
</div>

<h2>How often do we review the numbers?</h2>
<ul>
	<li><strong>Every three months:</strong> energy prices, equipment and installation costs, and tax credits or rebates.</li>
	<li><strong>Once a year:</strong> everything else, including flow rates, incoming water temperatures, efficiency ratings, lifespans and serial number formats.</li>
	<li><strong>Whenever a formula or data file changes:</strong> the published-example and manufacturer cross-checks are run again, and the affected pages get a new "Last updated" date.</li>
</ul>
<p>The "Last checked" date of each source is in the data file table above. A tool's "Last verified" date, shown at the bottom of each calculator, is the day its cross-check was last run.</p>

<h2>What are the limits of these calculators?</h2>
<ul>
	<li>Results are planning estimates. Your pipes, water pressure, local code and the exact model you buy all change the real answer.</li>
	<li>State averages, such as incoming water temperature or energy prices, hide local variation. Use your own measured value or your bill's rate when you have one.</li>
	<li>Some sources are older than we would like. DOE removed several of its consumer Energy Saver pages from energy.gov; where we rely on one of them, we cite an archived copy and say so in the data file.</li>
	<li>We are not plumbers or electricians, and nobody in the trade has reviewed these pages yet. We do not give instructions for gas, venting or electrical work beyond what the codes and manufacturer manuals say. Confirm sizing and code requirements with a licensed professional in your area.</li>
</ul>

<h2>How to report a mistake</h2>
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
