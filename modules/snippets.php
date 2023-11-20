<?php



/*** WIDGETS >> SNIPPETS -- WIP! ***/

// Prior to deactivating/deleting the Custom Sidebars plugin, save the cs sidebar_id to all posts for which they were active
add_shortcode('cs_sidebars_xfer', 'cs_sidebars_xfer');
function cs_sidebars_xfer ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: cs_sidebars_xfer", $do_log );
    
    // Init vars
    $info = "";
	
	// Set up basic query args for snippets retrieval
    $wp_args = array(
		'post_type'		=> 'any',
		'post_status' => array( 'private', 'draft', 'publish', 'archive' ),
		'posts_per_page'=> -1,
        'fields'		=> 'ids',
        'orderby'		=> 'meta_value',
		'order'			=> 'ASC',
        'meta_key'		=> '_cs_replacements',
	);
	
	// Meta query
	$meta_query = array(
		'_cs_replacements' => array(
			'key' => '_cs_replacements',
			'compare' => '!=',
			'value' => '',
		),
	);
	$wp_args['meta_query'] = $meta_query;
	
	$arr_posts = new WP_Query( $wp_args );
	$posts = $arr_posts->posts;
    //$info .= "WP_Query run as follows:";
    //$info .= "<pre>args: ".print_r($wp_args, true)."</pre>";
    $info .= "[".count($posts)."] posts found.<br />";
    
    // Determine which snippets should be displayed for the post in question
	foreach ( $posts as $post_id ) {
		
		$info .= "post_id: ".$post_id."<br />";
		
		$cs = get_post_meta( $post_id, '_cs_replacements', true );
		$sidebar_id = get_post_meta( $post_id, 'sidebar_id', true );
		//$info .= "sidebar_id: <pre>".print_r($sidebar_id, true)."</pre>";
		if ( empty($sidebar_id) ) {
			$sidebar_id = "";
			$info .= "_cs_replacements:<br />";
			foreach ( $cs as $k => $v ) {
				$info .= "k: ".$k." => v: ".$v."<br />";
				$sidebar_id .= $v;
				if ( count($cs) > 1 ) { $sidebar_id .= ";"; } // will this ever happen? don't think so, but just in case...
			}
		}
		//$info .= "custom sidebar: <pre>".print_r($cs, true)."</pre>";
		$info .= "revised sidebar_id: ".$sidebar_id."<br />";
		
		// Update postmeta with revised sidebar_id value
		$update_key = 'sidebar_id';
		if ( update_field( $update_key, $sidebar_id, $post_id ) ) {
			$info .= "updated field: ".$update_key." for post_id: $post_id<br />";
		} else {
			$info .= "update FAILED for field: ".$update_key." for post_id: $post_id<br />";
		}
		
		$info .= "<br />";
		
	}
	
	return $info;

}

//
add_shortcode('snippets', 'display_snippets');
function display_snippets ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: show_snippets", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
	$arr_ids = array(); // this array will containing snippets matched for display on the given post
    
    $args = shortcode_atts( array(
    	'post_id' => null,
		'limit'   => -1,
        'run_updates'  => false,
        'devmode' => false,
        //'return' => 'info',
        'sidebar_id' => 'sidebar-1', // default
    ), $atts );
    
    // Extract
	extract( $args );
	
	//
	if ( $devmode ) { 
		$info .= '<h2>Snippets -- WIP</h2>';
		//$info .= '<p>show : Show everywhere<br />hide : Hide everywhere<br />selected : Show widget on selected<br />notselected : Hide widget on selected</p>';
		$info .= "args: <pre>".print_r($args, true)."</pre>";
	}

	$arr_snippets = get_snippets ( $args );
	$arr_ids = $arr_snippets['ids'];
	$ts_info .= $arr_snippets['info'];
	
	// Compile info for the matching snippets for display
	foreach ( $arr_ids as $snippet_id ) {
	
		$title = get_the_title( $snippet_id );
		$snippet_content = get_the_content( null, false, $snippet_id );
		//$snippet_content = apply_filters('the_content', $snippet_content); // causes error -- instead use apply_shortcodes in sidebar.php
		//$snippet_content = do_shortcode($snippet_content); // causes error -- instead use apply_shortcodes in sidebar.php
		if ( $snippet_content ) { $snippet_content = wpautop($snippet_content); }
		//
		$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
		$sidebar_sortnum = get_post_meta( $snippet_id, 'sidebar_sortnum', true );
		$wtype = get_post_meta( $snippet_id, 'widget_type', true );
		//
		$snippet_content .= "<!-- wtype: $wtype -->";
		//
		if ( $wtype == "media_image" ) {
			$img_id = get_post_meta( $snippet_id, 'attachment_id', true );
			if ( $img_id ) {
				//$img_size = get_post_meta( $snippet_id, 'img_size', true );
				$img_size = "full";
				$classes = "snippet_media_image";
				$snippet_content .= '<div class="'.$classes.'">';
				$snippet_content .= wp_get_attachment_image( $img_id, $img_size );//$snippet_content .= wp_get_attachment_image( $img_id, $img_size, false ) );
				//$snippet_content .= $caption_html;
				$snippet_content .= '</div>';
			}			
		}
		//
		if ( $title == "Snippets" ) { continue; }
		//
		$info .= '<section id="snippet-'.$snippet_id.'" class="snippet widget widget_text widget_custom_html">';
		$info .= '<h2 class="widget-title">'.$title.'</h2>';
		$info .= '<div class="textwidget custom-html-widget">';
		$info .= $snippet_content;		
		if ( $sidebar_sortnum ) { $info .= '<!-- position: '.$sidebar_sortnum.'/widget_uid: $widget_uid -->'; }
		$info .= '</div>';
		$info .= '</section>';
	}
	// 
	if ( $devmode ) { $info .= "<hr />".$ts_info; } else { $info .= '<div class="troubleshooting">'.$ts_info.'</div>';}
	
	return $info;
	
}

