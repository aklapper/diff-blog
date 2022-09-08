<?php
/**
 * Manages the Microsoft URLs for the plugin.
 *
 * @since   1.13.0
 *
 * @package Tribe\Events\Virtual\Meetings\Microsoft
 */

namespace Tribe\Events\Virtual\Meetings\Microsoft;

use Tribe\Events\Virtual\Integrations\Abstract_Url;
use Tribe\Events\Virtual\Meetings\Microsoft\Event_Meta as Microsoft_Event_Meta;

/**
 * Class Url
 *
 * @since   1.13.0
 *
 * @package Tribe\Events\Virtual\Meetings\Microsoft
 */
class Url extends Abstract_Url {

	/**
	 * Url constructor.
	 *
	 * @since 1.13.0
	 *
	 * @param Api     $api     An instance of the Microsoft API handler.
	 * @param Actions $actions An instance of the Microsoft Actions handler.
	 */
	public function __construct( Api $api, Actions $actions ) {
		$this->api           = $api;
		self::$api_id        = Microsoft_Event_Meta::$key_source_id;
		self::$authorize_url = 'https://whodat.theeventscalendar.com/oauth/microsoft/v1/authorize';
		self::$refresh_url   = 'https://whodat.theeventscalendar.com/oauth/microsoft/v1/refresh';
		self::$revoke_url    = 'https://whodat.theeventscalendar.com/oauth/microsoft/v1/revoke';
		$this->actions       = $actions;
	}
}
