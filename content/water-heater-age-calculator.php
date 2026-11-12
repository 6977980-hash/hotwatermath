<?php
/**
 * /water-heater-age-calculator/ (STRATEGY §3.1). Every number is read from data/*.json or
 * data/computed/water-heater-age-calculator.json (built by npm run build:data from formulas.js).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c    = 'computed/water-heater-age-calculator';
$hwm_sf   = 'serial-formats';
$hwm_ls   = 'lifespans';
$hwm_v    = static fn( string $path, ?int $dec = null ): string => esc_html( hwm_value( $hwm_c, $path, $dec ) );
$hwm_l    = static fn( string $path, ?int $dec = null ): string => esc_html( hwm_value( $hwm_ls, $path, $dec ) );
$hwm_def  = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_opts = (array) hwm_raw( $hwm_sf, 'brandOptions.options' );
$hwm_hs   = (array) hwm_raw( $hwm_ls, 'adjustments.hardnessScale' );
$hwm_mv   = (array) hwm_raw( $hwm_ls, 'manufacturerViews.rheem' );
$hwm_look = array();
foreach ( (array) hwm_raw( $hwm_sf, 'officialLookups' ) as $hwm_o ) {
	$hwm_look[ $hwm_o['brand'] ] = $hwm_o['url'];
}
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field">
	<label for="age-brand">Brand</label>
	<select id="age-brand" name="brand">
		<?php foreach ( $hwm_opts as $hwm_o ) : ?>
			<option value="<?php echo esc_attr( $hwm_o['id'] ); ?>"<?php selected( $hwm_o['id'], $hwm_def['brand'] ?? '' ); ?>><?php echo esc_html( $hwm_o['label'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="field">
	<label for="age-serial">Serial number <span class="hint">On the rating plate. The example is a test serial from Rheem's own lookup; replace it with yours.</span></label>
	<input id="age-serial" name="serial" type="text" value="<?php echo esc_attr( (string) ( $hwm_def['serial'] ?? '' ) ); ?>" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="24">
</div>
<details class="disclosure">
	<summary>No serial, or brand not decoded? Enter the year</summary>
	<div class="field">
		<label for="age-year">Year it was made <span class="hint">From the rating plate. Used when the serial cannot be decoded.</span></label>
		<input id="age-year" name="madeYear" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off">
	</div>
	<div class="field">
		<label for="age-month">Month (optional)</label>
		<select id="age-month" name="madeMonth">
			<option value="">Not sure</option>
			<?php foreach ( (array) hwm_raw( $hwm_c, 'client.months' ) as $hwm_i => $hwm_m ) : ?>
				<option value="<?php echo esc_attr( (string) ( $hwm_i + 1 ) ); ?>"><?php echo esc_html( $hwm_m ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</details>
<div class="field">
	<label for="age-type">Type of water heater</label>
	<select id="age-type" name="type">
		<?php foreach ( (array) hwm_raw( $hwm_ls, 'toolTypes.options' ) as $hwm_o ) : ?>
			<option value="<?php echo esc_attr( $hwm_o['id'] ); ?>"<?php selected( $hwm_o['id'], $hwm_def['type'] ?? '' ); ?>><?php echo esc_html( $hwm_o['label'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="field">
	<label for="age-hardness">Water hardness <span class="hint">Assumed moderately hard; change if you know yours. <?php echo $hwm_ext( (string) ( $hwm_hs['mapUrl'] ?? '' ), 'USGS hardness map' ); // phpcs:ignore ?></span></label>
	<select id="age-hardness" name="hardness">
		<?php foreach ( array( 'soft', 'moderate', 'hard', 'veryHard' ) as $hwm_k ) : ?>
			<option value="<?php echo esc_attr( $hwm_k ); ?>"<?php selected( $hwm_k, $hwm_def['hardness'] ?? '' ); ?>><?php echo esc_html( $hwm_hs[ $hwm_k ]['label'] . ' (' . $hwm_hs[ $hwm_k ]['mgPerL'] . ' mg/L)' ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<label class="check"><input type="checkbox" name="flushed" value="1"<?php checked( ! empty( $hwm_def['flushed'] ) ); ?>> Tank flushed about once a year</label>
<label class="check"><input type="checkbox" name="anode" value="1"<?php checked( ! empty( $hwm_def['anode'] ) ); ?>> Anode rod has been replaced</label>
<?php
echo hwm_tool_data_script( 'hwm-age-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

/* ---------- what to do next, related ---------- */
$next_steps = array(
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'weigh a repair quote against the unit\'s age' ),
	array( 'slug' => 'water-heater-size-calculator', 'text' => 'Water heater size calculator', 'why' => 'size the replacement before it fails' ),
	array( 'slug' => 'water-heater-cost-calculator', 'text' => 'Water heater cost calculator', 'why' => 'price a new unit in your state' ),
	array( 'slug' => 'how-long-does-a-water-heater-last', 'text' => 'How long a water heater lasts', 'why' => 'lifespan by type and what shortens it' ),
);
$related    = array( 'repair-or-replace-water-heater', 'water-heater-size-calculator', 'tankless-water-heater-size-calculator', 'water-heater-cost-calculator' );

