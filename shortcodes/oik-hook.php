<?php // (C) Copyright Bobbing Wide 2014

/**
 * Return the hook type for this hook
 *
 * A hook can either be an 'action' hook or a 'filter' hook
 * Some people misuse filter hooks to perform actions
 *
 * @param ID $post_id - the post ID of the oik-hook post
 * @return string - the hook type - 'action'|'filter'
 */
function oikho_query_hook_type( $post_id ) {
  $type = get_post_meta( $post_id, "_oik_hook_type", true );
  return( $type );
}

/**
 * Implement [hook] shortcode to produce simple links to a hook or list of hooks
 *
 * If there's just one hook it's shown as "hook <i>hook type</i>".
 * If more than one then they're comma separated, but NOT in an HTML list "hook <i>type</i>, hook <i>type</i>"
 * Links are created to the local site. If the hook is not found then no link is created.
 *
 * @param array $atts - shortcode parameters
 * @param string $content - content
 * @param string $tag - the shortcode tag
 * @return string - generated HTML
 *
 */
function oikho_hook( $atts=null, $content=null, $tag=null ) {
  $hooks = bw_array_get_from( $atts, "hook,0", null );
  if ( $hooks ) {
    $hooka = bw_as_array( $hooks );
    //oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
    oik_require( "shortcodes/oik-hookslink.php", "oik-shortcodes" );
    $count = 0;
    foreach ( $hooka as $key => $hook ) {
      //$api = oikai_simplify_apiname( $api );
      if ( $count ) {
        e( "," );
        e( "&nbsp;" );
      }
      $count++;
      //$type = oikai_determine_reference_type( $hook ); 
      //oikai_handle_reference_type( $hook, $type );
      $posts = oikho_get_oik_hooks_byname( $hook );
      if ( $posts ) {
        foreach ( $posts as $post ) {
          alink( null, get_permalink( $post->ID), $hook );
          $hook_type = oikho_query_hook_type( $post->ID );
          e( " <i>$hook_type</i>" );
          
        }
      } else {
        e( $hook );
      }
         
    } 
  } else {
    // Don't do anything for a missing hook name ... or implements [hooks] ?
  }
  return( bw_ret() );
}
