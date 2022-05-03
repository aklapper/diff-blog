<?php
/**
 * Manages the Webex settings.
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */

namespace Tribe\Events\Virtual\Meetings\Webex;

use Tribe\Events\Virtual\Traits\With_AJAX;

/**
 * Class Settings
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */
class Settings {
	use With_AJAX;

	/**
	 * The prefix, in the context of tribe options, of each setting for this extension.
	 *
	 * @since 1.9.0
	 *
	 * @var string
	 */
	public static $option_prefix = 'tec_webex_';

	/**
	 * An instance of the Webex API handler.
	 *
	 * @since 1.9.0
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * An instance of the Webex Template_Modifications.
	 *
	 * @since 1.9.0
	 *
	 * @var Template_Modifications
	 */
	protected $template_modifications;

	/**
	 * The Webex URL handler instance.
	 *
	 * @since 1.9.0
	 *
	 * @var Url
	 */
	protected $url;

	/**
	 * Settings constructor.
	 *
	 * @since 1.9.0
	 *
	 * @param Api                    $api                    An instance of the Webex API handler.
	 * @param Template_Modifications $template_modifications An instance of the Template_Modifications handler.
	 * @param Url                    $url                    An instance of the URL handler.
	 */
	public function __construct( Api $api, Template_Modifications $template_modifications, Url $url ) {
		$this->api                    = $api;
		$this->template_modifications = $template_modifications;
		$this->url                    = $url;
	}

	/**
	 * Returns the URL of the Settings URL page.
	 *
	 * @since 1.9.0
	 *
	 * @return string The URL of the Webex API integration settings page.
	 */
	public static function admin_url() {
		return admin_url( 'edit.php?post_type=tribe_events&page=tribe-common&tab=addons' );
	}

	/**
	 * Returns the current API refresh token.
	 *
	 * If not available, then a new token should be fetched by the API.
	 *
	 * @since 1.9.0
	 *
	 * @return string|boolean The API access token, or false if the token cannot be fetched (error).
	 */
	public static function get_refresh_token() {
		return tribe_get_option( static::$option_prefix . 'refresh_token', false );
	}

	/**
	 * Adds the Webex API fields to the ones in the Events > Settings > APIs tab.
	 *
	 * @since 1.9.0
	 *
	 * @param array<string,array> $fields The current fields.
	 *
	 * @return array<string,array> The fields, as updated by the settings.
	 */
	public function add_fields( array $fields = [] ) {
		$wrapper_classes = tribe_get_classes(
			[
				'tec-settings-api-application' => true,
				'tec-events-settings-webex-application' => true,
			]
		);

		$webex_fields = [
			static::$option_prefix . 'wrapper_open'  => [
				'type' => 'html',
				'html' => '<div id="tribe-settings-webex-application" class="' . implode( ' ', $wrapper_classes ) . '">',
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
		 * Filters the Webex API settings shown to the user in the Events > Settings > APIs screen.
		 *
		 * @since 1.9.0
		 *
		 * @param array<string,array> A map of the Webex API fields that will be printed on the page.
		 * @param Settings $this This Settings instance.
		 */
		$webex_fields = apply_filters( 'tec_events_virtual_meetings_webex_settings_fields', $webex_fields, $this );

		// Insert the link after the other APIs and before the Google Maps API ones.
		$gmaps_fields = array_splice( $fields, array_search( 'gmaps-js-api-start', array_keys( $fields ) ) );

		$fields = array_merge( $fields, $webex_fields, $gmaps_fields );

		return $fields;
	}

	/**
	 * Provides the introductory text to the set up and configuration of the Webex API integration.
	 *
	 * @since 1.9.0
	 *
	 * @return string The introductory text to the the set up and configuration of the Webex API integration.
	 */
	protected function get_intro_text() {
		return $this->template_modifications->get_intro_text();
	}

	/**
	 * Get the API authorization fields.
	 *
	 * @since 1.9.0
	 *
	 * @return string The HTML fields.
	 */
	protected function get_authorize_fields() {
		return $this->template_modifications->get_api_authorize_fields( $this->api, $this->url );
	}
}
