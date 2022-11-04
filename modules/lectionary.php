<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** CPT: LITURGICAL DATE ***********/

//function get_cpt_liturgical_date_content() {	}


// Day Titles
add_shortcode('day_title', 'get_day_title');
function get_day_title( $atts = [], $content = null, $tag = '' ) {

    // TODO: Optimize this function! Queries run very slowly. Maybe unavoidable given wildcard situation. Consider restructuring data?
	$info = "\n<!-- get_day_title -->\n";
    
    $args = shortcode_atts( 
        array(
            'post_id'   => get_the_ID(),
            'series_id' => null,
            'the_date'  => null,
        ), 
        $atts
    );
    
    $post_id = (int) $args['post_id'];
    $series_id = (int) $args['series_id'];
    $the_date = $args['the_date'];
    $hide_day_titles = 0; // init

    if ( $post_id === null ) { $post_id = get_the_ID(); }
    $info .= "<!-- post_id: ".$post_id." -->\n"; // tft
    if ( $series_id ) { $info .= "<!-- series_id: ".$series_id." -->\n"; }
    //$info .= "<!-- the_date: ".$the_date." -->\n"; // tft
    
    // Check to see if day titles are to be hidden for the entire event series, if any
    if ( $series_id ) { 
    	$hide_day_titles = get_post_meta( $series_id, 'hide_day_titles', true );
    }
    
    // If there is no series-wide ban on displaying the titles, then should we display them for this particular post?
    if ( $hide_day_titles == 0 ) {
    	$hide_day_titles = get_post_meta( $post_id, 'hide_day_titles', true );
    }
    //$info .= "<!-- hide_day_titles: [$hide_day_titles] -->";
    
    if ( $hide_day_titles == 1 ) { 
        $info .= "<!-- hide_day_titles is set to true for this post/event -->";
        return $info;
    } else {
        //$info .= "<!-- hide_day_titles is not set or set to zero for this post/event -->";
    }
    
	if ($the_date == null) {
        
        $info .= "<!-- the_date is null -- get the_date -->"; // tft
		
        // If no date was specified when the function was called, then get event start_date or sermon_date OR ...
        if ( $post_id === null ) {
            
            return "<!-- no post -->";
            
        } else {
            
            $post = get_post( $post_id );
            $post_type = $post->post_type;
            $info .= "<!-- post_type: ".$post_type." -->"; // tft

            if ( $post_type == 'event' ) {
                
                $date_str = get_post_meta( $post_id, '_event_start_date', true );
                $the_date = strtotime($date_str);
                
            } else if ( $post_type == 'sermon' ) {
                
                $info .= "<!-- sermon -->";
                $the_date = the_field( 'sermon_date', $post_id );
                //if ( get_field( 'sermon_date', $post_id )  ) { $the_date = the_field( 'sermon_date', $post_id ); }
                
            } else {
                //$info .= "post_id: ".$post_id."<br />"; // tft
                //$info .= "post_type: ".$post_type."<br />"; // tft
            }
        }
        
	}    
    
    if ( $the_date == null ) {
        
        // If still no date has been found, give up.
        $info .= "<!-- no date available for which to find day_title -->\n"; // tft
        return $info;
        
    } else {
        
        // Otherwise, format the date and continue.
        $info .= "<!-- the_date: '$the_date' -->\n";
        $info .= "<!-- print_r the_date: '".print_r($the_date, true)."' -->\n"; // tft
        
        $timestamp = strtotime($the_date);
        $info .= "<!-- timestamp: '$timestamp' -->\n"; // tft
        
        $fixed_date_str = date("F d", $timestamp ); // day w/ leading zeros
        $info .= "<!-- fixed_date_str: '$fixed_date_str' -->\n"; // tft
        
        $day_num = intval(date("j", $timestamp ));
        if ( $day_num < 10 ) {
            $info .= "<!-- day_num: '$day_num' -->\n"; // tft
            $fixed_date_str_alt = date("F j", $timestamp ); // day w/ out leading zeros
            $info .= "<!-- fixed_date_str_alt: '$fixed_date_str_alt' -->\n"; // tft
        }
        
        $full_date_str = date("Y-m-d", $timestamp );        
        $info .= "<!-- full_date_str: '$full_date_str' -->\n"; // tft
    }
    
    // TODO: deal w/ replacement_date option (via Data Assignments field group)
    // date_assignments: "Use this field to override the default Fixed Date or automatic Date Calculation."
    // replacement_date: "Check the box if this is the ONLY date of observance during the calendar year in question. Otherwise the custom date assignment will be treated as an ADDITIONAL date of observance."
    
    /// Build single query to searh for any liturgical dates that match, whether dates are fixed, calculated, or manually assigned
    
    $litdate_args = array(
        'post_type'		=> 'liturgical_date',
        'post_status'   => 'publish',
        'meta_query'	=> array(
            'relation' => 'AND',
            array(
                'key'		=> 'day_title',
                'compare'	=> '=',
                'value'		=> '1',
            ),
            array(
                'relation'  => 'OR',
                array(
                    'key'		=> 'fixed_date_str',
                    'compare'	=> '=',
                    'value'		=> $fixed_date_str,
                ),
                array(
                    'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
                    'compare'	=> '=',
                    'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
                ),
                array(
                    'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
                    'compare'	=> '=',
                    'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
                ),
            )
          ),
      );
    
    //$info .= "<!-- litdate_args: <pre>".print_r($litdate_args, true)."</pre> -->"; // tft
    $arr_posts = new WP_Query( $litdate_args );
    $litdate_posts = $arr_posts->posts;
    $num_litdate_posts = count($litdate_posts);
    //$info .= "<!-- SQL-Query: <pre>{$arr_posts->request}</pre> -->"; // tft
    $info .= "<!-- num_litdate_posts: ".$num_litdate_posts." -->"; // tft

    // If some posts were retrieved for dates calculated and/or assigned
    
    $litdate_id = null; // init
    
    // ... multiple matches? prioritize... pick one and then fetch the ONE litdate, collect, &c.
    // WIP
    if ( $num_litdate_posts == 1 ) {
        
        $litdate_post = $litdate_posts[0];
        $litdate_id = $litdate_post->ID;
        $info .= "<!-- Single litdate_post found: <pre>".print_r($litdate_post, true)."</pre> -->"; // tft
        //$litdate_post_id = $litdate_posts[0]['ID'];
        
    } else if ( $num_litdate_posts > 1 ) {
        
        $litdates = array();
        
        // ...loop through, check for holy days vs saints and martyrs... check for override settings...
        foreach ( $litdate_posts AS $litdate_post ) {
            
            $litdate_post_id = $litdate_post->ID;
            $info .= "<!-- litdate_post->ID: ".$litdate_post_id." -->"; // tft
            
            // Get date_type (fixed, calculated, assigned)
            $date_type = get_post_meta( $litdate_post_id, 'date_type', true );
            $info .= "<!-- date_type: ".$date_type." -->"; // tft
            
            // Get category/priority
            $terms = get_the_terms( $litdate_post_id, 'liturgical_date_category' );
            //$info .= "<!-- terms: ".print_r($terms, true)." -->"; // tft
            
            $top_priority = 999;
            if ( $terms ) {
                
                foreach ( $terms as $term ) {
                    $priority = get_term_meta($term->term_id, 'priority', true);
                    $info .= "<!-- term: ".$term->slug." :: priority: ".$priority." -->"; // tft

                    if ( !empty($priority) && $priority < $top_priority) { 
                        $top_priority = $priority;
                        $info .= "<!-- NEW top_priority: ".$top_priority." -->"; // tft
                    }
                }
                
            }
            
            
            $info .= "<!-- top_priority: ".$top_priority." -->"; // tft
            $litdates[$top_priority] = $litdate_post_id;
            //
            
        }
        
        $info .= "<!-- litdates: ".print_r($litdates, true)." -->"; // tft
        uksort($litdates, arr_sort( 'key' ));
        $info .= "<!-- litdates sorted: ".print_r($litdates, true)." -->"; // tft
        
        // Get first item in the associative array -- that's the one to use because it has the lowest priority number and therefore is most important
        //$firstKey = array_key_first($array);
        
        $top_key = array_key_first($litdates);
        $info .= "<!-- top_key: ".$top_key." -->"; // tft
        $litdate_id = $litdates[$top_key];
        $info .= "<!-- litdate_id: ".$litdate_id." -->"; // tft
    }
    
    // 
    if ( $litdate_id ) {
        
        //$the_date = the_field( 'sermon_date', $post_id );
        //if ( get_field( 'sermon_date', $post_id )  ) { $the_date = the_field( 'sermon_date', $post_id ); }
        
        $litdate_title = get_the_title( $litdate_id );
		$litdate_content = get_the_content( null, false, $litdate_id ); // get_the_content( string $more_link_text = null, bool $strip_teaser = false, WP_Post|object|int $post = null )
        
        $collect = null; // init
        
        $info .= "<!-- litdate_id: $litdate_id -->";

        $args = array(
            'post_type'   => 'collect',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key'     => 'liturgical_date',
                    'value'   => $litdate_id
                )
            )
        );
        $collect_post = new WP_Query( $args );
        $collect = $collect_post->post_content;
            
        // TODO/atcwip: if no match by litdate_id, then check propers 1-29 by date (e.g. Proper 21: "Week of the Sunday closest to September 28")
        
        // If there's something other than the title available to display, then display the popup link
        // TODO: set width and height dynamically based on browser window dimensions
		$width = '650';
		$height = '450';
			
        if ( $collect != null ) {
        //if ( $collect != null || ( is_dev_site() && ! ($litdate_content == null && $collect == null) ) ) {

            $info .= '<a href="#!" id="dialog_handle_'.$litdate_id.'" class="calendar-day dialog_handle">';
            $info .= $litdate_title;
            $info .= '</a>';
            $info .= '<br />';
            $info .= '<div id="dialog_content_'.$litdate_id.'" class="calendar-day-desc dialog">';
            $info .= 		'<h2 autofocus>'.$litdate_title.'</h2>';
            if ( is_dev_site() ) {
                $info .= 		$litdate_content;
            }
            if ($collect !== null) {
                $info .= 	'<div class="calendar-day-collect">';
                //$info .= 		'<h3>Collect:</h3>';
                $info .= 		'<p>'.$collect.'</p>';
                $info .= 	'</div>';
            }
            $info .= '</div>'; //<!-- /calendar-day-desc -->

        } else {
            // If no content or collect, just show the day title
            $info .= '<span id="'.$litdate_id.'" class="calendar-day">'.$litdate_title.'</span><br />';
        }
        
    } else {
		
        $info .= "<!-- no litdate found -->";
		//$info .= "<!-- params: <pre>".print_r($params, true)."</pre> -->";
        
	}
	
	return $info;
	
}


