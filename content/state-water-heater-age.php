<?php
/**
 * /state-water-heater-age/ (STRATEGY §2 item 1c). Numbers come from data/serial-formats.json,
 * data/lifespans.json, data/brand-pages.json and data/computed/state-water-heater-age.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'state-water-heater-age';
$hwm_c    = 'computed/' . $hwm_file;
$hwm_ls   = 'lifespans';
$hwm_v    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_l    = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_cur  = (array) hwm_raw( $hwm_c, 'examples.current' );
$hwm_old  = (array) hwm_raw( $hwm_c, 'examples.old' );
$hwm_chk  = (array) hwm_raw( $hwm_c, 'checks' );
$hwm_bpc  = (array) hwm_raw( 'brand-pages', 'brands.state.checks' );
$hwm_r18  = (array) hwm_raw( 'brand-pages', 'brands.ao-smith.recalls.cpsc2018' );
$hwm_mv   = (array) hwm_raw( $hwm_ls, 'manufacturerViews.ao-smith' );
$hwm_mo   = (array) hwm_raw( $hwm_c, 'client.months' );
$hwm_alias = (array) hwm_raw( $hwm_c, 'alsoSoldAs' );
$hwm_sup  = 'https://www.statewaterheaters.com/support/';
$hwm_war  = 'https://www.statewaterheaters.com/support/warranty-verification.html';
$hwm_bcs  = (string) hwm_raw( 'serial-formats', 'brands.state.patterns.2.sourceUrl' );
$hwm_s4   = substr( (string) $hwm_cur['serial'], 0, 4 );
$hwm_from = (string) hwm_raw( 'serial-formats', 'brands.state.patterns.0.validFromYear' );
$hwm_to04 = (string) hwm_raw( 'serial-formats', 'brands.state.patterns.2.validToYear' );
$hwm_mlet = array_slice( str_split( (string) hwm_raw( 'serial-formats', 'brands.state.patterns.1.fields.month.letters' ) ), 0, count( $hwm_mo ) );

ob_start();
hwm_part( 'age-inputs', array( 'file' => $hwm_file, 'serial_hint' => 'The date is in the first three or four characters. The example is a test serial in the current State format; replace it with yours.' ) );
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'ao-smith-water-heater-age', 'text' => 'A. O. Smith water heater age', 'why' => 'the parent brand, same code since ' . $hwm_from ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'what to do once you know the age' ),
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'every other brand' ),
);
$related = array( 'ao-smith-water-heater-age', 'water-heater-age-calculator', 'how-long-does-a-water-heater-last', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> The current State code is the one the U.S. Consumer Product Safety Commission describes in its 2018 recall of A. O. Smith-made water heaters, State among them. Pre-<?php echo esc_html( $hwm_from ); ?> codes come from the Energy Trust of Oregon decoder and the Building Intelligence Center, and the source of each row is named in the table. Our decoder matched all <?php echo $hwm_v( 'checksMatched' ); // phpcs:ignore ?> test cases on this page; see <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you read a State water heater serial number?</h2>
<p>First check whether the serial starts with digits or a letter. If it starts with four digits, the first two are the year and the next two the production week: <code><?php echo esc_html( $hwm_s4 ); ?></code> is week <?php echo esc_html( (string) $hwm_cur['week'] ); ?> of <?php echo esc_html( (string) $hwm_cur['year'] ); ?>. If it starts with a letter, the unit predates <?php echo esc_html( $hwm_from ); ?> and that letter (or the second one) is the month.</p>
<?php
hwm_part(
	'serial-figure',
	array(
		'id'       => 'state-serial',
		'title'    => 'How a State serial number shows the date',
		'desc'     => 'A gas tank with the rating plate beside the gas control, and a close-up of the plate. In the serial ' . $hwm_cur['serial'] . ' the first two digits are the year and the next two are the week; a letter and six digits follow.',
		'unit'     => 'valve',
		'segments' => array(
			array( 'text' => substr( (string) $hwm_cur['serial'], 0, 2 ), 'label' => 'year: ' . $hwm_cur['year'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 2, 2 ), 'label' => 'week ' . $hwm_cur['week'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 4, 1 ), 'label' => 'letter', 'hl' => false ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 5 ), 'label' => 'six-digit unit number', 'hl' => false ),
		),
		'caption'  => 'Test serial ' . $hwm_cur['serial'] . ': ' . $hwm_cur['year'] . ', week ' . $hwm_cur['week'] . ' (' . $hwm_cur['madeLabel'] . '). Energy Trust describes the current State serial as the year, the week, a letter, then a unit number.',
	)
);
?>

<h2>State serial number formats, oldest to newest</h2>
<p>State has used three layouts. The decoder tries them in the order shown and rejects any result outside the years each layout was used.</p>
<?php hwm_part( 'serial-formats-table', array( 'file' => $hwm_file, 'caption' => 'State water heater serial number date codes' ) ); ?>

<h3>Month letters on pre-<?php echo esc_html( $hwm_from ); ?> State serials</h3>
<div class="table-wrap" role="region" aria-label="State month letters" tabindex="0">
	<table>
		<caption>State month letters (the letter I is never used)</caption>
		<thead><tr><?php foreach ( $hwm_mlet as $hwm_k ) : ?><th scope="col"><?php echo esc_html( $hwm_k ); ?></th><?php endforeach; ?></tr></thead>
		<tbody><tr><?php foreach ( $hwm_mo as $hwm_m ) : ?><td><?php echo esc_html( substr( (string) $hwm_m, 0, 3 ) ); ?></td><?php endforeach; ?></tr></tbody>
	</table>
	<p class="table-source">Sources: <?php echo $hwm_ext( (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'Energy Trust of Oregon decoder' ); // phpcs:ignore ?>, <?php echo $hwm_ext( $hwm_bcs, 'Building Intelligence Center' ); // phpcs:ignore ?>; L and M fit an A. O. Smith <?php echo $hwm_ext( (string) ( $hwm_bpc[2]['sourceUrl'] ?? '' ), 'CPSC recall serial range' ); // phpcs:ignore ?> from the same family.</p>
</div>
<p>On the oldest style, which Building Intelligence Center says ended in <?php echo esc_html( $hwm_to04 ); ?>, the first letter is a factory code and the second is the month: <code><?php echo esc_html( substr( (string) $hwm_chk[3]['serial'], 0, 4 ) ); ?></code> is <?php echo esc_html( (string) $hwm_chk[3]['ours'] ); ?>. From then until <?php echo esc_html( $hwm_from ); ?>, the month letter comes first: <code><?php echo esc_html( substr( (string) $hwm_chk[1]['serial'], 0, 3 ) ); ?></code> is <?php echo esc_html( (string) $hwm_chk[1]['ours'] ); ?>.</p>

<h2>Is a State water heater dated the same way as an A. O. Smith?</h2>
<p>For units built in <?php echo esc_html( $hwm_from ); ?> or later, yes: both put a two-digit year and a two-digit week at the start of the serial. The CPSC's 2018 recall covered State, A. O. Smith, Reliance and other brands in one serial range, digits <?php echo esc_html( $hwm_r18['firstDigits'] . ' to ' . $hwm_r18['lastDigits'] ); ?>, and said those digits are the year and week of production.</p>
<p>Before <?php echo esc_html( $hwm_from ); ?> the details differ. The Energy Trust decoder describes older A. O. Smith serials with the month as the second letter and older State serials with the month first, although recall serials show both companies also used the other style. The calculator tries both layouts for both brands where a source supports it. If you are not sure which brand you have, the name on the rating plate decides, and the <?php echo hwm_link( 'ao-smith-water-heater-age', 'A. O. Smith age page' ); // phpcs:ignore ?> covers the other side.</p>

<h2>Worked examples: a recent electric tank and a <?php echo esc_html( (string) $hwm_old['year'] ); ?> gas tank</h2>
<p><strong>Electric tank, moderately hard water, flushed yearly.</strong> <code><?php echo esc_html( $hwm_s4 ); ?></code> means <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?> (week <?php echo esc_html( (string) $hwm_cur['week'] ); ?>), so the tank is <?php echo esc_html( (string) $hwm_cur['age'] ); ?> years old on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?>. With no adjustments its expected life is the DOE typical <?php echo esc_html( (string) $hwm_cur['lifeTypical'] ); ?> years, so it has used <?php echo esc_html( (string) $hwm_cur['usedPct'] ); ?>%: "<?php echo esc_html( (string) $hwm_cur['statusLabel'] ); ?>", with <?php echo esc_html( $hwm_cur['remainingMin'] . ' to ' . $hwm_cur['remainingMax'] ); ?> years likely left.</p>
<p><strong>Gas tank on very hard water, never flushed.</strong></p>
<ol>
	<li>The serial starts <code><?php echo esc_html( substr( (string) $hwm_old['serial'], 0, 3 ) ); ?></code>: month letter <?php echo esc_html( substr( (string) $hwm_old['serial'], 0, 1 ) ); ?>, year <?php echo esc_html( substr( (string) $hwm_old['serial'], 1, 2 ) ); ?>, so <?php echo esc_html( (string) $hwm_old['madeLabel'] ); ?>.</li>
	<li>Age: <?php echo esc_html( (string) $hwm_old['age'] ); ?> years.</li>
	<li>Expected life: <?php echo esc_html( (string) $hwm_old['baseTypical'] ); ?> years, <?php echo esc_html( (string) $hwm_old['hardDelta'] ); ?> for very hard water and <?php echo esc_html( (string) $hwm_old['flushDelta'] ); ?> for no flushing, so <?php echo esc_html( (string) $hwm_old['lifeTypical'] ); ?> years (range <?php echo esc_html( $hwm_old['lifeLow'] . ' to ' . $hwm_old['lifeHigh'] ); ?>).</li>
	<li>Share used: <?php echo esc_html( (string) $hwm_old['usedPct'] ); ?>%, "<?php echo esc_html( (string) $hwm_old['statusLabel'] ); ?>". On water this hard, a tank of that age is past due; check the base for moisture and plan the replacement.</li>
</ol>

<h2>We checked the decoder against real State serials</h2>
<?php hwm_part( 'decoder-checks', array( 'file' => $hwm_file, 'caption' => 'State serials: our decoder against recall notices and published decoders' ) ); ?>

<h2>Where is the serial number on a State water heater?</h2>
<p>It is on the rating plate on the outside of the tank, on the line marked "Serial No."; for the recalled A. O. Smith-made units the CPSC places the data plate next to the gas control valve or thermostat. Newer State plates also carry a QR code. State says that scanning it with a phone camera shows the model number, serial number and warranty status, with links to the manual (<?php echo $hwm_ext( $hwm_sup, 'State support' ); // phpcs:ignore ?>).</p>
<p>No QR code? Type the serial into State's <?php echo $hwm_ext( $hwm_war, 'warranty verification page' ); // phpcs:ignore ?>, which accepts a serial or reference number. Either route confirms the unit's details straight from State's records.</p>

<h2>Other names that use the State code</h2>
<p>The Energy Trust of Oregon decoder lists many brand names under State Industries, among them <?php echo esc_html( implode( ', ', $hwm_alias ) ); ?>. If your label carries one of these names and says it was made by State Industries, choose State in the calculator.</p>
<h3>Kenmore water heater age</h3>
<p>Kenmore is the tricky one: Kenmore water heaters were made by more than one manufacturer, so the State code only fits units whose plate names State or A. O. Smith as the maker. For those, the CPSC's 2018 recall is clear: it lists Kenmore among the A. O. Smith-made brands whose first four serial digits, <?php echo esc_html( $hwm_r18['firstDigits'] . ' to ' . $hwm_r18['lastDigits'] ); ?> in the recall, are the year and week of production. A Kenmore serial starting with four digits therefore reads like this page's current code; choose Kenmore in the calculator and it applies the State rules with a warning.</p>
<p>If your Kenmore plate names another maker, or the serial does not fit any layout above, enter the year printed on the label instead of guessing.</p>

<h2>How long does a State water heater last?</h2>
<p>We could not find a lifespan figure that State publishes for its own tanks. Its parent company, A. O. Smith, says traditional tanks last <?php echo esc_html( $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] ); ?> years, and Department of Energy survey data gives a typical storage tank <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years, with most lasting <?php echo $hwm_l( 'types.gas_tank.low' ); // phpcs:ignore ?> to <?php echo $hwm_l( 'types.gas_tank.high' ); // phpcs:ignore ?>. The calculator uses the DOE figures.</p>
<p>Water quality moves that number more than the brand does. See <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?> for what shortens a tank's life and what you can do about it.</p>

<h2>Limitations</h2>
<p>The two pre-<?php echo esc_html( $hwm_from ); ?> layouts come from independent decoders, not from State, and a serial that fits neither may belong to a format we have not confirmed. Week codes give a month that can be off by one near a boundary. State's commercial products may follow other rules. Expected life is a DOE average, and our adjustments for water and maintenance are editorial assumptions described on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I tell how old my State water heater is?',
		'a' => 'Look at the start of the serial number on the rating plate. Four digits mean year and week (' . $hwm_s4 . ' is week ' . $hwm_cur['week'] . ' of ' . $hwm_cur['year'] . '). A letter followed by two digits means month and year, so ' . substr( (string) $hwm_chk[1]['serial'], 0, 3 ) . ' is ' . $hwm_chk[1]['ours'] . '. The calculator above reads both.',
	),
	array(
		'q' => 'Is there a State water heater serial number lookup?',
		'a' => 'State offers a warranty verification page where you type the serial, and newer rating plates carry a QR code that shows the model, serial and warranty status. Neither gives the age as a number, which the calculator on this page does.',
	),
	array(
		'q' => 'Who makes State water heaters?',
		'a' => 'State is an A. O. Smith brand. The CPSC 2018 recall notice lists State among the brands A. O. Smith sells, and since ' . $hwm_from . ' State serials use the same year-and-week code as A. O. Smith.',
	),
	array(
		'q' => 'What does a letter at the start of a State serial mean?',
		'a' => 'It usually means the unit was built before ' . $hwm_from . '. On those serials a letter from A to M, skipping I, is the month, followed by a two-digit year. On the oldest style, used until about ' . $hwm_to04 . ', the first letter is a factory code and the second is the month.',
	),
	array(
		'q' => 'How long does a State water heater last?',
		'a' => 'State does not publish its own figure that we could find. DOE survey data puts a typical storage tank at ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years, and parent company A. O. Smith says ' . $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] . ' years for tanks. Hard water and skipped flushing shorten it.',
	),
	array(
		'q' => 'How do I tell the age of a Kenmore water heater?',
		'a' => 'If the plate names State or A. O. Smith as the maker, read it like a State: four digits at the start are the year and week. The CPSC 2018 recall lists Kenmore among the A. O. Smith-made brands that use this code. Other Kenmore units came from other makers; use the label year for those.',
	),
	array(
		'q' => 'Is a Reliance water heater dated like a State?',
		'a' => 'Reliance is listed with State by the Energy Trust of Oregon decoder and with A. O. Smith in the CPSC 2018 recall, and current units use the same year-and-week code. Choose State in the calculator for a Reliance unit.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'A. O. Smith recalls Ultra-Low NOx water heaters (2018): State among the brands; first four serial digits = year and week', 'url' => (string) ( $hwm_bpc[0]['sourceUrl'] ?? '' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'A. O. Smith recalls gas water heaters (2008): A. O. Smith and State models, serial ranges', 'url' => (string) ( $hwm_bpc[2]['sourceUrl'] ?? '' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water heater serial number decoder (PDF)', 'url' => (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Building Intelligence Center', 'title' => 'STATE water heater age (serial styles)', 'url' => $hwm_bcs, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'State Water Heaters', 'title' => 'Support: QR code on the rating plate', 'url' => $hwm_sup, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'State Water Heaters', 'title' => 'Warranty verification', 'url' => $hwm_war, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long do water heaters last?', 'url' => (string) $hwm_mv['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
