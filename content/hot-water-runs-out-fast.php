<?php
/**
 * /hot-water-runs-out-fast/ (STRATEGY §2 item 27, Phase 6b batch 2): symptom article with the tank size calculator
 * embedded. Causes restate A. O. Smith's electric and gas Use & Care Guides and its reheat pages; the winter vs summer
 * table is data/computed/hot-water-runs-out-fast.json (formulas.hotShareOfMix, hotGallonsDrawn, temperatureRise).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_c   = 'computed/hot-water-runs-out-fast';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';

$related = array( 'water-heater-size-calculator', '40-vs-50-gallon-water-heater', 'tankless-water-heater-size-calculator', 'how-to-flush-a-water-heater', 'no-hot-water' );
hwm_part( 'safety-notice', array( 'gas' => false, 'power' => true, 'scald' => true ) );
?>
<h2>Why does my hot water run out so fast?</h2>
<p>Either the heater makes less hot water than it used to, or your household uses more than it can make. A. O. Smith's manuals list the usual causes: a heater too small for your use, a failed lower heating element on an electric tank, sediment, a melted dip tube or reversed connections, a thermostat set too low, a faulty shower mixing valve, and a hidden hot-water leak. Winter makes all of them worse.</p>
<p>Three questions narrow it down fast:</p>
<ol>
	<li><strong>Has it always been like this?</strong> If yes, the heater is probably too small for your busiest hour, or your household has grown. If it got worse recently, something has failed.</li>
	<li><strong>Is it every hot tap, or one shower?</strong> If only one shower runs short while the kitchen stays hot, the fault is that shower's mixing valve, not the heater.</li>
	<li><strong>Did it start right after an install or plumbing work?</strong> Then suspect reversed connections or a melted dip tube.</li>
</ol>
<?php echo hwm_table( $hwm_f, 'runsOutCauses' ); // phpcs:ignore -- escaped in the part. ?>

<h2>Is my water heater too small?</h2>
<p>Compare its first-hour rating with your busiest hour. The first-hour rating (FHR) is how many gallons of hot water a tank can deliver in an hour starting full, and it is printed on the yellow EnergyGuide label. A. O. Smith's example: a 40-gallon tank might have a <?php echo $hwm_t( 'runsOut.exampleFhr40' ); // phpcs:ignore ?>-gallon first-hour rating, because it heats more water while you use it.</p>
<dl>
	<dt>First-hour rating (FHR)</dt>
	<dd>The amount of hot water, in gallons, a storage water heater supplies in one hour of heavy use, starting with a full tank. It combines the stored water with what the heater can reheat in that hour, so two tanks of the same size can have different ratings.</dd>
</dl>
<p>Enter your busiest hour below. The calculator adds up showers, laundry and dishes the way the industry sizing worksheet does and gives the first-hour rating you need. If yours is lower, the heater is undersized for that hour.</p>
<?php
ob_start();
hwm_part( 'tank-size-inputs' );
hwm_part( 'calculator', array( 'tool' => 'water-heater-size-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater size check' ) );
?>
<p>Deciding between two sizes? See <?php echo hwm_link( '40-vs-50-gallon-water-heater', '40 vs 50 gallon water heater' ); // phpcs:ignore ?>. For an undersized heater in good condition, A. O. Smith suggests a cheaper option before replacing it: install thermostatic mixing valves at each tap and turn the tank up, so a hotter tank stretches further once it is mixed down. Without those valves, a higher setting raises the scald risk.</p>

<h2>Why does hot water run out faster in winter?</h2>
<p>Colder water comes in. A. O. Smith says water entering your home might be <?php echo $hwm_t( 'runsOut.winterInletLowF' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'runsOut.winterInletHighF' ); // phpcs:ignore ?> °F in winter and <?php echo $hwm_t( 'runsOut.summerInletLowF' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'runsOut.summerInletHighF' ); // phpcs:ignore ?> °F in summer, which adds <?php echo $hwm_t( 'runsOut.winterExtraMinLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'runsOut.winterExtraMinHigh' ); // phpcs:ignore ?> minutes to recovery. That hits you twice: each shower needs more hot water from the tank, and the tank reheats each gallon more slowly.</p>
<p>We worked out both effects for ten states. A shower at <?php echo $hwm_v( 'figures.mixF' ); // phpcs:ignore ?> °F (the shower temperature in Stiebel Eltron's sizing sheet) from a <?php echo $hwm_v( 'figures.hotF' ); // phpcs:ignore ?> °F tank, at the federal maximum of <?php echo $hwm_v( 'figures.gpm' ); // phpcs:ignore ?> gallons per minute, for <?php echo $hwm_v( 'figures.minutes' ); // phpcs:ignore ?> minutes:</p>
<?php echo hwm_table( $hwm_c, 'winterSummer' ); // phpcs:ignore -- escaped in the part. ?>
<p>The same shower uses <?php echo $hwm_v( 'figures.minExtraPct' ); // phpcs:ignore ?>% to <?php echo $hwm_v( 'figures.maxExtraPct' ); // phpcs:ignore ?>% more tank water in winter, and each gallon takes <?php echo $hwm_v( 'figures.minSlowPct' ); // phpcs:ignore ?>% to <?php echo $hwm_v( 'figures.maxSlowPct' ); // phpcs:ignore ?>% longer to reheat. Warm states see the biggest swing, because their summer water is so warm. A tank that just covers your mornings in October can come up short in January.</p>
<p>The math, so you can redo it: the share of tank water in a mixed shower is (shower temperature − inlet) ÷ (tank temperature − inlet). With <?php echo $hwm_v( 'figures.example.inletF' ); // phpcs:ignore ?> °F inlet water that is (<?php echo $hwm_v( 'figures.mixF' ); // phpcs:ignore ?> − <?php echo $hwm_v( 'figures.example.inletF' ); // phpcs:ignore ?>) ÷ (<?php echo $hwm_v( 'figures.hotF' ); // phpcs:ignore ?> − <?php echo $hwm_v( 'figures.example.inletF' ); // phpcs:ignore ?>) = <?php echo $hwm_v( 'figures.example.pct' ); // phpcs:ignore ?>% from the tank. Heat lost in the pipes is not counted, so real showers draw a little more. Inlet temperatures by state are estimates explained on <?php echo hwm_link( 'methodology', 'our methodology page' ); // phpcs:ignore ?>.</p>

<h2>Lower heating element failed (electric water heaters)</h2>
<p>This is the classic cause on an electric tank that used to be fine. A. O. Smith's electric manual says that if the lower element stops working, you still get some hot water, but not as much as before, and that the lower element usually wears out first because it does most of the work. Its heat-up guide adds that losing one of the two elements doubles recovery time.</p>
<p>A failed element is a replaceable part. Testing and replacing it means working near live terminals with the tank drained, so have a qualified person do it unless your manual's steps are something you are trained for. Our <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace calculator' ); // phpcs:ignore ?> tells you whether the repair is worth it on an older heater.</p>

<h2>Broken or melted dip tube</h2>
<p>The dip tube is a long plastic tube inside the tank, attached to the cold inlet, that carries incoming cold water to the bottom. If it breaks or melts, cold water mixes into the hot water at the top, and your shower turns lukewarm fast.</p>
<p>A. O. Smith's manual says the dip tube can melt if copper pipes were soldered while connected to the heater, and that this, like reversed hot and cold connections, is usually found soon after a new installation. The fix is to remove the cold inlet connection and fit a new dip tube. Melted heat traps from the same soldering can also cut the hot water flow.</p>

<h2>Sediment, a low setting, a mixing valve or a leak</h2>
<ul>
	<li><strong>Sediment.</strong> A. O. Smith says sediment acts like a blanket between the heat source and the water. It usually announces itself with popping or rumbling: see <?php echo hwm_link( 'water-heater-making-noise', 'water heater making noise' ); // phpcs:ignore ?>, and flush the tank (<?php echo hwm_link( 'how-to-flush-a-water-heater', 'how to flush a water heater' ); // phpcs:ignore ?>).</li>
	<li><strong>A thermostat set too low.</strong> If the water is never very hot, even at the start, check the setting first. The CPSC recommends <?php echo esc_html( (string) hwm_value( $hwm_f, 'safety.cpscSetpointF' ) ); ?> °F to prevent scalds (<?php echo $hwm_ext( $hwm_r( 'cpsc-scald' ), 'CPSC' ); // phpcs:ignore ?>).</li>
	<li><strong>A faulty mixing valve</strong> in a shower control. A. O. Smith says these can cut the hot water a shower delivers even though the tank is full of hot water.</li>
	<li><strong>A leak on the hot side.</strong> Even a small one can make it seem the heater produces little hot water, the manual says. A burner or element that runs almost all the time is the clue.</li>
</ul>

<h2>How fast should a water heater recover?</h2>
<p>A. O. Smith gives these typical numbers. If yours takes much longer than its type, something is wrong.</p>
<table>
	<caption>Typical recovery of tank water heaters</caption>
	<thead>
		<tr><th scope="col">Type</th><th scope="col">Recovery rate</th><th scope="col">Full reheat after the tank runs out</th></tr>
	</thead>
	<tbody>
		<tr><th scope="row">Gas tank</th><td><?php echo $hwm_t( 'runsOut.gasRecoveryGphLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'runsOut.gasRecoveryGphHigh' ); // phpcs:ignore ?> gallons per hour</td><td><?php echo $hwm_t( 'noHot.recoveryGasMinLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'noHot.recoveryGasMinHigh' ); // phpcs:ignore ?> minutes</td></tr>
		<tr><th scope="row">Electric tank</th><td><?php echo $hwm_t( 'runsOut.electricRecoveryGphLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'runsOut.electricRecoveryGphHigh' ); // phpcs:ignore ?> gallons per hour</td><td><?php echo $hwm_t( 'noHot.recoveryElectricMinLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'noHot.recoveryElectricMinHigh' ); // phpcs:ignore ?> minutes</td></tr>
		<tr><th scope="row">Tankless</th><td colspan="2">No reheat; limited by flow rate instead (gallons per minute it can heat at once)</td></tr>
	</tbody>
</table>
<p class="table-source">Source: <?php echo $hwm_ext( $hwm_r( 'aos-reheat' ), 'A. O. Smith, How long for a water heater to reheat?' ); // phpcs:ignore ?> (checked <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.aos-reheat.checked' ) ); ?>).</p>
<p>Because electric tanks recover more slowly, they are the ones that come up short in a busy house. If you are choosing a new heater, that is a reason to weigh a bigger electric tank or a gas one: see <?php echo hwm_link( 'gas-vs-electric-water-heater', 'gas vs electric water heater' ); // phpcs:ignore ?>.</p>

<h2>Tankless water heater: "running out" means too many taps</h2>
<p>A tankless unit cannot run out, but it can run short. When a second shower or the washer starts, the flow it must heat goes up, and the temperature drops if the unit is too small for that flow in your climate. A. O. Smith suggests spreading out demand or a model with a built-in buffer tank. Check yours with the <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless water heater size calculator' ); // phpcs:ignore ?>, which uses your state's winter inlet temperature.</p>

<h2>How to make hot water last longer</h2>
<p>Use less of it per shower, or make each gallon go further. These come from A. O. Smith's manual unless noted:</p>
<ul>
	<li><strong>A lower-flow showerhead.</strong> At the WaterSense limit of <?php echo $hwm_v( 'figures.lowFlowGpm' ); // phpcs:ignore ?> gallons per minute instead of <?php echo $hwm_v( 'figures.gpm' ); // phpcs:ignore ?>, a <?php echo $hwm_v( 'figures.minutes' ); // phpcs:ignore ?>-minute shower uses about <?php echo $hwm_v( 'figures.example.savedGal' ); // phpcs:ignore ?> fewer gallons of tank water (our math, at <?php echo $hwm_v( 'figures.example.inletF' ); // phpcs:ignore ?> °F inlet water).</li>
	<li><strong>Space out showers and laundry</strong> so the tank can recover between them.</li>
	<li><strong>Wash clothes in cold water</strong> and fix dripping hot taps.</li>
	<li><strong>Mixing valves plus a higher setting,</strong> for a tank that is a little small but otherwise healthy.</li>
	<li><strong>Flush the tank</strong> if it has sediment.</li>
</ul>

<h2>When this is a job for a licensed professional</h2>
<p>Checking the setting, testing other taps, flushing and changing a showerhead are owner tasks. Call a professional to test or replace heating elements or thermostats, to replace a dip tube or correct reversed connections, to install mixing valves, and to find a hidden leak. If the heater is old and too small, a replacement may be the better money: our <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?> prices one for your state.</p>

<h2>Limitations of this guide</h2>
<p>Causes and fixes restate A. O. Smith's manuals and reheat pages. The winter vs summer table is our arithmetic on estimated statewide inlet temperatures, a <?php echo $hwm_v( 'figures.minutes' ); // phpcs:ignore ?>-minute example shower and no pipe losses; your house and habits will differ. Read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'Why does my hot water run out after 5 minutes?',
		'a' => 'If it always has, the heater is likely too small for that hour. If it started recently on an electric tank, a failed lower element is the classic cause: A. O. Smith says you then get some hot water but not as much as before. Right after an install, suspect a melted dip tube or reversed connections.',
	),
	array(
		'q' => 'Why does my hot water run out faster in winter?',
		'a' => 'Colder inlet water. Each shower needs more tank water to reach the same temperature, and each gallon takes longer to reheat. Our state table shows a shower using ' . hwm_value( $hwm_c, 'figures.minExtraPct' ) . '% to ' . hwm_value( $hwm_c, 'figures.maxExtraPct' ) . '% more tank water in winter than in summer.',
	),
	array(
		'q' => 'How do I know if my dip tube is broken?',
		'a' => 'The water turns lukewarm quickly even though the heater is working, often soon after an installation or soldering near the heater. A. O. Smith says a melted dip tube is replaced by removing the cold inlet connection and fitting a new tube.',
	),
	array(
		'q' => 'How long should hot water last in a 50 gallon tank?',
		'a' => 'It depends on flow and inlet temperature more than on the tank. A ' . hwm_value( $hwm_c, 'figures.gpm' ) . ' GPM shower at ' . hwm_value( $hwm_c, 'figures.mixF' ) . ' °F draws about ' . hwm_value( $hwm_c, 'figures.example.fullGal' ) . ' gallons of tank water in ' . hwm_value( $hwm_c, 'figures.minutes' ) . ' minutes with ' . hwm_value( $hwm_c, 'figures.example.inletF' ) . ' °F inlet water. Compare your busiest hour with the first-hour rating on the label, not the tank size.',
	),
	array(
		'q' => 'Will turning up the water heater make hot water last longer?',
		'a' => 'Yes, a hotter tank is mixed with more cold water, so it stretches further. A. O. Smith suggests it only with thermostatic mixing valves at each tap, because hotter water scalds faster. The CPSC recommends setting water heaters to ' . hwm_value( $hwm_f, 'safety.cpscSetpointF' ) . ' °F.',
	),
	array(
		'q' => 'Does a tankless water heater run out of hot water?',
		'a' => 'Not in the way a tank does, but it can run short of flow. If more taps run than it can heat for your climate, the water cools. A. O. Smith suggests spreading out use or a model with a buffer tank; check the size with our tankless calculator.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Electronic Thermostat Electric Water Heater Use & Care Guide (2000598248): not enough hot water', 'url' => $hwm_r( 'aos-electric-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): troubleshooting', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long for a water heater to reheat?', 'url' => $hwm_r( 'aos-reheat' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long does it take a water heater to heat up?', 'url' => $hwm_r( 'aos-heat-up' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Stiebel Eltron', 'title' => 'Tankless sizing sheet: fixture flow rates and shower temperature', 'url' => $hwm_r( 'stiebel-sizing' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. EPA WaterSense', 'title' => 'Showerheads', 'url' => (string) hwm_raw( 'fixture-flow-rates', 'fixtures.shower.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
);
