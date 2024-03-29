<?php // (C) Copyright Bobbing Wide 2019,2020, 2021, 2022

if ( PHP_SAPI === "cli" ) {
	//oiksc_update_blocks_loaded();
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
function oiksc_update_block( $block_type_name, $keywords, $category, $parent, $variation ) {
	if ( is_array( $keywords) ) {
		$keywords_string=implode( ',', $keywords );
	} else {
		$keywords_string = $keywords;
	}
	$block_keywords = "$block_type_name $keywords_string $category";
	echo "Updating: $block_keywords" . PHP_EOL;
	$post = array();
	oik_require( "includes/bw_posts.php" );

	$post = oiksc_get_block( $block_type_name, $parent, $variation );
	if ( $post ) {

		//$post_id = wp_update_post( $post );
		//echo "updated: " . $post_id;
		echo "Updating" . $post->post_title;
		oiksc_update_block_yoastseo( $post, $keywords );
		oiksc_update_block_keywords( $post, $keywords );
		oiksc_update_block_category( $post, $category );
		echo PHP_EOL;
	} else {
		echo "Block does not exist: " . $block_type_name .":". $parent .":". $variation;
		echo PHP_EOL;
		echo "It needs to be created first";
		echo PHP_EOL;
	}
	//$ID = oiksc_update_shortcode();
	bw_flush();
}


/**
 * Gets a block by its meta data value and parent.
 *
 * @param $block_type_name - ignored if parent is not 0
 * @param int $parent - pass a non-zero parent to access the variation
 * @param null $variation - used if parent is set
 * @return mixed|null
 */
function oiksc_get_block( $block_type_name, $parent=0, $variation=null ) {
		bw_trace2();
		if ( $parent ) {

			$args  = array(		"post_type"    => "block"
			,		"meta_key"     => "_block_variation"
			,		"numberposts" => 1
			,		"meta_value"   => $variation
			, 'post_parent' => $parent
				, 'exclude' => -1
			);

		} else {
			$args=array(
				"post_type"   =>"block"
			,	"meta_key"    =>"_block_type_name"
			,	"numberposts"=>1
			,	"meta_value"  =>$block_type_name
			,	'post_parent' =>$parent
			, 'exclude' => -1
			);
		}
		$posts = bw_get_posts( $args );
		if ( $posts ) {
			$post = $posts[0];
		} else {
			$post = null;
			bw_trace2($args, "No result", true, BW_TRACE_ERROR);
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








