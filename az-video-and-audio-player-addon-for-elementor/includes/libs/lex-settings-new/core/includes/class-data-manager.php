<?php
namespace Lex\Settings\V2\Services;

/**
 * Data Manager Service
 * 
 * Handles all data operations: get, save, reset, import, export.
 */

use Lex\Settings\V2\Settings;
use Lex\Settings\V2\Utilities\DotNotation;
use Lex\Settings\V2\Utilities\Sanitizer;
use Lex\Settings\V2\Utilities\Validator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data Manager Service Class
 */
class DataManager {
    
    /**
     * Settings instance
     * 
     * @var Settings
     */
    private $settings;
    
    /**
     * Cached settings (merged defaults + saved)
     * 
     * @var array|null
     */
    private $cached_settings = null;
    
    /**
     * Constructor
     * 
     * @param Settings $settings Settings instance
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }
    
    /**
     * Get a single setting value by key (supports dot notation)
     * 
     * @param string $key Flat key or dot notation (e.g., 'api_key' or 'social.whatsapp_bg')
     * @param mixed $default Default value if not found
     * @return mixed The setting value or default
     */
    public function get($key, $default = null) {
        $all_settings = $this->getAll();
        return DotNotation::get($all_settings, $key, $default);
    }
    
    /**
     * Get all settings (merged defaults + saved values)
     * 
     * @return array Complete settings array
     */
    public function getAll() {
        // Return cached if available
        if ($this->cached_settings !== null) {
            return $this->cached_settings;
        }
        
        // Load defaults
        $defaults = $this->getDefaults();
        
        // Load saved settings from WordPress options
        $option_key = $this->settings->getConfig('option_key');
        $saved = get_option($option_key, []);
        
        // Merge: saved values override defaults. Uses mergeSettings() rather than
        // array_replace_recursive() so numeric-list values (is_multiple checkbox
        // fields) replace the default list wholesale instead of merging by index,
        // matching the write-path semantics in save().
        $merged = $this->mergeSettings($defaults, $saved);
        
        // Cache the result
        $this->cached_settings = $merged;
        
        return $merged;
    }
    
    /**
     * Get default value for a specific key
     * 
     * @param string $key Field key (flat or dot notation)
     * @return mixed Default value or null if not found
     */
    public function getDefault($key) {
        $defaults = $this->getDefaults();
        return DotNotation::get($defaults, $key, null);
    }
    
    /**
     * Get all default settings
     * 
     * @return array Default settings array
     */
    public function getDefaults() {
        $config_defaults = $this->settings->getConfig('defaults');
        
        // If defaults provided in config, use them
        if (is_array($config_defaults) && !empty($config_defaults)) {
            return $config_defaults;
        }
        
        // Fall back to config file (for demo/testing mode)
        $config_path = $this->settings->getConfig('config_path') . '/default-settings.php';
        if (file_exists($config_path)) {
            return require $config_path;
        }
        
        // No defaults available
        return [];
    }
    
    /**
     * Check if a key exists in settings
     * 
     * @param string $key Field key (flat or dot notation)
     * @return bool True if key exists
     */
    public function has($key) {
        $all_settings = $this->getAll();
        return DotNotation::has($all_settings, $key);
    }
    
    /**
     * Save settings with dot notation support
     * 
     * @param array $data Settings array to save
     * @return array Array with 'success' (bool) and 'changed' (bool) keys
     */
    public function save($data) {
        if (!is_array($data) || empty($data)) {
            return ['success' => false, 'changed' => false];
        }
        
        // Get existing settings
        $option_key = $this->settings->getConfig('option_key');
        $existing_settings = get_option($option_key, []);
        
        // Convert dot notation keys to nested structure
        $new_settings_nested = DotNotation::expand($data);
        
        // Merge new settings with existing ones
        // IMPORTANT: For array values, completely replace them instead of merging recursively
        // This ensures that when items are removed from multi-select fields, they are actually removed
        $merged_settings = $this->mergeSettings($existing_settings, $new_settings_nested);
        
        // Sanitize the settings before saving
        $sanitized_settings = Sanitizer::sanitizeSettings($merged_settings);
        
        // Check if settings actually changed
        $settings_changed = serialize($sanitized_settings) !== serialize($existing_settings);
        
        // Update option - always call to ensure database is in sync
        update_option($option_key, $sanitized_settings);
        
        // Clear cache
        $this->cached_settings = null;
        
        return [
            'success' => true,
            'changed' => $settings_changed
        ];
    }
    
