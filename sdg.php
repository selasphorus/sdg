<?php
/**
 * @package SDG
 */

/**
 * Plugin Name: SDG-OG (main)
 * Plugin URI:
 * Description: Custom post types, taxonomies and functions for music and more
 * Dependencies:	  
 * Requires Plugins:  whx4-core, stc
 * Version: 1.260818
 * Author: atc
 * Author URI:
 * License:
 * Text Domain: sdg
*/

// NB: this is a transitional version of the plugins, with all methods removed that don't pertain to the lectionary or worship
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// Define our handy constants.
$plugin_path = plugin_dir_path( __FILE__ );

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

/* +~+~+ ACF +~+~+ */

// Load ACF field groups hard-coded as PHP
require 'includes/acf-field-groups.php';

// TBC: preserve some version of option in OOP version to choose whether or not to use custom capabilities

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */
$includes = array( 'posttypes', 'taxonomies' );
foreach ( $includes as $inc ) {
    $filepath = $plugin_path . 'includes/'.$inc.'.php';
    if ( file_exists($filepath) ) { include_once( $filepath ); } //else { echo "inc file $filepath not found"; }
}

$modules = [ 'lectionary', 'sermons' ];
foreach ( $modules as $module ) {
    $filepath = $plugin_path . 'modules/'.$module.'.php';
    if ( file_exists($filepath) ) { include_once( $filepath ); }
}

/**
 * Temporarily exposes SDG's procedurally-registered post types to WXC's
 * TaxonomyRegistrar, until the OOP SDG rewrite registers them natively.
 *
 * @param array $postTypes Active post type slugs already contributed.
 * @return array
 */
function sdg_contribute_active_post_types(array $postTypes): array
{
    return array_merge($postTypes, [
        'bible_book', 'verse', 'reading', 'lectionary', 'liturgical_date', 'liturgical_date_calc', 'collect', 'psalms_of_the_day', 
        'sermon', 'sermon_series',
    ]);
}

if ( function_exists('wxc_log') ) { wxc_log('SDG about to register wxc_active_post_types filter', null, 'sdg'); }
error_log('SDG about to register wxc_active_post_types filter');
add_filter('wxc_active_post_types', 'sdg_contribute_active_post_types');

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

/**
 * Enqueue scripts and styles
 */
add_action( 'wp_enqueue_scripts', 'sdg_scripts_method' );
function sdg_scripts_method()
{
    global $current_user;
    $current_user = wp_get_current_user();

    $fpath = WP_PLUGIN_DIR . '/sdg/sdg.css';
    if (file_exists($fpath)) { $ver = filemtime($fpath); } else { $ver = "240823"; }
    wp_enqueue_style( 'sdg-style', plugins_url( 'sdg.css', __FILE__ ), $ver );
}

/* *** SERMONS *** */

