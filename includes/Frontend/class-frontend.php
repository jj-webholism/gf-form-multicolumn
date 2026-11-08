<?php
/**
 * Frontend display functionality of the plugin.
 *
 * @package WH\GF\Multicolumn\Frontend
 */

namespace WH\GF\Multicolumn\Frontend;

use WH\GF\Multicolumn\Frontend\Form_Current;

/**
 * Handles frontend hooks, script dequeuing, and form manipulation.
 */
class Frontend {



	/**
	 * Plugin slug identifier.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Current version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize class properties and attach hooks.
	 *
	 * @param string $plugin_name Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_filter( 'gform_get_form_filter', array( $this, 'process_form' ), 10, 2 );
		add_filter( 'gform_enable_legacy_markup', array( $this, 'filter_legacy_markup' ), 10, 2 );
	}

	/**
	 * Get the plugin slug.
	 *
	 * @return string
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Attaches frontend form processing filters.
	 *
	 * @return void
	 */
	public function display() {
		add_filter( 'gform_get_form_filter', array( $this, 'process_form' ), 10, 2 );
		add_filter( 'gform_enable_legacy_markup', array( $this, 'filter_legacy_markup' ), 10, 2 );
	}

	/**
	 * Processes form output to inject column markup.
	 *
	 * @param string $form_string HTML string of form.
	 * @param array  $form Gravity Forms form object.
	 * @return string Processed form markup.
	 */
	public function process_form( $form_string, $form ) {
		if ( is_admin() ) {
			return $form_string;
		}

		$current_form = new Form_Current( $form_string, $form );
		return $current_form->get_form();
	}

	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter

	/**
	 * Dequeues stylesheets and scripts based on per-form settings.
	 *
	 * @param array<string, mixed> $form The current form object.
	 * @param bool                 $is_ajax Whether the form is submitted via AJAX.
	 * @return void
	 */
	public function dequeue_selected_scripts( $form = array(), $is_ajax = false ): void {
		// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter
		if ( is_admin() ) {
			wp_dequeue_script( 'gfmc_scripts_public' );
			return;
		}

		if ( empty( $form ) || ! is_array( $form ) ) {
			return;
		}

		$mode = Form_Current::resolve_mode( $form );

		// Dequeue stylesheet if either 'none' mode is selected or legacy CSS was disabled.
		if ( ! $mode['enqueue_css'] ) {
			wp_dequeue_style( 'gf-form-multicolumn' );
		}

		// JS helper defaults to disabled (0) unless explicitly enabled (1).
		$enable_js = ! empty( $form['gfmcEnableJS'] );
		if ( ! $enable_js ) {
			wp_dequeue_script( 'gf-form-multicolumn-public' );
		}
	}

	/**
	 * Filters whether legacy markup should be enabled for a given form.
	 *
	 * @param bool  $is_legacy Current legacy status.
	 * @param mixed $form Form configuration array, object, ID, or null.
	 * @return bool
	 */
	public function filter_legacy_markup( $is_legacy, $form = null ): bool {
		if ( empty( $form ) ) {
			return (bool) $is_legacy;
		}

		// Handle Form ID passed as integer or numeric string.
		if ( is_numeric( $form ) && class_exists( '\GFAPI' ) ) {
			$form = \GFAPI::get_form( (int) $form );
		} elseif ( is_object( $form ) ) {
			$form = (array) $form;
		}

		if ( empty( $form ) || ! is_array( $form ) ) {
			return (bool) $is_legacy;
		}

		$mode = Form_Current::resolve_mode( $form );

		return 'legacy' === $mode['engine'];
	}
}
