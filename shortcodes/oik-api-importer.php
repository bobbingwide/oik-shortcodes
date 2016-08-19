<?php // (C) Copyright Bobbing Wide 2012-2016

/**
 * Print a function's parameters in a definition list 
 * 
 * @param ReflectionParameter $param the nth parameter
 * @param string $type parameter type. e.g. string, integer, array, mixed, post or an object type
 * @param string $name parameter name - should match $param->getName()
 * @param string $description parameter description
 * The parameter description ends with *dot* **space** *dot* space dot?
 * 
 */
function oikai_print_param_info( $param, $type="mixed", $name=null, $description=null ) {
  if ( null == $name ) {
    $name = "$" . $param->getName();
  } 
  $pos = $param->getPosition();
  $pos++;
  //bw_trace2( $pos, "pos" );
  $optional = $param->isOptional();
  //bw_trace2( $optional, "optional" );

  //bw_trace2( $param, "param" );
  //bw_trace2( $type );
  //bw_trace2( $name );
  //bw_trace2( $description );
  if ( $param->isDefaultValueAvailable() ) {
  
    $default = $param->getDefaultValue();
  } else {
    $default = null;
  }  
  //bw_trace2( $default, "Default: ");
  $passedbyref = $param->isPassedByReference();
  //bw_trace2( $passedbyref, "passed by ref" );
  
  stag( "dt" );
  e( $name );
  etag( "dt" );
  stag( "dd" );
  $dd = "( $type ) ";
  $dd .= ( $optional ) ? "<i>optional</i>" : "<i>required</i>" ;
  $dd .= " ";
  $dd .= ( $default )  ? "default: " . $default : "";
  $dd .= " - ";
  //$dd .= esc_html( substr( $description, 0, -5 ));
  e( $dd );
  $description = substr( $description, 0, -5 );
  $description = ltrim( $description, "- " );
  oikai_format_description( $description );
  etag( "dd" );
} 

/**
 * Print a parameter 
 *
 * `
 * Parameter #1 [ <optional> $parm2 = NULL ]DocBlock Object
   (
    [short_description:protected] => relect on me a while
    [long_description:protected] => Function to test the reflection logic to parse phpDoc comments and function prototypes
    If this works we can use this to dynamically generate content on the webpage
    BUT, since it may be dependent upon a certain version of PHP we might just have
    to create or update an oik_api record with the information
    [tags:protected] => Array
        (
            [0] => @link http://www.oik-plugins/
            [1] => @param string $parm1 - parameter 1
            [2] => @return string|null
        )

    [namespace:protected] => \
    [namespace_aliases:protected] => Array
        (
        )

   )
 * `
 * 
 * When the param is an array then we may document the array with a series of @type tags
 * which should also be formatted for the given param
 * We stop processing the @type fields when we come across another @param tag
 *   
 * @param object $param - refFunc parameter object 
 * @param object $docblock - docBlock for the function
 *  
 */
function oikai_print_param( $param, $docblock ) {
	//bw_trace2();
	//bw_backtrace();
  $parm = bw_array_get( $param, "name" );
  $parm = "$". $parm;
  $tags = $docblock->getTags();
	$found = null;
  //bw_trace2( $parm, "parm", true, BW_TRACE_VERBOSE );
  //bw_trace2( $tags, "tags", false, BW_TRACE_VERBOSE ); 
  $processed = null;
	$starteddl = false;
  foreach ( $tags as $tag ) {
    //bw_trace2( $tag, "tag" );
    list( $tagname, $type, $name, $description ) = explode( " ", $tag . " . . .", 4 );
    if ( $tagname == "@param" ) { 
      if ( $name == $parm ) {
        oikai_print_param_info( $param, $type, $name, $description );
				$found = $name;
				$processed = $name;
        //break;
      } else {
				$found = null;
        //bw_trace2( $name, "wrong param $parm " );
      }
		} elseif ( $tagname == "@type" ) {
			if ( $found == $parm ) {
				if ( !$starteddl ) {
					stag( "dl" );
					$starteddl = true;
				}
        oikai_print_param_info( $param, $type, $name, $description );
			}
    } else {
			
      break;
    } 
		
  }
	
	if ( $starteddl ) {
		etag( "dl" );
	}
  if ( null == $processed ) {
    oikai_print_param_info( $param );
  }
} 

/**
 * Print the return field information
 *
 * Now formats the description of the return value
 * 
 * @param string $type - the type of data returned
 * @param string $description - the description of the return value
 *
 */
function oikai_print_return_info( $type="void", $description=null ) {
  h2( "Returns" );
  $type = ( $type ) ? $type : "void";
  e( "<i>$type</i> " );
  //e( substr( $description, 0, -5 ));
  
  $description = substr( $description, 0, -5 );
  $description = ltrim( $description, "- " );
  
  oikai_format_description( $description );
}

/**
 * Print information about the @return value
 * 
 * @param object $refFunc - the reference function
 * @param object $docblock - the docBlock
 * @param bool $print - whether or not to print the output
 * @return type - the data type returned by the function 
 * 
 */
function oikai_print_return( $refFunc, $docblock, $print=true ) {
  // $returnsref = $refFunc->returnsReference();
  $tags = $docblock->getTags();
  $type = null;
  $description = null;
  foreach ( $tags as $tag ) {
    list( $tagname, $type, $description ) = explode( " ", $tag . " . . .", 3 );
    if ( ( $tagname == "@return" ) ) { 
      break;
    } else {
      $type = null;
      $description = null;
    }  
  }
  if ( $print ) {
    oikai_print_return_info( $type, $description );
  }
  return( $type );
}

/**
 * Print information about the @TODO tags, if any
 *
 * @param object $refFunc - a Reflection Function object
 * @param object $docBlock - a DocBlock object
 */
function oikai_print_todos( $refFunc, $docblock ) {
  $tags = $docblock->getTags();
  foreach ( $tags as $tag ) {
    //bw_trace2( $tag );
    list( $tagname, $description ) = explode( " ", $tag . " . . .", 2 );
    if ( substr( strtolower( $tagname ), 0, 5 ) == "@todo" ) {
      oikai_print_todo_info( $description );
    }
  }
}

/**
 * Print information about a TODO 
 *
 * There may be more than one TODO in a docblock. 
 * However, we shouldn't expect any. So, we format each TODO separately.
 * @param string $description
 *
 */ 
function oikai_print_todo_info( $description ) {
  sdiv( "todo" );
  h2( "TO DO" );
 
  $description = substr( $description, 0, -5 );
  $description = ltrim( $description, "- " );
  
  oikai_format_description( $description );
  ediv();
}

/**
 * Check to see if opcache is being used
 *
 * If it is then we will not be able to read the docblock using $refFunc->getDocComment()
 * against a function which is already loaded. Instead we use the dummy load and reflect technique
 * 
 * Prior to PHP 5.5.5 we can't find out anything about opcache
 * so we have to make it up.
 * WP Engine sets some constants which we might be able to use
 * BUT what we'll actually do is to see if we can get our own docBlock
 * written right here.
 *
 * @return bool - true if we believe that opcache processing is being used 
 */   
function oikai_using_opcache() {
	static $using_opcache = null;
	if ( null === $using_opcache ) {
		$using_opcache = false;
		$refFunc = oikai_reflect( __FUNCTION__ );
		if ( $refFunc ) { 
			$docComment = $refFunc->getDocComment(); 
			bw_trace2( $docComment, "docComment", false, BW_TRACE_DEBUG );
			if ( $docComment === false ) {
				$using_opcache = true;
			}
		}
		bw_trace2( $using_opcache, "using_opcache", false, BW_TRACE_DEBUG );
	}	
  return( $using_opcache ); 
}

/**
 * Determine if we're using oik-lib
 *
 * @return integer - the number of times "oik_lib_loaded" has been invoked
 */
function oikai_using_libs() {
	
	return( did_action( "oik_lib_loaded" ) );
}

/**
 * Attempt to create a reflection function / reflection method for this API
 *
 * When using opcache with comment stripping we have to use oikai_load_and_reflect().
 * When using shared libraries we may also need to use oikai_load_and_reflect().
 *
 * Otherwise processing depends on whether or not the function or method is already defined 
 * 
 * classname | class_exists| function or method exists |     Reflect using?
 * --------- | ------------| --------------- | ----------------
 * null      |    n/a      |       yes       |  oikai_reflect()
 * null      |    n/a      |       no        |  oikai_load_and_reflect()
 * set       |    no       |       n/a       |  oikai_load_and_reflect()
 * set       |    yes      |       no        |  oikai_load_and_reflect()
 * set       |    yes      |       yes       |  oikai_reflect_method()
 *
 * @param string $funcname - the API name
 * @param string $sourcefile - the full file name for this API
 * @param string $plugin - the plugin/theme name
 * @param string $classname - the classname if not part of $funcname
 * @return object - a refFunc object 
 */
function oikai_pseudo_reflect( $funcname, $sourcefile, $plugin, $classname=null ) {
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
  $refFunc = null;
  $using_opcache = oikai_using_opcache();
	if ( !$using_opcache ) {
		$using_oik_libs = oikai_using_libs();
	}
  if ( $using_opcache || $using_oik_libs ) {
    $refFunc = oikai_load_and_reflect( $funcname, $sourcefile, $plugin, $classname );
  } else {
    if ( $classname ) {
      if ( class_exists( $classname ) ) {
        if ( method_exists( $classname, $funcname ) ) {
          $refFunc = oikai_reflect_method( $classname, $funcname );
        } else {
          $refFunc = oikai_load_and_reflect( $funcname, $sourcefile, $plugin, $classname );
        }   
      } else {
        $refFunc = oikai_load_and_reflect( $funcname, $sourcefile, $plugin, $classname );
      }  
    } else {
      if ( function_exists( $funcname ) ) {
        $refFunc = oikai_reflect( $funcname );
				//bw_trace2( $refFunc, "refFunc", true, BW_TRACE_VERBOSE );
      } else {
        $refFunc = oikai_load_and_reflect( $funcname, $sourcefile, $plugin, $classname );
				//bw_trace2( $refFunc, "refFunc", true, BW_TRACE_VERBOSE );
      }
    }
  }
  bw_trace2( $refFunc, "refFunc", true, BW_TRACE_VERBOSE );
  return( $refFunc );
}

/**
 * Return the file path taking into account the special plugin name of 'wordpress'
 * 
 * @param string $sourcefile -
 * @param string $plugin - plugin or theme name
 * @param string $component_type
 * @return string - full file name of the WordPress file, plugin file or theme file
 */
function oik_pathw( $sourcefile, $plugin, $component_type= "plugin" ) {
  if ( $plugin == "wordpress" ) {
    $component_type = "wordpress";
  }
  switch( $component_type ) { 
    case "wordpress":
      $path = ABSPATH . $sourcefile;
      break;
    case "plugin": 
      $path = oik_path( $sourcefile, $plugin );
      break;
    case "theme":
    default:
      $path = get_theme_root() . '/' . $plugin . '/' . $sourcefile; 
  }
  return( $path );
}

/** 
 * Create a dummy reflection object for the API
 * 
 * Load the function/method from the file and create a dummy reflection object
 * that contains enough information for us to get by.
 *
 * @param string $funcname - the function or method name
 * @param string $sourcefile - relative filename
 * @param string $component_slug - the plugin or theme slug
 * @param string $classname - the classname, if applicable
 * @return refFunc object or null
 */   
function oikai_load_and_reflect( $funcname, $sourcefile, $component_slug, $classname ) {
	global $filename, $plugin, $component_type;
  $refFunc = null;    
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
	//bw_backtrace();
  if ( $sourcefile ) { 
    // oik_require( $sourcefile, $plugin );
    oik_require( "admin/oik-apis.php", "oik-shortcodes" );
    $component_type = oiksc_query_component_type( $component_slug );
    $filename = oik_pathw( $sourcefile, $component_slug, $component_type );
    if ( file_exists( $filename ) ) {
      //require_once( $filename );
      //$file = file_get_contents( $filename );
      //p( "TBC" );
      
      oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
      $functions = oiksc_list_file_functions2( $sourcefile, $component_type, $component_slug );
      //bw_trace2( $functions, "functions" );
      $function = oiksc_find_function( $functions, $funcname, $classname );
      //bw_trace2( $function );
      if ( $function ) {
				$filename = $sourcefile;
				$plugin = $component_slug;
        $refFunc = $function->load_and_reflect();
      }
    } else {
      p( "Source file not available: $sourcefile plugin: $plugin filename: $filename " );
    }
  }   
  return( $refFunc );
} 

/**
 * Return the ReflectionFunction for a function
 *
 * @param string $funcname - the function name
 * @return refFunc - ReflectionFunction object or null 
 * 
 * @package: oik-api importer
 * @uses ReflectionFunction
 * 
 */
