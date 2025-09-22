<?php
namespace VAPFEM;

/**
 * Shared Player Renderer Class
 * Single Source of Truth for both Elementor widgets and shortcodes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Player_Renderer {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Default configuration values (loaded from file)
     */
    private static $default_config = null;

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
     * Get default configuration from file
     */
    private function get_default_config() {
        if (null === self::$default_config) {
            self::$default_config = include VAPFEM_DIR . '/includes/player-defaults.php';
        }
        return self::$default_config;
    }

    /**
     * Get merged default configuration for video player
     */
    private function get_video_defaults() {
        $defaults = $this->get_default_config();
        return array_merge($defaults['shared'], $defaults['video']);
    }

    /**
     * Get merged default configuration for audio player
     */
    private function get_audio_defaults() {
        $defaults = $this->get_default_config();
        return array_merge($defaults['shared'], $defaults['audio']);
    }

    /**
     * Supported video types
     */
    private const SUPPORTED_VIDEO_TYPES = ['html5', 'youtube', 'vimeo'];

    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }

    /**
     * Check if video type is valid
     * 
     * @param string $video_type Video type to validate
     * @return bool
     */
    private function is_valid_video_type($video_type) {
        return in_array($video_type, self::SUPPORTED_VIDEO_TYPES, true);
    }

  /**
   * Render video player with flattened configuration array
   *
   * @param array $config Configuration array
   * @return void
   */
    public function render_video_player($config = []) {
      $config = array_merge($this->get_video_defaults(), $config);

        if (!$this->is_valid_video_type($config['video_type'])) {
            $this->render_error('Invalid video type provided.');
            return;
        }

      // Build data settings
      $data_settings = [
          'seek_time'          => $config['seek_time'],
          'volume'             => $config['volume'],
          'muted'              => $config['muted'],
          'clickToPlay'        => $config['click_to_play'],
          'keyboard_focused'   => $config['keyboard_focused'],
          'keyboard_global'    => $config['keyboard_global'],
          'tooltips_controls'  => $config['tooltips_controls'],
          'hideControls'       => $config['hide_controls'],
          'resetOnEnd'         => $config['reset_on_end'],
          'tooltips_seek'      => $config['tooltips_seek'],
          'invertTime'         => $config['invert_time'],
          'fullscreen_enabled' => $config['fullscreen_enabled'],
          'speed_selected'     => $config['speed_selected'],
          'quality_default'    => $config['quality_default'],
          'controls'           => $config['controls'],
          'ratio'              => $config['ratio'],
          'debug_mode'         => $config['debug_mode'],
      ];

      if ($config['video_type'] == 'html5') {
            $this->render_html5_markup($config, $data_settings);
      } elseif ($config['video_type'] == 'youtube') {
            $this->render_youtube_markup($config, $data_settings);
      } elseif ($config['video_type'] == 'vimeo') {
            $this->render_vimeo_player($config, $data_settings);
      }
  }

  /**
   * Render audio player with flattened configuration array
   *
   * @param array $config Configuration array
   * @return void
   */
    public function render_audio_player($config = []) {
        $config = array_merge($this->get_audio_defaults(), $config);

        if (empty($config['url'])) {
            $this->render_error('No audio source provided.');
            return;
        }

        // Build data settings for audio
        $data_settings = [
            'seek_time'          => $config['seek_time'],
            'volume'             => $config['volume'],
            'muted'              => $config['muted'],
            'keyboard_focused'   => $config['keyboard_focused'],
            'keyboard_global'    => $config['keyboard_global'],
            'tooltips_seek'      => $config['tooltips_seek'],
            'invertTime'         => $config['invert_time'],
            'speed_selected'     => $config['speed_selected'],
            'controls'           => $config['controls'],
            'debug_mode'         => $config['debug_mode'],
        ];

        $this->render_html5_audio_markup($config, $data_settings);
    }

    /**
     * Render HTML5 audio player
     *
     * @param array $config Configuration
     * @param array $data_settings Data settings
     * @return void
     */
    private function render_html5_audio_markup($config, $data_settings) {
        // Get file extension for type attribute
        $file_extension = '';
        if (!empty($config['url'])) {
            $path_info = pathinfo($config['url']);
            $file_extension = isset($path_info['extension']) ? $path_info['extension'] : 'mp3';
        }
        ?>
        <audio
            class="vapfem-player vapfem-audio"
            data-settings='<?php echo wp_json_encode($data_settings); ?>'
            <?php echo $config['autoplay'] ? 'autoplay allow="autoplay"' : ''; ?>
            <?php echo $config['loop'] ? 'loop' : ''; ?>
            preload="<?php echo esc_attr($config['preload']); ?>"
        >
            <source
                src="<?php echo esc_url($config['url']); ?>"
                type="audio/<?php echo esc_attr($file_extension); ?>"
            />
            <?php esc_html_e('Your browser does not support the audio element.', 'vapfem'); ?>
        </audio>
        <?php
    }

    /**
     * Render HTML5 video player
     *
     * @param array $config Configuration
     * @param array $data_settings Data settings
     * @return void
     */
    private function render_html5_markup($config, $data_settings) {
        if (empty($config['sources'])) {
            $this->render_error('No video sources provided for HTML5 player.');
            return;
        }

        // Get the first video URL for fallback download link
        $fallback_video_url = '';
        if (!empty($config['sources'][0]['url'])) {
            $fallback_video_url = $config['sources'][0]['url'];
        }
        ?>
        <video
              poster="<?php echo esc_attr($config['poster']); ?>"
              class="vapfem-player vapfem-video"
              <?php echo $config['autoplay'] ? 'autoplay' : ''; ?>
              <?php echo $config['muted'] ? 'muted' : ''; ?>
              <?php echo $config['loop'] ? 'loop' : ''; ?>
              data-settings='<?php echo wp_json_encode($data_settings); ?>'
          >
              <?php
              foreach($config['sources'] as $html5_video) {
                  $video_link = isset($html5_video['url']) ? $html5_video['url'] : '';

                  if (empty($video_link)) continue;

                  $extension = pathinfo($video_link, PATHINFO_EXTENSION);
                  $size = isset($html5_video['size']) ? $html5_video['size'] : '';
                  ?>
                  <source
                      src="<?php echo esc_url($video_link); ?>"
                      type="video/<?php echo esc_attr($extension); ?>"
                      size="<?php echo esc_attr($size); ?>"
                  />
                  <?php
              }
              ?>
              <?php if (!empty($fallback_video_url)): ?>
              <a href="<?php echo esc_url($fallback_video_url); ?>">
                <?php echo esc_html__('Download', 'vapfem'); ?>
              </a>
              <?php endif; ?>
          </video>
        <?php
    }

    /**
     * Render YouTube player
     * 
     * @param array $config Configuration
     * @param array $data_settings Data settings
     * @return void
     */
    private function render_youtube_markup($config, $data_settings) {
        if (empty($config['video_id'])) {
            $this->render_error('YouTube video ID is required.');
            return;
        }
        ?>
        <div class="plyr__video-embed vapfem-player vapfem-video"
            data-settings='<?php echo wp_json_encode($data_settings); ?>'
        >
            <iframe
                src="<?php echo esc_url($this->generate_youtube_url($config)); ?>"
                allowfullscreen
                allowtransparency
                allow="autoplay"
            ></iframe>
        </div>
        <?php $this->render_custom_poster_style($config); ?>
        <?php
    }

    /**
     * Render Vimeo player
     * 
     * @param array $config Configuration
     * @param array $data_settings Data settings
     * @return void
     */
    private function render_vimeo_player($config, $data_settings) {
        if (empty($config['video_id'])) {
            $this->render_error('Vimeo video ID is required.');
            return;
        }

        ?>
        <div class="plyr__video-embed vapfem-player vapfem-video"
            data-settings='<?php echo wp_json_encode($data_settings); ?>'
        >
            <iframe
                src="<?php echo esc_url($this->generate_vimeo_url($config)); ?>"
                allowfullscreen
                allowtransparency
                allow="autoplay"
            ></iframe>
        </div>
        <?php $this->render_custom_poster_style($config); ?>
        <?php
    }

    /**
     * Render custom poster style if needed
     * 
     * @param array $config Configuration
     * @return void
     */
    private function render_custom_poster_style($config) {
        if (empty($config['poster'])) {
            return;
        }

        printf(
            '<style type="text/css">.plyr__poster { background-image: url(\'%s\') !important; }</style>',
            esc_attr($config['poster'])
        );
    }

    /**
     * Build Vimeo embed URL
     * 
     * @param array $config Configuration
     * @return string Vimeo embed URL
     */
    private function generate_vimeo_url($config) {
        $params = [
            'autoplay' => $config['autoplay'] ? '1' : '0',
            'loop' => $config['loop'] ? '1' : '0',
            'byline' => 'false',
            'portrait' => 'false',
            'title' => 'false',
            'speed' => 'true',
            'transparent' => '0',
            'gesture' => 'media'
        ];

        $query_string = http_build_query($params);
        
        return sprintf(
            'https://player.vimeo.com/video/%s?%s',
            esc_attr($config['video_id']),
            $query_string
        );
    }

    /**
     * Build YouTube embed URL
     * 
     * @param array $config Configuration
     * @return string YouTube embed URL
     */
    private function generate_youtube_url($config) {
        $params = [
            'autoplay' => $config['autoplay'] ? '1' : '0',
            'loop' => $config['loop'] ? '1' : '0',
            'origin' => get_home_url(),
            'iv_load_policy' => '3',
            'modestbranding' => '1',
            'playsinline' => '1',
            'showinfo' => '0',
            'rel' => '0',
            'enablejsapi' => '1'
        ];

        $query_string = http_build_query($params);
        
        return sprintf(
            'https://www.youtube.com/embed/%s?%s',
            esc_attr($config['video_id']),
            $query_string
        );
    }

    /**
     * Render error message
     * 
     * @param string $message Error message
     * @return void
     */
    private function render_error($message) {
        printf(
            '<div class="vapfem-player-error" style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">%s</div>',
            esc_html($message)
        );
    }
}