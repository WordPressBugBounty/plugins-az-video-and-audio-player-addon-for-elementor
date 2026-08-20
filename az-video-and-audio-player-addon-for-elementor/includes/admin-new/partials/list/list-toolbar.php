<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Toolbar shared by the "All Players (New)" and "All Playlists (New)"
 * screens: status tabs + More menu, search, Filters panel, Sort panel.
 *
 * Expects in scope: $page_slug, $current_status, $counts, $search_placeholder,
 * $filter_groups (array of ['heading'=>string,'filter'=>string,'options'=>[['value','label'],...]]),
 * $toolbar_row_class, $toolbar_left_class, $toolbar_right_class (full class
 * strings for the outer row and its two flex containers - these differ
 * between the two screens today, so they stay parameters rather than being
 * silently unified).
 */

$leanpl_render_tab = function ($key, $label, $count, $current_status) use ($page_slug) {
  $url = admin_url('admin.php?page=' . $page_slug . '&status=' . $key);
  $active = $key === $current_status;
  $bg = $active ? 'lpl-bg-[#EEEEFE]' : 'lpl-bg-[#00000000] hover:lpl-bg-[#F3F4F7]';
  $color = $active ? 'lpl-text-[#4F46E5] lpl-font-semibold' : 'lpl-text-[#6A7180] lpl-font-semibold';
  ?>
  <a href="<?php echo esc_url($url); ?>"
    class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center lpl-rounded-[9px] ![text-decoration:none] ![outline:none] ![box-shadow:none] <?php echo esc_attr($bg . ' lpl-p-[8px_15px]'); ?>">
    <div
      class="lpl-text-[13px]/[normal] lpl-box-border <?php echo esc_attr($color); ?> lpl-text-left [white-space:nowrap]">
      <?php echo esc_html($label); ?>
    </div>
  </a>
  <?php
};

