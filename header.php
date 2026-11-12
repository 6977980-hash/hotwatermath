<?php
/**
 * Site header: <head>, skip link, logo and primary navigation (≤7 links).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_entry   = hwm_current();
$hwm_current_slug = $hwm_entry['slug'] ?? null;
?><!doctype html>
<html lang="<?php echo esc_attr( (string) hwm_site( 'language' ) ); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.className+=' js';</script>
<?php wp_head(); ?>
</head>
<body id="top" <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
	<div class="container site-header__inner">
		<a class="site-logo" href="<?php echo esc_url( hwm_url() ); ?>" aria-label="<?php echo esc_attr( (string) hwm_site( 'brand' ) ); ?> home">
			<?php echo file_get_contents( HWM_DIR . '/assets/brand/logo.svg' ); // phpcs:ignore -- our own static SVG. ?>
		</a>
		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">Menu</button>
		<nav class="site-nav" id="site-nav" aria-label="Main">
			<ul>
				<?php foreach ( hwm_nav() as $hwm_item ) : ?>
					<li><a href="<?php echo esc_url( hwm_url( $hwm_item[1] ) ); ?>"<?php echo $hwm_current_slug === $hwm_item[1] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $hwm_item[0] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</header>
