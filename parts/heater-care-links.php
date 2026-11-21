<?php
/**
 * "Keep your heater safe" box linking the recall lookup, maintenance schedule and failure database (2026-11-21).
 * Each link shows only when its page can be opened (live, or draft for editors), so drafts never leak to visitors.
 * Args: brand (recall brand id, optional: presets the recall lookup and names the brand's recall count),
 *       brandLabel (optional), only (array of 'recall'|'schedule'|'failure', optional).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_brand = (string) ( $args['brand'] ?? '' );
$hwm_only  = (array) ( $args['only'] ?? array( 'recall', 'schedule', 'failure' ) );
$hwm_items = array();
if ( in_array( 'recall', $hwm_only, true ) && hwm_can_open( hwm_page( 'water-heater-recall-lookup' ) ) ) {
	$hwm_n = 0;
	foreach ( (array) hwm_raw( 'recalls', 'recalls' ) as $hwm_r ) {
		$hwm_n += ( '' !== $hwm_brand && in_array( $hwm_brand, (array) $hwm_r['brands'], true ) ) ? 1 : 0;
	}
	$hwm_url  = hwm_url( 'water-heater-recall-lookup' ) . ( '' !== $hwm_brand ? '?brand=' . rawurlencode( $hwm_brand ) . '&model=' : '' );
	$hwm_why  = '' !== $hwm_brand
		? sprintf( '%s appears in %d U.S. CPSC water heater recall%s since %s; check your model and serial.', (string) ( $args['brandLabel'] ?? '' ), $hwm_n, 1 === $hwm_n ? '' : 's', (string) hwm_value( 'computed/water-heater-recall-lookup', 'figures.since' ) )
		: 'check the model and serial against every CPSC water heater recall.';
	$hwm_items[] = array( 'url' => $hwm_url, 'text' => 'Is it recalled?', 'why' => $hwm_why );
}
if ( in_array( 'schedule', $hwm_only, true ) && hwm_can_open( hwm_page( 'water-heater-maintenance-schedule' ) ) ) {
	$hwm_items[] = array( 'url' => hwm_url( 'water-heater-maintenance-schedule' ), 'text' => 'Maintenance schedule and reminders', 'why' => 'your flush, anode and valve dates, with free email reminders.' );
}
if ( in_array( 'failure', $hwm_only, true ) && hwm_can_open( hwm_page( 'water-heater-failure-database' ) ) ) {
	$hwm_items[] = array( 'url' => hwm_url( 'water-heater-failure-database' ) . '#report', 'text' => 'Did it fail? Add it to the failure database', 'why' => 'two minutes, no email; we publish real ages at failure by brand.' );
}
if ( ! $hwm_items ) {
	return;
}
?>
<aside class="next-steps heater-care" aria-label="Keep your water heater safe">
	<p class="heater-care__title"><strong>Keep your water heater safe</strong></p>
	<ul>
		<?php foreach ( $hwm_items as $hwm_i ) : ?>
			<li><a href="<?php echo esc_url( $hwm_i['url'] ); ?>"><?php echo esc_html( $hwm_i['text'] ); ?></a>: <?php echo esc_html( $hwm_i['why'] ); ?></li>
		<?php endforeach; ?>
	</ul>
</aside>
