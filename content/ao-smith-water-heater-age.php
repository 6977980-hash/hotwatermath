<?php
/**
 * /ao-smith-water-heater-age/ (STRATEGY §2 item 1c). Numbers come from data/serial-formats.json,
 * data/lifespans.json, data/brand-pages.json and data/computed/ao-smith-water-heater-age.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'ao-smith-water-heater-age';
$hwm_c    = 'computed/' . $hwm_file;
$hwm_ls   = 'lifespans';
$hwm_v    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_l    = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_cur  = (array) hwm_raw( $hwm_c, 'examples.current' );
$hwm_old  = (array) hwm_raw( $hwm_c, 'examples.old' );
$hwm_chk  = (array) hwm_raw( 'brand-pages', 'brands.ao-smith.checks' );
$hwm_cp18 = (string) ( $hwm_chk[0]['sourceUrl'] ?? '' );
$hwm_cp08 = (string) ( $hwm_chk[2]['sourceUrl'] ?? '' );
$hwm_mv   = (array) hwm_raw( $hwm_ls, 'manufacturerViews.ao-smith' );
$hwm_sup  = 'https://www.hotwater.com/support.html';
$hwm_war  = 'https://www.hotwater.com/support/warranty-verification.html';
$hwm_mo   = (array) hwm_raw( $hwm_c, 'client.months' );
$hwm_s4   = substr( (string) $hwm_cur['serial'], 0, 4 );
$hwm_r18  = (array) hwm_raw( 'brand-pages', 'brands.ao-smith.recalls.cpsc2018' );
$hwm_r08  = (array) hwm_raw( 'brand-pages', 'brands.ao-smith.recalls.cpsc2008' );

ob_start();
hwm_part( 'age-inputs', array( 'file' => $hwm_file, 'serial_hint' => 'The first four characters carry the date. The example is a test serial in the current A. O. Smith format; replace it with yours.' ) );
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'decide what to do with an older tank' ),
	array( 'slug' => 'state-water-heater-age', 'text' => 'State water heater age', 'why' => 'the State brand, also made by A. O. Smith' ),
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'decode any other brand' ),
);
$related = array( 'water-heater-age-calculator', 'state-water-heater-age', 'how-long-does-a-water-heater-last', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> The current A. O. Smith code is confirmed by two U.S. Consumer Product Safety Commission recall notices, which give real serial ranges next to production and sales dates. Older codes come from the Energy Trust of Oregon decoder. Our decoder matched all <?php echo $hwm_v( 'checksMatched' ); // phpcs:ignore ?> test cases below; see <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a> for the full list.</p>

<h2>How do you read an A. O. Smith serial number?</h2>
<p>On an A. O. Smith water heater made in 2008 or later, the first two digits of the serial are the year and the next two are the production week. A serial starting <code><?php echo esc_html( $hwm_s4 ); ?></code> was built in week <?php echo esc_html( (string) $hwm_cur['week'] ); ?> of <?php echo esc_html( (string) $hwm_cur['year'] ); ?>, which falls in <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>. A letter follows the four digits.</p>
<?php
hwm_part(
	'serial-figure',
	array(
		'id'       => 'aos-serial',
		'title'    => 'How an A. O. Smith serial number shows the date',
		'desc'     => 'A gas tank with the data plate next to the gas control valve, and a close-up of the plate. In the serial ' . $hwm_cur['serial'] . ' the first two digits are the year and the next two digits are the production week.',
		'unit'     => 'valve',
		'segments' => array(
			array( 'text' => substr( (string) $hwm_cur['serial'], 0, 2 ), 'label' => 'year: ' . $hwm_cur['year'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 2, 2 ), 'label' => 'week ' . $hwm_cur['week'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 4 ), 'label' => 'plant letter and unit number', 'hl' => false ),
		),
		'caption'  => 'Test serial ' . $hwm_cur['serial'] . ': year ' . $hwm_cur['year'] . ', week ' . $hwm_cur['week'] . ', so the tank was built in ' . $hwm_cur['madeLabel'] . '. CPSC notes the data plate sits next to the gas control valve or thermostat.',
	)
);
?>

<h2>A. O. Smith serial formats by era</h2>
<p>A. O. Smith has used three date layouts. If the first four characters are all digits, you have the current code. If the serial starts with a letter, it is an older unit and the month is a letter.</p>
<?php hwm_part( 'serial-formats-table', array( 'file' => $hwm_file, 'caption' => 'A. O. Smith serial number date codes, every era' ) ); ?>
<p>Month letters on older serials skip I, so A is January, L is November and M is December. The Energy Trust decoder writes the range as "A through N", but with I skipped that would give thirteen letters; the recall serials above confirm L and M, so the calculator treats N as unreadable rather than guess. The decoder rejects a four-digit year-week code that would date before <?php echo esc_html( (string) hwm_raw( 'serial-formats', 'brands.ao-smith.patterns.0.validFromYear' ) ); ?>, because A. O. Smith did not use it yet.</p>

<h2>How do you turn a production week into a month?</h2>
<p>A week number does not map exactly to a month, so the calculator uses the month the week mostly falls in. This chart shows the weeks it assigns to each month; a week on the boundary can belong to either.</p>
<div class="table-wrap" role="region" aria-label="Production week to month" tabindex="0">
	<table>
		<caption>A. O. Smith production week → month (as the calculator reads it)</caption>
		<thead><tr><th scope="col">Month</th><th scope="col">Weeks</th></tr></thead>
		<tbody>
			<?php foreach ( (array) hwm_raw( $hwm_c, 'weekToMonth' ) as $hwm_r ) : ?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_r['month'] ); ?></th><td><?php echo esc_html( (string) $hwm_r['weeks'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Computed with assets/js/formulas.js (month = week × 12 ÷ 52, rounded up). So week <?php echo esc_html( (string) $hwm_r18['lastWeek'] ); ?> of <?php echo esc_html( (string) $hwm_r18['lastYear'] ); ?> reads as July, while the CPSC gives <?php echo esc_html( (string) $hwm_r18['madeTo'] ); ?> as the last production day of its recall range: a boundary week.</p>
</div>

<h2>What the CPSC recalls show about the code</h2>
<p>Recall notices are the clearest public proof of how A. O. Smith dates its serials, because they print serial ranges and dates side by side. In 2018 the CPSC recalled Ultra-Low NOx gas water heaters whose first four serial digits run from <?php echo esc_html( $hwm_r18['firstDigits'] . ' to ' . $hwm_r18['lastDigits'] ); ?> and says those digits "represent the year and week of production" (<?php echo $hwm_ext( $hwm_cp18, 'CPSC notice' ); // phpcs:ignore ?>). The units were made from <?php echo esc_html( $hwm_r18['madeFrom'] . ' through ' . $hwm_r18['madeTo'] ); ?>, which lines up with week <?php echo esc_html( $hwm_r18['firstWeek'] . ' of ' . $hwm_r18['firstYear'] ); ?> to week <?php echo esc_html( $hwm_r18['lastWeek'] . ' of ' . $hwm_r18['lastYear'] ); ?>.</p>
<p>The 2008 recall of A. O. Smith FCG-75 gas water heaters shows the older code in use (<?php echo $hwm_ext( $hwm_cp08, 'CPSC notice' ); // phpcs:ignore ?>). Its serials start with <?php echo esc_html( implode( ' and ', (array) $hwm_r08['prefixes'] ) ); ?>, and the units were sold from <?php echo esc_html( $hwm_r08['soldFrom'] . ' to ' . $hwm_r08['soldTo'] ); ?>. Read as a month letter and a two-digit year, the decoder gives <?php echo esc_html( (string) ( (array) hwm_raw( $hwm_c, 'checks' ) )[2]['ours'] ); ?> for the first range, which fits the sales dates. The notice does not explain the code itself, so treat this layout as well supported rather than official.</p>
<?php hwm_part( 'decoder-checks', array( 'file' => $hwm_file, 'caption' => 'A. O. Smith serials: our decoder against recall notices and the Energy Trust decoder' ) ); ?>
<p>If you want to know whether your own unit is part of a recall, the CPSC notice lists the models and ranges; the serial alone is not enough, because recalls also depend on the model.</p>

<h2>Worked examples: a new and an old A. O. Smith</h2>
<p><strong>A current gas tank.</strong> The serial starts <code><?php echo esc_html( $hwm_s4 ); ?></code>, the water is moderately hard and the tank is flushed every year. That is week <?php echo esc_html( (string) $hwm_cur['week'] ); ?> of <?php echo esc_html( (string) $hwm_cur['year'] ); ?>, so on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?> the tank is <?php echo esc_html( (string) $hwm_cur['age'] ); ?> years old. Against the DOE typical life of <?php echo esc_html( (string) $hwm_cur['lifeTypical'] ); ?> years it has used <?php echo esc_html( (string) $hwm_cur['usedPct'] ); ?>%: "<?php echo esc_html( (string) $hwm_cur['statusLabel'] ); ?>", with <?php echo esc_html( $hwm_cur['remainingMin'] . ' to ' . $hwm_cur['remainingMax'] ); ?> years likely left.</p>
<p><strong>An older gas tank.</strong> The serial is <code><?php echo esc_html( (string) $hwm_old['serial'] ); ?></code>, from the 2008 recall range. The water is hard, the tank was never flushed, but the anode rod was replaced once.</p>
<ol>
	<li><?php echo esc_html( substr( (string) $hwm_old['serial'], 0, 1 ) ); ?> is a month letter (I is skipped) and <?php echo esc_html( substr( (string) $hwm_old['serial'], 1, 2 ) ); ?> is the year: <?php echo esc_html( (string) $hwm_old['madeLabel'] ); ?>.</li>
	<li>Age on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?>: <?php echo esc_html( (string) $hwm_old['age'] ); ?> years.</li>
	<li>Expected life starts at <?php echo esc_html( (string) $hwm_old['baseTypical'] ); ?> years. Hard water <?php echo esc_html( (string) $hwm_old['hardDelta'] ); ?>, no flushing <?php echo esc_html( (string) $hwm_old['flushDelta'] ); ?>, new anode +<?php echo esc_html( (string) $hwm_old['anodeDelta'] ); ?>: <?php echo esc_html( (string) $hwm_old['lifeTypical'] ); ?> years.</li>
	<li><?php echo esc_html( (string) $hwm_old['age'] ); ?> ÷ <?php echo esc_html( (string) $hwm_old['lifeTypical'] ); ?> = <?php echo esc_html( (string) $hwm_old['usedPct'] ); ?>% used: "<?php echo esc_html( (string) $hwm_old['statusLabel'] ); ?>". A tank this far past its expected life is worth replacing on your schedule, before a leak decides for you.</li>
</ol>

<h2>Where is the serial number on an A. O. Smith water heater?</h2>
<p>The model and serial numbers are on the data plate (rating plate) on the outside of the tank. The CPSC's 2018 notice puts that plate next to the gas control valve or thermostat. On newer units the same plate carries a QR code.</p>
<p>A. O. Smith says that scanning the QR code with a phone camera opens a page with the model number, serial number, warranty status and links to the manual (<?php echo $hwm_ext( $hwm_sup, 'A. O. Smith support' ); // phpcs:ignore ?>). If your plate has a QR code, that is the quickest way to confirm what the calculator tells you. You can also type the serial into A. O. Smith's <?php echo $hwm_ext( $hwm_war, 'warranty verification' ); // phpcs:ignore ?> page.</p>

<h2>How long does an A. O. Smith water heater last?</h2>
<p>A. O. Smith's own guide says traditional tank water heaters last <?php echo esc_html( $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] ); ?> years and its tankless units <?php echo esc_html( $hwm_mv['tanklessLow'] . ' to ' . $hwm_mv['tanklessHigh'] ); ?> years with proper care (<?php echo $hwm_ext( (string) $hwm_mv['sourceUrl'], 'A. O. Smith' ); // phpcs:ignore ?>). The U.S. Department of Energy puts the typical storage tank at <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years and a tankless unit at about <?php echo $hwm_l( 'types.gas_tankless.typical' ); // phpcs:ignore ?>, from national housing surveys.</p>
<p>For tankless models A. O. Smith calls hard water "the biggest threat", because minerals build up in the heat exchanger. It lists banging or popping noises, any leak around the unit and a repeating error code as signs the end is near. The calculator uses the DOE figures, adjusted for your water and maintenance; the <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'water heater lifespan guide' ); // phpcs:ignore ?> compares both views.</p>

<h2>Other brands made by A. O. Smith</h2>
<p>The 2018 CPSC notice lists A. O. Smith water heaters sold under the <?php echo esc_html( (string) $hwm_r18['brands'] ); ?> names. They do not all date their serials the same way:</p>
<ul>
	<li><strong>State and Reliance</strong> use the same year-week code since 2008, but a different month-letter layout before. See <?php echo hwm_link( 'state-water-heater-age', 'State water heater age' ); // phpcs:ignore ?>.</li>
	<li><strong>American, Whirlpool and U.S. Craftmaster</strong> start with a two-digit year and week as well; choose American or Whirlpool in the <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?>.</li>
	<li><strong>Kenmore</strong> units came from more than one maker, so check the name of the manufacturer on the plate first.</li>
</ul>

<h2>Mistakes to avoid with A. O. Smith serials</h2>
<ul>
	<li><strong>Reading the week as a month.</strong> In <code><?php echo esc_html( $hwm_s4 ); ?></code>, <?php echo esc_html( substr( $hwm_s4, 2, 2 ) ); ?> is a week, not May. Week numbers run past 12, which is the giveaway.</li>
	<li><strong>Mixing up the old layouts.</strong> On an older serial, check whether the first or second character is the month letter: the year digits come right after it.</li>
	<li><strong>Confusing the model and serial numbers.</strong> The model number names the series and size; only the serial carries the date.</li>
</ul>

<h2>Limitations</h2>
<p>The year-week code gives a week, not a day, so the month we show can be off by one at a month boundary. The older letter layouts come from the Energy Trust of Oregon decoder and from a recall serial range that fits them; A. O. Smith does not publish them. Commercial, boiler and oil-fired products can use other codes. Expected life is a DOE average, and our water and maintenance adjustments are editorial assumptions documented on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I tell how old my A. O. Smith water heater is?',
		'a' => 'Read the first four characters of the serial number on the data plate. On units made since 2008 they are digits: the first two are the year and the next two the week, so ' . $hwm_s4 . ' is week ' . $hwm_cur['week'] . ' of ' . $hwm_cur['year'] . '. Older serials start with a letter and use a month letter.',
	),
	array(
		'q' => 'Does A. O. Smith have a serial number lookup?',
		'a' => 'A. O. Smith offers a warranty verification page where you enter the serial number, and newer rating plates carry a QR code that opens the model, serial and warranty status. Neither shows the age as a single figure, which is what the calculator on this page adds.',
	),
	array(
		'q' => 'What do the first four digits of an A. O. Smith serial number mean?',
		'a' => 'The year and week of production. The U.S. Consumer Product Safety Commission says so in its 2018 recall notice, which covered serials whose first four digits run from ' . $hwm_r18['firstDigits'] . ' to ' . $hwm_r18['lastDigits'] . ': units made from ' . $hwm_r18['madeFrom'] . ' through ' . $hwm_r18['madeTo'] . '.',
	),
	array(
		'q' => 'How long does an A. O. Smith water heater last?',
		'a' => 'A. O. Smith says tank models last ' . $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] . ' years and tankless models ' . $hwm_mv['tanklessLow'] . ' to ' . $hwm_mv['tanklessHigh'] . ' with proper care. DOE survey data puts the typical storage tank at ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years. Hard water and skipped flushing shorten both.',
	),
	array(
		'q' => 'Are State and A. O. Smith serial numbers the same?',
		'a' => 'Since 2008, yes: both use a two-digit year and a two-digit week. Before 2008 the layouts differ, so use the State page for a State unit. The CPSC lists State among the brands A. O. Smith sells.',
	),
	array(
		'q' => 'Is the build date the start of my warranty?',
		'a' => 'Not necessarily. The serial gives the build date, and a unit may be installed months later. Check the warranty status by serial on A. O. Smith\'s warranty page, or scan the QR code on the plate, and keep your install receipt.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'A. O. Smith recalls Ultra-Low NOx water heaters due to fire hazard (2018): serial digits = year and week', 'url' => $hwm_cp18, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'A. O. Smith recalls gas water heaters due to fire and carbon monoxide hazards (2008)', 'url' => $hwm_cp08, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water heater serial number decoder (PDF)', 'url' => (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long do water heaters last?', 'url' => (string) $hwm_mv['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Support: QR code on the rating plate', 'url' => $hwm_sup, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Warranty verification', 'url' => $hwm_war, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
