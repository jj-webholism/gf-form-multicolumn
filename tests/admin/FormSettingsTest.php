<?php

/**
 * Test Gravity Forms backend admin settings registration.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use GFForms;
use WH\GF\Multicolumn\Admin\Admin;

class FormSettingsTest extends WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);
        set_current_screen('admin.php');

        // Hook Admin filters into WordPress for the test run:
        new Admin();
    }

    /**
     * Test that form settings include the unified layout/CSS dropdown and JS toggle.
     */
    public function test_form_settings_include_layout_engine_and_asset_controls(): void {
        $admin    = new Admin( 'gf-form-multicolumn', '4.0.9' );
        $form     = array( 'id' => 1 );
        $settings = $admin->register_settings_fields( array(), $form );

        $this->assertArrayHasKey( 'gfmc_settings', $settings );
        $fields = $settings['gfmc_settings']['fields'];

        // Index fields by name for clean assertion
        $field_names = array_column( $fields, 'name' );
        $this->assertContains( 'gfmc_layout_css', $field_names, 'gfmc_layout_css select is missing from form settings.' );
        $this->assertContains( 'gfmcEnableJS', $field_names, 'gfmcEnableJS checkbox is missing from form settings.' );

        $fields_by_name = array_combine( $field_names, $fields );

        // Validate gfmc_layout_css dropdown configuration
        $layout_css_field = $fields_by_name['gfmc_layout_css'];
        $this->assertSame( 'select', $layout_css_field['type'] );
        $this->assertSame( 'grid', $layout_css_field['default_value'] );

        $choice_values = array_column( $layout_css_field['choices'], 'value' );
        $this->assertSame( array( 'grid', 'grid_none', 'legacy', 'legacy_none' ), $choice_values );

        // Validate gfmcEnableJS checkbox configuration
        $js_field = $fields_by_name['gfmcEnableJS'];
        $this->assertSame( 'checkbox', $js_field['type'] );
    }

    /**
     * Test that custom field types (column_start, column_break, column_end) are registered in the editor field palette.
     */
    public function test_column_field_types_are_registered_in_field_groups()
    {
        $field_groups = apply_filters('gform_add_field_buttons', array());

        $registered_types = array();
        foreach ($field_groups as $group) {
            if (isset($group['fields']) && is_array($group['fields'])) {
                foreach ($group['fields'] as $field_button) {
                    $registered_types[] = $field_button['data-type'] ?? '';
                }
            }
        }

        $this->assertContains('column_start', $registered_types, 'column_start button is not registered in the editor palette');
        $this->assertContains('column_break', $registered_types, 'column_break button is not registered in the editor palette');
        $this->assertContains('column_end', $registered_types, 'column_end button is not registered in the editor palette');
    }
}