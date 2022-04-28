/**
 * .config/webpack.config.dev.js :
 * This file defines the development build configuration
 */
const { helpers, externals, presets } = require( '@humanmade/webpack-helpers' );
const { choosePort, filePath } = helpers;

module.exports = choosePort( 8080 ).then( port =>
	presets.development( {
		devServer: {
			port,
		},
		externals,
		entry: {
			editor: filePath( 'plugins/interconnection-blocks/src/editor.js' ),
			frontend: filePath( 'plugins/interconnection-blocks/src/frontend.js' ),
		},
		output: {
			path: filePath( 'plugins/interconnection-blocks/dist' )
		},
	} )
);
