<?php
/**
 * Ad slot A/B/C/S (CLAUDE.md §7). Prints nothing at all until both the ad client and this slot's
 * ad unit ID are set in data/site.json → integrations (adsenseClient, adsenseSlots.<letter>).
 * The box has a fixed reserved height (CSS) and the ad fills it, so CLS stays 0.
 * Markup stays provider-neutral (.ad-slot[data-slot]); only the <ins> is AdSense-specific.
 * Args: slot.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_slot   = strtoupper( (string) ( $args['slot'] ?? '' ) );
$hwm_client = hwm_adsense_client();
$hwm_unit   = in_array( $hwm_slot, array( 'A', 'B', 'C', 'S' ), true ) ? hwm_adsense_unit( $hwm_slot ) : '';
if ( '' === $hwm_client || '' === $hwm_unit ) {
	return;
}
?>
<div class="ad-slot" data-slot="<?php echo esc_attr( $hwm_slot ); ?>">
	<span class="ad-slot__label">Advertisement</span>
	<ins class="adsbygoogle" data-ad-client="<?php echo esc_attr( $hwm_client ); ?>" data-ad-slot="<?php echo esc_attr( $hwm_unit ); ?>"></ins>
</div>
