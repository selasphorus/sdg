<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** CPT: LITURGICAL DATE ***********/

// TODO: move this function to WHx4
function normalize_month_to_int( string $month ): ?int
{
    $month = strtolower( trim( $month ) );

    $map = [
        'jan' => 1, 'january'   => 1,
        'feb' => 2, 'february'  => 2,
        'mar' => 3, 'march'     => 3,
        'apr' => 4, 'april'     => 4,
        'may' => 5,
        'jun' => 6, 'june'      => 6,
        'jul' => 7, 'july'      => 7,
        'aug' => 8, 'august'    => 8,
        'sep' => 9, 'sept' => 9, 'september' => 9,
        'oct' => 10, 'october'  => 10,
        'nov' => 11, 'november' => 11,
        'dec' => 12, 'december' => 12,
    ];

    return $map[ $month ] ?? null;
}


add_shortcode( 'liturgical_dates', 'render_liturgical_dates_shortcode' );
function render_liturgical_dates_shortcode( $atts = [] ): string
{
    // Extract args    
    $args = shortcode_atts( [
        'date'        => null,
        'year'        => null,
        'month'       => null,
        'day_titles_only' => false,
        'debug'       => false,
        'filter_types' => '',
    ], $atts );
	extract( $args );

    $atts['return'] = 'formatted'; // force formatted output (instead of data array)

    // Convert comma-separated string to array
    if ( !empty( $atts['filter_types'] ) ) {
        $atts['filter_types'] = array_map( 'trim', explode( ',', strtolower( $atts['filter_types'] ) ) );
    }

    return get_liturgical_date_data( $atts );
}

// WIP: Combined logic from get_lit_dates and get_day_title
function get_liturgical_date_data( array $args = [] ): array|string
{
    $defaults = [
		'date'				=> null,
		'year'				=> null,
		'month'				=> null,
		'day_titles_only'	=> false,
		'exclusive'	=> false, // set to true to display only one primary (and possibly on secondary) litdate per calendar date. TODO: better arg name
		'return'			=> 'posts', // 'posts' | 'prioritized' | 'formatted'
		'formatted'			=> false,
		'show_meta_info'	=> false,
		'post_id'			=> null,
		'series_id'			=> null,
		'debug'				=> false,
		'filter_types'		=> [], // e.g. ['primary'] to limit output
		'type_labels'		=> [   // override default labels as needed -- probably won't need this though in fact...
			'primary'   => 'Primary',
			'secondary' => 'Secondary',
			'other'     => 'Other',
		],
	];

    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    $info = '';
    //$ts_info = '';
    $litdatePostsByDate = [];
    $litdate_data = [];

	//$info .= "args: <pre>".print_r($args,true)."</pre>";
	
	// Normalize date input
    if ($date) {
    
        $dateStr = date( 'Y-m-d', strtotime( $date ) );
        $start_date = $end_date = $dateStr;
        $year  = substr( $dateStr, 0, 4 );
        $month = substr( $dateStr, 5, 2 );
    
    } else {
    	
        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to current year if none is set
        }

		if ( empty( $month ) ) {
			$start_date = $year . '-01-01';
			$end_date   = $year . '-12-31';
		} else {
			
			if ( !is_numeric( $month ) ) {
				$month = normalize_month_to_int( $month );
			}

			$month = (int)$month;

			if ( $month < 1 || $month > 12 ) {
				return [
					'args' => $args,
					'info' => "Invalid month: '$month'",
				];
			}

			$start_date = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-01';
			$days_in_month = cal_days_in_month( CAL_GREGORIAN, $month, (int)$year );
			$end_date   = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-' . $days_in_month;
		}
    }

    $info .= "start_date: $start_date; end_date: $end_date<br />";

    $start = strtotime( $start_date );
    $end   = strtotime( $end_date );

    while ($start <= $end) {
        
        $dateStr = date( 'Y-m-d', $start );
        
        // === Fixed Date Matching ===
        $fixed_str = date( 'F j', $start );  // without leading zero
        $fixed_str_zero = date( 'F d', $start ); // with leading zero

        $fixedMetaQuery = [
            'relation' => 'OR',
            [ 'key' => 'fixed_date_str', 'value' => $fixed_str_zero, 'compare' => '=' ],
            [ 'key' => 'fixed_date_str', 'value' => $fixed_str,      'compare' => '=' ],
        ];

        if ( $day_titles_only ) {
            $fixedMetaQuery = [
                'relation' => 'AND',
                [ 'key' => 'day_title', 'value' => '1', 'compare' => '=' ],
                [ 'relation' => 'OR', $fixedMetaQuery[0], $fixedMetaQuery[1] ],
            ];
        }

        $fixedQueryArgs = [
            'post_type'      => 'liturgical_date',
            'post_status'    => 'publish',
            'meta_query'     => $fixedMetaQuery,
            'orderby'        => [ 'meta_value' => 'DESC', 'ID' => 'ASC' ],
            'meta_key'       => 'day_title',
            'posts_per_page' => -1,
        ];

		//$info .= "<strong>Fixed Date query_args</strong>: <pre>".print_r($fixedQueryArgs,true)."</pre>";
		
        $qFixed = new WP_Query($fixedQueryArgs);
        if ( $qFixed->have_posts() ) {
            $litdatePostsByDate[$dateStr] = $qFixed->posts;
            if ( count($qFixed->posts) != 1 ) { $info .= "<strong>$dateStr</strong>: found ".count($qFixed->posts)." matching fixed-date post(s)<br />"; }
        }

        // === Variable Date Matching ===
        $ymd = date( 'Ymd', $start ); // for ACF stored format

        $variableMetaQuery = [
            'relation' => 'OR',
            [ 'key' => 'date_calculations_XYZ_date_calculated', 'value' => $dateStr, 'compare' => '=' ],
            [ 'key' => 'date_assignments_XYZ_date_assigned',    'value' => $dateStr, 'compare' => '=' ],
            [ 'key' => 'date_calculations_XYZ_date_calculated', 'value' => $ymd,       'compare' => '=' ],
            [ 'key' => 'date_assignments_XYZ_date_assigned',    'value' => $ymd,       'compare' => '=' ],
        ];

        if ( $day_titles_only ) {
            $variableMetaQuery = [
                'relation' => 'AND',
                [ 'key' => 'day_title', 'value' => '1', 'compare' => '=' ],
                [ 'relation' => 'OR',
                    $variableMetaQuery[0],
                    $variableMetaQuery[1],
                    $variableMetaQuery[2],
                    $variableMetaQuery[3],
                ]
            ];
        }

        $variableQueryArgs = [
            'post_type'      => 'liturgical_date',
            'post_status'    => 'publish',
            'meta_query'     => $variableMetaQuery,
            'orderby'        => [ 'meta_value' => 'DESC', 'ID' => 'ASC' ],
            'meta_key'       => 'day_title',
            'posts_per_page' => -1,
        ];

		//$info .= "<strong>Variable Date query_args</strong>: <pre>".print_r($variableQueryArgs,true)."</pre>";
		
        $qVar = new WP_Query($variableQueryArgs);
        if ( $qVar->have_posts() ) {
            if ( isset( $litdatePostsByDate[$dateStr] ) ) {
                $litdatePostsByDate[$dateStr] = array_merge($litdatePostsByDate[$dateStr], $qVar->posts);
            } else {
                $litdatePostsByDate[$dateStr] = $qVar->posts;
            }
            if ( count($qVar->posts) != 1 ) { $info .= "<strong>$dateStr</strong>: found ".count($qVar->posts)." matching variable-date post(s)<br />"; }
        }

        $start = strtotime( '+1 day', $start );
    }

	// Loop through litdate posts and sort them by priority
	foreach ($litdatePostsByDate as $dateStr => $posts) {

		$unsorted = [];
		$primaryPost = null;
		$secondaryPost = null;
        $defaultPriority = 999;
        //
        //error_log('=== litdatePostsByDate for date: '.$dateStr.' ===');
        
		foreach ($posts as $post) {

			$postID = $post->ID;
			$postPriority = $defaultPriority;
			$type = 'other';
			//
			//error_log('postID: '.$postID);
			
			// Get the actual display_dates for the given litdate, to make sure the date in question hasn't been overridden			
			$display_dates_info = get_display_dates ( $postID, $year );
			//$ts_info .= $display_dates_info['info'];
			$display_dates = $display_dates_info['dates'];
			//$ts_info .= "display_dates: <pre>".print_r($display_dates, true)."</pre>";
			if ( !in_array($dateStr, $display_dates) ) {
				//$ts_info .= "date_str: ".$dateStr." is not one of the display_dates for this litdate.<br />";
				// Therefore don't show it.
				//$postID = null;
				continue;
			}

			// Get category/priority
			$terms = get_the_terms( $postID, 'liturgical_date_category' );
			// Looping through all the post's terms, set the post priority equal to the term priority with the lowest integer value
			if ( $terms && !is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$termPriority = get_term_meta($term->term_id, 'priority', true);
					error_log('term: '.print_r($term, true).' with priority: '.$termPriority);
					// If term_priority is lower than current max priority, then this is a more important category (#1 comes first)
					if ( is_numeric( $termPriority ) && $termPriority < $postPriority ) {
						$postPriority = (int)$termPriority;
					}
				}
			}

			// Check if litdate post has been designated as secondary
			$is_secondary = get_post_meta( $postID, 'secondary', true );

			// Set the type accordingly (default is 'other')
			if ( $is_secondary ) {
				$type = 'secondary';
			} elseif ( $postPriority < 999 ) {
				$type = 'primary';
			}

			$unsorted[$type][] = [
				'post'     => $post,
				'priority' => $postPriority,
			];
			
			//$info .= 'postID: '.$postID."; priority: ".$postPriority."<br />";
		}

		// Sort primaries by priority, lowest first
		$sorted = [];
		foreach ( $unsorted as $type => $posts ) {
			$sorted[$type] = $posts;
			usort( $sorted[$type], function( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			} );
		}
		/*if ( !empty( $unsorted['primary'] ) ) {
			usort( $sorted['primary'], function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			});
		}*/
		// wip...
        if ( $exclusive ) {
        	//$info .= "exclusive => get only the single most important matching primary post for date: ".$dateStr."<br />";
        	// Get the single most important matching litdate post
        	if ( !empty( $sorted['primary'] ) ) {
        		$primaryPost = $sorted['primary'][0];
        	} else if ( !empty( $sorted['other'] ) ) {
        		$primaryPost = $sorted['other'][0];
        	}
            if ($primaryPost) {
        		//$info .= "primaryPost found for date: ".$dateStr.": ".print_r($primaryPost,true)."<br />";
        		$info .= "primaryPost found with ID: ".$primaryPost['post']->ID."<br />";
        		$litdate_data[$dateStr]['primary'] = $primaryPost;
        	} else {
        		$info .= "No primaryPost found!<br />";
        	}
            // Get the most important secondary litdate post, if any
        	if ( !empty( $sorted['secondary'] ) ) {
        		$secondaryPost = $sorted['secondary'][0];
        	}
            if ($secondaryPost) { $litdate_data[$dateStr]['secondary'] = $secondaryPost; }
        } else {
            $litdate_data[$dateStr] = $sorted;
            //$litdate_data[$date] = $posts;
        }
	}

	// If formatted output was requested...
    if ( $args['return'] === 'formatted' ) {
    
    	//$info .= "litdate_data: <pre>".print_r($litdate_data,true)."</pre>";
    	
		$output = '';	
		//if ( $args['debug'] && !empty( $info ) ) { $output .= '<div class="debug-info">'.$info.'</div>'; }
		
		foreach ( $litdate_data as $dateStr => $typeGroups ) {
			
			$output .= "<div class='liturgical-date-block'>";
			$output .= "<strong>" . esc_html( date( 'l, F j, Y', strtotime( $dateStr ) ) ) . "</strong><br />";
	
			$groups_to_render = ['primary', 'secondary', 'other'];
			
			foreach ( $groups_to_render as $group_key ) {

				if ( !empty( $args['filter_types'] ) && !in_array( $group_key, $args['filter_types'], true ) ) {
					continue;
				}
				
				if ( !empty( $typeGroups[ $group_key ] ) ) {
					//if ( $group_key !== 'primary' ) {
						$label = $args['type_labels'][ $group_key ] ?? ucfirst( $group_key );
						$output .= "<em>$label</em><br />";
					//}

					foreach ( $typeGroups[ $group_key ] as $group_item ) {
						//$output .= "group_item: <pre>".print_r($group_item,true)."</pre>";

						if (is_array($group_item)) {
							$post = $group_item['post'];
							$postPriority = $post['priority'];
						} else {
							$post = $group_item;
						}
						$post = get_post( $post );
						if ( !$post instanceof WP_Post ) {
							//$output .= "So-called post ".print_r($post,true)." is not a WP_Post object. Moving on to the next...<br />";
							continue;
						}
						$title = get_the_title( $post );
						$link = get_permalink( $post );
						$output .= '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a>&nbsp;'; // <br />
						// Optional meta info
						if ( $show_meta_info ) {
							$terms = get_the_terms( $post, 'liturgical_date_category' );
							$term_names = $terms && !is_wp_error( $terms ) ? wp_list_pluck( $terms, 'name' ) : [];
							$date_type = get_post_meta( $post->ID, 'date_type', true );
							//
							$output .= '<small>'; //<br />
							$output .= 'Date type: ' . esc_html( $date_type );
							if ( !empty( $term_names ) ) {
								$output .= ' | Terms: ' . esc_html( implode( ', ', $term_names ) );
							}
							$output .= ' | Priority: ' . esc_html( $postPriority );
							$output .= '</small>';
						}

						$output .= '<br />';
					}
					$output .= "<br />";
				}
			}
	
			$output .= "</div><br />";
		}
		
		if ( $args['debug'] && !empty( $info ) ) { $output = '<div class="debug-info">'.$info.'</div>'.$output; } // info first
		//if ( $args['debug'] && !empty( $info ) ) { $output .= '<div class="debug-info">'.$info.'</div>'; } // output first
		
		return $output;
	}

    // Default return
    return [
        'args'                  => $args,
        'start_date'            => $start_date,
        'end_date'              => $end_date,
        'litdate_data'          => $litdate_data,
        //'litdate_posts_by_date' => $litdatePostsByDate,
        'info'                  => $info,
    ];
}

