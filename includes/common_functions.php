<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
    exit;
}

/*********** MISC METHODS ***********/

function sdg_log( $log_msg, $do_log = true ) {

    // Set do_ts to true for active troubleshooting; false for cleaner source & logs
    if ( $do_log === false ) { return; } // Abort if logging is turned off (set per calling fcn)

    // Create directory for storage of log files, if it doesn't exist already
    $log_filename = $_SERVER['DOCUMENT_ROOT']."/_sdg-devlog";
    if (!file_exists($log_filename)) {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }

    $timestamp = current_time('mysql'); // use WordPress function instead of straight PHP so that timezone is correct -- see https://codex.wordpress.org/Function_Reference/current_time
    //sdg_log( "loop_item_divider", $do_log );
    if ($log_msg == "divline1") {
        $log_msg = "\n=================================================================================\n";
    } else if ($log_msg == "divline2") {
        $log_msg = "-----------";
    } else {
        $log_msg = "[sdg_log ".$timestamp."] ".$log_msg;
        //$log_msg = "[sdg_log ".$timestamp."] ".$log_msg."\n";
    }

    // Generate a new log file name based on the date
    //$datestamp = current_time('Ymd'); // date('d-M-Y') // Old version -- daily logs
    // New version -- monthly logs
    $this_month = current_time('Ym');
    $log_file = $log_filename.'/'.$this_month.'-sdg_dev.log';

    // TODO/WIP: check server for past months' logs and delete them if they're more than one month old
    $last_month = date("Ym", strtotime("first day of previous month"));
    //$two_months_ago = $this_month-2;
    $two_months_ago = date("Ym", strtotime("-2 months"));
    if ( $last_month != $two_months_ago ) {
        $stale_log_file = $log_filename.'/'.$two_months_ago.'-sdg_dev.log';
    } else {
        $two_months_ago = date("Ym", strtotime("-3 months"));
        // wip deal w/ possible issue with date math if month has 31 days...
        $stale_log_file = $log_filename.'/'.$two_months_ago.'-sdg_dev.log';
    }
    if (file_exists($stale_log_file)) {
        $log_msg .= "\n>>>sdg_log<<< unlinked stale_log_file: $stale_log_file";
        unlink($stale_log_file);
    } else {
        //$log_msg .= "\n>>>sdg_log<<< No match found for stale_log_file: $stale_log_file"; // tft
    }

    // Syntax: file_put_contents(filename, data, mode, context)
    // (If filename does not exist, the file is created. Otherwise, the existing file is overwritten, unless the FILE_APPEND flag is set.)
    file_put_contents($log_file, $log_msg . "\n", FILE_APPEND);

}


/*********** POST BASICS ***********/

