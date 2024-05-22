<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin fiel, not much I can do when called directly.';
	exit;
}


/*********** WEBCASTS ***********/

function post_is_webcast_eligible( $post_id = null ) {
	
	if ($post_id == null) { $post_id = get_the_ID(); }
    
    if ( is_singular( array( 'event', 'post', 'page', 'sermon' ) ) 
          && ( has_term( 'webcasts', 'event-categories', $post_id ) 
              || in_category( 'webcasts', $post_id ) 
              || has_term( 'webcasts', 'page_tag', $post_id ) 
              || has_tag( 'webcasts', $post_id) 
              || has_term( 'webcasts', 'admin_tag', $post_id ) )
       ) {
        return true;
    }
    
    // Post does not have Webcast Info enabled
    return false;
    
}

// Obsolete(?)
add_shortcode('display_webcast', 'display_webcast');
function display_webcast( $post_id = null ) {
	
	if ( $post_id == null ) { $post_id = get_the_ID(); }
    
    $info = ""; // init
    
    if ( post_is_webcast_eligible( $post_id ) ) {
        
        $media_info = get_media_player( $post_id );
        $player_status = $media_info['status'];
        
        $info .= "<!-- Webcast Audio/Video Player for post_id: $post_id -->";
        $info .= $media_info['player'];
        $info .= "<!-- player_status: $player_status -->";
        $info .= '<!-- /Webcast Audio/Video Player -->'; 
        
    } else {
        
        return null;
        
        //$info .= "<!-- NOT post_is_webcast_eligible. -->";
        //$info .= '<br style="clear:both" />';
        // For troubleshooting only
        
        /*
        $post_type = get_post_type( $post_id );
        $post_categories = wp_get_post_categories( $post_id );
        $post_tags = get_the_tags( $post_id );
        $page_tags = get_the_terms( $post_id, 'page_tag' );
        $event_categories = get_the_terms( $post_id, 'event-categories' );
        //$terms_string = join(', ', wp_list_pluck($term_obj_list, 'name'));        
        
        $info .= "<!-- Terms for post_id $post_id of type $post_type: \n";
        if ( $post_categories ) { $info .= "categories: "       . print_r($post_categories, true)."\n"; }
        if ( $event_categories ){ $info .= "event_categories: " . print_r($event_categories, true)."\n"; }
        if ( $post_tags )       { $info .= "post_tags: "        . print_r($post_tags, true)."\n"; }
        if ( $page_tags )       { $info .= "page_tags: "        . print_r($page_tags, true)."\n"; }
        $info .= " -->";
        */
            
    }
    
    return $info;
}

function get_webcast_url( $post_id = null, $cuepoint = null ) {
	
    // init vars
	if ($post_id === null) { $post_id = get_the_ID(); }
	$info = "";
    $post_type = null;
	
    $video_id = get_field('video_id', $post_id);
    //$vimeo_id = get_field('vimeo_id', $post_id);
    $audio_file = get_field('audio_file', $post_id);
    // Don't even try to return a URL if there's a Vimeo ID or Audio File on record
    if ( !empty($video_id) || !empty($audio_file) ) { return null; } //  || !empty($vimeo_id)
    
	// TODO: implement jump to cuepoint via query parameter

    $webcast_status = get_webcast_status( $post_id );
    
	//$info .= "webcast_status: $webcast_status"; // tft
	if ( $webcast_status == 'on_demand' ) {
        $src = get_field('url_ondemand', $post_id);
	} else if ( $webcast_status == 'live' ) {
        $src = get_field('url_live', $post_id);
	} else {
		$src = null;
        //$src = "webcast_status: $webcast_status"; // ftf
	}
	
	return $src;
}

function get_webcast_status( $post_id = null ) {
	
    // init vars
	if ($post_id === null) { $post_id = get_the_ID(); }
	$info = "";
    $post_type = null;
    $now = current_time( 'mysql' ); // Get current date/time via WP function to ensure time zone matches site.
    
    if ( !post_is_webcast_eligible( $post_id ) ) { return false; }
    
    // ACF function: https://www.advancedcustomfields.com/resources/get_field/
    $webcast_status = get_field('webcast_status', $post_id);
    
    // If webcast_status wasn't set/selected manually, deduce it from the available webcast info
    if ( empty($webcast_status) || $webcast_status == "tbd" ) {
    
        $url_live = get_field('url_live', $post_id);
        $live_start = get_field('live_start', $post_id);
        //if ( $live_start == false && is_singular('event') ) { $live_start = get_post_meta( $post_id, '_event_start_date', true ); }
        $url_ondemand = get_field('url_ondemand', $post_id);
        
        // before, live, after, on_demand, technical_difficulties
        if ( !empty( $url_ondemand ) ) { 
            $webcast_status = 'on_demand';
        } elseif ( !empty( $url_live ) ) { 
            if ( !empty ($live_start) ) {
                if ( $live_start < $now ) {
                    $webcast_status = 'live';
                } else {
                    $webcast_status = 'before';
                }
            } else {
                $webcast_status = 'live';
            }
        } elseif ( !empty ($live_start) && $live_start < $now ) {
            $webcast_status = 'after';
        } else {
            $webcast_status = 'unknown';
        }
        
    }
    
	return $webcast_status;
}

