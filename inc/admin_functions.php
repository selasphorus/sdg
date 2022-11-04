<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/* **************************************************************************************************** */

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
function get_title_uid ( $post_id = null, $post_type = null, $tmp_title = null, $uid_field = 'title_for_matching' ) {
    
    sdg_log( "divline2" );
    sdg_log( "function called: get_title_uid" );
    
    sdg_log( "[get_title_uid] post_id: ".$post_id."; post_type: ".$post_type );
    sdg_log( "[get_title_uid] tmp_title: ".$tmp_title ); // formerly post_title
    sdg_log( "[get_title_uid] uid_field: ".$uid_field );
    
    if ( $post_id == null && $tmp_title == null ) {
        return null;
    }
    
    $new_t4m = ""; // init 
    
    $tmp_title = build_the_title( $post_id, $uid_field );
    //if ( !$post_title ) { $post_title = build_the_title( $post_id ); }
    if ( !$post_type ) { $post_type = get_post_type( $post_id ); }
    
    // Abort if error
    if ( strpos($tmp_title, 'Error! Abort!') !== false ) {
        return null; // TODO: test!
    }
    
    $old_t4m = get_post_meta( $post_id, $uid_field, true ); //get_post_meta( $post_id, 'title_for_matching', true );
    $new_t4m = super_sanitize_title ( $tmp_title );
    
    //sdg_log( "old_title: ".$post_title );
    sdg_log( "[get_title_uid] old_t4m: ".$old_t4m );    
    
    // Check to see if new_t4m is in fact unique to this post
    $t4m_posts = meta_value_exists( $post_type, $post_id, $uid_field, $new_t4m ); //meta_value_exists( $post_type, $post_id, 'title_for_matching', $new_t4m );
    if ( $t4m_posts && $t4m_posts > 0 ) { // meta_value_exists( $post_type, $meta_key, $meta_value )
        
        // not unique! fix it...
        sdg_log( "[get_title_uid] new_t4m not unique! Fix it.");
        
        if ( $old_t4m != $new_t4m ) {
            $i = $t4m_posts+1;
        } else {
            $i = $t4m_posts;
        }
        $new_t4m = $new_t4m."-v".$i;
        
    }
    if ( $new_t4m == $old_t4m ) {
        sdg_log( "[get_title_uid] new_t4m same as old_t4m." );
    } else {
        sdg_log( "[get_title_uid] new_t4m: ".$new_t4m );
    }
    
    return $new_t4m;
}

