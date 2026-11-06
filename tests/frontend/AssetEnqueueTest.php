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

        wp_register_style( 'gf-form-multicolumn', 'https://example.com/gf-form-multicolumn.css' );
        wp_register_script( 'gf-form-multicolumn-public', 'https://example.com/gf-form-multicolumn.js', array(), '4.0.9', true );

        $this->frontend = new Frontend( 'gf-form-multicolumn', '4.0.9' );
    }

    /**
     * Test CSS remains enqueued for 'grid' mode.
     */
    public function test_css_enqueued_for_grid_mode(): void {
        wp_enqueue_style( 'gf-form-multicolumn' );

        $form = array(
            'id'              => 1,
            'gfmc_layout_css' => 'grid',
        );

        $this->frontend->dequeue_selected_scripts( $form, false );

        $this->assertTrue( wp_style_is( 'gf-form-multicolumn', 'enqueued' ) );
    }

    /**
     * Test CSS is dequeued for 'grid_none' mode.
     */
    public function test_css_suppressed_for_grid_none_mode(): void {
        wp_enqueue_style( 'gf-form-multicolumn' );

        $form = array(
            'id'              => 2,
            'gfmc_layout_css' => 'grid_none',
        );

        $this->frontend->dequeue_selected_scripts( $form, false );

        $this->assertFalse( wp_style_is( 'gf-form-multicolumn', 'enqueued' ) );
    }

    /**
     * Test CSS is dequeued for 'legacy_none' mode.
     */
    public function test_css_suppressed_for_legacy_none_mode(): void {
        wp_enqueue_style( 'gf-form-multicolumn' );

        $form = array(
            'id'              => 3,
            'gfmc_layout_css' => 'legacy_none',
        );

        $this->frontend->dequeue_selected_scripts( $form, false );

        $this->assertFalse( wp_style_is( 'gf-form-multicolumn', 'enqueued' ) );
    }

    /**
     * Test backward compatibility with legacy keys (gfmcEnableCSS = 0).
     */
    public function test_css_suppressed_with_legacy_disabled_key(): void {
        wp_enqueue_style( 'gf-form-multicolumn' );

        $form = array(
            'id'            => 4,
            'gfmcEnableCSS' => 0,
        );

        $this->frontend->dequeue_selected_scripts( $form, false );

        $this->assertFalse( wp_style_is( 'gf-form-multicolumn', 'enqueued' ) );
    }

    /**
     * Test that filter_legacy_markup returns true for legacy mode and false for grid mode.
     */
    public function test_filter_legacy_markup_returns_correct_flag(): void {
        $legacy_form = array(
            'id'              => 6,
            'gfmc_layout_css' => 'legacy',
        );
        $grid_form = array(
            'id'              => 7,
            'gfmc_layout_css' => 'grid',
        );

        $this->assertTrue( $this->frontend->filter_legacy_markup( false, $legacy_form ) );
        $this->assertFalse( $this->frontend->filter_legacy_markup( false, $grid_form ) );
    }
}