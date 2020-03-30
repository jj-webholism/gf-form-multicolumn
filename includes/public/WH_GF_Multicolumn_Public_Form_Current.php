<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.0
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes/public
 */

namespace WH\GF\Multicolumn\Site;

class WH_GF_Multicolumn_Public_Form_Current {
	private $version;
	private $rowColumnArray;
	private $columnCounter;
	private $rowCounter;

	public function __construct( $version, $form ) {
		$this->version = $version;

		/*add_action( 'gform_enqueue_scripts',
		            array ( $this, 'enqueue_scripts' ) );*/

		// Set row and column counts
		$this->rowCount    = 1;
		$this->rowNumber   = 1;
		$this->columnCount = 0;

		$this->rowColumnArray =
			$this->calculate_row_and_column_count( $form );

		$this->structure_form_elements();
	}

	public function enqueue_scripts( $form = '', $is_ajax = true ) {

		echo ('<pre>WH_GF_Multicolumn_Public_Form_Current enqueue_public_scripts</pre>');

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

	private function calculate_row_and_column_count( $form ) {
		$rowColumnArray = array ();
		$rowCount       = 1;
		$columnCount    = 0;

		foreach ( $form['fields'] as $formField ) {
			if ( $formField['type'] === 'column_start' ) {
				// Determine counters based on form state
				$columnCount = 1;
			} elseif ( $formField['type'] === 'column_break' ) {
				$columnCount ++;
			} elseif ( $formField['type'] === 'column_end' || $formField['type'] === 'page' ) {
				$rowColumnArray[ $rowCount ] = $columnCount;
				$rowCount ++;
			}
		}

		return $rowColumnArray;
	}

	private function structure_form_elements() {
		$this->rowCounter    = 1;
		$this->columnCounter = 0;

		add_filter(
			'gform_field_container', array ( $this, 'define_output_elements' ),
			10, 6
		);

	}

	public function define_output_elements( $field_container, $field ) {
		if ( $field->type === 'column_start' ) {
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			return '<li class="gfmc-column gfmc-row-' . $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $this->rowColumnArray[ $this->rowCounter ] . $cssClass . '" style="width: ' . floor(
					100 / $this->rowColumnArray[ $this->rowCounter ]
				) . '%;"><ul>';
		}
		if ( $field->type === 'column_break' ) {
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			return '</ul></li><li class="gfmc-column gfmc-row-' .
			       $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $this->rowColumnArray[ $this->rowCounter ] . $cssClass . '" style="width: ' . floor(
				       100 / $this->rowColumnArray[ $this->rowCounter ]
			       ) . '%;"><ul>';
		}
		if ( $field->type === 'column_end' ) {
			$this->columnCounter = 0;
			$this->rowCounter ++;

			return ( '</ul></li>' );
		}
		if ( $field->type === 'row_break' || $field->type === 'page' ) {
			$this->columnCounter = 0;

			return '</ul><ul class="gform_fields top_label form_sublabel_below description_below">';
		}

		return ( $field_container );
	}
}
