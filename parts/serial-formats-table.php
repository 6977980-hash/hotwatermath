<?php
/**
 * One brand's serial date formats, every era, with the source of each.
 * Rows come from data/computed/<brand page>.json → formatRows (from data/serial-formats.json).
 * Args: file, caption.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_file = 'computed/' . (string) ( $args['file'] ?? '' );
$hwm_rows = (array) hwm_raw( $hwm_file, 'formatRows' );
?>
<div class="table-wrap" role="region" aria-label="<?php echo esc_attr( (string) ( $args['caption'] ?? '' ) ); ?>" tabindex="0">
	<table>
		<caption><?php echo esc_html( (string) ( $args['caption'] ?? '' ) ); ?></caption>
		<thead><tr><th scope="col">Era</th><th scope="col">Where the date is</th><th scope="col">Example</th><th scope="col">Source</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_rows as $hwm_r ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( (string) $hwm_r['era'] ); ?></th>
					<td><?php echo esc_html( ucfirst( (string) $hwm_r['rule'] ) ); ?></td>
					<td><?php echo esc_html( (string) $hwm_r['example'] ); ?></td>
					<td><a href="<?php echo esc_url( (string) $hwm_r['sourceUrl'] ); ?>" rel="noopener"><?php echo esc_html( (string) $hwm_r['source'] ); ?></a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">The decoder tries these patterns in this order. A star (*) in an example stands for any character.</p>
</div>
