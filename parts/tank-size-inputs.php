<?php
/**
 * Tank size calculator inputs, shared by /water-heater-size-calculator/ and articles that embed it (40 vs 50 gallon).
 * Options and defaults come from data/computed/water-heater-size-calculator.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c       = 'computed/water-heater-size-calculator';
$hwm_def     = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl      = (array) hwm_raw( $hwm_c, 'client' );
$hwm_acts    = (array) ( $hwm_cl['activities'] ?? array() );
$hwm_presets = (array) ( $hwm_cl['presets'] ?? array() );
?>
<div class="field">
	<label for="ts-people">People in your home</label>
	<select id="ts-people" name="people">
		<?php foreach ( array_keys( $hwm_presets ) as $hwm_n ) : ?>
			<option value="<?php echo esc_attr( (string) $hwm_n ); ?>"<?php selected( (string) $hwm_n, (string) ( $hwm_def['people'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_n . ( 1 === (int) $hwm_n ? ' person' : ' people' ) ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<fieldset>
	<legend>Fuel</legend>
	<label class="check"><input type="radio" name="fuel" value="gas"<?php checked( 'gas', $hwm_def['fuel'] ?? '' ); ?>> Gas or propane</label>
	<label class="check"><input type="radio" name="fuel" value="electric"> Electric</label>
	<label class="check"><input type="radio" name="fuel" value="heatpump"> Heat pump (hybrid)</label>
</fieldset>
<details class="disclosure">
	<summary>Your busiest hour (people fills in a typical one; change any count)</summary>
	<div class="field-grid">
		<?php foreach ( $hwm_acts as $hwm_k => $hwm_a ) : ?>
			<div class="field">
				<label for="ts-<?php echo esc_attr( (string) $hwm_k ); ?>"><?php echo esc_html( (string) $hwm_a['label'] ); ?> <span class="hint"><?php echo esc_html( (string) $hwm_a['gallons'] ); ?> gal each</span></label>
				<input id="ts-<?php echo esc_attr( (string) $hwm_k ); ?>" name="<?php echo esc_attr( (string) $hwm_k ); ?>" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="<?php echo esc_attr( (string) ( $hwm_def['counts'][ $hwm_k ] ?? 0 ) ); ?>" autocomplete="off">
			</div>
		<?php endforeach; ?>
	</div>
	<label class="check"><input type="checkbox" name="margin" value="1"> We often run several things at once (add <?php echo esc_html( hwm_value( $hwm_c, 'defaultCase.marginPct' ) ); ?>%, our assumption)</label>
</details>
<?php
echo hwm_tool_data_script( 'hwm-tank-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
