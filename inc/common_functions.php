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
		if ( $do_ts ) { return $ts_info; }
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
	
	if ( $do_ts ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
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
		'return'  	=> 'html',
		'do_ts'  	=> false,
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	$ts_info .= "sdg_post_thumbnail parsed/extracted args: <pre>".print_r($args, true)."</pre>";
	
    if ( $post_id === null ) { $post_id = get_the_ID(); }
    $post_type = get_post_type( $post_id );
    $img_id = null;
    if ( $return == "html" ) {
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
    $ts_info .= "return: $return<br />";
    //
    
    // Make sure this is a proper context for display of the featured image
    if ( post_password_required($post_id) || is_attachment($post_id) ) {
        return;
    } else if ( has_term( 'video-webcasts', 'event-categories' ) && is_singular('event') ) {        
        // featured images for events are handled via Events > Settings > Formatting AND via events.php (#_EVENTIMAGE)
        //return;
    } else if ( has_term( 'video-webcasts', 'category' ) ) {        
        $player_status = get_media_player( $post_id, 'above', 'video', true ); // get_media_player ( $post_id = null, $position = 'above', $media_type = 'video', $status_only = false, $url = null )
        if ( $player_status == "ready" ) {
            return;
        }
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
    
    if ( $return == "html" && !empty($img_id ) ) {
    
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
			
				if ( $return == "html" ) {
					if ( is_singular('person') ) {
						$img_size = "medium"; // portrait
						$classes .= " float-left";
					}
			
					$classes .= " is_singular";
			
					$img_html .= '<div class="'.$classes.'">';
					$img_html .= get_the_post_thumbnail( $post_id, $img_size );
					$img_html .= $caption_html;
					$img_html .= '</div><!-- .post-thumbnail -->';
				}

			} else {
		
				// If an image_gallery was found, show one image as the featured image
				// TODO: streamline this
				if ( $img_id && is_array($image_gallery) && count($image_gallery) > 0 && $return == "html" ) {
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
		
			if ( !empty($img_tag) && $return == "html" ) {
				$classes .= " float-left"; //$classes .= " NOT_is_singular";
				$img_html .= '<a class="'.$classes.'" href="'.get_the_permalink( $post_id ).'" aria-hidden="true">';
				$img_html .= $img_tag;
				$img_html .= '</a>';
			}
		
		} // END if is_singular()
	} // END if ( $return == "html" && !empty($img_id )
	    
    //$info .= '<div class="troubleshooting">'.$ts_info.'</div>';
    
    if ( $return == "html" ) {
    	$info .= $img_html;
    } else { // $return == "id"
    	$info = $img_id;
    	//$info .= '<div class="troubleshooting">'.$ts_info.'</div>';
    }
	
	if ( $echo == true ) {
		if ( $do_ts ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
		echo $info;    
	} else {
		//if ( $do_ts ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
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
function get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' ) {

	$info = null; // init
	
	// If we don't have actual values for all parameters, there's not enough info to proceed
	if ($post_id === null || $related_field_name === null || $related_post_type === null) { return null; }
	
	$related_id = null; // init
    if ( $return == 'single' ) {
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
        
        if ( $return == 'single' ) {
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
		'return'  	=> 'html',
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
    
    if ( $do_ts ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
    
    return $info;
	
}

/*********** MEDIA ***********/

// Get Media Player -- Based on contents of ACF A/V Info fields
function get_media_player ( $post_id = null, $position = 'above', $media_type = 'unknown', $status_only = false, $url = null ) {
	
    // Init vars
    $arr_info = array(); // return info and status, or status only, depending on options selected
	$info = "";
    $player = "";
    $player_status = "unknown";
    
    if ( $post_id == null ) { $post_id = get_the_ID(); } 
    $info .= "<!-- post_id: '".$post_id."'; position: '".$position."'; media_type: '".$media_type."'; status_only: '[".$status_only."]' -->";
    // If it's not a webcast-eligible post, then abort
    //if ( !post_is_webcast_eligible( $post_id ) ) { return false;  }
    
    // Get the basic media info
    $featured_AV = get_field('featured_AV', $post_id); // array of options (checkboxes field) including: featured_video, featured_audio, webcast (WIP)
    $media_format = get_field('media_format', $post_id); // array of options (checkboxes) including: youtube, vimeo, video, audio -- // formerly: $webcast_format = get_field('webcast_format', $post_id);
    $info .= "<!-- featured_AV: ".print_r($featured_AV, true)." -->";
	$info .= "<!-- media_format: ".print_r($media_format, true)." -->";
    
    $video_player_position = get_field('video_player_position', $post_id); // above/below/banner
    $audio_player_position = get_field('audio_player_position', $post_id); // above/below/banner
    $info .= "<!-- video_player_position: '".$video_player_position."' -->";
    $info .= "<!-- audio_player_position: '".$audio_player_position."' -->";
    
    if ( $media_type == "unknown" ) {
    	if ( $video_player_position == $position ) {
    		$media_type = 'video';
    	} else if ( $audio_player_position == $position ) {
    		$media_type = 'audio';
    	}
    	$info .= "<!-- media_type REVISED: '".$media_type."' -->";
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
		
		$info .= "<!-- video_id: '".$video_id."'; video_file src: '".$src." -->";
		
		if ( $src && in_array( 'video', $media_format) ) {
			$media_format = "video";
		} else if ( $video_id && in_array( 'vimeo', $media_format) ) {
			$media_format = "vimeo";
		} else {
			$media_format = "youtube";
		}
		
    } else if ( $media_type == "audio" ) {
    	
    	$audio_file = get_field('audio_file', $post_id);
    	$info .= "<!-- audio_file: '".$audio_file." -->";
    	if ( $audio_file ) { $media_format = "audio"; } else { $media_format = "unknown"; }
    	if ( is_array($audio_file) ) { $src = $audio_file['url']; } else { $src = $audio_file; }
    	
    } else {
    
		$media_format = "unknown";
		
	}

	$info .= "<!-- media_format REVISED: '".$media_format."' -->";
	if ( $media_format != 'unknown' ) {
		$player_status = "ready";
	}
    
    // Webcast?
    if ( is_array($featured_AV) && in_array( 'webcast', $featured_AV) ) {
    	$webcast_status = get_webcast_status( $post_id );
    	$url = get_webcast_url( $post_id ); //if ( empty($video_id)) { $src = get_webcast_url( $post_id ); }
    	$info .= "<!-- webcast_status: '".$webcast_status."'; webcast_url: '".$url."' -->";
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
        $info .= "<!-- audio_file ext: ".$ext." -->"; // tft
        $atts = array('src' => $src, 'preload' => 'auto' ); // Playback position defaults to 00:00 via preload -- allows for clearer nav to other time points before play button has been pressed
        $player .= "<!-- audio_player atts: ".print_r($atts,true)." -->";
        
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
            
        }
        
		
	} else if ( $media_format == "video" ) {
		
		// Video Player for vid file from Media Library
		$video .= '<div class="hero vidfile video-container">';
		$video .= '<video poster="" id="section-home-hero-video" class="hero-video" src="'.$video_file['url'].'" autoplay="autoplay" loop="loop" preload="auto" muted="true" playsinline="playsinline"></video>';
		$video .= '</div>';
		
	} else if ( $media_format == "vimeo" ) {
            
		// Video Player: Vimeo iframe embed code
		
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
		
		if ( $status_only == false ) {
		
			// Get SRC
			if ( !empty($yt_series_id) && !empty($yt_list_id) ) { // && $media_format == "youtube_list"    
				$src = 'https://www.youtube.com/embed/videoseries?si='.$yt_series_id.'?&list='.$yt_list_id.'&autoplay=0&loop=1&mute=0&controls=1';
				//https://www.youtube.com/embed/videoseries?si=gYNXkhOf6D2fbK_y&amp;list=PLXqJV8BgiyOQBPR5CWMs0KNCi3UyUl0BH	
			} else {
				$src = 'https://www.youtube.com/embed/'.$video_id.'?&playlist='.$video_id.'&autoplay=0&loop=1&mute=0&controls=1';
				//$src = 'https://www.youtube.com/embed/'.$youtube_id.'?&playlist='.$youtube_id.'&autoplay=1&loop=1&mute=1&controls=0'; // old theme header version -- note controls
				//$src = 'https://www.youtube.com/watch?v='.$video_id;
			}
			// Timestamp?
			if ( $yt_ts ) { $src .= "&start=".$yt_ts; }
			
			// Assemble media player iframe
			$player .= '<div class="hero video-container youtube-responsive-container">';
			$player .= '<iframe width="100%" height="100%" src="'.$src.'" title="YouTube video player" frameborder="0" allowfullscreen></iframe>'; // controls=0 // allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
			$player .= '</div>';
		}
		
	}
    
    // If there's media to display, show the player
    if ( $player != "" && $status_only == false ) { // !empty($vimeo_id) || !empty($audio_file) || !empty($url)
        
         // CTA
		// TODO: get this content from some post type manageable via the front end, by slug or id (e.g. 'cta-for-webcasts'
		// post type for CTAs could be e.g. "Notifications", or post in "CTAs" post category, or... 
		// -- or by special category of content associated w/ CPTs?
        $status_message = get_status_message ( $post_id, 'webcast_status' );
        $show_cta = get_post_meta( $post_id, 'show_cta', true );
        if ( $show_cta == "0" ) { 
            $show_cta = false;
            $info .= '<!-- show_cta: FALSE -->';
        } else { 
            $show_cta = true;
            $info .= '<!-- show_cta: TRUE -->';
        }
        $cta = "";
        if ( $show_cta ) {
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
        }
        
        //
        if ($status_message !== "") {
            $info .= '<p class="message-info">'.$status_message.'</p>';
            if ( !is_dev_site() // Don't show CTA on dev site. It's annoying clutter.
                    && $show_cta !== false
                    && get_post_type($post_id) != 'sermon' ) { // Also don't show CTA for sermons
                $info .= $cta;
            }
            //return $info; // tmp disabled because it made "before" Vimeo vids not show up
        }
        
        if ( is_singular('sermon') && has_term( 'webcasts', 'admin_tag', $post_id ) && get_post_meta( $post_id, 'audio_file', true ) != "" ) { 
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
		
	} else {
		
        // No $src or $video_id or $audio_file? Don't show anything.
        $player_status = "unavailable";
	
	}
	
    if ( $status_only == false ) {
        
        $arr_info['player'] = $info;
        $arr_info['position'] = $info;
        $arr_info['status'] = $player_status;
    
        return $arr_info;
        
    } else {
        return $player_status;
    }
	
    return null; // if all else fails...
	
}

// Display shortcode for media_player -- for use via EM settings
add_shortcode('display_media_player', 'display_media_player');
//function display_media_player( $post_id = null, $position = 'above' ) {
function display_media_player( $atts = array() ) {

	// Normalize attribute keys by making them all lowercase
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );
	
	// Override default attributes with user attributes
	$args = shortcode_atts(
		array(
			'post_id' => get_the_ID(),
			'position' => 'above',
		), $atts
	);
	
	// Extract args as vars
	extract( $args );
    
    $info = ""; // init
    
    // TODO: handle webcast status considerations
    if ( post_is_webcast_eligible( $post_id ) ) {
        //        
    } else {
        //            
    }
    
    $media_info = get_media_player( $post_id, $position ); // parameters: ( $post_id = null, $position = 'above', $media_type = 'unknown', $status_only = false, $url = null ) {
	$player_status = $media_info['status'];
	
	$info .= "<!-- Audio/Video for post_id: $post_id -->";
	$info .= $media_info['player'];
	$info .= "<!-- player_status: $player_status -->";
	$info .= '<!-- /Audio/Video -->';
    
    return $info;
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

?>