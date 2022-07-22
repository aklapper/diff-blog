<?php
/**
 * Handles the templates modifications required by the Google API integration.
 *
 * @since 1.11.0
 *
 * @package Tribe\Events\Virtual\Meetings\Google
 */

namespace Tribe\Events\Virtual\Meetings\Google;

use Tribe\Events\Virtual\Template;
use Tribe\Events\Virtual\Admin_Template;
use Tribe\Events\Virtual\Meetings\Google\Event_Meta as Google_Event_Meta;

/**
 * Class Template_Modifications
 *
 * @since 1.11.0
 *
 * @package Tribe\Events\Virtual\Meetings\Google
 */
class Template_Modifications {

	/**
	 * An instance of the front-end template handler.
	 *
	 * @since 1.11.0
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * An instance of the admin template handler.
	 *
	 * @since 1.11.0
	 *
	 * @var Template
	 */
	protected $admin_template;

	/**
	 * Template_Modifications constructor.
	 *
	 * @since 1.11.0
	 *
	 * @param Template $template An instance of the front-end template handler.
	 */
	public function __construct( Template $template, Admin_Template $admin_template ) {
		$this->template       = $template;
		$this->admin_template = $admin_template;
	}

	/**
	 * Adds Google authorize fields to events->settings->api.
	 *
	 * @since 1.11.0
	 *
	 * @param Api $api An instance of the Google API handler.
	 * @param Url $url The URLs handler for the integration.
	 *
	 * @return string HTML for the authorize fields.
	 */
	public function get_api_authorize_fields( Api $api, Url $url ) {
		/** @var \Tribe__Cache $cache */
		$cache   = tribe( 'cache' );
		$message = $cache->get_transient( Settings::$option_prefix . 'account_message' );
		if ( $message ) {
			$cache->delete_transient( Settings::$option_prefix . 'account_message' );
		}

		$args = [
			'api'     => $api,
			'url'     => $url,
			'message' => $message,
		];

		return $this->admin_template->template( 'google/api/authorize-fields', $args, false );
	}

	/**
	 * Get intro text for Google API UI
	 *
	 * @since 1.11.0
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

		return $this->admin_template->template( 'google/api/intro-text', $args, false );
	}

	/**
	 * Adds Google details to event single.
	 *
	 * @since 1.11.0
	 */
	public function add_event_single_google_details() {
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
			|| Google_Event_Meta::$key_source_id !== $event->virtual_meeting_provider
		) {
			return;
		}

		/**
		 * Filters whether the link button should open in a new window or not.
		 *
		 * @since 1.11.0
		 *
		 * @param boolean $link_button_new_window  Boolean of if link button should open in new window.
		 */
		$link_button_new_window = apply_filters( 'tec_events_virtual_link_button_new_window', false );

		$link_button_attrs = [];
		if ( ! empty( $link_button_new_window ) ) {
			$link_button_attrs['target'] = '_blank';
		}

		/**
		 * Filters whether the Google link should open in a new window or not.
		 *
		 * @since 1.11.0
		 *
		 * @param boolean $google_link_new_window  Boolean of if the Google link should open in new window.
		 */
		$google_link_new_window = apply_filters( 'tec_events_virtual_google_link_new_window', false );

		$google_link_attrs = [];
		if ( ! empty( $google_link_new_window ) ) {
			$google_link_attrs['target'] = '_blank';
		}

		$context = [
			'event'             => $event,
			'link_button_attrs' => $link_button_attrs,
			'google_link_attrs'  => $google_link_attrs,
		];

		$this->template->template( 'google/single/google-details', $context );
	}
}
