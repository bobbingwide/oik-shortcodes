<?php // (C) Copyright Bobbing Wide 2012, 2017
 
/**
 * Determine the reference_type from PHP tokens
 *
 * Attempt to determine what sort of API this is using token_get_all()
 * 
 * @param string $string - the "api" name
 * @return string  - the reference type determined from token_name()
 * 
 */
function oikai_determine_from_tokens( $string ) {
  $reference_type = null;
  $tokens = token_get_all( "<?php $string" );
  $token = array_shift( $tokens );
  while ( $token ) {
    $token = array_shift( $tokens );
    if ( is_array( $token ) ) {
      //print_r( $token );
      $token_name = token_name( $token[0] );
      //$reference_type = _oikai_determine_reference_type( $ 
      $reference_type = $token_name;  
      $token = null;  
    }    
  }
  return( $reference_type );
}


/**
 * See if this is a constant name 
 *
 * @param string $string - could be ABSPATH or WPINC or something
 * 
 * 
 */ 
function oikai_check_constants( $string ) {
  $constants = get_defined_constants( false );
  //$reference_type = oikai_query_function_type
  $constant = bw_array_get( $constants, $string, null );
  if ( $constant ) {
    $reference_type = "constant"; 
  } else {
    $reference_type = "T_STRING";
  }
  return( $reference_type );
} 

/**
 * 
 */
function oikai_check_class_method_or_function( $string ) {
  $reference_type = "T_STRING";
  $class = oikai_get_class( $string, null );
  if ( $class ) {
    $func = oikai_get_func( $string, $class );
    if ( $func ) {
      $reference_type = "method";
    } else { 
      $reference_type = "class"; 
    }
  } else {
    $reference_type = "function";
  }
  return( $reference_type );  
}

/**
 * Determine the reference type for a string
 *
 * @param string $string - the passed string literal
 * @return string - the determined reference type
 *
 * Value    | Meaning
 * -------- | -------
 * internal | This is a PHP function
 * user     | This is a currently active application function
 * T_STRING | We couldn't decide
 * constant | It's a defined constant name ( currently active )
 * T_ other | It's a PHP token such as T_REQUIRE, T_REQUIRE_ONCE, etc 
 * class    | It's a class 
 * function | It's a function - but it's not active otherwise we'd have received "user" or "internal"
 * method   | It's a method ( with class name ) 
 * null     | Not expected at the end of this processing
 */     
function oikai_determine_reference_type( $string ) {
  $reference_type = oikai_determine_function_type( $string );
  
  if ( !$reference_type ) {
    $reference_type = oikai_determine_from_tokens( $string );
  }
  if ( $reference_type == "T_STRING" ) {
    $reference_type = oikai_check_constants( $string );
  }
  
  if ( $reference_type == "T_STRING" ) {
    $reference_type = oikai_check_class_method_or_function( $string );
  }
  //p( "$string:$reference_type" );
  
  return( $reference_type );
} 

/**
 * Handle a link to a "user" function
 * 
 */
function oikai_handle_reference_type_user( $api, $reference_type ) {
  $posts = oikai_get_oik_apis_byname( $api );
  bw_trace2( $posts );
  if ( $posts ) {
    oikapi_simple_link( $api, $posts );
  } else {
    e( oikai_link_to_wordpress( $api ) );
  }
  e( "()" );
}

/**
 * Handle a link to an "internal" PHP function
 *
 * This includes T_xxx values we don't yet cater for
 * 
 * 
 */
function oikai_handle_reference_type_internal( $api, $reference_type ) {
  e( oikai_link_to_php( $api ));
  e( "()" );
}

/**
 * Handle a link to a "function"
 */
function oikai_handle_reference_type_function( $api, $reference_type ) {
  oikai_handle_reference_type_user( $api, $reference_type );
}

/**
 * Handle a link to a "class" 
 *
 */
function oikai_handle_reference_type_class( $api, $reference_type ) {
  $posts = oikai_get_oik_class_byname( $api );
  bw_trace2( $posts );
  if ( $posts ) {
    oikapi_simple_link( $api, $posts );
  } else {
    e( oikai_link_to_wordpress( $api ) );
  }
}

/**  
 * Produce a link to the API based on the reference_type
 *
 * @param string $api - the API name
 * @param string $reference_type - the determined reference type
 *
 */
function oikai_handle_reference_type( $api, $reference_type ) {
  $funcname = bw_funcname( __FUNCTION__, $reference_type );
  //e( $funcname );
  if ( $funcname != __FUNCTION__ ) {
    if ( is_callable( $funcname ) ) {
      call_user_func( $funcname, $api, $reference_type );  
    } else {
      fob();
      oikai_handle_reference_type_internal( $api, $reference_type );
    }  
  } else {
    oikai_handle_reference_type_internal( $api, $reference_type );
  }  
}

/**
 * Simplify the API name
 *
 * Sometimes we write an API as apiname()
 * If we wrap this in the API shortcode we should be able to cater for the extraneous ()'s
 * 
 * There could be other things we could also do... such as sanitization
 * 
 * @param string $api - the given API name 
 * @return string - the simplified API name
 *  
 */
function oikai_simplify_apiname( $api ) {
  $api = str_replace( "()", "", $api );
  return( $api );
}

/**
 * Implement [api] shortcode to produce simple links to an API
 *
 * If there's just one API it's shown as "api()".
 * If more than one then they're comma separated, but NOT in an HTML list "api(), api2()"
 * Links are created to PHP, the local site or the 'preferred' WordPress reference site.
 *
 * @param array $atts - shortcode parameters
 * @param string $content - content
 * @param string $tag - the shortcode tag
 * @return string - generated HTML
 *
 */
function oikai_api( $atts=null, $content=null, $tag=null ) {
	oiksc_autoload();
  $apis = bw_array_get_from( $atts, "api,0", null );
  if ( $apis ) {
    $apia = bw_as_array( $apis );
    oik_require( "shortcodes/oik-apilink.php", "oik-shortcodes" );
    oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
    $count = 0;
    foreach ( $apia as $key => $api ) {
      $api = oikai_simplify_apiname( $api );
      if ( $count ) {
        e( "," );
        e( "&nbsp;" );
      }
      $count++;
      $type = oikai_determine_reference_type( $api ); 
      oikai_handle_reference_type( $api, $type );
    } 
  } else {
    oik_require( "shortcodes/oik-api-status.php", "oik-shortcodes" );
    oikai_api_status( $atts );
  }
  return( bw_ret() );
}

/**
 * OK, but we also want to link to PHP stuff
 * So we need to be able to call that function
 *  
 */
function oikapi_simple_link( $api, $posts ) {
  if ( $posts ) { 
    $post = bw_array_get( $posts, 0, null );
  } else {
    $post = null;
  }   
  if ( $post ) {  
    alink( "bw_api", get_permalink( $post ), $api, $post->title );
  } else {    
    e( $api );
  }  
}

/**
 * Help hook for [api] shortcode
 */
function api__help( $shortcode="api" ) {
  return( "Simple API link" );
}

/**
 * Syntax hook for [api] shortcode
 */
function api__syntax( $shortcode="api" ) {
  $syntax = array( "api|0" => bw_skv( null, "<i>api</i>", "API name" )
                 );
  return( $syntax );
} 

/**
 * Example hook for [api] shortcode
 */
function api__example( $shortcode="api" ) {
  oik_require( "includes/oik-sc-help.php" );
  $text = "Links to different APIs: PHP,locally documented,WordPress reference" ;
  $example = "require,oik_require,hello_dolly";
  bw_invoke_shortcode( $shortcode, $example, $text );
}


