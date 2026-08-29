<?php
/**
 * Playlist Header card: whether to show the header row, its title text,
 * and the same pair for the item-count line. No locks - see
 * PLAYLIST-FREE-VS-PRO.md.
 *
 * show_header/show_count are switches, not selects - the classic metabox
 * renders them as plain checkboxes (no Inherit state), so this matches that
 * boolean-only behavior via the same on/off switch as the player screen's
 * Storage row (edit-section-advanced.php): a hidden input carries '1'/'0'
 * so both states always submit, data-lpl-switch/data-lpl-switch-target
 * drive the toggle in admin-new.js.
 *
 * @var int $post_id Current playlist ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_show_header_value = leanpl_admin_new_playlist_value('_playlist_show_header', $post_id);
$edit_show_header_checked = ('0' !== $edit_show_header_value);

$edit_show_count_value = leanpl_admin_new_playlist_value('_playlist_show_count', $post_id);
$edit_show_count_checked = ('0' !== $edit_show_count_value);
?>
<?php
$switch_label      = __('Show Header', 'vapfem');
$switch_name       = '_playlist_show_header';
$switch_value      = $edit_show_header_value;
$switch_show_if    = null;
$switch_show_if_on = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start<?php echo $edit_show_header_checked ? '' : ' lpl-hidden'; ?>" data-lpl-show-if="_playlist_show_header:1">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Header Text', 'vapfem'); ?>
  </div>
  <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <input type="text" name="_playlist_header_text"
      value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_header_text', $post_id)); ?>"
      placeholder="<?php esc_attr_e('e.g. Playlist, Up Next, Episodes...', 'vapfem'); ?>"
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none placeholder:lpl-text-label-secondary [appearance:none] [-webkit-appearance:none]" />
  </div>
</div>

<?php
$switch_label      = __('Show Item Count', 'vapfem');
$switch_name       = '_playlist_show_count';
$switch_value      = $edit_show_count_value;
$switch_show_if    = null;
$switch_show_if_on = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start<?php echo $edit_show_count_checked ? '' : ' lpl-hidden'; ?>" data-lpl-show-if="_playlist_show_count:1">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Count Label', 'vapfem'); ?>
  </div>
  <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <input type="text" name="_playlist_count_label"
      value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_count_label', $post_id)); ?>"
      placeholder="<?php esc_attr_e('e.g. Videos, Tracks, Episodes...', 'vapfem'); ?>"
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none placeholder:lpl-text-label-secondary [appearance:none] [-webkit-appearance:none]" />
  </div>
</div>
