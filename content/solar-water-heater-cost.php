<?php
/**
 * /solar-water-heater-cost/ (audit suggestion S-12, 2026-11-14): installed cost, savings and payback of a solar water heater
 * by state. Every number comes from data/computed/solar-water-heater-cost.json (npm run build:data, DOE FEMP equations in
 * assets/js/formulas.js → solarWaterHeating) or data/solar-water-heating.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/solar-water-heater-cost';
$hwm_sd  = 'solar-water-heating';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_s   = static fn( string $path ): string => esc_html( hwm_value( $hwm_sd, $path ) );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_cr  = (array) hwm_raw( $hwm_sd, 'federalCredit' );
$hwm_ic  = (array) hwm_raw( $hwm_sd, 'installedCost' );
$hwm_fe  = (array) hwm_raw( $hwm_sd, 'femp' );
$hwm_end = hwm_date( (string) ( $hwm_cr['lastDay'] ?? '' ) );
$hwm_fpg = (string) hwm_raw( $hwm_sd, 'meta.sourceUrl' );
$hwm_tk  = (array) hwm_raw( $hwm_sd, 'sizing.solarStorageTankSample' );

$related = array( 'heat-pump-water-heater-cost', 'gas-vs-electric-water-heater', 'electric-water-heater-installation-cost', 'water-heater-size-calculator' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Savings use the equations and sun data in the U.S. Department of Energy's <?php echo $hwm_ext( $hwm_fpg, 'FEMP solar hot water calculator' ); // phpcs:ignore ?>, applied to one typical household in each state. The installed cost is the National Renewable Energy Laboratory's range. The full method is on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#solar-water-heater-cost' ); ?>">our methodology page</a>.</p>

<h2>How much does a solar water heater cost?</h2>
<p>A home solar water heater costs about $<?php echo $hwm_v( 'costLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'costHigh' ); // phpcs:ignore ?> installed, according to the National Renewable Energy Laboratory (NREL). That covers the roof collectors, a solar storage tank with a backup heater, the pump and controls, and the labor. We use the middle, $<?php echo $hwm_v( 'costTypical' ); // phpcs:ignore ?>, for every payback on this page. A standard 50-gallon electric tank costs about $<?php echo $hwm_v( 'elInstalled' ); // phpcs:ignore ?> installed, so solar costs about $<?php echo $hwm_v( 'extraE' ); // phpcs:ignore ?> more up front.</p>
<p>NREL's range dates from <?php echo $hwm_v( 'costYear' ); // phpcs:ignore ?>, and it is still the most recent installed-cost figure we found from a national lab. The popular cost guides disagree with each other by a wide margin, from $<?php echo $hwm_v( 'guidesLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'guidesHigh' ); // phpcs:ignore ?>, so we show them side by side instead of picking the one that suits a story.</p>
<?php echo hwm_table( $hwm_c, 'costs' ); // phpcs:ignore -- escaped in the part. ?>
<p>Why the spread is so wide: a small batch (integral collector) system on a warm-climate roof is a very different job from a two-panel, freeze-protected system with a heat exchanger in a cold state. Ask any installer which type the quote is for. The storage tank alone is the cheapest part; Home Depot lists an <?php echo esc_html( (string) ( $hwm_tk['label'] ?? '' ) ); ?> at $<?php echo esc_html( number_format( (float) ( $hwm_tk['priceUsd'] ?? 0 ) ) ); ?> (checked <?php echo esc_html( hwm_date( (string) ( $hwm_tk['checked'] ?? '' ) ) ); ?>). The collectors, piping, controls and roof work make up the rest.</p>
<dl>
	<dt>Solar water heater (solar thermal)</dt>
	<dd>Roof collectors that heat water, or a fluid that heats water, directly from sunlight. It is not the same as solar panels (photovoltaics), which make electricity. A solar water heater always keeps a backup, usually an electric element or a gas burner, for cloudy days.</dd>
	<dt>Solar share (solar fraction)</dt>
	<dd>The part of your yearly water heating that the sun supplies. The backup supplies the rest. DOE's FEMP calculator calls it the "fraction of load provided by solar".</dd>
</dl>

<h2>Is there a tax credit for solar water heaters in 2026?</h2>
<p>No. The federal Residential Clean Energy Credit paid <?php echo $hwm_v( 'creditPct' ); // phpcs:ignore ?>% of the cost, but the IRS says &ldquo;<?php echo esc_html( (string) ( $hwm_cr['quotes'][1] ?? '' ) ); ?>&rdquo; A system installed in 2026 gets nothing from it.</p>
<p>Many cost guides still subtract that credit from their prices, one still says it runs for years to come, and that makes solar look <?php echo $hwm_v( 'creditPct' ); // phpcs:ignore ?>% cheaper than it is now. Check the <?php echo $hwm_ext( (string) ( $hwm_cr['sourceUrl'] ?? '' ), 'IRS page' ); // phpcs:ignore ?> before you count on it. Local utility or state rebates may still exist; the <?php echo $hwm_ext( (string) hwm_raw( 'hpwh-rebates', 'meta.sourceUrl' ), 'ENERGY STAR Rebate Finder' ); // phpcs:ignore ?> searches by ZIP code.</p>

<h2>Solar water heater savings and payback by state</h2>
<p>A solar water heater pays back fastest where electricity is expensive, not where the sun is strongest. Against an electric tank, the extra cost pays back within a tank's typical <?php echo $hwm_v( 'life' ); // phpcs:ignore ?>-year life in <?php echo $hwm_v( 'inLifeE' ); // phpcs:ignore ?> of the <?php echo $hwm_v( 'stateCount' ); // phpcs:ignore ?> states with DOE sun data. Against a gas tank it pays back within that time in <?php echo $hwm_v( 'inLifeG' ); // phpcs:ignore ?>.</p>
<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore -- escaped in the part. ?>
<p>The pattern is easy to read once you see it. <?php echo $hwm_v( 'sunniest.name' ); // phpcs:ignore ?> gets the highest solar share, <?php echo $hwm_v( 'sunniest.share' ); // phpcs:ignore ?>%, and <?php echo $hwm_v( 'cloudiest.name' ); // phpcs:ignore ?> the lowest, <?php echo $hwm_v( 'cloudiest.share' ); // phpcs:ignore ?>%: the sun changes the share by less than you might expect, because the collector is sized for each climate. What changes the money is the price of the energy you stop buying. <?php echo $hwm_v( 'best.name' ); // phpcs:ignore ?> saves the most, about $<?php echo $hwm_v( 'best.eSave' ); // phpcs:ignore ?> a year, while sunny <?php echo $hwm_v( 'fl.name' ); // phpcs:ignore ?> saves about $<?php echo $hwm_v( 'fl.eSave' ); // phpcs:ignore ?> because its incoming water is already warm and its power is cheaper. <?php echo $hwm_v( 'az.name' ); // phpcs:ignore ?> needs only about <?php echo $hwm_v( 'az.area' ); // phpcs:ignore ?> square feet of collector, yet takes <?php echo $hwm_v( 'az.ePay' ); // phpcs:ignore ?> to pay back.</p>
<p>FEMP lists no sun data city for <?php echo $hwm_v( 'missing' ); // phpcs:ignore ?>, so those rows are blank. A neighboring state's row is a fair guide.</p>

<h2>How we worked out the savings</h2>
<p>We used DOE's Federal Energy Management Program calculator, built for federal buildings, and ran its equations for a single household. The steps, with the default values FEMP calls &ldquo;<?php echo esc_html( lcfirst( rtrim( (string) ( $hwm_fe['quotes']['design'] ?? '' ), '.' ) ) ); ?>&rdquo;:</p>
<ol>
	<li><strong>Daily hot water load:</strong> <?php echo $hwm_v( 'gallons' ); // phpcs:ignore ?> gallons a day, heated from your state's average incoming water temperature to <?php echo $hwm_v( 'setpoint' ); // phpcs:ignore ?> °F (gallons × <?php echo $hwm_s( 'femp.btuPerGallonF' ); // phpcs:ignore ?> BTU per °F × rise).</li>
	<li><strong>Collector size:</strong> big enough to cover the whole load on the sunniest day, at a collector efficiency of <?php echo $hwm_s( 'femp.etaSolar' ); // phpcs:ignore ?>, using FEMP's maximum daily sunshine for the city.</li>
	<li><strong>Solar heat a year:</strong> collector area × average daily sunshine × efficiency × 365 days, of which FEMP counts <?php echo esc_html( (string) ( (float) ( $hwm_fe['utilization'] ?? 0 ) * 100 ) ); ?>% as usable.</li>
	<li><strong>Money saved:</strong> the electricity or gas the backup no longer buys (solar heat ÷ the backup's efficiency, <?php echo $hwm_v( 'uefE' ); // phpcs:ignore ?> UEF for an electric tank, <?php echo $hwm_v( 'uefG' ); // phpcs:ignore ?> for gas) at your state's EIA price, minus the pump's electricity and FEMP's upkeep allowance of <?php echo esc_html( (string) ( (float) ( $hwm_fe['omShareOfCostPerYear'] ?? 0 ) * 100 ) ); ?>% of the system cost a year ($<?php echo $hwm_v( 'upkeep' ); // phpcs:ignore ?>).</li>
	<li><strong>Payback:</strong> the extra cost over a standard tank ÷ the yearly saving. No discounting and no price rises, so read it as a rough guide.</li>
</ol>
<p>We checked our code against the spreadsheet itself: for FEMP's own example (<?php echo esc_html( number_format( (float) ( $hwm_fe['example']['gallonsPerDay'] ?? 0 ) ) ); ?> gallons a day in <?php echo esc_html( (string) ( $hwm_fe['example']['place'] ?? '' ) ); ?>) it gives the same collector area, solar heat and pump energy to the decimal. The test is in our formula tests and described on the methodology page.</p>

<?php hwm_ad( 'B' ); ?>

<h2>Solar or heat pump: which is the better upgrade?</h2>
<p>For most homes with an electric tank, a heat pump water heater (hybrid) saves more for far less money. It costs about $<?php echo $hwm_v( 'hpInstalled' ); // phpcs:ignore ?> installed, saves about $<?php echo $hwm_v( 'hpSave' ); // phpcs:ignore ?> a year at U.S. average prices and pays back in about <?php echo $hwm_v( 'hpPay' ); // phpcs:ignore ?> years. A solar system in the middle state, <?php echo $hwm_v( 'mid.name' ); // phpcs:ignore ?>, saves about $<?php echo $hwm_v( 'mid.eSave' ); // phpcs:ignore ?> a year and takes <?php echo $hwm_v( 'mid.ePay' ); // phpcs:ignore ?>.</p>
<?php echo hwm_table( $hwm_c, 'compare' ); // phpcs:ignore -- escaped in the part. ?>
<p>Solar still makes sense in a few cases: you pay a high electricity rate (see <?php echo $hwm_v( 'hi.name' ); // phpcs:ignore ?>, about $<?php echo $hwm_v( 'hi.eSave' ); // phpcs:ignore ?> a year), your heater sits in a space too small or too cold for a heat pump, or a local rebate covers a large share of the price. Our <?php echo hwm_link( 'heat-pump-water-heater-cost', 'heat pump water heater cost guide' ); // phpcs:ignore ?> has its own calculator with your state's prices.</p>
<?php hwm_part( 'doe-2029', array( 'heading' => false, 'lead' => 'The comparison tilts further toward heat pumps for new electric tanks.' ) ); ?>

<h2>Before you get a quote: a checklist</h2>
<ul>
	<li><strong>Roof:</strong> a south-facing section with little shade, and a roof with years left in it. Removing collectors to re-roof adds cost later.</li>
	<li><strong>Freeze protection:</strong> in any state that freezes, ask for an indirect (closed-loop) or drain-back system. Direct systems suit frost-free climates.</li>
	<li><strong>Certification:</strong> the IRS required SRCC certification for the old credit, and many local rebates still do. Ask for the system's SRCC OG-300 rating.</li>
	<li><strong>Backup:</strong> ask what heats the water on cloudy days, and at what efficiency. Your yearly bill depends on it.</li>
	<li><strong>Tank size:</strong> solar systems use a larger tank than a standard heater so a sunny afternoon can heat water for the evening. Check the first hour rating with our <?php echo hwm_link( 'water-heater-size-calculator', 'water heater size calculator' ); // phpcs:ignore ?>.</li>
	<li><strong>Upkeep:</strong> pumps, controls and the heat transfer fluid need checks over the years; ask what the installer's service plan costs.</li>
</ul>

<h2>Common mistakes when pricing solar water heating</h2>
<ul>
	<li><strong>Counting the expired federal credit.</strong> It ended on <?php echo esc_html( $hwm_end ); ?>.</li>
	<li><strong>Comparing with the wrong heater.</strong> Against a gas tank, solar saves much less, because gas is cheaper per unit of heat than electricity in most states.</li>
	<li><strong>Using a sunny-state payback in a cheap-power state.</strong> The table shows that sun alone does not decide it.</li>
	<li><strong>Mixing up solar panels and solar water heating.</strong> Panels make electricity; if you already have them, a heat pump water heater can use that power.</li>
</ul>

<h2>When is this a job for a licensed professional?</h2>
<p>Always. A solar water heater means roof penetrations, pressurized piping, a heat transfer fluid in cold climates and an electrical or gas backup. Most areas require a plumbing permit, and some require a building permit for the roof work. This page helps you budget and compare quotes; it is not an installation guide.</p>

<h2>Limitations of this estimate</h2>
<p>The installed cost is a <?php echo $hwm_v( 'costYear' ); // phpcs:ignore ?> national range, not adjusted for inflation or for your state, because we found no newer figure from a public agency. The FEMP method was built for federal buildings and uses one city per state, one collector efficiency and a simple upkeep allowance; real systems vary with collector type, tilt and shade. Energy prices are held flat. Use the result to decide whether a quote is worth getting, not as a promise of savings.</p>
<?php
$faq = array(
	array(
		'q' => 'How much does a solar water heater cost to install?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.costLow' ) . ' to $' . hwm_value( $hwm_c, 'figures.costHigh' ) . ' installed according to NREL, against about $' . hwm_value( $hwm_c, 'figures.elInstalled' ) . ' for a standard 50-gallon electric tank. Cost guides quote anywhere from $' . hwm_value( $hwm_c, 'figures.guidesLow' ) . ' to $' . hwm_value( $hwm_c, 'figures.guidesHigh' ) . ', depending on the system type and climate.',
	),
	array(
		'q' => 'Is there a federal tax credit for solar water heaters in 2026?',
		'a' => 'No. The IRS says the ' . hwm_value( $hwm_c, 'figures.creditPct' ) . '% Residential Clean Energy Credit is not available for any property placed in service after ' . $hwm_end . '. Some utilities and states still offer rebates; check the ENERGY STAR Rebate Finder for your ZIP code.',
	),
	array(
		'q' => 'How long does a solar water heater take to pay for itself?',
		'a' => 'Against an electric tank, from about ' . hwm_value( $hwm_c, 'figures.best.ePay' ) . ' in ' . hwm_value( $hwm_c, 'figures.best.name' ) . ' to ' . hwm_value( $hwm_c, 'figures.worst.ePay' ) . ' in ' . hwm_value( $hwm_c, 'figures.worst.name' ) . ' for a typical household. It pays back within a tank\'s ' . hwm_value( $hwm_c, 'figures.life' ) . '-year life in ' . hwm_value( $hwm_c, 'figures.inLifeE' ) . ' states. Against gas it rarely pays back.',
	),
	array(
		'q' => 'How much of my hot water will the sun provide?',
		'a' => 'With a collector sized to cover the sunniest day, about ' . hwm_value( $hwm_c, 'figures.cloudiest.share' ) . '% in ' . hwm_value( $hwm_c, 'figures.cloudiest.name' ) . ' to ' . hwm_value( $hwm_c, 'figures.sunniest.share' ) . '% in ' . hwm_value( $hwm_c, 'figures.sunniest.name' ) . ' over a year, using DOE FEMP sun data. The backup heater supplies the rest, mostly in winter and on cloudy days.',
	),
	array(
		'q' => 'Is a solar water heater better than a heat pump water heater?',
		'a' => 'For most homes, no. A heat pump costs about $' . hwm_value( $hwm_c, 'figures.hpInstalled' ) . ' installed and saves about $' . hwm_value( $hwm_c, 'figures.hpSave' ) . ' a year over an electric tank, paying back in about ' . hwm_value( $hwm_c, 'figures.hpPay' ) . ' years. Solar can win where power is very expensive or a heat pump will not fit.',
	),
	array(
		'q' => 'Do solar water heaters work in winter or on cloudy days?',
		'a' => 'They still collect some heat, but less, so the backup element or burner makes up the difference. In freezing climates the system needs freeze protection, usually a closed loop with antifreeze fluid or a drain-back design. The yearly solar share in our table already includes the winter months.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy, Federal Energy Management Program', 'title' => 'Solar Hot Water System Calculator (June 2023): equations, design defaults and solar resource data', 'url' => $hwm_fpg, 'accessed' => (string) ( $hwm_fe['checked'] ?? '' ) ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Energy Management Program', 'title' => 'Solar Hot Water System Size and Life Cycle Costs spreadsheet (xlsx)', 'url' => (string) ( $hwm_fe['calculatorUrl'] ?? '' ), 'accessed' => (string) ( $hwm_fe['checked'] ?? '' ) ),
	array( 'publisher' => 'National Renewable Energy Laboratory', 'title' => 'Low-Cost Solar Water Heating Research and Development Roadmap (NREL/TP-5500-54793, 2012)', 'url' => (string) ( $hwm_ic['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_ic['checked'] ?? '' ) ),
	array( 'publisher' => 'Internal Revenue Service', 'title' => 'Residential Clean Energy Credit', 'url' => (string) ( $hwm_cr['sourceUrl'] ?? '' ), 'accessed' => (string) ( $hwm_cr['checked'] ?? '' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Average residential electricity and natural gas prices by state', 'url' => (string) hwm_raw( 'energy-prices', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (89 FR 37778, May 6, 2024)', 'url' => (string) hwm_raw( 'uef-ranges', 'doe2029.sourceUrl' ), 'accessed' => (string) hwm_raw( 'uef-ranges', 'doe2029.checked' ) ),
);