function get_status_message ( $post_id = null, $message_type = 'webcast_status' ) {
    
    if ( $post_id == null ) { $post_id = get_the_ID(); }
    $post_type = get_post_type( $post_id );
    $status_message = "";
    //$status_message = "post_id: '".$post_id."'; message_type: '".$message_type."'"; // tft
    
	if ( $message_type == 'webcast_status' ) {
        
        if ( !post_is_webcast_eligible( $post_id ) ) {
            return $status_message; // return false;
        }
        
        $webcast_status = get_webcast_status( $post_id );
        $media_format = get_field('media_format', $post_id);
        $video_id = get_field('video_id', $post_id);
        //$vimeo_id = get_field('vimeo_id', $post_id);
        
        //$technical_difficulties = get_field('technical_difficulties', $post_id);
        //if ( $technical_difficulties == 'true' ) { $webcast_status = "technical_difficulties"; }
        //$status_message .= "technical_difficulties: '".$technical_difficulties."'"; // tft
        
        if ( $webcast_status === "before" ) {
            if ( empty( $video_id ) || $media_format == "vimeo_recurring" ) {
                // If live_start is set, display message saying that the webcast will be available on that date/time
                $live_start = get_field('live_start', $post_id);
                if ( $live_start == false && is_singular('event') ) { $live_start = get_post_meta( $post_id, '_event_start_local', true ); }
                if ( $live_start != "" ) {

                    $start_timestamp = strtotime($live_start);
                    $now = current_time( 'timestamp' );
                    $today = date('F d, Y', $now);
                    $start_day = date('F d, Y', $start_timestamp);
                    $start_time = date('H:i a', $start_timestamp);

                    if ( $start_timestamp > $now ) {
                        $status_message .= "A live webcast will be available starting ";
                        if ( $today == $start_day ) {
                            $status_message .= "today ";
                        } else {
                            $status_message .= "on ".$start_day;
                        }
                        $status_message .= " at ".$start_time.".";
                    } else {
                        //$status_message .= "<!-- live but past... -->";
                    }

                } else if ( $post_type != 'sermon' ) {
                    $status_message = "This webcast is not yet available.";
                }
            }
        } else if ( $webcast_status === "after" && $post_type != 'sermon' ) {
            $status_message .= "An on-demand webcast will be available shortly.";
        } else if ( $webcast_status === "live" ) {
            //$status_message .= "";
        }  else if ( $webcast_status === "on_demand" ) {
            //$status_message .= "";
        } else if ( $webcast_status === "technical_difficulties" ) {
            $status_message .= "This webcast is currently unavailable due to technical difficulties. We apologize for the inconvenience.";
        } else if ( $webcast_status === "cancelled" && $post_type != 'sermon' ) {
            $status_message .= "This webcast has been cancelled. We apologize for any inconvenience.";
        } else if ( $post_type != 'sermon' ) {
            // NB: there's no special message for $webcast_status === "live". This means that if the status is live but the live URL no longer works, this generic message will display.
            $status_message = "This webcast is currently unavailable."; 
        }
    }
    
    //$status_message .= "<!-- post_id: '".$post_id."'; webcast_status: '".$webcast_status."' -->"; // tft
    
    return $status_message;
}

// Get ID of post which is currently livestreaming, if any
function get_live_webcast_id () {

	$post_id = null;

    $wp_args = array(
		'post_type'   => 'event',
		'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => 'webcast_status',
                'value'   => 'live'
            ),
            'date_clause' => array(
                'key'     => '_event_start_date',
                'value'   => date('Y-m-d'), // today! (in case AG forgot to update the status after a live stream was over...)
            ),
			'time_clause' => array(
				'key' => '_event_start_time',
				'compare' => 'EXISTS',
			),
        ),
        'orderby'	=> array(
			'date_clause' => 'ASC',
			'time_clause' => 'DESC',
		),
		'fields' => 'ids',
		'cache_results' => false,
    );
    
    $query = new WP_Query( $wp_args );
    $posts = $query->posts;
    
    if ( count($posts) > 0 ) {
        $post_id = $posts[0];        
    }
    
    return $post_id;

}

// TODO (?): refashion as more generic helper function: get_related_event_link( $post_type = null, $post_id = null )
/*function get_webcast_event_link( $webcast_id = null ) {
	
	$info = ""; // init
	if ($webcast_id === null) { $webcast_id = get_the_ID(); }
	
	$event_id = get_webcast_event_id( $webcast_id );
	//$info .= "<!-- event_id: $event_id; webcast_id: $webcast_id -->";
	if ($event_id && $event_id !== "no posts") {
		$info .= '<a href="'. esc_url(get_the_permalink($event_id)) . '" title="event_id: '.$event_id.'/webcast_id: '.$webcast_id.'">' . get_the_title($event_id) . '</a>';
	} else {
		//$info .= "<!-- event_id: $event_id; webcast_id: $webcast_id -->";
		return null;
	}
	//$info .= '<a href="'. esc_url(get_permalink($event_id)) . '">' . get_the_title($event_id) . '</a>';
	
	return $info;
	
}*/

