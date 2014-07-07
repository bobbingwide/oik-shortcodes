<?php // (C) Copyright Bobbing Wide 2014

/**
 * Report the current status of the system
 *
 * @param bool $detail - show details
 * 
 */
function oikai_api_status( $detail=false ) {
  oikai_api_status_functions();
  oikai_api_status_classes();
  oikai_api_status_files( $detail );
  oikai_api_status_constants();
  //oikai_api_status_plugins();
}

/**
 * Report the current status of the system with timing
 *
 * These functions may be used in some fancy front-end dashboard
 * so we'll need them to be usable to query values as well as display them
 * How and when we show "details" is an interesting question
 */  
function oikai_api_status_timer( $detail=false, $text=null ) {
  $elapsed = timer_stop( false, 6 );
  h3( $text );
  p( "Load time (secs): $elapsed " ); 
  oikai_api_status( $detail );
  bw_flush();
  timer_start();
} 

/**
 * Display the status of "functions" - both PHP internal and user defined
 */
function oikai_api_status_functions() {
  $defined_functions = get_defined_functions(); 
  $internal = bw_array_get( $defined_functions, "internal", null );
  $count = count( $internal );
  p( "Internal functions: $count " );
  $user = bw_array_get( $defined_functions, "user", null );
  $count = count( $user );
  p( "User functions: $count " );  
}

function oikai_api_status_classes_details( $classes ) {
  if ( $classes ) {
    //bw_trace2( $classes );
    asort( $classes );
    stag( "table" );
    foreach ( $classes as $key => $class ) {
      stag( "tr" );
      stag( "td" );
      oikai_handle_reference_type_class( $class. "::", "class" );
      etag( "td" );
      etag( "tr" );
    }
    etag( "table" );
  }
}  


function oikai_api_status_classes( $detail=false ) {
  $declared_classes = get_declared_classes(); 
  $count = count( $declared_classes );
  p( "Classes: $count " );
  //oikai_api_status_classes_details( $declared_classes );
}

  
/**
 * Print out the details of the PHP user constants
 *
 * We don't print out all the PHP ones since there are far too many
 *
 * @param array $userc - user constants
 */
function oikai_api_status_constants_details( $userc ) { 
  if ( $userc ) {
    ksort( $userc );
    foreach ( $userc as $key => $value ) {
      //bw_tablerow( $key, $value );
      if ( substr( $key, 0,2 ) != "DB" ) { 
        p( "$key: $value " );
      }  
    }     
  }
}

                                        
function oikai_api_status_constants() {
  $constants = get_defined_constants( true );
  $userc = bw_array_get( $constants, "user", null );
  $count = count( $userc );
  p( "User constants: $count" );
  // oikai_api_status_constants_details( $userc );
}

function oikai_api_status_files_details( $files ) {
  if ( $files ) {
    ksort( $files );
    foreach ( $files as $key => $file ) {
      p( "$key: $file " );
    }     
  }
}


function oikai_api_status_files( $detail = false ) {
  $included_files = get_included_files();
  $count = count( $included_files );
  p( "Included files: $count" );
  if ( $detail ) {
    oikai_api_status_files_details( $included_files );
  }

}

/**
 * Get plugin status. 
 *
 * Not available in oik-batch.
 * @TODO We need to know if DB functions are available
 * 
 */
function oikai_api_status_plugins( $detail=false ) {
  $plugins = oikai_query_plugins();
  $count = oikai_query_plugin_count( $plugins ); 
  p( "Active plugins: $count" );
  return( $plugins );  
}

function oikai_query_plugin_count( $plugins=null ) {
  if ( !$plugins ) {
    $plugins = oikai_query_plugins();
  }   
  $count = count( $plugins );
  return( $count );
}  

function oikai_query_plugins() {
  if ( PHP_SAPI == "cli" ) {
    $plugins = array( "oik-batch" );
  } elseif ( bw_is_wordpress() ) {  
    oik_require( "admin/oik-depends.inc" );
    $plugins = bw_get_active_plugins();
  } else {
    $plugins = array( "oik-batch" );
  }
  return( $plugins );
}
