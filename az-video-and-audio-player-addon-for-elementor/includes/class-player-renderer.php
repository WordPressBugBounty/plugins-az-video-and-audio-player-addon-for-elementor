<?php
namespace LeanPL;

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
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Supported video types
     */
    private const SUPPORTED_VIDEO_TYPES = ['html5', 'youtube', 'vimeo'];

    /**
     * Keys sent to JS for both video and audio players.
     * Snake_case only — JS converts to Plyr format in player-utils.js.
     */
    private const COMMON_SETTINGS_KEYS = [
        'autoplay', 'muted', 'volume', 'loop',
        'seek_time', 'invert_time', 'speed_selected',
        'controls', 'storage_enabled', 'debug_mode',
        'keyboard_focused', 'keyboard_global',
        'tooltips_seek', 'tooltips_controls',
        'autopause',
    ];

    /**
     * Video-only keys — extend COMMON_SETTINGS_KEYS.
     */
    private const VIDEO_ONLY_SETTINGS_KEYS = [
        'click_to_play', 'hide_controls', 'reset_on_end',
        'fullscreen_enabled', 'quality_default', 'ratio',
    ];

    /**
     * Audio-only keys — extend COMMON_SETTINGS_KEYS (empty, slot exists for future).
     */
    private const AUDIO_ONLY_SETTINGS_KEYS = [];

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
     * Build the value of an inline `style="..."` attribute for a player wrapper.
     *
     * Reads Plyr CSS variables from $config and returns the joined declarations
     * (without the surrounding `style="..."`). Returns '' when nothing is set.
     * Caller is responsible for wrapping with `style="..."` and applying esc_attr().
     *
     * To support a new Plyr CSS variable later, add one line to the $map below.
     * Values are trusted to be sanitised at the input boundary
     * (Lex Settings + metabox save handlers).
     *
     * @param array $config         Merged player config.
     * @param bool  $reserve_ratio  When true (video only), emit --lpl-ratio so
     *                              CSS can reserve the aspect-ratio box before
     *                              Plyr initializes, preventing layout shift.
     * @return string Empty string or `--plyr-x: y; --plyr-z: w`.
     */
    private function build_player_style( array $config, bool $reserve_ratio = false ): string {
        // Only per-player color overrides go inline (beat stylesheet-level rules).
        // Global primary_color is output via wp_add_inline_style on .lpl-player
        // so it doesn't fight Elementor widget CSS. Per-player is flagged with a
        // dedicated key so the two levels stay distinguishable here.
        $declarations = [];
        $per_player_color = $config['per_player_primary_color'] ?? '';
        if ( $per_player_color !== '' ) {
            $declarations[] = '--plyr-color-main: ' . $per_player_color;
            $declarations[] = '--plyr-range-fill-background: ' . $per_player_color;
            $declarations[] = '--plyr-range-thumb-background: ' . $per_player_color;
        }

        // Reserve the video aspect-ratio up front to avoid pre-init layout shift.
        // ratio is stored in Plyr's "W:H" form (e.g. "16:9"); CSS aspect-ratio
        // wants "W/H". Empty/auto ratio falls back to 16/9 via the CSS default.
        if ( $reserve_ratio ) {
            $ratio = isset( $config['ratio'] ) ? trim( (string) $config['ratio'] ) : '';
            if ( $ratio !== '' && preg_match( '/^\d+\s*:\s*\d+$/', $ratio ) ) {
                $declarations[] = '--lpl-ratio: ' . str_replace( ':', '/', str_replace( ' ', '', $ratio ) );
            }
        }

        return implode( '; ', $declarations );
    }

  /**
   * Render video player with flattened configuration array
   *
   * @param array $config  Configuration array
   * @param int   $post_id lean_player CPT post ID for wrap (0 = inline, no wrap)
   * @return void
   */
    public function render_video_player($config = [], $post_id = 0) {
      $per_player_color = $config['per_player_primary_color'] ?? '';
      $config = Config_Merger::get_instance()->merge($config);
      if ( $per_player_color !== '' ) {
          $config['per_player_primary_color'] = $per_player_color;
      }

        if (!$this->is_valid_video_type($config['video_type'])) {
            $this->render_error('Invalid video type provided.');
            return;
        }

        /**
         * Fires before video player renders
         * 
         * Allows pro version to apply styling, enqueue scripts, modify config, etc.
         * 
         * @since 3.0.0
         * @param array  $config      Player configuration array
         * @param string $player_type Player type ('video' or 'audio')
         */
        do_action('leanpl/player/before_render', $config, 'video');

      // Build data settings — allowlist via constants, all keys stay snake_case.
      $keys = array_merge( self::COMMON_SETTINGS_KEYS, self::VIDEO_ONLY_SETTINGS_KEYS );
      $data_settings = array_intersect_key( $config, array_flip( $keys ) );

      $post_id = absint( $post_id );
      $wrap_attrs = $post_id > 0 ? ' id="lpl-player-' . esc_attr( $post_id ) . '"' : '';
      $wrap_classes = 'lpl-player-wrap lpl-video' . ( ! empty( $config['poster'] ) ? ' has-poster' : '' );
      echo '<div class="' . esc_attr( $wrap_classes ) . '"' . $wrap_attrs . '>';

      if ($config['video_type'] == 'html5') {
            $this->render_html5_markup($config, $data_settings);
      } elseif ($config['video_type'] == 'youtube') {
            $this->render_youtube_markup($config, $data_settings, $post_id);
      } elseif ($config['video_type'] == 'vimeo') {
            $this->render_vimeo_player($config, $data_settings, $post_id);
      }

      echo '</div>';
      
      /**
       * Fires after video player renders
       * 
       * Allows pro version to add analytics, tracking, custom scripts, etc.
       * 
       * @since 3.0.0
       * @param array  $config      Player configuration array
       * @param string $player_type Player type ('video' or 'audio')
       */
      do_action('leanpl/player/after_render', $config, 'video');
      
      // Output debug info if debug mode is enabled
      if (leanpl_is_debug_mode() || leanpl_is_test_mode()) {
          echo Config_Merger::get_debug_output_html();
      }
  }

  /**
   * Render audio player with flattened configuration array
   *
   * @param array $config  Configuration array
   * @param int   $post_id lean_player CPT post ID for wrap (0 = inline, no wrap)
   * @return void
   */
    public function render_audio_player($config = [], $post_id = 0) {
        $per_player_color = $config['per_player_primary_color'] ?? '';
        $config = Config_Merger::get_instance()->merge($config);
        if ( $per_player_color !== '' ) {
            $config['per_player_primary_color'] = $per_player_color;
        }

        if (empty($config['url'])) {
            $this->render_error('No audio source provided.');
            return;
        }

        /**
         * Fires before audio player renders
         * 
         * Allows pro version to apply styling, enqueue scripts, modify config, etc.
         * 
         * @since 3.0.0
         * @param array  $config      Player configuration array
         * @param string $player_type Player type ('video' or 'audio')
         */
        do_action('leanpl/player/before_render', $config, 'audio');

        // Build data settings — allowlist via constants, all keys stay snake_case.
        $keys = array_merge( self::COMMON_SETTINGS_KEYS, self::AUDIO_ONLY_SETTINGS_KEYS );
        $data_settings = array_intersect_key( $config, array_flip( $keys ) );

        $post_id    = absint( $post_id );
        $skin       = !empty( $config['audio_skin'] ) ? $config['audio_skin'] : 'default';
        $allowed_skins = [ 'default', 'dark', 'glass' ];
        if ( ! in_array( $skin, $allowed_skins, true ) ) {
            $skin = 'default';
        }
        $wrap_attrs   = $post_id > 0 ? ' id="lpl-player-' . esc_attr( $post_id ) . '"' : '';
        $wrap_attrs  .= ' data-skin="' . esc_attr( $skin ) . '"';
        $wrap_classes = 'lpl-player-wrap lpl-audio' . ( ! empty( $config['poster'] ) ? ' has-poster' : '' );
        echo '<div class="' . esc_attr( $wrap_classes ) . '"' . $wrap_attrs . '>';

        $this->render_html5_audio_markup($config, $data_settings);

        echo '</div>';
        
        /**
         * Fires after audio player renders
         * 
         * Allows pro version to add analytics, tracking, custom scripts, etc.
         * 
         * @since 3.0.0
         * @param array  $config      Player configuration array
         * @param string $player_type Player type ('video' or 'audio')
         */
        do_action('leanpl/player/after_render', $config, 'audio');
        
        // Output debug info if test mode is enabled
        if (leanpl_is_test_mode()) {
            echo Config_Merger::get_debug_output_html();
        }
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

        // Get correct MIME type for the file extension
        $mime_type = leanpl_get_audio_mime_type($file_extension);

        $brand_style = $this->build_player_style( $config );
        $has_poster = !empty($config['poster']);

        if ( $has_poster ) : ?>
            <img
                class="lpl-audio-poster"
                src="<?php echo esc_url( $config['poster'] ); ?>"
                alt="<?php echo esc_attr( $config['audio_title'] ?? '' ); ?>"
                aria-hidden="true"
            />
            <div class="lpl-audio-info">
                <?php if ( !empty( $config['audio_title'] ) ) : ?>
                <div class="lpl-audio-title"><?php echo esc_html( $config['audio_title'] ); ?></div>
                <?php endif; ?>
        <?php endif; ?>
        <audio
            class="lpl-player lpl-player--audio"
            <?php if ( $brand_style ) : ?>style="<?php echo esc_attr( $brand_style ); ?>"<?php endif; ?>
            data-settings='<?php echo esc_attr( wp_json_encode( $data_settings ) ); ?>'
            <?php echo $config['autoplay'] ? 'autoplay allow="autoplay"' : ''; ?>
            <?php echo $config['loop'] ? 'loop' : ''; ?>
            preload="<?php echo esc_attr($config['preload']); ?>"
        >
            <source
                src="<?php echo esc_url($config['url']); ?>"
                type="<?php echo esc_attr($mime_type); ?>"
            />
            <?php esc_html_e('Your browser does not support the audio element.', 'vapfem'); ?>
        </audio>
        <?php if ( $has_poster ) : ?>
            </div><!-- .lpl-audio-info -->
        <?php endif;
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

        $brand_style = $this->build_player_style( $config, true );
        ?>
        <video
              <?php if (!empty($config['poster'])): ?>poster="<?php echo esc_url($config['poster']); ?>"<?php endif; ?>
              class="lpl-player lpl-player--video"
              <?php if ( $brand_style ) : ?>style="<?php echo esc_attr( $brand_style ); ?>"<?php endif; ?>
              <?php echo $config['autoplay'] ? 'autoplay' : ''; ?>
              <?php echo $config['muted'] ? 'muted' : ''; ?>
              <?php echo $config['loop'] ? 'loop' : ''; ?>
              preload="<?php echo esc_attr($config['preload']); ?>"
              data-settings='<?php echo esc_attr( wp_json_encode( $data_settings ) ); ?>'
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
    private function render_youtube_markup($config, $data_settings, $post_id = 0) {
        if (empty($config['video_id'])) {
            $this->render_error('YouTube video ID is required.');
            return;
        }

        $brand_style = $this->build_player_style( $config, true );
        ?>
        <div class="plyr__video-embed lpl-player lpl-player--video"
            <?php if ( $brand_style ) : ?>style="<?php echo esc_attr( $brand_style ); ?>"<?php endif; ?>
            data-settings='<?php echo esc_attr( wp_json_encode( $data_settings ) ); ?>'
        >
            <iframe
                src="<?php echo esc_url($this->generate_youtube_url($config)); ?>"
                allowfullscreen
                allowtransparency
                allow="autoplay"
            ></iframe>
        </div>
        <?php $this->render_custom_poster_style($config, $post_id); ?>
        <?php
    }

    /**
     * Render Vimeo player
     *
     * @param array $config Configuration
     * @param array $data_settings Data settings
     * @return void
     */
    private function render_vimeo_player($config, $data_settings, $post_id = 0) {
        if (empty($config['video_id'])) {
            $this->render_error('Vimeo video ID is required.');
            return;
        }

        $brand_style = $this->build_player_style( $config, true );
        ?>
        <div class="plyr__video-embed lpl-player lpl-player--video"
            <?php if ( $brand_style ) : ?>style="<?php echo esc_attr( $brand_style ); ?>"<?php endif; ?>
            data-settings='<?php echo esc_attr( wp_json_encode( $data_settings ) ); ?>'
        >
            <iframe
                src="<?php echo esc_url($this->generate_vimeo_url($config)); ?>"
                allowfullscreen
                allowtransparency
                allow="autoplay"
            ></iframe>
        </div>
        <?php $this->render_custom_poster_style($config, $post_id); ?>
        <?php
    }

    /**
     * Render custom poster style scoped to this player instance.
     *
     * @param array $config  Configuration
     * @param int   $post_id Player post ID (used to scope the CSS selector)
     * @return void
     */
    private function render_custom_poster_style($config, $post_id = 0) {
        if (empty($config['poster'])) {
            return;
        }

        $post_id = absint($post_id);
        $selector = $post_id > 0
            ? '#lpl-player-' . $post_id . ' .plyr__poster'
            : '.plyr__poster';

        printf(
            '<style type="text/css">%s { background-image: url(\'%s\'); }</style>',
            esc_attr($selector),
            esc_url($config['poster'])
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
            '<div class="lpl-player__error" style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">%s</div>',
            esc_html($message)
        );
    }
}
