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
					<?php foreach ( array( 'data' => 'Data downloads', 'embed-calculators' => 'Embed a calculator' ) as $hwm_fslug => $hwm_flabel ) : ?>
						<?php if ( hwm_is_live( hwm_page( $hwm_fslug ) ) ) : ?>
							<li><a href="<?php echo esc_url( hwm_url( $hwm_fslug ) ); ?>"><?php echo esc_html( $hwm_flabel ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
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
(function(){var n=document.querySelector('.toc-rail');if(!n)return;var L={},H=[],c=null,t=false;n.querySelectorAll('a').forEach(function(a){var h=document.getElementById(a.hash.slice(1));if(h){L[h.id]=a;H.push(h);}});function u(){t=false;if(!n.offsetParent)return;var y=window.innerHeight*0.3,id=null;for(var i=0;i<H.length;i++){if(H[i].getBoundingClientRect().top<=y)id=H[i].id;else break;}var a=id?L[id]:null;if(a===c)return;if(c)c.removeAttribute('aria-current');c=a;if(a){a.setAttribute('aria-current','true');if(n.scrollHeight>n.clientHeight)n.scrollTop=Math.max(0,a.offsetTop-n.clientHeight/3);}}window.addEventListener('scroll',function(){if(!t){t=true;requestAnimationFrame(u);}},{passive:true});u();})();
</script>
<?php wp_footer(); ?>
</body>
</html>