$leanpl_render_more_row = function ($key, $label, $count, $svg, $current_status) use ($page_slug) {
  if ($count <= 0) {
    return;
  }
  $url = admin_url('admin.php?page=' . $page_slug . '&status=' . $key);
  $active = $key === $current_status;
  $row_bg = $active ? 'lpl-bg-[#EEEEFE]' : '';
  ?>
  <a href="<?php echo esc_url($url); ?>"
    class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[9px_10px] lpl-justify-between lpl-items-center lpl-rounded-[8px] ![text-decoration:none] <?php echo esc_attr($row_bg); ?>">
    <div
      class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[10px] lpl-justify-start lpl-items-center">
      <?php echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <div
        class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
        <?php echo esc_html($label); ?>
      </div>
    </div>
    <div
      class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[2px_7px] lpl-justify-start lpl-items-start lpl-bg-[#F3F4F7] lpl-rounded-[6px]">
      <div
        class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#6A7180] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php echo esc_html((string) $count); ?>
      </div>
    </div>
  </a>
  <?php
};
?>
<div class="<?php echo esc_attr($toolbar_row_class); ?>">

  <?php /* Toolbar left: status tabs + More dropdown */ ?>
  <div class="<?php echo esc_attr($toolbar_left_class); ?>">
    <?php $leanpl_render_tab('all', __('All', 'vapfem'), $counts['all'], $current_status); ?>
    <?php $leanpl_render_tab('publish', __('Published', 'vapfem'), $counts['publish'], $current_status); ?>
    <?php $leanpl_render_tab('draft', __('Drafts', 'vapfem'), $counts['draft'], $current_status); ?>
    <?php if ($counts['future'] + $counts['trash'] > 0): ?>
      <div class="lpl-relative">
        <div data-lpl-dropdown-trigger="more-menu"
          class="lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[5px] lpl-p-[8px_12px] lpl-justify-start lpl-items-center lpl-rounded-[9px]">
          <div
            class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#6A7180] lpl-font-semibold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('More', 'vapfem'); ?>
          </div>
          <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-down'); ?>
        </div>
        <div data-lpl-dropdown="more-menu"
          class="lpl-hidden lpl-box-border lpl-w-[236px] lpl-h-fit [box-shadow:0px_12px_32px_0px_#1B22331F] lpl-absolute lpl-left-0 lpl-top-full lpl-mt-[8px] lpl-z-10 lpl-flex lpl-flex-col lpl-gap-[2px] lpl-p-[6px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[12px]">
          <?php $leanpl_render_more_row('future', __('Scheduled', 'vapfem'), $counts['future'], \LeanPL\Admin_New\Admin_New_Icons::get('clock'), $current_status); ?>
          <?php $leanpl_render_more_row('trash', __('Trash', 'vapfem'), $counts['trash'], \LeanPL\Admin_New\Admin_New_Icons::get('trash'), $current_status); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php /* Toolbar right: search, Filters, Sort */ ?>
  <div class="<?php echo esc_attr($toolbar_right_class); ?>">
    <div
      class="lpl-box-border lpl-w-[260px] lpl-shrink-0 lpl-h-[38px] lpl-flex lpl-flex-row lpl-gap-[9px] lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-[#F3F4F7] lpl-rounded-[10px]">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('search'); ?>
      <input type="text" data-lpl-search-input placeholder="<?php echo esc_attr($search_placeholder); ?>"
        class="lpl-flex-1 lpl-min-w-0 lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-normal lpl-text-left placeholder:lpl-text-[#9AA1AE] ![background:transparent] ![border:none] ![box-shadow:none] ![padding:0] ![outline:none]" />
    </div>

    <?php /* Filters trigger + panel */ ?>
    <div class="lpl-relative">
      <div data-lpl-dropdown-trigger="filters"
        class="lpl-btn lpl-btn--md lpl-btn--outline-lt">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('filter'); ?>
        <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Filters', 'vapfem'); ?>
        </div>
        <span data-lpl-filter-count
          class="lpl-hidden lpl-box-border lpl-min-w-[18px] lpl-h-[18px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#4F46E5] lpl-text-[#FFFFFF] lpl-text-[11px]/[normal] lpl-font-semibold lpl-rounded-[9px] lpl-px-[5px]">0</span>
      </div>
      <div data-lpl-dropdown="filters"
        class="lpl-hidden lpl-box-border lpl-w-[300px] lpl-h-fit [box-shadow:0px_14px_36px_0px_#1B22331F] lpl-absolute lpl-right-0 lpl-top-full lpl-mt-[8px] lpl-z-10 lpl-flex lpl-flex-col lpl-gap-[16px] lpl-p-[16px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[14px]">
        <div
          class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
          <div
            class="lpl-text-[15px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-bold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Filters', 'vapfem'); ?>
          </div>
          <div data-lpl-checkbox-reset
            class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#4F46E5] lpl-font-semibold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Reset all', 'vapfem'); ?>
          </div>
        </div>

        <?php foreach ($filter_groups as $leanpl_fg_index => $leanpl_fg): ?>
          <?php if ($leanpl_fg_index > 0): ?>
            <div class="lpl-box-border lpl-w-full lpl-h-[1px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-[#EEF0F3]"></div>
          <?php endif; ?>
          <div
            class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[8px] lpl-justify-start lpl-items-start">
            <div
              class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#9AA1AE] lpl-font-bold lpl-tracking-[0.6px] lpl-text-left [white-space:nowrap]">
              <?php echo esc_html($leanpl_fg['heading']); ?>
            </div>
            <?php foreach ($leanpl_fg['options'] as $leanpl_opt): ?>
              <div
                class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[3px_0px] lpl-justify-start lpl-items-center">
                <div data-lpl-checkbox data-lpl-filter="<?php echo esc_attr($leanpl_fg['filter']); ?>" data-lpl-filter-value="<?php echo esc_attr($leanpl_opt['value']); ?>" role="checkbox" tabindex="0"
                  aria-checked="false"
                  class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-[18px] lpl-shrink-0 lpl-h-[18px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#FFFFFF] [outline:1.5px_solid_#E3E6EB] [outline-offset:-0.75px] aria-checked:lpl-bg-[#4F46E5] aria-checked:[outline:none] lpl-rounded-[5px]">
                  <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('checkbox-check'); ?>
                </div>
                <div
                  class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
                  <?php echo esc_html($leanpl_opt['label']); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <?php // Filters apply on every checkbox toggle, so the panel needs its own
        // result readout: the table behind it is largely covered while the
        // panel is open. Filled by applyRowFilters() in admin-new.js. ?>
        <div data-lpl-filter-match
          class="lpl-text-[12px]/[normal] lpl-box-border lpl-w-full lpl-shrink-0 lpl-text-[#6A7180] lpl-font-semibold lpl-text-left [white-space:nowrap]"
          aria-live="polite"></div>
        <div
          class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-justify-start lpl-items-center">
          <div data-lpl-checkbox-reset
            class="lpl-btn lpl-btn--md lpl-btn--outline-lt [flex:1_1_0]">
            <div class="lpl-box-border lpl-text-[#6A7180] lpl-text-left [white-space:nowrap]">
              <?php esc_html_e('Clear', 'vapfem'); ?>
            </div>
          </div>
          <div data-lpl-dropdown-close
            class="lpl-btn lpl-btn--md lpl-btn--indigo [flex:1_1_0]">
            <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
              <?php esc_html_e('Done', 'vapfem'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php /* Sort trigger (icon only) + panel */ ?>
    <div class="lpl-relative">
      <div data-lpl-dropdown-trigger="sort"
        class="lpl-btn-icon lpl-btn-icon--md lpl-btn-icon--outline-lt">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('sort'); ?>
      </div>
      <div data-lpl-dropdown="sort"
        class="lpl-hidden lpl-box-border lpl-w-[232px] lpl-h-fit [box-shadow:0px_12px_32px_0px_#1B22331F] lpl-absolute lpl-right-0 lpl-top-full lpl-mt-[8px] lpl-z-10 lpl-flex lpl-flex-col lpl-gap-[1px] lpl-p-[6px] lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[12px]">
        <div
          class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[8px_10px_6px_10px] lpl-justify-start lpl-items-start">
          <div
            class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#9AA1AE] lpl-font-bold lpl-tracking-[0.6px] lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('SORT BY', 'vapfem'); ?>
          </div>
        </div>
        <?php
        $leanpl_sort_options = [
          'date-desc'    => __('Newest first', 'vapfem'),
          'date-asc'     => __('Oldest first', 'vapfem'),
          'title-asc'    => __('Name (A–Z)', 'vapfem'),
          'title-desc'   => __('Name (Z–A)', 'vapfem'),
          'updated-desc' => __('Recently updated', 'vapfem'),
          'id-desc'      => __('ID (high to low)', 'vapfem'),
        ];
        foreach ($leanpl_sort_options as $leanpl_sort_key => $leanpl_sort_label):
          $leanpl_sort_selected = $leanpl_sort_key === 'date-desc' ? 'true' : 'false';
          ?>
          <div data-lpl-sort-option data-lpl-sort="<?php echo esc_attr($leanpl_sort_key); ?>" role="option"
            tabindex="0" aria-selected="<?php echo esc_attr($leanpl_sort_selected); ?>"
            class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[9px_10px] lpl-justify-between lpl-items-center aria-selected:lpl-bg-[#EEEEFE] lpl-rounded-[8px]">
            <div
              class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap] group-aria-selected:lpl-text-[#4F46E5] group-aria-selected:lpl-font-semibold">
              <?php echo esc_html($leanpl_sort_label); ?>
            </div>
            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('sort-selected-check'); ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
