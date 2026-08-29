<?php
/**
 * Playlist Appearance card: skin, accent color, background style, and the
 * gradient CSS string.
 *
 * Skin is free with only its 'dark' option pro-locked (Lock 17), same
 * partial-lock shape as edit-section-playlist-layout.php's Item Template
 * row. Accent color is whole-field pro (same lock pattern as the player
 * screen's edit-section-advanced.php gem-badged rows: row onclick opens
 * the upgrade modal, control gets disabled + no name attribute so a
 * locked value never posts, gem icon replaces the label). Background
 * style is free with only its 'gradient' option pro-locked, same
 * partial-lock shape as Skin. Gradient CSS stays whole-field pro since
 * it's only meaningful once gradient is unlocked.
 *
 * Accent color reuses the same wp-color-picker widget as the player
 * screen's _primary_color row - 'lex-color-picker' + data-color-picker are
 * its init hooks, initColorPickers() in admin-new.js does the wpColorPicker()
 * call.
 *
 * @var int $post_id Current playlist ID, 0 for a brand-new one.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<?php
$edit_skin_field    = leanpl_admin_new_playlist_registry()['_playlist_skin'] ?? [];
$edit_skin_options  = $edit_skin_field['options'] ?? [];
$edit_skin_pro_opts = $edit_skin_field['pro_options'] ?? [];
$edit_skin_stored   = leanpl_admin_new_playlist_value('_playlist_skin', $post_id);
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Skin', 'vapfem'); ?>
    </div>
    <?php if ($edit_skin_pro_opts) : ?>
      <span class="lpl-cursor-pointer" onclick="openUpgradeModal()"><?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?></span>
    <?php endif; ?>
  </div>
  <select name="_playlist_skin" class="lpl-w-[140px] lpl-h-[36px]">
    <?php foreach ($edit_skin_options as $edit_skin_value => $edit_skin_label) :
      $edit_skin_locked   = in_array($edit_skin_value, $edit_skin_pro_opts, true);
      $edit_skin_selected = ((string) $edit_skin_value === $edit_skin_stored);
      $edit_skin_text     = $edit_skin_locked ? ($edit_skin_label . ' (' . __('PRO', 'vapfem') . ')') : $edit_skin_label;
    ?>
      <option value="<?php echo esc_attr($edit_skin_value); ?>"
        <?php echo $edit_skin_selected ? ' selected' : ''; ?>
        <?php echo $edit_skin_locked ? ' disabled' : ''; ?>>
        <?php echo esc_html($edit_skin_text); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<?php $edit_accent_locked = leanpl_admin_new_playlist_is_locked('_playlist_accent_color'); ?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo $edit_accent_locked ? ' lpl-cursor-pointer' : ''; ?>"<?php echo $edit_accent_locked ? ' onclick="openUpgradeModal()"' : ''; ?>>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Accent Color', 'vapfem'); ?>
    </div>
    <?php if ($edit_accent_locked) : ?>
    <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?>
    <?php endif; ?>
  </div>
  <input
    type="text"
    id="lpl-playlist-accent-color"
    <?php if (!$edit_accent_locked) : ?>name="_playlist_accent_color"<?php endif; ?>
    value="<?php echo esc_attr(leanpl_admin_new_playlist_value('_playlist_accent_color', $post_id)); ?>"
    class="lex-color-picker lpl-w-[140px]"
    data-color-picker="true"
    <?php if ($edit_accent_locked) : ?>disabled<?php endif; ?>
  />
</div>

<?php
$edit_bg_style_field    = leanpl_admin_new_playlist_registry()['_playlist_bg_style'] ?? [];
$edit_bg_style_options  = $edit_bg_style_field['options'] ?? [];
$edit_bg_style_pro_opts = $edit_bg_style_field['pro_options'] ?? [];
$edit_bg_style_stored   = leanpl_admin_new_playlist_value('_playlist_bg_style', $post_id);
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Background Style', 'vapfem'); ?>
    </div>
    <?php if ($edit_bg_style_pro_opts) : ?>
      <span class="lpl-cursor-pointer" onclick="openUpgradeModal()"><?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?></span>
    <?php endif; ?>
  </div>
  <select name="_playlist_bg_style" class="lpl-w-[140px] lpl-h-[36px]">
    <?php foreach ($edit_bg_style_options as $edit_bg_style_value => $edit_bg_style_label) :
      $edit_bg_style_locked   = in_array($edit_bg_style_value, $edit_bg_style_pro_opts, true);
      $edit_bg_style_selected = ((string) $edit_bg_style_value === $edit_bg_style_stored);
      $edit_bg_style_text     = $edit_bg_style_locked ? ($edit_bg_style_label . ' (' . __('PRO', 'vapfem') . ')') : $edit_bg_style_label;
    ?>
      <option value="<?php echo esc_attr($edit_bg_style_value); ?>"
        <?php echo $edit_bg_style_selected ? ' selected' : ''; ?>
        <?php echo $edit_bg_style_locked ? ' disabled' : ''; ?>>
        <?php echo esc_html($edit_bg_style_text); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<?php
$edit_gradient_locked      = leanpl_admin_new_playlist_is_locked('_playlist_gradient_css');
$edit_shows_gradient_css   = leanpl_admin_new_playlist_value('_playlist_bg_style', $post_id) === 'gradient';
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start<?php echo $edit_gradient_locked ? ' lpl-cursor-pointer' : ''; ?><?php echo $edit_shows_gradient_css ? '' : ' lpl-hidden'; ?>" data-lpl-show-if="_playlist_bg_style:gradient"<?php echo $edit_gradient_locked ? ' onclick="openUpgradeModal()"' : ''; ?>>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Gradient CSS', 'vapfem'); ?>
      </div>
      <?php if ($edit_gradient_locked) : ?>
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[8px_12px] lpl-justify-start lpl-items-start lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <textarea
      <?php if (!$edit_gradient_locked) : ?>name="_playlist_gradient_css"<?php endif; ?>
      rows="2"
      placeholder="linear-gradient(135deg, #1a1a2e, #16213e)"
      <?php if ($edit_gradient_locked) : ?>disabled<?php endif; ?>
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none placeholder:lpl-text-label-secondary lpl-resize-none"
    ><?php echo esc_textarea(leanpl_admin_new_playlist_value('_playlist_gradient_css', $post_id)); ?></textarea>
  </div>
</div>
