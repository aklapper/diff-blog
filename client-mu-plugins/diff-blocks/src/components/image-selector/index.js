import PropTypes from 'prop-types';
import { getBlobByURL, isBlobURL } from '@wordpress/blob';
import { IconButton } from '@wordpress/components';
import { mediaUpload, MediaPlaceholder, MediaUpload } from '@wordpress/block-editor';
import classnames from 'classnames';

import './style.scss';

const ALLOWED_TYPES = [ 'image' ];

/**
 * A reusable for control for selecting or managing an image inside blocks.
 *
 * Shows a media placeholder component if no image is selected, and the
 * currently selected image otherwise.
 */
const ImageSelector = ( { className, imageID, imageAlt, imageSrc, crop, property, setAttributes } ) => {

	/**
	 * Handle an uploaded or newly selected image. Selects the desired crop
	 * size if it exists.
	 *
	 * Uploaded images are only given a blob URL when the media upload
	 * component initially returns, so we have to handle actually performing
	 * the media upload in this case.
	 */
	const onSelect = img => {

		if ( img && img.id && img.sizes ) {
			setAttributes( {
				[ `${property}ID` ]: img.id,
				[ `${property}Src` ]: ( crop in img.sizes ) ? img.sizes[ crop ].url : img.url,
				[ `${property}Alt` ]: img.alt,
			} );

			return;
		}

		if ( isBlobURL( img.url ) ) {
			const file = getBlobByURL( img.url );

			if ( file ) {
				mediaUpload( {
					filesList: [ file ],
					onFileChange: ( [ image ] ) => {
						if ( image.media_details ) {
							setAttributes( {
								[ `${property}ID` ]: image.id,
								[ `${property}Src` ]: ( crop in image.media_details.sizes ) ? image.media_details.sizes[ crop ].source_url : image.url,
								[ `${property}Alt` ]: image.alt,
							} );
						}
					},
					allowedTypes: ALLOWED_TYPES,
				} );
			}
		}
	};

	return imageID ? (
		<div
			className={ classnames(
				'image-selector__holder',
				`${className}__image`,
				`${className}__image-holder`
			) }
		>
			<img
				className={ `${className}__image` }
				data-id={ imageID }
				alt={ imageAlt }
				src={ imageSrc }
			/>
			<MediaUpload
				onSelect={ onSelect }
				allowedTypes={ ALLOWED_TYPES }
				value={ imageID }
				render={ ( { open } ) => (
					<IconButton
						icon="edit"
						className={ classnames(
							'image-selector__edit',
							`${className}__image-edit`
						) }
						onClick={ open }
					/>
				) }
			/>
		</div>
	) : (
		<MediaPlaceholder
			className={ `${className}__image` }
			onSelect={ onSelect }
			allowedTypes={ ALLOWED_TYPES }
			multiple={ false }
			labels={ { title: 'Upload Image' } }
		/>
	);
};

ImageSelector.propTypes = {
	// Base class name to apply to elements generated.
	className: PropTypes.string,
	// Base name of attributes to set on the parent when setAttributes is called.
	property: PropTypes.string,
	// ID of currently selected image.
	imageID: PropTypes.number,
	// Alt text for selected image.
	imageAlt: PropTypes.string,
	// URL to use as src for image.
	imageSrc: PropTypes.string,
	// Desired image crop (falls back to full size if not set of not available).
	crop: PropTypes.string,
	// The setAttributes callback from the block using this component.
	setAttributes: PropTypes.func.isRequired,
};

ImageSelector.defaultProps = {
	className: 'image',
	property: 'image',
};

export default ImageSelector;
