<?php
/**
 * Plugin Name: Handle non-ASCII MediaWiki OAuth usernames
 * Plugin Description: Prevent login errors when WP sanitizes a non-ASCII OAuth username into an empty string.
 */

namespace MW_OAuth_Username_Reconciliation;

/**
 * Check whether a username string matches the encoded format we expect.
 *
 * @param ?string $username Username to check.
 * @return bool Whether it matches our expected format. 
 */
function is_reencoded_username( ?string $username ) : bool {
	if ( empty( $username ) ) {
		return false;
	}
	return ! ! preg_match( '/^mw_(u[0-9a-e]{4})+/', $username );
}

/**
 * If a username has been sanitized to an empty string, re-parse the
 * raw version into a WP-compatible identifier.
 *
 * @param string $username     Username as sanitized by WordPress.
 * @param string $raw_username The username prior to sanitization.
 * @return string Filtered username.
 */
function provide_valid_username_for_non_ascii_mw_users( string $username, string $raw_username ) : string {
	if ( ! empty( $username ) ) {
		return $username;
	}

	// JSON encoding will render all scripts to a sequence of \u#### character
	// representations. Prefix with mw_ due to assumed MediaWiki origin.
	return 'mw_' . substr( preg_replace( '/[^A-Za-z0-9]/', '', wp_json_encode( $raw_username ) ), 0, 50 );
}
add_filter( 'sanitize_user', __NAMESPACE__ . '\\provide_valid_username_for_non_ascii_mw_users', 10, 2 );

/**
 * Override a reencoded user's display and nicenames to use their originally-
 * provided username value.
 *
 * @param array $data     Array of user data to be inserted.
 * @param bool  $update   Whether the user is being updated rather than created.
 * @param ?int  $user_id  ID of the user to be updated, or NULL if the user is being created.
 * @param array $userdata Raw array passed to wp_insert_user().
 * @return array Filtered array.
 */
function restore_original_nicename_for_reencoded_users( array $data, bool $update, $user_id, array $userdata ) : array {
	if ( $user_id || $update ) {
		// User is being updated, not created: no action needed.
		return $data;
	}

	if ( ! is_reencoded_username( $data['user_login'] ?? '' ) ) {
		// We didn't adjust this record: no action needed.
		return $data;
	}

	// The original array will hold the properly-encoded username from the
	// MediaWiki side. Use that value as display_name and user_nicename.
	$display_name = mb_substr( strip_tags( $userdata['user_login'] ?? '' ), 0, 50 );
	if ( ! empty( $display_name ) ) {
		$data['user_nicename'] = $display_name;
		$data['display_name'] = $display_name;
	}

	return $data;
}
add_filter( 'wp_pre_insert_user_data', __NAMESPACE__ . '\\restore_original_nicename_for_reencoded_users', 10, 4 );

/**
 * Override a reencoded user's nickname to match their display_name.
 *
 * @param array   $meta     Default meta values and keys for the user.
 * @param WP_User $user     User object.
 * @param bool    $update   Whether the user is being updated rather than created.
 * @param array   $userdata The raw array of data passed to wp_insert_user().
 * @return array Filtered meta.
 */
function set_nickname_for_reencoded_users( $meta, $user, $update, $userdata ) {
	if ( $update ) {
		// User is being updated, not created: no action needed.
		return $meta;
	}

	if ( ! is_reencoded_username( $meta['nickname'] ?? '' ) ) {
		// We didn't adjust this record: no action needed.
		return $meta;
	}

	// The original array will hold the properly-encoded username from the
	// MediaWiki side. Use that value as meta nickname.
	$display_name = mb_substr( strip_tags( $userdata['user_login'] ?? '' ), 0, 50 );
	if ( ! empty( $display_name ) ) {
		$meta['nickname'] = $display_name;
	}

	return $meta;
}
add_filter( 'insert_user_meta', __NAMESPACE__ . '\\set_nickname_for_reencoded_users', 10, 4 );
