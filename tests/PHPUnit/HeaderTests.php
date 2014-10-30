<?php

namespace TenUp\HTTP\v1_0_0;

use Mockery;
use ReflectionProperty;
use WP_Mock;
use Patchwork;

/**
 * @group HTTP
 * @group HTTP-Header
 */
class HeaderTest extends TestCase {

	protected $testFiles = array( 'header.php' );

	/**
	 * @runInSeparateProcess
	 */
	public function test_instance() {
		$this->assertNull( Header::instance() );

		$property = new ReflectionProperty( __NAMESPACE__ . '\\Header', 'container' );
		$property->setAccessible( true );
		$mock_header = $this->getMockHeaderObject();
		$property->setValue( $mock_header );

		$this->assertSame( $mock_header, Header::instance() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test___construct() {
		$property = new ReflectionProperty( __NAMESPACE__ . '\\Header', 'container' );
		$property->setAccessible( true );

		// Hackiest hack that ever did hack
		$test = $this;
		WP_Mock::wpFunction( __NAMESPACE__ . '\\add_action', array(
			'times'  => 1,
			'args'   => array( 'send_headers', '*' ),
			'return' => function ( $hook, $callback ) use ( $test ) {
				$test->assertInternalType( 'callable', $callback );
				$test->assertInstanceOf( __NAMESPACE__ . '\\Header', $callback[0] );
				$test->assertEquals( 'apply', $callback[1] );
			}
		) );

		$object = new Header();
		$this->assertSame( $object, $property->getValue() );
		$headers = $this->getHeadersProperty();
		$this->assertEquals( array(), $headers->getValue( $object ) );
	}

	/**
	 * @param      $headers
	 * @param      $key
	 * @param      $value
	 * @param bool $shouldSet
	 *
	 * @dataProvider data_add
	 */
	public function test_add( $headers, $key, $value, $shouldSet = false ) {
		$object          = $this->getMockHeaderObject( 'set' );
		$headersProperty = $this->getHeadersProperty();
		$headersProperty->setValue( $object, $headers );
		$expectation = $object->shouldReceive( 'set' );
		if ( $shouldSet ) {
			$expectation->once()->with( $key, $value );
		} else {
			$expectation->never();
		}

		/** @var Header $object */
		$object->add( $key, $value );

		$this->assertConditionsMet();
	}

	public function data_add() {
		return array(
			array( array( 'foo' => 'bar' ), 'foo', 'baz' ),
			array( array( 'foo' => 'bar' ), 'baz' . rand( 10, 99 ), rand( 100, 999 ), true ),
		);
	}

	/**
	 * @param $initial
	 * @param $expected
	 * @param $key
	 * @param $value
	 *
	 * @dataProvider data_set
	 */
	public function test_set( $initial, $expected, $key, $value ) {
		$headersProp = $this->getHeadersProperty();
		$object      = new Header();

		$headersProp->setValue( $object, $initial );

		$object->set( $key, $value );

		$this->assertEquals( $expected, $headersProp->getValue( $object ) );
	}

	public function data_set() {
		$key1   = 'key' . rand( 0, 9 );
		$value1 = 'value' . rand( 0, 9 );
		$value2 = 'value' . rand( 10, 99 );
		return array(
			array( array(), array( $key1 => $value1 ), $key1, $value1 ),
			array( array( $key1 => $value1 ), array( $key1 => $value2 ), $key1, $value2 ),
			array( array( 'something' => 'else' ), array( 'something' => 'else', $key1 => $value2 ), $key1, $value2 ),
			array( array( $key1 => $value1, 'something' => 'else' ), array( $key1 => $value2, 'something' => 'else', ), $key1, $value2 ),
		);
	}

	public function test_remove() {
		$headersProp = $this->getHeadersProperty();
		$object      = new Header();

		$expected      = $initial = array(
			'foo'                => 'bar',
			'baz' . rand( 0, 9 ) => 'bat' . rand( 0, 9 ),
		);
		$key           = 'test' . rand( 10, 99 );
		$initial[$key] = 'some value' . rand( 100, 999 );

		$headersProp->setValue( $object, $initial );

		$object->remove( $key );

		$this->assertEquals( $expected, $headersProp->getValue( $object ) );
	}

	public function test_clear() {
		$headers = $this->getHeadersProperty();
		$object  = new Header();

		$headers->setValue( $object, array( 'not' => 'empty' ) );

		$object->clear();

		$this->assertEquals( array(), $headers->getValue( $object ) );
	}

	public function test_get() {
		$prop   = $this->getHeadersProperty();
		$object = new Header();

		$array = array( 'test' . rand( 0, 9 ) => 'value' . rand( 10, 99 ) );
		$prop->setValue( $object, $array );

		$this->assertSame( $array, $object->get() );
	}

	/**
	 * @dataProvider data_parsed
	 */
	public function test_parsed( $initial, $expected ) {
		$prop   = $this->getHeadersProperty();
		$object = new Header();
		$prop->setValue( $object, $initial );

		$this->assertEquals( $expected, $object->parsed() );
	}

	public function data_parsed() {
		$r = rand( 0, 9 );
		return array(
			array( array(), array() ),
			array( array( "Test$r" => "Value$r" ), array( "Test$r: Value$r" ) ),
			array( array( "Test$r" => "Value$r", 'Stand-Alone' => null, ), array( "Test$r: Value$r", 'Stand-Alone' ) ),
		);
	}

	public function test_apply() {
		$object = $this->getMockHeaderObject( 'parsed' );

		$parsed = array(
			'Test-Value',
			'Content-Type: text/html',
		);
		$object->shouldReceive( 'parsed' )->once()->andReturn( $parsed );
		foreach ( $parsed as $header ) {
			WP_Mock::wpFunction( __NAMESPACE__ . '\\header', array(
				'times' => 1,
				'args'  => $header,
			) );
		}

		/** @var Header $object */
		$object->apply();

		$this->assertConditionsMet();
	}

	/**
	 * @return Mockery\MockInterface
	 */
	protected function getMockHeaderObject() {
		$methods = implode( ',', func_get_args() );
		$class   = __NAMESPACE__ . '\\Header';
		if ( $methods ) {
			$class .= "[$methods]";
		}
		return Mockery::mock( $class );
	}

	/**
	 * @return ReflectionProperty
	 */
	protected function getHeadersProperty() {
		$header = new ReflectionProperty( __NAMESPACE__ . '\\Header', 'headers' );
		$header->setAccessible( true );
		return $header;
	}

}