<?php
/**
 * View: Virtual Events Metabox Zoom API Create Meeting/Webinar Type Options.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/type-options.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.8.2
 * @deprecated 1.9.0 Use src/admin-views/virtual-metabox/api/type-options.php.
 *
 * @version 1.8.2
 *
 * @link    http://m.tri.be/1aiy
 *
 * @var \WP_Post             $event                   The event post object, as decorated by the `tribe_get_event` function.
 * @var array<string,string> $generation_urls         A map of the available URL generation labels and URLs.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

?>

<div class="tribe-events-virtual-meetings-api-create__types tribe-events-virtual-meetings-zoom-details__types">
	<?php foreach ( $generation_urls as $zoom_type => list( $generate_link_url, $generate_link_label, $disabled, $tooltip ) ) : ?>
		<?php
		$this->template( 'virtual-metabox/zoom/components/radio', [
			'metabox_id'    => 'tribe-events-virtual',
			'zoom_type'     => $zoom_type,
			'link'          => $generate_link_url,
			'label'         => $generate_link_label,
			'classes_label' => [ $disabled ? 'disabled' : '' ],
			'checked'       => 'meeting',
			'disabled'      => $disabled,
			'tooltip'       => $tooltip,
			'attrs'         => [
				'placeholder'    =>
					_x(
						'Select a Host',
						'The placeholder for the dropdown to select a host.',
						'events-virtual'
					),
				'data-zoom-type' => $zoom_type,
			],
		] );
		?>
	<?php endforeach; ?>
</div>
