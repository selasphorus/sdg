<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** CPT: SERMON ***********/

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

// WIP -- handle options via ACF instead -- see sdg.php

// Register our sdg_settings_init to the admin_init action hook.
//add_action( 'admin_init', 'sdg_sermons_settings_init' );

/**
 * Custom option and settings
 */
function sdg_sermons_settings_init() {

	// Register a new setting for "sdg_sermons" page.
	register_setting( 'sdg_sermons', 'sdg_sermons_settings' ); // register_setting( string $option_group, string $option_name, array $args = array() )

	// Register a new section in the "sdg_sermons" page.
	//add_settings_section( string $id, string $title, callable $callback, string $page, array $args = array() )
	add_settings_section(
		'sdg_sermons_settings',
		__( 'SDG Sermons Module Settings', 'sdg' ), 
		'sdg_sermons_settings_section_callback',
		'sdg_sermons'
	);
	
	// Register a new field in the "sdg_settings" section, inside the "sdg_sermons" page.
	//add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
	add_settings_field(
		'sdg_sermons_archive_content',
		__( 'Sermons Archive Content', 'sdg' ),
		'sdg_archive_content_field_cb',
		'sdg_sermons',
		'sdg_sermons_settings',
		['id' => 'sdg_sermons_archive_content', 'name' => 'sdg_sermons_archive_content', 'placeholder' => __('Sermons Archive Content', 'sdg'), 'default_value' => '', 'class' => 'form-field form-required', 'style' => 'width:25rem']
	);
	
	// WIP/TODO: image field -- for archive featured image
	
	// WIP/TODO: text area (WYSIWYG?) for top-of-page content
	
	
}

/**
 * Settings section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function sdg_sermons_settings_section_callback( $args ) {

	$options = get_option( 'sdg_sermons_settings' );
	//echo "options: <pre>".print_r($options,true)."</pre>"; // tft
	//echo '<!--p id="'.esc_attr( $args['id'] ).'">'.esc_html_e( 'Test Settings Section Header', 'sdg' ).'></p-->';

}

// Register our options page to the admin_menu action hook.
//add_action( 'admin_menu', 'sermons_register_options_page' );

/**
 * Adds a submenu page under a custom post type parent.
 */
 
/*function sermons_register_options_page() {

	// Fcn add_submenu_page is similar to add_options_page, but with control over placement in CMS as submenu of CPT instead of options-general.php
    //add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = '', int|float $position = null ): string|false
    add_submenu_page(
        'edit.php?post_type=sermon',
        __( 'Sermons CPT Options', 'sdg' ),
        __( 'Sermons Options', 'sdg' ),
        'manage_options',
        'sermons-cpt-options',
        'sermons_options_page_callback'
    );
    
}*/

/**
 * Display callback for the submenu page.
 */

/*
function sermons_options_page_callback() { 
    
    // check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// add error/update messages

	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'sdg_messages', 'sdg_message', __( 'Settings Saved', 'sdg' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'sdg_messages' );
	
	// Include the form to display the setting fields
	//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sdg-admin-settings.php';
	
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "sdg"
			settings_fields( 'sdg_sermons' );
			
			// output setting sections and their fields
			// (sections are registered for "sdg", each field is registered to a specific section)
			do_settings_sections( 'sdg_sermons' );
			
			// output save settings button
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
	
}
*/
/*
// Render a text field
function sdg_archive_content_field_cb( $args ) {

	$options = get_option( 'sdg_sermons_settings' );
	
	//echo "args: <pre>".print_r($args,true)."</pre>"; // tft
	//echo "options: <pre>".print_r($options,true)."</pre>"; // tft
	
	$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : esc_attr( $args['default_value']);
	$class = isset($args['class']) ? $args['class'] : '';
	$style = isset($args['style']) ? $args['style']: '';
	
	echo '<textarea 
		id="'.esc_attr( $args['id'] ).'" 
		name="sdg_settings['.esc_attr( $args['name'] ).']" 
		value="'.$value.'" 
		class="'.$class.'" 
		style="'.$style.'" 
		placeholder="'.esc_attr( $args['placeholder'] ).'"></textarea>';
		
}
*/

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

/**
 *
 */
