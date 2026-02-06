<?php
namespace Lex\Settings\V2\Services;

/**
 * Field Renderer Service
 * 
 * Handles rendering of form fields.
 * 
 * @package Lex\Settings
 */

use Lex\Settings\V2\Settings;
use Lex\Settings\V2\Utilities\DotNotation;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Field Renderer Service Class
 */
class FieldRenderer {
    
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
     * Render a form field
     * 
     * @param string $type Field type (text, number, select, etc.)
     * @param string $key Field key (flat or dot notation)
     * @param array $properties Field-specific properties
     * @return void
     */
    public function render($type, $key, $properties = []) {
        if (empty($type)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This is a fallback error message
            $this->renderFallback($key, __('Field type is missing', 'lex-settings'));
            return;
        }
        
        if (empty($key)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This is a fallback error message
            $this->renderFallback(null, __('Field key is missing', 'lex-settings'));
            return;
        }
        
        // Resolve template path
        $framework_path = $this->settings->getConfig('framework_path');
        $template_path = $framework_path . '/partials/fields/' . $type . '.php';
        
        if (!file_exists($template_path)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This is a fallback error message
            $this->renderFallback($key, __("Field type '{$type}' template not found", 'lex-settings'));
            return;
        }
        
        // Get field type definition from {type}.php file
        $field_defaults = $this->getFieldTypeDefinition($type, $template_path);
        
        // Get current value with default support
        // Priority: properties['value'] > retrieved value > properties['default'] > empty string
        if (isset($properties['value'])) {
            // Explicitly provided value - always respect it
            // For metaboxes: Only pass 'value' if meta exists, otherwise omit it to use default
            $current_value = $properties['value'];
        } else {
            // No explicit value - get from settings
            $retrieved_value = $this->getFieldValue($key);
            
            // Check if key actually exists in settings
            $key_exists = $this->settings->dataManager->has($key);
            
            if (!$key_exists && isset($properties['default'])) {
                // Key doesn't exist - use default if provided
                $current_value = $properties['default'];
            } else {
                // Key exists or no default - use retrieved value
                $current_value = $retrieved_value;
            }
        }
        
        // Merge configuration (user-provided properties override type defaults)
        $field_config = $this->mergeConfig($field_defaults, $properties);
        
        // Allow plugins to filter field config before rendering
        // Hook: lex_settings/field_config (generic) and lex_settings/field_config/{field_key} (specific)
        $instance_id = $this->settings->getConfig('instance_id');
        $field_config = apply_filters(
            'lex_settings/field_config', 
            $field_config, 
            $key, 
            $type, 
            $instance_id
        );
        // Also provide field-specific hook for granular control
        $field_config = apply_filters(
            'lex_settings/field_config/' . $key, 
            $field_config, 
            $type, 
            $instance_id
        );
        
        // Determine field name - allow override for post meta
        $field_name = $properties['field_name'] ?? $key;
        
        // Normalize field config
        $field = [
            'key'           => $key,
            'type'          => $type,
            'name'          => $field_name, // Use custom name if provided
            'id'            => 'lex_' . str_replace(['.', '-', '_'], '_', $field_name),
            'label'         => $field_config['label'] ?? '',
            'desc'          => $field_config['desc'] ?? '',
            'label_desc'    => $field_config['label_desc'] ?? '',
            'tooltip'       => $field_config['tooltip'] ?? '',
            'placeholder'   => $field_config['placeholder'] ?? '',
            'classes'       => $field_config['classes'] ?? [],
            'input_classes' => $field_config['input_classes'] ?? [],
            'disabled'      => $field_config['disabled'] ?? false,
            'pro'           => $field_config['pro'] ?? null,
        ];
        
        // Add type-specific properties
        foreach ($field_config as $config_key => $config_value) {
            if (!isset($field[$config_key])) {
                $field[$config_key] = $config_value;
            }
        }
        
        // Make variables available to template
        $value = $current_value;
        $fieldRenderer = $this; // Make FieldRenderer instance available to template
        
        // Include template - renders directly
        include $template_path;
    }
    
