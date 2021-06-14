<?php
/**
 * Handles the registration of Zoom as a meetings provider.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings
 */

namespace Tribe\Events\Virtual\Meetings;

use Tribe\Events\Virtual\Meetings\Zoom\Migration_Notice;
use Tribe\Events\Virtual\Meetings\Zoom\Settings;
use Tribe\Events\Virtual\Meetings\Zoom\Users;
use Tribe\Events\Virtual\Event_Meta;
use Tribe\Events\Virtual\Meetings\Zoom\Event_Meta as Zoom_Meta;
use Tribe\Events\Virtual\Meetings\Zoom\Api;
use Tribe\Events\Virtual\Meetings\Zoom\Meetings;
use Tribe\Events\Virtual\Meetings\Zoom\OAuth;
use Tribe\Events\Virtual\Meetings\Zoom\Password;
use Tribe\Events\Virtual\Meetings\Zoom\Template_Modifications;
use Tribe\Events\Virtual\Meetings\Zoom\Webinars;
use Tribe\Events\Virtual\Plugin;
use Tribe\Events\Virtual\Traits\With_Nonce_Routes;
use Tribe__Events__Main as Events_Plugin;

/**
 * Class Zoom_Provider
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual\Meetings
 */
class Zoom_Provider extends Meeting_Provider {
	use With_Nonce_Routes;

	/**
	 * The slug of this provider.
	 *
	 * @since 1.0.0
	 */
	const SLUG = 'zoom';

	/**
	 * {@inheritDoc}
	 */
	public function get_slug() {
		return self::SLUG;
	}

	/**
	 * Registers the bindings, actions and filters required by the Zoom API meetings provider to work.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		// Register this providers in the container to allow calls on it, e.g. to check if enabled.
		$this->container->singleton( 'events-virtual.meetings.zoom', self::class );
		$this->container->singleton( self::class, self::class );

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->add_actions();
		$this->add_filters();

		add_filter(
			'tribe_rest_event_data',
			$this->container->callback( Zoom_Meta::class, 'attach_rest_properties' ),
			10,
			2
		);

		$this->hook_templates();
		$this->enqueue_assets();
		$this->route_admin_by_nonce( $this->admin_routes(), 'manage_options' );
	}

	/**
	 * Filters the fields in the Events > Settings > APIs tab to add the ones provided by the extension.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,array> $fields The current fields.
	 *
	 * @return array<string,array> The fields, as updated by the settings.
	 */
	public function filter_addons_tab_fields( $fields ) {
		if ( ! is_array( $fields ) ) {
			return $fields;
		}

		return tribe( Zoom\Settings::class )->add_fields( $fields );
	}

	/**
	 * Renders the Zoom API link generation UI and controls, depending on the current state.
	 *
	 * @since 1.0.0
	 *
	 * @param string           $file        The path to the template file, unused.
	 * @param string           $entry_point The name of the template entry point, unused.
	 * @param \Tribe__Template $template    The current template instance.
	 */
	public function render_classic_meeting_link_ui( $file, $entry_point, \Tribe__Template $template ) {
		$this->container->make( Zoom\Classic_Editor::class )
		                ->render_initial_zoom_setup_options( $template->get( 'post' ) );
	}

	/**
	 * Renders the Zoom API controls related to the display of the Zoom Meeting link.
	 *
	 * @since 1.0.0
	 *
	 * @param string           $file        The path to the template file, unused.
	 * @param string           $entry_point The name of the template entry point, unused.
	 * @param \Tribe__Template $template    The current template instance.
	 */
	public function render_classic_display_controls( $file, $entry_point, \Tribe__Template $template ) {
		$this->container->make( Zoom\Classic_Editor::class )
						->render_classic_display_controls( $template->get( 'post' ) );
	}

	/**
	 * Filters the object returned by the `tribe_get_event` function to add to it properties related to Zoom meetings.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $event The events post object to be modified.
	 *
	 * @return \WP_Post The original event object decorated with properties related to virtual events.
	 */
	public function add_event_properties( $event ) {
		if ( ! $event instanceof \WP_Post ) {
			// We should only act on event posts, else bail.
			return $event;
		}

		return $this->container->make( Zoom_Meta::class )->add_event_properties( $event );
	}

