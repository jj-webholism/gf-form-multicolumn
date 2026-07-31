<?php
/*
 * Plugin Name: Multiple Columns for Gravity Forms
 * Plugin URI: https://wordpress.org/plugins/gf-form-multicolumn/
 * Description: Introduces new form elements into Gravity Forms which allow rows to be split into multiple columns.
 * Author: WebHolism
 * Author URI: http://www.webholism.com
 * Version: 4.1.0-beta3
 * Text Domain: gf-form-multicolumn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'GFMC_DEBUG' ) ) {
	define( 'GFMC_DEBUG', false );
}

if ( ! defined( 'GFMC_LOG_LEVEL' ) ) {
	define( 'GFMC_LOG_LEVEL', 'error' );
}

// require __DIR__ . '/vendor/autoload.php';

// Load Composer autoloader
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

use WH\GF\Multicolumn;
use WH\GF\Multicolumn\WH_GF_Multicolumn_Activator;
use WH\GF\Multicolumn\WH_GF_Multicolumn_Uninstaller;

add_action(
	'gform_loaded',
	array(
		'WH_GF_Multicolumn_Bootstrap',
		'load',
	),
	5
);

/**
 * Class GFMC_Bootstrap
 */
class WH_GF_Multicolumn_Bootstrap {
	/**
	 * Whether the plugin has already been loaded.
	 *
	 * @var bool
	 */
	private static $loaded = false;

	/**
	 * Load the plugin.
	 *
	 * @return void
	 */
	public static function load() {
		if ( self::$loaded ) {
			return;
		}

		self::$loaded = true;

		if ( ! method_exists( '\GFForms', 'include_addon_framework' ) ) {
			return;
		} else {
			GFAddOn::register( 'WH\GF\Multicolumn\WH_GF_Multicolumn' );
			WH\GF\Multicolumn\WH_GF_Multicolumn::get_instance();
		}
	}
}

/**
 * Activation is responsible for adding form options
 * gfmc_enable_css and gfmc_enable_js.
 * Uninstall will remove those settings and delete all multicolumn form
 * element entries.
 */
register_activation_hook( __FILE__, 'gfmc_plugin_activate' );
register_uninstall_hook( __FILE__, 'gfmc_plugin_uninstall' );


function gfmc_plugin_activate() {
	// Manually require the file.
	if ( ! class_exists( 'WH\GF\Multicolumn\WH_GF_Multicolumn_Activator' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/WH_GF_Multicolumn_Activator.php';
	}

	$activator = new WH_GF_Multicolumn_Activator();
	$activator->gfmc_activate();
}

function gfmc_plugin_uninstall() {
	// Manually require the file.
	if ( ! class_exists( 'WH\GF\Multicolumn\WH_GF_Multicolumn_Uninstaller' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/WH_GF_Multicolumn_Uninstaller.php';
	}

	$uninstaller = new WH_GF_Multicolumn_Uninstaller();
	$uninstaller->gfmc_uninstall();
}

/*
function gfmc_plugin_activate() {
	$activator = new WH_GF_Multicolumn_Activator();
	$activator->gfmc_activate();
}

function gfmc_plugin_uninstall() {
	// The Gravity Forms AddOns automatically deletes: Form settings, Plugin
	// settings, Entry meta & Version information
	$uninstaller = new WH_GF_Multicolumn_Uninstaller();
	$uninstaller->gfmc_uninstall();
}*/
