<?php // (C) Copyright Bobbing Wide 2012-2015

// Admin functions for oik_shortcodes
// We will provide an entry field to allow us to generate known shortcodes that have the appropriate filters
// 


/**
 * Define oik-shortcodes settings and page
 *
 */
function oiksc_lazy_admin_menu() {
  // register_setting( 'oik_workingfeedback_options', 'bw_workingfeedback', 'oik_plugins_validate' ); // No validation for oik-workingfeedback
  add_submenu_page( 'oik_menu', 'Create shortcode', "Create shortcode", 'manage_options', 'oik_shortcodes', "oiksc_options_do_page" );
  add_submenu_page( 'oik_menu', 'Create API(s)', "Create APIs", "manage_options", "oik_apis", "oiksc_api_do_page" );
	//add_submenu_page( 'oik_menu', 'Shortcode server options', 'Shortcode server options", "manage_options", "oiksc_options", "oiksc_options_do_page" );
}

/**
 * shortcodes admin page
 *
 * Allows the admin user to create a shortcode and its shortcode parameters from the code.
 * It assumes the code already includes shortcode help and syntax hooks.
 */
function oiksc_options_do_page() {
  oik_menu_header( "shortcodes options" );
  oik_box( NULL, NULL, "Create shortcode", "oiksc_options" );
  oik_menu_footer();
  bw_flush();
}

/**
 * APIs admin page
 * 
 * Allows the admin user to create an API. Use oik-batch in preference to this. 
 */
function oiksc_api_do_page() {
  oik_require( "admin/oik-apis.php", "oik-shortcodes" );
  oiksc_lazy_api_do_page();
}

/**
 * Display a table of parms rows for the "oik_sc_param" post types
 * 
 * 10 rows initially?
 * Columns: "
 * 
 
  name => bw_skv( "default", "values", "notes" );
  _sc_param_default
  _sc_param_values
  _sc_param_notes
 */

function oiksc_parms_rows( ) {
  stag( "table", "widefat" );
  $parmc = 1;
  $cols[]  = label( "sc${parmc}_name", "Name:" );
  $cols[]  = label( "sc${parmc}_type", "Type:" );
  $cols[]  = label( "sc${parmc}_default", "Default:" );
  $cols[]  = label( "sc${parmc}_values", "Values:" );
  $cols[]  = label( "sc${parmc}_notes", "Notes:" );
  bw_tablerow( $cols );
  $options = array( "#options" => oiksc_param_types() );
  for ( $parmc = 1; $parmc < 10; $parmc++ ) {
    $cols = array();
    $cols[] = itext( "sc${parmc}_name", 30, "", "textBox" ); 
    $cols[] = iselect( "sc${parmc}_type", "", $options ); 
    $cols[] = itext( "sc${parmc}_default", 30, "", "textBox" ); 
    $cols[] = itext( "sc${parmc}_values", 30, "", "textBox" ); 
    $cols[] = itext( "sc${parmc}_notes", 60, "", "textBox" ); 
    bw_tablerow( $cols );
  }
  etag( "table" );
}

/**
 * Return the "help" for the shortcode
 * 
 * @param string $code - the shortcode
 * @return string - the help
 */
function oiksc_get_help( $code ) {
  $help = bw_array_get( $_REQUEST, "help", null );
  if ( !$help ) {
    // obtain the help using the action
    oik_require( "includes/oik-sc-help.inc" );
    do_action( "oik_add_shortcodes" );
    $help = _bw_lazy_sc_help( $code );
  }
  return( $help );
}

/**
 * Return the "func" that implements the shortcode
 * 
 * @param ID $plugin - the plugin ID
 * @param string $code - the shortcode
 * @return string - the funcname either a funcname or class::funcname
 */
