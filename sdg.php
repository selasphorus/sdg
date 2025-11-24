<?php
/**
 * Plugin Name:       SDG
 * Description:       A WordPress plugin for godly matters
 * Dependencies:      Requires WHx4-Core, WHx4
 * Requires Plugins:  whx4-core, whx4
 * Version:           2.0
 * Author:            atc
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sdg
 *
 * @package           sdg
 */

declare(strict_types=1);

namespace atc\SDG;

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

// v1 designed using ACF PRO Blocks, Post Types, Options Pages, Taxonomies and more.
// v2 OOP version WIP

// Require Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use WXC\Plugin;

// WXC Add-on Modules
use SDG\Modules\Worship\WorshipModule as Worship; // To include Clergy, Sermons, ?....
//use SDG\Modules\Lectionary\LectionaryModule as Lectionary; // WIP
// Other modules TBD
// Old version modules: lectionary, newsletters, sermons, snippets, webcasts, // ninjaforms, slider, ensembles
// TODO: move snippets, newletters to separate add-on mini-plugins? Move webcasts to WHx4 as part of Events

// Once plugins are loaded, boot everything up
add_action('wxc_pre_boot', function() {
    // Wait until WXC is loaded, but BEFORE it boots
    if (!class_exists(Plugin::class)) {
        return;
    }

    // Register the modules with WXC
    add_filter('wxc_register_modules', function(array $modules): array {
        $modules['worship'] = Worship::class;
        //$modules['lectionary'] = Lectionary::class;
		return $modules;
	});
	
	// Register Field Keys
    add_filter('wxc_registered_field_keys', function() {
        if (!function_exists('acf_get_local_fields')) {
            return [];
        }

        $fields = acf_get_local_fields();
        $keys = [];

        foreach ($fields as $field) {
            if (isset($field['key'])) {
                $keys[] = $field['key'];
            }
        }

        return $keys;
    });
    
    // Register Assets
    add_filter('wxc_assets', static function (array $assets): array {
        // CSS
        $relCss = 'assets/css/sdg.css';
        $srcCss = plugins_url($relCss, __FILE__);
        $pathCss = plugin_dir_path(__FILE__) . $relCss;
    
        $assets['styles'][] = [
            'handle'   => 'sdg',
            'src'      => $srcCss,
            'path'     => $pathCss,
            'deps'     => [],
            'ver'      => 'auto',
            'media'    => 'all',
            'where'    => 'front',
            'autoload' => true,
        ];
    
        // JS
        /*$relJs = 'assets/js/sdg.js';
        $srcJs = plugins_url($relJs, __FILE__);
        $pathJs = plugin_dir_path(__FILE__) . $relJs;
    
        $assets['scripts'][] = [
            'handle'    => 'sdg',
            'src'       => $srcJs,
            'path'      => $pathJs,
            'deps'      => [],        // e.g., ['jquery']
            'ver'       => 'auto',
            'in_footer' => true,
            'where'     => 'front',
            'autoload'  => true,
        ];*/
    
        return $assets;
    });
    
}, 15); // Priority < 20 to run before WXC boot()

