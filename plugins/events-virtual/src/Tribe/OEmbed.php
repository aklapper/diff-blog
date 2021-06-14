<?php
/**
 * Handles OEmbed links.
 *
 * @since   1.0.0
 *
 * @package Tribe\Events\Virtual
 */

namespace Tribe\Events\Virtual;

use WP_oEmbed;

/**
 * Class OEmbed.
 *
 * @since 1.0.0
 *
 * @package Tribe\Events\Virtual
 */
class OEmbed {

	/**
	 * Adds a wrapper to the oEmbed HTML.
	 *
	 * @param string $html  The returned oEmbed HTML.
	 * @param object $data  A data object result from an oEmbed provider.
	 * @param string $url   The URL of the content to be embedded.
	 *
	 * @return string  The modified oEmbed HTML.
	 */
	public function make_oembed_responsive( $html, $data, $url ) {
		// Verify oembed data (as done in the oEmbed data2html code).
		if ( ! is_object( $data ) || empty( $data->type ) ) {
			return $html;
		}

		if ( empty( $html ) ) {
			return $html;
		}

		/**
		 * Filters whether to make oembed responsive or not.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $make_oembed_responsive  Boolean of if oembed should be made responsive.
		 */
		$make_oembed_responsive = apply_filters( 'tribe_events_virtual_make_oembed_responsive', true );

		if ( empty( $make_oembed_responsive ) ) {
			return $html;
		}

		$attrs = [ 'class' => 'tribe-events-virtual-single-video-embed__wrapper' ];

		// Add padding if height and width data exists.
		if ( ! empty( $data->height ) && ! empty( $data->width ) ) {
			// Calculate aspect ratio.
			$aspect_ratio = $data->height / $data->width;
			$padding      = round( $aspect_ratio * 100, 2 ) . '%';

			$attrs['style'] = "padding-bottom:$padding";
		}

		// convert attributes to key value HTML.
		$attrs = array_map(
			static function ( $key ) use ( $attrs ) {
				if ( is_bool( $attrs[ $key ] ) ) {
					return $attrs[ $key ] ? $key : '';
				}

				return $key . '=\'' . esc_attr( $attrs[ $key ] ) . '\'';
			},
			array_keys( $attrs )
		);

		$attrs_string = implode( ' ', $attrs );

		// Strip width and height from HTML.
		$html = preg_replace( '/(width|height)="\d*"\s/', '', $html );
		$html = "<div $attrs_string>$html</div>";

		/**
		 * Filters the responsive oembed HTML.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html  oEmbed HTML.
		 * @param object $data  data object result from an oEmbed provider.
		 * @param string $url   URL of the content to be embedded.
		 */
		return apply_filters( 'tribe_events_virtual_responsive_oembed_html', $html, $data, $url );
	}

	/**
	 * Tests if a link is embeddable.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The URL to test.
	 * @return boolean
	 */
	public function is_embeddable( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$oembed   = new WP_oEmbed();
		$provider = $oembed->get_provider( $url, [ 'discover' => false ] );

		return false !== $provider;
	}

	/**
	 * Get the error message for an unembeddable link.
	 *
	 * @since 1.0.0
	 * @since 1.0.4 Changed message to include link to the WordPress oEmbed page.
	 *
	 * @param string $url The unembeddable URL.
	 * @return string Failure message.
	 */
	public function get_unembeddable_message( $url ) {
		$message = sprintf(
			/* Translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
			_x(
				'The URL you have entered is not embeddable because the source is not supported by %1$sWordPress oEmbed%2$s.',
				'Tells user that URL cannot be embedded, and links to the WordPress oEmbed page for a list of embeddable sites.',
				'events-virtual'
			),
			'<a href="https://wordpress.org/support/article/embeds/" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);

		/**
		 * Allows filtering of the error message by external objects.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message The error message.
		 * @param string $url     The URL that failed.
		 */
		return apply_filters( 'tribe_events_virtual_get_unembeddable_message', $message, $url );
	}


	/**
	 * Ajax function to test an oembed link for "embeddability".
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_test_oembed_url() {
		if (
			! check_ajax_referer( 'tribe-check-embed', 'nonce' )
			|| empty( $_REQUEST['url'] )
		) {
			wp_send_json_error( null, 401 );
		}

		$url  = filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL );
		$test = $this->is_embeddable( $url );

		if ( false === $test || is_wp_error( $test ) ) {
			$message = $this->get_unembeddable_message( $url );
			wp_send_json_error( $message, 400 );
		}

		wp_send_json_success( $test, 200 );
	}
}
