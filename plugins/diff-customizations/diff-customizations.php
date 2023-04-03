<?php
/*
Plugin Name: Diff Customizations
Plugin URI: https://diff.wikimedia.org
Description: Adds customizations seperate from theme.
Version: 0.5
Author: Chris Koerner
Author URI: https://meta.wikimedia.org/wiki/Community_Relations
*/

//limit access to Jetpack to admins

add_action( 'admin_menu', 'diff_no_jetpack_menu_non_admins', 999 );

function diff_no_jetpack_menu_non_admins() {
	if (
		class_exists( 'Jetpack' )
		&& ! current_user_can( 'manage_options' )
	) {
		remove_menu_page( 'jetpack' );
	}
}


//limit access to Tools and Comments capabilities to admins
//These menu items are useless given there are no tools to configure for other roles like Contributors

add_action('admin_menu', 'diff_remove_tools_comments_pages');

function diff_remove_tools_comments_pages() {
	if (!current_user_can ('manage_options')
	){
		remove_menu_page( 'edit-comments.php' );
		remove_menu_page('tools.php');
    }
}


//remove commments from adimin bar

add_action( 'wp_before_admin_bar_render', 'diff_remove_admin_menus' );

function diff_remove_admin_menus() {
	if (!current_user_can ('manage_options')
	){
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
    }
}


//Let's remove some unnecessary widgets from the WordPress dashboard

function diff_disable_dashboard_widgets()
{
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // Remove Quick Draft
    remove_meta_box('dashboard_primary', 'dashboard', 'core'); // Remove WordPress Events and News
    remove_meta_box('notepad_widget', 'dashboard', 'core'); // Remove Notepad widget
}
add_action('admin_menu', 'diff_disable_dashboard_widgets');


//A little notice to contributors when they login

function diff_contributor_admin_notice(){
    global $pagenow;
    if ( $pagenow == 'index.php' ) {
    $user = wp_get_current_user();
    if ( in_array( 'contributor', (array) $user->roles ) ) {
    echo '<div class="notice notice-info is-dismissible">
          <p>Welcome to Diff. Please review the <a href="https://diff.wikimedia.org/editorial-guidelines/">editorial guidelines</a>. Click on <a href="post-new.php">+ New</a> to start writing.</p>
         </div>';
    }
}
}
add_action('admin_notices', 'diff_contributor_admin_notice');

//one for the main editor
function block_notice_enqueue() {
    wp_enqueue_script(
        'block_notice-script',
        plugins_url( 'block-notice.js', __FILE__ )
    );
}
add_action( 'enqueue_block_editor_assets', 'block_notice_enqueue' );

//add editorial calendar to toolbar
add_action( 'admin_bar_menu', 'diff_calendar_toolbar', 999 );

function diff_calendar_toolbar( $wp_admin_bar ) {
        $args = array(
                'id'    => 'calendar',
                'title' => 'Editorial Calendar',
                'href'  => admin_url() . 'admin.php?page=pp-calendar',
        );
        $wp_admin_bar->add_node( $args );
}

//disable comments on media attachments
function diff_filter_media_comment_status( $open, $post_id ) {
    $post = get_post( $post_id );
    if( $post->post_type == 'attachment' ) {
        return false;
    }
    return $open;
}
add_filter( 'comments_open', 'diff_filter_media_comment_status', 10 , 2 );

//disble Jetpack module for WordPress.com login

add_filter( 'jetpack_get_available_modules', 'diff_disable_jetpack_sso' );
function diff_disable_jetpack_sso( $modules ) {
    if( isset( $modules['sso'] ) ) {
        unset( $modules['sso'] );
    }
    return $modules;
}

add_filter( 'manage_edit-page_columns', 'diff_remove_language_columns', 110 );
add_filter( 'manage_edit-post_columns', 'diff_remove_language_columns', 110 );
add_filter( 'manage_edit-category_columns', 'diff_remove_language_columns', 110 );
add_filter( 'manage_edit-post_tag_columns', 'diff_remove_language_columns', 110 );
add_filter( 'manage_edit-tribe_events_columns', 'diff_remove_language_columns', 110 );
add_filter( 'manage_edit-tribe_events_cat_columns', 'diff_remove_language_columns', 110 );
add_filter( 'manage_edit-tribe_venue_columns', 'diff_remove_language_columns', 110 );
add_filter( 'manage_edit-tribe_organizer_columns', 'diff_remove_language_columns', 110 );

