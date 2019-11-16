<?php // (C) Copyright Bobbing Wide 2019

/**
 * @package 
 * 
 * Tests for Issue #62
 */
class Tests_issue_62 extends BW_UnitTestCase {

	public static $method = 'static method';

	function setUp(): void {
		oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
	}

	/**
	 * This test will pass but
	 * oikwp oik-shortcodes.php oik-shortcodes will fail with
	 * Fatal error: Cannot use "static" when no class scope is active in /tempfilename on line n
	 *
	 * until the code to cater for this has been 'fixed'.
	 */
	function test_return_new_static() {
		$result = new static();
		//print_r( $result );
		$this->assertInstanceOf(  "Tests_issue_62", $result );
	}


	/**
	 * This test will pass but oikwp oik-shortcodes will fail with the same message as above
	 * until the code to cater for this has been fixed.
	 */
	function test_static_coloncolon_method() {
		$class = static::$method;
		//print_r( $class );
		$this->assertEquals( "static method", $class );
	}

}
