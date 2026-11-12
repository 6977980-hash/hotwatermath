<?php
/**
 * Registry → WordPress pages sync (CLAUDE.md §2 "Content").
 *
 * One WordPress Page per data/pages.json entry whose status is not "planned".
 * Runs on theme activation and whenever the deploy signature (theme version +
 * registry contents) changes. Pages are matched by the _hwm_slug meta key.
 * Nothing is ever deleted: an entry removed from the registry, or set back to
 * "planned", is moved to the WordPress trash only if it has "remove": true.
 * Otherwise its page stays but renders a 404 (no registry entry = not allowed).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hwm_deploy_signature(): string {
	$registry = HWM_DIR . '/data/pages.json';
	return HWM_VERSION . ':' . ( is_readable( $registry ) ? md5_file( $registry ) : 'none' );
}

add_action( 'after_switch_theme', 'hwm_sync_pages' );
add_action(
	'init',
	static function () {
		if ( get_option( 'hwm_deploy_signature' ) !== hwm_deploy_signature() ) {
			hwm_sync_pages();
		}
	},
	20
);

function hwm_sync_pages(): array {
	if ( get_transient( 'hwm_sync_lock' ) ) {
		return array();
	}
	set_transient( 'hwm_sync_lock', 1, 60 );
	hwm_ensure_permalinks();

	$log      = array();
	$existing = array();
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ), 'numberposts' => -1, 'meta_key' => '_hwm_slug' ) ) as $post ) {
		$existing[ (string) get_post_meta( $post->ID, '_hwm_slug', true ) ] = $post;
	}

	foreach ( hwm_pages() as $slug => $entry ) {
		$status = $entry['status'] ?? 'planned';
		if ( 'planned' === $status ) {
			if ( ! empty( $entry['remove'] ) && isset( $existing[ $slug ] ) ) {
				wp_trash_post( $existing[ $slug ]->ID );
				$log[] = "trashed {$slug}";
			}
			continue;
		}
		$postarr = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => (string) ( $entry['title'] ?? $slug ),
			'post_name'      => '' === $slug ? 'home' : $slug,
			'post_content'   => '',
			'post_parent'    => 0,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		);
		// The registry's datePublished is the truth for schema; the WordPress date only must not be in the future (WP would schedule the page).
		$published = ( $entry['datePublished'] ?? '' ) . ' 00:00:00';
		$now       = current_time( 'mysql' );
		$postarr['post_date']     = ( '' !== ( $entry['datePublished'] ?? '' ) && $published < $now ) ? $published : $now;
		$postarr['post_date_gmt'] = get_gmt_from_date( $postarr['post_date'] );
		$postarr['edit_date']     = true;
		if ( isset( $existing[ $slug ] ) ) {
			$post = $existing[ $slug ];
			if ( $post->post_title !== $postarr['post_title'] || $post->post_name !== $postarr['post_name'] || 'publish' !== $post->post_status || $post->post_date_gmt > current_time( 'mysql', true ) ) {
				$postarr['ID'] = $post->ID;
				wp_update_post( $postarr );
				$log[] = "updated {$slug}";
			}
			$id = $post->ID;
		} else {
			// A page with this path may already exist (made by hand); adopt it instead of creating a duplicate.
			$found = get_page_by_path( $postarr['post_name'], OBJECT, 'page' );
			if ( $found ) {
				$postarr['ID'] = $found->ID;
				$id            = wp_update_post( $postarr );
				$log[]         = "adopted {$slug}";
			} else {
				$id    = wp_insert_post( $postarr );
				$log[] = "created {$slug}";
			}
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_hwm_slug', $slug );
			}
		}
		if ( '' === $slug && $id && ! is_wp_error( $id ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $id );
			update_option( 'page_for_posts', 0 );
		}
	}

	// Default content: the Hello world post and Sample Page are never public.
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ), array( 'privacy-policy-2', 'page' ) ) as $default ) {
		$post = get_page_by_path( $default[0], OBJECT, $default[1] );
		if ( $post && ! metadata_exists( 'post', $post->ID, '_hwm_slug' ) && 'publish' === $post->post_status ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'draft' ) );
			$log[] = "unpublished default {$default[0]}";
		}
	}
	update_option( 'wp_page_for_privacy_policy', 0 );

	flush_rewrite_rules( false );
	update_option( 'hwm_deploy_signature', hwm_deploy_signature(), true );
	update_option( 'hwm_last_sync', array( 'time' => gmdate( 'c' ), 'log' => $log ), false );
	do_action( 'hwm_after_sync', $log );
	hwm_purge_cache();
	delete_transient( 'hwm_sync_lock' );
	return $log;
}

/** Purge LiteSpeed (and anything listening) after a deploy changed the theme. */
function hwm_purge_cache(): void {
	do_action( 'litespeed_purge_all' );
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}
