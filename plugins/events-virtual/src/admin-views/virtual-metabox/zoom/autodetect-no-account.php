<?php
/**
 * View: Virtual Events Metabox Zoom API Autodetect no account message.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/autodetect-no-account.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.8.0
 * @deprecated 1.9.0 - Use src/admin-views/virtual-metabox/api/autodetect-no-account.php
 *
 * @version 1.8.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var array<string,string> $classes_wrap     An array of classes for the toggle wrap.
 * @var string               $message          The message to display.
 * @var string               $setup_link_url   The URL to setup Zoom accounts.
 * @var string               $setup_link_label The label of the button to setup Zoom accounts.
 * @var array<string,string> $wrap_attrs       Associative array of attributes of the dropdown wrap.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

$wrap_classes = [ 'tec-events-virtual-meetings-control', 'tec-events-virtual-meetings-control--message' ];
if ( ! empty( $classes_wrap ) ) {
	$wrap_classes = array_merge( $wrap_classes, $classes_wrap );
}

if ( empty( $wrap_attrs ) ) {
	$wrap_attrs = [];
}
?>

<div
	<?php tribe_classes( $wrap_classes ); ?>
	<?php tribe_attributes( $wrap_attrs ) ?>
>
	<div class="tribe-events-virtual-meetings-autodetect-zoom__message-inner">
		<?php echo $message; ?>
		<a
			class="tribe-events-virtual-meetings-zoom__setup-link"
			href="<?php echo esc_url( $setup_link_url ); ?>"
		>
			<?php echo esc_html( $setup_link_label ); ?>
		</a>

	</div>
</div>
