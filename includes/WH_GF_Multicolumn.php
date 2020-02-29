<?php

namespace WH\GF\Multicolumn\Classes;

use GFAddOn;
use WH\GF\Multicolumn\Admin\WH_GF_Multicolumn_Admin;
use WH\GF\Multicolumn\Site\WH_GF_Multicolumn_Public;

\GFForms::include_addon_framework();

/**
 * This defines relates to the administrator group - Move to ADMIN
 */
class WH_GF_Multicolumn extends
	GFAddOn {
	protected $plugin_name = 'gf-form-multicolumn';

	// Gravity Forms Class Variables
	protected $_version = '3.1.0';
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gfmc';
	protected $_path = 'gf-form-multicolumn/gf-form-multicolumn.php';
	protected $_full_path = __FILE__;

	private $admin;
	private $public;

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function minimum_requirements() {
		return [
			'php' => [
				'version' => '5.6',
			],
		];
	}

	public function pre_init() {
		parent::pre_init();

		add_action( 'plugins_loaded', [ $this, 'set_locale' ] );

		add_filter( 'gform_preview_styles',
		            [ $this, 'enqueue_admin_preview_scripts' ], 10, 2 );
	}

	public function init() {
		parent::init();
	}

	public function init_frontend() {
		parent::init_frontend();

		$this->public = new WH_GF_Multicolumn_Public(
			$this->get_plugin_name(), $this->get_version() );

		$this->public->display();
	}

	public function init_ajax() {
		parent::init_ajax();
		// add tasks or filters here that you want to perform only during ajax requests
	}

	public function init_admin() {
		parent::init_admin();

		// Used to apply backend only functionality such as adding additional
		// features to the Gravity Forms GUI.
		if ( is_admin() ) {
			$this->admin = new WH_GF_Multicolumn_Admin(
				$this->get_plugin_name(), $this->_version
			);
		}
	}

	public function set_locale() {
		$plugin_i18n = new WH_GF_Multicolumn_i18n();
	}

	public function enqueue_admin_preview_scripts( $styles, $form ) {
		wp_register_style( 'preview_stylesheet',
		                   plugins_url( '/public/css/gf-form-multicolumn.css',
		                                __FILE__ ), [], $this->_version );

		return [ 'preview_stylesheet' ];

	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->_version;
	}
}
