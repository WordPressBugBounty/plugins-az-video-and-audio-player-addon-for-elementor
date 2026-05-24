<?php
namespace LeanPL\Shortcodes;
use LeanPL\Assets_Manager;
use LeanPL\Player_Renderer;
use LeanPL\Config_Merger;
use LeanPL\Metaboxes;

/**
 * Player Shortcode Class
 * Handles [lean_player] shortcode functionality
 * Renders players from lean_player post type
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Player_Shortcode {

    /**
     * Instance of this class
     */
    private static $instance = null;

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
     * Register player shortcode
     */
    public function register_shortcode() {
        add_shortcode('lean_player', [$this, 'render_player_shortcode']);
    }

    /**
     * Player shortcode handler
     * 
     * Usage:
     * [lean_player id="123"]
     * 
     * @param array $atts Shortcode attributes
     * @param string|null $content Shortcode content (unused)
     * @return string Shortcode output
     */
    public function render_player_shortcode($atts, $content = null) {
        global $wp_scripts;
        
        if (is_admin() && !leanpl_is_elementor_editor()) {
            // Don't render in admin area except in Elementor editor
            return '';
        }

        // Ensure assets are loaded even used with do_shortcode()
        if( !wp_script_is('leanpl-main', 'enqueued') ){
            Assets_Manager::get_instance()->load_assets_by_context('frontend');
        }

        // Get post ID from attributes
        $post_id = $this->get_post_id($atts);
        
        if (!$post_id) {
            return $this->render_error(__('Player ID is required. Usage: [lean_player id="123"]', 'vapfem'));
        }

        // Get the post
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'lean_player') {
            return $this->render_error(__('Player not found.', 'vapfem'));
        }

        // Player type must be set
        $player_type = Metaboxes::get_field_value($post_id, '_player_type');
        if (!$player_type) {
            return $this->render_error(__('Player type is not set.', 'vapfem'));
        }

        // Prepare instance config from post meta
        $config = [];

        $config['autoplay'] = Metaboxes::get_field_value($post_id, '_autoplay');
        $config['muted'] = Metaboxes::get_field_value($post_id, '_muted');
        $config['loop'] = Metaboxes::get_field_value($post_id, '_loop');
        $config['volume'] = Metaboxes::get_field_value($post_id, '_volume');
        $config['invert_time'] = Metaboxes::get_field_value($post_id, '_invert_time');
        $config['seek_time'] = Metaboxes::get_field_value($post_id, '_seek_time');
        $config['tooltips_seek'] = Metaboxes::get_field_value($post_id, '_tooltips_seek');
        $config['speed_selected'] = Metaboxes::get_field_value($post_id, '_speed_selected');
        $config['storage_enabled'] = Metaboxes::get_field_value($post_id, '_storage_enabled');
        $config['video_type'] = Metaboxes::get_field_value($post_id, '_video_type');
        $config['poster'] = Metaboxes::get_field_value($post_id, '_poster');
        $config['click_to_play'] = Metaboxes::get_field_value($post_id, '_click_to_play');
        $config['fullscreen_enabled'] = Metaboxes::get_field_value($post_id, '_fullscreen_enabled');
        $config['hide_controls'] = Metaboxes::get_field_value($post_id, '_hide_controls');
        $config['reset_on_end'] = Metaboxes::get_field_value($post_id, '_reset_on_end');
        $config['tooltips_controls'] = Metaboxes::get_field_value($post_id, '_tooltips_controls');
        $config['preload'] = Metaboxes::get_field_value($post_id, '_preload');
        
        // Per-player controls (empty array means inherit from global)
        // If empty array or not set, controls will inherit from global/defaults via Config_Merger
        $per_player_controls = Metaboxes::get_field_value($post_id, '_controls');
        if (is_array($per_player_controls) && !empty($per_player_controls)) {
            $config['controls'] = $per_player_controls;
        }
        
        // Special processing (not direct match)
        if ($player_type === 'video') {
            $this->process_video_source($post_id, $config);
        } elseif ($player_type === 'audio') {
            $this->process_audio_source($post_id, $config);
        }

        // Process poster
        $config['poster'] = $this->process_poster($post_id);

        // Audio title shown alongside poster (only when poster is set)
        if ($player_type === 'audio' && !empty($config['poster'])) {
            $config['audio_title'] = get_the_title($post_id);
        }

        $renderer = Player_Renderer::get_instance();

        ob_start();
        
        if ($player_type === 'audio') {
            $renderer->render_audio_player($config);
        } else {
            $renderer->render_video_player($config);
        }
        
        return ob_get_clean();
    }

    /**
     * Get post ID from shortcode attributes
     * 
     * @param array $atts Shortcode attributes
     * @return int|null Post ID or null
     */
    private function get_post_id($atts) {
        // Get post ID from attributes
        if (isset($atts['id'])) {
            $post_id = absint($atts['id']);
            return $post_id > 0 ? $post_id : null;
        }

        return null;
    }

    /**
     * Process poster field
     * 
     * @param int $post_id Post ID
     * @return string|null Poster URL or null
     */
    private function process_poster($post_id) {
        $poster_id = Metaboxes::get_field_value($post_id, '_poster');
        
        if (!$poster_id) {
            return null;
        }

        // If numeric, it's an attachment ID
        if (is_numeric($poster_id)) {
            return wp_get_attachment_image_url($poster_id, 'full');
        }

        // Already a URL
        return esc_url_raw($poster_id);
    }

    /**
     * Process audio source field
     * 
     * @param int $post_id Post ID
     * @param array &$config Config array (passed by reference)
     * @return void
     */
    private function process_audio_source($post_id, &$config) {
        // Get audio source type (raw post meta - Config_Merger will handle priority)
        $audio_source_type = get_post_meta($post_id, '_audio_source_type', true);
        if (!$audio_source_type) {
            $audio_source_type = 'upload';
        }

        $audio_url = '';

        // Get audio URL based on source type
        if ($audio_source_type === 'upload') {
            // Media library upload
            $audio_source = get_post_meta($post_id, '_audio_source', true);
            if ($audio_source) {
                $audio_url = is_numeric($audio_source) 
                    ? wp_get_attachment_url($audio_source) 
                    : esc_url_raw($audio_source);
            }
        } else {
            // Direct URL (CDN/link)
            $html5_audio_url = get_post_meta($post_id, '_html5_audio_url', true);
            if ($html5_audio_url) {
                $audio_url = esc_url_raw($html5_audio_url);
            }
        }

        if ($audio_url) {
            $config['url'] = $audio_url;
        }
    }

    /**
     * Process video source fields
     * 
     * @param int $post_id Post ID
     * @param array &$config Config array (passed by reference)
     * @return void
     */
    private function process_video_source($post_id, &$config) {
        // Get video type (raw post meta - Config_Merger will handle priority)
        $video_type = get_post_meta($post_id, '_video_type', true);
        if (!$video_type) {
            $video_type = 'youtube';
        }
        
        $config['video_type'] = $video_type;

        // Process based on video type
        if ($video_type === 'youtube') {
            $this->process_youtube_source($post_id, $config);
        } elseif ($video_type === 'vimeo') {
            $this->process_vimeo_source($post_id, $config);
        } else {
            $this->process_html5_source($post_id, $config);
        }
    }

    /**
     * Process YouTube source
     * 
     * @param int $post_id Post ID
     * @param array &$config Config array (passed by reference)
     * @return void
     */
    private function process_youtube_source($post_id, &$config) {
        $youtube_url = get_post_meta($post_id, '_youtube_url', true);
        
        if (!$youtube_url) {
            return;
        }

        $video_info = leanpl_parse_video_url($youtube_url);
        $config['video_id'] = $video_info['id'];
        $config['sources'] = [];
    }

    /**
     * Process Vimeo source
     * 
     * @param int $post_id Post ID
     * @param array &$config Config array (passed by reference)
     * @return void
     */
    private function process_vimeo_source($post_id, &$config) {
        $vimeo_url = get_post_meta($post_id, '_vimeo_url', true);
        
        if (!$vimeo_url) {
            return;
        }

        $video_info = leanpl_parse_video_url($vimeo_url);
        $config['video_id'] = $video_info['id'];
        $config['sources'] = [];
    }

    /**
     * Process HTML5 video source
     * 
     * @param int $post_id Post ID
     * @param array &$config Config array (passed by reference)
     * @return void
     */
    private function process_html5_source($post_id, &$config) {
        // Get HTML5 source type (raw post meta - Config_Merger will handle priority)
        $html5_source_type = get_post_meta($post_id, '_html5_source_type', true);
        if (!$html5_source_type) {
            $html5_source_type = 'link';
        }

        $video_url = '';

        // Get video URL based on source type
        if ($html5_source_type === 'upload') {
            // Media library upload
            $video_source = get_post_meta($post_id, '_video_source', true);
            if ($video_source) {
                $video_url = is_numeric($video_source) 
                    ? wp_get_attachment_url($video_source) 
                    : esc_url_raw($video_source);
            }
        } else {
            // Direct URL (CDN/link)
            $html5_video_url = get_post_meta($post_id, '_html5_video_url', true);
            if ($html5_video_url) {
                $video_url = esc_url_raw($html5_video_url);
            }
        }

        // Parse video URL to create HTML5 sources
        if ($video_url) {
            $video_info = leanpl_parse_video_url($video_url);
            $config['video_id'] = $video_info['id'];
            $config['sources'] = $video_info['sources'];
        }
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

// Initialize the player shortcode
Player_Shortcode::get_instance();