// Function(s) to calculate variable liturgical_dates
add_shortcode('calculate_variable_dates', 'calc_litdates');
function calc_litdates( $atts = [] ) {

    // TODO: build in failsafe -- run this fcn ONLY for user queenbee
    
	$info = ""; // init
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;";
/*
	$a = shortcode_atts( array(
        'testing' => true,
        'id' => null,
        'year' => date('Y'),
        'num_posts' => 10,
        'admin_tag_slug' => 'dates-calculated', // 'programmatically-updated'
        'orderby' => 'title',
        'order' => 'ASC',
        'meta_key' => null
    ), $atts );
    
    $testing = $a['testing'];
    $num_posts = (int) $a['num_posts'];
    $year = get_query_var( 'y' );
    if ( $year == "" ) { $year = $a['year']; }
    //$year = get_query_var( 'year' ) ? get_query_var( 'year' ) : $a['year'];
    //$year = $a['year'];
    $orderby = $a['orderby'];
    $order = $a['order'];
    $meta_key = $a['meta_key'];
    $admin_tag_slug = $a['admin_tag_slug'];
    
	$args = array(
		'post_type' => 'liturgical_date',
		'post_status' => 'publish',
        'posts_per_page' => $num_posts,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'   => "date_type", 
                'value' => 'variable',
            ),
            array(
                'key'   => "date_calculation",
                'compare' => 'EXISTS'
            )
        ),
        'orderby'	=> $orderby,
        'order'	=> $order,
	);
    
    if ( $a['id'] !== null ) { $args['p'] = $a['id']; }
    if ( $a['meta_key'] !== null ) { $args['meta_key'] = $meta_key; }
    
	$arr_posts = new WP_Query( $args );
    $posts = $arr_posts->posts;
    
    $info .= ">>> calc_litdates <<<<br />";
    $info .= "testing: ".$a['testing']."; orderby: $orderby; order: $order; meta_key: $meta_key; ";
    $info .= "year: $year<br />";
    $info .= "[num posts: ".count($posts)."]<br />";
    //$info .= "args: <pre>".print_r( $args, true )."</pre>";
    $info .= "<!-- args: <pre>".print_r( $args, true )."</pre> -->";
    //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre><br />"; // tft
    $info .= "<br />";
    
    $liturgical_bases = array('advent' => 'advent_sunday_date', 'christmas' => 'December 25', 'epiphany' => 'January 6', 'ash wednesday' => 'ash_wednesday_date', 'lent' => 'ash_wednesday_date', 'easter' => 'easter_date', 'ascension day' => 'ascension_date', 'pentecost' => 'pentecost_date' );
    //$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
    $oabis = array('before', 'after', 'of', 'in');
    
    
    foreach ( $posts AS $post ) {
        
        setup_postdata( $post );
        $post_id = $post->ID;
        $post_title = $post->post_title;
        $slug = $post->post_name;
        $info .= '<span class="label">['.$post_id.'] "'.$post_title.'"</span><br />';
    
        // init -- surely there's a better way to do this? -- via an array or object?
        $calc_info = "";
        $changes_made = false;
        $date_calculation_str = "";
        $calc_basis = "";
        $calc_basis_field = "";
        $calc_weekday = "";
        $calc_month = "";
        $calc_oabi = ""; // before/after/of/in the basis_date/season?
        $calc_interval = "";
        $basis_date_str = "";
        $basis_date = "";
        $basis_date_weekday = "";
        $calc_date = "";
        $calc_formula = "";
        
        // Get date_calculation info & break it down
        $date_calculation_str = get_post_meta( $post_id, 'date_calculation', true );
        $date_calculation_str = str_replace('christmas day', 'christmas', strtolower($date_calculation_str) );
        $date_calculation_str = str_replace('the epiphany', 'epiphany', strtolower($date_calculation_str) );
        //$date_calculation_str = str_replace(['the', 'day'], '', strtolower($date_calculation_str) );
        //$calc_info .= $indent."date_calculation_str: $date_calculation_str<br />"; // tft
        
        // Get the liturgical date info upon which the calculation should be based (basis extracted from the date_calculation_str)
        foreach ( $liturgical_bases AS $basis => $basis_field ) {
            if (stripos($date_calculation_str, $basis) !== false) {
                $calc_basis = $basis;
                $calc_basis_field = $basis_field;
            }
        }
        
        if ( $calc_basis != "" ) {            
            //$calc_info .= $indent."calc_basis: $calc_basis // $calc_basis_field<br />"; // $info .= "calc_basis_field: $calc_basis_field -- "; // tft            
        } else {
            $calc_info .= $indent."No calc_basis found.<br />";
        }
        
        // Find the liturgical_date_calc post for the selected year
        // (liturgical_date_calc records contain the dates for Easter, Ash Wednesday, &c. per year)
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
        $liturgical_date_calc_post = new WP_Query( $args );
        $liturgical_date_calc_id = $liturgical_date_calc_post->ID;
        
        // Get the basis date in the given year, from the Liturgical Date Calculations CPT (liturgical_date_calc)
        if ( $calc_basis == 'christmas' ) {
            $basis_date_str = $year."-12-25";          
        } else if ( $calc_basis == 'epiphany' ) {                
            $basis_date_str = $year."-01-06";
            $num_sundays_after_epiphany = get_post_meta( $liturgical_date_calc_id, 'num_sundays_after_epiphany', true);
        } else {
            if ( $liturgical_date_calc_post ) {
                $basis_date_str = get_post_meta( $liturgical_date_calc_id, $calc_basis_field, true);
                //$calc_info .= $indent."basis_date_str: [$basis_date_str]<br />";
            } else {
                $calc_info .= $indent."No matching liturgical_date_calc_post for args ".print_r($args,true)."<br />";
            }
        }
    
        // Get related date, where relevant
        if ( $calc_basis == "epiphany" || $calc_basis == "advent" || $calc_basis == "pentecost" ) {
            // Get the Advent Sunday date
            $advent_sunday_date = get_post_meta( $liturgical_date_calc_id, 'advent_sunday_date', true);
        } else if ( $calc_basis == "epiphany" || $calc_basis == "lent" ) {
            // Get the Ash Wednesday date
            $ash_wednesday_date = get_post_meta( $liturgical_date_calc_id, 'ash_wednesday_date', true);
        }

        if ( $basis_date_str == "" ) {
            $basis_date_str = "$year-01-01";
        }
        
        // Get the basis_date from the string version
        $basis_date = strtotime($basis_date_str);
        $basis_date_weekday = strtolower( date('l', $basis_date) );
        
        $calc_info .= $indent.'<span class="notice">'."basis_date: $basis_date_str ($basis_date_weekday)</span> ($calc_basis // $calc_basis_field)<br />";
        
        // Check to see if the date to be calculated is in fact the same as the base date
        if ( strtolower($date_calculation_str) == $calc_basis ) { // Easter, Christmas, Ash Wednesday", &c.=
            
            $calc_date = $basis_date;
            
        } else {
            
            // ** Extract components of date_calculation_str & calculate date for $year
            
            // What's the weekday for the date to be calculated?
            foreach ( $weekdays AS $weekday ) {
                if (stripos($date_calculation_str, $weekday) !== false) {
                    $calc_weekday = strtolower($weekday);
                }
            }
            //$info .= $indent."calc_weekday: $calc_weekday<br />"; // tft

            // ** Does the date to be calculated fall before/after/of/in the basis_date/season?
            foreach ( $oabis AS $oabi ) {
                if (stripos($date_calculation_str, $oabi) !== false) {
                    $calc_oabi = strtolower($oabi);
                }
            }
            //$info .= $indent."calc_oabi: $calc_oabi<br />"; // tft

            // ** Determine the calc_interval -- number of days/weeks...
            if ( preg_match('/([0-9]+)/', $date_calculation_str) ) {
                
                //$info .= $indent."date_calculation_str contains numbers.<br />";          
                $calc_interval = str_replace([$calc_basis, $calc_weekday, $calc_oabi, 'the', 'th', 'nd', 'rd', 'st'], '', strtolower($date_calculation_str) );
                $calc_interval = trim( $calc_interval );
                
                //if ( $calc_oabi == ("in" || "of") ) { // Advent, Easter, Lent
                if ( ( $calc_basis == "advent" && $calc_oabi != "before" ) || ( $calc_basis == "easter" && $calc_oabi == "of" ) ) {
                    $calc_interval = (int) $calc_interval - 1; // Because Advent Sunday is first Sunday of Advent, so 2nd Sunday is basis_date + 1 week, not 2
                }
                //$info .= $indent."calc_interval: $calc_interval<br />"; // tft
                
            } else if ( strpos(strtolower($date_calculation_str), 'last') !== false ) {
                
                // e.g. "Last Sunday after the Epiphany"; "Last Sunday before Advent"; "Last Sunday before Easter"
                //$calc_info .= $indent."LAST<br />"; // tft
                if ( $calc_basis == "epiphany" ) {
                    $calc_interval = $num_sundays_after_epiphany;
                } else if ( $calc_basis == "easter" ) { // && $calc_oabi == "before"
                    $calc_formula = "previous Sunday"; //$calc_formula = "Sunday before";
                } else if ( $date_calculation_str == "last sunday before advent" ) {
                    $calc_formula = "previous Sunday";//$calc_formula = "Sunday before";
                }
                
            }
            
            // If the calc_formula hasn't already been determined, build it
            if ( $calc_formula == "" ) {
                
                //$calc_info .= $indent."About to build calc_formula...<br />"; // tft
                
                // If the basis_date is NOT a Sunday, then get the date of the first_sunday of the basis season
                if ( $basis_date_weekday != "" && $basis_date_weekday != 'sunday' ) {                    
                    $first_sunday = strtotime("next Sunday", $basis_date);
                    //$calc_info .= $indent."first_sunday after basis_date is ".date("Y-m-d", $first_sunday)."<br />"; // tft                    
                    if ( $calc_interval ) { $calc_interval = $calc_interval - 1; } // because math is based on first_sunday + X weeks. -- but only if calc_weekday is also Sunday? WIP
                    if ( $calc_interval === 0 ) { $calc_date = $first_sunday; }
                } else if ( $basis_date ) {
                    $first_sunday = $basis_date;
                    $calc_info .= $indent."first_sunday is equal to basis_date.<br />"; // tft
                }

                if ( $calc_basis != "" && $calc_weekday == "sunday" ) {

                    if ( ($calc_interval > 1 && $calc_oabi != "before") || ($calc_interval == 1 && $calc_oabi == ("after" || "in") ) ) {
                        $calc_formula = "+".$calc_interval." weeks";
                        $basis_date = $first_sunday;                    
                    } else if ( $calc_oabi == "before" ) { 
                        $calc_formula = "previous Sunday";
                    } else if ( $calc_oabi == "after" ) { 
                        $calc_formula = "next Sunday";
                    } else if ( $first_sunday ) {
                        $calc_date = $first_sunday; // e.g. "First Sunday of Advent"
                    } 

                } else if ( $calc_basis != "" && $calc_oabi == ( "before" || "after") ) {
                    
                    //$calc_info .= $indent."setting prev/next<br />"; // tft
                    // e.g. Thursday before Easter; Saturday after Easter -- BUT NOT for First Monday in September; Fourth Thursday in November -- those work fine as they are via simple strtotime
                    if ( $calc_oabi == "before" ) { $prev_next = "previous"; } else { $prev_next = "next"; } // could also use "last" instead of "previous"
                    $calc_formula = $prev_next." ".$calc_weekday; // e.g. "previous Friday";
                }
                
            }
            
            // If there's no $calc_formula yet, use the date_calculation_str directly
            if ( $calc_formula == "" ) { // && $calc_date == ""
                $calc_info .= $indent.'<span class="notice">'."calc based directly on date_calculation_str</span><br />";
                if ( $calc_oabi != "after" && $calc_date == "" ) {
                    $calc_formula = $date_calculation_str;               
                }
            }
            
            $calc_info .= $indent.">> date_calculation_str: $date_calculation_str<br />"; // tft
            //$calc_info .= $indent.">> [$calc_interval] -- [$calc_weekday] -- [$calc_oabi] -- [$calc_basis_field]<br />"; // tft
            $calc_info .= $indent.'>> calc_formula: "'.$calc_formula.'"; basis_date: '.date('Y-m-d',$basis_date).'<br />'; // tft
            //$calc_info .= $indent.'>> basis_date unformatted: "'.$basis_date.'<br />'; // tft
            //
            // Do the actual calculation
            if ( $calc_formula != "" && $basis_date != "" ) {
                $calc_date = strtotime("$calc_formula", $basis_date);
                //$calc_info .= $indent.'strtotime("'.$calc_formula.'",$basis_date)<br />';
                //$calc_info .= $indent."calc_date -- ".$calc_date.' = strtotime("'.$calc_formula.'", '.$basis_date.')<br />'; // tft
                // X-check with https://www.w3schools.com/php/phptryit.asp?filename=tryphp_func_strtotime
                // calc_formula examples: '-6 months' // '+2 year' // "last Sunday" // "+4 weeks" // "next Sunday" // '+1 week'
            } else {
                $calc_info .= $indent."Can't do calc -- calc_formula or basis_date is empty.<br />";
            }
            
            // Make sure the calculated date doesn't conflict with the subsequent church season -- this applies to only Epiphany (into Lent) and Pentecost (into Advent)
            // e.g. does this supposed Sunday of advent run into Lent?
            if ( $calc_basis == "epiphany" ) {
                $calc_info .= $indent."There are $num_sundays_after_epiphany Sundays after Epiphany in $year.<br />"; // tft
                if ( $calc_date > strtotime($ash_wednesday_date) ) { //if ( (int) $calc_interval > (int) $num_sundays_after_epiphany ) {
                    $calc_info .= $indent.'<span class="warning">Uh oh! That\'s too many Sundays.</span><br />'; // tft
                    $calc_info .= $indent.'<span class="warning">calc_date: ['.date('Y-m-d', $calc_date).']; ash_wednesday_date: '.$ash_wednesday_date.'</span><br />'; // tft
                    $calc_date = "N/A";
                }
            } else if ( $calc_basis == "lent" ) {
                // make sure this doesn't overlap w/ holy week
            } else if ( $calc_basis == "pentecost" ) {
                
                // Pentecost: "This season ends on the Saturday before the First Sunday of Advent."                
                // TODO -- figure out if this is the LAST Sunday of Pentecost?
                
                if ( $calc_date > strtotime("previous Saturday", strtotime($advent_sunday_date) ) ) {
                //if ( $calc_date > strtotime($advent_sunday_date) ) {
                    //$calc_info .= $indent.date( 'Y-m-d', strtotime("previous Saturday", strtotime($advent_sunday_date)) );
                    $calc_info .= $indent.'<span class="warning">'."Uh oh! ".date('Y-m-d',$calc_date)." conflicts with Advent. Advent Sunday is $advent_sunday_date.</span><br />"; // tft
                    $calc_date = "N/A";
                } else {
                    $calc_info .= $indent."(Advent begins on $advent_sunday_date.)<br />"; // tft
                }
            }

        }
        
        if ( $calc_date != "N/A" ) {
            $calc_date_str = date('Ymd', $calc_date); // was originally 'Y-m-d' format, which is more readable in DB, but ACF stores values edited via CMS *without* hyphens, despite field setting -- bug? or am I missing something?
            $calc_info .= $indent."calc_date_str: <strong>$calc_date_str</strong> (".date('l, F d, Y',$calc_date).")<br />"; // tft
        } else {
            $calc_date_str = "";
            $calc_info .= $indent."calc_date N/A<br />"; // tft
            $info .= $calc_info;
        }
        
        // 3. Save dates to ACF repeater field row for date_calculatedday_
        // DB: date_calculations >> date_calculated -- date_calculations_[#]_date_calculated
        
        if ( $calc_date_str != "" ) {
            
            $newrow = true;
            
            if ( have_rows('date_calculations', $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
                while ( have_rows('date_calculations', $post_id) ) : the_row();
                    $date_calculated = get_sub_field('date_calculated'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
                    if ( $date_calculated == $calc_date_str ) {
                        // Already in there
                        $newrow = false;
                        $info .= $indent."Old news. This date_calculated ($calc_date_str) is already in the database.<br />"; // tft
                    }
                endwhile;
            } // end if

            if ( $newrow == true ) {

                $row = array(
                    'date_calculated' => $calc_date_str
                );

                $info .= $calc_info;
                //$info .= "About to add row to post_id $post_id: <pre>".print_r( $row, true )."</pre>";
                if ( $testing != "true" ) {
                    if ( add_row('date_calculations', $row, $post_id) ) { // ACF function syntax: add_row($selector, $value, [$post_id])
                        $info .= $indent."ACF row added for post_id: $post_id<br />"; // tft
                    }
                }

            }
        } else {
            $info .= "calc_date_str is empty.<br />";
        }
        
        $info .= "<br />";
               
    } // END foreach post
    */
    return $info;
    
}

