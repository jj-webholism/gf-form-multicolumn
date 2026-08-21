<?php
/**
 * Column End field class.
 *
 * @package WH\GF\Multicolumn\Admin\Field
 */

namespace WH\GF\Multicolumn\Admin\Field;

use GF_Field;

if ( ! class_exists( '\GF_Field' ) ) {
	return;
}

/**
 * Custom field for ending a column group.
 */
class Column_End extends GF_Field {

	/**
	 * Field type identifier.
	 *
	 * @var string
	 */
	public $type = 'column_end';

	/**
	 * Returns the field title for display in the form editor.
	 *
	 * @return string Field title.
	 */
	public function get_form_editor_field_title() {
		return esc_html__( 'Row End', 'gf-form-multicolumn' );
	}
}