    /**
     * Render label HTML with optional PRO badge
     * 
     * @param array $field Field configuration
     * @return void
     */
    public function renderLabel($field) {
        if (empty($field['label'])) {
            return '';
        }
        
        $for_attr = !empty($field['id']) ? ' for="' . esc_attr($field['id']) . '"' : '';
        
        // Check if field has PRO configuration
        $has_pro = !empty($field['pro']) && is_array($field['pro']);
        $badge_position = $has_pro ? ($field['pro']['badge_position'] ?? 'block') : null;
        
        // Build label HTML based on PRO badge position
        if ($badge_position === 'block') {
            $badge_html = $this->getProBadge($field, 'label');
            printf(
                '%s<label%s>%s</label>',
                $badge_html,
                $for_attr,
                wp_kses_post($field['label'])
            );

            return;
        } elseif ($badge_position === 'inline') {
            $badge_html = $this->getProBadge($field, 'label');
            printf(
                '<label%s>%s %s</label>',
                $for_attr,
                wp_kses_post($field['label']),
                $badge_html
            );

            return;
        } elseif ($badge_position === 'absolute') {
            $badge_html = $this->getProBadge($field, 'label');
            printf(
                '<div class="lex-pro-badge__wrapper">%s<label%s>%s</label></div>',
                $badge_html,
                $for_attr,
                wp_kses_post($field['label'])
            );

            return;
        } else {
            printf(
                '<label%s>%s</label>',
                $for_attr,
                wp_kses_post($field['label'])
            );

            return;
        }
    }
    
    /**
     * Render tooltip HTML
     * 
     * @param array $field Field configuration
     * @return void
     */
    public function renderTooltip($field) {
        if (empty($field['tooltip'])) {
            return '';
        }
        
        $tooltip_class = 'lex-tooltip';
        if (isset($field['tooltip_width'])) {
            $tooltip_class .= ' lex-tooltip--' . esc_attr($field['tooltip_width']);
        }
        
        printf(
            '<span class="%s">
                <span class="dashicons dashicons-editor-help"></span>
                <span class="lex-tooltip__text">%s</span>
            </span>',
            esc_attr($tooltip_class),
            wp_kses_post($field['tooltip'])
        );
    }
    
    /**
     * Render description HTML
     * 
     * @param array $field Field configuration
     * @return string Description HTML or empty string
     */
    public function renderDescription($field) {
        $desc = $field['desc'] ?? '';
        
        // Don't append inherited value to description - it goes in title attribute instead
        
        if (empty($desc)) {
            return '';
        }
        
        $style = '';
        if (isset($field['desc_style'])) {
            $style = ' style="' . esc_attr($field['desc_style']) . '"';
        }
        
        return sprintf(
            '<p class="description"%s>%s</p>',
            $style,
            wp_kses_post($desc)
        );
    }
    
    /**
     * Get title attribute for inherited fields
     * 
     * @param array $field Field configuration
     * @return string Title attribute value or empty string
     */
    public function getInheritedTitle($field) {
        if (!isset($field['inherited_value'])) {
            return '';
        }
        
        $inherited_text = $this->formatInheritedValue(
            $field['inherited_value'],
            $field['inherited_source'] ?? 'Global Options',
            $field['type'] ?? 'text'
        );
        
        return esc_attr($inherited_text);
    }
    
    /**
     * Check if field is inherited (has inherited value)
     * 
     * @param array $field Field configuration
     * @return bool True if field is inherited
     */
    public function isInherited($field) {
        return isset($field['inherited_value']);
    }
    
    /**
     * Render PRO badge HTML
     * 
     * @param array $field Field configuration
     * @param string $position_context Where badge is being rendered
     * @return string PRO badge HTML or empty string
     */
    public function getProBadge($field, $position_context = 'label') {
        if (empty($field['pro']) || !is_array($field['pro'])) {
            return '';
        }
        
        $position = $field['pro']['badge_position'] ?? 'block';
        
        if ($position === false || $position === null) {
            return '';
        }
        
        return '<span class="lex-pro-badge lex-pro-badge--auto">PRO</span>';
    }
    
    /**
     * Convert array of classes to space-separated string
     * 
     * @param array|string $classes Classes array or string
     * @return string Space-separated class string
     */
    public function classnames($classes) {
        if (is_string($classes)) {
            return $classes;
        }
        
        if (is_array($classes)) {
            return implode(' ', array_filter($classes));
        }
        
        return '';
    }
    
    /**
     * Render label description HTML
     * 
     * @param array $field Field configuration
     * @return void
     */
    public function renderLabelDescription($field) {
        if (empty($field['label_desc'])) {
            return '';
        }
        
        printf(
            '<p class="description">%s</p>',
            wp_kses_post($field['label_desc'])
        );
    }
    
