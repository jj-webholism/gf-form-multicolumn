<?php
namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Admin\Admin;
use WH\GF\Multicolumn\Frontend\Frontend;

class SampleTest extends WP_UnitTestCase {

    public function test_plugin_classes_can_instantiate() {
        $admin = new \WH\GF\Multicolumn\Admin\Admin( 'gf-form-multicolumn', '4.0.9' );
        $frontend = new \WH\GF\Multicolumn\Frontend\Frontend( 'gf-form-multicolumn', '4.0.9' );
        $this->assertInstanceOf( \WH\GF\Multicolumn\Frontend\Frontend::class, $frontend );

        $this->assertNotNull( $admin );
    }

    public function test_wordpress_environment_is_active() {
        $this->assertTrue( function_exists( 'do_action' ) );
    }
}