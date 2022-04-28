<?php
/**
 * Manages the Zoom URLs for the plugin.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Plugin;

/**
 * Class Url
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Url {
	/**
	 * The base URL that should be used to authorize the Zoom App.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 - update to use new Zoom App endpoint.
	 *
	 * @var string
	 */
	public static $authorize_url = 'https://whodat.theeventscalendar.com/oauth/zoom/v2/authorize';

	/**
	 * The base URL that should be used to deauthorize the Zoom App.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 - update to use new Zoom App endpoint.
	 *
	 * @var string
	 */
	public static $revoke_url = 'https://whodat.theeventscalendar.com/oauth/zoom/v2/revoke';

	/**
	 * The base URL that should was previously used to deauthorize the Zoom App.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @var string
	 */
	public static $legacy_revoke_url = 'https://zoom.us/oauth/revoke';

	/**
	 * The current Zoom API handler instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * An instance of the API OAuth handler.
	 *
	 * @since 1.0.0
	 *
	 * @var OAuth
	 */
	protected $oauth;

	/**
	 * Url constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Api   $api   An instance of the Zoom API handler.
	 * @param OAuth $oauth An instance of the API OAuth handler.
	 */
	public function __construct( Api $api, OAuth $oauth ) {
		$this->api   = $api;
		$this->oauth = $oauth;
	}

	/**
	 * Returns the URL to disconnect from the Zoom API.
	 *
	 * The current version (2.0) of Zoom API does not provide a de-authorization endpoint, as such the best way to
	 * disconnect the application is to de-authorize its access token.
	 *
	 * @since 1.0.0
	 *
	 * @param string $current_url The URL to return to after a successful disconnection.
	 *
	 * @return string The URL to disconnect from the Zoom API.
	 *
	 * @link  https://marketplace.zoom.us/docs/guides/auth/oauth#revoking
	 */
	public function to_disconnect( $current_url = null ) {
		return add_query_arg( [
			Plugin::$request_slug => wp_create_nonce( OAuth::$deauthorize_nonce_action ),
		], Settings::admin_url() );
	}

	/**
	 * Returns the URL to authorize the use of the Zoom API.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Add a constant to be able to change the authorize url.
	 *
	 * @return string The request URL.
	 *
	 * @link  https://marketplace.zoom.us/docs/guides/auth/oauth
	 */
	public function to_authorize() {
		$license = get_option( 'pue_install_key_events_virtual' );

		$authorize_url = self::$authorize_url;
		if ( defined( 'TEC_VIRTUAL_EVENTS_ZOOM_API_AUTHORIZE_URL' ) ) {
			$authorize_url = TEC_VIRTUAL_EVENTS_ZOOM_API_AUTHORIZE_URL;
		}

		$real_url = add_query_arg( [
			'key'          => $license ? $license : 'no-license',
			'redirect_uri' => esc_url( $this->oauth->authorize_url() ),
		],
			$authorize_url
		);

		return $real_url;
	}

	/**
	 * Returns the URL that should be used to generate a Zoom API meeting link.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post|null $post A post object to generate the meeting for.
	 *
	 * @return string The URL to generate the Zoom Meeting.
	 */
	public function to_generate_meeting_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Meetings::$create_action );

		return add_query_arg( [
			'action'              => 'ev_zoom_meetings_create',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to update a Zoom API meeting link.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_Post|null $post A post object to update the meeting for.
	 *
	 * @return string The URL to update the Zoom Meeting.
	 */
	public function to_update_meeting_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Meetings::$update_action );

		return add_query_arg( [
			'action'              => 'ev_zoom_meetings_update',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to remove an event Zoom Meeting URL.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post A post object to remove the meeting from.
	 *
	 * @return string The URL to remove the Zoom Meeting.
	 */
	public function to_remove_meeting_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Meetings::$remove_action );

		return add_query_arg(
			[
				'action'              => 'ev_zoom_meetings_remove',
				Plugin::$request_slug => $nonce,
				'post_id'             => $post->ID,
				'_ajax_nonce'         => $nonce,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Returns the URL that should be used to generate a Zoom API webinar link.
	 *
	 * @since 1.1.1
	 *
	 * @param \WP_Post|null $post A post object to generate the webinar for.
	 *
	 * @return string The URL to generate the Zoom Webinar.
	 */
	public function to_generate_webinar_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Webinars::$create_action );

		return add_query_arg(
			[
				'action'              => 'ev_zoom_webinars_create',
				Plugin::$request_slug => $nonce,
				'post_id'             => $post->ID,
				'_ajax_nonce'         => $nonce,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Returns the URL that should be used to update a Zoom API webinar link.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_Post|null $post A post object to update the webinar for.
	 *
	 * @return string The URL to update the Zoom Webinar.
	 */
	public function to_update_webinar_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Webinars::$update_action );

		return add_query_arg(
			[
				'action'              => 'ev_zoom_webinars_update',
				Plugin::$request_slug => $nonce,
				'post_id'             => $post->ID,
				'_ajax_nonce'         => $nonce,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Returns the URL that should be used to remove an event Zoom Webinar URL.
	 *
	 * @since 1.1.1
	 *
	 * @param \WP_Post $post A post object to remove the webinar from.
	 *
	 * @return string The URL to remove the Zoom Webinars.
	 */
	public function to_remove_webinar_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( Webinars::$remove_action );

		return add_query_arg(
			[
				'action'              => 'ev_zoom_webinars_remove',
				Plugin::$request_slug => $nonce,
				'post_id'             => $post->ID,
				'_ajax_nonce'         => $nonce,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Returns the URL that should be used to select an account to setup for the Zoom API.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post|null $post A post object to generate the meeting for.
	 *
	 * @return string The URL to select the Zoom account.
	 */
	public function to_select_account_link( \WP_Post $post ) {
		$nonce = wp_create_nonce( API::$select_action );

		return add_query_arg( [
			'action'              => 'ev_zoom_account_select',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to change an account status.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id The Zoom Account ID to change the status.
	 *
	 * @return string The URL to change an account status.
	 */
	public function to_change_account_status_link( $account_id ) {
		$nonce = wp_create_nonce( API::$status_action );

		return add_query_arg( [
			'action'              => 'ev_zoom_settings_account_status',
			Plugin::$request_slug => $nonce,
			'account_id'     => $account_id,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to check a Zoom users account type.
	 *
	 * @since 1.8.2
	 *
	 * @return string The URL to validate the Zoom user type.
	 */
	public function to_validate_user_type( \WP_Post $post ) {
		$nonce = wp_create_nonce( Users::$validate_user_action );

		return add_query_arg( [
			'action'              => 'ev_zoom_validate_user_type',
			Plugin::$request_slug => $nonce,
			'post_id'             => $post->ID,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Returns the URL that should be used to delete an account.
	 *
	 * @since 1.5.0
	 *
	 * @param string $account_id The Zoom Account ID to change the status.
	 *
	 * @return string The URL to delete an account.
	 */
	public function to_delete_account_link( $account_id ) {
		$nonce = wp_create_nonce( API::$delete_action );

		return add_query_arg( [
			'action'              => 'ev_zoom_settings_delete_account',
			Plugin::$request_slug => $nonce,
			'account_id'     => $account_id,
			'_ajax_nonce'         => $nonce,
		], admin_url( 'admin-ajax.php' ) );
	}
}
