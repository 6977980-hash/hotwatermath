<?php
/**
 * /heat-pump-water-heater-cost/ (STRATEGY §2 item 12, §3.8): installed cost, savings and payback, with the heat pump
 * savings calculator embedded. Every number comes from data/computed/heat-pump-water-heater-cost.json (npm run build:data)
 * or data/heat-pump.json / data/incentives.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/heat-pump-water-heater-cost';
$hwm_hp  = 'heat-pump';
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_h   = static fn( string $path ): string => esc_html( hwm_value( $hwm_hp, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_hp, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_m   = (array) ( $hwm_cl['model'] ?? array() );
$hwm_f   = (array) hwm_raw( $hwm_c, 'figures' );
$hwm_end = hwm_date( (string) ( $hwm_f['creditEnded'] ?? '' ) );

$related = array( 'gas-vs-electric-water-heater', 'tankless-vs-tank-water-heater', 'water-heater-cost-calculator', 'water-heater-size-calculator', 'electric-water-heater-installation-cost' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Unit prices are current retail listings, installation lines are anchored to the U.S. Department of Energy's 2024 water heater rules, and running costs use EIA electricity and gas prices with the same formula as our <?php echo hwm_link( 'gas-vs-electric-water-heater', 'gas vs electric calculator' ); // phpcs:ignore ?>. Every figure is checked against ENERGY STAR and DOE on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#heat-pump-water-heater-cost' ); ?>">our methodology page</a>.</p>

<h2>How much does a heat pump water heater cost?</h2>
<p>A 50-gallon heat pump water heater costs about $<?php echo $hwm_v( 'sizes.50.fromEl' ); // phpcs:ignore ?> installed when it replaces an electric tank, and about $<?php echo $hwm_v( 'sizes.50.fromGas' ); // phpcs:ignore ?> when it replaces a gas tank, at U.S. average prices. The unit alone lists for about $<?php echo $hwm_v( 'sizes.50.unit' ); // phpcs:ignore ?>. Replacing gas costs more because the installer has to run a new 240-volt circuit.</p>
<?php echo hwm_table( $hwm_c, 'bySize' ); // phpcs:ignore -- escaped in the part. ?>
<dl>
	<dt>Heat pump water heater (hybrid)</dt>
	<dd>A storage tank that heats water by moving heat out of the surrounding air with a small compressor, like a refrigerator in reverse. It keeps ordinary electric elements as a backup for busy hours, which is why it is also sold as a "hybrid electric" water heater.</dd>
	<dt>First hour rating (FHR)</dt>
	<dd>How many gallons of hot water the heater can deliver in one busy hour, starting full. It is on the yellow EnergyGuide label and is the number to size by.</dd>
</dl>
<p>Larger tanks cost more mainly because the unit costs more; the installation work is the same. Replacing an electric tank, an 80-gallon unit costs about $<?php echo $hwm_v( 'sizes.80.fromEl' ); // phpcs:ignore ?> installed against $<?php echo $hwm_v( 'sizes.50.fromEl' ); // phpcs:ignore ?> for 50 gallons.</p>

<h2>Heat pump savings calculator</h2>
<p>Pick the heater you have now, your state and your household. The calculator prices both jobs with the same model as our cost calculator, works out each heater's yearly energy bill, and tells you how long the extra cost takes to pay back.</p>
<?php
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="hp-current">The heater you have now</label>
		<select id="hp-current" name="current">
			<option value="electric_tank" selected>Electric tank</option>
			<option value="gas_tank">Gas tank</option>
		</select>
	</div>
	<div class="field">
		<label for="hp-size">Heat pump size</label>
		<select id="hp-size" name="size">
			<?php foreach ( (array) ( $hwm_m['types']['heat_pump']['sizes'] ?? array() ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"<?php selected( (string) $hwm_s['id'], (string) ( $hwm_cl['defaults']['size'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="hp-household">Household</label>
		<select id="hp-household" name="household">
			<option value="typical">Typical (<?php echo esc_html( (string) ( $hwm_m['typicalGallons'] ?? '' ) ); ?> gal a day)</option>
			<?php for ( $hwm_i = 1; $hwm_i <= 6; $hwm_i++ ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_i ); ?>"><?php echo esc_html( $hwm_i . ( 1 === $hwm_i ? ' person' : ' people' ) ); ?></option>
			<?php endfor; ?>
		</select>
	</div>
	<div class="field">
		<label for="hp-state">Your state</label>
		<select id="hp-state" name="state">
			<?php foreach ( (array) hwm_raw( $hwm_c, 'stateOptions' ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"><?php echo esc_html( (string) $hwm_s['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
<div class="field">
	<label for="hp-location">Where it will go <span class="hint">Heat pumps pull heat from the room air.</span></label>
	<select id="hp-location" name="location">
		<?php foreach ( (array) ( $hwm_cl['location']['options'] ?? array() ) as $hwm_o ) : ?>
			<option value="<?php echo esc_attr( (string) $hwm_o['id'] ); ?>"<?php selected( (string) $hwm_o['id'], (string) ( $hwm_cl['location']['default'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_o['label'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<fieldset>
	<legend>Your space</legend>
	<label class="check"><input type="checkbox" name="space" value="1"> The room is small or cold and needs a louvered door, ducting or a condensate pump (adds about $<?php echo $hwm_v( 'space' ); // phpcs:ignore ?>)</label>
</fieldset>
<details class="disclosure">
	<summary>Prices, efficiency and rebate (assumed; change if you know yours)</summary>
	<div class="field-grid">
		<div class="field">
			<label for="hp-kwh">Electricity ($ per kWh) <span class="hint">EIA average for the state picked.</span></label>
			<input id="hp-kwh" name="kwh" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['us']['usdPerKwh'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="hp-therm">Natural gas ($ per therm) <span class="hint">Used when you have a gas tank.</span></label>
			<input id="hp-therm" name="therm" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['us']['usdPerTherm'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="hp-uef">Heat pump UEF <span class="hint">Median of ENERGY STAR models; on the EnergyGuide label.</span></label>
			<input id="hp-uef" name="uef" type="text" inputmode="decimal" maxlength="4" value="<?php echo esc_attr( (string) ( $hwm_m['types']['heat_pump']['uef'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="hp-rebate">Rebates you will get ($) <span class="hint">Utility or state rebate; none assumed.</span></label>
			<input id="hp-rebate" name="rebate" type="text" inputmode="decimal" maxlength="6" value="<?php echo esc_attr( hwm_value( $hwm_hp, 'savingsCalculator.rebateDefaultUsd' ) ); ?>" autocomplete="off">
		</div>
	</div>
</details>
<?php
echo hwm_tool_data_script( 'hwm-hp-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
hwm_part( 'calculator', array( 'tool' => 'heat-pump-water-heater-cost', 'inputs' => (string) ob_get_clean(), 'label' => 'Heat pump savings calculator' ) );
?>
<p>Payback here is simple payback: the extra installed cost divided by the yearly saving, with today's prices and no interest. Green means it pays back within <?php echo $hwm_v( 'paybackGood' ); // phpcs:ignore ?> years (our threshold), amber within the heater's typical <?php echo $hwm_v( 'life' ); // phpcs:ignore ?>-year life, red after that.</p>

<h2>How much does a heat pump water heater save?</h2>
<p>Against an electric tank, a typical household saves about $<?php echo $hwm_v( 'e0.saving' ); // phpcs:ignore ?> a year at U.S. average electricity prices: the heat pump uses about <?php echo $hwm_v( 'hpKwh' ); // phpcs:ignore ?> kWh a year where the tank uses <?php echo $hwm_v( 'elKwh' ); // phpcs:ignore ?> kWh, <?php echo $hwm_v( 'lessPct' ); // phpcs:ignore ?>% less. Against a gas tank the saving is smaller, about $<?php echo $hwm_v( 'g0.saving' ); // phpcs:ignore ?> a year, because gas is a cheaper way to make heat than resistance electricity.</p>
<p>ENERGY STAR puts the saving for a household of <?php echo $hwm_h( 'energyStar.householdPeople' ); // phpcs:ignore ?> at about $<?php echo esc_html( number_format( (float) hwm_raw( $hwm_hp, 'energyStar.savingUsdPerYear' ) ) ); ?> a year compared with a standard electric heater. Our calculator gives $<?php echo $hwm_v( 'e4.saving' ); // phpcs:ignore ?> for <?php echo $hwm_h( 'energyStar.householdPeople' ); // phpcs:ignore ?> people because it uses more hot water per person than ENERGY STAR's unstated household; the methodology page shows the gap.</p>
<p>The saving grows with three things: how much hot water you use, how much electricity costs where you live, and how cold your incoming water is. That is why the same heater saves about $<?php echo $hwm_v( 'bestE.saving' ); // phpcs:ignore ?> a year in <?php echo $hwm_v( 'bestE.name' ); // phpcs:ignore ?> and about $<?php echo $hwm_v( 'worstE.saving' ); // phpcs:ignore ?> in <?php echo $hwm_v( 'worstE.name' ); // phpcs:ignore ?>.</p>

<h2>How long does a heat pump water heater take to pay back?</h2>
<p>Replacing an electric tank, about <?php echo $hwm_v( 'e0.payback' ); // phpcs:ignore ?> years at U.S. average prices: the heat pump job costs about $<?php echo $hwm_v( 'e0.extra' ); // phpcs:ignore ?> more than a new electric tank and saves $<?php echo $hwm_v( 'e0.saving' ); // phpcs:ignore ?> a year. Replacing a gas tank, about <?php echo $hwm_v( 'g0.payback' ); // phpcs:ignore ?> years, which is longer than the heater's typical <?php echo $hwm_v( 'life' ); // phpcs:ignore ?>-year life.</p>
<div class="table-wrap" role="region" aria-label="Payback in four common cases" tabindex="0">
	<table>
		<caption>Payback in four common cases (50 gallons, typical household, U.S. average prices)</caption>
		<thead><tr><th scope="col">Case</th><th scope="col" class="num">Extra cost ($)</th><th scope="col" class="num">Saves a year ($)</th><th scope="col">Payback</th><th scope="col" class="num">Net over <?php echo $hwm_v( 'life' ); // phpcs:ignore ?> years ($)</th></tr></thead>
		<tbody>
			<?php
			foreach ( array(
				'e0'     => 'Replacing an electric tank',
				'eSpace' => 'Electric tank, room needs space work',
				'g0'     => 'Replacing a gas tank (new circuit)',
				'gSpace' => 'Gas tank, room needs space work',
			) as $hwm_k => $hwm_label ) :
				$hwm_x = (array) ( $hwm_f[ $hwm_k ] ?? array() );
				?>
				<tr><th scope="row"><?php echo esc_html( $hwm_label ); ?></th><td class="num"><?php echo esc_html( (string) $hwm_x['extra'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_x['saving'] ); ?></td><td><?php echo esc_html( 'never' === $hwm_x['payback'] ? 'Never' : $hwm_x['payback'] . ' years' ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_x['net'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Computed with the savings calculator above: installed costs from Home Depot listings and data/installation-costs.json, running costs from EIA prices. No rebate assumed.</p>
</div>
<p>Space work is the swing factor. DOE's 2024 rule counts up to $<?php echo $hwm_v( 'spaceHigh' ); // phpcs:ignore ?> of extra work for the hardest installs, averaging $<?php echo $hwm_v( 'space' ); // phpcs:ignore ?> (our range: $<?php echo $hwm_v( 'spaceLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'spaceHigh' ); // phpcs:ignore ?>), and on a gas home that one line can push payback past the life of the heater. If your heater sits in a roomy basement or garage, leave the box unticked.</p>

<h2>What does each line of the bill cover?</h2>
<p>In an electric-to-heat-pump job the unit is about $<?php echo $hwm_v( 'sizes.50.unit' ); // phpcs:ignore ?> of the $<?php echo $hwm_v( 'sizes.50.fromEl' ); // phpcs:ignore ?>. Installation labor is about $<?php echo $hwm_v( 'labor' ); // phpcs:ignore ?> (range $<?php echo $hwm_v( 'laborLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'laborHigh' ); // phpcs:ignore ?>), more than for a plain tank because the unit is taller and heavier and needs a condensate drain.</p>
<?php echo hwm_table( $hwm_c, 'itemsEl' ); // phpcs:ignore ?>
<?php echo hwm_table( $hwm_c, 'itemsGas' ); // phpcs:ignore ?>
<p>If your panel has no room for a new 240-volt breaker, a panel upgrade adds $<?php echo $hwm_v( 'panelLow' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'panelHigh' ); // phpcs:ignore ?> in DOE's estimate. ENERGY STAR notes that some makers sell 120-volt plug-in models built for gas replacements, which avoid the circuit but should be sized up <?php echo $hwm_h( 'electrical.plugInSizeUp' ); // phpcs:ignore ?>.</p>

<h2>Is there a heat pump water heater tax credit in 2026?</h2>
<p>No. The federal Energy Efficient Home Improvement Credit paid <?php echo $hwm_v( 'creditPct' ); // phpcs:ignore ?>% of the cost, up to $<?php echo $hwm_v( 'creditMax' ); // phpcs:ignore ?> a year, but the IRS says it covers only improvements made through <?php echo esc_html( $hwm_end ); ?>. A heater installed in 2026 does not qualify. Many cost guides still list it; check the IRS page before you count on it.</p>
<?php echo hwm_table( $hwm_c, 'incentives' ); // phpcs:ignore ?>
<p>The federal High-Efficiency Electric Home Rebate can pay up to $<?php echo $hwm_v( 'heehr' ); // phpcs:ignore ?> toward a heat pump water heater, but only for households under <?php echo $hwm_h( 'rebates.heehrHalfCostIncomePct' ); // phpcs:ignore ?>% of area median income, and only where a state has opened its program. DOE says the rebates are "available in select states"; your state energy office knows the status. Utility rebates are separate and can be stacked: put the amount in the calculator's rebate box to see the new payback. With a $<?php echo $hwm_v( 'heehr' ); // phpcs:ignore ?> rebate, the <?php echo $hwm_v( 'life' ); // phpcs:ignore ?>-year net saving in the electric-tank case rises from $<?php echo $hwm_v( 'e0.net' ); // phpcs:ignore ?> to $<?php echo $hwm_v( 'eReb.net' ); // phpcs:ignore ?>.</p>

<h2>Does a heat pump water heater fit your space?</h2>
<p>It needs air, warmth and a drain. ENERGY STAR's installation guide says most 240-volt models need at least <?php echo $hwm_h( 'space.minCubicFeet' ); // phpcs:ignore ?> cubic feet of air around them, a 10 by 10 foot room with a 7-foot ceiling, though some need only <?php echo $hwm_h( 'space.minCubicFeetSome' ); // phpcs:ignore ?>. A closet of at least <?php echo $hwm_h( 'space.closetCubicFeetLouvered' ); // phpcs:ignore ?> cubic feet works with a fully louvered door.</p>
<ul>
	<li><strong>Temperature:</strong> they work best in spaces that do not regularly drop below <?php echo $hwm_h( 'space.minSpaceTempF' ); // phpcs:ignore ?> °F, per the same guide. Below about <?php echo $hwm_h( 'energyStar.intakeColdF' ); // phpcs:ignore ?> °F intake air, the backup elements take over and savings drop.</li>
	<li><strong>Cooling:</strong> while running, the unit cools the room by <?php echo esc_html( number_format( (float) hwm_raw( $hwm_hp, 'energyStar.coolingBtuhLow' ) ) ); ?> to <?php echo esc_html( number_format( (float) hwm_raw( $hwm_hp, 'energyStar.coolingBtuhHigh' ) ) ); ?> Btu an hour. That is welcome in a hot garage and a small cost in a heated basement.</li>
	<li><strong>Condensate:</strong> it makes clean water that needs a drain or a condensate pump.</li>
	<li><strong>Noise:</strong> ENERGY STAR models run below <?php echo $hwm_h( 'sound.maxDba' ); // phpcs:ignore ?> dBA, about a background conversation; quieter ones are rated near <?php echo $hwm_h( 'sound.quietDba' ); // phpcs:ignore ?> dBA.</li>
	<li><strong>Power:</strong> a 240-volt model typically uses a <?php echo $hwm_h( 'electrical.breakerAmps' ); // phpcs:ignore ?>-amp breaker and <?php echo $hwm_h( 'electrical.wireGauge' ); // phpcs:ignore ?>-gauge wire, the same as a standard electric tank.</li>
</ul>

<h2>Heat pump vs gas vs electric: which costs less overall?</h2>
<p>Against an electric tank, the heat pump pays back within <?php echo $hwm_v( 'paybackGood' ); // phpcs:ignore ?> years in <?php echo $hwm_v( 'elFast' ); // phpcs:ignore ?> of the <?php echo $hwm_v( 'stateCount' ); // phpcs:ignore ?> places we price (the 50 states and DC). Against a gas tank it pays back within its life in only <?php echo $hwm_v( 'gasInLife' ); // phpcs:ignore ?> states, mostly where gas is expensive, such as <?php echo $hwm_v( 'bestG.name' ); // phpcs:ignore ?> (<?php echo $hwm_v( 'bestG.payback' ); // phpcs:ignore ?>).</p>
<details>
	<summary>Heat pump savings and payback in all 50 states and DC</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>
<p>For a side-by-side over 10 or 20 years including tankless units and replacements, use the <?php echo hwm_link( 'tankless-vs-tank-water-heater', 'tankless vs tank cost of ownership calculator' ); // phpcs:ignore ?>. To price the job with your own extras, use the <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?>.</p>

<h2>Common mistakes when pricing a heat pump water heater</h2>
<ul>
	<li><strong>Counting the 2025 tax credit.</strong> It ended on <?php echo esc_html( $hwm_end ); ?>.</li>
	<li><strong>Comparing it with a gas tank using electric-tank savings.</strong> The saving against gas is a fraction of the saving against electric.</li>
	<li><strong>Ignoring the room.</strong> A small closet with a solid door costs extra or hurts efficiency.</li>
	<li><strong>Sizing down.</strong> A heat pump recovers more slowly than a gas burner; ENERGY STAR advises sizing up when it replaces gas.</li>
</ul>

<h2>When is this a job for a licensed professional?</h2>
<p>Always for the electrical work: a new 240-volt circuit or panel change needs a licensed electrician and, in most places, a permit. The water, relief valve and condensate connections are plumbing work that most areas also permit and inspect. This page helps you budget and compare quotes; it is not an installation guide.</p>

<h2>Limitations of this estimate</h2>
<p>Unit prices are one retailer's listings on one day. Installation and space-work lines are our ranges anchored to DOE averages. Running costs assume today's prices stay flat, a <?php echo esc_html( (string) ( $hwm_m['setpointF'] ?? '' ) ); ?> °F setting and the heat pump running mostly in hybrid mode; a cold space or heavy use moves it onto its backup elements and lowers the saving. Rebates change often; the table above was checked on the date shown.</p>
<?php
$faq = array(
	array(
		'q' => 'How much does a heat pump water heater cost installed?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.sizes.50.fromEl' ) . ' for a 50-gallon unit replacing an electric tank and $' . hwm_value( $hwm_c, 'figures.sizes.50.fromGas' ) . ' replacing a gas tank, at U.S. average prices. An 80-gallon unit costs about $' . hwm_value( $hwm_c, 'figures.sizes.80.fromEl' ) . '. Tight spaces that need ducting or a louvered door add more.',
	),
	array(
		'q' => 'Is there a tax credit for heat pump water heaters in 2026?',
		'a' => 'No federal one. The IRS says the 25C credit covered improvements made through ' . $hwm_end . '. Income-qualified households may get up to $' . hwm_value( $hwm_c, 'figures.heehr' ) . ' from the federal HEEHR rebate where their state has opened it, and many utilities offer their own rebates.',
	),
	array(
		'q' => 'How much will a heat pump water heater save me?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.e0.saving' ) . ' a year against an electric tank and $' . hwm_value( $hwm_c, 'figures.g0.saving' ) . ' against a gas tank for a typical household at U.S. average prices. Bigger households and states with expensive electricity save more; the calculator above uses your state.',
	),
	array(
		'q' => 'Is a heat pump water heater worth it if I have gas?',
		'a' => 'Usually not on running cost alone. Replacing a gas tank pays back in about ' . hwm_value( $hwm_c, 'figures.g0.payback' ) . ' years at U.S. average prices, and within the heater\'s life in only ' . hwm_value( $hwm_c, 'figures.gasInLife' ) . ' states. A rebate, cheap electricity or solar panels can change that.',
	),
	array(
		'q' => 'What size heat pump water heater do I need?',
		'a' => 'Size by first hour rating, not tank volume. A typical 50-gallon heat pump delivers about ' . hwm_value( $hwm_c, 'figures.sizes.50.fhr' ) . ' gallons in the first hour and an 80-gallon one about ' . hwm_value( $hwm_c, 'figures.sizes.80.fhr' ) . '. ENERGY STAR advises going up a size when replacing gas.',
	),
	array(
		'q' => 'How much space does a heat pump water heater need?',
		'a' => 'ENERGY STAR says most 240-volt models need at least ' . hwm_value( $hwm_hp, 'space.minCubicFeet' ) . ' cubic feet of air, some only ' . hwm_value( $hwm_hp, 'space.minCubicFeetSome' ) . '. In a closet of at least ' . hwm_value( $hwm_hp, 'space.closetCubicFeetLouvered' ) . ' cubic feet a fully louvered door can do. The space should stay above ' . hwm_value( $hwm_hp, 'space.minSpaceTempF' ) . ' °F.',
	),
	array(
		'q' => 'How loud is a heat pump water heater?',
		'a' => 'ENERGY STAR certified models run below ' . hwm_value( $hwm_hp, 'sound.maxDba' ) . ' dBA, about the level of a background conversation, and some are rated near ' . hwm_value( $hwm_hp, 'sound.quietDba' ) . ' dBA, like a quiet dishwasher. The fan and compressor run only while the unit heats water.',
	),
);
$sources = array(
	array( 'publisher' => 'ENERGY STAR (U.S. EPA)', 'title' => 'Best Practices: Installing Heat Pump Water Heaters (2024)', 'url' => $hwm_r( 'es-install' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ENERGY STAR (U.S. EPA)', 'title' => 'Heat Pump Water Heater Guide: Best Practices and Technical Considerations (June 2024)', 'url' => $hwm_r( 'es-guide' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ENERGY STAR (U.S. EPA)', 'title' => 'Heat Pump Water Heaters: buying guidance', 'url' => $hwm_r( 'es-product' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Internal Revenue Service', 'title' => 'Energy Efficient Home Improvement Credit', 'url' => $hwm_r( 'irs-25c' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Code', 'title' => '42 U.S.C. 18795a: High-efficiency electric home rebate program', 'url' => $hwm_r( 'usc-heehr' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy', 'title' => 'Home Energy Rebates Program', 'url' => $hwm_r( 'doe-rebates' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): heat pump installed cost, space adders and payback', 'url' => (string) hwm_raw( 'installation-costs', 'references.doe-storage-2024.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Average residential electricity and natural gas prices by state', 'url' => (string) hwm_raw( 'energy-prices', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
);
