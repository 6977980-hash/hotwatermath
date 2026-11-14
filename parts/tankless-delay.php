<?php
/**
 * Tankless hot-water delay: the "cold water sandwich" and recirculation (audit suggestion S-7).
 * Data: data/tankless-reference-units.json → hotWaterDelay. Args: heading (string) = H2 text.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_d  = (array) hwm_raw( 'tankless-reference-units', 'hotWaterDelay' );
$hwm_sw = (array) ( $hwm_d['sandwich'] ?? array() );
$hwm_rc = (array) ( $hwm_d['recirculation'] ?? array() );
?>
<h2><?php echo esc_html( (string) ( $args['heading'] ?? 'Why does a tankless water heater run cold for a moment?' ) ); ?></h2>
<p>A gas tankless heater shuts its burner off between draws. If you turn the hot tap off and on again, a short slug of cold water passes through while the burner relights, so you feel hot, then cold, then hot. Installers call this the &ldquo;cold water sandwich&rdquo;. Recirculation and a small buffer tank are the two usual fixes.</p>
<blockquote class="source-quote"><p>&ldquo;<?php echo esc_html( (string) ( $hwm_sw['quote'] ?? '' ) ); ?>&rdquo;</p><footer><a href="<?php echo esc_url( (string) ( $hwm_sw['sourceUrl'] ?? '' ) ); ?>" rel="noopener"><?php echo esc_html( (string) ( $hwm_sw['source'] ?? '' ) ); ?></a>, checked <?php echo esc_html( hwm_date( (string) ( $hwm_sw['checked'] ?? '' ) ) ); ?></footer></blockquote>
<div class="table-wrap" role="region" aria-label="Ways to cut the tankless hot water delay" tabindex="0">
	<table>
		<caption>Ways to cut the tankless hot water delay</caption>
		<thead><tr><th scope="col">Fix</th><th scope="col">What it does</th><th scope="col">What the maker says</th></tr></thead>
		<tbody>
			<tr><th scope="row">Small electric buffer tank on the hot outlet</th><td>Blends out the cold slug after a restart</td><td><?php echo esc_html( (string) ( $hwm_sw['fix'] ?? '' ) ); ?> (Eemax)</td></tr>
			<tr><th scope="row">Recirculation pump, with or without a return line</th><td>Keeps hot water near the taps, so you also wait less for the first hot water</td><td><?php echo esc_html( (string) ( $hwm_rc['quote'] ?? '' ) ); ?> <?php echo esc_html( (string) ( $hwm_rc['how'] ?? '' ) ); ?>. (<a href="<?php echo esc_url( (string) ( $hwm_rc['sourceUrl'] ?? '' ) ); ?>" rel="noopener">Rinnai</a>)</td></tr>
			<tr><th scope="row">Change your habits</th><td>Avoid turning the hot tap off and on during a shower</td><td>Costs nothing; the burner stays lit while water keeps flowing</td></tr>
		</tbody>
	</table>
</div>
<p>Recirculation costs extra: a pump, controls and some heat lost from the pipes. It does not change the size you need, because sizing depends on the flow at your busiest moment, not on the delay. If you already have a recirculation loop with a tank heater, ask whether your new tankless model supports it before you buy.</p>
