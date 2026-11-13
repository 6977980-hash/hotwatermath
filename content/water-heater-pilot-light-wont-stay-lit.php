<?php
/**
 * /water-heater-pilot-light-wont-stay-lit/ (STRATEGY §2 item 19, Phase 6d, CLAUDE.md §8b): gas tank water heater pilot.
 * Causes, status light codes and the lighting procedure restate A. O. Smith's Signature 100 manual (100341469), its gas
 * Use & Care Guide (100400765) and pilot light guide, and Rheem's GG Series manual (data/safety.json). The lighting steps
 * are the label's homeowner procedure, summarized and attributed; repairs (thermocouple, thermopile, pilot, gas control)
 * get no steps: the manuals assign them to a qualified technician.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f = 'safety';
$hwm_s = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, 'pilot.' . $path ) );
$hwm_r = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_pct = (array) hwm_raw( 'computed/repair-or-replace-water-heater', 'rulesPct' );

$related = array( 'no-hot-water', 'repair-or-replace-water-heater', 'water-heater-age-calculator', 'water-heater-reset-button', 'gas-line-size-for-tankless-water-heater' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => false, 'scald' => false ) );
?>
<aside class="notice" aria-label="Gas safety">
	<p><strong>Never relight if you smell gas</strong>, and do not light the heater if gasoline, paint thinner or other flammable vapors may be in the area: A. O. Smith's manual says to call a qualified person instead. If a lack of air may be the cause, A. O. Smith warns that the heater can release carbon monoxide; have a technician fix the air supply before relighting. This page explains the problem; the manuals we read leave repairs to the pilot, thermocouple, thermopile and gas control to a qualified technician.</p>
</aside>

<h2>Why won't a water heater pilot light stay lit?</h2>
<p>Usually because the gas valve no longer gets the signal that the flame is there. A thermocouple or thermopile sits in the pilot flame and powers the pilot safety valve; A. O. Smith explains that if it is broken, "the pilot will not remain lit once the pilot knob is released." The other common causes are drafts, low gas pressure, not enough combustion air, and a failed gas control.</p>
<p>The fastest way to narrow it down is to notice when the pilot goes out. A flame that dies the moment you let go of the knob points to a different cause than one that burns for an hour and then disappears.</p>
<dl>
	<dt>Pilot light</dt>
	<dd>The small flame that lights the main burner. A. O. Smith: a traditional pilot burns all the time, and the "whoosh" when the heater comes on is the valve sending gas to the burner, lit by that flame.</dd>
	<dt>Thermocouple and thermopile</dt>
	<dd>Heat sensors in the pilot flame that make a small electric current. Older standing-pilot heaters use a thermocouple; many newer ones use a thermopile, which also powers an electronic gas control and its status light. If the flame goes out, the current stops and the valve shuts the gas off.</dd>
	<dt>FVIR</dt>
	<dd>Flammable Vapor Ignition Resistant design, found on modern gas tank heaters. A. O. Smith says it reduces the risk of fires from flammable vapors, and that it makes the heater "more sensitive to installation errors or improper installation environments."</dd>
</dl>

<h2>When does the pilot go out? What that tells you</h2>
<div class="table-wrap" role="region" aria-label="Pilot outage by timing" tabindex="0">
<table>
	<caption>Narrowing it down by when the pilot goes out</caption>
	<thead><tr><th scope="col">What happens</th><th scope="col">Most likely causes in the manuals</th><th scope="col">Next step</th></tr></thead>
	<tbody>
		<tr><th scope="row">Pilot will not light at all</th><td>Air in the gas line, no gas, knob not fully on Pilot, clogged pilot orifice or tube, failed igniter (A. O. Smith, Rheem)</td><td>Check that the gas valve to the heater is open and other gas appliances work; after three tries by the label, a technician or the gas supplier</td></tr>
		<tr><th scope="row">Lights, but goes out when you release the knob</th><td>Thermocouple loose or failed, thermopile weak, pilot safety valve (safety magnet) failed (Rheem, A. O. Smith)</td><td>A qualified service technician; this is a parts repair</td></tr>
		<tr><th scope="row">Lit, status light stays dark for more than <?php echo $hwm_s( 'igniterSeconds' ); // phpcs:ignore ?> seconds</th><td>Weak pilot flame, defective thermopile or loose wiring (A. O. Smith)</td><td>A qualified service technician</td></tr>
		<tr><th scope="row">Stays lit for a while, then goes out</th><td>Drafts, not enough combustion air, low or unsteady gas pressure (A. O. Smith)</td><td>Look for an open door, fan or vent nearby and clutter around the heater; if it repeats, a technician and your gas utility</td></tr>
		<tr><th scope="row">Pilot and burner both shut down after very hot water</th><td>Energy cutoff (ECO) or thermal switch opened; on A. O. Smith's control, 4 flashes</td><td>Gas control replacement by a qualified technician</td></tr>
		<tr><th scope="row">Will not light after paint, solvent or gasoline use nearby</th><td>Flammable vapor system has shut the heater down</td><td>Do not relight; call a qualified person (A. O. Smith)</td></tr>
	</tbody>
</table>
</div>
<p class="table-source">Source: our summary of the troubleshooting charts in A. O. Smith's Signature 100 Use &amp; Care Guide (100341469), its residential gas Use &amp; Care Guide (100400765) and pilot light guide, and Rheem's GG Series Use &amp; Care Manual, checked <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.aos-signature-manual.checked' ) ); ?>.</p>

<h2>What the status light on a gas water heater means</h2>
<p>On heaters with a thermopile control, the status light is the diagnostic tool. On A. O. Smith's control, one flash every <?php echo $hwm_s( 'normalFlashSeconds' ); // phpcs:ignore ?> seconds means normal operation, and the light stays dark until the thermopile has warmed up, which can take up to <?php echo $hwm_s( 'igniterSeconds' ); // phpcs:ignore ?> seconds of pilot flame. Other flash counts point to a specific part.</p>
<?php echo hwm_table( $hwm_f, 'pilotStatus' ); // phpcs:ignore -- escaped in the part. ?>
<p>A solid light right after the pilot went out is not a fault: the manual says the thermopile is cooling and the control "requires waiting <?php echo $hwm_s( 'waitMinutes' ); // phpcs:ignore ?> minutes between lighting attempts." Lighting again sooner simply does not work on that control. Codes differ between brands and control makers, so check the chart in your own manual or on the label.</p>

<h2>How to relight the pilot the way the manufacturer says</h2>
<p>Use the lighting instructions on the label on your heater; A. O. Smith says those "supersede" any general guide, including this one. On an A. O. Smith heater with a push-button igniter, the label's steps are, in summary:</p>
<ol>
	<li>Read all the safety warnings on the label first.</li>
	<li>Turn the gas control knob to Off.</li>
	<li>Wait <?php echo $hwm_s( 'waitMinutes' ); // phpcs:ignore ?> minutes to clear out any gas. If you then smell gas, stop and follow the "what to do if you smell gas" instructions.</li>
	<li>Turn the knob to Pilot.</li>
	<li>Press the knob all the way in and hold it; it should travel in about <?php echo $hwm_s( 'knobTravelIn' ); // phpcs:ignore ?> inch. While holding it, click the igniter button about once a second for up to <?php echo $hwm_s( 'igniterSeconds' ); // phpcs:ignore ?> seconds, until the status light begins to blink.</li>
	<li>When the light blinks, release the knob and set it to the temperature you want.</li>
</ol>
<p>If the light does not blink within <?php echo $hwm_s( 'igniterSeconds' ); // phpcs:ignore ?> seconds, the manual allows up to <?php echo $hwm_s( 'attempts' ); // phpcs:ignore ?> attempts, waiting <?php echo $hwm_s( 'waitMinutes' ); // phpcs:ignore ?> minutes between each. After that, turn the knob to Off and "call a qualified service technician or your gas supplier." A newly installed heater is the one exception worth knowing: A. O. Smith notes it "will have air in the gas line" and may take several attempts.</p>
<p>Other heaters differ. On A. O. Smith's plug-in electronic models, the manual says the heater "will light automatically. DO NOT try to light the pilot by hand." On older manual models, A. O. Smith's guide says the pilot is lit with a long lighter while holding the knob. If your label and this summary disagree, the label is right.</p>

<?php hwm_ad( 'B' ); ?>

<h2>Thermocouple, thermopile or gas valve: which part failed?</h2>
<p>The manuals separate them by symptom, and none of them gives a homeowner the repair. Rheem's chart for a pilot that "does not stay lit when the RED button is released" lists a loose thermocouple, a failed thermocouple, a failed safety magnet and an opened single-use gas shut-off, each with the same instruction: a qualified service technician tightens or replaces it. A. O. Smith's chart for a pilot that "will not light or remain lit" adds gas supply, air and control causes, summarized below.</p>
<?php echo hwm_table( $hwm_f, 'pilotCauses' ); // phpcs:ignore -- escaped in the part. ?>
<p>Many repair guides tell homeowners to clean the pilot orifice with a wire or to swap the thermocouple themselves. The manufacturers we checked do not: Rheem's manual says the pilot "should be cleaned or replaced by a qualified service technician" and warns "DO NOT attempt repair of gas piping, gas control (thermostat), burners, vent connectors or other safety devices."</p>

<h2>Why the flammable vapor system can shut the heater down</h2>
<p>Because it is designed to. A. O. Smith says "improper installation or an inadequate air supply can also cause the FVIR system to disable the water heater." A heater that has been exposed to a flammable vapors incident "will show a discoloration on the flame arrestor and require replacement of the entire water heater." The system has a resettable thermal switch, and the manual warns: "Do not attempt to disable or modify this feature in any way."</p>
<p>In practice: keep gasoline, paint, solvents and cleaners away from the heater, keep the air openings at the bottom clear of lint and debris, as Rheem's chart advises, and do not try to relight after a vapor incident.</p>

<h2>What a pilot light repair costs</h2>
<p>The parts are inexpensive; the service visit is most of the bill. On <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.hd-pilot-parts.checked' ) ); ?>, The Home Depot listed universal water heater thermocouples at $<?php echo $hwm_s( 'thermocoupleLow' ); // phpcs:ignore ?> to $<?php echo $hwm_s( 'thermocoupleHigh' ); // phpcs:ignore ?>, pilot and thermopile assemblies at $<?php echo $hwm_s( 'pilotAssemblyLow' ); // phpcs:ignore ?> to $<?php echo $hwm_s( 'pilotAssemblyHigh' ); // phpcs:ignore ?>, and gas control valves at $<?php echo $hwm_s( 'gasControlLow' ); // phpcs:ignore ?> to $<?php echo $hwm_s( 'gasControlHigh' ); // phpcs:ignore ?>. FVIR heaters use the model-specific pilot assembly listed in their manual's parts list, not always a universal thermocouple.</p>
<p>We found no labor price we would stand behind, so we do not publish one. What we can tell you is whether a quote is worth paying: Rheem's rule, used in our calculator, is to replace the heater if the repair costs more than <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% of a new one, or more than <?php echo esc_html( (string) ( $hwm_pct['old'] ?? '' ) ); ?>% once it is past <?php echo esc_html( (string) ( $hwm_pct['age'] ?? '' ) ); ?>% of its expected life.</p>
<?php
ob_start();
hwm_part( 'repair-inputs', array( 'checked' => array( 'noHot' ) ) );
hwm_part( 'calculator', array( 'tool' => 'repair-or-replace-water-heater', 'inputs' => (string) ob_get_clean(), 'label' => 'Repair or replace calculator' ) );
?>
<p>Not sure how old the heater is? The <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> reads it from the serial number.</p>

<h2>Common mistakes with a pilot that keeps going out</h2>
<ul>
	<li><strong>Relighting again and again without waiting.</strong> The manual's <?php echo $hwm_s( 'waitMinutes' ); // phpcs:ignore ?>-minute wait clears gas and lets the thermopile cool.</li>
	<li><strong>Releasing the knob too soon.</strong> Hold it until the status light blinks, up to <?php echo $hwm_s( 'igniterSeconds' ); // phpcs:ignore ?> seconds.</li>
	<li><strong>Ignoring the status light.</strong> Its flash count names the part.</li>
	<li><strong>Relighting when the problem is air supply.</strong> A. O. Smith links a lack of air to carbon monoxide; fix the air first.</li>
	<li><strong>Storing paint or gasoline near the heater.</strong> It can trigger the vapor system and is a fire risk.</li>
	<li><strong>Taking the burner apart.</strong> The manuals leave pilot, thermocouple and gas control work to a technician.</li>
</ul>

<h2>When to call a technician or the gas utility</h2>
<p>Call a qualified service technician when the pilot will not stay lit after the label's lighting attempts, when the status light shows a fault code, when you see a yellow or lazy flame, or when you suspect an air supply problem. Call your gas utility if other gas appliances also have trouble, which A. O. Smith links to supply pressure "outside the water heater", and right away if you smell gas. A heater whose controls have been under water is a replacement, not a repair: A. O. Smith's manual says it must be replaced.</p>
<p>See also our guides to <?php echo hwm_link( 'no-hot-water', 'no hot water' ); // phpcs:ignore ?>, which covers the checks before the pilot, and the <?php echo hwm_link( 'water-heater-reset-button', 'reset button' ); // phpcs:ignore ?> on electric heaters.</p>

<h2>Limitations of this guide</h2>
<p>Status codes, the lighting steps and the cause lists come from A. O. Smith and Rheem manuals; other brands and gas control makers use different procedures and codes, and your label and manual win. Part prices are listing prices on one day. Gas tankless units work differently; follow that manual. We are an independent calculator site, not plumbers or gas technicians: read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'Why does my water heater pilot light keep going out?',
		'a' => 'The manuals list drafts, too little combustion air, low gas pressure, a weak or failed thermocouple or thermopile, a clogged pilot, and a failed gas control. If it goes out when you release the knob, suspect the thermocouple or thermopile; if it dies later, suspect drafts, air supply or gas pressure.',
	),
	array(
		'q' => 'How long do you hold the pilot button on a water heater?',
		'a' => 'On A. O. Smith\'s thermopile control, hold the knob in and click the igniter for up to ' . hwm_value( 'safety', 'pilot.igniterSeconds' ) . ' seconds, until the status light starts blinking, then release. The thermopile can take that long to warm up. Other brands differ, so follow the label on your heater.',
	),
	array(
		'q' => 'How do I know if my thermocouple is bad?',
		'a' => 'The classic sign is a pilot that lights but goes out as soon as you let go of the knob; Rheem lists a loose or failed thermocouple first for that symptom. On a thermopile control, two flashes on A. O. Smith\'s status light mean the thermopile is not making enough voltage. A technician confirms and replaces it.',
	),
	array(
		'q' => 'Why do I have to wait 10 minutes to relight a water heater?',
		'a' => 'To let any gas clear out before you light it, and on thermopile controls to let the thermopile cool. A. O. Smith\'s label says to wait ' . hwm_value( 'safety', 'pilot.waitMinutes' ) . ' minutes, and its control "requires waiting 10 minutes between lighting attempts". If you smell gas after waiting, stop.',
	),
	array(
		'q' => 'Can a dirty air intake make the pilot go out?',
		'a' => 'Yes. A. O. Smith lists insufficient combustion air as a cause and says an inadequate air supply can make the flammable vapor system disable the heater. Keep the area around the bottom of the heater clear and clean. If air supply is the problem, a technician should fix it before relighting.',
	),
	array(
		'q' => 'How much does it cost to fix a pilot light that won\'t stay lit?',
		'a' => 'Parts listed at The Home Depot for $' . hwm_value( 'safety', 'pilot.thermocoupleLow' ) . ' to $' . hwm_value( 'safety', 'pilot.thermocoupleHigh' ) . ' for a thermocouple and up to $' . hwm_value( 'safety', 'pilot.gasControlHigh' ) . ' for a gas control when we checked; the service call adds more. On an old heater, compare the quote with a new one in the repair or replace calculator.',
	),
	array(
		'q' => 'Is it safe to relight a water heater pilot myself?',
		'a' => 'A. O. Smith says most pilot lights can be relit by a homeowner, following the label. Never relight if you smell gas or if flammable vapors may be present, and stop after three failed attempts. Repairs to the pilot, thermocouple or gas control are for a qualified technician.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Signature 100 gas water heater Use & Care Guide (100341469, March 2021): lighting instructions, troubleshooting, status light codes', 'url' => $hwm_r( 'aos-signature-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How to relight a water heater\'s pilot light', 'url' => $hwm_r( 'aos-pilot-guide' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): FVIR system, flammable vapors, air in the gas line, flood rule', 'url' => $hwm_r( 'aos-gas-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Gas Residential Water Heaters GG Series Use & Care Manual: troubleshooting chart', 'url' => $hwm_r( 'rheem-gg-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater thermocouples, pilot assemblies and gas control valves (listing prices)', 'url' => $hwm_r( 'hd-pilot-parts' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace', 'url' => (string) hwm_raw( 'troubleshooting', 'references.rheem-lifespan.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
