<?php
/**
 * Multi-column field group loader.
 *
 * @package WH\GF\Multicolumn\Admin\Field
 */

namespace WH\GF\Multicolumn\Admin\Field;

use GF_Fields;

/**
 * Registers multi-column field group and custom field controls.
 */
class Group {

	/**
	 * Initialize and register custom field types with Gravity Forms.
	 */
	public function __construct() {
		if ( class_exists( '\GF_Fields' ) ) {
			GF_Fields::register( new Column_Start() );
			GF_Fields::register( new Column_End() );
			GF_Fields::register( new Column_Separator() );
		}
	}
}
