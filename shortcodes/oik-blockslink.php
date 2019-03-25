<?php // (C) Copyright Bobbing Wide 2018

/**
 * Implements [blocks] shortcode to automagically determine the block list
 * 
 * If no functions have been passed then we can determine the list of blocks
 * either using the component ID or the context of the current post.
 * 
 * Note: The component parameter was added to support displaying shortcodes for a Template ( parent ) theme.
 * It may have no relevance for the blocks shortcode!
 * 
 * post_type     | action
 * ----------    | -------------
 * block | n/a
 * oik-plugins   | find ALL the blocks linked to the plugin through "_oik_sc_plugin"
 * oik-themes    | find ALL the blocks linked to the theme through "_oik_sc_plugin"
 * oik_api       | find all the blocks linked to the API through "_oik_sc_func"
 * other         | n/a
 * 
 * @param array $atts block parameters
 */
function oikai_listblocks( $atts ) {
	oik_require( "shortcodes/oik-list.php" ); 
	$post_id = bw_array_get( $atts, "component", null );
	if ( $post_id ) {
		$atts['post_type'] = "block";
		$atts['meta_key' ] = "_oik_sc_plugin";
		$atts['meta_value'] = $post_id;
		e( bw_list( $atts ) );
	} else {
		global $post;
		if ( $post ) {
			$post_id = $post->ID;
			if ( $post->post_type == "oik_api" ) {
				$atts['post_type'] = "block";
				$atts['meta_key' ] = "_oik_sc_func";
				$atts['meta_value'] = $post_id;
				e( bw_list( $atts ) );
			} elseif ( $post->post_type == "oik-plugins" ) {
				$atts['post_type'] = "block";
				$atts['meta_key' ] = "_oik_sc_plugin";
				$atts['meta_value'] = $post_id;
				$atts['fields'] = 'title,excerpt,block_category,block_keyword,block_classification';
				oik_require( "shortcodes/oik-table.php");
				e ( bw_table( $atts ) );
			} elseif ( $post->post_type == "oik-themes" ) {
				$atts['post_type'] = "block";
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
 * Load the blocks listed in the array
 *
 * Since the plugin/theme name is not specified we could get duplicates in the results.
 * 
 * @param array $blocks - array of blocks
 * @result array $posts - array of posts found
 */
function oiksc_get_blocks_byblock( $blocks ) {
  oik_require( "includes/bw_posts.php" );
  $atts = array();
  $atts['post_type'] = "block" ;
  //$atts['numberposts'] = -1; 
  $meta_query = array();
  $meta_query[] = array( "key" => "_block_type_name"
                       , "value" => $blocks
                       , "compare" => "IN"  
                       );
  $atts['meta_query'] = $meta_query;
  $posts = bw_get_posts( $atts ); 
  return( $posts );
} 

/**
 * Implement a link to a (set of) blocks
 * 
 * [blocks blocks=oik-block/wp]
 * [blocks oik-block/wp]
 *
 * @param array $atts - shortcode parameters
 * @param string $content - shortcode content - not expected
 * @param string $tag - shortcode name
 * @return string generated HTML 
 */
function oikai_blockslink( $atts=null, $content, $tag ) {
	$atts['thumbnail'] = bw_array_get( $atts, "thumbnail", "none");
  $block = bw_array_get( $atts, "blocks", null );
  $blocks = bw_as_array( $block );
  $unkeyed = bw_array_get_unkeyed( $atts );
  $blocks = array_merge( $blocks, $unkeyed );
  bw_trace2( $blocks, "blocks", true, BW_TRACE_DEBUG );
  
  if ( count( $blocks) ) {
    oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
    $posts = oiksc_get_blocks_byblock( $blocks );
    if ( $posts ) {
      $class = bw_array_get( $atts, "class", "bw_blocks" );
      sul( $class );
      foreach ( $posts as $post ) {
        bw_format_list( $post, $atts );
      }
      eul();
      
    } else {
      p( "Cannot find block(s):" . implode( ",", $blocks) );
    } 
  } else {
    oikai_listblocks( $atts );
  }
  return( bw_ret()); 
}

function blocks__help() {
  return( "Create links to related blocks" );
}

function blocks__syntax( $shortcode="blocks" ) {
  $syntax = array( "blocks" => bw_skv( null, "<i>block</i>", "block name list e.g. oik-block/wp" )
								 , "component" => bw_skv( null, "<i>ID</i>", "ID of plugin/theme for the blocks" )
                 );
  $syntax += _sc_classes();
  return( $syntax );
}
  
  


