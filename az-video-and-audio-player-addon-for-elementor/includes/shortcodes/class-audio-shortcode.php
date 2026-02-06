<?php
namespace LeanPL\Shortcodes;
use LeanPL\Assets_Manager;
use LeanPL\Player_Renderer;
use LeanPL\Config_Merger;

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
        'autoplay' => 'leanpl_normalize_boolean',
        'muted' => 'leanpl_normalize_boolean',
        'loop' => 'leanpl_normalize_boolean',
        'volume' => 'leanpl_float_clamp_0_1',
        'invert_time' => 'leanpl_normalize_boolean',
        'seek_time' => 'absint',

        // Keyboard & interaction (shared)
        'keyboard_focused' => 'leanpl_normalize_boolean',
        'keyboard_global' => 'leanpl_normalize_boolean',

        // Tooltips & UI (shared)
        'tooltips_seek' => 'leanpl_normalize_boolean',
        'speed_selected' => 'leanpl_convert_to_float',

        // Audio-specific
        'preload' => 'sanitize_text_field',

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
     * Register audio shortcode
     */
    public function register_shortcode() {
        add_shortcode('lean_audio', [$this, 'audio_player_shortcode']);
    }

    /**
     * Audio player shortcode handler
     * 
     * @param array $user_atts User-provided shortcode attributes
     * @return string Shortcode output HTML
     */
    public function audio_player_shortcode($user_atts) {
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
        // 2. Hook support: shortcode_atts_lean_audio filter
        // 3. Documentation: developers can see supported attributes via defaults array
        $atts = shortcode_atts($supported_attributes, $user_atts, 'lean_audio');

        // Validate URL first - before creating config
        $audio_url = isset($atts['url']) ? trim($atts['url']) : '';
        if (empty($audio_url)) {
            return $this->render_error('Audio URL is required.');
        }

        // Map user attributes to config
        // Only attributes that user actually provided (not empty) will be added
        // Config_Merger will handle inheritance: Instance → Global → Defaults
        $config = $this->transform_shortcode_attributes($atts);

        $renderer = Player_Renderer::get_instance();

        ob_start();
        $renderer->render_audio_player($config);
        return ob_get_clean();
    }

    /**
     * Transform audio shortcode attributes to renderer config
     * 
     * Only adds attributes that user actually provided (not empty strings).
     * Empty/null values are skipped, allowing Config_Merger to inherit from Global → Defaults.
     * This matches the pattern used in class-player-shortcode.php.
     * 
     * @param array $atts Filtered shortcode attributes (from shortcode_atts)
     * @return array Config array for player renderer
     */
    private function transform_shortcode_attributes($atts) {
        // Parse audio URL (always required - validated in handler)
        $audio_url = isset($atts['url']) ? trim($atts['url']) : '';
        
        // Start with audio source (always required)
        $config = [
            'url' => esc_url($audio_url),
        ];

        // Only add attributes that user actually provided (not empty strings)
        // Empty defaults from shortcode_atts() mean user didn't provide them
        foreach ($this->attribute_map as $attr => $converter) {
            // Skip if attribute is empty (user didn't provide it) or redundant keys
            if (!isset($atts[$attr]) || $atts[$attr] === '' || in_array($attr, ['url', 'src_type'])) {
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

// Initialize the audio shortcode
Audio_Shortcode::get_instance();