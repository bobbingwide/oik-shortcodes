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
	echo $query;
	echo PHP_EOL;
	$response = oikb_get_response( 'Continue?', true );
	if (  $response ) {
		$results = $wpdb->get_results( $query );
		print_r( $results );
	}
}

echo "Are you sure you want to completely reset the API ref for " ;
echo $_SERVER['SERVER_NAME'];
echo PHP_EOL;
$response = oikb_get_response( "Continue?", true );
echo "$response." . PHP_EOL;
if ( !$response ) {
	die();
}

$query = "select count(*), post_type from $wpdb->posts ";
$query .= "where post_type in ( 'oik_api', 'oik_class', 'oik_file', 'oik_hook', 'oik_parsed_source') ";
$query .= "group by post_type";

run_query( $query );


$query = "delete from $wpdb->posts ";
$query .= "where post_type in ( 'oik_api', 'oik_class', 'oik_file', 'oik_hook', 'oik_parsed_source' )";

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

from https://www.shawnhooper.ca/2015/10/22/cleaning-up-unused-terms-in-wordpress-database-in-mysql/
*/
/*
Step 1: Delete Term Relationships
I had already deleted all posts and postmeta, but the links between those post IDs and my taxonomies still existed.
*/
$query = "select count(*) FROM $wpdb->term_relationships ";
$query .= "WHERE object_id NOT IN (SELECT ID FROM $wpdb->posts)";
run_query( $query );

$query = "delete FROM $wpdb->term_relationships ";
$query .= "WHERE object_id NOT IN (SELECT ID FROM $wpdb->posts)";
run_query( $query );

/*
Step 2: Update the Term Counts
The next step is to update the term counts for each taxonomy.
In deleting the hundreds of posts that I did, many of my terms would now be used 0 times, but others would still have at least one post in them.
This statement will clean all of that up.
*/

$query = "UPDATE $wpdb->term_taxonomy tt ";
$query .= "SET count = (SELECT count(p.ID) ";
$query .= "FROM $wpdb->term_relationships tr ";
$query .= "LEFT JOIN wp_posts p ON p.ID = tr.object_id ";
$query .= "WHERE tr.term_taxonomy_id = tt.term_taxonomy_id)";
run_query( $query );

/*
Step 3: Delete Unused Terms
Finally, I ran the following statement to delete all terms that were no longer in use ( they have a count of 0 )
This leaves the rows in the term_taxonomy table.
*/
$query = "DELETE FROM $wpdb->terms a ";
$query .= "INNER JOIN $wpdb->term_taxonomy b ";
$query .= "ON a.term_id = b.term_id ";
$query .= "WHERE b.count = 0";
run_query( $query );