/**
 * Adds a box to the main column on the CPT edit screen.
 */
//add_action( 'add_meta_boxes', 'sdg_add_meta_boxes' );
/*function sdg_add_meta_boxes() {

	add_meta_box(
		'calculated_dates',
		__( 'Calculated Dates', 'sdg' ),
		'sdg_liturgical_date_meta_box_callback',
		'liturgical_date'
	);

}*/

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function liturgical_date_meta_box_callback( $post ) {

	// TODO: replace the following with a relative URL
	//echo '<h4><a href="/edit.php?post_type=XXX" target="_blank">Click to Edit XXX</a></h4>';
	
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'sdg_save_meta_box_data', 'sdg_meta_box_nonce' );
	
    // get all ACF date_calculations for this post // date_calculations_0_date_calculated
    
    echo "testing meta_box_callback.";
	
}


/*********** CPT: READING ***********/
function get_cpt_reading_content( $post_id = null ) {
	
    $info = "";
	if ($post_id === null) { $post_id = get_the_ID(); }
    
    // Get the CPT object
    $post = get_post($post_id);
    
    // Link to text of Bible Verses -- WIP
    $bible_book_id = get_post_meta( $post_id, 'book', true ); // TODO: use get_field instead? Will this work to retrieve ID? $bible_book = get_field( 'book', $post_id );
    $info .= "bible_book_id: '".$bible_book_id."'<br />"; // tft
    $bible_corpus_id = get_post_meta( $bible_book_id, 'bible_corpus_id', true );
    $info .= "bible_corpus_id: '".$bible_corpus_id."'<br />"; // tft
    $chapterverses = get_field( 'chapterverses', $post_id );
    
    // Get book num from book name
    $book_num = "tmp";
    // Construct BC-style ID: BBCCCVVV
    // Sub-divide chapterverses
    // e.g. 
    // 1:1-2:3
    // 11:1-9
    // 14:(1-7)8-24
    // 15:1-11, 17-21
    // 17:1-12a, 15-16
    // 2:4b-9, 15-17, 25-3:7
    // 27:46-28:4, 10-22
    // 49:29-50:14
    // If the string contains one or more hyphens, or more than one colon, then multiple chapters are involved
    if ( strpos($chapterverses,"-") || substr_count($chapterverses, ':') > 1 ) {
        $info .= "The string '".$chapterverses."' contains information about more than one chapter.<br />";
        
        $first = substr( $chapterverses, 0, strpos($chapterverses,"-") );
        if ( substr_count($chapterverses, '-') == 1 ) {
            $last = substr( $chapterverses, strpos($chapterverses,"-")+1 );
        } else {
            $last = "not sure!";
        }
        $info .= "first: ".$first."; last: ".$last;
        $info .= "<br />";
        //
    }
    // Determine all individual chapterverses contained w/in range
    // In case of range across chapters: 
    // 1) determine number of chapters (chapter_max - chapter_min);
    // 2) Count/fetch number of verses in chapter? Or simply construct verse IDs in loop and check for existence of each, stop when no match found?
    // $ch_max = ??
    // $ch_min = ??
    // $num_ch = $ch_max - $ch_min;
    // 
    // If the string contains one or more commas, then multiple verses or verse ranges are present for an individual chapter
    /*if ( strpos($chapterverses,",") ) {
        $info .= "The string '".$chapterverses."' contains ".substr_count($chapterverses, ',')." comma(s).";
        $info .= "<br />";
    }*/
    $info .= "The string '".$chapterverses."' contains ".substr_count($chapterverses, ',')." comma(s).";
    $info .= "<br />";
    
    //$pieces = explode("-", $chapterverses);
    //$chapter = substr( $chapterverses,0,strpos($chapterverses,":") );
    //$verses = substr( $chapterverses,strpos($chapterverses,":") );
    //$row_info .= "<!-- chapter: '$chapter'; verses: 'verses' -->"; // tft
    
    //$info .= "chapter: '".$chapter."'; verses: '".$verses."'"; // tft
    
    return $info;
    
}


