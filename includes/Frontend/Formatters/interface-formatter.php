<?php
/**
 * Formatter Interface.
 *
 * @package WH\GF\Multicolumn\Frontend\Formatters
 */

namespace WH\GF\Multicolumn\Frontend\Formatters;

/**
 * Interface Formatter_Interface
 */
interface Formatter_Interface {


	/**
	 * Formats raw form HTML into multi-column layout structure.
	 *
	 * @param string $form_string Form HTML markup.
	 * @param array  $form Gravity Forms configuration array.
	 * @return string Processed HTML markup.
	 */
	public function format( $form_string, array $form );
}