// Get array of snippet IDs matching given attributes
function get_snippets ( $args = array() ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: show_snippets", $do_log );
    
    // Init vars
    $arr_info = array();
    $info = "";
	$active_snippets = array(); // this array will containing snippets matched for display on the given post
    
    // Defaults
	$defaults = array(
		'post_id' => null,
		'limit'   => -1,
        'run_updates'  => false,
        'devmode' => false,
        'return' => 'info',
        'sidebar_id' => 'sidebar-1', // default
        'classes' => array(), // for use when called by stc_body_class fcn
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	//
	if ( $devmode ) { 
		$info .= '<h2>Snippets -- WIP</h2>';
		//$info .= '<p>show : Show everywhere<br />hide : Hide everywhere<br />selected : Show widget on selected<br />notselected : Hide widget on selected</p>';
		$info .= "args: <pre>".print_r($args, true)."</pre>";
	}
    
    // Is this a single post of some kind, or another kind of page (e.g. taxonomy archive)
    
    // is_singular, is_archive, is_tax, is_post_type_archive
    
	// Get post_type, if applicable
	if ( is_singular() ) { // is_single
		$info .= "is_singular<br />";
		if ( $post_id === null ) { $post_id = get_the_ID(); }
		$post_type = get_post_type( $post_id );
	} else {
		$info .= "NOT is_singular<br />";
		//$post_type = get_post_type( get_queried_object_id() );
		$post_type = "N/A";
		//post_type_archive_title();
		if ( is_archive() ) {
			$info .= "is_archive<br />";
			// what kind of archive?
			$object = get_queried_object();
			$object_class = get_class($object);
			$info .= "object_class: ".$object_class."<br />";
			//$info .= "get_queried_object: <pre>".print_r($object,true)."</pre>";
			if ( is_tax() ) {
				$tax = $object->taxonomy;
				$info .= "tax: ".$tax."<br />";
				$tax_obj = get_taxonomy($tax);
				$tax_post_types = $tax_obj->object_type;
				$info .= "tax_post_types: ".print_r($tax_post_types,true)."<br />";
				if ( count($tax_post_types) == 1 ) { $post_type = $tax_post_types[0]; }
			} else if ( is_post_type_archive() ) {
				$info .= "is_post_type_archive: ";
				$post_archive_title = post_type_archive_title("",false);
				$info .= $post_archive_title."<br />";
				if ( $object->name ) {
					$object_name = $object->name;
				} else {
					$object_name = strtolower($post_archive_title);
				}
				$info .= "object_name: ".$object_name."<br />";
				$post_type = $object_name;
			} else {
				//$info .= "get_the_archive_title: ".get_the_archive_title()."<br />";
				//$info .= "post_type_archive_title: ".post_type_archive_title()."<br />";
			}
			// WIP
		}
	}
	$info .= "post_type: $post_type<br />";
		
	// Check for custom sidebars 
	$cs = get_post_meta( $post_id, '_cs_replacements', true );
	//if ( $cs ) { $info .= "custom sidebar: <pre>".print_r($cs, true)."</pre>"; }
	//e.g. Array( [sidebar-1] => cs-17 )
	
	// Set up basic query args for snippets retrieval
    $wp_args = array(
		'post_type'		=> 'snippet',
		'post_status'	=> 'publish',
		'posts_per_page'=> $limit,
        'fields'		=> 'ids',
        'orderby'		=> 'meta_value',
		'order'			=> 'ASC',
        'meta_key'		=> 'sidebar_sortnum',
	);
	
	// Meta query
	$meta_query = array(
		'relation' => 'AND',
		'snippet_display' => array(
			'key' => 'snippet_display',
			'value' => array('show', 'selected', 'notselected'),
			'compare' => 'IN',
		),
		/*'sidebar_id' => array(
			'key' => 'sidebar_id',
			'value' => $sidebar_id,
			'compare' => '=',
		),*/
		// The sidebar clause ensures that we don't get widgets from bottom-widgets, wp_inactive_widgets, etc.
		'sidebar_id' => array(
			'relation' => 'OR',
			array(
				'key' => 'sidebar_id',
				'value' => $sidebar_id,
				'compare' => '=',
			),
			array(
				'key' => 'sidebar_id',
				'value' => 'cs-',
				'compare' => 'LIKE',
			),
		),
	);
	$wp_args['meta_query'] = $meta_query;
	
	$arr_posts = new WP_Query( $wp_args );
	$snippets = $arr_posts->posts;
    //$info .= "WP_Query run as follows:";
    //$info .= "<pre>args: ".print_r($wp_args, true)."</pre>";
    $info .= "[".count($snippets)."] snippets found.<br />";
    
    // Determine which snippets should be displayed for the post in question
	foreach ( $snippets as $snippet_id ) {
	
		$snippet_info = "";
		$snippet_logic_info = "";
		//
		$snippet_display = get_post_meta( $snippet_id, 'snippet_display', true );
		$sidebar_id = get_post_meta( $snippet_id, 'sidebar_id', true );
		$any_all = get_post_meta( $snippet_id, 'any_all', true );
		if ( empty($any_all) ) { $any_all = "any"; } // TODO: update_post_meta
		//
		$title = get_the_title( $snippet_id );
		$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
		//
		$snippet_status = "unknown"; // init
		$snippet_info .= '<div class="troubleshooting">';
		$snippet_info .= $title.' ['.$snippet_id.'/'.$widget_uid.'/'.$snippet_display;
		if ( $sidebar_id ) { $snippet_info .= '/'.$sidebar_id; }
		$snippet_info .= ']<br />';
		
		// Run updates?
		if ( $run_updates ) { $snippet_info .= '<div class="code">'.update_snippet_logic ( array( 'snippet_id' => $snippet_id ) ).'</div>'; }
		
		// TMP during transition?
		// TODO: add snippet status field?
		if ( $sidebar_id == "wp_inactive_widgets" ) {
		
			$snippet_status = "inactive";
			$snippet_logic_info .= "Snippet belongs to wp_inactive_widgets, i.e. status is inactive<br />";
			// TODO: remove from active_snippets array, if it was previously added...
			
		} else if ( $snippet_display == "show" ) {
		
			$active_snippets[] = $snippet_id; // add the item to the active_snippets array
			$snippet_status = "active";
			$snippet_logic_info .= "Snippet is set to show everywhere<br />";
			$snippet_logic_info .= "=> snippet_id added to active_snippets array<br />";
			
		} else {
		
			// Conditional display -- determine whether the given post should display this widget		
			$snippet_logic_info .= "<h3>Analysing display conditions...</h3>";
			
			// Set default snippet status for show on selected vs hide on selected
			if ( $snippet_display == "selected" ) {
				$snippet_status = "inactive";
			} else if ( $snippet_display == "notselected" ) {
				$active_snippets[] = $snippet_id; // add the item to the active_snippets array
				$snippet_status = "active";
			}
		
			$meta_keys = array( 'target_by_post', 'exclude_by_post', 'target_by_url', 'exclude_by_url', 'target_by_taxonomy', 'target_by_taxonomy_archive', 'target_by_post_type', 'target_by_post_type_archive', 'target_by_location' );
			foreach ( $meta_keys as $key ) {
			
				$$key = get_post_meta( $snippet_id, $key, true );
				//$snippet_info .= "key: $key => ".$$key."<br />";
				
				if ( !empty($$key) ) { //  && is_array($$key) && count($$key) == 1 && !empty($$key[0])
				
					$snippet_logic_info .= "key: $key =><br />";//$snippet_logic_info .= "key: $key => [".print_r($$key, true)."]<br />"; // ." [count: ".count($$key)."]"
					//$snippet_logic_info .= "[".print_r($$key, true)."]<br />";
					
					if ( ( $key == 'target_by_post_type' && $post_type != "N/A" ) || $key == 'target_by_post_type_archive') {
						
						if ( $key == 'target_by_post_type' ) {
							// This condition applies to singular posts only
							// Is the current page singular?
							if ( is_singular() ) {
								$snippet_logic_info .= "current page is_singular<br />";
							} else {
								$snippet_logic_info .= "current page NOT is_singular >> target_by_post_type does not apply<br /><br />";
								continue;
							}						
						} else {
							// This condition applies to archives only
							// Is the current page some kind of archive?
							if ( is_archive() ) {
								$snippet_logic_info .= "current page is_archive<br />";
							} else {
								$snippet_logic_info .= "current page NOT is_archive >> target_by_post_type_archive does not apply<br /><br />";
								continue;
							}
							if ( is_post_type_archive() ) { $snippet_logic_info .= "current page is_post_type_archive<br />"; }
						}
						
						// Is the given post of the matching type?
						$target_post_types = get_field($key, $snippet_id, false);
						//$snippet_logic_info .= "target_post_types: <pre>".print_r($target_post_types, true)."</pre><br />";
						//
						//
						// WIP: stored values are not bare post types, but rather e.g. [Array ( [0] => is_archive-event [1] => is_singular-person [2] => is_singular-product ) ]
						// => parse accordingly
						//
						//if ( $target_type && $post_type == $target_type ) {
						if ( is_array($target_post_types) && in_array($post_type, $target_post_types) ) {
							$snippet_logic_info .= "current post_type [$post_type] is in target post_types array<br />";//$snippet_logic_info .= "This post matches target post_type [$target_type].<br />";
							// TODO: figure out whether to do the any/all check now, or 
							// just add the id to the array and remove it later if "all" AND another condition requires exclusion?
							if ( $snippet_display == "selected" && $any_all == "any" ) {
								$active_snippets[] = $snippet_id; // add the item to the active_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> snippet_id added to active_snippets array<br />";
								//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							} else if ( $snippet_display == "notselected" ) {
								$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
								$snippet_status = "inactive";
								$snippet_logic_info .= "...but because snippet_display == notselected, that means it should not be shown<br />";
							}
						} else {
							$snippet_logic_info .= "This post does NOT match any of the array values.<br />";
							$snippet_logic_info .= "=> continue<br />";
						}
					
					} else if ( $key == 'target_by_post' || $key == 'exclude_by_post' ) {
					
						if ( is_singular() ) {
							$snippet_logic_info .= "current page is_singular<br />";
						} else {
							$snippet_logic_info .= "current page NOT is_singular >> $key does not apply<br /><br />";
							continue;
						}
							
						// Is the given post targetted or excluded?
						$target_posts = get_field($key, $snippet_id, false);
						if ( is_array($target_posts) && !empty($target_posts) && in_array($post_id, $target_posts) ) {
						
							// Post is in the target array
							$snippet_logic_info .= "This post is in the target_posts array<br />";
							// If it's for inclusion, add it to the array
							if ( $key == 'target_by_post' && $snippet_display == "selected" ) { //$any_all == "any" && 
								$active_snippets[] = $snippet_id; // add the item to the active_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> snippet_id added to active_snippets array (target_by_post/selected)<br />";
								//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							} else if ( $key == 'exclude_by_post' && $snippet_display == "notselected" ) { //$any_all == "any" && 
								$active_snippets[] = $snippet_id; // add the item to the active_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> snippet_id added to active_snippets array (exclude_by_post/notselected)<br />";
								//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							}
							// Snippet is inactive -- is in array, and either selected/excluded or notselected/targeted
							$snippet_logic_info .= "=> snippet inactive due to key: ".$key."/".$snippet_display."<br />";
							$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
							//if ( $snippet_display == "selected" ) { $snippet_status = "inactive"; } 
							$snippet_status = "inactive"; // ???
							break;
							
						} else {
						
							if ( empty($target_posts) ) { // redundant?
								$snippet_logic_info .= "The target_posts array is empty.<br />";
							} else {
								//???
								$snippet_logic_info .= "This post is NOT in the target_posts array.<br />";
								$snippet_logic_info .= "<!-- post_id: $post_id/target_posts: ".print_r($target_posts, true)." -->"; 
								if ( $snippet_display == "selected" ) {
									$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
									$snippet_status = "inactive";
								}
							}
							$snippet_logic_info .= "=> continue<br />";
						}
						
					} else if ( $key == 'target_by_url' || $key == 'exclude_by_url' ) {
					
						// Is the given post targetted or excluded?
						$target_urls = get_field($key, $snippet_id, false);
						
						// Loop through target urls looking for matches
						if ( is_array($target_urls) && !empty($target_urls) ) {
						
							//$snippet_logic_info .= "target_urls (<em>".$key."</em>): <br />";//$snippet_logic_info .= $key." target_urls: ".print_r($target_urls, true)."<br />";
						
							// Get current page path and/or slug
							global $wp;
							$current_url = home_url( add_query_arg( array(), $wp->request ) );
							//$snippet_logic_info .= "current_url: ".$current_url."<br />";
							$permalink = get_the_permalink($post_id);
							//$snippet_logic_info .= "permalink: ".$permalink."<br />";
							//if ( $permalink != $current_url ) { $current_url = $permalink; }
							$current_path = parse_url($current_url, PHP_URL_PATH);
							$snippet_logic_info .= "current_path: ".$current_path."<br />";
							
							foreach ( $target_urls as $k => $v ) {
								//$url = $v['url'];
								//$field = get_field_object('my_field');
								//$field_key = $field['key'];
								// WIP/TODO: get field key from key name?
								//$field_key = acf_maybe_get_field( 'field_name', false, false );
								if ( $key == 'target_by_url' ) {
									$field_key = 'field_6530630a97804';
								} else {
									$field_key = 'field_65306bc897806';
								}
								if ( isset($v[$field_key]) ) {
								
									$url = $v[$field_key];									
									if ( substr($url, -1) == "/" ) { $url = substr($url, 0, -1); } // Trim trailing slash, if any
									
									//$snippet_logic_info .= "target_url :: k: $k => v: ".print_r($v, true)."<br />";
									//$snippet_logic_info .= "target_url: ".$url."<br />";
									// compare url to current post path/slug
									$url_match = false;
									if ( $url == $current_path ) {
										// URL matches current path
										$snippet_logic_info .= "target_url: ".$url." matches current_path<br />";
										$url_match = true;										
									} else if ( strpos($url, '*') !== false ) {
										// Check for wildcard match
										$snippet_logic_info .= "** Wildcard url<br />";
										$snippet_logic_info .= "target_url: ".print_r($v, true)."<br />"; //$snippet_logic_info .= "target_url :: k: $k => v: ".print_r($v, true)."<br />";
										// Remove the asterisk to get the url_base
										$url_base = trim( substr($url, 0, strpos($url, '*')) );
										// clean up the bases so that the /s don't get in the way -- TODO: do this more efficiently, maybe with a custom trim fcn?
										if ( substr($url_base, 0, 1) == "/" ) { $url_base = substr($url_base, 1); } // Trim leading slash, if any
										if ( substr($url_base, -1) == "/" ) { $url_base = substr($url_base, 0, -1); } // Trim trailing slash, if any
										$snippet_logic_info .= "url_base: $url_base<br />";
										$current_path_base = $current_path;
										if ( substr($current_path_base, 0, 1) == "/" ) { $current_path_base = substr($current_path_base, 1); } // Trim leading slash, if any
										if ( substr($current_path_base, -1) == "/" ) { $current_path_base = substr($current_path_base, 0, -1); } // Trim trailing slash, if any
										$snippet_logic_info .= "current_path_base: $current_path_base<br />";
										// match to $current_path? true if current_path begins with url_base
										if ( substr($current_path_base, 0, strlen($url_base)) == $url_base ) {
											$url_match = true;
											$snippet_logic_info .= "current_path_base begins with wildcard url_base: $url_base<br />";
										}
									} else {
										//$snippet_logic_info .= "url $url does not match current_path $current_path<br />";
									}
								}
								if ( $url_match ) {
									if ( $key == 'target_by_url' && $snippet_display == "selected" ) {
										$active_snippets[] = $snippet_id; // add the item to the active_snippets array
										$snippet_status = "active";
										$snippet_logic_info .= "=> snippet_id added to active_snippets array (target_by_url/selected)<br />";
										//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
										$snippet_logic_info .= "=> BREAK<br />";
										break;
									} else if ( $key == 'exclude_by_url' && $snippet_display == "notselected" ) {
										$active_snippets[] = $snippet_id; // add the item to the active_snippets array
										$snippet_status = "active";
										$snippet_logic_info .= "=> snippet_id added to active_snippets array (exclude_by_url/notselected)<br />";
										//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
										$snippet_logic_info .= "=> BREAK<br />";
										break;
									}
									// Snippet is inactive -- found in target urls, and either selected/excluded or notselected/targeted
									$snippet_logic_info .= "=> snippet inactive due to key: ".$key."/".$snippet_display."<br />";
									$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
									$snippet_status = "inactive";
									break;
								}
							} // foreach ( $target_urls as $k => $v ) {
							$snippet_logic_info .= "current_path not targeted<br />";
							
						} // if ( is_array($target_urls) && !empty($target_urls) ) {
						
					} else if ( $key == 'target_by_taxonomy' ) { //  || $key == 'widget_logic_taxonomy'
						
						$target_taxonomies = get_field($key, $snippet_id, false);
						//$snippet_logic_info .= "target_taxonomies: <pre>".print_r($target_taxonomies, true)."</pre><br />";
						$arr_post_taxonomies = get_post_taxonomies();
						//$snippet_logic_info .= "arr_post_taxonomies: <pre>".print_r($arr_post_taxonomies, true)."</pre><br />";
						
						// TODO: simplify this logic
						if ( match_terms( $target_taxonomies, $post_id ) ) { // ! empty( $target_taxonomies ) && 
							$snippet_logic_info .= "This post matches the target taxonomy terms<br />";
							if ( $snippet_display == "selected" ) {
								$active_snippets[] = $snippet_id; // add the item to the active_snippets array
								$snippet_status = "active";
							} else {
								$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
								$snippet_status = "inactive";
								$snippet_logic_info .= "...but because snippet_display == notselected, that means it should not be shown<br />";
							}
							$snippet_logic_info .= "=> BREAK<br />";
							break;
						} else {
							$snippet_logic_info .= "This post does NOT match the target taxonomy terms<br />";
							if ( $snippet_display == "selected" ) {
								$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
								$snippet_status = "inactive";
								if ( $any_all == "all" ) {
									$snippet_logic_info .= "=> BREAK<br />";
									break;								
								}
							} else if ( $snippet_display == "notselected" ) {
								// WIP
								//$active_snippets[] = $snippet_id; // add the item to the active_snippets array
								//$snippet_status = "active";
								//$snippet_logic_info .= "...but because snippet_display == notselected, that means it should be shown<br />";
							}
							// break?							
						}
					
					} else if ( $key == 'target_by_taxonomy_archive' ) {
					
						$target_taxonomies = get_field($key, $snippet_id, false);
						//$snippet_logic_info .= "target_taxonomies (archives): <pre>".print_r($target_taxonomies, true)."</pre><br />";
						
						if ( is_tax() ) {
							// If this is a taxonomy archive AND target_taxonomies are set, check for a match
							$snippet_logic_info .= "current page is_tax<br />";
							foreach ( $target_taxonomies as $taxonomy ) {
								if ( is_tax($taxonomy) ) {
									$snippet_logic_info .= "This post is_tax archive for target taxonomy: $taxonomy<br />";
									if ( $snippet_display == "selected" ) {
										$active_snippets[] = $snippet_id; // add the item to the active_snippets array
										$snippet_status = "active";
										$snippet_logic_info .= "=> BREAK<br />";
										break;
									} else {
										$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
										$snippet_status = "inactive";
										$snippet_logic_info .= "...but because snippet_display == notselected, that means it should NOT be shown<br />";
									}
								}
							}
						}
					
					} else if ( $key == 'target_by_location' ) {
						// Is the given post/page in the right site location?
						$target_locations = get_field($key, $snippet_id, false);
						$locations = array( 'is_home', 'is_single', 'is_page', 'is_archive', 'is_search', 'is_attachment', 'is_category', 'is_tag' ); // is_singular
						$current_locations = array();
						foreach ( $locations as $location ) {
							if ( $location() ) {
								$snippet_logic_info .= "current page/post ".$location."<br />";
								$current_locations[] = $location;
							}
						}
						//
						$snippet_logic_info .= "target_locations: ".print_r($target_locations, true)."<br />";
						$snippet_logic_info .= "current_locations: ".print_r($current_locations, true)."<br />";
						if ( count($current_locations) == 1 ) { $current_location = $current_locations[0]; } else { $current_location = "multiple"; } // wip
						//
						//if ( match_locations( $target_locations, $post_id ) ) { // TODO? make match_locations fcn?
						if ( in_array($current_location, $target_locations) ) {
							//$active_snippets[] = $snippet_id; // add the item to the active_snippets array
							//$snippet_status = "active";
							$snippet_logic_info .= "This post matches the target_locations<br />";
							if ( $snippet_display == "selected" ) {
								$active_snippets[] = $snippet_id; // add the item to the active_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							} else {
								$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
								$snippet_status = "inactive";
								$snippet_logic_info .= "...but because snippet_display == notselected, that means it should NOT be shown<br />";
							}
						} else {
							$snippet_logic_info .= "This post does NOT match the target_locations<br />";
							if ( $snippet_display == "selected" ) {
								$active_snippets = array_diff($active_snippets, array($snippet_id)); // remove the item from the active_snippets array
								$snippet_status = "inactive";
							}
						}
						//
					} else {
						$snippet_logic_info .= "unmatched key: ".$key."<br />";
					}
					$snippet_logic_info .= "<br />";
					
				} else {
					$snippet_logic_info .= "key: $key => [empty]<br /><br />";
				}
			}
		
		}
		$snippet_logic_info .= "<hr />";
		$snippet_logic_info .= "snippet_status: ".$snippet_status;
		$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
		//
		$snippet_info .= '</div>'; // <div class="troubleshooting">
		$info .= $snippet_info;
    }
    
    // Make sure there are no duplicates in the active_snippets array
    $active_snippets = array_unique($active_snippets); // SORT_REGULAR
	
	//$active_snippets[] = 330389; // tft
	
	// If returning array of IDs, finish here
	if ( $return == "ids" ) { return $active_snippets; }
	
	$arr_info['info'] = $info;
	$arr_info['ids'] = $active_snippets;
	
	return $arr_info;
	
}

//
function get_snippet_by_widget_uid ( $widget_uid = null ) {

	$snippet_id = null;
	$info = "";
	
	if ( $widget_uid ) {
		$wp_args = array(
			'post_type'   => 'snippet',
			'post_status' => 'publish',
			'meta_key'    => 'widget_uid',
			'meta_value'  => $widget_uid,
			'fields'      => 'ids'
		);	
		$snippets = get_posts($wp_args);
	}
	
	if ( $snippets ) {
		//$info .= "snippets: <pre>".print_r($snippets,true)."</pre><hr />";
		// get existing post id
		if ( count($snippets) == 1 ) {
			$snippet_id = $snippets[0];
		} else if ( count($snippets) > 1 ) {
			//$info .= "More than one matching snippet!<br />";
			//$info .= "snippets: <pre>".print_r($snippets,true)."</pre><hr />";
		}
		//$info .= "snippet_id: ".$snippet_id."<br />";
	}
	
	return $snippet_id;

}

//
add_shortcode('widgets_to_snippets', 'convert_widgets_to_snippets');
function convert_widgets_to_snippets ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: convert_widgets_to_snippets", $do_log );
    
    $args = shortcode_atts( array(
		'limit'   => 1,
        'sidebar_id' => null,
        'widget_id'	=> null,
        'run_updates' => false,   
    ), $atts );
    
    // Extract
	extract( $args );
	
	$info = "";
	$i = 0;
	
	// Get wpstc_options data
	$arr_sidebars_widgets = get_option('sidebars_widgets'); // array of sidebars and their widgets (per sidebar id, e.g. "wp_inactive_widgets", "cs-11" )
	$widget_logic = get_option('widget_logic_options'); // widget display logic ( WidgetContext plugin -- being phased out )
	$cs_sidebars = get_option('cs_sidebars'); // contains name, id, description, before_widget, etc. for custom sidebars
	//
	$wtypes = array( 'text', 'custom_html', 'media_image', 'ninja_forms_widget' );
	//$wpstc_options = array( 'widget_text', 'widget_custom_html', 'widget_ninja_forms_widget' );
	//wtype: text => widget_text
	//wtype: custom_html
	//wtype: media_image => widget_media_image
	//wtype: media_gallery => widget_media_gallery
	//wtype: recent => widget_recent-posts
	//wtype: categories => widget_categories
	//wtype: em_calendar => widget_em_calendar
	//wtype: ninja_forms_widget => widget_ninja_forms_widget
	//wtype: wcpbc_products_by_category => widget_wcpbc_products_by_category
	/*
SELECT * FROM `wpstc_options` WHERE `option_name` 
LIKE '%widget_media_image%'
OR `option_name` LIKE '%widget_media_gallery%'
OR `option_name` LIKE '%widget_recent%'
OR `option_name` LIKE '%widget_categories%'
OR `option_name` LIKE '%widget_em_calendar%'
OR `option_name` LIKE '%widget_ninja_forms_widget%'
OR `option_name` LIKE '%widget_wcpbc_products_by_category%'  
ORDER BY `wpstc_options`.`option_name` ASC
*/
	//
	//
	//$text_widgets = get_option('widget_text');
	//$html_widgets = get_option('widget_custom_html');
	foreach ( $wtypes as $wtype ) {
		$option_name = "widget_".$wtype;
		$$option_name = get_option($option_name);
		//$varname = $wtype."_widgets";
	}
	// WIP
	//wtype: ninja_forms_widget
	//
	//$info .= "text_widgets: <pre>".print_r($text_widgets,true)."</pre><hr />";
	//$info .= "html_widgets: <pre>".print_r($html_widgets,true)."</pre><hr />";
	////////
	
	// Loop through sidebars and convert widgets to snippets
	
	$info .= "<h2>Sidebars/Widgets</h2>";
	//$info .= "<pre>arr_sidebars_widgets: ".print_r($arr_sidebars_widgets,true)."</pre><hr /><hr />";
	foreach ( $arr_sidebars_widgets as $sidebar => $widgets ) {
		
		// If we're handling a specific sidebar and this isn't it, move on to the next
		if ( $sidebar_id && $sidebar != $sidebar_id ) { continue; }
		
		// Skip wp_inactive_widgets -- tft
		//if ( $sidebar == 'wp_inactive_widgets' ) { continue; }
		//if ( $sidebar == "wp_inactive_widgets" || $sidebar == "mega-menu" || $sidebar == "array_version" || empty($widgets) ) { continue; }
		
		// Get the registered sidebar info -- name, id, description, before_widget, etc.
		$sidebar_name = null; // init
		$sidebar_info = wp_get_sidebar( $sidebar );
		if ( $sidebar_info ) { $sidebar_name = $sidebar_info['name']; }
		
		// Is this a Custom Sidebar?
		if ( strpos($sidebar, 'cs-') !== false ) {
			$custom_sidebar = true;
			//if ( $sidebar == "cs-29" ) { $info .= "Sermons sidebar... skip it for now<br />"; continue; } // Sermons sidebar. Special case
		} else {
			$custom_sidebar = false;
		}
		
		$info .= "<h3>sidebar: ";
		$info .= $sidebar;
		if ( $sidebar_name ) { $info .= ' => "'.$sidebar_name.'"'; }
		if ( $custom_sidebar ) { $info .= " [cs]"; }
		//$info .= " => sidebar_info: <pre>".print_r($sidebar_info,true)."</pre>";
		$info .= "</h3>";
		
		//$info .= "sidebar: ".$sidebar." => widgets: <pre>".print_r($widgets,true)."</pre><hr />";
		//$info .= "widgets: <pre>".print_r($widgets,true)."</pre><hr />";
		
		$info .= '<div class="code">';
		
		// Loop through widgets and create corresponding snippet records
		if ( is_array($widgets) ) {
		
			$info .= "<h4>Widgets</h4>";
			foreach ( $widgets as $i => $widget_uid ) {
			
				$info .= "<h5>widget ".$i.": ".$widget_uid."</h5>";

				// Separate type and id from widget_uid
				$wtype = substr($widget_uid, 0, strpos($widget_uid, "-"));
				$wid = substr($widget_uid, strpos($widget_uid, "-") + 1);
				$wtype_option = "widget_".$wtype;
				$info .= "wtype: ".$wtype."/"."wid: ".$wid."<br />";
				// Widget type?
				if ( isset($$wtype_option[$wid]) ) {
					$widget = $$wtype_option[$wid];
					$info .= "Matching $wtype widget found.<br />";
				} else {
					$widget = null;
				}
				if ( !in_array($wtype, $wtypes) ) {
					$info .= "We're not currently processing widgets of type: $wtype<br />";
					$widget = null; // tft
				}
					
				// If a widget was found, gather the info needed to create/update the corresponding snippet
				if ( $widget ) {
					
					$postarr = array();
					$meta_input = array();
					$conditions = array();
				
					// Does a snippet already exist based on this widget?
					$snippet_id = get_snippet_by_widget_uid ( $widget_uid );
					if ( $snippet_id ) {
						$postarr['ID'] = $snippet_id;
						$info .= "<h5>&rarr; snippet_id: ".$snippet_id."/".get_the_title($snippet_id)."</h5>";
					} else {
						$info .= "No existing snippet found for widget_uid: ".$widget_uid."<br />";
					}
				
					// Array fields for text widgets: title, text, filter, visual, csb_visibility, csb_clone...
					// TODO: check if fields are same for e.g. custom_html
					
					// Defaults for title and content
					$snippet_title = $widget_uid;
					$snippet_content = null;
					
					// Get actual widget content etc
					
					// Title
					if ( isset($widget['title']) && !empty($widget['title']) ) {
						$snippet_title = $widget['title'];
					}
					
					// Content
					if ( isset($widget['text']) ) {
						$snippet_content = $widget['text'];
					} else if ( isset($widget['content']) ) {
						$snippet_content = $widget['content'];
					}
					
					// WIP: find STC absolute hyperlinks in snippet content and relativize them (i.e. more clean up after AG...)
					// e.g. <a href="https://stcnyclive.wpengine.com/theology/">Gain understanding by attending classes</a>
					if ( strpos($snippet_content, 'http') !== false ) {
						$info .= "** Absolute urls in snippet_content => relativize them<br />";
						//$info = str_replace($search,$replace,$info);
						//$snippet_content = str_replace('https://stcnyclive.wpengine.com/','/',$snippet_content);
						$snippet_content = str_replace('https://stcnyclive.wpengine.com/','/',$snippet_content);						
					}
					
					// Image Widget?
					if ( $wtype == "media_image" ) {
						//
						if ( isset($widget['attachment_id']) && !empty($widget['attachment_id']) ) {
							$meta_input['attachment_id'] = $widget['attachment_id'];
						}
						if ( isset($widget['image_title']) && !empty($widget['image_title']) ) {
							$meta_input['image_title'] = $widget['image_title'];
						}
						if ( isset($widget['link_type']) && !empty($widget['link_type']) ) {
							$meta_input['link_type'] = $widget['link_type'];
						}
						if ( isset($widget['link_url']) && !empty($widget['link_url']) ) {
							$meta_input['link_url'] = $widget['link_url'];
						}
						/*
						[mega_menu_is_grid_widget] => true
						[size] => grid_crop_square
						[width] => 1500
						[height] => 844
						[caption] => 
						[alt] => 
						--[link_url] => /worship-and-pray/go-deeper/worship/choral-evensong/
						[image_classes] => 
						[link_classes] => 
						[link_rel] => 
						[link_target_blank] => 
						--[image_title] => Purchase CDs
						--[attachment_id] => 304791
						[url] => https://stcnycstg.wpengine.com/wp-content/uploads/2022/08/The-Saint-Thomas-Choir-at-the-Queens-Service-600x600.jpg
						--[title] => Choral Evensong
						...
						--[link_type] => custom
						*/
					}
					
					// Ninja Forms Widget?
					if ( $wtype == "ninja_forms_widget" ) {
						$form_id = $widget['form_id'];
						$display_title = $widget['display_title'];
						$info .= "NF form_id: ".$form_id."<br />";
						// Get form title for use as snippet title
						// WIP
						/*
						//Ninja_Forms()->form( 1 )->get();
						//$submissions = Ninja_Forms()->form( $form_id )->get_subs();
						//$setting = $model->get_setting( 'key' );
						//$form = Ninja_Forms()->form( $form_id );
						//$info .= "form: <pre>".print_r($form, true)."</pre>";
						//$snippet_title = Ninja_Forms()->form( $form_id )->get_setting( 'title' );
						// Use form_id in nf shortcode for content
						*/
						$snippet_content = "[ninja_form id=".$form_id."]";
					}
					
					//
					if ( ! ( $wtype == "text" || $wtype == "custom_html" || $wtype == "media_image" || $wtype == "ninja_forms_widget" ) ) {
						$info .= "<pre>".print_r($widget,true)."</pre>";
					}
					
					//
					$info .= "title: ".$snippet_title."<br />";
					
					// WIP: find if widget is included in one or more sidebars --> get sidebar_id(s)
					$widget_sidebar_id = get_sidebar_id($widget_uid);
					$info .= "widget_sidebar_id: ".$widget_sidebar_id."<br />";
					
					// TODO: check to see if snippet already exists with matching uid
					// If no match, create new snippet post record with title and text as above
					// If match, check for changes?
					
					// If title and content are set, then prep to save widget as snippet
					//if ( $snippet_title && $snippet_content ) {
					//if ( ( $wtype == "text" || $wtype == "custom_html" || $wtype == "ninja_forms_widget" ) && $snippet_title && $snippet_content ) { // tmp -- finish processing only for text and html widgets for now
					if ( in_array($wtype, $wtypes) && $snippet_title ) { // && $snippet_content
						//
						$postarr['post_title'] = wp_strip_all_tags( $snippet_title );
						$postarr['post_content'] = $snippet_content;
						$postarr['post_type'] = 'snippet';
						$postarr['post_status'] = 'publish';
						$postarr['post_author'] = 1; // get_current_user_id()
						// Set up preliminary meta_input
						$meta_input['widget_type'] = $wtype;
						$meta_input['widget_id'] = $wid;
						$meta_input['widget_uid'] = $widget_uid;
						if ( $sidebar ) {
							$meta_input['sidebar_id'] = $sidebar;
							$meta_input['sidebar_sortnum'] = $i;
						}
						
						// Proceed to processing widget display logic
						
						/*
						// Get existing value for sidebar_id field, if any
						$sidebars = get_post_meta( $snippet_id, 'sidebar_id', true );
						$info .= "snippet sidebars: ".$sidebars."<br />";
						$sidebars_revised = "";
						if ( empty($sidebars) ) {
							$sidebars_revised = $sidebar;
						} else if ( $sidebars != $sidebar ) {
							$sidebars_revised = $sidebars."; ".$sidebar;
						}
						$info .= "snippet sidebars_revised: ".$sidebars_revised."<br />";
						*/
						
						// Is this a Custom Sidebar?
						if ( $custom_sidebar ) {
						
							// This may be overridden later by the widget logic for this particular widget, 
							// ... but if not, default to showing it only on selected posts which were set to use this custom sidebar
							$meta_input['snippet_display'] = "selected";
							
							// NB/WIP only CS with sidebar location rules appears to be Sermons Sidebar => display on all individual sermon posts and sermon post archives

							// Get array of ids for posts using this custom sidebar
							global $wpdb;
	
							$sql = "SELECT `post_id` 
									FROM $wpdb->postmeta
									WHERE `meta_key` = '_cs_replacements'
									AND `meta_value` LIKE '%".'"'.$sidebar.'"'."%'";

							$arr_objs = $wpdb->get_results($sql);
							$cs_post_ids = array_column($arr_objs, 'post_id');
							sort($cs_post_ids); // Sort the array -- TODO: sort instead by post title
							if ( count($cs_post_ids) > 0 ) {
							
								$info .= count($cs_post_ids)." posts using this sidebar:<br />";
								//$info .= count($cs_post_ids)." posts using this sidebar: ".print_r($cs_post_ids,true)."<br />";
								
								//
								// WIP generalized fcn to determine revised value
								$updates = get_updated_field_value( $snippet_id, 'cs_post_ids', $cs_post_ids, 'array' ); // post_id, key, new_value, type
								$info .= $updates['info'];
								$updated_field_value = $updates['updated_value'];
								//
								if ( $updates && count($updated_field_value) > 0 ) {
									$info .= count($updated_field_value)." items in updated_field_value array<br />";
									//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
									$meta_input['cs_post_ids'] = serialize($updated_field_value);
								}
								
							} else {
								$info .= "There are no posts using this custom sidebar<br />";
								$meta_input['snippet_display'] = "hide";
							}
							$info .= "<hr />";
							
						} // END special handling for custom sidebars
					
						// Get widget logic -- WIP
						if ( isset($widget_logic[$widget_uid]) ) {
							$info .= "... found widget logic ...<br />";
							//$info .= "logic: <pre>".print_r($widget_logic[$widget_uid],true)."</pre><br />";
							$conditions = $widget_logic[$widget_uid];
						}
					
						// Loop through the conditions and prepare to save them to the snippet ACF fields, as applicable
						// NB: this is only step one; after the snippet has been created/updated,
						// .. we'll update the snippet logic and translate the old widget logic to fit the new ACF fields
						foreach ( $conditions as $condition => $subconditions ) {
					
							$condition_info = "";
							$subs_info = "";
							$subs_empty = true;
							$check_wordcount = false;
							//
							//$info .= "condition: ".$condition."<br />";
							//$info .= "subconditions: <br />";
							if ( $condition == 'incexc' ) {
								
								if ( !$custom_sidebar || $subconditions['condition'] != "show" ) {
									$meta_input['snippet_display'] = $subconditions['condition'];
								}
			
							} else if ( $condition == "url" ) {
		
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
								if ( isset($subconditions['urls']) && !empty($subconditions['urls']) ) {					
									$meta_input['widget_logic_target_by_url'] = $subconditions['urls']; // backup/transitional field
								}		
				
							} else if ( $condition == "urls_invert" ) {
		
								if ( isset($subconditions['urls_invert']) && !empty($subconditions['urls_invert']) ) {
									$meta_input['widget_logic_exclude_by_url'] = $subconditions['urls_invert']; // backup/transitional field					
								}
			
							} else if ( $condition == "location" || $condition == "custom_post_types_taxonomies" ) {
			
								$info .= "condition: ".$condition."<br />";
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
			
								// Init values array
								$values = array();
			
								// location => array (is_front_page, is_home, etc) --> target_by_location
								// custom_post_types_taxonomies => array of post types and custom taxonomy archives etc to target (or exclude)
			
								// Save only array elements where $v == 1
								foreach ( $subconditions as $k => $v ) {
									//$info .= "k: ".$k." => v: ".$v."<br />";
									if ( $v == 1 ) {
										$info .= "k: ".$k." => v: ".$v."<br />";
										$values[$k] = $v;
									}
								}
			
								// Determine the appropriate meta_key
								if ( $condition == "location" ) { $meta_key = 'widget_logic_location'; } else { $meta_key = 'widget_logic_custom_post_types_taxonomies'; }
			
								// Add the value(s) to the meta_input array
								if ( !empty($values) ) { $meta_input[$meta_key] = serialize($values); }
		
							} else if ( $condition == "taxonomy" ) {
			
								$info .= "condition: ".$condition."<br />";
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
			
								if ( isset($subconditions['taxonomies']) ) { 
									$taxonomies = $subconditions['taxonomies'];
									$info .= "taxonomies: ".$taxonomies."<br />";
									$meta_input['widget_logic_taxonomy'] = $taxonomies; // TODO: figure out why this isn't working
									$meta_input['target_by_taxonomy'] = $taxonomies;
								}
		
							} else if ( $condition == "word_count" ) {
		
								$info .= "condition: ".$condition."<br />";
								// WIP
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
		
							} else if ( is_array($subconditions) && !empty($subconditions) ) {
								$info .= "condition: ".$condition."<br />";
								if ( count($subconditions) == 1 && empty($subconditions[0]) ) {
									//$info .= "single empty subcondition<br />";
								} else {
									$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
									//$info .= count($subconditions)." subconditions<br />";
								}
								/*foreach ( $subconditions as $k => $v ) {
									//$info .= "k: ".$k." => v: ".$v."<br />";
								}*/
							} else {
								$info .= "condition: ".$condition."<br />";
								$info .= $subconditions." [not an array]<br />";
								//$meta_input[$condition] = $subconditions;
							}
							if ( !$subs_empty ) {
								//$condition_info .= $subs_info;
								//$condition_info .= $condition;
							}
							//
							//$info .= $condition_info;
		
						} // END foreach ( $conditions as $condition => $subconditions )
		
						// WIP
						$meta_input['widget_logic'] = print_r($conditions, true);
						
						// Init action var
						$action = null;
						
						// Finish setting up the post array for update/insert							
						$postarr['meta_input'] = $meta_input;
						
						if ( $wtype == "media_image" ) {
							//$info .= "snippet postarr: <pre>".print_r($postarr,true)."</pre>";
						}
						//
						if ( $snippet_id ) { //if ( isset($postarr['ID']) ) {
							$info .= "&rarr; About to update existing snippet [$snippet_id]<br />";
							// Update existing snippet
							$snippet_id = wp_update_post($postarr);
							if ( !is_wp_error($snippet_id) ) { $action = "updated"; }
						} else {
							$info .= "&rarr; About to create a new snippet<br />";
							// Insert the post into the database
							$snippet_id = wp_insert_post($postarr);
							if ( !is_wp_error($snippet_id) ) { $action = "inserted"; }
						}
						// Handle errors
						if ( is_wp_error($snippet_id) ) {
							//$info .= $snippet_id->get_error_message();
							$errors = $snippet_id->get_error_messages();
							foreach ($errors as $error) {
								$info .= $error;
							}
						}
		
						//
						if ( $action && $snippet_id ) {
							$info .= "&rarr;&rarr; Success! -- snippet record ".$action." [".$snippet_id."]<br />";				
							// Update snippet logic
							$info .= "&rarr;&rarr; update_snippet_logic<br />";
							$info .= update_snippet_logic ( array( 'snippet_id' => $snippet_id ) ); //$info .= '<div class="code">'.update_snippet_logic ( $snippet_id ).'</div>';
						} else {
							$info .= "&rarr;&rarr; No action<br />";
							//$info .= "snippet postarr: <pre>".print_r($postarr,true)."</pre>";
						}
		
					} else {
					
						if ( ! in_array($wtype, $wtypes) ) {
							$info .= "wtype: $wtype<br />";
							$info .= "snippet_content: <pre>".$snippet_content."</pre><br />";
						} else if ( ! ( $snippet_title && $snippet_content ) ) {
							$info .= "Incomplete data<br />";
							if ( !$snippet_title ) { $info .= "=> No title<br />"; }
							if ( !$snippet_content ) { $info .= "=> No content<br />"; } else { $info .= "snippet_content: <pre>".$snippet_content."</pre><br />"; }
						}
						
					}
				
				}
				
				//if ( $i > $limit ) { break; } // tft
				
				$info .= "<hr />";
			} // foreach ( $widgets...
			
		}
		
		//...
		$info .= '</div>';
	}
	
	////////
	
	return $info;
	
} // END function convert_widgets_to_snippets

// WIP
add_shortcode('convert_post_widgets', 'convert_post_widgets_to_snippets');
function convert_post_widgets_to_snippets () {
	
	// WIP
	
	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: convert_post_widgets_to_snippets", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
	$arr_ids = array(); // this array will containing snippets matched for display on the given post
    
    $args = shortcode_atts( array(
    	'post_id' => null,
		'limit'   => -1,
    ), $atts );
    
    // Extract
	extract( $args );
	
	// Set up basic query args for snippets retrieval
    $wp_args = array(
		'post_type'		=> 'any',
		'post_status' => array( 'private', 'draft', 'publish', 'archive' ),
		'posts_per_page'=> -1,
        'fields'		=> 'ids',
        //'orderby'		=> 'meta_value',
		//'order'			=> 'ASC',
        //'meta_key'		=> '_cs_replacements',
	);
	
	// Meta query
	$meta_query = array(
		'post_widget' => array(
			'key' => 'post_sidebar_widget_content',
			'compare' => '!=',
			'value' => '',
		),
	);
	$wp_args['meta_query'] = $meta_query;
	
	$arr_posts = new WP_Query( $wp_args );
	$posts = $arr_posts->posts;
    //$info .= "WP_Query run as follows:";
    //$info .= "<pre>args: ".print_r($wp_args, true)."</pre>";
    $info .= "[".count($posts)."] posts found.<br />";
    
    // Determine which snippets should be displayed for the post in question
	foreach ( $posts as $post_id ) {
		
		$info .= "post_id: ".$post_id."<br />";
		$info .= "post_title: ".get_the_title($post_id)."<br />";
		$widget_title = get_post_meta( $post_id, 'post_sidebar_widget_title', true );
		if ( empty($widget_title) ) { $widget_title = "More Resources"; }
		$widget_content = get_post_meta( $post_id, 'post_sidebar_widget_content', true );
		$widget_content = wpautop($widget_content);
		
		$info .= "widget_title: ".$widget_title."<br />";
		//$info .= "widget_content: <pre>".$widget_content."</pre><br />";
		
		$info .= "<hr /><br />";
		
		// TODO: create/update widget
		
	}	
    
    return $info;
    
}

// WIP -- messy draft -- do not use
add_shortcode('delete_widgets', 'delete_widgets');
function delete_widgets ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: delete_widgets", $do_log );
    
    $args = shortcode_atts( array(
        'limit'   => 1,
        'widget_types' => array( 'text', 'custom_html', 'media_image', 'ninja_forms_widget' ), //
        'widget_id'	=> null,
        'sidebars' => array( 'sidebar-1', 'wp_inactive_widgets', 'cs_sidebar' ),
        'run_updates' => false,
    ), $atts );
    
    // Extract
	extract( $args );
	
	$info = "";
	$i = 0;
	
	// Sidebars to process
	if ( !is_array($sidebars) ) { $sidebars = explode(',', $sidebars); }
	$info .= "sidebars: <pre>".print_r($sidebars,true)."</pre><hr /><hr />";
	
	// Widget Types to process
	if ( !is_array($widget_types) ) { $widget_types = explode(',', $widget_types); }
	$info .= "widget_types: <pre>".print_r($widget_types,true)."</pre><hr /><hr />";
	
	$info .= "<h2>Delete Widgets</h2>";
	
	// Get wpstc_options data
	$arr_sidebars_widgets = get_option('sidebars_widgets'); // array of sidebars and their widgets (per sidebar id, e.g. "wp_inactive_widgets", "cs-11" )
	//$widget_logic = get_option('widget_logic_options'); // widget display logic ( WidgetContext plugin -- being phased out )
	//$cs_sidebars = get_option('cs_sidebars'); // contains name, id, description, before_widget, etc. for custom sidebars
	//
	//
	foreach ( $widget_types as $wtype ) {
	
		$option_name = "widget_".$wtype;
		$info .= "option_name: ".$option_name."<br />";
		
		//$$option_name = get_option($option_name);
		$widgets = get_option($option_name);
		//
		//$info .= "widgets: <pre>".print_r($widgets,true)."</pre><hr /><hr />";
		//
		$i = 0;
		foreach ( $widgets as $key => $widget ) {
			
			$info .= "key: ".$key." / ";
			if ( isset($widget['title']) ) { $info .= $widget['title']; }
			$widget_uid = $wtype."-".$key;
			$info .= " [".$widget_uid."]";
			$info .= "<br />";
			
			// Which sidebar does this widget belong to?
			//$sidebar_id = wp_find_widgets_sidebar( $widget_id ); // nope
			$widget_sidebar = get_sidebar_id($widget_uid);
			if ( strpos($widget_sidebar, 'cs-') !== false ) {
				$widget_sidebar = "cs_sidebar"; 
			}
			$info .= "&rarr; widget_sidebar: ".$widget_sidebar."<br />";
			
			// Delete widget -- by unsetting key?
			//if ( $key == 3 ) { unset($widgets[$key]); }
			if ( in_array( $widget_sidebar, $sidebars ) ) {
				$info .= "&rarr; Delete this widget!<br />";
				unset($widgets[$key]);
				$i++;
			} else {
				$info .= "&rarr; Do NOT delete this widget!<br />";
			}		
			
			if ( $i > $limit && $limit > 0 ) { break; }
		}
		//
		//$info .= "REVISED widgets: <pre>".print_r($widgets,true)."</pre><hr /><hr />";
		
        // update DB option
        if ( $i > 0 ) {
        	$info .= "Preparing to delete ".$i." ".$wtype." widgets!<br />";
        	$updated = update_option( $option_name, $widgets );
			if ( !$updated ) {
				// do some form of error handling (6)
				$info .= "ERROR ON UPDATE<br />";
			} else {
				$info .= $i." ".$wtype." widgets deleted!<br />";
			}
        }
        
        $info .= "<br /><hr /><br />";
        
	}
	
	return $info;
	
}

