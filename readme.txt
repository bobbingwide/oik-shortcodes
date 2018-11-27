=== oik shortcodes server ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: oik, fields, custom post types, shortcodes, APIs, hooks, [bw_api], [apis], [hooks], [codes]
Requires at least: 4.9.8
Tested up to: 5.0-RC1
Gutenberg compatible: Testing
Stable tag: 1.30.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
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
* [md] - markdown

It provides an admin page where APIs can be added
BUT is more useful when invoked using oik-batch, a WP-CLI like interface 

In order for the Calls and Called by trees to be maintained you have to process each API twice.


Features
* Advanced shortcodes to display content
* Parsed APIs and files are stored to improve display performance.  (v1.22)
* Parses WordPress core, plugins and themes (v1.20)
* Pagination of long lists
* 'Compatible with Shortcake' checkbox? 


New in version 1.22

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

== Installation ==
1. Upload the contents of the oik-shortcodes plugin to the `/wp-content/plugins/oik-shortcodes' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Define your content using wp-admin or the Ajax interface

== Frequently Asked Questions ==
= Where is the FAQ? =
[oik FAQ](https://www.oik-plugins.com/oik/oik-faq)

= Can I get support? = 
Yes - see above 

== Screenshots ==
1. Editing an oik_api - showing the API type select list
2. Editing an oik_shortcode  

== Upgrade Notice ==
= 1.30.1 =
Fixes missing dynamic block problem with Gutenberg and Yoast SEO active.

= 1.30.0 = 
Upgrade for peaceful coexistence with the new block editor. 
 
= 1.29.0 =
Upgrade for compatibility with oik v3.2.3 

= 1.28.0 = 
Better supports actual usage in wp-a2z.org and oik-plugins

= 1.27.9 =
Now supports complete reparse of a component. Tested up to WordPress 4.7.1 and WordPress Multisite

= 1.27.8 = 
Contains improvements, changes and fixes for oik-plugins and WP-a2z. Tested up to WordPress 4.6 and WordPress Multisite

= 1.27.7 = 
Contains improvements and changes for oik-plugins and WP-a2z. Tested up to WordPress 4.6-RC2

= 1.27.6 =
Contains improvements and changes for oik-plugins and WP-a2z. Tested up to WordPress 4.5.3

= 1.27.5 = 
Contains improvements and changes for oik-plugins and WP-a2z. Tested up to WordPress 4.5.2

= 1.27.4 =
A multitude of improvements and changes for oik-plugins and WP-a2z.	 Tested up to WordPress 4.5

= 1.27.3 =
Improvements for oik-plugins and WP-a2z.

= 1.27.2 =
Improvements for oik-plugins and WP-a2z.

= 1.27.1 =
Improvements for oik-plugins and WP-a2z. Fixes missing information for classes and shortcodes.

= 1.27 = 
Improvements for oik-plugins.

= 1.26 =
Improvements for WP-a2z and oik-plugins. 

= 1.25 =
Bug fixes for wp-a2z.com and oik-plugins. Added pagination for "parsed_source" display. 

= 1.24 =
Bug fixes for wp-a2z.com. Improved shortcodes for when _plugin_ref field is available.

= 1.23 =
Bug fixes for wp-a2z.com. Added [hook] shortcode

= 1.22 =
Performance improvements for wp-a2z.com. Some bug fixes.

= 1.21 =
Improvements for wp-a2z.com - to document the themes

= 1.20 =
Improvements for wp-a2z.com - limit number of lines of source to be displayed per page

= 1.19 =
Improvements for wp-a2z.com 

= 1.18 = 
Formatting improvements for code section. New [api] shortcode for easy links.

= 1.17 = 
Fixes for wp-a2z.com to support Jetpack and display of "oik-file" callers

= 1.16 = 
Fix for _oik_fileref setting

= 1.15 =
To attempt to bring in line with WP-Parser for "wordpress" plugin

= 1.14 = 
Fixes for oik options > Create APIs

= 1.13 = 
Further support for shortcodes in titles. Tested with WordPress 3.9.1

= 1.12 = 
Improved support for shortcodes in titles. Tested with WordPress 3.9.1

= 1.11 = 
Now supports OO classes and methods

= 1.10.0423
Intermediate version for OO support

= 1.10 = 
Quick fix

= 1.09 =
Now supports 'archive' for oik_api and oik_hook and updates to oik_hook posts

= 1.08 =
Required for using [bw_related] to show shortcode examples.

= 1.07 = 
Required for sites where wp-login.php is protected by an extra page with complex cookie

= 1.06 = 
Required for rowlandscastlewebdesign.com/phphants as well

= 1.05 =
Required for oik-plugins.com and oik-plugins.co.uk 

= 1.04.0421 =
Pre-requisite to safe install of oik-plugins v1.1.0421

= 1.04.0315 = 
Depends on oik v2.0-alpha.0315 and oik-fields v1.18.0315 for multiple select noderef fields

= 1.04.0226 = 
Depends on oik v2.0-alpha, oik fields v1.18 and oik-plugins v1.1

= 1.04.0224
Depends on oik v1.18, oik fields v1.18 and oik-plugins v1.1

= 1.04.0222
Depends on oik v1.17, oik fields v1.18 and oik-plugins v1.1

= 1.04.0405 
Depends on oik v1.17

= 1.04 = 
Depends on oik v1.17

= 1.03.1115.1704
Depends on oik v1.17.1103.1626
 
= 1.03 = 
Depends on oik v1.17.1103.1626

= 1.02 = 
Depends on oik v1.17.1101.1850

= 1.01 = 
Includes a fix for the Create shortcode admin page
= 1.0 =
First version for oik-plugins.com, depends on oik v1.17 and oik-fields v1.18, oik-plugins v1.1

== Changelog ==
= 1.30.1 = 
* Changed: Attempt to improve performance by restricting output to bots [github bobbingwide oik-shortcodes issue 60]
* Fixed: oik_get_the_excerpt needs to allow for Gutenberg comments [github bobbingwide oik-shortcodes issue 59]
* Fixed: Missing dynamic blocks when using Gutenberg and Yoast SEO without meta descriptions [github bobbingwide oik-shortcodes issue 58]
* Tested: With WordPress 5.0-RC1
* Tested: With Gutenberg v4.5.1
* Tested: With PHP 7.1 and 7.2

= 1.30.0 = 
* Changed: Improve _oik_sc_func field processing in the editor's meta box [github bobbingwide oik-shortcodes issue 52]
* Changed: Support WordPress 5.0 and the new block editor [github bobbingwide oik-shortcodes issue #58]
* Tested: With WordPress 4.9.5 and 5.0-alpha and WordPress Multisite
* Tested: With PHP 7.1 and 7.2 
* Tested: With Gutenberg 2.7.0

= 1.29.0 = 
* Changed: Dependent upon oik v3.2.3, oik-plugins v1.16.0, oik-themes v1.3.0 and oik-fields v1.50.1 [github bobbingwide oik-shortcodes issue 57]
* Tested: With WordPress 4.9.1 and WordPress Multisite 
* Tested: With PHP 7.0, 7.1 and 7.2

= 1.28.0 = 
* Added: Batch routine to create _oik_hook_plugin post meta for oik_hook posts [github bobbingwide oik-shortcodes issues 48]
* Added: Set only one value for compatible_up_to based on publication date [github bobbingwide oik-shortcodes issues 50]
* Changed: API ref field temporarily not displayed [github bobbingwide oik-shortcodes issues [52]
* Changed: Registration of oik_shortcodes [github bobbingwide oik-shortcodes issues 55]
* Fixed: Support PHP 7.1 - replace '/tmp' by sys_get_temp_dir() [github bobbingwide oik-shortcodes issues 47]
* Fixed: Use php_sapi() instead of PHP_SAPI to resolve environmental problem [github bobbingwide oik-shortcodes issues 48]
* Fixed: paired_replacements() should not convert a single ' * ' to '<em>/em>' [github bobbingwide oik-shortcodes issues 46]
* Tested: With WordPress 4.8.2 and 4.9-beta3
* Tested: With PHP 7.0 and PHP 7.1

= 1.27.9 =
* Added: Support complete reparse with previous=0 [github bobbingwide oik-shortcodes issue 34]
* Fixed: Avoid recursion for ID=0  [github bobbingwide oik-shortcodes issue 45]
* Fixed: oikai_format_markdown_line() expanding more shortcodes than intended [github bobbingwide oik-shortcodes issue 42]
* Fixed: paging not working for get_avatar [githib bobbingwide oik-shortcodes issue 43]
* Tested: With WordPress 4.7.1 and WordPress Multisite

= 1.27.8 =
* Added: View on Trac for WordPress plugins and themes [github bobbingwide oik-shortcodes issue 38]
* Changed: Display which function does not exist [github bobbingwide oik-shortcodes issue 40]
* Changed: Don't use site_url() for relative links for /oik_api/ [github bobbingwide oik-shortcodes issue 7]
* Changed: Enhanced hook shortcode for type '.' [github bobbingwide oik-shortcodes issue 6]
* Fixed: Better support for formatting Descriptions [github bobbingwide oik-shortcodes issue 6]
* Tested: With WordPress 4.6 and WordPress Multisite

= 1.27.7 = 
* Changed: Add links to GitHub and WordPress core's TRAC browser [github bobbingwide oik-shortcodes issue 38]
* Changed: docblock params and Readme for GitHub
* Fixed: Cater for not finding the component name [github bobbingwide oik-shortcodes issue 29]
* Fixed: oik_file display not paginated on wp-a2z.org [github bobbingwide oik-shortcodes issue 36]

= 1.27.6 = 
* Added: Automate the creation of oik_shortcodes for a component [github bobbingwide oik-shortcodes issue 28]
* Changed: Allow restart with a different previous SHA [github bobbingwide oik-shortcodes issue 21]
* Changed: Add _component_version virtual field [github bobbingwide oik-shortcodes issue 29]
* Changed: Keep links to shortcode parameters local [github bobbingwide oik-shortcodes issue 30]
* Changed: Improve logic for hooks - parsing and displaying [github bobbingwide oik-shortcodes issue 31]
* Changed: Move oiksc_yoastseo to admin/oik-yoastseo.php [github bobbingwide oik-shortcodes issue 32]
* Fixed: Requests for form oik-shortcodes/shortcode/funcname result in 404 [github bobbingwide oik-shortcodes issue 33]
* Fixed: Remove PHP 7 specific logic in oik_create_codes_get_all_shortcodes()

= 1.27.5 = 
* Added: Add logic for reprocessing APIs
* Added: Add logic to build the MD5 hash for an API
* Added: Add logic to process all registered oik-plugins
* Added: Add logic to set MD5 hash to reduce reprocessing time for unchanged functions/methods [github bobbingwide oik-shortcodes issue 17]
* Added: Batch routine to convert _oik_api_calls from string to post ID
* Added: Check if the function/method source has changed [github bobbingwide oik-shortcodes issue 17] 
* Added: Load themes as well as plugins
* Added: Logic to batch create oik-shortcodes
* Added: Only add shortcodes for selected components
* Added: QAD improvements to programmatically create oik-shortcodes
* Added: Store parse status in post meta data for each component [github bobbingwide oik-shortcodes issue 21]
* Added: [codes] shortcodes should work for oik-themese as well as oik-plugins [github bobbingwide oik-shortcodes issue 26]
* Added: add component parameter to [codes] to support child themes [github bobbingwide oik-shortcodes issues 27]
* Changed: Cater for remote git repositories when running locally [github bobbingwide oik-shortcodes issue 22]
* Changed: Convert API names to post IDs before updating _oik_api_calls post meta
* Changed: Crash when global plugin or filename is not set
* Changed: Docblock for oikai_list_callees
* Changed: More work on accessing the real file
* Changed: Need autoloading for oiksc_genesis_404()
* Changed: Only allow command line invocation
* Changed: Temporary solution for adding Genesis shortcodes
* Changed: Trace levels and docblocks
* Fixed: Docblock markdown formatting error for some functions [github bobbingwide oik-shortcodes issue 25]
* Fixed: Don't implement unnecessary filter hooks [github bobbingwide oik-shortcodes issue 9] [github bobbingwide oik-shortcodes issue 10]
* Fixed: Ensure oikp_load_plugin exists in oiksc_load_component
* Fixed: Fatal error when parsing shared library files [github bobbingwide oik-shortcodes issue 19]
* Fixed: Get SHA only in get_to_sha()
* Fixed: Improve logic for missing component
* Fixed: Still need to set global plugin and filename in some situations
* Fixed: Uncaught Error: Cannot access self:: when no class scope is active [github bobbingwide oik-shortcodes issue 23]
* Fixed: We need to load the 'git' library manually at present

= 1.27.4 =
* Added: Automate the creation of Yoast SEO post meta [github bobbingwide oik-shortcodes issue 20]
* Added: Implement 'genesis_404' action for 404 handler logic [github bobbingwide oik-shortcodes issue 18]
* Added: Improve performance of oiksc_create_api() [github bobbingwide oik-shortcodes issue 9]
* Added: Improve performance of oiksc_create_file [github bobbingwide oik-shortcodes issue 10]
* Added: Improve performance with pragmatic links [github bobbingwide oik-shortcodes issue 18]
* Added: Logic to enable AJAX pagination of parsed_source
* Added: Preload all APIs
* Added: Reflect the folder structure in the oik_file post type hierarchy [github bobbingwide oik-shortcodes issue 15]
* Added: Support the start parameter for files
* Changed: Don't display Class ref or API type if null
* Changed: First pass working with _oik_api_calls as string or post_id
* Changed: Format parameters which are arrays [[github bobbingwide oik-shortcodes issue 6]
* Changed: Improve class oiksc_function_loaded for PHP 7 [github bobbingwide oik-shortcodes issue 8]
* Changed: Improve component processing
* Changed: Improve the contents of oik_parsed_source [github bobbingwide oik-shortcodes issue 7]
* Changed: Need oiksc_autoload for oik options > create API
* Changed: Trace levels
* Changed: Various performance improvements and fixes
* Changed: autoloading improvements
* Changed: docblock improvements
* Fixed: Allow for bw_get_latest_parsed_source_by_sourceref() to return null
* Fixed: Also cater for instanceof self and new self [github bobbingwide oik-shortcodes issue 8]
* Fixed: No longer need dummy wp_enqueue_style() function
* Fixed: Parsing a theme produces "Source file not available" [github bobbingwide oik-shortcodes issue 11]
* Fixed: _oiksc_get_php_files should ignore the .git folder [github bobbingwide oik-shortcodes issue 12]
* Fixed: oiksc_update_oik_hook() allowed to run when DOING_AJAX [github bobbingwide oik-shortcodes issue 13]
* Fixed: update _oik_api_hooks even if empty [github bobbingwide oik-shortcodes issue 17]
* Fixed: use oikai_dummy_TCES( false ) for each API

= 1.27.3 =
* Changed: oik-shortcode has archive
* Added: help and syntax for [md] shortcode
 
= 1.27.2 =
* Added: Improve display of shortcode parameters ( Issue #3 )
* Changed: Better support for &#nn; characters and HTML inside strings ( Issue #4 )
* Changed: Cater for &#nn; and HTML tags in PHP comments ( Issue #4 )
* Fixed: Compatibility with WordPres SEO ( Issue #1 )
* Fixed: Notice undefined $post_id in oikai_apiref()

= 1.27.1 =
* Added: Compatible with shortcake checkbox for shortcodes
* Changed: Add $start parameter to oiksc_do_files
* Changed: Now using semantic versioning
* Fixed: Improved 'the_content' filtering for oik_shortcodes and oik_class post types
* Fixed: oikai_listfiles() needs to load shortcodes/oik-navi.php
* Fixed: oikai_oik_class_parent() needs to load admin/oik-apis.php

= 1.27 =
* Added: [md] shortcode - to format content originally written as 'markdown' 
* Changed: [codes] shortcode now supports pagination; passes the $atts parameter to oikai_listcodes() 
* Changed: AJAX create API and create file functions now invoke the "oik_loaded" action
* Changed: Dependency on oik now v2.4 or higher.

= 1.26 =
* Added: TO DO section when @todo tags are present in API docblocks
* Added: Test to see if opcache processing prevents dynamic extraction of docblock information from already loaded functions
* Changed: Add docblock_token to oiksc_token_object to support docblock stripping from files
* Changed: Now formats Descriptions using something similar to GitHub Flavoured Markdown
* Changed: Improved some docblocks now that markdown is supported
* Changed: Limit length of "Uses APIs" select box to 80 characters
* Changed: Strip docblocks that precede functions in file displays
* Changed: _oiksc_list_classes2() sets docblock_token
* Changed: Sections now ordered by title: Called by, Invoked by, Calls, Call hooks

= 1.25 = 
* Changed: Added pagination support for parsed source display.
* Changed: Commented out some bw_trace/bw_backtrace calls
* Changed: Improved some docblock comments
* Changed: New API oikai_load_from_file() to help simplify the restructured oikai_listsource()
* Changed: oik-batch createapis2 will force updates even when parsed_source timestamp is set.
* Fixed: Problem with the callees, hooks and hook associations being removed when source was dynamically reparsed.
* Fixed: Problems due to use of bw_flush() when WordPress-SEO was attempting to find the excerpt to populate social media descriptions.
* Tested: With WordPress 4.0-beta3

= 1.24 =
* Added: Support for __FUNCTION__ to define API name ( token T_FUNC_C )
* Added: oikai_concoct_api_name2() similar to create the API name from the previous tokens
* Changed: Improved [apis] shortcode to work when the current post has a _plugin_ref field
* Changed: Improved [classes] shortcode to work when the current post has a _plugin_ref field
* Changed: Improved [files] shortcode to work when the current post has a _plugin_ref field
* Changed: Improved [hooks] shortcode to work when the current post has a _plugin_ref field
* Changed: oikai_concoct_api_name() to replace $this by the current class
* Fixed: No longer creates hook associations when the API is not already defined

= 1.23 =
* Added: [hook] shortcode to display an inline link to an action or filter hook
* Added: oikai_simplify_apiname() - in case () are used within the [api] shortcode
* Fixed: call esc_html to handle HTML in parameter descriptions that otherwise cause formatting problems
* Changed: No longer calls oiksc_status_report on 'shutdown'; logic cloned to oik-bwtrace v1.21
* Changed: Commented out some trace calls
 
= 1.22 =
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

= 1.21 =
* Changed: _oik_api_plugin noderef can now refer to oik-themes as well as oik-plugins
* Added: Logic to detect the component type: "plugin" or "theme"
* Changed: oik_pathw() and other functions extended to use $component_type
* Changed: oikai_listsource() now allowed up to 40 seconds to parse a source function.
* Changed: Concoct API name strips leading :'s when no class name found
* Changed: oikai_update_oik_class() requires $class parameter
* Changed: Shortcodes updated to handle oik-themes: [apis], [classes], [file], [hooks]
* Changed: Moved from oik-batch some common logic for listapis2 and createapis
* Added: oiksc_theme_basename() - similar to plugin_basename() but for themes 
 
= 1.20 =
* Added: oiksc_token_object::getSize() method to determine function sizes. Use in oik-batch. 
* Changed: oiksc_status_report() shows number of queries performed
* Changed: oikai_listsource() calls oikai_navi_source() to display paginated source code
* Fixed: Restructured oikai_api_status() - to fix [api] shortcode with no parameters

= 1.19 = 
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

= 1.18 = 
* Added: [api] shortcode to produce inline API lists
* Added: shortcodes/oik-api.php to handle the [api] shortcode
* Added: shortcodes/oik-api-status.php to display overall site status; special case of [api] shortcode with no parameters 
* Changed: oikai_link_to_wordpress() now links to wp-a2z.com, and supports methods
* Changed: Improved formatting of T_WHITESPACE and T_DOC_COMMENT 

= 1.17 = 
* Fixed: New logic to create the first line of code for a temporary function. oiksc_function_loader::create_dummy_function_line() 
* Fixed: Reduced notify message when function does not have parameters
* Fixed: Now escape HTML in docblock short and long descriptions
* Fixed: Removed unnecessary span tags appearing before the function source
* Changed: oikai_list_callers() now find "any" post type for a caller; ie. oik_file and oik_api
* Changed: oikho_listhooks() accepts $atts, to allow lists to be shown as ordered ( i.e. numbered )

= 1.16 =
* Added: columns and titles for oik_file CPT
* Fixed: fields for oik_class, oik_api and oik_hook
* Fixed: setting of _oik_fileref to a post ID not a complete post. See oiksc_get_oik_fileref()
* Changed: oikai_listapis() accepts $atts, to allow lists to be shown as ordered ( i.e. numbered )

= 1.15 = 
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

= 1.14 = 
* Fixed: Rework oik options > Create APIs to cater for classes and replacement file and API listing routines

= 1.13 =
* Changed: _oik_sc_the_title_cb is now implemented as a 'virtual' field
* Changed: Dependencies. Requires oik-sc-help, oik v2.3-alpha and oik-fields v1.36
* Fixed: Shouldn't crash if oik-plugins is not activated
* Changed: Comments are not allowed on oik_sc_param, oik_api and oik_hook post types 

= 1.12 = 
* Added: Post type support 'publicize' to "oik_shortcodes" and "shortcode_examples"
* Added: Expand during 'the_title' processing checkbox for shortcodes.
* Changed: Method for filtering / sanitizing post_titles containing shortcodes. Now filters on 'wp_insert_post_data'
* Changed: Improved dependency checking. 

= 1.11 = 
* Added: Logic to add default display for an oik_class post type
* Added: Logic to handle variable hook names
* Changed: [bw_api] shortcode syntax display now performs two passes: first to find links, second to display them
* Changed: No longer uses get_plugin_files() - replaced by _oiksc_get_php_files()
* Changed: oikai_add_callee() needs to handle the first API, if it's not itself
* Changed: Added CSS to style a T_VARIABLE
* Changed: Link to WordPress now links to http://developer.wordpress.org/reference/functions. Logic for classes and methods to be added.
* Changed: Commented out some bw_trace2() calls
* Fixed: docBlocks are not carried forward to functions which don't have them

= 1.10.04.25 =
* Fixed: Support function names made up of letters from 'function'
* Fixed: Support public methods... what are the other values... private, static

= 1.10.04.23 =
* Added: oik_class CPT for registering PHP classes
* Added: API type "method" - for class methods
* Added: oik_api has optional noderef to oik_class: _oik_api_class
* Added: oikai_pseudo_reflect() to "safely" create reflection functions
* Changed: Logic now supports funcnames in format class::methodname - representing a class method
* Changed: New logic for parsing source files to list classes, methods and functions. 
* Added: oikai_get_classref() automatically registers new classes and their parents, when detected

= 1.10 =
* Fixed: Correctly sets plugin ref for hooks

= 1.09 =
* Changed: Minor improvements to formatting for docblocks for action and filter hook invocations.
* Changed: Records plugin name and filename for action and filter hook invocations. 
* Changed: oik_api and oik_hook now have archives.
* Added: Ability to update an "oik_hook"; oiksc_update_oik_hook()
* Changed: Link to WordPress API reverted to visiting codex.wordpress.org/Function_Reference/function_name

= 1.08 =
* Added: post type shortcode_example with noderef to oik_shortcodes

= 1.07 = 
* Added: support for ajax request from non-logged in users. Requires valid api key to work
* Changed: Improved support single quotes in [bw_api]

= 1.06 = 
* Fixed: oiksc_handle_association_differences() not allowing multiple entries for _oik_hook_calls
* Changed: oiksc_handle_association_differences() now supports a force parameter
* Changed: _oik_api_type is now #optional
* Changed: _oik_api_calls and _oik_api_hooks are not displayed by [bw_fields] by default - as already shown by [apis] shortcode
* Changed: _oik_api_example and _oik_api_notes are not displayed by [bw_fields] by default... to be deprecated

= 1.05 = 
* Added: oik_hook custom post type to document WordPress action and filter hooks
* Added: [hooks] shortcode
* Changed: Removed _oik_sc_example textarea field
* Added: Logic to discover the hooks when parsing a function to create an API 
* Added: "Invoked by" and "Call hooks" sections for APIs
* Fixed: Help and syntax information for [apis] shortcode 

= 1.04.0421 =
* Changed: Now shares oikp_columns_and_titles() with oik-plugins

= 1.04.0315 =
* Added: APIs now include links to the APIs they call
* Added: [apis] shortcode now displays Called by and Calls list
* Added: function oikai_handle_token_T_STRING() now invokes an eponymous action to populate the API calls list
* Added: [bw_api] can now be used to build dynamic documentation - using the enclosed content form of the shortcode
* Added: Function ncr2ent() which performs the opposite of the WordPress ent2ncr() function
* Fixed: API importer ( oik options > Create APIs ) should process APIs from the first file in plugin's file list

= 1.04.0226 =
* Added: plugin name and API type columns for oik_api admin page and [bw_table] shortcode
* Added: filter to cater for shortcodes in API titles

= 1.04.0224 =
* Added: syntax highlighting for PHP code
* Added: includes links to API documentation: PHP, WordPress or oik_api
* Fixed: [bw_api] shortcode now checks for the implementing file for an API
* Fixed: Ajax server returns the result if the plugin is not defined or parameters are missing

= 1.04.0222 =
* Changed: Improved PHPdoc style comments
* Added: screenshots

= 1.04.0205 =
* Added: ajax interface for authorised users to add/update API definitions
* Changed: API post title now includes the first line of the 'description'
* Tested: with WordPress 3.5.1

= 1.04 =
* Added: Create APIs admin page to create an oik_api by selecting from lists
* Added: API now supports type field: shortcode, filter, action, public, private, deprecated, undefined
* Changed: Create shortcodes creates APIs of type "shortcode"

= 1.03.1115.1704
* Added: Added help and syntax help for the [codes] shortcode
* Added: Started creating code that will work when the parameter name is omitted
* Fixed: some syntax and fatal errors due to sloppy coding/testing.

= 1.03 =
* Added: [api] and [codes] shortcodes - to create links to related APIs and shortcodes

= 1.02 =
* Added: Support for requests for help for a shortcode
* Added: oik_shortcodes are now associated to their implementing function
* Changed: Automatically create an oik_api when adding an oik_shortcode using Create shortcodes

= 1.01 = 
* Fixed: Needed oik_require( "includes/bw_posts.inc" );

= 1.0 =
* Changed: Removed oik_sc_mapping post type; new code only needs oik_shortcodes and oik_sc_param
* Added: oik_shortcodes responds to 'the_content' to auto-populate the display
* Added: [bw_api] shortcode to display the syntax for a shortcode

= 0.1 =
* Added: First version on oik-plugins.co.uk
 

== Further reading ==
If you want to read more about the oik plugins then please visit the
[oik plugin](https://www.oik-plugins.com/oik) 
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




