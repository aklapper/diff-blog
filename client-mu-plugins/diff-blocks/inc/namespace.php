<?php
/**
 * Base namespace for the Diff Blocks editorial features plugin.
 */

namespace WikimediaDiff\Blocks;

use WikimediaDiff\Asset_Loader;

/**
 * Add all our hooks.
 */
function bootstrap() {
	// Enqueue JS and CSS.
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
}

/**
 * Enqueue block editor-only JavaScript and CSS.
 */
function enqueue_block_editor_assets() {
	Asset_Loader\enqueue_script_asset(
		'diff-blocks',
		plugin_dir_path( __DIR__ ) . 'build/editor.asset.php',
		plugins_url( 'build/editor.js', __DIR__ )
	);

	wp_localize_script(
		'diff-blocks',
		'diffBlocksData',
		[
			'isAdminRole' => current_user_can( 'manage_options' ),
		]
	);
}
