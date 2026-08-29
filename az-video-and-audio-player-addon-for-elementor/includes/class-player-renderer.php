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
        'seek_time', 'invert_time', 'speed_selected', 'speed_options',
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
        'poster',
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
     * Whether the audio title should actually be displayed.
     *
     * With a poster: whatever resolved into $config['audio_title'] (custom
     * text or the post-title fallback — see class-player-shortcode.php).
     * Without a poster: Classic only, and only ever the user's own explicit
     * text (class-player-shortcode.php never fills the post-title fallback
     * when there's no poster, so an empty value here already means "the user
     * never asked for one"). '' counts as Classic here too — no layout ever
     * explicitly chosen behaves identically to explicitly choosing Classic
     * everywhere else in this feature (resolve_player_layout()), so this
     * can't be the one place that treats them differently.
     *
     * @param array  $config Merged player config.
     * @param string $layout Validated layout value (see resolve_player_layout()).
     * @return bool
     */
    private function should_show_audio_title( array $config, string $layout ): bool {
        $title_enabled = $config['audio_title_enabled'] ?? true;
        $has_poster = !empty( $config['poster'] );
        return $title_enabled && !empty( $config['audio_title'] ) && ( $has_poster || $layout === 'classic' || $layout === '' );
    }

    /**
     * Validate the `player_layout` config value against the layout registry.
     *
     * `player_layout` holds one of two shapes (Lock A11 — live reference,
     * not stamp-and-step-away): a real layout key, or a Custom Preset id
     * (`preset_...`). A preset id resolves to *its own* stored `layout`
     * field (always `classic` or `floating` — see resolve_layout_controls()),
     * live, on every render. Delete the preset and this falls back to
     * `classic`: rendering must not depend on a preset that no longer exists.
     *
     * Empty stays empty — nothing was explicitly chosen at any level (global
     * or per-player), so the attribute stays honest about that rather than
     * being silently promoted to 'classic'. Visually there is no difference:
     * Classic ships zero CSS, so an empty data-lpl-player-layout attribute
     * matches exactly as many rules as "classic" does (none). An invalid
     * value (e.g. hand-edited post meta) gets the same empty treatment,
     * never the raw unrecognized string.
     *
     * @param array $config Merged player config.
     * @return string Validated layout value, or '' if unset/invalid.
     */
    private function resolve_player_layout( array $config ): string {
        $layout = $config['player_layout'] ?? '';
        if ( $layout === '' ) {
            return '';
        }

        if ( $this->is_custom_preset_ref( $layout ) ) {
            $resolved = Custom_Preset_Ajax::resolve_layout_ref( $layout );
            return $this->validate_layout( $resolved['layout'] );
        }

        return $this->validate_layout( $layout );
    }

    /**
     * @param string $layout Raw candidate layout value.
     * @return string A known layout key, or '' if unrecognized.
     */
    private function validate_layout( string $layout ): string {
        $allowed_layouts = array_keys( leanpl_get_player_layouts() );
        return in_array( $layout, $allowed_layouts, true ) ? $layout : '';
    }

    /**
     * @param string $value Raw `player_layout` config value.
     * @return bool True if this is a Custom Preset reference, not a real layout key.
     */
    private function is_custom_preset_ref( string $value ): bool {
        return strpos( $value, 'preset_' ) === 0;
    }

    /**
     * Every layout in the registry owns a fixed controls array per player
     * type — it fully replaces whatever $config['controls'] resolved to,
     * the layout wins outright, no intersection. An unresolved layout
     * (nothing chosen at any level — resolve_player_layout() stays '' in
     * that case, deliberately, for the data-lpl-player-layout attribute)
     * resolves to 'classic' here: Classic is the practical default, so an
     * unset layout falls back to Classic's controls — unless a real
     * selection survives in $config['controls'] from the retired standalone
     * Controls picker, which still wins (see is_default_controls() for how
     * a real selection is told apart from the merger's own default).
     *
     * A Custom Preset reference (Lock A11) is resolved first and, if found,
     * wins outright too — its own controls array *is* the whole point of
     * applying a preset. A deleted preset falls through to the classic
     * default below. A preset's stored array has no portrait-specific
     * variant (it's a single user-picked list, not a registry entry) so it
     * is deliberately never trimmed here — the user's explicit choice wins
     * even on a portrait video. The same goes for a legacy $config['controls']
     * selection below, for the same reason.
     *
     * A registry layout, on the other hand, may define a 'video_portrait'
     * list alongside 'video' (see leanpl_get_player_layouts()) for a video
     * that's in the narrow 9:16 (Shorts) shape, where the full 'video' list
     * is too crowded. Used in place of 'video' whenever present; layouts
     * that aren't crowded to begin with (Simple/Minimal) don't define one,
     * so they fall back to their normal 'video' list unchanged.
     *
     * @param array  $config      Merged player config.
     * @param string $layout      Validated layout value (see resolve_player_layout()).
     * @param string $player_type 'video' or 'audio'.
     * @return array Controls slugs to send to Plyr.
     */
    private function resolve_layout_controls( array $config, string $layout, string $player_type ): array {
        $raw = $config['player_layout'] ?? '';
        if ( $this->is_custom_preset_ref( $raw ) ) {
            $resolved = Custom_Preset_Ajax::resolve_layout_ref( $raw );
            if ( null !== $resolved['controls'] ) {
                return $resolved['controls'];
            }
            // Deleted preset, or a preset with no stored controls — fall
            // through below using whatever $layout resolved to.
        }

        // An explicitly chosen layout (a real registry key, including an
        // explicit 'classic', or a resolved Custom Preset's own layout)
        // always wins outright — its fixed controls array fully replaces
        // whatever $config['controls'] holds, no intersection.
        if ( $layout !== '' ) {
            return $this->pick_registry_controls( $layout, $player_type, $config );
        }

        // Nothing was explicitly chosen at any level. What's left is the
        // legacy/instance `controls` value (e.g. the Elementor widgets'
        // "Control Options (Legacy)" field, or per-player `_controls`).
        $has_controls = ! empty( $config['controls'] ) && is_array( $config['controls'] );

        // A selection the user actually made wins outright, portrait or not
        // — same contract as a Custom Preset's stored array above, and the
        // whole point of the 3.3.1 fix ("a custom Control Options selection
        // was silently discarded"). Trimming it for portrait would discard
        // it all over again, just on a narrower set of players.
        if ( $has_controls && ! $this->is_default_controls( $config['controls'] ) ) {
            return $config['controls'];
        }

        // No layout and no real selection. '' behaves identically to
        // explicitly choosing Classic everywhere else in this feature (see
        // resolve_player_layout()'s docblock), so a portrait video gets
        // Classic's registry entry here too — same as the $layout !== ''
        // branch above, portrait-aware.
        if ( $player_type === 'video' && leanpl_get_portrait_ratio_num( $config['ratio'] ?? '' ) !== '' ) {
            return $this->pick_registry_controls( 'classic', $player_type, $config );
        }

        // Landscape video, or audio (no portrait concept). Returning the
        // merged value rather than the registry array is deliberate: for
        // video the two are identical, but for audio $config['controls']
        // holds the shared default (which carries video-only slugs Plyr
        // simply ignores) and that is the shipped behaviour.
        if ( $has_controls ) {
            return $config['controls'];
        }

        return $this->pick_registry_controls( 'classic', $player_type, $config );
    }

    /**
     * Whether a `controls` array is indistinguishable from the baked-in
     * default in player-defaults.php.
     *
     * Config_Merger fills `controls` in for every player from that default
     * whether or not anyone ever touched the retired Controls picker, so a
     * non-empty value on its own proves nothing about user intent — this is
     * what separates "the merger filled it in" from "someone chose this".
     *
     * Order matters and is compared: reordering the same slugs reorders the
     * rendered control bar, so it counts as a real choice. Only the keys are
     * normalised, since an array_filter() upstream can leave gaps.
     *
     * A user who explicitly picks exactly the default set, in the default
     * order, is treated as not having chosen — the two are the same bar, so
     * there is nothing to preserve.
     *
     * @param array $controls Merged `controls` value.
     * @return bool True when it matches the default set exactly.
     */
    private function is_default_controls( array $controls ): bool {
        // Cached per request: leanpl_get_player_defaults() re-includes
        // player-defaults.php on every call, which rebuilds the controls
        // registry (and its __() calls) each time. This runs per rendered
        // player, so a page with several of them would pay for it repeatedly.
        static $default_controls = null;

        if ( null === $default_controls ) {
            $defaults = leanpl_get_player_defaults();
            $default_controls = array_values( $defaults['shared']['controls'] ?? [] );
        }

        return array_values( $controls ) === $default_controls;
    }

    /**
     * Read a registry layout's controls array, preferring its
     * 'video_portrait' list over 'video' when the video is in the narrow
     * 9:16 (Shorts) shape and the layout defines one. Shared by both the
     * explicit-layout branch and the "nothing chosen, defaults to Classic"
     * fallback in resolve_layout_controls(), so the two always resolve the
     * same trimmed set the same way.
     *
     * @param string $layout      A known registry key (see leanpl_get_player_layouts()).
     * @param string $player_type 'video' or 'audio'.
     * @param array  $config      Merged player config, for the video's ratio.
     * @return array Controls slugs to send to Plyr.
     */
    private function pick_registry_controls( string $layout, string $player_type, array $config ): array {
        $layout_controls = leanpl_get_player_layouts()[ $layout ]['controls'];
        $is_portrait     = $player_type === 'video' && leanpl_get_portrait_ratio_num( $config['ratio'] ?? '' ) !== '';
        if ( $is_portrait && isset( $layout_controls['video_portrait'] ) ) {
            return $layout_controls['video_portrait'];
        }
        return $layout_controls[ $player_type ];
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

      $layout = $this->resolve_player_layout( $config );
      $config['controls'] = $this->resolve_layout_controls( $config, $layout, 'video' );

      // Build data settings — allowlist via constants, all keys stay snake_case.
      $keys = array_merge( self::COMMON_SETTINGS_KEYS, self::VIDEO_ONLY_SETTINGS_KEYS );
      $data_settings = array_intersect_key( $config, array_flip( $keys ) );

      $post_id = absint( $post_id );
      $wrap_attrs = $post_id > 0 ? ' id="lpl-player-' . esc_attr( $post_id ) . '"' : '';
      $wrap_attrs .= ' data-lpl-player-layout="' . esc_attr( $layout ) . '"';

      $portrait_num = leanpl_get_portrait_ratio_num( $config['ratio'] ?? '' );

      $wrap_classes = 'lpl-player-wrap lpl-video'
          . ( ! empty( $config['poster'] ) ? ' has-poster' : '' )
          . ( $portrait_num !== '' ? ' is-portrait' : '' );

      if ( $portrait_num !== '' ) {
          $wrap_style = '--lpl-ratio-num: ' . $portrait_num;

          $max_height = leanpl_sanitize_css_length( $config['portrait_max_height'] ?? '' );
          if ( $max_height !== '' ) {
              $wrap_style .= '; --lpl-portrait-max-height: ' . $max_height;
          }

          $wrap_attrs .= ' style="' . esc_attr( $wrap_style ) . '"';
      }

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

        $layout = $this->resolve_player_layout( $config );
        $config['controls'] = $this->resolve_layout_controls( $config, $layout, 'audio' );

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
        $wrap_attrs  .= ' data-lpl-player-layout="' . esc_attr( $layout ) . '"';
        // Card chrome (padding/border/radius/shadow) is driven by CSS off
        // .has-poster or an explicit data-lpl-player-layout value — see
        // main.css. Neither covers a title shown with '' (unset) layout —
        // should_show_audio_title() treats '' as Classic (same rule as
        // resolve_player_layout()), but the empty attribute value can't
        // itself be a CSS selector target, so .has-title fills that one gap.
        $has_poster = !empty( $config['poster'] );
        $wrap_classes = 'lpl-player-wrap lpl-audio'
            . ( $has_poster ? ' has-poster' : '' )
            . ( ( ! $has_poster && $this->should_show_audio_title( $config, $layout ) ) ? ' has-title' : '' );
        echo '<div class="' . esc_attr( $wrap_classes ) . '"' . $wrap_attrs . '>';

        $this->render_html5_audio_markup($config, $data_settings, $layout);

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
     * @param array  $config        Configuration
     * @param array  $data_settings Data settings
     * @param string $layout        Validated player_layout value (see resolve_player_layout()).
     * @return void
     */
    private function render_html5_audio_markup($config, $data_settings, $layout = '') {
        // Get file extension for type attribute. Extensionless URLs (live
        // streams) fall back to mp3 so the type attribute stays well-formed.
        $file_extension = '';
        if (!empty($config['url'])) {
            $file_extension = leanpl_get_url_extension($config['url']) ?: 'mp3';
        }

        // Get correct MIME type for the file extension
        $mime_type = leanpl_get_audio_mime_type($file_extension);

        $brand_style = $this->build_player_style( $config );
        $has_poster = !empty($config['poster']);
        $has_title = $this->should_show_audio_title( $config, $layout );

        // .lpl-audio-info wraps the title + the <audio> element itself so its
        // flex `gap` provides consistent spacing between them whether or not
        // a poster is present — see --lpl-audio-info-gap in main.css.
        $show_info_wrapper = $has_poster || $has_title;

        if ( $show_info_wrapper ) :
            if ( $has_poster ) : ?>
            <img
                class="lpl-audio-poster"
                src="<?php echo esc_url( $config['poster'] ); ?>"
                alt="<?php echo esc_attr( $config['audio_title'] ?? '' ); ?>"
                aria-hidden="true"
            />
            <?php endif; ?>
            <div class="lpl-audio-info">
                <?php if ( $has_title ) : ?>
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
        <?php if ( $show_info_wrapper ) : ?>
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

                  // Extensionless URLs fall back to mp4 so the type attribute
                  // stays well-formed rather than rendering a bare "video/".
                  $extension = leanpl_get_url_extension($video_link) ?: 'mp4';
                  $mime_type = leanpl_get_video_mime_type($extension);
                  $size = isset($html5_video['size']) ? $html5_video['size'] : '';
                  ?>
                  <source
                      src="<?php echo esc_url($video_link); ?>"
                      type="<?php echo esc_attr($mime_type); ?>"
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
     * Plyr's YouTube/Vimeo providers unconditionally auto-fetch the
     * provider's own default thumbnail and set it as an inline style on
     * `.plyr__poster` (see plyr.min.js), regardless of whether a custom
     * `poster` was passed into Plyr's config. That auto-fetch (a network
     * round-trip to the provider's thumbnail CDN) commonly resolves before
     * our config-driven poster gets applied, so passing `poster` through
     * Plyr's config alone is not reliable for embedded providers.
     *
     * `!important` here is required, not stylistic: it's the only way to
     * beat Plyr's inline-style override regardless of timing, since
     * `!important` in an author stylesheet outranks a non-important inline
     * style in the CSS cascade no matter which one is applied later.
     *
     * `opacity` must be forced too: Plyr's own CSS (assets/css/plyr.css)
     * hides `.plyr__poster` by default (`opacity: 0`) and only reveals it
     * via `.plyr--stopped.plyr__poster-enabled .plyr__poster { opacity: 1 }`
     * — `plyr__poster-enabled` is added by Plyr's JS asynchronously (inside
     * `setPoster()`'s network-dependent success callback) and is just as
     * race-prone as the background-image override above. Without forcing
     * opacity, the correct image can be set on an invisible element (0%
     * opacity), which is exactly what happens for Vimeo in practice — the
     * background-image override "succeeds" while the element stays
     * invisible and the provider's own iframe content shows through
     * underneath instead.
     *
     * The rule is scoped to `:not(.plyr--playing)` so it steps aside once
     * real playback starts — `plyr--playing`/`plyr--paused` are toggled by
     * Plyr's `checkPlaying()` on actual media events (play/pause/timeupdate),
     * a different, reliable code path from the network-racy poster-enable
     * classes above. Forcing opacity unconditionally (no play-state scope)
     * was tried first and regressed: the poster stayed glued on top of the
     * video during active playback for every provider, since our override
     * doesn't know or care whether the video started.
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
        $scope = $post_id > 0 ? '#lpl-player-' . $post_id . ' ' : '';
        $selector = $scope . '.plyr:not(.plyr--playing) .plyr__poster';

        printf(
            '<style type="text/css">%s { background-image: url(\'%s\') !important; opacity: 1 !important; }</style>',
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