function oiksc_get_func( $code, $plugin ) {
  $func = null;
  $funcname = bw_array_get( $_REQUEST, "func", null );
  if ( !$funcname ) {
    $func = bw_array_get( $_REQUEST, "funcs", null );
    if ( !$func ) {
      oik_require( "shortcodes/oik-codes.php" );
      $callback = bw_get_shortcode_callback( $code );
      $funcname = bw_get_shortcode_function( $code, $callback );
      bw_trace2( $funcname, "funcname" );
    }
  }
  if ( !$func ) { 
    $post = oiksc_get_oik_api_byname( $funcname );
    if ( $post ) {
      $func = $post->ID;
    } else {
      p( "Creating new API $funcname for shortcode $code" );
      $file = oiksc_get_source( $code );
      $func = oiksc_create_oik_api( $plugin, $funcname, $file, "shortcode", $code );
    }
  }     
  return( $func );     
}

 

/**
 * Implement theme_basename equivalent of plugin_basename
 */
function oiksc_theme_basename( $file ) {
  $file = wp_normalize_path( $file );
  $theme_dir = wp_normalize_path( get_theme_root() );

  $file = preg_replace('#^' . preg_quote($theme_dir, '#') . '/#','',$file); // get relative path from theme's dir
  $file = trim($file, '/');
  return $file;
}


/**
 * return the relative file name given the full name of the source file
 *
 * @param string $fullname -
 * @return string - relative filename
 */
function oiksc_relative_filename( $fullname, $plugin=true ) {
  //bw_backtrace();
  if ( $plugin ) {
    $file = plugin_basename( $fullname );
  } else {
    $file = oiksc_theme_basename( $fullname );
  }  
  $parts = explode( '/', $file );
  array_shift( $parts );
  $file = implode( '/', $parts );
  bw_trace2( $file, "relative filename" );
  return( $file );
}  

/**
 * Determine the basename of the file that implements the shortcode from what we already know
 */ 
function oiksc_get_source( $code ) {
  global $bw_sc_file;
  $file = bw_array_get( $bw_sc_file, $code, false );
  if ( $file ) {
    $file = oiksc_relative_filename( $file );
  }
  return( $file );
}

/**
 * Create missing parameters for this shortcode 
 *   
 * @param array $syntax - shortcode syntax arary of $param => bw_skv()
 * @param ID $plugin - the post ID of the plugin ( NOT NEEDED **?** )
 * @param string $code - the shortcode name
 * @param ID $post_id - the post ID for the shortcode
 */
function _oiksc_create_param( $syntax, $plugin, $code, $post_id ) {
  if ( is_array( $syntax ) ) {
    foreach ( $syntax as $param => $skv ) {
      p( "Parameter $param" );
      $post = oiksc_get_sc_param( $code, $param, $post_id );
      if ( $post ) {
        // Check the skv values?
         p( "$param already defined for $code" );
      } else {
        $param_post_id = oiksc_create_oik_sc_param( $post_id, $code, $param, $skv );
        p( "$param created for $code. ID: $param_post_id" );
      }
    }  
  }
}

/**
 * Create an oik_sc_param record programmatically
 *
 * Note: We don't allow comments on shortcode parameters
 * 
 * @param ID $sc_post_id -  the post ID for the shortcode
 * @param string $code - the actual shortcode name e.g. "bw_plug"
 * @param string $param - the parameter name e.g. "name"
 * @param array $skv - a bw_skv() array
 * @return ID post ID
 */
function oiksc_create_oik_sc_param( $sc_post_id, $code, $param, $skv ) {
  $post = array( 'post_type' => 'oik_sc_param'
               , 'post_title' => "$code $param parameter"
               , 'post_name' => "$code $param parameter"
               , 'post_content' => "$param parameter for the $code shortcode"
               , 'post_status' => 'publish'
               , 'comment_status' => 'closed'
               );
  /* Set metadata fields */
  $_POST['_sc_param_code'] = $sc_post_id; 
  $_POST['_sc_param_name'] = $param;
  $_POST['_sc_param_type'] = bw_array_get( $skv, "type", "text" );
  $_POST['_sc_param_default'] = bw_array_get( $skv, "default", null );
  $_POST['_sc_param_values'] = bw_array_get( $skv, "values", null );
  $_POST['_sc_param_notes'] = bw_array_get( $skv, "notes", null );
  
  $param_post_id = wp_insert_post( $post, TRUE );
 
  bw_trace2( $param_post_id );
  return( $param_post_id );
}

