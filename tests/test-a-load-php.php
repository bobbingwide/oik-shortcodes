<?php

/**
 * @package oik-shortcodes
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Unit tests to load all the PHP files for PHP 8.2
*/
class Tests_load_php extends BW_UnitTestCase
{

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setUp(): void
	{
		parent::setUp();

	}

	function test_load_admin_php() {

		$files = glob( 'admin/*.php');
		//print_r( $files );

		foreach ( $files as $file ) {
			switch ( $file ) {

				case 'admin/oik-create-apis.php':
				case 'admin/oik-create-blocks.php':
				case 'admin/oik-create-codes.php':
				case 'admin/set_compatible_up_to.php':
				case 'admin/set_md5_hash.php':
				case 'admin/set_oik_api_calls.php':
				case 'admin/set_oik_hook_plugins.php':
					break;
				default:
					oik_require( $file, 'oik-shortcodes');
			}

		}
		$this->assertTrue( true );


	}
	function test_load_classes_php() {
		$files = glob( 'classes/*.php');
		//print_r( $files );

		foreach ( $files as $file ) {
			switch ( $file ) {

				case 'classes/class-oiksc-file-loader.php':

					oik_require( 'classes/class-oiksc-function-loader.php', 'oik-shortcodes');


				default:
					oik_require( $file, 'oik-shortcodes');
			}

		}
		$this->assertTrue( true );

	}

	function test_load_shortcodes_php() {
		$files = glob( 'shortcodes/*.php');
		//print_r( $files );

		foreach ( $files as $file ) {
			switch ( $file ) {

				case '':

					break;


				default:
					oik_require( $file, 'oik-shortcodes');
			}

		}
		$this->assertTrue( true );

	}

}
