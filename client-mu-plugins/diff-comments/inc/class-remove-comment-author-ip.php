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
	 * Remove recorded comment author IP address from the database.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run=<dry-run>]
	 * : Perform a test run if true, make changes to the database if false.
	 * ---
	 * default: true
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    wp diff-comments-remove-ip --dry-run=false
	 *
	 * @param array $args List of arguments passed to the commands.
	 * @param array $assoc_args Arguments passed to the command parsed into key/value pairs.
	 */
	public function __invoke( $args, $assoc_args ) {
		$count = 0;

		// If --dry-run is not set, then it will default to true.
		// Must set --dry-run explicitly to false to run this command.
		$dry_run = filter_var( $assoc_args['dry-run'] ?? true, FILTER_VALIDATE_BOOLEAN );

		if ( ! $dry_run ) {
			WP_CLI::warning( 'Dry run is disabled, data in the database will be modified.' );
		}

		// Get all comments with recorded IP.
		$comments = $this->get_comments_with_ip();

		if ( count( $comments ) > 1 ) {
			$label    = $dry_run ? 'Querying Comments' : 'Deleting ' . count( $comments ) . ' Recorded IP Address';
			$progress = \WP_CLI\Utils\make_progress_bar( $label, count( $comments ) );

			foreach ( $comments as $comment ) {
				$update_comment = [
					'comment_ID'        => $comment,
					'comment_author_IP' => '',
				];

				if ( ! $dry_run ) {
					wp_update_comment( $update_comment );
				}

				$count++;
				$progress->tick();
			}

			$progress->finish();

			if ( $dry_run === true ) {
				WP_CLI::success( sprintf( '%d comments would have had the author IP removed if this was not a dry run.', $count ) );
			} else {
				WP_CLI::success( sprintf( '%d comments had the author IP removed.', $count ) );
			}
		} else {
			WP_CLI::line( 'No comments found.' );
		}
	}

	/**
	 * Get all the comments with a recorded IP address.
	 */
	private function get_comments_with_ip() {
		// Get all comments.
		$args     = [
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
