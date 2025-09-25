<?php
namespace VAPFEM;

/**
 * Assets Manager Class
 * Handles registration and enqueuing of all plugin assets
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Assets_Manager {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Version of the plugin
     */
    private $version;

    /**
     * Track if assets have been loaded to prevent duplicates
     */
    private $assets_loaded = false;

    /**
     * Track if we're doing late loading (after wp_enqueue_scripts)
     */
    private $late_loading = false;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize assets management
     */
    public function init() {
        if( defined( 'WP_DEBUG' ) && WP_DEBUG ){
			$this->version = time();
		} else {
			$this->version = VAPFEM_VERSION;
		}

        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('wp_enqueue_scripts', array($this, 'conditional_enqueue'), 20); // Later priority for early detection
        add_action('elementor/frontend/after_register_scripts', array($this, 'register_scripts'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'register_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
    }

    /**
     * Register all plugin assets (scripts and styles)
     * Called during wp_enqueue_scripts for shortcode context
     */
    public function register_assets() {
        $this->register_scripts();
        $this->register_styles();
    }

    /**
     * Register all plugin scripts
     */
    public function register_scripts() {
        wp_register_script(
            'plyr',
            VAPFEM_URI . '/assets/js/plyr.min.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_register_script(
            'plyr-polyfilled',
            VAPFEM_URI . '/assets/js/plyr.polyfilled.min.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_register_script(
            'vapfem-main',
            VAPFEM_URI . '/assets/js/main.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    /**
     * Register all plugin styles
     */
    public function register_styles() {
        wp_register_style(
            'plyr',
            VAPFEM_URI . '/assets/css/plyr.css',
            array(),
            $this->version
        );

        wp_register_style(
            'vapfem-main',
            VAPFEM_URI . '/assets/css/main.css',
            array(),
            $this->version
        );
    }

    /**
     * Register admin-specific assets
     */
    public function register_admin_assets() {
        wp_register_style(
            'vapfem-admin',
            VAPFEM_URI . '/assets/css/admin.css',
            array(),
            $this->version
        );

        wp_register_script(
            'vapfem-admin',
            VAPFEM_URI . '/assets/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    /**
     * Early detection method - scan post content for shortcodes
     * Called during wp_enqueue_scripts hook
     */
    public function conditional_enqueue() {
        // Skip if already loaded or in admin
        if ($this->assets_loaded || is_admin()) {
            return;
        }

        global $post;

        // Check if current post contains lean_video shortcode
        if ($post && has_shortcode($post->post_content, 'lean_video')) {
            $this->enqueue_player_assets();
        }
    }

    /**
     * Enqueue player assets when needed
     * Called from shortcodes or widgets on demand
     */
    public function enqueue_player_assets() {
        // Prevent duplicate loading
        if ($this->assets_loaded) {
            return;
        }

        wp_enqueue_script('plyr');
        wp_enqueue_script('vapfem-main');
        wp_enqueue_style('plyr');
        wp_enqueue_style('vapfem-main');

        $this->assets_loaded = true;
    }

    /**
     * Ensure assets are loaded - runtime safety net
     * Called by shortcode execution to handle edge cases
     */
    public function ensure_assets_loaded() {
        // Skip if already loaded
        if ($this->assets_loaded) {
            return;
        }

        // Check if we're past wp_enqueue_scripts hook
        if (did_action('wp_enqueue_scripts')) {
            // Too late for normal enqueuing - use fallback
            $this->late_loading = true;
            $this->inline_critical_assets();
        } else {
            // Still time for normal enqueuing
            $this->enqueue_player_assets();
        }
    }

    /**
     * Fallback for late loading scenarios
     * Inline critical CSS and enqueue JS immediately
     */
    private function inline_critical_assets() {
        // Prevent duplicate loading
        if ($this->assets_loaded) {
            return;
        }

        // Inline critical CSS to prevent FOUC
        add_action('wp_footer', array($this, 'output_critical_css'), 1);

        // Enqueue JS in footer (still works after wp_enqueue_scripts)
        wp_enqueue_script('plyr');
        wp_enqueue_script('vapfem-main');

        $this->assets_loaded = true;
    }

    /**
     * Output critical CSS inline to prevent FOUC
     */
    public function output_critical_css() {
        if (!$this->late_loading) {
            return;
        }

        echo '<style id="vapfem-critical-css">
            .vapfem-player {
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .vapfem-player.plyr--ready {
                opacity: 1;
            }
            .vapfem-player video,
            .vapfem-player iframe {
                width: 100%;
                height: auto;
            }
        </style>';

        // Load full CSS via link tag
        echo '<link rel="stylesheet" id="plyr-css" href="' . esc_url(VAPFEM_URI . '/assets/css/plyr.css') . '?ver=' . esc_attr($this->version) . '" type="text/css" media="all" />';
        echo '<link rel="stylesheet" id="vapfem-main-css" href="' . esc_url(VAPFEM_URI . '/assets/css/main.css') . '?ver=' . esc_attr($this->version) . '" type="text/css" media="all" />';
    }
}

// Initialize assets manager
Assets_Manager::get_instance();