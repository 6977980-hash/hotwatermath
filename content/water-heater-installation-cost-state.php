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
<p>That is $<?php echo $hwm_f( 'g50Diff' ); // phpcs:ignore ?> <?php echo $hwm_f( 'g50DiffSign' ); // phpcs:ignore ?> than the same gas job at U.S. average prices ($<?php echo $hwm_f( 'usG50' ); // phpcs:ignore ?>). The gap comes only from labor, permit and extras: <?php echo $hwm_name; // phpcs:ignore ?>'s price level is <?php echo $hwm_f( 'multVs' ); // phpcs:ignore ?> the national level (Bureau of Economic Analysis, <?php echo $hwm_f( 'rppYear' ); // phpcs:ignore ?>), the <?php echo $hwm_f( 'multRankPhrase' ); // phpcs:ignore ?> of <?php echo $hwm_f( 'statesLabel' ); // phpcs:ignore ?>. The water heater itself sells for the same listing price everywhere.</p>
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
	<dd><?php echo $hwm_name; // phpcs:ignore ?>'s Regional Price Parity divided by 100, here <?php echo $hwm_f( 'mult' ); // phpcs:ignore ?>. It scales labor, permit and extras, not the unit.</dd>
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
<p>A quote well above these lines usually includes extras; the <?php echo hwm_link( 'water-heater-installation-cost', 'national replacement cost guide' ); // phpcs:ignore ?> explains each one.</p>
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
<p><?php echo esc_html( (string) $hwm_n['text'] ); ?></p>
<?php if ( ! empty( $hwm_n['quote'] ) ) : ?>
<blockquote class="source-quote"><p>&ldquo;<?php echo esc_html( (string) $hwm_n['quote'] ); ?>&rdquo;</p><footer><?php echo $hwm_ext( (string) $hwm_n['sourceUrl'], (string) $hwm_n['source'] ); // phpcs:ignore ?>, checked <?php echo esc_html( hwm_date( (string) $hwm_n['checked'] ) ); ?></footer></blockquote>
<?php else : ?>
<p>Source: <?php echo $hwm_ext( (string) $hwm_n['sourceUrl'], (string) $hwm_n['source'] ); // phpcs:ignore ?>.</p>
<?php endif; ?>
<?php if ( 'codeItems' === ( $hwm_n['presetExtra'] ?? '' ) ) : ?>
<p>With straps and the other code items, a 50-gallon gas replacement in <?php echo $hwm_name; // phpcs:ignore ?> comes to about $<?php echo $hwm_f( 'g50code.typical' ); // phpcs:ignore ?> once an expansion tank is added too.</p>
<?php endif; ?>
	<?php
endforeach;
$hwm_sections['notes'] = (string) ob_get_clean();

/* ---- what makes this state different (hand-written per state, numbers filled from its figures) ---- */
ob_start();
$hwm_loc = (array) $hwm_t( 'local' );
?>
<h2><?php echo esc_html( (string) ( $hwm_loc['heading'] ?? '' ) ); ?></h2>
<?php foreach ( (array) ( $hwm_loc['paras'] ?? array() ) as $hwm_lp ) : ?>
<p><?php echo esc_html( (string) $hwm_lp ); ?></p>
<?php endforeach; ?>
<?php
$hwm_sections['local'] = (string) ob_get_clean();

/* ---- running cost ---- */
ob_start();
?>
<h2>Which water heater is cheapest to run in <?php echo $hwm_name; // phpcs:ignore ?>?</h2>
<p>A heat pump water heater is the cheapest tank to run in <?php echo $hwm_name; // phpcs:ignore ?>, about $<?php echo $hwm_f( 'hYear' ); // phpcs:ignore ?> a year for a typical household, against $<?php echo $hwm_f( 'gYear' ); // phpcs:ignore ?> for a gas tank and $<?php echo $hwm_f( 'eYear' ); // phpcs:ignore ?> for a standard electric tank. <?php if ( 'gas_tankless' === $hwm_t( 'cheapestId' ) ) : ?>A gas tankless unit is lower still, about $<?php echo $hwm_f( 'gasTanklessYear' ); // phpcs:ignore ?>.<?php endif; ?></p>
<p>Prices: <?php echo $hwm_f( 'kwhCents' ); // phpcs:ignore ?> cents per kWh (<?php echo $hwm_f( 'kwhRankOrd' ); // phpcs:ignore ?> cheapest) and $<?php echo $hwm_f( 'therm' ); // phpcs:ignore ?> per therm (<?php echo $hwm_f( 'thermRankOrd' ); // phpcs:ignore ?> cheapest), EIA <?php echo $hwm_f( 'kwhYear' ); // phpcs:ignore ?>; <?php echo $hwm_f( 'gallons' ); // phpcs:ignore ?> gallons a day heated from <?php echo $hwm_f( 'inletAnnual' ); // phpcs:ignore ?> °F to <?php echo $hwm_f( 'setpointF' ); // phpcs:ignore ?> °F.</p>
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
<p>Propane serves about <?php echo $hwm_f( 'propanePct' ); // phpcs:ignore ?>% of homes here; the <?php echo hwm_link( 'gas-vs-electric-water-heater', 'gas vs electric calculator' ); // phpcs:ignore ?> prices it by the gallon.</p>
<?php
$hwm_sections['fuel'] = (string) ob_get_clean();

