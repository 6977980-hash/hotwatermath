<?php
/**
 * /tankless-water-heater-installation-cost/ (STRATEGY §2 item 14): what a tankless job costs, with the cost calculator
 * embedded and preset to gas tank → gas tankless. Cost only: venting, gas line and electrical sections say what the work
 * is and what it costs, never how to do it (CLAUDE.md §8b). Numbers: data/computed/tankless-water-heater-installation-cost.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/tankless-water-heater-installation-cost';
$hwm_ic  = 'installation-costs';
$hwm_tr  = 'tankless-reference-units';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_btu = static fn( string $i ): string => esc_html( hwm_value( $hwm_tr, 'sizeChart.gasInputsBtuh.' . $i, 0 ) );
$hwm_t50 = esc_html( hwm_value( 'computed/40-vs-50-gallon-water-heater', 'figures.gas.in50' ) );
$hwm_calc = (array) hwm_raw( $hwm_c, 'calculator' );

$related = array( 'tankless-vs-tank-water-heater', 'tankless-water-heater-size-calculator', 'water-heater-installation-cost', 'gas-water-heater-installation-cost', 'water-heater-cost-calculator' );
?>
<p class="notice"><strong>Cost only, not a how-to.</strong> This page explains what each part of a tankless job is and what it typically costs, so you can read a quote. Gas piping, venting and 240-volt wiring are licensed work that local code covers; we do not give installation steps.</p>

<h2>How much does it cost to install a tankless water heater?</h2>
<p>Switching from a gas tank to a whole-house gas tankless water heater costs about $<?php echo $hwm_v( 'gT.typical' ); // phpcs:ignore ?> installed at U.S. average prices, with a typical range of $<?php echo $hwm_v( 'gT.low' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'gT.high' ); // phpcs:ignore ?>. Replacing an old tankless unit with a new one costs about $<?php echo $hwm_v( 'gR.typical' ); // phpcs:ignore ?>, because the venting and gas line are already there.</p>
<p>The unit is only about <?php echo $hwm_v( 'gT.unitShare' ); // phpcs:ignore ?>% of a tank-to-tankless job. The rest is labor and the two upgrades almost every switch needs: a new vent and a bigger gas line.</p>
<?php echo hwm_table( $hwm_c, 'itemsGas' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Tankless water heater</dt>
	<dd>A heater with no storage tank that fires a large burner or electric elements only while a hot tap is open. It is also called an on-demand water heater. It is sized by flow in gallons per minute (GPM), not by gallons stored.</dd>
	<dt>Condensing tankless</dt>
	<dd>A gas tankless unit with a second heat exchanger that pulls extra heat out of the exhaust. It is more efficient and can usually vent through plastic pipe, but it needs a drain for the acidic condensate.</dd>
</dl>

<h2>Tankless installation cost calculator</h2>
<p>The calculator starts on the most common job, gas tank to gas tankless, with the extras it usually needs ticked. Change the fuel, the job or your state, and untick anything your home already has.</p>
<?php
ob_start();
hwm_part( 'cost-inputs', array( 'preset' => (array) ( $hwm_calc['inputs'] ?? array() ) ) );
hwm_part(
	'calculator',
	array(
		'tool'    => 'water-heater-cost-calculator',
		'inputs'  => (string) ob_get_clean(),
		'label'   => 'Tankless water heater installation cost calculator',
		'default' => (array) ( $hwm_calc['default'] ?? array() ),
	)
);
?>

<h2>Tankless installation cost by job</h2>
<p>What you are replacing decides the price more than the brand. Going from a tank to tankless adds new work; replacing tankless with tankless mostly does not.</p>
<?php echo hwm_table( $hwm_c, 'jobs' ); // phpcs:ignore ?>
<p>Compared with simply replacing a 50-gallon gas tank (about $<?php echo $hwm_v( 'gTank.typical' ); // phpcs:ignore ?>), switching to gas tankless costs about $<?php echo $hwm_v( 'gasPremium' ); // phpcs:ignore ?> more. On electric, going tankless costs about $<?php echo $hwm_v( 'elPremium' ); // phpcs:ignore ?> more than a new electric tank (about $<?php echo $hwm_v( 'eTank.typical' ); // phpcs:ignore ?>), mostly because of the panel.</p>

<h2>Why does going tankless need new venting?</h2>
<p>A tankless burner makes far more heat at once than a tank burner, so it usually needs its own vent of the type its manufacturer specifies, and the old tank's flue often does not qualify. A new or changed vent costs about $<?php echo $hwm_v( 'venting' ); // phpcs:ignore ?> in our model, from $<?php echo $hwm_v( 'ventingLow' ); // phpcs:ignore ?> for a short wall vent to $<?php echo $hwm_v( 'ventingHigh' ); // phpcs:ignore ?> for a long run.</p>
<p>The vent material and route come from the manufacturer's installation manual for the exact unit, and the local inspector checks them. Condensing units often vent through plastic pipe, which can be cheaper to run than the stainless steel some non-condensing units need. Ask your installer which one the quote is for.</p>

<h2>Why does a tankless water heater need a bigger gas line?</h2>
<p>Whole-house gas tankless units are rated at about <?php echo $hwm_btu( '0' ); // phpcs:ignore ?> to <?php echo $hwm_btu( '3' ); // phpcs:ignore ?> BTU per hour of gas input, against about <?php echo $hwm_t50; // phpcs:ignore ?> thousand BTU per hour for a typical 50-gallon gas tank. The pipe that fed the tank is often too small to carry that much gas to the new unit along with the furnace and stove.</p>
<p>Upsizing the gas line costs about $<?php echo $hwm_v( 'gasLine' ); // phpcs:ignore ?> in our model ($<?php echo $hwm_v( 'gasLineLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'gasLineHigh' ); // phpcs:ignore ?>), more if the run from the meter is long. The pipe size comes from the fuel gas code tables for your total load and pipe length, and your installer or gas utility may also need a larger meter. That is work for a licensed gas fitter, with a permit.</p>
<p>Not sure how much flow and gas input your home needs? The <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless water heater size calculator' ); // phpcs:ignore ?> works out the GPM and temperature rise for your state and shows which input size covers it.</p>

<h2>What electrical work does an electric tankless water heater need?</h2>
<p>A whole-house electric tankless unit draws a lot of current at once: one manufacturer's range runs from <?php echo $hwm_v( 'kwLow' ); // phpcs:ignore ?> kW (<?php echo $hwm_v( 'ampsLow' ); // phpcs:ignore ?> amps at 240 volts) to <?php echo $hwm_v( 'kwHigh' ); // phpcs:ignore ?> kW (<?php echo $hwm_v( 'ampsHigh' ); // phpcs:ignore ?> amps). Many homes need new circuits and a panel upgrade to supply that.</p>
<?php echo hwm_table( $hwm_c, 'electric' ); // phpcs:ignore ?>
<p>New 240-volt circuits cost about $<?php echo $hwm_v( 'circuit' ); // phpcs:ignore ?> in our model, and a panel upgrade to 200 amps about $<?php echo $hwm_v( 'panel' ); // phpcs:ignore ?> ($<?php echo $hwm_v( 'panelLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'panelHigh' ); // phpcs:ignore ?>). These sizes are for discussion with your electrician; the breaker and wire sizes for your unit come from its installation manual and local code.</p>

<h2>Tankless installation cost by state</h2>
<p>Labor and extras follow local price levels, so a gas tank-to-tankless job costs about $<?php echo $hwm_v( 'cheap.typical' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'cheap.name' ); // phpcs:ignore ?> and $<?php echo $hwm_v( 'dear.typical' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'dear.name' ); // phpcs:ignore ?>, the cheapest and dearest states in our model.</p>
<details>
	<summary>Tankless installation cost in all 50 states and DC</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>

<h2>Is the extra installation cost worth it?</h2>
<p>For most gas households, not on energy savings alone. A gas tankless unit saves about $<?php echo $hwm_v( 'tanklessSaving' ); // phpcs:ignore ?> a year to run for a typical household at U.S. average prices, and over <?php echo $hwm_v( 'years' ); // phpcs:ignore ?> years it came out ahead of a gas tank in only <?php echo $hwm_v( 'gtStatesPay' ); // phpcs:ignore ?> of the places we priced. It makes more sense when you use a lot of hot water, plan to stay a long time, or need endless hot water more than lower bills.</p>
<p>Tankless units last longer, about <?php echo $hwm_v( 'lifeTankless' ); // phpcs:ignore ?> years against <?php echo $hwm_v( 'lifeTank' ); // phpcs:ignore ?> for a tank in our model, which narrows the gap if you stay. Run your own numbers in the <?php echo hwm_link( 'tankless-vs-tank-water-heater', 'tankless vs tank cost of ownership calculator' ); // phpcs:ignore ?>.</p>

<h2>Mistakes that raise a tankless quote later</h2>
<ul>
	<li><strong>Pricing the unit, not the job.</strong> The unit is about <?php echo $hwm_v( 'gT.unitShare' ); // phpcs:ignore ?>% of the cost when you switch from a tank.</li>
	<li><strong>Buying too small.</strong> A unit sized for one shower in a cold-water state runs out of flow with two. Check with the <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless size calculator' ); // phpcs:ignore ?> before you get quotes.</li>
	<li><strong>Assuming the old gas line is fine.</strong> Ask every installer whether the quote includes a gas line check and upsizing.</li>
	<li><strong>Forgetting the condensate drain.</strong> A condensing unit needs a drain nearby, or a pump.</li>
	<li><strong>Skipping descaling.</strong> In hard-water areas the manufacturer usually requires periodic flushing to keep the warranty.</li>
</ul>

<h2>When to call a pro</h2>
<p>Always. A tankless install changes the gas line, the vent or the electrical service, and each is licensed work that needs a permit and inspection in most areas. Get at least two itemized quotes that list the unit, labor, vent, gas line or electrical work, and permit separately.</p>

<h2>Limitations</h2>
<p>These are planning estimates. The unit price is a typical retail listing for a whole-house model, and labor and extras are national ranges scaled by state price levels. Long vent runs, a gas meter upgrade, a panel that cannot be upgraded, or a unit in a finished wall can cost much more.</p>
<?php
$faq = array(
	array(
		'q' => 'How much does it cost to install a tankless water heater?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.gT.typical' ) . ' to switch from a gas tank to a whole-house gas tankless unit at U.S. average prices, including new venting, a gas line upgrade and the permit. Replacing an existing gas tankless unit costs about $' . hwm_value( $hwm_c, 'figures.gR.typical' ) . '.',
	),
	array(
		'q' => 'Why is tankless installation so expensive?',
		'a' => 'Because switching from a tank usually needs a new sealed vent (about $' . hwm_value( $hwm_c, 'figures.venting' ) . ') and a bigger gas line (about $' . hwm_value( $hwm_c, 'figures.gasLine' ) . '), on top of more labor than a tank swap. Electric tankless units often need new circuits and a panel upgrade instead.',
	),
	array(
		'q' => 'How much does an electric tankless water heater cost to install?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.eT.typical' ) . ' to replace an electric tank with a whole-house electric tankless unit at U.S. average prices, including new 240-volt circuits and a panel upgrade. The unit is cheap; the electrical work is most of the cost.',
	),
	array(
		'q' => 'Is labor for a tankless water heater more than for a tank?',
		'a' => 'Yes. Our model uses about $' . hwm_value( $hwm_c, 'figures.labor.typical' ) . ' of labor for a gas tankless job against $' . hwm_value( 'computed/water-heater-installation-cost', 'figures.laborGas.typical' ) . ' for a gas tank, before any venting or gas line work. Mounting the unit on a wall and repiping take longer than setting a tank.',
	),
	array(
		'q' => 'Does a tankless water heater need a new gas line?',
		'a' => 'Often. Whole-house units take about ' . hwm_value( $hwm_tr, 'sizeChart.gasInputsBtuh.0', 0 ) . ' to ' . hwm_value( $hwm_tr, 'sizeChart.gasInputsBtuh.3', 0 ) . ' BTU per hour, several times a tank, so the old pipe may be too small. A licensed installer sizes it from the fuel gas code tables for your total gas load and pipe length.',
	),
	array(
		'q' => 'Does a tankless water heater pay for its installation cost?',
		'a' => 'Rarely for a typical gas household at U.S. average prices: it saves about $' . hwm_value( $hwm_c, 'figures.tanklessSaving' ) . ' a year, which takes a long time to cover the extra cost of switching. It pays faster with heavy hot water use or when you are replacing a tankless unit anyway.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer gas-fired instantaneous water heaters, final rule (December 26, 2024): installed cost and venting', 'url' => (string) hwm_raw( $hwm_ic, 'references.doe-gas-tankless-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ic, 'references.doe-storage-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'Rinnai', 'title' => 'Tankless water heater model chart and specifications (gas input ratings)', 'url' => (string) hwm_raw( $hwm_tr, 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_tr, 'meta.sourceDate' ) ),
	array( 'publisher' => 'Rheem', 'title' => 'Performance tankless electric spec sheet (THD-PTE): power, current, breakers and wire', 'url' => (string) hwm_raw( 'electrical', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'electrical', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by State, 2024', 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Tankless water heater listing prices (unit prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'meta.sourceDate' ) ),
);
