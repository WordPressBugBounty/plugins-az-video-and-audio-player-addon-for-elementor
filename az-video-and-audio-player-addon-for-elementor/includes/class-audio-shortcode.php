<?php
namespace VAPFEM;

/**
 * Audio Shortcode Class
 * Handles [lean_audio] shortcode functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Audio_Shortcode {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Audio attribute mapping for shortcode
     */
    private $attribute_map = [
        // Audio source
        'url' => 'esc_url',
        'src_type' => 'sanitize_text_field',

        // Playback behavior (shared)
        'autoplay' => 'vapfem_yes_no_to_bool',
        'muted' => 'vapfem_yes_no_to_bool',
        'loop' => 'vapfem_yes_no_to_bool',
        'volume' => 'vapfem_float_clamp_0_1',
        'invert_time' => 'vapfem_yes_no_to_bool',
        'seek_time' => 'absint',

        // Keyboard & interaction (shared)
        'keyboard_focused' => 'vapfem_yes_no_to_bool',
        'keyboard_global' => 'vapfem_yes_no_to_bool',

        // Tooltips & UI (shared)
        'tooltips_seek' => 'vapfem_yes_no_to_bool',
        'speed_selected' => 'vapfem_convert_to_float',

        // Audio-specific
        'preload' => 'sanitize_text_field',

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
     * Register audio shortcode
     */
    public function register_shortcode() {
        add_shortcode('lean_audio', [$this, 'audio_player_shortcode']);
    }

    /**
     * Audio player shortcode handler
     */
    public function audio_player_shortcode($user_atts) {
        if(is_admin()) {
            // Don't render in admin area
            return '';
        }

        // Ensure assets are loaded (handles all edge cases)
        Assets_Manager::get_instance()->ensure_assets_loaded();

        // Generate default attributes from attribute map (no redundancy)
        $default_atts = $this->generate_default_atts();

        // Merge user attributes with defaults
        $atts = shortcode_atts($default_atts, $user_atts, 'lean_audio');

        // Basic validation
        if (empty($atts['url'])) {
            return '<div class="vapfem-error" style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">' . esc_html__('Audio URL is required.', 'vapfem') . '</div>';
        }

        // Convert shortcode attributes to renderer config
        $config = $this->transform_shortcode_attributes($atts);
        $renderer = Player_Renderer::get_instance();

        ob_start();
        $renderer->render_audio_player($config);
        return ob_get_clean();
    }

    /**
     * Generate default attributes for audio player
     */
    private function generate_default_atts() {
        $player_defaults = vapfem_get_audio_defaults();
        $default_atts = [];

        foreach ($this->attribute_map as $attr => $converter) {
            if ($attr === 'url') {
                // Special case for audio URL - empty by default (required)
                $default_atts[$attr] = '';
            } elseif ($attr === 'src_type') {
                // Manual src_type specification fallback
                $default_atts[$attr] = '';
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
     * Transform audio shortcode attributes to renderer config
     */
    private function transform_shortcode_attributes($atts) {
        // For audio, set the audio source directly
        $config = [
            'url' => isset($atts['url']) ? esc_url(wp_unslash($atts['url'])) : '',
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
}

// Initialize the audio shortcode
Audio_Shortcode::get_instance();