<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.0
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes/admin
 */

namespace WH\GF\Multicolumn\Admin;

use GFAPI;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Column_End;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Column_Start;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Column_Separator;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Group;

define( 'GF_MULTICOLUMN_FIELD_GROUP_TITLE', 'multiple_columns_fields' );

class WH_GF_Multicolumn_Admin {
	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->add_fields();

		add_action( 'gform_admin_pre_render',
		            array ( $this, 'admin_pre_render_function' ) );
		add_action( 'admin_enqueue_scripts',
		            array ( $this, 'enqueue_admin_scripts' ) );
		add_filter( 'gform_form_settings',
		            array ( $this, 'display_gfmc_form_settings' ),
		            10, 2 );
		add_filter( 'gform_pre_form_settings_save',
		            array ( $this, 'save_gfmc_form_setting' ) );
		add_filter( 'gform_tooltips', array ( $this, 'gfmc_tooltips' ) );
	}

	public function admin_pre_render_function( $form ) {
		if ( wp_script_is( 'gfmc-admin-scripts' ) ) {
			echo \GFCommon::is_form_editor() ? "<script type='text/javascript'>
        gform.addFilter( 'gform_validation_error_form_editor', 'gfmc_validate_form_columns', 10, 'gfmc_validate_form_columns_" . $form['id'] . "');
        </script>" : '';
		}

		return $form;
	}

	private function add_fields() {
		$gfmcFieldGroup           = new WH_GF_Multicolumn_Field_Group();
		$gfmcFieldColumnStart     = new WH_GF_Multicolumn_Field_Column_Start();
		$gfmcFieldColumnEnd       = new WH_GF_Multicolumn_Field_Column_End();
		$gfmcFieldColumnSeparator = new
		WH_GF_Multicolumn_Field_Column_Separator();
	}

	public function display_gfmc_form_settings( $settings, $form ) {
		$formCSSChecked          = $this->getCSSEnabledString( $form );
		$enable_gfmc_css_checked = $formCSSChecked;

		$settings[ __( 'Multiple Columns',
		               'gf-form-multicolumn' ) ]['gfmc_enable_css']
			= '<tr><th>' . __( 'Enable CSS',
			                   'gf-form-multicolumn' ) . ' ' .
			  gform_tooltip( 'gfmc_enable_css',
			                 '', true ) .
			  '</th><td>
		       <input id="gfmc_enable_css" type="checkbox" name="gfmc_enable_css"
		       value="1" ' . $enable_gfmc_css_checked . '>
		       <label for="gfmc_enable_css">' .
			  __( 'Load CSS Stylesheet', 'gf-form-multicolumn' ) .
			  '</label></td></tr>';

		$enable_gfmc_js_checked =
			rgar( $form, 'gfmc_enable_js' ) ? 'checked="checked"' : '';
		$settings[ __( 'Multiple Columns',
		               'gf-form-multicolumn' ) ]['gfmc_enable_js']
		                        = '<tr><th>' . __( 'Enable JS',
		                                           'gf-form-multicolumn' ) . ' ' .
		                          gform_tooltip( 'gfmc_enable_js', '', true ) . '</th>
		    <td>
		    <input id="gfmc_enable_css" type="checkbox" name="gfmc_enable_js"
		    value="1" ' . $enable_gfmc_js_checked . '>
		    <label for="gfmc_enable_js">' . __( 'Load JS Script',
		                                        'gf-form-multicolumn' ) . '</label></td></tr>';

		return $settings;
	}

	public function save_gfmc_form_setting( $form ) {
		$form['gfmc_enable_css'] = rgpost( 'gfmc_enable_css' ) ? '1' : '0';
		$form['gfmc_enable_js']  = rgpost( 'gfmc_enable_js' ) ? '1' : '0';

		return $form;
	}

	public function gfmc_tooltips() {
		$tooltips['gfmc_enable_css']              = __( 'Load MultiColumn specific CSS file (note that deactivating this may result in the plugin layout breaking).',
		                                                'gf-form-multicolumn' );
		$tooltips['gfmc_enable_js']               = __( 'Load MultiColumn specific JS file which alters spacing when using conditional logic elements.',
		                                                'gf-form-multicolumn' );
		$tooltips['form_multiple_columns_fields'] = __( 'Use elements to construct column divisions in the form.',
		                                                'gf-form-multicolumn' );

		return $tooltips;
	}

	public function enqueue_admin_scripts( $form ) {
		wp_register_script( 'gfmc-admin-scripts',
		                    plugins_url( '/js/gf-form-multicolumn-admin.min.js',
		                                 __FILE__ ),
		                    100,
		                    $this->version,
		                    'all' );
		$gfmcTranslationArray = array (
			'tooManyColumnStarts' => __( ' too many Column Starts to Column Ends. Please review and remove the excess Column Starts.',
			                             'gf-form-multicolumn' ),
			'tooManyColumnEnds'   => __( ' too many Column Ends to Column Starts. Please review and remove the excess Column Ends.',
			                             'gf-form-multicolumn' ),
		);
		wp_localize_script( 'gfmc-admin-scripts', 'gfmc',
		                    $gfmcTranslationArray );
		wp_enqueue_script(
			'gfmc-admin-scripts'
		);
		wp_dequeue_script( 'gfmc-scripts-public' );
		wp_dequeue_style( 'gfmc-styles' );
	}

	private function is_new_form( $form ) {
		return ( isset ( $form['gfmc_enable_css'] ) ) ? true : false;
	}

	private function getCSSEnabledString( $form ) {
		if ( $this->is_new_form( $form ) === false || (string) $form['gfmc_enable_css'] === '1' ) {
			return 'checked="checked"';
		}

		return '';
	}
}
