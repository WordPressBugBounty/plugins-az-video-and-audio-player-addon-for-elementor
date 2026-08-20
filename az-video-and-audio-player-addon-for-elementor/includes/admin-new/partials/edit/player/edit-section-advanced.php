<?php
/**
 * Advanced card: Storage switch, two numeric rows, four selects. The two
 * gem-badged rows (_seek_time, _invert_time) are pro-locked and render a gem
 * icon instead of a PRO pill.
 *
 * @var int $post_id Current player ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_storage_value   = ($post_id > 0) ? get_post_meta($post_id, '_storage_enabled', true) : '';
if ($edit_storage_value === '') {
  $edit_storage_value = '1';
}
$edit_storage_checked = ('0' !== $edit_storage_value);

$edit_volume_value    = ($post_id > 0) ? get_post_meta($post_id, '_volume', true) : '';
$edit_seek_time_value = ($post_id > 0) ? get_post_meta($post_id, '_seek_time', true) : '';
?>
<?php
$switch_label      = __('Storage', 'vapfem');
$switch_name       = '_storage_enabled';
$switch_value      = $edit_storage_value;
$switch_show_if    = null;
$switch_show_if_on = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Initial Volume', 'vapfem'); ?>
    </div>
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('(%)', 'vapfem'); ?>
    </div>
  </div>
  <input type="number" name="_volume" value="<?php echo esc_attr($edit_volume_value); ?>" min="0" max="100" step="1"
    class="lpl-text-[13px]/[normal] lpl-text-ink lpl-font-semibold lpl-w-[100px] lpl-h-[36px]" />
</div>

<?php $seek_locked = leanpl_admin_new_is_locked('_seek_time'); ?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $seek_locked ? ' lpl-cursor-pointer' : ''; ?>"<?php echo $seek_locked ? ' onclick="openUpgradeModal()"' : ''; ?>>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Skip Forward/Back Amount', 'vapfem'); ?>
    </div>
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('(Sec.)', 'vapfem'); ?>
    </div>
    <?php if ($seek_locked) : ?>
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?>
    <?php endif; ?>
  </div>
  <input type="number" <?php if (!$seek_locked) : ?>name="_seek_time"<?php endif; ?> value="<?php echo esc_attr($edit_seek_time_value); ?>" min="1" max="60" step="1"<?php if ($seek_locked) : ?> disabled<?php endif; ?>
    class="lpl-text-[13px]/[normal] lpl-text-ink lpl-font-semibold lpl-w-[100px] lpl-h-[36px]" />
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Starting Playback Speed', 'vapfem'); ?>
    </div>
  </div>
  <select name="_speed_selected" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_speed_selected', $post_id); ?>
  </select>
</div>

<?php $invert_locked = leanpl_admin_new_is_locked('_invert_time'); ?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $invert_locked ? ' lpl-cursor-pointer' : ''; ?>"<?php echo $invert_locked ? ' onclick="openUpgradeModal()"' : ''; ?>>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Time Display Format', 'vapfem'); ?>
    </div>
    <?php if ($invert_locked) : ?>
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?>
    <?php endif; ?>
  </div>
  <select <?php if (!$invert_locked) : ?>name="_invert_time"<?php endif; ?>
    <?php if ($invert_locked) : ?>disabled<?php endif; ?>
    class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_invert_time', $post_id); ?>
  </select>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Seek Tooltips', 'vapfem'); ?>
    </div>
  </div>
  <select name="_tooltips_seek" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_tooltips_seek', $post_id); ?>
  </select>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('HTML5 Media Preload', 'vapfem'); ?>
    </div>
  </div>
  <select name="_preload" class="lpl-h-[36px]">
    <?php leanpl_admin_new_options('_preload', $post_id); ?>
  </select>
</div>
