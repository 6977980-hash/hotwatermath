<?php
/**
 * Hand-check table for a brand age page: each test serial, what our decoder reads, what the source says.
 * Rows come from data/computed/<brand page>.json → checks (built and asserted by npm run build:data).
 * Args: file (computed file name), caption.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'computed/' . (string) ( $args['file'] ?? '' );
$hwm_rows = (array) hwm_raw( $hwm_file, 'checks' );
if ( ! $hwm_rows ) {
	return;
}
?>
<div class="table-wrap" role="region" aria-label="<?php echo esc_attr( (string) ( $args['caption'] ?? 'Decoder test cases' ) ); ?>" tabindex="0">
	<table>
		<caption><?php echo esc_html( (string) ( $args['caption'] ?? 'Decoder test cases' ) ); ?></caption>
		<thead><tr><th scope="col">Serial</th><th scope="col">Our decoder reads</th><th scope="col">The source says</th><th scope="col">Match</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_rows as $hwm_r ) : ?>
				<tr>
					<th scope="row"><code><?php echo esc_html( (string) $hwm_r['serial'] ); ?></code></th>
					<td><?php echo esc_html( (string) $hwm_r['ours'] ); ?></td>
					<td><?php echo esc_html( (string) $hwm_r['published'] ); ?>. <a href="<?php echo esc_url( (string) $hwm_r['sourceUrl'] ); ?>" rel="noopener"><?php echo esc_html( (string) $hwm_r['source'] ); ?></a></td>
					<td><?php echo ! empty( $hwm_r['match'] ) ? ( ! empty( $hwm_r['cycle'] ) ? 'Yes (earlier cycle year)' : 'Yes' ) : 'No'; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Decoded with the same code the calculator runs, as of <?php echo esc_html( hwm_date( (string) hwm_raw( $hwm_file, 'asOf' ) ) ); ?>. Serials ending in 12345, 000001 or similar are test serials: only the date characters matter.</p>
</div>
