<?php
/**
 * Plugin Name:       SDG (OOP)
 * Description:       A WordPress plugin for godly matters
 * Dependencies:      Requires WHx4-Core, WHx4
 * Requires Plugins:  whx4-core, whx4
 * Version:           2.0
 * Author:            atc
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sdg
 *
 * @package           sdg
 */

declare(strict_types=1);

namespace atc\SDG;

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

// v1 designed using ACF PRO Blocks, Post Types, Options Pages, Taxonomies and more.
// v2 OOP version WIP

// Require Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use atc\WXC\Plugin;

// WXC Add-on Modules
use atc\SDG\Modules\Worship\WorshipModule as Worship; // To include Clergy, Sermons, ?....
use atc\SDG\Modules\Lectionary\LectionaryModule as Lectionary; // WIP
// Other modules TBD
// Old version modules: lectionary, newsletters, sermons, snippets, webcasts, // ninjaforms, slider, ensembles
// TODO: move snippets, newletters to separate add-on mini-plugins? Move webcasts to WHx4 as part of Events

// Once plugins are loaded, boot everything up
add_action('wxc_pre_boot', function() {
    // Wait until WXC is loaded, but BEFORE it boots
    if (!class_exists(Plugin::class)) {
        return;
    }

    // Register the modules with WXC
    add_filter('wxc_register_modules', function(array $modules): array {
        $modules['worship'] = Worship::class;
        $modules['lectionary'] = Lectionary::class;
		return $modules;
	});
	
	// Register Field Keys
    add_filter('wxc_registered_field_keys', function() {
        if (!function_exists('acf_get_local_fields')) {
            return [];
        }

        $fields = acf_get_local_fields();
        $keys = [];

        foreach ($fields as $field) {
            if (isset($field['key'])) {
                $keys[] = $field['key'];
            }
        }

        return $keys;
    });
    
    // Register Assets
    add_filter('wxc_assets', static function (array $assets): array {
        // CSS
        $relCss = 'assets/css/sdg.css';
        $srcCss = plugins_url($relCss, __FILE__);
        $pathCss = plugin_dir_path(__FILE__) . $relCss;
    
        $assets['styles'][] = [
            'handle'   => 'sdg',
            'src'      => $srcCss,
            'path'     => $pathCss,
            'deps'     => [],
            'ver'      => 'auto',
            'media'    => 'all',
            'where'    => 'front',
            'autoload' => true,
        ];
    
        // JS
        /*$relJs = 'assets/js/sdg.js';
        $srcJs = plugins_url($relJs, __FILE__);
        $pathJs = plugin_dir_path(__FILE__) . $relJs;
    
        $assets['scripts'][] = [
            'handle'    => 'sdg',
            'src'       => $srcJs,
            'path'      => $pathJs,
            'deps'      => [],        // e.g., ['jquery']
            'ver'       => 'auto',
            'in_footer' => true,
            'where'     => 'front',
            'autoload'  => true,
        ];*/
    
        return $assets;
    });
    
}, 15); // Priority < 20 to run before WXC boot()

// WIP

/* ************************************ admin_functions *************************************************************** */

/**
 * Explode list using "," and ", ".
 *
 * @param string $string String to split up.
 * @return array Array of string parts.
 */
function birdhive_att_explode ( $string = '' ) 
{
	$string = str_replace( ', ', ',', $string );
	return explode( ',', $string );
}


/*** POST TITLE/SLUG FUNCTIONS ***/

/*function remove_accents ( $str ) {

    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');

    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'ss', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');

    return str_replace($a, $b, $str);

}*/

function super_sanitize_title ( $str = null ) {

    if ( $str === null ) { return null; }

    $str = strtolower($str); // Make it lowercase
    $str = str_replace('ß', 'ss', $str); // Do this before remove_accents, because that WP fcn replaces ß with a single 's'
    $str = remove_accents($str); // Remove accents &c.

    $str = str_replace(' & ', '_and_', $str);
    $str = str_replace(' – ', '_', $str);

    $str = str_replace('&#8211;', '_', $str); // &#8211; is Unicode for dash
    $str = str_replace('&#8212;', '_', $str); // &#8211; is Unicode for em-dash

    $str = strip_tags($str); // Remove HTML formatting

    // Remove unnecessary formatting and punctuation
    //
    //$patterns = array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/');
    //$replacements = array('', '-', '');
    //
    $patterns = array();
    $replacements = array();

    $patterns[1] = '/[^\w]&amp;[^\w]/';
    $replacements[1] = '_and_';

    $patterns[2] = '/&lsquo;/';
    $replacements[2] = '';

    $patterns[3] = '/&rsquo/';
    $replacements[3] = '';

    $patterns[4] = '/[^\w]&ndash;[^\w]/'; // en-dash -- with surrounding spaces
    $replacements[4] = '_';

    $patterns[5] = '/[^\w]&mdash;[^\w]/'; // em-dash -- with surrounding spaces
    $replacements[5] = '_';

    $patterns[6] = '/&ndash;/'; // en-dash -- no spaces
    $replacements[6] = '_';

    $patterns[7] = '/&mdash;/'; // em-dash -- no spaces
    $replacements[7] = '_';

    //$patterns[8] = '/[^\w]&[^\w]/'; // ampersand surrounded by spaces
    //$replacements[8] = '_and_';

    $patterns[9] = '/[^\w]\+[^\w]/'; // ampersand surrounded by spaces
    $replacements[9] = '_and_';

    $str = preg_replace($patterns, $replacements, $str);

    ///

    // Punctuation
    $str = str_replace("'", '', $str); // single straight quote
    $str = str_replace('‘', '', $str); // single left curly quote
    $str = str_replace('’', '', $str); // single right curly quote

    $str = str_replace(' (', '_', $str); // open parens preceded by a space
    $str = str_replace(') ', '_', $str); // close parens followed by a space
    $str = str_replace('(', '', $str); // open parens
    $str = str_replace(')', '', $str); // close parens
    $str = str_replace(' [', '_', $str); // square brackets
    $str = str_replace('] ', '_', $str);
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);

    $str = str_replace('. ', '_', $str); // period + space
    $str = str_replace('.', '_', $str); // period
    $str = str_replace(', ', '_', $str); // comma + space
    $str = str_replace(',', '_', $str); // comma
    $str = str_replace('…', '', $str); // elipses
    $str = str_replace(': ', '_', $str); // colon + space
    $str = str_replace(':', '_', $str); // colon
    $str = str_replace('; ', '_', $str); // semicolon + space
    $str = str_replace(';', '_', $str); // semicolon
    $str = str_replace(' | ', '_', $str); // pipe char with spaces
    $str = str_replace('|', '_', $str); // pipe char with spaces
    // -
    $str = str_replace(' - ', '_', $str); // spaced single hyphen
    $str = str_replace(' -- ', '_', $str); // spaced double hyphen
    $str = str_replace('--', '_', $str); // un-spaced double hyphen

    $str = str_replace('  ', ' ', $str); // double space -- replace with single
    $str = str_replace(' ', '_', $str); // single space -- replace with underscore
    // TODO: look for other types of whitespace, via regex \s\w -- ??

    $str = str_replace('/', '_', $str); // forward slash
    $str = str_replace('!', '', $str);
    $str = str_replace('?', '', $str);

    // Clean up multiple underscores
    $str = str_replace('____', '_', $str); // quadruple underscore
    $str = str_replace('___', '_', $str); // triple underscore
    $str = str_replace('__', '_', $str); // double underscore
    // Again...
    $str = str_replace('__', '_', $str); // double underscore
    // Again...
    //$str = str_replace('__', '_', $str); // double underscore

    $str = sanitize_title($str);

    //$str = ucwords($str, "_");
    // Trim off trailing underscore
    if ( substr($str, -1) == '_' ) {
    //if ( substr($str, 0, -1) == '_' ) {
        $str = substr($str, 0, -1);
    }
    return $str;

}

// WIP to replace pods_sanitize
function sanitize ( $str = null ) {
    return $str;
}

// WIP-NOW
// Build title_for_matching UID based on... ???
function get_title_uid ( $post_id = null, $post_type = null, $post_title = null, $uid_field = 'title_for_matching' ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: get_title_uid", $do_log );

    sdg_log( "[gtu] post_id: ".$post_id."; post_type: ".$post_type, $do_log );
    sdg_log( "[gtu] post_title: ".$post_title, $do_log );
    sdg_log( "[gtu] uid_field: ".$uid_field, $do_log );

    if ( empty($post_id) && empty($post_title) ) { return false; }

    $new_t4m = ""; // init

    if ( !$post_title ) { $post_title = get_the_title( $post_id ); }
    if ( !$post_type ) { $post_type = get_post_type( $post_id ); }

    // Abort if error
    if ( strpos($post_title, 'Error! Abort!') !== false ) { return false; } // TODO: test!

    $old_t4m = get_post_meta( $post_id, $uid_field, true ); //get_post_meta( $post_id, 'title_for_matching', true );
    $new_t4m = super_sanitize_title ( $post_title );

    sdg_log( "[gtu] old_t4m: ".$old_t4m, $do_log );

    // Check to see if new_t4m is in fact unique to this post
    $t4m_posts = meta_value_exists( $post_type, $post_id, $uid_field, $new_t4m ); // meta_value_exists( $post_type, $post_id, 'title_for_matching', $new_t4m );
    if ( $t4m_posts && $t4m_posts > 0 ) { // meta_value_exists( $post_type, $meta_key, $meta_value )

        // not unique! fix it...
        sdg_log( "[gtu] new_t4m not unique! Fix it.", $do_log);

        if ( $old_t4m != $new_t4m ) {
            $i = $t4m_posts+1;
        } else {
            $i = $t4m_posts;
        }
        $new_t4m = $new_t4m."-v".$i;

    }
    if ( $new_t4m == $old_t4m ) {
        sdg_log( "[gtu] new_t4m same as old_t4m.", $do_log );
    } else {
        sdg_log( "[gtu] new_t4m: ".$new_t4m, $do_log );
    }

    return $new_t4m;
}

