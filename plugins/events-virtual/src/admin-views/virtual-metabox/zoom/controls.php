<?php
/**
 * View: Virtual Events Metabox Zoom API link controls for 2+ meeting types.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/multiple-controls.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.1.1
 * @deprecated 1.4.0 Use setup.php
 *
 * @version 1.4.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \WP_Post $event               The event post object, as decorated by the `tribe_get_event` function.
 * @var bool     $is_authorized       Whether the user authorized the Zoom integration to create meeting links or not.
 * @var string   $offer_or_label      The localized "or" string.
 * @var string   $generate_link_url   The URL to generate a Zoom Meeting link.
 * @var string   $generate_link_label The label of the button to generate a Zoom Meeting link.
 *
 * @see     tribe_get_event() For the format of the event object.
 */
?>

<span
	id="tribe-events-virtual-meetings-zoom"
	class="tribe-events-virtual-meetings-zoom tribe-events-virtual-meetings-zoom-controls"
>

	<span class="tribe-events-virtual-meetings-zoom__or-label">
		<?php echo esc_html( $offer_or_label ); ?>
	</span>

	<?php if ( $is_authorized ) : ?>

		<a
			class="button tribe-events-virtual-meetings-zoom__create-link"
			href="<?php echo esc_url( $generate_link_url ); ?>"
		>
			<?php echo esc_html( $generate_link_label ); ?>
		</a>

	<?php else : ?>

		<a
			class="tribe-events-virtual-meetings-zoom__connect-link"
			href="<?php echo esc_url( $generate_link_url ); ?>"
		>
			<?php echo esc_html( $generate_link_label ); ?>
		</a>

	<?php endif; ?>

</span>
