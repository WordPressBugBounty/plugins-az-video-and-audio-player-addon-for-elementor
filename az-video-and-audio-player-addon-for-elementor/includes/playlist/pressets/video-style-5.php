<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
    'id'                   => 'video-style-5',
    'label'                => 'Style 5 — Course Layout',

    // Layout
    'position'             => 'left',
    'item_template'        => 'list',
    'panel_width'          => '320px',
    'max_width'            => '100%',
    'playlist_height_mode' => 'sync',
    'panel_max_height'     => '360px',

    // Appearance
    'skin'                 => 'default',
    'accent_color'         => '#2563eb',
    'bg_style'             => '',
    'gradient_css'         => '',
    'thumb_ratio'          => '16/9',

    // Header
    'show_header'          => '1',
    'header_text'          => 'Course Content',
    'show_count'           => '1',
    'count_label'          => 'Lessons',

    // List items
    'show_thumbnails'      => '',
    'show_numbers'         => '1',
    'show_meta'            => '1',
    'show_duration_badge'  => '',
    'show_duration_in_list'=> '1',
    'play_icon'            => 'active_only',

    // Audio
    'now_playing_style'    => 'compact',

    // Behavior
    'autoplay_next'        => '',
    'start_item'           => '1',
];
