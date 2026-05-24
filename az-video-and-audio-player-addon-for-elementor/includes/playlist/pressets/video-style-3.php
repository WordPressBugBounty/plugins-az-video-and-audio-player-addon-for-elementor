<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
    'id'                   => 'video-style-3',
    'label'                => 'Style 3 — Bottom Grid, 4 Columns',

    // Layout
    'position'             => 'bottom',
    'item_template'        => 'grid',
    'grid_columns'         => '4',
    'panel_width'          => '360px',
    'max_width'            => '100%',
    'playlist_height_mode' => 'fixed',
    'panel_max_height'     => '400px',

    // Appearance
    'skin'                 => 'default',
    'accent_color'         => '',
    'bg_style'             => '',
    'gradient_css'         => '',
    'thumb_ratio'          => '16/9',

    // Header
    'show_header'          => '1',
    'header_text'          => 'Playlist',
    'show_count'           => '1',
    'count_label'          => '',

    // List items
    'show_thumbnails'      => '1',
    'show_numbers'         => '',
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
