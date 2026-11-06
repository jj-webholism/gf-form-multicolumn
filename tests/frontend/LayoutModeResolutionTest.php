<?php

/**
 * Test layout engine and CSS resolution logic.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Form_Current;

class LayoutModeResolutionTest extends WP_UnitTestCase
{

    /**
     * Test new explicit setting values.
     *
     * @dataProvider layout_mode_provider
     *
     * @param string $setting_value Setting saved in form.
     * @param string $expected_engine Expected formatter engine ('grid' or 'legacy').
     * @param bool $expected_css Expected CSS enqueue status.
     */
    public function test_explicit_setting_resolution(string $setting_value, string $expected_engine, bool $expected_css): void
    {
        $form = array(
            'id' => 1,
            'gfmc_layout_css' => $setting_value,
        );

        $resolved = Form_Current::resolve_mode($form);

        $this->assertSame($expected_engine, $resolved['engine']);
        $this->assertSame($expected_css, $resolved['enqueue_css']);
    }

    /**
     * Data provider for explicit settings.
     *
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public function layout_mode_provider(): array
    {
        return array(
            'Modern Grid with CSS' => array('grid', 'grid', true),
            'Modern Grid without CSS' => array('grid_none', 'grid', false),
            'Legacy List with CSS' => array('legacy', 'legacy', true),
            'Legacy List without CSS' => array('legacy_none', 'legacy', false),
        );
    }

    /**
     * Test backward compatibility: existing form with legacy CSS enabled.
     */
    public function test_legacy_form_with_css_enabled_defaults_to_legacy_engine(): void
    {
        $form = array(
            'id' => 2,
            'gfmcEnableCSS' => 1,
        );

        $resolved = Form_Current::resolve_mode($form);

        $this->assertSame('legacy', $resolved['engine'], 'Existing form with gfmcEnableCSS=1 must use legacy engine.');
        $this->assertTrue($resolved['enqueue_css'], 'Existing form with gfmcEnableCSS=1 must load stylesheet.');
    }

    /**
     * Test backward compatibility: existing form with legacy CSS disabled.
     */
    public function test_legacy_form_with_css_disabled_defaults_to_legacy_engine_without_css(): void
    {
        $form = array(
            'id' => 3,
            'gfmcEnableCSS' => 0,
        );

        $resolved = Form_Current::resolve_mode($form);

        $this->assertSame('legacy', $resolved['engine'], 'Existing form with gfmcEnableCSS=0 must maintain legacy markup.');
        $this->assertFalse($resolved['enqueue_css'], 'Existing form with gfmcEnableCSS=0 must suppress stylesheet.');
    }

    /**
     * Test default behavior: brand new form with no multicolumn settings saved.
     */
    public function test_new_form_defaults_to_modern_grid_with_css(): void
    {
        $form = array(
            'id' => 4,
        );

        $resolved = Form_Current::resolve_mode($form);

        $this->assertSame('grid', $resolved['engine'], 'New form must default to modern grid engine.');
        $this->assertTrue($resolved['enqueue_css'], 'New form must default to CSS enabled.');
    }
}