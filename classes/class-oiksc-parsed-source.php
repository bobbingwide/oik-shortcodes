<?php // (C) Copyright Bobbing Wide 2014-2016
/**
 * oiksc_parsed_source class
 *
 * 
 *
 * The parsed source class deals with formatted output from the Dynamic API Reference
 * It's required to make the system faster. 
 * Parsing of wp_query() can take a very long time (40 secs)
 * - Because of its size: over 1700 LOC
 * - And complexity: actions and filters invoked
 * - and the time to lookup the permalinks for the APIs it calls 
 * 
 * So we need some methods to invoke that will allow us to 
 * create and view APIs, files and classes
 * storing the parse PHP for future use
 * 
 * We need methods to be able to detect whether or not we should reparse.
 * See also the comments in oik_register_parsed_source()

  bw_register_field( "_oik_sourceref", "noderef", "Source ref", array( "#type" => array( "oik_api", "oik_file", "oik_class" ) ) );
  bw_register_field( "_oik_parse_count", "number", "Parse count" );
  bw_register_field( "_oik_parsed_lines", "number", "Parsed lines" );
  bw_register_field( "_oik_parsed_tokens", "number", "Parsed tokens" );
  bw_register_field( "_oik_parsed_bytes", "number", "Parsed bytes" );
  
  
 *
 * _oik_parse_count - the number of times the file has been parsed.
 * These were the original design thoughts: 
 * 
 *  0 - never - initial state
 *  1 - once - meaning each class, method or function should have been found
 *  2 - twice - the caller/callee tree and hook associations for this file should now be known  
 *  more - meaning we should only need to parse this file if the file's timestamp is greater than the last update date (post_modified / post_modified_gmt )
  
 */
class oiksc_parsed_source {

 
 public $parse_count;    // The number of times this object has been parsed
 public $parsed_lines;
 public $parsed_tokens;
 public $parsed_bytes;
 public $po_post;
 
 /**
  * Create a new oik_parsed_source instance
  */ 
 function __construct() {
   $this->parse_count = 0;
   $this->parsed_lines = 0;
   $this->parsed_tokens = 0;
   $this->parsed_bytes = 0;
   $this->po_post = null;
 }
  
 /**
  * Load the parsed object referencing the source
  *
  * @param $post - the source post 
  * @return oiksc_parsed_source object or null  
  */  
 function load( $post ) {
   $po_post = bw_get_parsed_source_by_sourceref( $post->ID );
   if ( $po_post ) {
     // Good
   } else {
     // create the parsed object
     $po_post = bw_create_parsed_source( $post, null );
   } 
   $this->post = $po_post;
   return( $this );
 } 
}

/**
 * Return the ID of the source post 
 * 
 * @param post $parsed_source
 * @return ID - the ID of the source post
 */
function bw_get_sourceref( $parsed_source ) {
  $sourceref = get_post_meta( $parsed_source->ID, "_oik_sourceref", true );
  return( $sourceref );  
}

/**
 * Return the "parse_count" from the parsed source
 * This is actually the timestamp for the parsed file in whatever format that is..
 * 
 *
 */
function bw_get_parse_count( $parsed_source ) {
  $parse_count = get_post_meta( $parsed_source->ID, "_oik_parse_count", true );
  return( $parse_count );
}

/**
 * Get the file's timestamp
 *
 * @param string $file - partial file name e.g. classes/class-oiksc-parsed-source.php
 * @param string $component_type - "plugin" | "theme" 
 * @return string - file's timestamp - file modification time
 */
function bw_get_file_time( $file, $component_type ) {
  //bw_backtrace();
  oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
  $real_file = oiksc_real_file( $file, $component_type );
  $filemtime = filemtime( $real_file );
  return( $filemtime );
}

/**
 * Load the latest parsed source - if it is the latest
 *
 * We only return the parsed source if the source has been parsed more than once
 * AND the stored timestamp is the same as or later than the current timestamp for the file.
 *
 * If the stored timestamp is 1 then we've only parsed the file once. 
 * We won't be too sure whether or not the called APIs have been found. 
 * 
 * If the stored timestamp is earlier than the current this means the file has been updated
 * so we should re-parse it. 
 *
 * @param string $file - file name
 * @param string $component_type - "plugin" | "theme"
 * @param ID $id - ID of the post to have been parsed
 * @return post - the parsed source post object or null
 * 
 */
function bw_get_latest_parsed_source_by_sourceref( $file, $component_type, $post_id ) {
  // bw_trace2();
  $parsed_source = bw_get_parsed_source_by_sourceref( $post_id );
  if ( $parsed_source ) {
    $parse_count = bw_get_parse_count( $parsed_source );
    if ( $parse_count <= 1 ) {
      $parsed_source = null;
    } else {
      $file_time = bw_get_file_time( $file, $component_type );
      if ( $parse_count < $file_time ) {
        $parsed_source = null;
      }
    }
  }
  return( $parsed_source );
} 