/* ---- tankless ---- */
ob_start();
?>
<h2>Will a tankless water heater keep up in a <?php echo $hwm_name; // phpcs:ignore ?> winter?</h2>
<p>In the coldest month <?php echo $hwm_name; // phpcs:ignore ?>'s incoming water averages about <?php echo $hwm_f( 'inletWinter' ); // phpcs:ignore ?> °F, so a tankless unit must heat it <?php echo $hwm_f( 'riseW' ); // phpcs:ignore ?> °F to reach <?php echo $hwm_f( 'setpointF' ); // phpcs:ignore ?> °F. At that rise a large <?php echo $hwm_f( 'refInput' ); // phpcs:ignore ?> BTU/h gas unit delivers about <?php echo $hwm_f( 'gpmW' ); // phpcs:ignore ?> gallons per minute: <?php echo $hwm_f( 'showersW' ); // phpcs:ignore ?> shower<?php echo 1 === (int) $hwm_t( 'showersW' ) ? '' : 's'; ?> at full hot flow at once, or more when each shower mixes in cold water, as our <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless size calculator' ); // phpcs:ignore ?> assumes.</p>
<p>The same unit gives about <?php echo $hwm_f( 'seasonLossPct' ); // phpcs:ignore ?>% less hot water in the coldest month than in the warmest, so size it for winter.</p>
<h3>Month by month in <?php echo $hwm_name; // phpcs:ignore ?></h3>
<p>Incoming water is coldest in <?php echo $hwm_f( 'coldMonth' ); // phpcs:ignore ?> (about <?php echo $hwm_f( 'coldMonthInlet' ); // phpcs:ignore ?> °F) and warmest in <?php echo $hwm_f( 'warmMonth' ); // phpcs:ignore ?> (about <?php echo $hwm_f( 'warmMonthInlet' ); // phpcs:ignore ?> °F). Each day in <?php echo $hwm_f( 'coldMonth' ); // phpcs:ignore ?> takes about <?php echo $hwm_f( 'winterExtraPct' ); // phpcs:ignore ?>% more energy to heat the same hot water than a day in <?php echo $hwm_f( 'warmMonth' ); // phpcs:ignore ?>, for any type of water heater.</p>
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
<p><?php echo esc_html( $hwm_word[ $hwm_status ] ?? '' ); ?> The federal tax credit ended after <?php echo esc_html( hwm_date( (string) hwm_raw( 'incentives', 'credits.0.placedInServiceThrough' ) ) ); ?>, leaving the state program and utility rebates.</p>
<div class="notice">
	<p><strong><?php echo esc_html( (string) $hwm_reb['program'] ); ?></strong>, run by <?php echo esc_html( (string) $hwm_reb['agency'] ); ?>. <?php echo esc_html( (string) $hwm_reb['summary'] ); ?></p>
	<?php if ( ! empty( $hwm_reb['quote'] ) && ! empty( $hwm_reb['sourceUrl'] ) ) : ?>
		<p>What the official page said when we checked it on <?php echo esc_html( hwm_date( (string) $hwm_reb['checked'] ) ); ?>: “<?php echo esc_html( (string) $hwm_reb['quote'] ); ?>” (<?php echo $hwm_ext( (string) $hwm_reb['sourceUrl'], (string) $hwm_reb['agency'] ); // phpcs:ignore ?>)</p>
	<?php endif; ?>
</div>
<?php if ( $hwm_t( 'rebPrograms' ) ) : ?>
<h3>Utility and state rebates in <?php echo $hwm_name; // phpcs:ignore ?></h3>
<p>These are the water heater programs we could open and confirm on their own pages. Each one serves only its own customers or area.</p>
<?php echo hwm_table( $hwm_c, 'utilityRebates' ); // phpcs:ignore ?>
<?php endif; ?>
<?php foreach ( (array) $hwm_t( 'rebEnded' ) as $hwm_e ) : ?>
<p>Ended or changed: <?php echo esc_html( (string) $hwm_e['sponsor'] ); ?> says &ldquo;<?php echo esc_html( (string) $hwm_e['quote'] ); ?>&rdquo; (<?php echo $hwm_ext( (string) $hwm_e['sourceUrl'], 'checked ' . hwm_date( (string) $hwm_e['checked'] ) ); // phpcs:ignore ?>)</p>
<?php endforeach; ?>
<p>The federal program caps a heat pump water heater rebate at $<?php echo $hwm_f( 'heehrMax' ); // phpcs:ignore ?> where a state offers one. For any other utility, look yours up by ZIP code in the <?php echo $hwm_ext( (string) hwm_raw( $hwm_sp, 'references.rebateFinder.sourceUrl' ), 'ENERGY STAR Rebate Finder' ); // phpcs:ignore ?> before you buy, because many require pre-approval or a listed model.</p>
<?php
$hwm_sections['rebates'] = (string) ob_get_clean();

/* ---- pro + limitations ---- */
ob_start();
?>
<h2>When to call a pro in <?php echo $hwm_name; // phpcs:ignore ?></h2>
<p>Use a licensed plumber for any gas water heater, and a licensed electrician when the job adds or changes a circuit. Your local building department sets the permit rules and fee. Compare two or three itemized quotes with the lines above.</p>
<h2>Limitations</h2>
<p>These are planning estimates, not quotes. The price factor averages all consumer prices, not plumber rates. Energy prices are <?php echo $hwm_f( 'kwhYear' ); // phpcs:ignore ?> state averages, and rebates change; check each program's page before you count on one.</p>
<?php
$hwm_sections['limits'] = (string) ob_get_clean();

$hwm_order = $hwm_elec
	? array( 'cost', 'local', 'metros', 'items', 'calc', 'notes', 'running', 'rebates', 'fuel', 'tankless', 'limits' )
	: array( 'cost', 'local', 'items', 'metros', 'calc', 'notes', 'fuel', 'tankless', 'running', 'rebates', 'limits' );
?>
<p class="notice"><strong>How these prices are built.</strong> Retail unit prices plus our national labor ranges scaled by <?php echo $hwm_name; // phpcs:ignore ?>'s price level, the same model as the <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?> (<?php echo hwm_link( 'methodology', 'formulas published' ); // phpcs:ignore ?>).</p>
<?php
foreach ( $hwm_order as $hwm_key ) {
	echo $hwm_sections[ $hwm_key ] ?? ''; // phpcs:ignore -- our own buffered markup.
}

$hwm_progs       = (array) $hwm_t( 'rebPrograms' );
$hwm_permit_note = null;
foreach ( (array) $hwm_t( 'notes' ) as $hwm_n ) {
	if ( 'seismic' !== ( $hwm_n['id'] ?? '' ) && 'title24' !== ( $hwm_n['id'] ?? '' ) ) {
		$hwm_permit_note = $hwm_n;
	}
}
$faq = array(
	array(
		'q' => 'How much does it cost to replace a water heater in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.g50.typical' ) . ' for a 50-gallon gas tank and $' . hwm_value( $hwm_c, 'figures.e50.typical' ) . ' for a 50-gallon electric tank, installed, including labor, permit and haul-away. Typical ranges are $' . hwm_value( $hwm_c, 'figures.g50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.g50.high' ) . ' for gas and $' . hwm_value( $hwm_c, 'figures.e50.low' ) . ' to $' . hwm_value( $hwm_c, 'figures.e50.high' ) . ' for electric.',
	),
	array(
		'q' => 'How much does a tankless water heater cost installed in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.toTl.typical' ) . ' to switch from a gas tank to a whole-house gas tankless unit, including venting and gas line work, or about $' . hwm_value( $hwm_c, 'figures.gTl.typical' ) . ' to replace an existing tankless unit. In the coldest month one large unit gives about ' . hwm_value( $hwm_c, 'figures.gpmW' ) . ' gallons per minute here.',
	),
	array(
		'q' => 'What is different about replacing a water heater in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => (string) ( ( (array) $hwm_t( 'local' ) )['paras'][0] ?? '' ),
	),
	array(
		'q' => 'Do I need a permit to replace a water heater in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => ( $hwm_permit_note ? (string) $hwm_permit_note['text'] . ' ' : 'Usually yes; your city or county building department sets the rules. ' ) . 'Our prices include about $' . hwm_value( $hwm_c, 'figures.permit' ) . ' for the permit and haul-away.',
	),
	array(
		'q' => 'Are there heat pump water heater rebates in ' . hwm_value( $hwm_c, 'figures.name' ) . '?',
		'a' => ( $hwm_word[ $hwm_status ] ?? '' ) . ' ' . (string) $hwm_reb['summary'] . ( $hwm_progs ? ' Utility programs we confirmed: ' . implode( '; ', array_map( static fn( $x ) => $x['sponsor'] . ', ' . lcfirst( (string) $x['offer'] ), $hwm_progs ) ) . '.' : ' Check the ENERGY STAR Rebate Finder by ZIP code.' ),
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
foreach ( $hwm_progs as $hwm_x ) {
	$sources[] = array( 'publisher' => (string) $hwm_x['sponsor'], 'title' => 'Water heater rebate: ' . (string) $hwm_x['offer'], 'url' => (string) $hwm_x['sourceUrl'], 'accessed' => (string) $hwm_x['checked'] );
}
$sources[] = array( 'publisher' => 'ENERGY STAR', 'title' => 'Rebate Finder', 'url' => (string) hwm_raw( $hwm_sp, 'references.rebateFinder.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_sp, 'meta.sourceDate' ) );
