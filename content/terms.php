<?php
/** /terms: terms of use, estimates not advice, embeds, liability. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_brand = (string) hwm_site( 'brand' );
$hwm_email = (string) hwm_site( 'contact.email' );
?>
<p>By using <?php echo esc_html( $hwm_brand ); ?> (<?php echo esc_html( (string) hwm_site( 'domain' ) ); ?>) you agree to these terms. If you do not agree, please do not use the site.</p>

<h2>Estimates, not professional advice</h2>
<p>Our calculators and guides give planning estimates based on published formulas and sourced average data. They are not professional advice and they are not an installation design. Real results depend on your home, local codes, the exact equipment and how it is installed. Confirm sizing, code and safety questions with a licensed professional in your area before you buy or install anything.</p>
<p>Gas, venting and electrical work can cause fire, carbon monoxide poisoning, scalding or electric shock if done wrong. Our pages describe what codes and manufacturer manuals say; they do not authorize you to do that work yourself.</p>

<h2>Accuracy</h2>
<p>We work hard to keep numbers correct and sourced, and we explain our process on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">the methodology page</a>. Prices, incentives and product details change, so some figures may be out of date between our scheduled reviews. If you find an error, please tell us at <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a>.</p>

<h2>Using the site</h2>
<ul>
	<li>You may use the calculators for free, for personal or business planning.</li>
	<li>Do not attempt to disrupt the site, scrape it at a volume that slows it down, or use it to break any law.</li>
	<li>Our text, data compilations, diagrams, logo and code belong to <?php echo esc_html( $hwm_brand ); ?>. You may quote short passages with a link to the page you quoted.</li>
</ul>

<h2>Embedding our calculators</h2>
<p>You may embed our calculators on your own website using the code we provide on each calculator. You may keep or remove the credit line. Please do not alter the calculator itself, present it as your own work, or place it on pages that are illegal or misleading. We may change or stop an embed at any time.</p>

<h2>Links to other websites</h2>
<p>We link to government agencies, code bodies, manufacturers and, where labeled, retailers. We do not control those sites and are not responsible for their content or practices. Links to retailers may be affiliate links, as explained in the <a href="<?php echo esc_url( hwm_url( 'affiliate-disclosure' ) ); ?>">affiliate disclosure</a>.</p>

<h2>No warranty and limit of liability</h2>
<p>The site is provided "as is", without warranties of any kind. To the fullest extent the law allows, <?php echo esc_html( $hwm_brand ); ?> and its owner are not liable for any loss or damage arising from your use of the site or from decisions made using its results.</p>

<h2>Changes</h2>
<p>We may update these terms. The "Last updated" date below shows when they last changed. How we handle data is covered in the <a href="<?php echo esc_url( hwm_url( 'privacy-policy' ) ); ?>">privacy policy</a>.</p>
