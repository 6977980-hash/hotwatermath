<?php
/**
 * /embed/<tool>/ : the calculator alone, for other sites to iframe (CLAUDE.md §7b).
 * No nav, no ads, noindex, one brand-name credit link with rel="nofollow".
 * Variables: $hwm_embed_entry (registry entry).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_slug = (string) $hwm_embed_entry['slug'];
$hwm_out  = hwm_tool_content( $hwm_slug, $hwm_embed_entry );
?><!doctype html>
<html lang="<?php echo esc_attr( (string) hwm_site( 'language' ) ); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, follow">
<title><?php echo esc_html( $hwm_embed_entry['title'] . ' | ' . hwm_site( 'brand' ) ); ?></title>
<link rel="canonical" href="<?php echo esc_url( hwm_url( $hwm_slug ) ); ?>">
<?php hwm_integrations_head( false ); // GA4 + consent only, for the embed_view event; never ads. ?>
<link rel="stylesheet" href="<?php echo esc_url( hwm_asset( 'css/global.css' ) ); ?>">
</head>
<body class="embed-body">
<?php
// Links inside the frame open in a new tab and carry rel="nofollow" like the credit (CLAUDE.md §7b).
ob_start();
hwm_part( 'calculator', array( 'tool' => $hwm_slug, 'inputs' => $hwm_out['calculator_inputs'], 'label' => $hwm_embed_entry['title'] ) );
echo preg_replace( '/<a (?![^>]*\brel=)([^>]*)>/', '<a $1 target="_blank" rel="nofollow noopener">', (string) ob_get_clean() ); // phpcs:ignore -- our own escaped markup.
?>
<p class="embed-credit">Calculator by <a href="<?php echo esc_url( hwm_url( $hwm_slug ) ); ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html( (string) hwm_site( 'brand' ) ); ?></a></p>
<?php if ( ! empty( $hwm_embed_entry['scripts'] ) ) { echo hwm_importmap(); } // phpcs:ignore -- JSON of our own asset URLs. ?>
<?php foreach ( (array) ( $hwm_embed_entry['scripts'] ?? array() ) as $hwm_script ) : ?>
	<?php if ( preg_match( '/^[a-z0-9-]+$/', (string) $hwm_script ) ) : ?>
		<script type="module" src="<?php echo esc_url( hwm_asset( 'js/' . $hwm_script . '.js' ) ); ?>"></script>
	<?php endif; ?>
<?php endforeach; ?>
</body>
</html>
