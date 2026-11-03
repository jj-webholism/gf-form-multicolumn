<?php
/**
 * Logger class file.
 *
 * @package WH\GF\Multicolumn
 */

namespace WH\GF\Multicolumn;

/**
 * Handles plugin debug logging via Gravity Forms logging facility.
 */
class Logger {


	/**
	 * Log a message to Gravity Forms logs.
	 *
	 * @param string $message The message to log.
	 * @param string $level The log level (e.g. 'debug', 'error').
	 * @return void
	 */
	public static function log( $message, $level = 'debug' ): void {
		if ( class_exists( '\GFCommon' ) ) {
			if ( 'error' === $level ) {
				\GFCommon::log_error( 'gf-form-multicolumn: ' . $message );
			} else {
				\GFCommon::log_debug( 'gf-form-multicolumn: ' . $message );
			}
		}
	}
}