	/**
	 * Check Zoom Meeting in the admin on every load.
	 *
	 * @since 1.0.4
	 *
	 * @param \WP_Post $event The event post object.
	 *
	 * @return bool|void Whether the update completed.
	 */
	public function check_admin_zoom_meeting( $event ) {
		if ( ! $event instanceof \WP_Post ) {
			// We should only act on event posts, else bail.
			return $event;
		}

		return $this->container->make( Password::class )->update_password_from_zoom( $event );
	}

	/**
	 * Check Zoom Meeting Account in the admin on every event for compatibility with multiple accounts.
	 *
	 * @since TBD
	 *
	 * @param \WP_Post $event The event post object.
	 *
	 * @return bool|void Whether the update completed.
	 */
	public function update_event_for_multiple_accounts_support( $event ) {
		if ( ! $event instanceof \WP_Post ) {
			// We should only act on event posts, else bail.
			return $event;
		}

		return $this->container->make( Api::class )->update_event_for_multiple_accounts_support( $event );
	}

	/**
	 * Check Zoom Meeting on Front End and Set Transient.
	 *
	 * @since 1.0.4
	 *
	 * @return bool|void Whether the update completed.
	 */
	public function check_zoom_meeting() {

		if ( ! is_singular( Events_Plugin::POSTTYPE ) ) {
			return;
		}

		global $post;

		$transient_name = $post->ID . 'zoom_pw_last_check';

		$last_check = (string) get_transient( $transient_name );
		if ( $last_check ) {
			return;
		}

		set_transient( $transient_name, true, HOUR_IN_SECONDS );

		return $this->container->make( Password::class )->update_password_from_zoom( $post );
	}

	/**
	 * Render the Migration Notice to the New Zoom App.
	 *
	 * @since 1.4.0
	 */
	public function render_migration_notice() {
		$this->container->make( Migration_Notice::class )->render();
	}

	/**
	 * Hooks the template required for the integration to work.
	 *
	 * @since 1.0.0
	 */
	protected function hook_templates() {
		// Metabox.
		add_action(
			'tribe_template_entry_point:events-virtual/admin-views/virtual-metabox/container/video-source:before_li_close',
			[ $this, 'render_classic_meeting_link_ui' ],
			10,
			3
		);

		add_action(
			'tribe_template_entry_point:events-virtual/admin-views/virtual-metabox/container/display:before_ul_close',
			[ $this, 'render_classic_display_controls' ],
			10,
			3
		);

		// Email Templates.
		add_filter(
			'tribe_events_virtual_ticket_email_template',
			[
				$this,
				'maybe_change_email_template',
			],
			10,
			2
		);

		// Event Single.
		add_action(
			'tribe_events_single_event_after_the_content',
			[ $this, 'action_add_event_single_zoom_details' ],
			15,
			0
		);

		// Event Single - Blocks.
		add_action(
			'tribe_template_after_include:events/blocks/event-datetime',
			[ $this, 'action_add_event_single_zoom_details' ],
			20,
			0
		);
	}

