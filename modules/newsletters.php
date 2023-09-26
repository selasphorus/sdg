<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** Functions pertaining to CPT: NEWSLETTER ***********/

function get_cpt_newsletter_content( $post_id = null ) {
	
	// This function retrieves supplementary info -- the regular content template (content.php) handles title, content, featured image
	
    $info = ""; // init
    if ($post_id === null) { $post_id = get_the_ID(); }
    
    if ( $post_id === null ) { return false; }
    
    $post_pdf = get_field('pdf_file', $post_id);
    $info .= "<!-- post_pdf: ".print_r($post_pdf, true)." -->";
	if ($post_pdf) { 
        $info .= make_link($post_pdf['url'], "Newsletter PDF", get_bloginfo()." -- Newsletter PDF: ".get_the_title($post_id), null, "_blank"); // make_link( $url, $text, $title = null, $class = null, $target = null) 
	} else {
		//
	}
    
    return $info;
    
}

?>