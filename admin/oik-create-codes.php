<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Create oik-shortcodes entries programmatically in a batch process
 *
 * Syntax: oikwp oik-create-codes.php	url=jetpack.wp.a2z
 * when in this directory as the current directory
 * 
 */
oik_create_codes_loaded();

/**
 * Create oik-shortcode entries programmatically
 *
 * After the plugins, themes and APIs have been created we need to update the definitions of all the shortcodes
 *
 */
function oik_create_codes_loaded() {
	$required_component = oik_batch_query_value_from_argv( 1, null );
	oik_require( "admin/oik-apis.php", "oik-shortcodes" );
	$component_id = oiksc_get_component_by_name( $required_component );
	do_action( "oik_add_shortcodes" );
	oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
	$shortcodes = oik_create_codes_get_all_shortcodes();
	foreach ( $shortcodes as $shortcode => $component ) {
		// if we have the information then create the shortcode
		echo PHP_EOL;
		echo "$shortcode: $component" . PHP_EOL;
		if ( $component == $required_component ) {
			oikb_get_response( "Continue?", true );
			oik_create_codes_create_code( $shortcode, $component_id, $required_component );
		}	
	}
}

/**
 * Return array of shortcodes and implementing components
 * 
 * Note: Shortcodes may be implemented by multiple components, although only one may be active at any one time.
 * 
 * @TODO... so this array is potentially wrong.
 *
 * Haha! Caught out by some fancy PHP 7 code
 * 
 * `function oik_create_codes_get_all_shortcodes() : array {`
 * 
 */
function oik_create_codes_get_all_shortcodes() {
	global $shortcode_tags;
	$shortcodes = array();
	print_r( $shortcode_tags );
	add_filter( "oiksc_shortcodes_components", "oiksc_shortcodes_components_wordpress", 10 );
	add_filter( "oiksc_shortcodes_components", "oiksc_shortcodes_components_oik", 10 );
	
	$shortcodes = $shortcode_tags;
	$shortcodes = apply_filters( "oiksc_shortcodes_components", $shortcodes );
	//print_r( $shortcodes );
	return( $shortcodes );
}


/**
 * Implement "oiksc_shortcodes_components" for WordPress shortcodes
 *  
 * Return the plugin name for WordPress shortcodes.
 * @TODO Check if default themes contain shortcodes
 *
 * @param array $shortcodes - mapping of shortcode to plugin/implementing function
 * @return array updated array for WordPress shortcodes 
 */
function oiksc_shortcodes_components_wordpress( $shortcodes ) {
	$shortcodes['embed'] = 'wordpress';
	$shortcodes['wp_caption'] = 'wordpress';
	$shortcodes['caption'] = 'wordpress';
	$shortcodes['gallery'] = 'wordpress';
	$shortcodes['playlist'] = 'wordpress';
	$shortcodes['audio'] = 'wordpress';
	$shortcodes['video'] = 'wordpress';
	return( $shortcodes );
}

/**
 * Implement "oiksc_shortcodes_components" for 'oik' known shortcodes
 * 
 * For each shortcode, if we can find a $bw_sc_file[$shortcode] value  
 * then we set the plugin name to the component part of the $file's path.
 * 
 * Doing the reverse of bw_add_shortcode_file().
 * 
 * 
 * @param array $shortcode associative array mapping shortcodes to implementing function or plugin
 * @return array updated to reflect the implementing plugin
 */ 
function oiksc_shortcodes_components_oik( $shortcodes ) {
	global $bw_sc_file;
	foreach ( $shortcodes as $shortcode => $function ) {
		$file = bw_array_get( $bw_sc_file, $shortcode, null );
		if ( $file ) {
			$component = oiksc_get_component_name_from_file( $file );
			echo "$shortcode : $component" . PHP_EOL;
			$shortcodes[$shortcode] = $component; 
		}
	}
	return( $shortcodes );
}

/**
 * Find component from file name
 *
 * This extracts the plugin or theme name from the file name
 * 
 * @param string $file fully qualified file name of file implementing the shortcode
 * @return string component name
 */
function oiksc_get_component_name_from_file( $file ) {
	oik_require( "oik-list-wordpress-files.php", "oik-batch" );
	$file = strip_directory_path( ABSPATH, $file );
	$parts = explode( "/", $file );
	$component = $parts[2];
	return( $component );
}

/**
 * Programmatically create an oik-shortcode
 *
 * This is run in batch - doing what front end did
 *
 * @param string $shortcode the shortcode name
 * @param ID $component_id the implementing component ID
 * @param string $required_component the component name
 */ 
function oik_create_codes_create_code( $shortcode, $component_id, $required_component ) {
	$_REQUEST['_oiksc_create_shortcode'] = 'submit'; 
	$_REQUEST['code'] = $shortcode;
	$_REQUEST['plugin'] = $component_id;
	echo "Creating $shortcode: $required_component: $component_id" . PHP_EOL;
	$ID = oiksc_create_shortcode();
	bw_flush();
}


/**
 * Return the component ID for the name
 *
 */
if ( !function_exists( 'oiksc_get_component_by_name')) {
	function oiksc_get_component_by_name( $component_name ) {
		$component_type=oiksc_query_component_type( $component_name );
		$plugin_post   =oiksc_load_component( $component_name, $component_type );
		if ( $plugin_post ) {
			$component_id=$plugin_post->ID;
		} else {

			gob();
		}

		return ( $component_id );
	}
}
	





