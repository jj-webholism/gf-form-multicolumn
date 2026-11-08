<?php
/**
 * Grid Formatter Class.
 *
 * @package WH\GF\Multicolumn\Frontend\Formatters
 */

namespace WH\GF\Multicolumn\Frontend\Formatters;

/**
 * Handles modern CSS Grid layouts by transforming legacy markers into grid wrappers.
 */
class Grid_Formatter implements Formatter_Interface {



	/**
	 * Formats raw form HTML into CSS grid row containers.
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
			$cols      = isset( $layout_map['rows'][ $current_row ]['columns'] ) ? $layout_map['rows'][ $current_row ]['columns'] : 1;

			$replacement = '';

			if ( 'column_start' === $field_type ) {
				++$current_col;
				$replacement = sprintf(
					'<div class="gfmc-row gfmc-row-%2$d gfmc-form-%1$d-row-%2$d" data-gfmc-form="%1$d" data-gfmc-row="%2$d" style="--gfmc-cols:%3$d;"><div class="gfmc-col gfmc-col-%4$d gfmc-form-%1$d-col-%4$d%5$s" data-gfmc-col="%4$d">',
					(int) $form_id,
					(int) $current_row,
					(int) $cols,
					(int) $current_col,
					$css_class
				);
			} elseif ( 'column_break' === $field_type ) {
				++$current_col;
				$replacement = sprintf(
					'</div><div class="gfmc-col gfmc-col-%2$d gfmc-form-%1$d-col-%2$d%3$s" data-gfmc-col="%2$d">',
					(int) $form_id,
					(int) $current_col,
					$css_class
				);
			} elseif ( 'column_end' === $field_type ) {
				$current_col = 0;
				++$current_row;
				$replacement = '</div></div>';
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
			return isset( $field->type ) ? (string) $field->type : '';
		}

		if ( is_array( $field ) && isset( $field['type'] ) ) {
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
		if ( is_object( $field ) && isset( $field->id ) ) {
			return (string) $field->id;
		}

		if ( is_array( $field ) && isset( $field['id'] ) ) {
			return (string) $field['id'];
		}

		return '';
	}

	/**
	 * Safely extracts custom CSS classes from field.
	 *
	 * @param mixed $field Field object or array.
	 * @return string
	 */
	private function get_field_css_class( $field ): string {
		$class = '';

		if ( is_object( $field ) && ! empty( $field->cssClass ) ) {
			$class = (string) $field->cssClass;
		} elseif ( is_array( $field ) && ! empty( $field['cssClass'] ) ) {
			$class = (string) $field['cssClass'];
		}

		return '' !== $class ? ' ' . esc_attr( trim( $class ) ) : '';
	}
}