function update_title_for_matching ( $post_id ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function: sdg_update_title_for_matching", $do_log );

    if ( empty($post_id) ) { return false; }

    // Init vars
    $post = get_post( $post_id );
    $post_title = $post->post_title;
    $post_type = $post->post_type;
    //
    $new_t4m = "";
    $ts_info = "";
    $revised = false;

    $t4m = get_post_meta( $post_id, 'title_for_matching', true ); // get_post_meta( int $post_id, string $key = '', bool $single = false ) -- Return val will be an array if $single is false. Will be value of the meta field if $single is true.
    $title_clean = get_post_meta( $post_id, 'title_clean', true );

    if ( ! $title_clean || $title_clean == "" ) {
        sdg_log( "No title_clean stored in post_meta for this post -> use make_clean_title", $do_log );
        $title_clean = make_clean_title( $post_id );
    }
    $ts_info .= "<!-- post_title: $post_title // title_clean: $title_clean // t4m: $t4m -->";

    if ( !empty($t4m) ) {

        $ts_info .= "<!-- t4m already in DB: $t4m -->";
        // TODO: clean up existing T4Ms -- get rid of all spaces and other punctuation
        if (strpos($t4m, ' ') === false) {
            // t4m seems ok... but what if other fields have been updated -- composer or whatever...
            // TODO: figure out how/when to force updates...
            return true;
        }
        // t4m contains spaces and must be updated
    }

    // Build a new clean t4m

    $new_t4m = ""; // init
    $ts_info .= "<!-- t4m is empty -> build one -->"; //$info .= '<span class="label">title_for_matching is empty >> build one</span><br />';

    $sanitized_title = super_sanitize_title($title_clean);

    if ( $post_type == 'event' ) {

        $legacy_event_id = get_post_meta( $post_id, 'legacy_event_id', true );
        //$ts_info .= $indent.'<span class="label">legacy_event_id: </span>'.$legacy_event_id.'<br />';

        if ( !$legacy_event_id || $legacy_event_id == "" || $legacy_event_id == "0" ) {
            // Check to see if the legacy_event_id is contained in the existing slug, even if it somehow got erased from post_meta
            $new_legacy_event_id = substr( $slug, 0, strpos($slug, "_") ); //(int)
            if ( $new_legacy_event_id && $new_legacy_event_id != "" ) {
                $info .= "<!-- Extracted legacy_event_id from old slug: $new_legacy_event_id. -->";
                if ( $legacy_event_id == "" || $legacy_event_id == "0" ) {
                    update_post_meta( $post_id, 'legacy_event_id', $new_legacy_event_id );
                } else  {
                    add_post_meta( $post_id, 'legacy_event_id', $new_legacy_event_id, true );
                }
                $legacy_event_id = $new_legacy_event_id;
            }
        }

        if ( $legacy_event_id ) {
            //$info .= $indent.'<span class="label">legacy_event_id: </span>'.$legacy_event_id.'<br />';
            $new_t4m = $legacy_event_id."_".$sanitized_title;
        } else {
            //$info .= $indent.'No legacy_event_id<br />';
            $date_str = get_post_meta( $post_id, '_event_start_date', true );
            $new_t4m = $sanitized_title."-".$date_str;
        }

    } else if ( $post_type == 'repertoire' ) {

        $new_title = build_the_title( $post_id, 'title_for_matching', null );

        // Abort if error
        if ( strpos($new_title, 'Error! Abort!') !== false ) {
            //return null; // ???
        } else {
            $new_t4m = super_sanitize_title ( $new_title );
            // TODO-IMPORTANT: check to see if new_t4m is in fact unique
        }

    } else {

        //$new_t4m = "test";

    } // END post type variations

    // If revisions have been made, then update the postmeta
    //if ( $revised === true ) {
    if ( !empty($new_t4m) ) {

        // update_post_meta: If the meta field for the post does not exist, it will be added and its ID returned. Returns false if error or no change.
        if ( update_post_meta( $post_id, 'title_for_matching', $new_t4m ) ) {
            $ts_info .= sdg_add_post_term( $post_id, 't4m-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
        }

    }

    if ( $title_clean != $post_title ) {
        // Now that the title_for_matching is confirmed/updated: if the post_title isn't the clean one, update the post_title -- ???
    }

    $arr_info['title_for_matching'] = $new_t4m;
    //$arr_info['title_clean'] = $new_title_clean;

    if ( $do_ts ) { $arr_info['ts_info'] = $ts_info; } else { $arr_info['ts_info'] = null; }

    return $arr_info;
    // TODO: change to return true/false depending on outcome of add/update?

}

// Build proper title for rep/edition records, based on metadata components
// TODO:
// 1) separate out the title building bit so that it can be used independently of wp_insert_post_data
// 2) bind this with a t4m update function
// 3) create separate build_the_title functions per CPT

function build_the_title( $post_id = null, $uid_field = 'title_for_matching', $arr = array(), $abbr = false ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = devmode_active( array("sdg", "titles") );
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: build_the_title", $do_log );

    if ( $post_id == null ) { return false; }

    // Init vars
    $ts_info = "";
    $new_title = "";
    $authorship_arr = array();

    sdg_log( "[btt] post_id: ".$post_id, $do_log );
    sdg_log( "[btt] abbr: ".(int)$abbr, $do_log );
    $post_type = get_post_type( $post_id );

    // Get current (old) post_title and t4m (or uid?)
    $old_title = get_post_field( 'post_title', $post_id, 'raw' ); //get_the_title($post_id);
    $old_t4m = get_post_meta( $post_id, $uid_field, true ); //get_post_meta( $post_id, 'title_for_matching', true );

    // Check for CPT-specific build_the_title function
    // WIP
    $function_name = "build_".$post_type."_title";
    if ( function_exists($function_name) ) {

    }
    //build_POSTTYPE_title
    //build_repertoire_title
    //build_edition_title

    // If this is a repertoire record, check for a title_clean value
    // If there is no title_clean, abort -- there's a problem! -- except for Hymns (etc?) -- WIP! 240513
    if ( $post_type == 'repertoire' ) {
    	if ( isset($arr['title_clean']) ) { $title_clean = $arr['title_clean']; } else { $title_clean = get_field('title_clean', $post_id); }
    	if ( empty($title_clean) ) {
    		sdg_log( "[btt] Problem! title_clean is empty for repertoire record ID: ".$post_id, $do_log );
    		//return null;
    	}
    }

    // Set var values
    if ( !empty($arr) ) {

        sdg_log( "[btt] running btt using array derived from _POST.", $do_log );

        if ( $post_type == 'repertoire' ) {

            $title_clean = $arr['title_clean'];
            $excerpted_from_id = $arr['excerpted_from'];
            $excerpted_from = get_post_meta( $excerpted_from_id, 'title_clean', true );
            if ( empty($excerpted_from) && !empty($excerpted_from_id) ) {
                $excerpted_from = get_post_field( 'post_title', $excerpted_from_id, 'raw' );
            }
            $excerpted_from_txt = $arr['excerpted_from_txt'];
            if ( empty($excerpted_from) ) { $excerpted_from = $excerpted_from_txt; }
            $catalog_num = $arr['catalog_number'];
            $opus_num = $arr['opus_number'];

            // Authorship
            $authorship_arr['rep_title'] = $title_clean; //$authorship_arr['rep_title'] = $arr['title_clean'];
            $authorship_arr['composers'] = $arr['composers'];
            $authorship_arr['arrangers'] = $arr['arrangers'];
            $authorship_arr['transcribers'] = $arr['transcribers'];
            $authorship_arr['anon_info'] = $arr['anon_info'];
            //$authorship_arr['rep_categories'] = $arr['rep_categories'];

            $authorship_args = array( 'data' => $authorship_arr, 'format' => 'post_title', 'abbr' => $abbr ); //, 'is_single_work' => false, 'show_title' => false, 'links' => false
            $arr_authorship_info = get_authorship_info ( $authorship_args );
            $authorship_info = $arr_authorship_info['info'];
            $ts_info .= $arr_authorship_info['ts_info'];

            //$key_ids = $arr['keys']; // array of ids
            $first_line = $arr['first_line'];
            $tune_name = $arr['tune_name'];
            $psalm_num = $arr['psalm_num'];
            $year_completed = $arr['year_completed'];
            $old_t4m = $arr['t4m'];
            $rep_categories = $arr['rep_categories'];

        } else if ( $post_type == 'edition' ) {

            if ( $arr['repertoire_editions'] ) {
                $musical_works = $arr['repertoire_editions'];
            } else if ( $arr['musical_work'] ) {
                $musical_works = $arr['musical_work'];
            }
            if ( is_array($musical_works) && count($musical_works) == 1 ) {
                $musical_work_id = $musical_works[0];
                sdg_log( "[btt/edition] single musical_work_id found: ".$musical_work_id, $do_log );
            } else {
                // WIP -- TBD: how to deal w/ case of multiple works associated with one edition
                sdg_log( "[btt/edition] multiple musical_works found: ".print_r($musical_works, true), $do_log );
                foreach ( $musical_works AS $musical_work_id ) {
                    //$musical_work_id =
                }
            }

            // TODO: turn this array into a string!
            $choir_forces = $arr['choir_forces'];
            if ( is_array($choir_forces) ) {
                sdg_log( "[btt/edition] no voicings or soloists info >> use choir_forces_str: ".print_r($choir_forces, true), $do_log );
                $choir_forces_str = implode(", ",$choir_forces);
                $choir_forces_str = ucwords(str_replace("_"," ",$choir_forces_str));
                $choir_forces_str = str_replace("Men-s","Men's",$choir_forces_str);
                $choir_forces_str = str_replace("Mens","Men's",$choir_forces_str);
            } else {
                $choir_forces_str = "";
            }
            //
            $editors = $arr['editor']; // array of ids
            //sdg_log( "editors: ".print_r($editors, true), $do_log );
            $persons_args = array( 'arr_persons' => $editors, 'person_category' => 'editors', 'post_id' => $post_id, 'format' => $format, 'arr_of' => $arr_of, 'abbr' => $abbr, 'links' => false );
            $arr_editors_str = str_from_persons_array ( $persons_args );
            $editors_str = $arr_editors_str['info'];
            $ts_editors = $arr_editors_str['ts_info'];
            $ts_info .= $ts_editors;
            //sdg_log( "editors_str: ".$editors_str, $do_log );
            sdg_log( "-----", $do_log );

            $publication_id = $arr['publication']; // single id
            //sdg_log( "[btt/arr] publication_id: ".$publication_id, $do_log );
            if ( $publication_id ) {
                $publication = "[pub]".get_post_field( 'post_title', $publication_id, 'raw' );
            } else {
                $publication = "";
            }
            //
            $publisher_id = $arr['publisher']; // single id
            //sdg_log( "[btt/arr] publisher_id: ".$publisher_id, $do_log );
            if ( is_array($publisher_id) ) {
                $publisher = "publisher-array"; // tft
            } elseif ( $publisher_id ) {
                $publisher = get_post_field( 'post_title', $publisher_id, 'raw' );
            } else {
                $publisher = "";
            }
            //
            $publication_date = $arr['publication_date'];
            $box_num = $arr['box_num'];

            // Taxonomies
            $voicings        = $arr['voicings']; // array of ids
            $soloists        = $arr['soloists']; // array of ids
            $instruments     = $arr['instruments']; // array of ids

            $voicings_str = get_arr_str($voicings, "terms");
            if ( $voicings_str == "" ) {
                //sdg_log( "[btt/arr] voicings_str is empty.", $do_log );
                if ( $arr['voicing_txt'] != "" ) {
                    $voicings_str = $arr['voicing_txt'];
                    sdg_log( "[btt/arr] using backup txt field for voicings_str", $do_log );
                }
            } else {
                //sdg_log( "[btt/arr] voicings_str: ".$voicings_str, $do_log );
            }

            $soloists_str = get_arr_str($soloists, "terms");
            if ( $soloists_str == "" && $arr['soloists_txt'] != "" ) {
                $soloists_str = $arr['soloists_txt'];
                sdg_log( "[btt/arr] using backup txt field for soloists_str", $do_log );
            }

            $instruments_str = get_arr_str($instruments, "terms");
            if ( $instruments_str == "" && $arr['instrumentation_txt'] != "" ) {
                $instruments_str = $arr['instrumentation_txt'];
                sdg_log( "[btt/arr] using backup txt field for instruments_str: ".$instruments_str, $do_log );
            }


        }

        // For both rep & editions, handle key names
        $keys = $arr['keys']; // array of ids
        $keys_str = get_arr_str($keys, "terms");
        if ( $keys_str == "" && $arr['key_name_txt'] != "" ) {
            $keys_str = $arr['key_name_txt'];
            sdg_log( "[btt/arr] using backup txt field for keys_str", $do_log );
        }

    } else if ( $post_id ) {

        // If no array of data was submitted, get info via the post_id

        sdg_log( "[btt] running btt based on post_id.", $do_log );

        $authorship_arr['post_id'] = $post_id;

        if ( $post_type == 'repertoire' ) {

            // Get info via ACF get_field fcn rather than get_post_meta -- because the latter may return: Array( [0] => )

            $title_clean = get_field('title_clean', $post_id);
            $arr_excerpted_from = get_excerpted_from( $post_id );
    		$excerpted_from = $arr_excerpted_from['info'];
    		$ts_info .= $arr_excerpted_from['ts_info'];
            //$excerpted_from = get_field('excerpted_from', $post_id);
            $catalog_num = get_field('catalog_number', $post_id);
            $opus_num = get_field('opus_number', $post_id);

            // composer(s), arranger(s), transcriber:
            $authorship_args = array( 'data' => $authorship_arr, 'format' => 'post_title', 'abbr' => $abbr ); //, 'is_single_work' => false, 'show_title' => false, 'links' => false
            $arr_authorship_info = get_authorship_info ( $authorship_args );
            $authorship_info = $arr_authorship_info['info'];
            $ts_info .= $arr_authorship_info['ts_info'];

            $first_line = get_field('first_line', $post_id);
            $tune_name = get_field('tune_name', $post_id);
            $psalm_num = get_field('psalm_num', $post_id);

            $year_completed = get_field('year_completed', $post_id);
            $old_t4m = get_field('title_for_matching', $post_id);

        } else if ( $post_type == 'edition' ) {

            // ACF -- WIP
            $musical_works = get_field('repertoire_editions', $post_id, false);
            if ( empty($musical_works) ) {
                $musical_works = get_field('musical_work', $post_id, false);
            }
            if ( is_array($musical_works) && count($musical_works) == 1 ) {
                $musical_work_id = $musical_works[0];
            } else {
                // WIP -- TBD: how to deal w/ case of multiple works associated with one edition
                foreach ( $musical_works AS $musical_work_id ) {
                    //$musical_work_id =
                }
            }

            //
            $editors_str = "";
            $editors = get_field('editor', $post_id);
            foreach ( $editors as $editor ) {
                $editor_id = $editor->ID;
                if ( $editor_id ) {
                    $last_name = get_field('last_name', $editor_id);
                    if ($last_name) {
                        $editors_str .= $last_name;
                    } else {
                        $editors_str .= get_the_title($editor_id);
                    }
                    if ( is_array($editors) && count($editors) > 1 ) { $editors_str .= ", "; }
                }
            }
            // Trim trailing comma and space
            if ( substr($editors_str, -2) == ', ' ) {
                $editors_str = substr($editors_str, 0, -2); // trim off trailing comma
            }

            // get ACF fields
            // use the_field instead?
            $publisher = get_arr_str(get_field('publisher', $post_id));
            $publication = get_arr_str(get_field('publication', $post_id));
            $publication_date = get_field('publication_date', $post_id);
            $box_num = get_field('box_num', $post_id);
            $old_t4m = get_field('title_for_matching', $post_id);
            $choir_forces_str = get_arr_str(get_field('choir_forces', $post_id));
            $voicing_txt = get_field('voicing_txt', $post_id);
            $soloists_txt = get_field('soloists_txt', $post_id);
            $instrumentation_txt = get_field('instrumentation_txt', $post_id);

        }
    } else {
    	sdg_log( "[btt] No POST data arr or post_id!", $do_log );
    }

    // Taxonomies
    if ( empty($arr) && $post_id && ( $post_type == 'repertoire' || $post_type == 'edition' ) ) {

        sdg_log( "[btt] get taxonomy info from post_id: ".$post_id, $do_log );

        // Keys
        // Get term names for "key".
        $keys = wp_get_post_terms( $post_id, 'key', array( 'fields' => 'names' ) );
        if ( $keys ) { $keys_str = implode(", ",$keys); } else { $keys_str = ""; }
        if ( empty($keys_str) ) {
            $keys_str = get_field('key_name_txt', $post_id, false);
            //$keys_str = get_post_meta( $post_id, 'key_name_txt', true ); // or key_name?
            /*if ( ! $keys_str ) {
                $keys_str = get_field('key_name', $post_id, false);
            }*/
        }
        sdg_log( "[btt] keys_str: ".$keys_str, $do_log );

        if ( $post_type == 'repertoire' ) {
            $rep_categories = wp_get_post_terms( $post_id, 'repertoire_category', array( 'fields' => 'ids' ) );
        }

        if ( $post_type == 'edition' ) {

            // Get term names for "voicing".
            $voicings = wp_get_post_terms( $post_id, 'voicing', array( 'fields' => 'names' ) );
            if ( $voicings ) { $voicings_str = implode(", ", $voicings); } else { $voicings_str = ""; }
            sdg_log( "[btt] voicings_str: ".$voicings_str, $do_log );

            // Get term names for "soloist".
            $soloists = wp_get_post_terms( $post_id, 'soloist', array( 'fields' => 'names' ) );
            if ( $soloists ) { $soloists_str = implode(", ", $soloists); } else { $soloists_str = ""; }

            // Get term names for "instrument".
            $instruments = wp_get_post_terms( $post_id, 'instrument', array( 'fields' => 'names' ) );
            if ( $instruments ) { $instruments_str = implode(", ", $instruments); } else { $instruments_str = ""; }
        }

    }

    // Build the title
    if ( $post_type == 'repertoire' ) {

        // Hymns:
        // CATALOG_NUM -- FIRST_LINE -- TUNE_NAME

        // Not Hymns:
        // TITLE_CLEAN, from EXCERPTED_FROM, CATALOG_NUMBER, OPUS_NUMBER -- COMPOSER (COMPOSER DATES) *OR* (ANON_INFO) / arr. ARRANGER / transcr. TRANSCRIBER -- in KEY_NAME
        // In case of Psalms, prepend "Psalm"/"Psalms" as needed

        // TODO: get these IDs dynamically for portability
        // Check if tax term "Hymns" exists, e.g., and get get id... 240513
        $hymn_cat = term_exists( "hymns", "repertoire_category" );
		if ( $hymn_cat ) {
			$hymn_cat_id = $hymn_cat['term_id'];
		}
		$psalm_cat = term_exists( "psalms", "repertoire_category" );
		if ( $psalm_cat ) {
			$psalm_cat_id = $psalm_cat['term_id'];
		}
        $chant_cat = term_exists( "anglican-chant", "repertoire_category" );
		if ( $chant_cat ) {
			$chant_cat_id = $chant_cat['term_id'];
		}

        sdg_log( "[btt] title_clean: ".$title_clean, $do_log );

        if ( in_array($hymn_cat_id, $rep_categories) ) {

            // Hymns

            sdg_log( "[btt] This is a hymn.", $do_log );
            // TODO: deal w/ miscategorizations -- e.g.
            // Blest are the pure in heart (Ten Orisons) -- M. Searle Wright (1918-2004)

            // Hymn number
            $pattern = "/^[0-9]/i"; // starts w/ numeral -- e.g. "90 -- It came upon the midnight clear -- NOEL"
            if ( $catalog_num != "" ) {
                $new_title = "Hymn ". $catalog_num;
            } else if ( preg_match($pattern, $title_clean) ) {
                // starts w/ numeral
                $new_title = "Hymn "; // . $title_clean
            }

            // First line or title_clean
            if ( $first_line != "" ) {
                if ( $new_title != "" ) { $new_title .= " -- "; }
                $new_title .= $first_line;
            } else if ( $title_clean != "" ) {
                if ( $new_title != "" ) { $new_title .= " -- "; }
                $new_title .= $title_clean;
            }

            // Tune name
            if ( $tune_name != "" ) {
                if ( $new_title != "" ) { $new_title .= " -- "; }
                $new_title .= strtoupper($tune_name);
            }

            // Authorship info... -- TODO

        } else {

            // NOT Hymns

            // 1. Title (title_clean)
            if ( $title_clean != "" ) { $new_title = $title_clean; }

            // For Psalms, makes sure the title isn't just a number -- prepend "Psalm" or "Psalms" as needed
            if ( in_array($psalm_cat_id, $rep_categories) || in_array($chant_cat_id, $rep_categories ) ) { // Psalms & Anglican Chant

                // Check to see if title starts with numeral(s)
                // If so, prepend "Psalm" or "Psalms" to new_title
                $pattern1 = "/^[0-9]+(,\s*)[0-9]+/i"; // starts w/ sequence of commma-separated numbers >> multiple psalms -- e.g. "59, 60, 61 Anglican Chant (Atkins, Webb, Martin, Filsell)"
                $pattern2 = "/^[0-9]/i"; // starts w/ numeral >> single psalm -- e.g. "113 Anglican Chant"
                if ( preg_match($pattern1, $new_title) ) {
                    // starts w/ sequence of commma-separated numbers
                    $new_title = "Psalms ". $new_title;
                } else if ( preg_match($pattern2, $new_title) ) {
                    // starts w/ numeral >> single psalm
                    $new_title = "Psalm ". $new_title;
                } else if ( strpos($new_title, "Psalm") == 0 && ( strpos($psalm_num, ",") !== false || strpos($old_t4m, "psalms") !== false || strpos($old_title, "Psalms") !== false ) ) {
                    // starts w/ "Psalm" but this is actually a multi-psalm record
                    $new_title = str_replace('Psalm ', 'Psalms ', $new_title);
                }

            }

        }

        // 2. Excerpted From
        if ( !empty( $excerpted_from ) ) {
            $new_title .= ", from ".$excerpted_from;
        }

        // 3/4. Catalog/Opus nums
        if ( $catalog_num != "" && !in_array($hymn_cat_id, $rep_categories) ) { $new_title .= ", ". $catalog_num; } // NOT for Hymns
        if ( $opus_num != "" ) { $new_title .= ", ". $opus_num; }

        // 5. Authorship
        $new_title .= $authorship_info;

        // 6. Key(s)
        // TODO -- rethink this? tmp disabled -- don't really need it for rep titles.
        //if ( $keys_str != "" ) { $new_title .= " -- in ".$keys_str; }

        // 7. year_completed
        if ( !empty($year_completed) ) { $new_title .= " [comp. ".$year_completed."]"; }

    } else if ( $post_type == 'edition' ) {

        // musical_work: title_clean -- musical_work: composer (composer_dates) -- in key_name / ed. editor / publication / publisher / for voicing + soloists with instrumentation [in box_num]

        // 1. Musical Work
        if ( $musical_work_id != null ) {
            sdg_log( "[btt] musical_work_id: ".$musical_work_id, $do_log );

            $musical_work_title = get_the_title($musical_work_id);
            //$musical_work_title = get_field('post_title', $musical_work_id);

            // WIP: Get abbreviated version of work title w/ lastnames only

            // MW Authorship

            // A. Short version
            $authorship_args = array( 'data' => array( 'post_id' => $musical_work_id ), 'format' => 'edition_title', 'abbr' => true );
            $arr_authorship_info = get_authorship_info ( $authorship_args );
            $rep_authorship_short = $arr_authorship_info['info'];
            $ts_info .= $arr_authorship_info['ts_info'];

            // ltrim punctuation so as to avoid failed replacement if work, e.g., has no arranger but no composer listed -- TODO: integrate this into get_authorship_info fcn?
            $rep_authorship_short = ltrim( $rep_authorship_short, ', ' );
            $rep_authorship_short = ltrim( $rep_authorship_short, '-- ' );
            //$rep_authorship_short = trim( $rep_authorship_short, '()' ); // nope
            sdg_log( "[btt/edition] rep_authorship_short: ".$rep_authorship_short, $do_log );

            // B. Long version
            $authorship_args = array( 'data' => array( 'post_id' => $musical_work_id ), 'format' => 'edition_title', 'abbr' => false );
            $arr_authorship_info = get_authorship_info ( $authorship_args );
            $rep_authorship_long = $arr_authorship_info['info'];
            $ts_info .= $arr_authorship_info['ts_info'];

            // remove punctuation for purposes of string replacement
            $rep_authorship_long = ltrim( $rep_authorship_long, ', ' );
            $rep_authorship_long = ltrim( $rep_authorship_long, '-- ' );
            // authorship info will have been put in parens for psalms -- remove them for purposes of string replacement
            $rep_authorship_long = ltrim( $rep_authorship_long, '(' ); // left enclosing paren
            $rep_authorship_long = str_replace( "))", ")", $rep_authorship_long ); // get rid of final left enclosing paren but not closing paren for author dates

            sdg_log( "[btt/edition] rep_authorship_long: ".$rep_authorship_long, $do_log );
            sdg_log( "[btt/edition] musical_work_title: ".$musical_work_title, $do_log );

            // Compare short/long authorship; replace as needed
            if ( $rep_authorship_long != $rep_authorship_short ) {
                sdg_log( "[btt/edition] replace long with short authorship info.", $do_log );
                //htmlspecialchars_decode()
                //html_entity_decode()
                // TODO: figure out why this doesn't always work as expected -- sometimes no replacement is made despite the availability of valid strings.
                $count = 0;
                $musical_work = str_replace( $rep_authorship_long, $rep_authorship_short, $musical_work_title, $count );
                sdg_log( "[btt/edition] num replacements made: ".$count." (Replace [".$rep_authorship_long."] with [".$rep_authorship_short."] in [".$musical_work_title."] )", $do_log ); // tft
                sdg_log( "[btt/edition] revised musical_work_title: ".$musical_work, $do_log );
            } else {
                sdg_log( "[btt/edition] rep_authorship_long same as rep_authorship_short.", $do_log );
                $musical_work = $musical_work_title;
            }
            $new_title .= $musical_work;
            //sdg_log( "[btt/edition] revised musical_work_title: ".$musical_work, $do_log );
            //sdg_log( "[btt/edition] new_title (after adding musical_work info): ".$new_title, $do_log );
        } else {
            sdg_log( "[btt/edition] Abort! No musical_work found upon which to build the title.", $do_log );
            return "<pre>*** Error! Abort! No musical_work found upon which to build the title for Edition with post_id: $post_id. ***</pre>";
            //return null; // no musical work for building edition title.
        }

        // 2. Key(s)
        if ( $keys_str != "" && $uid_field == 'title_for_matching' ) { $new_title .= " -- in ".$keys_str; }

        // Publication info

        // 3. Editor
        if ( $editors_str != "" ) { $new_title .= " / ed. ".$editors_str; }
        //sdg_log( "[btt/edition] new_title (after editors_str): ".$new_title, $do_log );

        // 4. Publication
        if ( $publication != "" ) { $new_title .= " / ".$publication; }
        //sdg_log( "[btt/edition] new_title (after publication): ".$new_title, $do_log );

        // 5. Publisher
        if ( $publisher != "" ) { $new_title .= " / ".$publisher; }
        //sdg_log( "[btt/edition] new_title (after publisher): ".$new_title, $do_log );

        // 6. Voicings
        //sdg_log( "[btt/edition] voicings_str: ".$voicings_str, $do_log );
        if ( $voicings_str == "" ) {
            //sdg_log( "[btt/edition] voicings_str is empty >> try voicing_txt.", $do_log );
            if ( $voicing_txt != "" ) {
                $voicings_str = $voicing_txt;
                //sdg_log( "[btt/edition] voicings_str from voicing_txt: ".$voicings_str, $do_log );
            } else {
                //sdg_log( "[btt/edition] no luck w/ voicing_txt >> voicings_str still empty: [".$voicings_str."]", $do_log );
            }
        }
        if ( $voicings_str != "" && $uid_field == 'title_for_matching' ) {
            //sdg_log( "[btt/edition] add voicings_str to new_title.", $do_log );
            if ( is_array($voicings_str) ) {
            	$voicings_str = print_r($voicings_str, true);
            } else if ( $voicings_str == "Array" ) {
            	$voicings_str = "str: Array";
            }
            $new_title .= " / for ".$voicings_str;
            //$new_title .= " / for [voicings] ".$voicings_str; // tft
        }
        //sdg_log( "[btt/edition] new_title (after voicings_str): ".$new_title, $do_log );

        // (if no voicing, but yes soloists, then "for soloists")
        // multiple voicings &c. -- connect w/ ampersand?

        // 7. Soloists
        //sdg_log( "[btt/edition] soloists_str: ".$soloists_str, $do_log );
        if ( $soloists_str == "" && $soloists_txt != "" ) { $soloists_str = $soloists_txt; }
        if ( $soloists_str != "" && $uid_field == 'title_for_matching' ) {
            if ( $voicings_str != "" ) {
                $new_title .= " + ".$soloists_str;
            } else {
                $new_title .= " / for ".$soloists_str;
                //$new_title .= " / for [soloists] ".$soloists_str; // tft
            }
        }
        //sdg_log( "[btt/edition] new_title (after soloists_str): ".$new_title, $do_log );

        // Choir Forces -- if no Voicings or Soloists
        if ( $choir_forces_str != "" & $voicings_str == "" && $soloists_str == "" && $uid_field == 'title_for_matching' ) {
            sdg_log( "[btt/edition] no voicings or soloists info >> use choir_forces_str: ".$choir_forces_str, $do_log );
            $new_title .= " / for ".$choir_forces_str;
            //$new_title .= " / for [cf] ".$choir_forces_str; // tft
        } else {
            sdg_log( "[btt/edition] choir_forces: ".$choir_forces_str, $do_log );
        }

        // 8. Instrumentation
        //sdg_log( "[btt/edition] instruments_str: ".$instruments_str, $do_log );
        if ( $instruments_str == "" && $instrumentation_txt != "" ) { $instruments_str = $instrumentation_txt; }
        if ( $instruments_str != "" && $uid_field == 'title_for_matching' ) {
            if ( $voicings_str == "" && $soloists_str == "" ){
                $new_title .= " /";
            }
            if ( $instruments_str == "unacc" || $instruments_str == "Unaccompanied" ) {
                $new_title .= " (Unaccompanied)";
            } else if ( $voicings_str != "" || $soloists_str != "" || $choir_forces_str != "" ) {
                $new_title .= " with ".$instruments_str;
            } else {
                $new_title .= " for ".$instruments_str;
                //$new_title .= " for [instr] ".$instruments_str; // tft
            }
        }
        //sdg_log( "[btt/edition] new_title (after instruments_str): ".$new_title, $do_log );

        // 9. Box num
        if ( $box_num != "" ) { $new_title .= " [in ".$box_num."]"; }
        //sdg_log( "[btt/edition] new_title (after box_num): ".$new_title, $do_log );

    }

    sdg_log( "[btt] new_title (final): ".$new_title, $do_log );

    return $new_title;

}

// https://developer.wordpress.org/reference/hooks/updated_(meta_type)_meta/
// do_action( "updated_{$meta_type}_meta", int $meta_id, int $object_id, string $meta_key, mixed $_meta_value )
// do_action( "updated_post_meta", int $meta_id, int $object_id, string $meta_key, mixed $_meta_value )
// Fires immediately after updating metadata of a specific type.


/* 220223 moved from apostle/functions.php */

// TODO update for ACF -- get field_ids
///add_filter( 'wp_insert_post_data' , 'modify_post_title' , '99', 2 );
function modify_post_title( $data ) {

	if ($data['post_type'] == 'reading') {

		$title = $data['post_title'];
		if ($title == '') {
			//if ( isset($_POST['acf']['field_abc123']) ) { // // Check if book value was updated.
            //$arr['title_clean']     = $_POST['acf']['field_624615e7eca6f'];
			if ( isset( $_REQUEST['acf']['field_62718b8b97a1c'] ) ) {
				$arr_book = $_REQUEST['acf']['field_62718b8b97a1c'];
				$book_name = get_the_title($arr_book[0]);
                $title .= ucfirst($book_name).' ';
			}
			if ( isset( $_REQUEST['acf']['field_62718bc597a1d'] ) ) {
				$chapterverses = $_REQUEST['acf']['field_62718bc597a1d'];
				$title .= $chapterverses;
			}
			if ($title == '') { $title = 'Unnamed Reading Record'; }
		}
		$data['post_title'] = $title;

	} else if ($data['post_type'] == 'lectionary') {

		$title = $data['post_title'];
		if ( isset( $_REQUEST['acf']['field_62742fcbdac58'] ) ) {
			$year = $_REQUEST['acf']['field_62742fcbdac58'];

			if (strpos($title,'(Year '.ucfirst($year).')') === false) {

				if (strpos($title,'Year ') !== false) {
					$pattern = '/Year [A-C]/';
					$replacement = 'Year '.ucfirst($year);
					$title = preg_replace($pattern, $replacement, $title);
				} else {
					$title .= ' (Year '.ucfirst($year).')';
				}
			}
		}
		if ($title == '') { $title = 'Unnamed Lectionary Record'; }
		$data['post_title'] = $title;

	}

	return $data;
}


// TMP(?) disabled 02/02/23 -- will need to test to determine whether it's necessary when first saving/publishing a NEW post...
//add_filter('wp_insert_post_data', 'build_the_title_on_insert', 10, 2);
function build_the_title_on_insert( $data, $postarr ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = false;
    sdg_log( "divline1", $do_log );
    sdg_log( "function called: build_the_title_on_insert", $do_log );
    sdg_log( ">> filter: wp_insert_post_data", $do_log );

    // $data: (array) An array of slashed, sanitized, and processed post data.
    // $postarr: (array) An array of sanitized (and slashed) but otherwise unmodified post data.

    $post_id = $postarr['ID'];
    $post_type = $data['post_type'];
    $new_title = null;

    sdg_log( "[bttoi] post_id: ".$post_id."; post_type: ".$post_type, $do_log );

    if ( $post_id == 0 || ( $post_type != 'repertoire' && $post_type != 'edition' ) ) {
        return $data;
    }

    /*
    // ids for testing
    $dev_rep_ids = array ('16679', '31020', '23070');
    $live_rep_ids = array ('167740');
    $dev_edition_ids = array();
    $live_edition_ids = array();
    if ( is_dev_site() ) {
        //$test_ids = $dev_rep_ids;
        //$test_ids = $dev_edition_ids;
        $test_ids = array_merge($dev_rep_ids, $dev_edition_ids);
    } else {
        //$test_ids = $live_rep_ids;
        //$test_ids = $live_edition_ids;
        $test_ids = array_merge($live_rep_ids, $live_edition_ids);
    }
    //if ( !in_array($post_id, $test_ids) ) { return $data; }
    */

    //sdg_log( "data: ".print_r($data, true), $do_log );
    //sdg_log( "postarr: ".print_r($postarr, true), $do_log );
    //sdg_log( "_POST array: ".print_r($_POST, true), $do_log );

    // TODO: figure out how to run this ONLY if the post is being saved for the first time... Or is it not needed at all? Only run btt via sspc?
    /*
    if ( $post_type == 'repertoire' && isset($_POST['acf']['field_624615e7eca6f']) ) {

        sdg_log( "[bttoi] build the array of repertoire _POST data for submission to fcn build_the_title.", $do_log );

        // Get custom field data from $_POST array
        $arr = array(); // init

        //$field = get_field_object('XXX');
        //$field_key = $field['key'];
        //$arr['XXX'] = $_POST['acf']['field_XXX'];

        $arr['title_clean']     = $_POST['acf']['field_624615e7eca6f'];
        $arr['excerpted_from']  = $_POST['acf']['field_624616c9c8dc6']; // id
        $arr['excerpted_from_txt'] = $_POST['acf']['field_624616e0c8dc7']; // text
        $arr['catalog_number']  = $_POST['acf']['field_6240a7037b239'];
        $arr['opus_number']     = $_POST['acf']['field_6240a7037b272'];

        $arr['composers']       = $_POST['acf']['field_6240a74946dae']; // field name: composer ==> array of ids
        $arr['anon_info']       = $_POST['acf']['field_624616aac8dc5'];
        $arr['arrangers']       = $_POST['acf']['field_6246160deca70']; // field name: arranger ==> array of ids
        $arr['transcribers']    = $_POST['acf']['field_6246163ceca71']; // field name: transcriber ==> array of ids
        $arr['key_name']        = $_POST['acf']['field_604947ec970c0']; // group: legacy & admin fields
        $arr['year_completed']  = $_POST['acf']['field_6240a7037b2ab'];

        $arr['first_line']      = $_POST['acf']['field_6240a7037b445'];
        $arr['tune_name']       = $_POST['acf']['field_6240a7037b35b'];
        $arr['psalm_num']       = $_POST['acf']['field_5eed36b4593d3'];

        // Rep Categories
        $arr['rep_categories'] = $_POST['tax_input']['repertoire_category']; // array of ids
        $arr['keys'] = $_POST['tax_input']['key']; // array of ids

        $new_title = build_the_title( $post_id, 'title_for_matching', $arr ); // btt( $post_id = null, $uid_field = 'title_for_matching', $arr = array(), $abbr = false )

    } else if ( $data['post_type'] == 'edition' && ( isset($_POST['acf']['field_6244d279cde53']) || isset($_POST['acf']['field_626811b538d8e']) ) ) { //  field_626811b538d8e

        sdg_log( "[bttoi] build the array of edition _POST data for submission to fcn build_the_title.", $do_log );

        //sdg_log( "_POST array: ".print_r($_POST, true), $do_log );

        // Get custom field data from $_POST array
        $arr = array(); // init

        $arr['repertoire_editions'] = $_POST['acf']['field_6244d279cde53'];
        $arr['musical_work']    = $_POST['acf']['field_626811b538d8e'];

        $arr['choir_forces']    = $_POST['acf']['field_626817165f103'];

        $arr['editor']          = $_POST['acf']['field_6268174ef7f13']; // array of ids
        $arr['publication']     = $_POST['acf']['field_626817a097838']; // single id
        $arr['publisher']       = $_POST['acf']['field_6268177797837']; // single id
        $arr['publication_date']= $_POST['acf']['field_626818039783a'];
        $arr['box_num']         = $_POST['acf']['field_626818291431d'];

        // Taxonomies
        $arr['voicings']        = $_POST['tax_input']['voicing']; // array of ids
        $arr['soloists']        = $_POST['tax_input']['soloist']; // array of ids
        $arr['instruments']     = $_POST['tax_input']['instrument']; // array of ids
        $arr['keys']            = $_POST['tax_input']['key']; // array of ids

        // Legacy fields
        // dev site
        $arr['voicing_txt'] = $_POST['acf']['field_6040f76ca740b'];
        $arr['soloists_txt'] = $_POST['acf']['field_6040f8f9ed577'];
        $arr['instrumentation_txt'] = $_POST['acf']['field_6040f901ed578'];
        $arr['key_name_txt'] = $_POST['acf']['field_60429a952e6be'];

        $new_title = build_the_title( $post_id, 'title_for_matching', $arr, true ); // btt( $post_id = null, $uid_field = 'title_for_matching', $arr = array(), $abbr = false )

    } else if ( $data['post_type'] == 'repertoire' || $data['post_type'] == 'edition' ) {

        $new_title = build_the_title( $post_id ); // btt( $post_id = null, $uid_field = 'title_for_matching', $arr = array(), $abbr = false )

    } else {

        sdg_log( "[bttoi] Insufficient data for building the array of _POST data for submission to fcn build_the_title." );

    }*/

    if ( $new_title ) {

        // Abort if error
        if ( strpos($new_title, 'Error! Abort!') !== false ) {

            sdg_log( $new_title, $do_log );

        } else if ( $new_title == $data['post_title'] ) {

            sdg_log( "[bttoi] new_title same as old_title.", $do_log );

        } else {

            sdg_log( "[bttoi] old_title: ".$data['post_title'], $do_log );
            sdg_log( "[bttoi] new_title: ".$new_title, $do_log );

            // Save new title to data array
            $data['post_title'] = $new_title;
            //$postarr['post_title'] = $new_title;

            // Also save new slug, created based on new_title
            $new_slug = sanitize_title($new_title);
            //$data['post_name'] = $new_slug; // tmp disabled
        }

    }

    return $data;
    //return $postarr;
}


/*
// TODO
// Register the filter
add_filter('wp_unique_post_slug','prefix_the_slug',10,6);

function prefix_the_slug($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug){
    //Get value from the custom field
    $prefix = get_post_meta($post_ID,'cw_event_start_date',true);

    //Only prefix certain post type and if prefix field existed
    if($post_type==='post' && $prefix && !empty($prefix)){

        //Prefix only if it is not already prefixed
        preg_match ('/^\d\d\d\d\d\d/', $slug, $matches, PREG_OFFSET_CAPTURE);
        if(empty($matches)){
            return $prefix.'-'.$slug;
        }
    }
    return $slug;
}

*/


// Hide everything within and including the square brackets
// e.g. for titles matching the pattern "{Whatever} [xxx]" or "[xxx] {Whatever}"
function remove_bracketed_info ( $str, $remove_parens = false ) { //function sdg_remove_bracketed_info ( $str ) {

	//sdg_log( "function: remove_bracketed_info", $do_log );

	if ( strpos($str, '[') !== false ) {
		$str = preg_replace('/\[[^\]]*\]([^\]]*)/', trim('$1'), $str); // Bracketed info at end of string
		$str = preg_replace('/([^\]]*)\[[^\]]*\]/', trim('$1'), $str); // Bracketed info at start of string?
	}

	// Optionally, also remove everything within and includes parentheses
	if ( $remove_parens && strpos($str, '(') !== false ) {
		$str = preg_replace('/\([^\)]*\)([^\)]*)/', trim('$1'), $str);
		//$str = preg_replace('/([^\)]*)\([^\)]*\)/', trim('$1'), $str);
	}

	$str = trim($str);

	return $str;
}

// Function: clean up titles for creation of slugs and for front-end display
///add_filter( 'the_title', 'filter_the_title', 100, 2 );
function filter_the_title( $post_title, $post_id = null ) {

    //sdg_log( "function: filter_the_title", $do_log );
    $post_type = ""; // init

    if ( !is_admin() ) {

        if ( $post_id ) {
            $post = get_post( $post_id );
            $post_type = $post->post_type;
        }

        $return_revised = true;
        $post_title = make_clean_title( $post_id, $post_title, $return_revised );

        $post_title = sdg_format_title($post_title);

    }

    return $post_title;
}

// TODO/WIP: Troubleshoot
function make_clean_title( $post_id = null, $post_title = null, $return_revised = true ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function: make_clean_title", $do_log );

    //if ( $post_id === null ) { $post_id = get_the_ID(); }

    $clean_title = null; // init
    $post_type = null; // init

    //if ( empty($post_id ) { return false; } // WIP
    if ( $post_id === null && $post_title === null ) { return null; }

    if ( $post_id ) {
        $post = get_post( $post_id );
        $post_type = $post->post_type;
        if ( empty($post_title) ) {
            $post_title = $post->post_title;
        }
    }

    //sdg_log( "[smct] post_id: ".$post_id."; post_title: ".$post_title, $do_log );

    if ( $return_revised == true ) {
        //sdg_log( "[smct] >> return_revised", $do_log );
    }

	if ( is_admin() && $return_revised == true ) {

        if ( $post_type === 'event' ) {

            $clean_title = remove_bracketed_info($post_title);

			// Check to see if this is a legacy record with an ugly title;
			// If so, then modify display accordingly AND add the missing title_clean postmeta record

			if ( preg_match('/([0-9]+)_(.*)/', $clean_title) ) {
				$clean_title = preg_replace('/([0-9]+)_(.*)/', '$2', $clean_title);
				$clean_title = str_replace("_", " ", $clean_title);
			}

		} else if ( $post_type === 'repertoire' ) {

            $first_line = get_field( 'first_line', $post_id );

			// Hymns -- use first line
			if ( has_term( 'hymns', 'repertoire_category', $post_id ) && !empty($first_line != "") ) {
				$clean_title = $first_line;
				$display_title = $clean_title;
			} else {
				// WIP
				$clean_title = null;
			}

		}

        // WIP/TS
        /*if ( !empty($clean_title) && $post_type == 'repertoire' ) { // 230220 added post_type check -- TODO: re-expand application, but make sure to update this field if post_title is updated! e.g. for events
			if ( ! add_post_meta( $post_id, 'title_clean', $clean_title, true ) ) {
				update_post_meta ( $post_id, 'title_clean', $clean_title );
			}
		}*/

    } else if ( !is_admin() ) {

        // On the front end, display clean event titles for imported legacy events

        if ( $post_type == 'event' ) {

            $title_clean = get_post_meta( $post_id, 'title_clean', true );

            if ( $title_clean ) {

                $clean_title = $title_clean;

            } else {

                // Check to see if this is a legacy record with an ugly title;
                // If so, then modify display accordingly AND add the missing title_clean postmeta record
                if ( preg_match('/([0-9]+)_(.*)/', $post_title) ) {
                    $clean_title = preg_replace('/([0-9]+)_(.*)/', '$2', $clean_title);
                    $clean_title = str_replace("_", " ", $clean_title);
                    //add_post_meta( $post_id, 'title_clean', $post_title, true );
                }

            }

            // Hide everything after and including the colon for events with titles matching the pattern "{Whatever} Eucharist: {xxx}"
			if ( $clean_title && strpos($clean_title, 'Eucharist:') !== false ) {
				$clean_title = preg_replace('/(.*) Eucharist: (.*)/', '$1 Eucharist', $clean_title);
			}

        }

		//$clean_title = str_replace("--", "&mdash;", $clean_title);

	}

	// If no clean_title has been made, default to the original post_title
	if ( empty($clean_title) ) {
		$clean_title = $post_title;
	}

	if ( !is_admin() ) {
    	$clean_title = remove_bracketed_info($clean_title);
    	$clean_title = str_replace("|", "<br />", $clean_title);
    }

	return $clean_title;

}

// TODO: fix this for event posts being saved for the first time
// -- in that case, event_date not stored as metadata so must figure out how to retrieve it from the _POST array and pass it to this fcn.
function clean_slug( $post_id ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function: clean_slug", $do_log );

    // TODO check for empty post_id
    $post = get_post( $post_id );

    $info = "";
    $changes_made = false;
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;";
    $legacy_event_id = null; // init
    $title_for_matching = null; // init
    $append_date = true;
    $cancelled = false;

    // Get post_title as starting point for new slug
    $post_title = $post->post_title;
    $slug = $post->post_name;
    //$info .= $indent.'<span class="label">post_title: </span>'.$post_title.'<br />';
    $info .= "<!-- post_title: $post_title -->";

    // Clean title
    $title_clean = get_post_meta( $post_id, 'title_clean', true );
    //sdg_log( "title_clean (get_post_meta): ".$title_clean, $do_log );
    if ( empty ($title_clean) || strpos($title_clean, '[') !== false ) {
        $title_clean = make_clean_title( $post_id );
        //sdg_log( "title_clean (make_clean_title): ".$title_clean, $do_log );
    }
    sdg_log( "title_clean: ".$title_clean);

    // If the title_clean doesn't already contain date info, then...
    // ...make a better slug by combining title_clean and event_date

    // Deal w/ posts with date info as part of their titles
    $match_pattern = "~January|February|March|April|May|June|July|August|September|October|November|December~";
    if ( preg_match($match_pattern, $title_clean) ) {
        //$info .= $indent."** title_clean contains date info.<br />";
        $append_date = false;
        sdg_log( "append_date FALSE", $do_log );
    } else {
        sdg_log( "append_date TRUE", $do_log );
        //$info .= $indent."title_clean does NOT contain date info.<br />";
    }
    //$info .= "title_clean: $title_clean<br />";

    // **** First step in new slug

    // Get the "clean" permalink based on the post title
    $new_slug = sanitize_title( $title_clean, $post_id );
    //$info .= $indent.'<span class="label">new_slug: </span>'.$new_slug.'<br />'; //$info .= "new_slug: $new_slug<br />";

    // WIP: if a custom permalink has been created post 07-01-2020, then use it as the basis...
    /*if ( $slug != $clean_permalink ) {
        $new_slug = $slug;
    } else {

    }*/

    // ***
    // If title contains "-cancelled", move that to the end of the slug -- WIP
    $match_pattern = "/cancelled/";
    if ( preg_match($match_pattern, $new_slug) ) {
        $info .= "<!-- new_slug contains 'cancelled' -->";
        //$info .= $indent."** new_slug contains 'cancelled'.<br />";
        $new_slug = str_replace('-cancelled', '', $new_slug);
        $new_slug = str_replace('cancelled-', '', $new_slug);
        $cancelled = true;
    } else {
        //$info .= $indent."new_slug does NOT contain '-cancelled'.<br />"; // tft
    }

    // Clean up special characters
    // e.g. "Christoph Schl√ºtter" >>> new_slug: christoph-schl%e2%88%9aotter-2012-09-23
    // TODO -- maybe: if special chars detected, e.g. "√º", add special-chars admin tag and do manual cleanup later?
    /*
    // If update was successful, add admin tag to note that slug has been updated
    $terms = array( 1960 ); // slug-updated
    //wp_set_post_terms( $post_id, $terms, $taxonomy, $append );
    wp_set_post_terms( $post_id, $terms, 'admin_tag', true );
    */

    /*if ( preg_match('/([0-9]+)/', $title_clean) ) {
        $info .= "title_clean contains numbers.<br />";
        $append_date = false;
    } else {
        $info .= "title_clean does NOT contain numbers.<br />";
    }*/

    if ( $append_date == true ) {
        $date_str = get_post_meta( $post_id, '_event_start_date', true );
        // Check to see if title_clean ends in "-2" -- if so, remove those final two chars before appending the date_str
        if ( substr($new_slug, -2) == '-2' ) {
            $new_slug = substr($new_slug, 0, -2); // trim off trailing "-2"
        }
        $new_slug .= "-".$date_str;
    } else if ( is_dev_site() ) {
        //$new_slug .= "-ND";
    }

    if ( $cancelled == true ) {
        $new_slug .= "-cancelled";
    }

    //$info .= "<!-- old slug: $slug -->";
    //$info .= "<!-- new_slug: $new_slug -->";
    //$info .= $indent.'<span class="label">old slug: </span>'.$slug.'<br />';
    //$info .= $indent.'<span class="label">new slug: </span>'.$new_slug.'<br />';

    //$info .= '</div>';

    // TODO: check to see if new_slug is unique. If not, append a v2?

    $arr_info['info'] = $info;
    $arr_info['new_slug'] = $new_slug;
    $arr_info['old_slug'] = $slug;

    return $arr_info;

}

/*

// https://developer.wordpress.org/reference/hooks/save_post/
// Fires once a post has been saved.
// NB: Priority setting (in this case "20") seems to be a significant factor.
// TODO: figure this out!

*/
add_action( 'save_post', 'sdg_save_post_callback', 10, 3 );
function sdg_save_post_callback( $post_id, $post, $update ) {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "updates") );
    $do_log = devmode_active( array("sdg", "updates") );
    sdg_log( "divline1", $do_log );
    //sdg_log( "action: save_post", $do_log );
    //sdg_log( "action: save_post_event", $do_log );
    sdg_log( "function called: sdg_save_post_callback", $do_log );

    if ( is_dev_site() ) {
        sdg_add_post_term( $post_id, 'dev-test-tmp', 'admin_tag', true ); // tft
    }

    // Don't run if this is an auto-draft
    if ( isset($post->post_status) && 'auto-draft' == $post->post_status ) {
        return;
    }

    // Don't run if this is an Autosave operation
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }

    // If this is a post revision, then abort
    if ( wp_is_post_revision( $post_id ) ) {
        sdg_log( "[sspc] is post revision >> abort.", $do_log );
        return;
    }

    $post_type = $post->post_type;
    //$post_type = get_post_type( $post_id );
    // get custom fields from $_POST var?
    sdg_log( "[sspc] post_type: ".$post_type, $do_log );

    // ***WIP***
    if ( $post_type == "event" ) {

        // Is this part of a recurring event series?
        if ( $update ) {
            $recurrence_id = get_post_meta( $post_id, '_recurrence_id', true );
        } else {
            $recurrence_id = false;
        }

        // If this event is part of a recurring event series, then abort
        if ( $recurrence_id ) {
            sdg_log( "[sspc] is part of recurring event series ($recurrence_id) >> abort.", $do_log );
            return;
        }

        //$post = get_post( $post_id );

        // TODO: consolidate this w/ run_post_updates function -- too much redundancy!
        //sdg_run_post_updates( $atts = array() )
        //$info = sdg_run_post_updates( array ( 'post_id' => $post_id ) )

        /*** POST SLUG ***/

        // Get a new and better slug and update the post accordingly.

        if ( has_term( 'slug-updated', 'admin_tag', $post_id ) ) {
            // Slug has already been updated... But is it ok, or is further revision needed?
        } else {
            // If slug has NOT already been updated...
        }

        $new_slug_info = clean_slug( $post_id );
        $new_slug = $new_slug_info['new_slug'];
        $old_slug = $post->post_name;
        sdg_log( "[sspc] old_slug: ".$old_slug, $do_log );
        sdg_log( "[sspc] new_slug: ".$new_slug, $do_log );

        // Check to see if new_slug is really new. If it's identical to the existing slug, skip the update process.
        if ( $new_slug != $old_slug ) {

            // unhook this function to prevent infinite looping
            remove_action( 'save_post', 'sdg_save_post_callback' );
            //remove_action( 'save_post_event', 'sdg_save_event_post_callback' );

            // Update the post
            $update_args = array(
                'ID'           => $post_id,
                'post_name'     => $new_slug,
            );

            // Update the post into the database
            wp_update_post( $update_args, true );

            if ( ! is_wp_error($post_id) ) {
                // If update was successful, add admin tag to note that slug has been updated
                sdg_add_post_term( $post_id, 'slug-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
                //$info .= sdg_add_post_term( $post_id, 'slug-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
            }

            // re-hook this function
            add_action( 'save_post', 'sdg_save_post_callback', 10, 3 );
            //add_action( 'save_post_event', 'sdg_save_event_post_callback' );

        }

    } else if ( $post_type == "repertoire" || $post_type == "edition" ) {

        // TODO: check to see if this save is happening via WP All Import and if so, skip t4m changes

        // Get post obj, post_title, $old_t4m
        $the_post = get_post( $post_id );
        $post_title = $the_post->post_title;
        $title_clean = get_post_meta( $post_id, 'title_clean', true ); // or use get_field?

        // Get title/slug based on post field values
        // TODO: create separate build_the_title functions per CPT(?)
        $new_title = build_the_title( $post_id );
        $new_slug = sanitize_title($new_title);
        $old_t4m = get_post_meta( $post_id, 'title_for_matching', true ); // or use get_field?

        sdg_log( "[sspc] about to call function: get_title_uid", $do_log );
        $new_t4m = get_title_uid( $post_id, $post_type, $new_title ); // ( $post_id = null, $post_type = null, $post_title = null, $uid_field = 'title_for_matching' )

        sdg_log( "divline2", $do_log );

        sdg_log( "[sspc] post_id: ".$post_id, $do_log );
        sdg_log( "[sspc] post_type: ".$post_type, $do_log );
        sdg_log( "[sspc] post_title: ".$post_title, $do_log );
        sdg_log( "[sspc] title_clean: ".$title_clean, $do_log );
        //
        sdg_log( "[sspc] new_title: ".$new_title, $do_log );
        sdg_log( "[sspc] new_slug: ".$new_slug, $do_log );
        // WIP:
        //sdg_log( "old_title: ".$old_title );
        sdg_log( "[sspc] old_t4m: ".$old_t4m, $do_log );
        sdg_log( "[sspc] new_t4m: ".$new_t4m, $do_log );

        //$update_data = array(); // init

        // If we've got a new post_title and/or new t4m, prep to run the update
        /*if ( ( $new_t4m != "" && $new_t4m != $old_t4m) || ( $new_title != "" && $new_title != $old_title ) ) {
            $update_data['ID'] = $post_id;
        }*/

        if ( !empty($new_t4m) && $new_t4m != $old_t4m && $new_t4m != "test") {

            // TODO: get this to work when called via Update for individual post record (post.php)
            // For the moment, the t4m only gets updates when run via the title_updates function. No idea why.
            sdg_log( ">> update t4m", $do_log );
            //$update_data['meta_input'] = array( 'title_for_matching' => $new_t4m );
            $meta_update = update_post_meta( $post_id, 'title_for_matching', $new_t4m );
            sdg_log( "meta_update: ".$meta_update, $do_log );
            if ( $meta_update === true ) {
                sdg_log( "success!", $do_log );
                //sdg_log( sdg_add_post_term( $post_id, array('t4m-updated', 'programmatically-updated'), 'admin_tag', true ), $do_log );
            } else {
                //(int|bool) Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
            }


        }

    	// Check to see if new_slug is really new. If it's identical to the existing slug, skip the update process.
        if ( $new_title != $post_title ) {

			sdg_log( "[sspc] update the post_title", $do_log );

			// TODO: figure out how NOT to trigger wp_insert_post_data when running this update...

            // unhook this function to prevent infinite looping
            remove_action( 'save_post', 'sdg_save_post_callback' );
            //remove_action( 'save_post_event', 'sdg_save_event_post_callback' );

            // Update the post
            $update_args = array(
                'ID'       	=> $post_id,
                'post_title'=> $new_title,
                'post_name'	=> $new_slug,
            );

            // Update the post into the database
            wp_update_post( $update_args, true );

            if ( ! is_wp_error($post_id) ) {
                // If update was successful, add admin tag to note that slug has been updated
                sdg_add_post_term( $post_id, 'title-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
                //$info .= sdg_add_post_term( $post_id, 'slug-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
            }

            // re-hook this function
            add_action( 'save_post', 'sdg_save_post_callback', 10, 3 );
            //add_action( 'save_post_event', 'sdg_save_event_post_callback' );

        }

        /*** TITLE POSTMETA ***/
        /*
        // Get the title_for_matching. If none is set, or if it's empty, run the update function.
        $title_for_matching = get_post_meta( $post_id, 'title_for_matching', true );
        //$title_for_matching = get_post_meta( $post_id, 'title_for_matching', true );

        if ( empty($title_for_matching) ) {
            $t4m_info = update_title_for_matching( $post_id ); // sdg custom function
            $title_for_matching = $t4m_info['title_for_matching'];
            //$info .= $t4m_info['info'];
        }
        */

        // Get the title_clean. If none is set, or if it's empty, run the update function.
        // TODO: if title_clean is set and post_title != title_clean, replace post_title w/ title_clean?
        /*if ( $new_title_clean = make_clean_title( $post_id ) ) {
            $display_title = $new_title_clean;
        }*/

    } else if ( $post_type == "sermon" ) {

    	sdg_log( "[sspc] update the sermon_bbooks", $do_log );
    	if ( function_exists('update_sermon_bbooks') && update_sermon_bbooks( $post_id ) ) {
    		sdg_log( "[sspc] Success! Updated the sermon_bbooks", $do_log );
    	} else {
    		sdg_log( "[sspc] ERROR! Failed to update the sermon_bbooks", $do_log );
    	}

    } // end post_type check

}


/*********** DEV/CLEANUP FUNCTIONS ***********/

// Bulk updates to titles and title_for_matching postmeta values
// This fcn will be used primarily (exclusively?) for repertoire and edition records
///add_shortcode('title_updates', 'run_title_updates');
function run_title_updates ($atts = array(), $content = null, $tag = '') {

    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "titles") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: run_title_updates", $do_log );

    $info = "";

    $args = shortcode_atts( array(
		'post_type'   => 'repertoire',
        //'meta_key'  => null,
		'num_posts'   => 5,
        'post_id'  => null,
        'taxonomy'  => null,
        'tax_terms'  => null,
        'meta_key'  => null,
        'meta_value'  => null,
        'date_query' => 'before',
        'date_str'  => '1 week ago',
        'verbose'  => 'false',

    ), $atts );

    // Extract
	extract( $args );

    /*if ( strpos($tax_terms,",")) {
        $tax_terms = sdg_att_explode( $tax_terms );
        $tax_terms = "array('".implode("','",$tax_terms)."')"; // wrap each item in single quotes
    }*/
    if ( strpos($tax_terms,"NOT-") !== false ) {
        $tax_terms = str_replace("NOT-","",$tax_terms);
        $tax_operator = 'NOT IN';
    } else {
        $tax_operator = 'IN';
    }
    //$meta_values = sdg_att_explode( $meta_values );
    //$meta_values = "array('".implode("','",$meta_values)."')"; // wrap each item in single quotes

    $info .= "<p>";
    $info .= "About to run title/t4m updates for post_type: $post_type (batch size: $num_posts)."; //, meta_key: $meta_key
    if ( $post_id ) { $info .= "<br />post_id specified: $post_id."; }
    $info .= "</p>";

    // Update in batches to fix title/t4m fields.
    $wp_args = array(
		'post_type'   => $post_type,
		'post_status' => 'publish'
    );

    if ( $post_id ) {

        // If ID has been specified, get that specific single post
        $wp_args['p'] = $post_id;

    } else {

        // Otherwise, get posts not updated recently

        $wp_args['orderby'] = 'post_title';
        $wp_args['order'] = 'ASC';
        $wp_args['posts_per_page'] = $num_posts;
        $wp_args['date_query'] = array(
            /*array(
                'column' => 'post_date_gmt',
                'before' => '1 year ago',
            ),*/
            array(
                'column' => 'post_modified_gmt',
                $date_query  => $date_str,
            ),
        );

        if ( $post_type == "repertoire" || $post_type == "edition" ) {

            if ( $taxonomy && $tax_terms ) {

                $wp_args['tax_query'] = array(
                    array(
                        'taxonomy'  => $taxonomy,
                        'field'     => 'slug',
                        'terms'     => $tax_terms,
                        'operator'  => $tax_operator,
                    )
                );

            } else if ( $meta_key && $meta_value ) {

                $wp_args['meta_query'] = array(
                    array(
                        'key'     => $meta_key,
                        'value'   => $meta_value,
                        'compare' => '=',
                        //'compare' => 'EXISTS',
                    )
                );

            } else if ( $post_type == "repertoire" ) {

                $wp_args['meta_query'] = array(
                    array(
                        'key'     => 'title_clean',
                        'compare' => '!=',
                        'value'   => '',
                    )
                );

            }

        }

    }
        /*'tax_query' => array(
            //'relation' => 'OR', //tft
            array(
                'taxonomy' => 'admin_tag',
                'field'    => 'slug',
                'terms'    => array( 'programmatically-updated' ),
                //'terms'   => 'programmatically-updated',
                'operator' => 'NOT IN',
            ),
            array(
                'taxonomy' => 'admin_tag',
                'field'    => 'slug',
                'terms'    => 't4m-needs-attention',
                //'operator' => 'NOT IN',
            ),
        )*/


    //$info .= 'wp_args: <pre>'.print_r($wp_args, true).'</pre>';
    $arr_posts = new WP_Query( $wp_args );
    //$posts = $arr_posts->posts;
    $info .= "Found ".count($arr_posts->posts)." posts.<br /><br />";
    if ( count($arr_posts->posts) == 0 ) {
        $info .= "<pre>Last SQL-Query (query): {$arr_posts->request}</pre>";
    }

	if ( $arr_posts->have_posts() ) {
		while ( $arr_posts->have_posts() ) {

			$arr_posts->the_post();

            $post_id = get_the_ID();

            // init
            $update_title = false;
            $update_t4m = false;
            $cpt_info = "";

            //https://dev.saintthomaschurch.org/wp-admin/post.php?post=178180&action=edit&post_type=repertoire&classic-editor
            //$edit_url = "/wp-admin/post.php?post=$post_id&action=edit&post_type=$post_type&classic-editor";
            $edit_url = "/wp-admin/post.php?post=$post_id&action=edit&classic-editor";
            if ( $post_type != "post" ) {
            	$edit_url .= "&post_type=$post_type";
            }
            $edit_link = '<a href="'.$edit_url.'" target="_new">'.$post_id.'</a>&nbsp;';
            $old_t4m = get_post_meta( $post_id, 'title_for_matching', true );
            $old_title = get_post_field( 'post_title', $post_id, 'raw' ); //get_the_title($post_id);

            if ( $post_type == "repertoire" ) {

                // Get info for X-check
                $title_clean = get_post_meta( $post_id, 'title_clean', true );
                $cpt_info .= '-- title_clean: '.$title_clean.'<br />';
                $rep_categories = wp_get_post_terms( $post_id, 'repertoire_category', array( 'fields' => 'names' ) );
                $cpt_info .= '-- rep_categories: '.implode(", ",$rep_categories).'<br />';

                $title_clean = get_field('title_clean', $post_id);
                $first_line = get_field('first_line', $post_id);
                $composer = get_field('composer', $post_id);

                // Check to see if title_clean field is empty >> fill in with first_line, if available
                if ( empty($title_clean) && !empty($first_line) ) {
                    $cpt_info .= '-- title_clean not set >> fill in first_line<br />';
                    update_field('title_clean', $first_line, $post_id); // update_field($selector, $value, $post_id);
                }

                // Check to see if composer field is empty >> add 'Unknown'
                if ( empty($composer) ) {
                    $cpt_info .= '-- composer not set >> save as "Unknown"<br />';
                    $unknown_id = "60511";
                    update_field('composer', $unknown_id, $post_id);
                }

            }

            $new_title = build_the_title( $post_id, 'title_for_matching', null ); //
            $new_t4m = get_title_uid( $post_id, $post_type, $new_title ); // ( $post_id = null, $post_type = null, $post_title = null, $uid_field = 'title_for_matching' )

            sdg_log( "[run_title_updates] old_title: ".$old_title, $do_log );
            sdg_log( "[run_title_updates] old_t4m: ".$old_t4m, $do_log );
            sdg_log( "[run_title_updates] new_title: ".$new_title, $do_log );
            sdg_log( "[run_title_updates] new_t4m: ".$new_t4m, $do_log );

            if ( strpos($new_title, 'Error! Abort!') !== false || strpos($new_title, 'Problem!') !== false ) {

                $info .= $new_title.'<br />';

            } else if ( strpos($new_title, 'Array') !== false ) {

                $info .= 'uh oh! new title contains "Array"<br />';

            } else {

                $info .= '<pre>';
                $info .= 'post_id: '.$edit_link;
                $update_info = "";

                if ( $cpt_info != "" && $single_post_id) { $info .= '<br />'.$cpt_info; }

                if ( $new_title != "" && $new_title != $old_title ) {
                    $update_title = true;
                    $update_info .= 'old_title: '.$old_title.'<br />';
                    $update_info .= 'new_title: '.$new_title.'<br />';
                    $update_info .= '>> New post_title<br />';
                } else {
                    $info .= "<!-- old_title: ".$old_title." (unchanged) -->";
                }

                if ( $new_t4m != "" && $new_t4m != $old_t4m ) {
                    $update_t4m = true;
                    $update_info .= 'old_t4m: '.$old_t4m.'<br />';
                    $update_info .= 'new_t4m: '.$new_t4m.'<br />';
                    $update_info .= '>> New t4m<br />';
                    // Run t4m update
                    if ( update_post_meta( $post_id, 'title_for_matching', $new_t4m ) === true ) {
                        $update_info .= "success!<br />";
                        $update_info .= sdg_add_post_term( $post_id, array('t4m-updated', 'programmatically-updated'), 'admin_tag', true );
                    } else {
                        $update_info .= "hmmm...";
                    }
                } else {
                    $info .= "<!-- old_t4m: ".$old_t4m." (unchanged) -->";
                }

                if ( $update_title == false && $update_t4m == false ) {
                    if ( $cpt_info != "" && $single_post_id) { $info .= ''; } else { $info .= '&nbsp;&nbsp;'; }
                    $info .= '>> No changes.<br />'; //(but update anyway for mod date)
                    if ( $verbose == 'true' ) {
                        $info .= 'title: '.$old_title.'<br />';
                        $info .= 't4m: '.$old_t4m.'<br />';
                    }
                } else {
                    $info .= '<br />'.$update_info;
                    $info .= '>> Update the post<br />';
                }

                $info .= '</pre>'; // <br />

                // If either the title or t4m needs to change, update the post. If this is a rep item, the save_post callback will be triggered to update the title and t4m as needed.
                // On second thoughts, run the update no matter what, so that the modification date is updated and we can move on to other records for batch processing.
                wp_update_post( array( 'ID' => $post_id ) );//if ( $update_title == true || $update_t4m == true ) {}

            }

        }

    }

    $info .= 'wp_args: <pre>'.print_r($wp_args, true).'</pre>';

    return $info;

}


