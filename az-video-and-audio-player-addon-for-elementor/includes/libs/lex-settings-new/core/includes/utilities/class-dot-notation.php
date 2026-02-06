<?php
namespace Lex\Settings\V2\Utilities;

/**
 * Dot Notation Utility
 * 
 * Handles parsing and manipulation of dot notation keys (e.g., 'social.whatsapp_bg')
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dot Notation Utility Class
 */
class DotNotation {
    
    /**
     * Parse dot notation key into array segments
     * 
     * @param string $key Dot notation key (e.g., 'social.whatsapp_bg')
     * @return array Array of segments (e.g., ['social', 'whatsapp_bg'])
     */
    public static function parse($key) {
        if (strpos($key, '.') === false) {
            return [$key];
        }
        return explode('.', $key);
    }
    
    /**
     * Get value from array using dot notation
     * 
     * @param array $array Array to search
     * @param string $key Dot notation key (e.g., 'social.whatsapp_bg')
     * @param mixed $default Default value if not found
     * @return mixed The value or default
     */
    public static function get($array, $key, $default = null) {
        if (strpos($key, '.') === false) {
            // Flat key - direct lookup
            return isset($array[$key]) ? $array[$key] : $default;
        }
        
        // Dot notation - traverse nested array
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    /**
     * Set value in array using dot notation
     * 
     * @param array &$array Array to modify (passed by reference)
     * @param string $key Dot notation key (e.g., 'social.whatsapp_bg')
     * @param mixed $value Value to set
     */
    public static function set(&$array, $key, $value) {
        $keys = explode('.', $key);
        $current = &$array;
        
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                // Last segment - set the value
                if ($value === null) {
                    unset($current[$segment]);
                } else {
                    $current[$segment] = $value;
                }
            } else {
                // Create nested array if doesn't exist
                if (!isset($current[$segment]) || !is_array($current[$segment])) {
                    $current[$segment] = [];
                }
                $current = &$current[$segment];
            }
        }
    }
    
    /**
     * Check if key exists in array using dot notation
     * 
     * @param array $array Array to check
     * @param string $key Dot notation key
     * @return bool True if key exists
     */
    public static function has($array, $key) {
        if (strpos($key, '.') === false) {
            return isset($array[$key]);
        }
        
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return false;
            }
            $value = $value[$segment];
        }
        
        return true;
    }
    
    /**
     * Flatten nested array to dot notation keys
     * 
     * @param array $array Nested array
     * @param string $prefix Prefix for keys (used internally for recursion)
     * @return array Flattened array with dot notation keys
     */
    public static function flatten($array, $prefix = '') {
        $result = [];
        
        foreach ($array as $key => $value) {
            $new_key = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value, $new_key));
            } else {
                $result[$new_key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Expand dot notation keys to nested array
     * 
     * @param array $array Array with dot notation keys
     * @return array Nested array structure
     */
    public static function expand($array) {
        $nested = [];
        
        foreach ($array as $key => $value) {
            if (strpos($key, '.') !== false) {
                // Convert dot notation to nested array
                self::set($nested, $key, $value);
            } else {
                // Keep flat keys as-is
                $nested[$key] = $value;
            }
        }
        
        return $nested;
    }
}

