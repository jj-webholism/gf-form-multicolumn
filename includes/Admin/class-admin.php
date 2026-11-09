<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package WH\GF\Multicolumn\Admin
 */

namespace WH\GF\Multicolumn\Admin;

use GFForms;
use GFCommon;
use WH\GF\Multicolumn\Admin\Field\Column_End;
use WH\GF\Multicolumn\Admin\Field\Column_Start;
use WH\GF\Multicolumn\Admin\Field\Column_Separator;
use WH\GF\Multicolumn\Admin\Field\Group;
use WH\GF\Multicolumn\Frontend\Form_Current;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin page manipulation.
 */
class Admin {



	/**
	 * The ID of this plugin.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name = 'gf-form-multicolumn', $version = '4.0.9' ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->add_fields();
		$this->register_settings_hooks();
	}

	/**
	 * Get the plugin name.
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
	 * Add custom Gravity Forms fields.
	 *
	 * @return void
	 */
	private function add_fields(): void {
		if ( class_exists( '\GF_Fields' ) ) {
			new Group();
			new Column_Start();
			new Column_End();
			new Column_Separator();
		}
	}

	/**
	 * Register hooks for form settings and editor field buttons.
	 *
	 * @return void
	 */
	public function register_settings_hooks(): void {
		if ( $this->is_legacy_gf() ) {
			add_filter( 'gform_form_settings', array( $this, 'register_legacy_settings' ), 10, 2 );
			add_filter( 'gform_pre_form_settings_save', array( $this, 'save_legacy_settings' ), 10, 1 );
		} else {
			add_filter( 'gform_form_settings_fields', array( $this, 'register_settings_fields' ), 10, 2 );
		}

		// Modern GF 2.5+ editor group registration.
		add_filter( 'gform_field_groups_form_editor', array( $this, 'add_field_group' ), 10, 1 );

		// Editor field palette button registration & test compatibility.
		add_filter( 'gform_add_field_buttons', array( $this, 'add_field_buttons' ), 10, 1 );
	}

	/**
	 * Determine if running a pre-2.5 version of Gravity Forms.
	 * Defaults to false (modern) if version cannot be determined.
	 *
	 * @return bool
	 */
	private function is_legacy_gf(): bool {
		if ( class_exists( 'GFForms' ) && ! empty( GFForms::$version ) ) {
			return version_compare( GFForms::$version, '2.5', '<' );
		}
		if ( class_exists( 'GFCommon' ) && ! empty( GFCommon::$version ) ) {
			return version_compare( GFCommon::$version, '2.5', '<' );
		}

		return false;
	}

	/**
	 * Gets the form setting value, resolving legacy fallbacks when unset.
	 *
	 * @param array<string, mixed> $form Current form array.
	 * @return string Current layout mode ('grid', 'grid_none', 'legacy', 'legacy_none').
	 */
	public function get_layout_css_setting( array $form ): string {
		$mode = Form_Current::resolve_mode( $form );

		if ( 'legacy' === $mode['engine'] ) {
			return $mode['enqueue_css'] ? 'legacy' : 'legacy_none';
		}

		return $mode['enqueue_css'] ? 'grid' : 'grid_none';
	}

	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter

	/**
	 * Register Multi-Column settings into modern Gravity Forms (2.5 through 3.0+).
	 *
	 * @param array $fields Existing settings fields array.
	 * @param array $form Current form object.
	 * @return array Filtered settings fields.
	 */
	public function register_settings_fields( array $fields, array $form ): array {
		$current_value = $this->get_layout_css_setting( $form );

		$fields['gfmc_settings'] = array(
			'title'  => esc_html__( 'Multiple Columns Settings', 'gf-form-multicolumn' ),
			'fields' => array(
				array(
					'name'          => 'gfmc_layout_css',
					'label'         => esc_html__( 'Layout & Stylesheet', 'gf-form-multicolumn' ),
					'type'          => 'select',
					'value'         => $current_value,
					'default_value' => $current_value,
					'tooltip'       => esc_html__( 'Select whether columns use modern div grids or legacy list items, and whether to load the plugin stylesheet.', 'gf-form-multicolumn' ),
					'choices'       => array(
						array(
							'label' => esc_html__( 'Modern Grid (Load CSS)', 'gf-form-multicolumn' ),
							'value' => 'grid',
						),
						array(
							'label' => esc_html__( 'Modern Grid (No CSS - Custom Styling)', 'gf-form-multicolumn' ),
							'value' => 'grid_none',
						),
						array(
							'label' => esc_html__( 'Legacy List (Load Legacy CSS)', 'gf-form-multicolumn' ),
							'value' => 'legacy',
						),
						array(
							'label' => esc_html__( 'Legacy List (No CSS - Custom Styling)', 'gf-form-multicolumn' ),
							'value' => 'legacy_none',
						),
					),
				),
				array(
					'name'    => 'gfmcEnableJS',
					'label'   => esc_html__( 'Enable JS', 'gf-form-multicolumn' ),
					'type'    => 'checkbox',
					'choices' => array(
						array(
							'name'          => 'gfmcEnableJS',
							'label'         => esc_html__( 'Load spacing JavaScript helper', 'gf-form-multicolumn' ),
							'default_value' => 0,
						),
					),
				),
			),
		);

		return $fields;
	}

