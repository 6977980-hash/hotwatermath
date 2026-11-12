<?php
/** /editorial-policy: sourcing standards, checks, corrections, ads/affiliate independence (CLAUDE.md §6, §7). */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_email = (string) hwm_site( 'contact.email' );
?>
<h2>What we publish</h2>
<p>Hot Water Math publishes water heater calculators and the guides that explain them. Each page targets one question and gives the short answer first. Pages are written in plain English for US homeowners and use US units: gallons, degrees Fahrenheit, BTU per hour, kilowatts and therms.</p>

<h2>How we choose sources</h2>
<ul>
	<li>We use primary sources: U.S. government agencies such as the Department of Energy, ENERGY STAR, the Energy Information Administration and EPA WaterSense; building and safety codes; and manufacturer spec sheets, manuals and sizing guides.</li>
	<li>Every number that is not pure arithmetic is stored once, in a data file, with its source, a link and the date we checked it. Pages print the number from that file. We do not type numbers into the text by hand.</li>
	<li>Other websites' cost guides are used only to sanity-check our ranges, never as the source we quote.</li>
	<li>We never cite from memory or from a search result snippet. We open the source and read it.</li>
</ul>

<h2>How every page is checked before it goes live</h2>
<ol>
	<li><strong>Compare with the best answers.</strong> We read the top search results for the page's question and make sure our page covers every useful point, plus at least two things they miss. We never copy their wording or structure.</li>
	<li><strong>Open every source.</strong> We confirm each link loads and that the figure on our page matches what the source says that day, and we record the date.</li>
	<li><strong>Test the live page.</strong> We check it on a phone, run the calculator against hand-worked cases and validate the structured data.</li>
	<li><strong>Owner sign-off.</strong> A page stays hidden from search engines until the founder has read it and approved it.</li>
</ol>

<h2>Safety topics</h2>
<p>Pages that touch gas, electricity, scalding or Legionella restate official sources only, such as fuel gas and electrical codes, CPSC, CDC, DOE and manufacturer manuals. They explain what the problem is, what the code or manual requires and when the job needs a licensed professional. They do not give step-by-step instructions for gas or live electrical work.</p>

<h2>Who checks the content</h2>
<p>Pages are written and checked by the founder, <a href="<?php echo esc_url( hwm_url( 'about' ) ); ?>"><?php echo esc_html( (string) hwm_site( 'author.name' ) ); ?></a>, a calculator developer. We do not have a plumber or engineer on staff, and we will never show a "reviewed by" line that is not true. If an outside professional checks a page in future, the page will say who checked it and when.</p>

<h2>Corrections</h2>
<p>Email <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a> with the page and what looks wrong. We check every report against the source. When we confirm an error we fix it, update the page's "Last updated" date, and re-run any calculator cross-check it affects. We only change "Last updated" when the content really changes.</p>

<h2>Advertising and affiliate independence</h2>
<ul>
	<li>The site is paid for by display advertising and, on some sizing results, labeled retailer affiliate links. Details are in the <a href="<?php echo esc_url( hwm_url( 'affiliate-disclosure' ) ); ?>">affiliate disclosure</a>.</li>
	<li>Advertisers and retailers never see a page before it is published and have no say in any result, table or recommendation.</li>
	<li>Ads are labeled "Advertisement" and are never placed between a page's title and its calculator.</li>
	<li>We do not accept payment from plumbers, installers or contractors, we do not sell leads, and we do not recommend any service provider.</li>
</ul>
<p>How the numbers themselves are calculated is explained on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">the methodology page</a>.</p>
