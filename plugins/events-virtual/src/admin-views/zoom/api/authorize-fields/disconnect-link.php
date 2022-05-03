<?php
/**
 * View: Virtual Events Metabox Zoom API disconnect link.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/zoom/api/authorize-fields/disconnect-link
 *
 * See more documentation about our views templating system.
 *
 * @since   1.0.0
 * @deprecated 1.9.0 - Replaced with Multiple Account Support.
 *
 * @version 1.0.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var Url $url An instance of the URL handler.
 */

$disconnect_label = __( 'Disconnect', 'events-virtual' );
$current_url      = \Tribe__Settings::instance()->get_url( [ 'tab' => 'addons' ] );
$disconnect_url   = $url->to_disconnect( $current_url );
?>
<a
	href="<?php echo esc_url( $disconnect_url ); ?>"
	class="tribe-zoom-disconnect"
>
	<?php echo esc_html( $disconnect_label ); ?>
</a>
