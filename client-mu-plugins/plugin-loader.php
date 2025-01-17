<?php
/*
 * We recommend all plugins for your site are
 * loaded in code, either from a file like this
 * one or from your theme (if the plugins are
 * specific to your theme and do not need to be
 * loaded as early as this in the WordPress boot
 * sequence.
 *
 * @see https://vip.wordpress.com/documentation/vip-go/understanding-your-vip-go-codebase/
 */

// wpcom_vip_load_plugin( 'plugin-name' );
/**
 * Note the above requires a specific naming structure: /plugin-name/plugin-name.php
 * You can also specify a specific root file: wpcom_vip_load_plugin( 'plugin-name/plugin.php' );
 *
 * wpcom_vip_load_plugin only loads plugins from the `WP_PLUGIN_DIR` directory.
 * For client-mu-plugins `require __DIR__ . '/plugin-name/plugin-name.php'` works.
 */
// https://github.com/humanmade/asset-loader, for loading webpack-generated asset bundles.
require_once __DIR__ . '/asset-loader/asset-loader.php';
// Diff's own internal asset-loader.
require_once __DIR__ . '/diff-asset-loader/plugin.php';
// Editor blocks for the Diff site.
require_once __DIR__ . '/diff-blocks/plugin.php';
// Custom comments functionality for the Diff site.
require_once __DIR__ . '/diff-comments/plugin.php';

/**
 * Disable New Relic browser monitoring.
 *
 * See mu-plugins/vip-helpers/vip-newrelic.php for implementation of
 * the hook handler.
 */
add_action( 'template_redirect', 'wpcom_vip_disable_new_relic_js' );


//wpcom_vip_load_plugin( 'amp' );
//wpcom_vip_load_plugin('publishpress');
//wpcom_vip_load_plugin('capability-manager-enhanced/capsman-enhanced.php');
//wpcom_vip_load_plugin('polylang/polylang.php');
//wpcom_vip_load_plugin('wpdiscuz/class.WpdiscuzCore.php');
//wpcom_vip_load_plugin('wpdiscuz-comment-search/wpDiscuzCommentSearch.php');
//wpcom_vip_load_plugin('wpdiscuz-comment-translation/wpdiscuz-translate.php');
//wpcom_vip_load_plugin('wpdiscuz-frontend-moderation/class.wpDiscuzFrontEndModeration.php');
//wpcom_vip_load_plugin('wpdiscuz-report-flagging/wpDiscuzFlagComment.php');
//wpcom_vip_load_plugin('wpdiscuz-subscribe-manager/wpdSubscribeManager.php');
//wpcom_vip_load_plugin('wpdiscuz-syntax-highlighter/wpDiscuzSyntaxHighlighter.php');
//wpcom_vip_load_plugin('wpdiscuz-user-comment-mentioning/WpdiscuzUCM.php');
//wpcom_vip_load_plugin('wpdiscuz-widgets/wpDiscuzWidgets.php');
//wpcom_vip_load_plugin('co-authors-plus/co-authors-plus.php');
//wpcom_vip_load_plugin('fieldmanager/fieldmanager.php');
//wpcom_vip_load_plugin('diff-customizations/diff-customizations.php');
