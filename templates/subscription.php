<?php
/**
 * /subscription/, /subscription/confirm/, /subscription/unsubscribe/ (inc/lists.php). noindex, never cached.
 * Confirm and unsubscribe need a button press (a POST), so mail scanners that open links do not act for the reader.
 * Variables: $hwm_msg { step, token, message }.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_step  = (string) $hwm_msg['step'];
$hwm_token = (string) $hwm_msg['token'];
$hwm_title = 'confirm' === $hwm_step ? 'Confirm your email' : ( 'unsubscribe' === $hwm_step ? 'Unsubscribe' : 'Email signup' );
?><!doctype html>
<html lang="<?php echo esc_attr( (string) hwm_site( 'language' ) ); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( $hwm_title . ' | ' . hwm_site( 'brand' ) ); ?></title>
<link rel="stylesheet" href="<?php echo esc_url( hwm_asset( 'css/global.css' ) ); ?>">
</head>
<body>
<header class="site-header"><div class="container site-header__inner">
	<a class="site-logo" href="<?php echo esc_url( hwm_url() ); ?>" aria-label="<?php echo esc_attr( (string) hwm_site( 'brand' ) ); ?> home"><?php echo file_get_contents( HWM_DIR . '/assets/brand/logo.svg' ); // phpcs:ignore -- our own static SVG. ?></a>
</div></header>
<main id="main" class="container" style="max-width:640px;padding-block:var(--space-6)">
	<h1><?php echo esc_html( $hwm_title ); ?></h1>
	<?php if ( 'confirm' === $hwm_step || 'unsubscribe' === $hwm_step ) : ?>
		<p><?php echo 'confirm' === $hwm_step ? 'Press the button to start your emails. You can stop them at any time.' : 'Press the button to stop these emails and remove your address from this list.'; ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo 'confirm' === $hwm_step ? 'hwm_confirm' : 'hwm_unsub'; ?>">
			<input type="hidden" name="t" value="<?php echo esc_attr( $hwm_token ); ?>">
			<button class="btn" type="submit"><?php echo 'confirm' === $hwm_step ? 'Confirm my email' : 'Unsubscribe'; ?></button>
		</form>
	<?php else : ?>
		<p><?php echo esc_html( '' !== $hwm_msg['message'] ? $hwm_msg['message'] : 'Nothing to show here.' ); ?></p>
	<?php endif; ?>
	<p><a href="<?php echo esc_url( hwm_url() ); ?>">Back to <?php echo esc_html( (string) hwm_site( 'brand' ) ); ?></a> · <a href="<?php echo esc_url( hwm_url( 'privacy-policy' ) ); ?>">Privacy policy</a></p>
</main>
</body>
</html>
