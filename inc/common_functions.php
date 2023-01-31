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
	
	// What type of values have we got?
	if ( is_object($p1_val) || is_object($p2_val) ) {
		// If one or more value is of type 'object', then...???
		$merge_value = "OBJECT(S)!"; // tft
	} else if ( is_array($p1_val) && is_array($p2_val) ) {
		// If both values are arrays, then merge them
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
    if ( !empty($_POST) ) { 
    	$troubleshooting .= '_POST: <pre>'.print_r($_POST,true).'</pre>';
    	// WIP/TODO: Update p1 with merged values
    	//$troubleshooting .= "About to save merged values to p1 [".$_POST['p1_id']."]<br />";
    	//
    	// Save content (only if previously empty)
    	// Update core fields
		/*
		$data = array(
			'ID' => $post_id,
			'post_content' => $content,
			'meta_input' => array(
			'meta_key' => $meta_value,
			'another_meta_key' => $another_meta_value
		)
		);

		wp_update_post( $data, true );
		if (is_wp_error($post_id)) { // ?? if (is_wp_error($data)) {
			$errors = $post_id->get_error_messages();
			foreach ($errors as $error) {
				$info .= $error;
			}
		}
        */
        // Update ACF fields:
        //update_field($selector, $value, [$post_id]);
        // Update post-meta:
        /*
        if ( in_array('last_mod', $arr_updates) ) {
			if ( update_post_meta( $post_id, 'html_last_modified', wp_slash( $html_last_modified ) ) ) {
			//if ( update_post_meta( $post_id, 'html_last_modified', $html_last_modified ) ) {
				$info .= "Update OK for html_last_modified postmeta<br />";
			} else {
				$info .= "No update for html_last_modified postmeta (post_id: $post_id; html_last_modified: $html_last_modified)<br />";
				$last_mod = get_post_meta( $post_id, 'html_last_modified' );
				$info .= "current value(s) for html_last_modified: ".print_r($last_mod,true)."<br />";
			}
		}*/
    	// WIP/TODO: Move p2 to trash
    	//$troubleshooting .= "About to move p2 [".$_POST['p2_id']."] to trash<br />";
    	//wp_trash_post($p2_id);
    	//
    }
    //$troubleshooting .= '_REQUEST: <pre>'.print_r($_REQUEST,true).'</pre>'; // tft
    
    // init
    $arr_posts = array(); // tft
    $form_type = 'simple_merge';
    	
    if ( isset($_POST['p1_id']) && isset($_POST['p2_id']) ) {
    
    	$info .= "Got POST ids. Prep to merge...<br />";
    	$merging = true;
    	
    	if ( !empty($_POST['p1_id']) ) {
    		$p1_id = $_POST['p1_id'];
    		$arr_posts[] = $p1_id;
    		$post_type = get_post_type($p1_id);    		
    	} else {
    		$post_type = "UNKNOWN";
    	}
    	
    	if ( !empty($_POST['p2_id']) ) {
    		$p2_id = $_POST['p2_id'];
    		$arr_posts[] = $p2_id;  		
    	}    	
    	
    	// If a merge request has been submitted, then get the relevant post IDs
    	///$arr_posts( $_POST['p1_id'], $_POST['p2_id'] );
    	//$p1 = get_post($_POST['p1_id']);
    	//$p2 = get_post($_POST['p2_id']);
    	//$arr_posts = array($p1,$p2);
    	
    } else {
    
    	$merging = false;
    	
    	// Get posts based on submitted IDs
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
			'return_fields'   => 'ids',
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
				$info .= "<p>Num arr_posts: [".count($arr_posts)."]</p>";
				$troubleshooting .= "arr_posts: <pre>".print_r($arr_posts,true)."</pre>"; // tft
			
				if ( count($arr_posts) > 2 ) {
					$troubleshooting .= "<p>That's too many posts! I can only handle two at a time.</p>";
				}
		
				//$info .= '<div class="troubleshooting">'.$posts_info['info'].'</div>';
				$troubleshooting .= $posts_info['info']."<hr />";
				//$info .= $posts_info['info']."<hr />"; //$info .= "birdhive_get_posts/posts_info: ".$posts_info['info']."<hr />";
		
				// Print last SQL query string
				//global $wpdb;
				//$info .= '<div class="troubleshooting">'."last_query:<pre>".$wpdb->last_query."</pre>".'</div>'; // tft
				//$troubleshooting .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>"; // tft
		
			}
	
		}/* else {
			$arr_posts = array(); // empty array to avoid counting errors later, in case no posts were retrieved
		}*/
    }
    
    // =====================================
        
    // Get array of fields which apply to the given post_type -- basic fields as opposed to ACF fields
    $arr_core_fields = array( 'post_title', 'content', 'excerpt', 'post_thumbnail' ); // Also?: author, post_status, date published, date last modified -- read only?
    
    // Get all ACF field groups associated with the post_type
    $field_groups = acf_get_field_groups( array( 'post_type' => $post_type ) );
    
    // Get all taxonomies associated with the post_type
    $taxonomies = get_object_taxonomies( $post_type );
    $troubleshooting .= "taxonomies for post_type '$post_type': <pre>".print_r($taxonomies,true)."</pre>";
    
    // WIP/TODO: Make one big array of field_name & p1/p2 values from core_fields, field_groups, and taxonomies, and process that into rows...
    
    $info .= '<form method="post" class="sdg_merge_form '.$form_type.'">'; // action? method?
    // TODO: add field(s) for submitting post_ids for merging?
    
    if ( count($arr_posts) == 2 ) {
		
		//$troubleshooting .= "Two posts... moving forward...<br />";
		
		// TODO: give user choice of which post to treat as primary?
		$p1_id = $arr_posts[0];
		$p2_id = $arr_posts[1];
		//$p1 = $arr_posts[0];
		//$p2 = $arr_posts[1];
		$arr_fields = array(); // $arr_fields['field_name'] = array(field_cat, field_type, values...) -- field categories are: core_field, acf_field, or taxonomy;
    	
    	if ( $merging ) {
    	
    		// Form has been submitted... About to merge...
    		//...
    		$p1 = get_post($p1_id);
    		$p2 = get_post($p2_id);
    		
    	} else {
    	
    		// If not ready to run merge, assemble info about posts-to-merge
    		$p1 = get_post($p1_id);
    		$p2 = get_post($p2_id);
    		
    		// Get and compare last modified dates
			$p1_modified = $p1->post_modified;
			$p2_modified = $p2->post_modified;
		
			// Prioritize the post which was most recently modified by putting it in first position
			// In other words, swap p1/p2 if second post is newer
			if ( $p1_modified < $p2_modified ) {
				$p1_id = $arr_posts[1];
				$p2_id = $arr_posts[0];
				$p1 = get_post($p1_id);
    			$p2 = get_post($p2_id);
			}
			
			// Assemble general post info for table header
			$p1_info = "[".$p1_id."] ".$p1->post_modified." (".get_the_author_meta('user_nicename',$p1->post_author).")";
			$p2_info = "[".$p2_id."] ".$p2->post_modified." (".get_the_author_meta('user_nicename',$p2->post_author).")";
			//$info .= 'p1: <pre>'.print_r($p1,true).'</pre>';
			//$info .= 'p2: <pre>'.print_r($p2,true).'</pre>';
			$info .= "<pre>";
			$info .= "Post #1 >> Last modified: ".$p1->post_modified."; author: ".get_the_author_meta('user_nicename',$p1->post_author)."; ID: ".$p1_id."<br />";
			$info .= "Post #2 >> Last modified: ".$p2->post_modified."; author: ".get_the_author_meta('user_nicename',$p2->post_author)."; ID: ".$p2_id."<br />";
			$info .= "</pre>";
			//
			$info .= '<input type="hidden" name="p1_id" value="'.$p1_id.'">';
			$info .= '<input type="hidden" name="p2_id" value="'.$p2_id.'">';
			
    	}
		
		// TODO: tag which fields are ok to edit manually, to avoid trouble -- e.g. editions; choirplanner_id, &c. should be RO
		// TODO: include editing instructions -- e.g. separate category list with semicolons (not commas!)
		
		// Get core values for both posts
		foreach ( $arr_core_fields as $field_name ) {
			
			$field_type = "text";
			$field_label = ""; // tft
			
			if ( $merging ) {
				
				// Do the merging...
				$old_val = $p1->$field_name;
				$new_val = "";
				if ( !empty($_POST[$field_name]) ) {
					$new_val = $_POST[$field_name];
				}
				if ( !empty($old_val) || !empty($new_val) ) {
					if ( $old_val != $new_val ) {
						$info .= "[$field_name] old_val: $old_val;<br />[$field_name] new_val: $new_val<br />";
						// update value
						$info .= "New value not same as old for -> run update<br />";
					} else {
						//$info .= "New value same as old for $field_name<br />";
					}
				}				
				
			} else {
				$p1_val = $p1->$field_name;
				$p2_val = $p2->$field_name;
			
				$merged = merge_field_values($p1_val, $p2_val);
				$merge_value = $merged['merge_value'];
				$merge_info = $merged['info'];
			
				$arr_fields[$field_name] = array('field_cat' => "core_field", 'field_type' => $field_type, 'field_label' => $field_label, 'p1_val' => $p1_val, 'p2_val' => $p2_val, 'merge_val' => $merge_value, 'merge_info' => $merge_info);
				//$arr_fields[$field_name] = array("core_field", $p1_val, $p2_val, $merge_value, $merge_info);
			}
			
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
				
				$p1_val = get_field($field_name, $p1_id, false);
				$p2_val = get_field($field_name, $p2_id, false);
			
				if ( $merging ) {
				
					// Compare old stored value w/ new merge_value, to see whether update is needed
					$old_val = $p1_val;
					if ( is_array($old_val) ) { $old_val_str = print_r($old_val, true); } else { $old_val_str = $old_val; }
					$old_val_str = trim($old_val_str);
					
					$new_val = "";
					if ( !empty($_POST[$field_name]) ) {
						$new_val = $_POST[$field_name];
					}
					if ( is_array($new_val) ) { $new_val_str = print_r($new_val, true); } else { $new_val_str = $new_val; }
					$new_val_str = trim($new_val_str);
					
					if ( !empty($old_val) || !empty($new_val) ) {
						if ( $old_val_str != $new_val_str ) {
							$info .= "[$field_name] old_val_str: '$old_val_str';<br />[$field_name] new_val_str: '$new_val_str'<br />";
							// update value
							$info .= "New value not same as old for '$field_name' -> run update<br /><br />";
							// Do the merging...
							//
						} else {
							//$info .= "[$field_name] old_val_str: '$old_val_str';<br />[$field_name] new_val_str: '$new_val_str'<br />";
							//$info .= "New value same as old for $field_name<br /><br />";
						}
					}
					
				} else {
				
					// Not merging yet
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
					/*
					$field_info .= "[$i] group_field: <pre>".print_r($group_field,true)."</pre>"; // tft
					if ( $group_field['type'] == "relationship" ) { $field_info .= "post_type: ".print_r($group_field['post_type'],true)."<br />"; }
					if ( $group_field['type'] == "select" ) { $field_info .= "choices: ".print_r($group_field['choices'],true)."<br />"; }
					*/
					
				}
				
				$i++;

			}

		} // END foreach ( $field_groups as $group )
		
		// Get terms applied to both posts
		foreach ( $taxonomies as $taxonomy ) {
			
			if ( $merging ) {
				// Do the merging...
			} else {
				//
			}
				
			$field_type = "taxonomy";
			$field_name = $taxonomy;
			$field_label = "";
			
			// Get terms... WIP
			$p1_val = wp_get_post_terms( $p1_id, $taxonomy, array( 'fields' => 'ids' ) ); // 'all'; 'names'
			$p2_val = wp_get_post_terms( $p2_id, $taxonomy, array( 'fields' => 'ids' ) );
			
			//if ( !empty($p1_val) ) { $info .= "taxonomy [$field_name] p1_val: <pre>".print_r($p1_val, true)."</pre>"; }
			//if ( !empty($p2_val) ) { $info .= "taxonomy [$field_name] p2_val: <pre>".print_r($p2_val, true)."</pre>"; }
			
			// WIP/TODO: figure out best way to display taxonomy names while storing ids for actual merge operation
			$merged = merge_field_values($p1_val, $p2_val);
			$merge_value = $merged['merge_value'];
			$merge_info = $merged['info'];
			//$merge_value = "tmp"; $merge_info = "tmp";
		
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
		
		if ( $merging ) {
			
			// ...
			
		} else {
			
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
				
					// Open row
					$info .= '<tr>';
					$info .= '<td>'.'</td>'; // '<input type="hidden" name="test_input" value="test_val">'
				
					// Field info
					$info .= '<td>'.$field_cat.'</td>';
					$info .= '<td>'.$field_name;
					if ( !empty($field_label) ) { $info .= '<br />('.$field_label.')'; }
					$info .= '</td>';
				
					// Display P1 value
					$info .= '<td class="'.$p1_class.'">';
					if ( $field_type == "taxonomy" && is_array($p1_val) ) {
						foreach ( $p1_val as $term_id ) {
							$info .= get_term( $term_id )->name."<br />";
						}
					}
					$info .= $p1_val_str;
					$info .= '</td>';
				
					// TODO: set input type based on field_type -- see corresponding ACF fields e.g. select for fixed options; checkboxes for taxonomies... &c.
					// TODO: set some inputs with readonly attribute and class="readonly" to make it obvious to user
					//$readonly = " readonly";
					//$input_class = ' class="readonly"';
					// Deal w/ title_for_matching -- will be auto-regenerated, so manual editing is pointless
					//field_type: relationship
					//field_type: number -- e.g. choirplanner_id (legacy data)
					//
				
					// Display merge value
					$info .= '<td>';
					if ( $field_type == "text" || $field_type == "textarea" ) {
						$info .= '<textarea name="'.$field_name.'" rows="5" columns="20">'.$merge_value_str.'</textarea>';
						$info .= $merge_info;
					} else if ( $field_type == "taxonomy" ) {
						if ( is_array($merge_value) ) {
							foreach ( $merge_value as $term_id ) {
								$info .= get_term( $term_id )->name."<br />";
							}
						}
						$info .= '<pre>'.print_r($merge_value, true).'</pre>';
						$info .= '<input type="hidden" name="'.$field_name.'" value="'.print_r($merge_value, true).'" />';
					} else {
						$info .= 'field_type: '.$field_type.'<br /><span class="nb">'.$merge_value_str.'</span>'.$merge_info;
						$info .= '<input type="hidden" name="'.$field_name.'" value="'.print_r($merge_value, true).'" />';					
					}
					$info .= '</td>';
					
					//$info .= '<td><textarea name="'.$field_name.'" rows="5" columns="20">'.$merge_value_str.'</textarea>'.$merge_info.'</td>';
					//$info .= '<td><input type="text" name="'.$field_name.'" value="'.$merge_value_str.'" />'.$merge_info.'</td>';
					//$info .= '<td><span class="nb">'.$merge_value_str.'</span>'.$merge_info.'</td>';
				
					// Display P2 value
					$info .= '<td class="'.$p2_class.'">';
					if ( $field_type == "taxonomy" && is_array($p2_val) ) {
						foreach ( $p2_val as $term_id ) {
							$info .= get_term( $term_id )->name."<br />";
						}
					}
					$info .= $p2_val_str;
					$info .= '</td>';
				
					// Close row
					$info .= '</tr>';
				}
				
			}
		
			$info .= '</table>';
		
		}				
		
    }
    
    if ( $merging ) {
    	// Show input(s) for new pair of IDs?
    } else {
    	$info .= '<input type="submit" value="Merge Records">';
    }
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