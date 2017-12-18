<?php // (C) Copyright Bobbing Wide 2016,2017
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

	public $wpdb;
	public $query;
	public $post_type_object;
	
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
	 * 
	 */
	function __construct() {
		global $wpdb;
		global $wp_query;
		$this->query = $wp_query;
		$this->wpdb = $wpdb; 
		//bw_trace2( $this->query, "query" ); 
	}
	
	
	/**
	 * Find the post_type_object
	 * 
	 * @return object post type object or null 
	 */
	function get_post_type_object() {
		$this->post_type_object = null;
		$post_type = bw_array_get( $this->query->query, "post_type", null );
		if ( $post_type ) {
			$this->post_type_object = get_post_type_object( $post_type );
		}
		bw_trace2( $this->post_type_object, "post_type_object", false, BW_TRACE_DEBUG );
		return( $this->post_type_object );
	}
	
	/**
	 * Return the entry title
	 *
	 * @param string $text 
	 * @return updated entry title 
	 */
	function entry_title( $text ) {
		if ( $this->get_post_type_object() ) {
			$text = $this->post_type_object->labels->not_found;
			$text .= ": ";
			$text .= esc_html( $this->get_name() );
		}
		return( $text );
	}
	
	/**
	 * Get the name specified
	 *
	 * @return string $name
	 */
	function get_name() { 
		$name = bw_array_get( $this->query->query, "name", null );
		if ( $name ) {
			$name = urldecode( $name );
		}
		return( $name );
	}
 

	/**
	 * Filter the 404 text
	 * 
	 * @param string $text initial HTML for not found
	 * @return string generated HTML
	 *
	 */
	function handle_404( $text ) {
		gob(); // should be redundant now
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
	 * Return the handler for each post type
	 *
	 * This is only invoked for valid active post types
	 * 
	 * We only create handlers for public post types
	 * 
	 * @return array mapping of post type to implementing method
	 */
	function handlers() {
		$this->handlers = array( "oik_api" => "handle_oik_api" 
										 , "oik_file" => "handle_oik_file"
										 , "oik_class" => "handle_oik_class"
										 , "oik_hook" => "handle_oik_hook" 
										 //, "oik-plugins => "handle_oik_plugins"
										 //  "oik-pluginversion
										 //  oik-premiumversion
										 // oik-themes
										 // oik-themeversion
										 // oik-themiumversion
										 );
		return( $this->handlers );
	}									
	
	/**
	 * Attach the handler for this post_type
	 *
	 * If it's a valid post type object
	 * then find the handler and attach our filter functions
	 */
  function attach_post_type_handler() {
		if ( $this->get_post_type_object() ) {
			$handlers = $this->handlers(); 
			$handler = bw_array_get( $handlers, $this->post_type_object->name, null );
			if ( $handler ) {
				if ( method_exists( $this, $handler ) ) {
					add_filter( "genesis_404_entry_content", array( $this, $handler ) );
				} else {
					bw_trace2( $handler, "Using default handler method", false, BW_TRACE_ERROR );
					add_filter( "genesis_404_entry_content", array( $this, "handle_post_type" ) );
				}
					
				add_filter( "genesis_404_entry_title", array( $this, "entry_title" ) );
			} else {
				// No handler method for this post_type_object
			}
		} 
	}
	
	/**
	 * Generic post type 404 handler
	 * 
	 * @param string $text
	 * @return string 
	 */
	function handle_post_type( $text ) {
		$name = bw_array_get( $this->query->query, "name", null );
		$name = substr( $name, 0, 1 );
		$like = $name;
		oik_require( "shortcodes/oik-navi.php" );
		oik_require( "includes/bw_posts.php" );
		$atts = array( "post_type" => $this->post_type_object->name 
								, "orderby" => 'title'
								, "numberposts" => 10
								, "name" => $name
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
	
	/**
	 * oik_api 404 handler
	 * 
	 * 
	 */
	function handle_oik_api( $text ) {
		$atts = array( "post_type" => "oik_api" 
								, "orderby" => 'title'
								, "numberposts" => 10
								, "meta_key" => "_oik_api_name"
								, "meta_compare" => "LIKE" 
								//, "meta_value" => $this->another_like() 
								);
		$atts = $this->another_like_api( $atts );
		$text = $this->perform_queries( $atts );						
		return( $text );
	}
	
	/**
	 * oik-plugins 404 handler
	 * 
	 *
	 */
	function handle_oik_plugins( $text ) {
		$like = $this->another_like();
		$atts = array( "post_type" => "oik-plugins" 
								, "orderby" => 'title'
								, "numberposts" => 10
								, "meta_key" => "_oikp_slug"
								, "meta_value" => $like
								, "meta_compare" => "LIKE" 
								);
		$text = $this->perform_queries( $atts );						
		return( $text );
	}
	
	/** 
	 * Find potential matches to the query
	 *  
	 * See if we can improve on the query to find a potential match
	 *
	 * WordPress has already run some queries to find a match e.g.
	 *
	 * ` 
	 * [bw_sql 12 0.001407 wp;WP::main;WP::query_posts;WP_Query::query;WP_Query::get_posts]
	 *
	 * SELECT   wp_posts.* FROM wp_posts  WHERE 1=1  AND wp_posts.post_name = 'bbb_oik' 
   *	AND wp_posts.post_type = 'oik-plugins'  
	 * ORDER BY wp_posts.post_date DESC [/bw_sql]
	 *
	 * [bw_sql 13 0.004388 wp_old_slug_redirect]
	 *
	 * SELECT post_id FROM wp_postmeta&comma;  wp_posts WHERE ID = post_id 
	 * AND post_type = 'oik-plugins' AND meta_key = '_wp_old_slug' AND meta_value = 'bbb_oik'[/bw_sql]
	 *
	 * [bw_sql 14 0.001277 redirect_canonical;redirect_guess_404_permalink]
	 *
	 * SELECT ID FROM wp_posts WHERE post_name LIKE 'bbb\\_oik%' 
	 * AND post_type = 'oik-plugins' AND post_status = 'publish'[/bw_sql]
	 * 
	 * Perhaps we should choose just one of the words
	 * or the first character
	 *
	 * We don't want to waste too long as this might be a robot messing about.
	 * 
	 * Google would do a lookup of the common mistakes and find it that way
	 * We could use omniquery or some other search 
	 * 
	 */
	function another_like() {
		$name = $this->get_name();
		$name = substr( $name, 0, 1 );
		$like = $name;
		return( $like );
	}
	
	/**
	 * Alternative 'like' API - tbc
	 * 
	 * $name         | e.g.            | Processing 
	 * -------       | --------------- | ----------
	 * x%nny         | Happy%20Easter  | Happy
	 * class::method | WP_query::posts | List all the methods for the class
	 * class::       | WP_Query::      |  
	 */
	function another_like_api( $atts=null ) {
	
		oik_require( "shortcodes/oik-api.php", "oik-shortcodes" );
		oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
	
		$name = $this->get_name();
		$type = oikai_determine_reference_type( $name );
		switch ( $type ) {
			case 'class':
				$atts['post_type'] = "oik_class";
				$atts['meta_key'] = "_oik_class_name";
				$atts['meta_value'] = $this->another_like();
				break;
				
			case 'method':
				break;
			
			case 'function':
				break;
			
			case 'internal':
				break;
			
			default:
				break;
			
		}
		return( $atts );
	}

	/**
	 * Run the queries and display results
	 *
	 * @param array $atts
	 * @return string Generated HTML
	 */
	function perform_queries( $atts ) {
		oik_require( "includes/bw_posts.php" );
		oik_require( "shortcodes/oik-navi.php" );
		$posts = bw_get_posts( $atts );
		if ( $posts ) {
			$text = "We couldn't find exactly what you were looking for. Here are some possibilities." ;
			$text .= bw_navi( $atts ); 
    } else { 
			$text = "We couldn't find exactly what you were looking for. Perhaps it's in this list." ;
			unset( $atts['meta_key'] );
			$text .= bw_navi( $atts ); 
		}
		return( $text );
	}

}


