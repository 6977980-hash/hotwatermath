<?php
/**
 * /water-heater-maintenance-schedule/ (tools added 2026-11-21): maintenance dates + Heater Health Reminder signup.
 * Math: assets/js/formulas.js maintenanceSchedule via assets/js/maintenance-schedule.js.
 * Data: data/maintenance.json, data/lifespans.json → data/computed/water-heater-maintenance-schedule.json.
 * Reminder emails: parts/signup-form.php + inc/lists.php (same intervals).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-maintenance-schedule';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_def = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_ex  = (array) hwm_raw( $hwm_c, 'figures.example' );
$hwm_t   = array();
foreach ( (array) ( $hwm_cl['tasks'] ?? array() ) as $hwm_x ) {
	$hwm_t[ $hwm_x['id'] ] = $hwm_x;
}
$hwm_refs = (array) hwm_raw( 'maintenance', 'references' );
$hwm_year = (int) gmdate( 'Y' );
$hwm_sel  = static function ( string $name, string $id, array $opts, string $value ): string {
	$h = '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
	foreach ( $opts as $k => $label ) {
		$h .= '<option value="' . esc_attr( (string) $k ) . '"' . selected( (string) $k, $value, false ) . '>' . esc_html( (string) $label ) . '</option>';
	}
	return $h . '</select>';
};
$hwm_types  = array_map( static fn( $x ) => $x['label'], (array) ( $hwm_cl['types'] ?? array() ) );
$hwm_waters = array_map( static fn( $x ) => $x['label'], (array) ( $hwm_cl['water'] ?? array() ) );
$hwm_months = array();
foreach ( (array) ( $hwm_cl['months'] ?? array() ) as $hwm_i => $hwm_m ) {
	$hwm_months[ $hwm_i + 1 ] = $hwm_m;
}

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid field-grid--stack">
	<div class="field">
		<label for="ms-type">Type of water heater</label>
		<?php echo $hwm_sel( 'type', 'ms-type', $hwm_types, (string) ( $hwm_def['type'] ?? '' ) ); // phpcs:ignore -- escaped in the closure. ?>
	</div>
	<div class="field">
		<label for="ms-year">Year installed <span class="hint">Not sure? The <?php echo hwm_link( 'water-heater-age-calculator', 'age calculator' ); // phpcs:ignore ?> reads it from the serial.</span></label>
		<input id="ms-year" name="installYear" type="text" inputmode="numeric" maxlength="4" value="<?php echo esc_attr( (string) ( $hwm_def['installYear'] ?? '' ) ); ?>">
	</div>
	<div class="field">
		<label for="ms-month">Month installed</label>
		<?php echo $hwm_sel( 'installMonth', 'ms-month', $hwm_months, (string) ( $hwm_def['installMonth'] ?? '1' ) ); // phpcs:ignore ?>
	</div>
	<div class="field">
		<label for="ms-water">Your water</label>
		<?php echo $hwm_sel( 'water', 'ms-water', $hwm_waters, (string) ( $hwm_def['water'] ?? 'normal' ) ); // phpcs:ignore ?>
	</div>
	<div class="field">
		<label for="ms-last">Last service year <span class="hint">Optional: when it was last flushed or descaled.</span></label>
		<input id="ms-last" name="lastYear" type="text" inputmode="numeric" maxlength="4" value="<?php echo esc_attr( (string) ( $hwm_def['lastYear'] ?? '' ) ); ?>">
	</div>
	<div class="field">
		<label for="ms-lastm">Last service month</label>
		<?php echo $hwm_sel( 'lastMonth', 'ms-lastm', $hwm_months, (string) ( $hwm_def['lastMonth'] ?? '1' ) ); // phpcs:ignore ?>
	</div>
</div>
<?php
echo hwm_tool_data_script( 'hwm-ms-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'how-to-flush-a-water-heater', 'text' => 'How to flush a water heater', 'why' => 'the steps, from the maker\'s manual' ),
	array( 'slug' => 'water-heater-anode-rod', 'text' => 'Water heater anode rod', 'why' => 'how to check it and when to replace it' ),
	array( 'slug' => 'water-heater-recall-lookup', 'text' => 'Water heater recall lookup', 'why' => 'check the model against every CPSC recall' ),
);
$related = array( 'how-to-flush-a-water-heater', 'water-heater-anode-rod', 'water-heater-age-calculator', 'how-long-does-a-water-heater-last' );
?>
<p class="notice"><strong>Where the dates come from.</strong> Every interval is quoted from a maker's manual or support page, opened on <?php echo esc_html( hwm_date( (string) hwm_raw( 'maintenance', 'meta.sourceDate' ) ) ); ?>: A. O. Smith for tanks, GE for anode rods and Rinnai for tankless units. The replacement date is the low end of the Department of Energy's lifespan range. The quotes are in the table below and on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-maintenance-schedule' ); ?>">our methodology page</a>.</p>

<?php
hwm_part(
	'signup-form',
	array(
		'list'    => 'health',
		'id'      => 'health-reminder',
		'heading' => 'Heater Health Reminder: get an email when each job is due',
		'text'    => 'We use the heater you entered above and email you only when a task comes due, plus one note when it is time to plan a replacement. Free, no ads, one click to stop.',
		'hidden'  => array(
			'type'         => (string) ( $hwm_def['type'] ?? '' ),
			'installYear'  => (string) ( $hwm_def['installYear'] ?? '' ),
			'installMonth' => (string) ( $hwm_def['installMonth'] ?? '' ),
			'water'        => (string) ( $hwm_def['water'] ?? '' ),
		),
	)
);
?>

<h2>How often should a water heater be serviced?</h2>
<p>Once a year for most homes. A. O. Smith's manual says to drain and flush a tank and inspect its anode rod "after first six months of operation and at least annually thereafter", and to operate the relief valve every year. On hard water, A. O. Smith says twice a year. Rinnai says to flush a tankless unit "at least once a year".</p>
<?php echo hwm_table( $hwm_c, 'intervals' ); // phpcs:ignore -- escaped in the part. ?>
<p>The schedule above turns these intervals into dates for your heater. A task that was never done counts from the install date, so an older heater with no service record will show several overdue jobs. That is normal: do them once, enter the date, and the next ones move a year out.</p>

<h2>What does each maintenance job do?</h2>
<dl>
	<dt>Flush (tank)</dt>
	<dd>Drains the sediment that settles on the bottom of the tank. Sediment makes a gas tank rumble, slows heating and, A. O. Smith warns, can "deteriorate the tank and drain valve and cause leaks". Our <?php echo hwm_link( 'how-to-flush-a-water-heater', 'flushing guide' ); // phpcs:ignore ?> has the steps.</dd>
	<dt>Anode rod check (tank)</dt>
	<dd>The anode is a metal rod that corrodes so the steel tank does not. GE says to replace it "when more than 6" (15.2 cm) of core wire is exposed". A new rod is the cheapest way to make a tank last; see our <?php echo hwm_link( 'water-heater-anode-rod', 'anode rod guide' ); // phpcs:ignore ?>.</dd>
	<dt>Relief valve test (tank)</dt>
	<dd>The temperature and pressure (T&amp;P) relief valve lets water out if the tank gets too hot or too pressurized. Lifting its lever once a year, as the label on the valve shows, keeps it from sticking.</dd>
	<dt>Descale (tankless)</dt>
	<dd>Pumps a vinegar or descaling solution through the heat exchanger to dissolve scale. Scale cuts the flow and the temperature and can trip an error code.</dd>
	<dt>Inlet filter (tankless)</dt>
	<dd>A small screen where cold water enters the unit. Rinnai says to clean it right after the flush.</dd>
</dl>

<h2>Is a tank or a tankless heater more work to maintain?</h2>
<p>About the same time, different jobs. A tank needs a flush, an anode check and a valve test each year. A tankless unit has no tank and no anode, but needs a yearly descale, which needs a small pump and about an hour.</p>
<div class="table-wrap" role="region" aria-label="Tank vs tankless maintenance" tabindex="0">
	<table>
		<caption>Yearly maintenance: tank vs tankless</caption>
		<thead><tr><th scope="col">Job</th><th scope="col">Tank (gas, electric, heat pump)</th><th scope="col">Tankless</th></tr></thead>
		<tbody>
			<tr><th scope="row">Remove sediment or scale</th><td>Flush every <?php echo esc_html( (string) ( $hwm_t['flush']['everyMonths'] ?? '' ) ); ?> months (<?php echo esc_html( (string) ( $hwm_t['flush']['hardEveryMonths'] ?? '' ) ); ?> on hard water)</td><td>Descale every <?php echo esc_html( (string) ( $hwm_t['descale']['everyMonths'] ?? '' ) ); ?> months</td></tr>
			<tr><th scope="row">Corrosion protection</th><td>Anode check every <?php echo esc_html( (string) ( $hwm_t['anode']['everyMonths'] ?? '' ) ); ?> months</td><td>None (no tank)</td></tr>
			<tr><th scope="row">Safety valve</th><td>Test the T&amp;P valve every <?php echo esc_html( (string) ( $hwm_t['tp']['everyMonths'] ?? '' ) ); ?> months; pro check every <?php echo esc_html( (string) ( (int) ( $hwm_t['tpPro']['everyMonths'] ?? 0 ) / 12 ) ); ?> years</td><td>Per the manual</td></tr>
			<tr><th scope="row">Filter</th><td>None</td><td>Clean the inlet filter at each descale</td></tr>
			<tr><th scope="row">Plan a replacement</th><td>From <?php echo $hwm_v( 'tankLow' ); // phpcs:ignore ?> years</td><td>From <?php echo $hwm_v( 'tanklessLow' ); // phpcs:ignore ?> years</td></tr>
		</tbody>
	</table>
	<p class="table-source">Source: <?php echo esc_html( (string) hwm_raw( 'maintenance', 'meta.source' ) ); ?>.</p>
</div>

<h2>Worked example: electric tank on hard water</h2>
<?php
$hwm_xi = (array) ( $hwm_ex['input'] ?? array() );
$hwm_xr = (array) ( $hwm_ex['rows'][0] ?? array() );
?>
<p>An electric tank installed in <?php echo esc_html( ( $hwm_months[ (int) ( $hwm_xi['installMonth'] ?? 1 ) ] ?? '' ) . ' ' . ( $hwm_xi['installYear'] ?? '' ) ); ?>, on hard water, last flushed in <?php echo esc_html( ( $hwm_months[ (int) ( $hwm_xi['lastMonth'] ?? 1 ) ] ?? '' ) . ' ' . ( $hwm_xi['lastYear'] ?? '' ) ); ?>. On hard water the flush comes every <?php echo esc_html( (string) ( $hwm_t['flush']['hardEveryMonths'] ?? '' ) ); ?> months, so it was due again in <?php echo esc_html( (string) ( $hwm_xr['due'] ?? '' ) ); ?>. The anode check and valve test come a year after the last service.</p>
<?php echo hwm_table( $hwm_c, 'example' ); // phpcs:ignore ?>
<p>Doing the overdue jobs at one visit resets the dates: flush, pull the anode while the tank is drained, and test the valve before you refill. The five-year professional check still counts from the install date.</p>

<h2>What happens if you never flush a water heater?</h2>
<p>Sediment builds up on the bottom. On a gas tank it insulates the water from the burner, so more heat goes up the flue and the tank pops and rumbles. On an electric tank it can bury the lower element. In time it clogs the drain valve, and A. O. Smith warns that skipping the flush can let the tank and valve deteriorate and leak. A first flush on an old tank can stir sediment into the valve; if it will not drain, A. O. Smith says to call a qualified person.</p>

<h2>How the Heater Health Reminder works</h2>
<p>Sign up above with the heater you entered. You get one email to confirm your address, then a welcome email with your schedule. After that we email only when a task falls due: on average a few emails a year for a tank, fewer for a tankless unit, and one note when the heater reaches <?php echo $hwm_v( 'tankLow' ); // phpcs:ignore ?> years (tank) or <?php echo $hwm_v( 'tanklessLow' ); // phpcs:ignore ?> years (tankless), when it is worth pricing a replacement before it fails.</p>
<p>We store your email address and the four answers above, nothing else, in our own database. We do not share or sell them, the emails carry no ads, and every email has a one-click unsubscribe that deletes your address. Details are in our <a href="<?php echo esc_url( hwm_url( 'privacy-policy' ) . '#email-lists' ); ?>">privacy policy</a>.</p>

<h2>Common mistakes</h2>
<ul>
	<li><strong>Flushing an electric tank and turning the power back on before it is full.</strong> A dry element burns out in seconds. A. O. Smith: "it is critical to make sure the tank is full before turning the power back on."</li>
	<li><strong>Capping a dripping relief valve.</strong> Never. A drip usually means high water pressure or a missing expansion tank; see our <?php echo hwm_link( 'water-heater-pressure-relief-valve-leaking', 'relief valve guide' ); // phpcs:ignore ?>.</li>
	<li><strong>Skipping the anode on softened water.</strong> GE notes that softened water makes the rod "consume more rapidly", so a yearly check matters more, not less.</li>
	<li><strong>Using the tank schedule for a tankless unit.</strong> A tankless heater has no tank to drain; it needs descaling, which is a different job.</li>
</ul>

<h2>When to call a pro</h2>
<p>Call a licensed plumber when the drain valve will not open or will not close, when the anode rod will not come loose, when the relief valve keeps dripping after a test, or for the five-year valve check A. O. Smith suggests. Any work on gas lines, the gas valve or wiring is a job for a licensed professional. Before any job, turn the gas control to "Pilot" or "Off", or switch off the breaker, and let the water cool: it can scald.</p>

<h2>Limitations</h2>
<p>The intervals are the makers' general advice; your own heater's manual wins where it differs, and very hard water may need more frequent service than any schedule shows. We found no openable maker source for a heat pump model's air filter interval, so the schedule leaves it out: check your manual. The replacement date is a planning date from national lifespan data, not a prediction for your heater.</p>
<?php
$faq = array(
	array(
		'q' => 'How often should I flush my water heater?',
		'a' => 'Once a year for a tank, starting six months after it is installed, according to A. O. Smith\'s manual. On hard water A. O. Smith says twice a year. A tankless unit is descaled once a year instead, as Rinnai advises. Enter your heater above for the dates.',
	),
	array(
		'q' => 'How often should the anode rod be checked?',
		'a' => 'Every year. A. O. Smith says to inspect it at the first flush, six months after install, and at least once a year after that, and GE says to replace it when more than 6 inches of core wire shows. Softened water uses it up faster.',
	),
	array(
		'q' => 'How often should I test the water heater relief valve?',
		'a' => 'Once a year, by lifting the lever as shown on the valve\'s label, says A. O. Smith\'s manual. A. O. Smith also suggests a professional inspection every five years. If it keeps dripping after the test, have the water pressure and expansion tank checked.',
	),
	array(
		'q' => 'Does a tankless water heater need maintenance?',
		'a' => 'Yes. Rinnai says to flush the mineral deposits out of a tankless unit at least once a year, and to clean the inlet water filter at the same time. Hard water needs it more often. There is no anode rod or tank to drain.',
	),
	array(
		'q' => 'Are the reminder emails free?',
		'a' => 'Yes. You confirm your email once, then get an email only when a job is due and one note when it is time to plan a replacement. There are no ads in the emails, and every one has a one-click unsubscribe link that deletes your address.',
	),
	array(
		'q' => 'What should I do if my water heater has never been serviced?',
		'a' => 'Do the jobs once now: flush, check the anode and test the relief valve. On an old tank, the drain valve may clog with sediment; if it will not drain, call a plumber rather than forcing it. Then enter the date above and the schedule moves forward.',
	),
);
$sources = array();
foreach ( $hwm_refs as $hwm_r ) {
	$sources[] = array( 'publisher' => $hwm_r['publisher'], 'title' => $hwm_r['title'], 'url' => $hwm_r['sourceUrl'], 'accessed' => $hwm_r['checked'] );
}
$sources[] = array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (lifetime estimates)', 'url' => (string) hwm_raw( 'lifespans', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'lifespans', 'meta.sourceDate' ) );
