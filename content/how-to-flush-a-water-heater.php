<?php
/**
 * /how-to-flush-a-water-heater/ (STRATEGY §2 item 22, Phase 6b batch 1): care article with the age calculator embedded.
 * Steps restate the A. O. Smith residential gas manual (100400765) and A. O. Smith's maintenance page;
 * tankless descaling restates Rinnai. Figures come from data/troubleshooting.json and the age calculator's computed file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_age = 'computed/water-heater-age-calculator';
$hwm_hs  = (array) hwm_raw( 'lifespans', 'adjustments.hardnessScale' );

$related = array( 'water-heater-age-calculator', 'water-heater-anode-rod', 'water-heater-leaking', 'how-long-does-a-water-heater-last', 'tankless-water-heater-size-calculator' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>How often should you flush a water heater?</h2>
<p>A. O. Smith's manual says to drain and flush a tank water heater after the first <?php echo $hwm_t( 'flush.firstFlushMonths' ); // phpcs:ignore ?> months, to see how much sediment builds up, then <?php echo $hwm_t( 'flush.thenEvery' ); // phpcs:ignore ?>, or more often if your water is hard. Rheem also says to flush the tank once a year. A tankless unit is descaled instead, and Rinnai says <?php echo $hwm_t( 'flush.tanklessEvery' ); // phpcs:ignore ?>.</p>
<?php echo hwm_table( $hwm_f, 'flushSchedule' ); // phpcs:ignore -- escaped in the part. ?>
<p>Hard water is what speeds the schedule up. The U.S. Geological Survey counts water as hard from <?php echo esc_html( (string) ( $hwm_hs['hard']['mgPerL'] ?? '' ) ); ?> mg/L of calcium carbonate (<?php echo $hwm_ext( (string) ( $hwm_hs['mapUrl'] ?? '' ), 'USGS hardness map' ); // phpcs:ignore ?>), and your water utility can usually tell you yours. If a lot of sediment comes out at that first flush, flush more often than once a year.</p>
<dl>
	<dt>Draining vs flushing</dt>
	<dd>Draining empties the tank through the drain valve. Flushing goes one step further: with the drain still open, you turn the cold water back on so fresh water stirs up and pushes out the sediment that settled on the tank floor, until the water runs clear.</dd>
	<dt>Sediment</dt>
	<dd>Minerals from your water (mostly calcium and magnesium) that settle at the bottom of a tank. In an electric tank they also coat the elements; in a tankless unit they form scale inside the heat exchanger.</dd>
</dl>

<h2>Why flush at all?</h2>
<p>Because sediment shortens a tank's life. The A. O. Smith manual says draining sediment "extends the life of the tank and drain valve", and that skipping it "can deteriorate the tank and drain valve and cause leaks." Sediment also makes the heater work harder: A. O. Smith says it reduces efficiency and that heavy deposits can damage the heater.</p>
<p>You may hear it before you see it. A. O. Smith says minerals that collect on the tank bottom cause popping or rumbling noises as the water heats, and its fix is to drain and flush the tank.</p>

<h2>What you need</h2>
<ul>
	<li>A garden hose long enough to reach a floor drain, a sump or outside (or buckets).</li>
	<li>Thick gloves and safety glasses, which A. O. Smith recommends for any maintenance.</li>
	<li>A flat screwdriver, in case the drain valve has a slot instead of a handle.</li>
	<li>Towels, and for some valves a threaded hose cap for a tight fit.</li>
	<li>Your heater's manual. It wins over any general guide, including this one.</li>
</ul>
<figure class="figure">
	<svg viewBox="0 0 640 300" width="640" height="300" role="img" aria-labelledby="flush-set-t flush-set-d">
		<title id="flush-set-t">How a tank is set up for flushing</title>
		<desc id="flush-set-d">The cold water valve on top of the tank is closed while draining, a hot water faucet in the house is open to let air in, and a garden hose runs from the drain valve near the bottom of the tank to a floor drain.</desc>
		<g fill="none" stroke="#17212e" stroke-width="2">
			<rect x="110" y="60" width="150" height="200" rx="20" fill="#eef3f8"/>
			<path d="M150 60 V24 H96 M220 60 V24 H300 V70"/>
			<circle cx="150" cy="36" r="8" fill="#ffffff"/>
			<path d="M292 70 h16"/>
			<path d="M110 236 H90 c-30 0 -30 40 10 44 H440"/>
			<rect x="428" y="276" width="40" height="10" fill="#ffffff"/>
		</g>
		<path d="M300 80 v14 M300 100 v8" stroke="#0b4a7d" stroke-width="3"/>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<text x="20" y="40">Cold valve:</text>
			<text x="20" y="56">closed to drain,</text>
			<text x="20" y="72">open to flush</text>
			<text x="318" y="78">Hot faucet open</text>
			<text x="318" y="94">(lets air in)</text>
			<text x="185" y="160" text-anchor="middle">Tank</text>
			<text x="185" y="178" text-anchor="middle">(gas or power off)</text>
			<text x="12" y="200">Drain valve</text>
			<text x="12" y="216">open</text>
			<text x="300" y="268">Garden hose to a drain</text>
			<text x="490" y="285">Floor drain</text>
		</g>
		<path d="M130 238 q20 -8 40 0" stroke="#a35a00" stroke-width="3" fill="none"/>
		<text x="185" y="254" text-anchor="middle" font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="12" fill="#7a4f00">sediment</text>
	</svg>
	<figcaption>The flushing setup from the A. O. Smith manual: heat off, cold valve closed while the tank drains, a hot faucet open, and a hose from the drain valve to a drain. Diagram by Hot Water Math.</figcaption>
</figure>

<h2>How to flush a tank water heater, step by step</h2>
<p>These are the steps in A. O. Smith's residential gas manual, with the electric differences from its maintenance guide. Read your own manual first; if you are not comfortable with any step, A. O. Smith says to contact a qualified person.</p>
<ol>
	<li><strong>Turn off the heat.</strong> Gas: turn the gas control to Off, then close the manual gas valve on the supply line. Electric: switch off the heater's breaker.</li>
	<li><strong>Let the water cool.</strong> Open a hot water faucet and let it run until the water is cool. A. O. Smith suggests turning the heater off <?php echo $hwm_t( 'flush.coolHours' ); // phpcs:ignore ?> before draining to cut the scald risk.</li>
	<li><strong>Close the cold water supply</strong> valve on the pipe into the top of the tank.</li>
	<li><strong>Connect the hose</strong> to the drain valve near the bottom and run the other end to a drain, outside, or into buckets.</li>
	<li><strong>Open the drain valve,</strong> and open a hot water faucet so the tank drains faster.</li>
	<li><strong>Flush.</strong> If a lot of sediment came out, open the cold water supply with the drain still open and let water run through until no more sediment comes out. Then close the drain valve.</li>
	<li><strong>Refill.</strong> With the drain valve closed and a hot faucet open, open the cold supply. The manual says to let the hot faucet run full for at least <?php echo $hwm_t( 'flush.refillFaucetMinutes' ); // phpcs:ignore ?> minutes, so all the air is out and the tank is completely full. Then close the faucet.</li>
	<li><strong>Check the anode rod</strong> while you are at it: the manual pairs the first flush with an anode inspection (see the <?php echo hwm_link( 'water-heater-anode-rod', 'anode rod guide' ); // phpcs:ignore ?>).</li>
	<li><strong>Turn the heat back on.</strong> Gas: follow the lighting instructions on the heater's label. Electric: switch the breaker back on only once the tank is full. It may take <?php echo $hwm_t( 'flush.reheatHours' ); // phpcs:ignore ?> for a tank of cold water to heat.</li>
	<li><strong>Check for leaks</strong> at the drain valve and any fitting you touched.</li>
</ol>

<h2>Gas and electric tanks: what changes</h2>
<p>The draining is the same; the shut-down and restart are not, and those are the two steps to get right.</p>
<ul>
	<li><strong>Electric: never power an empty tank.</strong> A. O. Smith says it is "critical to make sure the tank is full before turning the power back on." An element with no water around it burns out, and you are left with no hot water until it is replaced.</li>
	<li><strong>Gas: never light an empty tank.</strong> The manual says not to light the heater unless the tank is completely full. Relight only by the label on the heater. If you smell gas at any point, stop and follow the safety notice at the top of this page.</li>
	<li><strong>Gas: expect some condensation.</strong> A refilled tank full of cold water can drip condensation onto the burner, with a sizzle or a small puddle. The manual says it is normal and stops once the tank heats.</li>
</ul>

<h2>What if nothing comes out of the drain valve?</h2>
<p>Sediment can block the drain valve itself. The A. O. Smith manual says that if you cannot get the tank to drain, contact a qualified person. First rule out the simple cause: A. O. Smith says that if nothing flows, open a hot water faucet somewhere in the house to let air in. A valve that drips after you close it needs replacing; our <?php echo hwm_link( 'water-heater-leaking', 'water heater leaking guide' ); // phpcs:ignore ?> covers drain valve leaks.</p>

<h2>Should you flush an old water heater that has never been flushed?</h2>
<p>Usually yes, carefully, but know the risk. A tank that has gone years without a flush often has a thick sediment layer and a drain valve that has not moved in a long time. The valve may clog or leak once opened. None of the manufacturers we checked say not to flush an old tank, but A. O. Smith's answer to a tank that will not drain is to call a qualified person.</p>
<p>The age of the tank matters more than the flush. Flushing removes sediment; it cannot undo corrosion. Check how old your heater is first with the <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> below: in it, a tank past <?php echo esc_html( (string) hwm_value( $hwm_age, 'bandsPct.amberFrom' ) ); ?>% of its typical life is worth planning a replacement for, and one past <?php echo esc_html( (string) hwm_value( $hwm_age, 'bandsPct.redFrom' ) ); ?>% can fail at any time.</p>
<?php
ob_start();
hwm_part( 'age-inputs', array( 'file' => 'water-heater-age-calculator' ) );
hwm_part( 'calculator', array( 'tool' => 'water-heater-age-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater age calculator' ) );
?>
<p>The calculator's "Tank flushed about once a year" box and water hardness setting change the expected life it uses. For what the numbers mean by type, see <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?>.</p>

<h2>How to flush a tankless water heater (descaling)</h2>
<p>A tankless unit has no tank of sediment to drain. Instead, scale builds up inside its heat exchanger, and the fix is to circulate a descaling solution through it. A. O. Smith says tankless models need descaling, especially where the water is hard; Rinnai says to flush the mineral deposits <?php echo $hwm_t( 'flush.tanklessEvery' ); // phpcs:ignore ?>.</p>
<ul>
	<li><strong>What it takes:</strong> the unit's isolation (service) valves, a small pump, hoses and a bucket. Rinnai's procedure uses <?php echo $hwm_t( 'flush.tanklessVinegarGallons' ); // phpcs:ignore ?> gallons (<?php echo $hwm_t( 'flush.tanklessVinegarLiters' ); // phpcs:ignore ?> liters) of undiluted white vinegar and says to "never use chemical solutions of any kind."</li>
	<li><strong>How long:</strong> Rinnai says the procedure may take up to <?php echo $hwm_t( 'flush.tanklessMinutes' ); // phpcs:ignore ?> minutes.</li>
	<li><strong>Which steps:</strong> Rinnai itself tells owners to follow the flushing instructions in their own manufacturer's manual, because valve layouts differ by brand and model (<?php echo $hwm_ext( $hwm_r( 'rinnai-flush' ), 'Rinnai procedure' ); // phpcs:ignore ?>).</li>
</ul>
<p>Scale is also why hard water shortens a tankless unit's life. If you are choosing a unit, our <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless water heater size calculator' ); // phpcs:ignore ?> sizes it for your climate and fixtures.</p>

<h2>Common flushing mistakes</h2>
<ul>
	<li><strong>Draining while the water is still hot.</strong> Tank water can be hot enough to scald in seconds; let it cool first.</li>
	<li><strong>Leaving the cold supply open while draining.</strong> The tank never empties; close it to drain, open it only for the flush stage.</li>
	<li><strong>Restoring power or gas before the tank is full.</strong> It can burn out an electric element; on a gas heater, the manual forbids lighting an empty tank.</li>
	<li><strong>Skipping the air purge.</strong> Run a hot faucet full until the water flows steadily, so the tank is full of water, not air.</li>
	<li><strong>Using chemicals in a tankless unit.</strong> Rinnai specifies white vinegar only.</li>
</ul>

<h2>When flushing is a job for a licensed professional</h2>
<p>Flushing is a routine homeowner task in the manufacturers' manuals. Call a professional when the drain valve will not open or will not stop dripping, when the tank leaks, when you smell gas, when the pilot will not relight or an electric heater gives no hot water after a refill, or when you are not comfortable working around gas or electricity. A. O. Smith also suggests a yearly service visit, or twice a year with hard water.</p>

<h2>Limitations of this guide</h2>
<p>The steps restate one manufacturer's residential gas manual and maintenance pages; other brands and models differ, and your manual wins. We do not say how much sediment is "too much", because no manufacturer we checked gives a figure. We are not plumbers; <?php echo hwm_link( 'methodology', 'our methodology' ); // phpcs:ignore ?> explains how we source every number.</p>
<?php
$faq = array(
	array(
		'q' => 'How often should I flush my water heater?',
		'a' => 'A. O. Smith says to drain and flush a tank after the first ' . hwm_value( $hwm_f, 'flush.firstFlushMonths' ) . ' months, then ' . hwm_value( $hwm_f, 'flush.thenEvery' ) . ', more often with hard water. Rheem also says yearly. With hard water, A. O. Smith suggests a service visit twice a year. Tankless units are descaled at least yearly.',
	),
	array(
		'q' => 'How long does it take to flush a water heater?',
		'a' => 'Allow for cooling, draining and refilling. A. O. Smith says most tanks take ' . hwm_value( $hwm_f, 'leak.drainMinutesLow' ) . ' to ' . hwm_value( $hwm_f, 'leak.drainMinutesHigh' ) . ' minutes to drain, the refill needs a hot faucet running at least ' . hwm_value( $hwm_f, 'flush.refillFaucetMinutes' ) . ' minutes, and reheating can take ' . hwm_value( $hwm_f, 'flush.reheatHours' ) . '. Rinnai\'s tankless flush takes up to ' . hwm_value( $hwm_f, 'flush.tanklessMinutes' ) . ' minutes.',
	),
	array(
		'q' => 'Is it bad to flush an old water heater?',
		'a' => 'It is not forbidden, but an old drain valve can clog with sediment or leak once opened. If the tank will not drain, A. O. Smith says to call a qualified person. Flushing cannot repair corrosion, so check the age first; an old tank may be due for replacement anyway.',
	),
	array(
		'q' => 'Do I need to flush a tankless water heater?',
		'a' => 'Yes, but it is called descaling. A pump circulates a solution through the heat exchanger to dissolve scale. Rinnai says to do it at least once a year with ' . hwm_value( $hwm_f, 'flush.tanklessVinegarGallons' ) . ' gallons of undiluted white vinegar and no chemicals. Follow your model\'s manual for the valve steps.',
	),
	array(
		'q' => 'Can I flush a water heater without turning off the water?',
		'a' => 'You need the cold supply closed to drain the tank, then open for the flush stage, when fresh water pushes sediment out through the open drain. Leaving it open the whole time keeps the tank full and does not clear the bottom properly.',
	),
	array(
		'q' => 'Why is there no hot water after I flushed the tank?',
		'a' => 'On an electric heater, powering it before the tank was full can burn out the upper element. On a gas heater, the pilot or burner has to be relit by the label. A tank of cold water can also take ' . hwm_value( $hwm_f, 'flush.reheatHours' ) . ' to heat. See our no hot water guide for the checks.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Installation Instructions and Use & Care Guide (100400765): draining and flushing', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater maintenance: draining, flushing and anode rod', 'url' => $hwm_r( 'aos-maintenance' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater maintenance checklist', 'url' => $hwm_r( 'aos-checklist' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rinnai', 'title' => '13-step tankless water heater flushing procedure', 'url' => $hwm_r( 'rinnai-flush' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace (maintenance)', 'url' => $hwm_r( 'rheem-lifespan' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Geological Survey', 'title' => 'Hardness of water', 'url' => (string) ( $hwm_hs['sourceUrl'] ?? '' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
);
