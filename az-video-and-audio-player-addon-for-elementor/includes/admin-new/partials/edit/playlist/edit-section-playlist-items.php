<?php
/**
 * Playlist List Items card: thumbnails on/off + shape, duration badges,
 * item numbers, meta text, and where the play icon shows.
 *
 * All boolean rows are switches (see edit-section-playlist-header.php's
 * docblock for why - classic has no Inherit state for these either).
 *
 * play_icon is free except its 'hidden' option, which classic pro-locks
 * (Playlist_Metaboxes::render_display_options_metabox()'s pro_options).
 * No admin-new pattern existed yet for locking a single <option> inside an
 * otherwise-free <select>, so this renders the option list by hand rather
 * than through leanpl_admin_new_playlist_options(): the locked option gets
 * `disabled` (native selects can't be clicked into, so there's nothing for
 * an onclick handler to intercept) plus a "(PRO)" suffix so it's clear why
 * it can't be picked, same intent as the gem-badge rows elsewhere just
 * expressed inline since it's one option, not the whole row.
 *
 * @var int $post_id Current playlist ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<?php
$edit_show_thumbnails_value = leanpl_admin_new_playlist_value('_playlist_show_thumbnails', $post_id);
$switch_label      = __('Show Thumbnails', 'vapfem');
$switch_name       = '_playlist_show_thumbnails';
$switch_value      = $edit_show_thumbnails_value;
$switch_show_if    = null;
$switch_show_if_on = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo ('0' !== $edit_show_thumbnails_value) ? '' : ' lpl-hidden'; ?>" data-lpl-show-if="_playlist_show_thumbnails:1">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Thumbnail Shape', 'vapfem'); ?>
  </div>
  <select name="_playlist_thumb_ratio" class="lpl-w-[140px] lpl-h-[36px]">
    <?php leanpl_admin_new_playlist_options('_playlist_thumb_ratio', $post_id); ?>
  </select>
</div>

<?php
$edit_show_duration_badge_value = leanpl_admin_new_playlist_value('_playlist_show_duration_badge', $post_id);
$switch_label      = __('Show Duration Badge', 'vapfem');
$switch_name       = '_playlist_show_duration_badge';
$switch_value      = $edit_show_duration_badge_value;
$switch_show_if    = '_playlist_show_thumbnails:1';
$switch_show_if_on = ('0' !== $edit_show_thumbnails_value);
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<?php
$switch_label       = __('Show Duration Next To Title', 'vapfem');
$switch_name        = '_playlist_show_duration_in_list';
$switch_value       = leanpl_admin_new_playlist_value('_playlist_show_duration_in_list', $post_id);
$switch_show_if     = null;
$switch_show_if_on  = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<?php
$switch_label       = __('Show Item Numbers', 'vapfem');
$switch_name        = '_playlist_show_numbers';
$switch_value       = leanpl_admin_new_playlist_value('_playlist_show_numbers', $post_id);
$switch_show_if     = null;
$switch_show_if_on  = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<?php
$switch_label       = __('Show Meta Text', 'vapfem');
$switch_name        = '_playlist_show_meta';
$switch_value       = leanpl_admin_new_playlist_value('_playlist_show_meta', $post_id);
$switch_show_if     = null;
$switch_show_if_on  = null;
include LEANPL_DIR . '/includes/admin-new/partials/shared/field-switch.php';
?>

<?php
$edit_play_icon_field    = leanpl_admin_new_playlist_registry()['_playlist_play_icon'] ?? [];
$edit_play_icon_options  = $edit_play_icon_field['options'] ?? [];
$edit_play_icon_pro_opts = $edit_play_icon_field['pro_options'] ?? [];
$edit_play_icon_stored   = leanpl_admin_new_playlist_value('_playlist_play_icon', $post_id);
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Play Icon', 'vapfem'); ?>
  </div>
  <select name="_playlist_play_icon" class="lpl-w-[140px] lpl-h-[36px]">
    <?php foreach ($edit_play_icon_options as $edit_play_icon_value => $edit_play_icon_label) :
      $edit_play_icon_locked   = in_array($edit_play_icon_value, $edit_play_icon_pro_opts, true);
      $edit_play_icon_selected = ((string) $edit_play_icon_value === $edit_play_icon_stored);
      $edit_play_icon_text     = $edit_play_icon_locked ? ($edit_play_icon_label . ' (' . __('PRO', 'vapfem') . ')') : $edit_play_icon_label;
    ?>
      <option value="<?php echo esc_attr($edit_play_icon_value); ?>"
        <?php echo $edit_play_icon_selected ? ' selected' : ''; ?>
        <?php echo $edit_play_icon_locked ? ' disabled' : ''; ?>>
        <?php echo esc_html($edit_play_icon_text); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>
