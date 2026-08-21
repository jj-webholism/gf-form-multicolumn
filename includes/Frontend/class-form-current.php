<?php
/**
 * Current Form Handler Class.
 *
 * @package WH\GF\Multicolumn\Frontend
 */

namespace WH\GF\Multicolumn\Frontend;

use GFCommon;

/**
 * Parses and manipulates current Gravity Forms instance to render multi-column layouts.
 */
class Form_Current {

	/**
	 * Gravity Forms legacy version flag.
	 *
	 * @var string
	 */
	public $gf_legacy_version = '2.5';

	/**
	 * Path to plugin directory.
	 *
	 * @var string
	 */
	public $plugin_directory = '';

	/**
	 * Matrix storing row and column counts.
	 *
	 * @var array
	 */
	public $row_column_array = array();

	/**
	 * Current column counter.
	 *
	 * @var int
	 */
	public $column_counter = 0;

	/**
	 * Current row counter.
	 *
	 * @var int
	 */
	public $row_counter = 0;

	/**
	 * Instance counter for current form.
	 *
	 * @var int
	 */
	public $instance_counter = 0;

	/**
	 * Output HTML string.
	 *
	 * @var string
	 */
	private $form_string = '';

	/**
	 * Form configuration object.
	 *
	 * @var array
	 */
	private $form = array();

	/**
	 * Initialize class properties and run form structural calculations.
	 *
	 * @param string $form_string Form HTML output.
	 * @param array  $form        Gravity Forms configuration array.
	 */
	public function __construct( $form_string = '', $form = array() ) {
		$this->form_string      = $form_string;
		$this->form             = $form;
		$this->plugin_directory = plugin_dir_path( __FILE__ );

		$this->instance_counter = isset( $form['id'] ) ? (int) $form['id'] : 0;

		$this->calculate_row_and_column_count();
	}

	/**
	 * Retrieves structured form HTML output.
	 *
	 * @return string Processed HTML string.
	 */
	public function get_form() {
		return $this->structure_form_elements();
	}

	/**
	 * Calculates grid positions and matrix counts for rows and columns.
	 *
	 * @return void
	 */
	private function calculate_row_and_column_count() {
		$row_count    = 0;
		$column_count = 0;

		if ( isset( $this->form['fields'] ) && is_array( $this->form['fields'] ) ) {
			foreach ( $this->form['fields'] as $form_field ) {
				if ( isset( $form_field->type ) && 'column_start' === $form_field->type ) {
					++$column_count;
				} elseif ( isset( $form_field->type ) && 'column_break' === $form_field->type ) {
					++$column_count;
				} elseif ( isset( $form_field->type ) && 'column_end' === $form_field->type ) {
					$this->row_column_array[ $row_count ] = $column_count;
					++$row_count;
					$column_count = 0;
				}
			}
		}
	}

	/**
	 * Structures layout elements for form display.
	 *
	 * @return string Modified form HTML string.
	 */
	private function structure_form_elements() {
		$this->row_counter    = 0;
		$this->column_counter = 0;

		$gravity_forms_version = class_exists( '\GFCommon' ) ? GFCommon::$version : '2.5';

		if ( version_compare( $gravity_forms_version, $this->gf_legacy_version, '<' ) ) {
			return $this->define_output_elements();
		}

		return $this->define_output_elements_2_5();
	}

