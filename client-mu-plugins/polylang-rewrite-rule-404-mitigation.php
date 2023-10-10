<?php
/**
 * Plugin Name: Polylang 404 rewrite issue mitigation
 * Plugin Author: Human Made
 * Plugin Description: Attempt to reset rewrites if they disappear.
 *
 * Plugin created 2023-10 while troubleshooting Polylang rewrites issue: All
 * Polylang rewrites periodically disappear from the site on VIP, causing every
 * non-English content URI to become inaccessible.
 *
 * @see https://support.wpvip.com/hc/en-us/requests/172999
 * @see https://hmn.slack.com/archives/C011TP2PSHJ/p1693405818743349
 * @see https://github.com/humanmade/Wikimedia/issues/905
 */

namespace Polylang_Rewrite_Rule_404_Mitigation;

use WP_Query;

const CACHE_KEY = 'polylang_debugging_cache_flush_time';
const CACHE_GROUP = 'polylang_debugging';

/**
 * Get the activation state of Polylang Pro.
 */
function is_polylang_active() : bool {
    return is_plugin_active( 'polylang-pro/polylang.php' );
}

/**
 * Connect namespace functions to actions and hooks.
 */
function bootstrap() : void {
    add_action( 'set_404', __NAMESPACE__ . '\\check_polylang_rewrite_status_on_404' );
}
bootstrap();

/**
 * Check whether Polyang URLs are present while handling a 404, and attempt to
 * reset the rewrite state on the site by deleting rewrite_rules option if they
 * are missing.
 *
 * @param WP_Query $query
 * @return void
 */
function check_polylang_rewrite_status_on_404( WP_Query $query ) : void {
    if ( ! is_polylang_active() ) {
        // Take no action if Polylang is not active at all.
        return;
    }

    global $wp_rewrite;

    if ( ! isset( $wp_rewrite ) || empty( $wp_rewrite->rules ) ) {
        // Safeguard against a missing-global state which should not be reachable.
        return;
    }

    foreach ( $wp_rewrite->rules as $pattern => $handler ) {
        if ( strpos( $pattern, '|fr|' ) !== false && strpos( $handler, 'lang=' ) !== false ) {
            // This sure looks like a Polylang rewrite. Things look OK.
            return;
        }
    }

    // When was the last time we flushed rewrites?
    $last_rewrite_flush = wp_cache_get( CACHE_KEY, CACHE_GROUP );
    if ( is_int( $last_rewrite_flush ) && time() - $last_rewrite_flush < 30 ) {
        // Only try flushing once every 30s to avoid excessive option thrashing.
        return;
    }

    // Delete the option. This will prompt WordPress to reconstruct it on the next page view.
    // Since Polylang Pro is active, the regenerated rules SHOULD include Polylang's.
    error_log( 'Polylang rewrites missing on 404. Deleting rewrite_rules.' );
    delete_option( 'rewrite_rules' );
    wp_cache_set( CACHE_KEY, time(), CACHE_GROUP, 30 );
}
