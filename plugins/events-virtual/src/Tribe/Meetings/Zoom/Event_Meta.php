<?php
/**
 * Handles the post meta related to Zoom Meetings.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Encryption;
use Tribe\Events\Virtual\Event_Meta as Virtual_Event_Meta;
use Tribe\Events\Virtual\Meetings\Zoom_Provider;
use Tribe__Utils__Array as Arr;

/**
 * Class Event_Meta
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Event_Meta {

	/**
	 * An array of fields to encrypt, using names from Zoom API.
	 *
	 * @since 1.4.0
	 *
	 * @var array<string|boolean> An array of field names and whether the field is an array.
	 */
	public static $encrypted_fields = [
		'meeting_data'      => true,
		'host_email'        => false,
		'alternative_hosts' => false,
	];

	/**
	 * Removes the Zoom Meeting meta from a post.
	 *
	 * @since 1.0.0
	 *
	 * @param int|\WP_Post $post The event post ID or object.
	 */
	public static function delete_meeting_meta( $post ) {
		$event = tribe_get_event( $post );

		if ( ! $event instanceof \WP_Post ) {
			return false;
		}

		$zoom_meta = static::get_post_meta( $event );

		foreach ( array_keys( $zoom_meta ) as $meta_key ) {
			delete_post_meta( $event->ID, $meta_key );
		}

		return true;
	}

	/**
	 * Returns an event post meta related to Zoom Meetings.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 - Add decryption for encrytped fields.
	 *
	 * @param int|\WP_Post $post The event post ID or object.
	 *
	 * @return array The Zoom Meeting post meta or an empty array if not found or not an event.
	 */
	public static function get_post_meta( $post ) {
		$event = tribe_get_event( $post );

		if ( ! $event instanceof \WP_Post ) {
			return [];
		}

		$all_meta = get_post_meta( $event->ID );

		$prefix = Virtual_Event_Meta::$prefix . 'zoom_';

		$flattened_array = Arr::flatten(
			array_filter(
				$all_meta,
				static function ( $meta_key ) use ( $prefix ) {
					return 0 === strpos( $meta_key, $prefix );
				},
				ARRAY_FILTER_USE_KEY
			)
		);

		// Decrypt the encrypted meta fields.
		$encrypted_fields = self::$encrypted_fields;
		$encryption       = tribe( Encryption::class );
		foreach ( $flattened_array as $meta_key => $meta_value ) {
			$encrypted_field_key = str_replace( $prefix, '', $meta_key );

			if ( ! array_key_exists( $encrypted_field_key, $encrypted_fields ) ) {
				continue;
			}

			$flattened_array[ $meta_key ] = $encryption->decrypt( $meta_value, $encrypted_fields[ $encrypted_field_key ] );
		}

		return $flattened_array;
	}

	/**
	 * Add information about the zoom meeting if available, only if the user has permission to read_private_posts via
	 * the REST Api.
	 *
	 * @since 1.1.1
	 *
	 * @param array<string,mixed> $data  The current data of the event.
	 * @param \WP_Post            $event The event being updated.
	 *
	 * @return array<string,mixed> An array with the data of the event on the endpoint.
	 */
	public function attach_rest_properties( array $data, \WP_Post $event ) {
		$event = tribe_get_event( $event );

		if ( ! $event instanceof \WP_Post || ! current_user_can( 'read_private_posts' ) ) {
			return $data;
		}

		if ( empty( $data['meetings'] ) ) {
			$data['meetings'] = [];
		}

		if ( ! $event->virtual || empty( $event->zoom_meeting_id ) ) {
			return $data;
		}

		$data['meetings']['zoom'] = [
			'id'           => $event->zoom_meeting_id,
			'url'          => $event->zoom_join_url,
			'numbers'      => $event->zoom_global_dial_in_numbers,
			'password'     => get_post_meta( $event->ID, Virtual_Event_Meta::$prefix . 'zoom_password', true ),
			'type'         => $event->zoom_meeting_type,
			'instructions' => $event->zoom_join_instructions,
		];

		return $data;
	}

	/**
	 * Get the host email from the meta or saved zoom data.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return string|null The found host email or null for the meeting.
	 */
	public static function get_host_email( \WP_Post $event ) {
		$encryption = tribe( Encryption::class );
		$prefix     = Virtual_Event_Meta::$prefix;
		$host_email = $encryption->decrypt( get_post_meta( $event->ID, $prefix . 'zoom_host_email', true ) );

		if ( $host_email ) {
			return $host_email;
		}

		$all_zoom_details = $encryption->decrypt( get_post_meta( $event->ID, $prefix . 'zoom_meeting_data', true ) );

		return Arr::get( $all_zoom_details, 'host_email', null );
	}

	/**
	 * Get the alternative host emails from the meta or saved zoom data.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return string|null The found host email or null for the meeting.
	 */
	public static function get_alt_host_emails( \WP_Post $event ) {
		$encryption        = tribe( Encryption::class );
		$prefix            = Virtual_Event_Meta::$prefix;
		$alternative_hosts = $encryption->decrypt( get_post_meta( $event->ID, $prefix . 'zoom_alternative_hosts', true ) );

		if ( $alternative_hosts ) {
			return $alternative_hosts;
		}

		$all_zoom_details = $encryption->decrypt( get_post_meta( $event->ID, $prefix . 'zoom_meeting_data', true ) );
		$settings = Arr::get( $all_zoom_details, 'settings', [] );

		return Arr::get( $settings, 'alternative_hosts', '' );
	}

	/**
	 * Adds Zoom Meeting related properties to an event post object.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
	 *
	 * @return \WP_Post The decorated event post object, with Zoom related properties added to it.
	 */
	public static function add_event_properties( \WP_Post $event ) {
		$prefix = Virtual_Event_Meta::$prefix;

		$is_new_event = empty( $event->ID );

		$event->zoom_meeting_type      = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'zoom_meeting_type', true );
		$event->zoom_meeting_id        = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'zoom_meeting_id', true );
		$event->zoom_join_url          = $is_new_event ? '' : tribe( Password::class )->get_zoom_meeting_link( $event );
		$event->zoom_join_instructions = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'zoom_join_instructions', true );
		$event->zoom_display_details   = $is_new_event ? '' : get_post_meta( $event->ID, $prefix . 'zoom_display_details', true );
		$event->zoom_host_email        = $is_new_event ? '' : self::get_host_email( $event );
		$event->zoom_alternative_hosts = $is_new_event ? '' : self::get_alt_host_emails( $event );

		$dial_in_numbers = $is_new_event ? [] : array_filter(
			(array) get_post_meta( $event->ID, $prefix . 'zoom_global_dial_in_numbers', true )
		);

		$compact_phone_number = static function ( $phone_number ) {
			return trim( str_replace( ' ', '', $phone_number ) );
		};

		$event->zoom_global_dial_in_number = count( $dial_in_numbers )
			? array_keys( $dial_in_numbers )[0]
			: '';

		$event->zoom_global_dial_in_numbers = [];
		foreach ( $dial_in_numbers as $phone_number => $country ) {
			$event->zoom_global_dial_in_numbers[] = [
				'country' => $country,
				'compact' => $compact_phone_number( $phone_number ),
				'visual'  => $phone_number,
			];
		}

		if ( ! empty( $event->zoom_join_url ) ) {
			// An event that has a Zoom Meeting assigned should be considered virtual.
			$event->virtual                  = true;
			$event->virtual_meeting          = true;
			$event->virtual_meeting_url      = $event->zoom_join_url;
			$event->virtual_meeting_provider = tribe( Zoom_Provider::class )->get_slug();

			// Override the virtual url if no zoom details and linked button is checked.
			if (
				empty( $event->zoom_display_details )
				&& ! empty( $event->virtual_linked_button )
			) {
				$event->virtual_url = $event->virtual_meeting_url;
			} else {
				// Set virtual url to null if Zoom Meeting is connected to the event.
				$event->virtual_url = null;
			}
		}

		return $event;
	}

	/**
	 * Parses and Saves the data from a metabox update request.
	 *
	 * @since 1.0.0
	 *
	 * @param int                 $post_id The post ID of the post the date is being saved for.
	 * @param array<string,mixed> $data    The data to save, directly from the metabox.
	 */
	public function save_metabox_data( $post_id, array $data ) {
		$prefix = Virtual_Event_Meta::$prefix;

		$join_url = get_post_meta( $post_id, $prefix . 'zoom_join_url', true );

		// An event that has a Zoom Meeting link should always be considered virtual, let's ensure that.
		if ( ! empty( $join_url ) ) {
			update_post_meta( $post_id, Virtual_Event_Meta::$key_virtual, true );
		}

		$map = [
			'meetings-zoom-display-details' => $prefix . 'zoom_display_details',
		];
		foreach ( $map as $data_key => $meta_key ) {
			$value = Arr::get( $data, 'meetings-zoom-display-details', false );
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $meta_key, $value );
			} else {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}
}
