<?php
/**
 * Shared edit-screen top bar. Player and playlist edit screens both
 * `include` this file after setting the variables below in scope -
 * this partial reads them, it never hardcodes anything screen-specific.
 *
 * Expected variables:
 * @var string $edit_back_url   Href for the back-chevron link.
 * @var string $edit_title      Screen title next to the logo, e.g. "Edit Player".
 * @var string $edit_post_title Current value of the inline-editable title field.
 * @var string $edit_shortcode  Shortcode text shown in the copy-chip.
 * @var string $edit_preview_url Href for the Preview button. '#' for a
 *             brand-new, unsaved post (see below) - the button renders
 *             disabled in that case and admin-new.js swaps in the real URL
 *             (from the save response's previewUrl) once the first save
 *             succeeds.
 * @var string $edit_publish_label 'Publish' or 'Update' - see
 *             Settings_Page::render_player_edit_new_page(). admin-new.js
 *             flips the rendered label to 'Update' in place after a
 *             successful save, so this is only the initial value.
 * @var bool   $edit_publish_wired Whether a save endpoint exists for this
 *             screen. False drops the data attribute admin-new.js binds to
 *             and greys the button out. Both player and playlist edit
 *             screens now set this true (leanpl_admin_new_save_player /
 *             leanpl_admin_new_save_playlist); a future screen that isn't
 *             wired yet can still ship the chrome disabled to match this
 *             partial's fallback path.
 * @var string $edit_trash_redirect Where the overflow menu's Trash lands
 *             after a successful trash - each screen's own list.
 * @var string $edit_add_new_url   Where the overflow menu's Add New lands -
 *             the same screen's URL with no ?post= (brand-new post).
 * @var string $edit_add_new_label Screen-aware label for Add New: "Add New
 *             Player" on the player edit screen, "Add New Playlist" on the
 *             playlist one. Empty hides the entry.
 * @var bool   $edit_add_new_is_playlist Whether this Add New entry should
 *             open the video/audio type picker modal (add-playlist-type-modal.php,
 *             initAddPlaylistModal() in admin-new.js) instead of navigating
 *             straight to $edit_add_new_url - true only on the playlist edit
 *             screen. Defaults to false so the player edit screen (which
 *             doesn't set this var at all) keeps its plain link.
 *
 * The overflow menu's Trash item reuses the list screen's existing
 * data-lpl-row-action mechanism (initRowActions() in admin-new.js, backed
 * by leanpl_admin_new_bulk_trash) rather than a new endpoint - only shown
 * once there's a real post to trash ($post_id > 0). The Add New item is a
 * plain anchor to the screen's own URL with no ?post=, which the render
 * method treats as a brand-new post (no saved source, no preview yet).
 * Title + Publish/Update are wired to a real post (leanpl_admin_new_save_player /
 * leanpl_admin_new_save_playlist, admin-new.js initPublishButton()).
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<div
  class="lpl-box-border lpl-w-full lpl-h-[63.5px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[0px_24px] lpl-justify-between lpl-items-center lpl-bg-[#FFFFFF] [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:var(--lpl-line)] [margin:0px_0px_-0.5px_0px]"
>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
    <a href="<?php echo esc_url($edit_back_url); ?>" class="lpl-box-border lpl-flex lpl-flex-row lpl-items-center">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-left'); ?>
    </a>
    <div class="lpl-box-border lpl-w-[30px] lpl-shrink-0 lpl-h-[30px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-brand lpl-rounded-[10px]">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('play'); ?>
    </div>
    <div class="lpl-text-[15px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php echo esc_html($edit_title); ?>
    </div>
    <div class="lpl-box-border lpl-w-[1px] lpl-shrink-0 lpl-h-[22px] lpl-bg-line-strong"></div>
    <div class="lpl-box-border lpl-w-[300px] lpl-shrink-0 lpl-h-[36px] lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[0px_12px] lpl-justify-between lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[10px]">
      <input type="text" data-lpl-title-input value="<?php echo esc_attr($edit_post_title); ?>"
        class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-text-left lpl-min-w-0 [flex:1_1_0] [appearance:none] [-webkit-appearance:none]" />
      <div data-lpl-title-focus class="lpl-cursor-pointer lpl-box-border lpl-shrink-0 lpl-flex lpl-flex-row lpl-items-center">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('pencil'); ?>
      </div>
    </div>
  </div>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Shortcode:', 'vapfem'); ?>
    </div>
    <?php $field = ['value' => $edit_shortcode, 'variant' => 'chip']; ?>
    <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-field-code.php'; ?>
    <?php unset($field); ?>
    <?php $edit_preview_unsaved = ('#' === $edit_preview_url); ?>
    <a
      href="<?php echo esc_url($edit_preview_url); ?>"
      <?php echo $edit_preview_unsaved ? '' : 'target="_blank" rel="noopener"'; ?>
      data-lpl-preview-button
      <?php echo $edit_preview_unsaved ? 'aria-disabled="true"' : ''; ?>
      class="lpl-btn lpl-btn--sm lpl-btn--outline aria-disabled:lpl-opacity-60">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('eye'); ?>
      <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Preview', 'vapfem'); ?>
      </div>
    </a>
    <div <?php echo $edit_publish_wired ? 'data-lpl-publish-button role="button" tabindex="0"' : 'aria-disabled="true" title="' . esc_attr__('Saving is not wired up on this screen yet.', 'vapfem') . '"'; ?>
      class="lpl-btn lpl-btn--sm lpl-btn--indigo aria-disabled:lpl-opacity-60 aria-disabled:lpl-pointer-events-none">
      <div data-lpl-publish-label class="lpl-box-border lpl-text-left [white-space:nowrap]">
        <?php echo esc_html($edit_publish_label); ?>
      </div>
    </div>
    <div class="lpl-relative">
      <div data-lpl-dropdown-trigger="edit-overflow"
        class="lpl-cursor-pointer lpl-box-border lpl-flex lpl-flex-row lpl-items-center">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('ellipsis'); ?>
      </div>
      <div data-lpl-dropdown="edit-overflow"
        class="lpl-hidden lpl-box-border lpl-w-[180px] lpl-h-fit [box-shadow:0px_12px_32px_0px_#1B22331F] lpl-absolute lpl-right-0 lpl-top-full lpl-mt-[8px] lpl-z-10 lpl-flex lpl-flex-col lpl-gap-[1px] lpl-p-[6px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px] lpl-rounded-[10px]">
        <?php if (!empty($edit_add_new_url) && !empty($edit_add_new_label)) : ?>
        <a
          href="<?php echo esc_url($edit_add_new_url); ?>"
          data-lpl-dropdown-close
          <?php echo !empty($edit_add_new_is_playlist) ? 'data-lpl-add-playlist-trigger' : ''; ?>
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px] hover:lpl-bg-surface ![text-decoration:none]">
          <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php echo esc_html($edit_add_new_label); ?>
          </div>
        </a>
        <?php endif; ?>
        <?php if ($post_id > 0) : ?>
        <div
          data-lpl-row-action="trash" data-lpl-row-action-id="<?php echo esc_attr($post_id); ?>"
          data-lpl-row-action-redirect="<?php echo esc_url($edit_trash_redirect); ?>"
          data-lpl-dropdown-close
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px] hover:lpl-bg-surface">
          <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Move to Trash', 'vapfem'); ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
