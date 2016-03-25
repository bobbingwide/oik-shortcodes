<?php // (C) Copyright Bobbing Wide 2016

/**
 * Lazy implementation of "run_oik-shortcodes.php" 
 * to perform createapi2.php against the local database
 * 
 * Uses _local versions of the functions from oik-batch/creatapi2.php
 * 
 *
 */
function oiksc_lazy_run_oik_shortcodes() {
	
	echo "Running creatapi2 directly" . PHP_EOL;

  oik_require( "admin/oik-apis.php", "oik-shortcodes" );
	oik_require( "oik-ignore-list.php", "oik-batch" );
	oik_require( "oik-login.inc", "oik-batch" );
	

  //$component = "allow-reinstalls";
	//$previous = null;
	//$start = null;
	
	$component = bw_array_get( $_SERVER['argv'], 1, "allow-reinstalls" );
	$previous = bw_array_get( $_SERVER['argv'], 2, null );
	$start = bw_array_get( $_SERVER['argv'], 3, 1 );
	
	$met = ini_get( 'max_execution_time' );
	echo "Max execution time is: $met" . PHP_EOL;
	
	
	ini_set('memory_limit','2048M');
	
	//bw_trace2( $_SERVER, "_SERVER" );  
	$components = bw_as_array( $component );
	
	oiksc_preloader();
	
	oiksc_preload_content(); 
	
	//foreach ( $components as $component ) {
		_ca_doaplugin_local( $component, $previous, $start );
	//} 
		
		
}

/**  
 * Create the APIs for a component
 *
 * List the files for the component
 * Components supported:
 * "wordpress" - all files in the WordPress installation excluding wp-content
 * <i>plugin</i> - all the files in the named plugin 
 * <i>theme</i> - all the files in the named theme
 *
 * Process each file in the component, defining the file, classes, methods and APIs
 * 
 * @param string $component - the name of the plugin or theme, or "wordpress"
 * @param string $previous - the previous version to compare against - for performance
 * @param string $start 
 */
function _ca_doaplugin_local( $component, $previous=null, $start=1 ) {
	global $plugin;
	$plugin = $component;
	if ( $plugin ) {
		$component_type = oiksc_query_component_type( $plugin );
		if ( $component_type ) {
			echo "Doing a $component_type: " . $plugin . PHP_EOL;
			$response = oikb_get_response( "Continue?", true );
			if ( $response ) {
				oik_require( "admin/oik-apis.php", "oik-shortcodes" );
				//wp_register_plugin_realpath( WP_PLUGIN_DIR . "/$plugin/." );
				oik_require( "oik-list-wordpress-files.php", "oik-batch" );
				oik_require( "oik-list-previous-files.php", "oik-batch" );
				$component_preloaded = oiksc_pre_load_component( $plugin, $component_type );
				if ( $component_preloaded ) {
					$files = oikb_list_changed_files( $previous, $plugin, $component_type );
					if ( null === $files ) {
						$files = oiksc_load_files( $plugin, $component_type );
						$files = oikb_maybe_do_files( $files, $previous, $component, $component_type );
					}
					if ( $component_type == "wordpress" ) {
						$files = oikb_filter_wordpress_files( $files );
					}
					oiksc_do_files( $files, $plugin, $component_type, "_ca_dofile_local", $start );
				} else {
					echo "Plugin/theme not defined: $component" . PHP_EOL;
				}
			} else {
				echo "Invalid plugin/theme: $component" . PHP_EOL;
			}
		} else {
			//echo "Missing --plugin= parameter" . PHP_EOL;
			echo "Cannot determine component type for: $component" . PHP_EOL;
			gob();
		}
	}	
}


/**
 * Process a file
 *
 * We process .php and .inc files
 * Note: WordPress doesn't have any .inc files
 *
 * @TODO: Expand to cover ALL files.
 *
 * @param string $file - the file name - fully qualified
 * @param string $plugin - the plugin or theme name
 * @param string $component_type "plugin", "theme" or "wordpress"
 */ 
