<?php
/**
 * Behavior card: eight Inherit / Yes / No selects. The locked row
 * (_hide_controls) is the one with a PRO pill and no name attribute.
 *
 * Selects are plain WP-admin-default <select> elements (native border,
 * background, focus ring, dropdown arrow) - only size and radius are
 * overridden, via .lpl-admin select in tailwind-admin.src.css.
 *
 * Click to Play/Pause, Allow Fullscreen, and Auto-Hide Controls only
 * apply to a video player, so those three rows carry
 * data-lpl-show-if="_player_type:video" (admin-new-conditional-fields.js)
 * instead of a "(Video)" label suffix. That condition reads the hidden
 * _player_type input below, which requestPreview() (admin-new.js) keeps
 * in sync with whichever source is actually showing - same "hidden input
 * mirrors JS state" pattern edit-layout-grid.php's _player_layout input
 * uses.
 *
 * @var int $post_id Current player ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_behavior_player_type = ($post_id > 0) ? (get_post_meta($post_id, '_player_type', true) ?: 'video') : 'video';
?>
<input type="hidden" name="_player_type" value="<?php echo esc_attr($edit_behavior_player_type); ?>" data-lpl-player-type-input>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Autoplay', 'vapfem'); ?>
    </div>
  </div>
  <select name="_autoplay" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_autoplay', $post_id); ?>
  </select>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Start Muted', 'vapfem'); ?>
    </div>
  </div>
  <select name="_muted" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_muted', $post_id); ?>
  </select>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Loop Playback', 'vapfem'); ?>
    </div>
  </div>
  <select name="_loop" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_loop', $post_id); ?>
  </select>
</div>

<div data-lpl-show-if="_player_type:video" class="<?php echo $edit_behavior_player_type === 'video' ? '' : 'lpl-hidden '; ?>lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Click Video to Play/Pause', 'vapfem'); ?>
    </div>
  </div>
  <select name="_click_to_play" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_click_to_play', $post_id); ?>
  </select>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Reset to Start When Finished', 'vapfem'); ?>
    </div>
  </div>
  <select name="_reset_on_end" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_reset_on_end', $post_id); ?>
  </select>
</div>

<div data-lpl-show-if="_player_type:video" class="<?php echo $edit_behavior_player_type === 'video' ? '' : 'lpl-hidden '; ?>lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Allow Fullscreen', 'vapfem'); ?>
    </div>
  </div>
  <select name="_fullscreen_enabled" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_fullscreen_enabled', $post_id); ?>
  </select>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Control Button Tooltips', 'vapfem'); ?>
    </div>
  </div>
  <select name="_tooltips_controls" class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_tooltips_controls', $post_id); ?>
  </select>
</div>

<?php $locked = leanpl_admin_new_is_locked('_hide_controls'); ?>
<div data-lpl-show-if="_player_type:video" class="<?php echo $edit_behavior_player_type === 'video' ? '' : 'lpl-hidden '; ?>lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $locked ? ' lpl-cursor-pointer' : ''; ?>"<?php echo $locked ? ' onclick="openUpgradeModal()"' : ''; ?>>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Auto-Hide Controls', 'vapfem'); ?>
    </div>
    <?php if ($locked) : ?>
      <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[3px_7px] lpl-justify-center lpl-items-center lpl-bg-[#6C5CE7] lpl-rounded-[4px]">
        <div class="lpl-text-[9px]/[normal] lpl-box-border lpl-text-[#FFFFFF] lpl-font-bold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('PRO', 'vapfem'); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <select <?php if (!$locked) : ?>name="_hide_controls"<?php endif; ?>
    <?php if ($locked) : ?>disabled<?php endif; ?>
    class="lpl-w-[100px] lpl-h-[36px]">
    <?php leanpl_admin_new_options('_hide_controls', $post_id); ?>
  </select>
</div>
