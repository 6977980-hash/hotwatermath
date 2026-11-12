<?php
/** /contact: email only (no form), what we can and cannot help with, LinkedIn link (CLAUDE.md §6). */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_email    = (string) hwm_site( 'contact.email' );
$hwm_linkedin = (string) ( (array) hwm_site( 'author.sameAs' ) )[0];
?>
<h2>Email</h2>
<p class="notice"><strong><a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a></strong></p>
<p>Email is the only way to reach us. There is no contact form on this site, so nothing you send passes through a third-party form service. We read every message and aim to reply within a few days.</p>

<h2>What to write to us about</h2>
<ul>
	<li><strong>Errors:</strong> a number, source or result that looks wrong. Please include the page link and, if you can, the source you are comparing with.</li>
	<li><strong>How a calculator works:</strong> questions about a formula or a default value.</li>
	<li><strong>Sources:</strong> a better or newer primary source we should use.</li>
	<li><strong>Embedding:</strong> questions about putting one of our calculators on your website.</li>
</ul>

<h2>What we cannot help with</h2>
<ul>
	<li>Advice on a specific installation, diagnosis of your unit, or code questions for your town. For those, talk to a licensed professional in your area.</li>
	<li>Contractor recommendations. We do not refer or recommend plumbers or installers.</li>
	<li>Paid placements, sponsored posts or link exchanges.</li>
</ul>

<h2>Who you are writing to</h2>
<p>Messages go to the founder, <?php echo esc_html( (string) hwm_site( 'author.name' ) ); ?>. You can also find the founder on <a href="<?php echo esc_url( $hwm_linkedin ); ?>" rel="me noopener">LinkedIn</a>. Read more <a href="<?php echo esc_url( hwm_url( 'about' ) ); ?>">about Hot Water Math</a>, our <a href="<?php echo esc_url( hwm_url( 'editorial-policy' ) ); ?>">corrections process</a>, and how we handle your data in the <a href="<?php echo esc_url( hwm_url( 'privacy-policy' ) ); ?>">privacy policy</a>.</p>
