<?php
/**
 * /tankless-water-heater-size-calculator/ (STRATEGY §3.2). Every number is read from data/*.json or
 * data/computed/tankless-water-heater-size-calculator.json (built by npm run build:data from formulas.js).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/tankless-water-heater-size-calculator';
$hwm_fx  = 'fixture-flow-rates';
$hwm_tr  = 'tankless-reference-units';
$hwm_v   = static fn( string $path, ?int $dec = null ): string => esc_html( hwm_value( $hwm_c, $path, $dec ) );
$hwm_f   = static fn( string $path, ?int $dec = 1 ): string => esc_html( hwm_value( $hwm_fx, $path, $dec ) );
$hwm_def = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_set = hwm_value( 'site', 'defaults.setpointTempF' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_fixture_labels = array(
	'shower'         => 'Showers',
	'bathroomFaucet' => 'Bathroom faucets',
	'kitchenFaucet'  => 'Kitchen faucet',
	'dishwasher'     => 'Dishwasher',
	'clothesWasher'  => 'Clothes washer',
	'tubFill'        => 'Bathtub filling',
);
$hwm_states = (array) ( $hwm_cl['states'] ?? array() );
uasort( $hwm_states, static fn( $a, $b ) => strcmp( (string) $a['name'], (string) $b['name'] ) );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field">
	<label for="tk-state">Your state <span class="visually-hidden">(sets the winter incoming water temperature)</span></label>
	<select id="tk-state" name="state">
		<option value="">Not sure (<?php echo esc_html( hwm_value( $hwm_c, 'defaultInputs.inletF' ) ); ?> °F example)</option>
		<?php foreach ( $hwm_states as $hwm_k => $hwm_s ) : ?>
			<option value="<?php echo esc_attr( (string) $hwm_k ); ?>"><?php echo esc_html( (string) $hwm_s['name'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<fieldset>
	<legend>Running at the same time</legend>
	<div class="field-grid">
		<?php foreach ( $hwm_fixture_labels as $hwm_k => $hwm_label ) : ?>
			<div class="field">
				<label for="tk-<?php echo esc_attr( $hwm_k ); ?>"><?php echo esc_html( $hwm_label ); ?> <span class="hint"><?php echo esc_html( hwm_value( $hwm_fx, 'fixtures.' . $hwm_k . '.gpm', 1 ) ); ?> GPM each</span></label>
				<input id="tk-<?php echo esc_attr( $hwm_k ); ?>" name="<?php echo esc_attr( $hwm_k ); ?>" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="<?php echo esc_attr( (string) ( $hwm_def['counts'][ $hwm_k ] ?? 0 ) ); ?>" autocomplete="off">
			</div>
		<?php endforeach; ?>
	</div>
	<label class="check"><input type="checkbox" name="lowflow" value="1"> Low-flow fixtures (WaterSense: shower <?php echo $hwm_f( 'fixtures.shower.lowFlowGpm' ); // phpcs:ignore ?>, bathroom faucet <?php echo $hwm_f( 'fixtures.bathroomFaucet.lowFlowGpm' ); // phpcs:ignore ?> GPM)</label>
</fieldset>
<fieldset>
	<legend>Fuel</legend>
	<label class="check"><input type="radio" name="fuel" value="gas"<?php checked( 'gas', $hwm_def['fuel'] ?? '' ); ?>> Natural gas</label>
	<label class="check"><input type="radio" name="fuel" value="propane"> Propane</label>
	<label class="check"><input type="radio" name="fuel" value="electric"> Electric</label>
</fieldset>
<details class="disclosure">
	<summary>Temperatures and efficiency (assumed; change if you know yours)</summary>
	<div class="field">
		<label for="tk-inlet">Incoming water temperature (°F) <span class="hint">Coldest month for the state you pick. Measured yours? Type it here.</span></label>
		<input id="tk-inlet" name="inlet" type="text" inputmode="decimal" value="<?php echo esc_attr( hwm_value( $hwm_c, 'defaultInputs.inletF' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="tk-setpoint">Hot water temperature (°F) <span class="hint">Assumed <?php echo esc_html( $hwm_set ); ?> °F, the DOE FEMP setting for most homes.</span></label>
		<input id="tk-setpoint" name="setpoint" type="text" inputmode="decimal" value="<?php echo esc_attr( $hwm_set ); ?>" autocomplete="off">
	</div>
	<div class="field" data-field="efficiency">
		<label for="tk-efficiency">Gas efficiency (UEF) <span class="hint">Assumed <?php echo esc_html( hwm_value( $hwm_c, 'client.gasEfficiency.condensing', 2 ) ); ?> (condensing, ENERGY STAR median). Non-condensing units can be as low as <?php echo esc_html( hwm_value( $hwm_c, 'client.gasEfficiency.nonCondensing', 2 ) ); ?>.</span></label>
		<input id="tk-efficiency" name="efficiency" type="text" inputmode="decimal" value="<?php echo esc_attr( hwm_value( $hwm_c, 'defaultInputs.efficiency' ) ); ?>" autocomplete="off">
	</div>
</details>
<?php
echo hwm_tool_data_script( 'hwm-tankless-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'tankless-vs-tank-water-heater', 'text' => 'Tankless vs tank water heater', 'why' => 'compare ten-year cost with your energy prices' ),
	array( 'slug' => 'tankless-water-heater-installation-cost', 'text' => 'Tankless water heater installation cost', 'why' => 'what the switch costs' ),
	array( 'slug' => 'gas-line-size-for-tankless-water-heater', 'text' => 'Gas line size for a tankless water heater', 'why' => 'what the code tables say' ),
	array( 'slug' => 'tankless-water-heater-electrical-requirements', 'text' => 'Tankless water heater electrical requirements', 'why' => 'breakers, wire and service' ),
	array( 'slug' => 'water-heater-size-calculator', 'text' => 'Tank water heater size calculator', 'why' => 'size a tank instead' ),
);
$related    = array( 'water-heater-size-calculator', 'tankless-vs-tank-water-heater', 'gas-vs-electric-water-heater', 'water-heater-age-calculator' );

$hwm_ex   = (array) hwm_raw( $hwm_c, 'example' );
$hwm_dc   = (array) hwm_raw( $hwm_c, 'defaultCase' );
$hwm_ie   = (array) hwm_raw( $hwm_c, 'inletExtremes' );
$hwm_big  = (array) hwm_raw( $hwm_tr, 'gasUnits.0' );
$hwm_rule = (array) hwm_raw( $hwm_tr, 'rules' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Fixture flows are the federal maximums for fixtures sold today (10 CFR 430.32), incoming water temperature is estimated for your state from NOAA climate normals with the correlation DOE's EnergyPlus software uses, and the formula is checked against Rinnai, Noritz and Rheem sizing charts on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#tankless-water-heater-size-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you size a tankless water heater?</h2>
<p>Size a tankless water heater by two numbers: the flow of hot water you use at the same time, in gallons per minute (GPM), and the temperature rise, which is your hot water setting minus the coldest incoming water. The unit must deliver that flow at that rise. The calculator does both steps and turns them into gas input or electric power.</p>
<ol>
	<li><strong>Add up the flow.</strong> List what can run at once in your busiest moment, such as two showers and the kitchen faucet, and add their flow rates.</li>
	<li><strong>Find the temperature rise.</strong> Subtract the incoming water temperature in your coldest month from your setpoint. With a <?php echo esc_html( $hwm_set ); ?> °F setpoint and <?php echo $hwm_v( 'defaultCase.inlet' ); // phpcs:ignore ?> °F water, the rise is <?php echo $hwm_v( 'defaultCase.rise' ); // phpcs:ignore ?> °F.</li>
	<li><strong>Work out the heat.</strong> Heat output in BTU per hour = GPM × <?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.lbPerGallon', 2 ) ); ?> pounds per gallon × <?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.minutesPerHour' ) ); ?> minutes × rise.</li>
	<li><strong>Convert to a unit size.</strong> Gas input = output ÷ efficiency (UEF). Electric kW = output ÷ <?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.btuPerKwh', 0 ) ); ?>.</li>
</ol>
<dl>
	<dt>Flow rate (GPM)</dt>
	<dd>How many gallons of water a fixture lets through per minute. A showerhead sold in the U.S. may use at most <?php echo $hwm_f( 'fixtures.shower.gpm' ); // phpcs:ignore ?> GPM; WaterSense models use <?php echo $hwm_f( 'fixtures.shower.lowFlowGpm' ); // phpcs:ignore ?> or less.</dd>
	<dt>Temperature rise (ΔT)</dt>
	<dd>How many degrees the heater must warm the water: setpoint minus incoming temperature. The colder the water, the less flow the same unit can deliver.</dd>
	<dt>Uniform Energy Factor (UEF)</dt>
	<dd>The DOE efficiency rating on the label: the share of the energy a unit uses that ends up as hot water in a standard 24-hour test. Condensing gas units are rated near <?php echo esc_html( hwm_value( $hwm_c, 'client.gasEfficiency.condensing', 2 ) ); ?>; older non-condensing designs can be as low as <?php echo esc_html( hwm_value( $hwm_c, 'client.gasEfficiency.nonCondensing', 2 ) ); ?>.</dd>
</dl>
<?php echo hwm_table( 'computed/reference-tables', 'fixtures' ); // phpcs:ignore -- escaped in the part. ?>

<h2>Tankless water heater size chart</h2>
<p>Read across from your temperature rise to see the most hot water each size can make. A <?php echo $hwm_v( 'chartHighlight.gasBtuh' ); // phpcs:ignore ?> BTU/h gas unit, the largest common size for homes, delivers about <?php echo $hwm_v( 'chartHighlight.gasGpm' ); // phpcs:ignore ?> GPM at a <?php echo $hwm_v( 'chartHighlight.rise' ); // phpcs:ignore ?> °F rise; a <?php echo $hwm_v( 'chartHighlight.kw' ); // phpcs:ignore ?> kW electric unit delivers about <?php echo $hwm_v( 'chartHighlight.elecGpm' ); // phpcs:ignore ?> GPM at the same rise.</p>
<?php echo hwm_table( $hwm_c, 'gasChart' ); // phpcs:ignore ?>
<?php echo hwm_table( $hwm_c, 'electricChart' ); // phpcs:ignore ?>
<p>The charts use the same formula as the calculator. Manufacturer charts for real units land within a few percent of these numbers: Rinnai lists its <?php echo esc_html( (string) ( $hwm_big['model'] ?? '' ) ); ?> at <?php echo esc_html( (string) ( $hwm_big['gpmAt70'] ?? '' ) ); ?> GPM for a <?php echo $hwm_v( 'publishedRisesF.0' ); // phpcs:ignore ?> °F rise and <?php echo esc_html( (string) ( $hwm_big['gpmAt50'] ?? '' ) ); ?> GPM for a <?php echo $hwm_v( 'publishedRisesF.1' ); // phpcs:ignore ?> °F rise. Always check the flow chart of the model you plan to buy, because efficiency and minimum flow differ between units.</p>

<h2>What is the temperature rise in your state?</h2>
<p>Your temperature rise is the hot water setting minus the incoming water temperature in the coldest month. Incoming water ranges from about <?php echo esc_html( (string) ( $hwm_ie['coldest']['f'] ?? '' ) ); ?> °F in <?php echo esc_html( (string) ( $hwm_ie['coldest']['name'] ?? '' ) ); ?> to about <?php echo esc_html( (string) ( $hwm_ie['warmest']['f'] ?? '' ) ); ?> °F in <?php echo esc_html( (string) ( $hwm_ie['warmest']['name'] ?? '' ) ); ?> in the coldest month, so the same unit can be plenty in one state and too small in another.</p>
<p>No agency measures tap water temperature state by state. We estimate it from NOAA's 1991–2020 air temperature normals with the mains water correlation that DOE's EnergyPlus building software uses. If you can measure your cold water in winter, run the kitchen cold tap for a minute and read it with a thermometer, then type that value in the calculator.</p>
<details class="disclosure">
	<summary>Incoming water temperature for all 50 states and DC</summary>
	<?php echo hwm_table( 'computed/inlet-temps', 'inletTemps' ); // phpcs:ignore ?>
</details>

<h2>Worked example: two showers and a kitchen faucet in <?php echo $hwm_v( 'example.state' ); // phpcs:ignore ?></h2>
<p>A household in <?php echo $hwm_v( 'example.state' ); // phpcs:ignore ?> wants two showers and the kitchen faucet to run together on a winter morning. Here is the math, step by step.</p>
<ol>
	<li><strong>Flow:</strong> <?php echo $hwm_v( 'example.counts.shower' ); // phpcs:ignore ?> showers × <?php echo $hwm_v( 'example.showerGpm' ); // phpcs:ignore ?> GPM + <?php echo $hwm_v( 'example.counts.kitchenFaucet' ); // phpcs:ignore ?> kitchen faucet × <?php echo $hwm_v( 'example.kitchenGpm' ); // phpcs:ignore ?> GPM = <?php echo $hwm_v( 'example.gpm' ); // phpcs:ignore ?> GPM.</li>
	<li><strong>Rise:</strong> <?php echo esc_html( $hwm_set ); ?> °F − <?php echo $hwm_v( 'example.inlet' ); // phpcs:ignore ?> °F (the state's coldest month) = <?php echo $hwm_v( 'example.rise' ); // phpcs:ignore ?> °F.</li>
	<li><strong>Heat:</strong> <?php echo $hwm_v( 'example.gpm' ); // phpcs:ignore ?> × <?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.lbPerGallon', 2 ) ); ?> × <?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.minutesPerHour' ) ); ?> × <?php echo $hwm_v( 'example.rise' ); // phpcs:ignore ?> = <?php echo $hwm_v( 'example.output' ); // phpcs:ignore ?> BTU/h.</li>
	<li><strong>Gas:</strong> <?php echo $hwm_v( 'example.output' ); // phpcs:ignore ?> ÷ <?php echo $hwm_v( 'example.uef' ); // phpcs:ignore ?> = <?php echo $hwm_v( 'example.gasInput' ); // phpcs:ignore ?> BTU/h of input. That is more than one <?php echo $hwm_v( 'example.largestGas' ); // phpcs:ignore ?> BTU/h unit, so this home needs <?php echo $hwm_v( 'example.gasUnits' ); // phpcs:ignore ?> units piped together, or fewer fixtures at once.</li>
	<li><strong>Electric:</strong> <?php echo $hwm_v( 'example.output' ); // phpcs:ignore ?> ÷ <?php echo esc_html( hwm_value( 'computed/reference-tables', 'physics.btuPerKwh', 0 ) ); ?> = <?php echo $hwm_v( 'example.kw' ); // phpcs:ignore ?> kW, about <?php echo $hwm_v( 'example.amps' ); // phpcs:ignore ?> amps. The largest whole-house electric unit in our reference is <?php echo $hwm_v( 'example.largestKw' ); // phpcs:ignore ?> kW, so electric tankless is not realistic here.</li>
</ol>
<p>Switching to WaterSense showerheads (<?php echo $hwm_v( 'example.lowShowerGpm' ); // phpcs:ignore ?> GPM) drops the flow to <?php echo $hwm_v( 'example.lowGpm' ); // phpcs:ignore ?> GPM and the gas need to <?php echo $hwm_v( 'example.lowGasInput' ); // phpcs:ignore ?> BTU/h. That still needs <?php echo $hwm_v( 'example.lowGasUnits' ); // phpcs:ignore ?> units in a <?php echo $hwm_v( 'example.state' ); // phpcs:ignore ?> winter, which is why cold-climate homes often choose one large unit and accept that two showers plus the kitchen tap is the limit, or keep a tank.</p>

<h2>Gas vs electric tankless sizing</h2>
<p>Gas tankless units are sized in BTU per hour of gas input and electric units in kilowatts. The heat the water needs is the same either way, but electric units top out much lower: a large gas unit can serve two or three fixtures at once in most of the country, while a whole-house electric unit is usually limited to one or two in cold climates.</p>
<p>Propane units use the same BTU/h rating as natural gas units, so the calculator's gas result applies to both. Electric units also need a large electrical service. The reference models below are one manufacturer's published requirements, shown so you know what to ask your electrician, not as a wiring design.</p>
<?php echo hwm_table( 'computed/reference-tables', 'electricReference' ); // phpcs:ignore ?>
<p class="notice notice--warn">When the rise is above <?php echo esc_html( (string) ( $hwm_rule['coldClimateRiseF'] ?? '' ) ); ?> °F and the flow is above <?php echo esc_html( (string) ( $hwm_rule['coldClimateGpm'] ?? '' ) ); ?> GPM, the calculator warns that a single electric unit is rarely enough. That threshold is our own rule of thumb; the size chart above shows why.</p>

<h2>What size tankless water heater for a family of 4?</h2>
<p>A family of four usually wants two showers and a bathroom faucet at once, about <?php echo esc_html( (string) hwm_value( $hwm_c, 'climate.0.gpm' ) ); ?> GPM. The size that covers it depends on your state, because the temperature rise changes with the climate.</p>
<?php $hwm_climate = (array) hwm_raw( $hwm_c, 'climate' ); ?>
<div class="table-wrap" role="region" aria-label="Family of four by climate" tabindex="0">
	<table>
		<caption>Two showers and a bathroom faucet (<?php echo esc_html( (string) hwm_value( $hwm_c, 'climate.0.gpm' ) ); ?> GPM) by state</caption>
		<thead><tr><th scope="col">State</th><th scope="col" class="num">Coldest-month water (°F)</th><th scope="col" class="num">Rise (°F)</th><th scope="col" class="num">Gas input (BTU/h)</th><th scope="col" class="num">Gas units</th><th scope="col" class="num">Electric (kW)</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_climate as $hwm_r ) : ?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_r['name'] ); ?></th><td class="num"><?php echo esc_html( (string) $hwm_r['inlet'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_r['rise'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_r['gasInput'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_r['gasUnits'] ); ?></td><td class="num"><?php echo esc_html( (string) $hwm_r['kw'] ); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Source: computed with the calculator's formula; fixture flows from 10 CFR 430.32, water temperatures from NOAA normals and the EnergyPlus correlation, UEF <?php echo esc_html( hwm_value( $hwm_c, 'client.gasEfficiency.condensing', 2 ) ); ?>.</p>
</div>
<p>Two bathrooms versus three matters less than how many fixtures run at once. Three bathrooms rarely means three showers at the same moment; if it does, plan for two gas units or a tank. Enter your own count in the calculator rather than going by the number of bathrooms.</p>

<h2>Common tankless sizing mistakes</h2>
<ul>
	<li><strong>Using the yearly average water temperature.</strong> A unit sized for the average runs short every winter. The calculator uses the coldest month.</li>
	<li><strong>Counting every fixture in the house.</strong> Size for what runs together in your busiest moment, not for every tap.</li>
	<li><strong>Reading the maximum GPM on the box.</strong> The headline GPM is usually measured at a small rise. Look up the flow at your rise on the model's chart.</li>
	<li><strong>Forgetting gas supply and venting.</strong> A large gas unit often needs a bigger gas line and a new vent. Plan for these before you choose the size.</li>
	<li><strong>Assuming electric is a drop-in swap.</strong> Whole-house electric units need several large breakers and a service that can carry the load.</li>
</ul>

<h2>When is this a job for a licensed professional?</h2>
<p>Installing a tankless water heater involves gas piping, venting or high-current wiring, and local codes and permits apply. A licensed plumber or gas fitter should confirm the gas line size and venting, and a licensed electrician should confirm the breaker, wire and service capacity for an electric unit. Our planned guides on <?php echo hwm_link( 'gas-line-size-for-tankless-water-heater', 'gas line size for a tankless water heater' ); // phpcs:ignore ?> and <?php echo hwm_link( 'tankless-water-heater-electrical-requirements', 'tankless electrical requirements' ); // phpcs:ignore ?> explain what the codes say, for discussion with your installer.</p>

<h2>Limitations of this estimate</h2>
<p>The calculator heats the full fixture flow to the setpoint, while a shower actually mixes hot and cold water. That overstates the need a little, which errs on the safe side. Real units lose output at high altitude, and some will not fire below a minimum flow. Incoming water temperature is a statewide estimate; homes on wells or in mountain areas can differ. Use the result to shortlist sizes, then confirm with the manufacturer's chart for the exact model.</p>
<?php
$hwm_c0 = (array) hwm_raw( $hwm_c, 'climate.0' );
$hwm_c2 = (array) hwm_raw( $hwm_c, 'climate.2' );
$faq    = array(
	array(
		'q' => 'What size tankless water heater do I need for a family of 4?',
		'a' => 'Most families of four size for two showers and a faucet at once, about ' . hwm_value( $hwm_c, 'climate.0.gpm' ) . ' GPM. In ' . $hwm_c0['name'] . ' that needs about ' . $hwm_c0['gasInput'] . ' BTU/h of gas input; in ' . $hwm_c2['name'] . ' about ' . $hwm_c2['gasInput'] . ' BTU/h, which takes ' . $hwm_c2['gasUnits'] . ' units. Enter your state and fixtures for your number.',
	),
	array(
		'q' => 'What size tankless water heater do I need for 2 or 3 bathrooms?',
		'a' => 'Count the fixtures that run at the same time, not the bathrooms. Two showers at once is ' . hwm_value( $hwm_c, 'defaultCase.gpm' ) . ' GPM, which one large gas unit covers in most states at a ' . hwm_value( $hwm_c, 'defaultCase.rise' ) . ' °F rise. Three showers at once usually needs two gas units, or a tank.',
	),
	array(
		'q' => 'How is electric tankless sizing different from gas?',
		'a' => 'The water needs the same heat, but electric units are rated in kilowatts and top out around ' . hwm_value( $hwm_c, 'example.largestKw' ) . ' kW for homes. Two showers at a ' . hwm_value( $hwm_c, 'defaultCase.rise' ) . ' °F rise need about ' . hwm_value( $hwm_c, 'defaultCase.kw' ) . ' kW, more than one unit. Electric works best in warm climates or for one or two fixtures.',
	),
	array(
		'q' => 'What does temperature rise mean?',
		'a' => 'Temperature rise is how many degrees the heater must warm the incoming water to reach your setting. With ' . $hwm_set . ' °F hot water and ' . hwm_value( $hwm_c, 'defaultCase.inlet' ) . ' °F incoming water the rise is ' . hwm_value( $hwm_c, 'defaultCase.rise' ) . ' °F. The bigger the rise, the less flow a tankless unit can deliver.',
	),
	array(
		'q' => 'Can one tankless water heater run two showers?',
		'a' => 'Yes, in most of the U.S. a ' . hwm_value( $hwm_c, 'chartHighlight.gasBtuh' ) . ' BTU/h gas unit runs two ' . hwm_value( $hwm_fx, 'fixtures.shower.gpm', 1 ) . ' GPM showers at a ' . hwm_value( $hwm_c, 'defaultCase.rise' ) . ' °F rise. In the coldest states, or with an electric unit, two showers can be more than one unit can heat. Low-flow showerheads help.',
	),
	array(
		'q' => 'Is it better to oversize a tankless water heater?',
		'a' => 'A little extra capacity is safer than too little. Noritz advises that slightly oversizing is better than undersizing, because an undersized unit runs short in winter. A much larger unit costs more and may need a bigger gas line, so size for your real peak plus a small margin.',
	),
	array(
		'q' => 'How do I convert GPM to liters per minute?',
		'a' => 'Multiply gallons per minute by ' . hwm_value( 'computed/reference-tables', 'physics.litersPerGallon' ) . ' to get liters per minute, so ' . hwm_value( $hwm_c, 'defaultCase.gpm' ) . ' GPM is about ' . hwm_value( $hwm_c, 'defaultCase.lpm' ) . ' L/min. Divide a temperature rise in °F by ' . hwm_value( 'computed/reference-tables', 'physics.fahrenheitPerCelsius' ) . ' to get the rise in °C. Everything else on this page uses U.S. units.',
	),
);
$sources = array(
	array( 'publisher' => 'U.S. Code of Federal Regulations', 'title' => '10 CFR 430.32: energy and water conservation standards (faucet and showerhead maximum flow)', 'url' => (string) hwm_raw( $hwm_fx, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. EPA WaterSense', 'title' => 'WaterSense labeled showerheads', 'url' => (string) hwm_raw( $hwm_fx, 'fixtures.shower.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Pacific Northwest National Laboratory, Building America Solution Center', 'title' => 'Instantaneous water heater sizing (fixture and appliance flow ranges)', 'url' => (string) hwm_raw( $hwm_fx, 'fixtures.dishwasher.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'NOAA National Centers for Environmental Information', 'title' => 'Climate at a Glance: statewide average temperature, 1991–2020', 'url' => (string) hwm_raw( 'groundwater-temp-by-state', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. DOE EnergyPlus Engineering Reference', 'title' => 'Water mains temperatures (Christensen–Burch correlation)', 'url' => (string) hwm_raw( 'groundwater-temp-by-state', 'correlation.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. DOE Federal Energy Management Program', 'title' => 'Purchasing energy-efficient residential water heaters (setpoint guidance)', 'url' => (string) hwm_raw( 'site', 'defaults.setpointTempF.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'Residential water heaters key product criteria', 'url' => (string) hwm_raw( 'uef-ranges', 'types.gas_tankless.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rinnai America', 'title' => 'Tankless water heater model chart and specifications (2016)', 'url' => (string) hwm_raw( $hwm_tr, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Noritz', 'title' => 'How to size a tankless (printable sizing guide)', 'url' => (string) hwm_raw( $hwm_tr, 'noritzExample.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem', 'title' => 'Performance tankless electric spec sheet (RETEX series)', 'url' => (string) hwm_raw( 'electrical', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. DOE (UNT web archive copy)', 'title' => 'Energy Saver: sizing a new water heater, tankless example', 'url' => (string) hwm_raw( $hwm_fx, 'doeExample.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