function sdg_post_title ( $args = array() ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = false;
    $fcn_id = "[sdg-pt]&nbsp;";
    sdg_log( "divline2", $do_log );

    // Init vars
    $info = "";
    $ts_info = "";

    $ts_info .= $fcn_id."<pre>args: ".print_r($args, true)."</pre>";

    // Defaults
    $defaults = array(
        'the_title'        => null, // optional override to set title via fcn arg, not via post
        'post'            => null,
        'line_breaks'    => false,
        'show_subtitle'    => false,
        'show_person_title' => false, // WIP
        'show_series_title' => false,
        'link'            => false,
        'echo'            => true,
        'hlevel'        => 1,
        'hlevel_sub'    => 2,
        'hclass'          => 'entry-title',
        'hclass_sub'      => 'subtitle',
        'before'          => '',
        'after'          => '',
        'called_by'        => null,
        'do_ts'            => devmode_active( array("sdg", "titles") ),
    );

    // Parse & Extract args
    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    $ts_info .= $fcn_id."<pre>args parsed/extracted: ".print_r($args, true)."</pre>";

    $hclass .= " sdgp";
    if ( is_numeric($post) ) {
        $post_id = $post;
        $post = get_post( $post_id );
    } else {
        //$ts_info .= "Not is_numeric: ".$post."<br />";
        $post_id = isset( $post->ID ) ? $post->ID : 0;
    }
    if ( $post ) {
        $ts_info .= $fcn_id."post_id: ".$post_id."<br />";
        //$ts_info .= "<pre>post: ".print_r($post, true)."</pre>";
        $post_type = $post->post_type;
    } else {
        $post_type = null;
    }

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
        // WIP: maybe not practical to build in all args for person title display in this function -- use get_person_display_name directly instead
        /*if ( $post_type == "person" ) {
            $title = get_person_display_name($post_id);
        } else {
            $title = $post->post_title;
        }*/
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
    $title = sdg_format_title($title, $line_breaks);

    // Prepend "before" text, if any
    $title = $before.$title;

    // If we're showing the subtitle, retrieve and format the relevant text
    $subtitle = ""; // init
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
    $series_title = "";
    $series_field = "";
    if ( is_singular('event') ) { $show_series_title = "wordy"; } // Force show_series_title for event records
    //
    if ( $show_series_title ) {    // && function_exists( 'is_dev_site' ) && is_dev_site()

        $ts_info .= $fcn_id."show_series_title: ".$show_series_title."<br />";
        $hclass .= " with-series-title";

        // Determine the series type
        if ( $post->post_type == "event" ) {
            $series_field = 'event_series'; //$series_field = 'series_events'; //$series_field = 'events_series';
        } else if ( $post->post_type == "sermon" ) {
            $series_field = 'sermons_series';
        }
        $ts_info .= $fcn_id."series_field: $series_field<br />";
        $series = get_post_meta( $post_id, $series_field, true );
        if (isset($series[0])) { $series_id = $series[0]; } else { $series_id = null; $info .= "<!-- series: ".print_r($series, true)." -->"; }
        // If a series_id has been found, then show the series title and subtitle
        if ( $series_id ) {
            $ts_info .= $fcn_id."series_id: $series_id<br />";
            $series_title = get_the_title( $series_id );
            $series_title = '<a href="'.esc_url( get_permalink($series_id) ).'" rel="bookmark">'.$series_title.'</a>';
            if ( $show_series_title == "prepend" ) {
                $series_title = $series_title."&nbsp;&mdash;&nbsp;";
            //} else if ( $show_series_title == "append" ) {
                //$series_title = "Series: ".$series_title."<br />";
            } else if ( $show_series_title == "wordy" || $show_series_title == "append" ) {
                if ( $post->post_type == "event" ) {
                    $series_title = 'Part of the event series '.$series_title;
                } else {
                    $series_title = "From the ".ucfirst($post->post_type)." Series &mdash; ".$series_title; // for sermons -- etc?
                }
            }
            if ( $hlevel_sub ) {
                $series_title = '<h'.$hlevel_sub.' class="'.$hclass_sub.'">'.$series_title.'</h'.$hlevel_sub.'>';
            } else if ( is_post_type_archive('event') || is_page('events') ) {
                $series_title = '<p class="series-title subtitle">'.$series_title.'</p>';
            } else {
                $series_title = '<span class="series-title subtitle">'.$series_title.'</span>';
            }
            //$series_title = '<a href="'.esc_url( get_permalink($series_id) ).'" rel="bookmark"><span class="series-title">'.get_the_title( $series_id ).'</span></a>';
            //$series_title = '<span class="series-title">'.get_the_title( $series_id ).'</span>';
            /*
            // Check to see if the series has a subtitle
            $series_subtitle = get_post_meta( $series_id, 'series_subtitle', true );
            //
            if ( empty( $series_subtitle ) ) {
                if ( $post->post_type == "event" ) {
                    $series_subtitle = 'Part of the event series '.$series_title;
                } else {
                    $series_subtitle = "From the ".ucfirst($post->post_type)." Series &mdash; ".get_the_title( $series_id ); // for sermons -- etc?
                }
            }
            if ( !empty( $series_subtitle ) ) {
                $series_subtitle = '<a href="'.esc_url( get_permalink($series_id) ).'" rel="bookmark" target="_blank">'.$series_subtitle.'</a>';
                $series_subtitle = '<h'.$hlevel_sub.' class="'.$hclass_sub.'">'.$series_subtitle.'</h'.$hlevel_sub.'>';
            }*/
        }

        // TODO: add hyperlink to the series page?
        //
    }

    // Hyperlink the title, if applicable
    if ( $link ) {
        $title = '<a href="'.esc_url( get_permalink($post_id) ).'" rel="bookmark">'.$title.'</a>';
    }

    // Format the title according to the parameters for heading level and class
    if ( $hlevel && $hlevel != 0 ) {
        $title = '<h'.$hlevel.' class="'.$hclass.'">'.$title.'</h'.$hlevel.'>'; // '<h1 class="entry-title">'
    }

    // Add the title, subtitle, and series_title to the info for return
    // WIP: streamline
    if ( $series_title && $show_series_title == 'prepend' ) {
        $info .= $series_title;
    }
    $info .= $title;
    $info .= $subtitle;
    if ( $series_title && $show_series_title != 'prepend' && $show_series_title !== false) {
        $info .= $series_title;
    }

    //$ts_info .= "END sdg_post_title<br />";

    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "basics" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }

    // Echo or return, as requested via $echo arg.
    if ( $echo ) {
        echo $info;
    } else {
        return $info;
    }

}

