<?php
/**
 * Manages all the Account Connection to Zoom
 *
 * @since   1.5.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Meetings\Zoom\Settings;
use Tribe\Events\Virtual\Traits\With_AJAX;
use Tribe__Utils__Array as Arr;
use Tribe__Events__Main as TEC;

/**
 * Class Account_API
 *
 * @since   1.5.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
abstract class Account_API {
	use With_AJAX;

	/**
	 * Whether a Zoom account has been loaded for the API to use.
	 *
	 * @since 1.5.0
	 *
	 * @var boolean
	 */
	protected $account_loaded = false;

	/**
	 * Whether a Zoom account supports webinars.
	 *
	 * @since 1.5.0
	 *
	 * @var boolean
	 */
	protected $supports_webinars = false;

	/**
	 * The name of the loaded account.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public $loaded_account_name = '';

	/**
	 * The key to get the option with a list of all Zoom accounts.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	protected $all_account_key = 'tec_zoom_accounts';

	/**
	 * The prefix to save all single accounts with.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	protected $single_account_prefix = 'tec_zoom_account_';

	/**
	 * The current Zoom Account API access token.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	protected $access_token;

	/**
	 * The current Zoom Account API refresh token.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	protected $refresh_token;

	/**
	 * An array of fields to encrypt, using names from Zoom API.
	 *
	 * @since 1.5.0
	 *
	 * @var array<string|boolean> An array of field names and whether the field is an array.
	 */
	protected $encrypted_fields = [
		'name'          => false,
		'email'         => false,
		'access_token'  => false,
		'refresh_token' => false,
	];

	/**
	 * The meta field name to save the account id to for single posts.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	protected $account_id_meta_field_name = '_tribe_events_zoom_account_id';

	/**
	 * The name of the action used to get an account setup to generate a Zoom meeting or webinar.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public static $select_action = 'events-virtual-zoom-account-setup';

	/**
	 * Returns the current API access token.
	 *
	 * If not available, then a new token will be fetched.
	 *
	 * @since 1.5.0
	 *
	 * @param string $id
	 * @param string $refresh_token The API refresh token for the account.
	 *
	 * @return string The API access token, or an empty string if the token cannot be fetched.
	 */
	abstract function refresh_access_token( $id, $refresh_token );

	/**
	 * Get a User's information or settings.
	 *
	 * @since 1.5.0
	 *
	 * @param string  $user_id      A Zoom user id.
	 * @param boolean $settings     Whether to fetch the users settings.
	 * @param string  $access_token A provided access token to use to access the API.
	 *
	 * @return array<string|mixed> An array of data from the Zoom API.
	 */
	abstract function fetch_user( $user_id = '', $settings = false, $access_token = '' );

	/**
	 * Checks whether the current Zoom API is ready to use.
	 *
	 * @since 1.5.0
	 *
	 * @return bool Whether the current Zoom API has a loaded account.
	 */
	public function is_ready() {
		return ! empty( $this->account_loaded );
	}

	/**
	 * Checks whether the current Zoom account supports webinars.
	 *
	 * @since 1.5.0
	 *
	 * @return bool Whether the current Zoom account supports webinars.
	 */
	public function supports_webinars() {
		return ! empty( $this->supports_webinars );
	}

	/**
	 * Load a specific account into the API.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string|string> $account A Zoom account with the fields to access the API.
	 *
	 * @return bool Whether the account is loaded into the class to use for the API, default is false.
	 */
	public function load_account( array $account = [] ) {
		if ( $this->is_valid_account( $account ) ) {
			$this->init_account( $account );

			return true;
		}

		// Check for single events first.
		$loaded_account = '';
		if ( is_singular( TEC::POSTTYPE ) ){
			$post_id = get_the_ID();

			// Get the account id and if found, use to get the account.
			if ( $account_id = get_post_meta( $post_id, $this->account_id_meta_field_name, true ) ) {
				$loaded_account = $this->get_account_by_id( $account_id );
			}

			if ( ! $loaded_account ) {
				return false;
			}

			if ( $this->is_valid_account( $loaded_account ) ) {
				$this->init_account( $loaded_account );

				return true;
			}
		}

		// If nothing loaded so far and this is not the admin, then return false.
		if ( ! is_admin() ) {
			return false;
		}

		$account_id = $this->get_account_id_in_admin();

		// Get the account id and if found, use to get the account.
		if ( $account_id ) {
			$loaded_account = $this->get_account_by_id( $account_id );
		}

		if ( ! $loaded_account ) {
			return false;
		}

		if ( $this->is_valid_account( $loaded_account ) ) {
			$this->init_account( $loaded_account );

			return true;
		}

		return false;
	}

	/**
	 * Get the Zoom account id in the WordPress admin.
	 *
	 * @since 1.5.0
	 *
	 * @param int $post_id The optional post id
	 *
	 * @return false|mixed|string
	 */
	public function get_account_id_in_admin( $post_id = 0 ) {
		// If there is a post id, check if it is a post and if so use to get the account id.
		$post = $post_id ? get_post( $post_id ) : '';
		if ( $post instanceof \WP_Post ) {
			return get_post_meta( $post_id, $this->account_id_meta_field_name, true );
		}

		// Attempt to load through ajax requested variables.
		$nonce             = tribe_get_request_var( '_ajax_nonce' );
		$zoom_account_id   = tribe_get_request_var( 'zoom_account_id' );
		$requested_post_id = tribe_get_request_var( 'post_id' );
		if ( $zoom_account_id && $requested_post_id && $nonce ) {

			// Verify the nonce is valid.
			$valid_nonce = $this->is_valid_nonce( $nonce );
			if ( ! $valid_nonce ) {
				return '';
			}
			// Verify there is a real post.
			$post = get_post( $post_id );
			if ( $post instanceof \WP_Post ) {
				return esc_html( $zoom_account_id );
			}
		}

		// Safety check.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return '';
		}

		// Set the ID if on the single event editor.
		if ( ! $post_id ) {
			$screen = get_current_screen();
			if ( ! empty( $screen->id ) && $screen->id == TEC::POSTTYPE ) {
				global $post;
				$post_id = $post->ID;
			}
		}

		if ( ! $post_id ) {
			return '';
		}

		return esc_html( get_post_meta( $post_id, $this->account_id_meta_field_name, true ) );
	}


	/**
	 * Load a specific account by the id.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id The Zoom account id to get and load for use with the API.
	 *
	 * @return bool Whether the account is loaded into the class to use for the API, default is false.
	 */
	public function load_account_by_id( $account_id ) {
		$account = $this->get_account_by_id( $account_id );

		// Return not-found if no account.
		if ( empty( $account ) ) {
			return 'not-found';
		}

		// Return disabled if the is disabled.
		if ( empty( $account['status'] ) ) {
			return 'disabled';
		}

		return $this->load_account( $account );
	}

	/**
	 * Check if an account has all the information to be valid.
	 *
	 * It will attempt to refresh the access token if it has expired.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string|string> $account A Zoom account with the fields to access the API.
	 *
	 * @return bool
	 */
	protected function is_valid_account( $account ) {
		if ( empty( $account['id'] ) ) {
			return false;
		}
		if ( empty( $account['refresh_token'] ) ) {
			return false;
		}
		if ( empty( $account['expiration'] ) ) {
			return false;
		}

		// Attempt to refresh the token.
		$access_token = $this->maybe_refresh_access_token( $account );
		if ( empty( $access_token ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Initialize an Account to use for the API.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string|string> $account A Zoom account with the fields to access the API.
	 */
	protected function init_account( $account ) {
		$this->access_token        = $account['access_token'];
		$this->refresh_token       = $account['refresh_token'];
		$this->id                  = $account['id'];
		$this->supports_webinars   = tribe_is_truthy( $account['webinars'] );
		$this->account_loaded      = true;
		$this->loaded_account_name = $account['name'];
	}

	/**
	 * Get the listing of Zoom Accounts.
	 *
	 * @since 1.5.0
	 *
	 * @param boolean $all_data Whether to return all account data, default is only name and status.
	 *
	 * @return array<string|string> $list_of_accounts An array of all the Zoom accounts.
	 */
	public function get_list_of_accounts( $all_data = false ) {
		// Get list of accounts and decrypt the PII
		$list_of_accounts = get_option( $this->all_account_key, [] );
		foreach ( $list_of_accounts as $account_id => $account ) {
			if ( empty( $account['name'] ) ) {
				continue;
			}
			$list_of_accounts[ $account_id ]['name'] = $this->encryption->decrypt( $account['name'] );

			// If false (default ) skip getting all the account data.
			if ( empty( $all_data ) ) {
				continue;
			}
			$account_data = $this->get_account_by_id( $account_id );

			$list_of_accounts[ $account_id ] = $account_data;
		}

		return $list_of_accounts;
	}

	/**
	 * Get list of accounts formatted for options dropdown.
	 *
	 * @since 1.5.0
	 *
	 * @param boolean $all_data Whether to return only active accounts or not.
	 *
	 * @return array<string,mixed>  An array of Zoom Accounts formatted for options dropdown.
	 */
	public function get_formatted_account_list( $active_only = false ) {
		$available_accounts = $this->get_list_of_accounts( true );
		if ( empty( $available_accounts ) ) {
			return [];
		}

		$accounts = [];
		foreach ( $available_accounts as $account ) {
			$name  = Arr::get( $account, 'name', '' );
			$value = Arr::get( $account, 'id', '' );
			$status = Arr::get( $account, 'status', false );

			if ( empty( $name ) || empty( $value ) ) {
				continue;
			}

			if ( $active_only && ! $status ) {
				continue;
			}

			$accounts[] = [
				'text'  => (string) $name,
				'id'    => (string) $value,
				'value' => (string) $value,
			];
		}

		return $accounts;
	}

	/**
	 * Update the list of accounts with provided account.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string|string> $account_data The array of data for an account to add to the list.
	 */
	protected function update_list_of_accounts( $account_data ) {
		$accounts                        = $this->get_list_of_accounts();
		// If there are no options and the original_account is empty, lets save the first account added.
		if ( empty( $accounts ) && ! tribe_get_option( Settings::$option_prefix . 'original_account' ) ) {
			tribe_update_option( Settings::$option_prefix . 'original_account', esc_attr( $account_data['id']  ) );
		}

		$accounts[ esc_attr( $account_data['id'] ) ] = [
			'name'   => esc_attr( $account_data['name'] ),
			'status' => esc_attr( $account_data['status'] ),
		];

		update_option( $this->all_account_key, $accounts );
	}

	/**
	 * Delete from the list of accounts the provided account.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id The id of the single account to save.
	 */
	protected function delete_from_list_of_accounts( $account_id ) {
		$accounts                        = $this->get_list_of_accounts();
		unset( $accounts[ $account_id ] );

		update_option( $this->all_account_key, $accounts );
	}

	/**
	 * Get a Single Zoom Account by id.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id The id of the single account.
	 *
	 * @return array<string|string> $account The Zoom account data.
	 */
	public function get_account_by_id( $account_id ) {
		// Get an account and decrypt the PII.
		$account = get_option( $this->single_account_prefix . $account_id, [] );
		foreach ( $account as $field_key => $value ) {
			if ( ! array_key_exists( $field_key, $this->encrypted_fields ) ) {
				continue;
			}

			$account[ $field_key ] = $this->encryption->decrypt( $value, $this->encrypted_fields[ $field_key ] );
		}

		return $account;
	}

	/**
	 * Set an Account with the provided id.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string|string> $account_data A specific Zoom account data to save.
	 */
	public function set_account_by_id( array $account_data ) {
		update_option( $this->single_account_prefix . $account_data['id'], $account_data, false );

		$this->update_list_of_accounts( $account_data );
	}

	/**
	 * Delete a Zoom account by ID.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id The id of the single account.
	 *
	 * @return bool Whether the account has been deleted and the access token revoked.
	 */
	public function delete_account_by_id( $account_id ) {
		$revoked = $this->revoke_account_by_id( $account_id );

		if ( ! $revoked ) {
			return $revoked;
		}

		delete_option( $this->single_account_prefix . $account_id );

		$this->delete_from_list_of_accounts( $account_id );

		return $revoked;
	}

	/**
	 * Revoke the Zoom accounts access token.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id The id of the single account.
	 *
	 * @return bool Whether the account access token is revoked.
	 */
	protected function revoke_account_by_id( $account_id ) {
		$revoked = false;

		// Load account and get a valid not expired token as only those can be revoked.
		$account_loaded = $this->load_account_by_id( $account_id );
		if ( empty( $account_loaded ) ) {
			return $revoked;
		}

		$revoke_url = Url::$revoke_url;
		if ( defined( 'TEC_VIRTUAL_EVENTS_ZOOM_API_REVOKE_URL' ) ) {
			$revoke_url = TEC_VIRTUAL_EVENTS_ZOOM_API_REVOKE_URL;
		}

		$this->post(
			$revoke_url,
			[
				'headers' => [
					'Authorization' => $this->get_token_authorization_header(),
				],
				'body'    => [
					'token' => $this->access_token,
				],
			],
			Api::OAUTH_POST_RESPONSE_CODE
		)->then(
			function ( array $response ) use ( &$revoked ) {
				if (
					! (
						isset( $response['body'] )
						&& false !== ( $body = json_decode( $response['body'], true ) )
						&& isset( $body['status'] )
						&& 'success' === $body['status']
					)
				) {
					do_action( 'tribe_log', 'error', __CLASS__, [
						'action'   => __METHOD__,
						'message'  => 'Zoom Account Revoke Failed.',
						'response' => $body,
					] );

					return $revoked;
				}

				$revoked = true;

				return $revoked;
			}
		);

		return $revoked;
	}

	/**
	 * Save the account id to the post|event.
	 *
	 * @since 1.5.0
	 *
	 * @param int $post_id The id to save the meta field too.
	 * @param string $account_id The id of the single account to save.
	 *
	 * @return bool|int
	 */
	public function save_account_id_to_post( $post_id, $account_id ) {
		return update_post_meta( $post_id, $this->account_id_meta_field_name, $account_id );
	}

	/**
	 * Set an Account Access Data with the provided id.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id    The id of the single account to save.
	 * @param string $access_token  The Zoom Account API access token.
	 * @param string $refresh_token The Zoom Account API refresh token.
	 * @param string $expiration    The expiration in seconds as provided by the server.
	 */
	public function set_account_access_by_id( $account_id, $access_token, $refresh_token, $expiration ) {
		$account_data                 = $this->get_account_by_id( $account_id );
		$account_data['access_token'] = $this->encryption->encrypt( $access_token );
		$account_data['refresh_token'] = $this->encryption->encrypt( $refresh_token );
		$account_data['expiration']   = $expiration;

		$this->set_account_by_id( $account_data );
	}

	/**
	 * Save a Zoom Account.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string,array> $response An array representing the access token request response, in the format
	 *                                      returned by WordPress `wp_remote_` functions.
	 *
	 * @return bool|mixed The access token for an account.
	 */
	public function save_account( array $response ) {
		if ( ! (
			isset( $response['body'] )
			&& ( false !== $d = json_decode( $response['body'], true ) )
			&& isset( $d['access_token'], $d['refresh_token'], $d['expires_in'] )
		)
		) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'action'  => __METHOD__,
				'code'    => wp_remote_retrieve_response_code( $response ),
				'message' => 'Response body missing or malformed',
			] );

			return false;
		}

		// Set the access token here as we have to call fetch_user immediately, to get the user information.
		$access_token  = $d['access_token'];
		$refresh_token = $d['refresh_token'];
		$expiration    = $this->get_exiration_time_stamp( $d['expires_in'] );

		// Get the user who authorized the account.
		$user         = $this->fetch_user( 'me', false, $access_token);
		if ( empty( $user['id'] ) ) {
			return false;
		}

		$settings         = $this->fetch_user( $user['id'], true, $access_token );
		$account_data     = $this->prepare_account_data( $user, $access_token, $refresh_token, $expiration, $settings, true );
		$existing_account = $this->get_account_by_id( $account_data['id'] );
		$this->set_account_by_id( $account_data );

		$account_msg = $existing_account ?
			_x( 'Zoom connection refreshed for %1$s', 'The refresh message if the account exists.', 'events-virtual' )
			: _x( 'Zoom Account added for %1$s', 'The refresh message if the account exists.', 'events-virtual' );
		$message = sprintf(
			/* Translators: %1$s: the name of the account that has been added or refreshed from Zoom . */
			$account_msg,
			$this->encryption->decrypt( $account_data['name'] )
		);

		set_transient( Settings::$option_prefix . 'account_message', $message, MINUTE_IN_SECONDS );

		return $access_token;
	}

	/**
	 * Save a Zoom Access Token and Expiration information for an Account.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string,array> $response An array representing the access token request response, in the format
	 *                                      returned by WordPress `wp_remote_` functions.
	 *
	 * @return bool Whether the access token has been updated.
	 */
	public function save_access_and_expiration( $id, array $response ) {
		if ( ! (
			isset( $response['body'] )
			&& ( false !== $d = json_decode( $response['body'], true ) )
			&& isset( $d['access_token'], $d['refresh_token'], $d['expires_in'] )
		)
		) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'action'  => __METHOD__,
				'code'    => wp_remote_retrieve_response_code( $response ),
				'message' => 'Response body missing or malformed',
			] );

			return false;
		}

		$access_token  = $d['access_token'];
		$refresh_token = $d['refresh_token'];
		$expiration    = $this->get_exiration_time_stamp( $d['expires_in'] );

		$this->set_account_access_by_id( $id, $access_token, $refresh_token, $expiration );

		return true;
	}

	/**
	 * Prepare a single Zoom's account data to save.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string|string> $user          The user information from Zoom.
	 * @param string               $access_token  The Zoom Account API access token.
	 * @param string               $refresh_token The Zoom Account API refresh token.
	 * @param string               $expiration    The expiration in seconds as provided by the server.
	 * @param array<string|mixed>  $settings      The user settings from Zoom.
	 * @param boolean              $status        The status of the zoom account, whether active or not.
	 *
	 * @return array<string|string> The account information prepared for saving.
	 */
	protected function prepare_account_data( $user, $access_token, $refresh_token, $expiration, $settings, $status ) {
		return [
			'id'            => $user['id'],
			'name'          => $this->encryption->encrypt( $user['first_name'] . ' ' . $user['last_name'] ),
			'email'         => $this->encryption->encrypt( $user['email'] ),
			'access_token'  => $this->encryption->encrypt( $access_token ),
			'refresh_token' => $this->encryption->encrypt( $refresh_token ),
			'expiration'    => $expiration,
			'webinars'      => tribe_is_truthy( $settings['feature']['webinar'] ),
			'status'        => $status,
		];
	}

	/**
	 * Returns the access token based authorization header to send requests to the Zoom API.
	 *
	 * @since 1.5.0
	 *
	 * @return string|boolean The authorization header, to be used in the `headers` section of a request to Zoom API or false if not available.
	 */
	public function get_token_authorization_header( $access_token = '' ) {
		if ( $access_token ) {
			return 'Bearer ' . $access_token;
		}

		if ( $this->access_token ) {
			return 'Bearer ' . $this->access_token;
		}

		return false;
	}

	/**
	 * Get the expiration time stamp.
	 *
	 * @since 1.5.0
	 *
	 * @param string The amount of time in seconds until the access token expires.
	 *
	 * @return string The timestamp when the access token expires.
	 */
	public function get_exiration_time_stamp( $expires_in ) {
		// Take the expiration in seconds as provided by the server and remove a minute to pad for save delays.
		return ( (int) $expires_in ) - MINUTE_IN_SECONDS + current_time( 'timestamp' );

	}

	/**
	 * Get the refresh token.
	 *
	 * @since 1.5.0
	 *
	 * @return string The refresh token.
	 */
	public function get_refresh_token() {
		return $this->refresh_token;
	}

	/**
	 * Maybe refresh the access token or use the saved one.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string|string> $account A Zoom account with the fields to access the API.
	 *
	 * @return bool|mixed|string
	 */
	protected function maybe_refresh_access_token( $account ) {
		// If token is valid, return it to start using it.
		if (
			current_time( 'timestamp' ) <= $account['expiration'] &&
			! empty( $account['access_token'] )
		) {
			return $account['access_token'];
		}

		// Attempt to refresh the token.
		$access_token = $this->refresh_access_token( $account['id'], $account['refresh_token'] );
		if ( empty( $access_token ) ) {
			return false;
		}

		return $access_token;
	}

	/**
	 * Handles the request to select a Zoom account.
	 *
	 * @since 1.5.0
	 *
	 * @param string|null $nonce The nonce that should accompany the request.
	 *
	 * @return bool Whether the request was handled or not.
	 */
	public function ajax_selection( $nonce = null ) {
		if ( ! $this->check_ajax_nonce( static::$select_action, $nonce ) ) {
			return false;
		}

		$event = $this->check_ajax_post();

		if ( ! $event ) {
			return false;
		}

		/** @var \Tribe\Events\Virtual\Meetings\Zoom\Classic_editor */
		$classic_editor = tribe( Classic_Editor::class );

		$zoom_account_id = tribe_get_request_var( 'zoom_account_id' );
		// If no account id found, fail the request.
		if ( empty( $zoom_account_id ) ) {
			$error_message = _x( 'The Zoom Account ID is missing to access the API.', 'Account ID is missing error message.', 'events-virtual' );
			$classic_editor->render_meeting_generation_error_details( $event, $error_message, true );

			wp_die();

			return false;
		}

		$account_loaded = $this->load_account_by_id( $zoom_account_id );
		// If there is no token, then stop as the connection will fail.
		if ( ! $account_loaded ) {
			$error_message = _x( 'The Zoom Account could not be loaded to access the API. Please try refreshing the account in the Events API Settings.', 'Zoom account loading error message.', 'events-virtual' );

			$classic_editor->render_meeting_generation_error_details( $event, $error_message, true );

			wp_die();

			return false;
		}

		$post_id = $event->ID;
		$success = false;

		// get the setup
		$classic_editor->render_meeting_link_generator( $event, true, false, $zoom_account_id );
		$this->save_account_id_to_post( $post_id, $zoom_account_id );

		wp_die();

		return $success;
	}

	/**
	 * Check if a nonce is valid from a list of actions.
	 *
	 * @since 1.5.0
	 *
	 * @param string $nonce  The nonce to check.
	 *
	 * @return bool Whether the nonce is valid or not.
	 */
	protected function is_valid_nonce( $nonce ) {
		/**
		 * Filters a list of Zoom ajax nonce actions.
		 *
		 * @since 1.5.0
		 *
		 * @param array<string,callable> A map from the nonce actions to the corresponding handlers.
		 */
		$actions = apply_filters( 'tribe_events_virtual_meetings_zoom_actions', [] );

		foreach ( $actions as $action => $callback ) {
			if ( $this->check_ajax_nonce( $action, $nonce ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Update the Zoom account on existing events before Multiple Account Support.
	 *
	 * @since 1.5.0
	 *
	 * @param \WP_Post $event The event post object.
	 *
	 * @return bool|void Whether the account has been added.
	 */
	public function update_event_for_multiple_accounts_support( $event ) {

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

		$account_id = get_post_meta( $event->ID, $this->account_id_meta_field_name, true );
		if ( $account_id ) {
			return;
		}

		$account_id = tribe_get_option( Settings::$option_prefix . 'original_account', '' );
		if ( empty( $account_id ) ) {
			return;
		}

		$this->save_account_id_to_post( $event->ID, $account_id );

		return true;
	}
}
