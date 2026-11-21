<?php
/**
 * Email lists and the failure database (added 2026-11-21; docs/DECISIONS.md):
 *  - recall alerts (list "recall"): a confirmed subscriber gets one email when a recall that touches their brand
 *    (or any recall, if no brand) is added to data/recalls.json;
 *  - Heater Health Reminder (list "health"): emails when a maintenance task in data/maintenance.json comes due;
 *  - failure reports (table hwm_failures), shown only in aggregate once data/failure-database.json publishAt is met.
 *
 * Privacy: double opt-in, one-click unsubscribe, no IP addresses stored (rate limits use a salted hash held in a
 * transient for an hour), no third-party service. Mail goes through wp_mail(); when HWM_SMTP_HOST, HWM_SMTP_USER and
 * HWM_SMTP_PASS are defined in wp-config.php (never in this repo), it is sent through that SMTP mailbox.
 * Emails carry no ads or affiliate links (they stay informational).
 *
 * Date arithmetic for reminders uses the same intervals and month rule as assets/js/formulas.js addMonths() /
 * maintenanceSchedule(), counted from the install date (tests/lists-php.test.mjs checks the series match).
 * No other math happens here (docs/DECISIONS.md 2026-11-21).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const HWM_DB_VERSION = '1';

/* ---------- tables ---------- */

function hwm_table_name( string $name ): string {
	global $wpdb;
	return $wpdb->prefix . 'hwm_' . $name;
}

function hwm_db_install(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$c = $wpdb->get_charset_collate();
	dbDelta(
		'CREATE TABLE ' . hwm_table_name( 'subscribers' ) . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(190) NOT NULL,
		list varchar(20) NOT NULL,
		status varchar(12) NOT NULL DEFAULT 'pending',
		token char(40) NOT NULL,
		profile text NOT NULL,
		sent text NOT NULL,
		created datetime NOT NULL,
		confirmed datetime DEFAULT NULL,
		last_mail datetime DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email_list (email,list),
		KEY token (token),
		KEY list_status (list,status)
		) $c;"
	);
	dbDelta(
		'CREATE TABLE ' . hwm_table_name( 'failures' ) . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created date NOT NULL,
		brand varchar(40) NOT NULL,
		type varchar(20) NOT NULL,
		size varchar(10) NOT NULL DEFAULT '',
		install_year smallint(5) unsigned NOT NULL,
		fail_year smallint(5) unsigned NOT NULL,
		cause varchar(30) NOT NULL,
		outcome varchar(20) NOT NULL DEFAULT '',
		flushed varchar(8) NOT NULL DEFAULT 'unknown',
		anode varchar(8) NOT NULL DEFAULT 'unknown',
		water varchar(10) NOT NULL DEFAULT 'normal',
		state char(2) NOT NULL DEFAULT '',
		note varchar(300) NOT NULL DEFAULT '',
		status varchar(10) NOT NULL DEFAULT 'new',
		PRIMARY KEY  (id),
		KEY brand (brand),
		KEY status (status)
		) $c;"
	);
	update_option( 'hwm_db_version', HWM_DB_VERSION, true );
}

add_action(
	'init',
	static function () {
		if ( get_option( 'hwm_db_version' ) !== HWM_DB_VERSION ) {
			hwm_db_install();
		}
		if ( ! wp_next_scheduled( 'hwm_lists_daily' ) ) {
			wp_schedule_event( time() + 600, 'daily', 'hwm_lists_daily' );
		}
	},
	5
);

/* ---------- small helpers ---------- */

/** Options allowed for a field, from the data files (never free text). */
function hwm_list_options( string $field ): array {
	switch ( $field ) {
		case 'recallBrand':
			return array_merge( array( '' ), array_keys( (array) hwm_raw( 'recalls', 'brands' ) ), array( 'rinnai', 'noritz', 'takagi', 'bosch', 'other' ) );
		case 'failBrand':
			return array_map( static fn( $o ) => (string) $o['id'], (array) hwm_raw( 'serial-formats', 'brandOptions.options' ) );
		case 'type':
			return array_keys( (array) hwm_raw( 'maintenance', 'types' ) );
		case 'water':
			return array_keys( (array) hwm_raw( 'maintenance', 'water' ) );
		case 'cause':
			return array_map( static fn( $o ) => (string) $o['id'], (array) hwm_raw( 'failure-database', 'causes' ) );
		case 'outcome':
			return array_merge( array( '' ), array_map( static fn( $o ) => (string) $o['id'], (array) hwm_raw( 'failure-database', 'outcomes' ) ) );
		case 'yesNo':
			return array_map( static fn( $o ) => (string) $o['id'], (array) hwm_raw( 'failure-database', 'yesNo' ) );
		case 'state':
			return array_merge( array( '' ), array_keys( (array) hwm_raw( 'groundwater-temp-by-state', 'states' ) ) );
	}
	return array();
}

