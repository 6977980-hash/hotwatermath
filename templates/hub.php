<?php
/**
 * Hub pages (/calculators, /guides): list every live page of the hub's type
 * (drafts too for logged-in editors) and the planned ones as "coming".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_entry = hwm_current();
$hwm_type  = (string) ( $hwm_entry['hub'] ?? 'tool' );
$hwm_items = hwm_pages_of( $hwm_type, hwm_is_draft_preview() ? array( 'live', 'draft' ) : array( 'live' ) );
$hwm_soon  = hwm_pages_of( $hwm_type, array( 'planned' ) );
$hwm_out   = hwm_content( (string) $hwm_entry['slug'], $hwm_entry );
get_header();
?>
<main id="main" class="container">
	<?php hwm_part( 'breadcrumb', array( 'entry' => $hwm_entry ) ); ?>
	<?php hwm_part( 'page-intro', array( 'entry' => $hwm_entry ) ); ?>
	<?php hwm_part( 'quick-answer', array( 'text' => $hwm_entry['quickAnswer'] ?? '' ) ); ?>

	<?php
	// Calculators hub: the core tools first, then the per-brand serial decoders (children of the age calculator).
	$hwm_groups = array( array( 'tool' === $hwm_type ? 'Sizing, age and cost calculators' : 'All water heater guides', array_values( array_filter( $hwm_items, static fn( $e ) => empty( $e['parent'] ) ) ) ) );
	$hwm_kids   = array_values( array_filter( $hwm_items, static fn( $e ) => ! empty( $e['parent'] ) ) );
	if ( $hwm_kids ) {
		$hwm_groups[] = array( 'Serial number decoders by brand', $hwm_kids );
	}
	?>
	<?php foreach ( $hwm_groups as $hwm_group ) : ?>
		<?php if ( ! $hwm_group[1] ) { continue; } ?>
		<?php if ( $hwm_group[0] ) : ?><h2><?php echo esc_html( $hwm_group[0] ); ?></h2><?php endif; ?>
		<ul class="cards">
			<?php foreach ( $hwm_group[1] as $hwm_p ) : ?>
				<li class="card">
					<?php if ( 'draft' === ( $hwm_p['status'] ?? '' ) ) : ?><span class="badge">Draft</span><?php endif; ?>
					<h3><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a></h3>
					<p><?php echo esc_html( (string) ( $hwm_p['summary'] ?? $hwm_p['description'] ?? '' ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endforeach; ?>

	<div class="prose">
		<?php echo $hwm_out['html']; // phpcs:ignore -- our own content file. ?>
	</div>

	<?php if ( $hwm_soon ) : ?>
		<h2>Coming next</h2>
		<p class="prose">These are being built and checked now. Each one goes up only after its formulas and sources are verified.</p>
		<ul class="cards">
			<?php foreach ( $hwm_soon as $hwm_p ) : ?>
				<li class="card card--muted">
					<span class="badge">Coming soon</span>
					<h3><?php echo esc_html( (string) $hwm_p['title'] ); ?></h3>
					<p><?php echo esc_html( (string) ( $hwm_p['summary'] ?? '' ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php hwm_part( 'author-line', array( 'entry' => $hwm_entry, 'show_disclaimer' => false ) ); ?>
</main>
<?php
get_footer();