// Purpose: update new fields from legacy fields, e.g. target_by_url => target_by_post
add_shortcode('update_snippet_logic', 'update_snippet_logic');
function update_snippet_logic ( $atts = [] ) { //function update_snippet_logic ( $snippet_id = null ) {

	// TS/logging setup
    $do_ts = true; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    $args = shortcode_atts( array(
        'snippet_id' => null,
    ), $atts );
    
    // Extract
	extract( $args );
	
    // Init vars
    $info = "";
	$ts_info = "";
	
	if ( $snippet_id === null ) { return false; }
	
	//if ( $snippet_id === null ) { $snippet_id = get_the_ID(); }
	//$snippet = get_post ( $snippet_id );
	//$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
	
	//
	$info .= '<div class="code">';
	$info .= ">> update_snippet_logic for snippet_id: $snippet_id<br />";
	//$info .= "widget_uid: $widget_uid<br />";
	
	// Get snippet logic
	// -- WIP
	$meta_keys = array( 'cs_post_ids', 'widget_logic_target_by_url', 'target_by_url', 'exclude_by_url', 'widget_logic_exclude_by_url', 'target_by_post_type', 'widget_logic_custom_post_types_taxonomies', 'target_by_location', 'widget_logic_location', 'widget_logic_taxonomy', 'target_by_taxonomy' );
	//$meta_keys = array( 'target_by_url_txt', 'exclude_by_url_txt', 'target_by_taxonomy', 'target_by_post_type', 'target_by_location' );
	foreach ( $meta_keys as $key ) {
	
		$$key = get_field( $key, $snippet_id );
		//$$key = get_post_meta( $snippet_id, $key, true );
		$key_ts_info = "";
		$key_ts_info .= "<strong>key: $key</strong><br />";
		//$key_ts_info .= "key: $key => ".$$key."<br />";
		//$key_ts_info .= "=> <pre>".print_r($$key, true)."</pre>";
		
		// If the key has a corresponding value, then proceed to process that value
		if ( !empty($$key) ) {
		
			//$key_ts_info .= "<strong>key: $key</strong><br />";
			//$key_ts_info .= "=> <pre>".print_r($$key, true)."</pre>"; // ." [count: ".count($$key)."]"
			
			// Unserialize as needed (legacy fields only, yes? -- perhaps consolidate with below)
			if ( !is_array($$key) && strpos($$key, '{') !== false ) {
				$key_ts_info .= "key: $key => ";//$key_ts_info .= "key: $key => ".$$key."<br />";
				$key_ts_info .= "unserialize...<br >";
				$$key = unserialize($$key);
				//$key_ts_info .= "unserialized key: $key => ".print_r($$key,true)."<br />";
			}	
				
			// Clean up legacy field values
			if ( !is_array($$key) && strpos($key, 'widget_logic_') !== false ) {
			
				$key_ts_info .= "widget_logic field; not array => clean up, update, and explode<br />";
				// Replace multiple (one or more) line breaks with a single one.
				$$key = preg_replace("/[\r\n]+/", "\n", $$key);
				// Update the legacy field with the cleaned-up version
				update_field( $key, $$key, $snippet_id );
			
				// Turn the text into an array of conditions
				$conditions = explode("\n",$$key);
						
			} else {
			
				$conditions = $$key;
				
			}
			
			//
			//if ( !is_array($conditions) ) { continue; }
			// TODO: fine-tune sorting in case of multidimensional or associative arrays(?)
			// TODO: run the following ONLY for one-dimensional non-associative arrays 
			//if ( is_array($conditions) ) { sort($conditions); }
			
			//$key_ts_info .= count($conditions)." condition(s)<br />";
			//$key_ts_info .= "conditions: <pre>".print_r($conditions, true)."</pre>";
			
			// TODO: streamline! get rid of code redundancy -- WIP 231027
			
			if ( $key == 'cs_post_ids' ) {
			
				if ( is_array($$key) ) {
					$$key = array_unique($$key);
					$key_ts_info .= count($$key)." $key<br />";
				} else {
					$key_ts_info .= "$key => ".print_r($$key,true)."<br />";
					continue; // can't do much with a non-array... wip
				}
				
				$matched_posts = array();
				$update_limit = 250;
				$key_ts_info .= "update_limit: ".$update_limit."<br />";
				
				$key_ts_info .= "-----------<br />";
				foreach ( $conditions as $condition ) {				
					$p_id = intval($condition);
					//$key_ts_info .= "p_id: ".$p_id."<br />";
					// Check to see if p_id is a valid post id
					$post = get_post( $p_id );
					if ( $post ) {
						$recurrence_id = get_post_meta( $p_id, '_recurrence_id', true );
						if ( $recurrence_id ) {
							$key_ts_info .= "p_id: ".$p_id."";
							$key_ts_info .= '&rarr; RID: <span class="nb">'.$recurrence_id.'</span><br />';
							$matched_posts[] = $recurrence_id; // WIP
						} else {
							//$key_ts_info .= "p_id: ".$p_id." (not attached to a recurring event)<br />";
							$matched_posts[] = $p_id;
							//$key_ts_info .= "postmeta: ".print_r(get_post_meta($id), true)."<br />";
						}
					} else {
						$key_ts_info .= "NO POST FOUND for p_id: ".$p_id."";
					}		
				}
				$matched_posts = array_unique($matched_posts);
				$key_ts_info .= count($matched_posts)." matched_posts<br />";
				
				//
				foreach ( $cs_post_ids as $x => $id ) {
					
					$post = get_post( $id );
					$post_info = $x.".) ".$post->post_title." [$id]";
				
					// Get post status -- we're only interested published posts
					$post_status = get_post_status( $id );
					if ( $post_status != "publish" ) { $post_info .= " <em>*** ".$post_status." ***</em>"; }
					$post_info .= " // ".$post->post_name." // ";
					//$post_info .= "<br />";
					// WIP 231117
					// TODO: look for patterns -- e.g. coffee-hour-following-the-9am-eucharist-*
				
					// Is this an attached instance of a recurring event?
					$recurrence_id = get_post_meta( $id, '_recurrence_id', true );
					if ( $recurrence_id ) {
						$post_info .= '&rarr; RID: <span class="nb">'.$recurrence_id.'</span>';
						// Remove individual instance id from ids array and save parent id instead? or.... WIP
					} else {
						//$post_info .= "postmeta: ".print_r(get_post_meta($id), true)."<br />";
					}
					$post_info .= "<br />";
					$info .= $post_info;
				}
				$info .= "<hr />";
				//
				
				// Save the matched posts to the 'cs_post_ids' snippet field -- this is largely for backup
				/*
				$update_key = $key;
				$updated_field_value = $matched_posts;
				//$updates = get_updated_field_value( $snippet_id, $update_key, $update_value, 'array' ); // post_id, key, new_value, type
				//$key_ts_info .= $updates['info'];
				//$updated_field_value = $updates['updated_value'];
				//if ( $updates && count($updated_field_value) > 0 ) {
				if ( count($updated_field_value) > 0 ) {
					$key_ts_info .= "about to update field '$update_key'<br />";
					$key_ts_info .= count($updated_field_value)." items in updated_field_value array<br />";
					//$key_ts_info .= count($updated_field_value)." items in updated_field_value array<br />";
					//$key_ts_info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
					//$key_ts_info .= "about to update field '$update_key' with value(s): ".print_r($updated_field_value, true)."<br />";					
					if ( count($updated_field_value) < $update_limit ) {
						if ( update_field( $key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= "--- No updates run -- update limit set to $update_limit records or fewer ---<br />";
					}
				} else {
					//$updated_field_value  = $matched_posts; // for purposes of secondary and tertiary updates
				}
				*/
				
				// Also save those same matched_posts/updated_field_value to the target_by_post field -- this field determines actual snippet display
				$key_ts_info .= "<br /><strong>Preparing for secondary snippet updates...</strong><br /><br />";
				$update_key = 'target_by_post';
				//$update_value = $updated_field_value;
				$update_value = $matched_posts;
				//
				$secondary_updates = get_updated_field_value( $snippet_id, $update_key, $update_value, 'array' ); // post_id, key, new_value, type
				$key_ts_info .= $secondary_updates['info'];
				$secondary_updated_field_value = $secondary_updates['updated_value'];
				if ( $secondary_updates && count($secondary_updated_field_value) > 0 ) {
					$key_ts_info .= "about to update field '$update_key'<br />";
					$key_ts_info .= count($secondary_updated_field_value)." items in secondary_updated_field_value array<br />";
					//$key_ts_info .= "=> <pre>".print_r($secondary_updated_field_value, true)."</pre>";
					//$key_ts_info .= "about to update field '$update_key' with value(s): ".print_r($secondary_updated_field_value, true)."<br />";
					if ( count($secondary_updated_field_value) < $update_limit ) {
						if ( update_field( $update_key, $secondary_updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= "--- No updates run -- update limit set to $update_limit records or fewer ---<br />";
					}
				}
				
				//
				$sidebar_id = get_post_meta( $snippet_id, 'sidebar_id', true );
				// Is this a Custom Sidebar? If so, update other snippets accordingly
				if ( strpos($sidebar_id, 'cs-') !== false ) {
				
					// Update other snippets to prevent display of these cs_post_ids
					// Update matching snippets with arr_ids...
					
					// TODO: figure out how to edit widget logic for remaining widgets (not snippets) to add cs- posts to list of exclusions
					// 
	
					// WIP 231113
					$key_ts_info .= "<br /><strong>Preparing for tertiary snippet updates...</strong><br /><br />";
					// add cs_posts_ids to widgets that are set to snippet_display == notselected
					// ... otherwise sidebar-1 widgets like News, Events will be displayed
					// ... AND add/merge into exclude_by_post field for snippet_display == selected
					$wp_args = array(
						'post_type'   => 'snippet',
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'fields'      => 'ids',
					);
					$meta_query = array(
						'relation' => 'AND',
						'snippet_display' => array(
							'key' => 'snippet_display',
							'value' => array('selected', 'notselected'),
							'compare' => 'IN',
						),
						'sidebar_id' => array(
							'key' => 'sidebar_id',
							'value' => 'cs-',
							'compare' => 'NOT LIKE',
						),
					);
					$wp_args['meta_query'] = $meta_query;
					$snippets = get_posts($wp_args);
					if ( $snippets ) {
						// This is tmp disabled because it resulted in way too many posts in the target_by_post field
						// TODO: figure out how to search for patterns, whether setting exclusions via wildcard URLs or taxonomies
						$key_ts_info .= "Found ".count($snippets)." snippets eligible for tertiary updates based on CS data<br /><hr /><br />";						
						//$key_ts_info .= "=> <pre>".print_r($snippets, true)."</pre>";
						//$key_ts_info .= "Found ".count($snippets)." snippets for args: ";
						//$key_ts_info .= "=> <pre>".print_r($wp_args, true)."</pre>";
						
						foreach ( $snippets as $i => $snip_id ) {
							$snippet_display = get_field('snippet_display', $snip_id, false);
							$sidebar_id = get_field('sidebar_id', $snip_id, false);
							if ( $snippet_display == "selected" ) {
								$update_key = 'exclude_by_post';
							} else {
								$update_key = 'cs_post_ids';
							}
							$key_ts_info .= $i.") id: ".$snip_id." [sidebar_id: ".$sidebar_id."/snippet_display: ".$snippet_display."/update_key: ".$update_key."]<br />";
							/*
							$update_value = $updated_field_value;
							$tertiary_updates = get_updated_field_value( $snip_id, $update_key, $update_value, 'array' ); // post_id, key, new_value, type
							$key_ts_info .= $tertiary_updates['info'];
							$tertiary_updated_field_value = $tertiary_updates['updated_value'];
							if ( $tertiary_updates && count($tertiary_updated_field_value) > 0 ) {
								$key_ts_info .= "about to update field '$update_key' for snip_id: $snip_id<br />";
								$key_ts_info .= count($tertiary_updated_field_value)." items in tertiary_updated_field_value array<br />";
								//
								$key_ts_info .= "--- tertiary updates tmp disabled ($update_key)<br />";
								if ( $update_key == 'cs_post_ids' ) { // text field, not relationship => save as string								
									$tertiary_updated_field_value = serialize($tertiary_updated_field_value);
									//$key_ts_info .= "serialized tertiary_updated_field_value: ".print_r($tertiary_updated_field_value,true)."<br />";
								}
								//
								//$key_ts_info .= "=> <pre>".print_r($tertiary_updated_field_value, true)."</pre>";
								//$key_ts_info .= "about to update field '$key' with value(s): ".print_r($tertiary_updated_field_value, true)."<br />";
								if ( update_field( $update_key, $tertiary_updated_field_value, $snip_id ) ) {
									$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snip_id<br />";
								} else {
									$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snip_id<br />";
								}
							}*/
							$key_ts_info .= "<br />";
						}
						
					}
					
				}
					
			} else if ( $key == 'widget_logic_target_by_url' || $key == 'target_by_url' || $key == 'widget_logic_exclude_by_url' || $key == 'exclude_by_url' ) {
				
				// Init arrays
				$matched_posts = array();
				$repeater_additions = array();
				$repeater_removals = array();				
				//
				if ( $key == 'widget_logic_target_by_url' || $key == 'target_by_url' ) {
					$target_key = 'target_by_post';
					$repeater_key = 'target_by_url';
				} else if ( $key == 'widget_logic_exclude_by_url' || $key == 'exclude_by_url' ) {
					$target_key = 'exclude_by_post';
					$repeater_key = 'exclude_by_url';
				}
				//
				$repeater_rows = get_field( $repeater_key, $snippet_id );
				if ( empty($repeater_rows) ) { 
					$repeater_rows = array();
					$repeater_values = array();
				} else {
					// Sort the existing repeater_rows and save the sorted array
					$repeater_values = array_column($repeater_rows, 'url');
					//$key_ts_info .= "repeater_rows repeater_values: ".print_r($repeater_rows, true)."<br />";
					array_multisort($repeater_values, SORT_ASC, $repeater_rows);
					update_field( $repeater_key, $repeater_rows, $snippet_id );
				}
				//$key_ts_info .= "existing repeater_rows: ".print_r($repeater_rows, true)."<br />";
				//
				// TODO: (re-)check repeater_rows to see if updates are needed from target_by_url => target_by_post
				//
				$key_ts_info .= "-----------<br />";
				foreach ( $conditions as $condition ) {
					//
					if ( empty($condition) ) { continue; }
					$condition_info = "";
					$url = null;
					$slug = null;
					$post_type = null;
					$matched_post_id = null;
					//
					if ( is_array($condition) ) {
						if ( isset($condition['url']) ) {
							$url = $condition['url'];
							$condition_info .= "url: ".$url."<br />";
						} else {
							$condition_info .= "condition: <pre>".print_r($condition, true)."</pre>";
							continue;
						}					
					} /*else if ( gettype($condition) == "string" ) {
						$p_id = intval($condition);
						$condition_info .= "p_id: ".$p_id."<br />";
						// Check to see if p_id is a valid post id
						$post = get_post( $p_id );
						if ( $post ) { $matched_post_id = $p_id; }
					} */else {
						$condition_info .= "condition: ".$condition." [".gettype($condition)."]<br />";
						$url = $condition;
					}					
					//
					if ( $url ) {
					
						$url_bk = $url; // in case we're relativizing and post is matched, so we can remove the url from the repeater array
						
						// Parse the url
						$hostname = parse_url($url, PHP_URL_HOST);
						if ( !empty($hostname) ) { $condition_info .= "&rarr; hostname: $hostname<br />"; }
						//
						$path = parse_url($url, PHP_URL_PATH);
						//$key_ts_info .= "&rarr; path: $path<br />";
						$querystring = parse_url($url, PHP_URL_QUERY);
						if ( !empty($querystring) ) {
							$querystring = "?".$querystring;
							$condition_info .= "&rarr; querystring: $querystring<br />";
						}
						
						// Does the URL contain a wildcard character? i.e. asterisk?
						// e.g. /shop*, events/*
						if ( strpos($url, '*') !== false ) {
							$wildcard = true;
							$condition_info .= "** Wildcard url => add to repeaters; can't be matched<br />";
							$repeater_additions[] = $url;
						} else {
							$wildcard = false;
						}
						
						// Is this an STC absolute URL? If so, remove the first bit
						if ( substr($url, 0, 4) == "http" ) {
							$condition_info .= "** Absolute url => relativize it [$url]<br />";
							//$url_bits = parse_url($url);
							// If this is an STC url, remove everything except the path
							if ( preg_match("/stc|saint/", $hostname) ) {
								$url = $path.$querystring;
								$condition_info .= "&rarr; revised url: $url<br />";
								$condition_info .= "&rarr; remove old: $url_bk<br />";
								$repeater_removals[] = $url_bk; // Remove the absolute URL
							}
						}
						//
						$date_validation_regex = "/\/[0-9]{4}\/[0-9]{1,2}\/[0-9]{1,2}/"; 
						if ( substr($url, 5) == "event" || substr($url, 1, 5) == "event" ) {
							$post_type = "event";
						} else if ( preg_match($date_validation_regex, $url) ) {
							$post_type = "post";
						}
						//
						if ( $post_type ) {
							// Extract slug from path
							// First, trim trailing slash, if any
							if ( substr($path, -1) == "/" ) { $path = substr($path, 0, -1); }
							$url_bits = explode("/",$path); // The last bit is slug
							$slug = end($url_bits);
							//$condition_info .= "url_bits: ".print_r($url_bits, true)."<br />";
							$condition_info .= "$post_type slug: $slug<br />";
						} else {
							$condition_info .= "path: $path<br />";
							//$condition_info .= "url: $url<br />";
						
						}
						// Look for matching post
						if ( $wildcard == false ) {
							if ( $slug && $post_type ) {
								$condition_info .= "get_page_by_path with slug: $slug; post_type: $post_type<br />";
								$matched_post = get_page_by_path($slug, OBJECT, $post_type);
							} else {
								$condition_info .= "get_page_by_path with path: $path<br />";
								$matched_post = get_page_by_path($path);
							}
							if ( $matched_post ) { $matched_post_id = $matched_post->ID; }
						} else {
							$condition_info .= "wildcard url => no attempt made to match post<br />";
						}
						
					}
					
					//
					if ( $matched_post_id ) {
						$condition_info .= "&rarr; matching post found with id: $matched_post_id<br />";
						$matched_posts[] = $matched_post_id;
						if ( $url ) {
							$condition_info .= "&rarr; remove from repeater_rows array: $url<br />";
							$repeater_removals[] = $url;
						}						
					} else {
						$condition_info .= "&rarr; NO matching post found<br />";
						if ( $url ) {
							$tmp_urls = array_column($repeater_rows, 'url');
							// Is the url already to be found in the repeater_rows
							$match_key = array_search($url, $tmp_urls); //$match_key = array_search($url, array_column($repeater_rows, 'url')); // not working -- why not?!?
							if ( $match_key ) {
								$condition_info .= "&rarr; The url '".$url."' is already in repeater_rows array at position ".$match_key."<br />";
							} else {
								// TODO: check to see if the url is already in the array!
								$repeater_additions[] = $url;
								$condition_info .= "&rarr; No match_key &rarr; Added url '".$url."' to repeater_additions array<br />";
							}
						}
					}
					$condition_info .= "---<br />";
					//
					$key_ts_info .= $condition_info;
					
				} // END foreach $conditions
				$key_ts_info .= "<hr />";
				
				// Save the matched posts to the snippet field
				$updates = get_updated_field_value( $snippet_id, $target_key, $matched_posts, 'array' ); // post_id, key, new_value, type
				$key_ts_info .= $updates['info'];
				$updated_field_value = $updates['updated_value'];
				if ( $updates && count($updated_field_value) > 0 ) {
					$key_ts_info .= "about to update field '$target_key'<br />";
					$key_ts_info .= count($updated_field_value)." items in updated_field_value array<br />";
					//$key_ts_info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
					//$key_ts_info .= "about to update field '$target_key' with value(s): ".print_r($updated_field_value, true)."<br />";
					if ( update_field( $target_key, $updated_field_value, $snippet_id ) ) {
						$key_ts_info .= "updated field: ".$target_key." for snippet_id: $snippet_id<br />";
					} else {
						$key_ts_info .= "update FAILED for field: ".$target_key." for snippet_id: $snippet_id<br />";
					}
				}
				
				// Update the associated repeater field as needed
				
				// First, remove duplicates and repeater_removals
				$repeater_rows_revised = array();
				//
				if ( !empty($repeater_rows) ) {
			
					$key_ts_info .= count($repeater_rows)." repeater_rows<br />"; //$key_ts_info .= "repeater_rows: <pre>".print_r($repeater_rows, true)."</pre>";//"<br />"; //<pre></pre>
				
					$key_ts_info .= "<h4>About to clean up repeater_rows by removing repeater_removals...</h4>";
					// Update repeater_rows array by removing removals
					if ( !empty($repeater_removals) ) {
						sort($repeater_removals); //$repeater_removals = array_unique($repeater_removals, SORT_REGULAR);
						//$key_ts_info .= "repeater_removals: <pre>".print_r($repeater_removals, true)."</pre>";
						foreach ( $repeater_rows as $k => $v ) {
							$repeater_url = $v['url'];
							//$key_ts_info .= "k: $k / repeater_url (v): $repeater_url<br />";
							if ( in_array($repeater_url, $repeater_removals) ) {
								$key_ts_info .= "The url: $repeater_url will NOT be added to the repeater_rows_revised array<br />";
								//$key_ts_info .= "removing url: $repeater_url<br />";
								//unset($repeater_rows[$k]);
							} else {
								//$key_ts_info .= "Adding url to repeater_rows_revised -- not in repeater_removals array: $repeater_url<br />";
								$repeater_rows_revised[] = array('url' => $repeater_url); //$repeater_rows_revised[$k] = $repeater_rows[$k];
							}
						}
					} else {
						$key_ts_info .= "repeater_removals array is empty<br />";
					}
				}
			
				// Second, add repeater_additions, making sure they're not duplicates...
				if ( !empty($repeater_additions) ) {
					$key_ts_info .= "<h4>About to add repeater_additions to repeater_rows...</h4>";
					//$key_ts_info .= "repeater_additions: <pre>".print_r($repeater_additions, true)."</pre>";
					foreach ( $repeater_additions as $url ) {
						// TODO: make sure url isn't a duplicate of an existing array item
						if ( in_array($url, $repeater_values) ) {
							$key_ts_info .= "The url '".$url."' is already in the repeater_rows array<br />";
						} else {
							$repeater_rows_revised[] = array('url' => $url);
							$key_ts_info .= "Added url '".$url."' to the repeater_rows_revised array<br />";
						}
					}
				}
			
				// Update the field with the revised array
				if ( !empty($repeater_rows_revised) ) {
				
					// Remove duplicates
					$key_ts_info .= "About to update repeater_rows...<br />";
					//
					//$repeater_rows_revised = array_unique($repeater_rows_revised, SORT_REGULAR); // not working!
					//
					$repeater_values = array_column($repeater_rows_revised, 'url');
					//$key_ts_info .= "repeater_rows_revised repeater_values: <pre>".print_r($repeater_values, true)."</pre><br />";
					array_multisort($repeater_values, SORT_ASC, $repeater_rows_revised);
					// Fix the sorting!
					//
					$key_ts_info .= "repeater_key: ".$repeater_key."<br />";
					//$key_ts_info .= "repeater_rows_revised: <pre>".print_r($repeater_rows_revised, true)."</pre><br />";
					//
					if ( $repeater_rows_revised == $repeater_rows ) {
						$key_ts_info .= "No changes necessary -- repeater_rows_revised == repeater_rows<br />";
					} else {
						//$key_ts_info .= "updates tmp disabled<br />";
						if ( update_field( $repeater_key, $repeater_rows_revised, $snippet_id ) ) {
							$key_ts_info .= "updated repeater field: ".$repeater_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for repeater field: ".$repeater_key." for snippet_id: $snippet_id<br />";
						}
					}
				
				}
				
				
			} else if ( $key == 'target_by_post_type' || $key == 'target_by_taxonomy_archive' || $key == 'widget_logic_custom_post_types_taxonomies' ) {
			
				// If this is the widget_logic version of the field, update our new target_by_post_type field
				if ( $key == 'widget_logic_custom_post_types_taxonomies' ) {
				
					$key_ts_info .= "conditions: <pre>".print_r($conditions, true)."</pre>";
					//
					$cpt_conditions = array();
					$cpt_archive_conditions = array();
					$tax_conditions = array();
					$updated_cpt_conditions = array();
					$updated_cpt_archive_conditions = array();
					$updated_tax_conditions = array();
					
					foreach ( $conditions as $condition => $value ) {
						$key_ts_info .= "condition: $condition => $value<br />";
						if ( strpos($condition, 'is_tax') !== false ) {
							// get rid of the is_tax- prefix before saving
							$condition = substr($condition,strlen('is_tax-'));
							$tax_conditions[] = $condition;
						} else if ( strpos($condition, 'is_archive') !== false ) {
							// get rid of the is_archive- prefix before saving
							$condition = substr($condition,strlen('is_archive-'));
							$cpt_archive_conditions[] = $condition;
						} else if ( strpos($condition, 'is_singular') !== false ) {
							// get rid of the is_singular- prefix before saving
							$condition = substr($condition,strlen('is_singular-'));
							$cpt_conditions[] = $condition;
						} else {
							$key_ts_info .= "uncategorized condition: $condition [$value]<br />";
						}			
					}
					$key_ts_info .= "tax_conditions: ".print_r($tax_conditions, true)."<br />";
					$key_ts_info .= "cpt_archive_conditions: ".print_r($cpt_archive_conditions, true)."<br />";
					$key_ts_info .= "cpt_conditions: ".print_r($cpt_conditions, true)."<br />";
					
					// CPT conditions
					$update_key = 'target_by_post_type';
					$updates = get_updated_field_value( $snippet_id, $update_key, $cpt_conditions, 'array' ); // post_id, key, new_value, type
					$key_ts_info .= $updates['info'];
					$updated_field_value = $updates['updated_value'];
					if ( $updates && count($updated_field_value) > 0 ) {
						$key_ts_info .= "about to update field '$update_key'<br />";
						$info .= count($updated_field_value)." items in updated_field_value array<br />";
						//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
						if ( update_field( $update_key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= count($updated_field_value)." count(updated_field_value) but no update because....???<br />";					
					}
					$key_ts_info .= "<hr />";
					/*
					$existing_cpt_conditions = get_field( 'target_by_post_type', $snippet_id );
					if ( empty($existing_cpt_conditions) ) {
						$key_ts_info .= "No existing_cpt_conditions => update `target_by_post_type` with widget_logic cpt_conditions<br />";
						$updated_cpt_conditions = $cpt_conditions;
					} else if ( $existing_cpt_conditions == $cpt_conditions ) {
						$key_ts_info .= "existing_cpt_conditions in `target_by_post_type` field same as widget_logic cpt_conditions => no update needed<br />";
					} else {
						// Merge the arrays
						$updated_cpt_conditions = array_unique(array_merge($existing_cpt_conditions, $cpt_conditions));
					}
					if ( $updated_cpt_conditions ) {							
						$key_ts_info .= "updated_cpt_conditions: ".print_r($updated_cpt_conditions, true)."<br />";
						if ( update_field( 'target_by_post_type', $updated_cpt_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_post_type` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_post_type` for snippet_id: $snippet_id<br />";
						}
					}
					*/
					
					// CPT Archive conditions
					$update_key = 'target_by_post_type_archive';
					$updates = get_updated_field_value( $snippet_id, $update_key, $cpt_archive_conditions, 'array' ); // post_id, key, new_value, type
					$key_ts_info .= $updates['info'];
					$updated_field_value = $updates['updated_value'];
					if ( $updates && count($updated_field_value) > 0 ) {
						$key_ts_info .= "about to update field '$update_key'<br />";
						$info .= count($updated_field_value)." items in updated_field_value array<br />";
						//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
						if ( update_field( $update_key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= count($updated_field_value)." count(updated_field_value) but no update because....???<br />";					
					}
					$key_ts_info .= "<hr />";
					/*
					$existing_cpt_archive_conditions = get_field( 'target_by_post_type_archive', $snippet_id );
					if ( empty($existing_cpt_archive_conditions) ) {
						$key_ts_info .= "No existing_cpt_archive_conditions => update `target_by_post_type_archive` with widget_logic cpt_archive_conditions<br />";
						$updated_cpt_archive_conditions = $cpt_archive_conditions;
					} else if ( $existing_cpt_archive_conditions == $cpt_archive_conditions ) {
						$key_ts_info .= "existing_cpt_archive_conditions in `target_by_post_type_archive` field same as widget_logic cpt_archive_conditions => no update needed<br />";
					} else {
						// Merge the arrays
						$updated_cpt_archive_conditions = array_unique(array_merge($existing_cpt_archive_conditions, $cpt_archive_conditions));
					}
					//
					if ( $updated_cpt_archive_conditions ) {							
						$key_ts_info .= "updated_cpt_archive_conditions: ".print_r($updated_cpt_archive_conditions, true)."<br />";
						if ( update_field( 'target_by_post_type_archive', $updated_cpt_archive_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_post_type_archive` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_post_type_archive` for snippet_id: $snippet_id<br />";
						}
					}
					*/
					
					// Taxonomy Archive Conditions
					$update_key = 'target_by_taxonomy_archive';
					$updates = get_updated_field_value( $snippet_id, $update_key, $tax_conditions, 'array' ); // post_id, key, new_value, type
					$key_ts_info .= $updates['info'];
					$updated_field_value = $updates['updated_value'];
					if ( $updates && count($updated_field_value) > 0 ) {
						$key_ts_info .= "about to update field '$update_key'<br />";
						$info .= count($updated_field_value)." items in updated_field_value array<br />";
						//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
						if ( update_field( $update_key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= count($updated_field_value)." count(updated_field_value) but no update because....???<br />";					
					}
					$key_ts_info .= "<hr />";
					/*
					$existing_tax_conditions = get_field( 'target_by_taxonomy_archive', $snippet_id );
					if ( empty($existing_tax_conditions) ) {
						$key_ts_info .= "No existing_tax_conditions => update `target_by_post_type` with widget_logic conditions<br />";
						$updated_tax_conditions = $tax_conditions;
					} else if ( $existing_tax_conditions == $tax_conditions ) {
						$key_ts_info .= "existing_tax_conditions in `target_by_post_type` field same as widget_logic conditions => no update needed<br />";
					} else {
						$updated_tax_conditions = array_unique(array_merge($existing_tax_conditions, $tax_conditions)); // Merge the arrays
					}
					//
					if ( $updated_tax_conditions ) {							
						$key_ts_info .= "updated_tax_conditions: ".print_r($updated_tax_conditions, true)."<br />";
						//$key_ts_info .= "updates tmp disabled<br />";
						if ( update_field( 'target_by_taxonomy_archive', $updated_tax_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_taxonomy_archive` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_taxonomy_archive` for snippet_id: $snippet_id<br />";
						}
					}
					*/
				}
				
			} else if ( $key == 'target_by_taxonomy' || $key == 'widget_logic_taxonomy' ) {
			
				//
				if ( $conditions ) { $key_ts_info .= "tax_pairs => <pre>".print_r($conditions, true)."</pre>"; } // tax_pairs => conditions			
				// ... WIP ...
					
				// WIP -- TODO: use fcns copied from WidgetContext customizations to split pairs into array and compare/merge etc
				//$target_taxonomies = get_field($key, $snippet_id, false);
				
			
			} else if ( $key == 'target_by_location' || $key == 'widget_logic_location' ) {
			
				// If this is the widget_logic version of the field, update our new target_by_post_type field (???)
				if ( $key == 'widget_logic_location' ) {
				
					$wll_conditions = array();
					$updated_conditions = array();
					
					foreach ( $conditions as $condition => $value ) {
						// TODO: if widget_logic condition is "is_single" => save instead as target_by_post_type = "post" // WIP 231115
						if ( $condition == "is_single" ) {
							$key_ts_info .= "special case: is_single<br />";
							/// NB: this code is redundant -- maybe figure out a way to streamline this?
							$update_key = 'target_by_post_type';
							$new_value = array( "post" );
							$updates = get_updated_field_value( $snippet_id, $update_key, $new_value, 'array' ); // post_id, key, new_value, type
							$key_ts_info .= $updates['info'];
							$updated_field_value = $updates['updated_value'];
							if ( $updates && count($updated_field_value) > 0 ) {
								$key_ts_info .= "about to update field '$update_key'<br />";
								$info .= count($updated_field_value)." items in updated_field_value array<br />";
								//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
								if ( update_field( $update_key, $updated_field_value, $snippet_id ) ) {
									$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
								} else {
									$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
								}
							} else {
								$key_ts_info .= count($updated_field_value)." count(updated_field_value) but no update because....???<br />";					
							}
							$key_ts_info .= "<hr />";							
							///
						} else {
							$wll_conditions[] = $condition;
						}						
					}
					
					// TODO: update to use new get_updated_field_value fcn
					$existing_conditions = get_field( 'target_by_location', $snippet_id );
					if ( empty($existing_conditions) ) {
						$key_ts_info .= "No existing_conditions => update `target_by_location` with widget_logic cpt_conditions<br />";
						$updated_conditions = $wll_conditions;
					} else if ( $existing_conditions == $wll_conditions ) {
						$key_ts_info .= "existing_conditions in `target_by_location` field same as widget_logic wll_conditions => no update needed<br />";
					} else {
						$updated_conditions = array_unique(array_merge($existing_conditions, $wll_conditions)); // Merge the arrays
					}
					//
					if ( $updated_conditions ) {							
						$key_ts_info .= "updated_cpt_conditions: ".print_r($updated_conditions, true)."<br />";
						if ( update_field( 'target_by_location', $updated_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_location` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_location` for snippet_id: $snippet_id<br />";
						}
					}
					
				}
				
				// WIP -- TODO: translate widget_logic_location to target_by_location options
				/*
				widget_logic e.g.:
				is_front_page
				is_home
				is_singular == All posts, pages and custom post types
				is_single
				is_page
				is_attachment => 1
				is_search
				is_404
				is_archive
				is_category == All category archives
				
				// Related WP functions
				is_archive(): bool == Archive pages include category, tag, author, date, custom post type, and custom taxonomy based archives.
				See also
				is_category()
				is_tag()
				is_author()
				is_date()
				is_post_type_archive()
				is_tax()
				//
				is_singular( string|string[] $post_types = '' ): bool == Determines whether the query is for an existing single post of any post type (post, attachment, page, custom post types).
				*/
				
			}
			
			$ts_info .= $key_ts_info;
			$ts_info .= "<hr />";
			
		} else {
			//$ts_info .= "No meta data found for key: $key<br />";
		}
	}
	
	if ( $do_ts ) { $info .= $ts_info; }
	
	$info .= '</div>';
	
	return $info;
	
}

//
function get_updated_field_value ( $post_id = null, $key = null, $new_value = null, $type = 'array' ) {

	// init
	$arr = array();
	$info = "";
	$info .= ">> get_updated_field_value for key: $key <<<br />";
	//
	if ( $type == 'array' ) {
		$updated_value = array();
	} else {
		$updated_value = null;
	}
	
	// Get existing field value, if any
	if ( $post_id ) {
		$old_value = get_field( $key, $post_id, false ); //get_field($selector, $post_id, $format_value);
		//$old_value = get_post_meta( $post_id, $key, true );
	} else {
		$old_value = null;
	}
	
	if ( $type == 'array' ) {
		
		//$info .= "field/var type == 'array'<br />";
		
		// Unserialize as needed -- TODO: eliminate redundancy
		if ( !is_array($old_value) && strpos($old_value, '{') !== false ) {
			$info .= "unserialize old_value...<br >";
			$old_value = unserialize($old_value);
			//$info .= "=> ".print_r($old_value,true)."<br />";
		}
		//if ( !is_array($old_value) && !empty($old_value) ) { $old_value = json_decode($old_value); }
		
		// Sort the existing values and save the sorted array
		if ( is_array($old_value) && !empty($old_value) ) {
			$info .= count($old_value)." items in old_value array<br />";
			//$info .= "=> ".print_r($old_value, true)."<br />"; //"<pre></pre>";
			// TODO: what about if this isn't an array of post ids? generalize... tbd
			$old_value_sorted = sort_post_ids_by_title($old_value);
			if ( $old_value_sorted ) {
				$info .= $old_value_sorted['info'];
				$old_value = $old_value_sorted['post_ids'];
				//$info .= "old_value (sorted): ".print_r($old_value, true)."<br />"; //"<pre></pre>";
			}
			// re-serialize?
			//update_field( $target_key, $old_value, $snippet_id );
		} else if ( empty($old_value) ) {
			$info .= "old_value is empty<br />";
		} else {
			$info .= "old_value: ".print_r($old_value, true)."<br />";
		}
		
		// Evaluate the new data
		if ( !empty($new_value) ) {
		
			$info .= count($new_value)." items in new_value array<br />";
			//$info .= "new_value: <pre>".print_r($new_value, true)."</pre>";
			
			// If we're dealing w/ an array of post ids, sort them by post title
			if ( $key == "target_by_post" || $key == "exclude_by_post" ) {
				$new_value_sorted = sort_post_ids_by_title($new_value);
				if ( $new_value_sorted ) {
					$info .= $new_value_sorted['info'];
					$new_value = $new_value_sorted['post_ids'];
					$info .= "new_value sorted (sort_post_ids_by_title)<br />";
					//$info .= "new_value (sorted): ".print_r($new_value, true)."<br />"; //"<pre></pre>";
				}
			}			
			// TODO, maybe: look for patterns in post types, categories, if there are many similar posts? (e.g. instances of recurring events)
			// Determine whether an update is needed
			if ( empty($old_value) ) {
				$info .= $key." field is empty (old_value) >> use new_value<br />";
				$updated_value = $new_value;
			} else if ( $new_value == $old_value ) {
				$info .= "new_value for '$key' same as old_value => no update needed<br />";
			} else {
				// Merge old and new arrays
				$info .= "Merge old_value with new_value for '$key' field<br />";
				$updated_value = array_unique(array_merge($old_value, $new_value));
				sort($updated_value); // Sort the array -- TODO: sort instead by post title
				$info .= count($updated_value)." items in updated_value array<br />";
			}
		
		} else {
		
			$info .= "new_value array is empty ==> no update needed<br />";
			
		}
		
	} else {
		// WIP/TBD as needed...
	}
	
	//$info .= "updated_value: ".print_r($updated_value, true)."<br />"; //"<pre></pre>";
	
	$arr['info'] = $info;
	$arr['updated_value'] = $updated_value;
	
	//return $updated_value;
	return $arr;
	
}

/*** Copied from mods to WidgetContext ***/
//
function match_terms( $rules, $post_id ) {
        
    $ts_info = "";
    
	if ( $post_id == null ) { 
		$post_id = get_the_ID();
		//$post_type = get_post_type( $post_id );
	}
	$ts_info .= "post_id: ".$post_id."<br />";
	
	if ( function_exists('sdg_log') ) { 
		//sdg_log("divline2");
		//sdg_log("function called: match_terms");
		//sdg_log("post_id: ".$post_id);
	}
	
	// init/defaults
	//$match = false;
	//$arr_tterms = array(); // Multidimensional array of taxonomies & terms
	
	// Determine the match_type
	if ( (strpos($rules, '||') !== false && strpos($rules, '&&') !== false )  // String includes both 'and' AND 'or' operators
		|| preg_match("/\s*\:\s*-\s*/", $rules) != 0 // has_exclusions
		//|| preg_match("/(\\()*(\\))/", $x) !== false // String contains something (enclosed within parens)
		) { 
		$match_type = 'complex';
		$matches_found = 0;
	} else if ( 
		strpos($rules, '&&') !== false // If string contains "and" but no or operator, and no parens
		|| preg_match("/match_type\s*\:\s*all/", $rules) != 0 // Match if string contains "match_type:all" (with or without whitespace around colon)
		|| preg_match("/\s*\:\s*-\s*/", $rules) != 0 // has_exclusions
		) {
		$match_type = 'all';
	} else {
		$match_type = 'any'; // Default: match any of the given terms
	}
	$ts_info .= "match_type: ".$match_type."<br />";
	$ts_info .= "rules (str): ".$rules."<br />";
	
	if ( function_exists('sdg_log') ) { 
		//sdg_log("rules (str): '".$rules."'");
		//sdg_log("match_type: ".$match_type); // ."; has_exclusions: ".$has_exclusions
	}
	
	// Explode the rules string into an array, items separated by line breaks
	$pairs = explode( "\n", $rules );
	//if ( function_exists('sdg_log') ) { sdg_log("pairs: ".print_r($pairs,true)); }
	
	// Build an associative array of the given rules
	//$arr_rules = array_map('process_tax_pair', $pairs); // why doesn't this work???
	$arr_rules = array();
	foreach ( $pairs as $pair ) {
		$arr_rules[] = process_tax_pair($pair);
	}
	
	if ( function_exists('sdg_log') ) { 
		if ( !empty($arr_rules) ) {
			//sdg_log"arr_rules: ".print_r($arr_rules,true));
		} else {
			//sdg_log"arr_rules is empty.");
		}
	}
			
	/*
	// TODO: deal w/ possibility of combinations of terms -- allow e.g. has term AND term (+); has term OR term; has term NOT term (-)?

	e.g. 
	match_type:complex
	(event-categories:worship-services
	|| event-categories:video-webcasts)
	&&
	sermon_topic:abraham
	
	e.g.
	(event-categories:worship-services && category:music) 
	|| sermon_topic:abraham 
	
	*/
	
	$num_rules = count($arr_rules);
	
	if ( $arr_rules ) {
		foreach ( $arr_rules as $rule ) {
			
			if ( empty($rule) ) { continue; }
			
			$taxonomy = $rule['taxonomy'];
			$term = $rule['term'];
			if ( isset($rule['operator']) ) { $operator = $rule['operator']; } else { $operator = null; }
			$exclusion = $rule['exclusion'];
			//if ( function_exists('sdg_log') ) { sdg_log("term: ".$term."; taxonomy: ".$taxonomy."; operator: ".$operator."; exclusion: ".$exclusion); }
			$ts_info .= "term: ".$term."; taxonomy: ".$taxonomy."; operator: ".$operator."; exclusion: ".$exclusion."<br />";
			
			if ( $taxonomy == 'match_type' || empty($taxonomy) ) {
				//if ( function_exists('sdg_log') ) { sdg_log("match_type or empty >> continue"); }
				continue; // This is not actually a taxonomy rule; move on to the next.
			}
			
			// Check to see if taxonomy even APPLIES to the given post before worrying about whether it matches a specific term in that taxonomy
			$arr_taxonomies = get_post_taxonomies(); // get_post_taxonomies( $post_id );
			if ( !in_array( $taxonomy, $arr_taxonomies ) ) {
				$ts_info .= "taxonomy '$taxonomy' does not apply => arr_taxonomies: ".print_r($arr_taxonomies,true)."<br />";
				continue;
			}
			
			// Handle the matching based on the number and complexity of the rules
			if ( $num_rules == 1 ) {
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $exclusion == 'no' ) {
						$ts_info .= "Match found (single rule; has_term; exclusion false) >> return true<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (single rule; has_term; exclusion false) >> return true"); }
						//return $ts_info; //
						return true; // post has term for single rule AND term is not negated, therefore it is a match
					} else {
						$ts_info .= "Match found (single rule; has_term; exclusion TRUE) >> return false<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (single rule; has_term; exclusion TRUE) >> return false"); }
						return false;
					}                        
				} else if ($exclusion == 'no') {
					$ts_info .= "NO match found (single rule; NOT has_term; exclusion false) >> return false<br />";
					//if ( function_exists('sdg_log') ) { sdg_log("NO match found (single rule; NOT has_term; exclusion false) >> return false"); }
					return false; // post has term but single rule requires posts withOUT that term, therefore no match
				}
				
			} else if ( $match_type == 'any' && has_term( $term, $taxonomy, $post_id ) && $exclusion == 'no' ) { 
				
				$ts_info .= "Match found (match_type 'any'; has_term; exclusion false) >> return true<br />";
				//if ( function_exists('sdg_log') ) { sdg_log("match found (match_type 'any'; has_term; exclusion false) >> return true"); }
				return true; // Match any => match found (no need to check remaining rules, if any)
				
			} else if ( $match_type == 'all' ) {
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $exclusion == 'yes' ) {
						$ts_info .= "Match found (match_type 'all'; has_term; exclusion TRUE) >> return false<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (match_type 'all'; has_term; exclusion TRUE) >> return false"); }
						return false; // post has the term but rules say it must NOT have this term
					} else {
						$ts_info .= "Ok so far! (match_type 'all'; has_term; exclusion false) >> continue<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Ok so far! (match_type 'all'; has_term; exclusion false) >> continue"); }
					}
				} else if ( $exclusion == 'no' ) {
					$ts_info .= "NO match found (match_type 'all'; NOT has_term; exclusion false) >> return false<br />";
					//if ( function_exists('sdg_log') ) { sdg_log("NO match found (match_type 'all'; NOT has_term; exclusion false) >> return false"); }
					return false; // post does not have the term and rules require it must match all
				}
				
			} else if ( $match_type == 'complex' ) {
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $exclusion == 'yes' ) {
						$ts_info .= "Match found (match_type 'complex'; has_term; exclusion TRUE) >> return false<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (match_type 'complex'; has_term; exclusion TRUE) >> return false"); }
						return false; // post has the term but rules say it must NOT have this term
					} else {
						$ts_info .= "Ok so far! (match_type 'complex'; has_term; exclusion false) >> continue<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Ok so far! (match_type 'complex'; has_term; exclusion false) >> continue"); }
						$matches_found++;
					}
				} else if ( $exclusion == 'no' ) {
					$ts_info .= "NO match found (match_type 'complex'; NOT has_term; exclusion false) >> return false (?)<br />";
					//if ( function_exists('sdg_log') ) { sdg_log("NO match found (match_type 'complex'; NOT has_term; exclusion false) >> return false"); }
					//return false; // post does not have the term and rules require it must match all
				}
				
			}
			
			/*
			// Store terms in tterms array for match_type = all and complex matching once the loop is finished
			if ( $exclusion == true ) {
				$term = "-".$term;
			}
			if ($arr_tterms[$taxonomy]) {
				array_push($arr_tterms[$taxonomy],$term);
			} else {
				$arr_tterms[$taxonomy] = array($term);
			}
			*/
			
		} // end foreach $arr_rules
		
		// If we got through the entire list of rules and the post matched all the rules, return true
		if ( $match_type == 'all' ) {
			//if ( function_exists('sdg_log') ) { sdg_log("Matched! (match_type 'all') >> return true"); }
			return true;
		} else if ( $match_type == 'complex' && $matches_found > 0 ) {
			//if ( function_exists('sdg_log') ) { sdg_log("Matched! (match_type 'complex') with at least one positive match (and no matches to excluded categories) >> return true"); }
			return true;
		}
		
		// Now all that's left is to deal with complex queries...
		
		//if ( !empty($operator) )
		
		/*if ( $arr_tterms ) {
			
			foreach ( $arr_tterms as $taxonomy => $arr_terms ) {
				sdg_log("taxonomy: ".$taxonomy."; arr_terms: ".print_r($arr_terms, true));
				
				$post_terms = get_the_terms( $post_id, $taxonomy );
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $match_type == 'all' ) { // If a match has been found and this is a simple query, go ahead and return match/true
						return true;
					} else if ( $match_type == 'complex' ) {
						//
					}
				}
			}
		}*/
		
	}

	//if ( function_exists('sdg_log') ) { sdg_log("End of the line. Returning null."); }
	return null;
	//return $match;
	
}

