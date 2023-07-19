<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}


/*********** CPT: GROUP ***********/

// Display the titles and personnel for a given subgroup or groups
function display_group_personnel ( $args = array() ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );

	// Init vars
	$info = "";
	$ts_info = "";
	
	// Defaults
	$defaults = array(
		'group_id'		=> null,
		'subgroup_ids'	=> array(), // WIP -- how to limit display to chosen indices?
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
			
			//$subgroup = $subgroups[$subgroup_id];
			$subgroup_name = $subgroup['name'];
			$subgroup_personnel = $subgroup['personnel'];
			//
			foreach ( $subgroup_personnel as $group_person ) {
				//
			}
		}
    	
	} else {
	
		$ts_info .= "No group_id set<br />";
		
	}
	
	if ( $do_ts ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	// Return info for display
	return $info;
	
} // END function birdhive_display_collection ( $args = array() ) 


add_shortcode('group_personnel', 'sdg_group_personnel');
function sdg_group_personnel ( $atts = [] ) {

	$info = "";
	$ts_info = "";
	
	$args = shortcode_atts( array(
        'id' => null,
        'subgroup_ids' => array(),
    ), $atts );
    
    $group_id = $args['id'];
    
    $info .= display_group_personnel( array('group_id' => $group_id) );
    
    $info .= '<div class="troubleshooting">'.$ts_info.'</div>';
    
    return $info;
    
}

?>