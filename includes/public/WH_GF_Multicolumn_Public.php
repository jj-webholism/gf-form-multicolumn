<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.0
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/public
 */

namespace WH\GF\Multicolumn\Site;

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

	public function enqueue_selected_scripts( $form = '', $is_ajax = false ) {
		if ( ! ( $this->is_javascript_to_be_loaded( $form ) &&
		         ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) ) {
			wp_dequeue_script( 'gfmc-scripts-public' );
		}
		if ( ! ( $this->is_css_to_be_loaded( $form ) &&
		         ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) ) {
			wp_dequeue_style( 'gfmc-styles-v2' );
			wp_dequeue_style( 'gfmc-styles-v3' );
		}
	}

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
			//echo ('<br>is_gf_form_multicolumn_element_on_page<br><pre
			//>$field[\'type\']:'.$field['type'].', $field[\'cssClass\']: '.$field['cssClass'].'</pre>');
			if ( $field['type'] === 'column_start' || $field['type'] === 'column_end' || ($field['type'] === 'section' && (strpos($field['cssClass'], 'split-start') !== false) ) ) {
				return true;
			}
		}

		return false;
	}
}