// Function(s) to clean up titles/slugs/UIDs
///if ( is_dev_site() ) { add_shortcode('run_posts_cleanup', 'posts_cleanup'); }
//add_shortcode('run_posts_cleanup', 'posts_cleanup'); // tmp disabled on live site while troubleshooting EM issues.
function posts_cleanup( $atts = array() )
{

	$info = ""; // init
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;";

	$args = shortcode_atts( array(
        'testing' => true,
        'post_type' => 'event',
        'num_posts' => 10,
        'admin_tag_slug' => 'slug-updated', // 'uid-updated'; 'programmatically-updated'
        'orderby' => 'rand',
        'order' => null,
        'meta_key' => null
    ), $atts );

    // Extract
	extract( $args );

	$wp_args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
        'posts_per_page' => $num_posts,
        'tax_query' => array(
            'relation' => 'AND', //tft
            array(
                'taxonomy' => 'admin_tag',
                'field'    => 'slug',
                'terms'    => array( $admin_tag_slug ),
                'operator' => 'NOT IN',
            ),
            /*
            array(
                'taxonomy' => 'event-categories',
                'field'    => 'slug',
                'terms'    => 'choral-services',//'terms'    => 'worship-services',

            )*/
        ),
        /*'meta_query' => array(
            //'relation' => 'AND',
            array(
                'key'	  => "legacy_event_id",
                'compare' => 'NOT EXISTS',
            )
        ),*/
        'orderby'	=> $orderby,
	);

    if ( $order !== null ) {
        $wp_args['order'] = $order;
    }

    if ( $meta_key !== null ) {
        $wp_args['meta_key'] = $meta_key;
    }


	$arr_posts = new WP_Query( $wp_args );
    $posts = $arr_posts->posts;

    $info .= "testing: ".$testing."<br /><br />";
    $info .= "<!-- wp_args: <pre>".print_r( $wp_args, true )."</pre> -->";
    //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre><br />"; // tft
    $info .= "[num posts: ".count($posts)."]<br /><br />";

    foreach ( $posts AS $post ) {

        $changes_made = false;

        setup_postdata( $post );
        $post_id = $post->ID;
        $slug = $post->post_name;
        //$post_id = $post->ID;
        $info .= '<span class="label">post_id: </span>'.$post_id.'<br />';

        if ( $post->post_type == 'event' ) {

            $new_slug_info = clean_slug( $post_id );
            $new_slug = $new_slug_info['new_slug'];

            // TODO: check to see if new slug is unique! If not, append a digit to make it so...

            //wp_unique_post_slug( string $slug, int $post_ID, string $post_status, string $post_type, int $post_parent )
            //Computes a unique slug for the post, when given the desired slug and some post details.

            //$new_slug = wp_unique_post_slug( string $slug, int $post_ID, string $post_status, string $post_type, int $post_parent );

            // Check to see if new_slug is in fact unique to this post
            /*$slug_posts = meta_value_exists( $post_type, $post_id, $uid_field, $new_t4m ); //meta_value_exists( $post_type, $post_id, 'title_for_matching', $new_t4m );
            if ( $t4m_posts && $t4m_posts > 0 ) { // meta_value_exists( $post_type, $meta_key, $meta_value )

                // not unique! fix it...
                sdg_log( "[posts_cleanup] new_t4m not unique! Fix it.", $do_log );

                if ( $old_t4m != $new_t4m ) {
                    $i = $t4m_posts+1;
                } else {
                    $i = $t4m_posts;
                }
                $new_t4m = $new_t4m."-v".$i;

            }*/

            $info .= $new_slug_info['info'];
            $info .= '<span class="label">>>> new_slug: </span>'.$new_slug.'<br />';

            //$changes_made = true; // tmp disabled until unique-check is active and tested
        }

        if ( $changes_made == true ) {

            // Update the post
            $update_args = array(
                'ID'           => $post_id,
                'post_name'    => $new_slug,
            );

            //$info .= $indent."update_args: <pre>".print_r( $update_args, true )."</pre>"; // tft

            // Update the post into the database
            // TODO fix/check clean_slug fcn and then set this live for both sites again.
            if ( is_dev_site() ) {
            //if ( $testing == 'false' ) {
                wp_update_post( $update_args, true );
                if ( is_wp_error($post_id) ) {

                    /*
                    // TMP disabled to see if this resolved errors in server log ("...Object of class WP_Error could not be converted to string...")
                    $errors = $post_id->get_error_messages();
                    foreach ($errors as $error) {
                        $info .= $error;
                    }*/

                } else {
                    // If update was successful, add admin tag to note that slug has been updated
                    $info .= sdg_add_post_term( $post_id, 'slug-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
                }
            }

        } else {
            $info .= $indent."No changes made.<br />";
        }

        $info .= "<br />";

    } // END foreach post

    return $info;

}


