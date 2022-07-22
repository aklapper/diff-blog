<?php
/**
 * Handles the post meta related to Google Meet.
 *
 * @since 1.11.0
 *
 * @package Tribe\Events\Virtual\Meetings\Google
 */

namespace Tribe\Events\Virtual\Meetings\Google;

use Tribe\Events\Virtual\Integrations\Abstract_Event_Meta;
use WP_Post;

/**
 * Class Event_Meta
 *
 * @since 1.11.0
 *
 * @package Tribe\Events\Virtual\Meetings\Google
 */
class Event_Meta extends Abstract_Event_Meta {

	/**
	 * {@inheritDoc}
	 */
	public static $key_source_id = 'google';

	/**
	 * {@inheritDoc}
	 */
	protected static $create_actions = [
		'ev_google_meetings_create',
	];

	/**
	 * {@inheritDoc}
	 */
	protected static function get_meeting_id( WP_Post $event ) {
		return $event->google_meeting_id;
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function get_meeting_join_url( WP_Post $event ) {
		return $event->google_join_url;
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function get_meeting_data_for_rest_api( WP_Post $event ) {
		return [
			'id'         => $event->google_meeting_id,
			'url'        => $event->google_join_url,
			'host_email' => $event->google_host_email,
			'numbers'    => $event->google_global_dial_in_numbers,
			'type'       => $event->google_meeting_type,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function get_api_properties( WP_Post $event, $prefix, $is_new_event ) {
		$event->google_meeting_type             = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'google_meeting_type', true );
		$event->google_meeting_id               = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'google_meeting_id', true );
		$event->google_conference_id            = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'google_conference_id', true );
		$event->google_join_url                 = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'google_join_url', true );
		$event->virtual_meeting_display_details = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'google_display_details', true );
		$event->google_host_email               = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'google_host_email', true );
		$event->google_global_dial_in_numbers   = tribe( Phone_Number::class )->get_google_meet_number( $event );

		return $event;
	}
}