function get_cpt_sermon_meta( $post_id = null ) {
	
	/* 
	// Model from old site for archive listing:
	Sunday, July 14, 2019 	-- Date sermon delivered
    + THE FIFTH SUNDAY AFTER PENTECOST (PR 10) 	-- Day Title
    It???s not about being good; it???s about trying to be a neighbor 	-- Sermon Title
    Fr Turner | 11:00 am Choral Eucharist 	-- Preacher | Service Time & Title
    Deuteronomy 30:9-14	, I Corinthians 1:1-14	, Luke 10:25-37 	-- Scriptural References
	
	//-- STC
	// Model from old site for singular listing:
	Sunday August 25, 2019
	11:00 am - Saint Thomas Church 
	Preacher: Fr Turner
    */
	
    // init
	$info = "";
    $sermon_date = "";
    $arr_posts = array();
    
	if ($post_id === null) { $post_id = get_the_ID(); }
	//$info .= "sermon post_id: $post_id<br />";
    
	$post = get_post( $post_id ); // Is this necessary?
	
	$info .= '<!-- cpt_sermon_meta -->';
    $info .= '<!-- sermon_id: '.$post_id.' -->';
	
	// Display the sermon author
    $authors = get_field('sermon_author', $post_id);
	if ($authors) { 
        
        foreach( $authors as $author ){
            // TODO: hyperlink author(s)?
            $info .= '<span class="preacher author">'.get_the_title( $author->ID ).'</span>';
        }
		//$info .= '<span class="preacher author">'.the_field('sermon_author', $post_id).'</span>';
	}
    
	// Show the link(s) to the Service(s) at which the sermon was delivered
    $related_events = get_field('related_event', $post_id); // fast retrieval but impractical in terms of data entry without mods to display of event titles in UI
	if ($related_events) {
        if ($authors) { $info .= " | "; }
        foreach( $related_events as $related_event ){
            $info .= make_link( get_the_permalink( $related_event->ID ), get_the_title( $related_event->ID ) );
            $info .= '<!-- related_event->ID: '.$related_event->ID.' -->';  
        }      
    }
    
    // TODO: try, instead, matching sermon_date to _event_start_date? But what if a sermon was delivered on multiple occasions...?
    
    /*
    // This approach, retrieving related events by checking program rows, is terribly, terribly slow. Also it's dumb to have to do this every time.
    // TODO: Check to see if related_event field for sermon is populated; if not, try matching via program rows and then store the matching value(s) in that custom sermon field.
    $related_events = get_related_events ( "program_item", $post_id ); // 
    $event_posts = $related_events['event_posts'];
    $related_events_info = $related_events['info'];
    
    if ( $event_posts ) { 
        if ($authors) { $info .= " | "; }
        //-- STC
        //$info .= "<h3>Performances at Saint Thomas Church:</h3>";
        $x = 1;
        foreach($event_posts as $event_post) { 
            setup_postdata($event_post);
            //$info .= "[$x] event_post: <pre>".print_r($event_post, true)."</pre>"; // tft
            $event_post_id = $event_post->ID;
            
            // TODO: modify to show title & event date as link text
            $event_title = get_the_title($event_post_id);
            $date_str = get_post_meta( $event_post_id, '_event_start_date', true );
            if ( $date_str ) { $event_title .= ", ".$date_str; }
            $info .= make_link( get_the_permalink($event_post_id), $event_title, null, "_blank" ) . "<br />"; //( $url, $linktext, $class = null, $target = null)
            
            $x++;
        }
    }
    */
    
    // TODO: revise to display event title plus date/time -- and only show sermon_date if no event is linked
	if ( $related_events ) {
		// Show date/time/title of related_event, aka service
	} else {
        // Show the sermon date/time
    }
    
    // Show the sermon date/time -- for archives ONLY, moved to apostle/template-parts/content-sermon.php so as to display date ABOVE sermon title, a l?? event archives.
    $sermon_date = get_field('sermon_date', $post_id);
    if ( is_singular('sermon') && $sermon_date ) {
        
        $info .= '<div class="calendar-date">';
        //$info .= "<!-- sermon_date: ".print_r($sermon_date, true)."-->"; // tft
		$date = date_create($sermon_date);
		$the_date = date_format($date,"l, F d, Y \@ h:i a");
		$info .= $the_date."<br />";
        if ( function_exists('get_day_title') ) { $info .= get_day_title( array ('the_date' => $sermon_date ) ); }
		$info .= '</div>';
        
    }
    
    // Sermon Webcast (Audio)
    if ( is_singular('sermon') 
        && has_term( 'webcasts', 'admin_tag', $post_id ) 
        && get_post_meta( $post_id, 'audio_file', true ) != "" ) { 
        $sermon_audio = true;
        $info .= '<a href="#sermon-audio">Listen to the sermon</a>';
        $info .= "<!-- audio_file: ".get_post_meta( $post_id, 'audio_file', true )." -->";
    } else {
        $sermon_audio = false;
    }
	
    // Scripture Citations
    $citations = get_field('scripture_citations', $post_id);
	if ( $citations ) {
		
        if ( is_singular('sermon') ) { $info .= '<p class="citations">'; } else { $info .= '<p>'; }
        
        $info .= "Scripture citation(s): ";
        
        foreach( $citations as $citation ){
            // TODO: add hyperlinks to Bible Verses (readings)
            $info .= get_the_title( $citation->ID )."; ";
        }
        
        if ( substr($info, -2) == "; " ) { // count($citations) > 0 && 
            // Trim trailing semicolon and space
            $info = substr($info, 0, -2);
        }
		$info .= '</p>';
        
	} else if ( get_field('scripture_citations_txt', $post_id) ) {
        $info .= "Scripture citation(s): ";
		$info .= the_field('scripture_citations_txt', $post_id)."<br />";
	} else if ( $sermon_audio == true ) {
        $info .= "<hr />";
    }
    
	$info .= '<!-- /cpt_sermon_meta -->';
	
	return $info;
}

