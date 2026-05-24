<?php
namespace LeanPL\Shortcodes;
use LeanPL\Assets_Manager;
use LeanPL\Player_Renderer;
use LeanPL\Config_Merger;

/**
 * Video Shortcode Class
 * Handles [lean_video] shortcode functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Video_Shortcode {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Video attribute mapping for shortcode
     * Thse supported attributes are used in the shortcode
     */
    private $attribute_map = [
        // URL and video source
        'url' => 'esc_url',
        'type' => 'sanitize_text_field',
        'id' => 'sanitize_text_field',
        'poster' => 'esc_url',

        // Playback behavior
        'autoplay' => 'leanpl_normalize_boolean',
        'muted' => 'leanpl_normalize_boolean',
        'loop' => 'leanpl_normalize_boolean',
        'preload' => 'sanitize_text_field',
        'volume' => 'leanpl_float_clamp_0_1',
        'click_to_play' => 'leanpl_normalize_boolean',
        'invert_time' => 'leanpl_normalize_boolean',
        'seek_time' => 'absint',
        'hide_controls' => 'leanpl_normalize_boolean',
        'reset_on_end' => 'leanpl_normalize_boolean',

        // Keyboard & interaction
        'keyboard_focused' => 'leanpl_normalize_boolean',
        'keyboard_global' => 'leanpl_normalize_boolean',
        'fullscreen_enabled' => 'leanpl_normalize_boolean',

        // Tooltips & UI
        'tooltips_controls' => 'leanpl_normalize_boolean',
        'tooltips_seek' => 'leanpl_normalize_boolean',
        'speed_selected' => 'leanpl_convert_to_float',

        // HTML5 specific
        'sources' => 'leanpl_convert_sources_to_html5_list',
        'quality_default' => 'leanpl_convert_to_int',

        // Aspect ratio
        'ratio' => 'sanitize_text_field',

        // Controls
        'controls' => 'leanpl_convert_comma_to_array',

        // Debugging
        'debug_mode' => 'leanpl_normalize_boolean',
    ];

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize shortcode
     */
    public function init() {
        add_action('init', [$this, 'register_shortcode']);
    }

    /**
     * Register video shortcode
     */
    public function register_shortcode() {
        add_shortcode('lean_video', [$this, 'cb_video_player_shortcode']);
    }

    /**
     * Video player shortcode handler
     * 
     * @param array $user_atts User-provided shortcode attributes
     * @return string Shortcode output HTML
     */
    public function cb_video_player_shortcode($user_atts) {
        if (is_admin() && !leanpl_is_elementor_editor()) {
            // Don't render in admin area except in Elementor editor
            return '';
        }

        // Ensure assets are loaded even used with do_shortcode()
        if( !wp_script_is('leanpl-main', 'enqueued') ){
            Assets_Manager::get_instance()->load_assets_by_context('frontend');
        }

        // Get all supported attribute keys from attribute_map (for filtering & documentation)
        // Empty defaults = no actual default values merged, just filtering unknown attributes
        $supported_attributes = array_fill_keys(array_keys($this->attribute_map), '');
        
        // Add storage_enabled (exists in defaults but not in attribute_map)
        $supported_attributes['storage_enabled'] = '';
        
        // Use shortcode_atts for:
        // 1. Filtering unknown attributes (WordPress best practice)
        // 2. Hook support: shortcode_atts_lean_video filter
        // 3. Documentation: developers can see supported attributes via defaults array
        $atts = shortcode_atts($supported_attributes, $user_atts, 'lean_video');

        // Map user attributes to config
        // Only attributes that user actually provided (not empty) will be added
        // Config_Merger will handle inheritance: Instance → Global → Defaults
        $config = $this->map_shortcode_to_player_config($atts);

        $renderer = Player_Renderer::get_instance();

        ob_start();
        $renderer->render_video_player($config);
        return ob_get_clean();
    }

    /**
     * Transform video shortcode attributes to renderer config
     * 
     * Only adds attributes that user actually provided (not empty strings).
     * Empty/null values are skipped, allowing Config_Merger to inherit from Global → Defaults.
     * This matches the pattern used in class-player-shortcode.php.
     * 
     * @param array $atts Filtered shortcode attributes (from shortcode_atts)
     * @return array Config array for player renderer
     */
    private function map_shortcode_to_player_config($atts) {
        $video_url  = isset($atts['url'])  ? trim($atts['url'])                    : '';
        $type_hint  = isset($atts['type']) ? sanitize_text_field($atts['type'])    : '';

        // When the user passes a bare ID (no URL pattern) + type="youtube"|"vimeo",
        // treat the url value as the ID directly instead of running URL parsing.
        if ( $type_hint && ! preg_match('/[.\/]/', $video_url) ) {
            $video_info = [
                'type'    => $type_hint,
                'id'      => $video_url,
                'sources' => [],
            ];
        } else {
            $video_info = leanpl_parse_video_url($video_url);
        }

        // Start with video source info (always required)
        $config = [
            'video_type' => $video_info['type'],
            'video_id'   => $video_info['id'],
            'sources'    => $video_info['sources'],
        ];

        // Only add attributes that user actually provided (not empty strings)
        // Empty defaults from shortcode_atts() mean user didn't provide them
        foreach ($this->attribute_map as $attr => $converter) {
            // Skip if attribute is empty (user didn't provide it) or redundant keys
            if (!isset($atts[$attr]) || $atts[$attr] === '' || in_array($attr, ['url', 'type', 'id'])) {
                continue;
            }

            // User provided this attribute - convert and add to config
            if (function_exists($converter)) {
                $converted_value = $converter(wp_unslash($atts[$attr]));
            } else {
                // Fallback - shouldn't happen with global functions
                $converted_value = sanitize_text_field(wp_unslash($atts[$attr]));
            }

            // Only add if conversion succeeded
            if ($converted_value !== null) {
                $config[$attr] = $converted_value;
            }
        }

        // Storage (not in attribute_map but exists in defaults)
        if (isset($atts['storage_enabled']) && $atts['storage_enabled'] !== '') {
            $config['storage_enabled'] = leanpl_normalize_boolean($atts['storage_enabled']);
        }

        return $config;
    }

    /**
     * Render error message
     * 
     * @param string $message Error message
     * @return string Error HTML
     */
    private function render_error($message) {
        return sprintf(
            '<div class="lpl-error" style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">%s</div>',
            esc_html($message)
        );
    }
}

// Initialize the video shortcode
Video_Shortcode::get_instance();
