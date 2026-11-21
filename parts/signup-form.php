<?php
/**
 * Email signup box (inc/lists.php): recall alerts or Heater Health Reminder. Double opt-in; posts to admin-post.php
 * (works without JavaScript; assets/js/forms.js shows the answer in place). No nonce: pages are cached, so spam is
 * handled by the honeypot field, a rate limit and the confirmation email.
 * Args: list ('recall'|'health'), heading, text, hidden (array name => value, kept in sync by the tool script),
 *       fields (HTML of extra visible fields, optional), id (anchor id).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_list = 'health' === ( $args['list'] ?? '' ) ? 'health' : 'recall';
$hwm_id   = (string) ( $args['id'] ?? 'signup' );
?>
<section class="signup" id="<?php echo esc_attr( $hwm_id ); ?>" aria-labelledby="<?php echo esc_attr( $hwm_id ); ?>-h">
	<h2 id="<?php echo esc_attr( $hwm_id ); ?>-h"><?php echo esc_html( (string) ( $args['heading'] ?? '' ) ); ?></h2>
	<p><?php echo esc_html( (string) ( $args['text'] ?? '' ) ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-signup="<?php echo esc_attr( $hwm_list ); ?>">
		<input type="hidden" name="action" value="hwm_signup">
		<input type="hidden" name="list" value="<?php echo esc_attr( $hwm_list ); ?>">
		<?php foreach ( (array) ( $args['hidden'] ?? array() ) as $hwm_k => $hwm_v ) : ?>
			<input type="hidden" name="<?php echo esc_attr( (string) $hwm_k ); ?>" value="<?php echo esc_attr( (string) $hwm_v ); ?>">
		<?php endforeach; ?>
		<div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
		<?php echo $args['fields'] ?? ''; // phpcs:ignore -- markup from our own content file. ?>
		<div class="field">
			<label for="<?php echo esc_attr( $hwm_id ); ?>-email">Email address</label>
			<input id="<?php echo esc_attr( $hwm_id ); ?>-email" type="email" name="email" required autocomplete="email" maxlength="190">
		</div>
		<div class="field field--check">
			<label><input type="checkbox" name="consent" value="1" required> Email me about this. I can unsubscribe with one click in any email. (<a href="<?php echo esc_url( hwm_url( 'privacy-policy' ) . '#email-lists' ); ?>">How we use your email</a>)</label>
		</div>
		<button class="btn" type="submit">Sign up</button>
		<p class="form-msg" role="status" aria-live="polite" data-form-msg></p>
	</form>
</section>
