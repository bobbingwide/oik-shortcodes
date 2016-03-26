<?php // (C) Copyright Bobbing Wide 2016
/**
 * 404 handler for oik-shortcodes
 *
 * Displays some useful information on the 404 page when a link wasn't quite right.
 * This is primarily to cater for pragmatic links where we expect the post_type to be correct
 * but the item may not actually be a known API, file or hook
 * 
 * In a WPMS environment we have a look to see if the master blog has the content
 * and then try to display that information.
 *
 * We'll perform a search and list the items that may match.
 * 
 */
class oiksc_404_handler {

	public $query;
	public $wpdb;
	
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
	 * Constructor for 404 handler
	 */
	function __construct() {
		global $wpdb;
		global $wp_query;
		$this->query = $wp_query;
		$this->wpdb = $wpdb; 
		bw_trace2( $this->query, "query" ); 
	}
	
	/**
	 * Return the entry title
	 */
	function entry_title( $text ) {
		$post_type = bw_array_get( $this->query->query, "post_type", null );
		$post_type_object = get_post_type_object( $post_type );
		if ( $post_type_object ) {
			$text = $post_type_object->labels->singular_name;
		}
		$text .= " not found: ";
		$text .= bw_array_get( $this->query->query, "name", null );
		//print_r( $post_type_object );
		return( $text );
	}

	/**
	 * Filter the 404 text
	 * 
	 * @param string $text initial HTML for not found
	 * @return string generated HTML
	 *
	 */
	function handle_404( $text ) {
		$text = "All in good time";
		//print_r( $this->query );
		$post_type = $this->process_post_type();
		if ( $post_type ) {
			//$text = "Handling: $post_type";
			$text = $this->handle_post_type( $text, $post_type );
		} else {
			$text = "Not quite sure what you're looking for here. Try searching";
		} 
		return( $text );
	}
	
	/**
	 * Return the post_type if we want to handle this post_type
	 *
	 * Also check it's a valid post type object that's publicly accessible.
	 * 
	 * @return 
	 */
  function process_post_type() {
		$handles = bw_assoc( bw_as_array( "oik_api,oik_file,oik_class,oik-plugins,oik-theme,oik_hook" ) );
		$post_type = bw_array_get( $this->query->query, "post_type", null );
		$post_type = bw_array_get( $handles, $post_type, null );
		
		$post_type_object = get_post_type_object( $post_type );
		if ( !$post_type_object ) {
			$post_type = null;
		}
		return( $post_type );
	}
	
	/**
	 * Should this be implemented with one class per post type?
	 * with each subclass having its own handle method?
	 *
	 */
	function handle_post_type( $text, $post_type ) {
		$name = bw_array_get( $this->query->query, "name", null );
		$name = substr( $name, 0, 1 );
		$like = $name;
		oik_require( "shortcodes/oik-navi.php" );
		oik_require( "includes/bw_posts.inc" );
		$atts = array( "post_type" => $post_type 
								, "orderby" => 'title'
								, "numberposts" => 10
								, "meta_key" => "_oikp_slug"
								, "meta_value" => $like
								, "meta_compare" => "LIKE" 
								);
		$posts = bw_get_posts( $atts );
		if ( $posts ) {
			$text = "We couldn't find exactly what you were looking for. Here are some similar." ;
			$text .= bw_navi( $atts ); 
    } else { 
			$text = "We couldn't find exactly what you were looking for. Perhaps it's in this list." ;
			unset( $atts['meta_key'] );
			$text .= bw_navi( $atts ); 
								
		}
								
		return( $text );
	}
	


}
