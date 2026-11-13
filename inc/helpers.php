<?php
/**
 * Registry lookups, URLs and template helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** All registry entries keyed by slug ("" is the home page). */
function hwm_pages(): array {
	static $pages = null;
	if ( null === $pages ) {
		$pages = array();
		foreach ( (array) hwm_raw( 'pages', 'pages' ) as $entry ) {
			if ( is_array( $entry ) && isset( $entry['slug'] ) ) {
				$pages[ (string) $entry['slug'] ] = $entry;
			}
		}
	}
	return $pages;
}

function hwm_page( string $slug ): ?array {
	$pages = hwm_pages();
	return $pages[ $slug ] ?? null;
}

/** Registry entries of one type with one of the given statuses. */
function hwm_pages_of( string $type, array $statuses = array( 'live' ) ): array {
	return array_values(
		array_filter(
			hwm_pages(),
			static function ( $entry ) use ( $type, $statuses ) {
				return ( $entry['type'] ?? '' ) === $type && in_array( $entry['status'] ?? '', $statuses, true );
			}
		)
	);
}

/** Canonical origin from data/site.json. On other hosts (local, staging) the current home URL is used. */
function hwm_origin(): string {
	$origin = (string) hwm_site( 'origin' );
	$home   = untrailingslashit( home_url() );
	return ( '' !== $origin && wp_parse_url( $origin, PHP_URL_HOST ) === wp_parse_url( $home, PHP_URL_HOST ) ) ? $origin : $home;
}

/** Absolute URL of a registry slug, always with a trailing slash. */
function hwm_url( string $slug = '' ): string {
	$slug = trim( $slug, '/' );
	return hwm_origin() . '/' . ( '' === $slug ? '' : $slug . '/' );
}

function hwm_asset( string $path ): string {
	$file = HWM_DIR . '/assets/' . $path;
	$ver  = is_readable( $file ) ? substr( md5( (string) filemtime( $file ) . filesize( $file ) ), 0, 8 ) : HWM_VERSION;
	return HWM_URI . '/assets/' . $path . '?v=' . $ver;
}

/** Registry slug for a WordPress page object. */
function hwm_slug_for_post( $post ): ?string {
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return null;
	}
	$stored = get_post_meta( $post->ID, '_hwm_slug', true );
	if ( is_string( $stored ) && metadata_exists( 'post', $post->ID, '_hwm_slug' ) ) {
		return $stored;
	}
	return null;
}

/** Registry entry of the page being viewed, or null. */
function hwm_current(): ?array {
	static $resolved = false, $entry = null;
	if ( ! did_action( 'wp' ) ) {
		return null;
	}
	if ( ! $resolved ) {
		$resolved = true;
		if ( is_page() || is_front_page() ) {
			$slug = hwm_slug_for_post( get_queried_object() );
			if ( null !== $slug ) {
				$found = hwm_page( $slug );
				if ( $found && 'planned' !== ( $found['status'] ?? 'planned' ) ) {
					$entry = $found;
				}
			}
		}
	}
	return $entry;
}

function hwm_is_live( ?array $entry ): bool {
	return $entry && 'live' === ( $entry['status'] ?? '' );
}

/** Include a template part from parts/ with named arguments. */
function hwm_part( string $name, array $args = array() ): void {
	$file = HWM_DIR . '/parts/' . $name . '.php';
	if ( preg_match( '/^[a-z0-9-]+$/', $name ) && is_readable( $file ) ) {
		include $file;
	}
}

/** Shortcut used inside content files: hwm_ad( 'B' ). */
function hwm_ad( string $slot ): void {
	hwm_part( 'ad-slot', array( 'slot' => $slot ) );
}

/**
 * Run a content file and collect what it produced.
 * The file echoes its body HTML and may set $faq, $sources, $next_steps, $related
 * and (tools only) $calculator_inputs.
 *
 * @return array{html:string,faq:array,sources:array,next_steps:array,related:array,calculator_inputs:string}
 */
