<?php
namespace LeanPL\Admin;
use LeanPL\Settings_Page;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Menu Class
 *
 * Handles menu registration for the plugin
 */
class Menu {

    /**
     * Single instance
     */
    private static $_instance = null;

    /**
     * Get instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Lex Settings instance
     * 
     * @var \Lex\Settings\V2\Settings|null
     */
    private $lex_settings = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize
     */
    public function init() {
        // Don't initialize Lex Settings here - wait for admin_init
        // so translations are loaded
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_lex_settings')); // Initialize on admin_init
        add_action('admin_enqueue_scripts', array($this, 'enqueue_menu_hash_script'));
        add_filter('submenu_file', array($this, 'submenu_file_cb'), 10, 2);
        add_filter('parent_file', array($this, 'parent_file_cb'));
        add_filter('plugin_action_links_' . plugin_basename(LEANPL_DIR . '/plugin-main.php'), array($this, 'add_plugin_action_links'));
        add_action('admin_head', function () {
            // Don't remove notices on Freemius pages - they need to show opt-in notices
            $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            if ($this->is_our_admin_page() && $page !== 'lean_player-license') {
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
            }
        });
    }

    /**
     * Initialize Lex Settings Framework
     * Called on admin_init hook so translations are loaded
     */
    public function init_lex_settings() {
        // Only initialize once
        if ($this->lex_settings !== null) {
            return;
        }
        
        $framework_file = LEANPL_DIR . '/includes/libs/lex-settings-new/core/Settings.php';
        $framework_dir = dirname($framework_file); // Points to core/ directory
        
        if (!file_exists($framework_file)) {
            return; // Framework not found
        }
        
        // Check if already loaded (by another plugin using v2)
        if (!class_exists('\Lex\Settings\V2\Settings')) {
            require_once $framework_file;
        }
        
        // Load default settings (empty array for now since no fields yet)
        $default_settings = array();
        
        // Initialize framework
        $this->lex_settings = new \Lex\Settings\V2\Settings([
            'framework_path' => $framework_dir,    // CRITICAL: Your plugin's framework directory
            'register_menu' => false,              // Don't register menu (we have our own)
            'menu_slug' => 'lean_player-settings',  // Settings page slug
            'instance_id' => 'leanpl',             // Unique identifier
            'page_title' => esc_html__('Lean Player', 'vapfem'),
            'menu_title' => esc_html__('Video & Audio Player', 'vapfem'),
            'capability' => 'manage_options',
            'logo' => 'dashicons dashicons-video-alt3',      // Dashicon for header logo
            'option_key' => 'leanpl_settings',     // For future settings
            'defaults' => $default_settings,       // Default settings array
            'version' => LEANPL_VERSION,
            'dropdown_label' => esc_html__('Shortcodes', 'vapfem'),
            // Use callback instead of allowed_pages - leverages existing leanpl_is_our_admin_page() function
            'page_check_callback' => 'leanpl_is_our_admin_page',
        ]);
        
        // Register tabs after framework is initialized
        $this->register_tabs();
    }

