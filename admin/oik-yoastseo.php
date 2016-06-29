<?php // (C) Copyright Bobbing Wide 2016

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
 * @param string $type - API, Class, File, Shortcode or shortcode parameter
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
