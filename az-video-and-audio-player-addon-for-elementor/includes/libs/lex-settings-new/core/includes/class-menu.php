<?php
namespace Lex\Settings\V2\Services;

/**
 * Menu Service
 * 
 * Handles WordPress admin menu registration and page rendering.
 */

use Lex\Settings\V2\Settings;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Menu Service Class
 */
class Menu {
    
    /**
     * Settings instance
     * 
     * @var Settings
     */
    private $settings;
    
    /**
     * Constructor
     * 
     * @param Settings $settings Settings instance
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }
    
    /**
     * Register admin menu
     * 
     * @return void
     */
    public function register() {
        if (!$this->settings->getConfig('register_menu')) {
            return;
        }
        
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_head', [$this, 'removeThirdPartyNotices']);
    }
    
    /**
     * Register WordPress admin menu
     * 
     * @return void
     */
    public function registerAdminMenu() {
        add_menu_page(
            $this->settings->getConfig('page_title'),
            $this->settings->getConfig('menu_title'),
            $this->settings->getConfig('capability'),
            $this->settings->getConfig('menu_slug'),
            [$this, 'renderPage'],
            $this->settings->getConfig('icon'),
            $this->settings->getConfig('position')
        );
    }
    
    /**
     * Render settings page
     * 
     * @return void Outputs HTML directly
     */
    public function renderPage() {
        // Check permissions
        if (!current_user_can($this->settings->getConfig('capability'))) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'lex-settings'));
        }
        
        // Wrap in WordPress admin wrapper
        echo '<div class="wrap">';
        
        // Include settings page template
        $framework_path = $this->settings->getConfig('framework_path');
        $settings_page_path = $framework_path . '/partials/settings-page.php';
        
        if (file_exists($settings_page_path)) {
            // Make $settings available to template
            $settings = $this->settings;
            include $settings_page_path;
        } else {
            printf('<p>%s</p>', __('Settings page template not found.', 'lex-settings'));
        }
        
        echo '</div>';
    }
    
    /**
     * Check if we're on our admin page
     * 
     * @return bool True if on settings page
     */
    public function isOurPage() {
        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }
        
        $hook_suffix = 'toplevel_page_' . $this->settings->getConfig('menu_slug');
        return $screen->id === $hook_suffix;
    }
    
    /**
     * Remove third-party plugin notices on our settings page
     * 
     * @return void
     */
    public function removeThirdPartyNotices() {
        if ($this->isOurPage()) {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }
}

