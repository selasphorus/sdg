<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** POST BASICS ***********/

// TODO: replace w/ sdg_post_title via SDG plugin? or via display_content plugin?
function sdg_post_title ( $args = array() ) {

	$info = "";
	$ts_info = "";
	
	//$ts_info .= "<pre>args: ".print_r($args, true)."</pre>";
	
	// Defaults
	$defaults = array(
		'post'			=> null,
		'line_breaks'	=> false,
		'show_subtitle'	=> false,
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

	// Parse args
	$args = wp_parse_args( $args, $defaults );

	// Extract
	extract( $args );
	
	if ( is_int($post) ) { 
		$post_id = $post;
		$post = get_post( $post_id );
	} else {
		$post_id = isset( $post->ID ) ? $post->ID : 0;
	}
	//$ts_info .= "post_id: ".$post_id."<br />";
	//$ts_info .= "<pre>post: ".print_r($post, true)."</pre>";
	$title = $post->post_title;
	
	if ( strlen( $title ) == 0 || $post_id == 0) {
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
	if ( $link ) {
		$title = '<a href="'.esc_url( get_permalink($post_id) ).'" rel="bookmark">'.$title.'</a>';
	}
	
	if ( $hlevel ) {
		$title = '<h'.$hlevel.' class="'.$hclass.'">'.$title.'</h'.$hlevel.'>';
	}
	
	$info .= $title;
	$info .= $subtitle;
	$info .= $series_subtitle;
	
	if ( $echo ) {
		echo $info;
	} else {
		return $info;
	}
	
}


// Custom fcn for thumbnail/featured image display
function sdg_post_thumbnail ( $args ) {
    
    $info = "";
	$ts_info = "";
	
	$ts_info .= "<pre>sdg_post_thumbnail args: ".print_r($args, true)."</pre>";
	
	// Defaults
	$defaults = array(
		'post_id'	=> null,
		'format'	=> "singular", // default to singular; other option is excerpt
		'img_size'	=> "thumbnail",
		'sources'	=> array("featured_image", "gallery"),
		'echo'		=> true,
		'return'  	=> 'html',
	);

	// Parse args
	$args = wp_parse_args( $args, $defaults );

	// Extract
	extract( $args );
	
    if ( $post_id === null ) { $post_id = get_the_ID(); }
    $img_id = null;
    $img_html = "";
    $image_gallery = array();
    if ( $sources == "all" ) {
    	$sources = array("featured", "gallery", "custom_thumb", "content");
    }
    
    if ( $context == "singular" && !is_page('events') ) {
        $img_size = "full";
    }
    
    /*
    // Defaults
	$defaults = array(
		'post_id'         => null,
		'preview_length'  => 55,
		'readmore'        => false,
	);
	
    // Parse args
	$args = wp_parse_args( $args, $defaults );

	// Extract
	extract( $args );
	*/
    
    $ts_info = "";
    $ts_info .= "post_id: $post_id<br />";
    $ts_info .= "format: $format<br />";
    $ts_info .= "get_the_ID(): ".get_the_ID()."<br />";
    $ts_info .= "img_size: ".print_r($img_size, true)."<br />";
    $ts_info .= "sources: ".print_r($sources, true)."<br />";
    $ts_info .= "return: $return<br />";
    
    // Are we using the custom image, if any is set?
    if ( in_array("custom_thumb", $sources ) ) {
    	$ts_info .= "Check for custom_thumb<br />";
        // First, check to see if the post has a Custom Thumbnail
        $custom_thumb_id = get_post_meta( $post_id, 'custom_thumb', true );
        if ( $custom_thumb_id ) {
        	$ts_info .= "custom_thumb_id found: $custom_thumb_id<br />";
            $img_id = $custom_thumb_id;
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
					$ts_info .= "Random thumbnail ID: $img_id<br />";
				} else {
					$ts_info .= "No image_gallery found.<br />";
				}
            }
            
            // Image(s) in post content?
            if ( in_array("content", $sources ) ) {
				if ( empty($img_id) && function_exists('get_first_image_from_post_content') ) { 
					$image_info = get_first_image_from_post_content( $post_id );
					if ( $image_info ) {
						$img_id = $image_info['id'];
					} else {
						//$img_id = "test"; // tft
					}
				}
			}

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
    
    // Make sure this is a proper context for display of the featured image
    if ( post_password_required($post_id) || is_attachment($post_id) ) {
        return;
    } else if ( has_term( 'video-webcasts', 'event-categories' ) && is_singular('event') ) {        
        // featured images for events are handled via Events > Settings > Formatting AND via events.php (#_EVENTIMAGE)
        return;
    } else if ( has_term( 'video-webcasts', 'category' ) ) {        
        $player_status = get_media_player( $post_id, true ); // get_media_player ( $post_id = null, $status_only = false, $url = null )
        if ( $player_status == "ready" ) {
            return;
        }        
    } else if ( is_page_template('page-centered.php') && $post_id == get_the_ID() ) {        
		return;
	} else if ( is_singular() && $post_id == get_the_ID() && in_array( get_field('featured_image_display'), array( "background", "thumbnail", "banner" ) ) ) {        
        return; // wip
    }

    $ts_info .= "Ok to display the image, if one is found.<br />";
    
    // Ok to display the image! Set up classes for styling
    $classes = "post-thumbnail sdg";
    
    // Retrieve the caption (if any) and return it for display
    if ( get_post( $img_id  ) ) {
		$caption = get_post( $img_id  )->post_excerpt;
		if ( !empty($caption) && !is_singular('person') ) {
			$classes .= " has-caption";
		} else {
			$classes .= " no-caption";
		}
    }
    
    if ( $context == "singular" && !( is_page('events') ) ) {
        
        $ts_info .= "is_singular<br />";
        
        if ( has_post_thumbnail($post_id) ) {
            
            if ( $return == "html" ) {
            	if ( is_singular('person') ) {
					$img_size = "medium"; // portrait
					$classes .= " float-left";
				}
			
				$classes .= " is_singular";
			
				$img_html .= '<div class="'.$classes.'">';
				$img_html .= get_the_post_thumbnail( $post_id, $img_size );
				$img_html .= '</div><!-- .post-thumbnail -->';
            } else {
            	$img_id = get_post_thumbnail_id( $post_id );
            }

        } else {
        
        	// If an image_gallery was found, show one image as the featured image
        	// TODO: streamline this
        	if ( $img_id && is_array($image_gallery) && count($image_gallery) > 0 ) {
        		$img_html = wp_get_attachment_image( $img_id, $img_size, false, array( "class" => "featured_attachment" ) );
        	}
        	
        }
        
    } else if ( !( $context == "singular" && is_page('events') ) ) { 
        
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
        	$classes .= " float-left"; //$classes .= " NOT_is_singular"; // tft
        	$img_html .= '<a class="'.$classes.'" href="'.get_the_permalink().'" aria-hidden="true">';
        	$img_html .= $img_tag;
        	$img_html .= '</a>';
        }        
        
    } // End if is_singular()
    
    //$info .= '<div class="troubleshooting">'.$ts_info.'</div>'; // tft
    
    if ( $return == "html" ) {
    	$info .= $img_html;
    } else { // $return == "id"
    	$info = $img_id;
    	//$info .= '<div class="troubleshooting">'.$ts_info.'</div>';
    }
	
	if ( $echo == true ) {
		$info .= '<div class="troubleshooting">'.$ts_info.'</div>'; // tft
		echo $info;    
	} else {
		//if ( is_dev_site() ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; } // tft
		return $info;
	}

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
    $args = array(
        'post_type'   => $related_post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key'     => $related_field_name,
                'value'   => $post_id
            )
        )
    );
    // Run query
    $related_posts = new WP_Query( $args );

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
            $info = $related_posts->data();
        }
        
    }
	
	return $info;
	
}

