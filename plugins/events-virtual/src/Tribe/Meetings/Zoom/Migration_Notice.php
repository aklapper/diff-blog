<?php
/**
 * Handles displaying the Migration Notice for the Zoom App Authorization.
 *
 * @since   1.4.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */

namespace Tribe\Events\Virtual\Meetings\Zoom;
/**
 * Class Migration_Notice
 *
 * @since   1.4.0
 *
 * @package Tribe\Events\Virtual\Meetings\Zoom
 */
class Migration_Notice {

	/**
	 * Renders the Notice to Authorize the new Zoom App.
	 *
	 * @since 1.4.0
	 */
	public function render() {

		tribe_notice(
			'zoom-app-migration',
			[ $this, 'display_notice' ],
			[
				'type'    => 'warning',
				'dismiss' => 1,
				'wrap'    => 'p',
			],
			[ $this, 'should_display' ]
		);

	}

	/**
	 * This function determines if the user can do something about the Zoom authorization, since we only want to display notices for users who can.
	 *
	 * @since  1.4.0
	 *
	 * @return boolean Whether the notice should display.
	 */
	public function should_display() {
		// Bail if the user is not admin or cannot manage plugins
		return current_user_can( 'activate_plugins' );
	}

	/**
	 * HTML for the Zoom App notice.
	 *
	 * @since  1.4.0
	 *
	 * @return string The notice text and link for the Zoom App.
	 */
	public function display_notice() {

		$text = _x( 'Thank you for updating to the latest version of Virtual Events. You will need to <a href="%1$s">reconnect your Zoom account</a> for the plugin to work as intended.', 'The migration notice to authorize the new Zoom App.', 'events-virtual' );

		return sprintf( $text, Settings::admin_url() );
	}
}
