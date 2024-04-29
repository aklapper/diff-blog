<?php
/**
 * Plugin Name: Diff Security Plugin Integration
 */

namespace Diff\Security_Plugin_Integration;

use WP_REST_Request;

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
 * Enable a specific REST API endpoint to be accessed without authentication.
 *
 * @param bool            $is_allowed Whether the endpoint is publicly accessible, false by default.
 * @param WP_REST_Request $request    Active REST Request object.
 *
 * @return bool Whether the anonymous request should be permitted.
 */
function allow_anonymous_access_to_specific_endpoint( bool $is_allowed, WP_REST_Request $request ) : bool {
	// Allow The Events Calendar plugin route.
	if ( strpos( $request->get_route(), '/tribe/views/' ) !== false ) {
		return true;
	}

	return $is_allowed;
}
add_filter( 'wmf/security/rest_api/public_endpoint', __NAMESPACE__ . '\\allow_anonymous_access_to_specific_endpoint', 10, 2 );
