<?php
namespace LeanPL;

/**
 * Config Merger Class
 * Handles unified priority system: Instance Settings → Global Options → Player Defaults
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Config_Merger {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Cached defaults (shared + video + audio merged)
     */
    private $defaults_cache = null;

    /**
     * Debug output buffer
     */
    private static $debug_output = [];

    /**
     * Get singleton instance
     * 
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if debug mode is enabled
     * 
     * @return bool True if debug mode is enabled via query parameter
     */
    private static function is_test_mode() {
        return function_exists( 'leanpl_is_test_mode' ) && leanpl_is_test_mode();
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }

    /**
     * Main merge method: Instance → Global → Defaults
     * 
     * @param array $instance_config Instance-level config
     * @return array Merged configuration
     */
    public function merge($instance_config = []) {
        $debug_info = [];
        
        if (self::is_test_mode()) {
            $debug_info['start'] = 'Config_Merger::merge() START';
            $debug_info['instance_config'] = $instance_config;
        }
        
        // Get all defaults (shared + video + audio merged)
        $defaults = $this->get_all_defaults();
        
        if (self::is_test_mode()) {
            $debug_info['defaults'] = $defaults;
        }
        
        // Get all global options (flat keys)
        $global_options = leanpl_get_option(null, []);
        
        if (self::is_test_mode()) {
            $debug_info['global_options'] = $global_options;
        }
        
        // Start with defaults as base
        $merged = [];
        $key_debug = [];
        
        // Loop through all default keys
        foreach ($defaults as $key => $default_value) {
            // Get instance value (may not exist)
            $instance_value = isset($instance_config[$key]) ? $instance_config[$key] : null;
            
            // Get global value (may not exist)
            $global_value = isset($global_options[$key]) ? $global_options[$key] : null;
            
            // Collect debug info for important keys
            if (self::is_test_mode() && ($key === 'loop' || in_array($key, ['autoplay', 'muted', 'volume']))) {
                $key_debug[$key] = [
                    'instance' => [
                        'value' => $instance_value,
                        'type' => gettype($instance_value),
                        'is_set' => $this->is_value_set($instance_value)
                    ],
                    'global' => [
                        'value' => $global_value,
                        'type' => gettype($global_value),
                        'is_set' => $this->is_value_set($global_value)
                    ],
                    'default' => [
                        'value' => $default_value,
                        'type' => gettype($default_value)
                    ]
                ];
            }
            
            // Merge with priority: Instance → Global → Default
            $merged[$key] = $this->merge_value($key, $instance_value, $global_value, $default_value);
            
            // Add final value to debug
            if (self::is_test_mode() && isset($key_debug[$key])) {
                $key_debug[$key]['final'] = [
                    'value' => $merged[$key],
                    'type' => gettype($merged[$key]),
                    'source' => $this->get_priority_source($key, $instance_value, $global_value, $default_value, $merged[$key])
                ];
            }
        }
        
        if (self::is_test_mode()) {
            $debug_info['key_details'] = $key_debug;
            $debug_info['final_merged'] = $merged;
            $debug_info['end'] = 'Config_Merger::merge() END';
            
            // Store debug info
            self::$debug_output[] = $debug_info;
        }
        
        return $merged;
    }

    /**
     * Get all defaults (shared + video + audio merged)
     * 
     * @return array All defaults flattened
     */
    private function get_all_defaults() {
        // Use cache if available
        if (null !== $this->defaults_cache) {
            return $this->defaults_cache;
        }
        
        // Load defaults from file
        $defaults = include LEANPL_DIR . '/includes/player-defaults.php';
        
        // Merge shared + video + audio
        // Note: video and audio both have 'controls' key, video will override shared
        $merged = array_merge($defaults['shared'], $defaults['video'], $defaults['audio']);
        
        // Cache the result
        $this->defaults_cache = $merged;
        
        return $this->defaults_cache;
    }

    /**
     * Merge single value with priority chain
     * 
     * Priority: Instance → Global → Default
     * 
     * Rules:
     * - Arrays: Replace completely (no merging)
     * - Not set (inherit): null, '' (empty string), [] (empty array) → inherit from global/defaults
     * - Set (explicit): Everything else (false, 0, '0', '1', 'yes', 'no', 'true', 'false', etc.) → use as-is
     * 
     * @param string $key Config key
     * @param mixed $instance Instance value (null/empty = not set)
     * @param mixed $global Global value (null/empty = not set)
     * @param mixed $default Default value (always exists)
     * @return mixed Final value
     */
    private function merge_value($key, $instance, $global, $default) {

        // Check if values are set BEFORE normalization to preserve distinction between empty and explicit values
        // Normalization converts '' to null, so checking after normalization would lose this distinction
        $instance_is_set = $this->is_value_set($instance);
        $global_is_set = $this->is_value_set($global);
        
        // Only normalize boolean values if the default is a boolean
        // This prevents numeric fields (volume, seek_time) from being incorrectly converted
        $should_normalize = is_bool($default);
        
        if ($should_normalize) {
            // Normalize boolean values (WordPress options might store as strings like '0'/'1')
            $instance_normalized = leanpl_normalize_boolean($instance);
            $global_normalized = leanpl_normalize_boolean($global);
            
            // Use normalized value only if it's a boolean (successful normalization)
            $instance_value = (is_bool($instance_normalized)) ? $instance_normalized : $instance;
            $global_value = (is_bool($global_normalized)) ? $global_normalized : $global;
        } else {
            // For non-boolean fields (arrays, strings, numbers), preserve original value
            $instance_value = $instance;
            $global_value = $global;
        }
        
        // Determine final value based on priority
        $final_value = null;
        
        // Priority 1: Instance value
        if ($instance_is_set) {
            $final_value = $instance_value;
        }
        // Priority 2: Global value
        elseif ($global_is_set) {
            $final_value = $global_value;
        }
        // Priority 3: Default value (always exists - already correct type from defaults file)
        else {
            $final_value = $default;
        }
        
        // Convert volume from 0-100 (stored format) to 0-1 (player format), clamped to valid range.
        // Delegates to leanpl_float_clamp_0_1() (functions-converter.php) so shortcode and
        // metabox/Elementor volume values share one clamp rule instead of two copies drifting apart.
        if ($key === 'volume' && is_numeric($final_value)) {
            $final_value = leanpl_float_clamp_0_1($final_value);
        }
        
        return $final_value;
    }

    /**
     * Check if value is explicitly set (not inherited)
     * 
     * Empty values (null, '', []) are considered "not set" and should inherit.
     * Explicit values (false, 0) are considered "set" and should be used.
     * 
     * @param mixed $value Value to check
     * @return bool True if explicitly set
     */
    private function is_value_set($value) {
        // null = not set
        if ($value === null) {
            return false;
        }
        
        // Empty string = not set
        if ($value === '') {
            return false;
        }
        
        // Empty array = not set
        if (is_array($value) && empty($value)) {
            return false;
        }
        
        // Everything else is considered set (including false, 0, etc.)
        return true;
    }
    
    /**
     * Get priority source for debugging
     * 
     * @param string $key Config key
     * @param mixed $instance Instance value
     * @param mixed $global Global value
     * @param mixed $default Default value
     * @param mixed $final Final merged value
     * @return string Priority source description
     */
    private function get_priority_source($key, $instance, $global, $default, $final) {
        // Only normalize if default is boolean (same logic as merge_value)
        $should_normalize = is_bool($default);
        
        if ($should_normalize) {
            // Normalize values before comparison (same normalization as merge_value)
            // This ensures we compare normalized values, not raw database strings
            $instance_normalized = leanpl_normalize_boolean($instance);
            $global_normalized = leanpl_normalize_boolean($global);
            
            // Use normalized value only if it's a boolean (same logic as merge_value)
            $instance_value = (is_bool($instance_normalized)) ? $instance_normalized : $instance;
            $global_value = (is_bool($global_normalized)) ? $global_normalized : $global;
        } else {
            // For non-boolean fields, preserve original values
            $instance_value = $instance;
            $global_value = $global;
        }
        
        // Compare values to determine source
        // BUT check is_value_set on ORIGINAL values (before normalization)
        if ($this->is_value_set($instance) && $final === $instance_value) {
            return "INSTANCE";
        }
        if ($this->is_value_set($global) && $final === $global_value) {
            return "GLOBAL";
        }
        return "DEFAULT";
    }
    
    /**
     * Enqueue debug styles using wp_add_inline_style
     * 
     * @return void
     */
    private static function enqueue_debug_styles() {
        $handle = 'lpl-config-merger-debug';
        
        // Register empty stylesheet if not already registered
        if (!wp_style_is($handle, 'registered')) {
            wp_register_style($handle, false);
        }
        
        // Enqueue if not already enqueued
        if (!wp_style_is($handle, 'enqueued')) {
            wp_enqueue_style($handle);
        }
        
        // Add inline styles
        $css = '
            .lpl-debug-container {
                background: #f0f0f0;
                border: 2px solid #333;
                padding: 15px;
                margin: 20px 0;
                font-family: monospace;
                font-size: 12px;
                max-width: 100%;
                overflow-x: auto;
            }
            .lpl-debug-title {
                margin-top: 0;
                color: #d63638;
            }
            .lpl-debug-merge-item {
                margin-bottom: 20px;
                padding: 10px;
                background: white;
            }
            .lpl-debug-details {
                margin: 10px 0;
            }
            .lpl-debug-summary {
                cursor: pointer;
                font-weight: bold;
            }
            .lpl-debug-pre {
                background: #f9f9f9;
                padding: 10px;
                overflow-x: auto;
            }
            .lpl-debug-key-details {
                margin: 10px 0;
            }
            .lpl-debug-key-item {
                margin: 10px 0;
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
            }
            .lpl-debug-key-label {
                color: #0073aa;
            }
            .lpl-debug-key-final {
                color: #d63638;
            }
        ';
        
        wp_add_inline_style($handle, $css);
    }
    
    /**
     * Get debug output as visible HTML panel
     * 
     * @return string HTML with debug info
     */
    public static function get_debug_output_html() {
        if (!self::is_test_mode() || empty(self::$debug_output)) {
            return '';
        }
        
        // Enqueue styles
        self::enqueue_debug_styles();
        
        $html = '<div class="lpl-debug-container">';
        $html .= '<h3 class="lpl-debug-title">Lean Player Config Debug</h3>';
        
        foreach (self::$debug_output as $index => $debug) {
            $html .= '<div class="lpl-debug-merge-item">';
            $html .= '<strong>Merge #' . ($index + 1) . '</strong><br><br>';
            
            $html .= '<details open class="lpl-debug-details"><summary class="lpl-debug-summary">Instance Config</summary><pre class="lpl-debug-pre">' . esc_html(print_r($debug['instance_config'], true)) . '</pre></details>';
            $html .= '<details open class="lpl-debug-details"><summary class="lpl-debug-summary">Global Options</summary><pre class="lpl-debug-pre">' . esc_html(print_r($debug['global_options'], true)) . '</pre></details>';
            $html .= '<details open class="lpl-debug-details"><summary class="lpl-debug-summary">Defaults</summary><pre class="lpl-debug-pre">' . esc_html(print_r($debug['defaults'], true)) . '</pre></details>';
            
            // Key details
            if (!empty($debug['key_details'])) {
                $html .= '<div class="lpl-debug-key-details"><strong>Key Details:</strong><br>';
                foreach ($debug['key_details'] as $key => $details) {
                    $html .= '<div class="lpl-debug-key-item">';
                    $html .= '<strong class="lpl-debug-key-label">Key: ' . esc_html($key) . '</strong><br>';
                    $html .= 'Instance: <code>' . esc_html(var_export($details['instance']['value'], true)) . '</code> (type: ' . esc_html($details['instance']['type']) . ', is_set: ' . ($details['instance']['is_set'] ? 'true' : 'false') . ')<br>';
                    $html .= 'Global: <code>' . esc_html(var_export($details['global']['value'], true)) . '</code> (type: ' . esc_html($details['global']['type']) . ', is_set: ' . ($details['global']['is_set'] ? 'true' : 'false') . ')<br>';
                    $html .= 'Default: <code>' . esc_html(var_export($details['default']['value'], true)) . '</code> (type: ' . esc_html($details['default']['type']) . ')<br>';
                    $html .= '<strong class="lpl-debug-key-final">Final: <code>' . esc_html(var_export($details['final']['value'], true)) . '</code> (type: ' . esc_html($details['final']['type']) . ', source: ' . esc_html($details['final']['source']) . ')</strong>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            
            $html .= '<details open class="lpl-debug-details"><summary class="lpl-debug-summary">Final Merged Config</summary><pre class="lpl-debug-pre">' . esc_html(print_r($debug['final_merged'], true)) . '</pre></details>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Clear debug output after outputting
        self::$debug_output = [];
        
        return $html;
    }
}

