<?php
/**
 * Shared Layout Calculator for Legacy and Grid Formatters.
 *
 * @package WH\GF\Multicolumn\Frontend\Formatters
 */

namespace WH\GF\Multicolumn\Frontend\Formatters;

/**
 * Parses a Gravity Forms configuration array to map row and column counts.
 */
class Layout_Calculator {



	/**
	 * Calculates the column and row structure from a form's marker fields.
	 *
	 * @param array<string, mixed> $form Gravity Forms configuration array.
	 * @return array<string, mixed> Layout map containing row divisions.
	 */
	public function calculate( array $form ): array {
		$layout_map = array( 'rows' => array() );

		if ( empty( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
			return $layout_map;
		}

		$row_idx   = 1;
		$col_count = 0;

		foreach ( $form['fields'] as $field ) {
			$type = $this->get_field_type( $field );

			if ( 'column_start' === $type ) {
				$col_count = 1;
			} elseif ( 'column_break' === $type ) {
				++$col_count;
			} elseif ( 'column_end' === $type ) {
				$layout_map['rows'][ $row_idx ] = array(
					'columns' => max( 1, $col_count ),
				);
				++$row_idx;
				$col_count = 0;
			}
		}

		return $layout_map;
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
}
