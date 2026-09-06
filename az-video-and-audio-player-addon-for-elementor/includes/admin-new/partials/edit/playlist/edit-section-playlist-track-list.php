<?php
/**
 * Playlist edit screen - "Playlist Items" card. Drag-reorder, inline edit,
 * duplicate, and remove for the tracks already on this playlist. Separate
 * from the "Item Display" card (edit-section-playlist-items.php), which
 * only holds display-option toggles (thumbnails/duration badge/numbers/
 * meta/play icon) - this card is the track list itself.
 *
 * Task 1 (this file): static markup only, real `_playlist_items` data,
 * no drag/click/save behavior yet - that's Task 2 (admin-new.js) and
 * Task 3 (save wiring). Every data-lpl-track-* attribute below is the
 * contract those later tasks hook into.
 *
 * Each row's inline edit panel intentionally renders blank/placeholder
 * fields rather than the item's real url/title/duration/meta/poster -
 * Task 2 fetches and prefills those via the existing leanpl_playlist_get_player
 * AJAX action when the row is opened, same as edit-playlist-track-drawer.php's
 * Media Hub reuses existing endpoints instead of a new one.
 *
 * @var int    $post_id            Current playlist ID, 0 for a brand-new one.
 * @var string $edit_playlist_type 'video' or 'audio', set in html-playlist-edit-new-page.php.
 */
if (!defined('ABSPATH')) {
  exit;
}

// Same shape as edit-playlist-track-drawer.php's Media Hub existing-items
// query, kept local to this file (track_list_ prefix, not track_drawer_)
// since both partials render on the same page load and PHP top-level
// includes share one variable scope.
$track_list_items = [];
if ($post_id > 0) {
  $track_list_saved = get_post_meta($post_id, '_playlist_items', true);
  if (is_array($track_list_saved)) {
    foreach ($track_list_saved as $track_list_saved_item) {
      $track_list_player_id = absint($track_list_saved_item['id'] ?? 0);
      if ($track_list_player_id && get_post_type($track_list_player_id) === 'lean_player') {
        $track_list_items[] = $track_list_player_id;
      }
    }
  }
}
$track_list_count      = count($track_list_items);
$track_list_source_labels = [
  'youtube'  => __('YouTube', 'vapfem'),
  'vimeo'    => __('Vimeo', 'vapfem'),
  'external' => __('External', 'vapfem'),
  'self-hosted' => __('Uploaded', 'vapfem'),
];
$track_list_source_colors = [
  'youtube'  => '#EF4444',
  'vimeo'    => '#1AB7EA',
  'external' => '#767D86',
  'self-hosted' => '#10B981',
];

