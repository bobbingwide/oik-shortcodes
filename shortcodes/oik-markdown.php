<?php // (C) Copyright Bobbing Wide 2014, 2015

/**
 * Implement [md] shortcode for Markdown 
 *
 *
 * Unlike other Markdown processors this doesn't save the content as HTML
 * the markdown source remains in the shortcode.
 * 
 * Looks like this is a subset of Jetpack's Markdown http://http://jetpack.me/support/markdown/
 * See http://en.support.wordpress.com/markdown-quick-reference/
 * 
 * @param array $atts - shortcode attributes - not expected
 * @param string $content - text to mark down. May contain HTML?
 * @param string $tag - not expected
 * @return string - the generated HTML
 */
function oikai_markdown( $atts=null, $content=null, $tag=null ) {
  if ( $content ) {
    oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
    oikai_format_description( $content );
  }
  return( bw_ret() );
}

function md__help( $shortcode="md" ) {
	return( "Format Markdown" );
}

function md__syntax( $shortcode="md" ) {
	return( array() );
}
