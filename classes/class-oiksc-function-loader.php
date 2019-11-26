<?php // (C) Copyright Bobbing Wide 2014-2017

/**
 * Implement the dummy function for Reflection
 * 
 * 
 * 
 */
class oiksc_function_loader {
  var $function_obj;  // an instance of an oiksc_token_object
  var $dummy_function_name; // the name we give to the dummy function
  var $filename; // the plugin file name e.g. foobar/foobar.php
  var $plugin;   // the plugin slug e.g. foobar
  var $tempnam;  // the temporary name for the file to contain the dummy function
  var $component_type; 
  
  /**
   * Class constructor for oiksc_function_loader
   *
   * @param string $function - the name of the function or method
   * @return object oiksc_function_loader instance
   */
  function __construct( $function ) {
		//bw_backtrace();
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
		
    $this->function_obj = $function;
    static $fid = 0;
    $this->dummy_function_name = "oiksc_dummy_function_$fid" ;
    $fid++;
    ///$this->component_type = $component_type;
    $this->component_type = null;
    $this->extract_to_tmp();
		//bw_trace2( $this, "this" );
    $this->require_tmp();
    //return( $this );
  }
  
  /**
   * Append a record to the temporary file
   *
   * @param string $line - some PHP source code
   */
  function write( $line ) {
    $handle = fopen( $this->tempnam, "a+" );
    //echo $line;
    fwrite( $handle, $line );
    fclose( $handle );
  }
  
  /**
   *
   * Create a new first line for the function
   * 
       * Since we're loading this as a function we need to eliminate keywords that are applicable to methods but not functions.
       * 
       * Handling abstract is belt and braces - since there shouldn't be any code.
       * 
       * @TODO - this is still messy. For JetPack it converted func and caused a fatal error.
       * This current version alters variable names as well
       * Would it not be better to tokenize the line, find the first literal and replace that?
       * Answer: Yes. As demonstrated by [bw_api] public static protected function func( $func [/bw_api]
       * being handled correctly.
       * 

   */ 
  function create_dummy_function_line( $line ) {
    $tokens = token_get_all( "<?php " . $line );
    $replaced = false;
    $newline = null;
    foreach ( $tokens as $token ) {
      if ( !$replaced ) {
        if ( is_array( $token ) && ( $token[0] === T_STRING ) ) {
          $replaced = true;
          $newline = "function " . $this->dummy_function_name;
        } else {
          // Not a string token - keep looking
        }  
      } else {
        if ( is_array( $token )) {
          $newline .= $token[1];
        } else {
          $newline .= $token;
        }
      } 
    }
    return( $newline );
  }
  
  
  /**
   * Extract the source of a function or method to a temporary file
   *
   * If the function is an abstract method then it will contain no code
   * This is detectable by the end line being null.
   * Rather than load the first line we just create an empty function.
   *
   * 
   * Notes: We may use namespace oikscloa in the future.
   * 
   */
  function extract_to_tmp() { 
  
    // We need to specify the file name and component type here
    $contents_arr = oiksc_load_file( null, $this->component_type, $this->plugin ); 
    $this->tempnam = tempnam( sys_get_temp_dir(), "oikscloa");
    $start = $this->function_obj->getStartLine();
    $start--;
    $end = $this->function_obj->getEndLine();
    $line = "<?php //" . $this->function_obj->methodname . "\n" ;
    
    $this->write( $line );
    for ( $i = $start ; $i< $end; $i++ ) {
      //$line = strip_for_fun( $contents[$i] );
      $line = $contents_arr[$i];
      /**
       * Since we're loading this as a function we need to eliminate keywords that are applicable to methods but not functions.
       * Handling abstract is belt and braces - since there shouldn't be any code.
       * 
       * @TODO - this is still messy. For JetPack it converted func and caused a fatal error.
       * This current version alters variable names as well
       * Would it not be better to tokenize the line, find the first literal and replace that?
       * Answer: Yes. As demonstrated by [bw_api] public static protected function func( $func [/bw_api]
       * being handled correctly.
       * 
       */
      if ( $i == $start ) {
        $line = $this->create_dummy_function_line( $line );
				//$line = str_replace( "::", "__",  $line );
	      $line = str_replace( "self", "Telf", $line );

      } else { 
				$line = str_replace( "parent::", "Quarent::", $line );
				$line = str_replace( "self", "Telf", $line );
	            $line = str_replace( "new static", "new Ttatic", $line );
	            $line = str_replace( "static::", "Ttatic::", $line );
			//$line = str_replace( "Telf::", 'self__', $line );
			}
      $this->write( $line );
    }
    if ( !$end ) {
      $this->write( "function " . $this->dummy_function_name . "(){}" );
    }    
  }
  
  /**
   * Load the file like any other PHP file 
   *
   * We want to be able to perform Reflection Function processing on this source therefore it needs to be loaded into the current process.
   * Question: Is there any point in attempting to wrap this and detect any problems? 
   */ 
  function require_tmp() {
    require_once( $this->tempnam );
    unlink( $this->tempnam );
  }
}
  
