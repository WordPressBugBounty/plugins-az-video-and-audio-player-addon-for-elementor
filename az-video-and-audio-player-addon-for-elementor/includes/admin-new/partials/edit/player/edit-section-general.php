<?php
/**
 * General card rows: poster/thumbnail media picker, Track Title (Audio),
 * Aspect Ratio (Video).
 *
 * Expected variables (from html-player-edit-new-page.php includer scope):
 * @var int $post_id Current player ID, 0 for a brand-new one.
 *
 * Poster is read from real post meta here (same two-state markup that used
 * to live in edit-field-media.php); the picker is wired in admin-new.js
 * initPosterPicker() and saves via _poster, the same key the classic
 * metabox writes.
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_poster_id       = ($post_id > 0) ? (int) get_post_meta($post_id, '_poster', true) : 0;
$edit_poster_url      = $edit_poster_id > 0 ? (wp_get_attachment_image_url($edit_poster_id, 'medium') ?: '') : '';
$edit_poster_filename = $edit_poster_url !== '' ? wp_basename(parse_url($edit_poster_url, PHP_URL_PATH)) : '';
$has_poster           = $edit_poster_url !== '';

$edit_audio_title_value = ($post_id > 0) ? get_post_meta($post_id, '_audio_title', true) : '';

$edit_ratio_value               = ($post_id > 0) ? get_post_meta($post_id, '_ratio', true) : '';
$edit_portrait_max_height_value = ($post_id > 0) ? get_post_meta($post_id, '_portrait_max_height', true) : '';
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Poster/Thumbnail', 'vapfem'); ?>
      </div>
    </div>
  </div>

  <div
    data-lpl-poster-empty
    data-lpl-poster-picker
    class="<?php echo $has_poster ? 'lpl-hidden ' : ''; ?>lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-[72px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-center lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]"
  >
    <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('upload'); ?>
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-mute lpl-font-normal lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Select Image', 'vapfem'); ?>
    </div>
  </div>

  <div
    data-lpl-poster-set
    data-lpl-poster-id="<?php echo esc_attr($edit_poster_id); ?>"
    class="<?php echo $has_poster ? '' : 'lpl-hidden '; ?>lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[8px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]"
  >
    <div class="lpl-relative lpl-box-border lpl-w-[52px] lpl-h-[52px] lpl-shrink-0 lpl-rounded-[6px] lpl-overflow-hidden [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px]">
      <img
        src="<?php echo esc_url($edit_poster_url); ?>"
        alt=""
        class="lpl-absolute lpl-top-0 lpl-left-0 lpl-w-full lpl-h-full lpl-object-cover"
        data-lpl-poster-image
      />
    </div>
    <div class="lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-center lpl-items-start">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-w-full lpl-text-ink lpl-font-medium lpl-truncate" data-lpl-poster-filename>
        <?php echo esc_html($edit_poster_filename); ?>
      </div>
      <div class="lpl-box-border lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
        <div data-lpl-poster-picker role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#4F46E5] lpl-font-medium">
          <?php esc_html_e('Change', 'vapfem'); ?>
        </div>
        <div data-lpl-poster-remove role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#DC2626] lpl-font-medium">
          <?php esc_html_e('Remove', 'vapfem'); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Track Title (Audio)', 'vapfem'); ?>
      </div>
    </div>
  </div>
  <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <input type="text" name="_audio_title" value="<?php echo esc_attr($edit_audio_title_value); ?>"
      placeholder="<?php esc_attr_e('Defaults to the post title', 'vapfem'); ?>"
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none placeholder:lpl-text-label-secondary [appearance:none] [-webkit-appearance:none]" />
  </div>
</div>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-between lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Aspect Ratio (Video)', 'vapfem'); ?>
  </div>
  <select name="_ratio" class="lpl-h-[36px] lpl-w-[200px] lpl-shrink-0">
    <?php leanpl_admin_new_options('_ratio', $post_id); ?>
  </select>
</div>

<div
  data-lpl-show-if="_ratio:9:16"
  class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start<?php echo $edit_ratio_value !== '9:16' ? ' lpl-hidden' : ''; ?>"
>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-between lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Max Height', 'vapfem'); ?>
    </div>
    <div class="lpl-box-border lpl-w-[200px] lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
      <input type="text" name="_portrait_max_height" value="<?php echo esc_attr($edit_portrait_max_height_value); ?>"
        placeholder="70vh"
        class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none placeholder:lpl-text-label-secondary [appearance:none] [-webkit-appearance:none]" />
    </div>
  </div>
  <div class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-label-secondary lpl-font-normal lpl-text-left">
    <?php esc_html_e("Controls the video's height on screen. The width resizes itself to match. Accepts values like 70vh or 500px.", 'vapfem'); ?>
  </div>
</div>
