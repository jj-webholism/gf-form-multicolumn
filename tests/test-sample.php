<?php
/**
 * Class Test_Form_Render
 *
 * @package Gf_Form_Multicolumn
 */

class Test_Form_Render extends WP_UnitTestCase {

    public function test_form_build_and_render() {
        // 1. Verify Gravity Forms successfully loaded in the test suite
        $this->assertTrue( class_exists( 'GFAPI' ), 'Gravity Forms API is not active.' );

        // 2. Programmatically define the form structure
        // Update the 'type' below to match your actual custom field types (e.g., 'column_start')
        $form_data = [
            'title'  => 'Automated Multicolumn Test',
            'fields' => [
                [
                    'id'    => 1,
                    'type'  => 'text', // TODO: Change to your custom start field type
                    'label' => 'Start Column',
                ],
                [
                    'id'    => 2,
                    'type'  => 'text',
                    'label' => 'First Name',
                ],
                [
                    'id'    => 3,
                    'type'  => 'text', // TODO: Change to your custom end field type
                    'label' => 'End Column',
                ],
            ],
        ];

        // 3. Insert the form into the temporary test database
        $form_id = \GFAPI::add_form( $form_data );

        // Assert the form was created successfully (should return an integer ID, not a WP_Error)
        $this->assertIsNumeric( $form_id, 'Form failed to create. GFAPI returned an error.' );

        // 4. Render the frontend HTML output as a string
        $html_output = gravity_form( $form_id, false, false, false, '', true, 12, false );

        // 5. Assert the expected output
        // Check that a standard Gravity Forms wrapper exists
        $this->assertStringContainsString( 'gform_wrapper', $html_output );

        // TODO: Assert your specific plugin HTML structure here!
        // Example: $this->assertStringContainsString( 'gf_multicolumn_wrapper', $html_output );
    }
}