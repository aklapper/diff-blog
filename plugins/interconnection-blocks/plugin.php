<?php
/**
 * Plugin Name: Interconnection Blocks
 * Plugin URI: https://github.com/wpcomvip/wikimedia-blog-wikimedia-org
 * Description: Blocks and other editorial functionality for the Interconnection theme.
 * Author: Human Made
 * Author URI: https://humanmade.com/
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package wikimediadiff
 */

namespace WikimediaDiff\Interconnection_Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Require utility functions.
require_once __DIR__ . '/inc/namespace.php';

// Kick off.
add_action(
	'plugins_loaded',
	function () {
		bootstrap();
	}
);
