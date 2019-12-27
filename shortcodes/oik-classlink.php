<?php // (C) Copyright Bobbing Wide 2014-2017
/** 
 * Automagically determine the class list
 *
 * If no classes have been passed then we can determine the list of classes from the context of the current post
 *
 * post_type      action
 * ----------     -------------
 * oik-plugins    find ALL the classes linked to the plugin through "_oik_api_plugin"
 * oik-themes     find ALL the classes linked to the plugin through "_oik_api_plugin"
 * oik_class      find ALL the classes which extend this class - using bw_tree. 
 * other          see if we can find a _plugin_ref field and use that 
 *
 * Note: There should be no real need for using [classes] on an "oik_class" page,
 * as the default display shows the Extends and Extended by lists
 * but we could use this shortcode in a widget or another template.
 
 * @param array $atts - shortcode parameters
 */
function oikai_listclasses( $atts ) {
  global $post;
  if ( $post ) {
    if ( $post->post_type == "oik-plugins" ) {
      oik_require( "shortcodes/oik-navi.php" ); 
      $atts['post_type'] = "oik_class"; 
      $atts['meta_key' ] = "_oik_api_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
    } elseif ( $post->post_type == "oik-themes" ) {
      oik_require( "shortcodes/oik-navi.php" ); 
      $atts['post_type'] = "oik_class"; 
      $atts['meta_key' ] = "_oik_api_plugin";
      $atts['meta_value'] = $post->ID;
      e( bw_navi( $atts ) );
    } elseif ( $post->post_type == "oik_class" ) {
      oik_require( "shortcodes/oik-tree.php" ); 
      e( bw_tree( $atts ) );
    } else {  
      $id = get_post_meta( $post->ID, "_plugin_ref", true );
      if ( $id ) {
        $atts['post_type'] = "oik_class"; 
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
 * Implement links to a list of classes
 * 
 * [classes classes=class-name]
 * 
 * alternatively
 * [classes WC_Widget] will return the values in $atts[0] and $atts[1] 
 * so we can get the API names from there!
 * 
 * How about? 
 * [classes "bbboing_sc fiddle" ]  
 *
 */
function oikai_classlink( $atts=null, $content, $tag ) {
  $class = bw_array_get( $atts, "classes", null );
  $classes = bw_as_array( $class );
  $unkeyed = bw_array_get_unkeyed( $atts );
  $classes = array_merge( $classes, $unkeyed );
  
  bw_trace2( $classes, "classes" );
  
  if ( count( $classes) ) {
    oikai_list_classes_byname( $classes, $atts );
  } else {
    oikai_listclasses( $atts ); 
  }
  return( bw_ret()); 
}

/**
 * Load the APIs listed in the array
 * 
 * @param array $classes - array of classes
 * @return array $posts - array of posts found
 * 
 */
function oikai_get_oik_classes_byname( $classes ) {
  oik_require( "includes/bw_posts.php" );
  $posts = bw_get_by_metakey_array( "oik_class", "_oik_class_name", $classes );
  return( $posts );
}

/**
 * Produce a list of classes as links
 *
 * @param array $classes - array of class names
 * @param array $atts - shortcode parameters
 * Note: the "class=" parameter is used for the CSS class
 * 
 */
function oikai_list_classes_byname( $classes, $atts ) {
  $posts = oikai_get_oik_classes_byname( $classes );
  if ( $posts ) {
    $class = bw_array_get( $atts, "class", "bw_classes" );
    sul( $class );
    foreach ( $posts as $post ) {
      bw_format_list( $post, $atts );
    }
    eul();
  } else {
    p( "Cannot find class:" . implode( ",", $classes ) );
  } 
}  

function classes__help() {
  return( "Link to class definitions" );
}

function classes__syntax( $shortcode="classes" ) {  
  $syntax = array( "classes" => bw_skv( null, "<i>class-name</i>", "Class names e.g. WP_Widget" )
                 );
  $syntax += _sc_classes( false );
  return( $syntax );
}
  
  


