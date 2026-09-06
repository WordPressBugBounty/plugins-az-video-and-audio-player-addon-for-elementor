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
	if ( isset( $_GET['lpl_test'] ) ) {
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
 * Resolve the plugin edition (free or pro)
 *
 * The only place these rules live. Resolved once per request and cached, so
 * every caller sees the same answer no matter when it asks. Do not call this
 * directly for gating, use leanpl_should_load_pro() / leanpl_is_pro_active().
 *
 * @return string 'pro' or 'free'
 */
function leanpl_detect_edition() {
    static $edition = null;

    if ($edition !== null) {
        return $edition;
    }

    // 1. No pro code shipped, nothing else can make this pro. Keeps the answer
    //    honest in the wp.org build, where .distignore strips pro/ but the
    //    override below still ships whatever the repo had.
    if (!file_exists(LEANPL_DIR . '/pro/pro-loader.php')) {
        return $edition = 'free';
    }

    // 2. Standalone PRO build (folder has -pro suffix)
    if (strpos(plugin_basename(LEANPL_FILE), '-pro/') !== false) {
        return $edition = 'pro';
    }

    // 3. Development: check the override
    if (defined('LEANPL_EDITION_OVERRIDE') && LEANPL_EDITION_OVERRIDE === 'pro') {
        return $edition = 'pro';
    }

    // 4. Default: FREE mode
    return $edition = 'free';
}

/**
 * Check if pro code should load
 *
 * The single edition predicate. Safe to call at any point after the plugin
 * file has defined LEANPL_DIR / LEANPL_FILE, including inside bootstrap.
 *
 * @return bool True if this install runs in pro mode
 */
function leanpl_should_load_pro() {
    return leanpl_detect_edition() === 'pro';
}

/**
 * Check if pro version is active
 *
 * Feature-gating name for leanpl_should_load_pro(). Same answer, reads better
 * at call sites that gate a feature rather than a require.
 *
 * @return bool True if pro is active, false otherwise
 */
function leanpl_is_pro_active() {
    return leanpl_should_load_pro();
}

function leanpl_ssot( $section, $key = null ) {
	static $data = null;
	if ( $data === null ) {
		$data = include LEANPL_DIR . '/includes/ssot.php';
	}
	if ( $key === null ) {
		return $data[ $section ] ?? [];
	}
	return $data[ $section ][ $key ] ?? null;
}

/**
 * Keys that only pro users may override via shortcode attributes.
 *
 * For free users, any key in this list is silently dropped in
 * Config::clean_attr_overrides() and falls back to post meta or global default.
 * Pro users pass all keys through. New free keys added to defaults automatically
 * work without updating this list.
 *
 * @return string[]
 */
function leanpl_get_playlist_pro_shortcode_keys(): array {
    return [
        'item_template',
        'grid_columns',
        'skin',
        'accent_color',
        'bg_style',
        'gradient_css',
        'play_icon',
        'now_playing_style',
        'start_item',
        'auto_thumbnail',
    ];
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

/**
 * Get all plugin page identifiers (single source of truth)
 * 
 * Returns all hooks, screen IDs, page slugs, and post types
 * that belong to this plugin's admin pages.
 * 
 * @return array Array with keys:
 *   - 'hooks' (array): WordPress hook suffixes (e.g., 'toplevel_page_lean-player')
 *   - 'page_slugs' (array): Page slugs from $_GET['page'] (e.g., 'lean-player')
 *   - 'post_types' (array): Custom post types (e.g., 'lean_player')
 *   - 'screen_ids' (array): Screen IDs (e.g., 'lean_player', 'toplevel_page_lean-player')
 */
function leanpl_get_our_page_identifiers() {
    $identifiers = array(
        // Post types
        'post_types' => array(
            'lean_player',
            'lean_playlist',
        ),
        
        // Page slugs (from $_GET['page'])
        'page_slugs' => array(
            'lean-player',
            'lean_player-hire-me',
            'lean-player-settings',
            'lean-player-playlist',
            'lean_player-all-players-new',
            'lean-player-edit',
            'lean-playlist-edit',
            // 'lean_player-license' removed - added via filter hook from pro folder
        ),

        // Hook suffixes. Only the top-level page itself gets 'toplevel_page_{slug}';
        // every real submenu under it gets '{parent_slug}_page_{submenu_slug}'
        // (verified via get_plugin_page_hookname() against the actual add_submenu_page()
        // calls in class-menu.php - WP does NOT use 'toplevel_page_' for submenus).
        // The URL-only pages registered by Menu::register_url_only_page() have no
        // parent at all, so they get the parentless 'admin_page_{slug}' form.
        'hooks' => array(
            'toplevel_page_lean-player',                        // Main menu / Media Players landing
            'lean-player_page_lean-player-settings',            // Settings submenu
            'lean-player_page_lean-player-playlist',            // Playlist submenu
            'lean-player_page_lean_player-hire-me',             // Hire Me submenu (legacy slug kept)
            'admin_page_lean_player-all-players-new',           // All Players (URL-only page)
            'admin_page_lean-player-edit',                      // Edit Player (URL-only page)
            'admin_page_lean-playlist-edit',             // Edit Playlist (URL-only page)
        ),

        // Screen IDs (mirror hook suffixes for admin pages)
        'screen_ids' => array(
            'lean_player',                                      // Post type screen
            'toplevel_page_lean-player',                        // Main menu page screen
            'lean-player_page_lean-player-settings',            // Settings page screen
            'lean-player_page_lean-player-playlist',            // Playlist page screen
            'lean-player_page_lean_player-hire-me',             // Hire Me page screen (legacy slug)
            'admin_page_lean_player-all-players-new',           // All Players page screen (URL-only)
            'admin_page_lean-player-edit',                      // Edit Player page screen (URL-only)
            'admin_page_lean-playlist-edit',             // Edit Playlist page screen (URL-only)
        ),
    );
    
    /**
     * Filters plugin page identifiers
     * 
     * Allows pro features and extensions to add their own page identifiers
     * (page slugs, hooks, screen IDs, post types)
     *
     * @since 3.0.0
     * @param array $identifiers Array with keys: 'post_types', 'page_slugs', 'hooks', 'screen_ids'
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

    $base_url = leanpl_ssot( 'brand', 'upgrade_base_url' );

    $url = $base_url . '?utm_source=' . esc_attr($args['utm_source']) . '&utm_medium=' . esc_attr($args['utm_medium']) . '&utm_campaign=' . esc_attr($args['utm_campaign']);

    if(!empty($args['scroll_to'])){
        $url .= $args['scroll_to'];
    }

    return $url;
}