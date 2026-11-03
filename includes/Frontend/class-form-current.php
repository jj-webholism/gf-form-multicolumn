<?php
/**
 * Current Form Handler Class.
 *
 * @package WH\GF\Multicolumn\Frontend
 */

namespace WH\GF\Multicolumn\Frontend;

use WH\GF\Multicolumn\Frontend\Formatters\Formatter_Interface;
use WH\GF\Multicolumn\Frontend\Formatters\Grid_Formatter;
use WH\GF\Multicolumn\Frontend\Formatters\Legacy_Formatter;

/**
 * Resolves configuration and delegates formatting to the appropriate strategy.
 */
class Form_Current {

	/**
	 * Output HTML string.
	 *
	 * @var string
	 */
	private $form_string = '';

	/**
	 * Form configuration array.
	 *
	 * @var array
	 */
	private $form = array();

	/**
	 * Initialize class properties.
	 *
	 * @param string $form_string Form HTML output.
	 * @param array  $form        Gravity Forms configuration array.
	 */
	public function __construct( $form_string = '', $form = array() ) {
		$this->form_string = $form_string;
		$this->form        = $form;
	}

	/**
	 * Returns the processed form HTML.
	 *
	 * @param string|null $override_engine Optional manual engine override ('grid' or 'legacy_list').
	 * @return string Processed HTML.
	 */
	public function get_form( $override_engine = null ) {
		$formatter = $this->resolve_formatter( $override_engine );
		return $formatter->format( $this->form_string, $this->form );
	}

	/**
	 * Resolves which formatter strategy to instantiate.
	 *
	 * @param string|null $override_engine Optional manual engine override.
	 * @return Formatter_Interface
	 */
	private function resolve_formatter( $override_engine = null ) {
		$engine = $override_engine;

		if ( empty( $engine ) ) {
			// Check form settings; default to modern 'grid'.
			$engine = isset( $this->form['gfmc_layout_engine'] ) ? $this->form['gfmc_layout_engine'] : 'grid';
		}

		if ( 'legacy_list' === $engine || 'li' === $engine ) {
			return new Legacy_Formatter();
		}

		return new Grid_Formatter();
	}
}
