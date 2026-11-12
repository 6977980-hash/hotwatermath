<?php
/** Breadcrumb (schema is printed in <head> by inc/schema.php). Args: entry. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_trail = hwm_trail( $args['entry'] );
if ( count( $hwm_trail ) < 2 ) {
	return;
}
?>
<nav class="breadcrumb" aria-label="Breadcrumb">
	<ol>
		<?php foreach ( $hwm_trail as $hwm_i => $hwm_crumb ) : ?>
			<?php if ( $hwm_i === count( $hwm_trail ) - 1 ) : ?>
				<li><span aria-current="page"><?php echo esc_html( $hwm_crumb[0] ); ?></span></li>
			<?php else : ?>
				<li><a href="<?php echo esc_url( $hwm_crumb[1] ); ?>"><?php echo esc_html( $hwm_crumb[0] ); ?></a></li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ol>
</nav>
