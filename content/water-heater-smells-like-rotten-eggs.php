<?php
/**
 * /water-heater-smells-like-rotten-eggs/ (STRATEGY §2 item 28, Phase 6b batch 2): symptom article with the age
 * calculator embedded. Gas-leak check first (Kansas Corporation Commission, A. O. Smith gas manual); heater causes and
 * fixes restate A. O. Smith, GE Appliances and Penn State Extension, including where they disagree on the anode metal.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_age = 'computed/water-heater-age-calculator';

$related = array( 'water-heater-anode-rod', 'how-to-flush-a-water-heater', 'water-heater-age-calculator', 'water-heater-leaking', 'no-hot-water' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>Is it a gas leak or the water?</h2>
<p>Rule out gas first, because the two smell alike. Natural gas has no smell of its own; gas companies add a sulfur odorant called mercaptan so leaks can be noticed, and it is what makes gas smell like rotten eggs, the Kansas Corporation Commission explains. If you smell it in the air near the water heater, the stove or a gas line, leave now and call the gas utility or 911 from outside.</p>
<p>Do not go back in until the gas company says it is safe. A. O. Smith adds a warning worth knowing: the odorant can fade over time, so do not depend on smell alone, and it recommends a fuel gas and carbon monoxide detector.</p>
<p>If the smell only appears when you run a tap, and it is in the water itself, read on.</p>
<figure class="figure">
	<svg viewBox="0 0 640 340" width="640" height="340" role="img" aria-labelledby="egg-fig-t egg-fig-d">
		<title id="egg-fig-t">Rotten egg smell: what it means</title>
		<desc id="egg-fig-d">Flowchart. Do you smell it in the air near a gas appliance? Yes: possible gas leak, leave and call the gas utility from outside. No, it is in the water: does only the hot water smell? Yes: the water heater, bacteria reacting with the anode rod. No, hot and cold both smell: on a well, hydrogen sulfide in the groundwater, test the water; on city water, tell your water supplier.</desc>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e" text-anchor="middle">
			<rect x="170" y="10" width="300" height="44" rx="8" fill="#e6f0f8" stroke="#0b4a7d"/>
			<text x="320" y="29">Do you smell it in the air</text>
			<text x="320" y="46">near a gas appliance or gas line?</text>
			<path d="M470 32 H530 V70" fill="none" stroke="#17212e"/>
			<text x="500" y="26">Yes</text>
			<rect x="430" y="70" width="200" height="56" rx="8" fill="#fdeceb" stroke="#a8231a"/>
			<text x="530" y="92">Possible gas leak: leave,</text>
			<text x="530" y="110">call the utility from outside</text>
			<path d="M320 54 V90" fill="none" stroke="#17212e"/>
			<text x="370" y="78">No, it is in the water</text>
			<rect x="170" y="90" width="230" height="44" rx="8" fill="#e6f0f8" stroke="#0b4a7d"/>
			<text x="285" y="117">Does only the hot water smell?</text>
			<path d="M200 134 V170 H110 V190" fill="none" stroke="#17212e"/>
			<text x="150" y="162">Yes</text>
			<rect x="10" y="190" width="220" height="70" rx="8" fill="#fdf4dc" stroke="#7a4f00"/>
			<text x="120" y="212">The water heater:</text>
			<text x="120" y="230">bacteria react with the</text>
			<text x="120" y="248">anode rod (fix below)</text>
			<path d="M370 134 V170 H460 V190" fill="none" stroke="#17212e"/>
			<text x="440" y="162">No, hot and cold</text>
			<rect x="330" y="190" width="140" height="70" rx="8" fill="#ffffff" stroke="#4a5562"/>
			<text x="400" y="212">Private well:</text>
			<text x="400" y="230">hydrogen sulfide</text>
			<text x="400" y="248">in the water; test it</text>
			<rect x="490" y="190" width="140" height="70" rx="8" fill="#ffffff" stroke="#4a5562"/>
			<text x="560" y="212">City water:</text>
			<text x="560" y="230">tell your</text>
			<text x="560" y="248">water supplier</text>
			<path d="M470 225 H490" fill="none" stroke="#17212e"/>
			<text x="320" y="300" fill="#4a5562">Run a cold tap and a hot tap side by side to tell which one smells.</text>
		</g>
	</svg>
	<figcaption>Drawn by Hot Water Math from Kansas Corporation Commission, A. O. Smith and Penn State Extension guidance.</figcaption>
</figure>
<?php echo hwm_table( $hwm_f, 'smellSources' ); // phpcs:ignore -- escaped in the part. ?>

<h2>Why does only my hot water smell like rotten eggs?</h2>
<p>Something in the water heater is making hydrogen sulfide gas. A. O. Smith explains that harmless bacteria normally present in tap water can multiply in a water heater and give off the rotten egg smell. Penn State Extension adds the chemistry: the magnesium anode rod in the tank can reduce sulfates in the water to hydrogen sulfide. The warm, still water in a tank suits both.</p>
<dl>
	<dt>Hydrogen sulfide</dt>
	<dd>A gas that gives water a rotten egg or sulfur odor. Penn State Extension says most people can smell it well below <?php echo $hwm_t( 'smell.detectBelowMgL' ); // phpcs:ignore ?> mg/L (milligrams per liter), and that the sulfur-reducing bacteria that make it in water do not cause human health concerns.</dd>
	<dt>Anode rod</dt>
	<dd>A metal rod inside a tank water heater that corrodes so the steel tank does not. Its metal matters here: magnesium reacts most with sulfates. See our <?php echo hwm_link( 'water-heater-anode-rod', 'water heater anode rod guide' ); // phpcs:ignore ?>.</dd>
</dl>
<p>A water softener can play a part. A. O. Smith notes that a softener makes an anode rod wear out faster, and Penn State says a softener can, in rare cases, give the bacteria a place to grow.</p>

<h2>How to get rid of the rotten egg smell in a water heater</h2>
<p>There are four fixes, and they do not last equally. Manufacturers and Penn State agree on what works; they disagree on which anode metal to use.</p>
<div class="table-wrap" role="region" aria-label="Fixes for a rotten egg smell from the water heater" tabindex="0">
<table>
	<caption>Fixes for a rotten egg smell from the water heater</caption>
	<thead>
		<tr><th scope="col">Fix</th><th scope="col">What the sources say</th><th scope="col">How long it lasts</th></tr>
	</thead>
	<tbody>
		<tr><th scope="row">Change the anode rod metal</th><td>A. O. Smith: a special zinc rod "may help reduce or eliminate the odor"; its troubleshooting page names an aluminum/zinc rod. GE: special alloy rods are available. Penn State: an aluminum rod "should eliminate" the odor and keep the tank protected.</td><td>Usually long term, if it works for your water</td></tr>
		<tr><th scope="row">Heat the tank hotter for a while</th><td>A. O. Smith: raise the tank to <?php echo $hwm_t( 'smell.boostF' ); // phpcs:ignore ?> °F to reduce bacteria growth, then return to a safer setting, with thermostatic mixing valves because of the scald risk.</td><td>Temporary; the smell can come back</td></tr>
		<tr><th scope="row">Flush with hydrogen peroxide</th><td>A. O. Smith's troubleshooting page: flush the heater with a water and hydrogen peroxide solution. Follow your manual for how.</td><td>Temporary</td></tr>
		<tr><th scope="row">Chlorination</th><td>A. O. Smith's manual calls a chlorination system "the only sure treatment" for the bacteria. Penn State notes that one-off shock chlorination of a well often lets the smell return quickly.</td><td>Lasting only as continuous treatment</td></tr>
	</tbody>
</table>
</div>
<p class="table-source">Sources: A. O. Smith Use &amp; Care Guides and troubleshooting page, GE Appliances, Penn State Extension (checked <?php echo esc_html( (string) hwm_raw( $hwm_f, 'references.psu-h2s.checked' ) ); ?>).</p>
<p>Our reading: start with a flush, then change the rod to the metal your heater's maker sells for odor. Why the maker? Because removing the rod, or fitting one it does not approve, can void the warranty. A. O. Smith says a rod must be installed at all times or the warranty is void, and Penn State warns that simply removing the magnesium rod shortens the heater's life and will likely void it.</p>
<p>Replacing the rod means shutting off the heater, draining some water and loosening a tight fitting; our <?php echo hwm_link( 'water-heater-anode-rod', 'anode rod guide' ); // phpcs:ignore ?> covers what is involved, and <?php echo hwm_link( 'how-to-flush-a-water-heater', 'how to flush a water heater' ); // phpcs:ignore ?> covers the flush.</p>

<h2>Rotten egg smell in hot and cold water (well water)</h2>
<p>Then it is not the water heater. Penn State Extension says hydrogen sulfide occurs naturally in groundwater from sulfur-reducing bacteria, and is most common in wells drilled into shale and sandstone. Changing the anode rod will not help; treating all the water entering the house will.</p>
<p>Test first, because the right treatment depends on the level. Penn State notes the test has to be done at home, or the sample chemically stabilized before it goes to a lab. Its guide to treatments by level:</p>
<?php echo hwm_table( $hwm_f, 'h2sTreatment' ); // phpcs:ignore -- escaped in the part. ?>
<p>On city water, a smell in both hot and cold water is a question for your water supplier.</p>

<h2>Is the rotten egg smell in water dangerous?</h2>
<p>At the levels you can smell in a home's water, it is mainly unpleasant. Penn State Extension says hydrogen sulfide in water is an aesthetic concern: the nose detects it well before it causes health concerns, and it makes water undrinkable long before it reaches harmful concentrations, which is why it has no drinking water standard. It can corrode plumbing and leave black or yellow stains.</p>
<p>The smell in the air near a gas appliance is the dangerous one. That is the first check on this page for a reason.</p>

<h2>Is a smelly water heater a reason to replace it?</h2>
<p>Rarely on its own: the smell is a water chemistry problem that a rod change or treatment fixes. It can be a reason when the heater is old and the anode is spent, because a tank without anode protection corrodes. Rusty water or water at the base of the tank settles it: see <?php echo hwm_link( 'water-heater-leaking', 'water heater leaking' ); // phpcs:ignore ?>. Check the heater's age from its serial number before you spend on a new rod: a tank past <?php echo esc_html( (string) hwm_value( $hwm_age, 'bandsPct.redFrom' ) ); ?>% of its typical life can fail at any time.</p>
<?php
ob_start();
hwm_part( 'age-inputs', array( 'file' => 'water-heater-age-calculator' ) );
hwm_part( 'calculator', array( 'tool' => 'water-heater-age-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater age calculator' ) );
?>
<p>If the result says it is near the end, the <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace calculator' ); // phpcs:ignore ?> weighs a repair against a new unit.</p>

<h2>When this is a job for a licensed professional</h2>
<p>A suspected gas leak is a job for the gas utility, right away. For a smelly water heater, A. O. Smith's manual says to contact a qualified person to change the anode rod, and a chlorination system or whole-house water treatment is best sized and installed by a water treatment professional after a test. Get help, too, if the anode fitting will not turn or the tank is old and corroded around it.</p>

<h2>Limitations of this guide</h2>
<p>Causes and fixes restate A. O. Smith, GE Appliances, Penn State Extension and the Kansas Corporation Commission. Water chemistry varies, and a fix that works in one home may not in another. We give no chemical dosages on purpose: follow your heater's manual. Read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>: an independent calculator site, not plumbers or water treatment specialists.</p>
<?php
$faq = array(
	array(
		'q' => 'Why does my hot water smell like rotten eggs?',
		'a' => 'Bacteria in the tank make hydrogen sulfide, and the magnesium anode rod helps by reducing sulfates in the water, A. O. Smith and Penn State Extension explain. Only the hot water smells because it happens inside the heater. A different anode metal, a temporary higher setting, or chlorination are the fixes.',
	),
	array(
		'q' => 'Can a rotten egg smell from a water heater be a gas leak?',
		'a' => 'Yes. Gas companies add a sulfur odorant that smells like rotten eggs. If you smell it in the air near the heater or another gas appliance, rather than in the water from a tap, leave now and call the gas utility or 911 from outside.',
	),
	array(
		'q' => 'What anode rod gets rid of the rotten egg smell?',
		'a' => 'Sources differ. A. O. Smith names a special zinc or aluminum/zinc rod, GE offers special alloy rods, and Penn State Extension says an aluminum rod should eliminate it. Use the odor rod your heater\'s maker sells, because an unapproved rod or no rod can void the warranty.',
	),
	array(
		'q' => 'Will turning up the water heater kill the smell?',
		'a' => 'It can reduce it for a while. A. O. Smith says raising the tank to ' . hwm_value( $hwm_f, 'smell.boostF' ) . ' °F reduces bacteria growth, then you return to a safer setting. Hotter water scalds faster, so it recommends thermostatic mixing valves at each tap.',
	),
	array(
		'q' => 'Is it safe to shower in water that smells like rotten eggs?',
		'a' => 'At household levels it is mainly unpleasant. Penn State Extension says hydrogen sulfide in water is an aesthetic concern that people can smell well before it causes health concerns. A smell in the air near gas appliances is different: treat that as a possible gas leak.',
	),
	array(
		'q' => 'Why do both my hot and cold water smell like sulfur?',
		'a' => 'Then the source is the water supply, not the heater. On a private well, Penn State Extension says hydrogen sulfide comes from sulfur-reducing bacteria in the groundwater; test the water and treat all of it. On city water, contact your supplier.',
	),
);
$sources = array(
	array( 'publisher' => 'Kansas Corporation Commission', 'title' => 'Natural gas safety: what does gas smell like?', 'url' => $hwm_r( 'kcc-gas' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): water odor, gas odorant, anode rod', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Electronic Thermostat Electric Water Heater Use & Care Guide (2000598248): water odor', 'url' => $hwm_r( 'aos-electric-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater not working? Discolored or smelly water', 'url' => $hwm_r( 'aos-not-working' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'GE Appliances', 'title' => 'Water heater anode rod', 'url' => $hwm_r( 'ge-anode' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Penn State Extension', 'title' => 'Hydrogen sulfide (rotten egg odor) in water wells', 'url' => $hwm_r( 'psu-h2s' ), 'accessed' => '2026-11-13' ),
);
