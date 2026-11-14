<?php
/**
 * /tankless-vs-tank-water-heater/ (STRATEGY §3.6): cost of ownership of a tank, a tankless unit and a heat pump.
 * Every number is read from data/*.json or data/computed/tankless-vs-tank-water-heater.json (npm run build:data).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/tankless-vs-tank-water-heater';
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_m   = (array) ( $hwm_cl['model'] ?? array() );
$hwm_def = (array) ( $hwm_cl['defaults'] ?? array() );
$hwm_f   = (array) hwm_raw( $hwm_c, 'figures' );
$hwm_g   = (array) ( $hwm_f['gas'] ?? array() );
$hwm_e   = (array) ( $hwm_f['electric'] ?? array() );
$hwm_g20 = (array) ( $hwm_f['gas20'] ?? array() );
$hwm_x   = static fn( array $set, string $type, string $key ): string => esc_html( (string) ( $set[ $type ][ $key ] ?? '' ) );
$hwm_fv  = static fn( string $key ): string => esc_html( (string) ( $hwm_f[ $key ] ?? '' ) );
$hwm_be  = static fn( $year ): string => $year ? 'year ' . (int) $year : 'not within the period';
$hwm_yrs = (string) ( $hwm_f['years'] ?? '' );
$hwm_ppl = (array) ( $hwm_f['people'] ?? array() );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="tt-fuel">Your fuel</label>
		<select id="tt-fuel" name="fuel">
			<option value="gas"<?php selected( 'gas', (string) ( $hwm_def['fuel'] ?? '' ) ); ?>>Natural gas</option>
			<option value="propane">Propane</option>
			<option value="electric"<?php selected( 'electric', (string) ( $hwm_def['fuel'] ?? '' ) ); ?>>Electric</option>
		</select>
	</div>
	<div class="field">
		<label for="tt-household">Household</label>
		<select id="tt-household" name="household">
			<option value="typical">Typical (<?php echo esc_html( (string) ( $hwm_m['typicalGallons'] ?? '' ) ); ?> gal a day)</option>
			<?php for ( $hwm_i = 1; $hwm_i <= 6; $hwm_i++ ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_i ); ?>"><?php echo esc_html( $hwm_i . ( 1 === $hwm_i ? ' person' : ' people' ) ); ?></option>
			<?php endfor; ?>
		</select>
	</div>
	<div class="field">
		<label for="tt-state">Your state</label>
		<select id="tt-state" name="state">
			<?php foreach ( (array) hwm_raw( $hwm_c, 'stateOptions' ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"><?php echo esc_html( (string) $hwm_s['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="tt-years">Years you expect to stay</label>
		<input id="tt-years" name="years" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( $hwm_yrs ); ?>" autocomplete="off">
	</div>
</div>
<details class="disclosure">
	<summary>Energy prices (assumed; change if you know yours)</summary>
	<div class="field-grid">
		<div class="field">
			<label for="tt-kwh">Electricity ($ per kWh) <span class="hint">EIA average for the state picked.</span></label>
			<input id="tt-kwh" name="kwh" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['us']['usdPerKwh'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="tt-therm">Natural gas ($ per therm) <span class="hint">EIA average for the state picked.</span></label>
			<input id="tt-therm" name="therm" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['us']['usdPerTherm'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="tt-propane">Propane ($ per gallon) <span class="hint">EIA U.S. residential average, <?php echo esc_html( (string) ( $hwm_m['propane']['period'] ?? '' ) ); ?>, before taxes.</span></label>
			<input id="tt-propane" name="propane" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['propane']['usdPerGallon'] ?? '' ) ); ?>" autocomplete="off">
		</div>
	</div>
</details>
<?php
echo hwm_tool_data_script( 'hwm-tco-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'tankless-water-heater-size-calculator', 'text' => 'Tankless water heater size calculator', 'why' => 'how big a tankless unit your home needs' ),
	array( 'slug' => 'water-heater-cost-calculator', 'text' => 'Water heater cost calculator', 'why' => 'itemize the installed price for your job' ),
	array( 'slug' => 'gas-vs-electric-water-heater', 'text' => 'Gas vs electric running cost', 'why' => 'yearly cost of all five types' ),
);
$related = array( 'tankless-water-heater-size-calculator', 'water-heater-size-calculator', 'water-heater-cost-calculator', 'gas-vs-electric-water-heater' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Installed prices come from the same model as our cost calculator, yearly running costs from the same formula as our gas vs electric tool, and lifespans from the U.S. Department of Energy. We compare the running costs and the heat pump payback with DOE's 2024 rules on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#tankless-vs-tank-water-heater' ); ?>">our methodology page</a>.</p>

<h2>Is a tankless water heater worth it?</h2>
<p>For a typical household on natural gas, a tankless unit does not pay back its higher price within <?php echo esc_html( $hwm_yrs ); ?> years at U.S. average prices. It saves about $<?php echo $hwm_fv( 'gasTanklessSaving' ); // phpcs:ignore ?> a year to run, but switching costs about $<?php echo $hwm_x( $hwm_g, 'gas_tankless', 'installed' ); // phpcs:ignore ?> installed, so the simple payback is about <?php echo $hwm_x( $hwm_g, 'gas_tankless', 'payback' ); // phpcs:ignore ?> years. It pays when hot water use is high, you stay long, or you are replacing a tankless unit anyway.</p>
<p>Its longer life still counts. After <?php echo esc_html( $hwm_yrs ); ?> years a tankless unit has about $<?php echo $hwm_x( $hwm_g, 'gas_tankless', 'residual' ); // phpcs:ignore ?> of its life left and the tank about $<?php echo $hwm_x( $hwm_g, 'gas_tank', 'residual' ); // phpcs:ignore ?>. Subtracting that, the <?php echo esc_html( $hwm_yrs ); ?>-year cost is about $<?php echo $hwm_x( $hwm_g, 'gas_tankless', 'total' ); // phpcs:ignore ?> for tankless against $<?php echo $hwm_x( $hwm_g, 'gas_tank', 'total' ); // phpcs:ignore ?> for a 50-gallon gas tank, <?php echo ! empty( $hwm_f['gasTanklessAhead'] ) ? 'so tankless comes out about $' . esc_html( (string) $hwm_f['gasTanklessAheadBy'] ) . ' ahead on paper' : 'so the tank stays cheaper'; ?>. In cash actually spent by then, tankless is still behind: $<?php echo $hwm_x( $hwm_g, 'gas_tankless', 'spent' ); // phpcs:ignore ?> against $<?php echo $hwm_x( $hwm_g, 'gas_tank', 'spent' ); // phpcs:ignore ?>.</p>
<p>The picture changes with the time you stay. Over <?php echo $hwm_fv( 'years20' ); // phpcs:ignore ?> years the tank needs <?php echo esc_html( (string) ( $hwm_g20['gas_tank']['repl'] ?? '' ) ); ?> replacement, and the totals come to about $<?php echo $hwm_x( $hwm_g20, 'gas_tank', 'total' ); // phpcs:ignore ?> for the tank and $<?php echo $hwm_x( $hwm_g20, 'gas_tankless', 'total' ); // phpcs:ignore ?> for tankless. DOE estimates about <?php echo $hwm_fv( 'lifeTank' ); // phpcs:ignore ?> years of life for a tank and <?php echo $hwm_fv( 'lifeTankless' ); // phpcs:ignore ?> for a gas tankless unit.</p>
<dl>
	<dt>Cost of ownership</dt>
	<dd>Everything you pay over the years you own it (the installed price, the energy and upkeep to run it, and any replacement within that time, minus any tax credit), less the value of the life still left in the heater at the end.</dd>
	<dt>Simple payback</dt>
	<dd>The extra up-front cost divided by the yearly saving, with no interest. It does not count that a longer-lasting unit is replaced less often.</dd>
</dl>

<h2>Tankless vs tank: the cost over <?php echo esc_html( $hwm_yrs ); ?> years</h2>
<p>At U.S. average prices and a typical day of hot water, these are the totals for the two fuels. On gas, <?php echo ! empty( $hwm_f['gasTanklessAhead'] ) ? 'the totals are close and tankless leads only because of its longer life left, while its simple payback is longer than ' . esc_html( $hwm_yrs ) . ' years' : 'the 50-gallon tank stays the cheapest choice'; ?>; on electricity, a heat pump water heater pays back its higher price in <?php echo esc_html( $hwm_be( $hwm_e['heat_pump']['be'] ?? null ) ); ?> and an electric tankless unit never does.</p>
<div class="table-wrap" role="region" aria-label="Cost of ownership compared" tabindex="0">
	<table>
		<caption><?php echo esc_html( $hwm_yrs ); ?>-year cost of ownership, typical household, U.S. average prices</caption>
		<thead><tr><th scope="col">Option</th><th scope="col" class="num">Installed</th><th scope="col" class="num">Running cost a year</th><th scope="col" class="num"><?php echo esc_html( $hwm_yrs ); ?>-year total</th><th scope="col">Pays back</th></tr></thead>
		<tbody>
			<?php
			$hwm_rows = array(
				array( 'Gas tank, 50 gal', $hwm_g, 'gas_tank' ),
				array( 'Gas tankless (switching from a tank)', $hwm_g, 'gas_tankless' ),
				array( 'Heat pump, 50 gal (switching from gas)', $hwm_g, 'heat_pump' ),
				array( 'Electric tank, 50 gal', $hwm_e, 'electric_tank' ),
				array( 'Electric tankless (switching from a tank)', $hwm_e, 'electric_tankless' ),
				array( 'Heat pump, 50 gal (replacing an electric tank)', $hwm_e, 'heat_pump' ),
			);
			foreach ( $hwm_rows as $hwm_r ) :
				$hwm_o = (array) ( $hwm_r[1][ $hwm_r[2] ] ?? array() );
				$hwm_base = in_array( $hwm_r[2], array( 'gas_tank', 'electric_tank' ), true );
				?>
				<tr><th scope="row"><?php echo esc_html( $hwm_r[0] ); ?></th><td class="num">$<?php echo esc_html( (string) ( $hwm_o['installed'] ?? '' ) ); ?></td><td class="num">$<?php echo esc_html( (string) ( $hwm_o['year'] ?? '' ) ); ?></td><td class="num">$<?php echo esc_html( (string) ( $hwm_o['total'] ?? '' ) ); ?></td><td><?php echo esc_html( $hwm_base ? 'Baseline' : ( ! empty( $hwm_o['be'] ) ? 'Year ' . (int) $hwm_o['be'] : 'Not within ' . $hwm_yrs . ' years' ) ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Computed with the calculator's model: equipment listings and our installation ranges, EIA prices, DOE typical day and lifespans. Running cost includes yearly upkeep ($<?php echo $hwm_fv( 'maintTankless' ); // phpcs:ignore ?> descaling for tankless, $<?php echo $hwm_fv( 'maintTank' ); // phpcs:ignore ?> for a tank's anode rod). Totals subtract the value of the life left at the end; "Pays back" is the simple payback year. Federal tax credit $0 (see below).</p>
</div>
<p>The tankless price includes the new venting and larger gas line most tank-to-tankless jobs need. If your home already has them, for example because you are replacing an old tankless unit, the extra cost is far smaller; price your own job in the <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?>.</p>

<h2>Does household size change the answer?</h2>
<p>Yes. A tankless unit saves a share of the energy, so the more hot water you use, the more it saves each year. For a gas home at U.S. average prices, tankless pays back in <?php echo esc_html( strtolower( (string) ( $hwm_ppl[2]['be'] ?? '' ) ) ); ?> for a <?php echo esc_html( (string) ( $hwm_ppl[2]['people'] ?? '' ) ); ?>-person household; for <?php echo esc_html( (string) ( $hwm_ppl[0]['people'] ?? '' ) ); ?> people the answer is "<?php echo esc_html( strtolower( (string) ( $hwm_ppl[0]['be'] ?? '' ) ) ); ?>".</p>
<div class="table-wrap" role="region" aria-label="Tankless vs tank by household size" tabindex="0">
	<table>
		<caption>Gas tank vs gas tankless, <?php echo esc_html( $hwm_yrs ); ?>-year total by household size</caption>
		<thead><tr><th scope="col" class="num">People</th><th scope="col" class="num">Gas tank</th><th scope="col" class="num">Gas tankless</th><th scope="col">Tankless pays back</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_ppl as $hwm_p ) : ?>
				<tr><th scope="row" class="num"><?php echo esc_html( (string) $hwm_p['people'] ); ?></th><td class="num">$<?php echo esc_html( number_format( (float) $hwm_p['tank'] ) ); ?></td><td class="num">$<?php echo esc_html( number_format( (float) $hwm_p['tankless'] ) ); ?></td><td><?php echo esc_html( (string) $hwm_p['be'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Hot water use scaled at <?php echo $hwm_fv( 'perPerson' ); // phpcs:ignore ?> gallons per person a day (DOE Buildings Energy Data Book). Larger households may also need a larger tank, which this table does not add.</p>
</div>

<h2>Where does tankless pay back? State by state</h2>
<p>Gas tankless paid back within <?php echo esc_html( $hwm_yrs ); ?> years in <?php echo $hwm_fv( 'gasTanklessStatesPay' ); // phpcs:ignore ?> of <?php echo $hwm_fv( 'stateCount' ); // phpcs:ignore ?> states and DC for a typical household. Cheap natural gas keeps the yearly saving small almost everywhere. Heat pumps replacing an electric tank pay back much faster, because electricity is the costlier fuel.</p>
<details>
	<summary>Payback year in all 50 states and DC</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>

<h2>Tankless water heater pros and cons</h2>
<div class="table-wrap" role="region" aria-label="Tankless vs tank pros and cons" tabindex="0">
	<table>
		<caption>Tankless vs tank water heaters compared</caption>
		<thead><tr><th scope="col">Factor</th><th scope="col">Tankless</th><th scope="col">Tank</th></tr></thead>
		<tbody>
			<tr><th scope="row">Upfront cost</th><td>Higher, especially when venting and gas line must change</td><td>Lower; usually a like-for-like swap</td></tr>
			<tr><th scope="row">Running cost</th><td>Lower: no tank to keep hot</td><td>Higher: standby heat loss</td></tr>
			<tr><th scope="row">Lifespan (DOE)</th><td>About <?php echo $hwm_fv( 'lifeTankless' ); // phpcs:ignore ?> years (gas)</td><td>About <?php echo $hwm_fv( 'lifeTank' ); // phpcs:ignore ?> years</td></tr>
			<tr><th scope="row">Hot water supply</th><td>Endless, but limited in flow: too many showers at once drop the temperature</td><td>A full tank at once, then a wait while it recovers</td></tr>
			<tr><th scope="row">Space</th><td>Wall-mounted, small</td><td>Needs floor space for the tank</td></tr>
			<tr><th scope="row">Maintenance</th><td>Descaling in hard water, per the manual</td><td>Flushing sediment, anode checks</td></tr>
			<tr><th scope="row">Power outage</th><td>Needs electricity for controls and fan</td><td>Some gas tanks with a standing pilot keep working</td></tr>
		</tbody>
	</table>
</div>
<p>The flow limit matters most in winter, when the incoming water is coldest. The <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless size calculator' ); // phpcs:ignore ?> checks that a unit can run your showers at once in your state's coldest month.</p>

<h2>Hybrid (heat pump) vs tankless: which saves more?</h2>
<p>A heat pump water heater has the lowest running cost of any type we compare, about $<?php echo $hwm_x( $hwm_g, 'heat_pump', 'year' ); // phpcs:ignore ?> a year against $<?php echo $hwm_x( $hwm_g, 'gas_tankless', 'year' ); // phpcs:ignore ?> for gas tankless at U.S. average prices. In an electric home it is usually the best value: it pays back against an electric tank in <?php echo esc_html( $hwm_be( $hwm_e['heat_pump']['be'] ?? null ) ); ?>. In a gas home, the 240-volt circuit it needs adds to the switch cost.</p>
<p>A heat pump also needs room: it draws heat from the air around it and cools that space, so a small closet may need a louvered door or ducting. DOE counted that kind of work at up to several thousand dollars for the hardest installs.</p>

<h2>Are there tax credits or rebates?</h2>
<p>The federal Energy Efficient Home Improvement Credit (25C) used to cover <?php echo esc_html( hwm_value( 'incentives', 'credits.0.percent' ) ); ?>% of a qualifying heat pump water heater, up to $<?php echo esc_html( hwm_value( 'incentives', 'credits.0.maxUsd', 0 ) ); ?>, and <?php echo esc_html( hwm_value( 'incentives', 'credits.1.percent' ) ); ?>% of a qualifying gas unit, up to $<?php echo esc_html( hwm_value( 'incentives', 'credits.1.maxUsd', 0 ) ); ?>. The IRS says it applies only to property placed in service through <?php echo esc_html( hwm_date( hwm_value( 'incentives', 'credits.0.placedInServiceThrough' ) ) ); ?>, so the calculator uses $0. We checked this on <?php echo esc_html( hwm_date( (string) hwm_raw( 'incentives', 'meta.sourceDate' ) ) ); ?> and review it again by <?php echo esc_html( hwm_date( (string) hwm_raw( 'incentives', 'nextReview' ) ) ); ?>.</p>
<p>Utility and state rebates still exist in many places, especially for heat pump water heaters. Look yours up in the <?php echo '<a href="' . esc_url( (string) hwm_raw( 'incentives', 'rebateFinder.sourceUrl' ) ) . '" rel="noopener">ENERGY STAR Rebate Finder</a>'; // phpcs:ignore ?> and subtract it from the installed price.</p>

<h2>Common mistakes in the tankless decision</h2>
<ul>
	<li><strong>Comparing unit prices only.</strong> The venting and gas line are the big part of a tankless switch.</li>
	<li><strong>Counting savings for too many years.</strong> If you move in five years, the extra cost may never come back.</li>
	<li><strong>Undersizing.</strong> A unit that is too small in winter gives lukewarm showers; size it for your coldest month.</li>
	<li><strong>Ignoring the heat pump.</strong> In an electric home it usually beats tankless on total cost.</li>
</ul>

<h2>When is this a job for a licensed professional?</h2>
<p>Tankless units need correct gas pipe sizing, venting and, for electric models, large circuits. Heat pump units need a condensate drain and enough air. Have a licensed plumber or electrician check your home before you choose.</p>

<h2>Limitations of this estimate</h2>
<p>The comparison ignores inflation, energy price changes and the time value of money. Running costs come from efficiency ratings, which leave out the electricity a gas tankless unit's fan uses and real-world short draws. Lifespans are national averages, a replacement inside the period is priced at the same installed cost as the first unit, and the value left at the end is counted on a straight line over the heater's life. A tank's size is fixed at 50 gallons here; a larger household may need a bigger tank, which costs more.</p>
<?php
$faq = array(
	array(
		'q' => 'Is a tankless water heater worth it?',
		'a' => 'Often not on cost alone. For a typical gas household at U.S. average prices, switching to tankless does not pay back within ' . $hwm_yrs . ' years. It can pay for large households, long stays, homes that already have tankless venting, or when space matters more than cost.',
	),
	array(
		'q' => 'Do tankless water heaters last longer?',
		'a' => 'Yes. DOE estimates about ' . ( $hwm_f['lifeTankless'] ?? '' ) . ' years for gas tankless units and about ' . ( $hwm_f['lifeTank'] ?? '' ) . ' years for tank water heaters. Over ' . ( $hwm_f['years20'] ?? '' ) . ' years a tank usually needs one replacement, which narrows the cost gap.',
	),
	array(
		'q' => 'What is the cost difference between tankless and tank?',
		'a' => 'At U.S. average prices, a 50-gallon gas tank costs about $' . ( $hwm_g['gas_tank']['installed'] ?? '' ) . ' installed and a gas tankless unit about $' . ( $hwm_g['gas_tankless']['installed'] ?? '' ) . ' when it replaces a tank, including new venting and a larger gas line. Tankless then costs less to run each year.',
	),
	array(
		'q' => 'Hybrid water heater vs tankless: which is better?',
		'a' => 'On running cost, the hybrid (heat pump) wins: about $' . ( $hwm_g['heat_pump']['year'] ?? '' ) . ' a year against $' . ( $hwm_g['gas_tankless']['year'] ?? '' ) . ' for gas tankless at U.S. average prices. In an electric home a heat pump is usually the better buy; in a gas home the switch costs more.',
	),
	array(
		'q' => 'How long does it take for a tankless water heater to pay for itself?',
		'a' => 'For a typical gas household at U.S. average prices it does not within ' . $hwm_yrs . ' years; for a ' . ( $hwm_ppl[2]['people'] ?? '' ) . '-person household it pays back in about ' . strtolower( (string) ( $hwm_ppl[2]['be'] ?? '' ) ) . '. Use the calculator with your state, household and years.',
	),
	array(
		'q' => 'Can a tankless water heater run out of hot water?',
		'a' => 'Not in the way a tank does, but it has a flow limit. If too many fixtures run at once, or the incoming water is very cold, the water comes out cooler. Size the unit for your coldest month and busiest moment.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Consumer water heaters final rule (May 6, 2024): lifetimes, installed and operating costs', 'url' => (string) hwm_raw( 'installation-costs', 'references.doe-storage-2024.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Consumer gas-fired instantaneous water heaters final rule (December 26, 2024)', 'url' => (string) hwm_raw( 'installation-costs', 'references.doe-gas-tankless-2024.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Internal Revenue Service', 'title' => 'Energy Efficient Home Improvement Credit (25C)', 'url' => (string) hwm_raw( 'incentives', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'incentives', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Average residential electricity and natural gas prices', 'url' => (string) hwm_raw( 'energy-prices', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'Rebate Finder', 'url' => (string) hwm_raw( 'incentives', 'rebateFinder.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. DOE Federal Energy Management Program', 'title' => 'Purchasing energy-efficient residential water heaters (typical daily use)', 'url' => (string) hwm_raw( 'hot-water-use', 'typicalHouseholdGallonsPerDay.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
