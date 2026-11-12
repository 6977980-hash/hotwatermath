<?php
/**
 * Site footer: About/Contact/Privacy/Terms on every page (CLAUDE.md §6 UX basics).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="site-footer">
	<div class="container">
		<div class="site-footer__grid">
			<div>
				<h2><?php echo esc_html( (string) hwm_site( 'brand' ) ); ?></h2>
				<p><?php echo esc_html( (string) hwm_site( 'description' ) ); ?></p>
				<p>Planning estimates, not professional advice. We do not refer or recommend contractors.</p>
			</div>
			<nav aria-label="Site">
				<h2>Site</h2>
				<ul>
					<li><a href="<?php echo esc_url( hwm_url() ); ?>">Home</a></li>
					<li><a href="<?php echo esc_url( hwm_url( 'calculators' ) ); ?>">Calculators</a></li>
					<li><a href="<?php echo esc_url( hwm_url( 'guides' ) ); ?>">Guides</a></li>
					<li><a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">Methodology</a></li>
				</ul>
			</nav>
			<nav aria-label="Legal">
				<h2>Policies</h2>
				<ul>
					<?php foreach ( hwm_footer_nav() as $hwm_item ) : ?>
						<?php if ( in_array( $hwm_item[1], array( 'methodology' ), true ) ) { continue; } ?>
						<li><a href="<?php echo esc_url( hwm_url( $hwm_item[1] ) ); ?>"><?php echo esc_html( $hwm_item[0] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
		<div class="site-footer__legal">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( (string) hwm_site( 'brand' ) ); ?> &middot; <a href="mailto:<?php echo esc_attr( (string) hwm_site( 'contact.email' ) ); ?>"><?php echo esc_html( (string) hwm_site( 'contact.email' ) ); ?></a> &middot; <a class="back-to-top" href="#top">Back to top</a></p>
		</div>
	</div>
</footer>
<script>
(function(){var b=document.querySelector('.nav-toggle'),n=document.getElementById('site-nav');if(!b||!n)return;b.addEventListener('click',function(){var o=n.classList.toggle('is-open');b.setAttribute('aria-expanded',o?'true':'false');});document.querySelectorAll('a[href="#top"]').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();window.scrollTo(0,0);var s=document.querySelector('.skip-link');if(s)s.focus();});});})();
</script>
<?php wp_footer(); ?>
</body>
</html>