/**
 * Create the oik_sc_param records for each of the shortcode atts
 * 
 * with input coming from the shortcode__syntax
 * or the input fields 
 *
 */
function _oiksc_create_params( $plugin, $code, $post_id ) {
  $syntax = array();
  for ( $parmc = 1; $parmc < 10; $parmc++ ) {
    $name = bw_array_get( $_REQUEST, "sc${parmc}_name", null );
    $type = bw_array_get( $_REQUEST, "sc${parmc}_type", null );
    $default = bw_array_get( $_REQUEST, "sc${parmc}_default", null );
    $values = bw_array_get( $_REQUEST, "sc${parmc}_values", null );
    $notes = bw_array_get( $_REQUEST, "sc${parmc}_notes", null );
    if ( $name ) {
      $syntax[ $name ] = bw_skv( $default, $values, $notes );
    }  
  }
  bw_trace2( $syntax );
  oik_require( "includes/oik-sc-help.inc" );
  $syntax_code = _bw_lazy_sc_syntax( $code );
  bw_trace2( $syntax_code );
  
  // Once again... there's no need to merge them - just treat each array separately
  _oiksc_create_param( $syntax, $plugin, $code, $post_id ); 
  _oiksc_create_param( $syntax_code, $plugin, $code, $post_id ); 
} 

/**
 * Access the shortcode by its name and the plugin it's associated with
 * 
 * @param ID $plugin - the ID of the plugin that provides the shortcode
 * @param string $code - the shortcode
 * 
 * Note: This uses the 'meta_query' capability to return the post by matching meta data
 *
 */
function oiksc_get_shortcode_byname( $plugin, $code ) {
  $atts = array();
  $atts['post_type'] = "oik_shortcodes" ;
  $atts['numberposts'] = 1; 
  $meta_query = array();
  $meta_query[] = array( "key" => "_oik_sc_plugin", "value" => $plugin );
  $meta_query[] = array( "key" => "_oik_sc_code", "value" => $code ); 
  $atts['meta_query'] = $meta_query;
  $posts = bw_get_posts( $atts ); 
  $post = bw_array_get( $posts, 0, null );
  return( $post );
} 

/**
 * Create an oik_shortcodes record programmatically
 *
             [oik_shortcodes] => Array
                (
                    [_oik_sc_code] => _oik_sc_code
                    [_oik_sc_plugin] => _oik_sc_plugin
                    [_oik_sc_func] => _oik_fc_func
                    [_oik_sc_example] => _oik_sc_example
                    [_oik_sc_example_cb] => _oik_sc_example_cb
                    [_oik_sc_live_cb] => _oik_sc_live_cb
                    [_oik_sc_snippet_cb] => _oik_sc_snippet_cb
                    [_oik_sc_endcode_cb] => _oik_sc_endcode_cb
                )

 *
 */
function oiksc_create_oik_shortcode( $plugin, $code, $help, $func ) {
  $post = array( 'post_type' => 'oik_shortcodes'
               , 'post_title' => "$code - $help"
               , 'post_name' => $code
               , 'post_content' => $help
               , 'post_status' => 'publish'
               );
  /* Set metadata fields */
  $_POST['_oik_sc_plugin'] = $plugin;
  $_POST['_oik_sc_code'] = $code; 
  $_POST['_oik_sc_func'] = $func; 
  
  $post_id = wp_insert_post( $post, TRUE );
 
  bw_trace2( $post_id );
  return( $post_id );
}



/**
 * Respond to the submit button by creating an oik_shortcode, parameters and values
 */
