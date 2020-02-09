<?php

namespace WH\GF\Multicolumn\Site;

class WH_GF_Multicolumn_Public_Form_Current {
	private $version;
	private $rowColumnArray;
	private $columnCounter;
	private $rowCounter;

	public function __construct( $version, $form ) {
		$this->version = $version;
		add_action( 'gform_enqueue_scripts',
		            [ $this, 'enqueue_scripts' ] );
		// Set row and column counts
		$this->rowCount    = 1;
		$this->rowNumber   = 1;
		$this->columnCount = 0;

		$this->rowColumnArray =
			$this->calculate_row_and_column_count( $form );

		$this->structure_form_elements();
	}

	private function calculate_row_and_column_count( $form ) {
		$rowColumnArray = [];
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
			'gform_field_container', [ $this, 'define_output_elements' ],
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

	public function enqueue_scripts( $form ) {
		if ( $this->is_javascript_to_be_loaded( $form ) &&
		     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
			wp_enqueue_script(
				'gfmc-scripts',
				plugins_url( '/js/gf-form-multicolumn.js', __FILE__ ),
				100,
				$this->version,
				'all'
			);
		}
		if ( $this->is_css_to_be_loaded( $form ) &&
		     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
			wp_enqueue_style(
				'gfmc-styles',
				plugins_url( '/css/gf-form-multicolumn.css', __FILE__ ),
				false,
				$this->version,
				'all'
			);
		}
	}

	private function is_javascript_to_be_loaded( $form ) {
		return isset( $form['gfmc_enable_js'] ) && $form['gfmc_enable_js'];
	}

	private function is_css_to_be_loaded( $form ) {
		return isset( $form['gfmc_enable_css'] ) && $form['gfmc_enable_css'];
	}

	private function is_gf_form_multicolumn_element_on_page( $form ) {
		foreach ( $form['fields'] as $field ) {
//			echo ('<pre>'); var_dump ($field['type']); echo ('</pre>');
			if ( $field['type'] === 'column_start' || $field['type'] === 'column_end' ) {
				return true;
			}
		}

		return false;
	}
}