function hwm_content( string $name, array $entry = array() ): array {
	$out  = array(
		'html'       => '',
		'faq'        => array(),
		'sources'    => array(),
		'next_steps' => array(),
		'related'    => array(),
		'calculator_inputs' => '',
	);
	$file = HWM_DIR . '/content/' . $name . '.php';
	if ( ! preg_match( '/^[a-z0-9-]+$/', $name ) || ! is_readable( $file ) ) {
		return $out;
	}
	$faq        = array();
	$sources    = array();
	$next_steps = array();
	$related    = array();
	$calculator_inputs = '';
	ob_start();
	include $file;
	$out['html'] = (string) ob_get_clean();
	foreach ( array( 'faq', 'sources', 'next_steps', 'related' ) as $key ) {
		$out[ $key ] = is_array( $$key ) ? $$key : array();
	}
	$out['calculator_inputs'] = is_string( $calculator_inputs ) ? $calculator_inputs : '';
	return $out;
}

/** "2026-11-13" → "November 13, 2026". Returns "" for empty or invalid dates. */
function hwm_date( ?string $iso ): string {
	if ( ! $iso ) {
		return '';
	}
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $iso, new DateTimeZone( 'UTC' ) );
	return $date ? $date->format( 'F j, Y' ) : '';
}

/** Breadcrumb trail for an entry: array of [ name, url ]. */
function hwm_trail( array $entry ): array {
	$trail = array( array( 'Home', hwm_url() ) );
	$type  = $entry['type'] ?? '';
	if ( 'home' === $type ) {
		return $trail;
	}
	if ( 'tool' === $type && ( $hub = hwm_page( 'calculators' ) ) ) {
		$trail[] = array( 'Calculators', hwm_url( 'calculators' ) );
	} elseif ( 'article' === $type && ( $hub = hwm_page( 'guides' ) ) ) {
		$trail[] = array( 'Guides', hwm_url( 'guides' ) );
	}
	if ( ! empty( $entry['parent'] ) && ( $parent = hwm_page( (string) $entry['parent'] ) ) ) {
		$trail[] = array( $parent['title'], hwm_url( $parent['slug'] ) );
	}
	$trail[] = array( $entry['title'], hwm_url( $entry['slug'] ) );
	return $trail;
}

/** Primary navigation (≤7 links, CLAUDE.md §4). */
function hwm_nav(): array {
	return array(
		array( 'Calculators', 'calculators' ),
		array( 'Guides', 'guides' ),
		array( 'Methodology', 'methodology' ),
		array( 'About', 'about' ),
		array( 'Contact', 'contact' ),
	);
}

function hwm_footer_nav(): array {
	return array(
		array( 'About', 'about' ),
		array( 'Contact', 'contact' ),
		array( 'Methodology', 'methodology' ),
		array( 'Editorial policy', 'editorial-policy' ),
		array( 'Privacy policy', 'privacy-policy' ),
		array( 'Terms of use', 'terms' ),
		array( 'Affiliate disclosure', 'affiliate-disclosure' ),
	);
}

/** Simple registry search used by search.php and 404.php (page bodies are not stored in the database). */
function hwm_search( string $query ): array {
	$terms = array_filter( preg_split( '/\s+/', strtolower( trim( $query ) ) ) );
	if ( ! $terms ) {
		return array();
	}
	$scored = array();
	foreach ( hwm_pages() as $entry ) {
		if ( 'planned' === ( $entry['status'] ?? 'planned' ) ) {
			continue;
		}
		$haystack = strtolower( implode( ' ', array( $entry['title'] ?? '', $entry['h1'] ?? '', $entry['description'] ?? '', $entry['quickAnswer'] ?? '', implode( ' ', (array) ( $entry['keywords'] ?? array() ) ) ) ) );
		$title    = strtolower( $entry['title'] ?? '' );
		$score    = 0;
		foreach ( $terms as $term ) {
			if ( str_contains( $title, $term ) ) {
				$score += 3;
			} elseif ( str_contains( $haystack, $term ) ) {
				++$score;
			}
		}
		if ( $score > 0 ) {
			$scored[] = array( $score, $entry );
		}
	}
	usort( $scored, static fn( $a, $b ) => $b[0] <=> $a[0] );
	return array_column( $scored, 1 );
}

