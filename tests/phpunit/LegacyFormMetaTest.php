<?php
/**
 * Legacy Form Schema Regression Tests.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Frontend;
use WH\GF\Multicolumn\Preview\Preview;

/**
 * Class LegacyFormMetaTest
 */
class LegacyFormMetaTest extends WP_UnitTestCase
{

    /**
     * Frontend instance.
     *
     * @var Frontend
     */
    private $frontend;

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
        $this->frontend = new Frontend('gf-form-multicolumn', '4.0.9');
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
     * Verify that forms created under early plugin versions (missing all modern gfmc meta keys)
     * process through the frontend filter cleanly without triggering PHP warnings or notices.
     */
    public function test_legacy_form_missing_all_gfmc_meta_keys_renders_safely(): void
    {
        $raw_legacy_form = [
            'id' => 99,
            'title' => 'Historic Form (v1.0 schema)',
            'description' => 'Form created prior to modern grid engine settings.',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'column_start',
                    'label' => 'Row Start',
                ],
                [
                    'id' => 2,
                    'type' => 'text',
                    'label' => 'Old Text Field',
                ],
                [
                    'id' => 3,
                    'type' => 'column_break',
                    'label' => 'Column Break',
                ],
                [
                    'id' => 4,
                    'type' => 'text',
                    'label' => 'Second Column Field',
                ],
                [
                    'id' => 5,
                    'type' => 'column_end',
                    'label' => 'Row End',
                ],
            ],
            // Deliberately omit: markupVersion, gfmc_layout_css, gfmcEnableCSS,
            // gfmcEnableJS, gfmc_layout_engine, confirmations, and layoutGroupId.
        ];

        $mock_html = '<div class="gform_wrapper" id="gform_wrapper_99"><div class="gform_body"><div class="gform_fields"></div></div></div>';

        // Intercept PHP notices / warnings (e.g. undefined index) and fail the test
        set_error_handler(
            static function (int $errno, string $errstr, string $errfile, int $errline): bool {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );

        try {
            $output = $this->frontend->process_form($mock_html, $raw_legacy_form);
            $is_legacy = $this->frontend->filter_legacy_markup(false, $raw_legacy_form);
            restore_error_handler();
        } catch (\Throwable $e) {
            restore_error_handler();
            $this->fail('Legacy form triggered PHP Notice/Warning: ' . $e->getMessage());
        }

        $this->assertIsString($output);
        $this->assertIsBool($is_legacy);
    }

    /**
     * Verify that Gravity Forms markupVersion = 1 (Legacy <ul> / <li> engine)
     * and missing preview keys are handled gracefully by Preview and Frontend.
     */
    public function test_legacy_markup_version_one_compatibility(): void
    {
        $v1_form = [
            'id' => 100,
            'title' => 'GF Legacy Markup Version 1',
            'markupVersion' => 1, // Legacy <ul>/<li> engine
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'column_start',
                ],
                [
                    'id' => 2,
                    'type' => 'text',
                ],
                [
                    'id' => 3,
                    'type' => 'column_end',
                ],
            ],
        ];

        // Ensure filter_preview_styles does not trigger notices on missing gfmc_layout_css
        $styles = $this->preview->filter_preview_styles(['default-style'], $v1_form);
        $this->assertIsArray($styles);

        // Ensure process_preview_form executes cleanly without $_GET['gf_page'] set
        $raw_html = '<div class="gform_wrapper"><ul class="gform_fields"><li class="gfield">Item</li></ul></div>';
        $output = $this->preview->process_preview_form($raw_html, $v1_form);
        $this->assertSame($raw_html, $output);

        // Test legacy markup filter when form explicitly requests legacy layout
        $v1_legacy_form = $v1_form;
        $v1_legacy_form['gfmc_layout_css'] = 'legacy';
        $this->assertTrue($this->frontend->filter_legacy_markup(false, $v1_legacy_form));

        // Test legacy markup filter defaults to grid mode (false) when gfmc_layout_css is unset
        $this->assertFalse($this->frontend->filter_legacy_markup(true, $v1_form));
    }
}