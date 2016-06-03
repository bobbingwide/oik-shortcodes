<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Lazy implementation of "run_oik-shortcodes.php" 
 *
 * Syntax: oikwp oik-create-apis.php [component [previous [start]]]
 * run from the oik-shortcodes plugin directory
 * 
 * Where: 
 * - component is the selected component e.g. wordpress
 * - previous is the SHA of the previously completed run
 * - start is the index of the file to start from
 *
 * In the case of a Git repository being updated/replaced such that the SHAs have changed
 * then you need to pass a new previous SHA that is for a commit that is equivalent to, or earlier than the one
 * that's been done. 
 
 * Perform createapi2.php against the local database
 * 
 * Uses _local versions of the functions from oik-batch/creatapi2.php
 *
 * @TODO libs/oik-git.php should be loaded as a library
 *
 */
function oiksc_lazy_run_oik_shortcodes() {
	
	echo "Running creatapi2 directly" . PHP_EOL;

  oik_require( "admin/oik-apis.php", "oik-shortcodes" );
	oik_require( "oik-ignore-list.php", "oik-batch" );
	oik_require( "oik-login.inc", "oik-batch" );
	oik_require( "libs/oik-git.php", "oik-batch" );
	

  //$component = "allow-reinstalls";
	//$previous = null;
	//$start = null;
	
	
	// We'll attempt to find previous and start from the parse_status field for this component
	// but this COULD be used to override the logic
	
	$component = oik_batch_query_value_from_argv( 1, null );
	$previous = oik_batch_query_value_from_argv( 2, null );
	$start = oik_batch_query_value_from_argv( 3, 1 );
	
	$met = ini_get( 'max_execution_time' );
	echo "Max execution time is: $met" . PHP_EOL;
	ini_set('memory_limit','2048M');
	
	//bw_trace2( $_SERVER, "_SERVER" );
	// @TODO Cater for a start index for the components, to allow for restarting where we left off from the complete list of components 
	if ( $component ) {
		$components = bw_as_array( $component );
	} else {
		$components = array();
		$components = oiksc_load_all_components( $components );
	}
	
	oiksc_preloader();
	oiksc_reassign_hooks();
	
	oiksc_preload_content(); 
	
	foreach ( $components as $component ) {
		_ca_doaplugin_local( $component, $previous, $start );
	} 
}

/**
 * Load all components 
 *
 * Load all the components that are defined and return an array of their plugin/theme slugs
 *
 * We assume:
 * - all of these components are available as Git repositories. 
 * - ... we could check the GitHub repo field but at the end of the day we want to support ALL the components anyway
 * - that the Git repo status is up to date
 * - that the version we're running locally is in line with the Git repo.
 * 
 * We load plugins and themes separately, processing plugins first
 * @TODO Add logic for themes
 * @TODO Cater for plugins we don't want to document but which are defined as plugins
 * 
 * @param array $components 
 * 
 */
function oiksc_load_all_components( $components) {
	$components = oiksc_load_all_plugins( $components );
	$components = oiksc_load_all_themes( $components );
	print_r( $components );
	return( $components );
}

/**
 * Return the registered oik-plugins
 *
 */
function oiksc_load_all_plugins( $components ) {
	oik_require( "includes/bw_posts.inc" );
	$atts = array( "post_type" => "oik-plugins"
							 , "numberposts" => -1
							 );
	$posts = bw_get_posts( $atts );
	foreach ( $posts as $post ) {
		$component = get_post_meta( $post->ID, "_oikp_slug", true );
		$components[] = $component;
	}
	return( $components ); 
}

	
/**
 * Return the registered oik-themes
 *
 */
function oiksc_load_all_themes( $components ) {
	oik_require( "includes/bw_posts.inc" );
	$atts = array( "post_type" => "oik-themes"
							 , "numberposts" => -1
							 );
	$posts = bw_get_posts( $atts );
	foreach ( $posts as $post ) {
		$component = get_post_meta( $post->ID, "_oikth_slug", true );
		$components[] = $component;
	}
	return( $components ); 
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
function _ca_doaplugin_local( $component, $previous=null, $start=null ) {
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
				
					$oiksc_parse_status = oiksc_parse_status::instance();
					$oiksc_parse_status->register_fields();
					$oiksc_parse_status->set_component( $component_preloaded->ID );
					$oiksc_parse_status->fetch_status();
					$finished = $oiksc_parse_status->finished_two_passes();
					if ( $finished && null == $previous ) {
						$previous = $oiksc_parse_status->get_to_sha( $previous );
						echo "We've finished the previous pass: $previous" . PHP_EOL;
					} else {
						
						$previous = $oiksc_parse_status->get_from_sha( $previous );
						$start = $oiksc_parse_status->get_file_m( $start );
						echo "Continuing previous pass: $previous from $start" . PHP_EOL;
						
					}
					$files = oikb_list_changed_files( $previous, $plugin, $component_type, $oiksc_parse_status );
					if ( null === $files ) {
						$files = oiksc_load_files( $plugin, $component_type );
						$files = oikb_maybe_do_files( $files, $previous, $component, $component_type );
					}
					if ( $component_type == "wordpress" ) {
						$files = oikb_filter_wordpress_files( $files );
					}
					$finished = $oiksc_parse_status->finished_two_passes();
					if ( !$finished ) {
						oiksc_do_files( $files, $plugin, $component_type, "_ca_dofile_local", $start );
					}
				} else {
					echo "Plugin/theme not defined: $component" . PHP_EOL;
				}
			} else {
				echo "Invalid plugin/theme: $component" . PHP_EOL;
			}
		} else {
			//echo "Missing --plugin= parameter" . PHP_EOL;
			echo "Cannot determine component type for: $component" . PHP_EOL;
			echo "Perhaps the component is not available." . PHP_EOL;
			bw_trace2( $component, "Missing component", true, BW_TRACE_WARNING );
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
      //echo $file . PHP_EOL;
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
  $apis = _oiksc_get_apis2( $file, true, $component_type, $plugin_p );
  $file = strip_directory_path( ABSPATH, $file );
  foreach ( $apis as $api ) {
		$apiname = $api->getApiName();
		echo "Processing API: $apiname, $file, $plugin, $component_type " . PHP_EOL;
		$needs_processing = _ca_does_api_need_processing( $api, $file, $component_type, $plugin );
		if ( $needs_processing ) {
			oiksc_local_oiksc_create_api( $plugin, $file, $component_type, $api );
			$discard = bw_ret();
		} else {
			echo "Nothing to do for: $apiname" . PHP_EOL;
		}	
  }
}

