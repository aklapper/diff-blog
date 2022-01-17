<?php
/**
 * View: Virtual Events Metabox Zoom API account list.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/zoom/api/authorize-fields/add-link
 *
 * See more documentation about our views templating system.
 *
 * @since   1.5.0
 *
 * @version 1.5.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var Api                 $api  An instance of the Zoom API handler.
 * @var Url                 $url  An instance of the URL handler.
 * @var array<string|mixed> $list An array of the Zoom accounts authorized for the site.
 */

if ( empty( $accounts ) ) {
	return;
}
?>
<ul>
	<?php foreach ( $accounts as $account_id => $account ) : ?>
		<li class="tribe-settings-zoom-account-details tribe-common"
			data-account-id="<?php echo esc_attr( $account_id ); ?>"
		>
			<div class="tribe-settings-zoom-account-details__account-name">
				<?php echo esc_html( $account['name'] ); ?>
			</div>
			<div class="tribe-settings-zoom-account-details__refresh-account">
				<button
					class="tribe-settings-zoom-account-details__account-refresh"
					type="button"
					data-zoom-refresh="<?php echo $url->to_authorize(); ?>"
					<?php echo tribe_is_truthy( $account['status'] ) ? '' : 'disabled'; ?>
				>
					<?php $this->template( 'zoom/api/components/icons/refresh', [ 'classes' => [ 'tribe-events-virtual-virtual-event__icon-svg' ] ] ); ?>
					<span class="screen-reader-text">
						<?php echo esc_html_x( 'Refresh Zoom Account', 'Refreshes a Zoom account from the website.', 'events-virtual' ); ?>
					</span>
				</button>
			</div>
			<div class="tribe-settings-zoom-account-details__account-status">
				<?php
				$this->template( 'components/switch', [
					'id'            => 'account-status-' . $account_id,
					'label'         => _x( 'Toggle to Change Account Status', 'Disables the Zoom Account for the Website.', 'events-virtual' ),
					'classes_wrap'  => [ 'tribe-events-virtual-meetings-zoom-control', 'tribe-events-virtual-meetings-zoom-control--switch' ],
					'classes_input' => [ 'account-status', 'tribe-events-virtual-meetings-zoom-settings-switch__input' ],
					'classes_label' => [ 'tribe-events-virtual-meetings-zoom-settings-switch__label' ],
					'name'          => 'account-status',
					'value'         => 1,
					'checked'       => $account['status'],
					'attrs'         => [
						'data-ajax-status-url' => $url->to_change_account_status_link( $account_id ),
					],
				] );
				?>
			</div>
			<div class="tribe-settings-zoom-account-details__account-delete">
				<button
					class="dashicons dashicons-trash tribe-settings-zoom-account-details__delete-account"
					type="button"
					data-ajax-delete-url="<?php echo $url->to_delete_account_link( $account_id ); ?>"
					<?php echo tribe_is_truthy( $account['status'] ) ? '' : 'disabled'; ?>
				>
					<span class="screen-reader-text">
						<?php echo esc_html_x( 'Remove Zoom Account', 'Removes a Zoom account from the website.', 'events-virtual' ); ?>
					</span>
				</button>
			</div>
		</li>
	<?php endforeach; ?>
</ul>