$hwm_ex = (array) hwm_raw( $hwm_c, 'example' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Each brand's date code is taken from a source we opened and checked: the manufacturer, a government recall notice or the Energy Trust of Oregon decoder. The Rheem code was tested against <?php echo $hwm_ext( $hwm_look['Ruud'] ?? '', "Rheem/Ruud's own age lookup" ); // phpcs:ignore ?>, and every test case is on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>. Lifespans come from the U.S. Department of Energy.</p>

<h2>How do you read a water heater serial number?</h2>
<p>Find the serial number on the rating plate and read its first few characters: most brands put the build year and the month or week there. The code differs by brand, so pick your brand in the calculator above, or use the table below to decode it by hand.</p>
<p>There are three common patterns. Rheem and Ruud print a factory letter, then a two-digit production week and a two-digit year. A. O. Smith, State and American print a two-digit year followed by a two-digit week. Bradford White uses two letters: the first is the year and the second is the month. Navien puts a two-digit year, a one-character month and a two-digit day after the model code.</p>
<?php
$hwm_rows = (array) hwm_raw( $hwm_c, 'summaryRows' );
?>
<div class="table-wrap" role="region" aria-label="Water heater serial number date codes by brand" tabindex="0">
	<table>
		<caption>Water heater serial number date codes by brand</caption>
		<thead><tr><th scope="col">Brand</th><th scope="col">How the date is written</th><th scope="col">Example</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_rows as $hwm_r ) : ?>
				<tr>
					<th scope="row"><?php echo hwm_link( $hwm_r['id'] . '-water-heater-age', $hwm_r['brand'] ); // phpcs:ignore -- escaped in helper. ?></th>
					<td><?php echo esc_html( $hwm_r['rule'] ); ?><?php echo '' !== $hwm_r['note'] ? '. <em>' . esc_html( $hwm_r['note'] ) . '</em>' : ''; ?></td>
					<td><?php echo esc_html( $hwm_r['example'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Sources: <?php echo $hwm_ext( (string) hwm_raw( $hwm_sf, 'brands.bradford-white.sourceUrl' ), 'Bradford White' ); // phpcs:ignore ?>, <?php echo $hwm_ext( $hwm_look['Ruud'] ?? '', 'Rheem/Ruud lookup' ); // phpcs:ignore ?>, <?php echo $hwm_ext( (string) hwm_raw( $hwm_sf, 'brands.navien.sourceUrl' ), 'U.S. CPSC (Navien)' ); // phpcs:ignore ?>, <?php echo $hwm_ext( (string) hwm_raw( $hwm_sf, 'meta.sourceUrl' ), 'Energy Trust of Oregon decoder' ); // phpcs:ignore ?> (checked <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_sf, 'meta.sourceDate' ) ) ); ?>)</p>
</div>
<p>A star (*) in an example stands for any character. Codes that only give a week are shown as the month that week falls in. When a code gives only a year, the calculator counts from the middle of that year, which keeps the error under six months.</p>

<h2>Where is the serial number on a water heater?</h2>
<p>The serial number is on the rating plate, a printed sticker or metal plate on the side of the unit. On a tank it sits on the upper half of the jacket, often near the gas valve or the upper heating element cover. On a tankless unit it is on the side or front of the cabinet.</p>
<figure class="figure">
	<svg viewBox="0 0 640 300" width="640" height="300" role="img" aria-labelledby="svg-plate-title svg-plate-desc">
		<title id="svg-plate-title">Where to find the rating plate</title>
		<desc id="svg-plate-desc">Three drawings: a gas tank with the rating plate on the side above the gas valve, an electric tank with the plate between the two heating element covers, and a wall-hung tankless unit with the label on the right side of the cabinet.</desc>
		<g fill="none" stroke="#17212e" stroke-width="2">
			<rect x="40" y="40" width="130" height="210" rx="18"/>
			<path d="M95 40v-22h20v22"/>
			<rect x="85" y="200" width="40" height="22" rx="3"/>
			<rect x="250" y="40" width="130" height="210" rx="18"/>
			<path d="M305 40v-22h20v22"/>
			<rect x="270" y="80" width="30" height="22" rx="3"/>
			<rect x="270" y="190" width="30" height="22" rx="3"/>
			<rect x="470" y="50" width="110" height="160" rx="10"/>
			<path d="M500 210v34M525 210v34M550 210v34"/>
		</g>
		<g fill="#e8f1f9" stroke="#0b4a7d" stroke-width="2.5">
			<rect x="110" y="100" width="48" height="60" rx="3"/>
			<rect x="318" y="120" width="48" height="60" rx="3"/>
			<rect x="566" y="80" width="22" height="58" rx="3"/>
		</g>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="15" fill="#17212e" text-anchor="middle">
			<text x="105" y="278">Gas tank</text>
			<text x="315" y="278">Electric tank</text>
			<text x="525" y="278">Tankless</text>
			<text x="134" y="135" font-size="11" fill="#0b4a7d">PLATE</text>
			<text x="342" y="155" font-size="11" fill="#0b4a7d">PLATE</text>
			<text x="105" y="216" font-size="10">gas valve</text>
			<text x="285" y="74" font-size="10">element</text>
			<text x="285" y="230" font-size="10">element</text>
		</g>
	</svg>
	<figcaption>Typical rating plate positions. Look for the line marked "Serial No." or "S/N", not the model number.</figcaption>
</figure>
<p>Do not confuse the serial number with the model number, which names the product line and size. If the plate is painted over or faded, a flashlight held at a low angle often makes the characters readable. Bradford White and Navien show where the plate is on their own sites: <?php echo $hwm_ext( $hwm_look['Bradford White'] ?? '', 'Bradford White rating plate' ); // phpcs:ignore ?> and <?php echo $hwm_ext( $hwm_look['Navien'] ?? '', 'Navien serial label' ); // phpcs:ignore ?>.</p>

<h2>Worked example: a Bradford White electric tank</h2>
<p>Say the serial on an electric tank starts with <code>SF</code>, the water is hard, and the tank has never been flushed. Here is every step the calculator takes.</p>
<ol>
	<li><strong>Year:</strong> S is a year letter. Bradford White skips I, O, Q, R, U and V, so S stands for <?php echo $hwm_v( 'example.year' ); // phpcs:ignore ?>, or a multiple of <?php echo $hwm_v( 'example.cycleYears' ); // phpcs:ignore ?> years earlier (<?php echo esc_html( implode( ' or ', (array) ( $hwm_ex['otherYears'] ?? array() ) ) ); ?>). A tank in this condition is not from <?php echo esc_html( (string) ( $hwm_ex['otherYears'][0] ?? '' ) ); ?>, so the latest year is the right one.</li>
	<li><strong>Month:</strong> F is the sixth month letter, so the build date is <?php echo esc_html( (string) ( $hwm_ex['madeLabel'] ?? '' ) ); ?>.</li>
	<li><strong>Age:</strong> on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_c, 'asOf' ) ) ); ?> the tank is <?php echo $hwm_v( 'example.age' ); // phpcs:ignore ?> years old.</li>
	<li><strong>Expected life:</strong> an electric tank typically lasts <?php echo $hwm_v( 'example.baseTypical' ); // phpcs:ignore ?> years (range <?php echo $hwm_v( 'example.baseLow' ); // phpcs:ignore ?> to <?php echo $hwm_v( 'example.baseHigh' ); // phpcs:ignore ?>). Hard water takes off <?php echo esc_html( ltrim( hwm_value( $hwm_c, 'example.hardDelta' ), '-' ) ); ?> year and no flushing another <?php echo esc_html( ltrim( hwm_value( $hwm_c, 'example.flushDelta' ), '-' ) ); ?>, so the adjusted life is <?php echo $hwm_v( 'example.lifeTypical' ); // phpcs:ignore ?> years (range <?php echo $hwm_v( 'example.lifeLow' ); // phpcs:ignore ?> to <?php echo $hwm_v( 'example.lifeHigh' ); // phpcs:ignore ?>).</li>
	<li><strong>Result:</strong> <?php echo $hwm_v( 'example.age' ); // phpcs:ignore ?> ÷ <?php echo $hwm_v( 'example.lifeTypical' ); // phpcs:ignore ?> = <?php echo $hwm_v( 'example.usedPct' ); // phpcs:ignore ?>% of expected life used, which is the "<?php echo $hwm_v( 'example.statusLabel' ); // phpcs:ignore ?>" band. Remaining life is <?php echo $hwm_v( 'example.remainingMax' ); // phpcs:ignore ?> years at most, and it could fail sooner.</li>
