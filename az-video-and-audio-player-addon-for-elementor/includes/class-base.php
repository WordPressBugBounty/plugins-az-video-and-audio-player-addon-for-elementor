<?php
namespace LeanPL;

if (!defined('ABSPATH')) {
    exit;
}

// Early deps: functions.php defines leanpl_is_pro_active(), which freemius-init
// reads in bootstrap() long before load_dependencies() runs.
require_once LEANPL_DIR . '/includes/functions.php';

/**
 * Main Plugin Base Class
 * 
 * Handles core plugin initialization
 */
class Base {
    
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bootstrap the plugin
     * Called from main plugin file before plugins_loaded action
     */
    public static function bootstrap($plugin_file) {
        // Check if we should load pro features
        $should_load_pro = leanpl_should_load_pro();
        
        // Load pro-loader early if it exists and we're in pro mode
        if ($should_load_pro) {
            $pro_loader_path = LEANPL_DIR . '/pro/pro-loader.php';
            if (file_exists($pro_loader_path)) {
                require_once $pro_loader_path;
            }
        }

        // Reads leanpl_is_pro_active() for its is_premium / pricing-menu decisions.
        require_once LEANPL_DIR . '/includes/freemius-init.php';
        
        /**
         * Fires early in bootstrap, before plugins_loaded action
         * Perfect for Freemius SDK initialization and other early pro setup
         * 
         * @since 3.0.0
         * @param string $plugin_file Main plugin file path
         */
        do_action('leanpl/bootstrap/early_init', $plugin_file);
        
        // Load dual activation preventer
        require_once LEANPL_DIR . '/includes/class-dual-activation-preventer.php';
        
        // Register activation hook
        register_activation_hook($plugin_file, [__CLASS__, 'on_activation']);
        
        // Ensure only one version is active
        Dual_Activation_Preventer::ensure_single_activation();
        
        // Initialize on plugins_loaded
        add_action('plugins_loaded', [__CLASS__, 'plugins_loaded_cb'], 10);
    }

    /**
     * Callback for plugins_loaded action
     */
    public static function plugins_loaded_cb() {
        // Initialize base plugin
        self::get_instance();
        
        // Load Pro features if available
        self::load_pro();
    }

    /**
     * Plugin activation handler
     */
    public static function on_activation() {
        // Existing users may deactivate and reactivate the plugins multiple times. So we need to track installation time only if it's not already set.
        if (!get_option('leanpl_installed_time')) {
            update_option('leanpl_installed_time', time(), '', false);
        }
    }

    /**
     * Load Pro features if available
     */
    private static function load_pro() {
        if (leanpl_should_load_pro() && file_exists(LEANPL_DIR . '/pro/pro-loader.php')) {
            require_once LEANPL_DIR . '/pro/pro-loader.php';
            
            if (function_exists('leanpl_pro_init')) {
                leanpl_pro_init();
            }
        }
    }

    /**
     * Constructor - Initialize plugin
     */
    private function __construct() {
        $this->load_textdomain();
        $this->load_dependencies();
        $this->init_integrations();
        $this->maybe_create_demos();
    }

    /**
     * Load plugin text domain for translations
     */
    private function load_textdomain() {
        load_plugin_textdomain('vapfem', false, LEANPL_DIR . '/languages/');
    }

    /**
     * Load all required files
     */
    private function load_dependencies() {
        // Load utility functions first
        require_once LEANPL_DIR . '/includes/functions-converter.php';
        require_once LEANPL_DIR . '/includes/functions-player.php';
        require_once LEANPL_DIR . '/includes/functions-admin.php';

        // Load core classes
        require_once LEANPL_DIR . '/includes/class-assets-manager.php';
        require_once LEANPL_DIR . '/includes/class-player-renderer.php';
        require_once LEANPL_DIR . '/includes/class-config-merger.php';

        // Load shortcodes
        require_once LEANPL_DIR . '/includes/shortcodes/class-video-shortcode.php';
        require_once LEANPL_DIR . '/includes/shortcodes/class-audio-shortcode.php';
        require_once LEANPL_DIR . '/includes/shortcodes/class-player-shortcode.php';
        require_once LEANPL_DIR . '/includes/shortcodes/class-timestamp-shortcode.php';

        // Load playlist feature conditionally
        if ( leanpl_get_option( 'playlist.enabled', true ) ) {
            require_once LEANPL_DIR . '/includes/playlist/class-playlist.php';
        }

         // Load post type and metaboxes. Used in frontend as well to fetch player data
        require_once LEANPL_DIR . '/includes/class-custom-posts.php';
        require_once LEANPL_DIR . '/includes/class-metaboxes.php';
        require_once LEANPL_DIR . '/includes/class-metaboxs-save.php';

        // Load preview class (needed for frontend preview functionality)
        require_once LEANPL_DIR . '/includes/class-player-preview.php';

        // Load admin files
        if (is_admin()) {
            // Deactivation feedback now rides the Freemius dialog (see diagnostic-data.php),
            // so the in-house modal class is no longer loaded.
            // require_once LEANPL_DIR . '/includes/admin/class-deactivation-feedback.php';
            require_once LEANPL_DIR . '/includes/admin/class-menu.php';
            require_once LEANPL_DIR . '/includes/admin/class-settings-page.php';
            require_once LEANPL_DIR . '/includes/admin/class-player-table-columns.php';
        }

        // Include Elementor integration
        require_once LEANPL_DIR . '/includes/elementor/class-integration.php';
    }

