<?php
/**
 * Plugin Name: Diff Security Plugin Integration
 */

namespace Diff\Security_Plugin_Integration;

/**
 * When the environment type is "local", add CSP allowed image origins to
 * permit proxying media requests through to deployed environment.
 *
 * @param string[] $allowed_origins List of origins to allow in this CSP.
 * @param string   $policy_type     CSP type.
 * @return string[] Filtered policy allowed origins array.
 */
function maybe_add_local_media_proxy_origins( array $allowed_origins, string $policy_type ): array {
	if ( 'local' !== wp_get_environment_type() ) {
		return $allowed_origins;
	}

	if ( 'img-src' === $policy_type ) {
		/**
		 * Permit proxying images through to production or to develop.
		 *
		 * @see https://docs.wpvip.com/how-tos/dev-env-add-media/#h-proxy-media-files
		 */
		$allowed_origins[] = 'https://diff.wikimedia.org/';
		$allowed_origins[] = 'https://blog-wikimedia-org-develop.go-vip.net';
	}

	return $allowed_origins;
}
add_filter( 'wmf/security/csp/allowed_origins', __NAMESPACE__ . '\\maybe_add_local_media_proxy_origins', 10, 2 );

/**
 * Add CSP allowed iframe origins to permit proxying embedded media requests.
 *
 * @param string[]  $allowed_origins List of origins to allow in this CSP.
 * @param string    $policy_type     CSP type.
 *
 * @return string[] Filtered list of permitted origins.
 */
function permit_custom_iframe_providers( array $allowed_origins, string $policy_type ) : array {
	if ( in_array( $policy_type, [ 'frame-src' ], true ) ) {
		$allowed_origins[] = 'https://*.wikimedia.org';
		$allowed_origins[] = 'https://*.wikipedia.org';
		$allowed_origins[] = 'https://datawrapper.dwcdn.net/';
	}

	return $allowed_origins;
}
add_filter( 'wmf/security/csp/allowed_origins', __NAMESPACE__ . '\\permit_custom_iframe_providers', 10, 2 );
