<?php
/**
 * Home and utility pages: breadcrumb, H1, quick answer, body from content/<slug>.php,
 * optional FAQ and sources, author line (no tool disclaimer on legal pages).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_entry = hwm_current();
if ( ! $hwm_entry ) {
	require HWM_DIR . '/404.php';
	return;
}
$hwm_out  = hwm_content( (string) ( $hwm_entry['contentFile'] ?? $hwm_entry['slug'] ), $hwm_entry );
$hwm_home = 'home' === ( $hwm_entry['type'] ?? '' );
// Long utility pages (methodology, policies) get the desktop "On this page" rail; the home page uses the full width instead.
$hwm_toc  = $hwm_home ? array() : hwm_toc_prepare( $hwm_out );
$hwm_rail = ! $hwm_home && hwm_has_rail( $hwm_toc );
get_header();
?>
<main id="main" class="container">
	<?php hwm_part( 'breadcrumb', array( 'entry' => $hwm_entry ) ); ?>
	<?php if ( $hwm_rail ) : ?><div class="layout layout--sidebar"><?php endif; ?>
	<article class="<?php echo $hwm_home ? 'hwm-home' : 'prose'; ?>">
		<?php if ( ! $hwm_home ) : // The home page prints its H1 and quick answer inside its hero (content/home.php). ?>
			<?php hwm_part( 'page-intro', array( 'entry' => $hwm_entry ) ); ?>
			<?php hwm_part( 'quick-answer', array( 'text' => $hwm_entry['quickAnswer'] ?? '' ) ); ?>
		<?php endif; ?>
		<?php echo $hwm_out['html']; // phpcs:ignore -- our own content file. ?>
		<?php hwm_part( 'faq', array( 'items' => $hwm_out['faq'], 'url' => hwm_url( $hwm_entry['slug'] ) ) ); ?>
		<?php hwm_part( 'sources', array( 'items' => $hwm_out['sources'] ) ); ?>
		<?php hwm_part( 'author-line', array( 'entry' => $hwm_entry, 'show_disclaimer' => false ) ); ?>
	</article>
	<?php if ( $hwm_rail ) : ?>
		<?php hwm_part( 'rail', array( 'items' => $hwm_toc ) ); ?>
	</div>
	<?php endif; ?>
</main>
<?php
get_footer();
