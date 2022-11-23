import React from 'react';

import { CheckboxControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
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
	const { postStatus, currentUserRoles } = useSelect( ( select ) => {
		const currentUser = select( 'core' ).getCurrentUser();
		const postStatus = select( 'core/editor' ).getEditedPostAttribute( 'status' );
		const currentUserRoles = currentUser && currentUser.id
			? select( 'core' ).getUser( currentUser.id )?.roles || []
			: [];

		return {
			postStatus,
			currentUserRoles,
		};
	} );
	const showCheckbox = postStatus === 'draft' && ! currentUserRoles.includes( 'administrator' );
	const shouldLock = ! agreed && showCheckbox;

	// Put all the logic in the useEffect hook.
	useEffect( () => {
		if ( shouldLock ) {
			lockPostSaving();
		} else {
			unlockPostSaving();
		}

		// The dispatchers are static references, safe to ignore.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ shouldLock ] );

	return (
		<PluginPrePublishPanel title={ __( 'Publish Checklist', 'diff-blocks' ) } initialOpen>
			{  showCheckbox ? (
				<CheckboxControl
					checked={ agreed }
					onChange={ setDisclaimerAgreement }
					help={
						<p>
							<a target="_blank" rel="noopener noreferrer" href="https://creativecommons.org/licenses/by-sa/3.0/">
								{ __( 'CC BY-SA 3.0 license', 'diff-blocks' ) }
							</a>
						</p>
					}
					label={ __( 'By clicking publish, you agree to license your work under the CC BY-SA 3.0 license. Please be aware that this license is irrevocable and allows others to use and remix your work off of Wikimedia websites.', 'diff-blocks' ) }
				/>
			) : (
				<>
					<p>{ __( 'You agree to license your work under the CC BY-SA 3.0 license. Please be aware that this license is irrevocable and allows others to use and remix your work off of Wikimedia websites.', 'diff-blocks' ) }</p>
					<p>
						<a target="_blank" rel="noopener noreferrer" href="https://creativecommons.org/licenses/by-sa/3.0/">
							{ __( 'CC BY-SA 3.0 license', 'diff-blocks' ) }
						</a>
					</p>
				</>
			) }
		</PluginPrePublishPanel>
	);
};

export const name = 'content-licensing-notice';

export const settings = {
	render: ContentLicensingNotice,
};
