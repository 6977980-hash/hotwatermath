<?php
/**
 * /water-heater-model-number-lookup/ (tools added 2026-11-20): model number and label decoder.
 * Math: assets/js/formulas.js decodeModel, decodeSerial, ageEstimate, warrantyEnd via assets/js/model-lookup.js.
 * Numbers: data/computed/water-heater-model-number-lookup.json, data/model-formats.json, data/serial-formats.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-model-number-lookup';
$hwm_mf  = 'model-formats';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_def = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_look = array();
foreach ( (array) hwm_raw( 'serial-formats', 'officialLookups' ) as $hwm_o ) {
	$hwm_look[ $hwm_o['brand'] ] = $hwm_o['url'];
}
$hwm_bw   = (array) hwm_raw( $hwm_mf, 'brands.bradford-white.patterns.0' );
$hwm_wp   = (array) hwm_raw( $hwm_mf, 'brands.whirlpool.patterns.0' );
$hwm_ws   = (array) hwm_raw( $hwm_mf, 'warrantyStart' );
$hwm_ex   = (array) hwm_raw( $hwm_c, 'figures.example' );
$hwm_rnd  = (array) hwm_raw( $hwm_c, 'figures.rheemRounded' );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid field-grid--stack">
	<div class="field">
		<label for="ml-brand">Brand</label>
		<select id="ml-brand" name="brand">
			<?php foreach ( (array) ( $hwm_cl['brandOptions'] ?? array() ) as $hwm_o ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_o['id'] ); ?>"<?php selected( (string) $hwm_o['id'], (string) ( $hwm_def['brand'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_o['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="ml-model">Model number <span class="hint">After "Model" or "M/N" on the label. The example is a Rheem model; replace it.</span></label>
		<input id="ml-model" name="model" type="text" value="<?php echo esc_attr( (string) ( $hwm_def['model'] ?? '' ) ); ?>" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="30">
	</div>
	<div class="field">
		<label for="ml-serial">Serial number <span class="hint">After "Serial" or "S/N". The example is a test serial from Rheem's own lookup.</span></label>
		<input id="ml-serial" name="serial" type="text" value="<?php echo esc_attr( (string) ( $hwm_def['serial'] ?? '' ) ); ?>" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="24">
	</div>
	<div class="field">
		<label for="ml-type">Type of water heater <span class="hint">Read from the model number when we can.</span></label>
		<select id="ml-type" name="type">
			<option value="auto"<?php selected( 'auto', (string) ( $hwm_def['type'] ?? '' ) ); ?>>From the model number</option>
			<?php foreach ( (array) ( $hwm_cl['types'] ?? array() ) as $hwm_t ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_t['id'] ); ?>"><?php echo esc_html( (string) $hwm_t['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
<details class="disclosure">
	<summary>Have a photo of the label? Open it here</summary>
	<div class="field">
		<label for="ml-photo">Photo of the rating label <span class="hint">It stays on your device; nothing is uploaded.</span></label>
		<input id="ml-photo" type="file" accept="image/*" data-photo-input>
	</div>
	<p class="hint" data-photo-note>Some browsers can read the text in the photo and fill in the numbers for you; on others, read them from the photo and type them above.</p>
	<img data-photo-preview alt="Your photo of the water heater rating label" width="640" height="480" hidden style="max-width:100%;height:auto">
</details>
<?php
echo hwm_tool_data_script( 'hwm-ml-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'add hard water and upkeep to the life estimate' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'weigh a repair against its age and warranty' ),
	array( 'slug' => 'water-heater-quote-checker', 'text' => 'Water heater quote checker', 'why' => 'check a replacement quote for the same size' ),
);
$related = array( 'water-heater-age-calculator', 'rheem-water-heater-age', 'bradford-white-water-heater-age', 'how-long-does-a-water-heater-last' );
?>
<p class="notice"><strong>How this lookup is checked.</strong> We decode only the parts of a model number that the maker's own documents state, or that hold for every listing we have. The Rheem rule was run against all <?php echo $hwm_v( 'rheemCount' ); // phpcs:ignore ?> Rheem tank listings in our price data and matched every one. The Bradford White and Whirlpool rules come from their spec sheet and warranty. Every test is on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-model-number-lookup' ); ?>">our methodology page</a>.</p>

<h2>How do you read a water heater model number?</h2>
<p>Split it into blocks of letters and digits. On most tank models, one two-digit block is the size in gallons, a letter or two names the fuel, and on Rheem, Bradford White, Whirlpool and American models another block is the tank warranty in years. The order differs by brand, so the lookup above applies the right rule for the brand you pick.</p>
<p>Here is the default example, Rheem <?php echo esc_html( (string) ( $hwm_ex['model'] ?? '' ) ); ?>, read block by block:</p>
<ol>
	<?php foreach ( (array) ( $hwm_ex['lines'] ?? array() ) as $hwm_line ) : ?>
		<li><?php echo esc_html( (string) $hwm_line ); ?></li>
	<?php endforeach; ?>
</ol>
<p>The rest of the number, the series letters and the revision at the end, tells the maker which version it is. You do not need it to know the size or warranty.</p>
<?php echo hwm_table( $hwm_c, 'rules' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Model number</dt>
	<dd>Names the product: every heater of that model has the same number. It tells you the size, fuel and often the warranty.</dd>
	<dt>Serial number</dt>
	<dd>Names this one unit. For most brands its first characters are the build date, which is how you find the age.</dd>
	<dt>Rating plate</dt>
	<dd>The label on the side of the heater, near the controls, that carries both numbers, the capacity, the input rating and the gas type or voltage.</dd>
</dl>

<h2>What does each line on a water heater label mean?</h2>
<p>The rating plate lists the model and serial number, the capacity in gallons, the burner input in BTU per hour (gas) or the element watts and voltage (electric), the gas type and the working pressure. The model number is the shortcut: it repeats the size and fuel in code, and adds the warranty.</p>
<?php echo hwm_table( $hwm_c, 'labelFields' ); // phpcs:ignore ?>
<p>If the model number and the label disagree, trust the label. Bradford White's spec sheet, for example, lists the <?php echo esc_html( (string) ( $hwm_bw['specRows'][3]['model'] ?? '' ) ); ?> at <?php echo $hwm_v( 'bwLowboy' ); // phpcs:ignore ?> gallons although its number says 50: the number is a size class, and the lowboy tank is shorter and holds a little less.</p>

<h2>How do I find my water heater's warranty from the model number?</h2>
<p>On several brands the warranty years are written into the model number. In Rheem's XG and XE models it is the two digits after the height letter (06, 09, 10 or 12). Bradford White's spec sheet says that for 10-year models you "change suffix from “6” to “10”". Whirlpool's warranty prints a code table: 6, 9 or 12 after the first letters.</p>
<?php echo hwm_table( $hwm_c, 'rheemExamples' ); // phpcs:ignore ?>
<p>The warranty runs from installation, not from the build date. Whirlpool's warranty, for one, covers a tank that "leaks within the warranty period listed on the data plate label after the original installation". The lookup counts from the build date because that is what the serial number shows, so your real end date is usually a few months later. Keep the receipt: it is the proof of the install date.</p>
<?php if ( $hwm_rnd ) : ?>
<p>One quirk: the input digits are rounded. Rheem's <?php echo esc_html( (string) $hwm_rnd[0]['model'] ); ?> reads <?php echo esc_html( (string) $hwm_rnd[0]['input'] ); ?>, while its product name says <?php echo esc_html( (string) $hwm_rnd[0]['named'] ); ?> BTU. Use the label's input line when you size a gas line or vent.</p>
<?php endif; ?>

<h2>Where is the model and serial number on a water heater?</h2>
<p>On a tank water heater, look for a metal or foil label on the side of the jacket, usually just above the gas valve or the upper electrical cover. On a tankless unit it is on the side or the front of the cabinet. Bradford White shows where its plate sits on <?php echo $hwm_ext( $hwm_look['Bradford White'] ?? '', 'its own page' ); // phpcs:ignore ?>, and Navien does the same on <?php echo $hwm_ext( $hwm_look['Navien'] ?? '', 'its warranty page' ); // phpcs:ignore ?>.</p>
<p>A label that is hard to read through grime or at a bad angle is easier to read from a photo. Take one with the flash off, zoom in, and open it in the lookup above so you can copy the numbers. The photo never leaves your device.</p>

<h2>Worked example: is it still under warranty?</h2>
<p>Take the default numbers: a Rheem with model <?php echo esc_html( (string) ( $hwm_def['model'] ?? '' ) ); ?> and serial <?php echo esc_html( (string) ( $hwm_def['serial'] ?? '' ) ); ?>.</p>
<ol>
	<li>The model number gives a 50-gallon, tall, natural gas tank with a 6-year tank warranty.</li>
	<li>The serial number's Rheem date code gives the build date: <?php echo esc_html( (string) ( hwm_raw( $hwm_c, 'default.table.rows.1.value' ) ?? '' ) ); ?>.</li>
	<li>Build date plus 6 years: the tank warranty ended about <?php echo esc_html( (string) ( hwm_raw( $hwm_c, 'default.table.rows.7.value' ) ?? '' ) ); ?>.</li>
	<li>At <?php echo esc_html( (string) ( hwm_raw( $hwm_c, 'default.table.rows.2.value' ) ?? '' ) ); ?>, it has used part of the typical life for a gas tank; see the result above for the share.</li>
</ol>
<p>If a heater like this leaks now, the tank warranty is unlikely to pay for a new one. Our <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace calculator' ); // phpcs:ignore ?> weighs the repair against the age.</p>

<h2>Common mistakes</h2>
<ul>
	<li><strong>Reading the model number for the age.</strong> The build date is in the serial number. Two heaters of the same model can be ten years apart.</li>
	<li><strong>Mixing up O and 0, I and 1.</strong> Many codes use only some letters; Bradford White leaves out I, O, Q, R, U and V in its year letters. If a number will not decode, check those characters first.</li>
	<li><strong>Taking the size class as the exact volume.</strong> A 50 in the model number can be a 48-gallon lowboy. The Capacity line is exact.</li>
	<li><strong>Counting the warranty from the build date and giving up.</strong> It runs from installation, and labor coverage is often shorter than the tank's. Ask the maker with the serial number.</li>
</ul>

<h2>When to call a pro</h2>
<p>Decoding a label is safe and needs no tools. If the label is missing, burnt or behind a gas valve you would have to move, leave it and ask a licensed plumber, who can also confirm the gas type and venting before any replacement. For a warranty claim, call the maker first: Whirlpool's warranty, for one, asks you to contact its support line and pays a labor allowance only through the service provider it refers.</p>

<h2>Limitations</h2>
<p>We decode model numbers only for the brands and lines in the table above, because no source we could open states the key for A. O. Smith, State, Rinnai, Navien, Noritz or Takagi. For those, the serial number still gives the build date where its code is known, and the label gives the rest. Rheem models outside the XG and XE series, and Ruud-branded numbers, are not covered by our rule; Rheem and Ruud run <?php echo $hwm_ext( $hwm_look['Ruud'] ?? '', 'their own age lookup' ); // phpcs:ignore ?>, which beats any decoder.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I find out what size my water heater is?',
		'a' => 'Read the Capacity line on the rating label, in gallons. The model number repeats it: on Rheem XG and XE models the two digits after XG or XE are the gallons, and on Bradford White RG models the two digits after the series digit are the size class.',
	),
	array(
		'q' => 'How do I know if my water heater is still under warranty?',
		'a' => 'Find the warranty years in the model number or on the label, then the build date in the serial number. Add the years to the date. The warranty runs from installation, so if you have the receipt, count from that day instead. The lookup above does the math.',
	),
	array(
		'q' => 'What does the 06 in a Rheem model number mean?',
		'a' => 'It is the tank warranty in years. In all ' . hwm_value( $hwm_c, 'figures.rheemWithYears' ) . ' Rheem XG and XE listings in our price data that name a warranty, the two digits after the height letter match it: 06 is a 6-year model, 09 a 9-year, 12 a 12-year.',
	),
	array(
		'q' => 'Can I find the age of a water heater from the model number?',
		'a' => 'No. The model number is the same for every unit of that model. The build date is in the serial number; most brands put the year and the month or week in its first characters. Enter both numbers above to get the age.',
	),
	array(
		'q' => 'Is it safe to upload a photo of my water heater label?',
		'a' => 'On this page nothing is uploaded. The photo opens in your own browser so you can read it; where the browser has a built-in text reader, it suggests the model and serial. Close the page and the photo is gone.',
	),
	array(
		'q' => 'Why does my model number not decode?',
		'a' => 'Either the brand is one we have no sourced key for, or the number is from an older series with a different layout. Check O against 0 and I against 1, then read the size and fuel from the label, and the age from the serial number.',
	),
);
$sources = array(
	array( 'publisher' => 'Bradford White', 'title' => 'Residential Atmospheric Vent Gas Water Heater specification sheet (C1101-B-0618)', 'url' => (string) ( $hwm_bw['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_bw['checked'] ?? '' ) ),
	array( 'publisher' => 'American Water Heater Company', 'title' => 'Whirlpool brand residential water heater limited warranty (form 100236126)', 'url' => (string) ( $hwm_wp['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_wp['checked'] ?? '' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Rheem water heater listings (product names with gallons, height, input and warranty)', 'url' => (string) hwm_raw( $hwm_mf, 'brands.rheem.patterns.0.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_mf, 'brands.rheem.patterns.0.checked' ) ),
	array( 'publisher' => 'American Water Heaters', 'title' => 'Product support pages (model titles with 6- or 10-year warranty)', 'url' => (string) hwm_raw( $hwm_mf, 'brands.american.patterns.0.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_mf, 'brands.american.patterns.0.checked' ) ),
	array( 'publisher' => 'Energy Trust of Oregon', 'title' => 'Water Heater Serial Number Decoder', 'url' => (string) hwm_raw( 'serial-formats', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'serial-formats', 'meta.sourceDate' ) ),
	array( 'publisher' => 'Ruud', 'title' => 'How old is my water heater? (official lookup)', 'url' => (string) ( $hwm_look['Ruud'] ?? '' ), 'accessed' => (string) hwm_raw( 'serial-formats', 'meta.sourceDate' ) ),
);
