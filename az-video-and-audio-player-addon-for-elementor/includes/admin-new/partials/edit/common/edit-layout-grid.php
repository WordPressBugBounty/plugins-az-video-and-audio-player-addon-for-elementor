<?php
/**
 * Layout picker grid. Player-specific for now (playlist edit screen may
 * or may not reuse this - not decided yet). One card per $edit_layouts
 * entry, single-select via aria-selected + group-aria-selected, same
 * pattern as the list screens' sort menu.
 *
 * Naming: this is a layout picker throughout (matches the player_layout
 * key) - "preset" is reserved for the Custom Preset add-tile only.
 *
 * @var array  $edit_layouts List of ['key', 'label', 'image' (URL, absent
 *              for the Custom Preset / Inherit tiles), 'selected' (bool,
 *              optional), 'variant' (optional: 'add' for the Custom Preset
 *              tile, non-selectable; 'inherit' for the playlist screen's
 *              "use the global Player Layout" tile, selectable with an
 *              empty key, same as leaving the classic image-select unset)].
 * @var string $edit_layout_input_name Meta key the hidden input posts as -
 *              '_player_layout' for the player screen (default, so existing
 *              callers need no change), '_playlist_player_layout' for the
 *              playlist screen. Set by edit-section-card.php from
 *              $section['layout_input_name'].
 * @var bool   $edit_layout_locked Whole-grid pro lock (default false, so
 *              the player screen's free grid needs no change). When true:
 *              a gem badge replaces the label's normal weight, the hidden
 *              input drops its `name` so a locked pick never posts (same
 *              "no name = never posts" pattern as every other whole-field
 *              pro row - see edit-section-playlist-appearance.php's Accent
 *              Color), and every tile opens the upgrade modal on click
 *              instead of getting a data-lpl-layout-option (so
 *              initLayoutPicker() in admin-new.js never wires it up as
 *              selectable), and a notice + Upgrade to PRO CTA renders below
 *              the grid, pointing at the free site-wide picker in Global
 *              Settings so the lock reads as "per-item override is pro",
 *              not "this feature is pro". Set by edit-section-card.php from
 *              $section['layout_locked'].
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_layout_input_name = $edit_layout_input_name ?? '_player_layout';
$edit_layout_locked     = $edit_layout_locked ?? false;

$edit_layout_selected = 'classic';
foreach ($edit_layouts as $edit_layout_row) {
  if (!empty($edit_layout_row['selected'])) {
    $edit_layout_selected = $edit_layout_row['key'];
    break;
  }
}
?>
<input
  type="hidden"
  <?php if (!$edit_layout_locked) : ?>name="<?php echo esc_attr($edit_layout_input_name); ?>"<?php endif; ?>
  value="<?php echo esc_attr($edit_layout_selected); ?>"
  data-lpl-layout-input
>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
    <?php esc_html_e('Player Layout', 'vapfem'); ?>
  </div>
  <?php if ($edit_layout_locked) : ?>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[3px_7px] lpl-justify-center lpl-items-center lpl-bg-[#6C5CE7] lpl-rounded-[4px]">
    <div class="lpl-text-[9px]/[normal] lpl-box-border lpl-text-[#FFFFFF] lpl-font-bold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('PRO', 'vapfem'); ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<div class="lpl-grid lpl-grid-cols-3 max-md:lpl-grid-cols-2 lpl-gap-x-[12px] lpl-gap-y-[7px] lpl-w-full">
  <?php foreach ($edit_layouts as $edit_layout) : ?>
    <?php if ('add' === ($edit_layout['variant'] ?? '')) : ?>
      <?php
      // Selectable-looking tile (role/aria-selected/checkmark badge, same
      // shape as the real layout tiles below) even though it never carries
      // data-lpl-layout-option - initLayoutPicker() (admin-new.js) only
      // wires up tiles that have that attribute, so this one is deliberately
      // excluded from its native click-to-select flow. Its own applied/empty
      // state is driven entirely by custom-preset-builder.js's
      // syncCardLabel(), keyed off data-lpl-custom-preset-tile - same "mute
      // the real picker while a preset owns the field" job
      // .lpl-cpm__real-check-hidden does on the classic lex-settings grid.
      ?>
      <div
        <?php if ($edit_layout_locked) : ?>
        onclick="openUpgradeModal()"
        <?php else : ?>
        onclick="LeanPLCustomPreset.open()" data-lpl-custom-preset-tile
        <?php endif; ?>
        role="option" tabindex="0" aria-selected="false"
        class="lpl-cpm__custom-preset-tile lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[7px] lpl-justify-start lpl-items-start"
      >
        <div class="lpl-relative lpl-box-border lpl-w-full [aspect-ratio:554/302] lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[5px] lpl-p-[6px] lpl-justify-center lpl-items-center lpl-bg-[#F1F3F3] [outline:1px_solid_#DCDCDE] [outline-offset:-0.5px] aria-selected:[outline:2px_solid_#3758EA] lpl-rounded-[8px] lpl-overflow-hidden">
          <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('plus'); ?>
          <div class="lpl-hidden group-aria-selected:lpl-flex lpl-absolute lpl-right-[6px] lpl-top-[6px] lpl-box-border lpl-w-[14px] lpl-shrink-0 lpl-h-[14px] lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-brand lpl-rounded-[8px]">
            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('check'); ?>
          </div>
        </div>
        <div class="lpl-cpm__custom-preset-label lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-medium lpl-text-left [white-space:nowrap]">
          <?php echo esc_html($edit_layout['label']); ?>
        </div>
      </div>
    <?php else : ?>
      <div
        <?php if ($edit_layout_locked) : ?>
        onclick="openUpgradeModal()"
        <?php else : ?>
        data-lpl-layout-option="<?php echo esc_attr($edit_layout['key']); ?>"
        <?php endif; ?>
        role="option" tabindex="0" aria-selected="<?php echo !empty($edit_layout['selected']) ? 'true' : 'false'; ?>"
        class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[7px] lpl-justify-start lpl-items-start"
      >
        <?php if ('inherit' === ($edit_layout['variant'] ?? '')) : ?>
          <div class="lpl-relative lpl-box-border lpl-w-full [aspect-ratio:554/302] lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[5px] lpl-p-[6px] lpl-justify-center lpl-items-center lpl-bg-[#F1F3F3] [outline:1px_dashed_#DCDCDE] [outline-offset:-0.5px] aria-selected:[outline:2px_solid_#3758EA] lpl-rounded-[8px] lpl-overflow-hidden">
            <?php echo leanpl_ssot('icons', 'layout_svg'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted inline SVG. ?>
            <div class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-normal lpl-text-center [white-space:nowrap]">
              <?php esc_html_e('Global Default', 'vapfem'); ?>
            </div>
            <div class="lpl-hidden group-aria-selected:lpl-flex lpl-absolute lpl-right-[6px] lpl-top-[6px] lpl-box-border lpl-w-[14px] lpl-shrink-0 lpl-h-[14px] lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-brand lpl-rounded-[8px]">
              <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('check'); ?>
            </div>
          </div>
        <?php else : ?>
          <div class="lpl-relative lpl-box-border lpl-w-full [aspect-ratio:554/302] lpl-shrink-0 [outline:1px_solid_#DCDCDE] [outline-offset:-0.5px] aria-selected:[outline:2px_solid_#3758EA] lpl-rounded-[8px] lpl-overflow-hidden">
            <img
              src="<?php echo esc_url($edit_layout['image']); ?>"
              alt=""
              class="lpl-absolute lpl-top-0 lpl-left-0 lpl-box-border lpl-w-full lpl-h-full lpl-object-cover"
            />
            <div class="lpl-hidden group-aria-selected:lpl-flex lpl-absolute lpl-right-[6px] lpl-top-[6px] lpl-box-border lpl-w-[14px] lpl-shrink-0 lpl-h-[14px] lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-brand lpl-rounded-[8px]">
              <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('check'); ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-medium lpl-text-left [white-space:nowrap]">
          <?php echo esc_html($edit_layout['label']); ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
<?php if ($edit_layout_locked) : ?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[10px_12px] lpl-justify-start lpl-items-start lpl-bg-[#F5F3FF] [outline:1.5px_solid_#6C5CE7] [outline-offset:-1.5px] lpl-rounded-[8px]">
  <div class="lpl-box-border lpl-w-[16px] lpl-shrink-0 lpl-h-[16px] lpl-mt-[1px]">
    <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?>
  </div>
  <div class="lpl-box-border lpl-flex-1">
    <div class="lpl-text-[12px]/[1.5] lpl-box-border lpl-text-[#3D2E9C] lpl-font-medium lpl-text-left">
      <?php
      printf(
        /* translators: 1: opening <a> tag linking to Settings -> Player Layout, 2: closing </a> tag. */
        esc_html__('One Player Layout for your whole site is free, in %1$sGlobal Settings%2$s.', 'vapfem'),
        '<a href="' . esc_url(admin_url('admin.php?page=lean-player-settings#settings')) . '" target="_blank" class="lpl-font-semibold lpl-underline">',
        '</a>'
      );
      ?>
      <strong class="lpl-font-bold"><?php esc_html_e('Want a different look for a specific player or playlist? Upgrade to PRO and set a custom layout for any of them, individually.', 'vapfem'); ?></strong>
    </div>
    <button
      type="button"
      onclick="openUpgradeModal()"
      class="lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-h-fit lpl-mt-[8px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[6px_12px] lpl-justify-center lpl-items-center lpl-bg-[#6C5CE7] lpl-border-0 lpl-rounded-[6px]"
    >
      <span class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#FFFFFF] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Upgrade to PRO', 'vapfem'); ?>
      </span>
    </button>
  </div>
</div>
<?php endif; ?>
