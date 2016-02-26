<?php // (C) Copyright Bobbing Wide 2012-2016
/**
 * Display the "Create API" admin page
 */
function oiksc_lazy_api_do_page() {
  oik_menu_header( "APIs" );
  oik_box( NULL, NULL, "Create API", "oiksc_api" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Produce a list of all the installed, but not necessarily active plugins
 * 
 * @returns array 
 * 
 * @uses get_plugins()
 */
function _oiksc_get_plugins() {
  $plugins = get_plugins();
  bw_trace2( $plugins, "plugins", false, BW_TRACE_DEBUG );
  return( $plugins );
}

/**
 * Get the selected plugin
 *
 * @TODO - Need to be more consistent
 *
 * @return string/integer - depending on where it's called
 * 
 */
function _oiksc_get_plugin() {
  $plugin = bw_array_get( $_REQUEST, "plugin", null );
  return( $plugin );
} 

/**
 * Get the selected API
 *
 * @return string the API name; function or class::methodname
 */
function _oiksc_get_api() {
  $api = bw_array_get( $_REQUEST, "api", null );
  return( $api );
}

/**
 * Return the array of oiksc_token_objs as a simple assoc array
 *
 * Note: This can return class names (format "class::") as well as methods and functions, but the default is false. 
 * 
 * @param array $apis - array of oiksc_token_objs
 * @param bool $class_names - true if class names are required in the list, false if not 
 * 
 */
function _oiksc_apis_list( $apis, $class_names=false ) {
  $assoc = array();
  foreach ( $apis as $api ) {
    if ( $api->methodname || $class_names ) {
      $apiname = $api->getApiName();
      $assoc[$apiname] = $apiname;
    }  
  }
  return( $assoc );
}

/**
 * Get the plugin slug
 *
 * @TODO decide if we need to trim anything past the first '/', if present
 *
 * @param string/integer - the plugin name OR post_id
 * @return string - the plugin slug 
 */
function oiksc_get_plugin_slug( $plugin ) {
  if ( is_numeric( $plugin ) ) {
    $slug = get_post_meta( $plugin, "_oikp_slug", true );
  } else {
    $slug = $plugin;
  }
  //if ( $slug == "wordpress" ) {
  //  $slug = null;
  // }
  return( $slug );
}

/**
 * List the APIs for the selected filename
 *
 * @param string $file - file name
 * @param bool $reload - causes the static $apis array to be rebuilt
 * @return array - associative array of API names
 */
function _oiksc_get_apis( $file=null, $reload=false ) {
  global $plugin;
  static $apis = null;
  if ( !$apis || $reload ) {
    if ( $file ) {
      oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
      $plugin = _oiksc_get_plugin();
      $plugin = oiksc_get_plugin_slug( $plugin );
      $component_type = oiksc_query_component_type( $plugin );
      $apis = oiksc_list_file_functions2( $file, $component_type );
      $apis = _oiksc_apis_list( $apis );
    } else {
      $apis = array();
    }
  }
  return( $apis );
} 

/**
 * List the APIs for the selected filename
 *
 * This function is similar to _oiksc_get_apis() except it returns an array of objects
 * 
 * @param string $file - file name
 * @param bool $reload - causes the static $apis array to be rebuilt
 * @return array - array of oiksc_token_objects
 */
function _oiksc_get_apis2( $file=null, $reload=false, $component_type ) {
  static $apis = null;
  if ( !$apis || $reload ) {
    if ( $file ) {
      //oik_require( "admin/oik-apis.php", "oik-shortcodes" );
      oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
      $apis = oiksc_list_file_functions2( $file, $component_type );
    } else {
      $apis = array();
    }
  }
  return( $apis );
} 


/**
 * Get the selected API type
 *
 * @TODO The API type should probably become a taxonomy. But first we need to improve the method of setting it.
 *
 * @return string - the selected api_type
 *
 */
function _oiksc_get_type() {
  $type = bw_array_get( $_REQUEST, "api_type", null );
  return( $type );
}

/**
 * Produce a list of the files for a plugin/theme
 *
 * @TODO Verify dependency on oik-batch for listing "wordpress" files 
 * 
 * @param string/ID $plugin_id - plugin slug or post ID
 * @param bool $plugin - true when processing a plugin, false when a theme 
 * @return list of files in the plugin
 */
function _oiksc_get_files( $plugin_id, $plugin=true ) {
  if ( is_numeric( $plugin_id ) ) {
		if ( $plugin ) {
			$name = WP_PLUGIN_DIR . '/';
			$name .= get_post_meta( $plugin_id, "_oikp_slug", true );
		} else {
			$name = get_theme_root() . '/'; 
			$name .= get_post_meta( $plugin_id, "_oikth_slug", true );
		}
  } else {
    $name= $plugin_id;
  }
  bw_trace2( $name, "name", true, BW_TRACE_VERBOSE );
  if ( $name ) {
    if ( $name == WP_PLUGIN_DIR . '/' . "wordpress" ) {
      oik_require( "oik-list-wordpress-files.php", "oik-batch" );
      $files = _la_get_wordpress_files();
      global $plugin;
      $plugin = null;
    } else {
      $files = _oiksc_get_php_files( $name, $plugin );
    }  
  } else {
    $files = array();
  }
  bw_trace2( $files, "files", false, BW_TRACE_VERBOSE );
  return( $files );
}

/**
 * List PHP files within this directory 
 *
 * Code copied from WP-parser\lib\runner.php but modified to return ANY file name
 * So the function name is a bit suspect! 
 * 
 * Note: We ignore all files in the .git/ folder
 *
 * @param string $directory - the root directory for the file list. Must not be null
 * @return array of relative file names  
 */
function _oiksc_get_php_files( $directory, $plugin ) {
  oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
	$iterableFiles = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $directory ) );
	$files = array();
	try {
		foreach ( $iterableFiles as $file ) {
			//if ( $file->getExtension() !== 'php' ) {
			//	continue;
			//}
      $filename = $file->getPathname();
      $filename = oiksc_relative_filename( $filename, $plugin );
			bw_trace2( $filename, "filename" );
			if ( false === strpos( $filename, ".git/" ) ) {
				$files[] = $filename; 
			}
		}
	} catch ( \UnexpectedValueException $e ) {
		bw_trace2( 'Directory [%s] contained a directory we can not recurse into', $directory, true, BW_TRACE_ERROR );
	}
	return $files;
}
 
