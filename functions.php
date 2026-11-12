<?php
/**
 * Hot Water Math theme bootstrap. All logic lives in inc/*.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HWM_DIR', __DIR__ );
define( 'HWM_URI', get_template_directory_uri() );
define( 'HWM_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );

foreach ( array( 'data', 'helpers', 'cleanup', 'pages-sync', 'seo', 'schema', 'routes', 'headers' ) as $hwm_inc ) {
	require_once HWM_DIR . '/inc/' . $hwm_inc . '.php';
}
unset( $hwm_inc );