function oikai_reflect( $funcname ) {
	bw_trace2( null, null, true, BW_TRACE_VERBOSE );
  try {
    $refFunc = new ReflectionFunction( $funcname );
		
		bw_trace2( $refFunc, "refFunc", true, BW_TRACE_VERBOSE );
  
  } catch (Exception $e) {
    bw_trace2( $e->getMessage(), "Caught exception", true, BW_TRACE_ERROR );
    $refFunc = null;
  }
  bw_trace2( $refFunc, "refFunc", true, BW_TRACE_DEBUG );
  return( $refFunc );
}
  
/**
 * Return the ReflectionFunction for a method
 *
 * @param string $classname - the class name
 * @param string $funcname - the function name
 * @return refFunc - ReflectionFunction object or null 
 * 
 * @package: oik-api importer
 * @uses ReflectionMethod
 * 
 */
function oikai_reflect_method( $classname, $funcname ) {
  try {
    $refFunc = new ReflectionMethod( $classname, $funcname );
  } catch (Exception $e) {
    bw_trace2( $e->getMessage(), "Caught exception", true, BW_TRACE_ERROR );
    $refFunc = null;
  }
  //bw_trace2( $refFunc );
  return( $refFunc );
}  
  
/**
 * Display the source file name
 *
 * Note: There is no need to convert $plugin into a link as this is done later
 * 
 * @param object $refFunc - a refFunc object
 * @param string $sourcefile - source file name - within plugin
 * @param string $plugin - plugin name
 *  
 */
function oikai_reflect_filename( $refFunc, $sourcefile, $plugin ) {
  $fileName = $refFunc->getFileName();
  h2( "Source" );
  e( "File name: " . $plugin. '/' . $sourcefile );
}

/**
 * Display the Syntax for calling the API
 *
 * @param object $refFunc - reflection object
 * @param object $docblock - the docblock object
 * @param string $funcname - the function or method name
 * 
 */
function oikai_reflect_usage( $refFunc, $docblock, $funcname ) {
  h2( "Usage");
  stag( "pre", null, null, "lang=PHP" );
  $return_type = oikai_print_return( $refFunc, $docblock, false );
  if ( $return_type ) {
   e ( "$" . $return_type . " = ");
  }
  e( "$funcname(" );
  $sep = " $";
  $param = null;
  if ( count( $refFunc->getParameters() ) ) {
    foreach( $refFunc->getParameters() as $param ) {
      e ( $sep );
      e( $param->getName() );
      $sep = ", $";
    }
  }  
  if ( $param ) { 
    e( " " );
  }  
  e ( ");" );   
  etag( "pre" );
}

/**
 * echo some other stuff
 */  
function oikai_reflect_etc( $refFunc ) {
  $startLine = $refFunc->getStartLine();
  echo "Start line: $startLine";
  echo PHP_EOL;
  $endLine = $refFunc->getEndLine();
  echo "End line: $endLine";
  echo PHP_EOL;
}

/**
 * Return the docBlock from the Reflection function
 *
 * @param object $refFunc - a Reflection function object
 * @return object - a DocBlock object for the Reflection function's DocComment
 */
function oikai_reflect_docblock( $refFunc ) {
	$docComment = null;
	if ( method_exists( $refFunc, "getDocComment" ) ) {
		$docComment = $refFunc->getDocComment(); 
	} else {
		bw_trace2( $refFunc, "getDocComment does not exist", false, BW_TRACE_ERROR );
	}
	bw_trace2( $docComment, "docComment", false, BW_TRACE_DEBUG );
	if ( class_exists( "DocBlock" ) ) {
		bw_trace2( "DocBlock already loaded?", null, false, BW_TRACE_VERBOSE );
	}
  oik_require( "classes/oik-docblock.php", "oik-shortcodes" );
	//bw_trace2( "required", "oik-docblock", false, BW_TRACE_DEBUG );
	
	if ( class_exists( "DocBlock" ) ) {
		bw_trace2( "Creating new DocBlock", null, false, BW_TRACE_VERBOSE );
		$docblock = new DocBlock( $docComment );
	} else {
		bw_trace2( "DocBlock does not exist", null, false, BW_TRACE_ERROR );
		$docblock = null;
	}
  //bw_trace2( $docblock );
  return( $docblock );
} 

/**
 * Display the API descriptions
 *
 * Display the short description then the long description.
 * 
 * Content | How to deal with it
 * ------- | --------------------
 * @ TODO  | Refer to a TODO CPT
 * @ link  | Convert the link into a link
 * other   | How do we handle something like this?
 * 
 *
 * @TODO: Properly handle TODO's and links - these are stored separately from the long description
 * in the 
 * `
     [tags:protected] => Array
        (
            [0] => @TODO: Handle markdown and other attempts at formatting the notes such as plain tables
            [1] => @TODO: Properly handle TODO's and links - these are stored separately from the long description!
            [2] => @param object $docblock - the docBlock object
        )
 * `
 * 
 * @param object $docblock - the docBlock object
 * 
 */ 
function oikai_reflect_descriptions( $docblock ) {
  h2( "Description" );
  p( esc_html( $docblock->getShortDescription() ) );
  //p( esc_html( $docblock->getLongDescription() ) );
  sp();
  oikai_format_description( $docblock->getLongDescription() );
  ep();
  //bw_trace2( $docblock );
}

/**
 * Format markdown list
 *
 * Decide what to do when we see a blank line?
 * We won't know if we're starting actually starting a list until we see the first '-' 
 * But a blank line will end the list. We need to know the list type.
 * 
 * 
 * @param integer $list - whether or not we think we're in a list. false, true, next list item
 * @return integer - new value
 *
 */
function oikai_format_markdown_list( $list ) {
  if ( $list >= 1 ) {
    etag( oikai_list_type() );
    $list = 0;
  } else {
    //$list = (int) !$list;
    $list = 0;
  } 
  //e( __FUNCTION__ . $list );
  return( $list );
}

function oikai_list_type( $type = null ) {
  static $list_type = null;
  if ( $type ) {
    $list_type = $type;
  }
  return( $list_type );
}

/**  
 * Format a markdown list started with a hyphen
 *  
 */
function oikai_format_markdown_list_hyphen( $list ) {
  $rlist = $list;
  if ( 0 === $rlist ) {
    stag( oikai_list_type( "ul" ) );
  }
  $rlist++;
  bw_trace2( $rlist, "list++", false, BW_TRACE_DEBUG );
  return( $rlist ); 
}


/**  
 * Format a markdown list started with a number
 * 
 */
function oikai_format_markdown_list_number( $list ) {
  if ( 0 === $list ) {
    stag( oikai_list_type( "ol" ) );
  }
  $list++;
  return( $list ); 
}

/**
 * End the list we've started
 *
 * @param integer $list -
 * @returm integer 0
 */
function oikai_format_markdown_list_end( $list ) {
  if ( $list >= 1 ) {
    etag( "ul" );
  }
  $list = (int) 0;
  return( $list );  
}

/**
 * Create a heading taking into account number of #'s
 *
 * @param string $line - with one or more leading #####
 * 
 */ 
function oikai_format_markdown_heading( $line ) {
  $len = strlen( $line );
  $line = ltrim( $line, "#" );
  $level = $len - strlen( $line );
  $line = ltrim( $line );
  stag( "h$level" );
  e( esc_html( $line . " " ) );
  etag( "h$level" );
}

/**
 * Preprocess a line for subsequent formatting
 *
 * When the line appears to be a table line then prepend a pipe character followed by a space.
 * 
 * We also detect unordered lists created using 
 *  * blank star blank
 *  * like this
 *
 * And another way is to use 
 *   two blanks
 *   like this 
 *   which may represent a simple list.
 *
 * @TODO Allow for left, right and centred alignment
 * @TODO Allow for table lines where there's an intentional blank in cell 1.
 * @TODO Support nested lists
 * 
 * @param string $line - the line from the docblock
 * @return string - the preprocessed line
 */
function oikai_format_preprocess_line( $line ) {
  $pos = strpos( $line, " | " );
  if ( $pos !== false ) {
    $line = ltrim( $line, "| ");
    $line = "| " . $line; 
    //e( "tr: $line" );
  }
	
	$pos = strpos( $line, " * " );
	if ( $pos === 0 ) {
		$line = trim( $line );
	}
	
	$pos = strpos( $line, "  " );
	if ( $pos === 0 ) {
		$line = "* " .  trim( $line );
	}
  return( $line );   

}

/**
 * Handle the end of a table
 * 
 * Are tables as simple to end as lists?
 */
function oikai_format_markdown_table( $table ) {
  if ( $table > 1 ) {
    etag( "table" );
  }
  $table = 0;
  return( $table );
}


/**
 * Handle a line which appears to be part of a table
 *
 * Do we need to look ahead to the next line to find out if we really are processing a table?
 *
 * @param integer $table - where we are in the table processing
 * @param string $line - the line with "| " prepended, if required
 * 
 */
function oikai_format_markdown_table_line( $table, $line ) {
  //static $th = null;
  
  switch ( $table ) {
    case 0:
      oik_require( "bobbforms.inc" );
      stag( "table" );
      stag( "thead" );
      $line = ltrim( $line, "|" );
      $cols = str_getcsv( $line, "|" );
      bw_trace2( $cols, "cols", true, BW_TRACE_VERBOSE );
      bw_tablerow( $cols, "tr", "th" );
      etag( "thead" );
      break;
      
    case 1:
      // Do nothing yet
      // We might generate some special CSS for alignment
      break;
      
    default:
      $line = ltrim( $line, "|" );
      $cols = str_getcsv( $line, "|" );
      bw_trace2( $cols, "cols", true, BW_TRACE_VERBOSE );
      bw_tablerow( $cols, "tr", "td" );
  }
  $table++;
  return( $table );
}

/**
 * Format a markdown line 
 *
 * The line may not end in a blank so we append one to make up for the new line character we've stripped off
 *
 * Now we have to decide about this esc_html() call.
 * There's no point doing it if we're about to add some HTML 
 * so obviously we need to esc_html() first then apply the markdown
 * 
 * Wrapper | Becomes
 * ------- | ---------
 *  _      | <em>
 *  *      | <em>
 *  __     | <strong>
 *  **     | <strong>
 *  `      | <code>
 * {@link  | http://
 * {@see   | http://
 * 
 * @param string $line
 */ 
function oikai_format_markdown_line( $line ) {
	//bw_trace2( $line, "line", false );
  $line = esc_html( $line );
  $line .= " ";
  $line = paired_replacements( " **", "** ", " <strong>", "</strong> ", $line );
  $line = paired_replacements( " *",  "* ",  " <em>", "</em> ", $line );
  $line = paired_replacements( " __", "__ ", " <strong>", "</strong> ", $line );
  $line = paired_replacements( " _",  "_ ",  " <em>", "</em> ", $line );
  $line = paired_replacements( " `", "` ", " <code>", "</code> ", $line );
  $line = paired_replacements( "{@link ", "} ", "http://", " ", $line );
  $line = paired_replacements( "{@see ", "} ", "http://", " ", $line );
  $line = URL_autolink( $line );
  e( $line );
}

/**
 * Autolink an URL
 * 
 * We don't expect more than one URL per line
 *
 * These are for testing
 * 
 * {@link http://qw/oikcom }
 * {@link http://qw/oikcom } better
 *
 * @param string $line - which may contain an URL
 * @return string - line with autolinked URL
 */
function URL_autolink( $line ) {
  $url = strpos( $line, "http://" );
  if ( $url === false ) {
    $url = strpos( $line, "https://" );
  }
  if ( $url !== false ) {
    $left = substr( $line, 0, $url ); 
    $midright = substr( $line, $url );
    $rpos = strpos( $midright, " " );
    if ( $rpos !== false ) {
      $middle = substr( $midright, 0, $rpos );
      $niceurl = str_replace( "https:", "http:", $middle );
      $niceurl = str_replace( "http://", "", $niceurl );
      $right = substr( $midright, $rpos );
      $line = $left . retlink( null, $middle, $niceurl ) . $right;
    }
  }
  return( $line );  
}

/**
 * Perform replacements of paired markup strings
 *
 * Note: This logic requires the paired replacements to appear on the same line.
 * It should cater for nested pairs. 
 *
 * @param string $before - the start of the markdown string
 * @param string $after - the end of the markdown string
 * @param string $beforetag - the string to replace before 
 * @param string $aftertag - the string to replace after 
 * @param string $line - the string to be searched
 * @return string - the updated string  
 */
function paired_replacements( $before, $after, $beforetag, $aftertag, $line ) {
  $spos = strpos( $line, $before );
  while ( $spos !== false ) {
    $epos = strpos( $line, $after );
    if ( $epos > $spos ) {
      $line = replace_at( $epos, $after, $aftertag, $line );
      $line = replace_at( $spos, $before, $beforetag, $line );
      $spos = strpos( $line, $before );
    } else {
      $spos = false;
    }
  }
  return( $line );
}

