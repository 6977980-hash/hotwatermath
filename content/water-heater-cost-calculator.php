<?php
/**
 * /water-heater-cost-calculator/ (STRATEGY §3.5). Accepts ?type=&size= (and &state=&scenario=) so other pages can deep-link.
 * Every number is read from data/*.json or data/computed/water-heater-cost-calculator.json (npm run build:data).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c    = 'computed/water-heater-cost-calculator';
$hwm_ic   = 'installation-costs';
$hwm_cl   = (array) hwm_raw( $hwm_c, 'client' );
$hwm_m    = (array) ( $hwm_cl['model'] ?? array() );
$hwm_def  = (array) ( $hwm_cl['defaults'] ?? array() );
$hwm_din  = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_f    = (array) hwm_raw( $hwm_c, 'figures' );
$hwm_fv   = static function ( string $path ) use ( $hwm_c ): string {
	return esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
};
$hwm_usd  = static fn( $n ): string => esc_html( number_format( (float) $n ) );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_doe1 = (string) hwm_raw( $hwm_ic, 'references.doe-storage-2024.sourceUrl' );
$hwm_doe2 = (string) hwm_raw( $hwm_ic, 'references.doe-gas-tankless-2024.sourceUrl' );
$hwm_types = (array) ( $hwm_m['types'] ?? array() );
$hwm_dtype = (string) ( $hwm_def['type'] ?? 'gas_tank' );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="wc-type">Type</label>
		<select id="wc-type" name="type">
			<?php foreach ( (array) ( $hwm_m['typeOrder'] ?? array() ) as $hwm_t ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_t ); ?>"<?php selected( (string) $hwm_t, $hwm_dtype ); ?>><?php echo esc_html( (string) $hwm_types[ $hwm_t ]['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="wc-size">Size</label>
		<select id="wc-size" name="size">
			<?php foreach ( (array) ( $hwm_types[ $hwm_dtype ]['sizes'] ?? array() ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"<?php selected( (string) $hwm_s['id'], (string) ( $hwm_def['size'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="wc-state">Your state</label>
		<select id="wc-state" name="state">
			<?php foreach ( (array) hwm_raw( $hwm_c, 'stateOptions' ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"<?php selected( (string) $hwm_s['id'], (string) ( $hwm_def['state'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="wc-scenario">The job</label>
		<select id="wc-scenario" name="scenario">
			<?php foreach ( (array) ( $hwm_m['scenarios'] ?? array() ) as $hwm_k => $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_k ); ?>"<?php selected( (string) $hwm_k, (string) ( $hwm_def['scenario'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
<fieldset>
	<legend>Extras your job needs <span class="hint">(the job type ticks the usual ones; change them)</span></legend>
	<?php foreach ( (array) ( $hwm_m['extras'] ?? array() ) as $hwm_k => $hwm_x ) : ?>
		<label class="check"><input type="checkbox" name="<?php echo esc_attr( (string) $hwm_k ); ?>" value="1"<?php checked( ! empty( $hwm_din[ $hwm_k ] ) ); ?>> <?php echo esc_html( (string) $hwm_x['label'] ); ?></label>
	<?php endforeach; ?>
</fieldset>
<p class="hint">Assumed: equipment at current listing prices, labor and extras at our sourced ranges, scaled by your state's price level. Not sure which size you need? <?php echo hwm_link( 'water-heater-size-calculator', 'Size it first' ); // phpcs:ignore ?>.</p>
<?php
echo hwm_tool_data_script( 'hwm-cost-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'tankless-vs-tank-water-heater', 'text' => 'Tankless vs tank cost of ownership', 'why' => 'see whether a pricier unit pays back' ),
	array( 'slug' => 'gas-vs-electric-water-heater', 'text' => 'Gas vs electric running cost', 'why' => 'what each type costs a year in your state' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace calculator', 'why' => 'weigh a repair quote against this price' ),
);
$related = array( 'tankless-vs-tank-water-heater', 'gas-vs-electric-water-heater', 'water-heater-size-calculator', 'repair-or-replace-water-heater' );
$hwm_g40 = (array) ( $hwm_f['gas40'] ?? array() );
$hwm_g50 = (array) ( $hwm_f['gas50'] ?? array() );
$hwm_e40 = (array) ( $hwm_f['el40'] ?? array() );
$hwm_e50 = (array) ( $hwm_f['el50'] ?? array() );
$hwm_hp  = (array) ( $hwm_f['hp50'] ?? array() );
$hwm_gt  = (array) ( $hwm_f['gasTankless'] ?? array() );
$hwm_sc  = (array) ( $hwm_f['scenarios'] ?? array() );
$hwm_lg  = (array) ( $hwm_f['laborGas'] ?? array() );
$hwm_le  = (array) ( $hwm_f['laborElectric'] ?? array() );
$hwm_lt  = (array) ( $hwm_f['laborTankless'] ?? array() );
$hwm_pm  = (array) ( $hwm_f['permit'] ?? array() );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Unit prices are current retail listings (<?php echo esc_html( hwm_date( (string) hwm_raw( 'cost-ranges', 'lastReviewed' ) ) ); ?>). Installation labor and extras are our ranges, each anchored to a figure in the U.S. Department of Energy's 2024 water heater rules. Your state's factor is the federal Regional Price Parity. We check the totals against DOE's average installed costs on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-cost-calculator' ); ?>">our methodology page</a>.</p>

<h2>How much does a new water heater cost?</h2>
<p>A like-for-like replacement of a 50-gallon gas tank costs about $<?php echo esc_html( (string) ( $hwm_g50['typical'] ?? '' ) ); ?> installed at U.S. average prices, and a 50-gallon electric tank about $<?php echo esc_html( (string) ( $hwm_e50['typical'] ?? '' ) ); ?>. A heat pump water heater runs about $<?php echo esc_html( (string) ( $hwm_hp['typical'] ?? '' ) ); ?> and a gas tankless unit about $<?php echo esc_html( (string) ( $hwm_gt['typical'] ?? '' ) ); ?> when it replaces another tankless unit. Roughly half of a tank job is the unit and half is the installation.</p>
<?php echo hwm_table( $hwm_c, 'byUnit' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Unit-only price</dt>
	<dd>What the water heater itself sells for, before tax, delivery or installation. This is the number on a store shelf.</dd>
	<dt>Installed price</dt>
	<dd>The unit plus labor to take out the old heater and put in the new one, plus the permit and disposal of the old unit. This is what a plumber's quote shows.</dd>
</dl>
<p>Tankless units are listed as one "whole house" class because a whole-house unit's price depends far more on its flow rate than on a gallon size. The range column is wide on purpose: it runs from the cheapest listing with the low end of every installation line to the dearest listing with the high end.</p>

<h2>What does a 40-gallon or 50-gallon water heater replacement cost?</h2>
<p>A 40-gallon gas tank replacement costs about $<?php echo esc_html( (string) ( $hwm_g40['typical'] ?? '' ) ); ?> installed (range $<?php echo esc_html( (string) ( $hwm_g40['low'] ?? '' ) ); ?> to $<?php echo esc_html( (string) ( $hwm_g40['high'] ?? '' ) ); ?>), and a 40-gallon electric tank about $<?php echo esc_html( (string) ( $hwm_e40['typical'] ?? '' ) ); ?>. At 50 gallons the figures are about $<?php echo esc_html( (string) ( $hwm_g50['typical'] ?? '' ) ); ?> for gas and $<?php echo esc_html( (string) ( $hwm_e50['typical'] ?? '' ) ); ?> for electric. The installation work is the same for both sizes, so only the unit price changes.</p>
<p>Going up one size usually adds about the difference in unit price and nothing in labor, as long as the larger tank fits the same space and uses the same gas line and vent. A 75-gallon gas tank is different: it often needs a bigger burner, gas line or vent, so ask before you assume it is a simple swap. Use the <?php echo hwm_link( 'water-heater-size-calculator', 'water heater size calculator' ); // phpcs:ignore ?> before you shop, because buying a larger tank than you need costs you twice, once at purchase and again every year in standby heat loss.</p>

<h2>How much is labor to install a water heater?</h2>
<p>Labor for a like-for-like swap is about $<?php echo $hwm_usd( $hwm_lg['typical'] ?? 0 ); // phpcs:ignore ?> for a gas tank and $<?php echo $hwm_usd( $hwm_le['typical'] ?? 0 ); // phpcs:ignore ?> for an electric tank at U.S. average prices, and about $<?php echo $hwm_usd( $hwm_lt['typical'] ?? 0 ); // phpcs:ignore ?> for a gas tankless unit. Gas costs more because the installer also reconnects the gas line and vent and checks for leaks and draft.</p>
<?php echo hwm_table( $hwm_c, 'labor' ); // phpcs:ignore ?>
<p>DOE's own installation estimates come from RSMeans, a paid construction cost database, and DOE publishes only averages. That is why our ranges are written as our estimate, with the DOE figure each one leans on. A plumbers' trade group told DOE that its installation costs were too low for small contractors, so our typical values sit at or above DOE's.</p>

<h2>What extras can add to the price?</h2>
<p>The extras that change a quote most are the ones that build something new: venting, a larger gas line, a new 240-volt circuit or a panel upgrade. A permit and haul-away of the old unit are part of almost every job, about $<?php echo $hwm_usd( $hwm_pm['typical'] ?? 0 ); // phpcs:ignore ?> in DOE's estimate. The table shows each extra with the number it comes from.</p>
<?php echo hwm_table( $hwm_c, 'extras' ); // phpcs:ignore ?>
<p>Many local codes require a thermal expansion tank when the water supply has a check valve or pressure-reducing valve, and earthquake straps in seismic areas. Ask your installer or your building department which items your area requires.</p>

<h2>How much more does switching type or fuel cost?</h2>
<p>Switching costs more than replacing like for like because something new has to be built. Going from a gas tank to a gas tankless unit costs about $<?php echo esc_html( (string) ( $hwm_sc[4]['typical'] ?? '' ) ); ?> at U.S. average prices, mostly for venting and a larger gas line. Moving from a gas tank to a heat pump costs about $<?php echo esc_html( (string) ( $hwm_sc[3]['typical'] ?? '' ) ); ?> because a 240-volt circuit is needed.</p>
<?php echo hwm_table( $hwm_c, 'scenarios' ); // phpcs:ignore ?>
<p>DOE notes that a gas-to-electric switch may also need a panel upgrade, about $<?php echo $hwm_usd( hwm_raw( $hwm_ic, 'extras.panel.low' ) ); // phpcs:ignore ?> to $<?php echo $hwm_usd( hwm_raw( $hwm_ic, 'extras.panel.high' ) ); // phpcs:ignore ?> in its estimate, especially in older homes and homes with gas heating. If your panel is full, tick the panel upgrade in the calculator. Whether a switch pays back over time is what the <?php echo hwm_link( 'tankless-vs-tank-water-heater', 'tankless vs tank cost of ownership calculator' ); // phpcs:ignore ?> works out.</p>

<h2>How much does a water heater cost in your state?</h2>
<p>Labor and extras cost more where prices in general are higher. A like-for-like 50-gallon gas tank job runs from about $<?php echo esc_html( (string) ( $hwm_f['cheapState']['typical'] ?? '' ) ); ?> in <?php echo esc_html( (string) ( $hwm_f['cheapState']['name'] ?? '' ) ); ?> to about $<?php echo esc_html( (string) ( $hwm_f['dearState']['typical'] ?? '' ) ); ?> in <?php echo esc_html( (string) ( $hwm_f['dearState']['name'] ?? '' ) ); ?>. The unit price stays the same everywhere because heaters sell at national prices.</p>
<p>The state factor is the Regional Price Parity published by the U.S. Bureau of Economic Analysis, which ranges from <?php echo $hwm_fv( 'multLow' ); // phpcs:ignore ?> to <?php echo $hwm_fv( 'multHigh' ); // phpcs:ignore ?> times the U.S. average. It measures all consumer prices, so it stands in for local labor rates; it is not a plumber wage survey, and a large city can be dearer than its state average.</p>
<details>
	<summary>Installed cost of a 50-gallon gas tank in all 50 states and DC</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>

<h2>Worked example: replacing a 40-gallon gas tank</h2>
<p>A family replaces a failed 40-gallon gas tank with the same size, in the same spot, at U.S. average prices:</p>
<ol>
	<li>Unit: typical listing price $<?php echo esc_html( (string) ( $hwm_g40['unit'] ?? '' ) ); ?>.</li>
	<li>Labor: $<?php echo $hwm_usd( $hwm_lg['typical'] ?? 0 ); // phpcs:ignore ?>, at the U.S. average price level.</li>
	<li>Permit, haul-away and disposal: $<?php echo $hwm_usd( $hwm_pm['typical'] ?? 0 ); // phpcs:ignore ?>.</li>
	<li>Total: about $<?php echo esc_html( (string) ( $hwm_g40['typical'] ?? '' ) ); ?>, with a realistic range of $<?php echo esc_html( (string) ( $hwm_g40['low'] ?? '' ) ); ?> to $<?php echo esc_html( (string) ( $hwm_g40['high'] ?? '' ) ); ?>.</li>
</ol>
<p>If the local code also asks for an expansion tank, tick it and the total rises by its line. In a state with a higher price factor, labor and extras rise with it, but the unit price does not.</p>

<h2>How do you get a fair quote?</h2>
<p>Get two or three written quotes for the same unit size and the same scope, then compare them line by line with this calculator. A quote far above the high end usually includes work the others left out, or work you may not need.</p>
<ul>
	<li>Ask for the unit's brand, model, size and first hour rating in writing, and its warranty length.</li>
	<li>Ask whether the price includes the permit, haul-away, new connectors, an expansion tank, a drain pan and code updates.</li>
	<li>For a gas unit, ask whether the existing vent and gas line are adequate; for an electric or heat pump unit, whether the circuit and panel are.</li>
	<li>Ask how long the labor warranty is, and who handles the manufacturer warranty claim.</li>
	<li>Check rebates before you sign; the ENERGY STAR Rebate Finder lists utility and state offers for efficient models.</li>
</ul>

<h2>Common mistakes when budgeting</h2>
<ul>
	<li><strong>Using the shelf price as the budget.</strong> Installation roughly doubles the cost of a tank job.</li>
	<li><strong>Assuming tankless is a swap.</strong> Most tank-to-tankless jobs need new venting and a larger gas line.</li>
	<li><strong>Forgetting the panel.</strong> A heat pump or electric tankless unit may need a new circuit or a panel upgrade.</li>
	<li><strong>Oversizing.</strong> A tank larger than your busiest hour needs costs more to buy and to keep hot.</li>
</ul>

<h2>When is this a job for a licensed professional?</h2>
<p>Installing a water heater involves gas, venting, electrical connections and a pressure relief valve, and most places require a permit and an inspection. Have a licensed plumber, and an electrician for new circuits, do the work. This calculator helps you budget and compare quotes; it does not replace an on-site assessment.</p>

<h2>Limitations of this estimate</h2>
<p>Unit prices are listings from one large retailer on one day, mostly from one manufacturer family, so they are a snapshot and are rechecked every three months. Labor ranges are our estimate anchored to DOE averages, because no public source publishes labor by job type. DOE's figures are in 2022 or 2023 dollars. The state factor is an overall price index, not a plumber survey. Emergency, weekend and hard-to-reach jobs cost more.</p>
<?php
$faq = array(
	array(
		'q' => 'How much is a new water heater?',
		'a' => 'At U.S. average prices, about $' . ( $hwm_g50['typical'] ?? '' ) . ' installed for a 50-gallon gas tank and $' . ( $hwm_e50['typical'] ?? '' ) . ' for a 50-gallon electric tank, replacing like for like. The unit alone is about $' . ( $hwm_g50['unit'] ?? '' ) . ' for gas and $' . ( $hwm_e50['unit'] ?? '' ) . ' for electric. Heat pump and tankless jobs cost more.',
	),
	array(
		'q' => 'How much does it cost to replace a 40-gallon water heater?',
		'a' => 'About $' . ( $hwm_g40['typical'] ?? '' ) . ' installed for a 40-gallon gas tank and $' . ( $hwm_e40['typical'] ?? '' ) . ' for a 40-gallon electric tank at U.S. average prices, including the permit and haul-away. The range for gas is $' . ( $hwm_g40['low'] ?? '' ) . ' to $' . ( $hwm_g40['high'] ?? '' ) . ', depending on the unit and your area.',
	),
	array(
		'q' => 'How much does a 50-gallon water heater cost?',
		'a' => 'The unit lists for about $' . ( $hwm_g50['unit'] ?? '' ) . ' (gas) or $' . ( $hwm_e50['unit'] ?? '' ) . ' (electric). Installed like for like, expect about $' . ( $hwm_g50['typical'] ?? '' ) . ' for gas and $' . ( $hwm_e50['typical'] ?? '' ) . ' for electric at U.S. average prices. A 50-gallon heat pump water heater costs about $' . ( $hwm_hp['typical'] ?? '' ) . ' installed.',
	),
	array(
		'q' => 'What is the labor cost to install a water heater?',
		'a' => 'About $' . number_format( (float) ( $hwm_lg['typical'] ?? 0 ) ) . ' for a gas tank and $' . number_format( (float) ( $hwm_le['typical'] ?? 0 ) ) . ' for an electric tank at U.S. average prices, before the permit and haul-away. A gas tankless unit is about $' . number_format( (float) ( $hwm_lt['typical'] ?? 0 ) ) . '. New venting, gas lines or circuits are extra.',
	),
	array(
		'q' => 'Do you need a permit to replace a water heater?',
		'a' => 'In most places, yes: replacing a water heater usually needs a plumbing permit, and gas or electrical changes may need more. DOE counts about $' . number_format( (float) ( $hwm_pm['typical'] ?? 0 ) ) . ' for permitting, removal and disposal in an average job. Check with your local building department.',
	),
	array(
		'q' => 'How much does a tankless water heater cost installed?',
		'a' => 'Replacing a tankless unit with another costs about $' . ( $hwm_gt['typical'] ?? '' ) . ' for gas at U.S. average prices. Switching from a gas tank to tankless costs about $' . ( $hwm_sc[4]['typical'] ?? '' ) . ', because new venting and a larger gas line are usually needed.',
	),
	array(
		'q' => 'Does a water heater cost more in some states?',
		'a' => 'Yes, the installation part does. Using the federal price index, a 50-gallon gas tank job ranges from about $' . ( $hwm_f['cheapState']['typical'] ?? '' ) . ' in ' . ( $hwm_f['cheapState']['name'] ?? '' ) . ' to $' . ( $hwm_f['dearState']['typical'] ?? '' ) . ' in ' . ( $hwm_f['dearState']['name'] ?? '' ) . '. Big cities can be higher than their state average.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): installation costs and installed cost by type', 'url' => $hwm_doe1, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer gas-fired instantaneous water heaters, final rule (December 26, 2024): installation, permit and disposal costs', 'url' => $hwm_doe2, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater listings (equipment prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'lastReviewed' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Thermal expansion tank listing (parts price)', 'url' => (string) hwm_raw( $hwm_ic, 'references.home-depot-parts.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by state, 2024', 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'Rebate Finder', 'url' => (string) hwm_raw( 'incentives', 'rebateFinder.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
