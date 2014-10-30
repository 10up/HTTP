<?php
namespace TenUp\HTTP\Header;

/**
 * Wrapper class for HTTP header manipulation.
 *
 * @package    HTTP
 * @subpackage Header
 */
class Header {
	/**
	 * @var Header
	 */
	protected static $container;

	/**
	 * @return Header
	 */
	public static function instance() {
		return self::$container;
	}

	/**
	 * @var array
	 */
	protected $headers;

	/**
	 * Default constructor.
	 *
	 * @param bool $cache Should this instance be cached in the static container?
	 */
	public function __construct( $cache = true ) {
		if ( $cache ) {
			self::$container = $this;
		}

		$this->headers = array();

		add_action( 'send_headers', array( $this, 'apply' ) );
	}

	/**
	 * Add a key to the array. Will NOT overwrite existing values.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function add( $key, $value = null ) {
		if ( ! isset( $this->headers[$key] ) ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * Set a specific key in the array. Will overwrite previous value.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set( $key, $value = null ) {
		$this->headers[$key] = $value;
	}

	/**
	 * Remove a specific header from the array.
	 *
	 * @param string $key
	 */
	public function remove( $key ) {
		if ( isset( $this->headers[$key] ) ) {
			unset( $this->headers[$key] );
		}
	}

	/**
	 * Clear all previously set headers.
	 */
	public function clear() {
		$this->headers = array();
	}

	/**
	 * List the headers currently defined in the wrapper.
	 *
	 * @return array
	 */
	public function get() {
		return $this->headers;
	}

	/**
	 * Process the header array to real HTTP headers for easier use.
	 *
	 * @return array
	 */
	public function parsed() {
		$parsed = array();

		foreach ( $this->headers as $header => $value ) {
			$hstring = $header;

			if ( null !== $value ) {
				$hstring .= ': ' . $value;
			}

			$parsed[] = $hstring;
		}

		return $parsed;
	}

	/**
	 * Add registered headers to PHP's header collection.
	 */
	public function apply() {
		foreach ( $this->parsed() as $header ) {
			header( $header );
		}
	}
}