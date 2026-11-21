<?php
/**
 * /water-heater-failure-database/ (added 2026-11-21): crowd-sourced failure reports (form) and, later, the report.
 * Form options: data/failure-database.json → data/computed/water-heater-failure-database.json.
 * Reports are stored by inc/lists.php (table hwm_failures); nothing is shown until publishAt reports are in.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c    = 'computed/water-heater-failure-database';
$hwm_f    = (array) hwm_raw( $hwm_c, 'form' );
$hwm_fig  = (array) hwm_raw( $hwm_c, 'figures' );
$hwm_n    = function_exists( 'hwm_failure_count' ) ? hwm_failure_count() : 0;
$hwm_ls   = 'lifespans';
$hwm_mv   = (array) hwm_raw( $hwm_ls, 'manufacturerViews' );
$hwm_year = (int) gmdate( 'Y' );
$hwm_opts = static function ( array $list, string $empty = '' ): string {
	$h = '' !== $empty ? '<option value="">' . esc_html( $empty ) . '</option>' : '';
	foreach ( $list as $o ) {
		$h .= '<option value="' . esc_attr( (string) $o['id'] ) . '">' . esc_html( (string) $o['label'] ) . '</option>';
	}
	return $h;
};
$hwm_years = static function () use ( $hwm_year, $hwm_fig ): array {
	$out = array();
	for ( $y = $hwm_year; $y >= $hwm_year - (int) ( $hwm_fig['maxAgeYears'] ?? 40 ); $y-- ) {
		$out[] = array( 'id' => $y, 'label' => (string) $y );
	}
	return $out;
};
$related = array( 'how-long-does-a-water-heater-last', 'water-heater-age-calculator', 'repair-or-replace-water-heater', 'water-heater-maintenance-schedule' );
?>
<section class="signup report-form" id="report" aria-labelledby="report-h">
	<h2 id="report-h">Report a water heater that failed</h2>
	<p>Two minutes, no email needed. Only the totals are ever published, never a single report or your note.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-failure="1">
		<input type="hidden" name="action" value="hwm_failure">
		<div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
		<div class="field-grid field-grid--stack">
			<div class="field"><label for="fd-brand">Brand</label><select id="fd-brand" name="brand" required><?php echo $hwm_opts( (array) ( $hwm_f['brands'] ?? array() ), 'Pick the brand' ); // phpcs:ignore -- escaped in the closure. ?></select></div>
			<div class="field"><label for="fd-type">Type</label><select id="fd-type" name="type" required><?php echo $hwm_opts( (array) ( $hwm_f['types'] ?? array() ), 'Pick the type' ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-iy">Year installed <span class="hint">The <?php echo hwm_link( 'water-heater-age-calculator', 'age calculator' ); // phpcs:ignore ?> reads it from the serial.</span></label><select id="fd-iy" name="installYear" required><?php echo $hwm_opts( $hwm_years(), 'Pick a year' ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-fy">Year it failed</label><select id="fd-fy" name="failYear" required><?php echo $hwm_opts( $hwm_years() ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-cause">What failed</label><select id="fd-cause" name="cause" required><?php echo $hwm_opts( (array) ( $hwm_f['causes'] ?? array() ), 'Pick one' ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-out">What you did <span class="hint">Optional</span></label><select id="fd-out" name="outcome"><?php echo $hwm_opts( (array) ( $hwm_f['outcomes'] ?? array() ), 'Skip' ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-size">Size in gallons <span class="hint">Optional; tanks only</span></label><input id="fd-size" name="size" type="text" inputmode="numeric" maxlength="3"></div>
			<div class="field"><label for="fd-water">Your water</label><select id="fd-water" name="water"><?php echo $hwm_opts( (array) ( $hwm_f['water'] ?? array() ) ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-flush">Was it flushed or descaled most years?</label><select id="fd-flush" name="flushed"><?php echo $hwm_opts( array_reverse( (array) ( $hwm_f['yesNo'] ?? array() ) ) ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-anode">Was the anode rod ever replaced?</label><select id="fd-anode" name="anode"><?php echo $hwm_opts( array_reverse( (array) ( $hwm_f['yesNo'] ?? array() ) ) ); // phpcs:ignore ?></select></div>
			<div class="field"><label for="fd-state">State <span class="hint">Optional</span></label><select id="fd-state" name="state"><?php echo $hwm_opts( (array) ( $hwm_f['states'] ?? array() ), 'Skip' ); // phpcs:ignore ?></select></div>
		</div>
		<div class="field"><label for="fd-note">Anything else? <span class="hint">Optional, up to <?php echo esc_html( (string) ( $hwm_fig['noteMaxChars'] ?? '' ) ); ?> characters. Never published. No names or addresses, please.</span></label><textarea id="fd-note" name="note" maxlength="<?php echo esc_attr( (string) ( $hwm_fig['noteMaxChars'] ?? '' ) ); ?>"></textarea></div>
		<button class="btn" type="submit">Send my report</button>
		<p class="form-msg" role="status" aria-live="polite" data-form-msg></p>
	</form>
</section>

<p class="notice"><strong>Reports so far: <?php echo esc_html( number_format( $hwm_n ) ); ?>.</strong> We publish the first results when <?php echo esc_html( (string) ( $hwm_fig['publishAt'] ?? '' ) ); ?> reports are in, and show a brand on its own only once it has at least <?php echo esc_html( (string) ( $hwm_fig['minPerBrand'] ?? '' ) ); ?>. Until then this page shows no averages, because a handful of reports would mislead.</p>

<h2>Why build a water heater failure database?</h2>
<p>Because nobody publishes how long each brand really lasts. Makers give a range for all tanks: Rheem and A. O. Smith both say about <?php echo esc_html( (string) ( $hwm_mv['rheem']['tankLow'] ?? '' ) ); ?> to <?php echo esc_html( (string) ( $hwm_mv['rheem']['tankHigh'] ?? '' ) ); ?> years. The Department of Energy estimates an average from national housing surveys, not by brand. Review sites rank brands by star ratings, which measure how happy buyers were in the first months, not the age at failure.</p>
<p>Your report fills that gap: the brand, the age when it failed, what failed, and whether it was maintained. With enough reports we can answer questions no one else can, from real heaters:</p>
<ul>
	<li>How old is the average Rheem, A. O. Smith or Bradford White tank when it fails?</li>
	<li>Do tanks fail by leaking first, or do burners, elements and controls give out first?</li>
	<li>Do heaters that were flushed and had the anode replaced last longer, and by how much?</li>
	<li>Does hard water shorten a tank's life as much as people say?</li>
</ul>

<h2>What does the report ask, and why?</h2>
<p>Only what is needed to answer those questions. There is no email field, and the optional note is read only by us, to catch typing mistakes and spam.</p>
<?php echo hwm_table( $hwm_c, 'fields' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Age at failure</dt>
	<dd>The year it failed minus the year it was installed. It is the number we will publish by brand and type.</dd>
	<dt>Failure</dt>
	<dd>The day the heater stopped making hot water safely or started leaking, whether it was then repaired or replaced.</dd>
</dl>

<h2>How the results will be published</h2>
<p>Once <?php echo esc_html( (string) ( $hwm_fig['publishAt'] ?? '' ) ); ?> reports are in, we publish the totals on this page and as a free CSV on our data page, and update them every month:</p>
<ol>
	<li>Median and average age at failure, by type (gas tank, electric tank, heat pump, tankless).</li>
	<li>The same by brand, for each brand with at least <?php echo esc_html( (string) ( $hwm_fig['minPerBrand'] ?? '' ) ); ?> reports.</li>
	<li>The share of failures by cause: tank leaks against parts.</li>
	<li>Age at failure with and without regular flushing and anode replacement, and on hard against normal water.</li>
</ol>
<p>Every figure will state how many reports it is based on. This is self-reported data from the people who chose to send it, not a random sample, and we will say so next to every number. Reports that are impossible (a failure year before the install year) are rejected by the form, and we remove obvious duplicates and spam before counting.</p>

<h2>Why do water heaters fail?</h2>
<p>Most tanks end with a leak from the tank itself, which cannot be fixed. Rheem's guide says: "If the leak is from the tank itself, replacement is usually required." The tank's steel is protected by a glass lining and the anode rod, and once the rod is used up, A. O. Smith's checklist warns, "your tank starts rusting from the inside out". Parts such as elements, thermostats, gas valves and relief valves fail too, but they can be replaced. Our <?php echo hwm_link( 'water-heater-leaking', 'leaking water heater guide' ); // phpcs:ignore ?> shows which leaks are repairs and which are the end of the tank.</p>
<p>Tankless units have no tank to rust, so they tend to fail at the heat exchanger, often after years of scale on hard water, or at the fan, sensors or control board. That is one reason their published life is longer: DOE estimates about <?php echo esc_html( hwm_value( $hwm_ls, 'types.gas_tankless.typical' ) ); ?> years for a tankless unit against about <?php echo esc_html( hwm_value( $hwm_ls, 'types.gas_tank.typical' ) ); ?> for a tank. The database will show whether reports agree.</p>

<h2>Is my report private?</h2>
<p>Yes. We store the answers in the form and the date, in our own database. We do not store your IP address, ask for your name or email, or show any single report. The note is never published. If you put something in it you want deleted, write to <?php echo esc_html( (string) hwm_site( 'contact.email' ) ); ?> with the date and brand and we will remove the report. See our <a href="<?php echo esc_url( hwm_url( 'privacy-policy' ) . '#email-lists' ); ?>">privacy policy</a>.</p>

<h2>Limitations</h2>
<p>Self-reported ages can be off by a year or two: people often guess the install year. Use the <?php echo hwm_link( 'water-heater-age-calculator', 'age calculator' ); // phpcs:ignore ?> to read the build date from the serial before you report, if the heater is still there. People whose heater failed early may be more likely to report than people whose heater lasted, which can pull the averages down; we will compare the totals with DOE's lifespan data and say so where they differ.</p>
<?php
$faq = array(
	array(
		'q' => 'What is the most reliable water heater brand?',
		'a' => 'No public data answers it by age at failure. Makers give one range for all tanks, and review ratings measure early satisfaction. That is why we collect real failure reports here; once enough are in, we will publish average age at failure by brand.',
	),
	array(
		'q' => 'How long do water heaters last before they fail?',
		'a' => 'DOE estimates about ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . ' years for a tank, in a range of ' . hwm_value( $hwm_ls, 'types.gas_tank.low' ) . ' to ' . hwm_value( $hwm_ls, 'types.gas_tank.high' ) . ', and about ' . hwm_value( $hwm_ls, 'types.gas_tankless.typical' ) . ' years for a tankless unit. Our database will show how real heaters compare, by brand.',
	),
	array(
		'q' => 'Why do you not ask for my email?',
		'a' => 'We do not need it. The report is about the heater, not about you, and asking for less keeps your data private. If you want reminders for your new heater, the maintenance schedule page has a separate, optional email signup.',
	),
	array(
		'q' => 'Can I report a heater that I repaired instead of replaced?',
		'a' => 'Yes. Pick what failed and choose "Repaired it". A repaired part, such as an element or a gas valve, still tells us when and how heaters of that brand fail. If it fails again later, send a second report.',
	),
	array(
		'q' => 'When will the results be published?',
		'a' => 'When ' . ( $hwm_fig['publishAt'] ?? '' ) . ' reports are in. We then publish the totals on this page and as a free CSV on our data page, and update them every month. A brand is shown on its own once it has at least ' . ( $hwm_fig['minPerBrand'] ?? '' ) . ' reports.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (product lifetime)', 'url' => (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ls, 'meta.sourceDate' ) ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace', 'url' => (string) ( $hwm_mv['rheem']['sourceUrl'] ?? '' ), 'accessed' => (string) hwm_raw( 'troubleshooting', 'references.rheem-lifespan.checked' ) ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long do water heaters last?', 'url' => (string) ( $hwm_mv['ao-smith']['sourceUrl'] ?? '' ), 'accessed' => (string) hwm_raw( $hwm_ls, 'meta.sourceDate' ) ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Water heater maintenance checklist', 'url' => (string) hwm_raw( 'troubleshooting', 'references.aos-checklist.sourceUrl' ), 'accessed' => (string) hwm_raw( 'troubleshooting', 'references.aos-checklist.checked' ) ),
);
