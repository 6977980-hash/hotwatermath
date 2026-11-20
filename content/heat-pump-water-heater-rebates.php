<?php
/**
 * /heat-pump-water-heater-rebates/ (tools added 2026-11-20): rebate finder by state.
 * Math: assets/js/formulas.js heehrRebate via assets/js/rebate-finder.js.
 * Numbers: data/computed/heat-pump-water-heater-rebates.json, data/heehr-states.json, data/hpwh-rebates.json, data/incentives.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/heat-pump-water-heater-rebates';
$hwm_h   = 'heehr-states';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_def = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_fed = (array) hwm_raw( $hwm_h, 'federal' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_rf  = (array) hwm_raw( 'incentives', 'rebateFinder' );
$hwm_mi  = (array) hwm_raw( $hwm_c, 'client.costByState.MI' );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="rb-state">Your state</label>
		<select id="rb-state" name="state">
			<?php foreach ( (array) ( $hwm_cl['states'] ?? array() ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"<?php selected( (string) $hwm_s['id'], (string) ( $hwm_def['state'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="rb-income">Household income <span class="hint">Compared with your area's median income (AMI).</span></label>
		<select id="rb-income" name="income">
			<?php foreach ( (array) ( $hwm_fed['incomeRules'] ?? array() ) as $hwm_r ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_r['id'] ); ?>"<?php selected( (string) $hwm_r['id'], (string) ( $hwm_def['income'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_r['label'] ); ?></option>
			<?php endforeach; ?>
			<option value="unsure">Not sure</option>
		</select>
	</div>
	<div class="field">
		<label for="rb-cost">Project cost ($) <span class="hint">Optional. Blank = our typical installed price in your state.</span></label>
		<input id="rb-cost" name="cost" type="text" inputmode="decimal" maxlength="7" placeholder="<?php echo esc_attr( number_format( (float) ( $hwm_mi['typical'] ?? 0 ) ) ); ?>" autocomplete="off">
	</div>
</div>
<p class="hint">Utility rebates go to that utility's customers; for programs we have not checked, search <?php echo $hwm_ext( (string) ( $hwm_rf['sourceUrl'] ?? '' ), 'ENERGY STAR\'s Rebate Finder' ); // phpcs:ignore ?> by ZIP code.</p>
<?php
echo hwm_tool_data_script( 'hwm-rb-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'heat-pump-water-heater-cost', 'text' => 'Heat pump water heater cost and savings', 'why' => 'payback in your state, with the rebate taken off' ),
	array( 'slug' => 'water-heater-quote-checker', 'text' => 'Water heater quote checker', 'why' => 'check the installer\'s price before you reserve' ),
	array( 'slug' => 'water-heater-size-calculator', 'text' => 'Water heater size calculator', 'why' => 'pick 50, 65 or 80 gallons' ),
);
$related = array( 'heat-pump-water-heater-cost', 'water-heater-cost-calculator', 'gas-vs-electric-water-heater', 'water-heater-installation-cost' );
?>
<p class="notice"><strong>How this list is checked.</strong> We list a state program only if the National Association of State Energy Officials (NASEO) shows it as launched in its table updated <?php echo $hwm_v( 'naseoUpdated' ); // phpcs:ignore ?>, and we opened each state's page on <?php echo $hwm_v( 'checked' ); // phpcs:ignore ?>. Every utility rebate below was opened again that day and still shows the quoted offer. Amounts come from ENERGY STAR's program page, not from blogs.</p>

<h2>Are there rebates for heat pump water heaters in 2026?</h2>
<p>Yes, but not from the IRS. The federal tax credit covered heat pump water heaters placed in service through <?php echo $hwm_v( 'creditEnd' ); // phpcs:ignore ?>. What is left is the federal High-Efficiency Electric Home Rebate (HEEHR), up to $<?php echo $hwm_v( 'cap' ); // phpcs:ignore ?> for a heat pump water heater, which states run for low- and moderate-income homes, plus rebates from some utilities.</p>
<p>The HEEHR program is open in <?php echo $hwm_v( 'openStates' ); // phpcs:ignore ?> states and Washington, DC, on NASEO's list, and fully reserved in California. The U.S. Department of Energy says only that the rebates "are now available in select states" and points you to your State Energy Office, which is why we list each program by name.</p>
<dl>
	<dt>HEEHR (also called HEAR)</dt>
	<dd>The High-Efficiency Electric Home Rebate, from section 50122 of the Inflation Reduction Act. The federal government funds it; each state decides who qualifies and how to apply. Many states call it the Home Electrification and Appliance Rebate.</dd>
	<dt>Area median income (AMI)</dt>
	<dd>The middle household income for your county and household size. HEEHR uses it to set your share: below 80% of AMI, or 80% to 150%.</dd>
</dl>

<h2>How much is the HEEHR rebate for a heat pump water heater?</h2>
<p>Up to $<?php echo $hwm_v( 'cap' ); // phpcs:ignore ?>. ENERGY STAR's program page sets the share by income: up to 100% of the cost below 80% of area median income, and no more than 50% of the cost from 80% to 150%. Above 150% you are not eligible. The heater must be ENERGY STAR certified, and the household total across all upgrades is capped at $<?php echo $hwm_v( 'combined' ); // phpcs:ignore ?>.</p>
<?php echo hwm_table( $hwm_c, 'income' ); // phpcs:ignore -- escaped in the part. ?>
<p>States may narrow these rules. Washington, DC, serves only households below 80% of area median income until 2027, and New Mexico only low-income homes; the calculator applies both limits.</p>

<h2>Which states have heat pump water heater rebates?</h2>
<p>These are the <?php echo $hwm_v( 'programCount' ); // phpcs:ignore ?> HEEHR programs in NASEO's launch table. "Open" means launched and nothing on the state page we opened says it has closed; funds can still run out, so check the page before you buy.</p>
<?php echo hwm_table( $hwm_c, 'programs' ); // phpcs:ignore ?>
<p>If your state is not listed, it has not launched a HEEHR program in NASEO's table. Your utility may still pay a rebate.</p>

<h2>Which utilities give a heat pump water heater rebate?</h2>
<p>We checked <?php echo $hwm_v( 'utilityCount' ); // phpcs:ignore ?> utility programs in <?php echo $hwm_v( 'utilityStates' ); // phpcs:ignore ?> states, the states where we publish installation cost pages. The largest is <?php echo esc_html( (string) hwm_raw( $hwm_c, 'figures.maxUtility.sponsor' ) ); ?> at up to $<?php echo esc_html( (string) hwm_raw( $hwm_c, 'figures.maxUtility.usd' ) ); ?>, for a switch from gas. You must be the utility's customer, and most require an ENERGY STAR model.</p>
<?php echo hwm_table( $hwm_c, 'utilities' ); // phpcs:ignore ?>
<p>Thousands of smaller utilities and cities run their own programs. <?php echo $hwm_ext( (string) ( $hwm_rf['sourceUrl'] ?? '' ), 'ENERGY STAR\'s Rebate Finder' ); // phpcs:ignore ?> searches them by ZIP code; we do not repeat what it already does well.</p>

<h2>Worked example: a Michigan household</h2>
<p>The default above is a household in Michigan at 80% to 150% of area median income, replacing an electric tank with a 50-gallon heat pump water heater.</p>
<ol>
	<li>Our typical installed price in Michigan, permit included: $<?php echo $hwm_v( 'miCost' ); // phpcs:ignore ?>.</li>
	<li>HEEHR share at that income: no more than 50% of the cost, capped at $<?php echo $hwm_v( 'cap' ); // phpcs:ignore ?>.</li>
	<li>Rebate: the smaller of $<?php echo $hwm_v( 'cap' ); // phpcs:ignore ?> and half of $<?php echo $hwm_v( 'miCost' ); // phpcs:ignore ?>, so $<?php echo $hwm_v( 'miHeehr' ); // phpcs:ignore ?>.</li>
	<li>DTE Energy and Consumers Energy also offer instant discounts to their customers; their pages do not name the amount, so we count $0 for them.</li>
</ol>
<p>Take the rebate off the price and run the payback in our <?php echo hwm_link( 'heat-pump-water-heater-cost', 'heat pump water heater cost and savings calculator' ); // phpcs:ignore ?>.</p>

<h2>Common mistakes</h2>
<ul>
	<li><strong>Buying first, applying later.</strong> California's program states that rebates "are not available for equipment, appliances, and related products purchased or installed before a HEEHRA rebate is reserved and approved". Reserve first.</li>
	<li><strong>Counting on the federal tax credit.</strong> It ended for installs after <?php echo $hwm_v( 'creditEnd' ); // phpcs:ignore ?>. A contractor quoting a 30% credit for a 2026 install is out of date.</li>
	<li><strong>Adding every rebate together.</strong> Whether a utility rebate stacks with HEEHR is set by each program. Ask both before you count on the total.</li>
	<li><strong>Answering a rebate call.</strong> California warns that "bad actors are contacting homeowners" about these rebates. Apply only through the state or utility page itself.</li>
</ul>

<h2>When to call a pro</h2>
<p>Many HEEHR programs pay through a contractor enrolled in the program, so the installer is part of the application; Arizona and Georgia also have a retail or do-it-yourself path. A heat pump water heater also needs room, air and a condensate drain, and replacing a gas heater adds a 240-volt circuit. Have a licensed installer check the space and the panel before you reserve.</p>

<h2>Limitations</h2>
<p>We check the program list and every utility page about every three months, and programs open, pause and run out of money between checks. We list only the utilities in the <?php echo $hwm_v( 'utilityStates' ); // phpcs:ignore ?> states we cover, plus HEEHR in every state. The rebate shown for "not sure" is the most any household could get. Your income band is set by the program, not by us.</p>
<?php
$faq = array(
	array(
		'q' => 'Is there a tax credit for a heat pump water heater in 2026?',
		'a' => 'No. The IRS credit of 30% up to $2,000 covered heat pump water heaters placed in service through ' . hwm_value( $hwm_c, 'figures.creditEnd' ) . '. For a heater installed now, the federal help is the state-run HEEHR rebate, if your state has one and your income qualifies.',
	),
	array(
		'q' => 'How much is the federal rebate for a heat pump water heater?',
		'a' => 'Up to $' . hwm_value( $hwm_c, 'figures.cap' ) . ' through HEEHR. Below 80% of area median income it can cover the whole cost up to that cap; from 80% to 150% it pays no more than half. Above 150% you do not qualify.',
	),
	array(
		'q' => 'Which states have the HEEHR rebate?',
		'a' => 'NASEO lists ' . hwm_value( $hwm_c, 'figures.programCount' ) . ' launched programs: Arizona, California, Colorado, DC, Georgia, Indiana, Maine, Michigan, New Mexico, New York, North Carolina, Rhode Island, West Virginia and Wisconsin. California\'s single-family funds are fully reserved. Pick your state above for the details.',
	),
	array(
		'q' => 'Can I get a utility rebate and HEEHR together?',
		'a' => 'Sometimes. Each program sets its own rule on combining. Ask the utility and the state program before you buy, and keep the paperwork of both. The finder shows both so you can ask about the total.',
	),
	array(
		'q' => 'Do I need an ENERGY STAR heat pump water heater for the rebate?',
		'a' => 'For HEEHR, yes: ENERGY STAR lists certification as required. Most utility programs we checked ask for ENERGY STAR too, and some set a minimum efficiency such as a UEF of 2.2 or higher.',
	),
	array(
		'q' => 'What if my state has no HEEHR program?',
		'a' => 'Check your utility, then search ENERGY STAR\'s Rebate Finder by ZIP code. Your State Energy Office can say whether a HEEHR program is coming. Our heat pump water heater cost calculator shows the payback on energy savings alone for your state.',
	),
);
$sources = array(
	array( 'publisher' => 'NASEO', 'title' => 'Home Energy Rebates: State and Territory Energy Office Program Launches', 'url' => (string) hwm_raw( $hwm_h, 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_h, 'meta.sourceDate' ) ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'High-Efficiency Electric Home Rebate (HEEHR) Program: eligible products and maximum rebate amounts', 'url' => (string) ( $hwm_fed['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_fed['checked'] ?? '' ) ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Home Energy Rebates Program', 'url' => (string) ( $hwm_fed['doeUrl'] ?? '' ), 'accessed' => (string) ( $hwm_fed['checked'] ?? '' ) ),
	array( 'publisher' => 'Internal Revenue Service', 'title' => 'Energy Efficient Home Improvement Credit', 'url' => (string) hwm_raw( 'incentives', 'meta.sourceUrl' ), 'accessed' => (string) ( $hwm_fed['checked'] ?? '' ) ),
	array( 'publisher' => 'California Energy Commission', 'title' => 'Inflation Reduction Act Residential Energy Rebate Programs', 'url' => (string) ( $hwm_fed['notRetroactiveUrl'] ?? '' ), 'accessed' => (string) ( $hwm_fed['checked'] ?? '' ) ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'Rebate Finder', 'url' => (string) ( $hwm_rf['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_fed['checked'] ?? '' ) ),
);
