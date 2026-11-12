<?php
/**
 * "What to do next" box: 2–3 internal links. Links to pages that do not exist yet are skipped.
 * Args: items = [ [ 'slug' => ..., 'text' => descriptive anchor, 'why' => optional sentence ] ].
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_links = array();
foreach ( (array) ( $args['items'] ?? array() ) as $hwm_item ) {
	$hwm_p = hwm_page( (string) ( $hwm_item['slug'] ?? '' ) );
	if ( $hwm_p && ( hwm_is_live( $hwm_p ) || ( 'draft' === ( $hwm_p['status'] ?? '' ) && hwm_is_draft_preview() ) ) ) {
		$hwm_links[] = $hwm_item + array( 'url' => hwm_url( $hwm_p['slug'] ) );
	}
}
if ( ! $hwm_links ) {
	return;
}
?>
<aside class="next-steps" aria-labelledby="next-steps-heading">
	<h2 id="next-steps-heading">What to do next</h2>
	<ul>
		<?php foreach ( array_slice( $hwm_links, 0, 3 ) as $hwm_l ) : ?>
			<li><a href="<?php echo esc_url( $hwm_l['url'] ); ?>"><?php echo esc_html( (string) $hwm_l['text'] ); ?></a><?php echo ! empty( $hwm_l['why'] ) ? ': ' . esc_html( (string) $hwm_l['why'] ) : ''; ?></li>
		<?php endforeach; ?>
	</ul>
</aside>
