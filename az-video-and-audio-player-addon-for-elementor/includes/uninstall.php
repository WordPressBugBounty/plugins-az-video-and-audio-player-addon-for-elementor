<?php
/**
 * What deleting the plugin removes.
 *
 * Only the media-service data goes: the saved keys and the cached video
 * details.
 * Players, playlists and player settings stay, as they always have.
 *
 * This lives in the shared part of the plugin because the keys can outlive
 * the edition that saved them: a site that saved them and then moved to the
 * other edition still has them stored.
 *
 * @package LeanPL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'leanpl_uninstall_cleanup' ) ) {
	/**
	 * Remove the media-service data, on every site of a network.
	 *
	 * Stands back while the other copy of Lean Player is still installed,
	 * since that copy may be using the same keys right now.
	 *
	 * @return void
	 */
	function leanpl_uninstall_cleanup() {
		$other = 'lean-player-pro/plugin-main.php' === plugin_basename( LEANPL_FILE )
			? 'az-video-and-audio-player-addon-for-elementor/plugin-main.php'
			: 'lean-player-pro/plugin-main.php';

		/**
		 * Whether the other copy of Lean Player is installed, which keeps
		 * the shared data in place.
		 *
		 * @param bool   $installed Whether its main file is on disk.
		 * @param string $other     Its plugin basename.
		 */
		if ( apply_filters( 'leanpl/uninstall/other_copy_installed', file_exists( WP_PLUGIN_DIR . '/' . $other ), $other ) ) {
			return;
		}

		if ( ! is_multisite() ) {
			leanpl_uninstall_site();
			return;
		}

		foreach ( get_sites( [ 'fields' => 'ids', 'number' => 0 ] ) as $site_id ) {
			switch_to_blog( $site_id );
			leanpl_uninstall_site();
			restore_current_blog();
		}
	}
}

if ( ! function_exists( 'leanpl_uninstall_site' ) ) {
	/**
	 * Remove the media-service data from the current site.
	 *
	 * The names are spelled out rather than read from the classes that own
	 * them, because those classes are not loaded on every build.
	 *
	 * @return void
	 */
	function leanpl_uninstall_site() {
		global $wpdb;

		delete_option( 'leanpl_integrations' );

		// Cached video details. Each one goes through delete_transient() so a
		// cached copy goes too.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- a one-off sweep on delete; there is no API for finding transients by prefix.
		$names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_leanpl_bunny_v_' ) . '%'
			)
		);
		foreach ( $names as $name ) {
			delete_transient( substr( $name, strlen( '_transient_' ) ) );
		}
	}
}
