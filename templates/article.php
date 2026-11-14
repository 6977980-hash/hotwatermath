<?php
/**
 * Article template: same order as tools minus the calculator (CLAUDE.md §4).
 * The content file embeds the relevant calculator itself where it fits and varies its H2 order.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_entry = hwm_current();
$hwm_slug  = (string) $hwm_entry['slug'];
$hwm_out   = hwm_content( $hwm_slug, $hwm_entry );
$hwm_toc   = hwm_toc_prepare( $hwm_out );
get_header();
?>
<main id="main" class="container">
	<div class="layout<?php echo hwm_has_rail( $hwm_toc ) ? ' layout--sidebar' : ''; ?>">
		<article>
			<?php hwm_part( 'breadcrumb', array( 'entry' => $hwm_entry ) ); ?>
			<?php hwm_part( 'page-intro', array( 'entry' => $hwm_entry ) ); ?>
			<?php hwm_part( 'quick-answer', array( 'text' => $hwm_entry['quickAnswer'] ?? '' ) ); ?>
			<div class="prose">
				<?php echo $hwm_out['html']; // phpcs:ignore -- our own content file. ?>
			</div>
			<?php hwm_ad( 'B' ); ?>
			<div class="prose">
				<?php hwm_part( 'faq', array( 'items' => $hwm_out['faq'], 'url' => hwm_url( $hwm_slug ) ) ); ?>
				<?php hwm_part( 'sources', array( 'items' => $hwm_out['sources'] ) ); ?>
			</div>
			<?php hwm_ad( 'C' ); ?>
			<div class="prose">
				<?php hwm_part( 'author-line', array( 'entry' => $hwm_entry ) ); ?>
			</div>
			<?php hwm_part( 'related-tools', array( 'slugs' => $hwm_out['related'], 'heading' => 'Related calculators and guides' ) ); ?>
		</article>
		<?php hwm_part( 'rail', array( 'items' => $hwm_toc ) ); ?>
	</div>
</main>
<?php
get_footer();
