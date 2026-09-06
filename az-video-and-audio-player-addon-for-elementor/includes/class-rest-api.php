<?php
namespace LeanPL;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The plugin's REST endpoints. Deliberately separate from the wp_ajax_*
 * handlers (class-live-preview-ajax.php, class-custom-preset-ajax.php) so
 * the two mechanisms never get tangled - nothing here converts or replaces
 * an existing AJAX action.
 */
class Rest_Api {

    const NAMESPACE_V1 = 'leanpl/v1';

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( self::NAMESPACE_V1, '/stats', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_stats' ],
            'permission_callback' => '__return_true',
        ] );
    }

    /**
     * @return \WP_REST_Response
     */
    public function get_stats() {
        global $wpdb;

        $demo_like = $wpdb->esc_like( 'Demo' ) . '%';

        $video_players_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_player_type' AND pm.meta_value = %s WHERE p.post_type = 'lean_player' AND p.post_title NOT LIKE %s",
            'video',
            $demo_like
        ) );

        $audio_players_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_player_type' AND pm.meta_value = %s WHERE p.post_type = 'lean_player' AND p.post_title NOT LIKE %s",
            'audio',
            $demo_like
        ) );

        $playlists_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'lean_playlist' AND post_title NOT LIKE %s",
            $demo_like
        ) );

        $installed_time = leanpl_get_installed_time();

        return rest_ensure_response( [
            'version'            => leanpl_get_version(),
            'debug_mode'         => leanpl_is_test_mode() || leanpl_is_debug_mode(),
            'playlist_enabled'   => (bool) leanpl_get_option( 'playlist.enabled', true ),
            'video_players_count' => $video_players_count,
            'audio_players_count' => $audio_players_count,
            'playlists_count'    => $playlists_count,
            'installed_date'     => $installed_time ? date_i18n( get_option( 'date_format' ), $installed_time ) : '',
        ] );
    }
}

Rest_Api::get_instance();
