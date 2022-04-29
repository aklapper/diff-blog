<?php
/**
 * View: Virtual Events Metabox Zoom API Account Disabled.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/account-disabled-details.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.5.0
 * @deprecated 1.9.0 - Use src/admin-views/virtual-metabox/api/account-disabled-details.php
 *
 * @version 1.5.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var string $disabled_title The disabled title.
 * @var string $disabled_body  The disabled message.
 * @var string $link_url       The URL to generate a Zoom Meeting link.
 * @var string $link_label     The label of the button to generate a Zoom Meeting link.
 */
?>
<div class="tribe-events-virtual-meetings-zoom-message">
	<div class="tribe-events-virtual-meetings-zoom-error__details-header">
		<?php echo esc_html( $disabled_title ); ?>
	</div>

	<div class="tec-events-virtual-meetings-api-standard-details__wrapper tribe-events-virtual-meetings-zoom-error__details-wrapper">
		<p class="tribe-events-virtual-meetings-zoom-error__details-body">
			<?php echo wp_kses_post( $disabled_body ); ?>
		</p>
	</div>

	<div class="tribe-events-virtual-meetings-zoom-error__link-wrapper">
		<a
			class="tribe-events-virtual-meetings-zoom-error__link-connect"
			href="<?php echo esc_url( $link_url ); ?>"
		>
			<?php echo esc_html( $link_label ); ?>
		</a>
	</div>
</div>
