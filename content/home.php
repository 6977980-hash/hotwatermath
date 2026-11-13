<?php
/**
 * Home page (Phase 5): the seven calculators with one-line value props, a question → tool table,
 * the per-brand age pages, a "how our numbers are made" trust strip, recently updated pages and guides.
 * Cards and links appear only for pages the visitor can open (live, or draft for logged-in editors).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_tools  = array_values( array_filter( hwm_core_tools( array( 'live', 'draft' ) ), 'hwm_can_open' ) );
$hwm_brands = array_values( array_filter( hwm_pages_of( 'tool', array( 'live', 'draft' ) ), static fn( $e ) => ! empty( $e['parent'] ) && hwm_can_open( $e ) ) );
$hwm_guides = array_values( array_filter( hwm_pages_of( 'article', array( 'live', 'draft' ) ), 'hwm_can_open' ) );
$hwm_recent = hwm_recent_pages( 6 );

// Trust strip counts: pure counting of our own files, nothing invented.
$hwm_data_files = 0;
foreach ( (array) glob( HWM_DIR . '/data/*.json' ) as $hwm_file ) {
	$hwm_name = basename( (string) $hwm_file, '.json' );
	if ( '' !== (string) hwm_raw( $hwm_name, 'meta.sourceUrl' ) && ! in_array( $hwm_name, array( 'pages', 'site', 'affiliate' ), true ) ) {
		++$hwm_data_files;
	}
}
$hwm_checks = 0;
foreach ( hwm_core_tools( array( 'live', 'draft' ) ) as $hwm_t ) {
	$hwm_checks += count( (array) hwm_raw( 'computed/' . $hwm_t['slug'], 'crossChecks' ) );
}

/** Which tool answers which question (unique to the home page). Slug → [question, what you get]. */
$hwm_questions = array(
	'water-heater-size-calculator'          => array( 'What size tank do I need?', 'Your peak-hour demand in gallons and the first hour rating to shop for.' ),
	'tankless-water-heater-size-calculator' => array( 'How big a tankless unit do I need?', 'Flow in gallons per minute and the temperature rise for your state.' ),
	'water-heater-age-calculator'           => array( 'How old is my water heater?', 'The build month and year from the serial number, and the share of its expected life used.' ),
	'repair-or-replace-water-heater'        => array( 'Should I repair it or replace it?', 'A repair, replace or borderline verdict from age, symptom and quote.' ),
	'water-heater-cost-calculator'          => array( 'What will a new one cost installed?', 'An itemized low, typical and high price for your state and job.' ),
	'tankless-vs-tank-water-heater'         => array( 'Is tankless worth it over a tank?', 'Total cost of ownership over the years you choose, and the payback year if there is one.' ),
	'gas-vs-electric-water-heater'          => array( 'Is gas or electric cheaper to run?', 'Yearly running cost of five water heater types with your state\'s energy prices.' ),
);
?>
<h2>Which water heater calculator do you need?</h2>
<p>Each calculator answers one question a homeowner has when a water heater fails, gets old or needs choosing. Pick the one that matches yours; every result shows the formula and the source of each default value.</p>
<?php if ( $hwm_tools ) : ?>
	<ul class="cards">
		<?php foreach ( $hwm_tools as $hwm_p ) : ?>
			<li class="card">
				<h3><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a></h3>
				<p><?php echo esc_html( (string) ( $hwm_p['summary'] ?? '' ) ); ?></p>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<h2>Find the right tool for your question</h2>
<p>A <dfn>water heater calculator</dfn> here means a tool that applies a published formula, such as the first hour rating method or the temperature rise equation, to your own inputs. This table matches common questions to the tool that answers them.</p>
<div class="table-wrap" role="region" aria-label="Questions and the calculator that answers each" tabindex="0">
	<table>
		<caption>Questions and the calculator that answers each</caption>
		<thead><tr><th scope="col">Your question</th><th scope="col">Calculator</th><th scope="col">What you get</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_questions as $hwm_slug => $hwm_q ) : ?>
				<?php if ( ! hwm_page( $hwm_slug ) ) { continue; } ?>
				<tr>
					<th scope="row"><?php echo esc_html( $hwm_q[0] ); ?></th>
					<td><?php echo hwm_link( $hwm_slug ); // phpcs:ignore -- escaped in helper. ?></td>
					<td><?php echo esc_html( $hwm_q[1] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<section class="trust-strip" aria-labelledby="trust-heading">
	<h2 id="trust-heading">How our numbers are made</h2>
	<ul>
		<li><strong>Published formulas.</strong> Every result has a "How we calculated this" panel with your own numbers in the formula.</li>
		<li><strong><?php echo esc_html( (string) $hwm_data_files ); ?> sourced data files.</strong> Flow rates, groundwater temperatures, lifespans, prices and energy rates each name their source (DOE, ENERGY STAR, EIA, EPA WaterSense, manufacturer literature) and the date we checked it.</li>
		<li><strong><?php echo esc_html( (string) $hwm_checks ); ?> cross-check cases.</strong> We run each calculator against manufacturer sizing guides, recall records or federal labels and publish every difference.</li>
		<li><strong>No hidden sales.</strong> We are not plumbers, we do not refer contractors and we sell no leads.</li>
	</ul>
	<p>Read the formulas, sources and cross-check tables on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">our methodology page</a>, and <a href="<?php echo esc_url( hwm_url( 'about' ) ); ?>">who builds this site</a>.</p>
</section>

<?php if ( $hwm_brands ) : ?>
	<h2>How to read a serial number by brand</h2>
	<p>Most brands hide the build date in the serial number, and each brand uses its own code. These pages decode every known format with examples, and the <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> does it for you.</p>
	<ul class="link-columns">
		<?php foreach ( $hwm_brands as $hwm_p ) : ?>
			<li><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php if ( $hwm_recent ) : ?>
	<h2>Recently updated</h2>
	<ul class="recent-list">
		<?php foreach ( $hwm_recent as $hwm_p ) : ?>
			<li><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a> <span class="recent-list__date">updated <time datetime="<?php echo esc_attr( (string) $hwm_p['dateModified'] ); ?>"><?php echo esc_html( hwm_date( (string) $hwm_p['dateModified'] ) ); ?></time></span></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php if ( $hwm_guides ) : ?>
	<h2>Water heater guides</h2>
	<ul>
		<?php foreach ( $hwm_guides as $hwm_p ) : ?>
			<li><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a>: <?php echo esc_html( (string) ( $hwm_p['summary'] ?? '' ) ); ?></li>
		<?php endforeach; ?>
	</ul>
	<p>See <a href="<?php echo esc_url( hwm_url( 'guides' ) ); ?>">all water heater guides</a> or <a href="<?php echo esc_url( hwm_url( 'calculators' ) ); ?>">all water heater calculators</a>.</p>
<?php endif; ?>

<h2>Who this site is for</h2>
<p>Homeowners in the United States who are replacing a water heater, choosing between tank and tankless, or trying to tell how old their current unit is. DIYers and small contractors who want to check a number quickly are welcome too.</p>
<p>All results are planning estimates. Gas, venting and electrical work have code and safety rules that vary by town, so confirm the final choice with a licensed professional in your area.</p>
