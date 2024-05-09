import { registerPlugin, unregisterPlugin } from '@wordpress/plugins';
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

const name = 'publishpress-editor-panel';

const settings = {
	render: RemovePublishPressPanel,
};

registerPlugin( name, settings );

// HMR boilerplate.
if ( module.hot ) {
	module.hot.accept();
	module.hot.dispose( () => unregisterPlugin( name ) );
}
