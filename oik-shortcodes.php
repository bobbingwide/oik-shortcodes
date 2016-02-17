<?php 
/*
Plugin Name: oik shortcodes server
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-shortcodes
Description: oik shortcodes, APIs, hooks and classes and the [bw_api], [api], [apis], [codes], [hooks], [file], [files], [classes], [hook] and [md] shortcodes
Depends: oik base plugin, oik fields, oik-sc-help
Version: 1.27.4
Author: bobbingwide
Author URI: http://www.bobbingwide.com
License: GPL2

    Copyright 2012-2016 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

oik_shortcodes_loaded();  

/** 
 * Implement "oik_fields_loaded" action for oik-shortcodes
 *
 * Register the custom post types, taxonomies and fields for oik-shortcodes
 * 
 */
function oik_shortcodes_init() {
  oik_register_oik_shortcodes();
  oik_register_oik_sc_param();
  oik_register_oik_shortcode_example();
  //oik_register_oik_sc_mapping();
  oik_register_file();
  oik_register_class();
  oik_register_hook();
  oik_register_api();
  oik_register_parsed_source();
	
  add_action( 'the_content', "oiksc_the_content", 1, 1 );
  add_action( 'oik_admin_menu', 'oiksc_admin_menu' );
  add_filter( 'wp_insert_post_data', 'oiksc_wp_insert_post_data', 10, 2 );
  add_action( "oik_add_shortcodes", "oik_shortcodes_add_shortcodes" );
	
	/**
	 * We could move this logic to oik
	 * but since we need oik-plugins, oik-themes and oik-shortcodes
	 * all together it's just as good here as any.
	 */
	remove_filter( "get_the_excerpt", "wp_trim_excerpt" );
	add_filter( "get_the_excerpt", "oik_get_the_excerpt" );
	
	add_filter( "request", "oiksc_request" );
}

/**
 * Implement "get_the_excerpt" for oik-shortcodes
 *
 * WordPress SEO ( Yoast SEO ) has a nasty habit of asking for the Excerpt if you don't set a Meta description.
 * This can invoke a whole bunch of filters. We don't need this. 
 * While it quite easy to type the Meta description when you hand create content, it may not be created
 * for automatically generated stuff. 
 * This filter will return an excerpt either from the excerpt or the part of the content before any comment
 * This allows for <!--more and <!--page tags
 * If there isn't one we return the full post content.
 * That can be dealt with by the subsequent filters. 
 * 
 * @param string $excerpt
 * @return string the excerpt we think will do
 */
function oik_get_the_excerpt( $excerpt=null ) {
	global $post;
	bw_trace2( $post, "global post", true, BW_TRACE_DEBUG );
	if ( !$excerpt ) {
		if ( $post ) {
			if ( $post->post_excerpt ) {
				$excerpt = $post->post_excerpt;
				
			} else {
				$pos_more = strpos( $post->post_content, "<!--" );
				if ( $pos_more ) {
					$excerpt = substr( $post->post_content, 0, $pos_more );
				} else {
					$excerpt = $post->post_content;
				}
			} 
				
			
		}
	}
	return( $excerpt );
}

/**
 * Implement "oik_add_shortcodes" action for oik-shortcodes
 *
 * Register our lazy smart shortcodes
 *
 * @TODO - Add a shortcode for *methods*
 *  
 */
