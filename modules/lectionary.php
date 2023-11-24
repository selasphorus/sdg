<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** CPT: LITURGICAL DATE ***********/

// Get liturgical date records matching given date or date range (given month & year)
function get_lit_dates ( $args ) {

	// TODO: Optimize this function! Queries run very slowly. Maybe unavoidable given wildcard situation. Consider restructuring data?
	
	// init
	$litdates = array();
	$litdate_posts = array();
	$date = null;
	$year = null;
	$month = null;
	$day_titles_only = false;
	$start_date = null;
	$end_date = null;
	
	$ts_info = "\n<!-- get_lit_dates -->\n";
	
	// Set vars
	// TODO: remember how to do this more efficiently, setting defaults from array or along those lines...
	
	if ( isset($args['date']) ) {
	
		$date = $args['date'];
		// TODO: deal w/ possibility that date is passed in the wrong format >> convert it to YYYY-MM-DD
		//if str contains commas? if first four digits not a number? ... date('Y-m-d',strtotime($date))
		$start_date = $end_date = $date;
		$year = substr($date,0,4);
		$month = substr($date,5,2);
		
	} else {
	
		if ( isset($args['year']) ) { $year = $args['year']; }
		if ( isset($args['month']) ) { $month = $args['month']; }
		if ( isset($args['day_titles_only']) ) { $day_titles_only = $args['day_titles_only']; }	
		
		if ( empty($year) ) {
			// For now, default to current year
			// TODO: If month is set but not year, attempt to find all lit dates which may occur in the given month, taking into account variability that may range over multiple months
			$year = date('Y');
		}
		
		if ( empty($month) ) {
			$start_date = $year."-01-01";
			$end_date = $year."-12-31";
		} else {
			$start_date = $year."-".$month."-01";
			$end_date = $year."-".$month."-31";
			// TODO: set last day depending on number of days in month, not default to 31 (necessary?)
		}
		
	}
	
	$ts_info .= "<!-- start_date: '$start_date'; end_date: '$end_date'; year: '$year'; month: '$month' -->\n"; // tft
    
    // Loop through all dates in range from start to end
    $start = strtotime($start_date);
    $end = strtotime($end_date);

	while ($start <= $end) {
		
		// Build  query to search for any liturgical dates that match, whether dates are fixed, calculated, or manually assigned
		// TODO: consider running separate queries for fixed, assigned, calc?
		// 
    
		$litdate_args = array(
			'post_type'		=> 'liturgical_date',
			'post_status'   => 'publish',
			'orderby'  => array( 'meta_value' => 'DESC', 'ID' => 'ASC' ), // would title be better than ID as fallback?
			//'orderby'		=> 'meta_value',
			//'order'			=> 'DESC',
			'meta_key' 		=> 'day_title',
		);
		
		$meta_query = array();
		$meta_query['relation'] = 'AND';
	
		if ( $day_titles_only == true ) {
			$meta_query[] = array(
				'key'		=> 'day_title',
				'compare'	=> '=',
				'value'		=> '1',
			);
		}
        
        // Prep meta_sub_query which will check for various kinds of date matches
        $meta_sub_query = array( 'relation'  => 'OR' );
        
        // Add meta_query components for fixed dates
        $fixed_date_str = date("F d", $start ); // day w/ leading zeros
        //$ts_info .= "<!-- fixed_date_str: '$fixed_date_str' -->\n"; // tft
        $meta_sub_query[] = array(
			'key'		=> 'fixed_date_str',
			'compare'	=> '=',
			'value'		=> $fixed_date_str,
		);
		
		// Add alt component in case day num has been stored without leading zero
		$day_num = intval(date("j", $start ));
        if ( $day_num < 10 ) {
            //$ts_info .= "<!-- day_num: '$day_num' -->\n"; // tft
            $fixed_date_str_alt = date("F j", $start ); // day w/ out leading zeros
            //$ts_info .= "<!-- fixed_date_str_alt: '$fixed_date_str_alt' -->\n"; // tft
            $meta_sub_query[] = array(
				'key'		=> 'fixed_date_str',
				'compare'	=> '=',
				'value'		=> $fixed_date_str_alt,
			);
        }
        
        // Format date_str
        $full_date_str = date("Y-m-d", $start );        
        $ts_info .= "<!-- full_date_str: '$full_date_str' -->\n"; // tft
        
        // Add meta_query components for date calculations and assignments        
		$meta_sub_query[] = array(
			'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> $full_date_str,
		);
		$meta_sub_query[] = array(
			'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> $full_date_str,
		);
		// The following parameters can be phased out eventually once the DB is updated to standardize the date formats
		$meta_sub_query[] = array(
			'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
		);
		$meta_sub_query[] = array(
			'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
		);
        
        // Add sub_query to meta_query
    	$meta_query[] = $meta_sub_query;
    	
		$litdate_args['meta_query'] = $meta_query;
	
		// Run the query
		//$ts_info .= "<!-- litdate_args: <pre>".print_r($litdate_args, true)."</pre> -->"; // tft
		$arr_posts = new WP_Query( $litdate_args );
		$litdate_posts[$full_date_str] = $arr_posts->posts;
        
		// Go to the next day
		$start = strtotime("+1 day", $start);
	}
	
	
	// TODO: deal w/ replacement_date option (via Date Assignments field group)
    // date_assignments: "Use this field to override the default Fixed Date or automatic Date Calculation."
    // replacement_date: "Check the box if this is the ONLY date of observance during the calendar year in question. Otherwise the custom date assignment will be treated as an ADDITIONAL date of observance."
    
    // WIP/TODO: reorder the litdates by category->priority before returning the array?
    /*foreach ( $litdate_posts as $post ) {
    	$display_dates = get_display_dates ( $post->ID, $year )
    }
    */
    
    $litdates['troubleshooting'] = $ts_info;
    $litdates['posts'] = $litdate_posts;
    
    return $litdates;
	
}


