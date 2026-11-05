<?php
/**
 * Test frontend asset enqueueing based on form settings.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Frontend;

class AssetEnqueueTest extends WP_UnitTestCase {

    /**
     * Frontend instance.
     *
     * @var Frontend
     */
    private $frontend;

    /**
     * Set up test environment before each run.
     */
    public function set_up(): void {
        parent::set_up();

        $GLOBALS['wp_scripts'] = new \WP_Scripts();
        $GLOBALS['wp_styles']  = new \WP_Styles();

        // Register handles matching includes/class-plugin.php
        wp_register_style( 'gfmc_styles', 'https://example.com/gf-form-multicolumn.css' );
        wp_register_script( 'gfmc_scripts_public', 'https://example.com/gf-form-multicolumn.js', array(), '4.0.9', true );

        $this->frontend = new Frontend( 'gf-form-multicolumn', '4.0.9' );
    }

    /**
     * Test CSS remains enqueued and JS is dequeued under default form settings.
     */
    public function test_assets_enqueued_with_default_settings(): void {
        // GFAddOn enqueues both when column fields exist:
        wp_enqueue_style( 'gfmc_styles' );
        wp_enqueue_script( 'gfmc_scripts_public' );

        $form = array(
            'id'            => 1,
            'gfmcEnableCSS' => 1,
            'gfmcEnableJS'  => 0,
        );

        $this->frontend->dequeue_selected_scripts( $form, false );

        $this->assertTrue( wp_style_is( 'gfmc_styles', 'enqueued' ), 'CSS should remain enqueued when gfmcEnableCSS is 1.' );
        $this->assertFalse( wp_script_is( 'gfmc_scripts_public', 'enqueued' ), 'JS should be dequeued when gfmcEnableJS is 0.' );
    }

    /**
     * Test CSS is dequeued when disabled and JS is kept when enabled.
     */
    public function test_css_suppressed_and_js_enqueued_when_toggled(): void {
        wp_enqueue_style( 'gfmc_styles' );
        wp_enqueue_script( 'gfmc_scripts_public' );

        $form = array(
            'id'            => 2,
            'gfmcEnableCSS' => 0,
            'gfmcEnableJS'  => 1,
        );

        $this->frontend->dequeue_selected_scripts( $form, false );

        $this->assertFalse( wp_style_is( 'gfmc_styles', 'enqueued' ), 'CSS should be dequeued when gfmcEnableCSS is 0.' );
        $this->assertTrue( wp_script_is( 'gfmc_scripts_public', 'enqueued' ), 'JS should remain enqueued when gfmcEnableJS is 1.' );
    }

    /**
     * Test both assets are dequeued when both settings are disabled.
     */
    public function test_all_assets_suppressed_when_both_disabled(): void {
        wp_enqueue_style( 'gfmc_styles' );
        wp_enqueue_script( 'gfmc_scripts_public' );

        $form = array(
            'id'            => 3,
            'gfmcEnableCSS' => 0,
            'gfmcEnableJS'  => 0,
        );

        $this->frontend->dequeue_selected_scripts( $form, false );

        $this->assertFalse( wp_style_is( 'gfmc_styles', 'enqueued' ) );
        $this->assertFalse( wp_script_is( 'gfmc_scripts_public', 'enqueued' ) );
    }
}