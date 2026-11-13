<?php
/**
 * /water-heater-installation-cost-index/ (PHASES Phase 8 task 2, STRATEGY "Linkable assets"): annual by-state index of
 * installed price + 10-year energy cost for 50-gallon gas, electric and heat pump tanks, with a chart, a press-style
 * summary, an "embed this chart" snippet and a cite box. Every number comes from
 * data/computed/water-heater-installation-cost-index.json (npm run build:data, the same cost model as the calculators);
 * the chart is assets/charts/water-heater-cost-index.svg (npm run build:charts).
 * Intent: a ranked data asset (cost of ownership by state). The replacement cost article keeps "water heater
 * replacement / installation cost" and its own by-state price table; this page links to it (docs/DECISIONS.md).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-installation-cost-index';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_f   = (array) hwm_raw( $hwm_c, 'figures' );
$hwm_us  = (array) hwm_raw( $hwm_c, 'usRow' );
$hwm_y   = (string) $hwm_f['years'];
$hwm_png = HWM_URI . '/assets/charts/water-heater-cost-index.png';
$hwm_dim = (array) json_decode( (string) @file_get_contents( HWM_DIR . '/assets/charts/water-heater-cost-index.json' ), true ); // phpcs:ignore -- committed build output.
$hwm_svg = (string) @file_get_contents( HWM_DIR . '/assets/charts/water-heater-cost-index.svg' ); // phpcs:ignore -- committed build output, our own SVG.
$hwm_alt = $hwm_y . '-year cost to own a 50-gallon gas, heat pump or electric water heater in every U.S. state, from ' . $hwm_f['ownBest']['low']['name'] . ' (cheapest) to ' . $hwm_f['ownBest']['high']['name'] . ' (most expensive)';
$hwm_embed = '<!-- Chart by ' . hwm_site( 'brand' ) . '. Free to use. The credit line is optional; you may remove it. -->' . "\n"
	. '<img src="' . $hwm_png . '" alt="' . esc_attr( $hwm_alt ) . '" width="' . (int) ( $hwm_dim['svgWidth'] ?? 760 ) . '" height="' . (int) ( $hwm_dim['svgHeight'] ?? 955 ) . '" style="max-width:100%;height:auto" loading="lazy">' . "\n"
	. '<p style="font-size:13px">Chart by <a href="' . hwm_url() . '" rel="nofollow">' . esc_html( (string) hwm_site( 'brand' ) ) . '</a></p>';
$hwm_cite = hwm_site( 'brand' ) . ' (' . substr( (string) $hwm_f['lastReviewed'], 0, 4 ) . '). Water heater cost index by state. ' . hwm_url( 'water-heater-installation-cost-index' );
$hwm_links = (array) hwm_raw( $hwm_c, 'links' );
$hwm_csv   = hwm_origin() . '/data/water-heater-cost-index-by-state.csv';
$hwm_idx   = (array) hwm_raw( $hwm_c, 'tables.index' );

$related = array( 'water-heater-installation-cost', 'water-heater-cost-calculator', 'heat-pump-water-heater-cost', 'gas-vs-electric-water-heater', 'data' );
?>
<section class="press-summary" aria-labelledby="key-findings">
	<h2 id="key-findings">Key findings</h2>
	<ul>
		<li><strong><?php echo $hwm_v( 'ownBest.low.name' ); // phpcs:ignore ?></strong> is the cheapest state to own a water heater: $<?php echo $hwm_v( 'ownBest.low.v' ); // phpcs:ignore ?> over <?php echo esc_html( $hwm_y ); ?> years for the best of the three 50-gallon options. <strong><?php echo $hwm_v( 'ownBest.high.name' ); // phpcs:ignore ?></strong> is the most expensive at $<?php echo $hwm_v( 'ownBest.high.v' ); // phpcs:ignore ?>, <?php echo $hwm_v( 'ownBest.ratio' ); // phpcs:ignore ?> times as much.</li>
		<li>Installed prices differ by only <?php echo $hwm_v( 'installSpreadPct' ); // phpcs:ignore ?>% between the cheapest and dearest state, but yearly energy bills differ up to <?php echo $hwm_v( 'yearlyE.ratio' ); // phpcs:ignore ?> times. Over <?php echo esc_html( $hwm_y ); ?> years, energy decides where a water heater is expensive.</li>
		<li>A heat pump water heater costs less to own than an electric tank in all <?php echo $hwm_v( 'hpBeatsElOwn' ); // phpcs:ignore ?> states and DC: $<?php echo $hwm_v( 'hpVsElOwn.low.v' ); // phpcs:ignore ?> less in <?php echo $hwm_v( 'hpVsElOwn.low.name' ); // phpcs:ignore ?> up to $<?php echo $hwm_v( 'hpVsElOwn.high.v' ); // phpcs:ignore ?> less in <?php echo $hwm_v( 'hpVsElOwn.high.name' ); // phpcs:ignore ?>.</li>
		<li>Where a home has natural gas, a gas tank is still the cheapest to own in <?php echo $hwm_v( 'bestGas' ); // phpcs:ignore ?> states; the heat pump wins in <?php echo $hwm_v( 'bestHp' ); // phpcs:ignore ?>. A plain electric tank is the cheapest option in <?php echo $hwm_v( 'bestEl' ); // phpcs:ignore ?> states.</li>
		<li>At U.S. average prices the <?php echo esc_html( $hwm_y ); ?>-year cost is $<?php echo $hwm_v( 'us.g.own' ); // phpcs:ignore ?> for a gas tank, $<?php echo $hwm_v( 'us.h.own' ); // phpcs:ignore ?> for a heat pump and $<?php echo $hwm_v( 'us.e.own' ); // phpcs:ignore ?> for an electric tank.</li>
	</ul>
	<p>Data: BEA <?php echo $hwm_v( 'dataYear.rpp' ); // phpcs:ignore ?> price levels, EIA <?php echo $hwm_v( 'dataYear.electric' ); // phpcs:ignore ?> energy prices, NOAA water temperatures and DOE's 2024 water heater cost rules. <a href="<?php echo esc_url( $hwm_csv ); ?>">Download the full index (CSV)</a>.</p>
</section>

<h2>Which state is cheapest to own a water heater?</h2>
<p><?php echo $hwm_v( 'ownBest.low.name' ); // phpcs:ignore ?> is the cheapest: a 50-gallon gas tank there costs about $<?php echo $hwm_v( 'ownBest.low.v' ); // phpcs:ignore ?> to buy, install and run for <?php echo esc_html( $hwm_y ); ?> years. <?php echo $hwm_v( 'ownBest.high.name' ); // phpcs:ignore ?> is the most expensive at $<?php echo $hwm_v( 'ownBest.high.v' ); // phpcs:ignore ?> for its cheapest option. The chart ranks every state by the cheapest of the three tanks.</p>
<figure class="chart-figure">
	<?php echo $hwm_svg; // phpcs:ignore -- our own committed SVG (scripts/build-charts.mjs). ?>
	<figcaption>Each row shows one state's <?php echo esc_html( $hwm_y ); ?>-year cost for a gas tank (circle), heat pump (diamond) and electric tank (square). Hover or tap a row for its numbers; the full table is below.</figcaption>
</figure>
<?php echo hwm_table( $hwm_c, 'top' ); // phpcs:ignore -- escaped in the part. ?>

<h2>Water heater cost by state: the full index</h2>
<p>This table gives every state's typical installed price, yearly energy cost and <?php echo esc_html( $hwm_y ); ?>-year cost for the three common 50-gallon tanks, ranked from cheapest to own (1) to most expensive. The U.S. average row is at the bottom. States with their own cost page are linked.</p>
<div class="table-wrap" role="region" aria-label="Water heater cost index by state" tabindex="0">
	<table>
		<caption><?php echo esc_html( (string) $hwm_idx['caption'] ); ?></caption>
		<thead><tr>
			<?php foreach ( (array) $hwm_idx['columns'] as $hwm_col ) : ?>
				<th scope="col"<?php echo ! empty( $hwm_col['num'] ) ? ' class="num"' : ''; ?>><?php echo esc_html( $hwm_col['label'] . ( ! empty( $hwm_col['unit'] ) ? ' (' . $hwm_col['unit'] . ')' : '' ) ); ?></th>
			<?php endforeach; ?>
		</tr></thead>
		<tbody>
			<?php foreach ( (array) $hwm_idx['rows'] as $hwm_row ) : ?>
				<tr>
					<?php foreach ( (array) $hwm_idx['columns'] as $hwm_col ) : ?>
						<?php
						$hwm_cell = (string) ( $hwm_row[ $hwm_col['key'] ] ?? '' );
						if ( 'name' === $hwm_col['key'] ) :
							$hwm_slug = (string) ( $hwm_links[ $hwm_cell ] ?? '' );
							?>
							<th scope="row"><?php echo '' !== $hwm_slug ? hwm_link( $hwm_slug, $hwm_cell ) : esc_html( $hwm_cell ); // phpcs:ignore ?></th>
						<?php else : ?>
							<td<?php echo ! empty( $hwm_col['num'] ) ? ' class="num"' : ''; ?>><?php echo esc_html( $hwm_cell ); ?></td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot><tr>
			<td></td><th scope="row">U.S. average</th><td class="num">1.000</td>
			<?php foreach ( array( 'gi', 'ei', 'hi', 'gy', 'ey', 'hy', 'go', 'eo', 'ho', 'best' ) as $hwm_k ) : ?>
				<td<?php echo 'best' !== $hwm_k ? ' class="num"' : ''; ?>><?php echo esc_html( (string) ( $hwm_us[ $hwm_k ] ?? '' ) ); ?></td>
			<?php endforeach; ?>
		</tr></tfoot>
	</table>
	<p class="table-source">Source: <?php echo esc_html( (string) $hwm_idx['source'] ); ?> <a href="<?php echo esc_url( $hwm_csv ); ?>">Download as CSV</a>.</p>
</div>
<p>For an itemized price in one state, with metro areas, month-by-month running costs and rebate status, see the state pages under <?php echo hwm_link( 'water-heater-installation-cost', 'water heater replacement cost' ); // phpcs:ignore ?>.</p>

<h2>Why do running costs matter more than installation prices?</h2>
<p>Because labor prices vary far less between states than energy prices do. The cheapest state to install a 50-gallon gas tank, <?php echo $hwm_v( 'installG.low.name' ); // phpcs:ignore ?> ($<?php echo $hwm_v( 'installG.low.v' ); // phpcs:ignore ?>), is only <?php echo $hwm_v( 'installSpreadPct' ); // phpcs:ignore ?>% below the dearest, <?php echo $hwm_v( 'installG.high.name' ); // phpcs:ignore ?> ($<?php echo $hwm_v( 'installG.high.v' ); // phpcs:ignore ?>). The equipment costs the same everywhere; only labor, permit and haul-away follow local price levels.</p>
<p>Energy is different. Running an electric tank for a year costs about $<?php echo $hwm_v( 'yearlyE.low.v' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'yearlyE.low.name' ); // phpcs:ignore ?> and $<?php echo $hwm_v( 'yearlyE.high.v' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'yearlyE.high.name' ); // phpcs:ignore ?>, and a gas tank from $<?php echo $hwm_v( 'yearlyG.low.v' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'yearlyG.low.name' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'yearlyG.high.v' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'yearlyG.high.name' ); // phpcs:ignore ?>. Two things drive it: the local price of electricity or gas, and how cold the incoming water is, since colder water needs more heat to reach <?php echo $hwm_v( 'setpoint' ); // phpcs:ignore ?> °F.</p>
<div class="table-wrap" role="region" aria-label="Spread between states" tabindex="0">
<table>
	<caption>Spread between the cheapest and the dearest state</caption>
	<thead><tr><th scope="col">Measure</th><th scope="col">Lowest</th><th scope="col">Highest</th><th scope="col" class="num">Highest ÷ lowest</th></tr></thead>
	<tbody>
		<tr><th scope="row">Gas tank installed</th><td><?php echo $hwm_v( 'installG.low.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'installG.low.v' ); // phpcs:ignore ?></td><td><?php echo $hwm_v( 'installG.high.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'installG.high.v' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'installG.ratio' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Heat pump installed</th><td><?php echo $hwm_v( 'installH.low.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'installH.low.v' ); // phpcs:ignore ?></td><td><?php echo $hwm_v( 'installH.high.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'installH.high.v' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'installH.ratio' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Gas tank energy a year</th><td><?php echo $hwm_v( 'yearlyG.low.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'yearlyG.low.v' ); // phpcs:ignore ?></td><td><?php echo $hwm_v( 'yearlyG.high.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'yearlyG.high.v' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'yearlyG.ratio' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Electric tank energy a year</th><td><?php echo $hwm_v( 'yearlyE.low.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'yearlyE.low.v' ); // phpcs:ignore ?></td><td><?php echo $hwm_v( 'yearlyE.high.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'yearlyE.high.v' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'yearlyE.ratio' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Heat pump energy a year</th><td><?php echo $hwm_v( 'yearlyH.low.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'yearlyH.low.v' ); // phpcs:ignore ?></td><td><?php echo $hwm_v( 'yearlyH.high.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'yearlyH.high.v' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'yearlyH.ratio' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Electric tank, <?php echo esc_html( $hwm_y ); ?> years</th><td><?php echo $hwm_v( 'ownE.low.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'ownE.low.v' ); // phpcs:ignore ?></td><td><?php echo $hwm_v( 'ownE.high.name' ); // phpcs:ignore ?> $<?php echo $hwm_v( 'ownE.high.v' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'ownE.ratio' ); // phpcs:ignore ?></td></tr>
	</tbody>
</table>
</div>

<h2>Gas, electric or heat pump: which costs least over 10 years?</h2>
<p>If your home has natural gas, a gas tank is usually cheapest to own: it wins in <?php echo $hwm_v( 'bestGas' ); // phpcs:ignore ?> states, and a heat pump wins in the other <?php echo $hwm_v( 'bestHp' ); // phpcs:ignore ?>. In an all-electric home, a heat pump beats a standard electric tank in every state, because it uses about <?php echo $hwm_v( 'hpShare' ); // phpcs:ignore ?>% of the electricity for the same hot water.</p>
<p>The heat pump costs about $<?php echo $hwm_v( 'usHpPremium' ); // phpcs:ignore ?> more to install than an electric tank at U.S. average prices, but over <?php echo esc_html( $hwm_y ); ?> years it saves about $<?php echo $hwm_v( 'usHpVsElOwn' ); // phpcs:ignore ?> net of that. The gap is smallest where electricity is cheap and the water is warm, as in <?php echo $hwm_v( 'hpVsElOwn.low.name' ); // phpcs:ignore ?>, and largest where electricity is expensive and the water is cold, as in <?php echo $hwm_v( 'hpVsElOwn.high.name' ); // phpcs:ignore ?>. Our <?php echo hwm_link( 'heat-pump-water-heater-cost', 'heat pump water heater cost and payback guide' ); // phpcs:ignore ?> runs the payback for your own household, and the <?php echo hwm_link( 'gas-vs-electric-water-heater', 'gas vs electric calculator' ); // phpcs:ignore ?> compares all five types.</p>
<dl>
	<dt><?php echo esc_html( $hwm_y ); ?>-year cost of ownership</dt>
	<dd>The installed price of the water heater plus <?php echo esc_html( $hwm_y ); ?> years of the energy it uses, at today's prices. It leaves out rebates, repairs and interest, so it compares options on the two costs that differ most between them.</dd>
</dl>

<h2>Price your own job</h2>
<p>The index assumes a typical household and a like-for-like swap. Pick your own type, size, state and extras to see an itemized price from the same model.</p>
<?php
ob_start();
hwm_part( 'cost-inputs' );
hwm_part( 'calculator', array( 'tool' => 'water-heater-cost-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater cost calculator' ) );
?>

<h2>How we built the index</h2>
<p>Every number comes from the same cost model and formulas as our calculators, applied to each state's own data. Nothing is a survey average or a copied cost guide figure.</p>
<ol>
	<li><strong>Installed price.</strong> Retail unit prices for a 50-gallon gas tank, electric tank and heat pump (listings checked <?php echo esc_html( hwm_date( (string) $hwm_f['unitsReviewed'] ) ); ?>), plus labor, permit and haul-away anchored to the U.S. Department of Energy's 2024 water heater rule. Labor and extras are multiplied by the state's Regional Price Parity from the Bureau of Economic Analysis ÷ 100; the unit price is not.</li>
	<li><strong>Yearly energy.</strong> A typical household uses <?php echo $hwm_v( 'gallons' ); // phpcs:ignore ?> gallons of hot water a day, heated from the state's average incoming water temperature to <?php echo $hwm_v( 'setpoint' ); // phpcs:ignore ?> °F, divided by the median efficiency rating (UEF) of current models, times the state's EIA residential electricity or gas price.</li>
	<li><strong><?php echo esc_html( $hwm_y ); ?>-year cost.</strong> Installed price + <?php echo esc_html( $hwm_y ); ?> × yearly energy. All three tanks last longer than <?php echo esc_html( $hwm_y ); ?> years on average, so none needs replacing inside the window.</li>
	<li><strong>Rank.</strong> States are ordered by the lowest of their three <?php echo esc_html( $hwm_y ); ?>-year costs.</li>
</ol>
<p>The formulas and our checks against federal EnergyGuide labels and ENERGY STAR data are on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#cost-index' ); ?>">our methodology page</a>.</p>
<p><strong>Limitations.</strong> These are state averages for planning, not quotes. Cities usually run above their state, rural areas below. The gas column assumes the home already has a gas line; the heat pump column assumes a like-for-like replacement with room for the unit. Rebates and utility programs, which can cut a heat pump's price a lot, are left out because they change often. Energy prices are annual averages, and your own use may be higher or lower than a typical household's.</p>

<h2>Embed this chart</h2>
<p>Writers, teachers and utilities may republish the chart for free. Copy the code below; it shows the image with a small credit line, which you may remove. The underlying numbers are in the <?php echo hwm_link( 'data', 'data downloads' ); // phpcs:ignore ?> under CC BY 4.0.</p>
<div class="cite-box">
	<p class="cite-box__label">Embed code</p>
	<textarea readonly rows="5" aria-label="Chart embed code"><?php echo esc_textarea( $hwm_embed ); ?></textarea>
</div>

<h2>How to cite this index</h2>
<div class="cite-box">
	<p class="cite-box__label">Cite this</p>
	<textarea readonly rows="3" aria-label="Citation"><?php echo esc_textarea( $hwm_cite ); ?></textarea>
</div>
<p>We update the index when EIA or BEA publish new annual figures and note the date at the bottom of this page. Found a problem? Write to <a href="mailto:<?php echo esc_attr( (string) hwm_site( 'contact.email' ) ); ?>"><?php echo esc_html( (string) hwm_site( 'contact.email' ) ); ?></a>.</p>
<?php
$hwm_ic = 'installation-costs';
$faq    = array(
	array(
		'q' => 'What is the cheapest state to replace a water heater?',
		'a' => $hwm_f['installG']['low']['name'] . ' has the lowest installed price in our index: about $' . $hwm_f['installG']['low']['v'] . ' for a 50-gallon gas tank and $' . $hwm_f['installE']['low']['v'] . ' for an electric one. Counting ' . $hwm_y . ' years of energy, ' . $hwm_f['ownBest']['low']['name'] . ' is the cheapest state to own one.',
	),
	array(
		'q' => 'What is the most expensive state for a water heater?',
		'a' => $hwm_f['installG']['high']['name'] . ' has the highest installed prices ($' . $hwm_f['installG']['high']['v'] . ' for a 50-gallon gas tank). Counting ' . $hwm_y . ' years of energy, ' . $hwm_f['ownBest']['high']['name'] . ' is the most expensive at $' . $hwm_f['ownBest']['high']['v'] . ' for its cheapest option, mainly because of high energy prices.',
	),
	array(
		'q' => 'How much does a water heater cost to run per year?',
		'a' => 'At U.S. average prices a typical household pays about $' . $hwm_f['us']['g']['yearly'] . ' a year for a 50-gallon gas tank, $' . $hwm_f['us']['e']['yearly'] . ' for an electric tank and $' . $hwm_f['us']['h']['yearly'] . ' for a heat pump. By state, an electric tank ranges from $' . $hwm_f['yearlyE']['low']['v'] . ' to $' . $hwm_f['yearlyE']['high']['v'] . '.',
	),
	array(
		'q' => 'Is a heat pump water heater cheaper than electric in every state?',
		'a' => 'Over ' . $hwm_y . ' years, yes: in all ' . $hwm_f['hpBeatsElOwn'] . ' states and DC in our index, even with no rebate. It costs more to install, but it uses about ' . $hwm_f['hpShare'] . '% of the electricity, so the saving ranges from $' . $hwm_f['hpVsElOwn']['low']['v'] . ' to $' . $hwm_f['hpVsElOwn']['high']['v'] . '.',
	),
	array(
		'q' => 'Why is the cost index for 50-gallon tanks only?',
		'a' => 'Using one size for every state keeps the comparison like for like, and 50 gallons fits a typical family household. For other sizes, tankless units or a fuel switch, use the cost calculator on this page or the type guides it links to.',
	),
	array(
		'q' => 'Can I use this data in my article?',
		'a' => 'Yes. The full index is a free CSV under CC BY 4.0: credit ' . hwm_site( 'brand' ) . ' with a link. You may also embed the chart with the code on this page; its credit line is optional.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Bureau of Economic Analysis', 'title' => 'Regional Price Parities by State, ' . $hwm_f['dataYear']['rpp'], 'url' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'regional-cost-index', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Electric Power Annual, Table 2.10: average residential electricity price by state', 'url' => (string) hwm_raw( 'energy-prices', 'sources.electricity.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Natural gas prices: average residential price by state', 'url' => (string) hwm_raw( 'energy-prices', 'sources.gasPrice.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'NOAA National Centers for Environmental Information', 'title' => 'Climate at a Glance: statewide air temperature normals (incoming water temperature estimate)', 'url' => (string) hwm_raw( 'groundwater-temp-by-state', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'groundwater-temp-by-state', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): installed cost and installation adders', 'url' => (string) hwm_raw( $hwm_ic, 'references.doe-storage-2024.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ic, 'lastReviewed' ) ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater listing prices (unit prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'cost-ranges', 'meta.sourceDate' ) ),
);