// Lit Dates overview
add_shortcode('list_lit_dates', 'get_lit_dates_list');
function get_lit_dates_list( $atts = [], $content = null, $tag = '' ) {

	$info = "\n<!-- get_lit_dates_list -->\n";
    
    $args = shortcode_atts( array(
      	'year'   => date('Y'),
        'month' => null,
    ), $atts );
    
    // Extract
	extract( $args );
	
    // Set year
    if ( $year == "this_year" ) {
    	$month = date('Y');
    } else if ( $year == "next_year" ) {
    	$month = date('Y')+1;
    }
    
    // Set month
    if ( $month == "this_month" ) {
    	$month = date('m');
    } else if ( $month == "next_month" ) {
    	$month = date('m')+1;
    }
    
    // Just in case?
    $year = (int) $year;
    $month = (int) $month;
    
    // Get litdate posts according to date
    $litdate_args = array( 'year' => $year, 'month' => $month );
    $litdates = get_lit_dates( $litdate_args );
    
    $posts = $litdates['posts'];
    $info .= $litdates['troubleshooting'];
    
    foreach ( $posts AS $date => $date_posts ) {
        
        if ( !empty($date_posts)) {
        	$info .= '<a href="/events/'.date('Y-m-d',strtotime($date)).'/" target="_blank">';
        	$info .= date('l, F j, Y',strtotime($date));
        	$info .= "</a><br />";
        }
        //$info .= print_r($date_posts, true);
        
        // TODO: order the date_posts? (according to priority &c.) -- or should that happen via get_lit_dates
        
        $num_day_titles = 0;
        
        $i = 1;
        foreach ( $date_posts AS $lit_date ) {
        
        	//$info .= print_r($lit_date, true);
        	$litdate_id = $lit_date->ID;
        	$classes = "litdate";
        	$day_title = get_post_meta($litdate_id, 'day_title', true);
        	if ( $day_title == "1" ) { 
        		$classes .= " nb";
        		$num_day_titles++;
        		if ( $num_day_titles > 1 ) {
					//$classes .= " conflict";
				}
        	}
        	
        	//
			$info .= '<span class="'.$classes.'">';
			$info .= '<a href="'.get_permalink($litdate_id).'" class="smaller" target="_blank">';
			$info .= "[".$litdate_id."] ";
			$info .= '</a>';
			$info .= '</span>';
			$info .= $lit_date->post_title;			
			$info .= ' >> <a href="'.get_edit_post_link($litdate_id).'" class="subtle" target="_blank">Edit</a> << ';
			//$info .=" (".print_r($day_title, true).")";
			// TODO: determine/show if this is calc date, override date, &c.
        	//
        	$terms = get_the_terms( $litdate_id, 'liturgical_date_category' );
            //$info .= "<!-- terms: ".print_r($terms, true)." -->"; // tft
            if ( $terms ) {
            	$info .= '&nbsp;<span class="terms smaller green">';
                //$info .= " >> ";
                $i = 1;
                foreach ( $terms as $term ) {
                	// TODO: first, reorder the litdates by priority; THEN build the list
                    $priority = get_term_meta($term->term_id, 'priority', true);
                    //$info .= "<!-- term: ".$term->slug." :: priority: ".$priority." -->"; // tft
                    //$info .= "term: ".print_r($term, true)." "; // tft
                    $info .= $term->name;
                    if ( !empty($priority) ) { $info .= " (".$priority.")"; } else { $info .= " (#?)"; }
                    //$info .= "&nbsp;";
                    if ( $i >= 1 && $i < count($terms) && count($terms) > 1 ) { $info .= "; "; } //else { $info .= "[$i]"; }
                    $i++;
                }
                $info .= '</span>';	
            }
            //$info .= implode(" ",$terms);
            //
			$info .= '<br />';
			/*if ( have_rows('date_assignments', $litdate_id) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
				while ( have_rows('date_assignments', $litdate_id) ) : the_row();
					$replacement_date = get_sub_field('replacement_date'); // ACF fcn
					if ( $replacement_date == "1") {}
				endwhile;
			} // end if*/
			
			$i++;
        }
        
		/*$litdate_post_id = $litdate_post->ID;
		$info .= "[".$litdate_post_id."] ".$litdate_post->post_title."<br />"; // tft
		
		// Get date_type (fixed, calculated, assigned)
		$date_type = get_post_meta( $litdate_post_id, 'date_type', true );
		$info .= "date_type: ".$date_type."<br />"; // tft*/
		
		if ( !empty($date_posts)) { $info .= "<br />"; }
	}
    
    return $info;
    
} 

function get_cpt_liturgical_date_content( $post_id = null ) {
	
	// init
	$info = "";
	if ($post_id === null) { $post_id = get_the_ID(); }
	$litdate_id = $post_id;
	
	$info .= '<!-- cpt_liturgical_date_content -->';
    $info .= '<!-- litdate_id: '.$litdate_id.' -->';
    
    if ( have_rows('date_assignments', $litdate_id) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
		while ( have_rows('date_assignments', $litdate_id) ) : the_row();
			$date_assigned = get_sub_field('date_assigned');
			$replacement_date = get_sub_field('replacement_date');
			if ( $replacement_date == "1") {}
		endwhile;
	} // end if
	
	return $info;
}