    /**
     * Register tabs for Lex Settings
     */
    public function register_tabs() {
        if (!$this->lex_settings) {
            return;
        }
        
        // Register Player Behavior tab (main tab, not in dropdown)
        $this->lex_settings->registerTab([
            'id'       => 'settings',
            'label'    => esc_html__('Player Defaults', 'vapfem'),
            'icon'     => 'dashicons dashicons-performance',
            'order'    => 5,
        ]);

        // Register Playlist tab (main tab, not in dropdown)
        $this->lex_settings->registerTab([
            'id'       => 'playlist',
            'label'    => esc_html__( 'Playlist', 'vapfem' ),
            'icon'     => 'dashicons dashicons-playlist-video',
            'order'    => 5.3,
        ]);

        // Register Styling tab (main tab, not in dropdown)
        $this->lex_settings->registerTab([
            'id'       => 'styling',
            'label'    => esc_html__('Styling', 'vapfem'),
            'icon'     => 'dashicons dashicons-admin-appearance',
            'order'    => 5.5,
        ]);

        // Register Import/Export tab (main tab, not in dropdown)
        $this->lex_settings->registerTab([
            'id'       => 'import-export',
            'label'    => esc_html__('Import/Export', 'vapfem'),
            'icon'     => 'dashicons dashicons-database',
            'order'    => 6,
        ]);

        // Register Quick Start tab (main tab, not in dropdown)
        $this->lex_settings->registerTab([
            'id'       => 'quick-start',
            'label'    => esc_html__('Quick Start', 'vapfem'),
            'icon'     => 'dashicons dashicons-lightbulb',
            'order'    => 7,
        ]);

        // Register the guide tabs as dropdown tabs (under "Legacy Shortcodes")
        $this->lex_settings->registerTab([
            'id' => 'video-player',
            'label' => esc_html__('Video Player Shortcode', 'vapfem'),
            'icon' => 'dashicons dashicons-video-alt3',
            'order' => 10,
            'dropdown' => true,
        ]);
        
        $this->lex_settings->registerTab([
            'id' => 'audio-player',
            'label' => esc_html__('Audio Player Shortcode', 'vapfem'),
            'icon' => 'dashicons dashicons-format-audio',
            'order' => 20,
            'dropdown' => true,
        ]);

        $this->lex_settings->registerTab([
            'id' => 'playlist-shortcode',
            'label' => esc_html__('Playlist Shortcode', 'vapfem'),
            'icon' => 'dashicons dashicons-playlist-video',
            'order' => 25,
            'dropdown' => true,
        ]);
        
        $this->lex_settings->registerTab([
            'id' => 'all-options',
            'label' => esc_html__('All Shortcode Options', 'vapfem'),
            'icon' => 'dashicons dashicons-list-view',
            'order' => 30,
            'dropdown' => true,
        ]);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Top-level menu - links to Quick Start
        add_menu_page(
            esc_html__('Lean Player', 'vapfem'),
            esc_html__('Lean Player', 'vapfem'),
            'manage_options',
            'lean_player-settings',
            array($this, 'lean_player_settings_page'),
            'dashicons-video-alt3',
            30
        );
        
        // Submenu - Quick Start (replaces parent menu item)
        add_submenu_page(
            'lean_player-settings',
            esc_html__('Quick Start', 'vapfem'),
            esc_html__('Quick Start', 'vapfem'),
            'manage_options',
            'lean_player-settings',
            array($this, 'lean_player_settings_page')
        );
        
        // Submenu - All Players
        add_submenu_page(
            'lean_player-settings',
            esc_html__('All Players', 'vapfem'),
            esc_html__('All Players', 'vapfem'),
            'edit_posts',
            'edit.php?post_type=lean_player'
        );
        
        // Submenu - Add New Player
        add_submenu_page(
            'lean_player-settings',
            esc_html__('Add New Player', 'vapfem'),
            esc_html__('Add New Player', 'vapfem'),
            'edit_posts',
            'edit.php?post_type=lean_player&open_modal=1'
        );

        // Submenu - Categories
        add_submenu_page(
            'lean_player-settings',
            esc_html__('Categories', 'vapfem'),
            esc_html__('Categories', 'vapfem'),
            'manage_categories',
            'edit-tags.php?taxonomy=lean_player_cat&post_type=lean_player'
        );

        // Submenu - Playlists (conditional on playlist feature enabled)
        if ( leanpl_get_option( 'playlist.enabled', true ) ) {
            add_submenu_page(
                'lean_player-settings',
                esc_html__('Playlists', 'vapfem'),
                esc_html__('Playlists', 'vapfem'),
                'edit_posts',
                'edit.php?post_type=lean_playlist'
            );
        }

        // Submenu - Settings
        add_submenu_page(
            'lean_player-settings',
            esc_html__('Settings', 'vapfem'),
            esc_html__('Settings', 'vapfem'),
            'manage_options',
            'lean_player-settings-tab',
            array($this, 'lean_player_settings_page')
        );

        // Submenu - Upgrade Now

        if( !leanpl_is_pro_active() ) {
            add_submenu_page(
                'lean_player-settings',
                esc_html__('Upgrade to Premium', 'vapfem'),
                esc_html__('Upgrade to Premium', 'vapfem'),
                'manage_options',
                leanpl_get_upgrade_url(array('utm_medium' => 'menu'))
            );
        }
        
        // Submenu - Hire Me
        // @future: Enable this if we feel like it's necessary
        // add_submenu_page(
        //     'lean_player-settings',
        //     esc_html__('Hire Me', 'vapfem'),
        //     esc_html__('Hire Me', 'vapfem'),
        //     'manage_options',
        //     'lean_player-hire-me',
        //     array($this, 'hire_me_page')
        // );
    }

