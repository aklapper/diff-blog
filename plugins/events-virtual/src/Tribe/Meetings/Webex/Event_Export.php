<?php
/**
 * Export functions for Webex.
 *
 * @since 1.9.0
 * @package Tribe\Events\Virtual\Meetings\Webex;
 */

namespace Tribe\Events\Virtual\Meetings\Webex;

use Tribe\Events\Virtual\Export\Abstract_Export;
/**
 * Class Event_Export
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex;
 */
class Event_Export extends Abstract_Export {

	/**
	 * Modify the export parameters for the Webex source.
	 *
	 * @since 1.9.0
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
		if ( 'webex' !== $event->virtual_video_source ) {
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

		/**
		 * Allow filtering of the export fields for Webex.
		 *
		 * @since 1.9.0
		 *
		 * @param array    $fields      The various file format components for this specific event.
		 * @param \WP_Post $event       The WP_Post of this event.
		 * @param string   $key_name    The name of the array key to modify.
		 * @param string   $type        The name of the export type.
		 * @param boolean  $should_show Whether to modify the export fields for the current user, default to false.
		 */
		$fields = apply_filters( 'tec_events_virtual_webex_export_fields', $fields, $event, $key_name, $type, $should_show );

		return $fields;
	}

	/**
	 * Modify the gCal details component to add the password.
	 *
	 * @since 1.9.0
	 *
	 * @param array    $fields      The various file format components for this specific event.
	 * @param \WP_Post $event       The WP_Post of this event.
	 * @param string   $key_name    The name of the array key to modify.
	 * @param string   $type        The name of the export type.
	 * @param boolean  $should_show Whether to modify the export fields for the current user, default to false.
	 *
	 * @return array The various file format components for this specific event with the added password.
	 */
	public function add_password_to_gcal_details( $fields, $event, $key_name, $type, $should_show ) {
		if ( ! $should_show ) {
			return $fields;
		}

		if ( 'gcal' !== $type ) {
			return $fields;
		}

		if ( ! isset( $fields['details'] ) ) {
			return $fields;
		}

		if ( $this->str_contains( $fields['details'], $event->webex_password ) ) {
			return $fields;
		}

		if ( isset( $fields['details'] ) ) {
			$fields['details'] .= ' - ' . $this->get_password_label_with_password( $event );
		}

		return $fields;
	}

	/**
	 * Modify the iCal description component to add the password.
	 *
	 * @since 1.9.0
	 *
	 * @param array    $fields      The various file format components for this specific event.
	 * @param \WP_Post $event       The WP_Post of this event.
	 * @param string   $key_name    The name of the array key to modify.
	 * @param string   $type        The name of the export type.
	 * @param boolean  $should_show Whether to modify the export fields for the current user, default to false.
	 *
	 * @return array The various file format components for this specific event with the added password.
	 */
	public function add_password_to_ical_description( $fields, $event, $key_name, $type, $should_show ) {
		if ( ! $should_show ) {
			return $fields;
		}

		if ( 'ical' !== $type ) {
			return $fields;
		}

		if ( ! isset( $fields['DESCRIPTION'] ) ) {
			return $fields;
		}

		if ( $this->str_contains( $fields['DESCRIPTION'], $event->webex_password ) ) {
			return $fields;
		}

		if ( isset( $fields['DESCRIPTION'] ) ) {
			$fields['DESCRIPTION'] .= ' - ' . $this->get_password_label_with_password( $event );
		}

		return $fields;
	}

	/**
	 * Get the password label with included password.
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post $event The WP_Post of this event.
	 *
	 * @return string the password label with included password.
	 */
	public function get_password_label_with_password( $event ) {
		return esc_html(
			sprintf(
				// translators: %1$s:  Webex meeting password.
				_x(
					'Webex Password: %1$s',
					'The label for the Webex Meeting password, followed by the password for an exported event.',
					'events-virtual'
				),
				$event->webex_password
			)
		);
	}
}