/*********** CPT: PSALMS OF THE DAY ***********/
function get_cpt_psalms_of_the_day_content() {
	
}

// att service: "morning_prayer" or "evening_prayer"
add_shortcode('psalms_of_the_day', 'get_psalms_of_the_day');
function get_psalms_of_the_day( $atts = [], $content = null, $tag = '' ) {
	
    // init vars
    $info = "";
    $day_num = null;
    $post_type = null;
    
    $a = shortcode_atts( array(
		'id'       => get_the_ID(),
		'service'  => 'morning_prayer', // default to morning prayer
        'event_id' => null
    ), $atts );
    
	$post_id = $a['id'];
	$event_id = $a['event_id'];
    $service = $a['service'];
    
    $post = get_post( $post_id );
    if ( $post ) { $post_type = $post->post_type; }
    
    if ( $post_type == 'event' ) {
		$event_id = $post_id;
	}
    if ( is_dev_site() ) {
        //$info .= "post_id: $post_id; post_type: $post_type; event_id: $event_id; <br />";
    }
    
    if ($event_id === null) {
        
        // If no event has been designated, get the day number for today's date
        $day_num = date('j');
        $info .= "No event specified (post_type: $post_type).<br />";
        
    } else {
        
        // Get the event start date
        $event_post_obj   = get_post( $event_id );
        
        // EM Event
        $event_date = get_post_meta( $event_id, '_event_start_date', true ); 
        
        // Extract day number from date @ time string
        $day_num = date( 'j', strtotime($event_date) ); // or 'd'? (w/ leading zeros)
        //$day_num = (int) substr( $event_date, strpos($event_date, " "), strpos($event_date, "@") - strpos($event_date, " ") -1 ); // tribe events version
        
        if ( is_dev_site() ) {
            //$info .= "<!-- day_num: $day_num; event_date: $event_date -->"; // tft
        }
    }
    
    if (! (1 <= $day_num) && ($day_num <= 31) ) { return $info; }
    
    //$info .= "Psalms for Day ".$day_num.": ";
    
    // Find post with day_num = event day or today's day, if no event has been selected
    $args = array(
        'post_type'   => 'psalms_of_the_day',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key'     => 'day_num',
                'value'   => $day_num
            )
        )
    );
    $psalms_of_the_day = new WP_Query( $args );
    $potd_id = $psalms_of_the_day->ID;
    $mp_psalms = the_field( 'mp_psalms', $potd_id );
    $ep_psalms = the_field( 'ep_psalms', $potd_id );
    
    if ($service === "morning_prayer") {
        $info .= $mp_psalms; // return Psalms for Morning Prayer
    } else if ($service === "evening_prayer") {
        $info .= $ep_psalms; // return Psalms for Morning Prayer
    } else {
        $info .= $mp_psalms.", ".$ep_psalms; // return ALL Psalms of the Day
    }
    
    return $info;
    
}


?>