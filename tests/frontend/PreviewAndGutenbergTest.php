<?php

/**
 * Tests for Preview lifecycle and Gutenberg block editor integrations.
 *
 * @package WH\GF\Multicolumn\Tests\Frontend
 */

namespace WH\GF\Multicolumn\Tests\Frontend;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Preview\Preview;
use WH\GF\Multicolumn\Plugin;

/**
 * Validates Preview handler hooks and block asset enqueueing.
 */
class PreviewAndGutenbergTest extends WP_UnitTestCase
{

    /**
     * Preview instance.
     *
     * @var Preview
     */
    private $preview;

    /**
     * Set up test environment before each test.
     */
    public function set_up(): void
    {
        parent::set_up();
        $this->preview = new Preview('gf-form-multicolumn', '4.0.9');
    }

    /**
     * Tear down test environment after each test.
     */
    public function tear_down(): void
    {
        unset($_GET['gf_page']);
        parent::tear_down();
    }

    /**
     * Verify that Preview::init() registers all required hooks.
     */
    public function test_preview_init_registers_hooks(): void
    {
        $this->preview->init();

        $this->assertNotFalse(has_action('gform_preview_init', array($this->preview, 'register_preview_assets')));
        $this->assertNotFalse(has_filter('gform_preview_styles', array($this->preview, 'filter_preview_styles')));
        $this->assertNotFalse(has_filter('gform_get_form_filter', array($this->preview, 'process_preview_form')));
    }

    /**
     * Verify that register_preview_assets registers the plugin stylesheet handle.
     */
    public function test_register_preview_assets(): void
    {
        wp_deregister_style('gf-form-multicolumn');
        $this->assertFalse(wp_style_is('gf-form-multicolumn', 'registered'));

        $this->preview->register_preview_assets();

        $this->assertTrue(wp_style_is('gf-form-multicolumn', 'registered'));
    }

    /**
     * Verify filter_preview_styles appends the stylesheet handle when CSS is enabled.
     */
    public function test_filter_preview_styles_adds_handle_when_enabled(): void
    {
        $form = array(
            'id' => 10,
            'gfmc_layout_css' => 'grid',
        );

        $styles = $this->preview->filter_preview_styles(array('other-style'), $form);

        $this->assertContains('gf-form-multicolumn', $styles);
        $this->assertContains('other-style', $styles);
    }

    /**
     * Verify filter_preview_styles does not add the stylesheet handle when CSS is disabled.
     */
    public function test_filter_preview_styles_omits_handle_when_disabled(): void
    {
        $form = array(
            'id' => 11,
            'gfmc_layout_css' => 'grid_none',
        );

        $styles = $this->preview->filter_preview_styles(array(), $form);

        $this->assertNotContains('gf-form-multicolumn', $styles);
    }

    /**
     * Verify process_preview_form only transforms output during preview requests.
     */
    public function test_process_preview_form_bypasses_non_preview_requests(): void
    {
        unset($_GET['gf_page']);
        $form_raw = '<div class="gform_body">Raw Form</div>';
        $form_obj = array('id' => 1);

        $output = $this->preview->process_preview_form($form_raw, $form_obj);
        $this->assertSame($form_raw, $output);
    }

    /**
     * Verify block editor asset callback enqueues the stylesheet in admin context.
     */
    public function test_block_editor_assets_enqueued_in_admin(): void
    {
        set_current_screen('post.php');
        $plugin = Plugin::get_instance();

        wp_dequeue_style('gf-form-multicolumn');
        $plugin->enqueue_block_editor_assets();

        $this->assertTrue(wp_style_is('gf-form-multicolumn', 'enqueued'));
    }
}