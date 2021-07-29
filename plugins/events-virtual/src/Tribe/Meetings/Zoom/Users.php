<?php
/**
 * Manages the Zoom Users
 *
 * @since   1.4.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Encryption;
use Tribe__Utils__Array as Arr;

/**
 * Class Users
 *
 * @since   1.4.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Users {

	/**
	 * Users constructor.
	 *
	 * @since 1.4.0
	 *
	 * @param Api        $api        An instance of the Zoom API handler.
	 * @param Encryption $encryption An instance of the Encryption handler.
	 */
	public function __construct( Api $api, Encryption $encryption ) {
		$this->api        = $api;
		$this->encryption = ( ! empty( $encryption ) ? $encryption : tribe( Encryption::class ) );
	}

	/**
	 * Get list of users from Zoom.
	 *
	 * @since 1.4.0
	 * @since 1.5.0 - Add support for multiple accounts.
	 *
	 * @param null|string $account_id The account id to use to get the users with.
	 *
	 * @return array<string,mixed> An array of users from Zoom.
	 */
	public function get_users( $account_id = null ) {
		$api = $this->api;
		if ( $account_id ) {
			$api->load_account_by_id( $account_id );
		} else {
			$api->load_account();
		}

		if ( empty( $this->api->is_ready() ) ) {
			return [];
		}

		/** @var \Tribe__Cache $cache */
		$cache    = tribe( 'cache' );
		$cache_id = 'events_virtual_meetings_zoom_users' . md5( $this->api->id );

		/**
		 * Filters the time in seconds until the Zoom user cache expires.
		 *
		 * @since 1.4.0
		 *
		 * @param int     The time in seconds until the user cache expires, default 1 hour.
		 */
		$expiration = apply_filters( 'tribe_events_virtual_meetings_zoom_user_cache', HOUR_IN_SECONDS );
		$users      = $cache->get( $cache_id );

		if ( ! empty( $users ) ) {
			return $this->encryption->decrypt( $users, true );
		}

		$available_hosts = $api->fetch_users();
		$cache->set( $cache_id, $this->encryption->encrypt( $available_hosts, true ), $expiration );

		return $available_hosts;
	}

	/**
	 * Get list of hosts formatted for options dropdown.
	 *
	 * @since 1.4.0
	 * @since 1.5.0 - Add support for multiple accounts.
	 *
	 * @param null|string $account_id The account id to use to get the users with.
	 *
	 * @return array<string,mixed>  An array of Zoom Users to use as the host
	 */
	public function get_formatted_hosts_list( $account_id = null ) {
		$available_hosts = $this->get_users( $account_id );
		if ( empty( $available_hosts['users'] ) ) {
			return [];
		}

		$active_users    = $available_hosts['users'];
		$hosts           = [];
		foreach ( $active_users as $user ) {
			$name  = Arr::get( $user, 'email', '' );
			$value = Arr::get( $user, 'id', '' );
			$type  = Arr::get( $user, 'type', 0 );

			if ( empty( $name ) || empty( $value ) ) {
				continue;
			}

			$hosts[] = [
				'text'             => (string) $name,
				'id'               => (string) $value,
				'value'            => (string) $value,
				'alternative_host' => $type > 1 ? true : false,
			];
		}

		return $hosts;
	}

	/**
	 * Get the alternative users that can be used as hosts.
	 *
	 * @since 1.4.0
	 *
	 * @param array<string,mixed>   An array of Zoom Users to use as the alternative hosts.
	 * @param string $selected_alt_hosts The list of alternative host emails.
	 * @param string $current_host       The email of the current host.
	 * @param null|string $account_id The account id to use to get the users with.
	 *
	 * @return array|bool|mixed An array of Zoom Users to use as the alternative hosts.
	 */
	public function get_alternative_users( $alternative_hosts = [], $selected_alt_hosts = '', $current_host = '', $account_id = null ) {
		$all_users = $this->get_formatted_hosts_list( $account_id );

		$selected_alt_hosts = explode( ';', $selected_alt_hosts );

		// Filter out the current host email and any user that is not a valid alternative host.
		// Using array_values to reindex from zero or the options do not show in the multiselect.
		$alternative_hosts = array_values(
			array_filter(
				$all_users,
				static function ( $user ) use ( $current_host )  {
					return isset( $user['alternative_host'] )
						&& true === $user['alternative_host']
						&& $user['text'] !== $current_host;
				}
			)
		);

		// Change the dropdown value to the email for alternative hosts because that is what Zoom returns.
		$alternative_hosts_email_id = array_map(
			static function ( $user ) use ( $selected_alt_hosts ) {
				$user['id'] = $user['text'];
				$user['selected'] = in_array( $user['text'], $selected_alt_hosts ) ? true : false;
				return $user;
			},
			$alternative_hosts
		);

		return $alternative_hosts_email_id;
	}
}
