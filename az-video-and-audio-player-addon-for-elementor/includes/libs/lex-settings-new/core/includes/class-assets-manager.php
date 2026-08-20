<?php
namespace Lex\Settings\V2\Services;

/**
 * Assets Manager Service
 * 
 * Handles registration and enqueuing of all CSS and JavaScript assets.
 */

use Lex\Settings\V2\Settings;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets Manager Service Class
 */
class AssetManager {
    
    /**
     * Settings instance
     * 
     * @var Settings
     */
    private $settings;
    
    /**
     * Registered inline CSS styles
     * 
     * @var string
     */
    private $inline_styles = '';
    
    /**
     * Constructor
     * 
     * @param Settings $settings Settings instance
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
        $this->registerHooks();
    }
    
    /**
     * Register WordPress hooks
     * 
     * @return void
     */
    private function registerHooks() {
        add_action('admin_footer', [$this, 'renderUpgradeModal']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function renderUpgradeModal() {
        $page_check_callback = $this->settings->getConfig('page_check_callback');


        if(is_callable($page_check_callback) && call_user_func($page_check_callback)){
            $upgrade_modal_path = $this->settings->getConfig('framework_path') . '/partials/upgrade-modal.php';
            if (file_exists($upgrade_modal_path)) {
                include $upgrade_modal_path;
            }
        }
    }
    
    /**
     * Enqueue all assets
     * 
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueueAssets($hook) {
        // Check if we're on the settings page
        $hook_suffix = 'toplevel_page_' . $this->settings->getConfig('menu_slug');
        
        // Get current page identifiers
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $page_slug = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        
        // Check main settings page first
        if ($hook === $hook_suffix) {
            $this->enqueueStyles();
            $this->enqueueScripts();
            $this->localizeScripts();
            return;
        }
        
        // Check if custom callback is provided (takes precedence over allowed_pages)
        $page_check_callback = $this->settings->getConfig('page_check_callback');
        
        if (is_callable($page_check_callback)) {
            // Use custom callback for page checking
            if (call_user_func($page_check_callback, $hook)) {
                $this->enqueueStyles();
                $this->enqueueScripts();
                $this->localizeScripts();
                return;
            }
            // Callback returned false, don't load assets
            return;
        }
        
        // Fallback to default allowed_pages logic if no callback
        $allowed_pages = $this->settings->getConfig('allowed_pages');
        
        if (empty($allowed_pages) || !is_array($allowed_pages)) {
            return;
        }
        
        // Check each allowed page pattern
        foreach ($allowed_pages as $allowed) {
            if ($this->matchesPage($allowed, $hook, $screen_id, $page_slug)) {
                $this->enqueueStyles();
                $this->enqueueScripts();
                $this->localizeScripts();
                return;
            }
        }
    }
    
    /**
     * Check if a page matches the allowed pattern
     * 
     * Supports:
     * - Exact hook suffix match: 'toplevel_page_my-page'
     * - Exact screen ID match: 'toplevel_page_my-page'
     * - Exact page slug match: 'my-page'
     * - Wildcard pattern: 'my-plugin-*' (matches any page starting with 'my-plugin-')
     * 
     * @param string $pattern Pattern to match against
     * @param string $hook Hook suffix
     * @param string $screen_id Screen ID
     * @param string $page_slug Page slug
     * @return bool True if matches
     */
    private function matchesPage($pattern, $hook, $screen_id, $page_slug) {
        // Exact matches
        if ($pattern === $hook || $pattern === $screen_id || $pattern === $page_slug) {
            return true;
        }
        
        // Wildcard pattern (e.g., 'my-plugin-*')
        if (strpos($pattern, '*') !== false) {
            $regex = '/^' . str_replace(['*', '/'], ['.*', '\/'], preg_quote($pattern, '/')) . '$/';
            if (preg_match($regex, $hook) || preg_match($regex, $screen_id) || preg_match($regex, $page_slug)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Enqueue CSS files
     * 
     * @return void
     */
    public function enqueueStyles() {
        $base_url = plugin_dir_url($this->settings->getConfig('framework_path') . '/Settings.php');
        $version = $this->settings->getConfig('version');
        $instance_id = $this->settings->getInstanceId();
        
        // Enqueue WordPress color picker (required for color fields)
        wp_enqueue_style('wp-color-picker');
        
        // Enqueue CSS with instance-specific handles
        wp_enqueue_style(
            "{$instance_id}-lex-tokens",
            $base_url . 'assets/css/lex-tokens.css',
            [],
            $version
        );

        wp_enqueue_style(
            "{$instance_id}-lex-settings-core",
            $base_url . 'assets/css/lex-settings-core.css',
            ["{$instance_id}-lex-tokens"],
            $version
        );
        
        wp_enqueue_style(
            "{$instance_id}-lex-settings-tooltip",
            $base_url . 'assets/css/lex-settings-tooltip.css',
            ["{$instance_id}-lex-settings-core"],
            $version
        );
        
        wp_enqueue_style(
            "{$instance_id}-lex-settings-copy",
            $base_url . 'assets/css/lex-settings-copy.css',
            ["{$instance_id}-lex-settings-core"],
            $version
        );
        
        wp_enqueue_style(
            "{$instance_id}-lex-settings-modal",
            $base_url . 'assets/css/lex-settings-modal.css',
            ["{$instance_id}-lex-settings-core"],
            $version
        );

        wp_enqueue_style(
            "{$instance_id}-lex-drawer",
            $base_url . 'assets/css/lex-drawer.css',
            ["{$instance_id}-lex-settings-core"],
            $version
        );

        // Enqueue Select2 CSS (global handle - third-party library)
        if (!wp_style_is('select2', 'registered')) {
            wp_register_style(
                'select2',
                $base_url . 'assets/css/select2.min.css',
                [],
                $version
            );
        }
        wp_enqueue_style('select2');
    }
    
    /**
     * Enqueue JavaScript files
     * 
     * @return void
     */
    public function enqueueScripts() {
        $base_url = plugin_dir_url($this->settings->getConfig('framework_path') . '/Settings.php');
        $version = $this->settings->getConfig('version');
        $instance_id = $this->settings->getInstanceId();
        
        // Enqueue WordPress color picker
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue WordPress media library (required for media uploader fields)
        wp_enqueue_media();
        
        // Enqueue notifications module first (so it's available globally)
        wp_enqueue_script(
            "{$instance_id}-lex-settings-notifications",
            $base_url . 'assets/js/lex-settings-notifications.js',
            ['jquery'],
            $version,
            true
        );
        
        // Enqueue Select2 JS (global handle - third-party library)
        if (!wp_script_is('select2', 'registered')) {
            wp_register_script(
                'select2',
                $base_url . 'assets/js/select2.min.js',
                ['jquery'],
                $version,
                true
            );
        }
        wp_enqueue_script('select2');

        // Enqueue SortableJS (global handle - third-party library, drives every
        // .lex-sortable-checkbox-container drag-and-drop across the framework)
        if (!wp_script_is('sortablejs', 'registered')) {
            wp_register_script(
                'sortablejs',
                $base_url . 'assets/js/Sortable.min.js',
                [],
                $version,
                true
            );
        }
        wp_enqueue_script('sortablejs');

        // Enqueue core functionality
        wp_enqueue_script(
            "{$instance_id}-lex-settings-core",
            $base_url . 'assets/js/lex-settings-core.js',
            ['jquery', 'wp-color-picker', 'select2', 'sortablejs', "{$instance_id}-lex-settings-notifications"],
            $version,
            true
        );
        
        // Enqueue copy-to-clipboard component
        wp_enqueue_script(
            "{$instance_id}-lex-settings-copy",
            $base_url . 'assets/js/lex-settings-copy.js',
            ['jquery', "{$instance_id}-lex-settings-core"],
            $version,
            true
        );
        
        // Enqueue optional components
        wp_enqueue_script(
            "{$instance_id}-lex-settings-modal",
            $base_url . 'assets/js/lex-settings-modal.js',
            ["{$instance_id}-lex-settings-core"],
            $version,
            true
        );

        wp_enqueue_script(
            "{$instance_id}-lex-drawer",
            $base_url . 'assets/js/lex-drawer.js',
            ["{$instance_id}-lex-settings-core"],
            $version,
            true
        );

        wp_enqueue_script(
            "{$instance_id}-lex-settings-box-model",
            $base_url . 'assets/js/lex-settings-box-model.js',
            ["{$instance_id}-lex-settings-core"],
            $version,
            true
        );


        wp_add_inline_style('select2', '
/* Lex Settings Theme for Select2 */

/* Single Selection */
.select2-container--lex .select2-selection--single {
    background-color: #fff;
    border: 1px solid #d9d9d9;
    border-radius: 2px;
    outline: 0;
    transition: all 0.3s;
}

.select2-container--lex .select2-selection--single:hover {
    border-color: var(--lex-color-primary, #3858E9);
}

.select2-container--lex .select2-selection--single:focus,
.select2-container--lex.select2-container--focus .select2-selection--single {
    border-color: var(--lex-color-primary, #3858E9);
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.select2-container--lex .select2-selection--single .select2-selection__rendered {
    color: rgba(0, 0, 0, 0.85);
    line-height: 32px;
    padding-left: 11px;
    padding-right: 24px;
}

.select2-container--lex .select2-selection--single .select2-selection__clear {
    cursor: pointer;
    float: right;
    font-weight: normal;
    height: 30px;
    margin-right: 20px;
    color: rgba(0, 0, 0, 0.25);
}

.select2-container--lex .select2-selection--single .select2-selection__clear:hover {
    color: rgba(0, 0, 0, 0.45);
}

.select2-container--lex .select2-selection--single .select2-selection__placeholder {
    color: rgba(0, 0, 0, 0.25);
}

.select2-container--lex .select2-selection--single .select2-selection__arrow {
    background: transparent;
    border: none;
    height: 30px;
    position: absolute;
    top: 1px;
    right: 1px;
    width: 24px;
}

.select2-container--lex .select2-selection--single .select2-selection__arrow b {
    border-color: rgba(0, 0, 0, 0.25) transparent transparent transparent;
    border-style: solid;
    border-width: 5px 4px 0 4px;
    height: 0;
    left: 50%;
    margin-left: -4px;
    margin-top: -2px;
    position: absolute;
    top: 50%;
    width: 0;
}

.select2-container--lex[dir="rtl"] .select2-selection--single .select2-selection__clear {
    float: left;
}

.select2-container--lex[dir="rtl"] .select2-selection--single .select2-selection__arrow {
    left: 1px;
    right: auto;
}

.select2-container--lex.select2-container--open .select2-selection--single {
    border-color: var(--lex-color-primary, #3858E9);
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.select2-container--lex.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent rgba(0, 0, 0, 0.25) transparent;
    border-width: 0 4px 5px 4px;
}

.select2-container--lex.select2-container--open.select2-container--above .select2-selection--single {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

.select2-container--lex.select2-container--open.select2-container--below .select2-selection--single {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

/* Multiple Selection */
.select2-container--lex .select2-selection--multiple {
    background-color: #fff;
    border: 1px solid #d9d9d9;
    border-radius: 2px;
    cursor: text;
    outline: 0;
    padding: 1px 4px;
    min-height: 32px;
    transition: all 0.3s;
}

.select2-container--lex .select2-selection--multiple:hover {
    border-color: var(--lex-color-primary, #3858E9);
}

.select2-container--lex .select2-selection--multiple:focus,
.select2-container--lex.select2-container--focus .select2-selection--multiple {
    border-color: var(--lex-color-primary, #3858E9);
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.select2-container--lex .select2-selection--multiple .select2-selection__clear {
    display: none;
}

.select2-container--lex .select2-selection--multiple .select2-selection__choice {
    border: 1px solid #d9d9d9;
    background: #0000000f;
    border-radius: 4px;
    border-radius: 2px;
    display: inline-flex;
    align-items: center;
    flex-direction: row-reverse;
    margin: 2px 4px 2px 0;
    padding: 0 4px 0 8px;
    max-width: 99%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
    height: 24px;
    line-height: 22px;
}

.select2-container--lex .select2-selection--multiple .select2-selection__choice__display {
    cursor: default;
    padding: 0;
    color: rgba(0, 0, 0, 0.85);
    font-size: 14px;
    line-height: 22px;
    order: 2;
}

.select2-container--lex .select2-selection--multiple .select2-selection__choice__remove {
    background-color: transparent;
    border: none;
    color: rgba(0, 0, 0, 0.45);
    cursor: pointer;
    font-size: 0;
    font-weight: normal;
    padding: 0;
    margin: 0 0 0 4px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 14px;
    order: 1;
    position: relative;
}

.select2-container--lex .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: rgba(0, 0, 0, 0.75);
    outline: none;
}

.select2-container--lex .select2-selection--multiple .select2-selection__choice__remove::before {
    content: "×";
    font-size: 14px;
    line-height: 1;
    display: block;
    color: inherit;
}

.select2-container--lex[dir="rtl"] .select2-selection--multiple .select2-selection__choice {
    margin-left: 4px;
    margin-right: 0;
}

.select2-container--lex[dir="rtl"] .select2-selection--multiple .select2-selection__choice__display {
    padding-left: 0;
    padding-right: 0;
}

.select2-container--lex[dir="rtl"] .select2-selection--multiple .select2-selection__choice {
    flex-direction: row;
    padding: 0 8px 0 4px;
}

.select2-container--lex[dir="rtl"] .select2-selection--multiple .select2-selection__choice__remove {
    margin-right: 4px;
    margin-left: 0;
}

.select2-container--lex.select2-container--open .select2-selection--multiple {
    border-color: var(--lex-color-primary, #3858E9);
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.select2-container--lex.select2-container--open.select2-container--above .select2-selection--multiple {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

.select2-container--lex.select2-container--open.select2-container--below .select2-selection--multiple {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

/* Search Field */
.select2-container--lex .select2-search--dropdown .select2-search__field {
    border: 1px solid #d9d9d9;
    border-radius: 2px;
    outline: 0;
    padding: 4px 11px;
}

.select2-container--lex .select2-search--dropdown .select2-search__field:focus {
    border-color: var(--lex-color-primary, #3858E9);
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.select2-container--lex .select2-search--inline .select2-search__field {
    outline: 0;
    box-shadow: none;
    border: none;
    background: transparent;
    padding: 0;
    margin: 0;
    height: 30px;
    line-height: 30px;
}

/* Dropdown */
.select2-container--lex .select2-dropdown {
    background-color: #fff;
    border: none;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    margin-top: 4px;
}

.select2-container--lex .select2-dropdown--above {
    margin-top: 0;
    margin-bottom: 4px;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.15);
}

.select2-container--lex .select2-dropdown--below {
}

.select2-container--lex .select2-results > .select2-results__options {
    max-height: 256px;
    overflow-y: auto;
    padding: 4px 0;
}

.select2-container--lex .select2-results__option {
    padding: 5px 12px;
    color: rgba(0, 0, 0, 0.85);
    font-size: 14px;
    line-height: 22px;
    transition: background 0.3s;
    margin-bottom: 0px;
}

.select2-container--lex .select2-results__option--group {
    padding: 0;
    color: rgba(0, 0, 0, 0.45);
    font-size: 12px;
    font-weight: 500;
    padding: 8px 12px 4px;
}

.select2-container--lex .select2-results__option--disabled {
    color: rgba(0, 0, 0, 0.25);
    cursor: not-allowed;
}

.select2-container--lex .select2-results__option--highlighted.select2-results__option--selectable {
    background-color: #e6f7ff;
    color: rgba(0, 0, 0, 0.85);
}

.select2-container--lex .select2-results__option--selected {
    background-color: #e6f7ff;
    color: rgba(0, 0, 0, 0.85);
    font-weight: normal;
    position: relative;
}

.select2-container--lex .select2-results__option--selected::after {
    content: "✓";
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--lex-color-primary, #3858E9);
    font-weight: bold;
    font-size: 14px;
}

.select2-container--lex .select2-results__group {
    cursor: default;
    display: block;
    padding: 0;
    color: var(--lex-color-primary, #3858E9);
    font-size: 12px;
    font-weight: bold;
    padding: 0px 12px 4px;
}

.select2-container--lex.select2-container--open .select2-dropdown {
    border: none;
}

        ');
    }
    
    /**
     * Localize JavaScript with instance-specific data
     * 
     * @return void
     */
    public function localizeScripts() {
        $instance_id = $this->settings->getInstanceId();
        $js_global = $this->settings->getConfig('js_global');
        $ajax_prefix = $this->settings->getConfig('ajax_prefix');
        $nonce_action = $this->settings->getConfig('nonce_action');
        
        $localized_data = [
            'ajaxurl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce($nonce_action),
            'ajaxPrefix' => $ajax_prefix,
            'debugMode'  => $this->settings->getConfig('debug_mode'),
            'instanceId' => $instance_id,
            'i18n' => [
                'settingsSaved' => __('Settings saved successfully!', 'lex-settings'),
                'confirmAction' => __('Are you sure you want to proceed?', 'lex-settings'),
                'requestFailed' => __('Request failed. Please try again.', 'lex-settings'),
                'dismissNotice' => __('Dismiss this notice.', 'lex-settings'),
                'exportSettings' => __('Export functionality would download a JSON file with your settings.', 'lex-settings'),
                'selectFileToImport' => __('Please select a file to import.', 'lex-settings'),
                'importSettings' => __('Import functionality would read and apply settings from: ', 'lex-settings'),
                'resetAllSettings' => __('All settings would be reset to default values.', 'lex-settings'),
                'resetSectionSettings' => __('This section would be reset to defaults.', 'lex-settings'),
                'databaseOptimized' => __('Database tables optimized successfully!', 'lex-settings'),
                'cacheCleared' => __('All caches cleared successfully!', 'lex-settings'),
                'saving' => __('Saving...', 'lex-settings'),
                'resetting' => __('Resetting...', 'lex-settings'),
                'exporting' => __('Exporting...', 'lex-settings'),
                'importing' => __('Importing...', 'lex-settings'),
                'processing' => __('Processing...', 'lex-settings')
            ]
        ];
        
        // Add debug data if enabled
        if ($this->settings->getConfig('debug_mode')) {
            $localized_data['allSettings'] = $this->settings->dataManager->getAll();
            $localized_data['defaultSettings'] = $this->settings->dataManager->getDefaults();
        }
        
        // Localize to both notifications and core scripts
        wp_localize_script("{$instance_id}-lex-settings-notifications", $js_global, $localized_data);
        wp_localize_script("{$instance_id}-lex-settings-core", $js_global, $localized_data);
    }
    
    /**
     * Register inline CSS to be added to a stylesheet
     * 
     * Can be called from tab content files. The CSS will be output
     * in a <style> tag after all tab content files have been included.
     * 
     * @param string $css CSS code to add
     * @return void
     */
    public function addInlineStyle($css) {
        $this->inline_styles .= "\n" . trim($css);
    }
    
    /**
     * Get all registered inline styles
     * 
     * @return string All registered inline styles
     */
    public function getInlineStyles() {
        return $this->inline_styles;
    }
    
    /**
     * Clear all registered inline styles
     * 
     * Called after styles are output to prevent them from being added
     * again on subsequent page loads or AJAX requests.
     * 
     * @return void
     */
    public function clearInlineStyles() {
        $this->inline_styles = '';
    }
}
