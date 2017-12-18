<?php // (C) Copyright Bobbing Wide 2017

/**
 * Syntax: oikwp set_compatible_up_to.php url=domain
 *
 * from the oik-shortcodes/admin directory
 * 
 * See [github bobbingwide oik-fields issues 23]
 * which became oik-shortcodes issue #50
 *
 */

if ( PHP_SAPI !== "cli" ) { 
	die( "norty" );
}

/**
 * For each post type to which the "compatible_up_to" taxonomy is associated
 * reduce to just one term, the last term, or set it based on the publication date.
 *
 */
set_compatible_up_to();


function set_compatible_up_to() {
	$atts = array( "post_type" =>  "oik_pluginversion,oik_premiumversion,oik_themeversion,oik_themiumversion" 
							, "numberposts" => -1
							);
	oik_require( "includes/bw_posts.php" );
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
		if ( $term_id ) {
			wp_set_object_terms( $post->ID, $term_id, "compatible_up_to", false );
		}
		
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
		
		$release = get_release( $post );
		$term_id = get_term_by_name( $release );
		echo  " $release " ;
		if ( !$term_id ) {
			echo " NOT SET";
		}	
	}	
	return $term_id;
}


function release_dates() {
	static $releases;
	if ( is_null( $releases ) ) {
		$releases = array();
		$releases['2.9'] = "2009-12-18";
		$releases['3.0'] = "2010-06-17";
		$releases['3.1'] = "2011-02-23";
		$releases['3.2'] = "2011-04-04";
		$releases['3.3'] = "2011-12-12";
		$releases['3.4'] = "2012-06-13";
		$releases['3.5'] = "2012-12-11";
		$releases['3.6'] = "2013-08-01";
		$releases['3.7'] = "2013-10-24";
		$releases['3.8'] = "2013-12-12";
		$releases['3.9'] = "2014-04-16";
		$releases['4.0'] = "2014-09-04";
		$releases['4.1'] = "2014-12-12";
		$releases['4.2'] = "2015-04-03";
		$releases['4.3'] = "2015-08-18";
		$releases['4.4'] = "2015-12-08";
		$releases['4.5'] = "2016-04-12";
		$releases['4.6'] = "2016-08-16";
		$releases['4.7'] = "2016-12-06";
	}	
	return( $releases );
}

/**
 * Returns the release based on the post's creation date
 */
function get_release( $post ) {
	$releases = release_dates();
	$released = null;
	foreach ( $releases as $release => $release_date ) {
		if ( $release_date <= $post->post_date ) {
			$released = $release;
		}
		
	}
	return( $released );
}

function get_term_by_name( $release ) {
	$term = get_term_by( "name", $release, "compatible_up_to" );
	if ( $term ) {
		$term_id = $term->term_id;
	} else {
		$term_id = null;
	}
	return $term_id;	

}


	
	
