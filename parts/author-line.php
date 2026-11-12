<?php
/**
 * Author + verification line and disclaimer (CLAUDE.md §1, §4.11).
 * Args: entry, show_disclaimer (default true).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_e        = $args['entry'];
$hwm_verified = (string) ( hwm_site( 'dateVerified.' . $hwm_e['slug'] ) ?? '' );
$hwm_parts    = array(
	'Built by <a href="' . esc_url( hwm_url( 'about' ) ) . '">' . esc_html( (string) hwm_site( 'author.name' ) ) . '</a>',
	'Formulas and sources on <a href="' . esc_url( hwm_url( 'methodology' ) ) . '">our methodology page</a>',
);
if ( $hwm_verified ) {
	$hwm_parts[] = 'Last verified <time datetime="' . esc_attr( $hwm_verified ) . '">' . esc_html( hwm_date( $hwm_verified ) ) . '</time>';
}
if ( ! empty( $hwm_e['dateModified'] ) ) {
	$hwm_parts[] = 'Last updated <time datetime="' . esc_attr( (string) $hwm_e['dateModified'] ) . '">' . esc_html( hwm_date( (string) $hwm_e['dateModified'] ) ) . '</time>';
}
?>
<div class="author-line">
	<p><?php echo implode( ' &middot; ', $hwm_parts ); // phpcs:ignore -- escaped above. ?></p>
	<?php if ( $args['show_disclaimer'] ?? true ) : ?>
		<p class="disclaimer">These results are estimates for planning, not professional advice. Confirm sizing, code and safety questions with a licensed professional in your area.</p>
	<?php endif; ?>
</div>