/** 
 * Replace the instance of $source at $pos with $replace
 * 
 * 
 *
 * @param integer $pos - the position of the first character of source, starting from 0
 * @param string $source - the string to be replaced e.g. " *"
 * @param string $target - the string to replace the source with
 * @param string $line - the original line
 * @return string - the new line
 */ 
function replace_at( $pos, $source, $replace, $line ) {
  $left = substr( $line, 0, $pos );
  $right = substr( $line, $pos + strlen( $source ) ); 
  $line = $left . $replace . $right;
  return( $line );   
}  

/**
 * Format the long description
 *
 * It's been nicely typed in with a few formatting choices and we want to make it fairly readable.
 
 * First thing we want to do is handle single backticks to create a pre.
 * `
 * So this would be unformatted
 *  Does it work? Note the leading space.
 * `
 * What happens afterwards?
 *
 * #### For an unordered list
 *
 * - A blank line
 * - Followed by a series of lines prefixed with "- " 
 * * or they could be "* "
 * - And another blank line at the end 
 *
 * #### For an ordered list
 *  
 * 9. Prefix with a number
 * 2. Doesn't matter what numbering
 * 0123 So long as it starts with a digit, which includes 0
 * 1. Not sure what to do about dots after the number. Assume we can strip them.
 * 
 * #### For a heading
 *
 * The number of #'s indicate the heading level. This is an atx-style header.
 * 
 * #### Styled text
 * 
 *  _Wrapped_ stuff:
 *  
 * - Single `_underscores_` for _italics_  i.e. emphasis <em>
 * - Or single `*stars*` for *emphasis*  
 * - Double `**stars**` for **bold** i.e. <strong>
 * - Or double `__underscores__` for __strong__
 * - Nested stuff **Everyone _must_ attend the meeting at 5 o'clock today.**
 * 
 * #### URL links
  
 * - http://example.com 
 * - inline {@see example.com} -  {@see example.com} 
 * - inline {@link example.com} -  {@link example.com}
 *
 * 
 * #### TODO
 * 
 * - > for blockquotes
 * - inline `{@todo todo item}`
 *
 * #### For reference
 * 
 * - https://help.github.com/articles/markdown-basics/
 * - https://help.github.com/articles/github-flavored-markdown/
 * 
 * #### Tables
 * 
 * First header | Second header
 * ------------ | ------------
 * First cell   | Second cell
 * 
 * Initially we'll work with lines prefixed by the pipe character.
 * If we find a line that contains a space pipe space then we'll prefix it
 * 
 * 
   
 *  
 * @param string $long_description
 */ 
function oikai_format_description( $long_description ) {
  $lines = explode( "\n", $long_description );
  bw_trace2( $lines, count( $lines ), false, BW_TRACE_DEBUG );
  $backtick = false;
	$curlybrace = false;
  $list = 0;
  $table = 0;
  foreach ( $lines as $line ) {
    $line = oikai_format_preprocess_line( $line );
    if ( strlen( $line ) ) {
      $char = $line[0];
    } else {
      $char = null;
    }  
    switch ( $char ) {
      case null: 
        e( "\n" );
        $list = oikai_format_markdown_list( $list );
        $table = oikai_format_markdown_table( $table );
        break;
        
      case '`':
        //bw_trace2( $backtick, "backtick", false ) ;
        $backtick = !$backtick;
        //bw_trace2( $backtick, "! backtick", false );
        if ( $backtick ) {
          stag( "pre" );
        } else {
          etag( "pre" );
        }
      break;
      
      case '-':
      case '*':
        $list = oikai_format_markdown_list_hyphen( $list );
        if ( $list ) {
          stag( "li" );
        }
        $line = ltrim( $line, "*- " );
        //e( esc_html( $line . " " ) );
        oikai_format_markdown_line( $line );
        if ( $list ) {
          etag( "li" );
        }  
        break;
        
      case '0';  
      case ctype_digit( $char ):
        $list = oikai_format_markdown_list_number( $list );
        if ( $list ) {
          stag( "li" );
        }
        $line = ltrim( $line, "0123456789. " );
        //e( esc_html( $line . " " ) );
        oikai_format_markdown_line( $line );
        if ( $list ) {
          etag( "li" );
        }  
        break;
        
      case '|':
        $table = oikai_format_markdown_table_line( $table, $line );
        break;
        
      case '#':
        $list = oikai_format_markdown_list_end( $list );
        $table = oikai_format_markdown_table( $table );
        $hn = oikai_format_markdown_heading( $line );
        break;
			 
      case '{':
				$curlybrace = true;
        oikai_format_markdown_line( $line );
        break;
			
			case '}':
				$curlybrace = false;
        oikai_format_markdown_line( $line );
				break;
				
      case '@':
				bw_trace( $line, "Logic error. @ not expected", true, BW_TRACE_ERROR );
        oikai_format_markdown_line( $line );
			 	break;
    
      default: 
        $list = oikai_format_markdown_list_end( $list );
        //e( esc_html( $line . " " ) );
        oikai_format_markdown_line( $line );
        if ( $backtick ) { 
          e( "\n" );
        }  
        
    }
  }
  /**
   * End any tags we may have started 
   */
  $list = oikai_format_markdown_list_end( $list );
  $table = oikai_format_markdown_table( $table );
  if ( $backtick ) {
    etag( "pre" );
  }
}  

/**
 * List the function parameters
 *
 * @param refFunc $refFunc - the refFunc object
 * @param docBlock $docblock - the docblock object
 *
 */
function oikai_reflect_parameters( $refFunc, $docblock ) {
  h2( "Parameters" );
  stag( "dl" );
  if ( count( $refFunc->getParameters() ) ) {
    foreach( $refFunc->getParameters() as $param ){
      oikai_print_param( $param, $docblock );
    }
  }  
  etag( "dl" );
}

/**
 * Load a function from the source file
 *
 * @param string $fileName
 * @param object $refFunc
 * @return array lines from the source file
 */ 
function oikai_load_from_file( $fileName, $refFunc ) {
	static $savedFileName = null;
	static $savedFile = null;
	if ( $savedFileName !== $fileName ) {
		$savedFile = file( $fileName );
		$savedFileName = $fileName;
	} 
	$file = $savedFile;
 	// $file = file( $fileName );
  bw_trace2( $file, null, true, BW_TRACE_DEBUG );
  $start = $refFunc->getStartLine();
  $sources = array();
  $end = $refFunc->getEndLine();
  if ( !$end ) {
    $end = $start;
  }
  $start--;
  //stag ("pre lang=PHP" );
  for ( $line = $start; $line < $end; $line++ ) {
    $sourceline = $file[$line];
    $sources[] = $sourceline;
    //$sourceline = htmlentities( $sourceline );
    //e( $line+1 . " " . $sourceline );
  }  
  //etag( "pre" );
  return( $sources );
}

/**
 * Set the time limit except in batch mode
 *
 * Note: Some plugins check if it's OK to set this. e.g. Easy-Digital-Downloads
 * Here we just check if we're running in batch ( PHP from the command line )
 * 
 * @param integer $limit time limit in seconds
 */
function oikai_set_time_limit( $limit=120 ) {
	if ( PHP_SAPI == "cli" ) {
		//
	} else {
		set_time_limit( $limit );
	}
}

/**
 * List the source of the function
 *
 * List source may involve parsing some or all of the source to create the dynamic listing.
 * When we only want a bit of the source then we will parse only a subset
 * but when we're parsing it for real we do the lot.
 * Whether or not we update the parsed source after parsing depends on how we were doing it. 
 * 
 * $parsed_source | context( "paged" ) | processing                | update parsed source
 * -------------- | ------------------ | ---------------------     | --------------------  
 * latest         | false              | Parse the full source     | Yes     
 * latest         | other              | Use the parsed source     | No
 * not latest     | false              | Parse the full source     | Yes
 * not latest     | true               | Parse part of the source  | No
 *                                                                 
 * 
 * 
 * Once we've got the source then we can do the "navi" bit.
 * 
 * @param object $refFunc
 * @param ID $post_id 
 * @param string $component_type
 * @param bool $echo true if we actually do want the source listed
 * 
 */
function oikai_listsource( $refFunc, $post_id=null, $plugin_slug, $component_type, $echo=true ) {
	bw_trace2( $refFunc->methodname, $plugin_slug, false ); 
  $fileName = $refFunc->getFileName();
  $paged = bw_context( "paged" );
	$saved_post = null;
	$parsed_source_id = null;
  oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
	$oiksc_parsed_source = oiksc_parsed_source::instance();
  $parsed_source = $oiksc_parsed_source->get_latest_parsed_source_by_sourceref( $fileName, $component_type, $post_id, $plugin_slug );
  
  if ( $post_id && $paged !== false ) {
		
    // $parsed_source = bw_get_parsed_source_by_sourceref( $post_id );
		$saved_post = bw_global_post( $parsed_source );
		if ( $parsed_source ) {
			$parsed_source_id = $parsed_source->ID;
		}
  } else {
    $parsed_source = null;
  }
  
  if ( !$parsed_source ) { 
		$fileName = oiksc_real_file( $fileName, $component_type, $plugin_slug ); 
    $sources = oikai_load_from_file( $fileName, $refFunc );
		$parsing_necessary = $oiksc_parsed_source->is_parsing_necessary( $sources );
    if ( $paged === false && $parsing_necessary ) {
      oikai_set_time_limit();
      bw_push();
      oikai_syntax_source( $sources, 1 );
      $parsed_source = bw_ret();
      bw_pop();
      
      oik_require( "classes/class-oiksc-parsed-source.php", "oik-shortcodes" );
      oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
      //$plugin_slug = get_post_meta( $plugin_id, "_oikp_slug", true );
      //$component_type = oiksc_query_component_type( $plugin_slug );
      global $plugin;
      $plugin = $plugin_slug;
      // e( "flarg: $fileName" );
      $parsed_source_id = $oiksc_parsed_source->update_parsed_source( $post_id, $parsed_source, $fileName );   //   $files[$file]
    } else { 
      // We only handle a subset!
      // $parsed_source = null;
			//
    }
  } else { 
    $parsed_source = $parsed_source->post_content;
  }
	
	if ( $echo ) { 
		if ( $parsed_source ) {
			//oikai_navi_parsed_source( $parsed_source );
			oik_require( "shortcodes/oik-parsed-source.php", "oik-shortcodes" );
			$atts = array( "paged" => $paged, "id" => $parsed_source_id ); 
			$result = oikai_parsed_source( $atts, $parsed_source );
			c( "after oikai_parsed_source" );
			e( $result ); 
			c( "agter result" );
		} else {
			oikai_navi_source( $sources );
		}
	}	
	bw_global_post( $saved_post );
}

/**
 * List the source of a function using pagination
 *
 * Good programming style suggests functions should be less than 200 lines long.
 * Some WordPress code extends to over 1,000 lines.
 * This takes a long time to parse and markup with links
 * So until we have a cacheing system in place 
 * we'll use source code pagination
 * 
 * We assume that the PHP token get all function will not have a problem parsing source code that's been randomly chunked.
 * It's probably a very bad assumption. 
 * 
 * @param array $sources - array of source lines to paginate
 */
function oikai_navi_source( $sources ) {
  oik_require( "shortcodes/oik-navi.php" );
  $bwscid = bw_get_shortcode_id( true );
  $page = bw_check_paged_shortcode( $bwscid );
  $posts_per_page = 100; // get_option( "posts_per_page" );
  $count = count( $sources );
  $pages = ceil( $count / $posts_per_page );
  $start = ( $page-1 ) * $posts_per_page;
  $end = min( $start + $posts_per_page, $count ) -1;
  bw_navi_s2eofn( $start, $end, $count, bw_translate( "Lines: " ) );
  for ( $i = $start; $i<= $end; $i++ ) {
    $selection[] = $sources[$i];
  }
  //e( "countsel " . count( $selection ) );
  oikai_syntax_source( $selection, 1 ); 
  bw_navi_paginate_links( $bwscid, $page, $pages );
} 

/**
 * Simple function to syntax hilight PHP source
 * 
 * Possibly redundant?
 */
function oikai_highlight_source( $sources ) {
  $content = "<?php\n";
  $content .= implode( "", $sources );
  stag( "pre" );
  e( highlight_string( $content, true ) );
  etag( "pre" );
}