//
function process_tax_pair($rule) {
	
	if ( function_exists('sdg_log') ) { 
		sdg_log("function called: process_tax_pair");
		sdg_log("rule: ".$rule); 
	}
	
	$arr = array();

	// If this is an empty line, i.e. doesn't actually contain a pair, then return false
	if (strpos($rule, ':') === false) {
		return $arr;
	}
	
	// Remove all whitespace
	$x = preg_replace('/\s+/', '', $rule);
	
	// Check for operator
	if (strpos($x, '||') !== false) {
		$arr['operator'] = 'OR';
		$x = str_replace('||','',$x);
	} elseif (strpos($x, '&&') !== false) {
		$arr['operator'] = 'AND';
		$x = str_replace('&&','',$x);
	}
	
	// Check for minus sign to indicate EXclusion
	if (strpos($x, ':-') !== false) {
		$arr['exclusion'] = 'yes';
	} else {
		$arr['exclusion'] = 'no';
	}
	
	$arr['taxonomy'] = trim(substr($x,0,stripos($x,":")));
	//$arr['term'] = trim(substr($x,stripos($x,":")+1));
	$term = trim(substr($x,stripos($x,":")+1));
	$term = ltrim($term,"-");
	$arr['term'] = $term;

	/*
	$taxonomy = trim(substr($x,0,stripos($x,":")));
	$arr['taxonomy'] = $taxonomy;
	$arr['term'] = $term;

	if ($arr_tterms[$taxonomy]) {
		array_push($arr_tterms[$taxonomy],$term);
	} else {
		$arr_tterms[$taxonomy] = array($term);
	}
	*/

	return $arr;
	
}

