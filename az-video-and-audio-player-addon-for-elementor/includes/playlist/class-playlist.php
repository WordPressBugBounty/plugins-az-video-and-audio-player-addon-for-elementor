<?php
/**
 * Playlist — entry point. Loads deps, registers shortcode, handles render.
 *
 * Self-initialized: entry point, must register shortcode on load.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;
use LeanPL\Assets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Playlist class.
 *
 * Entry point. Loads deps, registers shortcode, handles render.
 */
class Playlist {

    /**
     * Instance.
     *
     * @var Playlist|null
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return Playlist
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        defined( 'LEANPL_PLAYLIST_DIR' ) || define( 'LEANPL_PLAYLIST_DIR', dirname( __FILE__ ) );
        $this->load_dependencies();

        // Assets are managed by the Assets_Manager class, so we don't need to enqueue them here.

        add_action( 'init', [ $this, 'register_shortcode' ] );
    }

    /**
     * Load playlist dependencies.
     *
     * @return void
     */
    private function load_dependencies() {
        require_once LEANPL_PLAYLIST_DIR . '/class-config.php';
        require_once LEANPL_PLAYLIST_DIR . '/class-css-helper.php';
        require_once LEANPL_PLAYLIST_DIR . '/class-player-markup-data.php';
        require_once LEANPL_PLAYLIST_DIR . '/class-item-view.php';
        require_once LEANPL_PLAYLIST_DIR . '/class-renderer.php';
        require_once LEANPL_PLAYLIST_DIR . '/class-preset-manager.php';

        // Admin-only playlist files
        if ( is_admin() ) {
            require_once LEANPL_PLAYLIST_DIR . '/class-playlist-metaboxes.php';
            require_once LEANPL_PLAYLIST_DIR . '/class-playlist-metaboxes-save.php';
            require_once LEANPL_PLAYLIST_DIR . '/class-playlist-table-columns.php';
        }
    }

    /**
     * Register shortcode.
     *
     * @return void
     */
    public function register_shortcode() {
        add_shortcode( 'lean_playlist', [ $this, 'render_shortcode' ] );
    }

    /**
     * Shortcode callback.
     *
     * @param array       $atts    Shortcode attributes.
     * @param string|null $content Shortcode content.
     * @return string
     */
    public function render_shortcode( $atts, $content = null ) {
        // Capture which keys the user actually wrote before shortcode_atts fills all defaults.
        $user_keys = is_array( $atts ) ? array_keys( $atts ) : [];

        // Allowed attrs derived from defaults — playlist-defaults.php is the SSOT.
        // 'enabled' and 'playlist_type' excluded (internal/runtime, not user-facing).
        $defaults         = leanpl_get_playlist_defaults();
        $excluded         = [ 'enabled', 'playlist_type' ];
        $allowed_defaults = array_diff_key( $defaults, array_flip( $excluded ) );

        $atts = shortcode_atts(
            array_merge( [ 'id' => 0 ], $allowed_defaults ),
            $atts,
            'lean_playlist'
        );

        $playlist_id = absint( $atts['id'] );

        // Only pass attrs the user explicitly wrote — not defaults for unset keys.
        // This prevents shortcode defaults from stomping post meta or global settings.
        $attr_overrides = array_intersect_key(
            array_diff_key( $atts, [ 'id' => 0 ] ),
            array_flip( $user_keys )
        );

        // Ensure assets are loaded even when used with do_shortcode().
        if ( ! wp_script_is( 'leanpl-main', 'enqueued' ) ) {
            Assets_Manager::get_instance()->load_assets_by_context( 'frontend' );
        }

        ob_start();
        $renderer = new Renderer( $playlist_id, $attr_overrides );
        $renderer->render();
        return ob_get_clean();
    }
}

Playlist::get_instance();