// Sermon updates - add related_event info
// TBD: is this now entirely obsolete?
add_shortcode( 'run_sermon_updates_fcn', 'sermon_updates' );
function sermon_updates ( $atts = array() )
{
	$info = "";

	$args = shortcode_atts( array(
        'legacy' => false,
        'testing' => true,
        //'id' => null,
        //'name' => null,
        'num_posts' => 10,
        //'header' => 'false',
    ), $atts );

    // Extract
	extract( $args );

    $info = ""; // init

    $wp_args = array(
        'post_type' => 'sermon',
        'post_status' => 'publish',
        'posts_per_page' => $num_posts,
        'orderby' => 'ID',
        'order'   => 'ASC'
    );

    if ( $legacy == 'true' ) {
        // Legacy Events
        $wp_args['meta_query'] =
            array(
                'relation' => 'AND',
                array(
                    'key'	  => "legacy_event_id",
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'	  => "related_event",
                    'compare' => 'NOT EXISTS',
                )
            );
    } else {
        // NON-Legacy Events
        $wp_args['meta_query'] =
            array(
                'relation' => 'AND',
                array(
                    'key'	  => "legacy_event_id",
                    'compare' => 'NOT EXISTS', // TODO: figure out how to check if NOT EXISTS *OR* is empty or zero...
                ),
                array(
                    'key'	  => "related_event",
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'	  => "sermon_date",
                    'compare' => 'EXISTS',
                )
            );
    }

	$arr_posts = new WP_Query( $wp_args );
    $sermon_posts = $arr_posts->posts;

    $info .= "wp_args: <pre>".print_r( $wp_args, true )."</pre>";
    //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre><br />"; // tft
    //$info .= "arr_posts->posts: <pre>".print_r( $arr_posts->posts, true )."</pre>";
    $info .= "[num sermon_posts: ".count($sermon_posts)."]<br /><br />";

    foreach ( $sermon_posts AS $sermon_post ) {
        setup_postdata( $sermon_post );

        $sermon_post_id = $sermon_post->ID;
        $sermon_title = get_the_title( $sermon_post_id );
        //$info .= "sermon_post_id: $sermon_post_id // ";
        $info .= make_link( get_the_permalink( $sermon_post_id ), '<em>'.$sermon_title.'</em>', $sermon_title );
        $info .= '&nbsp;[id:'.$sermon_post_id.'] // ';

        if ( $legacy == 'true' ) {
            // Legacy Events
            // ADD related_event record by retrieving event post ID based on legacy_event_id

            $legacy_event_id = get_field( 'legacy_event_id', $sermon_post_id );

            if ($legacy_event_id) {

                $info .= "legacy_event_id: $legacy_event_id<br />";

                if ( $legacy_event_id != "" ) {

                    //get event based on legacy_id stored in ACF group 'Event Legacy Fields'
                    $event_posts = get_posts(array(
                        //'numberposts'	=> -1,
                        'post_type'		=> 'event',
                        'meta_key'		=> 'legacy_id',
                        'meta_value'	=> $legacy_event_id
                    ));

                }
            }
        } else {
            // NON-Legacy Events
            // ADD related_event record by retrieving event post ID based on sermon_date

            $sermon_date = get_field( 'sermon_date', $sermon_post_id );

            if ($sermon_date) {
                $info .= "sermon_date: $sermon_date<br />";
                if ( $sermon_date != "" ) {
                    // Get event(s) based on sermon_date -- in Worship Services events-category only
                    $wp_args2 = array(
                        'post_type'     => 'event',
                        'post_status'   => 'publish',
                        'posts_per_page' => 10,
                        'meta_query'	=> array(
                            //'relation' => 'OR',
                            array(
                                'key'	=> "_event_start_local",
                                'value' => $sermon_date,
                            ),
                            /*array(
                                'key'	=> "_event_start",
                                'value' => $sermon_date,
                            )*/
                        ),
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'event-categories',
                                'field'    => 'slug',
                                'terms'    => 'worship-services',
                            )
                        ),
                    );

                    $arr_posts2 = new WP_Query( $wp_args2 );
                    $event_posts = $arr_posts2->posts;
                    //$info .= "wp_args2: <pre>".print_r( $wp_args2, true )."</pre>"; // tft
                    //$info .= "Last SQL-Query: <pre>{$arr_posts2->request}</pre><br />"; // tft
                }
            } else {
                $info .= '<span class="warning">No sermon_date.</span><br />';
            }
        }

        if ( $event_posts ) {
            if ( count($event_posts) > 1 ) {
               $info .= '<span class="warning">Found >1 event_posts!: '.count($event_posts).' posts</span><br />';
            }/* else if ( count($event_posts) < 1 ) {
               $info .= '<span class="warning">No related event_posts found.</span><br />';
            }*/

            foreach( $event_posts as $event_post ) {
                setup_postdata( $event_post );
                $event_id = $event_post->ID;
                $event_title = get_the_title( $event_id );

                //$info .= "&nbsp&nbsp&nbsp&nbsp[".$event_id."] ".$event_post->post_title."<br />";
                $info .= make_link( get_the_permalink( $event_id ), '&nbsp&nbsp&nbsp&nbsp'.$event_title, $event_title );
                $info .= '&nbsp;[id:'.$event_id.']<br />';

                // Add postmeta for the sermon with this info
                if ( $event_id && $testing == 'false' ) {
                    add_post_meta( $sermon_post_id, 'related_event', $event_id, true );
                } else {
                    $info .= "test mode: add_post_meta is disabled.<br />";
                }
            }
        } else {
            if ( $legacy == 'true' ) {
                $info .= "No live events matching legacy_event_id $legacy_event_id.<br />";
            } else {
                $info .= "No matching events for sermon_date '$sermon_date'.<br />";
            }
        }

        $info .= "<br />";
    }

    return $info;
}
