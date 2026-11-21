<?php
/**
 * /water-heater-recall-lookup/ (tools added 2026-11-21): brand + model + serial against the CPSC water heater recalls.
 * Math: assets/js/formulas.js recallCheck via assets/js/recall-lookup.js. Data: data/recalls.json →
 * data/computed/water-heater-recall-lookup.json. Alerts: parts/signup-form.php + inc/lists.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-recall-lookup';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_def = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_rec = static function ( string $id ): array {
	foreach ( (array) hwm_raw( 'recalls', 'recalls' ) as $r ) {
		if ( $id === (string) $r['id'] ) {
			return $r;
		}
	}
	return array();
};
$hwm_rheem = $hwm_rec( '16177' );
$hwm_uln   = $hwm_rec( '19029' );
$hwm_r110  = $hwm_rec( '05274' );
$hwm_nav   = $hwm_rec( '19057' );
$hwm_giant = $hwm_rec( '22016' );
$hwm_pv    = $hwm_rec( '07108' );
$hwm_lowes = $hwm_rec( '19030' );
$hwm_yr    = static fn( array $r ): string => substr( (string) ( $r['date'] ?? '' ), 0, 4 );
$hwm_ulnr  = (array) ( $hwm_uln['serialRules'][0] ?? array() );
$hwm_haz   = array();
foreach ( (array) hwm_raw( $hwm_c, 'figures.hazards' ) as $hwm_h ) {
	$hwm_haz[ $hwm_h['label'] ] = (int) $hwm_h['n'];
}

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid field-grid--stack">
	<div class="field">
		<label for="rl-brand">Brand</label>
		<select id="rl-brand" name="brand">
			<option value="">Pick the brand</option>
			<?php foreach ( (array) ( $hwm_cl['brandOptions'] ?? array() ) as $hwm_o ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_o['id'] ); ?>"<?php selected( (string) $hwm_o['id'], (string) ( $hwm_def['brand'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_o['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="rl-model">Model number <span class="hint">After "Model" or "M/N" on the label. The example is a recalled Rheem; replace it.</span></label>
		<input id="rl-model" name="model" type="text" value="<?php echo esc_attr( (string) ( $hwm_def['model'] ?? '' ) ); ?>" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="30">
	</div>
	<div class="field">
		<label for="rl-serial">Serial number <span class="hint">Optional, but many recalls cover only some build dates.</span></label>
		<input id="rl-serial" name="serial" type="text" value="<?php echo esc_attr( (string) ( $hwm_def['serial'] ?? '' ) ); ?>" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="24">
	</div>
	<div class="field">
		<label for="rl-made">Year made <span class="hint">Optional. Helps with recalls of gas valves and brand-wide recalls.</span></label>
		<input id="rl-made" name="madeYear" type="text" inputmode="numeric" value="" maxlength="4" placeholder="e.g. 2015">
	</div>
</div>
<?php
echo hwm_tool_data_script( 'hwm-rl-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'find the year made from the serial number' ),
	array( 'slug' => 'water-heater-model-number-lookup', 'text' => 'Water heater model number lookup', 'why' => 'read the size, fuel and warranty from the label' ),
	array( 'slug' => 'water-heater-maintenance-schedule', 'text' => 'Water heater maintenance schedule', 'why' => 'get reminders for the checks that keep it safe' ),
);
$related = array( 'water-heater-age-calculator', 'water-heater-model-number-lookup', 'repair-or-replace-water-heater', 'water-heater-maintenance-schedule' );
?>
<p class="notice"><strong>Where this list comes from.</strong> We read the U.S. Consumer Product Safety Commission's own recall database on <?php echo $hwm_v( 'read' ); // phpcs:ignore ?> and kept every residential water heater recall from <?php echo $hwm_v( 'since' ); // phpcs:ignore ?> on: <?php echo $hwm_v( 'count' ); // phpcs:ignore ?> recalls. The hazard, the fix and the company contact are copied word for word from each CPSC record. Every model listed in a notice was run through the lookup and matched its own recall (<?php echo $hwm_v( 'selfChecks' ); // phpcs:ignore ?> checks). How the match works is on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-recall-lookup' ); ?>">our methodology page</a>.</p>

<?php
hwm_part(
	'signup-form',
	array(
		'list'    => 'recall',
		'id'      => 'recall-alerts',
		'heading' => 'Get an email when a new water heater recall is announced',
		'text'    => 'We check the CPSC list every day. Pick your brand (or leave it on "any") and we email you only when a new recall names it. No ads, no sharing, one click to stop.',
		'fields'  => (function () use ( $hwm_cl, $hwm_def ) {
			ob_start();
			?>
			<div class="field">
				<label for="ra-brand">Brand to watch</label>
				<select id="ra-brand" name="brand">
					<option value="">Any brand</option>
					<?php foreach ( (array) ( $hwm_cl['brandOptions'] ?? array() ) as $hwm_o ) : ?>
						<option value="<?php echo esc_attr( (string) $hwm_o['id'] ); ?>"<?php selected( (string) $hwm_o['id'], (string) ( $hwm_def['brand'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_o['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php
			return (string) ob_get_clean();
		})(),
	)
);
?>

<h2>How do I know if my water heater has been recalled?</h2>
<p>Read the model and serial number from the rating label on the side of the heater, then compare them with the recall notices. The lookup above does that against every CPSC water heater recall since <?php echo $hwm_v( 'since' ); // phpcs:ignore ?>. A match or a possible match means: call the company in the notice, which can confirm it from your serial number in minutes.</p>
<p>Most notices name exact model numbers. Many also limit the recall to a span of build dates, which sit in the serial number, so a model can be recalled while your unit of it is not. That is why the serial field matters. A few notices name no model at all: they cover a part, such as a gas control valve, fitted to heaters of several brands, or every heater of one brand made in a given span.</p>
<dl>
	<dt>Recall</dt>
	<dd>A company's offer, announced with the CPSC, to repair, replace or refund a product that can hurt people. For water heaters the fix is almost always a free repair by a technician the company sends.</dd>
	<dt>CPSC</dt>
	<dd>The U.S. Consumer Product Safety Commission, the federal agency that announces consumer product recalls and keeps the public recall list we read.</dd>
	<dt>Remedy</dt>
	<dd>What the company will do for you: inspect, repair, replace the part or the heater. Each notice states it, and the lookup shows it word for word.</dd>
</dl>

<h2>Every water heater recall since <?php echo $hwm_v( 'since' ); // phpcs:ignore ?></h2>
<p>These are the <?php echo $hwm_v( 'count' ); // phpcs:ignore ?> residential water heater recalls in the CPSC list, newest first. The largest by far is the <?php echo $hwm_v( 'biggest.date' ); // phpcs:ignore ?> recall of <?php echo esc_html( lcfirst( (string) hwm_raw( $hwm_c, 'figures.biggest.product' ) ) ); ?>: <?php echo $hwm_v( 'biggest.units' ); // phpcs:ignore ?> units. The newest, from <?php echo $hwm_v( 'latest.date' ); // phpcs:ignore ?>, covers <?php echo esc_html( (string) hwm_raw( $hwm_c, 'figures.latest.product' ) ); ?>.</p>
<?php echo hwm_table( $hwm_c, 'recalls' ); // phpcs:ignore -- escaped in the part. ?>
<p>Boilers, pool and spa heaters, kettles and immersion heaters are left out, and so are recalls before <?php echo $hwm_v( 'since' ); // phpcs:ignore ?>: a heater that old is past every typical lifespan in our <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'water heater lifespan guide' ); // phpcs:ignore ?>.</p>

<h2>Which water heater brands have had recalls?</h2>
<p>A. O. Smith appears most often, mainly because one large recall in <?php echo esc_html( $hwm_yr( $hwm_uln ) ); ?> covered <?php echo esc_html( (string) count( (array) ( $hwm_uln['brands'] ?? array() ) ) ); ?> brand names made in its plants, and because brand names like State, Reliance and American belong to the same company. A recall count says little about quality on its own: a big maker sells far more heaters, and a recall means the problem was found and a fix offered.</p>
<?php echo hwm_table( $hwm_c, 'byBrand' ); // phpcs:ignore ?>
<p>Navien, Rinnai and other tankless makers sell through installers, so their recalls are often handled by the installer who fitted the unit. Rinnai has no water heater recall in this list; its one recent CPSC recall is of boilers, which we leave out.</p>

<h2>What dangers do water heater recalls warn about?</h2>
<p>Carbon monoxide and fire. Of the <?php echo $hwm_v( 'count' ); // phpcs:ignore ?> recalls, <?php echo esc_html( (string) ( $hwm_haz['Carbon monoxide'] ?? '' ) ); ?> name a carbon monoxide hazard, <?php echo esc_html( (string) ( $hwm_haz['Fire or explosion'] ?? '' ) ); ?> fire or explosion and <?php echo esc_html( (string) ( $hwm_haz['Burn or scald'] ?? '' ) ); ?> burns (a recall can name more than one). Most come from the gas side: burners, flue baffles, vent collars and gas valves.</p>
<?php
$hwm_types = array();
foreach ( (array) hwm_raw( $hwm_c, 'figures.byType' ) as $hwm_t ) {
	$hwm_types[] = array( 'type' => $hwm_t['type'], 'n' => $hwm_t['n'] );
}
?>
<div class="table-wrap" role="region" aria-label="Recalls by type of heater" tabindex="0">
	<table>
		<caption>Water heater recalls by type of heater</caption>
		<thead><tr><th scope="col">Type</th><th scope="col" class="num">Recalls</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_types as $hwm_t ) : ?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_t['type'] ); ?></th><td class="num"><?php echo esc_html( (string) $hwm_t['n'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Source: <?php echo esc_html( (string) hwm_raw( 'recalls', 'meta.source' ) ); ?>, read <?php echo $hwm_v( 'read' ); // phpcs:ignore ?>.</p>
</div>
<p>Only one electric tank recall is in the list, and it was of the control panel, not the tank. That fits how the hazards work: an electric heater has no flame and no exhaust, so it cannot make carbon monoxide.</p>

<h2>What should I do if my water heater is recalled?</h2>
<p>Do what the notice says, which is usually to stop using the heater and call the company for a free repair. The CPSC record for <?php echo esc_html( (string) ( $hwm_giant['product'] ?? '' ) ); ?> puts it this way: "<?php echo esc_html( (string) ( $hwm_giant['remedy'] ?? '' ) ); ?>"</p>
<ol>
	<li>Check the match with the company, using the phone number or website in the notice. Have the model and serial ready.</li>
	<li>If the notice says to stop using the heater, turn it off: the gas valve to "Off" or the breaker off, as the heater's manual shows.</li>
	<li>If you smell gas, leave the house and call the gas company from outside. Do not switch lights or appliances on or off.</li>
	<li>Make sure you have a working carbon monoxide alarm. Several notices, including Navien's, ask for one outside sleeping areas while you wait.</li>
	<li>Keep the repair record with the heater's papers. A buyer's home inspector may ask.</li>
</ol>
<p>The repair is free under the recall even if the heater is out of warranty. You do not need the receipt; the serial number is the proof.</p>

<h2>Recalls of gas valves and other parts</h2>
<p>Some recalls are of a part, not a heater model. In <?php echo esc_html( substr( (string) ( $hwm_r110['date'] ?? '' ), 0, 4 ) ); ?>, Robertshaw recalled R110 gas control valves fitted to gas water heaters of many brands; the notice says: "<?php echo esc_html( (string) ( $hwm_r110['hazard'] ?? '' ) ); ?>" Only valves from a few weeks of production were affected, so the date code on the valve matters, not your heater's model.</p>
<p>The lookup flags these part recalls only when your heater's year made fits them, or when your serial falls in the range the notice gives. Otherwise it lists them under "same brand" so you can check the part yourself. If you are not sure which valve your heater has, the heater's maker can tell you from the serial number.</p>

<h2>Worked example: a recalled Rheem electric heater</h2>
<p>Take the default numbers above: a Rheem with model <?php echo esc_html( (string) ( $hwm_def['model'] ?? '' ) ); ?> and a serial starting Q2314.</p>
<ol>
	<li>The model is one of the <?php echo esc_html( (string) count( (array) ( $hwm_rheem['models'] ?? array() ) ) ); ?> in the <?php echo esc_html( (string) ( $hwm_rheem['date'] ?? '' ) ); ?> Rheem recall of <?php echo esc_html( lcfirst( (string) ( $hwm_rheem['product'] ?? '' ) ) ); ?>.</li>
	<li>The notice limits it to serials such as Q0114 to Q5214: a letter, then the week and the two-digit year. Q2314 is week 23 of 2014, inside the range.</li>
	<li>Result: a match. The hazard in the CPSC record: "<?php echo esc_html( (string) ( $hwm_rheem['hazard'] ?? '' ) ); ?>"</li>
	<li>The same model with a serial ending in 17 (built 2017) shows "outside the recalled range": the model is listed, but that unit is not.</li>
</ol>
<?php echo hwm_table( $hwm_c, 'cases' ); // phpcs:ignore ?>

<h2>Common mistakes</h2>
<ul>
	<li><strong>Checking only the brand.</strong> A brand having a recall does not mean your heater is in it. Most recalls cover a few models and a few months of production.</li>
	<li><strong>Typing the serial into the model field.</strong> The model names the product and is shared by thousands of units; the serial is unique. Both are on the label.</li>
	<li><strong>Taking "no match" as "safe".</strong> No match only means no match in the CPSC list. A maker can also run its own safety campaign, and a gas utility can issue its own notices.</li>
	<li><strong>Ignoring a recall on an old heater.</strong> The notice and the free fix are meant for every affected unit, whatever its age. Call the number in the notice.</li>
</ul>

<h2>When to call a pro</h2>
<p>A recall repair is done by the company's technician, so start with the number in the notice. Call your gas utility or a licensed plumber at once if you smell gas, see soot around the burner door, hear the carbon monoxide alarm, or see scorch marks near the heater. Do not try a gas valve or vent repair yourself: the CPSC notices all send the repair to a technician.</p>

<h2>Limitations</h2>
<p>The list covers recalls announced through the CPSC. It does not cover recalls in Canada only, a maker's own service campaigns, or commercial water heaters. Serial ranges are our reading of each notice's text, so treat a "match" or "outside the range" as a strong hint and let the company confirm it. The lookup checks the CPSC list daily for new water heater recalls; we read each new one and add it by hand, so a brand-new recall can take a few days to appear here.</p>
<?php
$faq = array(
	array(
		'q' => 'How do I check if my water heater is recalled?',
		'a' => 'Find the model and serial number on the rating label on the side of the heater. Enter them in the lookup above, which checks every CPSC water heater recall since ' . hwm_value( $hwm_c, 'figures.since' ) . '. If it shows a match, call the company in the notice to confirm and book the free repair.',
	),
	array(
		'q' => 'Has Rheem recalled any water heaters?',
		'a' => 'Yes. In the CPSC list, Rheem recalled Performance Platinum electric water heaters sold at The Home Depot (' . $hwm_yr( $hwm_rheem ) . ') for a control panel that can overheat, and, with Paloma, Ruud and Richmond, indoor power-vent tankless units (' . $hwm_yr( $hwm_pv ) . '). Enter your model above to check.',
	),
	array(
		'q' => 'Is there an A. O. Smith water heater recall?',
		'a' => 'The biggest is the ' . $hwm_yr( $hwm_uln ) . ' recall of Ultra-Low NOx gas water heaters, ' . strtolower( (string) ( $hwm_uln['units'] ?? '' ) ) . ' units sold mainly in California under ' . count( (array) ( $hwm_uln['brands'] ?? array() ) ) . ' brand names, with serials starting ' . ( $hwm_ulnr['from'] ?? '' ) . ' to ' . ( $hwm_ulnr['to'] ?? '' ) . '. A smaller ' . $hwm_yr( $hwm_lowes ) . ' recall covered a 30-gallon model sold at Lowe\'s. The lookup above checks both.',
	),
	array(
		'q' => 'Do water heater recalls expire?',
		'a' => 'The CPSC notices give no end date, and the companies tell owners of affected units to call. If your heater matches an old recall, call the number in the notice; if it has changed, the maker\'s main support line can find the recall from your serial number.',
	),
	array(
		'q' => 'Who pays for a recalled water heater repair?',
		'a' => 'The company that issued the recall. Every notice in our list offers a free repair, inspection or replacement, usually by a technician the company sends. You do not need a receipt or a valid warranty; the model and serial number prove the heater is covered.',
	),
	array(
		'q' => 'Will you email me if my water heater is recalled?',
		'a' => 'Yes, if you sign up above. We read the CPSC list every day, add each new water heater recall, and email people who chose that brand (or any brand). You confirm by email first, and every email has a one-click unsubscribe link.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => 'Recall database (SaferProducts.gov Recall REST web service, all records)', 'url' => (string) hwm_raw( 'recalls', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'recalls', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => (string) ( $hwm_uln['title'] ?? '' ), 'url' => (string) ( $hwm_uln['url'] ?? '' ), 'accessed' => (string) hwm_raw( 'recalls', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => (string) ( $hwm_rheem['title'] ?? '' ), 'url' => (string) ( $hwm_rheem['url'] ?? '' ), 'accessed' => (string) hwm_raw( 'recalls', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => (string) ( $hwm_nav['title'] ?? '' ), 'url' => (string) ( $hwm_nav['url'] ?? '' ), 'accessed' => (string) hwm_raw( 'recalls', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Consumer Product Safety Commission', 'title' => (string) ( $hwm_r110['title'] ?? '' ), 'url' => (string) ( $hwm_r110['url'] ?? '' ), 'accessed' => (string) hwm_raw( 'recalls', 'meta.sourceDate' ) ),
);
