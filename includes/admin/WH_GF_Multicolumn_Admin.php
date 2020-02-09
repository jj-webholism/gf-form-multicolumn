<?php

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

	protected $_short_title; // Naming in the admin menu
	protected $_title = 'Multi Columns for Gravity Forms'; // Title on the admin page

	public function __construct( $plugin_name, $short_title, $version ) {

		$this->plugin_name  = $plugin_name;
		$this->_short_title = $short_title;
		$this->version      = $version;

		$this->add_fields();

		add_action( 'gform_admin_pre_render',
		            [ $this, 'admin_pre_render_function' ] );
		add_action( 'admin_enqueue_scripts',
		            [ $this, 'enqueue_admin_scripts' ] );
		add_filter( 'gform_form_settings',
		            [ $this, 'display_gfmc_form_settings' ],
		            10, 2 );
		add_filter( 'gform_pre_form_settings_save',
		            [ $this, 'save_gfmc_form_setting' ] );
		add_filter( 'gform_tooltips', [ $this, 'gfmc_tooltips' ] );
	}

	public function admin_pre_render_function( $form ) {
		echo \GFCommon::is_form_editor() ? "<script type='text/javascript'>
        gform.addFilter( 'gform_validation_error_form_editor', 'gfmc_validate_form_columns');
        </script>" : '';

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
		                      <label for="gfmc_enable_css">' . __( 'Load CSS Stylesheet' ) .
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
		        <label for="gfmc_enable_js">' . __( 'Load JS Stylesheet' ) .
		                          '</label>
		    </td>
		</tr>';

		return $settings;
	}

	public function save_gfmc_form_setting( $form ) {
		$form['gfmc_enable_css'] = rgpost( 'gfmc_enable_css' ) ? '1' : '0';
		$form['gfmc_enable_js']  = rgpost( 'gfmc_enable_js' ) ? '1' : '0';

		return $form;
	}

	public function gfmc_tooltips() {
		$tooltips['gfmc_enable_css']              = 'Load MultiColumn specific CSS file (note that deactivating this may result in the plugin layout breaking).';
		$tooltips['gfmc_enable_js']               = 'Load MultiColumn specific JS file which alters spacing when using conditional logic elements.';
		$tooltips['form_multiple_columns_fields'] = 'Use elements to construct column divisions in the form.';

		return $tooltips;
	}

	public function enqueue_admin_scripts( $form ) {
		wp_enqueue_script(
			'gfmc-admin-scripts',
			plugins_url( '/js/gf-form-multicolumn-admin.js', __FILE__ ),
			100,
			$this->version,
			'all'
		);
	}

	private function is_new_form( $form ): bool {
		return ( isset ( $form['gfmc_enable_css'] ) ) ? true : false;
	}

	private function getCSSEnabledString( $form ) {
		if ( $this->is_new_form( $form ) === false || (string) $form['gfmc_enable_css'] === '1' ) {
			return 'checked="checked"';
		}

		return '';
	}
}
