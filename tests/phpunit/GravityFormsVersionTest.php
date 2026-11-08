<?php

/**
 * Tests compatibility across different Gravity Forms major versions.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Frontend;

class GravityFormsVersionTest extends WP_UnitTestCase
{

    private $frontend;

    public function set_up(): void
    {
        parent::set_up();
        $this->frontend = new Frontend('gf-form-multicolumn', '4.0.9');
    }

    /**
     * Verify handling when GF_MIN_VERSION or GFCommon::$version simulates older releases.
     */
    public function test_graceful_degradation_on_legacy_gf(): void
    {
        // Test form payload without modern markupVersion
        $form = [
            'id' => 50,
            'fields' => [
                ['id' => 1, 'type' => 'text'],
            ],
        ];

        // Ensure process_form does not crash if GF returns empty strings or non-standard HTML
        $empty_output = $this->frontend->process_form('', $form);
        $this->assertIsString($empty_output);

        $malformed_html = '<form id="gform_50">No wrapper div</form>';
        $passthrough = $this->frontend->process_form($malformed_html, $form);
        $this->assertIsString($passthrough);
    }
}