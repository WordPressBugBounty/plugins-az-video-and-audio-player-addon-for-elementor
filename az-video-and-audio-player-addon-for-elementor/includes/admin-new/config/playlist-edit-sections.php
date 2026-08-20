<?php
/**
 * Right-column config for the playlist edit screen (POC phase).
 *
 * Same two-array shape as player-edit-sections.php:
 *
 * - 'sections' — accordion chrome only (label/icon/open). Rows live in each
 *   card's own edit-section-{key}.php partial.
 * - 'fields' — flat registry keyed by meta key, carrying only what markup
 *   cannot express: type, options, numeric bounds. Consumed by
 *   includes/admin-new/playlist-fields.php.
 *
 * Meta keys are the `_playlist_{field}` keys Playlist_Metaboxes::save()
 * writes and Playlist\Config reads, so a playlist edited here stays
 * consistent with the classic metabox. Field names also double as the
 * override keys Live_Preview_Ajax::build_playlist_overrides_from_post()
 * reads, which is what makes the left-column preview update live.
 *
 * Defaults live in includes/playlist/playlist-defaults.php and are never
 * restated in 'options' below - these fields have no global Settings-page
 * counterpart to inherit from (unlike single-player options), so an unset
 * meta value resolves straight to the hardcoded default, same as the
 * classic metabox's $val fallback in Playlist_Metaboxes::render_display_options_metabox().
 *
 * POC scope: cards land one at a time - see PLAYLIST-FREE-VS-PRO.md for what
 * a complete port has to cover.
 *
 * @return array{sections:array<string,array>, fields:array<string,array>}
 */
if (!defined('ABSPATH')) {
  exit;
}

// Same registry leanpl_get_player_layouts() (includes/functions-player.php)
// feeds the single-player screen's _player_layout picker and the global
// Settings player_layout field. 'options' below (used for save-time
// validation, see class-metabox-save.php's save_select()) stays a plain
// key => label map; the image tiles themselves are built into
// $playlist_layout_tiles below and rendered by edit-layout-grid.php via
// the 'playlist-layout' section's 'layouts' key, same mechanism
// player-edit-sections.php uses - no Custom Preset add-tile though (Lock
// A11 is out of scope here), and with an extra first "Inherit" tile the
// player screen's grid doesn't have, since empty here means "use the
// global Player Layout" rather than just defaulting to Classic.
// Same "empty/invalid falls back to classic" rule as
// Config::get_player_layout() / resolve_player_layout() - the Inherit
// tile's label names whichever layout is actually in effect right now,
// not a static "Global Default" string, so it stays honest if the site
// owner changes Settings -> Player Layout later.
$playlist_global_layout_key = leanpl_get_option( 'player_layout', '' );
$playlist_global_layout_key = array_key_exists( $playlist_global_layout_key, leanpl_get_player_layouts() )
  ? $playlist_global_layout_key
  : 'classic';
$playlist_inherit_label = sprintf(
  /* translators: %s: label of the layout currently set in Settings -> Player Layout. */
  __( 'Inherit (%s)', 'vapfem' ),
  leanpl_get_player_layouts()[ $playlist_global_layout_key ]['label']
);

$playlist_player_layout_options = array_merge(
  [ '' => $playlist_inherit_label ],
  wp_list_pluck( leanpl_get_player_layouts(), 'label' )
);

// $post_id isn't set when this file is included from
// leanpl_admin_new_playlist_registry() (a plain function, no post
// context) - same fallback player-edit-sections.php uses for its own
// $edit_layout_post_id.
$playlist_layout_post_id = isset( $post_id ) ? (int) $post_id : 0;
$playlist_layout_stored  = ( $playlist_layout_post_id > 0 )
  ? get_post_meta( $playlist_layout_post_id, '_playlist_player_layout', true )
  : '';
$playlist_layout_selected = array_key_exists( $playlist_layout_stored, leanpl_get_player_layouts() )
  ? $playlist_layout_stored
  : '';

