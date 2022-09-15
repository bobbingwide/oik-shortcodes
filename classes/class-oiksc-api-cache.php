<?php // (C) Copyright Bobbing Wide 2016

/**
 * Cache for oik_api records
 *
 * and perhaps more? 
 * Or would these be other classes?
 * we'll find out
 *
 *
 * Perhaps performance can be improved by following some of the stuff here
 * {@link https://www.percona.com/blog/2014/01/28/10-mysql-performance-tuning-settings-after-installation/ }
 * 
 */
class oiksc_api_cache {

	/**
	 * Array of posts... do we really need this?
	 */
	public $posts;
	
	/**
	 * Mapping of meta_key and meta_values back to the post ID
	 *
	 * $meta_values[ $meta_key ][ $meta_value ] = array( $post_id )
	 * 
	 */ 
	public $meta_values;
	
	/**
	 * Primary key to the meta_values array
	 */
	public $meta_key;
	
	/**
	 * Secondary key to the meta_values array
	 */
	public $meta_value;
	
	/**
	 * @TODO If we need to get posts ?
	 */ 
	public $post_type = "oik_api";
	
	/**
	 * @var $instance - the true instance
	 */
	private static $instance;
	
	/**
	 * Return a single instance of this class
	 *
	 * @return object 
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof self ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Constructor for oiksc_api_cache
	 *
	 */
	function __construct() {
		$this->posts = array();
		$this->meta_values = array();
		$this->meta_key = null;
		$this->meta_value = null;
		$this->post_type = "oik_api";
	}
	
	/**
	 * Load the selected posts
	 * and the associated posts from the meta data
	 *
	 *
	 *  $post_id, "_oik_api_calls", $oikai_callee 
	 
	 * _oik_api_name - the API name for the given post
	 *
	 * _oik_api_calls - one or more oik_api IDs
	 * _oik_api_plugin - one or more oik-plugins IDs
	 * _oik_api_fileref - one or more oik_file IDs
	 * _oik_api_associations - mapping of hooks and stuff
	 * 
	 */
	function load_posts( $function ) {
		$posts = $this->get_oik_api_byname( $function );
		//$this->map_posts( $posts, true );
		bw_trace2( $posts, "posts", true, BW_TRACE_VERBOSE );
        if ( $posts && count( $posts )) {
            $this->preload_api_calls($posts[0]);
        }
	}
	
 
  /** 
	 * Retrieve the oik_api post by API name
	 *
	 * @param string $function API name as function or class::method
	 * @return array of post IDs - hopefully just the one
	 *
	 */
	function get_oik_api_byname( $function ) {
		//	bw_trace2();
		//bw_backtrace();
		$this->post_type = "oik_api"; 
		$this->meta_key = "_oik_api_name";
		$this->meta_value = $function;
	 	$posts = $this->get_cached(); 
		if ( !$posts ) {
			oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
			// This only returns one post object, or null
			$post = oiksc_get_oik_api_byname( $function );
			//$this->map_posts( array( $post->ID ) );
			if ( $post ) {
				$this->map( $post->ID );
				$posts = array( $post->ID );
			}	
		}
		return( $posts );
	}
	
	/**
	 * Return the cached post IDs for this API
	 *
	 * @return array of post IDs 
	 */
	function get_cached() {
		bw_trace2( $this->meta_values, "meta_values", false, BW_TRACE_VERBOSE );
		//bw_backtrace();
		if ( isset( $this->meta_values[ $this->meta_key][ $this->meta_value ] ) ) {
			$posts = $this->meta_values[ $this->meta_key][ $this->meta_value ];
			
		} else {
			$posts = null;
			
		}
		bw_trace2( $posts, $this->meta_value, false, BW_TRACE_DEBUG );
		return( $posts );
	}
	
	/**
	 * Map an array of posts into the meta_values
	 * 
	 * @param array $post_ids
	 * @param bool $preload 
	 */
	function map_posts( $post_ids, $preload=false ) {
		bw_trace2( null, null, true, BW_TRACE_VERBOSE );
		if ( count( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				//echo "Mapping $post_id" . PHP_EOL;
				$this->meta_key = "_oik_api_name";
				$this->meta_value = get_post_meta( $post_id, $this->meta_key , true );
				bw_trace2( $this->meta_value, "meta_value", false, BW_TRACE_VERBOSE );
				$this->map( $post_id );
				if ( $preload ) { 
					$this->preload_api_calls( $post_id );
				}
			}
		}	
	}
	
	/**
	 * Preload the callees for this post
	 *
	 * @param ID $post_id
	 */
	function preload_api_calls( $post_id ) {
		$post_ids = get_post_meta( $post_id, "_oik_api_calls", false );
		bw_trace2( $post_ids, "_oik_api_calls", true, BW_TRACE_DEBUG );
		if ( $post_ids ) {
			$this->map_posts( $post_ids );
		}
	}
	
	/**
	 * Map a post
	 * 
	 * Note: $this meta_key and meta_value must have already been set
	 * 
	 * @param ID $post_id the post to be mapped
	 */
	function map( $post_id ) {
		$this->meta_values[ $this->meta_key ][ $this->meta_value ] = array( $post_id );
	} 
	
	function report() {
		print_r( $this->meta_values );
		bw_trace2( $this->meta_values, "meta_values", false, BW_TRACE_DEBUG );
	}
	
	/**
	 * Preload all _oik_api_name values 
	 *
	 * meta_key      | meta_values
	 * ------------- | ---------------
	 * _oik_api_name | class::method / function
	 * 
	 *  
	 */
	function preload_all_apis() {
		$this->fetch_all( "_oik_api_name" );		
	
	}	
	
	/**
	 * fetch all posts for the given meta_key
	 * 
	 	`
    [0] => stdClass Object
        (
            [post_id] => 4958
            [meta_value] => bw_oik_long
        )
		`		
	 */ 																
	function fetch_all( $meta_key ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '%s' ", $meta_key );
		$results = $wpdb->get_results( $sql );
 
		//bw_trace2( $results, "results" );
		$this->meta_key = $meta_key;
		foreach ( $results as $key => $result ) {
			$this->meta_value = $result->meta_value;
			$this->map( $result->post_id );
		}
		//$this->report();
		//gob();
	}
		

}

