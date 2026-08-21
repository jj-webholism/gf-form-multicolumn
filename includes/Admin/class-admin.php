<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package WH\GF\Multicolumn\Admin
 */

namespace WH\GF\Multicolumn\Admin;

use WH\GF\Multicolumn\Admin\Field\Column_End;
use WH\GF\Multicolumn\Admin\Field\Column_Start;
use WH\GF\Multicolumn\Admin\Field\Column_Separator;
use WH\GF\Multicolumn\Admin\Field\Group;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin page manipulation.
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->add_fields();
	}

	/**
	 * Add custom Gravity Forms fields.
	 *
	 * @return void
	 */
	private function add_fields() {
		if ( class_exists( '\GF_Fields' ) ) {
			new Group();
			new Column_Start();
			new Column_End();
			new Column_Separator();
		}
	}
}
