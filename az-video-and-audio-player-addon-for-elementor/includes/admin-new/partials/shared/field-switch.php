<?php
/**
 * Reusable on/off switch row: label + hidden input + the data-lpl-switch
 * toggle, admin-new.js drives the toggle via data-lpl-switch-target. Every
 * boolean field on the admin-new screens is a switch, not a checkbox (see
 * edit-section-playlist-header.php's docblock for why), so this is the one
 * place that markup lives.
 *
 * @var string      $switch_label       Translated row label.
 * @var string      $switch_name        Meta key - hidden input name and data-lpl-switch-target.
 * @var string      $switch_value       Raw stored meta value ('0' unchecks, anything else checks).
 * @var string|null  $switch_show_if     Optional data-lpl-show-if value, for a row that's only
 *                                       visible while another switch in the same card is on.
 * @var bool|null    $switch_show_if_on  Whether $switch_show_if's controlling switch is currently on
 *                                       - required together with $switch_show_if.
 */
if (!defined('ABSPATH')) {
  exit;
}

$switch_checked = ('0' !== $switch_value);
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center<?php echo (null !== $switch_show_if && !$switch_show_if_on) ? ' lpl-hidden' : ''; ?>"<?php echo (null !== $switch_show_if) ? ' data-lpl-show-if="' . esc_attr($switch_show_if) . '"' : ''; ?>>
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php echo esc_html($switch_label); ?>
  </div>
  <input type="hidden" name="<?php echo esc_attr($switch_name); ?>" value="<?php echo esc_attr($switch_value); ?>">
  <div
    data-lpl-switch data-lpl-switch-target="<?php echo esc_attr($switch_name); ?>" role="switch" tabindex="0"
    aria-checked="<?php echo $switch_checked ? 'true' : 'false'; ?>"
    class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-[40px] lpl-shrink-0 lpl-h-[23px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[2px] lpl-justify-start lpl-items-center lpl-bg-line-strong aria-checked:lpl-bg-brand-soft aria-checked:lpl-justify-end lpl-rounded-[12px]"
  >
    <div class="lpl-box-border lpl-w-[19px] lpl-shrink-0 lpl-h-[19px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] lpl-rounded-[10px]"></div>
  </div>
</div>
