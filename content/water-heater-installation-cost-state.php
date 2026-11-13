<?php
/**
 * Shared body of the state cost pages, /water-heater-installation-cost/{state}/ (PHASES.md 6c, STRATEGY §2 State pages).
 * Rendered by templates/state-cost.php. Every number comes from data/computed/water-heater-installation-cost-<state>.json
 * (npm run build:data: the same cost model as the cost calculator, EIA state prices, NOAA-derived incoming water
 * temperatures) and data/state-pages.json (EIA RECS fuel mix, rebate status, state notes). PHP only picks wording.
 * Section order follows the state's main water heating fuel, so an electric state leads with running costs and rebates,
 * a gas state with fuel mix and tankless output.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_leaf = (string) substr( (string) $entry['slug'], (int) strrpos( (string) $entry['slug'], '/' ) + 1 );
$hwm_c    = 'computed/water-heater-installation-cost-' . $hwm_leaf;
$hwm_f    = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_t    = static fn( string $path ) => hwm_raw( $hwm_c, 'figures.' . $path );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_sp   = 'state-pages';
$hwm_name = $hwm_f( 'name' );
$hwm_elec = (bool) $hwm_t( 'elecFirst' );
$hwm_reb  = (array) $hwm_t( 'rebate' );
$hwm_pre  = (array) hwm_raw( $hwm_c, 'calculator' );
$hwm_ic   = 'installation-costs';

$related = array( 'water-heater-installation-cost', 'water-heater-cost-calculator', 'gas-vs-electric-water-heater', 'heat-pump-water-heater-cost', 'tankless-water-heater-size-calculator' );

$hwm_sections = array();

/* ---- cost summary ---- */
ob_start();
?>
<h2>How much does it cost to install a water heater in <?php echo $hwm_name; // phpcs:ignore ?>?</h2>
<?php if ( $hwm_elec ) : ?>
<p>A 50-gallon electric tank costs about $<?php echo $hwm_f( 'e50.typical' ); // phpcs:ignore ?> installed in <?php echo $hwm_name; // phpcs:ignore ?> ($<?php echo $hwm_f( 'e50.low' ); // phpcs:ignore ?> to $<?php echo $hwm_f( 'e50.high' ); // phpcs:ignore ?>), and a 50-gallon gas tank about $<?php echo $hwm_f( 'g50.typical' ); // phpcs:ignore ?> ($<?php echo $hwm_f( 'g50.low' ); // phpcs:ignore ?> to $<?php echo $hwm_f( 'g50.high' ); // phpcs:ignore ?>). Both prices are a like-for-like replacement with labor, permit and haul-away of the old tank.</p>
<?php else : ?>
<p>A 50-gallon gas tank costs about $<?php echo $hwm_f( 'g50.typical' ); // phpcs:ignore ?> installed in <?php echo $hwm_name; // phpcs:ignore ?> ($<?php echo $hwm_f( 'g50.low' ); // phpcs:ignore ?> to $<?php echo $hwm_f( 'g50.high' ); // phpcs:ignore ?>), and a 50-gallon electric tank about $<?php echo $hwm_f( 'e50.typical' ); // phpcs:ignore ?> ($<?php echo $hwm_f( 'e50.low' ); // phpcs:ignore ?> to $<?php echo $hwm_f( 'e50.high' ); // phpcs:ignore ?>). Both prices are a like-for-like replacement with labor, permit and haul-away of the old tank.</p>
<?php endif; ?>
<?php if ( 0 === (int) $hwm_t( 'multPct' ) ) : ?>
<p>That is the same as the U.S. average. <?php echo $hwm_name; // phpcs:ignore ?>'s price level from the Bureau of Economic Analysis is <?php echo $hwm_f( 'rpp' ); // phpcs:ignore ?> for <?php echo $hwm_f( 'rppYear' ); // phpcs:ignore ?>, exactly the national level, so labor and permits are priced at the national ranges. Big metro areas usually run above the state level and rural counties below it.</p>
<?php else : ?>
<p>That is $<?php echo $hwm_f( 'g50Diff' ); // phpcs:ignore ?> <?php echo $hwm_f( 'g50DiffSign' ); // phpcs:ignore ?> than the same gas job at U.S. average prices ($<?php echo $hwm_f( 'usG50' ); // phpcs:ignore ?>). The gap comes only from labor, permit and extras: <?php echo $hwm_name; // phpcs:ignore ?>'s price level is <?php echo $hwm_f( 'multVs' ); // phpcs:ignore ?> the national level (Bureau of Economic Analysis, <?php echo $hwm_f( 'rppYear' ); // phpcs:ignore ?>), the <?php echo $hwm_f( 'multRankPhrase' ); // phpcs:ignore ?> of <?php echo $hwm_f( 'stateCount' ); // phpcs:ignore ?> states and DC. The water heater itself sells for the same listing price everywhere.</p>
<?php endif; ?>
<h3>What stands out in <?php echo $hwm_name; // phpcs:ignore ?></h3>
<ul>
	<?php foreach ( (array) $hwm_t( 'highlights' ) as $hwm_h ) : ?>
		<li><?php echo esc_html( (string) $hwm_h ); ?></li>
	<?php endforeach; ?>
