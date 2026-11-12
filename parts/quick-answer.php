<?php
/** Quick Answer capsule, ≤40 words, before the tool (CLAUDE.md §4.4). Args: text. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( empty( $args['text'] ) ) {
	return;
}
?>
<div class="quick-answer">
	<span class="quick-answer__label">Quick answer</span>
	<p><?php echo wp_kses( (string) $args['text'], array( 'a' => array( 'href' => true ), 'strong' => array() ) ); ?></p>
</div>
