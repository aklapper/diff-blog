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
 * @since   1.4.0
 * @since   1.8.2 - Add disabled option and tooltip.
 * @deprecated 1.9.0 Use src/admin-views/components/radio.php.
 *
 * @version 1.8.2
 *
 * @var string               $label         Label for the radio input.
 * @var string               $link          The link for the ajax request to generate the meeting or webinar.
 * @var string               $metabox_id    The metabox current ID.
 * @var string               $classes_label Class attribute for the radio input label.
 * @var string               $name          Name attribute for the radio input.
 * @var string|int           $checked       The checked radio option id.
 * @var boolean              $disabled      The checked radio option id.
 * @var string               $zoom_type     The type of Zoom event to create meeting or webinar.
 * @var array<string,string> $attrs         Associative array of attributes of the radio input.
 */

$label_classes = [ 'tec-events-virtual-meetings-control__label' ];
if ( ! empty( $classes_label ) ) {
	$label_classes = array_merge( $label_classes, $classes_label );
}

?>
<div
	class="tribe-events-virtual-meetings-zoom-control tribe-events-virtual-meetings-zoom-control--radio-wrap"
>
	<label
		for="<?php echo esc_attr( "{$metabox_id}-zoom-meeting-type" ); ?>"
		<?php tribe_classes( $label_classes ); ?>
	>
		<input
			id="<?php echo esc_attr( "{$metabox_id}-zoom-meeting-type" ); ?>"
			class="tribe-events-virtual-meetings-zoom-control--radio-input"
			name="<?php echo esc_attr( "{$metabox_id}[zoom-meeting-type]" ); ?>"
			type="radio"
			value="<?php echo esc_attr( $link ); ?>"
			<?php tribe_attributes( $attrs ) ?>
			<?php echo $checked === $zoom_type ? 'checked' : ''; ?>
			<?php echo $disabled ? 'disabled' : ''; ?>
		/>
		<?php echo esc_html( $label ); ?>
	</label>
	<?php
		if ( ! empty( $tooltip['message'] ) ) {
			$this->template( 'components/tooltip', $tooltip );
		}
	?>
</div>
