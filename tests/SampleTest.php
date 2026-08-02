<?php
namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\admin\WH_GF_Multicolumn_Admin;
use WH\GF\Multicolumn\public\WH_GF_Multicolumn_Public;

class SampleTest extends WP_UnitTestCase {

    public function test_plugin_classes_can_instantiate() {
        $admin  = new WH_GF_Multicolumn_Admin();
        $public = new WH_GF_Multicolumn_Public();

        $this->assertNotNull( $admin );
        $this->assertNotNull( $public );
    }

    public function test_wordpress_environment_is_active() {
        $this->assertTrue( function_exists( 'do_action' ) );
    }
}