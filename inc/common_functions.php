<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** POST RELATIONSHIPS ***********/

// The following function is the replacement for the old get_related_podposts fcn
function get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' ) {

	$info = null; // init
	
	// If we don't have actual values for all parameters, there's not enough info to proceed
	if ($post_id === null || $related_field_name === null || $related_post_type === null) { return null; }
	
	$related_id = null; // init
    if ( $return == 'single' ) {
        $limit = 1;
    } else {
        $limit = -1;
    }

	// Set args
    $args = array(
        'post_type'   => $related_post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key'     => $related_field_name,
                'value'   => $post_id
            )
        )
    );
    // Run query
    $related_posts = new WP_Query( $args );

    // Loop through the records returned 
    if ( $related_posts ) {
        
        if ( $return == 'single' ) {
            // TODO: simplify -- shouldn't really need a loop here...
            while ( $related_posts->have_posts() ) {            
                $related_posts->the_post();
                $related_id = get_the_ID();                
            }
            $info = $related_id;            
        } else {
            $info = $related_posts->data();
        }
        
    }
	
	return $info;
	
}

// Function to identify possible duplicates
// WIP -- not functional!
function get_possible_duplicate_posts( $post_id = null, $return = 'all' ) {

	$info = null; // init
	
	// If we don't have actual values for all parameters, there's not enough info to proceed
	if ($post_id === null ) { return null; }
	
	$post_type = get_post_type( $post_id );
	
	// Set post_status options based on user role
	if ( current_user_can('read_repertoire') ) {
		$post_status = array( 'publish', 'private', 'draft' );
	} else {
		$post_status = 'publish';
	}	
	
	$post_title = get_the_title( $post_id );
	
	// TODO/WIP: remove "a", "the", "an" and so on from post_title
	// Extract key words as array to use as search terms?
	// 
	
	if ( $post_type == "repertoire" ) {
		$musical_works = get_field( 'musical_work', $post_id );
        //$info .= '<pre>'.print_r($musical_works, true).'</pre>';
        /*foreach ( $musical_works as $musical_work ) {
            $info .= "<h3>".$musical_work->post_title."</h3>";
        }*/
		//$composers = get_field('composer', $post_id, false);
		/*if ( get_field( 'composer', $post_id )  ) {
			$composers_str = the_field( 'composer', $post_id );
		}*/
		/*foreach ( $composers as $composer ) {
			$composers_str .= get_the_title($composer);
		}*/
		// OR get_authorship_info -- with ids returned as array to compare w/ other works
	}
	
	// Set args
    $args = array(
        'post_type'   => $post_type,
        'post_status' => $post_status,
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key'     => $related_field_name,
                'value'   => $post_id
            )
        )
    );
    
    // Run query
    $related_posts = new WP_Query( $args );

    // Loop through the records returned 
    if ( $related_posts ) {
        
        if ( $return == 'single' ) {
            // TODO: simplify -- shouldn't really need a loop here...
            while ( $related_posts->have_posts() ) {            
                $related_posts->the_post();
                $related_id = get_the_ID();                
            }
            $info = $related_id;            
        } else {
            $info = $related_posts->data();
        }
        
    }
	
	return $info;
	
}

// WIP
function merge_field_values ( $p1_val = null, $p2_val = null ) {

	// init
	$arr_info = array();
	$merge_value = null;
	$info = "";
	
	// Compare values/merge arrays
	// If both values are arrays, then merge them
	if ( is_array($p1_val) && is_array($p2_val) ) {
		//$merge_value = array_merge($p1_val, $p2_val);
		$merge_value = array_unique(array_merge($p1_val, $p2_val));
		//$info .= "Merged arrays!";
	} else if ( !empty($p1_val) ) {
		// If p1_val is not empty, then compare it to p2_val
		if ( !empty($p2_val) ) {
			//compare... WIP
			if ( $p1_val == $p2_val ) {
				$merge_value = $p1_val; // They're identical
				//$info .= "==";
			} else {
				$merge_value = $p1_val;
				//$info .= "+";
				// TODO: save p2_val as backup?
			}
		} else {
			$merge_value = $p1_val;
		}				
	} else if ( !empty($p2_val) ) {
		$merge_value = $p2_val;
	}
	
	$arr_info['info'] = $info;
	$arr_info['merge_value'] = $merge_value;
	
	return $arr_info;
		
}