add_shortcode( 'sermon_transcript_pdf', 'get_cpt_sermon_transcript' );
function get_cpt_sermon_transcript( $atts = [], $content = null, $tag = '' ) {
		
	$info = "";
	
	$a = shortcode_atts( array(
		'id'       => get_the_ID(),
    ), $atts );
	$post_id = $a['id'];
	
    $sermon_pdf = get_field('sermon_pdf', $post_id);
	if ($sermon_pdf) { 
        // 
        $info .= "<!-- sermon_pdf: ".print_r($sermon_pdf, true)." -->";
        // TODO: the following line happens twice -- if it happens one more time, make it a mini-function?
        $info .= make_link($sermon_pdf['url'], "Printable Transcript", null, "_blank"); // make_link( $url, $linktext, $class = null, $target = null)
	} else {
		//$info .= "No sermon transcript associated with this record (post_id: $post_id)"; // tft
	}
	
	return $info;
}

add_shortcode( 'sermon_event_link', 'get_cpt_sermon_event' );
function get_cpt_sermon_event ( $atts = [] ) {
	
    $info = ""; // init
    
    $a = shortcode_atts( array(
        'id'       	=> get_the_ID(),
        //'link'		=> true,
        //'link_text'	=> null,
    ), $atts );
    $post_id = $a['id'];
    //$link = $a['link'];
    //$link_text = $a['link_text'];
    
    // Show the link to the Service at which the sermon was delivered
    $related_event = get_field('related_event', $post_id);
    if ($related_event) {
        
        $related_events = true;
        $info .= make_link( get_the_permalink( $related_event ), get_the_title( $related_event ) );
        
    }
    
    return $info;
    
}