// WIP
// A liturgical date may correspond to multiple dates in a year, if dates have been both assigned and calculated,
// or if a date has been assigned to replace the fixed date
// The following function determines which of the date(s) is active -- could be multiple, if date assigned is NOT a replacement_date
function get_display_dates ( $post_id = null, $year = null ) {
	
	$info = "";
	$dates = array();
	$arr_info = array();
	$fixed_date_str = ""; 
	
	// Get date_type (fixed, calculated, assigned)
    $date_type = get_post_meta( $post_id, 'date_type', true );
    $info .= "<!-- litdate post_id: ".$post_id."; date_type: ".$date_type." -->";
         
	// Get calculated or fixed date for designated year
	if ( $date_type == "fixed" ) {
		if ( !$fixed_date_str = get_field( 'fixed_date_str', $post_id ) ) { 
			$info .= "<!-- No fixed_date_str found. -->";
		} else {
			$info .= "<!-- fixed_date_str: ".$fixed_date_str." -->";
			$formatted_fixed_date_str = date("Y-m-d",strtotime($fixed_date_str));
			$info .= "<!-- formatted_fixed_date_str: ".$formatted_fixed_date_str." -->";
			$dates[] = $formatted_fixed_date_str;
		}		
	} else {
		// For variable dates, get date calculations
		// TODO: run a query instead to find rows relevant by $year -- it will be more efficient than retrieving all the rows
		if ( have_rows('date_calculations', $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
			while ( have_rows('date_calculations', $post_id) ) : the_row();
				$date_calculated = get_sub_field('date_calculated'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
				$year_calculated = substr($date_calculated, 0, 4);
				if ( $year_calculated == $year ) {
					$dates[] = $date_calculated;
				}
			endwhile;
		} // end if
	}
	
	// get date assignments to see if there is a replacement_date to override the fixed_date_str
	// TODO: run a query instead to find rows relevant by $year -- it will be more efficient than retrieving all the rows
	if ( have_rows('date_assignments', $post_id) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
		while ( have_rows('date_assignments', $post_id) ) : the_row();
			$date_assigned = get_sub_field('date_assigned');
			$replacement_date = get_sub_field('replacement_date');
			$year_assigned = substr($date_assigned, 0, 4);
			$info .= "<!-- date_assigned: ".$date_assigned." (".$year_assigned.") -->";
			if ( $year_assigned == $year ) {
				if ( $replacement_date == "1" ) {
					if ( $date_assigned != $fixed_date_str ) {
						$info .= "<!-- replacement_date date_assigned: ".$date_assigned." overrides fixed_date_str ".$fixed_date_str." for year ".$year." -->";
						$fixed_date_str = $date_assigned;
						$dates = array($fixed_date_str); // Since this is a replacement_date it should be the only one displayed in the given year -- don't add it to array; replace the array
						break;
					}
				} else {
					$dates[] = $date_assigned;
				}
			}
		endwhile;
	} // end if
	
	$arr_info['info'] = $info;
	$arr_info['dates'] = $dates;
	return $arr_info;
	
}

// WIP!
// Check to see if litdate has been assigned to another date to override the given date
// This function is used to check litdates that have already been found to match the given date, via assignment or calculation
function show_litdate_on_date( $litdate_id = null, $date_str = null ) { // TODO set default: date('Y-m-d')

	$info = "";
	//
	$info .= "<!-- litdate_id: $litdate_id -->";
	
	// Get date assignments; check to see if one is designated as a replacement_date that should negate the date match
	if ( have_rows('date_assignments', $litdate_id) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
		while ( have_rows('date_assignments', $litdate_id) ) : the_row();
			$replacement_date = get_sub_field('replacement_date'); // ACF fcn
			if ( $replacement_date == "1") {
				// TODO: get year of date_assigned and check it against full_date_str only if years match
				$date_assigned = get_sub_field('date_assigned');
				$year_assigned = substr($date_assigned, 0, 4);
				$year_to_match = substr($date_str, 0, 4);
				$info .= "<!-- replacement_date: ".$replacement_date."; date_assigned: ".$date_assigned."; date_str: ".$date_str." -->";
				if ( $date_assigned == $year_to_match ) {
					if ( $date_assigned != $date_str ) {
						// Don't show this date -- override in effect
						$info .= "<!-- date_assigned NE date_str (".$date_str.") >> don't show title -->";
						return false;
					} else {
						$info .= "<!-- date_assigned == date_str (".$date_str.") >> DO show title -->";
						return true;
					}
				}
			}
		endwhile;
	} // end if
	
	return true;
}

// Day Titles
add_shortcode('day_title', 'get_day_title');
function get_day_title( $atts = [], $content = null, $tag = '' ) {

    // TODO: Optimize this function! Queries run very slowly. Maybe unavoidable given wildcard situation. Consider restructuring data?
	$info = "";
	$ts_info = "\n<!-- get_day_title -->\n";
	$hide_day_titles = 0;
    
    $args = shortcode_atts( array(
		'post_id'   => get_the_ID(),
		'series_id' => null,
		'the_date'  => null,
	), $atts);
    
    // Extract
	extract( $args );

    if ( $post_id === null ) { $post_id = get_the_ID(); }
    $ts_info .= "<!-- post_id: ".$post_id." -->\n"; // tft
    if ( $series_id ) { $ts_info .= "<!-- series_id: ".$series_id." -->\n"; }
    
    // PROBLEM! TODO/WIP -- figure out why event listings accessed via pagination links send un-parseable date string to this function. It LOOKS like a string, but commas aren't recognized as commas, &c.
    // Make sure the date hasn't been returned enclosed in quotation marks
    // e.g. "Sunday, February 5, 2023"
    $ts_info .= "var the_date is of type: ".gettype($the_date)."<br />";
    //$ts_info .= "var_export of the_date: ".var_export($the_date,true)."<br />";
    //
    if ( strpos($the_date, '"') !== false || strpos($the_date, "'") !== false ) { $ts_info .= "[1] the_date contains quotation marks<br />"; } else { $ts_info .= "[1] the_date contains NO quotation marks<br />"; }
    //
    //$the_date = filter_var($the_date, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_SANITIZE_STRING); // FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_HIGH //$the_date = filter_var($the_date, FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH);
    //$the_date = (string) $the_date;
    //$the_date = preg_replace('/[^\PC\s]/u', '', $the_date);
    //$the_date = preg_replace('/[\x00-\x1F\x7F]/', '', $the_date);
    $the_date = preg_replace('/[[:cntrl:]]/', '', $the_date);
    //
    $the_date = htmlspecialchars_decode($the_date);
    $the_date = html_entity_decode($the_date);
    $the_date = strip_tags($the_date);
    $the_date = stripslashes($the_date);
    //
    
    if ( strpos($the_date, '"') !== false || strpos($the_date, "'") !== false ) { $ts_info .= "[2] the_date contains quotation marks<br />"; } else { $ts_info .= "[2] the_date contains NO quotation marks<br />"; }
    
    // Remove quotation marks
    $the_date = str_replace('\"', '', $the_date);
    $the_date = str_replace("\'", '', $the_date);
    $the_date = str_replace('"', '', $the_date);
    $the_date = str_replace("'", "", $the_date);
    
    
    //$ts_info .= "string cleanup attempted via filter_var, preg_replace, htmlspecialchars_decode, html_entity_decode, strip_tags, stripslashes, str_replace...<br />";
    //
    $ts_info .= "var_export of revised the_date: ".var_export($the_date,true)."<br />";
    
    //if (preg_match_all("/[,\s\n\t]+/i", $the_date, $matches)) { $ts_info .= "preg_match_all: ".print_r($matches, true)."<br />"; }
    
    //*/
    //if ( strpos($the_date, ',') !== false || strpos($the_date, ",") !== false ) { $ts_info .= "the_date contains one or more commas<br />"; } else { $ts_info .= "the_date contains NO commas<br />"; }
    //if ( strpos($the_date, ' ') !== false ) { $ts_info .= "the_date contains one or more spaces<br />"; } else { $ts_info .= "the_date contains NO spaces<br />"; }
    //if (preg_match_all("/[A-Za-z]+/i", $the_date, $matches)) { $ts_info .= "preg_match_all alpha: <pre>".print_r($matches, true)."</pre><br />"; } else { $ts_info .= "preg_match_all alpha: No matches<br />"; }
    //if (preg_match_all("/[0-9]+/i", $the_date, $matches)) { $ts_info .= "preg_match_all numeric: <pre>".print_r($matches, true)."</pre><br />"; } else { $ts_info .= "preg_match_all numeric: No matches<br />"; }
    //if (preg_match_all("/[^A-Za-z0-9]+/i", $the_date, $matches)) { $ts_info .= "preg_match_all NOT alpha-numeric: <pre>".print_r($matches, true)."</pre><br />"; } else { $ts_info .= "preg_match_all NON-alphanumeric: No matches<br />"; }
    
    
    $date_bits = explode(", ",$the_date);
    $ts_info .= "date_bits: ".print_r($date_bits,true)."<br />";
    $ts_info .= "<!-- the_date: ".$the_date." -->\n";
    $ts_info .= "the_date: ".$the_date."<br />";
    if ( strtotime($the_date) ) { $ts_info .= "strtotime(the_date): ".strtotime($the_date)."<br />"; } else { $ts_info .= '<span class="error">strtotime(the_date) FAILED</span><br />'; }
    //$ts_info .= "<!-- the_date: [".$the_date."] -->\n"; // tft
    
    //$info .= $ts_info; // tft
    
    // Check to see if day titles are to be hidden for the entire event series, if any
    if ( $series_id ) { 
    	$hide_day_titles = get_post_meta( $series_id, 'hide_day_titles', true );
    }
    
    // If there is no series-wide ban on displaying the titles, then should we display them for this particular post?
    if ( $hide_day_titles == 0 ) {
    	$hide_day_titles = get_post_meta( $post_id, 'hide_day_titles', true );
    }
    //$ts_info .= "<!-- hide_day_titles: [$hide_day_titles] -->";
    
    if ( $hide_day_titles == 1 ) { 
        $ts_info .= "<!-- hide_day_titles is set to true for this post/event -->";
        return $info;
    } else {
        //$ts_info .= "<!-- hide_day_titles is not set or set to zero for this post/event -->";
    }
    
	if ( $the_date == null ) {
        
        $ts_info .= "<!-- the_date is null -- get the_date -->"; // tft
		
        // If no date was specified when the function was called, then get event start_date or sermon_date OR ...
        if ( $post_id === null ) {
            
            return "<!-- no post -->";
            
        } else {
            
            $post = get_post( $post_id );
            $post_type = $post->post_type;
            $ts_info .= "<!-- post_type: ".$post_type." -->"; // tft

            if ( $post_type == 'event' ) {
                
                $date_str = get_post_meta( $post_id, '_event_start_date', true );
                $the_date = strtotime($date_str);
                
            } else if ( $post_type == 'sermon' ) {
                
                $ts_info .= "<!-- sermon -->";
                $the_date = the_field( 'sermon_date', $post_id );
                //if ( get_field( 'sermon_date', $post_id )  ) { $the_date = the_field( 'sermon_date', $post_id ); }
                
            } else {
                //$ts_info .= "post_id: ".$post_id."<br />"; // tft
                //$ts_info .= "post_type: ".$post_type."<br />"; // tft
            }
        }
        
	}    
    
    if ( $the_date == null ) {
        
        // If still no date has been found, give up.
        $ts_info .= "<!-- no date available for which to find day_title -->\n"; // tft
        $info .= '<div class="troubleshooting">'.$ts_info.'</div>';
        return $info;
        
    }
    
    // Get litdate posts according to date
    $date_str = date("Y-m-d", strtotime($the_date));
    $ts_info .= "<!-- date_str: ".$date_str." -->\n"; // tft
    $litdate_args = array( 'date' => $date_str, 'day_titles_only' => true); //$litdate_args = array( 'date' => $the_date, 'day_titles_only' => true);
    $litdates = get_lit_dates( $litdate_args );
    $year = substr($date_str, 0, 4); // for checking display_dates later in the fcn
    //
    if ( isset($litdates['posts'][$date_str]) ) { 
    	$litdate_posts = $litdates['posts'][$date_str];
	} else if ( isset($litdates['posts']) ) {
		$litdate_posts = $litdates['posts'];
		$ts_info .= "<!-- litdates['posts'][$date_str] not set -->";
		//$ts_info .= "<!-- litdates['posts']: <pre>".print_r($litdates['posts'], true)."</pre> -->"; // tft
	} else {
		$litdate_posts = array(); // empty
	}
    if ( is_array($litdate_posts) ) { $num_litdate_posts = count($litdate_posts); } else { $num_litdate_posts = 0; }
    //$ts_info .= "<!-- SQL-Query: <pre>{$arr_posts->request}</pre> -->"; // tft
    $ts_info .= "<!-- num_litdate_posts: ".$num_litdate_posts." -->"; // tft
    //if ( $litdates['ts_info'] ) { $ts_info .= $litdates['ts_info']; }
    
    // If some posts were retrieved for dates calculated and/or assigned
    
    $litdate_id = null; // init
    
    // WIP
    if ( $num_litdate_posts == 0 ) {    
    	$ts_info .= "<!-- litdate_args: <pre>".print_r($litdate_args, true)."</pre> -->";
    }
    
    if ( $num_litdate_posts > 0 ) {
        
        // ... multiple matches? prioritize... pick one and then fetch the ONE litdate, collect, &c.
        
        $litdates = array();
        
        // ...loop through, check for holy days vs saints and martyrs... check for override settings...
        foreach ( $litdate_posts AS $litdate_post ) {
            
            $litdate_id = $litdate_post->ID;
            $ts_info .= "<!-- litdate_post->ID: ".$litdate_id." -->"; // tft
            
            // Get the actual display_dates for the given litdate, to make sure the date in question hasn't been overridden			
			$display_dates_info = get_display_dates ( $litdate_id, $year );
			$ts_info .= $display_dates_info['info'];
			$display_dates = $display_dates_info['dates'];
			$ts_info .= "<!-- display_dates: <pre>".print_r($display_dates, true)."</pre> -->";
			if ( !in_array($date_str, $display_dates) ) {
				$ts_info .= "<!-- date_str: ".$date_str." is not one of the display_dates for this litdate. -->";
				// Therefore don't show it.
				$litdate_id = null;
				continue;
			}
            
            // Get date_type (fixed, calculated, assigned)
            $date_type = get_post_meta( $litdate_id, 'date_type', true );
            $ts_info .= "<!-- date_type: ".$date_type." -->"; // tft
            
            // Get category/priority
            $terms = get_the_terms( $litdate_id, 'liturgical_date_category' );
            //$ts_info .= "<!-- terms: ".print_r($terms, true)." -->"; // tft
            
            $priority = 999;
            if ( $terms ) {
                
                foreach ( $terms as $term ) {
                    $term_priority = get_term_meta($term->term_id, 'priority', true);
                    $ts_info .= "<!-- term: ".$term->slug." :: term_priority: ".$term_priority." -->"; // tft

                    if ( !empty($term_priority) ) {
                    	if ( $term_priority < $priority ) { // top priority is lowest number
                    		$priority = $term_priority;
                        	$ts_info .= "<!-- NEW priority: ".$priority." -->"; // tft
                        } else if ( isset($top_priority) && $term_priority == $top_priority ) {
                        	$ts_info .= "<!-- term_priority is same as priority -->"; // tft
                    	} else {
                    		$ts_info .= "<!-- term_priority is higher than priority -->"; // tft
                    	}                        
                    } else {
                    	$ts_info .= "<!-- term_priority is not set for term ".$term->slug." -->";
                    }
                }
                
            }
            
            $ts_info .= "<!-- priority: ".$priority." -->"; // tft
            $key = $priority."-".$litdate_id; // this will cause sort by priority num, then by litdate_id
            $litdates[$key] = $litdate_id;
            //$litdates[$top_priority] = $litdate_post_id;
            //
            
        }
        
        if ( count($litdates) > 0 ) {
        
        	$ts_info .= "<!-- litdates: ".print_r($litdates, true)." -->"; // tft
        	uksort($litdates, sdg_arr_sort( 'key', null, 'ASC' ));
        	$ts_info .= "<!-- litdates sorted: ".print_r($litdates, true)." -->"; // tft
       
			// Get first item in the associative array -- that's the one to use because it has the lowest priority number and therefore is most important
			//$firstKey = array_key_first($array);
		
			$top_key = array_key_first($litdates);
			$ts_info .= "<!-- top_key: ".$top_key." -->"; // tft
			$litdate_id = $litdates[$top_key];
			$ts_info .= "<!-- litdate_id: ".$litdate_id." -->"; // tft
			
        }
    }
    
    // 
    if ( $litdate_id ) {
        
        // TODO: extract this out as a separate small function to check the actual display date for this litdate for the year in question
        
        $show_title = show_litdate_on_date( $litdate_id, $date_str );
        
        if ( $show_title == true ) {
        
        	$litdate_title = get_the_title( $litdate_id );
			$litdate_content = get_the_content( null, false, $litdate_id ); // get_the_content( string $more_link_text = null, bool $strip_teaser = false, WP_Post|object|int $post = null )
			$collect_text = ""; // init

			$collect_args = array(
				'post_type'   => 'collect',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key'     => 'related_liturgical_date',
						'compare' 	=> 'LIKE',
						'value' 	=> '"' . $litdate_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
					)
				)
			);
			$collect = new WP_Query( $collect_args );
			if ( !empty($collect->posts) ) { $collect_post = $collect->posts[0]; } else { $collect_post = null; }
			if ( $collect_post ) { $collect_text = $collect_post->post_content; }
			
			// TODO/atcwip: if no match by litdate_id, then check propers 1-29 by date (e.g. Proper 21: "Week of the Sunday closest to September 28")
		
			// If there's something other than the title available to display, then display the popup link
			// TODO: set width and height dynamically based on browser window dimensions
			$width = '650';
			$height = '450';
			
			if ( !empty($collect_text) ) {

				$info .= '<a href="#!" id="dialog_handle_'.$litdate_id.'" class="calendar-day dialog_handle">';
				$info .= $litdate_title;
				$info .= '</a>';
				$info .= '<br />';
				$info .= '<div id="dialog_content_'.$litdate_id.'" class="calendar-day-desc dialog">';
				$info .= 		'<h2 autofocus>'.$litdate_title.'</h2>';
				if ( is_dev_site() ) {
					//$info .= 		$litdate_content;
				}
				if ($collect_text !== null) {
					$info .= 	'<div class="calendar-day-collect">';
					//$info .= 		'<h3>Collect:</h3>';
					$info .= 		'<p>'.$collect_text.'</p>';
					$info .= 	'</div>';
				}
				$info .= '</div>'; //<!-- /calendar-day-desc -->

			} else {
				//$ts_info .= "<!-- no collect_text found -->";
				//$ts_info .= "<!-- collect_args: <pre>".print_r($collect_args, true)."</pre> -->";
				//$ts_info .= "<!-- collect_post: <pre>".print_r($collect_post, true)."</pre> -->";
				// If no content or collect, just show the day title
				$info .= '<span id="'.$litdate_id.'" class="calendar-day">'.$litdate_title.'</span><br />';
			}
        }
        
    } else {
		
        $ts_info .= "<!-- no litdate found for display -->";
		//$ts_info .= "<!-- params: <pre>".print_r($params, true)."</pre> -->";
        
	}
	
	$info .= get_special_date_content( $the_date );
	//$info .= $ts_info;
	
	return $info;
	
}

