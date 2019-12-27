<?php // (C) Copyright Bobbing Wide 2012-2017
/** 
 * Automagically determine the file list
 *
 * If no functions have been passed then we can determine the list of APIs from the context of the current post
 *
 * post_type      action
 * ----------     -------------
 * oik-plugin     find ALL the files linked to the plugin through "_oik_api_plugin"
 * oik-theme      find ALL the files linked to the theme through 
 * oik_api        find all the files that this API includes  @TODO
 * oik-file       find all the files that this file includes @TODO
 * other          see if we can find a _plugin_ref field and use that 
 *
 * @param array $atts - shortcode parameters
 */
function oikai_listfiles( $atts ) {
  global $post;
  if ( $post ) {
    if ( $post->post_type == "oik-plugins" ) {
      oik_require( "shortcodes/oik-navi.php" ); 
      $atts['post_type'] = "oik_file"; 
      $atts['meta_key' ] = "_oik_api_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
      //e( bw_shortcode_event( $atts, null, "bw_list" ) );
    } elseif ( $post->post_type == "oik-themes" ) {
      oik_require( "shortcodes/oik-navi.php" ); 
      $atts['post_type'] = "oik_file"; 
      $atts['meta_key' ] = "_oik_api_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
    } elseif ( $post->post_type == "oik_file" ) {
      //oikai_list_callers_callees( $post->ID );
    } else {  
      $id = get_post_meta( $post->ID, "_plugin_ref", true );
      if ( $id ) {
			
				oik_require( "shortcodes/oik-navi.php" ); 
        $atts['post_type'] = "oik_file"; 
        $atts['meta_key' ] = "_oik_api_plugin";
        $atts['meta_value'] = $id;
        e( bw_navi( $atts ) );
      }
    }
    
  } else { 
    // Don't expect this! 
  }
}

/**
 * Implement [files] shortcode - links to a list of APIs
 * 
 * Examples:
 * [files file=functionname]
 * [files func=functionname]   - not yet implemented! 
 * 
 * alternatively
 * [files bbboing_sc fiddle] will return the values in $atts[0] and $atts[1] 
 * so we can get the file names from there!
 *
 * @param array $atts - shortcode parameters
 * @param string $content - not expected
 * @param string $tag - shortcode tag
 * @return string - the generated HTML
 */
function oikai_filelink( $atts=null, $content, $tag ) {
  $file = bw_array_get( $atts, "file", null );
  $files = bw_as_array( $file );
  $unkeyed = bw_array_get_unkeyed( $atts );
  $files = array_merge( $files, $unkeyed );
  
  bw_trace2( $files, "files" );
  
  $class = bw_array_get( $atts, "class", "bw_file" );
  if ( count( $files) ) {
    oikai_list_files_byname( $files, $atts );
  } else {
    oikai_listfiles( $atts ); 
  }
  return( bw_ret()); 
}

/**
 * Load the APIs listed in the array
 * 
 * The _oik_api_name stores the file name. For methods this includes the class name. e.g. Class::method
 * 
 * @param array $files - array of file names
 * @return array $posts - array of posts found
 */
function oikai_get_oik_files_byname( $files ) {
  oik_require( "includes/bw_posts.php" );
  $posts = bw_get_by_metakey_array( "oik_file", "_oik_file_name", $files );
  return( $posts );
}

/**
 * Produce a list of files as links
 * 
 * @param array $files - array of file names
 * @param array $atts - shortcode parameters
 */
function oikai_list_files_byname( $files, $atts ) {
  $posts = oikai_get_oik_files_byname( $files );
  if ( $posts ) {
    oik_require( "shortcodes/oik-list.php" );
    bw_simple_list( $posts, $atts );
  } else {
    p( "Cannot find file(s):" . implode( ",", $files ) );
  } 
}  

function files__help() {
  return( "Link to files definitions" );
}

function files__syntax( $shortcode="files" ) {  
  $syntax = array( "file" => bw_skv( null, "<i>filename</i>", "File name e.g. shortcodes/oik-filelink.php" )
                 );
  $syntax += _sc_classes( false );
  return( $syntax );
}
  
  


