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
     * Test that the plugin registers custom settings fields into Gravity Forms form settings.
     */
    public function test_form_settings_include_layout_engine_and_asset_controls()
    {
        $initial_fields = array();
        $form = array('id' => 1);

        // 1. Trigger the Gravity Forms settings filter
        $settings_fields = apply_filters('gform_form_settings_fields', $initial_fields, $form);

        // 2. Find the Multi-Column settings section or fields
        $flattened_fields = array();
        foreach ($settings_fields as $section) {
            if (isset($section['fields']) && is_array($section['fields'])) {
                foreach ($section['fields'] as $field) {
                    $flattened_fields[$field['name'] ?? ''] = $field;
                }
            }
        }

        // 3. Assert your CSS and JS checkboxes and layout engine settings are registered
        $this->assertArrayHasKey('gfmcEnableCSS', $flattened_fields, 'gfmcEnableCSS checkbox is missing from form settings');
        $this->assertArrayHasKey('gfmcEnableJS', $flattened_fields, 'gfmcEnableJS checkbox is missing from form settings');
        $this->assertArrayHasKey('gfmc_layout_engine', $flattened_fields, 'gfmc_layout_engine selector is missing from form settings');
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