<?php
/**
 * Server-rendered result table inside the calculator's result box (data/computed/<tool>.json → default.table).
 * The tool module rebuilds it with ui.js renderTable() using the same shape. PHP prints values only.
 * Args: table { caption, columns: [{ key, label, num? }], rows: [ { key: text } ], total?: { key: text } }.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_t    = (array) ( $args['table'] ?? array() );
$hwm_cols = (array) ( $hwm_t['columns'] ?? array() );
$hwm_row  = static function ( array $row, array $cols, bool $strong ): void {
	echo '<tr>';
	foreach ( $cols as $hwm_i => $hwm_c ) {
		$hwm_tag   = 0 === $hwm_i ? 'th' : 'td';
		$hwm_attr  = 0 === $hwm_i ? ' scope="row"' : '';
		$hwm_attr .= ! empty( $hwm_c['num'] ) ? ' class="num"' : '';
		$hwm_attr .= $strong ? ' style="font-weight:700"' : '';
		echo '<' . $hwm_tag . $hwm_attr . '>' . esc_html( (string) ( $row[ $hwm_c['key'] ] ?? '' ) ) . '</' . $hwm_tag . '>'; // phpcs:ignore -- fixed tag names.
	}
	echo '</tr>';
};
?>
<div class="table-wrap result__table" data-result-table>
	<table>
		<?php if ( ! empty( $hwm_t['caption'] ) ) : ?>
			<caption><?php echo esc_html( (string) $hwm_t['caption'] ); ?></caption>
		<?php endif; ?>
		<thead><tr>
			<?php foreach ( $hwm_cols as $hwm_c ) : ?>
				<th scope="col"<?php echo ! empty( $hwm_c['num'] ) ? ' class="num"' : ''; ?>><?php echo esc_html( (string) $hwm_c['label'] ); ?></th>
			<?php endforeach; ?>
		</tr></thead>
		<tbody>
			<?php foreach ( (array) ( $hwm_t['rows'] ?? array() ) as $hwm_r ) { $hwm_row( (array) $hwm_r, $hwm_cols, false ); } ?>
		</tbody>
		<?php if ( ! empty( $hwm_t['total'] ) ) : ?>
			<tfoot><?php $hwm_row( (array) $hwm_t['total'], $hwm_cols, true ); ?></tfoot>
		<?php endif; ?>
	</table>
</div>
