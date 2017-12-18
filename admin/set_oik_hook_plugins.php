<?php // (C) Copyright Bobbing Wide 2017

/**
 * Syntax: oikwp set_oik_hook_plugins.php url=domain
 *
 * from the oik-shortcodes/admin directory
 *
 * 
 *
 */

if ( PHP_SAPI !== "cli" ) { 
	die( "norty" );
}

/**
  
 *
 * 
 */
set_oik_hook_plugins();

/**
 * Sets _oik_hook_plugin where required
 * 
 * Goes through all the oik_hooks and sets _oik_hook_plugin where required
 * 
 * - We need to correct the value of _oik_hook_plugin for each oik_hook post.
 * - We can get the value of _oik_fileref and use that to look up _oik_api_plugin
 * 
 * @TODO This fails to update hooks where _oik_hook_plugin post meta does not exist.
 * 
 * 
 * e.g. 
 * post_type | post_id | _oik_fileref | _oik_api_plugin | _oik_hook_plugin
 * --------- | ------- | ------------ | --------------- | ----------------
 * oik_hook  | 18      | 3342         | n/a             | 4 - when corrected 
 * oik_file  | 3342    | 3307 (parent)| 4               | n/a
 *
  
 * e.g. 
 * post_type | post_id | _oik_fileref  | _oik_api_plugin | _oik_hook_plugin
 * --------- | ------- | ------------- | --------------- | ----------------
 * oik_hook  | 9451    | 21829         | n/a             | 2384 - when corrected 
 * oik_file  | 21829   | 21794 (parent)| 2384            | n/a
 */
function set_oik_hook_plugins() {
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
	
	$oik_hook_plugins = new oik_hook_plugins();
	$oik_hook_plugins->process();
}

class oik_hook_plugins {

	public $hook_names;
	public $hook_plugins;
	public $filerefs;
	public $api_plugins;
	
	public $hook_plugin;
	public $fileref; 
	public $api_plugin;
	
	function __construct() {
	}
	
	function process() {
		
		$meta_values = $this->load_all_post_meta();
		echo count( $meta_values['_oik_hook_name'] );
		echo PHP_EOL;
		echo count( $meta_values['_oik_hook_plugin'] );
		echo PHP_EOL;
		echo count( $meta_values['_oik_fileref'] ) ;
		echo PHP_EOL;
		echo count( $meta_values['_oik_api_plugin'] );
		echo PHP_EOL;
		$this->hook_names = $meta_values['_oik_hook_name'];
		$this->hook_plugins = $this->simplify_results( $meta_values['_oik_hook_plugin'] );
		$this->filerefs = $this->simplify_results( $meta_values['_oik_fileref' ] );
		$this->api_plugins = $this->simplify_results( $meta_values['_oik_api_plugin'] );
		//$this->create_map();
		$this->convert_all_hook_plugins();
	}
	
	/** 
	 * Load all the post meta data
	 * 
	 * Note: We don't care what post types the data is associated to.
	 * We asssume single or not?
	 *
	 */
	function load_all_post_meta() {
		$meta_keys = array( "_oik_hook_name", "_oik_hook_plugin", "_oik_fileref", "_oik_api_plugin" );
		$meta_values = array();
		foreach( $meta_keys as $meta_key ) { 
			$meta_values[ $meta_key ] = $this->load_post_meta( $meta_key );
		}
		return( $meta_values );
	}

	function load_post_meta( $meta_key="_oik_hook_name" ) {
		global $wpdb;
		$request = $wpdb->prepare( "select meta_id, post_id, meta_value from $wpdb->postmeta where meta_key = '%s' order by post_id ASC", $meta_key );
		echo $request;
		echo PHP_EOL;
		$results = $wpdb->get_results( $request );
		//print_r( $results );
		//$reduced = $this->simplify_results( $results );
		return( $results );
	}

	/**
	 * We can't use simplify results when there's more than one meta_value per post
	 */
	function simplify_results( $results ) {
		$reduced = array();	
		if ( count( $results ) ) {
			foreach ( $results as $result ) {
				$post_id = $result->post_id;
				$meta_value = $result->meta_value;
				$reduced[ $post_id ] = $meta_value;
			}
		}
		echo count( $reduced );
		echo PHP_EOL;	
		return( $reduced );
	}
	
	function convert_all_hook_plugins() {
		$total = count( $this->hook_names );
		$count = 0;
		$failed = 0;
		$updated = 0;
		$ok = 0;
		foreach ( $this->hook_names as $meta_data ) {
			$count++;
			echo "$count/$total/$failed/$updated/$ok ";
			echo "ID: " . $meta_data->post_id ; 
			echo " hook: " . $meta_data->meta_value;
			$hook_plugin = $this->get_hook_plugin( $meta_data->post_id );
			$api_plugin = $this->get_api_plugin( $meta_data->post_id );
			if ( null === $api_plugin ) {
				$failed++;
			} elseif ( $api_plugin != $hook_plugin ) {
				$this->update_post_meta( $meta_data->post_id, $api_plugin );
				$updated++;
			} else {
				$ok++;
			}
		}
		
		echo "$count/$total/$failed/$updated/$ok ";
	}
	
	function get_hook_plugin( $post_id ) {
		$this->hook_plugin = bw_array_get( $this->hook_plugins, $post_id, null );
		echo " Hook plugin: " .  $this->hook_plugin;
		return $this->hook_plugin;
	}
	
	function get_fileref( $post_id ) {
		$this->fileref = bw_array_get( $this->filerefs, $post_id, null );
    echo " Fileref: ";
		echo $this->fileref;
		//echo PHP_EOL;
		return $this->fileref;	
																		
	}
	
	function get_api_plugin( $post_id ) {
		$fileref = $this->get_fileref( $post_id );
		if ( $fileref ) {
			$this->api_plugin = bw_array_get( $this->api_plugins, $fileref, null );
			echo " API plugin: ";
			echo $this->api_plugin;
		}	else {
			$this->api_plugin = null;
			echo " Can't determine new value." ;
		}
		echo PHP_EOL;
		return $this->api_plugin;
	}
		
	
			
		
	function map( $meta_value ) {
		$mapped = bw_array_get( $this->api_map, $meta_value, $meta_value );
		return( $mapped );
	} 
	
	function update( $meta_data, $mapped ) {
		global $wpdb;
		$query = $wpdb->prepare( "update $wpdb->postmeta set meta_value = %s where meta_id = %s and post_id = %s and meta_key = '_oik_hook_plugin' and meta_value = '%s'"
														, $mapped
														, $meta_data->meta_id
														, $meta_data->post_id
	  												, $meta_data->meta_value
														); 
		echo $query;
		$wpdb->query( $query );
		
		//$where = array( "meta_id" => $meta_data->meta_id
		//							, "post_id" => $meta_data->post_id
		//							, "meta_key" => "_oik_api_calls"
		//							);
		//$wpdb->update( "postmeta", array( "meta_value" => $mapped ), $where );
		
		//gob();													
		
	}
	
	function update_post_meta( $post_id, $meta_value ) {
		echo " updating... " . PHP_EOL;
		update_post_meta( $post_id, "_oik_hook_plugin", $meta_value ); 
	}
		
		
	


}

