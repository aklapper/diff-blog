<?php
/**
 * Modified comment features for Wikimedia Diff blog.
 *
 * @package wikimediadiff
 */

namespace WikimediaDiff\Comments;

use WP_CLI;

/**
 * Setup filters and actions for the namespace.
 */
function bootstrap() {
	add_filter( 'pre_comment_user_ip', __NAMESPACE__ . '\\filter_comment_user_ip' );
}

/**
 * Filter the comment author’s IP address before it is recorded.
 *
 * @param string $comment_author_ip The comment author's IP address.
 *
 * @return string
 */
function filter_comment_user_ip( $comment_author_ip ) {
	// Return an empty string so nothing gets recorded.
	$comment_author_ip = '';

	return $comment_author_ip;
}
