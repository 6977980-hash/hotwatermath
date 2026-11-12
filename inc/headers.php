<?php
/**
 * Security headers (CLAUDE.md §6 "Technical"). Other sites may not frame any page except /embed/*. Same-origin framing stays allowed so the WordPress Customizer preview works.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hwm_security_headers( bool $embed = false ): void {
	if ( headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()' );
	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
	if ( $embed ) {
		header_remove( 'X-Frame-Options' );
		header( "Content-Security-Policy: frame-ancestors *; base-uri 'self'; object-src 'none'" );
	} else {
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( "Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'" );
	}
}

add_action(
	'send_headers',
	static function () {
		if ( is_admin() ) {
			return;
		}
		hwm_security_headers( false );
	}
);
