<?php // (C) Copyright Bobbing Wide 2012-2016

/** 
 * Automagically determine the shortcode list
 * 
 * If no functions have been passed then we can determine the list of APIs
 * either using the component ID or the context of the current post.
 * 
 * The component parameter was added to support displaying shortcodes for a Template ( parent ) theme
 * 
 * post_type     | action
 * ----------    | -------------
 * oik_shortcode | n/a
 * oik-plugins   | find ALL the shortcodes linked to the plugin through "_oik_sc_plugin"
 * oik-themes    | find ALL the shortcodes linked to the theme through "_oik_sc_plugin"
 * oik_api       | find all the shortcodes linked to the API through "_oik_sc_func"
 * other         | n/a
 * 
 * @param array $atts shortcode parameters
 */
function oikai_listcodes( $atts ) {
	oik_require( "shortcodes/oik-list.php" ); 
	$post_id = bw_array_get( $atts, "component", null );
	if ( $post_id ) {
		$atts['post_type'] = "oik_shortcodes"; 
		$atts['meta_key' ] = "_oik_sc_plugin";
		$atts['meta_value'] = $post_id;
		e( bw_list( $atts ) );
	} else {
		global $post;
		if ( $post ) {
			$post_id = $post->ID;
			if ( $post->post_type == "oik_api" ) {
				$atts['post_type'] = "oik_shortcodes"; 
				$atts['meta_key' ] = "_oik_sc_func";
				$atts['meta_value'] = $post_id;
				e( bw_list( $atts ) );
			} elseif ( $post->post_type == "oik-plugins" ) {
				$atts['post_type'] = "oik_shortcodes"; 
				$atts['meta_key' ] = "_oik_sc_plugin";
				$atts['meta_value'] = $post_id;
				e ( bw_list( $atts ) );
			} elseif ( $post->post_type == "oik-themes" ) {
				$atts['post_type'] = "oik_shortcodes"; 
				$atts['meta_key' ] = "_oik_sc_plugin";
				$atts['meta_value'] = $post_id;
				e ( bw_list( $atts ) );
			} else {
				// Not the right post_type to list APIs
				bw_trace2( $post->post_type, "Unexpected post_type" );
				p( "Unexpected post type: " .  $post->post_type );
			}
		} else { 
			// Don't expect this! 
		}
	}
}

/**
 * Load the shortcodes listed in the array
 *
 * Since the plugin/theme name is not specified we could get duplicates in the results.
 * 
 * @param array $shortcodes - array of shortcodes 
 * @result array $posts - array of posts found
 */
function oiksc_get_shortcodes_bycode( $shortcodes ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array();
  $atts['post_type'] = "oik_shortcodes" ;
  //$atts['numberposts'] = -1; 
  $meta_query = array();
  $meta_query[] = array( "key" => "_oik_sc_code"
                       , "value" => $shortcodes 
                       , "compare" => "IN"  
                       );
  $atts['meta_query'] = $meta_query;
  $posts = bw_get_posts( $atts ); 
  return( $posts );
} 

/**
 * Implement a link to a (set of) shortcodes
 * 
 * [codes codes=shortcode]
 * [codes func=shortcode]   - not yet implemented! 
 * 
 * alternatively
 * [codes bbboing_sc fiddle] will return the values in $atts[0] and $atts[1] 
 * so we can get the API names from there!
 * 
 * How about? 
 * [codes "bbboing_sc fiddle" ]  
 * 
 * @param array $atts - shortcode parameters
 * @param string $content - shortcode content - not expected
 * @param string $tag - shortcode name
 * @return string generated HTML 
 */
function oikai_codeslink( $atts=null, $content, $tag ) {
  $shortcode = bw_array_get( $atts, "codes", null );
  $shortcodes = bw_as_array( $shortcode );
  $unkeyed = bw_array_get_unkeyed( $atts );
  $shortcodes = array_merge( $shortcodes, $unkeyed );
  bw_trace2( $shortcodes, "shortcodes", true, BW_TRACE_DEBUG );
  
  if ( count( $shortcodes) ) {
    oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
    $posts = oiksc_get_shortcodes_bycode( $shortcodes );
    if ( $posts ) {
      $class = bw_array_get( $atts, "class", "bw_codes" );
      sul( $class );
      foreach ( $posts as $post ) {
        bw_format_list( $post, $atts );
      }
      eul();
      
    } else {
      p( "Cannot find shortcode(s):" . implode( ",", $shortcodes) );
    } 
  } else {
    oikai_listcodes( $atts ); 
  }
  return( bw_ret()); 
}

function codes__help() {
  return( "Create links to related shortcodes" );
}

function codes__syntax( $shortcode="codes" ) {  
  $syntax = array( "codes" => bw_skv( null, "<i>shortcode</i>", "Shortcode name list e.g. bw_pages" )
								 , "component" => bw_skv( null, "<i>ID</i>", "ID of plugin/theme for the shortcodes" )
                 );
  $syntax += _sc_classes();
  return( $syntax );
}
  
  


