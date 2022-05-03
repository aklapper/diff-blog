<?php
/**
 * Manages the Zoom settings for the extension.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Admin_Template;
use Tribe\Events\Virtual\Encryption;
use Tribe__Main as Common;
use Tribe\Events\Virtual\Traits\With_AJAX;

/**
 * Class Settings
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Settings {
	use With_AJAX;

	/**
	 * The prefix, in the context of tribe options, of each setting for this extension.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $option_prefix = 'tribe_zoom_';

	/**
	 * The name of the action used to change the status of an account.
	 *
	 * @since 1.5.0
	 * @deprecated 1.9.0 - use API::status_action
	 *
	 * @var string
	 */
	public static $status_action = 'events-virtual-meetings-zoom-settings-status';

	/**
	 * The name of the action used to delete an account.
	 *
	 * @since 1.5.0
	 * @deprecated 1.9.0 - use API::delete_action
	 *
	 * @var string
	 */
	public static $delete_action = 'events-virtual-meetings-zoom-settings-delete';

	/**
	 * The URL handler instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Url
	 */
	protected $url;

	/**
	 * An instance of the Zoom API handler.
	 *
	 * @since 1.0.0
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Settings constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Api $api An instance of the Zoom API handler.
	 * @param Url $url An instance of the URL handler.
	 */
	public function __construct( Api $api, Url $url, Admin_Template $template ) {
		$this->url        = $url;
		$this->api        = $api;
	}

	/**
	 * Returns the URL of the Settings URL page.
	 *
	 * @since 1.0.0
	 *
	 * @return string The URL of the Zoom API integration settings page.
	 */
	public static function admin_url() {
		return admin_url( 'edit.php?post_type=tribe_events&page=tribe-common&tab=addons' );
	}

	/**
	 * Adds the Zoom API fields to the ones in the Events > Settings > APIs tab.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,array> $fields The current fields.
	 *
	 * @return array<string,array> The fields, as updated by the settings.
	 */
	public function add_fields( array $fields = [] ) {
		$wrapper_classes = tribe_get_classes(
			[
				'tec-settings-api-application' => true,
				'tribe-settings-zoom-application' => true,
			]
		);

		$zoom_fields = [
			static::$option_prefix . 'wrapper_open'  => [
				'type' => 'html',
				'html' => '<div id="tribe-settings-zoom-application" data-nonce="' . wp_create_nonce( OAuth::$client_keys_autosave_nonce_action ) . '" class="' . implode( ' ', $wrapper_classes ) . '">',
			],
			static::$option_prefix . 'header'        => [
				'type' => 'html',
				'html' => $this->get_intro_text(),
			],
			static::$option_prefix . 'authorize'     => [
				'type' => 'html',
				'html' => $this->get_authorize_fields(),
			],
			static::$option_prefix . 'wrapper_close' => [
				'type' => 'html',
				'html' => '<div class="clear"></div></div>',
			],
		];

		/**
		 * Filters the Zoom API settings shown to the user in the Events > Settings > APIs screen.
		 *
		 * @since  1.0.0
		 *
		 * @param array<string,array> A map of the Zoom API fields that will be printed on the page.
		 * @param Settings $this This Settings instance.
		 */
		$zoom_fields = apply_filters( 'tribe_events_virtual_meetings_zoom_settings_fields', $zoom_fields, $this );

		// Insert the link after the other APIs and before the Google Maps API ones.
		$fields = Common::array_insert_before_key(
			'gmaps-js-api-start',
			$fields,
			$zoom_fields
		);

		return $fields;
	}

	/**
	 * Provides the introductory text to the set up and configuration of the Zoom API integration.
	 *
	 * @since 1.0.0
	 *
	 * @return string The introductory text to the the set up and configuration of the Zoom API integration.
	 */
	protected function get_intro_text() {
		return tribe( Template_Modifications::class )->get_intro_text();
	}

	/**
	 * Get the API authorization fields.
	 *
	 * @since 1.0.0
	 *
	 * @return string The HTML fields.
	 */
	protected function get_authorize_fields() {
		return tribe( Template_Modifications::class )->get_api_authorize_fields( $this->api, $this->url );
	}

	/**
	 * Gets the connect link for the ajax call.
	 *
	 * @since 1.0.1
	 *
	 * @return string HTML.
	 */
	public function get_connect_link() {
		return tribe( Template_Modifications::class )->get_connect_link( $this->api, $this->url );
	}

	/**
	 * Gets the disabled button for the ajax call.
	 *
	 * @since 1.0.1
	 *
	 * @return string HTML.
	 */
	public function get_disabled_button() {
		return tribe( Template_Modifications::class )->get_disabled_button();
	}

	/**
	 * The message template to display on user account changes.
	 *
	 * @since 1.5.0
	 *
	 * @param string $message The message to display.
	 * @param string $type    The type of message, either standard or error.
	 *
	 * @return string The message with html to display
	 */
	public function get_settings_message_template( $message, $type = 'standard' ) {
		return tribe( Template_Modifications::class )->get_settings_message_template( $message, $type );
	}

	/**
	 * Handles the request to change the status of a Zoom account.
	 *
	 * @since 1.5.0
	 * @deprecated 1.9.0 - Uses shared method in API class.
	 *
	 * @param string|null $nonce The nonce that should accompany the request.
	 *
	 * @return bool Whether the request was handled or not.
	 */
	public function ajax_status( $nonce = null ) {
		_deprecated_function( __METHOD__, '1.9.0', 'Api::ajax_status()' );

		return tribe( Api::class )->ajax_status( $nonce );
	}

	/**
	 * Handles the request to delete a Zoom account.
	 *
	 * @since 1.5.0
	 * @deprecated 1.9.0 - Uses shared method in API class.
	 *
	 * @param string|null $nonce The nonce that should accompany the request.
	 *
	 * @return bool Whether the request was handled or not.
	 */
	public function ajax_delete( $nonce = null ) {
		_deprecated_function( __METHOD__, '1.9.0', 'API::ajax_delete()' );

		return tribe( Api::class )->ajax_delete( $nonce );
	}
}