/*** VIMEO ***/
// This can't go inside the get_media_player function because of scoping rules -- see https://www.php.net/manual/en/language.namespaces.importing.php
use \Vimeo\Vimeo as Vimeo;

add_shortcode('vimeo_api_test', 'vimeo_api_test');
function vimeo_api_test() {
    
    $info = "";
    $filepath = ABSPATH.'vendor/autoload.php';
    
    if (file_exists($filepath)) {
        $info = "Found $filepath!\n";
        require $filepath;
    } else {
        $info = "$filepath not found...\n";
        return $info;
    }

    //use Vimeo\Vimeo; // causes fatal error!

    //$client = new Vimeo("b7f2f8e088399f314a5a0a491cbf6aec02e06695", "nMSMhds9TKKT2LXv8usyakteyzBtfVgUDs6WeOrvzgr7YrZGmu/sHrrJFFsSeeKhu/Z2/T0A2jldwiENhAABXdqXTquVitGl7Z8YUc2hNajNFmETIWHDxVEGRrGuNudH", "63c1738d4271a716bf1fc9f0a90d846c");
    //$client = new Vimeo("{client_id}", "{client_secret}", "{access_token}");

    //$response = $client->request('/tutorial', array(), 'GET');
    //print_r($response);
    
    //$info = $response;
    
    
    return $info;

}

//add_shortcode('vimeo_player_js', 'vimeo_player_js');
function vimeo_player_js() {
    
    // See e.g. https://dev.saintthomaschurch.org/events/vimeo-player-test-sdk-using-an-existing-player-2020-04-05/
    
    $info = "";
    
    $info .= '<script src="https://player.vimeo.com/api/player.js"></script>';
    $info .= '<script>';
    $info .= 'var iframe = document.querySelector("iframe");';
    $info .= 'var player = new Vimeo.Player(iframe);';
    $info .= 'console.log("-- Vimeo Test --");';
    $info .= 'player.on("play", function() {';
    $info .= '    console.log("Played the video");';
    $info .= '});';
    $info .= 'player.getVideoTitle().then(function(title) {';
    $info .= '    console.log("title:", title);';
    $info .= '});';
    $info .= '</script>';
    
    return $info;
    
}  

add_shortcode('vimeo_player_test', 'vimeo_player_js_test');
function vimeo_player_js_test() {
    
    $info = "";
    
    //$info .= '<script src="{url}"></script>';
    $info .= '<script src="https://player.vimeo.com/api/player.js"></script>';
    $info .= '<script>';
    
    $info .= 'var video01Player = new Vimeo.Player("vimeo_video_01");
    video01Player.on("play", function() {
      console.log("Played the first video");
    });';

    $info .= 'var video02Player = new Vimeo.Player("vimeo_video_02");
    video02Player.on("play", function() {
      console.log("Played the second video");
    });';
      
    /*
    $info .= 'var options01 = {
      id: {video01_id},
      width: {video01_width}
    };';
    $info .= 'var options02 = {
      url: {video02_url},
      width: {video02_width}
    };';

    $info .= 'var video01Player = new Vimeo.Player("{video01_name}", options01);';
    $info .= 'var video02Player = new Vimeo.Player("{video02_name}", options02);';

    $info .= 'video01Player.setVolume(0);';
    $info .= 'video02Player.setVolume(0);';

    $info .= 'video01Player.on("play", function() {
      console.log("Played the first video");
    });';
    $info .= 'video02Player.on("play", function() {
      console.log("Played the second video");
    });';
    */
    
    $info .= '</script>';
    
    return $info;
}

/*** ***/

add_shortcode('video_player_test', 'load_hls_js');
function load_hls_js( $atts = array() ) {

	$info = "";

	$args = shortcode_atts( array(
        'player_id' => null,
        'src' => null,
        'masked' => null
    ), $atts );
    
    // Extract
	extract( $args );
    
    $info = "";
    
    // See https://stackoverflow.com/a/48688707/264547
    // Use the JavaScript HLS client hls.js package to play m3u8 file via HTML5 audio/video tags
    $info .= '<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>';
    $info .= '<script>';
    
    $info .= 'if(Hls.isSupported()) {';
    $info .= "var video = document.getElementById('".$player_id."');";
    $info .= 'var hls = new Hls();';
    $info .= "hls.loadSource('".$src."');";
    $info .= 'hls.attachMedia(video);';
    $info .= 'hls.on(Hls.Events.MANIFEST_PARSED,function() {';
    //$info .= 'video.play();';
    $info .= '});';
    $info .= "} else if (video.canPlayType('application/vnd.apple.mpegurl')) {";
    $info .= "video.src = '".$src."';";
    $info .= "video.addEventListener('canplay',function() {";
    //$info .= 'video.play();';
    $info .= '});';
    $info .= '}';
    
    $info .= '</script>';
    
    if ( $masked !== null ) {
        $info .= '<div class="video_mask" style="background-color: teal;">test</div>';
    }
    
    return $info;

}



?>