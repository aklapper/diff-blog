<?php
/**
 * Export functions for Zoom.
 *
 * @since   1.7.3
 * @package Tribe\Events\Virtual\Service_Providers;
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Export\Abstract_Export;
/**
 * Class Event_Export
 *
 * @since 1.7.3
 *
 * @package Tribe\Events\Virtual\Export;
 */
class Event_Export extends Abstract_Export {

	/**
	 * Modify the export parameters for the Zoom source.
	 *
	 * @since 1.7.3
	 * @since 1.8.0 add should_show parameter.
	 *
	 * @param array    $fields      The various file format components for this specific event.
	 * @param \WP_Post $event       The WP_Post of this event.
	 * @param string   $key_name    The name of the array key to modify.
	 * @param string   $type        The name of the export type.
	 * @param boolean  $should_show Whether to modify the export fields for the current user, default to false.
	 *
	 * @return array The various file format components for this specific event.
	 */
	public function modify_video_source_export_output( $fields, $event, $key_name, $type, $should_show ) {
		if ( 'zoom' !== $event->virtual_video_source ) {
			return $fields;
		}

		// If it should not show or no linked button and details, set the permalink and return.
		if (
			! $should_show ||
			(
				! $event->virtual_linked_button &&
				! $event->virtual_meeting_display_details
			)
		 ) {
			$fields[ $key_name ] = $this->format_value( get_the_permalink( $event->ID ), $key_name, $type );

			return $fields;
		}

		$url = empty( $event->virtual_meeting_url ) ? $event->virtual_url : $event->virtual_meeting_url;

		$fields[ $key_name ] = $this->format_value( $url, $key_name, $type );

		return $fields;
	}
}
