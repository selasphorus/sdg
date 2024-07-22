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
    	$builder = display_field( 'builder', $post_id ); //$builder = get_post_meta( $post_id, 'builder', true );
    	$info .= '<strong>builder</strong>: <div class="xxx wip">'.$builder."</div>";
    	
    	/*
    	//<div class="source venue_source wip">
    	$venue_info_vp = get_post_meta( $post_id, 'venue_info_vp', true );
    	$info .= '<strong>venue_info_vp</strong>: <div class="xxx wip">'.$venue_info_vp."</div>";
    	
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