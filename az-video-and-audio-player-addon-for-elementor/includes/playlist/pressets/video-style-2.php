<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
    'id'                   => 'video-style-2',
    'label'                => 'Style 2 — Right Panel, Dark',

    // Layout
    'position'             => 'right',
    'item_template'        => 'list',
    'panel_width'          => '360px',
    'max_width'            => '100%',
    'playlist_height_mode' => 'sync',
    'panel_max_height'     => '360px',

    // Appearance
    'skin'                 => 'dark',
    'accent_color'         => '',
    'bg_style'             => '',
    'gradient_css'         => '',
    'thumb_ratio'          => '16/9',

    // Header
    'show_header'          => '1',
    'header_text'          => 'Up Next',
    'show_count'           => '1',
    'count_label'          => '',

    // List items
    'show_thumbnails'      => '1',
    'show_numbers'         => '1',
    'show_meta'            => '1',
    'show_duration_badge'  => '1',
    'show_duration_in_list'=> '',
    'play_icon'            => 'active_only',

    // Audio
    'now_playing_style'    => 'compact',

    // Behavior
    'autoplay_next'        => '',
    'start_item'           => '1',
];
