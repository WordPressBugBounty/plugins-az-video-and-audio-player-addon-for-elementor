<?php
namespace VAPFEM;

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
     * Render guide page
     */
    public function render_guide_page() {
        wp_enqueue_style('vapfem-admin');
        wp_enqueue_script('vapfem-admin');
        ?>
        <div class="wrap vapfem-admin">
            <div class="vapfem-heading-1"><?php echo esc_html__('Video & Audio Player Guide', 'vapfem'); ?></div>

            <!-- Main Content Area -->
            <div class="vapfem-layout--two-column">
                <!-- Left Content -->
                <div class="vapfem-layout__main">
                    <!-- Tab Navigation -->
                    <nav class="nav-tab-wrapper">
                        <a href="#video-player" class="nav-tab nav-tab-active" data-tab="video-player"><?php echo esc_html__('📺 Video Player', 'vapfem'); ?></a>
                        <a href="#audio-player" class="nav-tab" data-tab="audio-player"><?php echo esc_html__('🎵 Audio Player', 'vapfem'); ?></a>
                        <a href="#all-options" class="nav-tab" data-tab="all-options"><?php echo esc_html__('📋 All Shortcode Options', 'vapfem'); ?></a>
                        <a href="#elementor-integration" class="nav-tab" data-tab="elementor-integration"><?php echo esc_html__('🎨 Elementor Integration', 'vapfem'); ?></a>
                    </nav>

                    <?php $this->render_tab_content(); ?>
                </div>

                <!-- Right Sidebar -->
                <?php $this->render_sidebar(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render hire me page
     */
    public function render_hire_me_page() {
        wp_enqueue_style('vapfem-admin');
        wp_enqueue_script('vapfem-admin');

        include VAPFEM_DIR . '/includes/views/html-hire-page.php';
    }

    /**
     * Render all tab content (JavaScript handles switching)
     */
    private function render_tab_content() {
        // Load all tab contents for JavaScript-based switching
        include VAPFEM_DIR . '/includes/views/html-video-tab.php';
        include VAPFEM_DIR . '/includes/views/html-audio-tab.php';
        include VAPFEM_DIR . '/includes/views/html-available-options-tab.php';
        include VAPFEM_DIR . '/includes/views/html-elementor-tab.php';
    }

    /**
     * Render sidebar
     */
    private function render_sidebar() {
        include VAPFEM_DIR . '/includes/views/html-sidebar.php';
    }
}

// Initialize the settings page
Settings_Page::instance();