<?php
/**
 * Handles the creation, deletion and updates of post to post relationships.
 *
 * @since   6.0.0
 *
 * @pacakge TEC\Events_Pro\Custom_Tables\V1\Updates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series as Series_Model;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_REST_Request as Request;
use WP_REST_Server as REST_Server;
use WP_REST_Request;
/**
 * Class Relationships
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */
class Relationships {
	const RELATIONSHIP_REMOVE = 'RELATIONSHIP_REMOVE';
	const RELATIONSHIP_KEEP = 'RELATIONSHIP_KEEP';

	/**
	 * Saves the Event post relationships specified, if any, in the Request.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $post    A reference to the Post object.
	 * @param Request $request A reference to the object modeling the current request.
	 *
	 * @return bool Whether the Event post relationships were correctly saved or not.
	 */
	public function update( WP_Post $post, Request $request ) {
		if ( ! ( $event = $this->check_post( $post ) ) instanceof Event ) {
			return false;
		}

		if ( false === ( $series = $this->check_request( $request, $post, $event ) ) ) {
			return false;
		}

		if ( $series === self::RELATIONSHIP_KEEP ) {
			// We're in the correct state already.
			return true;
		}

		if ( self::RELATIONSHIP_REMOVE == $series ) {
			if ( tribe_is_recurring_event( $post->ID ) ) {
				// Cannot remove the only Series related to a Recurring event.
				return false;
			}

			tribe( Relationship::class )->detach_event( $event );
		} else {
			$series_post_ids = (array) Series_Model::vinsert( $series, [ 'post_status' => $post->post_status ] );

			tribe( Relationship::class )->with_event( $event, $series_post_ids );
		}

		return true;
	}

	/**
	 * Checks if the candidate post satisfies all the criteria required for the
	 * save or not.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $post A reference to the candidate post.
	 *
	 * @return Event|false Either a reference to an Event instance built for the
	 *                     candidate post, or `false` if the post does not satisfy the
	 *                     criteria.
	 */
	private function check_post( WP_Post $post ) {
		if ( TEC::POSTTYPE !== $post->post_type ) {
			return false;
		}

		$event = Event::find( $post->ID, 'post_id' );

		if ( ! $event instanceof Event ) {
			return false;
		}

		return $event;
	}

	/**
	 * Checks the current request parameters to dictate the next action to perform.
	 *
	 * @since 6.0.0
	 *
	 * @param Request $request A reference to the request object to check.
	 * @param WP_Post $post A reference to the Event post object.
	 * @param Event   $event A reference to the Event Model instance to check the request for.
	 *
	 * @return array<int>|false|string An array of Series post IDs, the value of a constant representin the
	 *                                 Action to perform, the title of a Series to insert, or `false` on failure.
	 */
	private function check_request( Request $request, WP_Post $post, Event $event ) {
		if ( $request->get_method() !== REST_Server::CREATABLE ) {
			// Only process on POST requests as that is when the operations are taking place.
			return false;
		}

		if ( (int) $request->get_param( 'id' ) !== $post->ID ) {
			// The request is not for this Event post, bail.
			return false;
		}

		if ( ! $request->has_param( Relationship::EVENTS_TO_SERIES_REQUEST_KEY ) ) {
			return false;
		}

		$series = (array) $request->get_param( Relationship::EVENTS_TO_SERIES_REQUEST_KEY );
		$series = $series === [ '-1' ] ? self::RELATIONSHIP_REMOVE : $series;

		// We send series data as a json string, or when creating a new series the series title.
		if ( is_array( $series ) ) {
			foreach ( $series as $i => $series_data ) {
				$json         = is_string( $series_data ) ? json_decode( $series_data, true ) : $series_data;
				$series[ $i ] = is_array( $json ) ? $json : [ 'id' => null, 'title' => $series_data ];
			}
		}

		// In case we have flagged as a removal, we must ensure a series is associated.
		if ( $series === self::RELATIONSHIP_REMOVE && '' !== (string) $event->rset ) {
			$current_relationship = Series_Relationship::find( $post->ID, 'event_post_id' );

			if ( $current_relationship instanceof Series_Relationship ) {
				$series = self::RELATIONSHIP_KEEP;
			} else {
				$series = [[ 'id' => null, 'title' => $post->post_title ]];
			}
		}

		return $series;
	}

	/**
	 * Looks for the request var from the series page to add
	 * a list of events to a series.
	 *
	 * @param WP_REST_Request $request
	 * @return bool True if an update was made, false if no update made.
	 */
	public function save_relationships_from_series( WP_REST_Request $request) {
		$post_id = $request->get_param( 'id' );

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {

			return false;
		}

		if ( $request->has_param( Relationship::SERIES_TO_EVENTS_REQUEST_KEY ) ) {
			$events       = $request->get_param( Relationship::SERIES_TO_EVENTS_REQUEST_KEY );

			// An empty array is a valid value!
			tribe( Relationship::class )->with_series( $post, $events, false );

			return true;
		}

		return false;
	}
}
