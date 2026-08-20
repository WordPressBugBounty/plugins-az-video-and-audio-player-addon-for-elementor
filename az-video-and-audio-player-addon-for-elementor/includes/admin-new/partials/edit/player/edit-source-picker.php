<?php
/**
 * Source picker: tab row (Media Library/YouTube/Vimeo/Audio) + OR
 * divider + URL paste section + Add Media button. Always starts on the
 * generic (no-tab-active) copy - see html-player-edit-new-page.php's
 * doc comment for why. Picking a source (Media Library selection or Add
 * Media) hides this whole surface and shows edit-player-surface.php's
 * live preview instead (admin-new.js, data-lpl-edit-surface).
 *
 * @var array  $edit_source_tabs           Tab configs from config/player-edit-source-tabs.php:
 *                                          [['key','label','show_url_field','url_label'?,'url_placeholder'?,'help_text'?], ...]
 * @var string $edit_source_active_tab     Key of the visually-selected tab, '' for none.
 * @var bool   $edit_source_show_url_field Whether the active tab's URL section is visible.
 * @var string $edit_source_url_label      e.g. "PASTE A MEDIA URL".
 * @var string $edit_source_url_placeholder
 * @var string $edit_source_help_text
 * @var bool   $edit_source_show_or        optional, default true.
 * @var bool   $edit_source_show_add_media optional, default true.
 */
if (!defined('ABSPATH')) {
  exit;
}
$edit_source_show_url_field = $edit_source_show_url_field ?? true;
$edit_source_show_or        = $edit_source_show_or ?? true;
$edit_source_show_add_media = $edit_source_show_add_media ?? true;
?>
<div class="lpl-box-border lpl-w-full lpl-h-[520px] lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[10px] lpl-overflow-hidden">
  <div class="lpl-box-border lpl-w-[520px] lpl-max-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[20px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[15px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Add media from different sources', 'vapfem'); ?>
    </div>
    <div class="lpl-box-border lpl-w-fit lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-justify-start lpl-items-center">
      <?php foreach ($edit_source_tabs as $edit_tab) : ?>
        <?php $edit_tab_active = ($edit_tab['key'] === $edit_source_active_tab); ?>
        <div data-lpl-source-tab="<?php echo esc_attr($edit_tab['key']); ?>"
          data-lpl-source-show-url="<?php echo !empty($edit_tab['show_url_field']) ? '1' : '0'; ?>"
          data-lpl-source-url-label="<?php echo esc_attr($edit_tab['url_label'] ?? ''); ?>"
          data-lpl-source-placeholder="<?php echo esc_attr($edit_tab['url_placeholder'] ?? ''); ?>"
          data-lpl-source-help="<?php echo esc_attr($edit_tab['help_text'] ?? ''); ?>"
          role="tab" tabindex="0" aria-selected="<?php echo $edit_tab_active ? 'true' : 'false'; ?>"
          class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-[36px] lpl-flex lpl-flex-row lpl-gap-[6px] lpl-p-[0px_14px] lpl-justify-center lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] aria-selected:lpl-bg-ink aria-selected:[outline:1px_solid_var(--lpl-ink)] lpl-rounded-[6px]">
          <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#1A1A1A] lpl-font-semibold lpl-text-left [white-space:nowrap] group-aria-selected:lpl-text-[#FFFFFF]">
            <?php echo esc_html($edit_tab['label']); ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($edit_source_show_or) : ?>
      <div data-lpl-source-or class="lpl-box-border lpl-w-[520px] lpl-max-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
        <div class="lpl-box-border [flex:1_1_0] lpl-h-[1px] lpl-bg-line"></div>
        <div class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-semibold lpl-tracking-[1px] lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('OR', 'vapfem'); ?>
        </div>
        <div class="lpl-box-border [flex:1_1_0] lpl-h-[1px] lpl-bg-line"></div>
      </div>
    <?php endif; ?>
    <div data-lpl-source-url-field class="lpl-box-border lpl-w-[520px] lpl-max-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[8px] lpl-justify-start lpl-items-start<?php echo $edit_source_show_url_field ? '' : ' lpl-hidden'; ?>">
      <div data-lpl-source-url-label-target class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-bold lpl-tracking-[0.5px] lpl-text-left [white-space:nowrap]">
        <?php echo esc_html($edit_source_url_label); ?>
      </div>
      <div class="lpl-box-border lpl-w-full lpl-h-[40px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_14px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[9px]">
        <input type="text" data-lpl-source-placeholder-target placeholder="<?php echo esc_attr($edit_source_url_placeholder); ?>"
          class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none] [-webkit-appearance:none]" />
      </div>
      <div data-lpl-source-help-target class="lpl-text-[12px]/[18px] lpl-box-border lpl-w-full lpl-text-[#2D2D2D] lpl-font-normal lpl-text-left">
        <?php echo esc_html($edit_source_help_text); ?>
      </div>
    </div>
    <?php if ($edit_source_show_add_media) : ?>
      <div data-lpl-add-media class="lpl-btn lpl-btn--md lpl-btn--indigo">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('plus'); ?>
        <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Add Media', 'vapfem'); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
