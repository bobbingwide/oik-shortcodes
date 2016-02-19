<?php // (C) Copyright Bobbing Wide 2016 

/**
 * Even more efficient handling of permalinks and titles
 * 
 * get_permalink() can be quite an overhead (*) 
 * and we don't want the http://example.com bit... just the bottom half of the permalink 
 * 
 * (*) probably a bit of an overstatement
 * 
 * 
 */
 
 
/**
 * Cached mapping of API names to post IDs
 */
class oiksc_link_map {


	public $home_url = null;
	
	public $post_ids = array();
	
	public $titles = array();
	
	
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
	 * Constructor for oiksc_link_map 
	 */
	function __construct() {
		$home_url = home_url();
		$url = parse_url( $home_url );
		bw_trace2( $url, "URL" );
		$this->home_url = $url['scheme'] . '://' . $url['host'];
		$this->post_ids = array();
	}
	
	function get_permalink( $post_id=null ) {
		if ( !isset( $this->post_ids[ $post_id ] ) ) {
			$permalink = get_permalink( $post_id ); 
			$permalink = str_replace( $this->home_url, "", $permalink );
			$this->post_ids[ $post_id ] = $permalink;
			bw_trace2( $permalink, "permalink", true, BW_TRACE_VERBOSE ); 
		}
		return( $this->post_ids[ $post_id ] );
	}
	
}



	
