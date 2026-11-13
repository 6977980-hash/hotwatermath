<?php
/**
 * Age calculator inputs, shared by the age calculator, the brand age pages and the lifespan article.
 * Defaults come from data/computed/<file>.json → defaultInputs; options from serial-formats.json and lifespans.json.
 * Args: file (computed file name without "computed/", default water-heater-age-calculator).
 * Form field names never match WordPress query vars (madeYear/madeMonth, not year/monthnum).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c    = 'computed/' . (string) ( $args['file'] ?? 'water-heater-age-calculator' );
$hwm_sf   = 'serial-formats';
$hwm_ls   = 'lifespans';
$hwm_def  = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_opts = (array) hwm_raw( $hwm_sf, 'brandOptions.options' );
$hwm_hs   = (array) hwm_raw( $hwm_ls, 'adjustments.hardnessScale' );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_hint = (string) ( $args['serial_hint'] ?? 'On the rating plate. The example is a test serial from Rheem\'s own lookup; replace it with yours.' );
?>
<div class="field">
	<label for="age-brand">Brand</label>
	<select id="age-brand" name="brand">
		<?php foreach ( $hwm_opts as $hwm_o ) : ?>
			<option value="<?php echo esc_attr( $hwm_o['id'] ); ?>"<?php selected( $hwm_o['id'], $hwm_def['brand'] ?? '' ); ?>><?php echo esc_html( $hwm_o['label'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="field">
	<label for="age-serial">Serial number <span class="hint"><?php echo esc_html( $hwm_hint ); ?></span></label>
	<input id="age-serial" name="serial" type="text" value="<?php echo esc_attr( (string) ( $hwm_def['serial'] ?? '' ) ); ?>" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="24">
</div>
<details class="disclosure">
	<summary>No serial, or brand not decoded? Enter the year</summary>
	<div class="field">
		<label for="age-year">Year it was made <span class="hint">From the rating plate. Used when the serial cannot be decoded.</span></label>
		<input id="age-year" name="madeYear" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off">
	</div>
	<div class="field">
		<label for="age-month">Month (optional)</label>
		<select id="age-month" name="madeMonth">
			<option value="">Not sure</option>
			<?php foreach ( (array) hwm_raw( $hwm_c, 'client.months' ) as $hwm_i => $hwm_m ) : ?>
				<option value="<?php echo esc_attr( (string) ( $hwm_i + 1 ) ); ?>"><?php echo esc_html( $hwm_m ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</details>
<div class="field">
	<label for="age-type">Type of water heater</label>
	<select id="age-type" name="type">
		<?php foreach ( (array) hwm_raw( $hwm_ls, 'toolTypes.options' ) as $hwm_o ) : ?>
			<option value="<?php echo esc_attr( $hwm_o['id'] ); ?>"<?php selected( $hwm_o['id'], $hwm_def['type'] ?? '' ); ?>><?php echo esc_html( $hwm_o['label'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="field">
	<label for="age-hardness">Water hardness <span class="hint">Assumed moderately hard; change if you know yours. <?php echo $hwm_ext( (string) ( $hwm_hs['mapUrl'] ?? '' ), 'USGS hardness map' ); // phpcs:ignore ?></span></label>
	<select id="age-hardness" name="hardness">
		<?php foreach ( array( 'soft', 'moderate', 'hard', 'veryHard' ) as $hwm_k ) : ?>
			<option value="<?php echo esc_attr( $hwm_k ); ?>"<?php selected( $hwm_k, $hwm_def['hardness'] ?? '' ); ?>><?php echo esc_html( $hwm_hs[ $hwm_k ]['label'] . ' (' . $hwm_hs[ $hwm_k ]['mgPerL'] . ' mg/L)' ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<label class="check"><input type="checkbox" name="flushed" value="1"<?php checked( ! empty( $hwm_def['flushed'] ) ); ?>> Tank flushed about once a year</label>
<label class="check"><input type="checkbox" name="anode" value="1"<?php checked( ! empty( $hwm_def['anode'] ) ); ?>> Anode rod has been replaced</label>
<?php
echo hwm_tool_data_script( 'hwm-age-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
