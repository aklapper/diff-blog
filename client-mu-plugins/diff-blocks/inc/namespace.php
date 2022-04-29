<?php

namespace WikimediaDiff\Interconnection_Editor;

/**
 * Add all our hooks.
 */
function bootstrap() {
	// Enqueue JS and CSS.
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_frontend_assets' );
}

/**
 * Enqueue block editor-only JavaScript and CSS.
 */
function enqueue_block_editor_assets() {

	$editor_js  = plugin_dir_url( dirname( __FILE__ ) ) . 'dist/editor.js';
	$editor_css = plugin_dir_url( dirname( __FILE__ ) ) . 'dist/editor.css';

	wp_enqueue_script(
		'interconnection-blocks-editor',
		$editor_js,
		[],
		'1.0.0',
		true
	);

	wp_enqueue_style(
		'interconnection-blocks-editor',
		$editor_css,
		[],
		'1.0.0',
		'all'
	);
}

/**
 * Enqueue front end and editor JavaScript and CSS assets.
 */
function enqueue_frontend_assets() {

	if ( is_admin() ) {
		return;
	}

	$frontend_js  = plugin_dir_url( dirname( __FILE__ ) ) . 'dist/frontend.js';
	$frontend_css = plugin_dir_url( dirname( __FILE__ ) ) . 'dist/frontend.css';

	wp_enqueue_script(
		'interconnection-blocks-frontend',
		$frontend_js,
		[],
		filemtime( $frontend_js ),
		true
	);

	$css_updated_time = filemtime( $frontend_css );
	if ( $css_updated_time ) {
		wp_enqueue_style(
			'interconnection-blocks-frontend',
			$frontend_css,
			[],
			$css_updated_time,
			'all'
		);
	}
}
