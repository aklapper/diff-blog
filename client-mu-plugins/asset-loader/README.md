# Asset Loader

This plugin exposes functions which may be used within other WordPress themes or plugins to aid in detecting and loading assets generated by Webpack, including those served from local `webpack-dev-server` instances.

[![Build Status](https://travis-ci.com/humanmade/asset-loader.svg?branch=main)](https://travis-ci.com/humanmade/asset-loader)

## Usage

This library is designed to work in conjunction with a Webpack configuration (such as those created with the presets in [@humanmade/webpack-helpers](https://github.com/humanmade/webpack-helpers)) which generate an asset manifest file. This manifest associates asset bundle names with either URIs pointing to asset bundles on a running DevServer instance, or else local file paths on disk.

### `Asset_Loader\register_asset()` and `Asset_Loader\enqueue_asset()`

`Asset_Loader` provides a set of methods for reading in this manifest file and registering a specific resource within it to load within your WordPress website. The primary public interface provided by this plugin is a pair of methods, `Asset_Loader\register_asset()` and `Asset_Loader\enqueue_asset()`. To register a manifest asset call one of these methods inside actions like `wp_enqueue_scripts` or `enqueue_block_editor_assets`, in the same manner you would have called the standard WordPress `wp_register_script` or `wp_enqueue_style` functions.

```php
<?php
namespace My_Theme\Scripts;

use Asset_Loader;

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets' );

/**
 * Enqueue the JS and CSS for blocks in the editor.
 *
 * @return void
 */
function enqueue_block_editor_assets() {
  Asset_Loader\enqueue_asset(
    // In a plugin, this would be `plugin_dir_path( __FILE__ )` or similar.
    get_stylesheet_directory() . '/build/asset-manifest.json',
    // The handle of a resource within the manifest. For static file fallbacks,
    // this should also match the filename on disk of a build production asset.
    'editor.js',
    [
      'handle'       => 'optional-custom-script-handle',
      'dependencies' => [ 'wp-element', 'wp-editor' ],
    ]
  );

  Asset_Loader\enqueue_asset(
    // In a plugin, this would be `plugin_dir_path( __FILE__ )` or similar.
    get_stylesheet_directory() . '/build/asset-manifest.json',
    // Enqueue CSS for the editor.
    'editor.css',
    [
      'handle'       => 'custom-style-handle',
      'dependencies' => [ 'some-style-dependency' ],
    ]
  );
}
```

To register an asset to be manually enqueued later, use `Asset_Loader\register_asset()` instead of `enqueue_asset()`. Both methods take the same arguments.

If a manifest is not present then `Asset_Loader` will attempt to load the specified resource from the same directory containing the manifest file.

By default, all enqueues will be added at the end of the page, in the `wp_footer` action. If you need your script to be enqueued in the document `<head>`, pass the flag `'in-footer' => false,` within the options array.

## Local Development

Before submitting a pull request, ensure that your PHP code passes all existing unit tests and conforms to our [coding standards](https://github.com/humanmade/coding-standards) by running these commands:

```sh
composer lint
composer test
```

If the above commands do not work, ensure you have [Composer](https://getcomposer.org/) installed on your machine & run `composer install` from the project root.

## Migrating from v0.3

Prior to v0.4, the main public interface exposed by this package was a pair of methods named `autoenqueue` and `autoregister`. Internally these methods used a somewhat "magical" and inefficient method of filtering through asset resources. They are deprecated as of v0.4, and have since been fully removed.

The following snippet of v0.3-compatible code using `autoenqueue` can be replaced fully with the `Asset_Loader\enqueue_asset()` example above.

```php
<?php
namespace My_Plugin_Or_Theme;

use Asset_Loader;

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets' );

/**
 * Enqueue the JS and CSS for blocks in the editor.
 *
 * @return void
 */
function enqueue_block_editor_assets() {
  Asset_Loader\autoenqueue(
    // In a plugin, this would be `plugin_dir_path( __FILE__ )` or similar.
    get_stylesheet_directory() . '/build/asset-manifest.json',
    // The output filename of the Webpack build.
    // At present this must be consistent between development & production builds.
    'editor.js',
    [
      'scripts' => [ 'wp-element', 'wp-editor' ],
      'handle'  => 'optional-manually-specified-script-handle',
    ]
  );
}
```

## License

This plugin is free software. You can redistribute it and/or modify it under the terms of the [GNU General Public License](LICENSE) as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