/**
 * Create a link for a PHP function
 *
 * Original notes... right at the start of the project in 2012
 * 
 * There is logic within wp-admin/plugin-editor.php that will try to show the documentation for a function
 *
 * For a PHP function it goes to: e.g. http://uk3.php.net/call_user_func
 * For a WordPress function it goes to: e.g. http://api.wordpress.org/core/handbook/1.0/?function=oik_depends&locale=en_US&version=3.4.2&redirect=true
 * 
 * It would be nice if we could reproduce this in a display function API
 * 
 * Note: The way you can tell if it's a PHP function is to use get_defined_functions()
 * @link http://uk1.php.net/get_defined_functions
 * PHP functions are in the [internal] array 
 * User defines - our's and WordPress's are in the [user] array
 * 
 * Example for the array_merge() function
 * @link http://www.php.net/manual/en/function.array-merge.php
 *
 * @param string $value
 * @return string HTML link to the PHP function
 */
function oikai_link_to_php( $value ) {
  $hyphen = str_replace( "_", "-", $value );
  $url = "http://www.php.net/manual/en/function.$hyphen.php" ;
  $link = retlink( null, $url, $value, "PHP docs for: $value" );
  return( $link );
}

/**
 * Create a link to WordPress documentation
 *
 * Originally this code would link to:
 *
 * @link http://api.wordpress.org/core/handbook/1.0/?function=bw_backtrace&locale=en_US&version=3.4.2&redirect=true
 *
 * As this was the method used in the code editor
 *
   <input type="button" class="button" value="Lookup " 
   onclick="if ( '' != jQuery('#docs-list').val() ) { 
   window.open( 'http://api.wordpress.org/core/handbook/1.0/?function=' + escape( jQuery( '#docs-list' ).val() ) + '
   &amp;locale=en_US&amp;version=3.4.2&amp;redirect=true'); }">
 *
 * Then we changed it to link to the codex:
 *
 * @link http://codex.wordpress.org/Function_Reference/$value
 *
 * In April 2014, links were to the 'new' Developer Reference
 * @TODO We needed to create different links for:
 *  functions  /reference/functions/$value
 *  classes    /reference/classes/$class
 *  methods    /reference/classes/$class/$method
 *
 * As of June 2014, links are to wp-a2z.com - the complete Dynamic API Reference
 * 
 * Again, we need to be able to construct the correct permalink
 * For OO code we just strip the "::"... and hope for the best
 * 
 * @TODO This doesn't detect class names on their own.
 *
 */
function oikai_link_to_wordpress( $value ) {
  $value = str_replace( ":", "", $value );
  global $wp_version;
  $url = "http://api.wordpress.org/core/handbook/1.0";
  $url = "http://codex.wordpress.org/Function_Reference/$value";
  $url = "http://developer.wordpress.org/reference/functions/$value"; 
  $url = "http://wp-a2z.com/oik_api/$value"; 
  /*
  $url = add_query_arg( array( "function" => $value
                             , "version" => $wp_version
                             , "locale" => "en_US"
                             , "redirect" => true 
                             )
                      , $url );
  */
  $link = retlink( null, $url, $value, "WordPress API: $value()" );
  return( $link );
}

/**
 * Create an "oik_hook" post_type
 *
 * @param string $hook - the hook name - which may contain variables
 * @param string $context - the function in which we first found out about this hook    
 * @return ID 
 */
function _oikai_create_oik_hook( $hook, $context ) {
  // p( "Creating hook $hook" );
  oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
  $post = oiksc_get_oik_hook_byname( $hook );
  //bw_trace2( $post, "post" );
  if ( !$post ) {
    $post_id = oiksc_create_oik_hook( $hook, $context );     
  } else {
    $post_id = $post->ID;
    oiksc_update_oik_hook( $post, $hook, $context );
  }
  
  //oiksc_build_callees( $api, $file, $plugin );
  //oikai_save_callees( $post_id );
  
  return( $post_id );
}

/**
 * Return a hook name from the dummy_TCES
 *
 * The hook name is the concatenation of the tokens we've saved
 * with double or single quotes removed
 *
 * @param array $tokens - the tokens
 * @return string $hook_name - which may be ''
 *  
 */
function oikai_concoct_hook_name2( $tokens ) {
  $TCESes = bw_context( "dummy_TCES" );
  //bw_trace2( $TCESes, "TCESes", false );
  $hook_name = '';
  if ( is_array( $TCESes) && count( $TCESes ) ) {
    foreach ( $TCESes as $TCES ) {
      //bw_trace2( $TCES, "TCES", false ); 
      if ( is_array( $tokens[$TCES] ) ) {
        $hook_name .= trim( $tokens[$TCES][1], "\"'" );
      }  
    }
  }  
  bw_trace2( $hook_name, "hook_name", false, BW_TRACE_DEBUG );
  //br( "hook_name $hook_name" );
  return( $hook_name );  
} 


/**
 * Return an API name from the dummy_TCES
 *
 * The API name is the concatenation of the tokens with "::"s separating them
 * We really don't expect any more than two tokens to have been saved
 * AND if it's only one then we remove the leading "::"
 *
 * @param array $tokens - the tokens
 * @return string $hook_name - which may be ''
 *  
 */
function oikai_concoct_api_name2( $tokens ) {
  $TCESes = bw_context( "dummy_TCES" );
  //bw_trace2( $TCESes, "TCESes", false );
  $api_name = '';
  if ( is_array( $TCESes) && count( $TCESes ) ) {
    foreach ( $TCESes as $TCES ) {
      //bw_trace2( $TCES, "TCES", false ); 
      if ( is_array( $tokens[$TCES] ) ) {
        $api_name .= "::" . trim( $tokens[$TCES][1], "\"'" );
      }  
    }
  }  
  bw_trace2( $api_name, "api_name", false, BW_TRACE_DEBUG );
  
  $api_name = ltrim( $api_name, ":" );
  bw_trace2( $api_name, "API_name", false, BW_TRACE_VERBOSE );
  return( $api_name );  
} 



/**
 * Set a link on each relevant token in the dummy TCES
 * 
 * @param ID $post_id - the ID of the post to link to
 * @param string $title - 
 * @param string $context - used for the link class
 * @param array - array of tokens 
 */
function oikai_set_links( $post_id, $title, $context, &$tokens ) {
  //e( "setting links for $post_id $title ");
  $TCESes = bw_context( "dummy_TCES" );
  //bw_trace2( $TCESes, "TCESes", false );
  if ( is_array( $TCESes) && count( $TCESes ) ) {
    $permalink = oiksc_get_permalink( $post_id );
    $title_text = get_the_title( $post_id );
    foreach ( $TCESes as $TCES ) {
      //bw_trace2( $TCES, "TCES", false ); 
      //bw_trace2( $tokens[$TCES], "tokens[TCES]", false );
      if ( is_array( $tokens[$TCES] ) ) {
        $tokens[$TCES][3] = retlink( "$context", $permalink, $tokens[$TCES][1], $title_text ); 
      } 
    }
  }
}


/**
 * Set a pragmatic link on each relevant token in the dummy TCES
 * 
 * @param ID $post_id - the ID of the post to link to
 * @param string $title - 
 * @param string $context - used for the link class
 * @param array - array of tokens 
 */
function oikai_set_pragmatic_links( $url, $title, $context, &$tokens ) {
  //e( "setting links for $post_id $title ");
  $TCESes = bw_context( "dummy_TCES" );
  //bw_trace2( $TCESes, "TCESes", false );
  if ( is_array( $TCESes) && count( $TCESes ) ) {
    foreach ( $TCESes as $TCES ) {
      //bw_trace2( $TCES, "TCES", false ); 
      //bw_trace2( $tokens[$TCES], "tokens[TCES]", false );
      if ( is_array( $tokens[$TCES] ) ) {
        $tokens[$TCES][3] = retlink( "$context", $url, $tokens[$TCES][1], $title ); 
      } 
    }
  }
}

/**
 * Handle the hook
 *
 * The hook can either be a WordPress hook, one of "ours", or some other plugin's or theme's hook
 * We need to create a post for it regardless of whose it is.
 * If it happens to be a WordPress hook then we may eventually show the link to the WordPress documentation when viewing the hook's details. 
 * e.g. 
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/gettext
 * or 
 * http://codex.wordpress.org/Plugin_API/Action_Reference/plugins_loaded
 */ 
function oikai_handle_hook( $key, $value, &$tokens, $context, $doaction=true ) {
  $hook = oikai_concoct_hook_name2( $tokens );
  $post_id = _oikai_create_oik_hook( $hook, $context );
  if ( $post_id ) {
    //e( $hook['prehook'] );
    
    oikai_set_links( $post_id, $hook, $context, $tokens );
    //$token[] = retlink( "bw_hook $context", get_permalink( $post_id ), $hook['hook'] );
    //e( $hook['posthook'] ); 
    // wp_strip_all_tags( $hook )
  } else {
    //e( $value );
  }
  //e( $encaps );
  if ( $doaction ) {
    /**
     * Record this action/filter invocation for the current function
     */
    do_action( "oikai_record_hook", $context, $hook, $post_id );
  }
  
  bw_context( "dummy_TCES", false ); 
  oikai_set_context( $post_id );
  return( $post_id );
}

/**
 * Set/reset the context fields for this function
 *
 * Processing depends on the $value
 * When it's an action hook or filter invocation then we set "hook" 
 * When it's an association of a function to a hook then we set "add_"
 * When null we reset fields
 * Otherwise we set the "literal" 
 *
 * Notes: This can't handle nested filters / hooks
 *
 * @param string $value - the hook invoker, the hook association method, null or a literal 
 */
function oikai_set_context( $value=null ) {
  switch ( $value ) {
    case "apply_filters":
    case "apply_filters_ref_array":
    case "do_action":
    case "do_action_ref_array":
      bw_context( "hook", $value ); 
      bw_context( "add_", false );
      bw_context( "operator", false );
      bw_context( "variable", false );
      bw_context( "dummy_TCES", false );
      break;
      
    case "add_action":
    case "add_filter": 
      bw_context( "hook", false );
      bw_context( "add_", $value );
      bw_context( "operator", false );
      bw_context( "variable", false );
      break;
      
    case null:
      //br( "setting hook and add_ to false ");
      bw_context( "hook", false );
      bw_context( "add_", false );
      bw_context( "operator", false );
      bw_context( "variable", false );
        
    default:
      bw_context( "docblock", false );
      bw_context( "literal", $value );
  }   
}

/**
 * Handle the add_action() or add_filter() association
 * 
 * When it comes to invoking the action it doesn't matter what method is being used to perform the add
 * we just need to know which function is going to be invoked for the hook.
 *
 * The "add_" context changes as we process the filter or action
 * It starts as the function name being used to add the filter or action.
 * Then it becomes the post ID of the hook
 * When it's called with the post_id it means we now know the name of the function
 * that is being associated to the hook
 * oikai_concoct_hook_name2() was misused to return the previously stored function name
 * we now call oikai_concoct_api_name2() 
 * which oikai_handle_token_T_STRING() uses to determine the function name.
 * 
 * @param string $value - the string literal being processed
 * @param string $add_ - the value of the "add_" context 
 * 
 */
function oikai_handle_association( $key, $value, &$tokens, $add_ ) {
  //bw_trace2();
  switch( $add_ ) {
    case "add_filter":
    case "add_action":
      $post_id = oikai_handle_hook( $key, $value, $tokens, $add_, false );
      //bw_context( "add_", $value );
      bw_context( "add_", $post_id );
      break;
      
    case null:
      break; 
        
    default:  // The value of add_ is the post_id of the hook
      // we need to obtain the value for the function ( T_STRING )
      $func = oikai_concoct_api_name2( $tokens );
      //br( "handling $key,$value,$add_,#$func#" );
      if ( $func ) {
        $func = trim( $func, "\"'" );
        $post_id = oikai_handle_token_T_STRING( $key, $func, $tokens, false );
        /**
         * Record this action/filter hook association.
         *
         * @param string $add_ - the function name registering the action or filter hook
         * @param ID $post_id  - post ID of the function being associated
         */
        do_action( "oikai_record_association", $add_, $post_id );
      }  
  }    
}

/** 
 * Handle a T_CONSTANT_ENCAPSED_STRING token 
 *
 * Strings encaps(ulat)ed in single or double quotes
 * 
 * @param string $value - the constant encapsed string - with single or double quotes
 */ 
function oikai_handle_token_T_CONSTANT_ENCAPSED_STRING( $key, $token, &$tokens ) {
  //var_dump( $token );
  oikai_dummy_TCES( $key, $token[1], $tokens );
}
  
/**
 * Handle a 'dummy' T_CONSTANT_ENCAPSED_STRING token
 * 
 * Processing depends on the context
 * 
 * If the "hook" context is set then this is a call to the action or filter
 * so we need to link to that action or filter in order to see who implements it.
 * This may involve creating the hook.
 * 
 * If the "add_" context is set then this is a call to add an action or filter to invoke.
 * We don't yet concern ourselves about remove_action or remove_filter; we could deal with that later.
 * 
 * If there's no context then we're just handling a normal string.
 * 
 */  
