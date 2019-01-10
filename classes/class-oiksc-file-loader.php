<?php // (C) Copyright Bobbing Wide 2014-2016

/**
 * Implement the dummy file for Reflection
 * 
 */
class oiksc_file_loader extends oiksc_function_loader {
  //var $function_obj;  // an instance of an oiksc_token_object
  //var $dummy_function_name; // the name we give to the dummy function
  //var $filename; // the plugin file name e.g. foobar/foobar.php
  //var $plugin;   // the plugin slug e.g. foobar
  //var $tempnam;  // the temporary name for the file to contain the dummy function
  var $contents_arr;  
  var $contents;
  
  /**
   * Class constructor for oiksc_file_loader
   *
   * @param string $function - the name of the function or method
   * @param string $component_type - the component type for this function or method
   * @return object oiksc_function_loader instance
   */
  function __construct( $contents_arr, $component_type ) {
    //parent::__construct( null );
    global $plugin, $filename;
		
		if ( !$plugin ) {
			echo "Global plugin not set: $plugin!" . PHP_EOL;
			gob();
		}
		if ( !$filename ) {
			echo "Global filename not set:$filename" . PHP_EOL;
			gob();
		}
    $this->plugin = $plugin;
    $this->filename = $filename;
    $this->function_obj = null;
    $this->contents_arr = $contents_arr;
    static $fid = 0;
    $this->dummy_function_name = "oiksc_dummy_function_$fid" ;
    $fid++;
    //$this->extract_to_tmp();
    //$this->require_tmp();
    
    //print_r( $contents_arr );
    //print_r( $this );
    $this->component_type = $component_type;
    $this->extract_to_tmp();
    $this->require_tmp();
    //return( $this );
  }
  
  /**
   * Extract the source of a file to a temporary file
   *
   * Note: We don't need to worry about Fatal messages from parent:: or self::
	 * when no class scope is active.
	 *
	 * @TODO: Find out why not! Maybe we don't ever read the file! 
	 * 
   */
  function extract_to_tmp() { 
		bw_trace2();
    $this->tempnam = tempnam( sys_get_temp_dir(), "oikscloa");
    $line = "<?php function ";
    $line .= $this->dummy_function_name;
    $line .= "(){ ?>\n";
    $this->write( $line );
    $this->contents = array();
    foreach ( $this->contents_arr as $line ) {
      if ( $line != "" ) {
				$line = str_replace( "parent::", "Quarent::", $line );
				$line = str_replace( "self", "Telf", $line );
				//$line = str_replace( "new static", "new Ttatic", $line );

				 
        $this->write( $line );
        $this->contents[] = $line ;
      }
    }
    /*
    
    $tokens = token_get_all( $contents );
    if ( _oiksc_get_token( $tokens, count( $tokens )-1, T_INLINE_HTML ) ) {
      $line = "\n<?php } // ended in html ?>";
    } else {
      $line = "\n}";
    }
    */
    
    $this->write( $line );
  }
  
  /**
   * Load the file like any other PHP file 
   *
   * We want to be able to perform Reflection Function processing on this source therefore it needs to be loaded into the current process.
   * Question: Is there any point in attempting to wrap this and detect any problems? 
   * @TODO - DO WE REALLY need to perform reflection function processing on the "file"?
   *
   */ 
  function require_tmp() {
    //p( $this->tempnam );
    //bw_trace2( $this->contents );
    //require_once( $this->tempnam );
    //unlink( $this->tempnam );
  }
}
  