/** A POST field, trimmed, or '' when it is not one of $allowed (when given). */
function hwm_post( string $key, ?array $allowed = null, int $max = 120 ): string {
	$v = isset( $_POST[ $key ] ) ? trim( sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- public form; spam is handled by honeypot + rate limit (page cache makes nonces unusable).
	$v = mb_substr( $v, 0, $max );
	if ( null !== $allowed && ! in_array( $v, $allowed, true ) ) {
		return '';
	}
	return $v;
}

function hwm_post_int( string $key, int $min, int $max ): ?int {
	$v = hwm_post( $key, null, 6 );
	if ( '' === $v || ! ctype_digit( $v ) ) {
		return null;
	}
	$n = (int) $v;
	return ( $n >= $min && $n <= $max ) ? $n : null;
}

/** True when this visitor has made more than $max requests of $action within an hour. No IP is stored. */
function hwm_rate_limited( string $action, int $max ): bool {
	// Behind the Hostinger CDN REMOTE_ADDR can be the edge, so the first forwarded address is used when present
	// (a forged header only lets a spammer skip the limit; double opt-in and the honeypot still apply).
	$fwd = explode( ',', (string) ( $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '' ) );
	$ip  = trim( $fwd[0] ) !== '' ? trim( $fwd[0] ) : (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );
	$key = 'hwm_rl_' . substr( hash_hmac( 'sha256', $action . '|' . $ip, wp_salt( 'nonce' ) ), 0, 32 );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return true;
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
	return false;
}

/** Reply to a form post: JSON for the page script, else a redirect to the message page. */
function hwm_form_reply( bool $ok, string $code, string $message ): void {
	if ( '1' === hwm_post( 'ajax', array( '1' ) ) ) {
		wp_send_json( array( 'ok' => $ok, 'code' => $code, 'message' => $message ), $ok ? 200 : 400 );
	}
	wp_safe_redirect( hwm_origin() . '/subscription/?m=' . rawurlencode( $code ) );
	exit;
}

function hwm_list_messages(): array {
	return array(
		'check-email'  => 'Almost done: we sent you an email. Click the link in it to confirm. If it is not there in a few minutes, look in spam.',
		'confirmed'    => 'You are signed up. You can stop the emails at any time with the link at the bottom of each one.',
		'unsubscribed' => 'You are unsubscribed and will get no more of these emails. Your address has been removed from this list.',
		'thanks'       => 'Thank you. Your report is saved. We publish the results once enough reports are in.',
		'invalid'      => 'Something in the form was missing or not valid. Go back, check the fields and try again.',
		'slow-down'    => 'Too many tries from this connection. Please wait an hour and try again.',
		'bad-link'     => 'This link is not valid any more. It may have been used already.',
	);
}

/** Send one plain-text email with the brand as sender and a one-click unsubscribe header when a token is given. */
function hwm_mail( string $to, string $subject, string $body, string $token = '' ): bool {
	$brand   = (string) hwm_site( 'brand' );
	$from    = defined( 'HWM_SMTP_USER' ) ? (string) HWM_SMTP_USER : (string) hwm_site( 'contact.email' );
	$headers = array( 'From: ' . $brand . ' <' . $from . '>', 'Content-Type: text/plain; charset=UTF-8' );
	if ( '' !== $token ) {
		$unsub     = hwm_origin() . '/subscription/unsubscribe/?t=' . rawurlencode( $token );
		$headers[] = 'List-Unsubscribe: <' . $unsub . '>';
		$headers[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';
		$body     .= "\n\n--\nYou get this because you signed up at " . hwm_site( 'domain' ) . ".\nStop these emails: " . $unsub . "\n" . $brand . ' · ' . hwm_url() . "\nThese emails carry no ads and no paid links.";
	}
	return wp_mail( $to, $subject, $body, $headers );
}

/* SMTP through the owner's mailbox when wp-config.php defines it (Hostinger: smtp.hostinger.com, port 465). */
add_action(
	'phpmailer_init',
	static function ( $mailer ) {
		if ( ! defined( 'HWM_SMTP_HOST' ) || ! defined( 'HWM_SMTP_USER' ) || ! defined( 'HWM_SMTP_PASS' ) ) {
			return;
		}
		$port = defined( 'HWM_SMTP_PORT' ) ? (int) HWM_SMTP_PORT : 465;
		$mailer->isSMTP();
		$mailer->Host       = (string) HWM_SMTP_HOST; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->Port       = $port; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->SMTPAuth   = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->Username   = (string) HWM_SMTP_USER; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->Password   = (string) HWM_SMTP_PASS; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->SMTPSecure = 465 === $port ? 'ssl' : 'tls'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->setFrom( (string) HWM_SMTP_USER, (string) hwm_site( 'brand' ), false );
	}
);

/* ---------- signup (recall alerts and health reminders) ---------- */

function hwm_handle_signup(): void {
	if ( '' !== hwm_post( 'website' ) ) { // Honeypot: people never see this field.
		hwm_form_reply( true, 'check-email', hwm_list_messages()['check-email'] );
	}
	if ( hwm_rate_limited( 'signup', 6 ) ) {
		hwm_form_reply( false, 'slow-down', hwm_list_messages()['slow-down'] );
	}
	$list  = hwm_post( 'list', array( 'recall', 'health' ) );
	$email = strtolower( sanitize_email( hwm_post( 'email', null, 190 ) ) );
	if ( '' === $list || ! is_email( $email ) || '1' !== hwm_post( 'consent', array( '1' ) ) ) {
		hwm_form_reply( false, 'invalid', hwm_list_messages()['invalid'] );
	}
	$year    = (int) gmdate( 'Y' );
	$profile = array();
	if ( 'recall' === $list ) {
		$profile = array( 'brand' => hwm_post( 'brand', hwm_list_options( 'recallBrand' ) ) );
	} else {
		$profile = array(
			'type'         => hwm_post( 'type', hwm_list_options( 'type' ) ),
			'installYear'  => hwm_post_int( 'installYear', $year - 40, $year ),
			'installMonth' => hwm_post_int( 'installMonth', 1, 12 ) ?? 1,
			'water'        => hwm_post( 'water', hwm_list_options( 'water' ) ),
		);
		if ( '' === $profile['type'] || null === $profile['installYear'] || '' === $profile['water'] ) {
			hwm_form_reply( false, 'invalid', hwm_list_messages()['invalid'] );
		}
	}
	global $wpdb;
	$t   = hwm_table_name( 'subscribers' );
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE email = %s AND list = %s", $email, $list ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from our prefix.
	$now = current_time( 'mysql', true );
	if ( $row && 'confirmed' === $row['status'] ) {
		$wpdb->update( $t, array( 'profile' => wp_json_encode( $profile ) ), array( 'id' => $row['id'] ) );
		hwm_form_reply( true, 'confirmed', hwm_list_messages()['confirmed'] );
	}
	if ( $row && 'pending' === $row['status'] && strtotime( $row['created'] . ' UTC' ) > time() - HOUR_IN_SECONDS ) {
		hwm_form_reply( true, 'check-email', hwm_list_messages()['check-email'] ); // One confirmation email per hour per address.
	}
	$token = wp_generate_password( 40, false, false );
	$data  = array( 'email' => $email, 'list' => $list, 'status' => 'pending', 'token' => $token, 'profile' => wp_json_encode( $profile ), 'sent' => '{}', 'created' => $now, 'confirmed' => null );
	if ( $row ) {
		$wpdb->update( $t, $data, array( 'id' => $row['id'] ) );
	} else {
		$wpdb->insert( $t, $data );
	}
	$what = 'recall' === $list ? 'water heater recall alerts' : 'Heater Health Reminder emails';
	hwm_mail(
		$email,
		'Confirm your ' . $what,
		"Someone (we hope you) asked for $what from " . hwm_site( 'brand' ) . ".\n\nConfirm here: " . hwm_origin() . '/subscription/confirm/?t=' . rawurlencode( $token ) . "\n\nIf this was not you, ignore this email and nothing will be sent."
	);
	hwm_form_reply( true, 'check-email', hwm_list_messages()['check-email'] );
}
add_action( 'admin_post_nopriv_hwm_signup', 'hwm_handle_signup' );
add_action( 'admin_post_hwm_signup', 'hwm_handle_signup' );

/** Subscriber row by token, or null. */
function hwm_subscriber_by_token( string $token ): ?array {
	if ( ! preg_match( '/^[A-Za-z0-9]{40}$/', $token ) ) {
		return null;
	}
	global $wpdb;
	$t   = hwm_table_name( 'subscribers' );
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE token = %s", $token ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $row ? $row : null;
}

function hwm_confirm( string $token ): string {
	$row = hwm_subscriber_by_token( $token );
	if ( ! $row || 'unsubscribed' === $row['status'] ) {
		return 'bad-link';
	}
	if ( 'confirmed' === $row['status'] ) {
		return 'confirmed';
	}
	global $wpdb;
	$profile = (array) json_decode( (string) $row['profile'], true );
	$sent    = array();
	$welcome = '';
	if ( 'health' === $row['list'] ) {
		$asof = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
		$rows = hwm_maintenance_rows( $profile, $asof );
		$lines = array();
		foreach ( $rows as $r ) {
			$sent[ $r['id'] ] = $r['lastDue'];   // Past due dates are shown now, not emailed again later.
			$lines[]          = '- ' . $r['label'] . ': next ' . $r['next'] . ( $r['overdue'] ? ' (overdue now)' : '' );
		}
		$welcome = "Your Heater Health Reminder is on. Here is your schedule:\n\n" . implode( "\n", $lines ) . "\n\nWe email you when each job comes due. Why each interval: " . hwm_url( 'water-heater-maintenance-schedule' );
	} else {
		$brand   = (string) ( $profile['brand'] ?? '' );
		$name    = '' !== $brand ? (string) ( hwm_raw( 'recalls', 'brands.' . $brand ) ?? ucfirst( $brand ) ) : '';
		$welcome = 'Your water heater recall alerts are on. We email you when the U.S. Consumer Product Safety Commission announces a water heater recall' . ( '' !== $name ? ' that names ' . $name : '' ) . ".\n\nCheck your heater now: " . hwm_url( 'water-heater-recall-lookup' );
	}
	$wpdb->update( hwm_table_name( 'subscribers' ), array( 'status' => 'confirmed', 'confirmed' => current_time( 'mysql', true ), 'sent' => wp_json_encode( (object) $sent ) ), array( 'id' => $row['id'] ) );
	hwm_mail( (string) $row['email'], 'You are signed up', $welcome, (string) $row['token'] );
	return 'confirmed';
}

function hwm_unsubscribe( string $token ): string {
	$row = hwm_subscriber_by_token( $token );
	if ( ! $row ) {
		return 'bad-link';
	}
	global $wpdb;
	// Keep no address after an unsubscribe: the row is deleted (a new signup starts fresh with double opt-in).
	$wpdb->delete( hwm_table_name( 'subscribers' ), array( 'id' => $row['id'] ) );
	return 'unsubscribed';
}

add_action( 'admin_post_nopriv_hwm_confirm', static function () { hwm_form_reply( true, hwm_confirm( hwm_post( 't', null, 40 ) ), '' ); } );
add_action( 'admin_post_hwm_confirm', static function () { hwm_form_reply( true, hwm_confirm( hwm_post( 't', null, 40 ) ), '' ); } );
add_action( 'admin_post_nopriv_hwm_unsub', static function () { hwm_form_reply( true, hwm_unsubscribe( hwm_post( 't', null, 40 ) ), '' ); } );
add_action( 'admin_post_hwm_unsub', static function () { hwm_form_reply( true, hwm_unsubscribe( hwm_post( 't', null, 40 ) ), '' ); } );

/* ---------- failure reports ---------- */

function hwm_handle_failure(): void {
	if ( '' !== hwm_post( 'website' ) ) {
		hwm_form_reply( true, 'thanks', hwm_list_messages()['thanks'] );
	}
	if ( hwm_rate_limited( 'failure', 5 ) ) {
		hwm_form_reply( false, 'slow-down', hwm_list_messages()['slow-down'] );
	}
	$year   = (int) gmdate( 'Y' );
	$max    = (int) hwm_raw( 'failure-database', 'maxAgeYears' );
	$row    = array(
		'created'      => gmdate( 'Y-m-d' ),
		'brand'        => hwm_post( 'brand', hwm_list_options( 'failBrand' ) ),
		'type'         => hwm_post( 'type', hwm_list_options( 'type' ) ),
		'size'         => (string) ( hwm_post_int( 'size', 1, 120 ) ?? '' ),
		'install_year' => hwm_post_int( 'installYear', $year - $max, $year ),
		'fail_year'    => hwm_post_int( 'failYear', $year - $max, $year ),
		'cause'        => hwm_post( 'cause', hwm_list_options( 'cause' ) ),
		'outcome'      => hwm_post( 'outcome', hwm_list_options( 'outcome' ) ),
		'flushed'      => hwm_post( 'flushed', hwm_list_options( 'yesNo' ) ),
		'anode'        => hwm_post( 'anode', hwm_list_options( 'yesNo' ) ),
		'water'        => hwm_post( 'water', hwm_list_options( 'water' ) ),
		'state'        => hwm_post( 'state', hwm_list_options( 'state' ) ),
		'note'         => hwm_post( 'note', null, (int) hwm_raw( 'failure-database', 'noteMaxChars' ) ),
		'status'       => 'new',
	);
	if ( '' === $row['brand'] || '' === $row['type'] || '' === $row['cause'] || null === $row['install_year'] || null === $row['fail_year'] || $row['fail_year'] < $row['install_year'] ) {
		hwm_form_reply( false, 'invalid', hwm_list_messages()['invalid'] );
	}
	foreach ( array( 'flushed', 'anode' ) as $k ) {
		$row[ $k ] = '' !== $row[ $k ] ? $row[ $k ] : 'unknown';
	}
	$row['water'] = '' !== $row['water'] ? $row['water'] : 'normal';
	global $wpdb;
	$wpdb->insert( hwm_table_name( 'failures' ), $row );
	delete_transient( 'hwm_failure_count' );
	hwm_form_reply( true, 'thanks', hwm_list_messages()['thanks'] );
}
add_action( 'admin_post_nopriv_hwm_failure', 'hwm_handle_failure' );
add_action( 'admin_post_hwm_failure', 'hwm_handle_failure' );

/** Reports received so far (not marked spam). Cached for an hour. */
function hwm_failure_count(): int {
	$n = get_transient( 'hwm_failure_count' );
	if ( false === $n ) {
		global $wpdb;
		$t = hwm_table_name( 'failures' );
		$n = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t WHERE status <> 'spam'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		set_transient( 'hwm_failure_count', $n, HOUR_IN_SECONDS );
	}
	return (int) $n;
}

/* ---------- reminder dates (mirror of formulas.js maintenanceSchedule) ---------- */

function hwm_add_months( DateTimeImmutable $d, int $months ): DateTimeImmutable {
	$first = $d->setDate( (int) $d->format( 'Y' ), (int) $d->format( 'n' ), 1 )->modify( ( $months >= 0 ? '+' : '' ) . $months . ' months' );
	$last  = (int) $first->format( 't' );
	return $first->setDate( (int) $first->format( 'Y' ), (int) $first->format( 'n' ), min( (int) $d->format( 'j' ), $last ) );
}

/**
 * For each task of the heater: the latest due date on or before $asof ('' if none yet), the next one after it,
 * and whether a due date has passed. Series: install + firstMonths, then every interval (hardEveryMonths on hard
 * water). The replacement note has one date: install + lifespan low years.
 */
function hwm_maintenance_rows( array $p, DateTimeImmutable $asof ): array {
	$type = (string) ( $p['type'] ?? '' );
	$fam  = (string) hwm_raw( 'maintenance', 'types.' . $type . '.family' );
	if ( '' === $fam || empty( $p['installYear'] ) ) {
		return array();
	}
	$inst = new DateTimeImmutable( sprintf( '%04d-%02d-15', (int) $p['installYear'], (int) ( $p['installMonth'] ?? 1 ) ), new DateTimeZone( 'UTC' ) );
	$out  = array();
	foreach ( (array) hwm_raw( 'maintenance', 'tasks' ) as $t ) {
		if ( $t['family'] !== $fam ) {
			continue;
		}
		$every = ( 'hard' === ( $p['water'] ?? '' ) && ! empty( $t['hardEveryMonths'] ) ) ? (int) $t['hardEveryMonths'] : (int) $t['everyMonths'];
		$due   = hwm_add_months( $inst, (int) $t['firstMonths'] );
		$last  = '';
		$k     = 0;
		while ( $due <= $asof && $k < 200 ) {
			$last = $due->format( 'Y-m-d' );
			$due  = hwm_add_months( $inst, (int) $t['firstMonths'] + $every * ( ++$k ) );
		}
		$out[] = array( 'id' => $t['id'], 'label' => $t['label'], 'link' => $t['link'], 'lastDue' => $last, 'next' => $due->format( 'F Y' ), 'nextIso' => $due->format( 'Y-m-d' ), 'overdue' => '' !== $last );
	}
	$rep  = (array) hwm_raw( 'maintenance', 'replacement' );
	$life = hwm_raw( 'lifespans', 'types.' . $type . '.' . $rep['lifespanKey'] );
	if ( is_numeric( $life ) ) {
		$due   = hwm_add_months( $inst, (int) round( (float) $life * 12 ) );
		$out[] = array( 'id' => $rep['id'], 'label' => $rep['label'], 'link' => $rep['link'], 'lastDue' => $due <= $asof ? $due->format( 'Y-m-d' ) : '', 'next' => $due->format( 'F Y' ), 'nextIso' => $due->format( 'Y-m-d' ), 'overdue' => $due <= $asof );
	}
	return $out;
}

/* ---------- daily job: reminders, recall alerts, CPSC watch ---------- */

add_action( 'hwm_lists_daily', 'hwm_lists_daily' );

function hwm_lists_daily(): array {
	$log = array( 'health' => hwm_send_health_reminders(), 'recall' => hwm_send_recall_alerts(), 'cpsc' => hwm_watch_cpsc() );
	update_option( 'hwm_lists_last_run', array( 'at' => gmdate( 'c' ), 'log' => $log ), false );
	return $log;
}

function hwm_send_health_reminders( int $limit = 150 ): int {
	global $wpdb;
	$t    = hwm_table_name( 'subscribers' );
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE list = 'health' AND status = 'confirmed' AND (last_mail IS NULL OR last_mail < %s) ORDER BY last_mail ASC LIMIT %d", gmdate( 'Y-m-d H:i:s', time() - 20 * DAY_IN_SECONDS ), $limit ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$asof = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
	$n    = 0;
	foreach ( (array) $rows as $s ) {
		$p    = (array) json_decode( (string) $s['profile'], true );
		$sent = (array) json_decode( (string) $s['sent'], true );
		$due  = array();
		foreach ( hwm_maintenance_rows( $p, $asof ) as $r ) {
			if ( '' !== $r['lastDue'] && ( $sent[ $r['id'] ] ?? '' ) < $r['lastDue'] ) {
				$due[]             = $r;
				$sent[ $r['id'] ] = $r['lastDue'];
			}
		}
		if ( ! $due ) {
			continue;
		}
		$lines = array_map( static fn( $r ) => '- ' . $r['label'] . "\n  How: " . hwm_url( (string) $r['link'] ) . "\n  After this: " . ( 'replace' === $r['id'] ? 'we will not remind you again' : 'next due ' . $r['next'] ), $due );
		$body  = "Your water heater is due for:\n\n" . implode( "\n\n", $lines ) . "\n\nSafety first: turn off the gas or the breaker before you open anything, and let the water cool. Hot water scalds. If a job needs gas or electrical work, use a licensed professional.\n\nYour full schedule: " . hwm_url( 'water-heater-maintenance-schedule' );
		if ( hwm_mail( (string) $s['email'], 'Water heater reminder: ' . strtolower( $due[0]['label'] ) . ( count( $due ) > 1 ? ' and more' : '' ), $body, (string) $s['token'] ) ) {
			$wpdb->update( $t, array( 'sent' => wp_json_encode( (object) $sent ), 'last_mail' => current_time( 'mysql', true ) ), array( 'id' => $s['id'] ) );
			++$n;
		}
	}
	return $n;
}

/** Email confirmed recall subscribers about recalls added to data/recalls.json since the last run. */
function hwm_send_recall_alerts( int $limit = 300 ): int {
	$recalls = (array) hwm_raw( 'recalls', 'recalls' );
	$ids     = array_map( static fn( $r ) => (string) $r['id'], $recalls );
	$known   = get_option( 'hwm_recalls_known' );
	if ( ! is_array( $known ) ) {
		update_option( 'hwm_recalls_known', $ids, false ); // First run: everything already listed counts as known.
		return 0;
	}
	$new = array_values( array_filter( $recalls, static fn( $r ) => ! in_array( (string) $r['id'], $known, true ) ) );
	if ( ! $new ) {
		return 0;
	}
	global $wpdb;
	$t    = hwm_table_name( 'subscribers' );
	$subs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE list = 'recall' AND status = 'confirmed' LIMIT %d", $limit ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$n    = 0;
	foreach ( (array) $subs as $s ) {
		$brand = (string) ( json_decode( (string) $s['profile'], true )['brand'] ?? '' );
		$hits  = array_filter( $new, static fn( $r ) => '' === $brand || in_array( $brand, (array) $r['brands'], true ) );
		if ( ! $hits ) {
			continue;
		}
		$parts = array_map( static fn( $r ) => $r['title'] . ' (' . $r['date'] . ")\nWhich units: " . $r['which'] . "\nHazard: " . $r['hazard'] . "\nWhat to do: " . $r['remedy'] . "\nCPSC notice: " . $r['url'], $hits );
		if ( hwm_mail( (string) $s['email'], 'Water heater recall: ' . reset( $hits )['product'], "A new U.S. CPSC water heater recall:\n\n" . implode( "\n\n", $parts ) . "\n\nCheck your model and serial: " . hwm_url( 'water-heater-recall-lookup' ), (string) $s['token'] ) ) {
			$wpdb->update( $t, array( 'last_mail' => current_time( 'mysql', true ) ), array( 'id' => $s['id'] ) );
			++$n;
		}
	}
	update_option( 'hwm_recalls_known', $ids, false );
	return $n;
}

/**
 * Look at CPSC recalls of the last 60 days; when one mentions a water heater and is not in data/recalls.json,
 * email the site owner once so it can be reviewed and added. Nothing is published automatically.
 */
function hwm_watch_cpsc(): int {
	$url = add_query_arg( array( 'format' => 'json', 'RecallDateStart' => gmdate( 'Y-m-d', time() - 60 * DAY_IN_SECONDS ) ), 'https://www.saferproducts.gov/RestWebServices/Recall' );
	$res = wp_remote_get( $url, array( 'timeout' => 20 ) );
	if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
		return -1;
	}
	$list  = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	$known = array_merge( array_map( static fn( $r ) => (string) $r['id'], (array) hwm_raw( 'recalls', 'recalls' ) ), array_map( 'strval', (array) hwm_raw( 'recalls', 'meta.excluded' ) ) );
	$seen  = (array) get_option( 'hwm_cpsc_seen', array() );
	$found = array();
	foreach ( (array) $list as $r ) {
		$id   = (string) ( $r['RecallNumber'] ?? '' );
		$text = strtolower( (string) ( $r['Title'] ?? '' ) . ' ' . (string) ( $r['Description'] ?? '' ) . ' ' . wp_json_encode( $r['Products'] ?? array() ) );
		if ( '' === $id || in_array( $id, $known, true ) || in_array( $id, $seen, true ) || ! preg_match( '/water heater|tankless/', $text ) ) {
			continue;
		}
		$found[] = $r;
		$seen[]  = $id;
	}
	if ( $found ) {
		$body = "New CPSC recalls that mention a water heater and are not yet on the recall lookup:\n\n" . implode( "\n\n", array_map( static fn( $r ) => $r['RecallNumber'] . ' · ' . substr( (string) $r['RecallDate'], 0, 10 ) . "\n" . $r['Title'] . "\n" . $r['URL'], $found ) ) . "\n\nTo add one: ask Claude to run npm run fetch:recalls and add it to data/recalls.json. Subscribers are emailed after that deploy.";
		hwm_mail( (string) hwm_site( 'contact.email' ), 'CPSC: new water heater recall to review', $body );
		update_option( 'hwm_cpsc_seen', array_slice( $seen, -200 ), false );
	}
	return count( $found );
}

/* ---------- /subscription/ pages (confirm, unsubscribe, messages) ---------- */

add_action(
	'parse_request',
	static function () {
		$path = hwm_request_path();
		if ( ! preg_match( '#^/subscription(?:/(confirm|unsubscribe))?/?$#', $path, $m ) ) {
			return;
		}
		$step  = $m[1] ?? '';
		$token = isset( $_GET['t'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['t'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		// RFC 8058 one-click unsubscribe from the mail client: a POST to the List-Unsubscribe URL.
		if ( 'unsubscribe' === $step && 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			hwm_unsubscribe( $token );
			status_header( 200 );
			header( 'Cache-Control: no-store' );
			exit;
		}
		$code = isset( $_GET['m'] ) ? sanitize_key( wp_unslash( (string) $_GET['m'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$msgs = hwm_list_messages();
		status_header( 200 );
		nocache_headers();
		header( 'X-LiteSpeed-Cache-Control: no-cache' );
		header( 'X-Robots-Tag: noindex, nofollow' );
		hwm_security_headers( false );
		$hwm_msg = array(
			'step'    => $step,
			'token'   => $token,
			'message' => $msgs[ $code ] ?? '',
		);
		include HWM_DIR . '/templates/subscription.php';
		exit;
	},
	0
);

/* ---------- admin: counts, CSV export, delete an address ---------- */

add_action(
	'admin_menu',
	static function () {
		add_management_page( 'Hot Water Math lists', 'Hot Water Math lists', 'manage_options', 'hwm-lists', 'hwm_admin_lists' );
	}
);

function hwm_admin_lists(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$st = hwm_table_name( 'subscribers' );
	$ft = hwm_table_name( 'failures' );
	$deleted = '';
	if ( isset( $_POST['hwm_delete_email'] ) && check_admin_referer( 'hwm_delete_email' ) ) {
		$e       = strtolower( sanitize_email( wp_unslash( (string) $_POST['hwm_delete_email'] ) ) );
		$deleted = (string) $wpdb->delete( $st, array( 'email' => $e ) );
	}
	if ( isset( $_POST['hwm_run_daily'] ) && check_admin_referer( 'hwm_run_daily' ) ) {
		hwm_lists_daily();
	}
	$counts = $wpdb->get_results( "SELECT list, status, COUNT(*) n FROM $st GROUP BY list, status", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$fails  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $ft" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$last   = get_option( 'hwm_lists_last_run' );
	echo '<div class="wrap"><h1>Hot Water Math lists</h1><table class="widefat striped" style="max-width:520px"><thead><tr><th>List</th><th>Status</th><th>People</th></tr></thead><tbody>';
	foreach ( (array) $counts as $c ) {
		echo '<tr><td>' . esc_html( $c['list'] ) . '</td><td>' . esc_html( $c['status'] ) . '</td><td>' . esc_html( $c['n'] ) . '</td></tr>';
	}
	echo '</tbody></table><p>Failure reports: <strong>' . esc_html( (string) $fails ) . '</strong> (publish at ' . esc_html( (string) hwm_raw( 'failure-database', 'publishAt' ) ) . ').</p>';
	echo '<p>SMTP: ' . ( defined( 'HWM_SMTP_HOST' ) ? 'set (' . esc_html( (string) HWM_SMTP_HOST ) . ')' : '<strong>not set</strong>: add HWM_SMTP_* to wp-config.php' ) . '. Last daily run: ' . esc_html( is_array( $last ) ? $last['at'] . ' ' . wp_json_encode( $last['log'] ) : 'never' ) . '</p>';
	$csv = static fn( string $what ) => esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hwm_csv&what=' . $what ), 'hwm_csv' ) );
	echo '<p><a class="button" href="' . $csv( 'failures' ) . '">Download failure reports (CSV)</a> <a class="button" href="' . $csv( 'subscribers' ) . '">Download subscribers (CSV)</a></p>'; // phpcs:ignore -- escaped above.
	echo '<form method="post">' . wp_nonce_field( 'hwm_run_daily', '_wpnonce', true, false ) . '<p><button class="button" name="hwm_run_daily" value="1">Run the daily job now</button></p></form>'; // phpcs:ignore
	echo '<h2>Delete an address (privacy request)</h2><form method="post">' . wp_nonce_field( 'hwm_delete_email', '_wpnonce', true, false ) . '<input type="email" name="hwm_delete_email" required> <button class="button">Delete from all lists</button></form>'; // phpcs:ignore
	if ( '' !== $deleted ) {
		echo '<p>Rows deleted: ' . esc_html( $deleted ) . '</p>';
	}
	echo '</div>';
}

add_action(
	'admin_post_hwm_csv',
	static function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'hwm_csv' ) ) {
			wp_die( 'Not allowed' );
		}
		global $wpdb;
		$what = 'subscribers' === ( $_GET['what'] ?? '' ) ? 'subscribers' : 'failures'; // phpcs:ignore WordPress.Security.NonceVerification
		$cols = 'subscribers' === $what ? 'email, list, status, profile, created, confirmed, last_mail' : '*';
		$rows = $wpdb->get_results( 'SELECT ' . $cols . ' FROM ' . hwm_table_name( $what ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="hotwatermath-' . $what . '-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		if ( $rows ) {
			fputcsv( $out, array_keys( $rows[0] ), ',', '"', '' );
		}
		foreach ( (array) $rows as $r ) {
			fputcsv( $out, $r, ',', '"', '' );
		}
		exit;
	}
);
