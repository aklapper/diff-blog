/**
 * Export all filters So they can be handled by the autoloader.
 */

/*
 * Namespace to register these filters. Not used, but necessary.
 */
export const name = 'interconnection/filters';

/*
 * Definition of filters to apply. Each requires certain fields.
 *
 * {
 *   @var {string} hook Filter to attach to.
 *   @var {String} namespace Name given to this callback function.
 *   @var {Function} callback Callback function to attach.
 *   @var {Number} priority Priority (defaults to 10).
 * }
 */
export const filters = [

	/**
	 * Add wide alignment to core paragraph blocks.
	 */
	{
		hook: 'blocks.registerBlockType',
		namespace: 'interconnection/paragraph-block-wide-align',
		callback: ( settings, name ) => {
			if ( name === 'core/paragraph' ) {
				return {
					...settings,
					supports: {
						...settings.supports,
						align: [
							'wide',
						],
					},

				}
			}

			return settings;
		},
	},

	/**
	 * Add "anchor" support to all registered block types.
	 */
	{
		hook: 'blocks.registerBlockType',
		namespace: 'interconnection/add-anchor-support',
		callback: settings => ( {
			...settings,
			supports: {
				...settings.supports,
				anchor: true,
			},
		} ),
		priority: 9, // Before the core filter that adds the 'anchor' attribute.
	},
];
