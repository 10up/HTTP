<?php

namespace TenUp\HTTP\v1_0\Header;

use Mockery;
use ReflectionProperty;
use TenUp\HTTP\v1_0 as H;

/**
 * @group HTTP
 * @group HTTP-Header
 */
class HeaderFunctionsTest extends H\TestCase {

	protected $testFiles = array(
		'functions.php',
		'header.php',
	);

	public function test_add_overwrite() {
		$object = $this->mockHeaderObject( 'set' );
		$key    = 'test' . rand( 0, 9 );
		$value  = 'value' . rand( 10, 99 );
		$object->shouldReceive( 'set' )->once()->with( $key, $value );
		add( $key, $value, true );
		$this->assertConditionsMet();
	}

	public function test_add() {
		$object = $this->mockHeaderObject( 'add' );
		$key    = 'test' . rand( 0, 9 );
		$value  = 'value' . rand( 10, 99 );
		$object->shouldReceive( 'add' )->once()->with( $key, $value );
		add( $key, $value, false );
		$this->assertConditionsMet();
	}

	public function test_remove() {
		$object = $this->mockHeaderObject( 'remove' );
		$key    = 'key' . rand( 0, 9 );
		$object->shouldReceive( 'remove' )->once()->with( $key );
		remove( $key );
		$this->assertConditionsMet();
	}

	public function test_clear() {
		$object = $this->mockHeaderObject( 'clear' );
		$object->shouldReceive( 'clear' )->once();
		clear();
		$this->assertConditionsMet();
	}

	public function test_get() {
		$object = $this->mockHeaderObject( 'parsed' );
		$values = array( 'test' . rand( 0, 9 ) => 'value' . rand( 10, 99 ) );
		$object->shouldReceive( 'parsed' )->once()->andReturn( $values );
		$this->assertSame( $values, get() );
		$this->assertConditionsMet();
	}

	/**
	 * @return Mockery\MockInterface
	 */
	protected function mockHeaderObject() {
		$methods = implode( ',', func_get_args() );
		$class   = 'TenUp\HTTP\v1_0\Header';
		if ( $methods ) {
			$class .= "[$methods]";
		}
		$mock     = Mockery::mock( $class );
		$instance = new ReflectionProperty( 'TenUp\HTTP\v1_0\Header', 'container' );
		$instance->setAccessible( true );
		$instance->setValue( $mock );
		return $mock;
	}

}