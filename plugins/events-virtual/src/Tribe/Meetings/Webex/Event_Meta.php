<?php
/**
 * Handles the post meta related to Webex Meetings.
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */

namespace Tribe\Events\Virtual\Meetings\Webex;

use Tribe\Events\Virtual\Event_Meta as Virtual_Event_Meta;
use Tribe\Events\Virtual\Meetings\Webex_Provider;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class Event_Meta
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */
class Event_Meta {

	/**
	 * Key for Webex and autodetect source.
	 *
	 * @since 1.9.0
	 *
	 * @var string
	 */
	public static $key_source_id = 'webex';

	/**
	 * Determines if the password should be shown
	 * based on the `virtual_show_embed_to` setting of the event.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return boolean
	 */
	public static function should_show_password( $event ) {
		if ( ! $event instanceof WP_Post ) {
			return false;
		}

		$show = ! in_array( Virtual_Event_Meta::$value_show_embed_to_logged_in, $event->virtual_show_embed_to, true ) || is_user_logged_in();

		/**
		 * Filters whether the virtual content should show or not.
		 *
		 * @since 1.0.4
		 *
		 * @param boolean $show  If the virtual content should show or not.
		 * @param WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
		 */
		return apply_filters( 'tec_events_virtual_show_virtual_content', $show, $event );
	}

	/**
	 * Get the Webex password if it should be shown.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return string|null The password or null if it should not be shown.
	 */
	public static function get_password( \WP_Post $event ) {
		$should_show = static::should_show_password( $event );

		/**
		 * Filters whether the Webex password should be shown.
		 *
		 * @since 1.9.0
		 *
		 * @param boolean $should_show Whether the password should be shown.
		 * @param WP_Post $event       The event post object, as decorated by the `tribe_get_event` function.
		 */
		$should_show = apply_filters( 'tec_events_virtual_meetings_webex_meeting_show_password', $should_show, $event );
		if ( ! $should_show ) {
			return null;
		}

		$prefix   = Virtual_Event_Meta::$prefix;
		$password = get_post_meta( $event->ID, $prefix . 'webex_password', true );

		if ( $password ) {
			return $password;
		}

		$all_webex_details = get_post_meta( $event->ID, $prefix . 'webex_meeting_data', true );

		return Arr::get( $all_webex_details, 'password', null );
	}

	/**
	 * Adds Webex related properties to an event post object.
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return \WP_Post The decorated event post object, with Webex related properties added to it.
	 */
	public static function add_event_properties( \WP_Post $event ) {

		// Get the current actions
		$current_action = tribe_get_request_var( 'action' );
		$create_actions = [
			'ev_webex_meetings_create',
		];

		// Return when Webex is not the source and not running the create actions for meetings and webinars.
		if ( static::$key_source_id !== $event->virtual_video_source && ! in_array( $current_action, $create_actions ) ) {
			return $event;
		}

		$prefix = Virtual_Event_Meta::$prefix;

		$is_new_event = empty( $event->ID );

		$event->webex_meeting_type              = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'webex_meeting_type', true );
		$event->webex_meeting_id                = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'webex_meeting_id', true );
		$event->webex_join_url                  = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'webex_join_url', true );
		$event->virtual_meeting_display_details = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'webex_display_details', true );
		$event->webex_host_email                = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'webex_host_email', true );
		$event->webex_password                  = self::get_password( $event );

		if ( ! empty( $event->webex_join_url ) ) {
			// An event that has a Webex Meeting assigned should be considered virtual.
			$event->virtual                  = true;
			$event->virtual_meeting          = true;
			$event->virtual_meeting_url      = $event->webex_join_url;
			$event->virtual_meeting_provider = static::$key_source_id;

			// Override the virtual url if no Webex details and linked button is checked.
			if (
				empty( $event->virtual_meeting_display_details )
				&& ! empty( $event->virtual_linked_button )
			) {
				$event->virtual_url = $event->virtual_meeting_url;
			} else {
				// Set virtual url to null if Webex Meeting is connected to the event.
				$event->virtual_url = null;
			}
		}

		return $event;
	}

	/**
	 * Parses and Saves the data from a metabox update request.
	 *
	 * @since 1.9.0
	 *
	 * @param int                 $post_id The post ID of the post the date is being saved for.
	 * @param array<string,mixed> $data    The data to save, directly from the metabox.
	 */
	public function save_metabox_data( $post_id, array $data ) {
		$prefix = Virtual_Event_Meta::$prefix;

		$join_url = get_post_meta( $post_id, $prefix . 'webex_join_url', true );

		// An event that has a Webex Meeting link should always be considered virtual, let's ensure that.
		if ( ! empty( $join_url ) ) {
			update_post_meta( $post_id, Virtual_Event_Meta::$key_virtual, true );
		}

		$map = [
			'meetings-api-display-details' => $prefix . 'webex_display_details',
		];
		foreach ( $map as $data_key => $meta_key ) {
			$value = Arr::get( $data, 'meetings-api-display-details', false );
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $meta_key, $value );
			} else {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}

	/**
	 * Returns an event post meta related to Webex.
	 *
	 * @since 1.9.0
	 *
	 * @param int|\WP_Post $post The event post ID or object.
	 *
	 * @return array The Webex post meta or an empty array if not found or not an event.
	 */
	public static function get_post_meta( $post ) {
		$event = tribe_get_event( $post );

		if ( ! $event instanceof \WP_Post ) {
			return [];
		}

		$all_meta = get_post_meta( $event->ID );

		$prefix = Virtual_Event_Meta::$prefix . 'webex_';

		$flattened_array = Arr::flatten(
			array_filter(
				$all_meta,
				static function ( $meta_key ) use ( $prefix ) {
					return 0 === strpos( $meta_key, $prefix );
				},
				ARRAY_FILTER_USE_KEY
			)
		);

		return $flattened_array;
	}

	/**
	 * Removes the Meeting meta from a post.
	 *
	 * @since 1.9.0
	 *
	 * @param int|\WP_Post $post The event post ID or object.
	 */
	public static function delete_meeting_meta( $post ) {
		$event = tribe_get_event( $post );

		if ( ! $event instanceof \WP_Post ) {
			return false;
		}

		$meta = static::get_post_meta( $event );

		foreach ( array_keys( $meta ) as $meta_key ) {
			delete_post_meta( $event->ID, $meta_key );
		}

		return true;
	}
}
