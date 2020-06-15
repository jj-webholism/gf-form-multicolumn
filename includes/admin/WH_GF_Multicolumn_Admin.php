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
		$enable_gfmc_css_checked = rgar( $form,
		                                 'gfmcEnableCSS' ) ? 'checked="checked"' : '';
		$enable_gfmc_js_checked  = rgar( $form,
		                                 'gfmcEnableJS' ) ? 'checked="checked"' : '';

		// Settings - CSS form element definition
		$gfmcEnableCSSHeaderString =
			'<th>' . __( 'Enable CSS', 'gf-form-multicolumn' ) .
			' ' . gform_tooltip( 'gfmc_enable_css', '', true ) .
			'</th>';

		$gfmcEnableCSSDataString = '<td>
		       <input id="gfmc_enable_css" type="checkbox" value="1" name="gfmcEnableCSS" '
		                           . $enable_gfmc_css_checked . '>
		       <label for="gfmc_enable_css">' .
		                           __( 'Load CSS Stylesheet',
		                               'gf-form-multicolumn' ) .
		                           '</label></td>';


		// Define the Settings Page custom elements layout
		// Note that the HTML name needs to align with the Settings variable
		// name
		$settings['Multiple Columns']['gfmcEnableCSS'] =
			'<tr>' . $gfmcEnableCSSHeaderString . $gfmcEnableCSSDataString . '</tr>';


		// Settings - JS form element definition
		$gfmcEnableJSHeaderString = '<th>' . __( 'Enable JS',
		                                         'gf-form-multicolumn' ) . ' ' .
		                            gform_tooltip( 'gfmc_enable_js', '',
		                                           true ) . '</th>';
		$enableJSValue            = rgar( $form, 'gfmcEnableJS' ) ? rgar( $form,
		                                                                  'gfmcEnableJS' ) : 0;

		$gfmcEnableJSDataString = '<td>
		    <input id="gfmc_enable_js" type="checkbox" value="1" name="gfmcEnableJS" ' .
		                          $enable_gfmc_js_checked . '>
		    <label for="gfmc_enable_js">' . __( 'Load JS Script',
		                                        'gf-form-multicolumn' ) . '</label></td>';

		$settings['Multiple Columns']['gfmcEnableJS']
			= '<tr>' . $gfmcEnableJSHeaderString .
			  $gfmcEnableJSDataString .
			  '</tr>';

		return $settings;
	}

	public function save_gfmc_form_setting( $form ) {
		/*		echo ('<br>pre rgpost( \'gfmcEnableCSS\' ): '.rgpost( 'gfmcEnableCSS' )
					  .'<br>');
				echo ('<br>pre rgpost( \'gfmcEnableJS\' ): '.rgpost( 'gfmcEnableJS' )
					  .'<br>');*/

		/*		if ( rgpost( 'gfmcEnableCSS' ) === 'on' || rgpost( 'gfmcEnableCSS' )
														   === true ) {
					$form['gfmcEnableCSS'] = true;
				} else {
					$form['gfmcEnableCSS'] = 0;
				}
				if ( rgpost( 'gfmcEnableJS' ) === 'on' || rgpost( 'gfmcEnableJS' ) ===
														 true
				) {
					$form['gfmcEnableJS'] = true;
				} else {
					$form['gfmcEnableJS'] = false;
				}*/
		$form['gfmcEnableCSS'] = rgpost( 'gfmcEnableCSS' ) == 1 ? true :
			false;
		$form['gfmcEnableJS']  = rgpost( 'gfmcEnableJS' ) == 1 ? true :
			false;

		/*		echo ('<pre>');
				var_dump($form);
				echo('</pre>');*/

		/*		echo ('<br>rgpost( \'gfmcEnableCSS\' ): '.rgpost( 'gfmcEnableCSS' ).'<br>');
				echo ('<br>rgpost( \'gfmcEnableJS\' ): '.rgpost( 'gfmcEnableJS' ).'<br>');*/

//		die ('save_gfmc_form_setting EXIT');
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

	public function enqueue_admin_scripts() {
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
//		wp_dequeue_script( 'gfmc-scripts-public' );
	}

	private function is_new_form( $form ) {
		//return ( isset ( $form['gfmcEnableCSS'] ) ) ? true : false;
	}

	private function getCSSEnabledString( $form ) {
		if ( $this->is_new_form( $form ) === false || (string) $form['gfmcEnableCSS'] === '1' ) {
			return 'checked="checked"';
		}

		return '';
	}
}
