<?php
namespace LeanPL;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dual Activation Preventer
 * 
 * Prevents free and pro versions from running simultaneously
 */
class Dual_Activation_Preventer {
    
    /**
     * Ensure only one version (free or pro) is active at a time
     * Pro version always takes priority - Free is auto-deactivated when Pro is active
     */
    public static function ensure_single_activation() {
        // Load plugin.php for is_plugin_active function
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        $free_plugin = 'az-video-and-audio-player-addon-for-elementor/plugin-main.php';
        $pro_plugin = 'lean-player-pro/plugin-main.php';
        
        // If both are active, deactivate Free (Pro wins)
        if (is_plugin_active($pro_plugin) && is_plugin_active($free_plugin)) {
            deactivate_plugins($free_plugin);
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>Lean Player Pro is active.</strong> The free version was automatically turned off.</p>';
                echo '</div>';
            });
        }
    }
}

