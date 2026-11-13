<?php
/**
 * /american-water-heater-age/ (STRATEGY §2 item 1c). American Water Heater Company code
 * (data/serial-formats.json → brands.american). Numbers come from data/brand-pages.json →
 * brands.american, data/lifespans.json and data/computed/american-water-heater-age.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file  = 'american-water-heater-age';
$hwm_c     = 'computed/' . $hwm_file;
$hwm_ls    = 'lifespans';
$hwm_v     = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_l     = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_ext   = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_bp    = (array) hwm_raw( 'brand-pages', 'brands.american' );
$hwm_cur   = (array) hwm_raw( $hwm_c, 'examples.current' );
$hwm_old   = (array) hwm_raw( $hwm_c, 'examples.old' );
$hwm_chk   = (array) hwm_raw( $hwm_c, 'checks' );
$hwm_r18   = (array) hwm_raw( 'brand-pages', 'brands.ao-smith.recalls.cpsc2018' );
$hwm_mv    = (array) hwm_raw( $hwm_ls, 'manufacturerViews.ao-smith' );
$hwm_hist  = (array) $hwm_bp['history'];
$hwm_wm    = (array) $hwm_bp['warrantyModels'];
$hwm_alias = (array) hwm_raw( $hwm_c, 'alsoSoldAs' );
$hwm_s4    = substr( (string) $hwm_cur['serial'], 0, 4 );
$hwm_et    = (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' );
$hwm_ia    = (string) $hwm_chk[3]['sourceUrl'];
$hwm_bca   = 'https://www.building-center.org/american-water-heater-age/';
$hwm_bcs   = 'https://www.building-center.org/american-standard-water-heater-age/';
$hwm_site  = 'https://www.americanwaterheater.com/';

ob_start();
hwm_part( 'age-inputs', array( 'file' => $hwm_file, 'serial_hint' => 'Ten digits on most tanks; only the first four matter. The example is a test serial; replace it with yours.' ) );
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'whirlpool-water-heater-age', 'text' => 'Whirlpool water heater age', 'why' => 'the brand American builds for Lowe\'s' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'what to do once you know the age' ),
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'every other brand' ),
);
$related = array( 'whirlpool-water-heater-age', 'ao-smith-water-heater-age', 'water-heater-age-calculator', 'how-long-does-a-water-heater-last' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> The rule (year, then week) is the one the Energy Trust of Oregon decoder gives for American, and the U.S. Consumer Product Safety Commission confirms it in a 2018 recall that lists American by name. Our decoder matched all <?php echo $hwm_v( 'checksMatched' ); // phpcs:ignore ?> test serials on this page; see <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you read an American water heater serial number?</h2>
<p>Most American serials are ten digits. The first two are the year and the next two are the week of that year: <code><?php echo esc_html( $hwm_s4 ); ?></code> is week <?php echo esc_html( (string) $hwm_cur['week'] ); ?> of <?php echo esc_html( (string) $hwm_cur['year'] ); ?>, or <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>. The remaining six digits number the unit and carry no date.</p>
<?php
hwm_part(
	'serial-figure',
	array(
		'id'       => 'american-serial',
		'title'    => 'How an American Water Heater serial number shows the date',
		'desc'     => 'A gas tank with the rating plate beside the gas control, and a close-up of the plate. In the ten-digit serial ' . $hwm_cur['serial'] . ' the first two digits are the year and the next two are the week; six digits follow.',
		'unit'     => 'valve',
		'segments' => array(
			array( 'text' => substr( (string) $hwm_cur['serial'], 0, 2 ), 'label' => 'year: ' . $hwm_cur['year'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 2, 2 ), 'label' => 'week ' . $hwm_cur['week'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 4 ), 'label' => 'six-digit unit number', 'hl' => false ),
		),
		'caption'  => 'Test serial ' . $hwm_cur['serial'] . ': ' . $hwm_cur['year'] . ', week ' . $hwm_cur['week'] . ' (' . $hwm_cur['madeLabel'] . '). The CPSC places the data plate next to the gas control valve or thermostat.',
	)
);
?>

<h2>Is the American code the same in every year?</h2>
<p>As far as any source shows, yes. The oldest dated American serial we could confirm, <code><?php echo esc_html( (string) $hwm_chk[3]['serial'] ); ?></code> from a data-tag photo on InspectApedia, reads <?php echo esc_html( (string) $hwm_chk[3]['ours'] ); ?>. Energy Trust's example, <code><?php echo esc_html( substr( (string) $hwm_chk[2]['serial'], 0, 4 ) ); ?></code> (<?php echo esc_html( (string) $hwm_chk[2]['ours'] ); ?>), uses the same layout, and so do the units the CPSC recall dates from <?php echo esc_html( $hwm_r18['madeFrom'] . ' through ' . $hwm_r18['madeTo'] ); ?>.</p>
<?php hwm_part( 'serial-formats-table', array( 'file' => $hwm_file, 'caption' => 'American water heater serial number date code' ) ); ?>
<p>InspectApedia does report much older tanks, built before the year-week code, whose serials are plain sequence numbers with no date. If your serial starts with a letter or has fewer than eight digits, it is probably one of those or another company's unit (see below). Use the building's age or the oldest date on any label instead.</p>

<h2>American, American Standard or American Appliance?</h2>
<p>Three different companies have put "American" on a water heater, and each dates its serials differently. Pick the right one before you decode.</p>
<div class="table-wrap" role="region" aria-label="Brands named American" tabindex="0">
	<table>
		<caption>Water heater brands with "American" in the name</caption>
		<thead><tr><th scope="col">Name on the label</th><th scope="col">Who made it</th><th scope="col">How the serial is dated</th></tr></thead>
		<tbody>
			<tr><th scope="row">American Water Heater (ProLine, U.S. Craftmaster)</th><td>American Water Heater Company, Johnson City, TN (A. O. Smith)</td><td>Year (2 digits), then week (2 digits): this page</td></tr>
			<tr><th scope="row">American Standard</th><td>Licensed brand; not the American Standard bath and kitchen company</td><td>Month letter, then two-digit year, e.g. J21-…; heat pump units use a 21-character serial (third party)</td></tr>
			<tr><th scope="row">American Appliance Mfg. Corp.</th><td>An older, separate manufacturer</td><td>Letter A, then year and week, e.g. A8548… (third party, uncertain)</td></tr>
		</tbody>
	</table>
	<p class="table-source">Sources: <?php echo $hwm_ext( $hwm_et, 'Energy Trust of Oregon' ); // phpcs:ignore ?>; <?php echo $hwm_ext( $hwm_bcs, 'Building Intelligence Center, American Standard' ); // phpcs:ignore ?>; <?php echo $hwm_ext( $hwm_ia, 'InspectApedia, American Appliance' ); // phpcs:ignore ?>. The calculator decodes the first row only.</p>
</div>

<h2>Which brands use the American code?</h2>
<p>The Energy Trust decoder says American made water heaters for dozens of brand names before A. O. Smith bought it and now makes <?php echo esc_html( implode( ', ', $hwm_alias ) ); ?> units. Its brand list includes store and hardware names such as Ace, Tru-Test and Master Plumber. If your plate names American Water Heater Company as the maker, the code on this page applies, whatever the brand name on the front.</p>
<p>Whirlpool is the biggest of these today; the <?php echo hwm_link( 'whirlpool-water-heater-age', 'Whirlpool water heater age page' ); // phpcs:ignore ?> covers its label, warranty code and lookup.</p>

<h2>Who owns American Water Heater?</h2>
<p>A. O. Smith. It completed its purchase of GSW, American's parent, in <?php echo esc_html( (string) $hwm_hist['acquired'] ); ?> (<?php echo $hwm_ext( (string) $hwm_hist['sourceUrl'], 'ACHR News' ); // phpcs:ignore ?>). The Energy Trust decoder gives <?php echo esc_html( (string) $hwm_hist['energyTrustYear'] ); ?> as the year, which does not match the deal reports; we use the reported date.</p>
<p>That ownership is why the code lines up with A. O. Smith's own. The CPSC's 2018 recall covered American, A. O. Smith, State, Whirlpool and other brands with one serial range, first four digits <?php echo esc_html( $hwm_r18['firstDigits'] . ' to ' . $hwm_r18['lastDigits'] ); ?>, and said those digits "represent the year and week of production". The <?php echo hwm_link( 'ao-smith-water-heater-age', 'A. O. Smith age page' ); // phpcs:ignore ?> covers the parent brand's older letter codes; we found no source that shows American using them.</p>

<h2>What the model number says about the warranty</h2>
<p>On current American residential tanks, the number right after the opening letter (G for gas, E for electric) is the warranty in years. American's own support pages name each model with its warranty, as the table shows.</p>
<div class="table-wrap" role="region" aria-label="American model numbers and warranty" tabindex="0">
	<table>
		<caption>American model numbers and the warranty in their names</caption>
		<thead><tr><th scope="col">Model</th><th scope="col">Warranty in the product name</th></tr></thead>
		<tbody>
			<?php foreach ( (array) $hwm_wm['items'] as $hwm_w ) : ?>
				<tr><th scope="row"><code><?php echo esc_html( (string) $hwm_w['model'] ); ?></code></th><td><?php echo esc_html( $hwm_w['years'] . ' years' ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Source: <?php echo $hwm_ext( $hwm_site, 'American Water Heaters product support pages' ); // phpcs:ignore ?> (checked 2026-11-13). Check your printed warranty for the exact terms.</p>
</div>
<p>Combine the model and the serial and you know whether the tank is still covered: a <?php echo esc_html( (string) $hwm_wm['items'][0]['model'] ); ?> made in <?php echo esc_html( (string) $hwm_cur['year'] ); ?> carries cover to about <?php echo esc_html( (string) ( (int) $hwm_cur['year'] + (int) $hwm_wm['items'][0]['years'] ) ); ?>, counted from installation.</p>

<h2>We checked the decoder against dated American serials</h2>
<?php hwm_part( 'decoder-checks', array( 'file' => $hwm_file, 'caption' => 'American serials: our decoder against the CPSC recall and published decoders' ) ); ?>

<h2>Worked examples: a <?php echo esc_html( (string) $hwm_cur['year'] ); ?> gas tank and a <?php echo esc_html( (string) $hwm_old['year'] ); ?> one</h2>
<p><strong>Gas tank, moderately hard water, flushed yearly.</strong> <code><?php echo esc_html( $hwm_s4 ); ?></code> is <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>, so on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?> the tank is <?php echo esc_html( (string) $hwm_cur['age'] ); ?> years old. Against the DOE typical life of <?php echo esc_html( (string) $hwm_cur['lifeTypical'] ); ?> years it has used <?php echo esc_html( (string) $hwm_cur['usedPct'] ); ?>%: "<?php echo esc_html( (string) $hwm_cur['statusLabel'] ); ?>".</p>
<p><strong>The <?php echo esc_html( (string) $hwm_old['year'] ); ?> tank from the data-tag photo, hard water, never flushed, anode replaced once.</strong></p>
<ol>
	<li><code><?php echo esc_html( substr( (string) $hwm_old['serial'], 0, 4 ) ); ?></code>: <?php echo esc_html( (string) $hwm_old['year'] ); ?>, week <?php echo esc_html( (string) $hwm_old['week'] ); ?>, so <?php echo esc_html( (string) $hwm_old['madeLabel'] ); ?>. Age <?php echo esc_html( (string) $hwm_old['age'] ); ?> years.</li>
	<li>Expected life: <?php echo esc_html( (string) $hwm_old['baseTypical'] ); ?> years, <?php echo esc_html( (string) $hwm_old['hardDelta'] ); ?> for hard water, <?php echo esc_html( (string) $hwm_old['flushDelta'] ); ?> for no flushing and +<?php echo esc_html( (string) $hwm_old['anodeDelta'] ); ?> for the new anode: <?php echo esc_html( (string) $hwm_old['lifeTypical'] ); ?> years.</li>
	<li>Share used: <?php echo esc_html( (string) $hwm_old['usedPct'] ); ?>%. A tank this far past its expected life is living on borrowed time; plan the replacement before it fails.</li>
</ol>

<h2>Where is the serial number on an American water heater?</h2>
<p>It is on the rating plate, the printed label on the side of the tank, on the line marked "Serial No." For the gas models in the 2018 recall, the CPSC says the data plate is next to the gas control valve or thermostat. The Building Intelligence Center notes that the plate usually shows a Johnson City, Tennessee address even on units built in Canada (<?php echo $hwm_ext( $hwm_bca, 'Building Intelligence Center' ); // phpcs:ignore ?>), so do not take the address as the factory.</p>
<p>American does not run a public age lookup that we could find. If you need the build date confirmed for a warranty claim, the warranty office asks for the model, serial, purchase date and install date.</p>

<h2>How long does an American water heater last?</h2>
<p>American does not publish its own lifespan figure that we found. Its parent, A. O. Smith, says traditional tanks last <?php echo esc_html( $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] ); ?> years, and DOE survey data gives a typical storage tank <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years (most <?php echo $hwm_l( 'types.gas_tank.low' ); // phpcs:ignore ?> to <?php echo $hwm_l( 'types.gas_tank.high' ); // phpcs:ignore ?>). The calculator uses the DOE figures and adjusts for your water and upkeep; <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?> explains each factor.</p>

<h2>Limitations</h2>
<p>The <?php echo esc_html( (string) $hwm_old['year'] ); ?> example comes from a third-party site, and the notes on American Standard and American Appliance come from third-party decoders. A week code gives the month, which can be off by one at a boundary. Commercial American models may follow other rules. Expected life is a DOE average, and our adjustments are editorial assumptions described on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I find the age of my American water heater?',
		'a' => 'Read the serial on the rating plate. The first two digits are the year and the next two the week: ' . $hwm_s4 . ' is week ' . $hwm_cur['week'] . ' of ' . $hwm_cur['year'] . ', about ' . $hwm_cur['madeLabel'] . '. The calculator above turns that into an age and an estimate of the life left.',
	),
	array(
		'q' => 'Is there an American water heater serial number lookup?',
		'a' => 'We found no public age lookup on American\'s own site. The serial code is simple enough to read yourself, and the decoder on this page does it for you. For warranty questions American asks for the model, serial, purchase and install dates.',
	),
	array(
		'q' => 'Is American Water Heater the same company as American Standard?',
		'a' => 'No. American Standard water heaters are a licensed brand made by a different company, and they date serials with a month letter and a two-digit year. American Water Heater Company, in Johnson City, Tennessee, uses year then week.',
	),
	array(
		'q' => 'Who owns American Water Heater?',
		'a' => 'A. O. Smith, since it bought American\'s parent company, GSW, in ' . $hwm_hist['acquired'] . '. American makes the American, ProLine, U.S. Craftmaster and Whirlpool brands, and its serials use the same year-and-week code as current A. O. Smith tanks.',
	),
	array(
		'q' => 'How do I read an American ProLine serial number?',
		'a' => 'The same way as any American serial: the first two digits are the year and the next two the week. ProLine is one of American\'s own product lines (its site sells ProLine gas and electric tanks), so choose American in the calculator.',
	),
	array(
		'q' => 'How long do American water heaters last?',
		'a' => 'DOE data puts a typical tank at ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years and parent company A. O. Smith says ' . $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] . '. A longer-warranty model has longer cover, but water hardness and flushing affect the real life more than the model does.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'A. O. Smith recalls Ultra-Low NOx water heaters (2018): American among the brands; first four serial digits = year and week', 'url' => (string) $hwm_r18['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water heater serial number decoder (PDF): American', 'url' => $hwm_et, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ACHR News', 'title' => 'A. O. Smith completes acquisition of GSW (April 26, 2006)', 'url' => (string) $hwm_hist['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'American Water Heaters', 'title' => 'Product support pages (model names with warranty years)', 'url' => $hwm_site, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'InspectApedia', 'title' => 'American water heater age (data tag 9950123040)', 'url' => $hwm_ia, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Building Intelligence Center', 'title' => 'American water heater age (plate address note)', 'url' => $hwm_bca, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Building Intelligence Center', 'title' => 'American Standard water heater age (different maker and code)', 'url' => $hwm_bcs, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long do water heaters last?', 'url' => (string) $hwm_mv['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
