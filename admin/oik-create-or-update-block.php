<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2019
 */

/**
 * Implements "oiksc_create_or_update_block" request
 *
 * To create or update a block definition for a WordPress block editor block
 *
 * Fields:
 * block
 */
function oiksc_lazy_create_or_update_block() {

	$title = bw_array_get( $_REQUEST, 'title');
	$block_type_name = bw_array_get( $_REQUEST, 'name');
	$component = bw_array_get( $_REQUEST, 'component');
	$keywords = bw_array_get( $_REQUEST, 'keywords');
	$category = bw_array_get( $_REQUEST, 'category');
	$icon = bw_array_get( $_REQUEST, 'icon');
	$description = bw_array_get( $_REQUEST, 'description' );

	bw_trace2( $icon, 'icon' );
	e( esc_html( $icon ));


	oik_require( 'admin/oik-update-blocks.php', 'oik-shortcodes');
	oik_require( 'admin/oik-create-blocks.php', 'oik-shortcodes');
	oik_require( "includes/bw_posts.php" );

	kses_remove_filters();

	$post = oiksc_get_block( $block_type_name );
	if ( null === $post ) {
		oiksc_create_block( $block_type_name, $title, $component, $icon, $description );
	}
	oiksc_update_block( $block_type_name, $keywords, $category );
	$post = oiksc_get_block( $block_type_name );

	//$block_icon = bw_array_get( $_REQUEST, 'icon');
	e( $block_type_name );
	e( $title );
	e( $description );
	//oik_require( 'includes/bw_formatter.php');
	//bw_field_function_edit( $post );

	$link = get_edit_post_link( $post->ID );
	if ( $link ) {
		BW_::alink( "bw_edit", $link, __( "[Edit]", "oik" ) );
	}










}