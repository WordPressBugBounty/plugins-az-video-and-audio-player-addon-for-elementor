<?php
namespace LeanPL;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Page Class
 *
 * Handles routing and rendering of admin pages
 */
class Settings_Page {

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
        // Any initialization if needed
    }

    /**
     * Render hire me page
     */
    public function render_hire_me_page() {
        include LEANPL_DIR . '/includes/views/html-hire-page.php';
    }
}

// Initialize the settings page
Settings_Page::instance();
