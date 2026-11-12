<?php
/**
 * Related tools: 3 cards (CLAUDE.md §4.12). Only pages that exist (live, or draft for logged-in editors).
 * Args: slugs (array), heading.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_cards = array();
foreach ( (array) ( $args['slugs'] ?? array() ) as $hwm_slug ) {
	$hwm_p = hwm_page( (string) $hwm_slug );
	if ( $hwm_p && ( hwm_is_live( $hwm_p ) || ( 'draft' === ( $hwm_p['status'] ?? '' ) && hwm_is_draft_preview() ) ) ) {
		$hwm_cards[] = $hwm_p;
	}
}
$hwm_cards = array_slice( $hwm_cards, 0, 3 );
if ( ! $hwm_cards ) {
	return;
}
?>
<section aria-labelledby="related-heading">
	<h2 id="related-heading"><?php echo esc_html( (string) ( $args['heading'] ?? 'Related calculators' ) ); ?></h2>
	<ul class="cards">
		<?php foreach ( $hwm_cards as $hwm_p ) : ?>
			<li class="card">
				<h3><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a></h3>
				<p><?php echo esc_html( (string) ( $hwm_p['summary'] ?? $hwm_p['description'] ?? '' ) ); ?></p>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
