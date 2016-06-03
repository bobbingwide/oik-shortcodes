<?php // (C) Copyright Bobbing Wide 2016

/**
 * Class oiksc_parse_status
 *
 * Maintains information about the parse status of a component
 * so that when an update has been applied we only need to parse
 * the changed files. We use git diff to find which files have changed
 * between the last completed parse and the most recent version
 * 
 * from | to   | file | of   | pass | current | processing
 * ---- | ---- | ---- | ---- | ---- | -------	| ------------
 * shax | shay |    m |    n |    1 | shay    | continue parsing pass 1 then do pass 2
 * shax | shay |    n |    n |    1 | shay    | do parse 2 from file 1
 * shax | shay |    m |    n |    2 | shay    | continue parsing pass 2
 * shax | shay |    n |    n |    2 | shay    | parsing complete
 * shax | shay |    n |    n |    2 | shaz    | set from to shay and to to shaz and start pass 1
 * shax | shay |    ? |    ? |    ? | shaz    | confused state - restart from shax to shaz
 * 
 */
 
class oiksc_parse_status {

	/**
	 * Serialized post meta data for the component ( plugin / theme ) 
	 * _oiksc_parse_status
	 */ 									
	public $parse_status; 
	
  public $from_sha;
	public $to_sha;
	public $file_m;
	public $of_n;
	public $pass;
	
	public $current_sha;
	
	public $component_id;

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
	 * Register the parse status field
	 */
	public function register_fields() {
		bw_register_field( "_oiksc_parse_status", "serialized", "Parse status" ); 
		bw_register_field_for_object_type( "_oiksc_parse_status", "oik-plugins" );
		bw_register_field_for_object_type( "_oiksc_parse_status", "oik-themes" );
	}
	
	/**
	 * Constructor oiksc_parse_status
	 * 
	 */
	function __construct() {
		$this->reset();
	}
	
	/**
	 * Reset the properties
	 */
	function reset() {
		$this->parse_status = array();
		$this->current_sha = null;
		$this->from_sha = 0;
		$this->to_sha = null;
		$this->file_m = 0;
		$this->of_n = 0;
		$this->pass = 0;
		$this->component_id = 0;
	}
	
	/**
	 * Fetch the parse status 
	 *
	 * For each component we store the parse status.
	 * We use this for checkpoint restarting.
	 *
	 * @TODO Improve on the print_r() 
	 */
	public function fetch_status() {
		$parse_status = get_post_meta( $this->component_id, "_oiksc_parse_status", true );
		bw_trace2( $parse_status, "parse_status", false, BW_TRACE_DEBUG );
		if ( $parse_status ) {
			//$this->parse_status = unserialize( $parse_status );
			$this->parse_status = $parse_status;
			$this->extract_parse_status();
		}
		print_r( $this );
	}
	
	/**
	 * Extract the parse status fields
	 */
	public function extract_parse_status() {
		$this->from_sha = bw_array_get( $this->parse_status, "from_sha", 0 );
		$this->to_sha = bw_array_get( $this->parse_status, "to_sha", null );
		$this->file_m = bw_array_get( $this->parse_status, "file_m", 0 );
		$this->of_n = bw_array_get( $this->parse_status, "of_n", 0 );
		$this->pass = bw_array_get( $this->parse_status, "pass", 0 );
	}
	
	/**
	 * Populate the parse status fields
	 */
	public function populate_parse_status() {
		$this->parse_status['from_sha'] = $this->from_sha;
		$this->parse_status['to_sha'] = $this->to_sha;
		$this->parse_status['file_m'] = $this->file_m;
		$this->parse_status['of_n'] = $this->of_n;
		$this->parse_status['pass'] = $this->pass;
	}
	
	/**
	 * Update the parse status
	 */
	public function update_status() {
		$this->populate_parse_status();
		//print_r( $this->parse_status );
		//print_r( $this );
		//$parse_status = serialize( $this->parse_status );
		update_post_meta( $this->component_id, "_oiksc_parse_status", $this->parse_status );
	}
	
	/**
	 * Get the pass number
	 *
	 * @return integer pass number
	 */
	public function get_pass() {
		return( $this->pass );
	}
	
	/**
	 * Set the pass number
	 *
	 * @param integer the pass number
	 */
	public function set_pass( $pass ) {
		$this->pass = $pass;
	}
	
	/**
	 * Get the current file number
	 */
	public function get_file_m() {
		return( $this->file_m );
	}
	
	/**
	 * Set the current file number
	 */
	public function set_file_m( $file_m ) {
		$this->file_m = $file_m;
	}
	/**
	 * 
	 */
	public function get_of_n() {
		return( $this->of_n );
	}
	
	/**
	 * Set of_n
	 */
	public function set_of_n( $of_n ) {
		$this->of_n = $of_n;
	}
	
	/** 
	 * Set the component ID
	 *
	 * We need to reset everything here.
	 *
	 * @param ID $component_id post ID of the plugin/theme
	 */ 
	public function set_component( $component_id ) {
		echo "Component ID: $component_id" . PHP_EOL;
		$this->reset();
		$this->component_id = $component_id;
	}
	
	/**
	 * Return the SHA from which to list files
	 * 
	 * @param string $previous
	 * @return string from_sha
	 */
	public function get_from_sha( $previous=null ) {
		if ( $previous ) {
			$this->reset();
			$this->set_from_sha( $previous );
			$this->update_status();
		}
		$from_sha = current( explode( ' ', $this->from_sha ) );
		return( $from_sha );
	}
	
	public function set_from_sha( $from_sha ) {
		$this->from_sha = $from_sha;
	}
	
	public function get_to_sha() {
		$to_sha = current( explode( ' ', $this->to_sha ) );
		return( $to_sha );
	}
	
	public function set_to_sha( $to_sha ) {
		$this->to_sha = $to_sha;
	}
	
	public function set_current_sha( $current_sha ) {
		$this->current_sha = $current_sha;
	}
	
	public function set_current_of_n( $current_of_n ) {
		$this->current_of_n = $current_of_n;
	}
	
	/**
	 * Check if two passes are complete
	 *
	 * @return bool true if two passes have been performed
	 */
	public function finished_two_passes() {
		$finished_two_passes =  ( $this->file_m == $this->of_n ) && ( $this->pass == 2 );
		return( $finished_two_passes );
	}
	
	/**
	 * Restart processing
	 *
	 * Determine 
	 */ 
	public function restart_processing() {
		//print_r( $this );
		$finished_two_passes = $this->finished_two_passes();
		if ( $finished_two_passes ) { 
			if ( $this->to_sha != $this->current_sha ) {
				$this->from_sha = $this->to_sha; 
				$this->to_sha = $this->current_sha;
				$this->file_m = 0;
				$this->of_n = $this->current_of_n;
				echo "Restarting" . PHP_EOL;
				print_r( $this );
			} else {
				// It's the same - no need to do anything
			}
		} else {
			if ( !$this->to_sha ) {
				$this->to_sha = $this->current_sha;
				$this->of_n = $this->current_of_n;
			
			} else {
				//
			}
		}
		$this->update_status();
	}	 

}
 
