<?php // (C) Copyright Bobbing Wide 2014

/** 
 * Get the file name from the current post
 *
 * If we are able to get an _oik_file_name
 * AND an _oik_api_plugin
 * which gives us a $plugin name
 * then we can construct the file name to return
 *
 * @return array|null  - array( $filename, $component_type ) - the fully qualified file name to load and component type or null
 */ 
function oikai_get_current_post_filename() {
  oik_require( "includes/bw_posts.inc" );
  $filename = null; 
  $post_id = bw_global_post_id(); 
  bw_trace2( $post_id ); 
  if ( $post_id ) {
    $file = get_post_meta( $post_id, "_oik_file_name", true );
    if ( $file ) {
      $plugin_id = get_post_meta( $post_id, "_oik_api_plugin", true );
      if ( $plugin_id ) {
        $plugin = get_post_meta( $plugin_id, "_oikp_slug", true );
        // It may be a theme so try for _oikth_slug
        if ( !$plugin ) {
          $plugin = get_post_meta( $plugin_id, "_oikth_slug", true );
          if ( $plugin ) {
            $component_type = "theme";
          }
        } else {
          $component_type = "plugin";
        }
        if ( $plugin ) {
          oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
          $filename = oik_pathw( $file, $plugin, $component_type );
        }
      } else {
        bw_trace2( $plugin_id, "Unable to determine plugin slug from ID" );
      }
    } else {
      bw_trace2( $post_id, "Unable to determine file name from ID" );
    }
  } else {
    bw_trace2( $post_id, "Global post id not set" );
  }
  if ( $filename ) {
    $filename = array( $filename, $component_type );
  } 
  return( $filename );
}

/** 
 * Implement [file] shortcode for oik-files
 *
 * If the file= parameter is not specified we assume the shortcode is being invoked for an oik_file post type
 * so determine the details from the global post.
 * 
 *
 * @param array $atts - shortcode parameters
 * @param string $content - not expected BUT we could pretend we had a file I suppose
 * @param string $tag - shortcode name
 * @return string HTML etc to complete the file reference
 */
function oikai_fileref( $atts=null, $content=null, $tag=null ) {
  $file = bw_array_get_from( $atts, "file,0", null );
  oik_require( "admin/oik-files.php", "oik-shortcodes" );
  if ( !$file ) {
    list( $file, $component_type ) = oikai_get_current_post_filename();
    $file_id = bw_global_post_id(); 
    if ( $file ) {
      oiksc_display_oik_file( $file, $component_type );
    } else {
      bw_trace2( $file, "Unable to determine file to display" );
    }
    if ( $file_id ) {
      oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
      oikai_list_callers_callees( $file_id );
    } 
  } else {
    $files = bw_as_array( $file );
    oik_require( "shortcodes/oik-filelink.php", "oik-shortcodes" );
    $file_id = oikai_list_files_byname( $files, array( "uo" => "," ) );
    //oik_require( "shortcodes/oik-parent.php" );
    //bw_post_link( $file_id );
  } 
  return( bw_ret()); 
}

function file__help( $shortcode="file" ) {
  return( "Display reference for a file" );
}

function file__syntax( $shortcode="file" ) {
  $syntax = array( "file" => bw_skv( null, "<i>file</i>", "plugin file name" )
                 );
  return( $syntax );
}
