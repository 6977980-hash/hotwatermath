<?php
/**
 * Ad slot A/B/C/S (CLAUDE.md §7). Prints nothing at all until an ad client is set
 * in data/site.json → integrations.adsenseClient. Markup stays provider-neutral.
 * Args: slot.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_client = (string) hwm_site( 'integrations.adsenseClient' );
$hwm_slot   = strtoupper( (string) ( $args['slot'] ?? '' ) );
if ( '' === $hwm_client || ! in_array( $hwm_slot, array( 'A', 'B', 'C', 'S' ), true ) ) {
	return;
}
?>
<div class="ad-slot" data-slot="<?php echo esc_attr( $hwm_slot ); ?>">
	<span class="ad-slot__label">Advertisement</span>
</div>
