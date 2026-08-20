<?php
/**
 * "New Playlist" video/audio type picker - shown before every playlist
 * creation entry point navigates to the edit screen (initAddPlaylistModal()
 * in admin-new.js intercepts the trigger's click, this is what it shows).
 * Type is fixed at creation and has no picker on the edit screen itself
 * (see html-playlist-edit-new-page.php's own docblock), so this modal is the
 * only place it's ever chosen - picking a card calls leanpl_admin_new_create_playlist
 * (ajax-actions.php), which creates the real (draft) lean_playlist post with
 * _playlist_type already set, then navigates to the trigger's own href with
 * &post={id} appended. The edit screen only ever sees a real, already-typed
 * post through this flow - it never has to render a "type chosen but no
 * post yet" state.
 *
 * Included by both html-all-playlists-new-page.php (the two "Add Playlist"
 * triggers) and html-playlist-edit-new-page.php (the overflow menu's "Add
 * New Playlist" trigger, edit-topbar.php) - same one partial either way, not
 * duplicated per screen.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<div data-lpl-add-playlist-overlay class="lpl-hidden lpl-fixed lpl-inset-0 lpl-z-[100000] lpl-bg-[#1B223366] lpl-flex lpl-justify-center lpl-items-center lpl-p-[24px]">
  <div class="lpl-box-border lpl-w-[560px] lpl-max-w-full lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[22px] lpl-p-[28px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px] lpl-rounded-[16px] [box-shadow:0px_24px_64px_0px_#1B22334D]">
    <div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
      <div class="lpl-text-[18px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left">
        <?php esc_html_e('New Playlist', 'vapfem'); ?>
      </div>
      <div data-lpl-add-playlist-close role="button" tabindex="0" aria-label="<?php esc_attr_e('Close', 'vapfem'); ?>"
        class="lpl-cursor-pointer lpl-box-border lpl-w-[28px] lpl-h-[28px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-text-ink-soft hover:lpl-bg-surface lpl-rounded-[8px]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('x'); ?>
      </div>
    </div>

    <div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-row lpl-gap-[14px] lpl-justify-start lpl-items-stretch">
      <a href="#" data-lpl-add-playlist-type="video"
        class="lpl-group lpl-cursor-pointer lpl-box-border [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[14px] lpl-p-[20px] lpl-justify-start lpl-items-start lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] hover:lpl-bg-[#FFFFFF] hover:[outline:2px_solid_var(--lpl-brand)] hover:[box-shadow:0px_8px_20px_0px_#1B22331F] lpl-transition-colors aria-disabled:lpl-opacity-60 aria-disabled:lpl-pointer-events-none lpl-rounded-[10px] ![text-decoration:none]">
        <div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-row lpl-justify-between lpl-items-center">
          <div class="lpl-box-border lpl-w-[40px] lpl-shrink-0 lpl-h-[40px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#1E2634] lpl-rounded-[9px]">
            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('thumb-video'); ?>
          </div>
          <div class="lpl-box-border lpl-w-[18px] lpl-shrink-0 lpl-h-[18px] lpl-flex lpl-flex-row lpl-justify-center lpl-items-center lpl-text-brand lpl-opacity-0 [transform:translateX(-4px)] group-hover:lpl-opacity-100 group-hover:[transform:translateX(0px)] lpl-transition-all lpl-duration-150">
            <div class="[transform:rotate(180deg)]">
              <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-left'); ?>
            </div>
          </div>
        </div>
        <div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start">
          <div class="lpl-text-[15px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left">
            <?php esc_html_e('Video Playlist', 'vapfem'); ?>
          </div>
          <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-text-left">
            <?php esc_html_e('YouTube, Vimeo, or uploaded video tracks.', 'vapfem'); ?>
          </div>
        </div>
      </a>
      <a href="#" data-lpl-add-playlist-type="audio"
        class="lpl-group lpl-cursor-pointer lpl-box-border [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[14px] lpl-p-[20px] lpl-justify-start lpl-items-start lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] hover:lpl-bg-[#FFFFFF] hover:[outline:2px_solid_var(--lpl-brand)] hover:[box-shadow:0px_8px_20px_0px_#1B22331F] lpl-transition-colors aria-disabled:lpl-opacity-60 aria-disabled:lpl-pointer-events-none lpl-rounded-[10px] ![text-decoration:none]">
        <div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-row lpl-justify-between lpl-items-center">
          <div class="lpl-box-border lpl-w-[40px] lpl-shrink-0 lpl-h-[40px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#EEEEFE] lpl-rounded-[9px]">
            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('thumb-audio'); ?>
          </div>
          <div class="lpl-box-border lpl-w-[18px] lpl-shrink-0 lpl-h-[18px] lpl-flex lpl-flex-row lpl-justify-center lpl-items-center lpl-text-brand lpl-opacity-0 [transform:translateX(-4px)] group-hover:lpl-opacity-100 group-hover:[transform:translateX(0px)] lpl-transition-all lpl-duration-150">
            <div class="[transform:rotate(180deg)]">
              <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-left'); ?>
            </div>
          </div>
        </div>
        <div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start">
          <div class="lpl-text-[15px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left">
            <?php esc_html_e('Audio Playlist', 'vapfem'); ?>
          </div>
          <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-text-left">
            <?php esc_html_e('Podcast episodes or uploaded audio tracks.', 'vapfem'); ?>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>
