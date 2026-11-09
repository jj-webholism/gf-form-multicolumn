<?php
/**
 * Column Start field class.
 *
 * @package WH\GF\Multicolumn\Admin\Field
 */

namespace WH\GF\Multicolumn\Admin\Field;

use GF_Field;

if ( ! class_exists( '\GF_Field' ) ) {
	return;
}

/**
 * Custom field for starting a column group.
 */
class Column_Start extends GF_Field {


	/**
	 * Field type identifier.
	 *
	 * @var string
	 */
	public $type = 'column_start';

	/**
	 * Returns the inline JavaScript executed when the field is added in the editor.
	 *
	 * @return string Inline JavaScript.
	 */
	public function get_form_editor_inline_script_on_page_render() {
		return sprintf(
			'function SetDefaultValues_%s(field) { field.label = %s; }',
			$this->type,
			wp_json_encode( $this->get_form_editor_field_title() )
		) . PHP_EOL;
	}

	/**
	 * Returns the field title for display in the form editor.
	 *
	 * @return string Field title.
	 */
	public function get_form_editor_field_title() {
		return esc_html__( 'Row Start', 'gf-form-multicolumn' );
	}

	/**
	 * Returns the field settings available in the form editor sidebar.
	 *
	 * @return array Available setting keys.
	 */
	public function get_form_editor_field_settings() {
		return array(
			'css_class_setting',
		);
	}

	/**
	 * Disables conditional logic for layout marker fields.
	 *
	 * @return bool
	 */
	public function is_conditional_logic_supported() {
		return false;
	}

	/**
	 * Returns the default field label.
	 *
	 * @param bool   $force_frontend_label Whether to force the frontend label.
	 * @param string $value Field value.
	 * @return string
	 */
	public function get_field_label( $force_frontend_label = true, $value = '' ) {
		return $this->get_form_editor_field_title();
	}

	/**
	 * Assigns the field button to the Multi-Column group.
	 *
	 * @return array Button specification.
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'multiple_columns_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}
}
