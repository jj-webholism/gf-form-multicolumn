<?php

namespace WH\GF\Multicolumn\Admin\Field;

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

use GF_Field;
use GF_Fields;

define( 'WH_GF_MULTICOLUMN_FIELD_GROUP_TITLE', 'Multiple Columns Fields' );

class WH_GF_Multicolumn_Field_Group extends
	GF_Field {
	public $type = 'multicolumn_group';

	private $buttonLabel = 'Multiple Columns Fields';
	private $button_group = GF_MULTICOLUMN_FIELD_GROUP_TITLE;

	/*
	 * Prevent adding a separate button for this field group
	 */
	public function get_form_editor_button() {
		return null;
	}

	public function add_button( $field_groups ) {
		$field_groups = $this->add_custom_field_group( $field_groups );

		return parent::add_button( $field_groups );
	}

	public function add_custom_field_group( $field_groups ) {
		foreach ( $field_groups as $field_group ) {
			if ( $field_group['name'] === $this->button_group ) {
				return $field_groups;
			}
		}

		$field_groups[] = [
			'name'   => $this->button_group,
			'label'  => __( $this->buttonLabel, 'gf-form-multicolumn' ),
			'fields' => [],
		];

		return $field_groups;
	}
}

try {
	GF_Fields::register( new WH_GF_Multicolumn_Field_Group() );
}
catch ( \Exception $e ) {
}
