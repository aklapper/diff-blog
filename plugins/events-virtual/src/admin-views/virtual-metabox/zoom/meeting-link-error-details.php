<?php
/**
 * View: Virtual Events Metabox Zoom API failed request details and controls.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/meeting-link-error-details.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.0.0
 * @deprecated 1.9.0 - Use src/admin-views/virtual-metabox/api/meeting-link-error-details.php.
 *
 * @version 1.0.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var string               $remove_link_url   The URL to remove the event Zoom Meeting.
 * @var string               $remove_link_label The label of the button to remove the event Zoom Meeting link.
 * @var array<string,string> $remove_attrs      Associative array of attributes of the remove link.
 * @var bool                 $is_authorized     Whether the user authorized the Zoom integration to create meeting links or not.
 * @var string               $error_body        The localized "or" string.
 * @var string               $link_url          The URL to generate a Zoom Meeting link.
 * @var string               $link_label        The label of the button to generate a Zoom Meeting link.
 */

$remove_link_label = _x(
	'Dismiss',
	'Accessible label of the control to dismiss a failure to generate a Zoom Meeting link.',
	'events-virtual'
);

?>

<div
	id="tribe-events-virtual-meetings-zoom"
	class="tec-events-virtual-meetings-video-source__inner tribe-events-virtual-meetings-zoom-error"
>

	<a
		class="tec-events-virtual-meetings-api-details__remove-link"
		href="<?php echo esc_url( $remove_link_url ); ?>"
		aria-label="<?php echo esc_attr( $remove_link_label ); ?>"
		title="<?php echo esc_attr( $remove_link_label ); ?>"
		<?php tribe_attributes( $remove_attrs ) ?>
	>
		×
	</a>

	<div class="tribe-events-virtual-meetings-zoom-error__title">
		<?php
		echo esc_html_x(
			'Zoom Link',
			'Header of the details shown when an attempt to generate a Zoom Meeting link fails.',
			'events-virtual'
		);
		?>
	</div>

	<div class="tec-events-virtual-meetings-api-standard-details__wrapper tribe-events-virtual-meetings-zoom-error__message-wrapper">
		<p class="tribe-events-virtual-meetings-zoom-error__message">
			<?php
			echo esc_html_x(
				'We were not able to generate a Zoom link.',
				'Message shown when an attempt to generate a Zoom Meeting link fails.',
				'events-virtual'
			);
			?>
		</p>
	</div>

	<div class="tribe-events-virtual-meetings-zoom-error__details-header">
		<?php
		echo esc_html_x(
			'Zoom error:',
			'Header of the error details section shown when an attempt to generate a Zoom Meeting link fails.',
			'events-virtual'
		);
		?>
	</div>

	<div class="tec-events-virtual-meetings-api-standard-details__wrapper tribe-events-virtual-meetings-zoom-error__details-wrapper">
		<p class="tribe-events-virtual-meetings-zoom-error__details-body">
			<?php echo wp_kses_post( $error_body ); ?>
		</p>
	</div>

	<div class="tribe-events-virtual-meetings-zoom-error__link-wrapper">
		<?php if ( $is_authorized ) : ?>

			<a
				class="button button-secondary tribe-events-virtual-meetings-zoom__create-link"
				href="<?php echo esc_url( $link_url ); ?>"
			>
				<?php echo esc_html( $link_label ); ?>
			</a>

		<?php else : ?>

			<a
				class="tribe-events-virtual-meetings-zoom-error__link-connect"
				href="<?php echo esc_url( $link_url ); ?>"
			>
				<?php echo esc_html( $link_label ); ?>
			</a>

		<?php endif; ?>
	</div>

</div>
