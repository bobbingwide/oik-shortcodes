<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Create oik-shortcodes entries programmatically in a batch process
 *
 * Syntax: oikwp oik-create-codes.php
 * when in this directory as the current directory
 * 
 */
oik_create_codes_loaded();

/**
 * Create oik-shortcode entries programmatically
 *
 * After the plugins, themes and APIs have been created
 * we need to update the definitions of all the shortcodes
 *
 * How do we do this?
 */
function oik_create_codes_loaded() {
	$required_component = oik_batch_query_value_from_argv( 1, null );
	do_action( "oik_add_shortcodes" );
	oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
	$shortcodes = oik_create_codes_get_all_shortcodes();
	foreach ( $shortcodes as $shortcode => $component ) {
		// if we have the information then create the shortcode
		
		echo "$shortcode: $component" . PHP_EOL;
		if ( $component == $required_component ) {
			oikb_get_response( "Continue?", true );
			oik_create_codes_create_code( $shortcode, $component );
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
 */
function oik_create_codes_get_all_shortcodes() : array {
	global $shortcode_tags;
	$shortcodes = array();
	print_r( $shortcode_tags );
	add_filter( "oiksc_shortcodes_components", "oiksc_shortcodes_components_wordpress" );
	add_filter( "oiksc_shortcodes_components", "oiksc_shortcodes_components_oik" );
	
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
 * @TODO Otherwise we can try using reflection functions.
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
 */ 
function oik_create_codes_create_code( $shortcode, $component ) {
	$_REQUEST['_oiksc_create_shortcode'] = 'submit'; 
	$_REQUEST['code'] = $shortcode;
	$_REQUEST['plugin'] = $component;
	echo "Creating $shortcode: $component" . PHP_EOL;
	$ID = oiksc_create_shortcode();
	bw_flush();
}