function oikai_handle_dummy_TCES( $key, $value, &$tokens ) {
  oikai_dummy_TCES( $key, $value, $tokens );
  $context = bw_context( "hook" );
  $add_ = bw_context( "add_" );
  //br( __FUNCTION__ .  __LINE__ . " $key is $value,$context,$add_," );
  if ( $context ) {
    oikai_handle_hook( $key, $value, $tokens, $context );
  } elseif ( $add_ ) {
    oikai_handle_association( $key, $value, $tokens, $add_ );
  } 
}

/**
 * Save the token ids for later processing.
 *
 * TCES is an abbreviation for T_CONSTANT_ENCAPSED_STRING
 * 
 * Basically what we're trying to do here is build up the complete hook name
 * which may be specified with variables in string literals, or concatenation
 * It's not actually necessary to store each of the tokens.
 * BUT it doesn't really do any harm.
 *
 * 
 * @param integer $key the index of the token to add  
 * @param string $value the value of the token to add ( not used? may be null )
 * @param array $tokens the array of tokens ( not used? may be null )
 */
function oikai_dummy_TCES( $key, $value=null, $tokens=null ) {
  if ( $key ) {
    $dummy_TCES = bw_context( "dummy_TCES" );
    $dummy_TCES[] = $key;
  } else { 
    $dummy_TCES = array();
  }  
  bw_context( "dummy_TCES", $dummy_TCES );
  bw_trace2( $dummy_TCES, "dummy_TCES", false, BW_TRACE_DEBUG );
}

/**
 * Handle a T_ENCAPSED_AND_WHITESPACE token
 * 
 * This logic is needed to cater for 
 *
 *  add_action( "rab$foo", "rabfoo::fighters" );
 * 
 * What we want to do is build up a dummy T_CONSTANT_ENCAPSED_STRING then attempt to handle that.
 * We need to process each token from the ( to the ,
 * which may mean we need to alter the main looping through the tokens 
 * 
 * This logic also needs to cater for T_VARIABLE tokens
 * 
 */
function oikai_handle_token_T_ENCAPSED_AND_WHITESPACE( $key, $token, &$tokens ) {
  //e( $value );
  oikai_dummy_TCES( $key, $token, $tokens );
}

  
/**
 * Handle a T_STRING_VARNAME token ( e.g. ${post_type} )
 *
 * This logic is needed to cater for
 *
 *  add_filter( "manage_edit-${post_type}_columns", "${post_type_namify}_columns", 10, 2 );
 *
 * @see oikai_handle_token_T_ENCAPSED_AND_WHITESPACE
 *
 */
function oikai_handle_token_T_STRING_VARNAME( $key, $token, &$tokens ) {
  oikai_dummy_TCES( $key, $token, $tokens );
}
/** 
 * Handle a T_STRING token
 * 
 * Identifiers, e.g. keywords like parent and self, function names, class names and more are matched as T_STRING tokens.
 *
 * We treat just about every string as a possible function name.
 * So first of all we concoct the name that it could be using recently handled tokens.
 * We use oikai_determine_function_type() to find out if the API is a PHP function ( "internal" )
 * or one we might recognise or one of WordPress'es.
 * If it's one of ours then we'll also record the fact that this function calls this API.
 *
 * @TODO - But should this alter the context? $value = $post_id; ?
 * 
 * At the end of processing we reset the context.
 
 * 
 * @param string $value - the string token. Not expected to be quoted.  
 * @param bool $doaction - whether or not to invoke the action that adds this as a called function.
 * @return ID - post_id of the API if it's one of ours, or null
 */ 
function oikai_handle_token_T_STRING( $key, $token, &$tokens, $doaction=true  ) {
  bw_trace2( null, null, true, BW_TRACE_VERBOSE );
  if ( is_array( $token ) ) {
    $value = $token[1];
  } else { 
    $value = $token;
  } 
  oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
  $api_name = oikai_concoct_api_name( $value );
  //br( "handle T_STRING,$value,$key,$api_name," ); 
  $type = oikai_determine_function_type( $api_name ); 
  if ( $type == "internal" ) {
    $tokens[$key][3] = oikai_link_to_php( $api_name );
    $post_id = null;
  } else {
		if ( is_multisite() ) {
			$post_id = oikai_pragmatic_link_to_api( $key, $tokens, $api_name, $doaction, $type ); 
		} else {
			$post_id = oikai_link_to_local_site( $key, $tokens, $api_name, $doaction, $type, $value ); 
		}
  } 
  if ( $doaction ) {
    //br( "calling set_context with !$value! !$post_id!" );      
    oikai_set_context( $value ); 
  }  
  return( $post_id ); 
}

/**
 * Create a link to the local site
 * 
 * Use the old ( slower ) way of creating links to the API
 *
 * 
 */
function oikai_link_to_local_site( $key, &$tokens, $api_name, $doaction, $type, $value ) {
	$post_ids = oikai_get_oik_api_byname( $api_name );
	if ( $post_ids ) { 
		$post_id = bw_array_get( $post_ids, 0, null );   
		if ( $post_id ) {
			//$post_id = $post;
			//$value = $post_id;
			if ( is_array( $tokens[$key] ) ) {
				$tokens[$key][3] = retlink( null, oiksc_get_permalink( $post_id ), $tokens[$key][1], get_the_title( $post_id ) );
			} else {
				oikai_set_links( $post_id, $api_name, "API", $tokens );
			} 
			if ( $doaction ) {                                                              
				//do_action( __FUNCTION__, $post_id );      // @TODO - convert __FUNCTION__ to a literal somewhere! Herb 2013/10/24
				do_action( "oikai_handle_token_T_STRING", $post_id ); 
			}  
		}
	}	else {
		if ( $type == "user" ) {
			$tokens[$key][3] = oikai_link_to_wordpress( $value );
		} else {
			// br( "$value $api_name" );  // This used to contain " $type" as well. **?**
		}
		$post_id = null;
	}
	return( $post_id );
}

/**
 * Create a link to the API without accessing the database
 * 
 * This is a bit like the Wiki way. We assume the link is going to be valid
 * 
 * We don't know the post_id so we use the api_name instead
 * No need to link to WordPress separately as this should do.
 *
 * @param integer $key 
 * @param array $tokens
 * @param string $api_name 
 * @param bool $doaction true if the action should be invoked
 * @param string $type function type
 */
function oikai_pragmatic_link_to_api( $key, &$tokens, $api_name, $doaction, $type ) {
	//$url = site_url( "oik_api/" . $api_name );
	$url = "/oik_api/" . $api_name;
	if ( is_array( $tokens[$key] ) ) {
		$tokens[$key][3] = retlink( null, $url, $tokens[$key][1] );
	} else {
		oikai_set_pragmatic_links( $url, $api_name, "API", $tokens );
	} 
	if ( $doaction ) {                                                              
		//do_action( __FUNCTION__, $post_id );      // @TODO - convert __FUNCTION__ to a literal somewhere! Herb 2013/10/24
	 	do_action( "oikai_handle_token_T_STRING", $api_name ); 
	}
}
/**
 * Handle a T_DOC_COMMENT
 * When we see a T_DOC_COMMENT in a function then this is expected to precede
 * the first call to do_action() or apply_filters() and contains the definition of the action or filter. 
 * This information is added to the registration of the hook 
 *
 * Note: We ignore T_COMMENTs
 * 
 * 
 */
function oikai_handle_token_T_DOC_COMMENT( $key, $token, $tokens ) {
  bw_context( "docblock", $token[1] );
}

/**
 * Handle a T_OBJECT_OPERATOR ( -> )
 * 
 * We want to be able to create links for 
 *
 * $this->method();
 *
 * as a link to the API "$this::method" 
 * So first we must be able to detect that we're part of a class.
 * AND prepend the class name to the next token.
 * 
 * 
 */
function oikai_handle_token_T_OBJECT_OPERATOR( $key, $token, $tokens ) {
  bw_context( "operator", $token[1] );
}

 
/**
 * Handle a T_DOUBLE_ARROW ( => )
 *
 * This is used in array assignment. It should cancel out any "operator"
 */
function oikai_handle_token_T_DOUBLE_ARROW( $key, $token, $tokens ) {
  bw_context( "operator", false );
}


/**
 * Handle a T_DOUBLE_COLON ( :: )
 * 
 * We want to be able to create links for 
 *
 * class::method();
 *
 * as a link to the API "class::method" 
 * 
 * So first we must be able to detect that we're part of a class.
 * AND prepend the class name to the next token.
 * 
 * 
 */
function oikai_handle_token_T_DOUBLE_COLON( $key, $token, $tokens ) { 
  bw_context( "operator", $token[1] );
}

/**
 * Handle a T_ARRAY ( array ) 
 * 
 * Treat this as an operator for API name concocting purposes
 */
function oikai_handle_token_T_ARRAY( $key, $token, $tokens ) {
  bw_context( "operator", $token[1] );
}

/**
 * Handle a T_VARIABLE 
 * 
 * We need to be able to cater for 
 * 
 * add_action( "action", array( $this, "action_hook" ) );
 * 
 * Determining the function to link to as $this::action_hook
 * where $this is expected to be the class name of the calling function
 * 
 *
 */
function oikai_handle_token_T_VARIABLE( $key, $token, $tokens ) {
  //e( $value );
  bw_context( "variable", $token[1] );
  oikai_dummy_TCES( $key, $token, $tokens );
}

/**
 * Handle a T_FUNC_C 
 *
 * We detect __FUNCTION__ and convert this to a link to the current function
 * the name of which is stored in "func" context.
 *
 */
function oikai_handle_token_T_FUNC_C( $key, $token, &$tokens ) {
  // What's the current function name? 
  $func = bw_context( "func" );
  //e( $func );
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
  oikai_handle_token_T_STRING( $key, $func, $tokens );
}

/**
 * Match the function type, if it's a defined function
 * 
 * @param array $defined_functions - array of defined functions
 * @param string $type - the required function type: "internal"|"user"
 * @param string $funcname - the function name
 * @param string - the function type for the function, if it's defined 
 */
function oikai_query_function_type( $defined_functions, $type, $funcname ) {
  $set = bw_array_get( $defined_functions, $type, null );
  if ( $set ) {
    $assoc = bw_assoc( $set );
    $defined = bw_array_get( $assoc, $funcname, null );
    if ( !$defined ) {
      $type = null;
    }
  } else {
    $type = null;
  }
  return( $type );
}

/**
 * Determine the function type
 * 
 * @param string $funcname - the function name
 * @return string function type: "internal", "user" or original funcname
 */
function oikai_determine_function_type( $funcname ) {
  $defined_functions = get_defined_functions();
  $type = oikai_query_function_type( $defined_functions, "internal", $funcname );
  if ( !$type ) {
    $type = oikai_query_function_type( $defined_functions, "user", $funcname );
  }
  //e( "Type: $type " );
  return( $type );
} 

/**
 * Concoct an API name
 * 
 * Given the $value and some context concoct an API name
 * 
 * @TODO - cater for different values of $variable
 * - $this
 * - classname
 * - parent
 * - __CLASS__
 *
 * variable operator  concocted name
 * -------- --------  --------------
 * $this    ::        $class::$value
 * $this    ->        $class::$value
 *                    $value
 *
 * When we discover we don't have a class name then we strip the leading "::"
 *
 * Use the "o,v,c,l,v" trace record when debugging. 
 *
 * @param string $value - the function/method part of the name
 * @return string - the concocted API name
 */
function oikai_concoct_api_name( $value ) {
  $operator = bw_context( "operator" );
  $variable = bw_context( "variable" );
  $class = bw_context( "classname" );
  $literal = bw_context( "literal" );
  
  bw_trace2( "$operator,$variable,$class,$literal,$value,", "o,v,c,l,v", true, BW_TRACE_DEBUG );
  
  if ( $variable && $operator ) {
    //bw_trace2( $class, "class" );
    $api_name = "$class::$value";
  } elseif ( $literal && $operator ) { 
    //bw_trace2( $literal, "literal" );
    $api_name = "$literal::$value";
  } elseif ( $variable && $class ) {
    $api_name = str_replace( $variable, $class, $value ); 
  } else {
    $api_name = $value;
  }
  $api_name = ltrim( $api_name, ":" );
  bw_trace2( $api_name, "API_name", false, BW_TRACE_DEBUG );
  return ( $api_name );
}
              
/**
 * Handle this specific token
 *
 * If it exists we invoke a function generated from the token_name(),
 * passing the given parameters 
 *  
 * @param integer $key - the index to the $tokens array
 * @param array $token - token array - the actual token
 * @param array $tokens - the full tokens array
 */ 
function oikai_handle_token( $key, $token, &$tokens ) {
  $tn = token_name( $token[0] );
  $funcname = "oikai_handle_token_$tn";
  if ( function_exists( $funcname ) ) {
    //span( $tn );
    $funcname( $key, $token, $tokens );
    //epan();
  } else {
    //e( $token[1] );
  }
} 

