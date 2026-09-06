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
                'contexts' => ['frontend', 'admin'],
            ],
            // 'plyr-polyfilled' => [
            //     'file' => '/assets/js/plyr.polyfilled.min.js',
            //     'deps' => ['jquery'],
            //     'in_footer' => true,
            //     'contexts' => ['frontend'],
            // ],
            'leanpl-player-utils' => [
                'file' => '/assets/js/player-utils.js',
                'deps' => [],
                'in_footer' => true,
                'contexts' => ['frontend', 'admin'],
            ],
            'leanpl-main' => [
                'file' => '/assets/js/main.js',
                'deps' => ['jquery', 'plyr', 'leanpl-player-utils'],
                'in_footer' => true,
                // 'admin' - needed for the lean_player live preview panel
                // (class-player-preview.php) to auto-init Plyr on the real
                // markup fragment the AJAX endpoint returns. enqueue_admin_assets()
                // already scopes ALL 'admin'-context assets to our own admin
                // pages (leanpl_is_our_admin_page()), so this doesn't leak
                // elsewhere in wp-admin.
                'contexts' => ['frontend', 'admin'],
            ],
            'leanpl-playlist' => [
                'file' => '/assets/js/playlist.js',
                'deps' => ['jquery', 'plyr', 'leanpl-player-utils'],
                'in_footer' => true,
                // 'admin' - lean_playlist live preview panel (class-player-preview.php),
                // same reasoning as leanpl-main above.
                'contexts' => ['frontend', 'admin'],
            ],
            'leanpl-elementor' => [
                'file' => '/assets/js/elementor.js',
                'deps' => ['jquery', 'leanpl-player-utils', 'leanpl-main', 'leanpl-playlist'],
                'in_footer' => true,
                'contexts' => ['elementor-widget'],
            ],
            'leanpl-elementor-editor' => [
                'file' => '/assets/js/elementor-editor.js',
                'deps' => ['jquery'],
                'in_footer' => true,
                'contexts' => ['elementor-editor'],
            ],
            'leanpl-custom-preset-builder' => [
                'file' => '/assets/js/custom-preset-builder.js',
                'deps' => ['jquery', 'plyr', 'leanpl-player-utils'],
                'in_footer' => true,
                'contexts' => ['admin'],
            ],
            'leanpl-live-preview' => [
                'file' => '/assets/js/live-preview.js',
                'deps' => ['jquery'],
                'in_footer' => true,
                'contexts' => ['admin'],
            ],
            // 'sortablejs' is registered by the lex-settings-new framework's own
            // asset manager (includes/libs/lex-settings-new/core/includes/class-assets-manager.php) -
            // single canonical copy lives there now, this plugin just depends on
            // the handle. Used here for the Playlist Items card's drag reorder.
            'leanpl-admin-new' => [
                'file' => '/assets/js/admin-new.js',
                'deps' => ['jquery', 'wp-color-picker', 'sortablejs'],
                'in_footer' => true,
                'contexts' => ['admin'],
            ],
            'leanpl-admin-new-conditional-fields' => [
                'file' => '/assets/js/admin-new-conditional-fields.js',
                'deps' => ['jquery', 'leanpl-admin-new'],
                'in_footer' => true,
                'contexts' => ['admin'],
            ],
        ],
        'styles' => [
            'leanpl-lex-tokens' => [
                'file' => '/includes/libs/lex-settings-new/core/assets/css/lex-tokens.css',
                'deps' => [],
                'in_footer' => false,
                'contexts' => ['frontend', 'admin', 'elementor-editor'],
            ],
            'plyr' => [
                'file' => '/assets/css/plyr.css',
                'deps' => [],
                'in_footer' => false,
                'contexts' => ['frontend', 'admin'],
            ],
            'leanpl-main' => [
                'file' => '/assets/css/main.css',
                'deps' => ['leanpl-lex-tokens', 'plyr'],
                'in_footer' => false,
                // 'admin' - real frontend layout CSS for the lean_player live
                // preview panel, see the script entry of the same handle above.
                'contexts' => ['frontend', 'admin'],
            ],
            'leanpl-playlist' => [
                'file' => '/assets/css/playlist.css',
                'deps' => ['leanpl-lex-tokens', 'plyr'],
                'in_footer' => false,
                // 'admin' - lean_playlist live preview panel (class-player-preview.php),
                // same reasoning as leanpl-main above.
                'contexts' => ['frontend', 'admin'],
            ],
            'leanpl-editor' => [
                'file' => '/assets/css/editor.css',
                'deps' => ['leanpl-lex-tokens'],
                'in_footer' => false,
                'contexts' => ['elementor-editor'],
            ],
            'leanpl-admin' => [
                'file' => '/assets/css/admin.css',
                'deps' => ['leanpl-lex-tokens'],
                'in_footer' => false,
                'contexts' => ['admin'],
            ],
            'leanpl-custom-preset-builder' => [
                'file' => '/assets/css/custom-preset-builder.css',
                'deps' => ['leanpl-lex-tokens'],
                'in_footer' => false,
                'contexts' => ['admin'],
            ],
            'leanpl-tailwind' => [
                // Compiled via `npm run tailwind:build` (tailwind.config.js) - prefix
                // 'tw-', preflight disabled, so utilities are purely additive and never
                // fight WP admin's own chrome or the lex-settings-new token system.
                'file' => '/assets/css/tailwind-admin.css',
                'deps' => ['leanpl-lex-tokens'],
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
        add_action('wp_enqueue_scripts', [$this, 'register_all'], 20); // Ensure theme's assets are loaded first

        // -- Load admin assets --
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // -- Plugin-context body class (scopes admin CSS to our screens) --
        add_filter('admin_body_class', [$this, 'add_scope_body_class']);
        
        // -- Load frontend assets --
        // Priority 21: must run after register_all() (20) — common_frontend_enqueue()
        // calls wp_add_inline_style('leanpl-main', ...), which requires the handle to
        // already be registered or it fails silently (no error, no output).
        add_action('wp_enqueue_scripts', [$this, 'common_frontend_enqueue'], 21);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_if_shortcode'], 30);

        // Also run on our own admin screens (live preview panel) - playlist.js's
        // entire IIFE bails at the top when `leanpl_params` is undefined
        // (localized here), silently skipping its autoInit('.lpl-playlist', ...)
        // registration. main.js has no such guard, which is why the player
        // preview worked without this.
        add_action('admin_enqueue_scripts', [$this, 'common_frontend_enqueue'], 21);

        // -- Load Elementor assets --
        // Note: Widget frontend assets are loaded via widget registration, so we don't need to load them here.
        //       But we need to register the assets for the editor.
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'register_n_enqueue_elementor_editor_assets']);
    }

    /**
     * Add the plugin-scope body class on our own admin screens.
     *
     * Metaboxes render into WordPress's #poststuff and the settings page is a
     * separate tree, so there is no single wrapper element for "our plugin".
     * A body class on our screens is that hook: CSS scoped to `.lpl-scope`
     * applies across every surface we render, and nowhere else.
     *
     * @param string $classes Space-separated body classes.
     * @return string
     */
    public function add_scope_body_class($classes) {
        if (leanpl_is_our_admin_page()) {
            $classes .= ' lpl-scope';
        }
        return $classes;
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
        wp_enqueue_style('wp-color-picker');
    }

    public function common_frontend_enqueue() {
        if ( is_admin() && ! leanpl_is_our_admin_page() ) {
            return;
        }

        // Output global accent color as a stylesheet rule scoped to .lpl-player-wrap.
        // Weaker than Elementor widget CSS ({{WRAPPER}} .plyr = two classes) and
        // weaker than per-player inline styles, so the cascade works correctly.
        $global_color = leanpl_get_option( 'primary_color', '' );
        if ( $global_color !== '' ) {
            $css = sprintf(
                '.lpl-player-wrap { --plyr-color-main: %s; --plyr-range-fill-background: %s; --plyr-range-thumb-background: %s; }',
                sanitize_hex_color( $global_color ),
                sanitize_hex_color( $global_color ),
                sanitize_hex_color( $global_color )
            );
            wp_add_inline_style( 'leanpl-main', $css );
        }

        // Enqueue jQuery first, other plugins may remove it from the queue
        wp_enqueue_script('jquery');
        
        wp_localize_script('jquery', 'leanpl_params', [
            'version' => $this->version,
            'debugMode' => leanpl_is_test_mode() || leanpl_is_debug_mode(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'i18n' => [
                'unavailable' => __( 'This audio could not be loaded. May be the station  is offline.', 'vapfem' ),
                'dropped'     => __( 'The connection was lost. This can happen with live streams.', 'vapfem' ),
                'retry'       => __( 'Try again', 'vapfem' ),
            ],
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
                has_shortcode( $post->post_content, 'lean_player' ) ||
                has_shortcode( $post->post_content, 'lean_playlist' )
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