// Function to merge duplicate records
add_shortcode('sdg_merge_form', 'sdg_merge_form');
//function sdg_merge_form ( $post_ids = array() ) {
function sdg_merge_form ($atts = [], $content = null, $tag = '') {
	
	$info = "";
    $troubleshooting = "";
    
    if ( !empty($_GET) ) { $troubleshooting .= '_GET: <pre>'.print_r($_GET,true).'</pre>'; }
    if ( !empty($_POST) ) { $troubleshooting .= '_POST: <pre>'.print_r($_POST,true).'</pre>'; }
    //$troubleshooting .= '_REQUEST: <pre>'.print_r($_REQUEST,true).'</pre>'; // tft
    
    $a = shortcode_atts( array(
		'post_type'	=> 'post',
		'ids'     	=> array(),
        'form_type'	=> 'simple_merge',
        'limit'    	=> '-1'
    ), $atts );
    
    $post_type = $a['post_type'];
    $form_type = $a['form_type'];
    $limit = $a['limit'];
    
	// Set post_status options based on user role
	if ( current_user_can('read_repertoire') ) {
		$post_status = array( 'publish', 'private', 'draft' );
	} else {
		$post_status = 'publish';
	}
    
    // Set up basic query args for retrieval of posts to merge
    $args = array(
		'post_type'       => array( $post_type ), // Single item array, for now. May add other related_post_types -- e.g. repertoire; edition
		'post_status'     => $post_status,
		//'posts_per_page'  => $limit, //-1, //$posts_per_page,
        'orderby'         => 'title',
        'order'           => 'ASC',
        //'return_fields'   => 'ids', // ?
	);
	
    // Turn the list of IDs into a proper array
    if ( !empty($a['ids']) ) {
    	$str_ids = $a['ids'];
    	$post_ids = array_map( 'intval', sdg_att_explode( $a['ids'] ) );
    } else if (isset($_GET['ids'])) {
		$post_ids = $_GET['ids'];
		$str_ids = implode(", ", $_GET['ids']);
    } else if (isset($_POST['ids'])) {
		$post_ids = $_POST['ids'];
		$str_ids = implode(", ", $_POST['ids']);
    } else {
    	$post_ids = array();
    	$str_ids = "";
    }
    
	$args['ids'] = $str_ids; // pass string as arg to be processed by birdhive_get_posts
    
    if ( count($post_ids) < 1 ) {
    	$troubleshooting .= "Not enough post_ids submitted.<br />";
    }
    
    //$info .= "form_type: $form_type<br />"; // tft

    // If post_ids have been submitted, then run the query
    if ( count($post_ids) > 1 ) {
            
		//$troubleshooting .= "About to pass args to birdhive_get_posts: <pre>".print_r($args,true)."</pre>"; // tft
	
		// Get posts matching the assembled args
		// =====================================
		$posts_info = birdhive_get_posts( $args );
	
		if ( isset($posts_info['arr_posts']) ) {
		
			$arr_posts = $posts_info['arr_posts']->posts;
			$troubleshooting .= "Num arr_posts: [".count($arr_posts)."]<br />";
			//$troubleshooting .= "arr_posts: <pre>".print_r($arr_posts,true)."</pre>"; // tft
			
			if ( count($arr_posts) > 2 ) {
				$troubleshooting .= "That's too many posts! I can only handle two at a time.<br />";
			}
		
			//$info .= '<div class="troubleshooting">'.$posts_info['info'].'</div>';
			$troubleshooting .= $posts_info['info']."<hr />";
			//$info .= $posts_info['info']."<hr />"; //$info .= "birdhive_get_posts/posts_info: ".$posts_info['info']."<hr />";
		
			// Print last SQL query string
			//global $wpdb;
			//$info .= '<div class="troubleshooting">'."last_query:<pre>".$wpdb->last_query."</pre>".'</div>'; // tft
			//$troubleshooting .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>"; // tft
		
		}
	
	} else {
		$arr_posts = array(); // empty array to avoid counting errors later, in case no posts were retrieved
	}
        
    // Get array of fields which apply to the given post_type -- basic fields as opposed to ACF fields
    $arr_core_fields = array( 'post_title', 'content', 'excerpt', 'post_thumbnail' );
    // post_title, content, excerpt, post_thumbnail (featured image)
    // author, post_status, date published, date last modified -- read only?
    //$arr_fields = get_fields...;
    //$info .= print_r($arr_fields, true); // tft
    
    // Get all ACF field groups associated with the post_type
    $field_groups = acf_get_field_groups( array( 'post_type' => $post_type ) );
    //$troubleshooting .= "field_groups for post_type '$post_type': <pre>".print_r($field_groups,true)."</pre>";
    /*$troubleshooting .= "field_groups for post_type '$post_type': <pre>";
    foreach ( $field_groups as $field_group ) {
    	$troubleshooting .= $field_group['title']."<br />";
    }
    $troubleshooting .= "</pre>";*/
    
    // Get all taxonomies associated with the post_type
    $taxonomies = get_object_taxonomies( $post_type );
    $troubleshooting .= "taxonomies for post_type '$post_type': <pre>".print_r($taxonomies,true)."</pre>";
    
    // TODO: Make one big array of field_name & p1/p2 values from core_fields, field_groups, and taxonomies, and process that into rows...
    
    $info .= '<form method="post" class="sdg_merge_form '.$form_type.'">'; // action? method?
    
    // TODO: add field(s) for submitting post_ids for merging?
    
    if ( count($arr_posts) == 2 ) {
		
		// TODO: give user choice of which post to treat as primary?
		$p1 = $arr_posts[0];
		$p2 = $arr_posts[1];
		$arr_fields = array(); // $arr_fields['field_name'] = array(field_cat, field_type, values...) -- field categories are: core_field, acf_field, or taxonomy;
    	
    	// Get and compare last modified dates
    	$p1_modified = $p1->post_modified;
    	$p2_modified = $p2->post_modified;
    	// Prioritize the post which was most recently modified by putting it in first position
    	// In other words, swap p1/p2 if second post is newer
    	if ( $p1_modified < $p2_modified ) {
    		$p1 = $arr_posts[1];
			$p2 = $arr_posts[0];
    	}
    	
    	// Assemble general post info for table header
    	$p1_info = "[".$p1->ID."] ".$p1->post_modified." (".get_the_author_meta('user_nicename',$p1->post_author).")";
    	$p2_info = "[".$p2->ID."] ".$p2->post_modified." (".get_the_author_meta('user_nicename',$p2->post_author).")";
    	//$info .= 'p1: <pre>'.print_r($p1,true).'</pre>';
    	//$info .= 'p2: <pre>'.print_r($p2,true).'</pre>';
    	$info .= "<pre>";
    	$info .= "Post #1 >> Last modified: ".$p1->post_modified."; author: ".get_the_author_meta('user_nicename',$p1->post_author)."; ID: ".$p1->ID."<br />";
    	$info .= "Post #2 >> Last modified: ".$p2->post_modified."; author: ".get_the_author_meta('user_nicename',$p2->post_author)."; ID: ".$p2->ID."<br />";
    	$info .= "</pre>";
    	/*$info .= '<table>';
		$info .= '<tr>';
		$info .= '<th>'.$p1->ID.'</th><th>'.$p1->post_modified.'</th><th>'.get_the_author_meta('user_nicename',$p1->post_author).'</th>';
		$info .= '<th>'.$p2->ID.'</th><th>'.$p2->post_modified.'</th><th>'.get_the_author_meta('user_nicename',$p2->post_author).'</th>';
		$info .= '</tr>';
		$info .= '</table>';*/
		
		// TODO: tag which fields are ok to edit manually, to avoid trouble -- e.g. editions; choirplanner_id, &c. should be RO
		// TODO: include editing instructions -- e.g. separate category list with semicolons (not commas!)
		
		
		// Get core values for both posts
		foreach ( $arr_core_fields as $field_name ) {
			
			$field_type = "text";
			$field_label = ""; // tft
			
			$p1_val = $p1->$field_name;
			$p2_val = $p2->$field_name;
			
			$merged = merge_field_values($p1_val, $p2_val);
			$merge_value = $merged['merge_value'];
			$merge_info = $merged['info'];
			
			$arr_fields[$field_name] = array('field_cat' => "core_field", 'field_type' => $field_type, 'field_label' => $field_label, 'p1_val' => $p1_val, 'p2_val' => $p2_val, 'merge_val' => $merge_value, 'merge_info' => $merge_info);
			//$arr_fields[$field_name] = array("core_field", $p1_val, $p2_val, $merge_value, $merge_info);
			
		}
		
		// Get meta values for both posts
		foreach ( $field_groups as $group ) {

			$group_key = $group['key'];
			//$info .= "group: <pre>".print_r($group,true)."</pre>"; // tft
			$group_title = $group['title'];
			$group_fields = acf_get_fields($group_key); // Get all fields associated with the group
			//$field_info .= "<hr /><strong>".$group_title."/".$group_key."] ".count($group_fields)." group_fields</strong><br />"; // tft

			$i = 0;
			foreach ( $group_fields as $group_field ) {

				$merge_value = null; // init
				$merge_info = "";
				
				// field_object parameters include: key, label, name, type, id -- also potentially: 'post_type' for relationship fields, 'sub_fields' for repeater fields, 'choices' for select fields, and so on
				$field_name = $group_field['name'];
				$field_label = $group_field['label'];
				$field_type = $group_field['type'];
				
				$p1_val = get_field($field_name, $p1->ID, false);
				$p2_val = get_field($field_name, $p2->ID, false);
				
				// If a value was retrieved for either post, then display more info about the field object (tft)
				if ( $p1_val || $p1_val ) {				
					if ( $field_name == "choir_voicing" ) {
					//$info .= "Field object ($field_name): <pre>".print_r($group_field,true)."</pre><br />";
					}					
				}
				
				$merged = merge_field_values($p1_val, $p2_val);
				$merge_val = $merged['merge_value'];
				$merge_info = $merged['info'];
			
				$arr_fields[$field_name] = array('field_cat' => "acf_field", 'field_type' => $field_type, 'field_label' => $field_label, 'p1_val' => $p1_val, 'p2_val' => $p2_val, 'merge_val' => $merge_val, 'merge_info' => $merge_info );
				//$arr_fields[$field_name] = array("acf_field", $p1_val, $p2_val, $merge_value, $merge_info);
			
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
				
				$i++;

			}

			if ( $field ) { 
				//$field_info .= "break.<br />";
				break;  // Once the field has been matched to a post_type field, there's no need to continue looping
			}

		} // END foreach ( $field_groups as $group )
		
		// Get terms applied to both posts
		foreach ( $taxonomies as $taxonomy ) {
			
			$field_type = "TMP";
			$field_label = "";
			
			// Get terms... WIP
			$p1_val = wp_get_post_terms( $p1->ID, $taxonomy, array( 'fields' => 'names' ) );
			$p2_val = wp_get_post_terms( $p2->ID, $taxonomy, array( 'fields' => 'names' ) );
			
			$merged = merge_field_values($p1_val, $p2_val);
			$merge_value = $merged['merge_value'];
			$merge_info = $merged['info'];
		
			$arr_fields[$field_name] = array('field_cat' => "taxonomy", 'field_type' => $field_type, 'field_label' => $field_label, 'p1_val' => $p1_val, 'p2_val' => $p2_val, 'merge_val' => $merge_value, 'merge_info' => $merge_info);
			//$arr_fields[$taxonomy] = array("taxonomy", $p1_val, $p2_val, $merge_value, $merge_info);
			
			/* e.g.
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
			*/
		}
		
		// Get related posts for both posts (events, &c?)
		// WIP
		
		// Build the table for review of post & merge values		
		$info .= '<table class="pre">';
		$info .= '<tr><th style="width:5px;">&nbsp;</th><th width="100px">Field Type</th><th width="180px">Field Name</th><th>P1 Value</th><th>Merge Value</th><th>P2 Value</th></tr>';
		
		foreach ( $arr_fields as $field_name => $values ) {
		
			$field_cat = $values['field_cat'];
			$field_type = $values['field_type'];
			$field_label = $values['field_label'];
			$p1_val = $values['p1_val'];
			$p2_val = $values['p2_val'];
			$merge_value = $values['merge_val'];
			$merge_info = $values['merge_info'];
			
			if ( is_array($p1_val) ) { $p1_val_str = "<pre>".print_r($p1_val,true)."</pre>"; } else { $p1_val_str = $p1_val; }
			if ( is_array($p2_val) ) { $p2_val_str = "<pre>".print_r($p2_val,true)."</pre>"; } else { $p2_val_str = $p2_val; }
			if ( is_array($merge_value) ) { 
				$merge_value_str = implode("; ",$merge_value);
				$merge_info .= "(".count($merge_value)." item array)";
			} else {
				$merge_value_str = $merge_value;
			}
			//if ( is_array($merge_value) ) { $merge_value_str = "<pre>".print_r($merge_value,true)."</pre>"; } else { $merge_value_str = $merge_value; }
			if ( $p1_val == $merge_value ) { $p1_class = "merged_val"; } else { $p1_class = "tbx"; }
			if ( $p2_val == $merge_value ) { $p2_class = "merged_val"; } else { $p2_class = "tbx"; }
			if ( !empty($merge_info) ) { $merge_info = ' <span class="merge_info">'.$merge_info.'</span>'; }
			//if ( !empty($merge_info) ) { $merge_info = ' ['.$merge_info.']'; }
			
			if ( !(empty($p1_val) && empty($p2_val)) ) {
				$info .= '<tr>';
				$info .= '<td>'.'<input type="hidden" name="test_input" value="test_val">'.'</td>';
				$info .= '<td>'.$field_cat.'</td>';
				$info .= '<td>'.$field_name;
				if ( !empty($field_label) ) { $info .= '<br />('.$field_label.')'; }
				$info .= '</td>';
				$info .= '<td class="'.$p1_class.'">'.$p1_val_str.'</td>';
				// TODO: set input type based on field_type -- see corresponding ACF fields e.g. select for fixed options; checkboxes for taxonomies... &c.
				// TODO: set some inputs with readonly attribute and class="readonly" to make it obvious to user
				//$readonly = " readonly";
				//$input_class = ' class="readonly"';
				// Deal w/ title_for_matching -- will be auto-regenerated, so manual editing is pointless
				//field_type: relationship
				//field_type: number -- e.g. choirplanner_id (legacy data)
				//
				if ( $field_type == "text" || $field_type == "textarea" ) {
					$info .= '<td><textarea name="'.$field_name.'" rows="5" columns="20">'.$merge_value_str.'</textarea>'.$merge_info.'</td>';
				} else {
					$info .= '<td>field_type: '.$field_type.'<br /><span class="nb">'.$merge_value_str.'</span>'.$merge_info.'</td>';
				}				
				//$info .= '<td><textarea name="'.$field_name.'" rows="5" columns="20">'.$merge_value_str.'</textarea>'.$merge_info.'</td>';
				//$info .= '<td><input type="text" name="'.$field_name.'" value="'.$merge_value_str.'" />'.$merge_info.'</td>';
				//$info .= '<td><span class="nb">'.$merge_value_str.'</span>'.$merge_info.'</td>';
				$info .= '<td class="'.$p2_class.'">'.$p2_val_str.'</td>';
				$info .= '</tr>';
			}
				
		}
		
		$info .= '</table>';
    }
    
    /*
        // Loop through the field names and create the actual form fields
        foreach ( $arr_fields as $arr_field ) {
            
            $field_info = ""; // init
            $field_name = $arr_field; // may be overrriden below
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
            } else {
                $placeholder = $field_name; // for input field
            }
            
            if ( $form_type == "advanced_search" ) {
                $field_label = str_replace("_", " ",ucfirst($placeholder));
                if ( $field_label == "Repertoire category" ) { 
                    $field_label = "Category";
                } else if ( $field_name == "liturgical_date" || $field_label == "Related liturgical dates" ) { 
                    $field_label = "Liturgical Dates";
                    $field_name = "repertoire_litdates";
                    $alt_field_name = "related_liturgical_dates";
                }
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
                $pick_object = null; // ?pods?
                $pick_custom = null; // ?pods?
                $field = null;
                $field_value = null;
                
                // First, deal w/ title field -- special case
                if ( $field_name == "post_title" ) {
                    $field = array( 'type' => 'text', 'name' => $field_name );
                }
                
                // Check to see if a field by this name is associated with the designated post_type -- for now, only in use for repertoire(?)
                $field = match_group_field( $field_groups, $field_name );
                
                if ( $field ) {
                    
                    // if field_name is same as post_type, must alter it to prevent automatic redirect when search is submitted -- e.g. "???"
                    if ( post_type_exists( $arr_field ) ) {
                        $field_name = $post_type."_".$arr_field;
                    }
                    
                    $query_assignment = "primary";
                    
                } else {
                    
                    //$field_info .= "field_name: $field_name -- not found for $post_type >> look for related field.<br />"; // tft
                    
                    // If no matching field was found in the primary post_type, then
                    // ... get all ACF field groups associated with the related_post_type(s)                    
                    $related_field_groups = acf_get_field_groups( array( 'post_type' => $related_post_type ) );
                    $field = match_group_field( $related_field_groups, $field_name );
                                
                    if ( $field ) {
                        
                        // if field_name is same as post_type, must alter it to prevent automatic redirect when search is submitted -- e.g. "publisher" => "edition_publisher"
                        if ( post_type_exists( $arr_field ) ) {
                            $field_name = $related_post_type."_".$arr_field;
                        }
                        $query_assignment = "related";
                        $field_info .= "field_name: $field_name found for related_post_type: $related_post_type.<br />"; // tft    
                        
                    } else {
                        
                        // Still no field found? Check taxonomies 
                        //$field_info .= "field_name: $field_name -- not found for $related_post_type either >> look for taxonomy.<br />"; // tft
                        
                        // For field_names matching taxonomies, check for match in $taxonomies array
                        if ( taxonomy_exists( $field_name ) ) {
                            
                            $field_info .= "$field_name taxonomy exists.<br />";
                                
                            if ( in_array($field_name, $taxonomies) ) {

                                $query_assignment = "primary";                                    
                                $field_info .= "field_name $field_name found in primary taxonomies array<br />";

                            } else {

                                // Get all taxonomies associated with the related_post_type
                                $related_taxonomies = get_object_taxonomies( $related_post_type );

                                if ( in_array($field_name, $related_taxonomies) ) {

                                    $query_assignment = "related";
                                    $field_info .= "field_name $field_name found in related taxonomies array<br />";                                        

                                } else {
                                    $field_info .= "field_name $field_name NOT found in related taxonomies array<br />";
                                }
                                //$info .= "taxonomies for post_type '$related_post_type': <pre>".print_r($related_taxonomies,true)."</pre>"; // tft

                                $field_info .= "field_name $field_name NOT found in primary taxonomies array<br />";
                            }
                            
                            $field = array( 'type' => 'taxonomy', 'name' => $field_name );
                            
                        } else {
                            $field_info .= "Could not determine field_type!<br />";
                        }
                    }
                }                
                
                if ( $field ) {
                    
                    //$field_info .= "field: <pre>".print_r($field,true)."</pre>"; // tft
                    
                    if ( isset($field['post_type']) ) { $field_post_type = $field['post_type']; } else { $field_post_type = null; } // ??
                    
                    // Check to see if a custom post type or taxonomy exists with same name as $field_name
                    // In the case of the choirplanner search form, this will be relevant for post types such as "Publisher" and taxonomies such as "Voicing"
                    if ( post_type_exists( $arr_field ) || taxonomy_exists( $arr_field ) ) {
                        $field_cptt_name = $arr_field;
                        //$field_info .= "field_cptt_name: $field_cptt_name same as arr_field: $arr_field<br />"; // tft
                    } else {
                        $field_cptt_name = null;
                    }

                    //
                    $field_info .= "field_name: $field_name<br />"; // tft
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
                            //$troubleshooting .= "query_assignment for field_name $field_name is *$query_assignment* >> search value: '$field_value'<br />";
                            
                            if ( $query_assignment == "primary" ) {
                                $search_primary_post_type = true;
                                $troubleshooting .= ">> Setting search_primary_post_type var to TRUE based on field $field_name searching value $field_value<br />";
                            } else {
                                $search_related_post_type = true;
                                $troubleshooting .= ">> Setting search_related_post_type var to TRUE based on field $field_name searching value $field_value<br />";
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
                    
                    //if ( ( $field_name == "post_title" || $field_name == "title_clean" ) && !empty($field_value) ) {
                    
                    if ( $field_name == "post_title" && !empty($field_value) ) {
                        
                        //$args['s'] = $field_value;
                        $args['_search_title'] = $field_value; // custom parameter -- see posts_where filter fcn

                    } else if ( $field_type == "text" && !empty($field_value) ) { 
                        
                        // TODO: figure out how to determine whether to match exact or not for particular fields
                        // -- e.g. box_num should be exact, but not necessarily for title_clean?
                        // For now, set it explicitly per field_name
                        //if ( $field_name == "box_num" ) {
                         //   $match_value = '"' . $field_value . '"'; // matches exactly "123", not just 123. This prevents a match for "1234"
                       // } else {
                         //   $match_value = $field_value;
                        //}
                        $match_value = $field_value;
                        //$mq_components[] =  array(
                        $query_component = array(
                            'key'   => $field_name,
                            'value' => $match_value,
                            'compare'=> 'LIKE'
                        );
                        
                        // Add query component to the appropriate components array
                        if ( $query_assignment == "primary" ) {
                            $mq_components_primary[] = $query_component;
                        } else {
                            $mq_components_related[] = $query_component;
                        }
                        
                        $field_info .= ">> Added $query_assignment meta_query_component for key: $field_name, value: $match_value<br/>";

                    } else if ( $field_type == "select" && !empty($field_value) ) { 
                        
                        // If field allows multiple values, then values will return as array and we must use LIKE comparison
                        if ( $field['multiple'] == 1 ) {
                            $compare = 'LIKE';
                        } else {
                            $compare = '=';
                        }
                        
                        $match_value = $field_value;
                        $query_component = array(
                            'key'   => $field_name,
                            'value' => $match_value,
                            'compare'=> $compare
                        );
                        
                        // Add query component to the appropriate components array
                        if ( $query_assignment == "primary" ) {
                            $mq_components_primary[] = $query_component;
                        } else {
                            $mq_components_related[] = $query_component;
                        }                        
                        
                        $field_info .= ">> Added $query_assignment meta_query_component for key: $field_name, value: $match_value<br/>";

                    } else if ( $field_type == "relationship" ) { // && !empty($field_value) 

                        $field_post_type = $field['post_type'];                        
                        // Check to see if more than one element in array. If not, use $field['post_type'][0]...
                        if ( count($field_post_type) == 1) {
                            $field_post_type = $field['post_type'][0];
                        } else {
                            // ???
                        }
                        
                        $field_info .= "field_post_type: ".print_r($field_post_type,true)."<br />";
                        
                        if ( !empty($field_value) ) {
                            
                            $field_value_converted = ""; // init var for storing ids of posts matching field_value
                            
                            // If $options,
                            if ( !empty($options) ) {
                                
                                if ( $arr_field == "publisher" ) {
                                    $key = $arr_field; // can't use field_name because of redirect issue
                                } else {
                                    $key = $field_name;
                                }
                                $query_component = array(
                                    'key'   => $key, 
                                    //'value' => $match_value,
                                    // TODO: FIX -- value as follows doesn't work w/ liturgical dates because it's trying to match string, not id... need to get id!
                                    'value' => '"' . $field_value . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                                    'compare'=> 'LIKE', 
                                );

                                // Add query component to the appropriate components array
                                if ( $query_assignment == "primary" ) {
                                    $mq_components_primary[] = $query_component;
                                } else {
                                    $mq_components_related[] = $query_component;
                                }
                                
                                if ( $alt_field_name ) {
                                    
                                    $meta_query['relation'] = 'OR';
                                    
                                    $query_component = array(
                                        'key'   => $alt_field_name,
                                        //'value' => $field_value,
                                        // TODO: FIX -- value as follows doesn't work w/ liturgical dates because it's trying to match string, not id... need to get id!
                                        'value' => '"' . $field_value . '"',
                                        'compare'=> 'LIKE'
                                    );
                                    
                                    // Add query component to the appropriate components array
                                    if ( $query_assignment == "primary" ) {
                                        $mq_components_primary[] = $query_component;
                                    } else {
                                        $mq_components_related[] = $query_component;
                                    }
                                    
                                }
                                
                            } else {
                                
                                // If no $options, match search terms
                                $field_info .= "options array is empty.<br />";
                                
                                // Get id(s) of any matching $field_post_type records with post_title like $field_value
                                $field_value_args = array('post_type' => $field_post_type, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', '_search_title' => $field_value, 'suppress_filters' => FALSE );
                                $field_value_posts = get_posts( $field_value_args );
                                if ( count($field_value_posts) > 0 ) {

                                    $field_info .= count($field_value_posts)." field_value_posts found<br />";
                                    //$field_info .= "field_value_args: <pre>".print_r($field_value_args, true)."</pre><br />";

                                    // The problem here is that, because ACF stores multiple values as a single meta_value array, 
                                    // ... it's not possible to search efficiently for an array of values
                                    // TODO: figure out if there's some way to for ACF to store the meta_values in separate rows?
                                    
                                    $sub_query = array();
                                    
                                    if ( count($field_value_posts) > 1 ) {
                                        $sub_query['relation'] = 'OR';
                                    }
                                    
                                    // TODO: make this a subquery to better control relation
                                    foreach ( $field_value_posts as $fvp_id ) {
                                        $sub_query[] = [
                                            'key'   => $arr_field, // can't use field_name because of "publisher" issue
                                            //'key'   => $field_name,
                                            'value' => '"' . $fvp_id . '"',
                                            'compare' => 'LIKE',
                                        ];
                                    }
                                    
                                    // Add query component to the appropriate components array
                                    if ( $query_assignment == "primary" ) {
                                        $mq_components_primary[] = $sub_query;
                                    } else {
                                        $mq_components_related[] = $sub_query;
                                    }
                                    //$mq_components_primary[] = $sub_query;
                                }
                                
                            }
                            
                            //$field_info .= ">> WIP: set meta_query component for: $field_name = $field_value<br/>";
                            $field_info .= "Added meta_query_component for key: $field_name, value: $field_value<br/>";
                            
                        }
                        
                        // For text fields, may need to get ID matching value -- e.g. person id for name mousezart (220824), if composer field were not set up as combobox -- maybe faster?
                        

                    } else if ( $field_type == "taxonomy" && !empty($field_value) ) {

                        $query_component = array (
                            'taxonomy' => $field_name,
                            //'field'    => 'slug',
                            'terms'    => $field_value,
                        );
                        
                        // Add query component to the appropriate components array
                        if ( $query_assignment == "primary" ) {
                            $tq_components_primary[] = $query_component;
                        } else {
                            $tq_components_related[] = $query_component;
                        }

                        if ( $post_type == "repertoire" ) {

                            // Since rep & editions share numerous taxonomies in common, check both
                            
                            $related_field_name = 'repertoire_editions'; //$related_field_name = 'related_editions';
                            
                            $field_info .= ">> WIP: field_type: taxonomy; field_name: $field_name; post_type: $post_type; terms: $field_value<br />"; // tft
                            
                        }

                    }
                    
                    //$field_info .= "-----<br />";
                    
                } // END if ( $field )
        */
        /*   
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
                    
                    } else if ( $field_type == "select" ) {
                        
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
                        
                        if ( $field_cptt_name != $arr_field ) {
                        //if ( $field_cptt_name != $field_name ) {
                            
                            $field_info .= "field_cptt_name NE arr_field<br />"; // tft
                            //$field_info .= "field_cptt_name NE field_name<br />"; // tft
                            
                            // TODO: 
                            if ( $field_post_type && $field_post_type != "person" && $field_post_type != "publisher" ) { // TMP disable options for person fields so as to allow for free autocomplete
                                
                                // TODO: consider when to present options as combo box and when to go for autocomplete text
                                // For instance, what if the user can't remember which Bach wrote a piece? Should be able to search for all...
                                
                                // e.g. field_post_type = person, field_name = composer 
                                // ==> find all people in Composers people_category -- PROBLEM: people might not be correctly categorized -- this depends on good data entry
                                // -- alt: get list of composers who are represented in the music library -- get unique meta_values for meta_key="composer"

                                // TODO: figure out how to filter for only composers related to editions? or lit dates related to rep... &c.
                                // TODO: find a way to do this more efficiently, perhaps with a direct wpdb query to get all unique meta_values for relevant keys
                                
                                //
                                // set up WP_query
                                $options_args = array(
                                    'post_type' => $post_type, //'post_type' => $field_post_type,
                                    'post_status' => 'publish',
                                    'fields' => 'ids',
                                    'posts_per_page' => -1, // get them all
                                    'meta_query' => array(
                                        'relation' => 'OR',
                                        array(
                                            'key'     => $field_name,
                                            'compare' => 'EXISTS'
                                        ),
                                        array(
                                            'key'     => $alt_field_name,
                                            'compare' => 'EXISTS'
                                        ),
                                    ),
                                );
                                
                                $options_arr_posts = new WP_Query( $options_args );
                                $options_posts = $options_arr_posts->posts;

                                //$field_info .= "options_args: <pre>".print_r($options_args,true)."</pre>"; // tft
                                $field_info .= count($options_posts)." options_posts found <br />"; // tft
                                //$field_info .= "options_posts: <pre>".print_r($options_posts,true)."</pre>"; // tft

                                $arr_ids = array(); // init

                                foreach ( $options_posts as $options_post_id ) {

                                    // see also get composer_ids
                                    $meta_values = get_field($field_name, $options_post_id, false);
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

                            }

                            asort($options);

                        } else {
                        	
                        	$input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="'.$input_class.'" />';                                            
                    
                    		//$input_html = "LE TSET"; // tft
                        	//$input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="autocomplete '.$input_class.' relationship" />';
                        }
                        
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
                        
                        $field_info .= "field_type could not be determined.";
                    }
                    
                    if ( !empty($options) ) { // WIP // && strpos($input_class, "combobox")

                        //if ( !empty($field_value) ) { $troubleshooting .= "options: <pre>".print_r($options, true)."</pre>"; } // tft

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
                    $input_class = "simple_merge";
                    $info .= '<input type="text" id="'.$field_name.'" name="'.$field_name.'" placeholder="'.$placeholder.'" value="'.$field_value.'" class="'.$input_class.'" />';
                }
                
                if ( $form_type == "advanced_search" ) {
                    $info .= '<br />';
                    //$info .= '<!-- '."\n".$field_info."\n".' -->';
                }
                
                //$troubleshooting .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />";
                //if ( strpos($field_name, "publisher") || strpos($field_name, "devmode") || strpos($arr_field, "devmode") || $field_name == "devmode" ) {
                if ( (!empty($field_value) && $field_name != 'search_operator' && $field_name != 'devmode' ) ||
                   ( !empty($options_posts) && count($options_posts) > 0 ) ||
                   strpos($field_name, "liturgical") ) {
                    $troubleshooting .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />";
                }
                //$field_name == "liturgical_date" || $field_name == "repertoire_litdates" || 
                //if ( !empty($field_value) ) { $troubleshooting .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />"; }
                
            } // End conditional for actual search fields
            
        } // end foreach ( $arr_fields as $field_name )
        
        */
        
        $info .= '<input type="submit" value="Merge Records">';
        $info .= '<a href="#!" id="form_reset">Clear Form</a>';
        $info .= '</form>';        
        
        // 
        $args_related = null; // init
        $mq_components = array();
        $tq_components = array();
        
        //$troubleshooting .= "mq_components: <pre>".print_r($mq_components,true)."</pre>";
        //$troubleshooting .= "tq_components: <pre>".print_r($tq_components,true)."</pre>";
        
        // Finalize meta_query or queries
        // ==============================
        /*
		if ( count($mq_components) > 1 && empty($meta_query['relation']) ) {
			$meta_query['relation'] = $search_operator;
		}
		if ( count($mq_components) == 1) {
			//$troubleshooting .= "Single mq_component.<br />";
			$meta_query = $mq_components; //$meta_query = $mq_components[0];
		} else {
			foreach ( $mq_components AS $component ) {
				$meta_query[] = $component;
			}
		}
		
		if ( !empty($meta_query) ) { $args['meta_query'] = $meta_query; }
        
        
        // Finalize tax_query or queries
        // =============================
        
            
		if ( count($tq_components) > 1 && empty($tax_query['relation']) ) {
			$tax_query['relation'] = $search_operator;
		}
		foreach ( $tq_components AS $component ) {			
			$tax_query[] = $component;
		}
		if ( !empty($tax_query) ) { $args['tax_query'] = $tax_query; }
        */
        
    
    $info .= '<div class="troubleshootingX">';
    $info .= $troubleshooting;
    $info .= '</div>';
    
    return $info;
    
}


