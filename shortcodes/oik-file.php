<?php // (C) Copyright Bobbing Wide 2014-2017

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
  oik_require( "includes/bw_posts.php" );
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
          //$filename = oik_pathw( $file, $plugin, $component_type );
					$filename = array( $file, $component_type, $plugin );
        }	else {
					echo "eh? $plugin_id, $component_type, $file" . PHP_EOL;
					gob();
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
  //if ( $filename ) {
  //} 
	//print_r( $filename );
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
	oiksc_autoload();
	$file = bw_array_get_from( $atts, "file,0", null );
	$full_file = bw_array_get( $atts, "1", null );
	oik_require( "admin/oik-files.php", "oik-shortcodes" );
	if ( !$file ) {
		list( $file, $component_type, $plugin ) = oikai_get_current_post_filename();
		//echo "$file, $component_type, $plugin" . PHP_EOL;
		$file_id = bw_global_post_id(); 
		if ( $file ) {
			oiksc_display_oik_file_or_folder( $file, $component_type, $file_id, $plugin );
		} else {
			bw_trace2( $file, "Unable to determine file to display" );
		}
	} else {
		if ( $full_file ) {
			
			oikai_pragmatic_link( $file, $full_file  );
		} else {
			$files = bw_as_array( $file );
			oik_require( "shortcodes/oik-filelink.php", "oik-shortcodes" );
			$file_id = oikai_list_files_byname( $files, array( "uo" => "," ) );
			//oik_require( "shortcodes/oik-parent.php" );
			//bw_post_link( $file_id );
		}	
	}
	return( bw_ret()); 
}

/**
 * Help hook for file shortcode
 */
function file__help( $shortcode="file" ) {
  return( "Display reference for a file" );
}

/**
 * Syntax hook for file shortcode
 */

function file__syntax( $shortcode="file" ) {
  $syntax = array( "file" => bw_skv( null, "<i>file</i>", "plugin file name" )
                 );
  return( $syntax );
}

/**
 * Create a pragmatic link for a file
 * 
 * Processing depends on the $file and we may also need the $full_file
 * in some instances ( perhaps we pass it on the link as a query parameter
 * to help if and when we get a 404
 * 
 * @TODO Revisit when the plugin or theme name is used as part of the file's title and post_meta key _oik_api_file
 * 
 * @param string $file relative file name with slash separators
 * @param string $full_file the full file name with platform specific separators. May be a resolved symlinked file namee
 */
function oikai_pragmatic_link( $file, $full_file=null ) {
	$parts = explode( "/", $file );
	switch ( $parts[0] ) {
		case "wp-content":
			array_shift( $parts );	// Remove the wp-content
			array_shift( $parts );  // Remove the plugins/themes/mu-plugins/upgrade/uploads etc
			array_shift( $parts );  // Remove the plugin or theme folder
			break;
		
		default:
			// WordPress file so we don't need to strip anything
			
	}
	$adjusted_file = implode( "/",  $parts );
	$url = site_url( "oik_file/" . $adjusted_file ); 
	alink( null, $url, $file );
}  
 
