<?php
/**
 * Plugin Name: Wikimedia Diff Blocks
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

namespace WikimediaDiff\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Require utility functions.
require_once __DIR__ . '/inc/namespace.php';

// Kick off.
bootstrap();
