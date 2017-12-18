<?php // (C) Copyright Bobbing Wide 2016,2017

if ( PHP_SAPI !== "cli" ) { 
	die( "norty" );
}

/**
 * Go through all the parsed_source
 * and set _oik_md5_hash for any file, class or function that's missing it
 *
 * This may or may not be faster than doing it the other way.
 * It may also give us a better way of processing in the first place
 * At least it seems a good idea to do it the OO way.
 * Though still not enough autoloading? 
 * And perhaps not enough classes for logic separation.
 * 
 */
set_md5_hash_loaded();

/**
 * set _oik_md5_hash
 */
function set_md5_hash_loaded() {
	ini_set('memory_limit','2048M');
	
  do_action( "oik_loaded" );
	oiksc_autoload();
	
	oik_require( "admin/oik-apis.php", "oik-shortcodes" );
	oik_require( "admin/oik-create-apis.php", "oik-shortcodes" );
	oik_require( "admin/oik-files.php", "oik-shortcodes" );
	oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
	oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
	oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
	oik_require( "includes/bw_posts.php" );
	oik_require( "oik-list-wordpress-files.php", "oik-batch" );
	oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );

	
	oiksc_reassign_hooks();
	
	$md5_hasher = new md5_hasher();
	$md5_hasher->process();
}

/**
 * Create MD5 hashes for every file, class and API ( function / method )
 * These are used to determine if the parsed_source needs to be rebuilt
 *
 *
 */
class md5_hasher {
	/**
	 * All the components in the site: e.g. wordpress, akismet, twentysixteen
	 */
	public $components;
	
	/**
	 * All the parsed_source files
	 */
	public $parsed_source;
	
	/**
	 * All the sources: files, classes and APIs ( functions & methods )
	 */
	public $sourcerefs;
	
