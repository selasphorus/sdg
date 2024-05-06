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

add_shortcode('display_webcast', 'display_webcast');
function display_webcast( $post_id = null ) {
	
	if ( $post_id == null ) { $post_id = get_the_ID(); }
    
    $info = ""; // init
    
    if ( post_is_webcast_eligible( $post_id ) ) {
        
        $media_info = get_media_player( $post_id );
        $player_status = $media_info['player_status'];
        
        $info .= "<!-- Webcast Audio/Video Player for post_id: $post_id -->";
        $info .= $media_info['info'];
        $info .= "<!-- player_status: $player_status -->";
        //$info .= get_media_player( $post_id );
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
        $webcast_format = get_field('webcast_format', $post_id);
        $video_id = get_field('video_id', $post_id);
        //$vimeo_id = get_field('vimeo_id', $post_id);
        
        //$technical_difficulties = get_field('technical_difficulties', $post_id);
        //if ( $technical_difficulties == 'true' ) { $webcast_status = "technical_difficulties"; }
        //$status_message .= "technical_difficulties: '".$technical_difficulties."'"; // tft
        
        if ( $webcast_status === "before" ) {
            if ( empty( $video_id ) || $webcast_format == "vimeo_recurring" ) {
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

// Get Media Player -- Based on contents of ACF/Webcast Info fields
function get_media_player ( $post_id = null, $status_only = false, $url = null ) {
	
    if ( $post_id == null ) { $post_id = get_the_ID(); } 
    
    // If it's not a webcast-eligible post, then abort
    if ( !post_is_webcast_eligible( $post_id ) ) {
        return false;
    }
    
    // init vars
    $arr_info = array(); // return info and status, or status only, depending on options selected
	$info = "";
    $player = "";
    $player_status = "unknown";
    
    // Get the webcast status, Video ID or URL, and format
    $webcast_status = get_webcast_status( $post_id );
    $video_id = get_field('video_id', $post_id);
    $audio_file = get_field('audio_file', $post_id);
    $podbeans_id = get_field('podbeans_id', $post_id); // podbeans -- deprecated
    $pb_channel_id = get_field('pb_channel_id', $post_id); // podbeans -- deprecated
    $url = get_webcast_url( $post_id ); //if ( empty($video_id)) { $src = get_webcast_url( $post_id ); }
    $webcast_format = get_field('webcast_format', $post_id);
    if ( empty($webcast_format) ) { $webcast_format = "audio"; } // Default to audio -- ??
    
    /*
    ---
    Webcast Format Options:
    ---
    vimeo : Vimeo Video/One-time Event
    vimeo_recurring : Vimeo Recurring Event
    youtube: YouTube
    video : Video (Flowplayer)
    video_as_audio : Video as Audio
    video_as_audio_live : Video as Audio - Livestream
    audio : Audio Only
    ---
    */
    
    if ( !empty($url) && ! ( $webcast_format == "video_as_audio" || $webcast_format == "audio") ) {
        $flowplayer = true;
        if ( $status_only === false ) {
            $info .= "<!-- flowplayer -->";
        }
    } else { 
        $flowplayer = false;
    }
	
    if ( $status_only == false ) {
	   $info .= "<!-- post_id: '".$post_id."'; webcast_status: '".$webcast_status."'; webcast_format: '".$webcast_format."'; video_id: '".$video_id."'; audio_file: '".$audio_file."'; webcast_url: '".$url."' -->";
    }
    
    if ( !empty($video_id) ) { //&& $webcast_format != "video_as_audio_live"
        
        if ( $webcast_format == "vimeo" || $webcast_format == "vimeo_recurring" ) {
            
            // Video Player: Vimeo iframe embed code
        
            $src = null; // init

            // Get src depending on format
            if ( $webcast_format == "vimeo_recurring" ) { // Show Vimeo player only if webcast is live -- because recurring events share single Vimeo ID
                if ( $webcast_status == "live" ) {
                    $src = 'https://vimeo.com/event/'.$video_id.'/embed/';
                }
            } else { // if ( $webcast_format == "vimeo" )
                $src = 'https://player.vimeo.com/video/'.$video_id;
            }

            if ( $src !== null ) {

                $player_status = "ready";
                
                if ( $status_only == false ) {
                    $player .= '<div class="vimeo_container">';
                    $player .= '<iframe id="vimeo" src="'.$src.'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%;"></iframe>';
                    //$player .= '<iframe id="vimeo" src="'.$src.'" width="'.$frame_width.'" height="'.$frame_height.'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                    $player .= '</div>';
                }               

            }
            
        } else if ( $webcast_format == "youtube" && ( !has_post_thumbnail( $post_id ) || ( $webcast_status == "live" || $webcast_status == "on_demand" ) ) ) {
            
            $player_status = "ready";
            if ( $status_only == false ) {
                $youtube_ts = get_field('youtube_ts');
                //$src = 'https://www.youtube.com/watch?v='.$video_id;
                $src = 'https://www.youtube.com/embed/'.$video_id.'?&playlist='.$video_id.'&autoplay=0&loop=1&mute=0&controls=1';
				if ( $youtube_ts ) { $src .= "&start=".$youtube_ts; }
                //$player = do_shortcode('[video src="'.$src.'"]'); //height="300"
                //$player .= '<div class="responsive-youtube"><iframe width="850" height="475" src="https://www.youtube.com/embed/'.$video_id.'?controls=1" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                $player .= '<div class="hero video-container youtube-responsive-container">';
				$player .= '<iframe width="100%" height="100%" src="'.$src.'" title="YouTube video player" frameborder="0" allowfullscreen></iframe>'; // controls=0 // allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
				$player .= '</div>';
            }
            
        }        
    
    } else if ( $webcast_format == "podbeans_live" ) {
        
        // src="https://www.podbean.com/live-player/?channel_id=L7vRFJrcwN&scode=d4cc62070ff4826cbf396d30b1aaff5f" 
        $player .= '<div class="podbeans_container">';
        $src = "https://www.podbean.com/live-player/?channel_id=".$pb_channel_id."&scode=d4cc62070ff4826cbf396d30b1aaff5f";
        $webcast_title = get_field('webcast_title', $post_id);
        $webcast_style = 'border: none; min-width: min(100%, 430px);';
		$player .= '<iframe id="podbeans_live" src="'.$src.'" title="'.$webcast_title.'" style="'.$webcast_style.'" frameborder="0" allowtransparency="true" allowfullscreen="" height="150" width="100%" scrolling="no" data-name="pb-iframe-player" referrerpolicy="no-referrer-when-downgrade"></iframe>';
		$player .= '</div>';
        //<iframe height="150" width="100%" style="border: none" scrolling="no" data-name="pb-iframe-player" referrerpolicy="no-referrer-when-downgrade" src="https://www.podbean.com/live-player/?channel_id=L7vRFJrcwN&scode=d4cc62070ff4826cbf396d30b1aaff5f" allowfullscreen=""></iframe>
    
    } else if ( !empty($podbeans_id) ) {
        
        // DEV WIP
        if ( $status_only == false ) {
            $info .= "<!-- podbeans_id: '".$podbeans_id."'-->";
        }

        $player .= '<div class="podbeans_container">';
        $src = "https://www.podbean.com/player-v2/?i=".$podbeans_id."-pb&from=pb6admin&pbad=0&share=1&download=1&rtl=0&fonts=Arial&skin=1&font-color=auto&logo_link=episode_page&btn-skin=c73a3a";
        $webcast_title = get_field('webcast_title', $post_id);
        $webcast_style = 'border: none; min-width: min(100%, 430px);';
        $player .= '<iframe id="podbeans" src="'.$src.'" title="'.$webcast_title.'" style="'.$webcast_style.'" frameborder="0" allowtransparency="true" height="150" width="100%" scrolling="no" data-name="pb-iframe-player"></iframe>';
        $player .= '</div>';
            
            
    } else if ( !empty($audio_file) || $webcast_format == "audio" || $webcast_format == "video_as_audio" || $webcast_format == "video_as_audio_live" ) {
        
        // WIP Audio Player: options for live and on-demand
        
        $player_id = 'audio_player';
        
        // WIP -- audio live stream
        
        if ( $webcast_format == "video_as_audio_live" ) {
            
            $info .= "webcast_format: 'video_as_audio_live'";
            $info .= "<br />";
            
            //e.g. -- see vimeo-test.php
            
            $vimeo_php = '/var/www/vhosts/saintthomaschurch.org/dev.saintthomaschurch.org/vendor/autoload.php';
            if ( file_exists($vimeo_php) ) {
                require $vimeo_php;
            } else {
                die("Error: The file does not exist.");
            }

            $info .= "Vimeo PHP found and loaded.";
            $info .= "<br />";
            $client_id = "b7f2f8e088399f314a5a0a491cbf6aec02e06695";
            $client_secret = "nMSMhds9TKKT2LXv8usyakteyzBtfVgUDs6WeOrvzgr7YrZGmu/sHrrJFFsSeeKhu/Z2/T0A2jldwiENhAABXdqXTquVitGl7Z8YUc2hNajNFmETIWHDxVEGRrGuNudH";
            $access_token = "63c1738d4271a716bf1fc9f0a90d846c";
            
            $client = new Vimeo($client_id, $client_secret, $access_token);
            //$client = new \Vimeo\Vimeo($client_id, $client_secret, $access_token);
            //  Fatal error: Uncaught Exception: Unable to complete request. [Unable to communicate securely with peer: requested domain name does not match the server's certificate.]
    
            
            // WIP
            //
            // Request Parameters:
            // url      --  string	-- The URL path (e.g.: /users/dashron).
            // params   --  string	-- An object containing all of your parameters (e.g.: { "per_page": 5, "filter" : "featured"} ).
            // method   --  string	-- The HTTP method (e.g.: GET).
            //
            // Response Parameters:
            // body     --  array	-- The parsed request body. All responses are JSON, so we parse this for you and give you the result.
            // status   --	number	-- The HTTP status code of the response. This partially informs you about the success of your API request.
            // headers  --  array	-- An associative array containing all of the response headers.

            $request_uri    = 'https://api.vimeo.com/me/videos/'.$video_id.'/m3u8_playback';
            $request_params = ''; // e.g. ['per_page' => 2]
            $request_method = 'GET';
            $info .= "video_id: '".$video_id."'<br />";
            //$info .= "<!-- video_id: '".video_id."'-->";
            
            if ( $client ) {
                $response = $client->request($request_uri, $request_params, $request_method);
                $info .= "response['status']: ".$response['status']; // tft
                $info .= "<br />";
                $info .= "response['headers']: ".print_r($response['headers'], true); // tft
                $info .= "<br />";
                $info .= "response['body']: ".print_r($response['body'], true); // tft
                $info .= "<br />";
            }
            
            ////
            /* JS e.g. from https://github.com/vimeo/vimeo-live-player-examples/blob/master/m3u8_demo_app.html
            
            const resp = await fetch(`https://api.vimeo.com/me/videos/${videoID}/m3u8_playback`, {headers: {"Authorization": auth}});
            if(resp.status !== 200) {
                throw new Error("server error");
            }
            */
                                     
            
        }
        
        //
        
        if ( !empty($audio_file) ) {
            
            // DEV WIP
            if ( $status_only == false ) {
                $info .= "<!-- audio_file: '".$audio_file."'-->";
            }
            $src = $audio_file;

        } else if ( !empty($video_id) ) {
            
            // DEV WIP
            if ( $status_only == false ) {
                $info .= "<!-- video_id: '".$video_id."'-->";
            }
            $src = null;
            //$atts = array('src' => $audio_file );

        } else if ( !empty($url) ) {
            
            // DEV WIP -- for non-locally hosted m3u8/mp4 files
            if ( $status_only == false ) {
                $info .= "<!-- url: '".$url."'-->";
            }
            $src = $url;
        }
        
        if ( !empty($src) ) {
            $type = wp_check_filetype( $src, wp_get_mime_types() ); // tft
            $ext = $type['ext'];
            //$info .= "<!-- file type: ".print_r($type, true)." -->"; // tft
            $info .= "<!-- ext: ".$ext." -->"; // tft
            //$info .= "wp_get_audio_extensions: ".print_r(wp_get_audio_extensions(), true); // tft

            $atts = array('src' => $src, 'preload' => 'auto' ); // Playback position defaults to 00:00 via preload -- allows for clearer nav to other time points before play button has been pressed
            $player .= "<!-- audio_player atts: ".print_r($atts,true)." -->";
        } else {
            // Vimeo video as audio
        }
        
        /*
        // Audio Player: HTML5 'generic' player via WP audio shortcode (summons mejs -- https://www.mediaelementjs.com/ -- stylable player)
        // NB default browser player has VERY limited styling options, which is why we're using the shortcode
        $player .= '<div class="audio_player">'; // media_player
        $player .= wp_audio_shortcode( $atts );
        $player .= '</div>';
        */
        
        /*if ( $ext == 'mp4' ) {
            
            // Video Player: HTML5 'generic' player via WP video shortcode (summons mejs -- https://www.mediaelementjs.com/ -- stylable player)
            // NB default browser player has VERY limited styling options, which is why we're using the shortcode
            $player .= '<div class="audio_player video_as_audio">'; // media_player
            $player .= wp_video_shortcode( $atts );
            $player .= '</div>';
            
        } else */
        if ( !empty($src) && !empty($ext) && !empty($atts) ) { // && !empty($url) 
            
            $player_status = "ready";
            
            if ( $status_only == false ) {
                // Audio Player: HTML5 'generic' player via WP audio shortcode (summons mejs -- https://www.mediaelementjs.com/ -- stylable player)
                // NB default browser player has VERY limited styling options, which is why we're using the shortcode
                $player .= '<div class="audio_player">'; // media_player
                $player .= wp_audio_shortcode( $atts );
                $player .= '</div>';
            }
            
        } else if ( !empty($url) ) {
            
            $player_status = "ready";
            
            if ( $status_only == false ) {
                
                // For m3u8 files, use generic HTML5 player for now, even though the styling is lousy. Can't get it to work yet via WP shortcode.
                $player .= '<div class="audio_player video_as_audio">';
                $player .= '<audio id="'.$player_id.'" class="masked" style="height: 3.5rem; width: 100%;" controls="controls" width="300" height="150">';
                $player .= 'Your browser does not support the audio element.';
                $player .= '</audio>';
                $player .= '</div>';

                // Create array of necessary attributes for HLS JS
                $atts = array('src' => $src, 'player_id' => $player_id ); // other options: $masked
                // Load HLS JS
                $player .= "<!-- Load HLS JS -->";
                $player .= load_hls_js( $atts );
            }
            
        } else if ( !empty($video_id) ) {
            
            $player_status = "ready";
            
            if ( $status_only == false ) {
                $player .= "<!-- Video as Audio -- WIP -->";
                $player .= '<iframe src="https://player.vimeo.com/video/'.$video_id.'" width="auto" height="50" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
            }
            
        }
        
        // END WIP experiments for video as audio
        
    } else if ( $flowplayer == true ) {
        
        // Video Player: Flowplayer
        
        $player_status = "ready";
            
        if ( $status_only == false ) {
            // Get the poster image
            $poster_image = get_the_post_thumbnail_url( $post_id );
            if (! $poster_image ) {
                $poster_image = "https://www.saintthomaschurch.org/wp-content/uploads/2019/07/Looking-West-to-the-High-Altar-with-a-Lenten-Frontal.jpg"; // default image
            }

            // Flowplayer container
            $player .= '<div class="flowplayer_container">';
            if ( $webcast_format == 'audio' ) {
                $player .= '<div id="flowplayer" class="audio-player"></div>';
            } else {
                $player .= '<div id="flowplayer"></div>';
            }
            //
            $player .= '</div>';

            // Google Analytics for Flowplayer
            $player .= "<!-- Google Analytics/Flowplayer -->";
            $player .= "<script>";
            $player .= "window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;";
            if ( !is_dev_site() ) {
                $player .= "ga('create', 'UA-163775836-1', 'auto');";
            } else {
                $player .= "ga('create', 'UA-163775836-2', 'auto');";
            }        
            $player .= "ga('send', 'pageview');";
            $player .= "</script>";
            $player .= "<script async src='//www.google-analytics.com/analytics.js'></script>";
            $player .= "<!-- End Google Analytics/Flowplayer -->";
        }
    
    }
    
    // TODO: get this content from some post type manageable via the front end, by slug or id (e.g. 'cta-for-webcasts'
    // post type for CTAs could be e.g. "Notifications", or post in "CTAs" post category, or... 
    // -- or by special category of content associated w/ CPTs?
    
    if ( $status_only == false ) {
        
        // Prep Webcast CTA
        $cta = "";
        $cta .= '<div class="cta">';
        $cta .= '<h2>Support Our Ministry</h2>';
        //$cta .= '<h2>Support Our Webcast Ministry</h2>';
        //$cta .= '<a href="https://www.saintthomaschurch.org/product/one-time-donation/" target="_blank" class="button">Make a donation for the work of the Episcopal Church in the Holy Land on Good Friday</a>';
        $cta .= '<a href="https://www.saintthomaschurch.org/product/annual-appeal-pledge/" target="_blank" class="button">Pledge to our Annual Appeal</a>&nbsp;';
        $cta .= '<a href="https://www.saintthomaschurch.org/product/one-time-donation/" target="_blank" class="button">Make a Donation</a>';
        $cta .= '<a href="https://www.saintthomaschurch.org/product/make-a-payment-on-your-annual-appeal-pledge/" target="_blank" class="button">Make an Annual Appeal Pledge Payment</a>';
        $cta .= '<br />';
        //cta .= '<h3>You can also text "give" to (855) 938-2085</h3>';
        $cta .= '<h3>You can also text "give" to <a href="sms://+18559382085">(855) 938-2085</a></h3>';
        //$cta .= '<h3><a href="sms://+18559382085?body=give">You can also text "give" to (855) 938-2085</a></h3>';
        $cta .= '</div>';
        // Old version:
        //cta .= '<div class="cta">Please support our webcast ministry by making a pledge to our <a href="https://www.saintthomaschurch.org/product/annual-appeal-pledge/" target="_blank">Annual Appeal</a> or a <a href="https://www.saintthomaschurch.org/product/one-time-donation/" target="_blank">one-time gift</a>.</div>';

        // Add call to action BEFORE audio player
        /*if ( $src && is_dev_site() ) {
            $info .= $cta;
        }*/

        $status_message = get_status_message ( $post_id, 'webcast_status' );
        $show_cta = get_post_meta( $post_id, 'show_cta', true );
        if ( $show_cta == "0" ) { 
            $show_cta = false;
            $info .= '<!-- show_cta: FALSE -->';
        } else { 
            $show_cta = true;
            $info .= '<!-- show_cta: TRUE -->';
        }

        if ($status_message !== "") {
            $info .= '<p class="message-info">'.$status_message.'</p>';
            if ( !is_dev_site() // Don't show CTA on dev site. It's annoying clutter.
                    && $show_cta !== false
                    && get_post_type($post_id) != 'sermon' ) { // Also don't show CTA for sermons
                $info .= $cta;
            }
            //return $info; // tmp disabled because it made "before" Vimeo vids not show up
        }
    }
    
    // If there's a webcast to display, show the player
    if ( $player != "" && $status_only == false ) { // !empty($vimeo_id) || !empty($audio_file) || !empty($url)
        
        $player_status = "ready";
        
        $info .= "<!-- MEDIA PLAYER -->"; // tft
		$info .= $player;
        $info .= "<!-- /MEDIA PLAYER -->"; // tft
        
        // Assemble Cuepoints (for non-Vimeo webcasts only -- Flowplayer; HTML5 Audio-only
        $rows = get_field('cuepoints', $post_id); // ACF function: https://www.advancedcustomfields.com/resources/get_field/ -- TODO: change to use have_rows() instead?
        /*if ( have_rows('cuepoints', $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
            while ( have_rows('cuepoints', $post_id) ) : the_row();
                $XXX = get_sub_field('XXX'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
            endwhile;
        } // end if
        */
            
            // Loop through rows and assemble cuepoints
        if ($rows) {
            
            // Cuepoints
            
            if ( $flowplayer == true ) {
                $info .= '<!-- Flowplayer Cuepoints -->'; // tft
            } else {
                $info .= '<!-- HTML5 Player Cuepoints -->'; // tft
                // TODO: move this to sdg.js?
                $info .= '<script>';
                $info .= '  var vid = document.getElementsByClassName("wp-audio-shortcode")[0];';
                $info .= '  function setCurTime( seconds ) {';
                $info .= '    vid.currentTime = seconds;';
                $info .= '  }';
                $info .= '</script>';
            }

            // Flowplayer Cuepoints
            $seek_buttons = ""; // init
            $button_actions = ""; // init

            $info .= '<div id="cuepoints" class="cuepoints scroll">';

            foreach( $rows as $row ) {

                //print_r($row); // tft
                $name = ucwords(strtolower($row['name'])); // Deal w/ the fact that many cuepoint labels were entered in UPPERCASE... :-[
                $start_time = $row['start_time'];
                $end_time = $row['end_time'];
                $button_id = $name.'-'.str_replace(':','',$start_time);

                // If the start_time is < 1hr, don't show the initial pair of zeros
                if ( substr( $start_time, 0, 3 ) === "00:" ) { $start_time = substr( $start_time, 3 ); }
                // Likewise, if the end_time is < 1hr, don't show the initial pair of zeros
                if ( substr( $end_time, 0, 3 ) === "00:" ) { $end_time = substr( $end_time, 3 ); }

                // Convert cuepoints to number of seconds for use in player
                $start_time_seconds = xtime_to_seconds($row['start_time']);
                $end_time_seconds = xtime_to_seconds($row['end_time']);

                $seek_buttons .= '<div class="cuepoint">';

                if ( $flowplayer == true ) { //if ( !empty($src) ) {

                    // Flowplayer
                    
                    $seek_buttons .= '<span class="cue_name"><button id="'.$button_id.'" class="cue_button">'.$name.'</button></span>';
                    /*if ( $start_time ) {
                        $seek_buttons .= '<span class="cue_time">'.$start_time;
                        if ( $end_time ) { $seek_buttons .= '-'.$end_time; }
                        $seek_buttons .= '</span>';
                    }*/
                    
                    $button_actions .= 'document.getElementById("'.$button_id.'").addEventListener("click", function(){ seek_to_cue('.$start_time_seconds.'); });';
                    
                } else {
                    
                    // HTML5
                    $seek_buttons .= '<span class="cue_name"><button id="'.$button_id.'" onclick="setCurTime('.$start_time_seconds.')" type="button" class="cue_button">'.$name.'</button></span>';
                    
                }

                if ( $start_time ) {
                    $seek_buttons .= '<span class="cue_time">'.$start_time;
                    if ( $end_time ) { $seek_buttons .= '-'.$end_time; }
                    $seek_buttons .= '</span>';
                }
                
                $seek_buttons .= '</div>';

            }

            $info .= $seek_buttons;
            $info .= '</div>';
            
        } // END if ($rows) for Cuepoints
        
        // If SRC url was found, load Flowplayer JS
        // TODO: move this to a separate function (?)
        if ( $flowplayer == true ) {
            
            // Embed the JS (Flowplayer)
            $player_js = '<script>';
            $player_js .= 'var api = flowplayer("#flowplayer", {';
            $player_js .= '	src      : "'.$url.'",';
            if ( $webcast_format != 'audio' ) {
                $player_js .= '	poster      : "'.$poster_image.'",';
            }
            $player_js .= '	autoplay : false, ';
            $player_js .= '	muted    : false,';
            $player_js .= '	token    : "eyJraWQiOiJXN1M0V3ZHSjVTMHgiLCJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJjIjoie1wiYWNsXCI6NixcImlkXCI6XCJXN1M0V3ZHSjVTMHhcIn0iLCJpc3MiOiJGbG93cGxheWVyIn0.WK4z3OP4whNB0g4y4EsSHJatsbZu3cT5je4bOUybTIjcDj_9AimhDnIw3sGGZbGfopUwxZ8XlPWKvncpvzUfWg", ';
            $player_js .= '	ui    : flowplayer.ui.USE_THIN_CONTROLBAR, ';
            $player_js .= '	ga: {';
            if ( !is_dev_site() ) { 
                $player_js .= '	    ga_instances: ["UA-163775836-1"],';
            } else {
                $player_js .= '	    ga_instances: ["UA-163775836-2"],';
            }
            $player_js .= '	    media_title: "[media_name]",';
            $player_js .= '	}';        
            $player_js .= '});';

            // button actions
            if ( isset ($button_actions) ) { $player_js .= $button_actions; }

            $player_js .= '</script>';

            $info .= $player_js;
            
        }
        
        // Add call to action beneath media player
        if ( $player != "" && !is_dev_site() && $show_cta !== false && $post_id != 232540 && get_post_type($post_id) != 'sermon' ) {
        //if ( (!empty($vimeo_id) || !empty($src)) && !is_dev_site() && $post_id != 232540 && get_post_type($post_id) != 'sermon' ) {    
            $info .= $cta;
        }
		
	} else {
		
        // No $src or $vimeo_id or $audio_file? Don't show anything.
        $player_status = "unavailable";
	
	}
	
    if ( $status_only == false ) {
        
        $arr_info['info'] = $info;
        $arr_info['player_status'] = $player_status;
    
        return $arr_info;
        
    } else {
        return $player_status;
    }
	
    return null; // if all else fails...
	
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
function load_hls_js( $atts = [] ) {

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