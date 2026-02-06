<?php
namespace Lex\Settings\V2\Services;

/**
 * Widget Renderer Service
 * 
 * Handles rendering of sidebar widgets.
 */

use Lex\Settings\V2\Settings;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Renderer Service Class
 */
class WidgetRenderer {
    
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
     * Render a sidebar widget
     * 
     * @param string $widget_name Widget identifier (trial, upgrade, support, etc.)
     * @param array $data Widget configuration data
     * @return void Outputs HTML directly
     */
    public function render($widget_name, $data = []) {
        if (isset($data['enabled']) && $data['enabled'] === false) {
            return;
        }
        
        $widget_slug = strtolower(trim($widget_name));
        $config_path = $this->settings->getConfig('config_path');
        $widget_path = $config_path . '/widgets/widget-' . $widget_slug . '.php';
        
        if (!file_exists($widget_path)) {
            ?>
            <div class="lex-sidebar-widget lex-sidebar-widget--missing">
                <div class="lex-sidebar-widget__header">
                    <span class="dashicons dashicons-info"></span>
                    <h3><?php echo esc_html__('Widget Not Found', 'lex-settings'); ?></h3>
                </div>
                <div class="lex-sidebar-widget__content">
                    <p class="description">
                        <?php 
                        printf(
                            esc_html__('Widget "%s" not found. Create %s to add this widget.', 'lex-settings'),
                            esc_html($widget_name),
                            '<code>config/widgets/widget-' . esc_html($widget_slug) . '.php</code>'
                        ); 
                        ?>
                    </p>
                </div>
            </div>
            <?php
            return;
        }
        
        // Make $config and $settings available inside the widget template
        $config = $data;
        $settings = $this->settings;
        $widgetRenderer = $this;
        include $widget_path;
    }
    
    /**
     * Render copy button HTML
     * 
     * @param string $text Text to copy
     * @param string $label Button label
     * @param array $args Optional arguments (variant, size, block, icon)
     * @return string Copy button HTML
     */
    public function renderCopyButton($text, $label = '', $args = []) {
        $defaults = [
            'label' => __('Copy', 'lex-settings'),
            'variant' => 'primary',
            'size' => '',
            'block' => false,
            'icon' => 'admin-page',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        if (!empty($label)) {
            $args['label'] = $label;
        }
        
        $classes = ['lex-copy-button'];
        
        if ($args['variant'] === 'secondary') {
            $classes[] = 'lex-copy-button--secondary';
        }
        
        if ($args['size'] === 'small') {
            $classes[] = 'lex-copy-button--small';
        }
        
        if ($args['block']) {
            $classes[] = 'lex-copy-button--block';
        }
        
        $class_string = implode(' ', $classes);
        $icon_class = 'dashicons-' . sanitize_html_class($args['icon']);
        
        printf(
            '<button type="button" class="%s" data-lex-copy="%s">
                <span class="dashicons %s"></span>
                %s
            </button>',
            esc_attr($class_string),
            esc_attr($text),
            esc_attr($icon_class),
            esc_html($args['label'])
        );
    }
}

