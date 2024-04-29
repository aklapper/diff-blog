<?php
/**
 * Base namespace for the Diff Blocks editorial features plugin.
 */

namespace WikimediaDiff\Blocks;

use Asset_Loader;

/**
 * Add all our hooks.
 */
function bootstrap() {
	// Enqueue JS and CSS.
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_frontend_assets' );
}

/**
 * Get the manifest for the site, using the dev server if available.
 */
function get_webpack_manifest() : ?string {
	$plugin_path = trailingslashit( plugin_dir_path( dirname( __FILE__, 1 ) ) );

	return Asset_Loader\Manifest\get_active_manifest( [
		$plugin_path . 'dist/development-asset-manifest.json',
		$plugin_path . 'dist/production-asset-manifest.json',
	] );
}

/**
 * Enqueue block editor-only JavaScript and CSS.
 */
function enqueue_block_editor_assets() {
	$manifest = get_webpack_manifest();

	if ( empty( $manifest ) ) {
		return;
	}

	Asset_Loader\enqueue_asset(
		$manifest,
		'editor.js',
		[
			'dependencies' => [
				'wp-block-editor',
				'wp-blocks',
				'wp-element',
				'wp-i18n',
			],
			'handle' => 'diff-blocks'
		]
	);

	Asset_Loader\enqueue_asset(
		$manifest,
		'editor.css',
		[
			'dependencies' => [],
			'handle' => 'diff-blocks'
		]
	);

	wp_localize_script(
		'diff-blocks',
		'diffBlocksData',
		[
			'isAdminRole' => current_user_can( 'manage_options' ),
		]
	);
}

/**
 * Enqueue front end and editor JavaScript and CSS assets.
 */
function enqueue_frontend_assets() {
	if ( is_admin() ) {
		return;
	}

	$manifest = get_webpack_manifest();

	if ( empty( $manifest ) ) {
		return;
	}

	Asset_Loader\enqueue_asset(
		$manifest,
		'frontend.js',
		[
			'dependencies' => [],
			'handle' => 'diff-blocks-frontend'
		]
	);

	Asset_Loader\enqueue_asset(
		$manifest,
		'frontend.css',
		[
			'dependencies' => [],
			'handle' => 'diff-blocks-frontend'
		]
	);
}