/**
 * Handle this specific character 
 *
 * Call a function for a specific character
 * e.g. oikai_handle_token_comma - to be used to process parameters to add_action() etc.
 *
 */
function oikai_handle_char( $key, $char, &$tokens ) {
  $char_functions = array( "," => "comma" 
                         , ")" => "rparen"
                         , "(" => "lparen"
                         , '"' => "dquote"
                         , ';' => "semicolon"
                         , '=' => 'equals'
                         );
  $fn = bw_array_get( $char_functions, $char, null );
  if ( $fn ) {
    $funcname = "oikai_handle_char_$fn";
    if ( function_exists( $funcname ) ) {
      $funcname( $key, $char, $tokens );
    } else {
      oikai_handle_char_( $key, $char, $tokens );
    }
  } else {
    oikai_handle_char_( $key, $char, $tokens );
  }   
}

/**
 * Default handling for a character
 * 
 */
function oikai_handle_char_( $key, $char, &$tokens ) {
  oikai_dummy_TCES( $key, $char, $tokens );
}
 
/**
 * Handle a parameter to a hook definition or invocation
 * 
 * We only handle a comma when we're NOT handling an array.
 * $operator
 *
 */ 
function oikai_handle_char_comma( $key, $char, &$tokens ) {
  $hook = bw_context( "hook" );
  $add_ = bw_context( "add_" );
  $operator = bw_context( "operator" );
  bw_trace2( $operator, "operator", true, BW_TRACE_DEBUG );
  if ( $operator == "array" ) {
    // wait until we get to a ')' before we actually do something
    
  } else {
    // br( "Processing TCES,$key,$char,$hook,$add_," );
    if ( $hook || $add_ ) {
      //$value = bw_context( "dummy_TCES" );
      //if ( $value ) {
        $value = oikai_concoct_hook_name2( $tokens ); 
        //br( "value $value" );
        //oikai_handle_token_T_CONSTANT_ENCAPSED_STRING( $key, $value, $tokens );
        
        oikai_handle_dummy_TCES( $key, $value, $tokens );
        //oikai_handle_dummy_TCES( $value );
        if ( $hook ) {
          oikai_set_context();
        }  
      //}
    }
    bw_context( "dummy_TCES", false );
  }  
}

/**
 * Handle the last parameter to a hook definition or invocation 
 *
 * If we were processing the array 'operator' then we now turn that off
 * indicating that we're done with array logic processing.
 * Here we assume that we don't need to count open and closed parentheses.
 * 
 */
function oikai_handle_char_rparen( $key, $char, &$tokens ) {
  $operator = bw_context( "operator" );
  if ( "array" == $operator ) {
    bw_context( "operator", false );
  }
  oikai_handle_char_comma( $key, $char, $tokens );
}

/**
 * Handle a double quote ( " )
 *
 * @TODO - is this still necessary? 
 
 */  
function oikai_handle_char_dquote( $key, $char, &$tokens ) {
  oikai_dummy_TCES( $key, $char, $tokens );
}

/**
 * Handle the semicolon ( ; )
 *
 * Reset the content including the dummy TCES
 */
function oikai_handle_char_semicolon( $key, $char, &$tokens ) {
  bw_context( "dummy_TCES", false );
  oikai_set_context();
}

/**
 * Handle the equals sign token
 * 
 * Same logic as for a semi-colon
 */
function oikai_handle_char_equals( $key, $char, &$tokens ) {
  oikai_handle_char_semicolon( $key, $char, $tokens );
}

/**
 * Display the PHP source of a method or API
 * 
 * We need two passes of the tokens
 * The first pass sets any links and also detects hooks and hook associations
 * The second displays the results - with the links active.
 *
 * @TODO Improve the logic to strip unwanted leading tokens to either take into account the number of tokens to strip OR any new line characters in string literals
 *
 * 
 * @param array $tokens - the array of tokens 
 * @param string $start - the start line number ( future use )
 * @param bool $prepend_php - true if the <?php token has been automatically prepended and now we want to remove it.
 *
 */
function oikai_easy_tokens( $tokens, $start, $prepend_php ) {
  wp_enqueue_style( "oikai-tokens-css", oik_url( "css/oikai-tokens.css", "oik-shortcodes" ) );
  if ( $prepend_php ) {  
    array_shift( $tokens );
  }
  // @TODO Either strip the last token if it's T_WHITESPACE OR make sure we see it. Otherwise the line numbers can appear to be incorrect.
  // $count = count( $tokens );
  // $last = end( $tokens );
  //bw_trace2( $tokens, "pretokens1", false );
  //p( "Pass 1" );
  oikai_process_tokens1( $tokens );
  //bw_trace2( $tokens, "pretokens2", false );
  //p( "Pass 2" );
  oikai_process_tokens2( $tokens );
}

/**
 * First pass of tokens
 * 
 * Analyse the PHP tokens to attempt to discover hook associations and invocations
 * and any links we can make to PHP, WordPress or our own APIs
 * 
 * @param array $tokens - array of PHP tokens
 */
function oikai_process_tokens1( &$tokens ) {
  foreach ( $tokens as $key => $token ) {
    //e( $key );
    if ( is_array( $token ) ) {
      oikai_handle_token( $key, $token, $tokens ); 
    } else {
      //sepan( null, $token );
      oikai_handle_char( $key, $token, $tokens );
    }
  }
}

/**
 * Second pass of tokens
 *
 * Now write the code out with syntax highlighting and any generated links.
 * @TODO Decide which is best... having a blank line at the end of the source OR stripping any trailing whitespace token.
 *
 *  
 * @param array - the array of PHP tokens
 */
function oikai_process_tokens2( $tokens ) {
  stag( "pre" );
  foreach ( $tokens as $key => $token ) {
    if ( is_array( $token ) ) {
      oikai_handle_token2( $key, $token ); 
    } else {
      sepan( null, $token );
      //oikai_handle_char2( $token );
    }
  }
  e( "&nbsp;" );
  etag( "pre" );
}

/**
 * Display the output token, as a link if available
 * 
 * If there is a link then display this else display the token, 
 * calling esc_html() to prevent any inline HTML from being treated as HTML.
 *
 * Style the token by enclosing it in a span with class of the token name ( e.g. T_STRING ).
 * Use the token key for the span ID= parameter. Note that this may not be unique if more than one API is displayed on a page.
 *
 * @param int $key - token index
 * @param array $token - token array 
 */ 
function oikai_handle_token2( $key, $token ) {
	bw_trace2( null, null, true, BW_TRACE_DEBUG );
  $tn = token_name( $token[0] );
  span( $tn, $key );
  if ( isset( $token[3] ) ) {
    e( $token[3] );
  } elseif ( $token[0] == T_COMMENT ) {
		if ( strpos( $token[1], "/* function <a" ) === 0 ) {
			// Let this through unchanged
		} elseif ( strpos( $token[1], "/* class <a" ) === 0 ) {
		  // Let this through unchanged
		} else {	
	
			$token[1] = str_replace( '&', '&amp;', $token[1] );
			$token[1] = str_replace( '<', '&lt;', $token[1] );
			$token[1] = str_replace( '>', '&gt;', $token[1] );
		}
    e( $token[1] );
  } elseif ( $token[0] == T_WHITESPACE ) {
    e( str_replace( "\t", "  ", $token[1] ) ) ;
  } elseif ( $token[0] == T_DOC_COMMENT ) {
    e( "\n" );
    e( esc_html( str_replace( "\t", "",  $token[1] ) ) );
	} elseif ( $token[0] == T_CONSTANT_ENCAPSED_STRING ) {
		//$token[1] = esc_html( $token[1] );
		//$token[1] = htmlentities( $token[1] );
		$token[1] = str_replace( '&', '&amp;', $token[1] );
		$token[1] = str_replace( '<', '&lt;', $token[1] );
		$token[1] = str_replace( '>', '&gt;', $token[1] );
		e( $token[1] );
  } else { 
    e( esc_html( $token[1] ) );
  }
  epan();
} 

/**
 * Produce a nicely formatted version of the function's source code
 * 
 * Note: You can get sometimes get Warning messages when the source is truncated at an arbitrary line number
 * e.g.  Warning: Unterminated comment starting line 100 in \oik-shortcodes\shortcodes\oik-api-importer.php on line 1255
 * @TODO Eliminate this Warning notice.
 *  
 * @param array $sources - source lines for the function
 * @param int $startline - the start line of the function ( future use )  
 */
function oikai_syntax_source( $sources, $startline, $prepend_php=true ) {
  if ( $prepend_php ) {
    $content = "<?php\n";
  } else {
    $content = null;
  }  
  $content .= implode( "", $sources );
  // bw_trace2();
  $tokens = token_get_all( $content );
  if ( !$prepend_php ) {
    //array_pop( $tokens );
  }
  if ( count( $tokens ) ) { 
    oikai_easy_tokens( $tokens, $startline, $prepend_php );
  }  
}

/**
 * Return the API or method name given the full funcname and classname 
 *
 * @TODO Question: Should we raise a concern if the classname doesn't match what we find in funcname?
 *
 * @param string $funcname - which may be in form class::method
 * @param string $classname - alternative class name, which may be null
 * @return string - the API or method name excluding the class and double colon operator  
 * 
 */
function oikai_get_func( $funcname, $classname ) { 
  if ( $classname ) {
    $func = str_replace( "$classname::", "", $funcname );
  } else { 
    $func = $funcname; 
  }
  $pos = strpos( $funcname, "::" );
  if ( $pos !== false ) {
    $func = substr( $funcname, $pos+2 );  
  }
  return( $func );
}

/** 
 * Return the class name for this method
 *
 * Given the funcname and classname determine the most appropriate class name
 * @param string $funcname - which may be in form class::method
 * @param string $classname - alternative class name, which may be null
 * @return string - the class name  
 */
function oikai_get_class( $funcname, $classname=null ) {
  if ( $funcname ) {
    $pos = strpos( $funcname, "::" );
  } else { 
    $pos = false;  
  }    
  if ( $pos !== false ) {
    $class = substr( $funcname, 0, $pos );
  } else {
    $class = $classname;
  }
  return( $class ); 
}

/** 
 * Query class from class name
 *
 * @TODO If WP-Parser has been run then we may also be able to find the class from the WP-Parser CPT
 *
 * @param string $class - class name
 * @return $post    
*/
function oikai_get_oik_class_byname( $class ) {
  oik_require( "includes/bw_posts.inc" ); 
  $atts = array();
  $atts['post_type'] = "oik_class" ;
  $atts['numberposts'] = 1; 
  $atts['meta_key'] = "_oik_class_name";
  $atts['meta_value'] = $class;
  $posts = bw_get_posts( $atts ); 
  $post = bw_array_get( $posts, 0, null );
  bw_trace2( $post, "oik_class?", true, BW_TRACE_VERBOSE );
  return( $post );
}
 
/**
 * Return the classref for the API, if applicable, creating the oik_class if necessary
 * 
 * @param string $funcname 
 * @param string $class
 * @param ID $plugin
 * @param string $file
 * @return post_id - post ID of the oik_class post
 */
function oikai_get_classref( $funcname, $class, $plugin, $file ) {
  $class = oikai_get_class( $funcname, $class );
  if ( $class ) {
    $post = oikai_get_oik_class_byname( $class );
    if ( !$post ) {
      $post_id = oikai_create_oik_class( $class, $plugin, $file );
    } else {
      oikai_update_oik_class( $post, $class, $plugin, $file );
      $post_id = $post->ID;
    }
  } else {
    $post_id = null;
  }
  return( $post_id ); 
} 

/**
 * Return the title for an oik_class post
 *
 * Currently this is just the class name
 *
 * @param string $class - the class name
 * @return string - the title 
 */
function oikai_oik_class_post_title( $class ) {
  $title = $class;
  return( $title );
}

/**
 * Return this class'es parent ID
 *
 * @param string $classs
 * @param string $plugin
 * @param string $file
 * @return ID - the parent class ID or null
 */
function oikai_oik_class_parent( $class, $plugin, $file ) {
  oik_require( "classes/oik-listapis2.php", "oik-shortcodes" );
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
  $parent_id = 0;
  oik_require( "admin/oik-apis.php", "oik-shortcodes" );
  
  $plugin_name = _oiksc_get_plugin();
  $component_type = oiksc_query_component_type( $plugin );
  $filename = oik_pathw( $file, $plugin, $component_type );
  // $filename = oik_path( $file, $plugin_name );
  if ( file_exists( $filename ) ) {
    $functions = oiksc_list_file_functions2( $filename, $component_type );
    $class_obj = oiksc_find_function( $functions, null, $class );
    $parent_class = $class_obj->extended;
    bw_trace2( $parent_class, "parent_class", false, BW_TRACE_DEBUG );
    if ( $parent_class ) {
      $parent_class_obj = oiksc_find_function( $functions, null, $parent_class );
      if ( $parent_class_obj ) {
        $parent_id = oikai_get_classref( null, $parent_class, $plugin, $file );
      } else {
        $parent_id = oikai_get_classref( null, $parent_class, $plugin, null );
      }
    }
  } else {
    bw_trace2( $filename, "File does not exist", true, BW_TRACE_ERROR );
  }  
  return( $parent_id );
}

