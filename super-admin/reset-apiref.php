<?php
/**
 * @copyright Bobbing Wide 2019
 * @package oik-shortcodes
 * @subpackage API ref
 *
 *
 * Syntax:
 *  cd oik-shortcodes/super-admin
 *  oikwp reset-apiref url=subdomain.wp.a2z
 *
 * where subdomain = develop, woocommerce, jetpack etc
 *
 */
if ( PHP_SAPI !== 'cli' ) {
	die();
}
global $wpdb;

function run_query( $query ) {
	global $wpdb;
	$request       = $wpdb->query( $query );
	$results = $wpdb->get_results( $request );
	print_r( $results );
}

echo "Are you sure you want to completely reset the API ref for " ;
echo $_SERVER['SERVER_NAME'];
echo PHP_EOL;
$response = oikb_get_response( "Continue?", true );

$query = "select count(*), post_type from $wpdb->posts ";
$query .= "where post_type in ( 'oik_api', 'oik_class', 'oik_file', 'oik_hook', 'oik_parsed_source') ";
$query .= "group by post_type";

run_query( $query );


$query = "delete from $wpdb->posts ";
$query .= "where post_type in ( 'oik_api', 'oik_class', 'oik_file', 'oik_hook', 'parsed_source' )";

run_query( $query );

/**
SELECT count(*), meta_key FROM `wp_3_postmeta` where post_id not in ( select distinct ID from wp_3_posts ) group by meta_key
*/
$query = "select count(*), meta_key from $wpdb->postmeta ";
$query .= "where post_id not in ";
$query .= "( select distinct ID from $wpdb->posts ) ";
$query .= "group by meta_key";
run_query( $query );


$query = "delete from $wpdb->postmeta ";
$query .= "where post_id not in ";
$query .= "( select distinct ID from $wpdb->posts ) ";

run_query( $query );
/*
What about taxonomies?

*/