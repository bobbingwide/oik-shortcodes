<?php // (C) Copyright Bobbing Wide 2019

/**
 * @package
 *
 * Tests for Issue #73
 */
class Tests_issue_73 extends BW_UnitTestCase {

	public static $method = 'static method';

	function setUp(): void {
		oik_require( 'classes/class-oiksc-wordpress-cache.php', 'oik-shortcodes' );
	}

	/**
	*/
	function test_return_new_wordpress_cache() {
		$result = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		//print_r( $result );
		$this->assertInstanceOf(  'OIK\oik_shortcodes\oiksc_wordpress_cache', $result );
	}

	/**
	 * For WordPress 5.3 the number of posts created by post type were:
	 *
	 * post_type | count  | accum
	 * --------- | ------ | ---
	 * oik_api | 7853     | 7853
	 * oik_class | 467     |  8320
	 * oik_file | 816     |  9136
	 * oik_hook | 2413 | 11549
	 */

	function test_load_all_CPT() {

		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$results = $wordpress_cache->load_all_CPT( 'oik_api', '_oik_api_name' );
		$this->assertEquals( $results, 7853 );
	}

	function test_load_cache_from_db() {
		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$wordpress_cache->load_cache_from_db();
		//$this->assert


	}

	function test_save_cache() {
		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$wordpress_cache->load_cache_from_db();
		$wordpress_cache->save_cache();

	}

	function test_load_cache() {
		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$wordpress_cache->load_cache();
		$count = $wordpress_cache->query_cache_count();
		$this->assertEquals( $count, 11549 );

	}



}
