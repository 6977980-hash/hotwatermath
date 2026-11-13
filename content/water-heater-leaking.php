<?php
/**
 * /water-heater-leaking/ (STRATEGY §2 item 17, Phase 6b batch 1): symptom article with the repair-or-replace tool embedded.
 * Facts and figures come from data/troubleshooting.json (each with its reference), data/installation-costs.json
 * and data/computed/repair-or-replace-water-heater.json. The relief valve section explains and cites only
 * (CLAUDE.md §8b); the full relief valve page is built in Phase 6d.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_ic  = static fn( string $path ): string => esc_html( hwm_value( 'installation-costs', $path, 0 ) );
$hwm_rr  = 'computed/repair-or-replace-water-heater';
$hwm_pct = (array) hwm_raw( $hwm_rr, 'rulesPct' );
$hwm_ls  = static fn( string $path ): string => esc_html( hwm_value( 'lifespans', $path ) );

$related = array( 'repair-or-replace-water-heater', 'water-heater-age-calculator', 'water-heater-cost-calculator', 'how-to-flush-a-water-heater', 'water-heater-anode-rod' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>What to do first when your water heater is leaking</h2>
<p>Stop the heat and stop the water, then find the source. A. O. Smith says tank water heaters hold <?php echo $hwm_t( 'leak.tankGallonsLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'leak.tankGallonsHigh' ); // phpcs:ignore ?> gallons, so a leak that gets worse can flood a room within hours. These steps follow the manufacturer's own guidance.</p>
<ol>
	<li><strong>Turn off the heat.</strong> On an electric heater, switch off its breaker. On a gas heater, turn the gas control to Off and close the gas shut-off valve on the supply pipe (the handle ends up across the pipe).</li>
	<li><strong>Turn off the cold water.</strong> Close the shut-off valve on the cold pipe that enters the top of the tank. If there is no valve there, or water is spraying, close your home's main water valve.</li>
	<li><strong>Protect the area.</strong> Mop up, move boxes off the floor and run fans. A. O. Smith warns that water soaking into drywall, flooring or insulation can grow mold within <?php echo $hwm_t( 'leak.moldHoursLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'leak.moldHoursHigh' ); // phpcs:ignore ?> hours. If the heater is in an attic or upstairs, check the ceiling below.</li>
	<li><strong>Drain the tank only if the tank itself leaks.</strong> Run a garden hose from the drain valve to a floor drain or outside and open the valve. Most tanks take <?php echo $hwm_t( 'leak.drainMinutesLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'leak.drainMinutesHigh' ); // phpcs:ignore ?> minutes to empty. The water stays hot for hours, so keep the hose end away from people and pets.</li>
	<li><strong>Find the source</strong> with the table below before you decide on a repair.</li>
</ol>

<h2>Is it really a leak, or condensation?</h2>
<p>Not every puddle is a leak. Wipe the tank and floor completely dry, then look again. A. O. Smith's rule: if the moisture comes back within <?php echo $hwm_t( 'leak.condensationWatchHours' ); // phpcs:ignore ?> hours, you have a leak.</p>
<dl>
	<dt>Condensation</dt>
	<dd>Water vapor from the air, or from the burner's exhaust, that turns to water on a cold surface. It is common on a gas water heater that has just been filled or after heavy hot water use, when the tank is full of cold water. The A. O. Smith manual says you may hear drips or a sizzle on the burner and see a small puddle, and that condensation "is not a leak and will go away" once the tank is at its normal temperature.</dd>
</dl>
<p>Condensation shows up when the tank is cold and fades as it heats. A real leak keeps coming whether the tank is hot or cold, and it usually leaves rust trails or mineral crust where it starts.</p>

<h2>Where is the water coming from?</h2>
<p>A. O. Smith says tank leaks typically start at <?php echo $hwm_t( 'leak.pointsCount' ); // phpcs:ignore ?> points: the relief valve, the drain valve, the pipe connections on top and the tank bottom. Electric and gas heaters each add one more. The place the water comes from tells you most of what you need to know.</p>
<figure class="figure">
	<svg viewBox="0 0 640 380" width="640" height="380" role="img" aria-labelledby="leak-map-t leak-map-d">
		<title id="leak-map-t">Where a tank water heater leaks</title>
		<desc id="leak-map-d">A tank water heater with six leak points marked: 1 pipe connections on top, 2 the temperature and pressure relief valve and its discharge pipe, 3 the drain valve near the bottom, 4 a heating element gasket on an electric tank, 5 the burner area of a gas tank, 6 the tank bottom.</desc>
		<g fill="none" stroke="#17212e" stroke-width="2">
			<rect x="150" y="70" width="170" height="250" rx="22" fill="#eef3f8"/>
			<path d="M195 70 V30 M275 70 V30"/>
			<path d="M320 110 H352 V300"/>
			<rect x="318" y="98" width="20" height="24" fill="#ffffff"/>
			<path d="M150 290 H126 v12"/>
			<rect x="134" y="186" width="16" height="30" fill="#ffffff"/>
			<path d="M170 320 v22 M300 320 v22"/>
			<rect x="196" y="326" width="78" height="14" fill="#ffffff"/>
		</g>
		<path d="M140 352 q40 -10 90 0 t110 0" fill="none" stroke="#0b4a7d" stroke-width="3"/>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<text x="185" y="22">Cold in</text>
			<text x="262" y="22">Hot out</text>
			<text x="236" y="200" text-anchor="middle">Tank</text>
		</g>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" font-weight="700" fill="#ffffff">
			<circle cx="235" cy="46" r="12" fill="#a8231a"/><text x="235" y="51" text-anchor="middle">1</text>
			<circle cx="372" cy="110" r="12" fill="#a8231a"/><text x="372" y="115" text-anchor="middle">2</text>
			<circle cx="106" cy="296" r="12" fill="#a8231a"/><text x="106" y="301" text-anchor="middle">3</text>
			<circle cx="114" cy="201" r="12" fill="#a8231a"/><text x="114" y="206" text-anchor="middle">4</text>
			<circle cx="235" cy="306" r="12" fill="#a8231a"/><text x="235" y="311" text-anchor="middle">5</text>
			<circle cx="318" cy="318" r="12" fill="#a8231a"/><text x="318" y="323" text-anchor="middle">6</text>
		</g>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<text x="420" y="60">1 Pipe connections: repair</text>
			<text x="420" y="110">2 Relief valve or its pipe: repair</text>
			<text x="420" y="160">3 Drain valve: repair</text>
			<text x="420" y="210">4 Element gasket (electric): repair</text>
			<text x="420" y="260">5 Burner area (gas): check first</text>
			<text x="420" y="310">6 Tank bottom or body:</text>
			<text x="436" y="328">replace the heater</text>
		</g>
	</svg>
	<figcaption>The six places a tank leaks. Only a leak from the tank itself (6) forces a replacement. Diagram by Hot Water Math from the A. O. Smith leak guide.</figcaption>
</figure>
<?php echo hwm_table( $hwm_f, 'leakPoints' ); // phpcs:ignore -- escaped in the part. ?>

<h2>Water heater leaking from the bottom</h2>
<p>A leak from the bottom is the one to take most seriously, but check two things before you assume the worst. First, look straight up: water from a fitting or the relief valve can run down the side and drip off the bottom. Second, look at the drain valve: a valve that is not fully closed, or has a worn washer, drips right at the base.</p>
<p>If the drain valve is dry and nothing is running down from above, the water is coming from the tank itself. A. O. Smith says a leak from the base "is often the first indication of a serious problem", usually corrosion that has eaten through the steel, and Rheem says a leak from the tank itself usually means replacement (<?php echo $hwm_ext( $hwm_r( 'rheem-lifespan' ), 'Rheem' ); // phpcs:ignore ?>). There is no lasting repair for a corroded tank. Drain it, keep the water off, and plan the replacement.</p>
<p>Sediment plays a part here. A. O. Smith's manual warns that failing to drain and flush sediment "can deteriorate the tank and drain valve and cause leaks." Our guide on <?php echo hwm_link( 'how-to-flush-a-water-heater', 'how to flush a water heater' ); // phpcs:ignore ?> covers the schedule.</p>

<h2>Water heater leaking from the top</h2>
<p>A leak on top is usually the easiest to fix. The cold inlet and hot outlet connect there, and A. O. Smith says the fittings can work loose from thermal expansion and wear or corrode over time. Look for wet pipe, green or white mineral crust, or rust where the pipes meet the tank.</p>
<ul>
	<li><strong>A loose fitting:</strong> A. O. Smith suggests tightening it gently with a wrench and warns that overtightening can damage the fitting and make the leak worse.</li>
	<li><strong>A corroded fitting or nipple:</strong> it needs replacing. A. O. Smith says corroded fittings need professional replacement, and a fitting that keeps loosening after you tighten it is worn out.</li>
	<li><strong>The anode rod port:</strong> on many tanks the anode rod screws into the top. A leak at its hex head points to that fitting; the A. O. Smith manual reinstalls rods with thread sealant tape or pipe joint compound. See our <?php echo hwm_link( 'water-heater-anode-rod', 'water heater anode rod guide' ); // phpcs:ignore ?>.</li>
	<li><strong>The relief valve:</strong> on some tanks it sits on top. See the next section.</li>
</ul>

<h2>Water heater leaking from the pressure relief valve</h2>
<p>The temperature and pressure relief valve (T&amp;P valve) is a safety device that opens to let water out if the tank gets too hot or too pressurized. A small drip from it usually means your water pressure is too high or the house needs a thermal expansion tank, not that the valve is broken, according to the A. O. Smith manual.</p>
<dl>
	<dt>Thermal expansion</dt>
	<dd>Water grows in volume as it heats. In a home with a check valve or pressure reducing valve on the main line, that extra water has nowhere to go, so pressure rises each time the heater runs. An expansion tank, a small tank with an air cushion fitted to the cold line, absorbs it.</dd>
</dl>
<p>What the manuals say to do, in plain terms:</p>
<ul>
	<li><strong>Never cap or plug the valve or its discharge pipe,</strong> and never run the heater without a working valve. The A. O. Smith manual says this "could cause an explosion."</li>
	<li><strong>If it is discharging a lot of very hot water,</strong> the tank may be overheating: turn the gas supply off (or the breaker on an electric unit) and call a qualified person.</li>
	<li><strong>Check the pressure.</strong> A. O. Smith says household pressure should be <?php echo $hwm_t( 'leak.pressurePrvLowPsi' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'leak.pressurePrvHighPsi' ); // phpcs:ignore ?> psi and no higher than <?php echo $hwm_t( 'leak.pressureMaxPsi' ); // phpcs:ignore ?> psi; Rheem suggests <?php echo $hwm_t( 'leak.rheemPsiLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'leak.rheemPsiHigh' ); // phpcs:ignore ?>. The manual suggests a pressure gauge on an outdoor faucet, read over a full day because pressure often peaks at night. If it runs high, a pressure reducing valve on the main line is the fix.</li>
	<li><strong>Add or check the expansion tank.</strong> Our installation cost data puts an expansion tank, parts and labor, at $<?php echo $hwm_ic( 'extras.expansionTank.low' ); // phpcs:ignore ?> to $<?php echo $hwm_ic( 'extras.expansionTank.high' ); // phpcs:ignore ?> (typical $<?php echo $hwm_ic( 'extras.expansionTank.typical' ); // phpcs:ignore ?>).</li>
	<li><strong>If pressure is right, an expansion tank is in place and it still drips,</strong> the valve may be broken. The manual says to have a qualified person replace it.</li>
</ul>
<p>A. O. Smith says homeowners should test the valve once a year by the instructions on its label, and a professional should inspect it every <?php echo $hwm_t( 'leak.tpInspectProYears' ); // phpcs:ignore ?> years. A full guide to the relief valve is on our list of safety pages; until it is published, follow the label on your valve and your heater's manual.</p>

<h2>Gas and electric water heaters: leaks that need extra care</h2>
<p>Both fuels leak in the same places, but each adds one spot where water and energy meet. Treat those two with more caution than a dripping pipe.</p>
<ul>
	<li><strong>Electric: at a heating element.</strong> A. O. Smith says failed gaskets around the elements let water seep out of the side of the tank where the elements pass through the steel. Water near live electrical parts is a shock hazard, so the breaker stays off until it is fixed. The repair is a new gasket or element.</li>
	<li><strong>Gas: at the burner.</strong> Moisture near the burner or the gas connection needs a professional right away, according to A. O. Smith, because water can upset combustion and create a carbon monoxide risk. The exception is the brief condensation on a cold tank described above.</li>
</ul>

<h2>Can a leaking water heater be repaired?</h2>
<p>Yes, unless the water comes from the tank itself. Fittings, valves, gaskets and elements are parts that can be replaced. A corroded tank cannot, and Rheem says a leak from the tank itself usually means replacement.</p>
<p>For a repairable leak, the next question is whether the repair is worth it on a heater of that age. Storage tanks typically last <?php echo $hwm_ls( 'types.gas_tank.typical' ); // phpcs:ignore ?> years, by U.S. Department of Energy data (see <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?>). Our rule, from Rheem's guidance: replace if a repair costs more than <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% of a new heater, and once a tank is near the end of its life, replace if it costs more than <?php echo esc_html( (string) ( $hwm_pct['old'] ?? '' ) ); ?>%. Tick "Water leaking from the tank itself" below and the tool goes straight to replace.</p>
<?php
ob_start();
hwm_part( 'repair-inputs' );
hwm_part( 'calculator', array( 'tool' => 'repair-or-replace-water-heater', 'inputs' => (string) ob_get_clean(), 'label' => 'Repair or replace calculator' ) );
?>
<p>Not sure how old the heater is? The <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> reads the build date from the serial number. If you are replacing it, the <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?> prices the new unit and installation in your state.</p>

<h2>How to prevent the next leak</h2>
<p>Most leaks start small, and routine care catches them early. The schedule below is what A. O. Smith publishes for tank heaters.</p>
<ul>
	<li><strong>Flush the tank</strong> after the first <?php echo $hwm_t( 'flush.firstFlushMonths' ); // phpcs:ignore ?> months, then <?php echo $hwm_t( 'flush.thenEvery' ); // phpcs:ignore ?>, more often with hard water.</li>
	<li><strong>Inspect the anode rod</strong> at the same time. A. O. Smith says rods usually last <?php echo $hwm_t( 'anode.lifeYearsLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'anode.lifeYearsHigh' ); // phpcs:ignore ?> years; once it is gone, the tank itself starts to corrode.</li>
	<li><strong>Test the relief valve</strong> once a year by its label.</li>
	<li><strong>Keep the pressure in range</strong> and fit an expansion tank if your system is closed.</li>
	<li><strong>Look for moisture, rust and mineral crust</strong> at every connection a few times a year.</li>
	<li><strong>Fit a metal drain pan piped to a drain and an automatic leak detector,</strong> both of which the A. O. Smith manual recommends, especially where a leak would reach finished rooms.</li>
</ul>

<h2>When a leak is a job for a licensed professional</h2>
<p>Drying the area, closing valves, tightening a loose fitting and checking the pressure are homeowner jobs. Call a licensed plumber when the tank itself leaks, when water is near the burner or the electrical parts, when the relief valve discharges hot water or keeps dripping after the pressure is fixed, or when a fitting is corroded. Replacing a water heater involves gas or electrical connections, venting and the relief valve, which are covered by code, and your area may require a permit.</p>

<h2>Limitations of this guide</h2>
<p>This page restates manufacturer guidance, mainly A. O. Smith's leak guide and its residential gas manual, with Rheem where it adds something. Your model's manual can differ, and it wins. Costs are national planning figures, not quotes. We are not plumbers; see <?php echo hwm_link( 'methodology', 'our methodology' ); // phpcs:ignore ?> for how we source and check every number.</p>
<?php
$faq = array(
	array(
		'q' => 'Is a leaking water heater an emergency?',
		'a' => 'Treat it as urgent. Tank heaters hold ' . hwm_value( $hwm_f, 'leak.tankGallonsLow' ) . ' to ' . hwm_value( $hwm_f, 'leak.tankGallonsHigh' ) . ' gallons, and A. O. Smith warns a serious leak can flood a home in hours. Turn off the power or gas and the cold water now. Water spraying, hissing, or water near the burner or wiring is an emergency.',
	),
	array(
		'q' => 'Can I still use my water heater if it is leaking?',
		'a' => 'No. A. O. Smith advises against using a leaking heater: turn off the power or gas and the water supply until the cause is fixed. A small leak can still damage floors and walls and grow mold, and leaks tend to get worse.',
	),
	array(
		'q' => 'Why is my water heater leaking from the bottom?',
		'a' => 'Check the drain valve and look for water running down from a fitting or the relief valve first. If those are dry, the steel tank has most likely corroded through. A corroded tank cannot be repaired, so the heater has to be replaced.',
	),
	array(
		'q' => 'Why does my water heater relief valve drip?',
		'a' => 'Usually because the water pressure is too high or there is no working thermal expansion tank, says A. O. Smith. Pressure should be ' . hwm_value( $hwm_f, 'leak.pressurePrvLowPsi' ) . ' to ' . hwm_value( $hwm_f, 'leak.pressurePrvHighPsi' ) . ' psi. Never cap or plug the valve. If it still drips with the pressure right and an expansion tank fitted, a qualified person should replace it.',
	),
	array(
		'q' => 'Can a leaking water heater be repaired?',
		'a' => 'Leaks from fittings, the drain valve, the relief valve or an element gasket can be repaired, because those are replaceable parts. A leak from the tank itself cannot. Our repair-or-replace calculator weighs a repair quote against the heater\'s age and a new unit\'s price.',
	),
	array(
		'q' => 'Is water under a gas water heater always a leak?',
		'a' => 'No. A. O. Smith says condensation often forms on a cold gas heater after it is filled or after heavy use, sometimes with a small puddle and a sizzle on the burner. It stops once the tank heats. If the floor is wet again a few hours after you dry it, it is a leak.',
	),
	array(
		'q' => 'How long does it take to drain a leaking water heater?',
		'a' => 'Most residential tanks take ' . hwm_value( $hwm_f, 'leak.drainMinutesLow' ) . ' to ' . hwm_value( $hwm_f, 'leak.drainMinutesHigh' ) . ' minutes to empty through a garden hose, according to A. O. Smith. Opening a hot water faucet lets air in and speeds it up. The water can stay scalding for hours.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'What to do when your hot water heater is leaking', 'url' => $hwm_r( 'aos-leaking' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater leaking: causes, signs and fixes', 'url' => $hwm_r( 'aos-leak-guide' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Installation Instructions and Use & Care Guide (100400765): troubleshooting and maintenance', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater not working? Common problems and fixes (leaks, pressure)', 'url' => $hwm_r( 'aos-not-working' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater maintenance (T&P valve inspection)', 'url' => $hwm_r( 'aos-maintenance' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace (March 25, 2026)', 'url' => $hwm_r( 'rheem-lifespan' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
);