// Function to identify possible duplicates
// WIP -- not functional!
function get_possible_duplicate_posts( $post_id = null, $return = 'all' ) {

	$info = null; // init
	
	// If we don't have actual values for all parameters, there's not enough info to proceed
	if ($post_id === null ) { return null; }
	
	$post_type = get_post_type( $post_id );
	
	// Set post_status options based on user role
	if ( current_user_can('read_repertoire') ) {
		$post_status = array( 'publish', 'private', 'draft' );
	} else {
		$post_status = 'publish';
	}	
	
	$post_title = get_the_title( $post_id );
	
	// TODO/WIP: remove "a", "the", "an" and so on from post_title
	// Extract key words as array to use as search terms?
	// 
	
	if ( $post_type == "repertoire" ) {
		$musical_works = get_field( 'musical_work', $post_id );
        //$info .= '<pre>'.print_r($musical_works, true).'</pre>';
        /*foreach ( $musical_works as $musical_work ) {
            $info .= "<h3>".$musical_work->post_title."</h3>";
        }*/
		//$composers = get_field('composer', $post_id, false);
		/*if ( get_field( 'composer', $post_id )  ) {
			$composers_str = the_field( 'composer', $post_id );
		}*/
		/*foreach ( $composers as $composer ) {
			$composers_str .= get_the_title($composer);
		}*/
		// OR get_authorship_info -- with ids returned as array to compare w/ other works
	}
	
	// Set args
    $args = array(
        'post_type'   => $post_type,
        'post_status' => $post_status,
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key'     => $related_field_name,
                'value'   => $post_id
            )
        )
    );
    
    // Run query
    $related_posts = new WP_Query( $args );

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
            $info = $related_posts->data();
        }
        
    }
	
	return $info;
	
}

// WIP
function merge_field_values ( $p1_val = null, $p2_val = null ) {

	// init
	$arr_info = array();
	$merge_value = null;
	$info = "";
	
	// Compare values/merge arrays
	
	// What type of values have we got?
	if ( is_object($p1_val) || is_object($p2_val) ) {
		// If one or more value is of type 'object', then...???
		$merge_value = "OBJECT(S)!"; // tft
	} else if ( is_array($p1_val) && is_array($p2_val) ) {
		// If both values are arrays, then merge them
		//$merge_value = array_merge($p1_val, $p2_val);
		$merge_value = array_unique(array_merge($p1_val, $p2_val));
		//$info .= "Merged arrays!";
	} else if ( !empty($p1_val) ) {
		// If p1_val is not empty, then compare it to p2_val
		if ( !empty($p2_val) ) {
			//compare... WIP
			if ( $p1_val == $p2_val ) {
				$merge_value = $p1_val; // They're identical
				//$info .= "==";
			} else {
				$merge_value = $p1_val;
				//$info .= "+";
				// TODO: save p2_val as backup?
			}
		} else {
			$merge_value = $p1_val;
		}				
	} else if ( !empty($p2_val) ) {
		$merge_value = $p2_val;
	}
	
	$arr_info['info'] = $info;
	$arr_info['merge_value'] = $merge_value;
	
	return $arr_info;
		
}

