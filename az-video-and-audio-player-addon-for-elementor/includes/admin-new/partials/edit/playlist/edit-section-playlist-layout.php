<?php
/**
 * Playlist Layout card: panel position, item template, grid columns, and
 * panel sizing (width/max-width/height mode/max-height).
 *
 * The Player Layout image-select grid (_playlist_player_layout) is NOT
 * rendered here - same split as the player screen's edit-section-layout-
 * branding.php. edit-section-card.php appends it after this partial via
 * $section['layouts'] / edit-layout-grid.php, built in
 * playlist-edit-sections.php with an extra first "Inherit" tile the
 * player screen's grid doesn't have (empty key = use the global Player
 * Layout, see Config::get_player_layout()).
 *
 * grid_columns only does anything when position is bottom/top *and* the
 * item template is grid (see playlist-defaults.php's @when notes). The POC
 * renders it unconditionally rather than hiding it - conditional row
 * visibility is a whole mechanism the player screen doesn't have either,
 * and guessing at it here would be inventing UI the classic metabox
 * doesn't have.
 *
 * panel_max_height only applies when playlist_height_mode is fixed, same
 * caveat as grid_columns above.
 *
 * @var int $post_id Current playlist ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}

// Computed once up front so the 3 conditional rows below can render their
// correct initial visibility server-side (no lpl-hidden-by-default flash -
// admin-new-conditional-fields.js only has to react to later changes).
$edit_position_stored = leanpl_admin_new_playlist_value('_playlist_position', $post_id);
?>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Panel Position', 'vapfem'); ?>
  </div>
  <select name="_playlist_position" class="lpl-w-[140px] lpl-h-[36px]">
    <?php leanpl_admin_new_playlist_options('_playlist_position', $post_id); ?>
  </select>
</div>

<?php
$edit_item_template_field    = leanpl_admin_new_playlist_registry()['_playlist_item_template'] ?? [];
$edit_item_template_options  = $edit_item_template_field['options'] ?? [];
$edit_item_template_pro_opts = $edit_item_template_field['pro_options'] ?? [];
$edit_item_template_stored   = leanpl_admin_new_playlist_value('_playlist_item_template', $post_id);
$edit_position_shows_template = in_array($edit_position_stored, ['bottom', 'top'], true);
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $edit_position_shows_template ? '' : ' lpl-hidden'; ?>" data-lpl-show-if="_playlist_position:bottom,top">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Item Template', 'vapfem'); ?>
  </div>
  <select name="_playlist_item_template" class="lpl-w-[140px] lpl-h-[36px]">
    <?php foreach ($edit_item_template_options as $edit_item_template_value => $edit_item_template_label) :
      $edit_item_template_locked   = in_array($edit_item_template_value, $edit_item_template_pro_opts, true);
      $edit_item_template_selected = ((string) $edit_item_template_value === $edit_item_template_stored);
      $edit_item_template_text     = $edit_item_template_locked ? ($edit_item_template_label . ' (' . __('PRO', 'vapfem') . ')') : $edit_item_template_label;
    ?>
      <option value="<?php echo esc_attr($edit_item_template_value); ?>"
        <?php echo $edit_item_template_selected ? ' selected' : ''; ?>
        <?php echo $edit_item_template_locked ? ' disabled' : ''; ?>>
        <?php echo esc_html($edit_item_template_text); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<?php $edit_shows_grid_columns = $edit_position_shows_template && $edit_item_template_stored === 'grid'; ?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $edit_shows_grid_columns ? '' : ' lpl-hidden'; ?>" data-lpl-show-if="_playlist_position:bottom,top;_playlist_item_template:grid">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Grid Columns', 'vapfem'); ?>
  </div>
  <input type="number" name="_playlist_grid_columns" min="2" max="6" step="1"
    value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_grid_columns', $post_id)); ?>"
    class="lpl-w-[140px] lpl-h-[36px]" />
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Panel Width', 'vapfem'); ?>
  </div>
  <div class="lpl-box-border lpl-w-[140px] lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <input type="text" name="_playlist_panel_width"
      value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_panel_width', $post_id)); ?>"
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none] [-webkit-appearance:none]" />
  </div>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Max Width', 'vapfem'); ?>
  </div>
  <div class="lpl-box-border lpl-w-[140px] lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <input type="text" name="_playlist_max_width"
      value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_max_width', $post_id)); ?>"
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none] [-webkit-appearance:none]" />
  </div>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Panel Height Mode', 'vapfem'); ?>
  </div>
  <select name="_playlist_playlist_height_mode" class="lpl-w-[140px] lpl-h-[36px]">
    <?php leanpl_admin_new_playlist_options('_playlist_playlist_height_mode', $post_id); ?>
  </select>
</div>

<?php $edit_shows_panel_max_height = leanpl_admin_new_playlist_value('_playlist_playlist_height_mode', $post_id) === 'fixed'; ?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $edit_shows_panel_max_height ? '' : ' lpl-hidden'; ?>" data-lpl-show-if="_playlist_playlist_height_mode:fixed">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Panel Max Height', 'vapfem'); ?>
  </div>
  <div class="lpl-box-border lpl-w-[140px] lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <input type="text" name="_playlist_panel_max_height"
      value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_panel_max_height', $post_id)); ?>"
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none] [-webkit-appearance:none]" />
  </div>
</div>
