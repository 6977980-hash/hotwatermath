<?php
/**
 * /no-hot-water/ (STRATEGY §2 item 18, Phase 6b batch 1): symptom article ("no hot water", "water heater not heating")
 * with the repair-or-replace tool embedded. Checks restate A. O. Smith's troubleshooting page and residential gas manual;
 * electrical and gas steps stop at what the manufacturer tells a homeowner to do (CLAUDE.md §8b).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_pct = (array) hwm_raw( 'computed/repair-or-replace-water-heater', 'rulesPct' );

$related = array( 'repair-or-replace-water-heater', 'water-heater-size-calculator', 'water-heater-age-calculator', 'how-to-flush-a-water-heater', 'water-heater-leaking' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => false ) );
?>
<h2>Is it every hot tap, or just one?</h2>
<p>Answer this first, because it splits the problem in two. If one faucet or shower runs cold but the others are hot, the water heater is working and the fault is in that fixture. If every hot tap in the house is cold, the problem is at the water heater, its power or its gas.</p>
<p>For a single cold shower, the usual suspect is its mixing valve. A. O. Smith's manual notes that many shower controls now have built-in mixing valves, and when they fail they can cut the hot water a shower delivers "even though there is plenty of hot water in the tank."</p>
<figure class="figure">
	<svg viewBox="0 0 640 330" width="640" height="330" role="img" aria-labelledby="nohot-flow-t nohot-flow-d">
		<title id="nohot-flow-t">No hot water: what to check first</title>
		<desc id="nohot-flow-d">Flowchart. Are all hot taps cold? No: check that fixture's mixing valve. Yes: an electric tank, check the breaker, then the reset button with the power off; a gas tank, check the gas valve and control knob, then the pilot or status light; a tankless unit, read the error code and check gas or power. If the check does not fix it, or it trips again, call a professional.</desc>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e" text-anchor="middle">
			<rect x="220" y="10" width="200" height="40" rx="8" fill="#e6f0f8" stroke="#0b4a7d"/>
			<text x="320" y="35">Are all hot taps cold?</text>
			<path d="M420 30 H500 V70" fill="none" stroke="#17212e"/>
			<text x="460" y="24">No</text>
			<rect x="420" y="70" width="200" height="40" rx="8" fill="#ffffff" stroke="#4a5562"/>
			<text x="520" y="88">That fixture: check its</text>
			<text x="520" y="104">mixing valve</text>
			<path d="M320 50 V80" fill="none" stroke="#17212e"/>
			<text x="334" y="70">Yes</text>
			<path d="M100 80 H320 M100 80 V130 M320 80 V130 M540 130 V120 H320" fill="none" stroke="#17212e"/>
			<rect x="10" y="130" width="180" height="40" rx="8" fill="#ffffff" stroke="#4a5562"/>
			<text x="100" y="155">Electric tank</text>
			<rect x="230" y="130" width="180" height="40" rx="8" fill="#ffffff" stroke="#4a5562"/>
			<text x="320" y="155">Gas tank</text>
			<rect x="450" y="130" width="180" height="40" rx="8" fill="#ffffff" stroke="#4a5562"/>
			<text x="540" y="155">Tankless</text>
			<path d="M100 170 V190 M320 170 V190 M540 170 V190" fill="none" stroke="#17212e"/>
			<rect x="10" y="190" width="180" height="56" rx="8" fill="#fdf4dc" stroke="#7a4f00"/>
			<text x="100" y="212">1 Breaker</text>
			<text x="100" y="232">2 Reset button (power off)</text>
			<rect x="230" y="190" width="180" height="56" rx="8" fill="#fdf4dc" stroke="#7a4f00"/>
			<text x="320" y="212">1 Gas valve and knob</text>
			<text x="320" y="232">2 Pilot or status light</text>
			<rect x="450" y="190" width="180" height="56" rx="8" fill="#fdf4dc" stroke="#7a4f00"/>
			<text x="540" y="212">1 Error code</text>
			<text x="540" y="232">2 Gas or power on</text>
			<path d="M100 246 V270 H540 V246 M320 246 V280" fill="none" stroke="#17212e"/>
			<rect x="170" y="280" width="300" height="40" rx="8" fill="#fdeceb" stroke="#a8231a"/>
			<text x="320" y="297">Not fixed, trips again, or you smell gas:</text>
			<text x="320" y="313">call a professional</text>
		</g>
	</svg>
	<figcaption>The order of checks in A. O. Smith's troubleshooting guidance, drawn by Hot Water Math. The table below gives the detail for each type.</figcaption>
</figure>
<?php echo hwm_table( $hwm_f, 'noHotByType' ); // phpcs:ignore -- escaped in the part. ?>

<h2>Electric water heater not heating</h2>
<p>Start at your electrical panel. A. O. Smith says a tripped circuit breaker is "a very common reason" an electric water heater stops working. Reset it once. If it trips again, leave it off: a breaker that keeps tripping points to a short, a loose wire or a grounded heating element, which is work for an electrician or plumber.</p>
<dl>
	<dt>Reset button (high-limit switch, ECO)</dt>
	<dd>A safety switch, usually red, behind the upper access panel of an electric tank. It cuts the power if the water gets too hot. A. O. Smith says it can trip because of a grounded heating element or a faulty thermostat.</dd>
</dl>
<p>If the breaker is fine, the reset button may have tripped. A. O. Smith's guidance is to turn the power off at the breaker first, remove the upper access panel and press the button firmly; a click means it had tripped. If it trips again, it says to contact a professional, because repeated tripping means a fault such as a shorted element or a failed thermostat. Do not touch anything else behind the panel with the power on.</p>
<p>Two more causes are worth knowing:</p>
<ul>
	<li><strong>A failed element or thermostat.</strong> Both are replaceable parts. A. O. Smith lists faulty elements as a cause of little or no heat and thermostat faults as a cause of uneven temperature.</li>
	<li><strong>The tank was powered while not full.</strong> After a flush or a repair, A. O. Smith says an element with no water around it burns out, leaving no hot water until the upper element is replaced. If the problem started right after the tank was drained, suspect this.</li>
</ul>
<p>Give it time after any fix. A. O. Smith says an electric tank needs about <?php echo $hwm_t( 'noHot.recoveryElectricMinLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'noHot.recoveryElectricMinHigh' ); // phpcs:ignore ?> minutes to recover.</p>

<h2>Gas water heater not heating</h2>
<p>Check the simple things before the pilot. A. O. Smith's gas manual and troubleshooting page give this order:</p>
<ol>
	<li><strong>The gas supply.</strong> The manual shut-off valve on the gas pipe should be open (handle in line with the pipe). A. O. Smith notes that gas supply problems stop the burner lighting and can put the pilot out. If other gas appliances in the house have also stopped, call your gas utility.</li>
	<li><strong>The control knob.</strong> Make sure it is not on Off or Vacation. On A. O. Smith's control, "HOT" is about <?php echo esc_html( (string) hwm_value( $hwm_f, 'safety.cpscSetpointF' ) ); ?> °F.</li>
	<li><strong>The pilot or the status light.</strong> On a standing-pilot heater, look through the sight glass. On an electronic control, the A. O. Smith manual says the status light flashes once every <?php echo $hwm_t( 'noHot.statusFlashSeconds' ); // phpcs:ignore ?> seconds when there is no fault; any other flash pattern is a code listed in the manual.</li>
	<li><strong>Time.</strong> After relighting, the manual says to wait: if you still have no hot water after <?php echo $hwm_t( 'noHot.waitHours' ); // phpcs:ignore ?> hours, go to the troubleshooting section. A. O. Smith says a gas tank needs about <?php echo $hwm_t( 'noHot.recoveryGasMinLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'noHot.recoveryGasMinHigh' ); // phpcs:ignore ?> minutes to recover after normal use.</li>
</ol>
<p>Relight a pilot only by the lighting instructions on the label on your heater, and never if you smell gas. If the pilot goes out again, A. O. Smith points first to gas supply problems; a pilot that will not stay lit is a job for a qualified technician. Our guide to a <?php echo hwm_link( 'water-heater-pilot-light-wont-stay-lit', 'pilot light that won’t stay lit' ); // phpcs:ignore ?> explains the causes and what the status light means.</p>

<h2>Tankless water heater: no hot water</h2>
<p>Read the display first. A tankless unit reports most faults as an error code, and your manual says what each one means and whether you can clear it. Then check that the gas valve is open or the breaker is on.</p>
<ul>
	<li><strong>Scale.</strong> Hard water builds scale inside the heat exchanger. A. O. Smith says tankless models need descaling in hard water areas, and Rinnai says to flush at least once a year. See <?php echo hwm_link( 'how-to-flush-a-water-heater', 'how to flush a water heater' ); // phpcs:ignore ?> for descaling.</li>
	<li><strong>Too much demand at once.</strong> A. O. Smith notes an undersized tankless unit struggles with high demand, and suggests spreading out demand or a model with a built-in buffer tank. Our <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless water heater size calculator' ); // phpcs:ignore ?> checks whether yours is big enough for your fixtures and climate.</li>
	<li><strong>A blocked inlet filter.</strong> Rinnai's flushing bulletin has you clean the in-line filter at the cold water inlet (<?php echo $hwm_ext( $hwm_r( 'rinnai-tb' ), 'Rinnai TB-188' ); // phpcs:ignore ?>); your manual shows where yours is.</li>
</ul>

<h2>Some hot water, but not enough or not hot enough</h2>
<p>If the water warms up but runs out fast or never gets hot, the A. O. Smith manual lists these causes. Most of them are not the heater's fault.</p>
<ul>
	<li><strong>A faulty mixing valve</strong> in a faucet or shower control. Check other taps.</li>
	<li><strong>A heater too small for your use.</strong> If it runs out quickly, it may be undersized. Our <?php echo hwm_link( 'water-heater-size-calculator', 'water heater size calculator' ); // phpcs:ignore ?> works out the first-hour rating your household needs.</li>
	<li><strong>Reversed connections or a melted dip tube.</strong> Usually found soon after a new install. The dip tube is a long plastic tube inside the tank, attached to the cold inlet; the manual says it can melt if copper pipes were soldered while connected to the heater.</li>
	<li><strong>A plumbing leak on the hot side.</strong> The manual says even a small one can make it seem the heater produces little hot water, with the burner running almost all the time.</li>
	<li><strong>Sediment or lime on the tank bottom,</strong> which cuts efficiency. Draining and flushing is the fix.</li>
</ul>
<p>Water that is too hot is a different problem: turn the setting down. The CPSC recommends setting water heaters to <?php echo esc_html( (string) hwm_value( $hwm_f, 'safety.cpscSetpointF' ) ); ?> °F to prevent scalds (<?php echo $hwm_ext( $hwm_r( 'cpsc-scald' ), 'CPSC' ); // phpcs:ignore ?>).</p>

<h2>Repair or replace a heater that will not heat?</h2>
<p>A heater that stops heating usually needs a part, not a replacement: elements, thermostats, gas controls and igniters are designed to be swapped. The exception is an old heater, where a repair may buy little time. Our rule, from Rheem's guidance: replace if the repair costs more than <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% of a new unit, or more than <?php echo esc_html( (string) ( $hwm_pct['old'] ?? '' ) ); ?>% once it is past <?php echo esc_html( (string) ( $hwm_pct['age'] ?? '' ) ); ?>% of its expected life. Enter the repair quote and tick "No hot water, or not enough".</p>
<?php
ob_start();
hwm_part( 'repair-inputs' );
hwm_part( 'calculator', array( 'tool' => 'repair-or-replace-water-heater', 'inputs' => (string) ob_get_clean(), 'label' => 'Repair or replace calculator' ) );
?>
<p>The age is printed in code in the serial number: our <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> decodes it. If the heater is also wet underneath, read <?php echo hwm_link( 'water-heater-leaking', 'water heater leaking' ); // phpcs:ignore ?> first, because a tank leak settles the question.</p>

<h2>When no hot water is a job for a licensed professional</h2>
<p>Resetting a breaker once, checking a valve and the knob, reading a code and relighting a pilot by its label are homeowner checks in the manufacturers' guidance. Call a licensed professional when a breaker or reset button trips again, when an element, thermostat or gas control needs replacing, when the pilot will not stay lit, when the status light shows a fault code you cannot clear, or whenever you smell gas. A. O. Smith's own advice: if you are not completely comfortable working on your water heater, call a licensed plumber.</p>

<h2>Limitations of this guide</h2>
<p>The checks restate A. O. Smith's troubleshooting guidance and its residential gas manual. Controls, codes and panel layouts differ by brand and model, and your manual wins. We give no wiring or gas repair steps on purpose. Read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>: an independent calculator site, not plumbers.</p>
<?php
$faq = array(
	array(
		'q' => 'Why is my water heater not heating?',
		'a' => 'On an electric heater, the most common cause is a tripped breaker, then a tripped reset button or a failed element or thermostat. On a gas heater, check that the gas valve is open, the knob is not on Off or Vacation, and the pilot or status light is normal. On a tankless unit, read the error code.',
	),
	array(
		'q' => 'Why do I have no hot water in only one faucet?',
		'a' => 'If other taps are hot, the heater is fine. A. O. Smith notes many shower controls have built-in mixing valves that can fail and cut the hot water at that one fixture. The valve or cartridge in that fixture needs repair.',
	),
	array(
		'q' => 'How long does it take for hot water to come back?',
		'a' => 'A. O. Smith says a gas tank needs about ' . hwm_value( $hwm_f, 'noHot.recoveryGasMinLow' ) . ' to ' . hwm_value( $hwm_f, 'noHot.recoveryGasMinHigh' ) . ' minutes and an electric tank ' . hwm_value( $hwm_f, 'noHot.recoveryElectricMinLow' ) . ' to ' . hwm_value( $hwm_f, 'noHot.recoveryElectricMinHigh' ) . ' minutes to recover. After relighting a gas heater, its manual says to check the troubleshooting section if there is still no hot water after ' . hwm_value( $hwm_f, 'noHot.waitHours' ) . ' hours.',
	),
	array(
		'q' => 'Where is the reset button on an electric water heater?',
		'a' => 'Behind the upper access panel, usually a red button. A. O. Smith says to turn the power off at the breaker first, remove the panel and press it firmly. If it trips again, call a professional: repeated tripping means a fault such as a shorted element or a bad thermostat.',
	),
	array(
		'q' => 'Why did my hot water stop after I flushed the tank?',
		'a' => 'On an electric heater, turning the power on before the tank was full can burn out the upper element, A. O. Smith warns. On a gas heater, the pilot or burner has to be relit by the label. A refilled tank also needs time to heat.',
	),
	array(
		'q' => 'Is no hot water a reason to replace the water heater?',
		'a' => 'Not by itself. Elements, thermostats and gas controls are replaceable parts. Replace the heater if the tank leaks, or if the repair costs more than ' . ( $hwm_pct['always'] ?? '' ) . '% of a new unit, or more than ' . ( $hwm_pct['old'] ?? '' ) . '% once the heater is old. Our repair-or-replace calculator works it out.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater not working? Common problems and fixes', 'url' => $hwm_r( 'aos-not-working' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Installation Instructions and Use & Care Guide (100400765): operation and troubleshooting', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater maintenance (refilling before restoring power)', 'url' => $hwm_r( 'aos-maintenance' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rinnai', 'title' => '13-step tankless water heater flushing procedure', 'url' => $hwm_r( 'rinnai-flush' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace', 'url' => $hwm_r( 'rheem-lifespan' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
);
