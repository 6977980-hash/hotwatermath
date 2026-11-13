<?php
/**
 * /gas-water-heater-installation-cost/ (STRATEGY §2 item 15): installed price of a gas tank by size and job, with the
 * cost calculator embedded (default: 50-gallon gas tank). Gas work is described for cost only (CLAUDE.md §8b).
 * Numbers: data/computed/gas-water-heater-installation-cost.json and data/computed/40-vs-50-gallon-water-heater.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/gas-water-heater-installation-cost';
$hwm_ic  = 'installation-costs';
$hwm_s   = 'computed/40-vs-50-gallon-water-heater';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_sz  = static fn( string $path ): string => esc_html( hwm_value( $hwm_s, 'figures.gas.' . $path ) );

$related = array( 'water-heater-installation-cost', 'electric-water-heater-installation-cost', 'gas-vs-electric-water-heater', 'tankless-water-heater-installation-cost', 'water-heater-size-calculator' );
?>
<h2>How much does a gas water heater cost to install?</h2>
<p>A 40-gallon gas water heater costs about $<?php echo $hwm_v( 's40.typical' ); // phpcs:ignore ?> installed and a 50-gallon one about $<?php echo $hwm_v( 's50.typical' ); // phpcs:ignore ?>, replacing a gas tank in the same spot at U.S. average prices. A 75-gallon tank costs about $<?php echo $hwm_v( 's75.typical' ); // phpcs:ignore ?>. The labor is the same for every size; the unit price is what changes.</p>
<?php echo hwm_table( $hwm_c, 'jobs' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Gas water heater (storage tank)</dt>
	<dd>A storage tank with a gas burner under it and a flue up the middle that carries the exhaust to a vent. It runs on natural gas or, with a different burner orifice and valve, on propane.</dd>
	<dt>Atmospheric vent vs power vent</dt>
	<dd>A standard tank sends its exhaust up a vertical vent by natural draft. A power-vent tank uses a fan to push it out through a pipe that can run sideways through a wall. Power-vent units cost more and need an outlet.</dd>
</dl>

<h2>What is included in a gas water heater installation?</h2>
<p>A like-for-like gas job is three lines: the unit, labor and the permit with haul-away. For a 50-gallon tank that is about $<?php echo $hwm_v( 's50.unit' ); // phpcs:ignore ?> + $<?php echo $hwm_v( 'labor.typical' ); // phpcs:ignore ?> + $<?php echo $hwm_v( 'permit' ); // phpcs:ignore ?> = $<?php echo $hwm_v( 's50.typical' ); // phpcs:ignore ?>.</p>
<?php echo hwm_table( $hwm_c, 'items' ); // phpcs:ignore ?>
<p>Labor covers shutting off and draining the old tank, disconnecting the gas, water and vent, setting the new tank, reconnecting, testing the gas connection for leaks, checking that the vent draws, and lighting it. It runs about $<?php echo $hwm_v( 'labor.low' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'labor.high' ); // phpcs:ignore ?> in our model, more where access is hard.</p>

<h2>Check a quote: what each line should say</h2>
<p>Ask for an itemized quote and check it against this list. A line missing from the quote may show up later as an extra.</p>
<ul>
	<li><strong>Unit:</strong> brand, model, gallons, first hour rating and whether it is natural gas or propane.</li>
	<li><strong>Labor:</strong> removal of the old tank and installation of the new one.</li>
	<li><strong>Permit and haul-away:</strong> about $<?php echo $hwm_v( 'permit' ); // phpcs:ignore ?> together in our model.</li>
	<li><strong>Code items:</strong> drain pan, seismic straps where required, relief-valve discharge piping (about $<?php echo $hwm_v( 'codeItems' ); // phpcs:ignore ?>).</li>
	<li><strong>Expansion tank:</strong> needed on many closed water systems (about $<?php echo $hwm_v( 'expansion' ); // phpcs:ignore ?>). Size it with our <?php echo hwm_link( 'water-heater-expansion-tank-size', 'expansion tank size calculator' ); // phpcs:ignore ?>.</li>
	<li><strong>Venting:</strong> whether the existing vent is reused or replaced, and why.</li>
	<li><strong>Gas line:</strong> whether the connector, shutoff valve or pipe is replaced.</li>
</ul>

<h2>Gas water heater cost calculator</h2>
<p>Start from a 50-gallon gas tank at U.S. average prices, or change the size, state and job. Tick the extras your quote includes to see if it is in a normal range.</p>
<?php
ob_start();
hwm_part( 'cost-inputs' );
hwm_part( 'calculator', array( 'tool' => 'water-heater-cost-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Gas water heater installation cost calculator' ) );
?>

<h2>How much more does it cost to switch from electric to gas?</h2>
<p>About $<?php echo $hwm_v( 'fromEl.typical' ); // phpcs:ignore ?> for a 50-gallon gas tank at U.S. average prices, against $<?php echo $hwm_v( 's50.typical' ); // phpcs:ignore ?> for a gas-to-gas swap. The difference is a gas line to the heater and a new vent, each several hundred dollars. If the house has no gas service at all, the utility's charge to bring it in comes on top.</p>
<?php echo hwm_table( $hwm_c, 'itemsSwitch' ); // phpcs:ignore ?>
<p>Running a gas line and a vent is licensed work. The pipe size comes from the fuel gas code tables for your home's total gas load and pipe length, and the vent type from the heater's installation manual; the local inspector checks both.</p>

<h2>Gas water heater installation cost by state</h2>
<p>Labor and permit prices follow each state's price level from the Bureau of Economic Analysis; unit prices are the same everywhere in our model.</p>
<details>
	<summary>Gas water heater installation cost in all 50 states and DC</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>

<h2>Which size gas water heater should you buy?</h2>
<p>Size by first hour rating (FHR), the gallons the heater delivers in your busiest hour. A typical 40-gallon gas tank delivers about <?php echo $hwm_sz( 'fhr40' ); // phpcs:ignore ?> gallons, a 50-gallon about <?php echo $hwm_sz( 'fhr50' ); // phpcs:ignore ?> and a 75-gallon about <?php echo $hwm_v( 'fhr75' ); // phpcs:ignore ?>. Going from 40 to 50 gallons costs about $<?php echo $hwm_sz( 'instDiff' ); // phpcs:ignore ?> more installed.</p>
<p>The <?php echo hwm_link( '40-vs-50-gallon-water-heater', '40 vs 50 gallon comparison' ); // phpcs:ignore ?> shows which households each size fits, and the <?php echo hwm_link( 'water-heater-size-calculator', 'water heater size calculator' ); // phpcs:ignore ?> works out the first hour rating for your own busiest hour.</p>

<h2>What does a gas water heater cost to run?</h2>
<p>About $<?php echo $hwm_v( 'runYear' ); // phpcs:ignore ?> a year (about $<?php echo $hwm_v( 'runMonth' ); // phpcs:ignore ?> a month) for a typical household at the U.S. average gas price of $<?php echo esc_html( hwm_value( $hwm_c, 'figures.thermPrice', 2 ) ); ?> per therm, against $<?php echo $hwm_v( 'elRunYear' ); // phpcs:ignore ?> for an electric tank. Gas was the cheaper tank to run in <?php echo $hwm_v( 'gasWins' ); // phpcs:ignore ?> of the <?php echo $hwm_v( 'stateCount' ); // phpcs:ignore ?> places we price. A heater lasts about <?php echo $hwm_v( 'life' ); // phpcs:ignore ?> years in our model, so running cost adds up to more than the install.</p>
<p>See the state-by-state numbers in the <?php echo hwm_link( 'gas-vs-electric-water-heater', 'gas vs electric water heater calculator' ); // phpcs:ignore ?>.</p>

<h2>Gas tank or gas tankless?</h2>
<p>A gas tankless unit costs about $<?php echo $hwm_v( 'tankless.typical' ); // phpcs:ignore ?> installed in place of a gas tank, against $<?php echo $hwm_v( 's50.typical' ); // phpcs:ignore ?> for a new 50-gallon tank, mostly for new venting and a bigger gas line. The full breakdown is on the <?php echo hwm_link( 'tankless-water-heater-installation-cost', 'tankless installation cost page' ); // phpcs:ignore ?>.</p>

<h2>When to call a pro</h2>
<p>Every gas water heater installation is a job for a licensed plumber or gas fitter, and most areas require a permit and inspection. A. O. Smith's own manual says installation and service must be done by a qualified installer, and warns that a heater that is not vented correctly can cause carbon monoxide poisoning (<a href="<?php echo esc_url( (string) hwm_raw( 'troubleshooting', 'references.aos-manual.sourceUrl' ) ); ?>" rel="noopener">A. O. Smith residential gas manual</a>).</p>
<?php hwm_part( 'safety-notice', array( 'gas' => true ) ); ?>

<h2>Limitations</h2>
<p>These are planning estimates for natural gas, standard-vent tanks. Power-vent and propane units, attic or crawlspace installs, and long vent runs cost more. Labor uses national ranges scaled by state price levels, not quotes from your area.</p>
<?php
$faq = array(
	array(
		'q' => 'How much does it cost to install a 50-gallon gas water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.s50.typical' ) . ' at U.S. average prices, replacing a gas tank in the same spot: about $' . hwm_value( $hwm_c, 'figures.s50.unit' ) . ' for the unit, $' . hwm_value( $hwm_c, 'figures.labor.typical' ) . ' for labor and $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for permit and haul-away. The typical range is $' . hwm_value( $hwm_c, 'figures.s50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.s50.high' ) . '.',
	),
	array(
		'q' => 'How much is labor to install a gas water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.labor.typical' ) . ' for a like-for-like swap at U.S. average prices, with a range of $' . hwm_value( $hwm_c, 'figures.labor.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.labor.high' ) . '. New venting, a gas line, code items and an expansion tank are priced separately.',
	),
	array(
		'q' => 'Is a gas water heater more expensive to install than electric?',
		'a' => 'Yes, slightly. A 50-gallon gas tank costs about $' . hwm_value( $hwm_c, 'figures.s50.typical' ) . ' installed against $' . hwm_value( 'computed/electric-water-heater-installation-cost', 'figures.s50.typical' ) . ' for electric, because gas labor includes the gas connection, leak test and vent. Gas usually costs less to run, so it often wins over the heater\'s life.',
	),
	array(
		'q' => 'How much does it cost to convert from electric to gas?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.fromEl.typical' ) . ' for a 50-gallon gas tank at U.S. average prices, including a gas line to the heater and a new vent. If your home has no gas service, the utility charges extra to bring a line and meter in.',
	),
	array(
		'q' => 'Do I need a permit to replace a gas water heater?',
		'a' => 'In most U.S. areas, yes. The inspector checks the gas connection, venting, relief valve and discharge pipe. Our prices include about $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for the permit and haul-away together; your building department sets the real fee.',
	),
	array(
		'q' => 'Can I install a gas water heater myself?',
		'a' => 'Many areas allow only licensed plumbers or gas fitters to connect gas appliances, and nearly all require a permit. A mistake can cause a gas leak or carbon monoxide in the home. Check your local rules and the heater\'s installation manual before deciding.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential gas water heater installation instructions and use and care guide', 'url' => (string) hwm_raw( 'troubleshooting', 'references.aos-manual.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): installed cost and installation adders', 'url' => (string) hwm_raw( $hwm_ic, 'references.doe-storage-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Gas water heater listing prices (unit prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'meta.sourceDate' ) ),
	array( 'publisher' => 'Rheem', 'title' => 'Performance gas water heater spec sheets (first hour rating, burner input)', 'url' => (string) hwm_raw( 'fhr-to-tank-size', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'fhr-to-tank-size', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Average residential natural gas and electricity prices by state', 'url' => (string) hwm_raw( 'energy-prices', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by State, 2024', 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
);