// Function(s) to calculate variable liturgical_dates

// Translate the date calculation string into components that can be used to do date math, and then do that math to calculate the date
function calc_date_from_str( $year = null, $date_calculation_str = null, $verbose = false ) {
	
	// Abort if date_calculation_str or year is empty
	if ( empty($date_calculation_str) || empty($year) ) { return false; }
	
	// Init vars
	$calc = array();
	$info = "";
	$indent = "&nbsp;&nbsp;&nbsp;&nbsp;"; // TODO: define this with global scope for all plugin functions
	
	$info .= '<h3>calc_date_from_str</h3>';
	$info .= '<div class="code">';
	if ( $verbose == "true" ) { $info .= "year: ".$year."<br />"; }
	
	// Init vars
	$liturgical_bases = array('advent' => 'advent_sunday_date', 'christmas' => 'December 25', 'epiphany' => 'January 6', 'ash wednesday' => 'ash_wednesday_date', 'lent' => 'ash_wednesday_date', 'easter' => 'easter_date', 'ascension day' => 'ascension_date', 'pentecost' => 'pentecost_date' );
    $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
    $boias = array('before', 'of', 'in', 'after'); // before/of/in/after the basis_date/season? 
    //
    list( $calc_basis, $calc_basis_field, $calc_month, $calc_date, $calc_formula ) = array( "", "", "", "", "" );
    list( $basis_date_str, $basis_date, $basis_date_weekday ) = array( "", "", "" );
    $calc_weekday = $calc_boia = $calc_interval = array(); // in case more than one match is found
	//
	$calc_components = explode(" ", $date_calculation_str);
	if ( $verbose == "true" ) { $info .= "calc_components: ".print_r($calc_components,true)."<br />"; }
	
	// Loop through all the components of the exploded date_calculation_str and determine component type
	// WIP -- why do this?
	foreach ( $calc_components as $component ) {
		$component_info = "";
		if ( array_key_exists($component, $liturgical_bases) ) {
			$component_info .= $indent."component '".$component."' is liturgical_base<br />";
		} else if ( in_array(ucfirst($component), $months) ) {
			$component_info .= $indent."component '".$component."' is month<br />";
		} else if ( in_array($component, $weekdays) ) {
			$component_info .= $indent."component '".$component."' is weekday<br />";
		} else if ( in_array($component, $boias) ) {
			$component_info .= $indent."component '".$component."' is boia<br />";
		} else if ( preg_match('/first|second|[0-9]+/', $component) ) {
			$component_info .= $indent."component '".$component."' is numeric/intervalic<br />";
			//$component_info .= $indent."component '".$component."' is numeric/intervalic --> matches: ".print_r($matches,true)."<br />";
		//} else if () { // first, last, second....
		} else if ($component == "the" ) { // wip
			$component_info .= $indent."component '".$component."' is expendable<br />";
		} else {
			$component_info .= $indent."component '".$component."' is ???<br />";
		}
		if ( $verbose == "true" ) { $info .= $component_info; }
	}
	
	// WIP!!!
	
	// calc_basis
	// Get the liturgical date info upon which the calculation should be based (basis extracted from the date_calculation_str)
	foreach ( $liturgical_bases AS $basis => $basis_field ) {
		if (stripos($date_calculation_str, $basis) !== false) {
			$calc_basis = $basis;
			$calc_basis_field = $basis_field;
		}
	}
	
	if ( !empty($calc_basis) ) {            
		if ( $verbose == "true" ) { $info .= "liturgical calc_basis: $calc_basis // $calc_basis_field<br />"; } // $info .= "calc_basis_field: $calc_basis_field -- "; // tft            
	} else {
		if ( $verbose == "true" ) { $info .= "No liturgical calc_basis found.<br />"; }
	}
        
	// Find the liturgical_date_calc post for the selected year
	// (liturgical_date_calc records contain the dates for Easter, Ash Wednesday, &c. per year)
	$wp_args = array(
		'post_type'   => 'liturgical_date_calc',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'fields'	=> 'ids',
		'meta_query' => array(
			array(
				'key'     => 'litdate_year',
				'value'   => $year.'-01-01'
			)
		)
	);
	$query = new WP_Query( $wp_args );
	$posts = $query->posts;    
	if ( count($posts) > 0 ) {
		$liturgical_date_calc_id = $posts[0];
		if ( $verbose == "true" ) { $info .= "liturgical_date_calc_id: $liturgical_date_calc_id<br />"; }
	} else {
		if ( $verbose == "true" ) { $info .= "No matching liturgical_date_calc_post for wp_args ".print_r($wp_args,true)."<br />"; }
		$liturgical_date_calc_id = null;
	}
        
	// Get the basis date in the given year, from the Liturgical Date Calculations CPT (liturgical_date_calc)
	if ( $calc_basis == 'christmas' ) {
		$basis_date_str = $year."-12-25";          
	} else if ( $calc_basis == 'epiphany' ) {                
		$basis_date_str = $year."-01-06";
		$num_sundays_after_epiphany = get_post_meta( $liturgical_date_calc_id, 'num_sundays_after_epiphany', true);
	} else {
		if ( !empty($liturgical_date_calc_id) && !empty($calc_basis_field) ) {
			$basis_date_str = get_post_meta( $liturgical_date_calc_id, $calc_basis_field, true);
		}
	}
    
	// Get related dates, where relevant
	// +~+~+~+~+~+~+~+~+~+~+
	
	// 1. Advent Sunday date
	if ( $calc_basis == "epiphany" || $calc_basis == "advent" || $calc_basis == "pentecost" ) {
		$advent_sunday_date = get_post_meta( $liturgical_date_calc_id, 'advent_sunday_date', true);
		if ( $verbose == "true" ) { $info .= "advent_sunday_date: ".$advent_sunday_date."<br />"; }
	}
	
	// 2. Ash Wednesday date
	if ( $calc_basis == "epiphany" || $calc_basis == "lent" ) {
		$ash_wednesday_date = get_post_meta( $liturgical_date_calc_id, 'ash_wednesday_date', true);
		if ( empty($ash_wednesday_date) ) { 
			$info .= $indent."No ash_wednesday_date found for liturgical_date_calc_id: $liturgical_date_calc_id<br />";
		} else {
			if ( $verbose == "true" ) { $info .= "ash_wednesday_date: ".$ash_wednesday_date."<br />"; }
		}
	}
		
	// If no basis date string has yet been established, then default to January first of the designated year
	if ( $basis_date_str == "" ) { $basis_date_str = $year."-01-01"; }
	if ( $verbose == "true" ) { $info .= "basis_date_str: $basis_date_str<br />"; } // '<span class="notice">'.</span> // ($calc_basis // $calc_basis_field)
        
	// Get the basis_date from the string version
	$basis_date = strtotime($basis_date_str);
	$basis_date_weekday = strtolower( date('l', $basis_date) );
	
	if ( $verbose == "true" ) { $info .= "basis_date: $basis_date_str ($basis_date_weekday)<br />"; } // .'<span class="notice">'.'</span>' //  ($calc_basis // $calc_basis_field)
        
	// Check to see if the date to be calculated is in fact the same as the base date
	if ( strtolower($date_calculation_str) == $calc_basis ) { // Easter, Christmas, Ash Wednesday", &c.=
		
		$calc_date = $basis_date;
		$info .= "date to be calculated is same as basis_date.<br />";
		
	} else {
            
        // ** Extract components of date_calculation_str & calculate date for $year
        
		// Does the date to be calculated fall before/after/of/in the basis_date/season?
		foreach ( $boias AS $boia ) {
			if ( preg_match_all('/'.$boia.'/', $date_calculation_str, $matches, PREG_OFFSET_CAPTURE) ) {
				$info .= $indent."boia '$boia' found in date_calculation_str<br />";
				$calc_boia[] = strtolower($boia);
				if ( count($matches) > 1 ) { $complex_formula = true; }
				if ( $verbose == "true" ) { $info .= "boia matches: ".print_r($matches, true)."<br />"; } //<pre></pre>
			}
		}
		if ( $verbose == "true" ) { $info .= "calc_boia: ".print_r($calc_boia, true)."<br />"; }
		if ( count($calc_boia) == 1 ) { $calc_boia = $calc_boia[0]; } else { $complex_formula = true; }
			
		// What's the weekday for the date to be calculated?
		foreach ( $weekdays AS $weekday ) {
			if (stripos($date_calculation_str, $weekday) !== false) {
				$info .= $indent."weekday '$weekday' found in date_calculation_str<br />";
				$calc_weekday[] = strtolower($weekday);
			}
		}
		if ( $verbose == "true" ) { $info .= "calc_weekday: ".print_r($calc_weekday, true)."<br />"; }
		if ( count($calc_weekday) == 1 ) { $calc_weekday = $calc_weekday[0]; } else { $complex_formula = true; }
			
		// TODO: deal w/ propers -- e.g. "Week of the Sunday closest to May 11"
			
		// ** Determine the calc_interval -- number of days/weeks...
        //if ( preg_match('/([0-9]+)/', $date_calculation_str) ) {
		if ( preg_match_all('/[0-9]+/', $date_calculation_str, $matches, PREG_OFFSET_CAPTURE) ) {
			
			if ( $verbose == "true" ) { $info .= "date_calculation_str contains numbers.<br />"; }
			if ( $verbose == "true" ) { $info .= "number matches: ".print_r($matches, true)."<br />"; } //<pre></pre>
			
			// Extract the calc_interval integer from the string by getting rid of everything else
			// WIP deal w/ multiple value possibilities for weekday, boia
			if ( !is_array($calc_weekday) && !is_array($calc_boia) ) {
				$calc_interval = str_replace([$calc_basis, $calc_weekday, $calc_boia, 'the', 'th', 'nd', 'rd', 'st'], '', strtolower($date_calculation_str) );
				$calc_interval = trim( $calc_interval );
			} else {
				$calc_interval = null; // tft
			}
			
			//if ( $calc_boia == ("in" || "of") ) { // Advent, Easter, Lent
			if ( !empty($calc_interval) && ( ( $calc_basis == "advent" && $calc_boia != "before" ) || ( $calc_basis == "easter" && $calc_boia == "of" ) ) ) {
				$calc_interval = (int) $calc_interval - 1; // Because Advent Sunday is first Sunday of Advent, so 2nd Sunday is basis_date + 1 week, not 2
			}
			if ( $verbose == "true" && !empty($calc_interval) ) { $info .= "calc_interval: $calc_interval<br />"; }
			
		} else if ( strpos(strtolower($date_calculation_str), 'last') !== false ) {
			
			// e.g. "Last Sunday after the Epiphany"; "Last Sunday before Advent"; "Last Sunday before Easter"
			//$info .= $indent."LAST<br />"; // tft
			if ( $calc_basis == "epiphany" ) {
				$calc_interval = $num_sundays_after_epiphany;
			} else if ( $calc_basis == "easter" ) { // && $calc_boia == "before"
				$calc_formula = "previous Sunday"; //$calc_formula = "Sunday before";
			} else if ( $date_calculation_str == "last sunday before advent" ) {
				$calc_formula = "previous Sunday"; //$calc_formula = "Sunday before";
			}
			
		}
            
		// If the calc_formula hasn't already been determined, build it
		if ( $calc_formula == "" ) {
			
			//$info .= $indent."About to build calc_formula...<br />"; // tft
			
			// If the basis_date is NOT a Sunday, then get the date of the first_sunday of the basis season
			if ( $basis_date_weekday != "" && $basis_date_weekday != 'sunday' ) {                    
				$first_sunday = strtotime("next Sunday", $basis_date);
				//$info .= $indent."first_sunday after basis_date is ".date("Y-m-d", $first_sunday)."<br />"; // tft                    
				if ( $calc_interval ) { $calc_interval = $calc_interval - 1; } // because math is based on first_sunday + X weeks. -- but only if calc_weekday is also Sunday? WIP
				if ( $calc_interval === 0 ) { $calc_date = $first_sunday; }
			} else if ( $basis_date ) {
				$first_sunday = $basis_date;
				if ( $verbose == "true" ) { $info .= "first_sunday is equal to basis_date.<br />"; }
			}
			
			if ( $complex_formula == true ) {
				
				// WIP/TODO: deal w/ complex cases like Corpus Christi: "thursday after the 1st sunday after pentecost"
				// Break the date_calculation_str down into components -- first "1st sunday after pentecost", then thursday after that date
				
			} else {
			
				if ( $calc_basis != "" && $calc_weekday == "sunday" ) {

					if ( ($calc_interval > 1 && $calc_boia != "before") || ($calc_interval == 1 && $calc_boia == ("after" || "in") ) ) {
						$calc_formula = "+".$calc_interval." weeks";
						$basis_date = $first_sunday;                    
					} else if ( $calc_boia == "before" ) { 
						$calc_formula = "previous Sunday";
					} else if ( $calc_boia == "after" ) { 
						$calc_formula = "next Sunday";
					} else if ( $first_sunday ) {
						$calc_date = $first_sunday; // e.g. "First Sunday of Advent"; "The First Sunday In Lent"
					} 

				} else if ( $calc_basis != "" && $calc_boia == ( "before" || "after") ) {
				
					//$info .= $indent."setting prev/next<br />"; // tft
					// e.g. Thursday before Easter; Saturday after Easter -- BUT NOT for First Monday in September; Fourth Thursday in November -- those work fine as they are via simple strtotime
					if ( $calc_boia == "before" ) { $prev_next = "previous"; } else { $prev_next = "next"; } // could also use "last" instead of "previous"
					$calc_formula = $prev_next." ".$calc_weekday; // e.g. "previous Friday";
				}
			
			}
			
		}
		
		// If there's no $calc_formula yet, use the date_calculation_str directly
		if ( empty($calc_formula) && empty($calc_date) ) {
			$info .= $indent."calc based directly on date_calculation_str<br />"; // .'<span class="notice">'.'</span>'
			if ( $calc_boia != "after" ) {
				$calc_formula = $date_calculation_str;               
			} else {
				//$info .= $indent."Unable to determine calc_formula -- calc_boia: \"$calc_boia\"; calc_date: $calc_date<br />";
			}
		}
		
		//$info .= $indent.">> date_calculation_str: $date_calculation_str<br />"; // tft
		//$info .= $indent.">> [$calc_interval] -- [$calc_weekday] -- [$calc_boia] -- [$calc_basis_field]<br />"; // tft
		//$info .= $indent.'>> basis_date unformatted: "'.$basis_date.'<br />'; // tft
		//
		// calc_date not yet determined >> do the actual calculation using the formula and basis_date
		if ( empty($calc_date) ) {
		
			$info .= '>> calc_formula: "'.$calc_formula.'"; basis_date: '.date('Y-m-d',$basis_date).'<br />'; // tft
			
			// WIP/TODO: deal w/ complex cases like Corpus Christi: "thursday after the 1st sunday after pentecost"
			// Must check to see if Pentecost is a Sunday, and if so, the basis_date must be set to the next Sunday after that.
			//
			
			if ( $calc_formula != "" && $basis_date != "" ) {
				$calc_date = strtotime("$calc_formula", $basis_date);
			} else {
				$info .= "Can't do calc -- calc_formula or basis_date is empty.<br />";
			}
			//$info .= $indent.'strtotime("'.$calc_formula.'",$basis_date)<br />';
			//$info .= $indent."calc_date -- ".$calc_date.' = strtotime("'.$calc_formula.'", '.$basis_date.')<br />'; // tft
			// X-check with https://www.w3schools.com/php/phptryit.asp?filename=tryphp_func_strtotime
			// calc_formula examples: '-6 months' // '+2 year' // "last Sunday" // "+4 weeks" // "next Sunday" // '+1 week'
		}
		
		// Make sure the calculated date doesn't conflict with the subsequent church season -- this applies to only Epiphany (into Lent) and Pentecost (into Advent)
		if ( $calc_basis == "epiphany" ) {
		
			// Make sure this supposed date in the Epiphany season doesn't run into Lent
			$info .= "There are $num_sundays_after_epiphany Sundays after Epiphany in $year.<br />"; // tft
			if ( $calc_date > strtotime($ash_wednesday_date) ) { //if ( (int) $calc_interval > (int) $num_sundays_after_epiphany ) {
				$info .= $indent.'<span class="warning">Uh oh! That\'s too many Sundays.</span><br />'; // tft
				$info .= $indent.'<span class="warning">calc_date: ['.date('Y-m-d', $calc_date).']; ash_wednesday_date: '.$ash_wednesday_date.'</span><br />'; // tft
				$calc_date = "N/A";
			}
			
		} else if ( $calc_basis == "lent" ) {
		
			// TODO: make sure date doesn't overlap w/ holy week
			
		} else if ( $calc_basis == "pentecost" ) {
			
			// Make sure this supposed date in Ordinary Time/Pentecost season isn't actually in Advent
			// Pentecost: "This season ends on the Saturday before the First Sunday of Advent."                
			// TODO -- figure out if this is the LAST Sunday of Pentecost?
			
			if ( $calc_date > strtotime("previous Saturday", strtotime($advent_sunday_date) ) ) {
			//if ( $calc_date > strtotime($advent_sunday_date) ) {
				//$info .= $indent.date( 'Y-m-d', strtotime("previous Saturday", strtotime($advent_sunday_date)) );
				$info .= $indent.'<span class="warning">'."Uh oh! ".date('Y-m-d',$calc_date)." conflicts with Advent. Advent Sunday is $advent_sunday_date.</span><br />"; // tft
				$calc_date = "N/A";
			} else {
				$info .= $indent."Ok -- Advent begins on $advent_sunday_date.<br />"; // tft
			}
		}

	}        
	
    // END WIP
    
    $info .= '</div>';
        
    $calc['calc_date'] = $calc_date;
    $calc['calc_info'] = $info;
    
    return $calc;

}

