<?php
/**
 * Numbered outbound source list (CLAUDE.md §4.10). Primary sources are followed links (rel="noopener" only).
 * Args: items = [ [ 'title' => ..., 'url' => ..., 'publisher' => ..., 'accessed' => 'YYYY-MM-DD' ] ].
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_items = array_values( array_filter( (array) ( $args['items'] ?? array() ), static fn( $i ) => ! empty( $i['url'] ) && ! empty( $i['title'] ) ) );
if ( ! $hwm_items ) {
	return;
}
?>
<section class="sources" aria-labelledby="sources-heading">
	<h2 id="sources-heading">Sources</h2>
	<ol>
		<?php foreach ( $hwm_items as $hwm_s ) : ?>
			<li>
				<?php if ( ! empty( $hwm_s['publisher'] ) ) : ?><?php echo esc_html( (string) $hwm_s['publisher'] ); ?>, <?php endif; ?>
				<a href="<?php echo esc_url( (string) $hwm_s['url'] ); ?>" rel="noopener"><?php echo esc_html( (string) $hwm_s['title'] ); ?></a><?php if ( ! empty( $hwm_s['accessed'] ) ) : ?>. Checked <?php echo esc_html( hwm_date( (string) $hwm_s['accessed'] ) ); ?><?php endif; ?>.
			</li>
		<?php endforeach; ?>
	</ol>
</section>
