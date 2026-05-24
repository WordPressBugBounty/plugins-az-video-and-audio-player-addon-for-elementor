<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lpl-card">
    <div class="lpl-heading-2 lpl-admin__main-title"><?php echo esc_html__('All Shortcode Options', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle"><?php echo esc_html__('Complete reference for [lean_video], [lean_audio], [lean_playlist]', 'vapfem'); ?></p>

    <div class="lpl-heading-3 lpl-admin__section-title"><?php echo esc_html__('Common', 'vapfem'); ?></div>
    <table class="lpl-options-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Option', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Values', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Default', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Description', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>url</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td><?php echo esc_html__('Valid URL', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Required', 'vapfem'); ?></td>
                <td><?php echo esc_html__('URL of the media file, YouTube video, or Vimeo video.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>autoplay</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Auto-start playback when the page loads.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>loop</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Loop playback when media ends.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>preload</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>auto, metadata, none</td>
                <td>metadata</td>
                <td><?php echo esc_html__('How much media to preload before the visitor clicks play.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>muted</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Start with audio muted.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>volume</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>0.0 - 1.0</td>
                <td>1.0</td>
                <td><?php echo esc_html__('Initial volume level, where 0 is muted and 1 is full volume.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>controls</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>play-large, play, progress, current-time, mute, volume, captions, settings, pip, airplay, fullscreen</td>
                <td><?php echo esc_html__('All controls', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Which control buttons to show.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>seek_time</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td><?php echo esc_html__('Number of seconds', 'vapfem'); ?></td>
                <td>10</td>
                <td><?php echo esc_html__('Time to skip when using fast forward or rewind.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>invert_time</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>true</td>
                <td><?php echo esc_html__('Show remaining time instead of elapsed time.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>keyboard_focused</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>true</td>
                <td><?php echo esc_html__('Enable keyboard shortcuts when the player is focused.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>keyboard_global</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Enable keyboard shortcuts globally on the page; use only with one player per page.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>tooltips_seek</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>true</td>
                <td><?php echo esc_html__('Show a time tooltip when hovering over the progress bar.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>speed_selected</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 4</td>
                <td>1</td>
                <td><?php echo esc_html__('Default playback speed.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>debug_mode</code> <span class="lpl-option-scope lpl-option-scope--both"><?php echo esc_html__('Both', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Enable debug mode for troubleshooting.', 'vapfem'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="lpl-heading-3 lpl-admin__section-title"><?php echo esc_html__('Video Only', 'vapfem'); ?></div>
    <table class="lpl-options-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Option', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Values', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Default', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Description', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>poster</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td><?php echo esc_html__('Valid image URL', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Empty', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Thumbnail image shown before video plays.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>click_to_play</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>true</td>
                <td><?php echo esc_html__('Click anywhere on the video to play or pause.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>hide_controls</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Hide controls after inactivity.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>reset_on_end</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Reset video to the start when playback ends.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>fullscreen_enabled</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>true</td>
                <td><?php echo esc_html__('Enable the fullscreen control.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>tooltips_controls</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td>yes, no, true, false</td>
                <td>false</td>
                <td><?php echo esc_html__('Show tooltips on control buttons.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>sources</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td><?php echo esc_html__('url1|quality1,url2|quality2', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Empty', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Multiple quality sources for HTML5 video.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>quality_default</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td>240, 360, 480, 576, 720, 1080, 1440, 2160</td>
                <td>576</td>
                <td><?php echo esc_html__('Default quality when the video plays.', 'vapfem'); ?></td>
            </tr>
            <tr>
                <td><code>ratio</code> <span class="lpl-option-scope lpl-option-scope--video"><?php echo esc_html__('Video', 'vapfem'); ?></span></td>
                <td>16:9, 4:3, 21:9, <?php echo esc_html__('etc.', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Auto', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Force an aspect ratio for videos, using the format width:height.', 'vapfem'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="lpl-heading-3 lpl-admin__section-title"><?php echo esc_html__('Audio Only', 'vapfem'); ?></div>
    <table class="lpl-options-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Option', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Values', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Default', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Description', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>controls</code> <span class="lpl-option-scope lpl-option-scope--audio"><?php echo esc_html__('Audio', 'vapfem'); ?></span></td>
                <td>play-large, play, progress, current-time, mute, volume, settings, pip, airplay, fullscreen</td>
                <td><?php echo esc_html__('All controls', 'vapfem'); ?></td>
                <td><?php echo esc_html__('Which control buttons to show for audio players.', 'vapfem'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="lpl-info-block lpl-info-block--note">
        <div class="lpl-heading-3"><?php echo esc_html__('Note', 'vapfem'); ?></div>
        <p><strong><?php echo esc_html__('Autoplay Browser Policy:', 'vapfem'); ?></strong></p>
        <p><?php echo esc_html__('Modern browsers block autoplay with sound enabled to improve user experience and reduce unwanted audio. If you set', 'vapfem'); ?> <code>autoplay="yes"</code> <?php echo esc_html__('without', 'vapfem'); ?> <code>muted="yes"</code>, <?php echo esc_html__('the video/audio may not start automatically. To ensure autoplay works:', 'vapfem'); ?></p>
        <ul>
            <li><?php echo esc_html__('Use', 'vapfem'); ?> <code>muted="yes"</code> <?php echo esc_html__('for autoplay to work reliably', 'vapfem'); ?></li>
            <li><?php echo esc_html__('Or rely on user interaction (click to play) for audio-enabled playback', 'vapfem'); ?></li>
            <li><?php echo esc_html__('This is a browser security feature, not a plugin limitation', 'vapfem'); ?></li>
        </ul>
    </div>
</div>
