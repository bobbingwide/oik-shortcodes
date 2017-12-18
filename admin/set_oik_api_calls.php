<?php // (C) Copyright Bobbing Wide 2016,2017

/**
 * Syntax: oikwp set_oik_api_calls.php url=domain
 *
 * from the oik-shortcodes/admin directory
 * 
 * It's not a good idea to store _oik_api_calls as the API name ( function or method ) 
 * since this makes it harder to produce the list of "Calls" for a File or API
 *
 * So we need to convert any post meta stored as a string to the post ID
 * We should also attempt to take into account the component and match the post_ID where possible.
 *
 */

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
set_oik_api_calls();

/**
 * set _oik_md5_hash
 */
function set_oik_api_calls() {
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
	
	$api_call_mapper = new api_call_mapper();
	$api_call_mapper->process();
}

class api_call_mapper {

	public $api_calls;
	public $api_names;
	public $api_map;
	
	function __construct() {
		$this->api_calls = null;
		$this->api_names = null;
		$this->api_map = null;
	}
	
	function process() {
		$meta_values = $this->load_all_post_meta();
		echo count( $meta_values['_oik_api_calls'] );
		echo count( $meta_values['_oik_api_name'] );
		$this->api_calls = $meta_values['_oik_api_calls'];
		$this->api_names = $meta_values['_oik_api_name' ];
		//$this->simplified_apis = $this->simplify_results( $this->api_names );
		$this->create_map();
		$this->convert_all_api_calls();
	}

	function load_all_post_meta() {
		$meta_keys = array( "_oik_api_calls", "_oik_api_name" );
		$meta_values = array();
		foreach( $meta_keys as $meta_key ) { 
			$meta_values[ $meta_key ] = $this->load_post_meta( $meta_key );
		}
		return( $meta_values );
	}

	function load_post_meta( $meta_key="_oik_api_calls" ) {
		global $wpdb;
		$request = $wpdb->prepare( "select meta_id, post_id, meta_value from $wpdb->postmeta where meta_key = '%s' order by post_id ASC", $meta_key );
		echo $request;
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
	
	function convert_all_api_calls(  ) {
		$total = count( $this->api_calls );
		$count = 0;
		foreach ( $this->api_calls as $meta_data ) {
			$count++;
			echo "$count/$total ";
			$meta_value = $meta_data->meta_value;
			if ( is_numeric( $meta_value ) ) {
				// That's great
				echo "OK" . PHP_EOL; 
			} elseif ( $meta_value == "__construct" ) {
				echo "Ho hum $meta_value" . PHP_EOL;
				
			}	else {
				echo "Need to convert $meta_value" . PHP_EOL;
				$this->convert( $meta_data );
			}
		}
	}
	/**
	 * Create the map from api_name to post_id
	 * 
	 * There may be more than one API with the same name, implemented in different components
	 */
	function create_map() {
		$this->api_map = array();
		foreach ( $this->api_names as $meta_data ) {
			$this->api_map[ $meta_data->meta_value] = $meta_data->post_id;
		}
	}
	
	function convert( $meta_data ) {
		$meta_value = $meta_data->meta_value;
		$mapped = $this->map( $meta_value );
		if ( $mapped != $meta_value ) {
			echo "Update $meta_value to $mapped" . PHP_EOL;
			$this->update( $meta_data, $mapped );
		} else {
			echo "No map found for $meta_value" . PHP_EOL;
		}
	}
		
	function map( $meta_value ) {
		$mapped = bw_array_get( $this->api_map, $meta_value, $meta_value );
		return( $mapped );
	} 
	
	function update( $meta_data, $mapped ) {
		global $wpdb;
		$query = $wpdb->prepare( "update $wpdb->postmeta set meta_value = %s where meta_id = %s and post_id = %s and meta_key = '_oik_api_calls' and meta_value = '%s'"
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
		
		
	


}

