<?php
/**
 * Frontend display functionality of the plugin.
 *
 * @package WH\GF\Multicolumn\Frontend
 */

namespace WH\GF\Multicolumn\Frontend;

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
	 * Initialize class properties.
	 *
	 * @param string $plugin_name Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
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
	}

	/**
	 * Processes form output to inject column markup.
	 *
	 * @param string $form_string HTML string of form.
	 * @param array  $form        Gravity Forms form object.
	 * @return string Processed form markup.
	 */
	public function process_form( $form_string, $form ) {
		if ( is_admin() ) {
			return $form_string;
		}

		$current_form = new Form_Current( $form_string, $form );
		return $current_form->get_form();
	}

	/**
	 * Dequeues unnecessary scripts on non-form pages.
	 *
	 * @return void
	 */
	public function dequeue_selected_scripts() {
		if ( is_admin() ) {
			wp_dequeue_script( 'gfmc_scripts_public' );
		}
	}
}
