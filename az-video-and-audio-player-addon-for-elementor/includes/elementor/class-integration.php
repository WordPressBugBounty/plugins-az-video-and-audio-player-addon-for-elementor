<?php
namespace LeanPL\Elementor;

use LeanPL\Assets_Manager;

/**
 * Elementor Integration Class
 * 
 * Handles ONLY Elementor-specific concerns:
 * - Compatibility checks
 * - Widget registration
 * - Admin notices
 * 
 * Asset loading delegated to Assets_Manager (DRY)
 */
class Integration {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Loading sequence: plugins_loaded -> elementor/loaded -> register_hooks()
     */
    private function __construct() {
        // Register ONLY widget-related hooks
        // Assets handled by Assets_Manager automatically
        $this->register_hooks();
    }

    private function register_hooks() {
        // ONLY widget registration - Assets_Manager handles assets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($widgets_manager) {
        // Load widget files
        require_once LEANPL_DIR . '/includes/elementor/widgets/video-player.php';
        require_once LEANPL_DIR . '/includes/elementor/widgets/audio-player.php';
        require_once LEANPL_DIR . '/includes/elementor/widgets/playlist.php';

        // Register widgets
        $widgets_manager->register(new \LeanPL_Video_Player());
        $widgets_manager->register(new \LeanPL_Audio_Player());
        $widgets_manager->register(new \LeanPL_Playlist_Widget());
    }
}