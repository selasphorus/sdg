<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}


/*********** CPT: REPERTOIRE (aka Musical Works) ***********/

/* ~~~ Admin/Dev functions ~~~ */
function update_repertoire_events( $rep_id = null, $run_slow_queries = false, $arr_event_ids = array() ) {
	
	$info = "";
	$updates = false;
	
	$info .= "About to run update_repertoire_events for rep item with ID:".$rep_id."<br />";
	
	// get the repertoire_events field contents for the rep item
	$repertoire_events = get_field('repertoire_events', $rep_id, false);
	
	if ( !empty($repertoire_events) ) {
		//$info .= "This rep item currently has the following repertoire_events: <pre>".print_r($repertoire_events,true)."</pre>";
		if ( !is_array($repertoire_events) ) { $repertoire_events = explode( ", ",$repertoire_events ); } // If it's not an array already, make it one
		$info .= "This rep item currently has [".count($repertoire_events)."] repertoire_events<br />";	
	} else {
		$info .= "This rep item currently has no repertoire_events.<br />";
		$repertoire_events = array(); // No repertoire_events set yet, so prep an empty array
	}
	
	// Check to see if any event_ids were submitted and proceed accordingly
	if ( empty($arr_event_ids) && $run_slow_queries == true ) {
	
		// No event_ids were submitted -> run a query to find ALL event_ids for events with programs containing the rep_id		
		$related_events = get_related_events ( "program_item", $rep_id );
		$arr_event_ids = $related_events['event_posts'];
	
		if ( empty($arr_event_ids) ) {
			$info .= "No related events were found using the get_related_events fcn.<br />"; // tft
		}
    
	}

	// Check event_ids to see if they're already in the repertoire_events array and add them if not
	foreach($arr_event_ids as $event_id) {
		if ( !in_array( $event_id, $repertoire_events ) ) {
			$repertoire_events[] = $event_id;
			$updates = true;
		} else {
			$info .= "The event_id [$event_id] is already in the array.<br />";	
		}
	}
	
	// If changes have been made, then update the repertoire_events field with the modified array of event_id values
	if ( $updates == true ) {
		if ( update_field('repertoire_events', $repertoire_events, $rep_id ) ) {
			$info .= "Success! repertoire_events field updated<br />";
			$info .= "Updated repertoire_events: <pre>".print_r($repertoire_events,true)."</pre>";
		} else {
			$info .= "phooey. update failed.<br />";
		}
	} else {
		$info .= "No update needed.<br />";
	}
	
	$info .= "+++++<br /><br />";
	
	return $info;
	
}

// WIP fcn to update to new bidirectional field: repertoire_litdates
/*function update_repertoire_litdates( $rep_id = null, $run_slow_queries = false, $arr_litdate_ids = array() ) {
	
	$info = "";
	$updates = false;
	
	$info .= "About to update repertoire_litdates for rep item with ID:".$rep_id."<br />";
	
	// get the repertoire_litdates field contents for the rep item
	$repertoire_litdates = get_field('repertoire_litdates', $rep_id, false);
	
	if ( !empty($repertoire_litdates) ) {
		$info .= "This rep item currently has the following repertoire_litdates: <pre>".print_r($repertoire_litdates,true)."</pre>";								
		if ( !is_array($repertoire_litdates) ) { $repertoire_litdates = explode( ", ",$repertoire_litdates ); } // If it's not an array already, make it one		
	} else {
		$info .= "This rep item currently has no repertoire_litdates.<br />";
		$repertoire_litdates = array(); // No repertoire_litdates set yet, so prep an empty array
	}
	
	// Check to see if any litdate_ids were submitted and proceed accordingly
	if ( empty($arr_litdate_ids) && $run_slow_queries == true ) {
	
		// No litdate_ids were submitted -> run a query to find ALL litdate_ids for litdates with programs containing the rep_id		
		$related_litdates = get_related_litdates ( "program_item", $rep_id );
		$arr_litdate_ids = $related_litdates['litdate_posts'];
	
		if ( empty($arr_litdate_ids) ) {
			$info .= "No related litdates were found using the get_related_litdates fcn.<br />"; // tft
		}
    
	}

	// Check litdate_ids to see if they're already in the repertoire_litdates array and add them if not
	foreach($arr_litdate_ids as $litdate_id) {
		if ( !in_array( $litdate_id, $repertoire_litdates ) ) {
			$repertoire_litdates[] = $litdate_id;
			$updates = true;
		} else {
			$info .= "The litdate_id [$litdate_id] is already in the array.<br />";	
		}
	}
	
	// If changes have been made, then update the repertoire_litdates field with the modified array of litdate_id values
	if ( $updates == true ) {
		if ( update_field('repertoire_litdates', $repertoire_litdates, $rep_id ) ) {
			$info .= "Success! repertoire_litdates field updated<br />";
			$info .= "Updated repertoire_litdates: <pre>".print_r($repertoire_litdates,true)."</pre>";
		} else {
			$info .= "phooey. update failed.<br />";
		}
	} else {
		$info .= "No update needed.<br />";
	}
	
	$info .= "+++++<br /><br />";
	
	return $info;
	
}*/


/* ~~~ Display functions ~~~ */

function get_cpt_repertoire_content( $post_id = null ) {
	
	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
	// Init vars
    $arr_info = array();
    $info = "";
    $ts_info = "";
    
	if ($post_id === null) { $post_id = get_the_ID(); }
	//$ts_info .="[get_cpt_repertoire_content] post_id: $post_id<br />";
	
    $arr_rep_info = get_rep_info( $post_id, 'display', true, true ); // get_rep_info( $post_id = null, $format = 'display', $show_authorship = true, $show_title = true )
	$rep_info = $arr_rep_info['info'];
	$ts_info .= $arr_rep_info['ts_info'];
						
	if ( $rep_info ) {
        //$info .= "<h3>The Work:</h3>";
        $info .= $rep_info;
    }
    
    // Related Events
    $repertoire_events = get_field('repertoire_events', $post_id, false);
	if ( empty($repertoire_events) && is_dev_site() ) {
		// Field repertoire_events is empty -> check to see if updates are in order
		$ts_info .= '<!-- '.update_repertoire_events( $post_id ).' -->';
	}
    
    if ( $repertoire_events ) { 
        //global $post;
        //-- STC
        $info .= "<h3>Performances at Saint Thomas Church:</h3>";
        $x = 1;
        foreach($repertoire_events as $event_post_id) { 
            //setup_postdata($event_post);
            //$ts_info .= "[$x] event_post: <pre>".print_r($event_post, true)."</pre>"; // tft
            //$event_post_id = $event_post->ID;
            
            // TODO: modify to show title & event date as link text
            $event_title = get_the_title($event_post_id);
            $date_str = get_post_meta( $event_post_id, '_event_start_date', true );
            if ( $date_str ) { $event_title .= ", ".$date_str; }
            $info .= make_link( get_the_permalink($event_post_id), $event_title, null, null, "_blank" ) . "<br />";
            
            $x++;
        }
    } else {
        if ( devmode_active() ) { 
            $info .= "<p>No related events were found.</p>"; // tft
        }
    }
    
    wp_reset_query();
    
    // Related Editions
    $related_editions = get_field('related_editions', $post_id, false);
    
    if ( $related_editions &&
        ( ( is_dev_site() && current_user_can('read_repertoire') ) || current_user_can('read_music') ) 
       ) {
       	//-- STC
        $info .= "<h3>Edition(s) in the Saint Thomas Library:</h3>";
        //$ts_info .= "<pre>related_editions: ".print_r($related_editions, true)."</pre>";
        foreach ( $related_editions as $edition_id ) {
            //$ts_info .= "edition_id: ".$edition_id."<br />";
            $info .= make_link( get_the_permalink($edition_id), get_the_title($edition_id) ) . "<br />";
        }        
    }
    
    // Possible Duplicate Posts
    /*$dupes = get_possible_duplicate_posts ( $post_id );
    $duplicate_posts = $dupes['posts'];
    $duplicate_posts_info = $dupes['info'];
    
    if ( $duplicate_posts ) { 
        
        $ts_info .= "<h3>Possible Duplicate(s):</h3>";
        $x = 1;
        foreach($duplicate_posts as $duplicate_post) { 
        
            setup_postdata($duplicate_post);
            //$ts_info .= "[$x] duplicate_post: <pre>".print_r($duplicate_post, true)."</pre>"; // tft
            $duplicate_post_id = $duplicate_post->ID;
            
            $ts_info .= make_link( get_the_permalink($duplicate_post_id), $duplicate_post->post_title, null, null, "_blank" ) . "<br />";
            
            // TODO: build in merge options
                        
            $x++;
        }
    } else {
        if ( devmode_active() ) { 
            $ts_info .= "<p>No duplicate posts were found.</p>"; // tft
        }
    }*/
    
    //$ts_info .= "test"; // tft
    //$ts_info = '<div class="troubleshooting">'.$ts_info.'</div>';
	
	$arr_info['info'] = $info;
    if ( $do_ts ) { $arr_info['ts_info'] = $ts_info; } else { $arr_info['ts_info'] = null; }
    
    return $arr_info;
}

