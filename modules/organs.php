<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** Functions pertaining to CPT: ORGANS ***********/

function get_cpt_organ_content( $post_id = null ) {
	
	// This function retrieves supplementary info -- the regular content template (content.php) handles title, content, featured image
	
    $info = ""; // init
    if ($post_id === null) { $post_id = get_the_ID(); }
    
    if ( $post_id === null ) { return false; }
    
    if ( !sdg_queenbee() ) {
    	// If not queenbee, show content instead of acf_form
    	// WIP
    	//$settings = array( 'fields' => array( 'venue_info_ip', 'venue_info_vp', 'venue_sources', 'venue_html_ip', 'organs_html_ip', 'organs_html_vp' ) );
    	$builder_str = get_arr_str(get_post_meta( $post_id, 'builder', true )); //$builder = get_field( 'builder', $post_id ); //
    	$info .= '<strong>builder(s)</strong>: <div class="xxx wip">'.$builder_str."</div>";
    	
    	//<div class="source venue_source wip">
    	
    	$model = get_post_meta( $post_id, 'model', true );
    	if ( $model ) { $info .= '<strong>Model</strong>: <div class="xxx wip">'.$model."</div>"; }
    	
    	$build_year = get_post_meta( $post_id, 'build_year', true );
    	if ( $build_year ) { $info .= '<strong>Build Year:</strong>: <div class="xxx wip">'.$build_year."</div>"; }
    	
    	$opus_num = get_post_meta( $post_id, 'opus_num', true );
    	if ( $opus_num ) { $info .= '<strong>Opus Num.</strong>: <div class="xxx wip">'.$opus_num."</div>"; }
    	
    	/*
    	$venue_sources = get_post_meta( $post_id, 'venue_sources', true );
    	$info .= '<strong>venue_sources</strong>: <div class="xxx wip">'.$venue_sources."</div>";
    	
    	$venue_html_ip = get_post_meta( $post_id, 'venue_html_ip', true );
    	$info .= '<strong>venue_html_ip</strong>: <div class="xxx wip">'.$venue_html_ip."</div>";
    	
    	$organs_html_ip = get_post_meta( $post_id, 'organs_html_ip', true );
    	$info .= '<strong>organs_html_ip</strong>: <div class="xxx wip">'.$organs_html_ip."</div>";
    	
    	$organs_html_vp = get_post_meta( $post_id, 'organs_html_vp', true );
    	$info .= '<strong>organs_html_vp</strong>: <div class="xxx wip">'.$organs_html_vp."</div>";
    	*/
    }
    
    return $info;
    
}

?>