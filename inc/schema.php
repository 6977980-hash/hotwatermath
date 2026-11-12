<?php
/**
 * JSON-LD helpers (CLAUDE.md §6 "Structured data"). One <script> per type.
 * Every value comes from data/site.json or data/pages.json.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hwm_jsonld( array $data ): void {
	$data = array( '@context' => 'https://schema.org' ) + $data;
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}

function hwm_schema_ids(): array {
	$origin = hwm_origin();
	return array(
		'org'     => $origin . '/#organization',
		'site'    => $origin . '/#website',
		'founder' => $origin . '/about/#ali-ahmad',
	);
}

/** Person node for the founder. */
function hwm_schema_person( bool $full = false ): array {
	$ids    = hwm_schema_ids();
	$person = array(
		'@type' => 'Person',
		'@id'   => $ids['founder'],
		'name'  => (string) hwm_site( 'author.name' ),
		'url'   => hwm_url( 'about' ),
	);
	if ( $full ) {
		$person['jobTitle']    = (string) hwm_site( 'author.jobTitle' );
		$person['description'] = (string) hwm_site( 'author.description' );
		$person['sameAs']      = array_values( (array) hwm_site( 'author.sameAs' ) );
		$person['worksFor']    = array( '@id' => $ids['org'] );
	}
	return $person;
}

function hwm_schema_organization(): array {
	$ids = hwm_schema_ids();
	$org = array(
		'@type'       => 'Organization',
		'@id'         => $ids['org'],
		'name'        => (string) hwm_site( 'brand' ),
		'url'         => hwm_url(),
		'description' => (string) hwm_site( 'description' ),
		'email'       => (string) hwm_site( 'contact.email' ),
		'logo'        => array(
			'@type'  => 'ImageObject',
			'url'    => HWM_URI . '/assets/brand/logo-512.png',
			'width'  => 512,
			'height' => 512,
		),
		'founder'     => hwm_schema_person( true ),
	);
	$same = array_values( (array) hwm_site( 'social' ) );
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	return $org;
}

function hwm_schema_breadcrumb( array $entry ): array {
	$items = array();
	foreach ( hwm_trail( $entry ) as $i => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb[0],
			'item'     => $crumb[1],
		);
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => hwm_url( (string) $entry['slug'] ) . '#breadcrumb',
		'itemListElement' => $items,
	);
}

function hwm_schema_webpage( array $entry ): array {
	$ids  = hwm_schema_ids();
	$url  = hwm_url( (string) $entry['slug'] );
	$type = array(
		'hub'   => 'CollectionPage',
		'about' => 'AboutPage',
	);
	$page = array(
		'@type'       => $type[ $entry['type'] ] ?? ( 'about' === $entry['slug'] ? 'AboutPage' : ( 'contact' === $entry['slug'] ? 'ContactPage' : 'WebPage' ) ),
		'@id'         => $url . '#webpage',
		'url'         => $url,
		'name'        => (string) ( $entry['h1'] ?? $entry['title'] ),
		'description' => (string) ( $entry['description'] ?? '' ),
		'inLanguage'  => (string) hwm_site( 'language' ),
		'isPartOf'    => array( '@id' => $ids['site'] ),
		'breadcrumb'  => array( '@id' => $url . '#breadcrumb' ),
		'publisher'   => array( '@id' => $ids['org'] ),
	);
	if ( ! empty( $entry['datePublished'] ) ) {
		$page['datePublished'] = $entry['datePublished'];
	}
	if ( ! empty( $entry['dateModified'] ) ) {
		$page['dateModified'] = $entry['dateModified'];
	}
	if ( 'about' === $entry['slug'] ) {
		$page['mainEntity'] = array( '@id' => $ids['founder'] );
	}
	return $page;
}

function hwm_schema_website(): array {
	$ids = hwm_schema_ids();
	return array(
		'@type'       => 'WebSite',
		'@id'         => $ids['site'],
		'url'         => hwm_url(),
		'name'        => (string) hwm_site( 'brand' ),
		'description' => (string) hwm_site( 'description' ),
		'inLanguage'  => (string) hwm_site( 'language' ),
		'publisher'   => array( '@id' => $ids['org'] ),
	);
}

/** Tool pages: WebApplication, never with ratings (CLAUDE.md §6). */
function hwm_schema_webapp( array $entry ): array {
	$ids = hwm_schema_ids();
	$url = hwm_url( (string) $entry['slug'] );
	return array(
		'@type'               => 'WebApplication',
		'@id'                 => $url . '#app',
		'name'                => (string) $entry['title'],
		'url'                 => $url,
		'description'         => (string) ( $entry['description'] ?? '' ),
		'applicationCategory' => 'UtilitiesApplication',
		'operatingSystem'     => 'Web',
		'isAccessibleForFree' => true,
		'offers'              => array(
			'@type'         => 'Offer',
			'price'         => 0,
			'priceCurrency' => 'USD',
		),
		'author'              => array( '@id' => $ids['founder'] ),
		'publisher'           => array( '@id' => $ids['org'] ),
	);
}

/** Articles: author Person + publisher. No reviewedBy, ever. */
function hwm_schema_article( array $entry ): array {
	$ids = hwm_schema_ids();
	$url = hwm_url( (string) $entry['slug'] );
	$og  = hwm_og_image();
	return array(
		'@type'            => 'Article',
		'@id'              => $url . '#article',
		'headline'         => (string) ( $entry['h1'] ?? $entry['title'] ),
		'description'      => (string) ( $entry['description'] ?? '' ),
		'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
		'datePublished'    => (string) ( $entry['datePublished'] ?? '' ),
		'dateModified'     => (string) ( $entry['dateModified'] ?? '' ),
		'author'           => hwm_schema_person(),
		'publisher'        => array( '@id' => $ids['org'] ),
		'image'            => $og['url'],
		'inLanguage'       => (string) hwm_site( 'language' ),
	);
}

/** FAQPage from [ [ 'q' => ..., 'a' => ... ] ]. Answers are plain text or simple HTML. */
function hwm_schema_faq( array $faq, string $url ): array {
	$items = array();
	foreach ( $faq as $item ) {
		$items[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( (string) $item['q'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => trim( wp_strip_all_tags( (string) $item['a'] ) ),
			),
		);
	}
	return array(
		'@type'      => 'FAQPage',
		'@id'        => $url . '#faq',
		'mainEntity' => $items,
	);
}

/** Page-level schema printed in <head>. FAQPage is printed by parts/faq.php next to the questions. */
add_action(
	'wp_head',
	static function () {
		$entry = hwm_current();
		if ( ! $entry ) {
			return;
		}
		$type = $entry['type'] ?? '';
		if ( 'home' === $type ) {
			hwm_jsonld( hwm_schema_website() );
			hwm_jsonld( hwm_schema_organization() );
		}
		hwm_jsonld( hwm_schema_webpage( $entry ) );
		hwm_jsonld( hwm_schema_breadcrumb( $entry ) );
		if ( 'about' === $entry['slug'] ) {
			hwm_jsonld( hwm_schema_person( true ) );
		}
		if ( 'tool' === $type ) {
			hwm_jsonld( hwm_schema_webapp( $entry ) );
		}
		if ( 'article' === $type ) {
			hwm_jsonld( hwm_schema_article( $entry ) );
		}
	},
	5
);
