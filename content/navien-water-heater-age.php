<?php
/**
 * /navien-water-heater-age/ (STRATEGY §2 item 1c). Navien tankless code from the U.S. CPSC recall
 * notice (data/serial-formats.json → brands.navien). Numbers come from data/brand-pages.json →
 * brands.navien, data/lifespans.json and data/computed/navien-water-heater-age.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'navien-water-heater-age';
$hwm_c    = 'computed/' . $hwm_file;
$hwm_ls   = 'lifespans';
$hwm_v    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_l    = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_bp   = (array) hwm_raw( 'brand-pages', 'brands.navien' );
$hwm_cur  = (array) hwm_raw( $hwm_c, 'examples.current' );
$hwm_old  = (array) hwm_raw( $hwm_c, 'examples.old' );
$hwm_chk  = (array) hwm_raw( $hwm_c, 'checks' );
$hwm_war  = (array) $hwm_bp['warranty'];
$hwm_life = (array) $hwm_bp['life'];
$hwm_lab  = (array) $hwm_bp['labelNote'];
$hwm_cpsc = (string) $hwm_bp['checks'][0]['sourceUrl'];
$hwm_bcs  = (string) $hwm_bp['checks'][3]['sourceUrl'];
$hwm_fwh  = (string) $hwm_bp['checks'][4]['sourceUrl'];
$hwm_mo   = (array) hwm_raw( $hwm_c, 'client.months' );
$hwm_let  = str_split( (string) hwm_raw( 'serial-formats', 'brands.navien.patterns.0.fields.month.letters' ) );
$hwm_from = (string) hwm_raw( 'serial-formats', 'brands.navien.patterns.0.validFromYear' );
$hwm_s    = (string) $hwm_cur['serial'];

ob_start();
hwm_part( 'age-inputs', array( 'file' => $hwm_file, 'serial_hint' => 'Type all 14 to 16 characters; dashes are fine. The example is a test serial in the current Navien layout.' ) );
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'how-long-does-a-water-heater-last', 'text' => 'How long a water heater lasts', 'why' => 'tankless against tank, with DOE figures' ),
	array( 'slug' => 'tankless-water-heater-size-calculator', 'text' => 'Tankless water heater size calculator', 'why' => 'if you are sizing a replacement' ),
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'every other brand' ),
);
$related = array( 'water-heater-age-calculator', 'tankless-water-heater-size-calculator', 'how-long-does-a-water-heater-last', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> The U.S. Consumer Product Safety Commission spelled out the Navien date code in a 2018 recall notice and printed recalled serial ranges beside their build dates. Our decoder gives the same month for every one of them and for the published third-party examples: <?php echo $hwm_v( 'checksMatched' ); // phpcs:ignore ?> of <?php echo esc_html( (string) count( $hwm_chk ) ); ?> cases, listed below and on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you read a Navien serial number?</h2>
<p>Skip the first five characters: four digits for the model and one more character. The next five give the date. Two digits are the year, one character is the month, and two digits are the day. In <code><?php echo esc_html( $hwm_s ); ?></code> that is year <?php echo esc_html( substr( $hwm_s, 5, 2 ) ); ?>, month <?php echo esc_html( substr( $hwm_s, 7, 1 ) ); ?> and day <?php echo esc_html( substr( $hwm_s, 8, 2 ) ); ?>: <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>.</p>
<?php
hwm_part(
	'serial-figure',
	array(
		'id'       => 'navien-serial',
		'title'    => 'How a Navien serial number shows the date',
		'desc'     => 'A wall-hung tankless water heater with the serial label on its right side, and a close-up of the serial ' . $hwm_s . ': a four-digit model code and one character, then the year, the month character and the day, then four more characters.',
		'unit'     => 'wall',
		'segments' => array(
			array( 'text' => substr( $hwm_s, 0, 4 ), 'label' => 'model code', 'hl' => false ),
			array( 'text' => substr( $hwm_s, 4, 1 ), 'label' => '', 'hl' => false ),
			array( 'text' => substr( $hwm_s, 5, 2 ), 'label' => 'year ' . $hwm_cur['year'], 'hl' => true ),
			array( 'text' => substr( $hwm_s, 7, 1 ), 'label' => 'month', 'hl' => true ),
			array( 'text' => substr( $hwm_s, 8, 2 ), 'label' => 'day', 'hl' => true ),
			array( 'text' => substr( $hwm_s, 10 ), 'label' => 'unit number', 'hl' => false ),
		),
		'caption'  => 'Test serial ' . $hwm_s . ': ' . $hwm_cur['madeLabel'] . '. Navien puts the serial on the outside right of the unit, between two barcodes.',
	)
);
?>

<h2>Navien month codes</h2>
<p>A single character holds the month, so the last three months need letters. The CPSC confirms 1 to 9 and X for October. Y and Z for November and December come from a third-party decoder and follow the same sequence.</p>
<div class="table-wrap" role="region" aria-label="Navien month codes" tabindex="0">
	<table>
		<caption>Navien month character</caption>
		<thead><tr><?php foreach ( $hwm_let as $hwm_k ) : ?><th scope="col"><?php echo esc_html( $hwm_k ); ?></th><?php endforeach; ?></tr></thead>
		<tbody><tr><?php foreach ( $hwm_mo as $hwm_m ) : ?><td><?php echo esc_html( substr( (string) $hwm_m, 0, 3 ) ); ?></td><?php endforeach; ?></tr></tbody>
	</table>
	<p class="table-source">Sources: <?php echo $hwm_ext( $hwm_cpsc, 'CPSC recall notice' ); // phpcs:ignore ?> (1 to 9, X); <?php echo $hwm_ext( $hwm_bcs, 'Building Intelligence Center' ); // phpcs:ignore ?> (X, Y, Z = months 10 to 12, third party).</p>
</div>

<h2>Navien serial number formats</h2>
<p>Current units use the layout above. Older units can carry a hyphenated tag with the full date written out, such as <code><?php echo esc_html( (string) $hwm_chk[4]['serial'] ); ?></code>: after the first four digits comes a four-digit year, a two-digit month and a two-digit day. The calculator reads both and ignores the hyphens.</p>
<?php hwm_part( 'serial-formats-table', array( 'file' => $hwm_file, 'caption' => 'Navien serial number date codes' ) ); ?>
<p>The decoder tries the current layout first and only accepts it for years from <?php echo esc_html( $hwm_from ); ?> on. That stops an old tag like the one above from being misread as a 2000s current-style serial.</p>

<h2>What the CPSC recall shows about the code</h2>
<p>In 2018 the CPSC recalled Navien NPE-180A and NPE-180S condensing tankless water heaters and NCB-180E combi-boilers converted to propane. The notice says the units were "manufactured between July 9, 2018 through October 14, 2018", explains that "18709 stands for July 9, 2018" and that "X stands for October", and lists the serial ranges (<?php echo $hwm_ext( $hwm_cpsc, 'CPSC notice' ); // phpcs:ignore ?>).</p>
<p>Look at the ranges and a second pattern appears. Each model's serials start with four digits that also appear in its UPC code: <code>7410</code> for the NPE-180A, <code>7419</code> for the NPE-180S, <code>6608</code> for the NCB-180E. So the first four characters identify the model, which is why the date starts at the sixth. The calculator handles Navien boilers' serials the same way, but its lifespan figures are for water heaters.</p>

<h2>We checked the decoder against real Navien serials</h2>
<?php hwm_part( 'decoder-checks', array( 'file' => $hwm_file, 'caption' => 'Navien serials: our decoder against the CPSC recall and published decoders' ) ); ?>
<p>The day is in the serial too. The calculator shows the month, which is enough for an age; read the day straight from the two digits after the month character.</p>

<h2>Worked examples: a <?php echo esc_html( (string) $hwm_cur['year'] ); ?> unit and a <?php echo esc_html( (string) $hwm_old['year'] ); ?> unit on very hard water</h2>
<p><strong>Tankless, moderately hard water, flushed every year.</strong> The serial gives <?php echo esc_html( (string) $hwm_cur['madeLabel'] ); ?>, so on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?> the unit is <?php echo esc_html( (string) $hwm_cur['age'] ); ?> years old. The calculator uses the DOE figure of <?php echo esc_html( (string) $hwm_cur['lifeTypical'] ); ?> years for tankless units, so it has used <?php echo esc_html( (string) $hwm_cur['usedPct'] ); ?>%: "<?php echo esc_html( (string) $hwm_cur['statusLabel'] ); ?>".</p>
<p><strong>Tankless on very hard water, never descaled, serial <?php echo esc_html( (string) $hwm_old['serial'] ); ?>.</strong></p>
<ol>
	<li>Characters 6 to 10, <code><?php echo esc_html( substr( (string) $hwm_old['serial'], 5, 5 ) ); ?></code>: year <?php echo esc_html( substr( (string) $hwm_old['serial'], 5, 2 ) ); ?>, month <?php echo esc_html( substr( (string) $hwm_old['serial'], 7, 1 ) ); ?>, day <?php echo esc_html( substr( (string) $hwm_old['serial'], 8, 2 ) ); ?>, so <?php echo esc_html( (string) $hwm_old['madeLabel'] ); ?>. Age <?php echo esc_html( (string) $hwm_old['age'] ); ?> years.</li>
	<li>Expected life: <?php echo esc_html( (string) $hwm_old['baseTypical'] ); ?> years, <?php echo esc_html( (string) $hwm_old['hardDelta'] ); ?> for very hard water and <?php echo esc_html( (string) $hwm_old['flushDelta'] ); ?> for no descaling: <?php echo esc_html( (string) $hwm_old['lifeTypical'] ); ?> years.</li>
	<li>Share used: <?php echo esc_html( (string) $hwm_old['usedPct'] ); ?>%, "<?php echo esc_html( (string) $hwm_old['statusLabel'] ); ?>". Scale is the main threat to a tankless heat exchanger, so a unit like this needs descaling now and a replacement plan.</li>
</ol>

<h2>Where is the serial number on a Navien water heater?</h2>
<p>Navien says: "<?php echo esc_html( (string) $hwm_lab['text'] ); ?>" (<?php echo $hwm_ext( (string) $hwm_lab['sourceUrl'], 'Navien warranty page' ); // phpcs:ignore ?>). The CPSC adds that it is also printed on the product packaging, which helps if the unit is boxed in or the label has faded.</p>
<p>Navien has no public age lookup that we found. Its technical support line asks for the serial, and the warranty needs proof of purchase and the installation date, so keep both with the serial.</p>

<h2>How long does a Navien tankless water heater last?</h2>
<p>Navien says its tankless water heaters "typically last <?php echo esc_html( $hwm_life['low'] . ' to ' . $hwm_life['high'] ); ?> years or more with proper installation, good water quality, and routine maintenance" (<?php echo $hwm_ext( (string) $hwm_life['sourceUrl'], 'Navien' ); // phpcs:ignore ?>). DOE uses <?php echo $hwm_l( 'types.gas_tankless.typical' ); // phpcs:ignore ?> years for gas tankless units, and the calculator uses that figure.</p>
<div class="table-wrap" role="region" aria-label="Navien warranty" tabindex="0">
	<table>
		<caption>Navien <?php echo esc_html( (string) $hwm_war['series'] ); ?> warranty terms</caption>
		<thead><tr><th scope="col">Use</th><th scope="col">Heat exchanger</th><th scope="col">Parts</th><th scope="col">Labor</th></tr></thead>
		<tbody>
			<tr><th scope="row">Home, standard or controlled recirculation</th><td><?php echo esc_html( $hwm_war['heatExchanger'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_war['parts'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_war['labor'] . ' year' ); ?></td></tr>
			<tr><th scope="row">Home, uncontrolled recirculation</th><td><?php echo esc_html( $hwm_war['uncontrolledHx'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_war['uncontrolledParts'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_war['labor'] . ' year' ); ?></td></tr>
			<tr><th scope="row">Commercial or space heating</th><td><?php echo esc_html( $hwm_war['commercialHx'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_war['commercialParts'] . ' years' ); ?></td><td><?php echo esc_html( $hwm_war['labor'] . ' year' ); ?></td></tr>
		</tbody>
	</table>
	<p class="table-source">Sources: <?php echo $hwm_ext( (string) $hwm_war['sourceUrl'], 'Navien NPE-A2 warranty' ); // phpcs:ignore ?>; <?php echo $hwm_ext( (string) $hwm_war['faqUrl'], 'Navien tankless FAQ' ); // phpcs:ignore ?> (commercial terms, NPE-2 series).</p>
</div>
<p>Two warranty conditions matter for an older unit. Navien excludes damage from "hard water deposits, lime or mineral build-up", and it voids cover for units fitted by a non-licensed installer or bought online. So the build date tells you whether the heat exchanger is still in its warranty years, but not whether a claim would be accepted.</p>

<h2>Mistakes to avoid with Navien serials</h2>
<ul>
	<li><strong>Reading the first digits as the year.</strong> They are the model code. A guide that reads the year from the start of a Navien serial will give you the wrong date.</li>
	<li><strong>Treating the month character as a number.</strong> X, Y and Z are months 10, 11 and 12, so <code>18X</code> is October 2018, not a typo.</li>
	<li><strong>Confusing the serial with the model.</strong> NPE-240A2 is a model; the serial is the long code between the two barcodes.</li>
</ul>

<h2>Limitations</h2>
<p>The Y and Z months and the older hyphenated layout come from third-party decoders, not Navien or the CPSC. The calculator reports the month, not the day. Expected tankless life is a DOE figure; Navien's own range is wider. Our adjustments for hardness and descaling are editorial assumptions explained on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I find the manufacture date of my Navien tankless water heater?',
		'a' => 'Find the serial on the right side of the unit, between two barcodes. Skip the first five characters; the next two digits are the year, then one month character (X, Y or Z for October to December), then two digits for the day. The calculator above reads it for you.',
	),
	array(
		'q' => 'Is there a Navien serial number lookup?',
		'a' => 'We found no public Navien age lookup. The date is printed in the serial itself, as the CPSC explained in its 2018 recall notice, so you can read it yourself or type it into the decoder on this page.',
	),
	array(
		'q' => 'What does X mean in a Navien serial number?',
		'a' => 'X is October. The month is a single character, so January to September are 1 to 9 and October is X, as the CPSC notice says. A third-party decoder gives Y for November and Z for December.',
	),
	array(
		'q' => 'How long do Navien tankless water heaters last?',
		'a' => 'Navien says ' . $hwm_life['low'] . ' to ' . $hwm_life['high'] . ' years or more with proper installation, good water and routine maintenance. DOE uses ' . hwm_value( $hwm_ls, 'types.gas_tankless.typical' ) . ' years for gas tankless units. Hard water shortens that unless the unit is descaled.',
	),
	array(
		'q' => 'How long is the Navien heat exchanger warranty?',
		'a' => 'On the residential NPE-A2 series it is ' . $hwm_war['heatExchanger'] . ' years with standard or controlled recirculation and ' . $hwm_war['uncontrolledHx'] . ' with uncontrolled recirculation. Parts are covered ' . $hwm_war['parts'] . ' years and labor ' . $hwm_war['labor'] . ' year, per Navien\'s warranty page.',
	),
	array(
		'q' => 'Where is the serial number on a Navien boiler or combi-boiler?',
		'a' => 'The CPSC notice treats Navien combi-boilers the same way: the serial is on a label on the side of the unit and on the packaging, and the date sits in the same place. This calculator\'s lifespan figures are for water heaters only.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Navien recalls tankless water heaters and boilers (2018, recall 19-057): date code and serial ranges', 'url' => $hwm_cpsc, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Navien', 'title' => 'Warranty: where the serial number is', 'url' => (string) $hwm_lab['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Navien', 'title' => 'NPE-A2 series warranty (residential)', 'url' => (string) $hwm_war['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Navien', 'title' => 'Tankless water heater FAQs (commercial warranty terms, technical support)', 'url' => (string) $hwm_war['faqUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Navien', 'title' => 'How long do tankless water heaters last?', 'url' => (string) $hwm_life['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Building Intelligence Center', 'title' => 'Navien water heater and boiler age (serial styles, X/Y/Z months)', 'url' => $hwm_bcs, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'FastWaterHeater', 'title' => 'Water heater age by brand (older Navien format)', 'url' => $hwm_fwh, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
