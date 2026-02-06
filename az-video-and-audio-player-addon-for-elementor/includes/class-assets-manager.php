<?php
namespace LeanPL;

/**
 * Unified Assets Manager
 * 
 * Single source of truth for ALL plugin assets across:
 * - Frontend (shortcodes, widgets)
 * - Elementor Editor
 * - Elementor Preview
 * - Admin pages
 * 
 * DRY Principle: All asset definitions, registration, and enqueuing in ONE place
 */
class Assets_Manager {

    private static $instance = null;
    private $version;
    private $assets_loaded = false;
    private $late_loading = false;
    private $loaded = [];

    /**
     * Asset definitions - SINGLE SOURCE OF TRUTH
     * All asset paths, handles, and dependencies defined here
     */
    private $assets = [
        'scripts' => [
            'plyr' => [
                'file' => '/assets/js/plyr.min.js',
                'deps' => ['jquery'],
                'in_footer' => true,
                'contexts' => ['frontend'],
            ],
            'plyr-polyfilled' => [
                'file' => '/assets/js/plyr.polyfilled.min.js',
                'deps' => ['jquery'],
                'in_footer' => true,
                'contexts' => ['frontend'],
            ],
            'leanpl-main' => [
                'file' => '/assets/js/main.js',
                'deps' => ['jquery', 'plyr'],
                'in_footer' => true,
                'contexts' => ['frontend'],
            ],
            'leanpl-admin' => [
                'file' => '/assets/js/admin.js',
                'deps' => ['jquery'],
                'in_footer' => true,
                'contexts' => ['admin'],
            ],
        ],
        'styles' => [
            'plyr' => [
                'file' => '/assets/css/plyr.css',
                'deps' => [],
                'in_footer' => false,
                'contexts' => ['frontend'],
            ],
            'leanpl-main' => [
                'file' => '/assets/css/main.css',
                'deps' => ['plyr'],
                'in_footer' => false,
                'contexts' => ['frontend'],
            ],
            'leanpl-editor' => [
                'file' => '/assets/css/editor.css',
                'deps' => [],
                'in_footer' => false,
                'contexts' => ['elementor-editor'],
            ],
            'leanpl-admin' => [
                'file' => '/assets/css/admin.css',
                'deps' => [],
                'in_footer' => false,
                'contexts' => ['admin'],
            ],
        ],
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    /**
     * Initialize the assets manager
     */
    public function init() {
        $this->version = leanpl_get_version();

        // -- Register all assets --
        add_action('admin_enqueue_scripts', [$this, 'register_all']);
        add_action('wp_enqueue_scripts', [$this, 'register_all']);

        // -- Load admin assets --
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // -- Load frontend assets --
        add_action('wp_enqueue_scripts', [$this, 'common_frontend_enqueue']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_if_shortcode'], 20);

        // -- Load Elementor assets --
        // Note: Widget frontend assets are loaded via widget registration, so we don't need to load them here.
        //       But we need to register the assets for the editor.
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'register_n_enqueue_elementor_editor_assets']);
    }

    /**
     * Register all assets
     */
    public function register_all() {
        foreach ($this->assets['styles'] as $handle => $config) {
            $url = LEANPL_URI . $config['file'];
            $deps = $config['deps'];
            $in_footer = $config['in_footer'];

            wp_register_style($handle, $url, $deps, $this->version, $in_footer);
        }

        foreach ($this->assets['scripts'] as $handle => $config) {
            $url = LEANPL_URI . $config['file'];
            $deps = $config['deps'];
            $in_footer = $config['in_footer'];

            wp_register_script($handle, $url, $deps, $this->version, $in_footer);
        }
    }

    public function enqueue_admin_assets($hook) {
        if (!leanpl_is_our_admin_page()) {
            return;
        }
        
        $this->load_assets_by_context('admin');
    }

    public function common_frontend_enqueue() {
        // Enqueue jQuery first, other plugins may remove it from the queue
        wp_enqueue_script('jquery');

        // Localization
        wp_localize_script('jquery', 'leanpl_params', [
            'version' => $this->version,
            'debugMode' => leanpl_is_test_mode() || leanpl_is_debug_mode(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Load by context
     * 
     * @param string $context The context to load the assets for
     * @return void
     */

    public function load_assets_by_context($context) {
        foreach ($this->assets['styles'] as $handle => $config) {
            // Skip if not for this context
            if (!in_array($context, $config['contexts'])) {
                continue;
            }

            // Enqueue it
            wp_enqueue_style($handle);
        }

        foreach ($this->assets['scripts'] as $handle => $config) {
            // Skip if not for this context
            if (!in_array($context, $config['contexts'])) {
                continue;
            }

            // Enqueue it
            wp_enqueue_script($handle);
        }
    }

    /**
     * Load if shortcode is found in the post content
     * 
     * Note: this will not support do_shortcode() usage
     * @return void
     */
    public function enqueue_if_shortcode() {
        if ($this->assets_loaded || is_admin()) {
            return;
        }

        global $post; // might be null for 404 pages

        // Fixed: Attempt to read property on null
        if ($post && ( 
                has_shortcode( $post->post_content, 'lean_video' ) ||
                has_shortcode( $post->post_content, 'lean_audio' ) || 
                has_shortcode( $post->post_content, 'lean_player' )
            )
        ) {
            $this->load_assets_by_context('frontend');
        }
    }

    /**
     * Load Elementor editor assets
     */
    public function register_n_enqueue_elementor_editor_assets() {
        // Register all assets again because in the current hooks the assets are not registered via wp_enqueue_scripts
        $this->register_all();

        // Load assets for Elementor editor
        $this->load_assets_by_context('elementor-editor');
    }
}

// Initialize
Assets_Manager::get_instance();