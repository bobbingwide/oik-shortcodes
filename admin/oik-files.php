<?php // (C) Copyright Bobbing Wide 2014-2016

/**
 * Strip all the classes, methods and APIs from the file
 *
 * @param array $contents_arr - the file contents
 * @param array $apis - array of oiksc_token_objs
 * @return array $contents_arr - the stripped file
 */
function oiksc_listfile_strip_apis( $contents_arr, $apis ) {
  foreach ( $apis as $api ) {
    $contents_arr = oiksc_contents_strip( $contents_arr, $api->getStartLine(), $api->getEndLine() ); 
    $contents_arr = oiksc_contents_strip_docblock( $contents_arr, $api->getStartLine(), $api->getEndLine(), $api );
    $contents_arr = oiksc_contents_link( $contents_arr, $api );
    //print_r( $contents_arr );
  }
  return( $contents_arr );
}

/**
 * Strip the lines from start to end
 *
 * Taking into account that the array index starts from 0
 * 
 */
function oiksc_contents_strip( $contents_arr, $start, $end ) {
  //echo "Stripping $start to $end" . PHP_EOL;
  for ( $index = $start; $index<= $end; $index++ ) {
    $contents_arr[ $index-1 ] = "";
  }
  //print_r( $contents_arr );
  return( $contents_arr );
}

/**
 * Strip the preceeding docblock
 * 
 * We know where the api starts and the size of the docblock
 * so we could attempt to  determine which lines to strip
 * e.g.
 * docblock lines = 5
 *  
 * start = 10
 * ....ddddds
 * But this assumes that the docblock actually starts there.
 * We either need to match it up by checking the first line
 * OR obtain the start information from the docblock_token. 
 * 
 */
function oiksc_contents_strip_docblock( $contents_arr, $start, $end, $api ) {
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
  $docblock = $api->docblock;
  $docblock_lines = explode( "\n", $docblock ); 
  $docblock_size = count( $docblock_lines );
  //$docblock_start = $start - $docblock_size;
  //bw_trace2( $docblock_size, "docblock_size", false );
  $docblock_start = $api->docblock_token[2];
  $docblock_end = $docblock_start + $docblock_size - 1; 
  $contents_arr = oiksc_contents_strip( $contents_arr, $docblock_start, $docblock_end );
  return( $contents_arr );
}

/**
 * Convert the stripped out class or function into a link to the class, method, or function
 *
 * Here we create special lines which are detected in the output so that the links are treated as links
 *
 * @param array $contents_arr
 * @param string $api
 * @return updated contents_arr
 */
function oiksc_contents_link( $contents_arr, $api ) {
	//print_r( $api );
	bw_trace2( null, null, true, BW_TRACE_DEBUG );
	$post_id = null;
	$index = $api->getStartLine();
	$line = "/*";
	$api_name = $api->getApiName();
	oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
	oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
	if ( $api->methodname ) {
		$line .= " function ";
		$post = oikai_get_oik_api_byname( $api->getApiName() );
		$post_id = $post[0];
	} else {
		$line .= " class ";
		$post = oikai_get_oik_class_byname( $api->classname );
		if ( $post ) {
			$post_id = $post->ID;
		}
		
	}
	if ( $post_id ) {
		$line .= retlink( null, get_permalink( $post_id ), get_the_title( $post_id ) );
	} else {
		$url = site_url( "oik_api/" . $api_name );
		$line .= retlink( null, $url, $api_name );  
	}
	 
	$line .= " */\n";
	$contents_arr[$index-1] = $line;
	return( $contents_arr );
}

/**
 * Create dummy function for the file
 *
 * @param $contents_arr
 * @param $component_type  
 */
function oiksc_listfile_create_dummy_function( $contents_arr, $component_type ) {
  oik_require( "classes/class-oiksc-function-loader.php", "oik-shortcodes" );
  oik_require( "classes/class-oiksc-file-loader.php", "oik-shortcodes" );
  $thisfile = new oiksc_file_loader( $contents_arr, $component_type );
  //var_dump( $thisfile );
  return( $thisfile->contents );
}

