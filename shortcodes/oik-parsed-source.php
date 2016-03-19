<?php // (C) Copyright Bobbing Wide 2016

/**
 * Implement [parsed_source] shortcode to support pagination of parsed_source using AJAX
 *
 * When called by oik-ajax $content will be null
 * so we obtain the data from the global post's post_content
 * Otherwise, from oikai_navi_parsed_source() we simulate the 
 * shortcode passing the $parsed_source as $content
 * 
 * @param array $atts 
 * @param string $content
 * @param string $tag "parsed_source"
 * @return string paginated ajaxifiable parsed_source 
 */							 

function oikai_parsed_source( $atts=null, $content=null, $tag="parsed_source" ) {
	bw_push();
	if ( !$content ) {
		$post = bw_global_post();
		$content = $post->post_content;
		//e( $content );
		bw_trace2( $content, "content" );
	}
	oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
	oikai_navi_parsed_source( $content );
	$result = bw_ret();
	$content = null;
	$atts['bwscid'] = bw_get_shortcode_id();
	$result = apply_filters( "oik_navi_result", $result, $atts, $content, $tag ); 
	bw_pop();
	return( $result );
}

