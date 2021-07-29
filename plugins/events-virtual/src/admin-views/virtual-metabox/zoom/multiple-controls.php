<?php
/**
 * View: Virtual Events Metabox Zoom API link controls for two or more generator links.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/controls.php
 *
 * See more documentation about our views templating system.
 *
 * @since   1.1.1
 * @deprecated 1.4.0 Use setup.php
 *
 * @version 1.4.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \WP_Post             $event                   The event post object, as decorated by the `tribe_get_event`
 *      function.
 * @var string               $offer_or_label          The localized "or" string.
 * @var string               $generation_toogle_label The label of the accordion button to show the generation links.
 * @var array<string,string> $generation_urls         A map of the available URL generation labels and URLs.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

?>

<div
	id="tribe-events-virtual-meetings-zoom"
	class="tribe-events-virtual-meetings-zoom tribe-events-virtual-meetings-zoom-controls tribe-events-virtual-meetings-zoom-controls--multi"
>
	<span class="tribe-events-virtual-meetings-zoom__or-label">
		<?php echo esc_html( $offer_or_label ); ?>
	</span>

	<div
		class="tribe-events-virtual-meetings-zoom-controls__accordion-wrapper"
	>

		<button
			type="button"
			class="button tribe-events-virtual-meetings-zoom-controls__accordion-element tribe-events-virtual-meetings-zoom-controls__accordion-toggle"
			data-js="tribe-events-accordion-trigger"
			aria-controls="tribe-events-virtual-meetings-zoom__generator-links"
			aria-expanded="false"
		>
			<?php echo esc_html( $generation_toogle_label ); ?>
			<span
				class="tribe-events-virtual-meetings-zoom-controls__accordion-toggle__arrow"
			>
			</span>
		</button>

		<div
			class="tribe-events-virtual-meetings-zoom-controls__accordion-element tribe-events-virtual-meetings-zoom-controls__accordion-contents"
			id="tribe-events-virtual-meetings-zoom__generator-links"
			aria-hidden="true"
		>
			<ul>
				<?php foreach ( $generation_urls as list( $generate_link_url, $generate_link_label ) ) : ?>
					<li>
						<a
							class="tribe-events-virtual-meetings-zoom__create-link tribe-events-virtual-meetings-zoom__create-link--multi"
							href="<?php echo esc_url( $generate_link_url ); ?>"
						>
							<?php echo esc_html( $generate_link_label ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

	</div>

</div>