// Sermons Search
function find_matching_sermons( $year = null, $author = null, $bbook = null, $topic = null ) {
    
    // init
    $info = array();
    $msg = "";
    
    // Set up basic args to retrieve all sermons in descending order by date delivered
    $args = array(
        'post_type'     => 'sermon',
        'post_status'   => 'publish',
        'posts_per_page'=> 10,
        'meta_key'      => 'sermon_date',
        'orderby'       => 'meta_value',
        'order'			=> 'DESC',
    );
    
    if ( $year == null && $author == null && $bbook == null && $topic == null ) {
        // All filters are set to null >> run the query with no additional args
        $sermons = new WP_Query( $args );
        return $sermons;
    }
    
    $msg .= '<div class="troubleshooting">'."year: $year; author: $author; bbook: $bbook; topic: $topic".'</div>';
    
    // Prep meta_query array
    $meta_query_components = array();    
    
    // Year
    if ($year === 'any' ) { $year = null; }
    if ( isset( $year ) && is_numeric( $year ) ) {
        //$msg .= "year: $year<br />"; // tft
        $meta_query_components[] =  array(
            'key'   => 'sermon_date',
            'value' => array( $year."-01-01", $year."-12-31" ),
            'type'    => 'numeric',
            'compare'   => 'BETWEEN',
        );
    }
    
    // Sermon Author
    $author_id = null; // init
    
    if ( !empty($author) ) {
        
        if ( is_numeric($author) ) {
            $author_id = $author;
            //$msg .= "<!-- author ".$author." is_numeric -->";
        } else {
            //$msg .= "<!-- author $author NOT is_numeric -->";
            if ($author !== 'any' ) {
                // deal w/ misc. names or other words entered in query string
                // $author_id = get author id from $author -- i.e. search persons for clergy person with last name that matches $author
                
                $person_args = array(
                    'post_type'   => 'person',
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        array(
                            'key'     => 'last_name',
                            'value'   => $author
                        )
                    )
                );
                $arr_persons = new WP_Query( $person_args );
                //$persons = $arr_persons->posts;
                
                if ( $arr_persons->have_posts() ) {
                    // TODO: simplify -- shouldn't really need a loop here...
                    while ( $arr_persons->have_posts() ) {
                        $arr_persons->the_post();
                        $author_id = get_the_ID();
                    }
                }
            }
        }

        if ( is_numeric($author_id) || $author == 'other' ) {

            //$msg .= "<p>author_id: $author_id</p>";
            //$msg .= "<!-- author_id: $author_id -->";
            if ($author === 'other') {
                $exclusions = array(15012, 15001, 124072, 123941, 143207, 15022, 246039); 
                //'(15012, 15001, 124072, 123941, 143207, 15022, 147858)';
                $compare = 'NOT IN';
                
                $authors_meta = array( 'relation' => 'AND' );
                foreach ( $exclusions as $ex_id ) {
                    $sub_meta_array = array(
                        'key'   => 'sermon_author',
                        'value' => '"' . $ex_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                        'compare'=> 'NOT LIKE'
                    );
                    $authors_meta[] = $sub_meta_array;
                }
                $meta_query_components[] = $authors_meta;
                
            } else {
                $meta_query_components[] =  array(
                    'key'   => 'sermon_author',
                    'value' => $author_id,
                    'value' => '"' . $author_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                    'compare'=> 'LIKE'
                );
            }

        }
        
    }
    
    // Book of the Bible
    if ( isset( $bbook ) ) {
        
        //$msg .= "<p>bbook: $bbook</p>";
        //$msg .= "<!-- bbook: $bbook -->";
        //scripture_citations > relationship to Readings > reading.book > relationship to Bible book/ bible_book.post_title
        
        $meta_query_components[] =  array(
            'key'   => 'scripture_citations',
            'value' => '%'.$bbook.'%',
            'compare'   => 'LIKE',
        );
    
    }
    
    // Topic
    if ($topic !== null) { 
			
        $msg .= "topic: $topic<br />";
        $tax_query = array(
            array(
                'taxonomy' => 'sermon_topic',
                //'field'    => 'slug',
                'terms'    => $topic,
            ),
        );
        //$msg .= "tax_query: <pre>".print_r($tax_query, true)."</pre>";
        $args['tax_query'] = $tax_query;
        
    }
    
    // Finalize meta_query
    if ( count($meta_query_components) > 1 ) {
        $meta_query['relation'] = 'AND';
        foreach ( $meta_query_components AS $component ) {
            $meta_query[] = $component;
        }
    } else {
        $meta_query = $meta_query_components;
    }
    if ( !empty($meta_query) ) { $args['meta_query'] = $meta_query; }
    
    // Run the query
    //$sermons = new WP_Query( $args );
    $posts = new WP_Query( $args );
    //$arr_sermons = $result->posts;
    
    $msg .= '<div class="troubleshooting">args: <pre>'.print_r($args, true).'</pre></div>';
    //$msg .= '<div class="troubleshooting">posts: <pre>'.print_r($posts, true).'</pre></div>';
    
    $info['args'] = $args;
    $info['msg'] = $msg;
    $info['posts'] = $posts;
    
    return $info;
    
}


