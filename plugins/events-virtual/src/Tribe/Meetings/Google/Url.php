<?php
/**
 * Manages the Google URLs for the plugin.
 *
 * @since 1.11.0
 *
 * @package Tribe\Events\Virtual\Meetings\Google
 */

namespace Tribe\Events\Virtual\Meetings\Google;

use Tribe\Events\Virtual\Plugin;

/**
 * Class Url
 *
 * @since 1.11.0
 *
 * @package Tribe\Events\Virtual\Meetings\Google
 */
class Url {
	/**
	 * The base URL that should be used to authorize the Google App.
	 *
	 * @since 1.11.0
	 *
	 * @var string
	 */
	public static $authorize_url = 'https://whodat.theeventscalendar.com/oauth/google/v1/authorize';

	/**
	 * The base URL to request an access token to Google API.
	 *
	 * @since 1.11.0
	 *
	 * @var string
	 *
	 */
	public static $refresh_url = 'https://whodat.theeventscalendar.com/oauth/google/v1/refresh';

	/**
	 * The base URL to revoke an authorized account.
	 *
	 * @since 1.11.0
	 *
	 * @var string
	 */
	public static $revoke_url = 'https://whodat.theeventscalendar.com/oauth/google/v1/revoke';

	/**
	 * The current Google API handler instance.
	 *
	 * @since 1.11.0
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Url constructor.
	 *
	 * @since 1.11.0
	 *
	 * @param Api   $api   An instance of the Google API handler.
	 */
	public function __construct( Api $api ) {
		$this->api   = $api;
	}

	/**
	 * Returns the URL to authorize the use of the Google API.
	 *
	 * @since 1.11.0
	 *
	 * @return string The request URL.
	 */
	public function to_authorize() {
		$authorize_url = static::$authorize_url;

		if ( defined( 'TEC_VIRTUAL_EVENTS_GOOGLE_API_AUTHORIZE_URL' ) ) {
			$authorize_url = TEC_VIRTUAL_EVENTS_GOOGLE_API_AUTHORIZE_URL;
		}

		$real_url = add_query_arg( [
			'redirect_uri'  => esc_url( admin_url() ),
			'state' => wp_create_nonce( API::$authorize_nonce_action ),
		],
			$authorize_url
		);

		return $real_url;
	}

	/**
	 * Returns the URL to refresh a token.
	 *
	 * @since 1.11.0
	 *
	 * @return string The request URL.
	 */
	public static function to_refresh() {
		$refresh_url = Url::$refresh_url;
		if ( defined( 'TEC_VIRTUAL_EVENTS_GOOGLE_API_REFRESH_URL' ) ) {
			$refresh_url = TEC_VIRTUAL_EVENTS_GOOGLE_API_REFRESH_URL;
		}

		return $refresh_url;
	}

	/**
	 * Returns the URL that should be used to change an account status to enabled or disabled in the settings.
	 *
	 * @since 1.11.0
	 *
	 * @param string $account_id The Google Account ID to change the status.
	 *
	 * @return string The URL to change an account status.
	 */
	public function to_change_account_status_link( $account_id ) {
		$nonce = wp_create_nonce( API::$status_action );

		return add_query_arg( [
			'action'              => 'ev_google_settings_account_status',
			Plugin::$request_slug => $nonce,
			'account_id'          => $account_id,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to delete an account in the settings.
	 *
	 * @since 1.11.0
	 *
	 * @param string $account_id The Google Account ID to change the status.
	 *
	 * @return string The URL to delete an account.
	 */
	public function to_delete_account_link( $account_id ) {
		$nonce = wp_create_nonce( API::$delete_action );

		return add_query_arg( [
			'action'              => 'ev_google_settings_delete_account',
			Plugin::$request_slug => $nonce,
			'account_id'          => $account_id,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to select an account to setup for the Google API.
	 *
	 * @since 1.11.0
	 *
	 * @param \WP_Post|null $post A post object of the event.
	 *
	 * @return string The URL to select the Google account.
	 */
	public function to_select_account_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( API::$select_action );

		return add_query_arg( [
			'action'              => 'ev_google_account_select',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to remove an event Google Meet URL.
	 *
	 * @since 1.11.0
	 *
	 * @param \WP_Post $post A post object to remove the meeting from.
	 *
	 * @return string The URL to remove the Google Meet.
	 */
	public function to_remove_meeting_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Meetings::$remove_action );

		return add_query_arg(
			[
				'action'              => 'ev_google_meetings_remove',
				Plugin::$request_slug => $nonce,
				'post_id'             => $post->ID,
				'_ajax_nonce'         => $nonce,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Returns the URL that should be used to generate a Google API meeting link.
	 *
	 * @since 1.11.0
	 *
	 * @param \WP_Post|null $post A post object to generate the meeting for.
	 *
	 * @return string The URL to generate the Google Meet.
	 */
	public function to_generate_meeting_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Meetings::$create_action );

		return add_query_arg( [
			'action'              => 'ev_google_meetings_create',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}
}
