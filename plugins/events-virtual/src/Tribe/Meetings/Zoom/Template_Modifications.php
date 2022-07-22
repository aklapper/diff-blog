<?php
/**
 * Handles the templates modifications required by the Zoom API integration.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;

use Tribe\Events\Virtual\Meetings\Zoom_Provider;
use Tribe\Events\Virtual\Template;
use Tribe\Events\Virtual\Admin_Template;

/**
 * Class Template_Modifications
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Template_Modifications {

	/**
	 * An instance of the front-end template handler.
	 *
	 * @since 1.0.0
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * An instance of the admin template handler.
	 *
	 * @since 1.0.0
	 *
	 * @var Admin_Template
	 */
	protected $admin_template;

	/**
	 * Template_Modifications constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Template       $template An instance of the front-end template handler.
	 * @param Admin_Template $template An instance of the backend template handler.
	 */
	public function __construct( Template $template, Admin_Template $admin_template ) {
		$this->template       = $template;
		$this->admin_template = $admin_template;
	}

	/**
	 * Adds Zoom details to event single.
	 *
	 * @since 1.0.0
	 */
	public function add_event_single_zoom_details() {
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
			|| tribe( Zoom_Provider::class )->get_slug() !== $event->virtual_meeting_provider
		) {
			return;
		}

		/**
		 * Filters whether the link button should open in a new window or not.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $link_button_new_window  Boolean of if link button should open in new window.
		 */
		$link_button_new_window = apply_filters( 'tribe_events_virtual_link_button_new_window', false );

		$link_button_attrs = [];
		if ( ! empty( $link_button_new_window ) ) {
			$link_button_attrs['target'] = '_blank';
		}

		/**
		 * Filters whether the Zoom link should open in a new window or not.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $zoom_link_new_window  Boolean of if Zoom link should open in new window.
		 */
		$zoom_link_new_window = apply_filters( 'tribe_events_virtual_zoom_link_new_window', false );

		$zoom_link_attrs = [];
		if ( ! empty( $zoom_link_new_window ) ) {
			$zoom_link_attrs['target'] = '_blank';
		}

		$context = [
			'event'             => $event,
			'link_button_attrs' => $link_button_attrs,
			'zoom_link_attrs'   => $zoom_link_attrs,
		];

		$this->template->template( 'zoom/single/zoom-details', $context );
	}

	/**
	 * Adds Zoom authorize fields to events->settings->api.
	 *
	 * @since 1.0.0
	 *
	 * @param Api $api An instance of the Zoom API handler.
	 * @param Url $url The URLs handler for the integration.
	 *
	 * @return string HTML for the authorize fields.
	 */
	public function get_api_authorize_fields( Api $api, Url $url ) {
		$message = get_transient( Settings::$option_prefix . 'account_message' );
		if ( $message ) {
			delete_transient( Settings::$option_prefix . 'account_message' );
		}

		$args = [
			'api'     => $api,
			'url'     => $url,
			'message' => $message,
		];

		return $this->admin_template->template( 'zoom/api/authorize-fields', $args, false );
	}

	/**
	 * Gets Zoom connect link.
	 *
	 * @since 1.0.1
	 * @since 1.5.0 - Change to an add link for multiple account support.
	 *
	 * @param Api $api An instance of the Zoom API handler.
	 * @param Url $url The URLs handler for the integration.
	 *
	 * @return string HTML for the authorize fields.
	 */
	public function get_connect_link( Api $api, Url $url ) {
		$args = [
			'api' => $api,
			'url' => $url,
		];

		return $this->admin_template->template( 'zoom/api/authorize-fields/add-link', $args, false );
	}

	/**
	 * Get intro text for Zoom API UI
	 *
	 * @since 1.0.0
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

		return $this->admin_template->template( 'zoom/api/intro-text', $args, false );
	}

	/**
	 * The message template to display on user account changes.
	 *
	 * @since 1.5.0
	 *
	 * @param string $message The message to display.
	 * @param string $type    The type of message, either updated or error.
	 *
	 * @return string The message with html to display
	 */
	public function get_settings_message_template( $message, $type = 'updated' ) {
		return $this->admin_template->template( 'components/message', [
			'message' => $message,
			'type'    => $type,
		] );
	}

	/**
	 * Gets Zoom disabled connect button.
	 *
	 * @since 1.0.1
	 * @deprecated 1.11.0 - Replaced with Multiple Account Support, see Account_API class.
	 *
	 * @return string HTML for the authorize fields.
	 */
	public function get_disabled_button() {
		_deprecated_function( __METHOD__, '1.11.0', 'No replacement, functionality moved to whodat server.' );

		return $this->admin_template->template( 'zoom/api/authorize-fields/disabled-button', [], false );
	}

}
