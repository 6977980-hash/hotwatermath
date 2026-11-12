<?php
/**
 * Data access. Every number on the site is read through these helpers
 * from data/*.json or data/computed/*.json (CLAUDE.md §5).
 * PHP never does calculator math.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load a data file once per request.
 *
 * @param string $file File name without ".json". Use "computed/<name>" for build output.
 * @return array Decoded file, or an empty array when the file is missing or invalid.
 */
function hwm_data( string $file ): array {
	static $cache = array();
	if ( isset( $cache[ $file ] ) ) {
		return $cache[ $file ];
	}
	$data = array();
	if ( preg_match( '#^(computed/)?[a-z0-9-]+$#', $file ) ) {
		$path = HWM_DIR . '/data/' . $file . '.json';
		if ( is_readable( $path ) ) {
			$decoded = json_decode( (string) file_get_contents( $path ), true );
			if ( is_array( $decoded ) ) {
				$data = $decoded;
			}
		}
	}
	$cache[ $file ] = $data;
	return $data;
}

/**
 * Read one value by dot path, e.g. hwm_raw( 'lifespans', 'tank.gas.typical' ).
 *
 * @return mixed|null Null when the path does not exist.
 */
function hwm_raw( string $file, string $path ) {
	$node = hwm_data( $file );
	foreach ( explode( '.', $path ) as $key ) {
		if ( ! is_array( $node ) || ! array_key_exists( $key, $node ) ) {
			return null;
		}
		$node = $node[ $key ];
	}
	return $node;
}

/**
 * Read one value for printing. A record shaped { "value": x, "unit": "..." }
 * returns its value. Missing values return an empty string, never a guess.
 *
 * @param int|null $decimals Format numbers with this many decimals (thousands separators added).
 */
function hwm_value( string $file, string $path, ?int $decimals = null ): string {
	$value = hwm_raw( $file, $path );
	if ( is_array( $value ) && array_key_exists( 'value', $value ) ) {
		$value = $value['value'];
	}
	if ( null === $value || is_array( $value ) ) {
		return '';
	}
	if ( is_bool( $value ) ) {
		return $value ? 'yes' : 'no';
	}
	if ( null !== $decimals && is_numeric( $value ) ) {
		return number_format( (float) $value, $decimals );
	}
	return (string) $value;
}

/** Site settings shortcut: hwm_site( 'author.name' ). */
function hwm_site( string $path ) {
	return hwm_raw( 'site', $path );
}

/**
 * Render a sourced table from a data file.
 *
 * Convention: the data file holds
 *   "tables": { "<name>": { "caption": "...", "columns": [ { "key": "k", "label": "L", "unit": "gal" } ],
 *                           "rows": [ { "k": 1 } ], "source": "...", "sourceUrl": "...", "sourceDate": "..." } }
 * Table-level source fields fall back to the file's meta.
 */
function hwm_table( string $file, string $table = 'default' ): string {
	$spec = hwm_raw( $file, 'tables.' . $table );
	if ( ! is_array( $spec ) || empty( $spec['columns'] ) || empty( $spec['rows'] ) ) {
		return '';
	}
	$meta = hwm_raw( $file, 'meta' );
	$meta = is_array( $meta ) ? $meta : array();
	ob_start();
	hwm_part(
		'data-table',
		array(
			'caption'    => $spec['caption'] ?? '',
			'columns'    => $spec['columns'],
			'rows'       => $spec['rows'],
			'source'     => $spec['source'] ?? ( $meta['source'] ?? '' ),
			'sourceUrl'  => $spec['sourceUrl'] ?? ( $meta['sourceUrl'] ?? '' ),
			'sourceDate' => $spec['sourceDate'] ?? ( $meta['sourceDate'] ?? '' ),
		)
	);
	return (string) ob_get_clean();
}
