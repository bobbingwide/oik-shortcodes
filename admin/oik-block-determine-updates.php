<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2022
 * @package oik-shortcodes
 */

/**
 * Determines updates to apply for the selected component's blocks.
 *
 * @param string $content - HTML for the block content
 * @param object $parsed_block - the parsed block object
 * @param object $wp_block_object
 * @return string HTML to extend the original content
 */
function oiksc_oik_block_determine_updates( $content, $parsed_block, $wp_block_object ) {
    $extra_content = null;
    bw_trace2( $content, "content", false);
    bw_trace2( $parsed_block, "parsed_block", false);

    // Bail out early if in the editor.
    $action = bw_array_get( $_REQUEST, 'action', null );
    if ( 'edit' === $action ) {
        return $content;
    }
    $component = bw_array_get( $parsed_block['attrs'], 'component', null );
    if ( null === $component || empty( $component ) ) {
        p( "Error: Please specify component name in order to determine updates.");
    } else {
        $block_list = oiksc_get_block_list( $content, $component );
        $server_block_list = oiksc_get_server_block_list( $component );
        if ( count( $block_list ) ) {
            oiksc_compare_lists( $block_list, $server_block_list);
        }
    }
    return bw_ret();
}

/**
 * Returns an array of the query args to the AJAX request URLs.
 *
 * The original content is a set of links to run AJAX requests to create or update a block.
 * These requests contain the information needed to register the block.
 * Sadly, for blocks that aren't registered from block.json, we can't rely on information available in the server.
 *
 * @param $content
 * @param $component
 * @return array
 */
function oiksc_get_block_list( $content, $component ) {
    $content = str_replace( '</a>', "", $content);
    $block_list = [];
    $lines = explode( '<br/>', $content );

    foreach ( $lines as $line ) {
        if ( false !== strpos( $line, '<a href' )) {
            $result = [];
            $line = str_replace( '&amp;', '&', $line );
            $line = str_replace( '">', '&', $line );
            parse_str( $line, $result);
            // Discard the first and last entries in the array
            array_shift( $result );
            array_pop( $result );
            //print_r( $result );
            $block_list[] = $result;


            /*
             * Array
(
    [<a_href] => "/wp-admin/admin-ajax.php?action=oiksc_create_or_update_block
    [title] => Accordion
    [name] => coblocks/accordion
    [description] => Organize content within collapsable accordion items.
    [component] => coblocks
    [keywords] => coblocks,tabs,faq
    [category] => text
    [Create/Update:_Accordion_-_coblocks/accordion] =>
)
             */

        }
    }
    //print_r( $block_list );
    return $block_list;
}

/**
 * Checks the block status.
 *
 * @param $block
 * @param $component
 *
 */
function oiksc_check_block_status( $block, $component ) {
    p( "Checking... ");
    //print_r( $block);
    //e( '</a>');
    //array_merge( $_REQUEST, $block);
    $_REQUEST['name'] = $block['name'];
    $_REQUEST['title'] = $block['title'];
    $_REQUEST['description'] = $block['description'];
    $_REQUEST['component'] = $block['component'];
    $_REQUEST['keywords'] = $block['keywords'];
    $_REQUEST['category'] = $block['category'];
    $_REQUEST['variation'] = bw_array_get( $block, 'variation', null );
    //oiksc_create_or_update_block();
    oik_require( "admin/oik-create-or-update-block.php", "oik-shortcodes" );
    oiksc_lazy_create_or_update_block();
}

/**
 * Retrieves the server's block list for the component.
 *
 * @param $component
 * @return array $block_list
 */
function oiksc_get_server_block_list( $component ) {
    oik_require( 'admin/oik-create-blocks.php', 'oik-shortcodes' );
    oik_require( 'admin/oik-apis.php', 'oik-shortcodes');
    $component_id = oiksc_get_component_by_name( $component );
    $blocks = [];
    if ( $component ) {
        $args = [ 'post_type' => 'block',
            'meta_key' => '_oik_sc_plugin',
            'meta_value' => $component_id,
            'numberposts' => -1
            //'orderby' = '_block_type_name,_block_variation'
        ];
        $posts = bw_get_posts( $args );
        if ( $posts ) {
            foreach ( $posts as $post ) {
                $block = oiksc_get_server_block( $post, $component );
                $blocks[ $block[ 'name:variation'] ] = $block;
            }
        }
    }
    return $blocks;
}

