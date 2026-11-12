<?php
/**
 * Site search over the page registry (page bodies live in the repo, not the database). Noindex.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_q       = trim( (string) get_search_query( false ) );
$hwm_results = hwm_search( $hwm_q );
get_header();
?>
<main id="main" class="container">
	<div class="prose">
		<h1>Search</h1>
		<form class="search-form" role="search" method="get" action="<?php echo esc_url( hwm_url() ); ?>">
			<label class="visually-hidden" for="search-page">Search this site</label>
			<input id="search-page" type="search" name="s" value="<?php echo esc_attr( $hwm_q ); ?>" placeholder="Search calculators and guides">
			<button class="btn" type="submit">Search</button>
		</form>
		<?php if ( '' === $hwm_q ) : ?>
			<p>Type a word such as "tankless" or "serial number".</p>
		<?php elseif ( ! $hwm_results ) : ?>
			<p>Nothing matched "<?php echo esc_html( $hwm_q ); ?>". Browse <a href="<?php echo esc_url( hwm_url( 'calculators' ) ); ?>">all calculators</a> or <a href="<?php echo esc_url( hwm_url( 'guides' ) ); ?>">all guides</a>.</p>
		<?php else : ?>
			<h2><?php echo esc_html( count( $hwm_results ) . ' result' . ( 1 === count( $hwm_results ) ? '' : 's' ) . ' for "' . $hwm_q . '"' ); ?></h2>
			<ul>
				<?php foreach ( $hwm_results as $hwm_p ) : ?>
					<li><a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a>: <?php echo esc_html( (string) ( $hwm_p['summary'] ?? $hwm_p['description'] ?? '' ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