/**
 * Get the selected file 
 * 
 * @param array $files - array of files ( NOT USED **?** )
 * @return integer - index of the file in the $files array
 */
function _oiksc_get_file( $files ) {
  $file = bw_array_get( $_REQUEST, "file", null );
  return( $file );
} 

/**
 * API creation form
 *
 * Display drop down lists of 
 * - plugins - that have been registered on the server
 * - files - for the selected plugin
 * - APIs - functions implemented by the file
 * - API types
 * 
 * Note: This initial form does not implement any ajax so you have to click on List to refresh the display for a newly selected plugin, file or API
 *
 * When the user chooses Create then an API definition is created or updated as an oik_api post type
 * 
 * Note: The API description needs to be extracted from the phpdoc block for the API
 
 * Note: If they change the plugin then the list of files will change and things will go wrong
 * We need to be able to cater for this... using hidden fields or something ajaxy **?**
 *        
 */
function oiksc_api() {
  oik_require( "bobbforms.inc" );
  //p( "This form should magically create an oik_api (API) or a whole set of APIs for a selected plugin file" );
  p( "Choose 'List' each time you make a choice of plugin or file then 'Create API' when you have selected the API." );
  p( "To process all the APIs in a plugin use the oik-batch plugin" );
  oiksc_create_api();
  bw_form();
  stag( "table" );
  $plugin = _oiksc_get_plugin();
	
  $files = _oiksc_get_files( $plugin );
  array_unshift( $files, "0" );
  $file = _oiksc_get_file( $files );
  $api = _oiksc_get_api();
  //if ( $file ) { 
    $apis = _oiksc_get_apis( bw_array_get( $files, $file, null ) );
  //}
  bw_form_field_noderef( "plugin", "", "Select the plugin", $plugin, array( "#type" => array( "oik-plugins" /*, "oik-themes" */) ) );
  
  bw_select( "file", "Select the file", $file, array( "#options" => $files, "#optional" => true ) );
  bw_select( "api", "Select the API", $api, array( "#options" => $apis, "#optional" => true ) );
  bw_select( "api_type", "Choose the api type", "", array( "#options" => oiksc_api_types() ) );
  
  etag( "table" );
  sp();
  e( isubmit( "_oiksc_create_api", "Create API",  null, "button-primary" ) );
  e( "&nbsp;" );
  e( isubmit( "_oiksc_list_apis", "List", null, "button-secondary" ) );
  ep();
  etag( "form" );
  bw_flush();
} 

