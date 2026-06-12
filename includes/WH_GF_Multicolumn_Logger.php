<?php
/**
 * Logger for GF Form Multicolumn.
 *
 * @package GF_Form_Multicolumn
 */

namespace WH\GF\Multicolumn\Classes;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin logger.
 */
class WH_GF_Multicolumn_Logger {

    /**
     * Supported log levels.
     *
     * Higher numbers are more serious.
     */
    private const LEVELS = array(
        'debug'   => 100,
        'info'    => 200,
        'notice'  => 250,
        'warning' => 300,
        'error'   => 400,
    );

    /**
     * Check whether logging is enabled.
     *
     * Logging is intentionally opt-in. This prevents the plugin from writing
     * noisy debug information on production sites unless explicitly enabled.
     *
     * @return bool
     */
    public static function enabled(): bool {
        return defined( 'WP_DEBUG' )
            && WP_DEBUG
            && defined( 'WP_DEBUG_LOG' )
            && WP_DEBUG_LOG
            && defined( 'GFMC_DEBUG' )
            && GFMC_DEBUG;
    }

    /**
     * Log a debug message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context.
     *
     * @return void
     */
    public static function debug( string $message, array $context = array() ): void {
        self::log( 'debug', $message, $context );
    }

    /**
     * Log an informational message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context.
     *
     * @return void
     */
    public static function info( string $message, array $context = array() ): void {
        self::log( 'info', $message, $context );
    }

    /**
     * Log a notice message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context.
     *
     * @return void
     */
    public static function notice( string $message, array $context = array() ): void {
        self::log( 'notice', $message, $context );
    }

    /**
     * Log a warning message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context.
     *
     * @return void
     */
    public static function warning( string $message, array $context = array() ): void {
        self::log( 'warning', $message, $context );
    }

    /**
     * Log an error message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context.
     *
     * @return void
     */
    public static function error( string $message, array $context = array() ): void {
        self::log( 'error', $message, $context );
    }

    /**
     * Write a log entry.
     *
     * @param string $level   Log level.
     * @param string $message Log message.
     * @param array  $context Optional context.
     *
     * @return void
     */
    public static function log( string $level, string $message, array $context = array() ): void {
        if ( ! self::enabled() ) {
            return;
        }

        $level = strtolower( $level );

        if ( ! isset( self::LEVELS[ $level ] ) ) {
            $level = 'error';
        }

        if ( ! self::should_log_level( $level ) ) {
            return;
        }

        $entry = sprintf(
            '[GF Form Multicolumn] [%s] %s',
            strtoupper( $level ),
            $message
        );

        if ( ! empty( $context ) ) {
            $entry .= ' ' . wp_json_encode( self::normalise_context( $context ) );
        }

        self::write( $entry );
    }

    /**
     * Check whether the requested level should be logged.
     *
     * @param string $level Log level.
     *
     * @return bool
     */
    private static function should_log_level( string $level ): bool {
        $configured_level = defined( 'GFMC_LOG_LEVEL' )
            ? strtolower( (string) GFMC_LOG_LEVEL )
            : 'error';

        if ( ! isset( self::LEVELS[ $configured_level ] ) ) {
            $configured_level = 'error';
        }

        return self::LEVELS[ $level ] >= self::LEVELS[ $configured_level ];
    }

    /**
     * Write the entry to the configured log.
     *
     * Uses error_log() so that WordPress/PHP handles the logging destination.
     *
     * @param string $entry Log entry.
     *
     * @return void
     */
    private static function write( string $entry ): void {
        error_log( $entry ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
    }

    /**
     * Normalise context values so logs stay readable.
     *
     * @param array $context Context array.
     *
     * @return array
     */
    private static function normalise_context( array $context ): array {
        foreach ( $context as $key => $value ) {
            if ( is_object( $value ) ) {
                $context[ $key ] = sprintf(
                    'Object(%s)',
                    get_class( $value )
                );
            } elseif ( is_resource( $value ) ) {
                $context[ $key ] = 'Resource';
            }
        }

        return $context;
    }
}