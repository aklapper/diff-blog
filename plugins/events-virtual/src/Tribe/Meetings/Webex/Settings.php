<?php
/**
 * Manages the Webex settings.
 *
 * @since   1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */

namespace Tribe\Events\Virtual\Meetings\Webex;

use Tribe\Events\Virtual\Integrations\Abstract_Settings;
use Tribe\Events\Virtual\Meetings\Webex\Event_Meta as Webex_Meta;

/**
 * Class Settings
 *
 * @since   1.9.0
 * @since   1.11.0 - Change to use an abstract class for shared methods.
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */
class Settings extends Abstract_Settings {

	/**
	 * {@inheritDoc}
	 */
	public static $option_prefix = 'tec_webex_';

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
		self::$api_id                 = Webex_Meta::$key_source_id;
	}

	/**
	 * Returns the current API refresh token.
	 *
	 * If not available, then a new token should be fetched by the API.
	 *
	 * @since      1.11.0
	 * @return string|boolean The API access token, or false if the token cannot be fetched (error).
	 * @deprecated 1.11.0 - Removed unused method, no replacement.
	 *
	 */
	public static function get_refresh_token() {
		_deprecated_function( __METHOD__, '1.11.0', 'No replacement.' );

		return tribe_get_option( static::$option_prefix . 'refresh_token', false );
	}
}
