<?php
/**
 * /water-heater-temperature-setting/ (STRATEGY §2 item 21, Phase 6d, CLAUDE.md §8b): what to set the tank to.
 * Safety claims restate CPSC Publication 5098, DOE FEMP, CDC Legionella guidance and A. O. Smith's Use & Care Guides
 * (data/safety.json). The capacity table is data/computed/water-heater-temperature-setting.json (formulas.js).
 * Adjustment steps are limited to what CPSC and the manufacturer tell owners to do, with the power off.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'safety';
$hwm_c   = 'computed/water-heater-temperature-setting';
$hwm_s   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_sc  = static fn( int $row, string $col ): string => esc_html( strtolower( hwm_value( 'safety', 'tables.scald.rows.' . $row . '.' . $col ) ) ); // 0 = 120 °F, 2 = 130 °F, 4 = 140 °F.

$related = array( 'hot-water-runs-out-fast', 'water-heater-size-calculator', 'water-heater-expansion-tank-size', 'gas-vs-electric-water-heater', 'water-heater-smells-like-rotten-eggs' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>What temperature should a water heater be set at?</h2>
<p>Set it to <?php echo $hwm_s( 'temp.cpscF' ); // phpcs:ignore ?> °F. The U.S. Consumer Product Safety Commission "urges all users to lower their water heaters to 120 degrees Fahrenheit", and the Department of Energy's purchasing guide says "for most residential applications, set the temperature at 120°F." A. O. Smith ships its electric tanks set to about <?php echo $hwm_s( 'temp.aosElectricFactoryF' ); // phpcs:ignore ?> °F for the same reason: to reduce the risk of scalding.</p>
<p>That number is a starting point, not a law of nature. Two groups of facts pull in opposite directions: hotter water burns faster, and water that sits too cool lets bacteria grow. The rest of this page shows both, with the source for each, so you can pick the setting that fits your household.</p>
<dl>
	<dt>Thermostatic mixing valve</dt>
	<dd>A valve at a tap, shower or the heater outlet that blends hot and cold water to a set temperature, so the tank can be kept hotter than the water that reaches your skin. CDC and A. O. Smith both name it as the way to run a hot tank without scalding. It is also called a tempering or anti-scald valve.</dd>
</dl>

<h2>120 vs 140 °F: which setting is right?</h2>
<p>For most homes, 120 °F. Choose 140 °F only if you install mixing valves at the taps, or the heater feeds a household where Legionella is a bigger worry than burns and you have taken advice on it. The table puts the two settings side by side.</p>
<div class="table-wrap" role="region" aria-label="120 °F vs 140 °F water heater setting" tabindex="0">
<table>
	<caption>120 °F vs 140 °F water heater setting</caption>
	<thead>
		<tr><th scope="col"></th><th scope="col">120 °F</th><th scope="col">140 °F</th></tr>
	</thead>
	<tbody>
		<tr><th scope="row">Who recommends it</th><td>CPSC (scald prevention), DOE FEMP (most homes)</td><td>CDC for storing hot water in building water systems (Legionella control)</td></tr>
		<tr><th scope="row">Time for a serious burn (A. O. Smith)</th><td><?php echo ucfirst( $hwm_sc( 0, 'aos' ) ); // phpcs:ignore ?></td><td><?php echo ucfirst( $hwm_sc( 4, 'aos' ) ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Legionella</th><td>CDC's lower limit: growth is possible when warm water falls below 120 °F</td><td>CDC's storage temperature for hot water</td></tr>
		<tr><th scope="row">Hot water per tank (mixed to a <?php echo $hwm_v( 'mixF' ); // phpcs:ignore ?> °F shower)</th><td>Baseline</td><td>About <?php echo $hwm_v( 'extra140' ); // phpcs:ignore ?>% more, with a mixing valve</td></tr>
		<tr><th scope="row">Energy</th><td>Less heat lost from the tank; CPSC says lowering it saves energy and money</td><td>More heat lost from a hotter tank and pipes</td></tr>
		<tr><th scope="row">Water growth when heated (50-gal tank, from 40 °F)</th><td><?php echo $hwm_v( 'exp120' ); // phpcs:ignore ?>%</td><td><?php echo $hwm_v( 'exp140' ); // phpcs:ignore ?>%, more strain on the relief valve without an expansion tank</td></tr>
		<tr><th scope="row">Needs a mixing valve?</th><td>No, though A. O. Smith still recommends them</td><td>Yes, at each tap or at the heater</td></tr>
	</tbody>
</table>
</div>
<p class="table-source">Sources: CPSC Publication 5098; DOE FEMP; CDC Legionella toolkit and monitoring guidance; A. O. Smith gas Use &amp; Care Guide; expansion from AMTROL's table in our <?php echo hwm_link( 'water-heater-expansion-tank-size', 'expansion tank calculator' ); // phpcs:ignore ?>. Checked <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.cdc-potable.checked' ) ); ?>.</p>

<h2>How hot is too hot? Scald times by temperature</h2>
<p>Every 5 °F above 120 °F cuts the time to a serious burn sharply. A. O. Smith's manual puts it at <?php echo $hwm_sc( 0, 'aos' ); // phpcs:ignore ?> at 120 °F, <?php echo $hwm_sc( 2, 'aos' ); // phpcs:ignore ?> at 130 °F and <?php echo $hwm_sc( 4, 'aos' ); // phpcs:ignore ?> at 140 °F. The CPSC's figures for adults are close: <?php echo $hwm_sc( 0, 'cpsc' ); // phpcs:ignore ?>, <?php echo $hwm_sc( 2, 'cpsc' ); // phpcs:ignore ?> and <?php echo $hwm_sc( 4, 'cpsc' ); // phpcs:ignore ?>. Children and older adults burn faster.</p>
<?php echo hwm_table( $hwm_f, 'scald' ); // phpcs:ignore -- escaped in the part. ?>
<p>The CPSC says most tap water scald injuries and deaths happen to older people and to children under five. If anyone like that lives with you, or visits often, 120 °F at the tap matters more than any other number on this page.</p>
<p>The dial letters in the table are A. O. Smith's gas control. A. O. Smith calls them approximate, and other brands use other markings, so check your own heater's label or manual before you turn anything.</p>

<h2>Does 120 °F let Legionella grow?</h2>
<p>It is the edge, not the danger zone. CDC's guidance for building water systems is to store hot water above <?php echo $hwm_s( 'temp.cdcStoreF' ); // phpcs:ignore ?> °F and keep it from falling below <?php echo $hwm_s( 'temp.cdcCirculateF' ); // phpcs:ignore ?> °F in the pipes, and it says growth is possible when warm water falls below 120 °F. For homes rented to guests, CDC says to set the heater "at or above 120°F".</p>
<p>CDC also says water at <?php echo $hwm_s( 'temp.cdcKillLowF' ); // phpcs:ignore ?> to <?php echo $hwm_s( 'temp.cdcKillHighF' ); // phpcs:ignore ?> °F "can kill many harmful germs, but also increases the scalding risk", and that anyone who sets the heater above 120 °F should use thermostatic mixing valves at the faucet or shower. People catch Legionnaires' disease by breathing in mist that carries the bacteria, such as shower spray, and CDC says certain people are at increased risk. If someone in your home has a weakened immune system or a chronic lung condition, ask their doctor which setting to use.</p>
<p>What we found no source for: a single home temperature that is both scald-safe and Legionella-proof without mixing valves. That is why the official advice splits into "120 °F at the tap" and "hotter in storage with mixing valves". Also, A. O. Smith warns that repeated short draws can stack the tank in layers up to <?php echo $hwm_s( 'temp.stackingF' ); // phpcs:ignore ?> °F hotter than the setting, so the dial is never the only safeguard; its manual cites the ASSE 1070 standard and most plumbing codes saying the thermostat "should not be used as the sole means" to prevent scalds.</p>

<h2>How much more hot water do you get at a higher setting?</h2>
<p>About <?php echo $hwm_v( 'extra130' ); // phpcs:ignore ?>% more at 130 °F and <?php echo $hwm_v( 'extra140' ); // phpcs:ignore ?>% more at 140 °F, if a mixing valve brings the water back down to the same shower temperature. A hotter tank needs less of its own water per shower, so the same tank lasts longer. A. O. Smith says the same in words: a higher setting "allows the tank to provide much more hot water". We worked out how much.</p>
<?php echo hwm_table( $hwm_c, 'capacity' ); // phpcs:ignore -- escaped in the part. ?>
<p>The math, so you can redo it with your numbers: the share of a mixed shower that comes from the tank is (shower temperature − incoming water) ÷ (tank temperature − incoming water). At <?php echo $hwm_v( 'inletF' ); // phpcs:ignore ?> °F incoming water, a <?php echo $hwm_v( 'mixF' ); // phpcs:ignore ?> °F shower takes <?php echo $hwm_v( 'share120' ); // phpcs:ignore ?>% from a <?php echo $hwm_v( 'base' ); // phpcs:ignore ?> °F tank, or <?php echo $hwm_v( 'gal120' ); // phpcs:ignore ?> gallons in <?php echo $hwm_v( 'minutes' ); // phpcs:ignore ?> minutes at <?php echo $hwm_v( 'gpm' ); // phpcs:ignore ?> GPM, and <?php echo $hwm_v( 'share140' ); // phpcs:ignore ?>% (<?php echo $hwm_v( 'gal140' ); // phpcs:ignore ?> gallons) from a 140 °F tank.</p>
<p>Without a mixing valve, turning the tank up just means hotter water at the tap and a higher scald risk. If you keep running out at 120 °F, check whether the heater is simply too small before you turn it up.</p>
<?php
ob_start();
hwm_part( 'tank-size-inputs' );
hwm_part( 'calculator', array( 'tool' => 'water-heater-size-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater size check' ) );
?>
<p>For other causes, such as a failed element or a broken dip tube, see <?php echo hwm_link( 'hot-water-runs-out-fast', 'hot water runs out fast' ); // phpcs:ignore ?>.</p>

<h2>How to check your hot water temperature</h2>
<p>Measure it at a tap, because the dial is approximate. The CPSC's method:</p>
<ol>
	<li>Pick a time when no one has used hot water for at least <?php echo $hwm_s( 'temp.thermometerWaitHours' ); // phpcs:ignore ?> hours, or first thing in the morning.</li>
	<li>Run the hot water at the tap nearest the heater and hold a candy or meat thermometer in the stream.</li>
	<li>If the reading is too high, adjust the thermostat by the manufacturer's instructions, wait, and measure again.</li>
</ol>
<p>Measure at more than one tap if your house is large. A reading well above the setting can be the stacking A. O. Smith describes, a hot room around the heater, or a failing control; the last one is a job for a qualified person.</p>

<h2>How to adjust a gas or electric water heater</h2>
<p>Follow your heater's manual. Here is what the sources say owners can do, and what to leave alone.</p>
<ul>
	<li><strong>Gas tank:</strong> the temperature knob on the front of the gas control sets it. On A. O. Smith's control, "HOT" is about <?php echo $hwm_s( 'temp.cpscF' ); // phpcs:ignore ?> °F and each letter (A, B, C) adds about 10 °F; the manual says the markings are approximate. Turning the knob is all an owner does. Do not touch gas fittings or the burner, and if you smell gas, leave and call your gas supplier.</li>
	<li><strong>Electric tank:</strong> the CPSC says most electric water heaters have two thermostats, behind an upper and a lower access panel, and both must be set to the same temperature. Turn the power off at the breaker first: A. O. Smith warns that contact with the parts behind the access doors can cause severe injury or death from electric shock. Some newer electric models, like A. O. Smith's electronic one, have a front control panel instead.</li>
	<li><strong>Tankless and heat pump water heaters:</strong> set them on the unit's display or remote control as the manual describes. DOE's guide says mixing valves on residential systems should be set to 120 °F.</li>
	<li><strong>Apartment or a boiler-fed system:</strong> the CPSC suggests asking the building manager or fuel supplier to lower it.</li>
</ul>
<p>If you are not comfortable working near electrical parts, the CPSC notes that some electric utilities will adjust the thermostat for you.</p>

<h2>What setting to use when you go away</h2>
<p>Turn it down, not off, for a short trip. A. O. Smith's gas control has a "VAC" setting of about <?php echo $hwm_s( 'temp.vacationF' ); // phpcs:ignore ?> °F, its electronic electric model has a vacation mode, and its electric manual says to lower the setting to save energy if you plan to be gone for an extended time.</p>
<p>After a long absence, A. O. Smith gives one warning that most guides miss: hydrogen gas can build up in a hot water system that has not been used for <?php echo $hwm_s( 'temp.hydrogenWeeks' ); // phpcs:ignore ?> weeks or more, and it is extremely flammable. Open a hot water faucet at the kitchen sink for several minutes before you use any electrical appliance connected to the hot water, and keep any flame or spark away from the faucet.</p>

<h2>Common mistakes with the temperature setting</h2>
<ul>
	<li><strong>Trusting the dial.</strong> Markings are approximate and tanks can stack hotter. Measure at the tap.</li>
	<li><strong>Setting only one thermostat on an electric tank.</strong> The CPSC says both must match.</li>
	<li><strong>Turning it up to fix a small tank</strong> without mixing valves. You get hotter water, not more of it.</li>
	<li><strong>Going far below 120 °F to save money.</strong> CDC's guidance treats water below 120 °F as a condition where Legionella can grow.</li>
	<li><strong>Ignoring a relief valve that starts dripping after you turn it up.</strong> Hotter water expands more; see <?php echo hwm_link( 'water-heater-pressure-relief-valve-leaking', 'water heater pressure relief valve leaking' ); // phpcs:ignore ?>.</li>
</ul>

<h2>When the temperature is a job for a professional</h2>
<p>Call a licensed plumber when the water is much hotter than the setting, when the relief valve releases hot water, when you want mixing valves installed, or when the temperature will not change after you adjust it. A. O. Smith's manual says that if the tank overheats or the burner fails to shut off, turn off the gas supply to the heater and call a qualified person. On an electric tank, a thermostat that does not switch off is a part to be replaced, often after the red reset button trips; see <?php echo hwm_link( 'water-heater-reset-button', 'water heater reset button' ); // phpcs:ignore ?>.</p>

<h2>Limitations of this guide</h2>
<p>Scald times come from A. O. Smith's manual and the CPSC; burn risk varies with age and skin. CDC's temperature limits are written for building water systems, and we quote them as written rather than turning them into a home rule of our own. Our capacity table ignores heat lost in pipes, so real tanks deliver a little less. Local codes and state laws may set a maximum temperature at the tap. We are an independent calculator site, not plumbers or doctors: read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'What temperature should my water heater be set at?',
		'a' => 'Set it to 120 °F. The CPSC urges all users to lower their water heaters to 120 °F to prevent scalds, and the Department of Energy says 120 °F suits most homes. Use a higher setting only with thermostatic mixing valves at the taps.',
	),
	array(
		'q' => 'Is 140 °F too hot for a water heater?',
		'a' => 'At the tap, yes: A. O. Smith says 140 °F water can cause a serious burn in ' . strtolower( hwm_value( 'safety', 'tables.scald.rows.4.aos' ) ) . '. In the tank it can be fine if mixing valves bring the water down to about 120 °F before it reaches you. CDC uses 140 °F as its storage temperature for Legionella control.',
	),
	array(
		'q' => 'Is 120 °F hot enough to kill bacteria?',
		'a' => 'It is CDC\'s lower limit, not a kill temperature. CDC says Legionella growth is possible when warm water falls below 120 °F, and that 130 to 140 °F can kill many germs but raises scald risk. Households at higher risk should ask a doctor and use mixing valves with a hotter tank.',
	),
	array(
		'q' => 'What number on a water heater dial is 120 degrees?',
		'a' => 'On A. O. Smith gas controls, the "HOT" mark is about 120 °F, "A" about 130 °F and "B" about 140 °F. The manual calls these approximate, and other brands mark dials differently. Measure the water at a tap with a thermometer to be sure.',
	),
	array(
		'q' => 'Does lowering the water heater temperature save money?',
		'a' => 'Yes. The CPSC says lowering the temperature to 120 °F will conserve energy and save money, because a cooler tank loses less heat while it waits. The saving depends on how hot it was before and how much hot water you use; it is not a reason to go below 120 °F.',
	),
	array(
		'q' => 'Why is my hot water hotter than the setting?',
		'a' => 'A. O. Smith says repeated short draws can stack the tank in layers up to 30 °F hotter than the setting. A hot room, solar pre-heating or a failing control can also do it. If the water stays much hotter than the setting, have the control checked by a qualified person.',
	),
	array(
		'q' => 'What is 120 °F in Celsius?',
		'a' => '120 °F is 49 °C, and 140 °F is 60 °C. CDC and A. O. Smith give both units in their guidance.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy, FEMP', 'title' => 'Purchasing Energy-Efficient Residential Water Heaters', 'url' => $hwm_r( 'doe-femp' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'CDC', 'title' => 'Controlling Legionella in Potable Water Systems (Legionella Control Toolkit)', 'url' => $hwm_r( 'cdc-potable' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'CDC', 'title' => 'Monitoring Building Water', 'url' => $hwm_r( 'cdc-monitor' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'CDC', 'title' => 'Considerations for Vacation Rental Owners and Managers', 'url' => $hwm_r( 'cdc-rental' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'CDC', 'title' => 'About Legionnaires\' Disease', 'url' => $hwm_r( 'cdc-about' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): scald table, gas control settings, hydrogen warning', 'url' => $hwm_r( 'aos-gas-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Electronic Thermostat Electric Water Heater Use & Care Guide (2000598248)', 'url' => $hwm_r( 'aos-electric-manual' ), 'accessed' => '2026-11-13' ),
);