function oik_shortcodes_add_shortcodes() { 
  bw_add_shortcode( "bw_api", "oikai_apiref", oik_path( "shortcodes/oik-api-importer.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "api", "oikai_api", oik_path( "shortcodes/oik-api.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "apis", "oikai_apilink", oik_path( "shortcodes/oik-apilink.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "hooks", "oikho_hooklink", oik_path( "shortcodes/oik-hookslink.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "codes", "oikai_codeslink", oik_path( "shortcodes/oik-codeslink.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "file", "oikai_fileref", oik_path( "shortcodes/oik-file.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "files", "oikai_filelink", oik_path( "shortcodes/oik-filelink.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "classes", "oikai_classlink", oik_path( "shortcodes/oik-classlink.php", "oik-shortcodes" ), false ); 
  bw_add_shortcode( "hook", "oikho_hook", oik_path( "shortcodes/oik-hook.php", "oik-shortcodes" ), false );
  bw_add_shortcode( "md", "oikai_markdown", oik_path( "shortcodes/oik-markdown.php", "oik-shortcodes" ), false );
}

/** 
 * Register custom post type "oik_shortcodes" 
 *
 * The title should contain the shortcode name 
 * In the content the text before the <!--more --> should be the shortcode's short description
 * The rest of the content should show the syntax, examples, links to APIs etc
 * Some of this is autogenerated using shortcodes.
 *
 * Post type support of "publicize" is added to enable publicizing using JetPack.
 * 
 */
function oik_register_oik_shortcodes() {
  $post_type = 'oik_shortcodes';
  $post_type_args = array();
  $post_type_args['label'] = 'Shortcodes';
  $post_type_args['description'] = 'Shortcode definitions';
  $post_type_args['has_archive'] = true;
	// Not using query_var for this post type
	// $post_type_args['query_var'] = "oik-shortcodes";
  bw_register_post_type( $post_type, $post_type_args );
  add_post_type_support( $post_type, 'publicize' );
  
  bw_register_field( "_oik_sc_code", "text", "Shortcode" ); 
  bw_register_field( "_oik_sc_plugin", "noderef", "Plugin ref", array( "#type" => "oik-plugins" )); 
  bw_register_field( "_oik_sc_func", "noderef", "API ref", array( "#type" => "oik_api" )); 
  bw_register_field( "_oik_sc_example_cb", "checkbox", "Generate the programmed example?", array( "#theme" => false ) ); 
  bw_register_field( "_oik_sc_live_cb", "checkbox", "Generate examples using Live data?", array( "#theme" => false ) );
  bw_register_field( "_oik_sc_snippet_cb", "checkbox", "Generate snippets?", array( "#theme" => false ) ); 
  bw_register_field( "_oik_sc_endcode_cb", "checkbox", "Enclosed content shortcode? Needs [<i>/code</i>]?" );
  
  $the_title_args = array( "#callback" => "bw_get_shortcode_expands_in_titles"
                         , "#parms" => "_oik_sc_code" 
                         , "#plugin" => "oik"
                         , "#file" => "shortcodes/oik-codes.php"
                         , "#form" => false
                         , "#hint" => "virtual field"
                         ); 
  bw_register_field( "_oik_sc_the_title_cb", "virtual", "Expands in 'the_title'?", $the_title_args );
	bw_register_field( "_oik_sc_shortcake_cb", "checkbox", "Compatible with shortcake?" );
  
  bw_register_field_for_object_type( "_oik_sc_code", $post_type );
  bw_register_field_for_object_type( "_oik_sc_plugin", $post_type );
  bw_register_field_for_object_type( "_oik_sc_func", $post_type );
  bw_register_field_for_object_type( "_oik_sc_example_cb", $post_type );
  bw_register_field_for_object_type( "_oik_sc_live_cb", $post_type );
  bw_register_field_for_object_type( "_oik_sc_snippet_cb", $post_type );
  bw_register_field_for_object_type( "_oik_sc_endcode_cb", $post_type );
  bw_register_field_for_object_type( "_oik_sc_the_title_cb", $post_type );
  bw_register_field_for_object_type( "_oik_sc_shortcake_cb", $post_type );
  
  add_filter( "manage_edit-${post_type}_columns", "oik_shortcodes_columns", 10, 2 );
  add_action( "manage_${post_type}_posts_custom_column", "bw_custom_column_admin", 10, 2 );
}

/**
 * Implements "manage_edit-oik_shortcodes_columns" filter for oik-shortcodes
 *
 * @param array $columns - associative array of column titles
 * @param string $arg2 - dummy parameter
 * @return array - updated columns array
 */
function oik_shortcodes_columns( $columns, $arg2=null ) {
  $columns["_oik_sc_plugin"] = __("Plugin"); 
  // bw_trace2();
  return( $columns ); 
} 

/**
 * Theme the "_oik_hook_deprecated_cb" field, type checkbox 
 *
 * This is probably unnecessary as bw_theme_field_checkbox() (in oik-fields) will do the same thing
 *  
 * @param string $key - the field name
 * @param string $value - the field value
 *
 */
function bw_theme_field_checkbox__oik_hook_deprecated_cb( $key, $value ) {
  $value = bw_array_get( $value, 0, $value );
  if ( $value && $value != "0" ) {
    e( __( "Yes" ));
  } else { 
    e( __( "No" ));
  } 
}

/**
 * Return an array of parameter types
 * 
 * For parameter values which could be CSS classes or IDs we currently use text
 * @TODO consider changing this to a custom taxonomy
 *
 * @return array - simple array of parameter types
 */
function oiksc_param_types() {
  $sc_param_types = array( "n/a"
                         , "CSV"
                         , "ID"
                         , "bool"
                         , "classes" 
                         , "numeric"
                         , "post_type"
                         , "select"
                         , "text"
                         , "URL"
                         );
  return( $sc_param_types );
}                           

/** 
 * Register custom post type "oik_sc_param" 
 * 
 * A shortcode parameter consists of:
 *   Title=shortcode param parameter
 *   content=An overview of the different values for the parameter and how this might affect the output of the shortcode
 * The meta data includes: 
 * - a noderef to the specific shortcode (Note: shortcodes which are duplicated are identified in their titles by "code (plugin)" 
 * - the parameter name e.g. class
 * - the bw_skv() values: default, values, notes 
 * - the expected type of the shortcode - future use
 */
function oik_register_oik_sc_param() {
  $post_type = 'oik_sc_param';
  $post_type_args = array();
  $post_type_args['label'] = 'Shortcode parameters';
  $post_type_args['description'] = 'Parameter definitions for shortcodes';
  // This might reduce the amount of gumpf we see when searching. Herb 2014/01/10
  // $post_type_args['searchable'] = false;
  bw_register_post_type( $post_type, $post_type_args );
  
  bw_register_field( "_sc_param_code", "noderef", "Shortcode", array( '#type' => 'oik_shortcodes') );
  bw_register_field( "_sc_param_name", "text", "Parameter name" );
  bw_register_field( "_sc_param_type", "select", "Parameter type", array( '#options' => oiksc_param_types() ) );
  bw_register_field( "_sc_param_default", "text", "Default" ); 
  bw_register_field( "_sc_param_values", "text", "Other values" ); 
  bw_register_field( "_sc_param_notes", "text", "Notes" ); 
  
  bw_register_field_for_object_type( "_sc_param_code", $post_type );
  bw_register_field_for_object_type( "_sc_param_name", $post_type );
  bw_register_field_for_object_type( "_sc_param_type", $post_type );
  bw_register_field_for_object_type( "_sc_param_default", $post_type );
  bw_register_field_for_object_type( "_sc_param_values", $post_type );
  bw_register_field_for_object_type( "_sc_param_notes", $post_type );
}

/** 
 * Register custom post type "shortcode_example"
 * 
 * A shortcode example refers to a particular shortcode.
 * It could also refer to a whole host of shortcode parameters... but that might get too complex.
 * Post type support of "publicize" is added to enable publicizing using JetPack.
 *  
 */
function oik_register_oik_shortcode_example() {
  $post_type = 'shortcode_example';
  $post_type_args = array();
  $post_type_args['label'] = 'Shortcode examples';
  $post_type_args['description'] = 'Example shortcode usage';
  $post_type_args['supports'] = array( 'title', 'editor', 'excerpt', 'thumbnail' );
  $post_type_args['has_archive'] = true;
  bw_register_post_type( $post_type, $post_type_args );
  add_post_type_support( $post_type, 'publicize' );
  // bw_register_field( "_sc_param_code", "noderef", "Shortcode", array( '#type' => 'oik_shortcodes') );
  bw_register_field_for_object_type( "_sc_param_code", $post_type );
}

/**
 * Return an array of possible API types
 * 
 * <pre>
 * API type     Parameter / notes                                                        Returns
 * -----------  -----------------------------------------------------------------------  ------------------------------
 * "shortcode"  API can receive up to 3 parameters: $atts, $content, $tag                Returns the expanded shortcode
 * "filter"     API receives a number of parameters, defined using add_filter()          Return the filtered content 
 *              First  is normally the $content to filter. 
 * "action"     API can receive a number of parameters, defined using add_action()       void
 * "public"     Public API that isn't an shortcode, filter or action
 * "private"    Private API - sometimes prefixed with _                                  
 * "undefined"  not known at this time
 * "deprecated" 
 * "hook"       Private API such as funcname__syntax, funcname__help or similar 
 * "method"     A function implemented by a class.
 * "file"       A dummy function which is all the rest of the file 
 * </pre>
 *
 */
function oiksc_api_types() {
	bw_backtrace();
	if ( oik_require_lib( "bobbfunc" ) ) {
		static $types = array( "shortcode"
                , "filter"
                , "action"
                , "public"
                , "private"
                , "undefined"
                , "deprecated"
                , "hook"
                , "method"
                , "file" 
                );
		return( bw_assoc( $types) );              
	} else { 
		gob();
	}
}

/**
 * Return an array of possible hook types
 *
 * @return array - currently only contains "action" and "filter"
 */
function oiksc_hook_types() {

	bw_backtrace();
	//var_dump( debug_backtrace() );
  $types = bw_assoc( bw_as_array( "action,filter" ));
  return( $types );
}
  
/**
 * Register custom post type: oik_file
 * 
 * File hierarchy is implemented using the parent field?
 * We use the post_modified_date and the file passes count to tell whether or not to re-parse the file
 * 
 * Metadata fields:
 * _oik_file_name - the file name excluding the "plugin" path e.g. oik-shortcodes.php for this file or wp-includes/plugin.php
 *   Unique within the plugin but may be part of multiple plugins.
 *
 * _oik_api_plugin - noderef to an oik-plugins or oik-themes post type
 * _oik_file_deprecated_cb - Checked if the file is deprecated ( or even deleted )
 * 
 * _oik_api_calls - noderef to functions the main file invokes 
 * _oik_api_hooks - noderef to hooks the main file invokes
 * 
 * A file is effectively an unnamed function that is invoked when it's loaded - by a require or include function.
 * It can therefore invoke APIs and use hooks.               
 * 
 */
function oik_register_file() {
  $post_type = 'oik_file';
  $post_type_args = array();
  $post_type_args['label'] = 'Files';
  $post_type_args['singular_label'] = 'File';
  $post_type_args['description'] = 'Files';
  $post_type_args['has_archive'] = true;
  $post_type_args['hierarchical'] = true; 
  bw_register_post_type( $post_type, $post_type_args );
  add_post_type_support( $post_type, 'page-attributes' );
  
  bw_register_field( "_oik_file_name", "text", "File name" , array( "#length" => 80 ));
  bw_register_field( "_oik_api_plugin", "noderef", "Plugin ref", array( "#type" => array( "oik-plugins", "oik-themes") )); 
  //bw_register_field( "_oik_file_passes", "numeric", "Parse count", array( "#theme" => false )); 
  bw_register_field( "_oik_file_deprecated_cb", "checkbox", "Deprecated?"); 
  bw_register_field( "_oik_api_calls", "noderef", "Uses APIs", array( "#type" => "oik_api", "#multiple" => true, "#optional" => true, '#theme' => false, '#length' => 80 ));
  bw_register_field( "_oik_api_hooks", "noderef", "Uses hooks", array( "#type" => "oik_hook", "#multiple" => true, "#optional" => true, '#theme' => false ));
  
  bw_register_field_for_object_type( "_oik_file_name", $post_type );
  bw_register_field_for_object_type( "_oik_api_plugin", $post_type );
  //bw_register_field_for_object_type( "_oik_file_passes", $post_type );
  bw_register_field_for_object_type( "_oik_file_deprecated_cb", $post_type );
  bw_register_field_for_object_type( "_oik_api_calls", $post_type );
  bw_register_field_for_object_type( "_oik_api_hooks", $post_type );
  if ( function_exists( "oikp_columns_and_titles" ) ) {
    oikp_columns_and_titles( $post_type );
  }    
} 
  
/**
 * Register custom post type: oik_class
 * 
 * Class hierarchy is implemented using the parent field
 * 
 * Metadata fields:
 * _oik_class_name - the class name
 * _oik_api_plugin - noderef to oik-plugins
 * _oik_api_source - file where the class is defined
 * 
 */
function oik_register_class() {
  $post_type = 'oik_class';
  $post_type_args = array();
  $post_type_args['label'] = 'Classes';
  $post_type_args['singular_label'] = 'Class';
  $post_type_args['description'] = 'Classes';
  $post_type_args['has_archive'] = true;
  $post_type_args['hierarchical'] = true; 
  bw_register_post_type( $post_type, $post_type_args );
  add_post_type_support( $post_type, 'page-attributes' );
  
  bw_register_field( "_oik_class_name", "text", "Class name" , array( "#length" => 80 ));
  bw_register_field( "_oik_api_plugin", "noderef", "Plugin ref", array( "#type" => array( "oik-plugins", "oik-themes" ) )); 
  bw_register_field( "_oik_api_source", "text", "Sourcefile" , array( "#length" => 80 ));
  
  bw_register_field_for_object_type( "_oik_class_name", $post_type );
  bw_register_field_for_object_type( "_oik_api_plugin", $post_type );
  bw_register_field_for_object_type( "_oik_api_source", $post_type );
  bw_register_field_for_object_type( "_oik_fileref", $post_type );
  if ( function_exists( "oikp_columns_and_titles" ) ) {
    oikp_columns_and_titles( $post_type );
  }    
} 

/**
 * Register custom post type: oik_hook
 * 
 * Action hooks and filters extend the processing of WordPress, plugins and themes
 * Rather than maintain a list of functions that the hook calls we need to list the functions that implement the hook.
 * We can determine this from the add_action(), add_filter() calls which specify the hook name and the API that implements it.
 * Since hook names can be dynamically specified we need to find a mechanism of looking up the hook using wild cards
 * 
 * The plugin and sourcefile is required to identify where the hook documentation is originally held.
 * This seems a bit tricky doesn't it?
 * We use _oik_hook_docblock to keep a copy of the most recent docblock comment for the hook.
 * 
 */
function oik_register_hook() {
  $post_type = 'oik_hook';
  $post_type_args = array();
  $post_type_args['label'] = 'Hooks';
  $post_type_args['description'] = 'Action and filter hooks';
  $post_type_args['has_archive'] = true;
  bw_register_post_type( $post_type, $post_type_args );
  
  bw_register_field( "_oik_hook_name", "text", "Hook name" , array( "#length" => 80 ));
  bw_register_field( "_oik_hook_type", "select", "Hook type", array( "#options" => oiksc_hook_types()) );
  bw_register_field( "_oik_hook_plugin", "noderef", "Plugin ref", array( "#type" => array( "oik-plugins", "oik-themes" ), "#optional" => true  )); 
  bw_register_field( "_oik_hook_source", "text", "Sourcefile" , array( "#length" => 80 ));
  bw_register_field( "_oik_fileref", "noderef", "File ref", array( "#type" => "oik-plugins" )); 
  bw_register_field( "_oik_hook_docblock", "textarea", "Docblock" ); 
  bw_register_field( "_oik_hook_deprecated_cb", "checkbox", "Deprecated?" );
  //
  // _oik_hook_invokers is a (yet to be developed) dummy field. 
  // It's a dummy field since it can be built in reverse from the _oik_api_hooks field
  // Invokers are the functions that initiate the hook.
  //  
  bw_register_field( "_oik_hook_calls", "noderef", "Implementers", array( "#type" => "oik_api", "#multiple" => true, "#optional" => true, "#form" => false, "#theme" => false ));
  
  bw_register_field_for_object_type( "_oik_hook_name", $post_type );
  bw_register_field_for_object_type( "_oik_hook_type", $post_type );
  bw_register_field_for_object_type( "_oik_hook_plugin", $post_type );
  bw_register_field_for_object_type( "_oik_hook_source", $post_type );
  bw_register_field_for_object_type( "_oik_fileref", $post_type );
  bw_register_field_for_object_type( "_oik_hook_docblock", $post_type );
  bw_register_field_for_object_type( "_oik_hook_deprecated_cb", $post_type );
  bw_register_field_for_object_type( "_oik_hook_calls", $post_type );

  if ( function_exists( "oikp_columns_and_titles" ) ) {
    oikp_columns_and_titles( $post_type );
  }    
} 

/**
 * Register custom post type: oik_api
 * 
 * Metadata fields:
 * - Do not include syntax since this is determined programmatically
 * - It should be possible to augment the documentation for an API if we know the particular type
 * - It should not be necessary to document the number of parameters needed for an action or filter since 
 *   this should be obvious from the prototype
 * - BUT someone might want to specify the expected priority in the body
 * - _oik_api_hooks is a serialized structure that is not exposed to the end user. Herb 2013/10/23
 * - 2014/07/08 We no longer register _oik_api_example or _oik_api_notes - if required these should be created as separate post types
 * - oik_api_source has become redundant
 */
function oik_register_API() {
  $post_type = 'oik_api';
  $post_type_args = array();
  $post_type_args['label'] = 'APIs';
  $post_type_args['description'] = 'Application Programming Interfaces';
  $post_type_args['has_archive'] = true;
  bw_register_post_type( $post_type, $post_type_args );
  
  bw_register_field( "_oik_api_name", "text", "Function name" , array( "#length" => 80 ));
  bw_register_field( "_oik_api_class", "noderef", "Class ref", array( "#type" => "oik_class", "#optional" => true )); 
  bw_register_field( "_oik_api_plugin", "noderef", "Plugin ref", array( "#type" => array( "oik-plugins", "oik-themes" ) )); 
  bw_register_field( "_oik_api_source", "text", "Sourcefile" , array( "#length" => 80 ));
  bw_register_field( "_oik_fileref", "noderef", "File ref", array( "#type" => "oik_file" )); 
  bw_register_field( "_oik_api_type", "select", "API type", array( "#options" => oiksc_api_types(), "#optional" => true ) );
  //bw_register_field( "_oik_api_example", "textarea", "Examples", array( '#theme' => false, '#form' => false ) ); 
  //bw_register_field( "_oik_api_notes", "textarea", "Notes", array( '#theme' => false, '#form' => false ) ); 
  bw_register_field( "_oik_api_deprecated_cb", "checkbox", "Deprecated?" ); 
  
  //bw_register_field( "_oik_api_calls", "noderef", "Uses APIs", array( "#type" => "oik_api", "#multiple" => true, "#optional" => true, '#theme' => false ));
  //bw_register_field( "_oik_api_hooks", "noderef", "Uses hooks", array( "#type" => "oik_hook", "#multiple" => true, "#optional" => true, '#theme' => false ));
  
  bw_register_field_for_object_type( "_oik_api_name", $post_type );
  bw_register_field_for_object_type( "_oik_api_class", $post_type );
  bw_register_field_for_object_type( "_oik_api_plugin", $post_type );
  bw_register_field_for_object_type( "_oik_api_source", $post_type );
  bw_register_field_for_object_type( "_oik_fileref", $post_type );
  bw_register_field_for_object_type( "_oik_api_type", $post_type );
  //bw_register_field_for_object_type( "_oik_api_example", $post_type );
  //bw_register_field_for_object_type( "_oik_api_notes", $post_type );
  bw_register_field_for_object_type( "_oik_api_deprecated_cb", $post_type );
  bw_register_field_for_object_type( "_oik_api_calls", $post_type );
  bw_register_field_for_object_type( "_oik_api_hooks", $post_type );

  if ( function_exists( "oikp_columns_and_titles" ) ) {
    oikp_columns_and_titles( $post_type );
  }    
}

/**
 * Register Custom Post Type: oik_parsed_source
 *
 * The parsed source CPT contains the parsed source for an API, file or class
 * Whenever the [bw_api] shortcode is expanded to display the parsed source
 * we look for a parsed source version and use that in preference to dynamically parsing the source.
 * 
 * Logic will exist to check if the parsed source is the latest 
 * If it's not then the source will be reparsed and the call trees and hook invocations rebuilt
 * We therefore need to store private information about the API, file and class that we've parse
 * in order to be able to determine whether or not to re-parse the code
 * 
 * Reasons for reparsing the code are:
 * 1. Updated plugin producing new logic - here we just force the parsing
 * 2. Some other component has now been parsed - which may affect the links we create to "APIs", "WordPress a2z", PHP
 * 3. Change to the source PHP
 *
 * title: "Parsed $post_title of the referenced object
 * post_content: the parsed content
 * post_content_filtered: Not a good idea to use this field as WordPress cleans it out regularly
 * post_excerpt: might be an idea?
 * last_update_date: date when last parsed? - we probably can't trust this
 * 
 */
function oik_register_parsed_source() {
  $post_type = 'oik_parsed_source';
  $post_type_args = array();
  $post_type_args['label'] = 'Parsed Source';
  $post_type_args['description'] = 'Pre-parsed APIs, files and classes';
  $post_type_args['exclude_from_search'] = true;
  $post_type_args['show_in_nav_menus'] = false;
  $post_type_args['has_archive'] = false;
  bw_register_post_type( $post_type, $post_type_args );
  
  bw_register_field( "_oik_sourceref", "noderef", "Source ref", array( "#type" => array( "oik_api", "oik_file", "oik_class" ) ) );
  bw_register_field( "_oik_parse_count", "timestamp", "Parse count / Source file date" );
   
  bw_register_field_for_object_type( "_oik_sourceref", $post_type );
  bw_register_field_for_object_type( "_oik_parse_count", $post_type );
  
  if ( function_exists( "oikp_columns_and_titles" ) ) {
    oikp_columns_and_titles( $post_type );
  }
}    

/**
 * Add some content before other 'the_content' filtering is performed
 * 
 * @param post $post
 * @return string additional content
 
 * For oik_shortcodes we automatically add a [bw_code] shortcode if there isn't one already present
 * We search for "[bw_code " to allow for "[bw_codes" but don't worry about "[[bw_code blah]]"
 * Remember, we only add the code if we don't find "[bw_code "
 * If the content already contains [[bw_code blah]] then we assume that the proper [bw_code] has already been written
 * The reason for doing this is to cater for when we want to create an example but there isn't a code__example() function
 */
function oiksc_the_post_oik_shortcodes( $post ) {
  if ( false === strpos( $post->post_content, "[bw_code ") ) {
    $code = get_post_meta( $post->ID, "_oik_sc_code", true );
    $example = get_post_meta( $post->ID, "_oik_sc_example_cb", true );
    $live_example = get_post_meta( $post->ID, "_oik_sc_live_cb", true );
    $snippet = get_post_meta( $post->ID, "_oik_sc_snippet_cb", true );
    $additional_content = "[bw_code"; 
    $additional_content .= kv( "shortcode", $code );
    $additional_content .= kv( "help", "N" );
    $additional_content .= kv( "syntax", "N" );
    $additional_content .= kv( "example", $example );
    $additional_content .= kv( "live", $live_example );
    $additional_content .= kv( "snippet", $snippet );
    $additional_content .= "]";
  } else {
    $additional_content = null;
  }       
  bw_trace2( $additional_content, "additional content" );
  return( $additional_content );
}
  
/**
 * Add some content before other 'the_content' filtering is performed
 *
 * For an oik_class we want to display:
 * - Methods
 * - Extends - if there is a parent
 * - Extended by - for any child class
 * - and fields
 *
 * 
 * @param post $post
 * @return string additional content
 *
 */
function oiksc_the_post_oik_class( $post ) {
  if ( false === strpos( $post->post_content, "[") ) {
    $additional_content = "<h3>Methods</h3>";
    $additional_content .= "[apis posts_per_page=.]";
    if ( $post->post_parent ) {
      $additional_content .= "<h3>Extends</h3>";
      $additional_content .= "[bw_parent]";
    }
    $additional_content .= "<h3>Extended by</h3>";
    $additional_content .= "[bw_tree]";
    $additional_content .= "[bw_fields]";
  } else {
    $additional_content = null;
  }       
  bw_trace2( $additional_content, "additional content" );
  return( $additional_content );
}

 
/**
 * Add some content before other 'the_content' filtering is performed
 *
 * For an oik_sc_param we want to display:
 * - Fields
 * 
 * @param post $post
 * @return string additional content
 *
 */
function oiksc_the_post_oik_sc_param( $post ) {
  if ( false === strpos( $post->post_content, "[") ) {
		$additional_content = "<!--more-->";
		//$additional_content .= "[bw_fields]";
		$additional_content .= "[bw_code name=.]";
	} else {
		$additional_content = null;
	}
	return( $additional_content ); 
}

/**
 * Implement 'the_content' filter specifically for the oik_shortcodes or oik_class post types
 *
 * Note: Since this function can be invoked multiple times we have to stop it from going recursive
 * on us when other routines invoke the 'the_content' filter.
 
 * We would like to do this by
 * - testing to see if the content being filtered is the current post 
 * - testing if we need to append additional content
 * - updating the global post with this additional content.
 *
 * But this doesn't work when we're processing inside "get_the_excerpt" and/or when
 * the $content has already been filtered to change the <!--more--> tag! 
 
 *
 * @param string $content - the current content of the post
 * @return string $content - the filtered content of the post
 * 
 */
function oiksc_the_content( $content ) {
	global $post;
	static $contented = null;
	bw_trace2( $post, "global post", true, BW_TRACE_DEBUG );
	if ( $post ) {
		if ( ( $post->post_type == "oik_shortcodes" ) && $contented === null && ( false === strpos( $content, "[bw_code ") ) ) {
			$contented = $content;
			$content .= oiksc_the_post_oik_shortcodes( $post );
			//$post->post_content = $content;
		}	elseif ( ( $post->post_type == "oik_class" ) && $contented === null && ( false === strpos( $content, "[") ) ) {
			$contented = $content;
			$content .= oiksc_the_post_oik_class( $post );
			//$post->post_content = $content;
		}	elseif ( ( $post->post_type == "oik_sc_param" ) && $contented === null && ( false === strpos( $content, "[") ) ) { 
			$contented = $content;
			$content .= oiksc_the_post_oik_sc_param( $post );
		}
	}
	return( $content );
}

/**
 * Implement "oik_admin_menu" action for oik-shortcodes
 */
function oiksc_admin_menu() {
  oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
  oiksc_lazy_admin_menu();
} 

add_action( "init", "oiksc_shortcode_rewrite" );

/**
 * Implement "init" action for oik-shortcodes
 * 
 * Handle requests to http://www.oik-plugins.com/oik-shortcodes/%oik-shortcode%?callback=callback_function
 * to find the %oik-shortcode% and redirect to the definition.
 * 
 * If it's missing then perhaps it should be logged for creation.
 */
function oiksc_shortcode_rewrite() {
  add_rewrite_tag( "%oik-shortcode%", '([^/]+)' );
  add_rewrite_tag( "%oik-function%", '([^/]+)' );
  // add_permastruct( 'oik-shortcode', 'oik-shortcodes/%oik-shortcode%' );
  add_permastruct( 'oik-shortcode', 'oik-shortcodes/%oik-shortcode%/%oik-function%/' );
  // add_action( "template_redirect", "oiksc_template_redirect" ); 
}

/**
 * Handle the oik-shortcodes/%oik-shortcode% request
 */
function oiksc_template_redirect() {
  $oik_shortcode = get_query_var( "oik-shortcode" );
  $oik_function = get_query_var( "oik-function" );
  bw_trace2( $oik_shortcode, "oik-shortcode", false );
  bw_trace2( $oik_function, "oik-function", false );
  if ( $oik_shortcode ) {
    oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
    oiksc_lazy_redirect( $oik_shortcode, $oik_function ); 
  } 
}

/**
 * Load the post for the selected plugin/theme
 * 
 * @param string $plugin - the plugin or theme slug
 * @param string $component_type - "plugin"|"slug"
 */
function oiksc_load_component( $plugin, $component_type ) {
  $plugin_post = null;
  switch ( $component_type ) {
    case "wordpress":
    case "plugin":
      $plugin_post = oikp_load_plugin( $plugin );
      break;
      
    case "theme":
      if ( function_exists( "oikth_init" ) ) {
        oik_require( "feed/oik-themes-feed.php", "oik-themes" ); 
        $plugin_post = oikth_load_theme( $plugin );
      } else {
        gb();
      }
      break;
      
    default:
      bw_trace2( $component_type, "Invalid component type for $plugin" );
  }
  return( $plugin_post );
}
 

/**
 * Implement "wp_ajax_oiksc_create_api" action for oik-shortcodes
 *
 * The API may contain the API name in the format of 
 *  'class::api' - representing a method
 *  'api' - representing a function
 *  'class::' - representing a class
 * 
 * The value we store in "_oik_api_name" for a method will be in the class::api format 
 * 
 * API names are normally expected to be unique. This code does not cater for use of namespacing. 
 * 
 */
function oiksc_ajax_oiksc_create_api() {
  do_action( "oik_loaded" );
  // User still has to be authorised to perform the request!
  // So how do we check this?
  //oiksc_create_api();
  global $plugin_post, $plugin;
  
  $type = bw_array_get( $_REQUEST, "type", null );
  $api = bw_array_get( $_REQUEST, "api", null );
  $file = bw_array_get( $_REQUEST, "file", null );
  $plugin = bw_array_get( $_REQUEST, "plugin", null );
  $title = bw_array_get( $_REQUEST, "title", null );
  // We don't need the $type **?**
  if ( $api && $file ) {
    oik_require( "includes/bw_posts.inc" ); 
    oik_require( "admin/oik-apis.php", "oik-shortcodes" );
    oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
    oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
    oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
    if ( is_null( $plugin )) {
      $plugin = 'wordpress';
      //$file = ABSPATH . $file;
      //$file = str_replace( "\\", "/", $file );
      bw_trace2( $file, "file" );
    } 
    $component_type = oiksc_query_component_type( $plugin );
    $plugin_post = oiksc_load_component( $plugin, $component_type );
    if ( $plugin_post ) {
      $func = oikai_get_func( $api, null ); 
      if ( $func ) {
        $post_id = _oiksc_create_api( $plugin_post->ID, $api, $file, $type, $title ); 
      } else {
        $post_id = oikai_get_classref( $api, null, $plugin_post->ID, $file );
      }
    } else {
      e( "Invalid plugin: $plugin ");
    }    
  } else {
    bw_trace2();
    e( "missing stuff :$api:$file:$plugin:" );
  }
  bw_flush(); 
  exit();    
}

/**
 * Implement "wp_ajax_oiksc_create_file" action for oik-shortcodes
 *
 * Create or update an "oik_file" post AND parse all the classes, methods and APIs implemented, including the main file,
 * using logic similar to createapi2
 * 
 */
function oiksc_ajax_oiksc_create_file() {

  do_action( "oik_loaded" );
  // User still has to be authorised to perform the request!
  // So how do we check this?
  //oiksc_create_api();
  global $plugin_post, $plugin;
  
  $file = bw_array_get( $_REQUEST, "file", null );
  $plugin = bw_array_get( $_REQUEST, "plugin", null );
  if ( $file ) {
    oik_require( "includes/bw_posts.inc" ); 
    oik_require( "admin/oik-apis.php", "oik-shortcodes" );
    oik_require( "admin/oik-files.php", "oik-shortcodes" );
    //oik_require( "admin/oik-shortcodes.php", "oik-shortcodes" );
    oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
    oik_require( "shortcodes/oik-api-importer.php", "oik-shortcodes" );
    if ( is_null( $plugin )) {
      $plugin = 'wordpress';
      //$file = ABSPATH . $file;
      //$file = str_replace( "\\", "/", $file );
      bw_trace2( $file, "file" );
    } 
    // $plugin_post = oikp_load_plugin( $plugin );
    $component_type = oiksc_query_component_type( $plugin );
    $plugin_post = oiksc_load_component( $plugin, $component_type );
    if ( $plugin_post ) {
      $file_id = _oikai_create_file( $plugin_post->ID, $file ); 
      $filename = oik_pathw( $file, $plugin, $component_type );
      $parsed_source = oiksc_display_oik_file( $filename, $component_type, $file_id, true );
    } else {
      e( "Invalid component: $plugin, type: $component_type ");
    }    
  } else {
    bw_trace2();
    e( "missing stuff :$file:$plugin:" );
  }
  bw_flush(); 
  exit();    
}

/**
 * Validate the apikey field
 *
 * @TODO - This invokes apply_filters in a non-standard way. The returned value is expected to be the first parameter.
 *
 * @return string - the API key if valid.
 */
function oiksc_validate_apikey() {
  $apikey = bw_array_get( $_REQUEST, "oik_apikey", null );
  if ( $apikey ) {
    $apikey = apply_filters( "oik_validate_apikey", null, $apikey );
  } else { 
    p( "Missing oik_apikey" );
  }  
  return( $apikey );
}  

/**
 * Implement "wp_ajax_nopriv_oiksc_create_api" action for oik-shortcodes
 * 
 * Obtain the APIKEY from the request. If valid then continue with oiksc_ajax_oiksc_create_api() 
 */
function oiksc_ajax_nopriv_oiksc_create_api() {
  $continue = oiksc_validate_apikey();
  if ( $continue ) {
    oiksc_ajax_oiksc_create_api();
  } 
  bw_backtrace();
  bw_flush();
  exit();
}

/**
 * Implement "wp_ajax_nopriv_oiksc_create_file" action for oik-shortcodes
 * 
 * Obtain the APIKEY from the request. If valid then continue with oiksc_ajax_oiksc_create_file() 
 */
function oiksc_ajax_nopriv_oiksc_create_file() {
  $continue = oiksc_validate_apikey();
  if ( $continue ) {
    oiksc_ajax_oiksc_create_file();
  } 
  bw_backtrace();
  bw_flush();
  exit();
}

/**
 * Implement "admin_notices" action for oik-shortcodes" 
 *
 * - v1.27 now dependent upon oik v2.4
 */ 
function oik_shortcodes_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-shortcodes/oik-shortcodes.php", "oik_shortcodes_activation" );   
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      require_once( "admin/oik-activation.php" );
    }
  }  
  $depends = "oik:2.4,oik-fields:1.36,oik-plugins:1.10,oik-sc-help";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Sanitize titles which may contain shortcodes that we don't want to expand
 *
 * Make sure that shortcodes don't naff up the processing of other shortcodes.
 * Converting LSB ([) and RSB (]) to the character equivalent each time the post is saved
 * thereby preventing shortcode expansion during title filtering.
 *
 * @param string $post_title - the unsafe post title
 * @return string - a sanitized post title
 */
function oiksc_oik_api_title_save_pre( $post_title = NULL ) {
  if ( $post_title ) {
    $post_title = str_replace( "]]", "&#93;&#93;", $post_title ); 
    $post_title = str_replace( "]", "&#93;", $post_title ); 
    $post_title = str_replace( "[[", "&#91;&#91;", $post_title );
    $post_title = str_replace( "[", "&#91;", $post_title ); 
  }
  return( $post_title );
}

/**
 * Implement "wp_insert_post_data" filter to sanitize the post_title
 *
 * Originally developed by hooking into 'title_save_pre', hence the function name of the routine that we call,
 * this filter hooks onto 'wp_insert_post_data' since we need the context of the post_type in order to decide what to do.
 * Previously, this hook would filter ALL titles, which meant that we couldn't use shortcodes in other post types titles
 * even if we wanted to.
 *
 * It begs the question, "why do we need all the other filters?" Like these:
 *   add_filter( 'title_save_pre', 'oiksc_oik_api_title_save_pre' );
 *   add_filter( 'post_title', 'oiksc_oik_api_post_title', 10, 3 );
 * 
 * @param array $data - the post_data
 * @param array $postarr - even more data
 * @return array - the filtered $data  
 */
function oiksc_wp_insert_post_data( $data, $postarr ) {
  //bw_trace2();
  switch ( $data['post_type'] ) {
    case "oik_shortcodes":
    case "shortcode_example":
    case "oik_api":
    case "oik_hook":
      $data['post_title'] = oiksc_oik_api_title_save_pre( $data['post_title'] );
      break;
  
    default:
      // Let shortcodes be included in the title without modification
  }
  return( $data );
}

/** 
 * Add filters for the $post_type
 * 
 * @param string $post_type - the Custom Post type name
 */ 
if ( !function_exists( "oikp_columns_and_titles" ) ) {
function oikp_columns_and_titles( $post_type ) {
  if ( function_exists( "bw_function_namify" ) ) {
    $post_type_namify = bw_function_namify( $post_type );
    add_filter( "manage_edit-${post_type}_columns", "${post_type_namify}_columns", 10, 2 );
    add_action( "manage_${post_type}_posts_custom_column", "bw_custom_column_admin", 10, 2 );
    add_filter( "oik_table_fields_${post_type}", "${post_type_namify}_fields", 10, 2 );
    add_filter( "oik_table_titles_${post_type}", "${post_type_namify}_titles", 10, 3 ); 
  }
}
}

/**
 * Implement "manage_edit-oik_class_columns" filter for "oik_class" 
 * 
 */
function oik_class_columns( $columns, $arg2=null ) {
  //$columns['_oik_class
  $columns['_oik_api_plugin'] = __("Plugin" );
  //$columns['_oik_api_type'] = __("Type"); 
  return( $columns ); 
}

/**
 * Implement "oik_table_fields_oik_class" filter for oik_class
 */ 
function oik_class_fields( $fields, $arg2 ) {
  $fields['_oik_api_plugin'] = '_oik_api_plugin';
  //$fields['_oik_api_type'] = __("Type"); 
  return( $fields );
}

/**
 * Implement "oik_table_titles" filter for oik_class
 * 
 * Titles are remarkably similar to columns for the admin pages
 */
function oik_class_titles( $titles, $arg2=null, $fields=null ) {
  $titles = oik_class_columns( $titles, $arg2 );
  return( $titles );
}

/**
 * Implement "manage_edit-oik_api_columns" filter for "oik_api" 
 * 
 */
function oik_api_columns( $columns, $arg2=null ) {
  $columns['_oik_api_plugin'] = __("Plugin" );
  $columns['_oik_api_type'] = __("Type"); 
  return( $columns ); 
}

/**
 * Implement "oik_table_fields_oik_api" filter for oik_api 
 */ 
function oik_api_fields( $fields, $arg2 ) {
  $fields['_oik_api_plugin'] = '_oik_api_plugin';
  $fields['_oik_api_type'] = '_oik_api_type'; 
  return( $fields );
}

/**
 * Implement "oik_table_titles" filter for oik_api
 * 
 * Titles are remarkably similar to columns for the admin pages
 */
function oik_api_titles( $titles, $arg2=null, $fields=null ) {
  $titles = oik_api_columns( $titles, $arg2 );
  return( $titles );
}

/**
 * Implement "manage_edit-oik_hook_columns" filter for "oik_hook" 
 * 
 */
function oik_hook_columns( $columns, $arg2=null ) {
  $columns['_oik_hook_plugin'] = __("Plugin" );
  $columns['_oik_hook_type'] = __("Type"); 
  return( $columns ); 
}

/**
 * Implement "oik_table_fields_oik_hook" filter for oik_hook 
 */ 
function oik_hook_fields( $fields, $arg2 ) {
  $fields['_oik_hook_plugin'] = '_oik_hook_plugin';
  $fields['_oik_hook_type'] = '_oik_hook_type'; 
  return( $fields );
}

/**
 * Implement "oik_table_titles" filter for oik_hook
 * 
 * Titles are remarkably similar to columns for the admin pages
 */
function oik_hook_titles( $titles, $arg2=null, $fields=null ) {
  $titles = oik_hook_columns( $titles, $arg2 );
  return( $titles );
}


/**
 * Implement "manage_edit-oik_file_columns" filter for "oik_file" 
 * 
 */
function oik_file_columns( $columns, $arg2=null ) {
  $columns['_oik_api_plugin'] = __("Plugin" );
  return( $columns ); 
}

/**
 * Implement "oik_table_fields_oik_file" filter for oik_file 
 */ 
function oik_file_fields( $fields, $arg2 ) {
  $fields['_oik_api_plugin'] = '_oik_api_plugin';
  return( $fields );
}

/**
 * Implement "oik_table_titles" filter for oik_file
 * 
 * Titles are remarkably similar to columns for the admin pages
 */
function oik_file_titles( $titles, $arg2=null, $fields=null ) {
  $titles = oik_file_columns( $titles, $arg2 );
  return( $titles );
}

/**
 * Implement "manage_edit-oik_parsed_source_columns" filter for "oik_parsed_source" 
 * 
 */
function oik_parsed_source_columns( $columns, $arg2=null ) {
  $columns['_oik_sourceref'] = __( "Source ref" );
  $columns['_oik_parse_count'] = __( "Parse count / file date" );
  return( $columns ); 
}

/**
 * Implement "oik_table_fields_oik_parsed_source" filter for oik_parsed_source 
 */ 
function oik_parsed_source_fields( $fields, $arg2 ) {
  $fields['_oik_sourceref'] = '_oik_sourceref';
  return( $fields );
}

/**
 * Implement "oik_table_titles" filter for oik_parsed_source
 * 
 * Titles are remarkably similar to columns for the admin pages
 */
function oik_parsed_source_titles( $titles, $arg2=null, $fields=null ) {
  $titles = oik_parsed_source_columns( $titles, $arg2 );
  return( $titles );
}

/**
 * Trace the results and echo a comment?
 *
 */
function oiksc_trace2( $value, $text, $extra=false ) {
  bw_trace2( $value, $text, $extra );
  oiksc_c3( $value, $text, $extra );
}

/**
 * When tracing is inactive we write the output as a comment
 *
 * Uses c()?  
 */ 
function oiksc_c3( $value, $text, $extra=false ) {
  if ( defined('DOING_AJAX') && DOING_AJAX ) {
    // Not safe to echo here
  } else {
    c( "$text:$value\n");
  }
}  
 

/** 
 * Show some really basic stuff about the PHP version, and number of functions and classes implemented
 * 
 * This is in addition to other stuff produced by oik-bwtrace
 * Not need to show number of db_queries as this is already (optionally) included in the trace record
 * BUT we could sum the time spent in the DB
 * AND we could sum the time spent tracing
 * which 'could' give us the execution time doing other things
 * 
 */
function oiksc_status_report() {
  global $bw_trace_on, $bw_trace_count;
  oik_require( "shortcodes/oik-api-status.php", "oik-shortcodes" );
  $func = "oiksc_c3";
  $defined_functions = get_defined_functions(); 
  //$count = count( $defined_functions ); 
  $count_internal = count( $defined_functions["internal"] );
  $count_user = count( $defined_functions["user"] );
  $func( phpversion(), "PHP version", false ); 
  $func( $count_internal, "PHP functions", false );
  $func( $count_user, "User functions", false );
  $declared_classes = get_declared_classes(); 
  $count = count( $declared_classes );
  $func( $count, "Classes", false ); 
  // Don't trace $GLOBALS - there's far too much - approx 38K lines
  //$func( $GLOBALS, "Globals", false );
  $func( oikai_query_plugin_count(), "Plugins", false );
  $func( count( get_included_files() ), "Files", false );
  
  $func( count( $GLOBALS['wp_registered_widgets'] ), "Registered widgets", false );
  $func( count( $GLOBALS['wp_post_types'] ), "Post types", false );
  $func( count( $GLOBALS['wp_taxonomies'] ), "Taxonomies", false );
  global $wpdb;
  
  $func( $wpdb->num_queries, "Queries", false );
  $func( $bw_trace_count, "Trace records", false );
  
  $elapsed = timer_stop( false, 6 );
  // Do this regardless 
  
  if ( $bw_trace_on ) { 
    $func = "oiksc_trace2";
  } else {
    $func = "oiksc_c3";
  }
  $func( $elapsed, "Elapsed (secs)", false );
  
  bw_flush();
}

/**
 * Function to invoke when oik-shortcodes is loaded 
 */
function oik_shortcodes_loaded() {
  add_action( 'oik_fields_loaded', 'oik_shortcodes_init' );
  add_action( "admin_notices", "oik_shortcodes_activation", 11 );
  add_action( "wp_ajax_oiksc_create_api", "oiksc_ajax_oiksc_create_api" );
  add_action( "wp_ajax_nopriv_oiksc_create_api", "oiksc_ajax_nopriv_oiksc_create_api" );
  
  add_action( "wp_ajax_oiksc_create_file", "oiksc_ajax_oiksc_create_file" );
  add_action( "wp_ajax_nopriv_oiksc_create_file", "oiksc_ajax_nopriv_oiksc_create_file" );
  //add_action( "shutdown", "oiksc_status_report" );
	
	
	
	add_filter( "oik_query_autoload_classes" , "oiksc_oik_query_autoload_classes" );
  
}

/**
 * 
 */
function oiksc_request( $request ) {
	$oik_shortcode = bw_array_get( $request, "oik-shortcode", null );
	$oik_function =  bw_array_get( $request, "oik-function", null );
	if ( $oik_function == 'diy_oik_do_shortcode_simplified' ) {
		$oik_function = null;
	}
	if ( $oik_shortcode ) {
		$request['post_type'] = "oik_shortcodes";
		if ( $oik_function ) {
			$meta_query = array();
			$meta_query[] = array( "key" => "_oik_sc_func", "value" => $oik_function );
			$meta_query[] = array( "key" => "_oik_sc_code", "value" => $oik_shortcode ); 
			$request['meta_query'] = $meta_query;
		} else {
			$request['meta_key'] = "oik_sc_code";
			$request['meta_value'] = $oik_shortcode;
		}
	}	
	return( $request );
}

function oiksc_oik_query_autoload_classes( $classes ) {
	bw_trace2();
	$classes[] = array( "class" => "oiksc_link_map"
										, "plugin" => "oik-shortcodes"
										, "path" => "classes" 
                    , "file" => "classes/class-oiksc-link-map.php" 
										);
	return( $classes );								

}


