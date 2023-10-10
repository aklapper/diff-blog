<?php
/**
 * Plugin Name: Rewrite Debugger
 * Plugin Author: Human Made
 * Plugin Description: Log diagnostics information when rewrites change.
 *
 * Plugin created 2023-09 while troubleshooting Polylang rewrites issue: All polylang rewrites
 * periodically disappear from the site, causing all non-English content to become inaccessible.
 * @see https://support.wpvip.com/hc/en-us/requests/172999
 * @see https://hmn.slack.com/archives/C011TP2PSHJ/p1693405818743349
 */

namespace Rewrite_Monitor;

use Exception;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Server;

const POST_TYPE = 'rewrite_monitor_log';

// Adjust PHPCS for our needs.
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary under the circumstances.
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Necessary under the circumstances.

function bootstrap() : void {
    // Infrastructure to persistently capture monitoring messages.
    add_action( 'init', __NAMESPACE__ . '\\register_monitoring_log_post_type' );
    add_filter( 'rest_post_dispatch', __NAMESPACE__ . '\\simplify_rewrite_monitoring_log_rest_output', 10, 3 );

    // Monitoring.
    add_filter( 'pre_update_option_rewrite_rules', __NAMESPACE__ . '\\alert_on_change', 10, 3 );
    add_action( 'update_option_rewrite_rules', __NAMESPACE__ . '\\alert_once_changed', 10, 3 );
    add_action( 'delete_option', __NAMESPACE__ . '\\alert_before_delete' );
    add_action( 'delete_option_rewrite_rules', __NAMESPACE__ . '\\alert_after_delete' );
    add_action( 'add_option', __NAMESPACE__ . '\\alert_before_add', 10, 2 );
    add_action( 'add_option_rewrite_rules', __NAMESPACE__ . '\\alert_when_added' );
}
bootstrap();

/**
 * Add a custom post type to be used for logging monitoring messages to the database.
 */
function register_monitoring_log_post_type() : void {
    register_post_type(
        POST_TYPE,
        [
            'label'              => 'Rewrite modification log entry',
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => false,
            'show_in_rest'       => true,
            'rest_base'          => 'rewrite_logs',
            'rest_namespace'     => 'wiki/v1',
            'supports'           => [ 'title', 'editor' ],
        ]
    );
}

/**
 * Take a normal REST response from the rewrite logs endpoint and return a
 * formatted subset of that log item's data.
 *
 * @param array $item Log post data, as array.
 * @return array Streamlined output.
 */
function simplify_rest_log_item( array $item ) : array {
    return [
        'id'       => $item['id'],
        'title'    => $item['title']['rendered'],
        // Restore the appearance of the trace in the raw content.
        'content'  => preg_replace(
            '/-&gt;/',
            '->',
            get_post( $item['id'] )->post_content ?? '(invalid ID)'
        ),
        'date'     => $item['date'],
        'date_gmt' => $item['date_gmt'],
        'type'     => $item['type'],
    ];
}

/**
 * Remove irrelevant fields from log REST endpoint to simplify visual scanning of recent events.
 *
 * @param WP_HTTP_Response $result Outgoing REST response.
 * @return WP_HTTP_Response Filtered response.
 */
function simplify_rewrite_monitoring_log_rest_output( WP_HTTP_Response $result, WP_REST_Server $server, WP_REST_Request $request ) : WP_HTTP_Response {
    if ( strpos( $request->get_route(), '/wiki/v1/rewrite_logs' ) !== 0 ) {
        // Only alters rewrite_monitor_log responses.
        return $result;
    }

    $data = $result->get_data();

    if ( isset( $data['id'] ) ) {
        // Single post.
        return rest_ensure_response( simplify_rest_log_item( $data ) );
    }

    return rest_ensure_response( array_map( __NAMESPACE__ . '\\simplify_rest_log_item', $data ) );
}

/**
 * Add this event into a custom post type in the database.
 *
 * These can be easily queried via the REST API, and deleted en masse via WP-CLI.
 *
 * @param string $title               Identifying title for visual filtering via REST API.
 * @param string $message             Content to log.
 * @param bool   $output_to_error_log Whether to output message to error log. Default true.
 */
