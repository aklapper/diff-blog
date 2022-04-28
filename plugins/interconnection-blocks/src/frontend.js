const cache = {};

const importAll = ( context ) => context.keys().forEach( ( key ) => {
	cache[ key ] = context( key );
} );

/**
 * Import all frontend files for blocks.
 *
 * Any `block.js` or `block.scss` file in a block directory in
 * this directory gets built into the frontend bundle for the
 * site.
 */
const context = require.context( './blocks', true, /block\.(scss|js)$/ );

importAll( context );
