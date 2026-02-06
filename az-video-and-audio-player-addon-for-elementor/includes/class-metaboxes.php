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
                'label' => __('Audio Source Type', 'vapfem'),
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
                'label' => __('Loop', 'vapfem'),
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
                'label' => __('Default Playback Speed', 'vapfem'),
                'desc' => __('Set the initial playback speed when this player loads. Many users now prefer listening to podcasts and videos at faster speeds (1.2x or 1.5x). <br><strong>Note:</strong> YouTube and Vimeo will ignore any options outside the 0.5-2 range, so options outside of this range will be hidden automatically.', 'vapfem'),
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
                'label' => __('Skip Amount (Forward / Back)', 'vapfem'),
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
                'options' => [
                    'play-large' => __('Play Large (Video Only)', 'vapfem'),
                    'play' => __('Play', 'vapfem'),
                    'progress' => __('Progress Bar', 'vapfem'),
                    'current-time' => __('Current Time', 'vapfem'),
                    'mute' => __('Mute', 'vapfem'),
                    'volume' => __('Volume', 'vapfem'),
                    'captions' => __('Caption (Video Only)', 'vapfem'),
                    'settings' => __('Settings Icon', 'vapfem'),
                    'pip' => __('PIP (Video Only)', 'vapfem'),
                    'airplay' => __('Airplay', 'vapfem'),
                    'fullscreen' => __('Fullscreen (Video Only)', 'vapfem'),
                    'download' => __('Download', 'vapfem'),
                ],
                'group' => 'playback_options',
                'disabled' => true,
                'pro' => ['onclick' => 'openUpgradeModal'],
            ],
            '_tooltips_seek' => [
                'type' => 'select',
                'label' => __('Seek Tooltips', 'vapfem'),
                'desc' => __('Display a seek tooltip to indicate on click where the media would seek to', 'vapfem'),
                'options' => [
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '1',
                'group' => 'playback_options',
            ],
            '_poster' => [
                'type' => 'media',
                'label' => __('Poster Image', 'vapfem'),
                'desc' => __('Thumbnail image displayed before video playback starts', 'vapfem'),
                'button_text' => __('Select Image', 'vapfem'),
                'remove_text' => __('Remove', 'vapfem'),
                'library_type' => ['image'],
                'placeholder' => __('No poster image selected', 'vapfem'),
                'group' => 'video_source',
            ],
            '_click_to_play' => [
                'type' => 'select',
                'label' => __('Click to Play / Pause', 'vapfem'),
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
                'desc' => __('Reset video to the beginning after it finishes', 'vapfem'),
                'options' => [
                    '' => __('Use Global Option', 'vapfem'),
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '0',
                'group' => 'video_options',
            ],
            '_tooltips_controls' => [
                'type' => 'select',
                'label' => __('Control Tooltips', 'vapfem'),
                'desc' => __('Display control labels as tooltips on :hover & :focus. Examples: play icon, mute/unmute, pip, fullscreen, etc. By default, the labels are screen reader only.', 'vapfem'),
                'options' => [
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '0',
                'group' => 'video_options',
            ],
            '_preload' => [
                'type' => 'select',
                'label' => __('Preload', 'vapfem'),
                'desc' => __('How the audio should be loaded', 'vapfem'),
                'options' => [
                    'auto' => __('Auto - Load entire audio file', 'vapfem'),
                    'metadata' => __('Metadata - Load only metadata', 'vapfem'),
                    'none' => __('None - Don\'t preload', 'vapfem'),
                ],
                'default' => 'metadata',
                'group' => 'audio_options',
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
        add_action('add_meta_boxes', [$this, 'add_metaboxes']);
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
        $player_type_value = self::get_field_value($post->ID, '_player_type');

        ?>
        <div id="lpl-metabox-wrapper">
            <?php

            // Player type field
            $this->render_player_type_field($post, $settings, $fields);

            // Video source section
            $this->render_video_source_section($post, $settings, $fields, $player_type_value);

            // Audio source section
            $this->render_audio_source_section($post, $settings, $fields, $player_type_value);

            // Playback options section
            $this->render_playback_options_section($post, $settings, $fields);

            // Video specific options section
            $this->render_video_options_section($post, $settings, $fields, $player_type_value);

            // Audio specific options section
            $this->render_audio_options_section($post, $settings, $fields, $player_type_value);
            ?>
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

    /**
     * Render player type field
     * 
     * @param object $post Post object
     * @param object $settings Settings instance
     * @param array $fields All field definitions
     * @return void
     */
    private function render_player_type_field($post, $settings, $fields) {
        $field_keys = $this->get_field_keys_by_group('player_type');
        ?>
        <table class="form-table lex-form-table">
            <?php
            $this->render_field($settings, $post->ID, $field_keys[0], $fields);
            ?>
        </table>
        <?php
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
            $settings->sectionRenderer->startSection('video-source-config', esc_html__('VIDEO: Source Configuration', 'vapfem'), ['disable_save_button' => true]);
            
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
            // Poster Image field (applies to all video types)
            $this->render_field($settings, $post->ID, '_poster', $fields);
            ?>
            
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
            $settings->sectionRenderer->startSection('audio-source-config', esc_html__('AUDIO: Source Configuration', 'vapfem'), ['disable_save_button' => true]);
            
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
     * Render playback options section (shared)
     * 
     * @param object $post Post object
     * @param object $settings Settings instance
     * @param array $fields All field definitions
     * @return void
     */
    private function render_playback_options_section($post, $settings, $fields) {
        $settings->sectionRenderer->startSection('playback-options', esc_html__('Playback Options (Optional)', 'vapfem'), ['disable_save_button' => true]);
        
        $this->render_field_group($settings, $post->ID, 'playback_options', $fields);
        
        $settings->sectionRenderer->endSection();
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
            $settings->sectionRenderer->startSection('video-specific-options', esc_html__('Video Specific Options (Optional)', 'vapfem'), ['disable_save_button' => true]);
            
            $this->render_field_group($settings, $post->ID, 'video_options', $fields);
            
            $settings->sectionRenderer->endSection();
            ?>
        </div>
        <?php
    }

    /**
     * Render audio-specific options section
     * 
     * @param object $post Post object
     * @param object $settings Settings instance
     * @param array $fields All field definitions
     * @param string $player_type_value Current player type value
     * @return void
     */
    private function render_audio_options_section($post, $settings, $fields, $player_type_value) {
        $display = $this->get_conditional_display($player_type_value, 'audio');
        ?>
        <div class="lpl-conditional-section" 
             data-show-if="_player_type" 
             data-show-value="audio" 
             style="display: <?php echo esc_attr($display); ?>;">
            <?php
            $settings->sectionRenderer->startSection('audio-specific-options', esc_html__(' Audio Specific Options (Optional)', 'vapfem'), ['disable_save_button' => true]);
            
            $this->render_field_group($settings, $post->ID, 'audio_options', $fields);
            
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