</ol>
<p>The same tank with soft water and a yearly flush would show the unadjusted <?php echo $hwm_v( 'example.baseTypical' ); // phpcs:ignore ?>-year life. That gap is the reason the calculator asks about your water and maintenance.</p>

<h2>How long does a water heater last?</h2>
<p>A storage tank water heater typically lasts <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years, with most failing between <?php echo $hwm_l( 'types.gas_tank.low' ); // phpcs:ignore ?> and <?php echo $hwm_l( 'types.gas_tank.high' ); // phpcs:ignore ?>. DOE's estimate for a tankless water heater is about <?php echo $hwm_l( 'types.gas_tankless.typical' ); // phpcs:ignore ?> years. Our guide on <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?> covers each type in depth.</p>
<p>These figures come from the U.S. Department of Energy's 2024 water heater efficiency rules, which estimate lifetimes from national housing surveys. Manufacturers quote similar numbers: Rheem says tank units typically last <?php echo esc_html( $hwm_mv['tankLow'] . ' to ' . $hwm_mv['tankHigh'] ); ?> years and tankless units <?php echo esc_html( $hwm_mv['tanklessLow'] . ' to ' . $hwm_mv['tanklessHigh'] ); ?> years or more with care. The calculator uses the DOE figures because they are measured across millions of homes rather than one brand's experience.</p>
<?php echo hwm_table( 'computed/reference-tables', 'lifespans' ); // phpcs:ignore -- escaped in the part. ?>

