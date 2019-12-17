<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2019
 * @package oik-shortcodes
 *
 */


function oiksc_blocks_catalogued( $args = null ) {
	$blocks_catalogued = null;
	//$id = oiksc_maybe_get_current_post_id( $args );
	$id = bw_current_post_id();
	if ( $id ) {
		$blocks_catalogued=oiksc_count_blocks_directly( $id );
	}
	return $blocks_catalogued;
}

function oiksc_maybe_get_current_post_id( $atts ) {
	$id = bw_array_get_from( $atts, "id,0", null );
	if ( null == $id ) {
		if ( is_single() || is_singular() ) {
			$id = bw_current_post_id();
		} else {
			bw_trace2();
		}
	}
	return $id;

}

/**
 * Counts the blocks for the selected plugin.
 *
 * @param integer $id post ID of the plugin.
 *
 * @return string|null
 */
function oiksc_count_blocks_directly( $id ) {
	global $wpdb;
	$query = "select count(*) from $wpdb->postmeta ";
	$query .= "where meta_key = '_oik_sc_plugin' ";
	$query .= "and meta_value = %d ";
	$query .= "and post_id in ( select distinct ID from $wpdb->posts where post_type = 'block')";
	$request = $wpdb->prepare( $query, $id );
	$rows = $wpdb->get_var( $request );
	return $rows;
}