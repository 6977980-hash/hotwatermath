<?php
/**
 * /privacy-policy: AdSense/GA4 compliant (CLAUDE.md §7): Google named as a third-party vendor,
 * advertising cookies explained, opt-out links, consent message, link to Google's partner-sites page.
 * Sentences about GA4/AdSense switch on automatically when their IDs are set in data/site.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_email   = (string) hwm_site( 'contact.email' );
$hwm_brand   = (string) hwm_site( 'brand' );
$hwm_ga_on   = '' !== (string) hwm_site( 'integrations.ga4Id' );
$hwm_ads_on  = '' !== (string) hwm_site( 'integrations.adsenseClient' );
?>
<p>This policy explains what <?php echo esc_html( $hwm_brand ); ?> (<?php echo esc_html( (string) hwm_site( 'domain' ) ); ?>) collects when you use the site, who else is involved, and the choices you have. The site is run by <?php echo esc_html( (string) hwm_site( 'author.name' ) ); ?>. Questions: <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a>.</p>

<h2>What you type into a calculator</h2>
<p>Our calculators run in your web browser. The values you enter, such as your household size, state or serial number, are not sent to us or stored by us. If you use "Share link", your inputs are placed in the link itself, so anyone you send the link to can see them.</p>

<h2>Information collected automatically</h2>
<ul>
	<li><strong>Server logs.</strong> Like every website, our web host receives technical data with each request: IP address, browser type, the page requested, the referring page and the time. The host uses it to deliver pages, keep the site secure and fix problems. We do not use it to identify you.</li>
	<li><strong>Caching.</strong> Pages are served from a cache to make them fast. The cache does not set tracking cookies for visitors.</li>
	<li><strong>No accounts, no comments, no forms.</strong> You cannot create an account or post comments, and there is no contact form. If you email us, we receive your email address and message and use them only to reply.</li>
</ul>

<h2>Analytics: Google Analytics</h2>
<p><?php echo $hwm_ga_on ? 'We use' : 'We plan to use'; ?> Google Analytics 4, a service from Google LLC, to count visits and see which pages and calculators are used. Google Analytics uses cookies and similar technology to collect information such as pages viewed, approximate location, device type and how you reached the site. When a calculator gives a result, we may record a general event, for example which tool was used and a broad range of the inputs, never the exact values you typed.</p>
<p>Where the law requires your consent, such as in the European Economic Area, the UK and Switzerland, analytics cookies stay off until you agree (Google Consent Mode). You can also block Google Analytics with the <a href="https://tools.google.com/dlpage/gaoptout" rel="noopener">Google Analytics opt-out browser add-on</a>. Read <a href="https://support.google.com/analytics/answer/6004245" rel="noopener">how Google Analytics safeguards data</a>.</p>

<h2>Advertising: Google AdSense</h2>
<p><?php echo $hwm_ads_on ? 'This site shows' : 'Once approved, this site will show'; ?> ads served by Google AdSense. Google is a third-party vendor, and it uses cookies to serve ads on this site.</p>
<ul>
	<li>Google's use of advertising cookies enables it and its partners to serve ads to you based on your visit to this site and/or other sites on the internet.</li>
	<li>Other third-party vendors and ad networks may also serve ads on this site and may use cookies to do so.</li>
	<li>You may opt out of personalized advertising by visiting Google's <a href="https://myadcenter.google.com/" rel="noopener">My Ad Center (Ads Settings)</a>. In Europe you can also manage choices for many ad companies at <a href="https://www.youronlinechoices.eu/" rel="noopener">youronlinechoices.eu</a>.</li>
	<li>Read <a href="https://policies.google.com/technologies/partner-sites" rel="noopener">How Google uses information from sites or apps that use our services</a> and <a href="https://policies.google.com/technologies/ads" rel="noopener">how Google uses cookies in advertising</a>.</li>
</ul>
<p>Ads and advertisers never affect what our calculators say. See the <a href="<?php echo esc_url( hwm_url( 'affiliate-disclosure' ) ); ?>">affiliate disclosure</a> and <a href="<?php echo esc_url( hwm_url( 'editorial-policy' ) ); ?>">editorial policy</a>.</p>

<h2>Your consent choices</h2>
<p>Consent messages on this site are provided by Google's own consent management tool (Google AdSense "Privacy &amp; messaging"), which is certified for this purpose. Visitors from the European Economic Area, the UK and Switzerland are asked before advertising and analytics cookies are used; until you choose, Google tags run with consent set to "denied". Visitors from US states with privacy laws are shown an option to opt out of the sale or sharing of personal information for targeted advertising. You can change your choice at any time through the privacy link shown by that message.</p>
<p>You can also delete or block cookies in your browser settings. The calculators still work without cookies.</p>

<h2>Affiliate links</h2>
<p>Some sizing results may show labeled links to retailers. If you click one, the retailer may set its own cookies to record that you came from our site, under its own privacy policy. We do not receive your name or what you bought.</p>

<h2>Embedded calculators</h2>
<p>If you use one of our calculators embedded on another website, that website's own privacy policy also applies to your visit there.</p>

<h2>Children</h2>
<p>This site is meant for adults planning home purchases. It is not directed at children under 13, and we do not knowingly collect information from them.</p>

<h2>Your rights</h2>
<p>Depending on where you live, you may have the right to ask what personal information we hold about you, to correct it or to delete it. Because we do not run accounts or forms, we usually hold nothing beyond any emails you sent us. Write to <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a> and we will respond. For data held by Google, use your Google account settings or <a href="https://policies.google.com/privacy" rel="noopener">Google's privacy policy</a>.</p>

<h2>Changes to this policy</h2>
<p>If we add a new service that collects data, we will update this page first and change the "Last updated" date below.</p>
