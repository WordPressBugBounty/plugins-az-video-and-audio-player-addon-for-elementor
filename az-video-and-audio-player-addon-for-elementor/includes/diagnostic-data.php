<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Shared by every Supabase sender below. The anon key is public by design (it
// ships in the plugin zip); the table's insert-only RLS policy is what protects it.
if ( ! defined( 'LEANPL_SUPABASE_URL' ) ) {
	define( 'LEANPL_SUPABASE_URL', 'https://qqcwdsrfytuefaoitiqn.supabase.co/rest/v1/' );
}
if ( ! defined( 'LEANPL_SUPABASE_KEY' ) ) {
	define( 'LEANPL_SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFxY3dkc3JmeXR1ZWZhb2l0aXFuIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjE5ODAxNDAsImV4cCI6MjA3NzU1NjE0MH0.aiJyKVXWdhnsnV2oHAX0940_TZ6YTd5d3P_w6GXV0m4' );
}

// Real opt-in connect: send the same fields Freemius collected to Supabase.
leanpl_fs()->add_action( 'after_account_connection', function ( $fs_user, $fs_install ) {
	leanpl_send_diagnostic_data( array(
		'site_uid'       => md5( 'fs_install_' . $fs_install->id ),
		'site_url'       => $fs_install->url,
		'site_title'     => $fs_install->title,
		'plugin_version' => $fs_install->version,
		'wp_version'     => $fs_install->platform_version,
		'php_version'    => $fs_install->programming_language_version,
		'language'       => $fs_install->language,
		'first_name'     => $fs_user->first,
		'last_name'      => $fs_user->last,
	) );
}, 10, 2 );

add_action( 'admin_init', function () {
	static $notified = false;
	if ( $notified || ! function_exists( 'leanpl_fs' ) ) {
		return;
	}

	$already_flagged = get_option( 'leanpl_freemius_skip_seen' );
	if ( leanpl_fs()->is_anonymous() && ! $already_flagged ) {
		update_option( 'leanpl_freemius_skip_seen', 1 );
		$notified = true;
	}
}, 20 );

leanpl_fs()->add_action( 'before_admin_menu_init', function () {
	if ( empty( $_GET['pending_activation'] ) || ! fs_request_is_action( leanpl_fs()->get_unique_affix() . '_activate_new' ) ) {
		return;
	}
	$wp_user = wp_get_current_user();
	leanpl_send_diagnostic_data( array(
		'site_uid'       => md5( 'site_' . home_url() ),
		'site_url'       => home_url(),
		'site_title'     => get_bloginfo( 'name' ),
		'wp_version'     => get_bloginfo( 'version' ),
		'php_version'    => phpversion(),
		'language'       => get_locale(),
		'first_name'     => $wp_user->first_name,
		'last_name'      => $wp_user->last_name,
	) );
} );

if ( ! function_exists( 'leanpl_send_diagnostic_data' ) ) {
	function leanpl_send_diagnostic_data( array $fields ) {
		$fields = wp_parse_args( $fields, array(
			'plugin_slug'     => 'leanpl',
			'plugin_version'  => defined( 'LEANPL_VERSION' ) ? LEANPL_VERSION : null,
			'additional_data' => array(), // NOT NULL column, empty array = "nothing collected"
		) );

		leanpl_supabase_post( 'diagnostic_data', $fields );
	}
}

if ( ! function_exists( 'leanpl_supabase_post' ) ) {
	/**
	 * Fire-and-forget insert into a Supabase table.
	 *
	 * @param string $table  Table name appended to the REST base URL.
	 * @param array  $fields Row to insert.
	 */
	function leanpl_supabase_post( $table, array $fields ) {
		wp_remote_post( LEANPL_SUPABASE_URL . $table, array(
			'headers'  => array(
				'Content-Type'  => 'application/json',
				'apikey'        => LEANPL_SUPABASE_KEY,
				'Authorization' => 'Bearer ' . LEANPL_SUPABASE_KEY,
				'Prefer'        => 'return=minimal',
			),
			'body'     => wp_json_encode( $fields ),
			'timeout'  => 5,
			'blocking' => false, // fire-and-forget, don't slow down the redirect
		) );
	}
}

if ( ! function_exists( 'leanpl_get_deactivation_reason' ) ) {
	/**
	 * Slug and English label for a Freemius deactivation reason ID.
	 *
	 * Labels are transcribed from Freemius::_get_uninstall_reasons(). English only,
	 * since these rows are analytics, not UI. IDs are stable constants in
	 * class-freemius.php. Slugs for ids 1, 2, 12 and 15 match the keys the previous
	 * in-house collector used, so old and new rows group together.
	 *
	 * @param int $reason_id Freemius reason ID (1-15).
	 * @return array {key, text}; key is 'unknown' for an ID a future SDK adds.
	 */
	function leanpl_get_deactivation_reason( $reason_id ) {
		$reasons = array(
			1  => array( 'no-longer-needed', 'I no longer need the plugin' ),
			2  => array( 'switching-plugin', 'I found a better plugin' ),
			3  => array( 'short-period', 'I only needed the plugin for a short period' ),
			4  => array( 'broke-my-site', 'The plugin broke my site' ),
			5  => array( 'stopped-working', 'The plugin suddenly stopped working' ),
			6  => array( 'cant-pay-anymore', "I can't pay for it anymore" ),
			7  => array( 'custom-reason', 'Other' ),
			8  => array( 'didnt-work', "The plugin didn't work" ),
			9  => array( 'privacy-concern', "I don't like to share my information with you" ),
			10 => array( 'couldnt-make-it-work', "I couldn't understand how to make it work" ),
			11 => array( 'missing-feature', "The plugin is great, but I need specific feature that you don't support" ),
			12 => array( 'not-working', 'The plugin is not working' ),
			13 => array( 'not-what-i-wanted', "It's not what I was looking for" ),
			14 => array( 'didnt-work-as-expected', "The plugin didn't work as expected" ),
			15 => array( 'temporary', "It's a temporary deactivation - I'm troubleshooting an issue" ),
		);

		if ( ! isset( $reasons[ $reason_id ] ) ) {
			return array( 'key' => 'unknown', 'text' => '' );
		}

		return array(
			'key'  => $reasons[ $reason_id ][0],
			'text' => $reasons[ $reason_id ][1],
		);
	}
}

if ( ! function_exists( 'leanpl_send_deactivation_feedback' ) ) {
	/**
	 * Send a deactivation reason to Supabase.
	 *
	 * Freemius only stores the reason locally and ships it on delete, so most
	 * feedback never arrives. This sends it the moment the user submits.
	 *
	 * @param array $fields Row to insert.
	 */
	function leanpl_send_deactivation_feedback( array $fields ) {
		$fields = wp_parse_args( $fields, array(
			'plugin_slug'    => 'leanpl',
			'plugin_version' => defined( 'LEANPL_VERSION' ) ? LEANPL_VERSION : null,
			'wp_version'     => get_bloginfo( 'version' ),
			'php_version'    => phpversion(),
		) );

		leanpl_supabase_post( 'deactivation_feedback', $fields );
	}
}
