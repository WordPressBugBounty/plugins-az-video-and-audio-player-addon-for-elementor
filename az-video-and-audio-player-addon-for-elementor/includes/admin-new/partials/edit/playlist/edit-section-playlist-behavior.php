<?php
/**
 * Playlist Behavior card: autoplay-next and which item the playlist opens
 * on. start_item is whole-field pro (disabled + gem icon), same lock shape
 * as the player screen's _seek_time row - see edit-section-advanced.php.
 *
 * @var int $post_id Current playlist ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<?php
$switch_label      = __('Autoplay Next Item', 'vapfem');
$switch_name       = '_playlist_autoplay_next';
$switch_value      = leanpl_admin_new_playlist_value('_playlist_autoplay_next', $post_id);
$switch_show_if    = null;
$switch_show_if_on = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<?php $edit_start_item_locked = leanpl_admin_new_playlist_is_locked('_playlist_start_item'); ?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $edit_start_item_locked ? ' lpl-cursor-pointer' : ''; ?>"<?php echo $edit_start_item_locked ? ' onclick="openUpgradeModal()"' : ''; ?>>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Start On Item', 'vapfem'); ?>
    </div>
    <?php if ($edit_start_item_locked) : ?>
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?>
    <?php endif; ?>
  </div>
  <input type="number" <?php if (!$edit_start_item_locked) : ?>name="_playlist_start_item"<?php endif; ?>
    value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_start_item', $post_id)); ?>" min="1" step="1"
    <?php if ($edit_start_item_locked) : ?>disabled<?php endif; ?>
    class="lpl-w-[140px] lpl-h-[36px]" />
</div>
