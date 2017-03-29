<?php // (C) Copyright Bobbing Wide 2017

/**
 * Syntax: oikwp set_compatible_up_to.php url=domain
 *
 * from the oik-shortcodes/admin directory
 * 
 * See [github bobbingwide oik-fields issues 23]
 *
 */

if ( PHP_SAPI !== "cli" ) { 
	die( "norty" );
}

/**
 * For each post type to which the "compatible_up_to" taxonomy is associated
 * reduce to just the last term
 */
set_compatible_up_to();


function set_compatible_up_to() {
	$atts = array( "post_type" =>  "oik_pluginversion,oik_premiumversion,oik_themeversion,oik_themiumversion" 
							, "numberposts" => -1
							);
	oik_require( "includes/bw_posts.inc" );
	$posts = bw_get_posts( $atts );
	foreach ( $posts as $post ) {
		
		echo $post->ID;
		echo " ";
		echo $post->post_type;
		echo " " ;
		echo $post->post_title;
		echo " ";
		$term_id = get_last_term( $post );
		echo " ";
    echo $term_id;
		echo PHP_EOL;
		// wp_set_object_terms( $post->ID, $term_id, "compatible_up_to", false );
		
	}
}

/**
 * Gets the last term for post's hierarchical taxonomy 
 * 
 * @param object $post
 * @param string $taxonomy 
 * @return integer|null the taxonomy ID
 */
function get_last_term( $post, $taxonomy = "compatible_up_to" ) {
	$terms = get_the_terms($post->ID, $taxonomy);
	if ( $terms ) {
		end( $terms );
		$term = current( $terms );
		//print_r( $term );
		$term_id = $term->term_id;
		echo $term->name;
	} else {
		$term_id = null;
		echo "NOT SET";
	}	
	return $term_id;
}
