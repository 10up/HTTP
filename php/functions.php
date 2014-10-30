<?php
/**
 * Helper functions for the HTTP header wrapper.
 *
 * @package    HTTP
 * @subpackage Header
 */
namespace TenUp\HTTP\v1_0_0\Header;

use TenUp\HTTP\v1_0_0 as H;

/**
 * Add a header to the collection.
 *
 * @param string $key
 * @param null   $value
 * @param bool   $overwrite
 */
function add( $key, $value = null, $overwrite = false ) {
	$headers = H\Header::instance();

	if ( $overwrite ) {
		$headers->set( $key, $value );
	} else {
		$headers->add( $key, $value );
	}
}

/**
 * Remove a header from the collection.
 *
 * @param string $key
 */
function remove( $key ) {
	$headers = H\Header::instance();

	$headers->remove( $key );
}

/**
 * Clear all headers from the collection
 */
function clear() {
	$headers = H\Header::instance();

	$headers->clear();
}

/**
 * Get the header collection.
 *
 * @return array
 */
function get() {
	$headers = H\Header::instance();

	return $headers->parsed();
}