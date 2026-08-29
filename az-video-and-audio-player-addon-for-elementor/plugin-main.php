<?php
/**
Plugin Name: Lean Player - Video and Audio Player with Playlist for WordPress, Elementor and Gutenberg
Plugin URI: https://leanplugins.com/
Description: Video & Audio player for Elementor, Gutenberg & Classic Editor
Version: 3.3.2
Requires at least: 6.0
Requires PHP: 7.4
Author: LeanPlugins
Author URI: https://leanplugins.com/
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: vapfem
Domain Path: /languages/
*/

if (!defined('ABSPATH')) {
    exit;
}

// Both version may have this constant, so check first
if (!defined('LEANPL_VERSION')) {
    define('LEANPL_VERSION', '3.3.2');
    define('LEANPL_URI', plugins_url('', __FILE__));
    define('LEANPL_DIR', dirname(__FILE__));
    define('LEANPL_FILE', __FILE__);
    if (!defined('LEANPL_EDITION_OVERRIDE')) {
        define('LEANPL_EDITION_OVERRIDE', '');
    }
}

// Load and bootstrap plugin
require_once LEANPL_DIR . '/includes/class-base.php';
\LeanPL\Base::bootstrap(__FILE__);