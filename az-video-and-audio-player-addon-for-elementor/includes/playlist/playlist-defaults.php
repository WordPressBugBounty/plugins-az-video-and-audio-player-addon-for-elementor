<?php
/**
 * Playlist default options. Single source of truth. Used by Config, presets, custom config, Lex.
 *
 * @package LeanPL\Playlist
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return [
    // General
    'enabled'              => false,

    // Layout
    'position'             => 'right',   // 'right' | 'left' | 'bottom' | 'top'
    'item_template'        => 'list',    // 'list' | 'grid'  @when position=bottom|top
    'grid_columns'         => 4,         // 2–6  @when position=bottom|top+item_template=grid
    'panel_width'          => '360px',
    'max_width'            => '100%',
    'playlist_height_mode' => 'sync',    // 'sync' | 'fixed'
    'panel_max_height'     => '360px',   // @when playlist_height_mode=fixed

    // Appearance
    'skin'          => 'default',        // 'default' | 'dark'
    'bg_style'      => '',               // '' | 'gradient'
    'gradient_css'  => '',               // @when bg_style=gradient

    // Header
    'show_header' => true,
    'header_text'  => 'Playlist',        // @when show_header
    'show_count'   => true,
    'count_label'  => 'Items',           // @when show_count

    // List items
    'show_numbers'           => true,
    'show_thumbnails'        => false,
    'play_icon'              => 'always',  // 'always' | 'active_only' | 'hidden'
    'show_duration_badge'    => false,   // @when show_thumbnails
    'show_duration_in_list'  => true,    // renamed from show_duration_in_row
    'show_meta'              => true,
    'thumb_ratio'            => '16/9',  // '16/9' | '1/1'
    'auto_thumbnail'         => false,   // @pro auto-derive from YouTube/Vimeo when no custom poster

    // Player
    'player_layout'     => '',           // '' = inherit global Player Layout, else a leanpl_get_player_layouts() key
    'now_playing_style' => 'compact',    // 'compact' | 'large'  (audio only)

    // Behavior
    'autoplay_next' => true,
    'start_item'    => 1,

    // Colors
    'accent_color'  => '',
];
