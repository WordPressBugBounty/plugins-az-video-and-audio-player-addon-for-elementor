<?php
/**
 * Right-column config for the player edit screen (wiring phase).
 *
 * Two arrays:
 *
 * - 'sections' — accordion chrome only (label/icon/open, plus the
 *   layout-branding card's 'layouts' picker). Rows are NOT listed here
 *   anymore: each card's markup lives in its own
 *   edit-section-{key}.php partial, one partial per card.
 * - 'fields' — flat registry keyed by meta key, carrying only what markup
 *   cannot express: type, options, numeric bounds, and the pro lock. Labels
 *   and layout live in the card partials. Consumed by
 *   includes/admin-new/fields.php -> leanpl_admin_new_registry(), which
 *   passes every entry through the leanpl/metabox/field_config filter (the
 *   pro build strips pro/disabled via Pro_Settings_Unlocker).
 *
 * Meta keys are the same ones Metaboxes::get_field_definitions() writes, so
 * a player stays consistent across both screens. Defaults come from
 * includes/player-defaults.php and are never restated here. Inherit is
 * stored as '' (Config_Merger::is_value_set() reads it as "fall through").
 *
 * The `pro` key is always an array (never a bool) so getProBadge() and
 * $field['pro']['onclick'] behave; the separate `disabled` bool is what
 * actually greys the control. 'badge_position' => 'gem' marks the two gem
 * rows so the card partial renders a gem icon instead of a PRO pill.
 *
 * @return array{sections:array<string,array>, fields:array<string,array>}
 */
if (!defined('ABSPATH')) {
  exit;
}

