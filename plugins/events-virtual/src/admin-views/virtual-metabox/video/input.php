<?php
/**
 * View: Virtual Events Metabox Video Input URL
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/video/input.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.6.0
 * @deprecated 1.8.0 - Replaced with Autodetect, src/admin-views/virtual-metabox/autodetect/controls.php
 *
 * @version 1.6.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var string   $metabox_id The current metabox id.
 * @var \WP_Post $event       The current event post object, as decorated by the `tribe_get_event` function.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

use Tribe\Events\Virtual\OEmbed;
use Tribe\Events\Virtual\Event_Meta;

$oembed           = new OEmbed();
$placeholder_text = Event_Meta::get_video_source_text( $event );

/**
 * Allow filtering of the virtual event video url.
 *
 * @since 1.6.0
 *
 * @param string The virtual url string.
 * @param \WP_Post $event The current event post object, as decorated by the `tribe_get_event` function.
 */
$virtual_url          = apply_filters( 'tribe_events_virtual_video_source_virtual_url', $event->virtual_url, $event );

/**
 * Allow filtering to disable the video url field.
 *
 * @since 1.6.0
 *
 * @param bool Whether to disable the video url field or not.
 * @param \WP_Post $event The current event post object, as decorated by the `tribe_get_event` function.
 */
$virtual_url_disabled = apply_filters( 'tribe_events_virtual_video_source_virtual_url_disabled', false, $event );

$embed_notice_classes = [
	'tribe-events-virtual-video-source__not-embeddable-notice',
	'tribe-events-virtual-video-source__not-embeddable-notice--show' => ! empty( $virtual_url ) && ! $oembed->is_embeddable( $virtual_url ),
];
?>

<div
	id="tribe-events-virtual-meetings-video"
	class="tribe-dependent tribe-events-virtual-meetings-source-video"
	data-depends="#tribe-events-virtual-video-source"
	data-condition="video"
>
	<div
		class="tec-events-virtual-meetings-video-source__inner tribe-events-virtual-meetings-source-video__inner"
	>
		<div class="tribe-events-virtual-meetings-video-source__title">
			<?php echo esc_html( _x( 'Video', 'Title for video source.', 'events-virtual' ) ); ?>
		</div>

		<ul>
			<li class="tribe-events-virtual-video-source__virtual-url">
				<label
					for="<?php echo esc_attr( "{$metabox_id}-virtual-url" ); ?>"
					class="screen-reader-text tribe-events-virtual-video-source__virtual-url-input-label"
				>
					<?php echo esc_html_x( 'Live Stream URL', 'Label for live stream URL field', 'events-virtual' ); ?>
				</label>
				<input
					id="<?php echo esc_attr( "{$metabox_id}-virtual-url" ); ?>"
					name="<?php echo esc_attr( "{$metabox_id}[virtual-url]" ); ?>"
					value="<?php echo esc_url( $virtual_url ); ?>"
					type="url"
					class="components-text-control__input tribe-events-virtual-video-source__virtual-url-input"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'tribe-check-embed' ) ); ?>"
					data-oembed-test="true"
					placeholder="<?php echo esc_attr( $placeholder_text ); ?>"
					data-dependency-manual-control
					<?php disabled( $virtual_url_disabled ); ?>
				/>

			</li>
			<li
				<?php tribe_classes( $embed_notice_classes ); ?>
				role="alert"
			>
				<p class="tribe-events-virtual-video-source__not-embeddable-text event-helper-text">
					<?php echo esc_html( $oembed->get_unembeddable_message( $virtual_url ) ); ?>
				</p>
			</li>
		</ul>
	</div>
</div>