//
function make_terms_array($x) {
	$arr = array(trim(substr($x,0,stripos($x,":"))),trim(substr($x,stripos($x,":")+1)));
	return $arr;
}

/*** END copied from WidgetContext ***/

// WIP
/*
https://developer.wordpress.org/reference/functions/wp_find_widgets_sidebar/
wp_find_widgets_sidebar( string $widget_id ): string|null
Finds the sidebar that a given widget belongs to.
$widget_id string Required
The widget ID to look for.

Return
string|null The found sidebar's ID, or null if it was not found.
*/
function get_sidebar_id( $uid_to_match = null) {
	
	$info = "";
	
	$arr_sidebars_widgets = get_option('sidebars_widgets'); // array of sidebars and their widgets (per sidebar id, e.g. "wp_inactive_widgets", "cs-11" )
	
	// Loop through sidebars to look for match
	
	//$info .= "<h2>Sidebars/Widgets</h2>";
	//$info .= "<pre>arr_sidebars_widgets: ".print_r($arr_sidebars_widgets,true)."</pre><hr /><hr />";
	foreach ( $arr_sidebars_widgets as $sidebar_id => $widgets ) {
		
		// Get the registered sidebar info -- name, id, description, before_widget, etc.
		$sidebar_name = null; // init
		$sidebar_info = wp_get_sidebar( $sidebar_id );
		if ( $sidebar_info ) { $sidebar_name = $sidebar_info['name']; }
		
		// Is this a Custom Sidebar?
		if ( strpos($sidebar_id, 'cs-') !== false ) {
			$custom_sidebar = true;
			//if ( $sidebar == "cs-29" ) { $info .= "Sermons sidebar... skip it for now<br />"; continue; } // Sermons sidebar. Special case
		} else {
			$custom_sidebar = false;
		}
		
		$info .= "<h3>sidebar: ";
		$info .= $sidebar_id;
		if ( $sidebar_name ) { $info .= ' => "'.$sidebar_name.'"'; }
		if ( $custom_sidebar ) { $info .= " [cs]"; }
		//$info .= " => sidebar_info: <pre>".print_r($sidebar_info,true)."</pre>";
		$info .= "</h3>";
		
		//$info .= "sidebar: ".$sidebar." => widgets: <pre>".print_r($widgets,true)."</pre><hr />";
		//$info .= "widgets: <pre>".print_r($widgets,true)."</pre><hr />";
		
		$info .= '<div class="code">';
		
		// Loop through widgets and create corresponding snippet records
		if ( is_array($widgets) ) {
		
			//$info .= "<h4>Widgets</h4>";
			foreach ( $widgets as $i => $widget_uid ) {			
				if ( $widget_uid == $uid_to_match ) {
					$info .= "Matching widget found in sidebar: ".$sidebar_id."<br />";
					return $sidebar_id;
				}
			}
		}
		$info .= "&rarr; NO Matching widget found in sidebar: ".$sidebar_id." (".$sidebar_name.")<br />";
		$info .= '</div>';
	}
	
	//return $info;
	return null;

}

