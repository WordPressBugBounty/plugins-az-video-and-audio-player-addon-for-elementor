<?php
/**
 * Playlist edit screen - "Add Track" drawer. Opens from the floating Add
 * Track button below the live preview on a saved playlist, or from the
 * empty-state panel's own Add Track button on a brand-new one
 * (html-playlist-edit-new-page.php).
 *
 * Quick Add and Bulk Add are still visual only - real markup, but no
 * wp.media picker, no AJAX, no _playlist_items write. Media Hub is not:
 * its row list is a live lean_player query (not sample data), and filter +
 * search run client-side over those real rows. Checking/unchecking a row
 * stages that change in the DOM only (no write - same "nothing persists
 * until Update/Save Draft" contract every other field on this screen
 * follows) and re-requests the live preview with the drawer's current
 * checked set via item_ids (JSON-encoded, not a plain array - see
 * trackDrawerPendingItemIdsJson() in admin-new.js), which
 * class-live-preview-ajax.php uses to
 * override _playlist_items for that one render. The staged set only
 * actually reaches _playlist_items when leanpl_admin_new_save_playlist()
 * runs (ajax-actions.php), i.e. Update/Publish/Save Draft. Tab switching,
 * open/close (X, Cancel, overlay click, Escape), and the Add Another
 * checkbox are the remaining plain visual toggles - see initTrackDrawer()
 * in admin-new.js.
 *
 * Quick Add and Bulk Add are wired for real: both post to the same
 * leanpl_playlist_quick_add / leanpl_playlist_batch_add AJAX endpoints the
 * classic metabox's builder already uses (includes/playlist/ajax-actions.php),
 * reused as-is rather than duplicated - see initTrackDrawerQuickAdd() /
 * initTrackDrawerBulkAdd() in admin-new.js. A track created this way lands
 * as a new, pre-checked row in the Media Hub list (trackDrawerInsertRow()),
 * staged the same way a Media Hub checkbox click already is - nothing
 * writes to _playlist_items until Update/Publish/Save Draft.
 *
 * Included once per playlist edit screen load, regardless of $post_id -
 * Quick/Bulk Add and Media Hub all work before the playlist itself is ever
 * saved (they create/stage real lean_player posts, not playlist meta), and
 * the staged item_ids reach _playlist_items once
 * leanpl_admin_new_save_playlist() runs for the save that creates the
 * playlist post. $post_id inside this partial is just 0 in that case - the
 * "existing items" query below already guards on $post_id > 0 on its own.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<div data-lpl-track-drawer-overlay class="lpl-hidden lpl-fixed lpl-inset-0 lpl-z-[100000] lpl-bg-[#1B223366]"></div>
<div
  data-lpl-track-drawer
  role="dialog"
  aria-modal="true"
  aria-hidden="true"
  aria-label="<?php esc_attr_e('Add to Playlist', 'vapfem'); ?>"
  class="lpl-hidden lpl-fixed lpl-top-0 lpl-right-0 lpl-h-full lpl-w-[500px] lpl-max-w-full lpl-z-[100001] lpl-box-border lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] lpl-border-l lpl-border-line-strong lpl-shadow-[0_8px_30px_rgba(0,0,0,0.15)] lpl-translate-x-full lpl-transition-transform lpl-duration-200"
>
  <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[12px] lpl-p-[16px_24px] lpl-justify-start lpl-items-start lpl-border-b lpl-border-line-strong">
    <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
      <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
        <div class="lpl-box-border lpl-w-[30px] lpl-shrink-0 lpl-h-[30px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-ink lpl-rounded-[8px]">
          <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('plus'); ?>
        </div>
        <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start">
          <div class="lpl-text-[14px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-bold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Add to Playlist', 'vapfem'); ?>
          </div>
          <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-normal lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Paste, pick, or upload a track', 'vapfem'); ?>
          </div>
        </div>
      </div>
      <div
        data-lpl-track-drawer-close
        class="lpl-cursor-pointer lpl-box-border lpl-w-[28px] lpl-shrink-0 lpl-h-[28px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#F6F7F9] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px] lpl-text-ink-soft"
      >
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('x'); ?>
      </div>
    </div>
    <div data-lpl-track-drawer-tabs class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[4px] lpl-p-[4px] lpl-justify-start lpl-items-start lpl-bg-[#F6F7F9] lpl-rounded-[8px]">
      <div data-lpl-track-drawer-tab="quick" role="tab" tabindex="0" aria-selected="true" class="lpl-group lpl-cursor-pointer lpl-box-border [flex:1_1_0] lpl-h-[30px] lpl-flex lpl-flex-row lpl-gap-[4px] lpl-justify-center lpl-items-center lpl-bg-[#00000000] aria-selected:lpl-bg-ink lpl-rounded-[8px] lpl-text-[#000000] aria-selected:lpl-text-[#FFFFFF]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('circle-plus'); ?>
        <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-font-semibold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Quick Add', 'vapfem'); ?>
        </div>
      </div>
      <div data-lpl-track-drawer-tab="media-hub" role="tab" tabindex="0" aria-selected="false" class="lpl-group lpl-cursor-pointer lpl-box-border [flex:1_1_0] lpl-h-[30px] lpl-flex lpl-flex-row lpl-gap-[4px] lpl-justify-center lpl-items-center lpl-bg-[#00000000] aria-selected:lpl-bg-ink lpl-rounded-[8px] lpl-text-[#000000] aria-selected:lpl-text-[#FFFFFF]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('list'); ?>
        <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-font-semibold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Media Players', 'vapfem'); ?>
        </div>
      </div>
      <div data-lpl-track-drawer-tab="bulk" role="tab" tabindex="0" aria-selected="false" class="lpl-group lpl-cursor-pointer lpl-box-border [flex:1_1_0] lpl-h-[30px] lpl-flex lpl-flex-row lpl-gap-[4px] lpl-justify-center lpl-items-center lpl-bg-[#00000000] aria-selected:lpl-bg-ink lpl-rounded-[8px] lpl-text-[#000000] aria-selected:lpl-text-[#FFFFFF]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('copy-plus'); ?>
        <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-font-semibold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Bulk Add', 'vapfem'); ?>
        </div>
      </div>
    </div>
  </div>

  <?php // ── Quick Add panel ────────────────────────────────────────────────── ?>
  <div data-lpl-track-drawer-panel="quick" class="lpl-box-border lpl-w-full [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start lpl-overflow-y-auto">
    <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[8px] lpl-p-[12px_24px] lpl-justify-start lpl-items-start lpl-border-b lpl-border-line-strong">
      <label class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Paste a link', 'vapfem'); ?>
      </label>
      <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
        <?php
        // Audio never gets a youtube/vimeo badge (see the Media Hub filter
        // comment below) - the youtube example placeholder would be
        // misleading here, so audio gets a direct-file example instead.
        $track_drawer_url_placeholder = ( 'audio' === $edit_playlist_type )
          ? __( 'https://example.com/podcast-ep12.mp3', 'vapfem' )
          : __( 'https://youtube.com/watch?v=dQw4w9WgXcQ', 'vapfem' );
        ?>
        <input type="text" data-lpl-track-drawer-url placeholder="<?php echo esc_attr( $track_drawer_url_placeholder ); ?>"
          class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
      </div>
    </div>
    <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[12px_24px] lpl-justify-center lpl-items-center">
      <div class="lpl-box-border [flex:1_1_0] lpl-h-[1px] lpl-bg-line"></div>
      <div class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-semibold lpl-tracking-[0.5px] lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('OR', 'vapfem'); ?>
      </div>
      <div class="lpl-box-border [flex:1_1_0] lpl-h-[1px] lpl-bg-line"></div>
    </div>
    <div data-lpl-track-drawer-upload data-attachment-id="" class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[8px] lpl-p-[12px_24px] lpl-justify-start lpl-items-start lpl-border-b lpl-border-line-strong">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Or Upload', 'vapfem'); ?>
      </div>
      <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
        <div data-lpl-track-drawer-upload-preview class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-normal lpl-text-left lpl-w-full lpl-overflow-hidden [text-overflow:ellipsis] [white-space:nowrap]">
          <?php esc_html_e('No media selected', 'vapfem'); ?>
        </div>
      </div>
      <div class="lpl-box-border lpl-w-fit lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-start">
        <div data-lpl-track-drawer-upload-select class="lpl-btn lpl-btn--outline lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_12px] lpl-rounded-[8px] lpl-text-[13px]/[normal] [outline:1px_solid_var(--lpl-ink)]">
          <?php esc_html_e('Select / Upload Media', 'vapfem'); ?>
        </div>
        <div data-lpl-track-drawer-upload-remove class="lpl-btn lpl-btn--outline lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_12px] lpl-rounded-[8px] lpl-text-[13px]/[normal]">
          <?php esc_html_e('Remove', 'vapfem'); ?>
        </div>
      </div>
    </div>
    <?php
    // Title/Duration/Meta Text/Custom Thumbnail are all optional enrichment
    // (Title falls back to the URL/attachment title server-side, the rest
    // default to empty/none) - folded behind a dedicated, non-exclusive
    // toggle rather than the page's data-lpl-accordion-trigger mechanism:
    // that one is global and exclusive (initAccordions() closes every OTHER
    // [data-lpl-accordion-trigger] on the page when one opens), which would
    // fight the edit screen's own Layout/Behavior/Advanced cards if reused
    // here. This pair is scoped to the drawer only - see the small dedicated
    // handler in initTrackDrawer() (admin-new.js).
    ?>
    <div data-lpl-track-drawer-more-toggle role="button" tabindex="0" aria-expanded="false" class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[12px_24px] lpl-justify-start lpl-items-center lpl-bg-surface lpl-border-b lpl-border-line-strong hover:lpl-bg-[#F3F4F7]">
      <div class="lpl-box-border lpl-w-[22px] lpl-shrink-0 lpl-h-[22px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[6px] group-aria-expanded:[transform:rotate(180deg)]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-down'); ?>
      </div>
      <div class="lpl-text-[14px]/[normal] lpl-box-border lpl-text-ink lpl-font-bold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Additional Data', 'vapfem'); ?>
      </div>
    </div>

    <div data-lpl-track-drawer-more class="lpl-hidden lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start">
      <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[8px] lpl-p-[12px_24px] lpl-justify-start lpl-items-start lpl-border-b lpl-border-line-strong">
        <label class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Title', 'vapfem'); ?>
        </label>
        <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
          <input type="text" data-lpl-track-drawer-title
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
        </div>
      </div>
      <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[20px] lpl-p-[12px_24px] lpl-justify-start lpl-items-start lpl-border-b lpl-border-line-strong">
        <div class="lpl-box-border [flex:1_1_0] lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[8px] lpl-justify-start lpl-items-start">
          <label class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Duration', 'vapfem'); ?>
          </label>
          <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
            <input type="text" data-lpl-track-drawer-duration placeholder="<?php esc_attr_e('e.g. 3:45', 'vapfem'); ?>"
              class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
          </div>
        </div>
        <div class="lpl-box-border [flex:1_1_0] lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[8px] lpl-justify-start lpl-items-start">
          <label class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Meta Text', 'vapfem'); ?>
          </label>
          <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
            <input type="text" data-lpl-track-drawer-meta-text placeholder="<?php esc_attr_e('e.g. Episode 12', 'vapfem'); ?>"
              class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
          </div>
        </div>
      </div>
      <div data-lpl-track-drawer-poster data-attachment-id="" class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[8px] lpl-p-[12px_24px] lpl-justify-start lpl-items-start">
        <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Custom Thumbnail', 'vapfem'); ?>
        </div>

        <?php
        // Same two-state empty/set pattern as the player edit screen's
        // poster field (edit-section-general.php's
        // data-lpl-poster-empty/-set), so this reads as the same control
        // rather than a bespoke one - see initTrackDrawerMediaPickers() in
        // admin-new.js for the JS side.
        ?>
        <div data-lpl-track-drawer-poster-empty data-lpl-track-drawer-poster-select class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
          <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-normal lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('No image selected', 'vapfem'); ?>
          </div>
        </div>

        <div data-lpl-track-drawer-poster-set class="lpl-hidden lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[8px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
          <div class="lpl-relative lpl-box-border lpl-w-[52px] lpl-h-[52px] lpl-shrink-0 lpl-rounded-[6px] lpl-overflow-hidden [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px]">
            <img src="" alt="" class="lpl-absolute lpl-top-0 lpl-left-0 lpl-w-full lpl-h-full lpl-object-cover" data-lpl-track-drawer-poster-image />
          </div>
          <div class="lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-center lpl-items-start">
            <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-w-full lpl-text-ink lpl-font-medium lpl-truncate" data-lpl-track-drawer-poster-filename></div>
            <div class="lpl-box-border lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
              <div data-lpl-track-drawer-poster-select role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#4F46E5] lpl-font-medium">
                <?php esc_html_e('Change', 'vapfem'); ?>
              </div>
              <div data-lpl-track-drawer-poster-remove role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#DC2626] lpl-font-medium">
                <?php esc_html_e('Remove', 'vapfem'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php // ── Bulk Add panel ──────────────────────────────────────────────────── ?>
  <div data-lpl-track-drawer-panel="bulk" class="lpl-hidden lpl-box-border lpl-w-full [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[8px] lpl-p-[12px_24px] lpl-justify-start lpl-items-start">
    <label class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Paste Media URLs', 'vapfem'); ?>
    </label>
    <?php
    // Same audio-has-no-youtube/vimeo reasoning as the Quick Add url field
    // above - bulk examples swap to direct audio-file URLs for audio.
    $track_drawer_bulk_placeholder = ( 'audio' === $edit_playlist_type )
      ? "https://example.com/podcast-ep12.mp3\nhttps://example.com/podcast-ep13.mp3\n\n" . __( 'Paste one URL per line', 'vapfem' )
      : "https://youtube.com/watch?v=dQw4w9WgXcQ\nhttps://vimeo.com/76979871\nhttps://example.com/media.mp4\n\n" . __( 'Paste one URL per line', 'vapfem' );
    ?>
    <textarea rows="10" data-lpl-track-drawer-bulk-urls placeholder="<?php echo esc_attr( $track_drawer_bulk_placeholder ); ?>"
      class="lpl-text-[13px]/[21px] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-h-[280px] lpl-p-[12px] lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px] lpl-border-0 lpl-resize-none focus:lpl-outline-none"></textarea>
  </div>

  <?php // ── Media Hub panel ─────────────────────────────────────────────────── ?>
  <div data-lpl-track-drawer-panel="media-hub" class="lpl-hidden lpl-box-border lpl-w-full [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[12px] lpl-p-[12px_24px] lpl-justify-start lpl-items-start lpl-overflow-y-auto">
    <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('search'); ?>
      <input type="text" data-lpl-track-drawer-search placeholder="<?php esc_attr_e('Search your media library...', 'vapfem'); ?>"
        class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
    </div>
    <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-center">
      <?php
      // Audio players never get a youtube/vimeo badge - the audio branch of
      // leanpl_get_player_source_badge() only ever returns 'external' or
      // 'self-hosted' - so those two pills would just be permanent zero-
      // result dead ends on an audio playlist.
      $track_drawer_filters = ( 'audio' === $edit_playlist_type )
        ? [
            'all'      => __( 'All', 'vapfem' ),
            'external' => __( 'External', 'vapfem' ),
            'uploaded' => __( 'Uploaded', 'vapfem' ),
          ]
        : [
            'all'      => __( 'All', 'vapfem' ),
            'youtube'  => __( 'YouTube', 'vapfem' ),
            'vimeo'    => __( 'Vimeo', 'vapfem' ),
            'external' => __( 'External', 'vapfem' ),
            'uploaded' => __( 'Uploaded', 'vapfem' ),
          ];
      foreach ( $track_drawer_filters as $track_drawer_filter_key => $track_drawer_filter_label ) :
        $track_drawer_filter_active = ( 'all' === $track_drawer_filter_key );
      ?>
      <div data-lpl-track-drawer-filter="<?php echo esc_attr( $track_drawer_filter_key ); ?>" aria-pressed="<?php echo $track_drawer_filter_active ? 'true' : 'false'; ?>" class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-[26px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-center lpl-items-center lpl-bg-surface aria-pressed:lpl-bg-ink lpl-rounded-[12px]">
        <div class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#000000] group-aria-pressed:lpl-text-[#FFFFFF] lpl-font-medium lpl-text-left [white-space:nowrap]">
          <?php echo esc_html( $track_drawer_filter_label ); ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div data-lpl-track-drawer-media-list class="lpl-box-border lpl-w-full [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start">
      <?php
      // Real lean_player posts, same type (video/audio) as this playlist -
      // source badge comes from leanpl_get_player_source_badge(), the same
      // helper the All Players list uses, so "YouTube/Vimeo/External/
      // Uploaded" here always matches what that screen would show. Clicking
      // a row still only toggles its own selected look (initTrackDrawer() in
      // admin-new.js) - no wp.media wiring, no _playlist_items write yet.
      $track_drawer_source_labels = [
        'youtube'  => __( 'YouTube', 'vapfem' ),
        'vimeo'    => __( 'Vimeo', 'vapfem' ),
        'external' => __( 'External', 'vapfem' ),
        'uploaded' => __( 'Uploaded', 'vapfem' ),
      ];
      $track_drawer_source_colors = [
        'youtube'  => '#EF4444',
        'vimeo'    => '#1AB7EA',
        'external' => '#767D86',
        'uploaded' => '#10B981',
      ];
      // Player IDs already saved on this playlist - rows for these start
      // pre-selected (checked, not plus) so Media Hub reflects what's
      // actually already in the playlist rather than looking empty.
      $track_drawer_existing_item_ids = [];
      if ( $post_id > 0 ) {
        $track_drawer_saved_items = get_post_meta( $post_id, '_playlist_items', true );
        if ( is_array( $track_drawer_saved_items ) ) {
          foreach ( $track_drawer_saved_items as $track_drawer_saved_item ) {
            $track_drawer_existing_item_ids[] = absint( $track_drawer_saved_item['id'] ?? 0 );
          }
        }
      }

      // A missing _player_type meta defaults to 'video' everywhere else in
      // this codebase (see Config::build_single_item()) - matched here with
      // an EXISTS/NOT EXISTS pair rather than a plain meta_value so video
      // players saved before this meta key existed still show up.
      $track_drawer_media_meta_query = ( 'video' === $edit_playlist_type )
        ? [
            'relation' => 'OR',
            [ 'key' => '_player_type', 'value' => 'video' ],
            [ 'key' => '_player_type', 'compare' => 'NOT EXISTS' ],
          ]
        : [ [ 'key' => '_player_type', 'value' => $edit_playlist_type ] ];
      $track_drawer_media_posts = get_posts( [
        'post_type'      => 'lean_player',
        'post_status'    => [ 'publish', 'draft' ],
        'posts_per_page' => -1,
        'meta_query'     => $track_drawer_media_meta_query,
        'orderby'        => 'date',
        'order'          => 'DESC',
      ] );
      foreach ( $track_drawer_media_posts as $track_drawer_media_post ) :
        $track_drawer_media_badge  = leanpl_get_player_source_badge( $track_drawer_media_post->ID );
        $track_drawer_media_source = ( 'self-hosted' === $track_drawer_media_badge['type'] ) ? 'uploaded' : $track_drawer_media_badge['type'];
        $track_drawer_media_label  = $track_drawer_source_labels[ $track_drawer_media_source ] ?? $track_drawer_media_badge['label'];
        $track_drawer_media_color  = $track_drawer_source_colors[ $track_drawer_media_source ] ?? '#767D86';
        $track_drawer_media_in_playlist = in_array( $track_drawer_media_post->ID, $track_drawer_existing_item_ids, true );
      ?>
      <div data-lpl-track-drawer-media-row data-lpl-track-drawer-player-id="<?php echo esc_attr( $track_drawer_media_post->ID ); ?>" data-lpl-track-drawer-source="<?php echo esc_attr( $track_drawer_media_source ); ?>" aria-selected="<?php echo $track_drawer_media_in_playlist ? 'true' : 'false'; ?>" class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-min-h-[48px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[8px_5px] lpl-justify-start lpl-items-center lpl-rounded-[8px] hover:lpl-bg-surface">
        <div class="lpl-box-border lpl-w-[22px] lpl-shrink-0 lpl-h-[22px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-surface [outline:1px_solid_#000000] [outline-offset:-0.5px] group-aria-selected:lpl-bg-brand group-aria-selected:[outline:none] lpl-rounded-[6px] lpl-text-[#FFFFFF]">
          <span class="lpl-hidden group-aria-selected:lpl-flex"><?php echo \LeanPL\Admin_New\Admin_New_Icons::get('check'); ?></span>
          <span class="lpl-flex group-aria-selected:lpl-hidden lpl-text-[#000000]"><?php echo \LeanPL\Admin_New\Admin_New_Icons::get('plus'); ?></span>
        </div>
        <div class="lpl-box-border [flex:1_1_0] lpl-min-w-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start">
          <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-medium lpl-text-left [overflow-wrap:anywhere]">
            <?php echo esc_html( get_the_title( $track_drawer_media_post ) ); ?>
          </div>
        </div>
        <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-[20px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_8px] lpl-justify-center lpl-items-center lpl-rounded-[4px]" style="background-color:<?php echo esc_attr( $track_drawer_media_color ); ?>1A">
          <div class="lpl-text-[10px]/[normal] lpl-box-border lpl-font-semibold lpl-tracking-[0.3px] lpl-text-left [white-space:nowrap]" style="color:<?php echo esc_attr( $track_drawer_media_color ); ?>">
            <?php echo esc_html( $track_drawer_media_label ); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <div data-lpl-track-drawer-media-empty class="lpl-hidden lpl-box-border lpl-w-full lpl-p-[24px_0px] lpl-text-[13px]/[normal] lpl-text-ink-soft lpl-text-center">
        <?php esc_html_e( 'No media found.', 'vapfem' ); ?>
      </div>
    </div>
  </div>

  <?php
  // Three footer variants, one per tab - only the one matching the active
  // tab is shown (initTrackDrawer() toggles lpl-hidden alongside the panels
  // above). Quick Add's and Bulk Add's primary buttons post to
  // leanpl_playlist_quick_add / leanpl_playlist_batch_add (see
  // initTrackDrawerQuickAdd() / initTrackDrawerBulkAdd() in admin-new.js);
  // Media Hub's Done just closes the drawer - its rows already stage
  // add/remove on click, no separate submit of their own.
  ?>
  <div data-lpl-track-drawer-footer="quick" class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[12px_24px] lpl-justify-between lpl-items-center lpl-bg-[#FFFFFF] lpl-border-t lpl-border-line-strong">
    <label class="lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-center">
      <div class="lpl-relative lpl-box-border lpl-w-[15px] lpl-shrink-0 lpl-h-[15px]">
        <input type="checkbox" data-lpl-track-drawer-add-another
          class="lpl-peer lpl-appearance-none lpl-absolute lpl-inset-0 lpl-m-0 lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-full lpl-bg-[#FFFFFF] peer-checked:lpl-bg-brand [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[4px]" />
        <span class="lpl-pointer-events-none lpl-absolute lpl-inset-0 lpl-hidden peer-checked:lpl-flex lpl-flex-row lpl-justify-center lpl-items-center lpl-text-[#FFFFFF]"><?php echo \LeanPL\Admin_New\Admin_New_Icons::get('check'); ?></span>
      </div>
      <div class="lpl-text-[13px]/[normal] lpl-leading-none lpl-box-border lpl-text-[#000000] lpl-font-normal lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Add another after saving', 'vapfem'); ?>
      </div>
    </label>
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-center">
      <div data-lpl-track-drawer-cancel class="lpl-btn lpl-btn--outline lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_12px] lpl-rounded-[8px] lpl-text-[13px]/[normal]">
        <?php esc_html_e('Cancel', 'vapfem'); ?>
      </div>
      <div data-lpl-track-drawer-submit="quick" class="lpl-btn lpl-btn--indigo lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_16px] lpl-rounded-[8px] lpl-text-[13px]/[normal]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('plus'); ?>
        <span data-lpl-track-drawer-submit-label><?php esc_html_e('Add Track', 'vapfem'); ?></span>
      </div>
    </div>
  </div>
  <div data-lpl-track-drawer-footer="bulk" class="lpl-hidden lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[12px_24px] lpl-justify-between lpl-items-center lpl-bg-[#FFFFFF] lpl-border-t lpl-border-line-strong">
    <label class="lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-center">
      <div class="lpl-relative lpl-box-border lpl-w-[15px] lpl-shrink-0 lpl-h-[15px]">
        <input type="checkbox" data-lpl-track-drawer-add-another
          class="lpl-peer lpl-appearance-none lpl-absolute lpl-inset-0 lpl-m-0 lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-full lpl-bg-[#FFFFFF] peer-checked:lpl-bg-brand [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[4px]" />
        <span class="lpl-pointer-events-none lpl-absolute lpl-inset-0 lpl-hidden peer-checked:lpl-flex lpl-flex-row lpl-justify-center lpl-items-center lpl-text-[#FFFFFF]"><?php echo \LeanPL\Admin_New\Admin_New_Icons::get('check'); ?></span>
      </div>
      <div class="lpl-text-[13px]/[normal] lpl-leading-none lpl-box-border lpl-text-[#000000] lpl-font-normal lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Add another after saving', 'vapfem'); ?>
      </div>
    </label>
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-center">
      <div data-lpl-track-drawer-cancel class="lpl-btn lpl-btn--outline lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_12px] lpl-rounded-[8px] lpl-text-[13px]/[normal]">
        <?php esc_html_e('Cancel', 'vapfem'); ?>
      </div>
      <div data-lpl-track-drawer-submit="bulk" class="lpl-btn lpl-btn--indigo lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_16px] lpl-rounded-[8px] lpl-text-[13px]/[normal]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('plus'); ?>
        <span data-lpl-track-drawer-submit-label><?php esc_html_e('Bulk Add Tracks', 'vapfem'); ?></span>
      </div>
    </div>
  </div>
  <div data-lpl-track-drawer-footer="media-hub" class="lpl-hidden lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[12px_24px] lpl-justify-end lpl-items-center lpl-bg-[#FFFFFF] lpl-border-t lpl-border-line-strong">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-center">
      <div data-lpl-track-drawer-cancel class="lpl-btn lpl-btn--outline lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_12px] lpl-rounded-[8px] lpl-text-[13px]/[normal]">
        <?php esc_html_e('Cancel', 'vapfem'); ?>
      </div>
      <div data-lpl-track-drawer-close class="lpl-btn lpl-btn--indigo lpl-cursor-pointer lpl-h-[36px] lpl-p-[0px_16px] lpl-rounded-[8px] lpl-text-[13px]/[normal]">
        <?php esc_html_e('Done', 'vapfem'); ?>
      </div>
    </div>
  </div>
</div>
