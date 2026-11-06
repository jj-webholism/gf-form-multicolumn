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
	 * Formats raw form HTML into legacy <li> structural columns with nested <ul> containers.
	 *
	 * @param string               $form_string Form HTML markup.
	 * @param array<string, mixed> $form Gravity Forms configuration array.
	 * @return string Processed HTML markup.
	 */
	public function format( $form_string, array $form ) {
		if ( ! isset( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
			return $form_string;
		}

		$instance_counter = isset( $form['id'] ) ? (int) $form['id'] : 0;

		// Pre-calculate column divisor for each row block.
		$row_cols  = array();
		$row_idx   = 1;
		$col_count = 1;

		foreach ( $form['fields'] as $field ) {
			$type = isset( $field->type ) ? $field->type : '';
			if ( 'column_break' === $type ) {
				++$col_count;
			} elseif ( 'column_end' === $type ) {
				$row_cols[ $row_idx ] = $col_count;
				++$row_idx;
				$col_count = 1;
			}
		}

		$current_row = 1;
		$current_col = 0;
		$html_output = $form_string;

		foreach ( $form['fields'] as $form_field ) {
			$field_type = isset( $form_field->type ) ? $form_field->type : '';

			if ( ! in_array( $field_type, array( 'column_start', 'column_break', 'column_end' ), true ) ) {
				continue;
			}

            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$field_id = "field_{$instance_counter}_{$form_field->id}";
            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$css_class = ! empty( $form_field->cssClass ) ? ' ' . esc_attr( trim( $form_field->cssClass ) ) : '';

			$divisor          = isset( $row_cols[ $current_row ] ) ? $row_cols[ $current_row ] : 1;
			$width_percentage = (int) floor( 100 / $divisor );

			$replacement = '';

			if ( 'column_start' === $field_type ) {
				++$current_col;
				$replacement = sprintf(
					'<li class="gfmc-column gfmc-column%1$d gfmc-row-%2$d-column gfmc-row-%2$d-col-%3$d-of-%4$d%5$s"><ul class="flex-wrapper flex-wrapper-%4$d">',
					(int) $width_percentage,
					(int) $current_row,
					(int) $current_col,
					(int) $divisor,
					$css_class
				);
			} elseif ( 'column_break' === $field_type ) {
				++$current_col;
				$replacement = sprintf(
					'</ul></li><li class="gfmc-column gfmc-column%1$d gfmc-row-%2$d-column gfmc-row-%2$d-col-%3$d-of-%4$d%5$s"><ul class="flex-wrapper flex-wrapper-%4$d">',
					(int) $width_percentage,
					(int) $current_row,
					(int) $current_col,
					(int) $divisor,
					$css_class
				);
			} elseif ( 'column_end' === $field_type ) {
				$current_col = 0;
				++$current_row;
				$replacement = '</ul></li>';
			}

			$pattern     = '/<(?:li|div)[^>]*id=[\'"]' . preg_quote( $field_id, '/' ) . '[\'"][^>]*>(.*?)<\/(?:li|div)>/s';
			$html_output = preg_replace( $pattern, $replacement, $html_output );
		}

		return $html_output;
	}
}
