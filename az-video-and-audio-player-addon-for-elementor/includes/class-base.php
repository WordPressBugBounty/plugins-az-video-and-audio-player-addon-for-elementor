<?php
namespace LeanPL;

if (!defined('ABSPATH')) {
    exit;
}

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
        // Check if we should load pro features (respects LEANPL_MODE_SWITCH)
        $should_load_pro = self::should_load_pro();
        
        // Load pro-loader early if it exists and we're in pro mode
        if ($should_load_pro) {
            $pro_loader_path = LEANPL_DIR . '/pro/pro-loader.php';
            if (file_exists($pro_loader_path)) {
                require_once $pro_loader_path;
            }
        }
        
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
     * Check if pro features should be loaded
     * Respects LEANPL_MODE_SWITCH for development mode switching
     * 
     * @return bool True if pro mode is active
     */
    private static function should_load_pro() {
        // 1. Check if this is standalone PRO (folder has -pro suffix)
        if (strpos(plugin_basename(LEANPL_FILE), '-pro/') !== false) {
            return true;
        }
        
        // 2. Development: Check mode switch
        if (defined('LEANPL_MODE_SWITCH') && LEANPL_MODE_SWITCH === 'pro') {
            return true;
        }
        
        // 3. Default: FREE mode
        return false;
    }

    /**
     * Callback for plugins_loaded action
     */
    public static function plugins_loaded_cb() {
        // Detect and define plugin mode
        if (!defined('LEANPL_MODE')) {
            define('LEANPL_MODE', self::detect_mode());
        }
        
        // Initialize base plugin
        self::get_instance();
        
        // Load Pro features if available
        self::load_pro();
    }

    /**
     * Detect plugin mode (free or pro)
     */
    private static function detect_mode() {
        // 1. Check if this is standalone PRO (folder has -pro suffix)
        if (strpos(plugin_basename(LEANPL_FILE), '-pro/') !== false) {
            return 'pro';
        }
        
        // 2. Development: Check mode switch
        if (LEANPL_MODE_SWITCH === 'pro') {
            return 'pro';
        }
        
        // 3. Default: FREE mode
        return 'free';
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
        if (LEANPL_MODE === 'pro' && file_exists(LEANPL_DIR . '/pro/pro-loader.php')) {
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
        $this->init_deactivation_feedback();
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
        require_once LEANPL_DIR . '/includes/functions.php';

        // Load core classes
        require_once LEANPL_DIR . '/includes/class-assets-manager.php';
        require_once LEANPL_DIR . '/includes/class-player-renderer.php';
        require_once LEANPL_DIR . '/includes/class-config-merger.php';

        // Load shortcodes
        require_once LEANPL_DIR . '/includes/shortcodes/class-video-shortcode.php';
        require_once LEANPL_DIR . '/includes/shortcodes/class-audio-shortcode.php';
        require_once LEANPL_DIR . '/includes/shortcodes/class-player-shortcode.php';  

         // Load post type and metaboxes. Used in frontend as well to fetch player data
        require_once LEANPL_DIR . '/includes/class-custom-posts.php';
        require_once LEANPL_DIR . '/includes/class-metaboxes.php';
        require_once LEANPL_DIR . '/includes/class-metaboxs-save.php';

        // Load preview class (needed for frontend preview functionality)
        require_once LEANPL_DIR . '/includes/class-player-preview.php';

        // Load admin files
        if (is_admin()) {
            require_once LEANPL_DIR . '/includes/admin/class-deactivation-feedback.php';
            require_once LEANPL_DIR . '/includes/admin/class-menu.php';
            require_once LEANPL_DIR . '/includes/admin/class-settings-page.php';
            require_once LEANPL_DIR . '/includes/admin/class-player-table-columns.php';
        }

        // Include Elementor integration
        require_once LEANPL_DIR . '/includes/elementor/class-integration.php';
    }

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
}
