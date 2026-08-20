<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Per-playlist post meta field schema.
 *
 * Each entry is [ 'sanitize' => callback, 'checkbox' => bool ].
 * Meta key is always derived as '_playlist_' . $field.
 * Used by the save handler, merge layer, and UI renderer.
 */
function leanpl_sanitize_hex_color_or_empty( $value ) {
    if ( $value === '' || $value === null ) {
        return '';
    }
    return sanitize_hex_color( $value ) ?? '';
}

function leanpl_get_playlist_meta_field_schema() {
    return [
        // Appearance
        'skin'                  => [ 'sanitize' => 'sanitize_key' ],
        'thumb_ratio'           => [ 'sanitize' => 'sanitize_text_field' ],
        'bg_style'              => [ 'sanitize' => 'sanitize_key' ],
        'gradient_css'          => [ 'sanitize' => 'sanitize_textarea_field' ],
        'accent_color'          => [ 'sanitize' => 'leanpl_sanitize_hex_color_or_empty', 'nullable' => true ],

        // Layout
        'position'              => [ 'sanitize' => 'sanitize_key' ],
        'item_template'         => [ 'sanitize' => 'sanitize_key' ],
        'grid_columns'          => [ 'sanitize' => 'absint' ],
        'panel_width'           => [ 'sanitize' => 'sanitize_text_field', 'nullable' => true ],
        'max_width'             => [ 'sanitize' => 'sanitize_text_field', 'nullable' => true ],
        'playlist_height_mode'  => [ 'sanitize' => 'sanitize_key' ],
        'panel_max_height'      => [ 'sanitize' => 'sanitize_text_field', 'nullable' => true ],

        // Header
        'show_header'           => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],
        'header_text'           => [ 'sanitize' => 'sanitize_text_field', 'nullable' => true ],
        'show_count'            => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],
        'count_label'           => [ 'sanitize' => 'sanitize_text_field', 'nullable' => true ],

        // List items
        'show_thumbnails'       => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],
        'show_numbers'          => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],
        'play_icon'             => [ 'sanitize' => 'sanitize_key' ],
        'show_duration_badge'   => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],
        'show_duration_in_list' => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],
        'show_meta'             => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],

        // Player
        'player_layout'         => [ 'sanitize' => 'sanitize_key' ],

        // Audio
        'now_playing_style'     => [ 'sanitize' => 'sanitize_key' ],

        // Behavior
        'autoplay_next'         => [ 'sanitize' => 'sanitize_key', 'checkbox' => true ],
        'start_item'            => [ 'sanitize' => 'absint' ],
    ];
}