/**
 * Display oik_file logic
 *
 * Display the logic of the file excluding classes and their methods and APIs.
 * @TODO - Remove unnecessary requires.
 * @TODO - Add caller/callee tree - or does this go elsewhere
 *
 * @param string $file - the name of the file
 * @param string $component_type
 * @param ID $file_id - the ID of the file post
 * @param string $component_slug - plugin or theme slug
 * @param bool $force - true if we need to rebuild
 * @return string - parsed source or null 
 */
function oiksc_display_oik_file( $file, $component_type, $file_id, $component_slug, $force=false ) {
   oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
	 bw_trace2();
	 bw_backtrace();
	 
  if ( $file_id ) {
    oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
    //$parsed_source = bw_get_parsed_source_by_sourceref( $file_id );
    //$parsed_source = bw_get_latest_parsed_source_by_sourceref( $file, $component_type, $file_id, $component_slug );
		
		$oiksc_parsed_source = oiksc_parsed_source::instance();
		$parsed_source = $oiksc_parsed_source->get_latest_parsed_source_by_sourceref( $file, $component_type, $file_id, $component_slug );
  } else {
    $parsed_source = null;
  }
  
  if ( $parsed_source && !$force ) {
    oikai_navi_parsed_source( $parsed_source->post_content );
  } else { 
    oik_require( "admin/oik-apis.php", "oik-shortcodes" );
    $apis = oiksc_list_file_functions2( $file, $component_type, $component_slug );
    //var_dump( $apis );
    $contents_arr = oiksc_load_file( $file, $component_type, $component_slug );
    $contents_arr = oiksc_listfile_strip_apis( $contents_arr, $apis );
    $contents = oiksc_listfile_create_dummy_function( $contents_arr, $component_type );
		
		$needs_processing = $oiksc_parsed_source->is_parsing_necessary( $contents );
  
    /**
     * And now we can parse the $tempfile object
       and do oikai_easy_tokens();
     
       [function_obj] =>
       [dummy_function_name] => oiksc_dummy_function_0
       [filename] => C:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\listfile.php
       [plugin] =>
       [tempnam] => C:\Users\Herb\AppData\Local\Temp\oik57CF.tmp
     */
    oik_require( "bobbfunc.inc" );
    //require( ABSPATH . WPINC . "/link-template.php" );
    oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
  
    //if ( !function_exists( "wp_enqueue_style" ) ) {
    //  function wp_enqueue_style() {} 
    //} 
    //$contents_arr = oiksc_load_file( $tempfile->tempnam );
    
    
      
    add_action( "oikai_handle_token_T_STRING", "oikai_add_callee" );
    add_action( "oikai_record_association", "oikai_record_association", 10, 2 ); 
    add_action( "oikai_record_hook", "oikai_record_hook", 10, 3 );
       
    oikai_syntax_source( $contents, 0, false  );
    
    // parsed_source was null so now we can update it? 
    // well... only if we also update the other bits
    // otherwise it gets tits upped.
    /** 
     * Update the oik_parsed_source
     */
    $content = bw_ret();
    oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
		$real_file = oiksc_real_file( $file, $component_type, $component_slug );
    //bw_update_parsed_source( $file_id, $content, $real_file );
		
    $parsed_source_id = $oiksc_parsed_source->update_parsed_source( $file_id, $content, $real_file );   //   $files[$file]
    
    oikai_save_callees( $file_id );
    oiksc_save_hooks( $file_id );
    oiksc_save_associations( $file_id );
    
    e( $content ); 
  } 
  return( $parsed_source ); 
}

/**
 * Create or update an "oik_file" post_type
 * 
 * @param ID $plugin 
 * @param string $file
 * @return ID 
 */
function _oikai_create_file( $plugin, $file ) {
  p( "Creating file: $file" );
  bw_flush();
  $post = oiksc_get_oik_file_byname( $plugin, $file );
	$parent = oiksc_create_ancestry( $post, $plugin, $file );
  bw_trace2( $post, "post", true, BW_TRACE_DEBUG );
  if ( !$post ) {
    $post_id = oiksc_create_oik_file( $plugin, $file, $parent );     
  } else {
    $post_id = $post->ID;
		$post->post_parent = $parent;
    oiksc_update_oik_file( $post, $plugin, $file );
  }
  global $oikai_post_id;
  $oikai_post_id = $post_id;
  
   
  /**
   * We need to ensure that the first instance of the function name does not appear as a call to the function.
   * To achieve this we need to let the pseudo method know that it's part of the class.
   */
  //$class = oikai_get_class( $api );
  //p( "Class $class" );
  //bw_context( "classname", $class );
  //bw_context( "variable", "this" );
  // bw_context( "operator", "function" );
  
  //oiksc_build_callees( null, $file, $plugin );
  // oikai_save_callees( $post_id );
  
  // oiksc_save_hooks( $post_id );
  // oiksc_save_associations( $post_id );
  return( $post_id );
}


