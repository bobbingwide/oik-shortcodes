<?php // (C) Copyright Bobbing Wide 2014
/**
 * Functions to dynamically "load" methods and functions from source files that are not already loaded
 * 
 * Copied / moved from play/listapis2.php so that they can be used both by oik-batch's createapi.php and oik-shortcodes.
 *
 */
 
oik_require( "classes/class-oiksc-function-loader.php", "oik-shortcodes" );
oik_require( "classes/class-oiksc-token-object.php", "oik-shortcodes" ); 
 
/**
 * Return the token at $index if it's of type $type
 *
 * @param array $tokens - array of tokens from token_get_all()
 * @param integer $index - the index to the $tokens array
 * @param constant $type - constant token type required
 * @return $token - the token or null
 *
 * This doesn't include code to return the literal values
 *
 */
function _oiksc_get_token_object( $tokens, $index, $type=null ) {
  $token = null;
  if ( is_array( $tokens[$index] ) ) {
    if ( $tokens[$index][0] === $type ) {
      $token = new oiksc_token_object( $tokens[$index] );
    } else {
      $token = null;
    }  
  } elseif ( $tokens[$index] == $type ) {
    $token = new oiksc_token_object( $type );
  }
  return( $token );  
}

/**
 * Determine the end line number
 * 
 * When we come across a right curly brace the PHP token doesn't contain the line number.
 * 
 * We look for the previous token with a line number and add the number of line feeds in the token's content to its line number
 * to determine the end line of the right curly brace.
 * Note: We don't expect any more code after the right curly brace that ends the function.
 *
 * @param array $tokens - array of tokens
 * @param int $t - the index of the final curly brace
 * @return int - the calculated end line number
 
 */ 
function _oiksc_get_endline( $tokens, $t ) {
  $endline = null;
  while ( !is_array( $tokens[$t] ) ) {
    $t--;
  }
  $endline = $tokens[$t][2];
  $newlines = substr_count( $tokens[$t][1], "\n" );
  $endline += $newlines; //+ $crs;
  //echo "this thing ends at line $endline" . PHP_EOL;
  return( $endline );
}

/**
 * Return the "real file" name
 *
 * **?** This is a pretty hideous piece of code 
 * sometimes it gets passed a full file name
 * and other times it doesn't
 * 
 * @param $file - file name 
 * @param $component_type_p - the component type - "plugin"|"theme"| ?
 */ 
function oiksc_real_file( $file=null, $component_type_p=null ) {
  global $plugin, $filename, $component_type ;
  if ( $file ) {
    $filename = $file;
  }
  if ( file_exists( $filename ) ) {
    $real_file = $filename;  
  } else {
    if ( $component_type_p ) {
      $component_type = $component_type_p;
    }
    //echo "Plugin: $plugin! Filename: $filename! Component_type: $component_type!" . PHP_EOL;   
    if ( $plugin ) {
      if ( $plugin == "wordpress" ) {
        $real_file = ABSPATH . $filename; 
      } else {
        if ( $component_type == "plugin" ) {
          if ( defined( 'OIK_BATCH_DIR' ) ) { 
            $real_file = OIK_BATCH_DIR . '/' . $plugin . '/' . $filename;
          } else { 
            $real_file = WP_PLUGIN_DIR . '/' . $plugin . '/' . $filename;
          }
        } else {
          $real_file = get_theme_root() . '/' . $plugin . '/' . $filename; 
        }  
      }
    } else { 
      $real_file = ABSPATH . $filename;
    } 
  }
  //bw_trace2( $real_file, "Plugin: $plugin! Filename: $filename! Component_type: $component_type!" );
  //bw_backtrace();
  return( $real_file );
}

/**
 * Load the full file into an array 
 * 
 * @TODO - Allow the file to be loaded from the specified directory when invoked by oik-batch
 * 
 * @param string $file - a partial file name
 * @param string $component_type_p - "plugin"|"theme"| ?
 * @return array - the file contents
 */
function oiksc_load_file( $file=null, $component_type_p=null ) { 
  $real_file = oiksc_real_file( $file, $component_type_p );
  $contents_arr = file( $real_file );
  return( $contents_arr );
}

/** 
 * List functions implemented in the file
 * 
 *
 * Original commentary note:
 * The wp_doc_link_parse() function in wp-admin/includes/misc.inc does almost exactly the opposite of what we want.
 * It lists functions that might be documented as WordPress or PHP functions.
 * We want to list the ones that aren't.. so basically we want the contents of the $ignore_functions array! 
 * 
 * @param string $file - plugin source file name
 * @param string $component_type - "plugin"|"theme"
 * @return array $functions - array of implemented classes, methods and functions. 
 * 
 * In format:
 *
 * - "Class::" - class name only
 * - "Class::Method" - methods defined for the class
 * - "Function" - standalone functions
 *
 * Tokens are documented in http://php.net/manual/en/tokens.php 
 *
 * The values vary depending on PHP version, so we have to use the constant names. e.g. T_STRING, T_OBJECT_OPERATOR, T_FUNCTION, T_CLASS
 *
 */
function oiksc_list_file_functions2( $file, $component_type ) {
  // echo "Loading: $file, $component_type" . PHP_EOL;
  $contents_arr = oiksc_load_file( $file, $component_type );
  if ( $contents_arr ) {
    $contents = implode( $contents_arr );
    /**
     * Process each of the classes and their methods
     */
    $tokens = token_get_all( $contents );
    $classes = _oiksc_list_classes2( $tokens );
  } else { 
    $classes = null;
  }
  return( $classes );
}

