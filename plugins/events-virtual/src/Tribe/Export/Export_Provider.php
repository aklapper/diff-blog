<?php
/**
 * Handles the export-related functions of the plugin.
 *
 * @since   1.0.4
 * @package Tribe\Events\Virtual\Service_Providers;
 */

namespace Tribe\Events\Virtual\Export;

/**
 * Class Export_Provider
 *
 * @since 1.0.4
 * @package Tribe\Events\Virtual\Export;
 */
class Export_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations and registers the required filters.
	 *
	 * @since 1.0.4
	 */
	public function register() {
		$this->container->singleton( 'events-virtual.export', $this );
		$this->container->singleton( static::class, $this );

		add_filter( 'tribe_google_calendar_parameters', [ $this, 'filter_google_calendar_parameters' ], 10, 2 );
		add_filter( 'tribe_ical_feed_item', [ $this, 'filter_ical_feed_items' ], 10, 2 );
	}

	/**
	 * Filter the Google Calendar export parameters for an exporting event.
	 *
	 * @since 1.0.4
	 *
	 * @param array $output   Google Calendar Link params.
	 * @param int   $event_id The event id.
	 *
	 * @return  array Google Calendar Link params.
	 */
	public function filter_google_calendar_parameters( $output, $event_id ) {

		return $this->container->make( Event_Export::class )->modify_export_output( $output, $event_id, 'location', 'gcal' );
	}

	/**
	 * Filter the iCal export parameters for an exporting event.
	 *
	 * @since 1.0.4
	 *
	 * @param array    $item       The various iCal file format components of this specific event item.
	 * @param \WP_Post $event_post The WP_Post of this event.
	 *
	 * @return array  The various iCal file format components of this specific event item.
	 */
	public function filter_ical_feed_items( $item, $event_post ) {
		return $this->container->make( Event_Export::class )->modify_export_output( $item, $event_post->ID, 'LOCATION', 'ical' );
	}
}