/**
 * Return the "oik_file" post for the given plugin ID and file name
 *
 * If the plugin is not specified we just look for the file
 * 
 * 
 * @param ID $plugin - the plugin ref
 * @param string $file - the file name. e.g. admin/oik-files.php 
 * @return post - the oik_file post or null
 */
function oiksc_get_oik_file_byname( $plugin, $file ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array();
  $atts['post_type'] = "oik_file" ;
  $atts['numberposts'] = 1;
  $meta_query = array();
  if ( $plugin ) {
    $meta_query[] = array( "key" => "_oik_api_plugin", "value" => $plugin );
  }
  $meta_query[] = array( "key" => "_oik_file_name", "value" => $file ); 
  $atts['meta_query'] = $meta_query;
  $posts = bw_get_posts( $atts );
  $post = bw_array_get( $posts, 0, null );
  bw_trace2( $post, "oik_file?", true, BW_TRACE_VERBOSE );
  return( $post );
}

/**
 * Return the post_id for an oik_file
 *
 * Used to set the _oik_fileref
 * 
 * @param ID $plugin - the plugin ID
 * @param string $file - file name
 * @return ID - the post ID of the oik_file or null 
 */
function oiksc_get_oik_fileref( $plugin, $file ) {
  $post = oiksc_get_oik_file_byname( $plugin, $file );
  if ( $post ) {
    $post_id = $post->ID;
  } else { 
    $post_id = null;
  }
  return( $post_id );
}  



/** 
 * Create the post_title for an "oik_file"
 *
 * @param string $file - the file name e.g. admin/oik-files.php 
 * @return string - the post_title
 */
function oiksc_oik_file_post_title( $file ) {
  return( $file );
} 

/**
 * Programmatically create an oik_file record for a selected plugin's file
 *
 * Note: We don't allow comments on files. 
 * 
 * @param ID $plugin - the ID of the plugin for which this file is being created
 * @param string $file - the implementing filename within the plugin e.g. "oik/shortcodes/oik-codes.php"
 * @return ID - the post ID of the newly created oik_api record
 */
function oiksc_create_oik_file( $plugin, $file, $parent ) {
  //bw_backtrace();
  $post_title = oiksc_oik_file_post_title( $file );
  $post = array( 'post_type' => 'oik_file'
               , 'post_title' => $post_title
               , 'post_name' => oiksc_get_oik_file_post_name( $file )
               , 'post_content' => "<!--more -->[file][bw_fields]"
               , 'post_status' => 'publish'
               , 'comment_status' => 'closed'
							 , 'post_parent' => $parent
               );
  /* Set metadata fields */
  //oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
  $_POST['_oik_file_name'] = $file;
  $_POST['_oik_api_plugin'] = $plugin;
  //$_POST['_oik_file_passes'] = 0; // We've created the file but not yet parsed it. So passes = 0;
  $_POST['_oik_file_deprecated_cb'] = false;
  /* We don't know these values yet:
     _oik_api_calls
     _oik_api_hooks
  */
  $post_id = wp_insert_post( $post, TRUE );
  bw_trace2( $post_id, "post_id", true, BW_TRACE_DEBUG );
  return( $post_id );
}

/**
 * Update an "oik_file" post type
 *
 * Setting the post_name is required to update existing posts
 *
 * @param object $post - the oik_file post object
 * @param ID $plugin - the plugin ID 
 * @param string $file - the file name
 * @return ID 
 */
