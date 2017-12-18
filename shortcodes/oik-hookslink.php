<?php // (C) Copyright Bobbing Wide 2012-2017

/** 
 * Automagically determine the hook list
 *
 * If no functions have been passed then we can determine the list of hooks from the context of the current post
 *
 * post_type      action
 * ----------     -------------
 * oik-plugins    find ALL the hooks linked to the plugin through "_oik_hook_plugin"
 * oik_hook       find all the functions that this hook calls (implementers) and is called by (invokers)
 * other          see if we can find a _plugin_ref field and use that
 *
 * @param array $atts [hooks] shortcode parameters 
 */
function oikho_listhooks( $atts ) {
  global $post;
  if ( $post ) {
    oik_require( "shortcodes/oik-navi.php" ); 
    if ( $post->post_type == "oik-plugins" ) {
      $atts['post_type'] = "oik_hook"; 
      $atts['meta_key' ] = "_oik_hook_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
    } elseif ( $post->post_type == "oik-themes" ) {
      $atts['post_type'] = "oik_hook"; 
      $atts['meta_key' ] = "_oik_hook_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
    } elseif ( $post->post_type == "oik_hook" ) {
      oikho_list_callers_callees( $post->ID );
    } else { 
      $id = get_post_meta( $post->ID, "_plugin_ref", true );
      if ( $id ) {
        $atts['post_type'] = "oik_hook"; 
        $atts['meta_key' ] = "_oik_hook_plugin";
        $atts['meta_value'] = $id;
        e( bw_navi( $atts ) );
      }
    }
  } else { 
    // Don't expect this! 
  }
}

/** 
 * List the callers and callees for the selected oik_hook
 *
 * @param ID $post_id - the ID of the oik_hook
 */
function oikho_list_callers_callees( $post_id ) {
  sdiv( "bw_callers" );
  h3( "Invokers", "bw_callers" );
  oikho_list_callers( $post_id );
  ediv();
  sdiv( "bw_callees" );
  h3( "Implementers", "bw_callees" );
  oikho_list_callees( $post_id );
  ediv();
}  

/**
 * List callers (Invokers) of the selected hook
 * 
 * List the functions and files that invoke the hook using do_action / apply_filters or versions of
 * 
 * @param ID $post_id - of the hook that's being called
 *
 */
function oikho_list_callers( $post_id ) {
  oik_require( "shortcodes/oik-navi.php" );
  $atts['post_type'] = "oik_api,oik_file" ; // ,oik_file"; 
  $atts['meta_key' ] = "_oik_api_hooks";
  $atts['meta_value'] = $post_id;
  $atts['orderby'] = 'title';
  $atts['order'] = 'ASC';
  e( bw_navi( $atts ) );
  //$atts['post_type'] = "oik_file";
  //e( bw_navi( $atts ) );
}

/**
 * List callees (Implementers) of the selected hook
 * 
 * This function lists the implementers of the selected hook.
 * We get told about each implementer in fits and starts; as we come across the add_action/add_filter calls when we parse a function.
 * 
 * @param ID $post_id - of the hook that's being executed
 */
function oikho_list_callees( $post_id ) {
  $values = get_post_meta( $post_id, "_oik_hook_calls" ); 
  bw_trace2( $values, "values" );
  //bw_theme_field( "_oik_hook_calls", $values );
  //bw_format_field( array( "_oik_hook_calls" => $values ) );
  bw_navi_ids( $values );
} 

/**
 * Implement [hooks] shortcode to produce links to a list of hooks
 *
 *  
 * [hooks hook=hookname]
 * [hooks func=hookname]   - not yet implemented! 
 * 
 * alternatively
 * [hooks bbboing_sc fiddle] will return the values in $atts[0] and $atts[1] 
 * so we can get the hook names from there!
 * 
 * How about? 
 * [hook "bbboing_sc fiddle" ]  
 *
 * @param array $atts shortcode parameters
 * @param string $content not expected - yet
 * @param string $tag shortcode name
 * @return string the generated HTML
 */
function oikho_hooklink( $atts=null, $content, $tag ) {
  $function = bw_array_get( $atts, "hook", null );
  $functions = bw_as_array( $function );
  $unkeyed = bw_array_get_unkeyed( $atts );
  $functions = array_merge( $functions, $unkeyed );
  
  bw_trace2( $functions, "functions" );
  
  $atts["class"] = bw_array_get( $atts, "class", "bw_hook" );
  if ( count( $functions) ) {
    oikho_list_hooks_byname( $functions, $atts );
  } else {
    oikho_listhooks( $atts ); 
  }
  return( bw_ret()); 
}

/**
 * Load the hooks listed in the array
 * 
 * @param array $functions - array of functions
 * @result array $posts - array of posts found
 * 
 */
function oikho_get_oik_hooks_byname( $functions ) {
  oik_require( "includes/bw_posts.php" );
  $posts = bw_get_by_metakey_array( "oik_hook", "_oik_hook_name", $functions );
  return( $posts );
}

/**
 * Produce a list of hooks as links
 */
function oikho_list_hooks_byname( $functions, $atts ) {
  $posts = oikho_get_oik_hooks_byname( $functions );
  if ( $posts ) {
    $class = bw_array_get( $atts, "class", "bw_hook" );
    sul( $class );
    foreach ( $posts as $post ) {
      bw_format_list( $post, $atts );
    }
    eul();
  } else {
    p( "Cannot find hook(s):" . implode( ",", $functions ) );
  } 
}  

function hooks__help() {
  return( "Link to hook definitions" );
}

function hooks__syntax( $shortcode="hooks" ) {  
  $syntax = array( "hook" => bw_skv( null, "<i>hook name</i>", "Hook name e.g. oik_admin_menu" )
                 );
  $syntax += _sc_classes();
  return( $syntax );
}
  
  


