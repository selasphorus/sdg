<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** POST RELATIONSHIPS ***********/

// The following function is the replacement for the old get_related_podposts fcn
function sdg_get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' ) {

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
            usort($items, arr_sort('value', 'sort_date', 'DESC'));
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