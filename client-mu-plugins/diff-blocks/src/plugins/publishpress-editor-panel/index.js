import { useDispatch } from '@wordpress/data';

const { isAdminRole } = window.diffBlocksData;

/**
 * Remove PublishPress plugin Notifications panel.
 */
const RemovePublishPressPanel = () => {
	const panelID = 'publishpress-notifications';
	const { removeEditorPanel } = useDispatch( 'core/edit-post' );

	if ( ! isAdminRole ) {
		removeEditorPanel( 'meta-box-' + panelID );
	}
};

export const name = 'publishpress-editor-panel';

export const settings = {
	render: RemovePublishPressPanel,
};