// Function to merge duplicate records
add_shortcode('sdg_merge_form', 'sdg_merge_form');
//function sdg_merge_form ( $post_ids = array() ) {
function sdg_merge_form ($atts = [], $content = null, $tag = '') {
	
	// init vars
	$info = "";
    $ts_info = "";
    $form_action = null;
    
    // Retrieve any data submitted via forms or query vars
    if ( !empty($_GET) ) { $ts_info .= '<pre>_GET: '.print_r($_GET,true).'</pre>'; }
    if ( !empty($_POST) ) {
    	$ts_info .= '<pre>_POST: '.print_r($_POST,true).'</pre>';    	
    	if ( isset($_POST['form_action']) ) {
    		$form_action = $_POST['form_action'];
    	}
    }
    //$ts_info .= '_REQUEST: <pre>'.print_r($_REQUEST,true).'</pre>'; // tft
    
    // init
    $arr_posts = array(); // tft
    $form_type = 'simple_merge';
    
    if ( isset($_POST['p1_id']) && isset($_POST['p2_id']) && $form_action == "Merge Records" ) { // $form_action == "merge_records"
    
    	$merging = true;
    	$merge_errors = false;
    	$fields_merged = 0;
    	
    	if ( !empty($_POST['p1_id']) ) {
    		$p1_id = $_POST['p1_id'];
    		$arr_posts[] = $p1_id;
    		$post_type = get_post_type($p1_id);
    	} else {
    		$post_type = "UNKNOWN";
    	}
    	
    	if ( !empty($_POST['p2_id']) ) {
    		$p2_id = $_POST['p2_id'];
    		$arr_posts[] = $p2_id;  		
    	}
    	
    } else {
    
    	$merging = false;
    	$identical_posts = true; // until proven otherwise
    	
    	// Get posts based on submitted IDs
    	$a = shortcode_atts( array(
			'post_type'	=> 'post',
			'ids'     	=> array(),
			'form_type'	=> 'simple_merge',
			'limit'    	=> '-1'
		), $atts );
	
		$post_type = $a['post_type'];
		$form_type = $a['form_type'];
		$limit = $a['limit'];
	
		// Set post_status options based on user role
		if ( current_user_can('read_repertoire') ) {
			$post_status = array( 'publish', 'private', 'draft' );
		} else {
			$post_status = 'publish';
		}
	
		// Set up basic query args for retrieval of posts to merge
		$args = array(
			'post_type'       => array( $post_type ), // Single item array, for now. May add other related_post_types -- e.g. repertoire; edition
			'post_status'     => $post_status,
			//'posts_per_page'  => $limit, //-1, //$posts_per_page,
			'orderby'         => 'title',
			'order'           => 'ASC',
			'return_fields'   => 'ids',
		);
	
		// Turn the list of IDs into a proper array
		if ( !empty( $a['ids'] ) ) {
			$str_ids = $a['ids'];
			$post_ids = array_map( 'intval', sdg_att_explode( $a['ids'] ) );
		} else if ( isset($_GET['ids']) ) {
			$post_ids = $_GET['ids'];
			$str_ids = implode(", ", $_GET['ids']);
		} else if ( isset($_POST['ids']) ) {
			$post_ids = $_POST['ids'];
			$str_ids = implode(", ", $_POST['ids']);
		} else if ( isset($_POST['p1_id']) && isset($_POST['p2_id']) ) {
			$p1_id = $_POST['p1_id'];
			$p2_id = $_POST['p2_id'];
			$post_ids = array($p1_id,$p2_id);
			$str_ids = $p1_id.", ".$p2_id;
			//$str_ids = implode(", ", $_POST['ids']);
		} else {
			$post_ids = array();
			$str_ids = "";
		}
	
		$args['ids'] = $str_ids; // pass string as arg to be processed by birdhive_get_posts
	
		//if ( count($post_ids) < 1 ) { $ts_info .= "Not enough post_ids submitted.<br />"; }
	
		//$info .= "form_type: $form_type<br />"; // tft

		// If post_ids have been submitted, then run the query
		if ( count($post_ids) > 1 ) {
			
			//$ts_info .= "About to pass args to birdhive_get_posts: <pre>".print_r($args,true)."</pre>"; // tft
	
			// Get posts matching the assembled args
			// =====================================
			$posts_info = birdhive_get_posts( $args );
	
			if ( isset($posts_info['arr_posts']) ) {
		
				$arr_posts = $posts_info['arr_posts']->posts;
				//$ts_info .= "<p>Num arr_posts: [".count($arr_posts)."]</p>";
				//$ts_info .= "arr_posts: <pre>".print_r($arr_posts,true)."</pre>"; // tft
			
				if ( count($arr_posts) < 2 ) {
					$info .= '<p class="nb">Please submit IDs for two published posts.</p>';
					$ts_info .= "arr_posts: <pre>".print_r($arr_posts,true)."</pre>"; // tft
				} else if ( count($arr_posts) > 2 ) {
					$info .= '<p class="nb">That\'s too many posts! I can only handle two at a time.</p>';
					$ts_info .= "arr_posts: <pre>".print_r($arr_posts,true)."</pre>"; // tft
				}
		
				//$info .= '<div class="troubleshooting">'.$posts_info['info'].'</div>';
				$ts_info .= $posts_info['info']."<hr />"; // "posts_info: ".
				//$info .= $posts_info['info']."<hr />"; //$info .= "birdhive_get_posts/posts_info: ".$posts_info['info']."<hr />";
		
				// Print last SQL query string
				//global $wpdb;
				//$info .= '<div class="troubleshooting">'."last_query:<pre>".$wpdb->last_query."</pre>".'</div>'; // tft
				//$ts_info .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>"; // tft
		
			}
	
		}/* else {
			$arr_posts = array(); // empty array to avoid counting errors later, in case no posts were retrieved
		}*/
    }
    
    // =====================================
        
    // Get array of fields which apply to the given post_type -- basic fields as opposed to ACF fields
    $arr_core_fields = array( 'post_title', 'content', 'excerpt', 'post_thumbnail' ); // Also?: author, post_status, date published, date last modified -- read only?
    
    // Get all ACF field groups associated with the post_type
    $field_groups = acf_get_field_groups( array( 'post_type' => $post_type ) );
    
    // Get all taxonomies associated with the post_type
    $taxonomies = get_object_taxonomies( $post_type );
    //$ts_info .= "taxonomies for post_type '$post_type': <pre>".print_r($taxonomies,true)."</pre>";
    
    // WIP/TODO: Make one big array of field_name & p1/p2 values from core_fields, field_groups, and taxonomies, and process that into rows...
    
    //?ids%5B%5D=292003&ids%5B%5D=298829
    //292003 -- 298829
    
    // TODO: give user choice of which post to treat as primary?
	if ( isset($arr_posts[0]) ) { $p1_id = $arr_posts[0]; } else { $p1_id = null; }
	if ( isset($arr_posts[1]) ) { $p2_id = $arr_posts[1]; } else { $p2_id = null; }	
	
	// Set up the form for submitting IDs to compare
	// To do: set action to SELF without query vars(?)
	$info .= '<form id="select_ids" method="post" class="sdg_merge_form">';
	$info .= '<div class="input-group">';
	$info .= '<input type="text" id="p1_id" name="p1_id" value="'.$p1_id.'" class="merge-input" />';
	$info .= '<br /><label for="p1_id">Primary ID</label>';
	$info .= '</div>';
	$info .= '<div class="input-group">';
	if ( is_dev_site() ) { $info .= '<a href="#" id="swap-ids" class="action symbol">&#8644;</a>'; } else { $info .= '<span class="symbol">&lArr;</span>'; }
	$info .= '</div>';
	$info .= '<div class="input-group">';
	$info .= '<input type="text" id="p2_id" name="p2_id" value="'.$p2_id.'" class="merge-input" />';
	$info .= '<br /><label for="p2_id">Secondary ID</label>';
	$info .= '</div>';
	$info .= '<input type="hidden" name="form_action" value="review">';
	$info .= '&nbsp;&nbsp;&nbsp;';
	$info .= '<input type="submit" value="Compare Posts">';
	$info .= '</form>';
	$info .= '<br clear="all" />';
	//$info .= '<hr />';
	
    if ( count($arr_posts) == 2 ) {
		
		//$ts_info .= "Two posts... moving forward...<br />";
		
		$arr_fields = array(); // $arr_fields['field_name'] = array(field_cat, field_type, values...) -- field categories are: core_field, acf_field, or taxonomy;
    
    	// Set up the merge form
    	// To do: set action to SELF without query vars(?)
    	$info .= '<form id="merge_posts" method="post" class="sdg_merge_form '.$form_type.'">'; // action? method?
    
    	if ( $merging ) {
    	
    		// Form has been submitted... About to merge...
    		$p1 = get_post($p1_id);
    		$p2 = get_post($p2_id);
    		
    		$info .= "<h3>Merge request submitted...</h3>";
    		
    		// WIP: first update_repertoire_events for both posts(?)
    		$info .= '<div class="info">'.update_repertoire_events( $p1_id ).'</div>';
    		$info .= '<div class="info">'.update_repertoire_events( $p2_id ).'</div>';
    		
    		$info .= "<h3>About to merge values from post $p2_id into post $p1_id...</h3>";
    		
    	} else {
    	
    		// If not ready to run merge, assemble info about posts-to-merge
    		$p1 = get_post($p1_id);
    		$p2 = get_post($p2_id);
    		
    		// Get and compare last modified dates
			$p1_modified = $p1->post_modified;
			$p2_modified = $p2->post_modified;
		
			// Prioritize the post which was most recently modified by putting it in first position
			// In other words, swap p1/p2 if second post is newer
			/*if ( $p1_modified < $p2_modified ) {
				$p1_id = $arr_posts[1];
				$p2_id = $arr_posts[0];
				$p1 = get_post($p1_id);
    			$p2 = get_post($p2_id);
			}*/
			
			// Assemble general post info for table header
			$p1_info = "[".$p1_id."] ".$p1->post_modified." (".get_the_author_meta('user_nicename',$p1->post_author).")";
			$p2_info = "[".$p2_id."] ".$p2->post_modified." (".get_the_author_meta('user_nicename',$p2->post_author).")";
			//$info .= 'p1: <pre>'.print_r($p1,true).'</pre>'; // tft
			//$info .= 'p2: <pre>'.print_r($p2,true).'</pre>'; // tft
			//
			$info .= "<pre>";
			$info .= "Post #1 [ID: ".$p1_id. "] >> Last modified: ".$p1->post_modified."; author: ".get_the_author_meta('user_nicename',$p1->post_author);
			$info .= '&nbsp;<a href="'.get_permalink($p1_id).'" target="_blank">View</a>&nbsp;|&nbsp;<a href="'.get_edit_post_link($p1_id).'" target="_blank">Edit</a><br />';
			$info .= "Post #2 [ID: ".$p2_id. "] >> Last modified: ".$p2->post_modified."; author: ".get_the_author_meta('user_nicename',$p2->post_author);
			$info .= '&nbsp;<a href="'.get_permalink($p2_id).'" target="_blank">View</a>&nbsp;|&nbsp;<a href="'.get_edit_post_link($p2_id).'" target="_blank">Edit</a><br />';
			$info .= "</pre>";
			//
			$info .= '<input type="hidden" name="p1_id" value="'.$p1_id.'">';
			$info .= '<input type="hidden" name="p2_id" value="'.$p2_id.'">';
			
    	}
		
		// TODO: tag which fields are ok to edit manually, to avoid trouble -- e.g. editions; choirplanner_id, &c. should be RO
		// TODO: include editing instructions -- e.g. separate category list with semicolons (not commas!)
		
		// Get core values for both posts
		//array( 'post_title', 'content', 'excerpt', 'post_thumbnail' );
		foreach ( $arr_core_fields as $field_name ) {
			
			// Prep field and post comparison info
				
			$field_type = "text";
			$field_label = ""; // tft
			
			if ( $field_name == "post_thumbnail" ) {
				$p1_val = get_post_thumbnail_id($p1_id);
				$p2_val = get_post_thumbnail_id($p2_id);
			} else {
				$p1_val = $p1->$field_name;
				$p2_val = $p2->$field_name;
			}	
		
			$merged = merge_field_values($p1_val, $p2_val);
			$merge_value = $merged['merge_value'];
			$merge_info = $merged['info'];
		
			$arr_fields[$field_name] = array('field_cat' => "core_field", 'field_type' => $field_type, 'field_label' => $field_label, 'p1_val' => $p1_val, 'p2_val' => $p2_val, 'merge_val' => $merge_value, 'merge_info' => $merge_info);
			//$arr_fields[$field_name] = array("core_field", $p1_val, $p2_val, $merge_value, $merge_info);
			
		}
		
		// Get meta values for both posts
		foreach ( $field_groups as $group ) {

			$group_key = $group['key'];
			//$info .= "group: <pre>".print_r($group,true)."</pre>"; // tft
			$group_title = $group['title'];
			$group_fields = acf_get_fields($group_key); // Get all fields associated with the group
			//$field_info .= "<hr /><strong>".$group_title."/".$group_key."] ".count($group_fields)." group_fields</strong><br />"; // tft

			$i = 0;
			foreach ( $group_fields as $group_field ) {
			
				// Prep field and post comparison info
							
				// field_object parameters include: key, label, name, type, id -- also potentially: 'post_type' for relationship fields, 'sub_fields' for repeater fields, 'choices' for select fields, and so on
				$field_name = $group_field['name'];
				$field_label = $group_field['label'];
				$field_type = $group_field['type'];
				
				$p1_val = get_field($field_name, $p1_id, false);
				$p2_val = get_field($field_name, $p2_id, false);
					
				// If a value was retrieved for either post, then display more info about the field object (tft)
				if ( $p1_val || $p1_val ) {				
					if ( $field_name == "choir_voicing" ) {
					//$info .= "Field object ($field_name): <pre>".print_r($group_field,true)."</pre><br />";
					}					
				}
			
				$merged = merge_field_values($p1_val, $p2_val);
				$merge_val = $merged['merge_value'];
				$merge_info = $merged['info'];
		
				$arr_fields[$field_name] = array('field_cat' => "acf_field", 'field_type' => $field_type, 'field_label' => $field_label, 'p1_val' => $p1_val, 'p2_val' => $p2_val, 'merge_val' => $merge_val, 'merge_info' => $merge_info );
				/*
				$field_info .= "[$i] group_field: <pre>".print_r($group_field,true)."</pre>"; // tft
				if ( $group_field['type'] == "relationship" ) { $field_info .= "post_type: ".print_r($group_field['post_type'],true)."<br />"; }
				if ( $group_field['type'] == "select" ) { $field_info .= "choices: ".print_r($group_field['choices'],true)."<br />"; }
				*/
				
				$i++;

			}

		} // END foreach ( $field_groups as $group )
		
		// Get terms applied to both posts
		foreach ( $taxonomies as $taxonomy ) {
			
			// Prep field and post comparison info
			
			// Get terms... WIP
			$p1_val = wp_get_post_terms( $p1_id, $taxonomy, array( 'fields' => 'ids' ) ); // 'all'; 'names'
			$p2_val = wp_get_post_terms( $p2_id, $taxonomy, array( 'fields' => 'ids' ) );
			
			//if ( !empty($p1_val) ) { $info .= "taxonomy [$field_name] p1_val: <pre>".print_r($p1_val, true)."</pre>"; }
			//if ( !empty($p2_val) ) { $info .= "taxonomy [$field_name] p2_val: <pre>".print_r($p2_val, true)."</pre>"; }
			
			$field_type = "taxonomy";
			$field_name = $taxonomy;
			$field_label = "";
		
			// WIP/TODO: figure out best way to display taxonomy names while storing ids for actual merge operation
			$merged = merge_field_values($p1_val, $p2_val);
			$merge_value = $merged['merge_value'];
			$merge_info = $merged['info'];
			//$merge_value = "tmp"; $merge_info = "tmp";
	
			$arr_fields[$field_name] = array('field_cat' => "taxonomy", 'field_type' => $field_type, 'field_label' => $field_label, 'p1_val' => $p1_val, 'p2_val' => $p2_val, 'merge_val' => $merge_value, 'merge_info' => $merge_info);
			//$arr_fields[$taxonomy] = array("taxonomy", $p1_val, $p2_val, $merge_value, $merge_info);
			
			/* e.g.
			$rep_categories = wp_get_post_terms( $post_id, 'repertoire_category', array( 'fields' => 'names' ) );
			if ( count($rep_categories) > 0 ) {
				foreach ( $rep_categories as $category ) {
					if ( $category != "Choral Works" ) {
						$rep_info .= '<span class="category">';
						$rep_info .= $category;
						$rep_info .= '</span>';
					}                
				}
				//$rep_info .= "Categories: ";
				//$rep_info .= implode(", ",$rep_categories);
				//$rep_info .= "<br />";
			}
			*/
			
		}
		
		// Get related posts for both posts (events, &c?)
		// WIP
		//...
			
		if ( !$merging ) {
			// Open the table for comparison/review of post & merge values		
			$info .= '<table class="pre">';
			$info .= '<tr><th style="width:5px;">&nbsp;</th><th width="100px">Field Type</th><th width="180px">Field Name</th><th>P1 Value</th><th>Merge Value</th><th>P2 Value</th></tr>';
		}
		
		foreach ( $arr_fields as $field_name => $values ) {
	
			$field_cat = $values['field_cat'];
			$field_type = $values['field_type'];
			$field_label = $values['field_label'];
			$p1_val = $values['p1_val'];
			$p2_val = $values['p2_val'];
			$merge_value = $values['merge_val'];
			$merge_val_info = $values['merge_info'];
			//
			$post_field_name = "sdg_".$field_name;
			
			// WIP: track fields cumulatively to determine whether records are identical and, if so, offer option to delete p2 (or -- newer of two posts)
			if ( $p1_val != $p2_val ) { $identical_posts = false; }			
			
			if ( $merging ) {
                
				if ( $field_name == "post_title" ) {
					continue;
				}
				
				$merge_info = "";					
				if ( !empty($field_name) ) { $merge_info .= "[$field_name]<br />"; }
				
				// Compare old stored value w/ new merge_value, to see whether update is needed
				$old_val = $p1_val;
				if ( is_array($old_val) ) { $old_val = trim(implode("; ",$old_val)); } else { $old_val = trim($old_val); }				
				if ( !empty($_POST[$post_field_name]) ) { $new_val = trim($_POST[$post_field_name]); } else { $new_val = ""; }
				
				if ( !empty($old_val) || !empty($new_val) ) {
				
					$merge_info .= "old_val: '$old_val'; new_val: '$new_val'<br />";
					
					if ( strcmp($old_val, $new_val) != 0 ) {
					
						$merge_info .= "New value for $field_cat '$field_name' -> run update<br />";
						// convert new_val to array, if needed -- check field type >> explode
						$field_value = $new_val;
						
						// WIP: build in update options for all field_cats...
						if ( $field_cat == "core_field" ) {
							
							// $arr_core_fields = array( 'post_title', 'content', 'excerpt', 'post_thumbnail' );
							if ( $field_name == "post_thumbnail" ) {
								$merge_info .= "Prepped to run set_post_thumbnail:<br />>>> field_name: '$field_name' -- field_value: '".print_r($field_value, true)."' -- post_id: '$p1_id'<br />";
								if ( set_post_thumbnail( $p1_id, $new_val ) ) { // set_post_thumbnail( int|WP_Post $post, int $thumbnail_id ): int|bool
									$merge_info .= "Success! Updated $field_name -- p1 ($p1_id)<br />";
									$fields_merged++;
								} else {
									$merge_info .= '<span class="nb">'."Update failed for $field_name -- p1 ($p1_id)</span><br />";
									$merge_errors = true;
								}
							} else {
								$merge_info .= "Prepped to run update_post:<br />>>> field_name: '$field_name' -- field_value: '".print_r($field_value, true)."' -- post_id: '$p1_id'<br />";
								// WIP Update value via update_post, for other core fields
								$data = array(
									'ID' => $p1_id,
									$field_name => $field_value
								);
								wp_update_post( $data, true );
								if ( is_wp_error($p1_id) ) { // ?? if (is_wp_error($data)) {
									$merge_errors = true;
									$errors = $p1_id->get_error_messages();
									foreach ($errors as $error) {
										$info .= $error;
									}
								} else {
									$fields_merged++;
								}
							}
							
						} else if ( $field_cat == "acf_field" ) {
						
							//$new_value = esc_attr($new_val); // without this, the update fails for records with *single* quotation marks, but WITH, it saves the backslashes. What to do? TODO: figure this out...
							//$new_val = wp_slash($new_val);
							//TODO: try sanitize_meta?
							
							// convert new_val to array, if needed -- check field type >> explode
							if ( $field_type == 'relationship' ) {
								$field_value = explode("; ",$new_val);
							} else {
								$field_value = $new_val;
							}
							// WIP Update value via ACF update_field($field_name, $field_value, [$post_id]);
							$merge_info .= "Prepped to run ACF update_field:<br />field_name: '$field_name' -- field_value: '".print_r($field_value, true)."' -- post_id: '$p1_id'<br />";
							if ( update_field($field_name, $field_value, $p1_id) ) {
								$merge_info .= "Success! Ran update_field for $field_name.<br />";
								$fields_merged++;
							} else {
								//TODO: consider using WP update_post_meta instead if update_field fails?
								$merge_info .= '<span class="nb">'."Oh no! Update failed.</span><br />";
								$merge_errors = true;
							}
							
						} else if ( $field_cat == "taxonomy" ) {
						
							// Turn new_val into an array
							$arr_terms = explode("; ",$new_val);
							$merge_info .= "Prepped to run wp_set_post_terms:<br />>>> taxonomy: '$field_name' -- arr_terms: '".print_r($arr_terms, true)."' -- post_id: '$p1_id'<br />";
							// convert new_val to array, if needed -- check field type >> explode
							// WIP Update value via wp_set_post_terms( $post_id, $term_ids, $taxonomy ); // $term_ids = array( 5 ); // Correct. This will add the tag with the id 5.
							// wp_set_post_terms( int $post_id, string|array $terms = '', string $taxonomy = 'post_tag', bool $append = false ): array|false|WP_Error
							if ( wp_set_post_terms( $p1_id, $arr_terms, $field_name, false ) ) { // append=false, i.e. delete existing terms, don't just add on.
								$merge_info .= "Success! wp_set_post_terms completed.<br />";
								$fields_merged++;
							} else {
								$merge_info .= '<span class="nb">'."Oh no! Update via wp_set_post_terms failed.</span><br />";
								$merge_errors = true;
							}
						}
						
						//$merge_info .= "<br />";
						$merge_info = '<div class="info">'.$merge_info.'</div>';
						
						$info .= $merge_info;
						
					} else {
						//$merge_info .= "New value same as old for $field_name<br /><br />";
					}
				} // End if old and/or new value is non-empty
                
			} else { 
				
				// Comparison -- not merging
				
				// WIP/TODO: implode everything -- passing printed arrays is basically useless.
				if ( is_array($p1_val) ) { $p1_val_str = implode("; ",$p1_val); } else { $p1_val_str = $p1_val; }
				if ( is_array($p2_val) ) { $p2_val_str = implode("; ",$p2_val); } else { $p2_val_str = $p2_val; }
				//if ( is_array($p1_val) ) { $p1_val_str = "<pre>".print_r($p1_val,true)."</pre>"; } else { $p1_val_str = $p1_val; }
				//if ( is_array($p2_val) ) { $p2_val_str = "<pre>".print_r($p2_val,true)."</pre>"; } else { $p2_val_str = $p2_val; }
				if ( is_array($merge_value) ) { 
					$merge_value_str = implode("; ",$merge_value);
					//$merge_val_info .= "(".count($merge_value)." item array)";
				} else {
					$merge_value_str = $merge_value;
				}
				//if ( is_array($merge_value) ) { $merge_value_str = "<pre>".print_r($merge_value,true)."</pre>"; } else { $merge_value_str = $merge_value; }
				if ( $p1_val == $merge_value ) { $p1_class = "merged_val"; } else { $p1_class = "tbx"; }
				if ( $p2_val == $merge_value ) { $p2_class = "merged_val"; } else { $p2_class = "tbx"; }
				if ( !empty($merge_val_info) ) { $merge_val_info = ' <span class="merge_val_info">'.$merge_val_info.'</span>'; }
				//if ( !empty($merge_val_info) ) { $merge_val_info = ' ['.$merge_val_info.']'; }
		
				if ( !( empty($p1_val) && empty($p2_val) ) ) {
			
					// Open row
					$info .= '<tr>';
					$info .= '<td>'.'</td>';
			
					// Field info
					$info .= '<td>'.$field_cat.'</td>';
					$info .= '<td>'.$field_name;
					if ( !empty($field_label) ) { $info .= '<br />('.$field_label.')'; }
					$info .= '</td>';
			
					// Display P1 value
					$info .= '<td class="'.$p1_class.'">';
					if ( $field_type == "taxonomy" && is_array($p1_val) ) {
						foreach ( $p1_val as $term_id ) {
							$info .= get_term( $term_id )->name."<br />";
						}
					} else {
						$info .= $p1_val_str;
					}					
					$info .= '</td>';
			
					// TODO: set input type based on field_type -- see corresponding ACF fields e.g. select for fixed options; checkboxes for taxonomies... &c.
					// TODO: set some inputs with readonly attribute and class="readonly" to make it obvious to user
					//$readonly = " readonly";
					//$input_class = ' class="readonly"';
					// Deal w/ title_for_matching -- will be auto-regenerated, so manual editing is pointless
					//field_type: number -- e.g. choirplanner_id (legacy data)
					//
			
					// Display merge value
					$info .= '<td>';
                    
                    // WIP 230221 make input_name that won't conflict with any CPT name, taxonomy, &c. (reserved words) -- TS post issues...
                    $input_name = "sdg_".$field_name;
                    
					if ( $field_cat != "core_field" && ( $field_type == "text" || $field_type == "textarea" ) ) { // Disabled editing for core fields for now. Title is auto-gen anyway and thumbnails are seldom used for rep.
						$info .= '<textarea name="'.$input_name.'" rows="5" columns="20">'.$merge_value_str.'</textarea>';
						$info .= $merge_val_info;
					} else if ( $field_type == "taxonomy" ) {
						if ( is_array($merge_value) ) {
							foreach ( $merge_value as $term_id ) {
								$info .= '<span class="nb merged_val">'.get_term( $term_id )->name."</span><br />";
							}
						}
						$info .= '<span class="tmp"><pre>'.print_r($merge_value, true).'</pre></span>';
						$info .= '<input type="hidden" name="'.$input_name.'" value="'.$merge_value_str.'" />';
						//$info .= '<input type="hidden" name="'.$field_name.'" value="'.print_r($merge_value, true).'" />';
					} else {
						//$info .= 'field_type: '.$field_type.'<br />';
						$info .= '<span class="nb merged_val">'.$merge_value_str.'</span>'.$merge_val_info;
						if ( $field_name != "post_title" ) {
							$info .= '<input type="hidden" name="'.$input_name.'" value="'.$merge_value_str.'" />';
						}				
					}
					$info .= '</td>';
			
					// Display P2 value
					$info .= '<td class="'.$p2_class.'">';
					if ( $field_type == "taxonomy" && is_array($p2_val) ) {
						foreach ( $p2_val as $term_id ) {
							$info .= get_term( $term_id )->name."<br />";
						}
					} else {
						$info .= $p2_val_str;
					}					
					$info .= '</td>';
			
					// Close row
					$info .= '</tr>';
				} // End if p1 and/or p2 value is non-empty
			
			} // END if ( $merging )
		
		} // END foreach ( $arr_fields....
		
		if ( $merging ) {

			//$merge_errors = true; // tft to prevent trashing of p2
			
			if ( !$merge_errors ) {
				
				$info .= "<hr />";
			
				if ( $fields_merged > 0 ) {
					$info .= "<h3>Merge completed successfully for all fields. About to move p2 [".$_POST['p2_id']."] to trash.</h3>";
					// TODO: re-build the title
					$info .= "TODO: re-build the title<br />";
					$new_title = build_the_title( $_POST['p1_id'] );
					$post_title = get_the_title( $_POST['p1_id'] );
					if ( $new_title != $post_title ) {
						$info .= "P1 new_title ($new_title) NE post_title ($post_title).<br />";
						$new_slug = sanitize_title($new_title);
						// Update the post
						$update_args = array(
							'ID'       	=> $p1_id,
							'post_title'=> $new_title,
							'post_name'	=> $new_slug,
						);
						// Update the post
						wp_update_post( $update_args, true );
						$info .= '<div class="info">';
						if ( is_wp_error($p1_id) ) {
							$errors = $p1_id->get_error_messages();
							foreach ($errors as $error) {
								$info .= $error;
							}
						} else {
							$info .= "post_title updated";
						}
						$info .= '</div>';
					} else {
						$info .= '<div class="info">No change to post_title post-merge.</div>';
					}
				} else {
					$info .= "<h3>No merge required -- Primary post is up-to-date and complete. About to move duplicate p2 [".$_POST['p2_id']."] to trash.</h3>";
				}
			
				$info .= '<div class="info">';
				
				// Add deleted-after-merge admin_tag to P2
				$info .= "About to attempt to add admin_tag 'deleted-after-merge' to post p2 [$p2_id]<br />";
				//$info .= sdg_add_post_term( $p2_id, 'deleted-after-merge', 'admin_tag', true ); // this fcn is still WIP
				$p2_term_ids = wp_get_post_terms( $p2_id, 'admin_tag' );
				$p2_term = get_term_by( 'slug', 'deleted-after-merge', 'admin_tag' ); // get term id from slug
				if ( $p2_term ) { 
					$term_id = $p2_term->term_id;
					$p2_term_ids[] = $term_id;
					$terms_result = wp_set_post_terms( $p2_id, $p2_term_ids, 'admin_tag', true );
					if ( $terms_result ) { $info .= 'admin_tag added.<br />'; } else { $info .= "Nope...<br />"; }
				}
				
				// Add "merged" tag to P1
				$info .= "About to attempt to add admin_tag 'updated-via-merge' to post p1 [$p1_id]<br />";
				//$info .= sdg_add_post_term( $p1_id, 'updated-via-merge', 'admin_tag', true ); // this fcn is still WIP
				$p1_term_ids = wp_get_post_terms( $p1_id, 'admin_tag' );
				$p1_term = get_term_by( 'slug', 'updated-via-merge', 'admin_tag' ); // get term id from slug
				if ( $p1_term ) { 
					$term_id = $term->term_id;
					$term_ids[] = $term_id;
					$terms_result = wp_set_post_terms( $p2_id, $p1_term_ids, 'admin_tag', true );
					if ( $terms_result ) { $info .= 'admin_tag added.<br />'; } else { $info .= "Nope...<br />"; }
				}
				
				// Attempt to move P2 to the trash
				if ( wp_trash_post($p2_id) ) {
					$info .= "Success! p2 [".$_POST['p2_id']."] moved to trash.<br />";
					$p2_trashed = true;
				} else {
					$info .= '<span class="nb">'."ERROR! failed to move p2 [".$_POST['p2_id']."] to trash.</span><br />";
					$p2_trashed = false;
				}
				
				$info .= 'Post #1 >>&nbsp;<a href="'.get_permalink($p1_id).'" target="_blank">View</a>&nbsp;|&nbsp;<a href="'.get_edit_post_link($p1_id).'" target="_blank">Edit</a><br />';
				if ( $p2_trashed ) {
					$info .= 'Post #2 >>&nbsp;<a href="/wp-admin/edit.php?post_status=trash&post_type=repertoire" target="_blank">View P2 in Trash</a><br />';
				} else {
					$info .= 'Post #2 >>&nbsp;<a href="'.get_permalink($p2_id).'" target="_blank">View</a>&nbsp;|&nbsp;<a href="'.get_edit_post_link($p2_id).'" target="_blank">Edit</a><br />';
				}
				
				$info .= '</div>';
				
			} else {
				$info .= "<h3>Errors occurred during Merge operation. Therefore p2 [".$_POST['p2_id']."] has not yet been moved to the trash.</h3>";
			}
			
		} else {
			
			// Close the comparison table
			$info .= '</table>';
			
			//
			//$info .= '<input type="hidden" name="form_action" value="merge_records">';
			$info .= '<input type="submit" name="form_action" value="Merge Records"><br /><br />';
			$info .= '<p class="nb"><em>NB: This action cannot be undone!<br />The primary post will be updated with the field values displayed in <span class="green">green</span>, and in the center merge column;<br />the secondary post will be sent to the trash and all field values displayed in <span class="orange">orange</span> will be deleted/overwritten.</p>';
			//$info .= '<a href="#!" id="form_reset">Clear Form</a>';
		}
		
		$info .= '</form>';
    
	} else {
		$info .= '<hr />';
		//$info .= "Post count incorrect for comparison or merge (".count($arr_posts).")<br />";
	} // END if ( count($arr_posts) == 2 )       
    
    $info .= '<div class="troubleshootingX">';
    $info .= $ts_info;
    $info .= '</div>';
    
    return $info;
    
}


/*********** MEDIA ***********/

// Get a linked list of Media Items
add_shortcode('list_media_items', 'sdg_list_media_items');
function sdg_list_media_items ($atts = [] ) {

    global $wpdb;
    
	$info = "";
    $mime_types = array();
	
	$a = shortcode_atts( array(
      	'type'        => null,
		'category'    => null,
		'grouped_by'  => null,
    ), $atts );
	
    if ($a['type'] == "pdf") {
        $mime_types[] = "application/pdf";
    } else {
        $mime_types[] = $a['type'];
    }
    
    //$unsupported_mimes  = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
    //$all_mimes          = get_allowed_mime_types();
    //$mime_types       = array_diff( $all_mimes, $unsupported_mimes );
    
    $category = $a['category'];
    $grouped_by = $a['grouped_by'];
	
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    if ( !empty($mime_types) ) {
        $args['post_mime_type'] = $mime_types;
    }
    //'post_mime_type' => 'image/gif',
    
    if ( $category !== null ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' 	=> 'media_category',
                'field' 	=> 'slug',
                'terms' 	=> $category
            )
        );
    }

    $arr_posts = new WP_Query( $args );
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
                    
                    $args = array(
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
                    $liturgical_date_calc_post_obj = new WP_Query( $args );
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

?>