function log_to_db( string $title, string $message, bool $output_to_error_log = true ) : void {
    wp_insert_post(
        [
            'post_title'   => sprintf( '%s: %s [%s]', date( 'Y-m-d H:i:s' ), $title, get_unique_request_id() ),
            'post_type'    => POST_TYPE,
            'post_content' => $message,
            'post_status'  => 'publish',
        ]
    );
    if ( $output_to_error_log ) {
        error_log( $message );
    }
}

/**
 * Hash the request information to provide a unique-enough identifier for us to correlate log messages.
 *
 * @return string
 */
function get_unique_request_id() : string {
    return substr(
        hash(
            'md5',
            sprintf(
                '%s%s%s',
                sanitize_text_field( $_SERVER['REQUEST_METHOD'] ?? '?' ),
                sanitize_text_field( $_SERVER['REQUEST_URI'] ?? '/unknown/' ),
                sanitize_text_field( $_SERVER['REQUEST_TIME'] ?? 0 )
            )
        ),
        0,
        8
    );
}

/**
 * Log a string containing the request URL and method.
 *
 * @return string
 */
function get_request_details() : string {
    return sprintf(
        '[%s]: %s %s',
        get_unique_request_id(),
        sanitize_text_field( $_SERVER['REQUEST_METHOD'] ?? '?' ),
        sanitize_text_field( $_SERVER['REQUEST_URI'] ?? '/unknown/' )
    );
}

/**
 * Utility method to get a loggable call trace, from Stack Overflow.
 *
 * @return string
 */
function generate_call_trace() : string {
    $e = new Exception();
    $trace = explode( "\n", $e->getTraceAsString() );
    // reverse array to make steps line up chronologically.
    $trace = array_reverse( $trace );
    array_shift( $trace ); // remove {main}.
    array_pop( $trace ); // remove call to this method.
    $length = count( $trace );
    $result = array();

    for ($i = 0; $i < $length; $i++) {
        // replace '#someNum' with '$i)', set the right ordering.
        $result[] = ( $i + 1 )  . ')' . substr( $trace[$i], strpos( $trace[$i], ' ' ) );
    }

    return str_replace( '\\', '\\\\', implode( "\n ", $result ) );
}

/**
 * Log out the number of rewrites currently present in the global.
 */
function log_rewrite_global_count() : void {
    global $wp_rewrite;
    log_to_db(
        'Rewrite status',
        sprintf(
            "%s - \$wp_rewrite %s defined during %s. %s",
            get_request_details(),
            isset( $wp_rewrite ) ? 'is' : 'is not',
            current_action(),
            isset( $wp_rewrite ) ? sprintf(
                ' ->extra_top: %s; ->extra: %s; ->rules: %s',
                is_countable( $wp_rewrite->extra_rules_top ?? null ) ? count( $wp_rewrite->extra_rules_top ) : 'n/a',
                is_countable( $wp_rewrite->extra_rules ?? null ) ? count( $wp_rewrite->extra_rules ) : 'n/a',
                is_countable( $wp_rewrite->rules ?? null ) ? count( $wp_rewrite->rules ) : 'n/a',
            ) : ''
        )
    );
}

/**
 * Log out the number of rewrites currently present in the option.
 */
function log_rewrite_option_count() : void {
    $rewrites = get_option( 'rewrite_rules', null );
    log_to_db(
        '$wp_rewrite status',
        sprintf(
            "%s - rewrite_rules option %s defined during %s. %s",
            get_request_details(),
            isset( $rewrites ) ? 'is' : 'is not',
            current_action(),
            isset( $rewrites )
                ? ( is_countable( $rewrites ) ? count( $rewrites ) . ' rewrites in option.' : '(not countable).' )
                : ''
        )
    );
}

/**
 * Check the current count of rewrites within the stored option.
 */
function get_rewrite_count() : int {
    $rewrites = get_option( 'rewrite_rules', [] );
    if ( is_countable( $rewrites ) ) {
        return count( $rewrites );
    }
    return 0;
}

