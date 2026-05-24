<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
    'id'                   => 'audio-style-4',
    'label'                => 'Style 4 — Now Playing Card',

    // Layout
    'position'             => 'bottom',
    'item_template'        => 'list',
    'panel_width'          => '360px',
    'max_width'            => '420px',
    'playlist_height_mode' => 'fixed',
    'panel_max_height'     => '400px',

    // Appearance
    'skin'                 => 'default',
    'accent_color'         => '#f97316',
    'bg_style'             => '',
    'gradient_css'         => '',
    'thumb_ratio'          => '1/1',

    // Header
    'show_header'          => '1',
    'header_text'          => 'Playlist',
    'show_count'           => '1',
    'count_label'          => '',

    // List items
    'show_thumbnails'      => '1',
    'show_numbers'         => '1',
    'show_meta'            => '1',
    'show_duration_badge'  => '',
    'show_duration_in_list'=> '1',
    'play_icon'            => 'active_only',

    // Audio
    'now_playing_style'    => 'large',

    // Behavior
    'autoplay_next'        => '1',
    'start_item'           => '1',
];
