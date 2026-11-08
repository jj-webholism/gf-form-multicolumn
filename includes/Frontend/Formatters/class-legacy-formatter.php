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
		if ( empty( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
			return $form_string;
		}

		$instance_counter = isset( $form['id'] ) ? (int) $form['id'] : 0;
		$calculator       = new Layout_Calculator();
		$layout_map       = $calculator->calculate( $form );

		$form_id     = isset( $form['id'] ) ? (int) $form['id'] : 0;
		$current_row = 1;
		$current_col = 0;
		$html_output = $form_string;

		foreach ( $form['fields'] as $form_field ) {
			$field_type = $this->get_field_type( $form_field );

			if ( ! in_array( $field_type, array( 'column_start', 'column_break', 'column_end' ), true ) ) {
				continue;
			}

			$field_id  = 'field_' . $form_id . '_' . $this->get_field_id( $form_field );
			$css_class = $this->get_field_css_class( $form_field );

			$divisor          = isset( $layout_map['rows'][ $current_row ]['columns'] ) ? $layout_map['rows'][ $current_row ]['columns'] : 1;
			$width_percentage = (int) floor( 100 / $divisor );

			$replacement = '';

			if ( 'column_start' === $field_type ) {
				++$current_col;
				$replacement = sprintf(
					'<li class="gfmc-column gfmc-column%2$d gfmc-row-%3$d-column gfmc-row-%3$d-col-%4$d-of-%5$d gfmc-form-%1$d-row-%3$d-col-%4$d%6$s" data-gfmc-form="%1$d" data-gfmc-row="%3$d" data-gfmc-col="%4$d"><ul class="flex-wrapper flex-wrapper-%5$d">',
					(int) $form_id,
					(int) $width_percentage,
					(int) $current_row,
					(int) $current_col,
					(int) $divisor,
					$css_class
				);
			} elseif ( 'column_break' === $field_type ) {
				++$current_col;
				$replacement = sprintf(
					'</ul></li><li class="gfmc-column gfmc-column%2$d gfmc-row-%3$d-column gfmc-row-%3$d-col-%4$d-of-%5$d gfmc-form-%1$d-row-%3$d-col-%4$d%6$s" data-gfmc-form="%1$d" data-gfmc-row="%3$d" data-gfmc-col="%4$d"><ul class="flex-wrapper flex-wrapper-%5$d">',
					(int) $form_id,
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
			$html_output = (string) preg_replace( $pattern, $replacement, $html_output );
		}

		return $html_output;
	}

	/**
	 * Safely extracts field type from object or array representations.
	 *
	 * @param mixed $field Field object or array.
	 * @return string
	 */
	private function get_field_type( $field ): string {
		if ( is_object( $field ) ) {
			if ( method_exists( $field, 'get_input_type' ) ) {
				$type = (string) $field->get_input_type();
				if ( '' !== $type ) {
					return $type;
				}
			}
			if ( ! empty( $field->type ) ) {
				return (string) $field->type;
			}
		}

		if ( ( is_array( $field ) || $field instanceof \ArrayAccess ) && ! empty( $field['type'] ) ) {
			return (string) $field['type'];
		}

		return '';
	}

	/**
	 * Safely extracts field ID from object or array representations.
	 *
	 * @param mixed $field Field object or array.
	 * @return string
	 */
	private function get_field_id( $field ): string {
		if ( is_object( $field ) ) {
			if ( ! empty( $field->id ) ) {
				return (string) $field->id;
			}
		}

		if ( ( is_array( $field ) || $field instanceof \ArrayAccess ) && ! empty( $field['id'] ) ) {
			return (string) $field['id'];
		}

		return '';
	}

	/**
	 * Safely extracts sanitized CSS class string from object or array representations.
	 *
	 * @param mixed $field Field object or array.
	 * @return string
	 */
	private function get_field_css_class( $field ): string {
		$class = '';

		if ( is_object( $field ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$class = ! empty( $field->cssClass ) ? (string) $field->cssClass : '';
		} elseif ( is_array( $field ) ) {
			$class = ! empty( $field['cssClass'] ) ? (string) $field['cssClass'] : '';
		}

		return '' !== $class ? ' ' . esc_attr( trim( $class ) ) : '';
	}
}
