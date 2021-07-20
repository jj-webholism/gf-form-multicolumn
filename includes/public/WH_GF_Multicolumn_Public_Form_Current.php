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
	/*	private $version;*/
	private $gfLegacyVersion;
	private $rowColumnArray;
	private $columnCounter;
	private $rowCounter;

	private $instanceCounter;

	public function __construct( $version, $form ) {
		$this->instanceCounter          = 0;
		$this->internalContainerCounter = 0;

		// Set row and column counts
		$this->rowCount    = 1;
		$this->rowNumber   = 1;
		$this->columnCount = 0;

		$this->calculate_row_and_column_count( $form );

		// Stores form legacy value for ul/li (1), or divs (2) layout
		$this->gfLegacyVersion = isset( $form['markupVersion'] ) ? (int) $form['markupVersion'] : 1;

		$this->structure_form_elements();

		add_action( 'gform_enqueue_scripts',
		            array ( $this, 'dequeue_v2_style' ) );
	}

	private function build_form_field_row_array( $form ) {
		$rowCounter    = 1;
		$columnCounter = 1;

		for ( $i = 0; $i < count( $form['fields'] ); $i ++ )
		{
			$field = $form['fields'][$i];
			if ( $field->type === 'column_start' )
			{
				$this->formFieldRowArray[$rowCounter][] = [
					$field->id,
					$field->type,
					$field->cssClass,
				];
				for ( $j = $i ++; $j < count( $form['fields'] ) - $i; $j ++ )
				{
					$rowField = $form['fields'][$j];
					if ( $rowField === 'column_break' || $rowField === 'column_end' )
					{
						if ( $rowField->type === 'column_end' )
						{
							$rowCounter ++;
							break;
						}
						else
						{
							$columnCounter ++;
						}
					}
					else
					{
						$this->formFieldRowArray[$rowCounter][] = [
							$rowField->id,
							$rowField->type,
							$rowField->cssClass,
						];
					}
				}
			}
		}
	}

	private function calculate_row_and_column_count( $form ) {
		$rowCount    = 1;
		$columnCount = 0;

		foreach ( $form['fields'] as $formField )
		{
			if ( $formField['type'] === 'column_start' )
			{
				// Determine counters based on form state
				$columnCount = 1;
			}
			elseif ( $formField['type'] === 'column_break' )
			{
				$columnCount ++;
			}
			elseif ( $formField['type'] === 'column_end' )
			{
				$this->rowColumnArray[$rowCount] = $columnCount;
				$rowCount ++;
			}
		}

		return $this->rowColumnArray;
	}

	private function structure_form_elements() {
		$this->instanceCounter ++;

		$this->rowCounter    = 1;
		$this->columnCounter = 0;

		$gfInstallation      = get_plugin_data( ABSPATH .
		                                        'wp-content/plugins/gravityforms/gravityforms.php' );
		$gravityFormsVersion = $gfInstallation['Version'];


		if ( substr_count( $gravityFormsVersion, '.' ) > 1 )
		{
			$lastPeriod          = strrpos( $gravityFormsVersion, '.' ) - 1;
			$gravityFormsVersion = (float) substr( $gravityFormsVersion, 0,
			                                       strlen
			                                       ( $gravityFormsVersion )
			                                       - $lastPeriod );
		}
		else
		{
			$gravityFormsVersion = (float) $gravityFormsVersion;
		}

		if ( $gravityFormsVersion >= 2.5 && $this->gfLegacyVersion == 2 )
		{
			add_filter(
				'gform_field_container',
				array ( $this, 'define_output_elements_2_5' ),
				10, 6
			);
		}
		else
		{
			add_filter(
				'gform_field_container',
				array ( $this, 'define_output_elements' ),
				10, 6
			);
		}
	}

	public function define_output_elements( $field_container, $field ) {
		// Eliminate division by zero error
		$divisor = $this->rowColumnArray[$this->rowCounter] > 0 ?
			$this->rowColumnArray[$this->rowCounter] : 1;

		if ( $field->type === 'column_start' )
		{
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			return '<li class="gfmc-column gfmc-row-' . $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $divisor . $cssClass . '" style="width: ' . floor(
					100 / $divisor
				) . '%;"><ul>';
		}
		if ( $field->type === 'column_break' )
		{
			$this->columnCounter ++;

			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			return '</ul></li><li class="gfmc-column gfmc-row-' .
			       $this->rowCounter . '-column gfmc-row-' .
			       $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $divisor . $cssClass . '" style="width: ' . floor( 100 / $divisor ) . '%;"><ul>';
		}
		if ( $field->type === 'column_end' )
		{
			$this->columnCounter = 0;
			$this->rowCounter ++;

			return ( '</ul></li>' );
		}
		if ( $field->type === 'row_break' || $field->type === 'page' )
		{
			$this->columnCounter = 0;

			return '</ul><ul class="gform_fields top_label form_sublabel_below description_below">';
		}

		return ( $field_container );
	}

	public function define_output_elements_2_5( $field_container, $field, $form
	) {
		if ( $this->is_gfmc_field_type( $field->type ) )
		{
			$columnWidthCount = $this->rowColumnArray[$this->rowCounter] * 2;
			$columnWidth      = is_numeric( ( 100 - $columnWidthCount ) /
			                                $this->rowColumnArray[$this->rowCounter] )
				? floor( ( 100 - $columnWidthCount ) /
				         $this->rowColumnArray[$this->rowCounter] ) : 1;

			if ( $field->type === 'column_start' )
			{
				return ( $this->generate_start_of_row( $columnWidth ) );

			}
			if ( $field->type === 'column_break' )
			{
				return ( $this->generate_break_in_row( $columnWidthCount,
				                                       $columnWidth ) );
			}
			if ( $field->type === 'column_end' )
			{
				return ( $this->generate_end_of_row() );
			}

		}

		return ( $field_container );
	}

	private function is_gfmc_field_type( $fieldType ) {
		if ( $fieldType === 'column_start' || $fieldType === 'column_break'
		     || $fieldType === 'column_end' )
		{
			return true;
		}

		return false;
	}

	private function generate_start_of_row( $columnWidth ) {
		$this->columnCounter ++;

		return '<div class="gfmc-container"><div class="gfield gfmc-column gfmc-field" style="max-width: ' . $columnWidth . '%;">';

	}

	private function generate_break_in_row( $columnWidthCount, $columnWidth ) {
		$this->columnCounter ++;

		return '</div><div class="gfield gfmc-column gfmc-field" style="max-width: ' . $columnWidth . '%;">';
	}

	private function generate_end_of_row() {
		$this->columnCounter = 0;
		if ( isset( $this->rowColumnArray[$this->rowCounter + 1] ) )
		{
			$this->rowCounter ++;
		}

		return ( '</div></div>' );
	}

	public function dequeue_v2_style() {
		wp_dequeue_style( 'gfmc_styles_v2' );
	}
}
