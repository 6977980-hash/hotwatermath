<?php
/**
 * Expansion tank calculator inputs, shared by /water-heater-expansion-tank-size/ and articles that embed it
 * (relief valve leaking). Defaults come from data/computed/water-heater-expansion-tank-size.json → client.defaults.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c  = 'computed/water-heater-expansion-tank-size';
$hwm_cl = (array) hwm_raw( $hwm_c, 'client' );
$hwm_d  = (array) ( $hwm_cl['defaults'] ?? array() );
?>
<div class="field-grid">
	<div class="field">
		<label for="et-gallons">Water heater size (gallons) <span class="hint">On the rating label.</span></label>
		<input id="et-gallons" name="gallons" type="text" inputmode="decimal" maxlength="5" value="<?php echo esc_attr( (string) ( $hwm_d['heaterGallons'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="et-temp">Temperature setting (°F) <span class="hint">Assumed; change if you know yours.</span></label>
		<input id="et-temp" name="temp" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( (string) ( $hwm_d['tempF'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="et-supply">Water supply pressure (psi) <span class="hint">Read a gauge on a hose bib; assumed until you do.</span></label>
		<input id="et-supply" name="supply" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( (string) ( $hwm_d['supplyPsi'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="et-max">Maximum pressure (psi) <span class="hint">The relief valve setting; assumed.</span></label>
		<input id="et-max" name="max" type="text" inputmode="decimal" maxlength="3" value="<?php echo esc_attr( (string) ( $hwm_d['maxPsi'] ?? '' ) ); ?>" autocomplete="off">
	</div>
</div>
<?php
echo hwm_tool_data_script( 'hwm-et-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
