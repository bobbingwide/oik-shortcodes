# oik shortcodes server 
* Contributors: bobbingwide
* Donate link: http://www.oik-plugins.com/oik/oik-donate/
* Tags: oik, fields, custom post types, shortcodes, APIs, hooks, [bw_api], [apis], [hooks], [codes]
* Requires at least: 3.8
* Tested up to: 3.9.1
* Stable tag: 1.23
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
The oik shortcodes server is used to document and serve the help documentation for plugins and themes:

* lazy smart shortcodes
* oik APIs, classes and methods - documenting the public and private oik/bw Application Programming Interface
* hooks - the action and filter hooks that oik plugins Invoke or Implement

It provides the following shortcodes:
* [bw_api] - automatically report the syntax for an API
* [api] - link to selected API(s)
* [apis] - link to related APIs (in a list)
* [classes] - list of classes
* [codes] - link to related shortcodes
* [file] - display file contents
* [files] - list of files
* [hook] - link to selected hook(s)
* [hooks] - links to action / filter hooks

It provides an admin page where APIs can be added
BUT is more useful when invoked using oik-batch, a WP-CLI like interface

In order for the Calls and Called by trees to be maintained you have to process each API twice.

New in version 1.23
* [hook] shortcode to display an inline link to a hook


New in version 1.22
* Parsed APIs and files are stored to improve display performance. Parsed classes will be added later.
* The first time that the API or file is parsed then a new post is created, with a link to the source post.
* The second parse will update the version, setting the _oik_parse_count field to the timestamp of the source file.
* Subsequent parses will only update the saved version if the source file has changed, or been touched.
* Display of APIs or files will check if the saved version is the latest.
* Note: Cached parsed output is currently NOT paginated.

New in version 1.20
* Support for themes, using on oik-themes server

New in version 1.19
* [files] shortcode to list files delivered by a plugin
* [file] shortcode for links to files
* [classes] shortcode to list classes implemented in a plugin
* Support for pagination of long lists

New in version 1.15
* Support for recording files in the "oik_file" CPT
* [file] shortcode to display the logic implemented in a file, ignoring classes, methods and APIs

New in version 1.10

* Support for classes and methods
* Reflection functions applied against dummy functions if not alreay loaded


New in version 1.08

* routines to dynamically register action and filter hooks.
* routines to associate APIs to hooks
* listing of Invokers and Implementers of actions and hooks