/*********** MEDIA ***********/

// Get a linked list of Media Items
add_shortcode('list_media_items', 'sdg_list_media_items');
function sdg_list_media_items ($atts = [] ) {

    global $wpdb;
    
	$info = "";
    $mime_types = array();
	
	$a = shortcode_atts( array(
      	'type'        => null,
		'category'    => null,
		'grouped_by'  => null,
    ), $atts );
	
    if ($a['type'] == "pdf") {
        $mime_types[] = "application/pdf";
    } else {
        $mime_types[] = $a['type'];
    }
    
    //$unsupported_mimes  = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
    //$all_mimes          = get_allowed_mime_types();
    //$mime_types       = array_diff( $all_mimes, $unsupported_mimes );
    
    $category = $a['category'];
    $grouped_by = $a['grouped_by'];
	
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    if ( !empty($mime_types) ) {
        $args['post_mime_type'] = $mime_types;
    }
    //'post_mime_type' => 'image/gif',
    
    if ( $category !== null ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' 	=> 'media_category',
                'field' 	=> 'slug',
                'terms' 	=> $category
            )
        );
    }

    $arr_posts = new WP_Query( $args );
    $posts = $arr_posts->posts;
    //$info .= print_r($arr_posts, true);
    //$info .= "<!-- Last SQL-Query: ".$wpdb->last_query." -->";
    
    if ( !empty( $posts ) && !is_wp_error( $posts ) ){
		
        $info .= '<div class="media_list">';
        // init
        $items  = array();
        $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $litdates = array('ash_wednesday_date' => 'Ash Wednesday', 'easter_date' => 'Easter', 'pentecost_date' => 'Pentecost');
        $the_year = "";
        
        // Loop through the posts; built items array
        foreach ( $posts as $post ) {

            setup_postdata( $post );
            
            $title = $post->post_title;
            $post_id = $post->ID;
            $url = wp_get_attachment_url($post_id); // get_attachment_link($post_id);
            
            // Don't display the words "Music List", if present in the title
			if (strpos(strtolower($title), 'music list') !== false) { 
                $title = str_ireplace("Music List", "", $title);
			}
            
            if ( $grouped_by == "year" ) {
                
                // init
                $start_month = "";
                $end_month = "";
                
                // Extract year from filename
                $pattern = '/((19|20)\d{2})/';
                if ( preg_match($pattern, $title, $matches, PREG_OFFSET_CAPTURE) ) {
                    $year = trim($matches[0][0]);
                } else {
                    $year = null;
                }
                
                // For Music Lists, don't display the year in the title
                if ( $category == "music-lists" ) {
                    $title = str_ireplace($year, "", $title);
                }   
                
                // Get liturgical date calc info per year, in order to deal w/ lists named according to holidays (Easter, Ash Wednesday, Pentecost) instead of months
                // e.g. January-Easter 2019; Easter-September 2015
                if ( $year != $the_year ) {
                    
                    $the_year = $year;
                    
                    $args = array(
                        'post_type'   => 'liturgical_date_calc',
                        'post_status' => 'publish',
                        'posts_per_page' => 1,
                        'meta_query' => array(
                            array(
                                'key'     => 'litdate_year',
                                'value'   => $year.'-01-01'
                            )
                        )
                    );
                    $liturgical_date_calc_post_id = null; // init
                    $liturgical_date_calc_post_obj = new WP_Query( $args );
                    if ( $liturgical_date_calc_post_obj ) { 
                        $liturgical_date_calc_post = $liturgical_date_calc_post_obj->posts;
                        $liturgical_date_calc_post_id = $liturgical_date_calc_post[0]->ID;
                        $info .= "<!-- Found liturgical_date_calc_post for year $year with ID: ".$liturgical_date_calc_post[0]->ID." -->";
                        //$info .= "<!-- Found liturgical_date_calc_post for year $year: ".print_r($liturgical_date_calc_post, true)." >> ID: ".$liturgical_date_calc_post[0]->ID." -->"; // tft
                    } else { 
                        $info .= "<!-- NO liturgical_date_calc_post found for year $year -->"; 
                    } // tft
                    
                }
                
                foreach ( $months AS $i => $month ) {
                    
                    $num = (string)$i+1;
                    //$num = (string)$num;
                    $numlength = strlen($num);
                    if ($numlength == 1) {
                        $num = "0".$num;
                    }
                    
                    if (stripos($title, $month."-") !== false) {
                        $start_month = $num;
                        $info .= "<!-- Found start_month: $start_month from title: $title -->"; // tft
                    } else if (stripos($title, $month) !== false && stripos($title, "-") === false) {
                        $start_month = $num;
                        $info .= "<!-- Found start_month: $start_month from title: $title (no hyphens) -->"; // tft
                    }
                    
                    if (stripos($title, "-".$month) !== false) {
                        $end_month = $num;
                        $info .= "<!-- Found end_month: $end_month from title: $title -->"; // tft
                    } 
                }
                
                // If no start_month was found, look for: Easter, Ash Wednesday, Pentecost
                if ( $start_month == "" && $liturgical_date_calc_post_id ) {
                    $info .= "<!-- [post_id: $post_id] No start_month found >> try via litdates -->"; // tft
                    foreach ( $litdates AS $date_field => $litdate ) {
                        if (stripos($title, $litdate."-") !== false) {
                            $info .= "<!-- Found litdate match (start): $litdate in title: $title -->"; // tft
                            $start_date = get_post_meta( $liturgical_date_calc_post_id, $date_field, true);
                            $info .= "<!-- Found start_date via litdate: $start_date (date_field: $date_field) -->"; // tft
                            $start_month = date('m', strtotime($start_date) );
                        } else if (stripos($title, $litdate."-") !== false) {
                            $info .= "<!-- Found litdate match (end): $litdate in title: $title -->"; // tft
                            $end_date = get_post_meta( $liturgical_date_calc_post_id, $date_field, true);
                            $end_month = date('m', strtotime($start_date) );
                        }
                    }
                }
                
                $sort_date = $year.$start_month;                
                $items[] = array('id' => $post_id, 'title' => $title, 'url' => $url, 'year' => $year, 'sort_date' => $sort_date, 'start_month' => $start_month, 'end_month' => $end_month);
                
            } else {
                $items[] = array('id' => $post_id, 'title' => $title, 'url' => $url);
            }
            
            $info .= "<!-- +-----+ -->";

        }
        
        if ( $grouped_by == "year" ) {
            usort($items, sdg_arr_sort('value', 'sort_date', 'DESC'));
        }
        
        $the_year = ""; // reset
        foreach ( $items as $item ) {

            if ( $item['year'] != $the_year ) {
                $the_year = $item['year'];
                $info .= '<h2>'.$the_year.'</h2>';
            }
            if ( $grouped_by == "year" ) {
                $info .= "<!-- ".$item['sort_date']." -->";
            }
            $info .= '<a href="'.$item['url'].'" target="_new">'.$item['title'].'</a>';
            $info .= '<br />';

        }
        
        $info .= '</div>'; // close media_list div
        
    } else {
		$info .= "<p>No items found.</p>";
        $info .= "Last SQL-Query: ".$wpdb->last_query."";
        //$info .= "<!-- Last SQL-Query: ".$wpdb->last_query." -->";
	}
	return $info;
}

?>