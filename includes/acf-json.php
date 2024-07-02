<?php
/**
 * ACF Set custom load and save JSON points.
 *
 * @link https://www.advancedcustomfields.com/resources/local-json/
 */

add_filter( 'acf/json/load_paths', 'sdg_json_load_paths' );
add_filter( 'acf/settings/save_json/type=acf-field-group', 'sdg_json_save_path_for_field_groups' );
add_filter( 'acf/settings/save_json/type=acf-ui-options-page', 'sdg_json_save_path_for_option_pages' );
add_filter( 'acf/settings/save_json/type=acf-post-type', 'sdg_json_save_path_for_post_types' );
add_filter( 'acf/settings/save_json/type=acf-taxonomy', 'sdg_json_save_path_for_taxonomies' );
add_filter( 'acf/json/save_file_name', 'sdg_json_filename', 10, 3 );

/**
 * Set a custom ACF JSON load path.
 *
 * @link https://www.advancedcustomfields.com/resources/local-json/#loading-explained
 *
 * @param array $paths Existing, incoming paths.
 *
 * @return array $paths New, outgoing paths.
 *
 * @since 0.1.1
 */
function sdg_json_load_paths( $paths ) {
	$paths[] = SDG_PLUGIN_DIR . '/acf-json/field-groups';
	$paths[] = SDG_PLUGIN_DIR . '/acf-json/options-pages';
	$paths[] = SDG_PLUGIN_DIR . '/acf-json/post-types';
	$paths[] = SDG_PLUGIN_DIR . '/acf-json/taxonomies';

	return $paths;
}

/**
 * Set custom ACF JSON save point for
 * ACF generated post types.
 *
 * @link https://www.advancedcustomfields.com/resources/local-json/#saving-explained
 *
 * @return string $path New, outgoing path.
 *
 * @since 0.1.1
 */
function sdg_json_save_path_for_post_types() {
	return SDG_PLUGIN_DIR . '/acf-json/post-types';
}

/**
 * Set custom ACF JSON save point for
 * ACF generated field groups.
 *
 * @link https://www.advancedcustomfields.com/resources/local-json/#saving-explained
 *
 * @return string $path New, outgoing path.
 *
 * @since 0.1.1
 */
function sdg_json_save_path_for_field_groups() {
	return SDG_PLUGIN_DIR . '/acf-json/field-groups';
}

/**
 * Set custom ACF JSON save point for
 * ACF generated taxonomies.
 *
 * @link https://www.advancedcustomfields.com/resources/local-json/#saving-explained
 *
 * @return string $path New, outgoing path.
 *
 * @since 0.1.1
 */
function sdg_json_save_path_for_taxonomies() {
	return SDG_PLUGIN_DIR . '/acf-json/taxonomies';
}

/**
 * Set custom ACF JSON save point for
 * ACF generated Options Pages.
 *
 * @link https://www.advancedcustomfields.com/resources/local-json/#saving-explained
 *
 * @return string $path New, outgoing path.
 *
 * @since 0.1.1
 */
function sdg_json_save_path_for_option_pages() {
	return SDG_PLUGIN_DIR . '/acf-json/options-pages';
}

/**
 * Customize the file names for each file.
 *
 * @link https://www.advancedcustomfields.com/resources/local-json/#saving-explained
 *
 * @param string $filename  The default filename.
 * @param array  $post      The main post array for the item being saved.
 *
 * @return string $filename
 *
 * @since  0.1.1
 */
function sdg_json_filename( $filename, $post ) {
	$filename = str_replace(
		array(
			' ',
			'_',
		),
		array(
			'-',
			'-',
		),
		$post['title']
	);

	$filename = strtolower( $filename ) . '.json';

	return $filename;
}