// TODO: generalize for non-title text
function sdg_format_title ( $str = null, $line_breaks = false ) {

    // Return if empty
    if ( empty($str) ) {
        return $str;
    }

    // Italicize info contained w/in double brackets
    if (! is_admin()) {
        //
        $find = array('//', '\\');
        $replace   = array('<span class="emtitle">', '</span>');
        $str = str_replace($find, $replace, $str);
        //
        $find = array('{', '}');
        $replace   = array('<span class="emtitle">', '</span>');
        $str = str_replace($find, $replace, $str);
        //
        $find = array('[[', ']]');
        //$find = array('<<', '>>');
        //$find = array("&ldquo;", "&rdquo;");
        $replace   = array('<span class="emtitle">', '</span>');
        $str = str_replace($find, $replace, $str);
    }

    // Remove legacy UID info (STC)
    if ( preg_match('/([0-9]+)_(.*)/', $str) ) {
        $str = preg_replace('/([0-9]+)_(.*)/', '$2', $str);
        $str = str_replace("_", " ", $str);
    }
    $str = remove_bracketed_info($str);

    // Check for pipe character and replace it with line breaks or spaces, depending on settings
    if ( $line_breaks ) {
        $str = str_replace("|", "<br />", $str);
    } else {
        $str = str_replace("|", " ", $str);
        $str = str_replace("  ", " ", $str); // Replace double space with single, in case extra space was left following pipe
    }

    return $str;

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
        'fields'    => 'ids',
    );

    $query = new WP_Query( $wp_args );
    $post_ids = $query->posts;
    //$info .= count($post_ids)." posts found and sorted<br />";

    $arr_info['post_ids'] = $post_ids;
    $arr_info['info'] = $info;

    return $arr_info;

}

/*********** POST RELATIONSHIPS ***********/

function display_postmeta( $args = array() ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "meta") );
    $do_log = false;
    $fcn_id = "[sdg-dpm] ";
    sdg_log( "divline2", $do_log );

    // Init vars
    $info = "";
    $ts_info = "";

    //$ts_info .= "<pre>display_postmeta args: ".print_r($args, true)."</pre>";

    // Defaults
    $defaults = array(
        'post_id'    => null,
        /*'format'    => "singular", // default to singular; other option is excerpt
        'img_size'    => "thumbnail",
        'sources'    => array("featured_image", "gallery"),
        'echo'        => true,
        'return_value'      => 'html',
        'do_ts'      => false,*/
    );

    // Parse & Extract args
    $args = wp_parse_args( $args, $defaults );
    extract( $args );
    $ts_info .= $fcn_id."display_postmeta parsed/extracted args: <pre>".print_r($args, true)."</pre>";

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

    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "sdg" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }

    return $info;

}

/*********** DATES/TIME/SCOPES ***********/

