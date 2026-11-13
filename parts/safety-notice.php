<?php
/**
 * Safety notice at the top of a troubleshooting article (PHASES 6b task 3, CLAUDE.md §8b).
 * Text and figures come from data/troubleshooting.json → safety, each with its reference.
 * Args: gas (bool), power (bool), scald (bool).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_ref = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_pub = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.publisher' );
$hwm_s   = (array) hwm_raw( $hwm_f, 'safety' );
?>
<aside class="safety-notice" aria-label="Safety first">
	<p><strong>Safety first.</strong></p>
	<ul>
		<?php if ( ! empty( $args['gas'] ) ) : ?>
			<li><?php echo esc_html( (string) $hwm_s['gasSmell'] ); ?> (<a href="<?php echo esc_url( $hwm_ref( (string) $hwm_s['gasSmellRef'] ) ); ?>" rel="noopener"><?php echo esc_html( $hwm_pub( (string) $hwm_s['gasSmellRef'] ) ); ?> manual</a>)</li>
		<?php endif; ?>
		<?php if ( ! empty( $args['power'] ) ) : ?>
			<li><?php echo esc_html( (string) $hwm_s['powerOff'] ); ?> (<a href="<?php echo esc_url( $hwm_ref( (string) $hwm_s['powerOffRef'] ) ); ?>" rel="noopener"><?php echo esc_html( $hwm_pub( (string) $hwm_s['powerOffRef'] ) ); ?></a>)</li>
		<?php endif; ?>
		<?php if ( ! empty( $args['scald'] ) ) : ?>
			<li>Tank water can scald: the CPSC says most adults suffer third-degree burns from <?php echo esc_html( (string) $hwm_s['scaldF'] ); ?> °F water in <?php echo esc_html( (string) $hwm_s['scaldSeconds'] ); ?> seconds. Let the water cool before you drain or open anything (<a href="<?php echo esc_url( $hwm_ref( (string) $hwm_s['scaldRef'] ) ); ?>" rel="noopener">CPSC</a>).</li>
		<?php endif; ?>
	</ul>
</aside>