function oiksc_update_oik_file( $post, $plugin, $file ) {
  $post->post_title = oiksc_oik_file_post_title( $file );
	$post->post_name = oiksc_get_oik_file_post_name( $file );
  /* Set metadata fields */
  $_POST['_oik_file_name'] = $file;
  $_POST['_oik_api_plugin'] = $plugin;
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
  //$_POST['_oik_file_passes'] = ; // Apply this update separately
  wp_update_post( $post );
}

/**
 * Determine the post_parent for an oik_file 
 *
 * Here we will attempt to correct the hierarchy of oik_file posts
 * so that they appear nicely structured.
 * 
 * Assuming we will eventually process every directory
 * then we probably only need to handle one parent at a time
 * BUT that assumes we'll process a file in each directory in the tree
 * 
 * Processing depends on the values of the parameters passed
 * 
 * $post  | $file 			      | post_parent | Processing
 * ------ | -------------     | ----------  | -------------- 
 * 0      | filename.ext      | n/a         | The post_parent will be 0
 * 0      | path/filename.ext |	n/a         | Find the post_id for dirname( $file )
 * set    | filename.ext      | should be 0 | force it to 0
 * set    | path/filename.ext | 0           | Find the post_id for dirname( $file ) 
 *
 * @param post|null $post an existing post object or null
 * @param ID $plugin the ID of the plugin or theme
 * @param string $file the file name including the path ( forward slashes expected )
 * @return ID the post_parent ID, which may be 0
 */
function oiksc_create_ancestry( $post, $plugin, $file ) {
	//bw_backtrace();
	//bw_trace2();
	$post_parent = 0;
	if ( $post ) {
		$found_parent = oiksc_file_should_have_parent( $file, $post->post_parent );
	} else {
		$found_parent = oiksc_file_should_have_parent( $file, null );
	} 
	if ( null !== $found_parent ) {
		$post_parent = $found_parent; 
	} else {
		$post_parent = _oikai_create_file( $plugin, dirname( $file ) );
	}
	bw_trace2( $post_parent, "post_parent", false, BW_TRACE_DEBUG );
	return( $post_parent );
}

/**
 * Deterimine the right value for post_parent
 *
 * @param string $file - the file name
 * @param ID $current_parent 
 * @return found_parent - null when need to find one otherwise the required ID
 */
function oiksc_file_should_have_parent( $file, $current_parent ) {
	//bw_trace2();
	if ( false === strpos( $file, "/" ) ) {
		$found_parent = 0;
	} elseif ( 0 == $current_parent ) {
		$found_parent = null;
	} else {
		$found_parent = $current_parent;
	}
	return( $found_parent );
}

/**
 * Display an oik_file or folder
 *
 * When the file is a directory we need to display the children
 * Otherwise display the details of the file
 *  
 * @param string $file 
 * @param string $component_type
 * @param ID $file_id
 * @param string $plugin_slug
 * @param bool $force 
 */ 
function oiksc_display_oik_file_or_folder( $file, $component_type, $file_id, $plugin, $force=false ) {
	oiksc_set_global_plugin_filename( $plugin, $file );
  oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
	$real_file = oiksc_real_file( $file, $component_type, $plugin );
	if ( file_exists( $real_file ) ) {
		if ( is_dir( $real_file ) ) {
			//p( "This is a folder" );
			oik_require( "shortcodes/oik-tree.php" );
			e( bw_tree( array( "post_parent" => $file_id ) ) );
		} else {
			oiksc_display_oik_file( $file, $component_type, $file_id, $plugin, $force );
			if ( $file_id ) {
				oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
				oikai_list_callers_callees( $file_id );
			} 
		}
	} else {
		p( "File: $file ( component type: $component_type ) does not exist" );
	}
}

/**
 * Set the global plugin and filename
 * 
 * @TODO Remove dependency on globals
 * 
 * @param string $component_name - Component name of the plugin or theme. May also be 'wordpress'
 * @param string $file_name - File name - NOT fully qualified
 */
function oiksc_set_global_plugin_filename( $component_name, $file_name ) {
  global $plugin, $filename;
	$plugin = $component_name;
	$file = $file_name;
}

/**
 * Choose a post_name
 *
 * @param string $file
 * @return string the last part of the file name
 */ 
function oiksc_get_oik_file_post_name( $file ) {
	$slug = basename( $file );
	return( $slug );
}
