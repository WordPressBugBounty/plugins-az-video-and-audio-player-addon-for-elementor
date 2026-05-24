<?php
/**
 * Player Config SSOT
 *
 * This file is the canonical source for all player config key names and defaults.
 *
 * Adding a new player option — update these 3 files:
 * 1. This file — add key + default value in the correct section (shared/video/audio)
 * 2. includes/class-player-renderer.php — add key to COMMON_SETTINGS_KEYS,
 *    VIDEO_ONLY_SETTINGS_KEYS, or AUDIO_ONLY_SETTINGS_KEYS constant
 * 3. assets/js/player-utils.js — add snake_case read + Plyr format output
 *    in buildCommonConfig(), buildVideoConfig(), or buildAudioConfig()
 *
 * If the option should be user-configurable site-wide, also add a field to:
 *    includes/libs/lex-settings-new/config/tabs/settings.php
 */

/**
 * Player Default Settings
 * Single Source of Truth for all player default values
 * Used by: Elementor widgets, Shortcodes, and Renderer
 *
 * Structure:
 * - shared: Common settings for both video and audio players
 * - video: Video-specific settings only
 * - audio: Audio-specific settings only
 * 
 * Example demo urls for audio and videos are provided below
 * 
 * Audio:
 * https://download.samplelib.com/mp3/sample-15s.mp3
 * 
 * Youtube Video:
 * https://www.youtube.com/watch?v=bTqVqk7FSmY
 * 
 * Vimeo Video:
 * https://vimeo.com/76979871
 * 
 * 
 * $source array example 
 * array(
 *     array(
 *         'url' => 'https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4',
 *         'size' => '576',
 *     ),
 *     array(
 *         'url' => 'https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-720p.mp4',
 *         'size' => '720',
 *     ),
 *     array(
 *         'url' => 'https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-1080p.mp4',
 *         'size' => '1080',
 *     ),
 * )
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

return [
    // 🔄 SHARED: Common settings for both video and audio
    'shared' => [
        // Playback behavior
        'autoplay' => false,
        'autopause' => false, // Pause other players when this one starts playing
        'muted' => false,
        'loop' => false,
        'preload' => 'metadata', // HTML attribute on <audio>/<video>. Not a Plyr JS config key. 'auto', 'metadata', 'none'
        'volume' => 100, // Stored as 0-100 (UI format), converted to 0-1 by Config_Merger for Plyr
        'invert_time' => true,
        'seek_time' => 10, // The time, in seconds, to seek when a user hits fast forward or rewind.

        // Keyboard & interaction
        'keyboard_focused' => true,
        'keyboard_global' => false,

        // Tooltips & UI
        'tooltips_seek' => true,
        // Note: Tooltips are converted to object format { controls: boolean, seek: boolean } in JavaScript
        // - controls: Display control labels as tooltips on :hover & :focus (e.g., play icon, mute/unmute, pip)
        // - seek: Display a seek tooltip to indicate on click where the media would seek to
        'speed_selected' => '1', // Default playback speed. Options: 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 4
        // Note: Speed is converted to object format { selected: value, options: [...] } in JavaScript
        // YouTube and Vimeo will ignore/hide options outside 0.5-2 range automatically

        // Default controls — derived from leanpl_get_controls_registry() in functions-player.php
        'controls' => array_keys( array_filter( leanpl_get_controls_registry(), fn( $c ) => $c['default'] ) ),

        // Storage
        'storage_enabled' => true, // Allow use of local storage to store user settings

        // Styling
        'primary_color' => '#00b3ff', // Primary accent color for player controls (Plyr's default blue)

        // Debugging
        'debug_mode' => false,
    ],

    // 📺 VIDEO: Video-specific settings only
    'video' => [
        // Video source & type
        'video_type' => '', // Options: youtube, vimeo, html5
        'video_id' => '', // See example links above
        'poster' => '',

        // Video-specific behavior
        'click_to_play' => true,
        'hide_controls' => false,
        'reset_on_end' => false,
        'fullscreen_enabled' => true,
        'tooltips_controls' => false, // Display control labels as tooltips on :hover & :focus (e.g., play icon, mute/unmute, pip)

        // HTML5 video specific
        'sources' => array(), // See $source array example above
        'quality_default' => '576', // options: 4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240

        // Aspect ratio
        'ratio' => ''
        // Video specific controls (intentionally not using)
    ],

    // 🎵 AUDIO: Audio-specific settings only
    'audio' => [
        // Audio source
        'url' => '', // See demo links above

        // Poster / album art (resolved URL, not attachment ID)
        'poster'      => '',
        'audio_title' => '',
        'audio_skin'  => 'default', // 'default' (light card), 'dark', 'glass'

        // Audio-specific controls (intentionally not using)
    ],
];
