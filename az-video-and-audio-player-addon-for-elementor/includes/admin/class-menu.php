<?php
namespace LeanPL\Admin;
use LeanPL\Settings_Page;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load the Lex Settings framework at file-load time (before 'admin_menu'
// fires) so the framework can self-register the lex-settings-demo demo
// menu on its own admin_menu hook. Class file loading is safe before
// translations are ready; the Settings class only resolves translateable
// strings inside its constructor, which we still defer to admin_init
// below in init_lex_settings() for that reason.
$leanpl_framework_file = LEANPL_DIR . '/includes/libs/lex-settings-new/core/Settings.php';
if (file_exists($leanpl_framework_file) && !class_exists('\Lex\Settings\V2\Settings')) {
    require_once $leanpl_framework_file;
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
        // Runs late so Freemius' own submenu rebuild can't restore the parent label.
        add_action('admin_menu', array($this, 'rename_parent_submenu_item'), 999);
        add_action('admin_init', array($this, 'init_lex_settings')); // Initialize on admin_init
        add_action('admin_enqueue_scripts', array($this, 'enqueue_menu_hash_script'));
        add_action('current_screen', array($this, 'set_url_only_page_title'));
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

        // Core upgrade-modal partial now defaults to '#'; supply our real link.
        add_filter('lex_settings/upgrade_cta', array($this, 'filter_upgrade_cta'), 10, 2);
    }

    /**
     * Set the upgrade-modal button URL for our settings instance.
     * Other instances pass through untouched. Keeps the existing external
     * marketing URL + new-tab behavior.
     *
     * @param array  $cta         url/target/label for the CTA button.
     * @param string $instance_id The lex-settings instance the modal belongs to.
     * @return array
     */
    public function filter_upgrade_cta($cta, $instance_id) {
        if ('leanpl' !== $instance_id) {
            return $cta;
        }

        $cta['url'] = leanpl_get_upgrade_url(array('utm_medium' => 'modal'));
        // target stays '_blank' (external marketing page).
        return $cta;
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
        
        // Loaded at file-load time of class-menu.php so the framework can
        // self-register the demo menu; the class_exists guard keeps this safe
        // for hosts that load this file late.
        if (!class_exists('\Lex\Settings\V2\Settings')) {
            require_once $framework_file;
        }
        
        // Load default settings (empty array for now since no fields yet)
        $default_settings = array();
        
        // Initialize framework
        $this->lex_settings = new \Lex\Settings\V2\Settings([
            'framework_path' => $framework_dir,    // CRITICAL: Your plugin's framework directory
            'template_override_path' => LEANPL_DIR . '/includes/admin-new/partials/shared', // Nav header lives here, not in the vendored framework config/
            'register_menu' => false,              // Don't register menu (we have our own)
            'menu_slug' => 'lean-player',  // Top-level menu slug (matches add_menu_page() below)
            // Slugs that actually render the settings screen. We register the
            // menu ourselves, and Settings lives on its own slug now. Includes
            // the three admin-new screens so Menu::addBodyClass() tags them
            // with lex-settings-page and the framework's #wpcontent padding-left,
            // page background, and .wrap margin rules fire on them too.
            'settings_pages' => array(
                'lean-player-settings',
                'lean_player-all-players-new',
                'lean-player-playlist',
                'lean-player-edit',
                'lean-playlist-edit',
            ),
            'instance_id' => 'leanpl',             // Unique identifier
            'page_title' => esc_html__('Lean Player', 'vapfem'),
            'menu_title' => esc_html__('Video & Audio Player', 'vapfem'),
            'capability' => 'manage_options',
            'logo' => 'dashicons dashicons-video-alt3',      // Dashicon for header logo
            'option_key' => 'leanpl_settings',     // For future settings
            'defaults' => $default_settings,       // Default settings array
            'version' => leanpl_get_version(),
            'dropdown_label' => esc_html__('Shortcodes', 'vapfem'),
            // Use callback instead of allowed_pages - leverages existing leanpl_is_our_admin_page() function
            'page_check_callback' => 'leanpl_is_our_admin_page',
            'enable_demo' => false,
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
        
        // Register Settings tab (main tab, not in dropdown). Holds Behavior, Controls,
        // Video-Only, Playlist, Appearance, and Import/Export as vtabs (see config/tabs/settings.php).
        $this->lex_settings->registerTab([
            'id'       => 'settings',
            'label'    => esc_html__('Settings', 'vapfem'),
            'icon'     => 'dashicons dashicons-performance',
            'order'    => 5,
        ]);

        // Quick Start tab hidden for now (registerTab call removed). Content still
        // lives in config/tabs/quick-start.php - re-add the registerTab() call below
        // to bring it back.
        // $this->lex_settings->registerTab([
        //     'id'       => 'quick-start',
        //     'label'    => esc_html__('Quick Start', 'vapfem'),
        //     'icon'     => 'dashicons dashicons-lightbulb',
        //     'order'    => 7,
        // ]);

        // Shortcode guide tabs (dropdown, under "Legacy Shortcodes") hidden for now.
        // Content still lives in config/tabs/{video-player,audio-player,playlist-shortcode,all-options}.php
        // - re-add the registerTab() calls below to bring them back.
        // $this->lex_settings->registerTab([
        //     'id' => 'video-player',
        //     'label' => esc_html__('Video Player Shortcode', 'vapfem'),
        //     'icon' => 'dashicons dashicons-video-alt3',
        //     'order' => 10,
        //     'dropdown' => true,
        // ]);
        //
        // $this->lex_settings->registerTab([
        //     'id' => 'audio-player',
        //     'label' => esc_html__('Audio Player Shortcode', 'vapfem'),
        //     'icon' => 'dashicons dashicons-format-audio',
        //     'order' => 20,
        //     'dropdown' => true,
        // ]);
        //
        // $this->lex_settings->registerTab([
        //     'id' => 'playlist-shortcode',
        //     'label' => esc_html__('Playlist Shortcode', 'vapfem'),
        //     'icon' => 'dashicons dashicons-playlist-video',
        //     'order' => 25,
        //     'dropdown' => true,
        // ]);
        //
        // $this->lex_settings->registerTab([
        //     'id' => 'all-options',
        //     'label' => esc_html__('All Shortcode Options', 'vapfem'),
        //     'icon' => 'dashicons dashicons-list-view',
        //     'order' => 30,
        //     'dropdown' => true,
        // ]);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Top-level menu - lands on Media Players (the players list screen).
        // Slug is `lean-player` (hyphen-style) and the edit_posts cap matches
        // Freemius' `menu.capability` in includes/freemius-init.php, so the
        // parent menu is gated at `edit_posts` end-to-end. Editors can open it
        // without hitting the WordPress "Sorry, you are not allowed..." gate.
        add_menu_page(
            esc_html__('Lean Player', 'vapfem'),
            esc_html__('Lean Player', 'vapfem'),
            'edit_posts',
            'lean-player',
            array($this, 'all_players_new_page'),
            'dashicons-video-alt3',
            30
        );

        
        // Submenu - Quick Start (replaces parent menu item)
        // add_submenu_page(
        //     'lean-player',
        //     esc_html__('Quick Start', 'vapfem'),
        //     esc_html__('Quick Start', 'vapfem'),
        //     'manage_options',
        //     'lean-player',
        //     array($this, 'lean_player_settings_page')
        // );
        
        // Submenu - All Players
        // add_submenu_page(
        //     'lean-player',
        //     esc_html__('All Players', 'vapfem'),
        //     esc_html__('All Players', 'vapfem'),
        //     'edit_posts',
        //     'edit.php?post_type=lean_player'
        // );
        
        // Submenu - All Players New (custom Tailwind markup, not the native list table)
        //
        // URL-only page: the empty parent slug is deliberate, see the note above
        // register_url_only_page() for why remove_submenu_page() can't be used here.
        $this->register_url_only_page(
            'lean_player-all-players-new',
            array($this, 'all_players_new_page')
        );

        // Submenu - Add New Player
        // add_submenu_page(
        //     'lean-player',
        //     esc_html__('Add New Player', 'vapfem'),
        //     esc_html__('Add New Player', 'vapfem'),
        //     'edit_posts',
        //     'edit.php?post_type=lean_player&open_modal=1'
        // );

        // Submenu - Edit Player (custom Tailwind markup, visual-phase port of post.php's metabox)
        $this->register_url_only_page(
            'lean-player-edit',
            array($this, 'player_edit_new_page')
        );

        // Submenu - Edit Playlist (POC: preview + inspector, no save endpoint yet)
        $this->register_url_only_page(
            'lean-playlist-edit',
            array($this, 'playlist_edit_new_page')
        );

        // Submenu - Categories
        // add_submenu_page(
        //     'lean-player',
        //     esc_html__('Categories', 'vapfem'),
        //     esc_html__('Categories', 'vapfem'),
        //     'manage_categories',
        //     'edit-tags.php?taxonomy=lean_player_cat&post_type=lean_player'
        // );

        // Submenu - Playlists (conditional on playlist feature enabled)
        if ( leanpl_get_option( 'playlist.enabled', true ) ) {
            // add_submenu_page(
            //     'lean-player',
            //     esc_html__('Playlists', 'vapfem'),
            //     esc_html__('Playlists', 'vapfem'),
            //     'edit_posts',
            //     'edit.php?post_type=lean_playlist'
            // );

            // Submenu - Playlist (custom Tailwind markup, not the native list table)
            add_submenu_page(
                'lean-player',
                esc_html__('Playlists', 'vapfem'),
                esc_html__('Playlists', 'vapfem'),
                'edit_posts',
                'lean-player-playlist',
                array($this, 'all_playlists_new_page')
            );
        } else {
            // Feature disabled: no menu row, but the URL stays reachable
            // (bookmarks, deep links) - same URL-only page pattern as
            // lean-player-edit/lean-playlist-edit above, so a direct visit
            // still resolves to all_playlists_new_page() and gets the
            // "feature is disabled" notice (see
            // Settings_Page::render_all_playlists_new_page()) instead of
            // WP's generic "not allowed" wp_die() for an unregistered slug.
            $this->register_url_only_page(
                'lean-player-playlist',
                array($this, 'all_playlists_new_page')
            );
        }

        // Submenu - Settings
        add_submenu_page(
            'lean-player',
            esc_html__('Settings', 'vapfem'),
            esc_html__('Settings', 'vapfem'),
            'manage_options',
            'lean-player-settings',
            array($this, 'lean_player_settings_page')
        );

        // Submenu - Upgrade is supplied by Freemius; adding our own duplicates it.
    }

    /**
     * Pages reachable by URL only, never shown as menu items, keyed by slug.
     *
     * 'title' lives here rather than at the add_submenu_page() call because a
     * page with no parent has no menu row for WP to read a title back out of -
     * set_url_only_page_title() replays it from this map instead. 'submenu' is
     * the menu row that should light up while the page is open, for the same
     * reason: with no row of its own, each URL-only page borrows the one for
     * the list its posts live in. Editing a playlist highlights Playlists,
     * not Media Players.
     *
     * @return array<string,array{title:string,submenu:string}>
     */
    public static function url_only_pages() {
        return array(
            'lean_player-all-players-new' => array(
                'title'   => __('Media Players', 'vapfem'),
                'submenu' => 'lean-player',
            ),
            'lean-player-edit' => array(
                'title'   => __('Edit Player', 'vapfem'),
                'submenu' => 'lean-player',
            ),
            'lean-playlist-edit' => array(
                'title'   => __('Edit Playlist', 'vapfem'),
                'submenu' => 'lean-player-playlist',
            ),
            // Only actually URL-only (no menu row) when playlist.enabled is
            // false - see the else branch above. Harmless to always list
            // here: when the
            // feature is on, this slug has a real submenu row of its own, so
            // get_admin_page_title() resolves the title before
            // set_url_only_page_title() ever needs this entry, and
            // submenu_file_cb()/parent_file_cb() just point back at the
            // slug's own (already correct) submenu/parent.
            'lean-player-playlist' => array(
                'title'   => __('Playlists', 'vapfem'),
                'submenu' => 'lean-player-playlist',
            ),
        );
    }

    /**
     * Slugs of pages reachable by URL only, never shown as menu items.
     *
     * @return string[]
     */
    public static function url_only_page_slugs() {
        return array_keys(self::url_only_pages());
    }

    /**
     * Give URL-only pages the screen title their missing menu row would have carried.
     *
     * get_admin_page_title() resolves the title by finding the page in $menu or
     * $submenu, so a parentless page leaves the global $title null - which
     * admin-header.php then hands to strip_tags(), emitting a PHP 8 deprecation
     * and rendering an admin page with no <title> and no <h1> context. Runs on
     * current_screen because that fires after $plugin_page is known and before
     * admin-header.php reads $title.
     */
    public function set_url_only_page_title() {
        global $title, $plugin_page;

        if (!empty($title)) {
            return;
        }

        $url_only = self::url_only_pages();
        if (isset($url_only[$plugin_page])) {
            $title = $url_only[$plugin_page]['title'];
        }
    }

    /**
     * Register an admin page that is reachable by URL but never listed in a menu.
     *
     * The empty parent slug is the whole point. Registering under 'lean-player'
     * and then calling remove_submenu_page() looks equivalent but makes the page
     * return "Sorry, you are not allowed to access this page": registration
     * stores the hookname `lean-player_page_{slug}` in $_registered_pages, while
     * on the next request user_can_access_admin_page() re-derives the parent by
     * scanning $submenu - where remove_submenu_page() just deleted our entry -
     * comes up empty, and looks for `admin_page_{slug}` instead. With an empty
     * parent both sides agree on `admin_page_{slug}`, and no menu item is drawn
     * because nothing renders $submenu['']. parent_file_cb() puts the top-level
     * Lean Player item back in the highlighted state these pages lose.
     *
     * @param string   $slug     Page slug, as it appears in ?page=. Must be a key of url_only_pages().
     * @param callable $callback Renderer.
     */
    private function register_url_only_page($slug, $callback) {
        global $submenu;

        $url_only = self::url_only_pages();
        $title  = isset($url_only[$slug]) ? $url_only[$slug]['title'] : '';

        add_submenu_page(
            '',
            $title,
            $title,
            'edit_posts',
            $slug,
            $callback
        );

        // add_submenu_page() files the row under $submenu[''], which nothing
        // renders but get_admin_page_parent() still finds - and finding it makes
        // that function overwrite the global $parent_file with '' on every call,
        // including the one menu-header.php makes *after* the 'parent_file'
        // filter and before it draws the menu. That wipes parent_file_cb()'s
        // highlight. With no row to find, get_admin_page_parent() returns ''
        // without touching a non-empty $parent_file, so the highlight survives
        // and the $_registered_pages hookname stays 'admin_page_{slug}'.
        unset($submenu['']);
    }

    /**
     * add_menu_page() auto-registers a submenu duplicating the parent, labelled
     * with the top-level menu title. Relabel that entry "Media Players" - it points
     * at the players list screen, not a second "Lean Player" page.
     */
    public function rename_parent_submenu_item() {
        global $submenu;

        if (empty($submenu['lean-player'])) {
            return;
        }

        foreach ($submenu['lean-player'] as $key => $item) {
            if (isset($item[2]) && 'lean-player' === $item[2]) {
                $submenu['lean-player'][$key][0] = esc_html__('Media Players', 'vapfem');
                break;
            }
        }
    }

    /**
     * Manage submenu file highlighting and remove duplicates
     */
    public function submenu_file_cb($submenu_file, $parent_file) {
        global $submenu, $post_type, $pagenow;

        // URL-only pages have no menu row of their own (see register_url_only_page()),
        // so each borrows the row for the list its posts live in - the 'submenu'
        // key in url_only_pages(). parent_file_cb() opens the Lean Player menu,
        // this marks the item inside it.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET param for menu highlight only
        $page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        $url_only = self::url_only_pages();
        if ( isset( $url_only[ $page ] ) ) {
            $submenu_file = $url_only[ $page ]['submenu'];
        }

        // Remove duplicate submenu items
        if (isset($submenu['lean-player'])) {
            $seen = array();
            foreach ($submenu['lean-player'] as $key => $item) {
                $slug = $item[2];
                
                if (isset($seen[$slug])) {
                    unset($submenu['lean-player'][$key]);
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

        // Freemius admin.php?page=lean-player-account
        if ( $parent_file === 'lean-player-account' ){
            $submenu_file = 'admin.php?page=lean-player-account';
        }

        return $submenu_file;
    }

    /**
     * Highlight parent menu for lean_player post type and playlist taxonomy
     * Fixes menu highlighting when editing a lean_player post or viewing playlists
     */
    public function parent_file_cb($parent_file) {
        global $post_type, $pagenow;

        // URL-only pages register with an empty parent (see register_url_only_page()),
        // so WP resolves no parent to highlight. Point them at the top-level item.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET param for menu highlight only
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( in_array( $page, self::url_only_page_slugs(), true ) ) {
            $parent_file = 'lean-player';
        }

        // Highlight parent menu for our post types
        if ( in_array( $post_type, [ 'lean_player', 'lean_playlist' ], true ) ) {
            $parent_file = 'lean-player';
        }

        // Highlight parent menu for category taxonomy page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET param for menu highlight only
        if ( $pagenow === 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'lean_player_cat' ) {
            $parent_file = 'lean-player';
        }

        return $parent_file;
    }

    /**
     * Enqueue script to add hash fragment to menu links and handle highlight
     */
    public function enqueue_menu_hash_script() {
        wp_add_inline_script( 'jquery', <<<'JS'
            jQuery(document).ready(function($) {
                // Settings lives on its own slug (lean-player-settings) but the
                // Lex Settings framework opens on the Settings vertical tab, so
                // append the #settings hash fragment to the submenu link.
                $('#toplevel_page_lean-player a[href="admin.php?page=lean-player-settings"]').attr('href', 'admin.php?page=lean-player-settings#settings');

                // Open upgrade link in new tab
               // $('.wp-submenu a[href*="leanplugins.com"]').attr('target', '_blank');
            });
JS
        );
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=lean-player')) . '">' . esc_html__('Settings', 'vapfem') . '</a>';
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
     * All Players (New) page callback - delegate to Settings Page
     */
    public function all_players_new_page() {
        Settings_Page::instance()->render_all_players_new_page();
    }

    /**
     * All Playlists (New) page callback - delegate to Settings Page
     */
    public function all_playlists_new_page() {
        Settings_Page::instance()->render_all_playlists_new_page();
    }

    /**
     * Edit Player (New) page callback - delegate to Settings Page
     */
    public function player_edit_new_page() {
        Settings_Page::instance()->render_player_edit_new_page();
    }

    /**
     * Edit Playlist page callback - delegate to Settings Page
     */
    public function playlist_edit_new_page() {
        Settings_Page::instance()->render_playlist_edit_new_page();
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
