<?php
/**
 * Plugin Name: Wikimedia Diff Comments
 * Plugin URI: https://github.com/wpcomvip/wikimedia-blog-wikimedia-org
 * Description: Modifies the default WordPress comments functionality.
 * Author: Human Made
 * Author URI: https://humanmade.com
 * Network: true
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package WikimediaDiff-Comments
 */

namespace WikimediaDiff\Comments;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/namespace.php';

bootstrap();
