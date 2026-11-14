<?php
/**
 * Security headers (CLAUDE.md §6 "Technical"). No page may be framed except /embed/* (frameable by any site).
 * The only exception is the WordPress Customizer preview, which frames the front end from wp-admin on the
 * same origin: when is_customize_preview() is true the page keeps SAMEORIGIN / 'self'. Admin screens are not
 * touched here (is_admin()), so WordPress's own SAMEORIGIN header for wp-admin stays as it is.
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
		header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
	}
	if ( $embed ) {
		header_remove( 'X-Frame-Options' );
		header( "Content-Security-Policy: frame-ancestors *; base-uri 'self'; object-src 'none'" );
	} elseif ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( "Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'" );
	} else {
		header( 'X-Frame-Options: DENY' );
		header( "Content-Security-Policy: frame-ancestors 'none'; base-uri 'self'; object-src 'none'" );
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