function update_title_for_matching ( $post_id ) {
    
    sdg_log( "function: sdg_update_title_for_matching" );
    
    $post = get_post( $post_id );
    $post_title = $post->post_title;
    $post_type = $post->post_type;
    
    $info = ""; // init
    $new_t4m = "";
    $revised = false;
    
    $t4m = get_post_meta( $post_id, 'title_for_matching', true ); // get_post_meta( int $post_id, string $key = '', bool $single = false ) -- Return val will be an array if $single is false. Will be value of the meta field if $single is true.
    $title_clean = get_post_meta( $post_id, 'title_clean', true );
    
    if ( ! $title_clean || $title_clean == "" ) {
        sdg_log( "No title_clean stored in post_meta for this post -> use make_clean_title" );
        $title_clean = make_clean_title( $post_id );
    }
    $info .= "<!-- post_title: $post_title // title_clean: $title_clean // t4m: $t4m -->";
 
    if ( !empty($t4m) ) {
        
        $info .= "<!-- t4m already in DB: $t4m -->";
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
    $info .= "<!-- t4m is empty -> build one -->"; //$info .= '<span class="label">title_for_matching is empty >> build one</span><br />';

    $sanitized_title = super_sanitize_title($title_clean);

    if ( $post_type == 'event' ) {

        $legacy_event_id = get_post_meta( $post_id, 'legacy_event_id', true );
        //$info .= $indent.'<span class="label">legacy_event_id: </span>'.$legacy_event_id.'<br />';

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
            $info .= sdg_add_post_term( $post_id, 't4m-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
        }

    }
    
    if ( $title_clean != $post_title ) {
        // Now that the title_for_matching is confirmed/updated: if the post_title isn't the clean one, update the post_title -- ???
    }
    
    $arr_info['info'] = $info;
    $arr_info['title_for_matching'] = $new_t4m;
    //$arr_info['title_clean'] = $new_title_clean;
    
    return $arr_info;
    // TODO: change to return true/false depending on outcome of add/update?
    
}

// Build proper title for rep/edition records, based on metadata components
// TODO: 
// 1) separate out the title building bit so that it can be used independently of wp_insert_post_data
// 2) bind this with a t4m update function

function build_the_title( $post_id = null, $uid_field = 'title_for_matching', $arr = array(), $abbr = false ) {
    
    sdg_log( "divline2" );
    sdg_log( "function called: build_the_title" );
    
    sdg_log( "[btt] post_id: ".$post_id );
    sdg_log( "[btt] abbr: ".(int)$abbr );
    
    if ( $post_id == null ) { return null; }
    $post_type = get_post_type( $post_id ); //if ( $post_type == null ) { $post_type = get_post_type( $post_id ); }
    
    $old_title = get_post_field( 'post_title', $post_id, 'raw' ); //get_the_title($post_id);
    $old_t4m = get_post_meta( $post_id, $uid_field, true ); //get_post_meta( $post_id, 'title_for_matching', true );
    
    $new_title = ""; // init
    
    $authorship_arr = array(); // init
    //$authorship_arr['post_id'] = $post_id;
    
    // Set var values
    if ( !empty($arr) ) {
        
        sdg_log( "running btt using array derived from _POST." );
        
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
            
            $authorship_info = get_authorship_info( $authorship_arr, 'post_title', $abbr ); //( $data = array(), $format = 'post_title', $abbr = false, $is_single_work = false, $show_title = true ) 
            
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
                sdg_log( "[btt/edition] single musical_work_id found: ".$musical_work_id );
            } else {
                // WIP -- TBD: how to deal w/ case of multiple works associated with one edition
                sdg_log( "[btt/edition] multiple musical_works found: ".print_r($musical_works, true) );
                foreach ( $musical_works AS $musical_work_id ) {
                    //$musical_work_id = 
                }
            }
            
            // TODO: turn this array into a string!
            $choir_forces = $arr['choir_forces'];
            if ( is_array($choir_forces) ) {
                sdg_log( "[btt/edition] no voicings or soloists info >> use choir_forces_str: ".print_r($choir_forces, true) );
                $choir_forces_str = implode(", ",$choir_forces);
                $choir_forces_str = ucwords(str_replace("_"," ",$choir_forces_str));
                $choir_forces_str = str_replace("Men-s","Men's",$choir_forces_str);
                $choir_forces_str = str_replace("Mens","Men's",$choir_forces_str);
            } else {
                $choir_forces_str = "";
            }
            //
            $editors = $arr['editor']; // array of ids
            //sdg_log( "editors: ".print_r($editors, true) );
            $editors_str = str_from_persons_array ( $editors, 'editors', $post_id, $format, $arr_of, $abbr );
            //sdg_log( "editors_str: ".$editors_str );
            sdg_log( "-----");
            
            $publication_id = $arr['publication']; // single id
            //sdg_log( "[btt/arr] publication_id: ".$publication_id ); // tft
            if ( $publication_id ) {
                $publication = get_post_field( 'post_title', $publication_id, 'raw' );
            } else {
                $publication = "";
            }
            //
            $publisher_id = $arr['publisher']; // single id
            //sdg_log( "[btt/arr] publisher_id: ".$publisher_id ); // tft
            if ( $publisher_id ) {
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
            
            // TODO: streamline this string construction process for all the taxonomies
            
            $voicings_str = "";
            if ( is_array($voicings) && count($voicings) > 0 ) {
                //sdg_log( "[btt/arr] voicings: ".print_r($voicings, true) );
                foreach ( $voicings as $voicing_id ) {
                    if ( $voicing_id != 0) {
                        $term = get_term( $voicing_id );
                        if ($term) { 
                            //sdg_log( "[btt] voicing_id: $voicing_id/ term->name: ".$term->name );
                            $voicings_str .= $term->name;
                            if ( count($voicings) > 1 ) {
                                $voicings_str .= ", ";
                            }
                        }
                    }
                }
                if ( count($voicings) > 1 && substr($voicings_str, -2) == ', ' ) {
                    // Trim trailing comma and space
                    $voicings_str = substr($voicings_str, 0, -2);
                }
                //sdg_log( "[btt/arr] voicings_str: ".$voicings_str );
            }
            if ( $voicings_str == "" ) {
                //sdg_log( "[btt/arr] voicings_str is empty." );
                if ( $arr['voicing_txt'] != "" ) {
                    $voicings_str = $arr['voicing_txt'];
                    sdg_log( "[btt/arr] using backup txt field for voicings_str" );
                }
            } else {
                //sdg_log( "[btt/arr] voicings_str: ".$voicings_str );
            }
            
            $soloists_str = "";
            if ( is_array($soloists) && count($soloists) > 0 ) {
                foreach ( $soloists as $soloist_id ) {
                    if ( $soloist_id != 0) {
                        $term = get_term( $soloist_id );
                        if ($term) { 
                            $soloists_str .= $term->name;
                            if ( count($soloists) > 1 ) {
                                $soloists_str .= ", ";
                            }
                        }
                    }
                }
                if ( count($soloists) > 1 && substr($soloists_str, -2) == ', ' ) {
                    // Trim trailing comma and space
                    $soloists_str = substr($soloists_str, 0, -2);
                }
            }
            if ( $soloists_str == "" && $arr['soloists_txt'] != "" ) {
                $soloists_str = $arr['soloists_txt'];
                sdg_log( "[btt/arr] using backup txt field for soloists_str" );
            }
            
            $instruments_str = "";
            if ( is_array($instruments) && count($instruments) > 0 ) {
                foreach ( $instruments as $instrument_id ) {
                    if ( $instrument_id != 0) {
                        $term = get_term( $instrument_id );
                        if ($term) { 
                            $instruments_str .= $term->name;
                            if ( count($instruments) > 1 ) {
                                $instruments_str .= ", ";
                            }
                        }
                    }
                }
                if ( count($instruments) > 1 && substr($instruments_str, -2) == ', ' ) {
                    // Trim trailing comma and space
                    $instruments_str = substr($instruments_str, 0, -2);
                }
            }
            if ( $instruments_str == "" && $arr['instrumentation_txt'] != "" ) {
                $instruments_str = $arr['instrumentation_txt'];
                sdg_log( "[btt/arr] using backup txt field for instruments_str: ".$instruments_str );
            }
            
            
        }
        
        // For both rep & editions, handle key names
        $keys = $arr['keys']; // array of ids
        $keys_str = "";
        if ( is_array($keys) && count($keys) > 0 ) {
            foreach ( $keys as $key_id ) {
                if ( $key_id != 0) {
                    $term = get_term( $key_id );
                    if ($term) { 
                        $keys_str .= $term->name;
                        if ( count($keys) > 1 ) {
                            $keys_str .= ", ";
                        }
                    }
                }
            }
            if ( count($keys) > 1 && substr($keys_str, -2) == ', ' ) {
                // Trim trailing comma and space
                $keys_str = substr($keys_str, 0, -2);
            }
        }
        if ( $keys_str == "" && $arr['key_name_txt'] != "" ) {
            $keys_str = $arr['key_name_txt'];
            sdg_log( "[btt/arr] using backup txt field for keys_str" );
        }
        
    } else if ( $post_id ) {
        
        // If no array of data was submitted, get info via the post_id
        
        sdg_log( "running btt based on post_id." );
        
        $authorship_arr['post_id'] = $post_id;
        
        if ( $post_type == 'repertoire' ) {
            
            // Get info via ACF get_field fcn rather than get_post_meta -- because the latter may return: Array( [0] => )
            
            $title_clean = get_field('title_clean', $post_id);
            $excerpted_from = get_field('excerpted_from', $post_id);
            $catalog_num = get_field('catalog_number', $post_id);
            $opus_num = get_field('opus_number', $post_id);

            // composer(s), arranger(s), transcriber:
            $authorship_info = get_authorship_info( $authorship_arr, 'post_title', $abbr ); //( $data = array(), $format = 'post_title', $abbr = false, $is_single_work = false, $show_title = true )

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
            $publisher = get_field('publisher', $post_id);
            $publication = get_field('publication', $post_id);
            $publication_date = get_field('publication_date', $post_id);
            $box_num = get_field('box_num', $post_id);
            $old_t4m = get_field('title_for_matching', $post_id);
            $choir_forces_str = get_field('choir_forces', $post_id);       
            $voicing_txt = get_field('voicing_txt', $post_id);
            $soloists_txt = get_field('soloists_txt', $post_id);
            $instrumentation_txt = get_field('instrumentation_txt', $post_id);
            
        }
    }
    
    // Taxonomies
    if ( empty($arr) && $post_id && ( $post_type == 'repertoire' || $post_type == 'edition' ) ) {

        sdg_log( "[btt] get taxonomy info from post_id: ".$post_id ); // tft
        
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
        sdg_log( "[btt/taxonomies] keys_str: ".$keys_str ); // tft
        
        if ( $post_type == 'repertoire' ) {
            $rep_categories = wp_get_post_terms( $post_id, 'repertoire_category', array( 'fields' => 'ids' ) );
        }
        
        if ( $post_type == 'edition' ) {

            // Get term names for "voicing".
            $voicings = wp_get_post_terms( $post_id, 'voicing', array( 'fields' => 'names' ) );
            if ( $voicings ) { $voicings_str = implode(", ", $voicings); } else { $voicings_str = ""; }
            sdg_log( "[btt/taxonomies] voicings_str: ".$voicings_str ); // tft

            // Get term names for "soloist".
            $soloists = wp_get_post_terms( $post_id, 'soloist', array( 'fields' => 'names' ) );
            $soloists_str = implode(", ", $soloists);

            // Get term names for "instrument".
            $instruments = wp_get_post_terms( $post_id, 'instrument', array( 'fields' => 'names' ) );
            $instruments_str = implode(", ", $instruments);
        }
        
    }
    
    // Build the title
    if ( $post_type == 'repertoire' ) {
        
        // Hymns:
        // CATALOG_NUM -- FIRST_LINE -- TUNE_NAME
        
        // Not Hymns:
        // TITLE_CLEAN, from EXCERPTED_FROM, CATALOG_NUMBER, OPUS_NUMBER -- COMPOSER (COMPOSER DATES) *OR* (ANON_INFO) / arr. ARRANGER / transcr. TRANSCRIBER -- in KEY_NAME
        // In case of Psalms, prepend "Psalm"/"Psalms" as needed
        
        $hymn_cat_id = "1452"; // "Hymns" -- same id on live and dev
        $psalm_cat_id = "1461"; // "Psalms" -- same id on live and dev
        $chant_cat_id = "1528"; // "Anglican Chant" -- same id on live and dev
            
        if ( in_array($hymn_cat_id, $rep_categories) ) {

            // Hymns
            
            sdg_log( "[btt] This is a hymn." ); // tft
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
            sdg_log( "[btt] musical_work_id: ".$musical_work_id );
            
            $musical_work_title = get_the_title($musical_work_id);
            //$musical_work_title = get_field('post_title', $musical_work_id);
            
            // WIP: Get abbreviated version of work title w/ lastnames only
            
            // MW Authorship
            
            // A. Short version
            $rep_authorship_short = get_authorship_info( array( 'post_id' => $musical_work_id ), 'edition_title', true ); // abbr
            //sdg_log( "[btt/edition] rep_authorship_short: ".$rep_authorship_short ); // tft
            // ltrim punctuation so as to avoid failed replacement if work, e.g., has no arranger but no composer listed -- TODO: integrate this into get_authorship_info fcn?
            $rep_authorship_short = ltrim( $rep_authorship_short, ', ' );
            $rep_authorship_short = ltrim( $rep_authorship_short, '-- ' );
            $rep_authorship_short = trim( $rep_authorship_short, '()' );
            sdg_log( "[btt/edition] rep_authorship_short: ".$rep_authorship_short ); // tft
            
            // B. Long version
            $rep_authorship_long = get_authorship_info( array( 'post_id' => $musical_work_id ), 'edition_title', false ); // not abbr
            //sdg_log( "[btt/edition] rep_authorship_long: ".$rep_authorship_long ); // tft
            // remove punctuation for purposes of string replacement
            $rep_authorship_long = ltrim( $rep_authorship_long, ', ' );
            $rep_authorship_long = ltrim( $rep_authorship_long, '-- ' );
            // authorship info will have been put in parens for psalms -- remove them for purposes of string replacement
            $rep_authorship_long = ltrim( $rep_authorship_long, '(' ); // left enclosing paren
            $rep_authorship_long = str_replace( "))", ")", $rep_authorship_long ); // get rid of final left enclosing paren but not closing paren for author dates
            
            sdg_log( "[btt/edition] rep_authorship_long: ".$rep_authorship_long ); // tft
            sdg_log( "[btt/edition] musical_work_title: ".$musical_work_title ); // tft
            
            // Compare short/long authorship; replace as needed
            if ( $rep_authorship_long != $rep_authorship_short ) {
                sdg_log( "[btt/edition] replace long with short authorship info." ); // tft
                //htmlspecialchars_decode()
                //html_entity_decode()
                // TODO: figure out why this doesn't always work as expected -- sometimes no replacement is made despite the availability of valid strings.
                $count = 0;
                $musical_work = str_replace( $rep_authorship_long, $rep_authorship_short, $musical_work_title, $count );
                sdg_log( "[btt/edition] num replacements made: ".$count." (Replace [".$rep_authorship_long."] with [".$rep_authorship_short."] in [".$musical_work_title."] )" ); // tft
                sdg_log( "[btt/edition] revised musical_work_title: ".$musical_work ); // tft
            } else {
                sdg_log( "[btt/edition] rep_authorship_long same as rep_authorship_short." ); // tft
                $musical_work = $musical_work_title;
            }
            $new_title .= $musical_work;
            //sdg_log( "[btt/edition] revised musical_work_title: ".$musical_work ); // tft
            //sdg_log( "[btt/edition] new_title (after adding musical_work info): ".$new_title );
        } else {
            sdg_log( "[btt/edition] Abort! No musical_work found upon which to build the title." );
            return "<pre>*** Error! Abort! No musical_work found upon which to build the title for Edition with post_id: $post_id. ***</pre>";
            //return null; // no musical work for building edition title.
        }
        
        // 2. Key(s)
        if ( $keys_str != "" && $uid_field == 'title_for_matching' ) { $new_title .= " -- in ".$keys_str; }
        
        // Publication info
        
        // 3. Editor
        if ( $editors_str != "" ) { $new_title .= " / ed. ".$editors_str; }
        //sdg_log( "[btt/edition] new_title (after editors_str): ".$new_title );
        
        // 4. Publication
        if ( $publication != "" ) { $new_title .= " / ".$publication; }
        //sdg_log( "[btt/edition] new_title (after publication): ".$new_title );
        
        // 5. Publisher
        if ( $publisher != "" ) { $new_title .= " / ".$publisher; }
        //sdg_log( "[btt/edition] new_title (after publisher): ".$new_title );
        
        // 6. Voicings
        //sdg_log( "[btt/edition] voicings_str: ".$voicings_str ); // tft
        if ( $voicings_str == "" ) { 
            //sdg_log( "[btt/edition] voicings_str is empty >> try voicing_txt." ); // tft
            if ( $voicing_txt != "" ) {
                $voicings_str = $voicing_txt;
                //sdg_log( "[btt/edition] voicings_str from voicing_txt: ".$voicings_str ); // tft
            } else {
                //sdg_log( "[btt/edition] no luck w/ voicing_txt >> voicings_str still empty: [".$voicings_str."]" ); // tft
            }
        }
        if ( $voicings_str !== "" && $uid_field == 'title_for_matching' ) { 
            //sdg_log( "[btt/edition] add voicings_str to new_title." ); // tft
            $new_title .= " / for ".$voicings_str;
        }
        //sdg_log( "[btt/edition] new_title (after voicings_str): ".$new_title );
        
        // (if no voicing, but yes soloists, then "for soloists")
        // multiple voicings &c. -- connect w/ ampersand?
        
        // 7. Soloists
        //sdg_log( "[btt/edition] soloists_str: ".$soloists_str ); // tft
        if ( $soloists_str == "" && $soloists_txt != "" ) { $soloists_str = $soloists_txt; }
        if ( $soloists_str != "" && $uid_field == 'title_for_matching' ) { 
            if ( $voicings_str != "" ) { 
                $new_title .= " + ".$soloists_str;
            } else {
                $new_title .= " / for ".$soloists_str;
            }
        }
        //sdg_log( "[btt/edition] new_title (after soloists_str): ".$new_title );
        
        // Choir Forces -- if no Voicings or Soloists
        if ( $choir_forces_str != "" & $voicings_str == "" && $soloists_str == "" && $uid_field == 'title_for_matching' ) {
            sdg_log( "[btt/edition] no voicings or soloists info >> use choir_forces_str: ".$choir_forces_str );
            $new_title .= " / for ".$choir_forces_str;
        } else {
            sdg_log( "[btt/edition] choir_forces: ".$choir_forces_str );
        }
        
        // 8. Instrumentation
        //sdg_log( "[btt/edition] instruments_str: ".$instruments_str ); // tft
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
            }
        }
        //sdg_log( "[btt/edition] new_title (after instruments_str): ".$new_title );
        
        // 9. Box num
        if ( $box_num != "" ) { $new_title .= " [in ".$box_num."]"; }
        //sdg_log( "[btt/edition] new_title (after box_num): ".$new_title );
        
    }
    
    sdg_log( "[btt] new_title (final): ".$new_title );
    
    return $new_title;
    
}

