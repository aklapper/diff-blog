import React from 'react';

import { CheckboxControl } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * PrePublishChecklist plugin. Courtesy of SO.
 *
 * @returns {React.Node} Rendered checklist plugin.
 */
const ContentLicensingNotice = () => {
	const [ agreed, setDisclaimerAgreement ] = useState( false );
	const { lockPostSaving, unlockPostSaving } = useDispatch( 'core/editor' );

	// Put all the logic in the useEffect hook.
	useEffect( () => {
		if ( ! agreed ) {
			lockPostSaving();
		} else {
			unlockPostSaving();
		}

		// The dispatchers are static references, safe to ignore.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ agreed ] );

	return (
		<PluginPrePublishPanel title={ 'Publish Checklist' } initialOpen>
			<CheckboxControl
				checked={ agreed }
				onChange={ setDisclaimerAgreement }
				help={
					<p>
						<a target="blank" href="https://creativecommons.org/licenses/by-sa/3.0/">
							{ __( 'CC-BY-SA license details', 'diff-blocks' ) }
						</a>
					</p>
				}
				label={ __( 'I acknowledge that by submitting I license this content under Creative Commons Attribution-ShareAlike 3.0 (CC-BY-SA).', 'diff-blocks' ) }
			/>
		</PluginPrePublishPanel>
	);
};

export const name = 'content-licensing-notice';

export const settings = {
	render: ContentLicensingNotice,
};
