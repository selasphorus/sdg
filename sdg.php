<?php
/**
 * Plugin Name:       SDG
 * Description:       A WordPress plugin for godly matters
 * Dependencies:      Requires WHx4
 * Requires Plugins:  whx4
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
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// Require Composer autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use atc\WHx4\Plugin;

// Add-on Modules
use atc\SDG\Modules\Lectionary\LectionaryModule as Lectionary; // To include Clergy, Sermons, ?....
use atc\SDG\Modules\Worship\WorshipModule as Worship;
// Other modules TBD
// Old version modules: lectionary, newsletters, sermons, snippets, webcasts, // ninjaforms, slider, ensembles
// TODO: move snippets, newletters to separate add-on mini-plugins? Move webcasts to WHx4 as part of Events

// Once plugins are loaded, boot everything up
add_action( 'whx4_pre_boot', function() {
    // Wait until WHx4 is loaded, but BEFORE it boots
    //error_log( '~~~ whx4_pre_boot ~~~' );
    if ( class_exists( Plugin::class ) ) {
        //error_log( '~~~ about to attempt to register additional modules ~~~' );

        // Register the module with WHx4
        add_filter( 'whx4_register_modules', function( array $modules ): array {
            error_log( '~~~ whx4_register_modules hook fired ~~~' );
            /*return array_merge( $modules, [
                //'paydocs'      => PayDocs::class,
                //'taxprep'      => TaxPrep::class,
                //'documents'  => Documents::class
            ]);*/
            $modules['lectionary'] = Lectionary::class;
            $modules['worship'] = Worship::class;
            return $modules;
        } );

        add_filter( 'whx4_registered_field_keys', function() {
            error_log( '~~~ whx4_registered_field_keys hook fired ~~~' );
            if ( ! function_exists( 'acf_get_local_fields' ) ) {
                return [];
            }

            $fields = acf_get_local_fields();
            $keys = [];

            foreach ( $fields as $field ) {
                if ( isset( $field['key'] ) ) {
                    $keys[] = $field['key'];
                }
            }

            return $keys;
        });
    } else {
       error_log( '~~~ Plugin class DNE ~~~' );
    }
}, 15 ); // Priority < 20 to run before WHx4 boot()

