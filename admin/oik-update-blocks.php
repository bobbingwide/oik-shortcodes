<?php // (C) Copyright Bobbing Wide 2019

if ( PHP_SAPI !== "cli" ) {
	die();
}

/**
 * Update block entries programmatically in a batch process
 *
 * Syntax: oikwp oik-update-blocks.php blocktypename "keyword 1,keyword 2,keyword 3" category url=blocks.wp-a2z.org
 *
 * when in this directory, the oik-shortcodes/admin folder, as the current directory
 *
 * e.g.
 * cd oik-shortcodes/admin
 * oikwp oik-create-blocks.php oik-block/dashicon "Dash icon" oik-blocks
 * oikwp oik-update-blocks.php oik-block/dashicon "icon,oik,dash" widgets url=blocks.wp-a2z.org
 *
 */
oiksc_update_blocks_loaded();

/**
 * update oik-shortcode entries programmatically
 *
 * After the plugins, themes and APIs have been updated we need to update the definitions of all the shortblocks
 *
 */
function oiksc_update_blocks_loaded() {
	kses_remove_filters();
	$block_type_name = oik_batch_query_value_from_argv( 1, null );
	$keywords = oik_batch_query_value_from_argv( 2, null );
	$category = oik_batch_query_value_from_argv( 3, null );

	echo PHP_EOL;
	echo $block_type_name;
	echo PHP_EOL;
	echo $keywords;
	echo PHP_EOL;
	echo $category;
	echo PHP_EOL;
	oiksc_update_block( $block_type_name, $keywords, $category );
}





/**
 * Programmatically update a block post
 *
 * This is run in batch
 *
 * @param string $block_type_name
 * @param string $keywords
 * @param string $category
 * @param string $required_component

 */
function oiksc_update_block( $block_type_name, $keywords, $category ) {

	$block_keywords = "$block_type_name $keywords $category";
	echo "Updating $block_keywords" . PHP_EOL;
	$post = array();
	oik_require( "includes/bw_posts.php" );

	$post = oiksc_get_block( $block_type_name );
	if ( $post ) {

		//$post_id = wp_update_post( $post );
		//echo "updated: " . $post_id;
		echo "Updating" . $post->post_title;
		oiksc_update_block_yoastseo( $post, $keywords );
		oiksc_update_block_keywords( $post, $keywords );
		oiksc_update_block_category( $post, $category );
		echo PHP_EOL;
	} else {
		echo "Block does not exist: " . $block_type_name;
		echo PHP_EOL;
		echo "It needs to be created first";
		echo PHP_EOL;
	}
	//$ID = oiksc_update_shortcode();
	bw_flush();
}


function oiksc_get_block( $block_type_name ) {
	$args  = array( "post_type"    => "block"
	,"meta_key"     => "_block_type_name"
	,"number_posts" => 1
	,"meta_value"   => $block_type_name
	);
	$posts = bw_get_posts( $args );
	if ( $posts ) {
		$post = $posts[0];
	} else {
		$post = null;
	}
	return $post;
}

function oiksc_update_block_yoastseo( $post, $keywords ){
	$id = $post->ID;
	$focuskw = $post->post_title;
	$metadesc = get_post_meta( $id,"_yoast_wpseo_metadesc", true );
	if ( !$metadesc ) {
		$metadesc = $post->post_title . " " . $keywords;
		update_post_meta( $id, "_yoast_wpseo_metadesc", $metadesc );
	}
	echo $metadesc;
	echo PHP_EOL;
	echo $focuskw;
	echo PHP_EOL;

	update_post_meta( $id, "_yoast_wpseo_focuskw", $focuskw );
}


function oiksc_update_block_keywords( $post, $keywords ) {
	//echo $keywords;
	$result = wp_set_post_terms( $post->ID, $keywords, "block_keyword" );
	print_r( $result );
}

function oiksc_update_block_category( $post, $category ) {
	$result = wp_set_post_terms( $post->ID, $category, "block_category" );
	print_r( $result );

}