/**
 * List the classes/functions implemented within the source file
 *
 * At the moment we don't need to know much about the classes
 * since we're not particularly interested in the properties
 * more the methods.
 * So now we need to find the methods for each class? BUT HOW?
 * We don't want to attempt to use Reflection functions since we may not be able to load the class
 * 
 * So do we extract the class and then search for functions using this logic? 
 * OR do we just extend this loop to look for methods within classes.
 * 
 * @TODO Now that we're also storing the token for the docblock do we need to stored the docblock contents as well?  
 * 
 * @param array $tokens - array of PHP tokens
 * @param string $get_token - the token type we're looking for T_CLASS/T_FUNCTION
 * @return array of oiksc_token_object instances
 * 
 */    
function _oiksc_list_classes2( $tokens, $get_token=T_CLASS ) {
  $functions = array();
  // set an arbitrary end point past which we won't validly find a class due to there not being enough tokens
  // allowing us to increment $t without any worries
  // Actually we cheat and add 3 dummy white space tokens
  $count = count( $tokens );
  $tokens[$count] = T_WHITESPACE;
  $tokens[$count+1] =  T_WHITESPACE;
  $tokens[$count+2] =  T_WHITESPACE;
  $t = 0;
  $thisclass = null;
  $thismethod = null;
  $methods = array();
  $docblock = null;
  $docblock_token = null;
  
  $open_curlies = 0;
  while ( $t < $count ) {
    $new_docblock = _oiksc_get_token( $tokens, $t, T_DOC_COMMENT );
    if ( $new_docblock ) {
      $docblock = $new_docblock;
      $docblock_token = $tokens[$t];
    }  
      
    $class = _oiksc_get_token_object( $tokens, $t, $get_token );
    if ( $class ) {
      $t += 2;
      $class->classname = _oiksc_get_token( $tokens, $t, T_STRING ); 
      if ( $class->classname ) {
        if ( $get_token === T_CLASS ) {
          $t2 = $t + 2;
          $class->extends = _oiksc_get_token( $tokens, $t2, T_EXTENDS );
          if ( $class->extends ) {
            $class->extended = _oiksc_get_token( $tokens, $t2+2, T_STRING );
          }
          $class->implements = _oiksc_get_token( $tokens, $t2, T_IMPLEMENTS );
          if ( $class->implements ) {  
            // $functions[] = "$function $extends$implements $extended_or_implemented";
            $class->implemented = _oiksc_get_token( $tokens, $t2+2, T_STRING );
            // @TODO - continue for other implemented classes
          }
        }
        $class->open_curlies = $open_curlies;
        $class->docblock = $docblock;
        $class->docblock_token = $docblock_token; 
        $thisclass = $class; 
        //print_r( $thisclass );
        $functions[] = $thisclass;
        $docblock = null;
      } else {
        bw_trace2( "program error", "Expecting class name after 'class' token" );
      }
    }
    
    
    $function = _oiksc_get_token_object( $tokens, $t, T_FUNCTION );
    if ( $function ) {
      $t += 2;
      $function->methodname = _oiksc_get_token( $tokens, $t, T_STRING ); 
      if ( $function->methodname ) {
        $function->open_curlies = $open_curlies;
        $function->docblock = $docblock;
        $function->docblock_token = $docblock_token; 
        if ( $thisclass && $thisclass->classname ) {
          $function->classname = $thisclass->classname;
        }  
        $thismethod = $function; 
        array_push( $methods, $thismethod );
        // print_r( $thismethod );
        $functions[] = $thismethod;
        $docblock = null;
      } else {
        bw_trace2( "program error", "Expecting function name after 'function' token" );
      }
    }
    
    /* Keep track of curly braces so that we know where the class / function starts and ends */
    if ( $tokens[$t] == '{' ) {
      $open_curlies++;
      //echo "{ $open_curlies" . PHP_EOL;
    }
    
    if ( _oiksc_get_token( $tokens, $t, T_DOLLAR_OPEN_CURLY_BRACES ) ) {
      $open_curlies++;
      //echo "{ $open_curlies" . PHP_EOL;
    }
    
    if ( _oiksc_get_token( $tokens, $t, T_CURLY_OPEN ) ) {
      $open_curlies++;
      //echo "{ $open_curlies" . PHP_EOL;
    }
   
    if ( $tokens[$t] == '}' ) {
      $open_curlies--;
      //echo "} $open_curlies" . PHP_EOL;
      //print_r( $thisclass );
      //print_r( $thisclass->open_curlies ); 
      if ( $thisclass && $thisclass->open_curlies == $open_curlies ) {
        $thisclass->endline = _oiksc_get_endline( $tokens, $t );
        $thisclass = null;
      } 
      if ( $thismethod && $thismethod->open_curlies == $open_curlies ) {
        $thismethod->endline = _oiksc_get_endline( $tokens, $t );
        array_pop( $methods );
        $thismethod = end( $methods );
        //print_r( $thismethod );
        //gobn();
      } 
    }  
    $t++;  
  }
  //echo __FUNCTION__ . $get_token . PHP_EOL;
  //print_r( $functions );
  return( $functions ); 
}

/** 
 * Return the oiksc_token_object for the chosen function
 *
 * @param array $functions - array of token objects
 * @param string $funcname
 * @param string $classname 
 */
function oiksc_find_function( $functions, $funcname, $classname ) {
  $found = false;
  foreach ( $functions as $function ) {
    if ( !$found ) {
      $found = $function->match( $funcname, $classname ); 
    }   
  }
  return( $found );
}

