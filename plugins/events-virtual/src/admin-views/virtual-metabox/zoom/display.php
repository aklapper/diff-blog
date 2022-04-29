<?php
/**
 * View: Virtual Events Metabox Zoom API display controls.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/display.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.0.0
 * @deprecated 1.9.0 - Use src/admin-views/virtual-metabox/api/display.php.
 *
 * @version 1.0.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \WP_Post $event      The event post object, as decorated by the `tribe_get_event` function.
 * @var string   $metabox_id The metabox current ID.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

use Tribe\Events\Virtual\Meetings\Zoom_Provider;

$is_zoom = $event->virtual_meeting && tribe( Zoom_Provider::class )->get_slug() === $event->virtual_meeting_provider;

$classes = [
	'tec-events-virtual-display__list-item',
	'tribe-events-virtual-hidden' => ! $is_zoom,
];

?>
<li <?php tribe_classes( $classes ); ?>>
	<label for="<?php echo esc_attr( "{$metabox_id}-meetings-zoom-display-details" ); ?>">
		<input
			id="<?php echo esc_attr( "{$metabox_id}-meetings-zoom-display-details" ); ?>"
			name="<?php echo esc_attr( "{$metabox_id}[meetings-zoom-display-details]" ); ?>"
			type="checkbox"
			value="yes"
			<?php checked( tribe_is_truthy( $event->zoom_display_details ) ); ?>
		/>
		<?php
		echo esc_html_x(
			'Zoom link with details',
			'Option to display Zoom Meeting link in event.',
			'events-virtual'
		);
		?>
	</label>
</li>
