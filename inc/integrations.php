<?php
/**
 * Google integrations (CLAUDE.md §2 "Analytics", §7 "Monetization"), all switched by data/site.json → integrations:
 *   ga4Id          "G-XXXXXXX"            GA4, loaded after the page has finished loading.
 *   adsenseClient  "ca-pub-…" or "pub-…"  AdSense account meta tag + adsbygoogle.js loaded after page load; /ads.txt line.
 *   adsenseSlots   { A, B, C, S }         AdSense ad unit IDs. A slot's reserved box prints only when client AND its unit ID are set,
 *                                          so there are no empty holes while the account is under review (docs/DECISIONS.md).
 * Consent Mode v2 defaults are printed before any Google tag: denied in the EEA, UK and Switzerland until the visitor
 * answers Google's own consent message (AdSense "Privacy & messaging"), granted elsewhere. No third-party banner.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Validated GA4 measurement ID or ''. */
function hwm_ga4_id(): string {
	$id = strtoupper( trim( (string) hwm_site( 'integrations.ga4Id' ) ) );
	return preg_match( '/^G-[A-Z0-9]{4,20}$/', $id ) ? $id : '';
}

/** Validated AdSense client as "ca-pub-…" or ''. */
function hwm_adsense_client(): string {
	$pub = preg_replace( '/^ca-/', '', trim( (string) hwm_site( 'integrations.adsenseClient' ) ) );
	return preg_match( '/^pub-\d{10,20}$/', (string) $pub ) ? 'ca-' . $pub : '';
}

/** AdSense ad unit ID for a slot letter, or ''. */
function hwm_adsense_unit( string $slot ): string {
	$unit = trim( (string) hwm_site( 'integrations.adsenseSlots.' . strtoupper( $slot ) ) );
	return preg_match( '/^\d{6,20}$/', $unit ) ? $unit : '';
}

/** EEA + UK + Switzerland (ISO 3166-1 alpha-2), where consent is required before ad/analytics storage. */
function hwm_consent_regions(): array {
	return array( 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'IS', 'LI', 'NO', 'GB', 'CH' );
}

/**
 * Print the consent defaults, GA4 config and the deferred loader. $ads = false on /embed/* (no ads there).
 * Prints nothing at all when no integration is configured.
 */
function hwm_integrations_head( bool $ads = true ): void {
	$ga     = hwm_ga4_id();
	$client = $ads ? hwm_adsense_client() : '';
	if ( '' === $ga && '' === $client ) {
		return;
	}
	if ( $client ) {
		echo '<meta name="google-adsense-account" content="' . esc_attr( $client ) . "\">\n";
	}
	$denied  = array(
		'ad_storage'         => 'denied',
		'ad_user_data'       => 'denied',
		'ad_personalization' => 'denied',
		'analytics_storage'  => 'denied',
		'region'             => hwm_consent_regions(),
		'wait_for_update'    => 500,
	);
	$granted = array(
		'ad_storage'         => 'granted',
		'ad_user_data'       => 'granted',
		'ad_personalization' => 'granted',
		'analytics_storage'  => 'granted',
	);
	$srcs = array();
	if ( $ga ) {
		$srcs[] = array( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ga ), false );
	}
	if ( $client ) {
		$srcs[] = array( 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode( $client ), true );
	}
	$js  = 'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}';
	$js .= 'gtag("consent","default",' . wp_json_encode( $denied ) . ');';
	$js .= 'gtag("consent","default",' . wp_json_encode( $granted ) . ');';
	$js .= 'gtag("set","ads_data_redaction",true);';
	if ( $ga ) {
		$js .= 'gtag("js",new Date());gtag("config",' . wp_json_encode( $ga ) . ');';
	}
	// Deferred loader: Google scripts start only after the page has loaded, so they never compete with the LCP or the calculator.
	$js .= '(function(){var s=' . wp_json_encode( $srcs, JSON_UNESCAPED_SLASHES ) . ';function go(){s.forEach(function(x){var e=document.createElement("script");e.async=true;e.src=x[0];if(x[1])e.crossOrigin="anonymous";document.head.appendChild(e);});';
	$js .= 'document.querySelectorAll("ins.adsbygoogle").forEach(function(){(window.adsbygoogle=window.adsbygoogle||[]).push({});});}';
	$js .= 'function later(){("requestIdleCallback" in window)?requestIdleCallback(go,{timeout:3000}):setTimeout(go,1);}';
	$js .= 'if(document.readyState==="complete")later();else addEventListener("load",later);})();';
	echo '<script>' . $js . "</script>\n"; // phpcs:ignore -- built from validated IDs and JSON.
}

add_action(
	'wp_head',
	static function () {
		hwm_integrations_head( true );
	},
	0
);