$playlist_layout_tiles = [
  [
    'key'      => '',
    'label'    => $playlist_inherit_label,
    'variant'  => 'inherit',
    'selected' => ( '' === $playlist_layout_selected ),
  ],
];
foreach ( leanpl_get_player_layouts() as $playlist_layout_key => $playlist_layout_row ) {
  $playlist_layout_tiles[] = [
    'key'      => $playlist_layout_key,
    'label'    => $playlist_layout_row['label'],
    'image'    => $playlist_layout_row['image'],
    'selected' => ( $playlist_layout_selected === $playlist_layout_key ),
  ];
}
// Same inert placeholder tile as player-edit-sections.php's grid - Lock A11
// (assigning a Custom Preset via this picker) isn't wired up in admin-new
// on either screen yet, but the tile itself ships on both for visual
// parity between the two grids.
$playlist_layout_tiles[] = [ 'key' => 'custom', 'label' => __( 'Custom Preset', 'vapfem' ), 'variant' => 'add' ];

// Whole field is pro (Lock 18) - free users can only ever use whatever
// Settings -> Player Layout resolves to. 'pro' lives on the raw field
// config below (shared with the 'fields' registry entry, so
// Metabox_Save::save_playlist() skips it via leanpl_admin_new_playlist_is_locked()
// the same way _playlist_accent_color/_playlist_start_item do) and the
// filter is applied here by hand rather than by calling
// leanpl_admin_new_playlist_is_locked() - that helper calls
// leanpl_admin_new_playlist_registry(), which re-includes this very file,
// and this file may itself be mid-include from that same call (infinite
// recursion on the very first request that touches either).
$playlist_layout_field_raw = [
  'type'    => 'select',
  'options' => $playlist_player_layout_options,
  'pro'     => [ 'onclick' => 'openUpgradeModal' ],
];
$playlist_layout_field_filtered = apply_filters(
  'leanpl/metabox/field_config',
  array_merge( [ 'field_name' => '_playlist_player_layout' ], $playlist_layout_field_raw ),
  '_playlist_player_layout',
  0
);
$playlist_layout_locked = ! empty( $playlist_layout_field_filtered['pro'] );

