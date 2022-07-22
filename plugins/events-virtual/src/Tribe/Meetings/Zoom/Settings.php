<?php
/**
 * Manages the Zoom settings for the extension.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Integrations\Abstract_Settings;
use Tribe\Events\Virtual\Meetings\Zoom\Event_Meta as Zoom_Meta;

/**
 * Class Settings
 *
 * @since   1.0.0
 * @since   1.11.0 - Change to use an abstract class for shared methods.
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Settings extends Abstract_Settings {

	/**
	 * {@inheritDoc}
	 */
	public static $option_prefix = 'tribe_zoom_';

	/**
	 * The name of the action used to change the status of an account.
	 *
	 * @since      1.5.0
	 * @deprecated 1.9.0 - use API::status_action
	 *
	 * @var string
	 */
	public static $status_action = 'events-virtual-meetings-zoom-settings-status';

	/**
	 * The name of the action used to delete an account.
	 *
	 * @since      1.5.0
	 * @deprecated 1.9.0 - use API::delete_action
	 *
	 * @var string
	 */
	public static $delete_action = 'events-virtual-meetings-zoom-settings-delete';

	/**
	 * Settings constructor.
	 *
	 * @since 1.0.0
	 * @since 1.11.0 - Add Template Modifications
	 *
	 * @param Api                    $api                    An instance of the Zoom API handler.
	 * @param Url                    $url                    An instance of the URL handler.
	 * @param Template_Modifications $template_modifications An instance of the Template_Modifications handler.
	 */
	public function __construct( Api $api, Url $url, Template_Modifications $template_modifications ) {
		$this->url                    = $url;
		$this->api                    = $api;
		$this->template_modifications = $template_modifications;
		self::$api_id                 = Zoom_Meta::$key_source_id;
	}

	/**
	 * Handles the request to change the status of a Zoom account.
	 *
	 * @since      1.5.0
	 *
	 * @param string|null $nonce The nonce that should accompany the request.
	 *
	 * @return bool Whether the request was handled or not.
	 * @deprecated 1.9.0 - Uses shared method in API class.
	 *
	 */
	public function ajax_status( $nonce = null ) {
		_deprecated_function( __METHOD__, '1.9.0', 'Api::ajax_status()' );

		return tribe( Api::class )->ajax_status( $nonce );
	}

	/**
	 * Handles the request to delete a Zoom account.
	 *
	 * @since      1.5.0
	 *
	 * @param string|null $nonce The nonce that should accompany the request.
	 *
	 * @return bool Whether the request was handled or not.
	 * @deprecated 1.9.0 - Uses shared method in API class.
	 *
	 */
	public function ajax_delete( $nonce = null ) {
		_deprecated_function( __METHOD__, '1.9.0', 'API::ajax_delete()' );

		return tribe( Api::class )->ajax_delete( $nonce );
	}

	/**
	 * Gets the connect link for the ajax call.
	 *
	 * @since      1.0.1
	 * @return string HTML.
	 * @deprecated 1.11.0 - Replaced with Multiple Account Support, see Account_API class.
	 *
	 */
	public function get_connect_link() {
		_deprecated_function( __METHOD__, '1.11.0', 'No replacement, functionality moved to whodat server.' );

		return tribe( Template_Modifications::class )->get_connect_link( $this->api, $this->url );
	}

	/**
	 * Gets the disabled button for the ajax call.
	 *
	 * @since      1.0.1
	 * @return string HTML.
	 * @deprecated 1.11.0 - Replaced with Multiple Account Support, see Account_API class.
	 *
	 */
	public function get_disabled_button() {
		_deprecated_function( __METHOD__, '1.11.0', 'No replacement, functionality moved to whodat server.' );

		return tribe( Template_Modifications::class )->get_disabled_button();
	}

	/**
	 * The message template to display on user account changes.
	 *
	 * @since      1.5.0
	 *
	 * @param string $message The message to display.
	 * @param string $type    The type of message, either standard or error.
	 *
	 * @return string The message with html to display
	 * @deprecated 1.11.0 - Use direct call to Zoom's Template_Modifications->get_settings_message_template()
	 *
	 */
	public function get_settings_message_template( $message, $type = 'standard' ) {
		_deprecated_function( __METHOD__, '1.11.0', 'Template_Modifications->get_settings_message_template()' );

		return tribe( Template_Modifications::class )->get_settings_message_template( $message, $type );
	}
}
