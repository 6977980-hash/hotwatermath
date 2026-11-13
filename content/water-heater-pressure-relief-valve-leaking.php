<?php
/**
 * /water-heater-pressure-relief-valve-leaking/ (STRATEGY §2 item 24, Phase 6d, CLAUDE.md §8b): the T&P relief valve.
 * Facts restate A. O. Smith's gas and electric Use & Care Guides and Watts' warning tag and spec sheet (data/safety.json).
 * The expansion tank calculator is embedded through parts/expansion-tank-inputs.php. No valve repair or replacement
 * steps: Watts says repair or alteration is prohibited, and A. O. Smith sends replacement to a qualified person.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f = 'safety';
$hwm_c = 'computed/water-heater-pressure-relief-valve-leaking';
$hwm_s = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_v = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_r = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );

$related = array( 'water-heater-expansion-tank-size', 'water-heater-leaking', 'repair-or-replace-water-heater', 'water-heater-temperature-setting', 'water-heater-installation-cost' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>Why is my water heater pressure relief valve leaking?</h2>
<p>Usually because the valve is doing its job. A. O. Smith's manual says a small drip "usually means the home's water pressure is too high and/or you need a Thermal Expansion Tank." A lot of very hot water pouring out is different: it points to a tank that is overheating, and A. O. Smith says to turn off the gas supply and call a qualified person. Only when pressure and expansion are under control does a drip mean the valve itself has failed.</p>
<dl>
	<dt>Temperature and pressure relief valve (T&amp;P valve)</dt>
	<dd>The brass safety valve on the top or upper side of a tank water heater, with a pipe running down toward the floor. It opens if the water gets too hot or the pressure too high. Watts' standard valves open at <?php echo $hwm_s( 'tp.tempReliefF' ); // phpcs:ignore ?> °F, or at a set pressure of <?php echo $hwm_s( 'tp.setPsiOptions' ); // phpcs:ignore ?> psi, and are certified to <?php echo $hwm_s( 'tp.standard' ); // phpcs:ignore ?>. A. O. Smith says the valve's pressure setting must not exceed the working pressure on the heater's rating plate.</dd>
</dl>
<p>Never cap or plug the valve or its pipe to stop a leak. A. O. Smith warns that an explosion could occur if the valve or discharge pipe is blocked, and that the heater must not run without a working relief valve.</p>

<h2>Drip or gush: what the leak is telling you</h2>
<p>How much water comes out, and when, tells you most of what you need. Find your case in the table, then read the section on pressure below.</p>
<?php echo hwm_table( $hwm_f, 'tpCauses' ); // phpcs:ignore -- escaped in the part. ?>
<p>Watts' tag on the valve is blunt about any discharge during normal use: it "indicates an unsafe temperature or pressure condition or a problem with this valve", and its advice is to call a plumber. The steps in the table that an owner can do are reading a pressure gauge and lifting the test lever as the label describes. Everything else belongs to a qualified person.</p>

<h2>What water pressure makes a relief valve drip?</h2>
<p>Pressure is the most common cause, and three numbers matter. A. O. Smith says most codes allow a maximum incoming water pressure of <?php echo $hwm_s( 'tp.codeMaxPsi' ); // phpcs:ignore ?> psi, and recommends setting the home's pressure-reducing valve to <?php echo $hwm_s( 'tp.prvLowPsi' ); // phpcs:ignore ?> to <?php echo $hwm_s( 'tp.prvHighPsi' ); // phpcs:ignore ?> psi. Watts says system pressure must not exceed <?php echo $hwm_v( 'sharePct' ); // phpcs:ignore ?>% of the valve's set pressure, which is <?php echo $hwm_v( 'maxOperatingPsi' ); // phpcs:ignore ?> psi for a common <?php echo $hwm_s( 'tp.commonSetPsi' ); // phpcs:ignore ?> psi valve.</p>
<figure class="figure">
	<svg viewBox="0 0 640 220" width="640" height="220" role="img" aria-labelledby="tp-fig-t tp-fig-d">
		<title id="tp-fig-t">Water pressure scale for a home with a 150 psi relief valve</title>
		<desc id="tp-fig-d">A horizontal scale from 0 to 160 psi. 50 to 60 psi is the range A. O. Smith recommends for the pressure-reducing valve. 80 psi is the maximum incoming pressure most codes allow. 112.5 psi is Watts' upper limit for normal operation with a 150 psi valve. At 150 psi the valve opens.</desc>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<rect x="40" y="90" width="560" height="24" rx="4" fill="#f1f4f7" stroke="#4a5562"/>
			<rect x="215" y="90" width="35" height="24" fill="#2e7d4f"/>
			<rect x="250" y="90" width="70" height="24" fill="#e7f2ea"/>
			<rect x="320" y="90" width="114" height="24" fill="#fdf1d8"/>
			<rect x="434" y="90" width="131" height="24" fill="#f8dcd9"/>
			<path d="M565 80 V124" stroke="#a8231a" stroke-width="3"/>
			<path d="M320 84 V120" stroke="#7a4f00" stroke-width="2"/>
			<path d="M434 84 V120" stroke="#7a4f00" stroke-width="2" stroke-dasharray="4 3"/>
			<text x="40" y="136">0</text><text x="207" y="136"><?php echo $hwm_s( 'tp.prvLowPsi' ); // phpcs:ignore ?></text><text x="241" y="136"><?php echo $hwm_s( 'tp.prvHighPsi' ); // phpcs:ignore ?></text><text x="311" y="136"><?php echo $hwm_s( 'tp.codeMaxPsi' ); // phpcs:ignore ?></text><text x="420" y="136"><?php echo $hwm_v( 'maxOperatingPsi' ); // phpcs:ignore ?></text><text x="553" y="136"><?php echo $hwm_s( 'tp.commonSetPsi' ); // phpcs:ignore ?></text><text x="585" y="136">psi</text>
			<text x="150" y="60"><?php echo $hwm_s( 'tp.prvLowPsi' ); // phpcs:ignore ?>–<?php echo $hwm_s( 'tp.prvHighPsi' ); // phpcs:ignore ?> psi: set the</text>
			<text x="150" y="76">pressure-reducing valve here</text>
			<path d="M232 80 V90" stroke="#2e7d4f"/>
			<text x="270" y="166"><?php echo $hwm_s( 'tp.codeMaxPsi' ); // phpcs:ignore ?> psi: most codes'</text>
			<text x="270" y="182">maximum incoming pressure</text>
			<text x="400" y="40"><?php echo $hwm_v( 'maxOperatingPsi' ); // phpcs:ignore ?> psi: <?php echo $hwm_v( 'sharePct' ); // phpcs:ignore ?>% of a <?php echo $hwm_s( 'tp.commonSetPsi' ); // phpcs:ignore ?> psi</text>
			<text x="400" y="56">valve, Watts' normal limit</text>
			<path d="M434 60 V84" stroke="#7a4f00"/>
			<text x="470" y="166" fill="#a8231a"><?php echo $hwm_s( 'tp.commonSetPsi' ); // phpcs:ignore ?> psi: the</text>
			<text x="470" y="182" fill="#a8231a">valve opens</text>
			<text x="40" y="210" font-size="12">Thermal expansion in a closed system can push pressure up from your normal level toward the right of this scale.</text>
		</g>
	</svg>
	<figcaption>Pressure levels for a home with a 150 psi relief valve, drawn by Hot Water Math from A. O. Smith's manual and Watts' specification sheet. Your valve's setting is stamped on its tag.</figcaption>
</figure>
<p>To find your pressure, A. O. Smith's manual shows a water pressure gauge; the common kind threads onto an outdoor hose faucet. A reading above <?php echo $hwm_s( 'tp.codeMaxPsi' ); // phpcs:ignore ?> psi is the first thing to fix. A. O. Smith adds that many utilities have raised supply pressure over the years, and some homes now see more than <?php echo $hwm_s( 'tp.someHomesPsi' ); // phpcs:ignore ?> psi. Adjusting or adding a pressure-reducing valve on the main is plumbing work; A. O. Smith lists it among the code upgrades to make when a heater is installed.</p>

<h2>Thermal expansion: the drip that comes after a hot shower</h2>
<p>Water grows when it is heated, and in a closed system it has nowhere to go. A <?php echo $hwm_v( 'heater' ); // phpcs:ignore ?>-gallon tank heated from 40 °F to 120 °F grows about <?php echo $hwm_v( 'exp120' ); // phpcs:ignore ?>% (<?php echo $hwm_v( 'exp120Gal' ); // phpcs:ignore ?> gallon), and about <?php echo $hwm_v( 'exp140' ); // phpcs:ignore ?>% at 140 °F. A. O. Smith explains why that is now the norm: federal law requires backflow preventers and check valves that stop water from flowing back into the public system, often hidden inside the water meter, so most plumbing systems are now "closed". With no room to expand, pressure climbs until the relief valve lets some water out.</p>
<p>A thermal expansion tank gives it somewhere to go. A. O. Smith recommends a pressurized, properly sized expansion tank in almost all homes, with its air charge set to match the home's water pressure and never above <?php echo $hwm_s( 'tp.expTankPrechargeMaxPsi' ); // phpcs:ignore ?> psi. New tanks come pre-charged to about <?php echo $hwm_s( 'tp.expTankPrechargeMinPsi' ); // phpcs:ignore ?> psi, so an expansion tank that was never adjusted can be the reason a relief valve still drips. Size one here:</p>
<?php
ob_start();
hwm_part( 'expansion-tank-inputs' );
hwm_part( 'calculator', array( 'tool' => 'water-heater-expansion-tank-size', 'inputs' => (string) ob_get_clean(), 'label' => 'Expansion tank size calculator' ) );
?>
<p>The method and the check against AMTROL's published chart are on the <?php echo hwm_link( 'water-heater-expansion-tank-size', 'expansion tank size calculator' ); // phpcs:ignore ?> page. If the relief valve only started dripping after the heater was turned up, read <?php echo hwm_link( 'water-heater-temperature-setting', 'water heater temperature setting' ); // phpcs:ignore ?>: hotter water expands more.</p>

<h2>How to test a water heater relief valve</h2>
<p>Both A. O. Smith and Watts tell the owner to operate the valve by hand at least once a year, to make sure its waterways are clear. Follow the yellow label on your valve; if it has none, A. O. Smith's manual gives these points:</p>
<ol>
	<li>Check that the discharge pipe is attached and runs to a place where very hot water can go safely. Nobody should stand in front of the outlet.</li>
	<li>Lift the test lever. Hot water will be released, and it can cause severe burns.</li>
	<li>Let the lever snap back, then wipe up any water and empty the drain pan.</li>
	<li>If water does not flow freely from the end of the pipe, turn the heater off and call a plumber. Watts says to call immediately: the tank has no working protection.</li>
</ol>
<p>Testing can leave a valve dripping, because scale is dislodged onto the seat. A. O. Smith's troubleshooting section suggests operating the valve to flush debris; if it still drips with normal pressure and a working expansion tank, the valve may be broken. A valve that has dripped for a long time, A. O. Smith adds, should be inspected for mineral buildup. Watts also asks for an inspection by a licensed plumbing contractor or qualified technician at least every <?php echo $hwm_s( 'tp.inspectEveryYears' ); // phpcs:ignore ?> years.</p>

<h2>Can you fix or replace a relief valve yourself?</h2>
<p>You do not repair one: Watts' tag says "repair or alteration of this valve by anyone is prohibited by national safety standards/local codes." When a valve has failed, A. O. Smith's guidance is to have a qualified person replace it. A replacement must match the heater: A. O. Smith says its pressure setting must not exceed the tank's working pressure, Watts says its BTU discharge capacity must exceed the heater's input rating, and valves come with different shank lengths for different tanks. A. O. Smith also says not to reuse an old valve on a new heater.</p>
<p>The part is cheap. Water heater T&amp;P valves at The Home Depot listed for $<?php echo $hwm_s( 'tp.priceLow' ); // phpcs:ignore ?> to $<?php echo $hwm_s( 'tp.priceHigh' ); // phpcs:ignore ?> on <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.hd-tp-valves.checked' ) ); ?>, depending on brand and shank length. Most of the bill is the visit and the labor, which varies by area; we do not publish a labor figure for this job because we found no source we trust for it. The fix you need may also be a pressure-reducing valve or an expansion tank rather than a valve. If you get a quote and the heater is old, the <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace calculator' ); // phpcs:ignore ?> tells you whether the repair is worth it.</p>

<h2>Relief valve discharge pipe rules</h2>
<p>A leak you can see is a good thing: the pipe is meant to show you. A. O. Smith's manual and Watts' tag set out how it must run:</p>
<div class="table-wrap" role="region" aria-label="Relief valve discharge pipe requirements" tabindex="0">
<table>
	<caption>Relief valve discharge pipe requirements</caption>
	<thead>
		<tr><th scope="col">Rule</th><th scope="col">What it says</th><th scope="col">Source</th></tr>
	</thead>
	<tbody>
		<tr><td>Size</td><td>At least <?php echo $hwm_s( 'tp.dischargeMinIn' ); // phpcs:ignore ?> inch inside diameter, never smaller than the valve outlet, no reducer</td><td>A. O. Smith</td></tr>
		<tr><td>Nothing in the way</td><td>No valve, cap, plug or other restriction between the tank, the valve and the end of the pipe</td><td>A. O. Smith, Watts</td></tr>
		<tr><td>Slope</td><td>Pitched downward so the valve and pipe drain completely</td><td>A. O. Smith, Watts</td></tr>
		<tr><td>Where it ends</td><td>No more than <?php echo $hwm_s( 'tp.terminateMaxIn' ); // phpcs:ignore ?> inches above a floor drain or outside; not into the drain pan; never connected straight to a sewer line; inside the building in cold climates so it cannot freeze</td><td>A. O. Smith, Watts</td></tr>
		<tr><td>Air gap</td><td>At least <?php echo $hwm_s( 'tp.airGapIn' ); // phpcs:ignore ?> inches, through plain unthreaded pipe, where any discharge is clearly visible</td><td>Watts</td></tr>
		<tr><td>Material</td><td>Copper or CPVC approved for discharge use; not PVC, iron, flexible plastic or a hose</td><td>A. O. Smith</td></tr>
		<tr><td>Length</td><td>More than <?php echo $hwm_s( 'tp.maxLengthFt' ); // phpcs:ignore ?> feet or more than <?php echo $hwm_s( 'tp.maxElbows' ); // phpcs:ignore ?> elbows restricts the valve's capacity</td><td>Watts</td></tr>
	</tbody>
</table>
</div>
<p class="table-source">Sources: A. O. Smith gas Use &amp; Care Guide 100400765; Watts relief valve warning tag. Local plumbing codes can add to these. Checked <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.watts-tag.checked' ) ); ?>.</p>
<p>If the pipe ends in the drain pan, is threaded at the end, or has been cut off short, point it out to whoever fixes the leak. Those are code problems, and fixing them is part of the same job.</p>

<h2>Common mistakes with a leaking relief valve</h2>
<ul>
	<li><strong>Capping the pipe or the valve.</strong> A. O. Smith warns this can cause an explosion.</li>
	<li><strong>Replacing the valve without checking pressure.</strong> A new valve drips just like the old one if the pressure is still too high.</li>
	<li><strong>Adding an expansion tank and leaving it at the factory air charge.</strong> It must match your water pressure.</li>
	<li><strong>Ignoring a gush of hot water.</strong> That is overheating, not a pressure drip. Turn the heater off.</li>
	<li><strong>Reusing an old valve on a new heater.</strong> A. O. Smith says not to.</li>
</ul>

<h2>When a relief valve leak is a job for a licensed plumber</h2>
<p>Call one when hot water or steam pours out, when nothing comes out during a test, when the valve keeps dripping with pressure in the <?php echo $hwm_s( 'tp.prvLowPsi' ); // phpcs:ignore ?> to <?php echo $hwm_s( 'tp.prvHighPsi' ); // phpcs:ignore ?> psi range and a working expansion tank, when your pressure is above <?php echo $hwm_s( 'tp.codeMaxPsi' ); // phpcs:ignore ?> psi, or when the discharge pipe breaks the rules above. Those are the cases A. O. Smith and Watts send to a qualified person. Until then, if the valve is releasing very hot water, keep the heater off: on gas, turn the gas control off; on electric, switch off the breaker.</p>

<h2>Limitations of this guide</h2>
<p>The figures come from A. O. Smith's manuals and Watts' valve documents; other brands publish their own, and the tag on your valve wins. Local codes can set stricter discharge and pressure rules. We give no labor price because we found no source we would stand behind. We are an independent calculator site, not plumbers: read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>. For other leaks around the tank, see <?php echo hwm_link( 'water-heater-leaking', 'water heater leaking' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'Why is my water heater relief valve dripping?',
		'a' => 'A. O. Smith says a small drip usually means the home\'s water pressure is too high or the house needs a thermal expansion tank. Check pressure with a gauge; above ' . hwm_value( 'safety', 'tp.codeMaxPsi' ) . ' psi is too high. If pressure is ' . hwm_value( 'safety', 'tp.prvLowPsi' ) . ' to ' . hwm_value( 'safety', 'tp.prvHighPsi' ) . ' psi and an expansion tank is working, the valve may be broken.',
	),
	array(
		'q' => 'Is a leaking pressure relief valve dangerous?',
		'a' => 'A drip is a warning, not an emergency, but do not ignore it or plug it. A lot of very hot water pouring out means the tank is overheating: turn the gas control or breaker off and call a qualified person, as A. O. Smith advises. Never cap the valve or its pipe.',
	),
	array(
		'q' => 'How often should I test my water heater relief valve?',
		'a' => 'At least once a year, by lifting the test lever as the label on the valve describes. A. O. Smith and Watts both say so. Watts also asks for an inspection by a licensed plumbing contractor at least every ' . hwm_value( 'safety', 'tp.inspectEveryYears' ) . ' years. If no water flows during a test, turn the heater off and call a plumber.',
	),
	array(
		'q' => 'At what pressure does a water heater relief valve open?',
		'a' => 'At its set pressure, commonly ' . hwm_value( 'safety', 'tp.commonSetPsi' ) . ' psi, or at ' . hwm_value( 'safety', 'tp.tempReliefF' ) . ' °F water temperature, for Watts\' standard valves. The setting is stamped on the valve\'s tag and must not exceed the working pressure on the heater\'s rating plate. Watts says normal system pressure should stay below ' . hwm_value( 'computed/water-heater-pressure-relief-valve-leaking', 'figures.sharePct' ) . '% of the set pressure.',
	),
	array(
		'q' => 'How much does it cost to replace a water heater relief valve?',
		'a' => 'The valve itself listed for about $' . hwm_value( 'safety', 'tp.priceLow' ) . ' to $' . hwm_value( 'safety', 'tp.priceHigh' ) . ' at The Home Depot when we checked. Labor depends on your area. Watts says the valve must not be repaired, and A. O. Smith says to have a qualified person replace a broken one.',
	),
	array(
		'q' => 'Will an expansion tank stop my relief valve from leaking?',
		'a' => 'If thermal expansion is the cause, yes. A. O. Smith recommends a properly sized expansion tank in almost all homes, with its air charge set to your water pressure and never above ' . hwm_value( 'safety', 'tp.expTankPrechargeMaxPsi' ) . ' psi. If the valve still drips with one installed, check the pressure and the tank\'s air charge.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): relief valve, water pressure, expansion tank, troubleshooting', 'url' => $hwm_r( 'aos-gas-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Electronic Thermostat Electric Water Heater Use & Care Guide (2000598248)', 'url' => $hwm_r( 'aos-electric-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Watts Regulator Co.', 'title' => 'Temperature and pressure relief valve warning tag', 'url' => $hwm_r( 'watts-tag' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Watts', 'title' => 'Series 1L, 1XL, 10L, 100XL temperature and pressure relief valves: specification sheet', 'url' => $hwm_r( 'watts-spec' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater temperature and pressure relief valves (listing prices)', 'url' => $hwm_r( 'hd-tp-valves' ), 'accessed' => '2026-11-13' ),
);
