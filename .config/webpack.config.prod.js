/**
 * .config/webpack.config.prod.js :
 * This file defines the production build configuration
 */
const { helpers, externals, presets } = require( '@humanmade/webpack-helpers' );
const { filePath } = helpers;

module.exports = presets.production( {
	externals,
	entry: {
		editor: filePath( 'plugins/interconnection-blocks/src/editor.js' ),
		frontend: filePath( 'plugins/interconnection-blocks/src/frontend.js' ),
	},
	output: {
		path: filePath( 'plugins/interconnection-blocks/dist' ),
	},
} );