/* ---------- Assets ---------- */

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'hwm-global', hwm_asset( 'css/global.css' ), array(), null );
		$entry = hwm_current();
		foreach ( (array) ( $entry['scripts'] ?? array() ) as $script ) {
			if ( preg_match( '/^[a-z0-9-]+$/', (string) $script ) && is_readable( HWM_DIR . '/assets/js/' . $script . '.js' ) ) {
				wp_enqueue_script_module( 'hwm-' . $script, hwm_asset( 'js/' . $script . '.js' ), array(), null );
			}
		}
	}
);

/**
 * Import map that adds the content version to the shared modules (formulas.js, ui.js).
 * Tool modules import them by relative path, which carries no ?v=, so without this a CDN or
 * browser can keep an old formulas.js for up to the cache lifetime after a deploy.
 */
function hwm_importmap(): string {
	$map = array();
	foreach ( array( 'formulas', 'ui', 'cost-model' ) as $hwm_m ) {
		$map[ HWM_URI . '/assets/js/' . $hwm_m . '.js' ] = hwm_asset( 'js/' . $hwm_m . '.js' );
	}
	return '<script type="importmap">' . wp_json_encode( array( 'imports' => $map ), JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

add_action(
	'wp_head',
	static function () {
		$entry = hwm_current();
		if ( ! empty( $entry['scripts'] ) ) {
			echo hwm_importmap(); // phpcs:ignore -- JSON of our own asset URLs.
		}
	},
	1
);

/** Drafts are shown to visitors (noindex) so the owner can review them; links to drafts only appear for logged-in editors. */
function hwm_is_draft_preview(): bool {
	return is_user_logged_in() && current_user_can( 'edit_pages' );
}

/** Embed snippet: brand-name credit, rel="nofollow", removable, one link (CLAUDE.md §7b). */
function hwm_embed_snippet( string $tool ): string {
	$entry = hwm_page( $tool );
	$src   = hwm_origin() . '/embed/' . $tool . '/';
	$title = (string) ( $entry['title'] ?? 'Calculator' );
	return '<iframe src="' . $src . '" title="' . esc_attr( $title ) . '" width="100%" height="640" style="border:0;max-width:720px" loading="lazy"></iframe>' . "\n"
		. '<p style="font-size:13px">Calculator by <a href="' . hwm_url() . '" rel="nofollow">' . esc_html( (string) hwm_site( 'brand' ) ) . '</a></p>';
}

/* ---------- Template routing: the registry type picks the template ---------- */

add_filter(
	'template_include',
	static function ( $template ) {
		$entry = hwm_current();
		if ( ! $entry ) {
			return $template;
		}
		$map  = array(
			'tool'    => 'templates/tool.php',
			'article' => 'templates/article.php',
			'hub'     => 'templates/hub.php',
		);
		$file = $map[ $entry['type'] ?? '' ] ?? 'page.php';
		return HWM_DIR . '/' . $file;
	},
	99
);

/** Tool content: alias of hwm_content(); tool content files also set $calculator_inputs. */
function hwm_tool_content( string $name, array $entry = array() ): array {
	return hwm_content( $name, $entry );
}

/**
 * Internal link to a registry page by slug, only when the visitor can open it (live, or draft for logged-in editors).
 * Otherwise returns the anchor text alone, so drafts and planned pages are never linked for visitors.
 */
function hwm_link( string $slug, string $anchor = '' ): string {
	$entry = hwm_page( $slug );
	$text  = '' !== $anchor ? $anchor : (string) ( $entry['title'] ?? $slug );
	if ( $entry && ( hwm_is_live( $entry ) || ( 'draft' === ( $entry['status'] ?? '' ) && hwm_is_draft_preview() ) ) ) {
		return '<a href="' . esc_url( hwm_url( $slug ) ) . '">' . esc_html( $text ) . '</a>';
	}
	return esc_html( $text );
}

/** Print a computed tool's client data as inert JSON for its calculator module. */
function hwm_tool_data_script( string $id, string $file ): string {
	$client = hwm_raw( $file, 'client' );
	return '<script type="application/json" id="' . esc_attr( $id ) . '">' . wp_json_encode( $client, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>';
}
