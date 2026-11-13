<?php
/**
 * Cost calculator inputs, shared by /water-heater-cost-calculator/ and the installation cost articles that embed it
 * preset to their type. Options and defaults come from data/computed/water-heater-cost-calculator.json.
 * Args: preset (optional) = data/computed/<article>.json → calculator.inputs: { type, size, state, scenario, <extra>: true }.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c     = 'computed/water-heater-cost-calculator';
$hwm_cl    = (array) hwm_raw( $hwm_c, 'client' );
$hwm_m     = (array) ( $hwm_cl['model'] ?? array() );
$hwm_pre   = (array) ( $args['preset'] ?? array() );
$hwm_def   = $hwm_pre ? $hwm_pre : (array) ( $hwm_cl['defaults'] ?? array() );
$hwm_din   = $hwm_pre ? $hwm_pre : (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_types = (array) ( $hwm_m['types'] ?? array() );
$hwm_dtype = (string) ( $hwm_def['type'] ?? 'gas_tank' );
?>
<div class="field-grid">
	<div class="field">
		<label for="wc-type">Type</label>
		<select id="wc-type" name="type">
			<?php foreach ( (array) ( $hwm_m['typeOrder'] ?? array() ) as $hwm_t ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_t ); ?>"<?php selected( (string) $hwm_t, $hwm_dtype ); ?>><?php echo esc_html( (string) $hwm_types[ $hwm_t ]['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="wc-size">Size</label>
		<select id="wc-size" name="size">
			<?php foreach ( (array) ( $hwm_types[ $hwm_dtype ]['sizes'] ?? array() ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"<?php selected( (string) $hwm_s['id'], (string) ( $hwm_def['size'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="wc-state">Your state</label>
		<select id="wc-state" name="state">
			<?php foreach ( (array) hwm_raw( $hwm_c, 'stateOptions' ) as $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>"<?php selected( (string) $hwm_s['id'], (string) ( $hwm_def['state'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="wc-scenario">The job</label>
		<select id="wc-scenario" name="scenario">
			<?php foreach ( (array) ( $hwm_m['scenarios'] ?? array() ) as $hwm_k => $hwm_s ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_k ); ?>"<?php selected( (string) $hwm_k, (string) ( $hwm_def['scenario'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_s['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
<fieldset>
	<legend>Extras your job needs <span class="hint">(the job type ticks the usual ones; change them)</span></legend>
	<?php foreach ( (array) ( $hwm_m['extras'] ?? array() ) as $hwm_k => $hwm_x ) : ?>
		<label class="check"><input type="checkbox" name="<?php echo esc_attr( (string) $hwm_k ); ?>" value="1"<?php checked( ! empty( $hwm_din[ $hwm_k ] ) ); ?>> <?php echo esc_html( (string) $hwm_x['label'] ); ?></label>
	<?php endforeach; ?>
</fieldset>
<p class="hint">Assumed: equipment at current listing prices, labor and extras at our sourced ranges, scaled by your state's price level. Not sure which size you need? <?php echo hwm_link( 'water-heater-size-calculator', 'Size it first' ); // phpcs:ignore ?>.</p>
<?php
echo hwm_tool_data_script( 'hwm-cost-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
