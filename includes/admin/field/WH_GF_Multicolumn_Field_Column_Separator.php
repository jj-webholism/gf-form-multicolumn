<?php

namespace WH\GF\Multicolumn\Admin\Field;

if ( ! class_exists( 'GFForms' ) )
{
	die();
}

use GF_Field;
use GF_Fields;

class WH_GF_Multicolumn_Field_Column_Separator extends
	GF_Field {
	public $type = 'column_break';

	public function get_form_editor_inline_script_on_page_render() {
		$script = sprintf( "function SetDefaultValues_%s(field) {field.label = '%s';}",
		                   $this->type,
		                   esc_attr__( $this->get_form_editor_field_title(),
		                               'gf-form-multicolumn' ) ) . PHP_EOL;

		return $script;
	}

	public function get_form_editor_field_title() {
		return esc_attr__( 'Column Break', 'gf-form-multicolumn' );
	}

	public function get_form_editor_field_settings() {
		return [
			'css_class_setting',
		];
	}

	public function is_conditional_logic_supported() {
		return false;
	}

	public function get_field_label( $force_frontend_label, $value ) {
		return $this->get_form_editor_field_title();
	}

	public function get_form_editor_button() {
		return [
			'group' => GF_MULTICOLUMN_FIELD_GROUP_TITLE,
			'text'  => $this->get_form_editor_field_title(),
		];
	}
}

try
{
	GF_Fields::register( new WH_GF_Multicolumn_Field_Column_Separator() );
}
catch ( \Exception $e )
{
	// Debug this.
}