// Get liturgical date records matching given date or date range (given month & year)
// Returns multi-dimensional array of litdate post objects per date
// Merge this with the get_day_title fcn => return IDs or post objects in order of priority, and flagged as primary, secondary, low_priority(?)
function get_lit_dates ( $args ) 
{
	// TODO: Optimize this function! Queries run very slowly. Maybe unavoidable given wildcard situation. Consider restructuring data?
	
	$ts_info = "";
	
	// Defaults
	$defaults = array(
		'date'	=> null,
		'year'	=> null,
		'month'	=> null,
		'day_titles_only' => false,
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	// Init
	$litdates = array();
	$litdate_posts = array();
	$start_date = null;
	$end_date = null;
	
	$ts_info .= "&gt;&gt;&gt; get_lit_dates &lt;&lt;&lt;<br />";
	
	// Set vars
	// TODO: remember how to do this more efficiently, setting defaults from array or along those lines...
	
	if ( $date ) {
	
		// TODO: deal w/ possibility that date is passed in the wrong format >> convert it to YYYY-MM-DD
		//if str contains commas? if first four digits not a number? ... date('Y-m-d',strtotime($date))
		$start_date = $end_date = $date;
		$year = substr($date,0,4);
		$month = substr($date,5,2);
		
	} else {
		
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
	
	$ts_info .= "start_date: '$start_date'; end_date: '$end_date'; year: '$year'; month: '$month'<br />"; // tft
    
    // Loop through all dates in range from start to end
    $start = strtotime($start_date);
    $end = strtotime($end_date);

	while ($start <= $end) {
		
		// Build  query to search for any liturgical dates that match, whether dates are fixed, calculated, or manually assigned
		// TODO/WIP: run separate queries for fixed, assigned, calc -- so as to account for possibility that e.g. no assigned/calc dates exist for a fixed date litdate entry
		// 
        $arr_posts = array();
        
        // Format date_str
        $full_date_str = date("Y-m-d", $start );
        //$ts_info .= "full_date_str: '$full_date_str'<br />"; // tft
    
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
	
		if ( $day_titles_only ) {
			$meta_query[] = array(
				'key'		=> 'day_title',
				'compare'	=> '=',
				'value'		=> '1',
			);
		}
        
        // Prep meta_sub_query which will check for various kinds of date matches
        $meta_sub_query = array( 'relation'  => 'OR' );
        
        // +~+~+~+~+~+~+~+~+~+~+
        // 1. FIXED DATES
        $litdate_args_fixed = $litdate_args;
        $meta_query_fixed = $meta_query;
        $meta_sub_query_fixed = $meta_sub_query;
        
        // Add meta_query components for fixed dates
        $fixed_date_str = date("F d", $start ); // day w/ leading zeros
        //$ts_info .= "<!-- fixed_date_str: '$fixed_date_str' -->\n"; // tft
        $meta_sub_query_fixed[] = array(
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
            $meta_sub_query_fixed[] = array(
				'key'		=> 'fixed_date_str',
				'compare'	=> '=',
				'value'		=> $fixed_date_str_alt,
			);
        }
        
        // Add sub_query to meta_query
    	$meta_query_fixed[] = $meta_sub_query_fixed;    	
		$litdate_args_fixed['meta_query'] = $meta_query_fixed;
		
		// Run the query
		$ts_info .= "litdate_args_fixed: <pre>".print_r($litdate_args_fixed, true)."</pre>";
		$arr_fixed = new WP_Query( $litdate_args_fixed );
		$arr_posts_fixed = $arr_fixed->posts;
		$arr_posts = array_merge($arr_posts, $arr_posts_fixed);
		
        // +~+~+~+~+~+~+~+~+~+~+
        // 2. VARIABLE DATES
        
        $litdate_args_variable = $litdate_args;
        $meta_query_variable = $meta_query;
        $meta_sub_query_variable = $meta_sub_query;
        
        // TODO: streamline subqueries
        // Add meta_query components for date calculations and assignments        
		$meta_sub_query_variable[] = array(
			/*'relation' => 'AND',
			array(
				'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
				'compare'	=> 'EXISTS',
			),
			array(
				'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
				'compare'	=> '=',
				'value'		=> $full_date_str,
			),*/
			'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> $full_date_str,
		);
		$meta_sub_query_variable[] = array(
			/*'relation' => 'AND',
			array(
				'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
				'compare'	=> 'EXISTS',
			),
			array(
				'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
				'compare'	=> '=',
				'value'		=> $full_date_str,
			),*/
			'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> $full_date_str,
		);
		// The following parameters can be phased out eventually once the DB is updated to standardize the date formats
		$meta_sub_query_variable[] = array(
			/*'relation' => 'AND',
			array(
				'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
				'compare'	=> 'EXISTS',
			),
			array(
				'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
				'compare'	=> '=',
				'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
			),*/
			'key'		=> 'date_calculations_XYZ_date_calculated', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
		);
		$meta_sub_query_variable[] = array(
			/*'relation' => 'AND',
			array(
				'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
				'compare'	=> 'EXISTS',
			),
			array(
				'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
				'compare'	=> '=',
				'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
			),*/
			'key'		=> 'date_assignments_XYZ_date_assigned', // variable dates via ACF repeater row values
			'compare'	=> '=',
			'value'		=> str_replace("-", "", $full_date_str), // get rid of hyphens for matching -- dates are stored as yyyymmdd due to apparent ACF bug
		);
        
        // Add sub_query to meta_query
    	$meta_query_variable[] = $meta_sub_query_variable;    	
		$litdate_args_variable['meta_query'] = $meta_query_variable;
		
		// Run the query
		$ts_info .= "litdate_args_variable: <pre>".print_r($litdate_args_variable, true)."</pre>";
		$arr_variable = new WP_Query( $litdate_args_variable );
		$arr_posts_variable = $arr_variable->posts;
		$arr_posts = array_merge($arr_posts, $arr_posts_variable);
        
        // +~+~+~+~+~+~+~+~+~+~+
		$litdate_posts[$full_date_str] = $arr_posts;
		
		// Go to the next day
		$start = strtotime("+1 day", $start);
	}
	
	
	// TODO: deal w/ date exceptions (via Date Assignments field group) -- replacement_date, exclusion_date
    // date_assignments: "Use this field to override the default Fixed Date or automatic Date Calculation."
    
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
function get_lit_dates_list( $atts = array(), $content = null, $tag = '' )
{
	// TS/logging setup
    $do_ts = devmode_active( array("sdg", "lectionary") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: get_lit_dates_list", $do_log );
    
	// Init
	$info = "";
	$ts_info = "";
	
	$info = "\n<!-- get_lit_dates_list -->\n";
    
    $args = shortcode_atts( array(
      	'year'   => date('Y'),
        'month' => null,
        'public' => false, // set to true to show ONLY those dates being displayed on the front end -- WIP!
    ), $atts );
    
    // Extract
	extract( $args );
	
	$ts_info .= "args: <pre>".print_r($args,true)."</pre>";
	
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
    
    // If $public, then get_day_title per date
    
    // If NOT $public, get all litdates for the date range
    if ($public) {
    	//get_day_title( array ('the_date' => $sermon_date ) )
    } else {
    	$litdate_args = array( 'year' => $year, 'month' => $month );
		$litdates = get_lit_dates( $litdate_args );
		//$ts_info .= $litdates['troubleshooting'];
		
		$posts = $litdates['posts'];
    }
	
    
    
    foreach ( $posts AS $date_str => $date_posts ) {
        
        if ( !empty($date_posts)) {
        	$info .= '<a href="/events/'.date('Y-m-d',strtotime($date_str)).'/" target="_blank">';
        	$info .= date('l, F j, Y',strtotime($date_str));
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
        		//if ( $num_day_titles > 1 ) { $classes .= " conflict"; }
        	}
        	//
        	$classes = "litdate";
        	$secondary = get_post_meta($litdate_id, 'secondary', true);
			$info .= '<span class="'.$classes.'">';
			$info .= '<a href="'.get_permalink($litdate_id).'" class="smaller" target="_blank">';
			$info .= "[".$litdate_id."] ";
			$info .= '</a>';
			$info .= '</span>';
			//
        	if ( $secondary == "1" ) {
        		$classes .= " secondary";
        	}
			$info .= '<span class="'.$classes.'">';
			$info .= $lit_date->post_title;
			$info .= '</span>';		
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
			// TODO: deal w/ date exceptions?
			
			$i++;
        }
        
		/*$litdate_post_id = $litdate_post->ID;
		$info .= "[".$litdate_post_id."] ".$litdate_post->post_title."<br />"; // tft
		
		// Get date_type (fixed, calculated, assigned)
		$date_type = get_post_meta( $litdate_post_id, 'date_type', true );
		$info .= "date_type: ".$date_type."<br />"; // tft*/
		
		if ( !empty($date_posts)) { $info .= "<br />"; }
	}
	
	// Troubleshooting
    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "lectionary" ) ) { $info .= $ts_info; }
    
    return $info;
    
} 

function get_cpt_liturgical_date_content( $postID = null )
{
	// init
	$info = "";
	if ($postID === null) { $postID = get_the_ID(); }
	$litdate_id = $postID;
	
	$info .= '<!-- cpt_liturgical_date_content -->';
    $info .= '<!-- litdate_id: '.$litdate_id.' -->';
    
    if ( have_rows('date_assignments', $litdate_id) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
		while ( have_rows('date_assignments', $litdate_id) ) : the_row();
			$date_assigned = get_sub_field('date_assigned');
			$date_exception = get_sub_field('date_exception'); 
			//$info .= "<!-- date_exception: ".$date_exception." -->";
			//if ( $date_exception == "replacement_date" || $replacement_date == "1" ) { }
		endwhile;
	} // end if
	
	return $info;
}

// WIP
// A liturgical date may correspond to multiple dates in a year, if dates have been both assigned and calculated,
// or if a date has been assigned to replace the fixed date
// The following function determines which of the date(s) is active -- could be multiple, if date assigned is NOT a replacement_date
function get_display_dates ( $postID = null, $year = null )
{
	
	$info = "";
	$dates = array();
	$arr_info = array();
	$fixed_date_str = ""; 
	
	// Get date_type (fixed, calculated, assigned)
    $date_type = get_post_meta( $postID, 'date_type', true );
    $info .= "--- get_display_dates ---<br />";
    $info .= "litdate post_id: ".$postID."; date_type: ".$date_type."; year: ".$year."<br />";
         
	// Get calculated or fixed date for designated year
	if ( $date_type == "fixed" ) {
		if ( !$fixed_date_str = get_field( 'fixed_date_str', $postID ) ) { 
			$info .= "No fixed_date_str found.<br />";
		} else {
			$info .= "fixed_date_str: ".$fixed_date_str."<br />";
			if ( $year ) {
				$fixed_date_str .= " ".$year;
				$info .= "fixed_date_str (mod): ".$fixed_date_str."<br />";
			}
			$formatted_fixed_date_str = date("Y-m-d",strtotime($fixed_date_str));
			$info .= "formatted_fixed_date_str: ".$formatted_fixed_date_str."<br />";
			$dates[] = $formatted_fixed_date_str;
		}		
	} else {
		// For variable dates, get date calculations
		// TODO: run a query instead to find rows relevant by $year -- it will be more efficient than retrieving all the rows
		if ( have_rows('date_calculations', $postID) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
			while ( have_rows('date_calculations', $postID) ) : the_row();
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
	if ( have_rows('date_assignments', $postID) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
		while ( have_rows('date_assignments', $postID) ) : the_row();
			$date_assigned = get_sub_field('date_assigned');
			$date_exception = get_sub_field('date_exception'); 
			$replacement_date = get_sub_field('replacement_date'); // deprecated
			//$info .= "<!-- date_exception: ".$date_exception." -->";			
			$year_assigned = substr($date_assigned, 0, 4);
			$info .= "date_assigned: ".$date_assigned." (".$year_assigned.")<br />";
			if ( $year_assigned == $year ) {
				if ( $date_exception != "default" ) {
					if ( $date_assigned != $fixed_date_str && ( $date_exception == "replacement_date" || $replacement_date == "1" ) ) {
						$info .= "replacement_date date_assigned: ".$date_assigned." overrides fixed_date_str ".$fixed_date_str." for year ".$year."<br />";
						$fixed_date_str = $date_assigned;
						$dates = array($fixed_date_str); // Since this is a replacement_date it should be the only one displayed in the given year -- don't add it to array; replace the array
						break;
					} else if ( $date_exception == "exclusion_date" ) { //$date_assigned != $fixed_date_str && 
						// Remove the exclusion date from the array of dates
						$dates = array_diff($dates, [$date_assigned]);
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
// TODO: make this less confusing. It's all about dealing with display exceptions (replacement and exclusion dates)
// Check to see if litdate has been assigned to another date to override the given date
// This function is used to check litdates that have already been found to match the given date, via assignment or calculation
function show_litdate_on_date( $litdate_id = null, $date_str = null ) 
{ // TODO set default: date('Y-m-d')

	$info = "";
	//
	$info .= "<!-- litdate_id: $litdate_id -->";
	
	// Get date assignments; check to see if one is designated as a replacement_date that should negate the date match
	if ( have_rows('date_assignments', $litdate_id) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
		while ( have_rows('date_assignments', $litdate_id) ) : the_row();
		
			$date_exception = get_sub_field('date_exception');
			$replacement_date = get_sub_field('replacement_date'); // deprecated
			$info .= "<!-- date_exception: ".$date_exception." -->";
				
			// TODO: get year of date_assigned and check it against full_date_str only if years match
			$date_assigned = get_sub_field('date_assigned');
			$year_assigned = substr($date_assigned, 0, 4);
			$year_to_match = substr($date_str, 0, 4);
			$info .= "<!-- date_assigned: ".$date_assigned."; date_str: ".$date_str." -->";
			
			// Are we dealing with a date exception?
			if ( $date_exception != "default" || $replacement_date == "1" ) {
				if ( $date_assigned == $year_to_match ) { // Does the assigned date fall in the applicable calendar year?					
					// If this is a replacement_date assignment in the relevant year, then check to see if it matches the event calendar display date
					if ( $date_assigned != $date_str ) {
						// Don't show this date -- override in effect
						$info .= "<!-- date_assigned NE current date_str (".$date_str.") >> don't show title -->";
						return false;
					} else {
						$info .= "<!-- date_assigned == current date_str (".$date_str.") -->";
						if ( $date_exception == "exclusion_date" ) {
							$info .= "<!-- exclusion_date >> don't show title -->";
							return false;
						}
						$info .= "<!-- replacement_date >> DO show title -->";
						return true;
					}
				}
			}
		endwhile;
	} // end if
	
	return true;
}

// Collects -- get collect to match litdate (or calendar date? wip)
function get_collect_text( $litdate_id = null, $date_str = null )
{

	// TS/logging setup
    $do_ts = devmode_active( array("sdg", "lectionary") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: get_collect_text", $do_log );
    
	// Init
	$collect = null;
	$collect_text = "";
	$propers = false;
	$ts_info = "";
	
	$ts_info .= ">>> get_collect_text <<<<br />";
	$ts_info .= "litdate_id: ".$litdate_id."<br />";
	$ts_info .= "date_str: ".$date_str."<br />";
	$date = strtotime($date_str);
	$ts_info .= "litdate date Y-m-d: ".date('Y-m-d',$date)."<br />";
	// Get the month and year of the date_str for use in matching by date, as needed
	$month = date('F',$date);
	$year = date('Y',$date);
			
	if ( $litdate_id ) {
	
		$collect_args = array(
			'post_type'   => 'collect',
			'post_status' => 'publish',
			//'posts_per_page' => 1,
			);
			
		$litdate_title = get_the_title( $litdate_id );
		$ts_info .= "litdate_title: ".$litdate_title."<br />";
		
		if ( strpos(strtolower($litdate_title), 'sunday after pentecost') !== false ) {
			
			$propers = true;
			$ts_info .= "propers...<br />";
			
			// For season after pentecost, match by date
			
			$collect_args['meta_query'] = array(
				'relation' => 'AND',
				'first_clause' => array(
					'key' => 'date_calc',
					'value' => $month,
					'compare' 	=> 'LIKE',
				),
				'second_clause' => array(
					'key' => 'date_calc',
					'compare' 	=> 'EXISTS',
				),
			);
						
		} else {
		
			$ts_info .= "NOT propers...<br />";
			
			// All other collects match by litdate
			$collect_args['meta_query'] = array(
				'relation' => 'AND',
				'first_clause' => array(
					'key'     => 'related_liturgical_date',
					'value' 	=> '"' . $litdate_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
					'compare' 	=> 'LIKE',
				),
				'second_clause' => array(
					'key' => 'related_liturgical_date',
					'compare' 	=> 'EXISTS',
				),
			);
			
		}
		
		$ts_info .= "collect_args: <pre>".print_r($collect_args, true)."</pre>";
		
		$collects = new WP_Query( $collect_args );
		$collect_posts = $collects->posts;    
		
		if ( count($collect_posts) == 1 ) {
		
			$ts_info .= "single matching collect post found<br />";
			$collect = $collect_posts[0];
			
		} else if ( count($collect_posts) > 1 ) {
			
			$ts_info .= "multiple matching collect posts found<br />";
			
			foreach ( $collect_posts as $post ) {
				
				if ( $propers ) {
					
					$date_calc = get_post_meta( $post->ID, 'date_calc', true );
					$ts_info .= "date_calc: ".$date_calc."<br />";
					
					$date_calc = str_replace("Week of the Sunday closest to ","",$date_calc)." ".$year;
					$ts_info .= "date_calc mod: ".$date_calc."<br />";
					
					$ref_date = strtotime($date_calc);				
					$ts_info .= "ref_date Y-m-d: ".date('Y-m-d',$ref_date)."<br />";
					
					if ( $ref_date == $date ) {
						$ts_info .= "date matches ref_date<br />";
						$collect = $post;
						break;
					}
					
					// Get dates for Sundays preceding and following collect reference date
					$prev_sunday = strtotime('previous sunday',$ref_date);
					$next_sunday = strtotime('next sunday',$ref_date);
					
					// Which Sunday is closest to the ref_date?
					$closest_sunday = min($prev_sunday,$next_sunday);
					$ts_info .= "closest_sunday Y-m-d: ".date('Y-m-d',$closest_sunday)."<br />";
					
					// Does that closest Sunday date match our litdate date?
					if ( $closest_sunday == $date ) {
						$ts_info .= "date matches closest_sunday<br />";
						$collect = $post;
						break;
					}
					
				} else {
				
					$ts_info .= "collect post ID: ".$post->ID."<br />";
				
				}
				
			}
		} else {
			
			// No matching collects found
			// ...
			
		}
		
	}
	
	if ( $collect ) {
		$ts_info .= "collect id: ".$collect->ID."<br />";
		$collect_text = $collect->post_content;
		if ( $propers ) {
			$collect_text .= "&nbsp;<em>(".$collect->post_title.")</em>";
		}
	}
	
	// TS Info
	//if ( $ts_info != "" && ( $do_ts === true || $do_ts == "sdg" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	if ( $do_ts ) { $collect_text .= '<div class="troubleshooting">'.$ts_info.'</div>'; } // tft
	
	return $collect_text;

}

// Day Titles
// TODO/WIP: separate day title functionality from special notice functionality and/or create umbrella function to allow option of displaying both together
// TODO: merge this fcn with get_lit_dates(?)
add_shortcode('day_title', 'get_day_title');
function get_day_title( $atts = array(), $content = null, $tag = '' )
{
    // TODO: Optimize this function! Queries run very slowly. Maybe unavoidable given wildcard situation. Consider restructuring data?
    // TODO: add option to return day title only -- just the text, with no link or other formatting
    
    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "lectionary") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: get_day_title", $do_log );
    
	$info = "";
	$ts_info = "";
	$hide_day_titles = 0;
	$hide_special_notices = 0;
    
    $args = shortcode_atts( array(
		'post_id'   => get_the_ID(),
		'series_id' => null,
		'the_date'  => null,
		'formatted' => true,
		'return'	=> 'display', // options: return for display or just return litdate post(s)
	), $atts);
    
    // Extract
	extract( $args );
	
	$info .= "\n<!-- get_day_title -->\n";
	$ts_info .= ">>> get_day_title <<<<br />";
	
    if ( $postID === null ) { $postID = get_the_ID(); }
    $ts_info .= "[get_day_title] post_id: ".$postID."<br />";
    if ( $series_id ) { $ts_info .= "series_id: ".$series_id."<br />"; }
    
    // If no date has yet been set, try to find one
	if ( $the_date == null ) {
        
        $ts_info .= "the_date is null -- get the_date<br />";
		
        // If no date was specified when the function was called, then get event start_date or sermon_date OR ...
        if ( $postID === null ) {
            
            return "<!-- no post -->";
            
        } else {
            
            $post = get_post( $postID );
            $post_type = $post->post_type;
            $ts_info .= "post_type: ".$post_type."<br />";

            if ( $post_type == 'event' ) {
                
                $date_str = get_post_meta( $postID, '_event_start_date', true );
                $the_date = strtotime($date_str);
                
            } else if ( $post_type == 'sermon' ) {
                
                $the_date = the_field( 'sermon_date', $postID );
                //if ( get_field( 'sermon_date', $postID )  ) { $the_date = the_field( 'sermon_date', $postID ); }
                
            } else {
                //$ts_info .= "post_id: ".$postID."<br />";
                //$ts_info .= "post_type: ".$post_type."<br />";
            }
        }
        
	}    
    
    // If the date is still null, give up and go
    if ( $the_date == null ) {
        
        // If still no date has been found, give up.
        $ts_info .= "no date available for which to find day_title<br />";
        if ( $ts_info != "" && ( $do_ts === true || $do_ts == "" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
        return $info;
        
    }
    
    // PROBLEM! TODO/WIP -- figure out why event listings accessed via pagination links send un-parseable date string to this function. It LOOKS like a string, but commas aren't recognized as commas, &c.
    // Make sure the date hasn't been returned enclosed in quotation marks
    // e.g. "Sunday, February 5, 2023"
    $the_date_type = gettype($the_date);
    $ts_info .= "var the_date is of type: ".$the_date_type."<br />";
    //$ts_info .= "var_export of the_date: ".var_export($the_date,true)."<br />";
    //
    if ( $the_date_type == "string" ) {
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
		//if (preg_match_all("/[A-Za-z]+/i", $the_date, $matches)) { $ts_info .= "preg_match_all alpha: <pre>".print_r($matches, true)."</pre>"; } else { $ts_info .= "preg_match_all alpha: No matches<br />"; }
		//if (preg_match_all("/[0-9]+/i", $the_date, $matches)) { $ts_info .= "preg_match_all numeric: <pre>".print_r($matches, true)."</pre>"; } else { $ts_info .= "preg_match_all numeric: No matches<br />"; }
		//if (preg_match_all("/[^A-Za-z0-9]+/i", $the_date, $matches)) { $ts_info .= "preg_match_all NOT alpha-numeric: <pre>".print_r($matches, true)."</pre>"; } else { $ts_info .= "preg_match_all NON-alphanumeric: No matches<br />"; }
    	
    	$date_bits = explode(", ",$the_date); // ???
		$ts_info .= "date_bits: ".print_r($date_bits,true)."<br />";
		$ts_info .= "the_date: ".$the_date."<br />";
		if ( strtotime($the_date) ) { $ts_info .= "strtotime(the_date): ".strtotime($the_date)."<br />"; } else { $ts_info .= '<span class="error">strtotime(the_date) FAILED</span><br />'; }
    
    	$date_str = date("Y-m-d", strtotime($the_date));
    
    } else {
    	
    	//the_date is NOT a string?!? hm...
    	$date_str = "";
    
    }
    
    $ts_info .= "date_str: ".$date_str."<br />";
    
    // Show or Hide Day Titles?
    // +~+~+~+~+~+~+~+~+~+~+
    // Check to see if day titles are to be hidden for the entire event series, if any
    if ( $series_id ) { 
    	$hide_day_titles = get_post_meta( $series_id, 'hide_day_titles', true );
    }
    
    // If there is no series-wide ban on displaying the titles, then should we display them for this particular post?
    if ( $hide_day_titles == 0 ) {
    	$hide_day_titles = get_post_meta( $postID, 'hide_day_titles', true );
    }
    //$ts_info .= "<!-- hide_day_titles: [$hide_day_titles] -->";
    
    if ( $hide_day_titles == 1 ) { 
        $ts_info .= "hide_day_titles is set to true for this post/event<br />";
        if ( $ts_info != "" && ( $do_ts === true || $do_ts == "" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
        return $info;
    }
    
    // Show or Hide Special Notices?
    // +~+~+~+~+~+~+~+~+~+~+
    // Check to see if special notices are to be hidden for the entire event series, if any
    if ( $series_id ) { 
    	$hide_special_notices = get_post_meta( $series_id, 'hide_special_notices', true );
    }
    
    // If there is no series-wide ban on displaying the notices, then should we display them for this particular post?
    if ( $hide_special_notices == 0 ) {
    	$hide_special_notices = get_post_meta( $postID, 'hide_special_notices', true );
    }
    
    if ( $hide_special_notices == 1 ) { 
        $ts_info .= "hide_special_notices is set to true for this post/event<br />";
    }
    
    // Get litdate posts according to date
    
    $litdate_args = array( 'date' => $date_str, 'day_titles_only' => true); //$litdate_args = array( 'date' => $the_date, 'day_titles_only' => true);
    $litdates = get_lit_dates( $litdate_args );
    $year = substr($date_str, 0, 4); // for checking display_dates later in the fcn
    //
    if ( isset($litdates['posts'][$date_str]) ) { 
    	$litdate_posts = $litdates['posts'][$date_str];
	} else if ( isset($litdates['posts']) ) {
		$litdate_posts = $litdates['posts'];
		$ts_info .= "litdates['posts'][$date_str] not set<br />";
		//$ts_info .= "litdates['posts']: <pre>".print_r($litdates['posts'], true)."</pre>"; // tft
	} else {
		$litdate_posts = array(); // empty
	}
    if ( is_array($litdate_posts) ) { $num_litdate_posts = count($litdate_posts); } else { $num_litdate_posts = 0; }
    //$ts_info .= "SQL-Query: <pre>{$arr_posts->request}</pre>";
    $ts_info .= "num_litdate_posts: ".$num_litdate_posts."<br />";
    $ts_info .= $litdates['troubleshooting'];
    
    // If some posts were retrieved for dates calculated and/or assigned
    
    // Init
    $litdate_id = null;
    $litdate_id_secondary = null;
    
    // WIP
    if ( $num_litdate_posts == 0 ) {    
    	$ts_info .= "litdate_args: <pre>".print_r($litdate_args, true)."</pre>";
    }
    
    if ( $num_litdate_posts > 0 ) {
        
        // ... multiple matches? prioritize... pick one and then fetch the ONE litdate, collect, &c.
        
        $litdates = array();
        
        // ...loop through, check for holy days vs saints and martyrs... check for override settings...
        foreach ( $litdate_posts AS $litdate_post ) {
            
            $litdate_id = $litdate_post->ID;
            $ts_info .= "litdate_post->ID: ".$litdate_id."<br />";
            
            // Get the actual display_dates for the given litdate, to make sure the date in question hasn't been overridden			
			$display_dates_info = get_display_dates ( $litdate_id, $year );
			$ts_info .= $display_dates_info['info'];
			$display_dates = $display_dates_info['dates'];
			$ts_info .= "display_dates: <pre>".print_r($display_dates, true)."</pre>";
			if ( !in_array($date_str, $display_dates) ) {
				$ts_info .= "date_str: ".$date_str." is not one of the display_dates for this litdate.<br />";
				// Therefore don't show it.
				$litdate_id = null;
				continue;
			}
            
            // Get date_type (fixed, calculated, assigned)
            $date_type = get_post_meta( $litdate_id, 'date_type', true );
            $ts_info .= "date_type: ".$date_type."<br />";
            $is_secondary = get_post_meta($litdate_id, 'secondary', true);
            $ts_info .= "is_secondary: [".$is_secondary."]<br />";
            if ( $is_secondary ) {
            	$litdate_id_secondary = $litdate_id;
            	$litdate_id = null;
				$ts_info .= "litdate_id_secondary: ".$litdate_id_secondary."<br />";
				continue;
            }
            
            // Get category/priority
            $terms = get_the_terms( $litdate_id, 'liturgical_date_category' );
            //$ts_info .= "terms: ".print_r($terms, true)."<br />";
            
            $priority = 999;
            if ( $terms ) {
                
                foreach ( $terms as $term ) {
                    $term_priority = get_term_meta($term->term_id, 'priority', true);
                    $ts_info .= "term: ".$term->slug." :: term_priority: ".$term_priority."<br />";

                    if ( !empty($term_priority) ) {
                    	if ( $term_priority < $priority ) { // top priority is lowest number
                    		$priority = $term_priority;
                        	$ts_info .= "NEW priority: ".$priority."<br />";
                        } else if ( isset($top_priority) && $term_priority == $top_priority ) {
                        	$ts_info .= "term_priority is same as priority<br />";
                    	} else {
                    		$ts_info .= "term_priority is higher than priority<br />";
                    	}                        
                    } else {
                    	$ts_info .= "term_priority is not set for term ".$term->slug."<br />";
                    }
                }
                
            }
            
            $ts_info .= "priority: ".$priority."<br />";
            $key = $priority."-".$litdate_id; // this will cause sort by priority num, then by litdate_id
            $litdates[$key] = $litdate_id;
            //$litdates[$top_priority] = $litdate_post_id;
            //
            $ts_info .= "<hr />";
            
        }
        
        if ( count($litdates) > 0 ) {
        
        	$ts_info .= "litdates: ".print_r($litdates, true)."<br />";
        	uksort($litdates, sdg_arr_sort( 'key', null, 'ASC' ));
        	$ts_info .= "litdates sorted: ".print_r($litdates, true)."<br />";
       
			// Get first item in the associative array -- that's the one to use because it has the lowest priority number and therefore is most important		
			$top_key = array_key_first($litdates);
			$ts_info .= "top_key: ".$top_key."<br />";
			$litdate_id = $litdates[$top_key];
			$ts_info .= "litdate_id: ".$litdate_id."<br />";
			//
		
        }
    }
    
    // 
    if ( $litdate_id ) {
        
        // TODO: extract this out as a separate small function to check the actual display date for this litdate for the year in question
        
        $show_title = show_litdate_on_date( $litdate_id, $date_str );
        
        if ( $show_title == true ) {
        
        	$litdate_title = get_the_title( $litdate_id );
			$ts_info .= "litdate_title: ".$litdate_title."<br />";
			
			if ( $formatted == true ) {
			
				$ts_info .= "about to look for content and collect<br />";
				
				$litdate_content = get_the_content( null, false, $litdate_id ); // get_the_content( string $more_link_text = null, bool $strip_teaser = false, WP_Post|object|int $post = null )
				$collect_text = get_collect_text($litdate_id, $date_str);

				// TODO/atcwip: if no match by litdate_id, then check propers 1-29 by date (e.g. Proper 21: "Week of the Sunday closest to September 28")
		
				// If there's something other than the title available to display, then display the popup link
				// TODO: set width and height dynamically based on browser window dimensions
				$width = '650';
				$height = '450';
			
				if ( !empty($collect_text) ) {
				
					// TODO: modify title in case of Propers?
					
					$info .= '<a href="#!" id="dialog_handle_'.$litdate_id.'" class="calendar-day dialog_handle">';
					$info .= $litdate_title;
					$info .= '</a>';
					if ( $litdate_id_secondary ) { $info .= '<br /><span class="calendar-day secondary">'.get_the_title( $litdate_id_secondary ).'</span>'; }
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
					$info .= '</div>'; ///calendar-day-desc<br />

				} else {
					$ts_info .= "no collect_text found<br />";
					//$ts_info .= "collect_args: <pre>".print_r($collect_args, true)."</pre>";
					//$ts_info .= "collect_post: <pre>".print_r($collect_post, true)."</pre>";
					
					// If no content or collect, just show the day title
					$info .= '<span id="'.$litdate_id.'" class="calendar-day">'.$litdate_title.'</span>';
					if ( $litdate_id_secondary ) { $info .= '<br /><span class="calendar-day secondary">'.get_the_title( $litdate_id_secondary ).'</span>'; }
					$info .= '<br />';
				}
			
			} else {
				$info .= '<span id="'.$litdate_id.'">'.$litdate_title.'</span>'; // class="calendar-day"
			}
			
        }
        
    } else {
		
        $ts_info .= "no litdate found for display<br />";
		//$ts_info .= "params: <pre>".print_r($params, true)."</pre>";
		if ( $litdate_id_secondary ) { $info .= '<span class="calendar-day secondary">'.get_the_title( $litdate_id_secondary ).'</span><br />'; }
        
	}
	
	/*if ( $litdate_id_secondary ) { $info .= '<p class="calendar-day secondary">'.get_the_title( $litdate_id_secondary ).'</p>'; }*/
	
	// Append Event Special Notices content, as applicable
	if ( function_exists('get_special_date_content') && !$hide_special_notices ) { $info .= get_special_date_content( $the_date ); }
	
	// TS Info
	if ( $ts_info != ""&& ( $do_ts === true || $do_ts == "day_titles" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	$info .= "\n<!-- /get_day_title -->\n";
	
	return $info;
	
}

// Function(s) to calculate variable liturgical_dates

function get_liturgical_date_calc_id ( $year = null )
{
	// WIP
}

function get_basis_date ( $year = null, $liturgical_date_calc_id = null, $calc_basis = null, $calc_basis_id = null, $calc_basis_field = null ) {

	//if ( empty($calc_basis) ) { return null; }
	
	$info = "";
	$basis_date_str = null;
	$basis_date = null;
	
	$info .= ">>> get_basis_date <<<<br />";
	
	if ( $calc_basis == 'christmas' ) {
		$basis_date_str = $year."-12-25";          
	} else if ( $calc_basis == 'epiphany' ) {                
		$basis_date_str = $year."-01-06";
	} else if ( $calc_basis_id ) {
	
		// If the $calc_basis is a post_id, get the corresponding date_calculation for the given year
		// TODO: run a DB query instead to find rows relevant by $year? -- maybe more efficient than retrieving all the rows
		if ( have_rows('date_calculations', $calc_basis_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
			while ( have_rows('date_calculations', $calc_basis_id) ) : the_row();
				$date_calculated = get_sub_field('date_calculated'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
				$year_calculated = substr($date_calculated, 0, 4);
				if ( $year_calculated == $year ) {
					$basis_date_str = $date_calculated;
				}
			endwhile;
		} // end if
	
	} else if ( date('Y-m-d',strtotime($calc_basis)) == $calc_basis 
				|| strtolower(date('F d',strtotime($calc_basis))) == strtolower($calc_basis) 
				|| strtolower(date('F d Y',strtotime($calc_basis))) == strtolower($calc_basis) 
		) {
		
		// WIP: deal w/ possibilty that calc_basis is a date (str) -- in which case should be translated as the basis_date
		// If the calc_basis date includes month/day only, then add the year
		if ( strtolower(date('F d',strtotime($calc_basis))) == $calc_basis ) {
			$calc_basis = $calc_basis." ". $year;
			// Then convert it to Y-m-d format
			$calc_basis = date('Y-m-d',strtotime($calc_basis));
		}
		$basis_date_str = $calc_basis;
		
	} else if ( $liturgical_date_calc_id && $calc_basis_field ) {
		$basis_date_str = get_post_meta( $liturgical_date_calc_id, $calc_basis_field, true);
	} else {
		// If the calc_basis starts with a "the ", trim that off
		if ( substr($calc_basis,0,4) == "the ") { $calc_basis = substr($calc_basis,4); }
		// Append four-digit year (TODO: check first to see if it's already there? though when would that be the case...)
		$calc_basis .= " ".$year;
		$basis_date_str = date('Y-m-d',strtotime($calc_basis));
	}

	// If no basis date string has yet been established, then default to January first of the designated year
	if ( $basis_date_str == "" ) {
		$basis_date_str = $year."-01-01";
		//if ( $verbose == "true" ) { $info .= "(basis date defaults to first of the year)<br />"; }
	}
	//if ( $verbose == "true" ) { $info .= "basis_date_str: $basis_date_str ($calc_basis)<br />"; } // '<span class="notice">'.</span> // ($calc_basis // $calc_basis_field)

	if ( $basis_date_str ) {
		// Get the basis_date from the string version
		$basis_date = strtotime($basis_date_str);
		//$basis_date_weekday = strtolower( date('l', $basis_date) );	
		//if ( $verbose == "true" ) { $info .= "basis_date: $basis_date_str ($basis_date_weekday)<br />"; } // .'<span class="notice">'.'</span>' //  ($calc_basis // $calc_basis_field)
	}
	
	return $basis_date;

}

function get_calc_bases_from_str ( $date_calc_str = "", $ids_to_exclude = array() ) {
	
	// Init vars
	$arr_info = array();
	$calc_bases = array();
	$info = "";
	
	// litdate found in $date_calc_str?
	// query titles of  litdates to see if any are found in $date_calc_str (complete)
	// hm as it currently is, it's looking for entire $date_calc_str in title -- but what we need is to look for titles within date_calc_str...
	// wip
	// Set up query args
    $wp_args = array(
		'post_type'		=> 'liturgical_date',
		'post_status'   => 'publish',
		'posts_per_page'=> -1,
        'orderby'       => 'title',
        'order'         => 'ASC',
        //'return_fields' => 'ids',
        '_search_title'	=> $date_calc_str,
	);
	
	if ( !empty($ids_to_exclude) ) { $wp_args['post__not_in'] = $ids_to_exclude; }

    // Run the query
	$arr_posts = new WP_Query( $wp_args );
    
    $info .= "WP_Query run as follows:";
    $info .= "<pre>args: ".print_r($wp_args, true)."</pre>";
    $info .= "[".count($arr_posts->posts)."] posts found matching date_calc_str.<br />";
    //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre>";
	if ( count($arr_posts->posts) > 0 ) {
		foreach ( $arr_posts->posts AS $post ) {
			// Check to make sure this isn't an "Eve of" or "Week of" date before adding it to the array
			$calc_basis = strtolower($post->post_title);
			if ( strpos($calc_basis, 'eve of ') == false && strpos($calc_basis, 'week of ') == false ) {
				$calc_bases[] = array( 'post_id' => $post->ID, 'basis' => $calc_basis );
			} else {
				//
			}
		}
	} else {
		// if not...
		// lit basis found in $date_calc_str?
		$liturgical_bases = array('advent' => 'advent_sunday_date', 'christmas' => 'December 25', 'epiphany' => 'January 6', 'ash wednesday' => 'ash_wednesday_date', 'lent' => 'ash_wednesday_date', 'easter' => 'easter_date', 'ascension day' => 'ascension_date', 'pentecost' => 'pentecost_date' );
		
		// Get the liturgical date info upon which the calculation should be based (basis extracted from the date_calc_str)
		foreach ( $liturgical_bases AS $basis => $basis_field ) {
			if (stripos($date_calc_str, $basis) !== false) {
				$calc_bases[] = array( 'basis' => $basis, 'basis_field' => $basis_field );
				//if ( $verbose == "true" ) { $info .= "&rarr; "."calc_basis ".$basis." (".$basis_field.") found in date_calc_str.<br />"; }
			}
		}
	}
	
	//return $calc_bases;
	
	$arr_info['info'] = $info;
	$arr_info['calc_bases'] = $calc_bases;
	//
	
	return $arr_info;
	
}

function get_calc_boias_from_str ( $date_calc_str = "" ) {
	
	$calc_boias = array();
	
	$boias = array('before', 'of', 'in', 'after'); // before/of/in/after the basis_date/season? 
	
	// can we do this without the loop -- match str against array of substr?
	foreach ( $boias AS $boia ) {
		if ( preg_match_all('/\s*'.$boia.'\s*/', $date_calc_str, $matches, PREG_OFFSET_CAPTURE) ) {
			//$info .= "&rarr; "."boia '$boia' found in date_calc_str<br />"; // 
			//$calc_boia = strtolower($boia);
			$calc_boias[] = strtolower($boia);
			if ( count($matches) > 1 ) { 
				$complex_formula = true;
				//$info .= count($matches)." boia matches for '$boia'<br />";
				//foreach ( $matches as $match ) { }
			}
			//if ( $verbose == "true" ) { $info .= "boia matches: ".print_r($matches, true)."<br />"; } //<pre></pre>
		}
	}
	
	return $calc_boias;
	
}

function get_calc_weekdays_from_str ( $date_calc_str = "" ) {
	
	$calc_weekdays = array();
	
	$weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
	
	// What's the weekday for the date to be calculated?
	$calc_weekdays = array();
	foreach ( $weekdays AS $weekday ) {
		if (stripos($date_calc_str, $weekday) !== false) {
			//$info .= "&rarr; "."weekday '$weekday' found in date_calc_str<br />";
			$calc_weekdays[] = strtolower($weekday);
		}
	}	
	
	return $calc_weekdays;
	
}

function parse_date_str ( $args = array() ) {
	
	//
	$arr_info = array();
	$arr_elements = array();
	$complex_formula = false;
	//
	$info = "";
	$info .= '<strong>&gt;&gt;&gt; parse_date_str &lt;&lt;&lt;</strong><br />';
	$indent = "&nbsp;&nbsp;&nbsp;&nbsp;"; // TODO: define this with global scope for all plugin functions
	
	// Defaults
	$defaults = array(
		'year'				=> null,
		'date_calc_str'		=> null,
		'verbose'			=> true,
		'ids_to_exclude'	=> array(), // IDs to exclude -- e.g. when dealing w/ "Eve of" dates -- so that "eve of" post doesn't find itself as a possible basis
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	//
	if ( $verbose == "true" ) { $info .= "args: <pre>".print_r($args, true)."</pre>"; }
	$date_calc_str_bk = $date_calc_str; // copy the $date_calc_str to a new variable so we can preserve the original while making mods as needed
	//
	$liturgical_bases = array('advent' => 'advent_sunday_date', 'christmas' => 'December 25', 'epiphany' => 'January 6', 'ash wednesday' => 'ash_wednesday_date', 'lent' => 'ash_wednesday_date', 'easter' => 'easter_date', 'ascension day' => 'ascension_date', 'pentecost' => 'pentecost_date' ); // get rid of this here? only needed in this function for FYI components info -- not really functional
	//
    //$numbers = array('one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9); // WIP
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    $weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
    $boias = array('before', 'of', 'in', 'after'); // before/of/in/after the basis_date/season?
	//
	$components = array();
	$calc_basis = null;
	$calc_basis_field = null;
	$calc_basis_id = null;
	$calc_boia = null;
	$calc_weekday = null;
	$calc_interval = null;
	//
	
	// Loop through all the components of the exploded date_calc_str and determine component type
	// WIP -- why do this? -- maybe to determine early on if this is a complex formula that must be broken down into sub-formulas... 
	// "after the", "before the", "in the"(?)
	// e.g. Corpus Christi: "thursday after the 1st sunday after pentecost"
	// if str contains either multiple calc_bases OR multiple boias, then break it into parts (nested) and process core first, then final based on calc core date
	
	$calc_components = explode(" ", $date_calc_str);
	if ( $verbose == "true" ) { $info .= "[".count($calc_components)."] calc_components: ".print_r($calc_components,true)."<br />"; }
	$component_info = "";
	$previous_component = "";
	$previous_component_type = null;
	$i = 1;
	foreach ( $calc_components as $component ) {
		
		$component = strtolower($component);
		
		// First check to see if the component is a straight-up date! // date('Y-m-d', $calc_date) // (YYYY-MM-DD) //$calc_date_str = date('Y-m-d', $calc_date);
		if ( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $component) ) {
			$component_info .= $indent."component '".$component."' is a date<br />";
			$previous_component_type = "date";
		} else if ( array_key_exists($component, $liturgical_bases) ) {
			$component_info .= $indent."component '".$component."' is a liturgical_base<br />";
			$previous_component_type = "liturgical_base";
			// >> save as calc_basis, replacing loop below?
			// WIP
			// if multiple bases are found, proceed with the core subclause and then repeat calc...
			//
		} else if ( in_array($component, $months) ) {
			$component_info .= $indent."component '".$component."' is a month<br />";
			$previous_component_type = "month";
		} else if ( in_array($component, $weekdays) || in_array(substr($component, 0, strlen($component)-1), $weekdays) ) {
			$component_info .= $indent."component '".$component."' is a weekday<br />";
			if ( substr($component, -1) == "s" ) { $component_info .= $indent."component '".$component."' is plural<br />"; }
			$previous_component_type = "weekday";
		} else if ( in_array($component, $boias) ) {
			$component_info .= $indent."component '".$component."' is a boia<br />";
			$previous_component_type = "boia";
			// Potential calc_basis?
			if ( empty($calc_basis)) {
				$calc_basis = trim(substr($date_calc_str,strpos($date_calc_str,$component)+strlen($component)));
				$component_info .= $indent.'calc_basis: '.$calc_basis."<br />";
			}
		} else if ( contains_numbers($component) ) { // what about "last"? do we need to deal with that here? or third? fourth? etc?
			$component_info .= $indent."component '".$component."' is numeric/intervalic<br />";
			//$component_info .= $indent."component '".$component."' is numeric/intervalic --> matches: ".print_r($matches,true)."<br />";
			// WIP...
			// Translate words to digits as needed
			/*if ( !is_int($component) ) {
				$component_translated = sdg_word_to_digit($component);
				if ( !is_int($component_translated) ) {
					// wip
				}
				//$date_calc_str = str_replace($component, $component_translated, $date_calc_str);
				$component_info .= $indent."component_translated: '".$component_translated."'<br />";
			}*/
			if ( $previous_component_type == "month" ) {
				$component_info .= $indent."... and previous_component '".$previous_component."' is a month<br />";
				if ( empty($calc_basis)) { $calc_basis = $previous_component." ".$component; }
				// if not folloewd by year, add year wip...
				// only if last component?...
				//if ( $i == count($calc_components) ) { $calc_basis .= " ".$year; } // do this later via get_basis_date
			} else {
				$component_info .= $indent."... and previous_component '".$previous_component."' is a ".$previous_component_type."<br />";
			}
			$previous_component_type = "numeric";
		} else if ($component == "the" ) { // wip
			$component_info .= $indent."component '".$component."' is expendable<br />";
			$previous_component_type = "expendable";
		} else {
			$component_info .= $indent."component '".$component."' is ???<br />";
			$previous_component_type = "unknown";
		}
		$previous_component = $component;
		$i++;
	}
	if ( $verbose == "true" ) { $info .= "component_info (FYI): <br />".$component_info."<br /><hr />"; }
	
	// Determine the calc components
	// WIP!!!
	// TODO: check to see if multiple components come after the boia -- e.g. 1st sunday after august 15 -- and/or see if there's a sequence of components consisting of MONTH INT
	
	// 1. Liturgical calc basis (calc_basis)
	//if ( $verbose == "true" ) { $info .= ">> get_calc_bases_from_str<br />"; }
	if ( $calc_basis ) {
		$calc_basis = strtolower($calc_basis);
		if ( array_key_exists($calc_basis, $liturgical_bases) ) {
			//if ( $verbose == "true" ) { $info .= "calc_basis: $calc_basis is a liturgical_base<br />"; }
			$calc_bases = array();  // calc_bases array needs to be array of arrays to match get_calc_bases_from_str results
			$basis_field = $liturgical_bases[$calc_basis];
			$calc_bases[] = array('basis' => $calc_basis, 'basis_field' => $basis_field );
			$calc_bases_info = array( 'info' => "calc_basis: $calc_basis is a liturgical_base<br />", 'calc_bases' => $calc_bases );
		} else {
			if ( $verbose == "true" ) { $info .= ">> get_calc_bases_from_str using str calc_basis: $calc_basis<br />"; }
			$calc_bases_info = get_calc_bases_from_str($calc_basis, $ids_to_exclude);
		}		
	} else {
		if ( $verbose == "true" ) { $info .= ">> get_calc_bases_from_str using str date_calc_str: $date_calc_str<br />"; }
		$calc_bases_info = get_calc_bases_from_str($date_calc_str, $ids_to_exclude);
	}
	//$calc_bases_info = get_calc_bases_from_str($date_calc_str);
	$calc_bases = $calc_bases_info['calc_bases'];
	if ( $verbose == "true" ) {
		//$info .= "calc_bases: <pre>".print_r($calc_bases, true)."</pre>";
		$info .= $calc_bases_info['info']."<br />";
	}
	if ( empty($calc_bases) ) {
		if ( $verbose == "true" ) { $info .= "No calc_basis found.<br />"; }
	} else if ( count($calc_bases) > 1 ) {
		$complex_formula = true;
		$info .= '<span class="notice">More than one calc_basis found!</span><br />';
		$info .= "calc_bases: <pre>".print_r($calc_bases, true)."</pre>";
		//$info .= '</div>';
		//$calc['calc_info'] = $info;
		//return $calc; // abort early -- we don't know what to do with this date_calc_str
		foreach ( $calc_bases as $cb_tmp ) {
			if ( $cb_tmp['basis'] == $calc_basis ) {
				$info .= "cb_tmp basis: ".$cb_tmp['basis']." is identical to calc_basis<br />";
				$calc_basis = $cb_tmp['basis'];
				$calc_basis_id = $cb_tmp['post_id'];
			}
		}
		//
	} else if ( count($calc_bases) == 1 ) {
		if ( $verbose == "true" ) { $info .= "Single calc_basis found.<br />"; }
		$cb = $calc_bases[0];
		if ( is_array($cb) ) {
			$calc_basis = $cb['basis'];
			if ( isset($cb['post_id']) ) {
				$calc_basis_id = $cb['post_id'];
			} else if ( isset($cb['basis_field']) ) {
				$calc_basis_field = $cb['basis_field'];
			}
			//$info .= "cb: <pre>".print_r($cb, true)."</pre>";
		} else {
			$calc_basis = $cb;
		}
		
		// clean up the calc_basis -- e.g. if we were looking for "the third sunday of advent" and got "the third sunday of advent (gaudete)"
		// Remove anything in parentheses or brackets
		if ( $verbose == "true" ) { $info .= "About to remove bracketed info from calc_basis.<br />"; }
		$calc_basis = remove_bracketed_info($calc_basis,true);
		$info .= "calc_basis: $calc_basis<br />";
	}
			
	if ( $calc_basis ) { $components['calc_basis'] = $calc_basis; }
	if ( $calc_basis_id ) { $components['calc_basis_id'] = $calc_basis_id; }
	if ( $calc_basis_field ) { $components['calc_basis_field'] = $calc_basis_field; }
	if ( $verbose == "true" ) { $info .= "calc_basis: $calc_basis // calc_basis_id: $calc_basis_id // calc_basis_field: $calc_basis_field<br />"; }
	
	// 2. BOIAs
	// Does the date to be calculated fall before/after/of/in the basis_date/season?
	if ( $calc_basis ) {
		
		// get the calc_str without the already determined calc_basis
		if ( $verbose == "true" ) { $info .= "About to replace calc_basis '$calc_basis' in date_calc_str '$date_calc_str'<br />"; }
		$date_calc_str = trim(str_ireplace($calc_basis,"",$date_calc_str));
		if ( strtotime($date_calc_str) ) { $info .= 'date_calc_str: "'.$date_calc_str.'" is parseable by strtotime<br />'; } //else { $info .= 'date_calc_str: "'.$date_calc_str.'" is NOT parseable by strtotime<br />'; }
		if ( strtotime($date_calc_str."today") ) { $info .= 'date_calc_str: "'.$date_calc_str.'" is parseable by strtotime with the addition of the word "today"<br />'; } //else { $info .= 'date_calc_str: "'.$date_calc_str.'" is NOT parseable by strtotime with the addition of the word "today"<br />'; }
		if ( $verbose == "true" ) { $info .= "get_calc_boias_from_str from modified date_calc_str: $date_calc_str<br />"; }
	} else {
		if ( $verbose == "true" ) { $info .= "get_calc_boias_from_str from unmodified date_calc_str<br />"; }
	}
	$calc_boias = get_calc_boias_from_str($date_calc_str);
	if ( empty($calc_boias) ) {
		if ( $verbose == "true" ) { $info .= "No boias found.<br />"; }
	} else if ( count($calc_boias) > 1 ) {
		$complex_formula = true;
		$info .= '<span class="notice">More than one calc_boia found!</span><br />';
		$info .= "calc_boias: ".print_r($calc_boias, true)."<br />"; //<pre></pre>
		//$info .= '</div>';
		//$calc['calc_info'] = $info;
		//return $calc; // abort early -- we don't know what to do with this date_calc_str
	} else if ( count($calc_boias) == 1 ) {
		$calc_boia = $calc_boias[0];
		$components['calc_boia'] = $calc_boia;
		if ( $verbose == "true" ) { $info .= "calc_boia: $calc_boia<br />"; }
	}
	
	// 3. Weekdays
	$calc_weekdays = get_calc_weekdays_from_str($date_calc_str);
	if ( empty($calc_weekdays) ) {
		if ( $verbose == "true" ) { $info .= "No calc_weekday found.<br />"; }
	} else if ( count($calc_weekdays) > 1 ) {
		$complex_formula = true;
		$info .= '<span class="notice">More than one calc_weekday found!</span><br />';
		$info .= "calc_weekdays: ".print_r($calc_weekdays, true)."<br />"; //<pre></pre>
		//$info .= '</div>';
		//$calc['calc_info'] = $info;
		//return $calc; // abort early -- we don't know what to do with this date_calc_str
	} else if ( count($calc_weekdays) == 1 ) {
		$calc_weekday = $calc_weekdays[0];
		$components['calc_weekday'] = $calc_weekday;
		if ( $verbose == "true" ) { $info .= "calc_weekday: $calc_weekday<br />"; }
	}
	//
	
	// 4. Calc interval(s)
	// WIP 240903
	// translate words to digits etc -- move some functionality from calc_date_from_components
	// $calc_interval
	// in combo with calc_boia and calc_weekday, translate date_calc_str into something that can be handled by php strtotime
	// e.g. two sundays before >> 2 sundays previous >> previous sunday - 6 days >>> previous sunday - X weeks + 1 day
	
	// phase this out? or generalize?
	// If it's a complex formula, extract the sub_formula upon which the final calc will be based
	if ( $complex_formula ) {
		if ( $verbose == "true" ) { $info .= "This is a complex_formula => extract the sub_formula<br />"; }
		
		if ( strpos(strtolower($date_calc_str), 'after the ') !== false ) {
			$sub_calc_str = trim(substr( $date_calc_str, strpos($date_calc_str, "after the ")+9 )); // WIP 231204 -- generalize beyond Corpus Christi?
		} else {
			$sub_calc_str = ""; // ???
		}
		$info .= "sub_calc_str: $sub_calc_str<br />";
		
		$components['date_calc_str'] = $sub_calc_str;
		//if ( count($calc_weekdays) > 1 ) { $components['calc_weekday'] = $calc_weekdays[1]; }
		//
		$calc_weekdays = get_calc_weekdays_from_str($sub_calc_str);
		if ( count($calc_weekdays) == 1 ) {
			$components['calc_weekday'] = $calc_weekdays[0];
		}
		//
		$arr_elements['sub_calc_str'] = $components;
		//
		if ( strpos(strtolower($date_calc_str), 'after the ') !== false ) {
			$super_calc_str = trim(substr( $date_calc_str, 0, strpos($date_calc_str, "after the")+9 ))." sub_calc_str"; // WIP 231204
		} else {
			$super_calc_str = ""; // ???
		}
		
		$components['date_calc_str'] = $super_calc_str;
		//
		$calc_weekdays = get_calc_weekdays_from_str($super_calc_str);
		if ( count($calc_weekdays) == 1 ) {
			$components['calc_weekday'] = $calc_weekdays[0];
		}
		//
		$arr_elements['super_calc_str'] = $components;
		$info .= "super_calc_str: $super_calc_str<br />";
	} else {
		$components['date_calc_str'] = $date_calc_str;
		$arr_elements['calc_str'] = $components;
	}
	// get core sub-formula...
	// "after the", "before the", "in the"(?)
	// break complex formulas into separate elements
	//
	// return substrings with their components
	//
	$arr_info['info'] = $info;
	$arr_info['elements'] = $arr_elements;
	//
	
	return $arr_info;
	
}

// WIP: Translate the date calculation string into components that can be used to do date math, and then do that math to calculate the date
function calc_date_from_str( $args = array() ) {
	
	// Defaults
	$defaults = array(
		'year'				=> null,
		'date_calc_str'		=> null,
		'verbose'			=> false,
		'ids_to_exclude'		=> array(),
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	// Abort if date_calc_str or year is empty
	if ( empty($date_calc_str) || empty($year) ) { return false; }
	
	// Init vars
	$arr_info = array();
	$info = "";
	$calc_date = null;
	$indent = "&nbsp;&nbsp;&nbsp;&nbsp;"; // TODO: define this with global scope for all plugin functions
	
	$info .= '<strong>&gt;&gt;&gt; calc_date_from_str &lt;&lt;&lt;</strong><br />';
	if ( $verbose == "true" ) { $info .= "year: ".$year."<br />"; }
	if ( $verbose == "true" ) { $info .= "date_calc_str: ".$date_calc_str."<br />"; }
	
	// Find the liturgical_date_calc post for the selected year
	//$liturgical_date_calc_id = get_liturgical_date_calc_id ( $year ); // WIP	
	// (liturgical_date_calc records contain the dates for Easter, Ash Wednesday, &c. per year)
	// TODO: make this a separate function?
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
		if ( $verbose == "true" ) { $info .= "No matching liturgical_date_calc_post for wp_args: ".print_r($wp_args,true)."<br />"; } // <pre></pre>
		$liturgical_date_calc_id = null;
		// TBD: abort?
	}
	
	// Parse the date string
	$args = array( 'year' => $year, 'date_calc_str' => $date_calc_str, 'verbose' => $verbose, 'ids_to_exclude' => $ids_to_exclude );
	$date_elements_info = parse_date_str ( $args );
	$info .= $date_elements_info['info'];
	$date_elements = $date_elements_info['elements'];
	$calc_date = null;
	$new_basis_date_str = null;
	//
	
	// >> loop through elements foreach $elements as $element => $components
	foreach ( $date_elements as $element => $components ) { //foreach ( $date_elements as $components ) {
	
		$info .= "element: [".$element."]<br />";
		//
		if ( isset($components['date_calc_str']) ) { $date_calc_str = $components['date_calc_str']; }
		//
		if ( isset($components['calc_basis']) && strtolower($date_calc_str) == $components['calc_basis'] ) { // Easter, Christmas, Ash Wednesday, Pentecost", &c.=		
			$calc_date = get_basis_date( $year, $liturgical_date_calc_id, $components['calc_basis'], $components['calc_basis_field'] );
			$info .= "date to be calculated is same as basis_date.<br />";		
		} else {
			//
			if ( $new_basis_date_str ) { 
				$info .= "new_basis_date_str: ".$new_basis_date_str."<br />";
				// TODO: str_replace "the sub_calc_str" in date_calc_str
				$date_calc_str = str_replace("the sub_calc_str", $new_basis_date_str, $date_calc_str );
				//$date_calc_str = str_replace("sub_calc_str", $new_basis_date_str, $date_calc_str );
				$components['calc_basis'] = $new_basis_date_str;
				$components['calc_basis_field'] = null;
				$components['date_calc_str'] = $date_calc_str;
			}
			//
			$components['year'] = $year;
			$components['liturgical_date_calc_id'] = $liturgical_date_calc_id;
			//$components['date_calc_str'] = $date_calc_str;
			$components['verbose'] = $verbose;
			//
			//$info .= "[".$element."] components: <pre>".print_r($components, true)."</pre>";
		
			$calc = calc_date_from_components( $components );
			$info .= $calc['info'];
			$calc_date = $calc['date'];
		}
		// WIP -- if more than one element, get $calc_date as $new_basis_date from first calc and pass it to second in loop
		if ( is_int($calc_date) ) {
			$new_basis_date_str = date("Y-m-d", $calc_date );
		} else {
			if ( $verbose == "true" ) {
				if ( empty($calc_date) ) {
					$info .= '<span class="notice">'."Cannot create new_basis_date_str -- calc_date is empty</span>".'<br />';
				} else {
					$info .= '<span class="notice">'."Cannot create new_basis_date_str from calc_date: ".$calc_date." because it's a string</span>".'<br />';
				}				
			}
		}
	}
	
	
    if ( $calc_date ) {
    	if ( $verbose == "true" ) { $info .= 'calc_date: '.$calc_date.'<br />'; } //'<span class="notice">'.'</span>'.
    	if ( is_int($calc_date) ) {
    		$info .= '<span class="notice">'.'calc_date (timestamp >> formatted): '.date('Y-m-d', $calc_date).'</span>'.'<br />';
    	} else {
    		$info .= '<span class="notice">'."calc_date not a valid date: ".$calc_date." (string)</span>".'<br />'; //if ( $verbose == "true" ) { }
    		$calc_date = null;
    	}
    } 
        
    $arr_info['calc_date'] = $calc_date;
    $arr_info['calc_info'] = $info;
    
    return $arr_info;

}

function calc_date_from_components ( $args = array() ) {

	// WIP
	
	// Init vars
	$arr_info = array();
	$info = "";
	$calc_date = null;
	//
	$indent = "&nbsp;&nbsp;&nbsp;&nbsp;"; // TODO: define this with global scope for all plugin functions 
	
	// Defaults
	$defaults = array(
		'year'				=> null,
		'liturgical_date_calc_id'=> null,
		'date_calc_str'=> null,
		'calc_basis'		=> null,
		'calc_basis_id'		=> null,
		'calc_basis_field'	=> null,
        'calc_boia'			=> null,
        'calc_weekday'		=> null,
        'verbose'			=> false,
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	//
	$info .= '<strong>&gt;&gt;&gt; calc_date_from_components &lt;&lt;&lt;</strong><br />';
	if ( $verbose == "true" ) { $info .= "args: <pre>".print_r($args, true)."</pre>"; }
	  
	// Get the basis date in the given year, from the Liturgical Date Calculations CPT (liturgical_date_calc)
	$basis_date = get_basis_date( $year, $liturgical_date_calc_id, $calc_basis, $calc_basis_id, $calc_basis_field );
	if ( $calc_basis == "epiphany" ) {
		$num_sundays_after_epiphany = get_post_meta( $liturgical_date_calc_id, 'num_sundays_after_epiphany', true);
	}
	if ( $verbose == "true" && !empty($basis_date) ) { 
		$info .= "basis_date: $basis_date (".date('Y-m-d (l)', $basis_date).") <br />-- via get_basis_date for year: $year, liturgical_date_calc_id: $liturgical_date_calc_id, calc_basis: $calc_basis, calc_basis_id: $calc_basis_id, calc_basis_field: $calc_basis_field<br />";
	}
	
	// Check to see if the date to be calculated is in fact the same as the base date
	if ( strtolower($date_calc_str) == $calc_basis ) { // Easter, Christmas, Ash Wednesday", &c.=
		
		$calc_date = $basis_date;
		$info .= "date to be calculated is same as basis_date.<br />";
		
	} else {
        
		$calc_formula = null;
		$calc_interval = null;
		// TODO: deal w/ propers -- e.g. "Week of the Sunday closest to May 11"
		
		// Take a closer look at the basis_date
		if ( is_numeric($basis_date) && $verbose == "true" ) { $info .= "basis_date is a timestamp.<br />"; }
		
		// Check to see if the basis_date is a Sunday
		$basis_date_weekday = strtolower( date('l', $basis_date) );
		if ( $basis_date_weekday != "" && $basis_date_weekday != 'sunday' ) {
			
			// If the basis_date is NOT a Sunday, then get the date of the first_sunday of the basis season                 
			$first_sunday = strtotime("next Sunday", $basis_date);
			if ( $verbose == "true" ) { $info .= "first_sunday after basis_date is ".date("Y-m-d", $first_sunday)."<br />"; }
		
		} else if ( $basis_date ) {
			
			$first_sunday = $basis_date;
			if ( $verbose == "true" ) { $info .= "first_sunday is equal to basis_date.<br />"; }
			
		}
				
        // ** Extract components of date_calc_str & calculate date for $year
		// ** Determine the calc_interval -- number of days/weeks...
        if ( contains_numbers($date_calc_str) ) {
			
			// TODO/wip: also check for "two" etc
			if ( $verbose == "true" ) { $info .= "date_calc_str contains numbers.<br />"; }
			
			// Determine the calc_interval
			// WIP deal w/ multiple value possibilities for weekday, boia
			if ( !is_array($calc_weekday) && !is_array($calc_boia) ) { //&& !empty($calc_weekday) && !empty($calc_boia)
				// TODO: fix this
				$numbers = extract_numbers($date_calc_str);
				if ( $verbose == "true" ) { $info .= "numbers: ".print_r($numbers,true)."<br />"; } //<pre></pre>
				if ( count($numbers) == 1 ) {
					$calc_interval = $numbers[0];
					//$calc_interval = "";
				} else {
					$calc_interval = str_replace([$calc_basis, $calc_weekday, $calc_boia], '', strtolower($date_calc_str) );
					$calc_interval = str_replace(['the', 'th', 'nd', 'rd', 'st'], '', strtolower($date_calc_str) );
				}
				$calc_interval = trim( $calc_interval );
			}
			if ( $verbose == "true" && !empty($calc_interval) ) { $info .= "calc_interval: $calc_interval<br />"; }
			
			//if ( $calc_boia == ("in" || "of") ) { // Advent, Easter, Lent
			if ( !empty($calc_interval) && ( 
				( $calc_basis == "advent" && $calc_boia != "before" ) 
				|| ( $calc_basis == "easter" && $calc_boia == "of" )
				|| ( strtolower(date('F d',strtotime($calc_basis))) == strtolower($calc_basis) )
				) ) {
				
				$calc_interval = (int) $calc_interval - 1; // Because Advent Sunday is first Sunday of Advent, so 2nd Sunday is basis_date + 1 week, not 2
			
			} else if ( $first_sunday == $basis_date && $date_calc_str == "first sunday of"  ) {
			
				if ( $verbose == "true" ) { $info .= "data_calc_str == first sunday of && first_sunday == basis_date &#8756; calc_date = first_sunday<br />"; }
				$calc_date = $first_sunday;
			
			} else if ( $first_sunday != $basis_date ) {
			
				if ( $verbose == "true" ) { $info .= "first_sunday NE basis_date<br />"; }
				
				if ( $calc_interval ) { // && is_int($calc_interval)
					if ( $verbose == "true" ) { $info .= "Subtracting one from calc_interval ($calc_interval - 1)<br />"; }
					$calc_interval = $calc_interval - 1; // because math is based on first_sunday + X weeks. -- but only if calc_weekday is also Sunday? WIP
				}
				// ???
				if ( $calc_interval === 0 ) {
					$calc_date = $first_sunday;
					if ( $verbose == "true" ) { $info .= "Set calc_date = first_sunday ($first_sunday)<br />"; }
				}
				
			}
			
			if ( $verbose == "true" && !empty($calc_interval) ) { $info .= "calc_interval (final): $calc_interval<br />"; }
			
		} else if ( strpos(strtolower($date_calc_str), 'last') !== false ) {
			
			// e.g. "Last Sunday after the Epiphany"; "Last Sunday before Advent"; "Last Sunday before Easter"
			//$info .= $indent."LAST<br />"; // tft
			if ( $calc_basis == "epiphany" ) {
				$calc_interval = $num_sundays_after_epiphany; // WIP 240113
			} else if ( $calc_basis == "easter" ) { // && $calc_boia == "before"
				$calc_formula = "previous Sunday"; //$calc_formula = "Sunday before";
			} else if ( $date_calc_str == "last sunday before advent" ) {
				$calc_formula = "previous Sunday"; //$calc_formula = "Sunday before";
			}
			
		}
            
		// If the calc_formula hasn't already been determined, build it
		if ( empty($calc_date) && $calc_formula == "" ) {
			
			if ( $verbose == "true" ) { $info .= "About to build calc_formula...<br />"; }
			
			if ( !empty($calc_interval) && strpos(strtolower($calc_interval), 'days') !== false ) {
			
				if ( $verbose == "true" ) { $info .= "Calc by days +/-...<br />"; }
				
				//
				if ( $calc_interval && $calc_boia == "before" ) {
					$calc_formula = "-".$calc_interval;
				} else if ( $calc_interval && $calc_boia == "after" ) {
					$calc_formula = "+".$calc_interval;        
				}
				
			} else {
			
				if ( $calc_basis != "" && $calc_weekday == "sunday" ) {

					if ( ($calc_interval > 1 && $calc_boia != "before") || ($calc_interval == 1 && $calc_boia == ("after" || "in") ) ) {
						$calc_formula = "+".$calc_interval." weeks";
						$basis_date = $first_sunday;
					} else if ( ($calc_interval > 1 && $calc_boia == "before" ) ) {
						$calc_formula = "-".$calc_interval." weeks";
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
					if ( !empty($calc_weekday) ) {
						$calc_formula = $prev_next." ".$calc_weekday; // e.g. "previous Friday";
					} else {
						$calc_formula = $prev_next." day"; // e.g. "previous day";
					}				
				
				}
			
			}
			
		}
		
		// If there's no $calc_formula yet, use the date_calc_str directly
		if ( empty($calc_formula) && empty($calc_date) ) {
			$info .= '<span class="notice">'."calc based directly on date_calc_str</span><br />"; // .'</span>'
			if ( $calc_boia != "after" ) {
				$calc_formula = $date_calc_str;               
			} else {
				if ( $verbose == "true" ) { $info .= '<span class="notice">'."Unable to determine calc_formula -- calc_boia: \"$calc_boia\"; calc_date: $calc_date</span><br />"; }
			}
		}
		
		//$info .= $indent.">> date_calc_str: $date_calc_str<br />"; // tft
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
			
			$ash_wednesday_date = get_post_meta( $liturgical_date_calc_id, 'ash_wednesday_date', true);
			if ( empty($ash_wednesday_date) ) { 
				$info .= $indent."No ash_wednesday_date found for liturgical_date_calc_id: $liturgical_date_calc_id<br />";
				// TBD: abort?
			}
			if ( $verbose == "true" ) { $info .= "ash_wednesday_date: ".$ash_wednesday_date."<br />"; }
			
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
			
			$advent_sunday_date = get_post_meta( $liturgical_date_calc_id, 'advent_sunday_date', true);
			if ( $verbose == "true" ) { $info .= "advent_sunday_date: ".$advent_sunday_date."<br />"; }
			
			// WIP
			//$pentecost_date = get_post_meta( $liturgical_date_calc_id, 'pentecost_date', true);
			//if ( $verbose == "true" ) { $info .= "pentecost_date: ".$pentecost_date."<br />"; }
			
			//if ( $calc_date > strtotime($advent_sunday_date) ) {
			if ( $calc_date > strtotime("previous Saturday", strtotime($advent_sunday_date) ) ) {
				//$info .= $indent.date( 'Y-m-d', strtotime("previous Saturday", strtotime($advent_sunday_date)) );
				$info .= $indent.'<span class="warning">'."Uh oh! ".date('Y-m-d',$calc_date)." conflicts with Advent. Advent Sunday is $advent_sunday_date.</span><br />"; // tft
				$calc_date = null; //$calc_date = "N/A";
			} else {
				$info .= $indent."Ok -- Advent begins on $advent_sunday_date.<br />"; // tft
			}
		}

	}
	$info .= "<br />"; // <hr /><br />
	
	$arr_info['date'] = $calc_date;
    $arr_info['info'] = $info;
    
    return $arr_info;
	
	
}


add_shortcode('calculate_variable_dates', 'calc_litdates');
function calc_litdates( $atts = array() ) {

    // Failsafe -- run this fcn ONLY if logged in as webdev
    if ( !sdg_queenbee() ) { return "You are not authorized to run this operation.<br />"; }
    
	$info = "";
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;";

	$args = shortcode_atts( array(
        'testing' => true,
        'verbose' => false,
        'ids' => null,
        'years' => date('Y'),
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
    
    $info .= "&gt;&gt;&gt; calc_litdates &lt;&lt;&lt;<br />";
    $info .= "testing: $testing; verbose: $verbose; orderby: $orderby; order: $order; meta_key: $meta_key; ids: $ids; years: $years<br />";
    
    // Turn years var into array, in case of multiple years
    $arr_years = array(); // init
    if ( strpos($years, ',') !== false ) {
    	// comma-separated values
    	$arr_years = explode(",",$years);
    } else if ( strpos($years, '-') !== false ) {
    	// date range
    	$start_year = trim(substr($years, 0, strpos($years, "-") ));
    	$end_year = trim(substr($years, strpos($years, "-")+1 ));
    	//if (strlen($start_year) == 2 ) { }
    	if (strlen($end_year) == 2 ) { $end_year = "20".$end_year; }
    	$range = $end_year - $start_year;
    	$info .= "start_year: $start_year<br />";
    	$info .= "end_year: $end_year<br />";
    	$info .= "range: $range<br />";
    	for ( $i=0; $i<=$range; $i++ ) {
    		$arr_years[] = $start_year + $i;
    	}
    } else {
    	$arr_years[] = $years;
    }
    if ( $verbose == "true" ) { $info .= "arr_years: <pre>".print_r($arr_years,true)."</pre>"; }
    
    // Set up the WP query args
	$wp_args = array(
		'post_type' => 'liturgical_date',
		'post_status' => 'publish',
        'posts_per_page' => $num_posts,
        'orderby' => $orderby,
        'order'	=> $order,
	);
    
    // If ids and/or a meta_key (for ordering) have been specified, add those to the query args
    if ( !empty($ids) && strlen($ids) > 0 ) {
    	$wp_args['post__in'] = explode(', ', $ids);
    	$wp_args['meta_query'] = array(
            array(
                'key'   => "date_type", 
                'value' => 'variable',
            ),
        );
    } else {
    	$wp_args['meta_query'] = array(
            'relation' => 'AND',
            array(
                'key'   => "date_type", 
                'value' => 'variable',
            ),
            array(
                'key'   => "date_calculation",
                'compare' => 'EXISTS'
            )
        );
    }
    if ( !empty($meta_key) ) { $wp_args['meta_key'] = $meta_key; }
    
    // Run the query
	$arr_posts = new WP_Query( $wp_args );
    $posts = $arr_posts->posts;
    $info .= "[num posts: ".count($posts)."]<br />";
    //$info .= "wp_args: <pre>".print_r( $wp_args, true )."</pre>";
    if ( $verbose == "true" ) { $info .= "wp_args: <pre>".print_r( $wp_args, true )."<br />"; }
    //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre>";
    $info .= "<br />";
    
    // Loop through the posts and calculate the variable dates for the given year
    foreach ( $posts AS $post ) {
        
        setup_postdata( $post );
        $postID = $post->ID;
        $post_title = $post->post_title;
        $slug = $post->post_name;
        $info .= '<span class="label">['.$postID.'] "'.$post_title.'"</span><br />';
        $info .= '<div class="code indent">';
        
        // init
        $calc_info = "";
        $calc_date = null;
        $calc_date_str = "";
        
        $changes_made = false;
        $complex_formula = false;
		
        // Get date_calculation info & break it down
        $date_calc_str = strtolower(get_post_meta( $postID, 'date_calculation', true ));
        
        // Is this an "Eve of" litdate? Check to see if post_title begins with "Eve of" or "The Eve of"
		if ( strpos($post_title, "Eve of") === 0 || strpos($post_title, "The Eve of") === 0 ) {
			if ( $verbose == "true" ) { $info .= "Special case: 'Eve of' litdate ($post_title)<br />"; }
			if ( empty($date_calc_str) ) {
				if ( $verbose == "true" ) { $info .= "date_calc_str is empty => build it based on post_title<br />"; }
				if ( strpos($post_title, "The Eve of") === 0 ) { $post_title_mod = str_replace("The Eve of ", "", $post_title); } else { $post_title_mod = str_replace("Eve of ", "", $post_title); }	// TODO make more efficient w/ regexp?		
				$date_calc_str = "Day before ".$post_title_mod;
			}
		} else {
			 // Clean it up a little
			$date_calc_str = str_replace('christmas day', 'christmas', strtolower($date_calc_str) );
			$date_calc_str = str_replace('the epiphany', 'epiphany', strtolower($date_calc_str) );
			//$date_calc_str = str_replace(['the', 'day'], '', strtolower($date_calc_str) );
		}

        foreach ( $arr_years as $year ) {
        
        	$calc_info .= "<hr />About to do calc for year: $year<br />+~+~+~+~+<br />";
        	
			if ( !empty($date_calc_str) ) {
				$calc_info .= "date_calc_str: $date_calc_str<br />";
				$calc_args = array( 'year' => $year, 'date_calc_str' => $date_calc_str, 'verbose' => $verbose, 'ids_to_exclude' => array($postID) ); // exclude post's own id from calc basis determinations etc. --TODO/TBD: just past post_id, not array. Not sure when we'd need to exclude more than one post by id...
				$calc = calc_date_from_str( $calc_args ); //$calc = calc_date_from_str( $year, $date_calc_str, $verbose );
				if ( $calc ) {
					$calc_date = $calc['calc_date'];
					$calc_info .= $calc['calc_info'];
				} else {
					$calc_info .= '<span class="error">calc_date_from_str failed</span><br />';
				}
			} else {
				$calc_info .= "date_calc_str is empty<br />";
				//$calc = null;
			}   
			
			if ( !empty($calc_date) && $calc_date != "N/A" ) {
				$calc_date_str = date('Y-m-d', $calc_date);
				//$calc_date_str = date('Ymd', $calc_date); // was originally 'Y-m-d' format, which is more readable in DB, but ACF stores values edited via CMS *without* hyphens, despite field setting -- bug? or am I missing something?
				$calc_info .= "calc_date_str: <strong>$calc_date_str</strong> (".date('l, F d, Y',$calc_date).")<br />"; // tft
			} else {
				$calc_info .= "calc_date N/A<br />";         
			}
			
			// 3. Save dates to ACF repeater field row for date_calculatedday_
			// DB: date_calculations >> date_calculated -- date_calculations_[#]_date_calculated
			
			if ( $calc_date_str != "" ) {
				
				$newrow = true;
				
				if ( have_rows('date_calculations', $postID) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
					while ( have_rows('date_calculations', $postID) ) : the_row();
						$date_calculated = get_sub_field('date_calculated'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
						if ( $date_calculated == $calc_date_str ) {
							// Already in there
							$newrow = false;
							$calc_info .= "+++ Old news. This date_calculated ($calc_date_str) is already in the database. +++<br />"; // tft
						} else {
							//$calc_info .= "Old date_calculated: $date_calculated.<br />"; // tft
						}
					endwhile;
				} // end if
	
				if ( $newrow == true ) {
	
					$row = array(
						'date_calculated' => $calc_date_str
					);
	
					$calc_info .= "About to add row to post_id $postID: ".print_r( $row, true )."<br />"; // <pre></pre>
					if ( $testing != "true" ) {
						if ( add_row('date_calculations', $row, $postID) ) { // ACF function syntax: add_row($selector, $value, [$postID])
							$calc_info .= "ACF row added for post_id: $postID<br />";
						} else {
							$calc_info .= "ACF add row FAILED for post_id: $postID<br />";
						}
					}
	
				}
			} else {
				$calc_info .= "calc_date_str is empty.<br />";
			}
			
			if ( count($arr_years) > 1 ) { $calc_info .= "<br />"; }
        
        } // END foreach arr_years
        
        $info .= $calc_info;    
    	$info .= '</div>';
             
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
function get_cpt_reading_content( $postID = null ) {
	
    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "lectionary") ); 
    $do_log = false;
    //$fcn_id = "[sdg-pt] ";
    sdg_log( "divline2", $do_log );
    
    // Init vars
    $info = "";
    $ts_info = "";
	if ($postID === null) { $postID = get_the_ID(); }
    
    // Get the CPT object
    $post = get_post($postID);
    
    // Link to text of Bible Verses -- WIP
    $bible_book_id = get_post_meta( $postID, 'book', true ); // TODO: use get_field instead? Will this work to retrieve ID? $bible_book = get_field( 'book', $postID );
    if ( is_array($bible_book_id) ) {
    	$ts_info .= "bible_book_id is array: ".print_r($bible_book_id, true)."<br />";
    } else {
    	$ts_info .= "bible_book_id: '".$bible_book_id."'<br />";
    	$bible_corpus_id = get_post_meta( $bible_book_id, 'bible_corpus_id', true );
    	$ts_info .= "bible_corpus_id: '".$bible_corpus_id."'<br />";
    }
    
    $chapterverses = get_field( 'chapterverses', $postID );
    
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
    if ( ( strpos($chapterverses,"-") && substr_count($chapterverses, ':') > 1 ) || strpos($chapterverses,"-") && substr_count($chapterverses, ':') == 0 ) {
        $ts_info .= "The string '".$chapterverses."' contains information about more than one chapter.<br />";
        
        $first = substr( $chapterverses, 0, strpos($chapterverses,"-") );
        if ( substr_count($chapterverses, '-') == 1 ) {
            $last = substr( $chapterverses, strpos($chapterverses,"-")+1 );
        } else {
            $last = "not sure!";
        }
        $ts_info .= "first: ".$first."; last: ".$last;
        $ts_info .= "<br />";
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
    $ts_info .= "The string '".$chapterverses."' contains ".substr_count($chapterverses, ',')." comma(s).";
    $ts_info .= "<br />";
    
    //$pieces = explode("-", $chapterverses);
    //$chapter = substr( $chapterverses,0,strpos($chapterverses,":") );
    //$verses = substr( $chapterverses,strpos($chapterverses,":") );
    //$row_info .= "<!-- chapter: '$chapter'; verses: 'verses' -->"; // tft
    
    //$info .= "chapter: '".$chapter."'; verses: '".$verses."'"; // tft
    
    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "lectionary" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
    
    return $info;
    
}


/*********** CPT: PSALMS OF THE DAY ***********/
function get_cpt_psalms_of_the_day_content() {
	
}

// att service: "morning_prayer" or "evening_prayer"
add_shortcode('psalms_of_the_day', 'get_psalms_of_the_day');
function get_psalms_of_the_day( $atts = array(), $content = null, $tag = '' ) {
	
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
    
    $post = get_post( $postID );
    if ( $post ) { $post_type = $post->post_type; }
    if ( $post_type == 'event' ) { $event_id = $postID; }
    
    if ( is_dev_site() ) {
        //$info .= "post_id: $postID; post_type: $post_type; event_id: $event_id; <br />";
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