function oiksc_create_shortcode() {
  $create = bw_array_get( $_REQUEST, "_oiksc_create_shortcode", null );
  if ( $create ) {
    // Choose the input field over the select list 
    $code = bw_array_get( $_REQUEST, "code", null );
    if ( !$code ) {
      $code = bw_array_get( $_REQUEST, "codes", null );
    }  
    if ( $code ) {
      oik_require( "includes/bw_posts.inc" ); 
      $plugin = bw_array_get( $_REQUEST, "plugin", null );
      $help = oiksc_get_help( $code );
      $func = oiksc_get_func( $code, $plugin ); 
      $post_id = _oiksc_create_shortcode( $plugin, $code, $help, $func );
      if ( $post_id ) {
        _oiksc_create_params( $plugin, $code, $post_id );
      } else {
        p( "No post id returned " );
      }
    } else {
      p( "Missing code" );
    }  
  }
}

/** 
 * Set the post_title for an oik_api
 *
 * @param string $func the function name
 * @param string $type the API type
 * @param string $title the title of the API
 * @return string - the post title
 */
function oiksc_oik_api_post_title( $func, $type, $title ) {
  //if ( $type == "shortcode" ) {
    //if ( !$code ) {
      $code = $func;
    //}  
  //  $post_title = "$func() - [[$code]] shortcode";
  //} else {
    $post_title = "$func() - $title"; 
  //}
  $post_title = htmlentities( $post_title );
  $post_title = str_replace( "[", "&#91;", $post_title );
  $post_title = stripslashes( $post_title );
  return( $post_title );
} 

  
/** 
 * Set the post_title for an oik_hook
 *
 * @param string $hook the hook name
 * @param string $type "action" or "filter"
 * @param string $title the title of the hook, if known
 * @return string - the post title
 */
function oiksc_oik_hook_post_title( $hook, $type, $title=null ) {
  $post_title = "$hook - $type"; 
  $post_title = htmlentities( $post_title );
  $post_title = str_replace( "[", "&#91;", $post_title );
  $post_title = stripslashes( $post_title );
  return( $post_title );
}   

/**
 * Programmatically create an oik_api record for a selected plugin's func
 *
 * Note: We don't allow comments on APIs. Instead we may add an oik_api_example
 * in the same way as we do for shortcode examples. 
 * 
 * @param ID $plugin - the ID of the plugin for which this API is being created
 * @param string $func - the function name ( may be in form class::funcname )
 * @param string $file - the implementing filename within the plugin e.g. "oik/shortcodes/oik-codes.php"
 * @param string $type - the API type - this may be null - in which case we'll have to discover this later
 * @param string $title - the API title
 * @return ID - the post ID of the newly created oik_api record
 *
 * @param string $code - the shortcode implemented (omitting []'s)
 */
function oiksc_create_oik_api( $plugin, $func, $file, $type, $title=null ) {
  //bw_backtrace();
  $post_title = oiksc_oik_api_post_title( $func, $type, $title );
  $post = array( 'post_type' => 'oik_api'
               , 'post_title' => $post_title
               , 'post_name' => $func
               , 'post_content' => "<!--more -->[bw_api]"
               , 'post_status' => 'publish'
               , 'comment_status' => 'closed'
               );
  /* Set metadata fields */
  $_POST['_oik_api_name'] = $func;
  oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
  $_POST['_oik_api_class'] = oikai_get_classref( $func, null, $plugin, $file );
  $_POST['_oik_api_plugin'] = $plugin;
  $_POST['_oik_api_source'] = $file;
  oik_require( "admin/oik-files.php", "oik-shortcodes" );
  $_POST['_oik_fileref'] = oiksc_get_oik_fileref( $plugin, $file );
  $_POST['_oik_api_type'] = $type;
  
  // @TODO Find the correct way of setting WordPress-SEO metadata
  //$_POST['_yoast_wpseo_metadesc'] = $post_title;
  
  /* We don't know these values yet:
     _oik_api_example
     _oik_api_notes
     _oik_api_deprecated_cb
  */
  $post_id = wp_insert_post( $post, TRUE );
  bw_trace2( $post_id );
  return( $post_id );
}

/**
 * Return the hook type given the context
 * 
 * @param string $context - this is the API where we've found the filter/action being used
 * @return string type - "filter" or "action" (defaults to action) 
 */
