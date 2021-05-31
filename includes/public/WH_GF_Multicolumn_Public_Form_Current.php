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
//		echo ('<pre>'); print_r ($form['fields']); echo('</pre>');
		$rowColumnArray = array ();
		$rowCount       = 1;
		$columnCount    = 0;

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
				$rowColumnArray[$rowCount] = $columnCount;
				$rowCount ++;
			}
		}

		return $rowColumnArray;
	}

	private function structure_form_elements() {
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

		if ( $gravityFormsVersion >= 2.5 )
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
		if ( $field->type === 'column_start' )
		{
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			return '<li class="gfmc-column gfmc-row-' . $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $this->rowColumnArray[$this->rowCounter] . $cssClass . '" style="width: ' . floor(
					100 / $this->rowColumnArray[$this->rowCounter]
				) . '%;"><ul>';
		}
		if ( $field->type === 'column_break' )
		{
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			return '</ul></li><li class="gfmc-column gfmc-row-' .
			       $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $this->rowColumnArray[$this->rowCounter] . $cssClass . '" style="width: ' . floor(
				       100 / $this->rowColumnArray[$this->rowCounter]
			       ) . '%;"><ul>';
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

	public function define_output_elements_2_5( $field_container, $field ) {
		if ( $field->type === 'column_start' )
		{
			//echo ('<pre>'); var_dump ('column_start'); echo ('</pre>');
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			/*return '<div class="gfmc-container"><div class="gfmc-column gfmc-row-' .
			       $this->rowCounter .
			       '-column gfmc-row-' . $this->rowCounter . '-col-' .
			       $this->columnCounter . '-of-' . $this->rowColumnArray[
			                                       $this->rowCounter ] . $cssClass . '" style="width: ' .
			       floor( 100 / $this->rowColumnArray[ $this->rowCounter ] )
			       . '%;">';*/
			// Calculate the number of columns to build the CSS template
			// column string
			$gridColumnString = '';
			for (
				$i = 0; $i < $this->rowColumnArray[$this->rowCounter]; $i ++
			)
			{
				$gridColumnString .= floor( 100 / $this->rowColumnArray[$this->rowCounter] ) . "% ";
			}

			return '<div class="gfmc-container" style="grid-template-columns: repeat( auto-fit, minmax(' . floor( 100 / $this->rowColumnArray[$this->rowCounter] ) . '%, 1fr));"><div class="gfmc-column gfmc-row-' .
			       $this->rowCounter .
			       '-column gfmc-row-' . $this->rowCounter . '-col-' .
			       $this->columnCounter . '-of-' . $this->rowColumnArray[$this->rowCounter] . $cssClass . '" >';
		}
		if ( $field->type === 'column_break' )
		{
			//var_dump ('column_break');
			$this->columnCounter ++;
			$cssClass = $field->cssClass !== '' ? ' ' . $field->cssClass : '';

			/*return '</div><div class="gfmc-column gfmc-row-' .
			       $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $this->rowColumnArray[ $this->rowCounter ] . $cssClass . '" style="width: ' . floor(
				       100 / $this->rowColumnArray[ $this->rowCounter ]
			       ) . '%;">';*/

			return '</div><div class="gfmc-column gfmc-row-' .
			       $this->rowCounter . '-column gfmc-row-' . $this->rowCounter . '-col-' . $this->columnCounter . '-of-' . $this->rowColumnArray[$this->rowCounter] . $cssClass . '">';
		}
		if ( $field->type === 'column_end' )
		{
			//var_dump ('column_end');
			$this->columnCounter = 0;
			if ( isset( $this->rowColumnArray[$this->rowCounter + 1] ) )
			{
				$this->rowCounter ++;
			}

			return ( '</div></div>' );
		}

		return ( $field_container );
	}

	public function dequeue_v2_style() {
		wp_dequeue_style( 'gfmc_styles_v2' );
	}
}
