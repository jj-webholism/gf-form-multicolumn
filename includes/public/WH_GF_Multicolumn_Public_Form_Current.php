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

		// Set row and column counts
		$this->rowCount    = 1;
		$this->rowNumber   = 1;
		$this->columnCount = 0;

		$this->rowColumnArray =
			$this->calculate_row_and_column_count( $form );

		$this->structure_form_elements();

		add_action( 'gform_enqueue_scripts',
		            array ( $this, 'dequeue_v2_style' ) );
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
			} elseif ( $formField['type'] === 'column_end' ) {
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

			return '<li class="gfmc-column gfmc-row-' . $this->rowCounter .
			       '-column gfmc-row-' . $this->rowCounter . '-col-' .
			       $this->columnCounter . '-of-' . $this->rowColumnArray[ $this->rowCounter ] . $cssClass . '" style="width: ' . floor( 100 / $this->rowColumnArray[ $this->rowCounter ] ) . '%;"><ul>';
		}
		if ( $field->type === 'column_break' ) {
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			return '</ul></li><li class="gfmc-column gfmc-row-' . $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $this->rowColumnArray[ $this->rowCounter ] . $cssClass . '" style="width: ' . floor(
					100 / $this->rowColumnArray[ $this->rowCounter ]
				) . '%;"><ul>';
		}
		if ( $field->type === 'column_end' ) {
			$this->columnCounter = 0;
			if (isset($this->rowColumnArray[$this->rowCounter+1])) {
				$this->rowCounter ++;
			}

			return ( '</ul></li>' );
		}

		return ( $field_container );
	}

	public function dequeue_v2_style() {
		wp_dequeue_style( 'gfmc_styles_v2' );
	}
}