/*********** CPT: EDITION ***********/
function get_cpt_edition_content( $post_id = null ) {
    
    // TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
	
	// Init vars
	$info = "";
	$ts_info = "";
	if ($post_id === null) { $post_id = get_the_ID(); }
	
	$ts_info .= "<!-- edition post_id: $post_id -->";
    
    // Musical Work
    if ( get_field( 'repertoire_editions', $post_id )  ) {
        $repertoire_editions = get_field( 'repertoire_editions', $post_id );
        //$info .= '<pre>'.print_r($repertoire_editions, true).'</pre>';
        foreach ( $repertoire_editions as $musical_work_id ) {
            $info .= "<h3>".get_the_title($musical_work_id)."</h3>";
            //$info .= "<h3>".$musical_work->post_title."</h3>";
        }
    } elseif ( get_field( 'musical_work', $post_id )  ) {
        //$ts_info .= '<p class="devinfo">'."This record requires an update. It is using the old musical_work field and should be updated to use the new bidirectional repertoire_editions field.".'</p>';
        $ts_info .= '<!-- NB: This record requires an update. It is using the old musical_work field and should be updated to use the new bidirectional repertoire_editions field -->';
        $musical_works = get_field( 'musical_work', $post_id );
        //$info .= '<pre>'.print_r($musical_works, true).'</pre>';
        foreach ( $musical_works as $musical_work ) {
            $info .= "<h3>".$musical_work->post_title."</h3>";
        }
    } else {
        $ts_info .= "<!-- No musical_work found for edition with id: $post_id -->";
    }
    
    // TODO: use get_rep_info to make more refined view?
    
    /*$rep_info = get_rep_info( $post_id, 'display', true, true ); // get_rep_info( $post_id = null, $format = 'display', $show_authorship = true, $show_title = true )
	if ( $rep_info ) {
        $info .= "<h3>The Work:</h3>";
        $info .= $rep_info;
    }*/
    
    $info .= '<table class="edition_info">';
    //$info .= '<tr><td class="label"></td><td></td></tr>';
        
    // Publication Info
    if ( get_field( 'editor', $post_id )  ) {
        $editors = get_field( 'editor', $post_id );
        //$info .= '<tr><td><pre>'.print_r($editors, true).'</pre></td></tr>';
        foreach ( $editors as $editor ) {
            $info .= '<tr><td class="label">Editor</td><td>'.$editor->post_title.'</td></tr>';
        }
    }
    if ( get_field( 'publisher', $post_id )  ) {
        $publishers = get_field( 'publisher', $post_id );
        foreach ( $publishers as $publisher ) {
            $info .= '<tr><td class="label">Publisher</td><td>'.$publisher->post_title.'</td></tr>';
        }
    }
    if ( get_field( 'publication', $post_id )  ) {
        $publications = get_field( 'publication', $post_id );
        //$info .= '<tr><td class="label">Publication</td><td><pre>'.print_r($publications, true).'</pre></td></tr>'; // tft
        foreach ( $publications as $publication ) {
            if ( is_object($publication) ) { $publication_title = $publication->post_title; } else { $publication_title = get_the_title($publication); }
            $info .= '<tr><td class="label">Publication</td><td>'.$publication_title.'</td></tr>';
        }
    }
    if ( get_field( 'publication_date', $post_id )  ) {
        $publication_date = get_field( 'publication_date', $post_id );
        $info .= '<tr><td class="label">Publication Date</td><td>'.$publication_date.'</td></tr>';
    }
    
    // Choir Forces
    if ( get_field( 'choir_forces', $post_id )  ) {
        $choir_forces = get_field( 'choir_forces', $post_id );
        //$info .= '<tr><td class="label">Choir Forces</td><td><pre>'.print_r($choir_forces, true).'</pre></td></tr>';
        foreach ( $choir_forces as $choir ) {
            if ( is_array($choir) ) { $choir_label = $choir['label']; } else { $choir_label = $choir; }
            $info .= '<tr><td class="label">Choir Forces</td><td>'.$choir_label.'</td></tr>';
        }
    }
    
    
    // TODO: streamline this to process array of taxonomies
        
    // Get and display term names for "voicing"
    $voicings = wp_get_post_terms( $post_id, 'voicing', array( 'fields' => 'names' ) );
    $voicings_str = "";
    if ( count($voicings) > 0 ) {
        foreach ( $voicings as $voicing ) {
            //$voicings_str .= '<span class="voicing">';
            //$voicings_str .= '<pre>'.print_r($voicing, true).'</pre>';
            $voicings_str .= $voicing;
            //$voicings_str .= '</span>';
        }
    } else {
        $voicings_str = '<span class="fyi">N/A</span>';
    }
    $info .= '<tr><td class="label">Voicing</td><td>'.$voicings_str.'</td></tr>';
    
    // Get and display term names for "soloists"
    $soloists = wp_get_post_terms( $post_id, 'soloist', array( 'fields' => 'names' ) );
    $soloists_str = "";
    #$info .= print_r($soloists, true); // tft
    if ( count($soloists) > 0 ) {
        foreach ( $soloists as $soloist ) {
            $soloists_str .= $soloist;
        }
    } else {
        $soloists_str = '<span class="fyi">N/A</span>';
    }
    $info .= '<tr><td class="label">Soloists</td><td>'.$soloists_str.'</td></tr>';

    // Get and display term names for "instruments"
    $instruments = wp_get_post_terms( $post_id, 'instrument', array( 'fields' => 'names' ) );
    $instruments_str = "";
    #$info .= print_r($instruments, true); // tft
    if ( count($instruments) > 0 ) {
        foreach ( $instruments as $instrument ) {
            $instruments_str .= $instrument;
        }
    } else {
        $instruments_str = '<span class="fyi">N/A</span>';
    }
    $info .= '<tr><td class="label">Instruments</td><td>'.$instruments_str.'</td></tr>';
    
    // Get and display term names for "keys"
    $keys = wp_get_post_terms( $post_id, 'key', array( 'fields' => 'names' ) );
    $keys_str = "";
    #$info .= print_r($keys, true); // tft
    if ( count($keys) > 0 ) {
        foreach ( $keys as $key ) {
            $keys_str .= $key;
        }
    } else {
        $keys_str = '<span class="fyi">N/A</span>';
    }
    $info .= '<tr><td class="label">Key(s)</td><td>'.$keys_str.'</td></tr>';
    
    // WIP -- still to add:
    // library tags
        
    // Library Info
    //if ( current_user_can('music') ) {
    if ( current_user_can('read_music') ) { // Why is this generating an error?

        if ( $box_num = get_field( 'box_num', $post_id ) ) {            
            $info .= '<tr><td class="label">Call Num</td><td>'.$box_num.'</td></tr>';
        }
        if ( $library_notes = get_field( 'library_notes', $post_id ) ) {
            $info .= '<tr><td class="label">Library Notes</td><td>'.$library_notes.'</td></tr>';
        }
        if ( $scores = get_field( 'scores', $post_id ) ) {
            //$info .= '<tr><td class="label">Score(s)</td><td>'.$scores.'</td></tr>';
        }

    }

    $info .= '</table>';
    
    if ( $do_ts ) { $info .= $ts_info; }
    
	return $info;
	
}

// Function to determine if rep work is of anonymous or unknown authorship
function is_anon( $post_id = null ) {
    
    // Init vars
	if ($post_id === null) { $post_id = get_the_ID(); }
	if ( empty($post_id) ) { return null; }
    $info = "";
    $composers_str = "";
    $anon = false;
    
    // Do nothing if post_id is empty or this is not a rep record
    if ( $post_id === null || get_post_type( $post_id ) != 'repertoire' ) { return null; }
    
    $composers = get_field('composer', $post_id, false);
    if ( $composers ) {
    	foreach ( $composers as $composer ) {
			if ( $composer ) { $composers_str .= get_the_title($composer); }
		}
    }    
    
    if ( $composers_str == '[Unknown]' || $composers_str == 'Unknown' || $composers_str == 'Anonymous' || $composers_str == 'Plainsong' ) {
        $anon = true;
    }
    
    return $anon;
}

