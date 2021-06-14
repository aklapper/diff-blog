<?php
/**
 * View: Virtual Events Zoom Settings Message
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/zoom/api/message.php
 *
 * See more documentation about our views templating system.
 *
 * @since   TBD
 *
 * @version TBD
 *
 * @link    http://evnt.is/1aiy
 *
 * @var string $message The message to display.
 * @var string $type    The type of message, either standard or error.
 */
// If not message, do not display.
if ( empty( $message ) ) {
	return;
}
$message_classes = [ 'tribe-events-virtual-meetings-zoom-settings-message__wrap' ];

if ( ! empty( $message_classes ) ) {
	array_push( $message_classes, $type );
}
?>

<div
	id="tribe-events-virtual-meetings-zoom-settings-message"
	<?php tribe_classes( $message_classes ); ?>
>
	<?php esc_html_e( $message ); ?>
</div>
