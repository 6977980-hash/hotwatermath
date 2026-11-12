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
$hwm_out = hwm_content( (string) ( $hwm_entry['contentFile'] ?? $hwm_entry['slug'] ), $hwm_entry );
get_header();
?>
<main id="main" class="container">
	<?php hwm_part( 'breadcrumb', array( 'entry' => $hwm_entry ) ); ?>
	<article class="prose">
		<?php hwm_part( 'page-intro', array( 'entry' => $hwm_entry ) ); ?>
		<?php hwm_part( 'quick-answer', array( 'text' => $hwm_entry['quickAnswer'] ?? '' ) ); ?>
		<?php echo $hwm_out['html']; // phpcs:ignore -- our own content file. ?>
		<?php hwm_part( 'faq', array( 'items' => $hwm_out['faq'], 'url' => hwm_url( $hwm_entry['slug'] ) ) ); ?>
		<?php hwm_part( 'sources', array( 'items' => $hwm_out['sources'] ) ); ?>
		<?php hwm_part( 'author-line', array( 'entry' => $hwm_entry, 'show_disclaimer' => false ) ); ?>
	</article>
</main>
<?php
get_footer();
