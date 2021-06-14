<?php
/**
 * View: Virtual Events Zoom Settings Switch.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/zoom/api/components/switch.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var string               $label   Label for the dropdown input.
 * @var string               $id      ID of the dropdown input.
 * @var array<string,string> $classes An array of classes for the switch input.
 * @var string               $name    Name attribute for the dropdown input.
 * @var string|int           $value   The value of the swtich.
 * @var string|int           $checked Whether the switch is enabled or not.
 * @var array<string,string> $attrs   Associative array of attributes of the dropdown.
 */
$switch_classes = [ 'tribe-events-virtual-meetings-zoom-settings-switch__input' ];

if ( ! empty( $classes ) ) {
	$switch_classes = array_merge( $switch_classes, $classes );
}
?>
<div
	class="tribe-events-virtual-meetings-zoom-control tribe-events-virtual-meetings-zoom-control--switch"
>
	<input
		<?php tribe_classes( $switch_classes ); ?>
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="checkbox"
		value="<?php echo esc_attr( $value ); ?>"
		<?php checked( true, tribe_is_truthy( $checked ) ); ?>
		<?php tribe_attributes( $attrs ) ?>
	/>

	<label class="tribe-events-virtual-meetings-zoom-settings-switch__label" for="<?php echo esc_attr( $id ); ?>">
		<span class="screen-reader-text">
			<?php echo esc_html( $label ); ?>
		</span>
	</label>
</div>
