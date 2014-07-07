<?php // (C) Copyright Bobbing Wide 2014

/**
 * token object to record PHP classes, methods or functions
 *
 * This is a pretty naff implementation but it may serve its purpose for the first pass of extending
 * the code to handle PHP classes and methods.
 *
 * It needs to implement some of the code that a Reflection Function object implements
 *
 * 
 */
class oiksc_token_object {
  var $token;     // The token for the class, method or function
  var $docblock;  // The most recent docblock
  var $extends;           // parent class name
  var $implements;  
  var $extended_or_implemented;
  var $extended;
  var $implemented = array(); // Future use to list all the interfaces implemented
  var $classname;          // Will be null for a function
  var $methodname;         // Also used for function name
  var $endline;
  var $methods = array();
  var $refFunc = null;
  var $dummyFunc = null;
  var $open_curlies; // Number of open curly brackets - closed curly brackets when the class/function was encountered

  /**
   * Construct an oiksc_token_object
   *
   * @param array $token - a token object
   *
   */
  function __construct( $token ) {
    $this->token = $token;
  }
  
  /**
   * Export a token in CSV format
   *
   * Return a line in CSV format consisting of
   *  plugin,file,class,parent class,method,startline,endline,size
   *
   * This information is used to define a method/API
   *
   * @param string $plugin - plugin name
   * @param string $file - file name
   * @return string token in CSV format
   */
  function as_csv( $plugin, $file ) {
    $line[] = $plugin;
    $line[] = $file;
    $line[] = $this->classname;
    $line[] = $this->extended;
    $line[] = $this->methodname;
    $line[] = $this->getStartLine();
    $line[] = $this->getEndLine();
    $line[] = $this->getSize();
    $csv_line = implode( $line, "," );
    bw_trace2( $csv_line );
    return( $csv_line );
  }
  
  /**
   * Return the start line for the token object
   * 
   * @return integer - start line number
   */
  function getStartLine() {
    return( $this->token[2] );
  }
  
  /**
   * Return the end line for the token object
   * 
   * @return integer - end line number 
   */
  function getEndLine() {
    if ( $this->endline ) {
      $endLine = $this->endline;
    } else { 
      $endLine = null; // $this->getStartLine();
    }
    return( $endLine );
  }
  
  /**
   * Return the size of the token object
   * 
   * The size is end-start + 1
   * @return integer - the function length in lines
   */
  function getSize() {
    $end = $this->getEndLine();
    $start = $this->getStartLine();
    $size = $end - $start + 1;
    return( $size );
  }
  
  /**
   * Check if this token matches the requested API
   * 
   * @param string $funcname
   * @param string $classname
   * @return bool - true if both $funcname and $classname match
   */  
  function match( $funcname, $classname ) {
    if ( $funcname == $this->methodname && $classname == $this->classname ) { 
      $found = $this;
    } else {
      $found = null;
    }
    return( $found );
  }
  
  /**
   * Load the source file from start line to end line
   *
   * Loads the source for this function or method as a dummy function
   * Sets dummyFunc to the dummy Reflection Function
   */
  function load( ) {
    if ( null == $this->dummyFunc ) { 
      $this->dummyFunc = new oiksc_function_loader( $this ); 
    } 
    //bw_trace2( $this, __METHOD__ ); 
    ///bw_backtrace();
    //$this->refFunc =
    return( $this->dummyFunc );
  }
  
  function reflect() {
    if ( function_exists( $this->dummyFunc->dummy_function_name ) ) {
      $refFunc = oikai_reflect( $this->dummyFunc->dummy_function_name );
    } else {
      p( "Function does not exist" );
      $refFunc = null;
    }
    //echo $this->dummyFunc->dummy_function_name . PHP_EOL;  
    $this->refFunc = $refFunc; 
    return( $refFunc );
  } 
  
  function load_and_reflect() {
    $this->load();
    $this->reflect();
    $refFunc = $this;
    return( $refFunc );
  }
  
  function getDocComment() {
    $docblock = $this->docblock;
    return( $docblock );
  }
  
  function getParameters() {
    $this->load_and_reflect();
    if ( $this->refFunc ) {
      $parameters = $this->refFunc->getParameters();
    } else {
      $parameters = null;
      bw_backtrace();
    }
      
    return( $parameters );
  }
  
  function getFileName() {
    $filename = $this->dummyFunc->filename;
    return( $filename );
  }
  
  /**
   * Attempt to determine the API type from the source and comments
   */  
  function getApiType() {
    oik_require( "admin/oik-apitype.php", "oik-shortcodes" );
    if ( function_exists( "oikai_api_type" ) ) {
      oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
      $docblock = oikai_reflect_docblock( $this );
      // @TODO these functions will need moving.
      $type = oikai_api_type( $this->getApiName(), $this, $docblock ); 
      if ( $type ) {
        $type = $type[0];
      }
    } else {
      $type = null;
    }  
    return( $type );
  }
  
  function getShortDescription() {
    oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
    $docblock = oikai_reflect_docblock( $this );
    $sdesc = $docblock->getShortDescription();
    return( $sdesc );
  }
  
  function getApiName() {
    $apiname = null;
    if ( $this->classname ) {
      $apiname .= $this->classname . '::';
    }
    $apiname .= $this->methodname;
    return( $apiname );
  }
}
 
 
