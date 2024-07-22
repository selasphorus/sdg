<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** POST BASICS ***********/

function sdg_post_title ( $args = array() ) {
    
    // TS/logging setup
    $do_ts = devmode_active(); 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
	$info = "";
	$ts_info = "";
	//$ts_info = "START sdg_post_title<br />";
	
	//$ts_info .= "<pre>args: ".print_r($args, true)."</pre>";
	
	// Defaults
	$defaults = array(
		'the_title'		=> null, // optional override to set title via fcn arg, not via post
		'post'			=> null,
		'line_breaks'	=> false,
		'show_subtitle'	=> false,
		'show_person_title' => false, // WIP
		'show_series_title' => false,
		'link'			=> false,
		'echo'			=> true,
		'hlevel'		=> 1,
		'hlevel_sub'	=> 2,
		'hclass'  		=> 'entry-title',
		'hclass_sub'  	=> 'subtitle',
		'before'  		=> '',
		'after'  		=> '',
		'do_ts'			=> devmode_active(),
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	if ( is_numeric($post) ) { 
		$post_id = $post;
		$post = get_post( $post_id );
	} else {
		//$ts_info .= "Not is_numeric: ".$post."<br />";
		$post_id = isset( $post->ID ) ? $post->ID : 0;
	}
	$ts_info .= "post_id: ".$post_id."<br />";
	//$ts_info .= "<pre>post: ".print_r($post, true)."</pre>";
	if ( !$show_subtitle ) {
    	//$ts_info .= "[sdgpt] show_subtitles: false<br />";
    } else {
    	//$ts_info .= "[sdgpt] show_subtitles: true<br />";
    }
	
	// If a title has been submitted, use it; if not, get the post_title
	if ( $the_title ) {
		$title = $the_title;
	} else if ( is_object($post) ) {
		$title = $post->post_title;
	} else {
		$title = "";
	}
	
	// If both title and post_id are empty, abort
	if ( strlen( $title ) == 0 || $post_id == 0) {
		if ( $do_ts && !empty($ts_info) ) { return $ts_info; }
		return;
	}
	
	// WIP
	// What about clean_title?
	/*
	$clean_title = get_post_meta( $post_id, 'clean_title', true ); // for legacy events only
    if ( $clean_title && $clean_title != "" ) {
        $event_title = $clean_title;
    } else {
        $event_title = $EM_Event->event_name;
    }
    */
    
    // Clean it up
	if ( preg_match('/([0-9]+)_(.*)/', $title) ) {
        $title = preg_replace('/([0-9]+)_(.*)/', '$2', $title);
        $title = str_replace("_", " ", $title);
    }    
    $title = remove_bracketed_info($title);
        
	// Check for pipe character and replace it with line breaks or spaces, depending on settings
	if ( $line_breaks ) {
		$title = str_replace("|", "<br />", $title);
	} else {
		$title = str_replace("|", " ", $title);
		$title = str_replace("  ", " ", $title); // Replace double space with single, in case extra space was left following pipe
	}

	$title = $before.$title;
	
	// If we're showing the subtitle, retrieve and format the relevant text
	if ( $show_subtitle ) { // && function_exists( 'is_dev_site' ) && is_dev_site()
		$subtitle = get_post_meta( $post_id, 'subtitle', true );
		if ( strlen( $subtitle ) != 0 ) {
			// Add "with-subtitle" class to title header, if any
			$hclass .= " with-subtitle";
			if ( $hlevel_sub ) {
				$subtitle = '<h'.$hlevel_sub.' class="'.$hclass_sub.'">'.$subtitle.'</h'.$hlevel_sub.'>';
			} else {
				$subtitle = '<br /><span class="subtitle">'.$subtitle.'</span>';
			}
		}
	} else {
		$subtitle = "";
	}
	
	// If we're showing the person_title, retrieve and format the relevant text
	// WIP!
	if ( $show_person_title ) {
		/*$person_title = get_post_meta( $post_id, 'subtitle', true );
		if ( strlen( $person_title ) != 0 ) {
			$title .= ", ".$person_title;
		}*/
	}
	
	// If we're showing a series subtitle, retrieve and format the relevant text
	if ( $show_series_title ) {	// && function_exists( 'is_dev_site' ) && is_dev_site()
	
		$info .= "<!-- show_series_title -->";
		
		// Determine the series type
		if ( $post->post_type == "event" ) {
			$series_field = 'events_series';
		} else if ( $post->post_type == "sermon" ) {
			$series_field = 'sermons_series';
		}
		$info .= "<!-- series_field: $series_field -->";
		$series = get_post_meta( $post_id, $series_field, true );
		if (isset($series[0])) { $series_id = $series[0]; } else { $series_id = null; $info .= "<!-- series: ".print_r($series, true)." -->"; }
		$info .= "<!-- series_id: $series_id -->";
		$series_subtitle = get_post_meta( $series_id, 'series_subtitle', true );		
		if ( empty( $series_subtitle ) && !empty( $series_id ) ) {
			$series_subtitle = "From the ".ucfirst($post->post_type)." Series &mdash; ".get_the_title( $series_id );
		}		
		if ( !empty( $series_subtitle ) ) {
			if ( !empty( $series_id ) ) { 
				$series_subtitle = '<a href="'.esc_url( get_permalink($series_id) ).'" rel="bookmark" target="_blank">'.$series_subtitle.'</a>';
			}
			$series_subtitle = '<h'.$hlevel_sub.' class="'.$hclass_sub.'">'.$series_subtitle.'</h'.$hlevel_sub.'>';
		}
		// TODO: add hyperlink to the series page
		//
	} else {
		$series_subtitle = "";
	}
	/*
	if ( is_dev_site() ) {
        
        //$event_title = get_the_title($EM_Event->ID); // For some reason this is breaking things on the live site, but only when event titles have info in brackets with space around hyphen -- e.g. 2022 - Shrine Prayers
        
        // Get the series title, if any
        $series_id = null;
        $series_title = "";

        $event_series = get_post_meta( $post_id, 'events_series', true );
        if ( isset($event_series['ID']) ) { 
            $series_id = $event_series['ID'];
            $prepend_series_title = get_post_meta( $series_id, 'prepend_series_title', true );
            if ( $prepend_series_title == 1 ) { $series_title = get_the_title( $series_id ); }
        }

        // Prepend series_title, if applicable
        if ( $series_title != "" ) { $event_title = $series_title.": ".$event_title; }
    }
    */
    
    // Hyperlink the title, if applicable
	if ( $link ) {
		$title = '<a href="'.esc_url( get_permalink($post_id) ).'" rel="bookmark">'.$title.'</a>';
	}
	
	// Format the title according to the parameters for heading level and class
	if ( $hlevel ) {
		$title = '<h'.$hlevel.' class="'.$hclass.'">'.$title.'</h'.$hlevel.'>'; // '<h1 class="entry-title">'
	}
	
	// Add the title, subtitle, and series_subtitle to the info for return
	$info .= $title;
	$info .= $subtitle;
	$info .= $series_subtitle;
	
	//$ts_info .= "END sdg_post_title<br />";
	
	if ( $do_ts && !empty($ts_info) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	// Echo or return, as requested via $echo arg.
	if ( $echo ) {
		echo $info;
	} else {
		return $info;
	}
	
}

function sort_post_ids_by_title ( $arr_ids = array() ) {
	
	$arr_info = array();
	$info = "";
	
	//$info .= "arr_ids to be sorted by title => ".print_r($arr_ids,true)."<br />";
	
	$wp_args = array(
		'post_type'   => 'any',
		'post_status' => array( 'private', 'draft', 'publish', 'archive' ),
		'posts_per_page' => -1,
		'post__in' => $arr_ids,
		'orderby'   => 'title',
		'order'     => 'ASC',
		'fields'	=> 'ids',
	);
	
	$query = new WP_Query( $wp_args );
	$post_ids = $query->posts;
	//$info .= count($post_ids)." posts found and sorted<br />";
	
	$arr_info['post_ids'] = $post_ids;
	$arr_info['info'] = $info;
	
	return $arr_info;
	
}

/************** IMAGE FUNCTIONS ***************/

// Custom fcn for thumbnail/featured image display
function sdg_post_thumbnail ( $args = array() ) {
    
    // TS/logging setup
    $do_ts = devmode_active(); 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
	
	//$ts_info .= "<pre>sdg_post_thumbnail args: ".print_r($args, true)."</pre>";
	
	// Defaults
	$defaults = array(
		'post_id'	=> null,
		'format'	=> "singular", // default to singular; other option is excerpt
		'img_size'	=> "thumbnail",
		'sources'	=> array("featured_image", "gallery"),
		'echo'		=> true,
		'return_value' => 'html',
		//'do_ts'  	=> false,
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	$ts_info .= "sdg_post_thumbnail parsed/extracted args: <pre>".print_r($args, true)."</pre>";
	
    if ( $post_id == null ) { $post_id = get_the_ID(); }
    $post_type = get_post_type( $post_id );
    $img_id = null;
    if ( $return_value == "html" ) {
    	$img_html = "";
    	$caption_html = "";
    }
    
    $img_type = "post_image"; // other option: attachment_image
    
    $image_gallery = array();
    if ( $sources == "all" ) {
    	$sources = array("featured", "gallery", "custom_thumb", "content");
    }
    
    if ( $format == "singular" && !is_page('events') ) {
        $img_size = "full";
    }
    
    $ts_info .= "post_id: $post_id<br />";
    $ts_info .= "format: $format<br />";
    $ts_info .= "get_the_ID(): ".get_the_ID()."<br />";
    $ts_info .= "img_size: ".print_r($img_size, true)."<br />";
    $ts_info .= "sources: ".print_r($sources, true)."<br />";
    $ts_info .= "return_value: $return_value<br />";
    //
    
    // Make sure this is a proper context for display of the featured image
    $player_status = get_media_player( $post_id, true, 'above', 'video' );
	if ( $format == "singular" && $player_status == "ready" ) {
		return;
	} else {
		$ts_info .= "player_status: ".$player_status."<br />";
	}
    if ( post_password_required($post_id) || is_attachment($post_id) ) {
        return;
    } else if ( has_term( 'video-webcasts', 'event-categories' ) && is_singular('event') ) {        
        // featured images for events are handled via Events > Settings > Formatting AND via events.php (#_EVENTIMAGE)
        //return;
    } else if ( has_term( 'video-webcasts', 'category' ) ) {        
        //
    } else if ( is_page_template('page-centered.php') && $post_id == get_the_ID() ) {        
		return;
	} else if ( is_singular() && $post_id == get_the_ID() && in_array( get_field('featured_image_display'), array( "background", "thumbnail", "banner" ) ) ) {        
        return; // wip
    }

    $ts_info .= "Ok to display the image, if one has been found.<br />";
    
    // Ok to display the image! Set up classes for styling
    $classes = "post-thumbnail sdg";
    //$classes .= " zoom-fade"; //if ( is_dev_site() ) { $classes .= " zoom-fade"; }
    if ( is_singular('event') ) { $classes .= " event-image"; }
    if ( is_archive() || is_post_type_archive() ) { $classes .= " float-left"; }
    //
    
    // Are we using the custom image, if any is set?
    // Do this only for archive and grid display, not for singular posts of any kind (? people ?)
    if ( $format != "singular" && in_array("custom_thumb", $sources ) ) {
    	$ts_info .= "Check for custom_thumb<br />";
        // First, check to see if the post has a Custom Thumbnail
        $custom_thumb_id = get_post_meta( $post_id, 'custom_thumb', true );
        if ( $custom_thumb_id ) {
        	$ts_info .= "custom_thumb_id found: $custom_thumb_id<br />";
            $img_id = $custom_thumb_id;
        }
    }
    
    // WIP: order?
    // If this is a sermon, are we using the author image
    if ( $format != "singular" && $post_type == "sermon" ) {
    	if ( get_field('author_image_for_archive') ) {
    		$img_id = get_author_img_id ( $post_id );
    	} else {
    		$ts_info .= "author_image_for_archive set to false<br />";
    	}
    }

    // If we're not using the custom thumb, or if none was found, then proceed to look for other image options for the post
    if ( !$img_id ) {
        
        // Check to see if the given post has a featured image
        if ( has_post_thumbnail( $post_id ) ) {

            $img_id = get_post_thumbnail_id( $post_id );
            $ts_info .= "post has a featured image.<br />";

        } else {

            $ts_info .= "post has NO featured image.<br />";

            // If there's no featured image, see if there are any other images that we can use instead
            
            // Image Gallery?
            if ( in_array("gallery", $sources ) ) {
				// get image gallery images and select one at random
				$image_gallery = get_post_meta( $post_id, 'image_gallery', true );
				if ( is_array($image_gallery) && count($image_gallery) > 0 ) {
					$ts_info .= "Found an image_gallery array.<br />";
					$ts_info .= "image_gallery: <pre>".print_r($image_gallery, true)."</pre>";
					$i = array_rand($image_gallery,1); // Get one random image ID -- tmp solution
					// WIP: figure out how to have a more controlled rotation -- based on event date? day? cookie?
					/*
					// Get number of items in array...
					$img_count = count($image_gallery);
					// Get event date and weekday
					if ( get_post_type($post_id) == 'event' ) {
						// Is this an instance of a recurring event? look for recurrent event id...
						$recurrence_id = get_post_meta( $post_id, '_recurrence_id', true );
						if ( $recurrence_id ) {
					
							$meta = get_post_meta( $post_id );
							$ts_info .= "meta: <pre>".print_r($meta, true)."</pre>";
						
							// Get event object?
							//$ts_info .= print_r($XXX,true);
						
							// Get recurring event info
							$revent = get_post ( $recurrence_id );
							$ts_info .= "revent: <pre>".print_r($revent, true)."</pre>";
							$recurrence_interval = get_post_meta( $recurrence_id, '_recurrence_interval', true ); //'recurrence_interval' => array( 'name'=>'interval', 'type'=>'%d', 'null'=>true ), //every x day(s)/week(s)/month(s)
							$recurrence_freq = get_post_meta( $recurrence_id, '_recurrence_freq', true ); //'recurrence_freq' => array( 'name'=>'freq', 'type'=>'%s', 'null'=>true ), //daily,weekly,monthly?
							$recurrence_byday = get_post_meta( $recurrence_id, '_recurrence_byday', true ); //'recurrence_byday' => array( 'name'=>'byday', 'type'=>'%s', 'null'=>true ), //if weekly or monthly, what days of the week?
							//'recurrence_days' => array( 'name'=>'days', 'type'=>'%d', 'null'=>true ), //each event spans x days
							$ts_info .= "recurrence_id: $recurrence_id; recurrence_interval: $recurrence_interval; recurrence_freq: $recurrence_freq; recurrence_byday: $recurrence_byday<br />";
						
							// Get event date
							$event_date = get_post_meta( $post_id, '_event_start_date', true );
							$ts_info .= "event_date: $event_date; <br />";
						
						
							$date = explode('-', $event_date);
							$year = $date[0];
							$month = $date[1];
							$day = $date[2];
							$weekday = date('w', strtotime($event_date)); // A numeric representation of the day (0 for Sunday, 6 for Saturday)
							$yearday = date('z', strtotime($event_date)); // z - The day of the year (from 0 through 365)
						}					
					}
					*/
					$img_id = $image_gallery[$i];
					$img_type = "attachment_image";
					$ts_info .= "Random thumbnail ID: $img_id<br />";
				} else {
					$ts_info .= "No image_gallery found.<br />";
				}
            }
            
            // Image(s) in post content?
            if ( empty($img_id) && in_array("content", $sources ) && function_exists('get_first_image_from_post_content') ) {
				$image_info = get_first_image_from_post_content( $post_id );
				if ( $image_info ) {
					$img_id = $image_info['id'];
				}
			}

			// Image attachment(s)?
            if ( empty($img_id) ) {

                // The following approach would be a good default except that images only seem to count as 'attached' if they were directly UPLOADED to the post
                // Also, images uploaded to a post remain "attached" according to the Media Library even after they're deleted from the post.
                $images = get_attached_media( 'image', $post_id );
                //$images = get_children( "post_parent=".$post_id."&post_type=attachment&post_mime_type=image&numberposts=1" );
                if ($images) {
                    //$img_id = $images[0];
                    foreach ($images as $attachment_id => $attachment) {
                        $img_id = $attachment_id;
                    }
                }

            }

            // If there's STILL no image, use a placeholder
            // TODO: make it possible to designate placeholder image(s) for archives via CMS and retrieve it using new version of get_placeholder_img fcn
            // TODO: designate placeholders *per category*?? via category/taxonomy ui?
            if ( empty($img_id) ) {
                //if ( function_exists( 'is_dev_site' ) && is_dev_site() ) { $img_id = 121560; } else { $img_id = 121560; } // Fifth Avenue Entrance
                $img_id = null;
            }
        }
    }
    
    if ( $return_value == "html" && !empty($img_id ) ) {
    
		// For html return format, add caption, if there is one
        
        // Retrieve the caption
		$caption = get_post( $img_id )->post_excerpt;
		if ( !empty($caption) && $format == "singular" && !is_singular('person') ) {
			$classes .= " has-caption";
			$ts_info .= "Caption found for img_id $img_id: '$caption'<br />";
		} else {
			$classes .= " no-caption";
			$ts_info .= "No caption found for img_id $img_id<br />";
		}
		
		if ( $caption != "" ) {
			$caption_class = "sdg_post_thumbnail featured_image_caption";
			$caption_html = '<p class="'. $caption_class . '">' . $caption . '</p>';
		} else {
			$caption_html = '<br />';
		}
		
		// Set up the img_html
		if ( $format == "singular" && !( is_page('events') ) ) {
		
			$ts_info .= "post format is_singular<br />";
			if ( has_post_thumbnail($post_id) ) {
			
				if ( is_singular('person') ) {
					$img_size = "medium"; // portrait
					$classes .= " float-left";
				}
		
				$classes .= " is_singular";
		
				$img_html .= '<div class="'.$classes.'">';
				$img_html .= get_the_post_thumbnail( $post_id, $img_size );
				$img_html .= $caption_html;
				$img_html .= '</div><!-- .post-thumbnail -->';

			} else {
		
				// If an image_gallery was found, show one image as the featured image
				// TODO: streamline this
				if ( $img_id && is_array($image_gallery) && count($image_gallery) > 0 ) {
					$ts_info .= "image_gallery image<br />";
					$img_html .= '<div class="'.$classes.'">';
					$img_html .= wp_get_attachment_image( $img_id, $img_size, false, array( "class" => "featured_attachment" ) );
					$img_html .= $caption_html;
					$img_html .= '</div><!-- .post-thumbnail -->';
				}
			
			}
		
		} else if ( !( $format == "singular" && is_page('events') ) ) {
		
			$ts_info .= "NOT is_singular<br />";
		
			// NOT singular -- aka archives, search results, &c.
			$img_tag = "";
		
			if ( $img_id ) {
			
				// display attachment via thumbnail_id
				$img_tag = wp_get_attachment_image( $img_id, $img_size, false, array( "class" => "featured_attachment" ) );
			
				$ts_info .= 'post_id: '.$post_id.'; thumbnail_id: '.$img_id;
				if ( isset($images)) { $ts_info .= '<pre>'.print_r($images,true).'</pre>'; }
			
			} else {
			
				$ts_info .= 'Use placeholder img';
			
				if ( function_exists( 'get_placeholder_img' ) ) { 
					$img_tag = get_placeholder_img();
				}
			}
		
			if ( !empty($img_tag) ) {
				$classes .= " float-left"; //$classes .= " NOT_is_singular";
				$img_html .= '<a class="'.$classes.'" href="'.get_the_permalink( $post_id ).'" aria-hidden="true">';
				$img_html .= $img_tag;
				$img_html .= '</a>';
			}
		
		} // END if is_singular()
	} // END if ( $return_value == "html" && !empty($img_id )
	    
    //$info .= '<div class="troubleshooting">'.$ts_info.'</div>';
    
    if ( $return_value == "html" ) {
    	$info .= $img_html;
    } else { // $return_value == "id"
    	$info = $img_id;
    	//$info .= '<div class="troubleshooting">'.$ts_info.'</div>';
    }
	
	if ( $do_ts && !empty($ts_info) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	if ( $echo == true ) {
		//if ( $do_ts && !empty($ts_info) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
		echo $info;    
	} else {
		//if ( $do_ts && !empty($ts_info) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
		return $info;
	}

}

function sdg_get_placeholder_img() {
    
    $info = "";
    
    $placeholder = get_page_by_title('woocommerce-placeholder', OBJECT, 'attachment');
    if ( $placeholder ) { 
        $placeholder_id = $placeholder->ID;
        if ( wp_attachment_is_image($placeholder_id) ) {
            //$info .= "Placeholder image found with id '$placeholder_id'."; // tft
            $img_atts = wp_get_attachment_image_src($placeholder_id, 'medium');
            $img = '<img src="'.$img_atts[0].'" class="bordered" />';
        } else {
            //$info. "Attachment with id '$placeholder_id' is not an image."; // tft
        }
    } else {
        //$info .= "woocommerce-placeholder not found"; // tft
    }
    
    $info .= $img;
    
    return $info;
}

/**
 * Show captions for featured images
 *
 * @param string $html          Post thumbnail HTML.
 * @param int    $post_id       Post ID.
 * @param int    $post_image_id Post image ID.
 * @return string Filtered post image HTML.
 */
//add_filter( 'post_thumbnail_html', 'sdg_post_image_html', 10, 3 );
function sdg_post_image_html( $html, $post_id, $post_image_id ) {
    
    if ( is_singular() && !in_array( get_field('featured_image_display'), array( "background", "thumbnail", "banner" ) ) ) {
    
    	$html .= '<!-- fcn sdg_post_image_html -->';
    	
        $featured_image_id = get_post_thumbnail_id();
        if ( $featured_image_id ) {
            $caption = get_post( $featured_image_id )->post_excerpt;
            if ( $caption != "" ) {
                $caption_class = "sdg_post_image featured_image_caption";
                $html = $html . '<p class="'. $caption_class . '">' . $caption . '</p>'; // <!-- This displays the caption below the featured image -->
            } else {
                $html = $html . '<br />';
            }
        }
        
        $html .= '<!-- /fcn sdg_post_image_html -->';
        
    }
    
    return $html;
}

// TODO: combine this next function with the previous one to remove redundancy
/**
 * Show captions for attachment images
 *
 * @param string $html          Image HTML.
 * @param int    $attachment_id Image ID.
 * @return string Filtered post image HTML.
 */
//apply_filters( 'wp_get_attachment_image', string $html, int $attachment_id, string|int[] $size, bool $icon, string[] $attr )
/*add_filter( 'wp_get_attachment_image', 'sdg_attachment_image_html', 10, 3 );
function sdg_attachment_image_html( $html, $attachment_id, $post_image_id ) {
    
    // TODO: fix this for other post types. How to tell if attachment was called from content-excerpt.php template?
    if ( is_singular('event') && !in_array( get_field('featured_image_display'), array( "background", "thumbnail", "banner" ) ) ) {
    
    	$html .= '<!-- fcn sdg_attachment_image_html -->';
    	
        if ( $attachment_id ) {
            $caption = get_post( $attachment_id )->post_excerpt;
            if ( $caption != "" ) {
                $caption_class = "featured_image_caption";
                $html = $html . '<p class="'. $caption_class . '">' . $caption . '</p>'; // <!-- This displays the caption below the featured image -->
            } else {
                $html = $html . '<br />';
            }
        }
        
        $html .= '<!-- /fcn sdg_attachment_image_html -->';
        
    }
    
    return $html;
}*/

// Function to display featured caption in EM event template
add_shortcode( 'featured_image_caption', 'sdg_featured_image_caption' );
function sdg_featured_image_caption ( $post_id = null, $attachment_id = null ) {
	
	global $post;
	global $wp_query;
    $info = "";
    $caption = "";
    
    if ( $attachment_id ) {
    
    } else {
    	if ( $post_id == null ) { $post_id = get_the_ID(); }
    }
	
	// Retrieve the caption (if any) and return it for display
    if ( get_post_thumbnail_id() ) {
        $caption = get_post( get_post_thumbnail_id() )->post_excerpt;
    }
    
    if ( $caption != "" && !in_array( get_field('featured_image_display'), array( "background", "thumbnail", "banner" ) ) ) {
        $caption_class = "sdg_featured_image_caption";
        $info .= '<p class="'. $caption_class . '">';
        $info .= $caption;	
        $info .= '</p>';
    } else {
        $info .= '<p class="zeromargin">&nbsp;</p>'; //$info .= '<br class="empty_caption" />';
    }
	
	return $info;
	
}


/*********** POST RELATIONSHIPS ***********/

// The following function is the replacement for the old get_related_podposts fcn
function get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $single = false ) {

	$info = null; // init
	
	// If we don't have actual values for all parameters, there's not enough info to proceed
	if ($post_id === null || $related_field_name === null || $related_post_type === null) { return null; }
	
	$related_id = null; // init
    if ( $single ) {
        $limit = 1;
    } else {
        $limit = -1;
    }

	// Set args
    $wp_args = array(
        'post_type'   => $related_post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key'     => $related_field_name,
                'value'   => $post_id
            )
        ),
        'orderby'		=> 'title',
        'order'			=> 'ASC',
    );
    // Run query
    $related_posts = new WP_Query( $wp_args );
    
    // Loop through the records returned 
    if ( $related_posts ) {
        
        if ( $single ) {
            // TODO: simplify -- shouldn't really need a loop here...
            while ( $related_posts->have_posts() ) {            
                $related_posts->the_post();
                $related_id = get_the_ID();                
            }
            $info = $related_id;
        } else {
            $info = $related_posts->posts;
        }
        
        /*
        $info .= "<br />";
        //$info .= "related_posts: ".print_r($related_posts,true);
        $info .= "related_posts->posts:<pre>".print_r($related_posts->posts,true)."</pre>";
        $info .= "wp_args:<pre>".print_r($wp_args,true)."</pre>";
        */
        
    } else {
    	$info = "No matching posts found for wp_args: ".print_r($wp_args,true);
    }
	
	return $info;
	
}

function display_postmeta( $args = array() ) {
    
    // TS/logging setup
    $do_ts = true; // default to true tft 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
	
	//$ts_info .= "<pre>sdg_post_thumbnail args: ".print_r($args, true)."</pre>";
	
	// Defaults
	$defaults = array(
		'post_id'	=> null,
		/*'format'	=> "singular", // default to singular; other option is excerpt
		'img_size'	=> "thumbnail",
		'sources'	=> array("featured_image", "gallery"),
		'echo'		=> true,
		'return_value'  	=> 'html',
		'do_ts'  	=> false,*/
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	$ts_info .= "sdg_post_thumbnail parsed/extracted args: <pre>".print_r($args, true)."</pre>";
	
    if ( $post_id === null ) { $post_id = get_the_ID(); }
    
    $postmeta = get_post_meta( $post_id );
    //$info .= "postmeta: <pre>".print_r($postmeta,true).'</pre>';
    $info .= "<h3>Post Meta Data for post with ID $post_id</h3>";
    $info .= "<h4>(Displaying NON-empty values only)</h4>";
    $info .= "<pre>";
    foreach ( $postmeta as $key => $value ) {
    	if ( strpos($key,"_") !== 0 ) { // Don't bother to display ACF field identifier postmeta
    		//$info .= $key." => ".print_r($value,true);
    		if (count($value) > 1) {
    		
    			$info .= $key." => ".print_r($value,true);
    			
    		} else {
    		
    			$value = $value[0];
    			if ( empty($value) ) { continue; }    			
    			
    			if ( strpos($value,"<") !== false ) {
    				$info .= $key.' {html} =><br />';
    				//$info .= $key.' {html} => <div class="devwip"><pre>'.htmlspecialchars($value).'</pre></div>';
    				$info .= '<div class="devwip">';
    				//$info .= '<iframe srcdoc="'.$value.'" style="width: 50%; float:left;">[iframe]</iframe>';
    				//$info .= '<div style="width: 50%; float:left;">'.htmlspecialchars($value).'</div>';
    				if ( $key == 'venue_html_vp' ) { // TMP/WIP for AGO
						//$info .= '<iframe srcdoc="'.$value.'" style="">[iframe]</iframe>';
						$info .= "[WIP]";
					} else if ( $key == 'organs_html_ip' || $key == 'organs_html_vp' ) { // TMP/WIP for AGO
						//strip_tags($value, '<p><a>');
						$info .= $value;
					} else {
						$info .= htmlspecialchars($value);
					}
    				$info .= '</div>';
    			} else {
    				$info .= $key." => ".$value."<br />";
    			}    			
    		}
    	}
    }
    $info .= "</pre>";
    
    if ( $do_ts && !empty($ts_info) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
    
    return $info;
	
}

/*********** MEDIA ***********/

// Get Media Player -- Based on contents of ACF A/V Info fields
function get_media_player ( $post_id = null, $status_only = false, $position = null, $media_type = 'unknown', $url = null ) {
	
    // Init vars
    $arr_info = array(); // return info and status, or status only, depending on options selected
	$info = "";
	$ts_info = "";
    $player = "";
    $player_status = "unknown";
    $featured_video = false;
    $featured_audio = false;
    $multimedia = false; // does the post have both featured audio and video?
    
    if ( $post_id == null ) { $post_id = get_the_ID(); } 
    $ts_info .= "[gmp] post_id: '".$post_id."'; position: '".$position."'; media_type: '".$media_type."'; status_only: '[".$status_only."]'<br />";
    // If it's not a webcast-eligible post, then abort
    //if ( !post_is_webcast_eligible( $post_id ) ) { return false;  }
    
    // Get the basic media info
    $featured_AV = get_field('featured_AV', $post_id); // array of options (checkboxes field) including: featured_video, featured_audio, webcast (WIP)
    $media_format = get_field('media_format', $post_id); // array of options (checkboxes) including: youtube, vimeo, video, audio -- // formerly: $webcast_format = get_field('webcast_format', $post_id);
    $ts_info .= "featured_AV: ".print_r($featured_AV, true)."<br />";
	$ts_info .= "media_format: ".print_r($media_format, true)."<br />";
	//if ( is_array($media_format) && count($media_format) > 1 ) {
	if ( is_array($featured_AV) && count($featured_AV) > 1 ) {
		$multimedia = true;
		$ts_info .= "MULTIPLE FEATURED A/V MEDIA FOUND<br />";
	} else {
		$ts_info .= "Multimedia FALSE<br />";
		
	}
    
    // Get additional vars based on presence of featured audio/video
    if ( is_array($featured_AV) && in_array( 'video', $featured_AV) ) {
    	$featured_video = true;
    	$video_player_position = get_field('video_player_position', $post_id); // above/below/banner
    	$ts_info .= "video_player_position: '".$video_player_position."'<br />";
    	if ( $media_type == "unknown" && $video_player_position == $position ) {
    		$media_type = 'video';
    		$ts_info .= "media_type REVISED: '".$media_type."'<br />";
    	}
    }
    if ( is_array($featured_AV) && in_array( 'audio', $featured_AV) ) {
    	$featured_audio = true;
    	$audio_player_position = get_field('audio_player_position', $post_id); // above/below/banner
    	$ts_info .= "audio_player_position: '".$audio_player_position."'<br />";
    	if ( $media_type == "unknown" && $audio_player_position == $position ) {
    		$media_type = 'audio';
    		$ts_info .= "media_type REVISED: '".$media_type."'<br />";
    	}
    }
    
	//
    if ( $media_type == "video" ) {
    
    	$video_id = get_field('video_id', $post_id);
    	$yt_ts = get_field('yt_ts', $post_id); // YT timestamp
    	$yt_series_id = get_field('yt_series_id', $post_id);
    	$yt_list_id = get_field('yt_list_id', $post_id);
    	
    	// Mobile or desktop? If mobile, check to see if a smaller version is available -- WIP
		if ( wp_is_mobile() ) {
			$video_file = get_field('video_file_mobile'); //$video_file = get_field('featured_video_mobile');
		}
		if (empty($video_file) ) {
			$video_file = get_field('video_file'); //$video_file = get_field('featured_video');
		}
    	if ( is_array($video_file) ) { $src = $video_file['url']; } else { $src = $video_file; }
		
		$ts_info .= "video_id: '".$video_id."'; video_file src: '".$src."<br />";
		
		if ( $src && is_array($media_format) && in_array( 'video', $media_format) ) {
			$media_format = "video";
		} else if ( $video_id && is_array($media_format) && in_array( 'vimeo', $media_format) ) {
			$media_format = "vimeo";
		} else {
			$media_format = "youtube";
		}
		
    } else if ( $media_type == "audio" ) {
    	
    	$audio_file = get_field('audio_file', $post_id);
    	$ts_info .= "audio_file: '".$audio_file."<br />";
    	if ( $audio_file ) { $media_format = "audio"; } else { $media_format = "unknown"; }
    	if ( is_array($audio_file) ) { $src = $audio_file['url']; } else { $src = $audio_file; }
    	
    } else {
    
		$media_format = "unknown";
		
	}

	$ts_info .= "media_format REVISED: '".$media_format."'<br />";
	//if ( $media_format != 'unknown' ) { $player_status = "ready"; }
    
    // Webcast?
    if ( is_array($featured_AV) && in_array( 'webcast', $featured_AV) ) {
    	$webcast_status = get_webcast_status( $post_id );
    	//if ( $webcast_status == "live" || $webcast_status == "on_demand" ) { }
    	$url = get_webcast_url( $post_id ); //if ( empty($video_id)) { $src = get_webcast_url( $post_id ); }
    	$ts_info .= "webcast_status: '".$webcast_status."'; webcast_url: '".$url."'<br />";
    }
    
    /*
    DEPRECATED:
    ---
    Webcast Format Options:
    ---
    vimeo : Vimeo Video/One-time Event
    vimeo_recurring : Vimeo Recurring Event
    youtube: YouTube
    youtube_list : YouTube Playlist
    video : Video (formerly: Flowplayer -- future use tbd)
    video_as_audio : Video as Audio
    video_as_audio_live : Video as Audio - Livestream
    audio : Audio Only
    ---
    */
	
	if ( $media_format == "audio" ) {
	
		$type = wp_check_filetype( $src, wp_get_mime_types() ); // tft
        $ext = $type['ext'];
        $ts_info .= "audio_file ext: ".$ext."<br />"; // tft
        $atts = array('src' => $src, 'preload' => 'auto' ); // Playback position defaults to 00:00 via preload -- allows for clearer nav to other time points before play button has been pressed
        $ts_info .= "audio_player atts: ".print_r($atts,true)."<br />";
        
        if ( !empty($src) && !empty($ext) && !empty($atts) ) { // && !empty($url) 
            
            // Audio file from Media Library
            
        	$player_status = "ready";
            
            if ( $status_only == false ) {
                // Audio Player: HTML5 'generic' player via WP audio shortcode (summons mejs -- https://www.mediaelementjs.com/ -- stylable player)
                // NB default browser player has VERY limited styling options, which is why we're using the shortcode
                $player .= '<div class="audio_player">'; // media_player
                $player .= wp_audio_shortcode( $atts );
                $player .= '</div>';
            }
            
        } else if ( !empty($url) ) {
            
            // Audio file by URL
            
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
                $player .= "Load HLS JS<br />";
                $player .= load_hls_js( $atts );
            }
            
        }
		
	} else if ( $media_format == "video" && $src ) { //} else if ( $media_format == "video" && isset($video_file['url']) ) {
		
		// Video file from Media Library
		
		$player_status = "ready";
		
		if ( $status_only == false ) {
			$player .= '<div class="hero vidfile video-container">';
			$player .= '<video poster="" id="section-home-hero-video" class="hero-video" src="'.$src.'" autoplay="autoplay" loop="loop" preload="auto" muted="true" playsinline="playsinline"></video>';
			$player .= '</div>';
		}
		
	} else if ( $media_format == "vimeo" && $video_id ) {
            
		// Vimeo iframe embed
		
		$player_status = "ready";
		
		$src = 'https://player.vimeo.com/video/'.$video_id;
			
		if ( $status_only == false ) {
			$class = "vimeo_container";
			if ( $video_player_position == "banner" ) { $class .= " hero vimeo video-container"; }
			$player .= '<div class="'.$class.'">';
			if ( $video_player_position == "banner" ) { 
				$player .= '<video poster="" id="section-home-hero-video" class="hero-video" src="'.$src.'" autoplay="autoplay" loop="loop" preload="auto" muted="true" playsinline="playsinline" controls></video>';
			} else {
				$player .= '<iframe id="vimeo" src="'.$src.'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%;"></iframe>';
			}
			$player .= '</div>';
		}
		
	} else if ( $media_format == "youtube" ) {		
		//&& ( !has_post_thumbnail( $post_id ) || ( $webcast_status == "live" || $webcast_status == "on_demand" ) )
		
		// WIP -- deal w/ webcasts w/ status other than live/on_demand
		
		// Get SRC
		if ( !empty($yt_series_id) && !empty($yt_list_id) ) { // && $media_format == "youtube_list"    
			$src = 'https://www.youtube.com/embed/videoseries?si='.$yt_series_id.'?&list='.$yt_list_id.'&autoplay=0&loop=1&mute=0&controls=1';
			//https://www.youtube.com/embed/videoseries?si=gYNXkhOf6D2fbK_y&amp;list=PLXqJV8BgiyOQBPR5CWMs0KNCi3UyUl0BH	
		} else if ( !empty($video_id) ) {
			$src = 'https://www.youtube.com/embed/'.$video_id.'?&playlist='.$video_id.'&autoplay=0&loop=1&mute=0&controls=1';
			//$src = 'https://www.youtube.com/embed/'.$youtube_id.'?&playlist='.$youtube_id.'&autoplay=1&loop=1&mute=1&controls=0'; // old theme header version -- note controls
			//$src = 'https://www.youtube.com/watch?v='.$video_id;
		} else {
			$src = null;
		}
			
		if ( $src ) { $player_status = "ready"; }
		
		if ( $status_only == false ) {
			
			// Timestamp?
			if ( $yt_ts ) { $src .= "&start=".$yt_ts; }
			
			// Assemble media player iframe
			$player .= '<div class="hero video-container youtube-responsive-container">';
			$player .= '<iframe width="100%" height="100%" src="'.$src.'" title="YouTube video player" frameborder="0" allowfullscreen></iframe>'; // controls=0 // allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
			$player .= '</div>';
		}
		
	}
    
    if ( $status_only === true ) {
    	return $player_status;
    }
    
    // If there's media to display, show the player
    if ( $player_status == "ready" ) {
        
         // CTA
		// TODO: get this content from some post type manageable via the front end, by slug or id (e.g. 'cta-for-webcasts'
		// post type for CTAs could be e.g. "Notifications", or post in "CTAs" post category, or... 
		// -- or by special category of content associated w/ CPTs?
        $status_message = get_status_message ( $post_id, 'webcast_status' );
        $show_cta = get_post_meta( $post_id, 'show_cta', true );
        if ( $show_cta == "1" ) { $show_cta = true; } else { $show_cta = false; }
        // WIP -- don't show the CTA twice...
        if ( $multimedia && $media_format == "audio" ) {
        	$show_cta = false;
        } else {
        	$ts_info .= 'multimedia: '.$multimedia.'/ media_format: '.$media_format.'<br />';
        }
        $cta = "";
        if ( $show_cta ) {
        	$ts_info .= 'show_cta: TRUE<br />';
        	$cta .= '<div class="cta">';
			$cta .= '<h2>Support Our Ministry</h2>';
			//$cta .= '<a href="https://www.saintthomaschurch.org/product/one-time-donation/" target="_blank" class="button">Make a donation for the work of the Episcopal Church in the Holy Land on Good Friday</a>';
			$cta .= '<a href="https://www.saintthomaschurch.org/product/annual-appeal-pledge/" target="_blank" class="button">Pledge to our Annual Appeal</a>&nbsp;';
			$cta .= '<a href="https://www.saintthomaschurch.org/product/one-time-donation/" target="_blank" class="button">Make a Donation</a>';
			$cta .= '<a href="https://www.saintthomaschurch.org/product/make-a-payment-on-your-annual-appeal-pledge/" target="_blank" class="button">Make an Annual Appeal Pledge Payment</a>';
			$cta .= '<br />';
			//cta .= '<h3>You can also text "give" to (855) 938-2085</h3>';
			$cta .= '<h3>You can also text "give" to <a href="sms://+18559382085">(855) 938-2085</a></h3>';
			//$cta .= '<h3><a href="sms://+18559382085?body=give">You can also text "give" to (855) 938-2085</a></h3>';
			$cta .= '</div>';
        } else {
        	$ts_info .= 'show_cta: FALSE<br />';
        }
        
        //
        if ( $status_message !== "" && $position != "banner" ) {
            $info .= '<p class="message-info">'.$status_message.'</p>';
            if ( !is_dev_site() // Don't show CTA on dev site. It's annoying clutter.
                    && $show_cta !== false
                    && get_post_type($post_id) != 'sermon' ) { // Also don't show CTA for sermons
                $info .= $cta;
            }
            //return $info; // tmp disabled because it made "before" Vimeo vids not show up
        }
        
        if ( $player != "" && is_singular('sermon') && has_term( 'webcasts', 'admin_tag', $post_id ) && get_post_meta( $post_id, 'audio_file', true ) != "" && $position != "banner" ) { 
            $player = '<h3 id="sermon-audio" name="sermon-audio"><a>Sermon Audio</a></h3>'.$player;
        }
        
        $info .= "<!-- MEDIA_PLAYER -->";
		$info .= $player;
        $info .= "<!-- /MEDIA_PLAYER -->";
        
        // Assemble Cuepoints (for non-Vimeo webcasts only -- HTML5 Audio-only
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
            
            $info .= '<!-- HTML5 Player Cuepoints -->'; // tft
			// TODO: move this to sdg.js?
			$info .= '<script>';
			$info .= '  var vid = document.getElementsByClassName("wp-audio-shortcode")[0];';
			$info .= '  function setCurTime( seconds ) {';
			$info .= '    vid.currentTime = seconds;';
			$info .= '  }';
			$info .= '</script>';

            // Cuepoints
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
                $seek_buttons .= '<span class="cue_name"><button id="'.$button_id.'" onclick="setCurTime('.$start_time_seconds.')" type="button" class="cue_button">'.$name.'</button></span>';
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
        
        // Add call to action beneath media player
        if ( $player != "" && !is_dev_site() && $show_cta !== false && $post_id != 232540 && get_post_type($post_id) != 'sermon' ) {
            $info .= $cta;
        }
		
	}
	
	if ( $ts_info ) { $ts_info .= "+~+~+~+~+~+~+~+<br />"; }
	//if ( $do_ts && !empty($ts_info) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }        
	$arr_info['player'] = $info;
	$arr_info['ts_info'] = $ts_info;
	$arr_info['position'] = $position;
	$arr_info['status'] = $player_status;

	return $arr_info;
	
}

// Display shortcode for media_player -- for use via EM settings
add_shortcode('display_media_player', 'display_media_player');
function display_media_player( $atts = array() ) {

	// Normalize attribute keys by making them all lowercase
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );
	
	// Override default attributes with user attributes
	$args = shortcode_atts(
		array(
			'post_id' => get_the_ID(),
			'position' => 'above',
			'return' => 'info',
		), $atts
	);
	
	// Extract args as vars
	extract( $args );
    
    // init
    $info = "";
    if ( $return == "array" ) {
    	$arr_info = array();
    }    
    
    // TODO: handle webcast status considerations
    /*if ( function_exists('post_is_webcast_eligible') && post_is_webcast_eligible( $post_id ) ) {
        //        
    } else {
        //            
    }*/
    
    $media_info = get_media_player( $post_id, false, $position );
    if ( is_array($media_info) ) {
    	$player_status = $media_info['status'];
		//
		$info .= "<!-- Audio/Video for post_id: $post_id -->";
		$info .= $media_info['player'];
		$info .= "<!-- player_status: $player_status -->";
		$info .= '<!-- /Audio/Video -->';
    } else {
    	$info .= "<!-- ".print_r($media_info,true)." -->";
    }	
    
    if ( $return == "array" ) {
		$arr_info['info'] = $info;
		$arr_info['player_status'] = $player_status;
		return $arr_info;
    } else {
    	return $info;
    }
}

// Get a linked list of Media Items
add_shortcode('list_media_items', 'sdg_list_media_items');
function sdg_list_media_items ( $atts = array() ) {

    global $wpdb;
    
	$info = "";
    $mime_types = array();
	
	$args = shortcode_atts( array(
      	'type'        => null,
		'category'    => null,
		'grouped_by'  => null,
    ), $atts );
    
    // Extract
	extract( $args );
	
    if ($type == "pdf") {
        $mime_types[] = "application/pdf";
    } else {
        $mime_types[] = $type;
    }
    
    //$unsupported_mimes  = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
    //$all_mimes          = get_allowed_mime_types();
    //$mime_types       = array_diff( $all_mimes, $unsupported_mimes );
	
    $wp_args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    if ( !empty($mime_types) ) {
        $wp_args['post_mime_type'] = $mime_types;
    }
    //'post_mime_type' => 'image/gif',
    
    if ( $category !== null ) {
        $wp_args['tax_query'] = array(
            array(
                'taxonomy' 	=> 'media_category',
                'field' 	=> 'slug',
                'terms' 	=> $category
            )
        );
    }

    $arr_posts = new WP_Query( $wp_args );
    $posts = $arr_posts->posts;
    //$info .= print_r($arr_posts, true);
    //$info .= "<!-- Last SQL-Query: ".$wpdb->last_query." -->";
    
    if ( !empty( $posts ) && !is_wp_error( $posts ) ){
		
        $info .= '<div class="media_list">';
        // init
        $items  = array();
        $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $litdates = array('ash_wednesday_date' => 'Ash Wednesday', 'easter_date' => 'Easter', 'pentecost_date' => 'Pentecost');
        $the_year = "";
        
        // Loop through the posts; built items array
        foreach ( $posts as $post ) {

            setup_postdata( $post );
            
            $title = $post->post_title;
            $post_id = $post->ID;
            $url = wp_get_attachment_url($post_id); // get_attachment_link($post_id);
            
            // Don't display the words "Music List", if present in the title
			if (strpos(strtolower($title), 'music list') !== false) { 
                $title = str_ireplace("Music List", "", $title);
			}
            
            if ( $grouped_by == "year" ) {
                
                // init
                $start_month = "";
                $end_month = "";
                
                // Extract year from filename
                $pattern = '/((19|20)\d{2})/';
                if ( preg_match($pattern, $title, $matches, PREG_OFFSET_CAPTURE) ) {
                    $year = trim($matches[0][0]);
                } else {
                    $year = null;
                }
                
                // For Music Lists, don't display the year in the title
                if ( $category == "music-lists" ) {
                    $title = str_ireplace($year, "", $title);
                }   
                
                // Get liturgical date calc info per year, in order to deal w/ lists named according to holidays (Easter, Ash Wednesday, Pentecost) instead of months
                // e.g. January-Easter 2019; Easter-September 2015
                if ( $year != $the_year ) {
                    
                    $the_year = $year;
                    
                    $wp_args = array(
                        'post_type'   => 'liturgical_date_calc',
                        'post_status' => 'publish',
                        'posts_per_page' => 1,
                        'meta_query' => array(
                            array(
                                'key'     => 'litdate_year',
                                'value'   => $year.'-01-01'
                            )
                        )
                    );
                    $liturgical_date_calc_post_id = null; // init
                    $liturgical_date_calc_post_obj = new WP_Query( $wp_args );
                    if ( $liturgical_date_calc_post_obj ) { 
                        $liturgical_date_calc_post = $liturgical_date_calc_post_obj->posts;
                        $liturgical_date_calc_post_id = $liturgical_date_calc_post[0]->ID;
                        $info .= "<!-- Found liturgical_date_calc_post for year $year with ID: ".$liturgical_date_calc_post[0]->ID." -->";
                        //$info .= "<!-- Found liturgical_date_calc_post for year $year: ".print_r($liturgical_date_calc_post, true)." >> ID: ".$liturgical_date_calc_post[0]->ID." -->"; // tft
                    } else { 
                        $info .= "<!-- NO liturgical_date_calc_post found for year $year -->"; 
                    } // tft
                    
                }
                
                foreach ( $months AS $i => $month ) {
                    
                    $num = (string)$i+1;
                    //$num = (string)$num;
                    $numlength = strlen($num);
                    if ($numlength == 1) {
                        $num = "0".$num;
                    }
                    
                    if (stripos($title, $month."-") !== false) {
                        $start_month = $num;
                        $info .= "<!-- Found start_month: $start_month from title: $title -->"; // tft
                    } else if (stripos($title, $month) !== false && stripos($title, "-") === false) {
                        $start_month = $num;
                        $info .= "<!-- Found start_month: $start_month from title: $title (no hyphens) -->"; // tft
                    }
                    
                    if (stripos($title, "-".$month) !== false) {
                        $end_month = $num;
                        $info .= "<!-- Found end_month: $end_month from title: $title -->"; // tft
                    } 
                }
                
                // If no start_month was found, look for: Easter, Ash Wednesday, Pentecost
                if ( $start_month == "" && $liturgical_date_calc_post_id ) {
                    $info .= "<!-- [post_id: $post_id] No start_month found >> try via litdates -->"; // tft
                    foreach ( $litdates AS $date_field => $litdate ) {
                        if (stripos($title, $litdate."-") !== false) {
                            $info .= "<!-- Found litdate match (start): $litdate in title: $title -->"; // tft
                            $start_date = get_post_meta( $liturgical_date_calc_post_id, $date_field, true);
                            $info .= "<!-- Found start_date via litdate: $start_date (date_field: $date_field) -->"; // tft
                            $start_month = date('m', strtotime($start_date) );
                        } else if (stripos($title, $litdate."-") !== false) {
                            $info .= "<!-- Found litdate match (end): $litdate in title: $title -->"; // tft
                            $end_date = get_post_meta( $liturgical_date_calc_post_id, $date_field, true);
                            $end_month = date('m', strtotime($start_date) );
                        }
                    }
                }
                
                $sort_date = $year.$start_month;                
                $items[] = array('id' => $post_id, 'title' => $title, 'url' => $url, 'year' => $year, 'sort_date' => $sort_date, 'start_month' => $start_month, 'end_month' => $end_month);
                
            } else {
                $items[] = array('id' => $post_id, 'title' => $title, 'url' => $url);
            }
            
            $info .= "<!-- +-----+ -->";

        }
        
        if ( $grouped_by == "year" ) {
            usort($items, sdg_arr_sort('value', 'sort_date', 'DESC'));
        }
        
        $the_year = ""; // reset
        foreach ( $items as $item ) {

            if ( $item['year'] != $the_year ) {
                $the_year = $item['year'];
                $info .= '<h2>'.$the_year.'</h2>';
            }
            if ( $grouped_by == "year" ) {
                $info .= "<!-- ".$item['sort_date']." -->";
            }
            $info .= '<a href="'.$item['url'].'" target="_new">'.$item['title'].'</a>';
            $info .= '<br />';

        }
        
        $info .= '</div>'; // close media_list div
        
    } else {
		$info .= "<p>No items found.</p>";
        $info .= "Last SQL-Query: ".$wpdb->last_query."";
        //$info .= "<!-- Last SQL-Query: ".$wpdb->last_query." -->";
	}
	return $info;
}

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

/*********** DATES/TIME/SCOPES ***********/

// Create custom scopes: "Upcoming", "This Week", "This Season", "Next Season", "This Year", "Next Year"
// Returns array of $dates with $dates['start'] and $dates['end'] in format 'Ymd' (or TBD: other $date_format)
// NB: these scope definitions can be used for any post type with date fields -- other than EM events, which are handled separately through events module and EM plugin
function sdg_scope_dates( $scope = null ) {
    
    // TS/logging setup
    $do_ts = devmode_active(); 
    $do_log = false;
    sdg_log( "divline2", $do_log );
	
	if ( empty($scope) ) { return null; }
	
	// Init vars
	$dates = array();
	$start_date = null;
	$end_date = null;
	$date_format = "Ymd"; // no hyphens for ACF fields(?) ... "Y-m-d"
	
    // get info about today's date
    $today = time(); //$today = new DateTime();
	$year = date_i18n('Y');
	
	// Define basic season parameters
	$season_start = strtotime("September 1st");
	$season_end = strtotime("July 1st");
	
	if ( $scope == 'today' ){
        
        $start_date = date_i18n($date_format); // today
        $end_date = $start_date;
    
    } else if ( $scope == 'today_onward' ){ // if ( $scope == 'today-onward' ){
        
        $start_date = date_i18n($date_format); // today
        $decade = strtotime($start_date." +10 years");
        $end_date = date_i18n($date_format,$decade);
    
    } else if ( $scope == 'upcoming' ) {
    
    	// Get start/end dates of today plus six        
        $start_date = date_i18n($date_format); // today
        $seventh_day = strtotime($start_date." +6 days");
        $end_date = date_i18n($date_format,$seventh_day);
    
    } else if ( $scope == 'this_week' ) {
    
    	// Get start/end dates for the current week        
        $sunday = strtotime("last sunday");
        $sunday = date_i18n('w', $sunday)==date('w') ? $sunday+7*86400 : $sunday;
        $saturday = strtotime(date($date_format,$sunday)." +6 days");
        $start_date = date_i18n($date_format,$sunday);
        $end_date = date_i18n($date_format,$saturday);
    
    } else if ( $scope == 'last_week' ) {
    
    	// Get start/end dates for the previous week
    	// WIP       
        $sunday = strtotime("last sunday");
        $sunday = date_i18n('w', $sunday)==date('w') ? $sunday+7*86400 : $sunday;
        $saturday = strtotime(date($date_format,$sunday)." +6 days");
        $start_date = date_i18n($date_format,$sunday);
        $end_date = date_i18n($date_format,$saturday);
    
    } else if ( $scope == 'next_week' ) {
    
    	// Get start/end dates for the next week
    	// WIP
        $sunday = strtotime("last sunday");
        $sunday = date_i18n('w', $sunday)==date('w') ? $sunday+7*86400 : $sunday;
        $saturday = strtotime(date($date_format,$sunday)." +6 days");
        $start_date = date_i18n($date_format,$sunday);
        $end_date = date_i18n($date_format,$saturday);
    
    } else if ( $scope == 'this_month' ) {
    
    	// Get start/end dates for the current month
    	// WIP
    
    } else if ( $scope == 'last_month' ) {
    
    	// Get start/end dates for the previous month
    	// WIP
    
    } else if ( $scope == 'next_month' ) {
    
    	// Get start/end dates for the next month
    	// WIP
    
    } else if ( $scope == 'this_season' ) {
    
    	// Get actual season start/end dates
		if ($today < $season_start){
			$season_start = strtotime("-1 Year", $season_start);
		} else {
			$season_end = strtotime("+1 Year", $season_end);
		}
		
		$start_date = date_i18n($date_format,$season_start);
		$end_date = date_i18n($date_format,$season_end);
    
    } else if ( $scope == 'next_season' ) {
    
		// Get actual season start/end dates
		if ($today > $season_start){
			$season_start = strtotime("+1 Year", $season_start);
			$season_end = strtotime("+2 Year", $season_end);
		} else {
			$season_end = strtotime("+1 Year", $season_end);
		}
		
		$start_date = date_i18n($date_format,$season_start);
		$end_date = date_i18n($date_format,$season_end);
    
    } else if ( $scope == 'ytd' ) {
    
    	$start = strtotime("January 1st, {$year}");		
		$start_date = date_i18n($date_format,$start);
		$end_date = date_i18n($date_format); // today
    
    } else if ( $scope == 'this_year' ) {
    
    	$start = strtotime("January 1st, {$year}");
    	$end = strtotime("December 31st, {$year}");
		
		$start_date = date_i18n($date_format,$start);
		$end_date = date_i18n($date_format,$end);
    
    } else if ( $scope == 'last_year' ) {
    
    	$year = $year-1;
    	$start = strtotime("January 1st, {$year}");
    	$end = strtotime("December 31st, {$year}");
		
		$start_date = date_i18n($date_format,$start);
		$end_date = date_i18n($date_format,$end);
    
    } else if ( $scope == 'next_year' ) {
    
    	$year = $year+1;
    	$start = strtotime("January 1st, {$year}");
    	$end = strtotime("December 31st, {$year}");
		
		$start_date = date_i18n($date_format,$start);
		$end_date = date_i18n($date_format,$end);
    
    }
	
	$dates['start'] = $start_date;
	$dates['end'] 	= $end_date;
	
	return $dates;
	
}

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

/*** ***/

// The following is a WIP modified version of the sdg_search_form function in the music module
// TODO: make this function less STC-specific
// TODO: generalize for use with all post types
// TODO: revise to eliminate complex query -- make fastest possible initial query that can be stored in object cache
// ... e.g. match by title, or if no title field being searched, then get ids of all posts of certain type; or maybe limit by one criterion such as only choral works? TBD
// ... then loop through returned ids based on search criteria
add_shortcode('sdg_search_form_v2', 'sdg_search_form_v2');
function sdg_search_form_v2 ( $atts = array(), $content = null, $tag = '' ) {
	
	$info = "";
    $ts_info = "";
    $search_values = array(); // var to track whether any search values have been submitted and to which post_types they apply
    
    $ts_info .= '_GET: <pre>'.print_r($_GET,true).'</pre>'; // tft
    //$ts_info .= '_REQUEST: <pre>'.print_r($_REQUEST,true).'</pre>'; // tft
        
	$args = shortcode_atts( array(
        'form_type'    => 'simple_search',
		'post_type'    => 'post',
		'fields'       => null,
        'limit'        => '-1'
    ), $atts );
    
    // Extract
	extract( $args );
	
	//$ts_info .= "form_type: $form_type<br />";
	//$ts_info .= "post_type: $post_type<br />";

    // After building the form, assuming any search terms have been submitted, we're going to call the function birdhive_get_posts
    // In prep for that search call, initialize some vars to be used in the args array
    // Set up basic query args
    $bgp_args = array(
		'post_type'       => array( $post_type ), // Single item array, for now. May add other related_post_types -- e.g. repertoire; edition
		'post_status'     => 'publish',
		'posts_per_page'  => $limit, //-1, //$posts_per_page,
        'orderby'         => array( 'title' => 'ASC', 'ID' => 'ASC' ),
        'return_fields'   => 'ids',
	);
    
    // WIP / TODO: fine-tune ordering options -- e.g. for mlib: 1) rep with editions, sorted by title_clean 2) rep without editions, sorted by title_clean
    /*
    'orderby'	=> 'meta_value',
    'meta_key' 	=> '_event_start_date',
    'order'     => 'DESC',
    */
    
    /* wip -- phasing these out
    $default_query = true; // i.e. only searching by default params -- no actual search values specified
    $meta_query = array();
    $meta_query_related = array();
    $tax_query = array();
    $tax_query_related = array();
    //$options_posts = array();
    //
    $mq_components_primary = array(); // meta_query components
    $tq_components_primary = array(); // tax_query components
    $mq_components_related = array(); // meta_query components -- related post_type
    $tq_components_related = array(); // tax_query components -- related post_type
    */
    $arr_meta = array(); // for gathering meta values to match -- wip to replace meta_query
    $arr_tax = array(); // for gathering tax terms to match -- wip to replace tax_query
    
    // Get related post type(s), if any
    if ( $post_type == "repertoire" ) { // WIP: applies only for mlib
        $related_post_type = 'edition';
    } else {
        $related_post_type = null;
    }
    
    // init -- determines whether or not to *search* multiple post types -- depends on kinds of search values submitted
    $search_primary_post_type = false;
    $search_related_post_type = false;
    $query_assignment = "primary"; // init -- each field pertains to either primary or related query
    
    // Check to see if any fields have been designated via the shortcode attributes
    if ( $fields ) {
        
        // Turn the fields list into an array
        $arr_fields = sdg_att_explode( $fields );
        //$ts_info .= print_r($arr_fields, true);
        
        $info .= '<form class="sdg_search_form '.$form_type.'">';
        
        // Get all ACF field groups associated with the primary post_type
        $field_groups = acf_get_field_groups( array( 'post_type' => $post_type ) );
        
        // Get all taxonomies associated with the primary post_type
        $taxonomies = get_object_taxonomies( $post_type );
        //$info .= "taxonomies for post_type '$post_type': <pre>".print_r($taxonomies,true)."</pre>"; // tft
        
        // Set default search operator
        $search_operator = "and"; // init
        
        // Loop through the field names and create the actual form fields
        foreach ( $arr_fields as $arr_field ) {
            
            $field_info = ""; // init
            $field_name = $arr_field; // may be overrriden below
            $option_field_name = $field_name;
            $field_info .= "[".__LINE__"] field_name: $field_name; arr_field: $arr_field<br />";
            $field_info .= "[".__LINE__"] option_field_name: $option_field_name<br />";
            $alt_field_name = null; // for WIP fields/transition incomplete, e.g. repertoire_litdates replacing related_liturgical_dates
                    
            // Fine tune the field name
            if ( $field_name == "title" ) {
                $placeholder = "title"; // for input field
                if ( $post_type == "repertoire" ) { // || $post_type == "edition" // WIP: applies only for mlib
                    $field_name = "title_clean"; // todo -- address problem that editions don't have this field
                } else {
                    $field_name = "post_title";
                }
                $field_info .= "[".__LINE__"] field_name: $field_name; arr_field: $arr_field<br />";
            } else {
                $placeholder = $field_name; // for input field
            }
            
            if ( $form_type == "advanced_search" ) {
                $field_label = str_replace("_", " ",ucfirst($placeholder));
                if ( $field_label == "Repertoire category" ) { // WIP: applies only for mlib
                    $field_label = "Category";
                } else if ( $arr_field == "liturgical_date" || $field_name == "liturgical_date" || $field_label == "Related liturgical dates" ) { // WIP
                    $field_label = "Liturgical Dates";
                    $field_name = "repertoire_litdates";
                    $option_field_name = $field_name;
                    $alt_field_name = "related_liturgical_dates";
                }/* else if ( $field_name == "edition_publisher" ) {
                    $field_label = "Publisher";
                }*/
                $field_info .= "[".__LINE__"] field_name: $field_name; arr_field: $arr_field<br />";
            }
            
            // Check to see if the field_name is an actual field, separator, or search operator
            if ( str_starts_with($field_name, '&') ) { 
                
                // This "field" is a separator/text between fields                
                $info .= substr($field_name,1).'&nbsp;';
                
            } else if ( $field_name == 'search_operator' ) {
                
                // This "field" is a search operator, i.e. search type
                
                if ( !isset($_GET[$field_name]) || empty($_GET[$field_name]) ) { $search_operator = 'and'; } else { $search_operator = $_GET[$field_name]; } // default to "and"

                $info .= 'Search Type: ';
                $info .= '<input type="radio" id="and" name="search_operator" value="and"';
                if ( $search_operator == 'and' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="and">AND <span class="tip">(match all criteria)</span></label>&nbsp;';
                $info .= '<input type="radio" id="or" name="search_operator" value="or"';
                if ( $search_operator == 'or' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="or">OR <span class="tip">(match any)</span></label>';
                $info .= '<br />';
                        
            } else if ( $field_name == 'devmode' ) {
                
                // This "field" is for testing/dev purposes only
                
                if ( !isset($_GET[$field_name]) || empty($_GET[$field_name]) ) { $devmode = 'true'; } else { $devmode = $_GET[$field_name]; } // default to "true"

                $info .= 'Dev Mode?: ';
                $info .= '<input type="radio" id="devmode" name="devmode" value="true"';
                if ( $devmode == 'true' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="true">True</label>&nbsp;';
                $info .= '<input type="radio" id="false" name="devmode" value="false"';
                if ( $devmode !== 'true' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="false">False</label>';
                $info .= '<br />';
                        
            } else {
                
                // This is an actual search field
                
                // init/defaults
                $field_type = null; // used to default to "text"
                $field_post_type = null; //$post_type;                
                $pick_object = null; // ?pods?
                $pick_custom = null; // ?pods?
                $field = null;
                $field_value = null;
                /*$mq_component = null;
                $mq_alt_component = null;
                $mq_subquery = null;
                $tq_component = null;*/
                
                // First, deal w/ title field -- special case
                if ( $field_name == "post_title" ) {
                    $field = array( 'type' => 'text', 'name' => $field_name );
                }
                //if ( $field_name == "edition_publisher"
                
                // Check to see if a field by this name is associated with the designated post_type -- for now, only in use for repertoire(?)
                $field = match_group_field( $field_groups, $field_name );
                
                if ( $field ) {
                    
                    // if field_name is same as post_type, must alter it to prevent automatic redirect when search is submitted -- e.g. "???"
                    if ( post_type_exists( $field_name ) ) { //if ( post_type_exists( $arr_field ) ) {
                        $field_name = $post_type."_".$field_name; //$field_name = $post_type."_".$arr_field;
                    }
                    $field_info .= "[".__LINE__"] field_name: $field_name; arr_field: $arr_field<br />";
                    
                    $query_assignment = "primary";
                    
                } else {
                    
                    // Field not found for primary post type >> look for related field
                    ////SEE BELOW-- $field_post_type = $related_post_type; // Should this be set later only if field or taxonomy is found?
                    
                    //$field_info .= "field_name: $field_name -- not found for $post_type >> look for related field.<br />"; // tft
                    
                    // If no matching field was found in the primary post_type, then
                    // ... get all ACF field groups associated with the related_post_type(s)                    
                    $related_field_groups = acf_get_field_groups( array( 'post_type' => $related_post_type ) );
                    $field = match_group_field( $related_field_groups, $field_name );
                                
                    if ( $field ) {
                        
                        // if field_name is same as post_type, must alter it to prevent automatic redirect when search is submitted -- e.g. "publisher" => "edition_publisher"
                        if ( post_type_exists( $field_name ) ) { //if ( post_type_exists( $arr_field ) ) {
                            $field_name = $related_post_type."_".$field_name; //$field_name = $related_post_type."_".$arr_field;
                        }
                        $field_info .= "[".__LINE__"] field_name: $field_name; arr_field: $arr_field<br />";
                        $query_assignment = "related";
                        $field_info .= "field '$arr_field' found for related_post_type: $related_post_type [field_name: $field_name].<br />"; // tft    
                        
                    } else {
                        
                        // Still no field found? Check taxonomies 
                        //$field_info .= "field_name: $field_name -- not found for $related_post_type either >> look for taxonomy.<br />"; // tft
                        
                        // For field_names matching taxonomies, check for match in $taxonomies array
                        if ( taxonomy_exists( $field_name ) ) {
                            
                            $field_info .= "'$field_name' taxonomy exists"; // .<br />
                                
                            if ( in_array($field_name, $taxonomies) ) {

                                $query_assignment = "primary";                                    
                                $field_info .= " -- found in primary taxonomies array<br />";

                            } else {
                            
                            	$field_info .= " -- NOT found in primary taxonomies array<br />";
                            	
                                // Get all taxonomies associated with the related_post_type
                                $related_taxonomies = get_object_taxonomies( $related_post_type );

                                if ( in_array($field_name, $related_taxonomies) ) {

                                    $query_assignment = "related";
                                    $field_info .= " -- found in related taxonomies array<br />";                                        

                                } else {
                                    $field_info .= " -- NOT found in related taxonomies array<br />";
                                }
                                //$info .= "taxonomies for post_type '$related_post_type': <pre>".print_r($related_taxonomies,true)."</pre>"; // tft

                            }
                            
                            $field = array( 'type' => 'taxonomy', 'name' => $field_name );
                            
                        } else {
                            //$field_info .= "Could not determine field_type for field_name: $field_name<br />";
                        }
                    }
                }
                
                if ( $field ) {
                    
                    $field_info .= "field: <pre>".print_r($field,true)."</pre>";
                    
                    if ( isset($field['post_type']) ) { $field_post_type = $field['post_type']; } else { $field_post_type = null; } // ??
                    //$field_info .= "field_post_type: ".print_r($field_post_type,true)."<br />";
                    // Check to see if more than one element in array. If not, use $field['post_type'][0]...
					if ( is_array($field_post_type) ) {
						$field_post_type = $field['post_type'][0];
						$field_info .= "field_post_type: $field_post_type<br />";
					} else {
						// ???
					}
					
                    // Check to see if a custom post type or taxonomy exists with same name as $field_name
                    // In the case of the choirplanner search form, this will be relevant for post types such as "Publisher" and taxonomies such as "Voicing"
                    if ( post_type_exists( $arr_field ) || taxonomy_exists( $arr_field ) ) {
                        $field_cptt_name = $arr_field;
                        $field_info .= "field_cptt_name: $field_cptt_name same as arr_field: $arr_field<br />";
                    } else {
                        $field_cptt_name = null;
                    }

                    //
                    $field_info .= "[".__LINE__"] field_name: $field_name; arr_field: $arr_field<br />"; //$field_info .= "field_name: $field_name<br />";
                    if ( $alt_field_name ) { $field_info .= "alt_field_name: $alt_field_name<br />"; }                    
                    $field_info .= "query_assignment: $query_assignment<br />";

                    // Check to see if a value was submitted for this field
                    if ( isset($_GET[$field_name]) ) { // if ( isset($_REQUEST[$field_name]) ) {
                        
                        $field_value = $_GET[$field_name]; // $field_value = $_REQUEST[$field_name];
                        
                        // If field value is not empty...
                        if ( !empty($field_value) && $field_name != 'search_operator' && $field_name != 'devmode' ) {
                            //$search_values = true; // actual non-empty search values have been found in the _GET/_REQUEST array
                            // instead of boolean, create a search_values array? and track which post_type they relate to?
                            $search_values[] = array( 'field_post_type' => $field_post_type, 'arr_field' => $arr_field, 'field_name' => $field_name, 'field_value' => $field_value );
                            //$field_info .= "field value: $field_value<br />"; 
                            //$ts_info .= "query_assignment for field_name $field_name is *$query_assignment* >> search value: '$field_value'<br />";
                            
                            if ( $query_assignment == "primary" ) {
                                $search_primary_post_type = true;
                                $ts_info .= ">> Setting search_primary_post_type var to TRUE based on field $field_name searching value $field_value<br />";
                            } else {
                                $search_related_post_type = true;
                                $field_info .= ">> Setting search_related_post_type var to TRUE based on field $field_name searching value $field_value<br />";
                            }
                            
                        }
                        
                        $field_info .= "field value: $field_value<br />";
                        
                    } else {
                        //$field_info .= "field value: [none]<br />";
                        $field_value = null;
                    }

                    // Get 'type' field option
                    $field_type = $field['type'];
                    $field_info .= "field_type: $field_type<br />"; // tft
                    
                    if ( !empty($field_value) ) {
                        $field_value = sanitize($field_value); //$field_value = sdg_sanitize($field_value);
                    }
                    
                    //$field_info .= "field_name: $field_name<br />";                    
                    //$field_info .= "value: $field_value<br />";
                    
                    if ( $field_type !== "text" && $field_type !== "taxonomy" ) {
                        //$field_info .= "field: <pre>".print_r($field,true)."</pre>"; // tft
                        //$field_info .= "field key: ".$field['key']."<br />";
                        //$field_info .= "field return_format: ".$field['return_format']."<br />";
                    }                    
                    
                    if ( ( $field_name == "post_title" ) && !empty($field_value) ) {
                    	$bgp_args['_search_title'] = $field_value; // custom parameter -- see posts_where filter fcn
                    }
                    
                    // Not a title field >> meta or taxonomy, depending on field_type
                    // WIP
                    if ( $field_type == "text" && !empty($field_value) && $field_name != "post_title" ) {
                        
                        $match_value = $field_value;
                        
                        // WIP: figure out how to ignore punctuation in meta_value -- e.g. veni, redemptor...
                        if ( $field_name == "title_clean" && strpos($match_value," ") ) { $match_value = str_replace(" ","XXX",$match_value); }                        
                        
                        // TODO: figure out how to determine whether to match exact or not for particular fields
                        // -- e.g. box_num should be exact, but not necessarily for title_clean?
                        // For now, set it explicitly per field_name
                        if ( $field_name == "box_num" ) { // WIP: applies only for mlib; must be generalized for non-STC libs
                        	//$match_value = "'".$match_value."'";
                        	//$match_value = '"'.$match_value.'"'; // matches exactly "123", not just 123. This prevents a match for "1234"
                        } else {
                        	$match_value = "XXX".$match_value."XXX";
                        }
                        
                        // If querying title_clean, then also query tune_name -- WIP: applies only for mlib                        
                        if ( $field_name == "title_clean" && $post_type == "repertoire" ) {
                        	
                        	$arr_meta[] = array( 'query_assignment' => $query_assignment, 'relation' => 'OR', 'meta_key' => array('title_clean','tune_name'), 'meta_value' => $match_value, 'comparison' => 'LIKE', 'field_name' => $field_name, 'field_type' => $field_type ); // wip
                        	
                        	$mq_component = array(
								'relation' => 'OR',
								array(
									'key'   => 'title_clean',
									'value' => $match_value,
									'compare'=> 'LIKE'
								),
								array(
									'key'   => 'tune_name',
									'value' => $match_value,
									'compare'=> 'LIKE'
								)
							);
                        } else {
                        
                        	$arr_meta[] = array( 'query_assignment' => $query_assignment, 'relation' => null, 'meta_key' => $field_name, 'meta_value' => $match_value, 'comparison' => 'LIKE', 'field_name' => $field_name, 'field_type' => $field_type ); // wip
                        	
                        	$mq_component = array(
								'key'   => $field_name,
								'value' => $match_value,
								'compare'=> 'LIKE'
							);
                        }

                    } else if ( $field_type == "select" && !empty($field_value) ) {
                        
                        // If field allows multiple values, then values will return as array and we must use LIKE comparison
                        if ( $field['multiple'] == 1 ) {
                            $compare = 'LIKE';
                        } else {
                            $compare = '=';
                        }
                        
                        $match_value = $field_value;
                        $mq_component = array(
                            'key'   => $field_name,
                            'value' => $match_value,
                            'compare'=> $compare
                        );
						
                    } else if ( $field_type == "checkbox" && !empty($field_value) ) {
                        
                        $compare = 'LIKE';
                        
                        $match_value = $field_value;
                        $mq_component = array(
                            'key'   => $field_name,
                            'value' => $match_value,
                            'compare'=> $compare
                        );

                    } else if ( $field_type == "relationship" ) {
                    
                    	if ( !empty($field_value) ) {
                            
                            $field_value_converted = ""; // init var for storing ids of posts matching field_value
                            
                            // If $options,
                            if ( !empty($options) ) {
                                
                                if ( $arr_field == "publisher" ) {
                                    $key = $arr_field; // can't use field_name because of redirect issue
                                } else {
                                    $key = $field_name;
                                }
                                $mq_component = array(
                                    'key'   => $key, 
                                    //'value' => $match_value,
                                    // TODO: FIX -- value as follows doesn't work w/ liturgical dates because it's trying to match string, not id... need to get id!
                                    'value' => '"' . $field_value . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                                    'compare'=> 'LIKE', 
                                );
                                
                                $field_info .= "mq_component: ".print_r($mq_component,true)."<br />";
                                
                                if ( $alt_field_name ) {
                                    
                                    $meta_query['relation'] = 'OR';
                                    
                                    $mq_alt_component = array(
                                        'key'   => $alt_field_name,
                                        //'value' => $field_value,
                                        // TODO: FIX -- value as follows doesn't work w/ liturgical dates because it's trying to match string, not id... need to get id!
                                        'value' => '"' . $field_value . '"',
                                        'compare'=> 'LIKE'
                                    );
                                }
                                
                            } else {
                                
                                // If no $options, match search terms
                                $field_info .= "options array is empty.<br />";
                                
                                // Get id(s) of any matching $field_post_type records with post_title like $field_value
                                $field_value_args = array('post_type' => $field_post_type, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', '_search_title' => $field_value, 'suppress_filters' => FALSE );
                                //$field_value_posts = get_posts( $field_value_args );
                                $field_value_query = new WP_Query( $field_value_args );
                                $field_value_posts = $field_value_query->posts;
                                //
                                if ( count($field_value_posts) > 0 ) {

                                    $field_info .= count($field_value_posts)." field_value_posts found<br />";
                                    //$field_info .= "field_value_args: <pre>".print_r($field_value_args, true)."</pre><br />";

                                    // The problem here is that, because ACF stores multiple values as a single meta_value array, 
                                    // ... it's not possible to search efficiently for an array of values
                                    // TODO: figure out if there's some way to for ACF to store the meta_values in separate rows?
                                    
                                    $mq_subquery = array();
                                    
                                    if ( count($field_value_posts) > 1 ) {
                                        $mq_subquery['relation'] = 'OR';
                                    }
                                    
                                    // TODO: make this a subquery to better control relation
                                    foreach ( $field_value_posts as $fvp_id ) {
                                        $mq_subquery[] = [
                                            'key'   => $arr_field, // can't use field_name because of "publisher" issue
                                            //'key'   => $field_name,
                                            'value' => '"' . $fvp_id . '"',
                                            'compare' => 'LIKE',
                                        ];
                                        $field_info .= "mq_subquery: ".print_r($mq_subquery,true)."<br />";
                                    }
                                    
                                } else {
                                	// No matches found!
                                	$field_info .= "count(field_value_posts) not > 0<br />";
                                	//$field_info .= "field_value_args: ".print_r($field_value_args,true)."<br />";
                                	//$field_info .= "field_value_query->request: ".$field_value_query->request."<br />";
                                }
                                
                            }
                            
                            //$field_info .= ">> WIP: set meta_query component for: $field_name = $field_value<br/>";
                            $field_info .= "Added meta_query_component for key: $field_name, value: $field_value<br/>";
                            
                        }
                        
                        // For text fields, may need to get ID matching value -- e.g. person id for name mousezart (220824), if composer field were not set up as combobox -- maybe faster?
                        
                                                
                        /* ACF
                        create_field( $field_name ); // new ACF fcn to generate HTML for field 
                        // see https://www.advancedcustomfields.com/resources/creating-wp-archive-custom-field-filter/ and https://www.advancedcustomfields.com/resources/upgrade-guide-version-4/
                        
                        
                        // Old ACF -- see ca. 08:25 in video tutorial:
                        $field_obj = get_field_object($field_name);
                        foreach ( $field_obj['choices'] as $choice_value => $choice_label ) {
                            // checkbox code or whatever
                        }                        
                        */

                    } else if ( $field_type == "taxonomy" && !empty($field_value) ) {
                            
                        $field_info .= ">> WIP: field_type: taxonomy; field_name: $field_name; post_type: $post_type; terms: $field_value<br />"; // tft

						$tq_component = array (
							'taxonomy' => $field_name,
							//'field'    => 'slug',
							'terms'    => $field_value,
						);
					
						// Add query component to the appropriate components array
						
                        if ( $post_type == "repertoire" ) { // WIP: applies only for mlib

                            // Since rep & editions share numerous taxonomies in common, check both -- WIP                             
                            $related_field_name = 'repertoire_editions'; //$related_field_name = 'related_editions';
                            
                            // Add a tax query somehow to search for related_post_type posts with matching taxonomy value                            
                            // Create a secondary query for related_post_type?
                            // PROBLEM WIP -- tax_query doesn't seem to work with two post_types if tax only applies to one of them?
                            
                            /*
                            $tq_components_primary[] = array(
                                'taxonomy' => $field_name,
                                //'field'    => 'slug',
                                'terms'    => $field_value,
                            );
                            
                            // Add query component to the appropriate components array
                            if ( $query_assignment == "primary" ) {
                                $tq_components_primary[] = $tq_component;
                            } else {
                                $tq_components_related[] = $tq_component;
                            }
                            */

                        } else {
                        	//
                        }

                    }
                    
					// Add query components to the appropriate components arrays
					// Meta query
					if ( $mq_component ) {
						$default_query = false;
						$field_info .= ">> Added $query_assignment meta_query_component for key: $field_name, value: $match_value<br/>";
						if ( $query_assignment == "primary" ) { $mq_components_primary[] = $mq_component; } else { $mq_components_related[] = $mq_component; }
					}
					// Meta query alt
					if ( $mq_alt_component ) {
						$default_query = false;
						if ( $query_assignment == "primary" ) { $mq_components_primary[] = $mq_alt_component; } else { $mq_components_related[] = $mq_alt_component; }
					}
					// Meta subquery
					if ( $mq_subquery ) {
						$default_query = false;
						if ( $query_assignment == "primary" ) { $mq_components_primary[] = $mq_subquery; } else { $mq_components_related[] = $mq_subquery; }
					}
                    // Tax query
					if ( $tq_component ) {
						$default_query = false;
						if ( $query_assignment == "primary" ) { $tq_components_primary[] = $tq_component; } else { $tq_components_related[] = $tq_component; }
					}
					
                    //$field_info .= "-----<br />";
                    
                } else {
                	$field_info .= "No field found for field_name $field_name <br />";
                } // END if ( $field )
                
                
                // Set up the form fields
                // ----------------------
                if ( $form_type == "advanced_search" ) {
                   
                    //$field_info .= "CONFIRM field_type: $field_type<br />"; // tft
                    
                    $input_class = "advanced_search";
                    $input_html = "";
                    $options = array();
                    
                    if ( in_array($field_name, $taxonomies) ) {
                        $input_class .= " primary_post_type";
                        $field_label .= "*";
                    }                    
                    
                    $info .= '<label for="'.$field_name.'" class="'.$input_class.'">'.$field_label.':</label>';
                    
                    if ( $field_type == "text" ) {
                        
                        $input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="'.$input_class.'" />';                                            
                    
                    } else if ( $field_type == "select" || $field_type == "checkbox" ) {
                        
                        if ( isset($field['choices']) ) {
                            $options = $field['choices'];
                            //$field_info .= "field: <pre>".print_r($field, true)."</pre>";
                            //$field_info .= "field choices: <pre>".print_r($field['choices'],true)."</pre>"; // tft
                        } else {
                            $options = null; // init
                            $field_info .= "No field choices found. About to go looking for values to set as options...<br />";
                            $field_info .= "field: <pre>".print_r($field, true)."</pre>";
                        }
                        
                    } else if ( $field_type == "relationship" ) {
                        
                        if ( $field_cptt_name ) { $field_info .= "field_cptt_name: $field_cptt_name<br />"; } // tft 
                        if ( $arr_field ) { $field_info .= "arr_field: $arr_field<br />"; } // tft 
                        
                        // repertoire_litdates
                        // related_liturgical_dates
                        
                        //if ( $field_cptt_name != $arr_field ) {
                        //if ( $field_cptt_name != $field_name ) {
                            
                            $field_info .= "field_cptt_name NE arr_field<br />"; // tft
                            //$field_info .= "field_cptt_name NE field_name<br />"; // tft
                            
                            // TODO: 
                            if ( $field_post_type && $field_post_type != "person" ) { // TMP disable options for person fields so as to allow for free autocomplete // && $field_post_type != "publisher"
                                
                                $field_info .= "looking for options...<br />";
                                
                                // TODO: consider when to present options as combo box and when to go for autocomplete text
                                // For instance, what if the user can't remember which Bach wrote a piece? Should be able to search for all...
                                
                                // e.g. field_post_type = person, field_name = composer 
                                // ==> find all people in Composers person_category -- PROBLEM: people might not be correctly categorized -- this depends on good data entry
                                // -- alt: get list of composers who are represented in the music library -- get unique meta_values for meta_key="composer"

                                // TODO: figure out how to filter for only composers related to editions? or lit dates related to rep... &c.
                                // TODO: find a way to do this more efficiently, perhaps with a direct wpdb query to get all unique meta_values for relevant keys
                                
                                //
                                // set up WP_query
                                // TODO: fix keys -- should be `repertoire_litdates` NOT `repertoire_liturgical_date`; `publisher` NOT `edition_publisher`; 
                                // also make sure not to use alt_field_name if it's EMPTY!
                                if ( $query_assignment == "primary" ) { $options_post_type = $post_type; } else { $options_post_type = $related_post_type; }
                                //$option_field_name = $field_cptt_name;
                                //$option_field_name = $field_name;
                                
                                $options_args = array(
                                    'post_type' => $options_post_type,
                                    'post_status' => 'publish',
                                    'fields' => 'ids',
                                    'posts_per_page' => -1, // get them all
                                    'meta_query' => array(
                                        //'relation' => 'OR',
                                        array(
                                            'key'     => $option_field_name, // $arr_field instead?
                                            'compare' => 'EXISTS'
                                        ),
                                        /*array(
                                            'key'     => $alt_field_name,
                                            'compare' => 'EXISTS'
                                        ),*/
                                    ),
                                );
                                
                                $options_arr_posts = new WP_Query( $options_args );
                                $options_posts = $options_arr_posts->posts;

                                $field_info .= count($options_posts)." options_posts found <br />"; // tft
                                $field_info .= "options_args: <pre>".print_r($options_args,true)."</pre>";
                                //if ( count($options_posts) == 0 ) { $field_info .= "options_args: <pre>".print_r($options_args,true)."</pre>"; }
                                //$field_info .= "options_posts: <pre>".print_r($options_posts,true)."</pre>"; // tft

                                $arr_ids = array(); // init

                                foreach ( $options_posts as $options_post_id ) {

                                    // see also get composer_ids
                                    $meta_values = get_field($option_field_name, $options_post_id, false);
                                    $alt_meta_values = get_field($alt_field_name, $options_post_id, false);
                                    if ( !empty($meta_values) ) {
                                        //$field_info .= count($meta_values)." meta_value(s) found for field_name: $field_name and post_id: $options_post_id.<br />";
                                        foreach ($meta_values AS $meta_value) {
                                            $arr_ids[] = $meta_value;
                                        }
                                    }
                                    if ( !empty($alt_meta_values) ) {
                                        //$field_info .= count($alt_meta_values)." meta_value(s) found for alt_field_name: $alt_field_name and post_id: $options_post_id.<br />";
                                        foreach ($alt_meta_values AS $meta_value) {
                                            $arr_ids[] = $meta_value;
                                        }
                                    }

                                }

                                $arr_ids = array_unique($arr_ids);

                                // Build the options array from the ids
                                foreach ( $arr_ids as $id ) {
                                    if ( $field_post_type == "person" ) {
                                        $last_name = get_post_meta( $id, 'last_name', true );
                                        $first_name = get_post_meta( $id, 'first_name', true );
                                        $middle_name = get_post_meta( $id, 'middle_name', true );
                                        //
                                        $option_name = $last_name;
                                        if ( !empty($first_name) ) {
                                            $option_name .= ", ".$first_name;
                                        }
                                        if ( !empty($middle_name) ) {
                                            $option_name .= " ".$middle_name;
                                        }
                                        //$option_name = $last_name.", ".$first_name;
                                        $options[$id] = $option_name;
                                        // TODO: deal w/ possibility that last_name, first_name fields are empty
                                    } else {
                                        $options[$id] = get_the_title($id);
                                    }
                                }
                                
                                asort($options);

                            } else {
                            	$field_info .= "NOT looking for options for this relationship field...<br />";
                            	$input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="'.$input_class.'" />';
                            }

                            

                        //} else {
                        	
                        	//$input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="'.$input_class.'" />';
                    		//$input_html = "LE TSET"; // tft
                        	//$input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="autocomplete '.$input_class.' relationship" />';
                        //}
                        
                    } else if ( $field_type == "taxonomy" ) {
                        
                        // Get options, i.e. taxonomy terms
                        $obj_options = get_terms ( $field_name );
                        //$info .= "options for taxonomy $field_name: <pre>".print_r($options, true)."</pre>"; // tft
                        
                        // Convert objects into array for use in building select menu
                        foreach ( $obj_options as $obj_option ) { // $option_value => $option_name
                            
                            $option_value = $obj_option->term_id;
                            $option_name = $obj_option->name;
                            //$option_slug = $obj_option->slug;
                            $options[$option_value] = $option_name;
                        }
                        
                    } else {
                        
                        $field_info .= "Could not determine field_type for field_name: $field_name<br />"; //$field_info .= "field_type could not be determined.<br />";
                        
                    }
                    
                    if ( !empty($options) ) { // WIP // && strpos($input_class, "combobox")

                        //if ( !empty($field_value) ) { $ts_info .= "options: <pre>".print_r($options, true)."</pre>"; } // tft

                        $input_class .= " combobox"; // tft
                                                
                        $input_html = '<select name="'.$field_name.'" id="'.$field_name.'" class="'.$input_class.'">'; 
                        $input_html .= '<option value>-- Select One --</option>'; // default empty value // class="'.$input_class.'"
                        
                        // Loop through the options to build the select menu
                        foreach ( $options as $option_value => $option_name ) {
                            $input_html .= '<option value="'.$option_value.'"';
                            if ( $option_value == $field_value ) { $input_html .= ' selected="selected"'; }
                            //if ( $option_name == "Men-s Voices" ) { $option_name = "Men's Voices"; }
                            $input_html .= '>'.$option_name.'</option>'; //  class="'.$input_class.'"
                        }
                        $input_html .= '</select>';

                    } else if ( $options && strpos($input_class, "multiselect") !== false ) {
                        // TODO: implement multiple select w/ remote source option in addition to combobox (which is for single-select inputs) -- see choirplanner.js WIP
                    } else if ( empty($input_html) ) {
                        $input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="autocomplete '.$input_class.'" />'; // tft
                    }
                    
                    $info .= $input_html;
                    
                } else {
                    $input_class = "simple_search";
                    $info .= '<input type="text" id="'.$field_name.'" name="'.$field_name.'" placeholder="'.$placeholder.'" value="'.$field_value.'" class="'.$input_class.'" />';
                }
                
                if ( $form_type == "advanced_search" ) {
                    $info .= '<br />';
                    /*$info .= '<div class="devview">';
                    $info .= '<span class="troubleshooting smaller">'.$field_info.'</span>\n'; // tft
                    $info .= '</div>';*/
                    //$info .= '<!-- '."\n".$field_info."\n".' -->';
                }
                
                //$ts_info .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />";
                //if ( strpos($field_name, "publisher") || strpos($field_name, "devmode") || strpos($arr_field, "devmode") || $field_name == "devmode" ) {
                if ( (!empty($field_value) && $field_name != 'search_operator' && $field_name != 'devmode' ) ||
                   ( !empty($options_posts) && count($options_posts) > 0 ) ||
                   strpos($field_name, "liturgical") ) {
                    //$ts_info .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />";
                }
                $ts_info .= "+++++ FIELD INFO +++++<br />".$field_info."<br />";
                //$field_name == "liturgical_date" || $field_name == "repertoire_litdates" || 
                //if ( !empty($field_value) ) { $ts_info .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />"; }
                
            } // End conditional for actual search fields
            
        } // end foreach ( $arr_fields as $field_name )
        
        $info .= '<input type="submit" value="Search Library">';
        $info .= '<a href="#!" id="form_reset">Clear Form</a>';
        $info .= '</form>';
        
        // 
        $bgp_args_related = array(); // init
        $rep_cat_queried = false;
        
        //$ts_info .= "mq_components_primary: <pre>".print_r($mq_components_primary,true)."</pre>"; // tft
        //if ( !empty($tq_components_primary) ) { $ts_info .= "tq_components_primary: <pre>".print_r($tq_components_primary,true)."</pre>"; } // tft
        //$ts_info .= "mq_components_related: <pre>".print_r($mq_components_related,true)."</pre>"; // tft
        //if ( !empty($tq_components_primary) ) { $ts_info .= "tq_components_related: <pre>".print_r($tq_components_related,true)."</pre>"; } // tft
        
        // If field values were found related to both post types,
        // AND if we're searching for posts that match ALL terms (search_operator: "and"),
        // then set up a second set of args/birdhive_get_posts
        
        if ( $search_primary_post_type == true ) {
			$bgp_args['post_type'] = $post_type;
		}
		
		if ( $search_related_post_type == true ) {
			if ( is_array($bgp_args) && is_array($bgp_args_related) ) {
				$bgp_args_related = array_merge( $bgp_args_related, $bgp_args ); //$bgp_args_related = $bgp_args;
			}
            $bgp_args_related['post_type'] = $related_post_type;
        }
        
        if ( $search_primary_post_type == true && $search_related_post_type == true && $search_operator == "and" ) { 
            $ts_info .= "Querying both primary and related post_types (two sets of args)<br />";            
        } else if ( $search_primary_post_type == true && $search_related_post_type == true && $search_operator == "or" ) { 
            // WIP -- in this case
            $ts_info .= "Querying both primary and related post_types (two sets of args) but with OR operator... WIP<br />";
        } else {
            if ( $search_primary_post_type == true ) {
                // Searching primary post_type only
                $ts_info .= "Searching primary post_type only<br />";                
            } else if ( $search_related_post_type == true ) {
                // Searching related post_type only
                $ts_info .= "Searching related post_type only<br />";
                $bgp_args = null; // reset primary args to prevent triggering of second query
            }
        }
        
        // Finalize meta_query or queries
        // ==============================
        /* 
        WIP if meta_key = title_clean and related_post_type is true then incorporate also, using title_clean meta_value:
        $bgp_args['_search_title'] = $field_value; // custom parameter -- see posts_where filter fcn
        */
        
        if ( $search_primary_post_type == true ) {
			if ( count($mq_components_primary) > 1 && empty($meta_query['relation']) ) {
				$meta_query['relation'] = $search_operator;
			}
			if ( count($mq_components_primary) == 1) {                
				$meta_query = $mq_components_primary; //$meta_query = $mq_components_primary[0];
			} else {
				foreach ( $mq_components_primary AS $component ) {
					$meta_query[] = $component;
				}
			}
			/*foreach ( $mq_components_primary AS $component ) {
				$meta_query[] = $component;
			}*/
			if ( !empty($meta_query) ) { $bgp_args['meta_query'] = $meta_query; }
		}
		
		// related query
		if ( $search_related_post_type == true ) {
			if ( count($mq_components_related) > 1 && empty($meta_query_related['relation']) ) {
				$meta_query_related['relation'] = $search_operator;
			}
			if ( count($mq_components_related) == 1) {
				$meta_query_related = $mq_components_related; //$meta_query_related = $mq_components_related[0];
			} else {
				foreach ( $mq_components_related AS $component ) {
					$meta_query_related[] = $component;
				}
			}
			/*foreach ( $mq_components_related AS $component ) {
				$meta_query_related[] = $component;
			}*/
			if ( !empty($meta_query_related) ) { $bgp_args_related['meta_query'] = $meta_query_related; }
		}            
        
        // Finalize tax_query or queries
        // =============================
        
        if ( $search_primary_post_type == true ) {
			if ( count($tq_components_primary) > 1 && empty($tax_query['relation']) ) {
				$tax_query['relation'] = $search_operator;
			}
			// WIP: exclusions below apply only for mlib
			$rep_cat_exclusions = array('organ-works', 'piano-works', 'instrumental-music', 'instrumental-solo', 'orchestral', 'brass-music', 'psalms', 'hymns', 'noble-singers-repertoire', 'guest-ensemble-repertoire'); //, 'symphonic-works'
			$admin_tag_exclusions = array('exclude-from-search', 'external-repertoire');
			
			foreach ( $tq_components_primary AS $component ) {
		
				// Check to see if component relates to repertoire_category
				if ( $post_type == "repertoire" ) { // WIP: applies only for mlib
				
					$ts_info .= "tq component: <pre>".print_r($component,true)."</pre>";
					
					// TODO: limit this to apply to choirplanner search forms only (in case we eventually build a separate tool for searching organ works)
					// TODO: generalize this option to all cats to be set via SDG options -- currently it is VERY STC-specific...
					if ( $component['taxonomy'] == "repertoire_category" ) {
				
						$rep_cat_queried = true;
					
						// Add 'AND' relation...
						$component = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'repertoire_category',
								'terms'    => $component['terms'],
								'operator' => 'IN',
							),
							array(
								'taxonomy' => 'repertoire_category',
								'field'    => 'slug',
								'terms'    => $rep_cat_exclusions,
								'operator' => 'NOT IN',
								//'include_children' => true,
							),
							array(
								'taxonomy' => 'admin_tag',
								'field'    => 'slug',
								'terms'    => $admin_tag_exclusions,
								'operator' => 'NOT IN',
								//'include_children' => true,
							),
						);
						$default_query = false;
						$ts_info .= "revised component: <pre>".print_r($component,true)."</pre>";
					}
				}
				$tax_query[] = $component;
			}
			if ( $post_type == "repertoire" && $rep_cat_queried == false ) { // WIP: applies only for mlib
				$tax_query[] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'repertoire_category',
						'field'    => 'slug',
						'terms'    => $rep_cat_exclusions,
						'operator' => 'NOT IN',
						//'include_children' => true,
					),
					array(
						'taxonomy' => 'admin_tag',
						'field'    => 'slug',
						'terms'    => $admin_tag_exclusions,
						'operator' => 'NOT IN',
						//'include_children' => true,
					),
				);
			}
			if ( !empty($tax_query) ) { $bgp_args['tax_query'] = $tax_query; }
		}
		
		// related query
		if ( $search_related_post_type == true ) {
			if ( count($tq_components_related) > 1 && empty($tax_query_related['relation']) ) {
				$tax_query_related['relation'] = $search_operator;
			}
			foreach ( $tq_components_related AS $component ) {
				$tax_query_related[] = $component;
			}
			if ( !empty($tax_query_related) ) { $bgp_args_related['tax_query'] = $tax_query_related; }
		}            

        ///// WIP
        if ( $search_related_post_type == true && $related_post_type ) {
            
            // If we're dealing with multiple post types, then the and/or is extra-complicated, because not all taxonomies apply to all post_types
            // Must be able to find, e.g., repertoire with composer: Mousezart as well as ("OR") all editions/rep with instrument: Bells
            
            if ( $search_operator == "or" ) {
                if ( !empty($tax_query) && !empty($meta_query) ) {
                    $bgp_args['_meta_or_tax'] = true; // custom parameter -- see posts_where filters
                }
            }
        }
        /////
        
        // If search values have been submitted, then run the search query
        if ( count($search_values) > 0 ) {
            
            if ( $search_primary_post_type == true && $bgp_args ) {
				$ts_info .= "About to pass bgp_args to birdhive_get_posts: <pre>".print_r($bgp_args,true)."</pre>"; // tft
			
				// Get posts matching the assembled args
				/* ===================================== */
				if ( $default_query === true ) {
					$ts_info .= "Default query -- no need to run a search<br />";
				} else {
					if ( $form_type == "advanced_search" ) {
						//$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $posts_info = array(); // tft
						$posts_info = birdhive_get_posts( $bgp_args );
					} else {
						$posts_info = birdhive_get_posts( $bgp_args );
					}
					
					if ( isset($posts_info['arr_posts']) ) {
				
						$arr_post_ids = $posts_info['arr_posts']->posts; // Retrieves an array of IDs (based on return_fields: 'ids')
						$ts_info .= "Num arr_post_ids: [".count($arr_post_ids)."]<br />";
						//$ts_info .= "arr_post_ids: <pre>".print_r($arr_post_ids,true)."</pre>"; // tft
				
						$info .= '<div class="troubleshooting">'.$posts_info['ts_info'].'</div>';
				
						// Print last SQL query string
						//global $wpdb;
						//$info .= '<div class="troubleshooting">'."last_query:<pre>".$wpdb->last_query."</pre>".'</div>'; // tft
						//$ts_info .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>"; // tft
				
					}
				}			
			}
            
            if ( $search_related_post_type == true && $bgp_args_related && $default_query == false ) {
                
                $ts_info .= "About to pass bgp_args_related to birdhive_get_posts: <pre>".print_r($bgp_args_related,true)."</pre>"; // tft
                
                //$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $related_posts_info = array(); // tft
                $related_posts_info = birdhive_get_posts( $bgp_args_related );
                
                if ( isset($related_posts_info['arr_posts']) ) {
                
                    $arr_related_post_ids = $related_posts_info['arr_posts']->posts;
                    $ts_info .= "Num arr_related_post_ids: [".count($arr_related_post_ids)."]<br />";
                    //$ts_info .= "arr_related_post_ids: <pre>".print_r($arr_related_post_ids,true)."</pre>"; // tft

                    if ( isset($related_posts_info['info']) ) { $info .= '<div class="troubleshooting">'.$related_posts_info['info'].'</div>'; }
					if ( isset($related_posts_info['ts_info']) ) { $info .= '<div class="troubleshooting">'.$related_posts_info['ts_info'].'</div>'; }
                    
                    // WIP -- we're running an "and" so we need to find the OVERLAP between the two sets of ids... one set of repertoire ids, one of editions... hmm...
                    if ( !empty($arr_post_ids) ) {
                        
                        $ts_info .= "arr_post_ids NOT empty <br />";
                        
                        $related_post_field_name = "repertoire_editions"; // TODO: generalize!
                        
                        $full_match_ids = array(); // init
                        
                        // Search through the smaller of the two data sets and find posts that overlap both sets; return only those
                        // TODO: eliminate redundancy
                        if ( count($arr_post_ids) > count($arr_related_post_ids) ) {
                            // more rep than edition records
                            $ts_info .= "more rep than edition records >> loop through arr_related_post_ids<br />";
                            foreach ( $arr_related_post_ids as $tmp_id ) {
                                $ts_info .= "tmp_id: $tmp_id<br />";
                                $tmp_posts = get_field($related_post_field_name, $tmp_id); // repertoire_editions
                                if ( empty($tmp_posts) ) { $tmp_posts = get_field('musical_work', $tmp_id); } // WIP/tmp
                                if ( $tmp_posts ) {
                                    foreach ( $tmp_posts as $tmp_match ) {
                                        // Get the ID
                                        if ( is_object($tmp_match) ) {
                                            $tmp_match_id = $tmp_match->ID;
                                        } else {
                                            $tmp_match_id = $tmp_match;
                                        }
                                        // Look
                                        if ( in_array($tmp_match_id, $arr_post_ids) ) {
                                            // it's a full match -- keep it
                                            $full_match_ids[] = $tmp_match_id;
                                            $ts_info .= "$related_post_field_name tmp_match_id: $tmp_match_id -- FOUND in arr_post_ids<br />";
                                        } else {
                                            $ts_info .= "$related_post_field_name tmp_match_id: $tmp_match_id -- NOT found in arr_post_ids<br />";
                                        }
                                    }
                                } else {
                                    $ts_info .= "No $related_post_field_name records found matching related_post_id $tmp_id<br />";
                                }
                            }
                        } else {
                            // more editions than rep records
                            $ts_info .= "more editions than rep records >> loop through arr_post_ids<br />";
                            foreach ( $arr_post_ids as $tmp_id ) {
                                $tmp_posts = get_field($related_post_field_name, $tmp_id); // repertoire_editions
                                if ( empty($tmp_posts) ) { $tmp_posts = get_field('related_editions', $tmp_id); } // WIP/tmp
                                if ( $tmp_posts ) {
                                    foreach ( $tmp_posts as $tmp_match ) {
                                        // Get the ID
                                        if ( is_object($tmp_match) ) {
                                            $tmp_match_id = $tmp_match->ID;
                                        } else {
                                            $tmp_match_id = $tmp_match;
                                        }
                                        // Look for a match in arr_post_ids
                                        if ( in_array($tmp_match_id, $arr_related_post_ids) ) {
                                            // it's a full match -- keep it
                                            $full_match_ids[] = $tmp_match_id;
                                        } else {
                                            $ts_info .= "$related_post_field_name tmp_match_id: $tmp_match_id -- NOT in arr_related_post_ids<br />";
                                        }
                                    }
                                }
                            }
                        }
                        //$arr_post_ids = array_merge($arr_post_ids, $arr_related_post_ids); // Merge $arr_related_posts into arr_post_ids -- nope, too simple
                        $arr_post_ids = $full_match_ids;
                        $ts_info .= "Num full_match_ids: [".count($full_match_ids)."]".'</div>';
                        
                    } else {
                    	$ts_info .= "Primary arr_post_ids is empty >> use arr_related_post_ids as arr_post_ids<br />";
                        $arr_post_ids = $arr_related_post_ids;
                    }

                }
            }
            
            // 
            
            if ( !empty($arr_post_ids) ) {
                    
                //$ts_info .= "Num matching posts found (raw results): [".count($arr_post_ids)."]"; 
                $info .= '<div class="troubleshooting">'."Num matching posts found (raw results): [".count($arr_post_ids)."]".'</div>'; // tft -- if there are both rep and editions, it will likely be an overcount
                
                // WIP alt to complex (and very slow) query based on search
                // Loop through arr_post_ids and narrow things down based on search criteria
                foreach ( $arr_post_ids as $post_id ) {
                
                	// Get all post_meta for post_id
                	$post_meta = get_post_meta($post_id);
                	
                	foreach ( $arr_meta as $meta_test ) {
                	
                		//$arr_meta[] = array( 'query_assignment' => $query_assignment, 'relation' => 'OR', 'meta_key' => array('title_clean','tune_name'), 'meta_value' => $match_value, 'comparison' => 'LIKE', 'field_name' => $field_name, 'field_type' => $field_type );
                		if ( is_array($meta_test['meta_key'] ) ) {
                			
                		} else {
                			$test_key = $meta_test['meta_key'];
                		}
                		/*
                		if ( isset($post_meta[$test_key]) ) {
                		
                		}
                		*/
                	
                	}
                
                }
                
                
                
                $info .= format_search_results($arr_post_ids);

            } else {
                
                $info .= "No matching items found.<br />";
                
            } // END if ( !empty($arr_post_ids) )
            
            
            /*if ( isset($posts_info['arr_posts']) ) {
                
                $arr_posts = $posts_info['arr_posts'];//$posts_info['arr_posts']->posts; // Retrieves an array of WP_Post Objects
                
                $ts_info .= $posts_info['ts_info']."<hr />";
                
                if ( !empty($arr_posts) ) {
                    
                    $ts_info .= "Num matching posts found (raw results): [".count($arr_posts->posts)."]"; 
                    //$info .= '<div class="troubleshooting">'."Num matching posts found (raw results): [".count($arr_posts->posts)."]".'</div>'; // tft -- if there are both rep and editions, it will likely be an overcount
               
                    if ( count($arr_posts->posts) == 0 ) { // || $form_type == "advanced_search"
                        //$ts_info .= "bgp_args: <pre>".print_r($bgp_args,true)."</pre>"; // tft
                    }
                    
                    // Print last SQL query string
                    global $wpdb;
                    $ts_info .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>"; // tft

                    $info .= format_search_results($arr_posts);
                    
                } // END if ( !empty($arr_posts) )
                
            } else {
                $ts_info .= "No arr_posts retrieved.<br />";
            }*/
            
        } else {
            
            $ts_info .= "No search values submitted.<br />";
            
        }
        
        
    } // END if ( $fields )

    $info .= '<div class="troubleshooting">';
    $info .= $ts_info;
    $info .= '</div>';
    
    return $info;
    
}

?>