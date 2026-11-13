<?php
/**
 * /water-heater-anode-rod/ (STRATEGY §2 item 23, Phase 6b batch 1): care article with the age calculator embedded.
 * Rules come from the A. O. Smith manual and maintenance pages and GE Appliances; prices are Home Depot listing
 * prices (data/troubleshooting.json → references.home-depot-anodes); the replacement price comes from
 * data/computed/repair-or-replace-water-heater.json (shared cost model).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_new = esc_html( hwm_value( 'computed/repair-or-replace-water-heater', 'defaultInputs.replace', 0 ) );
$hwm_age = 'computed/water-heater-age-calculator';

$related = array( 'water-heater-age-calculator', 'how-to-flush-a-water-heater', 'water-heater-leaking', 'repair-or-replace-water-heater', 'how-long-does-a-water-heater-last' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>What does a water heater anode rod do?</h2>
<p>It corrodes so your tank does not. A steel tank in contact with water slowly rusts; the anode rod is a more reactive metal that gives itself up first, so the water attacks the rod instead of the steel. A. O. Smith calls it the water heater's "first line of defense" against corrosion and premature leaks.</p>
<dl>
	<dt>Sacrificial anode rod</dt>
	<dd>A rod of magnesium, aluminum or zinc around a steel core wire, screwed into the top of a steel tank. It is "sacrificed": it wears away over the years by galvanic corrosion, protecting the tank wall. When the metal is gone and only the core wire is left, the protection is gone too.</dd>
	<dt>Powered anode</dt>
	<dd>A replacement rod with its own power supply that, in its maker's words, uses "a small electrical current to prevent corrosion" instead of wearing away (<?php echo $hwm_ext( $hwm_r( 'corro-protec' ), 'Corro-Protec' ); // phpcs:ignore ?>). How long it lasts is the seller's claim; we have not tested one.</dd>
</dl>
<p>Tankless water heaters have no storage tank and no anode rod. Everything on this page is about tank water heaters, gas, electric and heat pump.</p>

<h2>How long does an anode rod last?</h2>
<p>Usually <?php echo $hwm_t( 'anode.lifeYearsLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'anode.lifeYearsHigh' ); // phpcs:ignore ?> years, according to A. O. Smith, and Rheem advises replacing it every <?php echo $hwm_t( 'anode.rheemReplaceYearsLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'anode.rheemReplaceYearsHigh' ); // phpcs:ignore ?> years. Your water decides where you fall: A. O. Smith says the life depends on what chemicals are in the water, how much hot water you use and whether a water softener is installed.</p>
<p>A water softener shortens it the most. Both the A. O. Smith manual and GE say softened water makes the rod deplete faster, so check it more often if you have one. That is also why a fixed replacement interval is only a rough guide: the only way to know is to look at the rod. A healthy rod is one reason a tank reaches the long end of <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?>.</p>

<h2>When to replace the anode rod</h2>
<p>Replace it when more than <?php echo $hwm_t( 'anode.coreWireInches' ); // phpcs:ignore ?> inches (<?php echo $hwm_t( 'anode.coreWireCm' ); // phpcs:ignore ?> cm) of the core wire is exposed at either end. That is GE's rule, and A. O. Smith's checklist uses the same <?php echo $hwm_t( 'anode.coreWireInches' ); // phpcs:ignore ?> inches. A. O. Smith adds that a rod in good condition is smooth and around <?php echo $hwm_t( 'anode.healthyDiameterIn' ); // phpcs:ignore ?> in diameter.</p>
<figure class="figure">
	<svg viewBox="0 0 640 230" width="640" height="230" role="img" aria-labelledby="anode-t anode-d">
		<title id="anode-t">Anode rod from new to fully depleted</title>
		<desc id="anode-d">Three rods. A new rod is smooth and even. A partly used rod is thinner and pitted but still covers the core wire. A depleted rod has bare core wire showing over more than <?php echo esc_attr( hwm_value( $hwm_f, 'anode.coreWireInches' ) ); ?> inches, which means it should be replaced.</desc>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<text x="20" y="45">New</text>
			<text x="20" y="115">Partly used</text>
			<text x="20" y="185">Replace now</text>
		</g>
		<rect x="140" y="32" width="18" height="18" fill="#4a5562"/>
		<rect x="158" y="34" width="440" height="14" rx="7" fill="#9aa7b4"/>
		<rect x="140" y="102" width="18" height="18" fill="#4a5562"/>
		<line x1="158" y1="111" x2="598" y2="111" stroke="#4a5562" stroke-width="2"/>
		<path d="M158 106 h80 l10 2 h60 l8 -2 h90 l12 3 h70 l10 -2 h60 v10 h-60 l-10 -2 h-70 l-12 3 h-90 l-8 -2 h-60 l-10 2 h-80 z" fill="#9aa7b4"/>
		<rect x="140" y="172" width="18" height="18" fill="#4a5562"/>
		<line x1="158" y1="181" x2="598" y2="181" stroke="#4a5562" stroke-width="2"/>
		<path d="M158 177 h60 l12 2 h50 l10 -1 h40 v6 h-40 l-10 -1 h-50 l-12 2 h-60 z" fill="#9aa7b4"/>
		<path d="M340 196 v10 h258 v-10" fill="none" stroke="#a8231a" stroke-width="2"/>
		<text x="469" y="222" text-anchor="middle" font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#a8231a">bare core wire: more than <?php echo esc_html( hwm_value( $hwm_f, 'anode.coreWireInches' ) ); ?> in.</text>
		<text x="140" y="24" font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="12" fill="#4a5562">hex head (top of tank)</text>
	</svg>
	<figcaption>Drawn by Hot Water Math after the A. O. Smith manual's photo of rods from new to fully depleted. The replace-at rule is GE's.</figcaption>
</figure>
<p>How often to look: the A. O. Smith manual says to inspect the rod after the first <?php echo $hwm_t( 'flush.firstFlushMonths' ); // phpcs:ignore ?> months, when you first flush the tank, then annually or more often if needed. GE says to remove and inspect it annually. Pairing the check with your yearly <?php echo hwm_link( 'how-to-flush-a-water-heater', 'water heater flush' ); // phpcs:ignore ?> saves a second drain.</p>

<h2>Signs the anode rod is used up</h2>
<p>You often cannot tell from outside, which is why the manuals say to look. Three signs point to the rod.</p>
<ul>
	<li><strong>Rusty hot water.</strong> A. O. Smith says rust-colored water from the hot taps suggests the rod is completely used up and the tank may be failing. If the cold water is rusty too, suspect the pipes instead.</li>
	<li><strong>A rotten-egg smell from the hot water only.</strong> A. O. Smith explains it as harmless bacteria in tap water multiplying in the heater and reacting with the rod. A different rod metal can help (below).</li>
	<li><strong>A tank that has never had the rod checked</strong> and is past <?php echo $hwm_t( 'anode.lifeYearsHigh' ); // phpcs:ignore ?> years old. Odds are the rod is gone.</li>
</ul>

<h2>Anode rod types and what they cost</h2>
<p>Replacement rods cost about <?php echo $hwm_t( 'anode.priceLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'anode.priceHigh' ); // phpcs:ignore ?> for magnesium, aluminum or zinc alloy, and about <?php echo $hwm_t( 'anode.poweredPrice' ); // phpcs:ignore ?> for a powered rod, going by Home Depot listing prices on <?php echo $hwm_t( 'anode.pricesChecked' ); // phpcs:ignore ?>. Prices change often; the table names the exact products so you can check.</p>
<?php echo hwm_table( $hwm_f, 'anodeTypes' ); // phpcs:ignore -- escaped in the part. ?>
<p>The listings are examples, not recommendations, and none is an affiliate link. A rod must fit your tank: thread size, length and whether it screws into its own port or comes combined with the hot water outlet nipple. Your manual or the maker's parts list gives the right one.</p>

<h2>Magnesium, aluminum or zinc: which should you choose?</h2>
<p>Start with what your manual specifies for your model. A. O. Smith names magnesium, aluminum and zinc as the rod metals, and says to determine which type your water heater needs before you buy.</p>
<ul>
	<li><strong>For a rotten-egg smell:</strong> the A. O. Smith manual says the standard rod can be replaced with a special zinc rod, which "may help reduce or eliminate the odor", and its troubleshooting page names an aluminum/zinc rod. GE also points to "special alloy" rods for odor. A. O. Smith is honest that killing the bacteria is the only sure fix; its page describes a hydrogen peroxide flush, or briefly raising the tank to <?php echo $hwm_t( 'anode.odorBoostF' ); // phpcs:ignore ?> °F with mixing valves to prevent scalding, for persistent cases.</li>
	<li><strong>With a water softener:</strong> any sacrificial rod wears faster. Plan to inspect it more often rather than to pick a "longer" metal.</li>
	<li><strong>Little room above the tank:</strong> a linked (flexible) rod is made in sections. For removal in tight spaces, the A. O. Smith maintenance page describes pulling the old rod up part way, cutting it off, and repeating.</li>
</ul>

<h2>Where is the anode rod on a water heater?</h2>
<p>On top of the tank. On many A. O. Smith tanks it sits under a black plastic cover marked "Anode", with foam insulation over its hex head. On others, the rod is combined with the hot water outlet nipple, so it sits where the hot pipe leaves the tank. Your manual's parts diagram shows which you have.</p>

<h2>How the anode rod is checked and replaced</h2>
<p>This is the procedure in the A. O. Smith manual, summarized so you know what the job involves. If you are not comfortable doing it, the manual says to have a qualified person replace the rod.</p>
<ol>
	<li>Turn the power off (or the gas control to Off), run hot water until it is cool, close the cold water supply and open a hot faucet to release the pressure.</li>
	<li>Remove the cover and the foam over the anode's hex head.</li>
	<li>Unscrew the rod with a <?php echo $hwm_t( 'anode.socket' ); // phpcs:ignore ?> six-point socket. The manual calls for an impact wrench, because factory rods are tight, and warns to seat the socket fully so the head does not strip.</li>
	<li>Inspect it and replace it if it is depleted. Wrap the new rod's threads in thread sealant tape or pipe joint compound and tighten it.</li>
	<li>Turn the cold supply back on, run a hot faucet until it flows full, check for leaks, then restore power or relight by the label.</li>
</ol>
<p>A combined outlet-nipple rod is removed with a pipe wrench at the nipple; the manual warns not to damage the threads.</p>

<h2>Anode rods and your warranty</h2>
<p>The rod is maintenance, and skipping it can cost you the tank warranty. GE says failure to replace the rod when consumed voids the tank warranty, and that running the tank with the rod removed shortens its life and voids coverage. A. O. Smith says a rod must be installed at all times or the warranty is void, and that rods are a consumable item not covered under warranty.</p>
<p>Keep a note of the date and what you found each time you check it. If you ever make a warranty claim, that record helps.</p>

<h2>Is replacing the anode rod worth it?</h2>
<p>On a younger tank, almost always. A rod costs about <?php echo $hwm_t( 'anode.priceLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'anode.priceHigh' ); // phpcs:ignore ?>, while a typical new gas tank installed costs about $<?php echo $hwm_new; // phpcs:ignore ?> in our cost model. A. O. Smith says replacing the rod before it is gone protects the tank for years longer.</p>
<p>On an old tank, it depends on whether the tank has already started to rust. Once the rod is gone, the steel corrodes, and a new rod cannot reverse that. A tank that already leaks needs replacing, not a rod: see <?php echo hwm_link( 'water-heater-leaking', 'water heater leaking' ); // phpcs:ignore ?> to tell a tank leak from a fitting leak. Check the age first with the <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> below: a tank past <?php echo esc_html( (string) hwm_value( $hwm_age, 'bandsPct.redFrom' ) ); ?>% of its typical life can fail at any time.</p>
<?php
ob_start();
hwm_part( 'age-inputs', array( 'file' => 'water-heater-age-calculator' ) );
hwm_part( 'calculator', array( 'tool' => 'water-heater-age-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater age calculator' ) );
?>
<p>The calculator's "Anode rod has been replaced" box adds <?php echo esc_html( hwm_value( 'lifespans', 'adjustments.anodeReplaced' ) ); ?> year to the expected life it uses, an editorial assumption we explain on <?php echo hwm_link( 'methodology', 'our methodology page' ); // phpcs:ignore ?>. If the result says the heater is near the end, our <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace calculator' ); // phpcs:ignore ?> weighs a repair against a new unit.</p>

<h2>When this is a job for a licensed professional</h2>
<p>Checking and replacing a rod is in the owner's manual, but it means draining, a stubborn fitting and working around gas or electric controls. Have a qualified person do it if the rod will not turn, if the head strips, if there is little room above the tank, if the tank is old and the fitting is corroded, or if you are not comfortable shutting the heater down. GE says its own anode inspection should be done by a qualified servicer or plumber.</p>

<h2>Limitations of this guide</h2>
<p>Rules and steps come from A. O. Smith and GE; other makers' rules can differ, and your manual wins. Prices are a single retailer's listings on one date, not quotes, and they change. We do not test rods ourselves. See <?php echo hwm_link( 'editorial-policy', 'our editorial policy' ); // phpcs:ignore ?> for how we choose sources.</p>
<?php
$faq = array(
	array(
		'q' => 'How often should I replace the anode rod in my water heater?',
		'a' => 'Check it rather than going by the calendar. A. O. Smith says rods usually last ' . hwm_value( $hwm_f, 'anode.lifeYearsLow' ) . ' to ' . hwm_value( $hwm_f, 'anode.lifeYearsHigh' ) . ' years and Rheem advises every ' . hwm_value( $hwm_f, 'anode.rheemReplaceYearsLow' ) . ' to ' . hwm_value( $hwm_f, 'anode.rheemReplaceYearsHigh' ) . '. Inspect it after the first ' . hwm_value( $hwm_f, 'flush.firstFlushMonths' ) . ' months and then yearly; replace it once more than ' . hwm_value( $hwm_f, 'anode.coreWireInches' ) . ' inches of core wire shows.',
	),
	array(
		'q' => 'How much does a water heater anode rod cost?',
		'a' => 'About ' . hwm_value( $hwm_f, 'anode.priceLow' ) . ' to ' . hwm_value( $hwm_f, 'anode.priceHigh' ) . ' for a magnesium, aluminum or zinc alloy rod and about ' . hwm_value( $hwm_f, 'anode.poweredPrice' ) . ' for a powered rod, by Home Depot listing prices on ' . hwm_value( $hwm_f, 'anode.pricesChecked' ) . '. Labor is extra if a plumber fits it.',
	),
	array(
		'q' => 'Is magnesium or aluminum better for an anode rod?',
		'a' => 'Use the metal your manual specifies. A. O. Smith lists magnesium, aluminum and zinc. If your hot water smells of rotten eggs, A. O. Smith and GE point to a zinc or special alloy rod. With softened water, any rod wears faster, so inspect it more often.',
	),
	array(
		'q' => 'What happens if you never replace the anode rod?',
		'a' => 'Once the rod is used up, the steel tank starts to corrode from the inside, which ends in a leak that cannot be repaired. GE says failing to replace a consumed rod also voids the tank warranty.',
	),
	array(
		'q' => 'Does a tankless water heater have an anode rod?',
		'a' => 'No. A tankless unit has no storage tank to protect. Its care task is descaling the heat exchanger, which Rinnai says to do at least once a year.',
	),
	array(
		'q' => 'Can I remove the anode rod to stop the smell?',
		'a' => 'No. GE says running the tank without the rod shortens its life and voids the warranty, and A. O. Smith says a rod must be installed at all times. Replace it with a zinc or special alloy rod instead, or treat the bacteria.',
	),
);
$sources = array(
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Installation Instructions and Use & Care Guide (100400765): replacing the anode rod, water odor', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater maintenance: checking the sacrificial anode', 'url' => $hwm_r( 'aos-maintenance' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater maintenance checklist', 'url' => $hwm_r( 'aos-checklist' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater leaking: causes, signs and fixes (anode rod life)', 'url' => $hwm_r( 'aos-leak-guide' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater not working? (smelly water, anode rod)', 'url' => $hwm_r( 'aos-not-working' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'GE Appliances', 'title' => 'Water heater: anode rod maintenance and service', 'url' => $hwm_r( 'ge-anode' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace (anode rod interval)', 'url' => $hwm_r( 'rheem-lifespan' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Corro-Protec', 'title' => 'How a powered anode rod works', 'url' => $hwm_r( 'corro-protec' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Anode rod listing prices (product pages named in the table)', 'url' => $hwm_r( 'home-depot-anodes' ), 'accessed' => '2026-11-13' ),
);
