<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin fiel, not much I can do when called directly.';
	exit;
}

/***** HOMEPAGE Slider *****/
/**
 * Displays the slider
 * Modified version of fcorpo_display_slider()
 */
function atc_display_slider( $slider_id = "285632" )  { // default to Homepage Slider (live site)
    echo '<div id="homepage-carousel">';
	if ( $slider_id == null ) {
        echo '<img src="https://www.saintthomaschurch.org/wp-content/uploads/2019/07/Fifth-Avenue-Entrance-e1601222397172.jpg" alt="Fifth Avenue Entrance" />'; // tft
    } else {
        echo do_shortcode( '[metaslider id="'.$slider_id.'"]' ); // Meta Slider
    }
    //echo do_shortcode( '[metaslider id="60001"]' ); // Meta Slider
	//echo do_shortcode( '[sp_wpcarousel id="74075"]' ); // WP Carousel plugin
	echo '</div>';
}


/*** Metaslider -- remove exclusion of Pages from Post Types available in Post Feed Slider ***/
add_filter('metaslider_post_feed_exclude_post_types', function() {
	return ['attachment'];
});

/**
 * Set custom capability to for MetaSlider modification.
 * TODO: figure out why this doesn't seem to work at all, despite being copy/pasted from metaslider documentation
 */
add_filter('metaslider_capability', 'metaslider_change_cap', 1, 0);
function metaslider_change_cap() {
    return 'manage_options';
}

?>