/**
 * Respond to the submit button by creating an oik_api  
 *
 * Processing of the submit button depends on which fields are set
 * 
 * Get the plugin, file and API name
 * <pre>
 * plugin   file  API  processing
 *      -     -    -   list the plugins
 *      
 *      y     -    -   list the files for the plugin
 *      y     y    -   list the APIs for the selected file
 *      y     y    y   create the API
 * </pre>
 */
function oiksc_create_api() {
  $create = bw_array_get( $_REQUEST, "_oiksc_create_api", null );
  if ( $create ) {
    // $plugins = _oiksc_get_plugins(); 
    $plugin_id = _oiksc_get_plugin(); 
    $files = _oiksc_get_files( $plugin_id );
    
    array_unshift( $files, 0 );
    
    $file = _oiksc_get_file( $files );
    //$apis = _oiksc_get_apis( $files[$file] );
    $api = _oiksc_get_api();
    $type = _oiksc_get_type();
    // $code = _oiksc_get_shortcode(); **?**
    if ( $type && $api && $file && $plugin_id ) {
      oik_require( "includes/bw_posts.inc" ); 
      $post_id = _oiksc_create_api( $plugin_id, $api, $files[$file], $type ); // Note: No $title 
      
      //$content = bw_ret();
      //oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
      //$plugin_slug = get_post_meta( $plugin_id, "_oikp_slug", true );
     // $component_type = oiksc_query_component_type( $plugin_slug );
     // global $plugin;
     // $plugin = $plugin_slug;
     // bw_update_parsed_source( $post_id, $content, oiksc_real_file( $files[$file], $component_type) );
     // echo $content; 
      oiksc_report( $post_id, "API", "creation" );      
    }
  }
}

/**
 * Report the results performing the action
 *
 */
function oiksc_report( $post_id, $type, $action, $title=null) {
  if ( $post_id ) {
    sdiv( "updated", "message" );
    sp();
    e( "$type ${action} success:" );
    e( "&nbsp;" . $title . "&nbsp;" );
    alink( null, get_permalink( $post_id ), "View $type" );
    ep();
    ediv();
  } else {
    sdiv( "error", "message" ); 
    p( "$type ${action} failure." );
    ediv();
  }
} 

/**
 * Save the hook invocations
 *  
 * The oik_hook CPT does not need a "hook called by" (Invokers) field as this can be determined in reverse from the 
 * _oik_api_hooks metadata created for each oik_api instance.
 *
 * @param ID $post_id - post ID of the API function that is being updated
 *
 * @global $oikai_association
 * @global $oikai_hook
 */
function oiksc_save_hooks( $post_id ) {
  global $oikai_hook;
  bw_trace2( $oikai_hook, "hooks", true, BW_TRACE_VERBOSE );
  if ( !empty( $oikai_hook ) ) {
    $hooks = array();
    foreach ( $oikai_hook as $key => $value ) {
      $hooks[] = $value[2];
    }
    bw_update_post_meta( $post_id, "_oik_api_hooks", $hooks );
  }  
}

/** 
 * Update hook associations defined by this API
 * 
 * The $oikai_association and $oikai_hook arrays are metadata related to the current API ( identified by $post_id )
 * 
 * Each time the API is parsed to update the API information these arrays are converted into metadata:
 *   _oik_api_hooks - lists the hooks that are invoked by the API. @see oiksc_save_hooks()
 *  
 *   _oik_api_associations - lists the associations that this function makes between hooks and their implementing functions.
 * 
 * The hook post type has a multiple select field which is a noderef to APIs ( oik_api) 
 *   _oik_hook_calls - lists the APIs that respond to this action/filter hook ie. the Implementers
 * 
 * The differences between before and after structures are used to alter the _oik_hook_implemented_by field for each of the hooks affected by this API
 */ 
function oiksc_save_associations( $post_id ) {  
  global $oikai_association;
  bw_trace2( $oikai_association, "associations", true, BW_TRACE_VERBOSE );
  $prev_associations = get_post_meta( $post_id, "_oik_api_associations", false );
  bw_trace2( $prev_associations, "previous associations", false, BW_TRACE_VERBOSE );
  
  oiksc_handle_association_differences( $prev_associations, $oikai_association, true );

  bw_update_post_meta( $post_id, "_oik_api_associations", $oikai_association );
  
} 

