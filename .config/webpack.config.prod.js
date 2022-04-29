/**
 * .config/webpack.config.prod.js :
 * This file defines the production build configuration
 */
const { helpers, externals, plugins, presets } = require( '@humanmade/webpack-helpers' );
const { filePath } = helpers;

module.exports = presets.production( {
	externals,
	entry: {
		editor: filePath( 'client-mu-plugins/diff-blocks/src/editor.js' ),
		frontend: filePath( 'client-mu-plugins/diff-blocks/src/frontend.js' ),
	},
	output: {
		path: filePath( 'client-mu-plugins/diff-blocks/dist' ),
	},
	plugins: [
		plugins.clean(),
	],
} );
