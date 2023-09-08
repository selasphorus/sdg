<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}


/*********** CPT: GROUP ***********/

// TODO: consider folding this in to the display-content plugin as a special content structure (group/subgroup)
// AND generalize it so as to be able to use it for links and other content types...
// Display the titles and personnel for a given subgroup or groups
function display_group_personnel ( $args = array() ) {

	// TS/logging setup
    $do_ts = true; 
    $do_log = false;
    sdg_log( "divline2", $do_log );

	// Init vars
	$info = "";
	$ts_info = "";
	
	// Defaults
	$defaults = array(
		'group_id'		=> null,
		'subgroup_ids'	=> array(),
		'return_format' => 'links', // other options: list; excerpts; archive (full post content); grid; table
		//TODO: add display options -- e.g. list, table, &c. -- OR -- do this via display_content functions...
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );	
	
	//$ts_info .= "args: <pre>".print_r($args, true)."</pre>";
	
	// Get args from array
	if ( $group_id ) {
			
		$ts_info .= "group_id: $group_id<br />";
		
    	$subgroups = get_field('subgroups', $group_id); // ACF collection item repeater field values
		
		if ( $subgroup_ids ) {
			$ts_info .= "subgroup_ids: <pre>".print_r($subgroup_ids, true)."</pre>";
			//$ts_info .= "subgroup_id: $subgroup_id<br />";
		}
		
		foreach ( $subgroups as $i => $subgroup ) {
		
			$ts_info .= "i: $i<br />";
			
			// NB: subgroup_ids are passed starting with "1" instead of zero
			if ( $subgroup_ids && !in_array($i+1, $subgroup_ids) ) {
				continue; // don't show this subgroup; continue on to the next in the array
			}
			
			//$subgroup_id = $subgroups[$subgroup_id];
			$subgroup_name = $subgroup['name'];
			$subgroup_personnel = $subgroup['personnel'];			
			$subgroup_info = ""; // init
			//
			//$info .= "[$i] ".$subgroup_name."<br />";
			
			// WIP
			foreach ( $subgroup_personnel as $group_person ) {
			
				//$info .= "group_person: <pre>".print_r($group_person, true)."</pre>";
				$title_id = $group_person['title'];
				$title_term = get_term($title_id);
				if ( $title_term ) { 
				
					$group_title = $title_term->name;
					
					// Get all persons matching this group_id and title_id which are current
					//...
					// TODO: would it be better to do this via a bidirectional field along the lines of repertoire_events rather than trying to query ACF repeater rows?
					//...
					
					$wp_args = array(
						'post_type'   => 'person',
						'post_status' => 'publish',
						//'posts_per_page' => 1,
						'meta_query' => array(
							'relation' => 'AND',
							array(
								'key'		=> "titles_XYZ_group", // name of custom field, with XYZ as a wildcard placeholder (must do this to avoid hashing)
								//'compare' 	=> 'LIKE',
								//'value' 	=> '"' . $group_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
								'value' 	=> $group_id,
							),
							array(
								'key'		=> "titles_XYZ_title", // name of custom field, with XYZ as a wildcard placeholder (must do this to avoid hashing)
								//'compare' 	=> 'LIKE',
								//'value' 	=> '"' . $title_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
								'value' 	=> $title_id,
							),
						),
						'fields' => 'ids',
					);
	
					$query = new WP_Query( $wp_args );
					$persons = $query->posts;
					
					$ts_info .= "wp_args: <pre>".print_r($wp_args, true)."</pre>";
					$ts_info .= "persons: <pre>".print_r($persons, true)."</pre>";
					//$ts_info .= "Last SQL-Query (query): <pre>{$query->request}</pre>";
					
					if ( $persons ) { $subgroup_info .= $group_title.": "; }
					
					// If the display-content plugin is active, then use its functionality to display the subgroup personnel
					if ( function_exists( 'birdhive_display_collection' ) ) {
						$display_args = array( 'content_type' => 'posts', 'display_format' => $return_format, 'items' => $persons ); //, 'arr_dpatts' => $args
						$subgroup_info .= birdhive_display_collection( $display_args );
					} else {
						foreach ( $persons as $person_id ) {
							$person_name = get_the_title($person_id);
							$subgroup_info .= $person_name."<br />";
						}
					}
					
				}
			}
			
			if ( !empty($subgroup_info) ) {
				$info .= $subgroup_name."<br />";
				$info .= $subgroup_info;
			}
		}
    	
	} else {
	
		$ts_info .= "No group_id set<br />";
		
	}
	
	if ( $do_ts ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	// Return info for display
	return $info;
	
} // END function display_group_personnel ( $args = array() ) 


add_shortcode('group_personnel', 'sdg_group_personnel');
function sdg_group_personnel ( $atts = [] ) {

	$info = "";
	$ts_info = "";
	
	$args = shortcode_atts( array(
        'id' => null,
        'subgroup_ids' => array(),
    ), $atts );
    
    $group_id = $args['id'];
    $subgroup_ids = $args['subgroup_ids'];
    
	// Turn the list of subgroup_ids (if any) into a proper array
	//if ( $subgroup_ids ) { $subgroup_ids = birdhive_att_explode( $subgroup_ids ); }
	if ( $subgroup_ids ) { $subgroup_ids = array_map( 'intval', birdhive_att_explode( $subgroup_ids ) ); }
    
    $info .= display_group_personnel( array('group_id' => $group_id, 'subgroup_ids' => $subgroup_ids ) );
    
    $info .= '<div class="troubleshooting">'.$ts_info.'</div>';
    
    return $info;
    
}

?>