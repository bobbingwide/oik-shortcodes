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
		$this->assertInstanceOf( 'OIK\oik_shortcodes\oiksc_wordpress_cache', $result );
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
	 *
	 */

	function test_load_all_CPT() {

		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$results         = $wordpress_cache->load_all_CPT( 'oik_api', '_oik_api_name' );
		$this->assertEquals( $results, 7853 );
	}

	function test_load_cache_from_db() {
		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$wordpress_cache->load_cache_from_db();
		$count = $wordpress_cache->query_cache_count();
		$this->assertEquals( $count, 11549 );
	}

	/**
	 * We shouldn't really run these 'tests' as they'll change the file if we get the url wrong.
	 */
	function test_save_cache() {
		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$wordpress_cache->load_cache_from_db();
		$wordpress_cache->save_cache();
		$this->assertFileExists( 'oiksc-wordpress-cache.json' );

	}

	function test_load_cache() {
		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$wordpress_cache->load_cache();
		$count = $wordpress_cache->query_cache_count();
		$this->assertEquals( $count, 11549 );

	}

	function test_get_cache_key() {
		$wordpress_cache = new OIK\oik_shortcodes\oiksc_wordpress_cache();
		$result = new stdClass();
		$result->post_type = 'oik_api';
		$result->meta_value = 'capital_P_dangit';
		$cache_key = $wordpress_cache->get_cache_key(  $result );
		$this->assertEquals( $cache_key, 'capital_P_dangit' );

	}

	function test_oiksc_load_wordpress_cache() {
		oik_require( 'admin/oik-create-apis.php', 'oik-shortcodes' );
		$wordpress_cache = oiksc_load_wordpress_cache();
		$this->assertInstanceOf( 'OIK\oik_shortcodes\oiksc_wordpress_cache', $wordpress_cache );
		$count = $wordpress_cache->query_cache_count();
		$this->assertEquals( $count, 11549 );
		//print_r( $wordpress_cache );

	}

	function test_is_wordpress_api() {
		oik_require( 'admin/oik-create-apis.php', 'oik-shortcodes' );
		$wordpress_cache = oiksc_load_wordpress_cache();
		$is_api          = $wordpress_cache->is_wordpress_api( 'capital_P_dangit' );
		$this->assertTrue( $is_api );
	}

	/**
	 * This is a good test. It helps us determine what can be unique and which field to use
	 * to create the cache_key
	 */

	function test_get_wordpress_link() {
		oik_require( 'admin/oik-create-apis.php', 'oik-shortcodes' );
		$wordpress_cache = oiksc_load_wordpress_cache();
		$link            = $wordpress_cache->get_wordpress_link( 'capital_P_dangit' );
		$expected = '<a class="oik_api" href="https://core.wp.a2z/oik_api/capital_p_dangit" title="capital_P_dangit() - Forever eliminate &quot;Wordpress&quot; from the planet (or at least the little bit we can influence).">capital_P_dangit</a>';
		$this->assertEquals( $expected, $link );
	}

}
