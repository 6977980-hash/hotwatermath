<?php
/**
 * /rheem-water-heater-age/ (STRATEGY §2 item 1c; covers Ruud, which shares the code).
 * Numbers come from data/serial-formats.json, data/lifespans.json, data/brand-pages.json
 * and data/computed/rheem-water-heater-age.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'rheem-water-heater-age';
$hwm_c    = 'computed/' . $hwm_file;
$hwm_ls   = 'lifespans';
$hwm_v    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_l    = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_cur  = (array) hwm_raw( $hwm_c, 'examples.current' );
$hwm_old  = (array) hwm_raw( $hwm_c, 'examples.old' );
$hwm_mv   = (array) hwm_raw( $hwm_ls, 'manufacturerViews.rheem' );
$hwm_look = array();
foreach ( (array) hwm_raw( 'serial-formats', 'officialLookups' ) as $hwm_o ) {
	$hwm_look[ $hwm_o['brand'] ] = $hwm_o['url'];
}
$hwm_ia    = (string) hwm_raw( 'serial-formats', 'brands.rheem.patterns.0.sourceUrl' );
$hwm_miss  = (array) hwm_raw( $hwm_c, 'lookupMisses' );
$hwm_alias = (array) hwm_raw( $hwm_c, 'alsoSoldAs' );
$hwm_s5    = substr( (string) $hwm_cur['serial'], 0, 5 );
$hwm_chk   = (array) hwm_raw( $hwm_c, 'checks' );

ob_start();
hwm_part( 'age-inputs', array( 'file' => $hwm_file, 'serial_hint' => 'Type it without spaces. The example is a test serial that Rheem\'s own lookup dates to the same month.' ) );
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'how-long-does-a-water-heater-last', 'text' => 'How long a water heater lasts', 'why' => 'what DOE data and Rheem say' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'Rheem\'s 50% rule, with your numbers' ),
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'every other brand' ),
);
$related = array( 'water-heater-age-calculator', 'bradford-white-water-heater-age', 'how-long-does-a-water-heater-last', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> We ran test serials through <?php echo $hwm_ext( $hwm_look['Ruud'] ?? '', "Rheem/Ruud's official age lookup" ); // phpcs:ignore ?> and wrote down the dates it returned; the decoder on this page gives the same month for every one. For older tags the official lookup does not read, the layout comes from the Energy Trust of Oregon decoder and InspectApedia, and each table row names its source. All <?php echo $hwm_v( 'checksMatched' ); // phpcs:ignore ?> cases are listed below and on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you read a Rheem serial number?</h2>
<p>On a current Rheem or Ruud water heater the serial starts with a plant letter, then a two-digit production week, then a two-digit year. In <code><?php echo esc_html( $hwm_s5 ); ?>…</code> the week is <?php echo esc_html( (string) $hwm_cur['week'] ); ?> and the year is <?php echo esc_html( (string) $hwm_cur['year'] ); ?>, so the unit was built in <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>. The rest is a unit number.</p>
<?php
hwm_part(
	'serial-figure',
	array(
		'id'       => 'rheem-serial',
		'title'    => 'How a Rheem serial number shows the date',
		'desc'     => 'A tank with the rating plate on the upper jacket, and a close-up of the plate. In the serial ' . $hwm_cur['serial'] . ' the first letter is the plant, the next two digits are the production week and the two after that are the year.',
		'unit'     => 'tank',
		'segments' => array(
			array( 'text' => substr( (string) $hwm_cur['serial'], 0, 1 ), 'label' => 'plant', 'hl' => false ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 1, 2 ), 'label' => 'week ' . $hwm_cur['week'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 3, 2 ), 'label' => 'year: ' . $hwm_cur['year'], 'hl' => true ),
			array( 'text' => substr( (string) $hwm_cur['serial'], 5 ), 'label' => 'unit number', 'hl' => false ),
		),
		'caption'  => 'Test serial ' . $hwm_cur['serial'] . ': week ' . $hwm_cur['week'] . ' of ' . $hwm_cur['year'] . '. The official Rheem/Ruud lookup returns a date inside that week, ' . ( (array) hwm_raw( $hwm_c, 'checks' ) )[0]['published'] . '.',
	)
);
?>
<p>Older Rheem tags put the month before the year instead. An all-digit serial such as <code><?php echo esc_html( substr( (string) $hwm_chk[4]['serial'], 0, 4 ) ); ?>…</code> is <?php echo esc_html( (string) $hwm_chk[4]['ours'] ); ?>, and tags with a letter prefix, printed like "RH 0806B…", give the month and year right after the letters. The table below lists every layout the calculator reads and the order it tries them in.</p>

<h2>Rheem and Ruud serial number formats</h2>
<?php hwm_part( 'serial-formats-table', array( 'file' => $hwm_file, 'caption' => 'Rheem and Ruud serial number date codes' ) ); ?>
<p>The two older-tag rows come from a third-party decoder, not from Rheem, so a result from them is a good estimate rather than a confirmed date. When the official lookup reads your serial, trust it over any decoder, including ours.</p>

<h2>Our decoder against Rheem's official lookup</h2>
<p>Rheem and Ruud run an official age lookup that reads their own production records. We tested it with made-up serials in each format on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?>. For current-style serials it returned an exact day, and our decoder landed in the same month every time.</p>
<?php hwm_part( 'decoder-checks', array( 'file' => $hwm_file, 'caption' => 'Rheem serials: our decoder against the official lookup and published examples' ) ); ?>
<p>The lookup is not complete, though. For <?php echo esc_html( implode( ', ', $hwm_miss ) ); ?> it answered that the serial "isn't coming up" and pointed to customer service. That includes the older prefixed tags and even one serial in the current shape, which suggests the lookup only covers some plants or years. In those cases the calculator still gives an estimate from the layout, and Rheem customer service can confirm it from the full serial.</p>

<h2>Worked examples: a <?php echo esc_html( (string) $hwm_cur['year'] ); ?> electric tank and a <?php echo esc_html( (string) $hwm_old['year'] ); ?> gas tank</h2>
<p><strong>Electric tank, hard water, never flushed.</strong> Serial <code><?php echo esc_html( (string) $hwm_cur['serial'] ); ?></code> is week <?php echo esc_html( (string) $hwm_cur['week'] ); ?> of <?php echo esc_html( (string) $hwm_cur['year'] ); ?>: <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>, so <?php echo esc_html( (string) $hwm_cur['age'] ); ?> years old on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?>. The DOE typical life of <?php echo esc_html( (string) $hwm_cur['baseTypical'] ); ?> years loses <?php echo esc_html( ltrim( (string) $hwm_cur['hardDelta'], '-' ) ); ?> for hard water and <?php echo esc_html( ltrim( (string) $hwm_cur['flushDelta'], '-' ) ); ?> for no flushing, leaving <?php echo esc_html( (string) $hwm_cur['lifeTypical'] ); ?>. That is <?php echo esc_html( (string) $hwm_cur['usedPct'] ); ?>% used, "<?php echo esc_html( (string) $hwm_cur['statusLabel'] ); ?>", with <?php echo esc_html( $hwm_cur['remainingMin'] . ' to ' . $hwm_cur['remainingMax'] ); ?> years left. A yearly flush from now on is the cheapest way to move it back toward the full <?php echo esc_html( (string) $hwm_cur['baseTypical'] ); ?>.</p>
<p><strong>Gas tank with an old prefixed tag.</strong> The tag reads <code><?php echo esc_html( (string) $hwm_old['serial'] ); ?></code>. After the letters, <?php echo esc_html( substr( (string) $hwm_old['serial'], 2, 2 ) ); ?> is the month and <?php echo esc_html( substr( (string) $hwm_old['serial'], 4, 2 ) ); ?> the year: <?php echo esc_html( (string) $hwm_old['madeLabel'] ); ?>, or <?php echo esc_html( (string) $hwm_old['age'] ); ?> years. Even with a replaced anode rod (+<?php echo esc_html( (string) $hwm_old['anodeDelta'] ); ?>, for <?php echo esc_html( (string) $hwm_old['lifeTypical'] ); ?> years), it has used <?php echo esc_html( (string) $hwm_old['usedPct'] ); ?>% of its expected life. It is living on borrowed time, and planning the replacement now avoids an emergency one.</p>

<h2>Which brands share the Rheem code?</h2>
<p>Ruud is part of Rheem and uses the same serial format; its official lookup is the one we tested. The Energy Trust of Oregon decoder also lists these names under Rheem: <?php echo esc_html( implode( ', ', $hwm_alias ) ); ?>. For any of them, choose Rheem / Ruud in the calculator.</p>
<h3>GE water heater age</h3>
<p>GE needs care. <?php echo esc_html( (string) hwm_raw( $hwm_c, 'caveat' ) ? (string) hwm_raw( $hwm_c, 'caveat' ) : 'Older GE water heaters were made by Rheem.' ); ?> The <?php echo hwm_link( 'water-heater-age-calculator', 'main age calculator' ); // phpcs:ignore ?> has a separate GE option that applies the Rheem code with that warning.</p>

<h2>Where is the serial number on a Rheem water heater?</h2>
<p>On a Rheem tank, the serial is on the rating plate, a label on the outer jacket above the gas control or the element covers; look for "Serial No." Some Rheem plates also print a manufacture date outright (InspectApedia shows tags marked "MFG. DATE"), which settles the question without decoding.</p>
<p>For tankless and heat pump models we could not find a Rheem page that says where the label is, and third-party guides disagree, so check the installation manual for your model. Rheem's own lookup pages say only that the serial number is all you need.</p>

<h2>How long does a Rheem water heater last?</h2>
<p>Rheem says its tank water heaters typically last <?php echo esc_html( $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] ); ?> years and tankless units <?php echo esc_html( $hwm_mv['tanklessLow'] . ' to ' . $hwm_mv['tanklessHigh'] ); ?> years or more with proper care (<?php echo $hwm_ext( (string) $hwm_mv['sourceUrl'], 'Rheem' ); // phpcs:ignore ?>). DOE survey data puts the typical storage tank a little higher, at <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years.</p>
<p>Rheem's maintenance advice is specific enough to act on:</p>
<ul>
	<li>Flush the tank once a year to clear sediment.</li>
	<li>Replace the anode rod every <?php echo esc_html( $hwm_mv['anodeEveryYearsLow'] . ' to ' . $hwm_mv['anodeEveryYearsHigh'] ); ?> years.</li>
	<li>Keep water pressure between <?php echo esc_html( $hwm_mv['pressurePsiLow'] . ' and ' . $hwm_mv['pressurePsiHigh'] ); ?> psi.</li>
	<li>If a repair costs more than <?php echo esc_html( (string) $hwm_mv['repairShareOfNewPct'] ); ?>% of a new unit, replacing is almost always the better buy.</li>
</ul>
<p>Rheem also names the signs that a tank is done: <?php echo esc_html( implode( '; ', (array) $hwm_mv['signs'] ) ); ?>.</p>

<h2>Mistakes to avoid with Rheem serials</h2>
<ul>
	<li><strong>Reading the week as a month.</strong> In a current serial the two digits after the plant letter can be anything up to 53. A value over 12 is a week, never a month.</li>
	<li><strong>Dropping the prefix letters.</strong> On older tags the letters in front tell you which layout applies. Type the whole serial, spaces removed.</li>
	<li><strong>Giving up when the lookup fails.</strong> A "not coming up" answer does not mean the serial is wrong; the layout still dates it.</li>
</ul>

<h2>Limitations</h2>
<p>Week-based serials give a week, so the month can be off by one at a boundary. The prefixed older layouts come from a third-party decoder, and the official lookup does not confirm them. Rheem tankless and commercial units may use other codes. Expected life is a DOE average across many homes; our hardness, flushing and anode adjustments are editorial assumptions explained on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I find the age of my Rheem water heater?',
		'a' => 'Read the serial on the rating plate. Current serials start with a letter, then two digits for the week and two for the year, so ' . $hwm_s5 . ' is week ' . $hwm_cur['week'] . ' of ' . $hwm_cur['year'] . '. Older all-digit serials give the month, then the year. The calculator above handles both.',
	),
	array(
		'q' => 'Is there an official Rheem serial number lookup?',
		'a' => 'Yes. Rheem and Ruud both run an official age lookup: you type the serial and it returns the manufacture date. In our tests it read current serials but not some older ones, which is where this page\'s decoder helps.',
	),
	array(
		'q' => 'Do Ruud water heaters use the same serial format as Rheem?',
		'a' => 'Yes. Ruud is a Rheem brand, the two share an official lookup, and the date sits in the same place: plant letter, production week, then year on current units.',
	),
	array(
		'q' => 'How long do Rheem water heaters last?',
		'a' => 'Rheem says ' . $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] . ' years for tank models and ' . $hwm_mv['tanklessLow'] . ' to ' . $hwm_mv['tanklessHigh'] . ' or more for tankless models with care. DOE data puts the typical storage tank at ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years. Flushing yearly and replacing the anode rod every ' . $hwm_mv['anodeEveryYearsLow'] . ' to ' . $hwm_mv['anodeEveryYearsHigh'] . ' years help it last.',
	),
	array(
		'q' => 'Why does the Rheem lookup say my serial is not coming up?',
		'a' => 'The lookup does not cover every serial. In our tests it returned no date for older prefixed tags and for one serial in the current format. Check the characters, try the decoder on this page, and contact Rheem customer service with the full serial for a confirmed date.',
	),
	array(
		'q' => 'Are GE water heaters dated like Rheem?',
		'a' => 'Older GE water heaters were made by Rheem and use the Rheem code. Newer GE units may come from another maker, so if the decoded date looks wrong, use the year printed on the label.',
	),
);
$sources = array(
	array( 'publisher' => 'Ruud (Rheem Manufacturing)', 'title' => 'How old is my water heater? (official age calculator)', 'url' => $hwm_look['Ruud'] ?? '', 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'How old is my equipment? (official age lookup)', 'url' => $hwm_look['Rheem'] ?? '', 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace (March 25, 2026)', 'url' => (string) $hwm_mv['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water heater serial number decoder (PDF)', 'url' => (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'InspectApedia', 'title' => 'Rheem water heater age decoder (older tag layouts)', 'url' => $hwm_ia, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
