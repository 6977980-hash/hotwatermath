<?php
/**
 * Right column on desktop (≥1100px): "On this page" list of the page's H2s, then the sidebar ad S
 * when one is configured. Hidden below 1100px, where the page is one column and nothing changes.
 * Without an ad the list is sticky; with an ad the ad is the sticky part and the list scrolls away.
 * Args: items (from hwm_toc_prepare).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_items = (array) ( $args['items'] ?? array() );
$hwm_ad    = hwm_ad_enabled( 'S' );
if ( ! $hwm_items && ! $hwm_ad ) {
	return;
}
?>
<div class="rail<?php echo $hwm_ad ? ' rail--ad' : ''; ?>">
	<?php if ( $hwm_items ) : ?>
		<nav class="toc-rail" aria-labelledby="toc-rail-title">
			<p class="toc-rail__title" id="toc-rail-title">On this page</p>
			<ol>
				<?php foreach ( $hwm_items as $hwm_i ) : ?>
					<li><a href="#<?php echo esc_attr( (string) $hwm_i['id'] ); ?>"><?php echo esc_html( (string) $hwm_i['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>
	<?php hwm_ad_sidebar(); ?>
</div>
