<?php
/**
 * View: Virtual Events Metabox Video Source section.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/container/video-source.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.0.0
 *
 * @version 1.0.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var string   $metabox_id The current metabox id.
 * @var \WP_Post $post       The current event post object, as decorated by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

use Tribe\Events\Virtual\OEmbed;
use Tribe\Events\Virtual\Event_Meta;

$oembed           = new OEmbed();
$placeholder_text = Event_Meta::get_video_source_text( $post );

$virtual_url          = apply_filters( 'tribe_events_virtual_video_source_virtual_url', $post->virtual_url, $post );
$virtual_url_disabled = apply_filters( 'tribe_events_virtual_video_source_virtual_url_disabled', false, $post );

$embed_notice_classes = [
	'tribe-events-virtual-video-source__not-embeddable-notice',
	'tribe-events-virtual-video-source__not-embeddable-notice--show' => ! empty( $virtual_url ) && ! $oembed->is_embeddable( $virtual_url ),
];
?>
<tr class="tribe-events-virtual-video-source">
	<td class="tribe-table-field-label tribe-events-virtual-video-source__label">
		<?php esc_html_e( 'Add Video Source:', 'events-virtual' ); ?>
	</td>
	<td class="tribe-events-virtual-video-source__content">
		<button
			class="dashicons dashicons-trash tribe-remove-virtual-event tribe-dependent"
			type="button"
			data-depends="#<?php echo esc_attr( "{$metabox_id}-setup" ); ?>"
			data-condition-checked
		>
			<span class="screen-reader-text">
				<?php echo esc_html_x( 'Remove Virtual Settings', 'Resets the virtual settings', 'events-virtual' ); ?>
			</span>
		</button>
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
				<?php $this->do_entry_point( 'before_li_close' ); ?>
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
	</td>
</tr>
