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
	 * Returns the field title for display in the form editor.
	 *
	 * @return string Field title.
	 */
	public function get_form_editor_field_title() {
		return esc_html__( 'Row Start', 'gf-form-multicolumn' );
	}
}