$yes_no_inherit = [ '' => __('Inherit', 'vapfem'), '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ];

// Layout tiles read straight from leanpl_get_player_layouts() (the same SSOT
// the classic metabox's image-select field uses) instead of restating
// key/label/image a second time - see CLAUDE.md "Vocabulary: skin vs layout
// vs preset". Selected tile comes from the post's stored _player_layout,
// with an explicit first "Inherit" tile (same shared edit-layout-grid.php
// 'inherit' variant the playlist screen's grid uses - see
// playlist-edit-sections.php) for a brand-new player or an unset/invalid
// value, rather than silently pre-selecting Classic for a choice the user
// never actually made. $post_id isn't set when this file is included from
// leanpl_admin_new_registry() (a plain function, no post context) - that
// call only wants $config['fields'], so it's fine to fall through to 0 here.
$edit_layout_post_id      = isset($post_id) ? (int) $post_id : 0;
$edit_layout_stored_value = ($edit_layout_post_id > 0) ? get_post_meta($edit_layout_post_id, '_player_layout', true) : '';
$edit_layout_selected_key = array_key_exists($edit_layout_stored_value, leanpl_get_player_layouts()) ? $edit_layout_stored_value : '';

// Same "empty/invalid falls back to classic" rule as
// class-player-renderer.php's resolve_player_layout() - names whichever
// layout Settings -> Player Layout actually resolves to right now, not a
// static string, so it stays honest if the site owner changes it later.
$edit_layout_global_key = leanpl_get_option( 'player_layout', '' );
$edit_layout_global_key = array_key_exists( $edit_layout_global_key, leanpl_get_player_layouts() )
  ? $edit_layout_global_key
  : 'classic';
$edit_layout_inherit_label = sprintf(
  /* translators: %s: label of the layout currently set in Settings -> Player Layout. */
  __( 'Inherit (%s)', 'vapfem' ),
  leanpl_get_player_layouts()[ $edit_layout_global_key ]['label']
);

$edit_layout_tiles = [
  [
    'key'      => '',
    'label'    => $edit_layout_inherit_label,
    'variant'  => 'inherit',
    'selected' => ('' === $edit_layout_selected_key),
  ],
];
foreach (leanpl_get_player_layouts() as $edit_layout_key => $edit_layout) {
  $edit_layout_tiles[] = [
    'key'      => $edit_layout_key,
    'label'    => $edit_layout['label'],
    'image'    => $edit_layout['image'],
    'selected' => ($edit_layout_selected_key === $edit_layout_key),
  ];
}
$edit_layout_tiles[] = ['key' => 'custom', 'label' => __('Custom Preset', 'vapfem'), 'variant' => 'add'];

// Whole grid is pro (Lock A18) - free users get whatever Settings -> Player
// Layout resolves to site-wide, same split as Accent Color (A17). Filter
// applied by hand rather than via leanpl_admin_new_is_locked(): that
// helper calls leanpl_admin_new_registry(), which re-includes this very
// file - and _player_layout deliberately isn't a leanpl_admin_new_registry()
// key anyway (see the ajax-actions.php save comment), so there's no
// existing entry to look up even if recursion weren't a problem.
// 'player_layout' is already in Pro_Settings_Unlocker's metabox allowlist
// (it predates this lock, added when A11's Custom Preset apply-gate needed
// it), so pro edition strips 'pro' here exactly like every other lock.
$edit_layout_field_filtered = apply_filters(
  'leanpl/metabox/field_config',
  [ 'field_name' => '_player_layout', 'pro' => [ 'onclick' => 'openUpgradeModal' ] ],
  '_player_layout',
  $edit_layout_post_id
);
$edit_layout_locked = ! empty( $edit_layout_field_filtered['pro'] );

return [
  'sections' => [
    // Only rendered for a player that already has a saved source -
    // html-player-edit-new-page.php unsets this key entirely for a
    // brand-new/sourceless player, which still gets the source picker
    // instead. See edit-section-source.php for why editing through it can
    // never change _player_type.
    'source'          => [ 'label' => __('Source', 'vapfem'),             'icon' => 'link',                'open' => false ],
    'general'         => [ 'label' => __('General', 'vapfem'),           'icon' => 'play-outline',       'open' => true ],
    'layout-branding' => [
      'label'         => __('Layout & Branding', 'vapfem'),
      'icon'          => 'palette',
      'open'          => false,
      'layouts'       => $edit_layout_tiles,
      'layout_locked' => $edit_layout_locked,
    ],
    'behavior'        => [ 'label' => __('Behavior', 'vapfem'),          'icon' => 'sliders-horizontal', 'open' => false ],
    'advanced'        => [ 'label' => __('Advanced', 'vapfem'),          'icon' => 'cog',                'open' => false ],
    'timestamp'       => [ 'label' => __('Timestamp Link Shortcode', 'vapfem'), 'icon' => 'braces',      'open' => false ],
  ],

  'fields' => [
    '_poster'      => [ 'type' => 'media' ],
    '_audio_title' => [ 'type' => 'text' ],
    '_ratio'       => [ 'type' => 'select', 'options' => leanpl_get_ratio_options() ],
    '_portrait_max_height' => [ 'type' => 'text' ],

    '_autoplay'           => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_muted'              => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_loop'               => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_click_to_play'      => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_reset_on_end'       => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_fullscreen_enabled' => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_tooltips_controls'  => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_hide_controls'      => [
      'type'     => 'select',
      'options'  => $yes_no_inherit,
      'disabled' => true,
      'pro'      => [ 'onclick' => 'openUpgradeModal' ],
    ],

    '_storage_enabled' => [ 'type' => 'switch', 'options' => [ '1' => __('Yes', 'vapfem'), '0' => __('No', 'vapfem') ], 'default' => '1' ],
    '_volume'          => [ 'type' => 'number', 'min' => 0, 'max' => 100, 'step' => 1 ],
    '_seek_time'       => [
      'type'     => 'number',
      'min'      => 1,
      'max'      => 60,
      'step'     => 1,
      'disabled' => true,
      'pro'      => [ 'onclick' => 'openUpgradeModal', 'badge_position' => 'gem' ],
    ],
    '_speed_selected'  => [ 'type' => 'select', 'options' => [ '' => __('Inherit', 'vapfem') ] + leanpl_get_speed_registry() ],
    '_invert_time'     => [
      'type'     => 'select',
      'options'  => [ '' => __('Inherit', 'vapfem'), '1' => __('Remaining', 'vapfem'), '0' => __('Elapsed', 'vapfem') ],
      'disabled' => true,
      'pro'      => [ 'onclick' => 'openUpgradeModal', 'badge_position' => 'gem' ],
    ],
    '_tooltips_seek'   => [ 'type' => 'select', 'options' => $yes_no_inherit ],
    '_preload'         => [ 'type' => 'select', 'options' => [ 'metadata' => __('Metadata (Recommended)', 'vapfem'), 'none' => __('None', 'vapfem'), 'auto' => __('Auto', 'vapfem') ] ],

    '_primary_color' => [
      'type'    => 'color',
      'default' => '',
      'pro'     => [ 'onclick' => 'openUpgradeModal' ],
    ],
  ],
];
