<?php
/**
 * Compact shortcode copy chip (the top bar's shortcode pill). Wires the
 * existing data-lpl-shortcode-text / data-lpl-copy-shortcode handler in
 * admin-new.js (no new JS convention).
 *
 * The Timestamp Link Shortcode card's full-width code box moved into
 * edit-section-timestamp.php during the wiring phase; this partial now
 * renders only the top bar's chip.
 *
 * @var array $field 'value' (required).
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-[36px] lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[0px_6px_0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[10px]">
  <div data-lpl-shortcode-text class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-label-secondary lpl-font-normal lpl-text-left [white-space:nowrap]">
    <?php echo esc_html($field['value']); ?>
  </div>
  <div data-lpl-copy-shortcode
    class="lpl-btn lpl-btn--xs lpl-btn--dark">
    <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Copy', 'vapfem'); ?>
    </div>
  </div>
</div>
