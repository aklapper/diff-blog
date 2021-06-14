<?php
/**
 * Handles Plugin Updates
 *
 * @since   TBD
 *
 * @package Tribe\Events\Virtual
 */

namespace Tribe\Events\Virtual;

use Tribe\Events\Virtual\Meetings\Zoom\Api;
use Tribe\Events\Virtual\Meetings\Zoom\OAuth;
use Tribe\Events\Virtual\Meetings\Zoom\Settings;
use Tribe__Events__Updater;

/**
 * Class Updater.
 *
 * @since TBD
 *
 * @package Tribe\Events\Virtual
 */
class Updater extends Tribe__Events__Updater {
	/**
	 * Virtual Events reset version.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $reset_version = '1.4';

	/**
	 * Virtual Events Schema Key.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $version_option = 'events-virtual-schema-version';

	/**
	 * Returns an array of callbacks with version strings as keys.
	 * Any key higher than the version recorded in the DB
	 * and lower than $this->current_version will have its
	 * callback called.
	 *
	 * @since TBD
	 *
	 * @return array<string|callback> The version number and callback to use.
	 */
	public function get_update_callbacks() {
		return [
			'1.5' => [ $this, 'multiple_account_migration_setup' ],
		];
	}

	/**
	 * Setup multiple account migration.
	 *
	 * @since TBD
	 *
	 * @return boolean whether the migration to multiple accounts is complete.
	 */
	public function multiple_account_migration_setup() {
		/** @var \Tribe\Events\Virtual\Meetings\Zoom\Api */
		$api = tribe( API::class );

		// Get the latest refresh token and use it refresh and add the account for multiple accounts.
		$refresh_token = tribe_get_option( Settings::$option_prefix . 'refresh_token' );
		$refresh_token = $api->encryption->decrypt( $refresh_token );
		if ( empty( $refresh_token ) ) {
			return false;
		}

		return $this->migrate_zoom_account( $api, $refresh_token );
	}

	/**
	 * Migrate the Zoom Account.
	 *
	 * @since TBD
	 *
	 * @param Api    $api           An instance of the API class.
	 * @param string $refresh_token The refresh token from the connection before multiple accounts.
	 *
	 * @return boolean whether the migration to multiple accounts is complete.
	 */
	public function migrate_zoom_account( $api, $refresh_token ) {
		$refreshed = false;
		$api->post(
			OAuth::$token_request_url,
			[
				'body'    => [
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
				],
			],
			200
		)->then(
			function ( array $response ) use ( &$api, &$refreshed ) {

				if (
					! (
						isset( $response['body'] )
						&& false !== ( $body = json_decode( $response['body'], true ) )
						&& isset( $body['access_token'], $body['refresh_token'], $body['expires_in'] )
					)
				) {
					do_action( 'tribe_log', 'error', __CLASS__, [
						'action'   => __METHOD__,
						'message'  => 'Zoom account migration could not be completed, please manually add your account on the Settings/API section.',
						'response' => $body,
					] );

					return false;
				}

				$refreshed = $api->save_account( $response );

				// Clear existing account settings.
				tribe_update_option( Settings::$option_prefix . 'refresh_token', '' );
				delete_transient( Settings::$option_prefix . 'access_token' );

				// Save the original account id to use to update existing events as they are viewed in the admin.
				$user = $api->fetch_user( 'me', false, $refreshed );
				if ( empty( $user['id'] ) ) {
					return $refreshed;
				}
				tribe_update_option( Settings::$option_prefix . 'original_account', esc_attr( $user['id'] ) );

				return $refreshed;
			}
		);

		return $refreshed;
	}

	/**
	 * Force upgrade script to run even without an existing version number
	 * The version was not previously stored for Virtual Events.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_new_install() {
		return false;
	}
}
