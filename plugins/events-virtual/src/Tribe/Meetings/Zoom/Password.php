<?php
/**
 * Manages the Zoom Meeting Password
 *
 * @since   1.0.2
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Event_Meta as Virtual_Event_Meta;
use Tribe__Utils__Array as Arr;

/**
 * Class Password
 *
 * @since   1.0.2
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Password {

	/**
	 * Password constructor.
	 *
	 * @since 1.0.4
	 *
	 * @param Api $api An instance of the Zoom API handler.
	 */
	public function __construct( Api $api ) {
		$this->api = $api;
	}

	/**
	 * Filter the Zoom meeting password on meeting creation.
	 *
	 * @since 1.0.2
	 *
	 * @param null|string|int $unused_password The existing password for the Zoom meeting, if any.
	 * @param array           $requirements    An array of password requirements from Zoom.
	 *
	 * @return string The password for the Zoom meeting.
	 */
	public function filter_zoom_password( $unused_password, array $requirements ) {
		return $this->generate_zoom_password(
			Arr::get( $requirements, 'password_length', 10 ),
			Arr::get( $requirements, 'password_have_special_character', false ),
			Arr::get( $requirements, 'password_only_allow_numeric', false )
		);
	}

	/**
	 * Get the Zoom meeting link, with or without the password hash.
	 *
	 * @since 1.0.2
	 *
	 * @param \WP_Post $event    The event post object, as decorated by the `tribe_get_event` function.
	 * @param bool     $is_email Optional. Whether to skip the filters and return the full join url with password.
	 *                           Default false.
	 *
	 * @return string The Zoom meeting join url.
	 */
	public function get_zoom_meeting_link( \WP_Post $event, $is_email = false ) {
		/*
		 * If the ID is empty, return a blank string to prevent remove_query_arg() using current URL.
		 *
		 * This happens because get_post_meta() will always return false when the ID is empty.
		 */
		if ( empty( $event->ID ) ) {
			return '';
		}

		$prefix        = Virtual_Event_Meta::$prefix;
		$zoom_join_url = get_post_meta( $event->ID, $prefix . 'zoom_join_url', true );

		// If is_email always return the full link with password.
		if ( $is_email ) {
			return $zoom_join_url;
		}

		/**
		 * Filters the Zoom meeting link show with the password for all site visitors.
		 *
		 * @since 1.0.2
		 *
		 * @param boolean $include_password Whether a user is logged in.
		 * @param \WP_Post $event The event post object, as decorated by the `tribe_get_event` function.
		 */
		$include_password = apply_filters( 'tribe_events_virtual_meetings_zoom_meeting_include_password', is_user_logged_in(), $event );

		if ( $include_password ) {
			return $zoom_join_url;
		}

		// Remove the Query Strings.
		$zoom_join_url = remove_query_arg( 'pwd', $zoom_join_url );

		return $zoom_join_url;
	}

	/**
	 * Get the password hash from the Zoom join URL query string.
	 *
	 * @since 1.0.2
	 *
	 * @param string $url The Zoom meeting join url.
	 *
	 * @return string|null The password hash.
	 */
	public function get_hash_pwd_from_join_url( $url ) {
		$query_string = wp_parse_url( $url, PHP_URL_QUERY );
		wp_parse_str( $query_string, $query_arr );

		return Arr::get( (array) $query_arr, 'pwd', null );
	}

	/**
	 * Get the Zoom meeting password requirements.
	 *
	 * @since 1.0.2
	 *
	 * @return array An array of password requirements.
	 */
	public function get_password_requirements() {
		// Default Requirements.
		$requirements = [
			'password_length'                 => 10,
			'password_have_special_character' => true,
			'password_only_allow_numeric'     => false,
		];

		/**
		 * Filters the Zoom meeting password requirements.
		 *
		 * @since 1.0.2
		 *
		 * @param array $requirements An array of password requirements.
		 */
		$requirements = apply_filters( 'tribe_events_virtual_meetings_zoom_password_requirements', $requirements );

		return $requirements;
	}

	/**
	 * Generates a random password drawn from the defined set of characters.
	 *
	 * @since 1.0.2
	 *
	 * @param int  $length        Optional. The length of password to generate. Default 6.
	 * @param bool $special_chars Optional. Whether to include standard special characters. Default false.
	 * @param bool $only_numeric  Optional. Whether to have only numeric values.  Default false.
	 *
	 * @return string The random password.
	 */
	public function generate_zoom_password( $length = 6, $special_chars = false, $only_numeric = false ) {
		// Build the chars pool.
		$chars      = '0123456789';
		$chars     .= ! $only_numeric ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' : '';
		$chars     .= ! $only_numeric && $special_chars ? '@-_*' : '';
		$chars_pool = str_split( $chars );// Let's minimize the rounds to fully leverage `array_rand`.

		$num_req = min( $length, count( $chars_pool ) );

		$password                = '';
		$current_password_length = 0;
		while ( $current_password_length < $length ) {
			shuffle( $chars_pool );
			$password .= implode( '', array_rand( array_flip( $chars_pool ), $num_req ) );

			// Remove duplicates.
			$password                = preg_replace( '~([' . preg_quote( $chars, '~' ) . '])\1+~', '$1', $password );
			$current_password_length = strlen( $password );
		}

		// Let's make sure the password length is the expected one.
		$password = substr( $password, 0, $length );

		return (string) $password;
	}

	/**
	 * Update the Zoom Password Related Meta Fields
	 *
	 * @since 1.0.4
	 *
	 * @param \WP_Post $event The event post object.
	 *
	 * @return bool|void Whether the update completed.
	 */
	public function update_password_from_zoom( $event ) {

		$event = tribe_get_event( $event->ID );

		if ( ! $event instanceof \WP_Post ) {
			return;
		}

		if ( empty( $event->virtual ) ) {
			return;
		}

		if ( empty( $event->zoom_meeting_id ) ) {
			return;
		}

		$this->api->load_account();
		if ( empty( $this->api->is_ready() ) ) {
			return;
		}

		$meeting = $this->api->fetch_meeting_data( $event->zoom_meeting_id, $event->zoom_meeting_type );

		if ( empty( $meeting['password'] ) || empty( $meeting['join_url'] ) ) {
			return;
		}

		// It would be nice to have the encrypted password available; should that not be the case let's extract it.
		$meeting_encrypted_password = isset( $meeting['encrypted_password'] )
			? $meeting['encrypted_password']
			: $this->get_hash_pwd_from_join_url( $meeting['join_url'] );

		$prefix = Virtual_Event_Meta::$prefix;
		update_post_meta( $event->ID, $prefix . 'zoom_password', esc_html( $meeting['password'] ) );
		update_post_meta( $event->ID, $prefix . 'zoom_password_hash', esc_html( $meeting_encrypted_password ) );
		update_post_meta( $event->ID, $prefix . 'zoom_join_url', esc_url( $meeting['join_url'] ) );

		return true;
	}
}
