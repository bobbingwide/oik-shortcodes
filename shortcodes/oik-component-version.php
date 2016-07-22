<?php // (C) Copyright Bobbing Wide 2016

/**
 * Implement _component_version virtual field
 
 * Note: call_user_func_array() calls the callback given by the first parameter with the parameters in param_arr.
 * ie. We don't get an array - each parameter is separated
 *
 * @param integer $ID - the post ID of the current post
 * @return string - currently expected to be null
 */
function oik_component_version( $ID ) {
	if ( $ID ) {
		$component_name = oik_query_component_by_ID( $ID );
		if ( $component_name ) {
			$version = oik_component_version_get_version( $component_name );
		} else {
			$version = null;
		}
		span( "_component_version" );
		e( $version );	
		epan();
	} else {
		// No ID so don't display anything
	}
}

/**
 * Determine the component name given the post ID
 *
 * By following noderefs, recursively if necessary, we should be able to reach the component name, from which we may determine the version.
 * 
 * The pre_mapping_keys array shows which field to access to determine the next ID to process.
 *
 * @TODO The pre_mapping_types array should actually be a simple associative array.
 * It tells us whether or not we can find a route to the component.
 * We could also use the field APIs to see if the virtual field is associated with the post type.
 * 
 * @param ID $id - the post ID we've got
 * @param string|null - the referenced component 
 */
function oik_query_component_by_ID( $ID ) {
	$post_type = get_post_type( $ID );
	$pre_mapping_types = array( "oik_pluginversion" => "oik-plugins" 
														, "oik_premiumversion" => "oik-plugins"
														, "oik_themeversion" => "oik-themes"
														, "oik_themiumversion" => "oik-themes"
														, "oik_shortcodes" => "oik_shortcodes"
														, "shortcode_example" => "oik_shortcodes"
														, "oik_api" => "oik_api"
														, "oik_class" => "oik_class"
														, "oik_file" => "oik_file"
														);
	$pre_map_type = bw_array_get( $pre_mapping_types, $post_type, null );
	$mapping = array( "oik-plugins" => "_oikp_name" 
									, "oik-themes" => "_oikth_slug"		
									);

	$meta_key = bw_array_get( $mapping, $post_type, null );
	if ( $meta_key ) {
		oik_component_version_type( $post_type );
		$meta_value = get_post_meta( $ID, $meta_key, true );
	} else {
		$pre_mapping_keys = array( "oik_pluginversion" => "_oikpv_plugin" 
														 , "oik_premiumversion" => "_oikpv_plugin"
														 , "oik_themeversion" => "_oiktv_theme"
														 , "oik_themiumversion" => "_oiktv_theme"
														 , "oik_shortcodes" => "_oik_sc_plugin"
														 , "shortcode_example" => "_sc_param_code"
														 , "oik_api" => "_oik_api_plugin"
														 , "oik_class" => "_oik_api_plugin"
														 , "oik_file" => "_oik_api_plugin"
														 );		
		$pre_map_type = bw_array_get( $pre_mapping_types, $post_type, null );
		if ( $pre_map_type ) {
			$pre_map_key = bw_array_get( $pre_mapping_keys, $post_type, null );
			$component_ID = get_post_meta( $ID, $pre_map_key, true );
			$meta_value = oik_query_component_by_ID( $component_ID);
		} else {
			$meta_value = null;
		}
	}
	return( $meta_value );
}

/**
 * Set/return the component type
 * 
 * We should already know this from previous queries
 * 
 *  
 */
function oik_component_version_type( $type=null ) {
	static $component_type;
	if ( $type ) {
		$component_type = $type;
	}
	return( $component_type );
}

/**
 * Get the component version
 *
 * Given the component name, return the version
 * When the name is "wordpress" we return the current WordPress version
 * 
 */
function oik_component_version_get_version( $component_name ) {
	$version = null;
	if ( "n/a" == $component_name ) {
		global $wp_version;
		$version = $wp_version;
	} else {
		$component_type = oik_component_version_type();
		//echo $component_type . PHP_EOL;
		require_once( ABSPATH . "wp-admin/includes/plugin.php" );
		if ( $component_type == "oik-plugins" ) {
			$oik_plugins = oik_require_lib( "oik_plugins" );
			$version = _bw_get_plugin_version( $component_name );
		} elseif ( $component_type == "oik-themes" ) {
			$oik_themes = oik_require_lib( "oik_themes" );
			$version = _bw_get_theme_version( $component_name );
		} else {
			// Not determined - don't display anything
		}
	}
	return( $version );
	
}