/** 
 * Handle any changes to the association differences defined by this API
 *
 * The post meta that we're going to change is "_oik_hook_calls" which is a multiple list of oik_api post_ids stored for each oik_hook.
 * The previous associations array contains the values to remove
 * The current associations array contains the value to add
 * If there is no difference we don't need to do anything unless force=true
 * 
 * 
 * We need to discover the post_ids of both the oik_hook and the oik_api
 * oik_hooks are expected to be present as they are dynamically built... is this dangerous
 * oik_apis are built manually
 * 
 * @param array $previous - previous associations for this API - may be null
 * @param array $current - the new set of associations for this API - may be null
 * @param bool $force - force the deletion and addition. Only used to apply corrections.
 */
function oiksc_handle_association_differences( $previous, $current, $force=false ) {
  if ( is_null( $previous ) ) {
    $previous = array();
  }  
  if ( is_null( $current ) ) {
    $current = array();
  }  
  if ( $force ) { 
    $to_delete = $previous;
  } else {
    $to_delete = array_diff( $previous, $current );
  }   
  bw_trace2( $to_delete, "to_delete", true, BW_TRACE_VERBOSE );
  if ( count( $to_delete ) ) {
    foreach ( $to_delete as $deleteme ) {
      list( $hook_id, $func_id ) = explode( ",", $deleteme );
      delete_post_meta( $hook_id, "_oik_hook_calls", $func_id ); 
    }
  }
  if ( $force ) {
    $to_add = $current;
  } else {   
    $to_add = array_diff( $current, $previous ); 
  }  
  bw_trace2( $to_add, "to_add", false, BW_TRACE_VERBOSE );
  if ( count( $to_add ) ) {
    foreach ( $to_add as $addme ) {
      //bw_trace2( $addme, "addme" );
      list( $hook_id, $func_id ) = explode( ",", $addme );
      //bw_trace2( $hook_id );
      //bw_trace2( $func_id );
      add_post_meta( $hook_id, "_oik_hook_calls", $func_id, false );
       
    }
  }
}  

/**
 * Create or update an "oik_api" post_type
 * 
 * @param ID $plugin 
 * @param string $class 
 * @param string $api
 * @param string $file
 * @param string $type
 * @param string $title
 * @return ID 
 */
function _oiksc_create_api( $plugin, $api, $file, $type, $title=null ) {
  p( "Creating API: $api file: $file" );
	
  oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
  $post_ids = oikai_get_oik_api_byname( $api );
  bw_trace2( $post_ids, "post_ids", true, BW_TRACE_DEBUG );
  if ( !$post_ids ) {
    $post_id = oiksc_create_oik_api( $plugin, $api, $file, $type, $title );     
  } else {
		$post = get_post( $post_ids[0] );
    $post_id = $post->ID;
    oiksc_update_oik_api( $post, $plugin, $api, $file, $type, $title );
  }
	oikai_load_posts( $api );
  global $oikai_post_id;
  $oikai_post_id = $post_id;
  
  /**
   * We need to ensure that the first instance of the function name does not appear as a call to the function.
   * To achieve this we need to let the pseudo method know that it's part of the class.
   */
  $class = oikai_get_class( $api );
  p( "Class $class" );
  bw_context( "classname", $class );
  //bw_context( "variable", "this" );
  //bw_context( "operator", "function" );
  
  bw_context( "paged", false );
  
  oiksc_build_callees( $api, $file, $plugin, $post_id );
  oikai_save_callees( $post_id );
  
  oiksc_save_hooks( $post_id );
  oiksc_save_associations( $post_id );
  return( $post_id );
}

/**
 * Build the callees for this API
 * 
 * @param string $api - the API name
 * @param string $file - the source file for this API
 * @param ID $plugin - the plugin/theme ID for this API
 * @param ID $post_id - the post ID for this API 
 * @param bool $echo - true if we want the output echoed 
 * 
 */ 
function oiksc_build_callees( $api, $file, $plugin, $post_id, $echo=true ) {
	//bw_trace2();
  add_action( "oikai_handle_token_T_STRING", "oikai_add_callee" );
  add_action( "oikai_record_association", "oikai_record_association", 10, 2 ); 
  add_action( "oikai_record_hook", "oikai_record_hook", 10, 3 ); 
  //add_action( "oikai_handle_token_T_ENCAPSED_STRING", "oikai_add_hook" );
  $slug = get_post_meta( $plugin, "_oikp_slug", true );
	if ( !$slug ) {
		$slug = get_post_meta( $plugin, "_oikth_slug", true );
	}
  oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
  oikai_build_apiref( $api, $file, $slug, null, $post_id, $echo );
}

