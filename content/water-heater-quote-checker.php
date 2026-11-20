<?php
/**
 * /water-heater-quote-checker/ (tools added 2026-11-20): is a water heater installation quote fair?
 * Math: assets/js/formulas.js quoteBand, lineBand and the shared cost model (cost-model.js) via assets/js/quote-checker.js.
 * Numbers: data/computed/water-heater-quote-checker.json, data/computed/water-heater-cost-calculator.json, data/quote-check.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-quote-checker';
$hwm_q   = 'quote-check';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_ex  = (array) hwm_raw( $hwm_c, 'figures.examples' );
$hwm_ck  = (array) hwm_raw( $hwm_c, 'figures.checklist' );
$hwm_cf  = (array) hwm_raw( 'computed/water-heater-cost-calculator', 'figures' );
$hwm_tx  = (array) ( $hwm_ex['tx-gas50'] ?? array() );
$hwm_fl  = (array) ( $hwm_ex['fl-el50'] ?? array() );
$hwm_ca  = (array) ( $hwm_ex['ca-hp50'] ?? array() );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="qc-total">Quote total ($) <span class="hint">The full price on the quote, before any rebate.</span></label>
		<input id="qc-total" name="total" type="text" inputmode="decimal" maxlength="7" autocomplete="off" placeholder="e.g. 2,400">
	</div>
</div>
<?php hwm_part( 'cost-inputs', array( 'legend' => 'What the quote includes' ) ); ?>
<details class="disclosure">
	<summary>Does the quote list line items? Check them too</summary>
	<div class="field-grid">
		<div class="field">
			<label for="qc-unit">Water heater (unit) price ($) <span class="hint">Optional.</span></label>
			<input id="qc-unit" name="unitPrice" type="text" inputmode="decimal" maxlength="6" autocomplete="off">
		</div>
		<div class="field">
			<label for="qc-labor">Labor ($) <span class="hint">Optional.</span></label>
			<input id="qc-labor" name="laborPrice" type="text" inputmode="decimal" maxlength="6" autocomplete="off">
		</div>
		<div class="field">
			<label for="qc-permit">Permit and haul-away ($) <span class="hint">Optional; tick it above too.</span></label>
			<input id="qc-permit" name="permitPrice" type="text" inputmode="decimal" maxlength="5" autocomplete="off">
		</div>
	</div>
</details>
<?php
echo hwm_tool_data_script( 'hwm-qc-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-cost-calculator', 'text' => 'Water heater cost calculator', 'why' => 'see every line of the estimate for your job' ),
	array( 'slug' => 'water-heater-model-number-lookup', 'text' => 'Water heater model number lookup', 'why' => 'check the size and warranty of the unit you are offered' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'if the quote is for a repair, not a new heater' ),
);
$related = array( 'water-heater-cost-calculator', 'water-heater-installation-cost', 'heat-pump-water-heater-rebates', 'repair-or-replace-water-heater' );
?>
<p class="notice"><strong>Where our range comes from.</strong> The checker prices your job with the same cost model as our <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?>: unit prices from current retail listings, labor and extras anchored to the U.S. Department of Energy's 2024 water heater rules, and a price factor for your state. That model is checked against DOE's installed costs on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-cost-calculator' ); ?>">our methodology page</a>. We do not sell installs or pass your quote to anyone.</p>

<h2>How do I know if a water heater quote is fair?</h2>
<p>Compare it with the price of the same job in your state: the same type and size of heater, and the same extras, such as the permit, an expansion tank or new venting. A quote between our low and high estimate is fair. One above the high end needs an explanation, and one far above it needs a second written estimate.</p>
<p>For a 50-gallon gas tank replacement at U.S. average prices, with the permit and haul-away, our range is $<?php echo esc_html( (string) hwm_raw( $hwm_c, 'figures.gas50.low' ) ); ?> to $<?php echo esc_html( (string) hwm_raw( $hwm_c, 'figures.gas50.high' ) ); ?>, typical $<?php echo esc_html( (string) hwm_raw( $hwm_c, 'figures.gas50.typical' ) ); ?>.</p>
<?php echo hwm_table( $hwm_c, 'bands' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Fair range</dt>
	<dd>Our low to high estimate for the job: the cheapest and dearest end of every line (unit, labor, each extra), scaled to your state.</dd>
	<dt>Far above</dt>
	<dd>More than <?php echo $hwm_v( 'farAbovePct' ); // phpcs:ignore ?>% over our high estimate. This cutoff is our own rule: no extra in our cost model explains a price that high.</dd>
</dl>

<h2>What should a water heater installation quote include?</h2>
<p>A good quote names the brand, model and size of the heater, the labor, the permit and haul-away of the old unit, and every extra the job needs. If it is one number with no detail, ask for the lines; you cannot compare two quotes otherwise.</p>
<ul>
	<li><strong>The unit:</strong> brand, model number, gallons and the warranty years. Our <?php echo hwm_link( 'water-heater-model-number-lookup', 'model number lookup' ); // phpcs:ignore ?> reads the size and warranty from the model number.</li>
	<li><strong>Labor:</strong> removal of the old heater and installation of the new one.</li>
	<li><strong>Permit and disposal:</strong> most places require a permit to replace a water heater.</li>
	<li><strong>Code items:</strong> a drain pan, seismic straps, relief valve piping, and an expansion tank on a closed system.</li>
	<li><strong>Job-specific work:</strong> venting, a gas line, a 240-volt circuit or a panel upgrade, if the new heater needs them.</li>
</ul>

<h2>Why is my water heater quote so high?</h2>
<p>Most high quotes come from extras, not from the heater. Switching fuel, moving the heater, a tankless conversion with new venting and a bigger gas line, or a heat pump that needs a new circuit can add more than the unit costs. Tick those items in the checker to see if they explain the gap.</p>
<p>Other reasons are honest too: a premium unit with a longer warranty, an emergency or weekend call, or a hard-to-reach spot. Ask which applies. If none does and the quote is far above our range, get more written estimates.</p>

<h2>Three example quotes, checked</h2>
<p>These are example quotes we made up to show how the checker reads them; they are not real customer quotes.</p>
<?php echo hwm_table( $hwm_c, 'examples' ); // phpcs:ignore ?>
<ol>
	<li><strong><?php echo esc_html( (string) ( $hwm_tx['label'] ?? '' ) ); ?>:</strong> a $<?php echo esc_html( number_format( (float) ( $hwm_tx['total'] ?? 0 ) ) ); ?> quote is <?php echo esc_html( (string) ( $hwm_tx['pct'] ?? '' ) ); ?>% <?php echo esc_html( (string) ( $hwm_tx['dir'] ?? '' ) ); ?> our typical $<?php echo esc_html( (string) ( $hwm_tx['typical'] ?? '' ) ); ?>, still inside the range. Ask what the extra pays for.</li>
	<li><strong><?php echo esc_html( (string) ( $hwm_fl['label'] ?? '' ) ); ?>:</strong> the total is fair, but the line items show labor at $<?php echo esc_html( number_format( (float) ( $hwm_fl['laborPrice'] ?? 0 ) ) ); ?>, below our range. A very low labor line can mean the permit or haul-away is missing; ask.</li>
	<li><strong><?php echo esc_html( (string) ( $hwm_ca['label'] ?? '' ) ); ?>:</strong> $<?php echo esc_html( number_format( (float) ( $hwm_ca['total'] ?? 0 ) ) ); ?> is more than <?php echo $hwm_v( 'farAbovePct' ); // phpcs:ignore ?>% over our high estimate of $<?php echo esc_html( (string) ( $hwm_ca['high'] ?? '' ) ); ?>. Unless the quote adds work we do not model, such as a panel upgrade or space changes, get more estimates. Tick those extras if they apply and the verdict changes.</li>
</ol>

<h2>How to get a fair price</h2>
<p>State consumer agencies give the same advice for any home improvement job, and it fits a water heater well:</p>
<ul>
	<?php foreach ( $hwm_ck as $hwm_i ) : ?>
		<li><?php echo esc_html( (string) $hwm_i['text'] ); ?> (<a href="<?php echo esc_url( (string) $hwm_i['sourceUrl'] ); ?>" rel="noopener"><?php echo esc_html( (string) $hwm_i['source'] ); ?></a>)</li>
	<?php endforeach; ?>
</ul>
<p>Check rebates before you sign: a heat pump water heater may qualify for a state or utility rebate that must be reserved first. Our <?php echo hwm_link( 'heat-pump-water-heater-rebates', 'heat pump water heater rebate finder' ); // phpcs:ignore ?> lists the ones we confirmed.</p>

<h2>Common mistakes</h2>
<ul>
	<li><strong>Comparing a total with a unit price.</strong> A shelf price leaves out labor, the permit and disposal. Compare installed with installed.</li>
	<li><strong>Comparing different jobs.</strong> A tank-to-tankless switch is a bigger job than a like-for-like swap. Pick the right job type in the checker.</li>
	<li><strong>Taking the lowest bid without asking why.</strong> Virginia's consumer guide says not to choose the lowest bidder automatically and to ask about price differences.</li>
	<li><strong>Paying most of it up front.</strong> Keep the deposit small and the final payment until the work passes inspection.</li>
</ul>

<h2>When to call a pro</h2>
<p>You need one for the job itself: gas, venting and 240-volt work are for a licensed plumber or electrician, and most places require a permit. Use this checker to judge the price, not to plan the work. If two licensed installers disagree about what your job needs, ask each to put the reason in writing.</p>

<h2>Limitations</h2>
<p>Our range is a model, not a survey of quotes. It uses one retailer's unit prices, labor ranges anchored to DOE's 2024 rules, and a state price factor; it cannot see your local labor market, a hard install or a premium brand. A quote outside our range is a reason to ask questions, not proof that it is unfair. We price only the extras in our cost model.</p>
<?php
$faq = array(
	array(
		'q' => 'How much should a water heater replacement cost?',
		'a' => 'A 50-gallon gas tank replacement with the permit runs about $' . hwm_value( $hwm_c, 'figures.gas50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.gas50.high' ) . ' at U.S. average prices, typical $' . hwm_value( $hwm_c, 'figures.gas50.typical' ) . '. Your state, the heater type and any extras move it. Enter your job above for the range that fits it.',
	),
	array(
		'q' => 'Is $' . hwm_value( $hwm_c, 'figures.sample.total' ) . ' too much for a water heater?',
		'a' => 'It depends on the job. For a 50-gallon gas tank replacement with the permit at U.S. average prices, $' . hwm_value( $hwm_c, 'figures.sample.total' ) . ' is ' . hwm_value( $hwm_c, 'figures.sample.verdict' ) . ': our range is $' . hwm_value( $hwm_c, 'figures.gas50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.gas50.high' ) . '. A tankless or heat pump job costs more. Check your exact job above.',
	),
	array(
		'q' => 'Should a water heater quote be itemized?',
		'a' => 'Ask for it. An itemized quote names the unit, the labor, the permit and each extra, so you can compare quotes line by line. New York\'s Attorney General advises that a contract name the brands of materials and the payment schedule.',
	),
	array(
		'q' => 'How many quotes should I get for a new water heater?',
		'a' => 'Three written estimates, if time allows. Virginia\'s consumer guide and New York\'s Attorney General both advise at least three. If your heater has failed and you need hot water fast, two is still better than one.',
	),
	array(
		'q' => 'How much deposit should a plumber ask for?',
		'a' => 'Little. Virginia\'s consumer guide suggests no more than 10% or $1,000, whichever is less, unless special-order items are needed, and New York\'s Attorney General says never to pay the full price up front.',
	),
	array(
		'q' => 'Does this checker share my quote with plumbers?',
		'a' => 'No. The check runs in your browser and your quote is not sent anywhere; our analytics see only rounded buckets, such as which band the result fell in. We do not sell installs, refer plumbers or collect leads.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Consumer water heaters final rule (2024): installation cost basis', 'url' => (string) hwm_raw( 'installation-costs', 'references.doe-storage-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( 'installation-costs', 'lastReviewed' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater listing prices', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'lastReviewed' ) ),
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional price parities by state', 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
	array( 'publisher' => 'Virginia DPOR', 'title' => 'Consumer Guide: Hiring a Contractor', 'url' => (string) ( $hwm_ck[0]['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_ck[0]['checked'] ?? '' ) ),
	array( 'publisher' => 'New York State Attorney General', 'title' => 'Hiring a home-improvement contractor', 'url' => (string) ( $hwm_ck[2]['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_ck[2]['checked'] ?? '' ) ),
);
