<?php
/**
 * Calculator wrapper. Inputs come from the tool's content file (args.inputs, HTML);
 * the default result is server-rendered from data/computed/<tool>.json so the page
 * is meaningful without JavaScript (CLAUDE.md §6). PHP does no math here.
 *
 * data/computed/<tool>.json → { "default": { "status": "ok|warn|bad", "statusLabel": "...",
 *   "headline": "...", "explain": "...", "formula": "...", "sources": "..." } }
 *
 * Args: tool (slug), inputs (HTML string), label.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_tool    = (string) ( $args['tool'] ?? '' );
$hwm_default = (array) ( hwm_raw( 'computed/' . $hwm_tool, 'default' ) ?? array() );
$hwm_state   = in_array( $hwm_default['status'] ?? '', array( 'ok', 'warn', 'bad' ), true ) ? ' result--' . $hwm_default['status'] : '';
$hwm_allowed = array( 'strong' => array(), 'em' => array(), 'a' => array( 'href' => true ), 'br' => array(), 'span' => array( 'class' => true ) );
?>
<section class="calc" id="calculator" data-tool="<?php echo esc_attr( $hwm_tool ); ?>" aria-label="<?php echo esc_attr( (string) ( $args['label'] ?? 'Calculator' ) ); ?>">
	<form class="calc__inputs" novalidate onsubmit="return false">
		<?php echo $args['inputs'] ?? ''; // phpcs:ignore -- markup from our own content file. ?>
	</form>
	<div class="calc__output">
		<div class="result<?php echo esc_attr( $hwm_state ); ?>" role="status" aria-live="polite" data-result>
			<?php if ( ! empty( $hwm_default['statusLabel'] ) ) : ?>
				<span class="result__status" data-result-status><?php echo esc_html( (string) $hwm_default['statusLabel'] ); ?></span>
			<?php endif; ?>
			<p class="result__headline" data-result-headline><?php echo wp_kses( (string) ( $hwm_default['headline'] ?? '' ), $hwm_allowed ); ?></p>
			<p class="result__explain" data-result-explain><?php echo wp_kses( (string) ( $hwm_default['explain'] ?? '' ), $hwm_allowed ); ?></p>
			<details class="disclosure">
				<summary>How we calculated this</summary>
				<pre class="formula" data-result-formula><?php echo esc_html( (string) ( $hwm_default['formula'] ?? '' ) ); ?></pre>
				<p><a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#' . $hwm_tool ); ?>">Full formula, sources and manufacturer cross-check</a></p>
			</details>
			<?php if ( ! empty( $hwm_default['sources'] ) ) : ?>
				<p class="result__sources" data-result-sources><?php echo wp_kses( (string) $hwm_default['sources'], $hwm_allowed ); ?></p>
			<?php endif; ?>
		</div>
		<div class="btn-row" style="margin-top: var(--space-3)">
			<button type="button" class="btn btn--ghost" data-action="copy">Copy result</button>
			<button type="button" class="btn btn--ghost" data-action="share">Share link</button>
		</div>
		<details class="disclosure" style="margin-top: var(--space-3)">
			<summary>Embed this calculator</summary>
			<p>Paste this on your site. The credit line is optional and you may remove it.</p>
			<textarea readonly rows="4" aria-label="Embed code"><?php echo esc_textarea( hwm_embed_snippet( $hwm_tool ) ); ?></textarea>
		</details>
	</div>
</section>
