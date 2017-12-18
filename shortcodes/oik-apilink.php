<?php // (C) Copyright Bobbing Wide 2012-2017
/** 
 * Automagically determine the API list
 *
 * If no functions have been passed then we can determine the list of APIs from the context of the current post
 *
 * post_type     | action
 * ----------    | -------------
 * oik-shortcodes| find the API from "_oik_sc_func"
 * oik-plugins   | find ALL the apis linked to the plugin through "_oik_api_plugin"
 * oik-themes    | find ALL the apis linked to the theme through "_oik_api_plugin"
 * oik_class     | find ALL the methods linked to the class through "_oik_api_class"
 * oik_api       | find all the apis that this API calls and is called by
 * other         | see if we can find a _plugin_ref field and use that  
 *
 * @param array $atts - shortcode parameters
 */
function oikai_listapis( $atts ) {
  global $post;
  if ( $post ) {
    oik_require( "shortcodes/oik-navi.php" ); 
    if ( $post->post_type == "oik_shortcodes" ) {
      $id = get_post_meta( $post->ID, "_oik_sc_func", true );
      $atts = array();
      $atts['post_type'] = "oik_api";
      $atts['include'] = $id;
      e( bw_navi( $atts ) );
    } elseif ( $post->post_type == "oik-plugins" ) {
      $atts['post_type'] = "oik_api"; 
      $atts['meta_key' ] = "_oik_api_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
      //e( bw_shortcode_event( $atts, null, "bw_list" ) );
    } elseif ( $post->post_type == "oik-themes" ) {
      $atts['post_type'] = "oik_api"; 
      $atts['meta_key' ] = "_oik_api_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
      //e( bw_shortcode_event( $atts, null, "bw_list" ) );
    } elseif ( $post->post_type == "oik_class" ) {
      $atts['post_type'] = "oik_api"; 
      $atts['meta_key' ] = "_oik_api_class";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
    } elseif ( $post->post_type == "oik_api" ) {
      oikai_list_callers_callees( $post->ID );
    } else {  
      $id = get_post_meta( $post->ID, "_plugin_ref", true );
      if ( $id ) {
        $atts['post_type'] = "oik_api"; 
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
 * List the callers and callees for the selected oik_api including information about hooks
 *
 * Sections displayed:
 *
 * - Called by - the callers of this API
 * - Invoked by - the hooks which cause this function to be invoked
 * - Calls - the functions this API calls
 * - Call hooks - the action and filters this API invokes 
 * 
 * @param ID $post_id - the ID of the oik_api
 */
function oikai_list_callers_callees( $post_id ) {
	$api_name = get_post_meta( $post_id, "_oik_api_name", true );
	bw_trace2( $api_name, "API name" );
	
  sdiv( "bw_callers" );
  h3( "Called by", "bw_callers" );
  oikai_list_callers( $post_id, $api_name );
  ediv();
  sdiv( "bw_invokers" );
  h3( "Invoked by", "bw_invokers" );
  oikai_list_invokers( $post_id, $api_name );
  ediv();
  sdiv( "bw_callees" );
  h3( "Calls", "bw_callees" );
  oikai_list_callees( $post_id );
  ediv();
  sdiv( "bw_invokes" );
  h3( "Call hooks", "bw_invokes" );
  oikai_list_hooks( $post_id );
  ediv();
}  

/**
 * List callers of the selected API
 *
 * The possible callers now includes "oik_file" as well as "oik_api"
 *
 * * Originally we used post_type="any"
 * * 2014/07/10 - now changed to use the defined post types
 * * 2014/11/11 - Now orders by post title
 *
 * @param ID $post_id - of the API that's being called
 * @param string $api_name - of the API that's being called
 */
function oikai_list_callers( $post_id, $api_name ) {
  oik_require( "shortcodes/oik-navi.php" );
  $atts['post_type'] = "oik_api,oik_file";  
  //$atts['meta_key' ] = "_oik_api_calls";
  //$atts['meta_value'] = $post_id;
  $atts['orderby'] = "title";
  $atts['order'] = "ASC";
	
	$meta_query = array();
  $meta_query[] = array( "key" => "_oik_api_calls"
                       , "value" => array( $post_id, $api_name )
                       , "compare" => "IN"  
                       );
	$atts['meta_query'] = $meta_query;												
  e( bw_navi( $atts ) );
}

/**
 * List invokers of the selected API
 *
 * Invokers are the action or filter hooks that call the API.
 * 
 * @param ID $post_id - of the API that's being called
 * @param string $api_name - future use
 */
function oikai_list_invokers( $post_id, $api_name ) {
  oik_require( "shortcodes/oik-navi.php" );
  $atts['post_type'] = "oik_hook"; 
  $atts['meta_key' ] = "_oik_hook_calls";
  $atts['meta_value'] = $post_id;
  $atts['orderby'] = "title";
  $atts['order'] = "ASC"; 
  e( bw_navi( $atts ) );
}

/**
 * List callees of the selected API
 * 
 * Callees are the functions that this API calls directly.
 * 
 * Note: This function does not cater for post meta data where the API name is stored rather than the post ID.
 * We use admin/set_oik_api_calls.php to convert from the API name to post IDs
 * Anything that's left over should be dealt with by a process to be created.
 * 
 * 
 * * 2014/11/11 Changed from using bw_navi_ids() to using bw_navi() since this allows us to list the posts sorted by title.
 * 
 * @param ID $post_id - of the API that's doing the calling
 */
function oikai_list_callees( $post_id ) {
  $values = get_post_meta( $post_id, "_oik_api_calls" ); 
	bw_trace2( $values, "values", true, BW_TRACE_VERBOSE );
  if ( $values ) {
    oik_require( "shortcodes/oik-navi.php" );
    // $atts['post_type'] = "oik_api"; 
    $atts['post_type'] = "oik_api";  
    $atts['post__in'] = $values;
    $atts['orderby'] = "title";
    $atts['order'] = "ASC"; 
    e( bw_navi( $atts ) );
  }
}

/**
 * List hooks invoked by the selected API
 *
 * Hooks are the action or filter hooks invoked using
 * 
 * - do_action(), 
 * - do_action_ref_array(),
 * - apply_filters(), 
 * - apply_filters_ref_array()
 * 
 * Change log:
 * 
 * * 2014/11/11 Changed from using bw_navi_ids() to using bw_navi() since this allows us to list the posts sorted by title.
 *
 * @param ID $post_id - of the API that's doing the hooking
 */
function oikai_list_hooks( $post_id ) {
  $values = get_post_meta( $post_id, "_oik_api_hooks" ); 
  //bw_trace2( $values, "values" );
  //bw_navi_ids( $values );
  if ( $values ) {
    oik_require( "shortcodes/oik-navi.php" );
    $atts['post_type'] = "oik_hook"; 
    $atts['post__in'] = $values;
    $atts['orderby'] = "title";
    $atts['order'] = "ASC"; 
    e( bw_navi( $atts ) );
  }
} 

/**
 * Implement [apis] shortcode - links to a list of APIs
 * 
 * Examples:
 * `[apis api=functionname]`
 * `[apis func=functionname]`   - not yet implemented! 
 * 
 * alternatively
 * `[apis bbboing_sc fiddle]` will return the values in $atts[0] and $atts[1] 
 * so we can get the API names from there!
 * 
 * How about? 
 * `[apis "bbboing_sc fiddle" ]`  
 *
 * @param array $atts - shortcode parameters
 * @param string $content - not expected
 * @param string $tag - shortcode tag
 * @return string - the generated HTML
 */
function oikai_apilink( $atts=null, $content, $tag ) {
  $function = bw_array_get( $atts, "api", null );
  $functions = bw_as_array( $function );
  $unkeyed = bw_array_get_unkeyed( $atts );
  $functions = array_merge( $functions, $unkeyed );
  
  bw_trace2( $functions, "functions" );
  
  $class = bw_array_get( $atts, "class", "bw_api" );
  if ( count( $functions) ) {
    oikai_list_apis_byname( $functions, $atts );
  } else {
    oikai_listapis( $atts ); 
  }
  return( bw_ret()); 
}

/**
 * Load the APIs listed in the array
 * 
 * The _oik_api_name stores the function name. For methods this includes the class name. e.g. Class::method
 * 
 * @param array $functions - array of function names
 * @return array $posts - array of posts found
 */
function oikai_get_oik_apis_byname( $functions ) {
  oik_require( "includes/bw_posts.php" );
  $posts = bw_get_by_metakey_array( "oik_api", "_oik_api_name", $functions );
  return( $posts );
}

/**
 * Load the API(s) for the given function
 *
 * @TODO There should only be one of these per plugin/theme.
 * But there could be multiple plugins or themes implementing the function.
 * We need to be able to cater for this sensibly.
 * Current solution is to work with the first one we come across
 * Do we actually order by ID?
 *
 * @param string $function
 * @return array of post IDs
 */
function oikai_get_oik_api_byname( $function ) {
	$api_cache = oiksc_api_cache::instance();
	$post_ids = $api_cache->get_oik_api_byname( $function );
	return( $post_ids ); 
}

/**
 * Load posts associated with the given function
 * 
 * @param string $function
 */
function oikai_load_posts( $function ) {
	$api_cache = oiksc_api_cache::instance();
	$api_cache->load_posts( $function );
}

/**
 * Produce a list of APIs as links
 * 
 * @param array $functions - array of function names
 * @param array $atts - shortcode parameters
 */
function oikai_list_apis_byname( $functions, $atts ) {
  $posts = oikai_get_oik_apis_byname( $functions );
  if ( $posts ) {
    $class = bw_array_get( $atts, "class", "bw_api" );
    sul( $class );
    foreach ( $posts as $post ) {
      bw_format_list( $post, $atts );
    }
    eul();
  } else {
    p( "Cannot find API(s):" . implode( ",", $functions ) );
  } 
}  

function apis__help() {
  return( "Link to API definitions" );
}

function apis__syntax( $shortcode="apis" ) {  
  $syntax = array( "api" => bw_skv( null, "<i>function-name</i>", "Function name e.g. bw_get_posts" )
                 );
  $syntax += _sc_classes();
  return( $syntax );
}
  
  