<h2>What changes the estimate?</h2>
<p>Hard water, skipped maintenance and the anode rod change how long a tank lasts. The calculator moves the expected life by a set number of years for each answer, and these adjustments are our own assumptions, not measured data. You can see them in "How we calculated this" and change every input.</p>
<dl>
	<dt>Water hardness</dt>
	<dd>Minerals in hard water settle on the tank floor and coat heating elements. We take off <?php echo esc_html( ltrim( hwm_value( $hwm_ls, 'adjustments.hardness.hard' ), '-' ) ); ?> year for hard water (<?php echo esc_html( (string) ( $hwm_hs['hard']['mgPerL'] ?? '' ) ); ?> mg/L) and <?php echo esc_html( ltrim( hwm_value( $hwm_ls, 'adjustments.hardness.veryHard' ), '-' ) ); ?> for very hard water (<?php echo esc_html( (string) ( $hwm_hs['veryHard']['mgPerL'] ?? '' ) ); ?> mg/L), using the U.S. Geological Survey scale.</dd>
	<dt>Yearly flushing</dt>
	<dd>Draining sediment once a year keeps it from building up into the layer that makes a tank rumble. Never flushing takes off <?php echo esc_html( ltrim( hwm_value( $hwm_ls, 'adjustments.noAnnualFlush' ), '-' ) ); ?> year.</dd>
	<dt>Anode rod</dt>
	<dd>The anode rod corrodes so the steel tank does not. A rod that has been replaced adds <?php echo $hwm_l( 'adjustments.anodeReplaced' ); // phpcs:ignore ?> year.</dd>
	<dt>Status bands</dt>
	<dd>Green means under <?php echo $hwm_v( 'bandsPct.amberFrom' ); // phpcs:ignore ?>% of expected life is used, amber from <?php echo $hwm_v( 'bandsPct.amberFrom' ); // phpcs:ignore ?>%, and red from <?php echo $hwm_v( 'bandsPct.redFrom' ); // phpcs:ignore ?>%. Expected life never drops below <?php echo $hwm_l( 'adjustments.minimumLifeYears' ); // phpcs:ignore ?> years, however many adjustments apply.</dd>