	/**
	 * Defines output structure for legacy Gravity Forms versions (< 2.5).
	 *
	 * @return string Parsed HTML.
	 */
	private function define_output_elements() {
		if ( ! isset( $this->form['fields'] ) || ! is_array( $this->form['fields'] ) ) {
			return $this->form_string;
		}

		$html_output = $this->form_string;

		foreach ( $this->form['fields'] as $form_field ) {
            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$css_class  = isset( $form_field->cssClass ) ? $form_field->cssClass : '';
			$field_type = isset( $form_field->type ) ? $form_field->type : '';

			if ( 'column_start' === $field_type ) {
				$width_percentage = isset( $this->row_column_array[ $this->row_counter ] ) && $this->row_column_array[ $this->row_counter ] > 0
					? (int) ( 100 / $this->row_column_array[ $this->row_counter ] )
					: 100;

				$replacement = $this->generate_start_of_row( $css_class, $width_percentage );
				$html_output = str_replace( "id='field_{$this->instance_counter}_{$form_field->id}'", $replacement, $html_output );
			} elseif ( 'column_break' === $field_type ) {
				$width_percentage = isset( $this->row_column_array[ $this->row_counter ] ) && $this->row_column_array[ $this->row_counter ] > 0
					? (int) ( 100 / $this->row_column_array[ $this->row_counter ] )
					: 100;

				$replacement = $this->generate_break_in_row( $css_class, $width_percentage );
				$html_output = str_replace( "id='field_{$this->instance_counter}_{$form_field->id}'", $replacement, $html_output );
			} elseif ( 'column_end' === $field_type ) {
				$replacement = $this->generate_end_of_row();
				$html_output = str_replace( "id='field_{$this->instance_counter}_{$form_field->id}'", $replacement, $html_output );

				++$this->row_counter;
			}
		}

		return $html_output;
	}

	/**
	 * Defines output structure for Gravity Forms 2.5+.
	 *
	 * @return string Parsed HTML string.
	 */
	private function define_output_elements_2_5() {
		if ( ! isset( $this->form['fields'] ) || ! is_array( $this->form['fields'] ) ) {
			return $this->form_string;
		}

		$html_output = $this->form_string;

		foreach ( $this->form['fields'] as $form_field ) {
            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$css_class  = isset( $form_field->cssClass ) ? $form_field->cssClass : '';
			$field_type = isset( $form_field->type ) ? $form_field->type : '';

			if ( 'column_start' === $field_type ) {
				$replacement = '<li id="field_' . $this->instance_counter . '_' . $form_field->id . '" class="gfield gfmc_row_start ' . esc_attr( $css_class ) . '">';
				$html_output = str_replace( "id='field_{$this->instance_counter}_{$form_field->id}'", $replacement, $html_output );
			} elseif ( 'column_break' === $field_type ) {
				$replacement = '<li id="field_' . $this->instance_counter . '_' . $form_field->id . '" class="gfield gfmc_column_break ' . esc_attr( $css_class ) . '">';
				$html_output = str_replace( "id='field_{$this->instance_counter}_{$form_field->id}'", $replacement, $html_output );
			} elseif ( 'column_end' === $field_type ) {
				$replacement = '<li id="field_' . $this->instance_counter . '_' . $form_field->id . '" class="gfield gfmc_row_end ' . esc_attr( $css_class ) . '">';
				$html_output = str_replace( "id='field_{$this->instance_counter}_{$form_field->id}'", $replacement, $html_output );

				++$this->row_counter;
			}
		}

		return $html_output;
	}

	/**
	 * Checks if field type belongs to multi-column field group.
	 *
	 * @param string $field_type Type identifier.
	 * @return bool True if multi-column field type.
	 */
	public function is_gfmc_field_type( $field_type ) {
		return in_array( $field_type, array( 'column_start', 'column_break', 'column_end' ), true );
	}

	/**
	 * Generates starting HTML element for a row.
	 *
	 * @param string $css_class Additional CSS classes.
	 * @return string Opening row element HTML.
	 */
	private function generate_start_of_row( $css_class ) {
		++$this->column_counter;
		return 'class="gfmc_row_start ' . esc_attr( $css_class ) . '"';
	}

	/**
	 * Generates separator HTML element in a row.
	 *
	 * @param string $css_class Additional CSS classes.
	 * @return string Column break element HTML.
	 */
	private function generate_break_in_row( $css_class ) {
		++$this->column_counter;
		return 'class="gfmc_column_break ' . esc_attr( $css_class ) . '"';
	}

	/**
	 * Generates ending HTML element for a row.
	 *
	 * @return string Closing row element HTML.
	 */
	private function generate_end_of_row() {
		$this->column_counter = 0;
		return 'class="gfmc_row_end"';
	}
}
