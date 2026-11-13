<?php
/**
 * /water-heater-reset-button/ (STRATEGY §2 item 25, Phase 6b batch 2): the electric high-limit switch (ECO), with
 * the repair-or-replace tool embedded. Steps restate A. O. Smith's troubleshooting page and electric Use & Care Guide;
 * GE Appliances' stricter rule is shown next to it. No wiring or gas repair steps (CLAUDE.md §8b).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_pct = (array) hwm_raw( 'computed/repair-or-replace-water-heater', 'rulesPct' );

$related = array( 'no-hot-water', 'repair-or-replace-water-heater', 'water-heater-age-calculator', 'hot-water-runs-out-fast', 'water-heater-making-noise' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>What does the reset button on a water heater do?</h2>
<p>It shuts the heater off when the water gets dangerously hot. On an electric tank the red button belongs to a safety switch that A. O. Smith calls the Energy Cut Off (ECO): when the tank overheats, it cuts power to the heating elements and stays off until someone presses the button. While it is tripped, you have no hot water.</p>
<dl>
	<dt>ECO (Energy Cut Off), high-limit switch</dt>
	<dd>A manual-reset temperature switch on the upper thermostat of an electric water heater, pressed against the tank wall. GE Appliances describes it as the control that "breaks the power circuit to the heating element" if the water temperature becomes excessively high. The red button is how you reset it.</dd>
</dl>
<p>A tripped reset button is a symptom, not the fault. The switch only trips because something let the water overheat, and A. O. Smith says that in most cases that is a problem with one of the elements or thermostats.</p>

<h2>Where is the reset button on a water heater?</h2>
<p>On an electric tank, it is behind the upper access panel on the side of the heater, on the upper thermostat, usually under a layer of insulation and a plastic cover. A. O. Smith's guidance is to remove the panel that covers the upper thermostat to find it. Tall tanks have two panels; the reset button is only on the upper one.</p>
<figure class="figure">
	<svg viewBox="0 0 640 360" width="640" height="360" role="img" aria-labelledby="reset-fig-t reset-fig-d">
		<title id="reset-fig-t">Where the reset button is on an electric water heater</title>
		<desc id="reset-fig-d">Drawing of an electric tank water heater. Behind the upper access panel: the upper thermostat with the red reset button (ECO) and the upper heating element. Behind the lower access panel: the lower thermostat and the lower heating element, which has no reset button. Turn the breaker off before removing either panel.</desc>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<rect x="150" y="20" width="200" height="320" rx="24" fill="#f1f4f7" stroke="#4a5562" stroke-width="2"/>
			<rect x="230" y="6" width="14" height="16" fill="#4a5562"/>
			<rect x="270" y="6" width="14" height="16" fill="#4a5562"/>
			<rect x="185" y="80" width="130" height="80" rx="6" fill="#ffffff" stroke="#0b4a7d" stroke-dasharray="5 3"/>
			<rect x="200" y="92" width="60" height="40" rx="4" fill="#e6f0f8" stroke="#0b4a7d"/>
			<circle cx="248" cy="104" r="7" fill="#c0392b"/>
			<rect x="270" y="140" width="40" height="8" rx="3" fill="#7a4f00"/>
			<rect x="185" y="230" width="130" height="80" rx="6" fill="#ffffff" stroke="#0b4a7d" stroke-dasharray="5 3"/>
			<rect x="200" y="242" width="60" height="40" rx="4" fill="#e6f0f8" stroke="#0b4a7d"/>
			<rect x="270" y="290" width="40" height="8" rx="3" fill="#7a4f00"/>
			<path d="M255 104 H400" fill="none" stroke="#c0392b"/>
			<text x="405" y="100">Red reset button (ECO)</text>
			<text x="405" y="117">on the upper thermostat</text>
			<path d="M315 144 H400 V150" fill="none" stroke="#17212e"/>
			<text x="405" y="160">Upper heating element</text>
			<path d="M260 262 H400" fill="none" stroke="#17212e"/>
			<text x="405" y="258">Lower thermostat</text>
			<text x="405" y="275">(no reset button)</text>
			<path d="M315 294 H400" fill="none" stroke="#17212e"/>
			<text x="405" y="298">Lower heating element</text>
			<text x="20" y="116">Upper access</text>
			<text x="20" y="133">panel</text>
			<path d="M110 120 H185" fill="none" stroke="#17212e"/>
			<text x="20" y="266">Lower access</text>
			<text x="20" y="283">panel</text>
			<path d="M110 270 H185" fill="none" stroke="#17212e"/>
			<text x="20" y="345" fill="#a8231a">Breaker off before you remove either panel.</text>
		</g>
	</svg>
	<figcaption>Typical layout of a two-element electric tank, drawn by Hot Water Math from A. O. Smith's guidance. Check the diagram in your own manual: layouts vary by model.</figcaption>
</figure>
<p>Gas water heaters, tankless units and heat pump water heaters work differently; see the gas section below.</p>

<h2>How to reset a water heater safely</h2>
<p>Turn the power off first, every time. A. O. Smith's steps for an electric water heater:</p>
<ol>
	<li>Turn off the water heater's circuit breaker in your electrical panel. A. O. Smith warns that components stay live even when the heater is not heating.</li>
	<li>Remove the access panel that covers the upper thermostat, and fold back the insulation. Do not touch the wires or terminals.</li>
	<li>Press the red reset button firmly until you hear it click. A click means it had tripped. No click means it had not tripped, and the cause of your problem is elsewhere.</li>
	<li>Put the insulation and the panel back, then turn the breaker on.</li>
	<li>Give it time: A. O. Smith says an electric tank needs about <?php echo $hwm_t( 'noHot.recoveryElectricMinLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'noHot.recoveryElectricMinHigh' ); // phpcs:ignore ?> minutes to recover.</li>
</ol>
<p>If the breaker trips when you turn it back on, leave it off. That points to a short, and A. O. Smith's guidance is to have the wiring, breaker or element checked rather than reset it again.</p>

<h2>Should you press the reset button yourself?</h2>
<p>Manufacturers disagree, and you should know both views. A. O. Smith tells owners how to press it with the power off, and says to contact a professional if it trips again. GE Appliances says resetting the high-limit control "should only be done by a qualified service technician", and that the cause must be found before the heater goes back into service.</p>
<p>Both agree on the part that matters: the button tripped for a reason. A. O. Smith's own electric manual says a tripped ECO "can usually be reset, but you should have a qualified person investigate the cause of the overheating." Our reading: one reset with the breaker off is a reasonable check if your manual allows it. A second trip means stop, leave the power off, and get the heater checked. If your heater is a GE, follow GE.</p>

<h2>Why does my water heater reset button keep tripping?</h2>
<p>Because something keeps letting the water overheat, and the switch is doing its job. A. O. Smith's guides point to three usual causes: a shorted or grounded heating element, a thermostat that fails to switch off, or a thermostat that does not sit tight against the tank. None of these is fixed by pressing the button again.</p>
<?php echo hwm_table( $hwm_f, 'resetCauses' ); // phpcs:ignore -- escaped in the part. ?>
<p>One more sign to watch: A. O. Smith says that if the relief valve on the side of the tank releases a lot of very hot water, the likely cause is a shorted heating element. Turn the power off at the breaker until it is fixed. A small drip from the relief valve is a different problem, usually water pressure; see <?php echo hwm_link( 'water-heater-leaking', 'water heater leaking' ); // phpcs:ignore ?>.</p>
<p>Some websites blame power surges or outages for a tripped button. We found no manufacturer guidance that says so, so we leave it out of the table.</p>

<h2>Reset button or circuit breaker?</h2>
<p>They protect against different things. Check the breaker first, because A. O. Smith calls a tripped breaker a very common reason an electric water heater stops working.</p>
<table>
	<caption>Breaker vs reset button on an electric water heater</caption>
	<thead>
		<tr><th scope="col"></th><th scope="col">Circuit breaker</th><th scope="col">Reset button (ECO)</th></tr>
	</thead>
	<tbody>
		<tr><th scope="row">Where</th><td>Your electrical panel</td><td>Behind the upper access panel on the heater</td></tr>
		<tr><th scope="row">Trips when</th><td>Too much current: a short, a grounded element, a wiring fault</td><td>The water in the tank gets too hot</td></tr>
		<tr><th scope="row">What it protects</th><td>The wiring in your house</td><td>You and the tank, from overheated water</td></tr>
		<tr><th scope="row">Trips again after one reset</th><td>Leave it off; have the circuit and element checked</td><td>Leave the power off; have the elements and thermostats checked</td></tr>
	</tbody>
</table>
<p class="table-source">Source: A. O. Smith troubleshooting page and electric Use &amp; Care Guide, checked <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.aos-electric-manual.checked' ) ); ?>.</p>

<h2>Gas water heater reset button</h2>
<p>Most gas tank water heaters have no red reset button like an electric one, and pressing the wrong button will not fix anything. On A. O. Smith's gas control, the button near the knob is the igniter: the lighting label says to hold the knob in and press the igniter button until the pilot lights.</p>
<p>What a gas heater has instead, and what to do:</p>
<ul>
	<li><strong>A flame-safety system with a resettable thermal switch.</strong> A. O. Smith's gas manual says its heaters have a resettable thermal switch as part of the flammable vapor system, and warns never to disable or modify it. If it has shut the heater down, that is a job for a qualified technician.</li>
	<li><strong>A status light or flash code</strong> on electronic gas controls. The manual lists what each pattern means. Some lockouts clear after you turn the control off, wait, and relight by the label; others name a part that needs a technician.</li>
	<li><strong>A pilot that has gone out.</strong> Relight it only by the instructions on the heater's label, and never if you smell gas. If you still have no hot water after <?php echo $hwm_t( 'noHot.waitHours' ); // phpcs:ignore ?> hours, the manual sends you to its troubleshooting section. Our <?php echo hwm_link( 'no-hot-water', 'no hot water guide' ); // phpcs:ignore ?> covers these checks.</li>
</ul>
<p>Tankless and heat pump water heaters usually show an error code on a display, and the manual says whether and how you can clear it. The code is more useful than any button: write it down before you call for service.</p>

<h2>Repair or replace a heater that keeps tripping?</h2>
<p>A heater that trips its reset button usually needs a part, not a new heater. Elements and thermostats are designed to be replaced. Age changes the answer: our rule, from Rheem's guidance, is to replace if the repair costs more than <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% of a new unit, or more than <?php echo esc_html( (string) ( $hwm_pct['old'] ?? '' ) ); ?>% once it is past <?php echo esc_html( (string) ( $hwm_pct['age'] ?? '' ) ); ?>% of its expected life. Enter the repair quote and the heater's age.</p>
<?php
ob_start();
hwm_part( 'repair-inputs', array( 'checked' => array( 'noHot' ) ) );
hwm_part( 'calculator', array( 'tool' => 'repair-or-replace-water-heater', 'inputs' => (string) ob_get_clean(), 'label' => 'Repair or replace calculator' ) );
?>
<p>Not sure how old it is? The <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> reads the date from the serial number. If the heater works but runs short, read <?php echo hwm_link( 'hot-water-runs-out-fast', 'hot water runs out fast' ); // phpcs:ignore ?>: a failed lower element is a common cause.</p>

<h2>When a tripping reset button is a job for a licensed professional</h2>
<p>Resetting a breaker once and pressing the reset button once with the power off are the owner-level checks in A. O. Smith's guidance. Call a licensed plumber or electrician when the button or the breaker trips again, when the button does not click and there is still no heat, when the relief valve releases very hot water, when you see damaged wiring, or when an element or thermostat needs replacing. Testing elements and thermostats means working near live terminals, which we do not describe on purpose.</p>

<h2>Limitations of this guide</h2>
<p>The steps restate A. O. Smith's troubleshooting page and its electric Use &amp; Care Guide; GE's rule is quoted next to it. Controls differ by brand and model, and your manual wins. The current manuals we opened do not publish the temperature at which the switch trips, so we give none. We are an independent calculator site, not electricians: read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'Why does my water heater reset button keep tripping?',
		'a' => 'Because the water keeps overheating. A. O. Smith says the usual causes are a shorted or grounded heating element, a thermostat that does not switch off, or a thermostat that is not tight against the tank. Pressing the button again does not fix any of them; have the elements and thermostats checked.',
	),
	array(
		'q' => 'Where is the reset button on an electric water heater?',
		'a' => 'Behind the upper access panel on the side of the tank, on the upper thermostat, usually under insulation and a plastic cover. It is red. Turn the breaker off before you remove the panel. The lower panel has a thermostat but no reset button.',
	),
	array(
		'q' => 'How do I know if the water heater reset button tripped?',
		'a' => 'Press it firmly with the power off. A. O. Smith says that if you hear a click, it had tripped. If there is no click, it had not tripped, and the thermostats or the power supply should be checked by a qualified person.',
	),
	array(
		'q' => 'Does a gas water heater have a reset button?',
		'a' => 'Usually not a red one like an electric tank. The button by the gas control on many models is the pilot igniter. Gas heaters have flame-safety systems and status lights instead; A. O. Smith says never to disable the thermal switch. Follow the lighting label and the manual.',
	),
	array(
		'q' => 'How long after pressing the reset button will I have hot water?',
		'a' => 'A. O. Smith says an electric tank needs about ' . hwm_value( $hwm_f, 'noHot.recoveryElectricMinLow' ) . ' to ' . hwm_value( $hwm_f, 'noHot.recoveryElectricMinHigh' ) . ' minutes to recover. If there is still no heat after that, the problem is likely an element or thermostat, not the reset button.',
	),
	array(
		'q' => 'Is it safe to keep resetting my water heater?',
		'a' => 'No. The switch trips to stop the tank from overheating. A. O. Smith says to contact a professional if it trips repeatedly, and GE says the cause must be investigated by a qualified technician before the heater goes back into service. Leave the breaker off until then.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater not working? Common problems and fixes (reset button steps)', 'url' => $hwm_r( 'aos-not-working' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Electronic Thermostat Electric Water Heater Use & Care Guide (2000598248): Energy Cut Off troubleshooting', 'url' => $hwm_r( 'aos-electric-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'GE Appliances', 'title' => 'Water Heater - Energy Cut Off Control (ECO)', 'url' => $hwm_r( 'ge-eco' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): lighting label, thermal switch, troubleshooting', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace', 'url' => $hwm_r( 'rheem-lifespan' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
);