// WIP
add_shortcode('widget_and_snippets', 'show_widgets_and_snippets');
function show_widgets_and_snippets ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: show_widgets_and_snippets", $do_log );
    
    $info = "";
    $ts_info = "";
    
    $args = shortcode_atts( array(
		'limit'   => 1,
		'post_id' => null,
    ), $atts );
    
    // Extract
	extract( $args );
	
	// Get widgets and snippets set to display per post
	
	// If no post_id has been specified, get random post ids according to $limit
	// TODO
	
	if ( $post_id === null ) { return "No post_id; "; } // tft
	//
	$arr_sidebars_widgets = get_option('sidebars_widgets');
	$cs_sidebars = get_option('cs_sidebars');
	//
	$info .= '<div class="code">';
	
	$info .= "<h3>post_id: ".$post_id." -- ".get_the_title($post_id)."</h3>";
	
	// Default sidebar_id
	$sidebar_id  = "sidebar-1";
	
	// Check for custom sidebars 
	$cs_replacements = get_post_meta( $post_id, '_cs_replacements', true );
	if ( $cs_replacements ) { 
		$info .= "cs_replacement: ".print_r($cs_replacements, true)."<br />";
		$first_key = array_key_first($cs_replacements);
		if ($first_key !== null) {
			$sidebar_id = $cs_replacements[$first_key];
		}
	} else {
		// Get page template? i.e. make sure this post uses a sidebar at all...
		//...
		$info .= "This post uses the default sidebar.<br />";
	}
	$info .= "sidebar_id: ".$sidebar_id."<br />";
	
	//$sidebar = wp_list_widget_controls($sidebar_id); // Show the widgets and their settings for a sidebar -- Used in the admin widget config screen -- DN seem to work at all on front end
	$sidebar = wp_get_sidebar( $sidebar_id ); // Retrieves the registered sidebar with the given ID: name, id, description, before_widget, etc.
	//$info .= "sidebar: ".print_r($sidebar, true)."<br />";
	$info .= '=> "'.$sidebar['name'].'"<br />';
	
	// Get widgets
	// -------
	$widgets = array(); // init
	if ( isset($arr_sidebars_widgets[$sidebar_id]) ) {
		$widgets = $arr_sidebars_widgets[$sidebar_id];
	} else if ( isset($cs_sidebars[$sidebar_id]) ) {
		$widgets = $cs_sidebars[$sidebar_id];
	}
	//$info .= "widgets: <pre>".print_r($widgets, true)."</pre>";
	
	// WIP --  // TODO: fix this -- not working because it's not filtering according to post_id passed to fcn, but rather according to current URL
	/*
	$sidebars_widgets = array( $sidebar_id => $widgets );
	$sidebars_widgets_filtered = apply_filters( 'sidebars_widgets', $sidebars_widgets );
	$filtered_widgets = $sidebars_widgets_filtered[$sidebar_id];
	//
	*/
	/*
	foreach ( $sidebars_widgets as $widget_area => $widget_list ) {

		if ( 'wp_inactive_widgets' === $widget_area || empty( $widget_list ) ) {
			continue;
		}

		foreach ( $widget_list as $pos => $widget_id ) {

			if ( ! $this->check_widget_visibility( $widget_id ) ) {
				unset( $sidebars_widgets[ $widget_area ][ $pos ] );
			}
		}
	}
	*/
	/*
	//$filtered_widgets = maybe_unset_widgets_by_context( $sidebars_widgets );
	//$info .= "filtered_widgets: <pre>".print_r($filtered_widgets, true)."</pre>";
	$info .= '<div class="code float-left" style="width: 49%;">';
	$info .= "filtered_widgets [WIP]:<br />";
	//$info .= "<pre>";
	foreach ( $filtered_widgets as $pos => $widget_uid ) {
		$info .= "[".$pos."] => ".$widget_uid;
		// If this is a text or html widget, get more info from the corresponding snippet records
		$snippet_id = get_snippet_by_widget_uid ( $widget_uid );
		if ( $snippet_id ) {
			$info .= " => snippet_id: ".$snippet_id;
			$info .= " => ".get_the_title($snippet_id);
		}
		//." => ".$widget_title
		$info .= "<br />";
	}
	//$info .= "</pre>";
	$info .= "</div>";
	*/
	// Get snippets
	// -------
	$snippets = get_snippets ( array( 'post_id' => $post_id, 'return' => 'ids', 'sidebar_id' => $sidebar_id ) );
	
	if ( $snippets ) {
		$info .= '<div class="code">'; // float-left" style="width: 49%;
		$info .= "snippets:<br />";
		//$info .= "<pre>".print_r($snippets, true)."</pre>";
		//$info .= "<pre>";
		$details = "";
		foreach ( $snippets as $pos => $snippet_id ) {
			$snippet_info = "";
			$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
			$widget_sidebar_id = get_post_meta( $snippet_id, 'sidebar_id', true );
			$snippet_info .= "[".$pos."] => ".$widget_uid;
			$snippet_info .= " => snippet_id: ".$snippet_id;
			$snippet_info .= " => ".get_the_title($snippet_id);
			$snippet_info .= " [".$widget_sidebar_id."]";
			$snippet_info .= "<br />";
			//
			$info .= $snippet_info;
			$details .= $snippet_info;
			// Check/update widget/snippet logic
			//$details .= 
		}
		//$info .= "</pre>";
		$info .= '</div>';
	}
	
	$info .= "</div>";
	
	$info .= '<hr class="clear" />';
	
	return $info;
	
}

// TODO: write fcn to to delete (inactive) html and text widgets that have been already converted — per sidebar ID
function widgets_cleanup() {

}

?>