</ul>
<?php echo hwm_table( $hwm_c, 'compare' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Price factor</dt>
	<dd><?php echo $hwm_name; // phpcs:ignore ?>'s Regional Price Parity divided by 100, here <?php echo $hwm_f( 'mult' ); // phpcs:ignore ?>. We multiply labor, permit and every extra by it; the unit price is not scaled.</dd>
	<dt>Like-for-like replacement</dt>
	<dd>A new heater of the same fuel and size class in the same spot, using the existing water lines, gas line or circuit, and vent.</dd>
</dl>
<?php
$hwm_sections['cost'] = (string) ob_get_clean();

/* ---- metro areas ---- */
ob_start();
?>
<h2>Water heater installation cost in <?php echo $hwm_name; // phpcs:ignore ?>'s biggest cities</h2>
<p>Within <?php echo $hwm_name; // phpcs:ignore ?>, a 50-gallon gas replacement runs from about $<?php echo $hwm_f( 'metroCheap.g50' ); // phpcs:ignore ?> in the <?php echo $hwm_f( 'metroCheap.name' ); // phpcs:ignore ?> area to $<?php echo $hwm_f( 'metroDear.g50' ); // phpcs:ignore ?> in the <?php echo $hwm_f( 'metroDear.name' ); // phpcs:ignore ?> area, a gap of $<?php echo $hwm_f( 'metroSpread' ); // phpcs:ignore ?>. The unit is the same; labor and permits follow each metro area's price level, which the Bureau of Economic Analysis measures separately from the state.</p>
<?php echo hwm_table( $hwm_c, 'metros' ); // phpcs:ignore ?>
<?php if ( '' !== (string) $hwm_t( 'metroSkipped' ) ) : ?>
<p>Not listed: <?php echo $hwm_f( 'metroSkipped' ); // phpcs:ignore ?>. These areas were redrawn after the Bureau of Economic Analysis's last metro-area price release, so there is no price level for them yet; use the statewide figure.</p>
<?php endif; ?>
<p>Outside these metro areas, small towns and rural counties usually price below the statewide figure. If your quote is far above the number for your area, ask what extras it includes.</p>
<?php
$hwm_sections['metros'] = (string) ob_get_clean();

/* ---- itemized ---- */
ob_start();
?>
<h2>Where the money goes on a <?php echo $hwm_name; // phpcs:ignore ?> replacement</h2>
<p>On a typical <?php echo $hwm_elec ? 'electric' : 'gas'; ?> job in <?php echo $hwm_name; // phpcs:ignore ?>, labor is about $<?php echo $hwm_elec ? $hwm_f( 'laborE' ) : $hwm_f( 'laborG' ); // phpcs:ignore ?> and the permit with haul-away about $<?php echo $hwm_f( 'permit' ); // phpcs:ignore ?>. The unit is about <?php echo $hwm_elec ? $hwm_f( 'e50.unitShare' ) : $hwm_f( 'g50.unitShare' ); // phpcs:ignore ?>% of the total, so the unit you pick matters more than where you live.</p>
<?php echo hwm_table( $hwm_c, 'itemsMain' ); // phpcs:ignore ?>
<details>
	<summary>The same breakdown for a 50-gallon <?php echo $hwm_elec ? 'gas' : 'electric'; ?> tank</summary>
	<?php echo hwm_table( $hwm_c, 'itemsOther' ); // phpcs:ignore ?>
</details>
<p>Gas labor runs higher than electric because the installer reconnects and leak-tests the gas line and checks the vent draft. Quotes above these ranges usually include extras: an expansion tank, code items, new venting or a move to a new spot. Our <?php echo hwm_link( 'water-heater-installation-cost', 'national replacement cost guide' ); // phpcs:ignore ?> explains each extra.</p>
<?php
$hwm_sections['items'] = (string) ob_get_clean();

/* ---- calculator ---- */
ob_start();
?>
<h2><?php echo $hwm_name; // phpcs:ignore ?> water heater cost calculator</h2>
<p>The calculator starts at a 50-gallon <?php echo $hwm_elec ? 'electric' : 'gas'; ?> tank in <?php echo $hwm_name; // phpcs:ignore ?><?php echo $hwm_t( 'presetExtras' ) ? ', with the code items a ' . $hwm_name . ' install needs ticked' : ''; // phpcs:ignore ?>. Change the type, size or job, or tick the extras your quote lists, to check a quote line by line.</p>
<?php
ob_start();
hwm_part( 'cost-inputs', array( 'preset' => (array) ( $hwm_pre['inputs'] ?? array() ) ) );
hwm_part( 'calculator', array( 'tool' => 'water-heater-cost-calculator', 'inputs' => (string) ob_get_clean(), 'label' => $hwm_name . ' water heater installation cost calculator', 'default' => (array) ( $hwm_pre['default'] ?? array() ) ) );
$hwm_sections['calc'] = (string) ob_get_clean();

/* ---- state notes (sourced, only some states) ---- */
ob_start();
foreach ( (array) $hwm_t( 'notes' ) as $hwm_n ) :
	?>
<h2><?php echo esc_html( (string) $hwm_n['heading'] ); ?></h2>
<p><?php echo esc_html( (string) $hwm_n['text'] ); ?> Source: <?php echo $hwm_ext( (string) $hwm_n['sourceUrl'], (string) $hwm_n['source'] ); // phpcs:ignore ?>.</p>
<?php if ( 'codeItems' === ( $hwm_n['presetExtra'] ?? '' ) ) : ?>
<p>With straps and the other code items, a 50-gallon gas replacement in <?php echo $hwm_name; // phpcs:ignore ?> comes to about $<?php echo $hwm_f( 'g50code.typical' ); // phpcs:ignore ?> once an expansion tank is added too.</p>
<?php endif; ?>
	<?php
endforeach;
$hwm_sections['notes'] = (string) ob_get_clean();

/* ---- running cost ---- */
ob_start();
?>
<h2>Which water heater is cheapest to run in <?php echo $hwm_name; // phpcs:ignore ?>?</h2>
<p>A heat pump water heater is the cheapest tank to run in <?php echo $hwm_name; // phpcs:ignore ?>, about $<?php echo $hwm_f( 'hYear' ); // phpcs:ignore ?> a year for a typical household, against $<?php echo $hwm_f( 'gYear' ); // phpcs:ignore ?> for a gas tank and $<?php echo $hwm_f( 'eYear' ); // phpcs:ignore ?> for a standard electric tank. <?php if ( 'gas_tankless' === $hwm_t( 'cheapestId' ) ) : ?>A gas tankless unit is lower still, about $<?php echo $hwm_f( 'gasTanklessYear' ); // phpcs:ignore ?>.<?php endif; ?></p>
<p>These figures use <?php echo $hwm_name; // phpcs:ignore ?>'s <?php echo $hwm_f( 'kwhYear' ); // phpcs:ignore ?> average residential prices from the Energy Information Administration: <?php echo $hwm_f( 'kwhCents' ); // phpcs:ignore ?> cents per kWh (<?php echo $hwm_f( 'kwhVs' ); // phpcs:ignore ?> the U.S. average, <?php echo $hwm_f( 'kwhRankOrd' ); // phpcs:ignore ?> cheapest of <?php echo $hwm_f( 'stateCount' ); // phpcs:ignore ?>) and $<?php echo $hwm_f( 'therm' ); // phpcs:ignore ?> per therm of gas (<?php echo $hwm_f( 'thermVs' ); // phpcs:ignore ?> the U.S. average, <?php echo $hwm_f( 'thermRankOrd' ); // phpcs:ignore ?> cheapest). The household draws <?php echo $hwm_f( 'gallons' ); // phpcs:ignore ?> gallons a day heated to <?php echo $hwm_f( 'setpointF' ); // phpcs:ignore ?> °F from <?php echo $hwm_name; // phpcs:ignore ?>'s average incoming water of <?php echo $hwm_f( 'inletAnnual' ); // phpcs:ignore ?> °F.</p>
<?php echo hwm_table( $hwm_c, 'running' ); // phpcs:ignore ?>
<?php if ( $hwm_t( 'gasCheaperToRun' ) ) : ?>
<p>Gas costs about $<?php echo $hwm_f( 'gasVsElecYear' ); // phpcs:ignore ?> a year less to run than a standard electric tank here. If your home already has gas, that covers gas's higher installed price (about $<?php echo $hwm_f( 'gasPremium' ); // phpcs:ignore ?> more) in <?php echo $hwm_f( 'gasPay' ); // phpcs:ignore ?>. Over <?php echo $hwm_f( 'years' ); // phpcs:ignore ?> years the cheapest of the three tanks to buy and run is the <?php echo esc_html( strtolower( (string) $hwm_t( 'ownBest' ) ) ); ?>.</p>
<?php else : ?>
<p>A standard electric tank costs about $<?php echo $hwm_f( 'gasVsElecYear' ); // phpcs:ignore ?> a year less to run than gas here. Over <?php echo $hwm_f( 'years' ); // phpcs:ignore ?> years the cheapest of the three tanks to buy and run is the <?php echo esc_html( strtolower( (string) $hwm_t( 'ownBest' ) ) ); ?>.</p>
<?php endif; ?>
<p>Replacing an electric tank with a heat pump costs about $<?php echo $hwm_f( 'hpExtra' ); // phpcs:ignore ?> more installed in <?php echo $hwm_name; // phpcs:ignore ?> and saves about $<?php echo $hwm_f( 'hpSave' ); // phpcs:ignore ?> a year, <?php echo $hwm_t( 'hpPaysInLife' ) ? 'paying back in about ' . $hwm_f( 'hpPay' ) . ' years, well inside its ' . $hwm_f( 'hpLife' ) . '-year typical life' : 'which does not pay back within its typical life'; // phpcs:ignore ?>. Run your own numbers with the <?php echo hwm_link( 'heat-pump-water-heater-cost', 'heat pump water heater savings calculator' ); // phpcs:ignore ?> or compare fuels with the <?php echo hwm_link( 'gas-vs-electric-water-heater', 'gas vs electric calculator' ); // phpcs:ignore ?>.</p>
<?php
$hwm_sections['running'] = (string) ob_get_clean();

/* ---- fuel mix ---- */
ob_start();
?>
<h2>How do <?php echo $hwm_name; // phpcs:ignore ?> homes heat water?</h2>
<?php if ( $hwm_t( 'elecMajority' ) ) : ?>
<p>Most <?php echo $hwm_name; // phpcs:ignore ?> homes heat water with electricity: <?php echo $hwm_f( 'electricPct' ); // phpcs:ignore ?>% against <?php echo $hwm_f( 'gasPct' ); // phpcs:ignore ?>% natural gas, according to the Energy Information Administration's 2020 home energy survey. If your home has no gas line, an electric tank or a heat pump is the like-for-like choice, and adding gas would bring a gas line, vent and permit on top of the prices above.</p>
<?php elseif ( $hwm_t( 'gasMajority' ) ) : ?>
<p>Most <?php echo $hwm_name; // phpcs:ignore ?> homes heat water with natural gas: <?php echo $hwm_f( 'gasPct' ); // phpcs:ignore ?>% against <?php echo $hwm_f( 'electricPct' ); // phpcs:ignore ?>% electricity, according to the Energy Information Administration's 2020 home energy survey. A gas home that switches to a heat pump also pays for a new 240-volt circuit, and sometimes a panel upgrade.</p>
<?php else : ?>
<p><?php echo $hwm_name; // phpcs:ignore ?> is split almost evenly: <?php echo $hwm_f( 'gasPct' ); // phpcs:ignore ?>% of homes heat water with natural gas and <?php echo $hwm_f( 'electricPct' ); // phpcs:ignore ?>% with electricity, according to the Energy Information Administration's 2020 home energy survey. Price the fuel you already have first; switching adds a gas line and vent, or a new circuit.</p>
<?php endif; ?>
<?php echo hwm_table( $hwm_c, 'fuelMix' ); // phpcs:ignore ?>
<?php if ( $hwm_t( 'fuelOilShown' ) ) : ?>
<p>About <?php echo $hwm_f( 'fuelOilPct' ); // phpcs:ignore ?>% of <?php echo $hwm_name; // phpcs:ignore ?> homes still heat water with fuel oil, often from the boiler. Our prices cover gas, electric and heat pump units only; an oil-fired or boiler-fed (indirect) water heater is priced differently.</p>
<?php endif; ?>
<p>Propane serves about <?php echo $hwm_f( 'propanePct' ); // phpcs:ignore ?>% of <?php echo $hwm_name; // phpcs:ignore ?> homes. We do not price propane separately: the running costs above use natural gas prices, and propane is sold by the gallon at different prices.</p>
<?php
$hwm_sections['fuel'] = (string) ob_get_clean();

/* ---- tankless ---- */
ob_start();
?>
<h2>Will a tankless water heater keep up in a <?php echo $hwm_name; // phpcs:ignore ?> winter?</h2>
<p>In the coldest month <?php echo $hwm_name; // phpcs:ignore ?>'s incoming water averages about <?php echo $hwm_f( 'inletWinter' ); // phpcs:ignore ?> °F, so a tankless unit must heat it <?php echo $hwm_f( 'riseW' ); // phpcs:ignore ?> °F to reach <?php echo $hwm_f( 'setpointF' ); // phpcs:ignore ?> °F. At that rise a large <?php echo $hwm_f( 'refInput' ); // phpcs:ignore ?> BTU/h gas unit delivers about <?php echo $hwm_f( 'gpmW' ); // phpcs:ignore ?> gallons per minute, enough for <?php echo $hwm_f( 'showersW' ); // phpcs:ignore ?> full-flow shower<?php echo 1 === (int) $hwm_t( 'showersW' ) ? '' : 's'; ?> at once.</p>
<?php echo hwm_table( $hwm_c, 'tankless' ); // phpcs:ignore ?>
<p>The same unit gives about <?php echo $hwm_f( 'seasonLossPct' ); // phpcs:ignore ?>% less hot water in the coldest month than in the warmest. Size a tankless unit for the winter figure, not the year average; the <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless size calculator' ); // phpcs:ignore ?> lets you pick <?php echo $hwm_name; // phpcs:ignore ?> to load its incoming water temperature.</p>
<h3>Month by month in <?php echo $hwm_name; // phpcs:ignore ?></h3>
<p>Incoming water is coldest in <?php echo $hwm_f( 'coldMonth' ); // phpcs:ignore ?> (about <?php echo $hwm_f( 'coldMonthInlet' ); // phpcs:ignore ?> °F) and warmest in <?php echo $hwm_f( 'warmMonth' ); // phpcs:ignore ?> (about <?php echo $hwm_f( 'warmMonthInlet' ); // phpcs:ignore ?> °F). Underground pipes lag the weather by about a month, which is why the coldest water comes after the coldest air. Each day in <?php echo $hwm_f( 'coldMonth' ); // phpcs:ignore ?> takes about <?php echo $hwm_f( 'winterExtraPct' ); // phpcs:ignore ?>% more energy to heat the same hot water than a day in <?php echo $hwm_f( 'warmMonth' ); // phpcs:ignore ?>, for any type of water heater.</p>
<?php echo hwm_table( $hwm_c, 'months' ); // phpcs:ignore ?>
<p>Switching from a gas tank to gas tankless costs about $<?php echo $hwm_f( 'toTl.typical' ); // phpcs:ignore ?> in <?php echo $hwm_name; // phpcs:ignore ?>, about $<?php echo $hwm_f( 'tlPremium' ); // phpcs:ignore ?> more than a new gas tank, because it usually needs new venting and a bigger gas line. The <?php echo hwm_link( 'tankless-water-heater-installation-cost', 'tankless installation cost guide' ); // phpcs:ignore ?> breaks that down.</p>
<?php
$hwm_sections['tankless'] = (string) ob_get_clean();

/* ---- rebates ---- */
ob_start();
$hwm_status = (string) ( $hwm_reb['status'] ?? 'unconfirmed' );
$hwm_word   = array(
	'open'        => 'Yes, for income-qualified households.',
	'limited'     => 'Not for a water heater on its own right now.',
	'closed'      => 'Not from the state program right now.',
	'notLaunched' => 'Not from the state program yet.',
	'unconfirmed' => 'We could not confirm it.',
);
?>
<h2>Are there water heater rebates in <?php echo $hwm_name; // phpcs:ignore ?>?</h2>
<p><?php echo esc_html( $hwm_word[ $hwm_status ] ?? '' ); ?> The federal tax credit for heat pump water heaters ended for heaters placed in service after <?php echo esc_html( hwm_date( (string) hwm_raw( 'incentives', 'credits.0.placedInServiceThrough' ) ) ); ?>, so what is left are the state's federally funded Home Energy Rebates and your utility's own rebates.</p>
<div class="notice">
	<p><strong><?php echo esc_html( (string) $hwm_reb['program'] ); ?></strong>, run by <?php echo esc_html( (string) $hwm_reb['agency'] ); ?>. <?php echo esc_html( (string) $hwm_reb['summary'] ); ?></p>
	<?php if ( ! empty( $hwm_reb['quote'] ) && ! empty( $hwm_reb['sourceUrl'] ) ) : ?>
		<p>What the official page said when we checked it on <?php echo esc_html( hwm_date( (string) $hwm_reb['checked'] ) ); ?>: “<?php echo esc_html( (string) $hwm_reb['quote'] ); ?>” (<?php echo $hwm_ext( (string) $hwm_reb['sourceUrl'], (string) $hwm_reb['agency'] ); // phpcs:ignore ?>)</p>
	<?php endif; ?>
</div>
<p>The federal program caps a heat pump water heater rebate at $<?php echo $hwm_f( 'heehrMax' ); // phpcs:ignore ?> where a state offers one. Utility rebates are separate and change often; look yours up by ZIP code in the <?php echo $hwm_ext( (string) hwm_raw( $hwm_sp, 'references.rebateFinder.sourceUrl' ), 'ENERGY STAR Rebate Finder' ); // phpcs:ignore ?> before you buy, because many require pre-approval or a listed model.</p>
<?php
$hwm_sections['rebates'] = (string) ob_get_clean();

/* ---- pro + limitations ---- */
ob_start();
?>
<h2>When to call a pro in <?php echo $hwm_name; // phpcs:ignore ?></h2>
<p>Use a licensed plumber for any gas water heater, and a licensed electrician when the job adds or changes a circuit. Most <?php echo $hwm_name; // phpcs:ignore ?> cities and counties require a permit and inspection for a water heater replacement; the local building department sets the fee and the rules. Get two or three itemized quotes and compare them with the lines above.</p>
<h2>Limitations</h2>
<p>These are planning estimates for <?php echo $hwm_name; // phpcs:ignore ?> as a whole, not quotes. The price factor is a statewide average of all consumer prices, not a survey of plumber rates, so a big metro can run higher and a rural county lower. Energy prices are <?php echo $hwm_f( 'kwhYear' ); // phpcs:ignore ?> state averages and your utility's rate may differ. Rebate status changes; check the official page before you count on one.</p>
<?php
$hwm_sections['limits'] = (string) ob_get_clean();

$hwm_order = $hwm_elec
	? array( 'cost', 'metros', 'items', 'calc', 'notes', 'running', 'rebates', 'fuel', 'tankless', 'limits' )
	: array( 'cost', 'items', 'metros', 'calc', 'notes', 'fuel', 'tankless', 'running', 'rebates', 'limits' );
?>
<p class="notice"><strong>How these prices are built.</strong> Unit prices are current retail listings. Labor, permit and extras start from our national ranges, anchored to the U.S. Department of Energy's 2024 water heater rules, and are scaled by <?php echo $hwm_name; // phpcs:ignore ?>'s price level. The same model runs the <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?> and every <?php echo hwm_link( 'methodology', 'formula is published' ); // phpcs:ignore ?>.</p>
<?php
foreach ( $hwm_order as $hwm_key ) {
	echo $hwm_sections[ $hwm_key ] ?? ''; // phpcs:ignore -- our own buffered markup.
}

$faq = array(
	array(
		'q' => 'How much does it cost to replace a water heater in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.g50.typical' ) . ' for a 50-gallon gas tank and $' . hwm_value( $hwm_c, 'figures.e50.typical' ) . ' for a 50-gallon electric tank, installed, including labor, permit and haul-away. Typical ranges are $' . hwm_value( $hwm_c, 'figures.g50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.g50.high' ) . ' for gas and $' . hwm_value( $hwm_c, 'figures.e50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.e50.high' ) . ' for electric.',
	),
	array(
		'q' => 'How much is labor to install a water heater in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.laborG' ) . ' for a gas tank and $' . hwm_value( $hwm_c, 'figures.laborE' ) . ' for an electric tank in a like-for-like swap, plus about $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for the permit and haul-away. These are our national ranges scaled by the state price factor of ' . hwm_value( $hwm_c, 'figures.mult' ) . '.',
	),
	array(
		'q' => 'How much does a tankless water heater cost installed in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.toTl.typical' ) . ' to switch from a gas tank to a whole-house gas tankless unit, including venting and gas line work, or about $' . hwm_value( $hwm_c, 'figures.gTl.typical' ) . ' to replace an existing tankless unit. In the coldest month one large unit gives about ' . hwm_value( $hwm_c, 'figures.gpmW' ) . ' gallons per minute here.',
	),
	array(
		'q' => 'Is gas or electric cheaper to run in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => 'For a typical household a gas tank costs about $' . hwm_value( $hwm_c, 'figures.gYear' ) . ' a year to run and a standard electric tank about $' . hwm_value( $hwm_c, 'figures.eYear' ) . ', at ' . hwm_value( $hwm_c, 'figures.kwhYear' ) . ' state average prices. A heat pump water heater is cheaper than both, about $' . hwm_value( $hwm_c, 'figures.hYear' ) . ' a year.',
	),
	array(
		'q' => 'Do I need a permit to replace a water heater in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => 'Usually yes. Most cities and counties require a permit and inspection for gas and electric replacements so the gas connection, venting and relief valve are checked. Our ' . hwm_value( $hwm_c, 'figures.name' ) . ' prices include about $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for the permit and haul-away; your local building department sets the actual fee.',
	),
	array(
		'q' => 'Are there heat pump water heater rebates in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => ( $hwm_word[ $hwm_status ] ?? '' ) . ' ' . (string) $hwm_reb['summary'] . ' Utility rebates are separate; check the ENERGY STAR Rebate Finder by ZIP code.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by State, ' . hwm_value( $hwm_c, 'figures.rppYear' ), 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Electric Power Annual, Table 2.10: residential electricity price by state', 'url' => (string) hwm_raw( 'energy-prices', 'sources.electricity.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Natural Gas Prices: average residential price by state', 'url' => (string) hwm_raw( 'energy-prices', 'sources.gasPrice.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => (string) hwm_raw( $hwm_sp, 'references.recs.publisher' ), 'title' => (string) hwm_raw( $hwm_sp, 'references.recs.title' ), 'url' => (string) hwm_raw( $hwm_sp, 'references.recs.pageUrl' ), 'accessed' => (string) hwm_raw( $hwm_sp, 'meta.sourceDate' ) ),
	array( 'publisher' => 'NOAA National Centers for Environmental Information', 'title' => 'Climate at a Glance: ' . hwm_value( $hwm_c, 'figures.name' ) . ' average temperature, 1991–2020 (basis of incoming water temperature)', 'url' => (string) hwm_raw( 'groundwater-temp-by-state', 'states.' . hwm_value( $hwm_c, 'figures.code' ) . '.sourceUrl' ), 'accessed' => (string) hwm_raw( 'groundwater-temp-by-state', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): installed cost and installation adders', 'url' => (string) hwm_raw( $hwm_ic, 'references.doe-storage-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater listing prices (unit prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'meta.sourceDate' ) ),
);
if ( ! empty( $hwm_reb['sourceUrl'] ) ) {
	$sources[] = array( 'publisher' => (string) $hwm_reb['agency'], 'title' => (string) $hwm_reb['program'], 'url' => (string) $hwm_reb['sourceUrl'], 'accessed' => (string) $hwm_reb['checked'] );
}
foreach ( (array) $hwm_t( 'notes' ) as $hwm_n ) {
	$sources[] = array( 'publisher' => (string) $hwm_n['source'], 'title' => (string) $hwm_n['heading'], 'url' => (string) $hwm_n['sourceUrl'], 'accessed' => (string) $hwm_n['checked'] );
}
$sources[] = array( 'publisher' => 'ENERGY STAR', 'title' => 'Rebate Finder', 'url' => (string) hwm_raw( $hwm_sp, 'references.rebateFinder.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_sp, 'meta.sourceDate' ) );
