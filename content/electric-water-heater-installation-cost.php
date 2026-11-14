<?php
/**
 * /electric-water-heater-installation-cost/ (STRATEGY §2 item 16): installed price of an electric tank by size and job,
 * with the cost calculator embedded and preset to a 50-gallon electric tank. Electrical work is described for cost only
 * (CLAUDE.md §8b). Numbers: data/computed/electric-water-heater-installation-cost.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c    = 'computed/electric-water-heater-installation-cost';
$hwm_ic   = 'installation-costs';
$hwm_s    = 'computed/40-vs-50-gallon-water-heater';
$hwm_v    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_sz   = static fn( string $path ): string => esc_html( hwm_value( $hwm_s, 'figures.el.' . $path ) );
$hwm_calc = (array) hwm_raw( $hwm_c, 'calculator' );
$hwm_es   = (string) hwm_raw( 'heat-pump', 'references.es-install.sourceUrl' );

$related = array( 'water-heater-installation-cost', 'gas-water-heater-installation-cost', 'heat-pump-water-heater-cost', 'gas-vs-electric-water-heater', 'water-heater-size-calculator' );
?>
<h2>How much does it cost to install an electric water heater?</h2>
<p>An electric water heater costs about $<?php echo $hwm_v( 's40.typical' ); // phpcs:ignore ?> installed for 40 gallons and $<?php echo $hwm_v( 's50.typical' ); // phpcs:ignore ?> for 50 gallons at U.S. average prices, replacing an electric tank like for like. That is the cheapest water heater job there is: no gas line, no vent, and the existing 240-volt circuit is reused.</p>
<p>Start the calculator on that job, or change the size, your state and what you are replacing:</p>
<?php
ob_start();
hwm_part( 'cost-inputs', array( 'preset' => (array) ( $hwm_calc['inputs'] ?? array() ) ) );
hwm_part(
	'calculator',
	array(
		'tool'    => 'water-heater-cost-calculator',
		'inputs'  => (string) ob_get_clean(),
		'label'   => 'Electric water heater installation cost calculator',
		'default' => (array) ( $hwm_calc['default'] ?? array() ),
	)
);
?>

<h2>Electric water heater cost by job</h2>
<p>What you replace sets the price. Electric to electric is the cheapest job; coming from gas adds a new circuit, and a heat pump or tankless unit costs more again.</p>
<?php echo hwm_table( $hwm_c, 'jobs' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Electric resistance water heater</dt>
	<dd>A storage tank heated by electric elements that work like the coil in a toaster. It turns electricity into heat one for one, so it is simple and cheap to buy but costly to run.</dd>
	<dt>240-volt circuit</dt>
	<dd>A dedicated circuit from the electrical panel with a double-pole breaker, used by water heaters, dryers and ranges. An electric tank needs one; a gas tank does not.</dd>
</dl>

<h2>Itemized: what a like-for-like replacement includes</h2>
<p>For a 50-gallon electric tank the price is the unit (about $<?php echo $hwm_v( 's50.unit' ); // phpcs:ignore ?>), labor (about $<?php echo $hwm_v( 'labor.typical' ); // phpcs:ignore ?>) and the permit with haul-away (about $<?php echo $hwm_v( 'permit' ); // phpcs:ignore ?>).</p>
<?php echo hwm_table( $hwm_c, 'items' ); // phpcs:ignore ?>
<p>Labor runs $<?php echo $hwm_v( 'labor.low' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'labor.high' ); // phpcs:ignore ?> in our model. It is lower than for gas because there is no gas connection to make or vent to check. Extras that inspectors often ask for: an expansion tank (about $<?php echo $hwm_v( 'expansion' ); // phpcs:ignore ?>) and a drain pan or straps.</p>

<h2>How much does it cost to switch from gas to electric?</h2>
<p>About $<?php echo $hwm_v( 'fromGas.typical' ); // phpcs:ignore ?> for a 50-gallon electric tank at U.S. average prices, because the installer has to run a new 240-volt circuit (about $<?php echo $hwm_v( 'circuit' ); // phpcs:ignore ?>, $<?php echo $hwm_v( 'circuitLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'circuitHigh' ); // phpcs:ignore ?>) and cap the gas line. If the panel has no room, a panel upgrade adds about $<?php echo $hwm_v( 'panel' ); // phpcs:ignore ?>, for a total near $<?php echo $hwm_v( 'fromGasPanel.typical' ); // phpcs:ignore ?>.</p>
<p>The electrician decides the breaker and wire size from the heater's installation manual and local code. ENERGY STAR notes that a 240-volt heat pump water heater typically uses a <?php echo esc_html( hwm_value( 'heat-pump', 'electrical.breakerAmps' ) ); ?>-amp breaker and <?php echo esc_html( hwm_value( 'heat-pump', 'electrical.wireGauge' ) ); ?>-gauge wire, "just like a standard electric storage water heater", so the same circuit can later serve a heat pump.</p>

<h2>Electric tank or heat pump: which costs less?</h2>
<p>A 50-gallon heat pump costs about $<?php echo $hwm_v( 'hpExtra' ); // phpcs:ignore ?> more installed than a new electric tank, and saves about $<?php echo $hwm_v( 'hpSaving' ); // phpcs:ignore ?> a year at U.S. average prices. That makes the heat pump the better buy for most electric homes that have room for it.</p>
<div class="table-wrap" role="region" aria-label="Electric tank vs heat pump" tabindex="0">
	<table>
		<caption>Electric tank vs heat pump, 50 gallons (typical household, U.S. average prices)</caption>
		<thead><tr><th scope="col">Item</th><th scope="col" class="num">Electric tank</th><th scope="col" class="num">Heat pump</th></tr></thead>
		<tbody>
			<tr><td>Installed, replacing an electric tank ($)</td><td class="num"><?php echo $hwm_v( 's50.typical' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'hp.typical' ); // phpcs:ignore ?></td></tr>
			<tr><td>Running cost a year ($)</td><td class="num"><?php echo $hwm_v( 'runYear' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'hpRunYear' ); // phpcs:ignore ?></td></tr>
		</tbody>
	</table>
	<p class="table-source">Source: our cost model; running cost uses EIA electricity prices.</p>
</div>
<p>The payback for your state and household is on the <?php echo hwm_link( 'heat-pump-water-heater-cost', 'heat pump water heater cost and savings page' ); // phpcs:ignore ?>.</p>

<h2>Why can't you buy a large electric tank?</h2>
<p>Federal efficiency rules for tanks above <?php echo $hwm_v( 'maxElectricGallons' ); // phpcs:ignore ?> gallons set a minimum that only a heat pump can meet. So standard electric tanks stop at <?php echo $hwm_v( 'maxElectricGallons' ); // phpcs:ignore ?> gallons. If your household needs more hot water than that, the choice is a heat pump, two tanks, or switching fuel.</p>

<?php hwm_part( 'doe-2029', array( 'heading' => false, 'lead' => 'The line moves down for heaters made from 2029.' ) ); ?>

<h2>Which size electric water heater should you buy?</h2>
<p>Size by first hour rating. A typical 40-gallon electric tank delivers about <?php echo $hwm_sz( 'fhr40' ); // phpcs:ignore ?> gallons in the busiest hour and a 50-gallon one about <?php echo $hwm_sz( 'fhr50' ); // phpcs:ignore ?>, less than gas tanks of the same size (<?php echo esc_html( hwm_value( $hwm_s, 'figures.gas.fhr40' ) ); ?> and <?php echo esc_html( hwm_value( $hwm_s, 'figures.gas.fhr50' ) ); ?>) because a <?php echo $hwm_v( 'elementKw' ); // phpcs:ignore ?> kW element reheats water more slowly than a gas burner. Going from 40 to 50 gallons costs about $<?php echo $hwm_sz( 'instDiff' ); // phpcs:ignore ?> more.</p>
<p>See the <?php echo hwm_link( '40-vs-50-gallon-water-heater', '40 vs 50 gallon comparison' ); // phpcs:ignore ?> or work it out with the <?php echo hwm_link( 'water-heater-size-calculator', 'water heater size calculator' ); // phpcs:ignore ?>.</p>

<h2>Electric water heater installation cost by state</h2>
<p>Labor and permit prices follow each state's price level; unit prices are the same everywhere in our model.</p>
<details>
	<summary>Electric water heater installation cost in all 50 states and DC</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>

<h2>What does an electric water heater cost to run?</h2>
<p>About $<?php echo $hwm_v( 'runYear' ); // phpcs:ignore ?> a year (about $<?php echo $hwm_v( 'runMonth' ); // phpcs:ignore ?> a month) for a typical household, using about <?php echo $hwm_v( 'kwh' ); // phpcs:ignore ?> kWh at the U.S. average price of $<?php echo esc_html( hwm_value( $hwm_c, 'figures.kwhPrice', 4 ) ); ?> per kWh. Over a <?php echo $hwm_v( 'life' ); // phpcs:ignore ?>-year life that is several times the installed price, which is why the running cost matters more than the quote.</p>

<h2>Common mistakes when pricing an electric water heater</h2>
<ul>
	<li><strong>Comparing unit prices instead of installed prices.</strong> The unit is only about <?php echo $hwm_v( 's50.unitShare' ); // phpcs:ignore ?>% of a like-for-like job.</li>
	<li><strong>Assuming any circuit will do when switching from gas.</strong> The heater needs its own 240-volt circuit sized to its manual; a quote without one is not the full job.</li>
	<li><strong>Buying a bigger electric tank instead of a heat pump.</strong> Above the federal size limit, only heat pumps are sold, and they cost far less to run.</li>
	<li><strong>Ignoring the running cost.</strong> Over the heater's life, the electricity costs much more than the installation.</li>
</ul>

<h2>When to call a pro</h2>
<p>Replacing an electric tank means working on a 240-volt circuit; a new circuit or panel work is a job for a licensed electrician, and most areas require a permit for both.</p>
<?php hwm_part( 'safety-notice', array( 'power' => true ) ); ?>

<h2>Limitations</h2>
<p>These are planning estimates for standard electric tanks. Labor uses national ranges scaled by state price levels. Old wiring, a full panel, a long run from the panel or a heater in a tight spot can raise the price.</p>
<?php
$faq = array(
	array(
		'q' => 'How much does it cost to install a 50-gallon electric water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.s50.typical' ) . ' at U.S. average prices when it replaces an electric tank: about $' . hwm_value( $hwm_c, 'figures.s50.unit' ) . ' for the unit, $' . hwm_value( $hwm_c, 'figures.labor.typical' ) . ' for labor and $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for permit and haul-away. The typical range is $' . hwm_value( $hwm_c, 'figures.s50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.s50.high' ) . '.',
	),
	array(
		'q' => 'How much is labor to install an electric water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.labor.typical' ) . ' for a like-for-like swap at U.S. average prices, with a range of $' . hwm_value( $hwm_c, 'figures.labor.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.labor.high' ) . '. A new circuit, a panel upgrade or an expansion tank costs extra.',
	),
	array(
		'q' => 'How much does it cost to switch from gas to an electric water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.fromGas.typical' ) . ' for a 50-gallon electric tank, including a new 240-volt circuit, at U.S. average prices. If the panel needs an upgrade, the total is closer to $' . hwm_value( $hwm_c, 'figures.fromGasPanel.typical' ) . '.',
	),
	array(
		'q' => 'Is an electric water heater cheaper to install than gas?',
		'a' => 'Yes, when you replace like for like: a 50-gallon electric tank costs about $' . hwm_value( $hwm_c, 'figures.s50.typical' ) . ' installed against $' . hwm_value( 'computed/gas-water-heater-installation-cost', 'figures.s50.typical' ) . ' for gas. But an electric tank costs about $' . hwm_value( $hwm_c, 'figures.runYear' ) . ' a year to run, more than gas in every state we price.',
	),
	array(
		'q' => 'Should I buy a heat pump instead of an electric tank?',
		'a' => 'Usually, if you have the space. A 50-gallon heat pump costs about $' . hwm_value( $hwm_c, 'figures.hpExtra' ) . ' more installed and saves about $' . hwm_value( $hwm_c, 'figures.hpSaving' ) . ' a year against an electric tank at U.S. average prices. It needs a room with enough air and a drain for condensate.',
	),
	array(
		'q' => 'Do I need a permit to replace an electric water heater?',
		'a' => 'In most U.S. areas, yes. The inspector checks the wiring connection, the relief valve and its discharge pipe, and any expansion tank. Our prices include about $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for the permit and haul-away together.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): installed cost and installation adders', 'url' => (string) hwm_raw( $hwm_ic, 'references.doe-storage-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'ENERGY STAR (U.S. EPA)', 'title' => 'Best Practices: Installing Heat Pump Water Heaters (2024): breaker and wire size', 'url' => $hwm_es, 'accessed' => (string) hwm_raw( 'heat-pump', 'references.es-install.checked' ) ),
	array( 'publisher' => 'Electronic Code of Federal Regulations', 'title' => '10 CFR 430.32(d): energy conservation standards for water heaters', 'url' => (string) hwm_raw( 'uef-ranges', 'federalMinimums.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Electric water heater listing prices (unit prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Average residential electricity prices by state', 'url' => (string) hwm_raw( 'energy-prices', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by State, 2024', 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
);