/** 
 * Add a callee for the current function
 * 
 * @TODO Ensure that the first "callee" gets added if it's NOT the current id
 
 *
 * @param ID $id - the post ID of the API 
 * @global $oikai_callee
 */
function oikai_add_callee( $id ) {
  global $oikai_callee;
  if ( !isset( $oikai_callee ) ) {
    $oikai_callee = array();
    global $oikai_post_id; 
    if ( $id != $oikai_post_id ) {
      //br( "Adding $id $oikai_post_id" );
      $oikai_callee[$id] = $id;
    }
  } else { 
    $oikai_callee[$id] = $id;
  }
  bw_trace2( $oikai_callee, "oikai_callee", true, BW_TRACE_VERBOSE );
}

/** 
 * Add a hook function association.
 *
 * This records the fact that the current function is naming a function to implement a particular hook
 * 
 * @param ID $hook - the post_id of the hook
 * @param ID $func - the post_id of the API  - this may be null if the API is not yet defined
 * @global $oikai_association
 */
function oikai_record_association( $hook, $func ) {
  global $oikai_association;
  if ( $func ) {
    if ( !isset( $oikai_association ) ) {
      $oikai_association = array();
    }
    $oikai_association[] = "$hook,$func"; 
  } else {  
    bw_trace2( $oikai_association, "oikai_association", true, BW_TRACE_VERBOSE );
    //bw_backtrace();
  }
}

/** 
 * Add a hook invocation.
 *
 * This records the fact that a hook has been invoked by the function being parsed. 
 * 
 * @param string $method - the invocation method: do_action, do_action_ref_array, apply_filters, apply_filters_ref_array
 * @param string $hook - the hook name e.g. "oikai_record_hook" 
 * @param ID $post_id - the post_id of the hook
 * @global $oikai_hook
 */
function oikai_record_hook( $method, $hook, $post_id ) {
  global $oikai_hook;
  if ( !isset( $oikai_hook ) ) {
    $oikai_hook = array();
  }
  $oikai_hook[] = array( $method, $hook, $post_id ); 
  bw_trace2( $oikai_hook, "oikai_hook", true, BW_TRACE_VERBOSE );
}

/**
 * Save the callee list for the current API
 * @param ID $post_id - the ID of the calling oik_api 
 * @global $oikai_callee
 * 
 */
function oikai_save_callees( $post_id ) {
  global $oikai_callee; 
  bw_trace2( $oikai_callee, "oikai_callee", true, BW_TRACE_VERBOSE );
  //if ( $oikai_callee[0] == $post_id ) {
  //  array_shift( $oikai_callee );
  //}
  bw_update_post_meta( $post_id, "_oik_api_calls", $oikai_callee );
}

/** 
 * List functions implemented in the file
 *
 * The wp_doc_link_parse() function in wp-admin/includes/misc.inc does almost exactly the opposite of what we want.
 * It lists functions that might be documented as WordPress or PHP functions.
 * We want to list the ones that aren't.. so basically we want the contents of the $ignore_functions array! 
 * 
 * Tokens are documented in {@link http://php.net/manual/en/tokens.php}
 *
 * The values vary depending on PHP version, so we have to use the constant names. e.g. T_STRING, T_OBJECT_OPERATOR, T_FUNCTION
 * 
 * @param string $file - plugin source file name
 * @return array $functions - array of implemented function names 
 */
function oiksc_list_file_functions( $file ) {
  $real_file = WP_PLUGIN_DIR . '/' . $file;
  $content = file_get_contents( $real_file );
  bw_trace2( $content, "content", true, BW_TRACE_VERBOSE );
  $tokens = token_get_all( $content );
  $functions = _oiksc_list_functions( $tokens );
  bw_trace2( $functions, "functions", false, BW_TRACE_VERBOSE );
  return( bw_assoc( $functions) );
}

/**
 * Return the token value at $index if it's of type $type
 *
 * @param array $tokens - array of tokens from token_get_all()
 * @param integer $index - the index to the $tokens array
 * @param constant $type - constant token type required
 * @return $value - value of token or null
 *
 * This doesn't include code to return the literal values
 *
 */
function _oiksc_get_token( $tokens, $index, $type=null ) {
  if ( is_array( $tokens[$index] ) ) {
    if ( $tokens[$index][0] === $type ) {
      $value = $tokens[$index][1];
    } else {
      $value = null;
    }  
  } else {
    $value = null;
  }
  return( $value );  
}

