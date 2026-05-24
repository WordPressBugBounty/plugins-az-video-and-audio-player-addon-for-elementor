<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
    'id'                   => 'video-style-4',
    'label'                => 'Style 4 — Minimal List',

    // Layout
    'position'             => 'right',
    'item_template'        => 'list',
    'panel_width'          => '300px',
    'max_width'            => '100%',
    'playlist_height_mode' => 'sync',
    'panel_max_height'     => '360px',

    // Appearance
    'skin'                 => 'default',
    'accent_color'         => '',
    'bg_style'             => '',
    'gradient_css'         => '',
    'thumb_ratio'          => '16/9',

    // Header
    'show_header'          => '',
    'header_text'          => '',
    'show_count'           => '',
    'count_label'          => '',

    // List items
    'show_thumbnails'      => '',
    'show_numbers'         => '1',
    'show_meta'            => '',
    'show_duration_badge'  => '',
    'show_duration_in_list'=> '1',
    'play_icon'            => 'active_only',

    // Audio
    'now_playing_style'    => 'compact',

    // Behavior
    'autoplay_next'        => '',
    'start_item'           => '1',
];
