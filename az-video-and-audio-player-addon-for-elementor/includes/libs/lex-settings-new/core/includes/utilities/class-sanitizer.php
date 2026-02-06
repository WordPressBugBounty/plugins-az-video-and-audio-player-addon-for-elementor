<?php
namespace Lex\Settings\V2\Utilities;

/**
 * Sanitizer Utility
 * 
 * Handles sanitization of settings data based on field types
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitizer Utility Class
 */
class Sanitizer {
    
    /**
     * Sanitize settings data recursively
     * 
     * @param array $data Settings to sanitize
     * @return array Sanitized settings
     */
    public static function sanitizeSettings($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursively sanitize nested arrays
                $sanitized[$key] = self::sanitizeSettings($value);
            } else {
                // Preserve '0' and '1' strings (checkbox values)
                if ($value === '0' || $value === '1' || $value === 0 || $value === 1) {
                    $sanitized[$key] = (string) $value; // Ensure it's '0' or '1'
                } elseif (is_bool($value)) {
                    // Convert boolean to '0'/'1' string
                    $sanitized[$key] = $value ? '1' : '0';
                } else {
                    // Basic sanitization for other types
                    $sanitized[$key] = sanitize_text_field(wp_unslash($value));
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize field value by type
     * 
     * @param mixed $value Value to sanitize
     * @param string $type Field type (text, number, email, etc.)
     * @return mixed Sanitized value
     */
    public static function sanitizeField($value, $type = 'text') {
        switch ($type) {
            case 'number':
            case 'range':
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'email':
                return sanitize_email($value);
                
            case 'url':
                return esc_url_raw($value);
                
            case 'textarea':
                return sanitize_textarea_field($value);
                
            case 'checkbox':
                // Checkbox values are '0' or '1'
                return ($value === '1' || $value === 1 || $value === true) ? '1' : '0';
                
            case 'color':
                // Hex color validation
                if (preg_match('/^#[a-fA-F0-9]{6}$/', $value)) {
                    return $value;
                }
                return '#000000'; // Default fallback
                
            case 'text':
            default:
                return sanitize_text_field(wp_unslash($value));
        }
    }
}

