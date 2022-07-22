<?php
/**
 * Manages the Zoom Users
 *
 * @since   1.4.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Admin_Template;
use Tribe\Events\Virtual\Encryption;
use Tribe\Events\Virtual\Metabox;
use Tribe\Events\Virtual\Traits\With_AJAX;
use Tribe__Utils__Array as Arr;

/**
 * Class Users
 *
 * @since   1.4.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Users {
	use With_AJAX;

	/**
	 * The name of the action used to get an account setup to generate a Zoom meeting or webinar.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	public static $validate_user_action = 'events-virtual-zoom-user-validate';

	/**
	 * The template handler instance.
	 *
	 * @since 1.8.2
	 *
	 * @var Admin_Template
	 */
	public $admin_template;

	/**
	 * Users constructor.
	 *
	 * @since 1.4.0
	 * @since 1.8.2 - Add the admin template class.
	 *
	 * @param Api            $api        An instance of the Zoom API handler.
	 * @param Encryption     $encryption An instance of the Encryption handler.
	 * @param Admin_Template $template   An instance of the Template class to handle the rendering of admin views.
	 */
	public function __construct( Api $api, Encryption $encryption, Admin_Template $admin_template ) {
		$this->api            = $api;
		$this->encryption     = ( ! empty( $encryption ) ? $encryption : tribe( Encryption::class ) );
		$this->admin_template = $admin_template;
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
		$users      = $cache->get_transient( $cache_id );

		if ( ! empty( $users ) ) {
			return $this->encryption->decrypt( $users, true );
		}

		$available_hosts = $api->fetch_users();
		$cache->set_transient( $cache_id, $this->encryption->encrypt( $available_hosts, true ), $expiration );

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
			$name  = Arr::get( $user, 'first_name', '' ) . ' ' .  Arr::get( $user, 'last_name', '' ) . ' - '. Arr::get( $user, 'email', '' );
			$last_name = Arr::get( $user, 'last_name', '' );
			$value = Arr::get( $user, 'id', '' );
			$type  = Arr::get( $user, 'type', 0 );

			if ( empty( $name ) || empty( $value ) ) {
				continue;
			}

			if ( empty( $last_name ) ) {
				$last_name = Arr::get( $user, 'first_name', '' );
			}

			$hosts[] = [
				'text'             => (string) trim( $name ),
				'email'            => (string) Arr::get( $user, 'email', '' ),
				'sort'             => (string) trim( $last_name ),
				'id'               => (string) $value,
				'value'            => (string) $value,
				'alternative_host' => $type > 1 ? true : false,
				'selected'         => $account_id === $value ? true : false,
			];
		}

		// Sort the hosts array by text(email).
		$sort_arr = array_column( $hosts, 'sort' );
		array_multisort( $sort_arr, SORT_ASC, $hosts );

		return $hosts;
	}

	/**
	 * Get the alternative users that can be used as hosts.
	 *
	 * @since 1.4.0
	 * @since 1.9.0 - Update formatting with changes from get_formatted_hosts_list().
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
				$user['id'] = $user['email'];
				$user['selected'] = in_array( $user['email'], $selected_alt_hosts ) ? true : false;
				return $user;
			},
			$alternative_hosts
		);

		return $alternative_hosts_email_id;
	}

	/**
	 * Handles the request to validate a user type.
	 *
	 * @since 1.9.0
	 *
	 * @param string|null $nonce The nonce that should accompany the request.
	 *
	 * @return string The html from the request containing success or error information.
	 */
	public function validate_user( $nonce = null ) {
		if ( ! $this->check_ajax_nonce( static::$validate_user_action, $nonce ) ) {
			return false;
		}

		$event = $this->check_ajax_post();
		if ( empty( $event ) ) {
			$error_message = _x( 'User validation failed because no event was found.', 'The event is missing error message for Zoom user validation.', 'events-virtual' );
			$this->admin_template->template( 'components/message', [
				'message' => $error_message,
				'type'    => 'error',
			] );

			wp_die();
		}

		$zoom_host_id = tribe_get_request_var( 'host_id' );
		// If no host id found, fail the request.
		if ( empty( $zoom_host_id ) ) {
			$error_message = _x( 'The Zoom Host ID is missing to access the API, please select a host from the dropdown and try again.', 'Host ID is missing error message for Zoom user validation.', 'events-virtual' );
			$this->admin_template->template( 'components/message', [
				'message' => $error_message,
				'type'    => 'error',
			] );

			wp_die();
		}

		$zoom_account_id = tribe_get_request_var( 'account_id' );
		// If no account id found, fail the request.
		if ( empty( $zoom_account_id ) ) {
			$error_message = _x( 'The Zoom Account ID is missing to access the API.', 'Account ID is missing error message for Zoom user validation.', 'events-virtual' );
			$this->admin_template->template( 'components/message', [
				'message' => $error_message,
				'type'    => 'error',
			] );

			wp_die();
		}

		$account_loaded = $this->api->load_account_by_id( $zoom_account_id );
		// If there is no token, then stop as the connection will fail.
		if ( ! $account_loaded ) {
			$error_message = _x( 'The Zoom Account could not be loaded to access the API. Please try refreshing the account in the Events API Settings.', 'Zoom account loading error message for Zoom user validation.', 'events-virtual' );

			$this->admin_template->template( 'components/message', [
				'message' => $error_message,
				'type'    => 'error',
			] );

			wp_die();
		}

		$settings        = $this->api->fetch_user( $zoom_host_id, true );
		if ( empty( $settings['feature'] ) ) {
			$error_message = _x( 'The Zoom API did not return the user settings. Please try refreshing the account in the Events Integration Settings.', 'Zoom API loading error message for Zoom user validation.', 'events-virtual' );

			$this->admin_template->template( 'components/message', [
				'message' => $error_message,
				'type'    => 'error',
			] );

			wp_die();
		}

		$webinar_support       = $this->api->get_webinars_support( $settings );
		$password_requirements = $this->api->get_password_requirements( $settings );

		/** @var \Tribe\Events\Virtual\Meetings\Zoom\Classic_editor */
		$classic_editor = tribe( Classic_Editor::class );
		$generation_urls = $classic_editor->get_link_creation_urls( $event, $webinar_support );

		$this->admin_template->template(
		'virtual-metabox/api/type-options',
			[
				'api_id'                => $this->api::$api_id,
				'generation_urls'       => $generation_urls,
				'password_requirements' => $password_requirements,
				'metabox_id'            => Metabox::$id,
			],
			true
		);

		wp_die();
	}
}
