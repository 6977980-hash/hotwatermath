<?php
/**
 * Repair-or-replace calculator inputs, shared by the tool page and the troubleshooting articles that embed it.
 * Defaults and options come from data/computed/repair-or-replace-water-heater.json; symptoms from decision-rules.json.
 * Args: checked (array of symptom ids to pre-tick, optional).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c     = 'computed/repair-or-replace-water-heater';
$hwm_def   = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_types = (array) hwm_raw( $hwm_c, 'client.types' );
$hwm_symp  = (array) hwm_raw( 'decision-rules', 'symptoms.items' );
$hwm_on    = (array) ( $args['checked'] ?? array() );
?>
<div class="field-grid">
	<div class="field">
		<label for="rr-age">Age (years)</label>
		<input id="rr-age" name="age" type="text" inputmode="decimal" maxlength="5" value="<?php echo esc_attr( (string) ( $hwm_def['ageYears'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="rr-type">Type</label>
		<select id="rr-type" name="type">
			<?php foreach ( $hwm_types as $hwm_k => $hwm_t ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_k ); ?>"<?php selected( (string) $hwm_k, (string) ( $hwm_def['type'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_t['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="rr-repair">Repair quote ($) <span class="hint">Parts and labor, total.</span></label>
		<input id="rr-repair" name="repair" type="text" inputmode="decimal" maxlength="7" value="<?php echo esc_attr( (string) ( $hwm_def['repairUsd'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="rr-replace">New unit, installed ($) <span class="hint">Assumed: typical installed price, U.S. average, from our cost calculator. Type your quote if you have one.</span></label>
		<input id="rr-replace" name="replace" type="text" inputmode="decimal" maxlength="7" value="<?php echo esc_attr( (string) ( $hwm_def['replace'] ?? '' ) ); ?>" autocomplete="off">
	</div>
</div>
<p class="hint">Not sure of the age? <?php echo hwm_link( 'water-heater-age-calculator', 'Find it from the serial number' ); // phpcs:ignore ?>; the result links back here with the age filled in.</p>
<fieldset>
	<legend>What is happening? (tick any)</legend>
	<?php foreach ( $hwm_symp as $hwm_s ) : ?>
		<label class="check"><input type="checkbox" name="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>" value="1"<?php checked( in_array( (string) $hwm_s['id'], $hwm_on, true ) ); ?>> <?php echo esc_html( (string) $hwm_s['label'] ); ?></label>
	<?php endforeach; ?>
</fieldset>
<details class="disclosure">
	<summary>Energy prices (assumed; change if you know yours)</summary>
	<div class="field">
		<label for="rr-kwh">Electricity ($ per kWh) <span class="hint">U.S. average, EIA.</span></label>
		<input id="rr-kwh" name="kwh" type="text" inputmode="decimal" value="<?php echo esc_attr( hwm_value( $hwm_c, 'client.prices.usdPerKwh' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="rr-therm">Natural gas ($ per therm) <span class="hint">U.S. average, EIA.</span></label>
		<input id="rr-therm" name="therm" type="text" inputmode="decimal" value="<?php echo esc_attr( hwm_value( $hwm_c, 'client.prices.usdPerTherm' ) ); ?>" autocomplete="off">
	</div>
</details>
<?php
echo hwm_tool_data_script( 'hwm-repair-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
