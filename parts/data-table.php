<?php
/**
 * Sourced table with caption and source line (CLAUDE.md §5). Use hwm_table() rather than calling this directly.
 * Args: caption, columns [ { key, label, unit?, num? } ], rows [ { key: value } ], source, sourceUrl, sourceDate.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_cols = (array) $args['columns'];
?>
<div class="table-wrap" role="region" aria-label="<?php echo esc_attr( (string) $args['caption'] ); ?>" tabindex="0">
	<table>
		<?php if ( ! empty( $args['caption'] ) ) : ?><caption><?php echo esc_html( (string) $args['caption'] ); ?></caption><?php endif; ?>
		<thead>
			<tr>
				<?php foreach ( $hwm_cols as $hwm_c ) : ?>
					<th scope="col"<?php echo ! empty( $hwm_c['num'] ) ? ' class="num"' : ''; ?>><?php echo esc_html( (string) $hwm_c['label'] ); ?><?php echo ! empty( $hwm_c['unit'] ) ? ' (' . esc_html( (string) $hwm_c['unit'] ) . ')' : ''; ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( (array) $args['rows'] as $hwm_row ) : ?>
				<tr>
					<?php foreach ( $hwm_cols as $hwm_i => $hwm_c ) : ?>
						<?php $hwm_v = $hwm_row[ $hwm_c['key'] ] ?? ''; ?>
						<?php if ( 0 === $hwm_i ) : ?>
							<th scope="row"><?php echo esc_html( is_scalar( $hwm_v ) ? (string) $hwm_v : '' ); ?></th>
						<?php else : ?>
							<td<?php echo ! empty( $hwm_c['num'] ) ? ' class="num"' : ''; ?>><?php echo esc_html( is_scalar( $hwm_v ) ? (string) $hwm_v : '' ); ?></td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ( ! empty( $args['source'] ) ) : ?>
		<p class="table-source">Source:
			<?php if ( ! empty( $args['sourceUrl'] ) ) : ?>
				<a href="<?php echo esc_url( (string) $args['sourceUrl'] ); ?>" rel="noopener"><?php echo esc_html( (string) $args['source'] ); ?></a>
			<?php else : ?>
				<?php echo esc_html( (string) $args['source'] ); ?>
			<?php endif; ?>
			<?php if ( ! empty( $args['sourceDate'] ) ) : ?>(<?php echo esc_html( (string) $args['sourceDate'] ); ?>)<?php endif; ?>
		</p>
	<?php endif; ?>
</div>
