<?php
/**
 * One collapsible right-column accordion card. Shared by player and
 * playlist edit screens - reads $section_key/$section only, never
 * branches on which card it's rendering.
 *
 * Expected variables:
 * @var string $section_key Array key from player-edit-sections.php, e.g. 'general'.
 *                           A 'playlist-' prefix routes the card partial
 *                           lookup below to partials/edit/playlist/ instead
 *                           of partials/edit/player/ - see player-edit-sections.php
 *                           and playlist-edit-sections.php for where each
 *                           $section_key set is defined.
 * @var array  $section     ['label' => string, 'icon' => string, 'open' => bool,
 *                            'layouts' => array (optional),
 *                            'layout_input_name' => string (optional,
 *                            defaults to '_player_layout' in
 *                            edit-layout-grid.php),
 *                            'layout_locked' => bool (optional, whole-field
 *                            pro lock - see edit-layout-grid.php)]. Rows
 *                            live in the card's own edit-section-{key}.php
 *                            partial, not here.
 *
 * Card body was ported at a fixed 558px/content-box width in the Pencil
 * export (matches the fixed-canvas problem docs/admin-redesign-pencil-
 * porting-process.md section 5 describes for the top bar) - fluid-ized
 * to w-full/box-border here so it tracks the flex-1 right column instead
 * of overflowing or under-filling it.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px] lpl-rounded-[10px] lpl-overflow-hidden">
  <div
    data-lpl-accordion-trigger="<?php echo esc_attr($section_key); ?>"
    role="button" tabindex="0" aria-expanded="<?php echo !empty($section['open']) ? 'true' : 'false'; ?>"
    class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[14px_16px] lpl-justify-start lpl-items-center"
  >
    <div class="lpl-box-border lpl-w-[30px] lpl-shrink-0 lpl-h-[30px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px] lpl-rounded-[7px]">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get($section['icon']); ?>
    </div>
    <div class="lpl-text-[14px]/[normal] lpl-box-border [flex:1_1_0] lpl-text-ink lpl-font-semibold lpl-text-left">
      <?php echo esc_html($section['label']); ?>
    </div>
    <div class="lpl-box-border lpl-w-[18px] lpl-shrink-0 lpl-h-[18px] group-aria-expanded:[transform:rotate(180deg)]">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('chevron-down'); ?>
    </div>
  </div>
  <div
    data-lpl-accordion="<?php echo esc_attr($section_key); ?>"
    class="<?php echo empty($section['open']) ? 'lpl-hidden ' : ''; ?>lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[14px] lpl-p-[14px_16px_18px_16px] lpl-justify-start lpl-items-start [border-width:1px_0px_0px_0px] [border-style:solid] [border-color:var(--lpl-line)] [margin:-0.5px_0px_0px_0px]"
  >
    <?php
    $edit_section_group = (strpos($section_key, 'playlist-') === 0) ? 'playlist' : 'player';
    ?>
    <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/' . $edit_section_group . '/edit-section-' . $section_key . '.php'; ?>
    <?php if (!empty($section['layouts'])) : ?>
      <?php
      $edit_layouts           = $section['layouts'];
      $edit_layout_input_name = $section['layout_input_name'] ?? '_player_layout';
      $edit_layout_locked     = $section['layout_locked'] ?? false;
      ?>
      <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-layout-grid.php'; ?>
    <?php endif; ?>
  </div>
</div>
