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
const META_KEY = 'rewrite_monitor_log_item_details';

// Adjust PHPCS for our needs.
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary under the circumstances.
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Necessary under the circumstances.

function bootstrap() : void {
    // Infrastructure to persistently capture monitoring messages.
    add_action( 'init', __NAMESPACE__ . '\\register_monitoring_log_post_type' );
    add_action( 'rest_api_init', __NAMESPACE__ . '\\register_rest_fields' );
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

function register_rest_fields() : void {
    register_rest_field(
        POST_TYPE,
        'log_item_fields',
        [
            'get_callback' => function( array $log_post ) {
                $fields = get_post_meta( $log_post['id'], META_KEY, true );
                return ! empty( $fields ) ? $fields : [];
            },
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
    return array_merge(
        [
            'id'       => $item['id'],
            'title'    => $item['title']['rendered'],
            'date'     => $item['date'],
            'date_gmt' => $item['date_gmt'],
            // Restore the appearance of the trace in the raw content.
            'content'  => preg_replace(
                '/-&gt;/',
                '->',
                get_post( $item['id'] )->post_content ?? '(invalid ID)'
            ),
            'type'     => $item['type'],
        ],
        $item['log_item_fields']
    );
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
    $log_item_id = wp_insert_post(
        [
            'post_title'   => sprintf( '[%s] %s', $title, get_unique_request_id() ),
            'post_type'    => POST_TYPE,
            'post_content' => $message,
            'post_status'  => 'publish',
        ]
    );
    $status_array = [
        'action'            => $title,
        'request_id'        => get_unique_request_id(),
        'request_method'    => sanitize_text_field( $_SERVER['REQUEST_METHOD'] ?? '?' ),
        'request_uri'       => sanitize_text_field( $_SERVER['REQUEST_URI'] ?? '/unknown/' ),
        'global_rule_count' => get_wp_rewrites_global_status(),
        'option_rule_count' => get_option_status(),
        'is_admin'          => is_admin() ? 'true' : 'false',
        'is_rest'           => defined( 'REST_REQUEST ') && REST_REQUEST ? 'true' : 'false',
        'is_cli'            => defined( 'WP_CLI' ) && WP_CLI ? 'true' : 'false',
        'user'              => is_user_logged_in() ? get_current_user_id() : ( ( defined( 'WP_CLI' ) && WP_CLI ) ? 'CLI user' : 'anonymous' ),
        'plugin_status'     => is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
    ];
    if ( ! empty( $log_item_id ) ) {
        add_post_meta( $log_item_id, META_KEY, $status_array );
    }
    if ( $output_to_error_log ) {
        error_log( $message . "\n" . wp_json_encode( $status_array ) );
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
 * Return out the number of rewrites currently present in the global.
 */
function get_wp_rewrites_global_status() : int|string {
    global $wp_rewrite;
    return is_countable( $wp_rewrite->rules ?? null )
        ? count( $wp_rewrite->rules ) . ' rules in $wp_rewrites global.'
        : '$wp_rewrites->rules is not countable.';
}

/**
 * Return the current count of rewrites within the stored option.
 */
function get_option_status() : int|string {
    $rewrites = get_option( 'rewrite_rules' );
    return ( is_countable( $rewrites ?? null )
        ? count( $rewrites ) . ' rewrites in rewrite_rules option.'
        : 'rewrite_rules option is not countable.' );
}

/**
 * Get the current count of rewrites within the stored option, as an integer.
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
            "%s - %s CHANGING in pre_update_option_rewrite_rules\n%s old rules, %s new rules. Current user: %d\n%s",
            get_request_details(),
            $option,
            is_countable( $old_value ) ? count( $old_value ) : ( empty( $old_value ) ? 0 : '(' . print_r( $old_value, true ) . ')' ),
            is_countable( $value ) ? count( $value ) : ( empty( $value ) ? 0 : '(' . print_r( $value, true ) . ')' ),
            is_user_logged_in() ? get_current_user_id() : 0,
            generate_call_trace()
        )
    );

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
                "%s - rewrite_rules are going to be DELETED, currently there are %d. Polylang is %s.\n%s",
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
            "%s - rewrite_rules DELETED.\n%s",
            get_request_details(),
            generate_call_trace()
        )
    );
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
                "%s - rewrite_rules are going to be ADDED, currently there are %d, %d incoming.\n%s",
                get_request_details(),
                get_rewrite_count(),
                is_countable( $value ) ? count( $value ) : '(unknown)',
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
            "%s - rewrite_rules were added, now there are %d.\n%s",
            get_request_details(),
            get_rewrite_count(),
            generate_call_trace()
        )
    );
}
