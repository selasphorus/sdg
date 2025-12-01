<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
    exit;
}

/*********** CPT: LITURGICAL DATE ***********/

// TODO: move the following to WHx4 -- some utility class
add_action('acf/input/admin_enqueue_scripts', function() {
    wp_enqueue_script(
        'acf-repeater-clear-all',
        get_stylesheet_directory_uri() . '/js/acf-repeater-clear-all.js',
        ['jquery'],
        null,
        true
    );
});

// TODO: move the following functions to WHx4 > \Util\DateHelper.php

// TMP -- see WHx4\src\Utils\Text
function snake ( $string = "")
{
    strtolower( str_replace( ' ', '_', $string ) );
}

function normalizeMonthToInt( string $month ): ?int
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

/**
 * Normalize date input to standardized Y-m-d start/end values.
 *
 * @param string|null $scope Optional keyword like 'this_month' or 'Easter 2025'
 * @param string|DateTimeInterface|null $date A date string, range, or DateTime object
 * @param int|null $year Fallback year if needed
 * @param int|string|null $month Fallback month if needed
 * @return array|string Array with 'startDate' and 'endDate' or single string if same
 */
function normalizeDateInput( array $args = [] ): array|DateTimeImmutable|string
{
    $args = wp_parse_args( $args, [
        'date'          => null,
        'scope'         => null,
        'year'          => null,
        'month'         => null,
        //'returnSingle'  => true, // WIP return a single date, as opposed
        'asDateObjects' => false,
    ] );
    extract( $args );

    $now = new DateTimeImmutable();

    if ( is_string( $scope ) ) {
        $scope_key = snake($scope);

        switch ( $scope_key ) {
            case 'today':
                return $now->format( 'Y-m-d' );

            case 'this_week':
                $start = $now->modify( 'monday this week' )->format( 'Y-m-d' );
                $end   = $now->modify( 'sunday this week' )->format( 'Y-m-d' );
                return [ 'startDate' => $start, 'endDate' => $end ];

            case 'this_month':
                $start = $now->modify( 'first day of this month' )->format( 'Y-m-d' );
                $end   = $now->modify( 'last day of this month' )->format( 'Y-m-d' );
                return [ 'startDate' => $start, 'endDate' => $end ];

            case 'next_month':
                $start = $now->modify( 'first day of next month' )->format( 'Y-m-d' );
                $end   = $now->modify( 'last day of next month' )->format( 'Y-m-d' );
                return [ 'startDate' => $start, 'endDate' => $end ];

            case 'last_year':
                $start = ( new DateTimeImmutable( 'first day of January last year' ) )->format( 'Y-m-d' );
                $end   = ( new DateTimeImmutable( 'last day of December last year' ) )->format( 'Y-m-d' );
                return [ 'startDate' => $start, 'endDate' => $end ];

            case 'next_year':
                $start = ( new DateTimeImmutable( 'first day of January next year' ) )->format( 'Y-m-d' );
                $end   = ( new DateTimeImmutable( 'last day of December next year' ) )->format( 'Y-m-d' );
                return [ 'startDate' => $start, 'endDate' => $end ];

            case 'this_season':
                $month_now = (int) $now->format( 'n' );
                $year_now  = (int) $now->format( 'Y' );

                if ( $month_now >= 9 ) {
                    $start = new DateTimeImmutable( "$year_now-09-01" );
                    $end   = new DateTimeImmutable( ($year_now + 1) . "-05-31" );
                } else {
                    $start = new DateTimeImmutable( ($year_now - 1) . "-09-01" );
                    $end   = new DateTimeImmutable( "$year_now-05-31" );
                }

                return [
                    'startDate' => $start->format( 'Y-m-d' ),
                    'endDate'   => $end->format( 'Y-m-d' ),
                ];
        }

        // Check for Easter YEAR pattern
        if ( preg_match( '/^easter\s+(\d{4})$/i', $scope, $matches ) ) {
            $easter = calculateEasterDate( (int) $matches[1] );
            return $easter->format( 'Y-m-d' );
        }
    }

    if ( $date instanceof DateTimeInterface ) {
        return $date->format( 'Y-m-d' );
    }

    // Date range in format "YYYY-mm-dd, YYYY-mm-dd"? Then set start, end dates
    //if ( is_string( $date ) && strpos( $date, ',' ) !== false ) {
    if ( preg_match( '/^\d{4}-\d{2}-\d{2},\s?\d{4}-\d{2}-\d{2}$/', $date ) ) {
        [ $raw_start, $raw_end ] = explode( ',', $date, 2 );
        $start = parseFlexibleDate( trim( $raw_start ) );
        $end   = parseFlexibleDate( trim( $raw_end ) );
        return [ 'startDate' => $start, 'endDate' => $end ];
    }

    if ( is_string( $date ) ) {
        return parseFlexibleDate( $date );
    }

    if ( $month ) {
        $month = str_pad( (string)(int) $month, 2, '0', STR_PAD_LEFT );
        $year  = $year ?? (int) $now->format( 'Y' );
        $start = DateTimeImmutable::createFromFormat( 'Y-m-d', "{$year}-{$month}-01" );
        $end   = $start->modify( 'last day of this month' );
        return [
            'startDate' => $start->format( 'Y-m-d' ),
            'endDate'   => $end->format( 'Y-m-d' ),
        ];
    }

    return $now->format( 'Y-m-d' );
}

/**
 * Parses a flexible natural-language date string.
 *
 * @param string $input
 * @return string
 */
function parseFlexibleDate( string $input ): string
{
    try {
        $dt = new DateTimeImmutable( $input );
        return $dt->format( 'Y-m-d' );
    } catch ( Exception $e ) {
        return '';
    }
}

/**
 * Calculates the Easter date for a given year.
 *
 * @param int $year
 * @return DateTimeImmutable
 */
function calculateEasterDate( int $year ): DateTimeImmutable
{
    $timestamp = easter_date( $year );
    return ( new DateTimeImmutable() )->setTimestamp( $timestamp );
}

/* END Date Normalization */

