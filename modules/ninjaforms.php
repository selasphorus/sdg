<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin fiel, not much I can do when called directly.';
	exit;
}

/*** NINJA FORMS ***/
    
// The following two template functions are a very, very simplified version of the WooCommerce core functionality that builds emails from templates -- see e.g. 'wc_get_template' (wc-core-functions.php)
/**
 * Get other templates, including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function sdg_get_template( $template_name, $args = array(), $template_path = '') { // function wc_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' )
    
    global $plugin_path;
    $template_path = $plugin_path.$template_path.$template_name;
    //$plugins_url = plugins_url();
    //$template_path = $plugins_url.'/stc/templates/ninjaforms/';
    
    if (file_exists( $template_path ) ) {
        
        if ( ! empty( $args ) && is_array( $args ) ) {
            extract( $args ); // @codingStandardsIgnoreLine
        }
        
        include $template_path;
        
    } else {
        stc_log("function: sdg_get_template");
        stc_log("could not locate template file: ".$template_path);
        echo "[could not locate template file: $template_path]";
    }
    
}

/**
 * Like sdg_get_template, but returns the HTML instead of outputting.
 *
 * @see sdg_get_template
 * @since 2.5.0
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 *
 * @return string
 */
function sdg_get_template_html( $template_name, $args, $template_path = '' ) {
	ob_start();
	sdg_get_template( $template_name, $args, $template_path ); //wc_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}

add_filter('ninja_forms_action_email_message', 'custom_nf_email_message', 10, 3);
function custom_nf_email_message($message, $data, $action_settings) {

    // init vars
    $info = "";
    $args = array();
    
    $form_title = $data['settings']['title'];
    if ( empty($form_title) ) {
        $form_title = $data['settings']['form_title'];
        if ( empty($form_title) ) { $form_title = "Website Form"; }
    }
    $email_heading = $form_title." Submission";
    $args['email_heading'] = $email_heading;
    
    // Set template paths
    $template_path = 'templates/ninjaforms/';
    $header_template_name = 'emails/email-header.php';
    $css_template_name = 'emails/email-styles.php';
    $footer_template_name = 'emails/email-footer.php';
    
    // Add header from template
    $info .= sdg_get_template_html( $header_template_name, $args, $template_path );
    
    // Add styles from template
    $css = sdg_get_template_html( $css_template_name, $args, $template_path );
    $info .= '<style>' . $css . '</style>';
    
    // Get form title from submitted data; add it as a header
    // TODO: figure out why the form_title var is sometimes empty and come up with a good default, if/when this header is re-enabled
    //$info .= '<h2>'.$data['settings']['form_title'].' Submission</h2>';
    
    // Add the message content
    $info .= $message;
    
    //if (is_dev_site()) { $info .= 'data: <pre>'.print_r($data,true).'</pre>'; } // tft
    //$info .= 'action_settings: <pre>'.print_r($action_settings,true).'</pre>'; // tft

    // Convert the submitted form data to an associative array
    /*$form_data = array();
    foreach ($data['fields'] as $key => $field) {
        $form_data[$field['key']] = $field['value'];
    }*/
    
    // Add footer from template
    $info .= sdg_get_template_html( $footer_template_name, $args, $template_path );
    
    // Return the modified HTML email body
    return $info;
    
}


/*
 * Custom capabilities for Ninja Forms admin
 */
 
// Ninja Forms
 add_filter('ninja_forms_menu_ninja-forms_capability', 'ninja_forms_menu_get_cap', 10, 1);
 function ninja_forms_menu_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_admin_menu';
     return $cap;
 }
 
// Ninja Forms -> Dashboard 
 add_filter('ninja_forms_admin_all_forms_capabilities', 'ninja_forms_dashboard_get_cap', 10, 1);
 function ninja_forms_dashboard_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_admin_menu';
     return $cap;
 }
 
// Ninja Forms -> Add New 
 add_filter('ninja_forms_admin_add_new_capabilities', 'ninja_forms_add_new_get_cap', 10, 1);
 function ninja_forms_add_new_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_add_new';
     return $cap;
 }
 
// Ninja Forms -> Submissions 
 add_filter('ninja_forms_admin_submissions_capabilities', 'ninja_forms_submissions_get_cap', 10, 1);
 function ninja_forms_submissions_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_submissions';
     return $cap;
 }
 
// Ninja Forms -> Settings
 add_filter('ninja_forms_admin_settings_capabilities', 'ninja_forms_settings_get_cap', 10, 1);
 function ninja_forms_settings_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_settings';
     return $cap;
 }
 
// Ninja Forms -> Get Help 
 add_filter('ninja_forms_admin_status_capabilities', 'ninja_forms_status_get_cap', 10, 1);
 function ninja_forms_status_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_status';
     return $cap;
 }
 
// Ninja Forms -> Add-Ons 
 add_filter('ninja_forms_admin_extend_capabilities', 'ninja_forms_extend_get_cap', 10, 1);
 function ninja_forms_extend_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_addons';
     return $cap;
 }

// Ninja Forms -> Import/Export
//'ninja_forms_admin_import_export_capabilities'
add_filter('ninja_forms_admin_import_export_capabilities', 'ninja_forms_impexp_get_cap', 10, 1);
 function ninja_forms_impexp_get_cap( $cap ) {
     $cap = 'nf_admin';
     //$cap = 'nf_impexp';
     return $cap;
 }


?>