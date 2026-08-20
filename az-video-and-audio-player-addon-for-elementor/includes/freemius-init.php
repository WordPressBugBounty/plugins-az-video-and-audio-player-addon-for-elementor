<?php
/**
 * Freemius SDK Integration (shared free + pro).
 *
 * Moved out of pro/ so the free wp.org build (which .distignore strips pro/ from)
 * also boots Freemius. This is what lets free users reach checkout/upgrade in wp-admin.
 * is_premium is derived from leanpl_is_pro_active() so one file serves both builds.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'leanpl_fs' ) ) {
	/**
	 * Create/return the global Freemius instance for this plugin.
	 *
	 * @return Freemius
	 */
	function leanpl_fs() {
		global $leanpl_fs;

		if ( ! isset( $leanpl_fs ) ) {
			// Include Freemius SDK (SDK now lives beside this file in includes/).
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			$is_pro = function_exists( 'leanpl_is_pro_active' ) && leanpl_is_pro_active();

			// Freemius config
			$leanpl_fs = fs_dynamic_init( array(
				'id'                  => '22137',
				'slug'                => 'az-video-and-audio-player-addon-for-elementor',  // free wp.org slug
				'premium_slug'        => 'lean-player-pro',
				'type'                => 'plugin',
				'public_key'          => 'pk_4d7befc98ab04b3d4b85608830932',
				'is_premium'          => $is_pro,
				// Shortens the name Freemius shows to just "Lean Player".
				//
				// Freemius has no filter for the product name: get_plugin_name() reads
				// the plugin header directly and every Freemius surface (opt-in notice,
				// admin notice badge, menu and page titles, activation and deactivation
				// messages) is built from it. The only lever is premium_suffix, which
				// set_name() strips off the end of the header name when it matches.
				//
				// So this must stay an EXACT match for everything in the "Plugin Name:"
				// header after "Lean Player", compared case-insensitively. Change the
				// header and this silently stops matching, bringing the long name back
				// everywhere. Keep the two in sync.
				'premium_suffix'      => '- Video and Audio Player with Playlist for WordPress, Elementor and Gutenberg',
				'has_premium_version' => true,
				'is_premium_only'     => false,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'is_org_compliant'    => true,
				// Automatically removed in the free version. If you're not using the
				// auto-generated free version, delete this line before uploading to wp.org.
				'menu'                => array(
					// Top-level menu slug. Matches `add_menu_page()` in
					// includes/admin/class-menu.php so Freemius submenu items
					// (Account / Pricing / Contact) attach under it.
					'slug'       => 'lean-player',
					// Mirrors the `edit_posts` cap on `add_menu_page()` so
					// editors can open the parent menu without hitting the
					// "Sorry, you are not allowed to access this page" gate.
					// Individual Freemius submenu items still require
					// `manage_options`, so editors just don't see those.
					'capability' => 'edit_posts',
					'support'    => false,
					'pricing'    => !$is_pro,               // free users need the pricing/upgrade page
					'contact'    => false,
				),
			) );

			// Plugin icon for the opt-in screen and account page.
			//
			// Registered on the instance rather than as a raw add_filter() so the
			// SDK builds the hook name itself. The raw form needs the slug spelled
			// out ('fs_plugin_icon_<slug>'), which is a second copy of the value
			// declared above and silently stops working if the two ever diverge.
			//
			// Returns false when the file is missing instead of a dead path: the
			// SDK skips its entire fallback chain the moment this filter returns a
			// string, without checking the file exists, so a dead path renders a
			// broken image. The pro build has shipped without assets/img before.
			$leanpl_fs->add_filter( 'plugin_icon', function () {
				$icon = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( LEANPL_FILE ) ) . '/assets/img/icon-300x300.png';

				return file_exists( $icon ) ? $icon : false;
			} );
		}

		return $leanpl_fs;
	}

	// Init Freemius
	leanpl_fs();

	// Signal that SDK was initiated
	do_action( 'leanpl_fs_loaded' );

	// Sends opt-in/diagnostic data
	require_once LEANPL_DIR . '/includes/diagnostic-data.php';

	// Freemius stores the deactivation reason locally and only ships it if the plugin
	// is later deleted, so most feedback never arrives. Grab it as it is submitted.
	// Priority 1 because the SDK handler runs at 10 and ends in exit().
	add_action(
		'wp_ajax_fs_submit_uninstall_reason_' . leanpl_fs()->get_id(),
		'leanpl_capture_deactivation_reason',
		1
	);
}

if ( ! function_exists( 'leanpl_capture_deactivation_reason' ) ) {
	/**
	 * Mirror the deactivation reason to our own database before the SDK handler runs.
	 *
	 * Never exits: the Freemius handler must still run at priority 10 to store the
	 * reason and echo its response, otherwise the dialog hangs.
	 */
	function leanpl_capture_deactivation_reason() {
		// Same nonce action and field name the SDK verifies in check_ajax_referer().
		check_ajax_referer( 'fs_submit_uninstall_reason_' . leanpl_fs()->get_id(), 'security' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Mirrors the SDK guard: reason IDs are unsigned integers.
		$reason_id = fs_request_get( 'reason_id' );
		if ( ! ctype_digit( $reason_id ) ) {
			return;
		}

		$reason = leanpl_get_deactivation_reason( (int) $reason_id );

		// leanpl_get_installed_time() returns a Unix timestamp or null. installed_time
		// is timestamptz, so send ISO-8601 with an explicit offset.
		$installed_ts   = leanpl_get_installed_time();
		$installed_time = $installed_ts ? gmdate( 'c', $installed_ts ) : null;

		$fields = array(
			'reason_key'     => $reason['key'],
			'reason_text'    => $reason['text'],
			// 128 chars to match what the SDK itself stores.
			'user_comment'   => substr( trim( fs_request_get( 'reason_info', '' ) ), 0, 128 ),
			'installed_time' => $installed_time,
		);

		// Only opted-in users have already shared their site with us, so only they
		// get an address attached. Everyone else stays unidentified.
		if ( leanpl_fs()->is_registered() ) {
			$fields['site_url'] = home_url();
		}

		leanpl_send_deactivation_feedback( $fields );
	}
}


/**
 * Short product name for the Freemius opt-in screen.
 *
 * The `Plugin Name:` header is the long SEO title, and Freemius echoes it in
 * several places on the connect screen. Swap it for the short brand name there.
 */
if ( ! function_exists( 'leanpl_fs_shorten_product_name' ) ) {
	function leanpl_fs_shorten_product_name( $html ) {
		return str_replace(
			'Lean Player - Video and Audio Player with Playlist for WordPress, Elementor and Gutenberg',
			'Lean Player',
			$html
		);
	}
}

// Permission disclaimer ("This will allow %s to" / license sync notice).
leanpl_fs()->add_filter( 'plugin_title', function () {
	return 'Lean Player';
} );

// Custom header for existing users updating your plugin.
leanpl_fs()->add_filter( 'connect-header_on-update', 'leanpl_fs_shorten_product_name' );

// Opt-in body copy (bold product name inside the message).
leanpl_fs()->add_filter( 'connect_message', 'leanpl_fs_shorten_product_name' );
leanpl_fs()->add_filter( 'connect_message_on_update', 'leanpl_fs_shorten_product_name' );