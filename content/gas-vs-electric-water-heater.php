<?php
/**
 * /gas-vs-electric-water-heater/ (STRATEGY §3.7): yearly operating cost of five water heater types.
 * Every number is read from data/*.json or data/computed/gas-vs-electric-water-heater.json (npm run build:data).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/gas-vs-electric-water-heater';
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_m   = (array) ( $hwm_cl['model'] ?? array() );
$hwm_f   = (array) hwm_raw( $hwm_c, 'figures' );
$hwm_us  = (array) ( $hwm_f['us'] ?? array() );
$hwm_t   = static fn( string $type, string $key ): string => esc_html( (string) ( $hwm_us[ $type ][ $key ] ?? '' ) );
$hwm_fv  = static fn( string $key ): string => esc_html( (string) ( $hwm_f[ $key ] ?? '' ) );
$hwm_ref = 'computed/reference-tables';
$hwm_k   = static fn( string $key, int $dec = 0 ): string => esc_html( hwm_value( $hwm_ref, 'physics.' . $key, $dec ) );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="ge-household">Household</label>
		<select id="ge-household" name="household">
			<option value="typical">Typical (<?php echo esc_html( (string) ( $hwm_m['typicalGallons'] ?? '' ) ); ?> gal a day)</option>
			<?php for ( $hwm_i = 1; $hwm_i <= 6; $hwm_i++ ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_i ); ?>"><?php echo esc_html( $hwm_i . ( 1 === $hwm_i ? ' person' : ' people' ) ); ?></option>
			<?php endfor; ?>
		</select>
	</div>
	<div class="field">
		<label for="ge-gasFuel">Gas you would use</label>
		<select id="ge-gasFuel" name="gasFuel">
			<option value="natural">Natural gas</option>
			<option value="propane">Propane</option>
		</select>
	</div>
	<div class="field">
		<label for="ge-state">Your state</label>
		<select id="ge-state" name="state">
			<?php foreach ( (array) hwm_raw( $hwm_c, 'stateOptions' ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"><?php echo esc_html( hwm_state_label( (string) $hwm_s['id'], (string) $hwm_s['name'] ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
<details class="disclosure">
	<summary>Prices, temperature and efficiency (assumed; change if you know yours)</summary>
	<div class="field-grid">
		<div class="field">
			<label for="ge-kwh">Electricity ($ per kWh) <span class="hint">EIA average for the state picked.</span></label>
			<input id="ge-kwh" name="kwh" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['us']['usdPerKwh'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="ge-therm">Natural gas ($ per therm) <span class="hint">EIA average for the state picked.</span></label>
			<input id="ge-therm" name="therm" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['us']['usdPerTherm'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="ge-propane">Propane ($ per gallon) <span class="hint">EIA U.S. residential average, <?php echo esc_html( (string) ( $hwm_m['propane']['period'] ?? '' ) ); ?>, before taxes.</span></label>
			<input id="ge-propane" name="propane" type="text" inputmode="decimal" value="<?php echo esc_attr( (string) ( $hwm_m['propane']['usdPerGallon'] ?? '' ) ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="ge-setpoint">Water heater setting (°F) <span class="hint">DOE FEMP recommends <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F.</span></label>
			<input id="ge-setpoint" name="setpoint" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?>" autocomplete="off">
		</div>
		<?php foreach ( (array) ( $hwm_m['typeOrder'] ?? array() ) as $hwm_id ) : ?>
			<div class="field">
				<label for="ge-uef-<?php echo esc_attr( (string) $hwm_id ); ?>">UEF, <?php echo esc_html( strtolower( (string) $hwm_m['types'][ $hwm_id ]['label'] ) ); ?></label>
				<input id="ge-uef-<?php echo esc_attr( (string) $hwm_id ); ?>" name="uef_<?php echo esc_attr( (string) $hwm_id ); ?>" type="text" inputmode="decimal" maxlength="5" value="<?php echo esc_attr( (string) $hwm_m['types'][ $hwm_id ]['uef'] ); ?>" autocomplete="off">
			</div>
		<?php endforeach; ?>
	</div>
	<p class="hint">UEF is on the unit's EnergyGuide label and spec sheet. Defaults are the median of real models.</p>
</details>
<?php
echo hwm_tool_data_script( 'hwm-op-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-cost-calculator', 'text' => 'Water heater cost calculator', 'why' => 'what each type costs to buy and install' ),
	array( 'slug' => 'tankless-vs-tank-water-heater', 'text' => 'Tankless vs tank cost of ownership', 'why' => 'purchase and running cost together' ),
	array( 'slug' => 'water-heater-size-calculator', 'text' => 'Water heater size calculator', 'why' => 'the right tank size for your household' ),
);
$related = array( 'water-heater-cost-calculator', 'tankless-vs-tank-water-heater', 'water-heater-size-calculator', 'tankless-water-heater-size-calculator' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Energy prices are the latest yearly state averages from the U.S. Energy Information Administration, efficiency is the median UEF of real models, and the typical day is DOE's. The same formula reproduces the yearly energy use printed on federal EnergyGuide labels within <?php echo esc_html( hwm_value( $hwm_c, 'labelMaxDiffPct' ) ); ?>%; see the cross-check on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#gas-vs-electric-water-heater' ); ?>">our methodology page</a>.</p>

<h2>Is a gas or electric water heater cheaper to run?</h2>
<p>A standard gas tank costs about $<?php echo $hwm_t( 'gas_tank', 'year' ); // phpcs:ignore ?> a year to run at U.S. average prices, against about $<?php echo $hwm_t( 'electric_tank', 'year' ); // phpcs:ignore ?> for a standard electric tank, for a typical household. Gas tanks were cheaper to run in <?php echo $hwm_fv( 'gasWins' ); // phpcs:ignore ?> of <?php echo $hwm_fv( 'stateCount' ); // phpcs:ignore ?> states and DC at their average prices. A heat pump water heater, at about $<?php echo $hwm_t( 'heat_pump', 'year' ); // phpcs:ignore ?> a year, beats both.</p>
<p>The reason is price per unit of heat. An electric tank turns nearly all of its electricity into hot water, but electricity costs several times more per BTU than natural gas, so the gas tank's lower efficiency still wins. A heat pump moves heat from the air instead of making it, which is why its UEF is several times higher.</p>
<dl>
	<dt>UEF (Uniform Energy Factor)</dt>
	<dd>The federal efficiency rating of a water heater: hot water energy delivered ÷ energy used in DOE's 24-hour test. Higher is better; it already includes standby loss for tank units.</dd>
	<dt>Therm</dt>
	<dd><?php echo $hwm_k( 'btuPerTherm' ); // phpcs:ignore ?> BTU of natural gas, the unit on most gas bills.</dd>
</dl>

<h2>Operating cost of five water heater types</h2>
<p>For a typical household (<?php echo $hwm_fv( 'gallons' ); // phpcs:ignore ?> gallons of hot water a day, heated from <?php echo $hwm_fv( 'inlet' ); // phpcs:ignore ?> °F to <?php echo $hwm_fv( 'setpoint' ); // phpcs:ignore ?> °F) at U.S. average prices of $<?php echo $hwm_fv( 'kwhPrice' ); // phpcs:ignore ?> per kWh and $<?php echo $hwm_fv( 'thermPrice' ); // phpcs:ignore ?> per therm, the yearly costs are:</p>
<div class="table-wrap" role="region" aria-label="Operating cost of five water heater types" tabindex="0">
	<table>
		<caption>Yearly and monthly running cost, typical household, U.S. average prices</caption>
		<thead><tr><th scope="col">Type</th><th scope="col" class="num">UEF</th><th scope="col" class="num">Energy a year</th><th scope="col" class="num">Per month</th><th scope="col" class="num">Per year</th></tr></thead>
		<tbody>
			<?php foreach ( (array) ( $hwm_m['typeOrder'] ?? array() ) as $hwm_id ) : ?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_m['types'][ $hwm_id ]['label'] ); ?></th><td class="num"><?php echo $hwm_t( $hwm_id, 'uef' ); // phpcs:ignore ?></td><td class="num"><?php echo '' !== ( $hwm_us[ $hwm_id ]['kwh'] ?? '' ) ? $hwm_t( $hwm_id, 'kwh' ) . ' kWh' : $hwm_t( $hwm_id, 'therms' ) . ' therms'; // phpcs:ignore ?></td><td class="num">$<?php echo $hwm_t( $hwm_id, 'month' ); // phpcs:ignore ?></td><td class="num">$<?php echo $hwm_t( $hwm_id, 'year' ); // phpcs:ignore ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Computed with the calculator's formula: EIA prices, DOE typical day, median UEF of real models (Rheem spec sheets, ENERGY STAR certified list).</p>
</div>
<p>Gas tankless units run cheaper than gas tanks because they have no tank to keep hot, which shows in their higher UEF. An electric tankless unit uses about the same energy as an electric tank: the tank's standby loss is small, and both convert electricity to heat one for one.</p>

<h2>How the running cost is calculated</h2>
<p>Yearly cost = the heat your hot water needs ÷ the heater's efficiency × your energy price. The heat is gallons per day × <?php echo $hwm_k( 'daysPerYear' ); // phpcs:ignore ?> days × <?php echo $hwm_k( 'lbPerGallon', 2 ); // phpcs:ignore ?> pounds per gallon × the temperature rise, because one BTU warms one pound of water by one degree.</p>
<ol>
	<li>Temperature rise = your water heater setting − the incoming water temperature in your state.</li>
	<li>Heat per year (BTU) = gallons a day × <?php echo $hwm_k( 'daysPerYear' ); // phpcs:ignore ?> × <?php echo $hwm_k( 'lbPerGallon', 2 ); // phpcs:ignore ?> × rise.</li>
	<li>Gas: therms = heat ÷ <?php echo $hwm_k( 'btuPerTherm' ); // phpcs:ignore ?> ÷ UEF. Electricity: kWh = heat ÷ <?php echo $hwm_k( 'btuPerKwh' ); // phpcs:ignore ?> ÷ UEF.</li>
	<li>Cost = therms or kWh × your price.</li>
</ol>
<p>This is the same arithmetic DOE uses for the EnergyGuide label. Our cross-check feeds each label's own UEF, test-day use and price into the formula and gets the label's kWh or therms back within <?php echo esc_html( hwm_value( $hwm_c, 'labelMaxDiffPct' ) ); ?>%.</p>

<h2>Gas vs electric operating cost by state</h2>
<p>State prices change the gap, but in every state's averages the gas tank still came out cheaper than the electric tank to run, and the heat pump cheaper than the gas tank in <?php echo $hwm_fv( 'hpVsGas' ); // phpcs:ignore ?> of <?php echo $hwm_fv( 'stateCount' ); // phpcs:ignore ?>. Colder states also need more heat, because the water comes in colder. Your own rates may differ from the state average; type them into the calculator.</p>
<details>
	<summary>Yearly running cost in all 50 states and DC</summary>
	<?php echo hwm_table( $hwm_c, 'byState' ); // phpcs:ignore ?>
</details>

<h2>How much does a bigger household change the bill?</h2>
<p>Running cost rises in step with hot water use, so a household that uses twice the hot water pays about twice as much. The table scales use by <?php echo esc_html( hwm_value( 'hot-water-use', 'perPersonGallonsPerDay.value' ) ); ?> gallons per person a day, from a DOE field study. That figure is older and higher than some newer estimates, so treat larger households as an upper case.</p>
<?php echo hwm_table( $hwm_c, 'people' ); // phpcs:ignore ?>

<h2>How many watts and kWh does a water heater use?</h2>
<p>A standard electric tank's heating elements are rated <?php echo $hwm_fv( 'elementKw' ); // phpcs:ignore ?> kW (<?php echo $hwm_fv( 'elementKwLow' ); // phpcs:ignore ?> to <?php echo $hwm_fv( 'elementKwHigh' ); // phpcs:ignore ?> kW in the models we track), and only one runs at a time. Over a year a typical household's electric tank uses about <?php echo $hwm_t( 'electric_tank', 'kwh' ); // phpcs:ignore ?> kWh, or <?php echo $hwm_t( 'electric_tank', 'kwhMonth' ); // phpcs:ignore ?> kWh a month.</p>
<p>A heat pump water heater uses about <?php echo $hwm_t( 'heat_pump', 'kwh' ); // phpcs:ignore ?> kWh a year for the same hot water. An electric tankless unit draws far more power while it runs, <?php echo $hwm_fv( 'tanklessKwLow' ); // phpcs:ignore ?> to <?php echo $hwm_fv( 'tanklessKwHigh' ); // phpcs:ignore ?> kW across one manufacturer's range, but only while water flows, so its yearly use is about <?php echo $hwm_t( 'electric_tankless', 'kwh' ); // phpcs:ignore ?> kWh. That high power is why it often needs new circuits; see the <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless size calculator' ); // phpcs:ignore ?> for the kW and amps your household needs.</p>

<h2>Beyond running cost: what else differs?</h2>
<div class="table-wrap" role="region" aria-label="Gas vs electric water heaters compared" tabindex="0">
	<table>
		<caption>Gas vs electric tank water heaters: what else to weigh</caption>
		<thead><tr><th scope="col">Factor</th><th scope="col">Gas tank</th><th scope="col">Electric tank</th></tr></thead>
		<tbody>
			<tr><th scope="row">Purchase and install</th><td>Costs more; needs a vent and gas line</td><td>Costs less; needs a 240-volt circuit</td></tr>
			<tr><th scope="row">Running cost</th><td>Usually lower</td><td>Usually higher, unless you switch to a heat pump</td></tr>
			<tr><th scope="row">Recovery</th><td>Faster, so a smaller tank can serve the same busiest hour</td><td>Slower; often needs a larger tank for the same first hour rating</td></tr>
			<tr><th scope="row">Power outage</th><td>Models with a standing pilot and no electric controls can keep heating; check your model's manual</td><td>No hot water</td></tr>
			<tr><th scope="row">Safety</th><td>Burns gas: needs a working vent and gas supply</td><td>No combustion or flue gases</td></tr>
		</tbody>
	</table>
</div>
<p>The tank sizes differ because gas burners reheat water faster than electric elements. Our <?php echo hwm_link( 'water-heater-size-calculator', 'water heater size calculator' ); // phpcs:ignore ?> shows the gas and electric size side by side for your household. If you are thinking about switching fuel, the <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?> adds the gas line, venting or circuit work to the price.</p>

<?php hwm_part( 'doe-2029', array( 'heading' => true, 'lead' => 'The electric side of this comparison changes for new heaters in a few years.' ) ); ?>

<h2>Common mistakes when comparing</h2>
<ul>
	<li><strong>Comparing efficiency, not cost.</strong> A higher UEF does not mean a lower bill if the fuel costs more per BTU.</li>
	<li><strong>Using a national price.</strong> State average electricity prices run from $<?php echo $hwm_fv( 'kwhPriceLow' ); // phpcs:ignore ?> to $<?php echo $hwm_fv( 'kwhPriceHigh' ); // phpcs:ignore ?> per kWh in EIA's data.</li>
	<li><strong>Ignoring the switch cost.</strong> Changing fuel can cost more than years of savings; check the cost of ownership before you switch.</li>
	<li><strong>Forgetting the setpoint.</strong> Setting the heater above <?php echo esc_html( hwm_value( 'site', 'defaults.setpointTempF' ) ); ?> °F raises the bill and the scald risk.</li>
</ul>

<h2>When is this a job for a licensed professional?</h2>
<p>Switching fuel means gas piping, venting or new electrical circuits, which need permits and licensed trades. Use this page to compare running costs; have a licensed plumber or electrician check what your home can support.</p>

<h2>Limitations of this estimate</h2>
<p>The formula uses the UEF rating, which comes from a standard test day; real use, a cold basement or a hot garage, and short draws change real efficiency. Heat pump efficiency drops in a cold room. UEF leaves out the small amount of electricity a gas tankless unit's fan and controls use. Bills also include fixed charges and tiered or time-of-use rates that a yearly average hides. Propane is not modeled yet.</p>
<?php
$faq = array(
	array(
		'q' => 'Is it cheaper to run a gas or electric water heater?',
		'a' => 'Usually gas. For a typical household at U.S. average prices, a standard gas tank costs about $' . ( $hwm_us['gas_tank']['year'] ?? '' ) . ' a year and an electric tank about $' . ( $hwm_us['electric_tank']['year'] ?? '' ) . '. A heat pump water heater is cheaper than both, at about $' . ( $hwm_us['heat_pump']['year'] ?? '' ) . ' a year.',
	),
	array(
		'q' => 'How many kWh does a water heater use per month?',
		'a' => 'A standard electric tank uses about ' . ( $hwm_us['electric_tank']['kwhMonth'] ?? '' ) . ' kWh a month for a typical household, or ' . ( $hwm_us['electric_tank']['kwh'] ?? '' ) . ' kWh a year. A heat pump water heater uses about ' . ( $hwm_f['hpShare'] ?? '' ) . '% of that. Larger households use more in proportion.',
	),
	array(
		'q' => 'How many watts does an electric water heater use?',
		'a' => 'Most standard electric tanks have ' . ( $hwm_f['elementKw'] ?? '' ) . ' kW elements, and only one heats at a time, so the heater draws about ' . ( $hwm_f['elementKw'] ?? '' ) . ' kW while heating. What you pay for is energy over time: about ' . ( $hwm_us['electric_tank']['kwh'] ?? '' ) . ' kWh a year for a typical household.',
	),
	array(
		'q' => 'How much electricity does a tankless water heater use?',
		'a' => 'An electric tankless unit uses about ' . ( $hwm_us['electric_tankless']['kwh'] ?? '' ) . ' kWh a year for a typical household, about the same as an electric tank. It draws much more power while water flows, which is why it often needs new circuits.',
	),
	array(
		'q' => 'What does a gas water heater cost per month?',
		'a' => 'About $' . ( $hwm_us['gas_tank']['month'] ?? '' ) . ' a month for a standard gas tank at U.S. average prices, using about ' . ( $hwm_us['gas_tank']['thermsMonth'] ?? '' ) . ' therms. A gas tankless unit costs about $' . ( $hwm_us['gas_tankless']['month'] ?? '' ) . ' a month for the same hot water.',
	),
	array(
		'q' => 'Should I switch from electric to gas?',
		'a' => 'Only if the savings cover the switch. Running a gas tank saves money each year, but a new gas line and vent can cost more than several years of savings. A heat pump water heater saves more and stays electric. Compare both with the cost of ownership calculator.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Average residential electricity prices by state (Electric Power Annual, Table 2.10)', 'url' => (string) hwm_raw( 'energy-prices', 'sources.electricity.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Natural gas prices: average residential price by state', 'url' => (string) hwm_raw( 'energy-prices', 'sources.gasPrice.sourceUrl' ), 'accessed' => (string) hwm_raw( 'energy-prices', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. Department of Energy, eCFR', 'title' => '10 CFR 430 Subpart B, Appendix E: uniform test method for water heaters (draw patterns, test temperatures)', 'url' => (string) hwm_raw( 'hot-water-use', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'hot-water-use', 'meta.sourceDate' ) ),
	array( 'publisher' => 'U.S. DOE Federal Energy Management Program', 'title' => 'Purchasing energy-efficient residential water heaters', 'url' => (string) hwm_raw( 'hot-water-use', 'typicalHouseholdGallonsPerDay.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'Certified water heaters dataset', 'url' => (string) hwm_raw( 'energystar-water-heaters', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem (via The Home Depot)', 'title' => 'EnergyGuide label, XE50T06ST45U1 electric water heater', 'url' => 'https://images.thdstatic.com/catalog/pdfImages/22/22032cf2-1800-482d-b21c-8526c5c35cdd.pdf', 'accessed' => '2026-11-13' ),
);
