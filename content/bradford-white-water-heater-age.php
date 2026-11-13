<?php
/**
 * /bradford-white-water-heater-age/ (STRATEGY §2 item 1c). Numbers come from data/serial-formats.json,
 * data/lifespans.json, data/brand-pages.json and data/computed/bradford-white-water-heater-age.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'bradford-white-water-heater-age';
$hwm_c    = 'computed/' . $hwm_file;
$hwm_ls   = 'lifespans';
$hwm_v    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_l    = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_bw   = 'https://www.bradfordwhite.com/my-water-heater/';
$hwm_faq  = (string) hwm_raw( $hwm_ls, 'manufacturerViews.bradford-white.sourceUrl' );
$hwm_wc   = 'https://warrantycenter.bradfordwhite.com/';
$hwm_cp   = (string) hwm_raw( 'brand-pages', 'brands.bradford-white.checks.3.sourceUrl' );
$hwm_g    = (array) hwm_raw( $hwm_c, 'examples.gas' );
$hwm_y    = (array) hwm_raw( $hwm_c, 'examples.cycle' );
$hwm_cyc  = $hwm_v( 'cycleYears' );
$hwm_mo   = (array) hwm_raw( $hwm_c, 'client.months' );

ob_start();
hwm_part( 'age-inputs', array( 'file' => $hwm_file, 'serial_hint' => 'The first two letters carry the date. The example is a test serial in the Bradford White format; replace it with yours.' ) );
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'decode any other brand' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'weigh a repair against the unit\'s age' ),
	array( 'slug' => 'how-long-does-a-water-heater-last', 'text' => 'How long a water heater lasts', 'why' => 'lifespan by type and what shortens it' ),
);
$related = array( 'water-heater-age-calculator', 'ao-smith-water-heater-age', 'rheem-water-heater-age', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> The letter charts are Bradford White's own (<?php echo $hwm_ext( $hwm_bw, 'Determine the age of my water heater' ); // phpcs:ignore ?>); the earlier letter cycles come from the Energy Trust of Oregon decoder, and one real recall range from the U.S. Consumer Product Safety Commission. Our decoder matched all <?php echo $hwm_v( 'checksMatched' ); // phpcs:ignore ?> test cases below, and every case is on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you read a Bradford White serial number?</h2>
<p>Look at the first two characters of the serial number on the rating plate. The first letter is the year of manufacture and the second letter is the month. Everything after those two letters is a production number and says nothing about the date.</p>
<?php
hwm_part(
	'serial-figure',
	array(
		'id'       => 'bw-serial',
		'title'    => 'How a Bradford White serial number shows the date',
		'desc'     => 'A tank with the rating plate on the upper jacket, and a close-up of the plate. In the serial ' . $hwm_g['serial'] . ' the first letter X is the year and the second letter A is the month; the remaining digits are a production number.',
		'unit'     => 'tank',
		'segments' => array(
			array( 'text' => substr( (string) $hwm_g['serial'], 0, 1 ), 'label' => 'year: ' . $hwm_g['latestYear'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_g['serial'], 1, 1 ), 'label' => 'month: ' . ( $hwm_mo[ (int) $hwm_g['month'] - 1 ] ?? '' ), 'hl' => true ),
			array( 'text' => substr( (string) $hwm_g['serial'], 2 ), 'label' => 'production number', 'hl' => false ),
		),
		'caption'  => 'Test serial ' . $hwm_g['serial'] . ': X is a year letter and A is January, so the tank was built in ' . $hwm_g['madeLabel'] . ' (or ' . implode( ' or ', (array) $hwm_g['otherYears'] ) . ', see below).',
	)
);
?>
<p>Bradford White leaves some letters out so they cannot be mistaken for digits or for each other. Year letters never use I, O, Q, R, U or V. Month letters run from A to M and skip I, so there are exactly twelve.</p>

<h2>Bradford White year letter chart</h2>
<p>Each year letter stands for one year in every <?php echo $hwm_cyc; // phpcs:ignore ?>-year cycle. Bradford White's own chart lists the two most recent cycles; the Energy Trust of Oregon decoder confirms the earlier ones, with A standing for <?php echo esc_html( implode( ', ', array_slice( (array) hwm_raw( $hwm_c, 'cycleStarts' ), 0, 3 ) ) ); ?>.</p>
<div class="table-wrap" role="region" aria-label="Bradford White year letters" tabindex="0">
	<table>
		<caption>Bradford White year letters (first character of the serial)</caption>
		<thead><tr><th scope="col">Letter</th><th scope="col">Possible years</th></tr></thead>
		<tbody>
			<?php foreach ( (array) hwm_raw( $hwm_c, 'yearLetters' ) as $hwm_r ) : ?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_r['letter'] ); ?></th><td><?php echo esc_html( (string) $hwm_r['years'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Sources: <?php echo $hwm_ext( $hwm_bw, 'Bradford White' ); // phpcs:ignore ?> (2004 to 2043), <?php echo $hwm_ext( (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'Energy Trust of Oregon decoder' ); // phpcs:ignore ?> (earlier cycles). Checked <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?>.</p>
</div>

<h2>Bradford White month letter chart</h2>
<div class="table-wrap" role="region" aria-label="Bradford White month letters" tabindex="0">
	<table>
		<caption>Bradford White month letters (second character of the serial)</caption>
		<thead><tr><th scope="col">Letter</th><th scope="col">Month</th><th scope="col">Letter</th><th scope="col">Month</th></tr></thead>
		<tbody>
			<?php
			$hwm_ml = (array) hwm_raw( $hwm_c, 'monthLetters' );
			$hwm_h  = (int) ceil( count( $hwm_ml ) / 2 );
			for ( $hwm_i = 0; $hwm_i < $hwm_h; $hwm_i++ ) :
				$hwm_a = $hwm_ml[ $hwm_i ];
				$hwm_b = $hwm_ml[ $hwm_i + $hwm_h ] ?? null;
				?>
				<tr>
					<th scope="row"><?php echo esc_html( (string) $hwm_a['letter'] ); ?></th><td><?php echo esc_html( (string) $hwm_a['month'] ); ?></td>
					<th scope="row"><?php echo esc_html( (string) ( $hwm_b['letter'] ?? '' ) ); ?></th><td><?php echo esc_html( (string) ( $hwm_b['month'] ?? '' ) ); ?></td>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>
	<p class="table-source">Source: <?php echo $hwm_ext( $hwm_bw, 'Bradford White, Determine the age of my water heater' ); // phpcs:ignore ?>.</p>
</div>

<h2>Which year is right when the letter repeats?</h2>
<p>Pick the most recent year that is not in the future unless the unit clearly looks older. A storage tank that reached <?php echo $hwm_cyc; // phpcs:ignore ?> years would be well past the <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?>-year typical life, so the latest year is usually right, but not always.</p>
<p>A real case shows why the calculator lists the other years. In 2002 the U.S. Consumer Product Safety Commission announced a recall of 75-gallon power-vented Bradford White gas water heaters with serials from "YD" through "YE" (<?php echo $hwm_ext( $hwm_cp, 'CPSC recall notice' ); // phpcs:ignore ?>). Those units were sold in April and May 2002. Typed into a decoder today, <code><?php echo esc_html( (string) $hwm_y['serial'] ); ?></code> reads as <?php echo esc_html( $hwm_mo[ (int) $hwm_y['month'] - 1 ] ?? '' ); ?> <?php echo esc_html( (string) $hwm_y['latestYear'] ); ?>, because Y is also the letter for <?php echo esc_html( (string) $hwm_y['latestYear'] ); ?>. The right answer is the earlier cycle, <?php echo esc_html( (string) $hwm_y['madeLabel'] ); ?>, which makes that tank <?php echo esc_html( (string) $hwm_y['age'] ); ?> years old, not a few years.</p>
<p>To settle it on your own unit, use clues the letter cannot give you:</p>
<ul>
	<li><strong>Other dates on the labels.</strong> Rating plates may name the edition year of the safety standard the unit was certified to. A tank cannot be older than the standard it was tested against.</li>
	<li><strong>The house records.</strong> A permit sticker, an installer's tag or a receipt gives the install date, which comes after the build date.</li>
	<li><strong>The look of the unit.</strong> Rust streaks, an old-style gas control and a faded label point to the earlier cycle.</li>
	<li><strong>Bradford White's warranty check.</strong> Entering the serial in the <?php echo $hwm_ext( $hwm_wc, 'Bradford White Warranty Center' ); // phpcs:ignore ?> shows the warranty length and expiration date, which pins down the decade.</li>
</ul>

<h2>Worked example: a Bradford White gas tank</h2>
<p>Take a gas tank whose serial starts with <code><?php echo esc_html( substr( (string) $hwm_g['serial'], 0, 2 ) ); ?></code>, in a home with moderately hard water, flushed every year.</p>
<ol>
	<li><strong>Year letter:</strong> X stands for <?php echo esc_html( (string) $hwm_g['latestYear'] ); ?> in the current cycle (or <?php echo esc_html( implode( ' or ', (array) $hwm_g['otherYears'] ) ); ?>). A gas tank in daily use is not <?php echo esc_html( (string) ( ( (array) $hwm_g['otherYears'] )[0] ?? '' ) ); ?>-era, so <?php echo esc_html( (string) $hwm_g['latestYear'] ); ?> it is.</li>
	<li><strong>Month letter:</strong> A is the first month letter, so the build date is <?php echo esc_html( (string) $hwm_g['madeLabel'] ); ?>.</li>
	<li><strong>Age:</strong> on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?> the tank is <?php echo esc_html( (string) $hwm_g['age'] ); ?> years old.</li>
	<li><strong>Expected life:</strong> DOE data puts a gas storage tank at <?php echo esc_html( (string) $hwm_g['baseTypical'] ); ?> years typical (<?php echo esc_html( $hwm_g['baseLow'] . ' to ' . $hwm_g['baseHigh'] ); ?>). Moderate water and yearly flushing change nothing.</li>
	<li><strong>Result:</strong> <?php echo esc_html( (string) $hwm_g['age'] ); ?> ÷ <?php echo esc_html( (string) $hwm_g['lifeTypical'] ); ?> = <?php echo esc_html( (string) $hwm_g['usedPct'] ); ?>% of expected life used, the "<?php echo esc_html( (string) $hwm_g['statusLabel'] ); ?>" band, with about <?php echo esc_html( $hwm_g['remainingMin'] . ' to ' . $hwm_g['remainingMax'] ); ?> years left.</li>
</ol>
<p>The 2002 recall tank from the section above, on hard water and never flushed, tells the other story: its life drops to <?php echo esc_html( (string) $hwm_y['lifeTypical'] ); ?> years and it has used <?php echo esc_html( (string) $hwm_y['usedPct'] ); ?>% of it. Read with the wrong cycle, the same serial would look almost new.</p>

<h2>We tested the decoder against Bradford White's chart</h2>
<p>Each serial below was decoded by the same code the calculator runs and compared with what Bradford White's chart or the CPSC notice says for those letters.</p>
<?php hwm_part( 'decoder-checks', array( 'file' => $hwm_file, 'caption' => 'Bradford White serials: our decoder against the published dates' ) ); ?>

<h2>Where is the serial number on a Bradford White water heater?</h2>
<p>The model and serial numbers are on the rating plate, a printed label on the outer jacket of the tank. Bradford White's own guide points you to that plate first ("locate your rating plate on your water heater"), then to the model and serial lines on it. The serial is the line marked "Serial No."; the model number above it names the product line and size.</p>
<p>If the label is faded, shine a flashlight across it at a low angle. Do not remove access covers or touch the gas control to look for a second label: everything you need is on the outside.</p>

<h2>How long does a Bradford White water heater last?</h2>
<p>Bradford White says its water heaters "can last <?php echo $hwm_l( 'manufacturerViews.bradford-white.tankLow' ); // phpcs:ignore ?> years or more" with a professional installation and proper maintenance (<?php echo $hwm_ext( $hwm_faq, 'Bradford White FAQ' ); // phpcs:ignore ?>). Department of Energy data puts the typical storage tank at <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years, with most lasting <?php echo $hwm_l( 'types.gas_tank.low' ); // phpcs:ignore ?> to <?php echo $hwm_l( 'types.gas_tank.high' ); // phpcs:ignore ?>.</p>
<p>Bradford White also names what shortens that life: harsh water and natural gas quality. On the warranty side, its FAQ says the warranty covers the glass-lined tank and component parts for leakage or malfunction, and that it passes to later owners as long as the heater stays at its original installation site in the USA. The length differs by model, so check yours by serial in the Warranty Center rather than assuming a number.</p>
<p>For a full breakdown by type, see <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?>.</p>

<h2>Jetglas and other Bradford White names</h2>
<p>Bradford White has also sold tanks under the Jetglas name, and the Energy Trust of Oregon decoder lists the same letter code for both. If your label says Jetglas, choose Bradford White in the calculator.</p>
<p>Some third-party guides report that serials starting with the letter O were made in January 1997. Bradford White's own chart does not list O, and we could not confirm the claim in a manufacturer or government source, so the calculator does not decode it. If your serial starts with O, enter the year from the label or ask Bradford White with the serial.</p>

<h2>Mistakes people make with Bradford White serials</h2>
<ul>
	<li><strong>Taking the oldest year.</strong> Most charts list two years per letter; people sometimes pick the earlier one and conclude a newer tank is twenty years old.</li>
	<li><strong>Reading the letters as digits.</strong> Because I, O, Q, R, U and V are never year letters, a serial that seems to start with 0 or 1 is really a different character, or a different format.</li>
	<li><strong>Using the install date for wear.</strong> A tank can sit in a warehouse for months. The serial gives the build date, which is what counts for corrosion.</li>
	<li><strong>Trusting age alone.</strong> A tank on very hard water that has never been flushed can fail years early, whatever its letters say.</li>
</ul>

<h2>Limitations</h2>
<p>The letter code gives a year and a month, never a day, and it cannot tell one <?php echo $hwm_cyc; // phpcs:ignore ?>-year cycle from the next. Bradford White also makes boilers, oil-fired and commercial products whose serials may follow other rules; this page covers residential water heaters. Expected life is an average from DOE survey data, and the hardness, flushing and anode adjustments are our own assumptions, set out on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I find the age of my Bradford White water heater?',
		'a' => 'Read the first two letters of the serial number on the rating plate. The first letter is the year and the second is the month, so a serial starting with XA was built in January ' . $hwm_g['latestYear'] . ' (or ' . implode( ' or ', (array) $hwm_g['otherYears'] ) . '). Enter the serial in the calculator above to get the age and remaining life.',
	),
	array(
		'q' => 'Does Bradford White have a serial number lookup?',
		'a' => 'Bradford White publishes its year and month letter chart, and its Warranty Center checks a serial for warranty length and expiration. It does not show the build date as a single answer, so use the chart or the calculator on this page, which applies the same chart.',
	),
	array(
		'q' => 'Why does one Bradford White letter mean two different years?',
		'a' => 'The year letters run in a ' . $hwm_cyc . '-year cycle, so each letter returns every ' . $hwm_cyc . ' years. The calculator shows the latest year that is not in the future and lists the others. Dates on the labels, install records or the warranty check tell you which cycle is right.',
	),
	array(
		'q' => 'Where is the serial number on a Bradford White water heater?',
		'a' => 'It is on the rating plate, the label on the outer jacket of the tank, on the line marked "Serial No." The model number sits on the same plate. You do not need to remove any cover to read it.',
	),
	array(
		'q' => 'How long does a Bradford White water heater last?',
		'a' => 'Bradford White says its water heaters can last ' . hwm_value( $hwm_ls, 'manufacturerViews.bradford-white.tankLow' ) . ' years or more with professional installation and proper maintenance. DOE data puts a typical storage tank at ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years. Harsh water and skipped maintenance shorten that.',
	),
	array(
		'q' => 'Is a Jetglas water heater dated the same way?',
		'a' => 'Yes. Jetglas is a Bradford White name, and the Energy Trust of Oregon decoder lists the same code: first letter for the year, second letter for the month. Choose Bradford White in the calculator.',
	),
);
$sources = array(
	array( 'publisher' => 'Bradford White', 'title' => 'Determine the age of my water heater (year and month letter chart)', 'url' => $hwm_bw, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Bradford White', 'title' => 'Frequently asked questions (lifespan, warranty coverage)', 'url' => $hwm_faq, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Bradford White', 'title' => 'Warranty Center (warranty check by serial number)', 'url' => $hwm_wc, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'CPSC, Bradford White Corporation announce recall of gas water heater (2002)', 'url' => $hwm_cp, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water heater serial number decoder (PDF)', 'url' => (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