// Create custom scopes: "Upcoming", "This Week", "This Season", "Next Season", "This Year", "Next Year"
// Returns array of $dates with $dates['start'] and $dates['end'] in format 'Ymd' (or TBD: other $date_format)
// NB: these scope definitions can be used for any post type with date fields -- other than EM events, which are handled separately through events module and EM plugin
function sdg_scope_dates( $scope = null ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "events") );
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

    } else if ( $scope == 'today_onward' || $scope == 'today-onward' ){

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
    $dates['end']     = $end_date;

    return $dates;

}

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

/*** SEARCH FORM ETC WIP ***/

add_shortcode('sdg_search_form', 'sdg_search_form');
function sdg_search_form ( $atts = array(), $content = null, $tag = '' ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg") );
    $do_log = false;
    sdg_log( "divline2", $do_log );

    // Init vars
    $info = "";
    $ts_info = "";
    //$search_values = false; // var to track whether any search values have been submitted on which to base the search
    $search_values = array(); // var to track whether any search values have been submitted and to which post_types they apply

    $ts_info .= '_GET: <pre>'.print_r($_GET,true).'</pre>'; // tft
    //$ts_info .= '_REQUEST: <pre>'.print_r($_REQUEST,true).'</pre>'; // tft

    $args = shortcode_atts( array(
        'post_type'    => 'post',
        'form_type'    => 'simple_search',
        'fields'       => null,
        'limit'        => '-1'
    ), $atts );

    // Extract
    extract( $args );

    //$info .= "form_type: $form_type<br />"; // tft

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

    // WIP / TODO: fine-tune ordering -- 1) rep with editions, sorted by title_clean 2) rep without editions, sorted by title_clean
    /*
    'orderby'    => 'meta_value',
    'meta_key'     => '_event_start_date',
    'order'     => 'DESC',
    */

    //
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

    // Get related post type(s), if any
    if ( $post_type == "repertoire" ) {
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
        //$info .= print_r($arr_fields, true); // tft

        // e.g. http://stthomas.choirplanner.com/library/search.php?workQuery=Easter&composerQuery=Williams

        $info .= '<form class="sdg_search_form '.$form_type.'">';
        //$info .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" class="sdg_search_form '.$form_type.'">';

        // Get all ACF field groups associated with the primary post_type
        $field_groups = acf_get_field_groups( array( 'post_type' => $post_type ) );

        // Get all taxonomies associated with the primary post_type
        $taxonomies = get_object_taxonomies( $post_type );
        //$info .= "taxonomies for post_type '$post_type': <pre>".print_r($taxonomies,true)."</pre>"; // tft

        ///
        $search_operator = "and"; // init

        // Loop through the field names and create the actual form fields
        foreach ( $arr_fields as $arr_field ) {

            $field_info = ""; // init
            $field_name = $arr_field; // may be overrriden below
            $option_field_name = $field_name;
            $field_info .= "[1253] field_name: $field_name; arr_field: $arr_field<br />";
            $field_info .= "[1254] option_field_name: $option_field_name<br />";
            $alt_field_name = null; // for WIP fields/transition incomplete, e.g. repertoire_litdates replacing related_liturgical_dates

            // Fine tune the field name
            if ( $field_name == "title" ) {
                $placeholder = "title"; // for input field
                if ( $post_type == "repertoire" ) { // || $post_type == "edition"
                    $field_name = "title_clean"; // todo -- address problem that editions don't have this field
                    //$field_name = "post_title";
                } else {
                    $field_name = "post_title";
                    //$field_name = "s";
                }
                $field_info .= "[1265] field_name: $field_name; arr_field: $arr_field<br />";
            } else {
                $placeholder = $field_name; // for input field
            }

            if ( $form_type == "advanced_search" ) {
                $field_label = str_replace("_", " ",ucfirst($placeholder));
                if ( $field_label == "Repertoire category" ) {
                    $field_label = "Category";
                } else if ( $arr_field == "liturgical_date" || $field_name == "liturgical_date" || $field_label == "Related liturgical dates" ) {
                    $field_label = "Liturgical Dates";
                    $field_name = "repertoire_litdates";
                    $option_field_name = $field_name;
                    $alt_field_name = "related_liturgical_dates";
                }/* else if ( $field_name == "edition_publisher" ) {
                    $field_label = "Publisher";
                }*/
                $field_info .= "[1281] field_name: $field_name; arr_field: $arr_field<br />";
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
                $mq_component = null;
                $mq_alt_component = null;
                $mq_subquery = null;
                $tq_component = null;

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
                    $field_info .= "[1354] field_name: $field_name; arr_field: $arr_field<br />";

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
                        $field_info .= "[1376] field_name: $field_name; arr_field: $arr_field<br />";
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

                    //$field_info .= "field: <pre>".print_r($field,true)."</pre>";

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
                    $field_info .= "[1446] field_name: $field_name; arr_field: $arr_field<br />"; //$field_info .= "field_name: $field_name<br />";
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

                    if ( $field_type == "text" && !empty($field_value) && $field_name != "post_title" ) {

                        $match_value = $field_value;

                        // WIP: figure out how to ignore punctuation in meta_value -- e.g. veni, redemptor...
                        if ( $field_name == "title_clean" && strpos($match_value," ") ) { $match_value = str_replace(" ","XXX",$match_value); }

                        // TODO: figure out how to determine whether to match exact or not for particular fields
                        // -- e.g. box_num should be exact, but not necessarily for title_clean?
                        // For now, set it explicitly per field_name
                        if ( $field_name == "box_num" ) {
                            //$match_value = "'".$match_value."'";
                            //$match_value = '"'.$match_value.'"'; // matches exactly "123", not just 123. This prevents a match for "1234"
                        } else {
                            $match_value = "XXX".$match_value."XXX";
                        }

                        // If querying title_clean, then also query tune_name
                        if ( $field_name == "title_clean" ) {
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

                        if ( $post_type == "repertoire" ) {

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

            $rep_cat_exclusions = array('organ-works', 'piano-works', 'instrumental-music', 'instrumental-solo', 'orchestral', 'brass-music', 'psalms', 'hymns', 'noble-singers-repertoire', 'guest-ensemble-repertoire'); //, 'symphonic-works'
            $admin_tag_exclusions = array('exclude-from-search', 'external-repertoire');

            foreach ( $tq_components_primary AS $component ) {

                // Check to see if component relates to repertoire_category
                if ( $post_type == "repertoire" ) {

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
            if ( $post_type == "repertoire" && $rep_cat_queried == false ) {
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
                        //$ts_info .= "arr_post_ids: <pre>".print_r($arr_post_ids,true)."</pre>";

                        $ts_info .= $posts_info['ts_info'];

                        // Print last SQL query string
                        //global $wpdb;
                        //$ts_info .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>";

                    }
                }
            }

            if ( $search_related_post_type == true && $bgp_args_related && $default_query == false ) {

                $ts_info .= "About to pass bgp_args_related to birdhive_get_posts: <pre>".print_r($bgp_args_related,true)."</pre>";

                //$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $related_posts_info = array();
                $related_posts_info = birdhive_get_posts( $bgp_args_related );

                if ( isset($related_posts_info['arr_posts']) ) {

                    $arr_related_post_ids = $related_posts_info['arr_posts']->posts;
                    $ts_info .= "Num arr_related_post_ids: [".count($arr_related_post_ids)."]<br />";
                    //$ts_info .= "arr_related_post_ids: <pre>".print_r($arr_related_post_ids,true)."</pre>";

                    if ( isset($related_posts_info['info']) ) { $ts_info .= $related_posts_info['info']; }
                    if ( isset($related_posts_info['ts_info']) ) { $ts_info .= $related_posts_info['ts_info']; }

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
                $info .= '<div class="troubleshooting">'."Num matching posts found (raw results): [".count($arr_post_ids)."]".'</div>'; // if there are both rep and editions, it will likely be an overcount
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

    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }

    return $info;

}


// The following is a WIP modified version of the above sdg_search_form function (formerly found in the music module)
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
    'orderby'    => 'meta_value',
    'meta_key'     => '_event_start_date',
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
            $field_info .= "[".__LINE__."] field_name: $field_name; arr_field: $arr_field<br />";
            $field_info .= "[".__LINE__."] option_field_name: $option_field_name<br />";
            $alt_field_name = null; // for WIP fields/transition incomplete, e.g. repertoire_litdates replacing related_liturgical_dates

            // Fine tune the field name
            if ( $field_name == "title" ) {
                $placeholder = "title"; // for input field
                if ( $post_type == "repertoire" ) { // || $post_type == "edition" // WIP: applies only for mlib
                    $field_name = "title_clean"; // todo -- address problem that editions don't have this field
                } else {
                    $field_name = "post_title";
                }
                $field_info .= "[".__LINE__."] field_name: $field_name; arr_field: $arr_field<br />";
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
                $field_info .= "[".__LINE__."] field_name: $field_name; arr_field: $arr_field<br />";
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
                    $field_info .= "[".__LINE__."] field_name: $field_name; arr_field: $arr_field<br />";

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
                        $field_info .= "[".__LINE__."] field_name: $field_name; arr_field: $arr_field<br />";
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
                    $field_info .= "[".__LINE__."] field_name: $field_name; arr_field: $arr_field<br />"; //$field_info .= "field_name: $field_name<br />";
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
                        //$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $posts_info = array();
                        $posts_info = birdhive_get_posts( $bgp_args );
                    } else {
                        $posts_info = birdhive_get_posts( $bgp_args );
                    }

                    if ( isset($posts_info['arr_posts']) ) {

                        $arr_post_ids = $posts_info['arr_posts']->posts; // Retrieves an array of IDs (based on return_fields: 'ids')
                        $ts_info .= "Num arr_post_ids: [".count($arr_post_ids)."]<br />";
                        //$ts_info .= "arr_post_ids: <pre>".print_r($arr_post_ids,true)."</pre>";

                        $ts_info .= $posts_info['ts_info'];

                        // Print last SQL query string
                        //global $wpdb;
                        //$ts_info .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>";

                    }
                }
            }

            if ( $search_related_post_type == true && $bgp_args_related && $default_query == false ) {

                $ts_info .= "About to pass bgp_args_related to birdhive_get_posts: <pre>".print_r($bgp_args_related,true)."</pre>";

                //$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $related_posts_info = array(); // tft
                $related_posts_info = birdhive_get_posts( $bgp_args_related );

                if ( isset($related_posts_info['arr_posts']) ) {

                    $arr_related_post_ids = $related_posts_info['arr_posts']->posts;
                    $ts_info .= "Num arr_related_post_ids: [".count($arr_related_post_ids)."]<br />";
                    //$ts_info .= "arr_related_post_ids: <pre>".print_r($arr_related_post_ids,true)."</pre>";

                    if ( isset($related_posts_info['info']) ) { $ts_info .= $related_posts_info['info']; }
                    if ( isset($related_posts_info['ts_info']) ) { $ts_info .= $related_posts_info['ts_info']; }

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
                            //
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

    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }

    return $info;

}

/**
* Ninja Forms - Required Field Text Broken Code Fix
* @package Ninja Forms
* @author Faisal Ahammad
* // SEE https://gist.github.com/faisalahammad/599771146f05817d1901af811f24b859
*/

/**
 * @param  $settings
 * @param  $form_id
 * @return mixed
 */
function decode_ninja_forms_display_form_settings( $settings, $form_id ) {
    $settings[ 'fieldsMarkedRequired' ] = html_entity_decode( $settings[ 'fieldsMarkedRequired' ] );
    return $settings;
}
add_filter( 'ninja_forms_display_form_settings', 'decode_ninja_forms_display_form_settings', 10, 2 );

// Alternative Code //

/**
 * @param  $strings
 * @return mixed
 */
 /*
function fix_ninja_forms_i18n_front_end( $strings ) {
    $strings[ 'fieldsMarkedRequired' ] = 'Fields marked with an <span class="ninja-forms-req-symbol">*</span> are required';
    return $strings;
}
add_filter( 'ninja_forms_i18n_front_end', 'fix_ninja_forms_i18n_front_end' );
*/


?>
