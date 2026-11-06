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
	 * @param array  $form Gravity Forms configuration array.
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
	 * Resolves the multicolumn layout engine and CSS requirement for a given form.
	 *
	 * @param array<string, mixed> $form Form configuration array.
	 * @return array{engine: string, enqueue_css: bool}
	 */
	public static function resolve_mode( array $form ): array {
		if ( ! empty( $form['gfmc_layout_css'] ) ) {
			switch ( (string) $form['gfmc_layout_css'] ) {
				case 'grid_none':
					return array(
						'engine'      => 'grid',
						'enqueue_css' => false,
					);
				case 'legacy':
					return array(
						'engine'      => 'legacy',
						'enqueue_css' => true,
					);
				case 'legacy_none':
					return array(
						'engine'      => 'legacy',
						'enqueue_css' => false,
					);
				case 'grid':
				default:
					return array(
						'engine'      => 'grid',
						'enqueue_css' => true,
					);
			}
		}

		// Backward compatibility for existing forms saved with legacy key.
		if ( array_key_exists( 'gfmcEnableCSS', $form ) ) {
			return array(
				'engine'      => 'legacy',
				'enqueue_css' => (bool) $form['gfmcEnableCSS'],
			);
		}

		// Backward compatibility for forms with only gfmc_layout_engine set.
		if ( ! empty( $form['gfmc_layout_engine'] ) ) {
			$engine = ( 'legacy_list' === $form['gfmc_layout_engine'] || 'legacy' === $form['gfmc_layout_engine'] ) ? 'legacy' : 'grid';
			return array(
				'engine'      => $engine,
				'enqueue_css' => true,
			);
		}

		// Brand new form default.
		return array(
			'engine'      => 'grid',
			'enqueue_css' => true,
		);
	}

	/**
	 * Resolves which formatter strategy to instantiate.
	 *
	 * @param string|null $override_engine Optional manual engine override.
	 * @return Formatter_Interface
	 */
	private function resolve_formatter( $override_engine = null ): Formatter_Interface {
		if ( ! empty( $override_engine ) ) {
			$engine = $override_engine;
		} else {
			$mode   = self::resolve_mode( $this->form );
			$engine = $mode['engine'];
		}

		if ( 'legacy' === $engine || 'legacy_list' === $engine ) {
			return new Legacy_Formatter();
		}

		return new Grid_Formatter();
	}
}
