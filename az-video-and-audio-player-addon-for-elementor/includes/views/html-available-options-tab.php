<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- All Options Tab -->
<div id="all-options" class="vapfem-tab-content">
    <div class="vapfem-card">
        <div class="vapfem-heading-2 vapfem-admin__main-title"><?php echo esc_html__('All Shortcode Options', 'vapfem'); ?></div>
        <p class="vapfem-admin__subtitle"><?php echo esc_html__('Complete reference of all available shortcode options for both video and audio player.', 'vapfem'); ?></p>

        <table class="vapfem-shortcode-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Option Name', 'vapfem'); ?></th>
                    <th><?php echo esc_html__('Video', 'vapfem'); ?></th>
                    <th><?php echo esc_html__('Audio', 'vapfem'); ?></th>
                    <th><?php echo esc_html__('Possible Values', 'vapfem'); ?></th>
                    <th><?php echo esc_html__('Default', 'vapfem'); ?></th>
                    <th><?php echo esc_html__('Description', 'vapfem'); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Common Options for Both Video and Audio -->
                <tr class="vapfem-options-table__section-header">
                    <td colspan="6"><strong><?php echo esc_html__('📋 Common Options (Both Video & Audio)', 'vapfem'); ?></strong></td>
                </tr>
                <tr>
                    <td><code>url</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td><?php echo esc_html__('Valid URL', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Required', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('URL of the media file or YouTube/Vimeo URL', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>autoplay</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Auto-start playback when page loads', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>loop</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Loop playback when media ends', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>muted</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Start with audio muted', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>volume</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>0.0 - 1.0</td>
                    <td>1.0</td>
                    <td><?php echo esc_html__('Initial volume level (0 = muted, 1 = full volume)', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>controls</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>play-large, play, progress, current-time, mute, volume, captions, settings, pip, airplay, fullscreen</td>
                    <td><?php echo esc_html__('All controls', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Which control buttons to show', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>seek_time</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td><?php echo esc_html__('Number (seconds)', 'vapfem'); ?></td>
                    <td>10</td>
                    <td><?php echo esc_html__('Time to skip when using fast forward/rewind', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>invert_time</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>true</td>
                    <td><?php echo esc_html__('Show remaining time instead of elapsed time', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>keyboard_focused</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>true</td>
                    <td><?php echo esc_html__('Enable keyboard shortcuts when player is focused', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>keyboard_global</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Enable keyboard shortcuts globally on page', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>tooltips_seek</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>true</td>
                    <td><?php echo esc_html__('Show time tooltip when hovering over progress bar', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>speed_selected</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 4</td>
                    <td>1</td>
                    <td><?php echo esc_html__('Default playback speed', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>debug_mode</code></td>
                    <td>✅</td>
                    <td>✅</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Enable debug mode for troubleshooting', 'vapfem'); ?></td>
                </tr>

                <!-- Video-Only Options -->
                <tr class="vapfem-options-table__section-header">
                    <td colspan="6"><strong><?php echo esc_html__('📺 Video-Only Options', 'vapfem'); ?></strong></td>
                </tr>
                <tr>
                    <td><code>poster_url</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td><?php echo esc_html__('Valid image URL', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Empty', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Thumbnail image shown before video plays', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>click_to_play</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td>yes, no, true, false</td>
                    <td>true</td>
                    <td><?php echo esc_html__('Click anywhere on video to play/pause', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>hide_controls</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Hide all controls after 2 seconds of inactivity', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>reset_on_end</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Reset video to start when playback ends', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>fullscreen_enabled</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td>yes, no, true, false</td>
                    <td>true</td>
                    <td><?php echo esc_html__('Enable fullscreen toggle button', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>tooltips_controls</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td>yes, no, true, false</td>
                    <td>false</td>
                    <td><?php echo esc_html__('Show tooltips on control buttons', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>sources</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td><?php echo esc_html__('url1|quality1,url2|quality2', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Empty', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Multiple quality sources for HTML5 video', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>quality_default</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td>240, 360, 480, 576, 720, 1080, 1440, 2160</td>
                    <td>576</td>
                    <td><?php echo esc_html__('Default quality when the video plays', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>ratio</code></td>
                    <td>✅</td>
                    <td>❌</td>
                    <td>16:9, 4:3, 21:9, <?php echo esc_html__('etc.', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Auto', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Force an aspect ratio for all videos. The format is \'w:h\' - e.g. \'16:9\' or \'4:3\'. If this is not specified then the default for HTML5 and Vimeo is to use the native resolution of the video. As dimensions are not available from YouTube via SDK, 16:9 is forced as a sensible default.', 'vapfem'); ?></td>
                </tr>

                <!-- Audio-Only Options -->
                <tr class="vapfem-options-table__section-header">
                    <td colspan="6"><strong><?php echo esc_html__('🎵 Audio-Only Options', 'vapfem'); ?></strong></td>
                </tr>
                <tr>
                    <td><code>controls</code></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>play-large, play, progress, current-time, mute, volume, settings, pip, airplay, fullscreen</td>
                    <td><?php echo esc_html__('All controls', 'vapfem'); ?></td>
                    <td><?php echo esc_html__('Which control buttons to show (audio-specific)', 'vapfem'); ?></td>
                </tr>
                <tr>
                    <td><code>preload</code></td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>auto, metadata, none</td>
                    <td>metadata</td>
                    <td><?php echo esc_html__('How much audio to preload', 'vapfem'); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="vapfem-info-block vapfem-info-block--note">
            <div class="vapfem-heading-3"><?php echo esc_html__('📝 Note:', 'vapfem'); ?></div>
            <p><strong><?php echo esc_html__('Autoplay Browser Policy:', 'vapfem'); ?></strong></p>
            <p><?php echo esc_html__('Modern browsers block autoplay with sound enabled to improve user experience and reduce unwanted audio. If you set', 'vapfem'); ?> <code>autoplay="yes"</code> <?php echo esc_html__('without', 'vapfem'); ?> <code>muted="yes"</code>, <?php echo esc_html__('the video/audio may not start automatically. To ensure autoplay works:', 'vapfem'); ?></p>
            <ul>
                <li><?php echo esc_html__('Use', 'vapfem'); ?> <code>muted="yes"</code> <?php echo esc_html__('for autoplay to work reliably', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Or rely on user interaction (click to play) for audio-enabled playback', 'vapfem'); ?></li>
                <li><?php echo esc_html__('This is a browser security feature, not a plugin limitation', 'vapfem'); ?></li>
            </ul>
        </div>
    </div>
</div>