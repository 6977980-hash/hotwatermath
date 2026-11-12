<?php
/**
 * 404: search + links to hubs (CLAUDE.md §6).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! is_404() ) {
	hwm_force_404();
}
get_header();
?>
<main id="main" class="container">
	<div class="prose">
		<h1>Page not found</h1>
		<p class="page-subtitle">The page you asked for does not exist or has moved.</p>
		<form class="search-form" role="search" method="get" action="<?php echo esc_url( hwm_url() ); ?>">
			<label class="visually-hidden" for="search-404">Search this site</label>
			<input id="search-404" type="search" name="s" placeholder="Search calculators and guides">
			<button class="btn" type="submit">Search</button>
		</form>
		<h2>Try one of these</h2>
		<ul>
			<li><a href="<?php echo esc_url( hwm_url( 'calculators' ) ); ?>">All water heater calculators</a></li>
			<li><a href="<?php echo esc_url( hwm_url( 'guides' ) ); ?>">All water heater guides</a></li>
			<li><a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">How our calculators work</a></li>
			<li><a href="<?php echo esc_url( hwm_url() ); ?>">Home page</a></li>
		</ul>
	</div>
</main>
<?php
get_footer();
