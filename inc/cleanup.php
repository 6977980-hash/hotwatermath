<?php
/**
 * WordPress clean-up (CLAUDE.md §2): no emoji, oEmbed, block CSS, generator,
 * jQuery on the front end, comments, archives, attachment pages or default content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'html5', array( 'search-form', 'script', 'style', 'navigation-widgets' ) );
		remove_theme_support( 'core-block-patterns' );
		remove_theme_support( 'widgets-block-editor' );
	}
);

// Head clutter.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
remove_action( 'wp_head', 'rel_canonical' );
remove_action( 'wp_head', 'wp_robots', 1 );
remove_action( 'wp_head', '_wp_render_title_tag', 1 );
remove_action( 'wp_head', 'wp_site_icon', 99 );
remove_action( 'wp_head', 'wp_resource_hints', 2 );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
add_filter( 'the_generator', '__return_empty_string' );
if ( function_exists( 'wp_print_auto_sizes_contain_css_fix' ) ) {
	remove_action( 'wp_head', 'wp_print_auto_sizes_contain_css_fix', 1 );
}
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
add_filter( 'should_load_separate_core_block_assets', '__return_false' );
add_filter( 'wp_speculation_rules_configuration', '__return_null' );

// Block library, global styles, jQuery: none on the front end.
add_action(
	'wp_enqueue_scripts',
	static function () {
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports', 'core-block-supports-duotone' ) as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
		if ( ! is_admin() && ! is_user_logged_in() ) {
			wp_dequeue_script( 'jquery' );
			wp_deregister_script( 'jquery' );
			wp_deregister_script( 'jquery-migrate' );
		}
	},
	100
);
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
add_filter( 'wp_default_scripts', static function ( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
} );

// oEmbed endpoints and the embed script.
add_action(
	'init',
	static function () {
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		wp_deregister_script( 'wp-embed' );
	},
	9999
);
add_filter( 'embed_oembed_discover', '__return_false' );

// Comments: off everywhere.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_action(
	'init',
	static function () {
		foreach ( array( 'post', 'page', 'attachment' ) as $type ) {
			remove_post_type_support( $type, 'comments' );
			remove_post_type_support( $type, 'trackbacks' );
		}
	},
	100
);
add_filter( 'xmlrpc_enabled', '__return_false' );
// xmlrpc_enabled=false only turns off authenticated methods; pingbacks stay unless removed here (audit F-23).
add_filter( 'xmlrpc_methods', static function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );
add_filter( 'wp_headers', static function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

// No attachment pages.
add_filter( 'pre_option_wp_attachment_pages_enabled', static fn() => '0' );

/**
 * Anything that is not a registry page, the home page, search, or a theme route is a 404:
 * posts (Hello world), sample page, author/date/tag/category/attachment archives, feeds, ?p=.
 */
add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || is_search() || is_404() || is_robots() || is_favicon() || '' !== (string) get_query_var( 'sitemap' ) || '' !== (string) get_query_var( 'sitemap-stylesheet' ) ) {
			return;
		}
		$allowed = false;
		if ( is_page() || is_front_page() ) {
			$allowed = null !== hwm_current();
		}
		if ( is_feed() || is_author() || is_date() || is_tag() || is_category() || is_tax() || is_attachment() || is_single() || is_archive() || ( is_home() && ! is_front_page() ) || is_comment_feed() || is_trackback() ) {
			$allowed = false;
		}
		if ( ! $allowed ) {
			hwm_force_404();
		}
	},
	1
);

function hwm_force_404(): void {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}

// Author enumeration via ?author=N redirects is closed too.
add_filter( 'redirect_canonical', static function ( $redirect, $requested ) {
	if ( preg_match( '/[?&]author=\d/i', (string) $requested ) ) {
		return false;
	}
	return $redirect;
}, 10, 2 );

// REST users endpoint off for visitors (no author names in the API).
add_filter( 'rest_endpoints', static function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );

// Permalinks must be /%postname%/ (set once on activation, re-checked in the sync routine).
function hwm_ensure_permalinks(): void {
	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules( false );
	}
}
add_action( 'after_switch_theme', 'hwm_ensure_permalinks' );

// No "did you mean" redirects for unknown URLs: an unknown URL is a plain 404.
add_filter( 'do_redirect_guess_404_permalink', '__return_false' );
