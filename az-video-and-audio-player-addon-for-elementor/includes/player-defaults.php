<?php
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
        'muted' => false,
        'loop' => false,
        'volume' => 1.0, // A number, between 0 and 1, representing the initial volume of the player.
        'invert_time' => true,
        'seek_time' => 10, // The time, in seconds, to seek when a user hits fast forward or rewind.

        // Keyboard & interaction
        'keyboard_focused' => true,
        'keyboard_global' => false,

        // Tooltips & UI
        'tooltips_seek' => true,
        'speed_selected' => '1', // options: 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 4

        // Basic controls (common to both)
        'controls' => ['play', 'progress', 'mute', 'volume', 'settings'],

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
        'tooltips_controls' => false,

        // HTML5 video specific
        'sources' => array(), // See $source array example above
        'quality_default' => '576', // options: 4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240

        // Aspect ratio
        'ratio' => '',

        // Video-specific controls
        'controls' => ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
    ],

    // 🎵 AUDIO: Audio-specific settings only
    'audio' => [
        // Audio source
        'url' => '', // See demo links above

        // Audio-specific behavior
        'preload' => 'metadata', // 'auto', 'metadata', 'none'

        // Audio-specific controls (no video controls like fullscreen, pip, etc.)
        'controls' => ['play', 'progress', 'mute', 'volume', 'settings', 'airplay', 'download'],
    ]
];