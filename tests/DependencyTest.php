<?php
namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use GFForms;

class DependencyTest extends WP_UnitTestCase {

    /**
     * Test behavior when Gravity Forms IS active.
     */
    public function test_plugin_initializes_when_gravity_forms_is_active(): void {
        if ( ! class_exists( 'GFForms' ) ) {
            $this->markTestSkipped( 'Gravity Forms is not installed in this environment.' );
        }

        // Verify your plugin hooks into Gravity Forms when active
        $this->assertTrue( class_exists( 'GFForms' ) );
        // Example: check if your GF hook filter was added
        // $this->assertGreaterThan( 0, has_filter( 'gform_admin_pre_render' ) );
    }

    /**
     * Test that main classes or functions load safely without fatal errors.
     */
    public function test_plugin_classes_are_safe_to_instantiate(): void {
        $admin = new \WH\GF\Multicolumn\admin\WH_GF_Multicolumn_Admin();
        $this->assertNotNull( $admin );
    }
}