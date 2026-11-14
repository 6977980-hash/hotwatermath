<?php
/**
 * State cost page template (PHASES.md 6c): one shared body for every /water-heater-installation-cost/{state}/ page.
 * Body: content/water-heater-installation-cost-state.php, fed by data/computed/water-heater-installation-cost-<state>.json.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_entry = hwm_current();
$hwm_slug  = (string) $hwm_entry['slug'];
$hwm_out   = hwm_content( 'water-heater-installation-cost-state', $hwm_entry );
get_header();
?>
<main id="main" class="container">
	<div class="layout layout--sidebar">
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
		<?php hwm_ad_sidebar(); ?>
	</div>
</main>
<?php
get_footer();
