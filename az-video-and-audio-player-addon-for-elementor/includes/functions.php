<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if debug mode is enabled
 * @return bool True if debug mode is enabled
 */
function leanpl_is_debug_mode() {
	if( defined( 'WP_DEBUG' ) && WP_DEBUG ){
		return true;
	}

	return false;
}

/**
 * Check if Lean Player test mode is enabled via query parameter
 * Usage: Add ?lpl_debug=1 to any URL to enable debug output
 * 
 * @return bool True if lpl_debug=1 is in the query string
 */
function leanpl_is_test_mode() {
	if ( isset( $_GET['lean_debug'] ) ) {
		return true;
	}
	
	return false;
}

/**
 * Get the plugin installation timestamp
 * 
 * Checks both new and old option keys for backward compatibility.
 * If old key exists but new doesn't, migrates the value to the new key.
 * 
 * @return int|null Unix timestamp or null if never installed
 */
function leanpl_get_installed_time() {
    // First old option key
    $timestamp = get_option('vapfem_installed_time');
    
    // If not found, check new option key
    if (!$timestamp) {
        $timestamp = get_option('leanpl_installed_time');
    }
    
    // Not installed yet? Return null
    if (!$timestamp) {
        return null;
    }
    
    // Return Unix timestamp
    return (int) $timestamp;
}

/**
 * Check if pro version is active
 * 
 * Respects LEANPL_MODE toggle for testing
 * 
 * @return bool True if pro is active, false otherwise
 */
function leanpl_is_pro_active() {
    // If in free mode, always return false
    if (defined('LEANPL_MODE') && LEANPL_MODE === 'free') {
        return false;
    }
    
    // Check if pro file exists and is loaded
    return file_exists(LEANPL_DIR . '/pro/pro-loader.php') && 
           function_exists('leanpl_pro_init');
}

/**
 * Get the plugin version for cache busting
 * 
 * @return string The plugin version
 */
function leanpl_get_version() {
	$version = LEANPL_VERSION;

	if( leanpl_is_debug_mode() ){
		$version .= '-' . time();
	}

	return $version;
}

/**
 * Check if we're in Elementor editor mode
 * 
 * @return bool True if we're in Elementor editor mode, false otherwise
 */
function leanpl_is_elementor_editor() {
    $is_elementor_editor = false;

    if ( did_action( 'elementor/loaded' ) 
        && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
        $is_elementor_editor = true;

    }

    return $is_elementor_editor;
}

function leanpl_get_player_defaults(){
    return include LEANPL_DIR . '/includes/player-defaults.php';
}

function leanpl_get_video_defaults(){
    $defaults = include LEANPL_DIR . '/includes/player-defaults.php';
    return array_merge($defaults['shared'], $defaults['video']);
}

function leanpl_get_audio_defaults(){
    $defaults = include LEANPL_DIR . '/includes/player-defaults.php';
    return array_merge($defaults['shared'], $defaults['audio']);
}

/**
 * Get all plugin page identifiers (single source of truth)
 * 
 * Returns all hooks, screen IDs, page slugs, and post types
 * that belong to this plugin's admin pages.
 * 
 * @return array Array with keys:
 *   - 'hooks' (array): WordPress hook suffixes (e.g., 'lean_player_page_lean_player-settings')
 *   - 'page_slugs' (array): Page slugs from $_GET['page'] (e.g., 'lean_player-settings')
 *   - 'post_types' (array): Custom post types (e.g., 'lean_player')
 *   - 'screen_ids' (array): Screen IDs (e.g., 'lean_player', 'lean_player_page_lean_player-settings')
 *   - 'wildcards' (array): Wildcard patterns (e.g., 'lean_player-*')
 */