// Stringify an array of person ids or objects, with formatting options
// TODO: better documentation
// TODO: add option to make_link for each name
function str_from_persons_array ( $args = array() ) {
    
    // TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: str_from_persons_array", $do_log );
    
    // Init vars
    $arr_info = array();
    $info = "";
    $ts_info = "";
    
    // Defaults
	$defaults = array(
		'arr_persons'     	=> array(),
		'person_category' 	=> null,
		'post_id' 			=> null,
		'format'    		=> 'display', // other possible values include: "post_title", "edition_title" -- ??
		'arr_of'    		=> 'objects',
		'abbr'    			=> false,
		'links'    			=> false,
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
    
    //sdg_log( "[str_from_persons] arr_persons: ".print_r($arr_persons, true), $do_log );
    sdg_log( "[ssfpa] person_category: ".$person_category, $do_log );
    sdg_log( "[ssfpa] post_id: ".$post_id, $do_log );
    sdg_log( "[ssfpa] format: ".$format, $do_log );
    sdg_log( "[ssfpa] arr_of: ".$arr_of, $do_log );
    sdg_log( "[ssfpa] abbr: ".(int)$abbr, $do_log );
    sdg_log( "[ssfpa] links: ".(int)$links, $do_log );
    
    $ts_info .= "<!-- [ssfpa] format: $format -->";
    $ts_info .= "<!-- [ssfpa] person_category: $person_category -->";
    $ts_info .= "<!-- [ssfpa] arr_persons: ".print_r($arr_persons, true)." -->";
    
    foreach ( $arr_persons AS $person_id ) {

        //$info .= "<pre>person: ".print_r($person, true)."</pre>"; // tft
        
        /*if ( $arr_of == "objects" ) {
            if ( isset($person['ID']) ) { $person_id = $person['ID']; } else { $person_id = null; }
        } else {
            $person_id = $person;
        }*/
        sdg_log( "[ssfpa] person_id: ".$person_id, $do_log );
        $ts_info .= "<!-- [ssfpa] person_id: ".$person_id." -->";
        
        // Set up display args to pass to fcn get_person_display_name
        if ( $abbr || has_term( 'psalms', 'repertoire_category', $post_id ) && !has_term( 'motets', 'repertoire_category', $post_id ) && !has_term( 'anthems', 'repertoire_category', $post_id ) ) { 
        	$name_abbr = "abbr";
        } else {
        	$name_abbr = "full";
        }
        
        $override = "none";
        $use_post_title = false;
        $show_prefix = false;
        $show_suffix = false;
        $show_job_title = false;
        $show_dates = false;
        $styled = true;
        	
        if ( $person_category == "composers" || $person_category == "arrangers" ) {
        	//
        }
        
        if ( ( $format == "post_title" || $format == "edition_title" ) && ( $person_category == "composers" || $person_category == "arrangers" ) ) { 
			$show_dates = true;
			$styled = false; // don't add person_dates span/style for post_titles
		} else if ( $abbr !== true ) {
			$show_dates = true;
			$styled = true; // add dates with span/style
		}
        
        if ( $links ) {
        	// TODO: verify post_type == person?
			$person_url = esc_url( get_permalink( $person_id ) );
			if ( $person_url ) { $display_args['url'] = $person_url; }
		} else {
			$person_url = null;
		}
		
		$display_args = array( 'person_id' => $person_id, 'override' => $override, 'name_abbr' => $name_abbr, 'show_prefix' => $show_prefix, 'show_suffix' => $show_suffix, 'show_job_title' => $show_job_title, 'show_dates' => $show_dates, 'url' => $person_url, 'styled' => $styled );
        
        // Get the display_name
        $arr_person_name = get_person_display_name( $display_args );
        $person_name = $arr_person_name['info'];            
        $info .= $person_name;
        $ts_info .= $arr_person_name['ts_info'];

        if (count($arr_persons) > 1) { $info .= ", "; }

    } // END foreach $arr_persons

    // Trim trailing comma and space
    if ( substr($info, -2) == ", " ) {
        $info = substr($info, 0, -2); // trim off trailing comma
    }
    
    $arr_info['info'] = $info;
	if ( $do_ts ) { $arr_info['ts_info'] = $ts_info; } else { $arr_info['ts_info'] = null; }
	
	return $arr_info;
    
}

// Retrieve properly formatted authorship info for Repertoire records
// Authorship: Composers, Arrangers, Transcriber, Librettists, &c.
// $format options include: display; post_title; ....? (TODO: better info here)
function get_authorship_info ( $args = array() ) {

	// TS/logging setup
	$do_ts = true; 
    $do_log = false;
    sdg_log( "divline2", $do_log ); 
    sdg_log( "function called: get_authorship_info", $do_log );
    
    // Defaults
	$defaults = array(
		'data'     		=> array(),
		'format'    	=> 'post_title',
		'abbr'    		=> false,
		'is_single_work'=> false,
		'show_title'    => false,
		'links'    		=> false,
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	/*
    sdg_log( "[authorship_info] data: ".print_r($data, true), $do_log );
    sdg_log( "[authorship_info] format: ".$format, $do_log );
    sdg_log( "[authorship_info] is_single_work: ".$is_single_work, $do_log );
    sdg_log( "[authorship_info] show_title: ".$show_title, $do_log );
    sdg_log( "[authorship_info] abbr: ".(int)$abbr, $do_log );
    */
    
    // Init vars
    $arr_info = array();
    $authorship_info = "";
    $ts_info = "";
    //
    $rep_title = "";
    $composers = array();
    $arrangers = array();
    $transcribers = array();
    $translators = array();
    $librettists = array();
    //
    $anon_info = "";
    $is_anon = false;
    $is_hymn = false;
    $is_psalm = false;
    //
    if ( $format == "post_title" || $format == "edition_title" ) {
    	$html = false;
    } else {
    	$html = true;
    }
    
    // Get info either via post_id, if set, or from data array
    if ( isset($data['post_id']) ) {
        
        sdg_log( "[authorship_info] get info from data['post_id']", $do_log );
        
        $post_id = $data['post_id'];
        ///$ts_info .= "<!-- [authorship_info] post_id: ".$post_id." -->";
        
        if ( isset($data['rep_title']) && $data['rep_title'] != "" ) { 
            $rep_title = $data['rep_title'];
        } else {
            $title_clean = get_post_meta( $post_id, 'title_clean' );
            if ( $title_clean != "" ) {
                $rep_title = $title_clean;
            } else {
                $rep_title = get_the_title( $post_id );
            }
        }
        
        $is_anon = is_anon($post_id);
        ///if ( $format == 'display' && $is_anon == true ) { $ts_info .= "<!-- anon: true -->"; } else { $ts_info .= "<!-- anon: false -->"; }

        // Taxonomies
        if ( has_term( 'hymns', 'repertoire_category', $post_id ) ) { $is_hymn = true; }
        if ( has_term( 'psalms', 'repertoire_category', $post_id ) ) { $is_psalm = true; }
        
        // Get postmeta
        $composers_str = "";
        $composers = get_field('composer', $post_id, false); // Can't use get_post_meta for ACF relationship fields because stored value is array
        if ( $composers ) { 
            $persons_args = array( 'arr_persons' => $composers, 'person_category' => 'composers', 'post_id' => $post_id, 'format' => $format, 'arr_of' => 'objects', 'abbr' => false, 'links' => $links );
            $arr_composers_str = str_from_persons_array ( $persons_args );
            $composers_str = $arr_composers_str['info'];
            $ts_composers = $arr_composers_str['ts_info'];
            $ts_info .= $ts_composers;
            //args: $arr_persons, $person_category = null, $post_id = null, $format = 'display', $arr_of = "objects", $abbr = false ) {
        }
        $display_composer = $composers_str;
        //
        $arrangers = get_field('arranger', $post_id, false);
        $transcribers = get_field('transcriber', $post_id, false);
        $librettists = get_field('librettist', $post_id, false);
        $translators = get_field('translator', $post_id, false);
        //
        $anon_info = get_post_meta( $post_id, 'anon_info', true ); // post_meta ok for text fields... but is it better/faster? TODO: RS //$anon_info = get_field( $post_id, 'anon_info', false );//

        // TODO: streamline this -- maybe along the lines of is_anon?
        if ( $format == 'display' ) { $ts_info .= "<!-- display_composer: ".$display_composer." -->"; } // tft
        if ( $display_composer == 'Plainsong' ) { 
            $plainsong = true;
            if ( $anon_info == "" ) {
                // TODO: change composer to "Anonymous" - ??
                //$anon_info = "Plainsong"; // TMP
            }                
        } else {
            $plainsong = false;
        }
        
        ///if ( $format == 'display') { $ts_info .= "<!-- anon_info: ".$anon_info." -->"; } // tft
        
        $arr_of = 'objects';
        
    } else {
        
        sdg_log( "[authorship_info] get info from data without post_id", $do_log );
        
        $post_id = null;
        //$is_hymn
        //$is_psalm
        
        if ( isset($data['rep_title']) ) { $rep_title = $data['rep_title']; } else { $rep_title = ""; }
        
        if ( isset($data['composers']) ) { $composers = $data['composers']; }
        if ( isset($data['arrangers']) ) { $arrangers = $data['arrangers']; }
        if ( isset($data['transcribers']) ) { $transcribers = $data['transcribers']; }
        if ( isset($data['anon_info']) ) { $anon_info = $data['anon_info']; } else { $anon_info = ""; }
        if ( isset($data['is_hymn']) ) { $is_hymn = $data['is_hymn']; }
        if ( isset($data['is_psalm']) ) { $is_psalm = $data['is_psalm']; }
        
        $arr_of = 'ids';
        
    }
    if ( $rep_title == "" || empty($rep_title) || $rep_title == "Responses" ) { $show_title = false; }
    $ts_info .= "<!-- [authorship_info] rep_title: ".print_r($rep_title,true)." -->";
    
    sdg_log( "[authorship_info] anon_info: ".$anon_info, $do_log );
    //sdg_log( "[authorship_info] rep_title: ".print_r($rep_title, true), $do_log );
    
    // Build the authorship_info string
    
    // 1. Composer(s)
    if ( !empty($composers) ) { //
        
        sdg_log( "[authorship_info] composers: ".print_r($composers, true), $do_log );
        
        $persons_args = array( 'arr_persons' => $composers, 'person_category' => 'composers', 'post_id' => $post_id, 'format' => $format, 'arr_of' => $arr_of, 'abbr' => $abbr, 'links' => $links );
        sdg_log( "[authorship_info] persons_args: ".print_r($persons_args, true), $do_log );
        $ts_info .= "<!-- [authorship_info] persons_args: <pre>".print_r($persons_args, true)."</pre> -->";
        $arr_composers_str = str_from_persons_array ( $persons_args );
        $composer_info = $arr_composers_str['info'];
        $ts_composers = $arr_composers_str['ts_info'];
        $ts_info .= $ts_composers;
                
        // TODO: check instead by ID? Would be more accurate and would allow for comments to be returned by fcn str_from_persons_array
        // Redundant: TODO: instead use is_anon fcn? Any reason why not to do this?
        if ( $composer_info == '[Unknown]' || $composer_info == 'Unknown' || $composer_info == 'Anonymous' || $composer_info == 'Plainsong' ) { //
            $is_anon = true;
            sdg_log( "[authorship_info] is_anon.", $do_log);
        } else {
            sdg_log( "[authorship_info] NOT is_anon.", $do_log);
        }
        if ( $composer_info == "Unknown" || ( $composer_info == "Anonymous" && $anon_info == "" ) ) { 
            $composer_info = "";
        }
        
        sdg_log( "[authorship_info] composer_info: ".$composer_info, $do_log );
        sdg_log( "[authorship_info] anon_info: ".$anon_info, $do_log );
        
        ///$ts_info .= "<!-- composer_info: ".$composer_info." -->";
        ///$ts_info .= "<!-- anon_info: ".$anon_info." -->";
        
        if ( $composer_info != "" || $anon_info != "" ) {

            if ( $is_anon == true ) { // || $composer_info == 'Plainsong'
                if ( $anon_info != "" ) {
                	$show_anon = "";
                    // 1a. "Anonymous/anon_info"
                    //sdg_log( "[authorship_info] is_anon + anon_info", $do_log );
                    if ( $format == "post_title" || $format == "edition_title" || $format == "concert_item" ) {
                        if ( $composer_info != "" ) {
                            $show_anon .= "/";
                        }
                        $show_anon .= $anon_info;
                        //$composer_info .= "Anonymous/".$anon_info.""; // ???
                    } else if ( $is_single_work !== true && $anon_info != "Plainsong" && $is_psalm == false ) {
                        $show_anon .= " (".$anon_info.")";
                    } else if ( $is_psalm == true && $composer_info == "Plainsong" ) {
                        $show_anon .= "/".$anon_info;
                        // TODO: make this same as all plainsong? Or keep variation for Psalms only?
                    } else {
                        $show_anon .= $anon_info;
                    }
                    if ( $html ) { $show_anon = '<span class="anon_info">'.$show_anon.'</span>'; }
                    if ( !empty( $show_anon) ) { $composer_info .= $show_anon; }
                }
            }

            if ( $is_single_work !== true && $composer_info != "" ) {

                // 1b. Composer name(s)
                if ( $format == "post_title" && $composer_info != "Unknown" && $composer_info != "" ) { // && $composer_info != "Anonymous"
                    $composer_info = " -- ".$composer_info;
                } else if ( $is_psalm && $format != "concert_item" ) { // has_term( 'psalms', 'repertoire_category', $post_id )
                    $composer_info = " (".$composer_info.")";
                } else if ( $plainsong == true ) {
                    if ( $show_title == true ) {
                        $composer_info = " &mdash; ".$composer_info;
                    }
                } else if ( $is_anon == true && $show_title == true ) {
                    $composer_info = " &mdash; ".$composer_info;
                } else if ( $rep_title != "" && $show_title == true ) {
                    $composer_info = ", by ".$composer_info;
                } else if ( $format != "edition_title" && $format != "concert_item"  ) {
                    $composer_info = "by ".$composer_info;
                }
                if ( $html ) { $composer_info = '<span class="composer">'.$composer_info.'</span>'; }
            }

        }

        if ( $is_single_work == true && $composer_info != "" ) {
            if ( $is_anon == false ) { 
                $authorship_info .= "Composer(s): ";
            } else if ( $plainsong == true || stripos($composer_info, 'tone') || stripos($composer_info, 'Plainsong') || stripos($anon_info, 'Plainsong') || $anon_info == 'Plainsong' ) { // also "mode"? -- 
                // TODO after upgrade to PHP 8: str_contains ( string $haystack , string $needle )
                $authorship_info .= "Tone/Mode: ";
            } else {
                $authorship_info .= "Authorship: ";
            }
            $authorship_info .= $composer_info."<br />";
        } else {
            $authorship_info .= $composer_info;
        }
        
    } else {
        $composer_info = "";
    }

    // 2. Arranger(s)
    if ( !empty($arrangers) ) {

        $persons_args = array( 'arr_persons' => $arrangers, 'person_category' => 'arrangers', 'post_id' => $post_id, 'format' => $format, 'arr_of' => $arr_of, 'abbr' => $abbr, 'links' => $links );
        $arr_arrangers_info = str_from_persons_array ( $persons_args );
        $arrangers_info = $arr_arrangers_info['info'];
        $ts_arrangers = $arr_arrangers_info['ts_info'];
        $ts_info .= $ts_arrangers;

        if ( $is_single_work == true && $arrangers_info != "") {
            $authorship_info .= "Arranger(s): ".$arrangers_info."<br />";
        } else {
            if ( $authorship_info != "" ) {
                //$authorship_info .= ", ";
            } else if ( $format != 'edition_title' && $format != "concert_item" ) {
                //$authorship_info .= " -- ";
            }
            if ( $authorship_info != "" ) { $authorship_info .= ", "; } else if ( $format != "concert_item" ) { $authorship_info .= " -- "; }
            if ( $html ) { 
            	$authorship_info .= '<span class="arranger">arr. '.$arrangers_info.'</span>';
            } else {
            	$authorship_info .= "arr. ".$arrangers_info;
            }
            
        }

    }

	// TODO: consolidate the following three blocks into a single loop for transcribers, librettists, translators (poss also arrangers)
	
    // 3. Transcriber(s)
    if ( !empty($transcribers) ) {

        $persons_args = array( 'arr_persons' => $transcribers, 'person_category' => 'transcribers', 'post_id' => $post_id, 'format' => $format, 'arr_of' => $arr_of, 'abbr' => $abbr, 'links' => $links );
        $arr_transcribers_info = str_from_persons_array ( $persons_args );
        $transcribers_info = $arr_transcribers_info['info'];
        $ts_transcribers = $arr_transcribers_info['ts_info'];
        $ts_info .= $ts_transcribers;

        if ( $transcribers_info != "" ) {
            if ( $is_single_work == true ) {
                $authorship_info .= "Transcriber(s): ".$transcribers_info."<br />";
            } else {
                if ( $authorship_info != "" ) {
                    //$authorship_info .= ", ";
                } else if ( $format != 'edition_title' && $format != "concert_item" ) {
                    $authorship_info .= " -- ";
                }
                if ( $html ) { 
					$authorship_info .= '<span class="transcriber">transcr. '.$transcribers_info.'</span>';
				} else {
					if ( $authorship_info != "" ) { $authorship_info .= ", "; } else { $authorship_info .= " -- "; }
					$authorship_info .= "transcr. ".$transcribers_info;
				}
            }
        }
        
    }

    // 4. Librettist(s)
    if ( !empty($librettists) && $format != "post_title" && $format != "edition_title" && $format != "concert_item" ) {
        
        $persons_args = array( 'arr_persons' => $librettists, 'person_category' => 'librettists', 'post_id' => $post_id, 'format' => $format, 'arr_of' => $arr_of, 'abbr' => $abbr, 'links' => $links );
        $arr_librettists_info = str_from_persons_array ( $persons_args );
        $librettists_info = $arr_librettists_info['info'];
        $ts_librettists = $arr_librettists_info['ts_info'];
        $ts_info .= $ts_librettists;

        if ( $is_single_work == true && $librettists_info != "") {
            $authorship_info .= "Librettist(s): ".$librettists_info."<br />";
        } else {
        	if ( $html ) { 
				$authorship_info .= '<span class="librettist">text by '.$librettists_info.'</span>';
			} else {
				if ( $authorship_info != "" ) { $authorship_info .= ", "; } else { $authorship_info .= " -- "; }
				$authorship_info .= "text by ".$librettists_info;
			}            
        }

    }

    // 5. Translator(s)
    if ( !empty($translators) && $format != "post_title" ) {

        $persons_args = array( 'arr_persons' => $translators, 'person_category' => 'translators', 'post_id' => $post_id, 'format' => $format, 'arr_of' => $arr_of, 'abbr' => $abbr, 'links' => $links );
        $arr_translators_info = str_from_persons_array ( $persons_args );
        $translators_info = $arr_translators_info['info'];
        $ts_translators = $arr_translators_info['ts_info'];
        $ts_info .= $ts_translators;
		
        if ( $is_single_work == true && $translators_info != "") {
            $authorship_info .= "Translator(s): ".$translators_info."<br />";
        } else {
        	if ( $html ) { 
				$authorship_info .= '<span class="librettist">transl. '.$translators_info.'</span>';
			} else {
				if ( $authorship_info != "" ) { $authorship_info .= ", "; } else { $authorship_info .= " -- "; }
				$authorship_info .= "transl. ".$translators_info;
			}
        }

    }
    
    $arr_info['info'] = $authorship_info;
    if ( $do_ts ) { $arr_info['ts_info'] = $ts_info; } else { $arr_info['ts_info'] = null; }
    
    return $arr_info;
    
}

// Excerpted From
function get_excerpted_from( $post_id = null ) {

	// TS/logging setup
	$do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log ); 
    
    // Init vars
    $arr_info = array();
    $excerpted_from = "";
    $ts_info = "";
    
    if ( $post_id == null ) { return null; }    
    //$ts_info .= "<!-- seeking excerpted_from info for post_id: $post_id -->"; // tft
    
    $excerpted_from_post = get_field('excerpted_from', $post_id, false);
    
    if ( $excerpted_from_post ) {
        
        //$ts_info .= "<!-- excerpted_from_post: ".print_r($excerpted_from_post, true)." -->";
        
        $excerpted_from_id = $excerpted_from_post[0]; // TODO: deal w/ possibility that there may be multiple values in the array
        
        $ts_info .= "<!-- excerpted_from_id: $excerpted_from_id -->";
        
        $excerpted_from_title_clean = get_post_meta( $excerpted_from_id, 'title_clean', true );
        if ( $excerpted_from_title_clean ) {
            $excerpted_from = $excerpted_from_title_clean;
        } else {
            $excerpted_from = get_the_title($excerpted_from_id);
        }
        
    } else if ( $excerpted_from_txt = get_post_meta( $post_id, 'excerpted_from_txt', true ) ) {
        $ts_info .= "<!-- excerpted_from_txt: $excerpted_from_txt -->";
        $excerpted_from = $excerpted_from_txt;
    } else {
        $excerpted_from = null;
    }
    
    $arr_info['info'] = $excerpted_from;
    if ( $do_ts ) { $arr_info['ts_info'] = $ts_info; } else { $arr_info['ts_info'] = null; }
    
    return $arr_info;
    
}

// Retrieve full rep title and associated info. 
// Return formats include 'display' (for front end), 'txt' (for back end(, and 'sanitized' (for DB matching)
function get_rep_info( $post_id = null, $format = 'display', $show_authorship = true, $show_title = true ) {
	
	// TS/logging setup
	$do_ts = true; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: get_rep_info", $do_log );
    
	// Init vars
    $arr_info = array();
    $info = "";
    $ts_info = "";    
	if ( $post_id === null ) { $post_id = get_the_ID(); }
    
    sdg_log( "[get_rep_info] post_id: ".$post_id, $do_log );
    sdg_log( "[get_rep_info] format: ".$format, $do_log );
    sdg_log( "[get_rep_info] show_authorship: ".$show_authorship, $do_log );
    sdg_log( "[get_rep_info] show_title: ".$show_title, $do_log );
    
    // Do nothing if post_id is empty or this is not a rep record
    if ( $post_id === null || get_post_type( $post_id ) != 'repertoire' ) { return null; }
    
    if ( $show_authorship == 'true' ) { $show_authorship = true; } else { $show_authorship = false; }    
    if ( $show_title == 'true' ) { $show_title = true; }
    if ( is_singular('repertoire') ) { $is_single_work = true; } else { $is_single_work = false; }
	//if ( $format == 'display') { $info = "<!-- post_id: $post_id -->"; } // tft
        
    $post_title = get_the_title( $post_id );
    $title_clean = get_post_meta( $post_id, 'title_clean', true );
    // TODO: if title_clean is empty, then $new_title_clean = make_clean_title( $post_id ) &c. ?
    $title_for_matching = get_post_meta( $post_id, 'title_for_matching', true );
    $catalog_number = get_post_meta( $post_id, 'catalog_number', true );
    $opus_number = get_post_meta( $post_id, 'opus_number', true );
    $tune_name = get_post_meta( $post_id, 'tune_name', true );
    // Consider getting all post_meta at once as array? -- $post_metas = get_post_meta(get_the_ID());

    if ( $title_clean != "" ) { $title = $title_clean; } else { $title = $post_title; $title_clean = $title; }
    
    // Hymn nums, where relevant
    if ( has_term( 'hymns', 'repertoire_category', $post_id) && $catalog_number != "" ) {
        $title = $catalog_number." &ndash; ".$title;
    }
    
    // Psalms
    if ( $is_single_work == false ) {
        
        if (substr($title,0,6) == "Psalm ") {
            $title = substr($title,6);
        } else if (substr($title,0,7) == "Psalms ") {
            $title = substr($title,7);
        } else {
            //if ( $format == 'display') { $info .= "<!-- ".substr($title,0,6)." -->"; }
        }
        
    }
    
    // Psalms: Anglican Chant
    // TODO: If title starts w/ number and includes words 'Anglican Chant' and has category 'Anglican Chant' and/or 'Psalms', then fix the post_title by prepending 'Psalm'
    
    if (  $show_title == false || // ACF field option per program row
        ( $format == 'display' && ( $title == "Responses" ) ) // Responses -- don't display title in event programs, &c. -- front end display
       ){ //|| has_term( 'responses', 'repertoire_category', $post_id )
        $title = "";
    }
    
    if ( $is_single_work == true && $title != "") {
        $info .= "Title: ".$title."<br />";
    }
    
    $arr_excerpted_from = get_excerpted_from( $post_id );
    $excerpted_from = $arr_excerpted_from['info'];
    if ( $format == 'display' ) { $info .= $arr_excerpted_from['ts_info']; }
       
    if ( $excerpted_from != "" ) {
        if ( $is_single_work == true ) {
            $info .= "Excerpted from: ".$excerpted_from."<br />";
        } else {
            $title .= ", from &lsquo;".$excerpted_from."&rsquo;";
            //$title .= ", from <em>".$excerpted_from."</em>";
        }        
    }
    
    // Catalog & Opus numbers
    if ( $catalog_number != "" && !has_term( 'hymns', 'repertoire_category', $post_id ) ) {
        if ( $is_single_work == true ) {
            $info .= "Catalog No.: ".$title."<br />";
        } else {
            $title .= ", ".$catalog_number;
        }        
    }
    if ( $opus_number != "" ) {
        if ( $is_single_work == true ) {
            $info .= "Opus No.: ".$title."<br />";
        } else {
            $title .= ", ".$opus_number;
        }
    }
    
    // Tune Name
    if ( $tune_name != "" ) {
        if ( $is_single_work == true ) {
            $info .= "Tune name: ".$tune_name."<br />";
        } else {
            $title .= " &ndash; ".$tune_name;
        }
	}
    
    // Add the assembled title to the info to be returned
    if ( $is_single_work == false ) {
        $info .= $title;
    }
    
    // Display authorship info
    if ( $show_authorship == true ) { // && $is_single_work == false
        
        $authorship_arr = array( 'post_id' => $post_id, 'rep_title' => $title );
        $authorship_args = array( 'data' => $authorship_arr, 'format' => $format, 'abbr' => false, 'is_single_work' => $is_single_work, 'show_title' => $show_title );
        $arr_authorship_info = get_authorship_info ( $authorship_args );
        $authorship_info = $arr_authorship_info['info'];
        if ( $title == "" && substr($authorship_info, 0, 2) == ", " ) { $authorship_info = substr($authorship_info, 2); } // trim leading comma and space
    
        $ts_info .= $arr_authorship_info['ts_info'];
        
        // ( $data = array(), $format = 'post_title', $abbr = false, $is_single_work = false, $show_title = true ) 
        if ( $authorship_info != "Unknown" ) {
            if ( $format == 'display' ) { $info .= '<span class="authorship">'; }
            $info .= $authorship_info;
            if ( $format == 'display' ) { $info .= '</span>'; }
        }

    } // END if ( $show_authorship == true ):
    
    if ( $format == 'sanitized' ) { 
        $info = super_sanitize_title( $info );
    } else if ( $format == 'txt' ) { 
        //$info = super_sanitize_title( $info );
    } else if ( $is_single_work == true ) {
        $ts_info .= "<!-- test -->";
    } else {
        $info = make_link( get_the_permalink( $post_id ), $info, $title_clean, 'subtle', '_blank' );
    }
	
	$arr_info['info'] = $info;
	if ( $do_ts ) { $arr_info['ts_info'] = $ts_info; } else { $arr_info['ts_info'] = null; }
	
	return $arr_info;
	
} // END function get_rep_info

function get_author_ids ( $post_id = null, $include_composers = true ) {
    
    $arr_ids = array();
	//if ($post_id === null) { $post_id = get_the_ID(); }
    
    // Do nothing if post_id is empty or this is not a rep record
    if ( $post_id === null || get_post_type( $post_id ) != 'repertoire' ) { return "no post_id"; } //return null; }
	
    // Get postmeta
    $composers = get_field('composer', $post_id, false);
    $arrangers = get_field('arranger', $post_id, false);
    $transcribers = get_field('transcriber', $post_id, false);

    if ( is_array($composers) ) { array_merge($arr_ids, $composers); }
    if ( is_array($arrangers) ) { array_merge($arr_ids, $arrangers); }
    if ( is_array($transcribers) ) { array_merge($arr_ids, $transcribers); }
    //if ( $arrangers ) { $arr_ids[] = $arrangers; }
    //if ( $transcribers ) { $arr_ids[] = $transcribers; }
    
    //$arr_ids[] = $composers;
    //if ( $composers ) { $arr_ids[] = $composers; }
    //if ( $arrangers ) { $arr_ids[] = $arrangers; }
    //if ( $transcribers ) { $arr_ids[] = $transcribers; }
    //array_merge($arr_ids, $composers, $arrangers, $transcribers);
    //array_merge($arr_ids, $arrangers);
    //array_merge($arr_ids, $transcribers);
    
    return $arr_ids;
    
}

function get_composer_ids ( $post_id = null ) {
    
    $arr_ids = array();
	//if ($post_id === null) { $post_id = get_the_ID(); }
    
    // Do nothing if post_id is empty or this is not a rep record
    if ( $post_id === null || get_post_type( $post_id ) != 'repertoire' ) { return "no post_id"; } //return null; }
	
    $composers = get_field('composer', $post_id, false);
    foreach ($composers AS $composer_id) {
        $arr_ids[] = $composer_id;
    }

    return $arr_ids;
    
}

/*** Choirplanner ***/

// WIP to replace pods w/ ACF
// TODO: generalize to make this not so repertoire-specific?
// https://www.advancedcustomfields.com/resources/creating-wp-archive-custom-field-filter/

/*
function match_group_field ( $field_groups, $field_name ) {
    
    $field = null;
    
    // Loop through the field_groups and their fields to look for a match (by field name)
    foreach ( $field_groups as $group ) {

        $group_key = $group['key'];
        //$info .= "group: <pre>".print_r($group,true)."</pre>"; // tft
        $group_title = $group['title'];
        $group_fields = acf_get_fields($group_key); // Get all fields associated with the group
        //$field_info .= "<hr /><strong>".$group_title."/".$group_key."] ".count($group_fields)." group_fields</strong><br />"; // tft

        $i = 0;
        foreach ( $group_fields as $group_field ) {

            $i++;

            if ( $group_field['name'] == $field_name ) {

                // field exists, i.e. the post_type is associated with a field matching the $field_name
                $field = $group_field;
                // field_object parameters include: key, label, name, type, id -- also potentially: 'post_type' for relationship fields, 'sub_fields' for repeater fields, 'choices' for select fields, and so on

                //$field_info .= "Matching field found for field_name $field_name!<br />"; // tft
                //$field_info .= "<pre>".print_r($group_field,true)."</pre>"; // tft

                /*
                $field_info .= "[$i] group_field: <pre>".print_r($group_field,true)."</pre>"; // tft
                $field_info .= "[$i] group_field: ".$group_field['key']."<br />";
                $field_info .= "label: ".$group_field['label']."<br />";
                $field_info .= "name: ".$group_field['name']."<br />";
                $field_info .= "type: ".$group_field['type']."<br />";
                if ( $group_field['type'] == "relationship" ) { $field_info .= "post_type: ".print_r($group_field['post_type'],true)."<br />"; }
                if ( $group_field['type'] == "select" ) { $field_info .= "choices: ".print_r($group_field['choices'],true)."<br />"; }
                $field_info .= "<br />";
                //$field_info .= "[$i] group_field: ".$group_field['key']."/".$group_field['label']."/".$group_field['name']."/".$group_field['type']."/".$group_field['post_type']."<br />";
                */
                /*

                break;
            }

        }

        if ( $field ) { 
            //$field_info .= "break.<br />";
            break;  // Once the field has been matched to a post_type field, there's no need to continue looping
        }

    } // END foreach ( $field_groups as $group )
    
    return $field;
}
*/

// TODO: make this function less STC-specific?
add_shortcode('sdg_search_form', 'sdg_search_form');
function sdg_search_form ($atts = [], $content = null, $tag = '') {
	
	$info = "";
    $ts_info = "";
    //$search_values = false; // var to track whether any search values have been submitted on which to base the search
    $search_values = array(); // var to track whether any search values have been submitted and to which post_types they apply
    
    $ts_info .= '_GET: <pre>'.print_r($_GET,true).'</pre>'; // tft
    //$ts_info .= '_REQUEST: <pre>'.print_r($_REQUEST,true).'</pre>'; // tft
        
	$a = shortcode_atts( array(
		'post_type'    => 'post',
		'fields'       => null,
        'form_type'    => 'simple_search',
        'limit'        => '-1'
    ), $atts );
    
    $post_type = $a['post_type'];
    $form_type = $a['form_type'];
    $limit = $a['limit'];
    
    //$info .= "form_type: $form_type<br />"; // tft

    // After building the form, assuming any search terms have been submitted, we're going to call the function birdhive_get_posts
    // In prep for that search call, initialize some vars to be used in the args array
    // Set up basic query args
    $args = array(
		'post_type'       => array( $post_type ), // Single item array, for now. May add other related_post_types -- e.g. repertoire; edition
		'post_status'     => 'publish',
		'posts_per_page'  => $limit, //-1, //$posts_per_page,
        'orderby'         => array( 'title' => 'ASC', 'ID' => 'ASC' ),
        'return_fields'   => 'ids',
	);
    
    // WIP / TODO: fine-tune ordering -- 1) rep with editions, sorted by title_clean 2) rep without editions, sorted by title_clean
    /*
    'orderby'	=> 'meta_value',
    'meta_key' 	=> '_event_start_date',
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
    if ( $a['fields'] ) {
        
        // Turn the fields list into an array
        $arr_fields = sdg_att_explode( $a['fields'] );
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
                    	$args['_search_title'] = $field_value; // custom parameter -- see posts_where filter fcn
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
                    /*$info .= '<div class="dev-only">';
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
        $args_related = array(); // init
        $rep_cat_queried = false;
        
        //$ts_info .= "mq_components_primary: <pre>".print_r($mq_components_primary,true)."</pre>"; // tft
        //if ( !empty($tq_components_primary) ) { $ts_info .= "tq_components_primary: <pre>".print_r($tq_components_primary,true)."</pre>"; } // tft
        //$ts_info .= "mq_components_related: <pre>".print_r($mq_components_related,true)."</pre>"; // tft
        //if ( !empty($tq_components_primary) ) { $ts_info .= "tq_components_related: <pre>".print_r($tq_components_related,true)."</pre>"; } // tft
        
        // If field values were found related to both post types,
        // AND if we're searching for posts that match ALL terms (search_operator: "and"),
        // then set up a second set of args/birdhive_get_posts
        
        if ( $search_primary_post_type == true ) {
			$args['post_type'] = $post_type;
		}
		
		if ( $search_related_post_type == true ) {
			if ( is_array($args) && is_array($args_related) ) {
				$args_related = array_merge( $args_related, $args ); //$args_related = $args;
			}
            $args_related['post_type'] = $related_post_type;
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
                $args = null; // reset primary args to prevent triggering of second query
            }
        }
        
        // Finalize meta_query or queries
        // ==============================
        /* 
        WIP if meta_key = title_clean and related_post_type is true then incorporate also, using title_clean meta_value:
        $args['_search_title'] = $field_value; // custom parameter -- see posts_where filter fcn
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
			if ( !empty($meta_query) ) { $args['meta_query'] = $meta_query; }
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
			if ( !empty($meta_query_related) ) { $args_related['meta_query'] = $meta_query_related; }
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
			if ( !empty($tax_query) ) { $args['tax_query'] = $tax_query; }
		}
		
		// related query
		if ( $search_related_post_type == true ) {
			if ( count($tq_components_related) > 1 && empty($tax_query_related['relation']) ) {
				$tax_query_related['relation'] = $search_operator;
			}
			foreach ( $tq_components_related AS $component ) {
				$tax_query_related[] = $component;
			}
			if ( !empty($tax_query_related) ) { $args_related['tax_query'] = $tax_query_related; }
		}            

        ///// WIP
        if ( $search_related_post_type == true && $related_post_type ) {
            
            // If we're dealing with multiple post types, then the and/or is extra-complicated, because not all taxonomies apply to all post_types
            // Must be able to find, e.g., repertoire with composer: Mousezart as well as ("OR") all editions/rep with instrument: Bells
            
            if ( $search_operator == "or" ) {
                if ( !empty($tax_query) && !empty($meta_query) ) {
                    $args['_meta_or_tax'] = true; // custom parameter -- see posts_where filters
                }
            }
        }
        /////
        
        // If search values have been submitted, then run the search query
        if ( count($search_values) > 0 ) {
            
            if ( $search_primary_post_type == true && $args ) {
				$ts_info .= "About to pass args to birdhive_get_posts: <pre>".print_r($args,true)."</pre>"; // tft
			
				// Get posts matching the assembled args
				/* ===================================== */
				if ( $default_query === true ) {
					$ts_info .= "Default query -- no need to run a search<br />";
				} else {
					if ( $form_type == "advanced_search" ) {
						//$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $posts_info = array(); // tft
						$posts_info = birdhive_get_posts( $args );
					} else {
						$posts_info = birdhive_get_posts( $args );
					}
					
					if ( isset($posts_info['arr_posts']) ) {
				
						$arr_post_ids = $posts_info['arr_posts']->posts; // Retrieves an array of IDs (based on return_fields: 'ids')
						$ts_info .= "Num arr_post_ids: [".count($arr_post_ids)."]<br />";
						//$ts_info .= "arr_post_ids: <pre>".print_r($arr_post_ids,true)."</pre>"; // tft
				
						$info .= '<div class="troubleshooting">'.$posts_info['ts_info'].'</div>';
				
						// Print last SQL query string
						//global $wpdb;
						//$info .= '<div class="troubleshooting">'."last_query:<pre>".$wpdb->last_query."</pre>".'</div>'; // tft
						//$ts_info .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>"; // tft
				
					}
				}			
			}
            
            if ( $search_related_post_type == true && $args_related && $default_query == false ) {
                
                $ts_info .= "About to pass args_related to birdhive_get_posts: <pre>".print_r($args_related,true)."</pre>"; // tft
                
                //$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $related_posts_info = array(); // tft
                $related_posts_info = birdhive_get_posts( $args_related );
                
                if ( isset($related_posts_info['arr_posts']) ) {
                
                    $arr_related_post_ids = $related_posts_info['arr_posts']->posts;
                    $ts_info .= "Num arr_related_post_ids: [".count($arr_related_post_ids)."]<br />";
                    //$ts_info .= "arr_related_post_ids: <pre>".print_r($arr_related_post_ids,true)."</pre>"; // tft

                    $info .= '<div class="troubleshooting">'.$related_posts_info['info'].'</div>';
					$info .= '<div class="troubleshooting">'.$related_posts_info['ts_info'].'</div>';
                    
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
                        //$ts_info .= "args: <pre>".print_r($args,true)."</pre>"; // tft
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
        
        
    } // END if ( $a['fields'] )

    $info .= '<div class="troubleshooting">';
    $info .= $ts_info;
    $info .= '</div>';
    
    return $info;
    
}

//
function format_search_results ( $post_ids, $search_type = "choirplanner" ) {
    
    // init
    $info = ""; 
    $ts_info = "";
    
    $ts_info .= "+~+~+~+~+~+~+~+~+~+~ format_search_results +~+~+~+~+~+~+~+~+~+~<br />";
    
    // TODO: generalize -- this is currently very specific to display of repertoire/editions info
    //if ( $search_type = "choirplanner" ) { }
    
    // TODO: untangle results if search was run for multiple post types
    // First match repertoire to editions? merge data somehow? 
    // deal w/ rep with no related editions
    // For repertoire-type posts, ...
    // For edition-type posts, ...
    
    //$posts = $posts->posts; // Retrieves an array of WP_Post Objects
    $rep_ids = array();
    foreach ( $post_ids as $post_id ) {
            
        //$info .= '<pre>'.print_r($post, true).'</pre>';
        //$info .= '<div class="troubleshooting">post: <pre>'.print_r($post, true).'</pre></div>';
        $post_type = get_post_type($post_id);
        //$ts_info .= 'post_id: '.$post_id."<br />";
        //$ts_info .= 'post_type: '.$post_type."<br />";
        if ( $post_type == "edition" ) {
            // Get the related repertoire record(s)
            if ( $repertoire_editions = get_field( 'repertoire_editions', $post_id ) ) { //  && !empty($repertoire_editions)
                $ts_info .= 'repertoire_editions for edition with post_id '.$post_id.': <pre>'.print_r($repertoire_editions, true).'</pre>';
                foreach ( $repertoire_editions as $musical_work ) {
                    if ( is_object($musical_work) ) {
                        $rep_ids[] = $musical_work->ID;
                    } else {
                        $rep_ids[] = $musical_work;
                    }
                }
            } elseif ( $musical_works = get_field( 'musical_work', $post_id )  ) {
                $ts_info .= 'musical_works for edition with post_id '.$post_id.': <pre>'.print_r($musical_works, true).'</pre>';
                $ts_info .= '<span class="devinfo">'."[$post_id] This record requires an update. It is using the old musical_work field and should be updated to use the new bidirectional repertoire_editions field.</span><br />";
                foreach ( $musical_works as $musical_work ) {
                    if ( is_object($musical_work) ) {
                        $rep_ids[] = $musical_work->ID;
                    } else {
                        $rep_ids[] = $musical_work;
                    }
                }           
            } else {
                $ts_info .= '<span class="devinfo">No musical_work found for edition with id: '.$post_id.'</span><br />';
            }
            //$rep_ids[] = $rep_post_id;
        } else if ( $post_type == "repertoire" ) {
            $rep_ids[] = $post_id;
        }
    }
    
    //$info .= 'rep_ids: <pre>'.print_r($rep_ids, true).'</pre>';
    $rep_ids = array_unique($rep_ids);
    //$info .= 'array_unique rep_ids: <pre>'.print_r($rep_ids, true).'</pre>';
    //$info .= "<br />+++++++++++<br />";
    
    $info .= "<p>Num matching posts found: [".count($rep_ids)."]</p>";
    $limit = 100; // tft -- limit num of posts to display, lest search is broken and it tried to display thousands of records at once...
    if ( count($rep_ids) > $limit ) {
    	$info .= "<p>To keep page load times under control, only the first ".$limit." results are displayed.<br />You might want to try narrowing your search by adding additional terms or filters.</p>";
    }
    
    $info .= '<form id="cp_merge" method="get" action="/merge-records/" target="_blank">';
    //$info .= '<form id="cp_merge" method="post" action="/merge-records/" target="_blank">'; // This works fine, but ids are lost on refresh of merge page. Pass them via GET instead for more flexibility.
    //$info .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" class="sdg_search_form '.$form_type.'">';
    $info .= '<table class="choirplanner search_results">';
    $info .= '<tr>';
    $info .= '<th class="actions" style="width: 2rem;"></th>'; // TODO: replace inline style w/ proper class definition
    $info .= '<th>Musical Work</th><th>Editions</th>';
    $info .= '</tr>';
    
    $i = 0;
    foreach ( $rep_ids as $rep_id ) {
        
        $post_id = $rep_id;
        $post_title = get_the_title($post_id);

        $title = get_field('title_clean', $post_id, false);
        if ( empty($title)) { $title = $post_title; }
        
        $info .= '<tr>';
        //
        $info .= '<td class="actions">';
        $info .= '<input type="checkbox" id="merge-'.$post_id.'" name="ids[]" value="'.$post_id.'" />'; // If using form action POST
        //$info .= '<input type="checkbox" id="merge-'.$post_id.'" name="merge-'.$post_id.'" value="'.$post_id.'" />';
        $info .= '</td>';
        //
        $info .= '<td class="repertoire">';
        $info .= '<div class="rep_item">';
        $info .= make_link( esc_url( get_permalink($post_id) ), $title, "TEST", null, '_blank' );
        $info .= "&nbsp;";
        $authorship_args = array( 'data' => array( 'post_id' => $post_id ), 'format' => 'display', 'abbr' => false, 'is_single_work' => false, 'show_title' => false, 'links' => true );
        $arr_authorship_info = get_authorship_info ( $authorship_args );
		$authorship_info = $arr_authorship_info['info'];
		$ts_info .= $arr_authorship_info['ts_info'];
        $info .= $authorship_info;
        /*
        $info .= " by ";
        // Composer(s)
        $composers = get_field('composer', $post_id, false);
        if ( $composers ) {
            foreach ( $composers AS $person_id ) {
                //$info .= "<pre>".print_r($composer, true)."</pre>";
                $composer_name = get_the_title($person_id);
                $composer_url = esc_url( get_permalink( $person_id ) );
                $info .= make_link( $composer_url, $composer_name, null, null, '_blank' );
            }
        }
        */
        $info .= ' <span class="devinfo">['.$post_id.']</span>';
        
        // Excerpted from
        $arr_excerpted_from = get_excerpted_from( $post_id );
    	$excerpted_from = $arr_excerpted_from['info'];
        if ( $excerpted_from ) { $info .= '<br /><span class="excerpted_from">Excerpted from: '.$excerpted_from.'</span>'; }
        
        // Tune Name
        $tune_name = get_field('tune_name', $post_id, false);
        if ( $tune_name ) { $info .= '<br /><span class="tune_name">Tune: '.$tune_name.'</span>'; }
        
        $info .= '</div>';

        // Get rep-specific info: rep categories, 
        $rep_info = "";

        // Get and display term names for "repertoire_category".
        $rep_categories = wp_get_post_terms( $post_id, 'repertoire_category', array( 'fields' => 'names' ) );
        if ( count($rep_categories) > 0 ) {
            foreach ( $rep_categories as $category ) {
                if ( $category != "Choral Works" ) {
                    $rep_info .= '<span class="category">';
                    $rep_info .= $category;
                    $rep_info .= '</span>';
                }                
            }
            //$rep_info .= "Categories: ";
            //$rep_info .= implode(", ",$rep_categories);
            //$rep_info .= "<br />";
        }

        // Get and display term names for "season".
        //$seasons = wp_get_post_terms( $post_id, 'season', array( 'fields' => 'names' ) );
        $seasons = get_field('season', $post_id, false); // returns array of IDs
        if ( is_array($seasons) && count($seasons) > 0 ) {
            foreach ( $seasons as $season ) {
                $rep_info .= '<span class="season">';
                $rep_info .= ucfirst($season);
                $rep_info .= '</span>';
            }
            //$rep_info .= implode(", ",$seasons);
        }
        
        // Get and display post titles for "related_liturgical_dates".
        $repertoire_litdates = get_field('repertoire_litdates', $post_id, false); // returns array of IDs
        if ( $repertoire_litdates ) {

            foreach ($repertoire_litdates AS $litdate_id) {
                $rep_info .= '<span class="liturgical_date">';
                $rep_info .= get_the_title($litdate_id);
                $rep_info .= '</span>';
            }

        }
        // Old version of field.
        $related_liturgical_dates = get_field('related_liturgical_dates', $post_id, false);
        if ( $related_liturgical_dates ) {

            foreach ($related_liturgical_dates AS $litdate_id) {
                $rep_info .= '<span class="liturgical_date_old devinfo">';
                $rep_info .= get_the_title($litdate_id);
                $rep_info .= '</span>';
            }

        }
        
        // Get and display term names for "occasion".
        $occasions = wp_get_post_terms( $post_id, 'occasion', array( 'fields' => 'names' ) );
        if ( count($occasions) > 0 ) {
            foreach ( $occasions as $occasion ) {
                $rep_info .= '<span class="occasion">';
                $rep_info .= $occasion;
                $rep_info .= '</span>';
            }
            //$rep_info .= implode(", ",$occasions);
        }

        // Get and display term names for "voicing".
        $voicings = wp_get_post_terms( $post_id, 'voicing', array( 'fields' => 'names' ) );
        if ( count($voicings) > 0 ) {
            foreach ( $voicings as $voicing ) {
                $rep_info .= '<span class="voicing devinfo">';
                $rep_info .= $voicing;
                $rep_info .= '</span>';
            }
        }

        // Get and display term names for "instrument".
        $instruments = wp_get_post_terms( $post_id, 'instrument', array( 'fields' => 'names' ) );
        if ( count($instruments) > 0 ) {
            foreach ( $instruments as $instrument ) {
                $rep_info .= '<span class="instrumentation devinfo">';
                $rep_info .= $instrument;
                $rep_info .= '</span>';
            }
        }
        
        // Get and display note of num event programs which include this work, if any
        // Get Related Events        
		// New way
		$repertoire_events = get_field('repertoire_events', $post_id, false);
		if ( is_array($repertoire_events) && count($repertoire_events) > 0 ) {
			$rep_info .= '<br /><span class="nb orange">This work appears in ['.count($repertoire_events).'] event program(s).</span>';
		} else {
			// Field repertoire_events is empty -> check to see if updates are in order
			if ( is_dev_site() ) {
				$rep_info .= '<p class="troubleshooting">';
				$rep_info .= update_repertoire_events( $post_id, false );
				$rep_info .= '</p>';
			} else if ( $i < 5 ) {  // On live site, for now, limit number of records that are processed, because the queries may be slow
				$rep_info .= '<p class="troubleshooting">{'.$i.'}'.update_repertoire_events( $post_id, false ).'</p>';
			}			
		}
		
		// Old way
		/*
		$related_events = get_related_events ( "program_item", $post_id );
		$event_post_ids = $related_events['event_posts'];

		if ( $event_post_ids ) {
			$info .= '<br /><span class="nb orange">This work appears in ['.count($event_post_ids).'] event program(s).</span>';
		}
		*/
		
		if ( $rep_info != "" ) {
			$info .= "<br />".$rep_info;
		}

        $info .= '</td>';

        // Related Editions
        $related_editions = get_field('repertoire_editions', $post_id, false);
        if ( empty($related_editions) ) {
            $related_editions = get_field('related_editions', $post_id, false);
        }
        //$info .= 'related_editions: <pre>'.print_r($related_editions, true).'</pre>';
        
        $info .= '<td class="editions">';
        
        if ( empty($related_editions) ) {

            $editions = '<div class="edition_info">';
            $editions .= "<span>No editions found in library database.</span>";
            $editions .= '</div>';

        } else {

            $editions = ""; // init
            $i = 1; // init counter

            foreach ( $related_editions AS $edition_id ) {

                //$info .= "<pre>".print_r($edition, true)."</pre>";
                
                $editions .= '<div class="edition_info">';
                $editions .= '<span class="counter">';
                $edition_url = esc_url( get_permalink( $edition_id ) );
                $editions .= make_link( $edition_url, '('.$i.')', null, null, '_blank' );
                //$editions .= '('.$i.')';
                $editions .= '</span>';

                // Publication Info
                $publication = get_field('publication', $edition_id);
                if ( $publication ){
                    $editions .= '<span class="publication">'; // publisher
                    $editions .= get_the_title($publication[0]);
                    //$editions .= the_field('publication', $edition_id); // nope
                    //$editions .= print_r($publication, true); // WIP 05/31/22 -- returns ID
                    $editions .= '</span>';
                    // todo -- link to publication?
                }
                
                $publisher = get_field('publisher', $edition_id);
                if ( $publisher ){
                    if ( $publication ){
                        $editions .= "/";
                    }
                    $editions .= '<span class="publisher">';
                    $pub_abbr = get_field('abbr', $publisher[0], false);
                    if ( $pub_abbr ) { $publisher = $pub_abbr; } else { $publisher = get_the_title($publisher[0]); }
                    $editions .= $publisher;
                    $editions .= '</span>';
                    //$editions .= make_link( $publisher_url, $publisher );
                }

                // Choir Forces
                $choir_forces = get_field('choir_forces', $edition_id);
                //$choir_forces = get_field_object('choir_forces', $edition_id);
                if ( $choir_forces ) {

                    /*$editions .= "choir_forces for edition $edition_id: <pre>".print_r($choir_forces, true)."</pre>";
                    $editions .= '<span class="choir_forces">';
                    if ( isset($choir_forces['label']) ) { $editions .= $choir_forces['label']; } else { $editions .= $choir_forces; }
                    $editions .= '</span>';*/
                    foreach ( $choir_forces as $choir ) {
                        //$choir = ucwords(str_replace("_"," ",$choir));
                        //$choir = str_replace("Mens","Men's",$choir);
                        $editions .= '<span class="choir_forces">';
                        //$editions .= print_r($choir, true);
                        if ( isset($choir['label']) ) { $editions .= $choir['label']; } else { $editions .= $choir; }
                        //$editions .= $choir['label'];
                        $editions .= '</span>';
                    }

                        /*if ( is_array($choir_forces) ) {
                            $editions .= "<pre>".print_r($choir_forces, true)."</pre>";
                            foreach ( $choir_forces as $choir ) {
                                if ( $choir == "Men-s Voices" ) { $choir = "Men's Voices"; }
                                $editions .= '<span class="choir_forces">';
                                $editions .= $choir;
                                $editions .= '</span>';
                            }
                        } else {
                            if ( $choir_forces == "Men-s Voices" ) { $choir_forces = "Men's Voices"; }
                            $editions .= '<span class="choir_forces">';
                            $editions .= $choir_forces;
                            $editions .= '</span>';
                        }*/
                        //$editions .= $edition_pod->display('choir_forces');

                }

                // Voicings
                $voicings_obj_list = get_the_terms( $edition_id, 'voicing' );
                // todo -- link to voicings?
                if ( $voicings_obj_list ) {
                    $voicings_str = join(', ', wp_list_pluck($voicings_obj_list, 'name'));
                    if ( !empty($voicings_str)) {
                        $editions .= '<span class="voicing">'.$voicings_str.'</span>';
                    }
                }

                // Soloists
                $soloists_obj_list = get_the_terms( $edition_id, 'soloist' );
                if ( $soloists_obj_list ) {
                    $soloists_str = join(', ', wp_list_pluck($soloists_obj_list, 'name'));
                    if ( !empty($soloists_str)) {
                        $editions .= '<span class="soloists">'.$soloists_str.'</span>';
                    }
                }

                // Instrumentation
                $instruments_obj_list = get_the_terms( $edition_id, 'instrument' );
                if ( $instruments_obj_list ) {
                    $instruments_str = join(', ', wp_list_pluck($instruments_obj_list, 'name'));
                    if ( !empty($instruments_str)) {
                        $editions .= '<span class="instrumentation">'.$instruments_str.'</span>';
                    }
                }

                // Keys
                $keys_obj_list = get_the_terms( $edition_id, 'key' );
                if ( $keys_obj_list ) {
                    $keys_str = join(', ', wp_list_pluck($keys_obj_list, 'name'));
                    if ( !empty($keys_str)) {
                        $editions .= '<span class="keys">'.$keys_str.'</span>';
                    }
                }

                $editions .= '</div>';

                // Box Num
                $box_num = get_field('box_num', $edition_id);
                if ( $box_num ){
                    $editions .= '<div class="box_num">';
                    $editions .= $box_num;
                    //$editions .= make_link( $edition_url, $box_num );
                    $editions .= '</div>';
                }

                $editions .= "<br />";

                $i++;
            } // foreach ($related_editions AS $edition)

            if ( substr($editions, -6) == '<br />' ) { $editions = substr($editions, 0, -6); } // trim off trailing OR
            
        }
        
        $info .= $editions;
        
        $info .= '</td>';
        $info .= '</tr>';
        
        $i++;
        if ( $i >= $limit ) { break; }
        
    } // END foreach ( $posts as $post )
    
    $info .= "</table>";
    
    $info .= '<input type="submit" value="Merge Selected">';
    //if ( is_dev_site() ) { $info .= '<input type="submit" value="Merge Selected">'; }
	$info .= "</form>";
    
    $info .= '<div class="troubleshooting">';
    $info .= $ts_info;
    $info .= '</div>';
    
    return $info;
    
}


/*********** CPT: GROUP ***********/
function get_cpt_group_content() {
	
	$info = "";
	$post_id = get_the_ID();
	$info .= "group post_id: $post_id<br />";
	
	return $info;
	
}

?>