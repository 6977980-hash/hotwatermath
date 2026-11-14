<?php
/**
 * /whirlpool-water-heater-age/ (STRATEGY §2 item 1c). Whirlpool water heaters are made by American
 * Water Heater, so the decoder uses the American code (data/serial-formats.json → brands.american).
 * Numbers come from data/brand-pages.json → brands.whirlpool, data/lifespans.json and
 * data/computed/whirlpool-water-heater-age.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'whirlpool-water-heater-age';
$hwm_c    = 'computed/' . $hwm_file;
$hwm_ls   = 'lifespans';
$hwm_v    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_l    = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_bp   = (array) hwm_raw( 'brand-pages', 'brands.whirlpool' );
$hwm_am   = (array) hwm_raw( 'brand-pages', 'brands.american' );
$hwm_cur  = (array) hwm_raw( $hwm_c, 'examples.current' );
$hwm_old  = (array) hwm_raw( $hwm_c, 'examples.old' );
$hwm_chk  = (array) hwm_raw( $hwm_c, 'checks' );
$hwm_r18  = (array) hwm_raw( 'brand-pages', 'brands.ao-smith.recalls.cpsc2018' );
$hwm_mv   = (array) hwm_raw( $hwm_ls, 'manufacturerViews.ao-smith' );
$hwm_war  = (array) $hwm_bp['warranty'];
$hwm_look = (array) $hwm_bp['lookup'];
$hwm_lab  = (array) $hwm_bp['labelNote'];
$hwm_hist = (array) $hwm_am['history'];
$hwm_s4   = substr( (string) $hwm_cur['serial'], 0, 4 );
$hwm_bcs  = (string) $hwm_chk[2]['sourceUrl'];
$hwm_site = 'https://www.whirlpoolwaterheaters.com/';

ob_start();
hwm_part( 'age-inputs', array( 'file' => $hwm_file, 'serial_hint' => 'Only the first four digits matter. The example is a test serial in the Whirlpool layout; replace it with yours.' ) );
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'american-water-heater-age', 'text' => 'American water heater age', 'why' => 'the company that builds Whirlpool tanks' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'what to do once you know the age' ),
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'every other brand' ),
);
$related = array( 'american-water-heater-age', 'water-heater-age-calculator', 'how-long-does-a-water-heater-last', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> The U.S. Consumer Product Safety Commission names Whirlpool among the brands in its 2018 recall of A. O. Smith-made water heaters and says the first four serial digits are the year and week of production. We tested our decoder against that recall range and the published Whirlpool examples: <?php echo $hwm_v( 'checksMatched' ); // phpcs:ignore ?> of <?php echo esc_html( (string) count( $hwm_chk ) ); ?> match, all listed below and on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you read a Whirlpool water heater serial number?</h2>
<p>Take the first four digits. The first two are the year and the next two are the week of that year, so <code><?php echo esc_html( $hwm_s4 ); ?></code> is week <?php echo esc_html( (string) $hwm_cur['week'] ); ?> of <?php echo esc_html( (string) $hwm_cur['year'] ); ?>, which falls in <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>. Whatever follows, a letter such as T or more digits, is a plant code and unit number and has no date in it.</p>
<?php
hwm_part(
	'serial-figure',
	array(
		'id'       => 'whirlpool-serial',
		'title'    => 'How a Whirlpool serial number shows the date',
		'desc'     => 'A tank water heater with the label low on the jacket, and a close-up of the label. In the serial ' . $hwm_cur['serial'] . ' the first two digits are the year and the next two are the week; a letter and six digits follow.',
		'unit'     => 'valve',
		'segments' => array(
			array( 'text' => substr( (string) $hwm_cur['serial'], 0, 2 ), 'label' => 'year: ' . $hwm_cur['year'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 2, 2 ), 'label' => 'week ' . $hwm_cur['week'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 4, 1 ), 'label' => 'letter', 'hl' => false ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 5 ), 'label' => 'unit number', 'hl' => false ),
		),
		'caption'  => 'Test serial ' . $hwm_cur['serial'] . ': ' . $hwm_cur['year'] . ', week ' . $hwm_cur['week'] . ' (' . $hwm_cur['madeLabel'] . '). Whirlpool says the label is near the bottom of the water heater.',
	)
);
?>
<p>Some Whirlpool serials are all digits, such as <code><?php echo esc_html( (string) $hwm_chk[3]['serial'] ); ?></code>, and others have a letter after the date, such as <code><?php echo esc_html( (string) $hwm_chk[2]['serial'] ); ?></code>. The date sits in the same four places in both.</p>

<h2>Turn the week into a month</h2>
<p>A week number is precise but hard to picture. Use this table to see which month a week falls in; the calculator does the same step for you.</p>
<div class="table-wrap" role="region" aria-label="Week to month" tabindex="0">
	<table>
		<caption>Production week to month</caption>
		<thead><tr><th scope="col">Month</th><th scope="col">Weeks</th></tr></thead>
		<tbody>
			<?php foreach ( (array) hwm_raw( $hwm_c, 'weekToMonth' ) as $hwm_r ) : ?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_r['month'] ); ?></th><td><?php echo esc_html( (string) $hwm_r['weeks'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Computed with the decoder's own formula (month = (week − 1) × 12 ÷ 52, rounded down, plus 1). A week at the edge of a month can belong to either one.</p>
</div>

<h2>Who makes Whirlpool water heaters?</h2>
<p>American Water Heater does. Whirlpool's water heater site calls itself a "Whirlpool Licensed Partner", its footer is signed by American Water Heater, and the Whirlpool warranty names American Water Heater Company in <?php echo esc_html( (string) $hwm_hist['johnsonCity'] ); ?> as the manufacturer (<?php echo $hwm_ext( $hwm_site, 'Whirlpool water heaters' ); // phpcs:ignore ?>).</p>
<p>American has been part of A. O. Smith since <?php echo esc_html( (string) $hwm_hist['acquired'] ); ?>, when A. O. Smith completed its purchase of American's parent, GSW. At the time A. O. Smith said the deal "partners us with Lowe's" (<?php echo $hwm_ext( (string) $hwm_hist['sourceUrl'], 'ACHR News' ); // phpcs:ignore ?>). That is why a Whirlpool serial reads exactly like an American one, and why the <?php echo hwm_link( 'american-water-heater-age', 'American water heater age page' ); // phpcs:ignore ?> covers the same code from the maker's side.</p>

<h2>Whirlpool serial number format</h2>
<?php hwm_part( 'serial-formats-table', array( 'file' => $hwm_file, 'caption' => 'Whirlpool water heater serial number date code (American code)' ) ); ?>
<p>We found no older Whirlpool layout. If your serial does not start with four digits, it may be a different maker's unit with a Whirlpool name on it; enter the year from the label instead.</p>

<h2>What the model number says about the warranty</h2>
<p>The Whirlpool warranty sheet says the warranty term is set by a code inside the model number. In the sample model <code><?php echo esc_html( (string) $hwm_war['modelExample'] ); ?></code>, the digit after the T is that code. Read it off your plate, then this table tells you how long the tank was covered.</p>
<div class="table-wrap" role="region" aria-label="Whirlpool warranty codes" tabindex="0">
	<table>
		<caption>Whirlpool warranty code in the model number</caption>
		<thead><tr><th scope="col">Warranty code</th><th scope="col">Tank</th><th scope="col">Parts</th><th scope="col">Labor</th></tr></thead>
		<tbody>
			<?php foreach ( (array) $hwm_war['codes'] as $hwm_w ) : ?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_w['code'] ); ?></th><td><?php echo esc_html( $hwm_w['tank'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_w['parts'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_w['labor'] . ( 1 === (int) $hwm_w['labor'] ? ' year' : ' years' ) ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Source: <?php echo $hwm_ext( (string) $hwm_war['sourceUrl'], 'Whirlpool residential water heater limited warranty (PDF)' ); // phpcs:ignore ?>. Covers the original owner in a single-family home; one year if used commercially.</p>
</div>
<p>Put the two numbers together. A tank made in week <?php echo esc_html( (string) $hwm_cur['week'] . ' of ' . $hwm_cur['year'] ); ?> with a <?php echo esc_html( (string) $hwm_war['codes'][0]['code'] ); ?> in the model was covered until about <?php echo esc_html( (string) ( (int) $hwm_cur['year'] + (int) $hwm_war['codes'][0]['tank'] ) ); ?>; the warranty runs from installation, which is usually a few months after the build date. A tank past its warranty is not broken, but a leak from the tank itself is then a replacement you pay for.</p>

<h2>Is your Whirlpool water heater part of a recall?</h2>
<p>Only if it is a specific gas model. The CPSC's 2018 recall covered Ultra-Low NOx gas water heaters sold mainly in California under the Whirlpool, American, A. O. Smith and other names, with serials whose first four digits run from <?php echo esc_html( $hwm_r18['firstDigits'] . ' to ' . $hwm_r18['lastDigits'] ); ?>, made <?php echo esc_html( $hwm_r18['madeFrom'] . ' through ' . $hwm_r18['madeTo'] ); ?>. The serial alone does not decide it; the model must match too, and the <?php echo $hwm_ext( (string) $hwm_r18['sourceUrl'], 'CPSC notice' ); // phpcs:ignore ?> lists the models and the contact line for Whirlpool owners.</p>

<h2>We checked the decoder against real Whirlpool serials</h2>
<?php hwm_part( 'decoder-checks', array( 'file' => $hwm_file, 'caption' => 'Whirlpool serials: our decoder against the CPSC recall and a published decoder' ) ); ?>
<p>The recall range is the stronger evidence, because the CPSC states the code and the dates side by side. The two examples from the Building Intelligence Center show the layouts printed on real labels, with and without a letter.</p>

<h2>Worked examples</h2>
<p><strong>Electric tank on hard water, flushed every year.</strong> <code><?php echo esc_html( $hwm_s4 ); ?></code> is <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>, so on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?> the tank is <?php echo esc_html( (string) $hwm_cur['age'] ); ?> years old. The DOE typical life of <?php echo esc_html( (string) $hwm_cur['baseTypical'] ); ?> years drops by <?php echo esc_html( (string) abs( (float) $hwm_cur['hardDelta'] ) ); ?> for hard water, to <?php echo esc_html( (string) $hwm_cur['lifeTypical'] ); ?>. It has used <?php echo esc_html( (string) $hwm_cur['usedPct'] ); ?>% of that: "<?php echo esc_html( (string) $hwm_cur['statusLabel'] ); ?>", with <?php echo esc_html( $hwm_cur['remainingMin'] . ' to ' . $hwm_cur['remainingMax'] ); ?> years likely left.</p>
<p><strong>Gas tank, never flushed, serial <?php echo esc_html( (string) $hwm_old['serial'] ); ?>.</strong></p>
<ol>
	<li>First four digits <code><?php echo esc_html( substr( (string) $hwm_old['serial'], 0, 4 ) ); ?></code>: <?php echo esc_html( (string) $hwm_old['year'] ); ?>, week <?php echo esc_html( (string) $hwm_old['week'] ); ?>, so <?php echo esc_html( (string) $hwm_old['madeLabel'] ); ?>.</li>
	<li>Age: <?php echo esc_html( (string) $hwm_old['age'] ); ?> years.</li>
	<li>Expected life: <?php echo esc_html( (string) $hwm_old['baseTypical'] ); ?> years, <?php echo esc_html( (string) $hwm_old['flushDelta'] ); ?> for no flushing, so <?php echo esc_html( (string) $hwm_old['lifeTypical'] ); ?> (range <?php echo esc_html( $hwm_old['lifeLow'] . ' to ' . $hwm_old['lifeHigh'] ); ?>).</li>
	<li>Share used: <?php echo esc_html( (string) $hwm_old['usedPct'] ); ?>%, "<?php echo esc_html( (string) $hwm_old['statusLabel'] ); ?>". Budget for a replacement now rather than after a leak.</li>
</ol>

<h2>Where is the serial number on a Whirlpool water heater?</h2>
<p>Whirlpool says: "<?php echo esc_html( (string) $hwm_lab['text'] ); ?>" On gas models that label sits beside the gas control; on electric models it is on the jacket, usually above or between the element covers. Look for the line marked "Serial No." and ignore the model number, which carries the warranty code but not the date.</p>
<p>Whirlpool's site also has a serial lookup. You type the serial and, if the unit is in its records, it shows a description and the manufacturing date (<?php echo $hwm_ext( (string) $hwm_look['url'], 'Whirlpool serial lookup' ); // phpcs:ignore ?>). When we tried the published example serials on <?php echo esc_html( hwm_date( (string) $hwm_look['tested'] ) ); ?>, it did not recognise them, so it works only with a real serial from your own label.</p>

<h2>Mistakes to avoid</h2>
<ul>
	<li><strong>Reading digits three and four as a month.</strong> They are a week. <code>1905</code> is week 5 of 2019, the turn of January and February, not May.</li>
	<li><strong>Using the model number.</strong> The model tells you size, fuel and warranty; only the serial holds the date.</li>
	<li><strong>Counting from the purchase date.</strong> The serial gives the build date. A tank can sit in a store or warehouse for months, so the install date may be later; your receipt or permit settles that.</li>
	<li><strong>Assuming every Whirlpool-branded unit is the same.</strong> The code above fits the tanks American builds. A label with a different layout should be dated from the year printed on it.</li>
</ul>

<h2>How long does a Whirlpool water heater last?</h2>
<p>Whirlpool does not publish its own lifespan figure that we could find. A. O. Smith, the parent of the company that builds Whirlpool tanks, says traditional tanks last <?php echo esc_html( $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] ); ?> years. Department of Energy survey data puts a typical storage tank at <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years, with most lasting <?php echo $hwm_l( 'types.gas_tank.low' ); // phpcs:ignore ?> to <?php echo $hwm_l( 'types.gas_tank.high' ); // phpcs:ignore ?>, and the calculator uses the DOE figures.</p>
<p>Warranty length is a weak guide on its own: a longer warranty means longer cover, not a promise of longer life, and water hardness and flushing still decide most of the result. See <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?> for what shortens a tank's life.</p>

<h2>Limitations</h2>
<p>The Whirlpool examples come from a third-party decoder; the code itself rests on the CPSC recall, which covers several brands at once. A week code gives the month, which can be off by one at a boundary. Whirlpool's own lookup only reads serials in its records. Expected life is a DOE average, and our hardness, flushing and anode adjustments are editorial assumptions explained on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I tell how old my Whirlpool water heater is?',
		'a' => 'Find the serial on the label near the bottom of the tank. The first two digits are the year and the next two the week, so ' . $hwm_s4 . ' means week ' . $hwm_cur['week'] . ' of ' . $hwm_cur['year'] . ', about ' . $hwm_cur['madeLabel'] . '. Type the serial into the calculator above for the age and remaining life.',
	),
	array(
		'q' => 'Is there a Whirlpool water heater serial number lookup?',
		'a' => 'Yes. Whirlpool\'s licensed water heater site has a "Need More Help" form that takes the serial and shows the manufacturing date for units in its records. It did not recognise published example serials in our test, so use your own label\'s serial.',
	),
	array(
		'q' => 'Who manufactures Whirlpool water heaters?',
		'a' => 'American Water Heater, an A. O. Smith company since ' . $hwm_hist['acquired'] . ', makes them under license from Whirlpool. The Whirlpool warranty names American Water Heater Company in Johnson City, Tennessee, as the manufacturer.',
	),
	array(
		'q' => 'What does the T in a Whirlpool serial number mean?',
		'a' => 'It is not part of the date. The date is the first four digits only, year then week. Letters and digits after that identify the plant and the individual unit, so you can ignore them when working out the age.',
	),
	array(
		'q' => 'How long is the warranty on a Whirlpool water heater?',
		'a' => 'It depends on the warranty code in the model number. Per the Whirlpool warranty sheet, ' . implode( '; ', array_map( static fn( $w ) => 'code ' . $w['code'] . ' gives ' . $w['tank'] . ' years on the tank, ' . $w['parts'] . ' on parts and ' . $w['labor'] . ( 1 === (int) $w['labor'] ? ' year' : ' years' ) . ' of labor', (array) $hwm_war['codes'] ) ) . '. Terms apply to the original owner in a single-family home.',
	),
	array(
		'q' => 'How long do Whirlpool water heaters last?',
		'a' => 'DOE data puts a typical tank at ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years, and A. O. Smith, parent of the maker, says ' . $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] . ' years for tanks. Hard water and skipped flushing shorten that; a new anode rod can add years.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'A. O. Smith recalls Ultra-Low NOx water heaters (2018): Whirlpool among the brands; first four serial digits = year and week', 'url' => (string) $hwm_r18['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Whirlpool water heaters (American Water Heater, licensed partner)', 'title' => 'Need More Help: serial lookup and label location', 'url' => (string) $hwm_look['url'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Whirlpool water heaters (American Water Heater Company)', 'title' => 'Residential water heater limited warranty, warranty codes (PDF, 2015)', 'url' => (string) $hwm_war['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ACHR News', 'title' => 'A. O. Smith completes acquisition of GSW (April 26, 2006)', 'url' => (string) $hwm_hist['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water heater serial number decoder (PDF): Whirlpool under American', 'url' => (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Building Intelligence Center', 'title' => 'Whirlpool water heater age (serial styles)', 'url' => $hwm_bcs, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long do water heaters last?', 'url' => (string) $hwm_mv['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
