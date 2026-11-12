<?php
/** H1 + one-sentence subtitle. Args: entry. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_e = $args['entry'];
?>
<div class="page-intro">
	<h1><?php echo esc_html( (string) ( $hwm_e['h1'] ?? $hwm_e['title'] ) ); ?></h1>
	<?php if ( ! empty( $hwm_e['subtitle'] ) ) : ?>
		<p class="page-subtitle"><?php echo esc_html( (string) $hwm_e['subtitle'] ); ?></p>
	<?php endif; ?>
</div>
