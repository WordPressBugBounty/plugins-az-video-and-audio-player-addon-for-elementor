<?php
namespace Lex\Settings\V2\Utilities;

/**
 * Security Utility
 * 
 * Handles security checks (nonces, capabilities)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security Utility Class
 */
class Security {
    
    /**
     * Verify nonce
     * 
     * @param string $nonce Nonce value
     * @param string $action Nonce action
     * @return bool True if nonce is valid
     */
    public static function verifyNonce($nonce, $action) {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Check user capability
     * 
     * @param string $capability Required capability
     * @return bool True if user has capability
     */
    public static function checkCapability($capability = 'manage_options') {
        return current_user_can($capability);
    }
    
    /**
     * Verify request (nonce + capability)
     * 
     * This method will send JSON error and exit if verification fails.
     * 
     * @param string $nonce Nonce value (from POST)
     * @param string $nonce_name Nonce parameter name in POST
     * @param string $nonce_action Nonce action
     * @param string $capability Required capability
     * @return bool True if verification passes (will exit if fails)
     */
    public static function verifyRequest($nonce, $nonce_name, $nonce_action, $capability = 'manage_options') {
        // Verify nonce
        if (!check_ajax_referer($nonce_action, $nonce_name, false)) {
            wp_send_json_error(['message' => __('Security check failed', 'lex-settings')]);
            exit;
        }
        
        // Check user capabilities
        if (!self::checkCapability($capability)) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'lex-settings')]);
            exit;
        }
        
        return true;
    }
}