## Installation 
1. Upload the contents of the oik-shortcodes plugin to the `/wp-content/plugins/oik-shortcodes' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Define your content using wp-admin or the Ajax interface

## Frequently Asked Questions 
# Where is the FAQ? 
[oik FAQ](http://www.oik-plugins.com/oik/oik-faq)

# Is there a support forum? 
Yes - please use the standard WordPress forum - http://wordpress.org/tags/oik?forum_id=10

# Can I get support? 
Yes - see above

## Screenshots 
1. Editing an oik_api - showing the API type select list
2. Editing an oik_shortcode

## Upgrade Notice 
# 1.23 
Bug fixes for wp-a2z.com. Added [hook] shortcode

# 1.22 
Performance improvements for wp-a2z.com. Some bug fixes.

# 1.21 
Improvements for wp-a2z.com - to document the themes

# 1.20 
Improvements for wp-a2z.com - limit number of lines of source to be displayed per page

# 1.19 
Improvements for wp-a2z.com

# 1.18 
Formatting improvements for code section. New [api] shortcode for easy links.

# 1.17 
Fixes for wp-a2z.com to support Jetpack and display of "oik-file" callers

# 1.16 
Fix for _oik_fileref setting

# 1.15 
To attempt to bring in line with WP-Parser for "wordpress" plugin

# 1.14 
Fixes for oik options > Create APIs

# 1.13 
Further support for shortcodes in titles. Tested with WordPress 3.9.1

# 1.12 
Improved support for shortcodes in titles. Tested with WordPress 3.9.1

# 1.11 
Now supports OO classes and methods

# 1.10.0423
Intermediate version for OO support

# 1.10 
Quick fix

# 1.09 
Now supports 'archive' for oik_api and oik_hook and updates to oik_hook posts

# 1.08 
Required for using [bw_related] to show shortcode examples.

# 1.07 
Required for sites where wp-login.php is protected by an extra page with complex cookie

# 1.06 
Required for rowlandscastlewebdesign.com/phphants as well

# 1.05 
Required for oik-plugins.com and oik-plugins.co.uk

# 1.04.0421 
Pre-requisite to safe install of oik-plugins v1.1.0421

# 1.04.0315 
Depends on oik v2.0-alpha.0315 and oik-fields v1.18.0315 for multiple select noderef fields

# 1.04.0226 
Depends on oik v2.0-alpha, oik fields v1.18 and oik-plugins v1.1

# 1.04.0224
Depends on oik v1.18, oik fields v1.18 and oik-plugins v1.1

# 1.04.0222
Depends on oik v1.17, oik fields v1.18 and oik-plugins v1.1

# 1.04.0405
Depends on oik v1.17

# 1.04 
Depends on oik v1.17

# 1.03.1115.1704
Depends on oik v1.17.1103.1626

# 1.03 
Depends on oik v1.17.1103.1626

# 1.02 
Depends on oik v1.17.1101.1850

# 1.01 
Includes a fix for the Create shortcode admin page
# 1.0 
First version for oik-plugins.com, depends on oik v1.17 and oik-fields v1.18, oik-plugins v1.1

## Changelog 
# 1.23 
* Added: [hook] shortcode to display an inline link to an action or filter hook
* Added: oikai_simplify_apiname() - in case () are used within the [api] shortcode
* Fixed: call esc_html to handle HTML in parameter descriptions that otherwise cause formatting problems
* Changed: No longer calls oiksc_status_report on 'shutdown'; logic cloned to oik-bwtrace v1.21
* Changed: Commented out some trace calls

# 1.22 
* Added: new CPT "oik_parsed_source" which is used to store the parsed version of an API or file.
* Deleted: Removed some unused fields: _oik_api_example, _oik_api_notes
* Changed: Needed to use bw_flush() to ensure that the parsed content was the only output in the "oik_parsed_source" post_content
* Added: The "oik_parsed_source" _oik_parse_count field
* Fixed: First called function in a method was not being handled correctly
* Added: Parsed output is currently NOT paginated.
* Added: classes/class-oiksc-parsed-source.php - but not written as OO code!
* Changed: Extracted oiksc_real_file() from oiksc_load_file(). It's still a messy hack
* Fixed: "wordpress" files should display content.
* Fixed: Changed oik_pathw() to detect plugin= "wordpress" as the "wordpress" component type
* Added: Some more docBlock comments

# 1.21 
* Changed: _oik_api_plugin noderef can now refer to oik-themes as well as oik-plugins
* Added: Logic to detect the component type: "plugin" or "theme"
* Changed: oik_pathw() and other functions extended to use $component_type
* Changed: oikai_listsource() now allowed up to 40 seconds to parse a source function.
* Changed: Concoct API name strips leading :'s when no class name found
* Changed: oikai_update_oik_class() requires $class parameter
* Changed: Shortcodes updated to handle oik-themes: [apis], [classes], [file], [hooks]
* Changed: Moved from oik-batch some common logic for listapis2 and createapis
* Added: oiksc_theme_basename() - similar to plugin_basename() but for themes

# 1.20 
* Added: oiksc_token_object::getSize() method to determine function sizes. Use in oik-batch.
* Changed: oiksc_status_report() shows number of queries performed
* Changed: oikai_listsource() calls oikai_navi_source() to display paginated source code
* Fixed: Restructured oikai_api_status() - to fix [api] shortcode with no parameters

# 1.19 
* Added: [classes] shortcode ( shortcodes/oik-classlink.php )
* Added: [files] shortcode ( shortcodes/oik-filelink.php )
* Added: oiksc_status_report() implements some tracing on 'shutdown'
* Added: shortcodes/oik-api-status.php - used by oik-batch - to report some potentially useful status information
* Changed: API lists are now paginated, using bw_navi() instead of bw_list()
* Changed: Use bw_navi_ids() instead of bw_format_field() to display paginated lists for noderef fields
* Changed: docblock updates
* Changed: [file] shortcode can be used to produce a simple list of links to files.
* Changed: oikai_get_classref() will now update an oik_class instance
* Changed: oiksc_the_post_oik_class() will paginate the API list
* Fixed: Setting of _oik_fileref to a post ID rather than a serialised post
* Fixed: oiksc_get_help() invokes 'oik_add_shortcodes' action so that lazy shortcodes are loaded

# 1.18 
* Added: [api] shortcode to produce inline API lists
* Added: shortcodes/oik-api.php to handle the [api] shortcode
* Added: shortcodes/oik-api-status.php to display overall site status; special case of [api] shortcode with no parameters
* Changed: oikai_link_to_wordpress() now links to wp-a2z.com, and supports methods
* Changed: Improved formatting of T_WHITESPACE and T_DOC_COMMENT

# 1.17 
* Fixed: New logic to create the first line of code for a temporary function. oiksc_function_loader::create_dummy_function_line()
* Fixed: Reduced notify message when function does not have parameters
* Fixed: Now escape HTML in docblock short and long descriptions
* Fixed: Removed unnecessary span tags appearing before the function source
* Changed: oikai_list_callers() now find "any" post type for a caller; ie. oik_file and oik_api
* Changed: oikho_listhooks() accepts $atts, to allow lists to be shown as ordered ( i.e. numbered )

# 1.16 
* Added: columns and titles for oik_file CPT
* Fixed: fields for oik_class, oik_api and oik_hook
* Fixed: setting of _oik_fileref to a post ID not a complete post. See oiksc_get_oik_fileref()
* Changed: oikai_listapis() accepts $atts, to allow lists to be shown as ordered ( i.e. numbered )

# 1.15 
* Added: "oik_file" custom post type
* Added: [file] shortcode to display a file's contents excluding implements classes, methods and APIs ( shortcodes/oik-file.php )
* Added: admin/oik-files.php, classes/class-oiksc-file-loader.php
* Added: _oik_fileref field to eventually replace _oik_api_source, _oik_hook_source
* Added: oiksc_ajax_oiksc_create_file() to define the plugins PHP files and determine callees, hook associations and invocations.
* Changed: Extended logic to display callers/callees to work with "oik_file"  (see oikho_list_callers )
* Changed: Shortcodes registered in response to "oik_add_shortcodes" action
* Changed: oiksc_ajax_oiksc_create_api() now supports adding classes which don't have any methods
* Changed: oiksc_ajax_oiksc_create_api() now caters for special plugin "wordpress"
* Fixed: _oiksc_list_classes2() caters for T_CURLY_OPEN
* Added: oik_pathw() helper function that detects the "wordpress" plugin
* Changed: oikai_syntax_source() to handle "oik_file" processing; when a file is being loaded we don't automatically prepend <?php
* Fixed: Incorrect processing when apply_filters() is invoked during string concatenation
* Fixed: oikai_handle_token2() invokes esc_html() when the token has not been replaced by a link or is not a comment.

# 1.14 
* Fixed: Rework oik options > Create APIs to cater for classes and replacement file and API listing routines

# 1.13 
* Changed: _oik_sc_the_title_cb is now implemented as a 'virtual' field
* Changed: Dependencies. Requires oik-sc-help, oik v2.3-alpha and oik-fields v1.36
* Fixed: Shouldn't crash if oik-plugins is not activated
* Changed: Comments are not allowed on oik_sc_param, oik_api and oik_hook post types

# 1.12 
* Added: Post type support 'publicize' to "oik_shortcodes" and "shortcode_examples"
* Added: Expand during 'the_title' processing checkbox for shortcodes.
* Changed: Method for filtering / sanitizing post_titles containing shortcodes. Now filters on 'wp_insert_post_data'
* Changed: Improved dependency checking.

# 1.11 
* Added: Logic to add default display for an oik_class post type
* Added: Logic to handle variable hook names
* Changed: [bw_api] shortcode syntax display now performs two passes: first to find links, second to display them
* Changed: No longer uses get_plugin_files() - replaced by _oiksc_get_php_files()
* Changed: oikai_add_callee() needs to handle the first API, if it's not itself
* Changed: Added CSS to style a T_VARIABLE
* Changed: Link to WordPress now links to http://developer.wordpress.org/reference/functions. Logic for classes and methods to be added.
* Changed: Commented out some bw_trace2() calls
* Fixed: docBlocks are not carried forward to functions which don't have them

# 1.10.04.25 
* Fixed: Support function names made up of letters from 'function'
* Fixed: Support public methods... what are the other values... private, static

# 1.10.04.23 
* Added: oik_class CPT for registering PHP classes
* Added: API type "method" - for class methods
* Added: oik_api has optional noderef to oik_class: _oik_api_class
* Added: oikai_pseudo_reflect() to "safely" create reflection functions
* Changed: Logic now supports funcnames in format class::methodname - representing a class method
* Changed: New logic for parsing source files to list classes, methods and functions.
* Added: oikai_get_classref() automatically registers new classes and their parents, when detected

# 1.10 
* Fixed: Correctly sets plugin ref for hooks

# 1.09 
* Changed: Minor improvements to formatting for docblocks for action and filter hook invocations.
* Changed: Records plugin name and filename for action and filter hook invocations.
* Changed: oik_api and oik_hook now have archives.
* Added: Ability to update an "oik_hook"; oiksc_update_oik_hook()
* Changed: Link to WordPress API reverted to visiting codex.wordpress.org/Function_Reference/function_name

# 1.08 
* Added: post type shortcode_example with noderef to oik_shortcodes

# 1.07 
* Added: support for ajax request from non-logged in users. Requires valid api key to work
* Changed: Improved support single quotes in [bw_api]

# 1.06 
* Fixed: oiksc_handle_association_differences() not allowing multiple entries for _oik_hook_calls
* Changed: oiksc_handle_association_differences() now supports a force parameter
* Changed: _oik_api_type is now #optional
* Changed: _oik_api_calls and _oik_api_hooks are not displayed by [bw_fields] by default - as already shown by [apis] shortcode
* Changed: _oik_api_example and _oik_api_notes are not displayed by [bw_fields] by default... to be deprecated

# 1.05 
* Added: oik_hook custom post type to document WordPress action and filter hooks
* Added: [hooks] shortcode
* Changed: Removed _oik_sc_example textarea field
* Added: Logic to discover the hooks when parsing a function to create an API
* Added: "Invoked by" and "Call hooks" sections for APIs
* Fixed: Help and syntax information for [apis] shortcode

# 1.04.0421 
* Changed: Now shares oikp_columns_and_titles() with oik-plugins

# 1.04.0315 
* Added: APIs now include links to the APIs they call
* Added: [apis] shortcode now displays Called by and Calls list
* Added: function oikai_handle_token_T_STRING() now invokes an eponymous action to populate the API calls list
* Added: [bw_api] can now be used to build dynamic documentation - using the enclosed content form of the shortcode
* Added: Function ncr2ent() which performs the opposite of the WordPress ent2ncr() function
* Fixed: API importer ( oik options > Create APIs ) should process APIs from the first file in plugin's file list

# 1.04.0226 
* Added: plugin name and API type columns for oik_api admin page and [bw_table] shortcode
* Added: filter to cater for shortcodes in API titles

# 1.04.0224 
* Added: syntax highlighting for PHP code
* Added: includes links to API documentation: PHP, WordPress or oik_api
* Fixed: [bw_api] shortcode now checks for the implementing file for an API
* Fixed: Ajax server returns the result if the plugin is not defined or parameters are missing

# 1.04.0222 
* Changed: Improved PHPdoc style comments
* Added: screenshots

# 1.04.0205 
* Added: ajax interface for authorised users to add/update API definitions
* Changed: API post title now includes the first line of the 'description'
* Tested: with WordPress 3.5.1

# 1.04 
* Added: Create APIs admin page to create an oik_api by selecting from lists
* Added: API now supports type field: shortcode, filter, action, public, private, deprecated, undefined
* Changed: Create shortcodes creates APIs of type "shortcode"

# 1.03.1115.1704
* Added: Added help and syntax help for the [codes] shortcode
* Added: Started creating code that will work when the parameter name is omitted
* Fixed: some syntax and fatal errors due to sloppy coding/testing.

# 1.03 
* Added: [api] and [codes] shortcodes - to create links to related APIs and shortcodes

# 1.02 
* Added: Support for requests for help for a shortcode
* Added: oik_shortcodes are now associated to their implementing function
* Changed: Automatically create an oik_api when adding an oik_shortcode using Create shortcodes

# 1.01 
* Fixed: Needed oik_require( "includes/bw_posts.inc" );

# 1.0 
* Changed: Removed oik_sc_mapping post type; new code only needs oik_shortcodes and oik_sc_param
* Added: oik_shortcodes responds to 'the_content' to auto-populate the display
* Added: [bw_api] shortcode to display the syntax for a shortcode

# 0.1 
* Added: First version on oik-plugins.co.uk


## Further reading 
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik)
**"the oik plugin - for often included key-information"**

For more information about the WordPress Code Reference

http://make.wordpress.org/docs/2013/03/07/better-wordpress-code-reference/

Where it's supposed to reside:

http://developer.wordpress.org/

How to document an action or filter hook:

http://make.wordpress.org/core/handbook/inline-documentation-standards/php-documentation-standards/#4-hooks-actions-and-filters

WP-parser:

https://github.com/rmccue/WP-Parser


Places where you can find Function reference material:

http://api.wordpress.org/core/handbook/1.0/?function=__return_true&version=3.6.1&locale=en_US&redirect=1
http://api.wordpress.org/core/handbook/1.0/?function=_return_true&version=3.6.1&locale=en_US&redirect=1


http://codex.wordpress.org/Function_Reference/_return_true




