<?php

/**
 * The public-specific functionality of the plugin which covers original form
 * creation style.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.1
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes/public
 */

namespace WH\GF\Multicolumn\Site;

class WH_GF_Multicolumn_Public_Form_Deprecated {
	private $version;
	private $cssArray;
	private $rowColumnArray;
	private $columnCounter;
	private $rowCounter;

	public function __construct( $version, $form ) {
		$this->version = $version;
		add_action( 'gform_enqueue_scripts',
		            array ( $this, 'dequeue_v3_style' ) );
		$this->rowColumnArray =
			$this->calculate_row_and_column_count( $form );
		$this->structure_form_elements();
	}

	private function calculate_row_and_column_count( $form ) {
		$rowColumnArray      = array ();
		$rowCount            = 1;
		$columnCount         = 0;
		$dividerStartCounter = 0;
		$dividerEndCounter   = 0;

		foreach ( $form['fields'] as $formField ) {
			if ( $formField['type'] === 'section' ) {
				if ( strpos( $formField->cssClass, 'split-start' ) !== false ) {
					// Set row and column details for later break up based on row and column position
					$formField->calculationFormula = 'row-' . $rowCount .
					                                 'column-' . $columnCount ++;
					$rowColumnArray[ $rowCount ]   = $columnCount;

					$formField->type = 'dividerStart';
					++ $dividerStartCounter;
					$formField->cssClass .= ' dividerStart-' . $dividerStartCounter;
				} elseif ( strpos( $formField->cssClass,
				                   'split-end' ) !== false ) {
					$formField->type = 'dividerEnd';
					++ $dividerEndCounter;
					$formField->cssClass .= ' dividerEnd-' . $dividerEndCounter;
				} elseif ( strpos( $formField->cssClass,
				                   'new-row' ) !== false ) {
					$formField->type = 'row-divider';
					// Reset column counter
					$columnCount         = 0;
					$rowCount            += $rowCount;
					$formField->cssClass .= ' row-divider';
				}
			}
		}

		// Loop again through the field list to ensure that the first and last column are identified, based on the numbers defined in the previous loop
		foreach ( $form['fields'] as $formField ) {
			if ( $formField->type === 'dividerStart' && strpos(
					$formField->cssClass, 'dividerStart-1'
				) ) {
				$formField->cssClass .= ' start-divider';
			} elseif ( $formField->type === 'dividerEnd' && strpos(
					$formField->cssClass, 'dividerEnd-' . $dividerEndCounter
				) ) {
				$formField->cssClass .= ' end-divider';
			}
			// Add column count to all dividers to ensure that this can be calculated as the split quantity later
			if ( $formField->type === 'dividerStart' ) {
				// Set cssClass details to allow for break up of rows and columns based on
				// Variable to hold the value stored in the calculationFormula parameter that relates to the row and column count
				// Not that this will hold the value for the commencement of the column string, and this defines the end of the row count number
				$endOfRowCount   = strpos( $formField->calculationFormula,
				                           'column-' );
				$rowNumberLength = $endOfRowCount - 4;
				$rowNumber       = substr(
					$formField->calculationFormula, 4, $rowNumberLength
				);
				$columnNumber    = substr( $formField->calculationFormula,
				                           $endOfRowCount + 7 ) + 1;
				if ( strpos( $formField->cssClass,
				             'column-count-' ) === false ) {
					$formField->cssClass .= ' column-count-' . $columnNumber . '-of-' . $rowColumnArray[ $rowNumber ];
				}
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

	function define_output_elements( $field_container, $field ) {
		if ( IS_ADMIN ) {
			return $field_container;
		} // only modify HTML on the front end

		// Variable to specify the width of the column
		$columnWidth = null;

		// Calculate width value based on the number of columns in the row, which has been coded into the cssClass
		$columnCountStartPos = strpos( $field->cssClass, '-of-' );
		if ( $columnCountStartPos !== false ) {
			$columnTotalForRowTerminator = strpos(
				substr( $field->cssClass, $columnCountStartPos + 4 ), ' '
			);
			if ( $columnTotalForRowTerminator > 0 ) {
				$columnTotalForRow = substr(
					$field->cssClass, $columnCountStartPos + 4,
					$columnTotalForRowTerminator
				);
			} else {
				$columnTotalForRow = substr(
					$field->cssClass, $columnCountStartPos + 4
				);
			}
			$columnWidth = ' width: ' . floor( 100 / $columnTotalForRow ) . '%;';
		}

		$columnSpecificCSSTextPos = strpos( $field->cssClass,
		                                    'column-count-' );
		$columnSpecificCSSText    = null;
		if ( $columnSpecificCSSTextPos !== false ) {
			$columnSpecificCSSText = substr(
				$field->cssClass, $columnSpecificCSSTextPos
			);
		}

		// Break the existing cssClass definition to see if the previously set markers for the start and end columns have been set
		if ( $field->type === 'dividerStart' && strpos(
			                                        $field->cssClass,
			                                        'start-divider'
		                                        ) !== false ) {
			$this->cssArray[] = '<style>.' . $columnSpecificCSSText . ' { ' . $columnWidth . ' }</style>';
			$field_container  = '<li class="' . $columnSpecificCSSText . ' divider-list-item multicolumn-start"><div class="multicolumn-wrapper"><ul>';
		} elseif ( $field->type === 'dividerStart' ) {
			$this->cssArray[] = '<style>.' . $columnSpecificCSSText . ' { ' . $columnWidth . ' }</style>';
			$field_container  = '<li class="' . $columnSpecificCSSText . ' divider-list-item"><div><ul>';
		}
		if ( $field->type === 'dividerEnd' && strpos(
			                                      $field->cssClass,
			                                      'end-divider'
		                                      ) !== false ) {
			$field_container = '</ul></div></li>';
		} elseif ( $field->type === 'dividerEnd' ) {
			$field_container = '</ul>';
		}
		if ( $field->type === 'row-divider' ) {
			$field_container = '<div class="row-divider"></div>';
		}

		return $field_container;
	}

	public function dequeue_v3_style() {
		wp_dequeue_style( 'gfmc_styles_v3' );
	}
}
