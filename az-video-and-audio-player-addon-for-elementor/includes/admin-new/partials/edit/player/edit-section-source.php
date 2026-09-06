<?php
/**
 * Source card: shows the media currently powering this player and lets the
 * user update it in place - a new URL for a link-based source (YouTube,
 * Vimeo, direct video/audio link, live stream), or a new file via the media
 * library for an uploaded one. Only rendered once html-player-edit-new-page.php
 * has confirmed a saved source exists ($edit_has_saved_source) - a brand-new
 * player still gets the full tabbed picker instead.
 *
 * Deliberately never lets the player switch between video and audio: every
 * field below reuses the exact meta key the source picker itself already
 * writes for THIS player's current type (config/player-edit-source-tabs.php's
 * tabs / initAddMedia() in admin-new.js), so submitting a change here can
 * only replace that one URL/file - _player_type, _video_type,
 * _html5_source_type/_audio_source_type all stay whatever they already were.
 *
 * The link-based fields below need no dedicated JS: they're plain named
 * inputs inside [data-lpl-edit-form], so initFormLivePreview()'s existing
 * delegated 'change' listener and requestFormPreview()'s merge-over-
 * lastSourcePayload already carry an edit straight through to the live
 * preview and to save, exactly like _audio_title in edit-section-general.php.
 * Only the uploaded-file case needs its own JS (initSourceMediaPicker() in
 * admin-new.js) to open a type-scoped wp.media frame.
 *
 * Expected variables (from html-player-edit-new-page.php includer scope):
 * @var int $post_id Current player ID - always > 0 here.
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_source_player_type = get_post_meta($post_id, '_player_type', true) ?: 'video';
$edit_source_video_type  = get_post_meta($post_id, '_video_type', true) ?: 'youtube';
$edit_source_html5_type  = get_post_meta($post_id, '_html5_source_type', true) ?: 'upload';
$edit_source_audio_type  = get_post_meta($post_id, '_audio_source_type', true) ?: 'upload';

$edit_source_is_upload = ('video' === $edit_source_player_type && 'html5' === $edit_source_video_type && 'upload' === $edit_source_html5_type)
  || ('audio' === $edit_source_player_type && 'upload' === $edit_source_audio_type);

if ($edit_source_is_upload) {
  $edit_source_attachment_id = (int) get_post_meta($post_id, ('audio' === $edit_source_player_type) ? '_audio_source' : '_video_source', true);
  $edit_source_attachment_url = $edit_source_attachment_id ? (wp_get_attachment_url($edit_source_attachment_id) ?: '') : '';
  $edit_source_attachment_filename = $edit_source_attachment_url !== '' ? wp_basename(parse_url($edit_source_attachment_url, PHP_URL_PATH)) : '';
} else {
  // Which single URL field is live depends on player type + video type -
  // same branching leanpl_get_source_type_label() (functions-player.php)
  // and the source-picker tabs use, kept local here since this is the only
  // place that needs the field name/label/value together, not just a label.
  if ('audio' === $edit_source_player_type) {
    $edit_source_field_name  = '_html5_audio_url';
    $edit_source_field_label = __('Audio URL', 'vapfem');
  } elseif ('vimeo' === $edit_source_video_type) {
    $edit_source_field_name  = '_vimeo_url';
    $edit_source_field_label = __('Vimeo URL', 'vapfem');
  } elseif ('html5' === $edit_source_video_type) {
    $edit_source_field_name  = '_html5_video_url';
    $edit_source_field_label = __('Video URL (CDN)', 'vapfem');
  } else {
    $edit_source_field_name  = '_youtube_url';
    $edit_source_field_label = __('YouTube URL', 'vapfem');
  }
  $edit_source_field_value = get_post_meta($post_id, $edit_source_field_name, true);
}
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
  <?php if ($edit_source_is_upload) : ?>
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php echo ('audio' === $edit_source_player_type) ? esc_html__('Uploaded Audio', 'vapfem') : esc_html__('Uploaded Video', 'vapfem'); ?>
    </div>
    <div
      data-lpl-source-media-picker="<?php echo esc_attr($edit_source_player_type); ?>"
      class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[8px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]"
    >
      <div class="lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-center lpl-items-start">
        <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-w-full lpl-text-ink lpl-font-medium lpl-truncate" data-lpl-source-media-filename>
          <?php echo esc_html($edit_source_attachment_filename !== '' ? $edit_source_attachment_filename : __('No file selected', 'vapfem')); ?>
        </div>
        <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#4F46E5] lpl-font-medium">
          <?php esc_html_e('Change', 'vapfem'); ?>
        </div>
      </div>
    </div>
  <?php else : ?>
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php echo esc_html($edit_source_field_label); ?>
    </div>
    <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
      <input type="text" name="<?php echo esc_attr($edit_source_field_name); ?>" value="<?php echo esc_attr($edit_source_field_value); ?>"
        class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none placeholder:lpl-text-label-secondary [appearance:none] [-webkit-appearance:none]" />
    </div>
  <?php endif; ?>
</div>
