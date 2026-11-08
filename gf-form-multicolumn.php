<?php
/**
 * Plugin Name:       Gravity Forms Multiple Columns
 * Plugin URI:        https://webholism.com/
 * Description:       Adds multi-column capabilities to Gravity Forms.
 * Version:           4.0.9
 * Author:            Webholism
 * Author URI:        https://webholism.com/
 * Text Domain:       gf-form-multicolumn
 * Domain Path:       /languages
 *
 * @package WH\GF\Multicolumn
 */

namespace WH\GF\Multicolumn;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load Composer autoloader in development, or register native SPL fallback in production.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	spl_autoload_register(
		function ( $class_name ) {
			$prefix = 'WH\\GF\\Multicolumn\\';
			$len    = strlen( $prefix );

			if ( 0 !== strncmp( $prefix, $class_name, $len ) ) {
				return;
			}

			$relative_class = substr( $class_name, $len );
			$parts          = explode( '\\', $relative_class );
			$short_class    = array_pop( $parts );
			$slug           = strtolower( str_replace( '_', '-', $short_class ) );

			$subpath = empty( $parts ) ? '' : implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR;
			$base    = __DIR__ . '/includes/' . $subpath;

			$class_file = $base . 'class-' . $slug . '.php';
			if ( file_exists( $class_file ) ) {
				require_once $class_file;
				return;
			}

			$interface_file = $base . 'interface-' . $slug . '.php';
			if ( file_exists( $interface_file ) ) {
				require_once $interface_file;
				return;
			}

			$trait_file = $base . 'trait-' . $slug . '.php';
			if ( file_exists( $trait_file ) ) {
				require_once $trait_file;
			}
		}
	);
}

add_action( 'gform_loaded', array( 'WH\GF\Multicolumn\GFMC_Loader', 'load' ), 5 );

/**
 * Main loader class for initializing the Gravity Forms AddOn.
 */
class GFMC_Loader {

	/**
	 * Instantiates and registers the main plugin controller.
	 *
	 * @return void
	 */
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		\GFForms::include_addon_framework();
		Plugin::get_instance();
	}
}

/**
 * Plugin activation hook callback.
 *
 * @return void
 */
function gfmc_plugin_activate() {
	Activator::activate();
}

/**
 * Plugin uninstallation hook callback.
 *
 * @return void
 */
function gfmc_plugin_uninstall() {
	// Execute uninstaller routines.
	Uninstaller::uninstall();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\gfmc_plugin_activate' );