/**
 * Return an array of implemented functions 
 *
 * @param array $tokens - array of tokens from token_get_all()
 * @return array $functions - simple array of function names
 * 
 * We're looking for sequences of tokens like this:
 
   $t     T_FUNCTION function
   $t+1   T_WHITESPACE
   $t+2   T_STRING function_name
   $t+3   (
   ...
   $t+n   )
   $t+n+1 {
   
 * Here's an idea - return the line number as the key! 
 *
 */  
function _oiksc_list_functions( $tokens ) {
  $functions = array();
  // set an arbitrary end point past which we won't validly find a function due to their not being enough tokens
  // allowing us to increment $t without any worries
  $count = count( $tokens ) - 3;
  $t = 0;
  while ( $t < $count ) {
    $function = _oiksc_get_token( $tokens, $t, T_FUNCTION );
    if ( $function ) {
      $t += 2;
      $function = _oiksc_get_token( $tokens, $t, T_STRING ); 
      if ( $function ) {
        $functions[] = $function;
      }
    }
    $t++;  
  }
  return( $functions );  
} 

/**
 * Determine the component type
 *
 * Component types supported are:
 *
 * component_type | When
 * -------------- | -------------------
 * "wordpress"    | $plugin == "wordpress"
 * "plugin"       | $plugin directory exists in WP_PLUGIN_DIR
 * "theme"        | $plugin directory exists in WP_TEMPLATE_DIR 
 *
 * We expect mu-plugins to be delivered as normal plugins and then relocated as required.
 * Ditto for dropins {@see _get_dropins() }
 * 
 * @param string $plugin - the plugin or theme slug ( e.g. 'wordpress', 'oik', or 'genesis' )
 * @return string - the component type
 */
function oiksc_query_component_type( $plugin ) {
  if ( $plugin == "wordpress" ) {
    $component_type = "wordpress";
  } elseif ( file_exists( WP_PLUGIN_DIR . "/$plugin" ) ) {
    $component_type = "plugin";
  } elseif ( file_exists( get_theme_root() . "/$plugin" ) ) {
    $component_type = "theme"; 
  } else {
    $component_type = null;
  }
  //echo "Component type: $component_type" . PHP_EOL;
	bw_trace2( $component_type, "component_type", true, BW_TRACE_DEBUG );
  return( $component_type );
}

/**
 * Load the filenames for the selected component
 *
 * @param string $plugin - the component name - it could be a theme
 * @param string $component_type - the component type: "plugin"|"theme"| ?
 * @return array - array of file names
 */
function oiksc_load_files( $plugin, $component_type ) {       
  switch ( $component_type ) {
    case "wordpress":
      oik_require( "oik-list-wordpress-files.php", "oik-batch" );
      $files = _la_get_wordpress_files();
      $plugin = null;
      break;
    case "plugin":
      //wp_register_plugin_realpath( WP_PLUGIN_DIR . "/$plugin/." );
      $files = _oiksc_get_files( WP_PLUGIN_DIR . "/$plugin" );
      break;
    case "theme": 
      $files = _oiksc_get_files( get_theme_root() . "/$plugin", false );
      break;
    default:
      echo "Unrecognised component: $plugin" . PHP_EOL;
  }
  bw_trace2( $files, "files", true, BW_TRACE_DEBUG );
  //echo "WP_PLUGIN_DIR" . WP_PLUGIN_DIR . PHP_EOL;
  //echo "THEME_ROOT" . get_theme_root() . PHP_EOL;
  return( $files );
}


/**
 * Invoke the callback function for each file
 * 
 * 
 * @param array $files - 
 * @param string $plugin - the component name
 * @param string $component_type - the component type
 * @param string $callback - callback function to invoke
 * @param integer $start first function to process 
 */
function oiksc_do_files( $files, $plugin, $component_type, $callback, $start=1 ) {
  $count = 0;
	$total = count( $files );
  foreach ( $files as $file ) {
    //echo oiksc_relative_filename( $file ) . PHP_EOL;
    
    switch ( $component_type ) {
      case "wordpress" :
        $rfile = ABSPATH . $file;
        break;
      case "plugin":
        //echo $file;
        $rfile = plugin_basename( $file ); 
        break;
      case "theme":
        //echo $file;
				oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
        $rfile = oiksc_theme_basename( $file ); 
        //echo $rfile;
      break;
      default:
        // Shouldn't get here
    } 
    $count++;   
    echo "File:$count,$total,$rfile,$file" . PHP_EOL;
    //echo 
    call_user_func( $callback, $rfile, $plugin, $component_type, $start  );
  }
}   