add_shortcode('calculate_variable_dates', 'calc_litdates');
function calc_litdates( $atts = [] ) {

    // Failsafe -- run this fcn ONLY if logged in as webdev
    if ( !queenbee() ) { return "You are not authorized to run this operation.<br />"; }
    
	$info = "";
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;";

	$args = shortcode_atts( array(
        'testing' => true,
        'verbose' => false,
        'ids' => null,
        'year' => date('Y'),
        'num_posts' => 10,
        'admin_tag_slug' => 'dates-calculated', // 'programmatically-updated'
        'orderby' => 'title',
        'order' => 'ASC',
        'meta_key' => null
    ), $atts );
    
    // Extract
	extract( $args );

	// WIP
    //if ( empty($year) && get_query_var('y') ) { $year = get_query_var('y'); }
    
    $info .= ">>> calc_litdates <<<<br />";
    $info .= "testing: $testing; verbose: $verbose; orderby: $orderby; order: $order; meta_key: $meta_key; ";
    $info .= "year: $year<br />";
    
    // Set up the WP query args
	$wp_args = array(
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
        'orderby' => $orderby,
        'order'	=> $order,
	);
    
    // If ids and/or a meta_key (for ordering) have been specified, add those to the query args
    if ( $ids !== null ) { $wp_args['post__in'] = explode(', ', $ids); }
    if ( $meta_key !== null ) { $wp_args['meta_key'] = $meta_key; }
    
    // Run the query
	$arr_posts = new WP_Query( $wp_args );
    $posts = $arr_posts->posts;
    $info .= "[num posts: ".count($posts)."]<br />";
    //$info .= "wp_args: <pre>".print_r( $wp_args, true )."</pre>";
    $info .= "<!-- wp_args: <pre>".print_r( $wp_args, true )."</pre> -->";
    //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre><br />"; // tft
    $info .= "<br />";
    
    // Loop through the posts and calculate the variable dates for the given year
    foreach ( $posts AS $post ) {
        
        setup_postdata( $post );
        $post_id = $post->ID;
        $post_title = $post->post_title;
        $slug = $post->post_name;
        $info .= '<span class="label">['.$post_id.'] "'.$post_title.'"</span><br />';
    	
        // init
        $calc_info = "";
        
        $changes_made = false;
        $complex_formula = false;
        
        // Get date_calculation info & break it down
        $date_calculation_str = get_post_meta( $post_id, 'date_calculation', true );
        $date_calculation_str = str_replace('christmas day', 'christmas', strtolower($date_calculation_str) );
        $date_calculation_str = str_replace('the epiphany', 'epiphany', strtolower($date_calculation_str) );
        //$date_calculation_str = str_replace(['the', 'day'], '', strtolower($date_calculation_str) );
        $calc_info .= $indent."date_calculation_str: $date_calculation_str<br />"; // tft
        
        //
        $calc = calc_date_from_str( $year, $date_calculation_str, $verbose );
        if ( $calc ) {
        	$calc_date = $calc['calc_date'];
        	$calc_info .= $indent.$calc['calc_info'];
        } else {
        	$calc_info .= $indent.'<span class="error">calc_date_from_str failed</span><br />';
        }
                
        if ( !empty($calc_date) && $calc_date != "N/A" ) {
            $calc_date_str = date('Y-m-d', $calc_date);
            //$calc_date_str = date('Ymd', $calc_date); // was originally 'Y-m-d' format, which is more readable in DB, but ACF stores values edited via CMS *without* hyphens, despite field setting -- bug? or am I missing something?
            $calc_info .= $indent."calc_date_str: <strong>$calc_date_str</strong> (".date('l, F d, Y',$calc_date).")<br />"; // tft
        } else {
            $calc_date_str = "";
            $calc_info .= $indent."calc_date N/A<br />"; // tft            
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
                        $calc_info .= $indent."+++ Old news. This date_calculated ($calc_date_str) is already in the database.<br />".$indent."+++<br />"; // tft
                    } else {
                    	//$calc_info .= $indent."Old date_calculated: $date_calculated.<br />"; // tft
                    }
                endwhile;
            } // end if

            if ( $newrow == true ) {

                $row = array(
                    'date_calculated' => $calc_date_str
                );

                $calc_info .= "About to add row to post_id $post_id: <pre>".print_r( $row, true )."</pre>";
                if ( $testing != "true" ) {
                    if ( add_row('date_calculations', $row, $post_id) ) { // ACF function syntax: add_row($selector, $value, [$post_id])
                        $calc_info .= $indent."ACF row added for post_id: $post_id<br />"; // tft
                    }
                }

            }
        } else {
        	$calc_info .= "calc_date_str is empty.<br />";
        }
        
        $info .= $calc_info;
        $info .= "<br />";
             
    } // END foreach post
    
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
    
    $args = shortcode_atts( array(
		'post_id'       => get_the_ID(),
        'event_id' => null,
		'service'  => 'morning_prayer',
    ), $atts );
    
    // Extract
	extract( $args );
    
    $post = get_post( $post_id );
    if ( $post ) { $post_type = $post->post_type; }
    if ( $post_type == 'event' ) { $event_id = $post_id; }
    
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
    $wp_args = array(
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
    $psalms_of_the_day = new WP_Query( $wp_args );
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