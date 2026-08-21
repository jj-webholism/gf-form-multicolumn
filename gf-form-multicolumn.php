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

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
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