function oiksc_get_hook_type( $context ) {
  static $types = array( "apply_filters" => "filter"
                       , "apply_filters_ref_array" => "filter"
                       , "add_filter" => "filter" 
                       );
  $type = bw_array_get( $types, $context, "action" );
  return( $type );
} 

/**
 * Return a formatted docblock
 *
 * @TODO - leave the docblock post meta as unformatted so that multiple definitions can be recorded and analysed.
 *
 */
function oiksc_get_docblock() {
  $docComment = bw_context( "docblock" );
  oik_require( "classes/oik-docblock.php", "oik-shortcodes" );
  $docblock = new DocBlock( $docComment );
  $docblock_text = $docblock->getShortDescription();
  $docblock_text .= "&nbsp;";
  $docblock_text .= $docblock->getLongDescription();
  return( $docblock_text );
}

/**
 * Return the post_ID for the plugin name 
 */
function oiksc_get_plugin() {
  //$plugin = bw_array_get( $_REQUEST, "plugin", null );
  global $plugin_post;
  if ( $plugin_post ) {
    $plugin = $plugin_post->ID;
  } else {
    $plugin = null;
  }
  bw_trace2( $plugin );
  //bw_backtrace();
  return( $plugin );
}

/**
 * Return the filename
 */   
function oiksc_get_filename() {
  $filename = bw_array_get( $_REQUEST, "file", null );
  bw_trace2( $filename );
  //bw_backtrace();
  return( $filename );
}

/**
 * Programmatically create an oik_hook record
 *
 * Note: We don't allow comments on oik_hook posts
 * 
 * @param string $hook - the hook name - which may contain a variable
 * @param string $context - how this hook is being used
 * @return ID - the post ID of the newly created oik_hook record
 *
 */
function oiksc_create_oik_hook( $hook, $context ) {
  //bw_backtrace();
  $type = oiksc_get_hook_type( $context );
  $post_title = oiksc_oik_hook_post_title( $hook, $type );
  $docblock = oiksc_get_docblock();
  $post = array( 'post_type' => 'oik_hook'
               , 'post_title' => $post_title
               , 'post_name' => $hook
               , 'post_content' => "$docblock<!--more -->[bw_fields][hooks]"
               , 'post_status' => 'publish'
               , 'comment_status' => 'closed'
               );
  /* Set metadata fields */
  $plugin = oiksc_get_plugin();
  $file = oiksc_get_filename();
  $_POST['_oik_hook_name'] = $hook;
  $_POST['_oik_hook_type'] = $type; 
  $_POST['_oik_api_plugin'] = $plugin;
  $_POST['_oik_api_source'] = $file;
  oik_require( "admin/oik-files.php", "oik-shortcodes" );
  $_POST['_oik_fileref'] = oiksc_get_oik_fileref( $plugin, $file );
  $_POST['_oik_hook_docblock'] = $docblock;
  //$_POST['_oik_hook_deprecated_cb'] = false;
  $post_id = wp_insert_post( $post, TRUE );
  bw_trace2( $post_id );
  return( $post_id );
}

/**
 * Update the meta data for an oik_api post_type
 *
 * @param post $post - the oik_api object
 * @param ID $plugin - the plugin this API is associated with
 * @param string $func - the API name
 * @param string $file - the source file for the API
 * @param string $type - the API type
 */
function oiksc_update_oik_api( $post, $plugin, $func, $file, $type, $title ) {
  $post->post_title = oiksc_oik_api_post_title( $func, $type, $title );
  /* Set metadata fields */
  $_POST['_oik_api_name'] = $func;
  oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
  $_POST['_oik_api_class'] = oikai_get_classref( $func, null, $plugin, $file );
  $_POST['_oik_api_plugin'] = $plugin;
  $_POST['_oik_api_source'] = $file;
  oik_require( "admin/oik-files.php", "oik-shortcodes" );
  $_POST['_oik_fileref'] = oiksc_get_oik_fileref( $plugin, $file );
  $_POST['_oik_api_type'] = $type;
  wp_update_post( $post );
}

