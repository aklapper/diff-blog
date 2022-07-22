<?php
/**
 * Handles the templates modifications required by the Webex API integration.
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */

namespace Tribe\Events\Virtual\Meetings\Webex;

use Tribe\Events\Virtual\Meetings\Webex\Event_Meta as Webex_Event_Meta;
use Tribe\Events\Virtual\Template;
use Tribe\Events\Virtual\Admin_Template;

/**
 * Class Template_Modifications
 *
 * @since 1.9.0
 *
 * @package Tribe\Events\Virtual\Meetings\Webex
 */
class Template_Modifications {

	/**
	 * An instance of the front-end template handler.
	 *
	 * @since 1.9.0
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * An instance of the admin template handler.
	 *
	 * @since 1.9.0
	 *
	 * @var Template
	 */
	protected $admin_template;

	/**
	 * Template_Modifications constructor.
	 *
	 * @since 1.9.0
	 *
	 * @param Template $template An instance of the front-end template handler.
	 */
	public function __construct( Template $template, Admin_Template $admin_template ) {
		$this->template = $template;
		$this->admin_template = $admin_template;
	}

	/**
	 * Adds Webex details to event single.
	 *
	 * @since 1.9.0
	 */
	public function add_event_single_webex_details() {
		// Don't show on password protected posts.
		if ( post_password_required() ) {
			return;
		}

		$event = tribe_get_event( get_the_ID() );

		if (
			empty( $event->virtual )
			|| empty( $event->virtual_meeting )
			|| empty( $event->virtual_should_show_embed )
			|| empty( $event->virtual_meeting_display_details )
			|| Webex_Event_Meta::$key_source_id !== $event->virtual_meeting_provider
		) {
			return;
		}

		/**
		 * Filters whether the link button should open in a new window or not.
		 *
		 * @since 1.9.0
		 *
		 * @param boolean $link_button_new_window  Boolean of if link button should open in new window.
		 */
		$link_button_new_window = apply_filters( 'tec_events_virtual_link_button_new_window', false );

		$link_button_attrs = [];
		if ( ! empty( $link_button_new_window ) ) {
			$link_button_attrs['target'] = '_blank';
		}

		/**
		 * Filters whether the Webex link should open in a new window or not.
		 *
		 * @since 1.9.0
		 *
		 * @param boolean $webex_link_new_window  Boolean of if the Webex link should open in new window.
		 */
		$webex_link_new_window = apply_filters( 'tec_events_virtual_webex_link_new_window', false );

		$webex_link_attrs = [];
		if ( ! empty( $webex_link_new_window ) ) {
			$webex_link_attrs['target'] = '_blank';
		}

		$context = [
			'event'             => $event,
			'link_button_attrs' => $link_button_attrs,
			'webex_link_attrs'  => $webex_link_attrs,
		];

		$this->template->template( 'webex/single/webex-details', $context );
	}

	/**
	 * Adds Webex authorize fields to events->settings->api.
	 *
	 * @since 1.9.0
	 *
	 * @param Api $api An instance of the Webex API handler.
	 * @param Url $url The URLs handler for the integration.
	 *
	 * @return string HTML for the authorize fields.
	 */
	public function get_api_authorize_fields( Api $api, Url $url ) {
		/** @var \Tribe__Cache $cache */
		$cache    = tribe( 'cache' );
		$message = $cache->get_transient( Settings::$option_prefix . 'account_message' );
		if ( $message ) {
			$cache->delete_transient( Settings::$option_prefix . 'account_message' );
		}

		$args = [
			'api'     => $api,
			'url'     => $url,
			'message' => $message,
		];

		return $this->admin_template->template( 'webex/api/authorize-fields', $args, false );
	}

	/**
	 * Gets Webex connect link.
	 *
	 * @since 1.0.1
	 *
	 * @param Api $api An instance of the Webex API handler.
	 * @param Url $url The URLs handler for the integration.
	 *
	 * @return string HTML for the authorize fields.
	 */
	public function get_connect_link( Api $api, Url $url ) {
		$args = [
			'api' => $api,
			'url' => $url,
		];

		return $this->admin_template->template( 'webex/api/authorize-fields/connect-link', $args, false );
	}

	/**
	 * Get intro text for Webex API UI
	 *
	 * @since 1.9.0
	 *
	 * @return string HTML for the intro text.
	 */
	public function get_intro_text() {
		$args = [
			'allowed_html' => [
				'a' => [
					'href'   => [],
					'target' => [],
				],
			],
		];

		return $this->admin_template->template( 'webex/api/intro-text', $args, false );
	}

	/**
	 * Gets Webex disabled connect button.
	 *
	 * @since 1.9.0
	 * @deprecated 1.11.0 - Replaced with Multiple Account Support, see Account_API class.
	 *
	 * @return string HTML for the authorize fields.
	 */
	public function get_disabled_button() {
		_deprecated_function( __METHOD__, '1.11.0', 'No replacement, functionality moved to whodat server.' );

		return $this->admin_template->template( 'webex/api/authorize-fields/disabled-button', [], false );
	}
}
