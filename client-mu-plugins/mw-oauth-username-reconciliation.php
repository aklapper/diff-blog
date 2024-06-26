<?php
/**
 * Plugin Name: Handle non-ASCII MediaWiki OAuth usernames
 * Plugin Description: Provide an alternative valid username if WP sanitizes a non-ASCII OAuth username into an empty string.
 */

namespace MW_OAuth_Username_Reconciliation;

/**
 * If a username has been sanitized to an empty string, use JSON conversion to
 * parse the non-ASCII username into a valid WP username string.
 *
 * @param string $username     Username as sanitized by WordPress.
 * @param string $raw_username The username prior to sanitization.
 */
function provide_valid_username_for_non_ascii_mw_users( string $username, string $raw_username ) : string {
	if ( empty( $username ) ) {
		// JSON encoding will render all scripts to a sequence of \u### character representations.
		$username = preg_replace( '/[^A-Za-z0-9]/', '', wp_json_encode( $raw_username ) );
		return substr( $username, 0, 60 );
	}
	return $username;
}
add_filter( 'sanitize_user', __NAMESPACE__ . '\\provide_valid_username_for_non_ascii_mw_users', 10, 2 );
