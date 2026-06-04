<?php
namespace LeanPL;

class Metaboxes {
    private static $instance = null;

    /**
     * Fields that support Global Options inheritance UI display
     * 
     * This constant acts as a marker/flag to enable inherited value information
     * in the metabox UI. Field names must be without the underscore prefix 
     * (e.g., 'autoplay' not '_autoplay').
     * 
     * IMPORTANT: When introducing a new global option:
     * - Add the field name here to show "inherited from Global Options" UI info
     * - For regular fields (text, number): also enables inheritance by returning 
     *   empty string instead of default value
     * - For select fields: inheritance works automatically if they have 
     *   "Use Global Option" option, but add here to show inherited value info
     * 
     * @var array
     */
    private const FIELDS_WITH_GLOBAL_OPTIONS = [
        'autoplay',
        'muted',
        'loop',
        'volume',
        'speed_selected',
        'click_to_play',
        'fullscreen_enabled',
        'hide_controls',
        'reset_on_end',
        'invert_time',
        'seek_time',
        'controls',
    ];

    /**
     * Field definitions - single source of truth
     * 
     * Each field includes a 'group' property that defines which section it belongs to.
     * This allows us to automatically render fields by group without hardcoding field names.
     * 
     * @return array Field configuration array
     */
    public static function get_field_definitions() {
        return [
            '_player_type' => [
                'type' => 'radio',
                'label' => __('Player Type', 'vapfem'),
                'desc' => __('Choose the type of player', 'vapfem'),
                'inline' => true,
                'options' => [
                    'video' => __('Video', 'vapfem'),
                    'audio' => __('Audio', 'vapfem'),
                ],
                'default' => 'video',
                'group' => 'player_type',
            ],
            '_video_type' => [
                'type' => 'radio',
                'label' => __('Video Type', 'vapfem'),
                'desc' => __('Choose the video source type', 'vapfem'),
                'inline' => true,
                'options' => [
                    'youtube' => __('YouTube', 'vapfem'),
                    'vimeo' => __('Vimeo', 'vapfem'),
                    'html5' => __('HTML5 Video File', 'vapfem'),
                ],
                'default' => 'youtube',
                'group' => 'video_source',
            ],
            '_youtube_url' => [
                'type' => 'text',
                'label' => __('YouTube URL or ID', 'vapfem'),
                'desc' => __('Enter YouTube video URL (e.g., https://www.youtube.com/watch?v=bTqVqk7FSmY) or just the video ID', 'vapfem'),
                'placeholder' => __('https://www.youtube.com/watch?v=bTqVqk7FSmY', 'vapfem'),
                'group' => 'video_source',
            ],
            '_vimeo_url' => [
                'type' => 'text',
                'label' => __('Vimeo URL or ID', 'vapfem'),
                'desc' => __('Enter Vimeo video URL (e.g., https://vimeo.com/76979871) or just the video ID', 'vapfem'),
                'placeholder' => __('https://vimeo.com/76979871', 'vapfem'),
                'group' => 'video_source',
            ],
            '_html5_source_type' => [
                'type' => 'radio',
                'label' => __('Video Source Type', 'vapfem'),
                'desc' => __('Choose how to provide the video', 'vapfem'),
                'inline' => true,
                'options' => [
                    'upload' => __('Upload Video', 'vapfem'),
                    'link' => __('Video Link (CDN/URL)', 'vapfem'),
                ],
                'default' => 'upload',
                'group' => 'video_source',
            ],
            '_video_source' => [
                'type' => 'media',
                'label' => __('HTML5 Video File', 'vapfem'),
                'desc' => __('Select a video file from the media library', 'vapfem'),
                'button_text' => __('Select / Upload Video', 'vapfem'),
                'remove_text' => __('Remove', 'vapfem'),
                'library_type' => ['video'],
                'placeholder' => __('No video selected', 'vapfem'),
                'group' => 'video_source',
            ],
            '_html5_video_url' => [
                'type' => 'text',
                'label' => __('Video URL', 'vapfem'),
                'desc' => __('Enter direct URL to video file (e.g., https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4)', 'vapfem'),
                'placeholder' => __('https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4', 'vapfem'),
                'group' => 'video_source',
            ],
            '_audio_source_type' => [
                'type' => 'radio',
                'label' => __('Audio Upload or URL', 'vapfem'),
                'desc' => __('Choose how to provide the audio', 'vapfem'),
                'inline' => true,
                'options' => [
                    'upload' => __('Upload Audio', 'vapfem'),
                    'link' => __('Audio Link (CDN/URL)', 'vapfem'),
                ],
                'default' => 'upload',
                'group' => 'audio_source',
            ],
            '_audio_source' => [
                'type' => 'media',
                'label' => __('HTML5 Audio File', 'vapfem'),
                'desc' => __('Select an audio file from the media library. Supported formats: MP3, OGG, WAV, M4A, AAC', 'vapfem'),
                'button_text' => __('Select / Upload Audio', 'vapfem'),
                'remove_text' => __('Remove', 'vapfem'),
                'library_type' => ['audio'],
                'placeholder' => __('No audio selected', 'vapfem'),
                'group' => 'audio_source',
            ],
            '_html5_audio_url' => [
                'type' => 'text',
                'label' => __('Audio URL', 'vapfem'),
                'desc' => __('Enter direct URL to audio file (e.g., https://download.samplelib.com/mp3/sample-15s.mp3)', 'vapfem'),
                'placeholder' => __('https://download.samplelib.com/mp3/sample-15s.mp3', 'vapfem'),
                'group' => 'audio_source',
            ],
            '_autoplay' => [
                'type' => 'select',
                'label' => __('Autoplay', 'vapfem'),
                'desc' => __('Try to start this player automatically when the page loads. <strong>If autoplay does not work, turn on "Start Muted" below.</strong> Most browsers only allow autoplay when sound is off. <a href="https://developer.mozilla.org/en-US/docs/Web/Media/Guides/Autoplay" target="_blank" rel="noopener noreferrer">Learn more about autoplay policies</a>.', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'group' => 'playback_options',
            ],
            '_muted' => [
                'type' => 'select',
                'label' => __('Start Muted', 'vapfem'),
                'desc' => __('Start this player with sound off. Required for autoplay in modern browsers.', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'group' => 'playback_options',
            ],
            '_loop' => [
                'type' => 'select',
                'label' => __('Loop Playback', 'vapfem'),
                'desc' => __('Play again automatically when finished', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'group' => 'playback_options',
            ],
            '_volume' => [
                'type' => 'number',
                'label' => __('Initial Volume', 'vapfem'),
                'desc' => __('Choose how loud this player starts, from 0% (mute) to 100% (full volume). Viewers can still change the volume while watching.', 'vapfem'),
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => 100,
                'group' => 'playback_options',
            ],
            '_storage_enabled' => [
                'type' => 'select',
                'label' => __('Storage', 'vapfem'),
                'desc' => __('Allow use of local storage to store user settings (volume, speed, quality, etc.)', 'vapfem'),
                'options' => [
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '1',
                'group' => 'playback_options',
            ],
            '_speed_selected' => [
                'type' => 'select',
                'label' => __('Starting Playback Speed', 'vapfem'),
                'desc' => __('Choose the speed this player starts with.', 'vapfem'),
                'tooltip' => __('<strong>What it does</strong><br>Sets the speed this player uses when it first loads.<br><br><strong>Common choices</strong><br>Many visitors prefer podcasts, lessons, and videos at faster speeds like 1.25x or 1.5x.<br><br><strong>Note</strong><br>YouTube and Vimeo only support speeds from 0.5x to 2x. Options outside that range will be hidden automatically.', 'vapfem'),
                'tooltip_width' => 'wide',
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '0.5' => __('0.5x (Slow)', 'vapfem'),
                    '0.75' => __('0.75x', 'vapfem'),
                    '1' => __('1x (Normal)', 'vapfem'),
                    '1.25' => __('1.25x', 'vapfem'),
                    '1.5' => __('1.5x', 'vapfem'),
                    '1.75' => __('1.75x', 'vapfem'),
                    '2' => __('2x (Fast)', 'vapfem'),
                    '4' => __('4x (Very Fast)', 'vapfem'),
                ],
                'group' => 'playback_options',
            ],
            '_seek_time' => [
                'type' => 'number',
                'label' => __('Skip Forward/Back Amount', 'vapfem'),
                'desc' => __('How many seconds to skip forward or backward when viewers use keyboard shortcuts (arrow keys) for fast forward or rewind. For example, 10 seconds means each press of the arrow key jumps 10 seconds. Note: Clicking on the progress bar will still jump directly to that position.', 'vapfem'),
                'min' => 1,
                'max' => 60,
                'step' => 1,
                'unit' => 'Seconds',
                'group' => 'playback_options',
                'disabled' => true,
                'pro' => ['onclick' => 'openUpgradeModal'],
            ],
            '_invert_time' => [
                'type' => 'select',
                'label' => __('Time Display Format', 'vapfem'),
                'desc' => __('Choose what the time display shows:<br>• <strong>Remaining Time (Countdown):</strong> counts down how much time is left (for example, "-2:30")<br>• <strong>Elapsed Time:</strong> counts up how much has already played (for example, "2:30").<br>Default: Remaining time.', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Remaining Time (Countdown)', 'vapfem'),
                    '0' => __('Elapsed Time (Incremental)', 'vapfem'),
                ],
                'group' => 'playback_options',
                'disabled' => true,
                'pro' => ['onclick' => 'openUpgradeModal'],
            ],
            '_controls' => [
                'type' => 'checkbox',
                'label' => __('Player Controls', 'vapfem'),
                'desc' => __('<strong>Uncheck all = Use Global Option.</strong> <br>Choose which buttons appear on the player (play, progress bar, volume, etc.) and in which order. Check a control to show it, uncheck to hide it.', 'vapfem'),
                'is_multiple' => true,
                'is_sortable' => true,
                'max_height' => '300px',
                'options' => array_map( fn( $c ) => $c['label'], leanpl_get_controls_registry() ),
                'group' => 'playback_options',
                'disabled' => true,
                'pro' => ['onclick' => 'openUpgradeModal'],
            ],
            '_tooltips_seek' => [
                'type' => 'select',
                'label' => __('Seek Tooltips', 'vapfem'),
                'desc' => __('Hover over the progress/scrubber bar → shows the time at that point (e.g. "1:24"). Helps you see where you\'ll jump before clicking.', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'group' => 'playback_options',
            ],
            '_poster' => [
                'type' => 'media',
                'label' => __('Custom Thumbnail', 'vapfem'),
                'desc' => __('Video: thumbnail before playback. Audio: album art in the player. Playlist: cover image in the playlist list.', 'vapfem'),
                'button_text' => __('Select Image', 'vapfem'),
                'remove_text' => __('Remove', 'vapfem'),
                'library_type' => ['image'],
                'placeholder' => __('No poster image selected', 'vapfem'),
                'group' => 'video_source',
            ],
            '_click_to_play' => [
                'type' => 'select',
                'label' => __('Click Video to Play/Pause', 'vapfem'),
                'desc' => __('Allow clicking on the video to play or pause', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '1',
                'group' => 'video_options',
            ],
            '_fullscreen_enabled' => [
                'type' => 'select',
                'label' => __('Fullscreen Button', 'vapfem'),
                'desc' => __('Show a fullscreen button on the player', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '1',
                'group' => 'video_options',
            ],
            '_hide_controls' => [
                'type' => 'select',
                'label' => __('Auto-Hide Controls', 'vapfem'),
                'desc' => __('Hide controls while playing, show on hover or tap', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'group' => 'video_options',
                'disabled' => true,
                'pro' => ['onclick' => 'openUpgradeModal'],
            ],
            '_reset_on_end' => [
                'type' => 'select',
                'label' => __('Reset to Start When Finished', 'vapfem'),
                'desc' => __('Video-only. Reset to the beginning after playback ends. Has no effect on audio players.', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '0',
                'group' => 'video_options',
            ],
            '_ratio' => [
                'type' => 'text',
                'label' => __('Video Shape (Aspect Ratio)', 'vapfem'),
                'desc' => __('Sets the width-to-height shape of the player so the page does not jump while the video loads.<br>• <strong>Leave empty</strong> for automatic (most widescreen videos are 16:9).<br>• Enter <code>width:height</code> to force a shape, for example <code>16:9</code> (widescreen), <code>4:3</code> (older TV), <code>1:1</code> (square), or <code>9:16</code> (vertical / phone).<br>Video-only. Has no effect on audio players.', 'vapfem'),
                'placeholder' => __('16:9', 'vapfem'),
                'group' => 'video_options',
            ],
            '_tooltips_controls' => [
                'type' => 'select',
                'label' => __('Control Button Tooltips', 'vapfem'),
                'desc' => __('Hover over a button (play, mute, fullscreen) → shows its name as a little bubble ("Play", "Mute").', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'group' => 'playback_options',
            ],
            '_preload' => [
                'type' => 'select',
                'label' => __('HTML5 Media Preload', 'vapfem'),
                'desc' => __('Choose how much HTML5 media loads before the visitor presses play.', 'vapfem'),
                'tooltip' => __('<strong>Metadata</strong><br>Loads only basic media details when the page opens, such as duration. The actual audio/video starts loading when the visitor presses play. Recommended for most sites.<br><br><strong>None</strong><br>Does not load the media until the visitor presses play. Best when a page has many players or you want to save bandwidth.<br><br><strong>Auto</strong><br>Tells the browser to start loading the media early, before the visitor presses play. Use only when this media is important and most visitors are likely to play it.', 'vapfem'),
                'tooltip_width' => 'xl',
                'options' => [
                    'metadata' => __('Metadata (Recommended)', 'vapfem'),
                    'none' => __('None', 'vapfem'),
                    'auto' => __('Auto', 'vapfem'),
                ],
                'default' => 'metadata',
                'group' => 'playback_options',
            ],
            '_primary_color' => [
                'type'    => 'color',
                'label'   => __( 'Player Accent Color', 'vapfem' ),
                'desc'    => __( 'Overrides the global accent color for this player. Leave empty to inherit.', 'vapfem' ),
                'default' => '',
                'group'   => 'appearance',
                'pro'     => [
                    'badge_position' => 'inline',
                    'onclick'        => 'openUpgradeModal',
                ],
            ],
        ];
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_metaboxes' ] );
        add_action( 'admin_footer', [ $this, 'render_type_modal' ] );
    }

    public function add_metaboxes() {
        add_meta_box('leanpl_metabox', 'Video & Audio Player', [$this, 'render_metabox'], 'lean_player', 'normal', 'high');
        // Add shortcode metabox in sidebar
        add_meta_box('leanpl_shortcode', __('Shortcode', 'vapfem'), [$this, 'render_shortcode_metabox'], 'lean_player', 'side', 'default');
    }

    public function render_metabox($post) {
        wp_nonce_field('leanpl_save_metabox', 'leanpl_metabox_nonce');

        $settings = \Lex\Settings\V2\Settings::getInstance('leanpl');

        $fields = self::get_field_definitions();
        $player_type_value = self::get_field_value( $post->ID, '_player_type' );

        // On new posts, honour the ?player_type query param set by the type-selection modal.
        $is_new   = $post->post_status === 'auto-draft';
        $url_type = isset( $_GET['player_type'] ) ? sanitize_key( wp_unslash( $_GET['player_type'] ) ) : '';
        if ( $is_new && $url_type && in_array( $url_type, [ 'video', 'audio' ], true ) ) {
            $player_type_value = $url_type;
        }

        $playlist_enabled = leanpl_get_option( 'playlist.enabled', true );

        ?>
        <div id="lpl-metabox-wrapper">
            <input type="hidden" name="_player_type" value="<?php echo esc_attr( $player_type_value ); ?>">

            <div class="lex-vtabs lex-vtabs--apple lpl-player-vtabs"
                 data-player-type="<?php echo esc_attr( $player_type_value ); ?>"
                 data-storage-suffix="<?php echo esc_attr( $post->ID ); ?>">

                <?php
                $source_tab = [
                    'id'    => 'p-source',
                    'label' => __( 'Source', 'vapfem' ),
                    'icon'  => leanpl_ssot( 'icons', 'source_svg' ),
                ];
                $optional_tabs = [
                    [
                        'id'    => 'p-behavior',
                        'label' => __( 'Behavior', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'behavior_svg' ),
                    ],
                    [
                        'id'    => 'p-controls',
                        'label' => __( 'Controls', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'controls_svg' ),
                    ],
                    [
                        'id'    => 'p-video',
                        'label' => __( 'Video-Only', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'video_svg' ),
                    ],
                    [
                        'id'    => 'p-appearance',
                        'label' => __( 'Appearance', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'appearance_svg' ),
                    ],
                ];
                if ( $playlist_enabled ) {
                    $optional_tabs[] = [
                        'id'    => 'p-playlist',
                        'label' => __( 'Playlist Data', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'playlist_svg' ),
                    ];
                }

                $groups = [
                    [ 'label' => '',                          'tabs' => [ $source_tab ] ],
                    [ 'label' => __( 'Optional', 'vapfem' ),  'tabs' => $optional_tabs ],
                ];
                echo \Lex\Settings\V2\Services\Vtabs::render_nav( $groups );
                ?>

                <div class="lex-vtabs__content">

                    <div class="lex-vtab-pane" data-vtab="p-source">
                        <?php
                        $this->render_video_source_section($post, $settings, $fields, $player_type_value);
                        $this->render_audio_source_section($post, $settings, $fields, $player_type_value);
                        $this->render_poster_field($post, $settings, $fields);
                        ?>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="p-behavior">
                        <?php $this->render_behavior_options_section($post, $settings, $fields, $player_type_value); ?>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="p-controls">
                        <?php $this->render_controls_section($post, $settings, $fields); ?>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="p-video">
                        <?php $this->render_video_only_section($post, $settings, $fields, $player_type_value); ?>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="p-appearance">
                        <?php $this->render_appearance_section( $post, $settings, $fields ); ?>
                    </div>

                    <?php if ( $playlist_enabled ) : ?>
                    <div class="lex-vtab-pane" data-vtab="p-playlist">
                        <?php $this->render_playlist_fields_section( $post, $settings ); ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render a single field
     * 
     * Combines prepare_field_args and fieldRenderer->render into one call
     * 
     * @param object $settings Settings instance
     * @param int $post_id Post ID
     * @param string $field_name Field name
     * @param array $fields All field definitions
     * @return void
     */
    private function render_field($settings, $post_id, $field_name, $fields) {
        $field_config = $fields[$field_name];
        $args = $this->prepare_field_args($post_id, $field_name, $field_config);

        $settings->fieldRenderer->render($field_config['type'], $field_name, $args);
    }

    /**
     * Get display style for conditional section
     * 
     * @param mixed $current_value Current field value
     * @param string $expected_value Expected value to show section
     * @return string 'block' or 'none'
     */
    private function get_conditional_display($current_value, $expected_value) {
        return ($current_value === $expected_value) ? 'block' : 'none';
    }

    /**
     * Get field keys by group
     * 
     * Extracts field keys from definitions that belong to a specific group.
     * This uses the 'group' metadata in field definitions - single source of truth.
     * 
     * @param string $group_name Group name
     * @return array Array of field keys
     */
    private function get_field_keys_by_group($group_name) {
        $fields = self::get_field_definitions();
        $keys = [];
        
        foreach ($fields as $key => $config) {
            if (isset($config['group']) && $config['group'] === $group_name) {
                $keys[] = $key;
            }
        }
        
        return $keys;
    }

    /**
     * Render multiple fields from a group
     * 
     * @param object $settings Settings instance
     * @param int $post_id Post ID
     * @param string $group_name Group name
     * @param array $fields All field definitions
     * @return void
     */
    private function render_field_group($settings, $post_id, $group_name, $fields) {
        $field_keys = $this->get_field_keys_by_group($group_name);
        
        foreach ($field_keys as $field_key) {
            $this->render_field($settings, $post_id, $field_key, $fields);
        }
    }

    public function render_type_modal() {
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->id, [ 'edit-lean_player', 'lean_player' ], true ) ) {
            return;
        }
        leanpl_render_type_modal_html( [
            'modal_id'    => 'lpl-plr-builder-modal',
            'backdrop_id' => 'lpl-plr-builder-modal-backdrop',
            'close_id'    => 'lpl-plr-builder-modal-close',
            'title'       => __( 'What kind of player?', 'vapfem' ),
            'sub'         => __( 'Choose once. This sets the source fields.', 'vapfem' ),
            'cards'       => [
                [
                    'id'    => 'lpl-plr-builder-type-video',
                    'type'  => 'video',
                    'svg'   => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="1.75" fill="none"/><path d="M10 9l5 3-5 3V9z" fill="currentColor"/></svg>',
                    'title' => __( 'Video Player', 'vapfem' ),
                    'desc'  => __( 'YouTube, Vimeo, or self-hosted video files', 'vapfem' ),
                ],
                [
                    'id'    => 'lpl-plr-builder-type-audio',
                    'type'  => 'audio',
                    'svg'   => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 18V6l12-2v12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.75" fill="none"/><circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="1.75" fill="none"/></svg>',
                    'title' => __( 'Audio Player', 'vapfem' ),
                    'desc'  => __( 'Music, podcasts, or audiobooks', 'vapfem' ),
                ],
            ],
        ] );
    }

    private function render_poster_field($post, $settings, $fields) {
        $settings->sectionRenderer->startSection( 'poster-field', '', [ 'disable_save_button' => true, 'no_title' => true ] );
        $this->render_field( $settings, $post->ID, '_poster', $fields );
        $settings->sectionRenderer->endSection();
    }

    /**
     * Render video source configuration section
     * 
     * All fields in this section belong to the 'video_source' group.
     * Field names are still explicit due to complex conditional logic.
     * 
     * @param object $post Post object
     * @param object $settings Settings instance
     * @param array $fields All field definitions
     * @param string $player_type_value Current player type value
     * @return void
     */
    private function render_video_source_section($post, $settings, $fields, $player_type_value) {
        // Get all video_source field keys from group definition (single source of truth)
        $video_source_keys = $this->get_field_keys_by_group('video_source');
        
        $display = $this->get_conditional_display($player_type_value, 'video');
        ?>
        <div class="lpl-conditional-section" 
             data-show-if="_player_type" 
             data-show-value="video" 
             style="display: <?php echo esc_attr($display); ?>;">
            <?php
            $settings->sectionRenderer->startSection('video-source-config', esc_html__('VIDEO: Source Configuration', 'vapfem'), ['disable_save_button' => true, 'no_title' => true]);
            
            $this->render_field($settings, $post->ID, '_video_type', $fields);
            
            $video_type_value = self::get_field_value($post->ID, '_video_type');
            
            // YouTube URL field
            $youtube_display = $this->get_conditional_display($video_type_value, 'youtube');
            ?>
            <div class="lpl-conditional-section" 
                 data-show-if="_video_type" 
                 data-show-value="youtube" 
                 style="display: <?php echo esc_attr($youtube_display); ?>;">
                <?php
                $this->render_field($settings, $post->ID, '_youtube_url', $fields);
                ?>
            </div>
            
            <?php
            // Vimeo URL field
            $vimeo_display = $this->get_conditional_display($video_type_value, 'vimeo');
            ?>
            <div class="lpl-conditional-section" 
                 data-show-if="_video_type" 
                 data-show-value="vimeo" 
                 style="display: <?php echo esc_attr($vimeo_display); ?>;">
                <?php
                $this->render_field($settings, $post->ID, '_vimeo_url', $fields);
                ?>
            </div>
            
            <?php
            // HTML5 Video Source fields
            $html5_display = $this->get_conditional_display($video_type_value, 'html5');
            ?>
            <div class="lpl-conditional-section" 
                 data-show-if="_video_type" 
                 data-show-value="html5" 
                 style="display: <?php echo esc_attr($html5_display); ?>;">
                <?php
                $this->render_field($settings, $post->ID, '_html5_source_type', $fields);
                
                $html5_source_type_value = self::get_field_value($post->ID, '_html5_source_type');
                
                // Upload Video field
                $upload_display = $this->get_conditional_display($html5_source_type_value, 'upload');
                ?>
                <div class="lpl-conditional-section" 
                     data-show-if="_html5_source_type" 
                     data-show-value="upload" 
                     style="display: <?php echo esc_attr($upload_display); ?>;">
                    <?php
                    $this->render_field($settings, $post->ID, '_video_source', $fields);
                    ?>
                </div>
                
                <?php
                // Video URL field
                $link_display = $this->get_conditional_display($html5_source_type_value, 'link');
                ?>
                <div class="lpl-conditional-section" 
                     data-show-if="_html5_source_type" 
                     data-show-value="link" 
                     style="display: <?php echo esc_attr($link_display); ?>;">
                    <?php
                    $this->render_field($settings, $post->ID, '_html5_video_url', $fields);
                    ?>
                </div>
            </div>
            
            <?php
            $settings->sectionRenderer->endSection();
            ?>
        </div>
        <?php
    }

    /**
     * Render audio source configuration section
     * 
     * @param object $post Post object
     * @param object $settings Settings instance
     * @param array $fields All field definitions
     * @param string $player_type_value Current player type value
     * @return void
     */
    private function render_audio_source_section($post, $settings, $fields, $player_type_value) {
        $display = $this->get_conditional_display($player_type_value, 'audio');
        ?>
        <div class="lpl-conditional-section" 
             data-show-if="_player_type" 
             data-show-value="audio" 
             style="display: <?php echo esc_attr($display); ?>;">
            <?php
            $settings->sectionRenderer->startSection('audio-source-config', esc_html__('AUDIO: Source Configuration', 'vapfem'), ['disable_save_button' => true, 'no_title' => true]);
            
            $this->render_field($settings, $post->ID, '_audio_source_type', $fields);
            
            $audio_source_type_value = self::get_field_value($post->ID, '_audio_source_type');
            
            // Upload Audio field
            $upload_display = $this->get_conditional_display($audio_source_type_value, 'upload');
            ?>
            <div class="lpl-conditional-section" 
                 data-show-if="_audio_source_type" 
                 data-show-value="upload" 
                 style="display: <?php echo esc_attr($upload_display); ?>;">
                <?php
                $this->render_field($settings, $post->ID, '_audio_source', $fields);
                ?>
            </div>
            
            <?php
            // Audio URL field
            $link_display = $this->get_conditional_display($audio_source_type_value, 'link');
            ?>
            <div class="lpl-conditional-section" 
                 data-show-if="_audio_source_type" 
                 data-show-value="link" 
                 style="display: <?php echo esc_attr($link_display); ?>;">
                <?php
                $this->render_field($settings, $post->ID, '_html5_audio_url', $fields);
                ?>
            </div>
            
            <?php
            $settings->sectionRenderer->endSection();
            ?>
        </div>
        <?php
    }

    /**
     * Render playlist data fields section
     * Shows optional duration and subtitle fields used by the playlist feature
     *
     * @param object $post    Post object
     * @param object $settings Settings instance
     * @return void
     */
    private function render_playlist_fields_section( $post, $settings ) {
        $settings->sectionRenderer->startSection( 'playlist-data', esc_html__( 'Playlist Data (Optional)', 'vapfem' ), [ 'disable_save_button' => true, 'no_title' => true ] );

        $duration_value = get_post_meta( $post->ID, '_duration', true );
        $meta_text_value = get_post_meta( $post->ID, '_meta_text', true );

        $settings->fieldRenderer->render( 'text', '_duration', [
            'label'       => esc_html__( 'Duration', 'vapfem' ),
            'desc'        => esc_html__( 'Used only for playlist display. Example: 3:45', 'vapfem' ),
            'placeholder' => '0:00',
            'value'       => $duration_value,
        ] );

        $settings->fieldRenderer->render( 'text', '_meta_text', [
            'label'       => esc_html__( 'Meta Text', 'vapfem' ),
            'desc'        => esc_html__( 'Shown below the title in the playlist list. E.g. BBC News, Serial Podcast, Chapter 3.', 'vapfem' ),
            'placeholder' => esc_html__( 'e.g., BBC News', 'vapfem' ),
            'value'       => $meta_text_value,
        ] );

        $settings->sectionRenderer->endSection();
    }

    /**
     * Render appearance section (per-player accent color override).
     *
     * @param object $post     Post object
     * @param object $settings Settings instance
     * @param array  $fields   All field definitions
     * @return void
     */
    private function render_appearance_section( $post, $settings, $fields ) {
        $settings->sectionRenderer->startSection( 'appearance', esc_html__( 'Appearance', 'vapfem' ), [
            'disable_save_button' => true,
            'no_title'            => true,
        ] );

        $this->render_field( $settings, $post->ID, '_primary_color', $fields );

        $settings->sectionRenderer->endSection();
    }

    /**
     * Render behavior options section (shared).
     *
     * Hosts the Auto-Start / Playback / advanced accordions that live inside the
     * "Behavior" tab in the player metabox.
     *
     * @param object $post Post object
     * @param object $settings Settings instance
     * @param array $fields All field definitions
     * @return void
     */
    private function render_behavior_options_section( $post, $settings, $fields, $player_type_value = '' ) {
        $settings->sectionRenderer->startSection( 'playback-auto-start', esc_html__( 'Auto-Start', 'vapfem' ), [
            'disable_save_button' => true,
            'collapsed'           => false,
            'accordion'           => true,
            'exclusive'           => 'metabox-playback',
            'summary_labels'      => [ esc_html__( 'Autoplay', 'vapfem' ), esc_html__( 'Start Muted', 'vapfem' ) ],
        ] );
        $this->render_field( $settings, $post->ID, '_autoplay', $fields );
        $this->render_field( $settings, $post->ID, '_muted', $fields );
        $settings->sectionRenderer->endSection();

        $settings->sectionRenderer->startSection( 'playback-core', esc_html__( 'Playback', 'vapfem' ), [
            'disable_save_button' => true,
            'collapsed'           => true,
            'accordion'           => true,
            'exclusive'           => 'metabox-playback',
            'summary_labels'      => [ esc_html__( 'Loop', 'vapfem' ), esc_html__( 'Volume', 'vapfem' ), esc_html__( 'Speed', 'vapfem' ) ],
        ] );
        $this->render_field( $settings, $post->ID, '_loop', $fields );
        $this->render_field( $settings, $post->ID, '_volume', $fields );
        $this->render_field( $settings, $post->ID, '_speed_selected', $fields );
        $settings->sectionRenderer->endSection();

        $settings->sectionRenderer->startSection( 'playback-advanced', esc_html__( 'Advanced', 'vapfem' ), [
            'disable_save_button' => true,
            'collapsed'           => true,
            'accordion'           => true,
            'exclusive'           => 'metabox-playback',
            'summary_labels'      => [ esc_html__( 'Preload', 'vapfem' ), esc_html__( 'Storage', 'vapfem' ) ],
        ] );
        $this->render_field( $settings, $post->ID, '_preload', $fields );
        $this->render_field( $settings, $post->ID, '_storage_enabled', $fields );
        $settings->sectionRenderer->endSection();
    }

    /**
     * Render the Controls tab (Controls & Keyboard). Mirrors the global
     * Settings "Controls" vtab for cross-surface consistency.
     */
    private function render_controls_section( $post, $settings, $fields ) {
        $settings->sectionRenderer->startSection( 'controls-buttons', esc_html__( 'Buttons & Order', 'vapfem' ), [
            'disable_save_button' => true,
            'collapsed'           => true,
            'accordion'           => true,
            'exclusive'           => 'metabox-controls',
            'summary_labels'      => [ esc_html__( 'Controls', 'vapfem' ) ],
        ] );
        $this->render_field( $settings, $post->ID, '_controls', $fields );
        $settings->sectionRenderer->endSection();

        $settings->sectionRenderer->startSection( 'controls-time-tooltips', esc_html__( 'Time & Tooltips', 'vapfem' ), [
            'disable_save_button' => true,
            'collapsed'           => true,
            'accordion'           => true,
            'exclusive'           => 'metabox-controls',
            'summary_labels'      => [ esc_html__( 'Invert Time', 'vapfem' ), esc_html__( 'Tooltips', 'vapfem' ) ],
        ] );
        $this->render_field( $settings, $post->ID, '_invert_time', $fields );
        $this->render_field( $settings, $post->ID, '_tooltips_controls', $fields );
        $this->render_field( $settings, $post->ID, '_tooltips_seek', $fields );
        $settings->sectionRenderer->endSection();

        $settings->sectionRenderer->startSection( 'controls-keyboard', esc_html__( 'Keyboard', 'vapfem' ), [
            'disable_save_button' => true,
            'collapsed'           => true,
            'accordion'           => true,
            'exclusive'           => 'metabox-controls',
            'summary_labels'      => [ esc_html__( 'Seek Time', 'vapfem' ) ],
        ] );
        $this->render_field( $settings, $post->ID, '_seek_time', $fields );
        $settings->sectionRenderer->endSection();
    }

    /**
     * Render the Video-Only tab. Mirrors the global Settings "Video-Only" vtab.
     * Conditional: only meaningful for video players.
     */
    private function render_video_only_section( $post, $settings, $fields, $player_type_value = '' ) {
        $display = $this->get_conditional_display( $player_type_value, 'video' );
        ?>
        <div class="lpl-conditional-section"
             data-show-if="_player_type"
             data-show-value="video"
             style="display: <?php echo esc_attr( $display ); ?>;">
            <?php
            $settings->sectionRenderer->startSection( 'playback-display', esc_html__( 'Video-Only', 'vapfem' ), [
                'disable_save_button' => true,
                'no_title'            => true,
            ] );
            $this->render_field( $settings, $post->ID, '_click_to_play', $fields );
            $this->render_field( $settings, $post->ID, '_fullscreen_enabled', $fields );
            $this->render_field( $settings, $post->ID, '_hide_controls', $fields );
            $this->render_field( $settings, $post->ID, '_reset_on_end', $fields );
            $this->render_field( $settings, $post->ID, '_ratio', $fields );
            $settings->sectionRenderer->endSection();
            ?>
        </div>
        <?php
    }

    /**
     * Render video-specific options section
     * 
     * @param object $post Post object
     * @param object $settings Settings instance
     * @param array $fields All field definitions
     * @param string $player_type_value Current player type value
     * @return void
     */
    private function render_video_options_section($post, $settings, $fields, $player_type_value) {
        $display = $this->get_conditional_display($player_type_value, 'video');
        ?>
        <div class="lpl-conditional-section" 
             data-show-if="_player_type" 
             data-show-value="video" 
             style="display: <?php echo esc_attr($display); ?>;">
            <?php
            $settings->sectionRenderer->startSection('video-specific-options', esc_html__('Video Specific Options (Optional)', 'vapfem'), ['disable_save_button' => true, 'no_title' => true]);
            
            $this->render_field_group($settings, $post->ID, 'video_options', $fields);
            
            $settings->sectionRenderer->endSection();
            ?>
        </div>
        <?php
    }

    /**
     * Render shortcode metabox in sidebar
     * 
     * @param WP_Post $post Post object
     * @return void
     */
    public function render_shortcode_metabox($post) {
        // Only show if post has been saved (has an ID)
        if (!$post->ID || $post->post_status === 'auto-draft') {
            ?>
            <p class="description">
                <?php echo esc_html__('Save the post to generate the shortcode.', 'vapfem'); ?>
            </p>
            <?php
            return;
        }

        // Generate shortcode
        $shortcode = '[lean_player id="' . esc_attr($post->ID) . '"]';
        ?>
        <div class="lpl-shortcode-metabox">
            <div class="lex-field" style="display: flex; gap: 5px; margin-bottom: 10px; margin-top: 15px;">
                <input 
                    type="text" 
                    readonly 
                    value="<?php echo esc_attr($shortcode); ?>" 
                    class="regular-text"
                    id="lpl-shortcode-input"
                    onclick="this.select();"
                    style="flex: 1;"
                />
                <button 
                    type="button"
                    class="lex-copy-button" 
                    data-lex-copy="<?php echo esc_attr($shortcode); ?>"
                    style="width: 70px; flex-shrink: 0;"
                >
                    <?php echo esc_html__('Copy', 'vapfem'); ?>
                </button>
            </div>
            
            <p class="description" style="">
                <span class="dashicons dashicons-info" style=""></span>
                <?php echo esc_html__('Paste this shortcode into any page, post, or widget to display the player.', 'vapfem'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Get field value with default fallback (public static)
     * 
     * @param int $post_id Post ID
     * @param string $field_name Field name (meta key)
     * @return mixed Field value or default if not set
     */
    public static function get_field_value($post_id, $field_name) {
        $fields = self::get_field_definitions();
        $value = get_post_meta($post_id, $field_name, true);
        // Only use default if meta doesn't exist (not if value is '0' or empty string)
        // This preserves explicit user choices like unchecked checkboxes
        if (!metadata_exists('post', $post_id, $field_name) && isset($fields[$field_name]['default'])) {
            $value = $fields[$field_name]['default'];
        }
        return $value;
    }

    /**
     * Prepare field arguments for rendering with default support
     * 
     * This method handles the logic for determining field values:
     * - If post meta exists, uses the stored value
     * - If post meta doesn't exist, allows default from field definition to be used
     * - For select fields with inheritance (empty string option), converts meta values
     * 
     * @param int $post_id Post ID
     * @param string $field_name Field name (meta key)
     * @param array $field_config Field configuration from get_field_definitions()
     * @return array Field arguments ready for fieldRenderer->render()
     */
    private function prepare_field_args($post_id, $field_name, $field_config) {
        $value = get_post_meta($post_id, $field_name, true);
        $exists = metadata_exists('post', $post_id, $field_name);
        
        $args = $this->build_base_args($field_name, $field_config, $post_id);
        $args['value'] = $this->determine_field_value($value, $exists, $field_config, $field_name);
        
        $this->add_inheritance_info_if_needed($args, $value, $exists, $field_name, $field_config);
        
        unset($args['type']);
        
        return $args;
    }

    /**
     * Build base field arguments array
     * 
     * @param string $field_name Field name
     * @param array $field_config Field configuration
     * @return array Base arguments array
     */
    private function build_base_args($field_name, $field_config, $post_id = 0) {
        $options = isset($field_config['options']) ? $field_config['options'] : null;
        
        $args = array_merge([
            'field_name' => $field_name,
        ], $field_config);
        
        // Preserve options array structure (prevents re-indexing of numeric string keys)
        if ($options !== null) {
            $args['options'] = $options;
        }

        // Allow pro plugin to filter metabox field config before rendering
        $args = apply_filters(
            'leanpl/metabox/field_config',
            $args,
            $field_name,
            $post_id
        );
        
        return $args;
    }

    /**
     * Determine the field value based on meta existence and field type
     * 
     * @param mixed $value Current meta value
     * @param bool $exists Whether meta exists
     * @param array $field_config Field configuration
     * @param string $field_name Field name
     * @return mixed Determined field value
     */
    private function determine_field_value($value, $exists, $field_config, $field_name) {
        $is_empty = !$exists || $value === '' || $value === null;
        $field_type = $field_config['type'];
        
        if ($field_type === 'select') {
            return $this->determine_select_value($value, $is_empty, $field_config);
        }
        
        return $this->determine_regular_field_value($value, $is_empty, $field_config, $field_name);
    }

    /**
     * Determine value for select fields
     * 
     * @param mixed $value Current meta value
     * @param bool $is_empty Whether value is empty
     * @param array $field_config Field configuration
     * @return mixed Determined select value
     */
    private function determine_select_value($value, $is_empty, $field_config) {
        $has_inheritance = isset($field_config['options']['']);
        
        if ($has_inheritance) {
            return $this->determine_select_with_inheritance($value, $is_empty);
        }
        
        return $this->determine_select_without_inheritance($value, $is_empty, $field_config);
    }

    /**
     * Determine value for select fields with inheritance support
     * 
     * @param mixed $value Current meta value
     * @param bool $is_empty Whether value is empty
     * @return string Determined value (empty string for inherit, or '1'/'0')
     */
    private function determine_select_with_inheritance($value, $is_empty) {
        if ($is_empty) {
            return '';
        }
        
        // Convert old checkbox format to select format
        if ($value === '1' || $value === 1 || $value === true) {
            return '1';
        }
        
        if ($value === '0' || $value === 0 || $value === false) {
            return '0';
        }
        
        return $value;
    }

    /**
     * Determine value for select fields without inheritance
     * 
     * @param mixed $value Current meta value
     * @param bool $is_empty Whether value is empty
     * @param array $field_config Field configuration
     * @return mixed Determined value (default if empty, otherwise the value)
     */
    private function determine_select_without_inheritance($value, $is_empty, $field_config) {
        if ($is_empty) {
            return $field_config['default'] ?? '';
        }
        
        return $value;
    }

    /**
     * Determine value for regular fields (text, number, etc.)
     * 
     * @param mixed $value Current meta value
     * @param bool $is_empty Whether value is empty
     * @param array $field_config Field configuration
     * @param string $field_name Field name
     * @return mixed Determined value
     */
    private function determine_regular_field_value($value, $is_empty, $field_config, $field_name) {
        if ($is_empty) {
            $has_global_option = $this->field_has_global_option($field_name);
            
            if ($has_global_option) {
                return '';
            }
            
            return $field_config['default'] ?? '';
        }
        
        return $value;
    }

    /**
     * Add inheritance information if field is empty and has global option
     * 
     * @param array &$args Field arguments (passed by reference)
     * @param mixed $value Current meta value
     * @param bool $exists Whether meta exists
     * @param string $field_name Field name
     * @param array $field_config Field configuration
     * @return void
     */
    private function add_inheritance_info_if_needed(&$args, $value, $exists, $field_name, $field_config) {
        $is_empty = !$exists || $value === '' || $value === null;
        $has_global_option = $this->field_has_global_option($field_name);
        
        if ($is_empty && $has_global_option) {
            $this->add_inherited_value_info($args, $field_name, $field_config);
        }
    }

    /**
     * Check if a field has a global option
     * 
     * @param string $field_name Field name (meta key with _ prefix)
     * @return bool True if field has global option
     */
    private function field_has_global_option($field_name) {
        // Convert meta key to global option key (remove _ prefix)
        $global_key = ltrim($field_name, '_');
        
        return in_array($global_key, self::FIELDS_WITH_GLOBAL_OPTIONS, true);
    }
    
    /**
     * Add inherited value information to field args
     * 
     * Gets global option value and adds it to field config for display in description area.
     * Only shows for Global Options, not for Default values.
     * 
     * @param array &$args Field arguments (passed by reference)
     * @param string $field_name Field name (meta key with _ prefix)
     * @param array $field_config Field configuration
     * @return void
     */
    private function add_inherited_value_info(&$args, $field_name, $field_config) {
        // Convert meta key to global option key (remove _ prefix)
        // _seek_time → seek_time
        $global_key = ltrim($field_name, '_');
        
        // Try to get global option value
        $settings = \Lex\Settings\V2\Settings::getInstance('leanpl');
        $global_value = $settings->dataManager->get($global_key, null);
        
        // Only show inherited value if it comes from Global Options
        if ($global_value !== null) {
            $args['inherited_value'] = $global_value;
            $args['inherited_source'] = 'Global Options';
        }
        // Don't show for Default values - user requested only Global Options
    }
}

Metaboxes::get_instance();