/**
 * Get the parsed source object by sourceref
 *
 * @param $id - post ID of the post we're processing
 * @return post - the post object - there should be a maximum of one
 */
function bw_get_parsed_source_by_sourceref( $id ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array(); 
  $atts['post_type'] = "oik_parsed_source";
  $atts['meta_key'] = "_oik_sourceref" ;
  $atts['meta_value'] = $id;
  $atts['numberposts'] = 1;
  $posts = bw_get_posts( $atts );
  $post = bw_array_get( $posts, 0, null );
  return( $post );
}

/** 
 * Create the parsed object
 *
 * When we create the object for the first time we don't set the _oik_parse_count
 * since we need to perform two parses of the component in order to complete the call tree 
 * 
 * @param ID $source_post - the source post ID 
 * @param string $content - the parsed content
 * @return ID post ID of the created object
 *  
 */  
function bw_create_parsed_object( $source_post, $content ) {
  $post = array();
  $post['post_type'] = "oik_parsed_source";
  $post['post_title'] = "Parsed: $source_post"; 
  //$post['post_name'] = $source_post->post_name;
  $post['post_content'] = $content;
  $post['post_status'] = "publish";
  $post['comment_status'] = "closed";
  
  /* Set metadata fields */
  $_POST['_oik_sourceref'] = $source_post;
  $_POST['_oik_parse_count'] = 1;
  
  // We don't know the values for these since we got the information too late
  //bw_register_field_for_object_type( "_oik_parsed_lines", $post_type );
  //bw_register_field_for_object_type( "_oik_parsed_tokens", $post_type );
  //bw_register_field_for_object_type( "_oik_parsed_bytes", $post_type );
  
  $post_id = wp_insert_post( $post, TRUE );
  bw_trace2( $post_id );
  return( $post_id );
} 

/**
 * Update the parsed object
 *
 * The object has been parsed again so we need to update it
 * 
 * 
 * @param post $parsed_source - the current parsed object
 * @param string $content - the new content
 * @param string $filename - the parsed file full name
 * @return ID the post ID of the updated post
 */
function bw_update_parsed_object( $parsed_source, $content, $filename ) {
  bw_backtrace();
  bw_trace2();
  $parsed_source->post_content = $content;
  $_POST['_oik_parse_count'] = filemtime( $filename );
   
  wp_update_post( $parsed_source );
	return( $parsed_source->ID );
}
                                              
/**
 * Update the parsed source for this API
 *
 * The source has been parsed again and now we want to save the content
 * and update some other stuff
 * 
 * @param ID $post_id - The parsed object's post ID
 * @param string $content - the parsed content
 * @param string $filename - the parsed source full filename  
 * 
 */
function bw_update_parsed_source( $post_id, $content, $filename ) {
  $parsed_source = bw_get_parsed_source_by_sourceref( $post_id );
  if ( $parsed_source ) {
    $parsed_id = bw_update_parsed_object( $parsed_source, $content, $filename );
  } else {
    $parsed_id = bw_create_parsed_object( $post_id, $content );
  }
	return( $parsed_id );
}

/**
 * Navigate the parsed source
 *
 * Like oikai_navi_source(), but with pre-parsed source, this routine needs to cater for the new line characters in the parsed source
 * treating them as line breaks and also the pre tags that have been wrapped around the parsed source.
 * 
 * @param string $parsed_source - the parsed source
 */                                          
function oikai_navi_parsed_source( $parsed_source ) {
	//bw_backtrace();
	c( "parsed source" );
	//e( $parsed_source->post_content );
	$parsed_source = rtrim( $parsed_source, "\n " );
	$sources = explode( "\n", $parsed_source );
	//bw_trace2( $sources, "sources" );
	oik_require( "shortcodes/oik-navi.php" );
	$bwscid = bw_get_shortcode_id( true );
	$page = bw_check_paged_shortcode( $bwscid );
	$posts_per_page = 100; // get_option( "posts_per_page" );
	$count = count( $sources );
	$pages = ceil( $count / $posts_per_page );
	$start = ( $page-1 ) * $posts_per_page;
	$end = min( $start + $posts_per_page, $count ) -1;
	bw_navi_s2eofn( $start, $end, $count, bw_translate( "Lines: " ) );
	if ( $start  ) {
		e( "<pre>" );
	}
	for ( $i = $start; $i<= $end; $i++ ) {
		// $selection[] = $sources[$i];
		//$line = $i+1;
		//e( "$line " );
		e( $sources[$i] );
		e( "\n" );
	}
	if ( $end < $count ) {
		e( "</pre>" );
	}
	//oikai_syntax_source( $selection, 1 ); 
	bw_navi_paginate_links( $bwscid, $page, $pages );
}
