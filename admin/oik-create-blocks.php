<?php // (C) Copyright Bobbing Wide 2019, 2020, 2021, 2022

if ( PHP_SAPI === "cli" ) {
	//oiksc_create_blocks_loaded();
    oiksc_list_registered_blocks();

}

/**
 * Create block entries programmatically in a batch process
 *
 * Syntax: oikwp oik-create-blocks.php blocktypename title component url=blocks.wp-a2z.org
 *
 * when in this directory, the oik-shortcodes/admin folder, as the current directory
 *
 * e.g.
 * cd oik-shortcodes/admin
 * oikwp oik-create-blocks.php oik-block/dashicon "Dash icon" oik-blocks
 * 
 */

/**
 * Creates Block and block variation entries programmatically.
 *
 */
function oiksc_create_blocks_loaded() {
	kses_remove_filters();
	$block_type_name = oik_batch_query_value_from_argv( 1, null );
	$title = oik_batch_query_value_from_argv( 2, null );
	$component = oik_batch_query_value_from_argv( 3, null );

	echo PHP_EOL;
	echo $block_type_name;
	echo PHP_EOL;
	echo $title;
	echo PHP_EOL;
	echo $component;
	echo PHP_EOL;
	$post_title = "$title - $block_type_name";
	oiksc_create_block( $block_type_name, $title, $component );

}

/**
 * Programmatically create a block post
 *
 * This used to be run in batch.
 *
 * @param string $block_type_name
 * @param string $title
 * @param string $required_component
 * @param string|null $icon
 * @param string|null $description

 */ 
function oiksc_create_block( $block_type_name, $title, $required_component, $icon=null, $description=null, $parent=0, $variation=null ) {

	oik_require( "admin/oik-apis.php", "oik-shortcodes" );
	$component_id = oiksc_get_component_by_name( $required_component );
	if ( !$component_id ) {
		echo "Component not defined: $required_component";
	}
	//$_REQUEST['_oiksc_create_shortcode'] = 'submit';
	//$_REQUEST['code'] = $shortcode;
	//$_REQUEST['plugin'] = $component_id;
	$post_title = "$title - $block_type_name";
	echo "Creating $post_title: $required_component: $component_id" . PHP_EOL;
	$post = array();

	$post = oiksc_get_block( $block_type_name, $parent, $variation );
	if ( !$post ) {
		$post = [];
		$post = array( 'post_type' => 'block'
		, 'post_title' => $post_title
		, 'post_status' => 'publish'
		);
		$post['title'] = $post_title;
		$post['post_content' ] = oiksc_create_block_content( $block_type_name, $icon, $title, $description, $variation );
		$post['post_parent'] = $parent;
		$_POST['_block_type_name'] = $block_type_name;
		$_POST['_block_variation'] = $variation;
		$_POST['_oik_sc_plugin'] = $component_id;
		//oikb_get_response( "Continue?", true );
		$post_id = wp_insert_post( $post );
		echo "Created: " . $post_id;
		echo PHP_EOL;
	} else {
		echo "Already exists: " .  $post->ID;
		echo $post->title;
		echo PHP_EOL;
	}
	//$ID = oiksc_create_shortcode();
	bw_flush();
}

/**
 * 	{"mainColor":"very-dark-gray","textColor":"very-light-gray","align":"wide","className":"has-very-light-gray-background-color is-style-default"}

 * @param $block_type_name
 *
 * @return array
 */

function oiksc_create_block_content( $block_type_name, $icon=null, $title=null, $description=null, $variation=null ) {
	$blockicon = $variation ? $block_type_name . '|' . $variation : $block_type_name;
	$atts = oiksc_block_atts_encode( [ 'className' => 'svg64',  'blockicon' => $blockicon,
	'showBlockTypeName' => false, 'showTitle' => false, 'showCategory' => false, 'showKeywords' => false ] );
	if ( $icon ) {
		$iconinfo = oiksc_icon_blockinfo( $icon, $block_type_name, $title, $description );
	} else {
		$iconinfo = oiksc_default_blockinfo();
	}
	$content = oiksc_generate_block( "oik-block/blockinfo", $atts, $iconinfo );
	$content .= oiksc_generate_block( "more", null, '<!--more-->' );
	$para = '<p class="has-background has-very-light-gray-background-color">Under Construction</p>';
	$content .= oiksc_generate_block( "paragraph", oiksc_block_atts_encode( ['backgroundColor' => 'very-light-gray'] ), $para );
	//$content .= oiksc_generate_block( "more", null, '<!--more-->' );
	$content .= oiksc_generate_block( "heading", null, "<h2>Screenshot</h2>" );
	//$content .= oiksc_generate_block( "oik-block/fields", oiksc_block_atts_encode( [ "fields" => "featured"  ] ) );
    $placeholder = oiksc_block_atts_encode( [ "placeholder" => "Paste the blocks screenshot here."]);
    $content .= oiksc_generate_block( "paragraph", $placeholder, "<p></p>");

    $content .= oiksc_generate_block( "heading", null, "<h2>Example</h2>" );
	$content .= oiksc_generate_block( "spacer", null, '<div style="height:100px" aria-hidden="true" class="wp-block-spacer"></div>' );
	$placeholder = oiksc_block_atts_encode( [ "placeholder" => "Type / to choose the sample block"]);
	$content .= oiksc_generate_block( "paragraph", $placeholder, "<p></p>");
	$content .= oiksc_generate_block( $block_type_name );
	$content .= oiksc_generate_block( "spacer", null, '<div style="height:100px" aria-hidden="true" class="wp-block-spacer"></div>' );
	$content .= oiksc_generate_block( "separator", null, '<hr class="wp-block-separator"/>');
	$content .= oiksc_generate_block( "heading", null, "<h2>Notes</h2>");

	// Can we write anything useful here such as whether or not this is an inner block.
	$content .= oiksc_generate_block( "list", null, '<ul><li>TBC</li></ul>');

	// When it's a variation then we don't need to display Variations.
	// When it's not then we don't know, programmatically, if there are variations.
	// until we edit the block.
	// $content .= oiksc_generate_block( "heading", null, "<h2>Variations</h2>");

	//echo $content;
	//oikb_get_response( "Continue?", true );
	return $content;
}

