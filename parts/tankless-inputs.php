<?php
/**
 * Tankless size calculator inputs, shared by /tankless-water-heater-size-calculator/ and the articles that embed it
 * (gas line size, electrical requirements). Defaults come from data/computed/tankless-water-heater-size-calculator.json.
 * Args: fuel (gas|propane|electric, optional; default = the tool's default fuel).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/tankless-water-heater-size-calculator';
$hwm_fx  = 'fixture-flow-rates';
$hwm_f   = static fn( string $path, ?int $dec = 1 ): string => esc_html( hwm_value( $hwm_fx, $path, $dec ) );
$hwm_def = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_set = hwm_value( 'site', 'defaults.setpointTempF' );
$hwm_fuel = in_array( $args['fuel'] ?? '', array( 'gas', 'propane', 'electric' ), true ) ? (string) $args['fuel'] : (string) ( $hwm_def['fuel'] ?? '' );
$hwm_fixture_labels = array(
	'shower'         => 'Showers',
	'bathroomFaucet' => 'Bathroom faucets',
	'kitchenFaucet'  => 'Kitchen faucet',
	'dishwasher'     => 'Dishwasher',
	'clothesWasher'  => 'Clothes washer',
	'tubFill'        => 'Bathtub filling',
	'soakingTub'     => 'Large soaking tub',
);
$hwm_cs  = (array) ( $hwm_cl['customShower'] ?? array() );
$hwm_pou = (array) ( $hwm_cl['pou'] ?? array() );
$hwm_states = (array) ( $hwm_cl['states'] ?? array() );
uasort( $hwm_states, static fn( $a, $b ) => strcmp( (string) $a['name'], (string) $b['name'] ) );
?>
<div class="field">
	<label for="tk-state">Your state <span class="visually-hidden">(sets the winter incoming water temperature)</span></label>
	<select id="tk-state" name="state">
		<option value="">Not sure (<?php echo esc_html( hwm_value( $hwm_c, 'defaultInputs.inletF' ) ); ?> °F example)</option>
		<?php foreach ( $hwm_states as $hwm_k => $hwm_s ) : ?>
			<option value="<?php echo esc_attr( (string) $hwm_k ); ?>"><?php echo esc_html( hwm_state_label( (string) $hwm_k, (string) $hwm_s['name'] ) ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<fieldset>
	<legend>What are you sizing?</legend>
	<label class="check"><input type="radio" name="mode" value="whole" checked="checked"> The whole house</label>
	<label class="check"><input type="radio" name="mode" value="pou"> One tap or shower (point of use)</label>
</fieldset>
<div class="field" data-mode="pou" hidden>
	<label for="tk-pouFixture">Fixture</label>
	<select id="tk-pouFixture" name="pouFixture">
		<?php foreach ( (array) ( $hwm_pou['fixtures'] ?? array() ) as $hwm_k ) : ?>
			<option value="<?php echo esc_attr( (string) $hwm_k ); ?>"><?php echo esc_html( (string) ( $hwm_fixture_labels[ $hwm_k ] ?? $hwm_k ) ); ?> (<?php echo esc_html( hwm_value( $hwm_fx, 'fixtures.' . $hwm_k . '.gpm', 1 ) ); ?> GPM)</option>
		<?php endforeach; ?>
	</select>
</div>
<div class="field" data-mode="pou" hidden>
	<label for="tk-pouGpm">Flow of that fixture (GPM) <span class="hint">Optional. Leave blank for the typical flow; check the rating stamped on the faucet aerator or shower head.</span></label>
	<input id="tk-pouGpm" name="pouGpm" type="text" inputmode="decimal" value="" autocomplete="off">
</div>
<fieldset data-mode="whole">
	<legend>Running at the same time</legend>
	<div class="field-grid">
		<?php foreach ( $hwm_fixture_labels as $hwm_k => $hwm_label ) : ?>
			<div class="field">
				<label for="tk-<?php echo esc_attr( $hwm_k ); ?>"><?php echo esc_html( $hwm_label ); ?> <span class="hint"><?php echo esc_html( hwm_value( $hwm_fx, 'fixtures.' . $hwm_k . '.gpm', 1 ) ); ?> GPM each</span></label>
				<input id="tk-<?php echo esc_attr( $hwm_k ); ?>" name="<?php echo esc_attr( $hwm_k ); ?>" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="<?php echo esc_attr( (string) ( $hwm_def['counts'][ $hwm_k ] ?? 0 ) ); ?>" autocomplete="off">
			</div>
		<?php endforeach; ?>
	</div>
	<div class="field">
		<label for="tk-showerGpm">Your shower head flow (GPM) <span class="hint">Optional. Standard heads are <?php echo esc_html( hwm_value( $hwm_fx, 'fixtures.shower.gpm', 1 ) ); ?> GPM; WaterSense heads use no more than <?php echo esc_html( hwm_value( $hwm_fx, 'fixtures.shower.lowFlowGpm', 1 ) ); ?>. Rain heads or two heads can be <?php echo esc_html( hwm_value( $hwm_fx, 'customShower.maxGpm', 1 ) ); ?>.</span></label>
		<input id="tk-showerGpm" name="showerGpm" type="text" inputmode="decimal" value="" autocomplete="off" list="tk-showerGpm-list">
		<datalist id="tk-showerGpm-list">
			<?php foreach ( (array) ( $hwm_cs['options'] ?? array() ) as $hwm_o ) : ?>
				<option value="<?php echo esc_attr( number_format( (float) $hwm_o, 1 ) ); ?>"></option>
			<?php endforeach; ?>
		</datalist>
	</div>
	<label class="check"><input type="checkbox" name="lowflow" value="1"> Low-flow fixtures (WaterSense: shower <?php echo $hwm_f( 'fixtures.shower.lowFlowGpm' ); // phpcs:ignore ?>, bathroom faucet <?php echo $hwm_f( 'fixtures.bathroomFaucet.lowFlowGpm' ); // phpcs:ignore ?> GPM)</label>
</fieldset>
<fieldset data-mode="whole">
	<legend>Fuel</legend>
	<label class="check"><input type="radio" name="fuel" value="gas"<?php checked( 'gas', $hwm_fuel ); ?>> Natural gas</label>
	<label class="check"><input type="radio" name="fuel" value="propane"<?php echo 'propane' === $hwm_fuel ? ' checked="checked"' : ''; ?>> Propane</label>
	<label class="check"><input type="radio" name="fuel" value="electric"<?php echo 'electric' === $hwm_fuel ? ' checked="checked"' : ''; ?>> Electric</label>
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