return [
  'sections' => [
    // First in the accordion, open by default - this is the card users
    // reach for immediately (reorder/edit/remove tracks), ahead of the
    // display-option cards below it.
    'playlist-track-list' => ['label' => __('Playlist Items', 'vapfem'), 'icon' => 'list-video',       'open' => true],
    'playlist-layout'     => [
      'label'             => __('Layout', 'vapfem'),
      'icon'              => 'sliders-horizontal',
      'open'              => false,
      'layouts'           => $playlist_layout_tiles,
      'layout_input_name' => '_playlist_player_layout',
      'layout_locked'     => $playlist_layout_locked,
    ],
    'playlist-appearance' => ['label' => __('Appearance', 'vapfem'), 'icon' => 'palette',             'open' => false],
    'playlist-header'     => ['label' => __('Header', 'vapfem'),     'icon' => 'header',              'open' => false],
    // Renamed from "List Items" now that 'playlist-track-list' owns that
    // title - this card is display-option toggles only (thumbnails,
    // duration badge, numbers, meta, play icon), not the track list itself.
    'playlist-items'      => ['label' => __('Item Display', 'vapfem'), 'icon' => 'items',             'open' => false],
    // Audio-only card - html-playlist-edit-new-page.php drops this section
    // from $edit_sections entirely when the playlist's _playlist_type isn't
    // 'audio', so a video playlist never sees it. Icon is 'audio', not
    // 'source-audio' - same glyph, recolored for the accordion-header
    // context (see Admin_New_Icons::get() docblock on that case).
    'playlist-audio'      => ['label' => __('Audio', 'vapfem'),       'icon' => 'audio',                'open' => false],
    'playlist-behavior'   => ['label' => __('Behavior', 'vapfem'),    'icon' => 'cog',                   'open' => false],
  ],

  'fields' => [
    '_playlist_position' => [
      'type'    => 'select',
      'options' => [
        'right'  => __('Right', 'vapfem'),
        'left'   => __('Left', 'vapfem'),
        'bottom' => __('Bottom', 'vapfem'),
        'top'    => __('Top', 'vapfem'),
      ],
    ],
    // Same partial-lock shape as play_icon/now_playing_style - free field,
    // only the 'grid' option is pro (classic's pro_options: ['grid']).
    // grid_columns below is a separate field and stays free for now - see
    // conversation notes on the SSOT pro-shortcode-keys gap.
    '_playlist_item_template' => [
      'type'        => 'select',
      'options'     => ['list' => __('List', 'vapfem'), 'grid' => __('Grid', 'vapfem')],
      'pro_options' => ['grid'],
    ],
    '_playlist_grid_columns'         => ['type' => 'number', 'min' => 2, 'max' => 6, 'step' => 1],
    '_playlist_panel_width'          => ['type' => 'text'],
    '_playlist_max_width'            => ['type' => 'text'],
    '_playlist_playlist_height_mode' => [
      'type'    => 'select',
      'options' => ['sync' => __('Sync', 'vapfem'), 'fixed' => __('Fixed', 'vapfem')],
    ],
    '_playlist_panel_max_height' => ['type' => 'text'],

    // _playlist_player_layout deliberately is NOT registered here, same
    // reasoning as _player_layout on the single-player screen (see
    // player-edit-sections.php) - it's structural, not a plain select, and
    // can hold a Custom Preset reference ('preset_...', Lock A11) that this
    // registry's generic save_select() would reject (not in 'options') and
    // silently blank back to ''. leanpl_admin_new_save_playlist()
    // (ajax-actions.php) saves and validates it by hand instead, against
    // leanpl_get_player_layouts() + the 'preset_' prefix. $playlist_layout_field_raw
    // above still supplies the pro lock via leanpl_admin_new_playlist_is_locked()
    // (which reads the 'fields' registry) - see that helper's own lookup.

    // Whole field is free - only 'dark' is pro (Lock 17), same partial-lock
    // shape as _playlist_item_template above.
    '_playlist_skin' => [
      'type'        => 'select',
      'options'     => ['default' => __('Light', 'vapfem'), 'dark' => __('Dark', 'vapfem')],
      'pro_options' => ['dark'],
    ],
    '_playlist_accent_color' => [
      'type'    => 'color',
      'default' => '',
      'pro'     => ['onclick' => 'openUpgradeModal'],
    ],
    // Whole field is free - only 'gradient' is pro (Lock 3), same
    // partial-lock shape as _playlist_item_template above.
    '_playlist_bg_style' => [
      'type'        => 'select',
      'options'     => ['' => __('Solid (use skin color)', 'vapfem'), 'gradient' => __('Custom gradient', 'vapfem')],
      'pro_options' => ['gradient'],
    ],
    '_playlist_gradient_css' => [
      'type' => 'textarea',
      'pro'  => ['onclick' => 'openUpgradeModal'],
    ],

    '_playlist_show_header' => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '1' ],
    '_playlist_header_text' => ['type' => 'text'],
    '_playlist_show_count'  => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '1' ],
    '_playlist_count_label' => ['type' => 'text'],

    '_playlist_show_thumbnails' => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '0' ],
    '_playlist_thumb_ratio'     => [
      'type'    => 'select',
      'options' => ['16/9' => __('16:9 (Widescreen)', 'vapfem'), '1/1' => __('1:1 (Square)', 'vapfem')],
    ],
    '_playlist_show_duration_badge'   => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '0' ],
    '_playlist_show_duration_in_list' => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '1' ],
    '_playlist_show_numbers'          => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '1' ],
    '_playlist_show_meta'             => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '1' ],
    // Whole field is free - only the 'hidden' option is pro-locked, same
    // split as classic (pro_options: ['hidden']). No top-level 'pro' key,
    // so leanpl_admin_new_playlist_is_locked() (whole-field check) stays
    // false here; the partial reads 'pro_options' itself to disable just
    // that one <option>.
    '_playlist_play_icon' => [
      'type'        => 'select',
      'options'     => [
        'active_only' => __('Active item only', 'vapfem'),
        'always'      => __('Always', 'vapfem'),
        'hidden'      => __('Hidden', 'vapfem'),
      ],
      'pro_options' => ['hidden'],
    ],

    // Same partial-lock shape as play_icon above - free field, only the
    // 'large' option is pro (classic's pro_options: ['large']).
    '_playlist_now_playing_style' => [
      'type'        => 'select',
      'options'     => ['compact' => __('Compact', 'vapfem'), 'large' => __('Large', 'vapfem')],
      'pro_options' => ['large'],
    ],

    '_playlist_autoplay_next' => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '1' ],
    // Whole-field pro, same as _seek_time on the player screen (disabled +
    // gem icon, no partial pro_options - classic locks the entire row too).
    '_playlist_start_item' => [
      'type' => 'number',
      'min'  => 1,
      'step' => 1,
      'pro'  => ['onclick' => 'openUpgradeModal'],
    ],
  ],
];
