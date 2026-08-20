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
        // Body class is needed whether or not the framework owns the menu -
        // host plugins registering their own menu still render this page.
        add_filter('admin_body_class', [$this, 'addBodyClass']);

        if (!$this->settings->getConfig('register_menu')) {
            return;
        }

        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_head', [$this, 'removeThirdPartyNotices']);
    }

    /**
     * Tag the settings screen so layout CSS can target it by class instead of
     * matching every admin page whose body class contains "_page_".
     *
     * Checks the page_check_callback first (single source of truth, e.g.
     * leanpl_is_our_admin_page) so host plugins that register their own menu
     * don't need to list every slug in settings_pages. Falls back to the
     * settings_pages array for instances without a callback.
     *
     * @param string $classes Space-separated body classes.
     * @return string
     */
    public function addBodyClass($classes) {
        $pages   = (array) $this->settings->getConfig('settings_pages');
        $page    = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $callback = $this->settings->getConfig('page_check_callback');

        if ($page && is_callable($callback) && call_user_func($callback)) {
            $classes .= ' lex-settings-page';
        } elseif ($page && in_array($page, $pages, true)) {
            $classes .= ' lex-settings-page';
        }

        return $classes;
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
     * Build the prepared data for the nav header template.
     *
     * Lifts all data prep (tab splitting, brand info, logo markup) out of
     * header.php so an override template receives ready-to-render data and
     * owns markup only.
     *
     * @return array {
     *     @type array  $main_tabs      Tabs without 'dropdown' set.
     *     @type array  $dropdown_tabs  Tabs with 'dropdown' set.
     *     @type string $plugin_name    Page title.
     *     @type string $plugin_version Framework/plugin version.
     *     @type string $logo_html      Pre-built logo markup (SVG or dashicon span).
     *     @type string $dropdown_label Label for the dropdown toggle.
     * }
     */
    public function getNavHeaderArgs() {
        $tabs = $this->settings->getTabs();

        $main_tabs = [];
        $dropdown_tabs = [];

        foreach ($tabs as $tab_id => $tab_config) {
            if (!empty($tab_config['dropdown'])) {
                $dropdown_tabs[$tab_id] = $tab_config;
            } else {
                $main_tabs[$tab_id] = $tab_config;
            }
        }

        $logo = $this->settings->getConfig('logo');

        if (!empty($logo) && strpos($logo, '<svg') !== false) {
            $logo_html = $logo;
        } else {
            $dashicon_class = !empty($logo) ? $logo : 'dashicons-admin-settings';
            if (strpos($dashicon_class, 'dashicons') === false) {
                $dashicon_class = 'dashicons ' . $dashicon_class;
            }
            $logo_html = '<span class="' . esc_attr($dashicon_class) . '"></span>';
        }

        return [
            'main_tabs'      => $main_tabs,
            'dropdown_tabs'  => $dropdown_tabs,
            'plugin_name'    => $this->settings->getConfig('page_title') ?: __('Settings', 'lex-settings'),
            'plugin_version' => $this->settings->getConfig('version') ?: '1.0.0',
            'logo_html'      => $logo_html,
            'dropdown_label' => $this->settings->getConfig('dropdown_label') ?: __('More', 'lex-settings'),
        ];
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