function _ca_dofile_local( $file, $plugin, $component_type ) {
  if ( $plugin ) {
    $inignorelist = _la_checkignorelist( $file );
  } else {
    $inignorelist = false;
  }
  if ( !$inignorelist ) {    
    echo "Processing: $file". PHP_EOL;
    $ext = pathinfo( $file, PATHINFO_EXTENSION );
    $ext = strtolower( $ext );
    $exts = bw_assoc( array( "php", "inc" ));
    $validext = bw_array_get( $exts, $ext, false );
    if ( $validext ) {
      _lf_dofile_local( $file, $plugin, $component_type );
      _ca_doapis_local( $file, $plugin, $component_type ); 
    }
  }  
}


/**
 * Create the apis for a particular file in a plugin
 * 
 * 
 * Obtain a list of the APIs in the source file. 
 * For each API send a message to the server to create/update the API.
 * Note: We ignore classes - these are created dynamically
 *
 *
 * @param string $file - file name to load
 * @global $plugin - which may be null when processing WordPress core
 */
function _lf_dofile_local( $file, $plugin, $component_type ) {
  //global $plugin;
  //if ( oikb_get_site() ) {
    if ( $plugin == "wordpress" ) {
      $file = strip_directory_path( ABSPATH, $file );
    } else {
      echo $file . PHP_EOL;
        
    }
    echo "Processing file: $plugin,$file" . PHP_EOL;
    $response = oikb_get_response( "Continue to process file?" );
    if ( $response ) {
      //$apikey = oikb_get_apikey();
      //oik_require( "oik-admin-ajax.inc", "oik-batch" );
      //oikb_admin_ajax_post_create_file( oikb_get_site(), $plugin, $file, $apikey );
			
			oiksc_local_oiksc_create_file( $plugin, $file, $component_type );
			$discard = bw_ret();
    }
    echo "Processing file: $plugin,$file ended" . PHP_EOL;
		
  //}
}



/**
 * Create the apis for a particular file in a plugin
 * 
 * 
 * Obtain a list of the APIs in the source file. 
 * For each API send a message to the server to create/update the API.
 * Note: We ignore classes - these are created dynamically
 *
 *
 * @param string $file - file name to load
 * @global $plugin - which may be null when processing WordPress core
 */
function _ca_doapis_local( $file, $plugin_p, $component_type ) {
  global $plugin;
  $plugin = $plugin_p;
  echo "Processing valid: $plugin $file $component_type" . PHP_EOL;
	
	
  $apis = _oiksc_get_apis2( $file, true, $component_type );
	
  $file = strip_directory_path( ABSPATH, $file );
  foreach ( $apis as $api ) {
		
		$apiname = $api->getApiName();
		
		echo "Processing API: $apiname, $file, $plugin, $component_type " . PHP_EOL;
		oiksc_local_oiksc_create_api( $plugin, $file, $component_type, $api );
			$discard = bw_ret();
    /* $response = _ca_checkforselected_api( $apiname, $count );
    if ( $response ) {
      $response = oikb_get_response( "Continue to create API?" );
    }  
    if ( $response ) {
		
    } 
		*/ 
    
  }
}

 
 
/**
 * Check for the selected API
 */
function _ca_checkforselected_api( $api, $count ) {
  global $selected_api;
  if ( $selected_api ) {
    $selected = ( $api == $selected_api ) || ( $count >= $selected_api );
  } else {
    $selected = true; 
  }
  return( $selected );
}


/**
 * Preload the libraries used in create_file and create_api
 */
function oiksc_preloader() {
  do_action( "oik_loaded" );
	oiksc_autoload();
	
	
	oik_require( "includes/bw_posts.inc" ); 
	oik_require( "admin/oik-apis.php", "oik-shortcodes" );
	oik_require( "admin/oik-files.php", "oik-shortcodes" );
	oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
	oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
	oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );


}

/**
 * Preload content
 * 
 * See if preloading ALL the content makes any difference on second and subsequent runs when running locally.
 */ 
function oiksc_preload_content() {

	$api_cache = oiksc_api_cache::instance();
	$api_cache->preload_all_apis();
	
}

/**
 * Pre-load the component we're processing
 *
 * @param string $plugin - the plugin or theme slug
 * @param string $component_type "plugin", "theme", "wordpress"
 * @param bool $force unnecessary parameter **?**
 */
function oiksc_pre_load_component( $plugin, $component_type, $force=false ) {
	global $plugin_post;
 	//if ( !$plugin_post ) {
		$plugin_post = oiksc_load_component( $plugin, $component_type );
  //}
	if ( null == $plugin_post ) {
		bw_trace2( $plugin_post, "Missing plugin_post", true, BW_TRACE_ERROR );
	}
	return( $plugin_post );
}	

