<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * One row of the "All Playlists (New)" table.
 *
 * Expected $row shape (see All_Playlists_Data::build_row):
 *   id, title, edit_url, status, status_label, type, type_label,
 *   item_count, item_count_label, created, created_ts, updated_ts, shortcode.
 *
 * No data-lpl-source: playlists have no source group, unlike players.
 * The data-lpl-* attributes on the row are the contract for the
 * client-side sort/filter/search logic (admin-new.js).
 */
?>
<div
  data-lpl-row
  data-lpl-id="<?php echo esc_attr( (string) $row['id'] ); ?>"
  data-lpl-type="<?php echo esc_attr( $row['type'] ); ?>"
  data-lpl-status="<?php echo esc_attr( $row['status'] ); ?>"
  data-lpl-title="<?php echo esc_attr( $row['title'] ); ?>"
  data-lpl-created="<?php echo esc_attr( (string) $row['created_ts'] ); ?>"
  data-lpl-updated="<?php echo esc_attr( (string) $row['updated_ts'] ); ?>"
  class="lpl-box-border lpl-w-full lpl-min-h-[67.5px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[14px_20px] lpl-justify-start lpl-items-center [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:#EEF0F3] [margin:0px_0px_-0.5px_0px]"
>
  <div
    class="lpl-box-border lpl-w-[44px] lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-start lpl-items-start"
  >
    <div
      data-lpl-checkbox
      data-lpl-row-checkbox
      role="checkbox"
      tabindex="0"
      aria-checked="false"
      class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-[17px] lpl-shrink-0 lpl-h-[17px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#FFFFFF] [outline:1.5px_solid_#E3E6EB] [outline-offset:-0.75px] aria-checked:lpl-bg-[#4F46E5] aria-checked:[outline:none] lpl-rounded-[5px]"
    >
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'checkbox-check' ); ?>
    </div>
  </div>
  <?php // Fixed-width title column. lpl-min-w-0 stays on the inner text column:
        // a flex item defaults to min-width:auto, so without it [flex:1_1_0]
        // refuses to shrink and a long title escapes the column. ?>
  <div
    class="lpl-box-border <?php echo esc_attr( $columns['title']['width'] ); ?> lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[13px] lpl-justify-start lpl-items-center"
  >
    <div
      class="lpl-box-border [flex:1_1_0] lpl-min-w-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start"
    >
      <?php // The row grows with its content (min-h, not h), so the title wraps
            // rather than truncating - long titles must stay fully readable.
            // overflow-wrap:anywhere is what breaks the space-less %20 titles
            // that would otherwise run past the column no matter its width.
            // The title attribute stays for a hover readout. ?>
      <a
        href="<?php echo esc_url( $row['edit_url'] ); ?>"
        title="<?php echo esc_attr( $row['title'] ); ?>"
        class="lpl-text-[14px]/[1.5] lpl-box-border lpl-w-full lpl-text-[#1B2233] lpl-font-medium lpl-text-left [overflow-wrap:anywhere] hover:lpl-text-brand hover:![text-decoration:underline] ![text-decoration:none]"
      >
        <?php echo esc_html( $row['title'] ); ?>
      </a>
      <div
        class="lpl-text-[12px]/[normal] lpl-box-border lpl-w-full lpl-truncate lpl-text-[#1B2233] lpl-font-normal lpl-text-left"
      >
        <?php echo esc_html( $row['item_count_label'] ); ?>
      </div>
    </div>
  </div>
  <div
    class="lpl-box-border <?php echo esc_attr( $columns['items']['width'] ); ?> lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center"
  >
    <?php if ( $row['type'] === 'audio' ) : ?>
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'type-audio' ); ?>
    <?php else : ?>
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'type-video' ); ?>
    <?php endif; ?>
    <div
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]"
    >
      <?php echo esc_html( $row['type_label'] ); ?>
    </div>
  </div>
  <div
    class="lpl-text-[13px]/[normal] lpl-box-border <?php echo esc_attr( $columns['created']['width'] ); ?> lpl-text-[#1B2233] lpl-font-normal lpl-text-left"
  >
    <?php echo esc_html( $row['created'] ); ?>
  </div>
  <div
    class="lpl-box-border <?php echo esc_attr( $columns['status']['width'] ); ?> lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-start lpl-items-start"
  >
    <div
      class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-p-[5px_12px] lpl-justify-start lpl-items-center <?php echo esc_attr( $row['pill_classes'] ); ?> lpl-rounded-[20px]"
    >
      <div
        class="lpl-text-[12px]/[normal] lpl-box-border lpl-font-semibold lpl-text-left [white-space:nowrap] <?php echo esc_attr( $row['pill_text_classes'] ); ?>"
      >
        <?php echo esc_html( $row['status_label'] ); ?>
      </div>
    </div>
  </div>
  <div
    class="lpl-box-border <?php echo esc_attr( $columns['shortcode']['width'] ); ?> lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-start lpl-items-start"
  >
    <div
      class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[6px_8px_6px_10px] lpl-justify-start lpl-items-center lpl-bg-[#F3F4F7] lpl-rounded-[8px]"
    >
      <div
        data-lpl-shortcode-text
        class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]"
      >
        <?php echo esc_html( $row['shortcode'] ); ?>
      </div>
      <div
        data-lpl-copy-shortcode
        class="lpl-btn-icon lpl-btn-icon--xs lpl-btn-icon--plain"
      >
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'copy' ); ?>
      </div>
    </div>
  </div>
  <div
    class="lpl-box-border <?php echo esc_attr( $columns['actions']['width'] ); ?> lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-end lpl-items-start"
  >
    <a
      href="<?php echo esc_url( $row['is_previewable'] ? $row['preview_url'] : '#' ); ?>"
      <?php echo $row['is_previewable'] ? 'target="_blank" rel="noopener"' : 'aria-disabled="true" tabindex="-1"'; ?>
      class="lpl-btn-icon lpl-btn-icon--sm lpl-btn-icon--hover aria-disabled:lpl-opacity-40 aria-disabled:lpl-pointer-events-none"
    >
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'row-preview' ); ?>
    </a>
    <a
      href="<?php echo esc_url( $row['edit_url'] ); ?>"
      class="lpl-btn-icon lpl-btn-icon--sm lpl-btn-icon--hover"
    >
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'row-edit' ); ?>
    </a>
    <div class="lpl-relative">
      <div data-lpl-dropdown-trigger="row-more-<?php echo esc_attr( (string) $row['id'] ); ?>"
        class="lpl-btn-icon lpl-btn-icon--sm lpl-btn-icon--hover"
      >
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'row-more' ); ?>
      </div>
      <div data-lpl-dropdown="row-more-<?php echo esc_attr( (string) $row['id'] ); ?>"
        class="lpl-hidden lpl-box-border lpl-w-[180px] lpl-h-fit [box-shadow:0px_12px_32px_0px_#1B22331F] lpl-absolute lpl-right-0 lpl-top-full lpl-mt-[8px] lpl-z-10 lpl-flex lpl-flex-col lpl-gap-[1px] lpl-p-[6px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[10px]"
      >
        <?php if ( $row['status'] !== 'trash' ) : ?>
          <div data-lpl-row-action="trash" data-lpl-row-action-id="<?php echo esc_attr( (string) $row['id'] ); ?>" data-lpl-dropdown-close
            class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px] hover:lpl-bg-[#F3F4F7]"
          >
            <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]"><?php esc_html_e( 'Move to Trash', 'vapfem' ); ?></div>
          </div>
        <?php else : ?>
          <div data-lpl-row-action="restore" data-lpl-row-action-id="<?php echo esc_attr( (string) $row['id'] ); ?>" data-lpl-dropdown-close
            class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px] hover:lpl-bg-[#F3F4F7]"
          >
            <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]"><?php esc_html_e( 'Restore', 'vapfem' ); ?></div>
          </div>
          <div data-lpl-row-action="delete" data-lpl-row-action-id="<?php echo esc_attr( (string) $row['id'] ); ?>" data-lpl-dropdown-close
            class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px] hover:lpl-bg-[#F3F4F7]"
          >
            <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#DC2626] lpl-font-medium lpl-text-left [white-space:nowrap]"><?php esc_html_e( 'Delete Permanently', 'vapfem' ); ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