// Add shortcode for display of sermon filters form
add_shortcode('sermon_filters', 'build_sermon_filters');
// TODO: eventually: create general function for sdg_filterform ( $menus = array() ) for creation of filter forms for other content tyeps
function build_sermon_filters() {
	
	$info = '<form id="sermon_filters" class="category-select filter-form sermon_filters" action="'.esc_url( get_permalink() ).'" method="get">';
	
	// Years select menu
	$years = range( 2001, date('Y') );
	$info .= sdg_selectmenu ( array( 'label' => 'Year', 'arr_values' => $years, 'select_name' => 'sermon_date' ) );
	$info .= '<br />';
	
	// Preachers menu
	// Limit the list to a specific set of active clergy, per their person_ids, or "Other"
    // TODO: figure out a more elegant way to do this so that it's easier to make changes
    if ( !is_dev_site() ) {
        // Fr. Turner 15012, Fr. Moretz 15001, Mo. Turner 15022, Fr. Shultz 282498, Mo. Lee-Pae 284270, Fr. Brown 14984, Fr. Cheng 143207, Sr. Promise 246039 -- LIVE SITE
        $author_ids = array(15012, 15001, 15022, 282498, 284270, 14984, 143207, 246039); // Fr. Bennett: 123941
    } else {
        // Fr. Turner, Fr. Moretz, Fr. Brown, Fr. Cheng, Mo. Turner, Sr. Promise -- DEV SITE
        $author_ids = array(15012, 15001, 14984, 143207, 15022, 147858); // Fr. Bennett:
    }
    
    $args = array(
        'post_type'   => 'person',
        'post_status' => 'publish',
        'include'     => $author_ids,
        'orderby'   => 'post__in',
    );
    $sermon_authors = get_posts($args);
    //$info .= print_r($sermon_authors, true);
    
    // Given that the number of ids included is so limited, the select_distinct query isn't currently necessary. Use get_posts instead.
	//$author_values = sdg_select_distinct( array ( 'post_type' => 'person', 'meta_key' => 'sermon_author', 'arr_include' => $author_ids ) ); 
    
	$info .= sdg_selectmenu ( array( 'label' => 'Preacher', 'arr_values' => $sermon_authors, 'select_name' => 'sermon_author', 'show_other' => true ) ) ;
	$info .= '<br />';
	
	// Bible Books menu
	$bbook_args = array(
        'post_type' => 'bible_book',
        'post_status' => 'publish',
		'orderby'	=> 'meta_value_num',
		'order'      => 'ASC',
		'meta_key' 	=> 'sort_num',
		'posts_per_page' => -1
    );
	$bbook_results = new WP_Query( $bbook_args );
	//$info .= "Last SQL-Query: {$bbook_results->request}<br />"; // tft
	if ($bbook_results->have_posts()) {
		
		$bbook_values = array();
		$bbook_values["optgroup_label"] = "Old Testament";
		
		while ( $bbook_results->have_posts() ) : $bbook_results->the_post();
		
			$bbook_id = get_the_ID();
			$bbook_title = get_the_title();			
			
			// Add the item to the array of books
			$bbook_values[$bbook_id] = $bbook_title;
		
			// Insert the optgroup labels for old and new Testaments, where relevant
			if ($bbook_title == "Malachi") {
				$bbook_values["optgroup_label2"] = "The Apocrypha";
			} else if ($bbook_title == "2 Maccabees" || $bbook_title == "II Maccabees") {
				$bbook_values["optgroup_label3"] = "New Testament";
			}
		
		endwhile;
		
		//$info .= 'bbook_values: <pre>'.print_r($bbook_values, true).'</pre>'; // tft
		$info .= sdg_selectmenu ( array( 'label' => 'Text', 'arr_values' => $bbook_values, 'select_name' => 'bbook' ) ) ;
		$info .= '<br />';
	}
	
	// Sermon Topics menu
	$info .= sdg_selectmenu ( array( 'label' => 'Topic', 'orderby' => 'name', 'tax' => 'sermon_topic' ) ) ;
	$info .= '<br />';
	
	$info .= '<div class="centeralign padded">';
	$info .= '<input type="submit" name="submit" value="Filter" />';
	$info .= '<input type="submit" name="submit" value="Clear Filters" id="reset" />';
	$info .= '</div>';
	$info .= '</form>';
	
	return $info;
	
}

?>