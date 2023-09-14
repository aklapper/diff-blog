<?php
/**
 * Plugin Name: Rewrite Debugger
 * Plugin Author: Human Made
 * Plugin Description: Log diagnostics information when rewrites change.
 */

namespace Rewrite_Monitor;

use Exception;

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
                $_SERVER['REQUEST_METHOD'] ?? '?',
                $_SERVER['REQUEST_URI'] ?? '/unknown/',
                $_SERVER['REQUEST_TIME'] ?? 0
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
        $_SERVER['REQUEST_METHOD'] ?? '?',
        $_SERVER['REQUEST_URI'] ?? '/unknown/'
    );
}

/**
 * Utility method to get a loggable call trace, from Stack Overflow.
 *
 * @return string
 */
function generateCallTrace() : string {
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

    return implode( ", ", $result );
}

/**
 * Log out the number of rewrites currently present in the global.
 */
function log_rewrite_count() : void {
    error_log(
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
 * Alert (with backtrace) when the rewrites are going to be updated.
 *
 * @param mixed  $value     New value.
 * @param mixed  $old_value Old value.
 * @param string $option    Name of option being updated.
 */
function alert_on_change( $value, $old_value, $option ) {
    error_log(
        sprintf(
            '%s - rewrite_rules CHANGING in pre_update_option_rewrite_rules. %s',
            get_request_details(),
            generateCallTrace()
        )
    );
    error_log(
        sprintf(
            '%s - %s changed: %s old rules, %s new rules. is_admin? %s; is REST? %s; Current user: %d',
            get_request_details(),
            $option,
            is_countable( $old_value ) ? count( $old_value ) : ( empty( $old_value ) ? 0 : '(' . print_r( $old_value, true ) . ')' ),
            is_countable( $value ) ? count( $value ) : ( empty( $value ) ? 0 : '(' . print_r( $value, true ) . ')' ),
            is_admin() ? 'true' : 'false',
            defined( 'REST_REQUEST ') && REST_REQUEST ? 'true' : 'false',
            is_user_logged_in() ? get_current_user_id() : 0
        )
    );

    return $value;
}
add_filter( 'pre_update_option_rewrite_rules', __NAMESPACE__ . '\\alert_on_change', 10, 3 );

/**
 * Alert once the filter value has changed.
 *
 * @param mixed  $old_value Old value.
 * @param mixed  $value     New value.
 * @param string $option    Name of option being updated.
 */
function alert_once_changed( $old_value, $value, $option ) : void {
    error_log( sprintf(
        '%s - rewrite_rules changed in %s action. %s old rules, %s new rules.',
        get_request_details(),
        current_action(),
        is_countable( $old_value ) ? count( $old_value ) : ( empty( $old_value ) ? 0 : '(' . print_r( $old_value, true ) . ')' ),
        is_countable( $value ) ? count( $value ) : ( empty( $value ) ? 0 : '(' . print_r( $value, true ) . ')' ),
    ) );
    // log_rewrite_count();
}
add_action( 'update_option_rewrite_rules', __NAMESPACE__ . '\\alert_once_changed', 10, 3 );