// https://developer.wordpress.org/reference/hooks/updated_(meta_type)_meta/
// do_action( "updated_{$meta_type}_meta", int $meta_id, int $object_id, string $meta_key, mixed $_meta_value )
// do_action( "updated_post_meta", int $meta_id, int $object_id, string $meta_key, mixed $_meta_value )
// Fires immediately after updating metadata of a specific type.


add_filter('wp_insert_post_data', 'sdg_build_the_title_on_insert', 10, 2);
function build_the_title_on_insert( $data, $postarr ) {
    
    sdg_log( "divline1" );
    sdg_log( "filter: wp_insert_post_data" );
    sdg_log( "function called: build_the_title_on_insert" );
    
    // $data: (array) An array of slashed, sanitized, and processed post data.
    // $postarr: (array) An array of sanitized (and slashed) but otherwise unmodified post data.
    
    $post_id = $postarr['ID'];
    $post_type = $data['post_type'];
    $new_title = null;
    
    sdg_log( "post_id: ".$post_id."; post_type: ".$post_type );
    
    if ( $post_id == 0 || ( $post_type != 'repertoire' && $post_type != 'edition' ) ) {
        return $data;
    }
    
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
    
    //sdg_log( "data: ".print_r($data, true) ); // tft
    //sdg_log( "postarr: ".print_r($postarr, true) ); // tft
    //sdg_log( "_POST array: ".print_r($_POST, true) ); // tft
    
    if ( $post_type == 'repertoire' && isset($_POST['acf']['field_624615e7eca6f']) ) {
        
        sdg_log( "build the array of repertoire _POST data for submission to fcn build_the_title." );
        
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
        
        sdg_log( "build the array of edition _POST data for submission to fcn build_the_title." );
        
        //sdg_log( "_POST array: ".print_r($_POST, true) ); // tft
        
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
        
        sdg_log( "Insufficient data for building the array of _POST data for submission to fcn build_the_title." );
        
    }
    
    if ( $new_title ) {
        
        // Abort if error
        if ( strpos($new_title, 'Error! Abort!') !== false ) {
            
            sdg_log( $new_title );
            
        } else if ( $new_title == $data['post_title'] ) {
            
            sdg_log( "new_title same as old_title.");
            
        } else {

            sdg_log( "old_title: ".$data['post_title'] );
            sdg_log( "new_title: ".$new_title );

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
function sdg_remove_bracketed_info ( $str ) {

	//sdg_log( "function: remove_bracketed_info" );

	if (strpos($str, '[') !== false) { 
		$str = preg_replace('/\[[^\]]*\]([^\]]*)/', trim('$1'), $str);
		$str = preg_replace('/([^\]]*)\[[^\]]*\]/', trim('$1'), $str);
	}

	return $str;
}

// Function: clean up titles for creation of slugs and for front-end display
add_filter( 'the_title', 'sdg_filter_the_title', 100, 2 );
function filter_the_title( $post_title, $post_id = null ) {
 
    //sdg_log( "function: filter_the_title" );
    $post_type = ""; // init
    
    if ( !is_admin() ) {
        
        if ( $post_id ) {
            $post = get_post( $post_id );
            $post_type = $post->post_type;
        }
        
        $return_revised = true;
        $post_title = make_clean_title( $post_id, $post_title, $return_revised );
        //if ( $post_type != 'event' ) { $post_title = make_clean_title( $post_id, $post_title, $return_revised ); } // tft
        
        /*
        // WIP!
        $event_series = get_post_meta( $post_id, 'event_series', true );
        if ( isset($event_series['ID']) ) {
            $series_id = $event_series['ID'];
            $prepend_series_title = get_post_meta( $series_id, 'prepend_series_title', true );
            if ( $prepend_series_title == 1 ) { $series_title = get_the_title( $series_id ); }
            // Prepend series_title, if applicable
            if ( $series_title != "" ) { $post_title = $series_title.": ".$post_title; }
        }
        */
 
    }   
    
    return $post_title;
}

// TODO/WIP: Troubleshoot
function make_clean_title( $post_id = null, $post_title = null, $return_revised = true ) {
	
    sdg_log( "divline1" );
    sdg_log( "function: make_clean_title");
    
    //if ( $post_id === null ) { $post_id = get_the_ID(); }
    
    $clean_title = null; // init
    $post_type = null; // init
    
    //if ( $post_id === null ) { return null; }
    if ( $post_id === null && $post_title === null ) { return null; }
    
    if ( $post_id ) {
        $post = get_post( $post_id );
        $post_type = $post->post_type;
        if ( empty($post_title) ) {
            $post_title = $post->post_title;
        }
    }
    
    sdg_log( "post_id: ".$post_id."; post_title: ".$post_title );
    
    if ( $return_revised == true ) {
        sdg_log( "function: make_clean_title >> return_revised" );
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
        
        if ( !empty($clean_title) ) {
			if ( ! add_post_meta( $post_id, 'title_clean', $clean_title, true ) ) {
				update_post_meta ( $post_id, 'title_clean', $clean_title );
			}
		}
        
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
			if (strpos($clean_title, 'Eucharist:') !== false) { 
				$clean_title = preg_replace('/(.*) Eucharist: (.*)/', '$1 Eucharist', $clean_title);
			}
            
        }
		
		//$clean_title = str_replace("--", "&mdash;", $clean_title);
		
	}
	
	// If no clean_title has been made, default to the original post_title
	if ( empty($clean_title) ) {
		$clean_title = $post_title;
	}
    
	if ( !is_admin() ) { // || $return_revised == true
    	$clean_title = remove_bracketed_info($clean_title);
    }
	
	return $clean_title;
	
}

// TODO: fix this for event posts being saved for the first time 
// -- in that case, event_date not stored as metadata so must figure out how to retrieve it from the _POST array and pass it to this fcn.
function clean_slug( $post_id ) {
    
    sdg_log( "divline1" );
    sdg_log( "function: clean_slug");
    
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
    //sdg_log( "title_clean (get_post_meta): ".$title_clean);
    if ( empty ($title_clean) || strpos($title_clean, '[') !== false ) { 
        $title_clean = make_clean_title( $post_id );
        //sdg_log( "title_clean (make_clean_title): ".$title_clean);    
    }
    sdg_log( "title_clean: ".$title_clean);

    // If the title_clean doesn't already contain date info, then...
    // ...make a better slug by combining title_clean and event_date

    // Deal w/ posts with date info as part of their titles
    $match_pattern = "~January|February|March|April|May|June|July|August|September|October|November|December~";
    if ( preg_match($match_pattern, $title_clean) ) {
        //$info .= $indent."** title_clean contains date info.<br />";
        $append_date = false;
        sdg_log( "append_date FALSE");
    } else {
        sdg_log( "append_date TRUE");
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
    
    sdg_log( "divline1" );
    sdg_log( "action: save_post" );
    //sdg_log( "action: save_post_event" );
    sdg_log( "function called: sdg_save_post_callback" );
    
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
        sdg_log( "is post revision >> abort." );
        return;
    }
    
    $post_type = $post->post_type;
    //$post_type = get_post_type( $post_id );
    // get custom fields from $_POST var?
 
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
            sdg_log( "is part of recurring event series ($recurrence_id) >> abort." );
            return;
        }

        //$post = get_post( $post_id );

        // TODO: consolidate this w/ run_post_updates function -- too much redundancy!
        //sdg_run_post_updates( $atts = [] )
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
        sdg_log( "old_slug: ".$old_slug);
        sdg_log( "new_slug: ".$new_slug);

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
        
    } else if ( $post_type == "repertoire" || $post_type == "edition" ) { //
        
        // TODO: check to see if this save is happening via WP All Import and if so, skip t4m changes
        
        // Get post obj, post_title, $old_t4m
        $the_post = get_post( $post_id );
        $post_title = $the_post->post_title;
        $old_t4m = get_post_meta( $post_id, 'title_for_matching', true );

        sdg_log( "about to call function: get_title_uid" );
        $new_t4m = get_title_uid( $post_id, $post_type, $post_title );
        
        sdg_log( "divline2" );

        sdg_log( "post_id: ".$post_id );
        sdg_log( "post_type: ".$post_type );
        sdg_log( "post_title: ".$post_title );
        //sdg_log( "old_title: ".$old_title );
        sdg_log( "old_t4m: ".$old_t4m );
        //sdg_log( "new_title: ".$new_title );
        sdg_log( "new_t4m: ".$new_t4m );

        //$update_data = array(); // init

        // If we've got a new post_title and/or new t4m, prep to run the update
        /*if ( ( $new_t4m != "" && $new_t4m != $old_t4m) || ( $new_title != "" && $new_title != $old_title ) ) {
            $update_data['ID'] = $post_id;
        }*/

        if ( !empty($new_t4m) && $new_t4m != $old_t4m && $new_t4m != "test") {

            // TODO: get this to work when called via Update for individual post record (post.php)
            // For the moment, the t4m only gets updates when run via the title_updates function. No idea why.
            sdg_log( ">> update t4m" );
            //$update_data['meta_input'] = array( 'title_for_matching' => $new_t4m );
            $meta_update = update_post_meta( $post_id, 'title_for_matching', $new_t4m );
            sdg_log( "meta_update: ".$meta_update );
            if ( $meta_update === true ) {
                sdg_log("success!");
                //sdg_log(sdg_add_post_term( $post_id, array('t4m-updated', 'programmatically-updated'), 'admin_tag', true ));
            } else {
                //(int|bool) Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
            }
            

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
        
    } // end post_type check

}


/*********** DEV/CLEANUP FUNCTIONS ***********/

// Bulk updates to titles and title_for_matching postmeta values
// This fcn will be used primarily (exclusively?) for repertoire and edition records
add_shortcode('title_updates', 'run_title_updates');
function run_title_updates ($atts = [], $content = null, $tag = '') {
    
    sdg_log( "divline2" );
    sdg_log( "function called: run_title_updates" );
    
    $info = "";
    
    $a = shortcode_atts( array(
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
    
    $post_type = $a['post_type'];
    //$meta_key = $a['meta_key'];
    $single_post_id = $a['post_id'];
    $num_posts = $a['num_posts'];
    $verbose = $a['verbose'];
    
    // tax_query components
    $taxonomy = $a['taxonomy'];
    $tax_terms = $a['tax_terms'];
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
    
    // meta_query components
    $meta_key = $a['meta_key'];
    $meta_value = $a['meta_value'];
    //$meta_values = sdg_att_explode( $meta_values );
    //$meta_values = "array('".implode("','",$meta_values)."')"; // wrap each item in single quotes
    
    // date_query components
    $date_query = $a['date_query'];
    $date_str = $a['date_str'];
    
    $info .= "<p>";
    $info .= "About to run title/t4m updates for post_type: $post_type (batch size: $num_posts)."; //, meta_key: $meta_key
    if ( $single_post_id ) { $info .= "<br />post_id specified: $single_post_id."; }
    $info .= "</p>";
    
    // Update in batches to fix title/t4m fields.
    $args = array(
		'post_type'   => $post_type,
		'post_status' => 'publish'
    );
    
    if ( $single_post_id ) {
        
        // If ID has been specified, get that specific single post
        $args['p'] = $single_post_id;
        
    } else {
        
        // Otherwise, get posts not updated recently
        
        $args['orderby'] = 'post_title';
        $args['order'] = 'ASC';
        $args['posts_per_page'] = $num_posts;
        $args['date_query'] = array(
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
                
                $args['tax_query'] = array(
                    array(
                        'taxonomy'  => $taxonomy,
                        'field'     => 'slug',
                        'terms'     => $tax_terms,
                        'operator'  => $tax_operator,
                    )
                );
                
            } else if ( $meta_key && $meta_value ) {
                
                $args['meta_query'] = array(
                    array(
                        'key'     => $meta_key,
                        'value'   => $meta_value,
                        'compare' => '=',
                        //'compare' => 'EXISTS',
                    )
                );
                
            } else if ( $post_type == "repertoire" ) {
                
                $args['meta_query'] = array(
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

    
    //$info .= 'args1: <pre>'.print_r($args, true).'</pre>';    
    $arr_posts = new WP_Query( $args );
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
            $new_t4m = get_title_uid( $post_id, $post_type, $new_title ); // field: title_for_matching
            //$new_cp_uid = get_title_uid( $post_id, $post_type, $new_title ); // field: cp_import_uid
            
            //sdg_log( "[run_title_updates] old_title: ".$old_title );
            //sdg_log( "[run_title_updates] old_t4m: ".$old_t4m );
            //sdg_log( "[run_title_updates] new_title: ".$new_title );
            //sdg_log( "[run_title_updates] new_t4m: ".$new_t4m );
            
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
                } else { // if ( $single_post_id )
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
                } else { // if ( $single_post_id )
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
    
    $info .= 'args1: <pre>'.print_r($args, true).'</pre>';
    
    return $info;
    
}


// Function(s) to clean up titles/slugs/UIDs
///if ( is_dev_site() ) { add_shortcode('run_posts_cleanup', 'posts_cleanup'); } 
//add_shortcode('run_posts_cleanup', 'posts_cleanup'); // tmp disabled on live site while troubleshooting EM issues.
function posts_cleanup( $atts = [] ) {

	$info = ""; // init
    $indent = "&nbsp;&nbsp;&nbsp;&nbsp;";

	$a = shortcode_atts( array(
        'testing' => true,
        'post_type' => 'event',
        'num_posts' => 10,
        'admin_tag_slug' => 'slug-updated', // 'uid-updated'; 'programmatically-updated'
        'orderby' => 'rand',
        'order' => null,
        'meta_key' => null
    ), $atts );
    
    $num_posts = (int) $a['num_posts'];
    $orderby = $a['orderby'];
    $admin_tag_slug = $a['admin_tag_slug'];
    
	$args = array(
		'post_type' => $a['post_type'],
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
    
    if ( $a['order'] !== null ) {
        $args['order'] = $a['order'];
    }
    
    if ( $a['meta_key'] !== null ) {
        $args['meta_key'] = $a['meta_key'];
    }
    
    
	$arr_posts = new WP_Query( $args );
    $posts = $arr_posts->posts;
    
    $info .= "testing: ".$a['testing']."<br /><br />";
    $info .= "<!-- args: <pre>".print_r( $args, true )."</pre> -->";
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
                sdg_log( "[get_title_uid] new_t4m not unique! Fix it.");

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
            //if ( $a['testing'] == 'false' ) {
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
function run_post_updates( $atts = [] ) {

	$a = shortcode_atts( array(
        'post_id' => get_the_ID()
    ), $atts );
    $post_id = (int) $a['post_id'];
    
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
            if ( $changes_made == true ) { // && $a['testing'] == 'false'
                
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
    
    if ( sdg_devmode_active() ) {
        $info = str_replace('<!-- ','<code>',$info);
        $info = str_replace(' -->','</code><br />',$info);
        $info = str_replace("\n",'<br />',$info);
    }
    
    return $info;
}


/* *** SERMONS *** */

// Sermon updates - add related_event info
add_shortcode( 'run_sermon_updates_fcn', 'sermon_updates' );
function sermon_updates ( $atts = [] ) {

	$info = "";

	$a = shortcode_atts( array(
        'legacy' => false,
        'testing' => true,
        //'id' => null,
        //'name' => null,
        'num_posts' => 10,
        //'header' => 'false',
    ), $atts );
    
    $num_posts = (int) $a['num_posts'];
    //$date_str = esc_attr( $a['date_str'] );
    
    $info = ""; // init
    
    $args = array(
        'post_type' => 'sermon',
        'post_status' => 'publish',
        'posts_per_page' => $num_posts,
        'orderby' => 'ID',
        'order'   => 'ASC'
    );
    
    if ( $a['legacy'] == 'true' ) {
        
        // Legacy Events
        $args['meta_query'] = 
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
        $args['meta_query'] =
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
    
	$arr_posts = new WP_Query( $args );
    $sermon_posts = $arr_posts->posts;
    
    $info .= "args: <pre>".print_r( $args, true )."</pre>";
    //$info .= "Last SQL-Query: <pre>{$arr_posts->request}</pre><br />"; // tft
    //$info .= "arr_posts->posts: <pre>".print_r( $arr_posts->posts, true )."</pre>";
    $info .= "[num sermon_posts: ".count($sermon_posts)."]<br /><br />";
    
    foreach ( $sermon_posts AS $sermon_post ) {
        
        setup_postdata( $sermon_post );
        
        $sermon_post_id = $sermon_post->ID;
        //$info .= "sermon_post_id: $sermon_post_id // ";
        $info .= make_link( get_the_permalink( $sermon_post_id ), '<em>'.get_the_title( $sermon_post_id ).'</em>' );
        $info .= '&nbsp;[id:'.$sermon_post_id.'] // ';
        
        if ( $a['legacy'] == 'true' ) {
            
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
                    $args2 = array(
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

                    $arr_posts2 = new WP_Query( $args2 );
                    $event_posts = $arr_posts2->posts;
                    //$info .= "args2: <pre>".print_r( $args2, true )."</pre>"; // tft
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
                
                //$info .= "&nbsp&nbsp&nbsp&nbsp[".$event_id."] ".$event_post->post_title."<br />";
                $info .= make_link( get_the_permalink( $event_id ), '&nbsp&nbsp&nbsp&nbsp'.get_the_title( $event_id ) );
                $info .= '&nbsp;[id:'.$event_id.']<br />';
                
                // Add postmeta for the sermon with this info
                if ( $event_id && $a['testing'] == 'false' ) {
                    add_post_meta( $sermon_post_id, 'related_event', $event_id, true );
                } else {
                    $info .= "test mode: add_post_meta is disabled.<br />";
                }
            }
            
        } else {
            
            if ( $a['legacy'] == 'true' ) {
                $info .= "No live events matching legacy_event_id $legacy_event_id.<br />";
            } else {
                $info .= "No matching events for sermon_date '$sermon_date'.<br />";
            }
            
        }

        $info .= "<br />";
    }
    
    return $info;
    
}

?>