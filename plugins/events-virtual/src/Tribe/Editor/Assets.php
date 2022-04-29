<?php
namespace Tribe\Events\Virtual\Editor;

use Tribe\Events\Virtual\Plugin;

/**
 * Events Virtual Gutenberg Assets.
 *
 * @since 1.7.1
 */
class Assets extends \tad_DI52_ServiceProvider {
	/**
	 * Registers and Enqueues the assets.
	 *
	 * @since 1.7.1
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		$plugin = tribe( Plugin::class );

		tribe_asset(
			$plugin,
			'tribe-virtual-gutenberg-data',
			'app/data.js',
			[
				'react',
				'react-dom',
				'wp-components',
				'wp-api',
				'wp-api-request',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
				'tribe-common-gutenberg-data',
				'tribe-common-gutenberg-utils',
				'tribe-common-gutenberg-store',
				'tribe-common-gutenberg-hoc',
			],
			'enqueue_block_editor_assets',
			[
				'in_footer' => false,
				'localize'  => [],
				'priority'  => 200,
				'conditionals' => tribe_callback(  'events.editor', 'is_events_post_type' ),
			]
		);

		tribe_asset(
			$plugin,
			'tribe-virtual-gutenberg-blocks',
			'app/blocks.js',
			[
				'react',
				'react-dom',
				'wp-components',
				'wp-api',
				'wp-api-request',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
				'tribe-common-gutenberg-data',
				'tribe-common-gutenberg-utils',
				'tribe-common-gutenberg-store',
				'tribe-common-gutenberg-icons',
				'tribe-common-gutenberg-hoc',
				'tribe-common-gutenberg-elements',
				'tribe-common-gutenberg-components',
			],
			'enqueue_block_editor_assets',
			[
				'in_footer' => false,
				'localize'  => [],
				'priority'  => 201,
				'conditionals' => tribe_callback(  'events.editor', 'is_events_post_type' ),
				'translations' => [
					'domain' => 'events-virtual',
					'path'   => $plugin->plugin_path . 'lang',
				],
			]
		);
	}
}