    /**
     * Render fallback HTML for error cases
     * 
     * @param string|null $field_key Field key if available
     * @param string $message Error message
     * @return string Fallback HTML
     */
    public function renderFieldFallback($field_key, $message) {
        $key_display = $field_key ? "Field: {$field_key}" : 'Unknown field';
        
        return sprintf(
            '<tr>
                <th scope="row">%s</th>
                <td>
                    <strong>Error:</strong> %s
                </td>
            </tr>',
            esc_html($key_display),
            esc_html($message)
        );
    }
    
    /**
     * Gets the field type definition (default configuration) from a template file
     * 
     * What it does:
     * - Loads the field template file (e.g., text.php, select.php)
     * - Sets a flag so the template knows to return defaults only (not render HTML)
     * - Gets the default configuration array that the template returns
     * - Returns those defaults so they can be merged with user-provided properties
     * 
     * Why it exists:
     * Each field type template defines its own default settings (like default classes,
     * placeholder text, etc.). This method extracts those defaults before we render
     * the actual field, so we can merge them with any custom properties the user provided.
     * 
     * @param string $type Field type (e.g., 'text', 'select', 'checkbox')
     * @param string $template_path Full path to the field template file
     * @return array Field type definition (default configuration from the template)
     */
    private function getFieldTypeDefinition($type, $template_path) {
        $field_defaults = [];
        
        // Include template in isolated scope to extract defaults
        $load_defaults_only = true;
        $defaults_result = include $template_path;
        
        if (is_array($defaults_result)) {
            $field_defaults = $defaults_result;
        }
        
        return $field_defaults;
    }
    
    /**
     * Merge field configuration
     * 
     * @param array $defaults Field type definition
     * @param array $properties User-provided properties
     * @return array Merged field configuration
     */
    private function mergeConfig($defaults, $properties) {
        $field_config = $defaults;
        
        // Merge with field-specific properties
        foreach ($properties as $key => $value) {
            if (is_array($value) && isset($field_config[$key]) && is_array($field_config[$key])) {
                // Special handling for options arrays (associative arrays that should preserve keys)
                if ($key === 'options') {
                    // For options, replace entirely to preserve numeric string keys
                    // array_merge() re-indexes numeric string keys, so we use direct assignment
                    $field_config[$key] = $value;
                } else {
                    // Merge arrays (like classes)
                    $field_config[$key] = array_merge($field_config[$key], $value);
                }
            } else {
                // Override scalar values
                $field_config[$key] = $value;
            }
        }
        
        return $field_config;
    }
    
    /**
     * Get current field value
     * 
     * @param string $key Field key
     * @return mixed Field value
     */
    private function getFieldValue($key) {
        $all_settings = $this->settings->dataManager->getAll();
        return DotNotation::get($all_settings, $key, '');
    }
    
    /**
     * Format inherited value for display in description
     * 
     * @param mixed $value Inherited value
     * @param string $source Source of inherited value ('Global Options' or 'Default')
     * @param string $field_type Field type (number, text, etc.)
     * @return string Formatted HTML string
     */
    private function formatInheritedValue($value, $source, $field_type) {
        $formatted = $this->formatValueForDisplay($value, $field_type);
        $source_text = ($source === 'Default') ? 'Default' : 'Global Options';
        
        return sprintf(
            'ℹ️ Currently using: %s (from %s)',
            esc_html($formatted),
            esc_html($source_text)
        );
    }
    
    /**
     * Format value for display based on field type
     * 
     * @param mixed $value Value to format
     * @param string $field_type Field type
     * @return string Formatted value string
     */
    private function formatValueForDisplay($value, $field_type) {
        // Boolean values
        if (is_bool($value) || $value === '1' || $value === '0' || $value === 1 || $value === 0) {
            $bool_value = ($value === true || $value === '1' || $value === 1);
            return $bool_value ? 'Enabled' : 'Disabled';
        }
        
        // Arrays
        if (is_array($value)) {
            if (empty($value)) {
                return 'None';
            }
            return implode(', ', array_map('esc_html', $value));
        }
        
        // Numbers, strings, etc. - return as string
        return (string) $value;
    }
    
    /**
     * Render fallback HTML for error cases (used when field type is missing, key is missing, or template is not found)
     * 
     * @param string|null $key Field key if available
     * @param string $message Error message
     * @return void
     */
    private function renderFallback($key, $message) {
        $key_display = $key ? "Field: {$key}" : 'Unknown field';
        
        printf(
            '<tr>
                <th scope="row">%s</th>
                <td>
                    <strong>Error:</strong> %s
                </td>
            </tr>',
            esc_html($key_display),
            esc_html($message)
        );
    }
}

