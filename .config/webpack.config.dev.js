/**
 * .config/webpack.config.dev.js :
 * This file defines the development build configuration
 */
const { helpers, externals, presets } = require( '@humanmade/webpack-helpers' );
const { choosePort, filePath, cleanOnExit } = helpers;

cleanOnExit( [
	filePath( 'client-mu-plugins/diff-blocks/dist/asset-manifest.json' ),
] );

module.exports = choosePort( 8181 ).then( port =>
	presets.development( {
		devServer: {
			port,
		},
		externals,
		entry: {
			editor: filePath( 'client-mu-plugins/diff-blocks/src/editor.js' ),
			frontend: filePath( 'client-mu-plugins/diff-blocks/src/frontend.js' ),
		},
		output: {
			path: filePath( 'client-mu-plugins/diff-blocks/dist' )
		},
	} )
);
