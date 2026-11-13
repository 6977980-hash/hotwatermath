<?php
/**
 * Brand serial figure: where the rating plate sits (small drawing) and the serial with its date characters marked.
 * Layout arithmetic only (character boxes); no calculator math.
 *
 * Args:
 *   id       (string) unique id prefix for title/desc.
 *   title    (string) SVG title.
 *   desc     (string) SVG description (what the drawing shows, in words).
 *   unit     (string) 'tank' (plate on the upper jacket), 'valve' (plate next to the gas control) or 'wall' (wall-hung tankless, label on the right side).
 *   segments (array)  [ [ 'text' => 'XA', 'label' => 'year, month', 'hl' => true ], ... ]
 *   caption  (string) figcaption text.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_id   = (string) ( $args['id'] ?? 'serial-fig' );
$hwm_segs = (array) ( $args['segments'] ?? array() );
$hwm_unit = (string) ( $args['unit'] ?? 'tank' );
$hwm_cw   = 24; // character box width in px.
$hwm_x0   = 250;
$hwm_font = '-apple-system, Segoe UI, Roboto, Arial, sans-serif';
?>
<figure class="figure">
	<svg viewBox="0 0 640 270" width="640" height="270" role="img" aria-labelledby="<?php echo esc_attr( $hwm_id ); ?>-t <?php echo esc_attr( $hwm_id ); ?>-d">
		<title id="<?php echo esc_attr( $hwm_id ); ?>-t"><?php echo esc_html( (string) ( $args['title'] ?? '' ) ); ?></title>
		<desc id="<?php echo esc_attr( $hwm_id ); ?>-d"><?php echo esc_html( (string) ( $args['desc'] ?? '' ) ); ?></desc>
		<g fill="none" stroke="#17212e" stroke-width="2">
			<?php if ( 'wall' === $hwm_unit ) : ?>
				<rect x="40" y="60" width="96" height="140" rx="8"/>
				<path d="M78 60v-30h20v30M60 200v26M88 200v26M116 200v26"/>
			<?php else : ?>
				<rect x="30" y="40" width="120" height="200" rx="18"/>
				<path d="M80 40v-22h20v22"/>
			<?php endif; ?>
			<?php if ( 'valve' === $hwm_unit ) : ?>
				<rect x="70" y="190" width="40" height="22" rx="3"/>
			<?php endif; ?>
			<rect x="220" y="40" width="400" height="200" rx="6"/>
		</g>
		<?php
		$hwm_plate = array(
			'tank'  => array( 92, 80, 'plate on the upper jacket' ),
			'valve' => array( 112, 150, 'plate beside the gas control' ),
			'wall'  => array( 140, 100, 'label on the right side' ),
		);
		$hwm_p     = $hwm_plate[ $hwm_unit ] ?? $hwm_plate['tank'];
		?>
		<rect x="<?php echo esc_attr( (string) $hwm_p[0] ); ?>" y="<?php echo esc_attr( (string) $hwm_p[1] ); ?>" width="<?php echo 'wall' === $hwm_unit ? '14' : '34'; ?>" height="44" rx="3" fill="#e8f1f9" stroke="#0b4a7d" stroke-width="2.5"/>
		<path d="M<?php echo esc_attr( ( $hwm_p[0] + ( 'wall' === $hwm_unit ? 14 : 34 ) ) . ' ' . ( $hwm_p[1] + 22 ) ); ?>L220 140" stroke="#0b4a7d" stroke-width="1.5" stroke-dasharray="4 4" fill="none"/>
		<g font-family="<?php echo esc_attr( $hwm_font ); ?>" fill="#17212e">
			<text x="90" y="262" font-size="13" text-anchor="middle"><?php echo esc_html( (string) $hwm_p[2] ); ?></text>
			<text x="240" y="70" font-size="13">RATING PLATE (detail)</text>
			<text x="240" y="100" font-size="13">MODEL NO.  ··········</text>
			<text x="240" y="138" font-size="13">SERIAL NO.</text>
		</g>
		<?php
		$hwm_x   = $hwm_x0;
		$hwm_row = 0;
		foreach ( $hwm_segs as $hwm_seg ) :
			$hwm_chars = str_split( (string) $hwm_seg['text'] );
			$hwm_w     = count( $hwm_chars ) * $hwm_cw;
			$hwm_hl    = ! empty( $hwm_seg['hl'] );
			?>
			<rect x="<?php echo esc_attr( (string) $hwm_x ); ?>" y="150" width="<?php echo esc_attr( (string) $hwm_w ); ?>" height="34" rx="4" fill="<?php echo $hwm_hl ? '#fde8c8' : '#f3f5f7'; ?>" stroke="<?php echo $hwm_hl ? '#a35a00' : '#9aa5b1'; ?>" stroke-width="1.5"/>
			<?php foreach ( $hwm_chars as $hwm_i => $hwm_ch ) : ?>
				<text x="<?php echo esc_attr( (string) ( $hwm_x + $hwm_i * $hwm_cw + $hwm_cw / 2 ) ); ?>" y="174" font-family="ui-monospace, Menlo, Consolas, monospace" font-size="19" text-anchor="middle" fill="#17212e"><?php echo esc_html( $hwm_ch ); ?></text>
			<?php endforeach; ?>
			<?php if ( ! empty( $hwm_seg['label'] ) ) : ?>
				<?php $hwm_ly = 0 === $hwm_row % 2 ? 204 : 224; ?>
				<path d="M<?php echo esc_attr( (string) ( $hwm_x + $hwm_w / 2 ) ); ?> 184v<?php echo esc_attr( (string) ( $hwm_ly - 196 ) ); ?>" stroke="<?php echo $hwm_hl ? '#a35a00' : '#9aa5b1'; ?>" stroke-width="1.5"/>
				<text x="<?php echo esc_attr( (string) ( $hwm_x + $hwm_w / 2 ) ); ?>" y="<?php echo esc_attr( (string) ( $hwm_ly + 6 ) ); ?>" font-family="<?php echo esc_attr( $hwm_font ); ?>" font-size="12" text-anchor="middle" fill="<?php echo $hwm_hl ? '#7a4300' : '#4a5562'; ?>"><?php echo esc_html( (string) $hwm_seg['label'] ); ?></text>
				<?php ++$hwm_row; ?>
			<?php endif; ?>
			<?php
			$hwm_x += $hwm_w + 4;
		endforeach;
		?>
	</svg>
	<figcaption><?php echo esc_html( (string) ( $args['caption'] ?? '' ) ); ?></figcaption>
</figure>
