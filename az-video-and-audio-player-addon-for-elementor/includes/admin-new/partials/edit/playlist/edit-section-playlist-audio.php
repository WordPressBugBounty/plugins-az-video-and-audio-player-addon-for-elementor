<?php
/**
 * Playlist Audio card: now-playing display style. Audio-only - this
 * partial is only ever included when html-playlist-edit-new-page.php has
 * kept 'playlist-audio' in $edit_sections, which it does only for a
 * playlist whose _playlist_type is 'audio'.
 *
 * 'large' is pro-locked, same partial-option pattern as play_icon in
 * edit-section-playlist-items.php - see that file's docblock for why this
 * is hand-rolled instead of going through leanpl_admin_new_playlist_options().
 *
 * @var int $post_id Current playlist ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_now_playing_field    = leanpl_admin_new_playlist_registry()['_playlist_now_playing_style'] ?? [];
$edit_now_playing_options  = $edit_now_playing_field['options'] ?? [];
$edit_now_playing_pro_opts = $edit_now_playing_field['pro_options'] ?? [];
$edit_now_playing_stored   = leanpl_admin_new_playlist_value('_playlist_now_playing_style', $post_id);
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Now Playing Display', 'vapfem'); ?>
  </div>
  <select name="_playlist_now_playing_style" class="lpl-w-[140px] lpl-h-[36px]">
    <?php foreach ($edit_now_playing_options as $edit_now_playing_value => $edit_now_playing_label) :
      $edit_now_playing_locked   = in_array($edit_now_playing_value, $edit_now_playing_pro_opts, true);
      $edit_now_playing_selected = ((string) $edit_now_playing_value === $edit_now_playing_stored);
      $edit_now_playing_text     = $edit_now_playing_locked ? ($edit_now_playing_label . ' (' . __('PRO', 'vapfem') . ')') : $edit_now_playing_label;
    ?>
      <option value="<?php echo esc_attr($edit_now_playing_value); ?>"
        <?php echo $edit_now_playing_selected ? ' selected' : ''; ?>
        <?php echo $edit_now_playing_locked ? ' disabled' : ''; ?>>
        <?php echo esc_html($edit_now_playing_text); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>
