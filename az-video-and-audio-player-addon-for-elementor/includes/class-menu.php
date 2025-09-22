<?php
namespace VAPFEM;

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
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_filter('plugin_action_links_' . plugin_basename(VAPFEM_DIR . '/plugin-main.php'), array($this, 'add_plugin_action_links'));
        add_action('admin_head', function () {
            if ($this->is_our_admin_page()) {
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
            }
        });
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            esc_html__('Video & Audio Player', 'vapfem'),
            esc_html__('Video & Audio Player', 'vapfem'),
            'manage_options',
            'vapfem-guide',
            array($this, 'guide_page'),
            'dashicons-video-alt3',
            30
        );

        // Submenu - Hire Me
        add_submenu_page(
            'vapfem-guide',
            esc_html__('Hire Me', 'vapfem'),
            esc_html__('Hire Me', 'vapfem'),
            'manage_options',
            'vapfem-hire-me',
            array($this, 'hire_me_page')
        );
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $guide_link = '<a href="' . esc_url(admin_url('admin.php?page=vapfem-guide')) . '">' . esc_html__('Go to Menu', 'vapfem') . '</a>';
        $links[] = $guide_link;

        return $links;
    }

    /**
     * Check if we're on our plugin's admin pages
     */
    private function is_our_admin_page() {
        $current_screen = get_current_screen();

        if (!$current_screen) {
            return false;
        }

        // Check if we're on our plugin's pages
        $our_pages = array(
            'vapfem-guide',
            'vapfem-hire-me',
        );

        return in_array($current_screen->id, $our_pages) ||
               (isset($_GET['page']) && in_array($_GET['page'], $our_pages));
    }

    /**
     * Guide page callback - delegate to Settings Page
     */
    public function guide_page() {
        Settings_Page::instance()->render_guide_page();
    }

    /**
     * Hire Me page callback - delegate to Settings Page
     */
    public function hire_me_page() {
        Settings_Page::instance()->render_hire_me_page();
    }
}

// Initialize the menu
Menu::instance();