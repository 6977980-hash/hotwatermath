<?php
/**
 * /tankless-water-heater-electrical-requirements/ (STRATEGY §2 item 15, Phase 6d, CLAUDE.md §8b): breakers, wire and
 * service for electric tankless water heaters, from two manufacturers' published requirements and the 2021 IRC
 * (Part VIII, restating the NEC) in general terms. Facts: data/safety.json → electrical and references; arithmetic:
 * data/computed/tankless-water-heater-electrical-requirements.json. No wiring steps: what the manual and code require,
 * what it costs, and that a licensed electrician does the work.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f = 'safety';
$hwm_c = 'computed/tankless-water-heater-electrical-requirements';
$hwm_s = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, 'electrical.' . $path ) );
$hwm_v = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_r = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_x = static fn( string $path ): string => esc_html( number_format( (float) hwm_raw( 'installation-costs', 'extras.' . $path ) ) );
$hwm_amp = static fn( int $i, string $col ): string => esc_html( (string) hwm_raw( $hwm_f, 'electrical.ampacity.' . $i . '.' . $col ) );
$hwm_irc = (string) hwm_raw( $hwm_f, 'electrical.edition' );

$related = array( 'tankless-water-heater-size-calculator', 'tankless-water-heater-installation-cost', 'gas-line-size-for-tankless-water-heater', 'tankless-vs-tank-water-heater', 'electric-water-heater-installation-cost' );
hwm_part( 'safety-notice', array( 'gas' => false, 'power' => true, 'scald' => false ) );
?>
<aside class="notice" aria-label="Electrical work is licensed work">
	<p><strong>Not a wiring design.</strong> This page explains what manufacturers specify and what the electrical code says in general terms, so you can read a quote and ask the right questions. The breaker, wire and service work is done by a licensed electrician, usually with a permit and inspection, and your local code official decides which edition of the National Electrical Code (NEC) and which amendments apply. Stiebel Eltron's own table ends: "Check local codes for compliance."</p>
</aside>

<h2>What size breaker does a tankless water heater need?</h2>
<p>The size printed in its installation manual and on its label. A whole-house electric unit needs one to four double-pole breakers: Stiebel Eltron specifies <?php echo $hwm_v( 't24Circuits' ); // phpcs:ignore ?> × <?php echo $hwm_v( 't24Breaker' ); // phpcs:ignore ?> A for its <?php echo $hwm_v( 't24kw' ); // phpcs:ignore ?> kW Tempra, and Rheem specifies <?php echo $hwm_v( 'r18Circuits' ); // phpcs:ignore ?> × <?php echo $hwm_v( 'r18Breaker' ); // phpcs:ignore ?> A for its <?php echo $hwm_v( 'r18kw' ); // phpcs:ignore ?> kW RETEX-18. The code points the same way: overcurrent protection for an appliance "shall not exceed" the rating marked on it (<?php echo esc_html( $hwm_irc ); ?>, E4101.4.1, NEC 422.11(E)).</p>
<p>So the useful question is not "what breaker does a tankless need" but "what does the manual for the model I am buying say", because two units of the same power can be wired differently. The table below shows how much it varies.</p>
<dl>
	<dt>Double-pole breaker</dt>
	<dd>A breaker that switches both 120-volt legs of a 240-volt circuit at once. Each circuit of an electric tankless unit gets its own.</dd>
	<dt>Ampacity</dt>
	<dd>The current a wire can carry continuously without overheating, set by its size, metal and insulation temperature rating.</dd>
	<dt>Service</dt>
	<dd>The capacity of the whole electrical supply to the house, in amps, set by the main breaker and the utility's lines.</dd>
</dl>

<h2>How many amps does an electric tankless water heater draw?</h2>
<p>Amps equal watts divided by volts. At 240 volts, a <?php echo $hwm_v( 't24kw' ); // phpcs:ignore ?> kW unit draws <?php echo $hwm_v( 't24Watts' ); // phpcs:ignore ?> ÷ 240 = <?php echo $hwm_v( 't24Amps' ); // phpcs:ignore ?> amps, which is why it is split across several circuits. The largest whole-house units in the table draw <?php echo $hwm_v( 'maxAmps' ); // phpcs:ignore ?> amps.</p>
<?php echo hwm_table( $hwm_c, 'units' ); // phpcs:ignore -- escaped in the part. ?>
<p>Read across a row and the logic is visible: divide the draw by the number of circuits and you get the current each breaker carries. The "breaker vs draw" column shows how close each manufacturer sizes the breaker to that current: from <?php echo $hwm_v( 'minPct' ); // phpcs:ignore ?>% to <?php echo $hwm_v( 'maxPct' ); // phpcs:ignore ?>% across these models. That spread is the reason sites disagree, and the reason the manual decides.</p>

<h2>Does the 125% rule apply to tankless water heaters?</h2>
<p>Not the way many guides apply it. The code's 125% sentence is written for tanks: the circuit and breaker for "fixed storage-type water heaters having a capacity of <?php echo $hwm_s( 'storageMaxGallons' ); // phpcs:ignore ?> gallons (450 L) or less" must be at least <?php echo $hwm_s( 'storageRulePct' ); // phpcs:ignore ?>% of the heater's rating (<?php echo esc_html( $hwm_irc ); ?>, E3702.10, NEC 422.13). A tankless unit is not a storage-type heater.</p>
<p>For a tankless unit, the manufacturer's marked rating governs, and it must not be exceeded (E4101.4.1). Stiebel Eltron states its approach plainly: "Overcurrent protection sized at 100% of load. Tankless water heaters are considered a non-continuous load." Its Tempra 24 draws <?php echo $hwm_v( 't24Per' ); // phpcs:ignore ?> A per circuit on <?php echo $hwm_v( 't24Breaker' ); // phpcs:ignore ?> A breakers. Rheem's RETEX-18 draws <?php echo $hwm_v( 'r18Per' ); // phpcs:ignore ?> A per circuit on <?php echo $hwm_v( 'r18Breaker' ); // phpcs:ignore ?> A breakers, <?php echo $hwm_v( 'r18Pct' ); // phpcs:ignore ?>% of the draw.</p>
<p>Applying the storage-tank rule anyway would put the Tempra 24 on <?php echo $hwm_v( 't24At125' ); // phpcs:ignore ?> A breakers, above the <?php echo $hwm_v( 't24Breaker' ); // phpcs:ignore ?> A its maker specifies, which the code does not allow. If your electrician or inspector reads it differently in your area, their reading wins; what should never happen is a breaker larger than the one marked on the unit.</p>

<h2>What wire size does a tankless water heater need?</h2>
<p>The size in the manual, at the temperature rating the manual names. Stiebel Eltron specifies 8 AWG copper for most Tempra circuits and adds: "Copper conductors with a temperature rating of <?php echo $hwm_s( 'stiebelConductorC' ); // phpcs:ignore ?> °C or greater must be used." Rheem specifies 6 or 8 AWG copper. The temperature rating matters because the code gives the same wire a different ampacity in each column.</p>
<?php echo hwm_table( $hwm_c, 'ampacity' ); // phpcs:ignore -- escaped in the part. ?>
<p>That is how 8 AWG can sit on a <?php echo $hwm_v( 't24Breaker' ); // phpcs:ignore ?> A breaker in Stiebel's table: at <?php echo $hwm_s( 'stiebelConductorC' ); // phpcs:ignore ?> °C, 8 AWG copper is rated <?php echo $hwm_amp( 1, 'c75' ); // phpcs:ignore ?> A. In the 60 °C column it is rated <?php echo $hwm_amp( 1, 'c60' ); // phpcs:ignore ?> A, and the code holds ordinary NM ("Romex") cable to that column (E3705.4.4, NEC 334.80). The breaker and the terminals on both ends also have to be rated for the temperature column used (E3705.4.1, NEC 110.14(C)). Choosing the wiring method is the electrician's job; the point for you is that "8 AWG" alone does not say whether a circuit is right.</p>
<p>Distance matters too. Stiebel says conductors "should be sized to maintain a voltage drop of less than <?php echo $hwm_s( 'stiebelVoltageDropPct' ); // phpcs:ignore ?>% under load", so a long run from the panel may need a larger wire than the table minimum.</p>

<?php hwm_ad( 'B' ); ?>

<h2>Is your electrical service big enough for a tankless water heater?</h2>
<p>Often not, for the larger units. Stiebel Eltron recommends a minimum main service for each model, "depending on total electric load at the installation site": from <?php echo esc_html( (string) hwm_raw( $hwm_f, 'electrical.units.0.serviceA' ) ); ?> A for its smallest Tempra to <?php echo esc_html( (string) hwm_raw( $hwm_f, 'electrical.units.5.serviceA' ) ); ?> A for the <?php echo $hwm_v( 'maxKw' ); // phpcs:ignore ?> kW model (last column of the table above). A home with a smaller service and an electric range, dryer and heat pump may have no room for a <?php echo $hwm_v( 't24Amps' ); // phpcs:ignore ?> A water heater.</p>
<p>Your electrician settles this with a load calculation for the whole house, done under the electrical code, before anyone buys a unit. If the service is too small, the choice is a panel and service upgrade, a smaller unit that serves fewer fixtures, a heat pump or tank water heater, or gas. In our cost model, a panel upgrade to 200 amps costs about $<?php echo $hwm_x( 'panel.low' ); // phpcs:ignore ?> to $<?php echo $hwm_x( 'panel.high' ); // phpcs:ignore ?> (DOE's range).</p>
<p>Check the size you actually need first. Electric units are sized in kW for the fixtures you run at once and how cold your incoming water is; the calculator below shows the kW and amps for your home.</p>
<?php
ob_start();
hwm_part( 'tankless-inputs', array( 'fuel' => 'electric' ) );
hwm_part( 'calculator', array( 'tool' => 'tankless-water-heater-size-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Tankless water heater size calculator', 'default' => (array) hwm_raw( $hwm_c, 'tanklessDefault' ) ) );
?>
<p>If the result is more than one whole-house unit, electric tankless is rarely practical for that home, and the <?php echo hwm_link( 'gas-line-size-for-tankless-water-heater', 'gas line sizing guide' ); // phpcs:ignore ?> or the <?php echo hwm_link( 'tankless-vs-tank-water-heater', 'tankless vs tank calculator' ); // phpcs:ignore ?> is the better next step.</p>

<h2>What happens on 208 volts?</h2>
<p>You lose about a quarter of the heat. Apartments, condos and some commercial buildings get 208 volts instead of 240. A heating element's resistance is fixed, so its power falls with the square of the voltage: (208 ÷ 240)² = <?php echo $hwm_v( 'lowPct' ); // phpcs:ignore ?>%. Rheem says its 240 V units can be used on 208 V "with <?php echo $hwm_s( 'rheemLowVoltPct' ); // phpcs:ignore ?>% reduced temperature output".</p>
<?php echo hwm_table( $hwm_c, 'v208' ); // phpcs:ignore -- escaped in the part. ?>
<p>Stiebel Eltron's published 208-volt ratings match the arithmetic exactly, which is a useful check on both. If your building is on 208 V, size the unit from its 208-volt rating, not the headline kW.</p>

<h2>Do gas tankless water heaters need electricity?</h2>
<p>Yes, but very little. Their fans, controls and igniters run on household current: Rinnai says its units "require <?php echo $hwm_s( 'gasTanklessVolts' ); // phpcs:ignore ?> volts AC, 60 Hz and less than <?php echo $hwm_s( 'gasTanklessAmps' ); // phpcs:ignore ?> amps". The manual says how it connects (plug or hard-wired) and what circuit it needs. The big upgrade for a gas tankless unit is the gas line, not the wiring.</p>

<h2>What the electrical work costs</h2>
<p>In our cost model, a new 240-volt circuit costs about $<?php echo $hwm_x( 'circuit.low' ); // phpcs:ignore ?> to $<?php echo $hwm_x( 'circuit.high' ); // phpcs:ignore ?>, and a whole-house electric tankless unit often needs two to four of them, plus the panel upgrade above when the service is too small. Our <?php echo hwm_link( 'tankless-water-heater-installation-cost', 'tankless installation cost guide' ); // phpcs:ignore ?> adds these to the unit, labor and permit for your state.</p>

<h2>Common mistakes with tankless wiring</h2>
<ul>
	<li><strong>Buying the unit before checking the service.</strong> The load calculation comes first.</li>
	<li><strong>Taking breaker sizes from a generic chart.</strong> Two units with the same kW can use different breakers; follow the manual.</li>
	<li><strong>Using a larger breaker than the unit is marked for.</strong> The code says it shall not exceed the marked rating.</li>
	<li><strong>Reading "8 AWG" without the temperature rating.</strong> The same wire is rated <?php echo $hwm_amp( 1, 'c60' ); // phpcs:ignore ?> A or <?php echo $hwm_amp( 1, 'c75' ); // phpcs:ignore ?> A depending on the column.</li>
	<li><strong>Ignoring 208 volts.</strong> A 240 V unit on 208 V gives about <?php echo $hwm_v( 'lowPct' ); // phpcs:ignore ?>% of its rated heat.</li>
	<li><strong>Reusing the old tank's circuit.</strong> A whole-house tankless unit draws several times more current than a tank's element.</li>
</ul>

<h2>When to call a licensed electrician</h2>
<p>For all of it: the load calculation, new circuits, the breakers, the wire, any panel or service upgrade, and the connection at the unit. Rheem's spec sheet tells installers to "check with local officials" before changing the electrical supply, and Stiebel Eltron's recommendations end with "check local codes for compliance". Ask your local building department about the permit.</p>
<p>What you can do yourself: find your main breaker's rating, list the large electric appliances in the house, and ask each electrician which manual and code edition they sized to, and whether the quote includes a load calculation.</p>

<h2>Limitations of this guide</h2>
<p>Breaker, wire and service figures are as published by Stiebel Eltron and Rheem for their own models; other brands differ, and your unit's manual wins. Code text is from the <?php echo esc_html( $hwm_irc ); ?>, which restates NEC rules; your area may use another NEC edition or local amendments, and the local code official decides. The ampacity rows are an excerpt and leave out ambient temperature and bundling corrections. We are an independent calculator site, not electricians: read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'What size breaker do I need for a tankless water heater?',
		'a' => 'The size in its installation manual. Whole-house electric units use one to four double-pole breakers, each sized by the maker: Stiebel Eltron specifies ' . hwm_value( $hwm_c, 'figures.t24Circuits' ) . ' × ' . hwm_value( $hwm_c, 'figures.t24Breaker' ) . ' A for a ' . hwm_value( $hwm_c, 'figures.t24kw' ) . ' kW unit. The code says the breaker must not exceed the rating marked on the appliance.',
	),
	array(
		'q' => 'How many amps does a tankless water heater use?',
		'a' => 'Divide watts by volts. A ' . hwm_value( $hwm_c, 'figures.t24kw' ) . ' kW unit at 240 volts draws ' . hwm_value( $hwm_c, 'figures.t24Amps' ) . ' amps and the largest whole-house units ' . hwm_value( $hwm_c, 'figures.maxAmps' ) . ' amps, split over several circuits. A gas tankless unit draws less than ' . hwm_value( 'safety', 'electrical.gasTanklessAmps' ) . ' amps at ' . hwm_value( 'safety', 'electrical.gasTanklessVolts' ) . ' volts, according to Rinnai.',
	),
	array(
		'q' => 'Does a tankless water heater need 200 amp service?',
		'a' => 'It depends on the unit and the rest of the house. Stiebel Eltron recommends minimum services from ' . hwm_value( 'safety', 'electrical.units.0.serviceA' ) . ' A for its smallest model to ' . hwm_value( 'safety', 'electrical.units.4.serviceA' ) . ' A for its ' . hwm_value( 'safety', 'electrical.units.4.kw' ) . ' kW model and ' . hwm_value( 'safety', 'electrical.units.5.serviceA' ) . ' A for ' . hwm_value( $hwm_c, 'figures.maxKw' ) . ' kW. An electrician\'s load calculation for your home decides.',
	),
	array(
		'q' => 'Is a tankless water heater a continuous load?',
		'a' => 'Stiebel Eltron says tankless water heaters "are considered a non-continuous load" and sizes its breakers at 100% of the load. The code\'s 125% rule is written for storage-type water heaters of ' . hwm_value( 'safety', 'electrical.storageMaxGallons' ) . ' gallons or less. Your inspector applies the local code; never exceed the breaker size marked on the unit.',
	),
	array(
		'q' => 'What size wire for a tankless water heater?',
		'a' => 'The size the manual specifies, usually 8 or 6 AWG copper per circuit in both makers’ tables, at the temperature rating it names. Stiebel Eltron requires ' . hwm_value( 'safety', 'electrical.stiebelConductorC' ) . ' °C or higher conductors. In the code table 8 AWG copper is rated ' . hwm_value( 'safety', 'electrical.ampacity.1.c60' ) . ' A in the 60 °C column and ' . hwm_value( 'safety', 'electrical.ampacity.1.c75' ) . ' A in the 75 °C column.',
	),
	array(
		'q' => 'Can I run a tankless water heater on 208 volts?',
		'a' => 'Many units allow it, at lower output. Power falls with the square of the voltage, so a 240 V unit on 208 V gives about ' . hwm_value( $hwm_c, 'figures.lowPct' ) . '% of its rated heat; Rheem says ' . hwm_value( 'safety', 'electrical.rheemLowVoltPct' ) . '% reduced output, and Stiebel lists its ' . hwm_value( $hwm_c, 'figures.t24kw' ) . ' kW model at ' . hwm_value( $hwm_c, 'figures.t24kw208' ) . ' kW on 208 V.',
	),
);
$sources = array(
	array( 'publisher' => 'International Code Council', 'title' => '2021 International Residential Code, Part VIII Electrical: E3702.10, Tables E3705.1 and E3705.6, E3705.4, E4101.4.1 (with NEC references)', 'url' => $hwm_r( 'irc-2021-electrical' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Stiebel Eltron', 'title' => 'Tempra Trend / Plus technical specifications (rev. 6.2025)', 'url' => $hwm_r( 'stiebel-tempra' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Performance tankless electric water heaters, specifications (form THD-PTE Rev. 2)', 'url' => $hwm_r( 'rheem-retex' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rinnai America', 'title' => 'Professional FAQ: power supply requirements', 'url' => $hwm_r( 'rinnai-faq' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Electronic Thermostat Electric Water Heater Use & Care Guide (2000598248): electrical safety', 'url' => $hwm_r( 'aos-electric-manual' ), 'accessed' => '2026-11-13' ),
);