/**
 * Remove the Polylang plugin admin language columns.
 *
 * We are using a very low priority to make sure this filter runs
 * after the Polylang plugin is done loading its code.
 *
 * To remove columns from custom post types, you can add additional filters in this format:
 * 'manage_edit-{$post_type}_columns'
 * replacing {$post_type} with the name of the custom post type.
 *
 * @param string[] $columns Array of column header labels keyed by column ID.
 *
 * @return string[] $columns Modified array of column header labels.
 */
function diff_remove_language_columns( $columns ) {
	// Remove any column with the $columns['language_*'] key pattern.
	foreach ( $columns as $language => $column ) {
		if ( preg_match( '/language_\w+/', $language ) ) {
			unset( $columns[ $language ] );
		}
	}

	return $columns;
}

//disable full screen editing (it is confusing people)

function diff_disable_editor_fullscreen_by_default() {
	$script = "window.onload = function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } }";
	wp_add_inline_script( 'wp-blocks', $script );
}
add_action( 'enqueue_block_editor_assets', 'diff_disable_editor_fullscreen_by_default' );


//Custom CSS for WordPress Dashboard
function diff_admin_stylesheet() {
  wp_enqueue_style('diff_admin-styles', get_stylesheet_directory_uri().'/admin.css');
}
add_action('admin_enqueue_scripts', 'diff_admin_stylesheet');


//allow contributor role to add string translations in Polylang

add_action( 'admin_menu', 'diff_contributor_string_translation');

function diff_contributor_string_translation() {
    if ( ! current_user_can( 'manage_options' ) && function_exists( 'PLL' ) ) {
        add_menu_page( __( 'Strings translations', 'polylang' ), __( 'Languages', 'polylang' ), 'edit_posts', 'mlang_strings', array( PLL(), 'languages_page' ), 'dashicons-translation' );
    }
}

/* Verify domain for Facebook */
add_action('wp_head', 'diff_fb_verify');
function diff_fb_verify(){
?>
<meta name ="facebook-domain-verification" content="yk2blq9pquiyqqsigh6bsjsxyck9g0" />
<?php
};

// filter domains so Jetpack Photon works
add_filter( 'jetpack_photon_skip_for_url', 'jetpack_photon_unbanned_domains', 10, 2 );

function jetpack_photon_unbanned_domains( $skip, $image_url ) {
    $unbanned_host_patterns = array(
        '/^(techblog|diff|policy)\.wikimedia\.org$/',
    );
    $host = wp_parse_url( $image_url, PHP_URL_HOST );
    foreach ( $unbanned_host_patterns as $unbanned_host_pattern ) {
        if ( 1 === preg_match( $unbanned_host_pattern, $host ) ) {
            return false;
        }
    }
    return $skip;
}

// Disable JS concatenation for admin users
add_filter( 'js_do_concat', 'diff_js_do_concat');
function diff_js_do_concat( $do_concat) {
if( is_admin() ) {
	return false;
	}
	return $do_concat;
	};

//Add fallback image for related posts feature

function diff_custom_image( $media, $post_id, $args ) {
if ( $media ) {
	return $media;
} else {
	$permalink = get_permalink( $post_id );
	$url = apply_filters( 'jetpack_photon_url', 'https://diff.wikimedia.org/wp-content/uploads/2020/12/related-post-placeholder.jpg' );

	return array( array(
		'type'  => 'image',
		'from'  => 'custom_fallback',
		'src'   => esc_url( $url ),
		'href'  => $permalink,
	) );
}
}
add_filter( 'jetpack_images_get_images', 'diff_custom_image', 10, 3 );

//Increase export of calendar events to 100

add_filter( 'tribe_ical_feed_posts_per_page', function() { return 100; } );


