<?php
/**
Plugin Name: AZ Video and Audio Player for Elementor, Gutenberg & Classic Editor
Plugin URI: 
Description: Video & Audio player for Elementor, Gutenberg & Classic Editor
Version: 2.1.2
Author: AZ Plugins
Author URI: 
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: vapfem
Domain Path: /languages/
*/

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define path
 */
define( 'VAPFEM_VERSION', '2.1.2' );
define( 'VAPFEM_URI', plugins_url('', __FILE__) );
define( 'VAPFEM_DIR', dirname( __FILE__ ) );

/**
 * Deactivate the pro plugin if active
 */
if ( ! function_exists('is_plugin_active') ){ 
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if( is_plugin_active('az-video-and-audio-player-for-elementor/plugin-main.php') ){
    add_action('update_option_active_plugins', function(){
        deactivate_plugins('az-video-and-audio-player-for-elementor/plugin-main.php');
    });
}

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'vapfem_plugin_activation');

/**
 * Handle plugin activation
 */
function vapfem_plugin_activation() {
    if (!get_option('vapfem_installed_time')) {
        add_option('vapfem_installed_time', time(), '', false);
    }
}

/**
 * Include all files
 */
include_once( VAPFEM_DIR. '/includes/init.php');