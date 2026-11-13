<?php
/**
 * /water-heater-element-replacement/ (STRATEGY §2 item 26, Phase 6d, CLAUDE.md §8b): electric heating elements.
 * Symptoms, pass range and replacement steps restate A. O. Smith's electric Use & Care Guide (data/safety.json); the
 * steps are the manual's own homeowner procedure, summarized and attributed, with its warnings, and nothing beyond it.
 * The resistance table is data/computed/water-heater-element-replacement.json (formulas.elementResistanceOhms).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f = 'safety';
$hwm_c = 'computed/water-heater-element-replacement';
$hwm_s = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_v = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_r = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_pct = (array) hwm_raw( 'computed/repair-or-replace-water-heater', 'rulesPct' );

$related = array( 'repair-or-replace-water-heater', 'water-heater-reset-button', 'hot-water-runs-out-fast', 'no-hot-water', 'gas-vs-electric-water-heater' );
hwm_part( 'safety-notice', array( 'gas' => false, 'power' => true, 'scald' => true ) );
?>
<aside class="notice" aria-label="Electrical safety">
	<p><strong>Electrical work.</strong> A. O. Smith's manual warns that working on an energized circuit "can result in severe injury or death from electrical shock." Its own steps start with the breaker off and a non-contact tester on the wires, and it says that if you are not comfortable replacing an element yourself, a qualified person should do it. This page explains the job; it is not a reason to open a live panel.</p>
</aside>

<h2>How do you know a water heater element is bad?</h2>
<p>By what the hot water does. A. O. Smith's electric manual says a failed lower element leaves you with some hot water "but not as much as before", and that the lower element usually wears out first because it does most of the work. A burned-out upper element is on its list of causes of no hot water at all. A shorted element can also trip the reset button or make the relief valve release very hot water.</p>
<?php echo hwm_table( $hwm_f, 'elementSymptoms' ); // phpcs:ignore -- escaped in the part. ?>
<p>Before blaming an element, A. O. Smith's no-hot-water checks come first: is the breaker on, and has the red reset button tripped? Our <?php echo hwm_link( 'no-hot-water', 'no hot water guide' ); // phpcs:ignore ?> and <?php echo hwm_link( 'water-heater-reset-button', 'reset button guide' ); // phpcs:ignore ?> walk through those.</p>
<dl>
	<dt>Heating element</dt>
	<dd>A metal rod that screws through the side of an electric tank into the water and heats it by electrical resistance, like the coil in a toaster. Standard home tanks have two, upper and lower, each with its own thermostat. A. O. Smith's controls run only one at a time.</dd>
</dl>

<h2>What should a water heater element read in ohms?</h2>
<p>About <?php echo $hwm_v( 'ohms4500' ); // phpcs:ignore ?> ohms for the common 4,500-watt, 240-volt element, measured cold with the power off and the wires removed. That figure is Ohm's law: resistance = volts² ÷ watts. A. O. Smith's manual gives a wider pass range of <?php echo $hwm_s( 'element.ohmsLow' ); // phpcs:ignore ?> to <?php echo $hwm_s( 'element.ohmsHigh' ); // phpcs:ignore ?> ohms for its element circuits. A reading near zero means a short; no reading at all means the element is open, or burned out.</p>
<?php echo hwm_table( $hwm_c, 'ohms' ); // phpcs:ignore -- escaped in the part. ?>
<p>Some guides say a good element reads zero ohms. That is wrong: zero ohms across the terminals is a dead short, which is what trips breakers and reset buttons. A good element reads close to the number in the table for its rating.</p>
<p>A. O. Smith's troubleshooting adds two rules worth knowing. A grounded element, where current leaks to the tank, is something it tells you to have a qualified person test for. And if both elements test good, the next suspect is a thermostat. The meter test itself means working inside the access panels; this page gives the values, not a walkthrough, so the person doing it can check their reading.</p>

<h2>Which replacement element do you need?</h2>
<p>The one that matches your heater's data plate. A. O. Smith's manual says to check the data plate "for the correct wattage and voltage" before you buy. The full-size electric tanks in our sizing data run on 240 volts, most with 4,500-watt elements; retailers also sell 120-volt elements for small heaters. The current column above shows how much the rating changes what the circuit carries, which is why the data plate, not the store shelf, decides.</p>
<p>Listings with the same rating still differ in sheath metal (copper, stainless steel, titanium), shape (straight or fold-back) and watt density. If you are unsure, take the old element or the heater's model number with you.</p>

<h2>What does a water heater element replacement cost?</h2>
<p>The part is cheap. On <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.hd-elements.checked' ) ); ?>, 4,500-watt, 240-volt elements at The Home Depot listed for $<?php echo $hwm_s( 'element.priceLow' ); // phpcs:ignore ?> to $<?php echo $hwm_s( 'element.priceHigh' ); // phpcs:ignore ?>, from basic copper to stainless and titanium. A tune-up kit with elements and thermostats listed for $<?php echo $hwm_s( 'element.kitPrice' ); // phpcs:ignore ?>. You also need an element wrench, which A. O. Smith recommends over a regular socket because sockets are often beveled and slip.</p>
<p>A professional job adds the service visit and labor, which vary a lot by area. We found no source for a labor price that we would stand behind, so we do not publish one. What we can do is tell you whether a quote is worth paying: Rheem's rule, used in our calculator, is to replace the heater if the repair costs more than <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% of a new one, or more than <?php echo esc_html( (string) ( $hwm_pct['old'] ?? '' ) ); ?>% once it is past <?php echo esc_html( (string) ( $hwm_pct['age'] ?? '' ) ); ?>% of its expected life.</p>
<?php
ob_start();
hwm_part( 'repair-inputs', array( 'checked' => array( 'noHot' ) ) );
hwm_part( 'calculator', array( 'tool' => 'repair-or-replace-water-heater', 'inputs' => (string) ob_get_clean(), 'label' => 'Repair or replace calculator' ) );
?>
<p>Not sure how old the heater is? The <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> reads it from the serial number. Elements are designed to be replaced, so on a heater in good shape a new element is usually the sensible fix.</p>

<h2>How the manufacturer says to replace a heating element</h2>
<p>A. O. Smith's electric Use &amp; Care Guide includes a homeowner procedure. In summary, and in its order (read your own heater's manual first; models differ):</p>
<ol>
	<li>Turn the power off at the circuit breaker, then open the junction box on top and check the power wires with a non-contact circuit tester to make sure the power is off.</li>
	<li>Open a hot water faucet and let it run until the water is cool, to reduce the risk of scalding.</li>
	<li>Turn off the cold water supply, connect a garden hose to the drain valve, and drain the tank.</li>
	<li>Remove the access panel, fold back the insulation and remove the plastic cover over the element.</li>
	<li>With the tank drained and the power off, remove the wires from the element and unscrew it with an element wrench.</li>
	<li>Check that the new element's voltage and wattage match the data plate, clean the threads in the tank opening, and fit the new element with its rubber gasket.</li>
	<li>Refill the tank with a hot faucet open, and let the hot water run full for at least <?php echo $hwm_s( 'element.fillMinutes' ); // phpcs:ignore ?> minutes so all the air is out.</li>
	<li>Check the new element for leaks, then reconnect the wires, put back the cover, insulation and panel, and close the junction box.</li>
	<li>Restore power. A. O. Smith says it may take <?php echo $hwm_s( 'element.heatUpHours' ); // phpcs:ignore ?> hours for the tank to heat up.</li>
</ol>
<p>If the new element leaks, A. O. Smith says to tighten it until the leak stops; if it will not stop, drain the tank again, remove the element, and check or replace the gasket.</p>

<h2>Why the tank must be full before the power goes on</h2>
<p>Because an element that is not under water burns out in moments. A. O. Smith's manual says that connecting power before the tank is completely full, with water running full stream from a hot tap for a full <?php echo $hwm_s( 'element.fillMinutes' ); // phpcs:ignore ?> minutes, "may cause the upper heating element to burn out." This is called a dry fire, and A. O. Smith's electronic models have a dry-fire diagnostic code for it. The manual's notice is plain: "Do not turn power back on until the tank is completely full of water." That includes turning the breaker on "just to check" while the tank is still empty.</p>
<div class="table-wrap" role="region" aria-label="Upper vs lower element" tabindex="0">
<table>
	<caption>Upper vs lower element on a two-element tank</caption>
	<thead>
		<tr><th scope="col"></th><th scope="col">Upper element</th><th scope="col">Lower element</th></tr>
	</thead>
	<tbody>
		<tr><th scope="row">What it heats</th><td>The top of the tank, where hot water leaves</td><td>The rest of the tank, and most of the daily load</td></tr>
		<tr><th scope="row">If it fails</th><td>Little or no hot water (A. O. Smith)</td><td>Some hot water, but less than before (A. O. Smith)</td></tr>
		<tr><th scope="row">Which wears out first</th><td>Less often</td><td>Usually this one (A. O. Smith)</td></tr>
		<tr><th scope="row">Reset button</th><td>Its thermostat carries the red reset button</td><td>No reset button</td></tr>
		<tr><th scope="row">Named in the dry-fire warning</th><td>Yes (A. O. Smith)</td><td>No</td></tr>
	</tbody>
</table>
</div>
<p class="table-source">Source: A. O. Smith electric Use &amp; Care Guide 2000598248 and A. O. Smith troubleshooting guidance, checked <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.aos-electric-manual.checked' ) ); ?>.</p>

<h2>Common mistakes when replacing an element</h2>
<ul>
	<li><strong>Trusting the breaker label.</strong> A. O. Smith says to check the wires with a non-contact tester every time.</li>
	<li><strong>Restoring power before the tank is full.</strong> The dry-fire rule above.</li>
	<li><strong>Buying by wattage alone.</strong> Voltage matters too; match the data plate.</li>
	<li><strong>Reusing the old gasket.</strong> The new element comes with one for a reason.</li>
	<li><strong>Replacing an element when the fault is a thermostat.</strong> A. O. Smith: if both elements test good, replace the thermostat.</li>
	<li><strong>Ignoring a breaker that trips after the repair.</strong> A. O. Smith's troubleshooting guidance is to leave it off and have the wiring, breaker or element checked.</li>
</ul>

<h2>When element replacement is a job for a licensed professional</h2>
<p>Call a licensed plumber or electrician if you are not comfortable working inside the panels, which is A. O. Smith's own advice; if the breaker trips again after the new element is in; if the element will not come out or the threads are damaged; if the wiring is scorched or melted; if a leak at the new element will not stop; or if the heater is old enough that replacing it may be the better buy. A heater that has been flooded is a replacement, not a repair: A. O. Smith says a water heater whose controls have been under water must be replaced.</p>

<h2>Limitations of this guide</h2>
<p>Steps, symptoms and the <?php echo $hwm_s( 'element.ohmsLow' ); // phpcs:ignore ?>–<?php echo $hwm_s( 'element.ohmsHigh' ); // phpcs:ignore ?> ohm range come from A. O. Smith's electric Use &amp; Care Guide; other brands' manuals can differ, and yours wins. The resistance table is arithmetic from the element's rating, so real readings vary a little. Prices are listing prices on one day. Heat pump water heaters also have backup elements, but their controls and access are different: follow that manual. We are an independent calculator site, not electricians: read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I know if my water heater element is bad?',
		'a' => 'Watch the hot water. A. O. Smith says a failed lower element leaves some hot water but less than before, and a burned-out upper element is a cause of no hot water. A reset button that keeps tripping can mean a shorted element. A meter reading, taken with the power off, confirms it.',
	),
	array(
		'q' => 'What should a 4500 watt water heater element read?',
		'a' => 'About ' . hwm_value( 'computed/water-heater-element-replacement', 'figures.ohms4500' ) . ' ohms at 240 volts, from Ohm\'s law (240 × 240 ÷ 4,500). A. O. Smith\'s manual accepts ' . hwm_value( 'safety', 'element.ohmsLow' ) . ' to ' . hwm_value( 'safety', 'element.ohmsHigh' ) . ' ohms for its element circuits. Near zero means a short; no reading means the element is burned out.',
	),
	array(
		'q' => 'Which element goes bad first, upper or lower?',
		'a' => 'Usually the lower one. A. O. Smith says the lower element does most of the work and so usually wears out before the upper element. The upper element is the one most at risk from a dry fire, when power is turned on before the tank is full.',
	),
	array(
		'q' => 'How much does a water heater element cost?',
		'a' => 'A 4,500-watt, 240-volt element listed for $' . hwm_value( 'safety', 'element.priceLow' ) . ' to $' . hwm_value( 'safety', 'element.priceHigh' ) . ' at The Home Depot when we checked, depending on the metal. Labor for a professional job varies by area. Enter a quote in the repair or replace calculator to see whether it is worth paying on an older heater.',
	),
	array(
		'q' => 'Do I have to drain the tank to replace an element?',
		'a' => 'A. O. Smith\'s procedure drains the tank before the element comes out, after the power is off and the water has cooled. Its thermostat procedure, by contrast, needs no draining. Refill completely and run a hot tap for ' . hwm_value( 'safety', 'element.fillMinutes' ) . ' minutes before you restore power.',
	),
	array(
		'q' => 'Can I replace a water heater element myself?',
		'a' => 'A. O. Smith includes homeowner steps in its manual, but warns that working on an energized circuit can kill, and says to have a qualified person do it if you are not comfortable. Never skip the breaker and the non-contact tester, and never restore power until the tank is full.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Electronic Thermostat Electric Water Heater Use & Care Guide (2000598248): replacing the heating element, diagnostic codes, troubleshooting', 'url' => $hwm_r( 'aos-electric-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): flood rule, relief valve', 'url' => $hwm_r( 'aos-gas-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Avoiding Tap Water Scalds (Publication 5098)', 'url' => $hwm_r( 'cpsc-scald' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => '4500-watt water heater heating elements (listing prices)', 'url' => $hwm_r( 'hd-elements' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace', 'url' => (string) hwm_raw( 'troubleshooting', 'references.rheem-lifespan.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
