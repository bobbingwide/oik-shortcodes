<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2019
 * @package oik-shortcodes

 */

namespace OIK\oik_shortcodes;


class oiksc_wordpress_cache {

	private $cache; // array of cached APIs, files, classes and hooks
	private $filename;
	private $results; // results of the SQL query

	function __construct() {
		$this->filename = 'oiksc-wordpress-cache.json';
		$this->cache = [];
	}

	/**
	 * Loads the cache from the WordPress database
	 *
	 * We need to find the WordPress component to be able to load
	 * all the posts for the CPT's: oik_api, oik_class, oik_file, oik_hook
	 */
	function load_cache_from_db() {
		$this->load_all_CPT( 'oik_api', '_oik_api_name' );
		$this->add_results_to_cache();
		$this->load_all_CPT( 'oik_class', '_oik_class_name' );
		$this->add_results_to_cache();
		$this->load_all_CPT( 'oik_file', '_oik_file_name' );
		$this->add_results_to_cache();
		$this->load_all_CPT( 'oik_hook', '_oik_hook_name' );
		$this->add_results_to_cache();
	}

	/**
	 * @param $post_type
	 * @param null $meta_key
	 *
	 * @return int|void
	 */
	function load_all_CPT( $post_type, $meta_key=null ) {

		global $wpdb;
		//$query = "select ID, post_title from $wpdb->posts where post_type = '%s' order by post_title ASC";
		$query = "SELECT ID, post_type, post_name, meta_value, post_title FROM $wpdb->posts, $wpdb->postmeta 
		WHERE ID = post_id and post_type = '%s' and meta_key = '%s' order by post_name, meta_value";
		$request = $wpdb->prepare( $query, $post_type, $meta_key );
		//echo $request;
		//echo PHP_EOL;
		$this->results = $wpdb->get_results( $request );
		//print_r( $results );
		return count( $this->results );


	}

	/**
	 * Add the results to the cache
	 */
	function add_results_to_cache() {
		foreach ( $this->results as $result ) {
			$this->add_to_cache( $result );
		}
	}

	/**
	 * Adds the result to the cache
	 *
	 * We need the cache to be able to create links to core.wp-a2z.org or developer.wordpress.org
	 * Not sure how we'll deal with dynamically linking to one or the other unless we filter the parsed source.
	 * Looks like we need the post_type as part of the result
	 *
	 *
	 * https://core.wp.a2z/           | https://developer.wordpress.org/reference/
	 * ------------------------------ | ---------------------------------
	 * oik_api/api_name               | functions/api_name
	 *
	 * oik_class/class_name           | classes/class_name
	 *                                | classes/class_name/method_name
	 * oik_file/wp-admin/about-php    |
	 * oik_hook/hook_name             | hooks/hook_name
	 *
	 *
	*/
	function add_to_cache( $result ) {
		$this->cache[] = $result;
	}

	function query_cache_count() {
		return count( $this->cache );
	}

	/**
	 * Saves the cache to a csv file - oiksc-wordpress-cache.csv
	 *
	 */
	function save_cache() {
		echo "Saving cache to: " . $this->filename;
		echo PHP_EOL;
		echo $this->query_cache_count();
		echo PHP_EOL;
		/*
		$output = [];
		foreach ( $this->cache as $result ) {
			print_r( $result );
			$output[] = implode( ',', $result);
			print_r( $output );
			gob();
		}
		*/
		$output = json_encode( $this->cache );
		file_put_contents( $this->filename, $output );
	}

	/**
	 * Loads the cache from the csv file - oiksc-wordpress-cache.csv
	 */
	function load_cache() {
		$cache = file_get_contents( $this->filename );
		$this->cache = json_decode( $cache );
	}

}