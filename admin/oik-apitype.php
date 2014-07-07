<?php // (C) Copyright Bobbing Wide 2014


/**
 * Determine the api type 
 * 
 * Determine if the function implements an action, filter, shortcode OR other dynamic API type such as shortcode help, shortcode syntax, shortcode example
 * AND if so the value(s) that it implements
 * Note: It's not expected to implement more than one type BUT can implement it for more than one of them.  e.g. filter and action
 * If an API implements a filter and an action then it should be classified as a filter.
 * 
 * We use the following methods to detect the API type:
 * 
 * 1. docblock short description: Implements "name" type for etc where type is "action", "filter" or "shortcode" and name is the name
 * 2. parms are $atts, $content, $tag then it's a "shortcode"
 * 3. funcname contains end's with "__blah" then it's a "hook" 
 * 4. funcname starts with _ - suggests it's private
 * 
 */
function oikai_api_type( $funcname, $refFunc, $docblock  ) {
  $type = null;
  $desc = oikai_choose_desc( $docblock, $funcname );
  if ( $type = oikai_function_implements( $desc ) ) {
    // We've determined the type from the Description and it's already in an array
  } elseif ( $type[0] = oikai_parm_match( $funcname, $refFunc, $docblock ) ) {
    // We've determined the type from the parameters
  } elseif ( $type[0] = oikai_funcname_analysis( $funcname ) ) { 
    // We've determined the type from the funcname 
  } elseif ( $funcname[0] == '_' ) {
      $type[0] = "private";
  } else {
    $type[0] = "";
  }  
  $type[2] = $desc;
  return( $type );
  
}

/**
 * Decide what to use for the "Description"
 * 
 */ 
function oikai_choose_desc( $docblock, $funcname ) {
  $ld = $docblock->getLongDescription();
  $sd = $docblock->getShortDescription();
  if ( $ld ) {
    if ( $sd ) {
      $desc = $sd;
    } else {
      $desc = $ld; 
    }
  } else { 
    if ( $sd ) {
      $desc = $sd;
    } else {
      $desc = oikai_reverse_engineer_funcname( $funcname );
    }  
  }
  $desc = oikai_first_line( $desc );
  return( $desc );
}

/**
 * The end of the first line is detected by a newline character (\n)
 * NOT a carriage return (\r)
 * NOT PHP_EOL; 
 */
function oikai_first_line( $paragraph ) {
  //$ps = explode( PHP_EOL, $paragraph, 2 );
  $ps = explode( "\n", $paragraph, 2 ) ;
  //echo count( $ps );
  return( $ps[0] );
} 

/**
 * Given a funcname attempt to produce a short description for the function
 *
 * How do we go about removing the prefix?
 *
 */
function oikai_reverse_engineer_funcname( $funcname ) {
  $desc = str_replace( "_", " ", $funcname );
  $desc = str_replace( "  ", " ", $desc );
  return( $desc );
}

/**
 * Return information about the function implementing something
 *
 * 
 * Works for:
 *  Implement "blah" action
 *  Implements the "blah" filter
 *  Implements [bw_code] shortcode
 *
 * @param string $desc - the function description
 * @return array|null - $type[0] should contain action, filter or shortcode, $type[1] is the name of what's being implemented
 *             
 */
function oikai_function_implements( $desc ) {
  $desc = str_replace( " the ", " ", $desc );
  $desc = str_replace( "Implements ", "Implement ", $desc );
  $descs = explode( " ", $desc . " 1 2 3 4 ", 4 );
  $implements = strcasecmp( "Implement", $descs[0] );
  if ( 0 == $implements  ) {
    $type[0] = $descs[2];
    $type[1] = $descs[1];
  } else {
    $type = null;
  }
  return( $type );  
}
  

/**
 * Attempt to determine the API type based on the parameters 
 *
 * $atts=null, $content=null, $tag=null -> "shortcode"
 * 
 *
 */
function oikai_parm_match( $funcname, $refFunc, $docblock ) {
  $params = $refFunc->getParameters();
  //print_r( $params );
  $type = null;
  $match = oikai_compare_param( $params, 0, "atts", true, true, null, false );
  if ( $match ) {
    $match = oikai_compare_param( $params, 1, "content", true, true, null, false ); 
    if ( $match ) {    
      $match = oikai_compare_param( $params, 2, "tag", true, true, null, false ); 
      if ( $match ) 
        $type = "shortcode"; 
    }
  }
  return( $type );
}


/**
 * Compare an actual parameter with one we're looking for
 * 
 * Note: The result of isDefaultValueAvailable will be false for a parameter if any subsequent parameters do not have default values! 
 * A field can't be optional if a subsequent field isn't. 
 * 
 */
function oikai_compare_param( $params, $position, $name, $Optional, $Default, $DefaultValue, $PassedByReference ) {
  $param = bw_array_get( $params, $position, null );
  if ( $param ) {
    $match = true;
    $match = oikai_compare_value( $match, $param, "getName", $name );
    $match = oikai_compare_value( $match, $param, "isOptional", $Optional );
    $match = oikai_compare_value( $match, $param, "isDefaultValueAvailable", $Default );
    if ( $match && $Default ) { 
      $match = oikai_compare_value( $match, $param, "getDefaultValue", $DefaultValue ); 
     }
    $match = oikai_compare_value( $match, $param, "isPassedByReference", $PassedByReference );
  } else {
    $match = false;
  }  
  return( $match );
} 

/**
 * Compare a param's method result with the given value
 */
function oikai_compare_value( $match, $param, $method, $value ) {
  if ( $match ) { 
    $got = $param->$method();
    if ( $got != $value ) {
      $match = false;
      // echo "$method got: $got value: $value false";
    }    
  }
  return( $match );
}

/**
 * Detect "hook" type functions that end with a hook suffix e.g. __help
 * 
 */
function oikai_funcname_analysis( $funcname ) { 
  $pos = strpos( $funcname, "__" );
  if ( $pos !== false ) {
    $suffix = substr( $funcname, $pos );
    $hooks = bw_assoc( bw_as_array( "__help,__syntax,__example" ));
    $hook = bw_array_get( $hooks, $suffix, false );
    if ( $hook ) { 
      $hook = "hook"; 
    }
  } else {
    $hook = false;
  }
  return( $hook );
}         