    /**
     * Manage submenu file highlighting and remove duplicates
     */
    public function submenu_file_cb($submenu_file, $parent_file) {
        global $submenu, $post_type, $pagenow;
        
        // Remove duplicate submenu items
        if (isset($submenu['lean_player-settings'])) {
            $seen = array();
            foreach ($submenu['lean_player-settings'] as $key => $item) {
                $slug = $item[2];
                
                if (isset($seen[$slug])) {
                    unset($submenu['lean_player-settings'][$key]);
                } else {
                    $seen[$slug] = true;
                }
            }
        }
        
        // When editing a lean_player post, highlight "All Players" submenu
        if ( $post_type === 'lean_player' && $pagenow === 'post.php' ) {
            $submenu_file = 'edit.php?post_type=lean_player';
        }

        // When adding a new lean_player post, highlight "Add New Player" submenu
        if ( $post_type === 'lean_player' && $pagenow === 'post-new.php' ) {
            $submenu_file = 'edit.php?post_type=lean_player&open_modal=1';
        }

        // When editing a lean_playlist post, highlight "Playlists" submenu
        if ( $post_type === 'lean_playlist' && $pagenow === 'post.php' ) {
            $submenu_file = 'edit.php?post_type=lean_playlist';
        }

        // Highlight Categories submenu when on category taxonomy page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET param for menu highlight only
        if ( $pagenow === 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'lean_player_cat' ) {
            $submenu_file = 'edit-tags.php?taxonomy=lean_player_cat&post_type=lean_player';
        }

        // Highlight Playlists submenu when on playlist list or add new page
        if ( $post_type === 'lean_playlist' && in_array( $pagenow, [ 'edit.php', 'post-new.php' ], true ) ) {
            $submenu_file = 'edit.php?post_type=lean_playlist';
        }

        return $submenu_file;
    }

    /**
     * Highlight parent menu for lean_player post type and playlist taxonomy
     * Fixes menu highlighting when editing a lean_player post or viewing playlists
     */
    public function parent_file_cb($parent_file) {
        global $post_type, $pagenow;

        // Highlight parent menu for our post types
        if ( in_array( $post_type, [ 'lean_player', 'lean_playlist' ], true ) ) {
            $parent_file = 'lean_player-settings';
        }

        // Highlight parent menu for category taxonomy page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET param for menu highlight only
        if ( $pagenow === 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'lean_player_cat' ) {
            $parent_file = 'lean_player-settings';
        }

        return $parent_file;
    }

    /**
     * Enqueue script to add hash fragment to menu links and handle highlight
     */
    public function enqueue_menu_hash_script() {
        wp_add_inline_script( 'jquery', <<<'JS'
            jQuery(document).ready(function($) {
                // Add hash fragment to Quick Start link
                $('#toplevel_page_lean_player-settings a[href="admin.php?page=lean_player-settings"]').attr('href', 'admin.php?page=lean_player-settings#quick-start');

                // Redirect Settings link to main page with hash
                $('#toplevel_page_lean_player-settings a[href="admin.php?page=lean_player-settings-tab"]').attr('href', 'admin.php?page=lean_player-settings#settings');

                // Update menu highlight based on hash
                function updateMenuHighlight() {
                    var hash = window.location.hash;
                    var currentPage = window.location.search;

                    if (currentPage.indexOf('page=lean_player-settings') === -1) {
                        return;
                    }

                    $('#toplevel_page_lean_player-settings .wp-submenu li').removeClass('current');
                    $('#toplevel_page_lean_player-settings .wp-submenu a').removeClass('current').removeAttr('aria-current');

                    if (hash === '#quick-start' || hash === '' || hash === '#') {
                        $('#toplevel_page_lean_player-settings a[href="admin.php?page=lean_player-settings#quick-start"]').addClass('current').attr('aria-current', 'page').closest('li').addClass('current');
                    } else if (hash === '#settings') {
                        $('#toplevel_page_lean_player-settings a[href="admin.php?page=lean_player-settings#settings"]').addClass('current').attr('aria-current', 'page').closest('li').addClass('current');
                    }
                }

                updateMenuHighlight();
                $(window).on('hashchange', updateMenuHighlight);

                // Open upgrade link in new tab
                $('.wp-submenu a[href*="leanplugins.com"]').attr('target', '_blank');
            });
JS
        );
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=lean_player-settings#quick-start')) . '">' . esc_html__('Settings', 'vapfem') . '</a>';
        $links[] = $settings_link;
        return $links;
    }

    /**
     * Check if we're on our plugin's admin pages
     * Uses single source of truth function
     */
    private function is_our_admin_page() {
        return leanpl_is_our_admin_page();
    }

    /**
     * Hire Me page callback - delegate to Settings Page
     */
    public function hire_me_page() {
        Settings_Page::instance()->render_hire_me_page();
    }

    /**
     * Lean Player Settings page callback - renders Lex Settings
     */
    public function lean_player_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'vapfem'));
        }
        
        // Render Lex Settings framework
        if ($this->lex_settings) {
            $this->lex_settings->menu->renderPage();
        } else {
            // Fallback if framework not loaded
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Settings', 'vapfem') . '</h1>';
            echo '<p>' . esc_html__('Settings framework not available.', 'vapfem') . '</p>';
            echo '</div>';
        }
    }
}

// Initialize the menu
Menu::instance();
