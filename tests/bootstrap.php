<?php
/**
 * PHPUnit bootstrap file.
 */

// Load Composer autoloader (includes PSR-4 and PHPUnit Polyfills)
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Initialize PHPUnit Polyfills for WP integration
// Yoast\PHPUnitPolyfills\Autoload::register();

// Path to WordPress test environment
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/wordpress-phpunit';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    // 1. Load Gravity Forms first
    // (Based on your previous error logs, your folder is 'gravityforms-v3-0-0')
    $gf_path = dirname( dirname( dirname( __FILE__ ) ) ) . '/gravityforms-v3-0-0/gravityforms.php';
    if ( file_exists( $gf_path ) ) {
        require $gf_path;
    }

	require dirname( dirname( __FILE__ ) ) . '/gf-form-multicolumn.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
