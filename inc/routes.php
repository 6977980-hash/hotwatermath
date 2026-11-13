<?php
/**
 * Virtual root files and theme routes (CLAUDE.md §6 "Root files"):
 * /ads.txt, /llms.txt, /<indexnow-key>.txt, /favicon.ico, /site.webmanifest, /embed/<tool>/.
 * A physical file with the same name in public_html wins, so never create one by hand.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Request path without query string, e.g. "/ads.txt". */
function hwm_request_path(): string {
	$uri  = (string) ( $_SERVER['REQUEST_URI'] ?? '/' );
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url(), PHP_URL_PATH );
	if ( $home && str_starts_with( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	return '/' . ltrim( $path, '/' );
}

function hwm_send_text( string $body, string $type = 'text/plain' ): void {
	status_header( 200 );
	header( 'Content-Type: ' . $type . '; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	header( 'X-Content-Type-Options: nosniff' );
	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput -- plain text responses.
	exit;
}

add_action(
	'parse_request',
	static function () {
		$path = hwm_request_path();

		if ( '/ads.txt' === $path ) {
			$pub  = preg_replace( '/^ca-/', '', hwm_adsense_client() );
			$body = '' !== $pub ? "google.com, {$pub}, DIRECT, f08c47fec0942fa0\n" : "# No ad network is configured yet.\n";
			hwm_send_text( $body );
		}

		if ( '/llms.txt' === $path ) {
			hwm_send_text( hwm_llms_txt(), 'text/plain' );
		}

		$key = (string) hwm_site( 'integrations.indexNowKey' );
		if ( $key && preg_match( '/^[a-zA-Z0-9-]{8,128}$/', $key ) && '/' . $key . '.txt' === $path ) {
			hwm_send_text( $key );
		}

		if ( '/favicon.ico' === $path ) {
			$file = HWM_DIR . '/assets/brand/favicon.ico';
			status_header( 200 );
			header( 'Content-Type: image/x-icon' );
			header( 'Cache-Control: public, max-age=604800' );
			readfile( $file );
			exit;
		}

		if ( '/site.webmanifest' === $path ) {
			$manifest = array(
				'name'             => (string) hwm_site( 'brand' ),
				'short_name'       => (string) hwm_site( 'brand' ),
				'start_url'        => '/',
				'display'          => 'browser',
				'background_color' => '#ffffff',
				'theme_color'      => (string) hwm_site( 'themeColor' ),
				'icons'            => array(
					array( 'src' => HWM_URI . '/assets/brand/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png' ),
					array( 'src' => HWM_URI . '/assets/brand/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png' ),
				),
			);
			hwm_send_text( (string) wp_json_encode( $manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ), 'application/manifest+json' );
		}

		if ( preg_match( '#^/embed/([a-z0-9-]+)/?$#', $path, $m ) ) {
			hwm_render_embed( $m[1] );
		}
	},
	0
);

/** /embed/<tool>/ : compact calculator, no nav, no ads, noindex. Only tools whose registry entry exists and is not planned. */
function hwm_render_embed( string $tool ): void {
	$entry = hwm_page( $tool );
	if ( ! $entry || 'tool' !== ( $entry['type'] ?? '' ) || 'planned' === ( $entry['status'] ?? 'planned' ) || ! is_readable( HWM_DIR . '/content/' . $tool . '.php' ) ) {
		return; // Falls through to WordPress, which answers 404.
	}
	if ( '/' !== substr( hwm_request_path(), -1 ) ) {
		wp_safe_redirect( hwm_origin() . '/embed/' . $tool . '/', 301 );
		exit;
	}
	status_header( 200 );
	header( 'X-Robots-Tag: noindex, follow' );
	hwm_security_headers( true );
	$hwm_embed_entry = $entry;
	include HWM_DIR . '/templates/embed.php';
	exit;
}

/** /llms.txt generated from the registry (optional, low priority per CLAUDE.md §6). */
function hwm_llms_txt(): string {
	$lines   = array( '# ' . hwm_site( 'brand' ), '', '> ' . hwm_site( 'description' ), '' );
	$lines[] = 'Every calculator shows its formula, and every default value names its source. Formulas and sources: ' . hwm_url( 'methodology' );
	$lines[] = '';
	foreach ( array( 'tool' => 'Calculators', 'article' => 'Guides' ) as $type => $label ) {
		$items = hwm_pages_of( $type, array( 'live' ) );
		if ( ! $items ) {
			continue;
		}
		$lines[] = '## ' . $label;
		foreach ( $items as $entry ) {
			$lines[] = '- [' . $entry['title'] . '](' . hwm_url( $entry['slug'] ) . '): ' . ( $entry['summary'] ?? $entry['description'] ?? '' );
		}
		$lines[] = '';
	}
	$lines[] = '## About';
	foreach ( array( 'about', 'methodology', 'editorial-policy', 'contact' ) as $slug ) {
		$entry = hwm_page( $slug );
		if ( hwm_is_live( $entry ) ) {
			$lines[] = '- [' . $entry['title'] . '](' . hwm_url( $slug ) . ')';
		}
	}
	return implode( "\n", $lines ) . "\n";
}

/* ---------- IndexNow: ping when a page goes live or its dateModified changes ---------- */

add_action(
	'hwm_after_sync',
	static function () {
		$current = array();
		foreach ( hwm_pages() as $slug => $entry ) {
			if ( hwm_is_live( $entry ) ) {
				$current[ $slug ] = (string) ( $entry['dateModified'] ?? '' );
			}
		}
		$previous = (array) get_option( 'hwm_indexnow_state', array() );
		$changed  = array();
		foreach ( $current as $slug => $modified ) {
			if ( ! array_key_exists( $slug, $previous ) || $previous[ $slug ] !== $modified ) {
				$changed[] = hwm_url( (string) $slug );
			}
		}
		$key = (string) hwm_site( 'integrations.indexNowKey' );
		if ( $changed && $key && wp_parse_url( hwm_origin(), PHP_URL_HOST ) === hwm_site( 'domain' ) ) {
			$response = wp_remote_post(
				'https://api.indexnow.org/indexnow',
				array(
					'timeout'  => 5,
					'blocking' => true,
					'headers'  => array( 'Content-Type' => 'application/json; charset=utf-8' ),
					'body'     => wp_json_encode(
						array(
							'host'        => hwm_site( 'domain' ),
							'key'         => $key,
							'keyLocation' => hwm_origin() . '/' . $key . '.txt',
							'urlList'     => array_slice( $changed, 0, 10000 ),
						)
					),
				)
			);
			update_option( 'hwm_indexnow_last', array( 'time' => gmdate( 'c' ), 'urls' => $changed, 'code' => is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_response_code( $response ) ), false );
		}
		// Only remember state once a key exists, so pages live before the key are pinged when it is added.
		if ( $key ) {
			update_option( 'hwm_indexnow_state', $current, false );
		}
	}
);
