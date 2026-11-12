<?php
/** /affiliate-disclosure: how the site earns money; rel="sponsored" rule; no contractor money (CLAUDE.md §7). */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_email = (string) hwm_site( 'contact.email' );
?>
<h2>How this site is paid for</h2>
<p>Hot Water Math is free to use. It is paid for in two ways:</p>
<ol>
	<li><strong>Display advertising.</strong> Ads are served by Google AdSense (or a similar ad network later). Ads are labeled "Advertisement". We do not choose which ads appear, and advertisers have no say in our content.</li>
	<li><strong>Retailer affiliate links.</strong> On some sizing results, a block titled "Units in this size class" may list types of water heaters that match your result, with links to retailers such as Amazon or Home Depot. If you buy through one of these links, we may earn a small commission at no extra cost to you.</li>
</ol>

<h2>Rules we follow</h2>
<ul>
	<li>Affiliate links appear only after a calculator result, only on sizing pages, and always under the label "Affiliate links".</li>
	<li>Every affiliate link is marked for search engines as sponsored (<code>rel="sponsored"</code>).</li>
	<li>The block lists product categories and specifications that match your result, such as tank size and first hour rating. It is not a ranking of brands and not a review.</li>
	<li>No commission, ad or payment ever changes a calculator result, a table, or what a page says.</li>
	<li>We take no money from plumbers, installers or contractors, we do not sell leads, and we do not recommend any service provider.</li>
</ul>

<h2>As an Amazon Associate</h2>
<p>If we join the Amazon Associates program, this statement applies: as an Amazon Associate we earn from qualifying purchases. Until then, no Amazon links appear on the site.</p>

<h2>Questions</h2>
<p>Email <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a>. Our full independence rules are in the <a href="<?php echo esc_url( hwm_url( 'editorial-policy' ) ); ?>">editorial policy</a>, and data handling for affiliate clicks is covered in the <a href="<?php echo esc_url( hwm_url( 'privacy-policy' ) ); ?>">privacy policy</a>. Read <a href="<?php echo esc_url( hwm_url( 'about' ) ); ?>">who runs this site</a>.</p>