    /**
     * Retired. Deactivation feedback now rides the Freemius dialog and is captured
     * server-side in includes/diagnostic-data.php, so the in-house modal is no longer
     * wired up. Kept for reference until the class file is removed.
     */
    private function init_deactivation_feedback() {
        // Initialize deactivation feedback (admin only if class exists)
        if (is_admin() && !leanpl_is_pro_active() && class_exists('\LeanPL\Admin\Deactivation_Feedback')) {

            $installed_time_iso = leanpl_get_installed_time() ? gmdate('Y-m-d\TH:i:s\Z', leanpl_get_installed_time()) : ''; // ISO formate for supabase

            new \LeanPL\Admin\Deactivation_Feedback([
                'plugin_basename'  => plugin_basename(LEANPL_FILE),
                'plugin_slug'      => 'leanpl',
                'plugin_version'   => LEANPL_VERSION,
                'prefix'           => 'leanpl',
                'show_close_icon'  => false,
                'reason_variation' => 'high-friction',
                'debug_mode'       => false,
                'installed_time_iso' => $installed_time_iso,
            ]);
        }
    }

    /**
     * Initialize integrations (Elementor, Gutenberg, etc.)
     */
    private function init_integrations() {
        if (did_action('elementor/loaded')) {
            \LeanPL\Elementor\Integration::get_instance();

        // On few elementor versions, the elementor/loaded action hook is not fired, so we need to wait for it.
        } else {
            add_action('elementor/loaded', function() {
                \LeanPL\Elementor\Integration::get_instance();
            }, 20);
        }
    }

    /**
     * Check if demos need to be created and create them if needed
     * Runs on every page load, but only creates demos once
     */
    private function maybe_create_demos() {
        // Check if demos have already been created
        $demos_already_created = get_option('leanpl_demos_created');

        if (!$demos_already_created) {
            // Demos don't exist yet - create them
            // This applies to both new installs and existing users updating

            // Hook to 'admin_init' to ensure post types are registered and we're in admin
            add_action('admin_init', [$this, 'create_demos_on_admin_init'], 20);
        }

        if (!get_option('leanpl_demo_playlists_created')) {
            add_action('admin_init', [$this, 'create_demo_playlists_on_admin_init'], 30);
        }
    }

    /**
     * Create demo players after WordPress admin is initialized
     * Runs on 'admin_init' hook to ensure post types are registered
     */
    public function create_demos_on_admin_init() {
        // Double-check flag (in case it was set between hook registration and execution)
        $demos_already_created = get_option('leanpl_demos_created');

        if (!$demos_already_created) {
            // Check if post type already has any data
            $existing_posts = get_posts([
                'post_type' => 'lean_player',
                'posts_per_page' => 1,
                'post_status' => 'any',
                'fields' => 'ids',
            ]);

            // Only create demos if post type is empty
            if (empty($existing_posts)) {
                // Create demo players
                require_once LEANPL_DIR . '/includes/class-demo-players.php';
                \LeanPL\Demo_Players::create_demo_players();

                // Mark demos as created to prevent duplicates
                update_option('leanpl_demos_created', true, false);
            } else {
                // Post type has data, mark as created to skip future checks
                update_option('leanpl_demos_created', true, false);
            }
        }
    }

    /**
     * Create demo playlists if enough players exist (threshold: 2 per type).
     * Runs after create_demos_on_admin_init (priority 30 vs 20).
     */
    public function create_demo_playlists_on_admin_init() {
        if (get_option('leanpl_demo_playlists_created')) {
            return;
        }

        require_once LEANPL_DIR . '/includes/class-demo-players.php';
        \LeanPL\Demo_Players::maybe_create_demo_playlists();

        update_option('leanpl_demo_playlists_created', true, false);
    }
}
