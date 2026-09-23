<?php
/**
 * LBANA block theme.
 *
 * Page layouts ship as locked patterns (patterns/*.php). Officers edit text
 * in the WordPress editor; structure, colour and type stay in this theme.
 *
 * Root server : https://bmlt.wszf.org/main_server/
 * Service body: 35
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'LBANA_VERSION', '1.0.0' );
define( 'LBANA_BMLT_ROOT', 'https://bmlt.wszf.org/main_server/' );
define( 'LBANA_SERVICE_BODY', '35' );
define( 'LBANA_HELPLINE', '877-233-4287' );

// Members-only gating. Off for the test: the portal is an open link.
if ( ! defined( 'LBANA_GATE_MEMBERS' ) ) {
	define( 'LBANA_GATE_MEMBERS', false );
}

require_once get_theme_file_path( 'inc/bmlt-and-accounts.php' );
require_once get_theme_file_path( 'inc/area.php' );
require_once get_theme_file_path( 'inc/site.php' );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

function lbana_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600;6..72,700&display=swap';
}

function lbana_enqueue() {
	wp_enqueue_style( 'lbana-fonts', lbana_fonts_url(), array(), null );
	wp_enqueue_style( 'lbana', get_stylesheet_uri(), array( 'lbana-fonts' ), LBANA_VERSION );
	wp_enqueue_style( 'lbana-registration', get_theme_file_uri( 'assets/registration.css' ), array( 'lbana' ), LBANA_VERSION );
	wp_enqueue_script( 'lbana-nav', get_theme_file_uri( 'assets/nav.js' ), array(), LBANA_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'lbana_enqueue' );

function lbana_setup() {
	add_theme_support( 'editor-styles' );
	add_editor_style( array( lbana_fonts_url(), 'style.css' ) );
	add_theme_support( 'responsive-embeds' );
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'lbana_setup' );

/* -------------------------------------------------------------------------
 * Keep the editor small. Officers edit words, not layouts.
 * ---------------------------------------------------------------------- */

// No block directory, no remote pattern library.
remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
add_filter( 'should_load_remote_block_patterns', '__return_false' );

function lbana_pattern_category() {
	register_block_pattern_category( 'lbana', array( 'label' => 'LBANA pages' ) );
}
add_action( 'init', 'lbana_pattern_category' );

/* -------------------------------------------------------------------------
 * Member role and optional gating
 * ---------------------------------------------------------------------- */

function lbana_register_member_role() {
	add_role( 'lbana_member', 'Member', array(
		'read'               => true,
		'lbana_view_members' => true,
	) );
	foreach ( array( 'administrator', 'editor' ) as $role_name ) {
		$role = get_role( $role_name );
		if ( $role ) { $role->add_cap( 'lbana_view_members' ); }
	}
}
add_action( 'after_switch_theme', 'lbana_register_member_role' );

function lbana_is_members_only( $post_id ) {
	return (bool) get_post_meta( $post_id, '_lbana_members_only', true );
}

function lbana_guard_members_area() {
	if ( ! LBANA_GATE_MEMBERS || is_admin() || ! is_singular() ) { return; }
	$post_id = get_queried_object_id();
	if ( ! lbana_is_members_only( $post_id ) ) { return; }
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( get_permalink( $post_id ) ) );
		exit;
	}
}
add_action( 'template_redirect', 'lbana_guard_members_area' );

function lbana_members_only_meta() {
	register_post_meta( 'page', '_lbana_members_only', array(
		'type'          => 'boolean',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_pages' ); },
	) );
}
add_action( 'init', 'lbana_members_only_meta' );

/* -------------------------------------------------------------------------
 * Housekeeping
 * ---------------------------------------------------------------------- */

// Staging never gets indexed, whatever the Reading setting says.
function lbana_noindex_staging( $robots ) {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( $host && 0 === strpos( $host, 'test.' ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'lbana_noindex_staging' );

// Comments are off everywhere.
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );
