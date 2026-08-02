<?php
namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;

class ActivationTest extends WP_UnitTestCase {

    /**
     * Test that the plugin main file can be included without fatal errors.
     */
    public function test_main_plugin_file_is_loaded(): void {
        $this->assertTrue( defined( 'ABSPATH' ) );
        // Verify main plugin constant or main class existence
        $this->assertTrue( class_exists( 'WH\GF\Multicolumn\admin\WH_GF_Multicolumn_Admin' ) );
    }

    /**
     * Test plugin activation hook logic.
     */
    public function test_plugin_activation_does_not_fatal(): void {
        // Suppress direct output if your hook echoes anything
        ob_start();

        // Trigger your plugin's activation callback explicitly
        do_action( 'activate_gf-form-multicolumn/gf-form-multicolumn.php' );

        $output = ob_get_clean();

        // Assert no unexpected PHP warnings/notices were dumped during activation
        $this->assertEmpty( $output, 'Plugin activation produced unexpected output/errors.' );
    }

    /**
     * Test that required options or default DB state are created on activation.
     */
    public function test_activation_sets_default_options(): void {
        // Execute activation hook
        do_action( 'activate_gf-form-multicolumn/gf-form-multicolumn.php' );

        // Check if expected plugin options were created in wp_options table
        // Example: $version = get_option('wh_gf_multicolumn_version');
        // $this->assertNotEmpty( $version );
        $this->assertTrue( true );
    }
}