	function __construct() {
		$this->components = null;
		$this->parsed_source = null;
		$this->sourcerefs = null;
	}
	
	
	function process() {
		$this->components = $this->load_components();
		$this->parsed_source = $this->load_parsed_source();
		$this->sourcerefs = $this->load_files_classes_apis();
		
		oiksc_preload_content(); 
	
		foreach ( $this->parsed_source as $post ) {
			$id = $post->ID;
			$md5_hash = get_post_meta( $id, "_oik_md5_hash", true );
			if ( $md5_hash ) {
				echo "$id:$md5_hash:OK" . PHP_EOL;
			} else {
				$sourceref = get_post_meta( $id, "_oik_sourceref", true );
				echo "$id:$md5_hash:$sourceref" . PHP_EOL;
				$this->build_hash( $post, $id, $sourceref );
			}
		}
	}	

function load_parsed_source() {
	$atts = array( "post_type" => "oik_parsed_source"
							, "numberposts" => -1 
							, "orderby" => "ID"
							, "order" => "ASC"
							);
	$posts = bw_get_posts( $atts );
	echo count( $posts ) . PHP_EOL;
	return( $posts );
}

function load_files_classes_apis() {
	$atts = array( "post_type" => "oik_file,oik_api,oik_class"
							, "numberposts" => -1 
							//, "orderby" => "ID"
							//, "order" => "ASC"
							);
	$posts = bw_get_posts( $atts );
	$keyed_posts = $this->key_posts( $posts );
	echo count( $keyed_posts ) . PHP_EOL;
	return( $keyed_posts );
}


function load_components() {
	$atts = array( "post_type" => "oik-plugins,oik-themes"
							, "numberposts" => -1 
							//, "orderby" => "ID"
							//, "order" => "ASC"
							);
	$posts = bw_get_posts( $atts );
	$keyed_posts = $this->key_posts( $posts );
	echo count( $keyed_posts ) . PHP_EOL;
	//print_r( $keyed_posts );
	return( $keyed_posts );
}

function key_posts( $posts ) {
	$key_posts = array();
	foreach ( $posts as $post ) {
		$key_posts[ $post->ID ] = $post;
	}
	return( $key_posts );
}

/**
 * Set the MD5 hash for the given file, API or class
 *
 * @param object $post
 * @param ID $id
 * @param ID $sourceref
 * @param array $sourcerefs 
 */
function build_hash( $post, $id, $sourceref ) {
	$source = bw_array_get( $this->sourcerefs, $sourceref, null );
	$source_type = $source->post_type;
	
	switch ( $source_type ) {
		case 'oik_file': 
			$this->hash_oik_file( $post, $id, $sourceref, $source );
			break;
		case 'oik_api':
			echo "building hash for API: $sourceref" . PHP_EOL;
			$this->hash_oik_api( $post, $id, $sourceref, $source );
			break;
		case 'oik_class':
			echo "building hash for class: $sourceref" . PHP_EOL;
			gob();
			break;
			
		default:
			gob();
	}
}

/**
 * Rebuild the MD5 hash for a file
 * 
 * Actually, is there any need if we can check the filemtime?
 * 
 */
function hash_oik_file( $post, $id, $sourceref, $source ) {
	$filename = get_post_meta( $sourceref, "_oik_file_name", true );
	$component_id = $this->get_component_id( $sourceref );
	$component_type = $this->get_component_type( $component_id );
	$component_slug = $this->get_component_slug( $component_id, $component_type );
	$file = oiksc_real_file( $filename, $component_type, $component_slug ); 
	echo PHP_EOL;
	echo "file: $file slug: $component_slug" . PHP_EOL;
	//$this->set_global_plugin_post( $component_id );
	_lf_dofile_local( $filename, $component_slug, $component_type );
}

/**
 * Rebuild the MD5 hash for an API
 *
 * @param object $post - the current parsed_source object
 * @param ID $id the parsed_source post ID
 * @param ID $sourceref - the original source post
 * @param 
 */
function hash_oik_api( $post, $id, $sourceref, $source ) {
	global $filename;
	$fileref = get_post_meta( $sourceref, "_oik_fileref", true );
	$filename = get_post_meta( $fileref, "_oik_file_name", true );
	$apiname = get_post_meta( $sourceref, "_oik_api_name", true );
	
	$component_id = $this->get_component_id( $sourceref );
	$component_type = $this->get_component_type( $component_id );
	$component_slug = $this->get_component_slug( $component_id, $component_type );
	
	$file = oiksc_real_file( $filename, $component_type, $component_slug ); 
	echo "file: $file API: $apiname component: $component_slug type: $component_type" . PHP_EOL;
	//$this->set_global_plugin_post( $component_id );
	//_lf_dofile_local( $filename, $component_slug, $component_type );
	$apis = _oiksc_get_apis2( $filename, true, $component_type, $component_slug );
	foreach ( $apis as $api ) {
		$current_apiname = $api->getApiName();
		if ( $current_apiname === $apiname ) {
			
			oiksc_local_oiksc_create_api( $component_slug, $filename, $component_type, $api );
			$discard = bw_ret();
		}
	}
}


/**
 * Set the global plugin_post
 */
function set_global_plugin_post( $component_id ) {
  global $plugin_post;
	$post = bw_array_get( $this->components, $component_id, null );
	if ( $post ) {
		$plugin_post = $post;
	} else {
		gob();
	}
}

function get_component_id( $sourceref ) {
	$component_id = get_post_meta( $sourceref, "_oik_api_plugin", true );
	return( $component_id );
}

/**
 * Find the component slug
 * 
 * @TODO Remove need for global $plugin
 */
function get_component_slug( $component_id , $component_type ) {
	if ( $component_type == "plugin" ) { 
		$component_slug = get_post_meta( $component_id, "_oikp_slug", true );
	} else {
		$component_slug = get_post_meta( $component_id, "_oikth_slug", true );
	}
	global $plugin;
	$plugin = $component_slug;
	return( $component_slug );
}

/**
 * Returns the component type 
 *
 * @param ID $component_id
 */

function get_component_type( $component_id ) {
	$post= bw_array_get( $this->components, $component_id, null );
	
	if ( $post ) {
		global $plugin_post;
		$plugin_post = $post;
		switch ( $post->post_type ) {
			case "oik-plugins":
				$component_type = "plugin";
				break;
			case "oik-themes":
				$component_type = "theme";
				break;
			default:
				gob();
				break;
		}
	} else {
		gob();
	}
	

	return( $component_type );
}

}
