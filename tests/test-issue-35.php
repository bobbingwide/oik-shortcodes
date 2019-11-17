<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2019
 * @package oik-shortcodes
 *
 * Tests Issue #35
 */
class Tests_issue_35 extends BW_UnitTestCase {

	function setUp(): void {
		oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
	}
	/**
	 * We should no longer get this:
	 * Warning: Unterminated comment starting line 2 in C:\apache\htdocs\wordpress\wp-content\plugins\oik-shortcodes\shortcodes\oik-api-importer.php on line 2368
	 *
	 */
	function test_oikai_syntax_source_unterminated_comment() {
		$sources = array();
		$sources[] = '/*';
		oikai_syntax_source( $sources, 0 );
		$output = bw_ret();
		$expected = '<pre><span class="T_COMMENT">/*</span>&nbsp;</pre>';
		$this->assertEquals( $expected, $output );
	}
}