add_shortcode( 'liturgical_dates', 'renderLitDatesShortcode' );
function renderLitDatesShortcode( $atts = [] ): string
{
    // Extract args
    $args = shortcode_atts( [
        'date'        => null,
        'scope'       => null,
        'year'        => null,
        'month'       => null,
        'show_date'   => true,
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

    $output = getLitDateData( $atts );
    if ( !is_string( $output ) ) { $output = print_r( $output, true ); }
    return $output;
}


// Day Titles
// TODO/WIP: separate day title functionality from special notice functionality and/or create umbrella function to allow option of displaying both together
add_shortcode('day_title', 'getDayTitle');
function getDayTitle( $atts = [], $content = null, $tag = '' )
{
    $output = '';

    // TS/logging setup
    $ts_info = "";
    $do_ts = devmode_active( array("sdg", "lectionary") );
    $do_ts = true; // tft

    $output .= "\n<!-- getDayTitle -->\n";
    // TODO: Optimize this function! Queries run very slowly. Maybe unavoidable given wildcard situation. Consider restructuring data?
    // TODO: add option to return day title only -- just the text, with no link or other formatting

    // Extract args
    $args = shortcode_atts( [
        'post_id'   => get_the_ID(),
        'series_id' => null,
        'date'      => null,
        'the_date'  => null, // deprecated -- to be removed as soon as changes are pushed live and plugin-templates/events-list.php has been updated on live site
        'exclusive' => true,
        'debug'     => false,
        'return'    => 'formatted',
        'show_content' => true,
    ], $atts );
    extract( $args );

    $postID = $post_id;

    // Show or Hide Day Titles for this series/event/post?
    // +~+~+~+~+~+~+~+~+~+~+
    $hideDayTitles = 0; // default
    // Check to see if day titles are to be hidden for the entire event series, if any
    if ( $series_id ) {  $hideDayTitles = get_post_meta( $series_id, 'hide_day_titles', true ); }
    // If there is no series-wide ban on displaying the titles, then should we display them for this particular post?
    if ( $hideDayTitles == 0 ) { $hideDayTitles = get_post_meta( $postID, 'hide_day_titles', true ); }
    /*if ( $hideDayTitles == 1 ) {
        $ts_info .= "hide_day_titles is set to true for this post/event<br />";
        if ( $ts_info != "" && ( $do_ts === true || $do_ts == "" ) ) { $output .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
        return $output;
    }*/

    // If no date has yet been set, try to find one
    if ( $the_date ) { $date = $the_date; }
    if ( $date == null ) {
        if ( $postID ) {
            $post = get_post( $postID );
            $post_type = $post->post_type;
            if ( $post_type == 'event' ) {
                $dateStr = get_post_meta( $postID, '_event_start_date', true );
                $date = strtotime($dateStr);
            } elseif ( $post_type == 'sermon' ) {
                $date = the_field( 'sermon_date', $postID );
            }
        }
    }

    // If the date is still null, give up and go
    if ( $date == null ) {
        $ts_info .= "no date available for which to find day_title<br />";
        if ( $ts_info != "" && ( $debug == true || $do_ts === true || $do_ts == "" ) ) { $output .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
        return $output;
    }

    // If hideDayTitles is false, go ahead and get litdates for the date
    if ( !$hideDayTitles ) { // == 0
        $args[ 'date' ] = $date;
        $args[ 'filter_types' ] = [ 'primary', 'secondary' ];
        $args[ 'debug' ] = $do_ts; // tft
        //
        $ts_info .= "About to getLitDateData for date: $date<br />";
        $output .= getLitDateData( $args );
    } else {
        $ts_info .= "hideDayTitles is set to true for this post/event<br />";
    }

    // Show or Hide Special Notices?
    // +~+~+~+~+~+~+~+~+~+~+
    $hideSpecialNotices = 0; // default
    // Check to see if special notices are to be hidden for the entire event series, if any
    if ( $series_id ) { $hideSpecialNotices = get_post_meta( $series_id, 'hide_special_notices', true ); }
    // If there is no series-wide ban on displaying the notices, then should we display them for this particular post?
    if ( $hideSpecialNotices == 0 ) { $hideSpecialNotices = get_post_meta( $postID, 'hide_special_notices', true ); }
    if ( $hideSpecialNotices == 1 ) { $ts_info .= "hideSpecialNotices is set to true for this post/event<br />"; }
    // Append Event Special Notices content, as applicable
    if ( function_exists('get_special_date_content') && !$hideSpecialNotices ) { $output .= get_special_date_content( $date ); }
    //if ( function_exists('getSpecialDateContent') && !$hideSpecialNotices ) { $output .= getSpecialDateContent( $date ); }

    // TS Info
    $output .= "\n<!-- /getDayTitle -->\n";
    if ( $ts_info != "" && ( $debug == true || $do_ts === true || $do_ts == "day_titles" ) ) { $output .= '<div class="troubleshooting">'.$ts_info.'</div>'; }

    return $output;

}

// WIP
function getLitDateData( array $args = [] ): array|string
{
    $defaults = [
        'date'             => null,
        'scope'            => null,
        'year'             => null,
        'month'            => null,
        //
        'post_id'   => null,
        'series_id' => null,
        //
        'day_titles_only'  => false,
        'exclusive'        => false, // set to true to display only one primary (and possibly one or more secondary) litdate per calendar date. TODO: better arg name
        //
        'return'           => 'posts', // 'posts' | 'prioritized' | 'formatted' | 'title'
        'formatted'        => false,
        'show_date'        => false, // we'll only display the date if returning formatted in non-events context
        'show_meta'        => false,
        'show_content'     => false,
        'admin'            => false, // whether to show Edit links etc.
        'debug'            => false,
        'filter_types'     => [], // e.g. ['primary'] to limit output
        'type_labels'      => [   // override default labels as needed -- probably won't need this though in fact...
            'primary'   => 'Primary',
            'secondary' => 'Secondary',
            'other'     => 'Other',
        ],
    ];

    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    //$info = '';
    $ts_info = '';
    $litdatePostsByDate = [];
    $litdateData = [];
    $startDate = null;
    $endDate = null;

    // WIP -- translate from WP-style atts to PSR-12 var names
    $postID = $post_id;

    if ( $exclusive ) { $day_titles_only = true; }
    // $filter_types = ['primary','secondary'];
    if ( $debug == "true" ) { $ts_info .= "getLitDateData args: <pre>".print_r($args,true)."</pre>"; }
    //if ( $admin ) { $info .= "exclusive: $exclusive; day_titles_only: $day_titles_only<br />"; }

    // Normalize date input
    if ( $date ) {
        $dateStr = normalizeDateInput( [ 'date' => $date ] );
        //
        if ( is_string( $dateStr ) ) {
            $startDate = $endDate = $dateStr;
            $year  = substr( $dateStr, 0, 4 );
            $month = substr( $dateStr, 5, 2 );
        } else {
            $ts_info .= "dateStr <pre>" . print_r( $dateStr, true) . " is not a string but a " . gettype( $dateStr ) . "!<br />";
        }
    } elseif ( $scope ) {
        $dates = normalizeDateInput( [ 'scope' => $scope ] );
        if ( is_string( $dates ) ) {
            $startDate = $endDate = $dates;
            $year  = substr( $dateStr, 0, 4 );
            $month = substr( $dateStr, 5, 2 );
        } else {
            $startDate = $dates[ 'startDate' ];
            $endDate = $dates[ 'endDate' ];
            //$ts_info .= "dateStr <pre>" . print_r( $dateStr, true) . " is not a string but a " . gettype( $dateStr ) . "!<br />";
        }
    } else {
        if ( empty( $year ) ) { $year = date( 'Y' ); } // default to current year if none is set
        if ( empty( $month ) ) {
            $startDate = $year . '-01-01';
            $endDate   = $year . '-12-31';
        } else {
            if ( !is_numeric( $month ) ) {
                $month = normalizeMonthToInt( $month );
            }
            $month = (int)$month;
            if ( $month < 1 || $month > 12 ) {
                return [
                    'args' => $args,
                    'info' => "Invalid month: '$month'",
                ];
            }
            //
            $startDate = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-01';
            $days_in_month = cal_days_in_month( CAL_GREGORIAN, $month, (int)$year );
            $endDate   = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-' . $days_in_month;
        }
    }

    //$ts_info .= "startDate: $startDate; endDate: $endDate<br />";

    $start = strtotime( $startDate );
    $end   = strtotime( $endDate );

    ///

    while ( $start <= $end ) {
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

        //$ts_info .= "<strong>Fixed Date query_args</strong>: <pre>".print_r($fixedQueryArgs,true)."</pre>";

        $qFixed = new WP_Query( $fixedQueryArgs );
        if ( $qFixed->have_posts() ) {
            $litdatePostsByDate[ $dateStr ] = $qFixed->posts;
            //if ( count($qFixed->posts) != 1 ) { $ts_info .= "<strong>$dateStr</strong>: found ".count($qFixed->posts)." matching fixed-date post(s)<br />"; }
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

        //$ts_info .= "<strong>Variable Date query_args</strong>: <pre>".print_r($variableQueryArgs,true)."</pre>";

        $qVar = new WP_Query($variableQueryArgs);
        if ( $qVar->have_posts() ) {
            if ( isset( $litdatePostsByDate[ $dateStr ] ) ) {
                $litdatePostsByDate[ $dateStr ] = array_merge($litdatePostsByDate[ $dateStr ], $qVar->posts);
            } else {
                $litdatePostsByDate[ $dateStr ] = $qVar->posts;
            }
            //if ( count($qVar->posts) != 1 ) { $ts_info .= "<strong>$dateStr</strong>: found ".count($qVar->posts)." matching variable-date post(s)<br />"; }
        }

        $start = strtotime( '+1 day', $start );
    }
    
    // init
    $primaryPost = null;
    $secondaryPost = null;

    // Loop through litdate posts and sort them by priority
    foreach ( $litdatePostsByDate as $dateStr => $posts ) {
        $unsorted = [];
        $primaryPost = null;
        $secondaryPost = null;
        $defaultPriority = 999;
        $year  = substr( $dateStr, 0, 4 );
        $ts_info .= count( $posts ) . " post(s) found for dateStr : ".$dateStr."<br />"; //.": ".print_r($posts,true)

        foreach ( $posts as $post ) {
            $postID = $post->ID;
            $postPriority = $defaultPriority;
            $dateGroup = 'other';
            $ts_info .= "postID: " . $postID . " (" . $post->post_title . ")<br />";

            // Get the actual displayDates for the given litdate, to make sure the dateStr in question hasn't been overridden
            $displayDatesInfo = getDisplayDates( $postID, $year );
            $displayDates = $displayDatesInfo[ 'dates' ];
            $ts_info .= $displayDatesInfo['info'];
            $ts_info .= "displayDates for postID: $postID/year: $year <pre>".print_r($displayDates, true)."</pre>";
            if ( !in_array($dateStr, $displayDates) ) {
                $ts_info .= "date_str: ".$dateStr." is not one of the display_dates for this litdate for year $year.<br />";
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
                    // If term_priority is lower than current max priority, then this is a more important category (#1 comes first)
                    if ( is_numeric( $termPriority ) && $termPriority < $postPriority ) {
                        $postPriority = (int)$termPriority;
                    }
                }
            }
            $ts_info .= "postPriority: ".$postPriority."<br />";

            // Check if litdate post has been designated as secondary
            $is_secondary = get_post_meta( $postID, 'secondary', true );

            // Set the type accordingly (default is 'other')
            if ( $is_secondary ) {
                $dateGroup = 'secondary';
            } elseif ( $postPriority < 999 ) {
                $dateGroup = 'primary';
            }

            $unsorted[ $dateGroup ][] = [
                'post'     => $post,
                'priority' => $postPriority,
            ];

            //$ts_info .= 'postID: '.$postID."; priority: ".$postPriority."; date_type: ".$dateGroup."<br />";
        }

        //$ts_info .= "unsorted array of posts and priorities: <pre>".print_r($unsorted,true)."</pre>"; // ok

        // Sort primaries by priority, lowest first
        $sorted = [];
        foreach ( $unsorted as $dateGroup => $posts ) {
            $sorted[ $dateGroup ] = $posts;
            usort( $sorted[ $dateGroup ], function( $a, $b ) {
                return $a[ 'priority' ] <=> $b[ 'priority' ];
            } );
        }

        //$info .= "sorted array of posts and priorities: <pre>".print_r($sorted,true)."</pre>"; // ok

        // wip...
        if ( $exclusive ) {
            //$info .= "exclusive => get only the single most important matching primary post for date: ".$dateStr."<br />";
            // Get the single most important matching litdate post
            if ( !empty( $sorted[ 'primary' ] ) ) {
                $primaryPost = $sorted[ 'primary' ][0];
            } elseif ( !empty( $sorted[ 'other' ] ) ) {
                $primaryPost = $sorted[ 'other' ][0];
            }
            if ( $primaryPost ) {
                //$info .= "primaryPost found for date: ".$dateStr.": ".print_r($primaryPost,true)."<br />";
                $ts_info .= "primaryPost found for date: ".$dateStr." with ID: ".$primaryPost['post']->ID." (" . $primaryPost['post']->post_title . ")<br />";
                $litdateData[ $dateStr ][ 'primary' ][] = $primaryPost;
            } else {
                //$info .= "No primaryPost found!<br />";
            }
            // Get the most important secondary litdate post, if any
            /*
            if ( !empty( $sorted[ 'secondary' ] ) ) {
                $secondaryPost = $sorted[ 'secondary' ][0];
            }
            if ( $secondaryPost ) {
                $ts_info .= "secondaryPost found with ID: ".$secondaryPost['post']->ID." (" . $secondaryPost['post']->post_title . ")<br />";
                $litdateData[ $dateStr ][ 'secondary' ][] = $secondaryPost;
            }
            */
            // Show ALL secondary litdate posts (usually only one, but sometimes multiple -- e.g. Ember Days/O Antiphons)
            if ( !empty( $sorted[ 'secondary' ] ) ) {
                $secondaryPosts = $sorted[ 'secondary' ];
            }
            if ( $secondaryPosts ) {
                $ts_info .= "secondaryPosts found: ".print_r($secondaryPosts,true)."<br />";
                $litdateData[ $dateStr ][ 'secondary' ] = $secondaryPosts;
            }
            
        } else {
            $litdateData[ $dateStr ] = $sorted;
        }
    }

    // If formatted output was requested...
    // TODO: revise to include options to show/hide date; to show collect or not... etc.
    if ( $args['return'] === 'simple' || $args['return'] === 'formatted' ) {
        $output = "";
        if ( $args['return'] === 'simple' ) {
            //$output .= "litdateData: <pre>".print_r($litdateData, true)."</pre>";
            if ( $primaryPost ) { $output .= $primaryPost['post']->post_title; }
            if ( $primaryPost && $secondaryPost ) { $output .= "<br />"; }
            if ( $secondaryPost ) { $output .= $secondaryPost['post']->post_title; }
        } else if ( $args['return'] === 'formatted' ) {
            $output = "";
            //$output .= "debug: [$debug]<br />";
            $data = formatLitDateData( $litdateData, $args );
            $output .= $data;
        }
        //if ( $debug ) { $output .= $info; }
        if ( $ts_info != "" && ( $debug == true || $do_ts === true || $do_ts == "day_titles" ) ) {
            $output .= '<div class="troubleshooting">'.$ts_info.'</div>';
        }
        return $output;
    }

    // Default return
    return [
        'args'       => $args,
        'startDate'  => $startDate,
        'endDate'    => $endDate,
        'litdateData'=> $litdateData,
        'info'       => $ts_info,
    ];
}

function formatLitDateData( $litDateData = [], $args = [] )
{
    $output = '';
    $ts_info = '';
    $modal = '';
    $primaryShown = false; // track whether a primary date is being displayed, so as to properly format secondary date, if any

    if ( $args[ 'admin' ] ) { $admin = $args[ 'admin' ]; } else { $admin = false; }
    if ( $args[ 'debug' ] ) { $debug = $args[ 'debug' ]; } else { $debug = false; }
    //
    //if ( $debug ) { $output .= "args: <pre>".print_r($args,true)."</pre>"; }

    foreach ( $litDateData as $dateStr => $typeGroups ) {
        $output .= "<div class='liturgical-date-block'>";
        if ( $admin || $args[ 'show_date' ] ) {
            $output .= '<a href="/events/' . date( 'Y-m-d', strtotime( $dateStr ) ) . '/" class="subtle" target="_blank">';
            $output .= date( 'l, F j, Y', strtotime( $dateStr ) );
            $output .= "</a><br />";
        }

        $groupsToDisplay = [ 'primary', 'secondary', 'other' ];

        foreach ( $groupsToDisplay as $groupKey ) {
            $ts_info .= "groupKey: $groupKey<br />";
            if ( !empty( $args[ 'filter_types' ] ) && !in_array( $groupKey, $args[ 'filter_types' ], true ) ) {
                continue;
            }

            if ( !empty( $typeGroups[ $groupKey ] ) ) {
                //if ( $args[ 'show_meta' ] ) { //if ( $groupKey !== 'primary' ) {
                    //$label = $args[ 'type_labels' ][ $groupKey ] ?? ucfirst( $groupKey );
                    //$output .= "<em>$label</em><br />";
                //}

                foreach ( $typeGroups[ $groupKey ] as $groupItem ) {
                    //$ts_info .= "groupItem: <pre>".print_r($groupItem,true)."</pre>";
					$post = $groupItem[ 'post' ];
					$postPriority = $groupItem[ 'priority' ];
					$post = get_post( $post );
					
					// Make sure we've got the right type of post object
					if ( !$post instanceof WP_Post ) {
						if ( $debug ) { $output .= "So-called post ".print_r($post,true)." is not a WP_Post object. Moving on to the next...<br />"; }
						continue;
					}
					if ( $post->post_type != "liturgical_date" ) {
						if ( $debug ) { $output .= "So-called litdate post with ID: ".$post->ID." is not the right type. It is a post of type '".$post->post_type."'. Moving on to the next...<br />"; }
						continue;
					}
					$postID = $post->ID;
					if ( $postID && $groupKey == "primary" ) {
						$primaryShown = true;
					}
					$title = get_the_title( $post );
					$link = get_permalink( $post );
					$class = $groupKey;
					$ts_info .= "postID: $postID; title: $title<br />";

					// TODO: option to return UN-linked version of title(s)?
					if ( $admin ) { $output .= '<a href="' . esc_url( $link ) . '" class="' . esc_html( $class ) . '">' . esc_html( $title ) . '</a>&nbsp;'; }

					// Optional meta info
					if ( $args[ 'show_meta' ] || $admin ) {
						$terms = get_the_terms( $post, 'liturgical_date_category' );
						$term_names = $terms && !is_wp_error( $terms ) ? wp_list_pluck( $terms, 'name' ) : [];
						$date_type = get_post_meta( $post->ID, 'date_type', true );
						if (!$date_type) { $date_type = "UNKNOWN"; }
						//
						$output .= '<small>'; //<br />
						$output .= 'ID: ' . $post->ID;
						$output .= ' | Date type: ' . esc_html( $date_type );
						if ( $date_type == "variable" ) {
							$date_calc = get_post_meta( $post->ID, 'date_calculation', true );
							if ( $date_calc ) { $output .= ' | Date calc: "' . esc_html( $date_calc ) . '"';}
						}
						if ( !empty( $term_names ) ) {
							$output .= ' | Terms: ' . esc_html( implode( ', ', $term_names ) );
						}
						$output .= ' | Priority: ' . esc_html( $postPriority );
						$output .= '</small>';
					}

					// Edit post link, for admin use
					if ( $admin ) { $output .= '&nbsp;>> <a href="' . get_edit_post_link( $postID ) . '" class="subtle" target="_blank">Edit</a> <<'; }

					// Content and collect?
					if ( $args[ 'show_content' ] && $groupKey == "primary" ) {
						//$ts_info .= "about to look for content and collect<br />";

						$litdate_content = get_the_content( null, false, $postID ); // get_the_content( string $more_link_text = null, bool $strip_teaser = false, WP_Post|object|int $post = null )
						$collect_text = get_collect_text( $postID, $dateStr );

						// TODO/atcwip: if no match by postID, then check propers 1-29 by date (e.g. Proper 21: "Week of the Sunday closest to September 28")

						// If there's something other than the title available to display, then display the popup link
						// TODO: set width and height dynamically based on browser window dimensions
						$width = '650';
						$height = '450';

						if ( !empty($collect_text) ) {
							// TODO: modify title in case of Propers?
							if ( !$admin ) { $output .= '<a href="#!" id="dialog_handle_'.$postID.'" class="calendar-day dialog_handle">' . $title . '</a>'; }
							// Put together the collect modal
							$modal .= '<div id="dialog_content_'.$postID.'" class="calendar-day-desc dialog">';
							$modal .= '<h2 autofocus>'.$title.'</h2>';
							//if ( is_dev_site() ) { $output .= $litdate_content; }
							if ( $collect_text !== null ) {
								$modal .= '<div class="calendar-day-collect">';
								//$output .= '<h3>Collect:</h3>';
								$modal .= '<p>'.$collect_text.'</p>';
								$modal .= '</div>';
							}
							$modal .= '</div>'; ///calendar-day-desc<br />

						} else {
							$ts_info .= "no collect_text found<br />";
							// If no content or collect, just show the day title
							$output .= '<span id="'.$postID.'" class="calendar-day">'.$title.'</span>';
						}
					} elseif ( $groupKey == "secondary" && !$admin ) {
						if ( $primaryShown ) { $output .= "<br />"; }
						$output .= '<span class="calendar-day secondary">' . $title . '</span>';
						if ( is_array( $typeGroups[ $groupKey ] ) && count($typeGroups[ $groupKey ] > 1 ) ) { $output .= "<br />"; }
					} else {
						$output .= '<br />';
						//$ts_info .= "show_content: " . $args[ 'show_content' ] . "; groupKey: $groupKey; postPriority: $postPriority<br />";
					}
                }
                if ( !$args[ 'exclusive' ] ) { $output .= "<br />"; }
            }
        }
        $output .= $modal;
        $output .= "</div><br />";
    }

    if ( $args[ 'debug' ] && !empty( $ts_info ) ) { $output = '<div class="troubleshooting">'.$ts_info.'</div>' . $output; } // info first
    //if ( $args['debug'] && !empty( $info ) ) { $output .= '<div class="debug-info">'.$info.'</div>'; } // output first

    return $output;
}

// ===== //

// WIP
// A liturgical date may correspond to multiple dates in a year, if dates have been both assigned and calculated,
// or if a date has been assigned to replace the fixed date
// The following function determines which of the date(s) is active -- could be multiple, if date assigned is NOT a replacement_date
function getDisplayDates ( $postID = null, $year = null )
{
    $info = "";
    $dates = array();
    $arr_info = array();
    $fixedDateStr = "";
    if ( empty( $year ) ) { $year = date( 'Y' ); } // default to current year if none is set

    // Get date_type (fixed, calculated, assigned)
    $date_type = get_post_meta( $postID, 'date_type', true );
    $info .= "--- getDisplayDates ---<br />";
    $info .= "litdate post_id: ".$postID."; date_type: ".$date_type."; year: ".$year."<br />";

    // Get calculated or fixed date for designated year
    if ( $date_type == "fixed" ) {
        if ( !$fixedDateStr = get_field( 'fixed_date_str', $postID ) ) {
            $info .= "No fixed_date_str found.<br />";
        } else {
            $info .= "fixed_date_str: ".$fixedDateStr."<br />";
            if ( $year ) {
                $fixedDateStr .= " ".$year;
                $info .= "fixed_date_str (mod): ".$fixedDateStr."<br />";
            }
            $formattedFixedDateStr = date("Y-m-d",strtotime($fixedDateStr));
            $info .= "formattedFixedDateStr: ".$formattedFixedDateStr."<br />";
            $dates[] = $formattedFixedDateStr;
        }
    } else {
        // For variable dates, get calculated dates
        $info .= "Variable date => check date_calculations.<br />";
        // TODO: run a query instead to find rows relevant by $year -- it will be more efficient than retrieving all the rows
        if ( have_rows('date_calculations', $postID) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
            while ( have_rows('date_calculations', $postID) ) : the_row();
                $dateCalculated = get_sub_field('date_calculated'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
                $yearCalculated = substr($dateCalculated, 0, 4);
                if ( $yearCalculated == $year ) {
                    $dates[] = $dateCalculated;
                }
            endwhile;
        } // end if
    }

    // get date assignments to see if there is a replacement_date to override the fixed_date_str
    // TODO: run a query instead to find rows relevant by $year -- it will be more efficient than retrieving all the rows
    $info .= "=> check date_assignments.<br />";
    $date_assignments = get_field('date_assignments', $postID );
    if ( $postID == 6998 ) { $info .= "date_assignments: <pre>" . print_r( $date_assignments, true ) . "</pre>"; }
    // have_rows/while_rows should work, but doesn't, always
    //if ( have_rows('date_assignments', $postID) ) { // ACF fcn: https://www.advancedcustomfields.com/resources/have_rows/
        //while ( have_rows('date_assignments', $postID) ) : the_row();
    if ( $date_assignments ) {
        foreach ( $date_assignments as $row ) {
            $dateAssigned = $row['date_assigned'];
            $dateException = $row['date_exception'];
            $replacementDate = $row['replacement_date'];
            //$dateAssigned = get_sub_field('date_assigned');
            //$dateException = get_sub_field('date_exception');
            //$replacementDate = get_sub_field('replacement_date'); // deprecated
            //$info .= "<!-- date_exception: ".$dateException." -->";
            $yearAssigned = substr($dateAssigned, 0, 4);
            $info .= "dateAssigned: ".$dateAssigned." (".$yearAssigned.")<br />";

            // Check the date assignments against our array of dates[]
            // Only bother if the assigned date fall in the applicable calendar year
            if ( $yearAssigned == $year ) {
                $info .= "yearAssigned matches year<br />";
                if ( $dateException != "default" ) { // Are we dealing with a date exception?
                    $info .= "dateException: $dateException<br />";
                    // If this is a replacement_date assignment, then check to see if it matches the event calendar display date
                    if ( $dateAssigned != $fixedDateStr && ( $dateException == "replacement_date" || $replacementDate == "1" ) ) {
                        $info .= "replacement_date date_assigned: ".$dateAssigned." overrides fixed_date_str ".$fixedDateStr." for year ".$year."<br />";
                        $fixedDateStr = $dateAssigned;
                        // Since this is a replacement_date it should be the only one displayed in the given year -- don't add it to array; replace the array
                        $dates = [ $fixedDateStr ];
                        break;
                    } elseif ( $dateException == "exclusion_date" ) {
                        // Remove the exclusion date from the array of dates
                        $dates = array_diff( $dates, [ $dateAssigned ] );
                    }
                } else {
                    // Date is not exceptional, so add it to the array with no further checks
                    $dates[] = $dateAssigned;
                }
            } else {
                $info .= "yearAssigned ($yearAssigned) does NOT match year ($year)<br />";
            }
        }
        //endwhile;
    } else {
        $info .= "=> NO date_assignments found for postID: $postID<br />"; //-- or rather, have_rows('date_assignments', $postID) is FALSE.
        //if ( $postID == 6998 ) { $info .= "X-check via get_field: <pre>" . print_r( get_field('date_assignments', $postID ), true ) . "</pre>"; }
        //$info .= "X-check via get_post_meta: <pre>" . print_r( get_post_meta( $postID ), true ) . "</pre>";
    }

    $arr_info['info'] = $info;
    $arr_info['dates'] = $dates;
    return $arr_info;
}

// Collects -- get collect to match litdate (or calendar date? wip)
function get_collect_text( $postID = null, $dateStr = null )
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
    $ts_info .= "postID: ".$postID."<br />";
    $ts_info .= "date_str: ".$dateStr."<br />";
    $date = strtotime($dateStr);
    $ts_info .= "litdate date Y-m-d: ".date('Y-m-d',$date)."<br />";
    // Get the month and year of the date_str for use in matching by date, as needed
    $month = date('F',$date);
    $year = date('Y',$date);

    if ( $postID ) {

        $collect_args = array(
            'post_type'   => 'collect',
            'post_status' => 'publish',
            //'posts_per_page' => 1,
            );

        $litdate_title = get_the_title( $postID );
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
                    'compare'     => 'LIKE',
                ),
                'second_clause' => array(
                    'key' => 'date_calc',
                    'compare'     => 'EXISTS',
                ),
            );

        } else {

            $ts_info .= "NOT propers...<br />";

            // All other collects match by litdate
            $collect_args['meta_query'] = array(
                'relation' => 'AND',
                'first_clause' => array(
                    'key'     => 'related_liturgical_date',
                    'value'     => '"' . $postID . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                    'compare'     => 'LIKE',
                ),
                'second_clause' => array(
                    'key' => 'related_liturgical_date',
                    'compare'     => 'EXISTS',
                ),
            );

        }

        $ts_info .= "collect_args: <pre>".print_r($collect_args, true)."</pre>";

        $collects = new WP_Query( $collect_args );
        $collect_posts = $collects->posts;

        if ( count($collect_posts) == 1 ) {

            $ts_info .= "single matching collect post found<br />";
            $collect = $collect_posts[0];

        } elseif ( count($collect_posts) > 1 ) {

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

// Function(s) to calculate variable liturgical_dates

function getBasisDate( $year = null, $litdateCalcID = null, $calcBasis = null, $calcBasisID = null, $calcBasisField = null )
{
    //if ( empty($calcBasis) ) { return null; }

    $arr_info = array();

    $info = "";
    $basisDateStr = "";
    $basisDate = null;
    $abort = false;

    $info .= ">>> getBasisDate <<<<br />";
    $info .= "year: ".$year." // litdateCalcID: " . $litdateCalcID . " // calcBasis: " . $calcBasis . " // calcBasisID: " . $calcBasisID . " // calcBasisField: " . $calcBasisField . "<br />";

    if ( $calcBasis == 'christmas' ) {
        $basisDateStr = $year."-12-25";
    } elseif ( $calcBasis == 'epiphany' ) {
        $basisDateStr = $year."-01-06";
    } elseif ( $calcBasisID ) {
        // If the $calcBasis is a postID, first check to see if it's a fixed or variable date
        $basisDateType = get_post_meta( $calcBasisID, 'date_type', true );
        $info .= "basisDateType: ".$basisDateType."<br />";
        if ( $basisDateType == "fixed" ) {
            // NB: this code block is copied from the getDisplayDates method. That redundancy should be eliminated, but is being temporarily tolerated in the name of expediency
            if ( !$fixedDateStr = get_field( 'fixed_date_str', $calcBasisID ) ) { // this line diverges from the getDisplayDates version, using calcBasisID instead of postID
                $info .= "No fixed_date_str found.<br />";
            } else {
                $info .= "fixed_date_str: ".$fixedDateStr."<br />";
                if ( $year ) {
                    $fixedDateStr .= " ".$year;
                    $info .= "fixed_date_str (mod): ".$fixedDateStr."<br />";
                }
                $formattedFixedDateStr = date("Y-m-d",strtotime($fixedDateStr));
                $info .= "formattedFixedDateStr: ".$formattedFixedDateStr."<br />";
                $basisDateStr = $formattedFixedDateStr; // this line diverges from the getDisplayDates version
            }
        } else {
            // If variable, get the corresponding date_calculation for the given year
            $info .= "looking for a variable date match for year: ".$year."<br />";
            // TODO: run a DB query instead to find rows relevant by $year? -- maybe more efficient than retrieving all the rows
            if ( have_rows('date_calculations', $calcBasisID) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
                while ( have_rows('date_calculations', $calcBasisID) ) : the_row();
                    $dateCalculated = get_sub_field('date_calculated'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
                    $year_calculated = substr($dateCalculated, 0, 4);
                    if ( $year_calculated == $year ) {
                        $basisDateStr = $dateCalculated;
                    }
                endwhile;
            } // end if
            if ( !empty($basisDateStr) ) { $info .= "basisDateStr found: ".$basisDateStr."<br />"; } else { $info .= "NO basisDateStr found for the given year.<br />"; $abort = true; }
        }
    } elseif ( date('Y-m-d', strtotime($calcBasis)) == $calcBasis
                || strtolower(date('F d',strtotime($calcBasis))) == strtolower($calcBasis)
                || strtolower(date('F d Y',strtotime($calcBasis))) == strtolower($calcBasis)
        ) {
        // WIP: deal w/ possibilty that calcBasis is a date (str) -- in which case should be translated as the basisDate
        $info .= "calcBasis is a date: ".$calcBasis."<br />";
        // If the calcBasis date includes month/day only, then add the year
        if ( strtolower(date('F d',strtotime($calcBasis))) == $calcBasis ) {
            $calcBasis = $calcBasis." ". $year;
            // Then convert it to Y-m-d format
            $calcBasis = date('Y-m-d',strtotime($calcBasis));
        }
        $basisDateStr = $calcBasis;
    } elseif ( $litdateCalcID && $calcBasisField ) {
        $basisDateStr = get_post_meta( $litdateCalcID, $calcBasisField, true);
    } else {
        // If the calcBasis starts with a "the ", trim that off
        if ( substr($calcBasis,0,4) == "the ") { $calcBasis = substr($calcBasis,4); }
        // Append four-digit year (TODO: check first to see if it's already there? though when would that be the case...)
        $calcBasis .= " ".$year;
        $basisDateStr = date('Y-m-d',strtotime($calcBasis));
    }

    // If no basis date string has yet been established, then default to January first of the designated year
    // Careful! -- this can mess things up for variable dates that are dependent on other variable dates
    if ( empty($basisDateStr) && $abort === false ) {
        $basisDateStr = $year."-01-01";
        //if ( $verbose == "true" ) { $info .= "(basis date defaults to first of the year)<br />"; }
    }
    //if ( $verbose == "true" ) { $info .= "basisDateStr: $basisDateStr ($calcBasis)<br />"; } // '<span class="notice">'.</span> // ($calcBasis // $calcBasisField)

    if ( !empty($basisDateStr) ) {
        // Get the basis_date from the string version
        $basisDate = strtotime($basisDateStr);
        //$basisDate_weekday = strtolower( date('l', $basisDate) );
        //if ( $verbose == "true" ) { $info .= "basis_date: $basisDateStr<br />"; } // ($basisDate_weekday) .'<span class="notice">'.'</span>' //  ($calcBasis // $calcBasisField)
    }

    $arr_info['date'] = $basisDate;
    $arr_info['info'] = $info;

    //return $basisDate;
    return $arr_info;

}

function getCalcBasesFromStr ( $dateCalcStr = "", $idsToExclude = array() )
{
    // Init vars
    $arr_info = array();
    $arrCalcBases = array();
    $info = "";

    // litdate found in $dateCalcStr?
    // query titles of  litdates to see if any are found in $dateCalcStr (complete)
    // hm as it currently is, it's looking for entire $dateCalcStr in title -- but what we need is to look for titles within dateCalcStr...
    // wip
    // Set up query args
    $wp_args = array(
        'post_type'        => 'liturgical_date',
        'post_status'   => 'publish',
        'posts_per_page'=> -1,
        'orderby'       => 'title',
        'order'         => 'ASC',
        //'return_fields' => 'ids',
        '_search_title'    => $dateCalcStr,
    );

    if ( !empty($idsToExclude) ) { $wp_args['post__not_in'] = $idsToExclude; }

    // Run the query
    $arr_posts = new WP_Query( $wp_args );

    //if ( $verbose == "true" ) {
        $info .= "WP_Query run as follows:";
        $info .= "<pre>query args: ".print_r($wp_args, true)."</pre>";
        $info .= "[".count($arr_posts->posts)."] posts found matching dateCalcStr.<br />";
        //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre>";
    //}
    if ( count($arr_posts->posts) > 0 ) {
        foreach ( $arr_posts->posts AS $post ) {
            // Check to make sure this isn't an "Eve of" or "Week of" date before adding it to the array
            $calcBasis = strtolower($post->post_title);
            if ( strpos($calcBasis, 'eve of ') == false && strpos($calcBasis, 'week of ') == false ) {
                $arrCalcBases[] = array( 'post_id' => $post->ID, 'basis' => $calcBasis );
            } else {
                //
            }
        }
    } else {
        // if not...
        // lit basis found in $dateCalcStr?
        $liturgical_bases = array('advent' => 'advent_sunday_date', 'christmas' => 'December 25', 'epiphany' => 'January 6', 'ash wednesday' => 'ash_wednesday_date', 'lent' => 'ash_wednesday_date', 'easter' => 'easter_date', 'ascension day' => 'ascension_date', 'pentecost' => 'pentecost_date' );

        // Get the liturgical date info upon which the calculation should be based (basis extracted from the dateCalcStr)
        foreach ( $liturgical_bases AS $basis => $basisField ) {
            if (stripos($dateCalcStr, $basis) !== false) {
                $arrCalcBases[] = array( 'basis' => $basis, 'basisField' => $basisField );
                //if ( $verbose == "true" ) { $info .= "&rarr; "."calcBasis ".$basis." (".$basisField.") found in dateCalcStr.<br />"; }
            }
        }
    }

    //return $arrCalcBases;

    $arr_info['info'] = $info;
    $arr_info['calcBases'] = $arrCalcBases;
    //

    return $arr_info;

}

function getCalcBoiasFromStr ( $dateCalcStr = "" )
{

    $calcBoias = array();

    $boias = array('before', 'of', 'in', 'after'); // before/of/in/after the basis_date/season?

    // can we do this without the loop -- match str against array of substr?
    foreach ( $boias AS $boia ) {
        if ( preg_match_all('/\s*'.$boia.'\s*/', $dateCalcStr, $matches, PREG_OFFSET_CAPTURE) ) {
            //$info .= "&rarr; "."boia '$boia' found in dateCalcStr<br />"; //
            //$calcBoia = strtolower($boia);
            $calcBoias[] = strtolower($boia);
            if ( count($matches) > 1 ) {
                $complex_formula = true;
                //$info .= count($matches)." boia matches for '$boia'<br />";
                //foreach ( $matches as $match ) { }
            }
            //if ( $verbose == "true" ) { $info .= "boia matches: ".print_r($matches, true)."<br />"; } //<pre></pre>
        }
    }

    return $calcBoias;

}

function getCalcWeekdaysFromStr ( $dateCalcStr = "" )
{

    $calcWeekdays = array();

    $weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

    // What's the weekday for the date to be calculated?
    $calcWeekdays = array();
    foreach ( $weekdays AS $weekday ) {
        if (stripos($dateCalcStr, $weekday) !== false) {
            //$info .= "&rarr; "."weekday '$weekday' found in dateCalcStr<br />";
            $calcWeekdays[] = strtolower($weekday);
        }
    }

    return $calcWeekdays;

}

function parseDateStr ( $args = array() )
{
    $arr_info = array();
    $arr_elements = array();
    $complex_formula = false; // TODO: deal w/ subset of dates that are dependent on other variable dates which must be calculated first.
    //
    $info = "";
    $info .= '<strong>&gt;&gt;&gt; parseDateStr &lt;&lt;&lt;</strong><br />';
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;"; // TODO: define this with global scope for all plugin functions

    // Defaults
    $defaults = array(
        'year'                => null,
        'dateCalcStr'        => null,
        'verbose'            => true,
        'idsToExclude'    => array(), // IDs to exclude -- e.g. when dealing w/ "Eve of" dates -- so that "eve of" post doesn't find itself as a possible basis
    );

    // Parse & Extract args
    $args = wp_parse_args( $args, $defaults );
    //extract( $args );
    $year = $args['year'];
    $dateCalcStr = $args['dateCalcStr'];
    $idsToExclude = $args['idsToExclude'];
    $verbose = $args['verbose'];
    //
    //if ( $verbose == "true" ) { $info .= "parseDateStr args: <pre>".print_r($args, true)."</pre>"; }
    $dateCalcStr_bk = $dateCalcStr; // copy the $dateCalcStr to a new variable so we can preserve the original while making mods as needed
    //
    $liturgical_bases = array('advent' => 'advent_sunday_date', 'christmas' => 'December 25', 'epiphany' => 'January 6', 'ash wednesday' => 'ash_wednesday_date', 'lent' => 'ash_wednesday_date', 'easter' => 'easter_date', 'ascension day' => 'ascension_date', 'pentecost' => 'pentecost_date' ); // get rid of this here? only needed in this function for FYI components info -- not really functional
    //
    //$numbers = array('one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9); // WIP
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    $weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
    $boias = array('before', 'of', 'in', 'after'); // before/of/in/after the basis_date/season?
    //
    $components = array();
    $calcBasis = null;
    $calcBasisField = null;
    $calcBasisID = null;
    $calcBoia = null;
    $calcWeekday = null;
    $calcInterval = null;
    //

    // Loop through all the components of the exploded dateCalcStr and determine component type
    // WIP -- why do this? -- maybe to determine early on if this is a complex formula that must be broken down into sub-formulas...
    // "after the", "before the", "in the"(?)
    // e.g. Corpus Christi: "thursday after the 1st sunday after pentecost"
    // if str contains either multiple calcBases OR multiple boias, then break it into parts (nested) and process core first, then final based on calc core date

    $calc_components = explode(" ", $dateCalcStr);
    if ( $verbose == "true" ) { $info .= "[".count($calc_components)."] calc_components: ".print_r($calc_components,true)."<br />"; }
    $component_info = "";
    $previous_component = "";
    $previous_component_type = null;
    $i = 1;
    foreach ( $calc_components as $component ) {

        $component = strtolower($component);

        // First check to see if the component is a straight-up date! // date('Y-m-d', $calcDate) // (YYYY-MM-DD) //$calcDateStr = date('Y-m-d', $calcDate);
        if ( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $component) ) {
            $component_info .= $indent."component '".$component."' is a date<br />";
            $previous_component_type = "date";
        } elseif ( array_key_exists($component, $liturgical_bases) ) {
            //$component_info .= $indent."component '".$component."' is a liturgical_base<br />";
            $previous_component_type = "liturgical_base";
            if ( $component == $dateCalcStr ) {
                $component_info .= $indent."component '".$component."', a liturgical_base, is equal to the complete dateCalcStr: $dateCalcStr<br />";
                $calcBasis = $dateCalcStr;
            }
            // >> save as calcBasis, replacing loop below?
            // WIP
            // if multiple bases are found, proceed with the core subclause and then repeat calc...
            //
        } elseif ( in_array($component, $months) ) {
            $component_info .= $indent."component '".$component."' is a month<br />";
            $previous_component_type = "month";
        } elseif ( in_array($component, $weekdays) || in_array(substr($component, 0, strlen($component)-1), $weekdays) ) {
            $component_info .= $indent."component '".$component."' is a weekday<br />";
            if ( substr($component, -1) == "s" ) { $component_info .= $indent."component '".$component."' is plural<br />"; }
            $previous_component_type = "weekday";
        } elseif ( in_array($component, $boias) ) {
            $component_info .= $indent."component '".$component."' is a boia<br />";
            $previous_component_type = "boia";
            // Potential calcBasis?
            if ( empty($calcBasis)) {
                $calcBasis = trim(substr($dateCalcStr,strpos($dateCalcStr,$component)+strlen($component)));
                if ( in_array($calcBasis, $months) ) {
                    $component_info .= $indent.'calcBasis "'.$calcBasis.'" is a month. Therefore we must use first of that month in the given year as basis.<br />';
                    $calcBasis = "first day of ".$calcBasis;
                } else {
                    $component_info .= $indent.'calcBasis: '.$calcBasis."<br />";
                }
            }
        } elseif ( containsNumbers($component) ) { // what about "last"? do we need to deal with that here? or third? fourth? etc?
            $component_info .= $indent."component '".$component."' is numeric/intervalic<br />";
            //$component_info .= $indent."component '".$component."' is numeric/intervalic --> matches: ".print_r($matches,true)."<br />";
            // WIP...
            // Translate words to digits as needed
            /*if ( !is_int($component) ) {
                $component_translated = sdg_word_to_digit($component);
                if ( !is_int($component_translated) ) {
                    // wip
                }
                //$dateCalcStr = str_replace($component, $component_translated, $dateCalcStr);
                $component_info .= $indent."component_translated: '".$component_translated."'<br />";
            }*/
            if ( $previous_component_type == "month" ) {
                $component_info .= $indent."... and previous_component '".$previous_component."' is a month<br />";
                if ( empty($calcBasis)) { $calcBasis = $previous_component." ".$component; }
                // if not folloewd by year, add year wip...
                // only if last component?...
                //if ( $i == count($calc_components) ) { $calcBasis .= " ".$year; } // do this later via getBasisDate
            } else {
                $component_info .= $indent."... and previous_component '".$previous_component."' is a ".$previous_component_type."<br />";
            }
            $previous_component_type = "numeric";
        } elseif ($component == "the" ) { // wip
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

    // 1. Liturgical calc basis (calcBasis)
    //if ( $verbose == "true" ) { $info .= ">> getCalcBasesFromStr<br />"; }
    if ( $calcBasis ) {
        $calcBasis = strtolower($calcBasis);
        if ( array_key_exists($calcBasis, $liturgical_bases) ) {
            if ( $verbose == "true" ) { $info .= "calcBasis: $calcBasis is a liturgical_base<br />"; }
            $arrCalcBases = array();  // calcBases array needs to be array of arrays to match getCalcBasesFromStr results
            $basisField = $liturgical_bases[$calcBasis];
            $arrCalcBases[] = array('basis' => $calcBasis, 'basisField' => $basisField );
            $calcBasesInfo = array( 'info' => "calcBasis: $calcBasis is a liturgical_base<br />", 'calcBases' => $arrCalcBases );
        } else {
            if ( $verbose == "true" ) { $info .= ">> getCalcBasesFromStr using str calcBasis: $calcBasis<br />"; }
            $calcBasesInfo = getCalcBasesFromStr($calcBasis, $idsToExclude);
        }
    } else {
        if ( $verbose == "true" ) { $info .= ">> getCalcBasesFromStr using str dateCalcStr: $dateCalcStr<br />"; }
        $calcBasesInfo = getCalcBasesFromStr($dateCalcStr, $idsToExclude);
    }
    //$calcBasesInfo = getCalcBasesFromStr($dateCalcStr);
    $calcBases = $calcBasesInfo['calcBases'];
    if ( $verbose == "true" ) {
        //$info .= "calcBases: <pre>".print_r($calcBases, true)."</pre>";
        $info .= $calcBasesInfo['info']."<br />";
    }
    if ( empty($calcBases) ) {
        if ( $verbose == "true" ) { $info .= "No calcBasis found.<br />"; }
    } elseif ( count($calcBases) > 1 ) {
        $complex_formula = true;
        $info .= '<span class="notice">More than one calcBasis found!</span><br />';
        $info .= "calcBases: <pre>".print_r($calcBases, true)."</pre>";
        //$info .= '</div>';
        //$calc['calcInfo'] = $info;
        //return $calc; // abort early -- we don't know what to do with this dateCalcStr
        foreach ( $calcBases as $cb_tmp ) {
            if ( $cb_tmp['basis'] == $calcBasis ) {
                $info .= "cb_tmp basis: ".$cb_tmp['basis']." is identical to calcBasis<br />";
                $calcBasis = $cb_tmp['basis'];
                $calcBasisID = $cb_tmp['post_id'];
            }
        }
        //
    } elseif ( count($calcBases) == 1 ) {
        if ( $verbose == "true" ) { $info .= "Single calcBasis found.<br />"; }
        $cb = $calcBases[0];
        if ( is_array($cb) ) {
            $calcBasis = $cb['basis'];
            if ( isset($cb['post_id']) ) {
                $calcBasisID = $cb['post_id'];
            } elseif ( isset($cb['basisField']) ) {
                $calcBasisField = $cb['basisField'];
            }
            //$info .= "cb: <pre>".print_r($cb, true)."</pre>";
        } else {
            $calcBasis = $cb;
        }

        // clean up the calcBasis -- e.g. if we were looking for "the third sunday of advent" and got "the third sunday of advent (gaudete)"
        // Remove anything in parentheses or brackets
        if ( $verbose == "true" ) { $info .= "About to remove bracketed info from calcBasis.<br />"; }
        $calcBasis = remove_bracketed_info($calcBasis,true);
        $info .= "calcBasis: $calcBasis<br />";
        // Clean up the calcBasis a bit more, in case it's one of those long litdate titles, like "Saint Lucy, Martyr at Syracuse"
        $calcBasis = trim(explode(',', $calcBasis)[0]); // remove everything after the comma, if there is one
    }

    if ( $calcBasis ) { $components['calcBasis'] = $calcBasis; }
    if ( $calcBasisID ) { $components['calcBasisID'] = $calcBasisID; }
    if ( $calcBasisField ) { $components['calcBasisField'] = $calcBasisField; }
    if ( $verbose == "true" ) { $info .= "calcBasis: $calcBasis // calcBasisID: $calcBasisID // calcBasisField: $calcBasisField<br />"; }

    // 2. BOIAs
    // Does the date to be calculated fall before/after/of/in the basis_date/season?
    if ( $calcBasis ) {
        // If they're not already identical, then get the dateCalcStr minus the already determined calcBasis
        if ( $dateCalcStr != $calcBasis ) {
            if ( $verbose == "true" ) { $info .= "About to replace calcBasis '$calcBasis' in dateCalcStr '$dateCalcStr'<br />"; }
            $dateCalcStr = trim(str_ireplace($calcBasis,"",$dateCalcStr));
        }
        if ( strtotime($dateCalcStr) ) { $info .= 'dateCalcStr: "'.$dateCalcStr.'" is parseable by strtotime<br />'; } //else { $info .= 'dateCalcStr: "'.$dateCalcStr.'" is NOT parseable by strtotime<br />'; }
        if ( strtotime($dateCalcStr."today") ) { $info .= 'dateCalcStr: "'.$dateCalcStr.'" is parseable by strtotime with the addition of the word "today"<br />'; } //else { $info .= 'dateCalcStr: "'.$dateCalcStr.'" is NOT parseable by strtotime with the addition of the word "today"<br />'; }
        if ( $verbose == "true" ) { $info .= "getCalcBoiasFromStr from modified dateCalcStr: $dateCalcStr<br />"; }
    } else {
        if ( $verbose == "true" ) { $info .= "getCalcBoiasFromStr from unmodified dateCalcStr<br />"; }
    }
    $calcBoias = getCalcBoiasFromStr($dateCalcStr);
    if ( empty($calcBoias) ) {
        if ( $verbose == "true" ) { $info .= "No boias found.<br />"; }
    } elseif ( count($calcBoias) > 1 ) {
        $complex_formula = true;
        $info .= '<span class="notice">More than one calcBoia found!</span><br />';
        $info .= "calcBoias: ".print_r($calcBoias, true)."<br />"; //<pre></pre>
        //$info .= '</div>';
        //$calc['calcInfo'] = $info;
        //return $calc; // abort early -- we don't know what to do with this dateCalcStr
    } elseif ( count($calcBoias) == 1 ) {
        $calcBoia = $calcBoias[0];
        $components['calcBoia'] = $calcBoia;
        if ( $verbose == "true" ) { $info .= "calcBoia: $calcBoia<br />"; }
    }

    // 3. Weekdays
    $calcWeekdays = getCalcWeekdaysFromStr($dateCalcStr);
    if ( empty($calcWeekdays) ) {
        if ( $verbose == "true" ) { $info .= "No calcWeekday found.<br />"; }
    } elseif ( count($calcWeekdays) > 1 ) {
        $complex_formula = true;
        $info .= '<span class="notice">More than one calcWeekday found!</span><br />';
        $info .= "calcWeekdays: ".print_r($calcWeekdays, true)."<br />"; //<pre></pre>
        //$info .= '</div>';
        //$calc['calcInfo'] = $info;
        //return $calc; // abort early -- we don't know what to do with this dateCalcStr
    } elseif ( count($calcWeekdays) == 1 ) {
        $calcWeekday = $calcWeekdays[0];
        $components['calcWeekday'] = $calcWeekday;
        if ( $verbose == "true" ) { $info .= "calcWeekday: $calcWeekday<br />"; }
    }
    //

    // 4. Calc interval(s)
    // WIP 240903
    // translate words to digits etc -- move some functionality from calcDateFromComponents
    // $calcInterval
    // in combo with calcBoia and calcWeekday, translate dateCalcStr into something that can be handled by php strtotime
    // e.g. two sundays before >> 2 sundays previous >> previous sunday - 6 days >>> previous sunday - X weeks + 1 day

    // phase this out? or generalize?
    // If it's a complex formula, extract the sub_formula upon which the final calc will be based
    if ( $complex_formula ) {
        if ( $verbose == "true" ) { $info .= "This is a complex_formula => extract the sub_formula<br />"; }

        if ( strpos(strtolower($dateCalcStr), 'after the ') !== false ) {
            $subCalcStr = trim(substr( $dateCalcStr, strpos($dateCalcStr, "after the ")+9 )); // WIP 231204 -- generalize beyond Corpus Christi?
        } else {
            $subCalcStr = ""; // ???
        }
        $info .= "subCalcStr: $subCalcStr<br />";

        $components['dateCalcStr'] = $subCalcStr;
        //if ( count($calcWeekdays) > 1 ) { $components['calcWeekday'] = $calcWeekdays[1]; }
        //
        $calcWeekdays = getCalcWeekdaysFromStr($subCalcStr);
        if ( count($calcWeekdays) == 1 ) {
            $components['calcWeekday'] = $calcWeekdays[0];
        }
        //
        $arr_elements['subCalcStr'] = $components;
        //
        if ( strpos(strtolower($dateCalcStr), 'after the ') !== false ) {
            $superCalcStr = trim(substr( $dateCalcStr, 0, strpos($dateCalcStr, "after the")+9 ))." subCalcStr"; // WIP 231204
        } else {
            $superCalcStr = ""; // ???
        }

        $components['dateCalcStr'] = $superCalcStr;
        //
        $calcWeekdays = getCalcWeekdaysFromStr($superCalcStr);
        if ( count($calcWeekdays) == 1 ) {
            $components['calcWeekday'] = $calcWeekdays[0];
        }
        //
        $arr_elements['superCalcStr'] = $components;
        $info .= "superCalcStr: $superCalcStr<br />";
    } else {
        $components['dateCalcStr'] = $dateCalcStr;
        $arr_elements['calcStr'] = $components;
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

// WIP 250728
// WIP: Translate the date calculation string into components that can be used to do date math, and then do that math to calculate the date
function calcDateFromStr( $args = array() )
{
    // Defaults
    $defaults = array(
        'year'                => null,
        'dateCalcStr'        => null,
        'verbose'            => false,
        'idsToExclude'        => array(),
    );

    // Parse & Extract args
    $args = wp_parse_args( $args, $defaults );
    //extract( $args );
    $year = $args['year'];
    $dateCalcStr = $args['dateCalcStr'];
    $idsToExclude = $args['idsToExclude'];
    $verbose = $args['verbose'];

    // Init vars
    $arr_info = array();
    $info = "";
    $calcDate = null;
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;"; // TODO: define this with global scope for all plugin functions

    // Abort if dateCalcStr or year is empty
    if ( empty($dateCalcStr) || empty($year) ) {
        $info .= "Insufficient data provided<br />";
        $info .= "dateCalcStr: $dateCalcStr; year: $year<br />";
        $arr_info['calcInfo'] = $info;
        return $arr_info;
    }

    $info .= '<strong>&gt;&gt;&gt; calcDateFromStr &lt;&lt;&lt;</strong><br />';
    if ( $verbose == "true" ) { $info .= "year: ".$year."<br />"; }
    if ( $verbose == "true" ) { $info .= "dateCalcStr: ".$dateCalcStr."<br />"; }

    // Find the liturgical_date_calc post for the selected year
    // TODO: *maybe* -- phase this out in favor of simply using php easter_date function. ( easter_date(year); )
    //$litdateCalcID = getLiturgicalDateCalcID ( $year ); // WIP
    // (liturgical_date_calc records contain the dates for Easter, Ash Wednesday, &c. per year)
    // TODO: make this a separate function?
    $wp_args = array(
        'post_type'   => 'liturgical_date_calc',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields'    => 'ids',
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
        $litdateCalcID = $posts[0];
        if ( $verbose == "true" ) { $info .= "liturgical_date_calc_id: $litdateCalcID<br />"; }
    } else {
        if ( $verbose == "true" ) { $info .= "No matching liturgical_date_calc_post for wp_args: ".print_r($wp_args,true)."<br />"; } // <pre></pre>
        $litdateCalcID = null;
        // TBD: abort?
    }

    // Parse the date string
    $args = array( 'year' => $year, 'dateCalcStr' => $dateCalcStr, 'verbose' => $verbose, 'idsToExclude' => $idsToExclude );
    $date_elements_info = parseDateStr ( $args );
    $info .= $date_elements_info['info'];
    $date_elements = $date_elements_info['elements'];
    $calcDate = null;
    $newBasisDateStr = null;
    //
    // >> loop through elements foreach $elements as $element => $components
    foreach ( $date_elements as $element => $components ) { //foreach ( $date_elements as $components ) {

        $info .= "element: [".$element."]<br />";
        //
        if ( isset($components['dateCalcStr']) ) { $dateCalcStr = $components['dateCalcStr']; }
        //
        if ( isset($components['calcBasis']) && strtolower($dateCalcStr) == $components['calcBasis'] ) { // Easter, Christmas, Ash Wednesday, Pentecost", &c.=
            if ( $verbose == "true" ) { $info .= "calcBasis: " . $components['calcBasis'] . "same as dateCalcStr: $dateCalcStr. About to getBasisDate.<br />"; }
            //getBasisDate( $year = null, $litdateCalcID = null, $calcBasis = null, $calcBasisID = null, $calcBasisField = null )
            $arrBasisDate = getBasisDate( $year, $litdateCalcID, $components['calcBasis'], null, $components['calcBasisField'] );
            $calcDate = $arrBasisDate['date'];
            $info .= $arrBasisDate['info'];
            $info .= "date to be calculated is same as basis_date => calcDate = getBasisDate: $calcDate<br />";
        } else {
            //
            if ( $newBasisDateStr ) {
                $info .= "newBasisDateStr: ".$newBasisDateStr."<br />";
                // TODO: str_replace "the subCalcStr" in dateCalcStr
                $dateCalcStr = str_replace("the subCalcStr", $newBasisDateStr, $dateCalcStr );
                //$dateCalcStr = str_replace("subCalcStr", $newBasisDateStr, $dateCalcStr );
                $components['calcBasis'] = $newBasisDateStr;
                $components['calcBasisField'] = null;
                $components['dateCalcStr'] = $dateCalcStr;
            }
            //
            $components['year'] = $year;
            $components['liturgical_date_calc_id'] = $litdateCalcID;
            //$components['dateCalcStr'] = $dateCalcStr;
            $components['verbose'] = $verbose;
            //
            //$info .= "[".$element."] components: <pre>".print_r($components, true)."</pre>";

            $calc = calcDateFromComponents( $components );
            $info .= $calc['info'];
            $calcDate = $calc['date'];
        }
        // WIP -- if more than one element, get $calcDate as $new_basis_date from first calc and pass it to second in loop
        if ( is_int($calcDate) ) {
            $newBasisDateStr = date("Y-m-d", $calcDate );
        } else if ( $verbose == "true" ) {
            if ( empty($calcDate) ) {
                $info .= '<span class="notice">'."Cannot create newBasisDateStr -- calcDate is empty</span>".'<br />';
            } else {
                $info .= '<span class="notice">'."Cannot create newBasisDateStr from calcDate: ".$calcDate." because it's a string</span>".'<br />';
            }
        }
    }

    if ( $calcDate ) {
        if ( $verbose == "true" ) { $info .= 'calcDate: '.$calcDate.'<br />'; } //'<span class="notice">'.'</span>'.
        if ( is_int($calcDate) ) {
            $info .= '<span class="notice">'.'calcDate (timestamp >> formatted): '.date('Y-m-d', $calcDate).'</span>'.'<br />';
        } else {
            $info .= '<span class="notice">'."calcDate not a valid date: ".$calcDate." (string)</span>".'<br />';
            $calcDate = null;
        }
    }

    $arr_info['calcDate'] = $calcDate;
    $arr_info['calcInfo'] = $info;

    return $arr_info;

}

function calcDateFromComponents ( $args = array() )
{
    // Init vars
    $arr_info = array();
    $info = "";
    $calcDate = null;
    //
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;"; // TODO: define this with global scope for all plugin functions

    // Defaults
    $defaults = array(
        'year'           => null,
        'liturgical_date_calc_id'=> null,
        'dateCalcStr'    => null,
        'calcBasis'      => null,
        'calcBasisID'    => null,
        'calcBasisField' => null,
        'calcBoia'       => null,
        'calcWeekday'    => null,
        'verbose'        => false,
    );

    // Parse & Extract args
    $args = wp_parse_args( $args, $defaults );
    //extract( $args );
    $verbose = $args['verbose'];
    $year = $args['year'];
    $litdateCalcID = $args['liturgical_date_calc_id'];
    $dateCalcStr = $args['dateCalcStr'];
    $calcBasis = $args['calcBasis'];
    $calcBasisID = $args['calcBasisID'];
    $calcBasisField = $args['calcBasisField'];
    $calcBoia = $args['calcBoia'];
    $calcWeekday = $args['calcWeekday'];
    //
    $info .= '<strong>&gt;&gt;&gt; calcDateFromComponents &lt;&lt;&lt;</strong><br />';
    if ( $verbose == "true" ) { $info .= "args: <pre>".print_r($args, true)."</pre>"; }

    // Get the basis date in the given year, from the Liturgical Date Calculations CPT (liturgical_date_calc)
    $arrBasisDate = getBasisDate( $year, $litdateCalcID, $calcBasis, $calcBasisID, $calcBasisField );
    $basisDate = $arrBasisDate['date'];
    $info .= $arrBasisDate['info'];
    if ( $calcBasis == "epiphany" ) {
        $num_sundays_after_epiphany = get_post_meta( $litdateCalcID, 'num_sundays_after_epiphany', true);
    }
    // Return early if no basisDate has been found
    if ( empty($basisDate) ) {
        if ( $verbose == "true" ) { $info .= "NO VALID basisDate FOUND!<br />-- via getBasisDate for year: $year, liturgical_date_calc_id: $litdateCalcID, calcBasis: $calcBasis, calcBasisID: $calcBasisID, calcBasisField: $calcBasisField<br />"; }        
        // Abort!
        return ['date' => null, 'info' => $info];
    }
    if ( $verbose == "true" ) {
        $info .= "basisDate: $basisDate (".date('Y-m-d (l)', $basisDate).") <br />-- via getBasisDate for year: $year, liturgical_date_calc_id: $litdateCalcID, calcBasis: $calcBasis, calcBasisID: $calcBasisID, calcBasisField: $calcBasisField<br />";
    }

    // Check to see if the date to be calculated is in fact the same as the base date
    if ( strtolower($dateCalcStr) == $calcBasis ) { // Easter, Christmas, Ash Wednesday", &c.=

        $calcDate = $basisDate;
        $info .= "date to be calculated is same as basis_date.<br />";

    } else {

        $calcFormula = null;
        $calcInterval = null;
        // TODO: deal w/ propers -- e.g. "Week of the Sunday closest to May 11"

        // Take a closer look at the basis_date
        if ( is_numeric($basisDate) && $verbose == "true" ) { $info .= "basis_date is a timestamp.<br />"; }

        // Check to see if the basis_date is a Sunday
        $basisDate_weekday = strtolower( date('l', $basisDate) );
        if ( $basisDate_weekday != "" && $basisDate_weekday != 'sunday' ) {

            // If the basis_date is NOT a Sunday, then get the date of the first_sunday of the basis season
            $first_sunday = strtotime("next Sunday", $basisDate);
            if ( $verbose == "true" ) { $info .= "first_sunday after basis_date is ".date("Y-m-d", $first_sunday)."<br />"; }

        } elseif ( $basisDate ) {

            $first_sunday = $basisDate;
            if ( $verbose == "true" ) { $info .= "first_sunday is equal to basis_date.<br />"; }

        }

        // ** Extract components of dateCalcStr & calculate date for $year
        // ** Determine the calcInterval -- number of days/weeks...
        if ( containsNumbers($dateCalcStr) ) {

            // TODO/wip: also check for "two" etc
            if ( $verbose == "true" ) { $info .= "dateCalcStr {$dateCalcStr} contains numbers.<br />"; }

            // Determine the calcInterval
            // WIP deal w/ multiple value possibilities for weekday, boia
            if ( !is_array($calcWeekday) && !is_array($calcBoia) ) { //&& !empty($calcWeekday) && !empty($calcBoia)
                // TODO: fix this
                $numbers = extractNumbers($dateCalcStr);
                if ( $verbose == "true" ) { $info .= "numbers: ".print_r($numbers,true)."<br />"; } //<pre></pre>
                if ( count($numbers) == 1 ) {
                    $calcInterval = $numbers[0];
                    //$calcInterval = "";
                } else {
                    $calcInterval = str_replace([$calcBasis, $calcWeekday, $calcBoia], '', strtolower($dateCalcStr) );
                    $calcInterval = str_replace(['the', 'th', 'nd', 'rd', 'st'], '', strtolower($dateCalcStr) );
                }
                $calcInterval = trim( $calcInterval );
            }
            if ( $verbose == "true" && !empty($calcInterval) ) { $info .= "calcInterval: $calcInterval<br />"; }

            //if ( $calcBoia == ("in" || "of") ) { // Advent, Easter, Lent
            if ( !empty($calcInterval) && (
                ( $calcBasis == "advent" && $calcBoia != "before" )
                || ( $calcBasis == "easter" && $calcBoia == "of" )
                || ( strtolower(date('F d',strtotime($calcBasis))) == strtolower($calcBasis) )
                ) ) {

                $calcInterval = (int) $calcInterval - 1; // Because Advent Sunday is first Sunday of Advent, so 2nd Sunday is basis_date + 1 week, not 2

            } elseif ( $first_sunday == $basisDate && $dateCalcStr == "first sunday of"  ) {

                if ( $verbose == "true" ) { $info .= "dateCalcStr == first sunday of && first_sunday == basis_date &#8756; calcDate = first_sunday<br />"; }
                $calcDate = $first_sunday;

            } elseif ( $first_sunday != $basisDate ) {

                if ( $verbose == "true" ) { $info .= "first_sunday NE basis_date<br />"; }

                if ( $calcInterval ) { // && is_int($calcInterval)
                    if ( $verbose == "true" ) { $info .= "Subtracting one from calcInterval ($calcInterval - 1)<br />"; }
                    $calcInterval = $calcInterval - 1; // because math is based on first_sunday + X weeks. -- but only if calcWeekday is also Sunday? WIP
                }
                // ???
                if ( $calcInterval === 0 ) {
                    $calcDate = $first_sunday;
                    if ( $verbose == "true" ) { $info .= "Set calcDate = first_sunday ($first_sunday)<br />"; }
                }

            }

            if ( $verbose == "true" && !empty($calcInterval) ) { $info .= "calcInterval (final): $calcInterval<br />"; }

        } elseif ( strpos(strtolower($dateCalcStr), 'last') !== false ) {

            // e.g. "Last Sunday after the Epiphany"; "Last Sunday before Advent"; "Last Sunday before Easter"
            //$info .= $indent."LAST<br />"; // tft
            if ( $calcBasis == "epiphany" ) {
                $calcInterval = $num_sundays_after_epiphany; // WIP 240113
            } elseif ( $calcBasis == "easter" ) { // && $calcBoia == "before"
                $calcFormula = "previous Sunday"; //$calcFormula = "Sunday before";
            } elseif ( $dateCalcStr == "last sunday before advent" ) {
                $calcFormula = "previous Sunday"; //$calcFormula = "Sunday before";
            }

        } else {
            $info .= "dateCalcStr '$dateCalcStr' does NOT contain numbers nor the substr 'last'.<br />";
        }

        // If the calcFormula hasn't already been determined, build it
        if ( empty($calcDate) && $calcFormula == "" ) {
            if ( $verbose == "true" ) { $info .= "About to build calcFormula...<br />"; }
            if ( !empty($calcInterval) && strpos(strtolower($calcInterval), 'days') !== false ) {
                if ( $verbose == "true" ) { $info .= "Calc by days +/-...<br />"; }
                //
                if ( $calcInterval && $calcBoia == "before" ) {
                    $calcFormula = "-".$calcInterval;
                } elseif ( $calcInterval && $calcBoia == "after" ) {
                    $calcFormula = "+".$calcInterval;
                }
            } else {
                if ( $calcBasis != "" && $calcWeekday == "sunday" ) {
                    if ( ($calcInterval > 1 && $calcBoia != "before") || ($calcInterval == 1 && $calcBoia == ("after" || "in") ) ) {
                        $calcFormula = "+".$calcInterval." weeks";
                        $basisDate = $first_sunday;
                    } elseif ( ($calcInterval > 1 && $calcBoia == "before" ) ) {
                        $calcFormula = "-".$calcInterval." weeks";
                        $basisDate = $first_sunday;
                    } elseif ( $calcBoia == "before" ) {
                        $calcFormula = "previous Sunday";
                    } elseif ( $calcBoia == "after" ) {
                        $calcFormula = "next Sunday";
                    } elseif ( $first_sunday ) {
                        $calcDate = $first_sunday; // e.g. "First Sunday of Advent"; "The First Sunday In Lent"
                    }
                } elseif ( $calcBasis != "" && ( $calcBoia == "before" || $calcBoia == "after" ) ) {
                    $info .= $indent."setting prev/next<br />"; // tft

                    // e.g. Thursday before Easter; Saturday after Easter -- BUT NOT for First Monday in September; Fourth Thursday in November -- those work fine as they are via simple strtotime
                    if ( $calcBoia == "before" ) { $prev_next = "previous"; } else { $prev_next = "next"; } // could also use "last" instead of "previous"
                    if ( !empty($calcWeekday) ) {
                        $calcFormula = $prev_next." ".$calcWeekday; // e.g. "previous Friday";
                    } else {
                        $calcFormula = $prev_next." day"; // e.g. "previous day";
                    }
                    $info .= "calcFormula: $calcFormula<br />";
                } else {
                    $info .= $indent."no action taken to determine date/formula based on calcBasis: $calcBasis / calcWeekday: $calcWeekday / calcBoia: $calcBoia<br />";
                }
            }
        }

        // If there's no $calcFormula yet, use the dateCalcStr directly
        if ( empty($calcFormula) && empty($calcDate) ) {
            $info .= '<span class="notice">'."calc based directly on dateCalcStr</span><br />"; // .'</span>'
            $info .= "dateCalcStr: '$dateCalcStr' // calcBasis: '$calcBasis'<br />";
            if ( $dateCalcStr == $calcBasis ) {
                $snakeBasis = snake($calcBasis); //$snakeBasis = strtolower(str_replace(' ', '_', trim($dateCalcStr)));
                $meta_key = $snakeBasis . '_date'; // e.g. 'ash_wednesday_date'
                $calcDate = get_post_meta( $litdateCalcID, $meta_key, true);
                $info .= "calcDate found via meta_key: '$meta_key': $calcDate<br />";
            } elseif ( $calcBoia != "after" ) {
                $calcFormula = $dateCalcStr;
            } else {
                if ( $verbose == "true" ) { $info .= '<span class="notice">'."Unable to determine calcFormula -- calcBoia: \"$calcBoia\"; calcDate: $calcDate</span><br />"; }
            }
        }

        //$info .= $indent.">> dateCalcStr: $dateCalcStr<br />"; // tft
        //$info .= $indent.">> [$calcInterval] -- [$calcWeekday] -- [$calcBoia] -- [$calcBasisField]<br />";
        //$info .= $indent.'>> basis_date unformatted: "'.$basisDate.'<br />'; // tft
        //
        // calcDate not yet determined >> do the actual calculation using the formula and basis_date
        if ( empty($calcDate) ) {

            $info .= '>> calcFormula: "'.$calcFormula.'"; basis_date: '.date('Y-m-d',$basisDate).'<br />';

            // WIP/TODO: deal w/ complex cases like Corpus Christi: "thursday after the 1st sunday after pentecost"
            // Must check to see if Pentecost is a Sunday, and if so, the basis_date must be set to the next Sunday after that.
            //

            if ( $calcFormula != "" && $basisDate != "" ) {
                $calcDate = strtotime("$calcFormula", $basisDate);
            } else {
                $info .= "Can't do calc -- calcFormula or basis_date is empty.<br />";
            }
            //$info .= $indent.'strtotime("'.$calcFormula.'",$basisDate)<br />';
            //$info .= $indent."calcDate -- ".$calcDate.' = strtotime("'.$calcFormula.'", '.$basisDate.')<br />'; // tft
            // X-check with https://www.w3schools.com/php/phptryit.asp?filename=tryphp_func_strtotime
            // calcFormula examples: '-6 months' // '+2 year' // "last Sunday" // "+4 weeks" // "next Sunday" // '+1 week'
        }

        // Make sure the calculated date doesn't conflict with the subsequent church season -- this applies to only Epiphany (into Lent) and Pentecost (into Advent)
        if ( $calcBasis == "epiphany" ) {

            $ash_wednesday_date = get_post_meta( $litdateCalcID, 'ash_wednesday_date', true);
            if ( empty($ash_wednesday_date) ) {
                $info .= $indent."No ash_wednesday_date found for liturgical_date_calc_id: $litdateCalcID<br />";
                // TBD: abort?
            }
            if ( $verbose == "true" ) { $info .= "ash_wednesday_date: ".$ash_wednesday_date."<br />"; }

            // Make sure this supposed date in the Epiphany season doesn't run into Lent
            $info .= "There are $num_sundays_after_epiphany Sundays after Epiphany in $year.<br />"; // tft
            if ( $calcDate > strtotime($ash_wednesday_date) ) { //if ( (int) $calcInterval > (int) $num_sundays_after_epiphany ) {
                $info .= $indent.'<span class="warning">Uh oh! That\'s too many Sundays.</span><br />'; // tft
                $info .= $indent.'<span class="warning">calcDate: ['.date('Y-m-d', $calcDate).']; ash_wednesday_date: '.$ash_wednesday_date.'</span><br />'; // tft
                $calcDate = "N/A";
            }

        } elseif ( $calcBasis == "lent" ) {

            // TODO: make sure date doesn't overlap w/ holy week

        } elseif ( $calcBasis == "pentecost" ) {

            // Make sure this supposed date in Ordinary Time/Pentecost season isn't actually in Advent
            // Pentecost: "This season ends on the Saturday before the First Sunday of Advent."
            // TODO -- figure out if this is the LAST Sunday of Pentecost?

            $advent_sunday_date = get_post_meta( $litdateCalcID, 'advent_sunday_date', true);
            if ( $verbose == "true" ) { $info .= "advent_sunday_date: ".$advent_sunday_date."<br />"; }

            // WIP
            //$pentecost_date = get_post_meta( $litdateCalcID, 'pentecost_date', true);
            //if ( $verbose == "true" ) { $info .= "pentecost_date: ".$pentecost_date."<br />"; }

            //if ( $calcDate > strtotime($advent_sunday_date) ) {
            if ( $calcDate > strtotime("previous Saturday", strtotime($advent_sunday_date) ) ) {
                //$info .= $indent.date( 'Y-m-d', strtotime("previous Saturday", strtotime($advent_sunday_date)) );
                $info .= $indent.'<span class="warning">'."Uh oh! ".date('Y-m-d',$calcDate)." conflicts with Advent. Advent Sunday is $advent_sunday_date.</span><br />"; // tft
                $calcDate = null; //$calcDate = "N/A";
            } else {
                $info .= $indent."Ok -- Advent begins on $advent_sunday_date.<br />"; // tft
            }
        }

    }
    $info .= "<br />"; // <hr /><br />

    $arr_info['date'] = $calcDate;
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
    } elseif ( strpos($years, '-') !== false ) {
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
        'order'    => $order,
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
    //if ( $verbose == "true" ) { $info .= "wp_args: <pre>".print_r( $wp_args, true )."<br />"; }
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
        $calcInfo = "";
        $calcDate = null;
        $calcDateStr = "";

        $changes_made = false;
        $complex_formula = false;

        // Get date_calculation info & break it down
        $dateCalcStr = strtolower(get_post_meta( $postID, 'date_calculation', true ));

        // Is this an "Eve of" litdate? Check to see if post_title begins with "Eve of" or "The Eve of"
        if ( strpos($post_title, "Eve of") === 0 || strpos($post_title, "The Eve of") === 0 ) {
            if ( $verbose == "true" ) { $info .= "Special case: 'Eve of' litdate ($post_title)<br />"; }
            if ( empty($dateCalcStr) ) {
                if ( $verbose == "true" ) { $info .= "dateCalcStr is empty => build it based on post_title<br />"; }
                if ( strpos($post_title, "The Eve of") === 0 ) { $post_title_mod = str_replace("The Eve of ", "", $post_title); } else { $post_title_mod = str_replace("Eve of ", "", $post_title); }    // TODO make more efficient w/ regexp?
                $dateCalcStr = "Day before ".$post_title_mod;
            }
        } else {
             // Clean it up a little
            $dateCalcStr = str_replace('christmas day', 'christmas', strtolower($dateCalcStr) );
            $dateCalcStr = str_replace('the epiphany', 'epiphany', strtolower($dateCalcStr) );
            //$dateCalcStr = str_replace(['the', 'day'], '', strtolower($dateCalcStr) );
        }

        foreach ( $arr_years as $year ) {

            $calcInfo .= "<hr />About to do calc for year: $year<br />+~+~+~+~+<br />";

            if ( !empty($dateCalcStr) ) {
                $calcInfo .= "dateCalcStr: $dateCalcStr<br />";
                $calc_args = array( 'year' => $year, 'dateCalcStr' => $dateCalcStr, 'verbose' => $verbose, 'idsToExclude' => array($postID) ); // exclude post's own id from calc basis determinations etc. --TODO/TBD: just past post_id, not array. Not sure when we'd need to exclude more than one post by id...
                $calc = calcDateFromStr( $calc_args ); //$calc = calcDateFromStr( $year, $dateCalcStr, $verbose );
                if ( $calc ) {
                    $calcDate = $calc['calcDate'];
                    $calcInfo .= $calc['calcInfo'];
                } else {
                    $calcInfo .= '<span class="error">calcDateFromStr failed</span><br />';
                    if ( $verbose == "true" ) { $calcInfo .= "calc_args: <pre>".print_r($calc_args,true)."</pre>"; }
                    if ( $verbose == "true" ) { $calcInfo .= "calc: <pre>".print_r($calc,true)."</pre>"; }
                }
            } else {
                $calcInfo .= "dateCalcStr is empty<br />";
                //$calc = null;
            }

            if ( !empty($calcDate) && $calcDate != "N/A" ) {
                $calcDateStr = date('Y-m-d', $calcDate);
                //$calcDateStr = date('Ymd', $calcDate); // was originally 'Y-m-d' format, which is more readable in DB, but ACF stores values edited via CMS *without* hyphens, despite field setting -- bug? or am I missing something?
                $calcInfo .= "calcDateStr: <strong>$calcDateStr</strong> (".date('l, F d, Y',$calcDate).")<br />"; // tft
            } else {
                $calcInfo .= "calcDate N/A<br />";
            }

            // 3. Save dates to ACF repeater field row for date_calculatedday_
            // DB: date_calculations >> date_calculated -- date_calculations_[#]_date_calculated

            if ( $calcDateStr != "" ) {

                $newrow = true;

                if ( have_rows('date_calculations', $postID) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
                    while ( have_rows('date_calculations', $postID) ) : the_row();
                        $dateCalculated = get_sub_field('date_calculated'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
                        if ( $dateCalculated == $calcDateStr ) {
                            // Already in there
                            $newrow = false;
                            $calcInfo .= "+++ Old news. This date_calculated ($calcDateStr) is already in the database. +++<br />"; // tft
                        } else {
                            //$calcInfo .= "Old date_calculated: $dateCalculated.<br />"; // tft
                        }
                    endwhile;
                } // end if

                if ( $newrow == true ) {

                    $row = array(
                        'date_calculated' => $calcDateStr
                    );

                    $calcInfo .= "About to add row to post_id $postID: ".print_r( $row, true )."<br />"; // <pre></pre>
                    if ( $testing != "true" ) {
                        if ( add_row('date_calculations', $row, $postID) ) { // ACF function syntax: add_row($selector, $value, [$postID])
                            $calcInfo .= "ACF row added for post_id: $postID<br />";
                        } else {
                            $calcInfo .= "ACF add row FAILED for post_id: $postID<br />";
                        }
                    }

                }
            } else {
                $calcInfo .= "calcDateStr is empty.<br />";
            }

            if ( count($arr_years) > 1 ) { $calcInfo .= "<br />"; }

        } // END foreach arr_years

        $info .= $calcInfo;
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
    } elseif ($service === "evening_prayer") {
        $info .= $ep_psalms; // return Psalms for Morning Prayer
    } else {
        $info .= $mp_psalms.", ".$ep_psalms; // return ALL Psalms of the Day
    }

    return $info;

}