/**
 * Programmatically create an oik_class record for a selected plugin's method
 *
 * We don't set any content. This is automatically generated during 'the_content' filter processing.
 *
 * @param string $class - the class name
 * @param ID $plugin - the ID of the plugin for which this oik_class is being created
 * @param string $file - the implementing file
 * @return ID - the post ID of the newly created oik_class record
 */
function oikai_create_oik_class( $class, $plugin, $file ) {
  //bw_backtrace();
  $post_title = oikai_oik_class_post_title( $class );
  $post = array( 'post_type' => 'oik_class'
               , 'post_title' => $post_title
               , 'post_name' => $class
               , 'post_content' => "<!--more -->"
               , 'post_status' => 'publish'
               );
  $post['post_parent'] = oikai_oik_class_parent( $class, $plugin, $file );             
  /* Set metadata fields */
  $_POST['_oik_class_name'] = $class;
  $_POST['_oik_api_plugin'] = $plugin;
  $_POST['_oik_api_source'] = $file;
  oik_require( "admin/oik-files.php", "oik-shortcodes" );
  $_POST['_oik_fileref'] = oiksc_get_oik_fileref( $plugin, $file );
  $post_id = wp_insert_post( $post, TRUE );
  bw_trace2( $post_id, "post_id", true, BW_TRACE_DEBUG );
  return( $post_id );
}

/**
 * Programmatically update the oik_class record
 *
 * This function is required to correct the _oik_fileref field which may have been inadvertently
 * set to a serialised post object rather than a simple post id.
 * 
 * @param object $post - a post object
 * @param string $class - the class name
 * @param ID $plugin - the implementing plugin ID
 * @param string $file - the filename.
 */
function oikai_update_oik_class( $post, $class, $plugin, $file ) {
  oik_require( "admin/oik-files.php", "oik-shortcodes" );
  $_POST['_oik_fileref'] = oiksc_get_oik_fileref( $plugin, $file );
  wp_update_post( $post );
}  

/**
 * Automatically create the API reference 
 *
 * Create the API reference information for:
 * - Short and long description
 * - Usage
 * - Parameters
 * - Return
 * - TO DO(s) 
 * - Sourcefile
 * 
 * Then show the dynamically generated Syntax
 *
 * @param string $funcname - the function name - @TODO which may be classname::funcname
 * @param string $sourcefile - the source file
 * @param string $plugin_slug - the plugin slug
 * @param string $classname - the class name for a method
 * @param ID $post_id - the post ID for the "api" 
 * @param bool $echo true if we actually want the source to be listed
 * @param ID $plugin_id - the post ID for the plugin or theme
 */
function oikai_build_apiref( $funcname, $sourcefile=null, $plugin_slug="oik", $classname=null, $post_id, $echo=true, $plugin_id ) {
	$func = oikai_get_func( $funcname, $classname );
	bw_context( "func", $func );
	$class = oikai_get_class( $funcname, $classname );
	bw_context( "classname", $class );
	// p( "Class: $class" );
	$refFunc = oikai_pseudo_reflect( $func, $sourcefile, $plugin_slug, $class );
	if ( $refFunc ) {
		if ( $echo ) {
			$docblock = oikai_reflect_docblock( $refFunc );
			oikai_reflect_descriptions( $docblock );
			oikai_reflect_usage( $refFunc, $docblock, $funcname );
			oikai_reflect_parameters( $refFunc, $docblock );
			oikai_print_return( $refFunc, $docblock );
			oikai_print_todos( $refFunc, $docblock );
			oikai_reflect_filename( $refFunc, $sourcefile, $plugin_slug );
			// bw_flush();
			//bw_push();
		}
		oik_require( "admin/oik-apis.php", "oik-shortcodes" );
		$component_type = oiksc_query_component_type( $plugin_slug );
		$source = oikai_listsource( $refFunc, $post_id, $plugin_slug, $component_type, $echo ); 
		oikai_external_links( $sourcefile, $plugin_slug, $post_id, $plugin_id, $refFunc );
	} else { 
		p( "No API information available for: " . $funcname );
	}  
}

/**
 * Build some dynamic documentation from the embedded content 
 *
 * With a bit of luck and a following wind, as Kieran O'Shea once put it...
 *
 * @TODO Determine how much of this pre-processing is still needed
 * 
 * @param string $content - some PHP source to dynamically format
 */ 
function oikai_build_dynamic_docs( $content ) {
  $ent = ncr2ent( $content );
  $dec = htmlspecialchars_decode( $ent );
  $dec = str_replace( "&lsquo;", "'", $dec );
  $dec = str_replace( "&rsquo;", "'", $dec );
  $dec = str_replace( "&ldquo;", '"', $dec );
  $dec = str_replace( "&rdquo;", '"', $dec );
	// Can we get away with not doing this if we now run autop after?
  //$dec = str_replace( "<br />", "", $dec );
  //$dec = str_replace( "<p>", "", $dec );
  //$dec = str_replace( "</p>", "", $dec );
  $parseme  = "<?php ";
  $parseme .= $dec;
  $startline = 1;
  bw_trace2( $parseme, "parseme", true, BW_TRACE_DEBUG );
  $tokens = token_get_all( $parseme );
	bw_trace2( $tokens, "tokens", false, BW_TRACE_DEBUG );
  oikai_easy_tokens( $tokens, $startline, true );
}

/**
 * Get the string value for a noderef
 *
 * @TODO This is equivalent to a function like get_fieldref(); which would be in oik-fields
 *
 * @param $post_id - post ID of the post with the noderef
 * @param $noderef_name - the field name for the noderef field
 * @param $field_name - the name of the field in the referenced node
 * @return string - the value of noderef.field
 */
function oikai_get_noderef_value( $post_id, $noderef_name, $field_name ) {
  $node_id = get_post_meta( $post_id, $noderef_name, true );
  if ( $node_id ) {
    $value = get_post_meta( $node_id, $field_name, true );
  } else { 
    $value = null;
  }  
  return( $value );
}

/**
 * Implement the [bw_api] shortcode
 * 
 * When coded as [bw_api]some php code[/bw_api] then this shortcode produces dynamic API documentation
 * When the funcname parameter is not specified then we assume this is being invoked for current post - which is an API
 * If we find the funcname (and sourcefile and plugin) then we can build the apiref
 * including the callers and callees  
 * 
 * @param array $atts { shortcode parameters
 *  @type string $funcname the API function name
 *                         which may be class::method ?  
 *  @type string $sourcefile the implementing sourcefile
 *  @type string $plugin the implementing plugin
 * }
 * @param string $content - PHP source to be dynamically documented
 * @param string $tag - the shortcode name
 * @return string - generated HTML
 */
function oikai_apiref( $atts=null, $content=null, $tag=null ) {

	oiksc_autoload();

  $funcname = bw_array_get( $atts, "funcname", null );
  $sourcefile = bw_array_get( $atts, "sourcefile", null );
  $plugin = bw_array_get( $atts, "plugin", null );
  $classname = null;
	$post_id = null;
  if ( $content ) {
    oikai_build_dynamic_docs( $content );
  } else { 
    if ( !$funcname ) {
      oik_require( "includes/bw_posts.inc" ); 
      $post_id = bw_global_post_id(); 
      bw_trace2( $post_id, "post_id", true, BW_TRACE_VERBOSE ); 
      $sourcefile = get_post_meta( $post_id, "_oik_api_source", true );
      if ( $sourcefile ) {
        $plugin_id = get_post_meta( $post_id, "_oik_api_plugin", true );
        
        if ( $plugin_id ) {
          $plugin = get_post_meta( $plugin_id, "_oikp_slug", true );
          // It may be a theme so try for _oikth_slug
          if ( !$plugin ) {
            $plugin = get_post_meta( $plugin_id, "_oikth_slug", true );
          }
          if ( $plugin ) { 
            $funcname = get_post_meta( $post_id, "_oik_api_name", true );
            $classname = oikai_get_noderef_value( $post_id, "_oik_api_class", "_oik_class_name" );              
            //$title = get_the_title();
            //$title = str_replace( "(", " ", $title );
            //$title = str_replace( ")", " ", $title );
            //$title = str_replace( array( "(",")","-"), " ", $title );
            //list( $funcname, $rest) = explode( " ", $title. " .", 2 );
          } else {
            e( "Plugin not found for oik_api" );
          }
        } else {
          e( "Plugin not defined for this API" );
        }  
      } else {
        e( "Sourcefile not defined for $post_id" );
      }  
    }  
    if ( $funcname ) {
      oikai_build_apiref( $funcname, $sourcefile, $plugin, $classname, $post_id, true, $plugin_id );
      if ( $post_id ) { 
        oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
        oikai_list_callers_callees( $post_id );
        do_action( "bw_metadata", $post_id );
      }  
    } else {
      e( "bw_api cannot determine the funcname" );
    }
  }
  return( bw_ret() );  
}


/** 
 * Help hook for [bw_api] shortcode
 */
function bw_api__help() {
  return( "Dynamic API syntax help" );
}

/**
 * Syntax hook for [bw_api] shortcode
 *
 * @TODO - check if we can actually support these parameters
 */
function bw_api__syntax( $shortcode="bw_api" ) {
  $syntax = array( "funcname" => bw_skv( null, "<i>function</i>" , "Name of the function" )
                 , "sourcefile" => bw_skv( null, "<i>sourcefile</i>", "Sourcefile containing the function" )
                 , "plugin" => bw_skv( null, "<i>plugin</i>", "Implementing plugin slug" )
                 );
  return( $syntax ); 
}

/**
 * Converts named entities into numbered entities.
 *
 * @since 1.5.1
 *
 * @param string $text The text within which entities will be converted.
 * @return string Text with converted entities.
 */
