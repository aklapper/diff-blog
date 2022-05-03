<?php
/**
 * View: Virtual Events Metabox Zoom API connect link.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/zoom/api/authorize-fields/connect-link
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
 * @var Api $api An instance of the Zoom API handler.
 * @var Url $url An instance of the URL handler.
 */

$authorize_link      = $url->to_authorize();
$missing_credentials = ! $api->is_authorized();
$connect_label       = $missing_credentials
	? _x( 'Connect to Zoom', 'Label to connect to Zoom API.', 'events-virtual' )
	: _x( 'Refresh Zoom Connection', 'Label to refresh connection to Zoom API.', 'events-virtual' );

$classes = [
	'tribe-button'                                      => true,
	'tribe-settings-zoom-application__authorize-button' => true,
];
?>
<a
	href="<?php echo esc_url( $authorize_link ); ?>"
	<?php tribe_classes( $classes ); ?>
>
	<?php echo esc_html( $connect_label ); ?>
</a>
<?php if ( ! $missing_credentials ) : ?>
	<?php $this->template( 'zoom/api/authorize-fields/disconnect-link', [ 'url' => $url ] ); ?>
<?php endif; ?>
