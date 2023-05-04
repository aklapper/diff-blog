<?php
/**
 * Remove comment author IP address from the database.
 *
 * @package WikimediaDiff-Comments
 */

namespace WikimediaDiff\Comments;

use WP_CLI;
use WP_CLI_Command;

/**
 * Class Remove_Comment_Author_IP
 *
 * @package WikimediaDiff\Comments
 */
class Remove_Comment_Author_IP extends WP_CLI_Command {
	/**
	 * Remove comment author IP addresses from the database.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Run the command without committing changes.
	 *
	 * [--ip-string]
	 * : An optional string to replace the IP address in all comments.
	 *
	 * ## EXAMPLES
	 *
	 *    wp comments remove-ip --dry-run
	 *    wp comments remove-ip --ip-string="IP Removed for Privacy"
	 *
	 * @param array $args List of arguments passed to the commands.
	 * @param array $assoc_args Arguments passed to the command parsed into key/value pairs.
	 */
	public function __invoke( $args, $assoc_args ) {
		global $wpdb;

		$dry_run   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run' );
		$ip_string = \WP_CLI\Utils\get_flag_value( $assoc_args, 'ip-string' );

		// Get all comments with recorded IP.
		$comments = $this->get_comments_with_ip();

		if ( count( $comments ) > 1 ) {
			if ( $dry_run ) {
				WP_CLI::success( sprintf( '%d comments will have the author IP removed.', count( $comments ) ) );
			} else {
				WP_CLI::confirm( 'Are you sure you want to delete the IP address from ' . count( $comments ) . ' comments in the database?', $assoc_args );

				$wpdb->query( "UPDATE wp_comments SET comment_author_IP = '$ip_string'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

				WP_CLI::success( sprintf( '%d comments had the author IP removed.', count( $comments ) ) );
			}
		} else {
			WP_CLI::line( 'No comments with recorded IP found.' );
		}
	}

	/**
	 * Get all the comments with a recorded IP address.
	 */
	private function get_comments_with_ip() {
		// Get all comments.
		$args         = [
			'status' => 'all',
			'number' => '',
		];
		$all_comments = get_comments( $args );
		$ip_coments   = [];

		if ( $all_comments ) {
			foreach ( $all_comments as $comment ) {
				if ( ! empty( $comment->comment_author_IP ) ) {
					$ip_coments[] = $comment->comment_ID;
				}
			}
		}

		return $ip_coments;
	}
}