	/**
	 * Enqueues the assets required by the integration.
	 *
	 * @since 1.0.0
	 */
	protected function enqueue_assets() {
		tribe_asset(
			tribe( Plugin::class ),
			'tribe-events-virtual-zoom-admin-js',
			'events-virtual-zoom-admin.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'localize' => [
					'name' => 'tribe_events_virtual_placeholder_strings',
					'data' => [
						'video'         => Event_Meta::get_video_source_text(),
						'zoom'          => self::get_zoom_link_placeholder_text(),
						'removeConfirm' => self::get_zoom_confirmation_to_remove_connection_text(),
					],
				],
			]
		);

		tribe_asset(
			tribe( Plugin::class ),
			'tribe-events-virtual-zoom-admin-style',
			'events-virtual-zoom-admin.css',
			[],
			'admin_enqueue_scripts'
		);

		tribe_asset(
			tribe( Plugin::class ),
			'tribe-events-virtual-zoom-settings-js',
			'events-virtual-zoom-settings.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'localize' => [
					'name' => 'tribe_events_virtual_settings_strings',
					'data' => [
						'refreshConfirm' => self::get_zoom_confirmation_to_refresh_account(),
						'deleteConfirm'  => self::get_zoom_confirmation_to_delete_account(),
					],
				],
			]
		);
	}

	/**
	 * Handles the save operations of the Classic Editor VE Metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param int                 $post_id The post ID of the event currently being saved.
	 * @param array<string,mixed> $data    The data currently being saved.
	 */
	public function on_metabox_save( $post_id, $data ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post && is_array( $data ) ) {
			return;
		}

		$this->container->make( Zoom_Meta::class )->save_metabox_data( $post_id, $data );
	}

	/**
	 * Handles updating Zoom meetings on post save.
	 *
	 * @since 1.0.2
	 *
	 * @param int     $post_id     The post ID.
	 * @param WP_Post $unused_post The post object.
	 * @param bool    $update      Whether this is an existing post being updated or not.
	 *
	 * @return void
	 */
	public function on_post_save( $post_id, $unused_post, $update ) {
		if ( ! $update ) {
			return;
		}

		$event = tribe_get_event( $post_id );

		if ( ! $event instanceof \WP_Post || empty( $event->duration ) ) {
			// Hook for the Event meta save to try later in the save request, data might be there then.
			if ( ! doing_action( 'tribe_events_update_meta' ) ) {
				// But do no re-hook if we're acting on it.
				add_action( 'tribe_events_update_meta', [ $this, 'on_post_save' ], 100, 3 );
			}

			return;
		}

		// Handle the update with the correct handler, depending on the meeting type.
		if ( empty( $event->zoom_meeting_type ) || Webinars::$meeting_type !== $event->zoom_meeting_type ) {
			$meeting_handler = $this->container->make( Meetings::class );
		} else {
			$meeting_handler = $this->container->make( Webinars::class );
		}

		$meeting_handler->update( $event );
	}

	/**
	 * Get authorized field template.
	 *
	 * @since 1.0.0
	 *
	 * @param Api $api An instance of the Zoom API handler.
	 * @param Url $url An instance of the URL handler.
	 *
	 * @return void
	 */
	public function zoom_api_authorize_fields( $api, $url ) {
		$this->container->make( Template_Modifications::class )->add_zoom_api_authorize_fields( $api, $url );
	}

	/**
	 * Conditionally inject content into ticket email templates.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template The template path, relative to src/views.
	 * @param array  $args     The template arguments.
	 *
	 * @return string
	 */
	public function maybe_change_email_template( $template, $args ) {
		// Just in case.
		$event = tribe_get_event( $args['event'] );

		if ( empty( $event ) ) {
			return $template;
		}

		if (
			empty( $event->virtual )
			|| empty( $event->virtual_meeting )
			|| tribe( self::class )->get_slug() !== $event->virtual_meeting_provider
		) {
			return $template;
		}

		$template = 'zoom/email/ticket-email-zoom-details';

		return $template;
	}

	/**
	 * Include the zoom details for event single.
	 *
	 * @since 1.0.0
	 */
	public function action_add_event_single_zoom_details() {
		// Don't show if requires log in and user isn't logged in.
		$base_modifications = $this->container->make( 'Tribe\Events\Virtual\Template_Modifications' );
		$should_show        = $base_modifications->should_show_virtual_content( tribe_get_Event( get_the_ID() ) );

		if ( ! $should_show ) {
			return;
		}

		$template_modifications = $this->container->make( Template_Modifications::class );
		$template_modifications->add_event_single_zoom_details();
	}

	/**
	 * Filters the password for the Zoom Meeting.
	 *
	 * @since 1.0.2
	 *
	 * @param null|string|int $password     The password for the Zoom meeting.
	 * @param array           $requirements An array of password requirements from Zoom.
	 */
	public function filter_zoom_password( $password, $requirements ) {
		return $this->container->make( Password::class )->filter_zoom_password( $password, $requirements );
	}

	/**
	 * Adds placeholder text for Zoom links.
	 *
	 * @since 1.0.0
	 *
	 * @param string        $text  The placeholder text.
	 * @param \WP_Post|null $event The events post object we're editing.
	 *
	 * @return string The placeholder text.
	 */
	public static function zoom_link_placeholder_text( $text, $event ) {
		if (
			empty( $event->virtual_meeting )
			|| tribe( self::class )->get_slug() !== $event->virtual_meeting_provider
		) {
			return $text;
		}

		return self::get_zoom_link_placeholder_text();
	}

	/**
	 * Get default placeholder text and filter it.
	 *
	 * @since 1.0.0
	 *
	 * @return string The placeholder text.
	 */
	public static function get_zoom_link_placeholder_text() {
		$text = __( 'Zoom link generated', 'events-virtual' );

		/**
		 * Allows filtering of the placeholder text for when Zoom overrides the URL field.
		 *
		 * @since 1.0.0
		 *
		 * @param string $text The current placeholder text.
		 */
		return apply_filters(
			'tribe_events_virtual_zoom_link_placeholder_text',
			$text
		);
	}

	/**
	 * Get the confirmation text for removing a Zoom connection.
	 *
	 * @since TBD
	 *
	 * @return string The confirmation text.
	 */
	public static function get_zoom_confirmation_to_remove_connection_text() {
		return _x(
			'Are you sure you want to remove this Zoom meeting from this event? This operation cannot be undone.',
			'The message to display to confirm a user would like to remove the Zoom connection from an event.',
			'events-virtual'
		);
	}

	/**
	 * Get the confirmation text for refreshing a Zoom account.
	 *
	 * @since TBD
	 *
	 * @return string The confirmation text.
	 */
	public static function get_zoom_confirmation_to_refresh_account() {
		return _x(
			'Before refreshing the connection, make sure you are logged into the Zoom account in this browser.',
			'The message to display before a user attempts to refresh a Zoom account connection.',
			'events-virtual'
		);
	}

	/**
	 * Get the confirmation text for deleting a Zoom account.
	 *
	 * @since TBD
	 *
	 * @return string The confirmation text.
	 */
	public static function get_zoom_confirmation_to_delete_account() {
		return _x(
			'Are you sure you want to delete this Zoom connection? This operation cannot be undone. Existing meetings tied to this account will not be impacted.',
			'The message to display to confirm a user would like to delete a Zoom account.',
			'events-virtual'
		);
	}

	/**
	 * Filters whether embed video control is hidden.
	 *
	 * @param boolean $is_hidden Whether the embed video control is hidden.
	 * @param WP_Post $event     The event object.
	 *
	 * @return boolean Whether the embed video control is hidden.
	 */
	public function filter_display_embed_video_hidden( $is_hidden, $event ) {
		return $event->virtual_meeting && tribe( self::class )->get_slug() === $event->virtual_meeting_provider;
	}

	/**
	 * Filters the video source virtual url.
	 *
	 * @param string  $virtual_url The virtual url.
	 * @param WP_Post $event       The event object.
	 *
	 * @return string The filtered virtual url.
	 */
	public function filter_video_source_virtual_url( $virtual_url, $event ) {
		if (
			empty( $event->virtual_meeting )
			|| tribe( self::class )->get_slug() !== $event->virtual_meeting_provider
		) {
			return $virtual_url;
		}

		return '';
	}

	/**
	 * Filters whether the video source virtual url is disabled.
	 *
	 * @param boolean $is_disabled Whether the video source virtual url is disabled.
	 * @param WP_Post $event       The event object.
	 *
	 * @return boolean Whether the video source virtual url is disabled.
	 */
	public function filter_video_source_virtual_url_disabled( $is_disabled, $event ) {
		return $event->virtual_meeting && tribe( self::class )->get_slug() === $event->virtual_meeting_provider;
	}

	/**
	 * Get the list of Zoom ajax nonce actions.
	 *
	 * @since TBD
	 *
	 * @return array<string,callable> A map from the nonce actions to the corresponding handlers.
	 */
	public function filter_virtual_meetings_zoom_ajax_actions() {
		return $this->admin_routes();
	}

	/**
	 * Provides the routes that should be used to handle Zoom API requests.
	 *
	 * The map returned by this method will be used by the `Tribe\Events\Virtual\Traits\With_Nonce_Routes` trait.
	 *
	 * @since 1.0.2.1 Added the method
	 * @since 1.0.4 Renamed the method to `admin_routes`.
	 *
	 * @return array<string,callable> A map from the nonce actions to the corresponding handlers.
	 */
	public function admin_routes() {
		return [
			Oauth::$authorize_nonce_action   => $this->container->callback( OAuth::class, 'handle_auth_request' ),
			OAuth::$deauthorize_nonce_action => $this->container->callback( OAuth::class, 'handle_deauth_request' ),
			Meetings::$create_action         => $this->container->callback( Meetings::class, 'ajax_create' ),
			Meetings::$update_action         => $this->container->callback( Meetings::class, 'ajax_update' ),
			Meetings::$remove_action         => $this->container->callback( Meetings::class, 'ajax_remove' ),
			Webinars::$create_action         => $this->container->callback( Webinars::class, 'ajax_create' ),
			Webinars::$update_action         => $this->container->callback( Webinars::class, 'ajax_update' ),
			Webinars::$remove_action         => $this->container->callback( Webinars::class, 'ajax_remove' ),
			API::$select_action              => $this->container->callback( API::class, 'ajax_selection' ),
			Settings::$status_action         => $this->container->callback( Settings::class, 'ajax_status' ),
			Settings::$delete_action         => $this->container->callback( Settings::class, 'ajax_delete' ),
		];
	}

	/**
	 * Hooks the filters required for the Zoom API integration to work correctly.
	 *
	 * @since 1.1.1
	 */
	protected function add_filters() {
		add_filter( 'tribe_addons_tab_fields', [ $this, 'filter_addons_tab_fields' ] );

		foreach ( [ Meetings::$meeting_type, Webinars::$meeting_type ] as $meeting_type ) {
			add_filter(
				"tribe_events_virtual_meetings_zoom_{$meeting_type}_password",
				[ $this, 'filter_zoom_password' ],
				10,
				2
			);
		}

		add_filter(
			'tribe_events_virtual_video_source_placeholder_text',
			[ $this, 'zoom_link_placeholder_text' ],
			10,
			2
		);
		add_filter(
			'tribe_events_virtual_display_embed_video_hidden',
			[ $this, 'filter_display_embed_video_hidden' ],
			10,
			2
		);
		add_filter(
			'tribe_events_virtual_video_source_virtual_url',
			[ $this, 'filter_video_source_virtual_url' ],
			10,
			2
		);
		add_filter(
			'tribe_events_virtual_video_source_virtual_url_disabled',
			[ $this, 'filter_video_source_virtual_url_disabled' ],
			10,
			2
		);
		add_filter(
			'tribe_events_virtual_meetings_zoom_ajax_actions',
			[ $this, 'filter_virtual_meetings_zoom_ajax_actions' ]
		);
	}

	/**
	 * Hooks the actions required for the Zoom API integration to work correctly.
	 *
	 * @since 1.1.1
	 */
	protected function add_actions() {
		// Filter event object properties to add the ones related to Zoom meetings for virtual events.
		add_action( 'tribe_events_virtual_add_event_properties', [ $this, 'add_event_properties' ] );
		add_action( 'add_meta_boxes_' . Events_Plugin::POSTTYPE, [ $this, 'check_admin_zoom_meeting' ] );
		add_action( 'add_meta_boxes_' . Events_Plugin::POSTTYPE, [ $this, 'update_event_for_multiple_accounts_support' ], 0 );
		add_action( 'wp', [ $this, 'check_zoom_meeting' ], 50 );
		add_action( 'tribe_events_virtual_metabox_save', [ $this, 'on_metabox_save' ], 10, 2 );
		add_action( 'save_post_tribe_events', [ $this, 'on_post_save' ], 100, 3 );
		add_action(
			'wp_ajax_events_virtual_meetings_zoom_autosave_client_keys',
			[ Zoom\OAuth::class, 'ajax_credentials_save' ]
		);
		add_action( 'admin_init', [ $this, 'render_migration_notice' ] );
	}
}
