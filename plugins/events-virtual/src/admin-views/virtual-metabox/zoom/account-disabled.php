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
 * @deprecated 1.9.0 - Use src/admin-views/virtual-metabox/api/account-disabled.php
 *
 * @version 1.5.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var string $disabled_title The disabled title.
 * @var string $disabled_body  The disabled message.
 * @var string $link_url       The URL to generate a Zoom Meeting link.
 * @var string $link_label     The label of the button to generate a Zoom Meeting link.
 * @var  bool  $echo           Whether to echo the template to the page or not.
 */
?>
<div
	id="tribe-events-virtual-meetings-zoom"
	class="tribe-events-virtual-meetings-zoom-details"
>
	<div
		id="tribe-events-virtual-meetings-zoom"
		class="tec-events-virtual-meetings-video-source__inner tribe-events-virtual-meetings-zoom-error"
	>
		<?php
		 $this->template(
			'virtual-metabox/zoom/account-disabled-details',
			[
				'disabled_title' => $disabled_title,
				'disabled_body'  => $disabled_body,
				'link_url'       => $link_url,
				'link_label'     => $link_label,
			],
			 $echo
		);
		?>
	</div>
</div>
