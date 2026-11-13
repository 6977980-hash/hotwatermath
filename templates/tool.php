<?php
/**
 * Tool page template, in the order of CLAUDE.md §4:
 * breadcrumb → H1 + subtitle → quick answer → calculator (+ next steps) → ad A →
 * H2 sections (content file) → ad B → FAQ → sources → author line + disclaimer → related tools.
 *
 * The content file content/<slug>.php echoes the H2 sections and sets:
 *   $calculator_inputs (HTML), $next_steps, $faq, $sources, $related (slugs).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_entry = hwm_current();
$hwm_slug  = (string) $hwm_entry['slug'];
$hwm_out   = hwm_tool_content( $hwm_slug, $hwm_entry );
get_header();
?>
<main id="main" class="container">
	<div class="layout layout--sidebar">
		<article>
			<?php hwm_part( 'breadcrumb', array( 'entry' => $hwm_entry ) ); ?>
			<?php hwm_part( 'page-intro', array( 'entry' => $hwm_entry ) ); ?>
			<?php hwm_part( 'quick-answer', array( 'text' => $hwm_entry['quickAnswer'] ?? '' ) ); ?>
			<?php hwm_part( 'calculator', array( 'tool' => $hwm_slug, 'inputs' => $hwm_out['calculator_inputs'], 'label' => $hwm_entry['title'] ) ); ?>
			<?php hwm_part( 'next-steps', array( 'items' => $hwm_out['next_steps'], 'problems' => (array) ( $hwm_entry['problems'] ?? array() ) ) ); ?>
			<?php hwm_ad( 'A' ); ?>
			<div class="prose">
				<?php echo $hwm_out['html']; // phpcs:ignore -- our own content file. ?>
			</div>
			<?php hwm_ad( 'B' ); ?>
			<div class="prose">
				<?php hwm_part( 'faq', array( 'items' => $hwm_out['faq'], 'url' => hwm_url( $hwm_slug ) ) ); ?>
				<?php hwm_part( 'sources', array( 'items' => $hwm_out['sources'] ) ); ?>
				<?php hwm_part( 'author-line', array( 'entry' => $hwm_entry ) ); ?>
			</div>
			<?php hwm_part( 'related-tools', array( 'slugs' => $hwm_out['related'] ) ); ?>
		</article>
		<aside aria-label="Advertisement"><?php hwm_ad( 'S' ); ?></aside>
	</div>
</main>
<?php
get_footer();
