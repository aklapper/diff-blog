<?php
/**
 * Manages the Webex URLs for the plugin.
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */

namespace Tribe\Events\Virtual\Meetings\Webex;

use Tribe\Events\Virtual\Plugin;

/**
 * Class Url
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */
class Url {
	/**
	 * The base URL that should be used to authorize the Webex App.
	 *
	 * @since 1.9.0
	 *
	 * @var string
	 */
	public static $authorize_url = 'https://whodat.theeventscalendar.com/oauth/webex/v1/authorize';

	/**
	 * The base URL to request an access token to Webex API.
	 *
	 * @since 1.9.0
	 *
	 * @var string
	 *
	 * @link  https://marketplace.webex.us/docs/guides/auth/oauth
	 */
	public static $token_request_url = 'https://whodat.theeventscalendar.com/oauth/webex/v1/token';

	/**
	 * The base URL to revoke an authorized account.
	 *
	 * @since 1.9.0
	 *
	 * @var string
	 */
	public static $revoke_url = 'https://whodatdev.theeventscalendar.com/oauth/webex/v1/revoke';

	/**
	 * The current Webex API handler instance.
	 *
	 * @since 1.9.0
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Url constructor.
	 *
	 * @since 1.9.0
	 *
	 * @param Api   $api   An instance of the Webex API handler.
	 */
	public function __construct( Api $api ) {
		$this->api   = $api;
	}

	/**
	 * Returns the URL to authorize the use of the Webex API.
	 *
	 * @since 1.9.0
	 *
	 * @return string The request URL.
	 */
	public function to_authorize() {
		$authorize_url = static::$authorize_url;

		if ( defined( 'TEC_VIRTUAL_EVENTS_WEBEX_API_AUTHORIZE_URL' ) ) {
			$authorize_url = TEC_VIRTUAL_EVENTS_WEBEX_API_AUTHORIZE_URL;
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
	 * Returns the URL that should be used to select an account to setup for the Webex API.
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post|null $post A post object of the event.
	 *
	 * @return string The URL to select the Webex account.
	 */
	public function to_select_account_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( API::$select_action );

		return add_query_arg( [
			'action'              => 'ev_webex_account_select',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to change an account status to enabled or disabled in the settings.
	 *
	 * @since 1.9.0
	 *
	 * @param string $account_id The Webex Account ID to change the status.
	 *
	 * @return string The URL to change an account status.
	 */
	public function to_change_account_status_link( $account_id ) {
		$nonce = wp_create_nonce( API::$status_action );

		return add_query_arg( [
			'action'              => 'ev_webex_settings_account_status',
			Plugin::$request_slug => $nonce,
			'account_id'     => $account_id,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to delete an account in the settings.
	 *
	 * @since 1.9.0
	 *
	 * @param string $account_id The Webex Account ID to change the status.
	 *
	 * @return string The URL to delete an account.
	 */
	public function to_delete_account_link( $account_id ) {
		$nonce = wp_create_nonce( API::$delete_action );

		return add_query_arg( [
			'action'              => 'ev_webex_settings_delete_account',
			Plugin::$request_slug => $nonce,
			'account_id'     => $account_id,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to remove an event Webex Meeting URL.
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post $post A post object to remove the meeting from.
	 *
	 * @return string The URL to remove the Webex Meeting.
	 */
	public function to_remove_meeting_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Meetings::$remove_action );

		return add_query_arg(
			[
				'action'              => 'ev_webex_meetings_remove',
				Plugin::$request_slug => $nonce,
				'post_id'             => $post->ID,
				'_ajax_nonce'         => $nonce,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Returns the URL that should be used to generate a Webex API meeting link.
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post|null $post A post object to generate the meeting for.
	 *
	 * @return string The URL to generate the Webex Meeting.
	 */
	public function to_generate_meeting_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Meetings::$create_action );

		return add_query_arg( [
			'action'              => 'ev_webex_meetings_create',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

}
