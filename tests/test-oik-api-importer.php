<?php // (C) Copyright Bobbing Wide 2019

/**
 * @package
 *
 * Tests for oikai_syntax_source()
 */
class Tests_oik_api_importer extends BW_UnitTestCase {

	function setUp(): void {
		oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
	}

	/**
	 *
	 */
	function prepare_content() {
		$content = "<?php\n";
		$content .= '$dang = capital_P_dangit( \'Wordpress\' );';
		return $content;
	}

	function get_tokens() {
		$content = $this->prepare_content();
		$tokens = token_get_all( $content );
		return $tokens;
	}

	/**
	 * This test showed me that it's no good to use this for oik_file
	 * when the component isn't WordPress and there's no path
	 *
	 */
	function test_oikai_determine_function_type() {
		$tests = [ 'capital_P_dangit' => 'oik_api'
					, 'WP' => 'oik_class'
					, 'index.php' => 'oik_file'
					, 'hook-wp' => 'oik_hook'
		];

		foreach ( $tests as $api_name => $expected ) {
			$type = oikai_determine_function_type( $api_name );
			$this->assertEquals( $expected, $type );
		}
	}

	function get_expected_cpd() {
		$expected = '<a class="oik_api" href="https://core.wp.a2z/oik_api/capital_p_dangit" ';
		$expected .= 'title="capital_P_dangit() - Forever eliminate &quot;Wordpress&quot; from the planet (or at least the little bit we can influence).">';
		$expected .= 'capital_P_dangit</a>';
		return $expected;

	}

	function test_handle_token_T_STRING() {
		$tokens = [];
		$tokens[] = array( 319, 'capital_P_dangit', 1 );
		oikai_handle_token_T_STRING( 0, $tokens[0], $tokens );
		$expected = $this->get_expected_cpd();
		$this->assertEquals( $tokens[0][3], $expected );
	}

	/**
	 * At the end of this token[5][3] should link to wp-a2z
	 *   [5] => Array
	(
	[0] => 319
	[1] => capital_P_dangit
	[2] => 2
	[3] => <a href="/oik_api/capital_P_dangit">capital_P_dangit</a>
	)
	 */

	function test_oikai_handle_token() {
		$tokens = $this->get_tokens();
		$key = 5;
		$token = $tokens[5];
		$token_name = token_name( $token[0] );
		oikai_handle_token( $key, $token, $tokens );
		$expected = $this->get_expected_cpd();
		$this->assertEquals( $tokens[5][3], $expected );
	}

	function dont_test_oikai_process_tokens1() {
		$tokens = $this->get_tokens();
		oikai_process_tokens1( $tokens );
		//print_r( $tokens );

	}

	/**
	 * Before wordpress_cache we get
	 * '<pre>
	 * <span class="T_VARIABLE">$dang</span>
	 * <span class="T_WHITESPACE" id="1"> </span>
	 * <span>=</span>
	 * <span class="T_WHITESPACE" id="3"> </span>
	 * <span class="T_STRING" id="4"><a href="/oik_api/capital_P_dangit">capital_P_dangit</a></span>
	 * <span>(</span>
	 * <span class="T_WHITESPACE" id="6"> </span>
	 * <span class="T_CONSTANT_ENCAPSED_STRING" id="7">'Wordpress'</span>
	 * <span class="T_WHITESPACE" id="8"> </span>
	 * <span>)</span>
	 * <span>;</span>&nbsp;
	 * </pre>'
	 *
	 * afterwards we want id=4 to link to wp-a2z.org
	 */

	function test_oikai_easy_tokens_for_wordpress_cache() {
		$sources = array();
		$sources[] = '$dang = capital_P_dangit( \'Wordpress\' );';
		oikai_syntax_source( $sources, 0 );
		$output = bw_ret();
		//$this->generate_expected_file( $output );
		$this->assertArrayEqualsFile( $output );

		//$expected = '<pre><span class="T_VARIABLE">$dang</span><span class="T_WHITESPACE" id="1"> </span><span>=</span><span class="T_WHITESPACE" id="3"> </span><span class="T_STRING" id="4"><a class="wordpress" href="https://core.wp.a2z/oik_api/capital_p_dangit">capital_P_dangit() - Forever eliminate &quot;Wordpress&quot; from the planet (or at least the little bit we can influence).</a></span><span>(</span><span class="T_WHITESPACE" id="6"> </span><span class="T_CONSTANT_ENCAPSED_STRING" id="7">\'Wordpress\'</span><span class="T_WHITESPACE" id="8"> </span><span>)</span><span>;</span>&nbsp;</pre>';
		//$this->assertEquals( $expected, $output );

	}



}
