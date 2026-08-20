<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Table footer ("Showing X of Y" + pager) shared by the "All Players (New)"
 * and "All Playlists (New)" screens. Fully static markup - both fields are
 * filled in client-side by admin-new.js, so this partial takes no parameters.
 */
?>
<div
  class="lpl-box-border lpl-w-full lpl-h-[59.5px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[14px_20px] lpl-justify-between lpl-items-center [border-width:1px_0px_0px_0px] [border-style:solid] [border-color:#EEF0F3] [margin:-0.5px_0px_0px_0px]">
  <div data-lpl-showing
    class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#9AA1AE] lpl-font-normal lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Showing 0 of 0', 'vapfem'); ?>
  </div>
  <div
    class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    <div data-lpl-pager
      class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
    </div>
  </div>
</div>