/**
 * Hijack dispatching of POST and PUT requests for tribe_events objects to
 * strip off certain meta if the current user is not able to publish posts.
 *
 * There is a bug in the REST API where even an unchanged value for meta
 * keys with permissions callbacks which a Contributor cannot pass will
 * cause a permissions error for the entire request when saved in the editor.
 *
 * This is a hack, and should be removed if we ever determine the bug is fixed.
 *
 * @param mixed           $dispatch_result Dispatch result, will be used if not empty.
 * @param WP_REST_Request $request         Request used to generate the response.
 * @param string          $route           Route matched for the request.
 * @param array           $handler         Route handler used for the request.
 * @return mixed Potentially a response object, or else null.
 */
function diff_skip_some_meta_when_saving_events_as_contributor( $dispatch_result, $request, $route, $handler ) {
    if ( $request->get_method() === 'GET' ) {
        // Not trying to update anything, permissions are not in play.
        return $dispatch_result;
    }

    if ( ! is_callable( $handler['callback'] ) ) {
        // Don't continue if we can't invoke the request in the same manner that
        // the core WP_REST_Server#respond_to_request() would.
        return $dispatch_result;
    }

    if ( ! str_contains( $route, 'tribe_events' ) ) {
        // At present we only observe this issue on event posts.
        return $dispatch_result;
    }

    if ( current_user_can( 'publish_posts' ) ) {
        // Current user can probably pass any required meta permissions checks.
        return $dispatch_result;
    }

    /**
     * Filters the meta keys which should be ignored when saving an event
     * post object while logged in as a Contributor. Permits meta to be
     * skipped while saving which would otherwise potentially cause a
     * permissions error.
     *
     * @param string[] $meta_keys Meta keys which cannot be saved as a Contributor.
     */
    $prohibited_meta_keys = apply_filters( 'diff/contributor_ignored_event_meta', [] );

    $includes_prohibited_meta = false;
    $updated_meta = $request->get_param( 'meta' );
    foreach ( $prohibited_meta_keys as $meta_key ) {
        if ( isset( $updated_meta[ $meta_key ] ) ) {
            unset( $updated_meta[ $meta_key ] );
            $includes_prohibited_meta = true;
        }
    }

    if ( $includes_prohibited_meta ) {
        // One or more of the keys in the meta array are known to cause a
        // permissions error on save. Invoke the request dispatcher callback
        // manually using our adapted version of the $request which has had
        // those meta values removed in the loop above, and return that
        // version of the response to short-circuit WP's own logic.
        $request->set_param( 'meta', $updated_meta );
        return call_user_func( $handler['callback'], $request );
    }

    // No issues here.
    return $dispatch_result;
}
add_filter( 'rest_dispatch_request', 'diff_skip_some_meta_when_saving_events_as_contributor', 10, 4 );

/**
 * Register the meta keys which should be skipped when saving an event as a contributor.
 *
 * @param string[] $meta_keys Meta keys which cannot be saved as a Contributor.
 * @return string[] Updated keys array.
 */
function diff_set_contributor_ignored_event_meta( $keys ) {
    $keys[] = 'jetpack_post_was_ever_published';
    return $keys;
}
add_filter( 'diff/contributor_ignored_event_meta', 'diff_set_contributor_ignored_event_meta' );

/**
 * Hide certain admin notices from non-admin users, or on local.
 *
 * It is not helpful to tell ordinary contributors to take actions which are
 * restricted to users capable of administering plugins.
 */
function diff_hide_certain_plugin_admin_notices() {
    // Hide these notices even for admins if local, they tend to be meaningless.
    $is_local = false; //wp_get_environment_type() === 'local';
    $is_admin_user = current_user_can( 'edit_plugins' ) || current_user_can( 'administrator' );
    if ( $is_admin_user && ! $is_local ) {
        return;
    }

    $suppressed_selectors = [
        '.notice#blogpublic-notice', // Only relevant for local / development environments.
        '.notice.is-dismissible[class*=wpdiscuz-]', // WPDiscuz activation nags.
        '.notice.is-dismissible[class*=tribe-]', // Events Calendar nags.
    ];

    wp_register_style( 'diff-suppress-plugin-notices', false );
    wp_add_inline_style(
        'diff-suppress-plugin-notices',
        join( ',', $suppressed_selectors ) . ' { display: none; }'
    );
    wp_enqueue_style( 'diff-suppress-plugin-notices' );
}
add_action( 'admin_enqueue_scripts', 'diff_hide_certain_plugin_admin_notices' );
