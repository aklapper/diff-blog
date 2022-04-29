<?php
/**
 * Block: Virtual Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-virtual/blocks/virtual-event.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 1.7.1
 *
 */
?>
<div class="tribe-block tribe-block--virtual-event">
    <?php
		/**
		 * Action to allow injecting the block content from various providers.
		 *
		 * @since 1.7.1
		 */
		do_action( 'tribe_events_virtual_block_content' );
	?>
</div>
