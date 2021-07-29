<?php
/**
 * Handles the filtering of the Context to add Virtual specific locations.
 *
 * @since   1.0.1
 * @package Tribe\Events\Virtual\Service_Providers;
 */

namespace Tribe\Events\Virtual\Context;

use Tribe__Context;


class Context_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.1
	 */
	public function register() {
		$this->container->singleton( 'events-virtual.context', $this );

		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );
	}

	/**
	 * Filters the context locations to add the ones used by The Events Calendar PRO.
	 *
	 * @since 1.0.1
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public function filter_context_locations( array $locations = [] ) {
		$locations = array_merge( $locations, [
			'virtual' => [
				'read' => [
					Tribe__Context::REQUEST_VAR => [ 'virtual' ],
					Tribe__Context::QUERY_VAR   => [ 'virtual' ],
				],
			],
		] );

		return $locations;
	}
}