/**
 * Update the post data and meta data for an oik_hook post_type
 *
 * @TODO We need to stop cache invalidation occurring when this happens. Can we simple define( 'WPLOCKDOWN', '1' ) ?
 * @param post $post - the oik_hook object
 * 
 */
function oiksc_update_oik_hook( $post, $hook, $context ) {
	if ( !is_user_logged_in() ) {
		return;
	}
  //bw_backtrace();
  $type = oiksc_get_hook_type( $context );
  $post->post_title = oiksc_oik_hook_post_title( $hook, $type );
  $docblock = oiksc_get_docblock();
  // Don't change the post content but do change the _oik_hook_docblock field
  // 
  //$post->post_content = "$docblock<!--more -->[bw_fields][hooks]";
  /* Set metadata fields */
  $plugin = oiksc_get_plugin();
  $file = oiksc_get_filename();
  $_POST['_oik_hook_name'] = $hook;
  $_POST['_oik_hook_type'] = $type;  
  $_POST['_oik_hook_plugin'] = $plugin; 
  $_POST['_oik_hook_source'] = $file;
  oik_require( "admin/oik-files.php", "oik-shortcodes" );
  $_POST['_oik_fileref'] = oiksc_get_oik_fileref( $plugin, $file );
  $_POST['_oik_hook_docblock'] = $docblock;
  //$_POST['_oik_hook_deprecated_cb'] = false;
  wp_update_post( $post );
}

/**
 * Retrieve an "oik_sc_param" record for the selected shortcode and parameter name
 * @param string $code - the name of the shortcode ( unused **?** )
 * @param string $name - the name of the parameter
 * @param ID - $sc_post_id - the ID of the shortcode (plugin name taken into account )
 */
function oiksc_get_sc_param( $code, $name, $sc_post_id ) {
 $atts = array();
 $atts['post_type'] = "oik_sc_param" ;
 $atts['numberposts'] = 1; 
 $meta_query = array();
 $meta_query[] = array( "key" => "_sc_param_code", "value" => $sc_post_id );
 $meta_query[] = array( "key" => "_sc_param_name", "value" => $name ); 
 $atts['meta_query'] = $meta_query;
 $posts = bw_get_posts( $atts ); 
 $post = bw_array_get( $posts, 0, null );
 return( $post );
}

/**
 * 
 * Creating a shortcode programmatically involves
 * 1. Check the code is not already defined
 * 2. If it is then we use the $post ID for the parameters
 * 3. If not then we create the shortcode
 * 3a. Which may require us to create the API that implements the shortcode
 * 3b. Which suggests we should also consider creating the plugin in the first place **?**
 * 4. For each specified parameter (either through interface or shortcode__syntax insert an oik_sc_param
 */
function _oiksc_create_shortcode( $plugin, $code, $help, $func ) {
  p( "Creating shortcode $code - $help" );
  $post = oiksc_get_shortcode_byname( $plugin, $code );
  bw_trace2( $post, "post" );
  if ( !$post ) {
    $post_id = oiksc_create_oik_shortcode( $plugin, $code, $help, $func );     
  } else {
    $post_id = $post->ID;
  }
  return( $post_id );
}



/** 
 * Produce the list of existing codes for which to create an oik_shortcode
 */
function oiksc_code_list() {
  global $shortcode_tags; 
  
  oik_require( "includes/oik-sc-help.inc" );
  do_action( "oik_add_shortcodes" );
  $sc_list = array();
  $sc_list[] = "None";
  
  foreach ( $shortcode_tags as $shortcode => $callback ) {
    $schelp = _bw_lazy_sc_help( $shortcode );
    $sc_list[$shortcode] =  $shortcode . " - " . $schelp;
  }
  asort( $sc_list );
  bw_select( "codes", "Active shortcodes", "", array( "#options" => $sc_list, "#optional" => true ));
} 


/** 
 * Produce the list of existing APIs for which to create an oik_shortcode
 */
function oiksc_func_list() {
  bw_form_field_noderef( "funcs", "", "Select the API", "", array( "#type" => "oik_api", "#optional" => true ));
} 


