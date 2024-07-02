<?php
/**
 * SDG Options Page: "Site Settings"
 *
 * @link https://www.advancedcustomfields.com/resources/options-page/
 */

/**
 * Check if ACF PRO is active and function exists
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	//add_action( 'acf/init', 'sdg_register_options_page' );
}

function sdg_register_options_page() {

	// Add the top-level page
	acf_add_options_page(
		array(
			'page_title' => 'SDG Settings',
			'menu_slug'  => 'sdg_settings',
			'redirect'   => false,
		)
	);

	// Add the sub-page
	/*
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Contact Information',
			'menu_slug'   => 'contact-information',
			'parent_slug' => 'site-settings',
		)
	);
	*/

	// Add 'Modules & Settings' field group
	acf_add_local_field_group(
		array(
			'key'      => 'group_sdg_modules',
			//'key'      => 'group_6511a57f5680c',
			'title'    => 'Modules &amp; Settings',
			'fields'   => array(
				array(
					'key'           => 'field_sdg_modules',
					//'key'           => 'field_6511a57fcbe7e',
					'label'         => 'Active Modules',
					'name'          => 'sdg_active_modules',
					'type'          => 'checkbox',
					'instructions' => 'Select the modules to activate.',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '25',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'people' => 'People',
						//'groups' => 'Groups',
						'places' => 'Places',
						//'locations' => 'Locations',
						//'buildings' => 'Buildings',
						'events' => 'Events',
					),
					'default_value' => array(
					),
					'return_format' => 'value',
					'allow_custom' => 0,
					'layout' => 'vertical',
					'toggle' => 0,
					'save_custom' => 0,
					'custom_choice_button_text' => 'Add new choice',
					'aria-label' => '',
					'relevanssi_exclude' => 0,
				),
				array(
					'key'           => 'field_sdg_use_custom_caps',
					'label'         => 'Use custom capabilities?',
					'name'          => 'sdg_use_custom_caps',
					'type'          => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '15',
						'class' => '',
						'id' => '',
					),
					'default_value' => array(
					),
					'return_format' => 'value',
					'layout' => 'horizontal',
					'aria-label' => '',
					'relevanssi_exclude' => 0,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'sdg_settings',
					),
				),
			),
		)
	);

	/* Group from ACF demo plugin -- left here temporarily as model...
	acf_add_local_field_group(
		array(
			'key'      => 'group_sdg_notification_bar',
			'title'    => 'Notification Bar',
			'fields'   => array(
				array(
					'key'        => 'field_sdg_notification_bar_group',
					'label'      => 'Notification Bar',
					'name'       => 'sdg_notification_bar_group',
					'aria-label' => '',
					'type'       => 'group',
					'layout'     => 'row',
					'sub_fields' => array(
						array(
							'key'           => 'field_sdg_notification_onoff',
							'label'         => 'Notification On/Off',
							'name'          => 'sdg_notification_onoff',
							'type'          => 'true_false',
							'message'       => 'Should the site-wide Notification Bar be showing?',
							'default_value' => 1,
							'ui_on_text'    => 'On',
							'ui_off_text'   => 'Off',
							'ui'            => 1,
						),
						array(
							'key'               => 'field_sdg_notification_message',
							'label'             => 'Notification Message',
							'name'              => 'sdg_notification_message',
							'type'              => 'textarea',
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'sdg_notification_onoff',
										'operator' => '==',
										'value'    => '1',
									),
								),
							),
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'site-settings',
					),
				),
			),
		)
	);
	*/
	
}
