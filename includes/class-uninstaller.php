<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WH\GF\Multicolumn
 */

namespace WH\GF\Multicolumn;

/**
 * Handles plugin uninstallation and cleanup.
 */
class Uninstaller {

	/**
	 * Runs uninstallation cleanup routines.
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			exit;
		}

		// Delete options or clean up custom tables here.
	}
}
