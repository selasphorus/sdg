<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}


/*********** CPT: PERSON ***********/

function get_cpt_person_content( $post_id = null ) {
	
	// This function retrieves supplementary info -- the regular content template (content.php) handles title, content, featured image
	
    $info = ""; // init
    if ($post_id === null) { $post_id = get_the_ID(); }
    
    if ( $post_id === null ) {
        return false;
    }
    
    /*
    // TODO: figure out where to put this -- probably appended to post_title?
    $dates = get_person_dates( $post_id, true );
    if ( $dates && $dates != "" && $dates != "(-)" ) { 
        $info .= $dates; 
    }*/
    
    // TODO: consider eliminating check for has_term, in case someone forgot to apply the appropriate category
    if ( has_term( 'composers', 'people_category', $post_id ) ) {
        // Get compositions
        $arr_obj_compositions = get_related_posts( $post_id, 'repertoire', 'composer' ); // get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' )
        if ( $arr_obj_compositions ) {
            
            $info .= "<h3>Compositions:</h3>";
            
            //$info .= "<p>arr_compositions (".count($arr_compositions)."): <pre>".print_r($arr_compositions, true)."</pre></p>";
            foreach ( $arr_obj_compositions as $composition ) {
                //$info .= $composition->post_title."<br />";
                $rep_info = get_rep_info( $composition->ID, 'display', false, true ); // ( $post_id = null, $format = 'display', $show_authorship = true, $show_title = true )
                $info .= make_link( get_permalink($composition->ID), $rep_info )."<br />"; // make_link( $url, $linktext, $class = null, $target = null)
            }
        }
    }
    
    // TODO: arranger, transcriber, translator, librettist
    
    // Find and display any associated Editions, Publications, Sermons, and/or Events
    
    if ( is_dev_site() ) {
        
        // Editions
        $arr_obj_editions = get_related_posts( $post_id, 'edition', 'editor' ); // get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' )
        
        if ( $arr_obj_editions ) {

            $info .= '<div class="publications">';
            $info .= "<h3>Publications:</h3>";

            //$info .= "<p>arr_obj_editions (".count($arr_obj_editionss)."): <pre>".print_r($arr_obj_editions, true)."</pre></p>";
            foreach ( $arr_obj_editions as $edition ) {
                //$info .= $edition->post_title."<br />";
                $info .= make_link( get_permalink($edition->ID), $edition->post_title )."<br />"; // make_link( $url, $linktext, $class = null, $target = null)
            }

            $info .= '</div>';
        }
    }
    
    // Sermons
    $arr_obj_sermons = get_related_posts( $post_id, 'sermon', 'sermon_author' ); // get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' )
    if ( $arr_obj_sermons ) {
        
        $info .= '<div class="dev-only sermons">';
        $info .= "<h3>Sermons:</h3>";

        foreach ( $arr_obj_sermons as $sermon ) {
            //$info .= $sermon->post_title."<br />";
            $info .= make_link( get_permalink($sermon->ID), $sermon->post_title )."<br />"; // make_link( $url, $linktext, $class = null, $target = null)
        }
        
        $info .= '</div>';
    }
    
    if ( is_dev_site() ) {
        
        /*
        // Get Related Events
        $args = array(
            'posts_per_page'=> -1,
            'post_type'		=> 'event',
            'meta_query'	=> array(
                array(
                    'key'		=> "personnel_XYZ_person", // name of custom field, with XYZ as a wildcard placeholder (must do this to avoid hashing)
                    'compare' 	=> 'LIKE',
                    'value' 	=> '"' . $post_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                )
            ),
            'orderby'	=> 'meta_value',
            'order'     => 'DESC',
            'meta_key' 	=> '_event_start_date',
        );

        $query = new WP_Query( $args );
        $event_posts = $query->posts;
        $info .= "<!-- args: <pre>".print_r($args,true)."</pre> -->";
        $info .= "<!-- Last SQL-Query: {$query->request} -->";

        if ( $event_posts ) { 
            global $post;
            $info .= '<div class="dev-only em_events">';
            //-- STC
            $info .= '<h3>Events at Saint Thomas Church:</h3>';
            foreach($event_posts as $post) { 
                setup_postdata($post);
                // TODO: modify to show title & event date as link text
                $event_title = get_the_title();
                $date_str = get_post_meta( get_the_ID(), '_event_start_date', true );
                if ( $date_str ) { $event_title .= ", ".$date_str; }
                $info .= make_link( get_the_permalink(), $event_title ) . "<br />";	
            }
            $info .= '</div>';
        } else {
            $info .= "<!-- No related events found for post_id: $post_id -->";
        }
        */
        
        $term_obj_list = get_the_terms( $post_id, 'people_category' );
        if ( $term_obj_list ) {
            $terms_string = join(', ', wp_list_pluck($term_obj_list, 'name'));
            $info .= '<div class="dev-only categories">';
            if ( $terms_string ) {
                $info .= "<p>Categories: ".$terms_string."</p>";
            }
            $info .= '</div>';
        }
        
        wp_reset_query();
    }
    
    return $info;
    
}

function get_person_dates( $post_id, $styled = false ) {
    
    sdg_log( "divline2" );
    sdg_log( "function called: get_person_dates" );
    
    //sdg_log( "[str_from_persons] arr_persons: ".print_r($arr_persons, true) );
    sdg_log( "[get_person_dates] post_id: ".$post_id );
    //sdg_log( "[get_person_dates] styled: ".$styled );
    
    $info = ""; // init
    
    // Try ACF get_field instead?
    $birth_year = get_post_meta( $post_id, 'birth_year', true );
    $death_year = get_post_meta( $post_id, 'death_year', true );
    $dates = get_post_meta( $post_id, 'dates', true );

    if ( !empty($birth_year) && !empty($death_year) ) {
        $info .= "(".$birth_year."-".$death_year.")";
    } else if ( !empty($birth_year) ) {
        $info .= "(b. ".$birth_year.")";
    } else if ( !empty($death_year) ) {
        $info .= "(d. ".$death_year.")";
    } else if ( !empty($dates) ) {
        $info .= "(".$dates.")";
    }
    
    if ( !empty($info) ) {
        if ( $styled == true ) {
            $info = ' <span class="person_dates">'.$info.'</span>';
        } else {
            $info = ' '.$info; // add space before dates str
        }
    }
    
    return $info;
    
}

?>