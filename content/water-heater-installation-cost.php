<?php
/**
 * /water-heater-installation-cost/ (STRATEGY §2 item 13): water heater replacement cost, itemized, by type, size and
 * state, with the cost calculator embedded. Every number comes from data/computed/water-heater-installation-cost.json
 * (npm run build:data, from the same cost model as the calculator) or data/installation-costs.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-installation-cost';
$hwm_ic  = 'installation-costs';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_sc  = (array) hwm_raw( $hwm_c, 'figures.scenarios' );
$hwm_doe = (string) hwm_raw( $hwm_ic, 'references.doe-storage-2024.sourceUrl' );

$related = array( 'water-heater-cost-calculator', 'gas-water-heater-installation-cost', 'electric-water-heater-installation-cost', 'tankless-water-heater-installation-cost', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>How these prices are built.</strong> Unit prices are current retail listings (checked <?php echo esc_html( hwm_date( (string) hwm_value( $hwm_c, 'figures.unitsReviewed' ) ) ); ?>). Labor and extras are anchored to the U.S. Department of Energy's 2024 water heater rule and scaled by each state's price level from the Bureau of Economic Analysis. The same model runs the <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?>, so every page on this site gives the same price for the same job.</p>

<h2>How much does it cost to replace a water heater?</h2>
<p>Replacing a 50-gallon gas tank costs about $<?php echo $hwm_v( 'g50.typical' ); // phpcs:ignore ?> installed at U.S. average prices, with a typical range of $<?php echo $hwm_v( 'g50.low' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'g50.high' ); // phpcs:ignore ?>. A 50-gallon electric tank costs about $<?php echo $hwm_v( 'e50.typical' ); // phpcs:ignore ?> ($<?php echo $hwm_v( 'e50.low' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'e50.high' ); // phpcs:ignore ?>). Both prices include the unit, labor, permit and haul-away of the old heater.</p>
<p>Here is where the money goes on the two most common jobs, a like-for-like swap in the same spot:</p>
<div class="table-wrap" role="region" aria-label="Where the money goes" tabindex="0">
	<table>
		<caption>Where the money goes: 50-gallon replacement, U.S. average (typical prices)</caption>
		<thead><tr><th scope="col">Line</th><th scope="col" class="num">Gas tank ($)</th><th scope="col" class="num">Electric tank ($)</th></tr></thead>
		<tbody>
			<tr><td>The water heater itself</td><td class="num"><?php echo $hwm_v( 'g50.unit' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'e50.unit' ); // phpcs:ignore ?></td></tr>
			<tr><td>Labor (remove the old unit, set and connect the new one)</td><td class="num"><?php echo $hwm_v( 'laborGas.typical' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'laborEl.typical' ); // phpcs:ignore ?></td></tr>
			<tr><td>Permit, haul-away and disposal</td><td class="num"><?php echo $hwm_v( 'permit' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'permit' ); // phpcs:ignore ?></td></tr>
		</tbody>
		<tfoot><tr><th scope="row">Total</th><td class="num"><?php echo $hwm_v( 'g50.typical' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'e50.typical' ); // phpcs:ignore ?></td></tr></tfoot>
	</table>
</div>
<p>The unit is about <?php echo $hwm_v( 'g50.unitShare' ); // phpcs:ignore ?>% of a gas job and <?php echo $hwm_v( 'e50.unitShare' ); // phpcs:ignore ?>% of an electric one. Gas labor runs higher because the installer reconnects the gas line and the vent, tests for leaks and checks the draft.</p>
<dl>
	<dt>Replacement (like for like)</dt>
	<dd>A new heater of the same fuel and roughly the same size, set in the same spot and connected to the existing water lines, gas line or circuit, and vent.</dd>
	<dt>Installed cost</dt>
	<dd>What you pay in total: the unit plus labor, permit, haul-away and any parts the job needs. It is the number to compare between quotes, not the unit price.</dd>
</dl>

<h2>Price your own replacement</h2>
<p>Pick the type and size you are buying, your state and the job. Extras that a code inspector often requires, such as an expansion tank, can be ticked on or off, and the itemized total updates as you go.</p>
<?php
ob_start();
hwm_part( 'cost-inputs' );
hwm_part( 'calculator', array( 'tool' => 'water-heater-cost-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater replacement cost calculator' ) );
?>

<h2>Replacement cost by type and size</h2>
<p>Tank water heaters are the cheapest to replace: about $<?php echo $hwm_v( 'g40.typical' ); // phpcs:ignore ?> for a 40-gallon gas tank and $<?php echo $hwm_v( 'e40.typical' ); // phpcs:ignore ?> for a 40-gallon electric one. A 50-gallon heat pump costs about $<?php echo $hwm_v( 'hp50.typical' ); // phpcs:ignore ?> in place of an electric tank, and a whole-house gas tankless unit about $<?php echo $hwm_v( 'gasTankless.typical' ); // phpcs:ignore ?> in place of an old tankless one.</p>
<?php echo hwm_table( $hwm_c, 'byType' ); // phpcs:ignore -- escaped in the part. ?>
<p>Going up one tank size adds mostly the unit price, because the labor is the same. The heat pump costs more mainly because the unit costs more; how much of that comes back in lower bills is on our <?php echo hwm_link( 'heat-pump-water-heater-cost', 'heat pump water heater cost and payback page' ); // phpcs:ignore ?>.</p>

<h2>Replacement vs installation: what is the difference in cost?</h2>
<p>A replacement reuses what is already there: the spot, water lines, gas line or circuit, and vent. A new installation, or a replacement that changes the location or fuel, adds work. For a 50-gallon gas tank, a plain replacement costs about $<?php echo $hwm_v( 'g50.typical' ); // phpcs:ignore ?>, and the same heater in a new location about $<?php echo $hwm_v( 'newInstall.typical' ); // phpcs:ignore ?>.</p>
<?php echo hwm_table( $hwm_c, 'replaceVsNew' ); // phpcs:ignore ?>
<p>Moving the heater adds about $<?php echo $hwm_v( 'relocation' ); // phpcs:ignore ?> (up to $<?php echo $hwm_v( 'relocationHigh' ); // phpcs:ignore ?>) for new water lines, gas or power and a vent route. Many older installs also lack items today's codes ask for, so even a same-spot replacement can pick up a drain pan, seismic straps or relief-valve piping (about $<?php echo $hwm_v( 'codeItems' ); // phpcs:ignore ?>) and an expansion tank (about $<?php echo $hwm_v( 'expansion' ); // phpcs:ignore ?>).</p>

<h2>What changes the job: the most common replacements</h2>
<p>The fuel and type you go to matter more than the size. Switching fuel or going tankless means new venting, a bigger gas line or a new 240-volt circuit, and each adds several hundred dollars.</p>
<div class="table-wrap" role="region" aria-label="Common replacement jobs" tabindex="0">
	<table>
		<caption>Common replacement jobs and what they include (U.S. average)</caption>
		<thead><tr><th scope="col">Job</th><th scope="col">Extras included</th><th scope="col" class="num">Typical ($)</th><th scope="col" class="num">Range ($)</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_sc as $hwm_row ) : ?>
				<tr><td><?php echo esc_html( (string) $hwm_row['label'] ); ?></td><td><?php echo esc_html( (string) $hwm_row['extras'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_row['typical'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_row['low'] . '–' . (string) $hwm_row['high'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Source: our cost model (retail unit prices, labor and extras from data/installation-costs.json).</p>
</div>
<p>Each switch has its own page with an itemized breakdown: <?php echo hwm_link( 'gas-water-heater-installation-cost', 'gas water heater installation cost' ); // phpcs:ignore ?>, <?php echo hwm_link( 'electric-water-heater-installation-cost', 'electric water heater installation cost' ); // phpcs:ignore ?> and <?php echo hwm_link( 'tankless-water-heater-installation-cost', 'tankless water heater installation cost' ); // phpcs:ignore ?>.</p>

<h2>How much does water heater replacement cost in my state?</h2>
<p>Labor and permit prices follow local price levels; the unit costs about the same everywhere. A 50-gallon gas replacement ranges from about $<?php echo $hwm_v( 'cheapG.typical' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'cheapG.name' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'dearG.typical' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'dearG.name' ); // phpcs:ignore ?>, and an electric one from $<?php echo $hwm_v( 'cheapE.typical' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'cheapE.name' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'dearE.typical' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'dearE.name' ); // phpcs:ignore ?>.</p>
<details>
	<summary>Replacement cost in all 50 states and DC (50-gallon tanks)</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>
<p>The <?php echo esc_html( (string) count( (array) hwm_raw( 'computed/state-cost-index', 'rows' ) ) ); ?> most populous states have their own page with itemized prices, running costs, winter tankless output and rebate status:</p>
<div class="table-wrap" role="region" aria-label="State cost pages" tabindex="0">
	<table>
		<caption>Water heater installation cost by state: 50-gallon tanks, typical installed price</caption>
		<thead><tr><th scope="col">State</th><th scope="col" class="num">Gas ($)</th><th scope="col" class="num">Electric ($)</th><th scope="col" class="num">Heat pump ($)</th><th scope="col">Main fuel in homes</th></tr></thead>
		<tbody>
			<?php foreach ( (array) hwm_raw( 'computed/state-cost-index', 'rows' ) as $hwm_row ) : ?>
				<tr><th scope="row"><?php echo hwm_link( (string) $hwm_row['slug'], 'Water heater cost in ' . (string) $hwm_row['name'] ); // phpcs:ignore ?></th><td class="num"><?php echo esc_html( (string) $hwm_row['g50f'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_row['e50f'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_row['hp50f'] ); ?></td><td><?php echo esc_html( (string) $hwm_row['main'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Source: our cost model scaled by each state's BEA price factor; main fuel from EIA's 2020 Residential Energy Consumption Survey.</p>
</div>
<p>To compare what each type costs to install <em>and</em> run for ten years in every state, ranked, see our <?php echo hwm_link( 'water-heater-installation-cost-index', 'water heater cost index by state' ); // phpcs:ignore ?>.</p>
<p>The price factor is the state's Regional Price Parity from the Bureau of Economic Analysis divided by 100: a factor above 1 means local prices run above the U.S. average, and below 1 means they run under it. Big cities usually run higher than their state average, and rural areas lower.</p>

<h2>Is it worth replacing a water heater before it fails?</h2>
<p>Often yes, if it is near the end of its life. A planned replacement lets you get three quotes, pick the size and type, and avoid emergency rates and water damage. If the heater is still young, a repair such as a new element, thermostat or anode rod is usually far cheaper.</p>
<p>To find out how old yours is, use the <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> with the serial number on the label. To compare a repair quote with this price, use the <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace calculator' ); // phpcs:ignore ?>.</p>

<h2>How to keep the replacement cost down</h2>
<ol>
	<li><strong>Replace like for like when you can.</strong> Staying with the same fuel and spot avoids new venting, gas line or circuit work.</li>
	<li><strong>Size by first hour rating, not habit.</strong> A tank one size too big costs more to buy and to keep hot. Check with the <?php echo hwm_link( 'water-heater-size-calculator', 'water heater size calculator' ); // phpcs:ignore ?>.</li>
	<li><strong>Get itemized quotes.</strong> Ask each installer to list the unit, labor, permit, haul-away and each extra, so you compare the same job.</li>
	<li><strong>Check rebates first.</strong> Utilities often rebate heat pump and high-efficiency units; the rebate can be worth more than a cheaper quote.</li>
	<li><strong>Do not skip the permit.</strong> It is part of the price, and an inspected install protects your home insurance and resale.</li>
</ol>

<h2>When to call a pro</h2>
<p>For any gas water heater, and for any electric one where wiring or the breaker changes, use a licensed plumber or electrician. Gas connections, venting and 240-volt circuits are covered by local code and most areas require a permit and inspection. Ask the local building department what applies where you live.</p>

<h2>Limitations</h2>
<p>These are planning estimates, not quotes. Unit prices are retail listings from one large chain and change with sales. Labor uses national ranges scaled by state price levels, not real quotes from your area. Jobs with unusual access, a long vent run, asbestos, or a heater in an attic or crawlspace can cost much more.</p>
<?php
$faq = array(
	array(
		'q' => 'How much does it cost to replace a 50-gallon water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.g50.typical' ) . ' installed for a gas tank and $' . hwm_value( $hwm_c, 'figures.e50.typical' ) . ' for an electric tank at U.S. average prices, including labor, permit and haul-away. Typical ranges are $' . hwm_value( $hwm_c, 'figures.g50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.g50.high' ) . ' for gas and $' . hwm_value( $hwm_c, 'figures.e50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.e50.high' ) . ' for electric.',
	),
	array(
		'q' => 'How much is labor to replace a water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.laborGas.typical' ) . ' for a gas tank and $' . hwm_value( $hwm_c, 'figures.laborEl.typical' ) . ' for an electric one in a like-for-like swap, at U.S. average prices. Permit and haul-away add about $' . hwm_value( $hwm_c, 'figures.permit' ) . '. New venting, a gas line or a new circuit are extra.',
	),
	array(
		'q' => 'Is it cheaper to replace a gas or electric water heater?',
		'a' => 'Electric is usually cheaper to replace: a 50-gallon electric tank costs about $' . hwm_value( $hwm_c, 'figures.e50.typical' ) . ' installed against $' . hwm_value( $hwm_c, 'figures.g50.typical' ) . ' for gas, because there is no gas line or vent to reconnect. Gas is usually cheaper to run; the gas vs electric calculator shows by how much in your state.',
	),
	array(
		'q' => 'Does a new water heater need a permit?',
		'a' => 'In most U.S. areas, yes, for both gas and electric replacements. The permit lets an inspector check the gas connection, venting, relief valve and any expansion tank. Our prices include about $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for the permit plus haul-away. Your local building department sets the actual fee.',
	),
	array(
		'q' => 'Why is my water heater replacement quote higher than this?',
		'a' => 'Usually because the job needs extras: code items such as a drain pan or straps (about $' . hwm_value( $hwm_c, 'figures.codeItems' ) . '), an expansion tank (about $' . hwm_value( $hwm_c, 'figures.expansion' ) . '), new venting, or a move to a new spot. Emergency and weekend calls and hard-to-reach attics also cost more. Ask for an itemized quote.',
	),
	array(
		'q' => 'How long does a water heater replacement take?',
		'a' => 'A like-for-like tank swap usually takes an installer a few hours, including draining and removing the old tank. Switching fuel, moving the heater or going tankless can take a full day or more, plus any time for the permit and inspection.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): installed cost and installation adders', 'url' => $hwm_doe, 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer gas-fired instantaneous water heaters, final rule (December 26, 2024)', 'url' => (string) hwm_raw( $hwm_ic, 'references.doe-gas-tankless-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by State, 2024', 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater listing prices (unit prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'meta.sourceDate' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Thermal expansion tank and drain pan listing prices (extras)', 'url' => (string) hwm_raw( $hwm_ic, 'references.home-depot-parts.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
);
