<?php
namespace Lex\Settings\V2\Utilities;

/**
 * Validator Utility
 * 
 * Handles validation of settings structure and import data
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validator Utility Class
 */
class Validator {
    
    /**
     * Validate import data structure
     * 
     * @param array $data Import data to validate
     * @return array Array with 'valid' (bool) and 'message' (string) keys
     */
    public static function validateImportData($data) {
        if (!is_array($data)) {
            return [
                'valid' => false,
                'message' => 'Invalid import data format'
            ];
        }
        
        // Validate structure - must have 'settings' key
        if (!isset($data['settings']) || !is_array($data['settings'])) {
            return [
                'valid' => false,
                'message' => 'Invalid file format: Missing settings data'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Import data is valid'
        ];
    }
    
    /**
     * Validate settings structure
     * 
     * @param array $settings Settings array to validate
     * @return array Array with 'valid' (bool) and 'errors' (array) keys
     */
    public static function validateSettings($settings) {
        $errors = [];
        
        if (!is_array($settings)) {
            $errors[] = 'Settings must be an array';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate field configuration
     * 
     * @param array $config Field configuration array
     * @return array Array with 'valid' (bool) and 'errors' (array) keys
     */
    public static function validateFieldConfig($config) {
        $errors = [];
        
        // Required fields
        if (empty($config['type'])) {
            $errors[] = 'Field type is required';
        }
        
        if (empty($config['key'])) {
            $errors[] = 'Field key is required';
        }
        
        // Validate field type
        $valid_types = [
            'text', 'textarea', 'select', 'checkbox', 'radio',
            'number', 'color', 'password', 'media', 'action-buttons',
            'css-border', 'css-box-model'
        ];
        
        if (!empty($config['type']) && !in_array($config['type'], $valid_types, true)) {
            $errors[] = sprintf('Invalid field type: %s', $config['type']);
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

