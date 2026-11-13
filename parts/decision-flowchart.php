<?php
/**
 * Repair-or-replace decision flowchart (inline SVG, our own drawing). The rule order matches
 * repairOrReplace() in assets/js/formulas.js; the thresholds are passed in from data/decision-rules.json.
 * Layout arithmetic only; no calculator math.
 *
 * Args: always (int, %), age (int, % of life), old (int, %).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_always = (string) ( $args['always'] ?? '' );
$hwm_age    = (string) ( $args['age'] ?? '' );
$hwm_old    = (string) ( $args['old'] ?? '' );
$hwm_steps  = array(
	array( 'Leak from the tank itself,', 'or the heater was flooded?', 'replace' ),
	array( 'Repair costs more than', $hwm_always . '% of a new unit?', 'replace' ),
	array( 'Used ' . $hwm_age . '%+ of its life and', 'repair more than ' . $hwm_old . '%?', 'replace' ),
	array( 'Used ' . $hwm_age . '%+ of its', 'typical life?', 'borderline' ),
	array( 'Repair costs more than', $hwm_old . '% of a new unit?', 'borderline' ),
);
$hwm_style = array(
	'replace'    => array( '#fdeceb', '#a8231a', 'Replace' ),
	'borderline' => array( '#fdf4dc', '#7a4f00', 'Borderline' ),
	'repair'     => array( '#e8f5ec', '#1d6b38', 'Repair' ),
);
$hwm_row = 92;
$hwm_h   = 20 + count( $hwm_steps ) * $hwm_row + 60;
$hwm_font = '-apple-system, Segoe UI, Roboto, Arial, sans-serif';
?>
<figure class="figure">
	<svg viewBox="0 0 360 <?php echo esc_attr( (string) $hwm_h ); ?>" width="360" height="<?php echo esc_attr( (string) $hwm_h ); ?>" role="img" aria-labelledby="rr-flow-t rr-flow-d" font-family="<?php echo esc_attr( $hwm_font ); ?>">
		<title id="rr-flow-t">Repair or replace decision flowchart</title>
		<desc id="rr-flow-d">Questions in order, top to bottom. Leak from the tank or flooded: replace. Repair over <?php echo esc_html( $hwm_always ); ?>% of a new unit: replace. Used <?php echo esc_html( $hwm_age ); ?>% or more of its typical life and repair over <?php echo esc_html( $hwm_old ); ?>%: replace. Used <?php echo esc_html( $hwm_age ); ?>% or more of its life with a cheaper repair: borderline. Younger heater with a repair over <?php echo esc_html( $hwm_old ); ?>%: borderline. Otherwise: repair.</desc>
		<defs>
			<marker id="rr-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0 0 L10 5 L0 10 z" fill="#4a5668"/></marker>
		</defs>
		<?php foreach ( $hwm_steps as $hwm_i => $hwm_s ) : ?>
			<?php
			$hwm_y  = 20 + $hwm_i * $hwm_row;
			$hwm_st = $hwm_style[ $hwm_s[2] ];
			?>
			<rect x="8" y="<?php echo esc_attr( (string) $hwm_y ); ?>" width="228" height="58" rx="8" fill="#f4f7fa" stroke="#0b4a7d" stroke-width="2"/>
			<text x="122" y="<?php echo esc_attr( (string) ( $hwm_y + 24 ) ); ?>" text-anchor="middle" font-size="14" fill="#17212e"><?php echo esc_html( $hwm_s[0] ); ?></text>
			<text x="122" y="<?php echo esc_attr( (string) ( $hwm_y + 43 ) ); ?>" text-anchor="middle" font-size="14" fill="#17212e"><?php echo esc_html( $hwm_s[1] ); ?></text>
			<line x1="236" y1="<?php echo esc_attr( (string) ( $hwm_y + 29 ) ); ?>" x2="262" y2="<?php echo esc_attr( (string) ( $hwm_y + 29 ) ); ?>" stroke="#4a5668" stroke-width="2" marker-end="url(#rr-arrow)"/>
			<text x="249" y="<?php echo esc_attr( (string) ( $hwm_y + 22 ) ); ?>" text-anchor="middle" font-size="12" fill="#4a5668">Yes</text>
			<rect x="264" y="<?php echo esc_attr( (string) ( $hwm_y + 9 ) ); ?>" width="88" height="40" rx="20" fill="<?php echo esc_attr( $hwm_st[0] ); ?>" stroke="<?php echo esc_attr( $hwm_st[1] ); ?>" stroke-width="2"/>
			<text x="308" y="<?php echo esc_attr( (string) ( $hwm_y + 34 ) ); ?>" text-anchor="middle" font-size="14" font-weight="700" fill="<?php echo esc_attr( $hwm_st[1] ); ?>"><?php echo esc_html( $hwm_st[2] ); ?></text>
			<line x1="122" y1="<?php echo esc_attr( (string) ( $hwm_y + 58 ) ); ?>" x2="122" y2="<?php echo esc_attr( (string) ( $hwm_y + $hwm_row - 2 ) ); ?>" stroke="#4a5668" stroke-width="2" marker-end="url(#rr-arrow)"/>
			<text x="132" y="<?php echo esc_attr( (string) ( $hwm_y + 78 ) ); ?>" font-size="12" fill="#4a5668">No</text>
		<?php endforeach; ?>
		<?php $hwm_y = 20 + count( $hwm_steps ) * $hwm_row; ?>
		<rect x="62" y="<?php echo esc_attr( (string) $hwm_y ); ?>" width="120" height="40" rx="20" fill="#e8f5ec" stroke="#1d6b38" stroke-width="2"/>
		<text x="122" y="<?php echo esc_attr( (string) ( $hwm_y + 25 ) ); ?>" text-anchor="middle" font-size="14" font-weight="700" fill="#1d6b38">Repair</text>
	</svg>
	<figcaption>The tool asks these questions in this order and stops at the first "yes". Percentages are of the price of a new unit, or of the heater's typical life. Safety problems, such as a relief valve that keeps discharging, come before any cost rule.</figcaption>
</figure>
