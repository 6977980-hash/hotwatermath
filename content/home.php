<?php
/**
 * Home page (Phase 5; banded layout 2026-11-14): hero with three "start here" paths, calculators grouped by job,
 * the per-brand age pages, a "how our numbers are made" trust strip, recently updated pages and guides.
 * Cards and links appear only for pages the visitor can open (live, or draft for logged-in editors).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_tools  = array_values( array_filter( hwm_core_tools( array( 'live', 'draft' ) ), 'hwm_can_open' ) );
$hwm_brands = array_values( array_filter( hwm_pages_of( 'tool', array( 'live', 'draft' ) ), static fn( $e ) => ! empty( $e['parent'] ) && hwm_can_open( $e ) ) );
$hwm_guides = array_values( array_filter( hwm_pages_of( 'article', array( 'live', 'draft' ) ), 'hwm_can_open' ) );
$hwm_recent = hwm_recent_pages( 4 );

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

/** Calculators grouped by the job the visitor has (slug lists; a slug that is not open is skipped). */
$hwm_tool_groups = array(
	array( 'Size it', array( 'water-heater-size-calculator', 'tankless-water-heater-size-calculator', 'water-heater-expansion-tank-size' ) ),
	array( 'Old or broken unit', array( 'water-heater-age-calculator', 'repair-or-replace-water-heater' ) ),
	array( 'Compare costs', array( 'water-heater-cost-calculator', 'tankless-vs-tank-water-heater', 'gas-vs-electric-water-heater' ) ),
);
$hwm_by_slug = array();
foreach ( $hwm_tools as $hwm_t ) {
	$hwm_by_slug[ $hwm_t['slug'] ] = $hwm_t;
}
// Any open calculator not placed in a group above still shows, in the last group.
$hwm_placed = array_merge( ...array_column( $hwm_tool_groups, 1 ) );
foreach ( array_keys( $hwm_by_slug ) as $hwm_s ) {
	if ( ! in_array( $hwm_s, $hwm_placed, true ) ) {
		$hwm_tool_groups[2][1][] = $hwm_s;
	}
}

/** Our own line icons (24×24, stroke = currentColor), one per calculator. */
$hwm_icons = array(
	'water-heater-age-calculator'           => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
	'tankless-water-heater-size-calculator' => '<rect x="6" y="3" width="12" height="18" rx="2"/><path d="M12.5 7.5 10 12h4l-2.5 4.5"/>',
	'water-heater-size-calculator'          => '<path d="M7 5a5 2 0 0 1 10 0v14a5 2 0 0 1-10 0z"/><path d="M7 5a5 2 0 0 0 10 0M9.5 11h5M9.5 14h5"/>',
	'repair-or-replace-water-heater'        => '<path d="M14.5 6.5a4 4 0 0 0-5.3 5.3L4 17l3 3 5.2-5.2a4 4 0 0 0 5.3-5.3l-2.5 2.5-2.5-.5-.5-2.5z"/>',
	'water-heater-cost-calculator'          => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
	'tankless-vs-tank-water-heater'         => '<path d="M12 3v18M5 21h14M4 8h16M4 8l-2.5 6a3 3 0 0 0 5 0zM20 8l-2.5 6a3 3 0 0 0 5 0z"/>',
	'gas-vs-electric-water-heater'          => '<path d="M8 21c-3-2-3.5-6 0-10 .5 2 1.5 3 3 3-1-4 1-8 4-10 0 4 4 6 4 11a6 6 0 0 1-6 6z"/>',
	'water-heater-expansion-tank-size'        => '<circle cx="12" cy="13" r="7"/><path d="M12 3v3M9 3h6M8.5 13h7"/>',
);
$hwm_icon = static function ( string $slug ) use ( $hwm_icons ): string {
	$paths = $hwm_icons[ $slug ] ?? '<circle cx="12" cy="12" r="9"/>';
	return '<svg class="card__icon" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
};

/** Start here: three jobs, each opening the first calculator for it. */
$hwm_paths = array(
	array( 'water-heater-size-calculator', 'I am buying a new water heater', 'Find the size, then the installed price.' ),
	array( 'water-heater-age-calculator', 'My water heater is old or acting up', 'Read its age, then decide repair or replace.' ),
	array( 'tankless-vs-tank-water-heater', 'I am comparing types and costs', 'Tank, tankless, heat pump, gas or electric.' ),
);

