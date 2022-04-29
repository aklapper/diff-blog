<?php
/**
 * Export functions abstract class.
 *
 * @since   1.7.3
 *
 * @package Tribe\Events\Virtual\Export;
 */

namespace Tribe\Events\Virtual\Export;

/**
 * Class Abstract_Export
 *
 * @since 1.7.3
 *
 * @package Tribe\Events\Virtual\Export;
 */
abstract class Abstract_Export {
	/**
	 * Format the exported value to conform to the export type's standard.
	 *
	 * @since 1.7.3
	 *
	 * @param string $value    The value being exported.
	 * @param string $key_name The key name to add to the value.
	 * @param string $type     The name of the export, ie ical, gcal, etc...
	 *
	 * @return string The value to add to the export.
	 */
	public function format_value( $value, $key_name, $type ) {

		if ( 'ical' === $type ) {
			/**
			 * With iCal we have to include the key name with the url
			 * or the export will only include the url without the defining tag.
			 * Example of expected output: - Location: https://tri.be?326t3425225
			 */
			$value = $key_name . ':' . $value;
		}

		return $value;
	}
}