function leanpl_get_our_page_identifiers() {
    $identifiers = array(
        // Post types
        'post_types' => array(
            'lean_player',
        ),
        
        // Page slugs (from $_GET['page'])
        'page_slugs' => array(
            'lean_player-settings',
            'lean_player-hire-me',
            // 'lean_player-license' removed - added via filter hook from pro folder
        ),
        
        // Hook suffixes (format: 'parent_page_slug')
        // For submenu pages under post type: '{post_type}_page_{page-slug}'
        'hooks' => array(
            'lean_player_page_lean_player-settings',  // Settings submenu
            'lean_player_page_lean_player-hire-me',    // Hire Me submenu
        ),
        
        // Screen IDs (usually same as hooks or post types)
        'screen_ids' => array(
            'lean_player',                                    // Post type screen
            'lean_player_page_lean_player-settings',         // Settings page screen
            'lean_player_page_lean_player-hire-me',          // Hire Me page screen
        ),
        
        // Wildcard patterns (for flexible matching)
        'wildcards' => array(
            'lean_player-*',  // Matches any page starting with 'lean_player-'
        ),
    );
    
    /**
     * Filters plugin page identifiers
     * 
     * Allows pro features and extensions to add their own page identifiers
     * (page slugs, hooks, screen IDs, post types, wildcards)
     * 
     * @since 3.0.0
     * @param array $identifiers Array with keys: 'post_types', 'page_slugs', 'hooks', 'screen_ids', 'wildcards'
     * @return array Modified identifiers array
     */
    return apply_filters('leanpl/admin/page_identifiers', $identifiers);
}

/**
 * Check if current admin page belongs to this plugin
 * 
 * Single source of truth for checking if we're on any plugin page.
 * Supports multiple identifier types: hooks, page slugs, screen IDs, post types, and wildcards.
 * 
 * @param string|null $hook Optional hook suffix to check (if null, uses current page context)
 * @return bool True if current page is one of our plugin pages
 */
