<?php

/**
 * The core Multi Columns class that overrides GFAddOn.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.0
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes
 */

namespace WH\GF\Multicolumn\Classes;

use GFAddOn;
use WH\GF\Multicolumn\Admin\WH_GF_Multicolumn_Admin;
use WH\GF\Multicolumn\Site\WH_GF_Multicolumn_Public;

\GFForms::include_addon_framework();

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
		return array (
			'php' => array (
				'version' => '5.6',
			),
		);
	}

	public function pre_init() {
		parent::pre_init();

		add_action( 'plugins_loaded', array( $this, 'set_locale' ) );

		add_filter( 'gform_preview_styles',
		            array( $this, 'enqueue_admin_preview_scripts' ), 10, 2 );

		add_action( 'gform_enqueue_scripts',
		            array( $this, 'enqueue_gfmc_scripts' ) );
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

		$this->public = new WH_GF_Multicolumn_Public(
			$this->get_plugin_name(), $this->get_version() );

		$this->public->display();
	}

	public function enqueue_gfmc_scripts( $form ) {
		$cssFileLocation = plugins_url( '/public/css/gf-form-multicolumn.css',
		                                __FILE__ );
		$jsFileLocation = plugins_url( '/public/js/gf-form-multicolumn.js',
		                               __FILE__ );

		if ( $this->is_javascript_to_be_loaded( $form ) &&
		     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
			wp_enqueue_script(
				'gfmc-scripts',
				$jsFileLocation,
				100,
				$this->get_version(),
				'all'
			);
		}
		if ( $this->is_css_to_be_loaded( $form ) &&
		     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
			wp_enqueue_style(
				'gfmc-styles',
				$cssFileLocation,
				false,
				$this->get_version(),
				'all'
			);
		}
	}

	private function is_javascript_to_be_loaded( $form ) {
		return isset( $form['gfmc_enable_js'] ) && $form['gfmc_enable_js'];
	}

	private function is_css_to_be_loaded( $form ) {
		return isset( $form['gfmc_enable_css'] ) && $form['gfmc_enable_css'];
	}

	private function is_gf_form_multicolumn_element_on_page( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] === 'column_start' || $field['type'] === 'column_end' ) {
				return true;
			}
		}

		return false;
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
		                                __FILE__ ), array(), $this->_version );

		return array( 'preview_stylesheet' );

	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->_version;
	}
}
