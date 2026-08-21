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
	 * @param string $level   The log level (e.g. 'debug', 'error').
	 * @return void
	 */
	public static function log( $message, $level = 'debug' ) {
		if ( class_exists( '\KLogger' ) && method_exists( '\KLogger', 'log' ) ) {
			\KLogger::log( $message, $level );
		}
	}
}