function leanpl_is_our_admin_page($hook = null) {
    // Get all our page identifiers
    $identifiers = leanpl_get_our_page_identifiers();
    
    // Get current page context
    $screen = get_current_screen();
    
    // Get hook suffix - prefer provided hook, then try global, then screen base
    $current_hook = $hook;
    if ($current_hook === null) {
        // Try to get from global (set by WordPress during admin_enqueue_scripts)
        $current_hook = isset($GLOBALS['hook_suffix']) ? $GLOBALS['hook_suffix'] : '';
        
        // If still empty and we have a screen, try to construct from screen
        if (empty($current_hook) && $screen) {
            // For submenu pages, WordPress uses format: {parent}_page_{slug}
            if (!empty($screen->base) && $screen->base === 'post' && !empty($screen->post_type)) {
                // Post type edit screens
                $current_hook = $screen->post_type;
            } elseif (!empty($screen->base) && strpos($screen->base, '_page_') !== false) {
                // Submenu pages
                $current_hook = $screen->base;
            }
        }
    }
    
    $screen_id = $screen ? $screen->id : '';
    $page_slug = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    $post_type = $screen ? $screen->post_type : '';
    
    // Check post types
    if (!empty($post_type) && in_array($post_type, $identifiers['post_types'], true)) {
        return true;
    }

    
    
    // Check page slugs
    if (!empty($page_slug) && in_array($page_slug, $identifiers['page_slugs'], true)) {
        return true;
    }
    
    // Check hooks
    if (!empty($current_hook) && in_array($current_hook, $identifiers['hooks'], true)) {
        return true;
    }
    
    // Check screen IDs
    if (!empty($screen_id) && in_array($screen_id, $identifiers['screen_ids'], true)) {
        return true;
    }
    
    // Check wildcard patterns
    foreach ($identifiers['wildcards'] as $pattern) {
        if (strpos($pattern, '*') !== false) {
            $regex = '/^' . str_replace(['*', '/'], ['.*', '\/'], preg_quote($pattern, '/')) . '$/';
            if (preg_match($regex, $current_hook) || 
                preg_match($regex, $screen_id) || 
                preg_match($regex, $page_slug)) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get a setting value from plugin settings
 * 
 * @param string|null $key Flat key or dot notation (e.g., 'section.field'). Null returns all settings.
 * @param mixed $default Default value if not found
 * @return mixed The setting value, all settings (if key is null), or default
 */
function leanpl_get_option($key = null, $default = null) {
    // Get all settings from WordPress options
    $all_settings = get_option('leanpl_settings', []);
    
    // No key? Return everything
    if ($key === null) {
        return $all_settings;
    }
    
    // Simple key (no dots)? Direct lookup
    if (strpos($key, '.') === false) {
        return isset($all_settings[$key]) ? $all_settings[$key] : $default;
    }
    
    // Dot notation: traverse the array
    $keys = explode('.', $key);
    $value = $all_settings;
    
    foreach ($keys as $segment) {
        // Not an array or key doesn't exist? Return default
        if (!is_array($value) || !isset($value[$segment])) {
            return $default;
        }
        // Go deeper
        $value = $value[$segment];
    }
    
    return $value;
}

/**
 * Shortcode Converter Functions
 * Shared functionality for both video and audio shortcodes
 */

/**
 * Normalize boolean values with smart validation
 * Handles:
 * - Boolean: true/false
 * - String: 'true'/'false', '1'/'0', 'yes'/'no', 'on'/'off'
 * - Numeric: 1/0
 * 
 * @param mixed $value Value to normalize
 * @return bool|null Normalized boolean or null if invalid/empty
 */
function leanpl_normalize_boolean($value) {
    // Already boolean - return as-is
    if (is_bool($value)) {
        return $value;
    }

    // Null or empty string - return null (let caller decide fallback)
    if ($value === null || $value === '') {
        return null;
    }

    // String values
    if (is_string($value)) {
        $lower = strtolower(trim($value));

        // True values
        if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
            return true;
        }

        // False values
        if (in_array($lower, ['false', '0', 'no', 'off'], true)) {
            return false;
        }
    }

    // Numeric values
    if (is_numeric($value)) {
        if ((int)$value === 1) {
            return true;
        }
        if ((int)$value === 0) {
            return false;
        }
    }

    // Invalid value - return null
    return null;
}

/**
 * Convert and clamp volume to 0-1 range with validation
 * Supports both 0-1 (legacy) and 0-100 (new) formats
 */
function leanpl_float_clamp_0_1($value) {
    $float = floatval($value);

    // If value is > 1, assume it's 0-100 format and convert to 0-1
    if ($float > 1) {
        $float = $float / 100;
    }

    // Clamp to valid range (0-1)
    if ($float < 0 || $float > 1) {
        $float = max(0, min(1, $float));
    }

    return $float;
}

/**
 * Convert comma-separated string to array for controls
 * Example: "play,fullscreen" → ['play', 'fullscreen']
 */
function leanpl_convert_comma_to_array($value) {
    if (empty($value)) {
        return null; // Let renderer use defaults
    }

    $sanitized = sanitize_text_field(wp_unslash($value));
    $array = array_map('trim', explode(',', $sanitized));

    // Remove empty values
    $array = array_filter($array);

    if (empty($array)) {
        return null;
    }

    return $array;
}

/**
 * Convert sources string to HTML5 video list format
 * Example: "video1.mp4|720,video2.mp4|1080" → [['url' => 'video1.mp4', 'size' => '720'], ...]
 */
function leanpl_convert_sources_to_html5_list($value) {
    if (empty($value)) {
        return null; // Let renderer use defaults
    }

    $sanitized = sanitize_text_field(wp_unslash($value));
    $sources = array_map('trim', explode(',', $sanitized));
    $html5_list = [];

    foreach ($sources as $source) {
        if (empty($source)) continue;

        // Parse format: "url|size" or just "url"
        $parts = explode('|', $source);
        $url = trim($parts[0]);
        $size = isset($parts[1]) ? trim($parts[1]) : '';

        if (!empty($url)) {
            $html5_list[] = [
                'url' => esc_url($url),
                'size' => $size
            ];
        }
    }

    if (empty($html5_list)) {
        return null;
    }

    return $html5_list;
}

/**
 * Convert string to float with validation
 */
function leanpl_convert_to_float($value) {
    $float = floatval(sanitize_text_field(wp_unslash($value)));
    return $float;
}

/**
 * Convert string to integer with validation
 */
function leanpl_convert_to_int($value) {
    $int = intval(sanitize_text_field(wp_unslash($value)));
    return $int;
}

/**
 * Convert HTML5 video list from array to comma-separated string
 *
 * @param array $sources Array of ['url' => ..., 'size' => ...] items
 * @return string Comma-separated string of URL|size pairs
 */
function leanpl_convert_html5_video_list_arr_to_str($sources) {
    $pairs = [];
    foreach ($sources as $video) {
        if (!empty($video['url'])) {
            $size_part = !empty($video['size']) ? '|' . $video['size'] : '';
            $pairs[] = $video['url'] . $size_part;
        }
    }
    return implode(',', $pairs);
}

/**
 * Parse video URL to extract type, ID, and create HTML5 sources if needed
 * 
 * Shared utility function for video URL parsing
 * Used by both Video_Shortcode and Player_Shortcode classes
 * 
 * @param string $url Video URL
 * @return array Video info with type, id, and sources
 */
function leanpl_parse_video_url($url) {
    $url = sanitize_text_field(wp_unslash($url));

    // YouTube patterns
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        return [
            'type' => 'youtube',
            'id' => $matches[1],
            'sources' => []
        ];
    }

    // Vimeo patterns
    if (preg_match('/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|)(\d+)(?:|\/\?))/', $url, $matches)) {
        return [
            'type' => 'vimeo',
            'id' => $matches[1],
            'sources' => []
        ];
    }

    // HTML5 - direct video file
    if (preg_match('/\.(mp4|webm|ogg)$/i', $url)) {
        return [
            'type' => 'html5',
            'id' => '',
            'sources' => [
                [
                    'url' => esc_url($url),
                    'size' => '' // No quality specified for single file
                ]
            ]
        ];
    }

    // Default to HTML5 if no pattern matches
    return [
        'type' => 'html5',
        'id' => '',
        'sources' => [
            [
                'url' => esc_url($url),
                'size' => ''
            ]
        ]
    ];
}

/**
 * Get media filename from attachment ID
 * 
 * @param int $attachment_id Media attachment ID
 * @return string Filename or empty string
 */
function leanpl_get_media_filename($attachment_id) {
    if (!$attachment_id || !is_numeric($attachment_id)) {
        return '';
    }
    
    $file_path = get_attached_file($attachment_id);
    if (!$file_path) {
        return '';
    }
    
    return basename($file_path);
}

/**
 * Get human-readable player type label
 * 
 * @param string $player_type 'video' or 'audio'
 * @return string 'Video' or 'Audio'
 */
function leanpl_get_player_type_label($player_type) {
    if ($player_type === 'audio') {
        return __('Audio', 'vapfem');
    }
    return __('Video', 'vapfem');
}

/**
 * Get source type label for a player
 * 
 * @param int $post_id Post ID
 * @return string Source type label (YouTube, Vimeo, HTML5 Video, Audio File)
 */
function leanpl_get_source_type_label($post_id) {
    $player_type = \LeanPL\Metaboxes::get_field_value($post_id, '_player_type');
    
    if ($player_type === 'audio') {
        // Check if uploaded or external
        $audio_source_type = \LeanPL\Metaboxes::get_field_value($post_id, '_audio_source_type');
        
        if ($audio_source_type === 'upload') {
            return __('Uploaded File', 'vapfem');
        } elseif ($audio_source_type === 'link') {
            return __('External File', 'vapfem');
        }
        
        // Fallback for old data that might not have source type
        return __('Audio File', 'vapfem');
    }
    
    // For video, check video type
    $video_type = \LeanPL\Metaboxes::get_field_value($post_id, '_video_type');
    
    switch ($video_type) {
        case 'youtube':
            return __('YouTube', 'vapfem');
        case 'vimeo':
            return __('Vimeo', 'vapfem');
        case 'html5':
            // Check if uploaded or external
            $html5_source_type = \LeanPL\Metaboxes::get_field_value($post_id, '_html5_source_type');
            
            if ($html5_source_type === 'upload') {
                return __('Uploaded File', 'vapfem');
            } elseif ($html5_source_type === 'link') {
                return __('External File', 'vapfem');
            }
            
            // Fallback for old data
            return __('HTML5 Video', 'vapfem');
        default:
            return __('YouTube', 'vapfem'); // Default fallback
    }
}

/**
 * Get upgrade URL
 * 
 * @return string Upgrade URL
 */
function leanpl_get_upgrade_url($args = []) {
    $defaults = [
        'utm_source' => 'plugin-free',
        'utm_medium' => 'modal',
        'utm_campaign' => 'upgrade',
        'scroll_to' => '#pricing'
    ];

    $args = wp_parse_args($args, $defaults);

    $base_url = 'https://leanplugins.com/wordpress-plugins/video-and-audio-player/';

    $url = $base_url . '?utm_source=' . esc_attr($args['utm_source']) . '&utm_medium=' . esc_attr($args['utm_medium']) . '&utm_campaign=' . esc_attr($args['utm_campaign']);

    if(!empty($args['scroll_to'])){
        $url .= $args['scroll_to'];
    }

    return $url;
}