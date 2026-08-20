<?php
namespace LeanPL;

/**
 * Player field registry - single source of truth.
 *
 * Used to be the classic per-player metabox too (add_meta_boxes render,
 * conditional sections, the sidebar shortcode box). That whole classic-UI
 * half was removed once admin-new's Edit Player screen + Admin_New\Metabox_Save
 * became the only way to edit a player - same cleanup already done for the
 * playlist side (see class-playlist-metaboxes.php's removal). What's left is
 * only what's still load-bearing: get_field_definitions() (functions-player.php,
 * class-player-shortcode.php, admin-new's field registry) and get_field_value()
 * (its only real consumer besides internal callers).
 */
class Metaboxes {

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
            '_player_layout' => [
                'type' => 'image-select',
                'label' => __('Layout', 'vapfem'),
                'desc' => __('Dont select any if you want to use the global Layout', 'vapfem'),
                'options' => array_merge(
                    array_map(
                        function ( $value, $layout ) {
                            return [ 'value' => $value, 'label' => $layout['label'], 'image' => $layout['image'] ];
                        },
                        array_keys( leanpl_get_player_layouts() ),
                        leanpl_get_player_layouts()
                    ),
                    [
                        [
                            'value' => '__custom_preset__',
                            'label' => __('Custom Preset', 'vapfem'),
                            'launcher' => 'LeanPLCustomPreset.open',
                        ],
                    ]
                ),
                'default' => '',
                'group' => 'player_preset',
                'columns' => 3,
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
                'sanitize' => 'url',
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
                'sanitize' => 'url',
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
                'options' => [ '' => __('Use Global Option', 'vapfem') ] + leanpl_get_speed_registry(),
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
            '_audio_title' => [
                'type' => 'text',
                'label' => __('Track Title', 'vapfem'),
                'desc' => __('Shown next to the poster image on audio players. Leave blank to use the post title. Only applies when a poster/thumbnail is set above.', 'vapfem'),
                'placeholder' => __('Defaults to the post title', 'vapfem'),
                'group' => 'video_source',
            ],
            '_audio_title_enabled' => [
                'type' => 'select',
                'label' => __('Show Track Title', 'vapfem'),
                'desc' => __('Hide the title text next to the poster image, even when one is set (via the field above or the post title).', 'vapfem'),
                'options' => [
                    '1' => __('Yes', 'vapfem'),
                    '0' => __('No', 'vapfem'),
                ],
                'default' => '1',
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
                'label' => __('Allow Fullscreen', 'vapfem'),
                'desc' => __('Let viewers expand the video to fullscreen.', 'vapfem'),
                'tooltip' => __('<strong>What it does</strong><br>Lets viewers open the video in fullscreen.<br><br><strong>On:</strong> the fullscreen button, double-click on the video, and the F key all work.<br><strong>Off:</strong> all three are disabled, fullscreen is fully turned off.<br><br><strong>Note:</strong> the Player Controls list only sets whether the button shows in the bar. To fully disable fullscreen, turn this off.', 'vapfem'),
                'tooltip_width' => 'wide',
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
                'desc' => __('Sets the player shape so the page does not jump while the video loads.', 'vapfem'),
                'tooltip' => __('<strong>What it does</strong><br>Reserves the player shape up front so the page does not jump while the video loads.<br><br><strong>Leave empty</strong> for automatic (most widescreen videos are 16:9).<br><br><strong>Enter <code>width:height</code></strong> to force a shape, for example <code>16:9</code> (widescreen), <code>4:3</code> (older TV), <code>1:1</code> (square), or <code>9:16</code> (vertical / phone).', 'vapfem'),
                'tooltip_width' => 'wide',
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
}
