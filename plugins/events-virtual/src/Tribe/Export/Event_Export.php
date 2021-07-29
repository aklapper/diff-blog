<?php
/**
 * Export functions for the plugin.
 *
 * @since   1.0.4
 * @package Tribe\Events\Virtual\Service_Providers;
 */

namespace Tribe\Events\Virtual\Export;

/**
 * Class Event_Export
 *
 * @since 1.0.4
 * @package Tribe\Events\Virtual\Export;
 */
class Event_Export {

	/**
	 * Modify the export parameters for a virtual event export.
	 *
	 * @since 1.0.4
	 *
	 * @param array  $fields   The various file format components for this specific event.
	 * @param int    $event_id The event id.
	 * @param string $key_name The name of the array key to modify.
	 *
	 * @return array The various file format components for this specific event.
	 */
	public function modify_export_output( $fields, $event_id, $key_name, $type = null ) {

		$event = tribe_get_event( $event_id );

		if ( ! $event instanceof \WP_Post ) {
			return $fields;
		}

		// If it is not a virtual event, return fields.
		if ( ! $event->virtual ) {
			return $fields;
		}

		// If there is a venue, return fields as is.
		if ( isset( $event->venues[0] ) ) {
			return $fields;
		}

		// If an embed video or no linked button, set the permalink and return.
		if (
			$event->virtual_embed_video ||
			(
				! $event->virtual_linked_button &&
				! $event->zoom_display_details
			)
		 ) {
			$fields[ $key_name ] = $this->format_value( get_the_permalink( $event->ID ), $key_name, $type );

			return $fields;
		}

		$url = $event->virtual_url;
		if ( ! empty( $event->virtual_meeting_url ) ) {
			$url = $event->virtual_meeting_url;
		}

		$fields[ $key_name ] = $this->format_value( $url, $key_name, $type );

		return $fields;
	}

	/**
	 * Format the exported value to conform to the export type's standard.
	 *
	 * @since 1.1.5
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