/**
 * Create an oik_file locally
 *
 * Create or update an "oik_file" post AND parse all the classes, methods and APIs implemented, including the main file,
 * using logic similar to createapi2.php
 * 
 */
function oiksc_local_oiksc_create_file( $plugin, $file, $component_type ) {
  global $plugin_post;
	echo "Processing: $file, $plugin, $component_type " . PHP_EOL;
	
  if ( is_null( $plugin )) {
    $plugin = 'wordpress';
    //$file = ABSPATH . $file;
    //$file = str_replace( "\\", "/", $file );
    bw_trace2( $file, "file" );
  } 
	if ( is_null( $plugin_post ) ) {
		bw_trace2( null, null, true, BW_TRACE_ERROR );
		bw_backtrace();
	}
  // $plugin_post = oikp_load_plugin( $plugin );
  $file_id = _oikai_create_file( $plugin_post->ID, $file ); 
  $filename = oik_pathw( $file, $plugin, $component_type );
  $parsed_source = oiksc_display_oik_file( $filename, $component_type, $file_id, true );
	echo PHP_EOL;
	oiksc_yoastseo( $file_id, $file, $plugin, "file" );
	oiksc_reset_globals();
}

/**
 * Create or update an oik_api locally
 *
 * The API may contain the API name in the format of 
 *  'class::api' - representing a method
 *  'api' - representing a function
 *  'class::' - representing a class
 * 
 * The value we store in "_oik_api_name" for a method will be in the class::api format 
 * 
 * API names are normally expected to be unique. This code does not cater for use of namespacing. 
 *
 * @param string $plugin
 * @param string $file
 * @param string $component_type
 * @param object $api_object
 */
function oiksc_local_oiksc_create_api( $plugin, $file, $component_type, $api_object ) {

	global $plugin_post;
	$api = $api_object->getApiName();	
	echo "API: $api " . PHP_EOL . PHP_EOL;
	if ( $plugin_post ) {
		//$func = oikai_get_func( $api, null ); 
		$func = $api_object->getMethod();
		if ( $func ) {
			echo "Processing: $func,$api,$file". PHP_EOL;
			$type = $api_object->getApiType();
			$title = $api_object->getShortDescription();
			$post_id = _oiksc_create_api( $plugin_post->ID, $api, $file, $type, $title ); 
			oiksc_yoastseo( $post_id, $api, $plugin, $type, $title );
		} else {
			echo "Processing classref: $api,$file" . PHP_EOL;
			$post_id = oikai_get_classref( $api, null, $plugin_post->ID, $file );
			oiksc_yoastseo( $post_id, $api, $plugin, "class" );
		}
	} else {
		e( "Invalid plugin: $plugin ");
	}
	oiksc_reset_globals();    
}

/**
 * Create Yoast SEO data
 *
 * Set fields specifically for Yoast SEO
 * 
 * WordPress SEO aka YoastSEO has a number of fields which, if not set
 * it goes away and attempts to determine from the content and excerpt.
 * 
 * This is time consuming at front-end runtime so we need to set the values ourselves.
 * 
 * @param ID $id 
 * @param string $name - the API, Class or Filename
 * @param string $plugin - component name: wordpress, plugin or theme
 * @param string $type - API, Class or File
 * @param string $desc - short description
 *
 */
function oiksc_yoastseo( $id, $name, $plugin, $type='API', $desc=null ) {
	$metadesc = "$name - $plugin $type - $desc";
	$focuskw = "$name $plugin $type";
	update_post_meta( $id, "_yoast_wpseo_metadesc", $metadesc );
	update_post_meta( $id, "_yoast_wpseo_focuskw", $focuskw );
}

/**
 * Reset globals
 * 
 * Unset all values in $_POST, ready for the next request
 */
function oiksc_reset_globals() {
	bw_trace2( $_POST, "_POST", false );
	unset( $_POST );
  global $oikai_hook;
	var_dump( $oikai_hook );
	$oikai_hook = null;
	var_dump( $oikai_hook );
	
	global $oikai_association;
	$oikai_association = null;
	global $oikai_callee;
	$oikai_callee = null;
	
}