/**
 * Determine if this API needs processing
 * 
 * If the parsed source is the latest then no, it doesn't
 * If the parsed source is not the latest then, we have to check if the API has changed since it was last parsed.
 *
 * @param object $api object
 * @param string $file
 * @param string $component_type
 * @param string $plugin
 * @return book true if it needs processing, false otherwise
 */
function _ca_does_api_need_processing( $api, $file, $component_type, $plugin ) {
	$needs_processing = true;
	$apiname = $api->getApiName();
	$post_ids = oikai_get_oik_api_byname( $apiname );
	if ( $post_ids ) {
		$post_id = $post_ids[0];
		oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
		$oiksc_parsed_source = oiksc_parsed_source::instance();
		$parsed_source = $oiksc_parsed_source->get_latest_parsed_source_by_sourceref( $file, $component_type, $post_id, $plugin );
		if ( $parsed_source ) {
			$needs_processing = false;
		} else {
		
			$fileName = oiksc_real_file( $file, $component_type, $plugin ); 
			// Here we need refFunc
			$refFunc = $api->load_and_reflect();
			//print_r( $refFunc );
			$sources = oikai_load_from_file( $fileName, $refFunc );
			//print_r( $sources );
			$needs_processing = $oiksc_parsed_source->is_parsing_necessary( $sources );
		}
	}
	bw_trace2( $needs_processing, "Needs processing?", true, BW_TRACE_VERBOSE );
	return( $needs_processing );	
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
 * @param string $plugin_slug
 * @param string $file relative file name 
 * @param string $component_type
 * 
 */
function oiksc_local_oiksc_create_file( $plugin_slug, $file, $component_type ) {
  global $plugin_post;
	echo "Processing: $file, $plugin_slug, $component_type " . PHP_EOL;
	
  if ( is_null( $plugin_slug )) {
    $plugin_slug = 'wordpress';
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
  //$filename = oik_pathw( $file, $plugin, $component_type );
	$filename = $file;
	global $plugin;
	$plugin = $plugin_slug;
  $parsed_source = oiksc_display_oik_file( $filename, $component_type, $file_id, $plugin_slug, true );
	echo PHP_EOL;
	oiksc_yoastseo( $file_id, $file, $plugin_slug, "file" );
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
 * @param string $plugin the plugin name
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
	if ( $plugin == "wordpress" ) {
		$plugin = "WordPress";
	}
	$metadesc = "$name $desc $plugin $type";
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
	bw_trace2( $_POST, "_POST", false, BW_TRACE_VERBOSE );
	unset( $_POST );
  global $oikai_hook;
	//var_dump( $oikai_hook );
	$oikai_hook = null;
	//var_dump( $oikai_hook );
	global $oikai_association;
	$oikai_association = null;
	global $oikai_callee;
	$oikai_callee = null;
}

/**
 * Reassign hooks 
 * 
 * Everything is being programmatically generated
 * so we don't have to worry about certain hooks which may be run zillions of times, effectively doing nothing except wasting time.
 * Here we remove actions and filters that aren't necessary
  
: 0   bw_trace_attached_hooks;9 bw_trace_backtrace;9
: 10   convert_invalid_entities;1 wp_filter_post_kses;1
: 50   balanceTags;1

 * Note: remove_action() calls remove_filter() so you can use either to get the same result.

 */
function oiksc_reassign_hooks() {
	remove_filter( 'content_save_pre', 'convert_invalid_entities', 10 );
	remove_filter( 'content_save_pre', 'wp_filter_post_kses', 10 );
	remove_filter( 'content_save_pre', 'balanceTags', 50 );
	remove_filter( "pre_get_posts", "oik_types_pre_get_posts", 10 );
	remove_filter( "pre_get_posts", "oik_types_pre_get_posts_for_archive", 11 );
	//remove_filter( "sanitize_title", "sanitize_title_with_dashes", 10 );
	remove_filter( "pre_kses", "wp_pre_kses_less_than", 10 );
}

