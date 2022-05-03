<?php
/**
 * View: Virtual Events Metabox Zoom API disabled connect button.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/zoom/api/authorize-fields/disabled-button
 *
 * See more documentation about our views templating system.
 *
 * @since   1.0.0
 * @deprecated 1.9.0 - Replaced with Multiple Account Support.
 *
 * @version 1.0.0
 *
 * @link    http://evnt.is/1aiy
 */

$connect_label   = _x( 'Connect to Zoom', 'Label to connect to Zoom API (disabled button).', 'events-virtual' );
?>
<button
	class="tribe-settings-zoom-application__authorize-button tribe-settings-zoom-application__authorize-button--disabled"
	title="<?php esc_attr_e( 'Enter client app information to connect to Zoom', 'events-virtual' ); ?>"
	disabled
>
	<?php echo esc_html( $connect_label ); ?>
</button>