/**
 * Alert (with backtrace) when the rewrites are going to be updated.
 *
 * @param mixed  $value     New value.
 * @param mixed  $old_value Old value.
 * @param string $option    Name of option being updated.
 */
function alert_on_change( $value, $old_value, $option ) {
    log_to_db(
        'Pre-Update',
        sprintf(
            "%s - %s CHANGING in pre_update_option_rewrite_rules\n%s old rules, %s new rules. is_admin? %s; is REST? %s;. Polylang is %s. Current user: %d\n%s",
            get_request_details(),
            $option,
            is_countable( $old_value ) ? count( $old_value ) : ( empty( $old_value ) ? 0 : '(' . print_r( $old_value, true ) . ')' ),
            is_countable( $value ) ? count( $value ) : ( empty( $value ) ? 0 : '(' . print_r( $value, true ) . ')' ),
            is_admin() ? 'true' : 'false',
            defined( 'REST_REQUEST ') && REST_REQUEST ? 'true' : 'false',
            is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
            is_user_logged_in() ? get_current_user_id() : 0,
            generate_call_trace()
        )
    );
    log_rewrite_global_count();
    log_rewrite_option_count();

    return $value;
}

/**
 * Alert once the filter value has changed.
 *
 * @param mixed  $old_value Old value.
 * @param mixed  $value     New value.
 * @param string $option    Name of option being updated.
 */
function alert_once_changed( $old_value, $value, $option ) : void {
    log_to_db(
        'Updated',
        sprintf(
            '%s - rewrite_rules changed in %s action. %s old rules, %s new rules.',
            get_request_details(),
            current_action(),
            is_countable( $old_value ) ? count( $old_value ) : ( empty( $old_value ) ? 0 : '(' . print_r( $old_value, true ) . ')' ),
            is_countable( $value ) ? count( $value ) : ( empty( $value ) ? 0 : '(' . print_r( $value, true ) . ')' ),
        )
    );
    log_rewrite_global_count();
    log_rewrite_option_count();
}

/**
 * Alert when detecting the option is about to be deleted.
 *
 * @param string $option Name of option being deleted.
 * @return void
 */
function alert_before_delete( $option ) : void {
    if ( $option === 'rewrite_rules' ) {
        log_to_db(
            'Will Delete',
            sprintf(
                '%s - rewrite_rules are going to be DELETED, currently there are %d. Polylang is %s. %s',
                get_request_details(),
                get_rewrite_count(),
                is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
                generate_call_trace()
            ),
            true
        );
    }
}

/**
 * Alert when detecting an option has been deleted.
 *
 * @param string $option Name of option being deleted.
 * @return void
 */
function alert_after_delete( $option ) : void {
    log_to_db(
        'Deleted',
        sprintf(
            '%s - rewrite_rules DELETED in %s action.',
            get_request_details(),
            current_action(),
        )
    );
    log_rewrite_global_count();
    log_rewrite_option_count();
}

/**
 * Log a note when rewrite rules are going to be updated.
 *
 * @param string $option Name of the option to add.
 * @param mixed  $value  Value of the option.
 * @return void
 */
function alert_before_add( $option, $value ) {
    if ( $option === 'rewrite_rules' ) {
        log_to_db(
            'Will Add',
            sprintf(
                '%s - rewrite_rules are going to be ADDED, currently there are %d, %d incoming. Polylang is %s. %s',
                get_request_details(),
                get_rewrite_count(),
                is_countable( $value ) ? count( $value ) : '(unknown)',
                is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
                generate_call_trace()
            ),
            true
        );
    }
}

/**
 * Alert when detecting the option has been added.
 *
 * @param string $option Name of option being added.
 * @return void
 */
function alert_when_added( $option ) : void {
    log_to_db(
        'Added',
        sprintf(
            '%s - rewrite_rules were added, now there are %d. Polylang is %s. %s',
            get_request_details(),
            get_rewrite_count(),
            is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
            generate_call_trace()
        )
    );
}
