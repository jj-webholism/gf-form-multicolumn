<?php
/**
 * Legacy List Formatter Class.
 *
 * @package WH\GF\Multicolumn\Frontend\Formatters
 */

namespace WH\GF\Multicolumn\Frontend\Formatters;

/**
 * Handles legacy <ul>/<li> float-based and list-structure layouts.
 */
class Legacy_Formatter implements Formatter_Interface {


	/**
	 * Formats raw form HTML into <li> structural elements.
	 *
	 * @param string $form_string Form HTML markup.
	 * @param array  $form Gravity Forms configuration array.
	 * @return string Processed HTML markup.
	 */
	public function format( $form_string, array $form ) {
		if ( ! isset( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
			return $form_string;
		}

		$html_output      = $form_string;
		$instance_counter = isset( $form['id'] ) ? (int) $form['id'] : 0;

		$class_map = array(
			'column_start' => 'gfmc_row_start',
			'column_break' => 'gfmc_column_break',
			'column_end'   => 'gfmc_row_end',
		);

		foreach ( $form['fields'] as $form_field ) {
            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$field_type = isset( $form_field->type ) ? $form_field->type : '';
            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$css_class = isset( $form_field->cssClass ) ? $form_field->cssClass : '';

			if ( ! isset( $class_map[ $field_type ] ) ) {
				continue;
			}

            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$field_id     = "field_{$instance_counter}_{$form_field->id}";
			$target_class = esc_attr( trim( $class_map[ $field_type ] . ' ' . $css_class ) );

			$pattern     = '/<(?:li|div)[^>]*id=[\'"]' . preg_quote( $field_id, '/' ) . '[\'"][^>]*>(.*?)<\/(?:li|div)>/s';
			$replacement = "<li id=\"{$field_id}\" class=\"gfield {$target_class}\"></li>";

			$html_output = preg_replace( $pattern, $replacement, $html_output );
		}

		return $html_output;
	}
}
