<?php
/**
 * Head output: title, description, canonical, robots, Open Graph, Twitter,
 * verification meta, icons. Sitemap and robots.txt filters. (CLAUDE.md §6)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Robots value for the current request. */
function hwm_robots(): string {
	$entry = hwm_current();
	if ( $entry && hwm_is_live( $entry ) ) {
		return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
	}
	return 'noindex, follow';
}

function hwm_title(): string {
	$brand = (string) hwm_site( 'brand' );
	$entry = hwm_current();
	if ( $entry ) {
		return (string) ( $entry['metaTitle'] ?? ( $entry['title'] . ' | ' . $brand ) );
	}
	if ( is_search() ) {
		return 'Search results | ' . $brand;
	}
	return 'Page not found | ' . $brand;
}

function hwm_description(): string {
	$entry = hwm_current();
	return $entry ? (string) ( $entry['description'] ?? '' ) : (string) hwm_site( 'description' );
}

function hwm_canonical(): string {
	$entry = hwm_current();
	return $entry ? hwm_url( (string) $entry['slug'] ) : '';
}

/** OG image for the current page: assets/og/<slug>.png (home = home.png; a child slug's "/" becomes "-"), else default.png. */
function hwm_og_image(): array {
	$entry = hwm_current();
	$name  = $entry ? ( '' === $entry['slug'] ? 'home' : str_replace( '/', '-', (string) $entry['slug'] ) ) : 'default';
	if ( ! is_readable( HWM_DIR . '/assets/og/' . $name . '.png' ) ) {
		$name = 'default';
	}
	return array(
		'url' => HWM_URI . '/assets/og/' . $name . '.png',
		'alt' => $entry ? ( $entry['h1'] ?? $entry['title'] ) . ' | ' . hwm_site( 'brand' ) : (string) hwm_site( 'brand' ),
	);
}

add_action(
	'wp_head',
	static function () {
		$entry       = hwm_current();
		$title       = hwm_title();
		$description = hwm_description();
		$canonical   = hwm_canonical();
		$og          = hwm_og_image();
		$brand       = (string) hwm_site( 'brand' );

		echo '<title>' . esc_html( $title ) . "</title>\n";
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . "\">\n";
		}
		echo '<meta name="robots" content="' . esc_attr( hwm_robots() ) . "\">\n";
		if ( $canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . "\">\n";
		}

		// Open Graph + Twitter.
		echo '<meta property="og:site_name" content="' . esc_attr( $brand ) . "\">\n";
		echo '<meta property="og:locale" content="en_US">' . "\n";
		echo '<meta property="og:type" content="' . ( $entry && 'article' === ( $entry['type'] ?? '' ) ? 'article' : 'website' ) . "\">\n";
		echo '<meta property="og:title" content="' . esc_attr( $entry['h1'] ?? $title ) . "\">\n";
		if ( $description ) {
			echo '<meta property="og:description" content="' . esc_attr( $description ) . "\">\n";
		}
		if ( $canonical ) {
			echo '<meta property="og:url" content="' . esc_url( $canonical ) . "\">\n";
		}
		echo '<meta property="og:image" content="' . esc_url( $og['url'] ) . "\">\n";
		echo '<meta property="og:image:width" content="1200">' . "\n";
		echo '<meta property="og:image:height" content="630">' . "\n";
		echo '<meta property="og:image:alt" content="' . esc_attr( $og['alt'] ) . "\">\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

		// Icons and manifest (stable URLs, CLAUDE.md §6 brand assets).
		// Hostinger answers /favicon.ico with a static 404 before WordPress runs, so the stable icon URL lives in the theme (docs/DECISIONS.md).
		echo '<link rel="icon" href="' . esc_url( HWM_URI . '/assets/brand/favicon.ico' ) . '" sizes="48x48">' . "\n";
		echo '<link rel="icon" href="' . esc_url( HWM_URI . '/assets/brand/icon.svg' ) . '" type="image/svg+xml">' . "\n";
		echo '<link rel="apple-touch-icon" href="' . esc_url( HWM_URI . '/assets/brand/apple-touch-icon.png' ) . '">' . "\n";
		echo '<link rel="manifest" href="/site.webmanifest">' . "\n";
		echo '<meta name="theme-color" content="' . esc_attr( (string) hwm_site( 'themeColor' ) ) . "\">\n";

		// Search engine verification (empty = off).
		$gsc  = (string) hwm_site( 'integrations.gscToken' );
		$bing = (string) hwm_site( 'integrations.bingToken' );
		if ( $gsc ) {
			echo '<meta name="google-site-verification" content="' . esc_attr( $gsc ) . "\">\n";
		}
		if ( $bing ) {
			echo '<meta name="msvalidate.01" content="' . esc_attr( $bing ) . "\">\n";
		}
	},
	1
);

// Search and 404 responses also say noindex in the header.
add_action(
	'template_redirect',
	static function () {
		if ( is_search() || is_404() ) {
			header( 'X-Robots-Tag: noindex, follow', true );
		}
	},
	20
);

/* ---------- Sitemap: core wp-sitemap.xml, live registry pages only ---------- */

add_filter(
	'wp_sitemaps_add_provider',
	static function ( $provider, $name ) {
		return 'posts' === $name ? $provider : false;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_post_types',
	static function ( $types ) {
		return array_intersect_key( $types, array( 'page' => true ) );
	}
);
add_filter(
	'wp_sitemaps_posts_query_args',
	static function ( $args ) {
		$ids = array( 0 );
		foreach ( get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_hwm_slug' ) ) as $id ) {
			$entry = hwm_page( (string) get_post_meta( $id, '_hwm_slug', true ) );
			if ( hwm_is_live( $entry ) ) {
				$ids[] = $id;
			}
		}
		$args['post__in'] = $ids;
		return $args;
	}
);
add_filter(
	'wp_sitemaps_posts_entry',
	static function ( $sitemap_entry, $post ) {
		$entry = hwm_page( (string) get_post_meta( $post->ID, '_hwm_slug', true ) );
		if ( $entry ) {
			$sitemap_entry['loc'] = hwm_url( (string) $entry['slug'] );
			if ( ! empty( $entry['dateModified'] ) ) {
				$sitemap_entry['lastmod'] = $entry['dateModified'];
			}
		}
		return $sitemap_entry;
	},
	10,
	2
);
// The front page entry is added by core separately for "page on front"; keep it only when home is live.
add_filter(
	'wp_sitemaps_posts_show_on_front_entry',
	static function ( $sitemap_entry ) {
		$home = hwm_page( '' );
		if ( ! hwm_is_live( $home ) ) {
			return array();
		}
		$sitemap_entry['loc'] = hwm_url();
		if ( ! empty( $home['dateModified'] ) ) {
			$sitemap_entry['lastmod'] = $home['dateModified'];
		}
		return $sitemap_entry;
	}
);
add_filter( 'wp_sitemaps_max_urls', static fn() => 2000 );

/* ---------- robots.txt ---------- */

add_filter(
	'robots_txt',
	static function ( $output, $public ) {
		if ( ! $public ) {
			return $output; // "Discourage search engines" is on; leave core's Disallow in place.
		}
		$lines   = array( 'User-agent: *', 'Disallow: /wp-admin/', 'Allow: /wp-admin/admin-ajax.php', 'Disallow: /?s=', 'Disallow: /search/', '', 'Sitemap: ' . hwm_origin() . '/wp-sitemap.xml' );
		return implode( "\n", $lines ) . "\n";
	},
	99,
	2
);