// Same audio-has-no-youtube/vimeo url placeholder split as the Add Track
// drawer's Paste a link field.
$track_list_url_placeholder = ('audio' === $edit_playlist_type)
  ? __('https://example.com/podcast-ep12.mp3', 'vapfem')
  : __('https://youtube.com/watch?v=dQw4w9WgXcQ', 'vapfem');
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start" data-lpl-track-list>

  <div data-lpl-track-list-rows class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start">
    <?php foreach ($track_list_items as $track_list_index => $track_list_player_id) :
      $track_list_title      = get_the_title($track_list_player_id);
      $track_list_badge      = leanpl_get_player_source_badge($track_list_player_id);
      $track_list_source_label = $track_list_source_labels[$track_list_badge['type']] ?? $track_list_badge['label'];
      $track_list_source_color = $track_list_source_colors[$track_list_badge['type']] ?? '#767D86';
      // Rows beyond the first 5 start collapsed - "Show N more" below reveals them.
      $track_list_overflow = ($track_list_index >= 5);
    ?>
    <div
      data-lpl-track-row
      data-lpl-track-player-id="<?php echo esc_attr($track_list_player_id); ?>"
      aria-expanded="false"
      class="lpl-group lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:var(--lpl-line)] [margin:0px_0px_-0.5px_0px] [&:last-child]:[border-width:0px] aria-expanded:lpl-bg-surface aria-expanded:lpl-rounded-[8px] aria-expanded:lpl-pr-[10px] aria-expanded:[border-bottom-width:0px]<?php echo $track_list_overflow ? ' lpl-hidden' : ''; ?>"
      <?php echo $track_list_overflow ? ' data-lpl-track-row-overflow' : ''; ?>
    >
      <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[8px_0px] lpl-justify-start lpl-items-center">
        <div data-lpl-track-drag-handle class="lpl-cursor-grab lpl-box-border lpl-w-[16px] lpl-shrink-0 lpl-h-[16px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-text-line-strong">
          <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('grip-vertical'); ?>
        </div>

        <div
          data-lpl-track-toggle
          role="button" tabindex="0"
          class="lpl-cursor-pointer lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-row lpl-gap-[10px] lpl-justify-start lpl-items-center"
        >
          <div class="lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[3px] lpl-justify-start lpl-items-start">
            <div data-lpl-track-title title="<?php echo esc_attr($track_list_title); ?>" class="lpl-text-[13px]/[normal] lpl-box-border lpl-w-full lpl-text-ink lpl-font-semibold lpl-text-left lpl-truncate">
              <?php echo esc_html($track_list_title); ?>
            </div>
            <div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
              <div data-lpl-track-source-badge class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-[16px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_6px] lpl-justify-center lpl-items-center lpl-rounded-[4px]" style="background-color:<?php echo esc_attr($track_list_source_color); ?>1A">
                <div data-lpl-track-source-label class="lpl-text-[9px]/[normal] lpl-box-border lpl-font-semibold lpl-tracking-[0.3px] lpl-text-left [white-space:nowrap]" style="color:<?php echo esc_attr($track_list_source_color); ?>">
                  <?php echo esc_html($track_list_source_label); ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[2px] lpl-justify-start lpl-items-center">
          <div data-lpl-track-duplicate role="button" tabindex="0" aria-label="<?php esc_attr_e('Duplicate', 'vapfem'); ?>" class="lpl-cursor-pointer lpl-box-border lpl-w-[26px] lpl-shrink-0 lpl-h-[26px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-text-ink-soft hover:lpl-bg-surface lpl-rounded-[6px]">
            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('copy'); ?>
          </div>
          <div data-lpl-track-remove role="button" tabindex="0" aria-label="<?php esc_attr_e('Remove', 'vapfem'); ?>" class="lpl-cursor-pointer lpl-box-border lpl-w-[26px] lpl-shrink-0 lpl-h-[26px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-text-ink-soft hover:lpl-bg-surface lpl-rounded-[6px]">
            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('x'); ?>
          </div>
        </div>
      </div>

      <?php // Inline edit panel - closed by default, opened by data-lpl-track-toggle (Task 2). Fields left blank; Task 2 prefills via leanpl_playlist_get_player. ?>
      <div data-lpl-track-edit-panel class="lpl-hidden lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[10px] lpl-p-[4px_0px_14px_26px] lpl-justify-start lpl-items-start">
        <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
          <label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Paste a link', 'vapfem'); ?>
          </label>
          <div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">
            <input type="text" data-lpl-track-edit-url placeholder="<?php echo esc_attr($track_list_url_placeholder); ?>"
              class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
          </div>
        </div>

        <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
          <label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Title', 'vapfem'); ?>
          </label>
          <div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">
            <input type="text" data-lpl-track-edit-title
              class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
          </div>
        </div>

        <div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-start">
          <div class="lpl-box-border [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
            <label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
              <?php esc_html_e('Duration', 'vapfem'); ?>
            </label>
            <div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">
              <input type="text" data-lpl-track-edit-duration placeholder="<?php esc_attr_e('e.g. 3:45', 'vapfem'); ?>"
                class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
            </div>
          </div>
          <div class="lpl-box-border [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
            <label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
              <?php esc_html_e('Meta Text', 'vapfem'); ?>
            </label>
            <div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">
              <input type="text" data-lpl-track-edit-meta-text placeholder="<?php esc_attr_e('e.g. Episode 12', 'vapfem'); ?>"
                class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />
            </div>
          </div>
        </div>

        <?php // Same two-state empty/set pattern as edit-playlist-track-drawer.php's data-lpl-track-drawer-poster-empty/-set. ?>
        <div data-lpl-track-edit-poster data-attachment-id="" class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
          <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Custom Thumbnail', 'vapfem'); ?>
          </div>

          <div data-lpl-track-edit-poster-empty data-lpl-track-edit-poster-select class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">
            <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-normal lpl-text-left [white-space:nowrap]">
              <?php esc_html_e('No image selected', 'vapfem'); ?>
            </div>
          </div>

          <div data-lpl-track-edit-poster-set class="lpl-hidden lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[8px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">
            <div class="lpl-relative lpl-box-border lpl-w-[44px] lpl-h-[44px] lpl-shrink-0 lpl-rounded-[6px] lpl-overflow-hidden [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px]">
              <img src="" alt="" class="lpl-absolute lpl-top-0 lpl-left-0 lpl-w-full lpl-h-full lpl-object-cover" data-lpl-track-edit-poster-image />
            </div>
            <div class="lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-center lpl-items-start">
              <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-w-full lpl-text-ink lpl-font-medium lpl-truncate" data-lpl-track-edit-poster-filename></div>
              <div class="lpl-box-border lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
                <div data-lpl-track-edit-poster-select role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#4F46E5] lpl-font-medium">
                  <?php esc_html_e('Change', 'vapfem'); ?>
                </div>
                <div data-lpl-track-edit-poster-remove role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#DC2626] lpl-font-medium">
                  <?php esc_html_e('Remove', 'vapfem'); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($track_list_count > 5) : ?>
    <div data-lpl-track-list-more-toggle role="button" tabindex="0" aria-expanded="false" class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[6px] lpl-p-[10px_0px] lpl-justify-center lpl-items-center">
      <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-brand lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <span data-lpl-track-list-more-count><?php echo esc_html((string) ($track_list_count - 5)); ?></span>
        <?php esc_html_e('more', 'vapfem'); ?>
      </div>
      <div class="lpl-box-border lpl-w-[14px] lpl-shrink-0 lpl-h-[14px] lpl-text-brand">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-down'); ?>
      </div>
    </div>
  <?php endif; ?>

  <?php
  // Always rendered, regardless of $track_list_count - the JS remove
  // handler (initTrackList() in admin-new.js) can empty the list purely
  // client-side without a page reload, and only ever toggles this block's
  // lpl-hidden class rather than inserting it fresh, so it has to already
  // be in the DOM on page load or removing the last track down to 0 would
  // have nothing to un-hide.
  ?>
  <div data-lpl-track-list-empty class="lpl-box-border lpl-w-full lpl-p-[24px_0px] lpl-text-[13px]/[normal] lpl-text-ink-soft lpl-text-center<?php echo (0 === $track_list_count) ? '' : ' lpl-hidden'; ?>">
    <?php esc_html_e('No tracks yet.', 'vapfem'); ?>
  </div>
</div>
