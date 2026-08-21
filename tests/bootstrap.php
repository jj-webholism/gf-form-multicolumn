<?php
/**
 * PHPUnit bootstrap file.
 */

// Load Composer autoloader (includes PSR-4 and PHPUnit Polyfills)
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Path to WordPress test environment
$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Check environment variable first, then fallback to system temp folder
if ( ! $_tests_dir || ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh?" . PHP_EOL;
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    // 1. Load Gravity Forms if present in neighboring directory
    $gf_path = dirname( dirname( dirname( __FILE__ ) ) ) . '/gravityforms-v3-0-0/gravityforms.php';
    if ( file_exists( $gf_path ) ) {
        require $gf_path;
    }

    // 2. Load our plugin
    require dirname( dirname( __FILE__ ) ) . '/gf-form-multicolumn.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// FORCE DATABASE SOCKET FOR LOCAL WP BEFORE BOOTSTRAP
if ( ! defined( 'DB_HOST' ) ) {
    define( 'DB_HOST', 'localhost:/tmp/local_mysql.sock' );
}

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";