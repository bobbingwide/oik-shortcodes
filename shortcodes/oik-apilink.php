<?php // (C) Copyright Bobbing Wide 2012, 2014
/** 
 * Automagically determine the API list
 *
 * If no functions have been passed then we can determine the list of APIs from the context of the current post
 *
 * post_type      action
 * ----------     -------------
 * oik-shortcodes find the API from "_oik_sc_func"
 * oik-plugins    find ALL the apis linked to the plugin through "_oik_api_plugin"
 * oik-themes     find ALL the apis linked to the theme through "_oik_api_plugin"
 * oik_class      find ALL the methods linked to the class through "_oik_api_class"
 * oik_api        find all the apis that this API calls and is called by
 * other          Do nothing 
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
      // Not the right post_type to list APIs
    }
    
  } else { 
    // Don't expect this! 
  }
}

/** 
 * List the callers and callees for the selected oik_api including information about hooks 
 * 
 * @param ID $post_id - the ID of the oik_api
 */
function oikai_list_callers_callees( $post_id ) {
  sdiv( "bw_callers" );
  h3( "Called by", "bw_callers" );
  oikai_list_callers( $post_id );
  ediv();
  sdiv( "bw_invokers" );
  h3( "Invoked by", "bw_invokers" );
  oikai_list_invokers( $post_id );
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
 * The possible callers now includes "oik_file" as well as "oik_api", so we use post_type="any"
 * 2014/07/10 - now changed to use the defined post types
 *
 * @param ID $post_id - of the API that's being called
 *
 */
function oikai_list_callers( $post_id ) {
  oik_require( "shortcodes/oik-navi.php" );
  // $atts['post_type'] = "oik_api"; 
  $atts['post_type'] = "oik_api,oik_file";  
  $atts['meta_key' ] = "_oik_api_calls";
  $atts['meta_value'] = $post_id;
  //$atts['posts_per_page'] = get_option( "posts_per_page" );
  e( bw_navi( $atts ) );
  //e( bw_shortcode_event( $atts, null, "bw_list" ) );
  
}

/**
 * List invokers of the selected API
 *
 * Invokers are the action or filter hooks that call the API.
 * 
 * @param ID $post_id - of the API that's being called
 */
function oikai_list_invokers( $post_id ) {
  oik_require( "shortcodes/oik-navi.php" );
  $atts['post_type'] = "oik_hook"; 
  $atts['meta_key' ] = "_oik_hook_calls";
  $atts['meta_value'] = $post_id;
  e( bw_navi( $atts ) );
  //e( bw_shortcode_event( $atts, null, "bw_list" ) );
}

/**
 * List callees of the selected API
 * 
 * Callees are the functions that this API calls directly
 * 
 * @param ID $post_id - of the API that's doing the calling
 */
function oikai_list_callees( $post_id ) {
  $values = get_post_meta( $post_id, "_oik_api_calls" ); 
  bw_trace2( $values, "values" );
  //bw_theme_field( "_oik_api_calls", $values );
  ///bw_format_field( array( "_oik_api_calls" => $values ) );
  
  bw_navi_ids( $values );
}

/**
 * List hooks invoked by the selected API
 *
 * Hooks are the action or filter hooks invoked using do_action(), do_action_ref_array(),
 * apply_filters() and apply_filters_ref_array()
 *
 * @param ID $post_id - of the API that's doing the hooking
 */
function oikai_list_hooks( $post_id ) {
  $values = get_post_meta( $post_id, "_oik_api_hooks" ); 
  //bw_format_field( array( "_oik_api_hooks" => $values ) );
  
  bw_navi_ids( $values );
} 

/**
 * Implement [apis] shortcode - links to a list of APIs
 * 
 * Examples:
 * [apis api=functionname]
 * [apis func=functionname]   - not yet implemented! 
 * 
 * alternatively
 * [apis bbboing_sc fiddle] will return the values in $atts[0] and $atts[1] 
 * so we can get the API names from there!
 * 
 * How about? 
 * [apis "bbboing_sc fiddle" ]  
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
  oik_require( "includes/bw_posts.inc" );
  $posts = bw_get_by_metakey_array( "oik_api", "_oik_api_name", $functions );
  return( $posts );
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
  
  


