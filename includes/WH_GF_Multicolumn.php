<?php

/**
 * The core Multi Columns class that overrides GFAddOn.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.1
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes
 */

namespace WH\GF\Multicolumn;

use GFAddOn;
use GFForms;
use WH\GF\Multicolumn\admin\WH_GF_Multicolumn_Admin;
use WH\GF\Multicolumn\public\WH_GF_Multicolumn_Public;

GFForms::include_addon_framework();

class WH_GF_Multicolumn extends GFAddOn {
	protected $plugin_name = 'gf-form-multicolumn';

	// Gravity Forms Class Variables
	protected $_version                  = '4.1.0-beta3';
	protected $_min_gravityforms_version = '1.9';
	protected $_slug                     = 'gfmc';
	protected $_path                     = 'gf-form-multicolumn/gf-form-multicolumn.php';
	protected $_full_path                = __FILE__;

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function minimum_requirements() {
		return array(
			'php' => array(
				'version' => '8.0',
			),
		);
	}

	public function pre_init() {
		parent::pre_init();

		add_action( 'plugins_loaded', array( $this, 'set_locale' ) );
		// add_action( 'gform_preview_init', array( $this, 'register_preview_styles_filter' ) );
	}

	// public function register_preview_styles_filter() {
	//
	// add_filter(
	// 'gform_preview_styles',
	// array( $this, 'enqueue_admin_preview_scripts' ),
	// 10,
	// 2
	// );
	// }

    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
	public function init() {
		parent::init();

		// add_filter( 'gform_preview_styles',
		// array ( $this, 'enqueue_admin_preview_scripts' ), 10,
		// 2 );
	}

	public function init_admin() {
		// Used to apply backend only functionality such as adding additional
		// features to the Gravity Forms GUI.
		if ( is_admin() ) {
			new WH_GF_Multicolumn_Admin();

			parent::init_admin();
		}
	}

	public function init_frontend() {
		parent::init_frontend();

		$public = new WH_GF_Multicolumn_Public();

		add_action(
			'gform_enqueue_scripts',
			array( $public, 'dequeue_selected_scripts' ),
			11
		);

		$public->display();
	}

	public function init_ajax() {
		parent::init_ajax();

		$ajaxForm = new WH_GF_Multicolumn_Public();

		$ajaxForm->display();
	}

	public function scripts() {
		$scripts[] =
			array(
				'handle'    => 'gfmc_scripts_public',
				'src'       => plugins_url(
					'/public/js/gf-form-multicolumn.min.js',
					__FILE__
				),
				'version'   => $this->get_version(),
				'deps'      => array( 'jquery', 'gform_conditional_logic' ),
				'in_footer' => true,
				'callback'  => array( $this, 'dequeue_if_in_admin_area' ),
				'enqueue'   => array(
					array(
						'field_types' => array(
							'column_start',
							'column_break',
							'column_end',
						),
					),
				),
			);

		$scripts[] =
			array(
				'handle'  => 'gfmc_scripts_admin',
				'src'     => plugins_url(
					'/admin/js/gf-form-multicolumn-admin.min.js',
					__FILE__
				),
				'version' => $this->get_version(),
				'strings' => array(
					'tooManyColumnStarts' => __(
						' too many Row Starts to Row Ends. Please review and remove the excess Row Starts.',
						'gf-form-multicolumn'
					),
					'tooManyColumnEnds'   => __(
						' too many Row Ends to Row Starts. Please review and remove the excess Row Ends.',
						'gf-form-multicolumn'
					),
				),
				'enqueue' => array(
					array(
						'admin_page' => array(
							'form_editor',
							'form_settings',
						),
					),
				),
			);

		return array_merge( parent::scripts(), $scripts );
	}

	public function dequeue_if_in_admin_area() {
		if ( is_admin() ) {
			wp_dequeue_script(
				'gfmc_scripts_public'
			);
			wp_deregister_script( 'gfmc_scripts_public' );
		}

		return true;
	}

	public function styles() {
		// Styles needs to be present in admin also for Gutenberg blocks preview
		$styles = array(
			array(
				'handle'  => 'gfmc_styles',
				'src'     => plugins_url(
					'/public/css/gf-form-multicolumn.css',
					__FILE__
				),
				'version' => $this->get_version(),
				'enqueue' => array(
					array(
						'field_types' => array(
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

	/*
		public function enqueue_admin_preview_scripts( $styles, $form ) {
		error_log( 'GFMC preview styles hook fired for form ' . rgar( $form, 'id' ) );
		$url = plugin_dir_url( dirname( __DIR__, 2 ) ) . 'public/css/gf-form-multicolumn.min.css';
		error_log( 'GFMC preview CSS URL: ' . $url );

		$handle = 'gfmc_preview_styles';

		//$src = plugins_url( '/public/css/gf-form-multicolumn.min.css', $this->_full_path );
		$src = plugins_url( '/public/css/gf-form-multicolumn.css', $this->_full_path );
	echo("enqueue_admin_preview_scripts");
		wp_register_style( $handle, $src, array(), $this->get_version() );
		$styles[] = $handle;

		return $styles;
	}*/

	public function set_locale() {
		new WH_GF_Multicolumn_I18n();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->_version;
	}
}