function oiksc_block_atts_encode( $atts ) {
	$block_atts = json_encode( $atts, JSON_UNESCAPED_SLASHES );
	return $block_atts;
}

function oiksc_default_blockinfo() {
	$icon = '<span class="editor-block-icon"><svg aria-hidden="true" role="img" focusable="false" class="dashicon dashicons-block-default" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M15 6V4h-3v2H8V4H5v2H4c-.6 0-1 .4-1 1v8h14V7c0-.6-.4-1-1-1h-1z"></path></svg></span>';
	$iconinfo = oiksc_icon_blockinfo( $icon, 'oik-block/blockicon', 'Block icon', 'Displays a block icon' );
	return $iconinfo;
}


/**
 * Creates the HTML for the blockicon
 *
 * We only want the block description, not the block type name, block title or category and keywords
 *
 * @param $icon
 * @param $block_type_name
 * @param null $block_title
 * @param null $block_description
 *
 * @return string
 */


function oiksc_icon_blockinfo( $icon, $block_type_name, $block_title=null, $block_description=null ) {
	$iconinfo = '<div class="wp-block-oik-block-blockinfo svg64"><div>';
	//$icon = str_replace( 'viewbox', 'viewBox', $icon );
	//$icon = str_replace( 'preserveaspectratio', 'preserveAspectRatio', $icon );
	$iconinfo .= $icon;
	$iconinfo .= '</div>';
	$iconinfo .= '<div>';
	$iconinfo .= '</div><div>';
	//$iconinfo .= $block_type_name;
	//$iconinfo .= '</div><div>';
	//$iconinfo .= $block_title;
 	//$iconinfo .= '</div><div>';
 	$iconinfo .= $block_description;
 	$iconinfo .= '</div></div>';
	return $iconinfo;
}

/**
* Return the component ID for the name
                                  *
 */
function oiksc_get_component_by_name( $component_name ) {
	$component_type = oiksc_query_component_type( $component_name );
	$plugin_post = oiksc_load_component( $component_name, $component_type );
	if ( $plugin_post ) {
		$component_id = $plugin_post->ID;
	} else {
		echo "Invalid component name: $component_name";
		$component_id = 0;
	}
	return( $component_id );
}

/** oiksc_get_block now only in oik-update-blocks.php */

/**
 * @return array

function oik_block_CPT_template() {
	$template = array();
	$template[] = ['oik-block/blockinfo', [ 'className' => 'svg64' ] ];
	$template[] = ['core/paragraph', ['backgroundColor' => 'very-light-gray'] ];
	$template[] = ['core/more' ];
	$template[] = ['core/heading', [ 'content' => "Screenshot" ] ];
	$template[] = ['oik-block/fields', [ 'fields' => 'featured'] ];
	$template[] = ['core/heading', [ 'content' => 'Example'] ];
	$template[] = ['core/spacer'];
	$template[] = ['core/paragraph', ['placeholder' => 'Type / to choose the sample block' ]];
	$template[] = ['core/spacer'];
	$template[] = ['core/separator'];
	$template[] = ['core/heading', [ 'content' => 'Notes'] ];
	$template[] = ['core/list'];

	return $template;
}
 */
function oiksc_generate_block( $block_type_name, $atts=null, $content=null ) {
	$block = "<!-- wp:$block_type_name ";
	if ( $atts ) {
		$block .= $atts;
		$block .= " ";
	}
	$block .= "-->";
	$block .= "\n";
	if ( $content ) {
		$block .= $content;
		$block .= "\n";
	}
	$block .= "<!-- /wp:$block_type_name -->";
	$block .= "\n\n";
	return $block;
}


function oiksc_list_registered_blocks() {
    echo "Registered blocks";
    $registry = WP_Block_Type_Registry::get_instance();
    $blocks = $registry->get_all_registered();
    ksort( $blocks );
    // $blocks is an enormous array of WP_Block_Type objects
    foreach ( $blocks as $name => $block ) {
        echo $name;
        echo ',';
        echo $block->title;
        echo ',';
        echo $block->description;
        echo ',';
        echo count( $block->variations);
        echo PHP_EOL;
    }

    //print_r( $blocks );
}

	





