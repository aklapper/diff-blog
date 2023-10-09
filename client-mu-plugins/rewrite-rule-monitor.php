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

// Adjust PHPCS for our needs.
// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only logging to error_log.
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary under the circumstances.
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Necessary under the circumstances.

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
    global $wp_rewrite;
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
            '%s - rewrite_rules changed: %s old rules, %s new rules. is_admin? %s; is REST? %s;. Polylang is %s. Current user: %d',
            get_request_details(),
            is_countable( $old_value ) ? count( $old_value ) : ( empty( $old_value ) ? 0 : '(' . print_r( $old_value, true ) . ')' ),
            is_countable( $value ) ? count( $value ) : ( empty( $value ) ? 0 : '(' . print_r( $value, true ) . ')' ),
            is_admin() ? 'true' : 'false',
            defined( 'REST_REQUEST ') && REST_REQUEST ? 'true' : 'false',
            is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
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
}
add_action( 'update_option_rewrite_rules', __NAMESPACE__ . '\\alert_once_changed', 10, 3 );

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
 * Alert when detecting the option is about to be deleted.
 *
 * @param string $option Name of option being deleted.
 * @return void
 */
function alert_before_delete( $option ) : void {
    if ( $option === 'rewrite_rules' ) {
        error_log( sprintf(
            '%s - rewrite_rules are going to be DELETED, currently there are %d. Polylang is %s. %s',
            get_request_details(),
            get_rewrite_count(),
            is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
            generateCallTrace()
        ) );
    }
}
add_action( 'delete_option', __NAMESPACE__ . '\\alert_before_delete' );

/**
 * Alert when detecting an option has been deleted.
 *
 * @param string $option Name of option being deleted.
 * @return void
 */
function alert_after_delete( $option ) : void {
    error_log( sprintf(
        '%s - rewrite_rules DELETED in %s action.',
        get_request_details(),
        current_action(),
    ) );
}
add_action( 'delete_option_rewrite_rules', __NAMESPACE__ . '\\alert_after_delete' );

/**
 * Log a note when rewrite rules are going to be updated.
 *
 * @param string $option Name of the option to add.
 * @param mixed  $value  Value of the option.
 * @return void
 */
function alert_before_add( $option, $value ) {
    if ( $option === 'rewrite_rules' ) {
        error_log( sprintf(
            '%s - rewrite_rules are going to be ADDED, currently there are %d, %d incoming. Polylang is %s. %s',
            get_request_details(),
            get_rewrite_count(),
            is_countable( $value ) ? count( $value ) : '(unknown)',
            is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
            generateCallTrace()
        ) );
    }
}
add_action( 'add_option', __NAMESPACE__ . '\\alert_before_add', 10, 2 );

/**
 * Alert when detecting the option has been added.
 *
 * @param string $option Name of option being added.
 * @return void
 */
function alert_when_added( $option ) : void {
    error_log( sprintf(
        '%s - rewrite_rules were added, now there are %d. Polylang is %s. %s',
        get_request_details(),
        get_rewrite_count(),
        is_plugin_active( 'polylang-pro/polylang.php' ) ? 'active' : 'inactive',
        generateCallTrace()
    ) );
}
add_action( 'add_option_rewrite_rules', __NAMESPACE__ . '\\alert_when_added' );
