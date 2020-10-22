<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.1
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/public
 */

namespace WH\GF\Multicolumn\Site;

\GFForms::include_addon_framework();

class WH_GF_Multicolumn_Public {
	private $plugin_name;
	private $version;

	private $form;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param   string  $plugin_name  The name of the plugin.
	 * @param   string  $version      The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function display() {
		add_filter( 'gform_pre_render', [ $this, 'pre_render_form' ], 10, 4 );

		// To run the conditional logic javascript mutator on AJAX form load.
		add_action( 'gform_register_init_scripts',
		            array ( $this, 'run_conditional_function' ), 10, 2 );
	}

	public function pre_render_form( $form ) {
		if ( $form !== false ) {
			$this->form = $form;

			// Test to see if the form contains any type of multicolumn element
			if ( $this->get_form_version_by_elements( $form ) === 2 ) {
				$version2Form = new
				WH_GF_Multicolumn_Public_Form_Deprecated( $this->version,
				                                          $form );
			} elseif ( $this->get_form_version_by_elements( $form ) > 2 ) {
				$version3Form = new
				WH_GF_Multicolumn_Public_Form_Current( $this->version, $form );
			}
		}

		return $form;
	}

	private function get_form_version_by_elements( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] === 'column_start' || $field['type'] === 'column_break' || $field['type'] === 'column_end' ) {
				return 3;
			}

			if ( ( $field['cssClass'] === 'split-start' ) || $field['cssClass'] === 'split-end' ) {
				return 2;
			}
		}
	}

	public function dequeue_selected_scripts( $form = '', $is_ajax = false ) {
		if ( ! rgar( $form, 'gfmcEnableJS' ) ) {
			wp_dequeue_script( 'gfmc_scripts_public' );
		}
		if ( ! rgar( $form, 'gfmcEnableCSS' ) ) {
			wp_dequeue_style( 'gfmc_styles_v2' );
			wp_dequeue_style( 'gfmc_styles_v3' );
		}
	}

	private function is_gf_form_multicolumn_element_on_page( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] === 'column_start' || $field['type'] === 'column_end' || ( $field['type'] === 'section' && ( strpos( $field['cssClass'],
			                                                                                                                         'split-start' ) !== false ) ) ) {
				return true;
			}
		}

		return false;
	}

	/* This is used for running the conditional logic mutator within AJAX
	forms. */
	public function run_conditional_function( $form ) {
		if ( rgar( $form, 'gfmcEnableJS' ) &&
		     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
			$script = 'gfFormsAddConditionalColumns();';

			\GFFormDisplay::add_init_script( $form['id'],
			                                 'gfmc-conditional-ajax',
			                                 \GFFormDisplay::ON_PAGE_RENDER,
			                                 $script );
		}
	}
}