function ncr2ent($text) {

	$to_ncr = array(
		'&quot;' => '&#34;',
		'&amp;' => '&#38;',
		'&frasl;' => '&#47;',
		'&lt;' => '&#60;',
		'&gt;' => '&#62;',
		'|' => '&#124;',
		'&nbsp;' => '&#160;',
		'&iexcl;' => '&#161;',
		'&cent;' => '&#162;',
		'&pound;' => '&#163;',
		'&curren;' => '&#164;',
		'&yen;' => '&#165;',
		'&brvbar;' => '&#166;',
		'&brkbar;' => '&#166;',
		'&sect;' => '&#167;',
		'&uml;' => '&#168;',
		'&die;' => '&#168;',
		'&copy;' => '&#169;',
		'&ordf;' => '&#170;',
		'&laquo;' => '&#171;',
		'&not;' => '&#172;',
		'&shy;' => '&#173;',
		'&reg;' => '&#174;',
		'&macr;' => '&#175;',
		'&hibar;' => '&#175;',
		'&deg;' => '&#176;',
		'&plusmn;' => '&#177;',
		'&sup2;' => '&#178;',
		'&sup3;' => '&#179;',
		'&acute;' => '&#180;',
		'&micro;' => '&#181;',
		'&para;' => '&#182;',
		'&middot;' => '&#183;',
		'&cedil;' => '&#184;',
		'&sup1;' => '&#185;',
		'&ordm;' => '&#186;',
		'&raquo;' => '&#187;',
		'&frac14;' => '&#188;',
		'&frac12;' => '&#189;',
		'&frac34;' => '&#190;',
		'&iquest;' => '&#191;',
		'&Agrave;' => '&#192;',
		'&Aacute;' => '&#193;',
		'&Acirc;' => '&#194;',
		'&Atilde;' => '&#195;',
		'&Auml;' => '&#196;',
		'&Aring;' => '&#197;',
		'&AElig;' => '&#198;',
		'&Ccedil;' => '&#199;',
		'&Egrave;' => '&#200;',
		'&Eacute;' => '&#201;',
		'&Ecirc;' => '&#202;',
		'&Euml;' => '&#203;',
		'&Igrave;' => '&#204;',
		'&Iacute;' => '&#205;',
		'&Icirc;' => '&#206;',
		'&Iuml;' => '&#207;',
		'&ETH;' => '&#208;',
		'&Ntilde;' => '&#209;',
		'&Ograve;' => '&#210;',
		'&Oacute;' => '&#211;',
		'&Ocirc;' => '&#212;',
		'&Otilde;' => '&#213;',
		'&Ouml;' => '&#214;',
		'&times;' => '&#215;',
		'&Oslash;' => '&#216;',
		'&Ugrave;' => '&#217;',
		'&Uacute;' => '&#218;',
		'&Ucirc;' => '&#219;',
		'&Uuml;' => '&#220;',
		'&Yacute;' => '&#221;',
		'&THORN;' => '&#222;',
		'&szlig;' => '&#223;',
		'&agrave;' => '&#224;',
		'&aacute;' => '&#225;',
		'&acirc;' => '&#226;',
		'&atilde;' => '&#227;',
		'&auml;' => '&#228;',
		'&aring;' => '&#229;',
		'&aelig;' => '&#230;',
		'&ccedil;' => '&#231;',
		'&egrave;' => '&#232;',
		'&eacute;' => '&#233;',
		'&ecirc;' => '&#234;',
		'&euml;' => '&#235;',
		'&igrave;' => '&#236;',
		'&iacute;' => '&#237;',
		'&icirc;' => '&#238;',
		'&iuml;' => '&#239;',
		'&eth;' => '&#240;',
		'&ntilde;' => '&#241;',
		'&ograve;' => '&#242;',
		'&oacute;' => '&#243;',
		'&ocirc;' => '&#244;',
		'&otilde;' => '&#245;',
		'&ouml;' => '&#246;',
		'&divide;' => '&#247;',
		'&oslash;' => '&#248;',
		'&ugrave;' => '&#249;',
		'&uacute;' => '&#250;',
		'&ucirc;' => '&#251;',
		'&uuml;' => '&#252;',
		'&yacute;' => '&#253;',
		'&thorn;' => '&#254;',
		'&yuml;' => '&#255;',
		'&OElig;' => '&#338;',
		'&oelig;' => '&#339;',
		'&Scaron;' => '&#352;',
		'&scaron;' => '&#353;',
		'&Yuml;' => '&#376;',
		'&fnof;' => '&#402;',
		'&circ;' => '&#710;',
		'&tilde;' => '&#732;',
		'&Alpha;' => '&#913;',
		'&Beta;' => '&#914;',
		'&Gamma;' => '&#915;',
		'&Delta;' => '&#916;',
		'&Epsilon;' => '&#917;',
		'&Zeta;' => '&#918;',
		'&Eta;' => '&#919;',
		'&Theta;' => '&#920;',
		'&Iota;' => '&#921;',
		'&Kappa;' => '&#922;',
		'&Lambda;' => '&#923;',
		'&Mu;' => '&#924;',
		'&Nu;' => '&#925;',
		'&Xi;' => '&#926;',
		'&Omicron;' => '&#927;',
		'&Pi;' => '&#928;',
		'&Rho;' => '&#929;',
		'&Sigma;' => '&#931;',
		'&Tau;' => '&#932;',
		'&Upsilon;' => '&#933;',
		'&Phi;' => '&#934;',
		'&Chi;' => '&#935;',
		'&Psi;' => '&#936;',
		'&Omega;' => '&#937;',
		'&alpha;' => '&#945;',
		'&beta;' => '&#946;',
		'&gamma;' => '&#947;',
		'&delta;' => '&#948;',
		'&epsilon;' => '&#949;',
		'&zeta;' => '&#950;',
		'&eta;' => '&#951;',
		'&theta;' => '&#952;',
		'&iota;' => '&#953;',
		'&kappa;' => '&#954;',
		'&lambda;' => '&#955;',
		'&mu;' => '&#956;',
		'&nu;' => '&#957;',
		'&xi;' => '&#958;',
		'&omicron;' => '&#959;',
		'&pi;' => '&#960;',
		'&rho;' => '&#961;',
		'&sigmaf;' => '&#962;',
		'&sigma;' => '&#963;',
		'&tau;' => '&#964;',
		'&upsilon;' => '&#965;',
		'&phi;' => '&#966;',
		'&chi;' => '&#967;',
		'&psi;' => '&#968;',
		'&omega;' => '&#969;',
		'&thetasym;' => '&#977;',
		'&upsih;' => '&#978;',
		'&piv;' => '&#982;',
		'&ensp;' => '&#8194;',
		'&emsp;' => '&#8195;',
		'&thinsp;' => '&#8201;',
		'&zwnj;' => '&#8204;',
		'&zwj;' => '&#8205;',
		'&lrm;' => '&#8206;',
		'&rlm;' => '&#8207;',
		'&ndash;' => '&#8211;',
		'&mdash;' => '&#8212;',
		'&lsquo;' => '&#8216;',
		'&rsquo;' => '&#8217;',
		'&sbquo;' => '&#8218;',
		'&ldquo;' => '&#8220;',
		'&rdquo;' => '&#8221;',
		'&bdquo;' => '&#8222;',
		'&dagger;' => '&#8224;',
		'&Dagger;' => '&#8225;',
		'&bull;' => '&#8226;',
		'&hellip;' => '&#8230;',
		'&permil;' => '&#8240;',
		'&prime;' => '&#8242;',
		'&Prime;' => '&#8243;',
		'&lsaquo;' => '&#8249;',
		'&rsaquo;' => '&#8250;',
		'&oline;' => '&#8254;',
		'&frasl;' => '&#8260;',
		'&euro;' => '&#8364;',
		'&image;' => '&#8465;',
		'&weierp;' => '&#8472;',
		'&real;' => '&#8476;',
		'&trade;' => '&#8482;',
		'&alefsym;' => '&#8501;',
		'&crarr;' => '&#8629;',
		'&lArr;' => '&#8656;',
		'&uArr;' => '&#8657;',
		'&rArr;' => '&#8658;',
		'&dArr;' => '&#8659;',
		'&hArr;' => '&#8660;',
		'&forall;' => '&#8704;',
		'&part;' => '&#8706;',
		'&exist;' => '&#8707;',
		'&empty;' => '&#8709;',
		'&nabla;' => '&#8711;',
		'&isin;' => '&#8712;',
		'&notin;' => '&#8713;',
		'&ni;' => '&#8715;',
		'&prod;' => '&#8719;',
		'&sum;' => '&#8721;',
		'&minus;' => '&#8722;',
		'&lowast;' => '&#8727;',
		'&radic;' => '&#8730;',
		'&prop;' => '&#8733;',
		'&infin;' => '&#8734;',
		'&ang;' => '&#8736;',
		'&and;' => '&#8743;',
		'&or;' => '&#8744;',
		'&cap;' => '&#8745;',
		'&cup;' => '&#8746;',
		'&int;' => '&#8747;',
		'&there4;' => '&#8756;',
		'&sim;' => '&#8764;',
		'&cong;' => '&#8773;',
		'&asymp;' => '&#8776;',
		'&ne;' => '&#8800;',
		'&equiv;' => '&#8801;',
		'&le;' => '&#8804;',
		'&ge;' => '&#8805;',
		'&sub;' => '&#8834;',
		'&sup;' => '&#8835;',
		'&nsub;' => '&#8836;',
		'&sube;' => '&#8838;',
		'&supe;' => '&#8839;',
		'&oplus;' => '&#8853;',
		'&otimes;' => '&#8855;',
		'&perp;' => '&#8869;',
		'&sdot;' => '&#8901;',
		'&lceil;' => '&#8968;',
		'&rceil;' => '&#8969;',
		'&lfloor;' => '&#8970;',
		'&rfloor;' => '&#8971;',
		'&lang;' => '&#9001;',
		'&rang;' => '&#9002;',
		'&larr;' => '&#8592;',
		'&uarr;' => '&#8593;',
		'&rarr;' => '&#8594;',
		'&darr;' => '&#8595;',
		'&harr;' => '&#8596;',
		'&loz;' => '&#9674;',
		'&spades;' => '&#9824;',
		'&clubs;' => '&#9827;',
		'&hearts;' => '&#9829;',
		'&diams;' => '&#9830;'
	);

	return str_replace( array_values($to_ncr), array_keys($to_ncr), $text );
}

/**
 * Return a permalink
 *
 * @param ID $post_id 
 * @return string permalink - with or without the home_url prefix
 */
function oiksc_get_permalink( $post_id=null ) {
	if ( oiksc_autoload() ) {
		$link_map = oiksc_link_map::instance();
		bw_trace2( $link_map, "link-map", true, BW_TRACE_VERBOSE );
		$permalink = $link_map->get_permalink( $post_id );
	}	else {
		$permalink = get_permalink( $post_id );
	}
	return( $permalink );
}

/**
 * Display external links
 * 
 * If the plugin or theme is on GitHub we can create a 'View on GitHub' link
 * @TODO If the plugin or theme is on WordPress.org we can create a 'View on Trac' link 
 *
 * @param string $sourcefile
 * @param string $plugin_slug 
 * @param ID $post_id post ID
 * @param ID $plugin_id plugin/theme ID
 * @param object|null $refFunc 
 */ 															
function oikai_external_links( $sourcefile, $plugin_slug, $post_id, $plugin_id, $refFunc) {
	bw_trace2( null, null, true, BW_TRACE_VERBOSE );
	oikai_link_to_github( $sourcefile, $plugin_slug, $post_id, $plugin_id, $refFunc ); 
	oikai_link_to_trac( $sourcefile, $plugin_slug, $post_id, $plugin_id, $refFunc );
}

/**
 * Display "View on GitHub" link
 * 
 * If the plugin or theme is on GitHub we can create a 'View on GitHub' link
 * 
 * For an API we need to find the line number.
 *
 * General format for $giturl is `https://github.com/$owner/$repository/blob/$branch/$sourcefile`
 * 
 * Note: $gitrepo represents $owner/$repository
 
 * Type | Link
 * ---- | ------
 * API  | $giturl#Lnnnnn where nnnnn is the start line  
 
 * @param string $sourcefile
 * @param string $plugin_slug 
 * @param ID $post_id post ID
 * @param ID $plugin_id plugin/theme ID
 * @param object|null $refFunc 
 */
function oikai_link_to_github( $sourcefile, $plugin_slug, $post_id, $plugin_id, $refFunc ) {
	$gitrepo = get_post_meta( $plugin_id, "_oikp_git", true );
	if ( $gitrepo ) {
		if ( $refFunc ) {
			$startline = $refFunc->getStartLine();
		} else {
			$startline = 1;
		}
		$giturl = "https://github.com/$gitrepo/blob/master/$sourcefile#L$startline";
		e( "&nbsp;" );
		alink( "github", $giturl, "View on GitHub" );
	}
}

/**
 * View on Trac
 *
 * If the plugin or theme is in WordPress TRAC then we should be able to view it on TRAC
 *
 * General format for WordPres core is:
 * 
 * `https://core.trac.wordpress.org/browser/tags/4.5/src/wp-includes/post.php#L1828`
 * 
 * For plugins we can just try the trunk version
 *
 * https://plugins.trac.wordpress.org/allow-reinstalls/trunk
 *
 * But for themes, trunk doesn't appear to exist so we need the version
 * 
 * https://themes.trac.wordpress.org/browser/$theme/$version/
 * 
 *
 * Determine root URL based on the plugin or theme type or name
 *
 *
 * @param string $sourcefile
 * @param string $plugin_slug - plugin or theme slug 
 * @param ID $post_id post ID
 * @param ID $plugin_id plugin/theme ID
 * @param object $refFunc 
 */
function oikai_link_to_trac( $sourcefile, $plugin_slug, $post_id, $plugin_id, $refFunc ) {
	$url = null;
	$plugin_type = get_post_meta( $plugin_id, "_oikp_type", true );
	$theme_type = null;
	if ( '' !== $plugin_type ) {
		switch ( $plugin_type ) {
			case '0':
				global $wp_version;
				$url = "https://core.trac.wordpress.org/browser/tags/$wp_version/src/";
				break;
				
			case '1':
			case '6': 
				$url = "https://plugins.trac.wordpress.org/browser/$plugin_slug/trunk/";
				break;
				
			case '2':
			case '3':
			case '4':
			case '5':
				break;
				
			default: 
				bw_trace2( $plugin_type, "plugin_type", true );
		}	
	} else {
		$theme_type = get_post_meta( $plugin_id, "_oikth_type", true );
		switch ( $theme_type ) {
			case '1':
			case '6':
				oik_require( "shortcodes/oik-component-version.php", "oik-shortcodes" );
				$version = oik_query_component_version( $post_id );
				$url = "https://themes.trac.wordpress.org/browser/$plugin_slug/$version/";
				break;
		}
	}
	if ( $url ) {
		if ( $refFunc ) {
			$startline = $refFunc->getStartLine();
		} else {
			$startline = 1;
		}
		$url .= "$sourcefile#L$startline";
		e( "&nbsp;" );
		alink( "svn", $url, "View on Trac" );	
		
	} else {
    bw_trace2( $plugin_type, "Plugin type", true );
		bw_trace2( $theme_type, "Theme type", false );
	}
}
		

 

