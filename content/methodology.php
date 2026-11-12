<?php
/**
 * /methodology: structure for Phase 0. Formulas, data sources and cross-check tables
 * are filled in Phases 1–4 (no placeholder numbers, ever).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_tools = hwm_pages_of( 'tool', array( 'live', 'draft', 'planned' ) );
$hwm_email = (string) hwm_site( 'contact.email' );
?>
<div class="notice notice--warn">
	<p><strong>This page is being built.</strong> The principles below apply now. Each tool's formula, data sources and manufacturer cross-check are added when that tool is built, starting with the data layer in Phase 1. Nothing here is filled with sample numbers.</p>
</div>

<h2>Principles behind every calculator</h2>
<dl>
	<dt>Physics first</dt>
	<dd>Heating water follows fixed physical relationships: the weight of a gallon of water, the energy in a kilowatt-hour or a therm, and the temperature rise from the water coming into your home to the water coming out of the heater. We use those relationships directly instead of rules of thumb.</dd>
	<dt>Sourced defaults</dt>
	<dd>Anything that is not physics, such as fixture flow rates, groundwater temperature, energy prices, efficiency ratings, lifespans and costs, comes from a named source with the date we checked it. Every default is shown in the calculator, labeled as assumed, and you can change it.</dd>
	<dt>One copy of each formula</dt>
	<dd>The same code produces the live result in your browser and every table printed on our pages, so a page can never disagree with its own calculator.</dd>
	<dt>Visible working</dt>
	<dd>Each result has a "How we calculated this" panel that repeats the formula with your numbers in it.</dd>
</dl>

<h2>Data sources we use</h2>
<p>We prefer primary sources in this order:</p>
<ol>
	<li>U.S. government agencies: the Department of Energy (energy.gov), ENERGY STAR, the Energy Information Administration (EIA) and EPA WaterSense.</li>
	<li>Building and safety codes, cited in general terms: the International Energy Conservation Code, the International Fuel Gas Code and NFPA 54, and the National Electrical Code.</li>
	<li>Manufacturer spec sheets, installation manuals and sizing guides.</li>
</ol>
<p>Cost guides from other websites are used only to sanity-check our ranges. They are never quoted as the source of a number. The full list of data files, each with its source, link and check date, is published here in Phase 1.</p>

<h2>Calculators and their cross-checks</h2>
<p>For each sizing tool we run at least three test cases and compare our answer with the manufacturer's own sizing guide for the same inputs. We publish the comparison, including any difference and the reason for it.</p>
<?php foreach ( $hwm_tools as $hwm_p ) : ?>
	<section id="<?php echo esc_attr( (string) $hwm_p['slug'] ); ?>">
		<h3><?php echo esc_html( (string) $hwm_p['title'] ); ?></h3>
		<p><?php echo esc_html( (string) ( $hwm_p['summary'] ?? '' ) ); ?></p>
		<p><em>Formula, data sources and cross-check table: to be completed in Phase <?php echo esc_html( (string) ( $hwm_p['phase'] ?? '1' ) ); ?>, when this calculator is built.</em></p>
	</section>
<?php endforeach; ?>

<h2>How often we review the numbers</h2>
<ul>
	<li><strong>Every three months:</strong> energy prices, equipment and installation cost ranges, and tax credits or rebates.</li>
	<li><strong>Once a year:</strong> everything else, including flow rates, groundwater temperatures, lifespans and serial number formats.</li>
	<li><strong>Whenever a formula or data file changes:</strong> the manufacturer cross-checks are run again and the affected pages get a new "Last updated" date.</li>
</ul>

<h2>Limitations</h2>
<ul>
	<li>Results are planning estimates. Your home's pipes, water pressure, local code and the exact model you buy all change the real answer.</li>
	<li>Averages such as groundwater temperature by state hide local variation. Use your own measured value when you have one.</li>
	<li>Cost ranges cannot account for every job. A quote from a licensed installer who has seen your home will always be more precise.</li>
	<li>We do not give advice on gas, venting or electrical work beyond what the codes and manufacturer manuals say. Those jobs need a licensed professional.</li>
</ul>
<p>Spotted a number that does not match your manufacturer's literature? Email <a href="mailto:<?php echo esc_attr( $hwm_email ); ?>"><?php echo esc_html( $hwm_email ); ?></a>. See also <a href="<?php echo esc_url( hwm_url( 'editorial-policy' ) ); ?>">our editorial policy</a> and <a href="<?php echo esc_url( hwm_url( 'about' ) ); ?>">who builds these calculators</a>.</p>