</dl>

<h2>What if the serial number does not decode?</h2>
<p>If the calculator cannot read a date, check the characters first, then enter the year printed on the label, or ask the manufacturer. Letters O and I are easy to misread as the digits 0 and 1, and a missing first character shifts the whole code.</p>
<ul>
	<li><strong>Rheem and Ruud:</strong> use the official lookups from <?php echo $hwm_ext( $hwm_look['Rheem'] ?? '', 'Rheem' ); // phpcs:ignore ?> or <?php echo $hwm_ext( $hwm_look['Ruud'] ?? '', 'Ruud' ); // phpcs:ignore ?>. They read the maker's own records, which beats any decoder.</li>
	<li><strong>Rinnai, Noritz, Takagi and Bosch:</strong> we do not decode these yet. Guides online disagree about their codes, and we could not open a manufacturer or government source that confirms one. Many tankless labels print the manufacture date in plain words; if not, the manufacturer can tell you from the serial.</li>
	<li><strong>No label at all:</strong> look for a date on the installation manual, a permit sticker or a dated service tag. These give the install date, which is close enough to judge the remaining life.</li>
</ul>

<h2>Common mistakes when dating a water heater</h2>
<ul>
	<li><strong>Reading the model number.</strong> The model number is the product line and size; only the serial carries the date.</li>
	<li><strong>Treating the build date as the install date.</strong> A unit can sit in a warehouse or store for months. The serial tells you when it was made, which is the age that matters for wear, but a warranty usually runs from the install or purchase date.</li>
	<li><strong>Taking the first year a letter code gives.</strong> Bradford White letters repeat every <?php echo $hwm_v( 'example.cycleYears' ); // phpcs:ignore ?> years. A tank rarely survives that many extra years, so the latest year is almost always right.</li>
	<li><strong>Judging by age alone.</strong> An older tank that was flushed and had its anode replaced can outlast a younger one on very hard water. The age is a planning signal, not a verdict.</li>
</ul>

<h2>Signs a water heater is failing</h2>
<p>Rheem lists three signs that a tank should be replaced: <?php echo esc_html( implode( '; ', (array) ( $hwm_mv['signs'] ?? array() ) ) ); ?>. Rusty water can also come from old galvanized pipes, so check whether only the hot side is affected before you blame the tank.</p>
<p>A leak from the tank body cannot be repaired. A leak from a fitting, the drain valve or the relief valve's pipe often can. If you see water around the base, our guide on whether to <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace the water heater' ); // phpcs:ignore ?> helps you weigh the choice.</p>

<h2>When is this a job for a licensed professional?</h2>
<p>Reading the rating plate is safe to do yourself: look, do not touch the gas valve, burner or electrical covers. Call a licensed plumber when the tank leaks from its body, the relief valve keeps discharging, you smell gas, or the unit is near the end of its expected life and you want it replaced before it fails. If you smell gas, leave the house and call your gas utility from outside.</p>

