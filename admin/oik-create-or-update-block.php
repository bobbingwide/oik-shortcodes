<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2019, 2020
 */

/**
 * Implements "oiksc_create_or_update_block" request
 *
 * To create or update a block definition for a WordPress block editor block.
 * Now supports creating blocks which are variations.
 *
 * Fields:
 * block
 *
 * https://blocks.wp.a2z/wp-admin/admin-ajax.php?action=oiksc_create_or_update_block
 * &title=Search
 * &name=default
 * &description=Help%20visitors%20find%20your%20content.
 * &component=gutenberg
 * &keywords=
 * &variation=core%2Fsearch
 * &icon=someverylongstring
 */
function oiksc_lazy_create_or_update_block() {

	$title = bw_array_get( $_REQUEST, 'title');
	$block_type_name = bw_array_get( $_REQUEST, 'name');
	$component = bw_array_get( $_REQUEST, 'component');
	$keywords = bw_array_get( $_REQUEST, 'keywords');
	$category = bw_array_get( $_REQUEST, 'category');
	$icon = bw_array_get( $_REQUEST, 'icon');
	$description = bw_array_get( $_REQUEST, 'description' );
	$variation = bw_array_get( $_REQUEST, 'variation');

	bw_trace2( $icon, 'icon' );
	e( esc_html( $icon ));


	oik_require( 'admin/oik-update-blocks.php', 'oik-shortcodes');
	oik_require( 'admin/oik-create-blocks.php', 'oik-shortcodes');
	oik_require( "includes/bw_posts.php" );

	kses_remove_filters();

	$parent_ID = 0;
	if ( $variation ) {
		$parent = oiksc_get_block( $variation, 0, null );
		if ( !$parent ) {
			e( "No parent block found for variation: $variation $block_type_name ");
			return;
		} else {
			$parent_ID = $parent->ID;
			e( "Parent is: $parent_ID" );

		}
		$saved = $block_type_name;
		$block_type_name = $variation;
		$variation = $saved;
	}

	$post = oiksc_get_block( $block_type_name, $parent_ID, $variation );
	if ( null === $post ) {
		oiksc_create_block( $block_type_name, $title, $component, $icon, $description, $parent_ID, $variation );
	}
	oiksc_update_block( $block_type_name, $keywords, $category, $parent_ID, $variation );
	$post = oiksc_get_block( $block_type_name, $parent_ID, $variation );

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