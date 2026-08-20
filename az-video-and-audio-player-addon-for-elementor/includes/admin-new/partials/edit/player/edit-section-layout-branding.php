<?php
/**
 * 
 *  card: the Player Accent Color picker row. The layout
 * picker grid below it is not part of this partial - edit-section-card.php
 * renders $section['layouts'] through edit-layout-grid.php, so the two
 * concerns stay separate.
 *
 * Wired to _primary_color (same meta key as the classic metabox's field,
 * class-metaboxes.php:382). Empty value = inherit the global accent color.
 * Uses the same wp-color-picker widget as lex-settings-new's color field
 * (includes/libs/lex-settings-new/core/partials/fields/color.php) - the
 * 'lex-color-picker' class + data-color-picker="true" are its init hooks,
 * initColorPickers() in admin-new.js does the wpColorPicker() call. The
 * widget's own "Clear" button (shown because defaultColor is passed as
 * false, not the stored value) is what lets the user reset to inherit.
 *
 * @var int $post_id Current player ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_color_locked = leanpl_admin_new_is_locked('_primary_color');
$edit_color_value  = ($post_id > 0) ? get_post_meta($post_id, '_primary_color', true) : '';
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start<?php echo $edit_color_locked ? ' lpl-cursor-pointer' : ''; ?>"<?php echo $edit_color_locked ? ' onclick="openUpgradeModal()"' : ''; ?>>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Player Accent Color', 'vapfem'); ?>
      </div>
      <?php if ($edit_color_locked) : ?>
      <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[3px_7px] lpl-justify-center lpl-items-center lpl-bg-[#6C5CE7] lpl-rounded-[4px]">
        <div class="lpl-text-[9px]/[normal] lpl-box-border lpl-text-[#FFFFFF] lpl-font-bold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('PRO', 'vapfem'); ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <input
    type="text"
    id="lpl-primary-color"
    <?php if (!$edit_color_locked) : ?>name="_primary_color"<?php endif; ?>
    value="<?php echo esc_attr($edit_color_value); ?>"
    class="lex-color-picker"
    data-color-picker="true"
    <?php if ($edit_color_locked) : ?>disabled<?php endif; ?>
  />
</div>
