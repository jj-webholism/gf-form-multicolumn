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
use function get_current_form;

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

	private $_no_conflict_scripts = array ();
	private $_no_conflict_styles = array ();
	private $_preview_styles = array ();

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

		add_action( 'plugins_loaded', array ( $this, 'set_locale' ) );

		add_filter( 'gform_preview_styles',
		            array ( $this, 'enqueue_admin_preview_scripts' ), 10, 2 );
	}

	public function init() {
		parent::init();
	}

	public function init_frontend() {
		parent::init_frontend();

		add_action( 'gform_enqueue_scripts',
		            array ( $this, 'enqueue_public_scripts' ) );

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

	public function scripts() {
		$scripts = array (
			array (
				'handle'  => 'gfmc-scripts-public',
				'src'     => plugins_url( '/public/js/gf-form-multicolumn.js',
				                          __FILE__ ),
				'version' => $this->get_version(),
				'deps'    => array ( 'jquery' ),
				'enqueue' => array (
					array (
						'field_types' => array (
							'column_start',
							'column_break',
							'column_end',
						),
					),
				),
			),
			/*array (
				'handle'  => 'gfmc-scripts-admin',
				'src'     => plugins_url( '/admin/js/gf-form-multicolumn-admin.min.js',
				                          __FILE__ ),
				'version' => $this->get_version(),
				'enqueue' => array (
					array (
						'admin_page' => array ( 'form_settings' ),
					),
				),
			),*/

		);

		return array_merge( parent::scripts(), $scripts );
	}

	public function styles() {
		$styles = array (
			array (
				'handle'  => 'gfmc-styles',
				'src'     => plugins_url( '/public/css/gf-form-multicolumn.css',
				                          __FILE__ ),
				'version' => $this->get_version(),
				'enqueue' => array (
					array (
						'field_types' => array (
							'column_start',
							'column_break',
							'column_end',
						),
					),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	public function enqueue_gfmc_scripts( $form ) {
		echo( 'enqueue_gfmc_scripts' );
		$cssFileLocation = plugins_url( '/public/css/gf-form-multicolumn.css',
		                                __FILE__ );
		$jsFileLocation  = plugins_url( '/public/js/gf-form-multicolumn.js',
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
		echo( '$this->is_css_to_be_loaded( $form ): '
		      . $this->is_css_to_be_loaded( $form ) );
		echo( '$this->is_gf_form_multicolumn_element_on_page( $form ): '
		      . $this->is_gf_form_multicolumn_element_on_page( $form ) );
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

	public function enqueue_public_scripts( $form = '', $is_ajax = false ) {
		if ( ! ( $this->is_javascript_to_be_loaded( $form ) &&
		         ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) ) {
			wp_dequeue_script( 'gfmc-scripts-public' );
		}
		wp_dequeue_script( 'gfmc-scripts-admin' );
		if ( ! ( $this->is_css_to_be_loaded( $form ) &&
		         ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) ) {
			wp_dequeue_style( 'gfmc-styles' );
		}
	}

	public function enqueue_admin_scripts( $form = '', $is_ajax = false ) {
		wp_dequeue_script( 'gfmc-scripts-public' );
		wp_dequeue_style( 'gfmc-styles' );
	}

//	public function enqueue_scripts( $form = '', $is_ajax = false ) {
//		var_dump('enqueue_scripts');
//
//		/*if ( empty( $form ) ) {
//			$form = get_current_form();
//		}*/
//
//		$jsFileLocation = plugins_url( '/public/js/gf-form-multicolumn.js',
//		                               __FILE__ );
//
//		$scripts = $this->scripts();
//		foreach ( $scripts as $script ) {
//			$src     = isset( $script['src'] ) ? $script['src'] : false;
//			$deps    = isset( $script['deps'] ) ? $script['deps'] : array ();
//			$version = array_key_exists( 'version',
//			                             $script ) ? $script['version'] : false;
//			wp_register_script( $script['handle'], $src, $deps, $version );
//
//			echo ('<br>registered '.$script['handle'].', location: '.$src);
//
//			if ( $this->is_javascript_to_be_loaded( $form ) &&
//			     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
//				wp_enqueue_script(
//					'gfmc-scripts',
//					$jsFileLocation,
//					100,
//					$this->get_version(),
//					'all'
//				);
//			}
//
//			/*if ( isset( $script['enqueue'] ) && $this->_can_enqueue_script( $script['enqueue'], $form, $is_ajax ) ) {
//				$this->add_no_conflict_scripts( array( $script['handle'] ) );
//				wp_enqueue_script( $script['handle'] );
//				if ( isset( $script['strings'] ) ) {
//					wp_localize_script( $script['handle'], $script['handle'] . '_strings', $script['strings'] );
//				}
//				if ( isset( $script['callback'] ) && is_callable( $script['callback'] ) ) {
//					$args = compact( 'form', 'is_ajax' );
//					call_user_func_array( $script['callback'], $args );
//				}
//			}*/
//		}
//		$cssFileLocation = plugins_url( '/public/css/gf-form-multicolumn.css',
//		                                __FILE__ );
//		$styles          = $this->styles();
//		foreach ( $styles as $style ) {
//			$src     = isset( $style['src'] ) ? $style['src'] : false;
//			$deps    = isset( $style['deps'] ) ? $style['deps'] : array ();
//			$version = array_key_exists( 'version',
//			                             $style ) ? $style['version'] : false;
//			wp_register_style( $style['handle'], $src, $deps, $version );
//
//			echo ('<br>registered '.$style['handle'].', location: '.$src);
//
//			if ( $this->is_css_to_be_loaded( $form ) &&
//			     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
//				wp_enqueue_style(
//					'gfmc-styles',
//					$cssFileLocation,
//					false,
//					$this->get_version(),
//					'all'
//				);
//			}
//		}
//
//	}

	private function is_javascript_to_be_loaded( $form ) {
		return isset( $form['gfmc_enable_js'] ) && $form['gfmc_enable_js'] ?
			1 : 0;
	}

	private function is_css_to_be_loaded( $form ) {
		return isset( $form['gfmc_enable_css'] ) && $form['gfmc_enable_css']
			? 1 : 0;
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
			add_action( 'gform_enqueue_scripts',
			            array ( $this, 'enqueue_admin_scripts' ) );

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
		                                __FILE__ ), array (), $this->_version );

		return array ( 'preview_stylesheet' );

	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->_version;
	}
}
