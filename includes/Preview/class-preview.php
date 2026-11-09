<?php
/**
 * Preview lifecycle handler for Gravity Forms.
 *
 * @package WH\GF\Multicolumn\Preview
 */

namespace WH\GF\Multicolumn\Preview;

use WH\GF\Multicolumn\Frontend\Form_Current;

/**
 * Manages form formatting and asset injection during Gravity Forms preview.
 */
class Preview {



	/**
	 * Plugin slug identifier.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize preview handler.
	 *
	 * @param string $plugin_name Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Gets the plugin slug identifier.
	 *
	 * @return string
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * Gets current plugin version string.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Registers preview-specific hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'gform_preview_init', array( $this, 'register_preview_assets' ), 10, 1 );
		add_filter( 'gform_preview_styles', array( $this, 'filter_preview_styles' ), 10, 2 );
		add_filter( 'gform_get_form_filter', array( $this, 'process_preview_form' ), 20, 2 );
	}

	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter

	/**
	 * Registers the plugin stylesheet during the preview initialization lifecycle.
	 *
	 * @param int|null $form_id ID of the form being previewed.
	 * @return void
	 */
	public function register_preview_assets( $form_id = null ): void {
		// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$src    = plugins_url( "assets/css/gf-form-multicolumn{$suffix}.css", dirname( __DIR__, 2 ) . '/gf-form-multicolumn.php' );

		if ( ! wp_style_is( $this->plugin_name, 'registered' ) ) {
			wp_register_style(
				$this->plugin_name,
				$src,
				array(),
				$this->version
			);
		}
	}

	/**
	 * Adds the multi-column stylesheet handle to Gravity Forms preview styles if enabled.
	 *
	 * @param array<int, string>   $styles Array of style handles to load in preview header.
	 * @param array<string, mixed> $form Current form configuration array.
	 * @return array<int, string> Filtered style handles.
	 */
	public function filter_preview_styles( $styles, $form = array() ): array {
		if ( ! is_array( $styles ) ) {
			$styles = array();
		}

		if ( empty( $form ) || ! is_array( $form ) ) {
			return $styles;
		}

		$mode = Form_Current::resolve_mode( $form );

		if ( $mode['enqueue_css'] ) {
			$this->register_preview_assets( isset( $form['id'] ) ? (int) $form['id'] : null );
			$styles[] = $this->plugin_name;
		}

		return array_unique( $styles );
	}

	/**
	 * Processes form HTML during preview mode to apply structural grid or legacy markup.
	 *
	 * @param string               $form_string Raw form HTML markup.
	 * @param array<string, mixed> $form Gravity Forms configuration array.
	 * @return string Processed HTML markup.
	 */
	public function process_preview_form( string $form_string, array $form ): string {
		if ( ! $this->is_preview_request() ) {
			return $form_string;
		}

		$current_form = new Form_Current( $form_string, $form );
		return $current_form->get_form();
	}

	/**
	 * Determines if the current request is a Gravity Forms preview.
	 *
	 * @return bool
	 */
	private function is_preview_request(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['gf_page'] ) && 'preview' === $_GET['gf_page'];
	}
}