// TODO: clean up or eliminate the following shortcode and function -- see sdg_save_post_callback
// Shortcode currently in use on dev site event pages
///if ( is_dev_site() ) { add_shortcode('run_post_updates', 'run_post_updates'); }
//add_shortcode('run_post_updates', 'run_post_updates');
function run_post_updates( $atts = array() )
{

	$args = shortcode_atts( array(
        'post_id' => get_the_ID()
    ), $atts );

    // Extract
	extract( $args );

	$info = ""; // init
    $admin_terms = array();
    if ( is_dev_site() ) { $admin_terms[] = 1960; } else { $admin_terms[] = 2203; } // Admin tag: slug-updated
    $update_args = array( 'ID' => $post_id);
    $changes_made = false;

    //if ($post_id === null) { $post_id = get_the_ID(); }
    $info .= "<!-- sdg_run_post_updates -- post_id: $post_id -->";

    // verify post is not a revision
    if ( ! wp_is_post_revision( $post_id ) ) {

        $post = get_post( $post_id );

        // Is this a recurring event?
        $recurrence_id = get_post_meta( $post_id, '_recurrence_id', true );

        // If this is an EVENT post, and not a recurrence, then run the updates.
        if ( $post->post_type == 'event' && ! $recurrence_id ) {

            // ** update_title_for_matching ** /
            // If title_for_matching has NOT already been updated, then get a new and better title_for_matching and update the post accordingly.
            if ( ! has_term( 't4m-updated', 'admin_tag', $post_id ) ) { // change tag to t4m-updated? formerly uid-updated
                // Get the title_for_matching. If none is set, or if it's empty, run the update function.
                $title_for_matching = get_post_meta( $post_id, 'title_for_matching', true );
                if ( ! $title_for_matching || $title_for_matching == "" ) {

                    $t4m_info = update_title_for_matching( $post_id ); // sdg custom function
                    $new_t4m = $t4m_info['title_for_matching'];
                    $info .= $t4m_info['info'];

                    if ( $new_t4m ) {
                        $info .= "<!-- new_t4m: $new_t4m -->";
                    }  else {
                        $info .= "<!-- no new_t4m  -->";
                    }

                } else {

                    // TODO: decide what to do if the title_for_matching is already set but not the post is not tagged as uid-updated -- check to see if current uid needs improvement?
                    $info .= "<!-- new_t4m already in DB; no update required: $title_for_matching -->";
                    //$info .= sdg_add_post_term( $post_id, 't4m-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info

                }
            } else {
                $info .= "<!-- post has_term 't4m-updated' -->";
            }

            // ** slug ** If slug has NOT already been updated, then get a new and better slug and update the post accordingly.
            //if ( ! has_term( 'slug-updated', 'admin_tag', $post_id ) ) {

                // Get the slug; update it if necessary
                $slug = $post->post_name;
                $new_slug_info = clean_slug( $post_id );
                $new_slug = $new_slug_info['new_slug'];

                // TODO: check to see if new slug is unique! If not, append a digit to make it so...

                $info .= $new_slug_info['info'];
                //$info .= "<!-- ".$new_slug_info['info']." -->";

                if ( $new_slug ) {

                    if ( $new_slug != $slug ) {
                        $info .= "<!-- new_slug: $new_slug -->";
                        //$update_args['post_name'] = $new_slug; // tmp disabled until unique-check is active
                        //$changes_made = true; // tmp disabled
                    } else {
                        $info .= "<!-- new_slug same as old slug. -->";
                        //$info .= "<!-- new_slug same as old slug. Update admin_tags only. -->";
                        //$info .= sdg_add_post_term( $post_id, 'slug-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
                    }

                }  else {
                    $info .= "<!-- no new_slug  -->";
                }

            //} else {
            //    $info .= "<!-- post does not qualify for slug update. -->";
            //}

            // If changes have been made, then update the post
            if ( $changes_made == true ) { // && $testing == 'false'

                wp_update_post( $update_args, true );

                if ( ! is_wp_error($post_id) ) {
                    $info .= "<!-- post updated -->";
                    // If update was successful, add admin tag to note that slug has been updated
                    $info .= sdg_add_post_term( $post_id, 'slug-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
                } else {
                    $info .= "<!-- post updated FAILED -->";
                }
            }

        } else { // if is EVENT --> not event
            $info .= "<!-- post_type: $post->post_type; recurrence_id: $recurrence_id  -->";
        }

    }

    if ( devmode_active( array("sdg", "updates") ) ) {
        $info = str_replace('<!-- ','<code>',$info);
        $info = str_replace(' -->','</code><br />',$info);
        $info = str_replace("\n",'<br />',$info);
    }

    return $info;
}


/*** ACF Related Events ***/
///add_filter('acf/fields/relationship/result/name=related_event', 'my_acf_fields_relationship_result', 10, 4);
function my_acf_fields_relationship_result( $text, $post, $field, $post_id )
{
    $text = $post->post_name;
    //$text .= ' [' . $post->post_name .  ']';
    return $text;
}

//add_filter('acf/fields/relationship/query/name=related_event', 'my_acf_fields_relationship_query', 10, 3);
///add_filter('acf/fields/relationship/query', 'my_acf_fields_relationship_query', 10, 3);
function my_acf_fields_relationship_query( $args, $field, $post_id )
{

    // TODO: check to see if args['post_type'] is event
    if ( $args['post_type'] == 'event' ) {
        $args['orderby'] = 'meta_value';
        $args['order'] = 'DESC';
        $args['meta_key'] = '_event_start_date';
    }

    return $args;
}

/**
 * Add post slugs to admin search for a specific post type
 *
 * Rebuilds the search clauses to include post slugs.
 *
 * https://gist.github.com/jjeaton/41eedccdd5256cf756ec
 *
 * @param  string $search
 * @param  WP_Query $query
 * @return string
 */
// WIP 251229
///add_filter( 'posts_search', 'sdg_include_slug_in_search', 10, 2 );
function sdg_include_slug_in_search( $search, $query )
{

	global $wpdb;

	// Only run if we're in the admin and searching our specific post type
	if ( $query->is_search() && $query->is_admin && 'event' == $query->query_vars['post_type'] ) {
		$search = ''; // We will rebuild the entire clause
		$searchand = '';
		if ( isset($query->query_vars) && isset($query->query_vars['search_terms']) ) {
			foreach ( $query->query_vars['search_terms'] as $term ) {
				$like = '%' . $wpdb->esc_like( $term ) . '%';
				$search .= $wpdb->prepare( "{$searchand}(($wpdb->posts.post_title LIKE %s) OR ($wpdb->posts.post_content LIKE %s) OR ($wpdb->posts.post_name LIKE %s))", $like, $like, $like );
				$searchand = ' AND ';
			}
		}
		//
		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
                $search .= " AND ($wpdb->posts.post_password = '') ";
            }
		}
	}

	return $search;

}


/***************************** common_functions ********************************/

/*********** MISC METHODS ***********/

function sdg_log( $log_msg, $do_log = true )
{

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

function sdg_post_title ( $args = array() )
{

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
function sdg_format_title ( $str = null, $line_breaks = false )
{

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

function sort_post_ids_by_title ( $arr_ids = array() )
{

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

function display_postmeta( $args = array() )
{

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
function sdg_scope_dates( $scope = null )
{

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

///add_shortcode('sdg_search_form', 'sdg_search_form');
function sdg_search_form ( $atts = array(), $content = null, $tag = '' )
{

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
///add_shortcode('sdg_search_form_v2', 'sdg_search_form_v2');
function sdg_search_form_v2 ( $atts = array(), $content = null, $tag = '' )
{

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
function decode_ninja_forms_display_form_settings( $settings, $form_id )
{
    $settings[ 'fieldsMarkedRequired' ] = html_entity_decode( $settings[ 'fieldsMarkedRequired' ] );
    return $settings;
}
///add_filter( 'ninja_forms_display_form_settings', 'decode_ninja_forms_display_form_settings', 10, 2 );