	/**
	 * Registers the Multiple Columns field group in the modern form editor sidebar.
	 *
	 * @param array $field_groups Existing field groups.
	 * @return array Modified field groups.
	 */
	public function add_field_group( array $field_groups ): array {
		foreach ( $field_groups as $group ) {
			if ( isset( $group['name'] ) && 'multiple_columns_fields' === $group['name'] ) {
				return $field_groups;
			}
		}

		$field_groups[] = array(
			'name'   => 'multiple_columns_fields',
			'label'  => esc_html__( 'Multiple Columns Fields', 'gf-form-multicolumn' ),
			'fields' => array(),
		);

		return $field_groups;
	}

	/**
	 * Add Multi-Column buttons to the Gravity Forms editor palette if not already present.
	 *
	 * @param array $field_groups Existing field groups.
	 * @return array Modified field groups.
	 */
	public function add_field_buttons( array $field_groups ): array {
		// Collect existing field data-types across all groups.
		$existing_types = array();
		foreach ( $field_groups as $group ) {
			if ( ! empty( $group['fields'] ) && is_array( $group['fields'] ) ) {
				foreach ( $group['fields'] as $field ) {
					if ( isset( $field['data-type'] ) ) {
						$existing_types[] = $field['data-type'];
					}
				}
			}
		}

		// Only append if not already registered by GF_Field classes.
		if ( ! in_array( 'column_start', $existing_types, true ) ) {
			// Find existing group if present, otherwise create it.
			$found = false;
			foreach ( $field_groups as &$group ) {
				if ( isset( $group['name'] ) && 'multiple_columns_fields' === $group['name'] ) {
					$group['fields'] = array_merge(
						$group['fields'] ?? array(),
						$this->get_field_button_definitions()
					);
					$found           = true;
					break;
				}
			}
			unset( $group );

			if ( ! $found ) {
				$field_groups[] = array(
					'name'   => 'multiple_columns_fields',
					'label'  => esc_html__( 'Multiple Columns Fields', 'gf-form-multicolumn' ),
					'fields' => $this->get_field_button_definitions(),
				);
			}
		}

		return $field_groups;
	}

	/**
	 * Get standard button definitions for the multi-column fields.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function get_field_button_definitions(): array {
		return array(
			array(
				'class'     => 'button',
				'value'     => esc_html__( 'Row Start', 'gf-form-multicolumn' ),
				'data-type' => 'column_start',
			),
			array(
				'class'     => 'button',
				'value'     => esc_html__( 'Column Break', 'gf-form-multicolumn' ),
				'data-type' => 'column_break',
			),
			array(
				'class'     => 'button',
				'value'     => esc_html__( 'Row End', 'gf-form-multicolumn' ),
				'data-type' => 'column_end',
			),
		);
	}

	/**
	 * Output raw table HTML settings for legacy Gravity Forms (< 2.5).
	 *
	 * @param array $settings Existing settings array.
	 * @param array $form Current form object.
	 * @return array
	 */
	public function register_legacy_settings( array $settings, array $form ): array {
		$enable_css = ! array_key_exists( 'gfmcEnableCSS', $form ) || (bool) $form['gfmcEnableCSS'];
		$enable_js  = ! empty( $form['gfmcEnableJS'] );

		$html  = '<tr>';
		$html .= '<th><label>' . esc_html__( 'Multiple Columns', 'gf-form-multicolumn' ) . '</label></th>';
		$html .= '<td>';
		$html .= '<label for="gfmcEnableCSS"><input type="checkbox" id="gfmcEnableCSS" name="gfmcEnableCSS" value="1" ' . checked( true, $enable_css, false ) . ' /> ' . esc_html__( 'Load CSS stylesheet', 'gf-form-multicolumn' ) . '</label><br />';
		$html .= '<label for="gfmcEnableJS"><input type="checkbox" id="gfmcEnableJS" name="gfmcEnableJS" value="1" ' . checked( true, $enable_js, false ) . ' /> ' . esc_html__( 'Load spacing JavaScript helper', 'gf-form-multicolumn' ) . '</label>';
		$html .= '</td>';
		$html .= '</tr>';

		$section = __( 'Form Options', 'gf-form-multicolumn' );
		if ( ! isset( $settings[ $section ] ) ) {
			$section = array_key_first( $settings ) ?? 'Multi-Column';
		}

		$settings[ $section ]['gfmc_settings'] = $html;

		return $settings;
	}

	/**
	 * Save settings for legacy Gravity Forms (< 2.5).
	 *
	 * @param array $form Current form array.
	 * @return array
	 */
	public function save_legacy_settings( array $form ): array {
		$form['gfmcEnableCSS'] = rgpost( 'gfmcEnableCSS' ) ? 1 : 0;
		$form['gfmcEnableJS']  = rgpost( 'gfmcEnableJS' ) ? 1 : 0;

		return $form;
	}
}
