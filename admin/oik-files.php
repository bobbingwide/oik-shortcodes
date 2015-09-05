<?php // (C) Copyright Bobbing Wide 2014

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
    $contents_arr = oiksc_contents_strip_docblock( $contents_arr, $api->getStartLine(), $api->getEndLine() );
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

function oiksc_contents_strip_docblock( $contents_arr, $start, $end ) {
  oiksc_contents_strip( $contents_arr, $start, $end );
  return( $contents_arr );
}

/**
 * Convert the stripped out class or function into a link to the class, method, or function
 */
function oiksc_contents_link( $contents_arr, $api ) {
  //print_r( $api );
  $index = $api->getStartLine();
  $line = "/* ";
  $api_name = $api->getApiName();
  oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
  if ( $api->methodname ) {
    $line .= " function ";
    $post = oiksc_get_oik_api_byname( $api->getApiName() );
  } else {
    $line .= " class ";
    $post = oikai_get_oik_class_byname( $api->classname );
  }
  if ( $post ) {
    $post_id = $post->ID;
    $line .= retlink( null, get_permalink( $post_id ), get_the_title( $post_id ) );
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
 * @param bool $force - true if we need to rebuild
 * @return string - parsed source or null 
 */
function oiksc_display_oik_file( $file, $component_type, $file_id, $force=false ) {
   oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
  if ( $file_id && !$force ) {
    oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
    //$parsed_source = bw_get_parsed_source_by_sourceref( $file_id );
    $parsed_source = bw_get_latest_parsed_source_by_sourceref( $file, $component_type, $file_id );
  } else {
    $parsed_source = null;
  }
  
  if ( $parsed_source ) {
    oikai_navi_parsed_source( $parsed_source->post_content );
  } else { 
    oik_require( "admin/oik-apis.php", "oik-shortcodes" );
    $apis = oiksc_list_file_functions2( $file, $component_type );
    //var_dump( $apis );
    $contents_arr = oiksc_load_file( $file, $component_type );
    $contents_arr = oiksc_listfile_strip_apis( $contents_arr, $apis );
    $contents = oiksc_listfile_create_dummy_function( $contents_arr, $component_type );
  
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
  
    if ( !function_exists( "wp_enqueue_style" ) ) {
      function wp_enqueue_style() {} 
    } 
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
    bw_update_parsed_source( $file_id, $content, $file );
    
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
  bw_trace2( $post, "post" );
  if ( !$post ) {
    $post_id = oiksc_create_oik_file( $plugin, $file );     
  } else {
    $post_id = $post->ID;
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
  bw_trace2( $post, "oik_file?" );
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
function oiksc_create_oik_file( $plugin, $file ) {
  bw_backtrace();
  $post_title = oiksc_oik_file_post_title( $file );
  $post = array( 'post_type' => 'oik_file'
               , 'post_title' => $post_title
               , 'post_name' => $file
               , 'post_content' => "<!--more -->[file][bw_fields]"
               , 'post_status' => 'publish'
               , 'comment_status' => 'closed'
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
  bw_trace2( $post_id );
  return( $post_id );
}

/**
 * Update an "oik_file" post type
 *
 * @param object $post - the oik_file post object
 * @param ID $plugin - the plugin ID 
 * @param string $file - the file name
 * @return ID 
 */
function oiksc_update_oik_file( $post, $plugin, $file ) {
  $post->post_title = oiksc_oik_file_post_title( $file );
  /* Set metadata fields */
  $_POST['_oik_file_name'] = $file;
  $_POST['_oik_api_plugin'] = $plugin;
  bw_trace2();
  //$_POST['_oik_file_passes'] = ; // Apply this update separately
  wp_update_post( $post );
}