    /**
     * Merge settings arrays, replacing entire arrays instead of merging recursively
     * 
     * This ensures that when array values (like multi-select fields) are updated,
     * the old array is completely replaced, not merged. This is critical for
     * multi-select fields where removing items should actually remove them.
     * 
     * @param array $existing Existing settings
     * @param array $new New settings to merge
     * @return array Merged settings
     */
    private function mergeSettings($existing, $new) {
        $merged = $existing;
        
        foreach ($new as $key => $value) {
            // If the new value is an array and the existing value is also an array,
            // check if we should replace the entire array or merge recursively
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                // Check if this is a numeric array (list) vs associative array (object)
                // Numeric arrays (like multi-select values) should be completely replaced
                // Associative arrays should be merged recursively
                if ($this->isNumericArray($value)) {
                    // Numeric array (list) - completely replace
                    $merged[$key] = $value;
                } else {
                    // Associative array - merge recursively
                    $merged[$key] = $this->mergeSettings($merged[$key], $value);
                }
            } else {
                // Not an array, or existing value is not an array - replace directly
                $merged[$key] = $value;
            }
        }
        
        return $merged;
    }
    
    /**
     * Check if an array is numeric (list) vs associative (object)
     * 
     * @param array $array Array to check
     * @return bool True if numeric array (list)
     */
    private function isNumericArray($array) {
        if (empty($array)) {
            return true; // Empty array is considered numeric
        }
        
        // Check if all keys are numeric and sequential starting from 0
        $keys = array_keys($array);
        $expected_keys = range(0, count($array) - 1);
        
        return $keys === $expected_keys;
    }
    
    /**
     * Reset specific fields to their default values
     * 
     * @param array $keys Array of field keys to reset
     * @return array Array with 'success' (bool) and 'changed' (bool) keys
     */
    public function reset($keys) {
        if (!is_array($keys) || empty($keys)) {
            return ['success' => false, 'changed' => false];
        }
        
        // Load defaults
        $defaults = $this->getDefaults();
        
        // Get current settings
        $option_key = $this->settings->getConfig('option_key');
        $original_settings = get_option($option_key, []);
        $current_settings = $original_settings;
        
        // For each field key, reset to default value
        foreach ($keys as $key) {
            $default_value = DotNotation::get($defaults, $key, null);
            
            // Convert boolean defaults to '0'/'1' strings for consistency
            if (is_bool($default_value)) {
                $default_value = $default_value ? '1' : '0';
            }
            
            if ($default_value !== null) {
                DotNotation::set($current_settings, $key, $default_value);
            } else {
                // Remove key if no default exists
                DotNotation::set($current_settings, $key, null);
            }
        }
        
        // Check if settings actually changed
        $settings_changed = serialize($current_settings) !== serialize($original_settings);
        
        // Update option - always call to ensure database is in sync
        update_option($option_key, $current_settings);
        
        // Clear cache
        $this->cached_settings = null;
        
        return [
            'success' => true,
            'changed' => $settings_changed
        ];
    }
    
    /**
     * Reset all settings to default values
     * 
     * @return array Array with 'success' (bool) and 'changed' (bool) keys
     */
    public function resetAll() {
        // Load defaults
        $defaults = $this->getDefaults();
        
        // Get current settings
        $option_key = $this->settings->getConfig('option_key');
        $existing_settings = get_option($option_key, []);
        
        // Check if settings actually changed
        $settings_changed = serialize($defaults) !== serialize($existing_settings);
        
        // Update option - always call to ensure database is in sync
        update_option($option_key, $defaults);
        
        // Clear cache
        $this->cached_settings = null;
        
        return [
            'success' => true,
            'changed' => $settings_changed
        ];
    }
    
    /**
     * Export all settings as JSON
     * 
     * @return array Array with 'success' (bool), 'data' (array), and 'filename' (string) keys
     */
    public function export() {
        // Get all current settings (merged defaults + saved)
        $all_settings = $this->getAll();
        
        // Get option key for reference
        $option_key = $this->settings->getConfig('option_key');
        
        // Build export data with metadata
        $export_data = [
            'version' => $this->settings->getConfig('version'),
            'timestamp' => current_time('mysql'),
            'site_url' => get_site_url(),
            'option_key' => $option_key,
            'settings' => $all_settings
        ];
        
        // Generate filename
        $filename = $this->settings->getConfig('option_key') . '-export-' . date('Y-m-d-His') . '.json';
        
        return [
            'success' => true,
            'data' => $export_data,
            'filename' => $filename
        ];
    }
    
    /**
     * Import settings from JSON data
     * 
     * @param array $data Import data array (should have 'settings' key)
     * @return array Array with 'success' (bool), 'imported_count' (int), and 'message' (string) keys
     */
    public function import($data) {
        // Validate import data
        $validation = Validator::validateImportData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        $settings_to_import = $data['settings'];
        
        // Get option key
        $option_key = $this->settings->getConfig('option_key');
        
        // Sanitize imported settings
        $sanitized_settings = Sanitizer::sanitizeSettings($settings_to_import);
        
        // Save imported settings (replace all existing settings)
        $result = update_option($option_key, $sanitized_settings);
        
        // Clear cache
        $this->cached_settings = null;
        
        if ($result) {
            return [
                'success' => true,
                'imported_count' => count($sanitized_settings),
                'message' => sprintf(
                    'Successfully imported %d setting(s).',
                    count($sanitized_settings)
                )
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save imported settings'
            ];
        }
    }
}