/**
 * Returns an associative block array for a block.
 *
 * The block array has a similar structure to the client block's array.
 * Note: We have to take variations into account. So we set the "name:variation" to be a combination
 * that can be used for the key of the associative array of these blocks.
 *
 * Normal block:
 *
 * [title] => Accordion
 * [name] => coblocks/accordion
 * [description] => Organize content within collapsable accordion items.
 * [component] => coblocks
 * [keywords] => coblocks,tabs,faq
 * [category] => text
 *
 * Variation:
 *
 * [title] => (string) "100"
 * [name] => (string) "one-column"
 * [description] => (string) "Add a structured wrapper for column blocks, then add content blocks youâ€™d like to the columns."
 * [component] => (string) "coblocks"
 * [keywords] => (string) ""
 * [variation] => (string) "coblocks/row"
 *
 * Client side variation: name is the parent block, variation is the child
 *
 * [title] => (string) "100 - coblocks/row"
 * [name] => (string) "coblocks/row"
 * [description] => (NULL)
 * [component] => (string) "coblocks"
 * [variation] => (string) "one-column"
 *
 * @param WP_post $post object
 * @param string $component - Component name
 * @return array
 */
function oiksc_get_server_block( $post, $component ) {
    $block = [];
    $block['title'] = $post->post_title;
    $block['name'] = get_post_meta( $post->ID, '_block_type_name', true);
    $block['description'] = null; // Not stored yet?
    $block['component'] = $component;
    $variation = get_post_meta( $post->ID, '_block_variation', true );
    $block['variation'] = $variation;
    // Don't really need keywords or category do we?
    //if ( $variation )
    $block['name:variation'] = $block['name'] . ':' . $variation;
    return $block;
}

/**
 * Compares the client block list with the server block list.
 * Attempt to find differences and deal with them
 *
 * Client block example
 * [62] => Array

[title] => (string) "100"
[name] => (string) "one-column"
[description] => (string) "Add a structured wrapper for column blocks, then add content blocks youâ€™d like to the columns."
[component] => (string) "coblocks"
[keywords] => (string) ""
[variation] => (string) "coblocks/row"
 *
 *

 * Server block example
 * [0] => Array

[title] => (string) "100 - coblocks/row"
[name] => (string) "coblocks/row"
[description] => (NULL)
[component] => (string) "coblocks"
[variation] => (string) "one-column"

}
 */
function oiksc_compare_lists( $client_block_list, $server_block_list ) {
    e( "Comparing lists");
    $catalogued_count = 0;
    $todo_count = 0;
    $extra_count = 0;
    $newly_done_count = 0;
    bw_trace2( $client_block_list, "client block list", false, BW_TRACE_VERBOSE );
    bw_trace2( $server_block_list, "server block list", false, BW_TRACE_VERBOSE );

    foreach ( $client_block_list as $block ) {

        $variation = bw_array_get( $block, 'variation', "" );
        if ( $variation !== "" ) {
            $block_name_variation = $variation . ':' .  $block['name'];
        } else {
            $block_name_variation = $block['name'] . ":";
        }
        $matched = bw_array_get( $server_block_list, $block_name_variation, null );
        if ( $matched ) {
            //p( "Matched: $block_name_variation");
            $server_block_list[$block_name_variation]['matched'] = true;
            $catalogued_count++;
        } else {
            $title_name = $block['title'] . ' - ' . $block['name'];

            // @TODO If it's a variation then check the parent block's already there.
            if ( $newly_done_count < 5) {
                $newly_done_count++;
                oiksc_check_block_status( $block, $block['component'] );
            } else {
                p( "No match for: $block_name_variation use Create/Update. $title_name");
                $todo_count++;
            }
        }
    }
    foreach ( $server_block_list as $block ) {
        $matched = bw_array_get( $block, 'matched', false );
        if ( !$matched ) {
            p( "Extra server block: " . $block['name:variation'] . $block['title'] );
            $extra_count++;
        }
    }
    p( "Block+variations count: " . count( $client_block_list) );
    p( "Catalogued: $catalogued_count");
    p( "Newly done: $newly_done_count");
    p( "TODO: $todo_count");
    p( "Extra: $extra_count");
}