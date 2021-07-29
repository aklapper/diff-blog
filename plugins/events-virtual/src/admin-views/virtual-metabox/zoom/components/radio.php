<?php
/**
 * View: Virtual Events Metabox Zoom API Radio Input.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/virtual-metabox/zoom/components/radio.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 1.4.0
 *
 * @var string               $label      Label for the radio input.
 * @var string               $link       The link for the ajax request to generate the meeting or webinar.
 * @var string               $metabox_id The metabox current ID.
 * @var string               $class      Class attribute for the radio input.
 * @var string               $name       Name attribute for the radio input.
 * @var string|int           $checked    The checked radio option id.
 * @var string               $zoom_type  The type of Zoom event to create meeting or webinar.
 * @var array<string,string> $attrs      Associative array of attributes of the radio input.
 */
?>
<div
	class="tribe-events-virtual-meetings-zoom-control tribe-events-virtual-meetings-zoom-control--radio"
>
	<label
		for="<?php echo esc_attr( "{$metabox_id}-zoom-meeting-type" ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
	>
		<input
			id="<?php echo esc_attr( "{$metabox_id}-zoom-meeting-type" ); ?>"
			class="<?php echo esc_attr( $class ); ?>"
			name="<?php echo esc_attr( "{$metabox_id}[zoom-meeting-type]" ); ?>"
			type="radio"
			value="<?php echo esc_attr( $link ); ?>"
			<?php echo $checked === $zoom_type ? 'checked' : ''; ?>
			<?php tribe_attributes( $attrs ) ?>
		/>
		<?php echo esc_html( $label ); ?>
	</label>

</div>