<h2>Limitations of this estimate</h2>
<p>We decode only the brands whose date code we could confirm in a source we opened. Codes can change between factories and years, so a result that looks wrong probably is: trust the label and the manufacturer over the decoder. Expected life is an average across many homes; a single unit can fail early or run years longer. The hardness, flushing and anode adjustments are our editorial assumptions, documented on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-age-calculator' ); ?>">our methodology page</a>.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I tell the age of my water heater from the serial number?',
		'a' => 'Read the first few characters of the serial on the rating plate. Rheem and Ruud use a factory letter, then the production week and year; A. O. Smith, State and American start with the year and week; Bradford White uses a year letter and a month letter. Enter the serial in the calculator and it decodes the date for you.',
	),
	array(
		'q' => 'Where is the serial number on a water heater?',
		'a' => 'It is on the rating plate, a sticker or metal plate on the side of the tank, usually on the upper half near the gas valve or the upper element cover. On a tankless unit it is on the side or front of the cabinet. Look for "Serial No." or "S/N", not the model number.',
	),
	array(
		'q' => 'What if my brand is not listed?',
		'a' => 'Choose "Other or not sure" and enter the year printed on the label, or the install date from a manual or permit. For Rinnai, Noritz, Takagi and Bosch we have not found an official source for the date code, so we do not guess. The manufacturer can confirm the date from the serial number.',
	),
	array(
		'q' => 'How long does a water heater last?',
		'a' => 'DOE data puts a typical storage tank at about ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years, with most lasting ' . hwm_value( $hwm_ls, 'types.gas_tank.low' ) . ' to ' . hwm_value( $hwm_ls, 'types.gas_tank.high' ) . ', and a tankless unit at about ' . hwm_value( $hwm_ls, 'types.gas_tankless.typical' ) . '. Hard water and skipped flushing shorten that. See ' . hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ) . ' for each type.',
	),
	array(
		'q' => 'What are the signs a water heater is failing?',
		'a' => 'Rheem names water leaking from the base of the tank, rusty or discolored hot water, and persistent rumbling or popping from hardened sediment. Running out of hot water sooner than before is another common sign. A leak from the tank body means replacement.',
	),
	array(
		'q' => 'Is the manufacture date the same as the install date?',
		'a' => 'No. The serial gives the date the unit was built. It may have been installed weeks or months later. Use the build date to judge wear, and the install or purchase date for warranty claims.',
	),
	array(
		'q' => 'Why does a Bradford White serial give more than one possible year?',
		'a' => 'Bradford White reuses its year letters every ' . hwm_value( $hwm_c, 'example.cycleYears' ) . ' years, so A can stand for ' . implode( ', ', array_reverse( (array) hwm_raw( $hwm_c, 'example.cycleStarts' ) ) ) . '. The calculator picks the latest year that is not in the future and lists the others. The look of the unit and other dates on its labels settle which is right.',
	),
);
$sources = array(
	array( 'publisher' => 'Ruud (Rheem Manufacturing)', 'title' => 'How old is my water heater? (official age calculator)', 'url' => $hwm_look['Ruud'] ?? '', 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'How old is my equipment? (official age lookup)', 'url' => $hwm_look['Rheem'] ?? '', 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Bradford White', 'title' => 'Determine the age of my water heater', 'url' => $hwm_look['Bradford White'] ?? '', 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Navien recalls tankless water heaters and boilers (2019): serial number date code', 'url' => (string) hwm_raw( $hwm_sf, 'brands.navien.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water heater serial number decoder (PDF)', 'url' => (string) hwm_raw( $hwm_sf, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Gas-fired Instantaneous Water Heaters (89 FR, December 26, 2024)', 'url' => (string) hwm_raw( $hwm_ls, 'evidence.3.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace', 'url' => (string) ( $hwm_mv['sourceUrl'] ?? '' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Geological Survey', 'title' => 'Hardness of water', 'url' => (string) ( $hwm_hs['sourceUrl'] ?? '' ), 'accessed' => '2026-11-13' ),
);
