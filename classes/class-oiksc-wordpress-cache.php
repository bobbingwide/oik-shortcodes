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

	private $wordpress_root = 'https://core.wp-a2z.org/';


	function __construct() {
		$this->filename = 'oiksc-wordpress-cache.json';
		$this->cache = [];
		$this->maybe_set_wordpress_root();
	}

	/**
	 * Sets the wordpress root to e.g. https://core.wp.a2z/
	 * @param $wordpress_root
	 */
	function set_wordpress_root( $wordpress_root=null ) {
		if ( null !== $wordpress_root ) {
			$this->wordpress_root = $wordpress_root;
		}
		return $this->wordpress_root;
	}

	function maybe_set_wordpress_root() {
		$server = bw_array_get( $_SERVER, 'SERVER_NAME', null );
		if ( false !== strpos($server, 'wp.a2z' ) ) {
			$this->set_wordpress_root( 'https://core.wp.a2z/' );
		}
	}

	function full_filename() {
		$full_filename = oik_path( $this->filename, 'oik-shortcodes');
		return $full_filename;
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
		$cache_key = $this->get_cache_key( $result );
		if ( $this->is_wordpress_api( $cache_key )) {
			echo "Cache key for result is not unique";
			print_r( $result );
			gob();
		}
		//echo $cache_key;
		$this->cache[ $cache_key ] = $result;
	}

	/**
	 * Gets the cache key for the result
	 *
	 * We hope this is going to be unique.
	 * Easy to check I suppose by logic in add_to_cache.
	 * OK. that was done _get_page_link is both an API and a hook
	 * so we have to be able to differentiate between these.
	 * For the time being we'll use a prefix of `hook-` for hooks.
	 *
	 * What about functions and classes and methods within classes.
	 * Are PHP class names case sensitive?

	 *
	 * @param $result	 *
	 * @return mixed
	 */

	function get_cache_key( $result ) {
		if ( 'oik_hook' === $result->post_type ) {
			$cache_key = 'hook-' . $result->meta_value;
			return $cache_key;
		}
		return $result->meta_value;
	}

	function is_wordpress_api( $key ) {
		$result = bw_array_get( $this->cache, $key, null );
		if ( null === $result ) {
			return false;
		}
		return true;
	}

	function query_api_type( $key ) {
		$result = bw_array_get( $this->cache, $key, null );
		if ( null === $result ) {
			$result = bw_array_get( $this->cache, 'hook-' . $key, null );
		}
		if ( null === $result ) {
			return null;
		}
		return $result->post_type;
	}

	function get_wordpress_link( $key ) {
		$result = bw_array_get( $this->cache, $key, null );
		if ( null === $result ) {
			$link = $key;

		} else {
			$link = $this->get_link_from_result( $result );
		}
		return $link;
	}

	function get_link_from_result( $result ) {
		$url = $this->wordpress_root; // should have a slash at the end
		$url .= $result->post_type;
		$url .= '/';
		$url .= $result->post_name;
		$link = retlink( $result->post_type, $url, $result->meta_value, $result->post_title );
		return $link;

	}

	function query_cache_count() {
		return count( $this->cache );
	}

	/**
	 * Saves the cache to a .json file - oiksc-wordpress-cache.json
	 *
	 */
	function save_cache( $echo=false) {
		if ( $echo ) {
			echo "Saving cache to: " . $this->filename;
			echo PHP_EOL;
			echo $this->query_cache_count();
			echo PHP_EOL;
		}
		$cache = array_values( $this->cache );
		$output = json_encode( $cache );
		file_put_contents( $this->full_filename(), $output );
	}

	/**
	 * Loads the cache from the csv file - oiksc-wordpress-cache.csv
	 */
	function load_cache() {
		$cache = file_get_contents( $this->full_filename() );
		$cache_array = json_decode( $cache );
		$this->results = $cache_array;
		$this->add_results_to_cache();
	}

	/**
	 * Redirects to https://core.wp-a2z.org if this is a core API, class, file or hook.
	 *
	 * We already know the current site is not core.wp-a2z.org and that the post_type is
	 * one of: oik_api, oik_class, oik_hook or oik_file
	 *
	 * @param array $request the parse reques to WordPress
	 * @return mixed the original $request
	 */
	function maybe_redirect( $request ) {
		$name = bw_array_get( $request, 'name', null );
		$funcname = $name;
		$post_type = bw_array_get( $request, 'post_type', null );
		if ( $post_type === 'oik_file') {
			$funcname = str_replace( '-php', '.php', $name );
		}

		if ( $funcname ) {
			$api_type = $this->query_api_type( $funcname );
			if ( $api_type ) {
				$url  = $this->wordpress_root;
				$url .= "/$api_type/$name";
				bw_trace2( $url, 'Redirect:', true, BW_TRACE_DEBUG );
				wp_redirect( $url, 301, 'WP-a2z' );
				exit();
			}
		}

		return $request;
	}

}