<?php
/** /about: who runs the site, why, how calculators are built and checked, what we are not (CLAUDE.md §6 E-E-A-T). */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_name     = (string) hwm_site( 'author.name' );
$hwm_brand    = (string) hwm_site( 'brand' );
$hwm_linkedin = (string) ( (array) hwm_site( 'author.sameAs' ) )[0];
$hwm_email    = (string) hwm_site( 'contact.email' );
?>
<h2>Who runs Hot Water Math</h2>
<p id="ali-ahmad"><?php echo esc_html( $hwm_brand ); ?> is built and run by <strong><?php echo esc_html( $hwm_name ); ?></strong>, its founder. <?php echo esc_html( $hwm_name ); ?> is an independent developer of calculator websites and does the work behind every page here: writing the formulas, collecting the source data, building the calculators and checking each page before it goes live.</p>
<p>See the founder's professional profile on <a href="<?php echo esc_url( $hwm_linkedin ); ?>" rel="me noopener">LinkedIn</a> or write to <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a>.</p>

<h2>Why this site exists</h2>
<p>Most water heater advice online gives a rule of thumb, such as "a family of four needs a 50-gallon tank", without showing where it comes from. Many calculator sites are copies of one another and never say what numbers they use. When a water heater fails, people have to decide fast, and a wrong size or a bad repair quote is expensive.</p>
<p>Hot Water Math exists to give one clear answer for one question at a time, and to show the working: the formula, your inputs, and the source behind each default value.</p>

<h2>How the calculators are built</h2>
<ol>
	<li><strong>Start from primary sources.</strong> Flow rates, peak-hour demand values, groundwater temperatures, energy prices and efficiency ratings come from bodies such as the U.S. Department of Energy, ENERGY STAR, the Energy Information Administration and EPA WaterSense, or from manufacturer spec sheets.</li>
	<li><strong>Keep every number in one place.</strong> Each value lives in a single data file with its source and the date it was checked. Pages read numbers from those files, so the same figure can never say two different things on two pages.</li>
	<li><strong>Use one copy of each formula.</strong> The same code produces the live result in your browser and the tables printed on the page.</li>
	<li><strong>Cross-check against manufacturers.</strong> Each sizing tool is tested on at least three cases and compared with the manufacturers' own sizing guides. The results, including differences and the reasons for them, are published on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">the methodology page</a>.</li>
	<li><strong>Check before publishing.</strong> Before a page goes live we compare it with the best existing answers, open every cited source to confirm the figure, and test the page on a phone.</li>
</ol>

<h2>What we are not</h2>
<ul>
	<li><strong>We are not plumbers or electricians</strong> and we do not claim trade credentials. Our work is data and calculation.</li>
	<li><strong>We are not a referral service.</strong> We never recommend, name or link a plumber, installer or contractor, and there are no quote-request or lead forms on this site.</li>
	<li><strong>We take no paid recommendations.</strong> The site is paid for by display advertising and, on some sizing results, clearly labeled retailer links. Neither changes a result. See the <a href="<?php echo esc_url( hwm_url( 'affiliate-disclosure' ) ); ?>">affiliate disclosure</a>.</li>
</ul>
<p>For anything involving gas lines, venting or electrical circuits, our pages explain what the codes and manuals say and when the job needs a licensed professional. They are not installation instructions.</p>

<h2>Corrections</h2>
<p>If you find a mistake, a broken source or a number that does not match your manufacturer's literature, email <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a>. We check every report, fix confirmed errors and update the page's "Last updated" date. Our full process is in the <a href="<?php echo esc_url( hwm_url( 'editorial-policy' ) ); ?>">editorial policy</a>.</p>