/**
 * shortcodes options
 *        
 */
function oiksc_options() {

  oik_require( "bobbforms.inc" );
  p( "This form should magically create an oik_shortcode (Shortcodes) and the required Shortcode parameters ( oik_sc_param ) and SC param mappings ( oik_sc_mapping )" );
  p( "Just write the code for the shortcode using the oik APIs and let the code take care of the rest " );
  
  oiksc_create_shortcode();
  
  bw_form();
  stag( "table" );
  bw_form_field_noderef( "plugin", "", "Select the plugin or theme", "", array( "#type" => array("oik-plugins", "oik-themes") ));
  oiksc_code_list();
  oiksc_func_list();
  bw_textfield( "code", 40, "Type the shortcode name", "" );
  bw_textfield( "help", 40, "Type the shortcode help", "" );
  bw_textfield( "func", 80, "Type the shortcode's implementing function", "" );
  etag( "table" );
  oiksc_parms_rows();
  p( isubmit( "_oiksc_create_shortcode", "Create shortcode",  null, "button-primary" ) );
  etag( "form" );
  bw_flush();
} 


/** 
 * Query API from function name
 *
 * @param string $function - function name or class::function name
 * @return $post    
*/
function oiksc_get_oik_api_byname( $function ) {
  oik_require( "includes/bw_posts.inc" ); 
  $atts = array();
  $atts['post_type'] = "oik_api" ;
  $atts['numberposts'] = 1; 
  $atts['meta_key'] = "_oik_api_name";
  $atts['meta_value'] = $function;
  $posts = bw_get_posts( $atts ); 
  $post = bw_array_get( $posts, 0, null );
  bw_trace2( $post, "oik_api?" );
  return( $post );
}

/** 
 * Query hook from hook name
 *
 * @param string $hook
 * @return $post    
*/
function oiksc_get_oik_hook_byname( $hook ) {
  oik_require( "includes/bw_posts.inc" ); 
  $atts = array();
  $atts['post_type'] = "oik_hook" ;
  $atts['numberposts'] = 1; 
  $atts['meta_key'] = "_oik_hook_name";
  $atts['meta_value'] = $hook;
  $posts = bw_get_posts( $atts ); 
  $post = bw_array_get( $posts, 0, null );
  bw_trace2( $post, "oik_hook?" );
  return( $post );
}

/**
 * Return a (hopefully one) shortcode given its name and implementing functions post ID, if available
 * 
 * @param string $oik_shortcode - the shortcode name e.g. oik
 * @param ID $func - the post ID of the implementing function
 * 
 */
function oiksc_get_shortcodes_byname( $oik_shortcode, $func=null ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array();
  $atts['post_type'] = "oik_shortcodes" ;
  $meta_query = array();
  if ( $func ) {
    $meta_query[] = array( "key" => "_oik_sc_func", "value" => $func );
  }
  $meta_query[] = array( "key" => "_oik_sc_code", "value" => $oik_shortcode ); 
  $atts['meta_query'] = $meta_query;
  $posts = bw_get_posts( $atts );
  bw_trace2( $posts );
  return( $posts );
}

/**
 * Redirect to the page for the shortcode
 * If we have the function we can look up the func ID and pass that in _oik_sc_func
 */
function oiksc_lazy_redirect( $oik_shortcode, $function=null ) {
  $api = oiksc_get_oik_api_byname( $function );
  if ( $api ) { 
    $func = $api->ID; 
  } else {
    $func = null;
  }
  $posts = oiksc_get_shortcodes_byname( $oik_shortcode, $func ); 
  if ( $posts ) {
    if ( count( $posts ) > 1 ) {
      bw_trace2( "more than one shortcode defined" );
      // the shortcode may be case insensitive - like OIK and oik  **?** deal with this later
    } 
    $post = bw_array_get( $posts, 0, null );
    $new_path = get_permalink( $post->ID );
    wp_redirect( $new_path, 301 );
    exit(); 
  } else {
    bw_trace2();
    // take pot luck - don't redirect
  }
}
