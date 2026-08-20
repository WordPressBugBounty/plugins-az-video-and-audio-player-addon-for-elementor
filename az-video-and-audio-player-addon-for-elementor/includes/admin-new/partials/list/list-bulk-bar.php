<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Bulk-action bar shared by the "All Players (New)" and "All Playlists
 * (New)" screens. Fully static markup - all dynamic text (selected count,
 * action labels, confirm state) is filled in client-side by admin-new.js
 * from the localized `strings`, so this partial takes no parameters.
 */
?>
<div data-lpl-bulkbar
  class="lpl-hidden lpl-box-border lpl-w-full lpl-h-[61.5px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[14px] lpl-p-[12px_20px] lpl-justify-between lpl-items-center lpl-bg-[#EEEEFE] [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:#EEF0F3] [margin:0px_0px_-0.5px_0px]">
  <div
    class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[14px] lpl-justify-start lpl-items-center">
    <div data-lpl-selected-count
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-bold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('0 selected', 'vapfem'); ?>
    </div>
    <div
      class="lpl-box-border lpl-w-[1px] lpl-shrink-0 lpl-h-[22px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-[#E3E6EB]">
    </div>
    <div class="lpl-relative">
      <div data-lpl-dropdown-trigger="change-status" data-lpl-bulk-action-current=""
        class="lpl-cursor-pointer lpl-box-border lpl-w-[200px] lpl-shrink-0 lpl-h-[38px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_14px] lpl-justify-between lpl-items-center lpl-bg-[#FFFFFF] [outline:1.5px_solid_#1B2233] [outline-offset:-0.75px] lpl-rounded-[10px]">
        <div data-lpl-bulk-action-label
          class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Change status', 'vapfem'); ?>
        </div>
        <svg viewBox="0 0 13.99993896484375 14" preserveAspectRatio="xMidYMid meet"
          xmlns="http://www.w3.org/2000/svg" class="lpl-box-border lpl-w-[16px] lpl-shrink-0 lpl-h-[16px]">
          <path
            d="M6.84619 4.68945q-0.08545 0.02734-0.19482 0.1128-0.14014 0.12646-0.53321 0.51611l-1.28857 1.2749q-1.81836 1.82178-1.86279 1.90723-0.04102 0.08203-0.04102 0.23584 0 0.15381 0.03418 0.23926 0.0376 0.08203 0.1333 0.18115 0.09912 0.0957 0.18115 0.1333 0.08545 0.03418 0.23926 0.03418 0.15381 0 0.23584-0.04102 0.08545-0.04443 1.66797-1.62353l1.58252-1.58252 1.58252 1.58252q1.58252 1.5791 1.66455 1.62353 0.08545 0.04102 0.23926 0.04102 0.15381 0 0.23584-0.03418 0.08545-0.0376 0.18115-0.1333 0.09912-0.09912 0.1333-0.18115 0.0376-0.08545 0.0376-0.23926 0-0.15381-0.04443-0.23584-0.04101-0.08545-1.85938-1.90723l-1.44238-1.42871q-0.33496-0.33496-0.46143-0.41699-0.09912-0.07178-0.19824-0.07178l-0.04102 0q-0.14014 0-0.18115 0.01367z"
            fill="#6A7180"></path>
        </svg>
      </div>
      <div data-lpl-dropdown="change-status"
        class="lpl-hidden lpl-box-border lpl-w-[200px] lpl-h-fit [box-shadow:0px_12px_32px_0px_#1B22331F] lpl-absolute lpl-left-0 lpl-top-full lpl-mt-[8px] lpl-z-10 lpl-flex lpl-flex-col lpl-gap-[1px] lpl-p-[6px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[10px]">
        <div data-lpl-bulk-action="set_status" data-lpl-bulk-action-scope="normal" data-lpl-dropdown-close
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px]">
          <div
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Set status', 'vapfem'); ?>
          </div>
        </div>
        <div data-lpl-bulk-action="trash" data-lpl-bulk-action-scope="normal" data-lpl-dropdown-close
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px]">
          <div
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Move to Trash', 'vapfem'); ?>
          </div>
        </div>
        <div data-lpl-bulk-action="restore" data-lpl-bulk-action-scope="trash" data-lpl-dropdown-close
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px]">
          <div
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Restore', 'vapfem'); ?>
          </div>
        </div>
        <div data-lpl-bulk-action="delete" data-lpl-bulk-action-scope="trash" data-lpl-dropdown-close
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px]">
          <div
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Delete permanently', 'vapfem'); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="lpl-relative lpl-hidden" data-lpl-bulk-status-wrapper>
      <div data-lpl-dropdown-trigger="bulk-status-target"
        class="lpl-cursor-pointer lpl-box-border lpl-w-[180px] lpl-shrink-0 lpl-h-[38px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_14px] lpl-justify-between lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[10px]">
        <div data-lpl-bulk-status-label
          class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Published', 'vapfem'); ?>
        </div>
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-down'); ?>
      </div>
      <div data-lpl-dropdown="bulk-status-target"
        class="lpl-hidden lpl-box-border lpl-w-[180px] lpl-h-fit [box-shadow:0px_12px_32px_0px_#1B22331F] lpl-absolute lpl-left-0 lpl-top-full lpl-mt-[8px] lpl-z-10 lpl-flex lpl-flex-col lpl-gap-[1px] lpl-p-[6px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[10px]">
        <div data-lpl-bulk-status-value="publish" data-lpl-dropdown-close
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px]">
          <div
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Published', 'vapfem'); ?>
          </div>
        </div>
        <div data-lpl-bulk-status-value="draft" data-lpl-dropdown-close
          class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-rounded-[8px]">
          <div
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Draft', 'vapfem'); ?>
          </div>
        </div>
      </div>
    </div>
    <div data-lpl-bulk-confirm role="button" tabindex="0"
      class="lpl-btn lpl-btn--md lpl-btn--indigo lpl-group aria-disabled:lpl-opacity-60 aria-disabled:lpl-cursor-not-allowed">
      <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
        class="lpl-hidden group-aria-disabled:lpl-block lpl-animate-spin lpl-box-border lpl-w-[14px] lpl-shrink-0 lpl-h-[14px]">
        <circle cx="8" cy="8" r="6.5" fill="none" stroke="#FFFFFF" stroke-opacity="0.35" stroke-width="2"></circle>
        <path d="M8 1.5a6.5 6.5 0 0 1 6.5 6.5" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round">
        </path>
      </svg>
      <div data-lpl-bulk-confirm-label
        class="lpl-box-border lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Confirm', 'vapfem'); ?>
      </div>
    </div>
  </div>
  <div data-lpl-clear-selection
    class="lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[7px] lpl-justify-start lpl-items-center">
    <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('clear-selection'); ?>
    <div
      class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#6A7180] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Clear selection', 'vapfem'); ?>
    </div>
  </div>
</div>