// Guides by group (order from data/pages.json meta.articleGroups); state pages go in their own grid.
$hwm_states     = array();
$hwm_state_wide = array();
$hwm_ggroups = array();
foreach ( (array) ( hwm_raw( 'pages', 'meta.articleGroups' ) ?? array() ) as $hwm_g ) {
	$hwm_ggroups[ (string) $hwm_g ] = array();
}
foreach ( $hwm_guides as $hwm_p ) {
	$hwm_g = (string) ( $hwm_p['group'] ?? 'More guides' );
	if ( str_contains( (string) $hwm_p['slug'], '/' ) ) {
		$hwm_states[] = $hwm_p;
		continue;
	}
	if ( 'Cost by State' === $hwm_g ) { // The all-states pages (cost index) sit above the state grid, not in a card.
		$hwm_state_wide[] = $hwm_p;
		continue;
	}
	$hwm_ggroups[ $hwm_g ][] = $hwm_p;
}
$hwm_ggroups = array_filter( $hwm_ggroups );
$hwm_per_group = 8;
?>
<section class="band hero">
	<div class="hero__text">
		<?php hwm_part( 'page-intro', array( 'entry' => hwm_current() ) ); ?>
		<?php hwm_part( 'quick-answer', array( 'text' => hwm_current()['quickAnswer'] ?? '' ) ); ?>
	</div>
	<nav class="hero__paths" aria-label="Start here">
		<p class="hero__paths-title">What do you need to do?</p>
		<ul class="paths">
			<?php foreach ( $hwm_paths as $hwm_path ) : ?>
				<?php if ( ! isset( $hwm_by_slug[ $hwm_path[0] ] ) ) { continue; } ?>
				<li><a class="path" href="<?php echo esc_url( hwm_url( $hwm_path[0] ) ); ?>"><span class="path__title"><?php echo esc_html( $hwm_path[1] ); ?></span> <span class="path__note"><?php echo esc_html( $hwm_path[2] ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
</section>

<section class="band" aria-labelledby="tools-heading">
	<h2 id="tools-heading">Which water heater calculator do you need?</h2>
	<p class="band__lead">Each calculator answers one question a homeowner has when a water heater fails, gets old or needs choosing. Pick the one that matches yours; every result shows the formula and the source of each default value.</p>
	<ul class="cards cards--tools">
		<?php foreach ( $hwm_tool_groups as $hwm_group ) : ?>
			<?php foreach ( $hwm_group[1] as $hwm_s ) : ?>
				<?php if ( ! isset( $hwm_by_slug[ $hwm_s ] ) ) { continue; } ?>
				<?php $hwm_p = $hwm_by_slug[ $hwm_s ]; ?>
				<li class="card card--tool">
					<span class="card__top"><?php echo $hwm_icon( (string) $hwm_p['slug'] ); // phpcs:ignore -- our own static SVG. ?><span class="card__tag"><?php echo esc_html( $hwm_group[0] ); ?></span></span>
					<h3><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a></h3>
					<p><?php echo esc_html( (string) ( $hwm_p['summary'] ?? '' ) ); ?></p>
					<span class="card__cta" aria-hidden="true">Open calculator &rarr;</span>
				</li>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</ul>
</section>

<section class="band band--alt" aria-labelledby="questions-heading">
	<h2 id="questions-heading">Find the right tool for your question</h2>
	<p class="band__lead">A <dfn>water heater calculator</dfn> here means a tool that applies a published formula, such as the first hour rating method or the temperature rise equation, to your own inputs. This table matches common questions to the tool that answers them.</p>
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
</section>

<section class="band band--brand" aria-labelledby="trust-heading">
	<h2 id="trust-heading">How our numbers are made</h2>
	<ul class="stats">
		<li class="stat"><span class="stat__big">Shown</span> <strong class="stat__label">Published formulas</strong> <span class="stat__text">Every result has a "How we calculated this" panel with your own numbers in the formula.</span></li>
		<li class="stat"><span class="stat__big"><?php echo esc_html( (string) $hwm_data_files ); ?></span> <strong class="stat__label">Sourced data files</strong> <span class="stat__text">Flow rates, groundwater temperatures, lifespans, prices and energy rates each name their source (DOE, ENERGY STAR, EIA, EPA WaterSense, manufacturer literature) and the date we checked it.</span></li>
		<li class="stat"><span class="stat__big"><?php echo esc_html( (string) $hwm_checks ); ?></span> <strong class="stat__label">Cross-check cases</strong> <span class="stat__text">We run each calculator against manufacturer sizing guides, recall records or federal labels and publish every difference.</span></li>
		<li class="stat"><span class="stat__big">0</span> <strong class="stat__label">Hidden sales</strong> <span class="stat__text">We are not plumbers, we do not refer contractors and we sell no leads.</span></li>
	</ul>
	<p>Read the formulas, sources and cross-check tables on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">our methodology page</a>, and <a href="<?php echo esc_url( hwm_url( 'about' ) ); ?>">who builds this site</a>.</p>
</section>

<?php if ( $hwm_brands ) : ?>
<section class="band" aria-labelledby="brands-heading">
	<h2 id="brands-heading">How to read a serial number by brand</h2>
	<p class="band__lead">Most brands hide the build date in the serial number, and each brand uses its own code. These pages decode every known format with examples, and the <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> does it for you.</p>
	<ul class="chips">
		<?php foreach ( $hwm_brands as $hwm_p ) : ?>
			<li><a class="chip" href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</section>
<?php endif; ?>

<?php if ( $hwm_guides ) : ?>
<section class="band band--alt" aria-labelledby="guides-heading">
	<h2 id="guides-heading">Water heater guides</h2>
	<p class="band__lead">Costs, lifespan and fixes, each built on the same sourced data as the calculators.</p>
	<?php
	$hwm_big   = array_filter( $hwm_ggroups, static fn( $l ) => count( $l ) > 2 );
	$hwm_small = array_filter( $hwm_ggroups, static fn( $l ) => count( $l ) <= 2 );
	$hwm_group_card = static function ( string $name, array $list, int $max ): void {
		?>
		<div class="guide-group">
			<h3><?php echo esc_html( $name ); ?></h3>
			<ul>
				<?php foreach ( array_slice( $list, 0, $max ) as $p ) : ?>
					<li><a href="<?php echo esc_url( hwm_url( $p['slug'] ) ); ?>"><?php echo esc_html( (string) $p['title'] ); ?></a><?php if ( count( $list ) <= 2 && ! empty( $p['summary'] ) ) : ?><span class="guide-group__note"><?php echo esc_html( (string) $p['summary'] ); ?></span><?php endif; ?></li>
				<?php endforeach; ?>
			</ul>
			<?php if ( count( $list ) > $max ) : ?>
				<p class="guide-group__more"><a href="<?php echo esc_url( hwm_url( 'guides' ) ); ?>">All <?php echo esc_html( (string) count( $list ) ); ?> <?php echo esc_html( strtolower( $name ) ); ?> guides</a></p>
			<?php endif; ?>
		</div>
		<?php
	};
	?>
	<div class="guide-groups">
		<?php foreach ( $hwm_big as $hwm_gname => $hwm_list ) { $hwm_group_card( (string) $hwm_gname, $hwm_list, $hwm_per_group ); } ?>
		<?php if ( $hwm_small || $hwm_states ) : ?>
			<div class="guide-stack">
				<?php foreach ( $hwm_small as $hwm_gname => $hwm_list ) { $hwm_group_card( (string) $hwm_gname, $hwm_list, $hwm_per_group ); } ?>
				<?php if ( $hwm_states ) : ?>
					<div class="guide-group">
						<h3>Installation cost by state</h3>
						<ul class="state-links">
							<?php foreach ( $hwm_states as $hwm_p ) : ?>
								<li><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) preg_replace( '/^Water Heater Installation Cost in /', '', (string) $hwm_p['title'] ) ); ?></a></li>
							<?php endforeach; ?>
						</ul>
						<?php foreach ( $hwm_state_wide as $hwm_p ) : ?>
							<p class="guide-group__more">All 50 states and DC: <a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<p>See <a href="<?php echo esc_url( hwm_url( 'guides' ) ); ?>">all water heater guides</a> or <a href="<?php echo esc_url( hwm_url( 'calculators' ) ); ?>">all water heater calculators</a>.</p>
</section>
<?php endif; ?>

<section class="band split">
	<div>
		<h2>Who this site is for</h2>
		<p>Homeowners in the United States who are replacing a water heater, choosing between tank and tankless, or trying to tell how old their current unit is. DIYers and small contractors who want to check a number quickly are welcome too.</p>
		<p>All results are planning estimates. Gas, venting and electrical work have code and safety rules that vary by town, so confirm the final choice with a licensed professional in your area.</p>
	</div>
	<?php if ( $hwm_recent ) : ?>
	<div class="split__side">
		<h2>Recently updated</h2>
		<ul class="recent-list">
			<?php foreach ( $hwm_recent as $hwm_p ) : ?>
				<li><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a> <span class="recent-list__date">updated <time datetime="<?php echo esc_attr( (string) $hwm_p['dateModified'] ); ?>"><?php echo esc_html( hwm_date( (string) $hwm_p['dateModified'] ) ); ?></time></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
</section>
