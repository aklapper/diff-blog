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

add_action('admin_init', 'diff_remove_tools_comments_pages');

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
          <p>Welcome to Diff. Please review the <a href="#">editorial guidelines</a>. Click on <a href="post-new.php">+ New</a> to start writing.</p>
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

add_filter( 'hidden_columns', 'diff_hide_language_columns' );
/**
 * Hide the Polylang plugin admin language columns.
 *
 * @param string[] $hidden_columns Array of hidden columns.
 *
 * @return string[] $hidden_columns Modified array of hidden columns.
 */
function diff_hide_language_columns( $hidden_columns ) {
	$hidden_columns = [
		'language_ak',
		'language_ar',
		'language_arg',
		'language_as',
		'language_ast',
		'language_ba',
		'language_bg',
		'language_bn',
		'language_ca',
		'language_cs',
		'language_da',
		'language_de',
		'language_el',
		'language_en',
		'language_eo',
		'language_es',
		'language_et',
		'language_fa',
		'language_fi',
		'language_fr',
		'language_he',
		'language_hi',
		'language_hu',
		'language_hy',
		'language_id',
		'language_it',
		'language_ja',
		'language_ka',
		'language_kn',
		'language_ko',
		'language_lad',
		'language_mai',
		'language_mi',
		'language_mk',
		'language_mr',
		'language_ms',
		'language_nb',
		'language_ne',
		'language_nl',
		'language_nn',
		'language_or',
		'language_pa',
		'language_pl',
		'language_pt',
		'language_pt-br',
		'language_ro',
		'language_ru',
		'language_sq',
		'language_sr',
		'language_sv',
		'language_ta',
		'language_th',
		'language_tr',
		'language_tt',
		'language_uk',
		'language_ur',
		'language_vi',
		'language_yua',
		'language_zh',
		'language_zh-hans',
		'language_zh-hant',
	];

	return $hidden_columns;
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
