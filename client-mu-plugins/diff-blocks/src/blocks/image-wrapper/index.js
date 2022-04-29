/**
 * Custom block image wrapper.
 */

/**
 * WordPress dependencies.
 */
import { InnerBlocks } from '@wordpress/block-editor';
import { WPElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import './style.scss';

export const name = 'interconnection/image-wrapper';

const TEMPLATE = [
	[ 'core/image', {
		'linkDestination': 'media',
	} ],
];

export const settings = {
	title: __( 'Image Wrapper', 'interconnection' ),

	description: __( 'Wrapper to use for images.', 'interconnection' ),

	category: 'common',

	icon: 'carrot',

	/**
	 * @param {object} props Block properties.
	 * @param {string} props.className Block classname.
	 *
	 * @returns {WPElement} Image Wrapper edit block component.
	 */
	edit( { className } ) {
		return (
			<div className={ className }>
				<InnerBlocks
					template={ TEMPLATE }
					templateLock="all"
				/>
			</div>
		);
	},

	/**
	 * @param {object} props Block properties.
	 * @param {string} props.className Block classname.
	 *
	 * @returns {WPElement} Image Wrapper save block component.
	 */
	save( { className } ) {
		return (
			<div className={ className }>
				<InnerBlocks.Content />
			</div>
		);
	},
};
