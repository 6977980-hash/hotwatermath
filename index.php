<?php
/**
 * Fallback template. Every real URL is routed by inc/helpers.php (template_include);
 * anything that reaches this file is treated as not found.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require HWM_DIR . '/404.php';
