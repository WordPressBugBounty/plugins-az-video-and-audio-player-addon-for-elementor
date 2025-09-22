<?php
namespace VAPFEM;

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
        'autoplay' => 'vapfem_yes_no_to_bool',
        'muted' => 'vapfem_yes_no_to_bool',
        'loop' => 'vapfem_yes_no_to_bool',
        'volume' => 'vapfem_float_clamp_0_1',
        'click_to_play' => 'vapfem_yes_no_to_bool',
        'invert_time' => 'vapfem_yes_no_to_bool',
        'seek_time' => 'absint',
        'hide_controls' => 'vapfem_yes_no_to_bool',
        'reset_on_end' => 'vapfem_yes_no_to_bool',

        // Keyboard & interaction
        'keyboard_focused' => 'vapfem_yes_no_to_bool',
        'keyboard_global' => 'vapfem_yes_no_to_bool',
        'fullscreen_enabled' => 'vapfem_yes_no_to_bool',

        // Tooltips & UI
        'tooltips_controls' => 'vapfem_yes_no_to_bool',
        'tooltips_seek' => 'vapfem_yes_no_to_bool',
        'speed_selected' => 'vapfem_convert_to_float',

        // HTML5 specific
        'sources' => 'vapfem_convert_sources_to_html5_list',
        'quality_default' => 'vapfem_convert_to_int',

        // Aspect ratio
        'ratio' => 'sanitize_text_field',

        // Controls
        'controls' => 'vapfem_convert_comma_to_array',

        // Debugging
        'debug_mode' => 'vapfem_yes_no_to_bool',
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
     */
    public function cb_video_player_shortcode($user_atts) {
        if(is_admin()) {
            // Don't render in admin area
            return '';
        }

        // Ensure assets are loaded (handles all edge cases)
        Assets_Manager::get_instance()->ensure_assets_loaded();

        // Generate default attributes from attribute map (no redundancy)
        // All default attributes values are populated from player-defaults.php file incluing url & sources
        $default_atts = $this->generate_default_atts();

        // Merge user attributes with defaults
        $atts = shortcode_atts($default_atts, $user_atts, 'lean_video'); // For add_filter support

        // User defined and default attributes are map it for final config for player renderer
        $config = $this->map_shortcode_to_player_config($atts);
        $renderer = Player_Renderer::get_instance();

        ob_start();
        $renderer->render_video_player($config);
        return ob_get_clean();
    }

    /**
     * Generate default attributes for video player
     */
    private function generate_default_atts() {
        $player_defaults = vapfem_get_video_defaults();
        $default_atts = [];

        foreach ($this->attribute_map as $attr => $converter) {
            if ($attr === 'url') {
                // Special case for video URL - use fallback video
                $default_atts[$attr] = '';
            } elseif ($attr === 'type' || $attr === 'id') {
                // Manual type/ID specification fallbacks
                $default_atts[$attr] = '';
            } elseif ($attr === 'sources') {
                // Convert HTML5 video list to string format
                $default_atts[$attr] = vapfem_convert_html5_video_list_arr_to_str($player_defaults['sources']);
            } elseif ($attr === 'controls') {
                // Convert controls array to comma-separated string
                $default_atts[$attr] = implode(',', $player_defaults['controls']);
            } else {
                // Use corresponding value from player defaults
                $default_atts[$attr] = $player_defaults[$attr] ?? '';
            }
        }

        return $default_atts;
    }

    /**
     * Transform video shortcode attributes to renderer config
     */
    private function map_shortcode_to_player_config($atts) {
        // Parse video URL to determine type and ID
        $video_info = $this->parse_video_url($atts['url']);

        // Start with video source info (always required)
        $config = [
            'video_type' => $video_info['type'],
            'video_id' => $video_info['id'],
            'sources' => $video_info['sources'],
        ];

        foreach ($this->attribute_map as $attr => $converter) {
            if (isset($atts[$attr])) {
                // User provided this attribute - convert and add to config
                if (function_exists($converter)) {
                    // Use WordPress native function or global function
                    $converted_value = $converter(wp_unslash($atts[$attr]));
                } else {
                    // Fallback - shouldn't happen with global functions
                    $converted_value = sanitize_text_field(wp_unslash($atts[$attr]));
                }

                if ($converted_value !== null) {
                    $config[$attr] = $converted_value;
                }
                // If conversion fails (returns null), skip - let renderer use defaults
            }
            // If user didn't provide attribute, don't add to config - let renderer use defaults
        }

        return $config;
    }

    /**
     * Parse video URL to extract type, ID, and create HTML5 sources if needed
     */
    private function parse_video_url($url) {
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
}

// Initialize the video shortcode
Video_Shortcode::get_instance();