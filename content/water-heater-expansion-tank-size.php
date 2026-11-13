<?php
/**
 * /water-heater-expansion-tank-size/ (STRATEGY §2 item 18, §3.8): thermal expansion tank size calculator.
 * Math: assets/js/formulas.js expansionTankSizing (AMTROL's published method) via assets/js/expansion-tank.js.
 * Numbers: data/computed/water-heater-expansion-tank-size.json and data/expansion-tanks.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-expansion-tank-size';
$hwm_et  = 'expansion-tanks';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_et, 'references.' . $key . '.sourceUrl' );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_d   = (array) ( $hwm_cl['defaults'] ?? array() );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="et-gallons">Water heater size (gallons) <span class="hint">On the rating label.</span></label>
		<input id="et-gallons" name="gallons" type="text" inputmode="decimal" maxlength="5" value="<?php echo esc_attr( (string) ( $hwm_d['heaterGallons'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="et-temp">Temperature setting (°F) <span class="hint">Assumed; change if you know yours.</span></label>
		<input id="et-temp" name="temp" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( (string) ( $hwm_d['tempF'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="et-supply">Water supply pressure (psi) <span class="hint">Read a gauge on a hose bib; assumed until you do.</span></label>
		<input id="et-supply" name="supply" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( (string) ( $hwm_d['supplyPsi'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="et-max">Maximum pressure (psi) <span class="hint">The relief valve setting; assumed.</span></label>
		<input id="et-max" name="max" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( (string) ( $hwm_d['maxPsi'] ?? '' ) ); ?>" autocomplete="off">
	</div>
</div>
<?php
echo hwm_tool_data_script( 'hwm-et-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-installation-cost', 'text' => 'Water heater replacement cost', 'why' => 'what the whole job costs, expansion tank included' ),
	array( 'slug' => 'water-heater-leaking', 'text' => 'Water heater leaking', 'why' => 'a dripping relief valve can mean uncontrolled expansion' ),
	array( 'slug' => 'water-heater-size-calculator', 'text' => 'Water heater size calculator', 'why' => 'size the heater itself' ),
);
$related = array( 'water-heater-installation-cost', 'water-heater-size-calculator', 'water-heater-leaking', 'water-heater-cost-calculator' );
$hwm_t0  = esc_html( hwm_value( $hwm_c, 'figures.d0.tank' ) );
$hwm_t1  = esc_html( hwm_value( $hwm_c, 'figures.r80.safer' ) );
$hwm_tf  = static fn( int $i ): string => esc_html( hwm_value( $hwm_et, 'expansionFactors.rows.' . $i . '.tempF' ) );
?>
<p class="notice"><strong>How this calculator is checked.</strong> It uses AMTROL's published sizing method and tank volumes. We ran all <?php echo $hwm_v( 'cells' ); // phpcs:ignore ?> cells of AMTROL's quick sizing chart through it: <?php echo $hwm_v( 'exact' ); // phpcs:ignore ?> give the same tank and <?php echo $hwm_v( 'saferMatch' ); // phpcs:ignore ?> match our "safer" pick. The one difference is explained on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-expansion-tank-size' ); ?>">our methodology page</a>.</p>

<h2>What size expansion tank do I need for my water heater?</h2>
<p>For most homes, a <?php echo $hwm_t0; // phpcs:ignore ?> thermal expansion tank. A <?php echo $hwm_v( 'heater' ); // phpcs:ignore ?>-gallon heater set to <?php echo $hwm_v( 'temp' ); // phpcs:ignore ?> °F with <?php echo $hwm_v( 'supply' ); // phpcs:ignore ?> psi water pressure needs about <?php echo $hwm_v( 'd0.required' ); // phpcs:ignore ?> gallons of tank volume, which the smallest common tank covers. Larger heaters, hotter settings and higher supply pressure push you to a <?php echo $hwm_t1; // phpcs:ignore ?>.</p>
<?php echo hwm_table( $hwm_c, 'quick' ); // phpcs:ignore -- escaped in the part. ?>
<p>"Safer" means the smaller tank is within <?php echo $hwm_v( 'headroomPct' ); // phpcs:ignore ?>% of its limit, so we name the next size too. AMTROL's own chart picks the larger tank in most such borderline cases.</p>
<dl>
	<dt>Thermal expansion tank</dt>
	<dd>A small steel tank on the cold water line by the water heater, with an air cushion behind a rubber diaphragm. When heating makes the water grow, the extra water pushes into the tank instead of raising the pressure in your pipes.</dd>
	<dt>Closed water system</dt>
	<dd>A plumbing system with a one-way device, such as a check valve, backflow preventer or pressure-reducing valve, between the house and the water main. Expanded water cannot flow back to the main, so the pressure rises unless something absorbs it.</dd>
</dl>

<h2>Do I need an expansion tank for my water heater?</h2>
<p>You need one if your home is a closed system. The Washington State Department of Health explains that when a water utility requires a check valve or backflow preventer after the meter, "it is essential that a T &amp; P Valve and thermal expansion tank be properly installed". AMTROL lists backflow preventers, check valves and pressure-reducing valves as the one-way devices that close a system.</p>
<p>Signs of uncontrolled expansion are a relief valve that drips after the heater runs, pressure that climbs on a gauge overnight, and faucets that spurt when first opened. Whether your local code requires one when you replace a water heater is set locally; ask your building department.</p>

<h2>How expansion tank size is worked out</h2>
<p>Water grows as it heats. AMTROL's table puts the growth from <?php echo esc_html( hwm_value( $hwm_et, 'expansionFactors.startTempF' ) ); ?> °F at about <?php echo $hwm_v( 'f120' ); // phpcs:ignore ?>% at <?php echo $hwm_tf( 1 ); // phpcs:ignore ?> °F and <?php echo $hwm_v( 'f140' ); // phpcs:ignore ?>% at <?php echo $hwm_tf( 3 ); // phpcs:ignore ?> °F. The tank must hold that extra water while the pressure stays below the relief valve setting.</p>
<?php echo hwm_table( $hwm_c, 'factors' ); // phpcs:ignore ?>
<p>The calculator follows AMTROL's method in three steps:</p>
<ol>
	<li><strong>Expanded water</strong> = heater gallons × expansion factor.</li>
	<li><strong>Design pressure factor</strong> = (maximum pressure + <?php echo esc_html( hwm_value( $hwm_et, 'atmosphericPsi' ) ); ?>) ÷ (maximum pressure − supply pressure), in psi.</li>
	<li><strong>Tank volume needed</strong> = expanded water × design pressure factor. Pick the smallest tank whose total volume is at least that and whose acceptance volume is at least the expanded water.</li>
</ol>
<p>For the default case that is <?php echo $hwm_v( 'd0.expanded' ); // phpcs:ignore ?> gallons × <?php echo $hwm_v( 'd0.dpf' ); // phpcs:ignore ?> = <?php echo $hwm_v( 'd0.required' ); // phpcs:ignore ?> gallons, so a <?php echo $hwm_v( 'd0.tank' ); // phpcs:ignore ?>.</p>

<h2>Common expansion tank sizes</h2>
<p>Residential thermal expansion tanks come in a few sizes. The acceptance volume, the most water the tank can take in, matters as much as the total volume; a <?php echo $hwm_t0; // phpcs:ignore ?> accepts only <?php echo esc_html( hwm_value( $hwm_et, 'tanks.0.acceptanceGallons' ) ); ?> gallons.</p>
<?php echo hwm_table( $hwm_c, 'tanks' ); // phpcs:ignore ?>
<p>These are one maker's models. Other brands list their own acceptance volumes; check the spec sheet of the tank you buy.</p>

<h2>What pressure should an expansion tank be set to?</h2>
<p>Set the tank's air charge to your home's static water pressure. AMTROL says the air pressure "should be equal to static line pressure", and that tanks ship with a factory charge that must be matched to your supply before installation. Measure the pressure with a gauge on an outdoor hose bib with no water running.</p>
<p>If your supply is above <?php echo $hwm_v( 'maxSupply' ); // phpcs:ignore ?> psi, the bigger problem is the pressure itself. A. O. Smith advises a pressure-reducing valve set to <?php echo $hwm_v( 'prvLow' ); // phpcs:ignore ?> to <?php echo $hwm_v( 'prvHigh' ); // phpcs:ignore ?> psi, and Watts notes supply pressure typically should not exceed <?php echo $hwm_v( 'maxSupply' ); // phpcs:ignore ?> psi under the Uniform Plumbing Code.</p>

<h2>Why a different calculator may give a bigger tank</h2>
<p>Sizing tools use different assumptions. Watts' calculator assumes warmer starting water and a lower final pressure, set from your supply pressure rather than the relief valve, which gives a larger tank than AMTROL's method with a <?php echo esc_html( (string) ( $hwm_d['maxPsi'] ?? '' ) ); ?> psi relief valve. Neither is wrong; ours follows AMTROL and lets you lower the maximum pressure if you want more margin.</p>
<p>Lowering the maximum to <?php echo esc_html( hwm_value( $hwm_et, 'relief.commonPsi.0' ) ); ?> psi, a common relief valve setting, raises the default case only to <?php echo $hwm_v( 'r50max125.required' ); // phpcs:ignore ?> gallons, still a <?php echo $hwm_v( 'r50max125.tank' ); // phpcs:ignore ?>.</p>

<h2>How much does an expansion tank cost?</h2>
<p>The tank itself lists for about $<?php echo $hwm_v( 'partLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'partHigh' ); // phpcs:ignore ?> for the small residential models below. Installed by a plumber, it costs about $<?php echo $hwm_v( 'costTypical' ); // phpcs:ignore ?> in our cost model ($<?php echo $hwm_v( 'costLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'costHigh' ); // phpcs:ignore ?>), less when it goes in with a new water heater.</p>
<?php echo hwm_table( $hwm_c, 'prices' ); // phpcs:ignore ?>

<h2>Common mistakes</h2>
<ul>
	<li><strong>Leaving the factory precharge.</strong> A tank charged far below your water pressure is partly full of water before it starts and absorbs less.</li>
	<li><strong>Putting it on the wrong line.</strong> AMTROL says it goes on the cold water supply, after the backflow preventer.</li>
	<li><strong>Sizing by heater gallons only.</strong> Supply pressure changes the answer; at <?php echo $hwm_v( 'maxSupply' ); // phpcs:ignore ?> psi a <?php echo $hwm_v( 'heater' ); // phpcs:ignore ?>-gallon heater needs <?php echo $hwm_v( 'r50at80.required' ); // phpcs:ignore ?> gallons of tank volume, not <?php echo $hwm_v( 'd0.required' ); // phpcs:ignore ?>.</li>
	<li><strong>Treating a dripping relief valve as the fault.</strong> It may be doing its job against expansion pressure; the fix can be the expansion tank or a pressure-reducing valve.</li>
</ul>

<h2>When to call a pro</h2>
<p>Installing an expansion tank means cutting into the water line and, in many areas, a permit. If your water pressure is above <?php echo $hwm_v( 'maxSupply' ); // phpcs:ignore ?> psi or the relief valve keeps opening, have a licensed plumber check the whole system, not just the tank.</p>

<h2>Limitations</h2>
<p>The calculator sizes for one storage water heater, starting from <?php echo esc_html( hwm_value( $hwm_et, 'expansionFactors.startTempF' ) ); ?> °F water as AMTROL does. It does not size tanks for boilers, well systems or several heaters in a row. Tank volumes are one manufacturer's; another brand's tank of the same nominal size may accept less.</p>
<?php
$faq = array(
	array(
		'q' => 'What size expansion tank do I need for a 50-gallon water heater?',
		'a' => 'Usually a ' . hwm_value( $hwm_c, 'figures.d0.tank' ) . '. At ' . hwm_value( $hwm_c, 'figures.temp' ) . ' °F and ' . hwm_value( $hwm_c, 'figures.supply' ) . ' psi supply, a 50-gallon heater needs about ' . hwm_value( $hwm_c, 'figures.d0.required' ) . ' gallons of tank volume. At ' . hwm_value( $hwm_c, 'figures.maxSupply' ) . ' psi it needs ' . hwm_value( $hwm_c, 'figures.r50at80.required' ) . ' gallons, still a ' . hwm_value( $hwm_c, 'figures.r50at80.tank' ) . '. Hotter settings may need a ' . hwm_value( $hwm_c, 'figures.r80.safer' ) . '.',
	),
	array(
		'q' => 'What size expansion tank for an 80-gallon water heater?',
		'a' => 'At ' . hwm_value( $hwm_c, 'figures.temp' ) . ' °F a ' . hwm_value( $hwm_c, 'figures.r80.tank' ) . ' is the minimum, but it is close to its limit, so a ' . hwm_value( $hwm_c, 'figures.r80.safer' ) . ' is the safer pick. At ' . hwm_value( $hwm_c, 'figures.maxSupply' ) . ' psi supply the heater needs about ' . hwm_value( $hwm_c, 'figures.r80.required' ) . ' gallons of tank volume. Enter your setting and pressure above.',
	),
	array(
		'q' => 'Do I need an expansion tank on my water heater?',
		'a' => 'Yes, if your home has a check valve, backflow preventer or pressure-reducing valve between it and the water main. That closed system traps expanded water. Washington State\'s health department calls an expansion tank essential in that case.',
	),
	array(
		'q' => 'What should the air pressure be in an expansion tank?',
		'a' => 'The same as your home\'s static water pressure, measured with a gauge at a hose bib with no water running. AMTROL says the precharge must be matched to the supply before installation, because tanks ship with a fixed factory charge.',
	),
	array(
		'q' => 'Can an expansion tank be too big?',
		'a' => 'Not in a harmful way. A larger tank absorbs the same expansion with a smaller pressure rise; it just costs more and takes more space. A tank that is too small lets the pressure climb toward the relief valve setting.',
	),
	array(
		'q' => 'How much does a thermal expansion tank cost?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.partLow' ) . ' to $' . hwm_value( $hwm_c, 'figures.partHigh' ) . ' for the tank in current listings, and about $' . hwm_value( $hwm_c, 'figures.costTypical' ) . ' installed in our cost model. It costs less when added during a water heater replacement.',
	),
);
$sources = array(
	array( 'publisher' => 'AMTROL', 'title' => 'Sizing the Therm-X-Trol (MC10007): expansion factor table and design pressure factor', 'url' => $hwm_r( 'amtrol-sizing' ), 'accessed' => (string) hwm_raw( $hwm_et, 'references.amtrol-sizing.checked' ) ),
	array( 'publisher' => 'AMTROL (Worthington Industries)', 'title' => 'Therm-X-Trol specifications and Quick Sizing Charts (MC8520)', 'url' => $hwm_r( 'amtrol-chart' ), 'accessed' => (string) hwm_raw( $hwm_et, 'references.amtrol-chart.checked' ) ),
	array( 'publisher' => 'Washington State Department of Health', 'title' => 'Protect Your Water Heater from Thermal Expansion (2025)', 'url' => $hwm_r( 'wa-doh' ), 'accessed' => (string) hwm_raw( $hwm_et, 'references.wa-doh.checked' ) ),
	array( 'publisher' => 'Watts', 'title' => 'Expansion Tank Sizing Calculator (Potable)', 'url' => $hwm_r( 'watts-etp' ), 'accessed' => (string) hwm_raw( $hwm_et, 'references.watts-etp.checked' ) ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential gas water heater manual: pressure-reducing valve setting', 'url' => (string) hwm_raw( $hwm_et, 'pressureAdvice.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Thermal expansion tank listing prices', 'url' => (string) hwm_raw( 'installation-costs', 'references.home-depot-parts.sourceUrl' ), 'accessed' => (string) hwm_raw( 'installation-costs', 'lastReviewed' ) ),
);
