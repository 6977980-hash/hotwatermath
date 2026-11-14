<?php
/**
 * /how-long-does-a-water-heater-last/ (STRATEGY §2 item 1b): informational article with the age calculator embedded.
 * Numbers come from data/lifespans.json, data/computed/how-long-does-a-water-heater-last.json
 * and data/computed/water-heater-age-calculator.json (the embedded calculator's default).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_ls  = 'lifespans';
$hwm_c   = 'computed/how-long-does-a-water-heater-last';
$hwm_l   = static fn( string $path ): string => esc_html( hwm_value( $hwm_ls, $path ) );
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, $path ) );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_ev  = (array) hwm_raw( $hwm_ls, 'evidence' );
$hwm_mr  = (array) hwm_raw( $hwm_ls, 'manufacturerViews.rheem' );
$hwm_ma  = (array) hwm_raw( $hwm_ls, 'manufacturerViews.ao-smith' );
$hwm_mb  = (array) hwm_raw( $hwm_ls, 'manufacturerViews.bradford-white' );
$hwm_hs  = (array) hwm_raw( $hwm_ls, 'adjustments.hardnessScale' );
$hwm_adj = (array) hwm_raw( $hwm_ls, 'adjustments' );
$hwm_bar = (array) hwm_raw( $hwm_c, 'bars' );
$hwm_doe = (string) hwm_raw( $hwm_ls, 'meta.sourceUrl' );
$hwm_fmp = (string) ( $hwm_ev[2]['sourceUrl'] ?? '' );
$hwm_tkl = (string) ( $hwm_ev[3]['sourceUrl'] ?? '' );
$hwm_bwf = (string) ( $hwm_mb['sourceUrl'] ?? '' );
$hwm_t   = 'types.gas_tank.';
$hwm_df  = (array) hwm_raw( $hwm_ls, 'doeFigures' );

$related = array( 'water-heater-age-calculator', 'repair-or-replace-water-heater', 'bradford-white-water-heater-age', 'rheem-water-heater-age' );
?>
<h2>Water heater lifespan by type</h2>
<p>A gas or electric storage tank water heater typically lasts <?php echo $hwm_l( $hwm_t . 'typical' ); // phpcs:ignore ?> years, and most fail between <?php echo $hwm_l( $hwm_t . 'low' ); // phpcs:ignore ?> and <?php echo $hwm_l( $hwm_t . 'high' ); // phpcs:ignore ?>. A heat pump water heater (hybrid) has the same expected life, because it is a storage tank with a heat pump on top. A tankless water heater averages about <?php echo $hwm_l( 'types.gas_tankless.typical' ); // phpcs:ignore ?> years.</p>
<?php echo hwm_table( 'computed/reference-tables', 'lifespans' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Service life (lifetime)</dt>
	<dd>The age at which a water heater is retired because it leaked, failed or was replaced. The Department of Energy estimates it from national housing surveys (the Residential Energy Consumption Survey and the American Housing Survey), which record the age of millions of installed units. A "typical" life is the middle of that curve, not a guarantee for any one tank.</dd>
</dl>

<h2>What do manufacturers say, and why are their numbers lower?</h2>
<p>Water heater makers quote shorter lives for tanks than DOE does. Rheem says tank units typically last <?php echo esc_html( $hwm_mr['tankLow'] . ' to ' . $hwm_mr['tankHigh'] ); ?> years, A. O. Smith says <?php echo esc_html( $hwm_ma['tankLow'] . ' to ' . $hwm_ma['tankHigh'] ); ?>, and Bradford White says its heaters "can last <?php echo esc_html( (string) $hwm_mb['tankLow'] ); ?> years or more" with professional installation and maintenance. For tankless units, Rheem and A. O. Smith both say <?php echo esc_html( $hwm_mr['tanklessLow'] . ' to ' . $hwm_mr['tanklessHigh'] ); ?> years or more.</p>
<figure class="figure">
	<svg viewBox="0 0 640 250" width="640" height="250" role="img" aria-labelledby="life-bars-t life-bars-d">
		<title id="life-bars-t">Water heater lifespan ranges: DOE data and manufacturer statements</title>
		<desc id="life-bars-d"><?php
		$hwm_d = array();
		foreach ( (array) ( $hwm_bar['rows'] ?? array() ) as $hwm_r ) {
			$hwm_d[] = $hwm_r['label'] . ' ' . ( $hwm_r['low'] === $hwm_r['high'] ? 'about ' . $hwm_r['low'] : $hwm_r['low'] . ' to ' . $hwm_r['high'] ) . ' years';
		}
		echo esc_html( implode( '; ', $hwm_d ) . '.' );
		?></desc>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<?php foreach ( (array) ( $hwm_bar['rows'] ?? array() ) as $hwm_i => $hwm_r ) : ?>
				<text x="172" y="<?php echo esc_attr( (string) ( 30 + $hwm_i * 32 ) ); ?>" text-anchor="end"><?php echo esc_html( (string) $hwm_r['label'] ); ?></text>
			<?php endforeach; ?>
		</g>
		<svg x="186" y="0" width="430" height="250" overflow="visible">
			<g stroke="#d5dbe1" stroke-width="1">
				<?php foreach ( (array) ( $hwm_bar['ticks'] ?? array() ) as $hwm_tk ) : ?>
					<line x1="<?php echo esc_attr( $hwm_tk['x'] . '%' ); ?>" x2="<?php echo esc_attr( $hwm_tk['x'] . '%' ); ?>" y1="10" y2="208"/>
				<?php endforeach; ?>
			</g>
			<?php foreach ( (array) ( $hwm_bar['rows'] ?? array() ) as $hwm_i => $hwm_r ) : ?>
				<rect x="<?php echo esc_attr( $hwm_r['x'] . '%' ); ?>" y="<?php echo esc_attr( (string) ( 17 + $hwm_i * 32 ) ); ?>" width="<?php echo esc_attr( $hwm_r['w'] . '%' ); ?>" height="18" rx="3" fill="<?php echo 'doe' === $hwm_r['kind'] ? '#0b4a7d' : '#a35a00'; ?>"/>
				<?php if ( null !== ( $hwm_r['tx'] ?? null ) && $hwm_r['low'] !== $hwm_r['high'] ) : ?>
					<rect x="<?php echo esc_attr( $hwm_r['tx'] . '%' ); ?>" y="<?php echo esc_attr( (string) ( 14 + $hwm_i * 32 ) ); ?>" width="3" height="24" fill="#17212e"/>
				<?php endif; ?>
			<?php endforeach; ?>
			<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="12" fill="#4a5562" text-anchor="middle">
				<?php foreach ( (array) ( $hwm_bar['ticks'] ?? array() ) as $hwm_tk ) : ?>
					<text x="<?php echo esc_attr( $hwm_tk['x'] . '%' ); ?>" y="226"><?php echo esc_html( (string) $hwm_tk['t'] ); ?></text>
				<?php endforeach; ?>
				<text x="50%" y="245">years</text>
			</g>
		</svg>
	</svg>
	<figcaption>Blue: U.S. Department of Energy (black mark = typical tank life). Orange: what the manufacturer says on its own site. DOE publishes one average for tankless units, so that bar is a line.</figcaption>
</figure>
<p>The gap has a simple reason. DOE counts every unit still running in real homes, including old tanks that keep working long after a maker would advise replacement. A manufacturer's figure is closer to "plan to replace by", and it is cautious by design. Both are useful: the DOE number tells you what is normal, the maker's number tells you when to start budgeting.</p>

<h2>Why do sources disagree on the exact number?</h2>
<p>Even DOE has published more than one figure. Its 2024 rule for storage water heaters estimates an average lifetime of "around <?php echo esc_html( (string) $hwm_df['storage2024AverageYears'] ); ?> years" from a survival curve fitted to survey data. Its 2010 rule reported <?php echo esc_html( (string) $hwm_df['storage2010AverageYears'] ); ?> years, and DOE's Federal Energy Management Program and ENERGY STAR (for heat pump models) use the same <?php echo esc_html( (string) $hwm_df['storage2010AverageYears'] ); ?> (<?php echo $hwm_ext( $hwm_doe, 'DOE 2024 rule' ); // phpcs:ignore ?>, <?php echo $hwm_ext( $hwm_fmp, 'DOE FEMP' ); // phpcs:ignore ?>). The industry group AHRI assumes <?php echo esc_html( $hwm_df['ahriLowYears'] . ' to ' . $hwm_df['ahriHighYears'] ); ?> years, according to comments quoted in the same rule.</p>
<p>We use <?php echo $hwm_l( $hwm_t . 'typical' ); // phpcs:ignore ?> years as the typical tank life and <?php echo $hwm_l( $hwm_t . 'low' ); // phpcs:ignore ?> to <?php echo $hwm_l( $hwm_t . 'high' ); // phpcs:ignore ?> as the range. That keeps the figure that most federal programs use in the middle and the newest DOE estimate as the upper end. For tankless units, DOE's 2024 rule for gas-fired instantaneous water heaters gives about <?php echo $hwm_l( 'types.gas_tankless.typical' ); // phpcs:ignore ?> years (<?php echo $hwm_ext( $hwm_tkl, 'DOE tankless rule' ); // phpcs:ignore ?>). We found no separate DOE figure for electric tankless models, so we apply the same average and say so.</p>

<?php
$hwm_rng = static fn( $lo, $hi ): string => ( null === $lo || '' === $lo ) ? 'Not stated' : ( ( null === $hi || $lo === $hi ) ? $lo . ' or more' : $lo . ' to ' . $hi );
$hwm_cmp = array(
	array( 'DOE 2024 storage rule (average)', 'Federal data', 'around ' . $hwm_df['storage2024AverageYears'], 'about ' . hwm_value( $hwm_ls, 'types.gas_tankless.typical' ) . ' (2024 tankless rule)', $hwm_doe ),
	array( 'DOE 2010 rule, FEMP and ENERGY STAR', 'Federal data', (string) $hwm_df['storage2010AverageYears'], 'Not stated', $hwm_fmp ),
	array( 'AHRI (industry group, quoted by DOE)', 'Industry', $hwm_df['ahriLowYears'] . ' to ' . $hwm_df['ahriHighYears'], 'Not stated', $hwm_doe ),
);
foreach ( array( $hwm_mr, $hwm_ma, $hwm_mb ) as $hwm_m ) {
	$hwm_cmp[] = array( (string) $hwm_m['label'], 'Manufacturer', $hwm_rng( $hwm_m['tankLow'] ?? null, $hwm_m['tankHigh'] ?? null ), $hwm_rng( $hwm_m['tanklessLow'] ?? null, $hwm_m['tanklessHigh'] ?? null ), (string) $hwm_m['sourceUrl'] );
}
foreach ( (array) hwm_raw( $hwm_ls, 'costGuideViews' ) as $hwm_g ) {
	$hwm_cmp[] = array( (string) $hwm_g['label'], 'Cost guide', (string) $hwm_g['tank'], (string) $hwm_g['tankless'], (string) $hwm_g['sourceUrl'] );
}
?>
<h3>Every lifespan figure side by side</h3>
<p>Federal data runs longest, manufacturers sit lowest, and the popular cost guides mostly repeat the manufacturers. We opened each source on <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_ls, 'costGuideViews.0.checked' ) ) ); ?>; the cost guides are listed for comparison only and are not used in our calculator.</p>
<div class="table-wrap" role="region" aria-label="Water heater lifespan by source" tabindex="0">
	<table>
		<caption>Water heater lifespan by source, in years</caption>
		<thead><tr><th scope="col">Source</th><th scope="col">Kind</th><th scope="col">Tank</th><th scope="col">Tankless</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_cmp as $hwm_row ) : ?>
				<tr><th scope="row"><a href="<?php echo esc_url( $hwm_row[4] ); ?>" rel="noopener"><?php echo esc_html( $hwm_row[0] ); ?></a></th><td><?php echo esc_html( $hwm_row[1] ); ?></td><td><?php echo esc_html( $hwm_row[2] ); ?></td><td><?php echo esc_html( $hwm_row[3] ); ?></td></tr>
			<?php endforeach; ?>
			<tr><th scope="row">Hot Water Math (what our tools use)</th><td>Our choice</td><td><?php echo $hwm_l( $hwm_t . 'low' ) . ' to ' . $hwm_l( $hwm_t . 'high' ) . ', typical ' . $hwm_l( $hwm_t . 'typical' ); // phpcs:ignore ?></td><td><?php echo 'about ' . $hwm_l( 'types.gas_tankless.typical' ); // phpcs:ignore ?></td></tr>
		</tbody>
	</table>
</div>

<h2>How long do gas and electric water heaters last?</h2>
<p>DOE gives gas and electric storage tanks the same expected life, <?php echo $hwm_l( 'types.gas_tank.typical' ); // phpcs:ignore ?> years for both, because what usually ends a tank is the same: the steel shell corrodes and leaks. The fuel changes what wears out along the way, not when the tank itself gives out.</p>
<p>A gas tank has a burner under the tank and a flue through its middle, so sediment on the tank floor sits right over the flame and makes it rumble. An electric tank heats with elements inside the water, and scale collects on them instead. Parts like elements, thermostats and gas controls can be replaced; a leaking tank cannot, which is why the tank sets the life in DOE's data for both fuels.</p>

<h2>How long does a tankless water heater last?</h2>
<p>About <?php echo $hwm_l( 'types.gas_tankless.typical' ); // phpcs:ignore ?> years on average, by DOE's estimate, and <?php echo esc_html( $hwm_ma['tanklessLow'] . ' to ' . $hwm_ma['tanklessHigh'] ); ?> years or more by Rheem's and A. O. Smith's. A tankless unit lasts longer because there is no tank of stored water to corrode. Its weak point is the heat exchanger: A. O. Smith's tankless care advice warns that <?php echo esc_html( lcfirst( rtrim( (string) $hwm_ma['hardWaterNote'], '.' ) ) ); ?>.</p>
<p>That is why A. O. Smith recommends descaling a tankless unit once a year where the water is hard. The warning signs it lists are different from a tank's: noises, temperature swings, reduced flow and an error code that keeps coming back.</p>

<h2>How long does a heat pump water heater last?</h2>
<p>DOE's Federal Energy Management Program assumes a product life of <?php echo $hwm_l( 'types.heat_pump.typical' ); // phpcs:ignore ?> years for heat pump water heaters, the same as for electric storage tanks, and ENERGY STAR uses the same figure (<?php echo $hwm_ext( $hwm_fmp, 'DOE FEMP' ); // phpcs:ignore ?>). The tank corrodes like any other, so the same care applies: a yearly flush and an anode check. The heat pump on top adds a compressor and a fan; follow the owner's manual for their care.</p>

<h2>What shortens a water heater's life?</h2>
<p>Hard water, skipped maintenance and a spent anode rod shorten a tank's life the most. All three speed up corrosion or scale, and all three are things you can check.</p>
<ul>
	<li><strong>Hard water.</strong> The U.S. Geological Survey calls water "hard" from <?php echo esc_html( (string) ( $hwm_hs['hard']['mgPerL'] ?? '' ) ); ?> mg/L of calcium carbonate and "very hard" above that (<?php echo $hwm_ext( (string) ( $hwm_hs['mapUrl'] ?? '' ), 'USGS hardness map' ); // phpcs:ignore ?>). Minerals settle as sediment in a tank and as scale in a tankless exchanger.</li>
	<li><strong>No flushing.</strong> Rheem advises flushing the tank once a year to clear that sediment.</li>
	<li><strong>The anode rod.</strong> The rod corrodes so the steel tank does not. Rheem advises replacing it every <?php echo esc_html( $hwm_mr['anodeEveryYearsLow'] . ' to ' . $hwm_mr['anodeEveryYearsHigh'] ); ?> years; once it is gone, the tank starts to rust from the inside.</li>
	<li><strong>High water pressure.</strong> Rheem suggests <?php echo esc_html( $hwm_mr['pressurePsiLow'] . ' to ' . $hwm_mr['pressurePsiHigh'] ); ?> psi; A. O. Smith notes that high pressure stresses the system and that an undersized unit wears out faster.</li>
	<li><strong>Water and gas quality.</strong> Bradford White names harsh water and natural gas quality as things that shorten a heater's life (<?php echo $hwm_ext( 'https://www.bradfordwhite.com/my-water-heater/', 'Bradford White' ); // phpcs:ignore ?>).</li>
</ul>
<p>Our age calculator turns these into a number. It takes <?php echo esc_html( ltrim( (string) ( $hwm_adj['hardness']['veryHard'] ?? '' ), '-' ) ); ?> years off for very hard water and <?php echo esc_html( ltrim( (string) ( $hwm_adj['noAnnualFlush'] ?? '' ), '-' ) ); ?> for no yearly flush, and adds <?php echo esc_html( (string) ( $hwm_adj['anodeReplaced'] ?? '' ) ); ?> for a replaced anode. Those are our editorial assumptions, not measurements. With them, a tank on very hard water that is never flushed has a typical life of <?php echo $hwm_v( 'worstCase.typical' ); // phpcs:ignore ?> years, and one with a fresh anode on moderate water <?php echo $hwm_v( 'bestCase.typical' ); // phpcs:ignore ?>.</p>

<h2>Check how old your own water heater is</h2>
<p>The build date is printed in code in the serial number on the rating plate. Pick the brand, type the serial, and the calculator shows the age, how much of its expected life is used and roughly how long it has left.</p>
<?php
ob_start();
hwm_part( 'age-inputs', array( 'file' => 'water-heater-age-calculator' ) );
hwm_part( 'calculator', array( 'tool' => 'water-heater-age-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Water heater age calculator' ) );
?>
<p>For a brand-by-brand guide to the codes, with every era and worked examples, see the pages for <?php echo hwm_link( 'bradford-white-water-heater-age', 'Bradford White' ); // phpcs:ignore ?>, <?php echo hwm_link( 'ao-smith-water-heater-age', 'A. O. Smith' ); // phpcs:ignore ?>, <?php echo hwm_link( 'rheem-water-heater-age', 'Rheem and Ruud' ); // phpcs:ignore ?> and <?php echo hwm_link( 'state-water-heater-age', 'State' ); // phpcs:ignore ?>, or the full <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?>.</p>

<h2>How old is too old?</h2>
<p>A tank that has used <?php echo esc_html( (string) hwm_value( 'computed/water-heater-age-calculator', 'bandsPct.amberFrom' ) ); ?>% of its typical life is worth planning for, and one past <?php echo esc_html( (string) hwm_value( 'computed/water-heater-age-calculator', 'bandsPct.redFrom' ) ); ?>% can fail at any time. The table shows where a unit of each age falls, before any adjustment for water or maintenance.</p>
<?php echo hwm_table( $hwm_c, 'ageShare' ); // phpcs:ignore -- escaped in the part. ?>
<p>Age alone does not mean you must replace a working water heater. It means a leak is more likely each year, and replacing on your schedule costs less than an emergency swap with water on the floor.</p>

<?php hwm_part( 'doe-2029', array( 'heading' => false, 'lead' => 'If your old tank is electric, plan its replacement with the coming standard in mind.' ) ); ?>

<h2>Signs your water heater is near the end</h2>
<p>The clearest sign is water leaking from the tank itself, which cannot be repaired. Rheem lists <?php echo esc_html( implode( '; ', (array) $hwm_mr['signs'] ) ); ?>. A. O. Smith adds <?php echo esc_html( implode( '; ', array_slice( (array) $hwm_ma['signs'], 2 ) ) ); ?>, for tankless units in particular.</p>
<ul>
	<li><strong>Check where the water comes from.</strong> A drip from a fitting, the drain valve or the relief valve's pipe is often fixable; a wet tank bottom usually is not.</li>
	<li><strong>Check both taps for rust.</strong> If only the hot side runs rusty, suspect the tank. If both do, the pipes may be the cause.</li>
	<li><strong>Listen after a reheat.</strong> Popping or rumbling means sediment. A flush may quiet it, but on an old tank it often means the bottom is badly scaled.</li>
</ul>
<p>Rheem's rule of thumb: if a repair costs more than <?php echo esc_html( (string) $hwm_mr['repairShareOfNewPct'] ); ?>% of a new unit, replacement is almost always the better buy. Our guide on whether to <?php echo hwm_link( 'repair-or-replace-water-heater', 'repair or replace a water heater' ); // phpcs:ignore ?> works through that choice with your numbers.</p>

<h2>Does the warranty tell you how long it will last?</h2>
<p>No. A warranty is how long the maker will pay for a covered failure, not a forecast of when the unit will fail. Bradford White, for example, covers the tank and parts for leakage or malfunction and shows each unit's warranty length when you enter the serial; the length varies by model (<?php echo $hwm_ext( $hwm_bwf, 'Bradford White FAQ' ); // phpcs:ignore ?>).</p>
<p>The life you get still depends on your water and your maintenance. Use the warranty to know who pays if it fails early, and the age to plan ahead.</p>

<h2>When is replacing a water heater a job for a licensed professional?</h2>
<p>Checking the age, flushing a tank as the manual describes and looking for leaks are homeowner jobs. Replacing a water heater usually is not: gas lines, venting, electrical connections and the relief valve are covered by code, and your area may require a permit. If you smell gas, leave the house and call your gas utility from outside.</p>
<?php
$faq = array(
	array(
		'q' => 'How long does a tankless water heater last?',
		'a' => 'About ' . hwm_value( $hwm_ls, 'types.gas_tankless.typical' ) . ' years on average, according to DOE, and ' . $hwm_ma['tanklessLow'] . ' to ' . $hwm_ma['tanklessHigh'] . ' years or more according to Rheem and A. O. Smith. There is no storage tank to corrode, so the heat exchanger sets the limit. Descaling it where the water is hard is the main thing that keeps it going.',
	),
	array(
		'q' => 'Do electric water heaters last longer than gas?',
		'a' => 'Not on average. DOE uses the same ' . hwm_value( $hwm_ls, 'types.gas_tank.typical' ) . '-year typical life for gas and electric storage tanks, because both usually end when the steel tank corrodes. Burners, elements and thermostats can be replaced; the tank cannot, so it sets the life for both.',
	),
	array(
		'q' => 'Can a water heater last 20 years?',
		'a' => 'A tankless unit often can: DOE\'s average for tankless models is about ' . hwm_value( $hwm_ls, 'types.gas_tankless.typical' ) . ' years. A storage tank that reaches 20 is well past the usual ' . hwm_value( $hwm_ls, $hwm_t . 'low' ) . ' to ' . hwm_value( $hwm_ls, $hwm_t . 'high' ) . ' years. It happens with soft water and good maintenance, but each extra year carries a higher risk of a leak.',
	),
	array(
		'q' => 'Does a longer warranty mean the water heater will last longer?',
		'a' => 'Not directly. A warranty sets how long the maker pays for a covered failure. Water hardness and maintenance decide most of the actual life. Check your unit\'s warranty by serial on the maker\'s site.',
	),
	array(
		'q' => 'How long does a heat pump water heater last?',
		'a' => 'DOE\'s Federal Energy Management Program and ENERGY STAR assume ' . hwm_value( $hwm_ls, 'types.heat_pump.typical' ) . ' years, the same as an electric storage tank. The tank corrodes like any other, so the same yearly flush and anode checks apply.',
	),
	array(
		'q' => 'Should I replace a 10-year-old water heater that still works?',
		'a' => 'Not automatically. At ' . hwm_value( $hwm_ls, $hwm_t . 'low' ) . ' years a tank is at the start of its usual failure range, so check for moisture at the base, rusty hot water and noise, and price a replacement now. Replacing before a leak avoids water damage and a rushed choice.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Water Heaters, final rule (89 FR, May 6, 2024), section IV.F.6 (product lifetime)', 'url' => $hwm_doe, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Energy Conservation Standards for Consumer Gas-fired Instantaneous Water Heaters, final rule (89 FR, December 26, 2024)', 'url' => $hwm_tkl, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy, FEMP', 'title' => 'Purchasing energy-efficient residential water heaters (assumed product life)', 'url' => $hwm_fmp, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Water heater lifespan: when to repair vs. replace (March 25, 2026)', 'url' => (string) $hwm_mr['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'How long do water heaters last?', 'url' => (string) $hwm_ma['sourceUrl'], 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Bradford White', 'title' => 'Frequently asked questions (lifespan, warranty)', 'url' => $hwm_bwf, 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Geological Survey', 'title' => 'Hardness of water', 'url' => (string) ( $hwm_hs['sourceUrl'] ?? '' ), 'accessed' => '2026-11-13' ),
);
