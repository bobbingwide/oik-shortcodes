<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package 
 * 
 * Tests for paired_replacements logic in shortcodes/oik-api-importer.php
 */
class Tests_issue_46 extends BW_UnitTestCase {

	function setUp(): void {
		oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
	}
	
	/**
	 * A single * should not be converted as emphasis markdown
	 * 
	 */
	function test_paired_replacements_overlap() {
		$expected = " * ";
		$line = $expected;
		$output = paired_replacements( " *",  "* ",  " <em>", "</em> ", $line );
		$this->assertEquals( $expected,  $output );
	}
	
	/**
	 * A double ** should not be converted as emphasis markdown
	 * 
	 */
	function test_paired_replacements_sidebyside() {
		$line = " ** ";
		$expected = " ** ";
		$output = paired_replacements( " *",  "* ",  " <em>", "</em> ", $line );
		$this->assertEquals( $expected,  $output );
	}
	
	/**
	 * A single blank space should not be converted as emphasis markdown
	 */
	function test_paired_replacements_one_blank_space() {
		$line = " * * ";
		$expected = " * * ";
		$output = paired_replacements( " *",  "* ",  " <em>", "</em> ", $line );
		$this->assertEquals( $expected,  $output );
	}
	
	/**
	 * Tests *emphasis* markdown.
	 */
	function test_emphasis_markdown() {
		$expected = "Tests <em>emphasis</em> markdown.";
		$line = "Tests *emphasis* markdown.";
		$output = paired_replacements( " *",  "* ",  " <em>", "</em> ", $line );
		$this->assertEquals( $expected,  $output );
	}
	

}
