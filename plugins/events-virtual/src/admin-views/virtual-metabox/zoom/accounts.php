<?php
/**
 * View: Virtual Events Metabox Zoom API account selection.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/accouts.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.5.0
 * @since   1.6.0 - remove $offer_or_label.
 *
 * @version 1.5.0
 *
 * @link    http://m.tri.be/1aiy
 *
 * @var \WP_Post             $event             The event post object, as decorated by the `tribe_get_event` function.
 * @var string               $select_url        The URL to select the Zoom account.
 * @var string               $select_label      The label used to designate the next step after selecting a Zoom Account.
 * @var array<string,string> $accounts          An array of users to be able to select as a host, that are formatted to use as options.
 * @var string               $remove_link_url   The URL to remove the event Zoom Meeting.
 * @var string               $remove_link_label The label of the button to remove the event Zoom Meeting link.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

$metabox_id = 'tribe-events-virtual';
?>

<div
	id="tribe-events-virtual-meetings-zoom"
	class="tribe-dependent tribe-events-virtual-meetings-zoom-details"
	data-depends="#tribe-events-virtual-video-source"
	data-condition="zoom"
>

	<div
		class="tribe-events-virtual-meetings-video-source__inner tribe-events-virtual-meetings-zoom-details__inner tribe-events-virtual-meetings-zoom-details__inner-accounts"
	>
		<a
			class="tribe-events-virtual-meetings-zoom-details__remove-link"
			href="<?php echo esc_url( $remove_link_url ); ?>"
			aria-label="<?php echo esc_attr( $remove_link_label ); ?>"
			title="<?php echo esc_attr( $remove_link_label ); ?>"
		>
			×
		</a>

		<div class="tribe-events-virtual-meetings-zoom-details__title">
			<?php echo esc_html( _x( 'Zoom Meeting', 'Title for Zoom Meeting or Webinar creation.', 'events-virtual' ) ); ?>
		</div>

		<?php $this->template( 'virtual-metabox/zoom/components/dropdown', $accounts ); ?>

		<span class="tribe-events-virtual-meetings-zoom-details__create-link-wrapper">
			<a
				class="button tribe-events-virtual-meetings-zoom-details__account-select-link"
				href="<?php echo esc_url( $select_url ); ?>"
			>
				<?php echo esc_html( $select_label ); ?>
			</a>
		</span>

	</div>
</div>
