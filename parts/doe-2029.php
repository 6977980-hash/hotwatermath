<?php
/**
 * DOE's 2029 water heater standard (89 FR 37778), audit suggestion S-13. Facts and quotes: data/uef-ranges.json → doe2029.
 * Args: lead (string, optional) = one page-specific sentence that opens the paragraph; heading (bool) = print an H3.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_r   = (array) hwm_raw( 'uef-ranges', 'doe2029' );
$hwm_q   = (array) ( $hwm_r['quotes'] ?? array() );
$hwm_due = hwm_date( (string) ( $hwm_r['complianceDate'] ?? '' ) );
?>
<?php if ( ! empty( $args['heading'] ) ) : ?>
<h3>What changes in <?php echo esc_html( substr( (string) ( $hwm_r['complianceDate'] ?? '' ), 0, 4 ) ); ?>: DOE's new water heater standard</h3>
<?php endif; ?>
<p><?php echo ! empty( $args['lead'] ) ? esc_html( (string) $args['lead'] ) . ' ' : ''; ?>DOE's final rule (<a href="<?php echo esc_url( (string) ( $hwm_r['sourceUrl'] ?? '' ) ); ?>" rel="noopener"><?php echo esc_html( (string) ( $hwm_r['citation'] ?? '' ) ); ?></a>) sets new minimum efficiencies for water heaters made or imported from <?php echo esc_html( $hwm_due ); ?>. For electric storage tanks of <?php echo esc_html( (string) ( $hwm_r['electricClassMinGallons'] ?? '' ) ); ?> to <?php echo esc_html( (string) ( $hwm_r['electricClassMaxGallons'] ?? '' ) ); ?> gallons, DOE expects &ldquo;<?php echo esc_html( (string) ( $hwm_q[4] ?? '' ) ); ?>&rdquo;. In plain terms: a new full-size electric tank made after that date will be a heat pump water heater, while small tanks (<?php echo esc_html( (string) ( $hwm_r['smallClassDefinition'] ?? '' ) ); ?>) can keep ordinary elements. The rule covers new production only: a heater already in your home, or one made before the date, can still be installed and used.</p>
