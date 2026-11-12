<?php
/**
 * FAQ as <details>/<summary> plus FAQPage JSON-LD (kept for answer engines; no rich result expected).
 * Args: items = [ [ 'q' => question, 'a' => answer HTML ] ], url, heading.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_items = array_values( array_filter( (array) ( $args['items'] ?? array() ), static fn( $i ) => ! empty( $i['q'] ) && ! empty( $i['a'] ) ) );
if ( ! $hwm_items ) {
	return;
}
$hwm_allowed = array( 'a' => array( 'href' => true, 'rel' => true ), 'strong' => array(), 'em' => array(), 'code' => array(), 'br' => array() );
?>
<section class="faq" aria-labelledby="faq-heading">
	<h2 id="faq-heading"><?php echo esc_html( (string) ( $args['heading'] ?? 'Frequently asked questions' ) ); ?></h2>
	<?php foreach ( $hwm_items as $hwm_item ) : ?>
		<details>
			<summary><?php echo esc_html( (string) $hwm_item['q'] ); ?></summary>
			<div class="faq__answer"><p><?php echo wp_kses( (string) $hwm_item['a'], $hwm_allowed ); ?></p></div>
		</details>
	<?php endforeach; ?>
	<?php hwm_jsonld( hwm_schema_faq( $hwm_items, (string) ( $args['url'] ?? '' ) ) ); ?>
</section>
